<?php
// Bắt đầu session
session_start();

// 1. BẢO VỆ: Kiểm tra xem có phải 'student' đã đăng nhập không
if (!isset($_SESSION['student_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit;
}

// 2. NẠP FILE VÀ LẤY KẾT NỐI CSDL
require 'db_connection.php';
$conn = getDbConnection();

// 3. LẤY ID CỦA THÍ SINH ĐANG ĐĂNG NHẬP
$thi_sinh_id = (int)$_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// 4. LẤY THÔNG TIN HỒ SƠ
// Chúng ta chỉ cần 3 cột: trạng thái, ngày nộp, và ghi chú
$sql_check = "SELECT trang_thai, ngay_nop, ghi_chu_can_bo FROM ho_so_xet_tuyen WHERE thi_sinh_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $thi_sinh_id);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);
$ho_so = mysqli_fetch_assoc($result);

// 5. ĐÓNG KẾT NỐI
mysqli_close($conn);

// 6. Đặt biến active cho sidebar
$current_page = 'xem-trang-thai';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tra cứu Trạng thái Hồ sơ</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .status-card {
            padding: 24px;
            border-radius: 12px;
            text-align: center;
            border-width: 1px;
            border-style: solid;
        }
        .status-card .icon {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .status-card h3 {
            font-size: 24px;
            margin: 0;
        }
        .status-card p {
            font-size: 16px;
            color: var(--text-secondary);
            margin-top: 8px;
        }
        
        /* Màu Xanh: Hợp lệ */
        .status-card.success {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
        }
        /* Màu Đỏ/Vàng: Cần bổ sung */
        .status-card.warning {
            background-color: #fffbeb;
            border-color: #fde68a;
            color: #b45309;
        }
        /* Màu Xám: Chờ */
        .status-card.pending {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            color: #334155;
        }
        
        .ghi-chu-can-bo {
            margin-top: 24px;
            padding: 16px;
            background-color: #f1f5f9;
            border-radius: 8px;
            text-align: left;
        }
        .ghi-chu-can-bo strong {
            display: block;
            margin-bottom: 8px;
            color: var(--text-primary);
        }
    </style>
</head>
<body class="app-body">

    <aside class="sidebar">
        <div class="logo"><img src="images/logo-doublemint.png" alt="Logo"></div>
        <nav class="nav-menu">
            <a href="student_dashboard.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>"><i class="fas fa-home"></i> Trang chủ</a>
            <a href="nop-ho-so.php" class="<?php echo ($current_page == 'nop-ho-so') ? 'active' : ''; ?>"><i class="fas fa-file-alt"></i> Nộp Hồ sơ</a>
            <a href="xem-trang-thai.php" class="<?php echo ($current_page == 'xem-trang-thai') ? 'active' : ''; ?>"><i class="fas fa-search"></i> Tra cứu Hồ sơ</a>
            <a href="doi-mat-khau.php" class="<?php echo ($current_page == 'doi-mat-khau') ? 'active' : ''; ?>"><i class="fas fa-key"></i> Đổi Mật khẩu</a>
            <a href="admin/logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="main-header student-header"> 
            <div class="welcome-text">
                <h1>Tra cứu Trạng thái Hồ sơ</h1>
                <p>Xin chào, <?php echo htmlspecialchars($student_name); ?>. Đây là kết quả hồ sơ của bạn.</p>
            </div>
        </header>

        <div class="card">
            <?php if (!$ho_so || in_array($ho_so['trang_thai'], ['chua_luu', 'da_luu'])): ?>
                <div class="status-card pending">
                    <i class="fas fa-file-alt icon"></i>
                    <h3>Chưa nộp hồ sơ</h3>
                    <p>Bạn chưa hoàn tất việc nộp hồ sơ. Vui lòng vào mục "Nộp Hồ sơ" để hoàn tất.</p>
                </div>
                
            <?php elseif ($ho_so['trang_thai'] == 'da_nop'): ?>
                <div class="status-card pending">
                    <i class="fas fa-hourglass-half icon"></i>
                    <h3>Đã nộp - Chờ duyệt</h3>
                    <p>Bạn đã nộp hồ sơ thành công vào lúc <?php echo date('d/m/Y H:i', strtotime($ho_so['ngay_nop'])); ?>. <br>
                    Hồ sơ của bạn đang được Cán bộ tuyển sinh xem xét.</p>
                </div>

            <?php elseif ($ho_so['trang_thai'] == 'hop_le'): ?>
                <div class="status-card success">
                    <i class="fas fa-check-circle icon"></i>
                    <h3>Hồ sơ HỢP LỆ</h3>
                    <p>Chúc mừng! Hồ sơ của bạn đã được duyệt và hợp lệ.</p>
                    
                    <?php if (!empty($ho_so['ghi_chu_can_bo'])): ?>
                    <div class="ghi-chu-can-bo">
                        <strong><i class="fas fa-sticky-note"></i> Ghi chú từ Cán bộ Tuyển sinh:</strong>
                        <?php echo nl2br(htmlspecialchars($ho_so['ghi_chu_can_bo'])); ?>
                    </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($ho_so['trang_thai'] == 'can_bo_sung'): ?>
                <div class="status-card warning">
                    <i class="fas fa-exclamation-triangle icon"></i>
                    <h3>Hồ sơ CẦN BỔ SUNG</h3>
                    <p>Hồ sơ của bạn bị từ chối do thiếu thông tin. Vui lòng xem ghi chú bên dưới.</p>
                    
                    <?php if (!empty($ho_so['ghi_chu_can_bo'])): ?>
                    <div class="ghi-chu-can-bo">
                        <strong><i class="fas fa-sticky-note"></i> Ghi chú bắt buộc từ Cán bộ:</strong>
                        <?php echo nl2br(htmlspecialchars($ho_so['ghi_chu_can_bo'])); ?>
                    </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
            
        </div>
    </main>

</body>
</html>