<?php
require '../../PHP/conexion.php';

// Configurar el encabezado para enviar JSON
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    // Validar los datos recibidos
    $cedula = filter_input(INPUT_POST, 'cedulaa', FILTER_SANITIZE_STRING);
    $nombres = filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING);
    $modalidad = filter_input(INPUT_POST, 'modalidad', FILTER_SANITIZE_STRING);
    $codigo = filter_input(INPUT_POST, 'codigo', FILTER_SANITIZE_STRING);
    $canton_id = filter_input(INPUT_POST, 'canton', FILTER_VALIDATE_INT);
    $puesto_id = filter_input(INPUT_POST, 'puesto', FILTER_SANITIZE_STRING);  // ID del puesto
    $oficina_id = filter_input(INPUT_POST, 'oficina', FILTER_SANITIZE_STRING);

    // Verificar si alguna variable está vacía o no es válida
if (!$cedula || !$nombres || !$modalidad || !$codigo || !$canton_id || !$puesto_id || !$oficina_id) {
    throw new Exception(
        "Datos incompletos o inválidos. " .
        "Valores recibidos: cedula = " . var_export($cedula, true) . ", " .
        "nombres = " . var_export($nombres, true) . ", " .
        "modalidad = " . var_export($modalidad, true) . ", " .
        "codigo = " . var_export($codigo, true) . ", " .
        "canton_id = " . var_export($canton_id, true) . ", " .
        "puesto_id = " . var_export($puesto_id, true) . ", " .
        "oficina_id = " . var_export($oficina_id, true)
    );
}

    // Comprobar si ya existe un empleado con la misma cédula
    $stmt = $conn->prepare("SELECT id FROM empleados WHERE numero_identificacion = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        throw new Exception("Ya existe un empleado con esa cédula.");
    }
    $stmt->close();


    // Obtener el nombre del puesto utilizando el ID
    $stmt = $conn->prepare("SELECT nombre_puesto FROM puestos WHERE id = ?");
    $stmt->bind_param("i", $puesto_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Puesto no encontrado.");
    }

    $nombre_puesto = $result->fetch_assoc()['nombre_puesto'];
    $stmt->close();

    // Obtener el nombre de oficina utilizando el ID
    $stmt = $conn->prepare("SELECT nombre FROM oficinatecnica WHERE id = ?");
    $stmt->bind_param("i", $oficina_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Oficina no encontrada.");
    }

    $nombre_oficina = $result->fetch_assoc()['nombre'];
    $stmt->close();

    // Obtener la provincia en base al cantón seleccionado
    $stmt = $conn->prepare("SELECT provincia_id FROM cantones WHERE id = ?");
    $stmt->bind_param("i", $canton_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Cantón no encontrado.");
    }

    $provincia_id = $result->fetch_assoc()['provincia_id'];
    $stmt->close();
    
    // Insertar los datos
    $stmt = $conn->prepare("
        INSERT INTO empleados (numero_identificacion, nombres, modalidad_laboral, codigo, provincia_id, canton_id, puesto_id,oficina_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssiii", $cedula, $nombres, $modalidad, $codigo, $provincia_id, $canton_id, $puesto_id, $oficina_id);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Registro agregado correctamente',
            'nombre_puesto' => $nombre_puesto,  // Incluir el nombre del puesto en la respuesta
            'nombre_oficina' => $nombre_oficina,  // Incluir el nombre del puesto en la respuesta
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