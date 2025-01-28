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

// Prepara la consulta con sentencia preparada
$stmt = $conn->prepare("SELECT a.id as ID, fecha_solicitud as 'FECHA SOLICITUD', c.nombres as EMPLEADO, tipo_permiso as 'TIPO PERMISO', fecha_desde as DESDE, fecha_hasta as HASTA, tiempo_total as 'TIEMPO TOTAL', 
                 motivo as MOTIVO, razon_comision as RAZON, observaciones as OBSERVACIONES, archivo_justificacion as JUSTIFICACIÓN
          FROM permisos AS a 
          left join usuarios as zz on a.user_id=zz.id
          LEFT JOIN empleados as c ON zz.cedula=c.numero_identificacion
          left join oficinatecnica as o on o.id=c.oficina_id
          LEFT JOIN empleados as z ON z.oficina_id=o.id
          WHERE z.numero_identificacion = ? AND estado = 'Pendiente'");

// Vincula el parámetro
$stmt->bind_param("i", $_SESSION['cedula']);

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

        /* Botones con estilo diferente para aceptar y rechazar */
        .confirm {
            background-color: green;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .cancel {
            background-color: red;
            color: white;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .confirm:hover {
            background-color: darkgreen;
        }

        .cancel:hover {
            background-color: darkred;
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
                                <button onclick='showConfirmationModal(\"Rechazar\", \"" . $fila['ID'] . "\")'>Rechazar</button>
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
    function showConfirmationModal(action, id) {
    selectedAction = action;
    selectedId = id;

    console.log("Acción seleccionada: ", selectedAction);
    console.log("ID seleccionado: ", selectedId);

    const modalMessage = action === 'Aprobar' 
        ? '¿Estás seguro de que deseas aprobar este permiso?' 
        : '¿Estás seguro de que deseas rechazar este permiso?';
    
    document.getElementById('confirmationMessage').innerText = modalMessage;
    document.getElementById('confirmationModal').style.display = 'block';
}

    // Confirmar la acción y enviar la solicitud al servidor sin redirección
    document.getElementById('confirmButton').onclick = function() {
        const formData = new FormData();
        formData.append('id', selectedId);
        formData.append('accion', selectedAction);

        // Usamos AJAX para enviar la solicitud sin recargar la página
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'revisor_permisos/actualizar_permiso.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                // Parsear la respuesta JSON
                const response = JSON.parse(xhr.responseText);

                if (response.status === 'success') {
                    alert("Correo Enviado Exitosamente");
                    // Si es aprobado o rechazado, elimina la fila
                    deleteRow(selectedId);
                    
                    // Cierra el modal
                    document.getElementById('confirmationModal').style.display = 'none';
                } else {
                    // Si hubo un error, muestra un mensaje
                    alert(response.message);
                }
            }
        };
        xhr.send(formData); // Enviar los datos sin recargar la página
    };

    // Cancelar la acción
    document.getElementById('cancelButton').onclick = function() {
        document.getElementById('confirmationModal').style.display = 'none';
    };

    // Eliminar la fila de la tabla
    function deleteRow(id) {
        var row = document.getElementById('row_' + id);
        if (row) {
            row.remove(); // Elimina la fila de la tabla
        }
    }
</script>
</body>
</html>
