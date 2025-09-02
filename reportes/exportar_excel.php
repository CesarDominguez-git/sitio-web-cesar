<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Database.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Conexión PDO
$pdo = Database::connect();

// Consultar los proyectos con cliente
$sql = "SELECT p.id, p.nombre AS proyecto, p.descripcion, p.porcentaje_avance, c.nombre AS cliente
        FROM proyectos p
        LEFT JOIN clientes c ON p.cliente_id = c.id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Nombre del Proyecto');
$sheet->setCellValue('C1', 'Descripción');
$sheet->setCellValue('D1', 'Avance (%)');
$sheet->setCellValue('E1', 'Cliente');

// Estilos en encabezados
$sheet->getStyle('A1:E1')->getFont()->setBold(true);

// Agregar datos
$fila = 2;
foreach ($proyectos as $proyecto) {
    $sheet->setCellValue("A{$fila}", $proyecto['id']);
    $sheet->setCellValue("B{$fila}", $proyecto['proyecto']);
    $sheet->setCellValue("C{$fila}", $proyecto['descripcion']);
    $sheet->setCellValue("D{$fila}", $proyecto['porcentaje_avance']);
    $sheet->setCellValue("E{$fila}", $proyecto['cliente'] ?? 'Sin cliente');
    $fila++;
}

// Enviar archivo Excel al navegador
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_proyectos.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
