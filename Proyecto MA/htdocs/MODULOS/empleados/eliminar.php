<?php
require '../../PHP/conexion.php';

// Asegúrate de que el contenido sea JSON.
header('Content-Type: application/json');

// Array para la respuesta.
$response = [
    'success' => false,
    'message' => 'Error desconocido.'
];

try {
    // Verifica si el ID está presente y es válido.
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID no especificado o inválido.');
    }

    $id = intval($_GET['id']);

    // Prepara y ejecuta la consulta.
    $query = $conn->prepare("DELETE FROM empleados WHERE id = ?");
    $query->bind_param("i", $id);

    if ($query->execute()) {
        $response['success'] = true;
        $response['message'] = 'Registro eliminado con éxito.';
    } else {
        throw new Exception('Error al eliminar el registro: ' . $conn->error);
    }

    // Limpia la consulta.
    $query->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    // Cierra la conexión.
    $conn->close();
    // Devuelve la respuesta como JSON.
    echo json_encode($response);
    exit;
}
?>
