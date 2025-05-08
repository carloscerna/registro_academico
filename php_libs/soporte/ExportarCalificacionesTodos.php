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
  // Obtener fecha y hora actual en el nuevo formato
    $fechaHoraActual = date('d/m/Y h:i:s A'); // Formato dd/mm/yyyy y hora en 12 horas con AM/PM
// Variables y $_REQUEST(), $_POST();
    $db_link = $dblink;
    $respuestaOK = false;
    $mensajeError = "";
    $contenidoOK = "";
    $observaciones = "";
    $URLNombreArchivo = "";
    $todasLasAsignaturas = "no";
    $DestinoArchivok = "";
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
    // CONSULTA DE LAS ASIGNTURAS, CALIFICACIONES U OTROS.
    $query_todas = "SELECT 
    a.codigo_nie, 
    TRIM(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) AS apellido_alumno,
    a.nombre_completo,
    TRIM(a.apellido_paterno || ' ' || a.apellido_materno) AS apellidos_alumno,
    a.fecha_nacimiento,
    am.codigo_bach_o_ciclo AS codigo_bachillerato,
    bach.nombre AS nombre_bachillerato,
    am.codigo_ann_lectivo, 
    ann.nombre AS nombre_ann_lectivo,
    am.codigo_grado,  
    gan.nombre AS nombre_grado,
    am.codigo_seccion,
    sec.nombre AS nombre_seccion,
    am.retirado,
    asig.codigo_area, 
    asig.nombre AS nombre_asignatura,
    asig.codigo_cc,
    n.codigo_asignatura,
    n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5,
    n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final,
    ROUND((n.nota_p_p_1 + n.nota_p_p_2 + n.nota_p_p_3),1) AS total_puntos_basica,
    ROUND((n.nota_p_p_1 + n.nota_p_p_2 + n.nota_p_p_3 + n.nota_p_p_4),1) AS total_puntos_media
    FROM alumno a
    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' 
    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
    INNER JOIN a_a_a_bach_o_ciclo aaa 
        ON aaa.codigo_asignatura = n.codigo_asignatura 
        AND aaa.codigo_ann_lectivo = '$codigo_annlectivo' 
        AND aaa.codigo_bach_o_ciclo = '$codigo_bachillerato' 
        AND aaa.codigo_grado = '$codigo_grado'
    WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all'
    ORDER BY apellido_alumno, n.codigo_asignatura ASC";
    //AND NOT (am.codigo_bach_o_ciclo = '10' AND asig.codigo_area = '03') -- Filtramos datos no requeridos
    // crear $datos.
          $result_asignatura = $db_link -> query($query_todas);
          $datos = $result_asignatura->fetchAll(PDO::FETCH_ASSOC);
  }////////////////////////////////////////////////////////////////////////////////////////////////////
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
      $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
      $codigo_area_actual = trim($fila['codigo_area']);
        // Verificar si cumple las condiciones de exclusión
        if ($codigo_bachillerato_actual === '15' && $codigo_area_actual === '03') {
          continue; // Saltar esta asignatura y no procesarla
      }
      //
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

    // PARA LAS CALIFICACIONES
    $datos_agrupados = [];

    foreach ($datos as $fila) {
        $nie = trim($fila['codigo_nie']);
        $asignatura = $fila['nombre_asignatura'];
        $nota = $fila['nota_p_p_1'];
        $codigo_cc = trim($fila['codigo_cc']);
        $indicador = $fila['indicador_p_p_1'];
        $codigo_bachillerato_actual = trim($fila['codigo_bachillerato']);
        $codigo_area_actual = trim($fila['codigo_area']);
        $nombreSeccion = trim($fila['nombre_seccion']);

    // Evitar que los datos se procesen si codigo_bachillerato es 10 y codigo_area es "03"
    if ($codigo_bachillerato_actual === "15" && $codigo_area_actual === "03") {
      continue; // Saltar esta fila y no procesarla
    } // condicion para el bachillerato tecnido vocacional administrativo.

  // Aplicar lógica según codigo_cc
        // Determinar el valor correcto para la columna codigo_cc
        if ($codigo_cc === "02") {
          if (is_null($nota) || $nota <= 0 || $nota == "") {
                $nota_formateada = "B"; // Si la calificación está vacía, asignar 1
            }elseif ($nota >= 9) {
                $nota_formateada = "E";
            } elseif ($nota >= 7) {
                $nota_formateada = "MB";
            } else {
                $nota_formateada = "B";
            }
        } elseif ($codigo_cc === "03") {
            $nota_formateada = $indicador; // Usar el valor de indicador
        } elseif ($codigo_cc === "01") { // Calificación
          if (is_null($nota) || $nota <= 0) { // Vacío o igual a 0
              $nota_formateada = "1"; // Asignar 1
          } elseif ($nota > 0 && $nota <= 0.99) { // Entre 0 y 0.99
              $nota_formateada = "1"; // Mantener tal como está
          } else {
              $nota_formateada = $nota; // Para otros valores
          }
      }  
        // Agrupar los datos correctamente
        if (!isset($datos_agrupados[$nie])) {
          $datos_agrupados[$nie] = []; // Inicializar agrupación

//            $datos_agrupados[$nie] = ['codigo_nie' => $nie];
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
// ESCRIBE EL NOMBRE DEL ARCHIVO.
function NombreArchivoExcel() {
  global $objPHPExcel, $codigo_bachillerato, $nombre_annlectivo, $path_root, $nombre_modalidad, $nombre_grado, $nombreSeccion, $periodo, $DestinoArchivo, $salidaJson,
      $contenidoOK;
  $fechaHoraActual = date('d-m-Y_h-i-s_A'); // Formato dd-mm-yyyy y hora 12 horas
  $num = 1; // Incremento de fila inicial
  $codigo_destino = 3; // Tipo de carpeta

  try {
      // Crear directorio para guardar el archivo
      CrearDirectorios($path_root, $nombre_annlectivo, $codigo_bachillerato, $codigo_destino, $periodo);

      // Verificar si el directorio existe y tiene permisos de escritura
      if (!file_exists($DestinoArchivo)) {
          mkdir($DestinoArchivo, 0777, true); // Crear directorio con permisos
      }

      // Limpiar el nombre del archivo
      $nombreArchivo = htmlspecialchars($nombre_grado) . " " . $nombreSeccion  . " - " . $nombre_modalidad;
      $nombreArchivo = str_replace(['/', ':'], '-', $nombreArchivo); // Reemplazar caracteres inválidos

      // Definir ruta completa del archivo
      $URLNombreArchivo = $DestinoArchivo . "/" . trim($nombreArchivo) . ".xlsx";

      // Borrar archivos y subcarpetas anteriores
     // borrarContenidoDirectorio($DestinoArchivo);

      // Guardar el archivo Excel
      $objWriter = new Xlsx($objPHPExcel);
      $objWriter->save($URLNombreArchivo);
// Definir el ícono de Excel con Font Awesome
$iconoExcel = '<i class="fas fa-file-excel" style="color: green;"></i>';

// Obtener el tamaño del archivo en KB o MB
$tamanoArchivo = filesize($URLNombreArchivo); // Tamaño en bytes
$tamanoKB = round($tamanoArchivo / 1024, 2); // Convertimos a KB
$tamanoMB = round($tamanoKB / 1024, 2); // Convertimos a MB si es mayor a 1024 KB
$tamanoFinal = $tamanoKB < 1024 ? "$tamanoKB KB" : "$tamanoMB MB"; // Mostrar en KB o MB

// Construir la tabla en PHP
$contenidoOK = "<table border='1' style='width: 100%; border-collapse: collapse; text-align: center;'>
    <thead>
        <tr>
            <th>#</th>
            <th>Archivo</th>
            <th>Tamaño</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>$num</td>
            <td>$iconoExcel <strong>$nombreArchivo.xlsx</strong></td>
            <td>$tamanoFinal</td>
        </tr>
    </tbody>
</table>";
      // salida Json
      $salidaJson = [
          "respuesta" => true,
          "mensaje" => "¡¡¡ :) Archivo Creado con Éxito :) !!!",
          "contenido" => $contenidoOK
      ];

      return $salidaJson;

  } catch (Exception $e) {
      // Manejo de errores
      return [
          "respuesta" => false,
          "mensaje" => "Error al crear archivo: " . $e->getMessage(),
          "contenido" => null
      ];
  }
}         
// BORRAR CARPETAS Y ARCHIVOS.
function borrarContenidoDirectorio($directorio) {
  // Verificar si el directorio existe
  if (is_dir($directorio)) {
      // Recorrer todos los archivos y subcarpetas en el directorio
      foreach (scandir($directorio) as $elemento) {
          if ($elemento !== '.' && $elemento !== '..') {
              $rutaElemento = $directorio . '/' . $elemento;

              // Si es una carpeta, llamar a la función de forma recursiva
              if (is_dir($rutaElemento)) {
                  borrarContenidoDirectorio($rutaElemento);
                  rmdir($rutaElemento); // Eliminar la carpeta después de borrar su contenido
              } elseif (is_file($rutaElemento)) {
                  unlink($rutaElemento); // Eliminar el archivo
              }
          }
      }
  }
}
