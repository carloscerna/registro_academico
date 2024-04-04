<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
// variables/conexion.
$host = 'localhost';
$port = 5432;
$database = 'acomtus';
$username = 'postgres';
$password = 'Orellana';
//Construimos el DSN//
try{
$dsn = "pgsql:host=$host;port=$port;dbname=$database";
}catch(PDOException $e) {
     echo  $e->getMessage();
     $errorDbConexion = true;   
 }
// Creamos el objeto
$dblink = new PDO($dsn, $username, $password);
// Validar la conexión.
if(!$dblink){
 // Variable que indica el status de la conexión a la base de datos
    $errorDbConexion = true;   
};
include($path_root."/registro_academico/includes/funciones.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
	$ruta = $path_root.'/registro_academico/formatos_hoja_de_calculo/CATALOGO DE RIESGOS.xlsx';
  //$trimestre = trim($_REQUEST["periodo_"]);
// variable de la conexi�n dbf.
    $db_link = $dblink;
// Inicializando el array
$datos=array(); $fila_array = 0;
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
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007
   $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
	 $fila = 3;
     $nombre_tabla = "catalogo_montos_ingreso";
    $objPHPExcel = $objReader->load($origen);
//
// Establecer formato para la fecha.
	date_default_timezone_set('America/El_Salvador');
	setlocale(LC_TIME,'es_SV');
	$fecha_actual = date("d-m-Y h:i:s");
	$timestamp = strtotime($fecha_actual);
// N�mero de hoja.
   $numero_de_hoja = 1;
	$numero = 3;	
// 	Recorre el numero de hojas que contenga el libro
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue() != "")
		  {
			 //  DATOS GENERALES.
			 	$codigo = trim($objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue());
				$descripcion = trim($objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue());
            //

				print $codigo . ' - ' . $descripcion  . " -- " .  "<br>";
				$fila++;
				// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
				 	$query = "INSERT INTO $nombre_tabla (codigo, descripcion) VALUES ('$codigo','$descripcion')";
                    
					$consulta = $dblink -> query($query);
		}	// FIN DEL WHILE PRINCIPAL DE L AHOJA DE CALCULO.			
?>