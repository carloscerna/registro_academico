<?php
//session_name('demoUI');
//session_start();
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: text/html;charset=iso-8859-1");
// Insertar y actualizar tabla de usuarios
sleep(0);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicaci�n";
$contenidoOK = "";
$lista = "";
$arreglo = array();
$datos = array();
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexi�n a la base de datos

include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// Validar conexi�n con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acci�n
		switch ($_POST['accion']) {
		case 'BuscarTodos':
                // armando el Query. Para la tabla alumno_matricula.
				$id_ = $_POST['id_x'];
			$query_alumno_matricula = "SELECT am.id_alumno_matricula, am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion, am.codigo_ann_lectivo, am.retirado, 
									am.repitente, am.codigo_turno, am.codigo_alumno, bach.nombre as nombre_modalidad, to_char(am.fecha_ingreso,'dd/mm/yyyy') as fecha_ingreso,
									gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo, 
									tur.nombre as nombre_turno, btrim(bach.nombre || CAST(' ' AS VARCHAR) || gan.nombre || CAST(' ' AS VARCHAR) || sec.nombre || CAST(' ' AS VARCHAR) || CAST(' ' AS VARCHAR) || tur.nombre || CAST(' - ' AS VARCHAR) || ann.nombre) as nombre_todos, 
									btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) as todos
									FROM alumno_matricula am 
									INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
									INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
									INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
									INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
									INNER JOIN turno tur ON tur.codigo = am.codigo_turno 
									WHERE codigo_alumno = '$id_' ORDER BY codigo_ann_lectivo";
			
                // Ejecutamos el Query. para la tabla alumno matricula.
                   $consulta_historial_matricula = $dblink -> query($query_alumno_matricula);
				// Validar si hay registros.
				if($consulta_historial_matricula-> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta_historial_matricula -> fetch(PDO::FETCH_BOTH))
					{
						$arreglo["data"][] = $listado;						
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = true;
					$contenidoOK = '';
					$mensajeError =  'No Registro';
				}
			break;
        
			default:
				$mensajeError = 'Esta acci�n no se encuentra disponible';
			break;
		}
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicaci�n';}
}
else{
	$mensajeError = 'No se puede establecer conexi�n con la base de datos';}
// Salida de la Array con JSON.
	if($_POST["accion"] === "BuscarTodos"){
		echo json_encode($arreglo);	
	}elseif($_POST["accion"] === "BuscarCodigo"){
		echo json_encode($datos);
		}
	else{
		// Armamos array para convertir a JSON
		$salidaJson = array("respuesta" => $respuestaOK,
			"mensaje" => $mensajeError,
			"contenido" => $contenidoOK);
		echo json_encode($salidaJson);
	}
?>