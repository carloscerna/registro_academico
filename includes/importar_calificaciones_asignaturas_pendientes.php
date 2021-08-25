<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
	$codigo_institucion = $_SESSION['codigo_institucion'];
	$ruta = '../files/' . $codigo_institucion . "/" . trim($_REQUEST["nombre_archivo_"]);
	  $trimestre = trim($_REQUEST["periodo_"]);
	  $nombre_archivo = trim($_REQUEST["nombre_archivo_"]);
// variable de la conexi�n dbf.
    $db_link = $dblink;
// Inicializando el array
	$datos=array(); $fila_array = 0;
	$datos[$fila_array]["registro"] = 'Si_registro';
	$datos[$fila_array]["nombre_archivo"] = $nombre_archivo;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////  
// iniciar PhpSpreadsheet
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
    use PhpOffice\PhpSpreadsheet\Shared\Date;
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
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';

// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

// Leemos un archivo Excel 2007
    //$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
    $objPHPExcel = $objReader->load($origen);

// N�mero de hoja.
   //$numero_de_hoja = 0;
   $total_de_hojas = $objPHPExcel->getSheetCount();


    for($numero_de_hoja=0;$numero_de_hoja<$total_de_hojas;$numero_de_hoja++)
    {	        
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
        $fila = 5; $num = 1;
	
        //	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
        while($objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue() != "")
        {
            //  DATOS GENERALES.
            $codigo_interno = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();
            $codigo_matricula = $objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue();
            $codigo_asignatura = $objPHPExcel->getActiveSheet()->getCell("G".$fila)->getValue();
            $nivel = $objPHPExcel->getActiveSheet()->getCell("K".$fila)->getValue();
            //  notas de actividades, recuperacion, periodos o trimestres.
            $nota_1 = $objPHPExcel->getActiveSheet()->getCell("L".$fila)->getValue();
            $nota_2 = $objPHPExcel->getActiveSheet()->getCell("M".$fila)->getValue();
            $nota_3 = $objPHPExcel->getActiveSheet()->getCell("N".$fila)->getValue();
            $nota_4 = $objPHPExcel->getActiveSheet()->getCell("O".$fila)->getValue();
            $nota_5 = $objPHPExcel->getActiveSheet()->getCell("P".$fila)->getValue();
            //$nota_final = $objPHPExcel->getActiveSheet()->getCell("Q".$fila)->getoldCalculatedValue();
            $nota_final = $objPHPExcel->getActiveSheet()->getCell("R".$fila)->getoldCalculatedValue();

            // CONSULTA Y ACTUALIZACIÓN DE DATOS.
            // consulta sobre el codigo del docente y el a?o lectivo.
                $query = "SELECT * from nota where codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asignatura'";	
            // Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
                $consulta_ = $dblink -> query($query);
            // recorremos la consulta. para asignarle a las variables.		
                if($consulta_ -> rowCount() != 0){
                // recorrer consulta.
                while($listado_ = $consulta_ -> fetch(PDO::FETCH_BOTH))
                {
                        //***********************************************************************************************
                        //	CALCULAR DE PRIMERO A SEXTO GRADO.
                        //	CALIFICACIÓN POR TRIMESTRE ENTERA Y CALIFICACIÓN FINAL EN ENTERO.
                        if($nivel >= "02" and $nivel <= "05"){
                            // Actualizar todas.
                            $query_actualizar = "UPDATE nota SET nota_p_p_1 = '$nota_1', nota_p_p_2 = '$nota_2', nota_p_p_3 = '$nota_3', nota_final = '$nota_final'
                                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asignatura'";
                            // ejecutar query.
                            $consulta_actualizar = $dblink -> query($query_actualizar);
                        }
                        //***********************************************************************************************
                        //	EDUCACIÓN MEDIA
                        if($nivel >= "06" and $nivel <= "09"){
                            // Actualizar todas.
                            $query_actualizar = "UPDATE nota SET nota_p_p_1 = '$nota_1', nota_p_p_2 = '$nota_2', nota_p_p_3 = '$nota_3', nota_p_p_4 = '$nota_4', nota_final = '$nota_final'
                                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asignatura'";
                            // ejecutar query.
                            $consulta_actualizar = $dblink -> query($query_actualizar);
                        }
                        //***********************************************************************************************
                        //	NOCTURNA
                        if($nivel >= "10" and $nivel <= "11"){
                            // Actualizar todas.
                            $query_actualizar = "UPDATE nota SET nota_p_p_1 = '$nota_1', nota_p_p_2 = '$nota_2', nota_p_p_3 = '$nota_3', nota_p_p_4 = '$nota_4', nota_p_p_5 = '$nota_p_p_5', nota_final = '$nota_final'
                                WHERE codigo_alumno = '$codigo_interno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asignatura'";
                            // ejecutar query.
                            $consulta_actualizar = $dblink -> query($query_actualizar);
                        }
                }			
            }
            // INCREMENTAR I PARA LA COLUMNA de excel.
            $fila++; $num++;
        }
}	// el for que recorre segun el numero de hojas que existan.
// Enviando la matriz con Json.
echo json_encode($datos);
?>