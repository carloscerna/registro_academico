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
      // Focalizado.
        if($nombre_grado == "Segundo grado" || $nombre_grado == "Tercer grado"){
          $nombre_grado = trim($NombreGrado[0]) . " " . trim($NombreGrado[1]);
        }
    $codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,4) . $_REQUEST["lstannlectivo"];
    $codigoModalidadGradoAnnLectivo = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,2) . $_REQUEST["lstannlectivo"];
    $periodo = $_REQUEST["lstperiodo"];
    $codigo_asignatura = substr($_REQUEST["lstasignatura"],0,3);
    $fecha = $_REQUEST["txtfecha"];
    $columnaAsignaturas = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
    // buscar la consulta y la ejecuta.
  consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
  // EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
      $total_asignaturas = 0;	
          while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
              {
                  $total_asignaturas = (trim($row['total_asignaturas']));
              }
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
        switch ($codigo_modalidad) {
          case ($codigo_modalidad >= '03' and $codigo_modalidad <= '05'): // educación basica y III Ciclo.
            if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}        
              break;
          case ($codigo_modalidad >= '06' and $codigo_modalidad <= '09'): // Edcuación Media Jornada Completa.
            if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
            if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4";}
              break;
          case ($codigo_modalidad >= '10' and $codigo_modalidad <= '12'): // educación media y III ciclo nocturna.
            if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
            if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4";}
            if($periodo == "Periodo 5"){$nota_p_p = "nota_p_p_5";}
              break;
          case ($codigo_modalidad >= '13' and $codigo_modalidad <= '14'): // Educación Parvularia Estándar de desarrollo
            if($periodo == "Periodo 1"){$nota_p_p = "indicador_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "indicador_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "indicador_p_p_3";}        
            if($periodo == "Alertas"){$nota_p_p = "alertas";}
              break;
          case ($codigo_modalidad == '16'): // Educación Básica Segundo y Tercer grado Focalizado.
            if($periodo == "Periodo 1"){$nota_p_p = "indicador_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "indicador_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "indicador_p_p_3";}        
              break;
          case ($codigo_modalidad == '15'): // Educación Media Bachillerato Tecnico Vocacion Administrativo Contable.
            if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
            if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4";}
              break;
          default:
            if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
            if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
            if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
        }
//
//  EVALUAR SI SON TODAS LAS ASIGNATURAS O SOLO UNA.
//
  if($todasLasAsignaturas == "yes"){
    // agregar CONSULTA PARA EDUCACIÒN PARVULARIA Y BASICA DE ESTANDAR DESARROLLO.
   $query_todas  = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
    a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.fecha_nacimiento,
    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, 
    asig.codigo_area, asig.nombre as nombre_asignatura,
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
          WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all'
            ORDER BY apellido_alumno, n.codigo_asignatura ASC";
          //
          $result_asignatura = $db_link -> query($query_todas);
          $datos = $result_asignatura->fetchAll(PDO::FETCH_ASSOC);

            while($row = $result_asignatura -> fetch(PDO::FETCH_BOTH))
              {
                  $nombre_asignatura = trim($row['nombre_asignatura']);
                  $nombre_asignatura = str_replace(['“','”','`','´','','.', '\\', '/', '*',' " ',':',","], ' ', $nombre_asignatura);
                  $nombre_asignatura_t[] = trim($nombre_asignatura);
                  $codigo_asignatura_t[] = trim($row['codigo_asignatura']);
                  $numeroNie[] = trim($row["codigo_nie"]);
              }

                  // Consulta
                  $codigo_bachillerato = $codigoModalidadGradoAnnLectivo; // Define tu variable
                    $query = "SELECT aaa.codigo_asignatura, aaa.orden, asig.nombre as nombre_asignatura, asig.codigo_servicio_educativo
                            FROM a_a_a_bach_o_ciclo aaa 
                            INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura 
                            WHERE btrim(aaa.codigo_bach_o_ciclo || aaa.codigo_grado || codigo_ann_lectivo) = :codigo_bachillerato
                            ORDER BY aaa.orden";
              
                  $stmt = $db_link->prepare($query);
                  $stmt->bindParam(':codigo_bachillerato', $codigo_bachillerato, PDO::PARAM_STR);
                  $stmt->execute();
             
                  // Encabezado en la primera fila (opcional)
                  $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Asignaturas'); // Encabezado general en A1
              
                  // Escribir nombres de asignaturas en columnas de la misma fila
                  $columna = 'B'; // Comienza en la columna B
                  $fila = 1; // Mantén los valores en la misma fila (fila 1)
                  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $nombreAsignatura = mb_strtoupper($row['nombre_asignatura'], 'UTF-8');
                      $objPHPExcel->getActiveSheet()->setCellValue("{$columna}{$fila}", $nombreAsignatura); // Escribir en columnas B, C, D...
                      $columna++; // Cambia a la siguiente columna
                  }
                      // Ajustar automáticamente el ancho de las columnas
                        foreach ($objPHPExcel->getActiveSheet()->getColumnIterator() as $column) {
                            $objPHPExcel->getActiveSheet()->getColumnDimension($column->getColumnIndex())->setAutoSize(true); // Ajustar ancho
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
    $sheet = $objPHPExcel->getActiveSheet();
    
    // **Encabezados**
    $sheet->setCellValue('A1', 'Código NIE'); // Columna A
    // Obtener nombres de asignaturas para los encabezados dinámicos
    $asignaturas = [];
    foreach ($datos as $fila) {
        if (!in_array($fila['nombre_asignatura'], $asignaturas)) {
            $asignaturas[] = $fila['nombre_asignatura'];
        }
    }
    // Coloca los nombres de asignaturas como encabezados (B, C, D...)
    $col = 'B';
    foreach ($asignaturas as $asignatura) {
        $sheet->setCellValue("$col" . '1', mb_strtoupper($asignatura, 'UTF-8'));
        $col++;
    }
    $datos_agrupados = [];

    foreach ($datos as $fila) {
        $nie = $fila['codigo_nie'];
        $asignatura = $fila['nombre_asignatura'];
        $nota = $fila['nota_p_p_1'];
        $codigo_cc = trim($fila['codigo_cc']);
        $indicador = $fila['indicador_p_p_1'];
    
        // Determinar el valor correcto para la columna codigo_cc
        if ($codigo_cc === "02") {
            if ($nota >= 9) {
                $nota_formateada = "E";
            } elseif ($nota >= 7) {
                $nota_formateada = "MB";
            } elseif ($nota >= 5) {
                $nota_formateada = "B";
            } else {
                $nota_formateada = ""; // Vacío para notas menores a 5
            }
        } elseif ($codigo_cc === "03") {
            $nota_formateada = $indicador; // Usar el valor de indicador
        } else {
            $nota_formateada = $nota; // Mantener nota original si es Calificación
        }
    
        // Agrupar los datos correctamente
        if (!isset($datos_agrupados[$nie])) {
            $datos_agrupados[$nie] = ['codigo_nie' => $nie];
        }
        $datos_agrupados[$nie][$asignatura] = $nota_formateada;
    }
    //var_dump($datos_agrupados);
    // **Rellenar datos en filas**
    $fila = 2;
    foreach ($datos_agrupados as $nie => $datosAlumno) {
        $sheet->setCellValue("A$fila", $nie); // Código NIE en columna A
        
        // Colocar notas en columnas según asignatura
        foreach ($asignaturas as $index => $asignatura) {
            $columna = chr(ord('B') + $index);
            $nota = isset($datosAlumno[$asignatura]) ? $datosAlumno[$asignatura] : ""; // Nota o vacío
            $sheet->setCellValue("$columna$fila", $nota);
        }

        $fila++; // Avanzar a la siguiente fila
    }
    // **Ajuste automático del ancho de columnas**
    foreach ($sheet->getColumnIterator() as $column) {
        $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
    }
    NombreArchivoExcel();        // Nombre dle archivo con los diferentes datos de estudiantes, calificaciones, etc.
  } // LINEA DEL ELSE PARA TODAS LAS ASIGNATURAS
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
    global $nota_p_p_, $objPHPExcel, $fila_excel, $valor_uno, $nota_concepto, $codigo_grado, $conteoAsignaturas, $columnaAsignaturas, $nota;
    $nota_p_p = $nota;
    switch ($codigo_cc)
    {
      case "01":  // calificación
          if($nota_p_p_ < 1){
            $objPHPExcel->getActiveSheet()->getStyle($columnaAsignaturas[$conteoAsignaturas].$fila_excel)->getNumberFormat()->setFormatCode('#,##0.0');
            $objPHPExcel->getActiveSheet()->SetCellValue($columnaAsignaturas[$conteoAsignaturas].$fila_excel, $valor_uno);
          }else{
            //print $conteoAsignaturas . "<br>";
            $objPHPExcel->getActiveSheet()->getStyle($columnaAsignaturas[$conteoAsignaturas].$fila_excel)->getNumberFormat()->setFormatCode('#,##0.0');
            $objPHPExcel->getActiveSheet()->SetCellValue($columnaAsignaturas[$conteoAsignaturas].$fila_excel, $nota_p_p_);
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
          if($codigo_grado == "17" || $codigo_grado == "18"){
            $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, "No lo hace"); 
          }else{
            $objPHPExcel->getActiveSheet()->SetCellValue("B".$fila_excel, "NE"); 
          }
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
  global $objPHPExcel, $fila_excel, $listado, $total_asignaturas, $conteoAsignaturas;
    $filaAsignatura = 1;
    $numeroNie = trim($listado["codigo_nie"]);
    $nombreAsignatura = trim($listado["n_asignatura"]);

    //Escribimos en la hoja en la celda e3. NIE, CALFICIACION, FECHA, OBSERVACION, ASIGNATURA.
    if($conteoAsignaturas == 1){
        
        $objPHPExcel->getActiveSheet()->SetCellValue("A".$fila_excel + 1, $numeroNie);
    }
}
// Escribimos en la hoja en la celda NIE, CALIFICACION, FECHA, OBSERVACIÓN Y CODIGO ASIGNATURA
function ExcelInicial(){
  global $objPHPExcel, $columnID;
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
    $longitudNombreArchivo = 240;
      CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,$periodo); // Crear Carpeta.                  
  // Unir Modalidad - Grado y Sección.
    //$nombre_directorio_mgs = replace_3(trim($nombre_modalidad."-" . $nombre_grado));
    $nombre_directorio_mgs = replace_3(trim($nombre_grado));
  // Con el nombre de la modalidad - grado - sección.
    if(!file_exists($DestinoArchivo . $nombre_directorio_mgs)){
          mkdir ($DestinoArchivo . $nombre_directorio_mgs); // Para Nóminas. Escolanadamente. PERIODO
          chmod ($DestinoArchivo . $nombre_directorio_mgs,07777);
      }
    if($nota_p_p_ == "Alertas" && $codigo_area == '09'){             // Destino Archivo.
      $nombre_archivo = htmlspecialchars($NombreAsignatura);                 // Nombre del Archivo.
      $URLNombreArchivo = $DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo;
        if(strlen($URLNombreArchivo) >= 220){ // VALIDAMOS PARA QUE NO EXCEDA DE 250 CARACTERES.
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
      $nombre_archivo = htmlspecialchars($nombre_grado);                 // Nombre del Archivo.
      $URLNombreArchivo = $DestinoArchivo.$nombre_directorio_mgs."/".$nombre_archivo;
        if(strlen($URLNombreArchivo) >= 220){ // VALIDAMOS PARA QUE NO EXCEDA DE 250 CARACTERES.
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
          WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all'
            ORDER BY apellido_alumno, n.codigo_asignatura ASC";
  // ejecutar la consulta. PARA MOSTRAR LOS RESULTADOS EN PANTALLA.
      $result = $db_link -> query($query);
}