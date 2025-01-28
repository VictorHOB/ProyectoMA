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
$query = "SELECT 

a.id AS ID, a.numero_identificacion AS CÉDULA, a.nombres AS EMPLEADO, a.modalidad_laboral AS MODALIDAD, a.codigo AS CÓDIGO,nombre_puesto as PUESTO, z.nombre AS OFICINA, c.id as id_puesto,z.id as id_oficina 

 FROM empleados a LEFT JOIN oficinatecnica z ON a.oficina_id = z.id left join puestos as c on a.puesto_id=c.id;";
$resultado = $conn->query($query);
// Consulta para obtener todos los empleados
$oficinas_query = "SELECT id, nombre FROM oficinatecnica order by nombre;";
$oficinas_result = $conn->query($oficinas_query);

$puestos_query = "SELECT id, nombre_puesto FROM puestos order by nombre_puesto;";
$puestos_result = $conn->query($puestos_query);
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
function mostrarModal(accion, id, numeroIdentificacion, nombres, modalidadLaboral = "", codigo = "", puesto = "", oficina = "") {
    let mensaje = accion === 'editar' 
        ? `¿Estás seguro de que deseas editar el registro de ${nombres} (ID: ${id})?` 
        : `¿Estás seguro de que deseas eliminar el registro de ${nombres} (ID: ${id})?`;

    document.getElementById('modal-mensaje').innerText = mensaje;

    document.getElementById('modal-confirmar').onclick = function() {
        if (accion === 'eliminar') {
            eliminarRegistro(id);
        } else if (accion === 'editar') {
            mostrarModalEdicion(id, numeroIdentificacion, nombres, modalidadLaboral, codigo, puesto,oficina);
        }
    };
    document.getElementById('modal').style.display = 'block';
}

//EDICION
function mostrarModalEdicion(id, numeroIdentificacion, nombres, modalidadLaboral, codigo, puesto, oficina) {


    document.getElementById('usuarioId').value = id;
    document.getElementById('cedula').value = numeroIdentificacion;
    document.getElementById('nombres').value = nombres;
    if (modalidadLaboral == "NOMBRAMIENTO") {
        document.getElementById('modalidad').value = 1;
    }
    if (modalidadLaboral == "CONTRATO INDEFINIDO") {
        document.getElementById('modalidad').value = 2;
    }
    if (modalidadLaboral == "CONTRATOS OCASIONALES") {
        document.getElementById('modalidad').value = 3;
    }
    document.getElementById('codigo').value = codigo;
    document.getElementById('puesto').value = puesto;
    document.getElementById('oficina').value = oficina; 

    // Cierra el modal de confirmación
    document.getElementById('modal').style.display = 'none';
    // Abre el modal de edición
    document.getElementById('editarModal').style.display = 'block';
}

function mostrarModalAgregar() {
    document.getElementById('agregarModal').style.display = 'block';
}

// Función para cerrar el modal
    function cerrarModal() {
    document.getElementById('editarModal').style.display = 'none';
    document.getElementById('modal').style.display = 'none';
    document.getElementById('agregarModal').style.display = 'none';

    // Limpia los valores del formulario de edición
    document.getElementById('usuarioId').value = "";
    document.getElementById('cedula').value = "";
    document.getElementById('nombres').value = "";
    document.getElementById('modalidad').value = "";
    document.getElementById('codigo').value = "";
    document.getElementById('oficina').value = "";
    document.getElementById('puesto').value = "";
}

function editarRegistro(event) {
    event.preventDefault();
console.log("Formulario enviado para actualizar");
    event.preventDefault();

    // Obtener los valores de los campos del formulario de edición
    const id = document.getElementById('usuarioId').value;
    const numeroIdentificacion = document.getElementById('cedula').value;
    const nombres = document.getElementById('nombres').value;
    const modalidad = document.getElementById('modalidad').value;
    const codigo = document.getElementById('codigo').value;
    const puesto = document.getElementById('puesto').value;
    const oficina = document.getElementById('oficina').value; 

    

    const xhr = new XMLHttpRequest();
    
    xhr.open("POST", "empleados/editar.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
    if (xhr.status === 200) {
        const respuesta = JSON.parse(xhr.responseText);
        if (respuesta.success) {
            const fila = document.getElementById(`fila-${respuesta.id}`);
            fila.children[1].textContent = respuesta.numero_identificacion;
            fila.children[2].textContent = respuesta.nombres;
            fila.children[3].textContent = respuesta.modalidad_laboral;
            fila.children[4].textContent = respuesta.codigo;
             fila.children[5].textContent = respuesta.puesto;
            fila.children[6].textContent = respuesta.oficina;

            cerrarModal();
            alert("Datos actualizados correctamente.");
        } else {
            alert("Error al actualizar los datos: " + respuesta.message);
        }
    } else {
        alert("Error al procesar la solicitud.");
    }
};

    xhr.send(`id=${id}&numero_identificacion=${numeroIdentificacion}&nombres=${nombres}&modalidad=${modalidad}&codigo=${codigo}&puesto=${puesto}&oficina=${oficina}`);
}

function eliminarRegistro(id) {

    fetch(`empleados/eliminar.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fila = document.getElementById(`fila-${id}`);
                if (fila) fila.remove();
                cerrarModal();
                alert("Registro eliminado correctamente.");
            } else {
                alert(`Error al eliminar el registro: ${data.message}`);
            }
        })
        .catch(error => {
            console.error("Error al eliminar el registro:", error);
            alert("Error de conexión. Inténtalo de nuevo más tarde.");
        });
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

function agregarRegistro(event) {
     event.preventDefault(); // Evita el envío tradicional del formulario

    // Obtener el valor de la cédula
    const cedula = document.getElementById("cedulaa").value;
    // Validar la cédula
    if (!validarCedula(cedula)) {
        alert("La cédula es inválida.");
        return; // Detener el proceso si la cédula no es válida
    }

    // Continuar con el envío del formulario
    const formData = new FormData(event.target);

    fetch("empleados/agregar.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((respuesta) => {
            if (respuesta.success) {
                // Aquí utilizamos los nombres correspondientes en lugar de los IDs
                const nombreOficina = respuesta.nombre_oficina;  // Nombre del responde
                const nombrePuesto = respuesta.nombre_puesto;      // Nombre del puesto
                const idnuevo = respuesta.id_nuevo; 

                // Aquí agregas la fila a la tabla dinámicamente
                const nuevaFila = `
                    <tr id="fila-${idnuevo}">
                        <td>${idnuevo}</td>
                        <td>${formData.get("cedulaa")}</td>
                        <td>${formData.get("nombres")}</td>
                        <td>${formData.get("modalidad")}</td>
                        <td>${formData.get("codigo")}</td>
                        <td>${nombrePuesto}</td>
                        <td>${nombreOficina}</td>  <!-- Aquí se muestra el nombre del puesto -->
                        <td>

                            <button onclick="mostrarModal('editar', '${idnuevo}', '${formData.get("cedula")}', '${formData.get("nombres")}', '${formData.get("modalidad")}', '${formData.get("codigo")}',${formData.get("puesto")}', '${formData.get("oficina")}')">Editar</button>
                            <button onclick="mostrarModal('eliminar', '${idnuevo}', '${formData.get("cedula")}', '${formData.get("nombres")}')">Eliminar</button>
                        </td>
                    </tr>
                `;
                document.querySelector("table tbody").insertAdjacentHTML("beforeend", nuevaFila);
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
                // Obtener una fila para identificar las columnas
                $fila = $resultado->fetch_assoc();
                // Excluir las columnas 'id_puesto' e 'id_responde'
                $columnas_a_excluir = ['id_puesto', 'id_oficina'];
                foreach (array_keys($fila) as $columna) {
                    if (!in_array($columna, $columnas_a_excluir)) {
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
                                echo "<tr id='fila-{$fila['ID']}'>";
                                echo "<td>" . htmlspecialchars($fila['ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['CÉDULA']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['EMPLEADO']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['MODALIDAD']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['CÓDIGO']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['PUESTO']) . "</td>";
                                echo "<td>" . htmlspecialchars($fila['OFICINA']) . "</td>";
                                echo "<td>
                                        <button onclick=\"mostrarModal('editar', '{$fila['ID']}', '{$fila['CÉDULA']}', '{$fila['EMPLEADO']}', '{$fila['MODALIDAD']}', '{$fila['CÓDIGO']}','{$fila['id_puesto']}','{$fila['id_oficina']}')\">Editar</button>
                                        <button onclick=\"mostrarModal('eliminar', '{$fila['ID']}', '{$fila['CÉDULA']}', '{$fila['EMPLEADO']}')\">Eliminar</button>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6'>No hay registros en la tabla</td></tr>";
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
    <div style="background-color: white; padding: 20px; margin-top: 150px; display: inline-block; width: auto; max-width: 800;">
        <form method="POST" action="empleados/editar.php" onsubmit="editarRegistro(event)">
            <input type="hidden" id="usuarioId" name="id">
            
            <p id="nombreModal"></p> <!-- Mostrar el nombre de la fila (no editable) -->

            <label for="cedula">Cedula:</label>
            <input type="text" id="cedula" name="cedula" required><br><br>

            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" required><br><br>

            <label for="modalidad">Modalidad Laboral:</label>
            <select id="modalidad" name="modalidad" required>
                <option value="1">NOMBRAMIENTO</option>
                <option value="4">NOMBRAMIENTO PROVISIONAL</option>
                <option value="2">CONTRATO  INDEFINIDO</option>
                <option value="3">CONTRATOS OCASIONALES</option>
            </select><br><br>

            <label for="codigo">Codigo:</label>
            <input type="number" id="codigo" name="codigo" required><br><br>

            <label for="oficina">Oficina:</label>
            <select id="oficina" name="oficina" required>
                <option value="">Seleccione una oficina</option>
                <?php
                if ($oficinas_result->num_rows > 0) {
                    while ($oficina = $oficinas_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($oficina['id']) . "'>" . htmlspecialchars($oficina['nombre']) . "</option>";
                    }
                }
                ?>
            </select><br><br>

             <label for="puesto">Puesto:</label>
            <select id="puesto" name="puesto" required>
                <option value="">Seleccione un puesto</option>
                <?php
                if ($puestos_result->num_rows > 0) {
                    while ($puesto = $puestos_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($puesto['id']) . "'>" . htmlspecialchars($puesto['nombre_puesto']) . "</option>";
                    }
                }
                ?>
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

<!-- Modal de adición -->
<div id="agregarModal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); text-align: center;">
    <div style="background-color: white; padding: 20px; margin-top: 150px; display: inline-block; width: auto; max-width: 800px;">
        <form id="formAgregar" onsubmit="agregarRegistro(event)">
            <p>Agregar nuevo registro</p>

            <label for="cedulaa">Cédula:</label>
            <input type="text" id="cedulaa" name="cedulaa" required><br><br>
            
            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" required><br><br>
            
            <label for="modalidad">Modalidad Laboral:</label>
            <select id="modalidad" name="modalidad" required>
                <option value="NOMBRAMIENTO">NOMBRAMIENTO</option>
                <option value="NOMBRAMIENTO PROVISIONAL">NOMBRAMIENTO PROVISIONAL</option>
                <option value="CONTRATO INDEFINIDO">CONTRATO INDEFINIDO</option>
                <option value="CONTRATOS OCASIONALES">CONTRATOS OCASIONALES</option>
            </select><br><br>
            
            <label for="codigo">Código:</label>
            <input type="number" id="codigo" name="codigo" required><br><br>

            <label for="oficina">Oficina:</label>
            <select id="oficina" name="oficina" required>
                <option value="">Seleccione una oficina</option>
                <?php
                $oficinas_result->data_seek(0);
                if ($oficinas_result->num_rows > 0) {
                    while ($oficina = $oficinas_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($oficina['id']) . "'>" . htmlspecialchars($oficina['nombre']) . "</option>";
                    }
                }
                ?>
            </select><br><br>

            <label for="canton">Cantón:</label>
            <select id="canton" name="canton" required>
                <!-- Cantones de Chimborazo -->
                <optgroup label="CHIMBORAZO">
                    <option value="1">Riobamba</option>
                    <option value="2">Alausí</option>
                    <option value="3">Chunchi</option>
                    <option value="4">Guano</option>
                </optgroup>

                <!-- Cantones de Pastaza -->
                <optgroup label="PASTAZA">
                    <option value="5">Puyo</option>
                    <option value="6">Mera</option>
                    <option value="7">Arajuno</option>
                </optgroup>

                <!-- Cantones de Cotopaxi -->
                <optgroup label="COTOPAXI">
                    <option value="8">Latacunga</option>
                    <option value="9">Salcedo</option>
                    <option value="10">Pujilí</option>
                </optgroup>

                <!-- Cantones de Tungurahua -->
                <optgroup label="TUNGURAHUA">
                    <option value="11">Ambato</option>
                    <option value="12">Baños</option>
                    <option value="13">Patate</option>
                </optgroup>

                <!-- Cantones de Napo -->
                <optgroup label="NAPO">
                    <option value="14">Tena</option>
                    <option value="15">Archidona</option>
                    <option value="16">El Chaco</option>
                </optgroup>
            </select><br><br>


            <label for="puesto">Puesto:</label>
            <select id="puesto" name="puesto" required>
                <option value="">Seleccione un puesto</option>
                <?php
                $puestos_result->data_seek(0);
                if ($puestos_result->num_rows > 0) {
                    while ($puesto = $puestos_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($puesto['id']) . "'>" . htmlspecialchars($puesto['nombre_puesto']) . "</option>";
                    }
                }
                ?>
            </select><br><br>
            
            <button type="submit">Guardar</button>
            <button type="button" onclick="cerrarModal()">Cancelar</button>
        </form>
    </div>
</div>

    <footer id="footer">
        Solicitar ayuda
    </footer>
</body>
</html>