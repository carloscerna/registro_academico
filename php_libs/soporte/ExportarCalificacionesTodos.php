<?php
// Ruta de los archivos con su carpeta correspondiente
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Archivos requeridos del sistema
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/funciones_2.php");
include($path_root."/registro_academico/includes/consultas.php");
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");  
include($path_root."/registro_academico/php_libs/fpdf/fpdf.php"); 

header("Content-Type: text/html; charset='UTF-8'");     
date_default_timezone_set('America/El_Salvador');  
setlocale(LC_TIME, 'spanish');

// Variables de control y peticiones $_REQUEST
$db_link = $dblink;
$respuestaOK = false;
$mensajeError = "";
$contenidoOK = "";
$observaciones = "";
$todasLasAsignaturas = $_REQUEST["TodasLasAsignaturas"] ?? "no";
$Exportar = json_decode($_REQUEST["Exportar"]);

$NombreAsignatura = $Exportar->NombreAsignatura;
$NombreGrado = $Exportar->NombreGST;
$nombre_annlectivo = $Exportar->NombreAnnLectivo;
$nombre_modalidad = $Exportar->NombreNivel;

$NombreGrado = explode("-", $NombreGrado);
$nombre_grado = trim($NombreGrado[0]);

if($nombre_grado == "Segundo grado" || $nombre_grado == "Tercer grado"){
    $nombre_grado = trim($NombreGrado[0]) . " " . trim($NombreGrado[1]);
}

$codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"], 0, 4) . $_REQUEST["lstannlectivo"];
$codigoModalidadGradoAnnLectivo = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"], 0, 2) . $_REQUEST["lstannlectivo"];
$periodo = $_REQUEST["lstperiodo"];
//$codigo_asignatura = substr($_REQUEST["lstasignatura"], 0, 3);
// En PHP: Procesar si lstasignatura es array o cadena
$lstAsignaturaInput = $_REQUEST["lstasignatura"] ?? null;

$fecha = $_REQUEST["txtfecha"];

// Carga de PhpSpreadsheet mediante Composer Autoload
require $path_root."/registro_academico/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$codigo_bachillerato = substr($codigo_all, 0, 2);
$codigo_modalidad = substr($codigo_all, 0, 2);
$codigo_grado = substr($codigo_all, 2, 2);
$codigo_seccion = substr($codigo_all, 4, 2);
$codigo_annlectivo = substr($codigo_all, 6, 2);

// Definición de la columna de nota según la modalidad y periodo
switch ($codigo_modalidad) {
    case ($codigo_modalidad >= '03' and $codigo_modalidad <= '05'):
        $nota_p_p = "nota_p_p_" . substr($periodo, -1);       
        break;
    case ($codigo_modalidad >= '06' and $codigo_modalidad <= '09'):
        $nota_p_p = "nota_p_p_" . substr($periodo, -1);
        break;
    case ($codigo_modalidad >= '10' and $codigo_modalidad <= '12'):
        $nota_p_p = "nota_p_p_" . substr($periodo, -1);
        break;
    case ($codigo_modalidad >= '13' and $codigo_modalidad <= '14'):
        $nota_p_p = ($periodo == "Alertas") ? "alertas" : "indicador_p_p_" . substr($periodo, -1);
        break;
    case ($codigo_modalidad == '16'):
        $nota_p_p = "indicador_p_p_" . substr($periodo, -1);
        break;
    case ($codigo_modalidad == '15' || $codigo_modalidad == '21' || $codigo_modalidad == '22' || $codigo_modalidad == '17' || $codigo_modalidad == '18'):
        $nota_p_p = "nota_p_p_" . substr($periodo, -1);
        break;
    default:
        $nota_p_p = "nota_p_p_1";
}

// Inicializar el lector de plantillas Excel
$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
$origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";

// INSTANCIA 1: Libro Académico General
$objPHPExcel = $objReader->load($origen."Formato - Importar Notas SIGES.xlsx");
$objPHPExcel->setActiveSheetIndex(0);
$sheetAcademico = $objPHPExcel->getActiveSheet();

// INSTANCIA 2: Libro Técnico/Modular (Solo si la modalidad es 15)
$objPHPExcelModulos = null;
$sheetModulos = null;
if ($codigo_modalidad == '15') {
    $objPHPExcelModulos = $objReader->load($origen."Formato - Importar Notas SIGES.xlsx");
    $objPHPExcelModulos->setActiveSheetIndex(0);
    $sheetModulos = $objPHPExcelModulos->getActiveSheet();
}

// =========================================================================
//  OPCIÓN 1: POR MATERIAS (FORMATO COMPLETO / CONSOLIDADO)
// =========================================================================
if ($todasLasAsignaturas == "yes") {
    $query_todas = "SELECT 
        a.codigo_nie, 
        TRIM(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) AS apellido_alumno,
        a.nombre_completo,
        TRIM(a.apellido_paterno || ' ' || a.apellido_materno) AS apellidos_alumno,
        a.fecha_nacimiento,
        am.codigo_bach_o_ciclo AS codigo_bachillerato,
        bach.nombre AS nombre_bachillerato,
        am.codigo_ann_lectivo, 
        ann.nombre AS nombre_ann_lectivo,
        am.codigo_grado,  
        gan.nombre AS nombre_grado,
        am.codigo_seccion,
        sec.nombre AS nombre_seccion,
        am.retirado,
        asig.codigo_area, 
        asig.nombre AS nombre_asignatura,
        asig.codigo_cc,
        n.codigo_asignatura,
        n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5,
        n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final
        FROM alumno a
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' 
        INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
        INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
        INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
        INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
        INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
        INNER JOIN a_a_a_bach_o_ciclo aaa 
            ON aaa.codigo_asignatura = n.codigo_asignatura 
            AND aaa.codigo_ann_lectivo = '$codigo_annlectivo' 
            AND aaa.codigo_bach_o_ciclo = '$codigo_bachillerato' 
            AND aaa.codigo_grado = '$codigo_grado'
        WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all'
        ORDER BY apellido_alumno, n.orden_siges ASC";

    $result_asignatura = $db_link->query($query_todas);
    $datos = $result_asignatura->fetchAll(PDO::FETCH_ASSOC);

    $asignaturas_academicas = [];
    $asignaturas_modulares = [];
    $nombreSeccion = "";

    foreach ($datos as $fila) {
        $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
        $codigo_area_actual = trim($fila['codigo_area']);
        $nombre_asig = $fila['nombre_asignatura'];
        $nombreSeccion = trim($fila['nombre_seccion']);

        if ($codigo_bachillerato_actual === '15' && $codigo_area_actual === '03') {
            if (!in_array($nombre_asig, $asignaturas_modulares)) {
                $asignaturas_modulares[] = $nombre_asig;
            }
        } else {
            if (!in_array($nombre_asig, $asignaturas_academicas)) {
                $asignaturas_academicas[] = $nombre_asig;
            }
        }
    }

    // Encabezados Opción Por Materias
    $sheetAcademico->setCellValue('A1', 'Estudiante');
    $sheetAcademico->setCellValue('B1', 'Nombre');
    $col = 'C';
    foreach ($asignaturas_academicas as $asig) {
        $sheetAcademico->setCellValue($col . '1', mb_strtoupper($asig, 'UTF-8'));
        $col++;
    }

    if ($codigo_modalidad == '15' && $sheetModulos !== null) {
        $sheetModulos->setCellValue('A1', 'Estudiante');
        $sheetModulos->setCellValue('B1', 'Nombre');
        $col = 'C';
        foreach ($asignaturas_modulares as $asig) {
            $sheetModulos->setCellValue($col . '1', mb_strtoupper($asig, 'UTF-8'));
            $col++;
        }
    }

    $datos_agrupados_academicos = [];
    $datos_agrupados_modulares = [];

    foreach ($datos as $fila) {
        $nie = trim($fila['codigo_nie']);
        $nombre_completo = trim($fila['apellido_alumno']);
        $asignatura = $fila['nombre_asignatura'];
        $nota = $fila[$nota_p_p];
        $codigo_cc = trim($fila['codigo_cc']);
        $indicador = $fila['indicador_p_p_1'];
        $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
        $codigo_area_actual = trim($fila['codigo_area']);

        $nota_formateada = calcularNotaFormateada($codigo_cc, $nota, $indicador);

        if ($codigo_bachillerato_actual === '15' && $codigo_area_actual === '03') {
            if (!isset($datos_agrupados_modulares[$nie])) {
                $datos_agrupados_modulares[$nie] = ['nombre' => $nombre_completo];
            }
            $datos_agrupados_modulares[$nie][$asignatura] = $nota_formateada;
        } else {
            if (!isset($datos_agrupados_academicos[$nie])) {
                $datos_agrupados_academicos[$nie] = ['nombre' => $nombre_completo];
            }
            $datos_agrupados_academicos[$nie][$asignatura] = $nota_formateada;
        }
    }

    // Llenar Filas Académicas
    $fila_num = 2;
    foreach ($datos_agrupados_academicos as $nie => $datosAlumno) {
        $sheetAcademico->setCellValue("A$fila_num", $nie);
        $sheetAcademico->setCellValue("B$fila_num", $datosAlumno['nombre']);
        
        foreach ($asignaturas_academicas as $index => $asig) {
            $columna = obtenerLetraColumna($index + 2); 
            $sheetAcademico->setCellValue("$columna$fila_num", $datosAlumno[$asig] ?? "");
        }
        $fila_num++;
    }

    // Llenar Filas Modulares
    if ($codigo_modalidad == '15' && !empty($datos_agrupados_modulares) && $sheetModulos !== null) {
        $fila_num_mod = 2;
        foreach ($datos_agrupados_modulares as $nie => $datosAlumno) {
            $sheetModulos->setCellValue("A$fila_num_mod", $nie);
            $sheetModulos->setCellValue("B$fila_num_mod", $datosAlumno['nombre']);
            
            foreach ($asignaturas_modulares as $index => $asig) {
                $columna = obtenerLetraColumna($index + 2);
                $sheetModulos->setCellValue("$columna$fila_num_mod", $datosAlumno[$asig] ?? "");
            }
            $fila_num_mod++;
        }
    }

} else {
    // =========================================================================
    //  OPCIÓN 2: POR MATERIA (MÉTODO TRADICIONAL / INDIVIDUAL)
    //  A1 = Código NIE | B1..N = Asignaturas | A2..N = NIEs | B2..N = Notas
    // =========================================================================
if (is_array($lstAsignaturaInput)) {
    // Extraer los códigos de 3 dígitos de cada elemento seleccionado
    $codigos_materia = array_map(function($item) {
        return substr($item, 0, 3);
    }, $lstAsignaturaInput);
    
    // Crear condición SQL IN ('001', '002', ...)
    $listaCodigosSQL = "'" . implode("','", $codigos_materia) . "'";
    $where_asignatura = " AND n.codigo_asignatura IN ($listaCodigosSQL) ";
} else {
    $codigo_asignatura = substr($lstAsignaturaInput, 0, 3);
    $where_asignatura = ($codigo_asignatura !== "00" && !empty($codigo_asignatura)) 
        ? " AND n.codigo_asignatura = '$codigo_asignatura' " 
        : "";
}

    $query_materia = "SELECT 
        a.codigo_nie, 
        TRIM(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) AS apellido_alumno,
        am.codigo_bach_o_ciclo AS codigo_bachillerato,
        asig.codigo_area, 
        asig.nombre AS nombre_asignatura,
        asig.codigo_cc,
        sec.nombre AS nombre_seccion,
        n.codigo_asignatura,
        n.$nota_p_p AS nota_eval,
        n.indicador_p_p_1
        FROM alumno a
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' 
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
        INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
        INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
        WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all'
        $where_asignatura
        ORDER BY apellido_alumno, n.orden_siges ASC";

    $result_materia = $db_link->query($query_materia);
    $datos = $result_materia->fetchAll(PDO::FETCH_ASSOC);

    $asignaturas_academicas = [];
    $asignaturas_modulares = [];
    $nombreSeccion = "";

    foreach ($datos as $fila) {
        $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
        $codigo_area_actual = trim($fila['codigo_area']);
        $nombre_asig = $fila['nombre_asignatura'];
        $nombreSeccion = trim($fila['nombre_seccion']);

        if ($codigo_bachillerato_actual === '15' && $codigo_area_actual === '03') {
            if (!in_array($nombre_asig, $asignaturas_modulares)) {
                $asignaturas_modulares[] = $nombre_asig;
            }
        } else {
            if (!in_array($nombre_asig, $asignaturas_academicas)) {
                $asignaturas_academicas[] = $nombre_asig;
            }
        }
    }

    // Encabezados Opción Por Materia (Formato antiguo)
    $sheetAcademico->setCellValue('A1', 'Código NIE');
    $col = 'B';
    foreach ($asignaturas_academicas as $asig) {
        $sheetAcademico->setCellValue($col . '1', mb_strtoupper($asig, 'UTF-8'));
        $col++;
    }

    if ($codigo_modalidad == '15' && $sheetModulos !== null) {
        $sheetModulos->setCellValue('A1', 'Código NIE');
        $col = 'B';
        foreach ($asignaturas_modulares as $asig) {
            $sheetModulos->setCellValue($col . '1', mb_strtoupper($asig, 'UTF-8'));
            $col++;
        }
    }

    $datos_agrupados_academicos = [];
    $datos_agrupados_modulares = [];

    foreach ($datos as $fila) {
        $nie = trim($fila['codigo_nie']);
        $asignatura = $fila['nombre_asignatura'];
        $nota = $fila['nota_eval'];
        $codigo_cc = trim($fila['codigo_cc']);
        $indicador = $fila['indicador_p_p_1'];
        $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
        $codigo_area_actual = trim($fila['codigo_area']);

        $nota_formateada = calcularNotaFormateada($codigo_cc, $nota, $indicador);

        if ($codigo_bachillerato_actual === '15' && $codigo_area_actual === '03') {
            if (!isset($datos_agrupados_modulares[$nie])) {
                $datos_agrupados_modulares[$nie] = [];
            }
            $datos_agrupados_modulares[$nie][$asignatura] = $nota_formateada;
        } else {
            if (!isset($datos_agrupados_academicos[$nie])) {
                $datos_agrupados_academicos[$nie] = [];
            }
            $datos_agrupados_academicos[$nie][$asignatura] = $nota_formateada;
        }
    }

    // Llenar Filas Modo Por Materia (A=NIE, B en adelante=Calificaciones)
    $fila_num = 2;
    foreach ($datos_agrupados_academicos as $nie => $notasAlumno) {
        $sheetAcademico->setCellValue("A$fila_num", $nie);
        
        foreach ($asignaturas_academicas as $index => $asig) {
            $columna = obtenerLetraColumna($index + 1); // Indice 1 empieza en B
            $sheetAcademico->setCellValue("$columna$fila_num", $notasAlumno[$asig] ?? "");
        }
        $fila_num++;
    }

    if ($codigo_modalidad == '15' && !empty($datos_agrupados_modulares) && $sheetModulos !== null) {
        $fila_num_mod = 2;
        foreach ($datos_agrupados_modulares as $nie => $notasAlumno) {
            $sheetModulos->setCellValue("A$fila_num_mod", $nie);
            
            foreach ($asignaturas_modulares as $index => $asig) {
                $columna = obtenerLetraColumna($index + 1);
                $sheetModulos->setCellValue("$columna$fila_num_mod", $notasAlumno[$asig] ?? "");
            }
            $fila_num_mod++;
        }
    }
}

// Autoajustar columnas
foreach ($sheetAcademico->getColumnIterator() as $column) {
    $sheetAcademico->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}
if ($sheetModulos !== null) {
    foreach ($sheetModulos->getColumnIterator() as $column) {
        $sheetModulos->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
    }
}

// Guardar y generar la respuesta JSON
NombreArchivoExcelDoble($objPHPExcel, $objPHPExcelModulos, $nombreSeccion, $datos_agrupados_academicos, $datos_agrupados_modulares, $todasLasAsignaturas);

echo json_encode($salidaJson);

// =========================================================================
//                  FUNCIONES AUXILIARES
// =========================================================================

function calcularNotaFormateada($codigo_cc, $nota, $indicador) {
    if ($codigo_cc === "02") {
        if (is_null($nota) || $nota <= 0 || $nota == "") return "B";
        if ($nota >= 9) return "E";
        if ($nota >= 7) return "MB";
        return "B";
    } elseif ($codigo_cc === "03") {
        return $indicador;
    } elseif ($codigo_cc === "01" || $codigo_cc === "04") {
        return (is_null($nota) || $nota <= 0) ? "1" : $nota;
    }
    return $nota;
}

function obtenerLetraColumna($index) {
    $letra = "";
    while ($index >= 0) {
        $letra = chr(($index % 26) + 65) . $letra;
        $index = floor($index / 26) - 1;
    }
    return $letra;
}

function NombreArchivoExcelDoble($objPHPExcel, $objPHPExcelModulos, $nombreSeccion, $datos_academicos, $datos_modulares, $todasLasAsignaturas = "no") {
    global $codigo_bachillerato, $nombre_annlectivo, $path_root, $nombre_modalidad, $nombre_grado, $periodo, $DestinoArchivo, $salidaJson;
    
    $codigo_destino = 3; 
    $conteo = 1;

    // 1. Mapeo del Periodo
    $mapaPeriodos = [
        "1" => "P1", "2" => "P2", "3" => "P3", "4" => "P4", "5" => "P5",
        "Periodo 1" => "P1", "Periodo 2" => "P2", "Periodo 3" => "P3", "Periodo 4" => "P4", "Periodo 5" => "P5",
        " Alert" => "Alertas", "Alertas" => "Alertas"
    ];

    $sufijoPeriodo = $mapaPeriodos[$periodo] ?? ("P" . substr(trim($periodo), -1));

    // 2. Definición de la etiqueta según la modalidad seleccionada
    $sufijoModo = ($todasLasAsignaturas === "yes") ? "Por Materias" : "Por Materia";
    
    $contenidoHTML = "
    <div style='overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px;'>
        <table style='width: 100%; border-collapse: collapse; text-align: center; font-family: system-ui, sans-serif; font-size: 14px;'>
            <thead>
                <tr style='background-color: #1e3a8a; color: #ffffff;'>
                    <th style='padding: 12px 15px;'>#</th>
                    <th style='padding: 12px 15px; text-align: left;'>Archivo Generado</th>
                    <th style='padding: 12px 15px;'>Tamaño</th>
                </tr>
            </thead>
            <tbody>";

    try {
        CrearDirectorios($path_root, $nombre_annlectivo, $codigo_bachillerato, $codigo_destino, $periodo);

        if (!file_exists($DestinoArchivo)) {
            mkdir($DestinoArchivo, 0777, true);
        }

        $nombreBase = htmlspecialchars($nombre_grado) . " " . $nombreSeccion . " - " . $nombre_modalidad;
        $nombreBaseClean = str_replace(['/', ':'], '-', $nombreBase);
        $iconoExcel = "<span style='color: #059669; margin-right: 10px;'><i class='fas fa-file-excel'></i></span>";

        // Construcción del nombre para el archivo ACADEMICO
        if (!empty($datos_academicos)) {
            $nombreArchivoAcademico = trim($nombreBaseClean) . " - ACADEMICO - " . $sufijoPeriodo . " - " . $sufijoModo . ".xlsx";
            $rutaAcademica = $DestinoArchivo . "/" . $nombreArchivoAcademico;
            
            $writer = new Xlsx($objPHPExcel);
            $writer->save($rutaAcademica);

            $tamano = round(filesize($rutaAcademica) / 1024, 2);
            $tamanoTexto = $tamano < 1024 ? "{$tamano} KB" : round($tamano / 1024, 2) . " MB";

            $contenidoHTML .= "
                <tr style='background-color: #ffffff; border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 12px 15px;'>{$conteo}</td>
                    <td style='padding: 12px 15px; text-align: left;'>{$iconoExcel}<b>{$nombreArchivoAcademico}</b></td>
                    <td style='padding: 12px 15px;'>{$tamanoTexto}</td>
                </tr>";
            $conteo++;
        }

        // Construcción del nombre para el archivo MODULAR (Si aplica)
        if ($codigo_bachillerato == '15' && $objPHPExcelModulos !== null && !empty($datos_modulares)) {
            $nombreArchivoModular = trim($nombreBaseClean) . " - MODULOS - " . $sufijoPeriodo . " - " . $sufijoModo . ".xlsx";
            $rutaModular = $DestinoArchivo . "/" . $nombreArchivoModular;

            $writerMod = new Xlsx($objPHPExcelModulos);
            $writerMod->save($rutaModular);

            $tamanoMod = round(filesize($rutaModular) / 1024, 2);
            $tamanoTextoMod = $tamanoMod < 1024 ? "{$tamanoMod} KB" : round($tamano / 1024, 2) . " MB";

            $contenidoHTML .= "
                <tr style='background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 12px 15px;'>{$conteo}</td>
                    <td style='padding: 12px 15px; text-align: left;'>{$iconoExcel}<b>{$nombreArchivoModular}</b></td>
                    <td style='padding: 12px 15px;'>{$tamanoTextoMod}</td>
                </tr>";
        }

        $contenidoHTML .= "</tbody></table></div>";

        $salidaJson = [
            "respuesta" => true,
            "mensaje" => "¡Se han exportado los archivos exitosamente!",
            "contenido" => $contenidoHTML
        ];

    } catch (Exception $e) {
        $salidaJson = [
            "respuesta" => false,
            "mensaje" => "Error al generar los archivos: " . $e->getMessage(),
            "contenido" => null
        ];
    }
}