<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'empleado') {
    header('Location: ../public/login.php');
    exit;
}

require_once '../config/Database.php';
$pdo = Database::connect();

$stmt = $pdo->query("
  SELECT nombre, email, rol, edad, ciudad, genero
  FROM usuarios
  WHERE rol = 'empleado' OR rol = 'admin'
  ORDER BY nombre ASC
");

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<div class="tabla-container">
  <h2><i class="fas fa-users"></i> Ver Usuarios</h2>

  <?php if ($usuarios): ?>
    <table>
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Email</th>
          <th>Rol</th>
          <th>Edad</th>
          <th>Ciudad</th>
          <th>Género</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['nombre']); ?></td>
            <td><?= htmlspecialchars($u['email']); ?></td>
            <td><?= htmlspecialchars(ucfirst($u['rol'])); ?></td>
            <td><?= htmlspecialchars($u['edad'] ?? 'No registrada'); ?></td>
            <td><?= htmlspecialchars($u['ciudad'] ?? 'No registrada'); ?></td>
            <td><?= htmlspecialchars(ucfirst($u['genero'] ?? 'No registrado')); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No hay usuarios registrados.</div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = "Ver Usuarios";
require '../includes/layout.php';
?>
