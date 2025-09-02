<?php
session_start();

// Validar sesión y rol
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'empleado') {
    header('Location: ../public/login.php');
    exit;
}

require_once '../config/Database.php';
$pdo = Database::connect();

$userId = $_SESSION['user_id'];

// Procesar cambio de estado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tarea_id'], $_POST['nuevo_estado'])) {
    $tarea_id = $_POST['tarea_id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    $estadosPermitidos = ['pendiente', 'en_proceso', 'completada'];
    if (in_array($nuevo_estado, $estadosPermitidos)) {
        $stmtUpdate = $pdo->prepare("UPDATE tareas SET estado = ? WHERE id = ? AND encargado_id = ?");
        $stmtUpdate->execute([$nuevo_estado, $tarea_id, $userId]);
    }
}

// Consultar tareas
$stmt = $pdo->prepare("
    SELECT t.id, t.descripcion, t.estado, p.nombre AS proyecto
    FROM tareas t
    LEFT JOIN proyectos p ON t.proyecto_id = p.id
    WHERE t.encargado_id = ?
    ORDER BY t.id DESC
");
$stmt->execute([$userId]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Colores de estado (usa Bootstrap badges)
function getEstadoColor($estado) {
    return match($estado) {
        'pendiente' => 'warning',
        'en_proceso' => 'primary',
        'completada' => 'success',
        default => 'secondary',
    };
}

ob_start();
?>

<div class="tabla-container">
  <h2><i class="fas fa-tasks"></i> Mis Tareas Asignadas</h2>

  <?php if (count($tareas) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Descripción</th>
          <th>Proyecto</th>
          <th>Estado</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tareas as $tarea): ?>
          <tr>
            <td><?= htmlspecialchars($tarea['descripcion']) ?></td>
            <td><?= htmlspecialchars($tarea['proyecto'] ?? 'Sin proyecto') ?></td>
            <td>
              <span class="badge bg-<?= getEstadoColor($tarea['estado']) ?>">
                <?= ucfirst($tarea['estado']) ?>
              </span>
            </td>
            <td>
              <form method="POST" class="d-flex align-items-center gap-2">
                <input type="hidden" name="tarea_id" value="<?= $tarea['id'] ?>">
                <select name="nuevo_estado" class="form-select form-select-sm" style="width: 150px;">
                  <option value="pendiente" <?= $tarea['estado'] === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                  <option value="en_proceso" <?= $tarea['estado'] === 'en_proceso' ? 'selected' : '' ?>>En Proceso</option>
                  <option value="completada" <?= $tarea['estado'] === 'completada' ? 'selected' : '' ?>>Completada</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No tienes tareas asignadas.</div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = "Mis Tareas";
require '../includes/layout.php';
?>
