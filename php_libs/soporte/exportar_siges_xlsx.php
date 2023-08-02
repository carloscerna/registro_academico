<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/funciones_2.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
      //
  // Establecer formato para la fecha.
  // 
   date_default_timezone_set('America/El_Salvador');
   setlocale(LC_TIME, 'spanish');
	//
    $db_link = $dblink;
    $respuestaOK = false;
    $mensajeError = "";
    $contenidoOK = "";
    $observaciones = "";
    $todasLasAsignaturas = "no";
    $todasLasAsignaturas  = $_REQUEST["TodasLasAsignaturas"];
// buscar la consulta y la ejecuta.
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
    //$objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Creamos un archivo CVS
    //$objWriter = new Xlsx($objPHPExcel);
    //$objWriter->save($path_root."/registro_web/formatos_hoja_de_calculo/05featuredemo.xlsx");
    //$objWriter->save($path_root."/registro_web/formatos_hoja_de_calculo/Formato - Importar Notas SIGES.xlsx");
// Leemos un archivo Excel 2007
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";
    $objPHPExcel = $objReader->load($origen."Formato - Importar Notas SIGES.xlsx");

// Leemos el archivo CVS
  /* $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
   $objPHPExcel = $objReader->load($path_root."/registro_web/formatos_hoja_de_calculo/05featuredemo.xlsx");*/
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex(0);
//Escribimos en la hoja en la celda NIE, CALIFICACION, FECHA, OBSERVACIÓN Y CODIGO ASIGNATURA
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'NIE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Calificacion');
    $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Fecha');
    $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Observacion');
    $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Asignatura');
    $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Nombre del Alumno');
    $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Grado');
    $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Sección');
    
// variables y consulta a la tabla.
      $codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,4) . $_REQUEST["lstannlectivo"];
      $periodo = $_REQUEST["lstperiodo"];
      $codigo_asignatura = substr($_REQUEST["lstasignatura"],0,3);
      $fecha = $_REQUEST["txtfecha"];
      
       // Información Académica.
       $codigo_bachillerato = substr($codigo_all,0,2);
       $codigo_modalidad = substr($codigo_all,0,2);
       $codigo_grado = substr($codigo_all,2,2);
       $codigo_seccion = substr($codigo_all,4,2);
       $codigo_annlectivo = substr($codigo_all,6,2);
       // Evaluador nota para basica y parvularia
       if($codigo_modalidad >= '03' and $codigo_modalidad <="12"){
          if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
          if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
          if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
          if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4";}
          if($periodo == "Periodo 5"){$nota_p_p = "nota_p_p_5";}
       }else{
          if($periodo == "Periodo 1"){$nota_p_p = "indicador_p_p_1";}
          if($periodo == "Periodo 2"){$nota_p_p = "indicador_p_p_2";}
          if($periodo == "Periodo 3"){$nota_p_p = "indicador_p_p_3";}        
          if($periodo == "Alertas"){$nota_p_p = "alertas";}
       }

      // para saber el nombre del grado
       $query = "SELECT codigo, nombre from grado_ano WHERE codigo = '".$codigo_grado."'";
    // ejecutar la consulta.
        $result_grado = $db_link -> query($query);
            while($row = $result_grado -> fetch(PDO::FETCH_BOTH))
        {
           $nombre_grado = (trim($row['nombre']));
        }


 //   $codigo_asignatura = substr($codigo_asignatura[$i],0,3);
 
 if($todasLasAsignaturas == "yes"){
   // agregar CONSULTA PARA EDUCACIÒN PARVULARIA Y BASICA DE ESTANDAR DESARROLLO.
   if($codigo_grado == "I3" || $codigo_grado == "4P" || $codigo_grado == "5P" || $codigo_grado == "6P" || $codigo_grado == "01")
    {
     $query_todas  = "SELECT aaa.codigo_asignacion, aaa.codigo_bach_o_ciclo, aaa.codigo_asignatura, aaa.codigo_ann_lectivo, aaa.codigo_sirai, aaa.codigo_grado, aaa.id_asignacion, aaa.orden,
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
    }else{
       $query_todas = "SELECT DISTINCT ON (aaa.codigo_asignatura) aaa.codigo_asignatura, aaa.codigo_grado, aaa.codigo_sirai, asi.nombre as nombre_asignatura
        from a_a_a_bach_o_ciclo aaa
           INNER JOIN asignatura asi ON asi.codigo = aaa.codigo_asignatura
            WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
            ORDER BY aaa.codigo_asignatura";
            }

    // ejecutar la consulta.
            $result_asignatura = $db_link -> query($query_todas);
                 while($row = $result_asignatura -> fetch(PDO::FETCH_BOTH))
                    {
                      if($codigo_grado == "I3" || $codigo_grado == "4P" || $codigo_grado == "5P" || $codigo_grado == "6P" || $codigo_grado == "01")
                     {
                      
                      $nombre_asignatura = trim($row['nombre_asignatura']);
                      $nombre_asignatura = str_replace(['.', '\\', '/', '*','"',':',","], ' ', $nombre_asignatura);
                      $nombre_asignatura_t[] = trim($nombre_asignatura);
                      $nombre_area_dimension_t[] = trim($row['descripcion_area_dimension']);
                      $nombre_area_subdimension_t[] = trim($row['descripcion_area_subdimension']);
                      $codigo_asignatura_t[] = $row['codigo_asignatura']; 
                     }else{
                      $nombre_asignatura = trim($row['nombre_asignatura']);
                      $nombre_asignatura = str_replace(['.', '\\', '/', '*','"',':',","], ' ', $nombre_asignatura);
                      $nombre_asignatura_t[] = trim($nombre_asignatura);
                      $codigo_asignatura_t[] = trim($row['codigo_asignatura']);
                      
                     }
                    }
 }else{
     // para saber el nombre de la asignatura.
   $query = "SELECT codigo, nombre from asignatura WHERE codigo = '".$codigo_asignatura."'";
    // ejecutar la consulta.
            $result_asignatura = $db_link -> query($query);
                 while($row = $result_asignatura -> fetch(PDO::FETCH_BOTH))
                   {
                    if($codigo_bachillerato >= '03'){
                       $nombre_asignatura = (trim($row['nombre']));
                       //$nombre_asignatura = str_replace(['.', '\\', '/', '*','"','1','3','4','5','6','7','8','9','0'], ' ', $cadena);
                    }else{
                       $nombre_asignatura =trim($row['nombre']);
                       $nombre_asignatura = str_replace(['.', '\\', '/', '*','"',':',","], ' ', $nombre_asignatura);
                    }
                   }
   
   // agregar CONSULTA PARA EDUCACIÒN PARVULARIA Y BASICA DE ESTANDAR DESARROLLO.
   if($codigo_grado == "I3" || $codigo_grado == "4P" || $codigo_grado == "5P" || $codigo_grado == "6P" || $codigo_grado == "01")
    {
    // CONSULTA PARA OBTENER LAS NOTAS DE LOS PERIODOS.
   $query = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
    a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.fecha_nacimiento,
    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, 
    asig.codigo_area,
    sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, asig.codigo_cc, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.alertas, n.nota_final, n.recuperacion,
    round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, aaa.codigo_sirai, aaa.codigo_asignatura, n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final, n.alertas
      FROM alumno a
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
        INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
        INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
        INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
        INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
        INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = n.codigo_asignatura and aaa.codigo_ann_lectivo = '".$codigo_annlectivo."' and aaa.codigo_bach_o_ciclo = '".$codigo_bachillerato."' and aaa.codigo_grado = '".$codigo_grado."' "
        ."INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura ".
        "WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."' and n.codigo_asignatura = '".$codigo_asignatura.
        "' ORDER BY apellido_alumno, n.codigo_asignatura ASC";
    }else{
       $query = "SELECT DISTINCT ON (aaa.codigo_asignatura) aaa.codigo_asignatura, aaa.codigo_grado, aaa.codigo_sirai, asi.nombre as nombre_asignatura
        from a_a_a_bach_o_ciclo aaa
           INNER JOIN asignatura asi ON asi.codigo = aaa.codigo_asignatura
            WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
            ORDER BY aaa.codigo_asignatura";
            }
              // ejecutar la consulta. PARA MOSTRAR LOS RESULTADOS EN PANTALLA.
                  $result = $db_link -> query($query);
 }
 //s
////////////////////////////////////////////////////////////////////////////////////////////////////
// RECORRER L ATABLA Y GUARDAR LOS DATOS.
// Correlativo, numero de linea.
////////////////////////////////////////////////////////////////////////////////////////////////////
if($todasLasAsignaturas == "yes"){    
////
//// REPETIR EL PROCESO DEPENDE DE LAS ASIGNATURAS SELECCIONADAS.
////
//$cantidad_caracteres = 100;
for ($i=0;$i<count($codigo_asignatura_t);$i++)    
  {
    // RECORRE LA MATRIZ CON LOS CODIGOS Y NOMBRES DE LAS ASINGTURAS.
    if($codigo_grado == "I3" || $codigo_grado == "4P" || $codigo_grado == "5P" || $codigo_grado == "6P" || $codigo_grado == "01")
    {
        if(trim($nombre_area_subdimension_t[$i]) == 'Ninguno'){
          $mystring = $nombre_area_dimension_t[$i] . '-'. trim($nombre_asignatura_t[$i]);
        }else{
          $mystring = $nombre_area_dimension_t[$i] . '-' . $nombre_area_subdimension_t[$i] . '-' . trim($nombre_asignatura_t[$i]);
        }
      
    }else{
      $mystring = trim($nombre_asignatura_t[$i]);
    }
    // variables.
    $mystring = str_replace(['\\', '/', '*','"',':',',','(',')'], ' ', $mystring);
    $cantidad_caracteres = strlen($mystring);
    if($cantidad_caracteres >= 10){$cantidad_caracteres = 9000;}
    $findme   = '.';
    $pos = strpos($mystring, $findme);


    // Nótese el uso de ===. Puesto que == simple no funcionará como se espera
    // porque la posición de 'a' está en el 1° (primer) caracter.
      if ($pos === false) {
      //  echo "La cadena '$findme' no fue encontrada en la cadena '$mystring'";
        $codigo_asignatura = $codigo_asignatura_t[$i];
        $nombre_asignatura = trim(substr($mystring,0,$cantidad_caracteres));
      } else {
        if($periodo == "Alertas"){
          $nombre_asignatura = $nombre_asignatura_t[$i];
        }else if ($pos >= 1 && $pos <= 3){
          $nombre_asignatura = trim(substr($mystring,$pos+1,$cantidad_caracteres));
        }else{
          $nombre_asignatura = trim(substr($mystring,0,$cantidad_caracteres));
        }  

        $codigo_asignatura = $codigo_asignatura_t[$i];
        $nombre_area_dimension = $nombre_area_dimension_t[$i];
      }
//    $nombre_asignatura = $nombre_area_dimension_t[$i] . '-' . $nombre_area_subdimension_t[$i] . '-' . trim($nombre_asignatura_t[$i]);
    // CONSULTA PARA OBTENER LAS NOTAS DE LOS PERIODOS.
            $query = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
              a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.fecha_nacimiento,
              am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
              gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, 
              asig.codigo_area,
              sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, asig.codigo_cc, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.alertas, n.nota_final, n.recuperacion,
              round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, aaa.codigo_sirai, aaa.codigo_asignatura, n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final, n.alertas
                FROM alumno a
                  INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                  INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
                  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                  INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
                  INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = n.codigo_asignatura and aaa.codigo_ann_lectivo = '".$codigo_annlectivo."' and aaa.codigo_bach_o_ciclo = '".$codigo_bachillerato."' and aaa.codigo_grado = '".$codigo_grado."' "
                  ."INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura ".
                  "WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."' and n.codigo_asignatura = '".$codigo_asignatura.
                  "' ORDER BY apellido_alumno, n.codigo_asignatura ASC";
                
              // ejecutar la consulta. PARA MOSTRAR LOS RESULTADOS EN PANTALLA.
                  $result = $db_link -> query($query);
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // CUANDO SE HA SELECCIONADO PARA TODAS LAS ASIGNATURA
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $num = 0; $fila_excel = 1;	
        while($row = $result -> fetch(PDO::FETCH_BOTH))
              {
               $nombre_annlectivo = trim($row['nombre_ann_lectivo']);
               $nombre_bachillerato = (trim($row['nombre_bachillerato']));
               $nombre_seccion = trim($row['nombre_seccion']);
               $nombre_completo = (trim($row['apellido_alumno']));
               $codigo_area = trim($row['codigo_area']);
               $nota_p_p_ = $row[$nota_p_p];
               // Variable para saber si la asignatura es de concepto o de calificación.
               $codigo_cc = (trim($row['codigo_cc']));
                 $num++; $fila_excel++; $valor_uno = 1;
               //Escribimos en la hoja en la celda e3. NIE, CALFICIACION, FECHA, OBSERVACION, ASIGNATURA.
                  $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, TRIM($row['codigo_nie']));
                // Evaluar si la asignatura es de CONCEPTO O CALIFICACIÓN.
                        switch ($codigo_cc)
                        {
                          case "01":  // calificación
                              if($nota_p_p_ < 1){
                                $objPHPExcel->getActiveSheet()->getStyle('B'.$fila_excel)->getNumberFormat()->setFormatCode('#,##0.0');
                                $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $valor_uno);
                              }else{
                                $objPHPExcel->getActiveSheet()->getStyle('B'.$fila_excel)->getNumberFormat()->setFormatCode('#,##0.0');
                                $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_p_p_);
                              }
                              break;
                          case "02":  // concepto.
                            if($nota_p_p_ < 1){
                              $nota_concepto = cambiar_concepto($valor_uno);
                              $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_concepto);
                            }else{
                              $nota_concepto = cambiar_concepto($nota_p_p_);
                              $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_concepto);
                            }
                              break;
                          case "03":  // Indicador
                            if(empty($nota_p_p_)){
                              $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, "NE"); 
                            }else{
                              $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_p_p_);                       
                            }
                              break;
                          default:
                            echo "";
                        }
                  // fecha y estilo de la fecha.
                  // Set the number format mask so that the excel timestamp  
                  // will be displayed as a human-readable date/time 
                     $objPHPExcel->getActiveSheet()->getStyle('C'.$fila_excel) 
                         ->getNumberFormat() 
                         ->setFormatCode( 
                         \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY 
                         ); 
                  // convertir A FORMATO DE FECHA
                   $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( 
                              $fecha );
                  // GRABAR EL VALOR
                  $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel, $excelDateValue);
                  // Observaciones
                  $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel, $observaciones);
                  // Código Asignatura
                  $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel, TRIM($row['codigo_sirai']));
                  // nombre completo del alumno por orden de apellidos
                  $objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel, TRIM($row['apellido_alumno']));
                  // NOMBRE GRADO
                  $objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, TRIM($row['nombre_grado']));
                  // NOMBRE SECCION
                  $objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, TRIM($row['nombre_seccion']));
              } // WHILE QUE RECORRE LA BASE DE DATOS.
                    // AJUSTAR AUTOMATICAMENTE EL ANCHO DE LAS COLUMNAS.
                    foreach(range('A','B') as $columnID) {
                     $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                         ->setAutoSize(true);
                    }
                    foreach(range('C','D') as $columnID) {
                     $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                         ->setAutoSize(true);
                    }
                    foreach(range('E','F') as $columnID) {
                     $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                         ->setAutoSize(true);
                    }   
                    foreach(range('G','H') as $columnID) {
                     $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                         ->setAutoSize(true);
                    } 
                     //Grabar el archivo en formato CVS    
                     $objWriter = new Xlsx($objPHPExcel);
                 // Verificar si Existe el directorio archivos.
                   $codigo_modalidad = $codigo_bachillerato;
                   $nombre_ann_lectivo = $nombre_annlectivo;
                  // Tipo de Carpeta a Grabar.
                   $codigo_destino = 3;
                   CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,$periodo);
                  // Crear Carpeta.
                  // Unir Modalidad - Grado y Sección.
                   $nombre_directorio_mgs = replace_3(trim($nombre_bachillerato."-".$nombre_grado."-".$nombre_seccion));
                  // Con el nombre de la modalidad - grado - sección.
                   if(!file_exists($DestinoArchivo . $nombre_directorio_mgs)){
                      // Para Nóminas. Escolanadamente. PERIODO
                         mkdir ($DestinoArchivo . $nombre_directorio_mgs);
                         chmod ($DestinoArchivo . $nombre_directorio_mgs,07777);
                     }
                  // Destino Archivo.
                   //$DestinoArchivo = $DestinoArchivo . $nombre_directorio_mgs;
                  // Nombre del archivo. Sólo el nombre de la Asignatura.
                   if($codigo_area == "09"){
                    $nombre_archivo = htmlspecialchars(substr($nombre_asignatura,0,110) . ".xlsx");
                      // Grabar el archivo.
                      $objWriter->save($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo);
                        // cambiar permisos del archivo antes grabado.
                      chmod($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo,07777);
                        // Condicionar las resuestas y mensajes.
                        $mensajeError .= "<tr class=text-success><td>" .$nombre_asignatura . "</td></tr>";
                   }else if($periodo == "Periodo 1" || $periodo == "Periodo 2" || $periodo == "Periodo 3" || $periodo == "Periodo 4" || $periodo == "Periodo 5"){
                    $nombre_archivo = htmlspecialchars(substr($nombre_asignatura,0,110) . ".xlsx");
                    // Grabar el archivo.
                    $objWriter->save($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo);
                      // cambiar permisos del archivo antes grabado.
                    chmod($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo,07777);
                      // Condicionar las resuestas y mensajes.
                      $mensajeError .= "<tr class=text-success><td>" .$nombre_asignatura . "</td></tr>";//"Codigo Modalidad:   $codigo_modalidad  Nombre asignatura:  $nombre_asignatura ";
                    }
                   $respuestaOK = true;
                   //$mensajeError = $query_todas;
                   //$mensajeError .= "<tr class=text-success><td>" .$nombre_asignatura . "</td></tr>";//"Codigo Modalidad:   $codigo_modalidad  Nombre asignatura:  $nombre_asignatura ";
                   $contenidoOK = $DestinoArchivo;
                    // Armamos array para convertir a JSON
                   $salidaJson = array("respuesta" => $respuestaOK,
                    "mensaje" => $mensajeError,
                    "contenido" => $contenidoOK);   
                   }
}  
else{
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // CUANDO SOLO SE HA SELECCIONADO PARA UNA SOLA ASIGNATURA
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  $num = 0; $fila_excel = 1; $valor_uno = 1;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
              {
               $nombre_annlectivo = trim($row['nombre_ann_lectivo']);
               $nombre_bachillerato = (trim($row['nombre_bachillerato']));
               $nombre_seccion = trim($row['nombre_seccion']);               
               $nombre_completo = (trim($row['apellido_alumno']));               
               $nota_p_p_ = $row[$nota_p_p];
               $codigo_area = trim($row['codigo_area']);
               // Variable para saber si la asignatura es de concepto o de calificación.
               $codigo_cc = (trim($row['codigo_cc']));               
                 $num++; $fila_excel++;     
               //Escribimos en la hoja en la celda e3. NIE, CALFICIACION, FECHA, OBSERVACION, ASIGNATURA.
                  $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, TRIM($row['codigo_nie']));
              // Evaluar si la asignatura es de CONCEPTO O CALIFICACIÓN.
                    switch ($codigo_cc)
                    {
                      case "01":  // calificación
                          if($nota_p_p_ < 1){
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$fila_excel)->getNumberFormat()->setFormatCode('#,##0.0');
                            $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $valor_uno);
                          }else{
                            $objPHPExcel->getActiveSheet()->getStyle('B'.$fila_excel)->getNumberFormat()->setFormatCode('#,##0.0');
                            $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_p_p_);
                          }
                          break;
                      case "02":  // concepto.
                        if($nota_p_p_ < 1){
                          $nota_concepto = cambiar_concepto($valor_uno);
                          $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_concepto);
                        }else{
                          $nota_concepto = cambiar_concepto($nota_p_p_);
                          $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_concepto);
                        }
                          break;
                      case "03":  // Indicador
                        if(empty($nota_p_p_)){
                            if($codigo_area == '09'){
                              $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, "NO"); 
                            }else{
                              $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, "T"); 
                            }
                          
                        }else{
                          $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, $nota_p_p_);                       
                        }
                          break;
                      default:
                        echo "";
                    }

                  // fecha y estilo de la fecha.
                     $objPHPExcel->getActiveSheet()->getStyle('C'.$fila_excel) 
                         ->getNumberFormat() 
                         ->setFormatCode( 
                         \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY 
                         ); 
                  // convertir A FORMATO DE FECHA
                   $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel( 
                              $fecha );
                  // GRABAR EL VALOR
                  $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel, $excelDateValue);              
                  // Observaciones
                  $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel, $observaciones);
                  // Código Asignatura
                  $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel, TRIM($row['codigo_sirai']));
                  // nombre completo del alumno por orden de apellidos
                  $objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel, TRIM($row['apellido_alumno']));
                  // NOMBRE GRADO
                  $objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, TRIM($row['nombre_grado']));
                  // NOMBRE SECCION
                  $objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, TRIM($row['nombre_seccion']));
              } // WHILE QUE RECORRE LA BASE DE DATOS.
                // AJUSTAR AUTOMATICAMENTE EL ANCHO DE LAS COLUMNAS.
                foreach(range('A','B') as $columnID) {
                 $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                     ->setAutoSize(true);
                }
                foreach(range('C','D') as $columnID) {
                 $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                     ->setAutoSize(true);
                }
                foreach(range('E','F') as $columnID) {
                 $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                     ->setAutoSize(true);
                }   
                foreach(range('G','H') as $columnID) {
                 $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
                     ->setAutoSize(true);
                } 
                 //Grabar el archivo en formato CVS    
                 $objWriter = new Xlsx($objPHPExcel);
             // Verificar si Existe el directorio archivos.
               $codigo_modalidad = $codigo_bachillerato;
               $nombre_ann_lectivo = $nombre_annlectivo;
              // Tipo de Carpeta a Grabar.
               $codigo_destino = 3;
               CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,$periodo);
              // Crear Carpeta.
              // Unir Modalidad - Grado y Sección.
               $nombre_directorio_mgs = replace_3(trim($nombre_bachillerato."-".$nombre_grado."-".$nombre_seccion));
              // Con el nombre de la modalidad - grado - sección.
               if(!file_exists($DestinoArchivo . $nombre_directorio_mgs)){
                  // Para Nóminas. Escolanadamente. PERIODO
                     mkdir ($DestinoArchivo . $nombre_directorio_mgs);
                     chmod ($DestinoArchivo . $nombre_directorio_mgs,07777);
                 }
                 
              // Destino Archivo.
               //$DestinoArchivo = $DestinoArchivo . $nombre_directorio_mgs;
              // Nombre del archivo. Sólo el nombre de la Asignatura.
              if($nota_p_p_ == "Alertas" && $codigo_area == '09'){
                $nombre_archivo = htmlspecialchars(substr($nombre_asignatura,0,110) . ".xlsx");
                  // Grabar el archivo.
                $objWriter->save($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo);
                  // cambiar permisos del archivo antes grabado.
                chmod($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo,07777);
                  // Condicionar las resuestas y mensajes.
              }else{
                $nombre_archivo = htmlspecialchars(substr($nombre_asignatura,0,110) . ".xlsx");
                  // Grabar el archivo.
                $objWriter->save($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo);
                  // cambiar permisos del archivo antes grabado.
                chmod($DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo,07777);
                  // Condicionar las resuestas y mensajes.
              }
               
               $respuestaOK = true;
               $mensajeError = $nombre_asignatura . "";//"Codigo Modalidad:   $codigo_modalidad  Nombre asignatura:  $nombre_asignatura ";
               $contenidoOK = $DestinoArchivo;
                // Armamos array para convertir a JSON
               $salidaJson = array("respuesta" => $respuestaOK,
                "mensaje" => $mensajeError,
                "contenido" => $contenidoOK);              
             } // ELSE PARA UNA SOLA ASIGNATURA.
///
///
//}  /// FIRN DEL FOR QUE CONTIENE EL NUMERO DE ASIGNATURAS SELECCIONADAS.
///
///
echo json_encode($salidaJson);
?>