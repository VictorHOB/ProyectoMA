<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();

// Conexión a la base de datos
require '../PHP/conexion.php';

// Obtener la lista de empleados para el selector
$empleados_query = "SELECT id, nombres FROM empleados";
$empleados_result = $conn->query($empleados_query);

// Filtro de empleado seleccionado
$empleado_id = $_SESSION['cedula'];

// Obtener el filtro por tipo de permiso, si se ha seleccionado
$tipo_permiso = isset($_GET['tipo_permiso']) ? $_GET['tipo_permiso'] : '0';

// Construir la consulta
$query = "SELECT a.id as ID, fecha_solicitud as 'FECHA SOLICITUD', b.nombres as EMPLEADO, tipo_permiso as 'TIPO PERMISO', fecha_desde as DESDE, fecha_hasta as HASTA, tiempo_total as 'TIEMPO TOTAL', 
                 motivo as MOTIVO, razon_comision as RAZON, observaciones as OBSERVACIONES, archivo_justificacion as JUSTIFICACIÓN, 
                 c.nombre as OFICINA, estado as ESTADO
          FROM permisos AS a 
          LEFT JOIN usuarios AS z ON a.user_id = z.id
          LEFT JOIN empleados AS b ON z.cedula = b.numero_identificacion 
          LEFT JOIN oficinatecnica AS c ON b.oficina_id = c.id";

// Crear un array para las condiciones de filtrado
$condiciones = [];

if ($empleado_id > 0) {
    $condiciones[] = "b.numero_identificacion = $empleado_id";
}

if ($tipo_permiso != '0') {
    $condiciones[] = "a.tipo_permiso = '$tipo_permiso'";
}

// Si hay condiciones, añadirlas a la consulta
if (count($condiciones) > 0) {
    $query .= " WHERE " . implode(" AND ", $condiciones);
}

$resultado = $conn->query($query);


// Calcular totales solo para permisos que no sean de comisión
$total_permisos = 0;
$horas_totales = 0;
$minutos_totales = 0;

if ($empleado_id > 0 && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        // Verificar si el permiso no es de comisión
        if ($fila['TIPO PERMISO'] !== 'comision') {
            $total_permisos++;

            // Extraer horas y minutos del formato "168 horas y 0 minutos"
            if (preg_match('/(\d+)\s*horas\s*y\s*(\d+)\s*minutos/', $fila['TIEMPO TOTAL'], $matches)) {
                $horas_totales += intval($matches[1]);
                $minutos_totales += intval($matches[2]);
            }
        }
    }

    // Ajustar minutos que excedan 60 a horas
    $horas_totales += floor($minutos_totales / 60);
    $minutos_totales = $minutos_totales % 60;

    // Reiniciar el puntero para mostrar los datos en la tabla
    $resultado->data_seek(0);
}

// Convertir horas totales a formato días, horas y minutos
$dias = floor($horas_totales / 8);
$horas_restantes = $horas_totales % 8;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Datos de la Tabla</title>
    <style>
        /* Estilos para los colores de la celda de estado */
        .estado-pendiente {
            background-color: yellow;
        }

        .estado-denegado {
            background-color: red;
            color: white;
        }

        .estado-aprobado {
            background-color: green;
            color: white;
        }
    </style>
</head>
<body>
    <?php include '../PHP/header.php'; ?>

    <main>

        <h1>Registros en la tabla</h1>

        <form method="GET" action="">
            <label for="empleado_id">Seleccionar empleado:</label>

            <label for="tipo_permiso">Seleccionar tipo de permiso:</label>
            <select name="tipo_permiso" id="tipo_permiso">
                <option value="0">Todos</option>
                <option value="comision" <?= isset($_GET['tipo_permiso']) && $_GET['tipo_permiso'] == 'comision' ? 'selected' : ''; ?>>Comisión</option>
                <option value="personal" <?= isset($_GET['tipo_permiso']) && $_GET['tipo_permiso'] == 'personal' ? 'selected' : ''; ?>>No Comisión</option>
            </select>

            <button type="submit">Filtrar</button>
        </form>

        <!-- Mostrar totales -->
        <?php if ($empleado_id > 0): ?>
            <p>Total permisos: <strong><?= $total_permisos ?></strong></p>
            <p>Tiempo total: <strong><?= $dias ?> días, <?= $horas_restantes ?> horas, <?= $minutos_totales ?> minutos</strong></p>
        <?php endif; ?>

        <!-- Tabla de registros -->
        <table border="1" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <?php
                    if ($resultado->num_rows > 0) {
                        $fila = $resultado->fetch_assoc();
                        foreach (array_keys($fila) as $columna) {
                            if ($columna != "contraseña_hash" && $columna != "creado_en") {
                                echo "<th style='padding: 8px; text-align: left;'>" . htmlspecialchars($columna) . "</th>";
                            }
                        }
                        $resultado->data_seek(0);
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($resultado->num_rows > 0) {
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($fila as $columna => $dato) {
                                if ($columna == 'ESTADO') {
                                    $estado_clase = '';

                                    switch ($dato) {
                                        case 'Pendiente':
                                            $estado_clase = 'estado-pendiente';
                                            break;
                                        case 'Denegado':
                                            $estado_clase = 'estado-denegado';
                                            break;
                                        case 'Aprobado':
                                            $estado_clase = 'estado-aprobado';
                                            break;
                                    }

                                    echo "<td class='" . $estado_clase . "' style='padding: 8px;'>" . htmlspecialchars($dato) . "</td>";
                                } elseif ($columna == 'JUSTIFICACIÓN' && !empty($dato)) {
                                    echo "<td style='padding: 8px;'><a href='/permisos/archivos/" . htmlspecialchars($dato) . "' download>Archivo</a></td>";
                                } else {
                                    echo "<td style='padding: 8px;'>" . htmlspecialchars($dato) . "</td>";
                                }
                            }
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='100%' style='text-align: center;'>No hay registros en la tabla</td></tr>";
                    }
                ?>
            </tbody>
        </table>
    </main>

    <footer id="footer">
        Solicitar ayuda
    </footer>
</body>
</html>
