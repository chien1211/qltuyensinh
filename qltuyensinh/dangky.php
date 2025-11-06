<?php
// Bắt đầu session
session_start();

// Nạp file kết nối CSDL
require 'db_connection.php'; //

$error_message = '';
$success_message = '';

// Kiểm tra xem form đã được submit chưa
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Lấy kết nối CSDL
    $conn = getDbConnection();

    // 1. LẤY DỮ LIỆU TỪ FORM (và làm sạch cơ bản)
    $ho_ten = mysqli_real_escape_string($conn, $_POST['ho_ten']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $so_cccd = mysqli_real_escape_string($conn, $_POST['so_cccd']);
    $so_dien_thoai = mysqli_real_escape_string($conn, $_POST['so_dien_thoai']);
    $ngay_sinh = mysqli_real_escape_string($conn, $_POST['ngay_sinh']); // Input type="date" trả về 'YYYY-MM-DD'

    // 2. VALIDATE DỮ LIỆU
    
    // 2.1. Kiểm tra mật khẩu khớp không
    if ($password != $confirm_password) {
        $error_message = "Mật khẩu xác nhận không khớp!";
    } 
    // 2.2. Kiểm tra xem Email hoặc CCCD đã tồn tại chưa
    else {
        $check_sql = "SELECT * FROM thi_sinh WHERE email = '$email' OR so_cccd = '$so_cccd'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $existing_user = mysqli_fetch_assoc($check_result);
            if ($existing_user['email'] == $email) {
                $error_message = "Email này đã được sử dụng. Vui lòng chọn email khác.";
            } else {
                $error_message = "Số CCCD này đã được đăng ký. Mỗi CCCD chỉ được tạo 1 tài khoản.";
            }
        } 
        // 2.3. Mọi thứ OK -> Thêm vào CSDL
        else {
            // (Lưu ý: Vẫn đang lưu mật khẩu trần theo ý bạn)
            $insert_sql = "INSERT INTO thi_sinh (ho_ten, email, password, so_cccd, so_dien_thoai, ngay_sinh) 
                           VALUES ('$ho_ten', '$email', '$password', '$so_cccd', '$so_dien_thoai', '$ngay_sinh')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $success_message = "Đăng ký thành công! Bạn có thể đăng nhập ngay bây giờ.";
                // (Tùy chọn) Chuyển hướng về trang login sau 2 giây
                header("refresh:2;url=login.php");
            } else {
                $error_message = "Lỗi hệ thống: Không thể tạo tài khoản. " . mysqli_error($conn);
            }
        }
    }
    
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản Thí sinh</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">

    <div class="auth-container" style="max-width: 600px;"> 
        <img src="images/logo-doublemint.png" alt="Logo" class="logo">
        <h1>Tạo tài khoản Thí sinh</h1>

        <?php if (!empty($error_message)): ?>
            <div style="background-color: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: left;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success_message)): ?>
            <div style="background-color: #f0fdf4; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: left;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <form id="register-form" method="POST" action="dangky.php">
            
            <div class="input-group">
                <label for="ho_ten">Họ và Tên</label>
                <input type="text" id="ho_ten" name="ho_ten" required>
            </div>
            
            <div class="input-group">
                <label for="email">Email (Dùng để đăng nhập)</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="input-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="input-group">
                <label for="confirm_password">Xác nhận Mật khẩu</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <div class="input-group">
                <label for="so_cccd">Số CCCD (12 số)</label>
                <input type="text" id="so_cccd" name="so_cccd" pattern="[0-9]{12}" title="CCCD phải đủ 12 số" required>
            </div>
            
            <div class="input-group">
                <label for="ngay_sinh">Ngày sinh</label>
                <input type="date" id="ngay_sinh" name="ngay_sinh" required>
            </div>

            <div class="input-group">
                <label for="so_dien_thoai">Số điện thoại</label>
                <input type="tel" id="so_dien_thoai" name="so_dien_thoai" required>
            </div>

            <button type="submit" class="auth-button">Đăng ký</button>

            <p class="auth-link">
                Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
            </p>
        </form>
    </div>

</body>
</html>