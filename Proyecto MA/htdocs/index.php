<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/colores.css">
    <title>PERMISOS MINISTERIO</title>
</head>
<body>
<header id="header">
    <div id="ministerio">
        <label>Ministerio del Ambiente, Agua y Transición Ecológica</label>
    </div>
    <div id="headerimages">
        <img src="MEDIA/Logo1.png" alt="Imagen 1" style="height: 75px; width: auto;">
        <img src="MEDIA/Logo2.png" alt="Imagen 2" style="height: 75px; width: auto;">
    </div>
    <div id="talentohumano">
        <label>TALENTO HUMANO<br>PERMISO OCASIONAL</label>
    </div>
</header>
<style>

/* Asegura que el main tenga un fondo de imágenes */
main {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
    box-sizing: border-box;
    height: 100%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    animation: cambioDeFondo 100s infinite; /* Cambia cada 15 segundos */
}

@keyframes cambioDeFondo {
            0% { background-image: url('MEDIA/img1.jpg'); }
            11.11% { background-image: url('MEDIA/img2.jpg'); }
            22.22% { background-image: url('MEDIA/img3.jpg'); }
            33.33% { background-image: url('MEDIA/img4.jpg'); }
            44.44% { background-image: url('MEDIA/img5.jpg'); }
            55.55% { background-image: url('MEDIA/img6.jpg'); }
            66.66% { background-image: url('MEDIA/img7.jpg'); }
            77.77% { background-image: url('MEDIA/img8.jpg'); }
            88.88% { background-image: url('MEDIA/img9.jpg'); }
            100% { background-image: url('MEDIA/img1.jpg'); }
        }


/* Estilo para el contenedor de login */
.login-container {
    width: 100%;

    max-width: 400px;
    margin: 0 auto;
    padding: 2em;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    background-color: rgba(255, 255, 255, 0.8); /* Fondo semitransparente para el formulario */
}

/* Agregar sombra para mejorar la legibilidad */
.login-container {
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3); /* Sombra para mejorar la visibilidad */
}

/* Hacer que el contenido del login sea legible sobre las imágenes */
h2, .form-group, button, p {
    color: #333; /* Cambiar color de texto para mejor contraste */
}


</style>

<main>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>

        <!-- Mostrar mensaje de error si existe -->
        <?php
        session_start();
        if (isset($_SESSION['login_message'])) {
            echo '<p style="color: red;">' . $_SESSION['login_message'] . '</p>';
            unset($_SESSION['login_message']); // Limpiar el mensaje después de mostrarlo
        }
        ?>

        <!-- Formulario de inicio de sesión -->
        <form action="PHP/login.php" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" id="email" name="email" required placeholder="Ingresa tu correo">
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña">
            </div>
            <button type="submit">Ingresar</button>
        </form>

        <!-- Enlace para solicitar ayuda -->
        <p><a href="#">¿Olvidaste tu contraseña?</a></p>
    </div>
</main>
<footer id="footer">
    Solicitar ayuda
</footer>
</body>
</html>
