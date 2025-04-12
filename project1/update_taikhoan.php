<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Lấy ID tài khoản từ URL
$id = $_GET['id'];

// Lấy thông tin tài khoản cần sửa
$result = $conn->query("SELECT * FROM taikhoan WHERE id = '$id'");
$account = $result->fetch_assoc();

// Xử lý cập nhật tài khoản
if (isset($_POST['update'])) {
    $password = $_POST['password']; // Sử dụng mật khẩu trực tiếp mà không mã hóa
    $email = $_POST['email'];
    $role = $_POST['role'];
    
    // Cập nhật thông tin tài khoản
    if ($conn->query("UPDATE taikhoan SET password='$password', email='$email', role='$role' WHERE id='$id'")) {
        $_SESSION['notification'] = "Cập nhật tài khoản thành công!";
    } else {
        $_SESSION['notification'] = "Lỗi khi cập nhật tài khoản: " . $conn->error;
    }
}

// Lấy danh sách role
$roleList = $conn->query("SELECT * FROM roles");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật Tài khoản</title>
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
        form select,
        form input[type="email"],
        form input[type="password"] {
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

    <section class="main-content">
        <h1>Cập nhật Tài khoản</h1>

        <!-- Hiển thị thông báo nếu có -->
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="notification">
                <?php 
                    echo $_SESSION['notification'];
                    unset($_SESSION['notification']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Trường Tên tài khoản chỉ đọc -->
            <input type="text" name="username" value="<?= $account['username'] ?>" readonly>
            
            <!-- Trường Mật khẩu -->
            <input type="password" name="password" placeholder="Mật khẩu" required>
            
            <!-- Trường Email -->
            <input type="email" name="email" value="<?= $account['email'] ?>" required>
            
            <!-- Trường Vai trò -->
            <select name="role" required>
                <option value="">Chọn vai trò</option>
                <?php while ($row = $roleList->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= $row['id'] == $account['role'] ? 'selected' : '' ?>><?= $row['role'] ?></option>
                <?php endwhile; ?>
            </select>
            
            <button type="submit" name="update">Cập nhật</button>
            <a href="manage_taikhoan.php" style="text-decoration: none; padding: 10px; background: #ccc; color: black; border-radius: 5px;">Quay lại</a>
        </form>
    </section>
</div>
</body>
</html>
