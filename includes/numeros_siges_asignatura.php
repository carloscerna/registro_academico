<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
include($path_root."/registro_web/includes/funciones.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                  $letra = "I";
                  $numero = 283;
                  
								$query_p = "select * from a_a_a_bach_o_ciclo where id_asignacion >= 1518 and id_asignacion <= 1565 order by orden";
							// Ejecutamos el Query.
									$consulta_p = $dblink -> query($query_p);
									// Verificar si existen registros.
									if($consulta_p -> rowCount() != 0){
										// convertimos el objeto
										while($listados = $consulta_p -> fetch(PDO::FETCH_BOTH))
										{
											$codigo_ = trim($listados['id_asignacion']);

										// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
										$query_cat = "UPDATE a_a_a_bach_o_ciclo SET codigo_sirai = '$letra$numero' WHERE id_asignacion = $codigo_";
										$consulta_cat = $dblink -> query($query_cat);
                              $numero++;
                              print $letra . $numero . " - " . $codigo_;
                              print "<br>";
										}

									}
									else{

                              }
										
										