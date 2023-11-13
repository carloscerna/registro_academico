<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
	$codigo_institucion = $_SESSION['codigo_institucion'];
	$ruta = $path_root.'/registro_academico/files/' . $codigo_institucion . "/" . trim($_REQUEST["nombre_archivo_"]);
	$nombre_archivo_ = trim($_REQUEST["nombre_archivo_"]);
  	$trimestre = trim($_REQUEST["periodo_"]);
	$codigo_grado = trim($_REQUEST["grado"]);
	$indicador_final = "";
// variable de la conexi�n dbf.
    $db_link = $dblink;
// Inicializando el array
	$datos=array(); $fila_array = 0;
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
	$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
	$origen = $ruta;
// Seleccionar el archivo con el se trabajar�
	$objPHPExcel = $objReader->load($origen);
// N�mero de hoja.
   $numero_de_hoja = 1;
   $total_de_hojas = $objPHPExcel->getSheetCount();
// Movilizarme la hoja del instrumento 2 PARA 4, 5, 6 Y 7 AÑOS.
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	VARIABLES PARA ELRECORRIDO CON EL WHILE.
   $columna_codigo_alumno = 0; $fila_indicador_codigo_asignatura = 11;
//	Variable para las actividades, nota promedio Y observaciones.
   $indicador_1 = ""; $indicador_2 = ""; $indicador_3 = ""; $indicador_final = "";
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
	    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
		// EVALUAR LA VARIABLE TRIMESTRE PARA EL INDICADOR CORRECTO.
				if($codigo_grado == "I3" || $codigo_grado == "4P" || $codigo_grado =="5P" || $codigo_grado =="6P" || $codigo_grado == "01")
				{
				  	$NombreEstudiante = array("D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
				  	"AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ");
				}
				//rint_r($NombreEstudiante);
				// EVALUAR LA VARIABLE TRIMESTRE PARA EL INDICADOR CORRECTO.
				switch ($trimestre)
					{
						case "Trimestre 1":
							$nota_p_p = 'indicador_p_p_1';
							break;
						case "Trimestre 2":
							$nota_p_p = 'indicador_p_p_2';
							break;
						case "Trimestre 3":
							$nota_p_p = 'indicador_p_p_3';  
							$indicador_final = "indicador_final";
							break;   
						default:
							echo "";
					}
			 //	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
				while($objPHPExcel->getActiveSheet()->getCell($NombreEstudiante[$columna_codigo_alumno]."9")->getValue() != "")
				{
					// valor del Código Alumno y Código Matricula.
						$nombre_del_alumno = $objPHPExcel->getActiveSheet()->getCell($NombreEstudiante[$columna_codigo_alumno]."8")->getValue();
						$codigo_alumno = $objPHPExcel->getActiveSheet()->getCell($NombreEstudiante[$columna_codigo_alumno]."9")->getValue();
						$codigo_matricula = $objPHPExcel->getActiveSheet()->getCell($NombreEstudiante[$columna_codigo_alumno]."10")->getValue();
						//
						//	RECORRER LA FILA DE LOS INDICADORES.
						//
						while($objPHPExcel->getActiveSheet()->getCell("B".$fila_indicador_codigo_asignatura)->getValue() != "")
							{
								// CAPTURAR CODIGO INDICADOR.
								$codigo_indicador = $objPHPExcel->getActiveSheet()->getCell("B".$fila_indicador_codigo_asignatura)->getValue();	
								// CAPTURAR VALOR DEL INDICADOR
								$valor_indicador = trim(strtoupper($objPHPExcel->getActiveSheet()->getCell($NombreEstudiante[$columna_codigo_alumno].$fila_indicador_codigo_asignatura)->getValue()));
								// SQL QUERY
								if($valor_indicador <> ""){
									$query_indicador = "UPDATE nota SET $nota_p_p = '$valor_indicador' WHERE codigo_alumno = $codigo_alumno and codigo_matricula = $codigo_matricula and codigo_asignatura = '$codigo_indicador'";
									$result = $db_link -> query($query_indicador);
								}
								// SQL QUERY - GUARDAR INDICADOR FINAL.
								if($trimestre == "Trimestre 3"){
									$query_indicador = "UPDATE nota SET $indicador_final = '$valor_indicador' WHERE codigo_alumno = $codigo_alumno and codigo_matricula = $codigo_matricula and codigo_asignatura = '$codigo_indicador'";
									$result = $db_link -> query($query_indicador);
								}
								// INCREMENTAR VALOR DE LA FILA.
									$fila_indicador_codigo_asignatura++;
							}
					// AUMENTAR EL VALOR DE LA COLUMNA PARA VER EL OTRO REGISTRO.
						$columna_codigo_alumno++;
					// REINIICAR EL VALOR DE DE LA FILA
						$fila_indicador_codigo_asignatura = 11;
				}		   
// FINAL DEL PROCESO
	$datos[$fila_array]["registro"] = 'Si_registro';
	$datos[$fila_array]["nombre_archivo"] = $nombre_archivo_;
	$fila_array++;
// Enviando la matriz con Json.
	echo json_encode($datos);
?>