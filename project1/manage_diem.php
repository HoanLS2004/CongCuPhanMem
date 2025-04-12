<?php
session_start();

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra xem người dùng có đăng nhập và có phải giảng viên không
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php"); // Chuyển hướng nếu không phải Admin
    exit;
}

// Biến lưu trữ thông báo
$message = "";
$messageColor = "red"; // Mặc định thông báo lỗi sẽ hiển thị màu đỏ

// Xử lý thêm, sửa, xóa
if (isset($_POST['add'])) {
    $maSV = $_POST['maSV'];
    $maMH = $_POST['maMH'];
    $heso1 = $_POST['heso1'];
    $heso3 = $_POST['heso3'];
    $heso6 = $_POST['heso6'];

    // Kiểm tra mã sinh viên có tồn tại không
    $checkSV = $conn->query("SELECT * FROM sinhvien WHERE maSV = '$maSV'");
    if ($checkSV->num_rows == 0) {
        $message = "Mã sinh viên không tồn tại. Vui lòng kiểm tra lại.";
    } else {
        // Kiểm tra mã sinh viên và mã môn học đã có điểm chưa
        $checkExists = $conn->query("SELECT * FROM diem WHERE maSV = '$maSV' AND maMH = '$maMH'");
        if ($checkExists->num_rows > 0) {
            $message = "Sinh viên này đã có điểm cho môn học này!";
        } else {
            $tongDiem = $heso1 * 0.1 + $heso3 * 0.3 + $heso6 * 0.6;
            if ($conn->query("INSERT INTO diem (maSV, maMH, heso1, heso3, heso6, tongDiem) VALUES ('$maSV', '$maMH', '$heso1', '$heso3', '$heso6', '$tongDiem')")) {
                $message = "Thêm điểm thành công!";
                $messageColor = "green"; // Màu xanh lá cho thông báo thành công
            } else {
                $message = "Có lỗi xảy ra khi thêm điểm.";
            }
        }
    }
} elseif (isset($_POST['delete'])) {
    $maBD = $_POST['maBD'];
    if ($conn->query("DELETE FROM diem WHERE maBD='$maBD'")) {
        $message = "Xóa điểm thành công!";
        $messageColor = "green";
    } else {
        $message = "Có lỗi xảy ra khi xóa điểm.";
    }
}

// Lấy danh sách điểm
$result = $conn->query("SELECT diem.*, sinhvien.maSV, monhoc.tenMH 
    FROM diem
    JOIN sinhvien ON diem.maSV = sinhvien.maSV
    JOIN monhoc ON diem.maMH = monhoc.maMH");

// Lấy danh sách sinh viên và môn học
$sinhvienList = $conn->query("SELECT * FROM sinhvien");
$monhocList = $conn->query("SELECT * FROM monhoc");

// Khởi tạo biến tìm kiếm
$maSVSearch = "";

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $maSVSearch = $_POST['maSVSearch'];

    // Xây dựng câu truy vấn tìm kiếm
    $query = "SELECT diem.*, sinhvien.maSV, monhoc.tenMH 
              FROM diem
              JOIN sinhvien ON diem.maSV = sinhvien.maSV
              JOIN monhoc ON diem.maMH = monhoc.maMH
              WHERE 1=1"; // Điều kiện mặc định

    // Điều kiện tìm kiếm theo mã sinh viên
    if ($maSVSearch != '') {
        $query .= " AND diem.maSV = '$maSVSearch'";
    }

    $result = $conn->query($query);
} else {
    // Nếu không tìm kiếm, lấy tất cả dữ liệu
    $result = $conn->query("SELECT diem.*, sinhvien.maSV, monhoc.tenMH 
                            FROM diem
                            JOIN sinhvien ON diem.maSV = sinhvien.maSV
                            JOIN monhoc ON diem.maMH = monhoc.maMH");
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Điểm</title>
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
        form input[type="number"] {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(50% - 10px);
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
        table a button {
            background-color: orange;
            margin-left: 5px;
        }

        table a button:hover {
            background-color: #2ecc71;
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
            <h1>Quản lý Điểm</h1>

            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maSVSearch" placeholder="Nhập Mã Sinh Viên" value="<?= htmlspecialchars($maSVSearch) ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <!-- Hiển thị thông báo -->
            <?php if (!empty($message)): ?>
                <p style="color: <?php echo $messageColor; ?>; font-weight: bold;">
                    <?php echo $message; ?>
                </p>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="maSV" placeholder="Nhập Mã Sinh viên" required>
                
                <select name="maMH" required>
                    <option value="">Chọn Môn học</option>
                    <?php while ($row = $monhocList->fetch_assoc()): ?>
                        <option value="<?= $row['maMH'] ?>"><?= $row['tenMH'] ?></option>
                    <?php endwhile; ?>
                </select>
                
                <input type="number" step="0.01" name="heso1" placeholder="Hệ số 1" required>
                <input type="number" step="0.01" name="heso3" placeholder="Hệ số 3" required>
                <input type="number" step="0.01" name="heso6" placeholder="Hệ số 6" required>
                <button type="submit" name="add">Thêm</button>
            </form>
            
            <table border="1">
                <thead>
                    <tr>
                        <th>Mã Sinh viên</th>
                        <th>Môn học</th>
                        <th>Hệ số 1</th>
                        <th>Hệ số 3</th>
                        <th>Hệ số 6</th>
                        <th>Tổng điểm</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['maSV'] ?></td>
                        <td><?= $row['tenMH'] ?></td>
                        <td><?= $row['heso1'] ?></td>
                        <td><?= $row['heso3'] ?></td>
                        <td><?= $row['heso6'] ?></td>
                        <td><?= $row['tongDiem'] ?></td>
                        <td>
                            <!-- Xóa điểm -->
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="maBD" value="<?= $row['maBD'] ?>">
                                <button type="submit" name="delete">Xóa</button>
                            </form>
                            <!-- Cập nhật điểm -->
                            <a class="cn" href="update_diem.php?maBD=<?= $row['maBD'] ?>"><button type="button">Cập nhật</button></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </section>
    </div>
</body>
</html>
