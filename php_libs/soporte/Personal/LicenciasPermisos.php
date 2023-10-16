<?php
session_name('demoUI');
//session_start();
// Script para ejecutar AJAX
// cambiar a utf-8.
	header("Content-Type: text/html; charset=utf-8");
// Insertar y actualizar tabla de usuarios
//sleep(1);
// Inicializamos variables de mensajes y JSON
	$respuestaOK = false;
	$mensajeError = "No se puede ejecutar la aplicación";
	$contenidoOK = "";
	$encabezado = "";
	$Accion = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
	include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
	include($path_root."/registro_academico/includes/funciones.php");
// Validar conexión con la base de datos
if($errorDbConexion == false)
{
	// Validamos qe existan las variables post
	if(isset($_REQUEST) && !empty($_REQUEST))
	{
		if(!empty($_POST['accion']))
		{
			// Variable
				$Accion = $_POST['accion'];
				$codigo_personal = $_POST['codigo_personal'];
			// Verificamos las variables de acción
			switch ($Accion) {
				case 'BuscarContratacion':
					// VALIDAR SI ES UN SUBDIRECTOR O DIRECTOR.
						if($_SESSION['codigo_perfil'] == '03'){
					// Obtener el valor del turno de la tabla Personal Responsable Licencia.
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
					$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario, ps.codigo_turno,
												cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro,
												tur.codigo as codigo_turno, tur.nombre as nombre_turno, cat_h.inicio as horario_inicio, cat_h.fin as horario_fin
										FROM personal_salario ps
										INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
										INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
										INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
										INNER JOIN turno tur ON tur.codigo = ps.codigo_turno
										INNER JOIN catalogo_horario cat_h ON cat_h.codigo = ps.codigo_horario
										WHERE ps.codigo_personal = '$codigo_personal' and ps.codigo_turno = '$codigo_turno' ORDER BY ps.codigo_personal";
					}else{
					// armando el Query. PARA LA TABLA HISTORIAL.
					$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario, ps.codigo_turno,
												cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro,
												tur.codigo as codigo_turno, tur.nombre as nombre_turno, cat_h.inicio as horario_inicio, cat_h.fin as horario_fin
									FROM personal_salario ps
									INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
									INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
									INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
									INNER JOIN turno tur ON tur.codigo = ps.codigo_turno
									INNER JOIN catalogo_horario cat_h ON cat_h.codigo = ps.codigo_horario
									WHERE ps.codigo_personal = '$codigo_personal' ORDER BY ps.codigo_personal";
					}
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
								// recopilar los valores de los campos.
								$id_personal_salario = trim($listadoPersonal['id_personal_salario']);
								$cod_personal = trim($listadoPersonal['codigo_personal']);
								$codigo_rubro = trim($listadoPersonal['codigo_rubro']);
								$tipo_descuento = trim($listadoPersonal['codigo_tipo_descuento']);
								$tipo_contratacion = trim($listadoPersonal['codigo_tipo_contratacion']);
								$nombre_contratacion = trim($listadoPersonal['nombre_contratacion']);
								$codigo_turno = trim($listadoPersonal['codigo_turno']);
								$nombre_turno = trim($listadoPersonal['nombre_turno']);
								$horario_inicio = trim($listadoPersonal['horario_inicio']);
								$horario_fin = trim($listadoPersonal['horario_fin']);
								$salario = trim($listadoPersonal['salario']);
							
							// pasar a la matriz.
							$datos[$fila_array]["id_personal_salario"] = $id_personal_salario;
							$datos[$fila_array]["codigo_personal"] = $cod_personal;
							$datos[$fila_array]["codigo_rubro"] = $codigo_rubro;
							$datos[$fila_array]["codigo_tipo_descuento"] = $tipo_descuento;
							$datos[$fila_array]["codigo_tipo_contratacion"] = $tipo_contratacion;
							$datos[$fila_array]["nombre_contratacion"] = $nombre_contratacion;
							$datos[$fila_array]["codigo_turno"] = $codigo_turno;
							$datos[$fila_array]["nombre_turno"] = $nombre_turno;
							$datos[$fila_array]["salario"] = $salario;
							$datos[$fila_array]["horario_inicio"] = $horario_inicio;
							$datos[$fila_array]["horario_fin"] = $horario_fin;
					
						// Incrementar el valor del array.
							$fila_array++; $num++;
						}
					}
					else{
					$datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
					}
				break;
				case 'GuardarLicenciasPermisos':
					$codigo_personal = $_POST['lstnombres'];
					$fecha = $_POST['txtFechaLyP_inicio'];
					$fecha_inicio = ($_POST['txtFechaLyP_inicio']);
					$fecha_fin = ($_POST['txtFechaLyP_fin']);
					$dia = $_POST['dia'];
					$hora = $_POST['hora'];
					$minutos = $_POST['minutos'];
					$codigo_licencia = $_POST['lstTipoLicencia_1'];
					$codigo_turno = substr($_POST['lstContratacion_1'],2,2);
					$observacion = $_POST['observaciones'];
					$hora_inicio = $_POST['hora_i'];
					$hora_fin = $_POST['hora_f'];
					$codigo_contratacion = substr($_POST['lstContratacion_1'],0,2);
					$codigo_contratacion_turno = $codigo_personal . $codigo_contratacion . $codigo_turno . $codigo_licencia;
					
					// Verificar si el Registro no Existe.
					$query_busqueda = "SELECT * from personal_licencias_permisos WHERE fecha = '$fecha' and btrim(codigo_personal || codigo_contratacion || codigo_turno || codigo_licencia_permiso) = '$codigo_contratacion_turno'";
					// Eejcutamos query.
					$consulta_busqueda = $dblink -> query($query_busqueda);

					$num_registros = $consulta_busqueda -> rowCount();
					
					if($num_registros !=0){
						// Si existen registros.
							$respuestaOK = false;
							$mensajeError = "Si Existe";
							$contenidoOK = "El Registro Ya Existe";
					}
					else{
						// Preparar query y condiciones para grabar una fecha o diferentes.
						 // Variables para el proceso de repetior el proceso de las fechas.
						$datetime1 = new DateTime($fecha_inicio);
						$datetime2 = new DateTime($fecha_fin);
						$interval = $datetime1->diff($datetime2);
						$resultado = $interval->format('%a');
						
						    for($i=0;$i<=$resultado;$i++)
						    {
								if($i == 0){
									//Query
									$query = "INSERT INTO personal_licencias_permisos (fecha, dia, hora, minutos, codigo_personal, codigo_licencia_permiso, codigo_turno, observacion, hora_inicio, hora_fin, codigo_contratacion)
									VALUES ('$fecha_inicio','$dia','$hora','$minutos','$codigo_personal','$codigo_licencia','$codigo_turno','$observacion','$hora_inicio','$hora_fin','$codigo_contratacion')";
									// Ejecutamos el query
									$resultadoQuery = $dblink -> query($query);
								}else{
									// Agregar el valor a la fecha o sea sumar el día.
									$fecha_nueva = new DateTime($fecha_inicio);
									$fecha_nueva->add(new DateInterval('P'.$i.'D'));
									$fecha_cambiada=$fecha_nueva->format('Y-m-d');
									//print $fecha_cambiada;
									//Query
									$query = "INSERT INTO personal_licencias_permisos (fecha, dia, hora, minutos, codigo_personal, codigo_licencia_permiso, codigo_turno, observacion, hora_inicio, hora_fin, codigo_contratacion)
									VALUES ('$fecha_cambiada','$dia','$hora','$minutos','$codigo_personal','$codigo_licencia','$codigo_turno','$observacion','$hora_inicio','$hora_fin','$codigo_contratacion')";
									
									// Ejecutamos el query
									$resultadoQuery = $dblink -> query($query);
								}
							}
		
						// revisar que no hayan errores.
						if($resultadoQuery == true){
							$respuestaOK = true;
							$mensajeError = "Si Registro";
						}
						else{
							$mensajeError = "No Registro";
							$respuestaOK = false;
						}
					}
				break;

				case 'ActualizarLyP':
					$id = $_POST['id_'];
					$fecha = $_POST['fecha'];
					$dia = $_POST['dia'];
					$hora = $_POST['hora'];
					$minutos = $_POST['minutos'];
					$codigo_licencia = $_POST['codigo_licencia'];
					$observacion = $_POST['observaciones'];
					$hora_inicio = $_POST['hora_inicio'];
					$hora_fin = $_POST['hora_fin'];

					$query_ = sprintf("UPDATE personal_licencias_permisos SET  fecha = '%s', dia ='%s',  hora = '%s',  minutos = '%s', codigo_licencia_permiso = '%s', observacion = '%s', hora_inicio = '%s', hora_fin = '%s'
							WHERE id_licencia_permiso=%d",
							$fecha, $dia, $hora, $minutos, $codigo_licencia, $observacion, $hora_inicio, $hora_fin
							,$id);
	
						// Ejecutamos el query
						$resultadoQuery = $dblink -> query($query_);
	
						if($resultadoQuery == true){
							$respuestaOK = true;
							$mensajeError = "Si Registro";							
						}
				break;
				case 'EditarLyP':
						// armando el Query. PARA LA TABLA HISTORIAL.
							$query_LyP = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin
												FROM personal_licencias_permisos lp
												WHERE lp.id_licencia_permiso = ".$_POST['id_x'];
				// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
				   $consulta_LyP = $dblink -> query($query_LyP);
				// Inicializando el array
				   $datos=array(); $fila_array = 0;
				// Recorriendo la Tabla con PDO::
					$num = 1;
					    if($consulta_LyP -> rowCount() != 0){		
						while($listadoLyP = $consulta_LyP -> fetch(PDO::FETCH_BOTH))
						  {
						      // recopilar los valores de los campos.
								  // recopilar los valores de los campos.
								  $id_licencia_permiso = trim($listadoLyP['id_licencia_permiso']);
								  $fecha = trim($listadoLyP['fecha']);
								  $codigo_contratacion = trim($listadoLyP['codigo_contratacion']);
								  $observacion = trim($listadoLyP['observacion']);
								  $dia = trim($listadoLyP['dia']);
								  $hora = trim($listadoLyP['hora']);
								  $minutos = trim($listadoLyP['minutos']);
								  $codigo_licencia_permiso = trim($listadoLyP['codigo_licencia_permiso']);
								  $codigo_turno = trim($listadoLyP['codigo_turno']);
								  $hora_inicio = trim($listadoLyP['hora_inicio']);
								  $hora_fin = trim($listadoLyP['hora_fin']);
						      
						      // pasar a la matriz.
						      $datos[$fila_array]["id_licencia_permiso"] = $id_licencia_permiso;
							  $datos[$fila_array]["fecha"] = $fecha;
							  $datos[$fila_array]["codigo_contratacion"] = $codigo_contratacion;
							  $datos[$fila_array]["observacion"] = $observacion;
							  $datos[$fila_array]["dia"] = $dia;
							  $datos[$fila_array]["hora"] = $hora;
							  $datos[$fila_array]["minutos"] = $minutos;
							  $datos[$fila_array]["codigo_licencia_permiso"] = $codigo_licencia_permiso;
							  $datos[$fila_array]["codigo_turno"] = $codigo_turno;
							  $datos[$fila_array]["hora_inicio"] = $hora_inicio;
							  $datos[$fila_array]["hora_fin"] = $hora_fin;
					  
						   // Incrementar el valor del array.
						     $fila_array++; $num++;
						  }
					    }
					    else{
						$datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
					    }
				break;
				case 'BuscarLicenciasPermisos':
					$codigo_personal = $_POST['codigo_personal'];
					$fecha_l_y_p = substr($_POST['fecha'],0,4);
					$codigo_contratacion = $_POST['codigo_contratacion'];
					$codigo_tipo_licencia = $_POST['codigo_licencia'];
				   // armando el Query. PARA LA TABLA HISTORIAL.
						// armando el Query. PARA LA TABLA HISTORIAL.
							$query_licencia = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
												btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente
												FROM personal_licencias_permisos lp
													INNER JOIN personal p ON p.id_personal = lp.codigo_personal
														WHERE lp.codigo_personal = '$codigo_personal' and btrim(lp.codigo_contratacion || lp.codigo_turno) = '$codigo_contratacion' and TO_CHAR(lp.fecha,'YYYY') = '$fecha_l_y_p'
															and lp.codigo_licencia_permiso = '$codigo_tipo_licencia'
												ORDER by lp.fecha";
						// Query para revisar la tabla tipo de licencia. (catalogo)
							$query_licencia_permiso = "SELECT codigo, nombre, saldo, minutos from tipo_licencia_o_permiso ORDER BY codigo";
								$consulta_licencia_permiso = $dblink -> query($query_licencia_permiso);
						// Recorrer la tabla licencia permiso y colocar datos en las respectivas tablas.
							$codigo_licencia_o_permiso = array(); $saldo_licencia_o_permiso = array(); $minutos_licencia_o_permiso = array(); $imprimir = array();
							while($listadoPersonalLyP = $consulta_licencia_permiso -> fetch(PDO::FETCH_BOTH))
							{
								// dar valor a variable de un solo SALDO EN MINUTOS.
								if($listadoPersonalLyP['codigo']==$codigo_tipo_licencia ){
									// repetir el proceso hasta que la tabla ya no tenga datos.
										$codigo_licencia_o_permiso[] = $listadoPersonalLyP['codigo'];
										$saldo_licencia_o_permiso[] = $listadoPersonalLyP['saldo'];
										$minutos_licencia_o_permiso[] = $listadoPersonalLyP['minutos'];
								}
							}
						//
						// DECLARAR VARIABLES PARA LAS MATRICES.
						//
							$tramite_dia = array(); $tramite_hora = array(); $tramite_minutos = array();
						// Recorriendo la Tabla con PDO::
							$num = 1; $j = 0;
							$verdadero = ""; $num_registros = 0; $num_datos = 0; $num_datos_tabla = 0;
							$consulta_codigo_licencia_permiso = $dblink -> query($query_licencia);
								// revisar si hay registros.
								$num_registros = $consulta_codigo_licencia_permiso -> rowCount();
								if($num_registros !=0){
									while($listadoPersonal = $consulta_codigo_licencia_permiso -> fetch(PDO::FETCH_BOTH))
										{
											// recopilar los valores de los campos.
											$id_ = trim($listadoPersonal['id_licencia_permiso']);
											$fecha = cambiaf_a_normal(trim($listadoPersonal['fecha']));
											$horario_inicio = trim($listadoPersonal['hora_inicio']);
											$hora_fin = trim($listadoPersonal['hora_fin']);
											$dia = trim($listadoPersonal['dia']);
											$hora = trim($listadoPersonal['hora']);
											$minutos = trim($listadoPersonal['minutos']);
											// pasar a la matriz.
											$datos[$j][] = "<tr>
											<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
											<td>$num
											<td>$id_
											<td>$fecha
											<td>$horario_inicio
											<td>$hora_fin
											<td>$dia
											<td>$hora
											<td>$minutos
											";
												$total_minutos = ($dia*5*60) + ($hora*60) + ($minutos);
												
												$tramite_dia[] = segundosToCadenaD($total_minutos);
												$tramite_hora[] = segundosToCadenaH($total_minutos);
												$tramite_minutos[] = segundosToCadenaM($total_minutos);
											// Aumentar el valor
												$num++;
										}	// salida del while.
											// Calcular el Disponible segùn Tipo de Contratación.
												$calculo_horas = 5;
													if($codigo_contratacion == "05"){ // PAGADOS POR EL CDE.
														$calculo_horas = 8;
													}
											// Calcular Tiempo, sumar dias, horas, minutos.
												$sub_sin_dia = array_sum($tramite_dia);
												$sub_sin_hora = array_sum($tramite_hora);
												$sub_sin_minutos = array_sum($tramite_minutos);
											// Caluclar Disponible en base a los minutos.
												$minutos_x_dias = $minutos_licencia_o_permiso[$j];
												$minutos_subtotal = ($sub_sin_dia*$calculo_horas*60) + ($sub_sin_hora*60) + ($sub_sin_minutos);
												$minutos = $minutos_x_dias - $minutos_subtotal;
												$saldo_x = segundosToCadena($minutos);
											// Acumularlo en la Matriz.
												$j++; // incorporar en el titulo de la talba
												$datos[$j][] = "$saldo_x";
												//	<td>$sub_sin_dia
											//		<td>$sub_sin_hora
											//		<td>$sub_sin_minutos
												
								}
								else{
										$datos[$j][] = '<tr><td>No se encontraron Registros</td></tr>';
										$num = 1;
									}
										// incrementar el valor de la matriz $datos
											$num_datos++;
											$datos[$j][] = $encabezado;
										// Eliminar los elmentos de la array que acumula los dia, minutos y horas.
											unset($tramite_dia, $tramite_hora, $tramite_minutos);
				break;
			case 'eliminarLyP':
				// Armamos el query
				$query = sprintf("DELETE FROM personal_licencias_permisos WHERE id_licencia_permiso = %s",
					 $_POST['id_user']);

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
		"contenido" => $contenidoOK,
		"encabezado"=>$encabezado);

if($Accion == 'EditarLyP' || $Accion == "BuscarLicenciasPermisos" || $Accion == 'BuscarContratacion'){
	// Enviando la matriz con Json.
		echo json_encode($datos);
}else{
	echo json_encode($salidaJson);
}
?>