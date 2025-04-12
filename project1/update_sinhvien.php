<?php
session_start();

require 'db_connect.php'; // Kết nối cơ sở dữ liệu

if (isset($_POST['update'])) {
    $maSV = trim($_POST['maSV']);
    $tenSV = trim($_POST['tenSV']);
    $gioiTinh = trim($_POST['gioiTinh']);
    $ngaySinh = trim($_POST['ngaySinh']);
    $diaChi = trim($_POST['diaChi']);
    $sdt = trim($_POST['sdt']);
    $email = trim($_POST['email']);
    $maLop = trim($_POST['maLop']);
    $CCCD = trim($_POST['CCCD']);
    $ngayCap = trim($_POST['ngayCap']);
    $noiCap = trim($_POST['noiCap']);
    $hoKhauThuongTru = trim($_POST['hoKhauThuongTru']);
    $danToc = trim($_POST['danToc']);
    $tonGiao = trim($_POST['tonGiao']);

    // Kiểm tra các trường không được để trống
    if (empty($maSV) || empty($tenSV) || empty($gioiTinh) || empty($ngaySinh) || empty($diaChi) || empty($sdt) || empty($email) || empty($maLop)|| 
    empty($CCCD) || empty($ngayCap) || empty($noiCap) || empty($hoKhauThuongTru) || empty($danToc) || empty($tonGiao)) {
        $_SESSION['notification'] = "Tất cả các trường đều bắt buộc!";
    } elseif (!preg_match('/^\d+$/', $sdt)) {
        $_SESSION['notification'] = "Số điện thoại chỉ được chứa chữ số!";
    } elseif (strlen($sdt) > 11) {
        $_SESSION['notification'] = "Số điện thoại không được vượt quá 11 số!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['notification'] = "Email không hợp lệ!";
    } else {
        // Kiểm tra lớp có tồn tại không
        $checkLop = $conn->query("SELECT * FROM lop WHERE maLop = '$maLop'");
        if ($checkLop->num_rows == 0) {
            $_SESSION['notification'] = "Lớp không tồn tại! Vui lòng chọn lớp hợp lệ.";
        } else {
            // Cập nhật sinh viên
            $updateQuery = "
                UPDATE sinhvien 
                SET 
                    tenSV='$tenSV', 
                    gioiTinh='$gioiTinh', 
                    ngaySinh='$ngaySinh', 
                    diaChi='$diaChi', 
                    sdt='$sdt', 
                    email='$email', 
                    maLop='$maLop',
                    CCCD='$CCCD',
                    ngayCap='$ngayCap',
                    noiCap='$noiCap',
                    hoKhauThuongTru='$hoKhauThuongTru',
                    danToc='$danToc',
                    tonGiao='$tonGiao' 
                WHERE 
                    maSV='$maSV'
            ";
            if ($conn->query($updateQuery)) {
                $_SESSION['notification'] = "Cập nhật thông tin sinh viên thành công!";
            } else {
                $_SESSION['notification'] = "Lỗi khi cập nhật sinh viên: " . $conn->error;
            }
        }
    }
}


// Lấy thông tin sinh viên
$maSV = $_GET['maSV']; // Lấy mã sinh viên từ URL
$result = $conn->query("SELECT * FROM sinhvien WHERE maSV = '$maSV'");
$sinhvien = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật Sinh viên</title>
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
            transition: border-color 0.3s ease;
        }

        form input[type="text"]:focus {
            border-color: #27ae60; 
            outline: none; 
            background-color: #ffffff; 
        }

        form button {
            padding: 14px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            font-size: 16px;
            font-weight: bold; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
        }

        form button:hover {
            background-color: #2ecc71;
            transform: scale(1.05); 
        }

        form a {
            display: inline-block;
            text-decoration: none;
            padding: 0px;
            background: #bdc3c7;
            color: black;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        form a:hover {
            background: #95a5a6;
            transform: scale(1.05); /* Phóng to nhẹ khi hover */
        }

        @media screen and (max-width: 768px) {
            form {
                padding: 15px;
                margin: 10px;
            }

            form input[type="text"] {
                font-size: 14px;
            }

            form button, form a {
                font-size: 14px; /* Thu nhỏ nút và liên kết cho màn hình nhỏ */
                padding: 10px 20px; /* Giảm kích thước padding */
            }
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
                
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>
        <section class="main-content">
            <h1>Cập nhật thông tin Sinh viên</h1>

            <!-- Hiển thị thông báo nếu có -->
            <?php if (isset($_SESSION['notification'])): ?>
                <div class="notification">
                    <p><?= $_SESSION['notification']; ?></p>
                </div>
                <?php unset($_SESSION['notification']); ?>
            <?php endif; ?>

            <form method="POST">
                <input type="text" name="maSV" placeholder="Mã sinh viên" value="<?= $sinhvien['maSV'] ?>" readonly required>
                <input type="text" name="tenSV" placeholder="Tên sinh viên" value="<?= $sinhvien['tenSV'] ?>" required>
                
                <label for="gioiTinh">Giới tính:</label>
                <select name="gioiTinh" id="gioiTinh" required>
                    <option value="">Chọn giới tính</option>
                    <option value="Nam" <?= $sinhvien['gioiTinh'] === 'Nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="Nữ" <?= $sinhvien['gioiTinh'] === 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                    <option value="Khác" <?= $sinhvien['gioiTinh'] === 'Khác' ? 'selected' : '' ?>>Khác</option>
                </select>

                <label for="ngaySinh">Ngày sinh:</label>
                <input type="date" name="ngaySinh" id="ngaySinh" value="<?= $sinhvien['ngaySinh'] ?>" required>
                
                <input type="text" name="diaChi" placeholder="Địa chỉ" value="<?= $sinhvien['diaChi'] ?>">
                <input type="text" name="sdt" placeholder="Số điện thoại" value="<?= $sinhvien['sdt'] ?>">
                <input type="email" name="email" placeholder="Email" value="<?= $sinhvien['email'] ?>">
                <select name="maLop" required>
                    <option value="">Chọn lớp</option>
                    <?php 
                    $lopList = $conn->query("SELECT * FROM lop");
                    while ($row = $lopList->fetch_assoc()):
                        $selected = ($row['maLop'] == $sinhvien['maLop']) ? 'selected' : '';
                    ?>
                        <option value="<?= $row['maLop'] ?>" <?= $selected ?>><?= $row['tenLop'] ?></option>
                    <?php endwhile; ?>
                </select>
                <input type="text" name="CCCD" placeholder="CCCD" value="<?= $sinhvien['CCCD'] ?>">
                <input type="date" name="ngayCap" placeholder="Ngày cấp" value="<?= $sinhvien['ngayCap'] ?>">
                <input type="text" name="noiCap" placeholder="Nơi cấp" value="<?= $sinhvien['noiCap'] ?>">
                <input type="text" name="hoKhauThuongTru" placeholder="Hộ khẩu thường trú" value="<?= $sinhvien['hoKhauThuongTru'] ?>">
                <input type="text" name="danToc" placeholder="Dân tộc" value="<?= $sinhvien['danToc'] ?>">
                <input type="text" name="tonGiao" placeholder="Tôn giáo" value="<?= $sinhvien['tonGiao'] ?>">
                <button type="submit" name="update">Cập nhật</button>
                <a href="manage_sinhvien.php" style="text-decoration: none; padding: 10px; background: #ccc; color: black; border-radius: 5px;">Quay lại</a>
            </form>

        </section>
    </div>
</body>
</html>
