<?php
// Bắt đầu session để lưu trạng thái đăng nhập
session_start();

// 1. NẠP FILE KẾT NỐI CSDL CỦA BẠN
require 'db_connection.php'; //

$error_message = '';

// 2. KIỂM TRA NẾU NGƯỜI DÙNG NHẤN NÚT "ĐĂNG NHẬP"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Lấy kết nối CSDL (Dùng hàm của bạn)
    $conn = getDbConnection();

    // Lấy dữ liệu từ form
    $role = $_POST['role-selector'];
    $username_or_email = $_POST['username']; // Field này giờ là email (khi role=student) hoặc username
    $password = $_POST['password']; // Lấy mật khẩu trần (không hash)

    // 3. LOGIC RẼ NHÁNH MỚI
    
    if ($role == 'student') {
        // ========== PHẦN MỚI CHO THÍ SINH ==========
        // "Tên đăng nhập" thực ra là email của họ
        $email = $username_or_email; 
        
        $sql_student = "SELECT * FROM thi_sinh WHERE email = ? AND password = ?";
        $stmt_student = mysqli_prepare($conn, $sql_student);
        
        if ($stmt_student) {
            mysqli_stmt_bind_param($stmt_student, "ss", $email, $password);
            mysqli_stmt_execute($stmt_student);
            $result_student = mysqli_stmt_get_result($stmt_student);

            if (mysqli_num_rows($result_student) == 1) {
                // ĐĂNG NHẬP THÍ SINH THÀNH CÔNG!
                $student = mysqli_fetch_assoc($result_student);
                
                // Tạo session cho Thí sinh
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['student_name'] = $student['ho_ten'];
                $_SESSION['role'] = 'student'; // Cực kỳ quan trọng

                // Chuyển hướng đến trang chủ của thí sinh
                header('Location: student_dashboard.php'); // (File này chúng ta sẽ tạo ở bước 2)
                exit;
            } else {
                // Đăng nhập thí sinh thất bại
                $error_message = "Email hoặc mật khẩu không chính xác!";
            }
            mysqli_stmt_close($stmt_student);
        }
        // ========== HẾT PHẦN MỚI ==========
        
    } else {
        // PHẦN CŨ: VAI TRÒ LÀ 'admin' hoặc 'staff'
        $username = $username_or_email; // Giữ nguyên là username
        
        $sql = "SELECT * FROM users WHERE username = ? AND password = ? AND role = ?";
        $stmt = mysqli_prepare($conn, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $username, $password, $role);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) == 1) {
                // ĐĂNG NHẬP ADMIN/STAFF THÀNH CÔNG!
                $user = mysqli_fetch_assoc($result);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; // 'admin' hoặc 'staff'
                
                header('Location: admin/index.php'); 
                exit;
            } else {
                $error_message = "Tên đăng nhập, mật khẩu hoặc vai trò không chính xác!";
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_message = "Lỗi hệ thống: Không thể chuẩn bị câu lệnh.";
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
    <title>Đăng nhập hệ thống</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <img src="images/logo-doublemint.png" alt="Logo Trường Học" class="logo">
        <h1>Đăng nhập tài khoản</h1>

        <?php
        if (!empty($error_message)) {
            echo '<div style="background-color: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: center;">'
                 . $error_message .
                 '</div>';
        }
        ?>

        <form id="login-form" method="POST" action="login.php">
            <div class="input-group role-selector-group">
                <label for="role-selector">Vai trò</label>
                <i class="fas fa-user-shield icon"></i>
                <select id="role-selector" name="role-selector">
                    <option value="student">Thí sinh</option>
                    <option value="staff">Cán bộ</option>
                    <option value="admin">Quản trị viên</option> 
                </select>
            </div>

            <div class="input-group">
                <label for="username">Tên đăng nhập</label>
                <input type="text" id="username" name="username" placeholder="Nhập tên đăng nhập" required>
            </div>

            <div class="input-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>

            <button type="submit" class="auth-button">Đăng nhập</button>

            <p class="auth-link">
                <a href="forgot-password.php">Quên mật khẩu?</a> | 
                <a href="dangky.php">Đăng ký tài khoản</a>
            </p>
        </form>
    </div>

    <script>
        const roleSelector = document.getElementById('role-selector');
        const roleIcon = document.querySelector('.role-selector-group .icon');
        
        // Lấy thêm label và input của "username"
        const usernameLabel = document.querySelector('label[for="username"]');
        const usernameInput = document.getElementById('username');

        // Hàm cập nhật toàn bộ form (icon + text)
        function updateFormForRole() {
            const selectedValue = roleSelector.value;
            switch (selectedValue) {
                case 'staff':
                    roleIcon.className = 'fas fa-chalkboard-teacher icon';
                    usernameLabel.textContent = 'Tên đăng nhập'; // Đổi text
                    usernameInput.placeholder = 'Nhập tên đăng nhập'; // Đổi placeholder
                    break;
                case 'admin':
                    roleIcon.className = 'fas fa-user-shield icon';
                    usernameLabel.textContent = 'Tên đăng nhập';
                    usernameInput.placeholder = 'Nhập tên đăng nhập';
                    break;
                case 'student':
                default:
                    roleIcon.className = 'fas fa-user-graduate icon';
                    usernameLabel.textContent = 'Email (Dùng để đăng nhập)'; // Đổi text
                    usernameInput.placeholder = 'Nhập email của bạn'; // Đổi placeholder
                    break;
            }
        }

        // Gắn sự kiện 'change' vào
        roleSelector.addEventListener('change', updateFormForRole);

        // Chạy 1 lần khi tải trang để icon và text khớp với giá trị đã chọn
        document.addEventListener('DOMContentLoaded', updateFormForRole);
    </script>

</body>
</html>