<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
		//$origen = $path_root."/registro_web/formatos_hoja_de_calculo/";
		//$nombre_de_hoja_de_calculo = "Parvularia-4 años seccion A.xlsx";
    //$trimestre = "Trimestre 1";
		// variables. del post.
  $ruta = '../files/' . trim($_REQUEST["nombre_archivo_"]);
  $trimestre = trim($_REQUEST["periodo_"]);
	$grado = trim($_REQUEST["grado"]);
// variable de la conexión dbf.
    $db_link = $dblink;
// Inicializando el array
$datos=array(); $fila_array = 0;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////  
// iniciar PhpSpreadsheet
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// call the autoload
    require $path_root."/registro_web/vendor/autoload.php";
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
		$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		$origen = $ruta;
// Seleccionar el archivo con el se trabajará
		$objPHPExcel = $objReader->load($origen);

// Número de hoja.
   //$numero_de_hoja = 0;
   $total_de_hojas = $objPHPExcel->getSheetCount();


    for($numero_de_hoja=0;$numero_de_hoja<$total_de_hojas;$numero_de_hoja++)
    {	        
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//	codigo de la asignatura. modalidad, docente
   $fila = 7; $fila_ = 7; $fila_nota = 7;
//	Variable para las actividades, nota promedio Y observaciones.
   $indicador_1 = ""; $indicador_2 = ""; $indicador_3 = ""; $indicador_final = "";
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
				// EVALUAR LA VARIABLE TRIMESTRE PARA EL INDICADOR CORRECTO.
				switch ($grado)
          {
						case "Educacion Basica":
								 $letra_indicador = array("F4","G4","H4","I4","J4");
									$nota_indicador = array("F","G","H","I","J"); // para los aspectos de la conducta
						break;
							default:
							echo"";
					}
				// EVALUAR LA VARIABLE TRIMESTRE PARA EL INDICADOR CORRECTO.
				switch ($trimestre)
          {
           case "Trimestre 1":
						$nota_p_p = 'nota_p_p_1';
           break;
           case "Trimestre 2":
						$nota_p_p = 'nota_p_p_2';
           break;
           case "Trimestre 3":
						$nota_p_p = 'nota_p_p_3';     
           case "Periodo 4":
						$nota_p_p = 'nota_p_p_4';  
           default:
						echo "";
          }
		   
			 /// LEER DATOS DEL INDICADOR Y DE LA HOJA DE EXCEL.
			 for ($ii = 0; $ii < count($letra_indicador); $ii++) {
				// CODIGO DEL INDICADOR.
					$codigo_asignatura = $objPHPExcel->getActiveSheet()->getCell($letra_indicador[$ii])->getValue();
			 //	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
			  while($objPHPExcel->getActiveSheet()->getCell("E".$fila_nota)->getValue() != "")
			   {
					// ASIGNAR VALORES A VARIABLES.
				  $codigo_interno = $objPHPExcel->getActiveSheet()->getCell("B".$fila_nota)->getValue();
				  $codigo_matricula = $objPHPExcel->getActiveSheet()->getCell("C".$fila_nota)->getValue();
				  $nombre_del_alumno = $objPHPExcel->getActiveSheet()->getCell("E".$fila_nota)->getValue();
					$nota_ = strtoupper($objPHPExcel->getActiveSheet()->getCell($nota_indicador[$ii].$fila_nota)->getValue());
					// SQL QUERY
          if($nota_ != 0 ){
							$query_indicador = "UPDATE nota SET $nota_p_p = '$nota_' WHERE codigo_alumno = $codigo_interno and codigo_matricula = $codigo_matricula and codigo_asignatura = '$codigo_asignatura'";
							$result = $db_link -> query($query_indicador);
          }
					// IMPRIMIR VALORES
					  // print "<br" . $codigo_interno . " " . $codigo_matricula . " " . $nombre_del_alumno . " nota: " . $nota_  ." $codigo_asignatura<br>";
					// ACUMULAR EL VALOR DE FILA _NOTA
						$fila_nota++;
				 }
					// REINICIAR EL VALOR DE FILA _NOTA
						$fila_nota=7;
			 }	//  CIERRE DEL FOR.
}	// condicion para determinar CUANTAS HOJAS RECORRE.
	//$total_indicadores = count($letra_indicador);
	//print "<br>";
//	print " --> " . $total_indicadores;
	$datos[$fila_array]["registro"] = 'Si_registro';
	$fila_array++;
// Enviando la matriz con Json.
	echo json_encode($datos);
?>