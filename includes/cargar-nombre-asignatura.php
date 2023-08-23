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
$codigo_se_post = explode("-",$_POST["codigo_grado_se"]);
$codigo_se = $codigo_se_post[1];
// Armamos el query.
	$query = "SELECT asig.id_asignatura, asig.nombre, asig.codigo as codigo_asignatura, asig.codigo_servicio_educativo, asig.codigo_cc, 
			asig.codigo_servicio_educativo, asig.codigo_area, asig.estatus, asig.ordenar, asig.codigo_estatus,
			cat_se.descripcion as nombre_servicio_educativo, 
			cat_cc.descripcion as nombre_cc, cat_cc.codigo,
			cat_area.descripcion as nombre_area, cat_area.codigo
			FROM asignatura asig
			INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = asig.codigo_servicio_educativo
			INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
			INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
			WHERE asig.codigo_servicio_educativo = '$codigo_se' and asig.codigo_estatus = '01'
				ORDER BY asig.estatus DESC, asig.ordenar ASC, asig.codigo_area";
// Ejecutamos el Query.
	$consulta = $dblink -> query($query);
// consultar si existen registros
	if($consulta -> rowCount() != 0){
		// Inicializando el array
		$datos=array(); $fila_array = 0;
		// convertimos el objeto
		while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
		{
			// variables
			$codigo = trim($listado['codigo_asignatura']);
			$nombre = trim($listado['nombre']);
			$id_ = trim($listado['id_asignatura']);
			$codigo_se = trim($listado['codigo_servicio_educativo']);
			$nombre_se = trim($listado['nombre_servicio_educativo']);
			$codigo_cc = trim($listado['codigo_cc']);
			$nombre_cc = trim($listado['nombre_cc']);
			$codigo_area = trim($listado['codigo_area']);
			$nombre_area = trim($listado['nombre_area']);
			$estatus = trim($listado['codigo_estatus']);
			$ordenar = trim($listado['ordenar']);
			//
			$datos[$fila_array]["codigo"] = $codigo;
			$datos[$fila_array]["descripcion"] = ($nombre);
				$fila_array++;
		}
	}
// Enviando la matriz con Json.
echo json_encode($datos);
?>