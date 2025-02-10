<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();
require 'PHP/auth.php';
$rol = isset($_SESSION['role']) ? $_SESSION['role'] : null;

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/colores.css">
    <title>PERMISOS MINISTERIO</title>
</head>
    <?php include 'PHP/header.php'; ?>
<body>
    <div class="contenedor-modulos">
        <div class="modulos">
            <div class="modulo" onclick="window.location.href='MODULOS/permisos.php'">Solicitud de Permisos</div>
            <div class="modulo" onclick="window.location.href='MODULOS/visor_permisos.php'">Registro de Permisos</div>
            <div class="modulo" onclick="window.location.href='MODULOS/cambio_contra.php'">Cambio de Contraseña</div>
        </div>

        <?php if ($rol === '2'||$rol === '0'): ?>
        <div class="modulos">
            <div class="modulo" onclick="window.location.href='MODULOS/revisor_permisos.php'">Aprobación/Denegación de Permisos</div>
        </div>
        <?php endif; ?>
        
        <?php if ($rol === '0'): ?>
        <div class="modulos">
            <div class="modulo" onclick="window.location.href='MODULOS/gestor.php'">Gestor de Usuarios</div>
            <div class="modulo" onclick="window.location.href='MODULOS/empleados.php'">Gestor de Empleados </div>
            <div class="modulo"  onclick="window.location.href='MODULOS/gestor_permisos.php'">Gestor de Permisos</div>    
        </div>
        <?php endif; ?>

        <?php if ($rol === '0'): ?>
        <div class="modulos">
            <div class="modulo" onclick="window.location.href='MODULOS/configuracion.php'">Administrativo</div>
        </div>
        <?php endif; ?>
      
    </div>
    <footer>
        Solicitar ayuda
    </footer>
</body>
</html>



