<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer el archivo JSON
    $configFile = '../../PHP/config.json';
    $configData = json_decode(file_get_contents($configFile), true);

    // Actualizar los valores con los datos del formulario
    foreach ($_POST as $key => $value) {
        // Si el valor es un checkbox, lo convertimos a booleano
        if (isset($value) && $value == '1') {
            $configData[$key] = true;
        } else {
            $configData[$key] = $value;
        }
    }

    // Guardar los cambios en el archivo JSON
    file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT));

    echo "¡Configuración guardada correctamente!";
}
?>
