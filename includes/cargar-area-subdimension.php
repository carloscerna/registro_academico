<?php
// Variables del POST
    $codigo_area = $_REQUEST['CodigoArea'];
    $codigo_dimension = $_REQUEST['CodigoDimension'];
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
		$query = "SELECT cat_area_asig.codigo as codigo_area, cat_area_asig.descripcion as descripcion_catalogo_area, 
        cat_area_dimension.descripcion as descripcion_area_dimension, cat_area_dimension.codigo_area, cat_area_dimension.codigo as codigo_dimension,
        cat_area_subdimension.codigo as codigo_subdimension, cat_area_subdimension.descripcion as descripcion_subdimension
        FROM catalogo_area_asignatura cat_area_asig
        INNER JOIN catalogo_area_dimension cat_area_dimension ON cat_area_dimension.codigo_area = cat_area_asig.codigo
        INNER JOIN catalogo_area_subdimension cat_area_subdimension ON cat_area_subdimension.codigo_area = cat_area_asig.codigo and cat_area_dimension.codigo = cat_area_subdimension.codigo_dimension
        WHERE cat_area_subdimension.codigo_area = '$codigo_area' and cat_area_subdimension.codigo_dimension = '$codigo_dimension'
        ORDER BY cat_area_dimension.codigo ASC";
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
     // Nombres de los campos de la tabla.
    	 $codigo = trim($listado['codigo_subdimension']); $descripcion = $listado['descripcion_subdimension'];
	 // Rellenando la array.
		$datos[$fila_array]["codigo"] = $codigo;
		$datos[$fila_array]["descripcion"] = ($descripcion);
	   $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>