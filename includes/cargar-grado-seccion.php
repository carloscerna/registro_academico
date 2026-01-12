<?php
// includes/cargar-grado-seccion.php
// VERSIÓN HÍBRIDA: Perfiles + Detección de Ocupados

ob_start(); // Iniciar buffer de limpieza
header('Content-Type: application/json; charset=utf-8');

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

$datos = [];

try {
    // Validar sesión y parámetros
    // Aceptamos 'annlectivo' (nuevo) o 'ann' (viejo) para compatibilidad
    $codigoAnnLectivo = $_REQUEST["annlectivo"] ?? ($_REQUEST["ann"] ?? null);
    $codigoBachillerato = $_REQUEST["modalidad"] ?? ($_REQUEST["elegido"] ?? null);
    
    // Variables de sesión
    $codigoPerfil = $_SESSION['codigo_perfil'] ?? '';
    $codigoPersonal = trim($_SESSION['codigo_personal'] ?? '');

    if (!$codigoAnnLectivo || !$codigoBachillerato) {
        throw new Exception("Faltan parámetros");
    }

    // =================================================================================
    // CASO 1: DOCENTE (06) - Solo ve sus grados asignados
    // =================================================================================
    if ($codigoPerfil == '06') {
        $query = "SELECT DISTINCT cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno,
                    grd.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
                  FROM carga_docente cd
                  INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
                  INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
                  INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
                  WHERE cd.codigo_bachillerato = :codigoBachillerato 
                  AND cd.codigo_ann_lectivo = :codigoAnnLectivo
                  AND cd.codigo_docente = :codigoPersonal
                  ORDER BY cd.codigo_grado, cd.codigo_seccion";

        $stmt = $dblink->prepare($query);
        $stmt->bindParam(':codigoBachillerato', $codigoBachillerato);
        $stmt->bindParam(':codigoAnnLectivo', $codigoAnnLectivo);
        $stmt->bindParam(':codigoPersonal', $codigoPersonal);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $descripcion = $row['nombre_grado'] . ' - ' . $row['nombre_seccion'] . ' - ' . $row['nombre_turno'];
            
            // UTF-8 Fix
            if (!mb_check_encoding($descripcion, 'UTF-8')) {
                $descripcion = mb_convert_encoding($descripcion, 'UTF-8', 'ISO-8859-1');
            }

            $datos[] = [
                "codigo" => $row['codigo_grado'] . $row['codigo_seccion'] . $row['codigo_turno'],
                "descripcion" => $descripcion,
                "ocupado" => false, // Para el docente, nada aparece bloqueado visualmente
                "encargado" => ""
            ];
        }
    } 
    // =================================================================================
    // CASO 2: ADMIN / REGISTRO (01, 04, 05) - Ven todo y detectan ocupados
    // =================================================================================
    elseif ($codigoPerfil == '04' || $codigoPerfil == '05' || $codigoPerfil == '01') { 
        
        // Aquí aplicamos la lógica "Super Detalle" con la subconsulta
        $query = "SELECT 
                    orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_turno,
                    grd.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
                    (
                        SELECT btrim(p.nombres || ' ' || p.apellidos)
                        FROM encargado_grado eg
                        INNER JOIN personal p ON eg.codigo_docente = p.id_personal
                        WHERE eg.codigo_ann_lectivo = orgs.codigo_ann_lectivo
                          AND eg.codigo_bachillerato = orgs.codigo_bachillerato
                          AND eg.codigo_grado = orgs.codigo_grado
                          AND eg.codigo_seccion = orgs.codigo_seccion
                          AND eg.codigo_turno = orgs.codigo_turno
                          AND eg.encargado = '1'
                        LIMIT 1
                    ) as docente_asignado
                  FROM organizacion_grados_secciones orgs
                  INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
                  INNER JOIN grado_ano grd ON grd.codigo = orgs.codigo_grado
                  INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
                  WHERE orgs.codigo_bachillerato = :codigoBachillerato 
                  AND orgs.codigo_ann_lectivo = :codigoAnnLectivo
                  ORDER BY orgs.codigo_grado, orgs.codigo_seccion";

        $stmt = $dblink->prepare($query);
        $stmt->bindParam(':codigoBachillerato', $codigoBachillerato);
        $stmt->bindParam(':codigoAnnLectivo', $codigoAnnLectivo);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $descripcion = $row['nombre_grado'] . ' - ' . $row['nombre_seccion'] . ' - ' . $row['nombre_turno'];
            $nombreEncargado = $row['docente_asignado'] ?? '';

            // UTF-8 Fix
            if (!mb_check_encoding($descripcion, 'UTF-8')) {
                $descripcion = mb_convert_encoding($descripcion, 'UTF-8', 'ISO-8859-1');
            }
            if (!empty($nombreEncargado) && !mb_check_encoding($nombreEncargado, 'UTF-8')) {
                $nombreEncargado = mb_convert_encoding($nombreEncargado, 'UTF-8', 'ISO-8859-1');
            }

            // Construir respuesta inteligente
            $datos[] = [
                "codigo" => $row['codigo_grado'] . $row['codigo_seccion'] . $row['codigo_turno'],
                "descripcion" => $descripcion,
                "ocupado" => !empty($nombreEncargado), // TRUE si ya tiene encargado
                "encargado" => $nombreEncargado
            ];
        }
    }
    else {
        // Perfil no autorizado
        // No devolvemos error fatal para no romper el JS, solo array vacío
    }

} catch (Exception $e) {
    // Si falla algo crítico, devolver array vacío para evitar alertas feas en JS
}

ob_end_clean(); // Limpiar basura
echo json_encode($datos);
?>