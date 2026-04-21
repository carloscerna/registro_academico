<?php
// ruta de los archivos con su carpeta
    $path_root=trim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");

// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");

// variables y consulta a la tabla.
      $codigo_alumno = $_REQUEST['txtidalumno'] ?? '';
      $db_link = $dblink;

    // Establecer formato para la fecha compatible con PHP 8
    date_default_timezone_set('America/El_Salvador');
    
    // Matriz de meses
    $meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
    
    // REEMPLAZO DE strftime() POR date() [Sustitución línea 27-29]
    $dia = date("d");		                    // El Día (ej: 20)
    $mes = $meses[date('n')-1];                 // El Mes en español
    $año = date("Y");		                    // El Año (ej: 2026)

class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
        //Logo con validación de existencia
        $logo = $_SESSION['logo_uno'] ?? '';
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$logo;
        
        if(!empty($logo) && file_exists($img)){
            $this->Image($img,5,4,20,26);
        }
        
        $this->SetFont('Arial','B',14);
        $this->RotatedText(30,10,convertirtexto($_SESSION['institucion'] ?? ''),0);
        
        $this->SetFont('Arial','B',12);
        $this->RotatedText(30,17,convertirtexto($_SESSION['direccion'] ?? ''),0);
        
        // Teléfono con protección null
        $telefono = $_SESSION['telefono'] ?? '';
        if(empty($telefono)){
            $this->RotatedText(30,24,'',0);    
        }else{
            $this->RotatedText(30,24,convertirtexto('Teléfono: ').$telefono,0);
        }

        // ARMAR ENCABEZADO
        $style6 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0));
        $this->CurveDraw(0, 37, 120, 40, 155, 20, 225, 20, '', $style6);
        $this->CurveDraw(0, 36, 120, 39, 155, 19, 225, 19, '', $style6);	
    }

    //Pie de página
    function Footer()
    {
        $this->SetY(-20);
        $this->SetFont('Arial','I',12);    
        $style6 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0));
        $this->CurveDraw(0, 267, 120, 270, 155, 250, 225, 250, '', $style6);
        $this->CurveDraw(0, 266, 120, 269, 155, 249, 225, 249, '', $style6);	
        
        $this->SetY(-15);
        $this->SetX(10);
        // Fecha de emisión usando date()
        $fecha_emision = date("d/m/Y H:i:s");
        $this->SetFont('Arial','',10);
        $this->Cell(0,10,convertirtexto('Emisión: ') . $fecha_emision,0,0,'R');
    }
}

// Creando el Informe
$pdf=new PDF('P','mm','Letter');
$pdf->SetMargins(20, 20);
$pdf->SetAutoPageBreak(true,5);
$pdf->AliasNbPages();
$pdf->AddPage();

// Agregar fuentes
$pdf->AddFont('Comic','','comic.php');
$pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');

$pdf->SetY(20);
$pdf->SetX(15);
$pdf->SetFillColor(224);

// Diseño de Rectángulos
$pdf->RoundedRect(45, 55, 155, 8, 2, '1234', 'DF'); 
$pdf->RoundedRect(105, 65, 35, 8, 2, '1234', '');   
$pdf->RoundedRect(90, 75, 35, 8, 2, '1234', '');   

$pdf->SetFont('Arial','',12);
$query = "SELECT a.id_alumno, a.codigo_nie, a.apellido_paterno, a.apellido_materno, a.nombre_completo, a.fecha_nacimiento,
            ann.nombre as nombre_annlectivo
            FROM alumno a
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
            INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
            WHERE a.id_alumno = '$codigo_alumno' LIMIT 1";

$result = $db_link->query($query);

if($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $apellido_alumno = trim((string)$row['apellido_paterno']) . ' ' . trim((string)$row['apellido_materno']) . ', ' . trim((string)$row['nombre_completo']);
    $fecha_nac = cambiaf_a_normal(trim((string)$row['fecha_nacimiento']));
    $nombre_ann = trim((string)$row['nombre_annlectivo']);

    $pdf->SetFont('Arial','',12);
    $pdf->SetXY(15,45);
    $pdf->RotatedText(20,60,'Alumno(a): ',0);
    $pdf->SetFont('Arial','IB',13);
    $pdf->RotatedText(50,60,convertirtexto($apellido_alumno),0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(20,70,convertirtexto('Número de Identificación Estudiantil (NIE): '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(112,70,convertirtexto($row['codigo_nie']),0);
    
    $pdf->SetFont('Arial','',12);
    $pdf->RotatedText(20,80,convertirtexto('Fecha de Nacimiento: '),0);
    $pdf->SetFont('Arial','B',13);
    $pdf->RotatedText(95,80,$fecha_nac,0);
    
    $pdf->SetFont('Comic','',56);
    $pdf->RotatedText(45,150,convertirtexto("PROMOCIÓN"),0);
    $pdf->RotatedText(80,180,convertirtexto($nombre_ann),0);
}

$pdf->Output();
?>