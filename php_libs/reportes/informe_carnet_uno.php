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

	//$path_fotos = $_REQUEST["path_foto"];
	$codigo_institucion = $_SESSION['codigo_institucion'];
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
			
            $codigo_grado = trim($row['codigo_grado']);
			if($codigo_grado >= '01' and $codigo_grado <= "09"){$codigo_grado = substr($codigo_grado,1,1);}
			if($codigo_grado == "11"){$codigo_grado = "2";}
			if($codigo_grado == "10"){$codigo_grado = "1";}
            $codigo_seccion = trim($row['codigo_seccion']);
			
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
    $this->MultiCell(75,4,$txt,0,'C');
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextMultiCellInstitucion($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(70,5,$txt,0,'C');
	$this->Rotate(0);
}

// rotar texto funcion MultiCell()
function RotatedTextNombre($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(55,4,$txt,0,'C',false);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextModalidad($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(55,4,$txt,0,'C',false);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextGrado($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(22,4,$txt,0,'L',false);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextFN($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(25,4,$txt,0,'C',false);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextNIE($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(25,5,$txt,0,'R',false);
	$this->Rotate(0);
}
// rotar texto funcion MultiCell()
function RotatedTextCarnet($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
    $this->MultiCell(75,4,$txt,0,'C',false);
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
    $pdf=new PDF('L','mm',array(85.852,54.102));
	//$pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(1, 1, 1);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,1);  
//Títulos de las columnas
    $pdf->AliasNbPages();
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////// variables y consulta a la tabla.
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      consultas(10,0,$codigo_all,'','','',$db_link,'');
      $filas = $result -> rowCount();
	  $nombres = array(); $nies = array(); $ruta_foto = array(); 
	  $fila_contar = 0;
		while($row = $result -> fetch(PDO::FETCH_BOTH))
		{
			$nombres[$fila_contar] = trim($row['nombre_completo']);
			$apellidos[$fila_contar] = trim($row['apellido_paterno']) .' ' . trim($row['apellido_materno']);
			$nombre_turno[$fila_contar] = trim($row['nombre_turno']);
			$fecha_nacimiento[$fila_contar] = cambiaf_a_normal(trim($row['fecha_nacimiento']));
			$nies[$fila_contar] = trim($row['codigo_nie']);
			$fotos[$fila_contar] = trim($row['foto']);
			
			if(trim($row['foto']) == ""){$fotos = 'nofoto.jpg';}else{$fotos = trim($row['foto']);}
		// Comprobar si existe el archivo.
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fotos/'.$codigo_institucion.'/'.$fotos))
		      {
				$ruta_foto[$fila_contar] = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fotos/'.$codigo_institucion.'/'.$fotos;	
		      }else{
				$fotos = 'no.jpg';
				$ruta_foto[$fila_contar] = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$fotos;
		      }
				$fila_contar++;
		}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $i=1;  $suma = 0; $contador_de_registros = 1;  $j=0;
    $ancho_del_carnet = 85.852; $alto_del_carnet = 54.102; $valor_x = 0; $valor_y = 0; $espacio_horizontal = 0; $espacio_vertical = 0;
    $espacio_nie_fecha = 56; $espacio_grado = 50;
    $espacio_logo_escudo_x = 8; $espacio_logo_escudo_y = 1;
    $espacio_firma = 30;
    
	// Carnet atras
	$ancho_del_carnet_atras = 80.852; $alto_del_carnet_atras = 49.102; $valor_x_atras = 2.5; $valor_y_atras = 2.5;
	// Espacio del cuadro de la foto.
	$tamaño_foto_x = 11; $tamaño_foto_y = 13;
	$valor_x_foto = 1; $valor_y_foto = 15; $espacio_y_foto = 15;
    $ancho_de_la_foto = 25; $alto_de_la_foto = 33;
	// Espacios
	$espacio_y_label_0 = 15; $espacio_x_label_0 = 20;	// 
	$espacio_y_label_1 = 18; $espacio_x_label_1 = 27;	// NOMBRE DEL ALUMNO8A)
	$espacio_y_label_2 = 31; $espacio_x_label_2 = 27;	// MODALIDAD
	$espacio_y_label_3 = 41; $espacio_x_label_3 = 27;	// Turno
	$espacio_y_label_4 = 46; $espacio_x_label_4 = 27;	// Fecha de Nacimiento.
	$espacio_y_label_5 = 52; $espacio_x_label_5 = 1;	// NIE
	$espacio_y_label_6 = 50; $espacio_x_label_6 = 48;	// 
	
	$espacio_y_text_0 = 3; $espacio_x_text_0 = 15;	// NOMBRE DE LA INSTITUCION.
	$espacio_y_text_1 = 19; $espacio_x_text_1 = 28;	// TEXT NOMBRE DEL ALUMNO
	$espacio_y_text_2 = 33; $espacio_x_text_2 = 28;	// TEXT MODALIDAD
	$espacio_y_text_3 = 23; $espacio_x_text_3 = 27;	// TEXT apellidos (materno - paterno)
	$espacio_y_text_4 = 38; $espacio_x_text_4 = 41;	// TEXT TURNO
	$espacio_y_text_5 = 48; $espacio_x_text_5 = 60;	// TEXT NIE
	$espacio_y_text_6 = 43; $espacio_x_text_6 = 57;	// TEXT FECHA DE NACIMIENTO
	
	// Espacios para la parte de atras.
	$ancho_del_sello = 25; $alto_del_sello = 25;
	$ancho_del_firma = 42; $alto_del_firma = 14;
    
	// Espacio del cuadro de la foto.
	$espacio_x_sello = 55; $espacio_y_sello = 25;
	$espacio_x_firma = 10; $espacio_y_firma = 30;
	// Espacios
	$espacio_y_label_0_v = 5; $espacio_x_label_0_v = 10;	// institucion
	$espacio_y_label_1_v = 50; $espacio_x_label_1_v = 10;	// director
	$espacio_y_label_2_v = 6; $espacio_x_label_2_v = 5;	// Leyenda Carnet Atras
	$espacio_y_label_3_v = 25; $espacio_x_label_3_v = 10;	// Este carnet es válido hasta
	
	$espacio_y_text_0_v = 47; $espacio_x_text_0_v = 10;	// nombre director

	// Varible Imagen Sello y Firma
	//Logo
	$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];
	// Imagen de Fondo.
	$img_firma = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];
	
	// Fondo y Variables.
	$pdf->SetFillColor(238,238,238);
    $fill = false; $numero_carnet = 0;
	$salto_de_carnet = false;
	// array para las etiquetas del carnet.
	$etiqueta_carnet = array("","Nombre del Alumno(a)","Modalidad - Grado - Sección","Turno:","Fecha Nacimiento:","Número de Identificación Estudiantil:","Este Carnet es válido hasta:","Director","Este Carnet es personal e instransferible, y acredita al portador como Estudiante y miembro de la Institución.","F.N.:");
	$setcolor = array("0,0,254");

	// Varible Imagen Logo e Fondo Carnet
	//Logo
	$img_logo = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
	// Imagen de Fondo.
	$img_fondo = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fondo2.jpg';
	//	recorre todos los registros de la consulta.
	for($i=0;$i<$filas;$i++)
	{
		// Crear el número de rectángulos necesarios.
		if($numero_carnet == 0)
		{
			if($salto_de_carnet == false){
				$pdf->AddPage();
				// Logo e imagen de fondo.
                $pdf->Image($img_fondo,$valor_x,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				$pdf->Image($img_logo,$espacio_logo_escudo_x,$espacio_logo_escudo_y,$tamaño_foto_x,$tamaño_foto_y);
				// Ancho y Alto del Carnet.
				$pdf->Rect($valor_x,$valor_y,$ancho_del_carnet,$alto_del_carnet);
				// Cuadro de la Foto.
				$pdf->rect($valor_x_foto,$valor_y_foto,$ancho_de_la_foto,$alto_de_la_foto);
				$pdf->Image($ruta_foto[$i],$valor_x_foto+1,$valor_y_foto+1,$ancho_de_la_foto-2,$alto_de_la_foto-2);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','B',16); $pdf->SetTextColor(76,0,153);
				$pdf->RotatedText($espacio_x_label_0,$espacio_y_label_0,utf8_decode($etiqueta_carnet[0]),0);
				$pdf->SetFont('Arial','B',10); $pdf->SetTextColor(0,0,254);
				$pdf->RotatedText($espacio_x_label_1,$valor_y+$espacio_y_label_1,utf8_decode($etiqueta_carnet[1]),0);
				$pdf->RotatedText($espacio_x_label_2,$valor_y+$espacio_y_label_2,utf8_decode($etiqueta_carnet[2]),0);
				$pdf->RotatedText($espacio_x_label_3,$valor_y+$espacio_y_label_3,utf8_decode($etiqueta_carnet[3]),0);
				// FECHA DE NACIMIENTO.
				$pdf->SetFont('Arial','B',10); $pdf->SetTextColor(0,0,254);
				$pdf->RotatedText($espacio_x_label_4,$valor_y+$espacio_y_label_4,utf8_decode($etiqueta_carnet[4]),0);
				$pdf->SetFont('Arial','B',9); $pdf->SetTextColor(54,6,250); // NIE
				$pdf->RotatedText($valor_x+$espacio_x_label_5,$valor_y+$espacio_y_label_5,utf8_decode($etiqueta_carnet[5]),0);
				//$pdf->SetFont('Arial','B',6); $pdf->SetTextColor(0,0,0); //
				//$pdf->RotatedText($valor_x+$espacio_x_label_6,$valor_y+$espacio_y_label_6,utf8_decode($etiqueta_carnet[6] . " " . $fecha_vencimiento),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','B',13); $pdf->SetTextColor(0,0,0); // NOMBRE DE LA INSTITUCIÓN.
				$pdf->RotatedTextMultiCellInstitucion($espacio_x_text_0,$espacio_y_text_0,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','B',10); $pdf->SetTextColor(0,0,0);
				$pdf->RotatedTextNombre($valor_x+$espacio_x_text_1,$valor_y+$espacio_y_text_1,utf8_decode($nombres[$i]),0);
				$pdf->RotatedTextNombre($valor_x+$espacio_x_text_3,$valor_y+$espacio_y_text_3,utf8_decode($apellidos[$i]),0);
				$pdf->SetFont('Arial','B',10); // Modalidad
				$pdf->RotatedTextModalidad($valor_x+$espacio_x_text_2,$valor_y+$espacio_y_text_2,utf8_decode($print_modalidad) .' - ' .substr(utf8_decode($codigo_grado),0,1). utf8_decode('º') . " - '" . utf8_decode($print_seccion)."'",0);
				$pdf->SetFont('Arial','B',10);	// Turno
				$pdf->RotatedTextGrado($valor_x+$espacio_x_text_4,$valor_y+$espacio_y_text_4,utf8_decode($nombre_turno[$i]),0);
				$pdf->SetFont('Arial','B',10);	// Fecha Nacimiento.
				$pdf->RotatedTextFN($valor_x+$espacio_x_text_6,$valor_y+$espacio_y_text_6,utf8_decode($fecha_nacimiento[$i]),0);
				$pdf->SetFont('Arial','B',14); $pdf->SetTextColor(255,0,0);
				$pdf->RotatedTextNIE($espacio_x_text_5,$espacio_y_text_5,trim(utf8_decode($nies[$i])),0);

				// crear de una sola vez la parte de atrás.
				$pdf->SetTextColor(0);
				$pdf->Addpage();
				// Logo e imagen de fondo.
				if($sello == "yes"){$pdf->Image($img_sello,$valor_x+$espacio_x_sello,$valor_y+$espacio_y_sello,$ancho_del_sello,$alto_del_sello);}
                if($firma == "yes"){$pdf->Image($img_firma,$valor_x+$espacio_x_firma,$valor_y+$espacio_y_firma,$ancho_del_firma,$alto_del_firma);}
				// Ancho y Alto del Carnet.
				$pdf->RoundedRect($valor_x_atras,$valor_y_atras,$ancho_del_carnet_atras,$alto_del_carnet_atras,3);
				// Tamaño y Etiqueta Carnet Valor 0.
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_x_label_1_v,$valor_y+$espacio_y_label_1_v,utf8_decode($etiqueta_carnet[7]),0);
				$pdf->SetFont('Arial','',10);
				$pdf->RotatedTextCarnet($valor_x+$espacio_x_label_2_v,$valor_y+$espacio_y_label_2_v,utf8_decode($etiqueta_carnet[8]),0);
				$pdf->SetFont('Arial','B',10); $pdf->SetTextColor(0,0,0); // FECHA DE VENCIMIENTO.
				$pdf->RotatedText($valor_x+$espacio_x_label_3_v,$valor_y+$espacio_y_label_3_v,utf8_decode($etiqueta_carnet[6] . " " . $fecha_vencimiento),0);
				// Tamaño y Etiqueta de Texto.
				$pdf->SetFont('Arial','',10);
				//$pdf->RotatedTextMultiCell($valor_x+$espacio_x_label_0_v,$valor_y+$espacio_y_label_0_v,utf8_decode($_SESSION["institucion"]),0);
				$pdf->SetFont('Arial','',8);
				$pdf->RotatedText($valor_x+$espacio_x_text_0_v,$valor_y+$espacio_y_text_0_v,cambiar_de_del($_SESSION["nombre_director"]),0);
			}
		}
		
		// Decisión del Salto de Página cuando $numero_carnet sea igual a ocho
			$numero_carnet++;
			if($numero_carnet == 1){
				$numero_carnet = 0;
			}
	}
// Salida del pdf.
    $pdf->Output();
?>