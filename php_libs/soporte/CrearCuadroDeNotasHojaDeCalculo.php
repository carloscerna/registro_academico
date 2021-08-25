<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
	include($path_root."/registro_academico/includes/funciones_2.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// variables y consulta a la tabla.
  $codigo_all = $_REQUEST["todos"];
  $db_link = $dblink;
// Inicializamos variables de mensajes y JSON
$respuestaOK = true;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
// Información Académica.
       $codigo_bachillerato = substr($codigo_all,0,2);
       $codigo_modalidad = substr($codigo_all,0,2);
       $codigo_grado = substr($codigo_all,2,2);
       $codigo_seccion = substr($codigo_all,4,2);
       $codigo_annlectivo = substr($codigo_all,6,2);
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
// Proceso de la creaciòn de la Hoja de cálculo.
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
              $print_bachillerato ='Modalidad: '.trim($row['nombre_bachillerato']);
              $nombre_modalidad = trim($row['nombre_bachillerato']);
              $nombre_ann_lectivo = trim($row['nombre_ann_lectivo']);
              $print_grado = 'Grado: '. trim($row['nombre_grado']);
              $nombre_grado = trim($row['nombre_grado']);
              $print_seccion = ('Sección: ').trim($row['nombre_seccion']);
              $nombre_seccion = trim($row['nombre_seccion']);
              $print_ann_lectivo = 'Año Lectivo: '.trim($row['nombre_ann_lectivo']);
                break;
            }
    $n_hoja = 0;	// variable para el activesheet.
    consultas(4,0,$codigo_all,'','','',$db_link,'');
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
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
//    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";
    /////
    // SELECCIONAR EL ARCHIVO DEPENDE DE LA MODALIDAD.
    ////
    switch ($codigo_bachillerato) {
        case "01":
              if($codigo_grado == "I1"){
                $objPHPExcel = $objReader->load($origen.".xlsx");
              }
              if($codigo_grado == "I2"){
                $objPHPExcel = $objReader->load($origen.".xlsx");
              }
              if($codigo_grado == "I3"){
                $objPHPExcel = $objReader->load($origen."Inicial 3.xlsx");
								 $codigo_asignatura_hoja = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
																					 ,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
																					 ,"BA4","BB4","BC4","BD4","BE4","BF4","BG4","BH4","BI4","BJ4","BK4","BL4","BM4","BN4","BO4","BP4"
                                           ,"F4","G4","H4","I4","J4","K4","L4","M4","N4");
                 $numero_hoja = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5","R5","S5","T5","U5","V5","W5","X5","Y5","Z5","AA5","AB5","AC5","AD5","AE5"
																					 ,"AF5","AG5","AH5","AI5","AJ5","AK5","AL5","AM5","AN5","AO5","AP5","AQ5","AR5","AS5","AT5","AU5","AV5","AW5","AX5","AY5","AZ5"
																					 ,"BA5","BB5","BC5","BD5","BE5","BF5","BG5","BH5","BI5","BJ5","BK5","BL5","BM5","BN5","BO5","BP5"
                                           ,"F5","G5","H5","I5","J5","K5","L5","M5","N5");
                 $nombre_asignatura_hoja = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6","R6","S6","T6","U6","V6","W6","X6","Y6","Z6","AA6","AB6","AC6","AD6","AE6"
																					 ,"AF6","AG6","AH6","AI6","AJ6","AK6","AL6","AM6","AN6","AO6","AP6","AQ6","AR6","AS6","AT6","AU6","AV6","AW6","AX6","AY6","AZ6"
																					 ,"BA6","BB6","BC6","BD6","BE6","BF6","BG6","BH6","BI6","BJ6","BK6","BL6","BM6","BN6","BO6","BP6"
                                           ,"F6","G6","H6","I6","J6","K6","L6","M6","N6");
                 // PARA LA ALERTA TEMPRANA.
                 $codigo_asignatura_hoja_alerta = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4");
                 $numero_hoja_alerta = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5");
                 $nombre_asignatura_hoja_alerta = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6");
              }
            break;
        case "02":
              if($codigo_grado == "4P"){
                $objPHPExcel = $objReader->load($origen."Parvularia-4.xlsx");
                 $codigo_asignatura_hoja = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
																					 ,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
																					 ,"BA4","BB4","BC4","BD4","BE4","BF4","BG4","BH4");
                 $numero_hoja = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5","R5","S5","T5","U5","V5","W5","X5","Y5","Z5","AA5","AB5","AC5","AD5","AE5"
																					 ,"AF5","AG5","AH5","AI5","AJ5","AK5","AL5","AM5","AN5","AO5","AP5","AQ5","AR5","AS5","AT5","AU5","AV5","AW5","AX5","AY5","AZ5"
																					 ,"BA5","BB5","BC5","BD5","BE5","BF5","BG5","BH5");
                 $nombre_asignatura_hoja = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6","R6","S6","T6","U6","V6","W6","X6","Y6","Z6","AA6","AB6","AC6","AD6","AE6"
																					 ,"AF6","AG6","AH6","AI6","AJ6","AK6","AL6","AM6","AN6","AO6","AP6","AQ6","AR6","AS6","AT6","AU6","AV6","AW6","AX6","AY6","AZ6"
																					 ,"BA6","BB6","BC6","BD6","BE6","BF6","BG6","BH6");

                   // PARA LA ALERTA TEMPRANA.
                      $codigo_asignatura_hoja_alerta = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4");
                      $numero_hoja_alerta = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5");
                      $nombre_asignatura_hoja_alerta = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6");
                  }
              if($codigo_grado == "5P"){
                $objPHPExcel = $objReader->load($origen."Parvularia-5.xlsx");
                $codigo_asignatura_hoja = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
																					 ,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
																					 ,"BA4","BB4","BC4","BD4"
                                           ,"F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4");
                 $numero_hoja = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5","R5","S5","T5","U5","V5","W5","X5","Y5","Z5","AA5","AB5","AC5","AD5","AE5"
																					 ,"AF5","AG5","AH5","AI5","AJ5","AK5","AL5","AM5","AN5","AO5","AP5","AQ5","AR5","AS5","AT5","AU5","AV5","AW5","AX5","AY5","AZ5"
																					 ,"BA5","BB5","BC5","BD5"
                                           ,"F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5","R5");
                 $nombre_asignatura_hoja = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6","R6","S6","T6","U6","V6","W6","X6","Y6","Z6","AA6","AB6","AC6","AD6","AE6"
																					 ,"AF6","AG6","AH6","AI6","AJ6","AK6","AL6","AM6","AN6","AO6","AP6","AQ6","AR6","AS6","AT6","AU6","AV6","AW6","AX6","AY6","AZ6"
																					 ,"BA6","BB6","BC6","BD6"
                                           ,"F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6","R6");
                 // PARA LA ALERTA TEMPRANA.
                 $codigo_asignatura_hoja_alerta = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4");
                 $numero_hoja_alerta = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5","R5");
                 $nombre_asignatura_hoja_alerta = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6","R6");
              }
              if($codigo_grado == "6P"){
                $objPHPExcel = $objReader->load($origen."Parvularia-6.xlsx");
                $codigo_asignatura_hoja = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4","R4","S4","T4","U4","V4","W4","X4","Y4","Z4","AA4","AB4","AC4","AD4","AE4"
																					 ,"AF4","AG4","AH4","AI4","AJ4","AK4","AL4","AM4","AN4","AO4","AP4","AQ4","AR4","AS4","AT4","AU4","AV4","AW4","AX4","AY4","AZ4"
																					 ,"BA4");
                 $numero_hoja = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5","R5","S5","T5","U5","V5","W5","X5","Y5","Z5","AA5","AB5","AC5","AD5","AE5"
																					 ,"AF5","AG5","AH5","AI5","AJ5","AK5","AL5","AM5","AN5","AO5","AP5","AQ5","AR5","AS5","AT5","AU5","AV5","AW5","AX5","AY5","AZ5"
																					 ,"BA5");
                 $nombre_asignatura_hoja = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6","R6","S6","T6","U6","V6","W6","X6","Y6","Z6","AA6","AB6","AC6","AD6","AE6"
																					 ,"AF6","AG6","AH6","AI6","AJ6","AK6","AL6","AM6","AN6","AO6","AP6","AQ6","AR6","AS6","AT6","AU6","AV6","AW6","AX6","AY6","AZ6"
																					 ,"BA6");
              }
            break;
        case "03":
            $objPHPExcel = $objReader->load($origen."Educacion Basica.xlsx");
            break;
        case "04":
            $objPHPExcel = $objReader->load($origen."Educacion Basica.xlsx");
            break;
        case "05":
            $objPHPExcel = $objReader->load($origen."Educacion Basica - Tercer Ciclo.xlsx");
            break;
        case "06":
            $objPHPExcel = $objReader->load($origen."Educacion Media.xlsx");
            break;
        case "07":
            $objPHPExcel = $objReader->load($origen."Educacion Media.xlsx");
            break;
        case "08":
            $objPHPExcel = $objReader->load($origen."Educacion Media.xlsx");
            break;
        case "09":
            $objPHPExcel = $objReader->load($origen."Educacion Media.xlsx");
            break;          
        default:
            $objPHPExcel = $objReader->load($origen."Educacion Media.xlsx");
    }      
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener los nombres de las asignturas.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Armamos el query.
  // EDUCACIÓN INICIAL
  if($codigo_grado =="I3" || $codigo_grado =="4P" || $codigo_grado =="5P" || $codigo_grado =="6P"){
     $query_asig = "SELECT aaa.codigo_asignacion, aaa.codigo_bach_o_ciclo, aaa.codigo_asignatura, aaa.codigo_ann_lectivo, aaa.codigo_sirai, aaa.codigo_grado, aaa.id_asignacion, aaa.orden,
              ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura, asig.codigo_area
                FROM a_a_a_bach_o_ciclo aaa
								INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado
								INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura
									WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
									ORDER BY aaa.orden";
    }
  // EDUCACIÓN BÁSICA Y TERCER CICLO.
  if($codigo_bachillerato >= "03" || $codigo_bachillerato <="05"){
    $query_asig = "SELECT aaa.codigo_asignacion, aaa.codigo_bach_o_ciclo, aaa.codigo_asignatura, aaa.codigo_ann_lectivo, aaa.codigo_sirai, aaa.codigo_grado, aaa.id_asignacion, aaa.orden,
              ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura
                FROM a_a_a_bach_o_ciclo aaa
								INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado
								INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura
									WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
									ORDER BY aaa.orden";
    }
  // EDUCACIÓN MEDIA.
  if($codigo_bachillerato >= "06" || $codigo_bachillerato <="09"){
    $query_asig = "SELECT aaa.codigo_asignacion, aaa.codigo_bach_o_ciclo, aaa.codigo_asignatura, aaa.codigo_ann_lectivo, aaa.codigo_sirai, aaa.codigo_grado, aaa.id_asignacion, aaa.orden,
                  ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura, asig.codigo_area
                FROM a_a_a_bach_o_ciclo aaa
								INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado
								INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura
									WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
									ORDER BY aaa.orden";
    }
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	    $result_consulta = $dblink -> query($query_asig);
      $fila_asignatura = $result_consulta -> rowCount();
// colocar informacion en la hoja seleccionada.
	$codigo_asignatura_matriz = array(); $nombre_asignatura_matriz = array();
//  CAPTURA DE DATOS A ARRAY PARA PARVULARIA, BASICA Y MEDIA.
    // PARVULARIA
      if($codigo_grado =="I3" || $codigo_grado =="4P" || $codigo_grado =="5P" || $codigo_grado =="6P")
      {
        while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
          {
            $nombre_asignatura[] =trim($row['nombre_asignatura']);
            $codigo_asignatura[] = trim($row['codigo_asignatura']);
            $codigo_area[] = trim($row['codigo_area']);
          }
      }
      // PARA EDUCACIÓN BASICA Y TERCER CICLO.
        if($codigo_grado >= "01" || $codigo_grado <="05")
        {
          while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
          {
            $nombre_asignatura[] =trim($row['nombre_asignatura']);
            $codigo_asignatura[] = trim($row['codigo_asignatura']);
          }
        }
      // PARA EDUCACIÓN MEDIA
      if($codigo_grado >= "06" || $codigo_grado <="09")
      {
        while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
        {
          $nombre_asignatura[] =trim($row['nombre_asignatura']);
          $codigo_asignatura[] = trim($row['codigo_asignatura']);
          $codigo_area[] = trim($row['codigo_area']);
        }
      }
// Construir y Asignar codigo asignatura y nombre para hoja de calculo.
    // EDUCACIÓN BASIC AY TERCER CICLO.
   if($codigo_bachillerato >= "03" and $codigo_bachillerato <="05")
   {
    $codigo_asignatura_hoja = array("F4","G4","H4","I4","J4","K4","L4","M4","N4","O4","P4","Q4");
    $numero_hoja = array("F5","G5","H5","I5","J5","K5","L5","M5","N5","O5","P5","Q5");
    $nombre_asignatura_hoja = array("F6","G6","H6","I6","J6","K6","L6","M6","N6","O6","P6","Q6");
   }
    // EDUCACIÓN MEDIA..
   if($codigo_bachillerato >= "06" and $codigo_bachillerato <="09")
   {
    $codigo_asignatura_hoja = array("F4","G4","H4","I4","J4");
    $numero_hoja = array("F5","G5","H5","I5","J5");
    $nombre_asignatura_hoja = array("F6","G6","H6","I6","J6");
   }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Get the current sheet with all its newly-set style properties
            if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado =="5P")
                {
                    $objWorkSheetBase = $objPHPExcel->getSheet(0);
                }else{
                  $objWorkSheetBase = $objPHPExcel->getSheet(0);
                }
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex($n_hoja);
    //$objPHPExcel->getActiveSheet($n_hoja)->setTitle(cambiar_de_del($print_grado).' '.$print_seccion);
    //$n_hoja++;    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//$objPHPExcel->getActiveSheet()->SetCellValue('A2', $nombre_docente_en_excel);
      $nombre_bachillerato_en_excel = "";
      $informacion_del_grado = $nombre_modalidad . " " . $nombre_grado . " " . $nombre_seccion . " " . $nombre_ann_lectivo;
			$objPHPExcel->getActiveSheet()->SetCellValue('A2', $informacion_del_grado);
    // EDUCACIÓN BASIC AY TERCER CICLO.
   if($codigo_bachillerato >= "03" and $codigo_bachillerato <="05")
   {      
      $objPHPExcel->getActiveSheet()->SetCellValue('F1', $_SESSION["institucion"]);
      $objPHPExcel->getActiveSheet()->SetCellValue('F2', "Código: " . $_SESSION["codigo_institucion"]);
   }
   if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado =="5P" || $codigo_grado =="6P")
   {      
      $objPHPExcel->getActiveSheet()->SetCellValue('F1', $_SESSION["institucion"]);
      $objPHPExcel->getActiveSheet()->SetCellValue('F2', "Código: " . $_SESSION["codigo_institucion"]);
   }
//
//  ROTULACIÓN PARA LA HOJA DE ALERTA TEMPRANA PARVULARIA
//
if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado == "5P")
  {
    $objPHPExcel->setActiveSheetIndex(1);
    $objPHPExcel->getActiveSheet()->SetCellValue('F2', "ALERTAS TEMPRANAS");
    $objPHPExcel->getActiveSheet()->SetCellValue('C1', $_SESSION["institucion"]);
    $objPHPExcel->getActiveSheet()->SetCellValue('C2', "Código: " . $_SESSION["codigo_institucion"]);
    $objPHPExcel->setActiveSheetIndex(0);
  }
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// recorrer la array para extraer los datos. DE LOS NOMBRE DE LAS ASIGNATURAS Y SU CODIGO.
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// Variables
    $i_h_a = 0; $i_h = 0;
// si existe la variable  para PARVULARIA.
    if(isset($codigo_area))
    {
          for($ca=0;$ca<count($codigo_area);$ca++){
            if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado == "5P")
                {
                  if($codigo_area[$ca] == "09"){
                    $objPHPExcel->setActiveSheetIndex(1);                  
                      $objPHPExcel->getActiveSheet()->SetCellValue($codigo_asignatura_hoja_alerta[$i_h_a], $codigo_asignatura[$ca]);
                      $objPHPExcel->getActiveSheet()->SetCellValue($numero_hoja_alerta[$i_h_a],$i_h_a+1);
                      $objPHPExcel->getActiveSheet()->SetCellValue($nombre_asignatura_hoja_alerta[$i_h_a], $nombre_asignatura[$ca]); 
                        $i_h_a++;
                  }else{
                    $objPHPExcel->setActiveSheetIndex(0);
                    $objPHPExcel->getActiveSheet()->SetCellValue($codigo_asignatura_hoja[$i_h], $codigo_asignatura[$ca]);
                    $objPHPExcel->getActiveSheet()->SetCellValue($numero_hoja[$i_h],$i_h+1);
                    $objPHPExcel->getActiveSheet()->SetCellValue($nombre_asignatura_hoja[$i_h], $nombre_asignatura[$ca]); 
                      $i_h++;
                  }
                } // CIERRE IF PARVULARIA E INICIAL (I3, 4, 5 Y 6 AÑOS)
                // condición para parvularia 6 años.
                if($codigo_grado == "6P")
                  {
                    $objPHPExcel->setActiveSheetIndex(0);
                    $objPHPExcel->getActiveSheet()->SetCellValue($codigo_asignatura_hoja[$i_h], $codigo_asignatura[$ca]);
                    $objPHPExcel->getActiveSheet()->SetCellValue($numero_hoja[$i_h],$i_h+1);
                    $objPHPExcel->getActiveSheet()->SetCellValue($nombre_asignatura_hoja[$i_h], $nombre_asignatura[$ca]); 
                      $i_h++;
                  } // CIERRE IF PARVULARIA 6 AÑOS
         } // FOR DEL CODIGO AREA.
      }else{
        // PARA EDUCACIÓN BASICA Y MEDIA (NOMBRES DE ASIGNTURAS Y CODIGO EN LA HOJA DE CALCULO).
        for($ca=0;$ca<count($nombre_asignatura);$ca++){
         // CONDICIÓN PARA BÁSICA DESDE PRIMER GRADO HASTA MEDIA..
              if($codigo_grado >= "01" || $codigo_grado <= "05")
                {
                  $objPHPExcel->setActiveSheetIndex(0);
                  $objPHPExcel->getActiveSheet()->SetCellValue($codigo_asignatura_hoja[$i_h], $codigo_asignatura[$ca]);
                  $objPHPExcel->getActiveSheet()->SetCellValue($numero_hoja[$i_h],$i_h+1);
                  $objPHPExcel->getActiveSheet()->SetCellValue($nombre_asignatura_hoja[$i_h], $nombre_asignatura[$ca]); 
                    $i_h++;
                } // CIERRE IF BASICA Y TERCER CICLO.
                /*if($codigo_grado >= "06" || $codigo_grado <= "09")
                {
                  if($codigo_area[])
                  $objPHPExcel->setActiveSheetIndex(0);
                  $objPHPExcel->getActiveSheet()->SetCellValue($codigo_asignatura_hoja[$i_h], $codigo_asignatura[$ca]);
                  $objPHPExcel->getActiveSheet()->SetCellValue($numero_hoja[$i_h],$i_h+1);
                  $objPHPExcel->getActiveSheet()->SetCellValue($nombre_asignatura_hoja[$i_h], $nombre_asignatura[$ca]); 
                    $i_h++;
                } // CIERRE IF BASICA Y TERCER CICLO.*/
          } // FOR DEL CODIGO grado.
      }
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// CIERRE. DE LOS NOMBRE DE LAS ASIGNATURAS Y SU CODIGO.
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// regresar a la hoja 0        
  $objPHPExcel->setActiveSheetIndex(0);      
// Correlativo, numero de linea.
    $num = 0; $fila_excel = 6;
    while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
    // acumular correlativo y fila.
        $num++; $fila_excel++;
      ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Apellidos (paterno y materno) - nombres.
        $apellidos_nombres = trim(cambiar_de_del_2($row['apellido_alumno']));
        // Apellidos (paterno y materno)
        $apellidos_materno_paterno = trim(cambiar_de_del_2($row['apellidos_alumno']));
        // Nombres
        $nombres = trim(cambiar_de_del_2($row['nombre_completo']));
        // Código Alumno
        $codigo_alumno = trim(($row['id_alumno']));
        // Código Matricula
        $codigo_matricula = trim(($row['codigo_matricula']));
  
        //  IMPRIMIR EL CONTENIDO DE  INFORMACION EN EXCEL. indicadores
            $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel,($codigo_alumno));
            $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel,($codigo_matricula));
            $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel,TRIM($row['codigo_nie']));
            $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel,($apellidos_nombres));  
   }    //  FIN DEL WHILE.
   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
    // Proteger hoja.
			$objPHPExcel->getActiveSheet()->getProtection()->setPassword('1');
  //    $objPHPExcel->getActiveSheet()->getProtection()->setSelectUnlockedCells(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
// Verificar si Existe el directorio archivos.
		$codigo_modalidad = $codigo_bachillerato;
		$nombre_ann_lectivo = $nombre_ann_lectivo;
	// Tipo de Carpeta a Grabar en cuadro de Calificaciones.
		$codigo_destino = 2;
		CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = replace_3("Cuadro de Notas " . "-". $nombre_grado ."-".$nombre_seccion.".xlsx");
// En caso de error.
	try {
    $mensajeError = "Archivo Creado... " . $nombre_archivo;
    // Grabar el archivo.
		$objWriter = new Xlsx($objPHPExcel);
		$objWriter->save($DestinoArchivo.$nombre_archivo);
    // cambiar permisos del archivo antes grabado.
		chmod($DestinoArchivo.$nombre_archivo,07777);
	}catch(Exception $e){
		$respuestaOK = false;
		$mensajeError = "";
		$contenidoOK = "Error - > ".$e;
	}
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
// eXPORTAR POR JSON (DATOS)
echo json_encode($salidaJson);	
?>