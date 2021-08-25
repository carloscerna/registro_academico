<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
$query = "SELECT codigo as codigo_turno, nombre as nombre_turno FROM turno ORDER BY codigo";
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
			$codigo_turno = trim($listado['codigo_turno']); $descripcion_turno = trim($listado['nombre_turno']);
         
	 // Rellenando la array.
			$datos[$fila_array]["codigo_turno"] = $codigo_turno;
			$datos[$fila_array]["descripcion_turno"] = ($descripcion_turno);

				$fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);
?>