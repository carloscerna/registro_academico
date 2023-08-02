<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
	$verificar_ann_lectivo = "no";
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
if(isset($_POST["verificar_ann_lectivo"])){
	$verificar_ann_lectivo = $_POST["verificar_ann_lectivo"];
	}
if($verificar_ann_lectivo == "si")
	{
		$query = "SELECT codigo, descripcion FROM libro_catalogo_ann_lectivo WHERE estatus = 't' ORDER BY codigo DESC ";}
	else{
		$query = "SELECT codigo, descripcion FROM libro_catalogo_ann_lectivo ORDER BY codigo DESC ";
	}
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
	 $codigo = trim($listado['codigo']); $descripcion = $listado['descripcion'];
	 // Rellenando la array.
         $datos[$fila_array]["codigo"] = $codigo;
	 $datos[$fila_array]["descripcion"] = utf8_encode($descripcion);
	   $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>