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
    $firma = $_REQUEST["chkfirma"];
    $sello = $_REQUEST["chksello"];
    $db_link = $dblink;
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_modalidad = trim($row['nombre_bachillerato']);
            $print_grado = trim($row['nombre_grado']);
            $print_seccion = trim($row['nombre_seccion']);
            $print_ann_lectivo = trim($row['nombre_ann_lectivo']);
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
    $this->MultiCell(65,4,$txt,0,'C');
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextNombre($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(55,4,$txt,0,'C',true);
	$this->Rotate(0);
}
//Cabecera de página
function Header()
{
    $this->SetFont('Arial','B',10);
}

//Pie de página
function Footer()
{

}
}
function carnet()
{
	global $nombres, $nies, $i;
	
}
//************************************************************************************************************************
// Creando el Informe.
//    $pdf=new PDF('P','mm',array(100,279));
	$pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(10, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);  
//Títulos de las columnas
    $pdf->AliasNbPages();
    $pdf->AddPage();
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////// variables y consulta a la tabla.
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      consultas(10,0,$codigo_all,'','','',$db_link,'');
      $filas = $result -> rowCount();
	  $fila_contar = 0;
		while($row = $result -> fetch(PDO::FETCH_BOTH))
		{
			$nombres[$fila_contar] = trim($row['nombres']);
			//$pdf->cell(3,3,$firma . " " . $sello,0,1);
				$fila_contar++;
		}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $valor_x = 18; $valor_y = 10; $espacio_horizontal = 0; $espacio_vertical = 10;
	$ancho_del_carnet = 85; $alto_del_carnet = 57; 
    $ancho_del_sello = 30; $alto_del_sello = 30;
	$ancho_del_firma = 45; $alto_del_firma = 17;
    
	// Espacio del cuadro de la foto.
	$espacio_x_sello = 50; $espacio_y_sello = 20;
	$espacio_x_firma = 10; $espacio_y_firma = 27;
	// Espacios
	$espacio_y_label_0 = 5; $espacio_x_label_0 = 10;	// institucion
	$espacio_y_label_1 = 50; $espacio_x_label_1 = 10;	// director
	
	$espacio_y_text_0 = 47; $espacio_x_text_0 = 10;	// nombre director

	
	// Fondo y Variables.
    $pdf->SetFillColor(238,238,238);
    $fill = false; $numero_carnet = 0;
	$salto_de_carnet = false;
	// array para las etiquetas del carnet.
	$etiqueta_carnet = array("Director");

	// Varible Imagen Sello y Firma
	//Logo
	$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];
	// Imagen de Fondo.
	$img_firma = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];
	//	recorre todos los registros de la consulta.
	for($i=0;$i<$filas;$i++)
	{
		// Crear el número de rectángulos necesarios.
		if($numero_carnet == 0)
		{
			if($salto_de_carnet == false){
				// Logo e imagen de fondo.
				if($sello == "yes"){$pdf->Image($img_sello,$valor_x+$espacio_x_sello,$valor_y+$espacio_y_sello,$ancho_del_sello,$alto_del_sello);}
                if($firma == "yes"){$pdf->Image($img_firma,$valor_x+$espacio_x_firma,$valor_y+$espacio_y_firma,$ancho_del_firma,$alto_del_firma);}
				// Ancho y Alto del Carnet.
				//$pdf->Rect($valor_x,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[0]),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','',10);
				$pdf->RotatedTextMultiCell($valor_x+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_x_text_0,$valor_y+$espacio_y_text_0,cambiar_de_del($_SESSION["nombre_director"]),0);
				
				$espacio_horizontal = 20;
				$salto_de_carnet = true;
			}
		}

		if($numero_carnet == 1)
		{
			if($salto_de_carnet == true){
				// Logo e imagen de fondo.
				if($sello == "yes"){$pdf->Image($img_sello,$valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_sello,$valor_y+$espacio_y_sello,$ancho_del_sello,$alto_del_sello);}
                if($firma == "yes"){$pdf->Image($img_firma,$valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_firma,$valor_y+$espacio_y_firma,$ancho_del_firma,$alto_del_firma);}
				// Ancho y Alto del Carnet.
				//$pdf->Rect($valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[0]),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','',10);
				$pdf->RotatedTextMultiCell($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_text_0,$valor_y+$espacio_y_text_0,cambiar_de_del($_SESSION["nombre_director"]),0);
				
				$espacio_horizontal = 20;
				$salto_de_carnet = false;
			}
		}

	
		if($numero_carnet == 2 or $numero_carnet == 4 or $numero_carnet == 6)
		{
			if($salto_de_carnet == false){
				$valor_y = $valor_y + $alto_del_carnet + $espacio_vertical;
				// Logo e imagen de fondo.
				if($sello == "yes"){$pdf->Image($img_sello,$valor_x+$espacio_x_sello,$valor_y+$espacio_y_sello,$ancho_del_sello,$alto_del_sello);}
                if($firma == "yes"){$pdf->Image($img_firma,$valor_x+$espacio_x_firma,$valor_y+$espacio_y_firma,$ancho_del_firma,$alto_del_firma);}
				// Ancho y Alto del Carnet.
				//$pdf->Rect($valor_x,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[0]),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','',10);
				$pdf->RotatedTextMultiCell($valor_x+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_x_text_0,$valor_y+$espacio_y_text_0,cambiar_de_del($_SESSION["nombre_director"]),0);
				
				$espacio_horizontal = 20;
				$salto_de_carnet = true;
			}
		}
	
		if($numero_carnet == 3 or $numero_carnet == 5 or $numero_carnet == 7)
		{
			if($salto_de_carnet == true){
				// Logo e imagen de fondo.
				if($sello == "yes"){$pdf->Image($img_sello,$valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_sello,$valor_y+$espacio_y_sello,$ancho_del_sello,$alto_del_sello);}
                if($firma == "yes"){$pdf->Image($img_firma,$valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_firma,$valor_y+$espacio_y_firma,$ancho_del_firma,$alto_del_firma);}
				// Ancho y Alto del Carnet.
				//$pdf->Rect($valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[0]),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','',10);
				$pdf->RotatedTextMultiCell($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_text_0,$valor_y+$espacio_y_text_0,cambiar_de_del($_SESSION["nombre_director"]),0);
				
				$espacio_horizontal = 0;
				$salto_de_carnet = false;
			}
		}
		// Decisión del Salto de Página cuando $numero_carnet sea igual a ocho
			$numero_carnet++;
			if($numero_carnet == 8){
				$numero_carnet = 0;
				$valor_y = 10;
				$pdf->Addpage();
			}
	}
// Salida del pdf.
    $pdf->Output();
?>