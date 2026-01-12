<?php
// includes/cargar-asignatura.php
// VERSIÓN MAESTRA: Perfiles + Detección de Ocupados + PHP 8.3

ob_start();
header('Content-Type: application/json; charset=utf-8');

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$datos = [];

try {
    // 1. Validar Sesión
    $codigoPerfil = $_SESSION['codigo_perfil'] ?? null;
    $codigoPersonal = trim($_SESSION['codigo_personal'] ?? '');

    if (!$codigoPerfil) {
        throw new Exception("Sesión no válida.");
    }

    if ($errorDbConexion) { throw new Exception("Error de conexión BD."); }

    // 2. Recibir Parámetros
    // Soportamos nombres nuevos (JS nuevo) y viejos para compatibilidad
    $modalidad = $_REQUEST["modalidad"] ?? null;
    $annlectivo = $_REQUEST["annlectivo"] ?? ($_REQUEST["ann"] ?? null);
    
    // Obtener el código GST (Grado Sección Turno)
    // El JS nuevo envía 'codigo_gst', el archivo viejo esperaba 'codigo_grado_seccion_turno'
    $codigo_gst = $_REQUEST["codigo_gst"] ?? ($_REQUEST["codigo_grado_seccion_turno"] ?? ($_REQUEST["elegido"] ?? ''));

    if (!$modalidad || !$annlectivo || empty($codigo_gst)) {
        // Retornamos vacío en lugar de error fatal para no romper el JS con alertas feas
        echo json_encode([]); 
        exit;
    }

    // 3. Desglosar GST (Grado, Sección, Turno)
    // Necesitamos los 3 para saber si la materia está ocupada en ESA sección específica
    $grado = ''; $seccion = ''; $turno = '';
    
    if (strlen($codigo_gst) >= 6) {
        $grado = substr($codigo_gst, 0, 2);
        $seccion = substr($codigo_gst, 2, 2);
        $turno = substr($codigo_gst, 4, 2);
    } else {
        // Si viene solo el grado (modo antiguo), solo tomamos los primeros 2
        $grado = substr($codigo_gst, 0, 2);
    }

    // =================================================================================
    // CASO 1: DOCENTE (06) - Solo ve LO SUYO
    // =================================================================================
    if ($codigoPerfil == '06') {
        // Consulta original optimizada
        $query = "SELECT DISTINCT cd.codigo_asignatura as codigo, asig.nombre as nombre
                  FROM carga_docente cd
                  INNER JOIN asignatura asig ON cd.codigo_asignatura = asig.codigo
                  WHERE cd.codigo_bachillerato = :modalidad
                    AND cd.codigo_grado = :grado
                    AND cd.codigo_ann_lectivo = :annlectivo
                    AND cd.codigo_docente = :codigoPersonal
                  ORDER BY asig.nombre"; // O asig.ordenar si existe

        $stmt = $dblink->prepare($query);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':grado', $grado);
        $stmt->bindParam(':annlectivo', $annlectivo);
        $stmt->bindParam(':codigoPersonal', $codigoPersonal);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $descripcion = trim($row['nombre']);
            
            // UTF-8 Fix
            if (!mb_check_encoding($descripcion, 'UTF-8')) {
                $descripcion = mb_convert_encoding($descripcion, 'UTF-8', 'ISO-8859-1');
            }

            $datos[] = [
                "codigo" => $row['codigo'],
                "descripcion" => $descripcion,
                "ocupado" => false, // Para el docente, visualmente no bloqueamos nada (es su carga)
                "encargado" => ""
            ];
        }
    }
    // =================================================================================
    // CASO 2: ADMIN / REGISTRO (01, 04, 05) - Ven TODO y detectan OCUPADOS
    // =================================================================================
    elseif ($codigoPerfil == '01' || $codigoPerfil == '04' || $codigoPerfil == '05') {
        
        // Consulta Maestra: Plan de Estudios + Subconsulta "Espía"
        $query = "SELECT 
                    aaa.codigo_asignatura as codigo, 
                    asig.nombre as nombre,
                    (
                        SELECT btrim(p.nombres || ' ' || p.apellidos)
                        FROM carga_docente cd
                        INNER JOIN personal p ON cd.codigo_docente = p.id_personal
                        WHERE cd.codigo_ann_lectivo = :annlectivo
                          AND cd.codigo_bachillerato = :modalidad
                          AND cd.codigo_grado = :grado
                          AND cd.codigo_seccion = :seccion  -- Validación estricta
                          AND cd.codigo_turno = :turno      -- Validación estricta
                          AND cd.codigo_asignatura = aaa.codigo_asignatura
                        LIMIT 1
                    ) as docente_asignado
                  FROM a_a_a_bach_o_ciclo aaa
                  INNER JOIN asignatura asig ON aaa.codigo_asignatura = asig.codigo
                  WHERE aaa.codigo_bach_o_ciclo = :modalidad
                    AND aaa.codigo_grado = :grado
                    AND aaa.codigo_ann_lectivo = :annlectivo
                  ORDER BY asig.ordenar, asig.nombre";

        $stmt = $dblink->prepare($query);
        $stmt->bindParam(':modalidad', $modalidad);
        $stmt->bindParam(':grado', $grado);
        $stmt->bindParam(':annlectivo', $annlectivo);
        // Parametros para la subconsulta (Solo si tenemos GST completo)
        $stmt->bindParam(':seccion', $seccion);
        $stmt->bindParam(':turno', $turno);
        
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $descripcion = trim($row['nombre']);
            $encargado = $row['docente_asignado'] ?? '';

            // UTF-8 Fix para Nombre Asignatura
            if (!mb_check_encoding($descripcion, 'UTF-8')) {
                $descripcion = mb_convert_encoding($descripcion, 'UTF-8', 'ISO-8859-1');
            }
            // UTF-8 Fix para Nombre Docente
            if (!empty($encargado) && !mb_check_encoding($encargado, 'UTF-8')) {
                $encargado = mb_convert_encoding($encargado, 'UTF-8', 'ISO-8859-1');
            }

            $datos[] = [
                "codigo" => $row['codigo'],
                "descripcion" => $descripcion,
                "ocupado" => !empty($encargado), // TRUE si hay profe asignado
                "encargado" => $encargado
            ];
        }
    }

} catch (Exception $e) {
    // Si hay error, devolvemos array vacío para no romper el JS
    // (Opcional: logging de error en servidor)
}

ob_end_clean();
echo json_encode($datos);
?>