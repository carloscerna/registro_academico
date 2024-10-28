
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
					$count_campo = array("codigo_genero"); $campo_indicador = array("matricula_maxima_m","matricula_maxima_f");
					$campo_m_f = array("_m","_f");
					$matricula_masculino = array(); $matricula_femenino = array();
					$nombre_turno = array(); $codigo_turno = array();
					$codigo_ann_lectivo = $_POST['codigo_ann_lectivo'];

					//  INFORMACIÓN DEL LA TABLA CATALOGO GENERO.
						$query_genero = "SELECT * FROM catalogo_genero ORDER BY codigo";
					// ejecutar la consulta.
						$result_genero = $dblink -> query($query_genero);
							while($row = $result_genero -> fetch(PDO::FETCH_BOTH))
							{
								$codigo_genero_bucle[] = trim($row['codigo']);
								$nombre_genero_bucle[] = trim($row['descripcion']);
							}
//							print_r($codigo_genero_bucle);
					//  INFORMACIÓN DEL LA TABLA TURNO.
						$query_indicadores = "SELECT * FROM catalogo_indicadores_educativos ORDER BY codigo";
					// ejecutar la consulta.
						$result_indicadores = $dblink -> query($query_indicadores);
						$num_registros = $result_indicadores -> rowCount();
							while($row = $result_indicadores -> fetch(PDO::FETCH_BOTH))
							{
								$codigo_indicadores_bucle[] = trim($row['codigo']);
								$nombre_indicadores_bucle[] = strtolower(trim($row['descripcion']));
							}
								//print_r($nombre_indicadores_bucle);							
					// Crea y Actualizar la tabla Temp_Indicadores_Educativos.
						$query ="SELECT TempIndicadoresEducativos ('$codigo_ann_lectivo')";
					// Eejcutamos query.
						$consulta = $dblink -> query($query);
					// Crea y Actualizar la tabla Temp_Indicadores_Educativos.
						$query ="SELECT * FROM temp_indicadores_educativos";
					// Eejcutamos query.
						$consulta = $dblink -> query($query);
						while($row = $consulta -> fetch(PDO::FETCH_BOTH))
						{
							$codigo_t_m_a[] = trim($row['codigo_modalidad_turno_ann_lectivo']);
						}
					// IF PRINCIPAL PARA CALCULAR SI HAY REGISTROS.										
					if($num_registros !=0){
						$respuestaOK = true;
						$mensajeError = "Si Existe";
						// Recorrer para optener los diferentes valores de las modalidades
						// PARA EXTRAER LA retirado, repitente y sobreedad.
						// PARA EXTRAER LA MATRICULA MAXIMA.
							for($tma=0;$tma<=count($codigo_t_m_a)-1;$tma++)
							{
									for($id=0;$id<=count($codigo_indicadores_bucle)-1;$id++)
									{
										for($ig=0;$ig<=count($codigo_genero_bucle)-1;$ig++)
											{
												if($nombre_indicadores_bucle[$id] === 'matricula maxima')
												{
													// Armar query para la matricula masculino 
															$query = "UPDATE temp_indicadores_educativos set ($campo_indicador[$ig]) = 
																			(SELECT count(codigo_genero) as total_ FROM alumno_matricula am 
																				INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
																					WHERE btrim(codigo_turno || codigo_bach_o_ciclo || codigo_ann_lectivo) = '$codigo_t_m_a[$tma]'
																						and a.codigo_genero = '$codigo_genero_bucle[$ig]')
																			WHERE codigo_modalidad_turno_ann_lectivo = '$codigo_t_m_a[$tma]'";
													//  Ejecutar query.
														$result_conteo = $dblink -> query($query);
												}else{
													// Armar query para la matricula masculino 
														$query = "UPDATE temp_indicadores_educativos set ($nombre_indicadores_bucle[$id]$campo_m_f[$ig]) = 
																	(SELECT count($nombre_indicadores_bucle[$id]) as total_ FROM alumno_matricula am 
																		INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
																		WHERE btrim(codigo_turno || codigo_bach_o_ciclo || codigo_ann_lectivo) = '$codigo_t_m_a[$tma]'
																			and a.codigo_genero = '$codigo_genero_bucle[$ig]' 
																			and am.$nombre_indicadores_bucle[$id] = 't')
																	WHERE codigo_modalidad_turno_ann_lectivo = '$codigo_t_m_a[$tma]'";
													//  Ejecutar query.
														$result_conteo = $dblink -> query($query);
													}
											} // FOR DEL CODIGO GENERO
									} // FOR DEL INDICADOR, matricula, retirado, sobreedad, nuevo ingreso.
								} // FOR DE LA MODALIDAD TURNO Y AÑO LECTIVO.
								// CONTRUIR MATRIZ PARA EL GRAFICO Y TABLA DE DATOS.
									//  INFORMACIÓN DEL LA TABLA TURNO.
										$query_turno = "SELECT * FROM turno ORDER BY codigo";
										$ctb = 0; $m_t_m = 0; $m_t_f = 0;
										// ejecutar la consulta.
											$result_turno = $dblink -> query($query_turno);
												while($row = $result_turno -> fetch(PDO::FETCH_BOTH))
												{
													$codigo_turno_bucle[] = trim($row['codigo']);
													$nombre_turno_bucle[] = trim($row['nombre']);
												}
									// Crea y Actualizar la tabla Temp_Indicadores_Educativos.
										$query ="SELECT * FROM temp_indicadores_educativos ORDER BY codigo_modalidad_turno_ann_lectivo";
									// Eejcutamos query.
										$consulta = $dblink -> query($query);
										while($row = $consulta -> fetch(PDO::FETCH_BOTH))
										{
											// sumar todos los del turno.
											$turno_solo = substr(rtrim($row["codigo_modalidad_turno_ann_lectivo"]),0,2);
											if($codigo_turno_bucle[$ctb] == $turno_solo)
											{
												$datos[$fila_array]["nombre_turno"] = rtrim($row["nombre_turno"]);
												$m_t_m = $m_t_m + ($row["matricula_maxima_m"] - $row["retirado_m"]);
												$m_t_f = $m_t_f + ($row["matricula_maxima_f"] - $row["retirado_f"]);
											}else{
												// Agregar otra fila.
													$fila_array++;
												// Asignar a Array.
													$datos[$fila_array]["nombre_modalidad"] = "Sub-Total";
													$datos[$fila_array]["matricula_ciclo_m"] =  $m_t_m;
													$datos[$fila_array]["matricula_ciclo_f"] = $m_t_f;
													$datos[$fila_array]["matricula_ciclo"] = $m_t_m + $m_t_f;
												// + del catalogo generto.
													$ctb++;
												// Agregar otra fila.
													$fila_array++;
											}
											$datos[$fila_array]["nombre_turno"] = rtrim($row["nombre_turno"]);
											$datos[$fila_array]["nombre_modalidad"] = rtrim($row["nombre_modalidad"]);
											$datos[$fila_array]["matricula_masculino"] =  $row["matricula_maxima_m"];
											$datos[$fila_array]["matricula_femenino"] = $row["matricula_maxima_f"];
											$datos[$fila_array]["retirado_masculino"] =  $row["retirado_m"];
											$datos[$fila_array]["retirado_femenino"] = $row["retirado_f"];
											$datos[$fila_array]["repitente_masculino"] =  $row["repitente_m"];
											$datos[$fila_array]["repitente_femenino"] = $row["repitente_f"];
											$datos[$fila_array]["sobreedad_masculino"] =  $row["sobreedad_m"];
											$datos[$fila_array]["sobreedad_femenino"] = $row["sobreedad_f"];
											$datos[$fila_array]["nuevo_ingreso_masculino"] =  $row["nuevo_ingreso_m"];
											$datos[$fila_array]["nuevo_ingreso_femenino"] = $row["nuevo_ingreso_f"];
												$fila_array++;
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