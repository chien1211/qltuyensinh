<?php
session_start(); // Khởi động session

// Hủy tất cả các biến session
$_SESSION = array();

// Hủy session
session_destroy();

// Chuyển hướng người dùng về trang đăng nhập
header("location: ../login.php");
exit;
?>