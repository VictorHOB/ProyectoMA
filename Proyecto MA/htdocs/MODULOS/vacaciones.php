<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Calendario Interactivo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #calendario {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 20px;
            width: 70%;
        }
        .dia {
            font-weight: bold;
            text-align: center;
            padding: 10px;
            background-color: #f3f3f3;
        }
        .fecha {
            text-align: center;
            padding: 15px;
            background-color: #e0e0e0;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .fecha:hover {
            background-color: #c0c0c0;
        }
        .vacio {
            background-color: #f3f3f3;
        }
        #info-vacaciones {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            width: 70%;
            text-align: center;
        }
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 70%;
            margin-bottom: 20px;
        }
        .controls select {
            padding: 5px;
        }
    </style>
</head>

<?php include '../PHP/header.php'; ?>

<body>

    <h1>Calendario de Vacaciones</h1>

    <div class="controls">
        <!-- Selección de mes y año -->
        <button onclick="prevMonth()">Mes Anterior</button>
        <select id="mes" onchange="actualizarCalendario()">
            <option value="0">Enero</option>
            <option value="1">Febrero</option>
            <option value="2">Marzo</option>
            <option value="3">Abril</option>
            <option value="4">Mayo</option>
            <option value="5">Junio</option>
            <option value="6">Julio</option>
            <option value="7">Agosto</option>
            <option value="8">Septiembre</option>
            <option value="9">Octubre</option>
            <option value="10">Noviembre</option>
            <option value="11">Diciembre</option>
        </select>
        <input type="number" id="año" value="2024" onchange="actualizarCalendario()" min="1900" max="2100">
        <button onclick="nextMonth()">Mes Siguiente</button>
    </div>

    <div id="calendario">
        <!-- Cabeceras de los días de la semana -->
        <div class="dia">Lunes</div>
        <div class="dia">Martes</div>
        <div class="dia">Miércoles</div>
        <div class="dia">Jueves</div>
        <div class="dia">Viernes</div>
        <div class="dia">Sábado</div>
        <div class="dia">Domingo</div>
    </div>

    <div id="info-vacaciones">
        <!-- Esta sección mostrará la información cuando se haga clic en una fecha -->
        Haz clic en una fecha para ver las vacaciones de los empleados.
    </div>

    <script>
        const vacaciones = {
            1: 'Empleado 1 de vacaciones',
            5: 'Empleado 4 en vacaciones',
            10: 'Empleado 5 y 6 de vacaciones',
            15: 'Empleado 7 en vacaciones',
            20: 'Empleado 8 y 9 de vacaciones',
            25: 'Empleado 10 en vacaciones',
            31: 'Empleado 11 de vacaciones'
        };

        const calendario = document.getElementById('calendario');
        const mesSelect = document.getElementById('mes');
        const añoInput = document.getElementById('año');

        // Función para actualizar el calendario según mes y año seleccionados
        function actualizarCalendario() {
            const mes = parseInt(mesSelect.value);
            const año = parseInt(añoInput.value);
            
            calendario.innerHTML = `
                <div class="dia">Lunes</div>
                <div class="dia">Martes</div>
                <div class="dia">Miércoles</div>
                <div class="dia">Jueves</div>
                <div class="dia">Viernes</div>
                <div class="dia">Sábado</div>
                <div class="dia">Domingo</div>
            `; // Resetea el calendario

            const primerDiaMes = new Date(año, mes, 1).getDay();
            const diasEnMes = new Date(año, mes + 1, 0).getDate();
            const vaciosAntes = (primerDiaMes + 6) % 7; // Ajuste para que el lunes sea el primer día (domingo es 0)

            // Espacios vacíos antes del primer día del mes
            for (let i = 0; i < vaciosAntes; i++) {
                const vacio = document.createElement('div');
                vacio.classList.add('vacio');
                calendario.appendChild(vacio);
            }

            // Añadir los días del mes
            for (let dia = 1; dia <= diasEnMes; dia++) {
                const fechaDiv = document.createElement('div');
                fechaDiv.classList.add('fecha');
                fechaDiv.textContent = dia;
                fechaDiv.onclick = () => mostrarInfo(dia);
                calendario.appendChild(fechaDiv);
            }
        }

        // Función que muestra la información de vacaciones
        function mostrarInfo(dia) {
            const info = vacaciones[dia] || 'No hay vacaciones en esta fecha';
            document.getElementById('info-vacaciones').innerHTML = `Información para el día ${dia}: ${info}`;
        }

        // Funciones para cambiar de mes
        function prevMonth() {
            let mes = parseInt(mesSelect.value);
            let año = parseInt(añoInput.value);

            if (mes === 0) {
                mes = 11;
                año--;
            } else {
                mes--;
            }

            mesSelect.value = mes;
            añoInput.value = año;
            actualizarCalendario();
        }

        function nextMonth() {
            let mes = parseInt(mesSelect.value);
            let año = parseInt(añoInput.value);

            if (mes === 11) {
                mes = 0;
                año++;
            } else {
                mes++;
            }

            mesSelect.value = mes;
            añoInput.value = año;
            actualizarCalendario();
        }

        // Inicializa el calendario en el mes actual
        actualizarCalendario();
    </script>

</body>

<footer id="footer">
    Solicitar ayuda
</footer>

</html>
