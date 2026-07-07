<?php
// 1. SILENCIAR ADVERTENCIAS (Vital para FPDF en PHP 8)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/consultas.php");
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include $path_root."/registro_academico/php_libs/fpdf/fpdf.php";

header("Content-Type: text/html; charset=UTF-8");    

$codigo_annlectivo = $_REQUEST["annlectivo"] ?? '';
$db_link = $dblink;
$print_nombre_docente = "";  

date_default_timezone_set('America/El_Salvador');

// Variables de fecha
$hoy = getdate();
$NombreDia = $hoy["wday"];
$dia = $hoy["mday"];
$mes = $hoy["mon"];
$year = $hoy["year"];

// Uso de date('t') para mayor compatibilidad en PHP 8 si 'calendar' no está activo
$total_de_dias = date('t', mktime(0, 0, 0, $mes, 1, $year));

$nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
$nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    
$fechaTextual = $nombresDias[$NombreDia] . " " . $dia . " de " . $nombresMeses[$mes] . " de " . $year;
$fecha = function_exists('convertirTexto') ? convertirTexto("Santa Ana, $fechaTextual") : "Santa Ana, $fechaTextual";



class PDF extends FPDF {
    function Header() {
        global $year;
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.($_SESSION['logo_uno'] ?? '');
        if(!empty($_SESSION['logo_uno']) && file_exists($img)){
            $this->Image($img,10,5,15,20);
        }
        $this->SetFont('Arial','B',16); // Cambiado a Arial por si PoetsenOne no carga
        $this->Cell(200,6,isset($_SESSION['institucion']) ? convertirtexto($_SESSION['institucion']) : '',0,1,'C');
        $this->Cell(200,4,convertirtexto('Nómina de Estudiantes por Edades'),0,1,'C');
        

        $this->SetXY(170,25);
        $this->Write(6,pdfTexto("Año: ") . $year);

        
        $this->Line(5,35,210,35);
        $this->Ln(10);
    }

    function Footer() {
        global $fecha;
        $this->SetY(-10);
        $this->SetFont('Arial','I',8);
        $this->Line(5,270,210,270);
        $this->Cell(0,10,pdfTexto('Página ').$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
    }



    function TituloTabla($titulo)
    {
        $this->Ln(4);
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(220,220,220);

        $this->Cell(190,7,pdfTexto($titulo),1,1,'C',true);
    }

    function EncabezadoTabla()
    {
        $this->SetFont('Arial','B',10);

        $this->Cell(90,7,'GRADO',1,0,'C',true);
        $this->Cell(30,7,'NINOS',1,0,'C',true);
        $this->Cell(30,7,'NINAS',1,0,'C',true);
        $this->Cell(40,7,'TOTAL',1,1,'C',true);
    }

    function FilaTabla($grado,$h,$m)
    {
        $this->SetFont('Arial','',10);

        $this->Cell(90,6,pdfTexto($grado),1);

        $this->Cell(30,6,$h,1,0,'C');

        $this->Cell(30,6,$m,1,0,'C');

        $this->Cell(40,6,$h+$m,1,1,'C');
    }

    function TotalTabla($h,$m)
    {
        $this->SetFont('Arial','B',10);

        $this->Cell(90,7,'TOTAL',1);

        $this->Cell(30,7,$h,1,0,'C');

        $this->Cell(30,7,$m,1,0,'C');

        $this->Cell(40,7,$h+$m,1,1,'C');
    }




}

$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(10,10,10);
$pdf->SetAutoPageBreak(true,10);
$pdf->AliasNbPages();
$pdf->AddPage();

consultas(23,0,$codigo_annlectivo,'','','',$db_link,'');
global $result;

if($result && $result->rowCount() != 0){
    $total12M = 0;
    $total12F = 0;

    $total13M = 0;
    $total13F = 0;

    $total20M = 0;
    $total20F = 0;

    $datos = [];

    while($row = $result->fetch(PDO::FETCH_ASSOC))
    {
        $datos[] = $row;
    }
    
$pdf->TituloTabla("LISTADO DE ESTUDIANTES DE 12 AÑOS");

$pdf->EncabezadoTabla();


foreach($datos as $fila)
{
    if(($fila["edad12_m"]+$fila["edad12_f"])==0){
        continue;
    }

    $pdf->FilaTabla(
        ($fila["nombre_grado"]),
        $fila["edad12_m"],
        $fila["edad12_f"]
    );

    $total12M += $fila["edad12_m"];
    $total12F += $fila["edad12_f"];
}

$pdf->TotalTabla($total12M,$total12F);


$pdf->Ln(8);

$pdf->TituloTabla("LISTADO DE ESTUDIANTES DE 13 A 19 AÑOS");

$pdf->EncabezadoTabla();

foreach($datos as $fila)
{
    if(($fila["edad13_19_m"]+$fila["edad13_19_f"])==0){
        continue;
    }

    $pdf->FilaTabla(
        ($fila["nombre_grado"]),
        $fila["edad13_19_m"],
        $fila["edad13_19_f"]
    );

    $total13M += $fila["edad13_19_m"];
    $total13F += $fila["edad13_19_f"];
}

$pdf->TotalTabla($total13M,$total13F);

$pdf->Ln(8);

$pdf->TituloTabla("LISTADO DE ESTUDIANTES DE 20 AÑOS O MÁS");

$pdf->EncabezadoTabla();

foreach($datos as $fila)
{
    if(($fila["edad20_m"]+$fila["edad20_f"])==0){
        continue;
    }

    $pdf->FilaTabla(
        ($fila["nombre_grado"]),
        $fila["edad20_m"],
        $fila["edad20_f"]
    );

    $total20M += $fila["edad20_m"];
    $total20F += $fila["edad20_f"];
}

    $pdf->TotalTabla($total20M,$total20F);




    $nombreArchivo =
    "Nomina_Edades_" .
    quitar_tildes(str_replace(" ","_",convertirtexto($_SESSION['institucion']) )).
    "_" .
    $nombreAñoLectivo .
    "_" .
    date("Ymd_His") .
    ".pdf";

    $pdf->Output('I',$nombreArchivo);
} else {
    $pdf->Cell(150,7,'NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
    $pdf->Output();
} 


function pdfTexto(string $texto): string
{
    return mb_convert_encoding(
        trim($texto),
        'Windows-1252',
        'UTF-8'
    );
}


function quitar_tildes($cadena)
{
    $buscar = array(
        'á','é','í','ó','ú','Á','É','Í','Ó','Ú',
        'ñ','Ñ'
    );

    $reemplazar = array(
        'a','e','i','o','u','A','E','I','O','U',
        'n','N'
    );

    return str_replace($buscar,$reemplazar,$cadena);
}

?>