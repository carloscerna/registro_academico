<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
	$usuario = "root";
	$contrasena = "admin";  // en mi caso tengo contraseña pero en casa caso introducidla aquí.
	$servidor = "localhost";
	$basededatos = "registro_civil";
//  Conexión
	$conexion = mysqli_connect( $servidor, $usuario, $contrasena) or die ("No se ha podido conectar al servidor de Base de datos");
// Base de dtaos
	$db = mysqli_select_db( $conexion, $basededatos ) or die ( "Upps! Pues va a ser que no se ha podido conectar a la base de datos" );
//
	set_time_limit(0);
    ini_set("memory_limit","2000M");
// Inicializando el array
$datos=array(); $fila_array = 0;
// variables. del post.
$ruta = $path_root.'/registro_academico/formatos_hoja_de_calculo/catalogos/catalogo.xlsx';
// Inicializando el array
$datos=array(); $fila_array = 0;
// call the autoload
  require $path_root."/registro_academico/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
  use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
  use PhpOffice\PhpSpreadsheet\Read\Xlsx;
// Creamos un objeto Spreadsheet object
  $objPHPExcel = new Spreadsheet();
// set codings.
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007
	$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
	 $fila = 1;
    $objPHPExcel = $objReader->load($origen);
// N�mero de hoja.
   $numero_de_hoja = 0;
   //$total_de_hojas = $objPHPExcel->getSheetCount();
// 	Recorre el numero de hojas que contenga el libro

       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
	
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue() != "")
		  {
			 //  DATOS GENERALES.
			 $codigo = $objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue();
			 $descripcion = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();
			// $codigo_departamento = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();

			$query = "INSERT INTO catalogo_tipo_documento (codigo, descripcion) VALUES ('$codigo','$descripcion')";
		// ejecutar la consulta.
			$resultado = mysqli_query($conexion, $query) or die ( "Algo ha ido mal en la consulta a la base de datos");
				print "<p>".$fila."</p>";
				print "<p>"."'$codigo' - '$descripcion' "."</p>";
				
				$fila = $fila + 1;
		}
		print $fila;
?>