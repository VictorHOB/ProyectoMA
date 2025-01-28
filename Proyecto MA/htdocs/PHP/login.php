<?php
session_start();
include 'conexion.php'; // Archivo donde conectas a tu base de datos

// Obtener los datos del formulario
$email = $_POST['email'];
$password = $_POST['password'];

// Verificar si el correo pertenece al dominio permitido
/*
$allowed_domain = "@ambiente.gob.ec";
if (strpos($email, $allowed_domain) === false) {
    $_SESSION['login_message'] = "Acceso restringido a correos del dominio ambiente.gob.ec.";
    header("Location: ../index.php"); // Redirigir al login
    exit();
}
*/

// Consulta para obtener el usuario con el email proporcionado
$sql = "SELECT a.id, cedula, nombres, correo, contraseña_hash, rol FROM usuarios as a left join empleados as b on a.cedula=b.numero_identificacion WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si el usuario existe en la base de datos
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verificar la contraseña
    if (password_verify($password, $user['contraseña_hash'])) {
        // Crear la sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['cedula']=$user['cedula'];
        $_SESSION['nombre']=$user['nombres'];
        $_SESSION['email'] = $user['correo'];
        $_SESSION['role'] = $user['rol'];
        $_SESSION['login_message'] = "Bienvenido, has iniciado sesión correctamente.";
        
        // Redirigir al módulo principal
        header("Location: ../modular.php");
        exit();
    } else {
        // Contraseña incorrecta
        $_SESSION['login_message'] = "Contraseña incorrecta.";
    }
} else {
    // Usuario no encontrado
    $_SESSION['login_message'] = "Usuario no encontrado.";
}

// Redirigir al formulario de inicio de sesión con el mensaje de error
header("Location: ../index.php");
exit();
?>

