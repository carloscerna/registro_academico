<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/funciones_2.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");  // CONEXION DE LA BASE DE DATOS.
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php"); // Llamar a la libreria fpdf
    header("Content-Type: text/html; charset='UTF-8'");     // cambiar a utf-8.
    date_default_timezone_set('America/El_Salvador');  // Establecer formato para la fecha.
    setlocale(LC_TIME, 'spanish');
// Variables y $_REQUEST(), $_POST();
    $db_link = $dblink;
    $respuestaOK = false;
    $mensajeError = "";
    $contenidoOK = "";
    $observaciones = "";
    $URLNombreArchivo = "";
    $todasLasAsignaturas = "no";
    $todasLasAsignaturas  = $_REQUEST["TodasLasAsignaturas"];
    $Exportar  = json_decode($_REQUEST["Exportar"]);
      $NombreAsignatura = $Exportar->NombreAsignatura;
      $NombreGrado = $Exportar->NombreGST;
      $nombre_annlectivo = $Exportar->NombreAnnLectivo;
      $nombre_modalidad = $Exportar->NombreNivel;
      $NombreGrado = explode("-", $NombreGrado);
      $nombre_grado = trim($NombreGrado[0]);
    $codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,4) . $_REQUEST["lstannlectivo"];
    $periodo = $_REQUEST["lstperiodo"];
    $codigo_asignatura = substr($_REQUEST["lstasignatura"],0,3);
    $fecha = $_REQUEST["txtfecha"];
    
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Time zone.
    date_default_timezone_set('America/El_Salvador');
// Set default font
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007-Office 365.
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";
    $objPHPExcel = $objReader->load($origen."Formato - Importar Notas SIGES.xlsx");
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex(0);
// Escribimos en la hoja en la celda NIE, CALIFICACION, FECHA, OBSERVACIÓN Y CODIGO ASIGNATURA
    ExcelInicial();
// Información Académica.
      $codigo_bachillerato = substr($codigo_all,0,2);
      $codigo_modalidad = substr($codigo_all,0,2);
      $codigo_grado = substr($codigo_all,2,2);
      $codigo_seccion = substr($codigo_all,4,2);
      $codigo_annlectivo = substr($codigo_all,6,2);
// Evaluador nota para basica y parvularia, Extraer el nombre del grado
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
//
//  EVALUAR SI SON TODAS LAS ASIGNATURAS O SOLO UNA.
//
  if($todasLasAsignaturas == "yes"){
    // agregar CONSULTA PARA EDUCACIÒN PARVULARIA Y BASICA DE ESTANDAR DESARROLLO.
        $query_todas  = "SELECT DISTINCT ON (aaa.codigo_asignatura) aaa.codigo_asignacion, aaa.codigo_bach_o_ciclo, aaa.codigo_asignatura, aaa.codigo_ann_lectivo, aaa.codigo_sirai, 
            aaa.codigo_grado, aaa.id_asignacion, aaa.orden,
            ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura,
            asig.codigo_area as codigo_area_asignatura, asig.codigo_area_dimension, asig.codigo_area_subdimension, cat_area_di.descripcion as descripcion_area_dimension,
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
                  ORDER BY aaa.codigo_asignatura, asig.codigo_area, asig.codigo_area_dimension, asig.codigo_area_subdimension";
          //
          $result_asignatura = $db_link -> query($query_todas);
            while($row = $result_asignatura -> fetch(PDO::FETCH_BOTH))
              {
                  $nombre_asignatura = trim($row['nombre_asignatura']);
                  $nombre_asignatura = str_replace(['“','”','`','´','','.', '\\', '/', '*',' " ',':',","], ' ', $nombre_asignatura);
                  $nombre_asignatura_t[] = trim($nombre_asignatura);
                  $codigo_asignatura_t[] = trim($row['codigo_asignatura']);
                  $nombre_area[] = trim($row['descripcion_area']);
                  $nombre_area_dimension_t[] = trim($row['descripcion_area_dimension']);
                  $nombre_area_subdimension_t[] = trim($row['descripcion_area_subdimension']);
              }
  }else{
        // CONSULTA PARA OBTENER LAS NOTAS DE LOS PERIODOS.
          BuscarPorCodigoTabla($codigo_asignatura);
  }
////////////////////////////////////////////////////////////////////////////////////////////////////
// RECORRER LA TABLA Y GUARDAR LOS DATOS.
////////////////////////////////////////////////////////////////////////////////////////////////////
  if($todasLasAsignaturas == "yes")
  {    
    for ($i=0;$i<count($codigo_asignatura_t);$i++)        //// REPETIR EL PROCESO DEPENDE DE LAS ASIGNATURAS SELECCIONADAS.
      {
        // Nombre Original de la variables $NombreAsignatura.
            $NombreAsignatura = $nombre_area_dimension_t[$i] . '-' . $nombre_area_subdimension_t[$i] . '-' . trim($nombre_asignatura_t[$i]);         
        // RECORRE LA MATRIZ CON LOS CODIGOS Y NOMBRES DE LAS ASIGNATURAS.
            /// 
            if(trim($nombre_area_subdimension_t[$i]) == 'Ninguno'){
              $NombreAsignatura = $nombre_area_dimension_t[$i] . '-' . $nombre_area_subdimension_t[$i] . '-' . trim($nombre_asignatura_t[$i]); 
            }
        /// CONDICIONASL PARA AREA_SUBDIMENSION y AREA DIMENSION ES IGUAL A NINGUNO.
            if(trim($nombre_area_subdimension_t[$i]) == 'Ninguno' && trim($nombre_area_dimension_t[$i]) == 'Ninguno'){
              $NombreAsignatura = $nombre_area[$i] . "-" . trim($nombre_asignatura_t[$i]);
            }
            // AREA DIMENSION ES IGUAL A NINGUNO
            if(trim($nombre_area_subdimension_t[$i]) == 'Ninguno' && trim($nombre_area_dimension_t[$i]) != 'Ninguno'){
              $NombreAsignatura = $nombre_area_dimension_t[$i] . '-' . trim($nombre_asignatura_t[$i]); 
            }        

        // lo asigna para poder realizar la busqueda.
          $codigo_asignatura = $codigo_asignatura_t[$i];
        // CONSULTA PARA OBTENER LAS NOTAS DE LOS PERIODOS.
          BuscarPorCodigoTabla($codigo_asignatura);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // CUANDO SE HA SELECCIONADO PARA TODAS LAS ASIGNATURA
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $num = $i; $fila_excel = 1;	
        while($listado = $result -> fetch(PDO::FETCH_BOTH))
        {
          $nombre_completo = (trim($listado['apellido_alumno']));
          $codigo_area = trim($listado['codigo_area']);
          $nota_p_p_ = $listado[$nota_p_p];
          $codigo_cc = (trim($listado['codigo_cc']));         // Variable para saber si la asignatura es de concepto o de calificación.
          $fila_excel++; $valor_uno = 1;  // inremento del valor de la fila para excel.
            ConceptoCalificacion($codigo_cc);         // Evaluar si la asignatura es de CONCEPTO O CALIFICACIÓN.
            EscribirExcel();  // Escribe en los encabezados.
        } // WHILE QUE RECORRE LA BASE DE DATOS.
          NombreArchivoExcel();        // Nombre dle archivo con los diferentes datos de estudiantes, calificaciones, etc.
    }  // LINEA DEL FOR QUE RECORRE LA MATRIZ DE VARIAS ASIGNATURAS.
  } // LINEA DEL ELSE PARA TODAS LAS ASIGNATURAS
else
{
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // CUANDO SOLO SE HA SELECCIONADO PARA UNA SOLA ASIGNATURA
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      $num = 0; $fila_excel = 1; 
        while($listado = $result -> fetch(PDO::FETCH_BOTH))
            {
              $nombre_completo = (trim($listado['apellido_alumno']));               
              $nota_p_p_ = $listado[$nota_p_p];
              $codigo_area = trim($listado['codigo_area']);
              $codigo_cc = (trim($listado['codigo_cc']));               // Variable para saber si la asignatura es de concepto o de calificación.
              $fila_excel++; $valor_uno = 1; // inremento del valor de la fila para excel.
              ConceptoCalificacion($codigo_cc);         // Evaluar si la asignatura es de CONCEPTO O CALIFICACIÓN.
              EscribirExcel();  // Escribe en los encabezados.
            }
            NombreArchivoExcel();        // Nombre dle archivo con los diferentes datos de estudiantes, calificaciones, etc.
} // ELSE PARA UNA SOLA ASIGNATURA.
///
///
//}  /// FIRN DEL FOR QUE CONTIENE EL NUMERO DE ASIGNATURAS SELECCIONADAS.
///
///            
// Salida del JSON
  echo json_encode($salidaJson);
//
//  FUNCIONES
//
function ConceptoCalificacion($codigo_cc){
  global $nota_p_p_, $objPHPExcel, $fila_excel, $valor_uno, $nota_concepto;
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
}
//Escribir en Excel.
function EscribirExcel(){
  global $objPHPExcel, $fila_excel, $listado, $fecha, $observaciones;
    //Escribimos en la hoja en la celda e3. NIE, CALFICIACION, FECHA, OBSERVACION, ASIGNATURA.
    $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel, TRIM($listado['codigo_nie']));
          // fecha y estilo de la fecha.
          // Set the number format mask so that the excel timestamp  
          // will be displayed as a human-readable date/time 
          $objPHPExcel->getActiveSheet()->getStyle('C'.$fila_excel) 
          ->getNumberFormat() 
          ->setFormatCode( 
          \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_DDMMYYYY 
          ); // convertir A FORMATO DE FECHA
    $excelDateValue = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($fecha);  // Fecha.
    $objPHPExcel->getActiveSheet()->SetCellValue("C".$fila_excel, $excelDateValue); // Fecha.
    $objPHPExcel->getActiveSheet()->SetCellValue("D".$fila_excel, $observaciones);                // Observaciones
    $objPHPExcel->getActiveSheet()->SetCellValue("E".$fila_excel, TRIM($listado['codigo_sirai']));    // Código Asignatura
    $objPHPExcel->getActiveSheet()->SetCellValue("F".$fila_excel, TRIM($listado['apellido_alumno'])); // nombre completo del alumno por orden de apellidos
    $objPHPExcel->getActiveSheet()->SetCellValue("G".$fila_excel, TRIM($listado['nombre_grado']));    // NOMBRE GRADO
    $objPHPExcel->getActiveSheet()->SetCellValue("H".$fila_excel, TRIM($listado['nombre_seccion']));  // NOMBRE SECCION
}
// Escribimos en la hoja en la celda NIE, CALIFICACION, FECHA, OBSERVACIÓN Y CODIGO ASIGNATURA
function ExcelInicial(){
  global $objPHPExcel, $columnID;
    $objPHPExcel->getActiveSheet()->SetCellValue('A1', 'NIE');
    $objPHPExcel->getActiveSheet()->SetCellValue('B1', 'Calificacion');
    $objPHPExcel->getActiveSheet()->SetCellValue('C1', 'Fecha');
    $objPHPExcel->getActiveSheet()->SetCellValue('D1', 'Observacion');
    $objPHPExcel->getActiveSheet()->SetCellValue('E1', 'Asignatura');
    $objPHPExcel->getActiveSheet()->SetCellValue('F1', 'Nombre del Alumno');
    $objPHPExcel->getActiveSheet()->SetCellValue('G1', 'Grado');
    $objPHPExcel->getActiveSheet()->SetCellValue('H1', 'Sección');
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
}
// ESCRIBE EL NOMBRE DEL ARCHIVO.
function NombreArchivoExcel(){
  global $objPHPExcel, $codigo_bachillerato, $nombre_annlectivo, $path_root, $nombre_modalidad, $nombre_grado, $nombre_seccion, $periodo,
          $DestinoArchivo, $nota_p_p_, $codigo_area, $NombreAsignatura, $contenidoOK, $num, $salidaJson, $num;
    $num++; // incremento de la fila ( nombre asignatura.)
    $objWriter = new Xlsx($objPHPExcel);  //Grabar el archivo en formato CVS    
    $codigo_modalidad = $codigo_bachillerato; // Verificar si Existe el directorio archivos.
    $nombre_ann_lectivo = $nombre_annlectivo;
    $codigo_destino = 3; // Tipo de Carpeta a Grabar.
    $longitudNombreArchivo = 230;
      CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,$periodo); // Crear Carpeta.                  
  // Unir Modalidad - Grado y Sección.
    $nombre_directorio_mgs = replace_3(trim($nombre_modalidad."-" . $nombre_grado));
  // Con el nombre de la modalidad - grado - sección.
    if(!file_exists($DestinoArchivo . $nombre_directorio_mgs)){
          mkdir ($DestinoArchivo . $nombre_directorio_mgs); // Para Nóminas. Escolanadamente. PERIODO
          chmod ($DestinoArchivo . $nombre_directorio_mgs,07777);
      }
    if($nota_p_p_ == "Alertas" && $codigo_area == '09'){             // Destino Archivo.
      $nombre_archivo = htmlspecialchars($NombreAsignatura . ".xlsx");                 // Nombre del Archivo.
      $URLNombreArchivo = $DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo;
        if(strlen($URLNombreArchivo) >= 250){ // VALIDAMOS PARA QUE NO EXCEDA DE 250 CARACTERES.
          $URLNombreArchivo = substr($URLNombreArchivo,0,$longitudNombreArchivo) . ".xlsx";
        }else{
          $URLNombreArchivo = substr($URLNombreArchivo,0,$longitudNombreArchivo) . ".xlsx";
        }
      $objWriter->save($URLNombreArchivo);  // Guardar el archivo.
      // GUARDAR PARA LA VARIABLE CONTENIDOOK
      $contenidoOK .= "<tr>                 
        <td>$num
        <td>$nombre_archivo
      ";
    }else{
      $nombre_archivo = htmlspecialchars($NombreAsignatura . ".xlsx");                 // Nombre del Archivo.
      $URLNombreArchivo = $DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo;
        if(strlen($URLNombreArchivo) >= 250){ // VALIDAMOS PARA QUE NO EXCEDA DE 250 CARACTERES.
          $URLNombreArchivo = substr($URLNombreArchivo,0,$longitudNombreArchivo) . ".xlsx";
        }else{
          $URLNombreArchivo = substr($URLNombreArchivo,0,$longitudNombreArchivo) . ".xlsx";
        }
        
      $objWriter->save($URLNombreArchivo);  // Guardar el archivo.
      // GUARDAR PARA LA VARIABLE CONTENIDOOK
      $contenidoOK .= "<tr>                 
        <td>$num
        <td>$nombre_archivo
      ";
    }              
      $respuestaOK = true;
      $mensajeError = "¡¡¡ :) Archivo Creado con Éxito :) !!!";
    // Armamos array para convertir a JSON
      $salidaJson = array(
        "respuesta" => $respuestaOK,
        "mensaje" => $mensajeError,
        "contenido" => $contenidoOK
      );  
}
// CONSULTA SOBRE LA TABLA SOLO POR UNA ASIGNATURA.
function BuscarPorCodigoTabla($codigo_asignatura){
  global $codigo_annlectivo, $codigo_bachillerato, $codigo_all, $codigo_asignatura, $codigo_grado, $db_link, $result;
  // Armar query y ejecutarlo. para consultar la tabla por codigo asignatura.
    $query = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
    a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.fecha_nacimiento,
    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, 
    asig.codigo_area,
    sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, asig.codigo_cc, 
    n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.alertas, n.nota_final, n.recuperacion,
    round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, 
    aaa.codigo_sirai, aaa.codigo_asignatura, n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final, n.alertas
      FROM alumno a
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
        INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
        INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
        INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
        INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
        INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = n.codigo_asignatura and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and 
                    aaa.codigo_bach_o_ciclo = '$codigo_bachillerato' and aaa.codigo_grado = '$codigo_grado'
        INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
          WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all' and n.codigo_asignatura = '$codigo_asignatura'
            ORDER BY apellido_alumno, n.codigo_asignatura ASC";
  // ejecutar la consulta. PARA MOSTRAR LOS RESULTADOS EN PANTALLA.
      $result = $db_link -> query($query);
}
?>