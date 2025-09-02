<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_rol'], ['empleado', 'admin'])) {
  header('Location: ../public/login.php');
  exit;
}

// Ejecutar script Python

$output = shell_exec('"C:\\Users\\Jair CG\\AppData\\Local\\Programs\\Python\\Python313\\python.exe" "C:\\xampp\\htdocs\\TEC\\graficos\\grafico_pastel.py" 2>&1');



?>

<div class="container mt-4">
  <h2><i class="fas fa-chart-pie"></i> Distribución de Tareas por Estado</h2>
  <p>Este gráfico muestra el porcentaje de tareas agrupadas por su estado actual.</p>
  
  <img src="../graficos/grafico_pastel.png?rand=<?= rand(); ?>" alt="Gráfico de Tareas" class="img-fluid border rounded">

  <a href="ver_grafico.php" class="btn btn-primary mt-3">
    <i class="fas fa-sync-alt"></i> Actualizar Gráfico
  </a>
</div>

<?php
$content = ob_get_clean();
$title = "Gráfico de Tareas";
require '../includes/layout.php';
?>
