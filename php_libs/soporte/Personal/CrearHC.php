<?php
session_name('demoUI');
// Inicializamos variables de mensajes y JSON
$respuestaOK = true;
$mensajeError = "Si Save";
$contenidoOK = "Si Save";
$mensajeErrorTabla = "";
$tiempo_inicio = microtime(true); //true es para que sea calculado en segundos
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/funciones_2.php");
// COLOCAR UN LIMITE A LA MEMORIA PARA LA CREACIÓN DE LA HOJA DE CÁLCULO.
    set_time_limit(0);
    ini_set("memory_limit","1024M");
// Iniciamos variables.
    $hoja_aspectos = 0;
    $n_hoja = 4;	// variable para el activesheet.
	$fila_docente = 0;
    $codigo_docente = trim($_REQUEST["codigo_docente"]);
    $codigo_annlectivo = trim($_REQUEST["codigo_annlectivo"]);
	$trimestre_1 = trim($_REQUEST["t1"]);
	$trimestre_2 = trim($_REQUEST["t2"]);
	$trimestre_3 = trim($_REQUEST["t3"]);
	$trimestre_4 = trim($_REQUEST["t4"]);
// consulta sobre el codigo del docente y el a?o lectivo.
  $query = "SELECT eg.encargado, eg.codigo_ann_lectivo, eg.codigo_grado, eg.codigo_seccion, eg.codigo_bachillerato, eg.codigo_docente, eg.imparte_asignatura
						FROM encargado_grado eg 
							WHERE eg.codigo_docente = '".$codigo_docente."' and eg.codigo_ann_lectivo = '".$codigo_annlectivo."'";	
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	    $consulta_docente = $dblink -> query($query);
// recorremos la consulta. para asignarle a las variables.		
		if($consulta_docente -> rowCount() != 0){
		// crear array para el primer ciclo
			$codigo_bachillerato_primer_ciclo = array(); $codigo_grado_primer_ciclo = array(); $codigo_seccion_primer_ciclo = array();
			 while($listadoDocente = $consulta_docente -> fetch(PDO::FETCH_BOTH))
				{
					$encargado = trim($listadoDocente['encargado']);
					$imparteasignatura = trim($listadoDocente['imparte_asignatura']);
					$codigo_bachillerato_primer_ciclo[] = trim($listadoDocente['codigo_bachillerato']);
					$codigo_grado_primer_ciclo[] = trim($listadoDocente['codigo_grado']);
					$codigo_seccion_primer_ciclo[] = trim($listadoDocente['codigo_seccion']);
				}			
		}	
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007 y verificar si la carpeta o directorio existe.
		$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		$origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";
		$nombre_de_hoja_de_calculo = "Control de Actividades Ver.2025.xlsx";
		
		// Grabar la Nota según sean las condiciones.
						if($trimestre_1 == "yes"){
							$nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-1.xlsx";
						}
						
						if($trimestre_2 == "yes" && $trimestre_1 == "yes"){
							$nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-2.xlsx";
						}
						
						if($trimestre_3 == "yes"){
							$nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-3.xlsx";
						}

						if($trimestre_4 == "yes"){
							$nombre_de_hoja_de_calculo = "Control de Actividades Ver.2019-4.xlsx";
						}
		// Seleccionar el archivo con el se trabajará
		$objPHPExcel = $objReader->load($origen.$nombre_de_hoja_de_calculo);
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina. de primer ciclo o asignatura independiente.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   // GENERAR NUEVAMENTE FILA_DOCENTE PARA LA CARGA DOCENTE.
   	$query = "SELECT cd.codigo_docente, cd.codigo_asignatura, cd.codigo_seccion, cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_ann_lectivo
      	FROM carga_docente cd
      	WHERE cd.codigo_docente = '".$codigo_docente."' and cd.codigo_ann_lectivo = '".$codigo_annlectivo."'"
         ." ORDER by cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, cd.codigo_asignatura";
	//cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, asig.codigo
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	    $consulta_docente = $dblink -> query($query);
		$fila_docente = $consulta_docente -> rowCount();
	// crear array para el primer ciclo
	$codigo_bachillerato_partes = array(); $codigo_grado_partes = array(); $codigo_seccion_partes = array(); $codigo_asignatura_partes = array();
   // Verificar si posee carga academica.
   if($consulta_docente -> rowCount() != 0){
         while($listadoDocente = $consulta_docente -> fetch(PDO::FETCH_BOTH))
            {
				$codigo_bachillerato_partes[] = trim($listadoDocente['codigo_bachillerato']);
				$codigo_grado_partes[] = trim($listadoDocente['codigo_grado']);
				$codigo_seccion_partes[] = trim($listadoDocente['codigo_seccion']);
				$codigo_asignatura_partes[] = trim($listadoDocente['codigo_asignatura']);
            }
   }else{
      // Terminar proceso i enviar información corresondiente.
		$respuestaOK = false;
		$mensajeError = "Error de Creación.";
		$contenidoOK = "<strong>No hay Carga Académica Asignada</strong>";
		$mensajeErrorTabla .= "<tr class=text-warning><td>En Carga Docente $consulta_docente</td></tr>";
      // Armamos array para convertir a JSON
         $salidaJson = array("respuesta" => $respuestaOK,
            "mensaje" => $mensajeError,
            "contenido" => $contenidoOK);	
      // enviar el Json
      echo json_encode($salidaJson);
         return;
   }
   // RECORRER EL NUMERO DE ELEMENTOS ENCONTRADOS. 
   for($ii=0;$ii<$fila_docente;$ii++)
   {
    // substraer codigos de las asignaturas
	 $query = "SELECT cd.codigo_docente, cd.codigo_ann_lectivo, cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, cd.codigo_asignatura,
	    asig.nombre as nombre_asignatura, btrim(pd.nombres || CAST(' ' AS VARCHAR) || pd.apellidos) as nombre_docente, grado.nombre as nombre_grado, sec.nombre as nombre_seccion,
	    ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_bachillerato, asig.codigo_cc, asig.codigo_area,
       cat_cc.descripcion as concepto_calificacion
	    from carga_docente cd
	    INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
	    INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
	    INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
	    INNER JOIN personal pd ON pd.id_personal = (cd.codigo_docente)::int
	    INNER JOIN grado_ano grado ON grado.codigo = cd.codigo_grado
	    INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
		INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
	    WHERE codigo_docente = '".$codigo_docente."' and codigo_ann_lectivo = '".$codigo_annlectivo.
	    "' and codigo_bachillerato = '".$codigo_bachillerato_partes[$ii]."' and codigo_grado = '".$codigo_grado_partes[$ii].
	    "' and codigo_seccion = '".$codigo_seccion_partes[$ii]."' and cd.codigo_asignatura = '".$codigo_asignatura_partes[$ii].
	    "' ORDER BY cd.codigo_grado, cd.codigo_seccion, cd.codigo_asignatura";
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	    $result_consulta = $dblink -> query($query);
		$fila_asignatura = $result_consulta -> rowCount();
		$result = $dblink -> query($query);
// colocar informacion en la hoja seleccionada.
	$codigo_asignatura_matriz = array(); $nombre_asignatura_matriz = array();
		while($rows = $result_consulta -> fetch(PDO::FETCH_BOTH))
            {
				$codigo_asignatura_matriz[] = trim($rows['codigo_asignatura']);
				$nuevo_codigo_asignatura = trim($rows['codigo_asignatura']);
				$codigo_asignatura = trim($rows['codigo_asignatura']);
				$nombre_asignatura = replace_3((trim($rows['nombre_asignatura'])));
				$bach = trim($rows['codigo_bachillerato']);
				$nombre_bachillerato = trim($rows['nombre_bachillerato']);
				$nombre_bachillerato_en_excel = (trim($rows['nombre_bachillerato']));
				$codigo_bach = trim($rows['codigo_bachillerato']);
				$ann = trim($rows['codigo_ann_lectivo']);
				$codigo_ann = trim($rows['codigo_ann_lectivo']);
				$nombre_ann_lectivo = trim($rows['nombre_ann_lectivo']);
				$grado = trim($rows['codigo_grado']);
				$codigo_grado = trim($rows['codigo_grado']);
				$sec = trim($rows['codigo_seccion']);
				$codigo_seccion = trim($rows['codigo_seccion']);
				$nombre_docente_en_excel = (trim($rows['nombre_docente']));
				$nombre_docente = trim($rows['nombre_docente']);
				$nombre_grado = (TRIM($rows['nombre_grado']));		     	     
				$nombre_seccion = trim($rows['nombre_seccion']);
				$concepto_calificacion = trim($rows['concepto_calificacion']);
            $codigo_concepto_calificacion = trim($rows['codigo_cc']);
            $codigo_area = trim($rows['codigo_area']);
			}
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////	    
	    // En la siguientes lineas se condicionara si se desea para primer ciclo. para generar solamente dos hojas mas.
	    //  Get the current sheet with all its newly-set style properties
	    // VERIFICAR CUANDO SEAN ASIGNATURAS PARA LOS ASPECTOS DE LA CONDUCTA.
       // O CONVIVENCIA CIUDADANA
                     if($codigo_area == '07')
                         {
                           $hoja_aspectos++;
                           if($hoja_aspectos == 1){$pase = 1; $numero_hoja_clonar = 1;}
                           if($hoja_aspectos == 2){$pase = 1; $numero_hoja_clonar = 1;}
                           if($hoja_aspectos == 3){$pase = 1; $numero_hoja_clonar = 1;}
                           if($hoja_aspectos == 4){$pase = 1; $numero_hoja_clonar = 1;}
                           if($hoja_aspectos == 5){$pase = 1; $numero_hoja_clonar = 1;}
                         }else{
                        $hoja_aspectos = 0; $pase = 0; $numero_hoja_clonar = 0;
                      }            
	if($pase == 0 and $numero_hoja_clonar == 0)
			{
				// seleccionar que hoja se va duplicar.
            // turno Vespertino o Matutino.
            if($codigo_bach == "10" || $codigo_bach == "11" || $codigo_bach == "12"){
               // Nocturna
                  $numero_hoja_clonar = 2;
            }
				$objWorkSheetBase = $objPHPExcel->getSheet($numero_hoja_clonar);
				//  Create a clone of the current sheet, with all its style properties
				$objWorkSheet1 = clone $objWorkSheetBase;
				//  Set the newly-cloned sheet title
				$objWorkSheet1->setTitle('Cloned Sheet');
				//  Attach the newly-cloned sheet to the $objPHPExcel workbook
				$objPHPExcel->addSheet($objWorkSheet1);
				// Indicamos que se pare en la hoja uno del libro
				$objPHPExcel->setActiveSheetIndex($n_hoja);
				$objPHPExcel->getActiveSheet($n_hoja)->setTitle(substr($codigo_grado,1,1).'.º-'.$nombre_seccion.' '.substr($nombre_asignatura,0,10));
				$n_hoja++;
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//Escribimos en la hoja en la celda e3. los datos del bachillerato, grado, secci?n, a?o lectivo, etc.
			// Y comparamos si es el primer ciclo u otro.
			$objPHPExcel->getActiveSheet()->SetCellValue('E2', $nombre_docente_en_excel);
			$objPHPExcel->getActiveSheet()->SetCellValue('F2', "'".$codigo_docente."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('D3', "'".$codigo_bach."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E3', $nombre_bachillerato_en_excel);
			$objPHPExcel->getActiveSheet()->SetCellValue('D4', "'".$codigo_ann."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E4', $nombre_ann_lectivo);
			$objPHPExcel->getActiveSheet()->SetCellValue('D5', "'".$codigo_grado."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E5', $nombre_grado);
			$objPHPExcel->getActiveSheet()->SetCellValue('D6', "'".$codigo_seccion."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E6', $nombre_seccion);
			$objPHPExcel->getActiveSheet()->SetCellValue('D7', "'".$nuevo_codigo_asignatura."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E7', $nombre_asignatura);
	    //colocar en G4, BASICA O MEDIA
	      if($codigo_bach >= "01" and $codigo_bach <= "05")
	      {
            // TITULOS PARA LAS HOJAS DEPENDIENDO DEL NIVEL EDUCATIVO.
               $objPHPExcel->getActiveSheet()->SetCellValue('G4', 'BASICA');
            // TRIMESTRES O PERIODOS.
               $objPHPExcel->getActiveSheet()->SetCellValue('G7', 'T R I M E S T R E');
               $objPHPExcel->getActiveSheet()->SetCellValue('Z7', 'T R I M E S T R E');
               $objPHPExcel->getActiveSheet()->SetCellValue('AS7', 'T R I M E S T R E');
               $objPHPExcel->getActiveSheet()->SetCellValue('BL7', 'SOLO PARA EDUCACIÓN MEDIA');
               
               if($codigo_bach == "05"){
               		$objPHPExcel->getActiveSheet()->SetCellValue('G4', 'BASICA - TERCER CICLO');}
                     // TRIMESTRES O PERIODOS.
                        $objPHPExcel->getActiveSheet()->SetCellValue('G7', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('Z7', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('AS7', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('BL7', 'SOLO PARA EDUCACIÓN MEDIA');
               }else{
                  $objPHPExcel->getActiveSheet()->SetCellValue('G4', 'MEDIA');
                  // TRIMESTRES O PERIODOS.
                     $objPHPExcel->getActiveSheet()->SetCellValue('G7', 'P E R I O D O');
                     $objPHPExcel->getActiveSheet()->SetCellValue('Z7', 'P E R I O D O');
                     $objPHPExcel->getActiveSheet()->SetCellValue('AS7', 'P E R I O D O');
                     $objPHPExcel->getActiveSheet()->SetCellValue('BL7', 'P E R I O D O');
               }
	      
	    $codigo_all = $bach.$grado.$sec.$ann;
			$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
					a.genero, a.id_alumno as cod_alumno, am.id_alumno_matricula as codigo_matricula, am.codigo_bach_o_ciclo,
					bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion,
					n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.codigo_asignatura, ae.codigo_alumno, ae.encargado
					FROM alumno a 
					INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
					INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
					INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
					INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
					INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
					INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
					INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
						WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."' and n.codigo_asignatura = '".$nuevo_codigo_asignatura."' and n.codigo_alumno = a.id_alumno ORDER BY apellido_alumno ASC";
		// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
		    $result = $dblink -> query($query);
			$result1 = $dblink -> query($query);
	    //  imprimir datos del bachillerato.
		while($rows = $result1 -> fetch(PDO::FETCH_BOTH))
		    {
				$nombre_bachillerato = trim($rows['nombre_bachillerato']);
				$nombre_grado = TRIM($rows['nombre_grado']);
				 
				//$nombre_grado = iconv("ISO-8859-1", "UTF-8",trim($rows['nombre_grado']));
				$nombre_seccion = trim($rows['nombre_seccion']);
				$nombre_ann_lectivo = trim($rows['nombre_ann_lectivo']);
				//$nombre_asignatura = iconv("ISO-8859-1", "UTF-8",trim($rows['nombre_asignatura']));
				
				$codigo_bach = "'".trim($rows['codigo_bach_o_ciclo'])."'";
				$codigo_ann = "'".trim($rows['codigo_ann_lectivo'])."'";
				$codigo_seccion = "'".trim($rows['codigo_seccion'])."'";
				$codigo_grado = "'".trim($rows['codigo_grado'])."'";
				
				$codigo_encargado = trim($rows['encargado']);
				
				$filename = $nombre_grado." ".$nombre_seccion;
			break;
		    }	
	    // Correlativo, numero de linea.
		$num = 0; $fila_excel = 12;
			while($row = $result -> fetch(PDO::FETCH_BOTH))
				{
					// acumular correlativo y fila.
						$num++; $fila_excel++;
					// validar el genero.
					if(TRIM($row['genero']) == 'm'){$sexo = 'M';}else{$sexo = 'F';}
					////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
					//  IMPRIMIR EL CONTENIDO DE  INFORMACION EN EXCEL.
						$objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, $num);
						$objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, TRIM($row['codigo_nie']));
						$objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel, TRIM($row['codigo_alumno']));
						$objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel, TRIM($row['codigo_matricula']));
						$objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel, (TRIM($row['apellido_alumno'])));
						$objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel, $sexo);
					// Grabar la Nota según sean las condiciones.
						if($trimestre_1 == "yes"){
							$objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, (TRIM($row['nota_p_p_1'])));
						}
						if($trimestre_2 == "yes"){
							$objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, (TRIM($row['nota_p_p_1'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("AR".$fila_excel, (TRIM($row['nota_p_p_2'])));
						}
						if($trimestre_3 == "yes"){
							$objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, (TRIM($row['nota_p_p_1'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("AR".$fila_excel, (TRIM($row['nota_p_p_2'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("BK".$fila_excel, (TRIM($row['nota_p_p_3'])));
						}
						if($trimestre_4 == "yes"){
							$objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, (TRIM($row['nota_p_p_1'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("AR".$fila_excel, (TRIM($row['nota_p_p_2'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("BK".$fila_excel, (TRIM($row['nota_p_p_3'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("CD".$fila_excel, (TRIM($row['nota_p_p_4'])));
						}
					////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
				}    //  FIN DEL WHILE.
	    // Proteger hoja.
        
       
			$objPHPExcel->getActiveSheet()->getProtection()->setPassword('1');
            $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
	}
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if($pase == 1 and $numero_hoja_clonar == 1)
	{
		if($hoja_aspectos == 1){
		    // seleccionar que hoja se va duplicar.
            if($codigo_bach == "10" || $codigo_bach == "11" || $codigo_bach == "12"){
               // Nocturna
                  $numero_hoja_clonar = 3;
            }
			$objWorkSheetBase = $objPHPExcel->getSheet($numero_hoja_clonar);
		    //  Create a clone of the current sheet, with all its style properties
			$objWorkSheet1 = clone $objWorkSheetBase;
		    //  Set the newly-cloned sheet title
			$objWorkSheet1->setTitle('Cloned Sheet');
		    //  Attach the newly-cloned sheet to the $objPHPExcel workbook
			$objPHPExcel->addSheet($objWorkSheet1);
		    // Indicamos que se pare en la hoja uno del libro
			$objPHPExcel->setActiveSheetIndex($n_hoja);
			$objPHPExcel->getActiveSheet($n_hoja)->setTitle(substr($codigo_grado,1,1).'-'.$nombre_seccion.' Competencias Ciudadanas');
			$n_hoja++;
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //Escribimos en la hoja en la celda e3. los datos del bachillerato, grado, secci?n, a?o lectivo, etc.
	    // Y comparamos si es el primer ciclo u otro.		
			$objPHPExcel->getActiveSheet()->SetCellValue('E2', $nombre_docente_en_excel);
			$objPHPExcel->getActiveSheet()->SetCellValue('F2', "'".$codigo_docente."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('D3', "'".$codigo_bach."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E3', $nombre_bachillerato_en_excel);
			$objPHPExcel->getActiveSheet()->SetCellValue('D4', "'".$codigo_ann."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E4', $nombre_ann_lectivo);
			$objPHPExcel->getActiveSheet()->SetCellValue('D5', "'".$codigo_grado."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E5', $nombre_grado);
			$objPHPExcel->getActiveSheet()->SetCellValue('D6', "'".$codigo_seccion."'");
			$objPHPExcel->getActiveSheet()->SetCellValue('E6', $nombre_seccion);	
		//$objPHPExcel->getActiveSheet()->SetCellValue('D7', "'".$nuevo_codigo_asignatura."'");
		//$objPHPExcel->getActiveSheet()->SetCellValue('E7', $nombre_asignatura);
		
	    //colocar en G4, BASICA O MEDIA
	      if($codigo_bach >= "01" and $codigo_bach <= "05")
	      {
            $objPHPExcel->getActiveSheet()->SetCellValue('G4', 'BASICA');
            $objPHPExcel->getActiveSheet()->SetCellValue('G11', "'".$nuevo_codigo_asignatura."'");
            $objPHPExcel->getActiveSheet()->SetCellValue('L11', "'".$nuevo_codigo_asignatura."'");
            $objPHPExcel->getActiveSheet()->SetCellValue('Q11', "'".$nuevo_codigo_asignatura."'");
            $objPHPExcel->getActiveSheet()->SetCellValue('V11', "'".$nuevo_codigo_asignatura."'");
            // Nombre Asignatura.
            $objPHPExcel->getActiveSheet()->SetCellValue('G12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('L12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('Q12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('V12', $nombre_asignatura);
            
            
                     // TRIMESTRES O PERIODOS.
                        $objPHPExcel->getActiveSheet()->SetCellValue('G8', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('L8', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('Q8', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('V8', 'SOLO PARA EDUCACIÓN MEDIA');

		
			if($codigo_bach == "05"){
				$objPHPExcel->getActiveSheet()->SetCellValue('G4', 'BASICA - TERCER CICLO');}
                     // TRIMESTRES O PERIODOS.
                        $objPHPExcel->getActiveSheet()->SetCellValue('G8', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('L8', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('Q8', 'T R I M E S T R E');
                        $objPHPExcel->getActiveSheet()->SetCellValue('V8', 'SOLO PARA EDUCACIÓN MEDIA');
			}else{
            	$objPHPExcel->getActiveSheet()->SetCellValue('G4', 'MEDIA');
               $objPHPExcel->getActiveSheet()->SetCellValue('G11', "'".$nuevo_codigo_asignatura."'");
               $objPHPExcel->getActiveSheet()->SetCellValue('L11', "'".$nuevo_codigo_asignatura."'");
               $objPHPExcel->getActiveSheet()->SetCellValue('Q11', "'".$nuevo_codigo_asignatura."'");
               $objPHPExcel->getActiveSheet()->SetCellValue('V11', "'".$nuevo_codigo_asignatura."'");
               // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AA11', "'".$nuevo_codigo_asignatura."'");   
               }
               // Nombre Asignatura.
               $objPHPExcel->getActiveSheet()->SetCellValue('G12', $nombre_asignatura);
               $objPHPExcel->getActiveSheet()->SetCellValue('L12', $nombre_asignatura);
               $objPHPExcel->getActiveSheet()->SetCellValue('Q12', $nombre_asignatura);
               $objPHPExcel->getActiveSheet()->SetCellValue('V12', $nombre_asignatura);
               // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AA12', $nombre_asignatura);
               }
            // TRIMESTRES O PERIODOS.
               $objPHPExcel->getActiveSheet()->SetCellValue('G8', 'P E R I O D O');
               $objPHPExcel->getActiveSheet()->SetCellValue('L8', 'P E R I O D O');
               $objPHPExcel->getActiveSheet()->SetCellValue('Q8', 'P E R I O D O');
               $objPHPExcel->getActiveSheet()->SetCellValue('V8', 'P E R I O D O');
               // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AA8', 'P E R I O D O');
               }               
            }
		}
		
		// COLOCAR CÓDIGO PARA EL ASPECTO DE LA CONDUCTA.
		switch ($hoja_aspectos) {
			case 2:
				$objPHPExcel->getActiveSheet()->SetCellValue('H11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('M11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('R11', "'".$nuevo_codigo_asignatura."'");
            $objPHPExcel->getActiveSheet()->SetCellValue('W11', "'".$nuevo_codigo_asignatura."'");
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AB11', "'".$nuevo_codigo_asignatura."'");   
               }
            // Nombre Asignatura.
            $objPHPExcel->getActiveSheet()->SetCellValue('H12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('M12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('R12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('W12', $nombre_asignatura);
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AB12', $nombre_asignatura);
               }
				break;
			case 3:
				$objPHPExcel->getActiveSheet()->SetCellValue('I11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('N11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('S11', "'".$nuevo_codigo_asignatura."'");
            $objPHPExcel->getActiveSheet()->SetCellValue('X11', "'".$nuevo_codigo_asignatura."'");
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AC11', "'".$nuevo_codigo_asignatura."'");   
               }
            // Nombre Asignatura.
            $objPHPExcel->getActiveSheet()->SetCellValue('I12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('N12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('S12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('X12', $nombre_asignatura);
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AC12', $nombre_asignatura);
               }
				break;
			case 4:
				$objPHPExcel->getActiveSheet()->SetCellValue('J11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('O11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('T11', "'".$nuevo_codigo_asignatura."'");
            $objPHPExcel->getActiveSheet()->SetCellValue('Y11', "'".$nuevo_codigo_asignatura."'");
           // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AD11', "'".$nuevo_codigo_asignatura."'");   
               }
            // Nombre Asignatura.
            $objPHPExcel->getActiveSheet()->SetCellValue('J12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('O12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('T12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('Y12', $nombre_asignatura);
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AD12', $nombre_asignatura);
               }
				break;
			case 5:
				$objPHPExcel->getActiveSheet()->SetCellValue('K11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('P11', "'".$nuevo_codigo_asignatura."'");
				$objPHPExcel->getActiveSheet()->SetCellValue('U11', "'".$nuevo_codigo_asignatura."'");
            	$objPHPExcel->getActiveSheet()->SetCellValue('Z11', "'".$nuevo_codigo_asignatura."'");
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AE11', "'".$nuevo_codigo_asignatura."'");   
               }
            // Nombre Asignatura.
            $objPHPExcel->getActiveSheet()->SetCellValue('K12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('P12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('U12', $nombre_asignatura);
            $objPHPExcel->getActiveSheet()->SetCellValue('Z12', $nombre_asignatura);
            // Nocturna.
               if($codigo_bach == "10"){
                  $objPHPExcel->getActiveSheet()->SetCellValue('AE12', $nombre_asignatura);
               }
				break;
		}		
	    $codigo_all = $bach.$grado.$sec.$ann;
			$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
					a.genero, a.id_alumno as cod_alumno, am.id_alumno_matricula as codigo_matricula, am.codigo_bach_o_ciclo,
					bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion,
					n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.codigo_asignatura, ae.codigo_alumno, ae.encargado
					FROM alumno a 
					INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
					INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
					INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
					INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
					INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
					INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
					INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
						WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."' and n.codigo_asignatura = '".$nuevo_codigo_asignatura."' and n.codigo_alumno = a.id_alumno ORDER BY apellido_alumno ASC";
		// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
		    $result = $dblink -> query($query);
			$result1 = $dblink -> query($query);  
	    //  imprimir datos del bachillerato.
		while($rows = $result1 -> fetch(PDO::FETCH_BOTH))
		    {
			$nombre_bachillerato = trim($rows['nombre_bachillerato']);
			$nombre_grado = TRIM($rows['nombre_grado']);
			 
			//$nombre_grado = iconv("ISO-8859-1", "UTF-8",trim($rows['nombre_grado']));
			$nombre_seccion = trim($rows['nombre_seccion']);
			$nombre_ann_lectivo = trim($rows['nombre_ann_lectivo']);
			//$nombre_asignatura = iconv("ISO-8859-1", "UTF-8",trim($rows['nombre_asignatura']));
			
			$codigo_bach = "'".trim($rows['codigo_bach_o_ciclo'])."'";
			$codigo_ann = "'".trim($rows['codigo_ann_lectivo'])."'";
			$codigo_seccion = "'".trim($rows['codigo_seccion'])."'";
			$codigo_grado = "'".trim($rows['codigo_grado'])."'";
			
			$codigo_encargado = trim($rows['encargado']);
			
			$filename = $nombre_grado." ".$nombre_seccion;
			break;
		    }	
	    // Correlativo, numero de linea.
		$num = 0; $fila_excel = 12;
		while($row = $result -> fetch(PDO::FETCH_BOTH))
		{
		// acumular correlativo y fila.
		    $num++; $fila_excel++;
		// validar el genero.
		if(TRIM($row['genero']) == 'm'){$sexo = 'M';}else{$sexo = 'F';}
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //  IMPRIMIR EL CONTENIDO DE  INFORMACION EN EXCEL.
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			$objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, $num);
			$objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, TRIM($row['codigo_nie']));
            $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel, TRIM($row['codigo_alumno']));
            $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel, TRIM($row['codigo_matricula']));
            $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel, (TRIM($row['apellido_alumno'])));
			$objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel, $sexo);
					// Grabar la Nota según sean las condiciones.
						if($trimestre_1 == "yes"){
							switch ($hoja_aspectos) {
									case 1:
										$objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, (TRIM($row['nota_p_p_1'])));		
										break;
									case 2:
										$objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, (TRIM($row['nota_p_p_1'])));
										break;
									case 3:
										$objPHPExcel->getActiveSheet()->SetCellValue("I".$fila_excel, (TRIM($row['nota_p_p_1'])));
										break;
									case 4:
										$objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel, (TRIM($row['nota_p_p_1'])));
										break;
									case 5:
										$objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel, (TRIM($row['nota_p_p_1'])));
										break;
								}
						}
						if($trimestre_2 == "yes"){
							switch ($hoja_aspectos) {
									case 1:
										$objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("L".$fila_excel, (TRIM($row['nota_p_p_2'])));		
										break;
									case 2:
										$objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("M".$fila_excel, (TRIM($row['nota_p_p_2'])));
										break;
									case 3:
										$objPHPExcel->getActiveSheet()->SetCellValue("I".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("N".$fila_excel, (TRIM($row['nota_p_p_2'])));
										break;
									case 4:
										$objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel, (TRIM($row['nota_p_p_2'])));
										break;
									case 5:
										$objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel, (TRIM($row['nota_p_p_2'])));
										break;
								}							
						}
						if($trimestre_3 == "yes"){
							switch ($hoja_aspectos) {
									case 1:
										$objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("L".$fila_excel, (TRIM($row['nota_p_p_2'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										break;
									case 2:
										$objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("M".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("R".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										break;
									case 3:
										$objPHPExcel->getActiveSheet()->SetCellValue("I".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("N".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("S".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										break;
									case 4:
										$objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("T".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										break;
									case 5:
										$objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("U".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										break;
								}							
						}
						if($trimestre_4 == "yes"){
							switch ($hoja_aspectos) {
									case 1:
										$objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("L".$fila_excel, (TRIM($row['nota_p_p_2'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("V".$fila_excel, (TRIM($row['nota_p_p_4'])));		
										break;
									case 2:
										$objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("M".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("R".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("W".$fila_excel, (TRIM($row['nota_p_p_4'])));		
										break;
									case 3:
										$objPHPExcel->getActiveSheet()->SetCellValue("I".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("N".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("S".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("X".$fila_excel, (TRIM($row['nota_p_p_4'])));		
										break;
									case 4:
										$objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("T".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, (TRIM($row['nota_p_p_4'])));		
										break;
									case 5:
										$objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel, (TRIM($row['nota_p_p_1'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel, (TRIM($row['nota_p_p_2'])));
										$objPHPExcel->getActiveSheet()->SetCellValue("U".$fila_excel, (TRIM($row['nota_p_p_3'])));		
										$objPHPExcel->getActiveSheet()->SetCellValue("Z".$fila_excel, (TRIM($row['nota_p_p_4'])));		
										break;
								}							
						}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
	    }    //  FIN DEL WHILE.
	    // Proteger hoja.
			$objPHPExcel->getActiveSheet()->getProtection()->setPassword('1');
         //$objPHPExcel->getActiveSheet()->getProtection()->setSelectUnlockedCells(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
	}
   }// FOR QUE SIRVE PARA VER CUANTAS HOJAS SE CREAN.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Guardamos el archivo en formato Excel 2003
// Verificar si Existe el directorio archivos.
		$codigo_modalidad = $codigo_bach;
	// Tipo de Carpeta a Grabar.
		$codigo_destino = 2;
		CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
    // nombre del archivo. verificar el tipo.
    	$nombre_archivo = replace_3($nombre_docente."-".$nombre_ann_lectivo.".xlsx");
		if (!mb_check_encoding($nombre_docente, 'UTF-8')){
			$nombre_docente = mb_convert_encoding($nombre_docente,'UTF-8');
			$nombre_archivo = $nombre_docente."-".$nombre_ann_lectivo.".xlsx";
		}
    // Eliminar hojas que se han tomado como base.
    	$objPHPExcel->removeSheetByIndex(0);
    	$objPHPExcel->removeSheetByIndex(0);
    	$objPHPExcel->removeSheetByIndex(0);   // hOJAS DE LA NOCTURNA.
    	$objPHPExcel->removeSheetByIndex(0);   // hOJAS DE LA NOCTURNA.
	try {
		$objPHPExcel->setActiveSheetIndex(0);
    // Verificar el formato a grabar el archivo.
		$objWriter = new Xlsx($objPHPExcel);
	// nuevo nombre sin espacios.. para guardar el archivo.
		$nombre_archivo = str_replace(" ","-",$nombre_archivo);
     // Guardar la ubicacion y el nombre del archivo.
		$objWriter->save($DestinoArchivo.$nombre_archivo);
      // Calcular el tiempo.
      $tiempo_fin = microtime(true); //true es para que sea calculado en segundos
      $duration = $tiempo_fin - $tiempo_inicio;
      $hours = (int)($duration/60/60);
      $minutes = (int)($duration/60)-$hours*60;
      $seconds = (int)$duration-$hours*60*60-$minutes*60;
		
      $n_hoja = $n_hoja - 4;
      $mensajeError = "/registro_academico/Archivos/10391/Cuadro_Notas/2019/".$nombre_archivo;
      $contenidoOK = "<p><strong>Nombre del Archivo: " . $nombre_archivo . "</strong></p>"
      . "<p>" . "Nº de Hojas creadas: " . $n_hoja  . "</p>"
      . "<p>" . "Tiempo empleado: " . $minutes . " minutos " . $seconds . " segundos" . "</p>";
      //."<a href=/registro_academico/Archivos/10391/Cuadro_Notas/2019/".$nombre_archivo." download>$nombre_archivo</a>"
      //;
    // cambiar permisos del archivo antes grabado.
		//chmod($DestinoArchivo.$nombre_archivo,07777);
	}catch(Exception $e){
		$respuestaOK = false;
		$mensajeError = "/registro_academico/Archivos/10391/Cuadro_Notas/2019/".$nombre_archivo;
		$contenidoOK = "Nº de Hoja - > ".$n_hoja." ".$e;
	}
// Armamos array para convertir a JSON
	$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK,
		"mensajeErrorTabla" => $mensajeErrorTabla);	
// enviar el Json
echo json_encode($salidaJson);
?>