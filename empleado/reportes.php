<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['admin', 'empleado'])) {
    header("Location: ../public/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Generar Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">📄 Generación de Reportes</h2>

    <form action="generar_reporte.php" method="GET">
        <div class="mb-3">
            <label for="tipo" class="form-label">Selecciona el tipo de reporte:</label>
            <select name="tipo" id="tipo" class="form-select" required>
                <option value="">-- Seleccionar --</option>
                <option value="usuarios">Usuarios</option>
                <option value="proyectos">Proyectos</option>
                <option value="tareas">Tareas</option>
                <option value="clientes">Clientes</option>

            </select>
        </div>
        <button type="submit" class="btn btn-primary">Generar PDF</button>
    </form>
</div>

</body>
</html>

<?php
$content = ob_get_clean();
require_once '../includes/layout.php';
?>

