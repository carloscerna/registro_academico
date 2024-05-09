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
$codigo_asignatura_array = array(); // Educación Básica de 1.º a 6.º.
$todos='134P012401';
$codigo_bachillerato = substr($todos,0,2);
$codigo_grado = substr($todos,2,2);
$codigo_annlectivo = substr($todos,6,2);
$num = 0;

// buscar asignaturas dependiendo del Ciclo y Grado.
// Consultar a la tabla codigo asignatura, para generar el codigo individual de cada una de ellas segun el ciclo o bachillerato.
	$query_consulta_asignatura = "SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = '".$codigo_bachillerato."' and codigo_ann_lectivo = '".$codigo_annlectivo."' and codigo_grado = '".$codigo_grado."' ORDER BY codigo_asignatura ASC";
		$result_consulta = $dblink -> query($query_consulta_asignatura);
		while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
		{
			$codigo_asignatura_array[] = $row['codigo_asignatura'];
		}
// Armamos el query.
	$query = "SELECT DISTINCT asig.id_asignatura, asig.nombre, asig.codigo as codigo_asignatura, asig.codigo_servicio_educativo, asig.codigo_cc, 
	asig.codigo_servicio_educativo, asig.codigo_area, asig.estatus, asig.ordenar, asig.codigo_estatus, asig.codigo_area_dimension, asig.codigo_area_subdimension,
	cat_se.descripcion as nombre_servicio_educativo, 
	cat_cc.descripcion as nombre_cc, cat_cc.codigo, cat_area.descripcion as nombre_area, cat_area.codigo,
	cat_area_subdi.codigo, cat_area_subdi.descripcion as descripcion_area_subdimension,
	asig.codigo_area_dimension, asig.codigo_area_subdimension, cat_area_di.descripcion as descripcion_area_dimension
	FROM asignatura asig 
		INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = asig.codigo_servicio_educativo 
		INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc 
		INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area 
		INNER JOIN catalogo_area_dimension cat_area_di ON cat_area_di.codigo = asig.codigo_area_dimension
		INNER JOIN catalogo_area_subdimension cat_area_subdi ON cat_area_subdi.codigo =  asig.codigo_area_subdimension
			WHERE asig.codigo_servicio_educativo = '$codigo_se' and asig.codigo_estatus = '01'
				ORDER BY asig.codigo_area, asig.codigo_area_dimension, asig.codigo_area_subdimension, asig.ordenar";
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
			$nombre_area_dimension = trim($listado['descripcion_area_dimension']);
			$nombre_area_subdimension = trim($listado['descripcion_area_subdimension']);
			// validar el nombre de la asignatura con su area, dimension y subdimension.
				if($nombre_area_dimension == "Ninguno"){
					$nombre_area_dimension_subdimension_asignatura = $nombre_area . " - " . $nombre;
				}else{
					$nombre_area_dimension_subdimension_asignatura = $nombre_area . "-" . $nombre_area_dimension . "-" . $nombre_area_subdimension . "-" . $nombre;
				}
			//
			$datos[$fila_array]["codigo"] = $codigo;
			$datos[$fila_array]["descripcion"] = $nombre_area_dimension_subdimension_asignatura;
				$fila_array++;
		}
	}
// Enviando la matriz con Json.
echo json_encode($datos);
?>