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
	$fecha = date("Y");
	$fecha_vencimiento = "31/12/" . $fecha;

	$path_fotos = $_REQUEST["path_foto"];
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
// rotar texto funcion MultiCell()
function RotatedTextModalidad($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(55,4,$txt,0,'C',true);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextGrado($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(22,4,$txt,0,'L',true);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextSeccion($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(5,4,$txt,0,'C',true);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextNIE($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(20,5,$txt,0,'L',true);
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
	  $nombres = array(); $nies = array(); $ruta_foto = array(); 
	  $fila_contar = 0;
		while($row = $result -> fetch(PDO::FETCH_BOTH))
		{
			$nombres[$fila_contar] = trim($row['nombres']);
			$nies[$fila_contar] = trim($row['codigo_nie']);
			$fotos[$fila_contar] = trim($row['foto']);
			$codigo_genero = trim($row['codigo_genero']);
			$codigo_institucion = $_SESSION['codigo_institucion'];
			
			$fotos = trim($row['foto']);
		// Comprobar si existe el archivo.
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fotos/'.$codigo_institucion.'/'.$fotos))
		      {
				$ruta_foto[$fila_contar] = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fotos/'.$codigo_institucion.'/'.$fotos;	
		      }else if($codigo_genero == '01'){
					$fotos = 'avatar_masculino.png';
					$ruta_foto[$fila_contar] = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$fotos;
				}
				else{
					$fotos = 'avatar_femenino.png';
					$ruta_foto[$fila_contar] = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$fotos;
				}
				$fila_contar++;
		}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $i=1;  $suma = 0; $contador_de_registros = 1;  $j=0;
    $ancho_del_carnet = 85; $alto_del_carnet = 57; $valor_x = 10; $valor_y = 10; $espacio_horizontal = 0; $espacio_vertical = 10;
    $espacio_nie_fecha = 56; $espacio_grado = 50;
    $espacio_logo_escudo_x = 1; $espacio_logo_escudo_y = 1;
    $espacio_firma = 30;
    
	// Espacio del cuadro de la foto.
	$tamaño_foto_x = 14; $tamaño_foto_y = 16;
	$valor_x_foto = 10; $valor_y_foto = 27; $espacio_y_foto = 17;
    $ancho_de_la_foto = 25; $alto_de_la_foto = 33;
	// Espacios
	$espacio_y_label_0 = 16; $espacio_x_label_0 = 20;	// CARNET ESTUDIANTIL
	$espacio_y_label_1 = 21; $espacio_x_label_1 = 26;	// NOMBRE DEL ALUMNO8A)
	$espacio_y_label_2 = 35; $espacio_x_label_2 = 26;	// MODALIDAD
	$espacio_y_label_3 = 50; $espacio_x_label_3 = 26;	// GRADO
	$espacio_y_label_4 = 50; $espacio_x_label_4 = 62;	// SECCIÓN
	$espacio_y_label_5 = 55; $espacio_x_label_5 = 1;	// NIE
	$espacio_y_label_6 = 55; $espacio_x_label_6 = 38;	// FECHA DE VENCIMIENTO
	
	$espacio_y_text_0 = 2; $espacio_x_text_0 = 15;	// NOMBRE DE LA INSTITUCION.
	$espacio_y_text_1 = 22; $espacio_x_text_1 = 28;	// TEXT NOMBRE DEL ALUMNO
	$espacio_y_text_2 = 37; $espacio_x_text_2 = 28;	// TEXT MODALIDAD
	$espacio_y_text_3 = 47; $espacio_x_text_3 = 39;	// TEXT GRADO
	$espacio_y_text_4 = 47; $espacio_x_text_4 = 78;	// TEXT SECCION
	$espacio_y_text_5 = 51; $espacio_x_text_5 = 10;	// TEXT NIE
	$espacio_y_text_6 = 37; $espacio_x_text_6 = 28;	// TEXT FECHA DE VENCIOMIENTO
	
	// Fondo y Variables.
    $pdf->SetFillColor(238,238,238);
    $fill = false; $numero_carnet = 0;
	$salto_de_carnet = false;
	// array para las etiquetas del carnet.
	$etiqueta_carnet = array("CARNET ESTUDIANTIL","Nombre del Alumno(a)","Modalidad","Grado:","Sección:","NIE:","Fecha de Vencimiento:");

	// Varible Imagen Logo e Fondo Carnet
	//Logo
	$img_logo = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
	// Imagen de Fondo.
	$img_fondo = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/carnet.png';
	//	recorre todos los registros de la consulta.
	for($i=0;$i<$filas;$i++)
	{
		// Crear el número de rectángulos necesarios.
		if($numero_carnet == 0)
		{
			if($salto_de_carnet == false){
				// Logo e imagen de fondo.
                $pdf->Image($img_fondo,$valor_x,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				$pdf->Image($img_logo,$valor_x+$espacio_logo_escudo_x,$valor_y+$espacio_logo_escudo_y,$tamaño_foto_x,$tamaño_foto_y);
				// Ancho y Alto del Carnet.
				$pdf->Rect($valor_x,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Cuadro de la Foto.
				$pdf->rect($valor_x_foto,$valor_y_foto,$ancho_de_la_foto,$alto_de_la_foto);
				$pdf->Image($ruta_foto[$i],$valor_x_foto+1,$valor_y_foto+1,$ancho_de_la_foto-2,$alto_de_la_foto-2);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','B',16);
				$pdf->RotatedText($valor_x+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($etiqueta_carnet[0]),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedText($valor_x+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[1]),0);
				$pdf->RotatedText($valor_x+$espacio_x_label_2,$valor_y+$espacio_y_label_2,utf8_decode($etiqueta_carnet[2]),0);
				$pdf->RotatedText($valor_x+$espacio_x_label_3,$valor_y+$espacio_y_label_3,utf8_decode($etiqueta_carnet[3]),0);				
				$pdf->RotatedText($valor_x+$espacio_x_label_4,$valor_y+$espacio_y_label_4,utf8_decode($etiqueta_carnet[4]),0);
				$pdf->SetFont('Arial','B',13);
				$pdf->RotatedText($valor_x+$espacio_x_label_5,$valor_y+$espacio_y_label_5,utf8_decode($etiqueta_carnet[5]),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedText($valor_x+$espacio_x_label_6,$valor_y+$espacio_y_label_6,utf8_decode($etiqueta_carnet[6] . " " . $fecha_vencimiento),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextMultiCell($valor_x+$espacio_x_text_0,$valor_y+$espacio_y_text_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextNombre($valor_x+$espacio_x_text_1,$valor_y+$espacio_y_text_1,utf8_decode($nombres[$i]),0);
				$pdf->SetFont('Arial','B',9);
				$pdf->RotatedTextModalidad($valor_x+$espacio_x_text_2,$valor_y+$espacio_y_text_2,utf8_decode($print_modalidad),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedTextGrado($valor_x+$espacio_x_text_3,$valor_y+$espacio_y_text_3,utf8_decode($print_grado),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedTextSeccion($valor_x+$espacio_x_text_4,$valor_y+$espacio_y_text_4,utf8_decode($print_seccion),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedTextNIE($valor_x+$espacio_x_text_5,$valor_y+$espacio_y_text_5,utf8_decode($nies[$i]),0);
				
				$espacio_horizontal = 20;
				$salto_de_carnet = true;
			}
		}

		if($numero_carnet == 1)
		{
			if($salto_de_carnet == true){
				// Logo e imagen de fondo.
                $pdf->Image($img_fondo,$valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				$pdf->Image($img_logo,$valor_x+$espacio_horizontal+$espacio_logo_escudo_x+$ancho_del_carnet,$valor_y+$espacio_logo_escudo_y,$tamaño_foto_x,$tamaño_foto_y);
				// Ancho y Alto del Carnet.
				$pdf->Rect($valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Cuadro de la Foto.
				$pdf->rect($valor_x_foto+$espacio_horizontal+$ancho_del_carnet,$valor_y_foto,$ancho_de_la_foto,$alto_de_la_foto);
				$pdf->Image($ruta_foto[$i],$valor_x_foto+1+$espacio_horizontal+$ancho_del_carnet,$valor_y_foto+1,$ancho_de_la_foto-2,$alto_de_la_foto-2);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','B',16);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($etiqueta_carnet[0]),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[1]),0);				
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_2,$valor_y+$espacio_y_label_2,utf8_decode($etiqueta_carnet[2]),0);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_3,$valor_y+$espacio_y_label_3,utf8_decode($etiqueta_carnet[3]),0);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_4,$valor_y+$espacio_y_label_4,utf8_decode($etiqueta_carnet[4]),0);
				$pdf->SetFont('Arial','B',13);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_5,$valor_y+$espacio_y_label_5,utf8_decode($etiqueta_carnet[5]),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_6,$valor_y+$espacio_y_label_6,utf8_decode($etiqueta_carnet[6]  . " " . $fecha_vencimiento),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextMultiCell($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_0,$valor_y+$espacio_y_text_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextNombre($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_1,$valor_y+$espacio_y_text_1,utf8_decode($nombres[$i]),0);
				$pdf->SetFont('Arial','B',9);
				$pdf->RotatedTextModalidad($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_2,$valor_y+$espacio_y_text_2,utf8_decode($print_modalidad),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedTextGrado($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_3,$valor_y+$espacio_y_text_3,utf8_decode($print_grado),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextSeccion($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_4,$valor_y+$espacio_y_text_4,utf8_decode($print_seccion),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedTextNIE($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_5,$valor_y+$espacio_y_text_5,utf8_decode($nies[$i]),0);
				
				$espacio_horizontal = 0;
				$salto_de_carnet = false;
			}
		}
		
		if($numero_carnet == 2 or $numero_carnet == 4 or $numero_carnet == 6)
		{
			if($salto_de_carnet == false){
				$valor_y = $valor_y + $alto_del_carnet + $espacio_vertical;
				// Logo e imagen de fondo.
                $pdf->Image($img_fondo,$valor_x+$espacio_horizontal,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				$pdf->Image($img_logo,$valor_x+$espacio_horizontal,$valor_y+$espacio_logo_escudo_y,$tamaño_foto_x,$tamaño_foto_y);
				// Ancho y Alto del Carnet.
				$pdf->Rect($valor_x+$espacio_horizontal,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Cuadro de la Foto.
				$pdf->rect($valor_x_foto+$espacio_horizontal,$valor_y+$espacio_y_foto,$ancho_de_la_foto,$alto_de_la_foto);
				$pdf->Image($ruta_foto[$i],$espacio_horizontal+$valor_x_foto+1,$valor_y+$espacio_y_foto+1,$ancho_de_la_foto-2,$alto_de_la_foto-2);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','B',16);
				$pdf->RotatedText($valor_x+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($etiqueta_carnet[0]),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedText($valor_x+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[1]),0);				
				$pdf->RotatedText($valor_x+$espacio_x_label_2,$valor_y+$espacio_y_label_2,utf8_decode($etiqueta_carnet[2]),0);
				$pdf->RotatedText($valor_x+$espacio_x_label_3,$valor_y+$espacio_y_label_3,utf8_decode($etiqueta_carnet[3]),0);
				$pdf->RotatedText($valor_x+$espacio_x_label_4,$valor_y+$espacio_y_label_4,utf8_decode($etiqueta_carnet[4]),0);
				$pdf->SetFont('Arial','B',13);
				$pdf->RotatedText($valor_x+$espacio_x_label_5,$valor_y+$espacio_y_label_5,utf8_decode($etiqueta_carnet[5]),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedText($valor_x+$espacio_x_label_6,$valor_y+$espacio_y_label_6,utf8_decode($etiqueta_carnet[6] . " " . $fecha_vencimiento),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextMultiCell($valor_x+$espacio_x_text_0,$valor_y+$espacio_y_text_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextNombre($valor_x+$espacio_x_text_1,$valor_y+$espacio_y_text_1,utf8_decode($nombres[$i]),0);
				$pdf->SetFont('Arial','B',9);
				$pdf->RotatedTextModalidad($valor_x+$espacio_x_text_2,$valor_y+$espacio_y_text_2,utf8_decode($print_modalidad),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedTextGrado($valor_x+$espacio_x_text_3,$valor_y+$espacio_y_text_3,utf8_decode($print_grado),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextSeccion($valor_x+$espacio_x_text_4,$valor_y+$espacio_y_text_4,utf8_decode($print_seccion),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedTextNIE($valor_x+$espacio_x_text_5,$valor_y+$espacio_y_text_5,utf8_decode($nies[$i]),0);
				
				$espacio_horizontal = 20;
				$salto_de_carnet = true;
			}
		}
		
		if($numero_carnet == 3 or $numero_carnet == 5 or $numero_carnet == 7)
		{
			if($salto_de_carnet == true){
				// Logo e imagen de fondo.
                $pdf->Image($img_fondo,$valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				$pdf->Image($img_logo,$valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y+$espacio_logo_escudo_y,$tamaño_foto_x,$tamaño_foto_y);
				// Ancho y Alto del Carnet.
				$pdf->Rect($valor_x+$espacio_horizontal+$ancho_del_carnet,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Cuadro de la Foto.
				$pdf->rect($valor_x_foto+$espacio_horizontal+$ancho_del_carnet,$valor_y+$espacio_y_foto,$ancho_de_la_foto,$alto_de_la_foto);
				$pdf->Image($ruta_foto[$i],$espacio_horizontal+$valor_x_foto+1+$ancho_del_carnet,$valor_y+$espacio_y_foto+1,$ancho_de_la_foto-2,$alto_de_la_foto-2);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','B',16);
				$pdf->RotatedText($valor_x+$espacio_horizontal+$ancho_del_carnet+$espacio_x_label_0,$valor_y+$espacio_y_label_0,utf8_decode($etiqueta_carnet[0]),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[1]),0);				
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_2,$valor_y+$espacio_y_label_2,utf8_decode($etiqueta_carnet[2]),0);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_3,$valor_y+$espacio_y_label_3,utf8_decode($etiqueta_carnet[3]),0);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_4,$valor_y+$espacio_y_label_4,utf8_decode($etiqueta_carnet[4]),0);
				$pdf->SetFont('Arial','B',13);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_5,$valor_y+$espacio_y_label_5,utf8_decode($etiqueta_carnet[5]),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedText($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_label_6,$valor_y+$espacio_y_label_6,utf8_decode($etiqueta_carnet[6] . " " . $fecha_vencimiento),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextMultiCell($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_0,$valor_y+$espacio_y_text_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextNombre($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_1,$valor_y+$espacio_y_text_1,utf8_decode($nombres[$i]),0);
				$pdf->SetFont('Arial','B',9);
				$pdf->RotatedTextModalidad($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_2,$valor_y+$espacio_y_text_2,utf8_decode($print_modalidad),0);
				$pdf->SetFont('Arial','B',8);
				$pdf->RotatedTextGrado($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_3,$valor_y+$espacio_y_text_3,utf8_decode($print_grado),0);
				$pdf->SetFont('Arial','B',10);
				$pdf->RotatedTextSeccion($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_4,$valor_y+$espacio_y_text_4,utf8_decode($print_seccion),0);
				$pdf->SetFont('Arial','B',11);
				$pdf->RotatedTextNIE($valor_x+$ancho_del_carnet+$espacio_horizontal+$espacio_x_text_5,$valor_y+$espacio_y_text_5,utf8_decode($nies[$i]),0);
				
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