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
$lista = "";
$arreglo = array();
$datos = array();
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexi�n a la base de datos

include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// Validar conexi�n con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acci�n
		switch ($_POST['accion']) {
		case 'BuscarTodos':
				// Armamos el query.
				$query = "SELECT a.id_alumno, to_char(a.fecha_nacimiento,'dd/mm/yyyy') as fecha_nacimiento, a.edad, btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_apellidos, a.codigo_nie, a.codigo_estatus, cat_est.descripcion as estatus
						FROM alumno a
                        INNER JOIN catalogo_estatus cat_est ON cat_est.codigo = a.codigo_estatus";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
				// Validar si hay registros.
				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$arreglo["data"][] = $listado;						
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = true;
					$contenidoOK = '';
					$mensajeError =  'No Registro';
				}
			break;

			case 'AgregarNuevoEstudiante':		
				// armar variables.
				// TABS-1
				$apellido_materno = trim($_POST['apellido_materno']);
				$apellido_paterno = trim($_POST['apellido_paterno']);
				$nombre_completo = trim($_POST['txtnombres']);
				$direccion_alumno = trim($_POST['direccion_alumno']);
				$nie = trim($_POST['nie']);
				$telresidencia = trim($_POST['telresidencia']);
				$telcelular = trim($_POST['telcelular']);
				$email_alumno = trim($_POST['email_alumno']);
				$medicamento_alumno = trim($_POST['medicamento_alumno']);
				//TABS-2
				$fecha_nacimiento = trim($_POST['fechanacimiento']);
				$numero = trim($_POST['numero_pn']);
				$folio = trim($_POST['folio_pn']);
				$tomo = trim($_POST['tomo_pn']);
				$libro = trim($_POST['libro_pn']);
				$edad = trim($_POST['edad_enviar']);
				//crear una variable diferente ppara el genero, guardar en campo genero y .
				$codigo_genero = trim($_POST['lstgenero']);
				$codigo_estado_civil = trim($_POST['lstEstadoCivil']);
				$codigo_departamento = trim($_POST['lstdepartamento']);
				$codigo_municipio = trim($_POST['lstmunicipio']);
				$codigo_estado_familiar = trim($_POST['lstestadofamiliar']);
				$codigo_actividad_economica = trim($_POST['lstactividadeconomica']);
				$codigo_tipo_discapacidad = trim($_POST['lsttipodiscapacidad']);
				$codigo_servicio_apoyo_educativo = trim($_POST['lstservicioapoyoeducativo']);
				$codigo_zona_residencia = trim($_POST['lstzonaresidencia']);
				// Tabs-3
				$codigo_estatus = trim($_POST['lstEstatus']);
				// Tabs-4
				// Informaci�n del Padre/Madre o Encargado(a)
                    $fecha_nacimiento_e = array($_POST["txtfechanacimientop"],$_POST["txtfechanacimientom"],$_POST["txtfechanacimientoo"]);
                    $codigo_nacionalidad_e = array($_POST["lstNacionalidadP"],$_POST["lstNacionalidadM"],$_POST["lstNacionalidadO"]);
                    $codigo_estado_familiar_e = array($_POST["lstEstadoFamiliarP"],$_POST["lstEstadoFamiliarM"],$_POST["lstEstadoFamiliarO"]);
                    $codigo_zona_e = array($_POST["lstZonaResidenciaP"],$_POST["lstZonaResidenciaM"],$_POST["lstZonaResidenciaO"]);
                    $codigo_departamento_e = array($_POST["lstDepartamentoP"],$_POST["lstDepartamentoM"],$_POST["lstDepartamentoO"]);
                    $codigo_municipio_e = array($_POST["lstMunicipioP"],$_POST["lstMunicipioM"],$_POST["lstMunicipioO"]);
                    // DATOS DEL RESPONSABLE.
					$nombre_padre = array($_POST["txtnombrep"],$_POST["txtnombrem"],$_POST["txtnombreo"]);
					$lugar_padre = array($_POST["txtlugarp"],$_POST["txtlugarm"],$_POST["txtlugaro"]);
					$pop_padre = array($_POST["txtpop"],$_POST["txtpom"],$_POST["txtpoo"]);
					$dui_padre = array(trim($_POST["txtduip"]),trim($_POST["txtduim"]),trim($_POST["txtduio"]));
					$telefono_padre = array($_POST["txttelefonop"],$_POST["txttelefonom"],$_POST["txttelefonoo"]);	   
					$direccion_padre = array($_POST["txtdireccionp"],$_POST["txtdireccionm"],$_POST["txtdirecciono"]);
					$idaencargado = array($_POST["txtidep"],$_POST["txtidem"],$_POST["txtideo"]);
					// RADIO BUTTON QUIEN SERA EL RESPONSABLE.
					if ($_POST["rdencargado"] == 'Padre'){$en = 't';}else{$en = 'f';}
					if ($_POST["rdencargado"] == 'Madre'){$en1 = 't';}else{$en1 = 'f';}
					if ($_POST["rdencargado"] == 'Otro'){$en2 = 't';}else{$en2 = 'f';}
                // TAB-5 - MATRICULAR
                    $codigo_ann_lectivo = $_POST["lstannlectivo"];
                    $codigo_modalidad = $_POST["lstmodalidad"];
                    $codigo_gst = $_POST["lstgradoseccion"];
                    $codigo_grado = substr($_POST["lstgradoseccion"],0,2);
                    $codigo_seccion = substr($_POST["lstgradoseccion"],2,2);
                    $codigo_turno = substr($_POST["lstgradoseccion"],4,2);
                    $fecha_matricula = trim($_POST['txtfechaMatricula']);
                // GUARDAR VALOR DE LAS VARIABLES POST EN SESSION.
                    $_SESSION['s_codigo_ann_lectivo'] = $codigo_ann_lectivo;
                    $_SESSION['s_codigo_modalidad'] = $codigo_modalidad;
                    $_SESSION['s_codigo_grado_seccion_turno'] = $codigo_gst;
                // cambiar el valor para genero por el campo que lo toma como "m" o "f"
					if($codigo_genero == '01'){$genero = "m";}else{$genero = "f";}
					// VAR
					$encargado = array($en,$en1,$en2);
					// QIERU
					$query = "INSERT INTO alumno (apellido_materno, apellido_paterno, nombre_completo, codigo_nie, direccion_alumno, telefono_alumno, codigo_departamento,
						codigo_municipio, fecha_nacimiento, pn_numero, pn_folio, pn_tomo, pn_libro, medicamento, direccion_email,
						edad, genero, codigo_estado_civil, codigo_estado_familiar, codigo_actividad_economica,
						codigo_apoyo_educativo, codigo_discapacidad, codigo_zona_residencia, telefono_celular, codigo_genero, codigo_estatus)
						VALUES ('$apellido_materno','$apellido_paterno','$nombre_completo','$nie','$direccion_alumno','$telresidencia','$codigo_departamento',
						'$codigo_municipio','$fecha_nacimiento','$numero','$folio','$tomo','$libro','$medicamento_alumno','$email_alumno',
						'$edad','$genero','$codigo_estado_civil','$codigo_estado_familiar','$codigo_actividad_economica',
						'$codigo_servicio_apoyo_educativo','$codigo_tipo_discapacidad','$codigo_zona_residencia','$telcelular','$codigo_genero','$codigo_estatus')";
					// Ejecutamos el query
					$resultadoQuery = $dblink -> query($query);
					// Obtenemos el id de user para edici�n
					$query_ultimo = "SELECT id_alumno from alumno ORDER BY id_alumno DESC LIMIT 1 OFFSET 0";
					// Ejecutamos el Query.
					$consulta = $dblink -> query($query_ultimo);

					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// obtenemos el �ltimo c�digo asignado.
						$codigo_alumno = $listado['id_alumno'];
					}
					
					// Agregar valores para el encargado.
					// Actualizar valores del encargado.
					for ($i=0;$i<=2;$i++){
						$query_encargado ="INSERT INTO alumno_encargado (codigo_alumno,nombres,lugar_trabajo,profesion_oficio,dui,telefono,direccion,encargado, fecha_nacimiento, codigo_nacionalidad, codigo_familiar, codigo_zona, codigo_departamento, codigo_municipio)
							VALUES ($codigo_alumno,'$nombre_padre[$i]','$lugar_padre[$i]','$pop_padre[$i]','$dui_padre[$i]','$telefono_padre[$i]','$direccion_padre[$i]','$encargado[$i]','$fecha_nacimiento_e[$i]','$codigo_nacionalidad_e[$i]','$codigo_estado_familiar_e[$i]','$codigo_zona_e[$i]','$codigo_departamento_e[$i]','$codigo_municipio_e[$i]')";
	    
						// Ejecutamos el query guardar los datos en la tabla alumno..
							$resultadoQueryEncargado = $dblink -> query($query_encargado);				
						}
                    ///////////////////////////////////////////////////////////////////////////////////////
                    // RPOCESO DE LA MATRICULA.
                    ///////////////////////////////////////////////////////////////////////////////////////
                        $pn = true;
                        $certificado = true;
                        // graba el codigo del alumno en la tabla alumno matricula. para poder generar el codigo matricula.
                            $query_matricula = "INSERT INTO alumno_matricula (codigo_alumno) VALUES (".$codigo_alumno.")";
                        // Ejecutamos el Query.
                            $consulta = $dblink -> query($query_matricula);
                    
                        // Consultar a la tabla el ultimo ingresado de la tabla alumno matricula, para que pueda generar el codigo de la matricula.				
                            $query_consulta_matricula = "SELECT codigo_alumno, id_alumno_matricula from alumno_matricula where codigo_alumno = ".$codigo_alumno." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
                        // Ejecutamos el Query.
                            $result_consulta = $dblink -> query($query_consulta_matricula);
                                while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                    {$fila_alumno = $row{0}; $fila_matricula = $row{1};}
                            
                        // Actualizar la tabla alumno_matricula con codigos de bachillerato, grado, seccion, a�o lectivo y a�o lectivo.
                         $query_update_matricula = "UPDATE alumno_matricula SET codigo_bach_o_ciclo = '$codigo_modalidad',
                                codigo_grado = '$codigo_grado',
                                codigo_seccion = '$codigo_seccion',
                                codigo_ann_lectivo ='$codigo_ann_lectivo',
                                certificado = '$certificado',
                                pn = '$pn',
                                codigo_turno = '$codigo_turno',
                                fecha_ingreso = '$fecha_matricula'
                                WHERE codigo_alumno = ".$fila_alumno." and id_alumno_matricula = ".$fila_matricula;
                        // Ejecutar Query.
                            $consulta = $dblink -> query($query_update_matricula);
            
                        // Consultar a la tabla alumno_matricula y codigo bachillerato o ciclo.
                            $query_consulta = "SELECT codigo_bach_o_ciclo from alumno_matricula where codigo_alumno = ".$codigo_alumno." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
                            $result_consulta = $dblink -> query($query_consulta);
                                while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                    {$fila_codigo_bachillerato = $row{0};}
            
                        // Consultar a la tabla codigo asignatura, para generar el codigo individual de cada una de ellas segun el ciclo o bachillerato.
                            $query_consulta_asignatura = "SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = '".$fila_codigo_bachillerato."' and codigo_ann_lectivo = '".$codigo_ann_lectivo."' and codigo_grado = '".$codigo_grado."' ORDER BY codigo_asignatura ASC";
                                $result_consulta = $dblink -> query($query_consulta_asignatura);
                                    while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                    {
                                        $fila_codigo_asignatura = $row{0};      
                                        $query_insert = "INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES ('$fila_codigo_asignatura',$fila_alumno,$fila_matricula)";
                                        $result_consulta_insert_notas = $dblink -> query($query_insert);
                                    }                            
                        ///////////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////////
					if($resultadoQuery == true){
						$respuestaOK = true;
						$mensajeError = "Si Save";
						$contenidoOK = 'Se ha agregado el registro correctamente';
					}
					else{
						$mensajeError = "No se puede guardar el registro en la base de datos ".$query;
					}
			break;
			
			case 'EditarRegistro':
				// armar variables.
				// TABS-1
				$apellido_materno = trim($_POST['apellido_materno']);
				$apellido_paterno = trim($_POST['apellido_paterno']);
				$nombre_completo = trim($_POST['txtnombres']);
				$direccion_alumno = trim($_POST['direccion_alumno']);
				$nie = trim($_POST['nie']);
				$telresidencia = trim($_POST['telresidencia']);
				$telcelular = trim($_POST['telcelular']);
				$email_alumno = trim($_POST['email_alumno']);
				$medicamento_alumno = trim($_POST['medicamento_alumno']);
				
				//TABS-2
				$fecha_nacimiento = trim($_POST['fechanacimiento']);
                // Actualizar el PN si existe el documento.
                //$pn_documento = trim($_POST['pn_boolean']);
                //if($pn_documento == "si"){$pn_doc = "t";}else{$pn_doc = "f";}
				$numero = trim($_POST['numero_pn']);
				$folio = trim($_POST['folio_pn']);
				$tomo = trim($_POST['tomo_pn']);
				$libro = trim($_POST['libro_pn']);
				$edad = trim($_POST['edad_enviar']);
				//crear una variable diferente ppara el genero, guardar en campo genero y .
				$codigo_genero = trim($_POST['lstgenero']);
				$codigo_estado_civil = trim($_POST['lstEstadoCivil']);
				$codigo_departamento = trim($_POST['lstdepartamento']);
				$codigo_municipio = trim($_POST['lstmunicipio']);
				$codigo_estado_familiar = trim($_POST['lstestadofamiliar']);
				$codigo_actividad_economica = trim($_POST['lstactividadeconomica']);
				$codigo_tipo_discapacidad = trim($_POST['lsttipodiscapacidad']);
				$codigo_servicio_apoyo_educativo = trim($_POST['lstservicioapoyoeducativo']);
				$codigo_zona_residencia = trim($_POST['lstzonaresidencia']);
				
				// Tabs-3
				$codigo_estatus = trim($_POST['lstEstatus']);
                // TAB-5 - MATRICULAR
				$codigo_ann_lectivo = $_POST["lstannlectivo"];
				$codigo_modalidad = $_POST["lstmodalidad"];
				$codigo_gst = $_POST["lstgradoseccion"];
				$codigo_grado = substr($_POST["lstgradoseccion"],0,2);
				$codigo_seccion = substr($_POST["lstgradoseccion"],2,2);
				$codigo_turno = substr($_POST["lstgradoseccion"],4,2);
				$fecha_matricula = trim($_POST['txtfechaMatricula']);
					if(isset($_POST["chkCrearMatricula"])){$CrearMatricula = "yes";}else{$CrearMatricula = "no";}
                // cambiar el valor para genero por el campo que lo toma como "m" o "f"
					if($codigo_genero === '01'){$genero = "m";}else{$genero = "f";}
                    
				// Tabs-4
				// Informaci�n del Padre/Madre o Encargado(a)
                    $fecha_nacimiento_e = array($_POST["txtfechanacimientop"],$_POST["txtfechanacimientom"],$_POST["txtfechanacimientoo"]);
                    $codigo_nacionalidad_e = array($_POST["lstNacionalidadP"],$_POST["lstNacionalidadM"],$_POST["lstNacionalidadO"]);
                    $codigo_estado_familiar_e = array($_POST["lstEstadoFamiliarP"],$_POST["lstEstadoFamiliarM"],$_POST["lstEstadoFamiliarO"]);
                    $codigo_zona_e = array($_POST["lstZonaResidenciaP"],$_POST["lstZonaResidenciaM"],$_POST["lstZonaResidenciaO"]);
                    $codigo_departamento_e = array($_POST["lstDepartamentoP"],$_POST["lstDepartamentoM"],$_POST["lstDepartamentoO"]);
                    $codigo_municipio_e = array($_POST["lstMunicipioP"],$_POST["lstMunicipioM"],$_POST["lstMunicipioO"]);
                
					$nombre_padre = array($_POST["txtnombrep"],$_POST["txtnombrem"],$_POST["txtnombreo"]);
					$lugar_padre = array($_POST["txtlugarp"],$_POST["txtlugarm"],$_POST["txtlugaro"]);
					$pop_padre = array($_POST["txtpop"],$_POST["txtpom"],$_POST["txtpoo"]);
					$dui_padre = array(trim($_POST["txtduip"]),trim($_POST["txtduim"]),trim($_POST["txtduio"]));
					$telefono_padre = array($_POST["txttelefonop"],$_POST["txttelefonom"],$_POST["txttelefonoo"]);	   
					$direccion_padre = array($_POST["txtdireccionp"],$_POST["txtdireccionm"],$_POST["txtdirecciono"]);
					$idaencargado = array($_POST["txtidep"],$_POST["txtidem"],$_POST["txtideo"]);
					
					if ($_POST["rdencargado"] == 'Padre'){$en = 't';}else{$en = 'f';}
					if ($_POST["rdencargado"] == 'Madre'){$en1 = 't';}else{$en1 = 'f';}
					if ($_POST["rdencargado"] == 'Otro'){$en2 = 't';}else{$en2 = 'f';}
					
					$encargado = array($en,$en1,$en2);
					
				//$ = trim($_POST['']);
				// armar consulta para guardar datos del alumno.
				$query = sprintf("UPDATE alumno SET apellido_materno='%s', apellido_paterno = '%s', nombre_completo = '%s',
						 direccion_alumno = '%s', codigo_nie = '%s', telefono_alumno = '%s', telefono_celular = '%s', direccion_email = '%s', medicamento = '%s',
						  fecha_nacimiento = '%s', pn_numero = '%s', pn_folio = '%s', pn_tomo = '%s', pn_libro = '%s', edad = '%s', codigo_genero = '%s',
						  codigo_estado_civil = '%s', codigo_departamento = '%s', codigo_municipio = '%s', codigo_estado_familiar = '%s', codigo_actividad_economica = '%s',
						  codigo_discapacidad = '%s', codigo_apoyo_educativo = '%s', codigo_zona_residencia = '%s',
						  codigo_estatus = '%s', genero = '%s'
							WHERE id_alumno=%d",
							$apellido_materno,$apellido_paterno,$nombre_completo,
							$direccion_alumno,$nie,$telresidencia,$telcelular,$email_alumno,$medicamento_alumno,
							$fecha_nacimiento,$numero,$folio,$tomo,$libro,$edad,$codigo_genero,
							$codigo_estado_civil,$codigo_departamento,$codigo_municipio,$codigo_estado_familiar,$codigo_actividad_economica,
							$codigo_tipo_discapacidad,$codigo_servicio_apoyo_educativo,$codigo_zona_residencia,
							$codigo_estatus, $genero
							,$_POST['id_user']);
                    //, $pn_doc , partida_nacimiento = '%s'
							
				// Ejecutamos el query guardar los datos en la tabla alumno..
				$resultadoQuery = $dblink -> query($query);

				// Validamos que se haya actualizado el registro
				   if($resultadoQuery == true){					
					// Actualizar valores del encargado.
					for ($i=0;$i<=2;$i++){
                        // validar fecha.
                        if(empty($fecha_nacimiento_e[$i]))
                        {
                         $fecha_nacimiento_e[$i] = "01/01/2019";   
                        }
                        
						$query_encargado = sprintf("UPDATE alumno_encargado SET nombres = '%s', lugar_trabajo = '%s', profesion_oficio = '%s', dui = '%s',
							telefono = '%s', direccion = '%s', encargado = '%s', fecha_nacimiento = '%s', codigo_nacionalidad = '%s', codigo_familiar = '%s',
                            codigo_zona = '%s', codigo_departamento = '%s', codigo_municipio = '%s'
							WHERE codigo_alumno = %d and id_alumno_encargado = %d",
							$nombre_padre[$i],$lugar_padre[$i],$pop_padre[$i],$dui_padre[$i],$telefono_padre[$i],$direccion_padre[$i],
							$encargado[$i], $fecha_nacimiento_e[$i], $codigo_nacionalidad_e[$i], $codigo_estado_familiar_e[$i], $codigo_zona_e[$i], $codigo_departamento_e[$i],
                            $codigo_municipio_e[$i]
							,$_POST['id_user'],$idaencargado[$i]);	

						// Ejecutamos el query guardar los datos en la tabla alumno..
						$resultadoQueryEncargado = $dblink -> query($query_encargado);				
						}

					///////////////////////////////////////////////////////////////////////////////////////
                    // RPOCESO DE LA MATRICULA.
					///////////////////////////////////////////////////////////////////////////////////////
					if($CrearMatricula == "yes")
					{
					
					$codigo_alumno = $_POST["id_user"];
					$pn = true;
					$certificado = true;
					// graba el codigo del alumno en la tabla alumno matricula. para poder generar el codigo matricula.
						$query_matricula = "INSERT INTO alumno_matricula (codigo_alumno) VALUES (".$codigo_alumno.")";
					// Ejecutamos el Query.
						$consulta = $dblink -> query($query_matricula);
				
					// Consultar a la tabla el ultimo ingresado de la tabla alumno matricula, para que pueda generar el codigo de la matricula.				
						$query_consulta_matricula = "SELECT codigo_alumno, id_alumno_matricula from alumno_matricula where codigo_alumno = ".$codigo_alumno." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
					// Ejecutamos el Query.
						$result_consulta = $dblink -> query($query_consulta_matricula);
							while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
								{$fila_alumno = $row{0}; $fila_matricula = $row{1};}
						
					// Actualizar la tabla alumno_matricula con codigos de bachillerato, grado, seccion, a�o lectivo y a�o lectivo.
					 $query_update_matricula = "UPDATE alumno_matricula SET codigo_bach_o_ciclo = '$codigo_modalidad',
							codigo_grado = '$codigo_grado',
							codigo_seccion = '$codigo_seccion',
							codigo_ann_lectivo ='$codigo_ann_lectivo',
							certificado = '$certificado',
							pn = '$pn',
							codigo_turno = '$codigo_turno',
							fecha_ingreso = '$fecha_matricula'
							WHERE codigo_alumno = ".$fila_alumno." and id_alumno_matricula = ".$fila_matricula;
					// Ejecutar Query.
						$consulta = $dblink -> query($query_update_matricula);
		
					// Consultar a la tabla alumno_matricula y codigo bachillerato o ciclo.
						$query_consulta = "SELECT codigo_bach_o_ciclo from alumno_matricula where codigo_alumno = ".$codigo_alumno." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
						$result_consulta = $dblink -> query($query_consulta);
							while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
								{$fila_codigo_bachillerato = $row{0};}
		
					// Consultar a la tabla codigo asignatura, para generar el codigo individual de cada una de ellas segun el ciclo o bachillerato.
						$query_consulta_asignatura = "SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = '".$fila_codigo_bachillerato."' and codigo_ann_lectivo = '".$codigo_ann_lectivo."' and codigo_grado = '".$codigo_grado."' ORDER BY codigo_asignatura ASC";
							$result_consulta = $dblink -> query($query_consulta_asignatura);
								while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
								{
									$fila_codigo_asignatura = $row{0};      
									$query_insert = "INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES ('$fila_codigo_asignatura',$fila_alumno,$fila_matricula)";
									$result_consulta_insert_notas = $dblink -> query($query_insert);
								}                            
					///////////////////////////////////////////////////////////////////////////////////////
					///////////////////////////////////////////////////////////////////////////////////////
				}
					$respuestaOK = true;
					$mensajeError = 'Si Update';
					$contenidoOK = 'Se ha Afectado '.' Registro(s).<br>'.'Se ha afectado ' . $CrearMatricula;
				}else{
					$mensajeError = 'No se ha actualizado el registro'.$query;
				}
			break;
		
			case 'eliminarEstudiante':
				$id_ = $_REQUEST["id_estudiante"];
				// Armamos el query
				$query = "DELETE FROM alumno WHERE id_alumno = '$id_';
						  DELETE FROM alumno_encargado WHERE codigo_alumno = '$id_';
						  DELETE FROM alumno_matricula WHERE codigo_alumno = '$id_';";

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

            case 'ActualizarTabla':
                // armar variables y consulta Query.
				$codigo_matricula[] = $_POST["codigo_matricula"];
                $repitente[] = $_POST["repitente"];
				$retirado[] = $_POST["retirado"];
                $fila = $_POST["fila"];
			
				$fila = $fila - 1;
                // recorrer la array para extraer los datos.
				for($i=0;$i<=$fila;$i++){
					$codigo_m = $codigo_matricula[0][$i];
					
					$r = $repitente[0][$i];
					$re = $retirado[0][$i];
					// armar sql para actualizar tabla alumno_matricula.
					$query_matricula = "UPDATE alumno_matricula SET 
										repitente = '$r',
										retirado = '$re'
											WHERE id_alumno_matricula = '$codigo_m'";
					// Ejecutamos el Query.
					$consulta_matricula = $dblink -> query($query_matricula);
				}                
                $respuestaOK = true;
                $mensajeError = "";
                $contenidoOK = "";
            break;
            // PROCESO PARA ELIMINAR LA MATRICULA.
            case 'eliminarMatricula':
                $codigo_matricula = $_POST["codigo_matricula"];
                $codigo_alumno = $_POST["codigo_alumno"];
				// Armamos el query
				$query_matricula = "DELETE FROM alumno_matricula WHERE codigo_alumno = $codigo_alumno and id_alumno_matricula = $codigo_matricula";
				$query_nota = "DELETE FROM nota WHERE codigo_alumno = $codigo_alumno and codigo_matricula = $codigo_matricula";

				// Ejecutamos el query
					$count = $dblink -> exec($query_matricula);
					$count2 = $dblink -> exec($query_nota);
				
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Matricula Borrada';
					$contenidoOK = '';

				}else{
                    $respuestaOK = false;
					$mensajeError = 'Matricula No Borrada';
				}
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
// Salida de la Array con JSON.
	if($_POST["accion"] === "BuscarTodos" or $_POST["accion"] === "BuscarTodosCodigo"){
		echo json_encode($arreglo);	
	}elseif($_POST["accion"] === "BuscarCodigo" or $_POST["accion"] === "GenerarCodigoNuevo" or $_POST["accion"] === "EditarRegistro2"){
		echo json_encode($datos);
		}
	else{
		// Armamos array para convertir a JSON
		$salidaJson = array("respuesta" => $respuestaOK,
			"mensaje" => $mensajeError,
			"contenido" => $contenidoOK);
		echo json_encode($salidaJson);
	}
?>