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
       $codigo_grado = substr($codigo_all,2,2);
       $codigo_seccion = substr($codigo_all,4,2);
       $codigo_annlectivo = substr($codigo_all,6,2);
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato ='Modalidad: '.trim($row['nombre_bachillerato']);
			$nombre_ann_lectivo = trim($row['nombre_ann_lectivo']);
            $print_grado = 'Grado: '. trim($row['nombre_grado']);
			$nombre_grado = trim($row['nombre_grado']);
            $print_seccion = ('Sección: ').trim($row['nombre_seccion']);
			$nombre_seccion = trim($row['nombre_seccion']);
            $print_ann_lectivo = 'Año Lectivo: '.trim($row['nombre_ann_lectivo']);
            $nombre_turno = trim($row['nombre_turno']);
	            break;
            }
// Proceso de la creaciòn de la Hoja de cálculo.
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
    $objPHPExcel = $objReader->load($origen."Formato - Caracterizacion - 2023.xlsx");
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Get the current sheet with all its newly-set style properties
    $objWorkSheetBase = $objPHPExcel->getSheet(0); 
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex($n_hoja);
    //$objPHPExcel->getActiveSheet($n_hoja)->setTitle(cambiar_de_del($print_grado).' '.$print_seccion);
    $n_hoja++;    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Time zone.
    //echo date('H:i:s') . " Set Encabezado"."<br />";
//Escribimos en la hoja en la celda e3. los datos del bachillerato, grado, sección, año lectivo, etc.
   // $objPHPExcel->getActiveSheet()->SetCellValue('A1', $print_bachillerato);
    //$objPHPExcel->getActiveSheet()->SetCellValue('A2', $print_grado);
    //$objPHPExcel->getActiveSheet()->SetCellValue('C2', $print_seccion);
    //$objPHPExcel->getActiveSheet()->SetCellValue('D2', $print_ann_lectivo);
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
    // genero estudiante
    $genero_estudiante = trim($row['genero_estudiante']);
	$nombres = trim(cambiar_de_del_2($row['nombre_completo']));
  // Código Alumno
  $codigo_alumno = trim(($row['id_alumno']));
  $nombre_grado_seccion = trim(($row['nombre_grado'])) . ' ' . trim(($row['nombre_seccion']));
  // Código Matricula
  $codigo_matricula = trim(($row['codigo_matricula']));
  // datos de los encargados
  $nombre_encargado = trim(($row['nombres']));
  $dui_encargado = trim(($row['encargado_dui']));
  $telefono_encargado = trim(($row['telefono_encargado']));
  $telefono_estudiante = trim(($row['telefono_alumno']));
  $nombre_parentesco = trim(($row['nombre_tipo_parentesco']));
  $numero_telefono_encargado = trim(($row['telefono_encargado']));
  $direccion = trim(($row['direccion_alumno']));
  $fecha_nacimiento = trim(cambiaf_a_normal(($row['fecha_nacimiento'])));
  $edad = trim(($row['edad']));
  $pn_numero = trim(($row['pn_numero']));
  $pn_tomo = trim(($row['pn_tomo']));
  $pn_libro = trim(($row['pn_libro']));
  $pn_folio = trim(($row['pn_folio']));
  $fecha_nacimiento_encargado = trim(cambiaf_a_normal(($row['encargado_fecha_nacimiento'])));

  //$ = trim(($row['']));
        //  IMPRIMIR EL CONTENIDO DE  INFORMACION EN EXCEL.
	    $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, $num);
        $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel,($codigo_alumno));
        $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel,($codigo_matricula));
        $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel, TRIM($row['codigo_nie']));
        $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel,($apellidos_nombres));
        $objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel,($genero_estudiante));
        $objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel,($nombre_grado));
        $objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel,($nombre_seccion));
        $objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel,($nombre_turno));
        $objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel,($fecha_nacimiento));
        $objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel,($edad));
        $objPHPExcel->getActiveSheet()->SetCellValue("L".$fila_excel,($direccion));
        $objPHPExcel->getActiveSheet()->SetCellValue("M".$fila_excel, $telefono_estudiante);

        // VALIDATION LIST COLUMNA n7;N67
        $validation = $objPHPExcel->getActiveSheet()->getCell('N7')
            ->getDataValidation();
        $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
        $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Lista');
        $validation->setPrompt('Por Favor Seleccion.');
        $validation->setFormula1('=\'Datos\'!$A$2:$A$14');
        for ($i=8; $i < 68 ; $i++) { 
            $objPHPExcel->getActiveSheet()->getCell('N'.$i)->setDataValidation(clone $validation);
        }

        // VALIDATION LIST COLUMNA Q7;Q67 (SI O NO)
        $validation = $objPHPExcel->getActiveSheet()->getCell('Q7')
            ->getDataValidation();
        $validation->setType( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST );
        $validation->setErrorStyle( \PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION );
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Input error');
        $validation->setError('Value is not in list.');
        $validation->setPromptTitle('Lista');
        $validation->setPrompt('Por Favor Seleccion.');
        $validation->setFormula1('=\'Datos\'!$B$2:$B$3');
        for ($i=7; $i < 68 ; $i++) { 
            $objPHPExcel->getActiveSheet()->getCell('Q'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AD'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AF'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AG'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('Ah'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AI'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AK'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AO'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AS'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('AU'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('BB'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('BC'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('BD'.$i)->setDataValidation(clone $validation);
            $objPHPExcel->getActiveSheet()->getCell('BE'.$i)->setDataValidation(clone $validation);
        }
        
        // datos del encargado nombre y n.º de dui.
//        $objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel,($nombre_encargado));
  //      $objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel,($fecha_nacimiento_encargado));
    //    $objPHPExcel->getActiveSheet()->SetCellValue("R".$fila_excel,($dui_encargado));
      //  $objPHPExcel->getActiveSheet()->SetCellValue("S".$fila_excel,($nombre_parentesco));
        //$objPHPExcel->getActiveSheet()->SetCellValue("T".$fila_excel,($numero_telefono_encargado));
        //
        // GENERO ESTUDIANTAE
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
   }    //  FIN DEL WHILE.
// Verificar si Existe el directorio archivos.
		$codigo_modalidad = $codigo_bachillerato;
		$nombre_ann_lectivo = $nombre_ann_lectivo;
	// Tipo de Carpeta a Grabar Cuadro de Calificaciones.
		$codigo_destino = 1;
		CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = replace_3($codigo_modalidad . "-". $nombre_grado ."-".$nombre_seccion." - Caracterizacion.xlsx");
        $contenidoOK = "Archivo Caracterización Creado: " . $nombre_archivo;
	try {
    // Grabar el archivo.
		$objWriter = new Xlsx($objPHPExcel);
		$objWriter->save($DestinoArchivo.$nombre_archivo);
    // cambiar permisos del archivo antes grabado.
		chmod($DestinoArchivo.$nombre_archivo,07777);
	}catch(Exception $e){
		$respuestaOK = false;
		$mensajeError = "No Save";
		$contenidoOK = "Error - > ".$e;
	}
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);	
?>