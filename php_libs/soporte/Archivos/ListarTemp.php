<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ruta inicial por defecto
$rutaBase = 'C:/TempSistemaRegistro/Carpetas/10391';

// Obtener la ruta del GET o usar la ruta base
$carpeta = isset($_GET['ruta']) && !empty($_GET['ruta']) ? $_GET['ruta'] : $rutaBase;

// Normalizar la ruta para que use siempre "/"
$carpeta = str_replace('\\', '/', $carpeta);

// Validar que la carpeta exista y sea un directorio
if (empty($carpeta) || !is_dir($carpeta)) {
    header('Content-Type: application/json');
    // Devolvemos un array vacío o un error para que DataTables no falle
    echo json_encode([]); 
    exit; // Detener si la carpeta no es válida
}

$contenido = array_diff(scandir($carpeta), array('..', '.'));
$resultados = [];

foreach ($contenido as $elemento) {
    // Usamos "/" directamente ya que normalizamos $carpeta
    $rutaCompleta = $carpeta . '/' . $elemento; 
    
    // --- INICIO DE LA MODIFICACIÓN ---
    
    // Obtener el timestamp de la última modificación
    // Usamos @ para suprimir errores si el archivo es temporal o inaccesible
    $timestamp = @filemtime($rutaCompleta); 
    
    if ($timestamp === false) {
        // En caso de error (ej. permisos), mostrar 'N/A'
        $fechaMod = 'N/A';
        $horaMod = 'N/A';
    } else {
        // Formatear la fecha y la hora
        $fechaMod = date("Y-m-d", $timestamp);
        $horaMod = date("H:i:s", $timestamp);
    }

    // --- FIN DE LA MODIFICACIÓN ---

    if (is_dir($rutaCompleta)) {
        $resultados[] = [
            'nombre'  => $elemento,
            'tipo'    => 'Carpeta',
            'ruta'    => $rutaCompleta,
            'formato' => 'Carpeta', // Añadido para consistencia
            'fecha'   => $fechaMod,  // <-- NUEVO
            'hora'    => $horaMod    // <-- NUEVO
        ];
    } else {
        $extension = pathinfo($rutaCompleta, PATHINFO_EXTENSION);
        $resultados[] = [
            'nombre'  => $elemento,
            'tipo'    => 'Archivo',
            'formato' => empty($extension) ? 'Archivo' : $extension, // Manejar archivos sin extensión
            'ruta'    => $rutaCompleta,
            'fecha'   => $fechaMod,  // <-- NUEVO
            'hora'    => $horaMod    // <-- NUEVO
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($resultados);

// NOTA: Se eliminó un '}' extra que estaba aquí en tu archivo original.
?>