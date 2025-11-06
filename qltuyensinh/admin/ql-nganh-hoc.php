<?php
session_start();
// 1. BẢO MẬT: CHỈ ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php');
    exit;
}

// 2. NẠP FILE VÀ KẾT NỐI
require '../db_connection.php';
$conn = getDbConnection();
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$current_page = 'ql-nganh-hoc'; // Để highlight sidebar
$message = '';
$message_type = '';

// 3. LOGIC XỬ LÝ FORM (THÊM / XÓA)

// --- 3A. XỬ LÝ THÊM MỚI (CREATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_nganh'])) {
    $ma_nganh = mysqli_real_escape_string($conn, $_POST['ma_nganh']);
    $ten_nganh = mysqli_real_escape_string($conn, $_POST['ten_nganh']);
    $khoi_xet_tuyen = mysqli_real_escape_string($conn, $_POST['khoi_xet_tuyen']);
    $chi_tieu = (int)$_POST['chi_tieu'];
    
    // Kiểm tra trùng mã ngành
    $check_sql = "SELECT * FROM nganh_hoc WHERE ma_nganh = '$ma_nganh'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "Lỗi: Mã ngành '$ma_nganh' đã tồn tại!";
        $message_type = 'error';
    } else {
        $sql_insert = "INSERT INTO nganh_hoc (ma_nganh, ten_nganh, khoi_xet_tuyen, chi_tieu) 
                       VALUES ('$ma_nganh', '$ten_nganh', '$khoi_xet_tuyen', $chi_tieu)";
        
        if (mysqli_query($conn, $sql_insert)) {
            $message = "Thêm ngành học '$ten_nganh' thành công!";
            $message_type = 'success';
        } else {
            $message = "Lỗi khi thêm: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// --- 3B. XỬ LÝ YÊU CẦU XÓA (DELETE) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    $sql_delete = "DELETE FROM nganh_hoc WHERE id = $id_to_delete";
    
    if (mysqli_query($conn, $sql_delete)) {
        $message = "Xóa ngành học thành công!";
        $message_type = 'success';
    } else {
        $message = "Lỗi khi xóa: " . mysqli_error($conn);
        $message_type = 'error';
    }
    header('Location: ql-nganh-hoc.php?msg=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

if (isset($_GET['msg'])) { $message = $_GET['msg']; $message_type = $_GET['type']; }

// 4. LOGIC LẤY DỮ LIỆU (READ)
$sql_select = "SELECT * FROM nganh_hoc";
$result_select = mysqli_query($conn, $sql_select);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Quản lý Ngành học</title>
</head>
<body class="app-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'partials/main_header.php'; ?>
        
        <div class="card" style="margin-bottom: 32px;">
            <h3>Thêm Ngành học/Nguyện vọng mới</h3>
            
            <?php if (!empty($message)): ?>
                <div style="background-color: <?php echo ($message_type == 'success') ? '#f0fdf4' : '#fef2f2'; ?>; 
                            color: <?php echo ($message_type == 'success') ? '#15803d' : '#b91c1c'; ?>; 
                            padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="ql-nganh-hoc.php" method="POST">
                <input type="hidden" name="them_nganh" value="1">
                <div class="stats-grid" style="gap: 20px;"> <div class="form-group">
                        <label for="ma_nganh">Mã ngành (vd: IT1)</label>
                        <input type="text" id="ma_nganh" name="ma_nganh" required>
                    </div>
                    <div class="form-group">
                        <label for="ten_nganh">Tên ngành (vd: Công nghệ thông tin)</label>
                        <input type="text" id="ten_nganh" name="ten_nganh" required>
                    </div>
                    <div class="form-group">
                        <label for="khoi_xet_tuyen">Khối xét tuyển (vd: A00, A01)</label>
                        <input type="text" id="khoi_xet_tuyen" name="khoi_xet_tuyen">
                    </div>
                    <div class="form-group">
                        <label for="chi_tieu">Chỉ tiêu</label>
                        <input type="number" id="chi_tieu" name="chi_tieu" value="100">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-plus"></i> Thêm Ngành</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Danh sách Ngành học</h3>
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Mã ngành</th>
                            <th>Tên ngành</th>
                            <th>Khối xét tuyển</th>
                            <th>Chỉ tiêu</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_select) == 0): ?>
                            <tr><td colspan="5" style="text-align: center;">Chưa có ngành học nào.</td></tr>
                        <?php else: ?>
                            <?php while ($nganh = mysqli_fetch_assoc($result_select)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($nganh['ma_nganh']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($nganh['ten_nganh']); ?></td>
                                    <td><?php echo htmlspecialchars($nganh['khoi_xet_tuyen']); ?></td>
                                    <td><?php echo htmlspecialchars($nganh['chi_tieu']); ?></td>
                                    <td>
                                        <a href="ql-nganh-hoc.php?delete_id=<?php echo $nganh['id']; ?>" 
                                           style="color: var(--danger-color);" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa ngành này?');">Xóa</a>
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
<?php mysqli_close($conn); ?>