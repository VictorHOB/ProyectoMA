<?php
// Inicia la sesión
session_start();

// Conexión a la base de datos
require '../../PHP/conexion.php';
require '../../libs/PHPMailer-master/PHPMailer-master/src/Exception.php';
require '../../libs/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require '../../libs/PHPMailer-master/PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$correosalida = 'dzd.chimborazo@gmail.com';
$contraseñaSalida = 'plxu smrg ucoi edvn';

// Leer el cuerpo de la solicitud en formato JSON
$inputJSON = file_get_contents('php://input');
$inputData = json_decode($inputJSON, true); // Convertir JSON a un array asociativo

// Verifica que se hayan recibido los datos necesarios
if (isset($inputData['id']) && isset($inputData['accion'])) {
    $id = intval($inputData['id']);  // Convertir a entero para evitar inyecciones
    $accion = htmlspecialchars($inputData['accion']);  // Sanitizar texto para evitar XSS
    $observacion = isset($inputData['observacion']) ? htmlspecialchars($inputData['observacion']) : ''; // Sanitizar la observación


    // Aquí puedes continuar con el procesamiento...


    // Determina el nuevo estado según la acción
    $nuevoEstado = ($accion === 'Aprobar') ? 'Aprobado' : 'Denegado';

    // Prepara y ejecuta la actualización del estado del permiso
    $stmt = $conn->prepare("UPDATE permisos SET estado = ?, observaciones_jefe = ? WHERE id = ?");
    $stmt->bind_param("ssi", $nuevoEstado, $observacion, $id);
    $stmt->execute();

    // Si la actualización fue exitosa
    if ($stmt->affected_rows > 0) {
        
        /* Configuración del correo
        // Obtener los detalles del permiso y el correo del empleado asociado al permiso
        $stmtDetalles = $conn->prepare("
            SELECT p.user_id, p.tipo_permiso, p.fecha_desde, p.fecha_hasta, p.tiempo_total, p.motivo, p.razon_comision, p.observaciones, p.archivo_justificacion, u.correo 
            FROM permisos p
            JOIN usuarios u ON p.user_id = u.id
            WHERE p.id = ?");
        $stmtDetalles->bind_param("i", $id);
        $stmtDetalles->execute();
        $resultDetalles = $stmtDetalles->get_result();

        if ($resultDetalles->num_rows > 0) {
            $permiso = $resultDetalles->fetch_assoc();
            $correoEmpleado = $permiso['correo'];

            
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            try {
                // Configuración del servidor SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp-relay.brevo.com';        // Servidor SMTP de Gmail
                $mail->SMTPAuth   = true;                     // Habilitar autenticación SMTP
                $mail->Username   = '84607d002@smtp-brevo.com';     // Tu dirección de correo de Gmail
                $mail->Password   = 'f7zjC5tVgxJBsZrc';          // Tu contraseña de Gmail o App Password
                $mail->SMTPSecure = 'tls';                    // Usar SSL en lugar de TLS
                $mail->Port       = 587;                      // Puerto SMTP para SSL

                // Configuración del remitente y destinatario
                $mail->setFrom('series250@gmail.com', 'PERMISOS MINISTERIO DE AMBIENTE'); //TEST
                //$mail->setFrom($correosalida, 'PERMISOS MINISTERIO DE AMBIENTE'); //DIRECCIÓN REMITENTE MINISTERIO
                //$mail->addAddress($correoEmpleado);//DIRECCION RECEPTOR

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Estado de tu Solicitud de Permiso';
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h2 style='color: #0056b3;'>Estado de tu Solicitud de Permiso</h2>
                        <p>Tu solicitud de permiso con ID {$id} ha sido <strong>{$nuevoEstado}</strong>.</p>
                        <p><strong>Detalles del permiso:</strong></p>
                        <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                            <tr style='background-color: #f2f2f2;'>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Tipo de Permiso:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['tipo_permiso']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Fecha Desde:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['fecha_desde']}</td>
                            </tr>
                            <tr style='background-color: #f2f2f2;'>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Fecha Hasta:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['fecha_hasta']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Tiempo Total:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['tiempo_total']}</td>
                            </tr>
                            <tr style='background-color: #f2f2f2;'>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Motivo:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['motivo']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Razón de Comisión:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['razon_comision']}</td>
                            </tr>
                            <tr style='background-color: #f2f2f2;'>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Observaciones:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>{$permiso['observaciones']}</td>
                            </tr>
                            <tr>
                                <td style='padding: 10px; border: 1px solid #ddd;'><strong>Archivo de Justificación:</strong></td>
                                <td style='padding: 10px; border: 1px solid #ddd;'>" . (!empty($permiso['archivo_justificacion']) ? "<a href='https://modularzonal3.kesug.com/permisos/archivos/" 
                                . $permiso['archivo_justificacion'] . "'>Ver archivo</a>" : 'No disponible') . "</td>
                            </tr>
                        </table>
                        <p style='font-size: 12px; color: #888;'>Este mensaje fue generado automáticamente, no es necesario responder.</p>
                    </div>";
                */
                // Enviar el correo
                if ( true) {
                    echo 'success';
        } else {
            echo 'Error al actualizar permiso.';
        }
    } 
} else {
    echo 'Error al actualizar permiso.';
}
$stmt->close();
$conn->close();
?>
