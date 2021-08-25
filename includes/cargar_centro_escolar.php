<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// armando el Query.
$query = "SELECT DISTINCT codigo_institucion, nombre_institucion from informacion_institucion ORDER BY codigo_institucion";
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
	 $codigo = trim($listado['codigo_institucion']); $descripcion = trim($listado['nombre_institucion']);
	 // Rellenando la array.
         $datos[$fila_array]["codigo"] = $codigo;
		 $datos[$fila_array]["descripcion"] = ($descripcion);
	   $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>