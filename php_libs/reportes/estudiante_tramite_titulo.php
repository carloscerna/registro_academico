<?php
/**
 * Informe: Trámite de Título
 * Compatibilidad: PHP 8.1 / 8.2 / 8.3
 */

// 1. CONFIGURACIÓN INICIAL DE SEGURIDAD
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED); 
ini_set('display_errors', 0); 
ob_start(); 

$path_root = trim($_SERVER['DOCUMENT_ROOT']);

if (session_status() === PHP_SESSION_NONE) {
    session_name('demoUI'); 
    session_start();
}

// 2. INCLUSIÓN DE LIBRERÍAS
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/consultas.php");
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
require($path_root."/registro_academico/php_libs/fpdf/fpdf.php");

// 3. HELPER FUNCTIONS
if (!function_exists('textoPDF')) {
    function textoPDF($string) {
        if (is_null($string)) return '';
        return mb_convert_encoding((string)$string, 'ISO-8859-1', 'UTF-8');
    }
}

if (!function_exists('textoBD')) {
    function textoBD($string) {
        if (is_null($string)) return '';
        return mb_convert_encoding((string)$string, 'UTF-8', 'ISO-8859-1'); 
    }
}

// 4. CAPTURA DE VARIABLES
$codigo_all       = $_REQUEST["todos"] ?? '';
$codigo_matricula = $_REQUEST['txtcodmatricula'] ?? '';
$codigo_alumno    = $_REQUEST['txtidalumno'] ?? '';
$firma            = $_REQUEST["chkfirma"] ?? 'no';
$sello            = $_REQUEST["chksello"] ?? 'no';

$db_link = $dblink;

// 5. CONSULTA DE ENCABEZADO
consultas(18, 0, $codigo_all, '', '', '', $db_link, '');

$print_bachillerato = ''; 
$print_ann_lectivo = '';

while($row = $result_encabezado->fetch(PDO::FETCH_BOTH)) {
    $print_bachillerato = textoBD(trim((string)$row['nombre_bachillerato']));
    $print_ann_lectivo  = textoBD(trim((string)$row['nombre_ann_lectivo']));
}

// 6. CONFIGURACIÓN DE FECHA
date_default_timezone_set('America/El_Salvador');
$meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
$dia = date("d");
$num_mes = (int)date('n');
$mes = $meses[$num_mes - 1]; 
$año = date("Y");

// Array de días manual para evitar errores de "cientos"
$dias_en_letras = [
    1 => "primer", 2 => "dos", 3 => "tres", 4 => "cuatro", 5 => "cinco",
    6 => "seis", 7 => "siete", 8 => "ocho", 9 => "nueve", 10 => "diez",
    11 => "once", 12 => "doce", 13 => "trece", 14 => "catorce", 15 => "quince",
    16 => "dieciséis", 17 => "diecisiete", 18 => "dieciocho", 19 => "diecinueve", 20 => "veinte",
    21 => "veintiuno", 22 => "veintidós", 23 => "veintitrés", 24 => "veinticuatro", 25 => "veinticinco",
    26 => "veintiséis", 27 => "veintisiete", 28 => "veintiocho", 29 => "veintinueve", 30 => "treinta", 31 => "treinta y uno"
];

// 7. CLASE PDF PERSONALIZADA
class PDF extends FPDF
{
    function Header()
    {
        $institucion = isset($_SESSION['institucion']) ? textoPDF($_SESSION['institucion']) : '';
        $nombre_distrito = isset($_SESSION['nombre_distrito']) ? textoPDF($_SESSION['nombre_distrito']) : ''; 
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo-sv.png';
        if(file_exists($img)){ $this->Image($img, 95, 12, 22, 22); }
        $this->SetFont('Arial','B',11);
        $this->SetY(38);
        $this->Cell(180, 5, textoPDF('MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA'), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('REPÚBLICA DE EL SALVADOR'), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('DIRECCIÓN DEPARTAMENTAL DE EDUCACIÓN DE SANTA ANA'), 0, 1, 'C');
        if(!empty($nombre_distrito)){ $this->Cell(180, 5, $nombre_distrito, 0, 1, 'C'); }
        $this->ln(2); 
        $this->Cell(180, 5, $institucion, 0, 1, 'C');
    }

    function Footer()
    {
        global $firma, $sello;
        $imagen_firma = $_SESSION['imagen_firma'] ?? '';
        $imagen_sello = $_SESSION['imagen_sello'] ?? '';
        $nombre_director = $_SESSION['nombre_director'] ?? '';

        $this->SetY(-40);
        if($firma == 'yes' && !empty($imagen_firma)){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_firma;
            if(file_exists($img)){ $this->Image($img, 80, 230, 60, 15); }
        }
        if($sello == 'yes' && !empty($imagen_sello)){
            $img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_sello;
            if(file_exists($img_sello)){ $this->Image($img_sello, 130, 225, 28, 28); }
        }
        $director_clean = ($nombre_director);
        $director_fmt = function_exists('cambiar_de_del') ? ($director_clean) : $director_clean;
        $this->SetFont('Arial','B',11);
        $this->Cell(180, 5, textoPDF($director_fmt), 0, 1, 'C');
        $this->SetFont('Arial','',11);
        $this->Cell(180, 5, textoPDF('Director(a) del Centro Educativo'), 0, 1, 'C');
    }
}

// 8. GENERACIÓN DEL DOCUMENTO
$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(20, 20);
$pdf->SetAutoPageBreak(true, 10);
$pdf->AddPage();
$pdf->SetXY(20,85);
$pdf->SetFont('Arial','',12); 

$nombre_departamento = textoBD($_SESSION['nombre_departamento'] ?? '');
$nombre_municipio    = textoBD($_SESSION['nombre_municipio'] ?? '');
$nombre_distrito     = textoBD($_SESSION['nombre_distrito'] ?? '');

if(function_exists('cambiar_de_del')){
    $nombre_departamento = cambiar_de_del($nombre_departamento);
    $nombre_municipio = cambiar_de_del($nombre_municipio);
    $nombre_distrito = cambiar_de_del($nombre_distrito);
}

// Lógica de Modalidad
$nombre_modalidad = '';
if(!empty($print_bachillerato)){
    $porciones = explode(" ", $print_bachillerato);
    if(str_contains(strtolower($porciones[0]), 'bachillerato') && count($porciones) > 1){
         unset($porciones[0]);
         $nombre_modalidad = implode(" ", $porciones);
    } else {
        $nombre_modalidad = $print_bachillerato;
    }
}

// 9. CONSULTA DATOS ALUMNO E IMPRESIÓN
consultas_alumno(3, 0, $codigo_all, $codigo_alumno, $codigo_matricula, '', $db_link, '');      

while($row = $result->fetch(PDO::FETCH_BOTH))
{
    $nombre_estudiante = textoBD(trim((string)$row['nombre_a_pm']));
    $codigo_nie = textoBD(trim((string)$row['codigo_nie']));
    $institucion_nombre = textoBD($_SESSION['institucion'] ?? '');

    // Lógica de año retroactivo
    $anio_actual_sistema = (int)$año;
    $anio_lectivo_estudiante = (int)$print_ann_lectivo;
    $anio_calculado = ($anio_actual_sistema > $anio_lectivo_estudiante) ? $anio_lectivo_estudiante : $anio_actual_sistema;

    // FECHA FORMATEADA SIN ERRORES
    $dia_final = $dias_en_letras[(int)$dia] ?? $dia;
    // Limpieza profunda del año para evitar "cientos"
    $anio_final_texto = strtolower(num2letras($año));
    $anio_final_texto = str_replace(["cientos ", "pesos", "con 00/100", "  "], ["", "", "", " "], $anio_final_texto);
    if($año == "2026") $anio_final_texto = "dos mil veintiséis";

    $txt_p1 = 'El infrascrito(a) director(a) del '.$institucion_nombre. ' del distrito de ' . $nombre_distrito .' del municipio de ' . $nombre_municipio. ', Departamento de ' . $nombre_departamento.'.';
    
    $txt_p2 = 'HACE CONSTAR QUE: '. $nombre_estudiante .', Con Número de Identificación Estudiantil (NIE): '.$codigo_nie
        .' ha culminado satisfactoriamente sus estudios de bachillerato en la modalidad de '. $nombre_modalidad .' en este Centro Educativo, dando cumplimiento a todos los requisitos exigidos por el Ministerio de Educación para la legalización del Título de Bachillerato '. $nombre_modalidad . '.';
    
    $txt_p3 = 'Por tanto, su título que le acredita como Bachiller de la República, se encuentra en trámite de legalización. Ante ello, el Ministerio de Educación, Ciencia y Tecnología está haciendo las gestiones pertinentes con base a la solicitud enviada por nuestra institución'
        .', para la emisión del respectivo título en la mayor brevedad posible, el cual tendrá validez a partir del 26 de noviembre del año '. $anio_calculado .'.';
    
    $txt_p4 = 'Y para los usos que el/la interesado(a) estime conveniente, se le extiende la presente constancia, en el distrito de ' . $nombre_distrito . ' en el municipio de '. $nombre_municipio . ' departamento de '. $nombre_departamento.', '
        . 'a los '. $dia_final .' días del mes de '.$mes.' de '. trim($anio_final_texto) .'.';
    
    // Impresión con interlineado de 7 para asegurar espacio
    $pdf->MultiCell(0, 7, textoPDF($txt_p1), 0, "J");
    $pdf->ln(4);
    $pdf->MultiCell(0, 7, textoPDF($txt_p2), 0, "J");
    $pdf->ln(4);
    $pdf->MultiCell(0, 7, textoPDF($txt_p3), 0, "J");
    $pdf->ln(4);
    $pdf->MultiCell(0, 7, textoPDF($txt_p4), 0, "J");
    
    break; 
}

// 10. SALIDA
$nombre_archivo = preg_replace('/[^A-Za-z0-9 \-_]/', '', $nombre_estudiante);
$nombre_archivo = str_replace(" ", "_", $nombre_archivo) . ".pdf";

ob_end_clean();
$pdf->Output('I', $nombre_archivo);
?>