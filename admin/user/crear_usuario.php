<?php
require_once '../../config/Database.php';
$pdo = Database::connect();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $rol = $_POST['rol'];
    $edad = $_POST['edad'];
    $ciudad = trim($_POST['ciudad']);
    $genero = $_POST['genero'];

    $email = strtolower($nombre) . '.' . strtolower($apellido) . '@tec.com';

    $password = password_hash('123456', PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol, edad, ciudad, genero) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $apellido, $email, $password, $rol, $edad, $ciudad, $genero]);
        $mensaje = "Usuario creado con éxito. Correo generado: $email (contraseña: 123456)";
    } catch (PDOException $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}


ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Crear Nuevo Usuario</h2>
    
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
<?php endif; ?>

<form method="POST" class="bg-white text-dark p-4 rounded shadow-sm">
    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="apellido" class="form-label">Apellido</label>
        <input type="text" name="apellido" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="edad" class="form-label">Edad</label>
        <input type="number" name="edad" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="ciudad" class="form-label">Ciudad</label>
        <input type="text" name="ciudad" class="form-control" required>
    </div>

    <div class="mb-3">
        <label for="genero" class="form-label">Género</label>
        <select name="genero" class="form-select" required>
            <option value="">Seleccionar</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
            <option value="otro">Otro</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="rol" class="form-label">Rol</label>
        <select name="rol" class="form-select" required>
            <option value="">Seleccionar</option>
            <option value="admin">Administrador</option>
            <option value="empleado">Empleado</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Crear Usuario</button>
</form>

<?php

$content = ob_get_clean();
$title = "Crear Usuario";
require  '../../includes/layout.php';
