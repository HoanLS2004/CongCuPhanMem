<?php
session_start(); // Khởi tạo session

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra nếu chưa đăng nhập hoặc không phải sinh viên, chuyển hướng đến trang đăng nhập
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}

// Lấy mã sinh viên từ session
$maSV = $_SESSION['maSV'] ?? '';

// Lấy tên môn học từ form tìm kiếm (nếu có)
$tenMH = $_POST['tenMH'] ?? '';

// Kiểm tra nếu mã sinh viên không tồn tại
if (empty($maSV)) {
    die("Mã sinh viên không tồn tại. Vui lòng đăng nhập lại.");
}

// Truy vấn thông tin sinh viên cùng với tên lớp
$queryStudent = $conn->prepare("
    SELECT sinhvien.maSV, sinhvien.tenSV, sinhvien.gioiTinh, sinhvien.ngaySinh, sinhvien.diaChi, sinhvien.sdt, sinhvien.email, lop.tenLop 
    FROM sinhvien
    JOIN lop ON sinhvien.maLop = lop.maLop
    WHERE sinhvien.maSV = ?
");
$queryStudent->bind_param("i", $maSV);
$queryStudent->execute();
$resultStudent = $queryStudent->get_result();
$studentInfo = $resultStudent->fetch_assoc();

// Truy vấn điểm của sinh viên
$queryGrades = $conn->prepare(
    "SELECT diem.*, monhoc.tenMH 
    FROM diem
    JOIN monhoc ON diem.maMH = monhoc.maMH
    WHERE diem.maSV = ? AND monhoc.tenMH LIKE ?"
);
$likeTenMH = '%' . $tenMH . '%';
$queryGrades->bind_param("ss", $maSV, $likeTenMH);
$queryGrades->execute();
$resultGrades = $queryGrades->get_result();
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

        /* Thông tin sinh viên */
        .student-info {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #ecf0f1;
            border-radius: 5px;
            box-shadow: 0px 2px 4px rgba(0, 0, 0, 0.1);
        }

        .student-info p {
            margin: 5px 0;
            font-size: 16px;
            color: #2c3e50;
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
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #ffffff;
            border-radius: 5px;
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
        }

        table tbody tr:hover {
            background-color: #dfe6e9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 10px;
            }

            form input[type="text"] {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <h2>
                <img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Sinh Viên
            </h2>
            <ul>
                <li><a href="student_home.php"><i class="material-icons">home</i> Trang Chủ</a></li>
                <li><a href="student_view_grades.php"><i class="material-icons">visibility</i> Xem Điểm</a></li>
                <li><a href="sv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
                <li><a href="donate_info.php"><i class="material-icons">assessment</i>  Thông tin học phí</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Xem Điểm Sinh Viên</h1>

            <!-- Thông tin sinh viên -->
            <div class="student-info">
                <p><strong>Mã Sinh Viên:</strong> <?= htmlspecialchars($studentInfo['maSV']) ?></p>
                <p><strong>Tên Sinh Viên:</strong> <?= htmlspecialchars($studentInfo['tenSV']) ?></p>
                <p><strong>Giới Tính:</strong> <?= htmlspecialchars($studentInfo['gioiTinh']) ?></p>
                <p><strong>Ngày Sinh:</strong> <?= htmlspecialchars($studentInfo['ngaySinh']) ?></p>
                <p><strong>Địa Chỉ:</strong> <?= htmlspecialchars($studentInfo['diaChi']) ?></p>
                <p><strong>Số Điện Thoại:</strong> <?= htmlspecialchars($studentInfo['sdt']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($studentInfo['email']) ?></p>
                <p><strong>Lớp:</strong> <?= htmlspecialchars($studentInfo['tenLop']) ?></p>
            </div>


            <!-- Form tìm kiếm môn học -->
            <form method="POST">
                <input type="text" name="tenMH" placeholder="Nhập Tên Môn Học" value="<?= htmlspecialchars($tenMH) ?>">
                <button type="submit">Tìm Kiếm</button>
            </form>

            <!-- Bảng điểm -->
            <table>
                <thead>
                    <tr>
                        <th>Tên Môn Học</th>
                        <th>Hệ Số 1</th>
                        <th>Hệ Số 3</th>
                        <th>Hệ Số 6</th>
                        <th>Tổng Điểm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultGrades->num_rows > 0): ?>
                        <?php while ($row = $resultGrades->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['tenMH']) ?></td>
                                <td><?= htmlspecialchars($row['heso1']) ?></td>
                                <td><?= htmlspecialchars($row['heso3']) ?></td>
                                <td><?= htmlspecialchars($row['heso6']) ?></td>
                                <td><?= htmlspecialchars($row['tongDiem']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Không tìm thấy dữ liệu điểm.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
