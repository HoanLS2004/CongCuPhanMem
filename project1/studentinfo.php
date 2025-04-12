
<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (isset($_SESSION['username'])) {
    $sql = "SELECT * FROM sinhvien WHERE maSV = ?";
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Sinh Viên</title>
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

        form input[type="text"],
        form select,
        form input[type="password"] {
            font-size: 16px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: calc(100% - 10px);
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
        <section class="main-content">
            
        <h2>Thông tin Sinh viên</h2>
    <table>
        <thead>
            <tr>
                <th>Mã sinh viên</th>
                <th>Tên sinh viên</th>
                <th>Giới tính</th>
                <th>Ngày sinh</th>
                <th>Địa chỉ</th>
                <th>Số điện thoại</th>
                <th>Email</th>
                <th>Tên lớp</th>
                <th>CCCD</th>
                <th>Ngày cấp</th>
                <th>Nơi cấp</th>
                <th>Hộ khẩu</th>
                <th>Dân tộc</th>
                <th>Tôn giáo</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['maSV'] ?></td>
                    <td><?= $row['tenSV'] ?></td>
                    <td><?= $row['gioiTinh'] ?></td>
                    <td><?= $row['ngaySinh'] ?></td>
                    <td><?= $row['diaChi'] ?></td>
                    <td><?= $row['sdt'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td><?= $row['maLop'] ?></td>
                    <td><?= $row['CCCD'] ?></td>
                    <td><?= $row['ngayCap'] ?></td>
                    <td><?= $row['noiCap'] ?></td>
                    <td><?= $row['hoKhauThuongTru'] ?></td>
                    <td><?= $row['danToc'] ?></td>
                    <td><?= $row['tonGiao'] ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

        </section>
    </div>
</body>
</html>
