<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
//
$ruta = "$path_root/registro_academico/vendore/autoload.php";
require $ruta;
//
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require "$path_root/registro_academico/vendore/PHPMailer/PHPMailer/src/Exception.php";
require "$path_root/registro_academico/vendore/PHPMailer/PHPMailer/src/PHPMailer.php";
require "$path_root/registro_academico/vendore/PHPMailer/PHPMailer/src/SMTP.php";


$phpMailer = new PHPMailer;
# Puede ser ruta relativa o absoluta
$nombreDelDocumento = "C:/TempSistemaRegistro/Carpetas/10391/Boleta Calificaciones/Educacion_Basica/2021/Cuarto A/123.pdf";


if (!file_exists($nombreDelDocumento)) {
    exit("El archivo $nombreDelDocumento no existe");
}


try {
    $phpMailer->setFrom("carlos.w.cerna@gmail.com", "Luis Cabrera Benito"); # Correo y nombre del remitente
    $phpMailer->addAddress("carlos.wilfredo77@gmail.com"); # El destinatario
    $phpMailer->Subject = "Archivo adjunto"; # Asunto
    $phpMailer->Body = "Hola, amigo. Estamos probando los archivos adjuntos."; # Cuerpo en texto plano
    // Aquí la magia:
    $phpMailer->addAttachment($nombreDelDocumento);
    if (!$phpMailer->send()) {
        echo "Error enviando correo: " . $phpMailer->ErrorInfo;
    }
    # Opcionalmente podrías eliminar el archivo después de enviarlo, si quieres
    // if (file_exists($nombreDelDocumento)) {
    // unlink($nombreDelDocumento);
    // }
    echo "Enviado correctamente";
} catch (Exception $e) {
    echo "Excepción: " . $e->getMessage();
}
?>