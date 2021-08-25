<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// armando el Query.
$codigo_alumno = $_POST["id_alumno"];
$codigo_ann_lectivo = $_POST["ann_lectivo"];

$query = "SELECT am.id_alumno_matricula, am.codigo_alumno, am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion, am.codigo_turno,
			bach.nombre as nombre_modalidad, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
				from alumno_matricula am
					INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
					INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
					INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
					INNER JOIN turno tur ON tur.codigo = am.codigo_turno
							WHERE am.codigo_ann_lectivo = '".$codigo_ann_lectivo."' and am.codigo_alumno = ".$codigo_alumno;
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
	$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
	if($consulta -> rowCount() != 0)
	{
		while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	  {
		   // Nombres de los campos de la tabla.
			  $codigo_matricula = trim($listado['id_alumno_matricula']);
			  $codigo_modalidad = trim($listado["codigo_bach_o_ciclo"]);
			  $mgst = trim($listado['nombre_modalidad']) . ' ' . trim($listado['nombre_grado']) . ' ' . trim($listado['nombre_seccion']) . ' ' . trim($listado['nombre_turno']);
		  // Rellenando la array.
			  $datos[$fila_array]["mgst"] = $mgst;
			  $datos[$fila_array]["codigo_matricula"] = $codigo_matricula;
			  $datos[$fila_array]["codigo_modalidad"] = $codigo_modalidad;
			  
			  $fila_array++;
		  }
	}else{
		$datos[$fila_array]["mgst"] = "No se encuentra el Registro";
		$datos[$fila_array]["codigo_matricula"] = 0;
		$datos[$fila_array]["codigo_modalidad"] = 0;
	}
// Enviando la matriz con Json.
echo json_encode($datos);	
?>