<?php
// File này sẽ tự động nhận $role và $current_page từ file index.php
?>
<aside class="sidebar">
    <div class="logo"><img src="../images/logo-doublemint.png" alt="Logo"></div>
    <nav class="nav-menu">
        
        <a href="index.php" class="<?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Tổng quan
        </a>

        <?php if ($role == 'staff'): ?>
            <a href="duyet-ho-so.php" class="<?php echo ($current_page == 'duyet-ho-so') ? 'active' : ''; ?>">
                <i class="fas fa-user-check"></i> Duyệt hồ sơ
            </a>
            <a href="tra-cuu-thi-sinh.php" class="<?php echo ($current_page == 'tra-cuu') ? 'active' : ''; ?>">
                <i class="fas fa-search"></i> Tra cứu thí sinh
            </a>
        <?php endif; ?>

        <?php if ($role == 'admin'): ?>
            <a href="ql-can-bo.php" class="<?php echo ($current_page == 'ql-can-bo') ? 'active' : ''; ?>">
                <i class="fas fa-users-cog"></i> Quản lý Cán bộ
            </a>
            <a href="ql-nganh-hoc.php" class="<?php echo ($current_page == 'ql-nganh-hoc') ? 'active' : ''; ?>">
                <i class="fas fa-book"></i> Quản lý Ngành học
            </a>
            <a href="ql-dot-tuyen-sinh.php" class="<?php echo ($current_page == 'ql-dot-ts') ? 'active' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Đợt tuyển sinh
            </a>
            <a href="xuat-bao-cao.php" class="<?php echo ($current_page == 'bao-cao') ? 'active' : ''; ?>">
                <i class="fas fa-file-export"></i> Xuất báo cáo
            </a>
        <?php endif; ?>
        
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
    </nav>
</aside>