<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit;
}

$query = "SELECT COUNT(*) AS total, 
                 SUM(CASE WHEN tongDiem >= 5 THEN 1 ELSE 0 END) AS passed,
                 SUM(CASE WHEN tongDiem < 5 THEN 1 ELSE 0 END) AS failed,
                 AVG(tongDiem) AS avgScore
          FROM diem";
$result = $conn->query($query);
$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Định dạng chữ cho phần tiêu đề */
        .main-content h1 {
            font-size: 28px;
            color: #34495e; /* Màu chữ cho tiêu đề */
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
            font-family: 'Arial', sans-serif; /* Font chữ cho tiêu đề */
        }

        /* Định dạng chữ cho các đoạn văn */
        .main-content p {
            font-size: 18px;
            color: #7f8c8d; /* Màu chữ cho các đoạn văn */
            margin: 10px 0;
            line-height: 1.6;
            font-family: 'Verdana', sans-serif; /* Font chữ cho nội dung */
        }

        /* Định dạng chữ cho số liệu nổi bật */
        .main-content p strong {
            color: #2c3e50; /* Màu chữ cho số liệu nổi bật */
            font-weight: bold;
            font-family: 'Verdana', sans-serif; /* Font chữ cho số liệu */
        }
    </style>
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
                <li><a href="manage_diem.php"><i class="material-icons">edit</i> Nhập Điểm</a></li>
                <li><a href="teacher_view_grades.php"><i class="material-icons">visibility</i> Xem Điểm</a></li>
                <li><a href="teacher_report.php"><i class="material-icons">assessment</i> Báo Cáo</a></li>
                <li><a href="gv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>
        <section class="main-content">
            <h1>Báo Cáo Kết Quả Học Tập</h1>
            <p>Tổng số sinh viên: <?= $data['total'] ?></p>
            <p>Số sinh viên đã vượt qua: <?= $data['passed'] ?></p>
            <p>Số sinh viên không vượt qua: <?= $data['failed'] ?></p>
            <p>Điểm trung bình: <?= number_format($data['avgScore'], 2) ?></p>
        </section>
        
    </div>
</body>
</html>
