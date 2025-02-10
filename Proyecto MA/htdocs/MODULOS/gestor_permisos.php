<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();
require '../PHP/auth.php';

// Conexión a la base de datos
require '../PHP/conexion.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '0') {
    header("Location: ../modular.php");
    exit();
}

// Obtener la lista para el filtro de empleados por nombre
$empleados_query = "SELECT z.id as user_id, b.nombres FROM usuarios AS z LEFT JOIN empleados AS b ON z.cedula = b.numero_identificacion order by b.nombres asc";
$empleados_result = $conn->query($empleados_query);

// Filtro de usuario seleccionado
$empleado_id = isset($_GET['empleado_id']) ? intval($_GET['empleado_id']) : 0;

// Obtener el filtro por tipo de permiso, si se ha seleccionado
$tipo_permiso = isset($_GET['tipo_permiso']) ? $_GET['tipo_permiso'] : '0';

// Obtener el filtro por tipo de permiso, si se ha seleccionado
$motivo_permiso = isset($_GET['motivo_permiso']) ? $_GET['motivo_permiso'] : '0';

// Construir la consulta
$query = "SELECT a.id as ID, fecha_solicitud as 'FECHA SOLICITUD', b.nombres as EMPLEADO, tipo_permiso as 'TIPO PERMISO', motivo as MOTIVO, razon_comision as RAZON,
                 fecha_desde as DESDE, fecha_hasta as HASTA, tiempo_total as 'TIEMPO TOTAL',reincorporacion as RETORNA,  
                  observaciones as OBSERVACIÓN, observaciones_jefe as 'OBSERVACIÓN JEFE', archivo_justificacion as JUSTIFICACIÓN, 
                 c.nombre as OFICINA,(select nombres from empleados as z where z.id=a.responsable) as RESPONSABLE, estado as ESTADO
          FROM permisos AS a    
          LEFT JOIN usuarios AS z ON a.user_id = z.id
          LEFT JOIN empleados AS b ON z.cedula = b.numero_identificacion 
          LEFT JOIN oficinatecnica AS c ON b.oficina_id = c.id";

// Crear un array para las condiciones de filtrado
$condiciones = [];

if ($empleado_id > 0) {
    $condiciones[] = "a.user_id = $empleado_id";
}

if ($tipo_permiso != '0') {
    $condiciones[] = "a.tipo_permiso = '$tipo_permiso'";
}


if ($motivo_permiso != '0') {
    $condiciones[] = "a.motivo = '$motivo_permiso'";
}

// Si hay condiciones, se añaden a la consulta
if (count($condiciones) > 0) {
    $query .= " WHERE " . implode(" AND ", $condiciones);
}


$query .= " order by ID desc";
$resultado = $conn->query($query);


// Calcular totales solo para permisos que no sean de comisión y estén aprobados
$total_permisos = 0;
$horas_totales = 0;
$minutos_totales = 0;
$dias_totales = 0;

if ($empleado_id > 0 && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        // Verificar si el permiso no es de comisión y está aprobado
        if ($fila['TIPO PERMISO'] !== 'comision' && $fila['ESTADO'] === 'Aprobado') {
            $total_permisos++;

            // Extraer días, horas y minutos del formato "X días, X horas, X minutos"
            if (preg_match('/(\d+)\s*días?,\s*(\d+)\s*horas?,\s*(\d+)\s*minutos?/', $fila['TIEMPO TOTAL'], $matches)) {
                $dias_totales += intval($matches[1]);
                $horas_totales += intval($matches[2]);
                $minutos_totales += intval($matches[3]);
            }
        }
    }

    // Ajustar minutos que excedan 60 a horas
    $horas_totales += floor($minutos_totales / 60);
    $minutos_totales = $minutos_totales % 60;

    // Ajustar horas que excedan 24 a días
    $dias_totales += floor($horas_totales / 8);
    $horas_totales = $horas_totales % 8;

    // Reiniciar el puntero para mostrar los datos en la tabla
    $resultado->data_seek(0);
}

// Convertir horas totales a formato días, horas y minutos
$dias = $dias_totales;
$horas_restantes = $horas_totales;
$minutos_restantes = $minutos_totales;

// Aquí podrías usar las variables $dias, $horas_restantes, y $minutos_restantes para mostrarlas en el formato deseado.


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

        <!-- Formulario de selección de empleado -->

        <form method="GET" action="">
            <label for="empleado_id">Seleccionar empleado:</label>
            <select name="empleado_id" id="empleado_id">
                <option value="0">Todos</option>
                <?php
                if ($empleados_result->num_rows > 0) {
                    while ($empleado = $empleados_result->fetch_assoc()) {
                        $selected = $empleado['user_id'] == $empleado_id ? 'selected' : '';
                        echo "<option value='" . $empleado['user_id'] . "' $selected>" . htmlspecialchars($empleado['nombres']) . "</option>";
                    }
                }
                ?>
            </select>
            
            <label for="tipo_permiso">Seleccionar tipo de permiso:</label>
            <select name="tipo_permiso" id="tipo_permiso">
                <option value="0">Todos</option>
                <option value="comision" <?= isset($_GET['tipo_permiso']) && $_GET['tipo_permiso'] == 'comision' ? 'selected' : ''; ?>>Comisión</option>
                <option value="personal" <?= isset($_GET['tipo_permiso']) && $_GET['tipo_permiso'] == 'personal' ? 'selected' : ''; ?>>Personal</option>
            </select>
            <label for="tipo_permiso">Seleccionar motivo de permiso:</label>
            <select name="motivo_permiso" id="motivo_permiso">
                <option value="0">Todos</option>
            </select>
            <br><br>
            <button type="submit">Filtrar</button>
            <br><br>
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
                            if ($columna == "ID") {
                                echo "<th>" . htmlspecialchars($columna) . "</th>";
                            }else{
                                 echo "<th>" . htmlspecialchars($columna) . "</th>";

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

                                    echo "<td class='" . $estado_clase . "'; '>" . htmlspecialchars($dato) . "</td>";
                                } elseif ($columna == 'JUSTIFICACIÓN' && !empty($dato)) {
                                    echo "<td><a href='/permisos/archivos/" . htmlspecialchars($dato) . "' download>Archivo</a></td>";
                                } else {
                                    echo "<td>" . htmlspecialchars($dato) . "</td>";
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


<script>
window.onload = function() {
    var tablas = document.querySelectorAll("table");
    
    tablas.forEach(function(tabla) {
        var width = tabla.offsetWidth;
        var containerWidth = tabla.parentElement.offsetWidth;

        // Si la tabla es más ancha que el contenedor, aplicar la escala
        if (width > containerWidth) {
            tabla.style.transform = "scale(0.9)";
            tabla.style.transformOrigin = "top left";
        }
    });
};

// Opciones por tipo de permiso
const opcionesMotivo = {
    personal: [
        { value: "todos", text: "ASUNTOS PARTICULARES" },
        { value: "particular", text: "ASUNTOS PARTICULARES" },
        { value: "enfermedad", text: "ENFERMEDAD" },
        { value: "calamidad", text: "CALAMIDAD DOMÉSTICA" },
        { value: "vacaciones", text: "PERMISO VACACIONES" },
    ],
};

// Función para actualizar las opciones de motivo
function actualizarMotivo() {
    const tipoPermiso = document.getElementById('tipo_permiso').value;
    const motivo = document.getElementById('motivo_permiso');
    if (tipoPermiso=='comision'){
        motivo.innerHTML = '<option value="">COMISIÓN</option>';
    }if(tipoPermiso=='personal'||tipoPermiso=='0'){
        motivo.innerHTML = '<option value="">TODOS</option>';
    }

    // Agrega las nuevas opciones basadas en el tipo de permiso
    if (opcionesMotivo[tipoPermiso]) {
        opcionesMotivo[tipoPermiso].forEach(opcion => {
            const opt = document.createElement('option');
            opt.value = opcion.value;
            opt.textContent = opcion.text;
            motivo.appendChild(opt);    
        });
    }

    motivo.value = '';
}

document.getElementById('tipo_permiso').addEventListener('change', actualizarMotivo);
</script>
