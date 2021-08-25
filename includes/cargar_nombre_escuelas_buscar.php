<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// armando el Query.
$query = "SELECT DISTINCT codigo, nombre, codigo_departamento, codigo_municipio from catalogo_escuelas
	WHERE codigo = '".$_POST['codigo_ce']."' ORDER BY codigo";
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
	 $codigo = trim($listado['codigo']); $descripcion = trim($listado['nombre']);
	 $codigo_departamento = trim($listado['codigo_departamento']); $codigo_municipio = trim($listado['codigo_municipio']);
	 // Rellenando la array.
         $datos[$fila_array]["codigo"] = $codigo;
		 $datos[$fila_array]["descripcion"] = ($descripcion);
		 $datos[$fila_array]["codigo_departamento"] = ($codigo_departamento);
		 $datos[$fila_array]["codigo_municipio"] = ($codigo_municipio);
	   $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>