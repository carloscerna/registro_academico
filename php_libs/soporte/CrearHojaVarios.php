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
    $nombre_archivo = "02-10391.xlsx";
    $objPHPExcel = $objReader->load($origen."02-10391.xlsx");
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
		while($objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue() != "")
		  {
			 //  DATOS GENERALES.
			 $nie = $objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue();
			 //$descripcion = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
			// $codigo_departamento = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();

		     $query = "SELECT a.codigo_nie, a.id_alumno, am.codigo_bach_o_ciclo, bach.nombre as nombre_bachillerato,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad, a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.partida_nacimiento, a.telefono_celular,
                a.codigo_departamento, a.codigo_municipio,
                gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.retirado,
                tur.nombre as nombre_turno,
                gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.retirado, tur.nombre as nombre_turno,
                cat_gs.descripcion as genero_estudiante
                from alumno a 
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                INNER JOIN catalogo_familiar cat_f ON cat_f.codigo = ae.codigo_familiar
                INNER JOIN catalogo_genero cat_g ON cat_g.codigo = ae.codigo_genero
                INNER JOIN catalogo_genero cat_gs ON cat_gs.codigo = a.codigo_genero
                    where am.codigo_ann_lectivo = '25'";
		// ejecutar la consulta.
				$consulta = $dblink -> query($query);
                while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                {
                    // obtenemos el �ltimo c�digo asignado.
                    $apellidos_nombres = $listado['apellido_alumno'];
                    $codigo_alumno = $listado['id_alumno'];
                    $direccion_alumno = $listado['direccion_alumno'];
                    $codigo_nie = $listado['codigo_nie'];
                    $nombre_grado = $listado['nombre_grado'];
                    $nombre_seccion = $listado['nombre_seccion'];
                    $nombre_turno = $listado['nombre_turno'];                    
                    $retirado = $listado['retirado'];

                    $fecha_nacimiento = trim(cambiaf_a_normal(($listado['fecha_nacimiento'])));
                    $edad = trim(($listado['edad']));
                    $pn_numero = trim(($listado['pn_numero']));
                    $pn_tomo = trim(($listado['pn_tomo']));
                    $pn_libro = trim(($listado['pn_libro']));
                    $pn_folio = trim(($listado['pn_folio']));
                    // genero estudiante
                    $genero_estudiante = trim($listado['genero_estudiante']);

                    if($retirado == 'false'){
                        $retirado = "Si";
                    }else{
                        $retirado = "No";
                    }

                    $codigo_departamento = trim($listado["codigo_departamento"]);
                    $codigo_municipio = trim($listado["codigo_municipio"]);
                // Extraer nombre del Municpio y Departamento.
                    if($codigo_nie == "10767079"){
                        print $query_d_m = "SELECT depa.codigo, depa.nombre as nombre_departamento, muni.codigo, muni.nombre as nombre_municipio
                        FROM departamento depa 
                            INNER JOIN municipio muni ON muni.codigo_departamento = depa.codigo
                                WHERE depa.codigo = '$codigo_departamento' and muni.codigo = '$codigo_municipio'";
                    }else{
                        $query_d_m = "SELECT depa.codigo, depa.nombre as nombre_departamento, muni.codigo, muni.nombre as nombre_municipio
                        FROM departamento depa 
                            INNER JOIN municipio muni ON muni.codigo_departamento = depa.codigo
                                WHERE depa.codigo = '$codigo_departamento' and muni.codigo = '$codigo_municipio'";
                    }
                   
                //  Ejecutar Query.
                    $result_d_m = $dblink -> query($query_d_m);
                    while($row_d_m = $result_d_m -> fetch(PDO::FETCH_BOTH))
                    {
                        $departamento_nacimiento = convertirtexto(strtolower(trim($row_d_m["nombre_departamento"])));
                        $municipio_nacimiento = convertirtexto(strtolower(trim($row_d_m["nombre_municipio"])));
                    }    
                //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                    $objPHPExcel->getActiveSheet()->SetCellValue("O".$fila_excel, TRIM($listado['nombre_grado']));
                    $objPHPExcel->getActiveSheet()->SetCellValue("P".$fila_excel, TRIM($listado['nombre_seccion']));
                    $objPHPExcel->getActiveSheet()->SetCellValue("Q".$fila_excel, $retirado);
                    $objPHPExcel->getActiveSheet()->SetCellValue("R".$fila_excel, $nombre_turno);

                    $objPHPExcel->getActiveSheet()->SetCellValue("S".$fila_excel,($genero_estudiante));
                    $objPHPExcel->getActiveSheet()->SetCellValue("T".$fila_excel,($fecha_nacimiento));
                    $objPHPExcel->getActiveSheet()->SetCellValue("U".$fila_excel,($edad));
                    $objPHPExcel->getActiveSheet()->SetCellValue("V".$fila_excel,($pn_numero));
                    $objPHPExcel->getActiveSheet()->SetCellValue("W".$fila_excel,($pn_folio));
                    $objPHPExcel->getActiveSheet()->SetCellValue("X".$fila_excel,($pn_tomo));
                    $objPHPExcel->getActiveSheet()->SetCellValue("Y".$fila_excel,($pn_libro));
                    $objPHPExcel->getActiveSheet()->SetCellValue("Z".$fila_excel,($departamento_nacimiento));
                    $objPHPExcel->getActiveSheet()->SetCellValue("AA".$fila_excel,($municipio_nacimiento));
                    $objPHPExcel->getActiveSheet()->SetCellValue("AE".$fila_excel,($codigo_alumno));
                    $objPHPExcel->getActiveSheet()->SetCellValue("AF".$fila_excel,($apellidos_nombres));
                    $objPHPExcel->getActiveSheet()->SetCellValue("AG".$fila_excel,($direccion_alumno));

                    print "<p>$fila - $codigo_nie - $apellidos_nombres - $nombre_grado - $nombre_seccion - $retirado - $departamento_nacimiento - $municipio_nacimiento</p>";
                }
				$fila = $fila + 1; $fila_excel = $fila_excel + 1;
		}
    // Grabar el archivo.
    $objWriter = new Xlsx($objPHPExcel);
    $objWriter->save($origen.$nombre_archivo);