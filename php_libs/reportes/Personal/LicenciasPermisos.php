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
	$fecha_desde = $_REQUEST['fecha_desde'];
	$fecha_hasta = $_REQUEST['fecha_hasta'];
	$fecha_inicio = $_REQUEST['fecha_inicio'];
	$nombre_ann_lectivo = substr($_REQUEST['fecha_desde'],0,4);
	$codigo_contratacion = $_REQUEST['codigo_contratacion'];
	$codigo_turno = $_REQUEST['codigo_turno'];
	$codigo_contratacion_turno = $codigo_contratacion . $codigo_turno;
	$db_link = $dblink;
	// Calcular el Disponible segùn Tipo de Contratación.
	$calculo_horas = 5;
	if($codigo_contratacion == "05"){ // PAGADOS POR EL CDE.
		$calculo_horas = 8;
	}
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
	$query_nombres_personal = "SELECT ps.codigo_tipo_contratacion, ps.codigo_turno, ps.codigo_personal,
				p.nombres, p.apellidos, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_c, p.codigo_estatus
				FROM personal_salario ps
				INNER JOIN personal p ON p.id_personal = ps.codigo_personal
				WHERE p.codigo_estatus = '01' and btrim(ps.codigo_tipo_contratacion || ps.codigo_turno) = '$codigo_contratacion_turno'
				ORDER BY nombre_c";
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	$consulta_personal = $dblink -> query($query_personal);
	$consulta_licencia_permiso = $dblink -> query($query_licencia_permiso);
	$consulta_encabezado = $dblink -> query($query_personal);
	$consulta_nombres = $dblink -> query($query_nombres_personal);
// Obtener el encabezado.
	while($listadoPersonalE = $consulta_encabezado -> fetch(PDO::FETCH_BOTH))
		{
			$nombre_turno = trim($listadoPersonalE['nombre_turno']);
			$nombre_contratacion = mb_convert_encoding((trim($listadoPersonalE['nombre_contratacion'])),"ISO-8859-1","UTF-8");
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
				//$minutos_licencia_o_permiso[] = $listadoPersonalLyP['minutos'];
				$minutos_licencia_o_permiso[] = $listadoPersonalLyP['saldo'] * $calculo_horas * 60;
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
							if($j >= 0 && $j <= 7)
								{
									$total_minutos = ($dia*$calculo_horas*60) + ($hora*60) + ($minutos);
									
									if($dia > 0 || $hora > 0 || $minutos > 0){
										$pase++;
									}
									$tramite_dia[$i][$j] = segundosToCadenaD($total_minutos,$calculo_horas);
									$tramite_hora[$i][$j] = segundosToCadenaH($total_minutos,$calculo_horas);
									$tramite_minutos[$i][$j] = segundosToCadenaM($total_minutos,$calculo_horas);
								}
				}	// while que examina el bucle que cuenta los dias, horas y minutos.
			} // Verificar la consulta
			// PROCESO PARA OBTENER EL CONSUMO DEL MES EN VIGENCIA. DIA, HORAS, MINTUOS.
			$query_saldo_ = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos FROM personal_licencias_permisos 
							WHERE codigo_personal = '".$codigo_docente_a[$i]."' and fecha >= '".$fecha_inicio."' and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_permiso = '".$codigo_licencia_o_permiso[$j]."' and codigo_turno = '".$codigo_turno."'";
			$consulta_saldo_ = $dblink -> query($query_saldo_);
			//
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
							if($j >= 0 && $j <= 7)
								{
									$total_minutos = ($dia*$calculo_horas*60) + ($hora*60) + ($minutos);
									
									$tramite_dia_saldo[$i][$j] = segundosToCadenaD($total_minutos,$calculo_horas);
									$tramite_hora_saldo[$i][$j] = segundosToCadenaH($total_minutos,$calculo_horas);
									$tramite_minutos_saldo[$i][$j] = segundosToCadenaM($total_minutos,$calculo_horas);
								}
					}	// while que examina el bucle que cuenta los dias, horas y minutos.
			} // Verificar la consulta
	}	// for que recorre los tipos de licencias.
      //  PROCESO QUE DECIDE QUE PERSONA SE IMPRIME.
            $imprimir[$i] = true;
    }	// for que recorre el personal docente.
class PDF extends FPDF
{
//Cabecera de página
function Header(){
    //Logo
    global $nombre_mes, $nombre_contratacion, $nombre_ann_lectivo, $nombre_turno;
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_uno']; $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',12);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(250,4,'CONTROL DE LICENCIAS Y PERMISOS DEL PERSONAL DOCENTE',0,1,'C');
    $this->SetFont('Arial','B',10);
    $this->SetX(30);
    $this->Cell(130,4,'CENTRO EDUCATIVO: '.mb_convert_encoding($_SESSION['institucion'],"ISO-8859-1","UTF-8"),0,0,'L');
    $this->Cell(40,4,mb_convert_encoding('CÓDIGO: '.$_SESSION['codigo'],"ISO-8859-1","UTF-8"),0,0,'L');
    $this->Cell(40,4,'TURNO: '.$nombre_turno,0,0,'L'); 
    $this->Cell(40,4,'MES: '.$nombre_mes,0,1,'L');
    $this->SetX(30);
    $this->Cell(40,4,mb_convert_encoding('AÑO LECTIVO: '.$nombre_ann_lectivo,"ISO-8859-1","UTF-8"),0,1,'L');
    $this->SetXY(0,0);
    $this->SetFont('Arial','B',6);
    $this->Cell(40,4,mb_convert_encoding($nombre_contratacion,"ISO-8859-1","UTF-8"),0,1,'L');
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
    //(numero, nie, nombre, edad, genero, col1,col2,col3,col4,col5,col6)
    $w=array(5,50,5,5,5,15,5,5,5,15,5,5,5,15,5,5,5,15,5,5,15,15,15,15,15); //determina el ancho de las columnas
    $w2=array(5,50,60,15,145,10,30,25); //determina el ancho de las columnas

    // primera fila
    $this->Cell($w2[0],5,'','LTR',0,'C',1);
    $this->Cell($w2[1],5,'','LTR',0,'C',1);
    $this->Cell($w2[4],5,'Licencia con goce de sueldo','LTBR',0,'C',1);
    $this->Cell($w2[3],5,'Licencia','LTR',0,'C',1);
    $this->Cell($w2[3],5,'','LTR',0,'C',1);
    $this->Cell($w2[3],5,'','LTR',0,'C',1);
    $this->Cell($w2[3],5,'Llegadas','LTR',0,'C',1);
    $this->LN();
    
    // segunda fila 1 bloque
    $this->Cell($w2[0],5,'','LR',0,'C',1);
    $this->Cell($w2[1],5,'','LR',0,'C',1);
    $this->Cell($w2[2],5,'Enfermedad','LBR',0,'C',1);
    // segunda fila 2 bloque
    $this->Cell($w2[6],5,'Enf.Pariente o Duelo','LBR',0,'C',1);
    // segunda fila 3 bloque
    $this->Cell($w2[6],5,'Por Permiso Personal','LBR',0,'C',1);
    // segunda fila 4 bloque
    $this->Cell($w2[7],5,'Permiso Por Mater.','LBR',0,'C',1);
    // segunda fila 5 bloque
    $this->Cell($w2[3],5,'sin','LR',0,'C',1);
    $this->Cell($w2[3],5,'Saldo','LR',0,'C',1);
    $this->Cell($w2[3],5,'Inasistencia','LR',0,'C',1);
    $this->Cell($w2[3],5,'Tardes o','LR',0,'C',1);
    $this->LN();
    
    // tercera fila 1 bloque
    $this->Cell($w2[0],5,'','LR',0,'C',1);
    $this->Cell($w2[1],5,'','LR',0,'C',1);
    $this->Cell($w2[3],5,mb_convert_encoding('Sin Trámite',"ISO-8859-1","UTF-8"),'LBR',0,'C',1);
    $this->Cell($w2[3],5,'Saldo','LR',0,'C',1);
    // tercera fila 2 bloque
    $this->Cell($w2[3],5,mb_convert_encoding('Con Trámite',"ISO-8859-1","UTF-8"),'LBR',0,'C',1);
    $this->Cell($w2[3],5,'Saldo','LR',0,'C',1);
    // tercera fila 3 bloque
    $this->Cell($w2[3],5,'','LBR',0,'C',1);
    $this->Cell($w2[3],5,'Saldo','LR',0,'C',1);
    // tercera fila 4 bloque
    $this->Cell($w2[3],5,'','LBR',0,'C',1);
    $this->Cell($w2[3],5,'Saldo','LR',0,'C',1);
    // tercera fila 5 bloque
    $this->Cell($w2[5],5,'','LBR',0,'C',1);
    $this->Cell($w2[3],5,'Saldo','LR',0,'C',1);    
    // segunda fila 5 bloque
    $this->Cell($w2[3],5,'goce de','LR',0,'C',1);
    $this->Cell($w2[3],5,'60','LR',0,'C',1);
    $this->Cell($w2[3],5,'Injusti','LR',0,'C',1);
    $this->Cell($w2[3],5,'salidas a.','LR',0,'C',1);
    $this->LN();
       
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,mb_convert_encoding($header[$i],"ISO-8859-1","UTF-8"),'LBR',0,'C',1);
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
    $header=array('Nº','Nombre','D','H','M','(15 días)','D','H','M','(90 días)','D','H','M','(20 días)','D','H','M','(5 días)','D','H','(120 días)','sueldoD','días','cada','de la h.');
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
    //(numero, nie, nombre, edad, genero, col1,col2,col3,col4,col5,col6)
    $w=array(5,50,5,5,5,15,5,5,5,15,5,5,5,15,5,5,5,15,5,5,15,15,15,15,15); //determina el ancho de las columnas
    $w2=array(5,50,60,15,145,10,30,25); //determina el ancho de las columnas
	//
    $fill=true; $num=1;
	$count = count($codigo_docente_a);
       for($i=0;$i<$count;$i++)
       {
         if($imprimir[$i] == true)
					{
					$pdf->SetFont('Arial','',8.5); // I : Italica; U: Normal;
					$pdf->Cell($w[0],5.8,$num,1,0,'C',$fill);        // núermo correlativo
					$pdf->Cell($w[1],5.8,(cambiar_de_del($nombre_docente_a[$i])),1,0,'L',$fill);  // NIE
				// contar para para la licencias o permisos.
				$count_lic = count($codigo_licencia_o_permiso);
				for($j=0;$j<$count_lic;$j++)
				{									
					// Imprimiendo en pantalla las licencias o permisos según su clasificación y presentación en pantall.a
					if($j >=0 && $j<=3 || $j == 5)
					{
						if($tramite_dia[$i][$j] == 0){$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);}else{$pdf->Cell($w[2],5.8,$tramite_dia[$i][$j],1,0,'L',$fill);}  // dia
						if($tramite_hora[$i][$j] == 0){$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);}else{$pdf->Cell($w[2],5.8,$tramite_hora[$i][$j],1,0,'L',$fill);}  // hora
						if($tramite_minutos[$i][$j] == 0){$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);}else{$pdf->Cell($w[2],5.8,$tramite_minutos[$i][$j],1,0,'L',$fill);} // minutos
					}
					//Por Maternidad.
					if($j == 4)
					{
						if($tramite_dia[$i][$j] == 0){$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);}else{$pdf->Cell($w[2],5.8,$tramite_dia[$i][$j],1,0,'L',$fill);}  // dia
						if($tramite_hora[$i][$j] == 0){$pdf->Cell($w[2],5.8,'',1,0,'L',$fill);}else{$pdf->Cell($w[2],5.8,$tramite_hora[$i][$j],1,0,'L',$fill);}  // hora
					}
					// Calculo del saldo
					$minutos_x_dias = $minutos_licencia_o_permiso[$j];
					$minutos_subtotal = ($tramite_dia_saldo[$i][$j]*$calculo_horas*60) + ($tramite_hora_saldo[$i][$j]*60) + ($tramite_minutos_saldo[$i][$j]);
					$minutos = $minutos_x_dias - $minutos_subtotal;
					$saldo_x = segundosToCadena($minutos,$calculo_horas);
					
					if($j >= 0 && $j <= 4 || $j == 5)
					{
						$pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
						$pdf->Cell($w[5],5.8,$saldo_x,1,0,'L',$fill);  // saldo
						$pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
					}
					
					if($j >= 6 && $j <= 7)
					{
						$pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
						$pdf->Cell($w[5],5.8,'',1,0,'L',$fill);  // saldo
						$pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
					}
				}
            $pdf->Ln();
            //$fill=!$fill;
	    $num++;
	    // Salto de página.
        	if($num == 23 || $num == 45){
				$pdf->Cell(array_sum($w),0,'','B');
				$pdf->AddPage();
				$pdf->SetY(25);
				$pdf->FancyTable($header);}
	 } //cierre del if.
        } //cierre del for
// Cerrando Línea Final.
   $pdf->Cell(array_sum($w),0,'','T');
   $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// Salida del pdf.
    $pdf->Output();
?>