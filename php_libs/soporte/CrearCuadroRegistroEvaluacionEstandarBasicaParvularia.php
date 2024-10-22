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
    $codigo_all__ = $codigo_bachillerato . $codigo_annlectivo;
// Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME,'es_SV');
// CREAR MATRIZ DE MESES Y FECH.
    $meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
//Crear una línea. Fecha con getdate();
    $hoy = getdate();
    $NombreDia = $hoy["wday"];  // dia de la semana Nombre.
    $dia = $hoy["mday"];    // dia de la semana
    $mes = $hoy["mon"];     // mes
    $año = $hoy["year"];    // año
    $total_de_dias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, $año);
    $NombreMes = $meses[(int)$mes - 1];
// definimos 2 array uno para los nombre de los dias y otro para los nombres de los meses
    $nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    $fecha = convertirTexto("Santa Ana, $nombresDias[$NombreDia] $dia de $nombresMeses[$mes] de $año");
    setlocale(LC_MONETARY,"es_ES");
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
    if($codigoNivel == '14' || $codigoNivel == '13' || $codigoNivel == '16'){
        $objPHPExcel = $objReader->load($origen."CUADRO EDUCACION BASICA ESTANDAR DE DESARROLLO.xlsx");
        $EstudianteIndicadorFinal = ["D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
            "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ","AR","AS","AT","AU","AV","AW","AX","AY","AZ",
            "BA","BB","BC","BD","BE","BF","BG","BH","BI","BJ","BK","BL","BM","BN","BO","BP","BQ","BR","BS","BT","BU","BV","BW","BX","BY","BZ",
            "CA","CB","CC","CD","CE","CF","CG","CH","CI","CJ","CK","CL","CM","CN"];
    }else if($codigoNivel == '116'){
        $objPHPExcel = $objReader->load($origen."CUADRO REGISTRO DE EVALUACION EDUCACION BASICA SEGUNDO Y TERCERO FOCALIZADO.xlsx");
        $EstudianteIndicadorFinal = ["D","E","F","G","H","I","J"];
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
    $numeroDeAcuerdo = $_SESSION['numero_acuerdo'];
    $diaEntrega = $_SESSION['dia_entrega'];
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
    $objPHPExcel->getActiveSheet()->SetCellValue('H2', $nombreInstitucion);
    $objPHPExcel->getActiveSheet()->SetCellValue('C2', $nombreCodigoInstitucion);
    //
    $objPHPExcel->getActiveSheet()->SetCellValue('C3', $nombreNivel);
    $objPHPExcel->getActiveSheet()->SetCellValue('C4', $nombreGrado);
    //
    $objPHPExcel->getActiveSheet()->SetCellValue('F4', $nombreSeccion);
    $objPHPExcel->getActiveSheet()->SetCellValue('K4', $nombreTurno);
    $objPHPExcel->getActiveSheet()->SetCellValue('S4', $nombreAñoLectivo);

    $objPHPExcel->getActiveSheet()->SetCellValue('X2', $nombreDepartamento);
    $objPHPExcel->getActiveSheet()->SetCellValue('X3', $nombreMunicipio);
    $objPHPExcel->getActiveSheet()->SetCellValue('R9', $numeroDeAcuerdo);
    $objPHPExcel->getActiveSheet()->SetCellValue('C63', $fecha);
//
    $objPHPExcel->getActiveSheet()->SetCellValue('B67', $nombreDirector);
    $objPHPExcel->getActiveSheet()->SetCellValue('G67', $nombreEncargado);
// Indicamos que se pare en la hoja uno del libro
    //$n_hoja++;    
    $objPHPExcel->setActiveSheetIndex(0);
// Correlativo, numero de linea.
    $num = 0; $fila_excel = 12; $numIndicadorFinal = 0; $filaComponente = 11;
    $NombreArea = []; // matriz para el conteo de elementos de cada area.
// Verificar los estudiantes uno po uno.
    for ($EstudiantesFor=0; $EstudiantesFor < count($EstudianteCodigo); $EstudiantesFor++) { 
        consultas_alumno(2,0,"",$EstudianteCodigo[$EstudiantesFor],$EstudianteCodigoMatricula[$EstudiantesFor],$codigo_all__,$db_link,"");
            while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                // COLOCAR EL NOMBRE DE LOS COMPONENTES DE ESTUDIO.
                // CUANDO $ESTUDIANTESFOR = 0
                    if($EstudiantesFor == 0){
                        $NombreArea[] = trim(($row['nombre_area'])); //
                        $NombreAreaDimension = trim(($row['descripcion_area_dimension'])); //
                        $NombreAsignatura = cambiar_de_del_2(trim($row['n_asignatura'])); // Nombre Area Dimensión.
                        // condicinar el Area Demnsión cuando sea igual.
                            if($NombreAreaDimension == "Ninguno"){
                                $NombreAreaDimension = "";
                                $NombreComponente = $NombreAsignatura;
                            }else{
                                $NombreComponente = $NombreAreaDimension . " - " . $NombreAsignatura;
                            }
                        // Escribir nombre del componente.
                        $objPHPExcel->getActiveSheet()->SetCellValue($EstudianteIndicadorFinal[$numIndicadorFinal].$filaComponente, $NombreComponente);

                    }
                // RECORRER LA INFORMACIÓN PARA COLOCAR EL NOMBRE DEL ESTUDIANTE E INDICADOR FINAL.
                    if($codigoNivel == '16'){
                        $indicadorFinal = ucwords(trim($row['indicador_p_p_2']));  // Indicador Final.
                        switch ($indicadorFinal) {
                            case 'Sobresaliente':
                                $indicadorFinal = "SO";  // Indicador Final.
                                break;
                            case 'Satisfactorio':
                                $indicadorFinal = "SA";  // Indicador Final.
                                break;
                            case 'En proceso':
                                $indicadorFinal = "E/P";  // Indicador Final.
                                break;
                            case 'No lo hace':
                                $indicadorFinal = "N/H";  // Indicador Final.
                                break;
                        }
                    }else{
                        $indicadorFinal = trim($row['indicador_p_p_2']);  // Indicador Final.
                    }
                //
                if($num == 0){
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $CodigoNie = TRIM($row['codigo_nie']);  // Codigo Nie.
                    $ApellidosNombres = trim(cambiar_de_del_2($row['apellido_alumno'])); // Apellidos (paterno y materno) - nombres.
                    // INFORMACION PARA EL CUADRO DE REGISTRO DE EVALUACIÓN.
                    $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $CodigoNie);
                    $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel,$ApellidosNombres);
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $objPHPExcel->getActiveSheet()->SetCellValue($EstudianteIndicadorFinal[$numIndicadorFinal].$fila_excel,($indicadorFinal));
                }else{
                    $objPHPExcel->getActiveSheet()->SetCellValue($EstudianteIndicadorFinal[$numIndicadorFinal].$fila_excel,($indicadorFinal));
                }
                // Total de indicadores o asignaturas.
                    if($num == $total_asignaturas-1){
                        $numIndicadorFinal = 0;
                        $fila_excel++;
                        $num = 0;
                    }else{
                        $numIndicadorFinal++;
                        // acumular correlativo y fila.
                        $num++; 
                    }
            }    //  FIN DEL WHILE.
    }   // FIN DEL FOR.
    // DESCRIPCION DE CADA AREA APARTIR DE LA MATRIZ NombreArea.
        if($codigoGrado == "4P" || $codigoGrado == "5P"){
            $buscarArea = ["Alertas","DESARROLLO PERSONAL Y SOCIAL","MOTORA","COMUNICACION Y EXPRESIÓN","RELACION CON EL MEDIO","ALERTAS"];
        }else if($codigoGrado == '6P'){
            $buscarArea = ["DESARROLLO PERSONAL Y SOCIAL","MOTORA","COMUNICACION Y EXPRESIÓN","RELACION CON EL MEDIO","ALERTAS"];
        }else{
            $buscarArea = ["LENGUAJE","MATEMÁTICA","CIENCIA Y TECNOLOGÍA","ESTUDIOS SOCIALES","EDUCACIÓN FÍSICA","EDUCACIÓN ARTÍSTICA","INGLES"];
        }

        $CeldaCombinada = []; $UltimaCelda = 0; $PrimerCelda = [];
    // a partir de la fila 10 columna D.
        $filaArea = 10;
    // Contar los valores repetidos
        $conteo = array_count_values($NombreArea);
        //var_dump($conteo);
        //var_dump($conteo);
        for ($Pi=0; $Pi < count($buscarArea) ; $Pi++) {
            if (array_key_exists($buscarArea[$Pi], $conteo)) {
//                print "elemento $Pi: " . $conteo[$buscarArea[$Pi]];
                switch ($Pi) {
                    case 0: // AREA
                        $CeldaCombinada[] = $EstudianteIndicadorFinal[0] . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] - 1] . $filaArea;
                        //
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"]];
                        $PrimerCelda[] = "D";
                        $PrimerCelda[] = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"]];
                        break;
                    case 1: // AREA
                        $CeldaCombinada[] = $UltimaCelda . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] - 1] . $filaArea;
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]]];
                        $PrimerCelda[] = $UltimaCelda;
                        break;
                    case 2: // AREA
                        $CeldaCombinada[] = $UltimaCelda . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] - 1] . $filaArea;
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]]];
                        $PrimerCelda[] = $UltimaCelda;
                        break;
                    case 3: // AREA
                        $CeldaCombinada[] = $UltimaCelda . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] - 1] . $filaArea;
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]]];
                        $PrimerCelda[] = $UltimaCelda;
                        break;
                    case 4: // AREA
                        $CeldaCombinada[] = $UltimaCelda . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] + $conteo[$buscarArea[$Pi-4]] - 1] . $filaArea;
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] + $conteo[$buscarArea[$Pi-4]]];
                        $PrimerCelda[] = $UltimaCelda;
                        break;
                    case 5: // AREA
                        $CeldaCombinada[] = $UltimaCelda . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] + $conteo[$buscarArea[$Pi-4]] + $conteo[$buscarArea[$Pi-5]] - 1] . $filaArea;
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] + $conteo[$buscarArea[$Pi-4]] + $conteo[$buscarArea[$Pi-5]]];
                        $PrimerCelda[] = $UltimaCelda;
                        break;
                    case 6: // AREA
                        $CeldaCombinada[] = $UltimaCelda . $filaArea . ":" . $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] + $conteo[$buscarArea[$Pi-4]] + $conteo[$buscarArea[$Pi-5]] + $conteo[$buscarArea[$Pi-6]] - 1] . $filaArea;
                        $UltimaCelda = $EstudianteIndicadorFinal[$conteo["$buscarArea[$Pi]"] + $conteo[$buscarArea[$Pi-1]] + $conteo[$buscarArea[$Pi-2]] + $conteo[$buscarArea[$Pi-3]] + $conteo[$buscarArea[$Pi-4]] + $conteo[$buscarArea[$Pi-5]] + $conteo[$buscarArea[$Pi-6]]];
                        $PrimerCelda[] = $UltimaCelda;
                        break;
                    default:
                        break;
                }
            }   
        }
  //      var_dump($CeldaCombinada);
   //     var_dump($PrimerCelda);
        //$NumAreaAncho = $conteo["DESARROLLO PERSONAL Y SOCIAL"];
        for ($Ti=0; $Ti < count($CeldaCombinada); $Ti++) { 
            $objPHPExcel->getActiveSheet()->mergeCells($CeldaCombinada[$Ti])->setCellValue($PrimerCelda[$Ti].$filaArea,$buscarArea[$Ti]);    # code...
            $objPHPExcel->getActiveSheet()->getStyle($CeldaCombinada[$Ti])->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle($CeldaCombinada[$Ti])->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // CONTEO PARA EL CUADRO ESTADISTICO.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    consulta_indicadores(19,0,$codigo_all,'','','',$db_link,'');
        global $totalMasculino, $totalFemenino, $totalMasculinoRetirados, $totalFemeninoRetirados, $EstudianteRetirados;
            $objPHPExcel->getActiveSheet()->SetCellValue('W66', $totalMasculino);
            $objPHPExcel->getActiveSheet()->SetCellValue('W67', $totalFemenino);
            //
            $objPHPExcel->getActiveSheet()->SetCellValue('Y66', $totalMasculinoRetirados);
            $objPHPExcel->getActiveSheet()->SetCellValue('Y67', $totalFemeninoRetirados);
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Tipo de Carpeta a Grabar Cuadro de Registro de Evaluación.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$codigo_destino = 5;
		CrearDirectorios($path_root,$nombreAñoLectivo,$codigoNivel,$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = convertirTexto("Cuadro de Regisro de Evaluacion  -  "). $nombreGrado . " - " . $nombreSeccion . ".xlsx";
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