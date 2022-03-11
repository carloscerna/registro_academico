<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
	include($path_root."/registro_academico/includes/funciones_2.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// variables y consulta a la tabla.
$codigo_ann_lectivo = $_REQUEST["codigo_annlectivo"];
$cc_ann_lectivo = $_REQUEST["codigo_annlectivo"];
$db_link = $dblink;
$codigo_all_indicadores = array(); $nombre_grado = array(); $nombre_seccion = array(); $nombre_modalidad = array(); $nombre_ann_lectivo = array();
$codigo_grado_tabla = array(); $codigo_grado_ = array(); $nombre_modalidad_consolidad = array(); $nombre_turno = array(); $nombre_turno_consolidado = array();

// buscar la consulta y la ejecuta.
consultas(13,0,$codigo_ann_lectivo,'','','',$db_link,'');
//  captura de datos para información individual de grado y sección.
while($row = $result -> fetch(PDO::FETCH_BOTH))
   {
       $print_bachillerato = utf8_decode(''.trim($row['nombre_bachillerato']));
       $print_grado = utf8_decode(''.trim($row['nombre_grado']));
       $print_seccion = utf8_decode(''.trim($row['nombre_seccion']));
       $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
       // Variables
       $codigo_modalidad = trim($row[0]);
       $codigo_grado = trim($row['codigo_grado']);
       $codigo_seccion = trim($row['codigo_seccion']);
       $codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);
       $codigo_turno = trim($row['codigo_turno']);
       // ArrayÇ
       $codigo_grado_[] = trim($row['codigo_grado']);
       $nombre_grado[] = utf8_decode($row['nombre_grado']);
       $nombre_seccion[] = $row['nombre_seccion'];
       $nombre_modalidad[] = $row['nombre_bachillerato'];
       $nombre_ann_lectivo[] = $row['nombre_ann_lectivo'];
       $nombre_turno[] = $row['nombre_turno'];
       // modalidad, grado, sección, año lectivo.
       $codigo_all_sobreedad[] = $codigo_modalidad . $codigo_grado . $codigo_seccion . $codigo_ann_lectivo . $codigo_turno;
   }

   //print_r($codigo_all_sobreedad);
// Inicializamos variables de mensajes y JSON
    $respuestaOK = true;
    $mensajeError = "No se puede ejecutar la aplicación";
    $contenidoOK = "";

// Proceso de la creaciòn de la Hoja de cálculo.
    $n_hoja = 0;	// variable para el activesheet.
    //consultas(4,0,$codigo_all,'','','',$db_link,'');
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
    $objPHPExcel = $objReader->load($origen."Formato - Listado SOBREEDAD.xlsx");
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
// Correlativo, numero de linea.
    $num = 0; $fila_excel = 5; $numero_grado_sobreedad = 0;
    $codigo_grado_sobreedad = array("01","02","03","04","05","06","07","08","09","10","11","12");
		for($jh=0;$jh<=count($codigo_all_sobreedad)-1;$jh++)
		{
            // verificar codigo grado
                    //
                        $codigo_grado_ok = $codigo_grado_sobreedad[$numero_grado_sobreedad];
                    //  EJECUTAR CONSULTA.
                        consultas(4,0,$codigo_all_sobreedad[$jh],'','','',$db_link,'');
                    //  RECORRER LA CONSULTA.
                        while($listado_sobreedad = $result -> fetch(PDO::FETCH_BOTH))
                        {
                            $apellidos_nombres = trim(cambiar_de_del_2($listado_sobreedad['apellido_alumno']));
                            $nie = trim($listado_sobreedad["codigo_nie"]);
                            $fecha_nacimiento = cambiaf_a_normal(trim($listado_sobreedad["fecha_nacimiento"]));
                            $edad = trim($listado_sobreedad["edad"]);                            
                            $nombre_grado = trim($listado_sobreedad["nombre_grado"]);     
                            $codigo_grado = trim($listado_sobreedad["codigo_grado"]);                            
                            $nombre_seccion = trim($listado_sobreedad["nombre_seccion"]);                            
                            $nombre_encargado = trim($listado_sobreedad["nombres"]);                            
                            $dui_encargado = trim($listado_sobreedad["encargado_dui"]);
                            $nombre_parentesco = trim($listado_sobreedad["nombre_tipo_parentesco"]);
                            $telefono_encargado = trim($listado_sobreedad["encargado_telefono"]);
                            $direccion_encargado = trim($listado_sobreedad["encargado_direccion"]);
                            $nombre_turno = trim($listado_sobreedad["nombre_turno"]);               

                            // Imprimir valores si la sobreedad es mayor dependiendo del grado.
                            if($codigo_grado >= "01" and $codigo_grado <= "12"){
                                calcular_sobreedad_($edad, $codigo_grado);
                                if($sobreedad == "t"){
                                    //  IMPRIMIR EL CONTENIDO DE  INFORMACION EN EXCEL.
                                    $num++;
                                    $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, $num);
                                    $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nie);
                                    $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel,($apellidos_nombres));
                                    $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel,($fecha_nacimiento));
                                    $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel,($edad));
                                    $objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel,($nombre_grado));
                                    $objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel,($nombre_seccion));
                                    $objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel,($nombre_turno));
                                    //SOBREEDAD
                                    calcular_sobreedad_escala($edad, $codigo_grado);
                                        if($sobreedad_escala == 1){
                                            $objPHPExcel->getActiveSheet()->SetCellValue("I".$fila_excel,"X");
                                        }
                                        if($sobreedad_escala == 2){
                                            $objPHPExcel->getActiveSheet()->SetCellValue("J".$fila_excel,"X");
                                        }
                                        if($sobreedad_escala == 3){
                                            $objPHPExcel->getActiveSheet()->SetCellValue("K".$fila_excel,"X");
                                        }
                                        if($sobreedad_escala == 4){
                                            $objPHPExcel->getActiveSheet()->SetCellValue("L".$fila_excel,"X");
                                        }
                                    //DATOS DEL ENCARGADO
                                    // datos del encargado nombre y n.º de dui.
                                        $objPHPExcel->getActiveSheet()->SetCellValue("M".$fila_excel,($nombre_encargado));
                                        $objPHPExcel->getActiveSheet()->SetCellValue("N".$fila_excel,($dui_encargado));
                                        $objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel,($nombre_parentesco));
                                        $objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel,($telefono_encargado));
                                        $objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel,($direccion_encargado));
                                    // aumentar fila excel
                                        $fila_excel++;
                                }
                            }
                        }
        }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
// Verificar si Existe el directorio archivos.
		$nombre_ann_lectivo = $nombre_ann_lectivo;
	// Tipo de Carpeta a Grabar Cuadro de Calificaciones.
		$codigo_destino = 1;
		CrearDirectorios($path_root,"","",$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = replace_3("Informe Sobreedad ".$print_ann_lectivo.".xlsx");
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
?>