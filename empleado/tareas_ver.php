<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'empleado') {
    header('Location: ../public/login.php');
    exit;
}

require_once '../config/Database.php';
$pdo = Database::connect();

// Traer tareas
$stmt = $pdo->query("
  SELECT t.descripcion, t.estado, p.nombre AS proyecto
  FROM tareas t
  LEFT JOIN proyectos p ON t.proyecto_id = p.id
  ORDER BY t.id DESC
");
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Badge de estado
function badgeEstado($e) {
  return match ($e) {
    'pendiente' => 'warning',
    'en_proceso' => 'primary',
    'completada' => 'success',
    default => 'secondary',
  };
}

ob_start();
?>

<div class="tabla-container">
  <h2><i class="fas fa-tasks"></i> Ver Tareas</h2>

  <?php if ($tareas): ?>
    <table>
      <thead>
        <tr>
          <th>Descripción</th>
          <th>Proyecto</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tareas as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['descripcion']); ?></td>
            <td><?= htmlspecialchars($t['proyecto'] ?? 'Sin proyecto'); ?></td>
            <td>
              <span class="badge bg-<?= badgeEstado($t['estado']); ?>">
                <?= htmlspecialchars(ucfirst($t['estado'])); ?>
              </span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No hay tareas registradas.</div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = "Ver Tareas";
require '../includes/layout.php';
?>
