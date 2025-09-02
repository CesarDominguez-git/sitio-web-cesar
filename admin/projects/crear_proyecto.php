<?php
require_once '../../config/Database.php';

$pdo = Database::connect();

// Obtener lista de clientes
$stmt = $pdo->query("SELECT id, nombre FROM clientes");
$clientes = $stmt->fetchAll();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $cliente_id = $_POST['cliente_id'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_fin'] ?? null;

    if (!empty($nombre) && !empty($cliente_id) && !empty($fecha_inicio) && !empty($fecha_fin)) {
        $sql = "INSERT INTO proyectos (nombre, descripcion, fecha_inicio, fecha_fin, cliente_id) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $descripcion, $fecha_inicio, $fecha_fin, $cliente_id]);

        header("Location: proyecto_gestion.php");
        exit;
    } else {
        $error = "Todos los campos obligatorios deben completarse.";
    }
}

ob_start();
?>

<h2 class="text- mb-4">Crear Nuevo Proyecto</h2>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="card p-4 shadow-sm bg-white">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre del proyecto *</label>
        <input type="text" class="form-control" id="nombre" name="nombre" required>
    </div>

    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
    </div>

    <div class="mb-3">
        <label for="cliente_id" class="form-label">Cliente asociado *</label>
        <select class="form-select" id="cliente_id" name="cliente_id" required>
            <option value="" disabled selected>Seleccione un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?= $cliente['id'] ?>"><?= htmlspecialchars($cliente['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="fecha_inicio" class="form-label">Fecha de Inicio *</label>
        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
    </div>

    <div class="mb-3">
        <label for="fecha_fin" class="form-label">Fecha Final Prevista *</label>
        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
    </div>

    <div class="d-flex justify-content-between">
        <button type="submit" class="btn btn-primary">Crear Proyecto</button>
    </div>
</form>

<?php
$content = ob_get_clean();
$title = "Crear Proyecto";
require __DIR__ . '/../../includes/layout.php'; 
?>
