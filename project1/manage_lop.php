<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải Admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php"); // Chuyển hướng nếu không phải Admin
    exit;
}

// Lấy danh sách khoa
$khoaList = $conn->query("SELECT * FROM khoa");

// Xử lý thêm và xóa lớp
if (isset($_POST['add'])) {
    $maLop = $_POST['maLop'];
    $tenLop = $_POST['tenLop'];
    $maKH = $_POST['maKH'];

    // Kiểm tra mã lớp có trùng không
    $checkMaLop = $conn->query("SELECT * FROM lop WHERE maLop = '$maLop'");
    if ($checkMaLop->num_rows > 0) {
        $_SESSION['error'] = "Mã lớp đã tồn tại. Vui lòng nhập mã khác.";
    } else {
        // Kiểm tra tên lớp có trùng không
        $checkTenLop = $conn->query("SELECT * FROM lop WHERE tenLop = '$tenLop'");
        if ($checkTenLop->num_rows > 0) {
            $_SESSION['error'] = "Tên lớp đã tồn tại. Vui lòng nhập tên khác.";
        } else {
            // Thêm lớp vào cơ sở dữ liệu
            if ($conn->query("INSERT INTO lop (maLop, tenLop, maKH) VALUES ('$maLop', '$tenLop', '$maKH')")) {
                $_SESSION['success'] = "Thêm lớp thành công!";
            } else {
                $_SESSION['error'] = "Có lỗi xảy ra khi thêm lớp.";
            }
        }
    }
} elseif (isset($_POST['delete'])) {
    $maLop = $_POST['maLop'];

    // Kiểm tra xem lớp có bản ghi tham chiếu trong bảng `sinhvien` không
    $check = $conn->prepare("SELECT COUNT(*) AS count FROM sinhvien WHERE maLop = ?");
    $check->bind_param("s", $maLop);
    $check->execute();
    $result = $check->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // Nếu có bản ghi tham chiếu, không cho phép xóa
        $_SESSION['error'] = "Không thể xóa lớp này. Có sinh viên đang thuộc lớp này!";
    } else {
        // Nếu không có bản ghi tham chiếu, thực hiện xóa
        $stmt = $conn->prepare("DELETE FROM lop WHERE maLop = ?");
        $stmt->bind_param("s", $maLop);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Xóa lớp thành công!";
        } else {
            $_SESSION['error'] = "Có lỗi xảy ra khi xóa lớp.";
        }
    }
}

// Lấy danh sách lớp và khoa
$result = $conn->query("SELECT lop.*, khoa.tenKH FROM lop JOIN khoa ON lop.maKH = khoa.maKH");
$khoaList = $conn->query("SELECT * FROM khoa");

// Khởi tạo biến tìm kiếm
$maLopSearch = '';

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $maLopSearch = $_POST['maLopSearch'];

    // Xây dựng câu truy vấn tìm kiếm
    $query = "SELECT lop.*, khoa.tenKH FROM lop JOIN khoa ON lop.maKH = khoa.maKH WHERE 1=1"; // Điều kiện mặc định

    // Điều kiện tìm kiếm theo mã lớp
    if ($maLopSearch != '') {
        $query .= " AND lop.maLop LIKE '%$maLopSearch%'";
    }

    $result = $conn->query($query);
} else {
    // Nếu không tìm kiếm, lấy tất cả dữ liệu
    $result = $conn->query("SELECT lop.*, khoa.tenKH FROM lop JOIN khoa ON lop.maKH = khoa.maKH");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Lớp</title>
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
            width: calc(30% - 5px); /* Đảm bảo 3 ô nằm ngang, trừ khoảng cách */
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
            <h1>Quản lý Lớp</h1>
            
            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maLopSearch" placeholder="Tìm kiếm theo Mã Lớp" value="<?= htmlspecialchars($maLopSearch) ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <h2>Thêm lớp</h2>
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

            <!-- Form Thêm lớp mới -->
            <form method="POST">
                <input type="text" name="maLop" placeholder="Mã lớp" required>
                <input type="text" name="tenLop" placeholder="Tên lớp" required>
                <select name="maKH" required>
                    <option value="">Chọn khoa</option>
                    <?php while ($row = $khoaList->fetch_assoc()): ?>
                        <option value="<?= $row['maKH'] ?>"><?= $row['tenKH'] ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add">Thêm</button>
            </form>

            <!-- Bảng Danh sách lớp -->
            <table border="1">
                <thead>
                    <tr>
                        <th>Mã lớp</th>
                        <th>Tên lớp</th>
                        <th>Tên khoa</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['maLop'] ?></td>
                    <td><?= $row['tenLop'] ?></td>
                    <td><?= $row['tenKH'] ?></td>
                    <td>
                        <!-- Form Xóa lớp -->
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="maLop" value="<?= $row['maLop'] ?>">
                            <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa lớp này?')">Xóa</button>
                        </form>


                        <!-- Nút Cập nhật thông tin lớp -->
                        <a href="update_lop.php?maLop=<?= htmlspecialchars($row['maLop']) ?>">
                            <button type="button">Cập nhật</button>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </div>
</body>
</html>
