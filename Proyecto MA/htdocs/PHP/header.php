<!-- header.php -->
<header id="header">
    <div id="ministerio">
        <label>Ministerio del Ambiente, Agua y Transición Ecológica</label>
    </div>
    <div id="headerimages">
        <img src="../MEDIA/Logo1.png" alt="Imagen 1" style="height: 75px; width: auto;">
        <img src="../MEDIA/Logo2.png" alt="Imagen 2" style="height: 75px; width: auto;">
    </div>
    <div id="talentohumano">
        <label>TALENTO HUMANO<br>PERMISO OCASIONAL</label>
    </div>
</header>

<!-- Botón con enlace -->
<a href="https://modularzonal3.kesug.com/modular.php" class="btn-modular">Ir al Modular</a>
<a href="https://modularzonal3.kesug.com/PHP/logout.php" class="btn-logout">Salir</a>

<!-- Estilos del botón -->
<style>
    .btn-modular {
        display: inline-block; /* Hace que se vea como un botón */
        width: 10%; /* Ancho del botón */
        padding: 10px 20px; /* Espaciado interno */
        background-color: white; /* Color de fondo blanco */
        color: black; /* Color del texto negro */
        text-decoration: none; /* Quita el subrayado */
        font-size: 16px; /* Tamaño del texto */
        font-weight: bold; /* Texto en negrita */
        border: 2px solid black; /* Bordes negros */
        border-radius: 5px; /* Bordes redondeados */
        transition: background-color 0.3s ease, color 0.3s ease; /* Transición suave */
        text-align: center; /* Centra el texto */
        margin: 10px 10px; /* Espaciado superior e inferior */

    }

    .btn-modular:hover {
        background-color: black; /* Fondo negro al pasar el ratón */
        color: white; /* Texto blanco al pasar el ratón */
    }

    .btn-logout {
        display: inline-block;
        width: 10%;
        padding: 10px 20px;
        background-color: red;
        color: white;
        text-decoration: none;
        font-size: 16px;
        font-weight: bold;
        border: 2px solid red;
        border-radius: 5px;
        transition: background-color 0.3s ease, color 0.3s ease;
        text-align: center;
        margin: 10px 10px;
    }

    .btn-logout:hover {
        background-color: darkred;
        border-color: darkred;
    }
</style>
