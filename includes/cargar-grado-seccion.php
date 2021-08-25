<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
// VALIDAR SI ES UN DOCENTE O ES EL ADMINISTRADOR.
if($_SESSION['codigo_perfil'] == '06'){
// ir a la tabla carga docente.
$query = "SELECT DISTINCT cd.codigo_bachillerato, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno,
		sec.nombre as nombre_seccion, grd.nombre as nombre_grado, tur.nombre as nombre_turno
		from carga_docente cd
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
		INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
		INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
		INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
        INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
		where cd.codigo_bachillerato = '".$_POST["elegido"]."' and cd.codigo_ann_lectivo = '".$_POST["ann"]. "' and cd.codigo_docente = '".$_SESSION['codigo_personal']."' ORDER BY cd.codigo_bachillerato";
}else{
$query = "SELECT orgs.codigo_bachillerato, orgs.codigo_ann_lectivo, orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_turno,
		sec.nombre as nombre_seccion, grd.nombre as nombre_grado, tur.nombre as nombre_turno
		from organizacion_grados_secciones orgs
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgs.codigo_bachillerato
		INNER JOIN ann_lectivo ann ON ann.codigo = orgs.codigo_ann_lectivo
		INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
		INNER JOIN grado_ano grd ON grd.codigo = orgs.codigo_grado
        INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
		where orgs.codigo_bachillerato = '".$_POST["elegido"]."' and orgs.codigo_ann_lectivo = '".$_POST["ann"].
		"' ORDER BY orgs.codigo_grado, orgs.codigo_seccion";
}
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
			$codigo_grado = trim($listado['codigo_grado']); $descripcion_grado = trim($listado['nombre_grado']);
			$codigo_seccion = trim($listado['codigo_seccion']); $descripcion_seccion = trim($listado['nombre_seccion']);
			$codigo_turno = trim($listado['codigo_turno']); $descripcion_turno = trim($listado['nombre_turno']);
         
	 // Rellenando la array.
			$datos[$fila_array]["codigo_grado"] = $codigo_grado;
			$datos[$fila_array]["descripcion_grado"] = ($descripcion_grado);

			$datos[$fila_array]["codigo_seccion"] = $codigo_seccion;
			$datos[$fila_array]["descripcion_seccion"] = ($descripcion_seccion);
         
			$datos[$fila_array]["codigo_turno"] = $codigo_turno;
			$datos[$fila_array]["descripcion_turno"] = ($descripcion_turno);
				$fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);
?>