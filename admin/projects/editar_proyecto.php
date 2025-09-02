<?php
session_start();
require_once '../../config/Database.php';
$pdo = Database::connect();

$proyecto_id = $_GET['id'] ?? null;
if (!$proyecto_id) {
    die("ID de proyecto no especificado.");
}

// Traer datos del proyecto
$stmt = $pdo->prepare("SELECT * FROM proyectos WHERE id = ?");
$stmt->execute([$proyecto_id]);
$proyecto = $stmt->fetch();
if (!$proyecto) {
    die("Proyecto no encontrado.");
}

// Traer lista de clientes
$clientes = $pdo->query("SELECT id, nombre FROM clientes")->fetchAll();

// Eliminar proyecto si se solicitó
if (isset($_POST['eliminar'])) {
    $stmt = $pdo->prepare("DELETE FROM proyectos WHERE id = ?");
    $stmt->execute([$proyecto_id]);

    header("Location: proyecto_gestion.php");
    exit;
}

// Actualizar proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['eliminar'])) {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $porcentaje = $_POST['porcentaje_avance'] ?? 0;
    $cliente_id = $_POST['cliente_id'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    $stmt = $pdo->prepare("
        UPDATE proyectos 
        SET nombre = ?, descripcion = ?, porcentaje_avance = ?, cliente_id = ?, fecha_inicio = ?, fecha_fin = ?
        WHERE id = ?
    ");
    $stmt->execute([$nombre, $descripcion, $porcentaje, $cliente_id, $fecha_inicio, $fecha_fin, $proyecto_id]);

    header("Location: proyecto_gestion.php");
    exit;
}

ob_start();
?>

<div class="tabla-container">
    <h2><i class="fas fa-edit"></i> Editar Proyecto</h2>

    <form method="POST" class="mb-4">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre del proyecto:</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($proyecto['nombre']) ?>" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción:</label>
            <textarea name="descripcion" id="descripcion" class="form-control" required><?= htmlspecialchars($proyecto['descripcion']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="porcentaje_avance" class="form-label">Porcentaje de avance:</label>
            <input type="number" name="porcentaje_avance" id="porcentaje_avance" class="form-control" min="0" max="100" value="<?= $proyecto['porcentaje_avance'] ?>" required>
        </div>

        <div class="mb-3">
            <label for="cliente_id" class="form-label">Cliente:</label>
            <select name="cliente_id" id="cliente_id" class="form-select" required>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>" <?= $cliente['id'] == $proyecto['cliente_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cliente['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha de Inicio:</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                   value="<?= htmlspecialchars($proyecto['fecha_inicio'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha Final Prevista:</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                   value="<?= htmlspecialchars($proyecto['fecha_fin'] ?? '') ?>">
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
            <a href="proyecto_gestion.php" class="btn btn-secondary">Cancelar</a>
            <a href="asociar_tareas.php?id=<?= $proyecto_id ?>" class="btn btn-info">Editar Tareas</a>
        </div>
    </form>

    <form method="POST" onsubmit="return confirm('¿Estás seguro de eliminar este proyecto? Esta acción no se puede deshacer.');">
        <input type="hidden" name="eliminar" value="1">
        <button type="submit" class="btn btn-danger">Eliminar Proyecto</button>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = "Editar Proyecto";
require '../../includes/layout.php';
?>
