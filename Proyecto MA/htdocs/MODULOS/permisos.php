<?php
// Inicia la sesión y verifica si el usuario tiene acceso
session_start();
require '../PHP/auth.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>PERMISOS MINISTERIO</title>
</head>
<body>
    <?php include '../PHP/header.php'; ?>

    <main>
        <form action="../PHP/enviar_permiso.php" method="POST" id="permiso" enctype="multipart/form-data">

            <hr><br>
            <div class="tabla">

                <table>
                    <tr class="tablelabel"> <label for="txt1">SOLICITANTE: </label><?php echo $_SESSION['nombre']; ?></tr>
                    <tr>
                        <td class="tablelabel"><label for="tipo_permiso">TIPO DE PERMISO:</label></td>
                        <td class="tablecontent">
                            <select name="tipo_permiso" id="tipo_permiso" required>
                                <option value="">Seleccionar</option>
                                <option value="personal">PERSONAL</option>
                                <option value="comision">COMISIÓN</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="tablelabel" id="tdt"><label for="desde">DESDE:</label></td>
                        <td class="tablecontent" id="tdt"><input type="datetime-local" name="desde" id="desde" required></td>
                    </tr>
                    <tr>
                        <td class="tablelabel" id="tdt"><label for="hasta">HASTA:</label></td>
                        <td class="tablecontent" id="tdt"><input type="datetime-local" name="hasta" id="hasta" required></td>
                    </tr>
                    <tr>
                        <td class="tablelabel" id="tdt"><label for="total">TIEMPO TOTAL:</label></td>
                        <td class="tablecontent" id="tdt"><input type="text" name="tiempo_total" id="tiempo_total" value="0" readonly></td>
                    </tr>
                    <tr>
                        <td class="tablelabel"><label for="motivo">MOTIVO DEL PERMISO:</label></td>
                        <td class="tablecontent">
                            <select name="motivo" id="motivo" required>
                                <option value="">Seleccionar</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="comision_reason" style="display:none;">
                        <td class="tablelabel"><label for="razon_comision">RAZÓN DE LA COMISIÓN:</label></td>
                        <td class="tablecontent">
                            <textarea name="razon_comision" id="razon_comision" style="width: 100%; height: 75px;"></textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <br>
            <label for="observaciones">OBSERVACIONES DEL SERVIDOR</label><br>
            <textarea name="observaciones" id="observaciones" style="width: 100%; height: 75px;"></textarea>
            <br><br>
            <label for="justificacion">ARCHIVO DE JUSTIFICACIÓN</label><br>
            <input type="file" name="justificacion" id="justificacion" accept="application/pdf, image/*">
            <br><br><hr>
            <br><input type="submit" value="SOLICITAR PERMISO">
        </form>

        <!-- Div para mostrar la hora del servidor -->
        <div id="horaServidor" style="margin-top: 20px;">
            <label>Hora del Servidor: </label>
            <span id="hora"></span> <!-- Aquí se mostrará la hora -->
        </div>

        <br><br>
    </main>

    <footer id="footer">
        Solicitar ayuda
    </footer>

    <script>
// Función para obtener la hora del servidor y mostrarla en el elemento HTML correspondiente
function obtenerHoraServidor() {
    fetch('../PHP/hora-servidor.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta de red');
            }
            return response.text();
        })
        .then(data => {
            document.getElementById('hora').innerText = data;
        })
        .catch(error => console.error('Error al obtener la hora del servidor:', error));
}

// Opciones por tipo de permiso
const opcionesMotivo = {
    personal: [
        { value: "particular", text: "ASUNTOS PARTICULARES" },
        { value: "enfermedad", text: "ENFERMEDAD" },
        { value: "calamidad", text: "CALAMIDAD DOMÉSTICA" },
    ],
    comision: [
        { value: "comision", text: "COMISIÓN" }
    ],
};

// Función para actualizar las opciones de motivo
function actualizarMotivo() {
    const tipoPermiso = document.getElementById('tipo_permiso').value;
    const motivo = document.getElementById('motivo');
    motivo.innerHTML = '<option value="">Seleccionar</option>';

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

// Función para calcular el tiempo total
function calcularTiempoTotal() {
    var desde = document.getElementById("desde").value;
    var hasta = document.getElementById("hasta").value;

    if (desde && hasta) {
        var fechaDesde = new Date(desde);
        var fechaHasta = new Date(hasta);
        var tiempoTotal = fechaHasta - fechaDesde;

        if (tiempoTotal < 0) {
            document.getElementById("tiempo_total").value = "Error: fecha 'Hasta' no puede ser anterior a 'Desde'.";
        } else {
            var horas = Math.floor(tiempoTotal / (1000 * 60 * 60));
            var minutos = Math.floor((tiempoTotal % (1000 * 60 * 60)) / (1000 * 60));
            var totalTexto = horas + " horas y " + minutos + " minutos";
            
            document.getElementById("tiempo_total").value = totalTexto;
        }
    }
}

// Función para mostrar u ocultar el campo de "Razón de la Comisión"
function mostrarOcultarRazonComision() {
    var motivo = document.getElementById('motivo').value;
    var comisionReason = document.getElementById('comision_reason');
    var razonComision = document.getElementById('razon_comision');
    
    if (motivo === 'comision') {
        comisionReason.style.display = 'table-row';
        razonComision.setAttribute('required', 'required'); // Campo obligatorio
    } else {
        comisionReason.style.display = 'none';
        razonComision.removeAttribute('required'); // Campo opcional
    }
}

// Configuración de los campos al cargar la página
window.onload = function() {
    
    obtenerHoraServidor();
    setInterval(obtenerHoraServidor, 30000);

    var ahora = new Date();
    ahora.setHours(ahora.getHours() -5); // Añadir 5 horas
    var fechaMinima = ahora.toISOString().slice(0, 16);
    document.getElementById("desde").setAttribute("min", fechaMinima);

    document.getElementById("desde").addEventListener("change", function() {
        var fechaDesde = this.value;
        document.getElementById("hasta").setAttribute("min", fechaDesde);
    });

    document.getElementById("desde").addEventListener("change", calcularTiempoTotal);
    document.getElementById("hasta").addEventListener("change", calcularTiempoTotal);

    document.getElementById('motivo').addEventListener('change', mostrarOcultarRazonComision);
};

document.getElementById("permiso").addEventListener("submit", function(event) {
    event.preventDefault();  // Evitar que el formulario se envíe de forma tradicional

    // Crear un objeto FormData para enviar los datos del formulario
    var formData = new FormData(this);

    // Enviar el formulario con AJAX
    fetch('../PHP/enviar_permiso.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(result => {
        // Mostrar el mensaje de éxito
        if (result.includes("correo enviado")) {
            alert("Correo enviado exitosamente.");
            window.location.href = window.location.href;

        } else {
            alert("Error al enviar el correo. Intenta nuevamente."+ result);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Hubo un error al enviar el formulario.");
    });
});

    </script>
</body>
</html>
