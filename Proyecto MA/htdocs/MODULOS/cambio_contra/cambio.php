<?php

session_start();
require '../../PHP/conexion.php'; // Asegura que este archivo define correctamente $conn

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id'] ?? null;
    $contrasena_actual = $_POST['contrasena_actual'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

    if (!$usuario_id) {
        echo json_encode(['status' => 'error', 'message' => 'No estás autenticado.']);
        exit;
    }

    if (empty($contrasena_actual) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    if ($nueva_contrasena !== $confirmar_contrasena) {
        echo json_encode(['status' => 'error', 'message' => 'Las contraseñas no coinciden.']);
        exit;
    }

    // Verificar la contraseña actual
    $stmt = $conn->prepare("SELECT contraseña_hash FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();

    if (!$usuario || !password_verify($contrasena_actual, $usuario['contraseña_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'La contraseña actual es incorrecta.']);
        exit;
    }

    // Actualizar la contraseña
    $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET contraseña_hash = ? WHERE id = ?");
    $stmt->bind_param("si", $hash_contrasena, $usuario_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Contraseña actualizada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar la contraseña.']);
    }

    $stmt->close();
    $conn->close();
}
?>
