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
					$codigo_personal = $_REQUEST['codigo_personal'];
					$fecha_l_y_p = substr($_REQUEST['fecha'],0,4);
					$codigo_contratacion = $_REQUEST['codigo_contratacion'];
				   // armando el Query. PARA LA TABLA HISTORIAL.
						// armando el Query. PARA LA TABLA HISTORIAL.
							$query_personal = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
												btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, tur.nombre as nombre_turno, tc.nombre as nombre_contratacion
												FROM personal_licencias_permisos lp
												INNER JOIN personal p ON p.id_personal = lp.codigo_personal
												INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
												INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_permiso
												INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
												WHERE lp.codigo_personal = '$codigo_personal' and btrim(lp.codigo_contratacion || lp.codigo_turno) = '$codigo_contratacion' and TO_CHAR(lp.fecha,'YYYY') = '$fecha_l_y_p'
												ORDER BY lp.fecha";
						// Query para revisar la tabla tipo de licencia.
							$query_licencia_permiso = "SELECT codigo, nombre, saldo, minutos from tipo_licencia_o_permiso ORDER BY codigo";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_personal = $dblink -> query($query_personal);
							$consulta_licencia_permiso = $dblink -> query($query_licencia_permiso);
							$consulta_encabezado = $dblink -> query($query_personal);
						// Obtener el encabezado.
							    while($listadoPersonalE = $consulta_encabezado -> fetch(PDO::FETCH_BOTH))
							      {
									$nombre_docente = trim($listadoPersonalE['nombre_docente']);
									$encabezado="Docente: "."<b>".$nombre_docente."</b>";
									$nombre_turno = trim($listadoPersonalE['nombre_turno']);
									$nombre_contratacion = utf8_encode(trim($listadoPersonalE['nombre_contratacion']));
									break;
								  }

		
	    
class PDF extends FPDF
{
//Cabecera de página
function Header(){
 global $nombre_contratacion, $nombre_turno;
    //Logo
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_dos']; $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',12);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(150,4,'CONTROL DE LICENCIAS Y PERMISOS DEL PERSONAL DOCENTE',0,1,'C');
    $this->SetFont('Arial','B',10);
    $this->SetX(30);
    $this->Cell(150,4,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(190,4,utf8_decode('Tipo de Contratación: '.$nombre_contratacion.' - '.'Turno: '.$nombre_turno),0,1,'C');
    $this->SetX(30);
    $this->SetXY(0,0);
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
    $this->Line(15,270,90,270);
    //Crear una línea de la segunda firma.
    $this->Line(130,270,190,270);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y "); $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
        //Nombre Subdirector(a)
    $this->SetXY(40,270);
    $this->Cell(20,6,'Subdirector(a)',0,1,'C');
        //Nombre Director
    $this->SetXY(150,270);
    $this->Cell(20,6,'Director',0,1,'C');
    }
    
//Tabla coloreada
function FancyTable($header){
    global $fecha_l_y_p, $nombre_docente;
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,255,255);$this->SetTextColor(0);$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);$this->SetFont('','B',12);
    //Cabecera
    //(numero, tipo de permiso, turno, fecha, dia, hora, minutos)
    $w=array(5,100,20,20,15,15,15); //determina el ancho de las columnas
    $w2=array(5,50,60,15,145,10,30,25); //determina el ancho de las columnas
 
     $this->Cell(190,12,utf8_decode($nombre_docente)." - ".$fecha_l_y_p,1,1,'C');
     $this->SetFont('','B',9);
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetLineWidth(.3);$this->SetFont('','B',9);
    $this->SetFillColor(255,255,255);$this->SetTextColor(0);$this->SetFont('');
    //Datos
    $fill=false;}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter'); $data = array();
//Títulos de las columnas
    $header=array('Nº','Tipo de Licencia o Permiso','Turno','Fecha','Día','Hora','Minutos');
    $pdf->AliasNbPages(); $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(20); $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// Salto de línea.
    $pdf->ln();
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    //cabecera
        //(numero, tipo de permiso, turno, fecha, dia, hora, minutos)
    $w=array(5,100,20,20,15,15,15); //determina el ancho de las columnas
    $w2=array(40,45); //determina el ancho de las columnas
	
	// colores del fondo, texto, línea.
		$pdf->SetFillColor(230,227,227);
		$fill=false; $num=1;
		$fill2=true;
// INICIAR EL PROCESO DE IMPRESION EN PANTALLA.
//
// DECLARAR VARIABLES PARA LAS MATRICES.
//
    $numero = 1;
//
// leer tabla tipo de licencia o permiso. codigo y nombre en matriz.
///
	// Query para revisar la tabla tipo de licencia.
	 	 $query_licencia_permiso = "SELECT codigo, nombre, saldo, minutos from tipo_licencia_o_permiso ORDER BY codigo";
	// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
		 $consulta_licencia_permiso = $dblink -> query($query_licencia_permiso);
	//	variables array.							
		$codigo_licencia_o_permiso = array(); $saldo_licencia_o_permiso = array(); $imprimir = array(); $num=0;
			while($listadoPersonalLyP = $consulta_licencia_permiso -> fetch(PDO::FETCH_BOTH))
				{
					 // repetir el proceso hasta que la tabla ya no tenga datos.
						$codigo_licencia_o_permiso[] = $listadoPersonalLyP['codigo'];
						$saldo_licencia_o_permiso[] = $listadoPersonalLyP['saldo'];
						$minutos_licencia_o_permiso[] = $listadoPersonalLyP['minutos'];
				}

 // contar para para la licencias o permisos.
	 $count_lic = count($codigo_licencia_o_permiso);
          for($j=0;$j<$count_lic;$j++)
           {	        
				// Armar query para especificar cada licencia o permiso.
				$query_codigo_licencias = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
						btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, tlp.nombre as nombre_licencia_permiso, tur.nombre as nombre_turno
							FROM personal_licencias_permisos lp
								INNER JOIN personal p ON p.id_personal = lp.codigo_personal
								INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
								INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_permiso
								INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
								WHERE lp.codigo_personal = '$codigo_personal' and btrim(lp.codigo_contratacion || lp.codigo_turno) = '$codigo_contratacion' and TO_CHAR(lp.fecha,'YYYY') = '$fecha_l_y_p' and lp.codigo_licencia_permiso = '$codigo_licencia_o_permiso[$j]'
								ORDER BY lp.fecha";
												
				$consulta_codigo_licencia_permiso = $dblink -> query($query_codigo_licencias);
				// revisar si hay registros.
				$num_registros = $consulta_codigo_licencia_permiso -> rowCount();
									
				if($num_registros !=0){
						while($row_print = $consulta_codigo_licencia_permiso -> fetch(PDO::FETCH_BOTH))
						  {
							// Variables.
							$num++;
							$dia = $row_print['dia'];
							$hora = $row_print['hora'];
							$minutos = $row_print['minutos'];
							$nombre_licencia_permiso = $row_print['nombre_licencia_permiso'];
							
							$fecha = cambiaf_a_normal($row_print['fecha']);
							$pdf->Cell($w[0],5.8,$num,1,0,'L',$fill);  // NUM
							$pdf->Cell($w[1],5.8,utf8_decode($row_print['nombre_licencia_permiso']),1,0,'L',$fill);  // tipo de licencia o permiso.
							$pdf->Cell($w[2],5.8,utf8_decode($row_print['nombre_turno']),1,0,'L',$fill);  // nombre turno
							$pdf->Cell($w[3],5.8,$fecha,1,0,'L',$fill);  // fecha
							$pdf->Cell($w[4],5.8,$row_print['dia'],1,0,'C',$fill);  // dia
							$pdf->Cell($w[5],5.8,$row_print['hora'],1,0,'C',$fill);  // hora
							$pdf->Cell($w[6],5.8,$row_print['minutos'],1,0,'C',$fill);  // minutos
							$pdf->Ln();
								// calcular subtotales y totales.
								//////////////////////////////////////////////////
							// Calcular los dias minutos y segundos.
								if($j >= 0 and $j <= 7)
									{
										$total_minutos = ($dia*5*60) + ($hora*60) + ($minutos);
													
										$tramite_dia[] = segundosToCadenaD($total_minutos);
										$tramite_hora[] = segundosToCadenaH($total_minutos);
										$tramite_minutos[] = segundosToCadenaM($total_minutos);
									}
							$numero++;
							// Salto de página.
									if($numero == 25 || $numero == 50 || $numero == 75){
										$pdf->AddPage();
										$pdf->ln();$pdf->ln();$pdf->ln();$pdf->ln();$pdf->ln();$pdf->ln();$pdf->ln();
										$pdf->FancyTable($header);
										// colores del fondo, texto, línea.
										$pdf->SetFillColor(230,227,227);
										$numero = 1;
									}
						}   // FIN DEL WHILE donde se encuentran todos los registros de las licencias.
							///////////////////////////////////////////////////////////////////////////////////////////////////////		
									if($j >= 0 and $j <= 7)
										{
											$sub_sin_dia = array_sum($tramite_dia);
											$sub_sin_hora = array_sum($tramite_hora);
											$sub_sin_minutos = array_sum($tramite_minutos);
														
											$minutos_x_dias = $minutos_licencia_o_permiso[$j];
											$minutos_subtotal = ($sub_sin_dia*5*60) + ($sub_sin_hora*60) + ($sub_sin_minutos);
											$minutos = $minutos_x_dias - $minutos_subtotal;
											$saldo_x = segundosToCadena($minutos);

											$pdf->SetFont('Arial','B',11);														
											$pdf->Cell($w[0],5.8,'',1,0,'L',$fill2);  // fecha
											$pdf->Cell($w[1],5.8,utf8_decode('Saldo '.$saldo_licencia_o_permiso[$j] . ' Días. ' . $saldo_x),1,0,'R',$fill2);  // dia
											$pdf->Cell($w[2],5.8,'',1,0,'L',$fill2);  // fecha
											$pdf->Cell($w[3],5.8,'TOTAL',1,0,'L',$fill2);  // fecha
											$pdf->Cell($w2[1],5.8,segundosToCadena($minutos_subtotal),1,0,'C',$fill2);  // dia
											$pdf->SetFont('Arial','',9);
											$pdf->Ln(); $pdf->Ln();
										}
										// regresar el valor de num a 0.
										$num = 0;
				}	// Condición para revisar si hay registros a través del count
					// Eliminar los elmentos de la array que acumula los dia, minutos y horas.
						unset($tramite_dia, $tramite_hora, $tramite_minutos);
	   }	// for para repeir los x tipo de licencia o permiso que existan,
// Salida del pdf.
    $pdf->Output();
?>