<?php
session_start();

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (isset($_GET['masv'])) {
    $MASV = $_GET['masv'];

    // Lấy thông tin môn học
    $stmt = $conn->prepare("SELECT * FROM hocphi WHERE masv = ?");
    $stmt->bind_param("s", $MASV);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        $_SESSION['notification'] = "Học phí không tồn tại!";
        header("Location: hocphi.php");
        exit;
    }
    $stmt->close();
}




// Xử lý cập nhật học phí
if (isset($_POST['update'])) {
    // Lấy dữ liệu từ form và gán vào các biến
    $maHocPhi = $_POST['maHocPhi'];
    $maSV = $_POST['maSV'];
    $namHoc = $_POST['namHoc'];
    $tongTien = $_POST['tongTien'];
    $soTienDaDong = $_POST['soTienDaDong'];
    $ngayDong = $_POST['ngayDong'];
    $hanDong = $_POST['hanDong'];

    // Kiểm tra các trường không được để trống và dữ liệu hợp lệ
    if (empty($maHocPhi) || empty($maSV) || empty($namHoc) || empty($tongTien) || empty($soTienDaDong) || empty($ngayDong) || empty($hanDong)) {
        $_SESSION['notification'] = "Tất cả các trường đều bắt buộc!";
    } elseif (!preg_match('/^\d+$/', $tongTien) || !preg_match('/^\d+$/', $soTienDaDong)) {
        $_SESSION['notification'] = "Số tiền phải là số!";
    } else {
        // Sử dụng prepared statement để bảo vệ khỏi SQL Injection
        $stmt = $conn->prepare("UPDATE hocphi SET namHoc = ?, tongTien = ?, soTienDaDong = ?, ngayDong = ?, hanDong = ? WHERE maHocPhi = ? AND maSV = ?");
        $stmt->bind_param("sssssss", $namHoc, $tongTien, $soTienDaDong, $ngayDong, $hanDong, $maHocPhi, $maSV);

        // Kiểm tra kết quả và lưu thông báo vào session
        if ($stmt->execute()) {
            $_SESSION['notification'] = "Cập nhật học phí thành công!";
        } else {
            $_SESSION['notification'] = "Lỗi khi cập nhật học phí: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật học phí</title>
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
            <!-- Sidebar content -->
            <h2><img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin </h2>
            <ul>
                <li><a href="manage_khoa.php"><i class="material-icons">school</i> Quản lý Khoa</a></li>
                <li><a href="manage_lop.php"><i class="material-icons">class</i> Quản lý Lớp</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="manage_giangvien.php"><i class="material-icons">person_outline</i> Quản lý Giảng viên</a></li>
                <li><a href="manage_monhoc.php"><i class="material-icons">book</i> Quản lý Môn học</a></li>
                <li><a href="manage_taikhoan.php"><i class="material-icons">account_circle</i> Phân Quyền</a></li>
                <li><a href="hocphi.php"><i class="material-icons">assessment</i> Quản lí học vụ</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>
        </nav>
        <section class="main-content">
            <h1>Cập nhật học phí</h1>

            <!-- Hiển thị thông báo nếu có -->
            <?php if (isset($_SESSION['notification'])): ?>
                <div class="notification">
                    <p><?= $_SESSION['notification']; ?></p>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>
            <form method="POST">
    <!-- Các input fields tương ứng với dữ liệu trong PHP -->
    <input type="text" name="maHocPhi" value="<?= htmlspecialchars($row['MAHOCPHI']) ?>" required readonly>
    <input type="text" name="maSV" value="<?= htmlspecialchars($row['MASV']) ?>" required readonly>
    <input type="text" name="namHoc" value="<?= htmlspecialchars($row['NAMHOC']) ?>" required>
    <input type="text" name="tongTien" value="<?= htmlspecialchars($row['TONGTIEN']) ?>" required>
    <input type="text" name="soTienDaDong" value="<?= htmlspecialchars($row['SOTIENDADONG']) ?>" required>
    <input type="date" name="ngayDong" value="<?= htmlspecialchars($row['NGAYDONG']) ?>" required>
    <input type="date" name="hanDong" value="<?= htmlspecialchars($row['HANDONG']) ?>" required>

    <!-- Nút Cập nhật -->
    <button type="submit" name="update">Cập nhật</button>
    
    <!-- Nút Quay lại -->
    <a href="hocphi.php" style="text-decoration: none; padding: 10px; background: #ccc; color: black; border-radius: 5px;">Quay lại</a>
</form>

        </section>
    </div>
</body>
</html>
