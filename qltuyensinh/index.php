<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Tuyển sinh Đại học</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <nav class="public-nav">
        <div class="nav-container">
            <a href="index.php" class="nav-logo">
                <img src="images\logo-doublemint.png" alt="Logo">
                <span>HỆ THỐNG TUYỂN SINH</span>
            </a>
            <div class="nav-links">
                <a href="#nganh-hoc">Ngành học</a>
                <a href="#phuong-thuc">Phương thức</a>
                <a href="#tin-tuc">Tin tức</a>
            </div>
            <div class="nav-auth">
                <a href="login.php" class="btn btn-login">Đăng nhập</a>
                <a href="dangky.php" class="btn btn-register">Đăng ký</a>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="hero-content">
            <h1>MỞ CỔNG TUYỂN SINH NĂM 2026</h1>
            <p>Xét tuyển đơn giản, nhanh chóng, minh bạch. Nộp hồ sơ ngay hôm nay!</p>
            <a href="dangky.php" class="btn btn-register btn-large">
                <i class="fas fa-file-alt"></i> Nộp hồ sơ ngay
            </a>
        </div>
    </header>

    <main class="public-main">
        
        <section id="phuong-thuc" class="info-section">
            <h2>Phương thức xét tuyển</h2>
            <div class="info-grid">
                <div class="info-card">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Xét tuyển Học bạ</h3>
                    <p>Sử dụng kết quả học tập THPT (học bạ) để đăng ký xét tuyển.</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-book-reader"></i>
                    <h3>Xét điểm thi THPT</h3>
                    <p>Sử dụng kết quả kỳ thi Tốt nghiệp THPT Quốc gia.</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-award"></i>
                    <h3>Tuyển thẳng</h3>
                    <p>Theo quy định của Bộ GD&ĐT và đề án tuyển sinh của trường.</p>
                </div>
            </div>
        </section>

        <section id="nganh-hoc" class="info-section">
            <h2>Ngành đào tạo mũi nhọn</h2>
            <div class="info-grid">
                <div class="info-card">
                    <i class="fas fa-laptop-code"></i>
                    <h3>Công nghệ thông tin</h3>
                    <p>Đào tạo kỹ sư phần mềm, an toàn thông tin, AI.</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-briefcase-medical"></i>
                    <h3>Quản trị Kinh doanh</h3>
                    <p>Chuyên sâu về marketing, tài chính, nhân sự.</p>
                </div>
                <div class="info-card">
                    <i class="fas fa-paint-brush"></i>
                    <h3>Thiết kế Đồ họa</h3>
                    <p>Sáng tạo không giới hạn với thiết kế 2D, 3D, UI/UX.</p>
                </div>
            </div>
        </section>

    </main>

    <footer class="public-footer">
        <p>&copy; 2025 Hệ thống Quản lý tuyển sinh hệ Đại học. Phát triển bởi [Nguyen Van Huong].</p>
        <p>Địa chỉ: Số 1, Phố Xốm, Phú Lãm, Hà Đông, Hà Nội | Hotline: 0398.994.705</p>
    </footer>

</body>
</html>