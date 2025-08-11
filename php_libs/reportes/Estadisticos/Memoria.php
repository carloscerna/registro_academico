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
     $codigo_ann_lectivo = $_REQUEST["ann_lectivo"];
     $db_link = $dblink;
     $codigo_all_indicadores = array(); $nombre_grado = array(); $nombre_modalidad = array(); $nombre_ann_lectivo = array();
	 $codigo_modalidad_matriz = array();
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
//CONSULTA PARA LE MEMORIA ESTADISTICA
	$query_grados = "SELECT DISTINCT ROW(org.codigo_bachillerato), org.codigo_bachillerato as codigo_modalidad, org.codigo_grado, org.codigo_ann_lectivo,
		gan.nombre as nombre_grado, ann.nombre as nombre_ann_lectivo,  bach.nombre as nombre_modalidad, bach.ordenar
			FROM organizacion_grados_secciones org
				INNER JOIN grado_ano gan ON gan.codigo = org.codigo_grado
				INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
				INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
					WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' ORDER BY bach.ordenar, org.codigo_bachillerato, org.codigo_grado, org.codigo_ann_lectivo";
//  ejecutar consulta para la memoria estadistica.
	$result_grados = $db_link -> query($query_grados);
//  captura de datos para información individual de grado y sección.
	while($row = $result_grados -> fetch(PDO::FETCH_BOTH))
        {
	    	$codigo_grado = trim($row['codigo_grado']);
            $nombre_ann_lectivo = trim($row['nombre_ann_lectivo']);
            $codigo_modalidad = trim($row['codigo_modalidad']);
			$codigo_modalidad_matriz[] = trim($row['codigo_modalidad']);
            $nombre_modalidad[] = convertirTexto(trim($row['nombre_modalidad']));
            $nombre_grado[] = cambiar_de_del(trim($row['nombre_grado']));
	    	// modalidad, grado y año lectivo.
	    	$codigo_indicadores[] = $codigo_modalidad . $codigo_grado . $codigo_ann_lectivo;
        }
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $nombre_ann_lectivo;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,15,12,15);
    //Título
    $this->SetFont('Arial','',10);
    $this->Cell(350,4,convertirtexto('MINISTERIO DE EDUCACION, CIENCIA Y TECNOLOGIA'),0,1,'C');
    $this->Cell(350,4,convertirtexto('DIRECCION DEPARTAMENTAL DE SANTA ANA'),0,1,'C');
    $this->SetFont('Arial','B',10);
    $this->Cell(350,4,convertirtexto('MEMORIA ESTADISTICA ') . $nombre_ann_lectivo,0,1,'C');
    $this->SetFont('Arial','',8);
    $this->ln();
    $this->Cell(150,4,'CENTRO ESCOLAR: ' . convertirtexto($_SESSION['institucion']),0,0,'L');
    $this->Cell(100,4,'CODIGO: ' . convertirtexto($_SESSION['codigo']),0,1,'R');
    //
    $this->Cell(150,4,convertirtexto('DISTRITO EDUCATIVO 02-02 ZONA 3'),0,0,'L');
    $this->Cell(100,4,'MUNICIPIO: SANTA ANA',0,1,'R');}
//Pie de página
function Footer()
{
	//Firma Director.
	$nombre_director = cambiar_de_del($_SESSION['nombre_director']);
		$this->RotatedText(260,205,$nombre_director,0);	    // Nombre Director
		$this->RotatedText(270,210,'Director(a)',0);			// ETIQUETA DIRECTOR.
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
    global $print_ann_lectivo;
    //Restauración de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
		$this->SetFont('Arial','B',8);
    // PRIMERA LINEA
        $this->Cell(120,4,'NIVEL/GRADO','LTR',0,'C');
        $this->Cell(30,4,'MATRICULA MAXIMA','LTR',0,'C');
        $this->Cell(30,4,'DESERCION','LTR',0,'C');
        $this->Cell(30,4,'REPITENCIA','LTR',0,'C');
        $this->Cell(30,4,'APROBADOS','LTR',0,'C');
        $this->Cell(30,4,'REPROBADOS','LTR',0,'C');
        $this->Cell(30,4,'SOBREEDAD','LTR',0,'C');
        $this->Cell(30,4,'MATRICULA FINAL','LTR',1,'C');
    // SEGUNDA LINEA
        $this->Cell(120,4,'','LBR',0,'C');
        for ($i=0; $i <=6 ; $i++) { 
            $this->Cell(10,4,'M',1,0,'C');
            $this->Cell(10,4,'F',1,0,'C');
            $this->Cell(10,4,'T',1,0,'C');
        }
	// Salto de línea
		$this->ln();
}
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
    $data = array();
    #Establecemos los márgenes izquierda, arriba y derecha: 
	    $pdf->SetMargins(5, 15, 5);
    #Establecemos el margen inferior: 
		$pdf->SetAutoPageBreak(true,5);
		$pdf->AliasNbPages();
		$pdf->AddPage();
    // Aqui mandamos texto a imprimir o al documento.
		$pdf->SetY(40);
		$pdf->SetX(5);
	// llamar al encabezado.
       $pdf->encabezado();
	// Ancho de las diferentes columnas	
		$ancho=array(0,120,30,10); //determina el ancho de las columnas
		$alto=array(0,5.5);
		$indicadores = array("maxima","desercion","repitencia","aprobados","reprobados","sobreedad","final");
		$tmm = array(); $tmf = array(); $tdm = array(); $tdf = array(); $trm = array(); $trf = array();
		$tam = array(); $taf = array(); $trem = array(); $tref = array(); $tsm = array(); $tsf = array(); $tfm = array(); $tff = array();
	   // Evaluar si existen registros.
		for($jh=0;$jh<=count($codigo_indicadores)-1;$jh++)
		{
			//
			$pdf->SetFont('Arial','',8);
			// CAMBIAR ETIQUETA PARA LA DESCRIPCIÓN DEL GRADO
			switch ($codigo_modalidad_matriz[$jh]) {
				case '02':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh] . convertirtexto(' años')),1,0,'L');
					break;
				case '06':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh] . ' General'),1,0,'L');
					break;
				case '07':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh] . convertirtexto(' Técnico')),1,0,'L');
					break;
				case '09':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh] . convertirtexto(' Técnico')),1,0,'L');
					break;
				case '10':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh] . convertirtexto(' Nocturna')),1,0,'L');
					break;
				case '11':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh] . convertirtexto(' Nocturna')),1,0,'L');
					break;
				case '12':
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' .($nombre_grado[$jh] . convertirtexto(' Nocturna')),1,0,'L');
					break;
				default:
					$pdf->Cell($ancho[1],$alto[1],$nombre_modalidad[$jh] . ' - ' . ($nombre_grado[$jh]),1,0,'L');
					break;
			}	// cierre del swicth
			// ARMAS LAS DIFERENTES CONSULTAS 
			// VARIABLES
			// variables para retenidos y promovidos.
			$total_masculino = 0; $total_femenino = 0;
			$total_final_masculino = 0; $total_final_femenino = 0;
			$calculo_final = 0; $total_final_masculino = 0; $total_final_femenino = 0;
			// variables para el cambio de INNER JOIN
				$innerJoinMatriculaM = ""; $innerJoinMatriculaF = "";
				$innerJoinMatriculaM_m = ""; $innerJoinMatriculaF_m = "";
				$innerJoinMatriculaM_r = ""; $innerJoinMatriculaF_r = "";
				$promovidos_retenidos = "";
			// Evaluar si existen registros INDICADORES.
			for($ind=0;$ind<=count($indicadores)-1;$ind++)
			{
				// CAMBIAR ETIQUETA PARA LA DESCRIPCIÓN DEL GRADO
				switch ($indicadores[$ind]) {
					case "maxima":
						$innerJoinMatriculaM = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'm'";
						$innerJoinMatriculaF = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'f'";
						$calculo_final = 0;
						break;
					case "desercion":
						$innerJoinMatriculaM = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'm' and am.retirado = 't' ";
						$innerJoinMatriculaF = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'f' and am.retirado = 't' ";
						$calculo_final = 0;
						break;
					case "repitencia":
						$innerJoinMatriculaM = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'm' and am.repitente = 't' ";
						$innerJoinMatriculaF = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'f' and am.repitente = 't' ";
						$calculo_final = 0;
						break;
					case "sobreedad":
						$innerJoinMatriculaM = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'm' and am.sobreedad = 't' ";
						$innerJoinMatriculaF = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'f' and am.sobreedad = 't' ";
						$calculo_final = 0;
						break;
					case "final":
						$innerJoinMatriculaM_m = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'm'";
						$innerJoinMatriculaF_m = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'f'";

						$innerJoinMatriculaM_r = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'm' and am.retirado = 't' ";
						$innerJoinMatriculaF_r = "INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and a.genero = 'f' and am.retirado = 't' ";

						$calculo_final = 1;
						break;
					case "aprobados":
						$promovidos_retenidos = "promovidos";
						$calculo_final = 2;
						break;
					case "reprobados":
						$promovidos_retenidos = "retenidos";
						$calculo_final = 2;
						break;
					default:
						$calculo_final = 0;
						break;
				}	// FIN DEL SWICTH INDICADORES

				if($calculo_final == 1){
					// ARMAR CONSULTAS
					//consulta para obtener el total de alumnos masculino.
					$query_total_masculino = "SELECT count(*) as total_alumnos_matricula_inicial_masculino
						FROM alumno a
							$innerJoinMatriculaM_m
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
							INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
							INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
									WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."'";
					//consulta para obtener el total de alumnos femenino.
					$query_total_femenino = "SELECT count(*) as total_alumnos_matricula_inicial_femenino
						FROM alumno a
							$innerJoinMatriculaF_m
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
							INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
							INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
								WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."'";
					// 
					//	CONSULTAS RESULT
					//
					$result_total_masculino = $db_link -> query($query_total_masculino);
					$result_total_femenino = $db_link -> query($query_total_femenino);
					//
					//	imprimir DATOS DE LA MATRICULA.
					//
					//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_masculino = 0;
							while($rows_total_alumnos_m = $result_total_masculino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_masculino = trim($rows_total_alumnos_m['total_alumnos_matricula_inicial_masculino']);
								}

						//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_femenino = 0;
							while($rows_total_alumnos_f = $result_total_femenino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_femenino = trim($rows_total_alumnos_f['total_alumnos_matricula_inicial_femenino']);
								}	
					//
					// total final
						$total_final_masculino = $total_alumnos_masculino;
						$total_final_femenino = $total_alumnos_femenino;
					//
					//
					// ARMAR CONSULTAS
					//consulta para obtener el total de alumnos masculino.
					$query_total_masculino = "SELECT count(*) as total_alumnos_matricula_inicial_masculino
						FROM alumno a
							$innerJoinMatriculaM_r
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
							INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
							INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
									WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."'";
					//consulta para obtener el total de alumnos femenino.
					$query_total_femenino = "SELECT count(*) as total_alumnos_matricula_inicial_femenino
						FROM alumno a
							$innerJoinMatriculaF_r
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
							INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
							INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
								WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."'";
					// 
					//	CONSULTAS RESULT
					//
					$result_total_masculino = $db_link -> query($query_total_masculino);
					$result_total_femenino = $db_link -> query($query_total_femenino);
					//
					//	imprimir DATOS DE LA MATRICULA.
					//
					//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_masculino = 0;
							while($rows_total_alumnos_m = $result_total_masculino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_masculino = trim($rows_total_alumnos_m['total_alumnos_matricula_inicial_masculino']);
								}

						//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_femenino = 0;
							while($rows_total_alumnos_f = $result_total_femenino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_femenino = trim($rows_total_alumnos_f['total_alumnos_matricula_inicial_femenino']);
								}	
					//
						$total_alumnos_masculino = $total_final_masculino - $total_alumnos_masculino;
						$total_alumnos_femenino = $total_final_femenino - $total_alumnos_femenino;
				}else if($calculo_final == 2){
					// ARMAR CONSULTAS
					//consulta para obtener el total de alumnos masculino.
					$query_total_masculino = "SELECT sum($promovidos_retenidos) as total_alumnos_promovidos_masculino
						FROM estadistica_grados eg
									WHERE btrim(eg.codigo_bachillerato_ciclo || eg.codigo_grado || eg.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."' and eg.genero = 'Masculino'";
					//consulta para obtener el total de alumnos femenino.
					$query_total_femenino = "SELECT sum($promovidos_retenidos) as total_alumnos_promovidos_femenino
						FROM estadistica_grados eg
								WHERE btrim(eg.codigo_bachillerato_ciclo || eg.codigo_grado || eg.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."' and eg.genero = 'Femenino'";
					// 
					//	CONSULTAS RESULT
					//
					$result_total_masculino = $db_link -> query($query_total_masculino);
					$result_total_femenino = $db_link -> query($query_total_femenino);
					//
					//	imprimir DATOS DE LA MATRICULA.
					//
					//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_masculino = 0;
							while($rows_total_alumnos_m = $result_total_masculino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_masculino = trim($rows_total_alumnos_m['total_alumnos_promovidos_masculino']);
									if($total_alumnos_masculino == null){
										$total_alumnos_masculino = 0;
									}
								}

						//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_femenino = 0;
							while($rows_total_alumnos_f = $result_total_femenino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_femenino = trim($rows_total_alumnos_f['total_alumnos_promovidos_femenino']);
									if($total_alumnos_femenino == null){
										$total_alumnos_femenino = 0;
									}
								}	
				}else{
					// ARMAR CONSULTAS
					//consulta para obtener el total de alumnos masculino.
					$query_total_masculino = "SELECT count(*) as total_alumnos_matricula_inicial_masculino
						FROM alumno a
							$innerJoinMatriculaM
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
							INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
							INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
									WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."'";
					//consulta para obtener el total de alumnos femenino.
					$query_total_femenino = "SELECT count(*) as total_alumnos_matricula_inicial_femenino
						FROM alumno a
							$innerJoinMatriculaF
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
							INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
							INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
								WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_indicadores[$jh]."'";
					// 
					//	CONSULTAS RESULT
					//
					$result_total_masculino = $db_link -> query($query_total_masculino);
					$result_total_femenino = $db_link -> query($query_total_femenino);
					//
					//	imprimir DATOS DE LA MATRICULA.
					//
					//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_masculino = 0;
							while($rows_total_alumnos_m = $result_total_masculino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_masculino = trim($rows_total_alumnos_m['total_alumnos_matricula_inicial_masculino']);
									if($total_alumnos_masculino == null){
										$total_alumnos_masculino = 0;
									}
								}

						//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
						$total_alumnos_femenino = 0;
							while($rows_total_alumnos_f = $result_total_femenino -> fetch(PDO::FETCH_BOTH))
								{
									$total_alumnos_femenino = trim($rows_total_alumnos_f['total_alumnos_matricula_inicial_femenino']);
									if($total_alumnos_femenino == null){
										$total_alumnos_femenino = 0;
									}
								}			
				}
					//
					//	IMPRIMIR VALORES 
					//
					switch ($codigo_modalidad_matriz[$jh]) {
						case '02':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						case '06':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						case '07':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						case '09':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						case '10':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						case '11':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						case '12':
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
						default:
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino,1,0,'C');
							$pdf->Cell($ancho[3],$alto[1],$total_alumnos_femenino,1,0,'C');
							$pdf->SetFont('Arial','B',10);
								$pdf->Cell($ancho[3],$alto[1],$total_alumnos_masculino+$total_alumnos_femenino,1,0,'C',true);
							$pdf->SetFont('Arial','',10);
							break;
					}	// cierre del swicth

					// CAMBIAR ETIQUETA PARA LA DESCRIPCIÓN DEL GRADO
					switch ($indicadores[$ind]) {
						case "maxima":
							$tmm[] = $total_alumnos_masculino;
							$tmf[] = $total_alumnos_femenino;
							break;
						case "desercion":
							$tdm[] = $total_alumnos_masculino;
							$tdf[] = $total_alumnos_femenino;
							break;
						case "repitencia":
							$trm[] = $total_alumnos_masculino;
							$trf[] = $total_alumnos_femenino;
							break;
						case "aprobados":
							$tam[] = $total_alumnos_masculino;
							$taf[] = $total_alumnos_femenino;
							break;
						case "reprobados":
							$trem[] = $total_alumnos_masculino;
							$tref[] = $total_alumnos_femenino;
							break;
						case "sobreedad":
							$tsm[] = $total_alumnos_masculino;
							$tsf[] = $total_alumnos_femenino;
							break;
						case "final":
							$tfm[] = $total_alumnos_masculino;
							$tff[] = $total_alumnos_femenino;
							break;
						}
				}	// FIN DE FOR INDICADORES
			// SALTO DE LINEA
				$pdf->ln();
        }	// cierre del for de la matriz que es rellenada
		// total final de matricula inicial o maxima.
			$pdf->Cell($ancho[1],$alto[1],'TOTAL',1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($tmm),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($tmf),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($tmm) + array_sum($tmf),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
		// total final desercion
			$pdf->Cell($ancho[3],$alto[1],array_sum($tdm),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($tdf),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($tdm) + array_sum($tdf),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
		// total final repitencia
			$pdf->Cell($ancho[3],$alto[1],array_sum($trm),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($trf),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($trm) + array_sum($trf),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
		// total final aprobados
			$pdf->Cell($ancho[3],$alto[1],array_sum($tam),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($taf),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($tam) + array_sum($taf),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
		// total final reprobados
			$pdf->Cell($ancho[3],$alto[1],array_sum($trem),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($tref),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($trem) + array_sum($tref),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
		// total final sobreedad
			$pdf->Cell($ancho[3],$alto[1],array_sum($tsm),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($tsf),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($tsm) + array_sum($tsf),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
		// total final matricula final
			$pdf->Cell($ancho[3],$alto[1],array_sum($tfm),1,0,'C');
			$pdf->Cell($ancho[3],$alto[1],array_sum($tff),1,0,'C');
			$pdf->SetFont('Arial','B',10);
				$pdf->Cell($ancho[3],$alto[1],array_sum($tfm) + array_sum($tff),1,0,'C',true);
			$pdf->SetFont('Arial','',10);
			//
			$pdf->ln(); 
// Salida del pdf.
	$modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
	$print_nombre = 'MEMORIA ESTADISTICA ' . $nombre_ann_lectivo;
	$pdf->Output($print_nombre,$modo);