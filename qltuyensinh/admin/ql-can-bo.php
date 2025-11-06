<?php
session_start();

// 1. BẢO MẬT: CHỈ ADMIN MỚI CÓ QUYỀN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../login.php'); // Đá về trang login
    exit;
}

// 2. NẠP CÁC FILE CẦN THIẾT
require '../db_connection.php'; // Kết nối CSDL
$conn = getDbConnection();

// Lấy thông tin Admin
$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Đặt biến để sidebar biết trang nào đang active
$current_page = 'ql-can-bo';

$message = ''; // Biến để lưu thông báo (thành công hoặc lỗi)
$message_type = ''; // 'success' hoặc 'error'

// ---------------------------------------------------------------
// 3. LOGIC XỬ LÝ FORM (CREATE & DELETE)
// ---------------------------------------------------------------

// --- 3A. XỬ LÝ THÊM MỚI (CREATE) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['them_can_bo'])) {
    $ten_dang_nhap = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mat_khau = mysqli_real_escape_string($conn, $_POST['password']); // Lấy mật khẩu trần
    
    // (Tương lai) Bạn nên validate (kiểm tra rỗng, email hợp lệ,...) ở đây
    
    // Kiểm tra xem username hoặc email đã tồn tại chưa
    $check_sql = "SELECT * FROM users WHERE username = '$ten_dang_nhap' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $message = "Lỗi: Tên đăng nhập hoặc Email đã tồn tại!";
        $message_type = 'error';
    } else {
        // Chèn vào CSDL với role là 'staff' và mật khẩu trần (theo ý bạn)
        $sql_insert = "INSERT INTO users (username, email, password, role) 
                       VALUES ('$ten_dang_nhap', '$email', '$mat_khau', 'staff')";
        
        if (mysqli_query($conn, $sql_insert)) {
            $message = "Thêm cán bộ '$ten_dang_nhap' thành công!";
            $message_type = 'success';
        } else {
            $message = "Lỗi khi thêm cán bộ: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// --- 3B. XỬ LÝ YÊU CẦU XÓA (DELETE) ---
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    
    // Không cho phép admin tự xóa chính mình (dù trang này chỉ hiện 'staff')
    if ($id_to_delete == $_SESSION['user_id']) {
        $message = "Bạn không thể tự xóa chính mình!";
        $message_type = 'error';
    } else {
        $sql_delete = "DELETE FROM users WHERE id = $id_to_delete AND role = 'staff'";
        
        if (mysqli_query($conn, $sql_delete)) {
            if (mysqli_affected_rows($conn) > 0) {
                $message = "Xóa cán bộ thành công!";
                $message_type = 'success';
            } else {
                $message = "Không tìm thấy cán bộ để xóa.";
                $message_type = 'error';
            }
        } else {
            $message = "Lỗi khi xóa cán bộ: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
    // Chuyển hướng lại chính trang này để xóa tham số ?delete_id khỏi URL
    header('Location: ql-can-bo.php?msg=' . urlencode($message) . '&type=' . $message_type);
    exit;
}

// Lấy thông báo từ URL (sau khi xóa)
if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $message_type = $_GET['type'];
}

// ---------------------------------------------------------------
// 4. LOGIC LẤY DỮ LIỆU (READ)
// ---------------------------------------------------------------
// Thay thế "mock data" bằng query thật 100%
$sql_select = "SELECT id, username, email FROM users WHERE role = 'staff'";
$result_select = mysqli_query($conn, $sql_select);

// Chúng ta sẽ lặp qua $result_select ở phần HTML bên dưới

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Quản lý Cán bộ</title>
</head>
<body class="app-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        
        <?php include 'partials/main_header.php'; // File này sẽ tự hiển thị $username và $role ?>

        <div class="card" style="margin-bottom: 32px;">
            <h3>Thêm Cán bộ Tuyển sinh mới</h3>
            
            <?php if (!empty($message)): ?>
                <div style="background-color: <?php echo ($message_type == 'success') ? '#f0fdf4' : '#fef2f2'; ?>; 
                            color: <?php echo ($message_type == 'success') ? '#15803d' : '#b91c1c'; ?>; 
                            padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="ql-can-bo.php" method="POST">
                <input type="hidden" name="them_can_bo" value="1">
                
                <div class="form-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-plus"></i> Thêm Cán bộ
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Danh sách Cán bộ</h3>
            
            <div class="table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên đăng nhập</th>
                            <th>Email</th>
                            <th style="width: 100px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result_select) == 0): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-secondary);">
                                    Chưa có tài khoản cán bộ nào.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php while ($can_bo = mysqli_fetch_assoc($result_select)): ?>
                                <tr>
                                    <td><?php echo $can_bo['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($can_bo['username']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($can_bo['email']); ?></td>
                                    <td>
                                        <a href="ql-can-bo.php?delete_id=<?php echo $can_bo['id']; ?>" 
                                           style="color: var(--danger-color); text-decoration: none;"
                                           onclick="return confirm('Bạn có chắc chắn muốn xóa cán bộ này?');">Xóa</a>
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