
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
				case 'GraficoIndicadores':
                    // Inicializando el array
                    $datos=array(); $fila_array = 0; $nombre_modalidades=array();
					$codigo_genero = array('01','02'); $indicadores_educativos = array("matricula","retirado","repitente","sobreedad","nuevo_ingreso"); 
					$count_campo = array("codigo_genero");
					$matricula_masculino = array(); $matricula_femenino = array();
					$nombre_turno = array(); $codigo_turno = array();
					$codigo_ann_lectivo = $_POST['codigo_ann_lectivo'];
					$query_i = "";
					//  INFORMACIÓN DEL LA TABLA TURNO.
						$query_turno = "SELECT * FROM turno ORDER BY codigo";
						// ejecutar la consulta.
						$result_turno = $dblink -> query($query_turno);
						while($row = $result_turno -> fetch(PDO::FETCH_BOTH))
						{
							$codigo_turno_bucle[] = trim($row['codigo']);
							$nombre_turno_bucle[] = trim($row['nombre']);
						}
					// Verificar si el Registro no Existe. para obtener las MODALIDADES PARA EL AÑO LECTIVO EN CURSO.
					$query ="SELECT DISTINCT ROW(org.codigo_ann_lectivo), org.codigo_bachillerato,  org.codigo_ann_lectivo,
                                ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad
                                from organizar_ann_lectivo_ciclos org
                                INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo 
                                INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato 
                                where codigo_ann_lectivo = '$codigo_ann_lectivo' ORDER BY org.codigo_bachillerato, org.codigo_ann_lectivo";
					// Eejcutamos query.
					$consulta = $dblink -> query($query);
					$num_registros = $consulta -> rowCount();
										
					if($num_registros !=0){
						$respuestaOK = true;
						$mensajeError = "Si Existe";
										
						while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
						{
                            $nombre_modalidad = trim($listado['nombre_modalidad']);
                            $codigo_modalidad = trim($listado['codigo_bachillerato']);
                            // pasar a la matriz.
                            $nombre_modalidades[] = $nombre_modalidad;
                            $codigo_modalidad_ann[] = $codigo_modalidad . $codigo_ann_lectivo;
						}   // salida del while.
                        // Recorrer para optener los diferentes valores de las modalidades
						// PARA EXTRAER LA MATRICULA MAXIMA.
					/*	for($ct=0;$ct<=count($codigo_turno_bucle)-1;$ct++)	// turno (Matutino, Vespertino o Nocturna.)
							{
								for($j=0;$j<=count($codigo_modalidad_ann)-1;$j++) // MODALIDAD
									{
										for($ig=0;$ig<=count($codigo_genero)-1;$ig++)	// GENERO
											{
												// Armar query para la matricula masculino 
													$query = "SELECT count(codigo_genero) as total_ FROM alumno_matricula am 
														INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
														WHERE btrim(codigo_bach_o_ciclo || codigo_ann_lectivo || codigo_turno) = '$codigo_modalidad_ann[$j]$codigo_turno_bucle[$ct]'
														and a.codigo_genero = '$codigo_genero[$ig]' and am.codigo_turno = '$codigo_turno_bucle[$ct]'";
												print $query . "<br>";
												//  Ejecutar query.
													$result_conteo = $dblink -> query($query);
												// Recorrer la consulta. PARA OBTENER LA MATRICULA MAXIMA
												// Evaluar si existen registros.
													if($result_conteo -> rowCount() != 0)
														{
															while($row_ = $result_conteo -> fetch(PDO::FETCH_BOTH))
																{
																	if($codigo_genero[$ig] === "01"){$matricula_masculino[] = $row_['total_'];}
																	if($codigo_genero[$ig] === "02"){$matricula_femenino[] = $row_['total_'];}
																}
														}
											}
									}
							}*/
						// Recorrer para optener los diferentes valores de las modalidades
						// PARA EXTRAER LA retirado, repitente y sobreedad.
						// PARA EXTRAER LA MATRICULA MAXIMA.
						for($ct=0;$ct<=count($codigo_turno_bucle)-1;$ct++)	// turno (Matutino, Vespertino o Nocturna.)
							{
								for($id=0;$id<=count($indicadores_educativos)-1;$id++)
								{
									for($j=0;$j<=count($codigo_modalidad_ann)-1;$j++)
										{
											for($ig=0;$ig<=count($codigo_genero)-1;$ig++)
												{
													if($indicadores_educativos[$id] === 'matricula')
													{
														// Armar query para la matricula masculino 
															$query = "SELECT count(codigo_genero) as total_ FROM alumno_matricula am 
																INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
																	WHERE btrim(codigo_bach_o_ciclo || codigo_ann_lectivo || codigo_turno) = '$codigo_modalidad_ann[$j]$codigo_turno_bucle[$ct]'
																		and a.codigo_genero = '$codigo_genero[$ig]' and am.codigo_turno = '$codigo_turno_bucle[$ct]'";
														//  Ejecutar query.
															$result_conteo = $dblink -> query($query);
													}else{
														// Armar query para la matricula masculino 
															$query = "SELECT count($indicadores_educativos[$id]) as total_ FROM alumno_matricula am 
																INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
																	WHERE btrim(codigo_bach_o_ciclo || codigo_ann_lectivo || codigo_turno) = '$codigo_modalidad_ann[$j]$codigo_turno_bucle[$ct]'
																		and a.codigo_genero = '$codigo_genero[$ig]' 
																		and am.codigo_turno = '$codigo_turno_bucle[$ct]' and am.$indicadores_educativos[$id] = 't'";
														//  Ejecutar query.
															$result_conteo = $dblink -> query($query);
														}
														// INDICADOR MATRICULA.
															if($indicadores_educativos[$id] === 'matricula')
																{
																	while($row_ = $result_conteo -> fetch(PDO::FETCH_BOTH))
																		{
																			$total_registros = $row_['total_'];
																			// Evaluar si existen registros.
																			if($total_registros != 0)
																			{
																					if($codigo_genero[$ig] === "01")
																						{
																						// CONSTRUIR ARRAY
																							$datos[$fila_array]["nombre_turno"] = $nombre_turno_bucle[$ct];
																							$datos[$fila_array]["nombre_modalidad"] = $nombre_modalidades[$j];
																							$datos[$fila_array]["matricula_masculino"] =  $total_registros;
																						}
																					if($codigo_genero[$ig] === "02")
																						{
																							$datos[$fila_array]["matricula_femenino"] = $total_registros;
																							$fila_array++;
																						}	
																			} //IF PARA SABER SI TIENE REGISTROS.
																		} // DO WHILE DE LA CONSULTA
																} // IF del indicador MATRICULA

																if($indicadores_educativos[$id] === 'retirado')
																{
																	while($row_ = $result_conteo -> fetch(PDO::FETCH_BOTH))
																		{
																			$total_registros = $row_['total_'];
																					if($codigo_genero[$ig] === "01")
																						{
																							//if($total_registros == 0)
																						// CONSTRUIR ARRAY
																							$retirado_masculino[] =  $total_registros;
																						}
																					if($codigo_genero[$ig] === "02")
																						{
																							$retirado_femenino[] =  $total_registros;
																						}	
																		} // DO WHILE DE LA CONSULTA
																} // IF del indicador RETIRADO.
												} // FOR DEL CODIGO GENERO
										} // FOR DEL CODIGO MODALIDAD Y AÑO LECTIVO.
										//$fila_array = 0;
								} // FOR DEL INDICADOR, matricula, retirado, sobreedad, nuevo ingreso.
							} // FOR DEL CODIGO TURNO.MATRICULA, MATUTINO, VESPERTINO Y NOCTURNA.

								//print_r($retirado_masculino);
								$fila_array = 0;
								//print_r($codigo_modalidad_ann);
								for($j=0;$j<=count($codigo_modalidad_ann)-1;$j++)
								{
									for($ig=0;$ig<=count($codigo_genero)-1;$ig++)
									{
										if($codigo_genero[$ig] === "01")
										{
										// CONSTRUIR ARRAY
											$datos[$fila_array]["retirado_masculino"] = $retirado_masculino[$j];
										}
										
										if($codigo_genero[$ig] === "02")
										{
											$datos[$fila_array]["retirado_femenino"] = $retirado_femenino[$j];
											$fila_array++;
										}										
									}
								}
							}else
								{
									$respuestaOK = false;
									$mensajeError = "No Existe";
									$contenidoOK = '<tr><td colspan="6">No se encontraron Registros</td></tr>';
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
// Enviar datos.
echo json_encode($datos);
?>