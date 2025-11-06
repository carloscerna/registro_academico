<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Directorio base permitido (¡IMPORTANTE POR SEGURIDAD!)
$rutaBasePermitida = 'C:/TempSistemaRegistro/Carpetas';
$rutaBasePermitida = str_replace('\\', '/', $rutaBasePermitida);

/**
 * Función para eliminar un directorio y todo su contenido recursivamente.
 *
 * @param string $dir Ruta al directorio.
 * @return bool True si se eliminó, False si hubo un error.
 */
function borrarRecursivo($dir) {
    if (!is_dir($dir)) {
        return false;
    }
    
    // scandir() lista los archivos, incluyendo '.' y '..'
    $archivos = array_diff(scandir($dir), array('.', '..'));

    foreach ($archivos as $archivo) {
        $rutaCompleta = $dir . '/' . $archivo;
        
        if (is_dir($rutaCompleta)) {
            // Si es un directorio, llamar recursivamente
            borrarRecursivo($rutaCompleta);
        } else {
            // Si es un archivo, eliminarlo
            @unlink($rutaCompleta);
        }
    }
    
    // Finalmente, eliminar el directorio ahora vacío
    return @rmdir($dir);
}


$respuesta = [
    'success' => false,
    'message' => 'No se proporcionaron rutas.',
    'exitos' => 0,
    'fallos' => 0
];

if (isset($_POST['rutas']) && is_array($_POST['rutas'])) {
    
    $rutasAEliminar = $_POST['rutas'];
    
    if (empty($rutasAEliminar)) {
        $respuesta['message'] = 'La lista de rutas está vacía.';
        header('Content-Type: application/json');
        echo json_encode($respuesta);
        exit;
    }

    $exitos = 0;
    $fallos = 0;

    foreach ($rutasAEliminar as $ruta) {
        // Normalizar la ruta del archivo
        $rutaNormalizada = str_replace('\\', '/', $ruta);

        // --- ¡¡CONTROL DE SEGURIDAD CRÍTICO!! ---
        // Asegurarse de que el archivo está DENTRO de la carpeta permitida.
        if (strpos($rutaNormalizada, $rutaBasePermitida) !== 0) {
            $fallos++;
            continue; // Ignorar esta ruta, está fuera del directorio permitido
        }

        try {
            if (is_file($rutaNormalizada)) {
                if (@unlink($rutaNormalizada)) {
                    $exitos++;
                } else {
                    $fallos++;
                }
            } elseif (is_dir($rutaNormalizada)) {
                if (borrarRecursivo($rutaNormalizada)) {
                    $exitos++;
                } else {
                    $fallos++;
                }
            } else {
                // La ruta no existe (quizás fue eliminada en un paso anterior)
                $fallos++;
            }
        } catch (Exception $e) {
            $fallos++;
        }
    }

    $respuesta['success'] = true;
    $respuesta['exitos'] = $exitos;
    $respuesta['fallos'] = $fallos;
    $respuesta['message'] = "Eliminación completada. Éxitos: $exitos, Fallos: $fallos.";

}

header('Content-Type: application/json');
echo json_encode($respuesta);
?>