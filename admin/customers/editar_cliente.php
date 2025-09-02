<?php
require_once(__DIR__ . '/../../config/Database.php');
$pdo = Database::connect();

if (!isset($_GET['id'])) {
    header('Location: clientes.php');
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    die("Cliente no encontrado.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $deuda = $_POST['deuda'] ?? 0;

    if (!$nombre || !$email || !$telefono) {
        $error = "Por favor completa todos los campos obligatorios.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE clientes SET nombre = ?, email = ?, telefono = ?, deuda = ? WHERE id = ?");
            $stmt->execute([$nombre, $email, $telefono, $deuda, $id]);
            $success = "Cliente actualizado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al actualizar cliente: " . $e->getMessage();
        }
    }
}

ob_start();
?>

<div class="tabla-container">
    <div class="mb-4">
        <h2><i class="fas fa-user-edit"></i> Editar Cliente</h2>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="p-2">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre completo *</label>
            <input type="text" name="nombre" id="nombre" class="form-control" value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Correo electrónico *</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($cliente['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono *</label>
            <input type="text" name="telefono" id="telefono" class="form-control" value="<?= htmlspecialchars($cliente['telefono']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="deuda" class="form-label">Deuda actual (S/)</label>
            <input type="number" step="0.01" name="deuda" id="deuda" class="form-control" value="<?= htmlspecialchars($cliente['deuda']) ?>">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
    </form>
</div>

<?php
$content = ob_get_clean();
$title = "Editar Cliente";
require_once(__DIR__ . '/../../includes/layout.php');
?>
