<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải Admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php"); // Chuyển hướng nếu không phải Admin
    exit;
}

// Xử lý thêm môn học
if (isset($_POST['add'])) {
    $maMH = $_POST['maMH'];
    $tenMH = $_POST['tenMH'];
  

    // Kiểm tra mã môn học đã tồn tại chưa
    $stmt = $conn->prepare("SELECT * FROM monhoc WHERE maMH = ?");
    $stmt->bind_param("s", $maMH);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['notification'] = "Mã môn học đã tồn tại! Vui lòng nhập mã khác.";
        $stmt->close();
        header("Location: manage_monhoc.php");
        exit;
    }

    // Kiểm tra tên môn học đã tồn tại chưa
    $stmt = $conn->prepare("SELECT * FROM monhoc WHERE tenMH = ?");
    $stmt->bind_param("s", $tenMH);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['notification'] = "Tên môn học đã tồn tại! Vui lòng nhập tên khác.";
        $stmt->close();
        header("Location: manage_monhoc.php");
        exit;
    }

    // Sử dụng prepared statements để bảo vệ khỏi SQL Injection
    $stmt = $conn->prepare("INSERT INTO monhoc (maMH, tenMH) VALUES (?, ?)");
    $stmt->bind_param("ss", $maMH, $tenMH);

    if ($stmt->execute()) {
        $_SESSION['notification'] = "Môn học đã được thêm thành công!";
    } else {
        $_SESSION['notification'] = "Lỗi khi thêm môn học: " . $stmt->error;
    }
    $stmt->close();
    
    // Chuyển hướng kèm thông báo trong query string
    header("Location: manage_monhoc.php");
    exit;
} elseif (isset($_POST['delete'])) {
    $maMH = $_POST['maMH'];

    // Kiểm tra xem môn học có bản ghi tham chiếu không (ví dụ, trong bảng `diem`)
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM diem WHERE maMH = ?");
    $stmt->bind_param("s", $maMH);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Nếu có bản ghi tham chiếu, không cho phép xóa và thông báo
        $_SESSION['notification'] = "Không thể xóa môn học này. Có dữ liệu liên quan trong bảng khác (ví dụ: bảng điểm).";
    } else {
        // Nếu không có bản ghi tham chiếu, tiến hành xóa
        $stmt = $conn->prepare("DELETE FROM monhoc WHERE maMH = ?");
        $stmt->bind_param("s", $maMH);

        if ($stmt->execute()) {
            $_SESSION['notification'] = "Môn học đã được xóa thành công!";
        } else {
            $_SESSION['notification'] = "Lỗi khi xóa môn học: " . $stmt->error;
        }
    }

    $stmt->close();
    
    // Chuyển hướng kèm thông báo
    header("Location: manage_monhoc.php");
    exit;
}

// Khởi tạo biến tìm kiếm
$maMHSearch = '';

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $maMHSearch = $_POST['maMHSearch'];

    // Xây dựng câu truy vấn tìm kiếm
    $query = "SELECT monhoc.* 
              FROM monhoc 
              WHERE 1=1"; // Điều kiện mặc định

    // Điều kiện tìm kiếm theo mã môn học
    if ($maMHSearch != '') {
        $query .= " AND monhoc.maMH LIKE '%$maMHSearch%'";
    }

    $result = $conn->query($query);
} else {
    // Nếu không tìm kiếm, lấy tất cả dữ liệu
    $result = $conn->query("SELECT monhoc.*
                             FROM monhoc 
                             ");
}

// Lấy danh sách môn học
$result = $conn->query("SELECT monhoc.*
    FROM monhoc 
    ");


// Hiển thị thông báo nếu có
$notification = isset($_SESSION['notification']) ? $_SESSION['notification'] : '';
unset($_SESSION['notification']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Môn học</title>
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
        .notification {
        margin: 20px 0;
        padding: 15px;
        border-radius: 5px;
        font-size: 16px;
        text-align: center;
        }

        .notification.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .notification.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .notification.info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .notification.warning {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
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
        /* Nút Cập nhật: Màu xanh lá cây */
        .button-update {
            background-color: #4CAF50; /* Màu xanh lá cây */
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .button-update:hover {
            background-color: #45a049; /* Màu xanh lá cây tối hơn khi hover */
        }
        .a1{
            background-color: #27ae60;
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
                <li><a href="manage_monhoc.php" class="active"><i class="material-icons">book</i> Quản lý Môn học</a></li>
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
            <h1>Quản lý Môn học</h1>

            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maMHSearch" placeholder="Tìm kiếm theo Mã Môn học" value="<?= htmlspecialchars($maMHSearch) ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <?php if (isset($_SESSION['success'])): ?>
                <div style="color: green; font-weight: bold; margin-bottom: 10px;">
                    <?= $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="color: red; font-weight: bold; margin-bottom: 10px;">
                    <?= $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Hiển thị thông báo -->
            <?php if ($notification): ?>
                <div style="color: <?= strpos($notification, 'Lỗi') === false ? 'green' : 'red'; ?>; font-weight: bold; margin-bottom: 10px;">
                    <?= $notification; ?>
                </div>
            <?php endif; ?>

            <h2>Thêm Môn Học</h2>
            <!-- Form thêm môn học -->
            <form method="POST">
                <input type="text" name="maMH" placeholder="Mã môn học" required>
                <input type="text" name="tenMH" placeholder="Tên môn học" required>

               


                <button type="submit" name="add">Thêm</button>
            </form>

            <!-- Hiển thị danh sách môn học -->
            <h2>Danh sách Môn học</h2>
            <table border="1">
                <thead>
                    <tr>
                        <th>Mã môn học</th>
                        <th>Tên môn học</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['maMH'] ?></td>
                    <td><?= $row['tenMH'] ?></td>
                    
                    <td>
                        <!-- Nút Xóa -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="maMH" value="<?= $row['maMH'] ?>">
                            <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa môn học  này?')">Xóa</button>
                        </form>
                        <!-- Nút Cập nhật -->
                        <form method="GET" action="update_monhoc.php" style="display: inline;">
                            <input type="hidden" name="maMH" value="<?= $row['maMH'] ?>">
                            <button class='a1' type="submit">Cập nhật</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </div>
</body>
</html>
