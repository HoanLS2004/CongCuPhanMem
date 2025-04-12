<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit;
}

// Khởi tạo biến cho việc tìm kiếm
$maSVSearch = $_POST['maSVSearch'] ?? '';
$tenMHSearch = $_POST['tenMHSearch'] ?? '';

$query = "SELECT diem.*, sinhvien.maSV, sinhvien.tenSV, monhoc.tenMH 
          FROM diem
          JOIN sinhvien ON diem.maSV = sinhvien.maSV
          JOIN monhoc ON diem.maMH = monhoc.maMH
          WHERE 1";


if (!empty($maSVSearch)) {
    $query .= " AND diem.maSV LIKE '%$maSVSearch%'";
}

if (!empty($tenMHSearch)) {
    $query .= " AND monhoc.tenMH LIKE '%$tenMHSearch%'";
}

$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xem Điểm Sinh Viên</title>
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
        form input[type="email"] {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(30% - 10px);
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
                <li><a href="manage_diem.php"><i class="material-icons">edit</i> Nhập Điểm</a></li>
                <li><a href="teacher_view_grades.php"><i class="material-icons">visibility</i> Xem Điểm</a></li>
                <li><a href="teacher_report.php"><i class="material-icons">assessment</i> Báo Cáo</a></li>
                <li><a href="gv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Danh Sách Điểm Sinh Viên</h1>

            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maSVSearch" placeholder="Nhập Mã Sinh Viên" value="<?= htmlspecialchars($maSVSearch) ?>">
                <input type="text" name="tenMHSearch" placeholder="Nhập Tên Môn Học" value="<?= htmlspecialchars($tenMHSearch ?? '') ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
                <button type="submit" formaction="export_grades1.php">Xuất Excel</button>
            </form>
            <table border="1">
                <tr>
                    <thead>
                        <th>Mã Sinh Viên</th>
                        <th>Tên Sinh Viên</th>
                        <th>Tên Môn Học</th>
                        <th>Hệ Số 1</th>
                        <th>Hệ Số 3</th>
                        <th>Hệ Số 6</th>
                        <th>Tổng Điểm</th>
                    </thead>
                </tr>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['maSV'] ?></td>
                        <td><?= $row['tenSV'] ?></td>
                        <td><?= $row['tenMH'] ?></td>
                        <td><?= $row['heso1'] ?></td>
                        <td><?= $row['heso3'] ?></td>
                        <td><?= $row['heso6'] ?></td>
                        <td><?= $row['tongDiem'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Không tìm thấy điểm cho mã sinh viên này.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </section>
    </div>
</body>
</html>
