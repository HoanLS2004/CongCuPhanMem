<?php
session_start();
require 'db_connect.php'; // Kết nối cơ sở dữ liệu
require 'thuvien/vendor/autoload.php'; // Load thư viện PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Lọc theo lớp và môn học nếu có
$classFilter = isset($_GET['maLop']) ? $_GET['maLop'] : '';
$subjectFilter = isset($_GET['maMH']) ? $_GET['maMH'] : '';

// Truy vấn dữ liệu
$query = "SELECT sinhvien.maSV, sinhvien.tenSV, lop.tenLop, monhoc.tenMH, 
          AVG(diem.heso1 * 0.1 + diem.heso3 * 0.3 + diem.heso6 * 0.6) AS diemTrungBinh
          FROM diem
          JOIN sinhvien ON diem.maSV = sinhvien.maSV
          JOIN lop ON sinhvien.maLop = lop.maLop
          JOIN monhoc ON diem.maMH = monhoc.maMH
          WHERE 1";

if ($classFilter) {
    $query .= " AND sinhvien.maLop = '$classFilter'";
}

if ($subjectFilter) {
    $query .= " AND diem.maMH = '$subjectFilter'";
}

$query .= " GROUP BY sinhvien.maSV, monhoc.maMH";

$result = $conn->query($query);

// Tạo file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Tiêu đề
$sheet->setCellValue('A1', 'Mã Sinh viên');
$sheet->setCellValue('B1', 'Tên Sinh viên');
$sheet->setCellValue('C1', 'Lớp học');
$sheet->setCellValue('D1', 'Môn học');
$sheet->setCellValue('E1', 'Điểm Trung Bình');

// Ghi dữ liệu
$rowNumber = 2; // Dòng bắt đầu từ 2 (sau tiêu đề)
while ($row = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $rowNumber, $row['maSV']);
    $sheet->setCellValue('B' . $rowNumber, $row['tenSV']);
    $sheet->setCellValue('C' . $rowNumber, $row['tenLop']);
    $sheet->setCellValue('D' . $rowNumber, $row['tenMH']);
    $sheet->setCellValue('E' . $rowNumber, number_format($row['diemTrungBinh'], 2));
    $rowNumber++;
}

// Xuất file
$filename = 'BaoCao_DiemTrungBinh.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
