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
<header id="header">
    <div id="ministerio">
        <label>Ministerio del Ambiente, Agua y Transición Ecológica</label>
    </div>
    <div id="headerimages">
        <img src="MEDIA/Logo1.png" alt="Imagen 1" style="height: 75px; width: auto;">
        <img src="MEDIA/Logo2.png" alt="Imagen 2" style="height: 75px; width: auto;">
    </div>
    <div id="talentohumano">
        <label>TALENTO HUMANO<br>PERMISO OCASIONAL</label>
    </div>
</header>
<body>
    <!-- Contenedor principal para los módulos -->
    <div class="contenedor-modulos">
        <!-- Primer bloque de módulos -->
        <div class="modulos" id="permisos">
            <div class="modulo" onclick="window.location.href='MODULOS/permisos.php'">
                <label>Solicitud de Permisos</label>
            </div>
            <div class="modulo" onclick="window.location.href='MODULOS/visor_permisos.php'">
                            <label>Registro de Permisos Personales</label>
            </div>

                        <!-- DESARROLLO 
                        
                        <div class="modulo" onclick="window.location.href='MODULOS/vacaciones.php'">
                            <label>Calendario</label>
                        </div>
                        <div class="modulo" onclick="window.location.href='MODULOS/accion.html'">
                            <label>Acciones de Personal</label>
                        </div>
                        -->
        </div>

        <!-- Segundo bloque de módulos -->
        <div class="modulos" id="responsables">
            <!-- REVISOR DE PERMISOS -->
            <?php if ($rol === '2'||$rol === '0'): ?>
            <div class="modulo" onclick="window.location.href='MODULOS/revisor_permisos.php'">
                <label>Aprobación/Denegación de Permisos</label>
            </div>
            <?php endif; ?>
        </div>

        <!-- Segundo bloque de módulos -->
        <div class="modulos" id="gestion">
            
            <?php if ($rol === '0'): ?>
            <div class="modulo" onclick="window.location.href='MODULOS/gestor.php'">
                <label>Gestor de Usuarios</label>
            </div>

            <div class="modulo" onclick="window.location.href='MODULOS/empleados.php'">
                <label>Gestor de Empleados</label>
            </div>

            <div class="modulo"  onclick="window.location.href='MODULOS/gestor_permisos.php'">
                <label>Gestor de Permisos</label>
            </div>
            <?php endif; ?>
        </div>

        <!-- Segundo bloque de módulos -->
        <div class="modulos" id="administrativo">
            <!-- Mostrar Gestor de Usuarios solo si el rol es "admin" -->
            <?php if ($rol === '-1'): ?>
            <div class="modulo" onclick="window.location.href='MODULOS/configuracion.php'">
                <label>Configuración del Sistema</label>
            </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="JS/app.js"></script>
</body>


<footer id="footer">
    Solicitar ayuda
</footer>

</html>
