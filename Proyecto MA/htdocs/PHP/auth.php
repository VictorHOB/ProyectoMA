<?php
session_start();

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión iniciada, redirige al login
    header("Location: ../index.php");
    exit();
}
?>
