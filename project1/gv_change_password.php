<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Xử lý khi người dùng gửi yêu cầu đổi mật khẩu
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $username = $_SESSION['username']; // Lấy tên tài khoản từ session

    // Kiểm tra xem mật khẩu mới và xác nhận mật khẩu có khớp không
    if ($newPassword !== $confirmPassword) {
        $message = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
    } else {
        // Kiểm tra mật khẩu hiện tại
        $query = "SELECT password FROM taikhoan WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // So sánh mật khẩu hiện tại trực tiếp (không sử dụng password_verify)
            if ($currentPassword === $row['password']) {
                // Cập nhật mật khẩu mới (không mã hóa mật khẩu nữa)
                $updateQuery = "UPDATE taikhoan SET password = ? WHERE username = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("ss", $newPassword, $username); // Trực tiếp sử dụng mật khẩu mới
                if ($updateStmt->execute()) {
                    $message = "Đổi mật khẩu thành công!";
                } else {
                    $message = "Đã xảy ra lỗi khi đổi mật khẩu.";
                }
                $updateStmt->close();
            } else {
                $message = "Mật khẩu hiện tại không đúng.";
            }
        } else {
            $message = "Không tìm thấy thông tin tài khoản.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản lý Giảng Viên</title>
    <!-- Liên kết đến Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Nội dung chính */
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #ffffff;
            border-left: 1px solid #ddd;
            box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.1);
        }

        form input[type="text"],
        form select,
        form input[type="password"] {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(100% - 10px);
            box-sizing: border-box;
            background-color: #fdfdfd;
            transition: border-color 0.3s;
        }

        form input[type="text"]:focus,
        form select:focus,
        form input[type="email"]:focus {
            border-color: #3498db;
            outline: none;
        }

        form button {
            font-size: 16px;
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #2ecc71;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        table thead {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }

        table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        table tbody tr:nth-child(even) {
            background-color: #ecf0f1;
        }

        table tbody tr:hover {
            background-color: #dfe6e9;
        }

        table button {
            font-size: 14px;
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        table button.btn-delete {
            background-color: #e74c3c;
            color: white;
        }

        table button.btn-delete:hover {
            background-color: #c0392b;
        }

        table button.btn-edit {
            background-color: #2980b9;
            color: white;
        }

        table button.btn-edit:hover {
            background-color: #3498db;
        }

        .a1{
            color: white;
            background-color: #27ae60;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-bottom: 1px solid #ddd;
            }

            .main-content {
                padding: 10px;
            }

            form input[type="text"],
            form select {
                width: 100%;
            }

            table th,
            table td {
                font-size: 12px;
                padding: 10px;
            }
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
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="gv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>
        <section class="main-content">
            <h1>Đổi Mật Khẩu</h1>
            <?php if ($message): ?>
                <p><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>
            <form method="POST">
                <div>
                    <label for="current_password">Mật Khẩu Hiện Tại:</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                <div>
                    <label for="new_password">Mật Khẩu Mới:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <div>
                    <label for="confirm_password">Xác Nhận Mật Khẩu Mới:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <br>
                <button type="submit">Đổi Mật Khẩu</button>
            </form>
            </section>
    </div>
</body>
</html>
