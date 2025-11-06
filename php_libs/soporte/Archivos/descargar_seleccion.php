<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Directorio base permitido para descargas (¡IMPORTANTE POR SEGURIDAD!)
$rutaBasePermitida = 'C:/TempSistemaRegistro/Carpetas';
// Normalizar la ruta base
$rutaBasePermitida = str_replace('\\', '/', $rutaBasePermitida);

if (isset($_POST['archivos']) && is_array($_POST['archivos'])) {
    
    $archivosADescargar = $_POST['archivos'];
    
    if (empty($archivosADescargar)) {
        die("No se seleccionaron archivos.");
    }

    $zip = new ZipArchive();
    $nombreZip = 'descarga_multiple_' . date('Y-m-d_His') . '.zip';
    
    // Crear un archivo zip temporal en un lugar seguro (o en la misma carpeta)
    $rutaZipTemporal = sys_get_temp_dir() . '/' . $nombreZip;

    if ($zip->open($rutaZipTemporal, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("No se pudo crear el archivo zip.");
    }

    $archivosAgregados = 0;

    foreach ($archivosADescargar as $rutaArchivo) {
        // Normalizar la ruta del archivo
        $rutaArchivoNormalizada = str_replace('\\', '/', $rutaArchivo);

        // --- ¡¡CONTROL DE SEGURIDAD CRÍTICO!! ---
        // Evitar ataques "Path Traversal"
        // Asegurarse de que el archivo está DENTRO de la carpeta permitida.
        if (strpos($rutaArchivoNormalizada, $rutaBasePermitida) !== 0) {
            // El archivo está fuera del directorio permitido, ignorar
            continue; 
        }

        if (is_file($rutaArchivoNormalizada)) {
            // Añadir archivo al zip
            // basename() se usa para que el zip no contenga la estructura de carpetas del servidor
            $zip->addFile($rutaArchivoNormalizada, basename($rutaArchivoNormalizada));
            $archivosAgregados++;
        }
    }

    $zip->close();

    if ($archivosAgregados > 0) {
        // Forzar la descarga del ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($nombreZip) . '"');
        header('Content-Length: ' . filesize($rutaZipTemporal));
        header('Pragma: no-cache'); 
        header('Expires: 0');
        
        // Enviar el archivo al navegador
        readfile($rutaZipTemporal);
        
        // Borrar el archivo zip temporal
        unlink($rutaZipTemporal);
        exit;
    } else {
        // Si no se agregó ningún archivo válido (ej. solo carpetas o rutas inválidas)
        unlink($rutaZipTemporal); // Borrar zip vacío
        die("No se encontraron archivos válidos para descargar.");
    }

} else {
    die("No se proporcionaron archivos.");
}
?>