<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải giảng viên không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php"); // Chuyển hướng nếu không phải giảng viên
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản lý Giảng Viên</title>
    <!-- Liên kết đến Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>
                <img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Giảng Viên
            </h2>
            <ul>
                <li><a href="teacher_home.php"><i class="material-icons">home</i> Trang Chủ</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="gv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Chào mừng, Giảng viên</h1>
            <p>Vui lòng chọn một chức năng từ menu bên trái để bắt đầu quản lý.</p>

            <div class="function-links">
                <h2>Chức Năng</h2>
                <ul>
                    <li><a href="manage_sinhvien.php"><img src="https://student.hunre.edu.vn/congthongtin/assets/images/dashboad-item-4.png" alt="xem thông tin sv" style="width: 24px; height: 24px;"> xem thông tin sinh viên </a></li>
                    
                </ul>
            </div>
        </section>
    </div>
</body>
</html>
