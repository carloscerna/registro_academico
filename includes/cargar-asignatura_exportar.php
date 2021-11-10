<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
// VALIDAR SI ES UN DOCENTE O ES EL ADMINISTRADOR.
if($_SESSION['codigo_perfil'] == '06'){
// ir a la tabla carga docente.
$query = "SELECT DISTINCT cd.codigo_bachillerato, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_turno, cd.codigo_asignatura, grd.nombre as nombre_grado, asi.codigo, asi.nombre as nombre_asignatura 
				from carga_docente cd 
				INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato 
				INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo 
				INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado 
				INNER JOIN turno tur ON tur.codigo = cd.codigo_turno 
				INNER JOIN asignatura asi ON asi.codigo = cd.codigo_asignatura 
				where btrim(cd.codigo_grado || cd.codigo_seccion || cd.codigo_turno) = '".$_POST["elegido"]."' and cd.codigo_bachillerato = '".$_POST["modalidad"]."' and cd.codigo_ann_lectivo = '".$_POST["annlectivo"]. "' and cd.codigo_docente = '".$_SESSION['codigo_personal']."' ORDER BY cd.codigo_asignatura";
}else{
$query = "SELECT DISTINCT ON (aaa.codigo_asignatura) aaa.codigo_asignatura, aaa.codigo_grado, aaa.codigo_sirai, asi.nombre as nombre_asignatura from a_a_a_bach_o_ciclo aaa
			INNER JOIN asignatura asi ON asi.codigo = aaa.codigo_asignatura
				WHERE aaa.codigo_bach_o_ciclo = '$_POST[modalidad]'
				and aaa.codigo_ann_lectivo = '$_POST[annlectivo]'
				and aaa.codigo_grado = '$_POST[grado]'
				ORDER BY aaa.codigo_asignatura";
}
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
			$codigo_asignatura = trim($listado['codigo_asignatura']); $nombre_asignatura = trim($listado['nombre_asignatura']);
	 // Rellenando la array.
		    $datos[$fila_array]["codigo"] = $codigo_asignatura;
			$datos[$fila_array]["descripcion"] = ($nombre_asignatura);
			  $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);
?>