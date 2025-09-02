<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'empleado') {
    header('Location: ../public/login.php');
    exit;
}

require_once '../config/Database.php';
$pdo = Database::connect();

// Traer proyectos con info del cliente
$sql = "
    SELECT p.nombre, p.descripcion, p.porcentaje_avance, c.nombre AS cliente
    FROM proyectos p
    LEFT JOIN clientes c ON p.cliente_id = c.id
    ORDER BY p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="tabla-container">
  <h2><i class="fas fa-tasks"></i> Ver Proyectos</h2>

  <?php if (count($proyectos) > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Cliente</th>
          <th>Avance</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($proyectos as $proyecto): ?>
          <tr>
            <td><?= htmlspecialchars($proyecto['nombre']) ?></td>
            <td><?= htmlspecialchars($proyecto['descripcion']) ?></td>
            <td><?= htmlspecialchars($proyecto['cliente'] ?? 'No asignado') ?></td>
            <td>
              <div class="progress" style="height: 20px;">
                <div class="progress-bar 
                    <?= $proyecto['porcentaje_avance'] < 50 ? 'bg-warning' : ($proyecto['porcentaje_avance'] < 100 ? 'bg-info' : 'bg-success') ?>"
                    role="progressbar" style="width: <?= $proyecto['porcentaje_avance'] ?>%">
                  <?= $proyecto['porcentaje_avance'] ?>%
                </div>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No hay proyectos disponibles para mostrar.</div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = "Ver Proyectos";
require '../includes/layout.php';
