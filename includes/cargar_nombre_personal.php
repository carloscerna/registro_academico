<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// vAROABÑES POST.
$personal_da = "";
if(isset($_POST["personal_da"])){
	$personal_da = $_POST["personal_da"];
}
// Incluimos el archivo de funciones y conexión a la base de datos
	include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// VALIDAR SI ES UN SUBDIRECTOR O DIRECTOR.
	if($_SESSION['codigo_perfil'] == '03'){
// Obtener el valor del turno de la tabla Personal Responsable Licencia.
	$codigo_personal = $_SESSION['codigo_personal'];
	$query = "SELECT codigo_turno FROM personal_responsable_licencia WHERE codigo_personal = '$codigo_personal'";
// Ejecutamos el Query.
	$consulta = $dblink -> query($query);
// Recorriendo la Tabla con PDO::
    while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
		 // Nombres de los campos de la tabla.
			$codigo_turno = $listado['codigo_turno']; 
	}	
// armar query 	
	$query = "SELECT ps.codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || p.apellidos) as nombre_c
				FROM personal_salario ps
				INNER JOIN personal p ON p.id_personal = ps.codigo_personal
				WHERE p.codigo_estatus = '01'and ps.codigo_turno = '$codigo_turno' ORDER BY nombre_c";
}else{
	if($personal_da == "personal_docente_administrativo"){
		// armando el Query.
		$query = "SELECT p.id_personal as codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || p.apellidos) as nombre_c
					FROM personal p
						WHERE p.codigo_estatus = '01' and codigo_cargo >='02' and codigo_cargo <= '05' ORDER BY nombre_c";	
	}else{
		// armando el Query.
		$query = "SELECT p.id_personal as codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || p.apellidos) as nombre_c
						FROM personal p
							WHERE p.codigo_estatus = '01' ORDER BY nombre_c";
		}
}
// Ejecutamos el Query.
	$consulta = $dblink -> query($query);
// Inicializando el array
	$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
	while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
			$codigo = $listado['codigo_personal']; $descripcion = trim($listado['nombres']) . ' ' . trim($listado['apellidos']);
		 // Rellenando la array.
			$datos[$fila_array]["codigo"] = $codigo;
			$datos[$fila_array]["descripcion"] = ($descripcion);
			$fila_array++;
    }
// Enviando la matriz con Json.
	echo json_encode($datos);	
?>