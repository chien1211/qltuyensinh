<?php
session_start();

// 1. BẢO VỆ
if (!isset($_SESSION['student_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit;
}

// 2. KẾT NỐI CSDL
require 'db_connection.php';
$conn = getDbConnection();

// 3. LẤY THÔNG TIN
$thi_sinh_id = (int)$_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$message = '';
$message_type = '';

// 4. KIỂM TRA HỒ SƠ (Code cũ, vẫn chạy tốt)
$sql_check = "SELECT * FROM ho_so_xet_tuyen WHERE thi_sinh_id = ?";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "i", $thi_sinh_id);
mysqli_stmt_execute($stmt_check);
$result = mysqli_stmt_get_result($stmt_check);
$ho_so = mysqli_fetch_assoc($result);

// 4.1. Tạo hồ sơ rỗng (Code cũ, vẫn chạy tốt)
if (!$ho_so) {
    $sql_insert_new = "INSERT INTO ho_so_xet_tuyen (thi_sinh_id, trang_thai) VALUES (?, 'chua_luu')";
    $stmt_insert = mysqli_prepare($conn, $sql_insert_new);
    mysqli_stmt_bind_param($stmt_insert, "i", $thi_sinh_id);
    mysqli_stmt_execute($stmt_insert);
    $ho_so_id = mysqli_insert_id($conn);
    $ho_so = [ 'id' => $ho_so_id, 'thi_sinh_id' => $thi_sinh_id, 'ma_nganh_1' => '', 'ma_nganh_2' => '', 'diem_toan_12' => null, 'diem_ly_12' => null, 'diem_hoa_12' => null, 'diem_van_12' => null, 'diem_anh_12' => null, 'trang_thai' => 'chua_luu', 'ngay_nop' => null, 'ghi_chu_can_bo' => null, 'path_hoc_ba' => null, 'path_bang_tot_nghiep' => null ];
    $message = "Tạo hồ sơ mới thành công. Vui lòng điền thông tin.";
    $message_type = 'success';
}

// 4.2. LẤY DANH SÁCH NGÀNH HỌC (Code cũ, vẫn chạy tốt)
$sql_nganh = "SELECT ma_nganh, ten_nganh FROM nganh_hoc WHERE trang_thai = 'dang_tuyen'";
$result_nganh = mysqli_query($conn, $sql_nganh);
$danh_sach_nganh = [];
if ($result_nganh) { while ($row = mysqli_fetch_assoc($result_nganh)) { $danh_sach_nganh[] = $row; } }

// 4.3. KIỂM TRA "CÔNG TẮC" TUYỂN SINH (Code cũ, vẫn chạy tốt)
$sql_check_dot = "SELECT * FROM dot_tuyen_sinh WHERE trang_thai = 'dang_mo' AND CURDATE() >= ngay_bat_dau AND CURDATE() <= ngay_ket_thuc LIMIT 1";
$result_check_dot = mysqli_query($conn, $sql_check_dot);
$dot_tuyen_sinh_mo = mysqli_fetch_assoc($result_check_dot);

// -------------------------------------------------------------------
// 5. LOGIC KHÓA FORM (ĐÃ THAY ĐỔI)
// -------------------------------------------------------------------
// Biến kiểm soát MỚI: Chỉ khóa khi đang chờ duyệt ('da_nop') hoặc đã Hợp lệ ('hop_le')
// Nếu là 'can_bo_sung', $is_locked sẽ là FALSE -> Form được MỞ
$is_locked = in_array($ho_so['trang_thai'], ['da_nop', 'hop_le']);


// 6. XỬ LÝ POST (Đã nâng cấp)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$is_locked && $dot_tuyen_sinh_mo) {
    $ho_so_id_to_update = $ho_so['id'];

    // 6.1. NẾU NHẤN NÚT "NỘP HỒ SƠ"
    if (isset($_POST['nop_ho_so'])) {
        // (Xử lý upload file trước khi nộp - logic này là từ bước trước)
        $path_hoc_ba_moi = handleFileUpload('file_hoc_ba', $thi_sinh_id, 'hocba');
        $path_bang_tot_nghiep_moi = handleFileUpload('file_bang_tot_nghiep', $thi_sinh_id, 'bangtotnghiep');
        $final_path_hoc_ba = $path_hoc_ba_moi ? $path_hoc_ba_moi : $ho_so['path_hoc_ba'];
        $final_path_bang_tot_nghiep = $path_bang_tot_nghiep_moi ? $path_bang_tot_nghiep_moi : $ho_so['path_bang_tot_nghiep'];

        // (Lưu các thông tin khác)
        $ma_nganh_1 = mysqli_real_escape_string($conn, $_POST['ma_nganh_1']);
        $ma_nganh_2 = mysqli_real_escape_string($conn, $_POST['ma_nganh_2']);
        $diem_toan_12 = (float)$_POST['diem_toan_12'];
        // ... (các điểm khác)
        $diem_anh_12 = (float)$_POST['diem_anh_12'];

        // CHẠY LỆNH "CHỐT" HỒ SƠ (Cả nộp mới và nộp lại đều về 'da_nop')
        $sql_submit = "UPDATE ho_so_xet_tuyen SET 
                            ma_nganh_1 = ?, ma_nganh_2 = ?, 
                            diem_toan_12 = ?, diem_ly_12 = ?, diem_hoa_12 = ?, 
                            diem_van_12 = ?, diem_anh_12 = ?, 
                            path_hoc_ba = ?, path_bang_tot_nghiep = ?,
                            trang_thai = 'da_nop', -- Chuyển về trạng thái chờ duyệt
                            ngay_nop = NOW(), -- Cập nhật ngày nộp mới
                            ghi_chu_can_bo = NULL -- Xóa ghi chú cũ
                       WHERE id = ? AND thi_sinh_id = ?";
        
        $stmt_submit = mysqli_prepare($conn, $sql_submit);
        mysqli_stmt_bind_param($stmt_submit, "ssdddddssii",
            $ma_nganh_1, $ma_nganh_2, 
            $diem_toan_12, $diem_ly_12, $diem_hoa_12, 
            $diem_van_12, $diem_anh_12,
            $final_path_hoc_ba, $final_path_bang_tot_nghiep,
            $ho_so_id_to_update, $thi_sinh_id
        );
        
        if (mysqli_stmt_execute($stmt_submit)) {
            $message = "Nộp hồ sơ thành công! Hồ sơ của bạn đã được gửi đi.";
            $message_type = 'success';
            // Tải lại trạng thái mới
            $ho_so = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM ho_so_xet_tuyen WHERE id = $ho_so_id_to_update"));
            $is_locked = true; // Khóa form lại vì vừa nộp
        } else {
            $message = "Lỗi khi nộp hồ sơ: " . mysqli_error($conn);
            $message_type = 'error';
        }
    
    // 6.2. NẾU NHẤN NÚT "LƯU NHÁP"
    } elseif (isset($_POST['luu_nhap'])) {
        // (Xử lý upload file)
        $path_hoc_ba_moi = handleFileUpload('file_hoc_ba', $thi_sinh_id, 'hocba');
        $path_bang_tot_nghiep_moi = handleFileUpload('file_bang_tot_nghiep', $thi_sinh_id, 'bangtotnghiep');
        $final_path_hoc_ba = $path_hoc_ba_moi ? $path_hoc_ba_moi : $ho_so['path_hoc_ba'];
        $final_path_bang_tot_nghiep = $path_bang_tot_nghiep_moi ? $path_bang_tot_nghiep_moi : $ho_so['path_bang_tot_nghiep'];
        
        // (Lưu các thông tin khác)
        $ma_nganh_1 = mysqli_real_escape_string($conn, $_POST['ma_nganh_1']);
        $ma_nganh_2 = mysqli_real_escape_string($conn, $_POST['ma_nganh_2']);
        $diem_toan_12 = (float)$_POST['diem_toan_12'];
        // ... (các điểm khác)
        $diem_anh_12 = (float)$_POST['diem_anh_12'];
        
        // Trạng thái khi lưu nháp: Nếu đang 'can_bo_sung' thì vẫn là 'can_bo_sung', ngược lại là 'da_luu'
        $trang_thai_luu = ($ho_so['trang_thai'] == 'can_bo_sung') ? 'can_bo_sung' : 'da_luu';

        $sql_update = "UPDATE ho_so_xet_tuyen SET 
                            ma_nganh_1 = ?, ma_nganh_2 = ?, 
                            diem_toan_12 = ?, diem_ly_12 = ?, diem_hoa_12 = ?, 
                            diem_van_12 = ?, diem_anh_12 = ?, 
                            path_hoc_ba = ?, path_bang_tot_nghiep = ?,
                            trang_thai = ? -- Trạng thái lưu nháp
                       WHERE id = ? AND thi_sinh_id = ?";
                       
        $stmt_update = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "ssdddddssii", 
            $ma_nganh_1, $ma_nganh_2, $diem_toan_12, $diem_ly_12, $diem_hoa_12, 
            $diem_van_12, $diem_anh_12, $final_path_hoc_ba, $final_path_bang_tot_nghiep,
            $trang_thai_luu, // Biến trạng thái lưu
            $ho_so_id_to_update, $thi_sinh_id
        );

        if (mysqli_stmt_execute($stmt_update)) {
            $message = "Lưu nháp hồ sơ thành công!";
            $message_type = 'success';
            $ho_so = array_merge($ho_so, $_POST); 
            $ho_so['trang_thai'] = $trang_thai_luu;
            $ho_so['path_hoc_ba'] = $final_path_hoc_ba;
            $ho_so['path_bang_tot_nghiep'] = $final_path_bang_tot_nghiep;
        } else {
            $message = "Lỗi khi lưu hồ sơ: " . mysqli_error($conn);
            $message_type = 'error';
        }
    }
}
// (Hàm handleFileUpload phải được dán ở trên cùng, sau session_start())
function handleFileUpload($file_input_name, $thi_sinh_id, $file_type_prefix) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        $upload_dir = 'uploads/'; 
        $file_tmp_path = $_FILES[$file_input_name]['tmp_name'];
        $file_name = $_FILES[$file_input_name]['name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        // Kiểm tra định dạng file
        if (!in_array($file_extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
            return null; // Không phải định dạng cho phép
        }
        $new_file_name = $file_type_prefix . '_ts_' . $thi_sinh_id . '_' . time() . '.' . $file_extension;
        $dest_path = $upload_dir . $new_file_name;
        if (move_uploaded_file($file_tmp_path, $dest_path)) {
            return $dest_path; 
        }
    }
    return null; 
}
// 7. ĐÓNG KẾT NỐI
mysqli_close($conn);

// Đặt biến active cho sidebar
$current_page = 'nop-ho-so';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nộp Hồ sơ Xét tuyển</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ghi-chu-can-bo {
            margin-top: 16px; padding: 16px; background-color: #f1f5f9; 
            border-radius: 8px; text-align: left;
        }
        .ghi-chu-can-bo strong { display: block; margin-bottom: 8px; color: var(--text-primary); }
    </style>
</head>
<body class="app-body">

    <aside class="sidebar">
        </aside>

    <main class="main-content">
        <header class="main-header student-header"> 
            <div class="welcome-text">
                <h1>Hồ sơ Xét tuyển Trực tuyến</h1>
                <p>Xin chào, <?php echo htmlspecialchars($student_name); ?>. Vui lòng điền đầy đủ thông tin.</p>
            </div>
        </header>
        
        <?php if (!empty($message)): ?>
            <div style="background-color: <?php echo ($message_type == 'success') ? '#f0fdf4' : '#fef2f2'; ?>; 
                        color: <?php echo ($message_type == 'success') ? '#15803d' : '#b91c1c'; ?>; 
                        padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($ho_so['trang_thai'] == 'da_nop'): ?>
            <div style="background-color: #fffbeb; color: #b45309; padding: 20px; border-radius: 6px; margin-bottom: 16px; border: 1px solid #fde68a;">
                <h3 style="margin-top: 0;">Hồ sơ đang chờ duyệt.</h3>
                <p>Bạn đã nộp hồ sơ thành công vào lúc <?php echo date('d/m/Y H:i', strtotime($ho_so['ngay_nop'])); ?>. Hồ sơ đã bị khóa và không thể chỉnh sửa.</p>
            </div>
        <?php elseif ($ho_so['trang_thai'] == 'hop_le'): ?>
            <div style="background-color: #f0fdf4; color: #15803d; padding: 20px; border-radius: 6px; margin-bottom: 16px; border: 1px solid #bbf7d0;">
                <h3 style="margin-top: 0;">Hồ sơ HỢP LỆ.</h3>
                <p>Chúc mừng! Hồ sơ của bạn đã được duyệt. Bạn không cần thao tác gì thêm.</p>
            </div>
        <?php elseif ($ho_so['trang_thai'] == 'can_bo_sung'): ?>
            <div style="background-color: #fef2f2; color: #b91c1c; padding: 20px; border-radius: 6px; margin-bottom: 16px; border: 1px solid #fecaca;">
                <h3 style="margin-top: 0;">Hồ sơ bị từ chối - Cần Bổ sung!</h3>
                <p>Cán bộ đã từ chối hồ sơ của bạn. Vui lòng sửa các lỗi bên dưới, upload lại minh chứng (nếu cần) và **NỘP LẠI**.</p>
                <?php if (!empty($ho_so['ghi_chu_can_bo'])): ?>
                <div class="ghi-chu-can-bo" style="background-color: #fff; border: 1px solid #fecaca;">
                    <strong><i class="fas fa-sticky-note"></i> Ghi chú của Cán bộ:</strong>
                    <?php echo nl2br(htmlspecialchars($ho_so['ghi_chu_can_bo'])); ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <?php if ($dot_tuyen_sinh_mo): // KIỂM TRA CÔNG TẮC ?>
            <div class="card">
                <div style="background-color: #f0fdf4; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
                    <strong>Đang mở đợt: <?php echo htmlspecialchars($dot_tuyen_sinh_mo['ten_dot']); ?></strong>
                    (Hạn nộp: <?php echo date('d/m/Y', strtotime($dot_tuyen_sinh_mo['ngay_ket_thuc'])); ?>)
                </div>
                
                <form action="nop-ho-so.php" method="POST" enctype="multipart/form-data">
                <fieldset <?php if ($is_locked) echo 'disabled'; ?>> 
                    
                    <h3>3. Minh chứng (Upload file)</h3>
                    <div class="form-group">
                        <label for="file_hoc_ba">Học bạ THPT (File PDF, .jpg, .png)</label>
                        <input type="file" id="file_hoc_ba" name="file_hoc_ba" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if ($ho_so['path_hoc_ba']): ?>
                            <p style="color: green; margin-top: 5px;"><i class="fas fa-check-circle"></i> Đã tải lên: 
                                <a href="<?php echo htmlspecialchars($ho_so['path_hoc_ba']); ?>" target="_blank">Xem file hiện tại</a>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="file_bang_tot_nghiep">Bằng Tốt nghiệp (hoặc Giấy CNTN)</label>
                        <input type="file" id="file_bang_tot_nghiep" name="file_bang_tot_nghiep" accept=".pdf,.jpg,.jpeg,.png">
                        <?php if ($ho_so['path_bang_tot_nghiep']): ?>
                            <p style="color: green; margin-top: 5px;"><i class="fas fa-check-circle"></i> Đã tải lên: 
                                <a href="<?php echo htmlspecialchars($ho_so['path_bang_tot_nghiep']); ?>" target="_blank">Xem file hiện tại</a>
                            </p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-actions">
                        <?php if (!$is_locked): // Chỉ hiển thị nút nếu hồ sơ chưa bị khóa ?>
                            <button type="submit" name="luu_nhap" class="btn-submit">
                                <i class="fas fa-save"></i> Lưu nháp
                            </button>
                            
                            <button type="submit" name="nop_ho_so" class="btn-submit" 
                                    <?php if (!in_array($ho_so['trang_thai'], ['da_luu', 'can_bo_sung'])) echo 'disabled style="background-color: grey;"'; ?>
                                    onclick="return confirm('Bạn có chắc chắn muốn nộp hồ sơ? Sau khi nộp sẽ không thể sửa đổi.');">
                                <i class="fas fa-paper-plane"></i> 
                                <?php echo ($ho_so['trang_thai'] == 'can_bo_sung') ? 'Nộp lại Hồ sơ' : 'Nộp hồ sơ'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                </fieldset>
                </form>
            </div>
            
        <?php else: // NẾU KHÔNG CÓ ĐỢT NÀO MỞ ?>
            <div class="card">
                <div class="status-card pending">
                    <i class="fas fa-calendar-times icon"></i>
                    <h3>Hệ thống đã đóng</h3>
                    <p>Hiện tại không có đợt tuyển sinh nào đang mở. Vui lòng quay lại sau.</p>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>