<?php
require_once(__DIR__ . '/../../config/Database.php');
$pdo = Database::connect();

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
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email, telefono, deuda) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nombre, $email, $telefono, $deuda]);
            $success = "Cliente registrado correctamente.";
        } catch (PDOException $e) {
            $error = "Error al guardar cliente: " . $e->getMessage();
        }
    }
}

ob_start();
?>

<div class="mb-4">
    <h2>Registrar Nuevo Cliente</h2>
    <a href="clientes.php" class="btn btn-secondary">← Volver</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php elseif ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<form method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre completo *</label>
        <input type="text" name="nombre" id="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Correo electrónico *</label>
        <input type="email" name="email" id="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="telefono" class="form-label">Teléfono *</label>
        <input type="text" name="telefono" id="telefono" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="deuda" class="form-label">Deuda inicial (S/)</label>
        <input type="number" step="0.01" name="deuda" id="deuda" class="form-control" value="0.00">
    </div>

    <button type="submit" class="btn btn-primary">Guardar Cliente</button>
</form>

<?php
$content = ob_get_clean();
$title = "Nuevo Cliente";
require_once(__DIR__ . '/../../includes/layout.php');
?>
