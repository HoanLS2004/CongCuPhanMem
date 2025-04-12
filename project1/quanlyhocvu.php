<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Xử lý tìm kiếm
$search = '';
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['masvSearch']);
}

// Thêm mới xử lý học vụ
if (isset($_POST['add'])) {
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);
    $tensv = mysqli_real_escape_string($conn, $_POST['tensv']);
    $loaixuly = mysqli_real_escape_string($conn, $_POST['loaixuly']);
    $ngayxuly = mysqli_real_escape_string($conn, $_POST['ngayxuly']);
    $lydo = mysqli_real_escape_string($conn, $_POST['lydo']);
    $quyetdinh = mysqli_real_escape_string($conn, $_POST['quyetdinh']);
    $nguoixuly = mysqli_real_escape_string($conn, $_POST['nguoixuly']);
    $trangthai = mysqli_real_escape_string($conn, $_POST['trangthai']);

    $query = "INSERT INTO xulyhocvu (MASV, TENSV, LOAIXULYHOCVU, NGAYXULY, LYDO, QUYETDINH, NGUOIXULY, TRANGTHAI) 
              VALUES ('$masv', '$tensv', '$loaixuly', '$ngayxuly', '$lydo', '$quyetdinh', '$nguoixuly', '$trangthai')";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Thêm mới thành công!";
    } else {
        $_SESSION['error'] = "Thêm mới thất bại: " . mysqli_error($conn);
    }
}

// Xóa xử lý học vụ
if (isset($_POST['delete'])) {
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);
    $query = "DELETE FROM xulyhocvu WHERE MASV = '$masv'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Xóa thành công!";
    } else {
        $_SESSION['error'] = "Xóa thất bại: " . mysqli_error($conn);
    }
}

// Truy vấn danh sách xử lý học vụ
$query = "SELECT * FROM xulyhocvu";
if (!empty($search)) {
    $query .= " WHERE MASV LIKE '%$search%'";
}
$result = mysqli_query($conn, $query);
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
        <h1>Quản lý Xử Lý Học Vụ</h1>

<!-- Form tìm kiếm -->
<form method="POST">
    <input type="text" name="masvSearch" placeholder="Tìm kiếm theo Mã SV" value="<?= htmlspecialchars($search) ?>">
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

<h2>Thêm Xử Lý Học Vụ</h2>
<form method="POST">
    <input type="text" name="masv" placeholder="Mã SV" required>
    <input type="text" name="tensv" placeholder="Tên SV" required>
    <input type="text" name="loaixuly" placeholder="Loại Xử Lý" required>
    <input type="date" name="ngayxuly" required>
    <input type="text" name="lydo" placeholder="Lý Do" required>
    <input type="text" name="quyetdinh" placeholder="Quyết Định" required>
    <input type="text" name="nguoixuly" placeholder="Người Xử Lý" required>
    <select name="trangthai" required>
        <option value="Đang xử lý">Đang xử lý</option>
        <option value="Hoàn tất">Hoàn tất</option>
    </select>
    <button type="submit" name="add">Thêm</button>
</form>

<h2>Danh sách Xử Lý Học Vụ</h2>
<table border="1">
    <thead>
        <tr>
            <th>Mã SV</th>
            <th>Tên SV</th>
            <th>Loại Xử Lý</th>
            <th>Ngày Xử Lý</th>
            <th>Lý Do</th>
            <th>Quyết Định</th>
            <th>Người Xử Lý</th>
            <th>Trạng Thái</th>
            <th>Hành động</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= htmlspecialchars($row['MASV']) ?></td>
            <td><?= htmlspecialchars($row['TENSV']) ?></td>
            <td><?= htmlspecialchars($row['LOAIXULYHOCVU']) ?></td>
            <td><?= htmlspecialchars($row['NGAYXULY']) ?></td>
            <td><?= htmlspecialchars($row['LYDO']) ?></td>
            <td><?= htmlspecialchars($row['QUYETDINH']) ?></td>
            <td><?= htmlspecialchars($row['NGUOIXULY']) ?></td>
            <td><?= htmlspecialchars($row['TRANGTHAI']) ?></td>
            <td>
                <!-- Xóa -->
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="masv" value="<?= htmlspecialchars($row['MASV']) ?>">
                    <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</button>
                </form>
                <!-- Cập nhật -->
                <a href="update_hocvu.php?masv=<?= urlencode($row['MASV']) ?>">
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
