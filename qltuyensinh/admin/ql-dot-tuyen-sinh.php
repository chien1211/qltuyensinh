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
$current_page = 'ql-dot-ts'; // Để highlight sidebar
$message = '';
$message_type = '';

// 3. LOGIC XỬ LÝ FORM (THÊM / XÓA / BẬT/TẮT)

// --- 3A. XỬ LÝ THÊM MỚI (CREATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_dot'])) {
    $ten_dot = mysqli_real_escape_string($conn, $_POST['ten_dot']);
    $ngay_bat_dau = mysqli_real_escape_string($conn, $_POST['ngay_bat_dau']);
    $ngay_ket_thuc = mysqli_real_escape_string($conn, $_POST['ngay_ket_thuc']);
    
    // Mặc định khi thêm mới là 'da_dong'
    $sql_insert = "INSERT INTO dot_tuyen_sinh (ten_dot, ngay_bat_dau, ngay_ket_thuc, trang_thai) 
                   VALUES ('$ten_dot', '$ngay_bat_dau', '$ngay_ket_thuc', 'da_dong')";
    
    if (mysqli_query($conn, $sql_insert)) {
        $message = "Thêm đợt tuyển sinh thành công!";
        $message_type = 'success';
    } else {
        $message = "Lỗi khi thêm: " . mysqli_error($conn);
        $message_type = 'error';
    }
}

// --- 3B. XỬ LÝ YÊU CẦU XÓA (DELETE) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    $sql_delete = "DELETE FROM dot_tuyen_sinh WHERE id = $id_to_delete";
    if (mysqli_query($conn, $sql_delete)) $message = "Xóa đợt thành công!"; else $message = "Lỗi khi xóa";
    $message_type = mysqli_affected_rows($conn) > 0 ? 'success' : 'error';
    header('Location: ql-dot-tuyen-sinh.php?msg=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

// --- 3C. XỬ LÝ BẬT/TẮT (UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['toggle_id'])) {
    $id_to_toggle = (int)$_GET['toggle_id'];
    $current_status = mysqli_real_escape_string($conn, $_GET['status']);
    
    $new_status = ($current_status == 'dang_mo') ? 'da_dong' : 'dang_mo';
    
    // Logic: Chỉ cho phép 1 đợt 'dang_mo' tại một thời điểm
    if ($new_status == 'dang_mo') {
        // Trước khi MỞ 1 đợt, ĐÓNG tất cả các đợt khác lại
        mysqli_query($conn, "UPDATE dot_tuyen_sinh SET trang_thai = 'da_dong'");
    }
    
    // Cập nhật đợt được chọn
    $sql_toggle = "UPDATE dot_tuyen_sinh SET trang_thai = '$new_status' WHERE id = $id_to_toggle";
    if (mysqli_query($conn, $sql_toggle)) {
        $message = "Cập nhật trạng thái thành công!";
        $message_type = 'success';
    } else {
        $message = "Lỗi khi cập nhật";
        $message_type = 'error';
    }
    header('Location: ql-dot-tuyen-sinh.php?msg=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

if (isset($_GET['msg'])) { $message = $_GET['msg']; $message_type = $_GET['type']; }

// 4. LOGIC LẤY DỮ LIỆU (READ)
$sql_select = "SELECT * FROM dot_tuyen_sinh ORDER BY ngay_bat_dau DESC";
$result_select = mysqli_query($conn, $sql_select);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Quản lý Đợt Tuyển sinh</title>
    <style>
        .btn-toggle { padding: 8px 12px; text-decoration: none; border-radius: 6px; color: #fff; font-weight: 500; }
        .btn-toggle.on { background-color: #16a34a; } /* Xanh lá */
        .btn-toggle.off { background-color: #dc2626; } /* Đỏ */
    </style>
</head>
<body class="app-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'partials/main_header.php'; ?>
        
        <div class="card" style="margin-bottom: 32px;">
            <h3>Thêm Đợt Tuyển sinh mới</h3>
            
            <?php if (!empty($message)): ?>
                <div style="background-color: <?php echo ($message_type == 'success') ? '#f0fdf4' : '#fef2f2'; ?>; 
                            color: <?php echo ($message_type == 'success') ? '#15803d' : '#b91c1c'; ?>; 
                            padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="ql-dot-tuyen-sinh.php" method="POST">
                <input type="hidden" name="them_dot" value="1">
                <div class.form-group">
                    <label for="ten_dot">Tên đợt (vd: Đợt 1 - Xét Học bạ 2026)</label>
                    <input type="text" id="ten_dot" name="ten_dot" required>
                </div>
                <div class="stats-grid" style="gap: 20px;">
                    <div class.form-group">
                        <label for="ngay_bat_dau">Ngày bắt đầu</label>
                        <input type="date" id="ngay_bat_dau" name="ngay_bat_dau" required>
                    </div>
                    <div class.form-group">
                        <label for="ngay_ket_thuc">Ngày kết thúc</label>
                        <input type="date" id="ngay_ket_thuc" name="ngay_ket_thuc" required>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-plus"></i> Thêm Đợt</button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Danh sách Đợt Tuyển sinh</h3>
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Tên đợt</th>
                            <th>Ngày bắt đầu</th>
                            <th>Ngày kết thúc</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_select) == 0): ?>
                            <tr><td colspan="5" style="text-align: center;">Chưa có đợt tuyển sinh nào.</td></tr>
                        <?php else: ?>
                            <?php while ($dot = mysqli_fetch_assoc($result_select)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($dot['ten_dot']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($dot['ngay_bat_dau'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($dot['ngay_ket_thuc'])); ?></td>
                                    <td>
                                        <?php if ($dot['trang_thai'] == 'dang_mo'): ?>
                                            <a href="ql-dot-tuyen-sinh.php?toggle_id=<?php echo $dot['id']; ?>&status=dang_mo" 
                                               class="btn-toggle on" onclick="return confirm('Bạn có chắc muốn TẮT đợt này?');">
                                               <i class="fas fa-toggle-on"></i> Đang Mở
                                            </a>
                                        <?php else: ?>
                                            <a href="ql-dot-tuyen-sinh.php?toggle_id=<?php echo $dot['id']; ?>&status=da_dong" 
                                               class="btn-toggle off" onclick="return confirm('Bạn có chắc muốn BẬT đợt này? (Các đợt khác sẽ bị tắt)');">
                                               <i class="fas fa-toggle-off"></i> Đã Đóng
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="ql-dot-tuyen-sinh.php?delete_id=<?php echo $dot['id']; ?>" 
                                           style="color: var(--danger-color);" 
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa đợt này?');">Xóa</a>
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