<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Armar el query y captuar variable del POST.
	$codigo_annlectivo = $_POST['annlectivo'];
	if(isset($_POST['cd'])){$carga_docente_boolean = $_POST['cd'];}else{$carga_docente_boolean = false;}
	if(isset($_POST['codigo_personal'])){$codigo_docente = $_POST['codigo_personal'];}else{$codigo_docente = "";}
	
	$query = "SELECT p.id_personal as codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || p.apellidos) as nombre_c
						  FROM personal p
							  WHERE p.codigo_estatus = '01' and codigo_cargo = '03' ORDER BY nombre_c";	
   
if($_SESSION['codigo_perfil'] == '04' or $_SESSION['codigo_perfil'] == '05')
{
// Ejecutamos el Query.
	$consulta = $dblink -> query($query);
// Inicializando el array
	$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
    while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
	// Nombres de los campos de la tabla.
		$codigo = $listado['codigo_personal']; $descripcion = trim($listado['nombres']) . ' ' . trim($listado['apellidos']);
	//	Armar query y comparar si sepuede agregar a la matriz.
		if($_SESSION['codigo_perfil'] == '04')
		{
			if($carga_docente_boolean == true){
			$query_org ="SELECT org.codigo_bachillerato, bach.nombre as nombre_modalidad
							FROM organizar_planta_docente_ciclos org
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
							WHERE org.codigo_bachillerato >= '01' and org.codigo_bachillerato <='15' and org.codigo_ann_lectivo = '$codigo_annlectivo' and org.codigo_docente = $codigo_docente";				
			}else{
			$query_org = "SELECT * FROM organizar_planta_docente_ciclos org
							WHERE org.codigo_bachillerato >= '01' and org.codigo_bachillerato <='15' and org.codigo_ann_lectivo = '$codigo_annlectivo' and org.codigo_docente = $codigo";
			}
			$consulta_org = $dblink -> query($query_org);
		}
		
		if($_SESSION['codigo_perfil'] == '05')
		{
		if($carga_docente_boolean == true){
			$query_org ="SELECT org.codigo_bachillerato, bach.nombre as nombre_modalidad
							FROM organizar_planta_docente_ciclos org
							INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
							WHERE org.codigo_bachillerato >= '06' and org.codigo_ann_lectivo = '$codigo_annlectivo' and org.codigo_docente = $codigo_docente";				
			}else{
			$query_org = "SELECT * FROM organizar_planta_docente_ciclos org
							WHERE org.codigo_bachillerato >= '06' and org.codigo_ann_lectivo = '$codigo_annlectivo' and org.codigo_docente = $codigo";
			}
			$consulta_org = $dblink -> query($query_org);
		}
		
		if($consulta_org -> rowCount() !=0)
		{
			if($carga_docente_boolean == true)
			{
				// Rellena datos para la carga académica.
			    while($listado_org = $consulta_org -> fetch(PDO::FETCH_BOTH))
					{
						$codigo_modalidad = $listado_org['codigo_bachillerato'];
						$descripcion_modalidad = $listado_org['nombre_modalidad'];
						
						$datos[$fila_array]["codigo"] = $codigo_modalidad;
						$datos[$fila_array]["descripcion"] = ($descripcion_modalidad);
						$fila_array++;						
					}
			}else{
			// Rellenando la array.
				$datos[$fila_array]["codigo"] = $codigo;
				$datos[$fila_array]["descripcion"] = ($descripcion);
				$fila_array++;
				}
	  }
	  // Salir del bucle si lo que busca solo es la carga académica de un solo docente.
	  if($carga_docente_boolean == true)
	  {
		break;
	  }
    }		
}
else{
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
}

// Enviando la matriz con Json.
echo json_encode($datos);	
?>