<?php
require '../../PHP/conexion.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "DELETE FROM usuarios WHERE id = $id";
    if ($conn->query($query)) {
        echo "Registro eliminado con éxito.";
    } else {
        echo "Error al eliminar el registro: " . $conn->error;
    }
} else {
    echo "ID no especificado.";
}
?>
