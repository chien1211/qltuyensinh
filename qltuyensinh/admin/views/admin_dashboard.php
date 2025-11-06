<?php
// File này sẽ tự động nhận các biến $so_can_bo, $so_thi_sinh... từ file index.php
?>
<section class="stats-grid">
    <div class="stat-card blue">
        <div class="card-content">
            <div class="stat-number"><?php echo $so_can_bo; ?></div>
            <div class="stat-label">Cán bộ Tuyển sinh</div>
        </div>
        <div class="card-icon"><i class="fas fa-users-cog"></i></div>
    </div>
    <div class="stat-card green">
        <div class="card-content">
            <div class="stat-number"><?php echo $so_thi_sinh; ?></div>
            <div class="stat-label">Tổng Thí sinh ĐK</div>
        </div>
        <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
    </div>
    <div class="stat-card orange">
        <div class="card-content">
            <div class="stat-number"><?php echo $so_ho_so_moi; ?></div>
            <div class="stat-label">Hồ sơ mới chờ duyệt</div>
        </div>
        <div class="card-icon"><i class="fas fa-folder-open"></i></div>
    </div>
    <div class="stat-card red">
        <div class="card-content">
            <div class="stat-number"><?php echo $so_nganh_hoc; ?></div>
            <div class="stat-label">Ngành đào tạo</div>
        </div>
        <div class="card-icon"><i class="fas fa-book"></i></div>
    </div>
</section>