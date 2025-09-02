<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['admin', 'empleado'])) {
    header("Location: ../public/login.php");
    exit;
}
require_once '../libs/fpdf.php';
require_once '../config/Database.php';

$pdo = Database::connect();
$tipo = $_GET['tipo'] ?? '';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

if ($tipo === 'usuarios') {
    $pdf->Cell(0, 10, 'Reporte de Usuarios', 0, 1, 'C');
    $pdf->Ln(10);

    $stmt = $pdo->query("SELECT nombre, email, rol FROM usuarios");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Nombre', 1);
    $pdf->Cell(70, 10, 'Email', 1);
    $pdf->Cell(40, 10, 'Rol', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $pdf->Cell(60, 10, $row['nombre'], 1);
        $pdf->Cell(70, 10, $row['email'], 1);
        $pdf->Cell(40, 10, ucfirst($row['rol']), 1);
        $pdf->Ln();
    }

} elseif ($tipo === 'proyectos') {
    $pdf->Cell(0, 10, 'Reporte de Proyectos', 0, 1, 'C');
    $pdf->Ln(10);

    $stmt = $pdo->query("SELECT p.nombre, p.descripcion, p.porcentaje_avance, c.nombre AS cliente 
                         FROM proyectos p 
                         JOIN clientes c ON p.cliente_id = c.id");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, 'Proyecto', 1);
    $pdf->Cell(70, 10, 'Descripción', 1);
    $pdf->Cell(30, 10, 'Avance', 1);
    $pdf->Cell(40, 10, 'Cliente', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $pdf->Cell(50, 10, $row['nombre'], 1);
        $pdf->Cell(70, 10, substr($row['descripcion'], 0, 30), 1);
        $pdf->Cell(30, 10, $row['porcentaje_avance'].'%', 1);
        $pdf->Cell(40, 10, $row['cliente'], 1);
        $pdf->Ln();
    }

} elseif ($tipo === 'tareas') {
    $pdf->Cell(0, 10, 'Reporte de Tareas', 0, 1, 'C');
    $pdf->Ln(10);

$stmt = $pdo->query("SELECT t.descripcion, t.estado, 
           COALESCE(p.nombre, 'Sin Proyecto') AS proyecto, 
           COALESCE(u.nombre, 'Sin Encargado') AS encargado
    FROM tareas t
    LEFT JOIN proyectos p ON t.proyecto_id = p.id
    LEFT JOIN usuarios u ON t.encargado_id = u.id
   ");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 10, 'Tarea', 1);
    $pdf->Cell(30, 10, 'Estado', 1);
    $pdf->Cell(40, 10, 'Encargado', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $pdf->Cell(70, 10, substr($row['descripcion'], 0, 35), 1);
        $pdf->Cell(30, 10, ucfirst($row['estado']), 1);
        $pdf->Cell(40, 10, $row['encargado'], 1);
        $pdf->Ln();
    }

} elseif ($tipo === 'clientes') {
    $pdf->Cell(0, 10, 'Reporte de Clientes', 0, 1, 'C');
    $pdf->Ln(10);

    $stmt = $pdo->query("SELECT nombre, email, telefono, deuda FROM clientes");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, 'Nombre', 1);
    $pdf->Cell(50, 10, 'Email', 1);
    $pdf->Cell(40, 10, 'Telefono', 1);
    $pdf->Cell(30, 10, 'Deuda (S/.)', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $pdf->Cell(50, 10, substr($row['nombre'], 0, 25), 1);
        $pdf->Cell(50, 10, substr($row['email'], 0, 30), 1);
        $pdf->Cell(40, 10, $row['telefono'], 1);
        $pdf->Cell(30, 10, number_format($row['deuda'], 2), 1);
        $pdf->Ln();
    }

    if ($stmt->rowCount() === 0) {
        $pdf->Cell(0, 10, 'No se encontraron clientes registrados.', 0, 1, 'C');
    }
} else {
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Tipo de reporte no válido.', 0, 1);
    }

$pdf->Output('I', 'reporte.pdf');
exit;
