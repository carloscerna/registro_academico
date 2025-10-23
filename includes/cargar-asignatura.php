<?php
// cargar-asignatura.php (VERSIÓN MODIFICADA CON LÓGICA DE ROLES)

header('Content-Type: application/json; charset=utf-8');
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php"; // Asegúrate de incluir tu conexión PDO ($dblink)

// *** NUEVO: Obtener variables de sesión ***
// Asumimos que mainFunctions_conexion.php ya ha iniciado la sesión (session_start())
$codigoPerfil = $_SESSION['codigo_perfil'] ?? null;
$codigoPersonal = trim($_SESSION['codigo_personal'] ?? '');


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

// *** NUEVO: Validar perfil de usuario ***
if ($codigoPerfil === null) {
     echo json_encode(["error" => "Sesión no válida o perfil no encontrado."]);
     exit;
}


try {
    if ($errorDbConexion) { throw new Exception("Error de conexión BD."); }

    // 3. *** MODIFICADO: Consulta SQL según el perfil del usuario ***
    $query_asignaturas = "";
    $bindPersonal = false; // Flag para saber si vincular :codigoPersonal

    if ($codigoPerfil == '06') { // Perfil Docente
        // Asume que 'carga_docente' tiene los campos:
        // codigo_asignatura, codigo_bachillerato, codigo_grado, codigo_ann_lectivo, codigo_docente
        $query_asignaturas = "SELECT DISTINCT cd.codigo_asignatura as codigo, asig.nombre as nombre, asig.nombre as descripcion, asig.ordenar
                             FROM carga_docente cd
                             INNER JOIN asignatura asig ON cd.codigo_asignatura = asig.codigo
                             WHERE cd.codigo_bachillerato = :modalidad
                               AND cd.codigo_grado = :grado
                               AND cd.codigo_ann_lectivo = :annlectivo
                               AND cd.codigo_docente = :codigoPersonal
                             ORDER BY asig.ordenar";
        $bindPersonal = true; // Necesitamos vincular el código del docente

    } else if ($codigoPerfil == '01' || $codigoPerfil == '04' || $codigoPerfil == '05') { // Admin o Registro
        // Consulta original para ver todas las asignaturas de la estructura
        $query_asignaturas = "SELECT DISTINCT aaa.codigo_asignatura as codigo, asig.nombre as nombre, asig.nombre as descripcion, asig.ordenar
                             FROM a_a_a_bach_o_ciclo aaa
                             INNER JOIN asignatura asig ON aaa.codigo_asignatura = asig.codigo
                             WHERE aaa.codigo_bach_o_ciclo = :modalidad
                               AND aaa.codigo_grado = :grado
                               AND aaa.codigo_ann_lectivo = :annlectivo
                             ORDER BY asig.ordenar";
    } else {
        // Otro perfil no tiene acceso
        throw new Exception("Perfil no autorizado (" . htmlspecialchars($codigoPerfil) . ").");
    }

    // Preparar y ejecutar la consulta
    $stmt = $dblink->prepare($query_asignaturas);
    $stmt->bindParam(':modalidad', $modalidad);
    $stmt->bindParam(':grado', $codigo_grado); // Usar el $codigo_grado determinado
    $stmt->bindParam(':annlectivo', $annlectivo);

    // Vincular :codigoPersonal solo si es necesario (perfil 06)
    if ($bindPersonal) {
        if (empty($codigoPersonal)) {
            throw new Exception("Código de personal no encontrado en la sesión para el perfil docente.");
        }
        $stmt->bindParam(':codigoPersonal', $codigoPersonal);
    }
    
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Devolver resultado
    if ($resultado) {
         // Quitar el campo 'ordenar' de la respuesta JSON final
         $respuesta = array_map(function($item) { unset($item['ordenar']); return $item; }, $resultado);
    } else {
        $respuesta = []; // Devuelve array vacío si no hay resultados
    }
    echo json_encode($respuesta);

} catch (Exception $e) {
    echo json_encode(["error" => "Error al consultar asignaturas: " . $e->getMessage()]);
}
?>