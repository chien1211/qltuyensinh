<?php
session_start();

// 1. BẢO MẬT: CHỈ ADMIN HOẶC STAFF MỚI CÓ QUYỀN
// (Thí sinh không được vào đây)
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: ../login.php'); // Đá về trang login
    exit;
}

// 2. NẠP CÁC FILE CẦN THIẾT
require '../db_connection.php'; // Kết nối CSDL
$conn = getDbConnection();

// Lấy thông tin user (Admin/Staff)
$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Đặt biến để sidebar biết trang nào đang active
$current_page = 'duyet-ho-so';

// ---------------------------------------------------------------
// 3. LOGIC LẤY DANH SÁCH HỒ SƠ (READ)
// ---------------------------------------------------------------
// Đây là câu query
// JOIN 3 bảng: ho_so_xet_tuyen, thi_sinh, và nganh_hoc

$sql_select = "
    SELECT 
        hs.id AS ho_so_id,
        ts.ho_ten,
        ts.email AS email_thi_sinh,
        ts.so_cccd,
        hs.ngay_nop,
        ng1.ten_nganh AS ten_nganh_1
    FROM 
        ho_so_xet_tuyen AS hs
    JOIN 
        thi_sinh AS ts ON hs.thi_sinh_id = ts.id
    LEFT JOIN 
        nganh_hoc AS ng1 ON hs.ma_nganh_1 = ng1.ma_nganh
    WHERE 
        hs.trang_thai = 'da_nop' 
    ORDER BY 
        hs.ngay_nop ASC;    
";

$result_select = mysqli_query($conn, $sql_select);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Duyệt Hồ sơ Xét tuyển</title>
</head>
<body class="app-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        
        <?php include 'partials/main_header.php'; ?>

        <div class="card">
            <h3>Danh sách Hồ sơ mới (Chờ duyệt)</h3>
            
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Họ tên Thí sinh</th>
                            <th>Email</th>
                            <th>CCCD</th>
                            <th>Nguyện vọng 1</th>
                            <th>Ngày nộp</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_select) == 0): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-secondary);">
                                    <i class="fas fa-check-circle"></i> Tốt! Không có hồ sơ nào mới đang chờ duyệt.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($ho_so = mysqli_fetch_assoc($result_select)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ho_so['ho_ten']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ho_so['email_thi_sinh']); ?></td>
                                    <td><?php echo htmlspecialchars($ho_so['so_cccd']); ?></td>
                                    <td><?php echo htmlspecialchars($ho_so['ten_nganh_1'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ho_so['ngay_nop'])); ?></td>
                                    <td>
                                        <a href="chi-tiet-ho-so.php?id=<?php echo $ho_so['ho_so_id']; ?>" 
                                           class="btn-submit" 
                                           style="padding: 8px 12px; text-decoration: none; display: inline-block;">
                                            Xem & Duyệt
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>

    </main>
</body>
</html>
<?php
// Đóng kết nối CSDL
mysqli_close($conn);
?>