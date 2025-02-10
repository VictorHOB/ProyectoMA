<?php include '../PHP/auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../CSS/colores.css">
    <title>Cambiar Contraseña</title>
</head>
<body>
    <?php include '../PHP/header.php'; ?>

    <div class="contenedor">
        <h2>Cambiar Contraseña</h2>
        <form id="form-cambiar-contrasena">
            <label for="contrasena_actual">Contraseña Actual:</label>
            <input type="password" id="contrasena_actual" name="contrasena_actual" required>

            <label for="nueva_contrasena">Nueva Contraseña:</label>
            <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>

            <label for="confirmar_contrasena">Confirmar Nueva Contraseña:</label>
            <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>

            <button type="submit">Actualizar Contraseña</button>
        </form>
        <p id="mensaje"></p>
    </div>
<style>
        /* Estilos proporcionados */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }



        form h2 {
            text-align: center;
            color: #333;
        }

        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0 20px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }


        .alerta {
            background-color: #f44336;
            color: white;
            padding: 15px;
            margin-top: 10px;
            text-align: center;
            border-radius: 5px;
            display: none;
        }

        .alerta.success {
            background-color: #4CAF50;
        }


    </style>
    <script>
        document.getElementById("form-cambiar-contrasena").addEventListener("submit", function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            
            fetch("cambio_contra/cambio.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text()) // Convertir a texto primero para ver posibles errores
            .then(text => {
                try {
                    let data = JSON.parse(text); // Intentar parsear JSON
                    let mensaje = document.getElementById("mensaje");
                    mensaje.textContent = data.message;
                    mensaje.style.color = data.status === "success" ? "green" : "red";
                } catch (error) {
                    console.error("Respuesta inesperada del servidor:", text);
                }
            })
            .catch(error => console.error("Error en fetch:", error));
        });
    </script>
</body>
</html>
