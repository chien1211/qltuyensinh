<?php
session_start();

// 1. BẢO MẬT: CHỈ ADMIN HOẶC STAFF
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: ../login.php');
    exit;
}

// 2. NẠP FILE VÀ KẾT NỐI
require '../db_connection.php';
$conn = getDbConnection();
$role = $_SESSION['role'];
$username = $_SESSION['username'];
$current_page = 'duyet-ho-so'; // Vẫn là trang 'Duyệt hồ sơ'
$message = '';
$message_type = '';

// 3. LẤY ID HỒ SƠ TỪ URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: duyet-ho-so.php'); // Nếu không có ID, đá về trang danh sách
    exit;
}
$ho_so_id = (int)$_GET['id'];

// 4. XỬ LÝ KHI CÁN BỘ NHẤN NÚT (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $trang_thai_moi = '';
    $ghi_chu = mysqli_real_escape_string($conn, $_POST['ghi_chu_can_bo']);

    if (isset($_POST['approve'])) {
        $trang_thai_moi = 'hop_le';
        $message = "Đã duyệt hồ sơ HỢP LỆ.";
    } elseif (isset($_POST['reject'])) {
        $trang_thai_moi = 'can_bo_sung';
        $message = "Đã từ chối, yêu cầu CẦN BỔ SUNG.";
    }

    if (!empty($trang_thai_moi)) {
        // Chạy lệnh UPDATE
        $sql_update = "UPDATE ho_so_xet_tuyen SET trang_thai = ?, ghi_chu_can_bo = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ssi", $trang_thai_moi, $ghi_chu, $ho_so_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $message_type = 'success';
            // Duyệt xong, quay về trang danh sách
            header('Location: duyet-ho-so.php?msg=' . urlencode($message));
            exit;
        } else {
            $message = "Lỗi khi cập nhật: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}

// 5. LẤY TẤT CẢ THÔNG TIN CỦA HỒ SƠ NÀY (READ)
// Lại dùng JOIN
$sql_select_one = "
    SELECT 
        hs.*, 
        ts.ho_ten, ts.email, ts.so_cccd, ts.so_dien_thoai, ts.ngay_sinh,
        ng1.ten_nganh AS ten_nganh_1,
        ng2.ten_nganh AS ten_nganh_2
    FROM 
        ho_so_xet_tuyen AS hs
    JOIN 
        thi_sinh AS ts ON hs.thi_sinh_id = ts.id
    LEFT JOIN 
        nganh_hoc AS ng1 ON hs.ma_nganh_1 = ng1.ma_nganh
    LEFT JOIN 
        nganh_hoc AS ng2 ON hs.ma_nganh_2 = ng2.ma_nganh
    WHERE 
        hs.id = ?
";
$stmt_select_one = mysqli_prepare($conn, $sql_select_one);
mysqli_stmt_bind_param($stmt_select_one, "i", $ho_so_id);
mysqli_stmt_execute($stmt_select_one);
$result_one = mysqli_stmt_get_result($stmt_select_one);
$ho_so_chi_tiet = mysqli_fetch_assoc($result_one);

// Nếu không tìm thấy hồ sơ với ID này, đá về
if (!$ho_so_chi_tiet) {
    header('Location: duyet-ho-so.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Chi tiết Hồ sơ - <?php echo htmlspecialchars($ho_so_chi_tiet['ho_ten']); ?></title>
    <style>
        .detail-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 32px; }
        .detail-group { margin-bottom: 16px; }
        .detail-group label { display: block; font-weight: 500; color: var(--text-secondary); font-size: 13px; margin-bottom: 4px; }
        .detail-group p { font-size: 16px; font-weight: 500; color: var(--text-primary); margin: 0; padding: 10px; background-color: var(--secondary-color); border-radius: 6px; }
        .form-actions button.btn-approve { background-color: #16a34a; } /* Màu xanh lá */
        .main-content .form-actions .btn-submit.btn-reject { 
    background-color: #dc2626; /* Màu đỏ */
}
    </style>
</head>
<body class="app-body">

    <?php include 'partials/sidebar.php'; ?>

    <main class="main-content">
        <?php include 'partials/main_header.php'; ?>
        
        <?php if (!empty($message) && $message_type == 'error'): ?>
            <div style="background-color: #fef2f2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="detail-grid">

            <div class="card">
                <h3>Thông tin Thí sinh</h3>
                <div class="detail-group"><label>Họ tên</label><p><?php echo htmlspecialchars($ho_so_chi_tiet['ho_ten']); ?></p></div>
                <div class="detail-group"><label>Email</label><p><?php echo htmlspecialchars($ho_so_chi_tiet['email']); ?></p></div>
                <div class="detail-group"><label>CCCD</label><p><?php echo htmlspecialchars($ho_so_chi_tiet['so_cccd']); ?></p></div>
                <div class="detail-group"><label>Ngày sinh</label><p><?php echo date('d/m/Y', strtotime($ho_so_chi_tiet['ngay_sinh'])); ?></p></div>
                <div class="detail-group"><label>Số điện thoại</label><p><?php echo htmlspecialchars($ho_so_chi_tiet['so_dien_thoai']); ?></p></div>
                
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">
                
                <h3>Thông tin Xét tuyển</h3>
                <div class="detail-group"><label>Nguyện vọng 1</label><p><?php echo htmlspecialchars($ho_so_chi_tiet['ten_nganh_1'] ?? 'Không đăng ký'); ?> (Mã: <?php echo htmlspecialchars($ho_so_chi_tiet['ma_nganh_1']); ?>)</p></div>
                <div class="detail-group"><label>Nguyện vọng 2</label><p><?php echo htmlspecialchars($ho_so_chi_tiet['ten_nganh_2'] ?? 'Không đăng ký'); ?> (Mã: <?php echo htmlspecialchars($ho_so_chi_tiet['ma_nganh_2']); ?>)</p></div>
                
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">

                <h3>Điểm Học bạ Lớp 12</h3>
                <div class="stats-grid" style="gap: 20px;">
                    <div class="detail-group"><label>Toán</label><p><?php echo $ho_so_chi_tiet['diem_toan_12']; ?></p></div>
                    <div class="detail-group"><label>Lý</label><p><?php echo $ho_so_chi_tiet['diem_ly_12']; ?></p></div>
                    <div class="detail-group"><label>Hóa</label><p><?php echo $ho_so_chi_tiet['diem_hoa_12']; ?></p></div>
                    <div class="detail-group"><label>Văn</label><p><?php echo $ho_so_chi_tiet['diem_van_12']; ?></p></div>
                    <div class="detail-group"><label>Anh</label><p><?php echo $ho_so_chi_tiet['diem_anh_12']; ?></p></div>
                </div><hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">
            </div>
            <div class="card"> 
                <h3>Minh chứng Thí sinh Upload</h3>
                <div class="detail-group">
                    <label>File Học bạ THPT</label>
                    <?php if (!empty($ho_so_chi_tiet['path_hoc_ba'])): ?>
                        <p>
                            <a href="../<?php echo htmlspecialchars($ho_so_chi_tiet['path_hoc_ba']); ?>" 
                               target="_blank" class="btn-outline-primary" style="text-decoration: none;">
                                <i class="fas fa-eye"></i> Xem Học bạ
                            </a>
                        </p>
                    <?php else: ?>
                        <p style="color: #dc2626; padding: 10px; background-color: #fef2f2; border-radius: 6px;">
                            <i class="fas fa-times-circle"></i> Thí sinh chưa upload file này.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="detail-group">
                    <label>File Bằng Tốt nghiệp / Giấy CNTN</label>
                    <?php if (!empty($ho_so_chi_tiet['path_bang_tot_nghiep'])): ?>
                        <p>
                            <a href="../<?php echo htmlspecialchars($ho_so_chi_tiet['path_bang_tot_nghiep']); ?>" 
                               target="_blank" class="btn-outline-primary" style="text-decoration: none;">
                                <i class="fas fa-eye"></i> Xem Bằng Tốt nghiệp
                            </a>
                        </p>
                    <?php else: ?>
                        <p style="color: #dc2626; padding: 10px; background-color: #fef2f2; border-radius: 6px;">
                            <i class="fas fa-times-circle"></i> Thí sinh chưa upload file này.
                        </p>
                    <?php endif; ?>
                </div>
            </div> 
            <div class="card">
                <h3 style="margin-bottom: 8px;">Hồ sơ xin duyệt</h3> 
                <p style="color: var(--text-secondary); margin-bottom: 20px;"> Cán bộ ra quyết định và điền ghi chú (nếu cần).
                </p>
                <form action="chi-tiet-ho-so.php?id=<?php echo $ho_so_id; ?>" method="POST">
                    
                    <div class="form-group">
                        <label for="ghi_chu_can_bo">Ghi chú (Bắt buộc nếu từ chối)</label>
                        <textarea id="ghi_chu_can_bo" name="ghi_chu_can_bo" rows="6" class="form-control"><?php echo htmlspecialchars($ho_so_chi_tiet['ghi_chu_can_bo']); ?></textarea>
                    </div>

                    <div class="form-actions" style="display: flex; flex-direction: column; gap: 10px;">
                        <button type="submit" name="approve" class="btn-submit btn-approve" onclick="return confirm('Bạn có chắc chắn muốn DUYỆT HỢP LỆ hồ sơ này?');">
                            <i class="fas fa-check-circle"></i> Duyệt (Hợp lệ)
                        </button>
                        <button type="submit" name="reject" class="btn-submit btn-reject" onclick="return confirm('Bạn có chắc chắn muốn TỪ CHỐI hồ sơ này? (Yêu cầu bổ sung)');">
                            <i class="fas fa-times-circle"></i> Từ chối (Cần Bổ sung)
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </main>
</body>
</html>
<?php
// Đóng kết nối CSDL
mysqli_close($conn);