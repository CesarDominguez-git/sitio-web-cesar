<?php
require_once(__DIR__ . '/../../config/Database.php');

$db = Database::connect();

if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $db->prepare("DELETE FROM proyectos WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: proyecto_gestion.php');
    exit;
}

$stmt = $db->query("
    SELECT p.*, c.nombre AS cliente_nombre 
    FROM proyectos p 
    LEFT JOIN clientes c ON p.cliente_id = c.id
");
$proyectos = $stmt->fetchAll();

$title = "Gestión de Proyectos";
ob_start();
?>

<div class="tabla-container">
    <h2><i class="fas fa-project-diagram"></i> Gestión de Proyectos</h2>

    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Cliente</th>
                <th>Descripción</th>
                <th>Avance</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proyectos as $proyecto): ?>
                <tr>
                    <td><?= htmlspecialchars($proyecto['nombre']) ?></td>
                    <td><?= htmlspecialchars($proyecto['cliente_nombre'] ?? 'Sin Cliente') ?></td>
                    <td><?= htmlspecialchars($proyecto['descripcion']) ?></td>
                    <td>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar text-dark" role="progressbar" style="width: <?= $proyecto['porcentaje_avance'] ?>%;" aria-valuenow="<?= $proyecto['porcentaje_avance'] ?>" aria-valuemin="0" aria-valuemax="100">
                                <?= $proyecto['porcentaje_avance'] ?>%
                            </div>
                        </div>
                    </td>
            <td>
                <div class="d-flex gap-2">
                    <a href="editar_proyecto.php?id=<?= $proyecto['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                    <a href="proyecto_detalles.php?proyecto_id=<?= $proyecto['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i> Detalles</a>
                </div>
            </td>
        </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <a href="crear_proyecto.php" class="btn btn-success">+ Nuevo Proyecto</a>
        <a href="/TEC/reportes/exportar_excel.php" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Exportar Proyectos a Excel
        </a>
    </div>
</div>

<style>
    .tabla-container {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-top: 25px;
    }
    h2 {
        color: #4a4a4a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }
    h2 i {
        margin-right: 10px;
        color: #6e8efb;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px 15px;
        border-bottom: 1px solid #e1e5eb;
        color: #333;
        text-align: left;
        vertical-align: middle;
    }
    th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    tr:hover {
        background-color: #f8f9fa;
    }
    .progress {
        background-color: #e9ecef;
        border-radius: 5px;
    }
    .progress-bar {
        background-color: #6e8efb;
    }
</style>

<?php
$content = ob_get_clean();
require_once(__DIR__ . '/../../includes/layout.php');
?>
