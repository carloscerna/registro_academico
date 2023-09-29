<?php
session_name('demoUI');
// cambiar a utf-8.
	header("Content-Type: text/html; charset=utf-8");
// Insertar y actualizar tabla de usuarios
// Inicializamos variables de mensajes y JSON
	$respuestaOK = false;
	$mensajeError = "No se puede ejecutar la aplicación";
	$contenidoOK = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
	include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// Validar conexión con la base de datos
if($errorDbConexion == false)
{
	// Validamos qe existan las variables post
	if(isset($_REQUEST) && !empty($_REQUEST))
	{
		if(!empty($_POST['accion_h']))
		{
			$Accion = $_POST["accion_h"];
			// Verificamos las variables de acción
			switch ($Accion) {
				case "EditarContratacion":
				// armando el Query.
					$id_ = $_POST["id_"];
					$query_personal = "SELECT id_personal_salario, codigo_personal, codigo_rubro, codigo_tipo_contratacion, codigo_tipo_descuento, salario, codigo_turno, codigo_horario
							FROM personal_salario WHERE id_personal_salario = '$id_' ORDER BY codigo_personal";
				// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
					$consulta_personal = $dblink -> query($query_personal);
				// Inicializando el array
					$datos=array(); $fila_array = 0;
				// Recorriendo la Tabla con PDO::
					$num = 1;
						if($consulta_personal -> rowCount() != 0){		
							while($listadoPersonal = $consulta_personal -> fetch(PDO::FETCH_BOTH))
							{
								// recopilar los valores de los campos.
									$id_personal_salario = trim($listadoPersonal['id_personal_salario']);
									$cod_personal = trim($listadoPersonal['codigo_personal']);
									$codigo_rubro = trim($listadoPersonal['codigo_rubro']);
									$tipo_descuento = trim($listadoPersonal['codigo_tipo_descuento']);
									$codigo_turno = trim($listadoPersonal['codigo_turno']);
									$codigo_horario = trim($listadoPersonal['codigo_horario']);
									$tipo_contratacion = trim($listadoPersonal['codigo_tipo_contratacion']);
									$salario = trim($listadoPersonal['salario']);
								// pasar a la matriz.
									$datos[$fila_array]["id_personal_salario"] = $id_personal_salario;
									$datos[$fila_array]["codigo_personal"] = $cod_personal;
									$datos[$fila_array]["codigo_rubro"] = $codigo_rubro;
									$datos[$fila_array]["codigo_tipo_descuento"] = $tipo_descuento;
									$datos[$fila_array]["codigo_tipo_contratacion"] = $tipo_contratacion;
									$datos[$fila_array]["codigo_turno"] = $codigo_turno;
									$datos[$fila_array]["codigo_horario"] = $codigo_horario;
									$datos[$fila_array]["salario"] = $salario;
							// Incrementar el valor del array.
								$fila_array++; $num++;
							}
						}else{
							$datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
						}
				break;
				case 'GuardarContratacion':
					$cod_personal = $_POST['cod_personal'];
					$codigo_rubro = $_POST['codigo_rubro'];
					$tipo_contratacion = $_POST['tipo_contratacion'];
					$codigo_turno = $_POST['codigo_turno'];
					$codigo_horario = $_POST['codigo_horario'];
					$tipo_descuento = $_POST['tipo_descuento'];
					$salario = $_POST['salario'];
					//					
					$query = "INSERT INTO personal_salario (codigo_personal, codigo_tipo_contratacion, codigo_rubro, codigo_tipo_descuento, salario, codigo_turno, codigo_horario)
							VALUES ('$cod_personal','$tipo_contratacion','$codigo_rubro','$tipo_descuento','$salario','$codigo_turno','$codigo_horario')";
					// Ejecutamos el query
					$resultadoQuery = $dblink -> query($query);
					//
					if($resultadoQuery == true){
						$respuestaOK = true;
						$mensajeError = "Se ha agregado el registro correctamente";
						// armando el Query. PARA LA TABLA HISTORIAL.
							$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
												cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro
													FROM personal_salario ps
														INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
														INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
														INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
															WHERE ps.codigo_personal = '$cod_personal' ORDER BY ps.codigo_personal";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_personal = $dblink -> query($query_personal);	
						// Recorriendo la Tabla con PDO::
							$verdadero = ""; $num_registros = 0;
							$num_registros = $consulta_personal -> rowCount();
							$num = 1;
								while($listadoPersonal = $consulta_personal -> fetch(PDO::FETCH_BOTH))
								{
								// recopilar los valores de los campos.
								$id_ = trim($listadoPersonal['id_personal_salario']);
								$cod_personal = trim($listadoPersonal['codigo_personal']);
								$nombre_rubro = trim($listadoPersonal['nombre_rubro']);
								$nombre_descuento = trim($listadoPersonal['nombre_descuento']);
								$nombre_contratacion = trim($listadoPersonal['nombre_contratacion']);
								$salario = trim($listadoPersonal['salario']);
								// pasar a la matriz.
								$contenidoOK .= "<tr><td>$num
								<td>'.$id_
								<td>'.$nombre_rubro
								<td>'.$nombre_contratacion
								<td>'.$nombre_descuento
								<td>'.$salario
								<td><a data-accion=editar class='btn btn-xs btn-primary' href='$listadoPersonal[id_personal_salario]'>Editar</a>
								<td><a data-accion=eliminarContratacion class='btn btn-xs btn-warning' href='$listadoPersonal[id_personal_salario]'>Eliminar</a>"
								;
								$num++;
								}
					}
					else{
						$mensajeError = "No se puede guardar el registro en la base de datos";
					}
				break;
				case 'ActualizarContratacion':
					$codigo_personal = $_POST['codigo_personal'];
					$codigo_personal_salario = $_POST['id_p'];
					$codigo_rubro = $_POST['codigo_rubro'];
					$codigo_tipo_contratacion = $_POST['codigo_contratacion'];
					$codigo_tipo_descuento = $_POST['codigo_descuento'];
					$salario = $_POST['salario'];
                    $codigo_turno = $_POST['codigo_turno'];
					$codigo_horario = $_POST['codigo_horario'];
					//					
					$query = sprintf("UPDATE personal_salario SET codigo_rubro = '%s', codigo_tipo_contratacion='%s', codigo_tipo_descuento = '%s', salario = '%s', codigo_turno = '%s', codigo_horario = '%s'
							WHERE id_personal_salario=%d",
							$codigo_rubro, $codigo_tipo_contratacion, $codigo_tipo_descuento, $salario, $codigo_turno, $codigo_horario
							,$codigo_personal_salario);
					// Ejecutamos el query
					$resultadoQuery = $dblink -> query($query);
					// Obtenemos el id de user para edición
					if($resultadoQuery == true){
						$respuestaOK = true;
						$mensajeError = "Se ha actualizado el registro correctamente.";
						// armando el Query. PARA LA TABLA HISTORIAL.
							$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
												cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro
												FROM personal_salario ps
													INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
													INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
													INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
													WHERE ps.codigo_personal = '".
													$codigo_personal."' ORDER BY ps.codigo_personal";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_personal = $dblink -> query($query_personal);	
						// Recorriendo la Tabla con PDO::
							$verdadero = ""; $num_registros = 0;
							$num_registros = $consulta_personal -> rowCount();
							$num = 1;
								while($listadoPersonal = $consulta_personal -> fetch(PDO::FETCH_BOTH))
									{
									// recopilar los valores de los campos.
										$id_personal_salario = trim($listadoPersonal['id_personal_salario']);
										$cod_personal = trim($listadoPersonal['codigo_personal']);
										$nombre_rubro = trim($listadoPersonal['nombre_rubro']);
										$nombre_descuento = trim($listadoPersonal['nombre_descuento']);
										$nombre_contratacion = trim($listadoPersonal['nombre_contratacion']);
										$salario = trim($listadoPersonal['salario']);
									// pasar a la matriz.
										$contenidoOK .= "<tr><td>$num
										<td>'.$id_
										<td>'.$nombre_rubro
										<td>'.$nombre_contratacion
										<td>'.$nombre_descuento
										<td>'.$salario
										<td><a data-accion=editar class='btn btn-xs btn-primary' href='$listadoPersonal[id_personal_salario]'>Editar</a>
										<td><a data-accion=eliminarContratacion class='btn btn-xs btn-warning' href='$listadoPersonal[id_personal_salario]'>Eliminar</a>"
										;
										$num++;
									}
					}
					else{
						$mensajeError = "No se puede actualizar el registro en la base de datos ";
					}
				break;
				case 'BuscarContratacion':
					$cod_personal = $_POST['cod_personal'];
				   // armando el Query. PARA LA TABLA HISTORIAL.
							$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
												cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro
													FROM personal_salario ps
													INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
													INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
													INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
													WHERE ps.codigo_personal = '$cod_personal' ORDER BY ps.codigo_personal";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_personal = $dblink -> query($query_personal);	
						// Recorriendo la Tabla con PDO::
							$verdadero = ""; $num_registros = 0;
							$num_registros = $consulta_personal -> rowCount();
							$num = 1;
							if($num_registros !=0){
							// cambiar el valor de las variables
								$respuestaOK = true;
								$mensajeError = "Se ha agregado el registro correctamente";
								while($listadoPersonal = $consulta_personal -> fetch(PDO::FETCH_BOTH))
									{
									// recopilar los valores de los campos.
									$id_personal_salario = trim($listadoPersonal['id_personal_salario']);
									$cod_personal = trim($listadoPersonal['codigo_personal']);
									$nombre_rubro = trim($listadoPersonal['nombre_rubro']);
									$nombre_descuento = trim($listadoPersonal['nombre_descuento']);
									$nombre_contratacion = trim($listadoPersonal['nombre_contratacion']);
									$salario = trim($listadoPersonal['salario']);
									
									// pasar a la matriz.
									$contenidoOK .= "<tr><td>$num
									<td>'.$id_
									<td>'.$nombre_rubro
									<td>'.$nombre_contratacion
									<td>'.$nombre_descuento
									<td>'.$salario
									<td><a data-accion=editar class='btn btn-xs btn-primary' href='$listadoPersonal[id_personal_salario]'>Editar</a>
									<td><a data-accion=eliminarContratacion class='btn btn-xs btn-warning' href='$listadoPersonal[id_personal_salario]'>Eliminar</a>"
									;
								$num++;
									}
							}
							else{
								$contenidoOK = 'No hay registros de este usuario...';
							}
				break;
			case 'EliminarContratacion':
				$id_ = $_POST["id_"];
				// Armamos el query
				$query = "DELETE FROM personal_salario WHERE id_personal_salario = $id_";
				// Ejecutamos el query
					$count = $dblink -> exec($query);
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Se ha eliminado el registro correctamente'.$query;
					$contenidoOK = 'Se ha Eliminado '.$count.' Registro(s).';
				}else{
					$mensajeError = 'No se ha eliminado el registro'.$query;
				}
				break;
			default:
				$mensajeError = 'Esta acción no se encuentra disponible';
			break;
			}
		}	// condición de la busqueda del nùmero de DUI.
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicación';
}
}
else{
	$mensajeError = 'No se puede establecer conexión con la base de datos';}
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
// Condiciones para Data
if($Accion == 'EditarContratacion'){
	echo json_encode($datos);	
}else{
	echo json_encode($salidaJson);
}
?>