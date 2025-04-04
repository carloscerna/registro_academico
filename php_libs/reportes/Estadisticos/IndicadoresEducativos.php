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
// Establecer formato para la fecha.
	date_default_timezone_set('America/El_Salvador');
	setlocale(LC_TIME,'es_SV');
// CREAR MATRIZ DE MESES Y FECH.
	$meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
//Crear una línea. Fecha con getdate();
	$hoy = getdate();
	$NombreDia = $hoy["wday"];  // dia de la semana Nombre.
	$dia = $hoy["mday"];    // dia de la semana
	$mes = $hoy["mon"];     // mes
	$año = $hoy["year"];    // año
	$total_de_dias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, $año);
	$NombreMes = $meses[(int)$mes - 1];
// definimos 2 array uno para los nombre de los dias y otro para los nombres de los meses
	$nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
	$nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
	$fecha = convertirTexto("Santa Ana, $nombresDias[$NombreDia] $dia de $nombresMeses[$mes] de $año");
	setlocale(LC_MONETARY,"es_ES");
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
    global $print_nombre_docente, $nombreNivel, $nombreGrado, $nombreSeccion, $nombreAñoLectivo, $nombreTurno;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,15,20);
    //Arial bold 15
    $this->SetFont('PoetsenOne','',16);
    //Título - Nuevo Encabezado incluir todo lo que sea necesario.
    $this->Cell(350,6,convertirtexto($_SESSION['institucion']),0,1,'C');
    $this->Cell(350,4,convertirtexto('Indicadores Educativos'),0,1,'C');
    $this->Line(25,17,355,17);
    //Salto de línea
   // $this->Ln(20);
}
//Pie de página
function Footer()
{
  //
  // Establecer formato para la fecha.
  // 
  global $fecha;
  //Posición: a 1,5 cm del final
  $this->SetY(-10);
  //Arial italic 8
  $this->SetFont('Arial','I',8);
  //Crear ubna línea
  $this->Line(5,270,210,270);
  //Número de página y fecha.
  $this->Cell(0,10,convertirTexto('Página ').$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
}
//encabezado
function encabezado()
{
	global $nombreTurno, $nombreAñoLectivo;
	$w=[5,55,75,40,10,30]; //determina el ancho de las columnas
    //Restauración de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
    //Arial bold 15
    	$this->SetFont('PoetsenOne','',11);
		$this->SetXY(25,20);
		$this->Cell(100,4,convertirTexto("Turno: $nombreTurno"),0,0,'L');
		$this->Cell(200,4,convertirTexto("Año Lectivo: $nombreAñoLectivo"),0,1,'R');
	//
		$this->SetFont('Arial','B',8);
    // PRIMERA LINEA
		$this->SetXY(10,25);
		$this->Cell($w[0],4,convertirTexto('N.ª'),'LTR',0,'C');
		$this->Cell($w[1],4,'Docente Encargado','LTR',0,'C');
		$this->Cell($w[2],4,'NIVEL','LTR',0,'C');
		$this->Cell($w[3],4,'GRADO','LTR',0,'C');
		$this->Cell($w[4],4,'SEC.','LTR',0,'C');
		$this->Cell($w[5],4,'MATRICULA MAXIMA','LTR',0,'C');
		$this->Cell($w[5],4,'DESERCION','LTR',0,'C');
		$this->Cell($w[5],4,'REPITENCIA','LTR',0,'C');
		$this->Cell($w[5],4,'SOBREEDAD','LTR',0,'C');
		$this->Cell($w[5],4,'MATRICULA FINAL','LTR',1,'C');
	// SEGUNDA LINEA
		$this->SetX(10);
		$this->Cell(5,4,'','LB',0,'C');
		$this->Cell(55,4,'','LBR',0,'C');
		$this->Cell(75,4,'','LBR',0,'C');
		$this->Cell(40,4,'','LBR',0,'C');
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
// Tipos de fuente.
	$pdf->AddFont('Comic','','comic.php');
	$pdf->AddFont('Alte','','AlteHaasGroteskRegular.php');
	$pdf->AddFont('Alte','B','AlteHaasGroteskBold.php');
	$pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
// Evaluar si existen registros.
	if($result -> rowCount() != 0)
	{
		for($jh=0;$jh<=count($nombre_turno_bucle)-1;$jh++)
		{
			$pdf->AddPage();
			// Aqui mandamos texto a imprimir o al documento.
			$pdf->SetY(15);
			$pdf->SetX(10);
			// variable para el turno y el año lectivo.
				$nombreTurno = $nombre_turno_bucle[$jh];
				$nombreAñoLectivo = $nombre_ann_lectivo[0];
			// llamar al encabezado.
				$pdf->encabezado();
			// Ancho de las diferentes columnas	
				$w=[5,55,75,10,15,10,15,15,15,40]; //determina el ancho de las columnas
			// Variables para los diferentes cálculos.
				$Indicadores = [];
				$Indicadores["MatriculaMasculino"] = 0; $Indicadores["MatriculaFemenino"] = 0;
				$Indicadores["DesercionMasculino"] = 0; $Indicadores["DesercionFemenino"] = 0;
				$Indicadores["RepitenteMasculino"] = 0; $Indicadores["RepitenteFemenino"] = 0;
				$Indicadores["SobreedadMasculino"] = 0; $Indicadores["SobreedadFemenino"] = 0;
				$Indicadores["MatriculaFinalMasculino"] = 0; $Indicadores["MatriculaFinalFemenino"] = 0;
				$fill=false; $i=0; $m = 0; $f = 0; $suma = 0; $n_a = 0;
				$contador_tabla_grado = 0;
				$repitentem = 0; $repitentef = 0; $totalrepitente = 0;
				$sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
				$total_masculino_final = 0; $total_femenino_final = 0; $total_final = 0;
				$total_general_masculino = 0; $total_general_femenino = 0; $total_general = 0;
				$alto_fila = 7;
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
								$pdf->Cell($w[9],$alto_fila,$nombre_grado[$j],'LR',0,'J',$fill);
							$pdf->SetFont('Arial','',10);
							$pdf->Cell($w[3],$alto_fila,$nombre_seccion[$j],'LR',0,'C',$fill);
							//$pdf->Cell($w[3],$alto_fila,$nombre_turno[$j],'LR',0,'C',$fill);
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(1,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino = $row_indicadores['total_masculino'];
						$Indicadores["MatriculaMasculino"] += intval($total_masculino);
						$pdf->Cell($w[5],$alto_fila,$total_masculino,'LR',0,'C',$fill);
						}
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(2,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_femenino = $row_indicadores['total_femenino'];
						$Indicadores["MatriculaFemenino"] += intval($total_femenino);
						$pdf->Cell($w[5],$alto_fila,$total_femenino,'LR',0,'C',$fill);
						// femenino + masculino
						$pdf->Cell($w[5],$alto_fila,$total_masculino + $total_femenino,'LR',0,'C',$fill);
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(3,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino_desercion = $row_indicadores['total_masculino_desercion'];
						$Indicadores["DesercionMasculino"] += intval($total_masculino_desercion);
						if($total_masculino_desercion == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_desercion,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(4,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
					if($result_indicadores -> rowCount() != 0)
					{
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_desercion = $row_indicadores['total_femenino_desercion'];
							$Indicadores["DesercionFemenino"] += intval($total_femenino_desercion);
							if($total_femenino_desercion == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_femenino_desercion,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_desercion + $total_masculino_desercion) == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_desercion + $total_femenino_desercion,'LR',0,'C',$fill);}
						}		    
					}else{
						$Indicadores["DesercionFemenino"] += 0;
					}
						
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(5,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_repitente = $row_indicadores['total_masculino_repitente'];
							$Indicadores["RepitenteMasculino"] += intval($total_masculino_repitente);
							if($total_masculino_repitente == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_repitente,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(6,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_repitente = $row_indicadores['total_femenino_repitente'];
								$Indicadores["RepitenteFemenino"] += intval($total_femenino_repitente);
							if($total_femenino_repitente == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_femenino_repitente,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_repitente + $total_masculino_repitente) == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_repitente + $total_femenino_repitente,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(7,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_sobreedad = $row_indicadores['total_masculino_sobreedad'];
							$Indicadores["SobreedadMasculino"] += intval($total_masculino_sobreedad);
							if($total_masculino_sobreedad == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_sobreedad,'LR',0,'C',$fill);}
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(8,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_sobreedad = $row_indicadores['total_femenino_sobreedad'];
							$Indicadores["SobreedadFemenino"] += intval($total_femenino_sobreedad);
							if($total_femenino_sobreedad == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_femenino_sobreedad,'LR',0,'C',$fill);}
						// femenino + masculino desercion
							if(($total_femenino_sobreedad + $total_masculino_sobreedad) == 0){$pdf->Cell($w[5],$alto_fila,'','LR',0,'C',$fill);}else{$pdf->Cell($w[5],$alto_fila,$total_masculino_sobreedad + $total_femenino_sobreedad,'LR',0,'C',$fill);}
						}
					// calcular la matricula final.
						$total_masculino_final = $total_masculino - $total_masculino_desercion;
						$total_femenino_final = $total_femenino - $total_femenino_desercion;
					//
						$total_final = $total_masculino_final + $total_femenino_final;
					//
						$total_general_masculino = $total_general_masculino + $total_masculino_final;
						$total_general_femenino = $total_general_femenino + $total_femenino_final;
						$total_general = $total_general_masculino + $total_general_femenino;
						$Indicadores["MatriculaFinalMasculino"] = $total_general_masculino;
						$Indicadores["MatriculaFinalFemenino"] = $total_general_femenino;
						
						$pdf->Cell($w[5],$alto_fila,$total_masculino_final,'LR',0,'C',$fill);
						$pdf->Cell($w[5],$alto_fila,$total_femenino_final,'LR',0,'C',$fill);
						$pdf->Cell($w[5],$alto_fila,$total_final,'LR',0,'C',$fill);
					// número de la línea y fondo.
						$pdf->Ln();
						$fill=!$fill;
				} // if condiciones para imprimir dependiendo del codigo turno.
			}	// for codigo_all_indicadores
			//
			// colocar totales por turno
			//
			//var_dump($Indicadores);

			$pdf->SetX(10);
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell(185,$alto_fila,'TOTALES',1,0,'R');
			// MATRICULA
				$pdf->Cell(10,$alto_fila,$Indicadores["MatriculaMasculino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["MatriculaFemenino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["MatriculaFemenino"]+$Indicadores["MatriculaMasculino"],1,0,'C');
			// DESERCION
				$pdf->Cell(10,$alto_fila,$Indicadores["DesercionMasculino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["DesercionFemenino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["DesercionFemenino"]+$Indicadores["DesercionMasculino"],1,0,'C');
			// REPITENCIA
				$pdf->Cell(10,$alto_fila,$Indicadores["RepitenteMasculino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["RepitenteFemenino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["RepitenteFemenino"]+$Indicadores["RepitenteMasculino"],1,0,'C');
			// SOBREEDAD
				$pdf->Cell(10,$alto_fila,$Indicadores["SobreedadMasculino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["SobreedadFemenino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["SobreedadFemenino"]+$Indicadores["SobreedadMasculino"],1,0,'C');
			// SOBREEDAD
				$pdf->Cell(10,$alto_fila,$Indicadores["MatriculaFinalMasculino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["MatriculaFinalFemenino"],1,0,'C');
				$pdf->Cell(10,$alto_fila,$Indicadores["MatriculaFinalFemenino"]+$Indicadores["MatriculaFinalMasculino"],1,0,'C');
			//
				$pdf->SetFont('Arial','',10);
			// coloar matriz a cero.
			unset($Indicadores);
				$Indicadores=[];
		}	// for codigo_turno
    } // condición de vacío en la tabla.    
else{
	$pdf->AddPage();
    // si no existen registros.
    $pdf->Cell(150,10,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
 }
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
     $print_nombre = convertirTexto("Estadística: $nombreAñoLectivo");
     $pdf->Output($print_nombre,$modo);

	 // FUNCIONES.
function ($codigoGrado){
	global $codigoGrado;
	switch ($codigoGrado) {
		case ($codigoGrado == '4P'):
			$codigoGrado = '';
			break;
		
		default:
			# code...
			break;
	}
};