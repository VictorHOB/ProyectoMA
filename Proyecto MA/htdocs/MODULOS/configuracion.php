<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();

require '../PHP/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '0' && $_SESSION['role'] !== '2') {
    header("Location: ../modular.php");
    exit();
}

// Conexión a la base de datos
require '../PHP/conexion.php';
$configFile = '../PHP/config.json';
$configData = json_decode(file_get_contents($configFile), true); // Leer JSON

// Zonas horarias disponibles (esto es un ejemplo, puedes adaptarlo a tu caso)
$zonasHorarias = [
    "America/Guayaquil" => "America/Guayaquil",
    "America/Bogota" => "America/Bogota",
    "America/Lima" => "America/Lima",
    "Europe/Madrid" => "Europe/Madrid"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Configuración</title>
</head>
<body>
    <?php include '../PHP/header.php'; ?>

    <main>
        <form action="configuracion/guardar.php" method="POST">
            <?php
            foreach ($configData as $key => $value) {
                echo '<div>';
                
                // Etiqueta
                echo '<label for="' . $key . '">' . ucfirst(str_replace('_', ' ', $key)) . '</label>';
                
                // Campos basados en el tipo de valor
                if (is_bool($value)) {
                    // Checkbox para valores booleanos
                    echo '<input type="checkbox" name="' . $key . '" id="' . $key . '" value="1" ' . ($value ? 'checked' : '') . '>';
                } elseif ($key == 'zona_horaria') {
                    // Select para la zona horaria
                    echo '<select name="' . $key . '" id="' . $key . '">';
                    foreach ($zonasHorarias as $zona => $zonaLabel) {
                        echo '<option value="' . $zona . '" ' . ($value == $zona ? 'selected' : '') . '>' . $zonaLabel . '</option>';
                    }
                    echo '</select>';
                } else {
                    // Input para cadenas de texto y números
                    echo '<input type="text" name="' . $key . '" id="' . $key . '" value="' . htmlspecialchars($value) . '">';
                }

                echo '</div>';
            }
            ?>
            <button type="submit">Guardar Cambios</button>
        </form>
    </main>

    <footer id="footer">
        Solicitar ayuda
    </footer>
</body>
</html>


