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
       $codigo_all_ = $codigo_bachillerato . $codigo_grado . $codigo_seccion . $codigo_annlectivo;
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
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
    if($nombre_modalidad == "Primer Ciclo" || $nombre_modalidad == "Segundo Ciclo"){
        $objPHPExcel = $objReader->load($origen."CUADRO DE REGISTRO DE EDUCACION BASICA.xlsx");
    }else{
        $objPHPExcel = $objReader->load($origen."CUADRO DE REGISTRO DE EDUCACION BASICA III.xlsx");
    }
    // OBTENER EL NOMBRE DEL DOCENTE ENCARGADO.
    $query_encargado = "SELECT eg.id_encargado_grado, eg.encargado, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, eg.codigo_docente, bach.nombre, gann.nombre, sec.nombre, ann.nombre
                FROM encargado_grado eg
                INNER JOIN personal p ON eg.codigo_docente = p.id_personal
				INNER JOIN bachillerato_ciclo bach ON eg.codigo_bachillerato = bach.codigo
				INNER JOIN ann_lectivo ann ON eg.codigo_ann_lectivo = ann.codigo
				INNER JOIN grado_ano gann ON eg.codigo_grado = gann.codigo
				INNER JOIN seccion sec ON eg.codigo_seccion = sec.codigo
					WHERE btrim(bach.codigo || gann.codigo || sec.codigo || ann.codigo) = '".$codigo_all_."' and eg.encargado = 't' ORDER BY p.nombres";
    $result_encargado = $db_link -> query($query_encargado);
//  Nombre del Encargado.
    $nombre_encargado = '';
    while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
    {
            $nombre_encargado = trim($rows_encargado['nombre_docente']);
            $codigo_docente = trim($rows_encargado['codigo_docente']);
    }
    // NOMBRE DIRECTOR.
    $nombre_director =  $_SESSION['nombre_director'];
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Get the current sheet with all its newly-set style properties
    $objWorkSheetBase = $objPHPExcel->getSheet(0); 
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex($n_hoja);
    //$objPHPExcel->getActiveSheet($n_hoja)->setTitle(cambiar_de_del($print_grado).' '.$print_seccion);
    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Time zone.
    //echo date('H:i:s') . " Set Encabezado"."<br />";
//Escribimos en la hoja en la celda e3. los datos del bachillerato, grado, sección, año lectivo, etc.
    $objPHPExcel->getActiveSheet()->SetCellValue('B2', $nombre_grado);
    $objPHPExcel->getActiveSheet()->SetCellValue('B3', $nombre_seccion);
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', $nombre_modalidad);
    $objPHPExcel->getActiveSheet()->SetCellValue('B18', $nombre_encargado);
    $objPHPExcel->getActiveSheet()->SetCellValue('B19', $nombre_director);
// Indicamos que se pare en la hoja uno del libro
    $n_hoja++;    
    $objPHPExcel->setActiveSheetIndex($n_hoja);
// Correlativo, numero de linea.
    $num = 0; $fila_excel = 1;
    while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
    // acumular correlativo y fila.
        $num++; $fila_excel++;
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Apellidos (paterno y materno) - nombres.
	$apellidos_nombres = trim(cambiar_de_del_2($row['apellido_alumno']));
	// Apellidos (paterno y materno)
        $apellido_paterno = trim($row['apellido_paterno']);
        $apellido_materno = trim($row['apellido_materno']);
        $nombre_completo_2 = mb_strtoupper(trim($row['nombre_completo']),"UTF-8");
	    $apellidos_materno_paterno = trim(cambiar_de_del_2($row['apellidos_alumno']));
    // genero estudiante
        $genero_estudiante = trim($row['genero_estudiante']);
	    $nombres = trim(cambiar_de_del_2($row['nombre_completo']));
  // Código Alumno
        $codigo_alumno = trim(($row['id_alumno']));
        $nombre_grado_seccion = trim(($row['nombre_grado'])) . ' ' . trim(($row['nombre_seccion']));
  // Código Matricula
        $codigo_matricula = trim(($row['codigo_matricula']));
        $genero = mb_strtoupper(trim(($row['genero']),"UTF-8"));
        $nombre_completo = $nombres . " " . $apellidos_materno_paterno;
        //  IMPRIMIR EL CONTENIDO DE  INFORMACION EN EXCEL.
	    //$objPHPExcel->getActiveSheet()->SetCellValue("".$fila_excel, $num);
        // INFORMACION PARA EL CUANDRO DE PROMOCION
        $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel,($apellido_paterno));
        $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel,($apellido_materno));
        $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel,($nombre_completo_2));
        $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel, TRIM($row['codigo_nie']));
        $objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel, $genero);
        //
        // GENERO ESTUDIANTAE
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
   }    //  FIN DEL WHILE.
// Verificar si Existe el directorio archivos.
		$codigo_modalidad = $codigo_bachillerato;
		//$nombre_ann_lectivo = $nombre_ann_lectivo;
	// Tipo de Carpeta a Grabar Cuadro de Calificaciones.
		$codigo_destino = 1;
		CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = replace_3("Cuadro de Promoción " . $codigo_modalidad . "-". $nombre_grado ."-".$nombre_seccion.".xlsx");
        $contenidoOK = "Archivo Creado: " . $nombre_archivo;
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