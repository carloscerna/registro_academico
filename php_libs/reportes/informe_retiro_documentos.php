<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
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
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
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
function Header()
{
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(0,4,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(0,4,utf8_decode('ASAMBLEA GENERAL DE PADRES/MADRES O ENCARGADOS'),0,1,'C');
    $this->Line(10,20,260,20);
    //Salto de línea
   // $this->Ln(20);
}

//Pie de página
function Footer()
{
  // Establecer formato para la fecha.
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
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(10,15,80,85,25,40); //determina el ancho de las columnas
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',1);
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
    $pdf=new PDF('L','mm','Letter');
    $data = array();
//Títulos de las columnas
    $header=array('Nº','ID','Nombre de Alumnos/as','Nombre de Padre, Madre o Encargado','DUI','FIRMA');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',13); // I : Italica; U: Normal;
    $pdf->SetY(15);
    $pdf->SetX(10);
    $pdf->ln();
// buscar la consulta y la ejecuta.
  consultas(4,0,$codigo_all,'','','',$db_link,'');
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    //  imprimir datos del bachillerato.
    $pdf->Cell(120,10,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,10,$print_grado,0,0,'L');
    $pdf->Cell(20,10,$print_seccion,0,0,'L');
    $pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
    $pdf->ln();
    
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

//  mostrar los valores de la consulta
    $w=array(10,15,80,85,25,40); //determina el ancho de las columnas
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $pdf->Cell($w[0],10,$i,'LR',0,'C',$fill);       // núermo correlativo
            $pdf->Cell($w[1],10,trim($row['id_alumno']),'LR',0,'C',$fill); // NIE
            $pdf->Cell($w[2],10,utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill);    // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[3],10,utf8_decode(trim($row['nombres'])),'LR',0,'L',$fill);    // nombre del encargado
            $pdf->Cell($w[4],10,'','LR',0,'C',$fill);    // tickert
            $pdf->Cell($w[5],10,' ','LR',0,'C',$fill);  // line ade la firma
            $pdf->ln();
                      // Salto de Línea.
            if($i==15 || $i == 30 || $i == 46 || $i == 60){
               $pdf->Cell(array_sum($w),0,'','T');  $pdf->AddPage();
                  // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                //  imprimir datos del bachillerato.
                    $pdf->Cell(120,10,$print_bachillerato,0,0,'L');
                    $pdf->Cell(40,10,$print_grado,0,0,'L');
                    $pdf->Cell(20,10,$print_seccion,0,0,'L');
                    $pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
                    $pdf->ln();
               $pdf->FancyTable($header);}
                $fill=!$fill;
                $i=$i+1;
            }
            $pdf->Cell(array_sum($w),0,'','T');
        
                  // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i; $jj = $i;
            
                //$linea_faltante =  45 - $numero;
				if($numero >=31 and $numero <=45){$linea_faltante = 15 - $numero;}
				if($jj <=16 ){$linea_faltante = 30 - $numero;}
				if($jj >=16 and $jj <=30){$linea_faltante = 30 - $numero;}
				if($jj >=31 and $jj <=45){$linea_faltante = 45 - $numero;}
				if($jj >=46 and $jj <=60){$linea_faltante = 60 - $numero;}

                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetX(10);
                      $pdf->Cell($w[0],10,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],10,'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],10,'','LR',0,'C',$fill);  // NIE
                      $pdf->Cell($w[3],10,'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[4],10,'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[5],10,'','LR',0,'C',$fill);  // nota final
                      $pdf->Ln();   
                      $fill=!$fill;
                      // Salto de Línea.
                        if($jj == 15 || $jj == 30 || $jj == 46 || $jj == 60){
                           $pdf->Cell(array_sum($w),0,'','T');  $pdf->AddPage();
                              // Definimos el tipo de fuente, estilo y tamaño.
                            $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                            //  imprimir datos del bachillerato.
                                $pdf->Cell(120,10,$print_bachillerato,0,0,'L');
                                $pdf->Cell(40,10,$print_grado,0,0,'L');
                                $pdf->Cell(20,10,$print_seccion,0,0,'L');
                                $pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
                                $pdf->ln();
                           $pdf->FancyTable($header);}
					$jj++;	// agregar el valor para el salto de página.
                  }
		// Cerrando Línea Final.
		$pdf->Cell(array_sum($w),0,'','T');
        // Imprimir datos de suma de masculino y femenino.
            $pdf->ln();
// Salida del pdf.
    $pdf->Output();
?>