<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Configuración Regional: Hora exacta de El Salvador
date_default_timezone_set('America/El_Salvador'); 

/**
 * Función para convertir bytes en formato legible (KB, MB, GB)[cite: 1]
 */
function formatoTamaño($bytes) {
    if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
    return ($bytes > 0) ? $bytes . ' bytes' : '0 bytes';
}

// Ruta base del sistema
$rutaBase = 'C:/TempSistemaRegistro/Carpetas/10391';
$carpeta = isset($_GET['ruta']) && !empty($_GET['ruta']) ? $_GET['ruta'] : $rutaBase;
$carpeta = str_replace('\\', '/', $carpeta);

// Validación de seguridad de la carpeta
if (empty($carpeta) || !is_dir($carpeta)) {
    header('Content-Type: application/json');
    echo json_encode([]); 
    exit; 
}

$contenido = array_diff(scandir($carpeta), array('..', '.'));
$resultados = [];

foreach ($contenido as $elemento) {
    $rutaCompleta = $carpeta . '/' . $elemento; 
    $timestamp = @filemtime($rutaCompleta);
    
    // Formateo de fecha y hora (12h am/pm)[cite: 1]
    $fechaMod = ($timestamp) ? date("d/m/Y", $timestamp) : 'N/A';
    $horaMod = ($timestamp) ? date("h:i:s a", $timestamp) : 'N/A';

    // Obtener extensión y asegurar que no sea undefined[cite: 1]
    $extension = pathinfo($rutaCompleta, PATHINFO_EXTENSION);
    $formato = empty($extension) ? 'archivo' : strtolower($extension);

    if (is_dir($rutaCompleta)) {
        $resultados[] = [
            'nombre'  => $elemento,
            'tipo'    => 'Carpeta',
            'formato' => 'carpeta',
            'tamaño'  => '--',
            'ruta'    => $rutaCompleta,
            'fecha'   => $fechaMod,
            'hora'    => $horaMod
        ];
    } else {
        $resultados[] = [
            'nombre'  => $elemento,
            'tipo'    => 'Archivo',
            'formato' => $formato,
            'tamaño'  => formatoTamaño(@filesize($rutaCompleta)),
            'ruta'    => $rutaCompleta,
            'fecha'   => $fechaMod,
            'hora'    => $horaMod
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($resultados);
?>