<?php
ob_start();

require_once(__DIR__ . '/../../config/Database.php');
$pdo = Database::connect();

function mostrarClientes($pdo) {
    $stmt = $pdo->query("SELECT id, nombre, telefono, email, deuda FROM clientes");

    echo '<div class="tabla-container">';
    echo '<h2><i class="fas fa-users"></i> Listado de Clientes</h2>';
    
    echo '<table>';
    echo '<thead><tr>
            <th>Nombre</th>
            <th>Teléfono</th>
            <th>Email</th>
            <th>Deuda</th>
            <th>Acciones</th>
          </tr></thead>';
    echo '<tbody>';
    
    while($cliente = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($cliente['nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($cliente['telefono']) . '</td>';
        echo '<td>' . htmlspecialchars($cliente['email']) . '</td>';
        echo '<td class="' . ($cliente['deuda'] > 0 ? 'deuda' : 'pagado') . '">S/ ' . number_format($cliente['deuda'], 2) . '</td>';
        echo '<td>
                <a href="editar_cliente.php?id=' . $cliente['id'] . '" class="btn btn-sm btn-warning me-2">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="eliminar_cliente.php?id=' . $cliente['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Seguro que deseas eliminar este cliente?\')">
                    <i class="fas fa-trash-alt"></i> Eliminar
                </a>
              </td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '<div class="leyenda"><span class="deuda"></span> Con deuda • <span class="pagado"></span> Al día</div>';
    echo '</div>';
}
?>

<!-- ESTILOS -->
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
    }
    th {
        background-color: #f8f9fa;
        font-weight: 600;
    }
    tr:hover {
        background-color: #f8f9fa;
    }
    .deuda {
        color: #e74c3c;
        font-weight: 600;
    }
    .pagado {
        color: #2ecc71;
        font-weight: 600;
    }
    .leyenda {
        margin-top: 15px;
        font-size: 14px;
        color: #7f8c8d;
    }
    .leyenda span {
        display: inline-block;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin-right: 5px;
    }
    .leyenda .deuda {
        background-color: #e74c3c;
    }
    .leyenda .pagado {
        background-color: #2ecc71;
    }
</style>

<?php
mostrarClientes($pdo);
$content = ob_get_clean();
$title = "Gestión de Clientes";
require_once(__DIR__ . '/../../includes/layout.php');
?>
