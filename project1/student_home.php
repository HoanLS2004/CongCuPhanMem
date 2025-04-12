<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải Sinh viên không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php"); // Chuyển hướng nếu không phải Sinh viên
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản lý Sinh Viên</title>
    <!-- Liên kết đến Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>
                <img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Sinh Viên
            </h2>
            <ul>
                <li><a href="student_home.php"><i class="material-icons">home</i> Trang Chủ</a></li>
                <li><a href="studentinfo.php"><i class="material-icons">info</i> Thông tin cá nhân</a></li>
                <li><a href="sv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
                <li><a href="donate_info.php"><i class="material-icons">assessment</i>  Thông tin học phí</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Chào mừng, Sinh viên</h1>
            <p>Vui lòng chọn một chức năng từ menu bên trái để thực hiện các công việc của bạn.</p>

            <div class="function-links">
                <h2>Chức Năng</h2>
                <ul>
                <li><a href="studentinfo.php"><img src="https://student.hunre.edu.vn/congthongtin/assets/images/dashboad-item-5.png" alt="Xem tt" style="width: 24px; height: 24px;">thông tin cá nhân </a></li>
                   
                </ul>
            </div>
        </section>
    </div>
</body>
</html>
