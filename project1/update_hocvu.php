<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu


if (isset($_GET['masv'])) {
    $MASV = $_GET['masv'];

    // Lấy thông tin môn học
    $stmt = $conn->prepare("SELECT * FROM xulyhocvu WHERE masv = ?");
    $stmt->bind_param("s", $MASV);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $_SESSION['notification'] = "Học vụ không tồn tại!";
        header("Location: quanlyhocvu.php");
        exit;
    }
    $stmt->close();
}


if (isset($_POST['update'])) {
    // Lấy dữ liệu từ form và gán vào các biến
    $masv = $_POST['MASV'];           // Tên trường tương ứng với 'MASV'
    $tensv = $_POST['TENSV'];         // Tên trường tương ứng với 'TENSV'
    $loaixulyhocvu = $_POST['LOAIXULYHOCVU']; // Tên trường tương ứng với 'LOAIXULYHOCVU'
    $ngayxuly = $_POST['NGAYXULY'];  // Tên trường tương ứng với 'NGAYXULY'
    $lydo = $_POST['LYDO'];           // Tên trường tương ứng với 'LYDO'
    $quyetdinh = $_POST['QUYETDINH']; // Tên trường tương ứng với 'QUYETDINH'
    $nguoixuly = $_POST['NGUOIXULY']; // Tên trường tương ứng với 'NGUOIXULY'
    $trangthai = $_POST['TRANGTHAI']; // Tên trường tương ứng với 'TRANGTHAI'

        // Sử dụng prepared statements để bảo vệ khỏi SQL Injection
    $stmt = $conn->prepare("UPDATE xulyhocvu SET TENSV = ?, LOAIXULYHOCVU = ?, NGAYXULY = ?, LYDO = ?, QUYETDINH = ?, NGUOIXULY = ?, TRANGTHAI = ? WHERE MASV = ?");
    $stmt->bind_param("ssssssss", $tensv, $loaixulyhocvu, $ngayxuly, $lydo, $quyetdinh, $nguoixuly, $trangthai, $masv);

    // Kiểm tra kết quả và lưu thông báo vào session
    if ($stmt->execute()) {
        $_SESSION['notification'] = "Thông tin sinh viên đã được cập nhật thành công!";
    } else {
        $_SESSION['notification'] = "Lỗi khi cập nhật thông tin sinh viên: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý học vụ</title>
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
        <h1>Cập nhật học vụ</h1>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="notification">
                <?php 
                    echo $_SESSION['notification'];
                    unset($_SESSION['notification']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Form cập nhật môn học -->
        <form method="POST">
        <input type="text" name="MASV" value="<?= htmlspecialchars($row['MASV']) ?>" required>
        <input type="text" name="TENSV" value="<?= htmlspecialchars($row['TENSV']) ?>" required>
        <input type="text" name="LOAIXULYHOCVU" value="<?= htmlspecialchars($row['LOAIXULYHOCVU']) ?>" required>
        <input type="date" name="NGAYXULY" value="<?= htmlspecialchars($row['NGAYXULY']) ?>" required>
        <input type="text" name="LYDO" value="<?= htmlspecialchars($row['LYDO']) ?>" required>
        <input type="text" name="QUYETDINH" value="<?= htmlspecialchars($row['QUYETDINH']) ?>" required>
        <input type="text" name="NGUOIXULY" value="<?= htmlspecialchars($row['NGUOIXULY']) ?>" required>
        <input type="text" name="TRANGTHAI" value="<?= htmlspecialchars($row['TRANGTHAI']) ?>" required>

            <button type="submit" name="update">Cập nhật</button>
            <a href="quanlyhocvu.php" style="text-decoration: none; padding: 10px; background: #ccc; color: black; border-radius: 5px;">Quay lại</a>
        </form>
    </div>
</body>
</html>
