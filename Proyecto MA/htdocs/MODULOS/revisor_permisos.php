<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();

require '../PHP/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '0' && $_SESSION['role'] !== '2') {
    header("Location: ../modular.php");
    exit();
}

$config = json_decode(file_get_contents('/home/vol9_1/infinityfree.com/if0_37560293/htdocs/PHP/config.json'), true);
$jefe_zonal=$config['jefe_zonal'];

// Conexión a la base de datos
require '../PHP/conexion.php';


// Prepara la consulta con sentencia preparada
// Prepara la consulta con sentencia preparada
$query = "SELECT a.id as ID, fecha_solicitud as 'FECHA SOLICITUD', c.nombres as EMPLEADO, 
                 tipo_permiso as 'TIPO PERMISO', fecha_desde as DESDE, fecha_hasta as HASTA, 
                 tiempo_total as 'TIEMPO TOTAL', motivo as MOTIVO, razon_comision as RAZON, 
                  archivo_justificacion as JUSTIFICACIÓN
          FROM permisos AS a 
          LEFT JOIN usuarios as zz ON a.user_id = zz.id
          LEFT JOIN empleados as c ON zz.cedula = c.numero_identificacion
          LEFT JOIN empleados as z ON a.responsable = z.id
          WHERE  estado = 'Pendiente' AND z.numero_identificacion = ? ";


// Ahora preparamos la consulta con la condición corregida
$stmt = $conn->prepare($query);

// Vincula el parámetro
$stmt->bind_param("s", $_SESSION['cedula']);

// Ejecuta la consulta
$stmt->execute();

// Obtiene los resultados
$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Datos de la Tabla</title>
    <style>
        .estado-pendiente { background-color: yellow; }
        .estado-denegado { background-color: red; color: white; }
        .estado-aprobado { background-color: green; color: white; }

        /* Estilo del modal */
        #confirmationModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            text-align: center;
            padding-top: 100px;
        }
        #confirmationModal div {
            background-color: white;
            padding: 20px;
            display: inline-block;
            border-radius: 8px;
        }  
    </style>
</head>
<body>
    <?php include '../PHP/header.php'; ?>

    <main>
        <h1>Registros en la tabla</h1>
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
                        echo "<th>Acciones</th>";
                        $resultado->data_seek(0);
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($resultado->num_rows > 0) {
                    while ($fila = $resultado->fetch_assoc()) {
                        echo "<tr id='row_" . $fila['ID'] . "'>";
                        foreach ($fila as $columna => $dato) {
                            if ($columna == 'JUSTIFICACIÓN' && !empty($dato)) {
                                echo "<td style='padding: 8px;'><a href='/permisos/archivos/" . htmlspecialchars($dato) . "' download>Archivo</a></td>";
                            } else {
                                echo "<td style='padding: 8px;'>" . htmlspecialchars($dato) . "</td>";
                            }
                        }
                        echo "<td style='padding: 8px;'>
                                <button onclick='showConfirmationModal(\"Aprobar\", \"" . $fila['ID'] . "\")'>Aceptar</button>
                                <button style='background-color:red' onclick='showConfirmationModal(\"Rechazar\", \"" . $fila['ID'] . "\")'>Rechazar</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='100%' style='text-align: center;'>No hay registros en la tabla</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <!-- Modal de Confirmación -->
    <div id="confirmationModal">
        <div>
            <h3 id="confirmationMessage"></h3>
            <textarea id="observacion" placeholder="Ingrese una observación (opcional)" rows="5" "></textarea>
            <button id="confirmButton" class="confirm">Confirmar</button>
            <button id="cancelButton" class="cancel">Cancelar</button>
        </div>
    </div>

    <footer id="footer">
        Solicitar ayuda
    </footer>

    <script>
    let selectedAction = '';
    let selectedId = '';

    // Mostrar el modal de confirmación
    // Función para mostrar el modal de confirmación
function showConfirmationModal(action, id) {
    selectedAction = action;
    selectedId = id;

    document.getElementById('confirmationMessage').innerText = 
        action === 'Aprobar' ? '¿Estás seguro de que deseas aprobar este permiso?' 
        : '¿Estás seguro de que deseas rechazar este permiso?';

    document.getElementById('confirmationModal').style.display = 'block';
}

// Confirmar la acción y enviar la solicitud al servidor sin redirección
document.getElementById('confirmButton').addEventListener("click", function() {
    const observacion = document.getElementById('observacion').value.trim();

    fetch('revisor_permisos/actualizar_permiso.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: selectedId,
            accion: selectedAction,
            observacion: observacion
        })
    })
    .then(response => response.text())  // Espera texto plano
    .then(responseText => {
        if (responseText === 'success') {
            alert("Permiso " + selectedAction.toLowerCase() + " exitosamente.");
            deleteRow(selectedId);
            document.getElementById('confirmationModal').style.display = 'none';
        } else {
            alert("Error: " + responseText);
        }
    })
    .catch(error => {
        console.error("Error en la solicitud:", error);
        alert("Hubo un error al procesar la solicitud.");
    });
});


// Cancelar la acción
document.getElementById('cancelButton').addEventListener("click", function() {
    document.getElementById('confirmationModal').style.display = 'none';
});

// Función para eliminar la fila después de aprobar/rechazar
function deleteRow(id) {
    var row = document.getElementById('row_' + id);
    if (row) {
        row.remove();
    }
}

    </script>
</body>
</html>
