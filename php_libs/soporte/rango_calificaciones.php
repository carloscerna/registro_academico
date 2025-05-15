<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
header('Content-Type: application/json');

$ann = $_POST['annLectivo'] ?? '';
$mod = $_POST['modalidad'] ?? '';
$gradoseccion = $_POST['gradoSeccion'] ?? '';
$periodo = $_POST['periodo'] ?? '';

$pdo = $dblink;

// Separar grado, sección y turno (asumiendo 4 caracteres)
$codigo_grado = substr($gradoseccion, 0, 2);
$codigo_seccion = substr($gradoseccion, 2, 2);
$codigo_all = $mod . $codigo_grado . $codigo_seccion . $ann;

// Selección del campo de nota según el periodo
switch ($periodo) {
    case '1':
        $campo_periodo = 'nota_p_p_1';
        break;
    case '2':
        $campo_periodo = 'nota_p_p_2';
        break;
    case '3':
        $campo_periodo = 'nota_p_p_3';
        break;
    case '4':
        $campo_periodo = 'nota_p_p_4';
        break;
    case '5':
        $campo_periodo = 'nota_p_p_5';
        break;
    default:
        echo json_encode(['error' => 'Periodo no válido: ' . $periodo]);
        exit;
}

// Debug: puedes dejar esto activo momentáneamente
// echo json_encode(['debug' => compact('ann', 'mod', 'gradoseccion', 'periodo', 'codigo_all', 'campo_periodo')]);
// exit;

try {
    $sql = "
        SELECT asig.nombre AS nombre_asignatura,
            SUM(CASE WHEN n.$campo_periodo < 5 THEN 1 ELSE 0 END) AS menor_5,
            SUM(CASE WHEN n.$campo_periodo >= 5 AND n.$campo_periodo < 7 THEN 1 ELSE 0 END) AS entre_5_7,
            SUM(CASE WHEN n.$campo_periodo >= 7 THEN 1 ELSE 0 END) AS mayor_7
        FROM alumno_matricula am
        INNER JOIN nota n ON n.codigo_matricula = am.id_alumno_matricula
        INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
        WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = :codigo_all
          AND am.retirado = 'f'
        GROUP BY asig.ordenar, asig.nombre
        ORDER BY asig.ordenar
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['codigo_all' => $codigo_all]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($datos)) {
        echo json_encode([
            'error' => 'No se encontraron resultados',
            'query' => $sql,
            'codigo_all' => $codigo_all,
            'campo_periodo' => $campo_periodo
        ]);
    } else {
        echo json_encode($datos);
    }

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error en la consulta',
        'message' => $e->getMessage(),
        'query' => $sql,
        'parametros' => ['codigo_all' => $codigo_all]
    ]);
}
