<?php
if (isset($_GET['archivo'])) {
    $archivo = $_GET['archivo'];

    // Verifica que el archivo existe
    if (file_exists($archivo)) {
        // Forzar la descarga
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($archivo));
        readfile($archivo);
        exit;
    } else {
        echo "El archivo no existe.";
    }
} else {
    echo "No se especificó ningún archivo.";
}