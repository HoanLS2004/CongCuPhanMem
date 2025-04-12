<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu

// Kiểm tra vai trò đăng nhập
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Phân quyền cho Teacher
$isTeacher = ($_SESSION['role'] == 'Teacher');
$isAdmin = ($_SESSION['role'] == 'Admin');


// Lấy danh sách sinh viên và lớp
$result = $conn->query("SELECT sinhvien.*, lop.tenLop FROM sinhvien JOIN lop ON sinhvien.maLop = lop.maLop");
$lopList = $conn->query("SELECT * FROM lop");

// Xử lý thêm, sửa, xóa sinh viên
if (isset($_POST['add'])) {
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
    if (empty($maSV) || empty($tenSV) || empty($gioiTinh) || empty($ngaySinh) || empty($diaChi) || empty($sdt) || empty($email) || empty($maLop)|| empty($CCCD) || empty($ngayCap) || 
    empty($noiCap) || empty($hoKhauThuongTru) || empty($danToc) || empty($tonGiao)) {
        $_SESSION['error'] = "Tất cả các trường đều bắt buộc!";
    }  elseif (strlen($maSV) > 10) {
        $_SESSION['error'] = "Mã sinh viên không được vượt quá 10 số!";
    } elseif (!preg_match('/^\d+$/', $sdt)) {
        $_SESSION['error'] = "Số điện thoại chỉ được chứa chữ số!";
    } elseif (strlen($sdt) > 11) {
        $_SESSION['error'] = "Số điện thoại không được vượt quá 11 số!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email không hợp lệ!";
    } elseif (!in_array($gioiTinh, ['Nam', 'Nữ'])) {
        $_SESSION['error'] = "Giới tính không hợp lệ!";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngaySinh)) {
        $_SESSION['error'] = "Ngày sinh không đúng định dạng (YYYY-MM-DD)!";
    } elseif (!preg_match('/^\d+$/', $CCCD) || strlen($CCCD) !== 12) {
        $_SESSION['error'] = "CCCD phải là 12 chữ số!";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $ngayCap)) {
        $_SESSION['error'] = "Ngày cấp không đúng định dạng (YYYY-MM-DD)!";
    }  else {
        // Kiểm tra mã sinh viên có trùng không
        $check = $conn->query("SELECT * FROM sinhvien WHERE maSV = '$maSV'OR CCCD = '$CCCD'");
        if ($check->num_rows > 0) {
            $_SESSION['error'] = "Mã sinh viên hoặc CCCD đã tồn tại! Vui lòng nhập mã khác.";
        } else {
            // Kiểm tra lớp có tồn tại không
            $checkLop = $conn->query("SELECT * FROM lop WHERE maLop = '$maLop'");
            if ($checkLop->num_rows == 0) {
                $_SESSION['error'] = "Lớp không tồn tại! Vui lòng chọn lớp hợp lệ.";
            } else {
                // Thêm sinh viên
                $sql = "INSERT INTO sinhvien (maSV, tenSV, gioiTinh, ngaySinh, diaChi, sdt, email, maLop,CCCD, ngayCap, noiCap, hoKhauThuongTru, danToc, tonGiao) 
                        VALUES ('$maSV', '$tenSV', '$gioiTinh', '$ngaySinh', '$diaChi', '$sdt', '$email', '$maLop','$CCCD', '$ngayCap', '$noiCap', '$hoKhauThuongTru', '$danToc', '$tonGiao')";
                if ($conn->query($sql)) {
                    $_SESSION['success'] = "Thêm sinh viên thành công!";
                } else {
                    $_SESSION['error'] = "Lỗi khi thêm sinh viên! Vui lòng thử lại.";
                }
            }
        }
    }
} elseif (isset($_POST['delete'])) {
    $maSV = $_POST['maSV'];

  
    $checkReferences = $conn->prepare("SELECT COUNT(*) AS count FROM sinhvien WHERE maSV = ?");
    $checkReferences->bind_param("s", $maSV);
    $checkReferences->execute();
    $resultReferences = $checkReferences->get_result();
    $rowReferences = $resultReferences->fetch_assoc();

    if ($rowReferences['count'] > 0) {
        // Nếu không có bản ghi tham chiếu, thực hiện xóa
        if ($conn->query("DELETE FROM sinhvien WHERE maSV='$maSV'")) {
            $_SESSION['success'] = "Xóa sinh viên thành công!";
        } else {
            $_SESSION['error'] = "Lỗi khi xóa sinh viên!";
        }
        
    } else {
        // Nếu có bản ghi tham chiếu, không cho phép xóa
        $_SESSION['error'] = "Không thể xóa sinh viên này. Có dữ liệu liên quan trong bảng khác!";
    }
} elseif (isset($_POST['update'])) {
    $maSV = trim($_POST['maSV']);
    $tenSV = trim($_POST['tenSV']);
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
    if (empty($maSV) || empty($tenSV) || empty($diaChi) || empty($sdt) || empty($email) || empty($maLop)|| empty($CCCD) || empty($ngayCap) || 
    empty($noiCap) || empty($hoKhauThuongTru) || empty($danToc) || empty($tonGiao)) {
        $_SESSION['error'] = "Tất cả các trường đều bắt buộc!";
    } elseif (!preg_match('/^\d+$/', $sdt)) {
        $_SESSION['error'] = "Số điện thoại chỉ được chứa chữ số!";
    } elseif (strlen($sdt) > 11) {
        $_SESSION['error'] = "Số điện thoại không được vượt quá 11 số!";
    } elseif (!preg_match('/^\d{12}$/', $CCCD)) {
        $_SESSION['error'] = "CCCD phải là 12 chữ số!";
    } else {
        // Kiểm tra lớp có tồn tại không
        $checkLop = $conn->query("SELECT * FROM lop WHERE maLop = '$maLop'");
        if ($checkLop->num_rows == 0) {
            $_SESSION['error'] = "Lớp không tồn tại! Vui lòng chọn lớp hợp lệ.";
        } else {
            // Cập nhật sinh viên
            if ($conn->query("UPDATE sinhvien SET tenSV='$tenSV', diaChi='$diaChi', sdt='$sdt', email='$email', maLop='$maLop',CCCD='$CCCD', ngayCap='$ngayCap', noiCap='$noiCap', hoKhauThuongTru='$hoKhauThuongTru', 
                danToc='$danToc', tonGiao='$tonGiao' WHERE maSV='$maSV'")) {
                $_SESSION['success'] = "Cập nhật sinh viên thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi cập nhật sinh viên!";
            }
        }
    }
}

// Nếu đang sửa sinh viên, lấy thông tin của sinh viên đó
$editRow = null;
if (isset($_POST['edit'])) {
    $maSV = $_POST['maSV'];
    $editResult = $conn->query("SELECT * FROM sinhvien WHERE maSV='$maSV'");
    $editRow = $editResult->fetch_assoc();
}

// Khởi tạo biến tìm kiếm
$maSVSearch = '';

// Xử lý tìm kiếm
if (isset($_POST['search'])) {
    $maSVSearch = $_POST['maSVSearch'];

    // Xây dựng câu truy vấn tìm kiếm
    $query = "SELECT sinhvien.*, lop.tenLop FROM sinhvien JOIN lop ON sinhvien.maLop = lop.maLop WHERE 1=1"; // Điều kiện mặc định

    // Điều kiện tìm kiếm theo mã sinh viên
    if ($maSVSearch != '') {
        $query .= " AND sinhvien.maSV LIKE '%$maSVSearch%'";
    }

    $result = $conn->query($query);
} else {
    // Nếu không tìm kiếm, lấy tất cả dữ liệu
    $result = $conn->query("SELECT sinhvien.*, lop.tenLop FROM sinhvien JOIN lop ON sinhvien.maLop = lop.maLop");
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sinh viên</title>
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
        form input[type="date"],
        form select,
        form input[type="email"] {
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
        form input[type="date"]:focus,
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
                <img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin
            </h2>
            <ul>
            <?php if ($isAdmin): ?>
                <li><a href="manage_khoa.php"><i class="material-icons">school</i> Quản lý Khoa</a></li>
                <li><a href="manage_lop.php"><i class="material-icons">class</i> Quản lý Lớp</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="manage_giangvien.php"><i class="material-icons">person_outline</i> Quản lý Giảng viên</a></li>
                <li><a href="manage_monhoc.php"><i class="material-icons">book</i> Quản lý Môn học</a></li>
                <li><a href="manage_taikhoan.php"><i class="material-icons">account_circle</i> Phân Quyền</a></li>
                <li><a href="quanlyhocvu.php" class="active"><i class="material-icons">account_circle</i> Quản lý học vụ</a></li>
                <li><a href="hocphi.php" class="active"><i class="material-icons">account_circle</i> Quản lý học phí</a></li>
                <?php endif; ?>
                <?php if ($isTeacher): ?>
                <li><a href="teacher_home.php"><i class="material-icons">home</i> Trang Chủ</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="gv_change_password.php"><i class="material-icons">assessment</i> Đổi Mật Khẩu</a></li>
                <?php endif; ?>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Nội dung chính -->
        <section class="main-content">
            <h1>Quản lý Sinh viên</h1>

            <!-- Form tìm kiếm -->
            <form method="POST">
                <input type="text" name="maSVSearch" placeholder="Tìm kiếm theo Mã Sinh viên" value="<?= htmlspecialchars($maSVSearch) ?>">
                <button type="submit" name="search">Tìm Kiếm</button>
            </form>

            <!-- Thông báo thành công hoặc lỗi -->
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


            <h2>Thêm sinh viên mới</h2><br>
            <!-- Form Thêm hoặc Cập nhật -->
            <form method="POST">
                <?php if ($editRow): ?>
                    <input type="text" name="maSV" placeholder="Mã sinh viên" value="<?= $editRow['maSV'] ?>" readonly required>
                <?php else: ?>
                    <input type="text" name="maSV" placeholder="Mã sinh viên" required>
                <?php endif; ?>

                <input type="text" name="tenSV" placeholder="Tên sinh viên" value="<?= $editRow['tenSV'] ?? '' ?>" required>
                <select name="gioiTinh" required>
                    <option value="">Chọn giới tính</option>
                    <option value="Nam" <?= isset($editRow) && $editRow['gioiTinh'] == 'Nam' ? 'selected' : '' ?>>Nam</option>
                    <option value="Nữ" <?= isset($editRow) && $editRow['gioiTinh'] == 'Nữ' ? 'selected' : '' ?>>Nữ</option>
                </select>
                <input type="text" name="diaChi" placeholder="Địa chỉ" value="<?= $editRow['diaChi'] ?? '' ?>">
                <input type="text" name="CCCD" placeholder="CCCD" value="<?= $editRow['CCCD'] ?? '' ?>" required>
                <input type="text" name="sdt" placeholder="Số điện thoại" value="<?= $editRow['sdt'] ?? '' ?>">
                <label for="ngaySinh">Ngày sinh</label>
                <input type="date" name="ngaySinh" placeholder="Ngày sinh" value="<?= $editRow['ngaySinh'] ?? '' ?>" required>
                <input type="email" name="email" placeholder="Email" value="<?= $editRow['email'] ?? '' ?>">
                <input type="text" name="noiCap" placeholder="Nơi cấp" value="<?= $editRow['noiCap'] ?? '' ?>">
                <input type="text" name="hoKhauThuongTru" placeholder="Hộ khẩu thường trú" value="<?= $editRow['hoKhauThuongTru'] ?? '' ?>">
                <input type="text" name="danToc" placeholder="Dân tộc" value="<?= $editRow['danToc'] ?? '' ?>">
                <input type="text" name="tonGiao" placeholder="Tôn giáo" value="<?= $editRow['tonGiao'] ?? '' ?>"> 
                <select name="maLop" required>
                <option value="">Chọn lớp</option>
                    <?php 
                    $lopList = $conn->query("SELECT * FROM lop");
                    while ($row = $lopList->fetch_assoc()): 
                        $selected = isset($editRow) && $row['maLop'] == $editRow['maLop'] ? 'selected' : '';
                    ?>
                        <option value="<?= $row['maLop'] ?>" <?= $selected ?>><?= $row['tenLop'] ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="ngayCap">Ngày cấp CCCD</label>
                <input type="date" name="ngayCap" placeholder="Ngày cấp CCCD" value="<?= $editRow['ngayCap'] ?? '' ?>">
                <!-- Nút Cập nhật hoặc Thêm -->
                <?php if (!$isTeacher): ?>
                <button type="submit" name="<?= isset($editRow) ? 'update' : 'add' ?>" class="btn-submit"><?= isset($editRow) ? 'Cập nhật' : 'Thêm' ?></button>
                <?php endif; ?>
            </form>

            <!-- Bảng Sinh viên -->
            <table border="1">
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
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()):  ?>
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
                            <td class="actions">
                            <?php if (!$isTeacher): ?>
                                <!-- Xóa sinh viên -->
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="maSV" value="<?= $row['maSV'] ?>">
                                    <button type="submit" name="delete" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa sinh viên này?')">Xóa</button>
                                </form>

                                <!-- Nút Cập nhật thông tin sinh viên -->
                                <a href="update_sinhvien.php?maSV=<?= htmlspecialchars($row['maSV']) ?>&tenSV=<?= htmlspecialchars($row['tenSV']) ?>&gioiTinh=<?= htmlspecialchars($row['gioiTinh']) ?>&ngaySinh=<?= htmlspecialchars($row['ngaySinh']) ?>&diaChi=<?= htmlspecialchars($row['diaChi']) ?>&sdt=<?= htmlspecialchars($row['sdt']) ?>&email=<?= htmlspecialchars($row['email']) ?>&maLop=<?= htmlspecialchars($row['maLop']) ?>">
                                    <button class="a1" type="button" class="btn-edit">Cập nhật</button>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
</html>
