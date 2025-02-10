<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();

require '../PHP/auth.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== '0' && $_SESSION['role'] !== '2')) {
    header("Location: ../modular.php");
    exit();
}

// Conexión a la base de datos
require '../PHP/conexion.php';

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
    <?php
    $configFile = '../PHP/config.json';
    $configData = json_decode(file_get_contents($configFile), true); // Leer JSON

    if (isset($configData['zona_horaria']['opciones'])) {
        $zonasHorarias = $configData['zona_horaria']['opciones'];
    } else {
        $zonasHorarias = [];
    }
    $jefe_zonal=$configData['jefe_zonal'];
    // Realizamos la consulta para obtener los empleados
    $query = "SELECT id, nombres FROM empleados order by nombres";
    $result = $conn->prepare($query);
    $result->execute();
    $result = $result->get_result();

    // Verificamos si la consulta tuvo resultados
    $empleados = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $empleados[] = $row;
        }
    }
    ?>

    <main>
        <form action="configuracion/guardar.php" method="POST">
            <table>
                <tr>
                    <td><strong>CONFIGURACIÓN</strong></td>
                    <td><strong>VALORES</strong></td>
                </tr>
                <tr>
                    <td>Jefe Zonal</td>
                    <td>
                        <select name="jefe_zonal" id="jefe_zonal">
                            <?php foreach ($empleados as $empleado) : ?>
                                
                                <option value="<?= $empleado['id']; ?>" <?= ($jefe_zonal == $empleado['id']) ? 'selected' : ''; ?>>
                                    <?= htmlspecialchars($empleado['nombres']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td>Permitir Permisos Atrasados</td>
                    <td>
                        <input type="checkbox" name="permitir_permisos_anteriores" id="permitir_permisos_anteriores" value="1" <?= isset($configData['permitir_permisos_anteriores']) && $configData['permitir_permisos_anteriores'] ? 'checked' : ''; ?>>
                    </td>
                </tr>
            </table>

            <button type="submit">Guardar Cambios</button>
        </form>

    </main>

    <footer id="footer">
        Solicitar ayuda
    </footer>
</body>
</html>
