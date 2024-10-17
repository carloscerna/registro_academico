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
  consultas(13,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del grado en general. extrar la información de la cosulta del archivo consultas.php
  global $nombreNivel, $nombreGrado, $nombreSeccion, $nombreTurno, $nombreAñolectivo, $print_periodo, $codigoNivel;
// Proceso de la creaciòn de la Hoja de cálculo.
    $n_hoja = 0;	// variable para el activesheet.
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
//
    if($codigoNivel == '14'){
        $objPHPExcel = $objReader->load($origen."CUADRO EDUCACION BASICA ESTANDAR DE DESARROLLO.xlsx");
        $EstudianteIndicadorFinal = ["D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
            "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","A2","AT","AU","AW","AX","AY","AZ","BA",
        "BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BR","BS","BT","BU","BV","BW","BX","BY","BZ","CA","CB","CC","CD"];
    }else{
       // $objPHPExcel = $objReader->load($origen."CUADRO DE REGISTRO DE EDUCACION BASICA III.xlsx");
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
    $nombreEncargado = '';
    while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
    {
            $nombreEncargado = trim($rows_encargado['nombre_docente']);
            $codigo_docente = trim($rows_encargado['codigo_docente']);
    }
    // NOMBRE DIRECTOR.
    $nombreDirector =  $_SESSION['nombre_director'];
    $nombreInstitucion  = $_SESSION['institucion'];     // Nombre Institución.
    $nombreCodigoInstitucion = $_SESSION['codigo_escuela']; // Código Institucón.
    $nombreDepartamento =  $_SESSION['nombre_departamento'];
    $nombreMunicipio =  $_SESSION['nombre_municipio'];
////////////////////////////////////////////////////////////////////
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
//consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
$query_asig = "SELECT count(*) as total_asignaturas FROM a_a_a_bach_o_ciclo
WHERE btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo) = '".substr($codigo_all,0,4) . substr($codigo_all,6,2) ."'";
// ejecutar la consulta.
$result = $db_link -> query($query_asig);
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
$total_asignaturas = 0;	
while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
{
  $total_asignaturas = trim($row['total_asignaturas']);
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  matriz y query nomina de estudiantes y 
    $EstudianteCodigoMatricula = []; $EstudianteCodigo = [];
    consultas(4,0,$codigo_all,'','','',$db_link,'');
    while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
    {
        $EstudianteCodigo[] = trim($row['id_alumno']);
        $EstudianteCodigoMatricula[] = trim($row['codigo_matricula']);
    }
//  Get the current sheet with all its newly-set style properties
    $objWorkSheetBase = $objPHPExcel->getSheet($n_hoja); 
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex($n_hoja);
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//Escribimos en la hoja en la celda e3. los datos del bachillerato, grado, sección, año lectivo, etc.
    $objPHPExcel->getActiveSheet()->SetCellValue('C3', $nombreNivel);
    $objPHPExcel->getActiveSheet()->SetCellValue('C4', $nombreGrado);
    $objPHPExcel->getActiveSheet()->SetCellValue('F4', $nombreSeccion);
    $objPHPExcel->getActiveSheet()->SetCellValue('K4', $nombreTurno);
    $objPHPExcel->getActiveSheet()->SetCellValue('S4', $nombreAñoLectivo);
    $objPHPExcel->getActiveSheet()->SetCellValue('H2', $nombreInstitucion);
    $objPHPExcel->getActiveSheet()->SetCellValue('C2', $nombreCodigoInstitucion);
    $objPHPExcel->getActiveSheet()->SetCellValue('X2', $nombreDepartamento);
    $objPHPExcel->getActiveSheet()->SetCellValue('X3', $nombreMunicipio);
//
    $objPHPExcel->getActiveSheet()->SetCellValue('B67', $nombreDirector);
    $objPHPExcel->getActiveSheet()->SetCellValue('G67', $nombreEncargado);
// Indicamos que se pare en la hoja uno del libro
    //$n_hoja++;    
    $objPHPExcel->setActiveSheetIndex(0);
// Correlativo, numero de linea.
    $num = 0; $fila_excel = 12; $numIndicadorFinal = 0;
// Verificar los estudiantes uno po uno.
    for ($EstudiantesFor=0; $EstudiantesFor < count($EstudianteCodigo)-1; $EstudiantesFor++) { 
        consultas_alumno(2,0,"",$EstudianteCodigo[$EstudiantesFor],$EstudianteCodigoMatricula[$EstudiantesFor],$codigo_annlectivo,$db_link,"");
            while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                // acumular correlativo y fila.
                $num++; 
                $indicadorFinal = trim($row['indicador_final']);  // Indicador Final.
                if($num == 1){
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $CodigoNie = TRIM($row['codigo_nie']);  // Codigo Nie.
                    $ApellidosNombres = trim(cambiar_de_del_2($row['apellido_alumno'])); // Apellidos (paterno y materno) - nombres.
                    // INFORMACION PARA EL CUADRO DE REGISTRO DE EVALUACIÓN.
                    $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $CodigoNie);
                    $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel,($ApellidosNombres));
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $objPHPExcel->getActiveSheet()->SetCellValue($EstudianteIndicadorFinal[$numIndicadorFinal].$fila_excel,($indicadorFinal));
                }else{
                    $objPHPExcel->getActiveSheet()->SetCellValue($EstudianteIndicadorFinal[$numIndicadorFinal].$fila_excel,($indicadorFinal));
                }
                // Total de indicadores o asignaturas.
                    if($num == $total_asignaturas){
                        $numIndicadorFinal = 0;
                        $fila_excel = 12;
                    }else{
                        $numIndicadorFinal++;
                        $fila_excel++;
                    }
            }    //  FIN DEL WHILE.
    }   // FIN DEL FOR.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Tipo de Carpeta a Grabar Cuadro de Registro de Evaluación.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$codigo_destino = 5;
		CrearDirectorios($path_root,$nombreAñoLectivo,$codigoNivel,$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = convertirTexto("Cuadro de Regisro de Evaluacion  -  $nombreGrado  - $nombreSeccion.xlsx");
        $contenidoOK = "Archivo Creado: $nombre_archivo";
	try {
        // Grabar el archivo.
            $objWriter = new Xlsx($objPHPExcel);
            $objWriter->save("$DestinoArchivo$nombre_archivo");
        // cambiar permisos del archivo antes grabado.
            chmod($DestinoArchivo.$nombre_archivo,07777);
	}catch(Exception $e){
		$respuestaOK = false;
		$mensajeError = "No Save";
		$contenidoOK = "Error - > $e";
	}
// Armamos array para convertir a JSON
    $salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
    echo json_encode($salidaJson);	