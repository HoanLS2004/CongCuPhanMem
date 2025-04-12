<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải Admin không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit;
}

// Thêm giảng viên
if (isset($_POST['add'])) {
    $maGV = trim($_POST['maGV']);
    $tenGV = trim($_POST['tenGV']);
    $diaChi = trim($_POST['diaChi']);
    $sdt = trim($_POST['sdt']);
    $email = trim($_POST['email']);
    $ngaySinh = $_POST['ngaySinh'];
    $gioiTinh = $_POST['gioiTinh'];

    // Kiểm tra thông tin
    if (empty($maGV) || empty($tenGV) || empty($gioiTinh) || empty($ngaySinh)) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (strlen($sdt) > 11 || !preg_match('/^\d+$/', $sdt)) {
        $_SESSION['error'] = "Số điện thoại không hợp lệ!";
    } else {
        $stmt = $conn->prepare("SELECT * FROM giangvien WHERE maGV = ?");
        $stmt->bind_param("s", $maGV);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Mã giảng viên đã tồn tại!";
        } else {
            $stmt = $conn->prepare("INSERT INTO giangvien (maGV, tenGV, diaChi, sdt, email, ngaySinh, gioiTinh) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $maGV, $tenGV, $diaChi, $sdt, $email, $ngaySinh, $gioiTinh);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Thêm giảng viên thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi thêm: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Xóa giảng viên
if (isset($_POST['delete'])) {
    $maGV = $_POST['maGV'];
    $stmt = $conn->prepare("DELETE FROM giangvien WHERE maGV = ?");
    $stmt->bind_param("s", $maGV);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Xóa giảng viên thành công!";
    } else {
        $_SESSION['error'] = "Lỗi khi xóa: " . $stmt->error;
    }
    $stmt->close();
}

// Tìm kiếm giảng viên
$query = "SELECT * FROM giangvien";
if (isset($_POST['search'])) {
    $maGVSearch = trim($_POST['maGVSearch']);
    if (!empty($maGVSearch)) {
        $query .= " WHERE maGV LIKE '%" . $conn->real_escape_string($maGVSearch) . "%'";
    }
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giảng viên</title>
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
        form input[type="date"],
        form select,
        form input[type="email"] {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(50% - 10px);
            box-sizing: border-box;
            background-color: #fdfdfd;
            transition: border-color 0.3s;
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

        <section class="main-content">
            <h1>Quản lý Giảng viên</h1>

            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maGVSearch" placeholder="Tìm kiếm theo Mã Giảng viên">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <!-- Thêm thông báo -->
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
            <!-- Form thêm giảng viên -->
            <h2>Thêm Giảng viên</h2>
            <form method="POST">
                <input type="text" name="maGV" placeholder="Mã giảng viên" required>
                <input type="text" name="tenGV" placeholder="Tên giảng viên" required>
                <select name="gioiTinh" required>
                    <option value="">Chọn giới tính</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                </select>
                <input type="date" name="ngaySinh" required>
                <input type="text" name="diaChi" placeholder="Địa chỉ">
                <input type="text" name="sdt" placeholder="Số điện thoại">
                <input type="email" name="email" placeholder="Email">
                <button type="submit" name="add">Thêm</button>
            </form>

            <!-- Hiển thị danh sách giảng viên -->
            <table>
                <thead>
                    <tr>
                        <th>Mã giảng viên</th>
                        <th>Tên giảng viên</th>
                        <th>Giới tính</th>
                        <th>Ngày sinh</th>
                        <th>Địa chỉ</th>
                        <th>Số điện thoại</th>
                        <th>Email</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['maGV']) ?></td>
                            <td><?= htmlspecialchars($row['tenGV']) ?></td>
                            <td><?= htmlspecialchars($row['gioiTinh']) ?></td>
                            <td><?= htmlspecialchars($row['ngaySinh']) ?></td>
                            <td><?= htmlspecialchars($row['diaChi']) ?></td>
                            <td><?= htmlspecialchars($row['sdt']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="maGV" value="<?= htmlspecialchars($row['maGV']) ?>">
                    <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?')">Xóa</button>
                </form>
                <a href="update_giangvien.php?maGV=<?= htmlspecialchars($row['maGV']) ?>">
                    <button type="button">Cập nhật</button>
                </a>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
