<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu
// Xử lý tìm kiếm
$search = '';
if (isset($_POST['search'])) {
    $search = mysqli_real_escape_string($conn, $_POST['masvSearch']);
}

// Kiểm tra nếu người dùng là giảng viên
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM hocphi"; // Truy vấn dữ liệu từ bảng hocphi
$result = $conn->query($sql);
// Xử lý thêm học phí
if (isset($_POST['add'])) {
    $maHocPhi = trim($_POST['maHocPhi']);
    $maSV = trim($_POST['maSV']);
    $namHoc = trim($_POST['namHoc']);
    $tongTien = trim($_POST['tongTien']);
    $soTienDaDong = trim($_POST['soTienDaDong']);
    $ngayDong = trim($_POST['ngayDong']);
    $hanDong = trim($_POST['hanDong']);


    // Kiểm tra thông tin đầu vào
    if (empty($maHocPhi) || empty($maSV) || empty($namHoc) || empty($tongTien) || empty($soTienDaDong) || empty($ngayDong) || empty($hanDong) ) {
        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin!";
    } elseif (!is_numeric($tongTien) || !is_numeric($soTienDaDong)) {
        $_SESSION['error'] = "Tổng tiền và số tiền đã đóng phải là số!";
    } elseif ($soTienDaDong > $tongTien) {
        $_SESSION['error'] = "Số tiền đã đóng không được lớn hơn tổng tiền!";
    } else {
        // Kiểm tra mã học phí đã tồn tại chưa
        $stmt = $conn->prepare("SELECT * FROM hocphi WHERE MAHOCPHI = ?");
        $stmt->bind_param("s", $maHocPhi);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Mã học phí đã tồn tại!";
        } else {
            // Chèn dữ liệu vào bảng hocphi
            $stmt = $conn->prepare("INSERT INTO hocphi (MAHOCPHI, MASV, NAMHOC, TONGTIEN, SOTIENDADONG, NGAYDONG, HANDONG) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssddss", $maHocPhi, $maSV, $namHoc, $tongTien, $soTienDaDong, $ngayDong, $hanDong);


            if ($stmt->execute()) {
                $_SESSION['success'] = "Thêm học phí thành công!";
            } else {
                $_SESSION['error'] = "Lỗi khi thêm: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    header("Location: hocphi.php");
    exit;
}

// Xóa xử lý học vụ
if (isset($_POST['delete'])) {
    $masv = mysqli_real_escape_string($conn, $_POST['masv']);
    $query = "DELETE FROM hocphi WHERE MASV = '$masv'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Xóa thành công!";
    } else {
        $_SESSION['error'] = "Xóa thất bại: " . mysqli_error($conn);
    }
}
// Truy vấn danh sách xử lý học vụ
$query = "SELECT * FROM hocphi";
if (!empty($search)) {
    $query .= " WHERE MASV LIKE '%$search%'";
}
$result = mysqli_query($conn, $query);
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
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2><img src="image/logo.png" alt="Logo" style="width: 50px; vertical-align: middle;"> Trang Admin</h2>
            <ul>
                <li><a href="manage_khoa.php"><i class="material-icons">school</i> Quản lý Khoa</a></li>
                <li><a href="manage_lop.php"><i class="material-icons">class</i> Quản lý Lớp</a></li>
                <li><a href="manage_sinhvien.php"><i class="material-icons">person</i> Quản lý Sinh viên</a></li>
                <li><a href="manage_giangvien.php"><i class="material-icons">person_outline</i> Quản lý Giảng viên</a></li>
                <li><a href="manage_monhoc.php"><i class="material-icons">book</i> Quản lý Môn học</a></li>
                <li><a href="manage_taikhoan.php"><i class="material-icons">account_circle</i> Phân Quyền</a></li>
                <li><a href="quanlyhocvu.php" class="active"><i class="material-icons">account_circle</i> Quản lý học vụ</a></li>
                <li><a href="hocphi.php" class="active"><i class="material-icons">account_circle</i> Quản lý học phí</a></li>
            </ul>
            <div class="logout">
                <a href="logout.php"><i class="material-icons">exit_to_app</i> Đăng xuất</a>
            </div>
        </nav>

        <!-- Main Content Area -->
        <div class="main-content">
            <h1>Quản lý Học phí</h1>
            <form method="POST">
    <input type="text" name="masvSearch" placeholder="Tìm kiếm theo Mã SV" value="<?= htmlspecialchars($search) ?>">
    <button type="submit" name="search">Tìm Kiếm</button>

    </form>

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
</form>

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
                        <th>Hành động</th>
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
                                <td>
                <!-- Xóa -->
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="masv" value="<?= htmlspecialchars($row['MASV']) ?>">
                    <button type="submit" name="delete" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">Xóa</button>
                </form>
                <!-- Cập nhật -->
                <a href="update_hocphi.php?masv=<?= urlencode($row['MASV']) ?>">
                    <button type="button">Cập nhật</button>
                </a>
            </td>

                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">Không có dữ liệu học phí</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
                
            <!-- Form Thêm học phí -->
            <form method="POST" >
                <h2>Thêm học phí mới</h2>
                <label for="maHocPhi">Mã Học Phí</label>
                <input type="text" name="maHocPhi" required>

                <label for="maSinhVien">Mã Sinh Viên</label>
                <input type="text" name="maSV" required>

                <label for="namHoc">Năm Học</label>
                <input type="text" name="namHoc" required>

                <label for="tongTien">Tổng Tiền (VNĐ)</label>
                <input type="text" name="tongTien" required>

                <label for="soTienDaDong">Số tiền đã đóng (VNĐ)</label>
                <input type="text" name="soTienDaDong" required>
                
                <label for="ngayDong">Ngày đóng (VNĐ)</label>
                <input type="date" name="ngayDong" required>

                <label for="hanDong">Hạn đóng (VNĐ)</label>
                <input type="date" name="hanDong" required>

                <button type="submit" name="add" >Thêm</button>
            </form>
            
        </div>
    </div>
</body>
</html>
