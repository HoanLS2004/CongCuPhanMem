<?php
session_start();

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Lọc theo lớp và môn học nếu có
$classFilter = isset($_GET['maLop']) ? $_GET['maLop'] : '';
$subjectFilter = isset($_GET['maMH']) ? $_GET['maMH'] : '';

// Câu truy vấn báo cáo
$query = "SELECT sinhvien.maSV, sinhvien.tenSV, lop.tenLop, monhoc.tenMH, 
          AVG(diem.heso1 * 0.1 + diem.heso3 * 0.3 + diem.heso6 * 0.6) AS diemTrungBinh
          FROM diem
          JOIN sinhvien ON diem.maSV = sinhvien.maSV
          JOIN lop ON sinhvien.maLop = lop.maLop
          JOIN monhoc ON diem.maMH = monhoc.maMH
          WHERE 1";

// Thêm điều kiện lọc nếu có
if ($classFilter) {
    $query .= " AND sinhvien.maLop = '$classFilter'";
}

if ($subjectFilter) {
    $query .= " AND diem.maMH = '$subjectFilter'";
}

$query .= " GROUP BY sinhvien.maSV, monhoc.maMH";

// Thực thi truy vấn
$result = $conn->query($query);

// Lấy danh sách lớp và môn học
$classList = $conn->query("SELECT * FROM lop");
$subjectList = $conn->query("SELECT * FROM monhoc");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo Cáo Điểm Trung Bình</title>
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
                <li><a href="report.php"><i class="material-icons">assessment</i> Thống kê và Báo cáo</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Báo Cáo Điểm Trung Bình</h1>
            
            <!-- Form lọc báo cáo -->
            <form method="GET">
                <select name="maLop">
                    <option value="">Chọn lớp</option>
                    <?php while ($row = $classList->fetch_assoc()): ?>
                        <option value="<?= $row['maLop'] ?>" <?= ($classFilter == $row['maLop']) ? 'selected' : '' ?>>
                            <?= $row['tenLop'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <select name="maMH">
                    <option value="">Chọn môn học</option>
                    <?php while ($row = $subjectList->fetch_assoc()): ?>
                        <option value="<?= $row['maMH'] ?>" <?= ($subjectFilter == $row['maMH']) ? 'selected' : '' ?>>
                            <?= $row['tenMH'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Lọc</button>
                <button type="submit" formaction="export_report.php">Xuất Excel</button>
            </form>

            <!-- Bảng báo cáo -->
            <table border="1">
            <thead>
                <tr>
                    <th>Mã Sinh viên</th>
                    <th>Tên Sinh viên</th>
                    <th>Lớp học</th> <!-- Cột mới -->
                    <th>Môn học</th>
                    <th>Điểm Trung Bình</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['maSV'] ?></td>
                    <td><?= $row['tenSV'] ?></td>
                    <td><?= $row['tenLop'] ?></td> <!-- Giá trị lớp học -->
                    <td><?= $row['tenMH'] ?></td>
                    <td><?= number_format($row['diemTrungBinh'], 2) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>

        </section>
    </div>
</body>
</html>
