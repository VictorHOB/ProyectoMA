<?php
session_start();
session_destroy(); // Destruir todas las sesiones activas
header("Location: ../index.php"); // Redirigir a la página principal
exit();
?>
