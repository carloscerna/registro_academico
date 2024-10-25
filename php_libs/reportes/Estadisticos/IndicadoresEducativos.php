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
     $codigo_all_indicadores = []; $nombre_grado = []; $nombre_seccion = []; $nombre_modalidad = []; $nombre_ann_lectivo = [];
     $codigo_grado_tabla = []; $codigo_grado_comparar = []; $nombre_modalidad_consolidad = []; $nombre_turno = []; $nombre_turno_consolidado = [];
// buscar la consulta y la ejecuta.
	consultas(14,0,$codigo_ann_lectivo,'','','',$db_link,'');
//  captura de datos para información individual de grado y sección.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
        {
	    	$codigo_grado = trim($row['codigo_grado']);
			$codigo_seccion = trim($row['codigo_seccion']);
			$codigo_turno = trim($row['codigo_turno']);
	    	$codigo_modalidad_consolidado = trim($row[1]);
			//
			$nombre_modalidad[] = convertirTexto(trim($row['nombre_modalidad']));
			$nombre_grado[] = convertirTexto($row['nombre_grado']);
			$nombre_seccion[] = convertirTexto($row['nombre_seccion']);
			// arrays
			$nombre_modalidad_consolidado[] = trim($row['nombre_modalidad']);
	    	$nombre_grado_consolidado[] = convertirTexto(trim($row['nombre_grado']));
			$nombre_ann_lectivo[] = $row['nombre_ann_lectivo'];
			$nombre_turno_consolidado[] = $row['nombre_turno'];
	    	// modalidad, grado y año lectivo.
	    	$codigo_indicadores[] = $codigo_modalidad_consolidado . $codigo_grado . $codigo_ann_lectivo;
			$codigo_all_indicadores[] = $codigo_modalidad_consolidado . $codigo_grado . $codigo_seccion . $codigo_ann_lectivo . $codigo_turno;
        }
//		var_dump($codigo_all_indicadores);
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
    global $print_nombre_docente, $print_ann_lectivo, $print_turno;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(250,6,convertirtexto($_SESSION['institucion']),0,1,'C');
    $this->SetFont('Arial','',11);
    $this->Cell(250,4,convertirtexto('Indicadores Educativos'),0,1,'C');
    $this->Line(10,20,300,20);
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
    //Restauración de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('Arial','B',8);
    // PRIMERA LINEA
		$this->Cell(5,4,convertirTexto('N.ª'),'LTR',0,'C');
		$this->Cell(50,4,'Docente Encargado','LTR',0,'C');
		$this->Cell(70,4,'NIVEL','LTR',0,'C');
		$this->Cell(10,4,'GRADO','LTR',0,'C');
		$this->Cell(10,4,'SEC.','LTR',0,'C');
		$this->Cell(30,4,'MATRICULA MAXIMA','LTR',0,'C');
		$this->Cell(30,4,'DESERCION','LTR',0,'C');
		$this->Cell(30,4,'REPITENCIA','LTR',0,'C');
		$this->Cell(30,4,'SOBREEDAD','LTR',0,'C');
		$this->Cell(30,4,'MATRICULA FINAL','LTR',1,'C');
	// SEGUNDA LINEA
		$this->SetX(10);
		$this->Cell(5,4,'','LB',0,'C');
		$this->Cell(50,4,'','LBR',0,'C');
		$this->Cell(70,4,'','LBR',0,'C');
		$this->Cell(10,4,'','LBR',0,'C');
		$this->Cell(10,4,'','LBR',0,'C');
		for ($i=0; $i <=4 ; $i++) { 
			$this->Cell(10,4,'M',1,0,'C');
			$this->Cell(10,4,'F',1,0,'C');
			$this->Cell(10,4,'T',1,0,'C');
		}
	//
		$this->SetFont('Arial','',10);
		$this->ln();
}
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
    $data = [];
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',10);
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
			$pdf->SetY(25);
			$pdf->SetX(10);
			// variable para el turno.
				$print_turno = $nombre_turno_bucle[$jh];
			// llamar al encabezado.
				$pdf->encabezado();
			// Ancho de las diferentes columnas	
				$w=[5,50,70,10,15,10,15,15,15]; //determina el ancho de las columnas
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
					$pdf->SetX(10);
					// Consultar al docente encargado.
					 // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
						// buscar la consulta y la ejecuta.
						consultas_docentes(1,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						global $print_nombre_docente;
							$pdf->SetFont('Arial','',8);
								$pdf->Cell($w[0],$alto_fila,$i,'LR',0,'C',$fill);        // núermo correlativo
								$pdf->Cell($w[1],$alto_fila,$print_nombre_docente,'LR',0,'J',$fill);
								$pdf->Cell($w[2],$alto_fila,$nombre_modalidad[$j],'LR',0,'J',$fill);
								$pdf->Cell($w[3],$alto_fila,$nombre_grado[$j],'LR',0,'J',$fill);
							$pdf->SetFont('Arial','',10);
							$pdf->Cell($w[3],$alto_fila,$nombre_seccion[$j],'LR',0,'C',$fill);
							//$pdf->Cell($w[3],$alto_fila,$nombre_turno[$j],'LR',0,'C',$fill);
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(1,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino = $row_indicadores['total_masculino'];
						$pdf->Cell($w[5],$alto_fila,$total_masculino,'LR',0,'C',$fill);
						}
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(2,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_femenino = $row_indicadores['total_femenino'];
						$pdf->Cell($w[5],$alto_fila,$total_femenino,'LR',0,'C',$fill);
						// femenino + masculino
						$pdf->Cell($w[5],$alto_fila,$total_masculino + $total_femenino,'LR',0,'C',$fill);
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(3,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino_desercion = $row_indicadores['total_masculino_desercion'];
						if($total_masculino_desercion == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_desercion,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(4,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_desercion = $row_indicadores['total_femenino_desercion'];
							if($total_femenino_desercion == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_femenino_desercion,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_desercion + $total_masculino_desercion) == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_desercion + $total_femenino_desercion,'LR',0,'C',$fill);}
						}		    
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(5,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_repitente = $row_indicadores['total_masculino_repitente'];
							if($total_masculino_repitente == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_repitente,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(6,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_repitente = $row_indicadores['total_femenino_repitente'];
							if($total_femenino_repitente == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_femenino_repitente,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_repitente + $total_masculino_repitente) == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_repitente + $total_femenino_repitente,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(7,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_sobreedad = $row_indicadores['total_masculino_sobreedad'];
							if($total_masculino_sobreedad == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_sobreedad,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(8,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_sobreedad = $row_indicadores['total_femenino_sobreedad'];
							if($total_femenino_sobreedad == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_femenino_sobreedad,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_sobreedad + $total_masculino_sobreedad) == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_sobreedad + $total_femenino_sobreedad,'LR',0,'C',$fill);}
						}
					// calcular la matricula final.
						$total_masculino_final = $total_masculino - $total_masculino_desercion;
						$total_femenino_final = $total_femenino - $total_femenino_desercion;
						$total_final = $total_masculino_final + $total_femenino_final;
						$total_general_masculino = $total_general_masculino + $total_masculino_final;
						$total_general_femenino = $total_general_femenino + $total_femenino_final;
						$total_general = $total_general_masculino + $total_general_femenino;
						
						$pdf->Cell($w[5],$alto_fila,$total_masculino_final,'LR',0,'C',$fill);
						$pdf->Cell($w[5],$alto_fila,$total_femenino_final,'LR',0,'C',$fill);
						$pdf->Cell($w[5],$alto_fila,$total_final,'LR',0,'C',$fill);
					// número de la línea y fondo.
							$pdf->Ln();
							$fill=!$fill;
					// Salto de Línea.
							if($i > 28){
								$pdf->SetX(10);
								$pdf->Cell(array_sum($w)+5+(6*10),0,'','B');
								$pdf->AddPage();
							// Aqui mandamos texto a imprimir o al documento.
							$pdf->SetY(25);
							$pdf->SetX(10);
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
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
     $print_nombre = convertirTexto("Estadística: $nombreAñoLectivo");
     $pdf->Output($print_nombre,$modo);

	 // FUNCIONES.
function ($codigoGrado){
	switch ($codigoGrado) {
		case 'value':
			# code...
			break;
		
		default:
			# code...
			break;
	}
}