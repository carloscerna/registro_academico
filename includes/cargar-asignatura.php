<?php
// cargar-asignatura.php (VERSIÓN CON isset() PARA COMPATIBILIDAD)

header('Content-Type: application/json; charset=utf-8');
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php"; // Asegúrate de incluir tu conexión PDO ($dblink)

$respuesta = [];

// 1. Validar parámetros básicos (modalidad y annlectivo siempre necesarios)
$modalidad = $_REQUEST["modalidad"] ?? null;
$annlectivo = $_REQUEST["annlectivo"] ?? null;

if (!$modalidad || !$annlectivo) {
    echo json_encode(["error" => "Parámetros insuficientes (modalidad o annlectivo faltante)"]);
    exit;
}

// 2. Determinar el código de grado según el parámetro presente
$codigo_grado = null;
if (isset($_REQUEST["codigo_grado_seccion_turno"])) {
    // Modo antiguo: Extraer grado del código completo
    $codigo_completo = $_REQUEST["codigo_grado_seccion_turno"];
    // *** ¡IMPORTANTE! Ajusta substr() según tu estructura exacta ***
    // Asumiendo que el grado son los caracteres 3 y 4 (ej: 17'07'012501 -> '07')
    $codigo_grado = substr($codigo_completo, 0, 2);
} else if (isset($_REQUEST["elegido"])) {
    // Modo nuevo: Usar 'elegido' directamente
    $codigo_grado = $_REQUEST["elegido"];
}

// Validar que obtuvimos un código de grado
if ($codigo_grado === null) {
    echo json_encode(["error" => "Parámetro de grado faltante (ni codigo_grado_seccion_turno ni elegido)"]);
    exit;
}


try {
    if ($errorDbConexion) { throw new Exception("Error de conexión BD."); }

    // 3. Consulta SQL usando los parámetros correctos
    $query_asignaturas = "SELECT DISTINCT aaa.codigo_asignatura as codigo, asig.nombre as nombre, asig.nombre as descripcion, asig.ordenar
                         FROM a_a_a_bach_o_ciclo aaa
                         INNER JOIN asignatura asig ON aaa.codigo_asignatura = asig.codigo
                         WHERE aaa.codigo_bach_o_ciclo = :modalidad
                           AND aaa.codigo_grado = :grado
                           AND aaa.codigo_ann_lectivo = :annlectivo
                         ORDER BY asig.ordenar";

    $stmt = $dblink->prepare($query_asignaturas);
    $stmt->bindParam(':modalidad', $modalidad);
    $stmt->bindParam(':grado', $codigo_grado); // Usar el $codigo_grado determinado
    $stmt->bindParam(':annlectivo', $annlectivo);
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Devolver resultado
    if ($resultado) {
         $respuesta = array_map(function($item) { unset($item['ordenar']); return $item; }, $resultado);
    } else {
        $respuesta = [];
    }
    echo json_encode($respuesta);

} catch (Exception $e) {
    echo json_encode(["error" => "Error al consultar asignaturas: " . $e->getMessage()]);
}
?>