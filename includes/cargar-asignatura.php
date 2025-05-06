<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
try {
    // Verificar si los parámetros existen
    if (!isset($_REQUEST["modalidad"]) || !isset($_REQUEST["annlectivo"]) || !isset($_REQUEST["codigo_grado_seccion_turno"])) {
        echo json_encode(["error" => "Parámetros insuficientes"]);
        exit;
    }

    $codigoBachillerato = $_REQUEST["modalidad"];
    $codigoAnnLectivo = $_REQUEST["annlectivo"];
    $codigoPerfil = $_SESSION['codigo_perfil'];
    $codigoPersonal = $_SESSION['codigo_personal'];
    $codigoGradoSeccionTurno = $_REQUEST["codigo_grado_seccion_turno"];
    $codigoGrado = substr($codigoGradoSeccionTurno,0,2);
    // Determinar la consulta según el perfil
    if ($codigoPerfil == '06') {
        $query = "SELECT DISTINCT 
                        cd.codigo_bachillerato, cd.codigo_ann_lectivo, 
                        cd.codigo_grado || '-' || cd.codigo_seccion || '-' || cd.codigo_turno AS codigo,
                        grd.nombre || ' - ' || tur.nombre AS nombre, 
                        asi.codigo AS codigo_asignatura, asi.nombre AS nombre_asignatura
                  FROM carga_docente cd
                  INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
                  INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
                  INNER JOIN asignatura asi ON asi.codigo = cd.codigo_asignatura AND asi.estatus = '1'
                  WHERE cd.codigo_grado || '-' || cd.codigo_seccion || '-' || cd.codigo_turno = :codigoGradoSeccionTurno
                  AND cd.codigo_bachillerato = :codigoBachillerato
                  AND cd.codigo_ann_lectivo = :codigoAnnLectivo
                  AND cd.codigo_docente = :codigoPersonal
                  ORDER BY asi.codigo";
    } elseif (in_array($codigoPerfil, ['04', '05', '01'])) { // Registro Académico Básica y Media
        $query = "SELECT DISTINCT ON (aaa.codigo_asignatura) 
                        aaa.codigo_asignatura as codigo, 
                        aaa.codigo_grado || '-' || aaa.codigo_sirai AS codigo_sirai,
                        asi.nombre AS nombre
                  FROM a_a_a_bach_o_ciclo aaa
                  INNER JOIN asignatura asi ON asi.codigo = aaa.codigo_asignatura
                  WHERE aaa.codigo_bach_o_ciclo = :codigoBachillerato
                  AND aaa.codigo_ann_lectivo = :codigoAnnLectivo
                  AND aaa.codigo_grado = :codigoGrado
                  ORDER BY aaa.codigo_asignatura";
    } else {
        echo json_encode(["error" => "Perfil no autorizado"]);
        exit;
    }

    // Preparar la consulta con parámetros seguros
    $stmt = $dblink->prepare($query);
    $stmt->bindParam(':codigoBachillerato', $codigoBachillerato, PDO::PARAM_INT);
    $stmt->bindParam(':codigoAnnLectivo', $codigoAnnLectivo, PDO::PARAM_INT);
    
    if ($codigoPerfil == '06') {
        $stmt->bindParam(':codigoGradoSeccionTurno', $codigoGradoSeccionTurno, PDO::PARAM_STR);
        $stmt->bindParam(':codigoPersonal', $codigoPersonal, PDO::PARAM_INT);
    } elseif (in_array($codigoPerfil, ['04', '05', '01'])) {
        $stmt->bindParam(':codigoGrado',$codigoGrado, PDO::PARAM_INT);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar respuesta en formato JSON
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener los datos: " . $e->getMessage()]);
}