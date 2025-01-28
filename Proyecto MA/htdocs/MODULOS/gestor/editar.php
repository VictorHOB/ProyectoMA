<?php
// gestor/editar.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conectar a la base de datos
    require '../../PHP/conexion.php';

    // Obtener los datos del formulario
    $id = $_POST['id'];
    $correo = $_POST['correo'];
    $rol = $_POST['rol'];

    // Actualizar el registro en la base de datos
    $query = "UPDATE usuarios SET correo = ?, rol = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssi', $correo, $rol, $id);

    if ($stmt->execute()) {
        // Respuesta exitosa
        echo json_encode(['success' => true, 'id' => $id, 'correo' => $correo, 'rol' => $rol]);
    } else {
        // Respuesta con error
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el registro']);
    }

    $stmt->close();
    $conn->close();
}
?>
