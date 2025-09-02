<?php
session_start();
require_once(__DIR__ . '/../../config/Database.php');
$pdo = Database::connect();

// 🔑 Trae el usuario logueado para el menú (opcional)
if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $userData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 🗑️ Código para eliminar usuarios:
if (isset($_GET['eliminar'])) {
  $id = intval($_GET['eliminar']);
  try {
    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: /TEC/admin/user/usuarios.php");
    exit;
  } catch (PDOException $e) {
    die("Error al eliminar: " . $e->getMessage());
  }
}

// 📋 Trae la lista de usuarios:
try {
  $stmt = $pdo->query("SELECT id, nombre, apellido, email, rol, edad, ciudad, genero FROM usuarios ORDER BY id ASC");
  $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
  die("Error al obtener usuarios: " . $e->getMessage());
}

ob_start();
?>

<script>
  function confirmarEliminacion(id) {
    if (confirm("¿Estás seguro de que deseas eliminar este usuario?")) {
      window.location.href = "usuarios.php?eliminar=" + id;
    }
  }
</script>

<div class="tabla-container">
  <h2><i class="fas fa-users-cog"></i> Gestión de Usuarios</h2>

  <?php if (empty($usuarios)): ?>
    <div class="alert alert-warning">No hay usuarios registrados.</div>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Correo</th>
          <th>Rol</th>
          <th>Edad</th>
          <th>Ciudad</th>
          <th>Género</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $u): ?>
          <tr>
            <td><?= htmlspecialchars($u['id']) ?></td>
            <td><?= htmlspecialchars($u['nombre']) ?></td>
            <td><?= htmlspecialchars($u['apellido']) ?></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td><?= htmlspecialchars($u['rol']) ?></td>
            <td><?= htmlspecialchars($u['edad'] ?? 'No registrado') ?></td>
            <td><?= htmlspecialchars($u['ciudad'] ?? 'No registrada') ?></td>
            <td><?= htmlspecialchars(ucfirst($u['genero'] ?? 'No registrado')) ?></td>
            <td>
              <button onclick="confirmarEliminacion(<?= $u['id'] ?>)" class="btn btn-sm btn-danger">
                <i class="fas fa-trash-alt"></i> Eliminar
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3">
      <a href="../../reportes/exportar_usuarios.php" class="btn btn-success mb-3">
        <i class="fas fa-file-excel"></i> Exportar Usuarios a Excel
      </a>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = "Gestión de Usuarios";
require_once(__DIR__ . '/../../includes/layout.php');
?>
