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
     $codigo_all_indicadores = array(); $nombre_grado = array(); $nombre_modalidad = array(); $nombre_ann_lectivo = array();
     //CONSULTA PARA LE MEMORIA ESTADISTICA
        $query_grados = "SELECT DISTINCT ROW(org.codigo_bachillerato), org.codigo_bachillerato as codigo_modalidad, org.codigo_grado, org.codigo_ann_lectivo,
                    gan.nombre as nombre_grado, ann.nombre as nombre_ann_lectivo,  bach.nombre as nombre_modalidad
			            FROM organizacion_grados_secciones org
                            INNER JOIN grado_ano gan ON gan.codigo = org.codigo_grado
                            INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
                            INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
                                WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' ORDER BY org.codigo_bachillerato, org.codigo_grado, org.codigo_ann_lectivo";
    //  ejecutar consulta para la memoria estadistica.
	    $result_grados = $db_link -> query($query_grados);

//  captura de datos para información individual de grado y sección.
     while($row = $result_grados -> fetch(PDO::FETCH_BOTH))
        {
	    	$codigo_grado = trim($row['codigo_grado']);
            $nombre_ann_lectivo = trim($row['nombre_ann_lectivo']);
            $codigo_modalidad = trim($row['codigo_modalidad']);
            $nombre_modalidad[] = trim($row['nombre_modalidad']);
            $nombre_grado[] = (ucfirst(strtolower(trim($row['nombre_grado']))));
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
    $this->Cell(270,4,utf8_decode('MINISTERIO DE EDUCACION, CIENCIA Y TECNOLOGIA'),0,1,'C');
    $this->Cell(270,4,utf8_decode('DIRECCION DEPARTAMENTAL DE SANTA ANA'),0,1,'C');
    $this->SetFont('Arial','B',10);
        $this->Cell(270,4,utf8_decode('MEMORIA ESTADISTICA ') . $nombre_ann_lectivo,0,1,'C');
    $this->SetFont('Arial','',8);
    $this->ln();
    $this->Cell(150,4,'CENTRO ESCOLAR: ' . utf8_decode($_SESSION['institucion']),0,0,'L');
    $this->Cell(100,4,'CODIGO: ' . utf8_decode($_SESSION['codigo']),0,1,'R');
    //
    $this->Cell(150,4,utf8_decode('SISTEMA INTREGRADO N.° 3'),0,0,'L');
    $this->Cell(100,4,'MUNICIPIO: SANTA ANA',0,1,'R');}

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
    global $print_ann_lectivo;
    //Restauración de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
		$this->SetFont('Arial','B',8);
    // PRIMERA LINEA
        $this->Cell(50,4,'GRADO','LTR',0,'C');
        $this->Cell(30,4,'MATRICULA MAXIMA','LTR',0,'C');
        $this->Cell(30,4,'EGRESADOS','LTR',0,'C');
        $this->Cell(30,4,'MATRICULA FINAL','LTR',0,'C');
        $this->Cell(30,4,'PROMOVIDOS','LTR',0,'C');
        $this->Cell(30,4,'RETENIDOS','LTR',0,'C');
        $this->Cell(30,4,'REPITENCIA','LTR',0,'C');
        $this->Cell(30,4,'SOBREEDAD','LTR',1,'C');
    // SEGUNDA LINEA
        $this->Cell(50,4,'','LBR',0,'C');
        for ($i=0; $i <=6 ; $i++) { 
            $this->Cell(10,4,'M',1,0,'C');
            $this->Cell(10,4,'F',1,0,'C');
            $this->Cell(10,4,'T',1,0,'C');
        }
    
}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Letter');
    $data = array();
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 15, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',9);
    $pdf->AddPage();
    // Aqui mandamos texto a imprimir o al documento.
    $pdf->SetY(40);
    $pdf->SetX(5);
	// llamar al encabezado.
       $pdf->encabezado();
// Evaluar si existen registros.
		for($jh=0;$jh<=count($codigo_indicadores)-1;$jh++)
		{

			// Ancho de las diferentes columnas	
				$w=array(10,60,20,15,10,15,15,15); //determina el ancho de las columnas
        }
/*


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
							if($i > 25){
							$pdf->SetX(15);
							$pdf->Cell(array_sum($w)+5+(6*10),0,'','B');
							$pdf->AddPage();
							// Aqui mandamos texto a imprimir o al documento.
							$pdf->SetY(50);
							$pdf->SetX(15);
							$pdf->encabezado();
							}
				} // if condiciones para imprimir dependiendo del codigo turno.
			}	// for codigo_all_indicadores
/*
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// CONSOLIDADOS GENERALES
//////////////////////////////////////////////////////////////////////////////////////////////////////////
  $pdf->SetX(15);
      $pdf->cell(205,$alto_fila,' ','LR',0,'C',$fill);
      $pdf->Cell($w[4],$alto_fila,$total_general_masculino,'LR',0,'C',$fill);
      $pdf->Cell($w[4],$alto_fila,$total_general_femenino,'LR',0,'C',$fill);
      $pdf->Cell($w[4],$alto_fila,$total_general,'LR',1,'C',$fill);
    // cerrar línea si sólo hay una página.
    if($i == 26){
	$pdf->SetX(15);
	$pdf->Cell(array_sum($w)+5+(6*10),0,'','B');
    }
//*******************************************************/
//	CREAR PROCESO PARA CONSOLIDAR GRADOS
//
//******************************************************//
    /// armar subtotales. crear una nueva página.
  /*      $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
    $pdf->SetY(50);
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
					$pdf->SetY(50);
					$pdf->SetX(15);
					$pdf->encabezado();
			    }
		}
    } // condición de vacío en la tabla.    
else{
    // si no existen registros.
    $pdf->Cell(150,7,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
}   */
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
     $print_nombre = 'MEMORIA ESTADISTICA ' . $nombre_ann_lectivo;
     $pdf->Output($print_nombre,$modo);
?>