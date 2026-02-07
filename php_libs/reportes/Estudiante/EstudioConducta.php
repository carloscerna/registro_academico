<?php
// <-- CORRECCIÓN DEPRECATED PHP 8.3 -->
// Silenciamos advertencias de librerías antiguas (FPDF)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0); // Ocultar errores en pantalla para no corromper el PDF

ob_start(); 

$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Archivos requeridos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/consultas.php");
include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
require($path_root."/registro_academico/php_libs/fpdf/fpdf.php");

// Configuración regional
date_default_timezone_set('America/El_Salvador');
setlocale(LC_TIME, 'es_SV.UTF-8', 'es_ES.UTF-8', 'es_SV', 'es');

// Variables de entrada protegidas
$codigo_all = $_REQUEST["todos"] ?? '';
$conducta = $_REQUEST['lstconducta'] ?? '';
$codigo_matricula = $_REQUEST['txtcodmatricula'] ?? '';
$codigo_alumno = $_REQUEST['txtidalumno'] ?? '';
$estudias = $_REQUEST['lstestudia'] ?? '';
$traslado = $_REQUEST['txttraslado'] ?? '';
$mostrar_traslado = $_REQUEST["chktraslado"] ?? 'no';
$firma = $_REQUEST["chkfirma"] ?? 'no';
$sello = $_REQUEST["chksello"] ?? 'no';
$crear_archivos = $_REQUEST["chkCrearArchivoPdf"] ?? 'no';

$db_link = $dblink;

// Lógica de fechas
$meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
$hoy = getdate();
$NombreDia = $hoy["wday"];  
$dia = $hoy["mday"];    
$mes = $hoy["mon"];     
$año = $hoy["year"];    
$nombresMeses = [1=>"Enero",2=>"Febrero",3=>"Marzo",4=>"Abril",5=>"Mayo",6=>"Junio",7=>"Julio",8=>"Agosto",9=>"Septiembre",10=>"Octubre",11=>"Noviembre",12=>"Diciembre"];

// Función auxiliar UTF8
if (!function_exists('utf8_decode_fix')) {
    function utf8_decode_fix($texto) {
        if (is_null($texto)) return '';
        return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
    }
}

// Ejecutar consulta principal
consultas(13, 0, $codigo_all, '', '', '', $db_link, '');

global $nombreNivel, $nombreGrado, $nombreSeccion, $nombreTurno, $nombreAñoLectivo;

// ====================================================================
// CLASE PDF EXTENDIDA
// ====================================================================
class PDF extends FPDF
{
    var $angle=0;

    function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1) $x=$this->x;
        if($y==-1) $y=$this->y;
        if($this->angle!=0) $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function RotatedText($x,$y,$txt,$angle)
    {
        $this->Rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
        $this->Rotate(0);
    }

    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234')
    {
        // BLINDAJE PARA PHP 8: Asegurar que $angle es string
        $angle = (string)$angle; 
        
        $k = $this->k;
        $hp = $this->h;
        if($style=='F') $op='f';
        elseif($style=='FD' || $style=='DF') $op='B';
        else $op='S';
        
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k ));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l', $xc*$k,($hp-$y)*$k ));
        if (strpos($angle, '2')===false)
            $this->_out(sprintf('%.2F %.2F l', ($x+$w)*$k,($hp-$y)*$k ));
        else
            $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($angle, '3')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($angle, '4')===false)
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-($y+$h))*$k));
        else
            $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$yc)*$k ));
        if (strpos($angle, '1')===false)
            $this->_out(sprintf('%.2F %.2F l',($x)*$k,($hp-$y)*$k ));
        else
            $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }

    //Cabecera de página
    function Header()
    {
        $logo = $_SESSION['logo_uno'] ?? 'no_logo.png';
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$logo;
        if(file_exists($img)){
            $this->Image($img,5,4,20,26);
        }
        
        $this->SetFont('Arial','B',14);
        
        $institucion = $_SESSION['institucion'] ?? 'Nombre Institución';
        $codigo_inst = $_SESSION['codigo'] ?? '';
        $direccion = $_SESSION['direccion'] ?? '';
        $telefono = $_SESSION['telefono'] ?? '';

        $this->RotatedText(30,10,utf8_decode_fix($institucion . ' - ' . $codigo_inst),0);
        
        $this->SetFont('Arial','B',12);
        $this->RotatedText(30,17,utf8_decode_fix($direccion . ', Santa Ana '),0);
        
        if(!empty($telefono)){
            $this->RotatedText(30,24,utf8_decode_fix('Teléfono: ').$telefono,0);
        }
        
        // Estilo líneas (Sin CurveDraw para evitar errores)
        $this->SetLineWidth(0.5);
        $this->Line(20, 38, 200, 38);
        $this->Line(20, 39, 200, 39);
    }

    //Pie de página
    function Footer()
    {
        global $firma, $sello;
        
        $nombre_director = $_SESSION['nombre_director'] ?? 'Director';
        $imagen_firma = $_SESSION['imagen_firma'] ?? '';
        $imagen_sello = $_SESSION['imagen_sello'] ?? '';

        $this->SetY(-20);
        $this->SetFont('Arial','I',12);
        $this->Line(115,225,200,225);

        // Firma Director
        if($firma == 'yes'){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_firma;
            if(file_exists($img) && !empty($imagen_firma)){
                $this->Image($img,130,208,45,20);
            }
        }
        // Sello
        if($sello == 'yes'){
            $img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_sello;
            if(file_exists($img_sello) && !empty($imagen_sello)){
                $this->Image($img_sello,107,200,30,30);
            }
        }
        
        if(function_exists('cambiar_de_del')){
             $nombre_director_fmt = cambiar_de_del($nombre_director);
        } else {
             $nombre_director_fmt = utf8_decode_fix($nombre_director);
        }

        $this->RotatedText(120,230,$nombre_director_fmt,0);
        $this->RotatedText(140,235,'Director(a)',0);
        
        // Líneas decorativas pie (Sin CurveDraw)
        $this->Line(20, 267, 200, 267);
        $this->Line(20, 268, 200, 268);
    }
}

// CREANDO EL INFORME
$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(20, 20);
$pdf->SetAutoPageBreak(true,5);
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetY(20);
$pdf->SetX(15);

// Diseño Rectangulos
$pdf->SetFillColor(224);
$pdf->RoundedRect(45, 55, 155, 8, 2, 'DF', '1234');
$pdf->RoundedRect(105, 65, 35, 8, 2, '', '1234');
$pdf->RoundedRect(53, 75, 147, 8, 2, '', '1234');
$pdf->RoundedRect(55, 85, 110, 8, 2, '', '1234');
$pdf->RoundedRect(55, 95, 20, 8, 2, '', '1234');
$pdf->RoundedRect(55, 105, 20, 8, 2, '', '1234');

$pdf->SetFont('Arial','',12);

// Consultar Datos del Alumno
consultas_alumno(3,0,$codigo_all,$codigo_alumno,$codigo_matricula,'',$db_link,'');

// VARIABLES DE SESION
$lugar_emision = $_SESSION['se_extiende'] ?? 'Santa Ana';

while($row = $result->fetch(PDO::FETCH_BOTH)) {
    $nombreEstudiante = utf8_decode_fix(trim($row['apellido_alumno'] ?? ''));
    $codigoNIE = utf8_decode_fix(trim($row['codigo_nie'] ?? ''));

    $pdf->SetFont('Arial','',12);
    $pdf->SetXY(15,45);
    $pdf->MultiCell(180,8,utf8_decode_fix("El/la suscrito(a) Director(a) CERTIFICA que él/la: "));
    
    $pdf->RotatedText(20,60,'Alumno(a): ',0);
    $pdf->SetFont('Arial','IB',13);
    $pdf->RotatedText(50,60,$nombreEstudiante,0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(20,70,utf8_decode_fix('Número de Identificación Estudiantil (NIE): '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(110,70,$codigoNIE,0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(30,80,utf8_decode_fix('Modalidad: '),0);
    $pdf->SetFont('Arial','B',12);
    $pdf->RotatedText(55,80,($nombreNivel ?? ''),0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(30,90,utf8_decode_fix('Grado: '),0);
    $pdf->SetFont('Arial','B',12);
    $pdf->RotatedText(57,90,($nombreGrado ?? ''),0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(30,100,utf8_decode_fix('Sección: '),0);
    $pdf->SetFont('Arial','B',12);
    $pdf->RotatedText(57,100,($nombreSeccion ?? ''),0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(30,110,utf8_decode_fix('Año Lectivo: '),0);
    $pdf->SetFont('Arial','B',12);
    $pdf->RotatedText(57,110,utf8_decode_fix($nombreAñoLectivo ?? ''),0);

    $pdf->SetXY(20,120);
    
    $texto_cuerpo = utf8_decode_fix($estudias . " en esta institución y demostrando " . $conducta . " conducta hacia sus compañeros y maestros.");
    $pdf->MultiCell(180,10,$texto_cuerpo);

    if($mostrar_traslado == "yes"){
        $pdf->ln();
        $pdf->MultiCell(180,10,utf8_decode_fix($traslado));
    }
    
    $pdf->ln();
// Código corregido
$dia_letras = strtolower(trim(convertir($dia)));
$año_letras = strtolower(trim(convertir($año)));

$fecha_texto = utf8_decode_fix("Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en " . $lugar_emision . " de Santa Ana a los " . $dia_letras ." días del mes de " . strtolower($nombresMeses[$mes]) . " de " . $año_letras);
    $pdf->MultiCell(180,10,$fecha_texto);

    break; 
}

// SALIDA FINAL
$nombre_archivo_salida = $codigoNIE . "-" . str_replace(" ","_",$nombreEstudiante) . '.pdf';

if($crear_archivos == "no"){
    ob_end_clean(); 
    $pdf->Output('I', $nombre_archivo_salida);
} else {
    $ruta_destino = $path_root . "/registro_academico/temp/" . $nombre_archivo_salida;
    if(!is_dir(dirname($ruta_destino))) mkdir(dirname($ruta_destino), 0777, true);
    $pdf->Output('F', $ruta_destino);
    
    ob_end_clean();
    echo json_encode(array(
        "respuesta" => true,
        "mensaje" => "Archivo Generado",
        "contenido" => "Archivo creado: " . $nombre_archivo_salida,
        "url" => "temp/" . $nombre_archivo_salida 
    ));
}
?>