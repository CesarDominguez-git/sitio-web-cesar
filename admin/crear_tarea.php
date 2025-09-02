<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit;
}

require '../config/Database.php';
$db = new Database();
$pdo = Database::connect();

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion']);
    $fecha_inicio = $_POST['fecha_inicio'] ?: null;
    $fecha_fin = $_POST['fecha_fin'] ?: null;

    if ($descripcion !== "") {
        // Validar fechas si están definidas
        if ($fecha_inicio && $fecha_fin && $fecha_fin < $fecha_inicio) {
            $mensaje = "⚠️ La fecha de fin no puede ser anterior a la de inicio.";
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO tareas (descripcion, estado, fecha_inicio, fecha_fin) VALUES (?, 'pendiente', ?, ?)"
            );
            $stmt->execute([$descripcion, $fecha_inicio, $fecha_fin]);

            $mensaje = "✅ Tarea creada con éxito.";
        }
    } else {
        $mensaje = "⚠️ La descripción no puede estar vacía.";
    }
}

ob_start();
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Crear Nueva Tarea</h2>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="POST" class="mt-4" style="max-width: 500px;">
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción de la tarea</label>
            <input type="text" class="form-control" id="descripcion" name="descripcion" required>
        </div>

        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
        </div>

        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha de fin</label>
            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
        </div>

        <!-- Si quieres vincular a un proyecto, puedes incluir este select
        <div class="mb-3">
            <label for="proyecto_id" class="form-label">Proyecto</label>
            <select class="form-select" name="proyecto_id" id="proyecto_id">
                <option value="">Selecciona un proyecto</option>
                <?php foreach ($proyectos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        -->

        <button type="submit" class="btn btn-success">Crear Tarea</button>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = "Crear Tarea";
require '../includes/layout.php';
?>
