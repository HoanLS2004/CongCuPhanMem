<?php
session_start();

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải Admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php"); // Chuyển hướng nếu không phải Admin
    exit;
}

// Khởi tạo thông báo
$message = "";

// Xử lý thêm tài khoản
if (isset($_POST['add'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Kiểm tra trùng username hoặc email
    $checkQuery = "SELECT * FROM taikhoan WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $checkResult = $stmt->get_result();

    if ($checkResult->num_rows > 0) {
        $message = "Tên tài khoản hoặc email đã tồn tại!";
    } else {
        // Chèn dữ liệu nếu không trùng
        $insertQuery = "INSERT INTO taikhoan (username, password, email, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ssss", $username, $password, $email, $role);

        if ($stmt->execute()) {
            $message = "Thêm tài khoản thành công!";
        } else {
            $message = "Có lỗi xảy ra khi thêm tài khoản.";
        }
    }
} elseif (isset($_POST['delete'])) {
    $id = $_POST['id'];
    if ($conn->query("DELETE FROM taikhoan WHERE id='$id'")) {
        $message = "Xóa tài khoản thành công!";
    } else {
        $message = "Có lỗi xảy ra khi xóa tài khoản.";
    }
}

// Lấy danh sách tài khoản
$result = $conn->query("SELECT taikhoan.*, roles.role AS role_name FROM taikhoan JOIN roles ON taikhoan.role = roles.id");

// Khởi tạo biến tìm kiếm
$usernameSearch = '';

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $usernameSearch = $_POST['usernameSearch'];

    // Xây dựng câu truy vấn tìm kiếm
    $query = "SELECT taikhoan.*, roles.role AS role_name FROM taikhoan JOIN roles ON taikhoan.role = roles.id WHERE 1=1"; // Điều kiện mặc định

    // Điều kiện tìm kiếm theo tên tài khoản
    if ($usernameSearch != '') {
        $query .= " AND taikhoan.username LIKE '%$usernameSearch%'";
    }

    $result = $conn->query($query);
} else {
    // Nếu không tìm kiếm, lấy tất cả dữ liệu
    $result = $conn->query("SELECT taikhoan.*, roles.role AS role_name FROM taikhoan JOIN roles ON taikhoan.role = roles.id");
}

// Lấy danh sách vai trò
$roleList = $conn->query("SELECT * FROM roles");
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tài khoản</title>
    <!-- Liên kết đến Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Main Content Styling */
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #ffffff;
            border-left: 1px solid #ddd;
            box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.1);
        }

        .main-content h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #2980b9;
            text-transform: uppercase;
        }

        /* Form Styling */
        form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        form input[type="text"],
        form select,
        form input[type="email"],
        form input[type="password"] {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(50% - 10px); /* Đảm bảo 3 ô nằm ngang, trừ khoảng cách */
            box-sizing: border-box;
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
            background-color: #fdfdfd;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }

        table th, table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table thead {
            background-color: #34495e;
            color: white;
            font-weight: bold;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tbody tr:hover {
            background-color: #f1f1f1;
        }

        table button {
            font-size: 14px;
            padding: 5px 10px;
            background-color: #2980b9;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        table button:hover {
            background-color: #3498db;
        }

        table form button {
            background-color: #e74c3c;
        }

        table form button:hover {
            background-color: #c0392b;
        }

        table a button {
            background-color: #27ae60;
            margin-left: 5px;
        }

        table a button:hover {
            background-color: #2ecc71;
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
                width: 100%; /* Chiếm toàn bộ chiều rộng khi màn hình nhỏ */
            }

            table th,
            table td {
                font-size: 14px;
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
                <img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin
            </h2>
            <ul>
                <li><a href="manage_khoa.php"><i class="material-icons">school</i> Quản lý Khoa</a></li>
                <li><a href="manage_lop.php"><i class="material-icons">class</i> Quản lý Lớp</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="manage_giangvien.php"><i class="material-icons">person_outline</i> Quản lý Giảng viên</a></li>
                <li><a href="manage_monhoc.php"><i class="material-icons">book</i> Quản lý Môn học</a></li>
                <li><a href="manage_taikhoan.php" class="active"><i class="material-icons">account_circle</i> Phân Quyền</a></li>
                <li><a href="quanlyhocvu.php" class="active"><i class="material-icons">account_circle</i> Quản lý học vụ</a></li>
                <li><a href="hocphi.php" class="active"><i class="material-icons">account_circle</i> Quản lý học phí</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Phân Quyền</h1>
            
            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="usernameSearch" placeholder="Tìm kiếm theo Tên tài khoản" value="<?= htmlspecialchars($usernameSearch) ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <!-- Hiển thị thông báo -->
            <?php if ($message != ""): ?>
                    <div style="color: red; font-weight: bold; margin-bottom: 10px;">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>            
            <h2>Thêm Tài Khoản</h2>
            <!-- Form thêm tài khoản -->
            <form method="POST">
                <input type="text" name="username" placeholder="Tên tài khoản" required>
                <input type="password" name="password" placeholder="Mật khẩu" required>
                <input type="email" name="email" placeholder="Email" required>
                <select name="role" required>
                    <option value="">Chọn vai trò</option>
                    <?php while ($row = $roleList->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>"><?= $row['role'] ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add">Thêm</button>
            </form>

            <!-- Bảng hiển thị tài khoản -->
            <table border="1">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên tài khoản</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Mật khẩu</th> <!-- Thêm cột mật khẩu -->
                        <th>Hành động</th>
                    </tr>
                </thead>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['role_name'] ?></td>
                    <td><?= $row['password'] ?></td> <!-- Hiển thị mật khẩu -->
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa tài khoản này?')">Xóa</button>
                        </form>
                        <a href="update_taikhoan.php?id=<?= $row['id'] ?>"><button>Cập nhật</button></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>

        </section>
    </div>
</body>
</html>
