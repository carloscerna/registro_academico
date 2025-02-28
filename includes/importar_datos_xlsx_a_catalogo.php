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
//  $ruta = $path_root.'/sgp_web/formatos_hoja_de_calculo/fianzas.xls';
// $ruta = $path_root.'/sgp_web/formatos_hoja_de_calculo/prestamos.xls';
	$ruta = $path_root.'/registro_academico/formatos_hoja_de_calculo/EDUCACIÓN DESARROLLO ESTANDAR.xlsx';
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
// Time zone.
    //echo date('H:i:s') . " Set Time Zone"."<br />";
    //date_default_timezone_set('America/El_Salvador');
// set codings.
//    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007
   $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
	 $fila = 5;
	 $fila2 = 5;
	 $discapacidad = "nada";
	 $nip2 = "nada";
	 $nip = "nada";
	 $nip1 = "nada";
	 $old = "nada";
    $objPHPExcel = $objReader->load($origen);
//
// Establecer formato para la fecha.
	date_default_timezone_set('America/El_Salvador');
	setlocale(LC_TIME,'es_SV');
	$fecha_actual = date("d-m-Y h:i:s");
	$timestamp = strtotime($fecha_actual);
// N�mero de hoja.
   $numero_de_hoja = 17;
	$numero = 0;	
// 	Recorre el numero de hojas que contenga el libro
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue() != "")
		  {
			
			 //  DATOS GENERALES.
			 /*
			 	$codigo_departamento = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue()));
			 	$codigo_municipio = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
				$codigo_distrito = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue()));
				$nombre_distrito = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue()));
				$codigo_canton = trim(htmlspecialchars($objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue()));
				$descripcion = trim($objPHPExcel->getActiveSheet()->getCell("F".$fila)->getValue());
				//print $query = "INSERT INTO catalogo_abastecimiento (codigo, descripcion) values ('$codigo','$descripcion')";
				//$query = "INSERT INTO catalogo_canton (codigo, descripcion, codigo_departamento, codigo_municipio) values ('$codigo','$descripcion','$codigo_departamento','$codigo_municipio')";
				//print "<br>";
				//$consulta = $dblink -> query($query);
			*/
				//print $codigo . ' - ' . $descripcion  . ' - ' . $codigo_departamento . ' - ' .  $codigo_municipio . ' - ' .  "<br>";
//				$fila++;
			 	$codigo_area = $objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue();
			 	$codigo_dimension = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();
			 	$codigo_subdimension = $objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue();
				$descripcion_subdimension = $objPHPExcel->getActiveSheet()->getCell("F".$fila)->getValue();
			 	$codigo = $objPHPExcel->getActiveSheet()->getCell("G".$fila)->getValue();
			 	$descripcion = trim($objPHPExcel->getActiveSheet()->getCell("H".$fila)->getValue());
			 	$ordenar = $objPHPExcel->getActiveSheet()->getCell("I".$fila)->getValue();
			 	$codigo_cc = '03';
			 	$codigo_servicio_educativo = $objPHPExcel->getActiveSheet()->getCell("E2")->getValue();

				// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
//				$query = "INSERT INTO catalogo_distritos (codigo, descripcion, codigo_departamento, codigo_municipio) VALUES ('$codigo','$descripcion','$codigo_departamento','$codigo_municipio')";
				//$query = "INSERT INTO catalogo_municipios (codigo, descripcion, codigo_departamento) VALUES ('$codigo','$descripcion','$codigo_departamento')";
				//	$query = "INSERT INTO catalogo_departamentos (codigo, descripcion) VALUES ('$codigo','$descripcion')";
					//$query = "INSERT INTO catalogo_area_subdimension (codigo_area, codigo_dimension, codigo, descripcion) values ('$codigo_area','$codigo_dimension','$codigo_subdimension','$descripcion_subdimension')";
					//$query = "INSERT INTO catalogo_area_dimension (codigo, descripcion, codigo_area) VALUES ('$codigo', '$descripcion','$codigo_area')";
				 	$query = "INSERT INTO asignatura (nombre, codigo, codigo_cc, codigo_area, codigo_servicio_educativo, codigo_area_dimension, codigo_area_subdimension, ordenar) 
					VALUES ('$descripcion','$codigo','$codigo_cc','$codigo_area','$codigo_servicio_educativo','$codigo_dimension','$codigo_subdimension','$ordenar')";
				//			$updateQuery = "UPDATE catalogo_canton SET codigo_nuevo_municipio = '$codigo_nuevo_municipio', codigo_distrito = '$codigo_distrito'
//							WHERE codigo_departamento = '$codigo_departamento' and codigo_municipio = '$codigo_viejo_municipio'
//							";
//					$consulta = $dblink -> query($udpateQuery);
				//$query = "INSERT INTO catalogo_canton (codigo, descripcion, codigo_departamento, codigo_nuevo_municipio, codigo_distrito) VALUES ('$codigo_canton','$descripcion','$codigo_departamento','$codigo_municipio','$codigo_distrito')";			
				$consulta = $dblink -> query($query);
			// if(empty($codigo_municipio)){
			// 	print "NO GRABADO";
			// }else{
			// 	$consulta = $dblink -> query($query);
			// 	print $codigo_municipio . ' - ' . $codigo_departamento . ' - ' . $codigo_distrito .  ' - ' . $nombre_distrito . ' - ' . $codigo_canton . ' - ' . $descripcion;
			// }
				 $fila++;
				$numero++;

//			print $codigo_viejo_municipio . ' - ' . $codigo_nuevo_municipio . ' - ' . $codigo_departamento . ' - ' . $codigo_distrito;
			print 'N.º' . $numero . ' ' . $codigo_area . ' - ' . $codigo_dimension . ' - ' . $codigo_subdimension . ' - ' . $codigo . ' - ' . $descripcion . ' - ' . $codigo_cc . ' SE ' . $codigo_servicio_educativo . ' # ' . $ordenar;
			//print $codigo_area . ' - ' . $codigo  . ' - ' . $descripcion . ' - ' . $codigo_cc . ' - ' . $codigo_servicio_educativo;
			print "<br>";
		}	// FIN DEL WHILE PRINCIPAL DE L AHOJA DE CALCULO.

		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/*						$query_p = "SELECT id_productos, codigo, substring(codigo from 1 for 3)::int as codigo_cargo_numero_entero
											FROM catalogo_productos ORDER BY codigo_cargo_numero_entero DESC LIMIT 1";
							// Ejecutamos el Query.
									$consulta_p = $dblink -> query($query_p);
									// Verificar si existen registros.
									if($consulta_p -> rowCount() != 0){
										// convertimos el objeto
										while($listados = $consulta_p -> fetch(PDO::FETCH_BOTH))
										{
											$codigo_entero_p = trim($listados['codigo_cargo_numero_entero']) + 1;
											$codigo_string_p = (string) $codigo_entero_p;
											$codigo_nuevo_p = codigos_nuevos($codigo_string_p);
										}
										// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
										$query_cat = "INSERT INTO catalogo_productos (codigo, descripcion, existencia, codigo_categoria) VALUES ('$codigo_nuevo_p','$nombre','$cantidad','$codigo_nuevo')";
										$consulta_cat = $dblink -> query($query_cat);
									}
									else{
											$codigo_nuevo_p = "001";
										// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
										$query_cat = "INSERT INTO catalogo_productos (codigo, descripcion, existencia, codigo_categoria) VALUES ('$codigo_nuevo_p','$nombre','$cantidad','$codigo_nuevo')";
										$consulta_cat = $dblink -> query($query_cat);}
										
										
													// condici�n
			if((int) $codigo_categoria === $numero){
				$codigo_producto = $codigo_producto + 1;
				
			}else{
				$codigo_producto = 1;
				$numero = $numero + 1;
			}
			$objPHPExcel->getActiveSheet()->SetCellValue("B".$fila, $codigo_producto);*/