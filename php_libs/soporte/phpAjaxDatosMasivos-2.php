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
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexi�n a la base de datos

include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");
// Validar conexi�n con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acci�n
		switch ($_POST['accion']) {
			case 'BuscarLista':
				// Declarar Variables y Crear consulta Query.
						$codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,4) . $_REQUEST["lstannlectivo"];
				  
						 // Informaci�n Acad�mica.
						 $codigo_modalidad = substr($codigo_all,0,2);
						 $codigo_grado = substr($codigo_all,2,2);
						 $codigo_seccion = substr($codigo_all,4,2);
						 $codigo_annlectivo = substr($codigo_all,6,2);
						// Variable para el color de las filas.
						$color_tabla = "success";
						
						$query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
									btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo, 
									btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno  || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
									ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono, ae.direccion,
									a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad,
									a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.codigo_nie, a.codigo_genero, a.telefono_celular,
									am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.ann_anterior, ae.fecha_nacimiento,
									am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
									am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula,
									am.observaciones
										FROM alumno a
									INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
									INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
									INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
									INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
									INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
									INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
									WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all.
									"' ORDER BY apellido_alumno ASC";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
                // PRIMERA CONSULTA DE QUERY
				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// Variable para el color de las filas de la tabla.
						if($color_tabla == "success"){$color_tabla="active";}else{$color_tabla="success";}
						
						// Variables
						$num++;
						$id_alumno = $listado['id_alumno'];
						$codigo_matricula = $listado['codigo_matricula'];
						$apellido_alumno = (trim($listado['apellido_alumno']));
                        $direccion = $listado['direccion_alumno'];
                        $telefono = $listado['telefono_alumno'];
						$telefono_celular = $listado['telefono_celular'];
						

                        // armando el Query. PARA LA TABLA ALUMNO ENCARGADO.
                         $query_encargado = "SELECT id_alumno_encargado, codigo_alumno, nombres, lugar_trabajo, profesion_oficio, dui, telefono,
                                            direccion, encargado, institucion_proviene, fecha_nacimiento, codigo_nacionalidad, codigo_familiar, codigo_zona, codigo_departamento, codigo_municipio
                                            from alumno_encargado where codigo_alumno = ". $id_alumno ." order by id_alumno_encargado";
                        // Ejecutamos el Query.
                        $consulta_encargado = $dblink -> query($query_encargado);
                        $fila_array = 0;
                        // Debera crerase en las tablas correspondientes los campos para poder rellenar dicha informaci�n.
                            if($consulta_encargado -> rowCount() != 0){		
                                while($listadoEncargado = $consulta_encargado -> fetch(PDO::FETCH_BOTH))
                                  {
                                    $id_alumno_encargado = trim($listadoEncargado['id_alumno_encargado']);
                                    $nombres = trim($listadoEncargado['nombres']);
                                    $dui = trim($listadoEncargado['dui']);
                                    $encargado_bollean = trim($listadoEncargado['encargado']);
									$fecha_nacimiento = trim($listadoEncargado['fecha_nacimiento']);
									$telefono_encargado = $listadoEncargado['telefono'];
									$fecha_n_encargado = cambiaf_a_normal(trim($listadoEncargado['fecha_nacimiento']));
                                    // pasar a la matriz.
                                        $datos[$fila_array]["encargado_bollean"] = $encargado_bollean;
                                        $datos[$fila_array]["id_alumno_encargado"] = $id_alumno_encargado;
                                        $datos[$fila_array]["nombres"] = $nombres;
                                        $datos[$fila_array]["dui"] = $dui;
										$datos[$fila_array]["fecha_nacimiento"] = $fecha_nacimiento;
										$datos[$fila_array]["telefono_encargado"] = $telefono_encargado;
										$datos[$fila_array]["fecha_n_encargado"] = $fecha_n_encargado;
                                    // Incrementar el valor del array.
                                    $fila_array++;
                                  }
                            }
                            // valores en variables. DEL PADRE.
                            $bollean_p = $datos[0]["encargado_bollean"];
                            $nombre_p = $datos[0]["nombres"];
                            // valores en variables. DEL ,ADRE.
                            $bollean_m = $datos[1]["encargado_bollean"];
                            $nombre_m = $datos[1]["nombres"];
                            // valores en variables. DEL OTRO.
                            $bollean_o = $datos[2]["encargado_bollean"];
                            $nombre_o = $datos[2]["nombres"];

                            // definicar los checke
                            $boolean_1 = "<input type=radio name=e$id_alumno value=Padre>";
                            $boolean_2 = "<input type=radio name=e$id_alumno value=Madre>";
                            $boolean_3 = "<input type=radio name=e$id_alumno value=Otro>";
                            // Crear Check para EL PADRE.
                                if($bollean_p == "1"){
                                	$boolean_1 = "<input type=radio name=e$id_alumno value=Padre checked> <label class='text-danger'>Encargado</label>";
                                }
                            // Crear Check para EL MADRE.
                                if($bollean_m == "1"){
                                	$boolean_2 = "<input type=radio name=e$id_alumno value=Madre checked> <label class=text-danger>Encargado</label>";
                                }
                            // Crear Check para EL OTRO.
                                if($bollean_o == "1"){
                                	$boolean_3 = "<input type=radio name=e$id_alumno value=Otro checked> <label class=text-danger>Encargado</label>";
                                }
						// Armar Cadena con resultado de las respectivas variables.	
						$contenidoOK .= '<tr class='.$color_tabla.'><td>'.$num
							.'<td>'.$id_alumno
							.'<td>'.$apellido_alumno
                            .'<td><input type=hidden name=id_p class=form-control value = '.$datos[0]["id_alumno_encargado"].'>'
                            .''.$boolean_1
                            ."<input type=text name=nombres_p class=form-control value='$nombre_p'>"
							.'<input type=text name=dui_p class=form-control value = '.$datos[0]["dui"].'>'
							.'<input type=text name=fecha_nacimiento_p class=form-control value = '.$datos[0]["fecha_n_encargado"].'>'
							.'<input type=text name=telefono_p class=form-control value = '.$datos[0]["telefono_encargado"].'>'
                            .'<td><input type=hidden name=id_m class=form-control value = '.$datos[1]["id_alumno_encargado"].'>'
                            .''.$boolean_2
                            ."<input type=text name=nombres_m class=form-control value='$nombre_m'>"
							.'<input type=text name=dui_m class=form-control value = '.$datos[1]["dui"].'>'
							.'<input type=text name=fecha_nacimiento_m class=form-control value = '.$datos[1]["fecha_n_encargado"].'>'
							.'<input type=text name=telefono_m class=form-control value = '.$datos[1]["telefono_encargado"].'>'
                            .'<td><input type=hidden name=id_o class=form-control value = '.$datos[2]["id_alumno_encargado"].'>'
                            .''.$boolean_3
                            ."<input type=text name=nombres_o class=form-control value='$nombre_o'>"
							.'<input type=text name=dui_o class=form-control value = '.$datos[2]["dui"].'>'
							.'<input type=text name=fecha_nacimiento_o class=form-control value = '.$datos[2]["fecha_n_encargado"].'>'
							.'<input type=text name=telefono_o class=form-control value = '.$datos[2]["telefono_encargado"].'>'
							.'</td>'
							;
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = true;
					$contenidoOK = '
						<tr id="sinDatos">
							<td colspan="8" class="centerTXT">No Hay Registros de este alumno...</td>
						</tr>
					'.$query;
					$mensajeError =  'No Registro';
				}
			break;

			case 'ActualizarDatosPn':
				// armar variables y consulta Query.
				$codigo_alumno[] = $_POST["codigo_alumno"];
				$fila = $_POST["fila"];
				// VARIABLES DEL PADRE.
				$id_p_[] = $_POST["id_p"];
                $chkencargado_p_[] = $_POST["chkencargado_p"];
                $nombres_p_[] = $_POST["nombres_p"];
				$dui_p_[] = $_POST["dui_p"];
				$fecha_n_p_[] = $_POST["fecha_n_p"];
				$telefono_p_[] = $_POST["telefono_p"];
				// VARIABLES DEL PADRE.
				$id_m_[] = $_POST["id_m"];
                $chkencargado_m_[] = $_POST["chkencargado_m"];
                $nombres_m_[] = $_POST["nombres_m"];
				$dui_m_[] = $_POST["dui_m"];
				$fecha_n_m_[] = $_POST["fecha_n_m"];
				$telefono_m_[] = $_POST["telefono_m"];
				// VARIABLES DEL PADRE.
				$id_o_[] = $_POST["id_o"];
                $chkencargado_o_[] = $_POST["chkencargado_o"];
                $nombres_o_[] = $_POST["nombres_o"];
				$dui_o_[] = $_POST["dui_o"];
				$fecha_n_o_[] = $_POST["fecha_n_o"];
				$telefono_o_[] = $_POST["telefono_o"];
                
				$fila = $fila - 1;

				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila;$i++){
					$codigo_a = $codigo_alumno[0][$i];
					// LOS 3 RD ENCARGADO
                    if ($chkencargado_p_[0][$i] == 'true'){$en = 'true';}else{$en = 'false';}
					if ($chkencargado_m_[0][$i] == 'true'){$en1 = 'true';}else{$en1 = 'false';}
					if ($chkencargado_o_[0][$i] == 'true'){$en2 = 'true';}else{$en2 = 'false';}
					
					$encargados = array($en,$en1,$en2);
                    // DATOS DEL PADRE
					$idaencargado = array($id_p_[0][$i],$id_m_[0][$i],$id_o_[0][$i]);
                    $nombres = array($nombres_p_[0][$i],$nombres_m_[0][$i],$nombres_o_[0][$i]);
					$duis = array($dui_p_[0][$i],$dui_m_[0][$i],$dui_o_[0][$i]);
					$fechasnacimientos = array($fecha_n_p_[0][$i],$fecha_n_m_[0][$i],$fecha_n_o_[0][$i]);
					$telefonos = array($telefono_p_[0][$i],$telefono_m_[0][$i],$telefono_o_[0][$i]);
                    

                // Actualizar valores del encargado.
					for ($j=0;$j<=2;$j++){                       
						$query_encargado = sprintf("UPDATE alumno_encargado SET nombres = '%s', dui = '%s', telefono = '%s', fecha_nacimiento = '%s',
							encargado = '%s'
                            WHERE codigo_alumno = %d and id_alumno_encargado = %d",
							$nombres[$j],$duis[$j],$telefonos[$j],$fechasnacimientos[$j],
                            $encargados[$j]
							,$codigo_a,$idaencargado[$j]);	

						// Ejecutamos el query guardar los datos en la tabla alumno..
                            $resultadoQueryEncargado = $dblink -> query($query_encargado);				
					} // FINAL DEL FOR.
                } // FOR DE LOS DATOS.
				$respuestaOK = true;
				$contenidoOK = 'Registros Actualizados.';
				$mensajeError =  'Si Registro';
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

// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>