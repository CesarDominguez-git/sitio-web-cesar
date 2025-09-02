<?php
// Asegurarse de iniciar la sesión correctamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Conexión y datos del usuario actual
$userData = [
    'nombre' => 'Usuario',
    'email' => '-',
    'telefono' => '-',
    'rol' => '',
    'foto' => ''
];

if (isset($_SESSION['user_id'])) {
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=TEC", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT nombre, email, telefono, rol, foto FROM usuarios WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $userData = $row;
        }
    } catch (PDOException $e) {
        // Manejar el error si lo deseas
    }
}

// Función para mostrar la foto de perfil o una por defecto
function getUserFotoUrl($foto) {
    $rutaServidor = '/TEC/uploads/' . $foto;
    $rutaReal = realpath(__DIR__ . '/../uploads/' . $foto);

    if (!empty($foto) && $rutaReal && file_exists($rutaReal)) {
        return $rutaServidor;
    } else {
        return '/TEC/uploads/default.png';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= htmlspecialchars($title ?? 'Gestión de Proyectos') ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <!-- Tu CSS -->
    <link rel="stylesheet" href="/TEC/assets/css/perfil_dropdown.css?v=1.1">
    <link rel="stylesheet" href="/TEC/assets/css/style.css">

    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(-45deg, #0f0f0f, rgb(42, 40, 40), rgb(20, 21, 20), rgb(0, 0, 0));
            background-size: 400% 400%;
            animation: fondoOscuro 15s ease infinite;
        }
        @keyframes fondoOscuro {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 260px;
            background-color: #343a40;
            padding-top: 1rem;
            overflow-y: auto;
            color: white;
            box-shadow: 2px 0 5px rgba(0,0,0,0.3);
            z-index: 1000;
        }
        #sidebar h3 {
            padding-left: 20px;
            margin-bottom: 1rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        #sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            border-left: 4px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        #sidebar .nav-link i {
            margin-right: 12px;
            font-size: 18px;
        }
        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background-color: #495057;
            color: #ffc107;
            border-left-color: #ffc107;
            text-decoration: none;
        }
        #content {
            margin-left: 260px;
            padding: 20px 30px;
            min-height: 100vh;
            color: white;
        }
    </style>
</head>
<body>

<!-- Barra lateral -->
<nav id="sidebar">
    <h3>
        <?= (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin') ? 'Admin Panel' : 'Empleado' ?>
    </h3>

    <?php if (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin'): ?>
        <a href="/TEC/admin/dashboard_admin.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
        <a href="/TEC/admin/user/usuarios.php" class="nav-link"><i class="fas fa-users"></i> Gestión de Usuarios</a>
        <a href="/TEC/tareas/tareas.php" class="nav-link"><i class="fas fa-tasks"></i> Gestión de Tareas</a>
        <a href="/TEC/admin/projects/proyecto_gestion.php" class="nav-link"><i class="fas fa-project-diagram"></i> Gestión de Proyectos</a>
        <a href="/TEC/admin/customers/clientes.php" class="nav-link"><i class="fas fa-handshake"></i> Gestión de Clientes</a>
        <a href="/TEC/admin/reportes.php" class="nav-link"><i class="fas fa-file-pdf"></i> Generar Reporte PDF</a>
    <?php else: ?>
        <a href="/TEC/empleado/dashboard_empleado.php" class="nav-link"><i class="fas fa-home"></i> Inicio</a>
        <a href="/TEC/empleado/ver_tareas.php" class="nav-link"><i class="fas fa-tasks"></i> Mis Tareas</a>
        <a href="/TEC/empleado/proyecto_ver.php" class="nav-link"><i class="fas fa-project-diagram"></i> Ver Proyectos</a>
        <a href="/TEC/empleado/tareas_ver.php" class="nav-link"><i class="fas fa-tasks"></i> Ver Tareas</a>
        <a href="/TEC/empleado/usuarios_ver.php" class="nav-link"><i class="fas fa-users"></i> Ver Usuarios</a>
        <a href="/TEC/empleado/reportes.php" class="nav-link"><i class="fas fa-file-pdf"></i> Generar Reporte PDF</a>
    <?php endif; ?>
</nav>

<!-- Dropdown de perfil -->
<?php if (isset($_SESSION['user_id'])): ?>
<div class="dropdown" style="position: absolute; top: 15px; right: 30px; z-index: 1100;">
    <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="perfilDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="<?= htmlspecialchars(getUserFotoUrl($userData['foto'])) ?>"
             alt="Perfil"
             class="rounded-circle me-2"
             width="36"
             height="36"
             style="object-fit: cover;">
        <?= htmlspecialchars($userData['nombre']) ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end p-3" aria-labelledby="perfilDropdown" style="min-width: 260px;">
        <li class="mb-2">
            <div class="text-start small">
                <strong><i class="fas fa-user me-2"></i><?= htmlspecialchars($userData['nombre']) ?></strong><br>
                <i class="fas fa-envelope me-2"></i><?= htmlspecialchars($userData['email']) ?><br>
                <i class="fas fa-phone me-2"></i><?= htmlspecialchars($userData['telefono']) ?><br>
                <i class="fas fa-user-tag me-2"></i><?= htmlspecialchars($userData['rol']) ?>
            </div>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="<?= (isset($_SESSION['user_rol']) && $_SESSION['user_rol'] === 'admin') ? '/TEC/admin/perfil.php' : '/TEC/empleado/perfil.php' ?>">
                <i class="fas fa-user me-2"></i> Ver Perfil
            </a>
        </li>
        <li>
            <a class="dropdown-item text-danger" href="/TEC/public/logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Cerrar Sesión
            </a>
        </li>
    </ul>
</div>
<?php endif; ?>

<!-- Contenido dinámico -->
<div id="content">
    <?= $content ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
