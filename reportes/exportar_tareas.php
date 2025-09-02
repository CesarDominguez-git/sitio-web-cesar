<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = new PDO("mysql:host=localhost;dbname=TEC", "root", "");

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Título');
$sheet->setCellValue('C1', 'Descripción');
$sheet->setCellValue('D1', 'Estado');
$sheet->setCellValue('E1', 'Proyecto ID');
$sheet->setCellValue('F1', 'Encargado ID');

$stmt = $pdo->query("SELECT id, descripcion, estado, proyecto_id, encargado_id FROM tareas");
$fila = 2;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->setCellValue("A$fila", $row['id']);
    $sheet->setCellValue("C$fila", $row['descripcion']);
    $sheet->setCellValue("D$fila", $row['estado']);
    $sheet->setCellValue("E$fila", $row['proyecto_id']);
    $sheet->setCellValue("F$fila", $row['encargado_id']);
    $fila++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="tareas.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
