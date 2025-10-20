<?php
// <-- VERSIÓN REFACTORIZADA Y SEGURA: CrearNominas.php -->

// Set timezone
date_default_timezone_set('America/El_Salvador');
header('Content-Type: application/json; charset=utf-8'); // Output JSON

// --- INCLUDES ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/funciones_2.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php"; // Needed for $dblink and CrearDirectorios
require_once $path_root . "/registro_academico/vendor/autoload.php"; // PhpSpreadsheet

// Load PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Default JSON response
$respuesta = [
    "respuesta" => false,
    "mensaje" => "Petición inválida.",
    "contenido" => ""
];

try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }

    $codigo_all = $_REQUEST["todos"] ?? null;
    if (!$codigo_all) { throw new Exception("Faltan parámetros (código de grupo)."); }

    // --- 1. OBTENER DATOS DEL ENCABEZADO ---
    $sqlHeader = "SELECT btrim(bach.nombre) as nombre_bachillerato, btrim(gan.nombre) as nombre_grado,
                  btrim(sec.nombre) as nombre_seccion, ann.nombre as nombre_ann_lectivo,
                  bach.codigo as codigo_bachillerato, ann.codigo as codigo_ann_lectivo
                  FROM alumno_matricula am
                  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                  WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all
                  LIMIT 1";
    $stmtHeader = $dblink->prepare($sqlHeader);
    $stmtHeader->bindParam(':codigo_all', $codigo_all);
    $stmtHeader->execute();
    $headerData = $stmtHeader->fetch(PDO::FETCH_ASSOC);

    if (!$headerData) { throw new Exception("No se encontraron datos para el encabezado del grupo."); }

    $print_bachillerato = 'Modalidad: ' . $headerData['nombre_bachillerato'];
    $print_grado = 'Grado: ' . $headerData['nombre_grado'];
    $print_seccion = 'Sección: ' . $headerData['nombre_seccion'];
    $print_ann_lectivo = 'Año Lectivo: ' . $headerData['nombre_ann_lectivo'];
    $nombre_grado = $headerData['nombre_grado'];
    $nombre_seccion = $headerData['nombre_seccion'];
    $codigo_bachillerato = $headerData['codigo_bachillerato'];
    $nombre_ann_lectivo = $headerData['nombre_ann_lectivo'];


    // --- 2. OBTENER DATOS DE ESTUDIANTES Y ENCARGADOS ---
    $sqlData = "SELECT
            a.id_alumno, am.id_alumno_matricula as codigo_matricula, a.codigo_nie,
            btrim(trim(a.apellido_paterno) || ' ' || trim(a.apellido_materno) || ', ' || a.nombre_completo) as apellido_alumno,
            a.apellido_paterno, a.apellido_materno, a.nombre_completo,
            btrim(trim(a.apellido_paterno) || ' ' || trim(a.apellido_materno)) as apellidos_alumno,
            a.genero as genero_estudiante_code,
            CASE WHEN a.genero = '01' THEN 'Masculino' ELSE 'Femenino' END as genero_estudiante,
            a.fecha_nacimiento, AGE(a.fecha_nacimiento) as edad_interval, EXTRACT(YEAR FROM AGE(a.fecha_nacimiento)) as edad,
            a.pn_numero as pn_numero, a.pn_folio as pn_folio,
            a.pn_tomo as pn_tomo, a.pn_libro as pn_libro,
            btrim(gan.nombre) as nombre_grado, btrim(sec.nombre) as nombre_seccion,
            a.telefono_alumno as telefono_alumno, a.direccion_alumno as direccion_alumno,
            btrim(ae.nombres) as nombres_encargado, ae.fecha_nacimiento as encargado_fecha_nacimiento,
            ae.dui as encargado_dui, cf.descripcion as nombre_tipo_parentesco, ae.telefono as telefono_encargado
            FROM alumno a
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
            INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
            INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
            LEFT JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
            LEFT JOIN catalogo_familiar cf ON ae.codigo_familiar = cf.codigo
            WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all
            ORDER BY apellido_alumno";

    $stmtData = $dblink->prepare($sqlData);
    $stmtData->bindParam(':codigo_all', $codigo_all);
    $stmtData->execute();
    $studentData = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    if (empty($studentData)) { throw new Exception("No se encontraron estudiantes para este grupo."); }

    // --- 3. CREAR EL ARCHIVO EXCEL ---
    $origen = $path_root . "/registro_academico/formatos_hoja_de_calculo/";
    $templateFile = $origen . "Formato - Listado - 2023.xlsx";
    if (!file_exists($templateFile)) { throw new Exception("No se encontró el archivo de plantilla Excel: " . $templateFile); }

    $objReader = IOFactory::createReader("Xlsx");
    $objPHPExcel = $objReader->load($templateFile);
    $objPHPExcel->setActiveSheetIndex(0); // Trabajar en la primera hoja

    // Escribir encabezados personalizados
    $objPHPExcel->getActiveSheet()->setCellValue('A1', $print_bachillerato);
    $objPHPExcel->getActiveSheet()->setCellValue('A2', $print_grado);
    $objPHPExcel->getActiveSheet()->setCellValue('C2', $print_seccion);
    $objPHPExcel->getActiveSheet()->setCellValue('D2', $print_ann_lectivo);

    // Llenar datos de estudiantes
    $num = 0;
    $fila_excel = 4; // Fila inicial de datos en la plantilla
    foreach ($studentData as $row) {
        $num++;
        $fila_excel++;

        $nombre_completo_excel = trim(cambiar_de_del_2($row['nombre_completo'] . ' ' . $row['apellidos_alumno']));
        $nombre_completo_promocion = mb_strtoupper(trim($row['nombre_completo']), "UTF-8");
        $nombre_grado_seccion = trim($row['nombre_grado'] . ' ' . $row['nombre_seccion']);

        $objPHPExcel->getActiveSheet()->setCellValue("A" . $fila_excel, $num);
        $objPHPExcel->getActiveSheet()->setCellValue("B" . $fila_excel, $row['id_alumno']);
        $objPHPExcel->getActiveSheet()->setCellValue("C" . $fila_excel, $row['codigo_matricula']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("D" . $fila_excel, $row['codigo_nie'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // Forzar NIE como texto
        $objPHPExcel->getActiveSheet()->setCellValue("E" . $fila_excel, $nombre_completo_excel);
        $objPHPExcel->getActiveSheet()->setCellValue("F" . $fila_excel, $row['genero_estudiante']);
        $objPHPExcel->getActiveSheet()->setCellValue("G" . $fila_excel, cambiaf_a_normal($row['fecha_nacimiento']));
        $objPHPExcel->getActiveSheet()->setCellValue("H" . $fila_excel, $row['edad']);
        $objPHPExcel->getActiveSheet()->setCellValue("I" . $fila_excel, $row['pn_numero']);
        $objPHPExcel->getActiveSheet()->setCellValue("J" . $fila_excel, $row['pn_folio']);
        $objPHPExcel->getActiveSheet()->setCellValue("K" . $fila_excel, $row['pn_tomo']);
        $objPHPExcel->getActiveSheet()->setCellValue("L" . $fila_excel, $row['pn_libro']);
        $objPHPExcel->getActiveSheet()->setCellValue("M" . $fila_excel, $nombre_grado_seccion);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("N" . $fila_excel, $row['telefono_alumno'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue("O" . $fila_excel, trim($row['direccion_alumno']));
        $objPHPExcel->getActiveSheet()->setCellValue("P" . $fila_excel, trim($row['nombres_encargado']));
        $objPHPExcel->getActiveSheet()->setCellValue("Q" . $fila_excel, cambiaf_a_normal($row['encargado_fecha_nacimiento']));
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("R" . $fila_excel, $row['encargado_dui'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue("S" . $fila_excel, trim($row['nombre_tipo_parentesco']));
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("T" . $fila_excel, $row['telefono_encargado'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        // Columnas para cuadro de promoción (originalmente W, X, Y, Z, AA)
        $objPHPExcel->getActiveSheet()->setCellValue("W" . $fila_excel, trim($row['apellido_paterno']));
        $objPHPExcel->getActiveSheet()->setCellValue("X" . $fila_excel, trim($row['apellido_materno']));
        $objPHPExcel->getActiveSheet()->setCellValue("Y" . $fila_excel, $nombre_completo_promocion);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("Z" . $fila_excel, $row['codigo_nie'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->setCellValue("AA" . $fila_excel, mb_strtoupper($row['genero_estudiante_code'],"UTF-8")); // 'm' or 'f'
    }

    // --- 4. GUARDAR EL ARCHIVO ---
    // Asegurarse de que $DestinoArchivo se define correctamente en CrearDirectorios
    $codigo_destino = 1; // 1 = Cuadro de Calificaciones (según tu código original)
    // La función CrearDirectorios debe definir globalmente $DestinoArchivo
    CrearDirectorios($path_root, $nombre_ann_lectivo, $codigo_bachillerato, $codigo_destino, ""); 

    if (empty($DestinoArchivo) || !is_dir($DestinoArchivo)) {
        throw new Exception("El directorio de destino no es válido o no se pudo crear: " . ($DestinoArchivo ?? 'No definido'));
    }

    $nombre_archivo_base = replace_3($codigo_bachillerato . "-". $nombre_grado ."-".$nombre_seccion.".xlsx");
    $rutaCompletaArchivo = $DestinoArchivo . $nombre_archivo_base;

    $objWriter = new Xlsx($objPHPExcel);
    $objWriter->save($rutaCompletaArchivo);

    if (!file_exists($rutaCompletaArchivo)) {
        throw new Exception("No se pudo guardar el archivo Excel en el servidor.");
    }
    chmod($rutaCompletaArchivo, 0777); // Intentar dar permisos (puede fallar dependiendo del servidor)

    $respuesta['respuesta'] = true;
    $respuesta['mensaje'] = "Archivo Excel generado correctamente.";
    $respuesta['contenido'] = "Archivo guardado como: " . basename($rutaCompletaArchivo); // Solo el nombre del archivo

} catch (PDOException $e) {
    $respuesta['mensaje'] = "Error de Base de Datos: " . $e->getMessage();
    // Considera loggear el error completo: error_log($e->getMessage());
} catch (Exception $e) {
    $respuesta['mensaje'] = "Error: " . $e->getMessage();
    // Considera loggear el error completo: error_log($e->getMessage());
}

// Enviar respuesta JSON
echo json_encode($respuesta);
?>