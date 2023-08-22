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
	$query = "SELECT org.id_grados_secciones, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_ann_lectivo, org.codigo_turno,
	cat_se.descripcion as descripcion_se, org.codigo_servicio_educativo,
	ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
		FROM organizacion_grados_secciones org 
			INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
			INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
			INNER JOIN turno tur ON tur.codigo = org.codigo_turno
			INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = org.codigo_servicio_educativo
				WHERE org.codigo_ann_lectivo = '$codigo_annlectivo' and org.codigo_bachillerato = '$codigo_modalidad'
					ORDER BY org.codigo_ann_lectivo, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_turno";
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
			$datos[$fila_array]["codigo"] = $codigo_grado . $codigo_servicio_educativo;
			$datos[$fila_array]["descripcion"] = ($nombre_grado);
				$fila_array++;
		}
		// valores unicos de la array
		$datos = array_unique($datos);
	}
/* 
//if(isset($_POST["codigo_grado"]))
//{
  $codigo_grado = trim($_POST["codigo_grado"]); 
//}

// CONDIONAL PARA EDUCACION INICIAL sección 1, 2, 3.
if($codigo_modalidad == "01")
{
	if($codigo_grado == 'I1'){$codigo_se = "01";}
	if($codigo_grado == 'I2'){$codigo_se = "02";}
	if($codigo_grado == 'I3'){$codigo_se = "03";}
	
}

// CONDIONAL PARA Parvularia, sección 4, 5, 6.
if($codigo_modalidad == "02")
{
	if($codigo_grado == '4P'){$codigo_se = "04";}
	if($codigo_grado == '5P'){$codigo_se = "05";}
	if($codigo_grado == '6P'){$codigo_se = "06";}
	
}
// CONDIONAL PARA PRIMER CICLO Y SEGUNDO CICLO
if($codigo_modalidad >="03" and $codigo_modalidad <= "04")
{
	$codigo_se = "07";
}
// CONDIONAL PARA PRIMER CICLO Y SEGUNDO CICLO
if($codigo_modalidad >="05" and $codigo_modalidad <= "05")
{
	$codigo_se = "08";
}
// CONDIONAL PARA EL BACHILLERATO GENERAL
if($codigo_modalidad == "06")
{
		$codigo_se = "09";
}
// CONDIONAL PARA EL BACHILLERATO TECNICO 
if($codigo_modalidad == "07")
{
		$codigo_se = "10";
}
// CONDIONAL PARA EL BACHILLERATO TECNICO  TERCER AÑO CONTADOR
if($codigo_modalidad == "09")
{
		$codigo_se = "11";
}
// CONDIONAL PARA EL TERCER CICLO NOCTURNA
if($codigo_modalidad == "10")
{
		$codigo_se = "13";
}

if($_SESSION['codigo_perfil'] == '06'){

}
else{
 /*
   $query = "SELECT codigo as codigo_asignatura, nombre as nombre_asignatura
            FROM asignatura
              WHERE imprimir = 'true' and codigo_servicio_educativo = '$codigo_se'
                ORDER BY codigo, codigo_servicio_educativo";
*/
 /*$query = "SELECT codigo as codigo_asignatura, nombre as nombre_asignatura
            FROM asignatura
              WHERE imprimir = 'true' and codigo_servicio_educativo = '$codigo_se'
                ORDER BY codigo_servicio_educativo, codigo_area, id_asignatura";
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
		    $datos[$fila_array]["codigo_asignatura"] = $codigo_asignatura;
			$datos[$fila_array]["descripcion_asignatura"] = ($nombre_asignatura);
			  $fila_array++;
        } */
// Enviando la matriz con Json.
echo json_encode($datos);
?>