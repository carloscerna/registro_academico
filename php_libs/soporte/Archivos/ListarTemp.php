<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//
$carpeta = isset($_GET['ruta']) && !empty($_GET['ruta']) ? $_GET['ruta'] : 'C:/TempSistemaRegistro/Carpetas/10391'; // Ruta inicial
// Normalizar la ruta para que use siempre "/"
$carpeta = str_replace('\\', '/', $carpeta);


if (empty($carpeta)) {
    die("Error: La ruta está vacía."); // Detener si la carpeta no está definida
}
//
$contenido = array_diff(scandir($carpeta), array('..', '.'));
$resultados = [];
foreach ($contenido as $elemento) {
    $rutaCompleta = $carpeta . DIRECTORY_SEPARATOR . $elemento;
    // Normalizar la ruta completa
    $rutaCompleta = str_replace('\\', '/', $rutaCompleta);

    if (is_dir($rutaCompleta)) {
        $resultados[] = [
            'nombre' => $elemento,
            'tipo' => 'Carpeta',
            'ruta' => $rutaCompleta // Ruta completa de la carpeta
        ];
    } else {
        $extension = pathinfo($rutaCompleta, PATHINFO_EXTENSION);
        $resultados[] = [
            'nombre' => $elemento,
            'tipo' => 'Archivo',
            'formato' => $extension
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($resultados);
