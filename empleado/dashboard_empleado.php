<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'empleado') {
    header('Location: ../public/login.php');
    exit;
}


ob_start();
?>

<h2>Panel del Empleado</h2>
<p>Hola, <?= htmlspecialchars($_SESSION['user_name']) ?> 👋</p>

<p>Aquí puedes ver tus tareas asignadas:</p>




<?php
$content = ob_get_clean();
$title = "Dashboard Empleado";
require '../includes/layout.php';
?>
