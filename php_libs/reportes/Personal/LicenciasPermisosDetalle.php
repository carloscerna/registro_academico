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
	$codigo_tipo_contratacion = substr($_REQUEST['codigo_contratacion'],0,2);
	// Calcular el Disponible segùn Tipo de Contratación.
		$calculo_horas = 5;
		if($codigo_tipo_contratacion == "05"){ // PAGADOS POR EL CDE.
			$calculo_horas = 8;
		}
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
					$nombre_contratacion = mb_convert_encoding(trim($listadoPersonalE['nombre_contratacion']),'ISO-8859-1','UTF-8');
					break;
					}
class PDF extends FPDF
{
//Cabecera de página
function Header(){
 global $nombre_contratacion, $nombre_turno;
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_uno']; $this->Image($img,10,5,12,15);     //Logo
    $this->SetFont('Arial','B',12);     //Arial bold 15
    //Título
    $this->Cell(205,4,'CONTROL DE LICENCIAS Y PERMISOS DEL PERSONAL DOCENTE',0,1,'C');
    $this->SetFont('Arial','B',10);
    $this->Cell(205,4,mb_convert_encoding(trim(($_SESSION['institucion'])),'ISO-8859-1','UTF-8'),0,1,'C');
	$encabezado_linea_3 = trim(mb_convert_encoding('Tipo de Contratación: '.$nombre_contratacion.' - '.'Turno: '.$nombre_turno,'ISO-8859-1','UTF-8'));
    $this->Cell(205,4,$encabezado_linea_3,0,1,'C');
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
    $w=array(5,90,30,20,15,15,15); //determina el ancho de las columnas
    $w2=array(5,50,60,15,145,10,30,25); //determina el ancho de las columnas
 
     $this->Cell(190,12,mb_convert_encoding(($nombre_docente)." - ".$fecha_l_y_p,'ISO-8859-1','UTF-8'),1,1,'C');
     $this->SetFont('','B',9);
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,mb_convert_encoding(($header[$i]),'ISO-8859-1','UTF-8'),1,0,'C',1);
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
	$pdf->SetAutoPageBreak(true, 20);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetXY(10,20); 
// Salto de línea.
    $pdf->ln();
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    //cabecera
    //(numero, tipo de permiso, turno, fecha, dia, hora, minutos)
    $w=array(5,90,30,20,15,15,15); //determina el ancho de las columnas
    $w2=array(40,45); //determina el ancho de las columnas
	// colores del fondo, texto, línea.
		$pdf->SetFillColor(230,227,227);
		$fill=false; $num=1;
		$fill2=true;
// INICIAR EL PROCESO DE IMPRESION EN PANTALLA.
//
// DECLARAR VARIABLES PARA LAS MATRICES.
//
    $numero = 1; $margen_inferior = 10; $margen_superior = 20;
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
						//$minutos_licencia_o_permiso[] = $listadoPersonalLyP['minutos'];
						$minutos_licencia_o_permiso[] = $listadoPersonalLyP['saldo'] * $calculo_horas * 60;
				}
//
	$valor_y = $pdf->GetY();
	$pagina_alto = $pdf->GetPageHeight();
 // contar para para la licencias o permisos.
	 $count_lic = count($codigo_licencia_o_permiso);
          for($j=0;$j<$count_lic;$j++)
           {	
			// declarar matrices.
				$tramite_dia = array(); $tramite_hora = array(); $tramite_minutos = array();        
				// Armar query para especificar cada licencia o permiso.
				$query_codigo_licencias = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
					btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, tlp.nombre as nombre_licencia_permiso, tur.nombre as nombre_turno
						FROM personal_licencias_permisos lp
							INNER JOIN personal p ON p.id_personal = lp.codigo_personal
							INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
							INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_permiso
							INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
							WHERE lp.codigo_personal = '$codigo_personal' and btrim(lp.codigo_contratacion || lp.codigo_turno) = '$codigo_contratacion' and 
							TO_CHAR(lp.fecha,'YYYY') = '$fecha_l_y_p' and lp.codigo_licencia_permiso = '$codigo_licencia_o_permiso[$j]'
							ORDER BY lp.fecha";
												
				$consulta_codigo_licencia_permiso = $dblink -> query($query_codigo_licencias);
				// revisar si hay registros.
				$num_registros = $consulta_codigo_licencia_permiso -> rowCount();
				//					
				if($num_registros !=0){
					while($row_print = $consulta_codigo_licencia_permiso -> fetch(PDO::FETCH_BOTH))
						{
						// Variables.
						$num++;
						$dia = $row_print['dia'];
						$hora = $row_print['hora'];
						$minutos = $row_print['minutos'];
						$nombre_licencia_permiso = mb_convert_encoding(trim($row_print['nombre_licencia_permiso']),'ISO-8859-1','UTF-8'); 
						$nombre_turno = mb_convert_encoding(trim($row_print['nombre_turno']),'ISO-8859-1','UTF-8');
						
						$fecha = cambiaf_a_normal($row_print['fecha']);
						$pdf->Cell($w[0],5.8,$num,1,0,'L',$fill);  // NUM
						$pdf->Cell($w[1],5.8,$nombre_licencia_permiso,1,0,'L',$fill);  // tipo de licencia o permiso.
						$pdf->Cell($w[2],5.8,$nombre_turno,1,0,'C',$fill);  // nombre turno
						$pdf->Cell($w[3],5.8,$fecha,1,0,'L',$fill);  // fecha
						$pdf->Cell($w[4],5.8,$row_print['dia'],1,0,'C',$fill);  // dia
						$pdf->Cell($w[5],5.8,$row_print['hora'],1,0,'C',$fill);  // hora
						$pdf->Cell($w[6],5.8,$row_print['minutos'],1,0,'C',$fill);  // minutos
						$pdf->Ln();
						$valor_y = $pdf->GetY();
							// calcular subtotales y totales.
							//////////////////////////////////////////////////
						// Calcular los dias minutos y segundos.
							if($j >= 0 and $j <= 7)
								{
									$total_minutos = ($dia*$calculo_horas*60) + ($hora*60) + ($minutos);
									//
									$tramite_dia[] = segundosToCadenaD($total_minutos,$calculo_horas);
									$tramite_hora[] = segundosToCadenaH($total_minutos, $calculo_horas);
									$tramite_minutos[] = segundosToCadenaM($total_minutos, $calculo_horas);
								}
							$numero++;
						// Salto de página.
								SaltoPagina($valor_y);
					}   // FIN DEL WHILE donde se encuentran todos los registros de las licencias.
						///////////////////////////////////////////////////////////////////////////////////////////////////////		
								if($j >= 0 and $j <= 7)
									{
										$sub_sin_dia = array_sum($tramite_dia);
										$sub_sin_hora = array_sum($tramite_hora);
										$sub_sin_minutos = array_sum($tramite_minutos);
													
										$minutos_x_dias = $minutos_licencia_o_permiso[$j];
										$minutos_subtotal = ($sub_sin_dia*$calculo_horas*60) + ($sub_sin_hora*60) + ($sub_sin_minutos);
										$minutos = $minutos_x_dias - $minutos_subtotal;
										$utilizado = mb_convert_encoding(segundosToCadena($minutos_subtotal, $calculo_horas,$formato=2),'ISO-8859-1','UTF-8');
										$saldo_disponible = mb_convert_encoding(segundosToCadena($minutos, $calculo_horas, $formato=2),'ISO-8859-1','UTF-8');
										$DiasLicencia = mb_convert_encoding(segundosToCadena($minutos_x_dias, $calculo_horas, $formato=2),'ISO-8859-1','UTF-8');

										$pdf->SetFont('Arial','B',8);														
										$pdf->Cell($w[0],5.8,'',1,0,'L',$fill2);  // numero
										$pdf->Cell($w[1],5.8,'Licencia: ' . $DiasLicencia,1,0,'L',$fill2);  // licencia
										$pdf->SetFont('Arial','B',7);					
										$pdf->Cell($w[2] + $w[3],5.8,'Disponible: ' . $saldo_disponible,1,0,'L',$fill2);  // turno y fecha
										$pdf->Cell($w2[1],5.8,'Utilizado: ' . $utilizado,1,1,'C',$fill2);  // dia
										$pdf->SetFont('Arial','',9);
									}
									// regresar el valor de num a 0.
									$valor_y = $pdf->GetY();
									$num = 0;
													// Eliminar los elmentos de la array que acumula los dia, minutos y horas.
						unset($tramite_dia, $tramite_hora, $tramite_minutos);
				}	// Condición para revisar si hay registros a través del count
	   }	// for para repeir los x tipo de licencia o permiso que existan,
// Salida del pdf.
    $pdf->Output();

function SaltoPagina($valor_y){
	global $pdf, $valor_y, $margen_inferior, $margen_superior, $header;
	$AltoActual = $valor_y + $margen_inferior + $margen_superior;
	if($AltoActual > $pdf->GetPageHeight()){
		$pdf->AddPage();
		$pdf->SetXY(10,24);
		$pdf->FancyTable($header);
		$valor_y = $pdf->GetY();
		// colores del fondo, texto, línea.
		$pdf->SetFillColor(230,227,227);
	}
}
?>