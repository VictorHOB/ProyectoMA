<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();

require '../PHP/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== '0') {
    header("Location: ../modular.php");
    exit();
}
// Conexión a la base de datos
require '../PHP/conexion.php';

// Consulta para obtener los datos de la tabla
$query = "SELECT a.id, a.cedula, nombres, correo, rol FROM usuarios as a LEFT JOIN empleados as b ON a.cedula=b.numero_identificacion WHERE rol >'0' OR rol IS NULL";
$resultado = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Datos de la Tabla</title>
    <script>
    // Mostrar el modal de confirmación de la acción
    function mostrarModal(accion, id, correo, rol, nombre) {
    let mensaje = accion === 'editar' 
        ? `¿Estás seguro de que deseas editar este registro?\n ${nombre}` 
        : `¿Estás seguro de que deseas eliminar este registro?\n ${nombre}`;

    // Mostrar mensaje con la información actual de la fila
    document.getElementById('modal-mensaje').innerText = mensaje;

    // Configurar acción en el botón de confirmar
    document.getElementById('modal-confirmar').onclick = function() {
        if (accion === 'eliminar') {
            eliminarRegistro(id);
        } else if (accion === 'editar') {
            mostrarModalEdicion(id, correo, rol, nombre);
        }
    };
    document.getElementById('modal').style.display = 'block';
    }

    //// Mostrar el modal de edición con los datos
function mostrarModalEdicion(id, correo, rol, nombre) {
    // Llenar los campos del modal con los valores actuales
    document.getElementById('usuarioId').value = id;
    document.getElementById('correo').value = correo;
    document.getElementById('rol').value = rol;

    // Llenar los datos no editables en el modal de edición
    document.getElementById('nombreModal').innerText = `Nombre: ${nombre}`;

    // Mostrar el modal de edición y cerrar el modal de confirmación
    document.getElementById('modal').style.display = 'none';  // Cerrar el modal de confirmación
    document.getElementById('editarModal').style.display = 'block';  // Abrir el modal de edición
}

function mostrarModalAgregar() {
    document.getElementById('agregarModal').style.display = 'block';
}


    // Función para cerrar el modal
    function cerrarModal() {
        document.getElementById('modal').style.display = 'none';
        document.getElementById('editarModal').style.display = 'none';
        document.getElementById('agregarModal').style.display = 'none';
    }

    // Función para eliminar el registro usando AJAX
    function eliminarRegistro(id) {
        const xhr = new XMLHttpRequest();
        xhr.open("GET", `gestor/eliminar.php?id=${id}`, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                const fila = document.getElementById(`fila-${id}`);
                fila.remove();
                cerrarModal();
                alert(xhr.responseText);
            } else {
                alert("Error al eliminar el registro.");
            }
        };
        xhr.send();
    }


// Función para editar el registro usando AJAX
// Función para editar el registro usando AJAX
function editarRegistro(event) {
    // Prevenir que el formulario se envíe de forma tradicional
    event.preventDefault();

    // Obtener los datos del formulario
    const id = document.getElementById('usuarioId').value;
    const correo = document.getElementById('correo').value;
    const rol = document.getElementById('rol').value;

    // Crear la solicitud AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "gestor/editar.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Definir el callback de la respuesta
    xhr.onload = function() {
        // Parsear la respuesta JSON
        const respuesta = JSON.parse(xhr.responseText);

        // Verificar si la respuesta fue exitosa
        if (respuesta.success) {
            // Obtener la fila a actualizar (por ID de la fila)
            const fila = document.getElementById(`fila-${respuesta.id}`);

            // Actualizar los datos en las celdas correspondientes de la fila
            fila.children[3].textContent = respuesta.correo;  // Columna correo
            fila.children[4].textContent = respuesta.rol == 1 ? "1" : "2";  // Columna rol

            // Cerrar los modales (confirmación y edición)
            cerrarModal();

            // Alerta de éxito
            alert("Datos actualizados correctamente.");
        } else {
            // Mostrar mensaje de error si hubo un problema
            alert("Error al actualizar los datos: " + respuesta.message);
        }
    };

    // Manejar errores de solicitud AJAX
    xhr.onerror = function() {
        alert("Error en la solicitud.");
    };

    // Enviar la solicitud con los datos
    xhr.send(`id=${id}&correo=${correo}&rol=${rol}`);
}

function validarCedula(cedula) {
    // La cédula debe tener 10 dígitos
    if (cedula.length !== 10) {
        return false;
    }

    // Validación del último dígito de la cédula
    const provincia = parseInt(cedula.substring(0, 2)); // Primeros 2 dígitos
    if (provincia < 1 || provincia > 24) {
        return false; // La provincia debe estar entre 1 y 24
    }

    const digitos = cedula.split("").map(Number); // Convertir la cédula a un arreglo de dígitos
    const suma = digitos.slice(0, 9).reduce((acum, num, index) => {
        // Calcula la suma según la fórmula estándar de la cédula ecuatoriana
        if (index % 2 === 0) {
            num *= 2;
            if (num > 9) num -= 9; // Si el número es mayor que 9, restar 9
        }
        return acum + num;
    }, 0);

    const verificador = (suma % 10 === 0) ? 0 : 10 - (suma % 10); // Cálculo del último dígito
    return digitos[9] === verificador; // El último dígito debe coincidir con el cálculo
}


// Función para agregar el registro usando AJAX
function agregarRegistro(event) {
    // Prevenir que el formulario se envíe de forma tradicional
    event.preventDefault();

    // Obtener el valor de la cédula
    const cedula = document.getElementById("cedula").value;

    // Validar la cédula
    if (!validarCedula(cedula)) {
        alert("La cédula es inválida.");
        return; // Detener el proceso si la cédula no es válida
    }

    // Continuar con el envío del formulario
    const formData = new FormData(event.target);

    fetch("gestor/agregar.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((respuesta) => {
            if (respuesta.success) {
                const nombreEmpleado = respuesta.nombre_empleado; // Nombre del empleado
                const idnuevo = respuesta.id_nuevo; 

                // Crear la nueva fila con los mismos atributos y funciones
                const nuevaFila = `
                    <tr id="fila-${idnuevo}">
                        <td>${idnuevo}</td>
                        <td>${formData.get("cedula")}</td>
                        <td>${nombreEmpleado}</td>
                        <td>${formData.get("correo")}</td>
                        <td>1</td>
                        <td>
                            <button onclick="mostrarModal('editar', '${idnuevo}', '${formData.get("correo")}', '1', '${nombreEmpleado}')">Editar</button>
                            <button onclick="mostrarModal('eliminar', '${idnuevo}', '${formData.get("correo")}', '1', '${nombreEmpleado}')">Eliminar</button>
                        </td>
                    </tr>
                `;

                // Agregar la nueva fila a la tabla
                document.querySelector("table tbody").insertAdjacentHTML("beforeend", nuevaFila);

                // Cerrar el modal
                cerrarModal();
                alert("Nuevo registro agregado correctamente.");
            } else {
                alert("Error al agregar el registro: " + respuesta.message);
            }
        })
        .catch((error) => {
            console.error("Error al procesar la solicitud:", error);
            alert("Error al procesar la solicitud. Inténtalo más tarde.");
        });
}

</script>
</head>
<body>
    <?php include '../PHP/header.php'; ?>

    <main>
        <h1>Registros en la tabla</h1>
                <button onclick="mostrarModalAgregar()" style="width:200px">Añadir Nuevo Empleado</button><br>
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
                        echo "<th style='padding: 8px; text-align: left;'>Acciones</th>";
                        $resultado->data_seek(0);
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($resultado->num_rows > 0) {
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<tr id='fila-{$fila['id']}'>";
                            foreach ($fila as $columna => $dato) {  
                                echo "<td style='padding: 8px;'>" . htmlspecialchars($dato) . "</td>";
                            }
                            echo "<td style='padding: 8px; text-align: center;'>
                                    <button onclick=\"mostrarModal('editar', '{$fila['id']}', '{$fila['correo']}', '{$fila['rol']}', '{$fila['nombres']}')\">Editar</button>
                                    <button onclick=\"mostrarModal('eliminar', '{$fila['id']}', '{$fila['correo']}', '{$fila['rol']}', '{$fila['nombres']}')\">Eliminar</button>
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

    <!-- Modal de confirmación -->
    <div id="modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); text-align: center;">
        <div style="background-color: white; padding: 20px; margin-top: 150px; display: inline-block; width: 40%;">
            <p id="modal-mensaje"></p>
            <button id="modal-confirmar">Confirmar</button>
            <button onclick="cerrarModal()">Cancelar</button>
        </div>
    </div>

    <!-- Modal de edición -->
<div id="editarModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); text-align: center;">
    <div style="background-color: white; padding: 20px; margin-top: 150px; display: inline-block; width: 40%;">
        <form method="POST" action="gestor/editar.php" onsubmit="editarRegistro(event)">
            <input type="hidden" id="usuarioId" name="id">
            
            <p id="nombreModal"></p> <!-- Mostrar el nombre de la fila (no editable) -->

            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" style="width:40%;"><br><br>

            <label for="rol">Rol:</label>
            <select id="rol" name="rol">
                <option value="1">Usuario</option>
                <option value="2">Responsable</option>
            </select><br><br>

            <input class="btn-actualizar" type="submit" value="Actualizar">
            <style>
                .btn-actualizar {
                    width: 100%;
                    padding: 0.5em;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                input:hover {
                    background-color: #45a049;
                }
            </style>
            <button  type="button" onclick="cerrarModal()">Cancelar</button>
        </form>
    </div>
</div>


    <!-- Modal de agregar -->
<div id="agregarModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); text-align: center;">
    <div style="background-color: white; padding: 20px; margin-top: 150px; display: inline-block; width: 40%;">
        <form method="POST" action="gestor/agregar.php" onsubmit="agregarRegistro(event)">
            <input type="hidden" id="usuarioId" name="id">
            
            <p id="nombreModal"></p> <!-- Mostrar el nombre de la fila (no editable) -->

            <label for="cedula">Cédula:</label>
            <input type="text" id="cedula" name="cedula" required><br><br>

            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo"><br><br>

            <input class="btn-actualizar" type="submit" value="Actualizar">
            <style>
                .btn-actualizar {
                    width: 100%;
                    padding: 0.5em;
                    background-color: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                input:hover {
                    background-color: #45a049;
                }
            </style>
            <button  type="button" onclick="cerrarModal()">Cancelar</button>
        </form>
    </div>
</div>


    <footer id="footer">
        Solicitar ayuda
    </footer>
</body>
</html>
