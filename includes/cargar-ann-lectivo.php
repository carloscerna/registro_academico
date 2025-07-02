<?php
// Definir ruta raíz
$path_root = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

// Incluir archivos de conexión y funciones
require_once "$path_root/registro_academico/includes/mainFunctions_conexion.php";
require_once "$path_root/registro_academico/includes/funciones.php";

// Inicializar variables
$verificar_ann_lectivo = $_POST['verificar_ann_lectivo'] ?? 'no';

// Verificar el valor del perfil desde la sesión
$codigo_perfil = $_SESSION['codigo_perfil'] ?? null;

// Lógica para decidir qué consulta usar
if (in_array($codigo_perfil, ['01', '04', '05'])) {
    $sql = "SELECT codigo, nombre FROM ann_lectivo ORDER BY codigo DESC";
} else {
    $sql = "SELECT codigo, nombre FROM ann_lectivo WHERE estatus = 't' ORDER BY codigo DESC";
}

try {
    // Ejecutar la consulta
    $stmt = $dblink->prepare($sql);
    $stmt->execute();

    // Construir el array de resultados
    $datos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $datos[] = [
            'codigo' => trim($row['codigo']),
            'nombre' => convertirTexto($row['nombre']),
        ];
    }

    // Enviar respuesta JSON
    echo json_encode($datos);
} catch (PDOException $e) {
    // Enviar error si ocurre
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos.', 'detalles' => $e->getMessage()]);
}
