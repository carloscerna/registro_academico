<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
	set_time_limit(0);
    ini_set("memory_limit","2000M");
// Inicializando el array
$datos=array(); $fila_array = 2;
// variables. del post.
//$ruta = $path_root.'/registro_academico/formatos_hoja_de_calculo/COMPUTADORAS.xlsx';
// Inicializando el array
$datos=array(); $fila_array = 2;
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
    $nombre_archivo = "CE-TABLET-10391-1.xlsx";
    $objPHPExcel = $objReader->load($origen."CE-TABLET-10391-1.xlsx");
    //$nombre_archivo = "CE-COMPUTADORA.xlsx";
    //$objPHPExcel = $objReader->load($origen."CE-COMPUTADORA.xlsx");
// Leemos un archivo Excel 2007
	 $fila = 5;
     $fila_excel = 5;
// N�mero de hoja.
   $numero_de_hoja = 0;
   //$total_de_hojas = $objPHPExcel->getSheetCount();
// 	Recorre el numero de hojas que contenga el libro

       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
	
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("K".$fila)->getValue() != "")
		  {
			 //  DATOS GENERALES.
			 $nie = $objPHPExcel->getActiveSheet()->getCell("K".$fila)->getValue();
			 //$descripcion = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
			// $codigo_departamento = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();

		$query = "SELECT a.codigo_nie, a.id_alumno, am.codigo_bach_o_ciclo, bach.nombre as nombre_bachillerato,
                gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.retirado,
                tur.nombre as nombre_turno,
                gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.retirado, tur.nombre as nombre_turno
                from alumno a 
                INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                    where a.codigo_nie = '$nie' and am.codigo_ann_lectivo = '23'";
		// ejecutar la consulta.
				$consulta = $dblink -> query($query);
                while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                {
                    // obtenemos el �ltimo c�digo asignado.
                    $codigo_alumno = $listado['id_alumno'];
                    $codigo_nie = $listado['codigo_nie'];
                    $nombre_grado = $listado['nombre_grado'];
                    $nombre_seccion = $listado['nombre_seccion'];
                    $nombre_turno = $listado['nombre_turno'];                    
                    $retirado = $listado['retirado'];

                    if($retirado == 'false'){
                        $retirado = "Si";
                    }else{
                        $retirado = "No";
                    }
                    $objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, TRIM($listado['nombre_grado']));
                    $objPHPExcel->getActiveSheet()->SetCellValue("Z".$fila_excel, TRIM($listado['nombre_seccion']));
                    $objPHPExcel->getActiveSheet()->SetCellValue("AA".$fila_excel, $retirado);
                    $objPHPExcel->getActiveSheet()->SetCellValue("AB".$fila_excel, $nombre_turno);
                    //$objPHPExcel->getActiveSheet()->SetCellValue("AF".$fila_excel, '2023');
                    //$objPHPExcel->getActiveSheet()->SetCellValue("V".$fila_excel, TRIM($listado['nombre_grado']));
                    //$objPHPExcel->getActiveSheet()->SetCellValue("W".$fila_excel, TRIM($listado['nombre_seccion']));
                    //$objPHPExcel->getActiveSheet()->SetCellValue("X".$fila_excel, $retirado);
                    //$objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel, '2023');
                    //$objPHPExcel->getActiveSheet()->SetCellValue("Z".$fila_excel, trim($nombre_turno));                    
                    print "<p>$fila - $codigo_nie - $nombre_grado - $nombre_seccion - $retirado</p>";
                }
				$fila = $fila + 1; $fila_excel = $fila_excel + 1;
		}
    // Grabar el archivo.
    $objWriter = new Xlsx($objPHPExcel);
    $objWriter->save($origen.$nombre_archivo);
?>