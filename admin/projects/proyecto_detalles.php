<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

if (!isset($_GET['proyecto_id'])) {
    die("Proyecto no identificado");
}

$proyecto_id = intval($_GET['proyecto_id']);

try {
    $conn = new PDO("mysql:host=localhost;dbname=TEC", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ✅ Obtener proyecto con cliente
    $stmt = $conn->prepare("
        SELECT p.*, c.nombre AS cliente_nombre
        FROM proyectos p
        LEFT JOIN clientes c ON p.cliente_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$proyecto_id]);
    $proyecto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proyecto) {
        die("Proyecto no encontrado");
    }

    // ✅ Tareas
    $stmt = $conn->prepare("SELECT id, descripcion, estado FROM tareas WHERE proyecto_id = ?");
    $stmt->execute([$proyecto_id]);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error DB: " . $e->getMessage());
}

ob_start();
?>

<!-- ✅ DETALLES DEL PROYECTO -->
<div class="tabla-container mb-4">
    <h2><i class="fas fa-folder-open"></i> Detalles del Proyecto</h2>
    <table>
        <tbody>
            <tr>
                <th>Nombre:</th>
                <td><?= htmlspecialchars($proyecto['nombre']) ?></td>
            </tr>
            <tr>
                <th>Cliente:</th>
                <td><?= htmlspecialchars($proyecto['cliente_nombre'] ?? 'No asignado') ?></td>
            </tr>
            <tr>
                <th>Descripción:</th>
                <td><?= htmlspecialchars($proyecto['descripcion']) ?></td>
            </tr>
            <tr>
                <th>Fecha de Inicio:</th>
                <td><?= htmlspecialchars($proyecto['fecha_inicio'] ?? 'No definida') ?></td>
            </tr>
            <tr>
                <th>Fecha Final Prevista:</th>
                <td><?= htmlspecialchars($proyecto['fecha_fin'] ?? 'No definida') ?></td>
            </tr>
            <tr>
                <th>Avance:</th>
                <td>
                    <div class="progress" style="height: 20px; max-width: 300px;">
                        <div class="progress-bar" role="progressbar"
                             style="width: <?= intval($proyecto['porcentaje_avance']) ?>%;"
                             aria-valuenow="<?= intval($proyecto['porcentaje_avance']) ?>"
                             aria-valuemin="0" aria-valuemax="100">
                             <?= intval($proyecto['porcentaje_avance']) ?>%
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- ✅ KANBAN -->
<h3 class="text-white mb-3">Tablero Kanban</h3>
<div class="row g-4">
<?php
$estados = [
    'pendiente' => 'Por Hacer',
    'en_proceso' => 'En Proceso',
    'completada' => 'Completadas'
];
foreach ($estados as $estado => $titulo):
?>
  <div class="col-md-4">
    <div class="kanban-column p-3 bg-dark text-white rounded shadow">
      <h5 class="text-center"><?= $titulo ?></h5>
      <div class="kanban-list" id="<?= $estado ?>" ondragover="allowDrop(event)" ondrop="drop(event, '<?= $estado ?>')">
        <?php foreach ($tareas as $tarea): ?>
          <?php if ($tarea['estado'] === $estado): ?>
            <div class="kanban-card p-2 mb-2 bg-secondary rounded"
                 draggable="true"
                 ondragstart="drag(event)"
                 data-id="<?= $tarea['id'] ?>">
              <?= htmlspecialchars($tarea['descripcion']) ?>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>


<!-- ✅ Scripts Kanban -->
<script>
let dragged;

function allowDrop(ev) {
  ev.preventDefault();
}

function drag(ev) {
  dragged = ev.target;
}

function drop(ev, nuevoEstado) {
  ev.preventDefault();
  const dropZone = ev.target.closest('.kanban-list');
  if (dragged && dropZone) {
    dropZone.appendChild(dragged);

    const tareaId = dragged.getAttribute('data-id');
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "/TEC/vistas/actualizar_estado.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("tarea_id=" + tareaId + "&estado=" + nuevoEstado);
  }
}
</script>

<!-- ✅ Scripts Gantt -->
<script src="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/frappe-gantt/dist/frappe-gantt.css" />

<script>
const tasks = [
<?php foreach ($tareas as $t): ?>
{
  id: "<?= $t['id'] ?>",
  name: "<?= htmlspecialchars($t['descripcion']) ?>",
  start: "<?= date('Y-m-d') ?>",
  end: "<?= date('Y-m-d', strtotime('+3 days')) ?>",
  progress: <?= $t['estado'] === 'completada' ? 100 : ($t['estado'] === 'en_proceso' ? 50 : 0) ?>
},
<?php endforeach; ?>
];

new Gantt("#gantt", tasks, {
  view_mode: 'Week',
  custom_popup_html: null
});
</script>

<!-- ✅ ESTILOS -->
<style>
.tabla-container {
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.05);
  padding: 20px;
  margin-bottom: 25px;
}

.tabla-container h2 {
  color: #4a4a4a;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
}

.tabla-container h2 i {
  margin-right: 10px;
  color: #6e8efb;
}

.tabla-container table {
  width: 100%;
  border-collapse: collapse;
}

.tabla-container th,
.tabla-container td {
  padding: 10px 15px;
  border-bottom: 1px solid #e1e5eb;
  text-align: left;
}

.tabla-container th {
  background-color: #f8f9fa;
  font-weight: 600;
  width: 200px;
}

.tabla-container tr:hover {
  background-color: #f8f9fa;
}

.kanban-column {
  min-height: 400px;
  background-color: #2c2c2c;
}
.kanban-list {
  min-height: 200px;
  border: 2px dashed #555;
  padding: 10px;
  border-radius: 8px;
}
.kanban-card {
  cursor: grab;
  background-color: #495057;
  transition: transform 0.2s ease;
}
.kanban-card:hover {
  transform: scale(1.02);
}
#gantt {
  background: #fff;
  border-radius: 10px;
  padding: 15px;
}
</style>

<?php
$content = ob_get_clean();
$title = "Detalles del Proyecto";
require '../../includes/layout.php';
?>
