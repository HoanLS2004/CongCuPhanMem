<?php
session_start();

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

$message = "";

// Kiểm tra nếu nhận được mã khoa để cập nhật
if (isset($_GET['maKH'])) {
    $maKH = $_GET['maKH'];
    $stmt = $conn->prepare("SELECT * FROM khoa WHERE maKH = ?");
    $stmt->bind_param("s", $maKH);
    $stmt->execute();
    $result = $stmt->get_result();
    $khoa = $result->fetch_assoc();
} else {
    // Chuyển hướng nếu không có mã khoa
    header("Location: manage_khoa.php");
    exit();
}

// Xử lý cập nhật thông tin khoa
if (isset($_POST['update'])) {
    $tenKH = $_POST['tenKH'];
    $lienheKH = $_POST['lienheKH'];
    $stmt = $conn->prepare("UPDATE khoa SET tenKH = ?, lienheKH = ? WHERE maKH = ?");
    $stmt->bind_param("sss", $tenKH, $lienheKH, $maKH);
    if ($stmt->execute()) {
        $message = "Cập nhật khoa thành công!";
    } else {
        $message = "Có lỗi xảy ra. Vui lòng thử lại.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khoa</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .message {
            padding: 15px;
            background-color: #eafaf1; 
            color: #27ae60; 
            border: 1px solid #a3d9a5; 
            margin-bottom: 20px;
            border-radius: 8px; 
            text-align: center;
            font-size: 16px; 
            font-weight: 500;
        }

        form input[type="text"],
        form select {
            width: 100%;
            padding: 12px; 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #f9f9f9; 
            transition: border-color 0.3s ease;
        }

        form input[type="text"]:focus {
            border-color: #27ae60; 
            outline: none; 
            background-color: #ffffff; 
        }

        form button {
            padding: 14px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-size: 16px;
            font-weight: bold; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        }

        form button:hover {
            background-color: #2ecc71;
            transform: scale(1.05); 
        }

        form a {
            display: inline-block;
            text-decoration: none;
            padding: 0px;
            background: #bdc3c7;
            color: black;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        form a:hover {
            background: #95a5a6;
            transform: scale(1.05); /* Phóng to nhẹ khi hover */
        }

        @media screen and (max-width: 768px) {
            form {
                padding: 15px;
                margin: 10px;
            }

            form input[type="text"] {
                font-size: 14px;
            }

            form button, form a {
                font-size: 14px; /* Thu nhỏ nút và liên kết cho màn hình nhỏ */
                padding: 10px 20px; /* Giảm kích thước padding */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="sidebar">
            <h2><img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin </h2>
            <ul>
                <li><a href="manage_khoa.php"><i class="material-icons">school</i> Quản lý Khoa</a></li>
                <li><a href="manage_lop.php"><i class="material-icons">class</i> Quản lý Lớp</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="manage_giangvien.php"><i class="material-icons">person_outline</i> Quản lý Giảng viên</a></li>
                <li><a href="manage_monhoc.php"><i class="material-icons">book</i> Quản lý Môn học</a></li>
                <li><a href="manage_taikhoan.php"><i class="material-icons">account_circle</i> Phân Quyền</a></li>
                <li><a href="report.php"><i class="material-icons">assessment</i> Thống kê và Báo cáo</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>
        <!-- Form cập nhật khoa -->
        <section class="main-content">
            <h1>Cập Nhật khoa</h1>
            <!-- Hiển thị thông báo -->
            <?php if ($message): ?>
                <div class="message"><?= $message ?></div>
            <?php endif; ?>

            <form method="POST">
                <label for="maLop">Mã Khoa</label>
                <input type="text" name="maKH" value="<?= $khoa['maKH'] ?>" readonly>
                <label for="maLop">Tên Khoa</label>
                <input type="text" name="tenKH" value="<?= $khoa['tenKH'] ?>" required>
                <label for="maLop">Liên hệ khoa</label>
                <input type="text" name="lienheKH" value="<?= $khoa['lienheKH'] ?>">
                <button type="submit" name="update">Cập nhật</button>
                <a href="manage_khoa.php" style="text-decoration: none; padding: 10px; background: #ccc; color: black; border-radius: 5px;">Quay lại</a>
            </form>
        </section>
    </div>
</body>
</html>
