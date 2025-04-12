<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu
require_once 'thuvien/vendor/autoload.php'; // Tải thư viện PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Teacher') {
    header("Location: login.php");
    exit;
}

// Lấy dữ liệu tìm kiếm (nếu có)
$maSVSearch = $_POST['maSVSearch'] ?? '';
$tenMHSearch = $_POST['tenMHSearch'] ?? '';

// Truy vấn dữ liệu
$query = "SELECT diem.*, sinhvien.maSV, sinhvien.tenSV, monhoc.tenMH 
          FROM diem
          JOIN sinhvien ON diem.maSV = sinhvien.maSV
          JOIN monhoc ON diem.maMH = monhoc.maMH
          WHERE 1";


if (!empty($maSVSearch)) {
    $query .= " AND diem.maSV LIKE '%$maSVSearch%'";
}

if (!empty($tenMHSearch)) {
    $query .= " AND monhoc.tenMH LIKE '%$tenMHSearch%'";
}

$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Tạo file Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Thiết lập tiêu đề cột
$sheet->setCellValue('A1', 'Mã Sinh Viên');
$sheet->setCellValue('B1', 'Tên Sinh Viên'); // Thêm cột Tên Sinh Viên
$sheet->setCellValue('C1', 'Tên Môn Học');
$sheet->setCellValue('D1', 'Hệ Số 1');
$sheet->setCellValue('E1', 'Hệ Số 3');
$sheet->setCellValue('F1', 'Hệ Số 6');
$sheet->setCellValue('G1', 'Tổng Điểm');

// Điền dữ liệu
$rowIndex = 2; // Bắt đầu từ dòng thứ 2
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue("A{$rowIndex}", $row['maSV']);
    $sheet->setCellValue("B{$rowIndex}", $row['tenSV']); // Điền Tên Sinh Viên
    $sheet->setCellValue("C{$rowIndex}", $row['tenMH']);
    $sheet->setCellValue("D{$rowIndex}", $row['heso1']);
    $sheet->setCellValue("E{$rowIndex}", $row['heso3']);
    $sheet->setCellValue("F{$rowIndex}", $row['heso6']);
    $sheet->setCellValue("G{$rowIndex}", $row['tongDiem']);
    $rowIndex++;
}


    // Xuất file Excel
    $fileName = 'DanhSachDiem.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$fileName\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    echo "Không có dữ liệu để xuất.";
}
?>
