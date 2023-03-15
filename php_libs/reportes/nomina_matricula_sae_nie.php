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
            $print_bachillerato = iconv("UTF-8", "ISO-8859-1",'Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = iconv("UTF-8", "ISO-8859-1",'Grado: '.trim($row['nombre_grado']));
            $print_seccion = iconv("UTF-8", "ISO-8859-1",'Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = iconv("UTF-8", "ISO-8859-1",'Año Lectivo: '.trim($row['nombre_ann_lectivo']));
            $print_turno = iconv("UTF-8", "ISO-8859-1",'Turno: '.trim($row['nombre_turno']));

            $nombre_modalidad = iconv("UTF-8", "ISO-8859-1",trim($row['nombre_bachillerato']));
            $nombre_grado = iconv("UTF-8", "ISO-8859-1", trim($row['nombre_grado']));
            $nombre_seccion = iconv("UTF-8", "ISO-8859-1", trim($row['nombre_seccion']));
            $nombre_ann_lectivo = iconv("UTF-8", "ISO-8859-1", trim($row['nombre_ann_lectivo']));
            $nombre_turno = iconv("UTF-8", "ISO-8859-1", trim($row['nombre_turno']));
            $codigo_grado = trim($row['codigo_grado']);
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
    $this->Cell(150,4,iconv("UTF-8", "ISO-8859-1",$_SESSION['institucion']),0,1,'C');
    $this->Cell(190,4,iconv("UTF-8", "ISO-8859-1",'Nómina de Estudiantes'),0,1,'C');
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
    global $print_bachillerato, $print_grado, $print_ann_lectivo, $print_turno, $print_seccion;
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(224,235,255);$this->SetTextColor(0);$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('Arial','B',10);
    //Cabecera
    $w=array(10,12,15,80,76); //determina el ancho de las columnas
    $w2=array(10,5,12,80,75.5); //determina el ancho de las columnas

    //  imprimir datos del bachillerato.
    $this->Cell(80,7,$print_bachillerato,0,1,'L');
    $this->Cell(80,3,$print_grado,0,0,'L');
    $this->Cell(20,3,$print_seccion,0,0,'L');
    $this->Cell(50,3,$print_ann_lectivo,0,0,'L');
    $this->Cell(55,3,$print_turno,0,1,'L');
    // datos del encabezado de la tabla de datos de los estsudiantes.
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],6,iconv("UTF-8", "ISO-8859-1",$header[$i]),1,0,'C',false);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    //Datos
    $fill=false;}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter'); $data = array();
//Títulos de las columnas
    $header=array('Nº','Id','N I E','Nombre de Estudiantes','');
    $pdf->AliasNbPages(); $pdf->SetFont('Arial','',12);
    $pdf->SetTitle("Nomina de Estudiantes " . $codigo_grado . $nombre_seccion);  
    $pdf->SetSubject("Estudiantes");
    $pdf->AddPage();
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetY(18); $pdf->SetX(10);
    // fancy table.
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    // 
    $w=array(10,12,15,80,76); //determina el ancho de las columnas
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    // Recorrer la consulta de datos.
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $cambiar = true;
        while ($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $pdf->Cell($w[0],5,$i,'LR',0,'C',$fill);        // núermo correlativo
                $pdf->Cell($w[1],5,trim($row['id_alumno']),'LR',0,'C',$fill);  // id estudiante
                $pdf->Cell($w[2],5,trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
                $pdf->Cell($w[3],5,iconv("UTF-8", "ISO-8859-1",trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno

                $pdf->Cell($w[4],5,'','LR',1,'C',$fill);  // columna vacia
                // Salto de pagina si es igual ?
                    if($i == 45){
                        $pdf->Cell(array_sum($w),0,'','T');
                        $pdf->AddPage();
                        $pdf->FancyTable($header);          
                    }
                //$pdf->Ln();
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
	    
				    $pdf->Cell($w[4],5,'','LR',1,'C',$fill);  // col1
				        $fill=!$fill;
                }
// Cerrando Línea Final.
   $pdf->Cell(array_sum($w),0,'','T');
   $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).

     $nombre_archivo = $nombre_modalidad . " " . $nombre_grado . ' ' . $nombre_seccion . " " . $nombre_turno . " " . $nombre_ann_lectivo . "- Nomina.pdf";
        $pdf->Output($nombre_archivo,$modo);
?>