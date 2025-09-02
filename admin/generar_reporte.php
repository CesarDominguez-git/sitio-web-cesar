<?php
require_once '../libs/fpdf.php';
require_once '../config/Database.php';

$pdo = Database::connect();
$tipo = $_GET['tipo'] ?? '';

$pdf = new FPDF();
$pdf->AddPage();

// ✅ Encabezado de Tecnosoluciones
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Tecnosoluciones S.A.'), 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Fecha de Emision: ' . date('d/m/Y'), 0, 1, 'C');
$pdf->Ln(5);

// Función utilitaria para filas dinámicas
function drawRowWithMultiCells($pdf, $startX, $startY, $multiCols, $cellCols, $lineHeight = 8) {
    $yValues = [];
    $x = $startX;

    foreach ($multiCols as $col) {
        $pdf->SetXY($x, $startY);
        $pdf->MultiCell($col['width'], $lineHeight, utf8_decode($col['text']), 1);
        $yAfter = $pdf->GetY();
        $yValues[] = $yAfter;
        $x += $col['width'];
    }

    $alturaFila = max($yValues) - $startY;

    foreach ($cellCols as $col) {
        $pdf->SetXY($x, $startY);
        $pdf->Cell($col['width'], $alturaFila, utf8_decode($col['text']), 1, 0, $col['align'] ?? 'L');
        $x += $col['width'];
    }

    $pdf->SetY($startY + $alturaFila);
}

// --------------------------------------------------
// ✅ Usuarios
// --------------------------------------------------
if ($tipo === 'usuarios') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Usuarios', 0, 1, 'C');
    $pdf->Ln(5);

    $stmt = $pdo->query("SELECT nombre, email, rol FROM usuarios");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Nombre', 1);
    $pdf->Cell(70, 10, 'Email', 1);
    $pdf->Cell(40, 10, 'Rol', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $pdf->Cell(60, 10, utf8_decode($row['nombre']), 1);
        $pdf->Cell(70, 10, utf8_decode($row['email']), 1);
        $pdf->Cell(40, 10, ucfirst($row['rol']), 1);
        $pdf->Ln();
    }

// --------------------------------------------------
// ✅ Proyectos
// --------------------------------------------------
} elseif ($tipo === 'proyectos') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Proyectos', 0, 1, 'C');
    $pdf->Ln(5);

    $stmt = $pdo->query("SELECT p.nombre, p.descripcion, p.porcentaje_avance, c.nombre AS cliente 
                         FROM proyectos p 
                         JOIN clientes c ON p.cliente_id = c.id");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, 'Proyecto', 1);
    $pdf->Cell(80, 10, 'Descripcion', 1);
    $pdf->Cell(30, 10, 'Avance', 1);
    $pdf->Cell(30, 10, 'Cliente', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $x = $pdf->GetX();
        $y = $pdf->GetY();

        // Proyecto
        $pdf->SetXY($x, $y);
        $pdf->MultiCell(50, 8, utf8_decode($row['nombre']), 1);
        $y1 = $pdf->GetY();

        // Descripcion
        $pdf->SetXY($x + 50, $y);
        $pdf->MultiCell(80, 8, utf8_decode($row['descripcion']), 1);
        $y2 = $pdf->GetY();

        // Cliente
        $pdf->SetXY($x + 160, $y);
        $pdf->MultiCell(30, 8, utf8_decode($row['cliente']), 1);
        $y3 = $pdf->GetY();

        // Altura máxima de la fila
        $alturaFila = max($y1, $y2, $y3) - $y;

        // Avance
        $pdf->SetXY($x + 130, $y);
        $pdf->Cell(30, $alturaFila, $row['porcentaje_avance'].'%', 1, 0, 'C');

        // Solo dibuja Rect si hay espacio vacío
        if (($y1 - $y) < $alturaFila) {
            $alturaExtra = $alturaFila - ($y1 - $y);
            if ($alturaExtra > 0) {
                $pdf->Rect($x, $y + ($y1 - $y), 50, $alturaExtra);
            }
        }

        if (($y2 - $y) < $alturaFila) {
            $alturaExtra = $alturaFila - ($y2 - $y);
            if ($alturaExtra > 0) {
                $pdf->Rect($x + 50, $y + ($y2 - $y), 80, $alturaExtra);
            }
        }

        if (($y3 - $y) < $alturaFila) {
            $alturaExtra = $alturaFila - ($y3 - $y);
            if ($alturaExtra > 0) {
                $pdf->Rect($x + 160, $y + ($y3 - $y), 30, $alturaExtra);
            }
        }

        $pdf->SetY($y + $alturaFila);
    }



// --------------------------------------------------
// ✅ Tareas
// --------------------------------------------------
} elseif ($tipo === 'tareas') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Tareas', 0, 1, 'C');
    $pdf->Ln(5);

    $stmt = $pdo->query("
        SELECT t.descripcion, t.estado, 
               COALESCE(p.nombre, 'Sin Proyecto') AS proyecto, 
               COALESCE(u.nombre, 'Sin Encargado') AS encargado
        FROM tareas t
        LEFT JOIN proyectos p ON t.proyecto_id = p.id
        LEFT JOIN usuarios u ON t.encargado_id = u.id
    ");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(70, 10, 'Tarea', 1);
    $pdf->Cell(30, 10, 'Estado', 1);
    $pdf->Cell(50, 10, 'Proyecto', 1);
    $pdf->Cell(40, 10, 'Encargado', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        drawRowWithMultiCells(
            $pdf,
            $pdf->GetX(),
            $pdf->GetY(),
            [
                ['width' => 70, 'text' => $row['descripcion']],
            ],
            [
                ['width' => 30, 'text' => ucfirst($row['estado']), 'align' => 'C'],
                ['width' => 50, 'text' => $row['proyecto']],
                ['width' => 40, 'text' => $row['encargado']],
            ]
        );
    }

// --------------------------------------------------
// ✅ Clientes
// --------------------------------------------------
} elseif ($tipo === 'clientes') {
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Clientes', 0, 1, 'C');
    $pdf->Ln(5);

    $stmt = $pdo->query("SELECT nombre, email, telefono, deuda FROM clientes");

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(50, 10, 'Nombre', 1);
    $pdf->Cell(50, 10, 'Email', 1);
    $pdf->Cell(40, 10, 'Telefono', 1);
    $pdf->Cell(30, 10, 'Deuda (S/.)', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    foreach ($stmt as $row) {
        $pdf->Cell(50, 10, utf8_decode(substr($row['nombre'], 0, 25)), 1);
        $pdf->Cell(50, 10, utf8_decode(substr($row['email'], 0, 30)), 1);
        $pdf->Cell(40, 10, utf8_decode($row['telefono']), 1);
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
