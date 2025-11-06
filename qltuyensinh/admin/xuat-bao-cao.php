<?php
// File này không có HTML, nó chỉ "nhả" ra file CSV.

// 1. Khởi động các dịch vụ cốt lõi
session_start();
require '../db_connection.php';
$conn = getDbConnection();

// 2. BẢO MẬT: Phải là Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    // Nếu không phải admin, trả về lỗi 403 (Cấm)
    header("HTTP/1.1 403 Forbidden");
    exit;
}

// 3. Đặt tên file sẽ được tải về
$filename = "danh_sach_trung_tuyen_" . date('Y-m-d') . ".csv";

// 4. THIẾT LẬP "HEADER" ĐỂ ÉP TRÌNH DUYỆT TẢI FILE
// Đây là phần "ảo thuật"
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// 5. CHUẨN BỊ DỮ LIỆU TỪ CSDL
// Đây là câu JOIN "khủng" nhất: lấy TẤT CẢ thông tin
$sql = "
    SELECT 
        ts.ho_ten, ts.email, ts.so_cccd, ts.so_dien_thoai, ts.ngay_sinh,
        hs.trang_thai, hs.ngay_nop,
        ng1.ten_nganh AS ten_nganh_1,
        ng2.ten_nganh AS ten_nganh_2,
        hs.diem_toan_12, hs.diem_ly_12, hs.diem_hoa_12, 
        hs.diem_van_12, hs.diem_anh_12,
        hs.ghi_chu_can_bo
    FROM 
        ho_so_xet_tuyen AS hs
    JOIN 
        thi_sinh AS ts ON hs.thi_sinh_id = ts.id
    LEFT JOIN 
        nganh_hoc AS ng1 ON hs.ma_nganh_1 = ng1.ma_nganh
    LEFT JOIN 
        nganh_hoc AS ng2 ON hs.ma_nganh_2 = ng2.ma_nganh
    WHERE 
        hs.trang_thai = 'hop_le'  -- Chỉ xuất các hồ sơ HỢP LỆ
    ORDER BY 
        ts.ho_ten ASC
";

$result = mysqli_query($conn, $sql);

// 6. MỞ "DÒNG RA" (output stream) ĐỂ GHI FILE
// 'php://output' là một luồng đặc biệt, nó ghi thẳng vào response
$output = fopen('php://output', 'w');

// 6.1. Sửa lỗi font tiếng Việt trên Excel
// Excel rất ngu ngốc với file CSV UTF-8, nó cần 3 ký tự "BOM" này
fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

// 6.2. GHI DÒNG TIÊU ĐỀ (HEADER) CỦA CSV
$header = [
    'Ho Ten', 'Email', 'CCCD', 'So Dien Thoai', 'Ngay Sinh',
    'Trang Thai', 'Ngay Nop',
    'Nguyen Vong 1', 'Nguyen Vong 2',
    'Diem Toan', 'Diem Ly', 'Diem Hoa', 'Diem Van', 'Diem Anh',
    'Ghi Chu Cua Can Bo'
];
fputcsv($output, $header);

// 6.3. GHI DỮ LIỆU TỪNG DÒNG
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Chuyển đổi (map) dữ liệu từ DB sang file CSV
        $csv_row = [
            $row['ho_ten'],
            $row['email'],
            "'" . $row['so_cccd'],
            $row['so_dien_thoai'],
            date('d/m/Y', strtotime($row['ngay_sinh'])),
            $row['trang_thai'],
            date('d/m/Y H:i', strtotime($row['ngay_nop'])),
            $row['ten_nganh_1'],
            $row['ten_nganh_2'],
            $row['diem_toan_12'],
            $row['diem_ly_12'], 
            $row['diem_hoa_12'],
            $row['diem_van_12'],
            $row['diem_anh_12'],
            $row['ghi_chu_can_bo']
        ];
        fputcsv($output, $csv_row);
    }
}

// 7. ĐÓNG CÁC KẾT NỐI
fclose($output);
mysqli_close($conn);
exit; // Kết thúc file
?>