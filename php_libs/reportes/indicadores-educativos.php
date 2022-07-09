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
     $codigo_ann_lectivo = $_REQUEST["lstannlectivo"];
     $db_link = $dblink;
     $codigo_all_indicadores = array(); $nombre_grado = array(); $nombre_seccion = array(); $nombre_modalidad = array(); $nombre_ann_lectivo = array();
     $codigo_grado_tabla = array(); $codigo_grado_comparar = array(); $nombre_modalidad_consolidad = array(); $nombre_turno = array(); $nombre_turno_consolidado = array();
     
// buscar la consulta y la ejecuta.
  consultas(13,0,$codigo_ann_lectivo,'','','',$db_link,'');
//  captura de datos para información individual de grado y sección.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
        {
            $print_bachillerato = utf8_decode(''.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(''.trim($row['nombre_grado']));
            $print_seccion = utf8_decode(''.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
			// Variables
			$codigo_modalidad = trim($row[0]);
			$codigo_grado = trim($row['codigo_grado']);
    	    $codigo_seccion = trim($row['codigo_seccion']);
			$codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);
			$codigo_turno = trim($row['codigo_turno']);
			// Array
			$nombre_grado[] = utf8_decode($row['nombre_grado']);
			$nombre_seccion[] = $row['nombre_seccion'];
			$nombre_modalidad[] = $row['nombre_bachillerato'];
			$nombre_ann_lectivo[] = $row['nombre_ann_lectivo'];
			$nombre_turno[] = $row['nombre_turno'];
	    	// modalidad, grado, sección, año lectivo.
	    	$codigo_all_indicadores[] = $codigo_modalidad . $codigo_grado . $codigo_seccion . $codigo_ann_lectivo . $codigo_turno;
        }
		//print_r($codigo_all_indicadores);
		
	    // buscar la consulta y la ejecuta.
  consultas(14,0,$codigo_ann_lectivo,'','','',$db_link,'');
//  captura de datos para información individual de grado y sección.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
        {
	    	$codigo_grado = trim($row['codigo_grado']);
	    	$codigo_modalidad_consolidado = trim($row[1]);
			// arrays
			$nombre_modalidad_consolidado[] = trim($row['nombre_modalidad']);
	    	$nombre_grado_consolidado[] = utf8_decode($row['nombre_grado']);
			$nombre_ann_lectivo[] = $row['nombre_ann_lectivo'];
			$nombre_turno_consolidado[] = $row['nombre_turno'];
	    	// modalidad, grado y año lectivo.
	    	$codigo_indicadores[] = $codigo_modalidad_consolidado . $codigo_grado . $codigo_ann_lectivo;
        }
//  captura de datos para información individual de grado y sección.
		$query_turno = "SELECT * FROM turno ORDER BY codigo";
		// ejecutar la consulta.
		$result_turno = $db_link -> query($query_turno);
		while($row = $result_turno -> fetch(PDO::FETCH_BOTH))
		{
			$codigo_turno_bucle[] = trim($row['codigo']);
			$nombre_turno_bucle[] = trim($row['nombre']);
		}
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $print_nombre_docente, $print_ann_lectivo, $print_turno;;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(250,6,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->SetFont('Arial','',11);
    $this->Cell(15);
    $this->Cell(250,4,utf8_decode('Indicadores Educativos (SobreEdad y Repitencia)') . ' ' . $print_ann_lectivo . ' Turno: ' . $print_turno,0,0,'C');
    $this->Line(0,20,300,20);
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

//encabezado
function encabezado()
{
    global $print_ann_lectivo, $print_turno;
    //Restauración de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
    //	crear encabezado. año lectio y Turno
		//$this->RotatedText(18,22,($print_ann_lectivo),0);
		//$this->RotatedText(210,22,("Turno: " . $print_turno),0);
		$altoY = 21;
		$altoYTitulos = 26;
		$altoYTitulosMyF = 33;
		$altoYTitulos2MyF = 29;
		$this->SetFont('Arial','',10);
    // Generar el cuadro en donde se ubicara el grado, sección y año lectivo.
        	$this->RoundedRect(15, $altoY, 10, 15, 1.5, '1234', '');
			// Número de Línea.
			$this->RoundedRect(15, $altoY, 10, 15, 1.5, '1234', '');
			$this->RotatedText(18,$altoY+4,utf8_decode('Nº'),0);
			// Nombre del Docente o Encargado.
			$this->RoundedRect(25, $altoY, 60, 15, 1.5, '1234', '');
			$this->RotatedText(45,$altoY+4,'Nombre del Docente',0);
			// Grado.
			$this->SetFont('Arial','',9);
			$this->RoundedRect(85, $altoY, 20, 15, 1.5, '1234', '');
			$this->RotatedText(90,$altoY+4,'Grado',0);
			$this->SetFont('Arial','',10);
			// Sección
			$this->RoundedRect(105, $altoY, 15, 15, 1.5, '1234', '');
			$this->RotatedText(106.5,$altoY+4,utf8_decode('Sección'),0);
		   // cuadro para la sobreedad, repitencia y deserción.
		   // matricula Máxima.
	       $this->RoundedRect(120, $altoY, 30, 7.5, 1.5, '1234', '');
	       $this->RotatedText(122,$altoYTitulos,utf8_decode('Matricula Máxima'),0);
	       $this->RotatedText(124,$altoYTitulosMyF,utf8_decode('M'),0);
	       $this->RotatedText(134,$altoYTitulosMyF,utf8_decode('F'),0);
	       $this->RotatedText(144,$altoYTitulosMyF,utf8_decode('T'),0);
	       $this->RoundedRect(120, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); //m
	       $this->RoundedRect(130, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // f
	       $this->RoundedRect(140, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // t
	       // Deserción.
	       $this->RoundedRect(150, $altoY, 30, 7.5, 1.5, '1234', '');
	       $this->RotatedText(155,$altoYTitulos,utf8_decode('Deserción'),0);
	       $this->RotatedText(153,$altoYTitulosMyF,utf8_decode('M'),0);
	       $this->RotatedText(164,$altoYTitulosMyF,utf8_decode('F'),0);
	       $this->RotatedText(173,$altoYTitulosMyF,utf8_decode('T'),0);
	       $this->RoundedRect(150, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); //m
	       $this->RoundedRect(160, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // f
	       $this->RoundedRect(170, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // t
			// Repitencia.
	       $this->RoundedRect(180, $altoY, 30, 7.5, 1.5, '1234', '');
	       $this->RotatedText(185,$altoYTitulos,utf8_decode('Repitencia'),0);
	       $this->RotatedText(184,$altoYTitulosMyF,utf8_decode('M'),0);
	       $this->RotatedText(194,$altoYTitulosMyF,utf8_decode('F'),0);
	       $this->RotatedText(204,$altoYTitulosMyF,utf8_decode('T'),0);
	       $this->RoundedRect(180, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); //m
	       $this->RoundedRect(190, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // f
	       $this->RoundedRect(200, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // t
			// Sobreedad
	       $this->RoundedRect(210, $altoY, 30, 7.5, 1.5, '1234', '');
	       $this->RotatedText(215,$altoYTitulos,utf8_decode('Sobreedad'),0);
	       $this->RotatedText(214,$altoYTitulosMyF,utf8_decode('M'),0);
	       $this->RotatedText(224,$altoYTitulosMyF,utf8_decode('F'),0);
	       $this->RotatedText(234,$altoYTitulosMyF,utf8_decode('T'),0);
	       $this->RoundedRect(210, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); //m
	       $this->RoundedRect(220, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // f
	       $this->RoundedRect(230, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // t
			// Matricula Final.
	       $this->RoundedRect(240, $altoY, 30, 7.5, 1.5, '1234', '');
	       $this->RotatedText(243,$altoYTitulos,utf8_decode('Matricula Final'),0);
	       $this->RotatedText(243,$altoYTitulosMyF,utf8_decode('M'),0);
	       $this->RotatedText(254,$altoYTitulosMyF,utf8_decode('F'),0);
	       $this->RotatedText(264,$altoYTitulosMyF,utf8_decode('T'),0);
	       $this->RoundedRect(240, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); //m
	       $this->RoundedRect(250, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // f
	       $this->RoundedRect(260, $altoYTitulos2MyF, 10, 7.5, 1.5, '1234', ''); // t
}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Letter');
    $data = array();
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    //$pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
    //$pdf->SetY(50);
    //$pdf->SetX(15);
// Evaluar si existen registros.
	if($result -> rowCount() != 0)
	{
		for($jh=0;$jh<=count($nombre_turno_bucle)-1;$jh++)
		{
			$pdf->AddPage();
			// Aqui mandamos texto a imprimir o al documento.
			$pdf->SetY(35);
			$pdf->SetX(15);
			// variable para el turno.
				$print_turno = $nombre_turno_bucle[$jh];
			// llamar al encabezado.
				$pdf->encabezado();
			// Ancho de las diferentes columnas	
				$w=array(10,60,20,15,10,15,15,15); //determina el ancho de las columnas
			// Variables para los diferentes cálculos.
				$fill=false; $i=0; $m = 0; $f = 0; $suma = 0; $n_a = 0;
				$contador_tabla_grado = 0;
				$repitentem = 0; $repitentef = 0; $totalrepitente = 0;
				$sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
				$total_masculino_final = 0; $total_femenino_final = 0; $total_final = 0;
				$total_general_masculino = 0; $total_general_femenino = 0; $total_general = 0;
				$alto_fila = 6;
			// recorrer la tabla. SEGUN AÑO, MODALIDAD, GRADO, SECCIÓN	 
			for($j=0;$j<=count($codigo_all_indicadores)-1;$j++)
			{
				if($codigo_turno_bucle[$jh] == substr($codigo_all_indicadores[$j],8,2))
				{
					$i=$i+1; // Variables para el salto de página y el control de número de líneas.
					$pdf->SetX(15);
					// Consultar al docente encargado.
					 // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
						// buscar la consulta y la ejecuta.
						consultas_docentes(1,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						$print_nombre_docente = "";
						while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
							{
						$print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
						
						if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
							$print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
						}
						
							}
					$pdf->Cell($w[0],$alto_fila,$i,'LR',0,'C',$fill);        // núermo correlativo
					$pdf->Cell($w[1],$alto_fila,$print_nombre_docente,'LR',0,'J',$fill);
					//$pdf->Cell($w[1],$alto_fila,substr(utf8_decode($nombre_modalidad[$j]),0,22),'LR',0,'J',$fill);
					$pdf->Cell($w[2],$alto_fila,$nombre_grado[$j],'LR',0,'J',$fill);
					$pdf->Cell($w[3],$alto_fila,$nombre_seccion[$j],'LR',0,'C',$fill);
					//$pdf->Cell($w[3],$alto_fila,$nombre_turno[$j],'LR',0,'C',$fill);
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(1,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino = $row_indicadores['total_masculino'];
						$pdf->Cell($w[4],$alto_fila,$total_masculino,'LR',0,'C',$fill);
						}
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(2,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_femenino = $row_indicadores['total_femenino'];
						$pdf->Cell($w[4],$alto_fila,$total_femenino,'LR',0,'C',$fill);
						// femenino + masculino
						$pdf->Cell($w[4],$alto_fila,$total_masculino + $total_femenino,'LR',0,'C',$fill);
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(3,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino_desercion = $row_indicadores['total_masculino_desercion'];
						if($total_masculino_desercion == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_desercion,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(4,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_desercion = $row_indicadores['total_femenino_desercion'];
							if($total_femenino_desercion == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_femenino_desercion,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_desercion + $total_masculino_desercion) == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_desercion + $total_femenino_desercion,'LR',0,'C',$fill);}
						}		    
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(5,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_repitente = $row_indicadores['total_masculino_repitente'];
							if($total_masculino_repitente == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_repitente,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(6,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_repitente = $row_indicadores['total_femenino_repitente'];
							if($total_femenino_repitente == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_femenino_repitente,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_repitente + $total_masculino_repitente) == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_repitente + $total_femenino_repitente,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(7,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_sobreedad = $row_indicadores['total_masculino_sobreedad'];
							if($total_masculino_sobreedad == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_sobreedad,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(8,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_sobreedad = $row_indicadores['total_femenino_sobreedad'];
							if($total_femenino_sobreedad == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_femenino_sobreedad,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_sobreedad + $total_masculino_sobreedad) == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_sobreedad + $total_femenino_sobreedad,'LR',0,'C',$fill);}
						}
					// calcular la matricula final.
						$total_masculino_final = $total_masculino - $total_masculino_desercion;
						$total_femenino_final = $total_femenino - $total_femenino_desercion;
						$total_final = $total_masculino_final + $total_femenino_final;
						$total_general_masculino = $total_general_masculino + $total_masculino_final;
						$total_general_femenino = $total_general_femenino + $total_femenino_final;
						$total_general = $total_general_masculino + $total_general_femenino;
						
						$pdf->Cell($w[4],$alto_fila,$total_masculino_final,'LR',0,'C',$fill);
						$pdf->Cell($w[4],$alto_fila,$total_femenino_final,'LR',0,'C',$fill);
						$pdf->Cell($w[4],$alto_fila,$total_final,'LR',0,'C',$fill);
					// número de la línea y fondo.
							$pdf->Ln();
							$fill=!$fill;
					// Salto de Línea.
							if($i > 28){
								$pdf->SetX(15);
								$pdf->Cell(array_sum($w)+5+(6*10),0,'','B');
								$pdf->AddPage();
							// Aqui mandamos texto a imprimir o al documento.
							$pdf->SetY(30);
							$pdf->SetX(15);
							$pdf->encabezado();
							}
				} // if condiciones para imprimir dependiendo del codigo turno.
			}	// for codigo_all_indicadores
		}	// for codigo_turno
    } // condición de vacío en la tabla.    
else{
	$pdf->AddPage();
    // si no existen registros.
    $pdf->Cell(150,7,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// CONSOLIDADOS GENERALES
//////////////////////////////////////////////////////////////////////////////////////////////////////////
  $pdf->SetX(15);
      $pdf->cell(205,$alto_fila,' ','LR',0,'C',$fill);
      $pdf->Cell($w[4],$alto_fila,$total_general_masculino,'LR',0,'C',$fill);
      $pdf->Cell($w[4],$alto_fila,$total_general_femenino,'LR',0,'C',$fill);
      $pdf->Cell($w[4],$alto_fila,$total_general,'LR',1,'C',$fill);
    // cerrar línea si sólo hay una página.
    if($i == 28){
		$pdf->SetX(15);
		$pdf->Cell(array_sum($w)+5+(6*10),0,'','B');
    }
//*******************************************************//
//	CREAR PROCESO PARA CONSOLIDAR GRADOS
//
//******************************************************//
    /// armar subtotales. crear una nueva página.
        $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
    $pdf->SetY(30);
    $pdf->SetX(15);
 // Evaluar si existen registros.
    if($result -> rowCount() != 0){
	// llamar al encabezado.
	    $pdf->encabezado();
	    
	$w=array(10,40,20,15,10,15,15,15); //determina el ancho de las columnas
	
	$fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $n_a = 0;
	$contador_tabla_grado = 0;
	$repitentem = 0; $repitentef = 0; $totalrepitente = 0;
	$sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
	$alto_fila = 6;

  
	// recorrer la tabla.	 
	    for($j=0;$j<=count($codigo_indicadores)-1;$j++)
		{
		$pdf->SetX(15);
		$pdf->Cell($w[0],$alto_fila,$i,'LR',0,'C',$fill);        // núermo correlativo
		$pdf->Cell($w[1],$alto_fila,utf8_decode(substr($nombre_modalidad_consolidado[$j],0,23)),'LR',0,'J',$fill);
		$pdf->Cell($w[2],$alto_fila,$nombre_grado_consolidado[$j],'LR',0,'J',$fill);
		$pdf->Cell($w[3],$alto_fila,'','LR',0,'C',$fill);
		
		// consultar y mostrar valores de matricula. m y f.
		    consulta_indicadores(9,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_masculino = $row_indicadores['total_masculino'];
				$pdf->Cell($w[4],$alto_fila,$total_masculino,'LR',0,'C',$fill);
			    }
    
		// consultar y mostrar valores de matricula. m y f.
		    consulta_indicadores(10,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_femenino = $row_indicadores['total_femenino'];
				$pdf->Cell($w[4],$alto_fila,$total_femenino,'LR',0,'C',$fill);
				// femenino + masculino
				$pdf->Cell($w[4],$alto_fila,$total_masculino + $total_femenino,'LR',0,'C',$fill);
			    }

		// consultar y mostrar valores de matricula. m y f. desercion
		    consulta_indicadores(11,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_masculino_desercion = $row_indicadores['total_masculino_desercion'];
				if($total_masculino_desercion == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_desercion,'LR',0,'C',$fill);}
			    }

		// consultar y mostrar valores de matricula. m y f. desercion
		    consulta_indicadores(12,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_femenino_desercion = $row_indicadores['total_femenino_desercion'];
				if($total_femenino_desercion == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_femenino_desercion,'LR',0,'C',$fill);}
				// femenino + masculino desercion
				
				if(($total_femenino_desercion + $total_masculino_desercion) == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_desercion + $total_femenino_desercion,'LR',0,'C',$fill);}
			    }
			    
		// consultar y mostrar valores de matricula. m y f. repitente
		    consulta_indicadores(13,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_masculino_repitente = $row_indicadores['total_masculino_repitente'];
				if($total_masculino_repitente == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_repitente,'LR',0,'C',$fill);}
			    }

		// consultar y mostrar valores de matricula. m y f. repitente
		    consulta_indicadores(14,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_femenino_repitente = $row_indicadores['total_femenino_repitente'];
				if($total_femenino_repitente == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_femenino_repitente,'LR',0,'C',$fill);}
				// femenino + masculino desercion
				if(($total_femenino_repitente + $total_masculino_repitente) == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_repitente + $total_femenino_repitente,'LR',0,'C',$fill);}
			    }
			    
		// consultar y mostrar valores de matricula. m y f. sobreedad
		    consulta_indicadores(15,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_masculino_sobreedad = $row_indicadores['total_masculino_sobreedad'];
				if($total_masculino_sobreedad == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_sobreedad,'LR',0,'C',$fill);}
			    }

		// consultar y mostrar valores de matricula. m y f. sobreedad
		    consulta_indicadores(16,0,$codigo_indicadores[$j],'','','',$db_link,'');
		    	while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
			    {
				$total_femenino_sobreedad = $row_indicadores['total_femenino_sobreedad'];
				if($total_femenino_sobreedad == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_femenino_sobreedad,'LR',0,'C',$fill);}
				// femenino + masculino desercion
				if(($total_femenino_sobreedad + $total_masculino_sobreedad) == 0){$pdf->Cell($w[4],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[4],$alto_fila,$total_masculino_sobreedad + $total_femenino_sobreedad,'LR',0,'C',$fill);}
			    }    
		
			    //$pdf->cell($w[4],$alto_fila,$codigo_indicadores[$j],0,0,'C',$fill);
		// número de la línea y fondo.
					$pdf->Ln();
					$fill=!$fill;
					$i=$i+1;
        // salto de línea.
				if($i == 26){
					$pdf->SetX(15);
					$pdf->Cell(array_sum($w)+5+(6*10),0,'','B');
					$pdf->AddPage();
					// Aqui mandamos texto a imprimir o al documento.
					$pdf->SetY(30);
					$pdf->SetX(15);
					$pdf->encabezado();
			    }
		}
    } // condición de vacío en la tabla.    
else{
    // si no existen registros.
    $pdf->Cell(150,7,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
}   
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
     $print_nombre = $print_grado . ' ' . $print_seccion;
     $pdf->Output($print_nombre,$modo);
?>