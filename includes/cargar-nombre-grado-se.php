<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// VALIDAR SI ES UN DOCENTE O ES EL ADMINISTRADOR.
$codigo_se = "";
$codigo_grado = "";
$codigo_modalidad = trim($_POST["codigo_modalidad"]);
$codigo_annlectivo = trim($_POST["codigo_annlectivo"]);
	// Armamos el query.
	$query = "SELECT DISTINCT gr.nombre as nombre_grado, org.codigo_grado, 
		org.codigo_servicio_educativo
		FROM organizacion_grados_secciones org 
			INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
			INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
			INNER JOIN turno tur ON tur.codigo = org.codigo_turno
			INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = org.codigo_servicio_educativo
				WHERE org.codigo_ann_lectivo = '$codigo_annlectivo' and org.codigo_bachillerato = '$codigo_modalidad'
					ORDER BY org.codigo_grado, nombre_grado";
	// Ejecutamos el Query.
	$consulta = $dblink -> query($query);
	// Inicializando el array
	$datos=array(); $fila_array = 0;
	//
	if($consulta -> rowCount() != 0){
		// convertimos el objeto
		while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
		{
			$codigo_grado = trim($listado['codigo_grado']);
			$nombre_grado = trim($listado['nombre_grado']);
			$codigo_servicio_educativo = trim($listado['codigo_servicio_educativo']);
			//
			$datos[$fila_array]["codigo"] = $codigo_grado ."-". $codigo_servicio_educativo;
			$datos[$fila_array]["descripcion"] = ($nombre_grado);
				$fila_array++;
		}
	}
// Enviando la matriz con Json.
echo json_encode($datos);
?>