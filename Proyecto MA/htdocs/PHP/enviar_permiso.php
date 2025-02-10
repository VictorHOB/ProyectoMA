<?php
session_start();
require 'conexion.php'; // Conexión a la base de datos

require '../libs/PHPMailer-master/PHPMailer-master/src/Exception.php';
require '../libs/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require '../libs/PHPMailer-master/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
//obtener json de configuración
$config = json_decode(file_get_contents('/home/vol9_1/infinityfree.com/if0_37560293/htdocs/PHP/config.json'), true);
$permitir_permisos_anteriores = $config['permitir_permisos_anteriores'];
$jefe_zonal=$config['jefe_zonal'];

    // Obtener las fechas del formulario
    $fecha_inicio = $_POST['fecha_desde'];
    $fecha_fin = $_POST['fecha_hasta'];
    $hora_inicio = $_POST['hora_desde'];
    $hora_fin = $_POST['hora_hasta'];
        
    // Obtener la fecha y hora actual en formato adecuado
    $fecha_actual = new DateTime();

    // Combinar fecha y hora en objetos DateTime para comparación
    $datetime_inicio = new DateTime("$fecha_inicio $hora_inicio");
    $datetime_fin = new DateTime("$fecha_fin $hora_fin");

    // Convertir fechas a formato correcto para MySQL
    $fecha_inicio_mysql = $datetime_inicio->format('Y-m-d H:i:s');
    $fecha_fin_mysql = $datetime_fin->format('Y-m-d H:i:s');


//VALIDAR HORA MINIMA PERMISO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$permitir_permisos_anteriores) {
    // Validar que las fechas no sean anteriores a la fecha actual
    if ($datetime_inicio < $fecha_actual || $datetime_fin <= $fecha_actual) {
        echo "\nError: No puedes solicitar permisos con fecha y hora anteriores a la actual.";
        exit; // Detener el procesamiento
    }
    // Validar que la fecha de fin no sea anterior a la fecha de inicio
    if ($datetime_fin <= $datetime_inicio) {
        echo "\nError: La fecha de fin no puede ser anterior a la fecha de inicio.";
        exit; // Detener el procesamiento
    }

}



//VARIABLES PARA EL CORREIO
$correosalida='dzd.chimborazo@gmail.com';
//$_SESSION['email'];
$sqlresp = "SELECT d.correo, a.id as empleado_id, b.responsable as jefe_oficina_id, c.nombres as nombre_responsable
            FROM empleados as a 
            LEFT JOIN oficinatecnica as b ON a.oficina_id = b.id 
            LEFT JOIN empleados as c ON c.id = b.responsable 
            LEFT JOIN usuarios as d ON c.numero_identificacion = d.cedula 
            WHERE a.numero_identificacion = ?";

$stmtresp = $conn->prepare($sqlresp);
$stmtresp->bind_param("s", $_SESSION['cedula']);
$stmtresp->execute();
$resultresp = $stmtresp->get_result();
$row = $resultresp->fetch_assoc();

/*
// Verifica si el empleado es el jefe de la oficina técnica
if ($row['empleado_id'] == $row['jefe_oficina_id']) {
    // Si el empleado es el jefe de la oficina técnica, asignar el correo del jefe de los jefes
    $sqlaux = "SELECT d.correo
            FROM empleados as a 
            LEFT JOIN usuarios as d ON a.numero_identificacion = d.cedula 
            WHERE a.id = ?";

    $stmtaux = $conn->prepare($sqlaux);
    $stmtaux->bind_param("i", $jefe_zonal);
    $stmtaux->execute();
    $resultaux = $stmtaux->get_result();
    $rowaux = $resultaux->fetch_assoc();
    //$correollegada = $row['correo'];//TEST
    //$correollegada = $rowaux['correo']; //EN CASO DE IGUALDAD, ENVIAR A JEFE ZONAL
} else {
    // Si no es el jefe, asignar el correo del jefe directo
    $correollegada = $row['correo'];
}
*/

$correoRRHH='eliana.laverde@ambiente.gob.ec'; //CORREO RRHH

// Variables para los datos del formulario
$user_id = $_SESSION['user_id'];
$username = $_SESSION['nombre'];
$tipo_permiso = $_POST['tipo_permiso'] ?? null;
$tiempo_total = $_POST['tiempo_total'] ?? "0";
$motivo = $_POST['motivo'] ?? null;
$razon_comision = ($motivo === 'comision') ? ($_POST['razon_comision'] ?? null) : 'ND';
//$observaciones = $_POST['observaciones'] ?? null;
$observaciones =  null;
if ($row['empleado_id'] == $row['jefe_oficina_id'] && $row['empleado_id'] != 999) {
    $responsable=$jefe_zonal ?? null;
}else{
    $responsable=$row['jefe_oficina_id'] ?? null;
}
$reincorporacion = $_POST['fecha_reincorporacion'] ?? '0000-00-00';

$directorio_destino = '/home/vol9_1/infinityfree.com/if0_37560293/htdocs/permisos/archivos/';

$archivo_justificacion = $_FILES['justificacion'] ?? null;
$ruta_archivo = null;

// Verificar si se ha subido un archivo
if ($archivo_justificacion && $archivo_justificacion['error'] === UPLOAD_ERR_NO_FILE) {
    $ruta_archivo = null; // No se subió archivo, por lo que la ruta será NULL
} elseif ($archivo_justificacion && $archivo_justificacion['error'] === UPLOAD_ERR_OK) {
    // Continuar con la validación del archivo
         $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
        $tamano_maximo = 2 * 1024 * 1024; // 2 MB
        $nombre_archivo = basename($archivo_justificacion['name']);
        $extension_archivo = strtolower(pathinfo($nombre_archivo, PATHINFO_EXTENSION));
        $tamano_archivo = $archivo_justificacion['size'];

        if (!in_array($extension_archivo, $extensiones_permitidas)) {
            echo "Error: Tipo de archivo no permitido. Solo se aceptan: PDF, JPG, JPEG, PNG.";
            exit;
        }

        if ($tamano_archivo > $tamano_maximo) {
            echo "Error: El archivo supera el tamaño máximo permitido de 2 MB.";
            exit;
        }
        // Generar un nombre único para el archivo
        $fecha_emision = date('Ymd');
        $nombre_unico = "{$fecha_emision}_{$user_id}_" . uniqid() . ".{$extension_archivo}";
        $ruta_archivo = $directorio_destino . $nombre_unico;

        // Mover el archivo al directorio de destino
        if (!move_uploaded_file($archivo_justificacion['tmp_name'], $ruta_archivo)) {
            echo "Error: No se pudo guardar el archivo en el servidor.";
            exit;
        }
} else {
    echo "Error al cargar el archivo.";
    exit;
}
        

// Preparar y ejecutar la consulta SQL para guardar el permiso
$sql = "INSERT INTO permisos (user_id, tipo_permiso, fecha_desde, fecha_hasta, tiempo_total, motivo, razon_comision, observaciones, archivo_justificacion, responsable, reincorporacion)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssssis",
    $user_id,            // string
    $tipo_permiso,       // string
    $fecha_inicio_mysql,        // string
    $fecha_fin_mysql,        // string
    $tiempo_total,       // string
    $motivo,             // string
    $razon_comision,     // string
    $observaciones,      // string
    $nombre_unico,        // string o null
    $responsable,        //int id_empleado responsable
    $reincorporacion    //Fecha reincorporación
);


if ($stmt->execute()) {
    $id_permiso = $conn->insert_id;

    // Enviar correo
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';        // Servidor SMTP de Gmail
        $mail->SMTPAuth   = true;                     // Habilitar autenticación SMTP
        $mail->Username   = '84607d002@smtp-brevo.com';     // Tu dirección de correo de Gmail
        $mail->Password   = 'f7zjC5tVgxJBsZrc';          // Tu contraseña de Gmail o App Password
        $mail->SMTPSecure = 'tls';                    // Usar SSL en lugar de TLS
        $mail->Port       = 587;                      // Puerto SMTP para SSL

        $mail->setFrom('series250@gmail.com', 'PERMISOS MINISTERIO DE AMBIENTE'); //TEST
        //$mail->setFrom($correosalida, 'PERMISOS MINISTERIO DE AMBIENTE'); //DIRECCIÓN REMITENTE MINISTERIO
        $mail->addAddress('dzd.chimborazo@gmail.com'); //DIRECCIÓN RECEPTOR REPOSITORIO
        $mail->isHTML(true);

        $mail->Subject = 'Nueva Solicitud de Permiso';

        $mail->Body = '
            <div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
                <h2 style="color: #0056b3;">Nueva Solicitud de Permiso</h2>
                <p>Se ha generado una nueva solicitud de permiso con los siguientes detalles:</p>
                <p>Responsable a cargo: ' . htmlspecialchars($row['nombre_responsable']) . '</p>
                <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
                    <tr style="background-color: #f2f2f2;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Solicitante:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($username) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Hora de emisión:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . date('Y-m-d H:i:s') . '</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>ID Permiso:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($id_permiso) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Usuario ID:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($user_id) . '</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Tipo de Permiso:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($tipo_permiso) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Desde:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($fecha_desde) . '</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Hasta:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($fecha_hasta) . '</td>
                    </tr>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Tiempo Total:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($tiempo_total) . '</td>
                    </tr>
                    <tr style="background-color: #f2f2f2;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Motivo:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($motivo) . '</td>
                    </tr>';

        if ($motivo === 'comision') {
            $mail->Body .= '
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Razón Comisión:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($razon_comision) . '</td>
                    </tr>';
        }

        $mail->Body .= '
                    <tr style="background-color: #f2f2f2;">
                        <td style="padding: 10px; border: 1px solid #ddd;"><strong>Observaciones:</strong></td>
                        <td style="padding: 10px; border: 1px solid #ddd;">' . htmlspecialchars($observaciones) . '</td>
                    </tr>
                </table>
                <p style="color: #555;">
                    Por favor, revise la solicitud en 
                    <a href="https://modularzonal3.kesug.com/" style="color: #0056b3; text-decoration: none;">este enlace</a> 
                    y tome las acciones necesarias.
                </p>
                <p style="font-size: 12px; color: #888;">Este mensaje fue generado automáticamente, no es necesario responder.</p>
            </div>';

        if ($ruta_archivo) {
            $mail->Body .= "<p><strong>Archivo de Justificación:</strong> <a href='https://modularzonal3.kesug.com/permisos/archivos/" . basename($ruta_archivo) . "'>Descargar</a></p>";
        }


                $mail->send();
                echo "Permiso guardado y correo enviado.";
            } catch (Exception $e) {
                echo "Permiso guardado, pero no se pudo enviar el correo. Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error al guardar el permiso: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
?>