<?php
// Leer el archivo JSON
$config = json_decode(file_get_contents('config.json'), true);

// Verificar si se cargó correctamente el archivo
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error en el formato del JSON: " . json_last_error_msg();
    exit;
}

if (!$config) {
    echo "Error al cargar el archivo de configuración.";
    exit;
}

// Establecer la zona horaria usando JSON
$zona_horaria = $config['zona_horaria'];
date_default_timezone_set($zona_horaria); // Ajusta la zona horaria dinámica

// Obtener la hora actual y darle formato
echo date('d/m/Y - H:i'); 
?>
