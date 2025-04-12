<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải Admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php"); // Chuyển hướng nếu không phải Admin
    exit;
}

// Xử lý thêm khoa
if (isset($_POST['add'])) {
    $maKH = trim($_POST['maKH']);
    $tenKH = trim($_POST['tenKH']);
    $lienheKH = trim($_POST['lienheKH']);

    // Kiểm tra các trường bắt buộc
    if (empty($maKH) || empty($tenKH)) {
        $_SESSION['error'] = "Mã khoa và tên khoa không được để trống!";
    } elseif (empty($lienheKH)) {
        $_SESSION['error'] = "Số điện thoại không được để trống!";
    } elseif (strlen($lienheKH) > 11) {
        $_SESSION['error'] = "Số điện thoại không được vượt quá 11 số!";
    } elseif (!preg_match('/^\d+$/', $lienheKH)) {
        $_SESSION['error'] = "Số điện thoại chỉ được chứa chữ số!";
    } else {
        // Kiểm tra mã khoa đã tồn tại
        $stmt = $conn->prepare("SELECT * FROM khoa WHERE maKH = ?");
        $stmt->bind_param("s", $maKH);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Mã khoa đã tồn tại! Vui lòng nhập mã khác.";
        } else {
            // Kiểm tra tên khoa đã tồn tại
            $stmt = $conn->prepare("SELECT * FROM khoa WHERE tenKH = ?");
            $stmt->bind_param("s", $tenKH);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Tên khoa đã tồn tại! Vui lòng nhập tên khác.";
            } else {
                // Thêm khoa vào cơ sở dữ liệu
                $stmt = $conn->prepare("INSERT INTO khoa (maKH, tenKH, lienheKH) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $maKH, $tenKH, $lienheKH);
                if ($stmt->execute()) {
                    $_SESSION['success'] = "Thêm khoa thành công!";
                } else {
                    $_SESSION['error'] = "Lỗi khi thêm khoa! Vui lòng thử lại.";
                }
            }
        }
    }
}

// Xử lý xóa khoa
if (isset($_POST['delete'])) {
    $maKH = $_POST['maKH'];

    // Kiểm tra ràng buộc với bảng `lop`
    $check = $conn->prepare("SELECT COUNT(*) AS count FROM lop WHERE maKH = ?");
    $check->bind_param("s", $maKH);
    $check->execute();
    $result = $check->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $_SESSION['error'] = "Không thể xóa khoa này. Có lớp học đang tham chiếu đến khoa!";
    } else {
        // Nếu không có bản ghi tham chiếu, thực hiện xóa
        $stmt = $conn->prepare("DELETE FROM khoa WHERE maKH = ?");
        $stmt->bind_param("s", $maKH);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Xóa khoa thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa khoa! Vui lòng thử lại.";
        }
    }
}

// Lấy danh sách khoa
$result = $conn->query("SELECT * FROM khoa");

// Khởi tạo biến tìm kiếm
$maKHSearch = '';

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $maKHSearch = $_POST['maKHSearch'];

    // Xây dựng câu truy vấn tìm kiếm
    $query = "SELECT * FROM khoa WHERE 1=1"; // Điều kiện mặc định

    // Điều kiện tìm kiếm theo mã khoa
    if ($maKHSearch != '') {
        $query .= " AND maKH LIKE '%$maKHSearch%'";
    }

    $result = $conn->query($query);
} else {
    // Nếu không tìm kiếm, lấy tất cả dữ liệu
    $result = $conn->query("SELECT * FROM khoa");
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

        /* Form Styling */
        form {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        form input[type="text"],
        form select {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(30% - 10px); /* Đảm bảo 3 ô nằm ngang, trừ khoảng cách */
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
        <nav class="sidebar">
            <h2><img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin</h2>
            <ul>
                <li><a href="manage_khoa.php"><i class="material-icons">school</i> Quản lý Khoa</a></li>
                <li><a href="manage_lop.php"><i class="material-icons">class</i> Quản lý Lớp</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="manage_giangvien.php"><i class="material-icons">person_outline</i> Quản lý Giảng viên</a></li>
                <li><a href="manage_monhoc.php"><i class="material-icons">book</i> Quản lý Môn học</a></li>
                <li><a href="manage_taikhoan.php"><i class="material-icons">account_circle</i> Phân Quyền</a></li>
                <li><a href="quanlyhocvu.php" class="active"><i class="material-icons">account_circle</i> Quản lý học vụ</a></li>
                <li><a href="hocphi.php" class="active"><i class="material-icons">account_circle</i> Quản lý học phí</a></li>
                
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Quản lý Khoa</h1>

            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maKHSearch" placeholder="Tìm kiếm theo Mã Khoa" value="<?= htmlspecialchars($maKHSearch) ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <?php if (isset($_SESSION['success'])): ?>
                <div style="color: green; font-weight: bold;">
                    <?= $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="color: red; font-weight: bold;">
                    <?= $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <h2>Thêm Khoa</h2>
            <form method="POST">
                <input type="text" name="maKH" placeholder="Mã khoa" required>
                <input type="text" name="tenKH" placeholder="Tên khoa" required>
                <input type="text" name="lienheKH" placeholder="Liên hệ khoa">
                <button type="submit" name="add">Thêm</button>
            </form>

            <h2>Danh sách Khoa</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Mã khoa</th>
                        <th>Tên khoa</th>
                        <th>Liên hệ khoa</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['maKH']) ?></td>
                        <td><?= htmlspecialchars($row['tenKH']) ?></td>
                        <td><?= htmlspecialchars($row['lienheKH']) ?></td>
                        <td>
                            <!-- Xóa khoa -->
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="maKH" value="<?= htmlspecialchars($row['maKH']) ?>">
                                <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa khoa này?')">Xóa</button>
                            </form>
                            <!-- Cập nhật khoa -->
                            <a href="update_khoa.php?maKH=<?= urlencode($row['maKH']) ?>&tenKH=<?= urlencode($row['tenKH']) ?>&lienheKH=<?= urlencode($row['lienheKH']) ?>">
                                <button type="button">Cập nhật</button>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
