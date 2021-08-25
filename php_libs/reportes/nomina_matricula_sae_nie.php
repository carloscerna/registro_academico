<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Archivos que se incluyen.
     include($path_root."/registro_academico/includes/funciones.php");
     include($path_root."/registro_academico/includes/consultas.php");
     include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
     include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
     header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
     $codigo_all = $_REQUEST["todos"];
     $db_link = $dblink;
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while ($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
	    break;
            }
	    
class PDF extends FPDF
{
//Cabecera de página
function Header(){
    //Logo
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_uno']; $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(150,4,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(190,4,utf8_decode('Nómina de Alumnos/as'),0,1,'C');
    $this->Line(10,20,200,20);}

//Pie de página
function Footer(){
  // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');						
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y "); $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');}

//Tabla coloreada
function FancyTable($header){
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(224,235,255);$this->SetTextColor(0);$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);$this->SetFont('','B');
    //Cabecera
    $w=array(10,12,15,80,76); //determina el ancho de las columnas
    $w2=array(10,5,12,80,75.5); //determina el ancho de las columnas
       
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],6,utf8_decode($header[$i]),1,0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);$this->SetTextColor(0);$this->SetFont('');
    //Datos
    $fill=false;}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter'); $data = array();
//Títulos de las columnas
    $header=array('Nº','Id','N I E','Nombre de Alumnos/as','');
    $pdf->AliasNbPages(); $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(18); $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
//  imprimir datos del bachillerato.
    $pdf->Cell(80,10,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,10,$print_grado,0,0,'L');
    $pdf->Cell(20,10,$print_seccion,0,0,'L');
    $pdf->Cell(35,10,$print_ann_lectivo,0,1,'L');
// Salto de línea.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    //cabecera
    $w=array(10,12,15,80,76); //determina el ancho de las columnas
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $cambiar = true;
        while ($row = $result -> fetch(PDO::FETCH_BOTH))
            {

            $pdf->Cell($w[0],5,$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],5,trim($row['id_alumno']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[2],5,trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[3],5,utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno

            $pdf->Cell($w[4],5,'','LR',0,'C',$fill);  // col1
                  
            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;	// conteo de i++
        } //cierre del do while.          
             // rellenar con las lineas que faltan y colocar total de puntos y promedio.
                $numero = $i;
                $linea_faltante =  45 - $numero;
                $numero_p = $numero - 1;
		     for($i=0;$i<=$linea_faltante;$i++){
				$pdf->SetX(10);
				$pdf->Cell($w[0],5,$numero++,'LR',0,'C',$fill);  // N| de Orden.
				$pdf->Cell($w[1],5,'','LR',0,'l',$fill);  // nombre del alumno.
				$pdf->Cell($w[2],5,'','LR',0,'C',$fill);  // id
        $pdf->Cell($w[3],5,'','LR',0,'C',$fill);  // NIE
	    
				$pdf->Cell($w[4],5,'','LR',0,'C',$fill);  // col1
				$pdf->Ln();   
				$fill=!$fill;}
// Cerrando Línea Final.
   $pdf->Cell(array_sum($w),0,'','T');
   $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
     $print_nombre = $print_grado . ' ' . $print_seccion . ".pdf";
     $pdf->Output($print_nombre,$modo);
?>