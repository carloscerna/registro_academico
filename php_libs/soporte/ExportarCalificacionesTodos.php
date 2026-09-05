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
$todasLasAsignaturas = $_REQUEST["TodasLasAsignaturas"];
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
$codigo_asignatura = substr($_REQUEST["lstasignatura"], 0, 3);
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
        n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final,
        ROUND((n.nota_p_p_1 + n.nota_p_p_2 + n.nota_p_p_3),1) AS total_puntos_basica,
        ROUND((n.nota_p_p_1 + n.nota_p_p_2 + n.nota_p_p_3 + n.nota_p_p_4),1) AS total_puntos_media
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
        ORDER BY apellido_alumno, n.codigo_asignatura ASC";

    $result_asignatura = $db_link->query($query_todas);
    $datos = $result_asignatura->fetchAll(PDO::FETCH_ASSOC);

    // Listas dinámicas de asignaturas (Columnas de cada archivo)
    $asignaturas_academicas = [];
    $asignaturas_modulares = [];
    $nombreSeccion = "";

    // Paso 1: Clasificar las columnas (encabezados de asignaturas)
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

    // Paso 2: Configurar Encabezados en Hojas de Excel correspondientes
    // Excel Académico
    $sheetAcademico->setCellValue('A1', 'Código NIE');
    $col = 'B';
    foreach ($asignaturas_academicas as $asig) {
        $sheetAcademico->setCellValue($col . '1', mb_strtoupper($asig, 'UTF-8'));
        $col++;
    }

    // Excel Modular (si aplica)
    if ($codigo_modalidad == '15' && $sheetModulos !== null) {
        $sheetModulos->setCellValue('A1', 'Código NIE');
        $col = 'B';
        foreach ($asignaturas_modulares as $asig) {
            $sheetModulos->setCellValue($col . '1', mb_strtoupper($asig, 'UTF-8'));
            $col++;
        }
    }

    // Paso 3: Agrupar calificaciones por alumno para cada archivo
    $datos_agrupados_academicos = [];
    $datos_agrupados_modulares = [];

    foreach ($datos as $fila) {
        $nie = trim($fila['codigo_nie']);
        $asignatura = $fila['nombre_asignatura'];
        $nota = $fila[$nota_p_p];
        $codigo_cc = trim($fila['codigo_cc']);
        $indicador = $fila['indicador_p_p_1'];
        $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
        $codigo_area_actual = trim($fila['codigo_area']);

        // Formatear la calificación según codigo_cc
        $nota_formateada = "";
        if ($codigo_cc === "02") {
            if (is_null($nota) || $nota <= 0 || $nota == "") {
                $nota_formateada = "B";
            } elseif ($nota >= 9) {
                $nota_formateada = "E";
            } elseif ($nota >= 7) {
                $nota_formateada = "MB";
            } else {
                $nota_formateada = "B";
            }
        } elseif ($codigo_cc === "03") {
            $nota_formateada = $indicador;
        } elseif ($codigo_cc === "01" || $codigo_cc === "04") {
            if (is_null($nota) || $nota <= 0) {
                $nota_formateada = "1";
            } else {
                $nota_formateada = $nota;
            }
        }

        // Clasificar y guardar en el arreglo correspondiente de forma separada
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

    // Paso 4: Rellenar Filas de Datos - Académicos
    $fila_num = 2;
    foreach ($datos_agrupados_academicos as $nie => $datosAlumno) {
        $sheetAcademico->setCellValue("A$fila_num", $nie);
        foreach ($asignaturas_academicas as $index => $asig) {
            $columna = obtenerLetraColumna($index + 1); // Función auxiliar robusta de conversión de columnas
            $nota_celda = isset($datosAlumno[$asig]) ? $datosAlumno[$asig] : "";
            $sheetAcademico->setCellValue("$columna$fila_num", $nota_celda);
        }
        $fila_num++;
    }
    foreach ($sheetAcademico->getColumnIterator() as $column) {
        $sheetAcademico->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
    }

    // Paso 5: Rellenar Filas de Datos - Modulares
    if ($codigo_modalidad == '15' && !empty($datos_agrupados_modulares) && $sheetModulos !== null) {
        $fila_num_mod = 2;
        foreach ($datos_agrupados_modulares as $nie => $datosAlumno) {
            $sheetModulos->setCellValue("A$fila_num_mod", $nie);
            foreach ($asignaturas_modulares as $index => $asig) {
                $columna = obtenerLetraColumna($index + 1);
                $nota_celda = isset($datosAlumno[$asig]) ? $datosAlumno[$asig] : "";
                $sheetModulos->setCellValue("$columna$fila_num_mod", $nota_celda);
            }
            $fila_num_mod++;
        }
        foreach ($sheetModulos->getColumnIterator() as $column) {
            $sheetModulos->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
    }

    // Guardar y generar la respuesta JSON
    NombreArchivoExcelDoble($objPHPExcel, $objPHPExcelModulos, $nombreSeccion, $datos_agrupados_academicos, $datos_agrupados_modulares);
}

echo json_encode($salidaJson);

// =========================================================================
//                  FUNCIONES AUXILIARES
// =========================================================================

// Función de conversión segura de índice numérico a letras de columna de Excel (A, B, C... AA, AB...)
function obtenerLetraColumna($index) {
    $letra = "";
    while ($index >= 0) {
        $letra = chr(($index % 26) + 65) . $letra;
        $index = floor($index / 26) - 1;
    }
    return $letra;
}

// Función encargada de guardar ambos archivos de forma física y generar el reporte HTML en JSON (Versión Modernizada e Integrada)
function NombreArchivoExcelDoble($objPHPExcel, $objPHPExcelModulos, $nombreSeccion, $datos_academicos, $datos_modulares) {
    global $codigo_bachillerato, $nombre_annlectivo, $path_root, $nombre_modalidad, $nombre_grado, $periodo, $DestinoArchivo, $salidaJson;
    
    $codigo_destino = 3; 
    $conteo = 1;
    
    // Contenedor principal adaptable con estilos modernos consistentes con la interfaz
    $contenidoHTML = "
    <div style='overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);'>
        <table style='width: 100%; border-collapse: collapse; text-align: center; font-family: system-ui, -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif; font-size: 14px;'>
            <thead>
                <tr style='background-color: #1e3a8a; color: #ffffff;'>
                    <th style='padding: 12px 15px; font-weight: 600; border-bottom: 2px solid #1e40af;'>#</th>
                    <th style='padding: 12px 15px; font-weight: 600; text-align: left; border-bottom: 2px solid #1e40af;'>Archivo Generado</th>
                    <th style='padding: 12px 15px; font-weight: 600; border-bottom: 2px solid #1e40af;'>Tamaño</th>
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
        
        // Ícono de Excel estilizado (Badge verde redondeado)
        $iconoExcel = "<span style='display: inline-flex; align-items: center; justify-content: center; background-color: #ecfdf5; color: #059669; width: 28px; height: 28px; border-radius: 6px; margin-right: 10px; font-size: 14px;'><i class='fas fa-file-excel'></i></span>";

        // 1. Guardar archivo Académico
        if (!empty($datos_academicos)) {
            $nombreArchivoAcademico = trim($nombreBaseClean) . " - ACADEMICO.xlsx";
            $rutaAcademica = $DestinoArchivo . "/" . $nombreArchivoAcademico;
            
            $writer = new Xlsx($objPHPExcel);
            $writer->save($rutaAcademica);

            $tamano = round(filesize($rutaAcademica) / 1024, 2);
            $tamanoTexto = $tamano < 1024 ? "{$tamano} KB" : round($tamano / 1024, 2) . " MB";

            // Fila con fondo blanco limpio
            $contenidoHTML .= "
                <tr style='background-color: #ffffff; border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 12px 15px; color: #64748b; font-weight: bold;'>{$conteo}</td>
                    <td style='padding: 12px 15px; text-align: left; color: #1e293b;'>
                        <div style='display: flex; align-items: center;'>
                            {$iconoExcel}
                            <span style='font-weight: 500;'>{$nombreArchivoAcademico}</span>
                        </div>
                    </td>
                    <td style='padding: 12px 15px; color: #475569; font-weight: 500;'>{$tamanoTexto}</td>
                </tr>";
            $conteo++;
        }

        // 2. Guardar archivo Modular
        if ($codigo_bachillerato == '15' && $objPHPExcelModulos !== null && !empty($datos_modulares)) {
            $nombreArchivoModular = trim($nombreBaseClean) . " - MODULOS.xlsx";
            $rutaModular = $DestinoArchivo . "/" . $nombreArchivoModular;

            $writerMod = new Xlsx($objPHPExcelModulos);
            $writerMod->save($rutaModular);

            $tamanoMod = round(filesize($rutaModular) / 1024, 2);
            $tamanoTextoMod = $tamanoMod < 1024 ? "{$tamanoMod} KB" : round($tamanoMod / 1024, 2) . " MB";

            // Fila con sombreado de cebra alterno (#f8fafc)
            $contenidoHTML .= "
                <tr style='background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;'>
                    <td style='padding: 12px 15px; color: #64748b; font-weight: bold;'>{$conteo}</td>
                    <td style='padding: 12px 15px; text-align: left; color: #1e293b;'>
                        <div style='display: flex; align-items: center;'>
                            {$iconoExcel}
                            <span style='font-weight: 500;'>{$nombreArchivoModular}</span>
                        </div>
                    </td>
                    <td style='padding: 12px 15px; color: #475569; font-weight: 500;'>{$tamanoTextoMod}</td>
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