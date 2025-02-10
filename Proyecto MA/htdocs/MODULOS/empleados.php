<?php
session_start();
require '../PHP/auth.php';

// Verificar rol de usuario
if (!isset($_SESSION['role']) || $_SESSION['role'] !== '0') {
    header("Location: ../modular.php");
    exit();
}

require '../PHP/conexion.php';

// Consulta para obtener los datos de la tabla
$query = "SELECT 
    a.id AS ID, 
    a.nombres AS EMPLEADO, 
    a.numero_identificacion AS CÉDULA, 
    a.modalidad_laboral AS MODALIDAD, 
    a.codigo AS CÓDIGO, 
    c.nombre_puesto AS PUESTO, 
    z.nombre AS OFICINA, 
    c.id AS id_puesto, 
    z.id AS id_oficina 
FROM empleados a 
LEFT JOIN oficinatecnica z ON a.oficina_id = z.id 
LEFT JOIN puestos c ON a.puesto_id = c.id
ORDER BY EMPLEADO ASC";
$resultado = $conn->query($query);

// Consulta para obtener oficinas y puestos
$oficinas_query = "SELECT id, nombre FROM oficinatecnica ORDER BY nombre";
$oficinas_result = $conn->query($oficinas_query);

$puestos_query = "SELECT id, nombre_puesto FROM puestos ORDER BY nombre_puesto";
$puestos_result = $conn->query($puestos_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Datos de la Tabla</title>
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            margin-top: 5%;
            display: inline-block;
            width: auto;
            max-width: 800px;
            background-color: white;
            display: inline-block;
            width: 50%; /* Reducir el ancho al 50% del contenedor padre */
            height: auto; /* Altura automática según el contenido */
            max-height: 80vh; /* Limitar la altura máxima al 80% del viewport */
            overflow-y: auto; /* Habilitar scroll si el contenido es muy largo */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            
        }
        .btn-actualizar {
            width: 100%;
            padding: 0.5em;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn-actualizar:hover {
            background-color: #45a049;
        }
    </style>
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
                        $columnas_a_excluir = ['id_puesto', 'id_oficina', 'ID'];
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
                        echo "<td>" . htmlspecialchars($fila['EMPLEADO']) . "</td>";
                        echo "<td>" . htmlspecialchars($fila['CÉDULA']) . "</td>";
                        echo "<td>" . htmlspecialchars($fila['MODALIDAD']) . "</td>";
                        echo "<td>" . htmlspecialchars($fila['CÓDIGO']) . "</td>";
                        echo "<td>" . htmlspecialchars($fila['PUESTO']) . "</td>";
                        echo "<td>" . htmlspecialchars($fila['OFICINA']) . "</td>";
                        echo "<td>
                                <button onclick=\"mostrarModal('editar', '{$fila['ID']}', '{$fila['CÉDULA']}', '{$fila['EMPLEADO']}', '{$fila['MODALIDAD']}', '{$fila['CÓDIGO']}', '{$fila['id_puesto']}', '{$fila['id_oficina']}')\">Editar</button>
                                <button style='background-color:red' onclick=\"mostrarModal('eliminar', '{$fila['ID']}', '{$fila['CÉDULA']}', '{$fila['EMPLEADO']}')\">Eliminar</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay registros en la tabla</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>

    <!-- Modales -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <p id="modal-mensaje"></p>
            <button id="modal-confirmar" class="confirm">Confirmar</button>
            <button onclick="cerrarModal()" class="cancel">Cancelar</button>
        </div>
    </div>

    <div id="editarModal" class="modal">
        <div class="modal-content">
            <form method="POST" onsubmit="editarRegistro(event)">
                <input type="hidden" id="usuarioId" name="id">
                <label for="cedula">Cédula:</label>
                <input type="text" id="cedula" name="cedula" required><br><br>
                <label for="nombres">Nombres:</label>
                <input type="text" id="nombres" name="nombres" required><br><br>
                <label for="modalidad">Modalidad Laboral:</label>
                <select id="modalidad" name="modalidad" required>
                    <option value="1">NOMBRAMIENTO</option>
                    <option value="4">NOMBRAMIENTO PROVISIONAL</option>
                    <option value="2">CONTRATO INDEFINIDO</option>
                    <option value="3">CONTRATOS OCASIONALES</option>
                </select><br><br>
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
                <label for="codigo">Código:</label>
                <input type="number" id="codigo" name="codigo" required><br><br>
                <input class="confirm" type="submit" value="Actualizar">
                <button type="button" onclick="cerrarModal()" class="cancel">Cancelar</button>
            </form>
        </div>
    </div>

    <div id="agregarModal" class="modal">
        <div class="modal-content">
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
                <label for="codigo">Código:</label>
                <input type="number" id="codigo" name="codigo" required><br><br>
                <button type="submit" class="confirm">Añadir</button>
                <button type="button" onclick="cerrarModal()" class="cancel">Cancelar</button>
            </form>
        </div>
    </div>

    <footer id="footer">
        Solicitar ayuda
    </footer>
</body>
</html>



<script>
// Funciones para mostrar/cerrar modales
function mostrarModal(accion, id, cedula, nombres, modalidad = "", codigo = "", puesto = "", oficina = "") {
    const mensaje = accion === 'editar' 
        ? `¿Editar el registro de ${nombres}?` 
        : `¿Eliminar el registro de ${nombres}?`;
    document.getElementById('modal-mensaje').innerText = mensaje;

    document.getElementById('modal-confirmar').onclick = () => {
        if (accion === 'eliminar') {
            eliminarRegistro(id);
        } else if (accion === 'editar') {
            mostrarModalEdicion(id, cedula, nombres, modalidad, codigo, puesto, oficina);
        }
    };
    document.getElementById('modal').style.display = 'block';
}

function mostrarModalEdicion(id, cedula, nombres, modalidad, codigo, puesto, oficina) {
    document.getElementById('usuarioId').value = id;
    document.getElementById('cedula').value = cedula;
    document.getElementById('nombres').value = nombres;
    if (modalidad == "NOMBRAMIENTO") {
        document.getElementById('modalidad').value = 1;
    }if (modalidad == "CONTRATO INDEFINIDO") {
        document.getElementById('modalidad').value = 2;
    }if (modalidad == "CONTRATOS OCASIONALES") {
        document.getElementById('modalidad').value = 3;
    }if (modalidad == "NOMBRAMIENTO PROVISIONAL") {
        document.getElementById('modalidad').value = 4;
    }
    document.getElementById('codigo').value = codigo;
    document.getElementById('puesto').value = puesto;
    document.getElementById('oficina').value = oficina;

    document.getElementById('modal').style.display = 'none';
    document.getElementById('editarModal').style.display = 'block';
}

function mostrarModalAgregar() {
    document.getElementById('agregarModal').style.display = 'block';
}

function cerrarModal() {
    document.getElementById('modal').style.display = 'none';
    document.getElementById('editarModal').style.display = 'none';
    document.getElementById('agregarModal').style.display = 'none';
}

// Funciones para editar y eliminar registros
function editarRegistro(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    fetch("empleados/editar.php", {
        method: "POST",
        body: new URLSearchParams(formData),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Registro actualizado correctamente.");
            window.location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error al procesar la solicitud.");
    });
}

function eliminarRegistro(id) {
    fetch(`empleados/eliminar.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`fila-${id}`).remove();
                alert("Registro eliminado correctamente.");
                cerrarModal();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al procesar la solicitud.");
        });
}

// Función para agregar un nuevo registro
function agregarRegistro(event) {
    event.preventDefault();

    const cedula = document.getElementById("cedulaa").value;

    // Validar la cédula
    if (!validarCedula(cedula)) {
        alert("La cédula es inválida. Asegúrese de ingresar una cédula válida.");
        return;
    }

    const formData = new FormData(event.target);

    fetch("empleados/agregar.php", {
        method: "POST",
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Registro agregado correctamente.");
            window.location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error al procesar la solicitud.");
    });
}

function validarCedula(cedula) {
    // La cédula debe tener 10 dígitos
    if (cedula.length !== 10 || isNaN(cedula)) {
        return false;
    }

    // Extraer los primeros dos dígitos (provincia)
    const provincia = parseInt(cedula.substring(0, 2));
    if (provincia < 1 || provincia > 24) {
        return false; // La provincia debe estar entre 1 y 24
    }

    // Algoritmo de validación de cédula ecuatoriana
    const digitos = cedula.split("").map(Number);
    const ultimoDigito = digitos.pop(); // Último dígito (dígito verificador)

    let suma = 0;
    digitos.forEach((num, index) => {
        if (index % 2 === 0) {
            num *= 2;
            if (num > 9) num -= 9;
        }
        suma += num;
    });

    const verificador = (suma % 10 === 0) ? 0 : 10 - (suma % 10);
    return ultimoDigito === verificador;
}

</script>








