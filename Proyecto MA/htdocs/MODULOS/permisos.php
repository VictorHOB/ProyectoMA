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
                    <?php 
                             require '../PHP/conexion.php';
                            $empleado_cedula = $_SESSION['cedula'];
                            // Consulta tiempo usado de vacaciones
                            $query = "SELECT a.id as ID, fecha_solicitud as 'FECHA SOLICITUD', b.nombres as EMPLEADO, tipo_permiso as 'TIPO PERMISO', motivo as MOTIVO,razon_comision as RAZON, 
                                            fecha_desde as DESDE, fecha_hasta as HASTA, tiempo_total as 'TIEMPO TOTAL',reincorporacion as RETORNA,  
                                            observaciones as OBSERVACIONES, archivo_justificacion as JUSTIFICACIÓN, 
                                            c.nombre as OFICINA,(select nombres from empleados as z where z.id=a.responsable) as RESPONSABLE, estado as ESTADO
                                    FROM permisos AS a    
                                    LEFT JOIN usuarios AS z ON a.user_id = z.id
                                    LEFT JOIN empleados AS b ON z.cedula = b.numero_identificacion 
                                    LEFT JOIN oficinatecnica AS c ON b.oficina_id = c.id ";

                            // Crear un array para las condiciones de filtrado
                            $condiciones = [];

                            if ($empleado_cedula > 0) {
                                $condiciones[] = "z.cedula = $empleado_cedula";
                            }
                            // Si hay condiciones, se añaden a la consulta
                            if (count($condiciones) > 0) {
                                $query .= " WHERE " . implode(" AND ", $condiciones);
                            }

                            $resultado = $conn->query($query);

                            $horas_totales = 0;
                            $minutos_totales = 0;
                            $dias_totales = 0;

                            if ( $resultado->num_rows > 0) {
                                while ($fila = $resultado->fetch_assoc()) {
                                    // Verificar si el permiso no es de comisión y está aprobado
                                    if ($fila['TIPO PERMISO'] !== 'comision' && $fila['ESTADO'] === 'Aprobado') {

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
                    <!--TIEMPO DE VACACIONES HASTA EL MOMENTO-->
                    <p>Tiempo Vacaciones Utilizado: <strong><?= $dias ?> días, <?= $horas_restantes ?> horas, <?= $minutos_totales ?> minutos</strong></p>
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
                        <td class="tablelabel"><label for="motivo">MOTIVO DEL PERMISO:</label></td>
                        <td class="tablecontent">
                            <select name="motivo" id="motivo" required>
                                <option value="">Seleccionar</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="tablelabel"><label for="fecha_desde">FECHA DESDE:</label></td>
                        <td class="tablecontent"><input type="date" name="fecha_desde" id="fecha_desde" required></td>
                    </tr>
                    <tr>
                        <td class="tablelabel"><label for="hora_desde">HORA DESDE:</label></td>
                        <td class="tablecontent"><input type="time" name="hora_desde" id="hora_desde" required ></td>
                    </tr>
                    <tr>
                        <td class="tablelabel"><label for="fecha_hasta">FECHA HASTA:</label></td>
                        <td class="tablecontent"><input type="date" name="fecha_hasta" id="fecha_hasta" required></td>
                    </tr>
                    <tr>
                        <td class="tablelabel"><label for="hora_hasta">HORA HASTA:</label></td>
                        <td class="tablecontent"><input type="time" name="hora_hasta" id="hora_hasta" required></td>
                    </tr>
                    

                    <tr>
                        <td class="tablelabel" id="tdt"><label for="total">TIEMPO TOTAL:</label></td>
                        <td class="tablecontent" id="tdt"><input type="text" name="tiempo_total" id="tiempo_total" value="0" readonly></td>
                    </tr>
                    
                    <tr id="fecha_reincorporacion_tr" style="display:none;">
                        <td class="tablelabel"><label for="fecha_reincorporacion">FECHA REINCORPORACIÓN:</label></td>
                        <td class="tablecontent"><input type="date" name="fecha_reincorporacion" id="fecha_reincorporacion"></td>
                    </tr>
                    <tr id="comision_reason" style="display:none;">
                        <td class="tablelabel"><label for="razon_comision">RAZÓN DE LA COMISIÓN:</label></td>
                        <td class="tablecontent">
                            <textarea name="razon_comision" id="razon_comision" "></textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <br>
            <label for="observaciones">OBSERVACIONES DEL SERVIDOR</label><br>
            <textarea name="observaciones" id="observaciones"></textarea>
            <br><br>
            <label for="justificacion">ARCHIVO DE JUSTIFICACIÓN</label><br>
            <input class="boton" type="file" name="justificacion" id="justificacion" accept="application/pdf, image/*">
            <br><br><hr>
            <br><input class="boton" type="submit" value="SOLICITAR PERMISO">
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
        { value: "vacaciones", text: "PERMISO VACACIONES" },
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

// Función para calcular el tiempo total con la validación de horario
function calcularTiempoTotal() {
    var fechaDesde = document.getElementById("fecha_desde").value;
    var horaDesde = document.getElementById("hora_desde").value || "00:00:00";
    var fechaHasta = document.getElementById("fecha_hasta").value;
    var horaHasta = document.getElementById("hora_hasta").value || "00:00:00";
    var motivoPermiso = document.getElementById("motivo").value;

    if (fechaDesde && fechaHasta) {
        var inicio = new Date(`${fechaDesde}T${horaDesde}`); 
        var fin = new Date(`${fechaHasta}T${horaHasta}`);

        if (fin < inicio) {
            document.getElementById("tiempo_total").value = "Error: La fecha final no puede ser anterior a la inicial.";
            return;
        }

        let tiempoTotal;
        if (motivoPermiso === "vacaciones") {
            tiempoTotal = `${calcularDias(fechaDesde, fechaHasta, true)} días, 0 horas, 0 minutos`;
            calcularFechaReincorporacion();

        } else {
            tiempoTotal = calcularTiempoDetallado(inicio, fin);
        }

        document.getElementById("tiempo_total").value = tiempoTotal;
    }
}

// Función para calcular días de vacaciones (contando el día final completo)
function calcularDias(fechaInicio, fechaFin, esVacaciones) {
    let diasContados = 0;
    let fechaActual = new Date(fechaInicio);

    while (fechaActual <= new Date(fechaFin)) {
        let diaSemana = fechaActual.getDay(); // 0 = Domingo, 6 = Sábado
        if (esVacaciones || (diaSemana !== 0 && diaSemana !== 6)) {
            diasContados++;
        }
        fechaActual.setDate(fechaActual.getDate() + 1);
    }

    return diasContados;
}

// Función para calcular tiempo detallado en permisos NO vacaciones
function calcularTiempoDetallado(fechaInicio, fechaFin) {
    // Calcular la diferencia de días y multiplicarlo por 9 horas
    let dias = 0; // Diferencia de días entre las fechas
    let horas = (fechaFin.getDate() - fechaInicio.getDate()) * 9; // Multiplicar los días por 9 horas laborales por día

    // Calcular las horas y minutos adicionales de la diferencia entre las fechas
    let horaInicio = fechaInicio.getHours();  // Hora de inicio
    let horaFin = fechaFin.getHours();        // Hora de fin
    let minutosInicio = fechaInicio.getMinutes(); // Minutos de inicio
    let minutosFin = fechaFin.getMinutes();     // Minutos de fin
    
    let horasExtras = horaFin - horaInicio; // Calcular la diferencia de horas entre el inicio y fin
    let minutosExtras = minutosFin - minutosInicio; // Calcular la diferencia de minutos

    // Ajustar si los minutos extras son negativos
    if (minutosExtras < 0) {
        minutosExtras += 60;
        horasExtras -= 1;
    }

    // Sumar las horas y minutos extras al total de horas
    horas += horasExtras;
    let minutosTotales = minutosExtras;

    // Restar la hora del almuerzo si cruza el rango 12:00 - 14:00
    let horasAlmuerzo = calcularHorasAlmuerzo(fechaInicio, fechaFin);
    horas -= horasAlmuerzo; // Restamos las horas de almuerzo

    // Convertir las horas en días laborales (8 horas = 1 día laboral)
    if (horas >= 8) {
        dias += Math.floor(horas / 8); // Convierte las horas en días
        horas = horas % 8; // Ajusta las horas restantes
    }

    // Asegurarse de que las horas no sean negativas
    if (horas < 0) {
        horas = 0;
    }

    // Crear el resultado en formato de texto
    let resultado = [];
    if (dias >= 0) resultado.push(`${dias} días`);
    if (horas >= 0) resultado.push(`${horas} horas`);
    if (minutosTotales >= 0) resultado.push(`${minutosTotales} minutos`);

    // Si no hay resultados, devolver "0 minutos"
    return resultado.length > 0 ? resultado.join(", ") : "0 minutos";
}


// Función para calcular el tiempo perdido por la hora del almuerzo (12:00 - 14:00)
function calcularHorasAlmuerzo(inicio, fin) {
    let horasRestadas = 0;
    let fechaActual = new Date(inicio);

    while (fechaActual <= fin) {
        let inicioDia = new Date(fechaActual);
        let finDia = new Date(fechaActual);

        // Determinar el inicio y fin del día
        if (fechaActual.toDateString() === inicio.toDateString()) {
            inicioDia.setHours(inicio.getHours(), inicio.getMinutes(), 0, 0);
        } else {
            inicioDia.setHours(8, 0, 0, 0); // Jornada laboral desde las 8:00 AM
        }

        if (fechaActual.toDateString() === fin.toDateString()) {
            finDia.setHours(fin.getHours(), fin.getMinutes(), 0, 0);
        } else {
            finDia.setHours(17, 0, 0, 0); // Fin de jornada laboral a las 5:00 PM
        }

        // Verificar si el intervalo abarca todo el almuerzo (12:00 - 14:00)
        if (inicioDia.getHours() <= 12 && finDia.getHours() >= 14) {
            horasRestadas += 1; // Solo se resta 1 hora si el intervalo completo cubre de 12:00 a 14:00
        }

        // Avanzar al siguiente día
        fechaActual.setDate(fechaActual.getDate() + 1);
        fechaActual.setHours(8, 0, 0, 0); // Reiniciar a las 8:00 AM
    }

    return horasRestadas;
}




function calcularFechaReincorporacion() {
        let fechaHasta = document.getElementById("fecha_hasta").value;
        let fechaReincorporacion = document.getElementById("fecha_reincorporacion");

        if (fechaHasta) {
            let fechaFin = new Date(fechaHasta);
            let diaSemana = fechaFin.getDay(); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado

            // Si el último día es viernes (5), reincorporación el lunes (sumar 3 días)
            // Si el último día es sábado (6), reincorporación el lunes (sumar 2 días)
            // Si es cualquier otro día, reincorporación al siguiente día hábil
            if (diaSemana === 4) {
                fechaFin.setDate(fechaFin.getDate() + 3);
            } else if (diaSemana === 5) {
                fechaFin.setDate(fechaFin.getDate() + 2);
            } else {
                fechaFin.setDate(fechaFin.getDate() + 1);
            }

            // Formatear la fecha en formato YYYY-MM-DD
            let fechaFormateada = fechaFin.toISOString().split('T')[0];
            fechaReincorporacion.value = fechaFormateada;
            fechaReincorporacion.readonly = true; // Deshabilitar campo
        }
    }






// Eventos para activar el cálculo automáticamente
document.getElementById("fecha_desde").addEventListener("change", calcularTiempoTotal);
document.getElementById("hora_desde").addEventListener("change", calcularTiempoTotal);
document.getElementById("fecha_hasta").addEventListener("change", calcularTiempoTotal);
document.getElementById("hora_hasta").addEventListener("change", calcularTiempoTotal);


// Función para actualizar las opciones de tipo de permiso
function actualizarTipoPermiso() {
    const tipoMotivo = document.getElementById('motivo').value;

    // Deshabilitar o habilitar los campos de hora
    const horaDesde = document.getElementById('hora_desde');
    const horaHasta = document.getElementById('hora_hasta');
    const fechaReincorporacionTr = document.getElementById('fecha_reincorporacion_tr');
    
    if (tipoMotivo === 'vacaciones') {
        // Deshabilitar las horas
        horaDesde.disabled = true;
        horaHasta.disabled = true;
        horaDesde.value = '00:00:00';
        horaHasta.value = '23:59:00';
        
        // Mostrar el campo Fecha Reincorporación
        fechaReincorporacionTr.style.display = 'table-row';
    } else {
        // Habilitar las horas
        horaDesde.disabled = false;
        horaHasta.disabled = false;
        
        // Ocultar el campo Fecha Reincorporación
        fechaReincorporacionTr.style.display = 'none';
    }
}

// Llamar a la función cuando el tipo de permiso cambie
document.getElementById('motivo').addEventListener('change', actualizarTipoPermiso);

// Ejecutar la función al cargar la página para aplicar el estado inicial
window.onload = function() {
    actualizarTipoPermiso();
};


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
    ahora.setHours(ahora.getHours() - 5); // Ajuste de zona horaria
    var fechaMinima = ahora.toISOString().slice(0, 10); // Solo fecha, sin hora

    document.getElementById("fecha_desde").setAttribute("min", fechaMinima);

    // Restringir fecha_hasta a partir de la fecha_desde seleccionada
    document.getElementById("fecha_desde").addEventListener("change", function() {
        document.getElementById("fecha_hasta").setAttribute("min", this.value);
    });

    // Calcular tiempo total al cambiar fechas u horas
    document.getElementById("fecha_desde").addEventListener("change", calcularTiempoTotal);
    document.getElementById("hora_desde").addEventListener("change", calcularTiempoTotal);
    document.getElementById("fecha_hasta").addEventListener("change", calcularTiempoTotal);
    document.getElementById("hora_hasta").addEventListener("change", calcularTiempoTotal);

    // Mostrar u ocultar razón de comisión
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
            alert("Permiso guardado.");
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
