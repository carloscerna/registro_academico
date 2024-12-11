<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/funciones.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
	$ruta = $path_root.'/registro_academico/formatos_hoja_de_calculo/catalogos/DEPARTAMENTO Y MUNICIPIIOS EN TABLA VIEJA.xlsx';
  //$trimestre = trim($_REQUEST["periodo_"]);
// variable de la conexi�n dbf.
    $db_link = $dblink;
// Inicializando el array
	$datos=[]; $fila_array = 0;
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Leemos un archivo Excel 2007
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
    $fila = 2;
    $objPHPExcel = $objReader->load($origen);
// N�mero de hoja.
    $numero_de_hoja = 0;
// 	Recorre el numero de hojas que contenga el libro
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue() != "")
		  {
			 //  DATOS GENERALES.
			 	$codigo_viejo_municipio = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
                $codigo_departamento = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue()));
				$codigo_nuevo_municipio = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue()));
				$codigo_distrito = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("F".$fila)->getValue()));
            // query
			    $updateQuery = "UPDATE catalogo_canton SET codigo_nuevo_municipio = '$codigo_nuevo_municipio', codigo_distrito = '$codigo_distrito'
							WHERE codigo_departamento = '$codigo_departamento' and codigo_municipio = '$codigo_viejo_municipio'
							";
            // Ejecutar consulta.
				$consulta = $dblink -> query($updateQuery);
            // saltar fila.
         	    $fila++;
			    print $codigo_viejo_municipio . ' - ' . $codigo_nuevo_municipio . ' - ' . $codigo_departamento . ' - ' . $codigo_distrito;
			    print "<br>";
		}	// FIN DEL WHILE PRINCIPAL DE L AHOJA DE CALCULO.