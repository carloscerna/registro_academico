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

$codigo_all = $_REQUEST["todos"] ?? '';
$db_link = $dblink;
$print_nombre_docente = "";  

date_default_timezone_set('America/El_Salvador');

// Variables de fecha
$hoy = getdate();
$NombreDia = $hoy["wday"];
$dia = $hoy["mday"];
$mes = $hoy["mon"];
$año = $hoy["year"];

// Uso de date('t') para mayor compatibilidad en PHP 8 si 'calendar' no está activo
$total_de_dias = date('t', mktime(0, 0, 0, $mes, 1, $año));

$nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
$nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    
$fechaTextual = $nombresDias[$NombreDia] . " " . $dia . " de " . $nombresMeses[$mes] . " de " . $año;
$fecha = function_exists('convertirTexto') ? convertirTexto("Santa Ana, $fechaTextual") : "Santa Ana, $fechaTextual";

// Consultas iniciales
$codigo_nivel = substr($codigo_all, 0, 2);
consultas(13, 0, $codigo_all, '', '', '', $db_link, '');
global $nombreNivel, $nombreGrado, $nombreSeccion, $nombreTurno, $nombreAñolectivo, $print_periodo;

consultas_docentes(1, 0, $codigo_all, '', '', '', $db_link, '');
global $result_docente, $print_nombre_docente; 

class PDF extends FPDF {
    function Header() {
        global $print_nombre_docente, $nombreNivel, $nombreGrado, $nombreSeccion, $nombreAñoLectivo, $nombreTurno;
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.($_SESSION['logo_uno'] ?? '');
        if(!empty($_SESSION['logo_uno']) && file_exists($img)){
            $this->Image($img,10,5,15,20);
        }
        $this->SetFont('Arial','B',16); // Cambiado a Arial por si PoetsenOne no carga
        $this->Cell(200,6,isset($_SESSION['institucion']) ? convertirtexto($_SESSION['institucion']) : '',0,1,'C');
        $this->Cell(200,4,convertirtexto('Nómina de Estudiantes'),0,1,'C');
        
        $this->SetXY(25,20);
        $this->SetFont('Arial','B',11);
        $this->Write(6,"Docente Encargado: ");
        $this->SetFont('Arial','',12);
        $this->Write(6, ($print_nombre_docente ?? ''));   

        $this->SetXY(10,25);
        $this->Write(6,"Nivel: " . ($nombreNivel ?? ''));
        $this->SetXY(170,25);
        $this->Write(6,convertirTexto("Año: ") . ($nombreAñoLectivo ?? ''));
        $this->SetXY(10,30);
        $this->Write(6,"Grado: " . ($nombreGrado ?? ''));
        $this->SetXY(120,30);
        $this->Write(6,"Seccion: " . ($nombreSeccion ?? ''));
        
        $this->Line(5,35,210,35);
        $this->Ln(10);
    }

    function Footer() {
        global $fecha;
        $this->SetY(-10);
        $this->SetFont('Arial','I',8);
        $this->Line(5,270,210,270);
        $this->Cell(0,10,convertirTexto('Página ').$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
    }

    function FancyTable($header) {
        $this->SetFillColor(0,0,0);
        $this->SetTextColor(255);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial','B',9);
        $w=[10,15,85,20,10,10,10,10,10,10,10];
        for($i=0;$i<count($header);$i++){
            $this->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',1);
        }
        $this->Ln();
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('Arial','',8);
    }
}

$pdf = new PDF('P','mm','Letter');
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true,10);
$pdf->AliasNbPages();
$pdf->AddPage();

$header = ['Nº','N I E','Nombre de Alumnos/as','F.Nac.','Edad','G.','So.','Rep.','Ret.','N.I.','P.N.'];
$pdf->FancyTable($header);

consultas(4,0,$codigo_all,'','','',$db_link,'');
global $result;

if($result && $result->rowCount() != 0){
    $w = [10,15,85,20,10,10,10,10,10,10,10];
    $fill = false; $i = 1;
    $m = 0; $f = 0; 
    $repitentem = 0; $repitentef = 0; 
    $sobreedadm = 0; $sobreedadf = 0;
    $nuevoingresom = 0; $nuevoingresof = 0;
    
    // MATRIZ PARA CONTEO DE EDADES
    $conteoEdades = [];

    while($row = $result->fetch(PDO::FETCH_BOTH)) {
        $genero = strtolower($row['genero'] ?? 'm');
        $edad = (int)($row['edad'] ?? 0);
        
        // Lógica de conteo por edad y género
        if(!isset($conteoEdades[$edad])) {
            $conteoEdades[$edad] = ['m' => 0, 'f' => 0];
        }
        $conteoEdades[$edad][$genero]++;

        // Imprimir fila
        $pdf->Cell($w[0],5,$i,'LR',0,'C',$fill);
        $pdf->Cell($w[1],5,$row['codigo_nie'] ?? '','LR',0,'C',$fill);
        $pdf->Cell($w[2],5,convertirtexto($row['apellido_alumno'] ?? ''),'LR',0,'L',$fill);
        $pdf->Cell($w[3],5,cambiaf_a_normal($row['fecha_nacimiento'] ?? ''),'LR',0,'C',$fill);
        $pdf->Cell($w[4],5,$edad,'LR',0,'C',$fill);
        $pdf->Cell($w[5],5,strtoupper($genero),'LR',0,'C',$fill);
        
        $si = convertirtexto('Sí');
        $pdf->Cell($w[6],5,($row['sobreedad'] == 't' ? $si : ''),'LR',0,'C',$fill);
        $pdf->Cell($w[7],5,($row['repitente'] == 't' ? $si : ''),'LR',0,'C',$fill);
        $pdf->Cell($w[8],5,($row['retirado'] == 't' ? $si : ''),'LR',0,'C',$fill);
        $pdf->Cell($w[9],5,($row['nuevo_ingreso'] == 't' ? $si : ''),'LR',0,'C',$fill);
        $pdf->Cell($w[10],5,($row['partida_nacimiento'] == 't' ? $si : ''),'LR',0,'C',$fill);
        $pdf->Ln();
        
        $fill = !$fill;
        if($genero == 'm') {
            $m++;
            if($row['repitente'] == 't') $repitentem++;
            if($row['sobreedad'] == 't') $sobreedadm++;
            if($row['nuevo_ingreso'] == 't') $nuevoingresom++;
        } else {
            $f++;
            if($row['repitente'] == 't') $repitentef++;
            if($row['sobreedad'] == 't') $sobreedadf++;
            if($row['nuevo_ingreso'] == 't') $nuevoingresof++;
        }
        $i++;
        
        if($pdf->GetY() > 240) { // Salto de página manual si se llena
            $pdf->Cell(array_sum($w),0,'','T');
            $pdf->AddPage();
            $pdf->FancyTable($header);
        }
    }
    $pdf->Cell(array_sum($w),0,'','T');

    // --- TABLA ESTADÍSTICA GENERAL ---
    $pdf->Ln(10);
    $pdf->SetFont('Arial','B',11);
    $pdf->SetX(30);
    $pdf->Cell(160,7,'ESTADISTICA GENERAL',1,1,'C',TRUE);
    $pdf->SetX(30);
    $pdf->Cell(40,7,'CONCEPTO',1,0,'C');
    $pdf->Cell(40,7,'Masculino',1,0,'C');
    $pdf->Cell(40,7,'Femenino',1,0,'C');
    $pdf->Cell(40,7,'Total',1,1,'C');

    $datos_est = [
        ['MATRICULA', $m, $f, ($m+$f)],
        ['REPITENTES', $repitentem, $repitentef, ($repitentem+$repitentef)],
        ['SOBREEDAD', $sobreedadm, $sobreedadf, ($sobreedadm+$sobreedadf)],
        ['NUEVO INGRESO', $nuevoingresom, $nuevoingresof, ($nuevoingresom+$nuevoingresof)]
    ];

    foreach($datos_est as $d) {
        $pdf->SetX(30);
        $pdf->SetFont('Arial','B',10);
        $pdf->Cell(40,7,$d[0],1,0,'L');
        $pdf->SetFont('Arial','',10);
        $pdf->Cell(40,7,$d[1],1,0,'C');
        $pdf->Cell(40,7,$d[2],1,0,'C');
        $pdf->Cell(40,7,$d[3],1,1,'C');
    }

    // --- NUEVA TABLA: DESGLOSE POR EDADES ---
    $pdf->Ln(10);
    $pdf->SetX(30);
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(160,7,'RESUMEN POR EDADES',1,1,'C',TRUE);
    $pdf->SetX(30);
    $pdf->Cell(40,7,'Edad',1,0,'C');
    $pdf->Cell(40,7,'Masculino',1,0,'C');
    $pdf->Cell(40,7,'Femenino',1,0,'C');
    $pdf->Cell(40,7,'Subtotal',1,1,'C');

    ksort($conteoEdades); // Ordenar de menor a mayor edad
    $sumM = 0; $sumF = 0;
    foreach($conteoEdades as $edad => $cant) {
        $pdf->SetX(30);
        $pdf->SetFont('Arial','',10);
        $subtotal = $cant['m'] + $cant['f'];
        $pdf->Cell(40,7,$edad . convertirTexto(" años"),1,0,'C');
        $pdf->Cell(40,7,$cant['m'],1,0,'C');
        $pdf->Cell(40,7,$cant['f'],1,0,'C');
        $pdf->Cell(40,7,$subtotal,1,1,'C');
        $sumM += $cant['m'];
        $sumF += $cant['f'];
    }
    // Total final de edades para validar
    $pdf->SetX(30);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(40,7,'TOTAL',1,0,'R');
    $pdf->Cell(40,7,$sumM,1,0,'C');
    $pdf->Cell(40,7,$sumF,1,0,'C');
    $pdf->Cell(40,7,($sumM + $sumF),1,1,'C');

    $pdf->Output();
} else {
    $pdf->Cell(150,7,'NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
    $pdf->Output();
}    
?>