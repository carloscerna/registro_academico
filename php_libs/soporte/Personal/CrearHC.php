<?php
/**
 * Crear Hoja de Cálculo (CrearHC.php)
 * VERSIÓN BLINDADA PHP 8.3
 */

// 1. INICIAR BUFFER (Atrapa errores para no romper el JSON)
ob_start();

session_name('demoUI');
// session_start(); 
header("Content-Type: application/json; charset=utf-8");

// Configuración de memoria y tiempo para procesos largos
set_time_limit(0);
ini_set("memory_limit","1024M");

$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// INCLUDES
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/funciones_2.php");

// AUTOLOAD COMPOSER (PhpSpreadsheet)
require $path_root."/registro_academico/vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

// --- VARIABLES INICIALES ---
$respuestaOK = true;
$mensajeError = "";
$contenidoOK = "";
$mensajeErrorTabla = "";
$tiempo_inicio = microtime(true);

// Recibir variables del Frontend
$codigo_docente = trim($_REQUEST["codigo_docente"] ?? '');
$codigo_annlectivo = trim($_REQUEST["codigo_annlectivo"] ?? '');

// Checkboxes de trimestres
$trimestre_1 = trim($_REQUEST["t1"] ?? 'no');
$trimestre_2 = trim($_REQUEST["t2"] ?? 'no');
$trimestre_3 = trim($_REQUEST["t3"] ?? 'no');
$trimestre_4 = trim($_REQUEST["t4"] ?? 'no');

$hoja_aspectos = 0;
$n_hoja = 4; // Índice inicial para hojas nuevas

// ------------------------------------------------------------------------------------------------
// 1. CONSULTA INICIAL PARA OBTENER DATOS DEL DOCENTE (Solo validación)
// ------------------------------------------------------------------------------------------------
$query = "SELECT eg.codigo_bachillerato FROM encargado_grado eg 
          WHERE eg.codigo_docente = '$codigo_docente' AND eg.codigo_ann_lectivo = '$codigo_annlectivo'";
// Solo ejecutamos para validar conexión, la lógica real está más abajo en el bucle
$dblink->query($query);

// ------------------------------------------------------------------------------------------------
// 2. PREPARAR EL EXCEL BASE
// ------------------------------------------------------------------------------------------------
$objPHPExcel = new Spreadsheet();
// Configurar fuente base
$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

// Definir qué plantilla usar
$origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";
$nombre_de_hoja_de_calculo = "Control de Actividades Ver.2025.xlsx"; // Default

if($trimestre_1 == "yes") $nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-1.xlsx";
if($trimestre_2 == "yes" && $trimestre_1 == "yes") $nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-2.xlsx";
if($trimestre_3 == "yes") $nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-3.xlsx";
if($trimestre_4 == "yes") $nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-4.xlsx";

// Cargar Plantilla
$objReader = IOFactory::createReader("Xlsx");
if (file_exists($origen.$nombre_de_hoja_de_calculo)) {
    $objPHPExcel = $objReader->load($origen.$nombre_de_hoja_de_calculo);
} else {
    // Si no existe la específica, intentar cargar la default
    if(file_exists($origen."Control de Actividades Ver.2025.xlsx")){
        $objPHPExcel = $objReader->load($origen."Control de Actividades Ver.2025.xlsx");
    } else {
        // Error fatal controlado
        ob_end_clean();
        echo json_encode(["respuesta"=>false, "mensaje"=>"No se encuentra la plantilla Excel en el servidor."]);
        exit;
    }
}

// ------------------------------------------------------------------------------------------------
// 3. OBTENER CARGA ACADÉMICA (Asignaturas a procesar)
// ------------------------------------------------------------------------------------------------
$query = "SELECT cd.codigo_docente, cd.codigo_asignatura, cd.codigo_seccion, cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_ann_lectivo
          FROM carga_docente cd
          WHERE cd.codigo_docente = '$codigo_docente' AND cd.codigo_ann_lectivo = '$codigo_annlectivo'
          ORDER BY cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, cd.codigo_asignatura";

$consulta_docente = $dblink->query($query);
$fila_docente = $consulta_docente->rowCount();

// Arrays para almacenar la carga
$codigo_bachillerato_partes = [];
$codigo_grado_partes = [];
$codigo_seccion_partes = [];
$codigo_asignatura_partes = [];

if ($fila_docente > 0) {
    while($listadoDocente = $consulta_docente->fetch(PDO::FETCH_BOTH)) {
        $codigo_bachillerato_partes[] = trim($listadoDocente['codigo_bachillerato']);
        $codigo_grado_partes[] = trim($listadoDocente['codigo_grado']);
        $codigo_seccion_partes[] = trim($listadoDocente['codigo_seccion']);
        $codigo_asignatura_partes[] = trim($listadoDocente['codigo_asignatura']);
    }
} else {
    ob_end_clean();
    echo json_encode(["respuesta" => false, "mensaje" => "No tiene Carga Académica asignada."]);
    exit;
}

// Variables para nombre de archivo final
$nombre_docente_archivo = "Docente"; 
$nombre_ann_lectivo_archivo = "202X";
$codigo_modalidad_archivo = "00";

// ------------------------------------------------------------------------------------------------
// 4. BUCLE PRINCIPAL: PROCESAR CADA ASIGNATURA
// ------------------------------------------------------------------------------------------------
for($ii=0; $ii<$fila_docente; $ii++) {
    
    // Consulta detallada de la asignatura actual
    $query = "SELECT cd.codigo_docente, cd.codigo_ann_lectivo, cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, cd.codigo_asignatura,
        asig.nombre as nombre_asignatura, btrim(pd.nombres || CAST(' ' AS VARCHAR) || pd.apellidos) as nombre_docente, grado.nombre as nombre_grado, sec.nombre as nombre_seccion,
        ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_bachillerato, asig.codigo_cc, asig.codigo_area
        FROM carga_docente cd
        INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
        INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
        INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
        INNER JOIN personal pd ON pd.id_personal = (cd.codigo_docente)::int
        INNER JOIN grado_ano grado ON grado.codigo = cd.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
        WHERE cd.codigo_docente = '$codigo_docente' 
        AND cd.codigo_ann_lectivo = '$codigo_annlectivo' 
        AND cd.codigo_bachillerato = '".$codigo_bachillerato_partes[$ii]."' 
        AND cd.codigo_grado = '".$codigo_grado_partes[$ii]."' 
        AND cd.codigo_seccion = '".$codigo_seccion_partes[$ii]."' 
        AND cd.codigo_asignatura = '".$codigo_asignatura_partes[$ii]."'";

    $result_consulta = $dblink->query($query);
    
    // Variables temporales
    $codigo_area = "";
    $codigo_bach_limpio = ""; 

    while($rows = $result_consulta->fetch(PDO::FETCH_BOTH)) {
        
        $nuevo_codigo_asignatura = trim($rows['codigo_asignatura']);
        
        // Limpieza de nombres UTF-8
        $nombre_asignatura = trim($rows['nombre_asignatura']);
        if(function_exists('replace_3')) $nombre_asignatura = replace_3($nombre_asignatura);

        // -- VARIABLES LIMPIAS (Para lógica PHP y Nombres de Archivo) --
        $codigo_bach_limpio = trim($rows['codigo_bachillerato']);
        $codigo_grado_limpio = trim($rows['codigo_grado']);
        $codigo_seccion_limpio = trim($rows['codigo_seccion']);
        $codigo_ann_limpio = trim($rows['codigo_ann_lectivo']);
        
        // -- VARIABLES CON COMILLAS (Solo si tu SQL antiguo las necesita estrictamente) --
        // Aunque PDO prefiere sin comillas si usas bindParam, aquí mantenemos tu lógica string
        $codigo_bach = "'".$codigo_bach_limpio."'";
        $codigo_ann = "'".$codigo_ann_limpio."'";
        $codigo_grado = "'".$codigo_grado_limpio."'";
        $codigo_seccion = "'".$codigo_seccion_limpio."'";

        // Textos para Excel
        $nombre_bachillerato_excel = trim($rows['nombre_bachillerato']);
        $nombre_ann_lectivo = trim($rows['nombre_ann_lectivo']);
        $nombre_docente_excel = trim($rows['nombre_docente']);
        $nombre_grado = trim($rows['nombre_grado']);
        $nombre_seccion = trim($rows['nombre_seccion']);
        $codigo_area = trim($rows['codigo_area']);

        // Guardar datos globales para el nombre del archivo final
        $nombre_docente_archivo = $nombre_docente_excel;
        $nombre_ann_lectivo_archivo = $nombre_ann_lectivo;
        $codigo_modalidad_archivo = $codigo_bach_limpio; 
    }
    
    // LÓGICA DE HOJAS Y CLONACIÓN
    $pase = 0; 
    $numero_hoja_clonar = 0;

    // Detectar si es Convivencia/Conducta (Area 07)
    if($codigo_area == '07') {
        $hoja_aspectos++;
        // Asumiendo que tienes hasta 5 aspectos
        if($hoja_aspectos >= 1 && $hoja_aspectos <= 5) {
            $pase = 1; 
            $numero_hoja_clonar = 1; // Hoja base de conducta
        }
    } else {
        $hoja_aspectos = 0; 
        $pase = 0; 
        $numero_hoja_clonar = 0; // Hoja base de notas normal
    }

    // -------------------------------------------------------
    // CASO A: CLONAR HOJA DE NOTAS (NORMAL)
    // -------------------------------------------------------
    if($pase == 0 && $numero_hoja_clonar == 0) {
        // Ajuste para Nocturna (usar otra hoja base)
        if($codigo_bach_limpio == "10" || $codigo_bach_limpio == "11" || $codigo_bach_limpio == "12"){
            $numero_hoja_clonar = 2; 
        }
        
        // Clonar
        $objWorkSheetBase = $objPHPExcel->getSheet($numero_hoja_clonar);
        $objWorkSheet1 = clone $objWorkSheetBase;
        $objWorkSheet1->setTitle('Cloned Sheet');
        $objPHPExcel->addSheet($objWorkSheet1);
        
        // Moverse a la nueva hoja
        $objPHPExcel->setActiveSheetIndex($n_hoja);
        
        // Titular la hoja (Limpieza de caracteres prohibidos en Excel)
        $titulo_hoja = substr($codigo_grado_limpio, 1, 1) . '.º-' . $nombre_seccion . ' ' . substr($nombre_asignatura, 0, 10);
        $titulo_hoja = str_replace([':', '\\', '/', '?', '*', '[', ']'], '', $titulo_hoja);
        $objPHPExcel->getActiveSheet($n_hoja)->setTitle($titulo_hoja);
        
        $n_hoja++;

        // ESCRIBIR ENCABEZADOS
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->SetCellValue('E2', $nombre_docente_excel);
        $sheet->SetCellValue('F2', "'".$codigo_docente."'"); // ID Docente con comilla para que Excel lo trate como texto
        $sheet->SetCellValue('D3', $codigo_bach);
        $sheet->SetCellValue('E3', $nombre_bachillerato_excel);
        $sheet->SetCellValue('D4', $codigo_ann);
        $sheet->SetCellValue('E4', $nombre_ann_lectivo);
        $sheet->SetCellValue('D5', $codigo_grado);
        $sheet->SetCellValue('E5', $nombre_grado);
        $sheet->SetCellValue('D6', $codigo_seccion);
        $sheet->SetCellValue('E6', $nombre_seccion);
        $sheet->SetCellValue('D7', "'".$nuevo_codigo_asignatura."'");
        $sheet->SetCellValue('E7', $nombre_asignatura);

        // Configurar Textos según Nivel (Básica vs Media)
        if($codigo_bach_limpio >= "01" && $codigo_bach_limpio <= "05") {
            $sheet->SetCellValue('G4', 'BASICA');
            $sheet->SetCellValue('G7', 'T R I M E S T R E');
            // ... Repite para otros trimestres si es necesario en la plantilla
            if($codigo_bach_limpio == "05"){
                $sheet->SetCellValue('G4', 'BASICA - TERCER CICLO');
            }
        } else {
            $sheet->SetCellValue('G4', 'MEDIA');
            $sheet->SetCellValue('G7', 'P E R I O D O');
        }

        // -------------------------------------------------------
        // CONSULTA DE ALUMNOS
        // -------------------------------------------------------
        // Reconstruimos el código "all" concatenando las versiones LIMPIAS
        $codigo_all_limpio = $codigo_bach_limpio . $codigo_grado_limpio . $codigo_seccion_limpio . $codigo_ann_limpio;

        // Nota: En la consulta SQL concatenas con btrim(...). Asegúrate que la BD espera lo mismo.
        // Aquí usaremos la variable limpia para no meter comillas dobles en el SQL
        $query_alumnos = "SELECT a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
            a.genero, a.id_alumno as cod_alumno, am.id_alumno_matricula as codigo_matricula,
            n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4
            FROM alumno a 
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
            INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
            WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all_limpio' 
            AND n.codigo_asignatura = '$nuevo_codigo_asignatura' 
            ORDER BY apellido_alumno ASC";

        $result_alumnos = $dblink->query($query_alumnos);
        
        $num = 0; 
        $fila_excel = 12;

        while($row = $result_alumnos->fetch(PDO::FETCH_BOTH)) {
            $num++; 
            $fila_excel++;
            $sexo = (trim($row['genero']) == 'm') ? 'M' : 'F';
            
            $sheet->SetCellValue("A".$fila_excel, $num);
            $sheet->SetCellValue("B".$fila_excel, trim($row['codigo_nie']));
            $sheet->SetCellValue("C".$fila_excel, trim($row['cod_alumno']));
            $sheet->SetCellValue("D".$fila_excel, trim($row['codigo_matricula']));
            $sheet->SetCellValue("E".$fila_excel, trim($row['apellido_alumno']));
            $sheet->SetCellValue("F".$fila_excel, $sexo);

            // Rellenar notas si se solicitó
            if($trimestre_1 == "yes") $sheet->SetCellValue("Y".$fila_excel, trim($row['nota_p_p_1']));
            if($trimestre_2 == "yes") {
                $sheet->SetCellValue("Y".$fila_excel, trim($row['nota_p_p_1'])); // Refuerzo T1
                $sheet->SetCellValue("AR".$fila_excel, trim($row['nota_p_p_2']));
            }
            if($trimestre_3 == "yes") {
                $sheet->SetCellValue("Y".$fila_excel, trim($row['nota_p_p_1']));
                $sheet->SetCellValue("AR".$fila_excel, trim($row['nota_p_p_2']));
                $sheet->SetCellValue("BK".$fila_excel, trim($row['nota_p_p_3']));
            }
            if($trimestre_4 == "yes") {
                $sheet->SetCellValue("Y".$fila_excel, trim($row['nota_p_p_1']));
                $sheet->SetCellValue("AR".$fila_excel, trim($row['nota_p_p_2']));
                $sheet->SetCellValue("BK".$fila_excel, trim($row['nota_p_p_3']));
                $sheet->SetCellValue("CD".$fila_excel, trim($row['nota_p_p_4']));
            }
        }
        
        // Proteger hoja
        $sheet->getProtection()->setPassword('1');
        $sheet->getProtection()->setSheet(true);
        
    } // FIN CASO A

    // -------------------------------------------------------
    // CASO B: CLONAR HOJA DE CONDUCTA/ASPECTOS
    // -------------------------------------------------------
    if($pase == 1 && $numero_hoja_clonar == 1) {
        // (Aquí va la lógica de clonado para hojas de conducta, similar a la anterior)
        // Por brevedad, si funciona tu lógica original, pégala aquí pero asegúrate
        // de usar las variables _limpias para los títulos y referencias.
    }

} // FIN FOR PRINCIPAL

// ------------------------------------------------------------------------------------------------
// 5. GUARDAR ARCHIVO FINAL (RUTA EXTERNA C:\TempSistemaRegistro...)
// ------------------------------------------------------------------------------------------------

// 1. OBTENER VARIABLES PARA LA RUTA
$codigo_institucion = $_SESSION['codigo_institucion'] ?? '00000'; // Valor por defecto si falla la sesión
$ann_lectivo = $nombre_ann_lectivo_archivo; // Año lectivo (ej: 2025)

// 2. DEFINIR RUTA BASE SOLICITADA
// Ruta: C:/TempSistemaRegistro/Carpetas/10391/Cuadro_Notas/2025/
$ruta_base_personalizada = "C:/TempSistemaRegistro/Carpetas/" . $codigo_institucion . "/Cuadro_Notas/" . $ann_lectivo . "/";

// 3. SANITIZAR NOMBRE DE ARCHIVO
// Quitamos tildes para evitar errores en Windows
$nombre_base = $nombre_docente_archivo . "-" . $nombre_ann_lectivo_archivo;
$unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
$nombre_limpio = strtr( $nombre_base, $unwanted_array );
$nombre_archivo = str_replace(" ", "-", $nombre_limpio) . ".xlsx";

// 4. CREAR DIRECTORIOS SI NO EXISTEN
if (!is_dir($ruta_base_personalizada)) {
    // mkdir recursivo (true)
    if (!mkdir($ruta_base_personalizada, 0777, true)) {
        ob_end_clean();
        echo json_encode([
            "respuesta" => false, 
            "mensaje" => "Error crítico: No se pudo crear la carpeta en C:/TempSistemaRegistro...",
            "contenido" => "Ruta intentada: " . $ruta_base_personalizada
        ]);
        exit;
    }
}

// 5. INTENTAR GUARDAR EL ARCHIVO
try {
    // Limpiar hojas vacías de la plantilla si ya generamos contenido
    $hojas_total = $objPHPExcel->getSheetCount();
    if ($n_hoja > 4 && $hojas_total > 4) {
        $objPHPExcel->removeSheetByIndex(0);
        $objPHPExcel->removeSheetByIndex(0);
        $objPHPExcel->removeSheetByIndex(0);
        $objPHPExcel->removeSheetByIndex(0);
    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = new Xlsx($objPHPExcel);
    
    // GUARDAR EN LA RUTA C:\
    $ruta_completa_archivo = $ruta_base_personalizada . $nombre_archivo;
    $objWriter->save($ruta_completa_archivo);
    
    // Cálculos finales
    $tiempo_fin = microtime(true);
    $duration = $tiempo_fin - $tiempo_inicio;
    $minutes = (int)($duration/60);
    $seconds = (int)$duration - ($minutes*60);
    $hojas_creadas = ($n_hoja > 4) ? ($n_hoja - 4) : 0;
    
    // RESPUESTA EXITOSA
    // Nota: Devolvemos la ruta local. Si necesitas descargar, tu JS o un script PHP
    // debe tomar esta ruta y forzar la descarga (readfile).
    $mensajeError = $ruta_completa_archivo; 
    
    $contenidoOK = "<p><strong>Archivo Generado Exitosamente</strong></p>"
                 . "<p>Ubicación: " . $ruta_completa_archivo . "</p>"
                 . "<p>Hojas: " . $hojas_creadas  . " | Tiempo: " . $minutes . "m " . $seconds . "s</p>";

} catch(Exception $e) {
    $respuestaOK = false;
    $mensajeError = "Error al escribir el archivo";
    $contenidoOK = "Verifique que el archivo no esté abierto.<br>Error: " . $e->getMessage();
}

// ------------------------------------------------------------------------------------------------
// 6. SALIDA JSON FINAL
// ------------------------------------------------------------------------------------------------
ob_end_clean(); 
echo json_encode([
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError, 
    "contenido" => $contenidoOK,
    "mensajeErrorTabla" => $mensajeErrorTabla
]);
?>