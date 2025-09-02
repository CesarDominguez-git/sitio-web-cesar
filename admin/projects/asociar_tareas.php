<?php
require_once '../../config/Database.php';
$pdo = Database::connect();

// Validar ID de proyecto
$proyecto_id = $_GET['id'] ?? null;
if (!$proyecto_id) {
    die("ID de proyecto no válido.");
}

// Obtener nombre del proyecto
$stmt = $pdo->prepare("SELECT nombre FROM proyectos WHERE id = ?");
$stmt->execute([$proyecto_id]);
$proyecto = $stmt->fetch();
if (!$proyecto) {
    die("Proyecto no encontrado.");
}

// Obtener empleados
$empleados = $pdo->query("SELECT id, nombre FROM usuarios WHERE rol = 'empleado'")->fetchAll();

// Insertar nueva tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $encargado_id = $_POST['encargado_id'];
    $fecha_inicio = $_POST['fecha_inicio'] ?: null;
    $fecha_fin = $_POST['fecha_fin'] ?: null;

    $stmt = $pdo->prepare(
        "INSERT INTO tareas (descripcion, proyecto_id, encargado_id, fecha_inicio, fecha_fin) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->execute([$descripcion, $proyecto_id, $encargado_id, $fecha_inicio, $fecha_fin]);

    header("Location: asociar_tareas.php?id=$proyecto_id");
    exit;
}

// Obtener tareas asociadas
$stmt = $pdo->prepare("SELECT t.id, t.descripcion, t.estado, t.fecha_inicio, t.fecha_fin, u.nombre AS encargado
                       FROM tareas t
                       JOIN usuarios u ON t.encargado_id = u.id
                       WHERE t.proyecto_id = ?");
$stmt->execute([$proyecto_id]);
$tareas = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Tareas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Proyecto: <?= htmlspecialchars($proyecto['nombre']) ?></h2>

    <form method="POST" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" placeholder="Descripción de la tarea" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Empleado</label>
                <select name="encargado_id" class="form-select" required>
                    <option value="">Selecciona un empleado</option>
                    <?php foreach ($empleados as $empleado): ?>
                        <option value="<?= $empleado['id'] ?>"><?= htmlspecialchars($empleado['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Agregar</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Encargado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tareas as $t): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['descripcion']) ?></td>
                    <td><?= ucfirst($t['estado']) ?></td>
                    <td><?= $t['fecha_inicio'] ?? '-' ?></td>
                    <td><?= $t['fecha_fin'] ?? '-' ?></td>
                    <td><?= htmlspecialchars($t['encargado']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="proyecto_gestion.php" class="btn btn-secondary">← Volver</a>
</body>
</html>
