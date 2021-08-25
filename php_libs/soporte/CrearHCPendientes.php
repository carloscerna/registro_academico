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
    $n_hoja = 0;	// variable para el activesheet.
	$fila_docente = 6;
    // Correlativo, numero de linea.
	$num = 0; $fila_excel = 4;
    $codigo_docente = trim($_REQUEST["codigo_docente"]);
    $codigo_annlectivo = trim($_REQUEST["codigo_annlectivo"]);
	$guardar_registro = 0;
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
		$nombre_de_hoja_de_calculo = "Control de Actividades Asignaturas Pendientes.xlsx";
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
        INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
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
				$codigo_modalidad = trim($rows['codigo_bachillerato']);
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
                         }
	
				$objWorkSheetBase = $objPHPExcel->getSheet($n_hoja);
				$objPHPExcel->setActiveSheetIndex($n_hoja);
				$objPHPExcel->getActiveSheet($n_hoja)->setTitle($nombre_ann_lectivo);
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//Escribimos en la hoja en la celda e3. los datos del bachillerato, grado, secci?n, a?o lectivo, etc.
			// Y comparamos si es el primer ciclo u otro.
			$objPHPExcel->getActiveSheet()->SetCellValue('E2', $nombre_docente_en_excel);
	      
	    		$codigo_all = $bach.$grado.$sec.$ann;
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
					a.genero, a.id_alumno as cod_alumno, am.id_alumno_matricula as codigo_matricula, am.codigo_bach_o_ciclo,
					bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion,
					n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.nota_final, n.codigo_asignatura, ae.codigo_alumno, ae.encargado,
					round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, 
            		round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, 
            		round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4+n.nota_p_p_5),1) as total_puntos_nocturna
						FROM alumno a 
						INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
						INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
						INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
						INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
						INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
						INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
						INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
							WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."' 
    		                    and n.codigo_asignatura = '".$nuevo_codigo_asignatura."' 
            			            and n.codigo_alumno = a.id_alumno ORDER BY apellido_alumno ASC";
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
				// Total de puntos.
				$total_puntos_basica = trim($rows['total_puntos_basica']);
				$total_puntos_media = trim($rows['total_puntos_media']);
				$total_puntos_nocturna = trim($rows['total_puntos_nocturna']);
				//
				$codigo_encargado = trim($rows['encargado']);
				
				$filename = $nombre_grado." ".$nombre_seccion;
			break;
		    }	
			while($row = $result -> fetch(PDO::FETCH_BOTH))
				{
					// VALIDAR SI LA NOTA FINAL ES MAYOR DE.... DEPENDIENDO DE LA MODALDIAD.
					// EVALUAR EN EL CASO DE EDUCACIÓN BASICA, TERCER CICLO Y NOCTURNMA
					if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08'){
						if($codigo_modalidad == '03' || $codigo_modalidad == '04' || $codigo_modalidad == '05' || $codigo_modalidad == '10'){
							if(round($row['nota_final'],0) < 5){
								$guardar_registro = 1;
							}else{
								$guardar_registro = 0;
							}
						}else if($codigo_modalidad == '06' || $codigo_modalidad == '07' || $codigo_modalidad == '08' || $codigo_modalidad == '09'){
							if(round($row['nota_final'],0) < 6){
								$guardar_registro = 1;
							}else{
								$guardar_registro = 0;
							}
						}
					}else{
						// No guardar registro
							$guardar_registro = 0;
					}

					//
					if($guardar_registro == 1){
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
							$objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, $codigo_asignatura);
							$objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, $nombre_asignatura);
							$objPHPExcel->getActiveSheet()->SetCellValue("I".$fila_excel, $nombre_grado);
							$objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel, $nombre_seccion);
							$objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel, $codigo_modalidad);
						// Grabar la Nota según sean las condiciones.
							$objPHPExcel->getActiveSheet()->SetCellValue("L".$fila_excel, (TRIM($row['nota_p_p_1'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("M".$fila_excel, (TRIM($row['nota_p_p_2'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("N".$fila_excel, (TRIM($row['nota_p_p_3'])));
						// TOTAL PUNTOS.
							$objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel, (TRIM($row['total_puntos_basica'])));
						// BACHILLERATO
						if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){
							$objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel, (TRIM($row['nota_p_p_4'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel, (TRIM($row['total_puntos_media'])));
						}
						// BACHILLERATO NOCTURNA
						if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){
							$objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel, (TRIM($row['nota_p_p_4'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel, (TRIM($row['nota_p_p_5'])));
							$objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel, (TRIM($row['total_puntos_nocturna'])));
						}	
						////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
					}
				}    //  FIN DEL WHILE.
	    // Proteger hoja.
			$objPHPExcel->getActiveSheet()->getProtection()->setPassword('1');
            $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
    	$nombre_archivo = replace_3($nombre_docente."-".$nombre_ann_lectivo."-Asignaturas Pendientes.xlsx");
		if (!mb_check_encoding($nombre_docente, 'UTF-8')){
			$nombre_docente = mb_convert_encoding($nombre_docente,'UTF-8');
			$nombre_archivo = $nombre_docente."-".$nombre_ann_lectivo.".xlsx";
		}
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