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

    // Cargar tareas del proyecto
    $stmt = $conn->prepare("SELECT id, descripcion, estado FROM tareas WHERE proyecto_id = ?");
    $stmt->execute([$proyecto_id]);
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

ob_start();
?>

<h2 class="text-center mb-4">Kanban del Proyecto</h2>
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
            <div class="kanban-list" id="<?= $estado ?>" data-estado="<?= $estado ?>" ondragover="allowDrop(event)" ondrop="drop(event, '<?= $estado ?>')">
                <?php foreach ($tareas as $tarea): ?>
                    <?php if ($tarea['estado'] === $estado): ?>
                        <div class="kanban-card p-2 mb-2 bg-secondary rounded" draggable="true" ondragstart="drag(event)" data-id="<?= $tarea['id'] ?>">
                            <?= htmlspecialchars($tarea['descripcion']) ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

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
    if (dragged && dragged.parentNode.id !== nuevoEstado) {
        ev.target.closest('.kanban-list').appendChild(dragged);

        const tareaId = dragged.getAttribute('data-id');
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "actualizar_estado.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.send("tarea_id=" + tareaId + "&estado=" + nuevoEstado);
    }
}
</script>

<style>
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
</style>

<?php
$content = ob_get_clean();
$title = "Tablero Kanban";
require '../includes/layout.php';
?>
