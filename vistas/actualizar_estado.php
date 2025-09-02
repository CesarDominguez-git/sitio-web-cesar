<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tarea_id = intval($_POST['tarea_id']);
    $nuevo_estado = $_POST['estado'];

    if (!in_array($nuevo_estado, ['pendiente', 'en_proceso', 'completada'])) {
        http_response_code(400);
        exit("Estado inválido");
    }

    try {
        $conn = new PDO("mysql:host=localhost;dbname=TEC", "root", "");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE tareas SET estado = :estado WHERE id = :id");
        $stmt->execute([
            'estado' => $nuevo_estado,
            'id' => $tarea_id
        ]);

        echo "OK";
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Error de base de datos: " . $e->getMessage();
    }
}
?>
