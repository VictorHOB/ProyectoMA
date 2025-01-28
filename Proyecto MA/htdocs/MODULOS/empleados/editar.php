<?php
// gestor/editar.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Conectar a la base de datos
    require '../../PHP/conexion.php';

    // Verificar si los datos están definidos
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $numero_identificacion = isset($_POST['numero_identificacion']) ? $_POST['numero_identificacion'] : null;
    $nombres = isset($_POST['nombres']) ? $_POST['nombres'] : null;
    $aux = isset($_POST['modalidad']) ? $_POST['modalidad'] : null;
    $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : null;
    $puesto_id = isset($_POST['puesto']) ? $_POST['puesto'] : null;
    $oficina = isset($_POST['oficina']) ? $_POST['oficina'] : null;

    // Determinar la modalidad laboral
    $modalidades = [
        1 => "NOMBRAMIENTO",
        2 => "CONTRATO INDEFINIDO",
        3 => "CONTRATOS OCASIONALES"
    ];
    $modalidad_laboral = $modalidades[$aux] ?? "DESCONOCIDO";

    try {
        // Actualizar el registro en la base de datos
        $query = "UPDATE empleados SET numero_identificacion = ?, nombres = ?, modalidad_laboral = ?, codigo = ?, puesto_id = ?, oficina_id = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sssiiii', $numero_identificacion, $nombres, $modalidad_laboral, $codigo, $puesto_id, $oficina, $id);
            
        if (!$stmt->execute()) {
            throw new Exception("No se pudo actualizar el registro.");
        }

        // Consultar el nombre del empleado que responde
        $query_oficina = "SELECT nombre FROM oficinatecnica WHERE id = ?";
        $stmt_oficina = $conn->prepare($query_oficina);
        $stmt_oficina->bind_param('i', $oficina);
        $stmt_oficina->execute();
        $stmt_oficina->bind_result($nombre_oficina);
        if (!$stmt_oficina->fetch()) {
            throw new Exception("Oficina no encontrada.");
        }
        $stmt_oficina ->close();

        // Consultar el nombre del puesto
        $query_puesto = "SELECT nombre_puesto FROM puestos WHERE id = ?";
        $stmt_puesto = $conn->prepare($query_puesto);
        $stmt_puesto->bind_param('i', $puesto_id);
        $stmt_puesto->execute();
        $result_puesto = $stmt_puesto->get_result();

        if ($result_puesto->num_rows > 0) {
            $row = $result_puesto->fetch_assoc();
            $nombre_puesto = $row['nombre_puesto'];
        } else {
            throw new Exception("Puesto no encontrado para el ID: $puesto_id y $oficina");
        }
        $stmt_puesto->close();


        // Respuesta exitosa
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $id,
            'numero_identificacion' => $numero_identificacion,
            'nombres' => $nombres,
            'modalidad_laboral' => $modalidad_laboral,
            'codigo' => $codigo,
            'puesto' => $nombre_puesto,
            'oficina' => $nombre_oficina
        ]);
    } catch (Exception $e) {
        // Manejo de errores
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        if (isset($conn)) {
            $conn->close();
        }
    }
}
?>



