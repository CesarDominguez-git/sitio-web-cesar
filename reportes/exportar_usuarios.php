<?php
require '../vendor/autoload.php'; // Ajusta la ruta si es necesario
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Conexión a la base de datos
$pdo = new PDO("mysql:host=localhost;dbname=TEC", "root", "");

// Crear el archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nombre');
$sheet->setCellValue('C1', 'Correo');
$sheet->setCellValue('D1', 'Teléfono');
$sheet->setCellValue('E1', 'Rol');
$sheet->setCellValue('F1', 'Fecha de Ingreso');

// Datos
$stmt = $pdo->query("SELECT id, nombre, email, telefono, rol, fecha_ingreso FROM usuarios");
$fila = 2;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $sheet->setCellValue("A$fila", $row['id']);
    $sheet->setCellValue("B$fila", $row['nombre']);
    $sheet->setCellValue("C$fila", $row['email']);
    $sheet->setCellValue("D$fila", $row['telefono']);
    $sheet->setCellValue("E$fila", $row['rol']);
    $sheet->setCellValue("F$fila", $row['fecha_ingreso']);
    $fila++;
}

// Descargar el archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="usuarios.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
