<?php
$message = '';
// Chúng ta vẫn bắt sự kiện POST, nhưng chỉ để hiển thị một thông báo "demo"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    
    // FAKE LOGIC: Chỉ giả vờ là đã gửi
    $message = "OK! Nếu email " . htmlspecialchars($email) . " tồn tại trong hệ thống, chúng tôi đã gửi link khôi phục đến đó. (Đây là demo)";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="auth-body"> <div class="auth-container"> <img src="images/logo-doublemint.png" alt="Logo" class="logo">
        <h1>Khôi Phục Mật Khẩu</h1>
        
        <p style="color: var(--text-secondary); margin-bottom: 20px; text-align: left;">
            Nhập email đã đăng ký. Chúng tôi sẽ gửi cho bạn một liên kết (giả) để đặt lại mật khẩu.
        </p>

        <?php if (!empty($message)): ?>
            <div style="background-color: #f0fdf4; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 16px; text-align: left;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form action="forgot-password.php" method="POST">
            <div class="input-group"> <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Nhập email của bạn" required>
            </div>
            
            <button type="submit" class="auth-button"> Gửi link khôi phục
            </button>

            <p class="auth-link"> Đã nhớ ra? <a href="login.php">Quay lại Đăng nhập</a>
            </p>
        </form>
    </div>

</body>
</html>