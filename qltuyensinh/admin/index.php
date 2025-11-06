<?php
// LUÔN LUÔN bắt đầu session ở dòng đầu tiên
session_start();

// 1. BẢO VỆ: Kiểm tra xem user đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa, đá về trang login (lùi ra 1 cấp)
    header('Location: ../login.php');
    exit;
}

// 2. PHÂN QUYỀN: Lấy thông tin từ Session
$role = $_SESSION['role']; // 'admin' hoặc 'staff'
$username = $_SESSION['username'];

// 3. NẠP KẾT NỐI DATABASE
require '../db_connection.php'; // (lùi ra 1 cấp)
$conn = getDbConnection();

// 4. LẤY SỐ LIỆU (Tạm thời là số 0, sẽ thay bằng query thật)

// Biến cho Admin
$so_can_bo = 0;
$so_thi_sinh = 0;
$so_ho_so_moi = 0;
$so_nganh_hoc = 0;

// Biến cho Staff
$ho_so_cho_duyet = 0;
$ho_so_da_duyet = 0;

// 5. QUERY DỮ LIỆU THẬT TỪ DATABASE
if ($role == 'admin') {
    // === CODE CHO ADMIN ===

    // 1. Đếm số cán bộ (staff) - Đã sửa lỗi cú pháp
    $result_cb = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'staff'");
    if($result_cb) {
        $so_can_bo = mysqli_fetch_assoc($result_cb)['total'];
    }

    // 2. Đếm tổng số thí sinh (Mở ra vì giờ đã có bảng thi_sinh)
    $result_ts = mysqli_query($conn, "SELECT COUNT(*) AS total FROM thi_sinh");
    if($result_ts) {
        $so_thi_sinh = mysqli_fetch_assoc($result_ts)['total'];
    }

    // 3. Đếm hồ sơ mới (Mở ra, trang_thai = 'da_nop')
    $result_hs = mysqli_query($conn, "SELECT COUNT(*) AS total FROM ho_so_xet_tuyen WHERE trang_thai = 'da_nop'");
    if($result_hs) {
        $so_ho_so_moi = mysqli_fetch_assoc($result_hs)['total'];
    }
    
    // 4. Đếm số ngành học
    $result_nganh = mysqli_query($conn, "SELECT COUNT(*) AS total FROM nganh_hoc");
    if($result_nganh) {
        $so_nganh_hoc = mysqli_fetch_assoc($result_nganh)['total'];
    }

} elseif ($role == 'staff') {
    // === CODE CHO STAFF ===
    
    // 1. Đếm hồ sơ CHỜ DUYỆT (trang_thai = 'da_nop')
    $query_moi = "SELECT COUNT(*) AS total FROM ho_so_xet_tuyen WHERE trang_thai = 'da_nop'";
    $result_moi = mysqli_query($conn, $query_moi);
    if ($result_moi) {
        $ho_so_cho_duyet = mysqli_fetch_assoc($result_moi)['total'];
    }

    // 2. Đếm hồ sơ ĐÃ DUYỆT (bất kể 'hop_le' hay 'can_bo_sung')
    $query_da_duyet = "SELECT COUNT(*) AS total FROM ho_so_xet_tuyen WHERE trang_thai IN ('hop_le', 'can_bo_sung')";
    $result_da_duyet = mysqli_query($conn, $query_da_duyet);
    if ($result_da_duyet) {
        $ho_so_da_duyet = mysqli_fetch_assoc($result_da_duyet)['total'];
    }
    
    // 3. Lấy 5 hồ sơ mới nhất cho cái bảng ở dưới
    $query_danh_sach = "
        SELECT 
            hs.id AS ho_so_id,
            ts.ho_ten,
            hs.ngay_nop
        FROM 
            ho_so_xet_tuyen AS hs
        JOIN 
            thi_sinh AS ts ON hs.thi_sinh_id = ts.id
        WHERE 
            hs.trang_thai = 'da_nop'
        ORDER BY 
            hs.ngay_nop DESC
        LIMIT 5
    ";
    $result_danh_sach_moi = mysqli_query($conn, $query_danh_sach);
}

// Đặt biến để sidebar biết trang nào đang active
$current_page = 'dashboard';

// 6. LẮP RÁP GIAO DIỆN
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <?php include 'partials/header_meta.php'; ?>
    <title>Bảng điều khiển - Hệ thống Tuyển sinh</title>
</head>
<body class="app-body">

    <?php 
        // Nạp thanh sidebar (menu bên trái)
        include 'partials/sidebar.php'; 
    ?>

    <main class="main-content">
        
        <?php 
            // Nạp thanh header (chào mừng, tên user)
            include 'partials/main_header.php'; 
        ?>

        <?php
        // 7. RẼ NHÁNH NỘI DUNG CHÍNH
        if ($role == 'admin') {
            include 'views/admin_dashboard.php';
        } elseif ($role == 'staff') {
            include 'views/staff_dashboard.php';
        }
        ?>

    </main>
    </body>
</html>