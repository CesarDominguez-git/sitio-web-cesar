<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['empleado', 'admin'])) {
    header('Location: ../public/login.php');
    exit;
}

require_once '../config/Database.php';
$pdo = Database::connect();

$mensaje = '';
$error = '';

try {
    $conn = $pdo;
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Subir foto de perfil
if (isset($_POST['upload_foto']) && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $foto = $_FILES['foto'];
    $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
    $nombreArchivo = 'perfil_' . $_SESSION['user_id'] . '.' . $ext;
    $rutaDestino = '../uploads/' . $nombreArchivo;

    if (move_uploaded_file($foto['tmp_name'], $rutaDestino)) {
        $updateFoto = $conn->prepare("UPDATE usuarios SET foto = :foto WHERE id = :id");
        $updateFoto->execute(['foto' => $nombreArchivo, 'id' => $_SESSION['user_id']]);
        $_SESSION['user_foto'] = $nombreArchivo;
        $mensaje = "Foto de perfil actualizada.";
    } else {
        $error = "No se pudo subir la foto.";
    }
}

// Actualizar datos
if (isset($_POST['update_info'])) {
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    $update = $conn->prepare("UPDATE usuarios SET telefono = :telefono, direccion = :direccion WHERE id = :id");
    $update->execute([
        'telefono' => $telefono ?: null,
        'direccion' => $direccion ?: null,
        'id' => $_SESSION['user_id']
    ]);
    $_SESSION['user_telefono'] = $telefono;
    $mensaje = "Perfil actualizado correctamente.";
}

// Cambiar contraseña
if (isset($_POST['change_password'])) {
    $pass_actual = $_POST['pass_actual'] ?? '';
    $pass_nuevo = $_POST['pass_nuevo'] ?? '';
    $pass_confirm = $_POST['pass_confirm'] ?? '';

    $stmt = $conn->prepare("SELECT password FROM usuarios WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $usuarioPass = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuarioPass) {
        $error = "Usuario no encontrado.";
    } elseif (!password_verify($pass_actual, $usuarioPass['password'])) {
        $error = "La contraseña actual es incorrecta.";
    } elseif (strlen($pass_nuevo) < 6) {
        $error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($pass_nuevo !== $pass_confirm) {
        $error = "La confirmación de la contraseña no coincide.";
    } else {
        $hash = password_hash($pass_nuevo, PASSWORD_DEFAULT);
        $updatePass = $conn->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
        $updatePass->execute(['password' => $hash, 'id' => $_SESSION['user_id']]);
        $mensaje = "Contraseña actualizada correctamente.";
    }
}

// Obtener datos del usuario
$stmt = $conn->prepare("SELECT nombre, email, telefono, direccion, fecha_ingreso, rol, foto FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    die("Usuario no encontrado.");
}

$_SESSION['user_nombre'] = $usuario['nombre'];
$_SESSION['user_email'] = $usuario['email'];
$_SESSION['user_telefono'] = $usuario['telefono'];
$_SESSION['user_foto'] = $usuario['foto'] ?? null;

ob_start();
?>

<div class="tabla-container">
    <h2><i class="fas fa-user"></i> Mi Perfil</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm text-dark mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Información Personal</h5>
        </div>
        <div class="card-body">

            <!-- Foto de perfil -->
            <div class="mb-4 text-center">
                <?php if (!empty($usuario['foto'])): ?>
                    <img src="../uploads/<?= htmlspecialchars($usuario['foto']) ?>" class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user-circle fa-7x text-secondary"></i>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="mt-2">
                    <input type="file" name="foto" accept="image/*" class="form-control mb-2" required>
                    <button type="submit" name="upload_foto" class="btn btn-sm btn-outline-primary">Actualizar Foto</button>
                </form>
            </div>

            <!-- Datos básicos -->
            <dl class="row mb-4">
                <dt class="col-sm-4">Nombre:</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($usuario['nombre']) ?></dd>

                <dt class="col-sm-4">Correo:</dt>
                <dd class="col-sm-8"><?= htmlspecialchars($usuario['email']) ?></dd>

                <dt class="col-sm-4">Rol:</dt>
                <dd class="col-sm-8 text-capitalize"><?= htmlspecialchars($usuario['rol']) ?></dd>

                <dt class="col-sm-4">Fecha de Ingreso:</dt>
                <dd class="col-sm-8"><?= date('d/m/Y', strtotime($usuario['fecha_ingreso'])) ?></dd>
            </dl>

            <!-- Datos editables -->
            <div id="infoDisplay">
                <dl class="row">
                    <dt class="col-sm-4">Teléfono:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($usuario['telefono'] ?? 'No registrado') ?></dd>

                    <dt class="col-sm-4">Dirección:</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($usuario['direccion'] ?? 'No registrada') ?></dd>
                </dl>
                <button id="btnEditInfo" class="btn btn-primary">Editar Información</button>
            </div>

            <form method="post" id="infoForm" style="display:none;">
                <input type="hidden" name="update_info" value="1" />
                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="text" class="form-control" name="telefono" id="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección:</label>
                    <input type="text" class="form-control" name="direccion" id="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                <button type="button" id="btnCancelEdit" class="btn btn-secondary ms-2">Cancelar</button>
            </form>

            <hr>

            <!-- Cambiar contraseña -->
            <button class="btn btn-warning mb-3" id="btnTogglePasswordForm">Cambiar Contraseña</button>

            <form method="post" id="passwordForm" style="display:none;">
                <input type="hidden" name="change_password" value="1" />
                <div class="mb-3">
                    <label for="pass_actual" class="form-label">Contraseña Actual:</label>
                    <input type="password" class="form-control" name="pass_actual" required>
                </div>
                <div class="mb-3">
                    <label for="pass_nuevo" class="form-label">Nueva Contraseña:</label>
                    <input type="password" class="form-control" name="pass_nuevo" required>
                </div>
                <div class="mb-3">
                    <label for="pass_confirm" class="form-label">Confirmar Nueva Contraseña:</label>
                    <input type="password" class="form-control" name="pass_confirm" required>
                </div>
                <button type="submit" class="btn btn-warning text-dark">Guardar Contraseña</button>
            </form>

        </div>
    </div>
</div>

<script>
    document.getElementById('btnEditInfo').addEventListener('click', function () {
        document.getElementById('infoDisplay').style.display = 'none';
        document.getElementById('infoForm').style.display = 'block';
    });
    document.getElementById('btnCancelEdit').addEventListener('click', function () {
        document.getElementById('infoForm').style.display = 'none';
        document.getElementById('infoDisplay').style.display = 'block';
    });
    document.getElementById('btnTogglePasswordForm').addEventListener('click', function () {
        const form = document.getElementById('passwordForm');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            this.textContent = 'Cancelar Cambio de Contraseña';
        } else {
            form.style.display = 'none';
            this.textContent = 'Cambiar Contraseña';
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = "Mi Perfil";
require '../includes/layout.php';
?>
