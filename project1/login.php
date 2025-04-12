<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

$error_message = ""; // Khởi tạo biến thông báo lỗi

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']); // Sanitize input
    $password = $_POST['password']; // Mật khẩu người dùng nhập

    // Lấy thông tin người dùng từ bảng taikhoan
    $stmt = $conn->prepare("SELECT * FROM taikhoan WHERE username = ?");
    $stmt->bind_param('s', $username); // Sử dụng bind_param để truyền tham số
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Kiểm tra nếu người dùng tồn tại và so sánh mật khẩu
    if ($user && $password === $user['password']) { // So sánh mật khẩu trực tiếp
        // Lưu thông tin người dùng vào session
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Vai trò người dùng

        // Nếu là sinh viên, lưu mã sinh viên vào session
        if ($user['role'] == 'Student') {
            $maSVQuery = "SELECT maSV FROM sinhvien WHERE email = ?";
            $maSVStmt = $conn->prepare($maSVQuery);
            $maSVStmt->bind_param("s", $user['email']);
            $maSVStmt->execute();
            $maSVResult = $maSVStmt->get_result();

            if ($maSVResult->num_rows > 0) {
                $maSV = $maSVResult->fetch_assoc();
                $_SESSION['maSV'] = $maSV['maSV']; // Lưu mã sinh viên vào session
            }
        }

        // Chuyển hướng theo vai trò
        if ($user['role'] == 'Admin') {
            header('Location: admin_home.php');
        } elseif ($user['role'] == 'Teacher') {
            header('Location: teacher_home.php');
        } elseif ($user['role'] == 'Student') {
            header('Location: student_home.php');
        }
        exit;
    } else {
        $error_message = "Sai tên đăng nhập hoặc mật khẩu!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang đăng nhập</title>
    <style>
        /* CSS styles for the login page */
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .left-section {
            flex: 1.5;
            background: linear-gradient(135deg, #007bff, #00c3ff);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .left-section img {
            max-width: 80%;
            margin-bottom: 20px;
        }

        .left-section h2 {
            font-size: 24px;
            margin-bottom: 10px;
            text-align: center;
        }

        .left-section p {
            font-size: 18px;
            text-align: center;
            margin-bottom: 20px;
        }

        .right-section {
            flex: 1;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .logo {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .logo img {
            width: 40px;
            margin-right: 10px;
        }

        .logo h1 {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }

        .login-title {
            font-size: 22px;
            color: #333;
            margin-bottom: 20px;
        }

        .login-form {
            width: 100%;
            max-width: 400px;
        }

        .login-form label {
            font-size: 14px;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }

        .login-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .login-form input:focus {
            outline: none;
            border-color: #007bff;
        }

        .login-form button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .login-form button:hover {
            background-color: #0056b3;
        }

        .login-form .forgot-password {
            text-align: right;
            display: block;
            margin-bottom: 10px;
            font-size: 14px;
            text-decoration: none;
            color: #007bff;
        }

        .login-form .forgot-password:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        footer {
            text-align: center;
            font-size: 14px;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Phần bên trái -->
        <div class="left-section">
            <h2>WEBSITE QUẢN LÝ HỒ SƠ SINH VIÊN,</h2>
            <p>TRƯỜNG ĐẠI HỌC CÔNG NGHỆ GIAO THÔNG VẬN TẢI</p>
            <img src="image/grade.jpg" alt="ảnh">
        </div>

        <!-- Phần bên phải -->
        <div class="right-section">
            <div class="logo">
                <img src="image/logo.png" alt="Logo">
                <h1>UTT.edu / Edutech</h1>
            </div>
            <h2 class="login-title">Chào mừng bạn trở lại!</h2>
            <form class="login-form" method="POST">
                <!-- Hiển thị thông báo lỗi nếu có -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <label for="username">Tài khoản</label>
                <input type="text" id="username" name="username" placeholder="Nhập tài khoản" required>

                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
                <button type="submit" id="login-button">Đăng nhập</button>
            </form>
            <footer>
                v3.2 - 2024 | 2024
            </footer>
        </div>
    </div>
</body>
</html>
