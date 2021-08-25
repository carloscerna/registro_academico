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
	$codigo_modalidad_year = substr($_REQUEST["todos"],0,2) . substr($_REQUEST["todos"],6,2) ;
    $cod_por_asignatura = $_REQUEST["lstasignatura"];
    $db_link = $dblink;
    $j = 0;  $data = array();
// buscar la consulta y la ejecuta.
    consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
            $print_grado = utf8_decode('Grado:     '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección:  '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
            $print_periodo = utf8_decode('Período: _____');
            //$nombre_asignatura = utf8_decode((trim($row['n_asignatura'])));
	    break;
            }

// Obtener el Encargado de Grado.
    $query_encargado = "SELECT cd.id_carga_docente, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, cd.codigo_docente, bach.nombre, gann.nombre, sec.nombre, ann.nombre
								FROM carga_docente cd
						INNER JOIN personal p ON (cd.codigo_docente)::int = p.id_personal
						INNER JOIN bachillerato_ciclo bach ON cd.codigo_bachillerato = bach.codigo
						INNER JOIN ann_lectivo ann ON cd.codigo_ann_lectivo = ann.codigo
						INNER JOIN grado_ano gann ON cd.codigo_grado = gann.codigo
						INNER JOIN seccion sec ON cd.codigo_seccion = sec.codigo
							WHERE btrim(cd.codigo_bachillerato || cd.codigo_grado || cd.codigo_seccion || cd.codigo_ann_lectivo) = '$codigo_all' and cd.codigo_asignatura = '$cod_por_asignatura' ORDER BY nombre_docente";
// Eejcutar Consulta
	$result_encargado = $db_link -> query($query_encargado) or die("Consulta Fallida - Encargado"); 
//  Nombre del Encargado.
    $nombre_encargado = '';
    //  imprimir datos del bachillerato.
     while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
            {
				$nombre_encargado = cambiar_de_del(trim($rows_encargado['nombre_docente']));
				$codigo_docente = trim($rows_encargado['codigo_docente']);
				  break;
            }            
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    //  Variables globales.
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_periodo, $pagina_impar, $nombre_asignatura, $nombre_encargado;
        //Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
        $this->Image($img,5,5,25,30);
        //Arial bold 15
        $this->SetFont('Arial','B',13);
        //Título
        $this->RotatedText(35,10,utf8_decode($_SESSION['institucion']),0);
        $this->RotatedText(35,15,'Notas - Por Asignatura - Recuperacion',0,1,'L');
        
        $this->SetFont('Arial','',9);
        // Imprimir Modalidad y Asignatura.
        $this->RoundedRect(34, 16, 130, 6, 1.5, '');
        $this->RotatedText(35,20.5,$print_bachillerato,0);
        // Nombre Asignatura.
        $this->RoundedRect(34, 22, 130, 6, 1.5, '');
        $this->RotatedText(35,26,$print_ann_lectivo,0);
 //Colores, ancho de línea y fuente en negrita
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetDrawColor(0,0,0);
		$this->SetLineWidth(.3);
		$this->SetFont('Arial','B',8);
		// Crear Rectangulos.
		$this->SetFillColor(224,248,252);
		$this->Rect(15,40,192,15,true); // Principal.
		$this->SetFillColor(255);
		$this->Rect(15,40,7,15); // Número.
		$this->Rect(15,40,12,15); // Grado.
		$this->Rect(15,40,17,15); // sECCIÓN
		$this->Rect(15,40,32,15); // NIE.
		$this->Rect(15,40,92,15); // Orden Alfabetico
		$this->Rect(15,40,122,15); // Asignatura
		$this->Rect(15,40,132,15); // T1
		$this->Rect(15,40,142,15); // T2
		$this->Rect(15,40,152,15); // T3
		$this->Rect(15,40,162,15); // total puntos
		$this->Rect(15,40,172,15); // Promedio 3 trimestres
		$this->Rect(15,40,182,15); // Nota recuperacion
		$this->Rect(15,40,192,15); // nota final
		// Establecer ubicación Y.
		$this->SetY(40);
		//Cabecera tamaño de ancho y alto y array para los encabezados.
		$contenido_encabezado = array('Nº','Gr.','Sec.','NIE','Nombres','Asignatura','T1','T2','T3','Total Pto.','Prom. III T','Nota Recup.','Nota Final');
		//un arreglo con su medida  a lo ancho
		$this->SetWidths(array(7,5,5,15,60,30,10,10,10,10,10,10,10));
		//un arreglo con alineacion de cada celda
		$this->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C'));
		// Ubicación de la Información y tipo de letra.
      	$y=$this->GetY();
		$x=$this->GetX();
		$this->SetXY($x,$y);
		$this->SetFont('Arial','B',8);
		//OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
		$this->Row(array(utf8_decode($contenido_encabezado[0]),utf8_decode($contenido_encabezado[1]),utf8_decode($contenido_encabezado[2]),utf8_decode($contenido_encabezado[3]),utf8_decode($contenido_encabezado[4]),utf8_decode($contenido_encabezado[5])
						 ,utf8_decode($contenido_encabezado[6]),utf8_decode($contenido_encabezado[7]),utf8_decode($contenido_encabezado[8]),
						 utf8_decode($contenido_encabezado[9]),utf8_decode($contenido_encabezado[10]),utf8_decode($contenido_encabezado[11])
						 ,utf8_decode($contenido_encabezado[12])));
		//Restauración de colores y fuentes
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetFont('');
		//Datos
		$fill=false;
		$this->SetFont('Arial','',9);
}

//Pie de página
function Footer()
{
  // Establecer formato para la fecha.
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
    $pdf->SetMargins(15, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetXY(15,55);
// Definimos el tipo de fuente, estilo y tamaño.
    $w=array(7,5,5,15,60,30,10,10,10,10,10,10,10); //determina el ancho de las columnas
    $h=array(6,12); //determina el alto de las columnas    
    // colores del fondo, texto, línea.
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);

    $numero_linea = 1; $fill=false; $porcentaje_institucional = 0;
       consultas(17,0,$codigo_modalidad_year,$cod_por_asignatura,'','',$db_link,'');
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
			   // >Impresión de los promedios para las asignaturas.
                if($codigo_bachillerato >= '03' && $codigo_bachillerato <= '05'){
	                    $pdf->SetX(15);
						$pdf->SetFont('Arial','',8);
						$pdf->Cell($w[0],$h[0],$numero_linea,0,0,'C',$fill);
						$pdf->SetFont('Arial','',8);
						$pdf->Cell($w[1],$h[0],trim($row['codigo_grado']),0,0,'L',$fill);   // Grado
						$pdf->Cell($w[2],$h[0],trim($row['nombre_seccion']),0,0,'L',$fill);   // Sección
						$pdf->Cell($w[3],$h[0],trim($row['codigo_nie']),'LR',0,'L',$fill);   // NIE
						$pdf->Cell($w[4],$h[0],cambiar_de_del(trim($row['apellido_alumno'])),0,0,'L',$fill);   // NOMBRES
						$pdf->Cell($w[5],$h[0],substr(utf8_decode((trim($row['n_asignatura']))),0,20),0,0,'L',$fill);   // NOMBRE ASIGNATURA
						$pdf->SetFont('Arial','',8);
						if(trim($row['nota_p_p_1']) <> 0){$pdf->Cell($w[6],$h[0],trim($row['nota_p_p_1']),'L',0,'C',$fill);}else{$pdf->Cell($w[6],$h[0],'',0,0,'C',$fill);}
						if(trim($row['nota_p_p_2']) <> 0){$pdf->Cell($w[7],$h[0],trim($row['nota_p_p_2']),0,0,'C',$fill);}else{$pdf->Cell($w[7],$h[0],'',0,0,'C',$fill);}
						if(trim($row['nota_p_p_3']) <> 0){$pdf->Cell($w[8],$h[0],trim($row['nota_p_p_3']),'R',0,'C',$fill);}else{$pdf->Cell($w[8],$h[0],'',0,0,'C',$fill);}
						// total de puntos
						$pdf->SetFont('Arial','B',9);
						$pdf->Cell($w[9],$h[0],trim($row['total_puntos_basica']),0,0,'C',$fill);
						// Promedio 3 trimestres.
						$pdf->SetFont('Arial','B',9);
						  $pdf->Cell($w[10],$h[0],trim($row['nota_final']),0,0,'C',$fill);
						// Nota Recuperación y Final.
						if(trim($row['recuperacion']) <> 0){$pdf->Cell($w[11],$h[0],trim($row['recuperacion']),'LR',0,'C',$fill);}else{$pdf->Cell($w[11],$h[0],'','LR',0,'C',$fill);}
						if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[12],$h[0],verificar_nota($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[12],$h[0],'',0,0,'C',$fill);}
						$pdf->SetFont('Arial','',9);
						$pdf->Ln();
                 // Controlar el Salto de Página..
                if($numero_linea == 35 || $numero_linea == 70 || $numero_linea == 105 || $numero_linea == 140 || $numero_linea == 175 || $numero_linea == 210)
					{
						$numero_linea++;;
						$pdf->AddPage();
						$pdf->SetXY(5,55);
					}else{
						$numero_linea++;
						$fill=!$fill;
					}
				}
	     }	// fin del recorrido de la tabla.
// Salida del pdf.
    $pdf->Output();
?>