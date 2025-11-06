<?php
session_start();

// 1. BẢO MẬT: CHỈ ADMIN HOẶC STAFF
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: ../login.php');
    exit;
}

// 2. NẠP FILE VÀ KẾT NỐI
require '../db_connection.php';
$conn = getDbConnection();
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$current_page = 'tra-cuu'; // Để highlight sidebar

// ---------------------------------------------------------------
// 3. LOGIC LẤY DANH SÁCH HỒ SƠ LƯU TRỮ (READ)
// ---------------------------------------------------------------
// Câu query này y hệt trang "Duyệt",
// CHỈ KHÁC câu lệnh WHERE

$sql_select = "
    SELECT 
        hs.id AS ho_so_id,
        ts.ho_ten,
        ts.so_cccd,
        hs.ngay_nop,
        hs.trang_thai, -- Lấy cột trạng thái để hiển thị
        ng1.ten_nganh AS ten_nganh_1
    FROM 
        ho_so_xet_tuyen AS hs
    JOIN 
        thi_sinh AS ts ON hs.thi_sinh_id = ts.id
    LEFT JOIN 
        nganh_hoc AS ng1 ON hs.ma_nganh_1 = ng1.ma_nganh
    WHERE 
        hs.trang_thai IN ('hop_le', 'can_bo_sung')  -- << ĐÂY LÀ KHÁC BIỆT CHÍNH
    ORDER BY 
        hs.ngay_nop DESC; -- Hồ sơ mới duyệt xếp lên đầu
";

$result_select = mysqli_query($conn, $sql_select);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Tra cứu Hồ sơ (Lịch sử)</title>
    <style>
        .status-badge.hop-le { color: #15803d; background-color: #f0fdf4; }
        .status-badge.can-bo-sung { color: #b45309; background-color: #fffbeb; }
    </style>
</head>
<body class="app-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        
        <?php include 'partials/main_header.php'; ?>

        <div class="card">
            <h3>Lịch sử Hồ sơ đã xử lý</h3>
            <p style="color: var(--text-secondary); margin: 5px 0 20px 0;">
                Danh sách tất cả hồ sơ đã được "Duyệt" hoặc "Từ chối".
            </p>
            
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Họ tên Thí sinh</th>
                            <th>CCCD</th>
                            <th>Nguyện vọng 1</th>
                            <th>Ngày nộp</th>
                            <th>Trạng thái</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_select) == 0): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: var(--text-secondary);">
                                    Chưa có hồ sơ nào được xử lý.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($ho_so = mysqli_fetch_assoc($result_select)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($ho_so['ho_ten']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($ho_so['so_cccd']); ?></td>
                                    <td><?php echo htmlspecialchars($ho_so['ten_nganh_1'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($ho_so['ngay_nop'])); ?></td>
                                    <td>
                                        <?php if ($ho_so['trang_thai'] == 'hop_le'): ?>
                                            <span class="status-badge hop-le">Hợp lệ</span>
                                        <?php elseif ($ho_so['trang_thai'] == 'can_bo_sung'): ?>
                                            <span class="status-badge can-bo-sung">Cần Bổ sung</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="chi-tiet-ho-so.php?id=<?php echo $ho_so['ho_so_id']; ?>" 
                                           class="btn-outline-primary" style="text-decoration: none;">
                                            Xem lại
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