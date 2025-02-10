<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();
require '../PHP/auth.php';

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
$query = "SELECT a.id as ID, fecha_solicitud as 'FECHA SOLICITUD', b.nombres as EMPLEADO, tipo_permiso as 'TIPO PERMISO', motivo as MOTIVO,razon_comision as RAZON, 
                 fecha_desde as DESDE, fecha_hasta as HASTA, tiempo_total as 'TIEMPO TOTAL',reincorporacion as RETORNA,  
                  observaciones as OBSERVACIONES,observaciones_jefe as 'OBSERVACIONES JEFE', archivo_justificacion as JUSTIFICACIÓN, 
                 c.nombre as OFICINA,(select nombres from empleados as z where z.id=a.responsable) as RESPONSABLE, estado as ESTADO
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
$query .=" ORDER BY a.id desc";
$resultado = $conn->query($query);


// Calcular totales solo para permisos que no sean de comisión y estén aprobados
$total_permisos = 0;
$horas_totales = 0;
$minutos_totales = 0;
$dias_totales = 0;

if ($empleado_id > 0 && $resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        // Verificar si el permiso no es de comisión y está aprobado
        if ($fila['MOTIVO'] !== 'comision' && $fila['ESTADO'] === 'Aprobado' && $fila['MOTIVO'] != 'enfermedad' && $fila['MOTIVO'] != 'calamidad') {
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


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Datos de la Tabla</title>

</head>
<style>
/* Estilos para el modal */
.modal {
    display: none; /* Oculto por defecto */
    position: fixed; /* Posición fija */
    z-index: 1000; /* Asegura que esté por encima de otros elementos */
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto; /* Permite el scroll si es necesario */
    background-color: rgba(0, 0, 0, 0.5); /* Fondo oscuro semi-transparente */
}

.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* Centrado vertical */
    padding: 20px;
    border: 1px solid #888;
    width: 80%; /* Ancho del contenido */
    max-width: 500px; /* Ancho máximo */
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
}

.close-modal {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close-modal:hover,
.close-modal:focus {
    color: black;
    text-decoration: none;
}
</style>
<body>
    <?php include '../PHP/header.php'; ?>

    <main>

        <h1>Registros en la tabla</h1>

        <form method="GET" action="">
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
                                echo "<th style='text-align: center;'>" . htmlspecialchars($columna) . "</th>";

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

                                    echo "<td class='" . $estado_clase . "' style='padding: 8px;'>" . htmlspecialchars($dato);
                                    if ($fila['ESTADO'] === 'Pendiente') {
                                        echo "<br>
                                                <button class='btn-eliminar' data-permiso-id='" . htmlspecialchars($fila['ID']) . "'>Eliminar</button>
                                            ";
                                    }
                                    echo "</td>";
                                } elseif ($columna == 'JUSTIFICACIÓN' && !empty($dato)) {
                                    echo "<td style='padding: 8px;'><a href='/permisos/archivos/" . htmlspecialchars($dato) . "' download>Archivo</a></td>";
                                } elseif ($columna == 'JUSTIFICACIÓN') {
                                if ($columna == 'JUSTIFICACIÓN' && $fila['MOTIVO'] == 'enfermedad' && empty($dato)) {
                                    echo "<td style='padding: 8px;'>
                                            <button class='btn-subir-archivo' data-permiso-id='" . htmlspecialchars($fila['ID']) . "'>Subir archivo</button>
                                        </td>";
                                } elseif (!empty($dato)) {
                                    echo "<td style='padding: 8px;'><a href='/permisos/archivos/" . htmlspecialchars($dato) . "' download>Archivo</a></td>";
                                } else {
                                    echo "<td style='padding: 8px;'>No aplica</td>";
                                }
                            }else {
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

  
<!-- Modal para subir archivos -->
<div id="modal-subir-archivo" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Subir archivo justificativo</h2>
        <form id="form-subir-archivo" enctype="multipart/form-data">
            <input type="hidden" name="permiso_id" id="permiso-id">
            <input type="file" name="archivo_justificacion" accept=".pdf,.jpg,.jpeg,.png" required>
            <button type="submit">Subir</button>
        </form>
    </div>
</div>
</body>
</html>

<script>
// Abrir modal al hacer clic en el botón "Subir archivo"
document.querySelectorAll('.btn-subir-archivo').forEach(button => {
    button.addEventListener('click', function () {
        const permisoId = this.getAttribute('data-permiso-id');
        document.getElementById('permiso-id').value = permisoId;
        document.getElementById('modal-subir-archivo').style.display = 'flex';
    });
});

// Cerrar modal al hacer clic en la X
document.querySelector('.close-modal').addEventListener('click', function () {
    document.getElementById('modal-subir-archivo').style.display = 'none';
});

// Cerrar modal al hacer clic fuera del contenido
window.addEventListener('click', function (event) {
    const modal = document.getElementById('modal-subir-archivo');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Enviar formulario con AJAX
document.getElementById('form-subir-archivo').addEventListener('submit', function (event) {
    event.preventDefault(); // Evitar envío tradicional

    const formData = new FormData(this);

    fetch('visor_permisos/subir_archivo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        if (result.includes("Éxito")) {
            alert("Archivo subido correctamente.");
            window.location.reload(); // Recargar la página para actualizar la tabla
        } else {
            alert("Error al subir el archivo: " + result);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Hubo un error al subir el archivo.");
    });
});

document.querySelectorAll('.btn-eliminar').forEach(button => {
    button.addEventListener('click', function () {
        event.preventDefault(); // Evitar envío tradicional
        const permisoId = this.getAttribute('data-permiso-id');

        if (confirm("¿Estás seguro de que deseas eliminar este permiso pendiente?")) {
            fetch(`visor_permisos/eliminar.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: permisoId }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Permiso eliminado correctamente.");
                    window.location.reload();
                    // Eliminar la fila de la tabla
                } else {
                    alert("Error al eliminar el permiso: " + data.message);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Hubo un error al eliminar el permiso.");
            });
        }
    });
});
</script>
