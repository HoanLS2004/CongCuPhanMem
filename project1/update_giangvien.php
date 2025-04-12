<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maGV = $_POST['maGV'];
    $tenGV = $_POST['tenGV'];
    $gioiTinh = $_POST['gioiTinh'];
    $ngaySinh = $_POST['ngaySinh'];
    $diaChi = $_POST['diaChi'];
    $sdt = $_POST['sdt'];
    $email = $_POST['email'];

    // Dùng prepared statement để bảo mật dữ liệu
    $stmt = $conn->prepare("UPDATE giangvien SET tenGV = ?, gioiTinh = ?, ngaySinh = ?, diaChi = ?, sdt = ?, email = ? WHERE maGV = ?");
    $stmt->bind_param("sssssss", $tenGV, $gioiTinh, $ngaySinh, $diaChi, $sdt, $email, $maGV);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Cập nhật thông tin giảng viên thành công!";
    } else {
        $_SESSION['message'] = "Cập nhật thông tin thất bại: " . $conn->error;
    }

    $stmt->close();

    // Tránh lỗi resubmit form
    header("Location: " . $_SERVER['PHP_SELF'] . "?maGV=" . urlencode($maGV) . 
           "&tenGV=" . urlencode($tenGV) . 
           "&gioiTinh=" . urlencode($gioiTinh) .
           "&ngaySinh=" . urlencode($ngaySinh) .
           "&diaChi=" . urlencode($diaChi) . 
           "&sdt=" . urlencode($sdt) . 
           "&email=" . urlencode($email));
    exit();
}

// Lấy thông tin giảng viên từ URL nếu cần thiết
$maGV = $_GET['maGV'];
$result = $conn->query("SELECT * FROM giangvien WHERE maGV = '$maGV'");
$giangvien = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin Giảng viên</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
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

        .message.error {
            background-color: #fceaea;
            color: #e74c3c;
            border: 1px solid #f5c6cb;
        }

        form input[type="text"],
        form input[type="date"],
        form select,
        form input[type="email"] {
            width: 100%;
            padding: 12px; 
            margin-bottom: 20px; 
            border: 1px solid #ccc; 
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            background-color: #f9f9f9; 
        }

        form button {
            padding: 14px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        form button:hover {
            background-color: #2ecc71;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<div class="container">
        <nav class="sidebar">
            <h2><img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin </h2>
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
        <section class="main-content">
            <h1>Cập nhật thông tin Giảng viên</h1>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="message <?= strpos($_SESSION['message'], 'thất bại') !== false ? 'error' : '' ?>">
                    <?= $_SESSION['message'] ?>
                </div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="maGV" value="<?= htmlspecialchars($giangvien['maGV']) ?>" readonly required>
                <input type="text" name="tenGV" value="<?= htmlspecialchars($giangvien['tenGV']) ?>" placeholder="Tên giảng viên" required>
                
                <!-- Thêm thông tin giới tính -->
                <label for="gioiTinh">Giới tính:</label>
                <select name="gioiTinh" required>
                    <option value="Nam" <?= $giangvien['gioiTinh'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="Nữ" <?= $giangvien['gioiTinh'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                    <option value="Khác" <?= $giangvien['gioiTinh'] === 'Khác' ? 'selected' : '' ?>>Khác</option>
                </select>

                <!-- Thêm thông tin ngày sinh -->
                <label for="ngaySinh">Ngày sinh:</label>
                <input type="date" name="ngaySinh" value="<?= htmlspecialchars($giangvien['ngaySinh']) ?>" required>

                <input type="text" name="diaChi" value="<?= htmlspecialchars($giangvien['diaChi']) ?>" placeholder="Địa chỉ">
                <input type="text" name="sdt" value="<?= htmlspecialchars($giangvien['sdt']) ?>" placeholder="Số điện thoại">
                <input type="email" name="email" value="<?= htmlspecialchars($giangvien['email']) ?>" placeholder="Email">

                <button type="submit">Cập nhật</button>
                <a href="manage_giangvien.php" style="text-decoration: none; background-color: #ccc; padding: 10px; color: black; border-radius: 5px;">Quay lại</a>
            </form>
        </section>
    </div>
</body>
</html>
