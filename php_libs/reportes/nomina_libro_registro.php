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
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
			
			$nombre_seccion = utf8_decode(trim($row['nombre_seccion']));
	    break;
            }
        
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $print_nombre_docente;
    $this->SetFont('Arial','',13);
    //Movernos a la derecha
    $this->Cell(20);
    $this->SetFont('Arial','',11);
    $this->Cell(15);
}

//Pie de página
function Footer()
{
	global $nombre_grado, $nombre_seccion;
    //Posición: a 1,5 cm del final
    $this->SetY(-4);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,$nombre_grado.' - '.$nombre_seccion,0,0,'C');
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
    $w=array(9,51,100,10,15,16); //determina el ancho de las columnas
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
    $pdf=new PDF('P','mm','Legal');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    $data = array();
//Títulos de las columnas
    $header=array('Nº','N I E','Nombre de Alumnos/as','Edad','Género','Sección');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(5);
    $pdf->SetX(5);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// buscar la consulta y la ejecuta.
  consultas(4,0,$codigo_all,'','','',$db_link,'');
// Evaluar si existen registros.
   // $fila = pg_num_rows($result);
    if($result -> rowCount() != 0){
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;

    $pdf->ln();
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;

    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

    $w=array(9,51,100,10,15,16); //determina el ancho de las columnas
    //$ancho_libro = array(6.7); // para hp - oficio
    //$ancho_libro = array(6.3); // CANON TAMAÑO LEGAL PARTE 1
    $ancho_libro = array(6.1); // CANON TAMAÑO LEGAL PARTE 2
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $repitentem = 0; $repitentef = 0; $totalrepitente = 0; $sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
		while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $pdf->Cell($w[0],$ancho_libro[0],$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],$ancho_libro[0],trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[2],$ancho_libro[0],utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[3],$ancho_libro[0],$row['edad'],'LR',0,'C',$fill);  // edad
            $pdf->Cell($w[4],$ancho_libro[0],strtoupper($row['genero']),'LR',0,'C',$fill);    // genero

            $pdf->Cell($w[5],$ancho_libro[0],utf8_decode($nombre_seccion),'LR',0,'C',$fill);

            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;       
        } //cierre del do while.          
    }   // condicion si existen registros.
else{
    // si no existen registros.
     $pdf->Cell(150,7,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
}

// Salida del pdf.
	$modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
	$print_nombre = $print_grado . ' ' . $print_seccion;
	$pdf->Output($print_nombre,$modo);
?>