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
// cambiar el formato de date.
$date = fechaYMD();
//
// variables y consulta a la tabla.
					$fecha_desde = $_REQUEST['fecha_desde'];
					$fecha_hasta = $_REQUEST['fecha_hasta'];
					$fecha_inicio = $_REQUEST['fecha_inicio'];
					$nombre_ann_lectivo = substr($_REQUEST['fecha_desde'],0,4);
					$codigo_contratacion = $_REQUEST['codigo_contratacion'];
					$codigo_turno = $_REQUEST['codigo_turno'];
					$codigo_contratacion_turno = $codigo_contratacion . $codigo_turno;
					$db_link = $dblink;
						// armando el Query. PARA LA TABLA HISTORIAL.
							$query_personal = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
												btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, p.codigo_estatus, tur.nombre as nombre_turno, tc.nombre as nombre_contratacion
												FROM personal_licencias_permisos lp
												INNER JOIN personal p ON p.id_personal = lp.codigo_personal
												INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
												INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_permiso
												INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
												WHERE p.codigo_estatus = '01' and btrim(lp.codigo_contratacion || lp.codigo_turno) = '$codigo_contratacion_turno'";
						// Query para revisar la tabla tipo de licencia.
							$query_licencia_permiso = "SELECT codigo, nombre, saldo, minutos from tipo_licencia_o_permiso ORDER BY codigo";
						// Query para revisar la tabla personal.
							$query_nombres_personal = "SELECT p.id_personal as codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_c FROM personal p WHERE p.codigo_estatus = '01' ORDER BY nombre_c";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_personal = $dblink -> query($query_personal);
							$consulta_licencia_permiso = $dblink -> query($query_licencia_permiso);
							$consulta_encabezado = $dblink -> query($query_personal);
							$consulta_nombres = $dblink -> query($query_nombres_personal);
						// Obtener el encabezado.
							    while($listadoPersonalE = $consulta_encabezado -> fetch(PDO::FETCH_BOTH))
							      {
									$nombre_turno = trim($listadoPersonalE['nombre_turno']);
									$nombre_contratacion = utf8_encode(trim($listadoPersonalE['nombre_contratacion']));
									break;
								  }
                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
								//Crear una línea. Fecha.
								$dia = strftime("%d");		// El Día.
								$dato = explode("-",$fecha_desde);
								$dato_entero = (int)$dato[1];
								$nombre_mes = $meses[$dato_entero-1];     // El Mes.
				
// leer tabla de docentes. codigo y nombre en matriz.
   $codigo_docente_a = array(); $nombre_docente_a = array();
     while($row = $consulta_nombres-> fetch(PDO::FETCH_BOTH))
       {
        // repetir el proceso hasta que la tabla ya no tenga datos.
						$codigo_docente_a[] = $row['codigo_personal'];
						$nombre_docente_a[] = $row['nombre_c'];
       }
//
// leer tabla tipo de licencia o permiso. codigo y nombre en matriz.
//
	// Query para revisar la tabla tipo de licencia.
	 	 $query_licencia_permiso = "SELECT codigo, nombre, saldo, minutos from tipo_licencia_o_permiso ORDER BY codigo";
	// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
		 $consulta_licencia_permiso = $dblink -> query($query_licencia_permiso);
	//	variables array.							
		$codigo_licencia_o_permiso = array(); $saldo_licencia_o_permiso = array(); $imprimir = array(); $num=0; $pase = 0;
			while($listadoPersonalLyP = $consulta_licencia_permiso -> fetch(PDO::FETCH_BOTH))
				{
					 // repetir el proceso hasta que la tabla ya no tenga datos.
						$codigo_licencia_o_permiso[] = $listadoPersonalLyP['codigo'];
						$saldo_licencia_o_permiso[] = $listadoPersonalLyP['saldo'];
						$minutos_licencia_o_permiso[] = $listadoPersonalLyP['minutos'];
				}
       // coantar elmentos para recorrer DE LOS DOCENTES EN LA TABLA PERSONAL DOCENTE.
       $count = count($codigo_docente_a);
       for($i=0;$i<$count;$i++)
       {         
				// contar para para la licencias o permisos.
				$count_lic = count($codigo_licencia_o_permiso);
							 for($j=0;$j<$count_lic;$j++)
								{
									// PROCESO PARA OBTENER EL CONSUMO DEL MES EN VIGENCIA. DIA, HORAS, MINTUOS.
									$query_sum_ = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos FROM personal_licencias_permisos 
													WHERE codigo_personal = '".$codigo_docente_a[$i]."' and fecha >= '".$fecha_desde."' and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_permiso = '".$codigo_licencia_o_permiso[$j]."' and codigo_turno = '".$codigo_turno."'";
									$consulta_sum_ = $dblink -> query($query_sum_);
									
									if($consulta_sum_ == true){
										// while para los dias, horas y minutos per por mes.
												 while($row_print = $consulta_sum_ -> fetch(PDO::FETCH_BOTH))
													{
															$dia = $row_print['dia'];
															$hora = $row_print['hora'];
															$minutos = $row_print['minutos'];
																// calcular subtotales y totales.
																//////////////////////////////////////////////////
															// Calcular los dias minutos y segundos.
																if($j == 7)
																	{
																		$total_minutos = ($dia*5*60) + ($hora*60) + ($minutos);
																		
																		if($dia > 0 || $hora > 0 || $minutos > 0){
																			$pase++;
																		}
																		
																		$tramite_dia[$i][$j] = segundosToCadenaD($total_minutos);
																		$tramite_hora[$i][$j] = segundosToCadenaH($total_minutos);
																		$tramite_minutos[$i][$j] = segundosToCadenaM($total_minutos);
																	}
													}	// while que examina el bucle que cuenta los dias, horas y minutos.
									} // Verificar la consulta
									
									
									
									// PROCESO PARA OBTENER EL CONSUMO DEL MES EN VIGENCIA. DIA, HORAS, MINTUOS.
									$query_saldo_ = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos FROM personal_licencias_permisos 
													WHERE codigo_personal = '".$codigo_docente_a[$i]."' and fecha >= '".$fecha_inicio."' and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_permiso = '".$codigo_licencia_o_permiso[$j]."' and codigo_turno = '".$codigo_turno."'";
									$consulta_saldo_ = $dblink -> query($query_saldo_);
									
									if($consulta_saldo_ == true){
										// while para los dias, horas y minutos per por mes.
												 while($row_print = $consulta_saldo_ -> fetch(PDO::FETCH_BOTH))
													{
															$dia = $row_print['dia'];
															$hora = $row_print['hora'];
															$minutos = $row_print['minutos'];
																// calcular subtotales y totales.
																//////////////////////////////////////////////////
															// Calcular los dias minutos y segundos.
																if($j == 7)
																	{
																		$total_minutos = ($dia*5*60) + ($hora*60) + ($minutos);
																		
																		$tramite_dia_saldo[$i][$j] = segundosToCadenaD($total_minutos);
																		$tramite_hora_saldo[$i][$j] = segundosToCadenaH($total_minutos);
																		$tramite_minutos_saldo[$i][$j] = segundosToCadenaM($total_minutos);
																	}
													}	// while que examina el bucle que cuenta los dias, horas y minutos.
									} // Verificar la consulta
		}	// for que recorre los tipos de licencias.

								if($pase > 0){$imprimir[$i] = true;}else{$imprimir[$i] = false;}
									$pase = 0;
       }	// for que recorre el personal docente.


class PDF extends FPDF
{
//Cabecera de página
function Header(){
    //Logo
    global $nombre_mes, $nombre_contratacion, $nombre_ann_lectivo, $nombre_turno;
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_dos']; $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',12);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(0,4,utf8_decode('DEPARTAMENTAL DE EDUCACIÓN DE SANTA ANA'),0,1,'C');
    $this->Cell(0,4,'REPORTE DE LLEGADAS TARDIAS E INASISTENCIAS INJUSTIFICADAS',0,1,'C');
    $this->Cell(0,4,'CENTRO EDUCATIVO: '.utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(0,4,'INFORME CORRESPONDIENTE AL MES DE: '.$nombre_mes,0,0,'C');
    $this->Cell(0,4,utf8_decode('CÓDIGO: ').$_SESSION['codigo'],0,0,'R');
    $this->SetXY(0,0);
    $this->SetFont('Arial','B',6);
    $this->Cell(40,4,$nombre_contratacion,0,1,'L');
}
//Pie de página
function Footer(){
  // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');						
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
     //Crear una línea de la primera firma.
    $this->Line(15,200,90,200);
    //Crear una línea de la segunda firma.
    $this->Line(175,200,250,200);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y "); $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
        //Nombre Subdirector(a)
    $this->SetXY(40,200);
    $this->Cell(20,6,'Subdirector(a)',0,1,'C');
        //Nombre Director
    $this->SetXY(200,200);
    $this->Cell(20,6,'Director',0,1,'C');
    }
    
//Tabla coloreada
function FancyTable($header){
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,255,255);$this->SetTextColor(0);$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);$this->SetFont('','B',7);
    //Cabecera
    //(numero, nit, nombre del docnete, sb, ss, hc, fecha, dia, hora, minu. concepto)
    $w=array(5,25,30,60,20,20,20,20,10,10,10,30); //determina el ancho de las columnas
    $w2=array(5,25,30,60,60,20,30,30); //determina el ancho de las columnas

    // primera fila
    $this->Cell($w2[0],5,'','LTR',0,'C',1);
    $this->Cell($w2[1],5,'','LTR',0,'C',1);
    $this->Cell($w2[2],5,'','LTR',0,'C',1);
    $this->Cell($w2[3],5,'','LTBR',0,'C',1);
    $this->Cell($w2[4],5,'PARTIDA',1,0,'C',1);
    $this->Cell($w2[5],5,'','LTR',0,'C',1);
    $this->Cell($w2[6],5,'HORA/TIEMPO',1,0,'C',1);
    $this->Cell($w2[7],5,'','LTR',0,'C',1);
    $this->LN();
       
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),'LBR',0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(255,255,255);$this->SetTextColor(0);$this->SetFont('');
    //Datos
    $fill=false;}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Letter'); $data = array();
//Títulos de las columnas
    $header=array('Nº','NIP','NIT','Nombre del Docente','Sueldo Base','Sobre Sueldo','Hora Clase','Fecha','Día','Hora','Minutos','Concepto');
    $pdf->AliasNbPages(); $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(30); $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// Salto de línea.
    $pdf->ln();
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    //cabecera
    //(numero, nit, nombre del docnete, sb, ss, hc, fecha, dia, hora, minu. concepto)
    $w=array(5,25,30,60,20,20,20,20,10,10,10,30); //determina el ancho de las columnas
    $w2=array(5,15,20,50,60,20,30,30); //determina el ancho de las columnas
	
    $fill=false; $num=1; $minutos_sum = 0; $dia_sum = 0; $horas_sum = 0;$numero_linea = 1;
       // conteo de los docentes codigo.
       $count = count($codigo_docente_a);
       for($i=0;$i<$count;$i++)
       {
					$codigo_personal = $codigo_docente_a[$i];
					$codigo_docente = $codigo_docente_a[$i];
					$pase = 0;
					$minutos_sub = 0;
					$dia_sub = 0;
					$horas_sub = 0;
				// contar para para la licencias o permisos.
					$count_lic = count($codigo_licencia_o_permiso);
								 for($j=0;$j<$count_lic;$j++)
						{	        
								// Armar query para especificar cada licencia o permiso.
 								$query_codigo_licencias = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
										btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, p.nit, p.nip, ps.salario, ps.codigo_tipo_contratacion, tlp.nombre as nombre_licencia_permiso, tlp.codigo as codigo_licencia, tur.nombre as nombre_turno
											FROM personal_licencias_permisos lp
												INNER JOIN personal p ON p.id_personal = lp.codigo_personal
												INNER JOIN personal_salario ps ON p.id_personal = ps.codigo_personal
												INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
												INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_permiso
												INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
												WHERE lp.codigo_personal = '$codigo_personal' and btrim(ps.codigo_tipo_contratacion || lp.codigo_turno) = '$codigo_contratacion_turno' and lp.fecha >= '$fecha_desde' and lp.fecha <= '$fecha_hasta' and lp.codigo_licencia_permiso = '$codigo_licencia_o_permiso[$j]'
												ORDER BY lp.fecha";
																
								$consulta_codigo_licencia_permiso = $dblink -> query($query_codigo_licencias);
								// revisar si hay registros.
								$num_registros = $consulta_codigo_licencia_permiso -> rowCount();
									 
				 if($num_registros != 0){
						 while($row_print = $consulta_codigo_licencia_permiso -> fetch(PDO::FETCH_BOTH))
							 {
								 // Variables.
								 $fecha = $row_print['fecha'];
								 $dia = $row_print['dia'];
								 $hora = $row_print['hora'];
								 $minutos = $row_print['minutos'];
								 $nit = $row_print['nit'];
								 $nip = $row_print['nip'];
								 $nombre_docente = $row_print['nombre_docente'];
								 $codigo_contratacion = $row_print['codigo_contratacion'];
								 $observacion = $row_print['observacion'];
								 $codigo_licencia = $row_print['codigo_licencia'];
								 $codigo_tipo_contratacion = $row_print['codigo_tipo_contratacion'];
							 
								 // acumular los datos llegadas tardes.
								 if($j == 7){				
									$pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
										if($codigo_docente == ($row_print['codigo_personal']) && $pase == 0){$pase = 1;}
								 
								 if($pase == 1){
										//$num++;
										$pdf->Cell($w[0],5.8,$numero_linea.$num,1,0,'C',$fill);        // núermo correlativo
										$pdf->Cell($w[1],5.8,$nip,1,0,'L',$fill);  // NIP
										$pdf->Cell($w[2],5.8,$nit,1,0,'L',$fill);  // NIT
										$pdf->Cell($w[3],5.8,cambiar_de_del($nombre_docente),1,0,'L',$fill);  // Nombre del docente
								 
										if($codigo_tipo_contratacion == '01'){
										// sueldo base
										$pdf->Cell($w[4],5.8,'$ '.$row_print['salario'],1,0,'C',$fill);}
										else{$pdf->Cell($w[4],5.8,'',1,0,'C',$fill);}
										
										if($codigo_tipo_contratacion == '02'){
										// sobre sueldo
										$pdf->Cell($w[5],5.8,'$ '.$row_print['salario'],1,0,'L',$fill);}
										else{$pdf->Cell($w[5],5.8,'',1,0,'C',$fill);}
										
										if($codigo_tipo_contratacion == '03'){
										// horas clase mined
										$pdf->Cell($w[6],5.8,'$ '.$row_print['salario'],1,0,'L',$fill);}
										else{$pdf->Cell($w[5],5.8,'',1,0,'C',$fill);}
										$pase = 2;
								 }
								 else{
									//$num++;
												$pdf->Cell($w[0],5.8,$num.'',1,0,'C',$fill);        // núermo correlativo
												$pdf->Cell($w[1],5.8,'',1,0,'L',$fill);  // NIP
												$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);  // NIT
												$pdf->Cell($w[3],5.8,'',1,0,'L',$fill);  // Nombre del docente
												// sueldo base
												$pdf->Cell($w[4],5.8,'',1,0,'L',$fill);
												// sobre sueldo
												$pdf->Cell($w[5],5.8,'',1,0,'L',$fill);
												// horas clase mined
												$pdf->Cell($w[6],5.8,'',1,0,'L',$fill);
								 }
						 
								 // fecha
								 $pdf->Cell($w[7],5.8,cambiaf_a_normal($fecha),1,0,'C',$fill);
								 
								 if($dia == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{{$pdf->Cell($w[8],5.8,$dia,1,0,'C',$fill);}}
								 if($hora == 0){$pdf->Cell($w[9],5.8,'',1,0,'C',$fill);}else{{$pdf->Cell($w[9],5.8,$hora,1,0,'C',$fill);}}
								 if($minutos == 0){$pdf->Cell($w[10],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[10],5.8,$minutos,1,0,'C',$fill);} 	// minutos
								 
								 $minutos_sum = $minutos_sum + $minutos;       
								 $minutos_sub = $minutos_sub + $minutos;
						 
								 $horas_sum = $horas_sum + $hora;       
								 $horas_sub = $horas_sub + $hora;
								 
								 $dia_sum = $dia_sum + $dia;       
								 $dia_sub = $dia_sub + $dia;
								 
								 // concepto
								 $pdf->Cell($w[11],5.8,$observacion,1,0,'L',$fill); 
								 $pdf->Ln();
								 
								 $fill=!$fill;
								 $num++;
								 // Salto de página.
										 if($num == 20 || $num == 36){	
											$pdf->AddPage();
											$pdf->SetY(30); $pdf->SetX(10);
											$pdf->Ln();
											$pdf->FancyTable($header);}
										 }
							}	// while que examina el bucle que cuenta los dias, horas y minutos.
				 }
	   }	// for que recorre los tipos de licencias.
	   
		$fila = $num_registros;
		if($fila > 0){
		 
		 // para el salto de pagina dentro de los sub-totales de cada docente.
		$fill=!$fill;
		$num++; $numero_linea++;
		// Salto de página.
		    if($num == 18 || $num == 36){
		     
		     // imprimir los resultados.
		$pdf->Cell(200,5.8,'TOTAL',0,0,'R',$fill);
		//$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);
		if($dia_sum == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[8],5.8,$dia_sum,1,0,'C',$fill);} 	// minutos
		if($horas_sum == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[8],5.8,$horas_sum,1,0,'C',$fill);} 	// minutos
		if($minutos_sum == 0){$pdf->Cell($w[10],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[10],5.8,$minutos_sum,1,0,'C',$fill);} 	// minutos
		$pdf->Ln(); $pdf->Ln();
	       // imprimir la fecha.
		$pdf->Cell(200,5.8,'FECHA DE ENTREGA',0,0,'R',$fill);
		$pdf->Cell(30,5.8,cambiaf_a_normal($date),1,0,'C',$fill); 	// minutos
		$minutos_sum = 0;
		     $pdf->AddPage();
		     $pdf->SetY(30); $pdf->SetX(10);
		     $pdf->Ln();
		     $pdf->FancyTable($header);}
		    
		
		// imprimir el subtotal de cada docentes (minutos de llegadas tardes
		$pdf->Cell(200,5.8,'TOTAL',0,0,'R',$fill);
		//$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);
		if($dia_sub == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[8],5.8,$dia_sub,1,0,'C',$fill);} 	// minutos
		if($horas_sub == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[8],5.8,$horas_sub,1,0,'C',$fill);} 	// minutos
		if($minutos_sub == 0){$pdf->Cell($w[10],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[10],5.8,$minutos_sub,1,0,'C',$fill);} 	// minutos
		$pdf->Ln(); $pdf->Ln();}
       }	// for que recorre el personal docente.
       
	   /*
	      // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $num;
                $linea_faltante =  20 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetX(10);
						$pdf->Cell($w[0],5.8,$numero++,1,0,'C',$fill);  // N| de Orden.
						$pdf->Cell($w[1],5.8,'',1,0,'L',$fill);  // NIP
						$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);  // NIT
						$pdf->Cell($w[3],5.8,'',1,0,'L',$fill);  // Nombre del docente		
						
						// sueldo base
						$pdf->Cell($w[4],5.8,'',1,0,'L',$fill);
						// sobre sueldo
						$pdf->Cell($w[5],5.8,'',1,0,'L',$fill);
						// horas clase mined
						$pdf->Cell($w[6],5.8,'',1,0,'L',$fill);
						// fecha
						$pdf->Cell($w[7],5.8,'',1,0,'C',$fill);
					
						$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);
						$pdf->Cell($w[9],5.8,'',1,0,'C',$fill);
						$pdf->Cell($w[10],5.8,'',1,0,'C',$fill); 	// minutos
						// concepto
						$pdf->Cell($w[11],5.8,'',1,0,'L',$fill); 
                      $pdf->Ln();   
                      $fill=!$fill;
                  }*/

// imprimir los resultados.
		$pdf->Cell(200,5.8,'TOTAL',0,0,'R',$fill);
		//$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);
		if($dia_sum == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[8],5.8,$dia_sum,1,0,'C',$fill);} 	// minutos
		if($horas_sum == 0){$pdf->Cell($w[8],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[8],5.8,$horas_sum,1,0,'C',$fill);} 	// minutos
		if($minutos_sum == 0){$pdf->Cell($w[10],5.8,'',1,0,'C',$fill);}else{$pdf->Cell($w[10],5.8,$minutos_sum,1,0,'C',$fill);} 	// minutos
		$pdf->Ln(); $pdf->Ln();
// imprimir la fecha.
		$pdf->Cell(200,5.8,'FECHA DE ENTREGA',0,0,'R',$fill);
		$pdf->Cell(30,5.8,cambiaf_a_normal($date),1,0,'C',$fill); 	// minutos
		
		
// Salida del pdf.
    $pdf->Output();
?>