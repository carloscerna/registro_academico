<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ruta = isset($_POST['ruta']) ? $_POST['ruta'] : '';

    if (empty($ruta)) {
        echo json_encode(['success' => false, 'message' => 'La ruta no ha sido especificada.']);
        exit;
    }

    // Normalizar la ruta para que use siempre "/"
    $ruta = str_replace('\\', '/', $ruta);

    // Validar que la ruta está dentro del directorio permitido para evitar borrados maliciosos
    // Define la ruta base permitida
    $rutaBasePermitida = 'C:/TempSistemaRegistro/Carpetas/10391'; //
    $rutaBasePermitida = str_replace('\\', '/', $rutaBasePermitida); // Normalizar la ruta base

    // Asegurarse de que la ruta a borrar comience con la ruta base permitida
    if (strpos($ruta, $rutaBasePermitida) !== 0) {
        echo json_encode(['success' => false, 'message' => 'Operación no permitida: Acceso fuera del directorio autorizado.']);
        exit;
    }

    if (file_exists($ruta)) {
        if (is_dir($ruta)) {
            // Función para eliminar directorio y su contenido recursivamente
            function eliminarDirectorioRecursivo($dir) {
                if (!is_dir($dir)) {
                    return false;
                }
                $files = array_diff(scandir($dir), array('.', '..'));
                foreach ($files as $file) {
                    (is_dir("$dir/$file")) ? eliminarDirectorioRecursivo("$dir/$file") : unlink("$dir/$file");
                }
                return rmdir($dir);
            }

            if (eliminarDirectorioRecursivo($ruta)) {
                echo json_encode(['success' => true, 'message' => 'Carpeta eliminada exitosamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar la carpeta.']);
            }
        } else {
            if (unlink($ruta)) {
                echo json_encode(['success' => true, 'message' => 'Archivo eliminado exitosamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el archivo.']);
            }
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'El archivo o carpeta no existe.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud no permitido.']);
}
?>