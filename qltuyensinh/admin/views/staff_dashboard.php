<?php
// File này sẽ tự động nhận các biến $ho_so_cho_duyet, $ho_so_da_duyet... từ file index.php
?>
<section class="stats-grid">
    <div class="stat-card red">
        <div class="card-content">
            <div class="stat-number"><?php echo $ho_so_cho_duyet; ?></div>
            <div class="stat-label">Hồ sơ chờ duyệt</div>
        </div>
        <div class="card-icon"><i class="fas fa-folder-open"></i></div>
    </div>
    <div class="stat-card green">
        <div class="card-content">
            <div class="stat-number"><?php echo $ho_so_da_duyet; ?></div>
            <div class="stat-label">Hồ sơ đã duyệt</div>
        </div>
        <div class="card-icon"><i class="fas fa-user-check"></i></div>
    </div>
</section>

<div class="card" style="margin-top: 32px;">
    <h3>Các hồ sơ mới nhận</h3>
    <div class="table-container">
        <table class="results-table">
            <thead>
                <tr><th>Mã Hồ Sơ</th><th>Tên Thí Sinh</th><th>Ngày nộp</th><th>Hành động</th></tr>
            </thead>
            <tbody>
                <?php 
                // Biến $result_danh_sach_moi đã được tạo ở file index.php
                if (mysqli_num_rows($result_danh_sach_moi) == 0): 
                ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-secondary);">
                            Chưa có hồ sơ nào mới.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while ($ho_so = mysqli_fetch_assoc($result_danh_sach_moi)): ?>
                        <tr>
                            <td>HS-<?php echo $ho_so['ho_so_id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($ho_so['ho_ten']); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ho_so['ngay_nop'])); ?></td>
                            <td>
                                <a href="chi-tiet-ho-so.php?id=<?php echo $ho_so['ho_so_id']; ?>" 
                                    class="btn-outline-primary">
                                    Xem
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>