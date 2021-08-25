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
	$grado = trim($_REQUEST["grado"]);
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
	$total_de_hojas = $objPHPExcel->getSheetCount();
	$objPHPExcel->setActiveSheetIndex(0);
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
	case "I3":
		$letra_indicador = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
							,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
							,"BA4","BB4","BC4","BD4","BE4","BF4","BG4","BH4","BI4","BJ4","BK4","BL4","BM4","BN4","BO4","BP4");
		$nota_indicador = array("F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE"
							,"AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ"
							,"BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP"); // para los aspectos de la conducta
	break;
	case "4P":
		$letra_indicador = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
							,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
							,"BA4","BB4","BC4","BD4","BE4","BF4","BG4","BH4");
		$nota_indicador = array("F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE"
							,"AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ"
							,"BA","BB","BC","BD","BE","BF","BG","BH"); // para los aspectos de la conducta
	break;
	case "5P":
		$letra_indicador = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
							,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
							,"BA4","BB4","BC4","BD4");
		$nota_indicador = array("F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE"
							,"AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ"
							,"BA","BB","BC","BD"); // para los aspectos de la conducta						
	break;
	case "6P":
		$letra_indicador = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
							,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
							,"BA4");
		$nota_indicador = array("F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z","AA","AB","AC","AD","AE"
							,"AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ"
							,"BA"); // para los aspectos de la conducta												
	break;
	default:
    	$datos[$fila_array]["registro"] = 'No_registro';
            return;
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
           default:
            echo "";
		  }		   
/// LEER DATOS DEL INDICADOR Y DE LA HOJA DE EXCEL.
for ($ii = 0; $ii < count($letra_indicador); $ii++)
{
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
		if($nota_ <> ""){
				$query_indicador = "UPDATE nota SET $nota_p_p = '$nota_' WHERE codigo_alumno = $codigo_interno and codigo_matricula = $codigo_matricula and codigo_asignatura = '$codigo_asignatura'";
				$result = $db_link -> query($query_indicador);
			}
				$fila_nota++;
	}
		// REINICIAR EL VALOR DE FILA _NOTA
			$fila_nota=7;
}	//  CIERRE DEL FOR.

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($grado == 'I3' || $grado == '4P' || $grado == '5P')
{
/// ******************************************************************************************
/// CONDICIONAR SI EXISTEN ALERTAS.
/// ******************************************************************************************
$objPHPExcel->setActiveSheetIndex(1);
	//	codigo de la asignatura. modalidad, docente
	$fila = 7; $fila_ = 7; $fila_nota = 7;
	//	Variable para las actividades, nota promedio Y observaciones.
	$indicador_1 = ""; $indicador_2 = ""; $indicador_3 = ""; $indicador_final = "";
	switch ($grado)
	{
		case 'I3':
			$letra_indicador_alertas = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4");
			$nota_indicador_alertas = array("F","G","H","I","J","K","L","M","N","O"); // para los aspectos de la conducta
			$nota_p_p = 'alertas';			
		break;

		case '4P':
			$letra_indicador_alertas = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4");
			$nota_indicador_alertas = array("F","G","H","I","J","K","L","M","N","O","P"); // para los aspectos de la conducta
			$nota_p_p = 'alertas';
		break;

		case '5P':
			$letra_indicador_alertas = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4");
			$nota_indicador_alertas = array("F","G","H","I","J","K","L","M","N","O","P","Q","R"); 	// para los aspectos de la conducta		
			$nota_p_p = 'alertas';
		break;

		default:
		break;
	}
	for ($ii = 0; $ii < count($letra_indicador_alertas); $ii++)
	{
		// CODIGO DEL INDICADOR.
			$codigo_asignatura = $objPHPExcel->getActiveSheet()->getCell($letra_indicador_alertas[$ii])->getValue();
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("E".$fila_nota)->getValue() != "")
		{
			// ASIGNAR VALORES A VARIABLES.
			$codigo_interno = $objPHPExcel->getActiveSheet()->getCell("B".$fila_nota)->getValue();
			$codigo_matricula = $objPHPExcel->getActiveSheet()->getCell("C".$fila_nota)->getValue();
			$nombre_del_alumno = $objPHPExcel->getActiveSheet()->getCell("E".$fila_nota)->getValue();
			$nota_ = strtoupper($objPHPExcel->getActiveSheet()->getCell($nota_indicador_alertas[$ii].$fila_nota)->getValue());
			// SQL QUERY
			if($nota_ <> ""){
					$query_indicador = "UPDATE nota SET $nota_p_p = '$nota_' WHERE codigo_alumno = $codigo_interno and codigo_matricula = $codigo_matricula and codigo_asignatura = '$codigo_asignatura'";
					$result = $db_link -> query($query_indicador);
				}
					$fila_nota++;
		}
			// REINICIAR EL VALOR DE FILA _NOTA
				$fila_nota=7;
	}	//  CIERRE DEL FOR.
}
	/// ******************************************************************************************
/// CONDICIONAR SI EXISTEN ALERTAS.
/// ******************************************************************************************
	$datos[$fila_array]["registro"] = 'Si_registro';
	$fila_array++;
// Enviando la matriz con Json.
	echo json_encode($datos);
?>