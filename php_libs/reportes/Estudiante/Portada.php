<?php
/**
 * Portada.php - Versión compatible con PHP 8.x
 * MANTIENE EL ESTILO Y DISEÑO ORIGINAL COMPLETO.
 */

// 1. RUTAS E INCLUDES ORIGINALES
$path_root = trim($_SERVER['DOCUMENT_ROOT'] ?? '');
require_once($path_root . "/registro_academico/includes/funciones.php");
require_once($path_root . "/registro_academico/includes/consultas.php");
require_once($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
require_once($path_root . "/registro_academico/includes/DeNumero_a_Letras.php");
require_once($path_root . "/registro_academico/php_libs/fpdf/fpdf.php");

header("Content-Type: text/html; charset=UTF-8");

// 2. VARIABLES Y CONSULTA
$codigo_alumno = $_REQUEST['txtidalumno'] ?? '';
$db_link = $dblink;

date_default_timezone_set('America/El_Salvador');
setlocale(LC_TIME, 'es_SV.UTF-8', 'es_SV');

// CREAR MATRIZ DE MESES Y FECH.
$meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
$hoy = getdate();
$NombreDia = $hoy["wday"];  
$dia = $hoy["mday"];    
$mes = $hoy["mon"];     
$año = $hoy["year"];    
$total_de_dias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, $año);
$NombreMes = $meses[(int)$mes - 1];

$nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
$nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
$fecha = convertirTexto("Santa Ana, $nombresDias[$NombreDia] $dia de $nombresMeses[$mes] de $año");
setlocale(LC_MONETARY,"es_ES");


/**
 * Clase PDF - Mantiene tu lógica de rotación y soluciona el error de CurveDraw
 */
class PDF extends FPDF {
    public $angle = 0;

    // Cabecera de página
    function Header() {
        // Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
        $this->Image($img,5,4,20,26);

        // Título
        $this->SetFont('Arial','B',14);
        $this->RotatedText(30,10,convertirtexto($_SESSION['institucion']),0);

        $this->SetFont('Arial','B',12);
        $this->RotatedText(30,17,convertirtexto($_SESSION['direccion']),0);

        // Teléfono
        if(empty($_SESSION['telefono'])){
            $this->RotatedText(30,24,'',0);    
        }else{
            $this->RotatedText(30,24,convertirtexto('Teléfono: ').$_SESSION['telefono'],0);
        }

        // Encabezado
        $style6 = ['width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0)];
        $this->CurveDraw(0, 37, 120, 40, 155, 20, 222, 20, '', $style6);
        $this->CurveDraw(0, 36, 120, 39, 155, 19, 222, 19, '', $style6);	
    }

    function Rect($x, $y, $w, $h, $style = '') {
        parent::Rect($x, $y, $w, $h, (string)($style ?? ''));
    }

    function Rotate($angle, $x = -1, $y = -1) {
        if ($x == -1) $x = $this->x;
        if ($y == -1) $y = $this->y;
        if ($this->angle != 0) $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle); $s = sin($angle);
            $cx = $x * $this->k; $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function RotatedText($x, $y, $txt, $angle) {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, (string)($txt ?? ''));
        $this->Rotate(0);
    }

    function Footer() {
        global $fecha;
        // Ajuste de posición vertical para pie de página de 295 mm
        $this->SetY(-20);
        $this->SetFont('Arial','I',12);    

        $style6 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0));
        // Ajustamos la curva del pie de página al nuevo alto (295mm)
        $this->CurveDraw(0, 283, 120, 286, 155, 266, 225, 266, '', $style6);
        $this->CurveDraw(0, 282, 120, 285, 155, 265, 225, 265, '', $style6);	

        $this->SetY(-15);
        $this->SetX(10);
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,'Digitalizado: ' . $fecha,0,0,'R');
    }
}

// 3. CONSULTA SQL ORIGINAL
$query = "SELECT a.id_alumno, a.codigo_nie, a.apellido_paterno, a.apellido_materno, a.nombre_completo, a.fecha_nacimiento,
       a.pn_numero, a.pn_folio, a.pn_tomo, a.pn_libro,
	   es.nombre_departamento, es.nombre_municipio, es.nombre_distrito
        FROM alumno a
        INNER JOIN elsalvador es ON es.codigo_departamento = a.codigo_departamento_pn and es.codigo_municipio = a.codigo_municipio_pn and es.codigo_distrito = a.codigo_distrito_pn
        WHERE id_alumno = '$codigo_alumno'";

$consulta = $db_link->query($query);

if ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
    // 4. LIMPIEZA DE DATOS
    $nie = (string)($row['nie'] ?? '');
    $apellido_paterno = trim((string)($row['apellido_paterno']) ?? '');
    $apellido_materno = trim((string)($row['apellido_materno']) ?? '');
    $nombre_completo = trim((string)($row['nombre_completo']) ?? '');
    $genero = ((string)($row['codigo_genero'] ?? '')) == '01' ? 'MASCULINO' : 'FEMENINO';
    $pn_numero = (string)($row['pn_numero'] ?? '');
    $pn_folio = (string)($row['pn_folio'] ?? '');
    $pn_tomo = (string)($row['pn_tomo'] ?? '');
    $pn_libro = (string)($row['pn_libro'] ?? '');
    $fecha_nacimiento = !empty($row['fecha_nacimiento']) ? date('d/m/Y', strtotime($row['fecha_nacimiento'])) : '';
    $nombre_departamento = (string)($row['nombre_departamento'] ?? '');
    $nombre_municipio = (string)($row['nombre_municipio'] ?? '');
    $nombre_distrito = (string)($row['nombre_distrito'] ?? '');

    // =========================================================
    // AJUSTE DE MEDIDAS PARA FOLDER MANILA (23 cm x 29.5 cm)
    // En FPDF las medidas se definen en milímetros (mm):
    // 23 cm = 230 mm | 29.5 cm = 295 mm
    // =========================================================
    $ancho_personalizado = 230; 
    $alto_personalizado = 295;

    $pdf = new PDF('P', 'mm', array($ancho_personalizado, $alto_personalizado));
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Agregar fuentes
    $pdf->AddFont('Comic','','comic.php');
    $pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');

    $pdf->SetY(20);
    $pdf->SetX(15);

    // Diseños de Rectángulos
    $pdf->SetFillColor(224);
    $pdf->RoundedRect(45, 55, 155, 8, 2, '1234', 'DF'); // nombres y apellidos
    $pdf->RoundedRect(105, 65, 35, 8, 2, '1234', '');   // NIE
    $pdf->RoundedRect(90, 75, 35, 8, 2, '1234', '');   // Fecha de Nacimiento
    $pdf->RoundedRect(90, 85, 50, 8, 2, '1234', '');   // Departamento de nacimiento
    $pdf->RoundedRect(90, 95, 95, 8, 2, '1234', '');   // Municipio de Nacimiento
    $pdf->RoundedRect(90, 105, 95, 8, 2, '1234', '');  // Distrito de Nacimiento
    
    $pdf->RoundedRect(35, 115, 20, 8, 2, '1234', '');  // Pn numero
    $pdf->RoundedRect(77, 115, 20, 8, 2, '1234', '');  // Pn folio
    $pdf->RoundedRect(125, 115, 20, 8, 2, '1234', ''); // Pn libro
    $pdf->RoundedRect(172, 115, 20, 8, 2, '1234', ''); // Pn tomo

    $apellido_alumno = $apellido_paterno . ' ' . $apellido_materno . ', ' . $nombre_completo;

    // --- CONFIGURACIÓN PARA TEXTO VERTICAL A LA DERECHA ---
    $nie_estudiante = (string)($row['codigo_nie'] ?? '');
    $nombre_estudiante = $apellido_alumno ?? '';
    $texto_completo = convertirtexto($nie_estudiante . " - " . $nombre_estudiante);

    // Ajuste de posición X en 222mm (cerca del borde de los 230mm)
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->RotatedText(224, 10, $texto_completo, 270);

    // Contenido del documento
    $pdf->SetFont('Arial','',12);
    $pdf->SetXY(15,45);
    $pdf->RotatedText(20,60,'Alumno(a): ',0);
    $pdf->SetFont('Arial','IB',13);
    $pdf->RotatedText(50,60,convertirtexto(trim($apellido_alumno)),0);
    $pdf->SetFont('Arial','',12);

    $pdf->RotatedText(20,70,convertirtexto('Número de Identificación Estudiantil (NIE): '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(112,70,convertirtexto(trim($row['codigo_nie'])),0);
    $pdf->SetFont('Arial','',12);

    // Fecha de nacimiento
    $pdf->RotatedText(20,80,convertirtexto('Fecha de Nacimiento: '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(95,80,$fecha_nacimiento,0);
    $pdf->SetFont('Arial','',12);

    // Departamento de nacimiento
    $pdf->RotatedText(20,90,convertirtexto('Departamento de Nacimiento: '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(95,90,$nombre_departamento,0);
    $pdf->SetFont('Arial','',12);

    // Municipio de nacimiento
    $pdf->RotatedText(20,100,convertirtexto('Municipio de Nacimiento: '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(95,100,$nombre_municipio,0);
    $pdf->SetFont('Arial','',12);

    // Distrito de nacimiento
    $pdf->RotatedText(20,110,convertirtexto('Distrito de Nacimiento: '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(95,110,$nombre_distrito,0);
    $pdf->SetFont('Arial','',12);

    // Datos de Partida de Nacimiento
    $pdf->RotatedText(20,120,convertirtexto('Nº P.N.'),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(39,120,$pn_numero,0);
    $pdf->SetFont('Arial','',12);

    $pdf->RotatedText(60,120,convertirtexto('Nº Folio'),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(80,120,$pn_folio,0);
    $pdf->SetFont('Arial','',12);

    $pdf->RotatedText(105,120,convertirtexto('Nº Tomo'),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(132,120,$pn_tomo,0);
    $pdf->SetFont('Arial','',12);

    $pdf->RotatedText(152,120,convertirtexto('Nº Libro'),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(178,120,$pn_libro,0);
    $pdf->SetFont('Arial','',12);

    // Texto grande con ID
    $pdf->SetFont('Comic','',56);
    $pdf->RotatedText(180,200,convertirtexto(trim($row['id_alumno'])),270);

    $pdf->Output();
} else {
    echo "No se encontraron registros.";
}