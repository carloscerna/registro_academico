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
				case 'GuardarCD':
					$codigo_docente = $_POST['codigo_docente'];
					$codigo_asignatura = $_POST['codigo_asignatura'];
					$codigo_gst = $_POST['codigo_gst'];
					$codigo_ann_lectivo = $_POST['codigo_annlectivo'];
					$codigo_modalidad = $_POST['codigo_modalidad'];
					$codigo_grado = substr($codigo_gst,0,2);
					$codigo_seccion = substr($codigo_gst,2,2);
					$codigo_turno = substr($codigo_gst,4,2);
     // EVALUAR SI ES PARA TERCER AÑO TECNICO COMERCIO. las asignaturas que se divida la nota.
					$partes = 'f';
					$partes_dividida = 0;
					
					// Verificar si el Registro no Existe.
					$query_busqueda = "SELECT cd.codigo_grado, cd.codigo_ann_lectivo, cd.codigo_asignatura, cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno,
										asig.partes, asig.partes_dividida
											from carga_docente cd
											INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
											WHERE cd.codigo_ann_lectivo = '$codigo_ann_lectivo' and cd.codigo_asignatura = '$codigo_asignatura' and cd.codigo_bachillerato = '$codigo_modalidad' and btrim(cd.codigo_grado || cd.codigo_seccion || cd.codigo_turno) = '$codigo_gst'";
					// Eejcutamos query.
					$consulta_busqueda = $dblink -> query($query_busqueda);

					$num_registros = $consulta_busqueda -> rowCount();
					
					
			if($num_registros > 0)
				{
					if($codigo_modalidad >= "08" and $codigo_modalidad <= "09")
						{
						 while($row = $consulta_busqueda -> fetch(PDO::FETCH_BOTH))
								{
								  $partes = $row['partes'];
								  $partes_dividida = $row['partes_dividida'];
								}
						}
				}
				
				// VERIFICAR PARA GUARDAR LA CARGA ACADEMICA	
				if($num_registros > 0 and $partes == 'f' and $partes_dividida == 0)
					{
						// Si existen registros.
							$respuestaOK = false;
							$mensajeError = "Si Existe";
							$contenidoOK = "El Registro Ya Existe.";
					}
					else{
						$query = "INSERT INTO carga_docente (codigo_bachillerato, codigo_docente, codigo_asignatura, codigo_grado, codigo_seccion, codigo_turno, codigo_ann_lectivo)
						VALUES ('$codigo_modalidad','$codigo_docente','$codigo_asignatura','$codigo_grado','$codigo_seccion','$codigo_turno','$codigo_ann_lectivo')";
		
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
						$datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
					    }
				    
				    // Enviando la matriz con Json.
				    echo json_encode($datos);
				break;

				case 'BuscarCD':
					$codigo_docente = $_POST['codigo_docente'];
					$codigo_asignatura = $_POST['codigo_asignatura'];
					$codigo_gst = $_POST['codigo_gst'];
					$codigo_ann_lectivo = $_POST['codigo_annlectivo'];
					$codigo_modalidad = $_POST['codigo_modalidad'];
					$codigo_grado = substr($codigo_gst,0,2);
					$codigo_seccion = substr($codigo_gst,2,2);
					$codigo_turno = substr($codigo_gst,4,2);
					
				   // armando el Query. PARA LA TABLA HISTORIAL.
						$query_busqueda = "SELECT cd.id_carga_docente, cd.codigo_bachillerato, cd.codigo_asignatura, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno, cd.codigo_docente,
											bach.nombre as nombre_bachillerato, grado.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
											asig.nombre as nombre_asignatura, asig.codigo
											from carga_docente cd
											INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
											INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
											INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
											INNER JOIN personal pd ON pd.id_personal = (cd.codigo_docente)::int
											INNER JOIN grado_ano grado ON grado.codigo = cd.codigo_grado
											INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
											INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
												WHERE cd.codigo_ann_lectivo = '$codigo_ann_lectivo' and cd.codigo_docente = '$codigo_docente' and cd.codigo_bachillerato = '$codigo_modalidad' and btrim(cd.codigo_grado || cd.codigo_seccion || cd.codigo_turno) = '$codigo_gst'
												 ORDER BY asig.codigo";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_cd = $dblink -> query($query_busqueda);
							$num_registros = $consulta_cd -> rowCount();
							$num = 1;
							
									if($num_registros !=0){
										$respuestaOK = true;
										$mensajeError = "Si Existe";
										
										while($listadoCD = $consulta_cd -> fetch(PDO::FETCH_BOTH))
										  {
												// recopilar los valores de los campos.
												$id_carga_docente = trim($listadoCD['id_carga_docente']);
												$nombre_modalidad = trim($listadoCD['nombre_bachillerato']);
												$nombre_gst = trim($listadoCD['nombre_grado']) . ' ' . trim($listadoCD['nombre_seccion']) . ' ' . trim($listadoCD['nombre_turno']);
												$nombre_asignatura = trim($listadoCD['nombre_asignatura']);
												
												// pasar a la matriz.
												$contenidoOK .= '<tr><td class=centerTXT>'.$num
											  .'<td class=centerTXT>'.$id_carga_docente
											  .'<td class=centerTXT>'.$nombre_modalidad
											  .'<td class=centerTXT>'.$nombre_gst
											  .'<td>'.$nombre_asignatura
											  //.'<td class = centerTXT><a data-accion=EditarCD class="btn btn-xs btn-primary" href='.$listadoCD['id_carga_docente'].'>Editar</a>'
											  .'<td><a data-accion=eliminarCD class="btn btn-xs btn-warning" href='.$listadoCD['id_carga_docente'].'>Eliminar</a>'
											  ;
											  // Aumentar el valor
											  $num++;
											  
										}
										// salida del while.										
									}
									else{
											$respuestaOK = false;
											$mensajeError = "No Existe";
											$contenidoOK = '<tr><td colspan="6">No se encontraron Registros</td></tr>';
											$num = 1;
										}
				break;

			case 'eliminarCD':
				// Armamos el query
				$query = sprintf("DELETE FROM carga_docente WHERE id_carga_docente = %s",
					 $_POST['id_cd']);

				// Ejecutamos el query
					$count = $dblink -> exec($query);
				
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Se ha eliminado el registro correctamente'.$query;

					$contenidoOK = '<tr><td colspan="6">Se ha Eliminado '.$count.' Registro(s).</td></tr>';

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

if($_POST['accion'] == 'EditarCD'){
	
}else{
echo json_encode($salidaJson);
}