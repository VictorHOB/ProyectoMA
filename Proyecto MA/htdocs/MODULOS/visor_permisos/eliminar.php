<?php
session_start();
require '../../PHP/conexion.php';

// Verificar si el método de solicitud es DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $permisoId = $data['id'];

    // Verificar si el permiso existe y está en estado "Pendiente"
    $query = "SELECT estado FROM permisos WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $permisoId);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($estado);
        $stmt->fetch();

        if ($estado === 'Pendiente') {
            // Eliminar el permiso
            $deleteQuery = "DELETE FROM permisos WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $permisoId);

            if ($deleteStmt->execute()) {
                echo json_encode(["success" => true, "message" => "Permiso eliminado correctamente."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error al eliminar el permiso."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Solo se pueden eliminar permisos pendientes."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "El permiso no existe."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>