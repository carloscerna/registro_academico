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
				// Verificamos las variables de acción
			switch ($_POST['accion']) {
				case 'GuardarEG':
					$codigo_docente = $_POST['codigo_docente'];
					$codigo_gst = $_POST['codigo_gst'];
					$codigo_ann_lectivo = $_POST['codigo_annlectivo'];
					$codigo_modalidad = $_POST['codigo_modalidad'];
					$codigo_grado = substr($codigo_gst,0,2);
					$codigo_seccion = substr($codigo_gst,2,2);
					$codigo_turno = substr($codigo_gst,4,2);
					$encargado_grado = $_POST['encargado_grado'];
					$imparte_asignatura = $_POST['imparte_asignatura'];
					
					// Verificar si el Registro no Existe.
					$query_busqueda = "SELECT * from encargado_grado
								WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_bachillerato = '$codigo_modalidad'
								and btrim(codigo_grado || codigo_seccion || codigo_turno) = '$codigo_gst' and encargado = '$encargado_grado'";
					// Eejcutamos query.
					$consulta_busqueda = $dblink -> query($query_busqueda);

					$num_registros = $consulta_busqueda -> rowCount();
					
					if($num_registros !=0){
						// Si existen registros.
							$respuestaOK = false;
							$mensajeError = "Si Existe";
							$contenidoOK = "El Registro Ya Existe.";
					}
					else{
						$query = "INSERT INTO encargado_grado (codigo_docente, codigo_ann_lectivo, codigo_bachillerato, codigo_grado, codigo_seccion, codigo_turno, encargado, imparte_asignatura)
						VALUES ('$codigo_docente','$codigo_ann_lectivo','$codigo_modalidad','$codigo_grado','$codigo_seccion','$codigo_turno','$encargado_grado','$imparte_asignatura')";
		
						// Ejecutamos el query
						$resultadoQuery = $dblink -> query($query);
	
						if($resultadoQuery == true){
							$respuestaOK = true;
							$mensajeError = "Si Save";
						}
						else{
							$mensajeError = "No se puede guardar el registro en la base de datos ".$query;
						}
					}
				break;

				case 'ActualizarLyP':
					$id = $_POST['id_'];

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

						      
						      // pasar a la matriz.
						      $datos[$fila_array]["id_licencia_permiso"] = $id_licencia_permiso;
					  
						   // Incrementar el valor del array.
						     $fila_array++; $num++;
						  }
					    }
					    else{
						$datos[$fila_array]["no_registros"] = '<tr><td colspan="6"> No se encontraron registros.</td>';
					    }
				    
				    // Enviando la matriz con Json.
				    echo json_encode($datos);
				break;

				case 'BuscarEG':
					$codigo_docente = $_POST['codigo_docente'];
					$codigo_ann_lectivo = $_POST['codigo_annlectivo'];
					
				   // armando el Query. PARA LA TABLA HISTORIAL.
						$query_busqueda = "SELECT eg.id_encargado_grado, eg.codigo_bachillerato, eg.codigo_ann_lectivo, eg.codigo_grado, eg.codigo_seccion, eg.codigo_turno, eg.codigo_docente, eg.encargado, eg.imparte_asignatura,
											bach.nombre as nombre_bachillerato, grado.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
											from encargado_grado eg
											INNER JOIN bachillerato_ciclo bach ON bach.codigo = eg.codigo_bachillerato
											INNER JOIN ann_lectivo ann ON ann.codigo = eg.codigo_ann_lectivo
											INNER JOIN grado_ano grado ON grado.codigo = eg.codigo_grado
											INNER JOIN seccion sec ON sec.codigo = eg.codigo_seccion
											INNER JOIN turno tur ON tur.codigo = eg.codigo_turno
												WHERE eg.codigo_ann_lectivo = '$codigo_ann_lectivo' and eg.codigo_docente = '$codigo_docente'";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_eg = $dblink -> query($query_busqueda);
							$num_registros = $consulta_eg -> rowCount();
							$num = 1;
							
									if($num_registros !=0){
										$respuestaOK = true;
										$mensajeError = "Si Existe";
										
										while($listadoEG = $consulta_eg -> fetch(PDO::FETCH_BOTH))
										  {
												// recopilar los valores de los campos.
												$id_encargado_grado = trim($listadoEG['id_encargado_grado']);
												$nombre_modalidad = trim($listadoEG['nombre_bachillerato']);
												$imparte_asignatura = trim($listadoEG['imparte_asignatura']);
												$encargado = trim($listadoEG['encargado']);
												$nombre_gst = trim($listadoEG['nombre_grado']) . ' ' . trim($listadoEG['nombre_seccion']) . ' ' . trim($listadoEG['nombre_turno']);
												// Pasar encargado grado a Sí.
												if($encargado == '1'){$si_encargado = "Sí";}else{$si_encargado = "No";}
												// Pasar imparte asignatura a Sí.
												if($imparte_asignatura == '1'){$si_imparte_asignatura = "Sí";}else{$si_imparte_asignatura = "No";}
												// pasar a la matriz.
												$contenidoOK .= '<tr><td class=centerTXT>'.$num
											  .'<td class=centerTXT>'.$id_encargado_grado
											  .'<td class=centerTXT>'.$nombre_modalidad
											  .'<td class=centerTXT>'.$nombre_gst
											  .'<td class=centerTXT>'.$si_encargado
											  .'<td class=centerTXT>'.$si_imparte_asignatura
											  //.'<td class = centerTXT><a data-accion=EditarEG class="btn btn-xs btn-success" href='.$listadoEG['id_encargado_grado'].'><span class="glyphicon glyphicon-edit"></span> Editar</a>'
											  .'<td><a data-accion=eliminarEG class="btn btn-xs btn-warning" href='.$listadoEG['id_encargado_grado'].'><span class="glyphicon glyphicon-trash"></span> Eliminar</a>'
											  ;
											  // Aumentar el valor
											  $num++;
											  
										}
										// salida del while.										
									}
									else{
											$respuestaOK = false;
											$mensajeError = "No Existe";
											$contenidoOK = '<tr><td colspan="7">No se encontraron Registros</td></tr>';
											$num = 1;
										}
				break;

			case 'eliminarEG':
				// Armamos el query
				$query = sprintf("DELETE FROM encargado_grado WHERE id_encargado_grado = %s",
					 $_POST['id_eg']);

				// Ejecutamos el query
					$count = $dblink -> exec($query);
				
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Se ha eliminado el registro correctamente'.$query;

					$contenidoOK = '<tr><td colspan="7">Se ha Eliminado '.$count.' Registro(s).</td></tr>';

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

if($_POST["accion"] == 'EditarCD'){
	// hOLA
}else{
echo json_encode($salidaJson);
}