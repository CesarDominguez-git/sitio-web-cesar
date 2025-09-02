<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}

require_once '../config/Database.php';
$pdo = Database::connect();

// KPIs
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$totalProyectos = $pdo->query("SELECT COUNT(*) FROM proyectos")->fetchColumn();
$totalTareas = $pdo->query("SELECT COUNT(*) FROM tareas")->fetchColumn();
$tareasPendientes = $pdo->query("SELECT COUNT(*) FROM tareas WHERE estado = 'pendiente'")->fetchColumn();

// Datos para gráfico pastel: % tareas por estado
$stmt = $pdo->query("
    SELECT estado, COUNT(*) as total
    FROM tareas
    GROUP BY estado
");
$tareasPorEstado = $stmt->fetchAll(PDO::FETCH_ASSOC);

$estados = [];
$totalesPorEstado = [];
foreach ($tareasPorEstado as $row) {
    $estados[] = $row['estado'];
    $totalesPorEstado[] = $row['total'];
}

// Datos para gráfico barras: tareas por proyecto
$stmt2 = $pdo->query("
    SELECT p.nombre AS proyecto, COUNT(t.id) AS total_tareas
    FROM proyectos p
    LEFT JOIN tareas t ON p.id = t.proyecto_id
    GROUP BY p.id, p.nombre
");
$tareasPorProyecto = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$proyectos = [];
$totalesPorProyecto = [];
foreach ($tareasPorProyecto as $row) {
    $proyectos[] = $row['proyecto'];
    $totalesPorProyecto[] = $row['total_tareas'];
}

ob_start();
?>

<h1 class="mb-4">Panel de Administrador</h1>
<p>Bienvenido, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong> (Administrador)</p>

<!-- Acciones rápidas -->
<div class="mb-4">
    <a href="user/crear_usuario.php" class="btn btn-primary me-2"><i class="fas fa-user-plus me-1"></i> Crear Usuario</a>
    <a href="crear_tarea.php" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i> Crear Tarea</a>
</div>

<!-- Tarjetas KPI -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-bg-info mb-3">
            <div class="card-body">
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text fs-4"><?= $totalUsuarios ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-secondary mb-3">
            <div class="card-body">
                <h5 class="card-title">Proyectos</h5>
                <p class="card-text fs-4"><?= $totalProyectos ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning mb-3">
            <div class="card-body">
                <h5 class="card-title">Tareas</h5>
                <p class="card-text fs-4"><?= $totalTareas ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-danger mb-3">
            <div class="card-body">
                <h5 class="card-title">Pendientes</h5>
                <p class="card-text fs-4"><?= $tareasPendientes ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Distribución de Tareas</div>
            <div class="card-body">
                <canvas id="graficoPastel"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Tareas por Proyecto</div>
            <div class="card-body">
                <canvas id="graficoBarras"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctxPastel = document.getElementById('graficoPastel').getContext('2d');
    const graficoPastel = new Chart(ctxPastel, {
        type: 'pie',
        data: {
            labels: <?= json_encode($estados) ?>,
            datasets: [{
                data: <?= json_encode($totalesPorEstado) ?>,
                backgroundColor: ['#ffc107', '#0d6efd', '#198754', '#dc3545']
            }]
        }
    });

    const ctxBarras = document.getElementById('graficoBarras').getContext('2d');
    const graficoBarras = new Chart(ctxBarras, {
        type: 'bar',
        data: {
            labels: <?= json_encode($proyectos) ?>,
            datasets: [{
                label: 'Tareas',
                data: <?= json_encode($totalesPorProyecto) ?>,
                backgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = "Dashboard Administrador";
require '../includes/layout.php';
?>
