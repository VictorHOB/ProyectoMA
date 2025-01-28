<?php
require '../../PHP/conexion.php';

// Configurar el encabezado para enviar JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    // Validar los datos recibidos
    $cedula = filter_input(INPUT_POST, 'cedula', FILTER_SANITIZE_STRING);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_STRING);

    // Verificar si alguna variable está vacía o no es válida
    if (!$cedula || !$correo) {
        throw new Exception("Datos incompletos o inválidos");
    }

    // Comprobar si ya existe un empleado con la misma cédula
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("Ya existe un empleado con esa cédula.");
    }
    $stmt->close();

    // Obtener el nombre del empleado utilizando la cédula
    $stmt = $conn->prepare("SELECT nombres FROM empleados WHERE numero_identificacion = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Empleado no encontrado.");
    }

    $nombre_empleado = $result->fetch_assoc()['nombres'];
    $stmt->close();

    // Generar el hash de la contraseña utilizando la cédula
    $hashedPassword = password_hash($cedula, PASSWORD_DEFAULT);

    // Insertar los datos
    $stmt = $conn->prepare("INSERT INTO usuarios (cedula, correo, contraseña_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $cedula, $correo, $hashedPassword);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Registro agregado correctamente',
            'nombre_empleado' => $nombre_empleado,
            'id_nuevo' => $conn->insert_id // Obtener el ID recién insertado
        ];
    } else {
        throw new Exception("Error al insertar el registro: " . $conn->error);
    }

    $stmt->close();
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
    echo json_encode($response);
    exit;
}
?>
