<?php
session_start();

// Solo permitimos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Sanitizar el nombre de archivo
$filename = basename($_POST['file'] ?? '');
if ($filename === '') {
    http_response_code(400);
    exit('Parámetro "file" inválido');
}

// Ruta donde guardaste los exceles
$codigoInstitucion = $_SESSION['codigo_institucion'] ?? 'default';
$dir = "C:/TempSistemaRegistro/Carpetas/{$codigoInstitucion}";
$filepath = "{$dir}/{$filename}";

if (!is_readable($filepath)) {
    http_response_code(404);
    exit('Archivo no encontrado');
}

// Headers para forzar descarga
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// Enviar el fichero
readfile($filepath);
exit;
