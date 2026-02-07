<?php
/**
 * Informe: Trámite de Título
 * Compatibilidad: PHP 8.1 / 8.2 / 8.3
 */

// 1. CONFIGURACIÓN INICIAL DE SEGURIDAD
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED); // Silenciar notificaciones menores
ini_set('display_errors', 0); // No mostrar errores en pantalla (rompen el PDF)
ob_start(); // Iniciar buffer de salida

$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_name('demoUI'); // Asegúrate que coincida con tu sistema
    session_start();
}

// 2. INCLUSIÓN DE LIBRERÍAS
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/consultas.php");
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
require($path_root."/registro_academico/php_libs/fpdf/fpdf.php");

// 3. HELPER FUNCTIONS PARA PHP 8 (CODIFICACIÓN)
// Reemplazamos utf8_decode (obsoleto) por mb_convert_encoding

// Función para preparar texto que va AL PDF (UTF-8 -> ISO-8859-1)
if (!function_exists('textoPDF')) {
    function textoPDF($string) {
        if (is_null($string)) return '';
        // Convertimos a string por seguridad y cambiamos encoding
        return mb_convert_encoding((string)$string, 'ISO-8859-1', 'UTF-8');
    }
}

// Función para limpiar texto DE LA BD (Asegurar UTF-8 limpio)
if (!function_exists('textoBD')) {
    function textoBD($string) {
        if (is_null($string)) return '';
        // Aseguramos que sea UTF-8 válido
        return mb_convert_encoding((string)$string, 'UTF-8', 'ISO-8859-1'); 
    }
}

// 4. CAPTURA DE VARIABLES (BLINDADA)
$codigo_all       = $_REQUEST["todos"] ?? '';
$conducta         = $_REQUEST['lstconducta'] ?? '';
$codigo_matricula = $_REQUEST['txtcodmatricula'] ?? '';
$codigo_alumno    = $_REQUEST['txtidalumno'] ?? '';
$estudias         = $_REQUEST['lstestudia'] ?? '';
$traslado         = $_REQUEST['txttraslado'] ?? '';
$mostrar_traslado = $_REQUEST["chktraslado"] ?? 'no';
$firma            = $_REQUEST["chkfirma"] ?? 'no';
$sello            = $_REQUEST["chksello"] ?? 'no';

$db_link = $dblink;

// 5. CONSULTA DE ENCABEZADO
consultas(18, 0, $codigo_all, '', '', '', $db_link, '');

// Inicializar variables para evitar "Undefined variable"
$print_bachillerato = ''; 
$print_grado = '';
$print_seccion = '';
$print_ann_lectivo = '';
$codigo_modalidad = '';
$codigo_grado = '';

while($row = $result_encabezado->fetch(PDO::FETCH_BOTH)) {
    // Usamos string cast (string) para evitar pasar null a trim
    $print_bachillerato = textoBD(trim((string)$row['nombre_bachillerato']));
    $print_grado        = textoBD(trim((string)$row['nombre_grado']));
    $print_seccion      = textoBD(trim((string)$row['nombre_seccion']));
    $print_ann_lectivo  = textoBD(trim((string)$row['nombre_ann_lectivo']));
    $codigo_modalidad   = trim((string)$row['codigo_bachillerato']);
    $codigo_grado       = trim((string)$row['codigo_grado']);
}

// 6. CONFIGURACIÓN DE FECHA
date_default_timezone_set('America/El_Salvador');
// Array de meses seguro
$meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
$dia = date("d");
$num_mes = (int)date('n');
$mes = $meses[$num_mes - 1]; 
$año = date("Y");


// 7. CLASE PDF PERSONALIZADA
class PDF extends FPDF
{
    function Header()
    {
        // Variables de sesión protegidas
        $institucion = isset($_SESSION['institucion']) ? textoPDF($_SESSION['institucion']) : textoPDF('Institución Desconocida');
        $nombre_distrito = isset($_SESSION['nombre_distrito']) ? textoPDF($_SESSION['nombre_distrito']) : ''; 
        $logo = $_SESSION['logo_uno'] ?? 'escudo-sv.png'; // Fallback

        //Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo-sv.png';
        // Verificar existencia antes de pintar
        if(file_exists($img)){
            $this->Image($img, 95, 15, 26, 26);
        }

        $this->SetFont('Arial','B',12);
        
        $this->SetY(45);
        // Títulos estáticos (Usamos textoPDF para tildes)
        $this->Cell(180, 5, textoPDF('MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA'), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('REPÚBLICA DE EL SALVADOR'), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('DIRECCIÓN DEPARTAMENTAL DE EDUCACIÓN DE SANTA ANA'), 0, 1, 'C');
        
        if(!empty($nombre_distrito)){
            $this->Cell(180, 5, $nombre_distrito, 0, 1, 'C');
        }

        $this->ln(2); 
        $this->Cell(180, 5, $institucion, 0, 1, 'C');
    }

    function Footer()
    {
        global $firma, $sello;
        
        $imagen_firma = $_SESSION['imagen_firma'] ?? '';
        $imagen_sello = $_SESSION['imagen_sello'] ?? '';
        $nombre_director = $_SESSION['nombre_director'] ?? '';

        $this->SetY(-35); // Un poco más de espacio
        $this->SetFont('Arial','I',12);
        
        //Firma
        if($firma == 'yes' && !empty($imagen_firma)){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_firma;
            if(file_exists($img)){
                $this->Image($img, 80, 225, 70, 15); // Ajuste coordenadas
            }
        }
        
        // Sello
        if($sello == 'yes' && !empty($imagen_sello)){
            $img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_sello;
            if(file_exists($img_sello)){
                $this->Image($img_sello, 125, 220, 30, 30);
            }
        }

        // Nombre Director (Limpio y convertido para PDF)
        $director_clean = textoBD($nombre_director);
        
        // Si tienes la función cambiar_de_del, úsala, si no, usa el texto limpio
        if(function_exists('cambiar_de_del')){
            $director_fmt = cambiar_de_del($director_clean);
        } else {
            $director_fmt = $director_clean;
        }
        
        $this->Cell(180, 5, ($director_fmt), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('Director(a) del Centro Educativo'), 0, 1, 'C');
    }
}

// 8. GENERACIÓN DEL DOCUMENTO
$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(20, 20);
$pdf->SetAutoPageBreak(true, 5);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetXY(15,80);
$pdf->SetFont('Arial','',12); 

// Variables globales para el cuerpo
$nombre_departamento = textoBD($_SESSION['nombre_departamento'] ?? '');
$nombre_municipio    = textoBD($_SESSION['nombre_municipio'] ?? '');
$nombre_distrito    = textoBD($_SESSION['nombre_distrito'] ?? '');

if(function_exists('cambiar_de_del')){
    $nombre_departamento = cambiar_de_del($nombre_departamento);
    $nombre_municipio = cambiar_de_del($nombre_municipio);
    $nombre_distrito = cambiar_de_del($nombre_distrito);
}

// Lógica de Modalidad (Bachillerato)
$nombre_modalidad = '';
if(!empty($print_bachillerato)){
    // Usamos explode con límite para evitar errores si no hay espacios
    $porciones = explode(" ", $print_bachillerato);
    
    // Si el array tiene más de 1 elemento, tomamos desde el índice 1, sino todo el string
    $raw_modalidad = (count($porciones) > 1) ? $porciones[1] : $print_bachillerato;
    // A veces la modalidad son varias palabras después de "Bachillerato", 
    // lo ideal sería tomar todo menos la primera palabra si dice "Bachillerato":
    if(str_contains(strtolower($porciones[0]), 'bachillerato') && count($porciones) > 1){
         // Reconstruir el resto del string
         unset($porciones[0]);
         $raw_modalidad = implode(" ", $porciones);
    }
    
    $nombre_modalidad = trim($raw_modalidad);
    
    if(function_exists('cambiar_de_del')){
        $nombre_modalidad = cambiar_de_del($nombre_modalidad);
    }
}

$año_anterior = (int)$año; // Usar el año actual o restar si es necesario

// 9. CONSULTA DATOS ALUMNO E IMPRESIÓN
consultas_alumno(3, 0, $codigo_all, $codigo_alumno, $codigo_matricula, '', $db_link, '');      

$nombre_estudiante = 'Estudiante'; 

while($row = $result->fetch(PDO::FETCH_BOTH))
{
    // Datos limpios
    $nombre_estudiante = textoBD(trim((string)$row['nombre_a_pm']));
    $codigo_nie = textoBD(trim((string)$row['codigo_nie']));
    $institucion_nombre = textoBD($_SESSION['institucion'] ?? '');

    // -------------------------------------------------------------------
        // LÓGICA DE AÑOS (RETROACTIVA)
        // -------------------------------------------------------------------
        $anio_actual_sistema = (int)$año;              // Ej: 2026
        $anio_lectivo_estudiante = (int)$print_ann_lectivo; // Ej: 2025 (Viene de la BD)

        // Inicialmente usamos el año actual
        $anio_calculado = $anio_actual_sistema;

        // Si estamos en el año siguiente (o futuro) respecto a la graduación, restamos 1
        if ($anio_actual_sistema > $anio_lectivo_estudiante) {
            $anio_calculado = $anio_actual_sistema - 1;
        }

    // Construcción de Textos (Aún en UTF-8 o formato interno)

    $txt_p1 = 'El infrascrito(a) director(a) del '.$institucion_nombre. ' del distrito de ' . $nombre_distrito .' del municipio de ' . $nombre_municipio. ', Departamento de ' . $nombre_departamento.'.';
    
    $txt_p2 = 'HACE CONSTAR QUE: '. $nombre_estudiante .', Con Número de Identificación Estudiantil (NIE): '.$codigo_nie
        .' ha culminado satisfactoriamente sus estudios de bachillerato en la modalidad de '. $nombre_modalidad .' en este Centro Educativo, dando cumplimiento a todos los requisitos exigidos por el Ministerio de Educación para la legalización del Título de Bachillerato '. $nombre_modalidad . '.';
    
    $txt_p3 = 'Por tanto, su título que le acredita como Bachiller de la República, se encuentra en trámite de legalización. Ante ello, el Ministerio de Educación, Ciencia y Tecnología está haciendo las gestiones pertinentes con base a la solicitud enviada por nuestra institución'
        .', para la emisión del respectivo título en la mayor brevedad posible, el cual tendrá validez a partir del 26 de noviembre del año '. $anio_calculado .'.';
    
    $txt_p4 = 'Y para los usos que el/la interesado(a) estime conveniente, se le extiende la presente constancia, en el distrito de ' . $nombre_distrito . ' en el municipio de '. $nombre_municipio . ' departamento de '. $nombre_departamento.', '
        . 'a los '. strtolower(num2letras($dia)).' días de '.$mes.' de '.strtolower(num2letras($año)).'.';
    
    // Imprimir usando textoPDF para convertir a ISO-8859-1 compatible con FPDF
    $pdf->MultiCell(0, 8, textoPDF($txt_p1), 0, "J");
    $pdf->ln();
    $pdf->MultiCell(0, 8, textoPDF($txt_p2), 0, "J");
    $pdf->ln();
    $pdf->MultiCell(0, 8, textoPDF($txt_p3), 0, "J");
    $pdf->ln();
    $pdf->MultiCell(0, 8, textoPDF($txt_p4), 0, "J");
    
    break; // Solo procesar un alumno
}

// 10. SALIDA
$nombre_archivo = $nombre_estudiante . '.pdf';
// Limpiar nombre de archivo para Windows/Web
$nombre_archivo = preg_replace('/[^A-Za-z0-9 \-_]/', '', $nombre_archivo);
$nombre_archivo = str_replace(" ", "_", $nombre_archivo) . ".pdf";

ob_end_clean(); // Limpiar cualquier basura del buffer
$pdf->Output('I', $nombre_archivo); // 'I' para ver en navegador
?>