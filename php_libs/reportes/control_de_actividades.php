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
	$print_nombre = "";
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
// buscar la consulta y la ejecuta.
    consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $nombre_modalidad = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado:     '.trim($row['nombre_grado']));
            $nombre_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección:  '.trim($row['nombre_seccion']));
            $nombre_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
            $nombre_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
            $print_periodo = utf8_decode('Período: _____');
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
    //  Variables globales.
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_periodo, $pagina_impar;
    if($pagina_impar == false){
        //Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
        $this->Image($img,20,10,12,15);
        //Arial bold 15
        $this->SetFont('Arial','B',13);
        //Título
        $this->RotatedText(35,10,utf8_decode($_SESSION['institucion']),0);
        $this->RotatedText(35,15,'Control de Actividades',0,1,'L');
        
        $this->SetFont('Arial','',9);
        // Imprimir Modalidad y Asignatura.
        $this->RoundedRect(34, 16, 130, 6, 1.5, '1234', '');
        $this->RotatedText(35,20.5,$print_bachillerato,0);
        // Nombre Asignatura.
        $this->RoundedRect(34, 22, 130, 6, 1.5, '1234', '');
        $this->RotatedText(35,26,'Nombre Asignatura: ',0);
        // Nombre Docente
        $this->RoundedRect(34, 28, 130, 6, 1.5, '1234', '');
        $this->RotatedText(35,32.5,'Nombre Docente: ',0);
        
    // Generar el cuadro en donde se ubicara el grado, sección y año lectivo.
        $this->RoundedRect(169, 10, 35, 20, 3.5, '1234', '');
        $this->RotatedText(170,15,$print_grado,0);
        $this->RotatedText(170,19,$print_seccion,0);
        $this->RotatedText(170,23,$print_ann_lectivo,0);
        $this->RotatedText(170,27,$print_periodo,0);
    // Generar el cuadro que contiene la información de la cuadricula, nº, nombre, actividades  y %.
        $this->RoundedRect(20, 35, 184, 55, .5, '');    // Principal
        $this->RoundedRect(20, 35, 5, 55, .5, '');    // Nº
        $this->RoundedRect(25, 35, 80, 55, .5, '');    // Nombre
        $this->RotatedText(24, 75, utf8_decode('Nº de Orden'), 90);    // Nombre
        $this->RotatedText(40, 70, utf8_decode('Orden Alfabético por Apellido'), 0);    // Nombre
    // Línea Horizontal. Actividades Realizadas.
        $this->RoundedRect(105, 35, 99, 5, .5, '');
        $this->RotatedText(125, 39, utf8_decode('PRUEBAS Y ACTIVIDADES REALIZADAS'), 0);    // Nombre
    // Línea Horizontal. Porcentajes
        $this->RoundedRect(105, 40, 99, 5, .5, '');
        $this->RoundedRect(25, 35, 80, 10, .5, '');    // Porcentaje.
        $this->RotatedText(75, 44, utf8_decode('PORCENTAJES (%)'), 0);    // Nombre
    // Líneas Verticales para la cuadricula.
        $mov_izq = 105;
        $ancho_1 = 9;
        for($j=0;$j<=10;$j++)
        {
        $this->RoundedRect($mov_izq, 40, $ancho_1, 50, .5, '');  // cuadros verticales
        $mov_izq = $mov_izq + $ancho_1;
        }
    }   // decisión para mover el primer cuadro.
    
    if($pagina_impar == true){
        //Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
        $this->Image($img,10,10,12,15);
        //Arial bold 15
        $this->SetFont('Arial','B',13);
        //Título
        $this->RotatedText(25,10,utf8_decode($_SESSION['institucion']),0);
        $this->RotatedText(25,15,'Control de Actividades',0,1,'L');
        
        $this->SetFont('Arial','',9);
        // Imprimir Modalidad y Asignatura.
        $this->RoundedRect(24, 16, 130, 6, 1.5, '1234', '');
        $this->RotatedText(25,20.5,$print_bachillerato,0);
        // Nombre Asignatura.
        $this->RoundedRect(24, 22, 130, 6, 1.5, '1234', '');
        $this->RotatedText(25,26,'Nombre Asignatura: ',0);
        // Nombre Docente
        $this->RoundedRect(24, 28, 130, 6, 1.5, '1234', '');
        $this->RotatedText(25,32.5,'Nombre Docente: ',0);
        
    // Generar el cuadro en donde se ubicara el grado, sección y año lectivo.
        $this->RoundedRect(159, 10, 35, 20, 3.5, '1234', '');
        $this->RotatedText(160,15,$print_grado,0);
        $this->RotatedText(160,19,$print_seccion,0);
        $this->RotatedText(160,23,$print_ann_lectivo,0);
        $this->RotatedText(160,27,$print_periodo,0);
    // Generar el cuadro que contiene la información de la cuadricula, nº, nombre, actividades  y %.
        $this->RoundedRect(10, 35, 184, 55, .5, '');    // Principal
        $this->RoundedRect(10, 35, 5, 55, .5, '');    // Nº
        $this->RoundedRect(15, 35, 80, 55, .5, '');    // Nombre
        $this->RotatedText(14, 75, utf8_decode('Nº de Orden'), 90);    // Nombre
        $this->RotatedText(30, 70, utf8_decode('Orden Alfabético por Apellido'), 0);    // Nombre
    // Línea Horizontal. Actividades Realizadas.
        $this->RoundedRect(95, 35, 99, 5, .5, '');
        $this->RotatedText(125, 39, utf8_decode('ACTIVIDADES REALIZADAS'), 0);    // Nombre
    // Línea Horizontal. Porcentajes
        $this->RoundedRect(95, 40, 99, 5, .5, '');
        $this->RoundedRect(15, 35, 80, 10, .5, '');    // Porcentaje.
        $this->RotatedText(65, 44, utf8_decode('PORCENTAJES (%)'), 0);    // Nombre
    // Líneas Verticales para la cuadricula.
        $mov_izq = 95;
        $ancho_1 = 9;
        for($j=0;$j<=10;$j++)
        {
        $this->RoundedRect($mov_izq, 40, $ancho_1, 50, .5, '');  // cuadros verticales
        $mov_izq = $mov_izq + $ancho_1;
        }        
    }
    
    // Ubicación en donde empezará a imprimirlos valores.
    $this->SetY(90);
    // colores del fondo, texto, línea.
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    //Datos
    $fill=false;
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
    $this->SetY(-15);
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
 }
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(20, 10, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,10);
    
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',9);
    $pdf->AddPage();
    // variables y consulta a la tabla.
      consultas(4,0,$codigo_all,'','','',$db_link,'');

    $w=array(5,80,9); //determina el ancho de las columnas
    
    // colores del fondo, texto, línea.
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    // Variables a utilizar
    $fill = false; $i=1; $pagina_impar = false;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {           
            // Contabiliza el total de lineas para otra página o continuar en la misma.    
                if($i==26 || $i == 55){
                        $pagina_impar = true;
                	$pdf->Cell(array_sum($w)+(9*10),0,'','T');
			$pdf->SetMargins(10, 10, 5);
                        $pdf->AddPage();
                        }
                        
                    $pdf->Cell($w[0],7,$i,'LR',0,'C',$fill);        // número correlativo
                    $pdf->Cell($w[1],7,utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
                
                // Bloque que genera la cuadricula en total son 12.
                    for($j=0;$j<=10;$j++)
                    $pdf->Cell($w[2],7,'','LR',0,'C',$fill);    // lineas de ancho 7.
                    $pdf->Ln();
                                
                    $fill=!$fill;
                    $i=$i+1;
            }
            ///////////////////////////////////////////////////////////////////////////////////////
            // rellenar con las lineas que faltan y colocar total de puntos y promedio.
            //////////////////////////////////////////////////////////////////////////////////////
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                      $pdf->Cell($w[0],7,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],7,'','LR',0,'l',$fill);  // nombre del alumno.
																				
			for($j=0;$j<=10;$j++)											
                  		$pdf->Cell($w[2],7,'','LR',0,'C',$fill);    // lineas de ancho 7.
											
                      $pdf->Ln();   
                      $fill=!$fill;
                      
                      // Salto de Línea.
        		if($numero == 26 || $numero == 55){
		           $pdf->Cell(array_sum($w)+9*10,0,'','B');
			   $pdf->AddPage();
                          }
                  }
// Cierre de la Línea Final.        
   $pdf->Cell(array_sum($w)+(9*10),0,'','T');
// Salida del pdf.
    $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
    $print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-CA.pdf';
    $pdf->Output($print_nombre,$modo);
?>