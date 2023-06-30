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
    $n_hoja = 3;	// variable para el activesheet.
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
                $objPHPExcel = $objReader->load($origen.".xlsx");

              }
            break;
        case "13":
              if($codigo_grado == "4P"){
                $objPHPExcel = $objReader->load($origen."INSTRUMENTOS DESARROLLO ESTANDAR.xlsx");
                 $NombreEstudiante = array("D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z",
                 "AA","AB","AC","AD","AE","AF","AG","AH","AI","AJ","AK","AL","AM","AN","AO","AP","AQ");
                   // PARA LA ALERTA TEMPRANA.
                break;
                  }
              if($codigo_grado == "5P"){
                $objPHPExcel = $objReader->load($origen.".xlsx");
               
              }
              if($codigo_grado == "6P"){
                $objPHPExcel = $objReader->load($origen.".xlsx");
                
              }
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
              ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura,
              asig.codigo_area as codigo_area_asignatura, asig.codigo_area_dimension, cat_area_di.descripcion as descripcion_area_dimension,
              cat_area_subdi.codigo, cat_area_subdi.descripcion as descripcion_area_subdimension,
              cat_area.codigo, cat_area.descripcion as descripcion_area
                FROM a_a_a_bach_o_ciclo aaa
								INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado
								INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura
                INNER JOIN catalogo_area_dimension cat_area_di ON cat_area_di.codigo = asig.codigo_area_dimension
                INNER JOIN catalogo_area_subdimension cat_area_subdi ON cat_area_subdi.codigo =  asig.codigo_area_subdimension
                INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
									WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
									ORDER BY asig.ordenar";
    }
  
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	    $result_consulta = $dblink -> query($query_asig);
      $fila_asignatura = $result_consulta -> rowCount();
// colocar informacion en la hoja seleccionada.
	$codigo_asignatura_matriz = array(); $nombre_asignatura_matriz = array();
  $nombre_asignatura = array(); $codigo_asignatura = array(); $codigo_area_asignatura = array();
  $nombre_area = array(); $nombre_area_di = array(); $nombre_area_di_subdi = array();
//  CAPTURA DE DATOS A ARRAY PARA PARVULARIA, BASICA Y MEDIA.
    // PARVULARIA
      if($codigo_grado =="I3" || $codigo_grado =="4P" || $codigo_grado =="5P" || $codigo_grado =="6P")
      {
        while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
          {
            $nombre_asignatura[] =trim($row['nombre_asignatura']);
            $codigo_asignatura[] = trim($row['codigo_asignatura']);
            $codigo_area[] = trim($row['codigo_area_asignatura']);
            // Descripciòn Area, Dimensiòn y Subdimensión.
            $nombre_area[] = trim($row['descripcion_area']);
            $nombre_area_di[] = trim($row['descripcion_area_dimension']);
            $nombre_area_di_subdi[] = trim($row['descripcion_area_subdimension']);
          }
      }

// Construir y Asignar codigo asignatura y nombre para hoja de calculo.
    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // consulta a la tabla para optener la nomina.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Get the current sheet with all its newly-set style properties
          /*  if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado =="5P")
                {
                    $objWorkSheetBase = $objPHPExcel->getSheet(3);
                }else{
                  $objWorkSheetBase = $objPHPExcel->getSheet(0);
                }*/
// Indicamos que se pare en la hoja uno del libro
   // $objPHPExcel->setActiveSheetIndex($n_hoja);
    //$objPHPExcel->getActiveSheet($n_hoja)->setTitle(cambiar_de_del($print_grado).' '.$print_seccion);
    //$n_hoja++;    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////
			//$objPHPExcel->getActiveSheet()->SetCellValue('A2', $nombre_docente_en_excel);
      $nombre_bachillerato_en_excel = "";
      $informacion_del_grado = $nombre_modalidad . " " . $nombre_grado . " " . $nombre_seccion . " " . $nombre_ann_lectivo;
			$objPHPExcel->getActiveSheet()->SetCellValue('D4', $informacion_del_grado);
      // BUSCAR POR EL CODIGO DEL GRADO.
        if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado =="5P" || $codigo_grado =="6P")
        {      
            $objPHPExcel->getActiveSheet()->SetCellValue('D2', $_SESSION["institucion"]);
            $objPHPExcel->getActiveSheet()->SetCellValue('AF2',$_SESSION["codigo_institucion"]);
        }
//
//  ROTULACIÓN PARA LA HOJA DE ALERTA TEMPRANA PARVULARIA
//
if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado == "5P")
  {
    // $objPHPExcel->setActiveSheetIndex(1);
    // $objPHPExcel->getActiveSheet()->SetCellValue('F2', "ALERTAS TEMPRANAS");
    // $objPHPExcel->getActiveSheet()->SetCellValue('C1', $_SESSION["institucion"]);
    // $objPHPExcel->getActiveSheet()->SetCellValue('C2', "Código: " . $_SESSION["codigo_institucion"]);
    // $objPHPExcel->setActiveSheetIndex(0);
  }
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// recorrer la array para extraer los datos. DE LOS NOMBRE DE LAS ASIGNATURAS Y SU CODIGO.
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// Variables
    $i_h_a = 0; $i_h = 0; $fila = 11;
// si existe la variable  para PARVULARIA.
    if(isset($codigo_asignatura))
    {
          for($ca=0;$ca<count($codigo_asignatura);$ca++){
            if($codigo_grado == "I3" || $codigo_grado =="4P" || $codigo_grado == "5P")
                {
                  if($codigo_area[$ca] == "09" || $codigo_area[$ca] == "08"){
                    $objPHPExcel->setActiveSheetIndex(1);                  
                      //$objPHPExcel->getActiveSheet()->SetCellValue($codigo_asignatura_hoja_alerta[$i_h_a], $codigo_asignatura[$ca]);
                      //$objPHPExcel->getActiveSheet()->SetCellValue($numero_hoja_alerta[$i_h_a],$i_h_a+1);
                      //$objPHPExcel->getActiveSheet()->SetCellValue($nombre_asignatura_hoja_alerta[$i_h_a], $nombre_asignatura[$ca]); 
                        $i_h_a++;
                  }else{
                    // ARMAR VARIABLES PARA LA DESCRIPCION DEL AREA, DIMENSION Y SUBDIMENSION.
                    $nombre_area_di_subdi = $nombre_area[$ca] ."/". $nombre_area_di[$ca] . "/" . $nombre_area_di_subdi[$ca];
                    $objPHPExcel->setActiveSheetIndex(3);
                    $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila, $nombre_area_di_subdi);
                    $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila, $codigo_asignatura[$ca]);
                    $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila, $nombre_asignatura[$ca]);
                      $fila++;
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

          } // FOR DEL CODIGO grado.
      }
//////////////////////////////////////////////////////////////////////////////////////////////////////////
// CIERRE. DE LOS NOMBRE DE LAS ASIGNATURAS Y SU CODIGO.
//////////////////////////////////////////////////////////////////////////////////////////////////////////

// regresar a la hoja 0        
  $objPHPExcel->setActiveSheetIndex(3);      
// Correlativo, numero de linea.
    $num = 0; $fila_excel = 8; $NumeroEnColumna = 1;
    while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
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
            $objPHPExcel->getActiveSheet()->SetCellValue($NombreEstudiante[$num]."7",$NumeroEnColumna);  
            $objPHPExcel->getActiveSheet()->SetCellValue($NombreEstudiante[$num]."8",($apellidos_nombres));  
            $objPHPExcel->getActiveSheet()->SetCellValue($NombreEstudiante[$num]."9",($codigo_alumno));
            $objPHPExcel->getActiveSheet()->SetCellValue($NombreEstudiante[$num]."10",($codigo_matricula));
            //$objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel,TRIM($row['codigo_nie']));

            // acumular correlativo y fila.
              $num++; $fila_excel++; $NumeroEnColumna++;
   }    //  FIN DEL WHILE.
   ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
   
    // Proteger hoja.
			$objPHPExcel->getActiveSheet()->getProtection()->setPassword('1');
      //$objPHPExcel->getActiveSheet()->getProtection()->setSelectUnlockedCells(true);
			$objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
// Verificar si Existe el directorio archivos.
		$codigo_modalidad = $codigo_bachillerato;
		$nombre_ann_lectivo = $nombre_ann_lectivo;
	// Tipo de Carpeta a Grabar en cuadro de Calificaciones.
		$codigo_destino = 2;
		CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
	// Nombre del archivo.
		$nombre_archivo = replace_3("Parvularia Estándar de Desarrollo  " . "-". $nombre_grado ."-".$nombre_seccion.".xlsx");
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