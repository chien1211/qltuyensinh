<img width="1889" height="910" alt="image" src="https://github.com/user-attachments/assets/75396529-a772-4224-9cea-212a9891915b" />

### 📖 1. Giới thiệu
Trong bối cảnh công nghệ thông tin phát triển mạnh mẽ, việc ứng dụng phần mềm vào công tác quản lý đã trở nên vô cùng cần thiết. Đối với các trường đại học, quy trình tuyển sinh hàng năm là một nghiệp vụ quan trọng, phức tạp, đòi hỏi xử lý một khối lượng lớn hồ sơ của thí sinh. Việc quản lý thủ công bằng giấy tờ hoặc các công cụ không chuyên nghiệp (như Excel) thường tốn nhiều thời gian, nhân lực, và dễ xảy ra sai sót, nhầm lẫn.

Do đó, việc xây dựng một Hệ thống Quản lý Tuyển sinh trực tuyến là một giải pháp cấp thiết, giúp tự động hóa và tối ưu hóa quy trình từ lúc thí sinh nộp hồ sơ, xét duyệt, cho đến khi công bố kết quả.

### 🔧 2. Các công nghệ được sử dụng
<img width="957" height="508" alt="image" src="https://github.com/user-attachments/assets/6503ef4a-9a11-472e-9577-72d8efb5ae31" />

### 🚀 3. Hình ảnh các chức năng
###Trang đăng nhập
<img width="1430" height="754" alt="image" src="https://github.com/user-attachments/assets/22f649df-57bf-439e-b496-96293b16c089" />

###Trang quản trị viên
<img width="1899" height="908" alt="image" src="https://github.com/user-attachments/assets/73c10cc4-8f1b-4385-a206-f569463df105" />

###Quản lý cán bộ
<img width="1877" height="902" alt="image" src="https://github.com/user-attachments/assets/348c5898-863e-4c9f-90f4-9fa2729b18bb" />

###Quản lý ngành học
<img width="1879" height="911" alt="image" src="https://github.com/user-attachments/assets/d89ff258-fe9d-4b7d-b88e-efd1bcfb81d4" />

###Quản lý đợt tuyển sinh
<img width="1899" height="909" alt="image" src="https://github.com/user-attachments/assets/353d211c-3a86-49fd-8019-802f172773e9" />

###Cổng thông tin thí sinh
<img width="1906" height="907" alt="image" src="https://github.com/user-attachments/assets/e3025b00-f51f-41b8-a27d-2a527b9a5953" />

###Nộp hồ sơ xét tuyển
<img width="1589" height="889" alt="image" src="https://github.com/user-attachments/assets/f92cd06d-ba4d-4c59-b394-f0aecffb66ac" />

###Tra cứu trạng thái hồ sơ
<img width="1897" height="906" alt="image" src="https://github.com/user-attachments/assets/a0c25164-e609-4caf-aa38-142c7f12af64" />

### ⚙️ 4. Cài đặt
4.1. Cài đặt công cụ, môi trường và các thư viện cần thiết

* Tải và cài đặt XAMPP
    👉 https://www.apachefriends.org/download.html
    (Khuyến nghị bản XAMPP với PHP 8.x)

* Cài đặt Visual Studio Code và các extension:
    * PHP Intelephense
    * MySQL
    * Prettier – Code Formatter

4.2. Tải project
Clone project về thư mục htdocs của XAMPP (ví dụ ổ C):

```
cd C:\xampp\htdocs
https://github.com/chien1211/qltuyensinh.git
Truy cập project qua đường dẫn:
👉 http://localhost/authentication_login.
```

4.3. Setup database
Mở XAMPP Control Panel, Start Apache và MySQL

Truy cập MySQL WorkBench Tạo database:

4.4. Setup tham số kết nối
Mở file config.php (hoặc .env) trong project, chỉnh thông tin DB:

```
<?php

function getDbConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "Chien2005@";
    $dbname = "qltuyensinh";
    $port = 3306;

    // Tạo kết nối
    $conn = mysqli_connect($servername, $username, $password, $dbname, $port);

    // Kiểm tra kết nối
    if (!$conn) {
        die("Kết nối database thất bại: " . mysqli_connect_error());
    }
    // Thiết lập charset cho kết nối (quan trọng để hiển thị tiếng Việt đúng)
    mysqli_set_charset($conn, "utf8");
    return $conn;
}

?>
```

4.5. Chạy hệ thống
Mở XAMPP Control Panel → Start Apache và MySQL

Truy cập hệ thống: 👉 http://localhost/index.php

4.6. Đăng nhập lần đầu
Hệ thống có thể cấp tài khoản admin

