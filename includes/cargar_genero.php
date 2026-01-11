<?php
// <-- VERSIÓN BLINDADA PHP 8.3 -->
ob_start(); // 1. Iniciar captura de errores (buffer)

// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluimos el archivo de conexión
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// armando el Query.
$query = "SELECT codigo, descripcion FROM catalogo_genero";

// Ejecutamos el Query con protección básica
try {
    $consulta = $dblink->query($query);
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode([]); // Retornar vacío en caso de error fatal
    exit;
}

// Inicializando el array
$datos = array(); 
$fila_array = 0;

// Recorriendo la Tabla
while($listado = $consulta->fetch(PDO::FETCH_BOTH)) {
    // 2. PROTECCIÓN CONTRA NULOS (trim en PHP 8 no acepta null)
    $codigo = trim($listado['codigo'] ?? ''); 
    $descripcion = $listado['descripcion'] ?? '';
    
    // Rellenando la array.
    $datos[$fila_array]["codigo"] = $codigo;
    
    // 3. REEMPLAZO DE utf8_encode
    // utf8_encode convertía ISO-8859-1 -> UTF-8. Usamos la función moderna:
    $datos[$fila_array]["descripcion"] = mb_convert_encoding($descripcion, 'UTF-8', 'ISO-8859-1');
    
    $fila_array++;
}

// 4. LIMPIEZA Y SALIDA JSON
ob_end_clean(); // Borrar cualquier warning previo
header('Content-Type: application/json; charset=utf-8');
echo json_encode($datos);   
?>