<?php
session_start();
require '../../PHP/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["archivo_justificacion"])) {
    $permiso_id = intval($_POST['permiso_id']);
    $archivo = $_FILES["archivo_justificacion"];
    $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    $tamano_maximo = 2 * 1024 * 1024; // 2 MB
    $nombre_archivo = basename($archivo['name']);
    $extension_archivo = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
    $tamano_archivo = $archivo['size'];
    $directorio_destino = '/home/vol9_1/infinityfree.com/if0_37560293/htdocs/permisos/archivos/';

    // Validaciones
    if (!in_array($extension_archivo, $extensiones_permitidas)) {
        echo "Error: Tipo de archivo no permitido.";
        exit;
    }

    if ($tamano_archivo > $tamano_maximo) {
        echo "Error: Archivo demasiado grande (máx. 2MB).";
        exit;
    }

    // Generar nombre único
    $fecha_emision = date('Ymd');
    $nombre_unico = "{$fecha_emision}_{$permiso_id}_" . uniqid() . ".{$extension_archivo}";
    $ruta_archivo = $directorio_destino . $nombre_unico;

    if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
        $query = "UPDATE permisos SET archivo_justificacion = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("si", $nombre_unico, $permiso_id);
            if ($stmt->execute()) {
                echo "Éxito: Archivo subido con éxito.";
                exit; // Detener la ejecución aquí
            } else {
                echo "Error: Error al guardar el archivo en la base de datos.";
                exit;
            }
            $stmt->close();
        } else {
            echo "Error: Error en la preparación de la consulta.";
            exit;
        }
    } else {
        echo "Error: Error al mover el archivo.";
        exit;
    }
}
?>
