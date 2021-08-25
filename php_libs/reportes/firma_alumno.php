<?php
session_start();
 include("$_SESSION[path_root]/registro_academico/inc/conexion_db2.php");
 include("$_SESSION[path_root]/registro_academico/inc/consultas.php");
// Llamar a la libreria fpdf
 include("$_SESSION[path_root]/registro_academico/fpdf/fpdf.php");
// variables y consulta a la tabla.
  $codigo_all = $_REQUEST["txtall"];
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while ($row=pg_fetch_assoc($result_encabezado))
            {
            $print_bachillerato ='Bachillerato o ciclo: '.trim($row['nombre_bachillerato']);
            $print_grado = 'Grado: '.trim($row['nombre_grado']);
            $print_seccion = 'Sección: '.trim($row['nombre_seccion']);
            $print_ann_lectivo = 'Año Lectivo: '.trim($row['nombre_ann_lectivo']);
	    break;
            }

class PDF extends FPDF
{
// rotar texto funcion TEXT()
function RotatedText($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
  $this->Text($x,$y,$txt);
	$this->Rotate(0);
}

// rotar texto funcion MultiCell()
function RotatedTextMultiCell($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
  $this->MultiCell(43,4,$txt,0,'L');
	$this->Rotate(0);
}

function RotatedTextMultiCellAspectos($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
  $this->MultiCell(43,3,$txt,0,'L');
	$this->Rotate(0);
}

//Cabecera de página
function Header()
{
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,20,15,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Título
    $this->Cell(200,4,$_SESSION['institucion'],0,1,'C');
    $this->Cell(200,4,'NÓMINA DE ALUMNOS',0,1,'C');
    //Salto de línea
   // $this->Ln(20);
}

//Pie de página
function Footer()
{
  //
  // Establecer formato para la fecha.
  // 
  		date_default_timezone_set('America/El_Salvador');
   		setlocale(LC_TIME, 'spanish');
	//
							
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255);
    $this->SetTextColor(0,0,0);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(5,80,99); //determina el ancho de las columnas
    $w1=array(9); //determina el ancho de las columnas
    
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C',1);

    $this->SetFillColor(255,255,255);


    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    //Datos
    $fill=false;
}
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(20, 20, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,10);
    
    $data = array();
//Títulos de las columnas
    $header=array('Nº','Nombre de Alumnos/as','');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
    // variables y consulta a la tabla.
       $codigo_all = $_REQUEST["txtall"];
      consultas(4,0,$codigo_all,'','','',$db_link,'');
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    //  imprimir datos del bachillerato.
    $pdf->Cell(100,10,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,10,$print_grado,0,0,'L');
    $pdf->Cell(20,10,$print_seccion,0,0,'L');
    $pdf->Cell(20,10,$print_ann_lectivo,0,0,'L');
    $pdf->ln();
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;

    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.     
    $w=array(5,80,99); //determina el ancho de las columnas
    
    $fill = false; $i=1;
        while ($row=pg_fetch_assoc($result))
            {
            $pdf->Cell($w[0],7,$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],7,trim($row['apellido_alumno']),1,0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[2],7,'','LR',0,'C',$fill);    // lineas de ancho 7.
            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;
                
                if($i==29 || $i == 58){
                	$pdf->Cell(array_sum($w)+(9*11),0,'','T');
			$pdf->SetMargins(5, 20, 5);
			$pdf->AddPage();
				//  imprimir datos del bachillerato.
				$pdf->Cell(100,10,$print_bachillerato,0,0,'L');
				$pdf->Cell(40,10,$print_grado,0,0,'L');
				$pdf->Cell(20,10,$print_seccion,0,0,'L');
				$pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
				$pdf->ln();
			$pdf->FancyTable($header);}
            }
        
                  // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                      $pdf->Cell($w[0],5.8,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],5.8,'','LR',0,'l',$fill);  // nombre del alumno.
		       $pdf->Cell($w[2],5.8,'','LR',0,'C',$fill);    // lineas de ancho 7.
                      $pdf->Ln();   
                      $fill=!$fill;
                      
                      // Salto de Línea.
        		if($numero == 29 || $numero == 58){
		           $pdf->Cell(array_sum($w)+9*11,0,'','B');
			   $pdf->AddPage();
				//  imprimir datos del bachillerato.
				$pdf->Cell(100,10,$print_bachillerato,0,0,'L');
				$pdf->Cell(40,10,$print_grado,0,0,'L');
				$pdf->Cell(20,10,$print_seccion,0,0,'L');
				$pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
				$pdf->ln();
			   $pdf->FancyTable($header);}
                  }
            $pdf->Cell(array_sum($w),0,'','T');
// Salida del pdf.
    $pdf->Output();
?>