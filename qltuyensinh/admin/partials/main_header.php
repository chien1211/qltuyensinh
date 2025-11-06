<?php
// File này sẽ tự động nhận $role và $username từ file index.php
?>
<header class="main-header student-header"> 
    <div class="welcome-text">
        <h1><?php echo ($role == 'admin') ? 'Bảng điều khiển Quản trị' : 'Bảng xét đơn xét tuyển'; ?></h1>
        <p><?php echo ($role == 'admin') ? 'Tổng quan hoạt động của hệ thống.' : 'Xét duyệt hồ sơ.'; ?></p>
    </div>
    <div class="header-actions">
        <i class="fas fa-search"></i>
        <i class="fas fa-bell"></i>
        <div class="user-profile">
            <span><?php echo htmlspecialchars($username); ?></span> 
            <small><?php echo ($role == 'admin') ? 'Quản trị viên' : 'Cán bộ Tuyển sinh'; ?></small>
        </div>
    </div>
</header>