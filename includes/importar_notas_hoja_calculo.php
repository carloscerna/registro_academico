<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
	$codigo_institucion = $_SESSION['codigo_institucion'];
	$ruta = '../files/' . $codigo_institucion . "/" . trim($_REQUEST["nombre_archivo_"]);
	  $trimestre = trim($_REQUEST["periodo_"]);
	  $nombre_archivo = trim($_REQUEST["nombre_archivo_"]);
// variable de la conexi�n dbf.
    $db_link = $dblink;
// Inicializando el array
	$datos=array(); $fila_array = 0;
	$datos[$fila_array]["registro"] = 'Si_registro';
	$datos[$fila_array]["nombre_archivo"] = $nombre_archivo;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////  
// iniciar PhpSpreadsheet
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
    use PhpOffice\PhpSpreadsheet\Shared\Date;
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Time zone.
    //echo date('H:i:s') . " Set Time Zone"."<br />";
    date_default_timezone_set('America/El_Salvador');

// set codings.
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';

// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

// Leemos un archivo Excel 2007
    //$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
    $objPHPExcel = $objReader->load($origen);

// N�mero de hoja.
   //$numero_de_hoja = 0;
   $total_de_hojas = $objPHPExcel->getSheetCount();


    for($numero_de_hoja=0;$numero_de_hoja<$total_de_hojas;$numero_de_hoja++)
    {	        
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//	codigo de la asignatura. modalidad, docente
   $codigo_asignatura = trim($objPHPExcel->getActiveSheet()->getCell("D7")->getValue());
   $codigo_bachillerato = $objPHPExcel->getActiveSheet()->getCell("D3")->getValue();
   $codigo_grado_ = $objPHPExcel->getActiveSheet()->getCell("D5")->getValue();
   $codigo_grado = trim(str_replace("'","",$codigo_grado_));
   $codigo_docente = $objPHPExcel->getActiveSheet()->getCell("F2")->getValue();
   $fila = 13; $num = 1;
//	Variable para las actividades, nota promedio Y observaciones.
   $nota_a_1 = 0; $nota_a_2 = 0; $nota_a_3 = 0; $nota_r = 0; $observacion = '';
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////	    
	    // En la siguientes lineas se condicionara si se desea para primer ciclo. para generar solamente dos hojas mas.
	    //  Get the current sheet with all its newly-set style properties
	    // VERIFICAR CUANDO SEAN ASIGNATURAS PARA LOS ASPECTOS DE LA CONDUCTA.
	
      if($codigo_asignatura == "")
      {
       // generar las variables para guardar la nota y que periodo o trimestre.
		 switch ($trimestre)
		 {
		  case "P1":
			$nota_p_p = 'nota_p_p_1';
			$observacion = 'observacion_1';
			$codigo_de_letras_01 = array("G11","H11","I11","J11","K11"); // para los aspectos de la conducta
			$nota_de_letras_01 = array("G","H","I","J","K"); // para los aspectos de la conducta
			break;
		  case "P2":
			$nota_p_p = 'nota_p_p_2';
			$observacion = 'observacion_2';
			$codigo_de_letras_01 = array("L11","M11","N11","O11","P11"); // para los aspectos de la conducta
			$nota_de_letras_01 = array("L","M","N","O","P"); // para los aspectos de la conducta
			break;
		  case "P3":
			$nota_p_p = 'nota_p_p_3';
			$observacion = 'observacion_3';
			$codigo_de_letras_01 = array("Q11","R11","S11","T11","U11"); // para los aspectos de la conducta
			$nota_de_letras_01 = array("Q","R","S","T","U"); // para los aspectos de la conducta
		  	break;
		  case "P4":
			$nota_p_p = 'nota_p_p_4';
			$observacion = 'observacion_4';
			$codigo_de_letras_01 = array("V11","W11","X11","Y11","Z11"); // para los aspectos de la conducta
			$nota_de_letras_01 = array("V","W","X","Y","Z"); // para los aspectos de la conducta
		  	break;
		  case "P5":
				$nota_p_p = 'nota_p_p_5';
				$observacion = 'observacion_5';
				$codigo_de_letras_01 = array("AA11","AB11","AC11","AD11","AE11"); // para los aspectos de la conducta
				$nota_de_letras_01 = array("AA","AB","AC","AD","AE"); // para los aspectos de la conducta
		  break;
		default:
			echo "";
		 }

		 if($trimestre != "Recuperacion")
		 {
			   // tomar los c�digos de los aspectos.
			   $codigo_a_aspecto_1 = $objPHPExcel->getActiveSheet()->getCell($codigo_de_letras_01[0])->getValue();
			   $codigo_a_aspecto_2 = $objPHPExcel->getActiveSheet()->getCell($codigo_de_letras_01[1])->getValue();
			   $codigo_a_aspecto_3 = $objPHPExcel->getActiveSheet()->getCell($codigo_de_letras_01[2])->getValue();
			   $codigo_a_aspecto_4 = $objPHPExcel->getActiveSheet()->getCell($codigo_de_letras_01[3])->getValue();
			   $codigo_a_aspecto_5 = $objPHPExcel->getActiveSheet()->getCell($codigo_de_letras_01[4])->getValue();

			   
		   //	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
			  while($objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue() != "")
			   {	
				  $codigo_nie = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
				  $codigo_interno = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();
				  $codigo_matricula = $objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue();
				  $nombre_del_alumno = $objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue();

				  $nota_aspecto_1 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[0].$fila)->getValue();
				  $nota_aspecto_2 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[1].$fila)->getValue();
				  $nota_aspecto_3 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[2].$fila)->getValue();
				  $nota_aspecto_4 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[3].$fila)->getValue();
				  $nota_aspecto_5 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[4].$fila)->getValue();
			 //$observacion_1 = $objPHPExcel->getActiveSheet()->getCell("L".$fila)->getValue();
		   
				   // concionar los datos si el valor de la nota de perido es igual a cero no grabe.
			   if($nota_aspecto_1 != 0 || $nota_aspecto_2 != 0 || $nota_aspecto_3 != 0 || $nota_aspecto_4 != 0 || $nota_aspecto_5 != 0)
			   {
				  // Armar consulta para actulizar notas.
				  $query_aspectos_1 = "UPDATE nota SET ".$nota_p_p." = ".$nota_aspecto_1." WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_a_aspecto_1;
				  $query_aspectos_2 = "UPDATE nota SET ".$nota_p_p." = ".$nota_aspecto_2." WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_a_aspecto_2;
				  $query_aspectos_3 = "UPDATE nota SET ".$nota_p_p." = ".$nota_aspecto_3." WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_a_aspecto_3;
				  $query_aspectos_4 = "UPDATE nota SET ".$nota_p_p." = ".$nota_aspecto_4." WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_a_aspecto_4;
				  //$query_aspectos_5 = "UPDATE nota SET ".$nota_p_p." = ".$nota_aspecto_5." WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_a_aspecto_5;
				  
				  //$query_observacion = "UPDATE nota SET $observacion = '$observacion_1' WHERE codigo_alumno = $codigo_interno and codigo_matricula = $codigo_matricula";
				  // ejecutar la consulta.
						$result = $db_link -> query($query_aspectos_1);
					   $result = $db_link -> query($query_aspectos_2);
					   $result = $db_link -> query($query_aspectos_3);
					   $result = $db_link -> query($query_aspectos_4);
					   //$result = $db_link -> query($query_aspectos_5);
					// Query cuando son notas num�ricas.
						$query_nota_final_aspectos_1 = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_1)
							                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_1";
					// Query cuando son notas num�ricas.
						$query_nota_final_aspectos_2 = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_2)
							                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_2";
					// Query cuando son notas num�ricas.
						$query_nota_final_aspectos_3 = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_3)
							                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_3";
					// Query cuando son notas num�ricas.
						$query_nota_final_aspectos_4 = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_4)
							                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_4";												 
					/*/ Query cuando son notas num�ricas.
						$query_nota_final_aspectos_5 = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_5)
							                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_a_aspecto_5";												 */
					// ejecutamos el query de la nota final. 
					 $result = $db_link -> query($query_nota_final_aspectos_1);
					 $result = $db_link -> query($query_nota_final_aspectos_2);
					 $result = $db_link -> query($query_nota_final_aspectos_3);
					 $result = $db_link -> query($query_nota_final_aspectos_4);
					 //$result = $db_link -> query($query_nota_final_aspectos_5);
					  //$result = $db_link -> query($query_observacion);
				}
					  // INCREMENTAR I PARA LA COLUMNA de excel.
					$fila++; $num++;
			}
		   }	// if para que evalue si es recuperacion.
      }else{
	 // generar las variables para guardar la nota y que periodo o trimestre.
	switch ($trimestre)
	{
	 case "P1":
	   $nota_p_p = 'nota_p_p_1';
	   $observacion = 'observacion_1';
	   $nota_de_letras_01 = array("L","R","V","X","Y"); // para los aspectos de la conducta
	   break;
	 case "P2":
	   $nota_p_p = 'nota_p_p_2';
	   $observacion = 'observacion_2';
	   $nota_de_letras_01 = array("AE","AK","AO","AQ","AR"); // para los aspectos de la conducta
	   break;
	 case "P3":
	   $nota_p_p = 'nota_p_p_3';
	   $observacion = 'observacion_3';
	   $nota_de_letras_01 = array("AX","BD","BH","BJ","BK"); // para los aspectos de la conducta
	   break;
     case "P4":
	   $nota_p_p = 'nota_p_p_4';
	   $nota_de_letras_01 = array("BQ","BW","CA","CC","CD"); // para los aspectos de la conducta
	   $observacion = 'observacion_4';
	   break;
	case "P5":
		$nota_p_p = 'nota_p_p_5';
		$nota_de_letras_01 = array("CJ","CP","CT","CV","CW"); // para los aspectos de la conducta
		$observacion = 'observacion_5';
		break;
	case "Recuperacion":
	   $nota_p_p = 'recuperacion';
	   $nota_de_letras_01 = array("CF"); // para los aspectos de la conducta
	   break;
	 default:
	   echo "";
	}
	
//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
while($objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue() != "")
  {
    //  DATOS GENERALES.
    $codigo_nie = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
    $codigo_interno = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();
    $codigo_matricula = $objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue();
    $nombre_del_alumno = $objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue();

	 if($trimestre == "Recuperacion"){
		//comprobaci�n si no esta vac�a.
		$nota_recuperacion = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[0].$fila)->getCalculatedValue();
		$nota_trimestre_1 = $nota_recuperacion;
		if($nota_recuperacion == ''){$nota_recuperacion = 0;};		
		}else{
    //  notas de actividades, recuperacion, periodos o trimestres.
    $nota_a_1 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[0].$fila)->getoldCalculatedValue();
    $nota_a_2 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[1].$fila)->getoldCalculatedValue();
    $nota_a_3 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[2].$fila)->getoldCalculatedValue();
	//
	//$nota_a_1 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[0].$fila)->getValue();
    //$nota_a_2 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[1].$fila)->getValue();
    //$nota_a_3 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[2].$fila)->getValue();
    
    $nota_recuperacion = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[3].$fila)->getoldCalculatedValue();
    $nota_trimestre_1 = $objPHPExcel->getActiveSheet()->getCell($nota_de_letras_01[4].$fila)->getoldCalculatedValue();
}
      //// IMPORTANTE ////////////////
      //****************************************************************************
      // ANTES DE ACUTLIZAR VERIFICAR SI LA ASIGNATURA NO ESTA DIVIDIDA.
      // TABLA: asignatura; CAMPO: partes_dividida
      // si es igual a cero no hacer nada.
      // Armar consulta.
	/*$query_asignatura = "SELECT * FROM asignatura where codigo = ".trim($codigo_asignatura);
      // ejecutar la consulta.
        $result_asignatura = $db_link -> query($query_asignatura);
	//  recorrer la tabla.
	    while($row = $result_asignatura -> fetch(PDO::FETCH_BOTH))
		  {
		    $partes_dividida = $row['partes_dividida'];
		    break;
		  }*/
	  /// FINAL DE LA COMPROBACI�N DE ASIGNATURAS POR PARTE.
	  $partes_dividida = 0;
	//***********************************************************************************************
	//	CALCULAR DE PRIMERO A SEXTO GRADO.
	//	CALIFICACIÓN POR TRIMESTRE ENTERA Y CALIFICACIÓN FINAL EN ENTERO.
	  	if($codigo_grado >= "01" and $codigo_grado <= "05"){
			$nota_1 = round($nota_trimestre_1,0);  
		  }
	//***********************************************************************************************
	//	CALCULAR DE Séptimo  A Noveno GRADO.
	//	CALIFICACIÓN POR TRIMESTRE  Y CALIFICACIÓN FINAL EN ENTERO.
		if($codigo_grado >= "06" and $codigo_grado <= "09"){
			$nota_1 = round($nota_trimestre_1,0);  
		}
	//***********************************************************************************************
	//	CALCULAR DE Primeros y Tercer año de Bachillerato
	//	CALIFICACIÓN POR TRIMESTRE  Y CALIFICACIÓN FINAL EN ENTERO.
		if($codigo_grado >= "10" and $codigo_grado <= "16"){
			$nota_1 = round($nota_trimestre_1,1);  
		}
    // condicionar bachilleratos para redondear la nota de cada periodo.
		// Verificar cuando la calificación esta entre 0 y < 1.
		if($nota_1 > 0 and $nota_1 < 1){
			$nota_1 = 1;
		}
     // generar las variables para guardar la nota y que periodo o trimestre.
	switch ($trimestre)
	{
	 case "P1":
	   $nota_p_p = 'nota_p_p_1';
	   $nota_a1 = 'nota_a1_1'; $nota_a2 = 'nota_a2_1'; $nota_a3 = 'nota_a3_1'; $nota_r = 'nota_r_1';
	   $nota_ = $nota_1;
	   break;
	 case "P2":
	   $nota_p_p = 'nota_p_p_2';
	   $nota_a1 = 'nota_a1_2'; $nota_a2 = 'nota_a2_2'; $nota_a3 = 'nota_a3_2'; $nota_r = 'nota_r_2';
	   $nota_ = $nota_1;
	   break;
	 case "P3":
	   $nota_p_p = 'nota_p_p_3';
	   $nota_a1 = 'nota_a1_3'; $nota_a2 = 'nota_a2_3'; $nota_a3 = 'nota_a3_3'; $nota_r = 'nota_r_3';
	   $nota_ = $nota_1;
	   break;
	 case "P4":
	   $nota_p_p = 'nota_p_p_4';
	   $nota_a1 = 'nota_a1_4'; $nota_a2 = 'nota_a2_4'; $nota_a3 = 'nota_a3_4'; $nota_r = 'nota_r_4';
	   $nota_ = $nota_1;
	   break;
	   case "P5":
		$nota_p_p = 'nota_p_p_5';
		$nota_a1 = 'nota_a1_5'; $nota_a2 = 'nota_a2_5'; $nota_a3 = 'nota_a3_5'; $nota_r = 'nota_r_5';
		$nota_ = $nota_1;
		break;
	   case "Recuperacion":
	   $nota_p_p = 'recuperacion';
	   $nota_ = $nota_recuperacion;
	   break;
	 case "Nota PAES":
	   $nota_p_p = 'nota_paes';
	   $nota_ = 0;
	   break;
	 default:
	   echo "";
	}	    
// concionar los datos si el valor de la nota de perido es igual a cero no grabe.
	if($nota_1 != 0)
	{  
		// GUARDAR INFORNACION DE LA NOTA DIVIDIDA EN LA TABLA: NOTAS_PARTES
		//print "partes dividida : ".$partes_dividida;
		if($partes_dividida > 0){
		// antes de guardarla verificar que si existe para solo actualizar.
		// Armar consulta.
			$query_b_notas_partes = "SELECT * FROM notas_partes where codigo_alumno = ".$codigo_interno." and codigo_matricula = ".$codigo_matricula." and codigo_asignatura = ".$codigo_asignatura." and codigo_docente = ".$codigo_docente;
		// ejecutar la consulta.
			$result_b_notas_partes = $db_link -> query($query_b_notas_partes);
			
		// condicionar el resultado de la fila.
			if($result_b_notas_partes -> rowCount() > 0){
				//print $fila_partes . " Actualizar";
				// aqui ira informaci�n para actualizar los registros.
				$query_notas_partes = "update notas_partes set $nota_p_p = $nota_ WHERE codigo_alumno = $codigo_interno AND codigo_matricula = $codigo_matricula AND codigo_asignatura = $codigo_asignatura AND codigo_docente = $codigo_docente";
				}
			else{
				// Armar consulta.
				//print $fila_partes . " Agregar.";
				$query_notas_partes = "INSERT INTO notas_partes (codigo_alumno, codigo_matricula, codigo_asignatura, codigo_docente, $nota_p_p)
				VALUES ($codigo_interno,$codigo_matricula,$codigo_asignatura,$codigo_docente,$nota_)";}
				
				// ejecutar la consulta.
				$result_notas_partes = $db_link -> query($query_notas_partes);
			}
		//////////////////////////////////////////////////////////////////////////////////////////////
		// CUANDO LA ASIGNATURA NO ESTA DIVIDIDA.
		/////////////////////////////////////////////////////////////////////////////////////////////	
			if($partes_dividida == 0){
				if($trimestre == "Recuperacion"){
					$query = "UPDATE nota SET ".$nota_p_p." = ".$nota_recuperacion.
						" WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_asignatura;
				}else{
						// Armar consulta para actulizar notas.
						$query = "UPDATE nota SET ".$nota_p_p." = ".$nota_.", $nota_a1 = ".$nota_a_1.", $nota_a2 = ".$nota_a_2.", $nota_a3 = ".$nota_a_3.", $nota_r = ".$nota_recuperacion.
									" WHERE codigo_alumno = ".$codigo_interno." and codigo_matricula = '".$codigo_matricula."' and codigo_asignatura = ".$codigo_asignatura;
						// Crear matriz para verificación de datos.
							$datos[$fila_array]["notas"] = $query;										
			}
			// Ejecutar la consulta.
			$result = $db_link -> query($query);

		// Actualizar nota final.
		
						if ($codigo_bachillerato >= "'03'" and $codigo_bachillerato <= "'05'"){
						// Query cuando son notas num�ricas.
							$query_nota_final = "UPDATE nota SET
								nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
								from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura)
												WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura";
						// Ejectuamos query.
							$consulta_nota_final = $dblink -> query($query_nota_final);
						}
						if ($codigo_bachillerato >= "'06'" and $codigo_bachillerato <= "'09'"){
							$query_nota_final = "UPDATE nota SET
								nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,1) as promedio
								from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura)
												WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura";
						// Ejectuamos query.
							$consulta_nota_final = $dblink -> query($query_nota_final);
						}
						// CALCULO PARA NOCTURNA 5 MODULOS.
						if ($codigo_bachillerato >= "'10'" and $codigo_bachillerato <= "'11'"){
							$query_nota_final = "UPDATE nota SET
								nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4 + nota_p_p_5)/5,0) as promedio
								from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura)
												WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura";
						// Ejectuamos query.
							$consulta_nota_final = $dblink -> query($query_nota_final);
						}
						// EDUCACIÓN BÁSICA DE ADULTOS
						if ($codigo_bachillerato >= "'12'"){
							// Query cuando son notas num�ricas.
								$query_nota_final = "UPDATE nota SET
									nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
									from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura)
													WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura";
							// Ejectuamos query.
								$consulta_nota_final = $dblink -> query($query_nota_final);
							}
						//
						if ($codigo_bachillerato == "'10'" && $codigo_grado == "11"){
							$query_nota_final = "UPDATE nota SET
								nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4),0) as promedio
								from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura)
												WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura";
						// Ejectuamos query.
							$consulta_nota_final = $dblink -> query($query_nota_final);
						}
						if ($codigo_bachillerato == "'10'" && $codigo_grado == "10"){
							$query_nota_final = "UPDATE nota SET
								nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4),0) as promedio
								from nota WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura)
												WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = $codigo_asignatura";
						// Ejectuamos query.
							$consulta_nota_final = $dblink -> query($query_nota_final);
						}
			}
	}	// IF QUE CONDICIONA SI LAS ASIGNATURAS NO ESTAN EN CERO.
          // INCREMENTAR I PARA LA COLUMNA de excel.
	      $fila++; $num++; $fila_array++;
  }	// fin del while que condiciona si esta vacio.
}	// el for que recorre segun el numero de hojas que existan.
    
}	// condicion para determinar si es de primer ciclo.
// Enviando la matriz con Json.
echo json_encode($datos);