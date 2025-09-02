<?php
$output = shell_exec('"C:\\Users\\Jair CG\\AppData\\Local\\Programs\\Python\\Python313\\python.exe" "C:\\xampp\\htdocs\\TEC\\graficos\\grafico_pastel.py" 2>&1');
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Historial de Tareas</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
  <h1 class="mb-4"><i class="fas fa-chart-pie"></i> Historial de Tareas</h1>

  <!-- Debug del output -->
<div class="row">
  <!-- Card Gráfico de Pastel -->
  <div class="col-md-6 mb-4">
    <div class="card bg-dark text-white">
      <div class="card-header">
        <i class="fas fa-chart-pie"></i> Porcentaje de Tareas por Estado
      </div>
      <div class="card-body text-center">
        <img src="/TEC/graficos/grafico_pastel.png" alt="Gráfico de Pastel" class="img-fluid">
      </div>
    </div>
  </div>

  <!-- Card Gráfico de Barras -->
  <div class="col-md-6 mb-4">
    <div class="card bg-dark text-white">
      <div class="card-header">
        <i class="fas fa-chart-bar"></i> Tareas Asignadas por Usuario
      </div>
      <div class="card-body text-center">
        <img src="/TEC/graficos/grafico_barras.png" alt="Gráfico de Barras" class="img-fluid">
      </div>
    </div>
  </div>
</div>


<!-- FontAwesome para iconos -->
<script src="https://kit.fontawesome.com/a2d9d5c12c.js" crossorigin="anonymous"></script>
<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$content = ob_get_clean();
$title = "Historial de Tareas";
require '../includes/layout.php';