<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require '../config/Database.php';
$db = new Database();
$pdo = Database::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tarea_id'], $_POST['encargado_id'])) {
        $tareaId = intval($_POST['tarea_id']);
        $encargadoId = intval($_POST['encargado_id']);

        $stmt = $pdo->prepare("UPDATE tareas SET encargado_id = ? WHERE id = ?");
        $stmt->execute([$encargadoId, $tareaId]);
    } elseif (isset($_POST['completar_tarea_id'])) {
        $tareaId = intval($_POST['completar_tarea_id']);
        $stmt = $pdo->prepare("UPDATE tareas SET estado = 'completada' WHERE id = ?");
        $stmt->execute([$tareaId]);
    }
}

$stmt = $pdo->query("SELECT t.id, t.descripcion, t.encargado_id, u.nombre AS encargado_nombre
                     FROM tareas t
                     LEFT JOIN usuarios u ON t.encargado_id = u.id
                     WHERE t.estado = 'pendiente'");
$tareas = $stmt->fetchAll();

$empleados = $pdo->query("SELECT id, nombre FROM usuarios")->fetchAll();

ob_start();
?>

<div class="container mt-5 text-dark">
    <div class="tabla-container">
        <h2><i class="fas fa-tasks"></i> Tareas Pendientes</h2>

        <?php if (count($tareas) === 0): ?>
            <p>No hay tareas pendientes.</p>
            <a href="../reportes/exportar_tareas.php" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar Tareas a Excel
            </a>
            <a href="/TEC/tareas/historial_tareas.php" class="btn btn-primary">
                <i class="fas fa-history"></i> Historial
            </a>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th>Encargado actual</th>
                        <th>Asignar a</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tareas as $tarea): ?>
                        <tr>
                            <td><?= htmlspecialchars($tarea['descripcion']) ?></td>
                            <td>
                                <?= $tarea['encargado_nombre'] 
                                    ? htmlspecialchars($tarea['encargado_nombre'])
                                    : '<span style="color: #7f8c8d;">No asignado</span>' ?>
                            </td>
                            <td>
                                <form method="POST" class="d-flex">
                                    <input type="hidden" name="tarea_id" value="<?= $tarea['id'] ?>">
                                    <select name="encargado_id" class="form-select me-2" required>
                                        <option value="" disabled selected>Seleccionar empleado</option>
                                        <?php foreach ($empleados as $emp): ?>
                                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Asignar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <a href="../reportes/exportar_tareas.php" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Exportar Tareas a Excel
                </a>
                <a href="/TEC/tareas/historial_tareas.php" class="btn btn-info">
                    <i class="fas fa-chart-pie"></i> Ver Gráfico de Tareas
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Tareas Pendientes";
require '../includes/layout.php';
?>
