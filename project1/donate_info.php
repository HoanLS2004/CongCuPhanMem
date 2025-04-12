<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (isset($_SESSION['username'])) {
    $sql = "SELECT * FROM hocphi WHERE maSV = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    echo "Chưa đăng nhập!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Học phí</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* Nội dung chính */
        .main-content {
            flex: 1;
            padding: 20px;
            background-color: #ffffff;
            border-left: 1px solid #ddd;
            box-shadow: inset 0px 0px 5px rgba(0, 0, 0, 0.1);
        }

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
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2>
                <img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Sinh Viên
            </h2>
            <ul>
                <li><a href="student_home.php"><i class="material-icons">home</i> Trang Chủ</a></li>
                <li><a href="studentinfo.php"><i class="material-icons">info</i> Thông tin cá nhân</a></li>
                <li><a href="sv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
                <li><a href="donate_info.php"><i class="material-icons">assessment</i>  Thông tin học phí</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="main-content">
            <h1>Quản lý Học phí</h1>

            <!-- Bảng hiển thị học phí -->
            <table border="1">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Mã Học Phí</th>
                        <th>Mã Sinh Viên</th>
                        <th>Năm Học</th>
                        <th>Tổng Tiền (VNĐ)</th>
                        <th>Số Tiền Đã Thanh Toán</th>
                        <th>Ngày đóng</th>
                        <th>Hạn đóng</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php $stt = 1; ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $stt++ ?></td>
                                <td><?= htmlspecialchars($row['MAHOCPHI']) ?></td>
                                <td><?= htmlspecialchars($row['MASV']) ?></td>
                                <td><?= htmlspecialchars($row['NAMHOC']) ?></td>
                                <td><?= number_format($row['TONGTIEN']) ?> VNĐ</td>
                                <td><?= number_format($row['SOTIENDADONG']) ?> VNĐ</td>
                                <td><?= htmlspecialchars($row['NGAYDONG']) ?> VNĐ</td>
                                <td><?= htmlspecialchars($row['HANDONG']) ?></td> <!-- Hiển thị Hạn Đóng -->
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Không có dữ liệu học phí</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
