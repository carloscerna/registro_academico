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
		if(!empty($_POST['accion']))
		{
			$Accion = $_POST["accion"];
			// Verificamos las variables de acción
			switch ($Accion) {
				case "EditarContratacion":
				// armando el Query.
					$id_ = $_POST["id_"];
					$query_personal = "SELECT ps.fecha, ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario, ps.codigo_turno, ps.codigo_horario,
										btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) AS nombre_empleado,
										cat_cargo.descripcion as nombre_cargo
										FROM personal_salario ps
											INNER JOIN personal p ON p.id_personal = ps.codigo_personal
											INNER JOIN catalogo_cargo cat_cargo ON cat_cargo.codigo = p.codigo_cargo
											WHERE ps.id_personal_salario = '$id_' 
												ORDER BY ps.codigo_personal";
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
									$fecha = trim($listadoPersonal['fecha']);
									$cod_personal = trim($listadoPersonal['codigo_personal']);
									$nombre_personal = trim($listadoPersonal['nombre_empleado']);
									$nombre_cargo = trim($listadoPersonal['nombre_cargo']);
									$codigo_rubro = trim($listadoPersonal['codigo_rubro']);
									$tipo_descuento = trim($listadoPersonal['codigo_tipo_descuento']);
									$codigo_turno = trim($listadoPersonal['codigo_turno']);
									$codigo_horario = trim($listadoPersonal['codigo_horario']);
									$tipo_contratacion = trim($listadoPersonal['codigo_tipo_contratacion']);
									$salario = trim($listadoPersonal['salario']);
								// pasar a la matriz.
									$datos[$fila_array]["id_personal_salario"] = $id_personal_salario;
									$datos[$fila_array]["fecha"] = $fecha;
									$datos[$fila_array]["codigo_personal"] = $cod_personal;
									$datos[$fila_array]["nombre_personal"] = $nombre_personal;
									$datos[$fila_array]["nombre_cargo"] = $nombre_cargo;
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
					$codigo_personal = $_POST['codigo_personal'];
					$codigo_rubro = $_POST['lstRubro'];
					$tipo_contratacion = $_POST['lstTipoContratacion'];
					$codigo_turno = $_POST['lstTurno'];
					$codigo_horario = $_POST['lstHorario'];
					$tipo_descuento = $_POST['lstDescuento'];
					$salario = $_POST['SalarioContratacion'];
					$fecha = $_POST['FechaContratacion'];
					//					
					$query = "INSERT INTO personal_salario (fecha, codigo_personal, codigo_tipo_contratacion, codigo_rubro, codigo_tipo_descuento, salario, codigo_turno, codigo_horario)
							VALUES ('$fecha','$codigo_personal','$tipo_contratacion','$codigo_rubro','$tipo_descuento','$salario','$codigo_turno','$codigo_horario')";
					// Ejecutamos el query
					$resultadoQuery = $dblink -> query($query);
					//
					if($resultadoQuery == true){
						$respuestaOK = true;
						$mensajeError = "Se ha agregado el registro correctamente";
						// funciones...
							BuscarPersonalSalario();

					}else{
						$mensajeError = "No se puede guardar el registro en la base de datos";
					}
				break;
				case 'ActualizarContratacion':
					$id_ = $_POST["id_"];
					$codigo_personal = $_POST['codigo_personal'];
					$codigo_rubro = $_POST['lstRubro'];
					$tipo_contratacion = $_POST['lstTipoContratacion'];
					$codigo_turno = $_POST['lstTurno'];
					$codigo_horario = $_POST['lstHorario'];
					$tipo_descuento = $_POST['lstDescuento'];
					$salario = $_POST['SalarioContratacion'];
					$fecha = $_POST['FechaContratacion'];
					//					
					$query = "UPDATE personal_salario SET codigo_rubro = '$codigo_rubro', 
								codigo_tipo_contratacion = '$tipo_contratacion', codigo_tipo_descuento = '$tipo_descuento',
								salario = '$salario', codigo_turno = '$codigo_turno', codigo_horario = '$codigo_horario', fecha = '$fecha'
									WHERE id_personal_salario= '$id_'";
					// Ejecutamos el query
					$resultadoQuery = $dblink -> query($query);
					// Obtenemos el id de user para edición
					if($resultadoQuery == true){
						$respuestaOK = true;
						$codigo_personal = $_POST['codigo_personal'];
						$mensajeError = "Se ha Actualizado el registro correctamente";
						// Funcion....
							BuscarPersonalSalario();
					}
					else{
						$mensajeError = "No se puede actualizar el registro en la base de datos ";
					}
				break;
				case 'BuscarContratacion':
					$codigo_personal = $_POST['codigo_personal'];
					$mensajeError = "Se ha Encontrado el registro correctamente";
					// Funcion....
						BuscarPersonalSalario();
				break;
			case 'EliminarContratacion':
				$id_ = $_POST["id_"];
				$codigo_personal = $_POST['codigo_personal'];
				$mensajeError = "Se ha Encontrado el registro correctamente";
				// Armamos el query
				$query = "DELETE FROM personal_salario WHERE id_personal_salario = $id_";
				// Ejecutamos el query
					$count = $dblink -> exec($query);
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Se ha eliminado el registro correctamente';
					$contenidoOK = 'Se ha Eliminado '.$count.' Registro(s).';
					// Funcion....
						BuscarPersonalSalario();
				}else{
					$mensajeError = 'No se ha eliminado el registro';
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
// funciones...
function BuscarPersonalSalario(){
	global $codigo_personal, $dblink, $contenidoOK, $respuestaOK, $mensajeError;
	// armando el Query. PARA LA TABLA HISTORIAL.
	$query_personal = "SELECT ps.fecha, ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
	cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro,
	cat_tur.nombre as nombre_turno
		FROM personal_salario ps
		INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
		INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
		INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
		INNER JOIN turno cat_tur ON cat_tur.codigo = ps.codigo_turno
		WHERE ps.codigo_personal = '$codigo_personal' ORDER BY ps.codigo_personal";
	// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	$consulta_personal = $dblink -> query($query_personal);	
	// Recorriendo la Tabla con PDO::
	$num_registros = 0;
	$num_registros = $consulta_personal -> rowCount();
	$num = 1;
	if($num_registros !=0){
	// cambiar el valor de las variables
		$respuestaOK = true;
	//
		while($listadoPersonal = $consulta_personal -> fetch(PDO::FETCH_BOTH))
			{
			// recopilar los valores de los campos.
			$id_ = trim($listadoPersonal['id_personal_salario']);
			$cod_personal = trim($listadoPersonal['codigo_personal']);
			$nombre_rubro = trim($listadoPersonal['nombre_rubro']);
			$nombre_descuento = trim($listadoPersonal['nombre_descuento']);
			$nombre_turno = trim($listadoPersonal['nombre_turno']);
			$nombre_contratacion = trim($listadoPersonal['nombre_contratacion']);
			$salario = trim($listadoPersonal['salario']);
			$fecha = cambiaf_a_normal(trim($listadoPersonal['fecha']));
			
			// pasar a la matriz.
			$contenidoOK .= "<tr>
			<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
			<td>$num
			<td>$id_
			<td>$fecha
			<td>$nombre_rubro
			<td>$nombre_contratacion
			<td>$nombre_turno
			<td>$salario
			<td><a data-accion=EditarContratacion class='btn btn-xs btn-info' href=$id_ tabindex='-1' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fad fa-edit'></i></a>
			<a data-accion=EliminarContratacion class='btn btn-xs btn-warning' href=$id_ tabindex='-1' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fad fa-trash'></i></a>
			";
		$num++;
			}
	}
	else{
		$contenidoOK = 'No hay registros de este usuario...';
	}
}
?>