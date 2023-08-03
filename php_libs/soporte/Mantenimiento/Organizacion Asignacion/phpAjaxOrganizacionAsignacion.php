<?php
//session_name('demoUI');
//session_start();
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: text/html;charset=iso-8859-1");
// Insertar y actualizar tabla de usuarios
//sleep(1);

// Inicializamos variables de mensajes y JSON
$datos = array();
$respuestaOK = false;
$mensajeError = "No Registro";
$contenidoOK = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos

include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// Validar conexión con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO ORGANIZACION HORARIOS DE EXAMENES POR PERIODO.
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarHorarios':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_annlectivo = $_POST['codigo_annlectivo'];
				$codigo_modalidad = $_POST['codigo_modalidad'];
				// Armamos el query.
					$query = "SELECT pc.id_, pc.codigo_modalidad, pc.codigo_estatus, pc.fecha_desde, pc.fecha_hasta, pc.fecha_registro_academico,
						ann.nombre as descripcion_annlectivo,
						bach.codigo, bach.nombre as descripcion_modalidad,
						cat_p.codigo, cat_p.descripcion as descripcion_periodo
							FROM periodo_calendario pc
								INNER JOIN ann_lectivo ann ON ann.codigo = pc.codigo_annlectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = pc.codigo_modalidad
								INNER JOIN catalogo_periodo cat_p ON cat_p.id_ = pc.codigo_periodo
									WHERE codigo_annlectivo = '$codigo_annlectivo' and codigo_modalidad = '$codigo_modalidad'
										ORDER BY cat_p.codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$mensaje = "Si Registro";
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_ = trim($listado['id_']);
					$nombre_annlectivo = trim($listado['descripcion_annlectivo']);
					$nombre_modalidad = trim($listado['descripcion_modalidad']);
					$nombre_periodo = trim($listado['descripcion_periodo']);
					$codigo_estatus = trim($listado['codigo_estatus']);
					$fecha_desde = cambiaf_a_normal(trim($listado['fecha_desde']));
					$fecha_hasta = cambiaf_a_normal(trim($listado['fecha_hasta']));
					$fecha_registro_academico = cambiaf_a_normal(trim($listado['fecha_registro_academico']));
					$num++;
					// VARIABLES ESTATUS.
					if($codigo_estatus == '01'){
						$estatus = "<td><span class='badge badge-pill badge-info'>Activo</span></td>";
					}else{
						$estatus = "<td><span class='badge badge-pill badge-danger'>Inactivo</span></td>";
					}
					//
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$nombre_periodo
							<td>$fecha_desde
							<td>$fecha_hasta
							<td>$fecha_registro_academico
							$estatus
							<td><a data-accion=EditarHorarios class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href=$id_><i class='fas fa-edit'></i></a>
							<a data-accion=EliminarHorarios class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href=$id_><i class='fas fa-trash'></i></a>"
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'EditarHorarios':
				$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT * FROM periodo_calendario WHERE id_ = '$id_'";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
						$id_ = trim($listado['id_']);
						$codigo_estatus = trim($listado['codigo_estatus']);
						$codigo_periodo = trim($listado['codigo_periodo']);
						$fecha_desde = trim($listado['fecha_desde']);
						$fecha_hasta = trim($listado['fecha_hasta']);
						$fecha_registro_academico = trim($listado['fecha_registro_academico']);
					//
						$datos[$fila_array]["id_"] = $id_;
						$datos[$fila_array]["codigo_estatus"] = $codigo_estatus;
						$datos[$fila_array]["codigo_periodo"] = $codigo_periodo;
						$datos[$fila_array]["fecha_desde"] = $fecha_desde;
						$datos[$fila_array]["fecha_hasta"] = $fecha_hasta;
						$datos[$fila_array]["fecha_registro_academico"] = $fecha_registro_academico;

						$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarHorarios':
				$id_ = $_POST['IdHorarios'];
				// Armamos el query y iniciamos variables.
				$codigo_annlectivo = ($_POST['codigo_annlectivo']);
				$codigo_modalidad = ($_POST['codigo_modalidad']);
				$codigo_estatus = $_REQUEST['lstHorarios'];
				$codigo_periodo = ($_POST['lstPeriodosHorarios']);
				$FechaInicio = ($_POST['FechaInicio']);
				$FechaFin = ($_POST['FechaFin']);
				$FechaRA = ($_POST['FechaRA']);
				// ESTATUS
				if($codigo_estatus == '01'){
					$estatus = "1";
				}else{
					$estatus = "0";
				}
				// Armamos el query y iniciamos variables.
					$query = "UPDATE periodo_calendario 
							SET fecha_desde = '$FechaInicio',
								fecha_hasta = '$FechaFin',
								fecha_registro_academico = '$FechaRA',
								codigo_periodo = '$codigo_periodo',
								codigo_estatus = '$codigo_estatus',
								estatus = '$estatus'
									WHERE id_ = '$id_'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
				break;
			case 'GuardarHorarios':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
					$codigo_annlectivo = ($_POST['codigo_annlectivo']);
					$codigo_modalidad = ($_POST['codigo_modalidad']);
					$codigo_estatus = $_REQUEST['lstHorarios'];
					$codigo_periodo = ($_POST['lstPeriodosHorarios']);
					$FechaInicio = ($_POST['FechaInicio']);
					$FechaFin = ($_POST['FechaFin']);
					$FechaRA = ($_POST['FechaRA']);
				// ESTATUS
					if($codigo_estatus == '01'){
						$estatus = "1";
					}else{
						$estatus = "0";
					}
				 	$query = "SELECT * FROM periodo_calendario 
						WHERE codigo_annlectivo = '$codigo_annlectivo' and codigo_modalidad = '$codigo_modalidad' and codigo_periodo = '$codigo_periodo'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "El Nivel y Periodo ya Existen.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO periodo_calendario (codigo_annlectivo, codigo_modalidad, fecha_desde, fecha_hasta, fecha_registro_academico, codigo_periodo, codigo_estatus, estatus) 
							VALUES ('$codigo_annlectivo','$codigo_modalidad','$FechaInicio','$FechaFin','$FechaRA','$codigo_periodo','$codigo_estatus','$estatus')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado";
					$mensajeError = "Si Registro";
				}
			break;
			case 'EliminarHorarios':
				$id_ = $_REQUEST['id_'];
						// Armamos el query
						$query = "DELETE FROM periodo_calendario WHERE id_ = '$id_'";
						// Ejecutamos el query
							$count = $dblink -> exec($query);
						// Validamos que se haya actualizado el registro
						if($count != 0){
							$respuestaOK = true;
							$mensajeError = "Se ha eliminado el registro correctamente";

							$contenidoOK = "Se ha Eliminado $count Registro(s).";
						}else{
							$mensajeError = "No se ha eliminado el registro";
						}
			break;
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO ORGANIZACION MODALIDAD Y AÑO LECTIVO.
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarAnnLectivoModalidad':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_ann_lectivo = $_POST['codigo_annlectivo'];
				// Armamos el query.
					$query = "SELECT orgac.id_organizar_ann_lectivo_ciclos, orgac.codigo_ann_lectivo, orgac.codigo_bachillerato,
							ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad
								FROM organizar_ann_lectivo_ciclos orgac
								INNER JOIN ann_lectivo ann ON ann.codigo = orgac.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgac.codigo_bachillerato
								WHERE orgac.codigo_ann_lectivo = '$codigo_ann_lectivo' ORDER BY orgac.codigo_bachillerato";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$mensaje = "Si Registro";
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$codigo_modalidad = trim($listado['codigo_bachillerato']);
					$codigo_ann_lectivo = trim($listado['codigo_ann_lectivo']);
					$id_org_ann_lectivo_ciclos = trim($listado['id_organizar_ann_lectivo_ciclos']);
					$nombre_modalidad = trim($listado['nombre_modalidad']);
					$nombre_ann_lectivo = trim($listado['nombre_ann_lectivo']);
					$num++;
					// variables Json
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_org_ann_lectivo_ciclos
							.'<td class=centerTXT>'.$codigo_ann_lectivo
							.'<td class=centerTXT>'.$nombre_ann_lectivo
							.'<td class=centerTXT>'.$codigo_modalidad
							.'<td class=centerTXT>'.$nombre_modalidad
							.'<td class = centerTXT><a data-accion=editar_modalidad class="btn btn-xs btn-primary" href='.$listado['id_organizar_ann_lectivo_ciclos'].'>Editar</a>'
							.'<td class = centerTXT><a data-accion=eliminar_modalidad class="btn btn-xs btn-primary" href='.$listado['id_organizar_ann_lectivo_ciclos'].'>Eliminar</a>'
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'editar_modalidad':
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_bachillerato_ciclo, nombre, codigo FROM bachillerato_ciclo WHERE id_bachillerato_ciclo = ".$_POST['id_x']. " ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_bachillerato_ciclo = trim($listado['id_bachillerato_ciclo']);
					$nombre = trim($listado['nombre']);
					$codigo = trim($listado['codigo']);
					
					$datos[$fila_array]["id_modalidad"] = $id_bachillerato_ciclo;
					$datos[$fila_array]["codigo_modalidad"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'modificar_modalidad':
				$id_modalidad = $_POST['id_x'];
				$nombre = strtoupper($_POST['nombre_modalidad']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE bachillerato_ciclo SET nombre = '$nombre' WHERE id_bachillerato_ciclo = ". $id_modalidad;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarAnnLectivoModalidad':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
					$codigo_annlectivo = ($_POST['lstannlectivo']);
					$codigo_modalidad = ($_POST['lstmodalidad']);
						$query = "SELECT * FROM organizar_ann_lectivo_ciclos WHERE codigo_ann_lectivo = '$codigo_annlectivo' and codigo_bachillerato = '$codigo_modalidad'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
				//
				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Si Existe";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO organizar_ann_lectivo_ciclos (codigo_ann_lectivo, codigo_bachillerato) VALUES ('$codigo_annlectivo','$codigo_modalidad')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado";
					$mensajeError = "Si Registro";
				}
			break;
			case 'eliminar_modalidad':
				// Armamos el query
				$query = sprintf("DELETE FROM organizar_ann_lectivo_ciclos WHERE id_organizar_ann_lectivo_ciclos = %s",
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
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (ASIGNACIOND E ASIGNATURAS A LAS MODALIDADES)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarAAe':
				// Armamos el query.
				$query = "SELECT id_grado_ano, nombre, codigo FROM grado_ano ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo_grado"] = $codigo;	
					}
				}
			break;
			case 'BuscarAA':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_annlectivo = $_POST["codigo_annlectivo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				$codigo_grado = $_POST["codigo_grado"];
				// Armamos el query.
					$query = "SELECT aaa.codigo_asignacion, aaa.codigo_bach_o_ciclo, aaa.codigo_asignatura, aaa.codigo_ann_lectivo, aaa.codigo_sirai, aaa.codigo_grado, aaa.id_asignacion, aaa.orden,
								ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura
							FROM a_a_a_bach_o_ciclo aaa
								INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado
								INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura
									WHERE aaa.codigo_bach_o_ciclo = '$codigo_modalidad' and aaa.codigo_ann_lectivo = '$codigo_annlectivo' and aaa.codigo_grado = '$codigo_grado'
									ORDER BY aaa.orden";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$codigo_ann_lectivo = trim($listado['codigo_ann_lectivo']);
					$nombre_ann_lectivo = trim($listado['nombre_ann_lectivo']);
					$nombre_modalidad = trim($listado['nombre_modalidad']);
					$codigo_modalidad = trim($listado['codigo_bach_o_ciclo']);
					$codigo_grado = trim($listado['codigo_grado']);
					$nombre_grado = trim($listado['nombre_grado']);
					$codigo_asignatura = trim($listado["codigo_asignatura"]);
					$nombre_asignatura = trim($listado["nombre_asignatura"]);
					$codigo_sirai = trim($listado["codigo_sirai"]);
					$orden = trim($listado["orden"]);
					$id_ = trim($listado['id_asignacion']);
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_
							.'<td class=centerTXT>'.$codigo_ann_lectivo
							.'<td class=centerTXT>'.$nombre_ann_lectivo
							.'<td class=centerTXT>'.$codigo_modalidad
							.'<td class=centerTXT>'.$nombre_modalidad
							.'<td class=centerTXT>'.$codigo_grado
							.'<td class=centerTXT>'.$nombre_grado
							.'<td class=centerTXT>'.$codigo_asignatura
							.'<td class=centerTXT>'.$nombre_asignatura
							.'<td class=centerTXT>'."<input type=text id=codigo_sirai name=codigo_sirai value ='$codigo_sirai' class=form-control>"
							.'<td class=centerTXT>'."<input type=number id=orden name=orden value ='$orden' class=form-control>"
							.'<td class = centerTXT><a data-accion=editar_aaa class="btn btn-xs btn-primary" href='.$listado['id_asignacion'].'>Editar</a>'
							.'<td class = centerTXT><a data-accion=eliminar_aaa class="btn btn-xs btn-primary" href='.$listado['id_asignacion'].'>Eliminar</a>'
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'GuardarAA':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $codigo_ann_lectivo = ($_POST['lstannlectivoAA']);
				 $codigo_modalidad = ($_POST['lstmodalidadAA']);
				 $codigo_grado = $_POST["lstgradoAA"];
				 $codigo_asignatura = $_POST["lstasignatura"];
                 $TodasLasAsignaturas = $_POST["TodasLasAsignaturas"];
                 ///////////////////////////////////////////////////////////////////////////////////////////////
                 //// cambiar codigo para el servicio educativo
                 ///////////////////////////////////////////////////////////////////////////////////////////////
                 // CONDIONAL PARA EDUCACION INICIAL sección 1, 2, 3.
                            if($codigo_modalidad == "01")
                            {
                                if($codigo_grado == 'I1'){$codigo_se = "01";}
                                if($codigo_grado == 'I2'){$codigo_se = "02";}
                                if($codigo_grado == 'I3'){$codigo_se = "03";}
                                
                            }
                            
                            // CONDIONAL PARA Parvularia, sección 4, 5, 6.
                            if($codigo_modalidad == "02")
                            {
                                if($codigo_grado == '4P'){$codigo_se = "04";}
                                if($codigo_grado == '5P'){$codigo_se = "05";}
                                if($codigo_grado == '6P'){$codigo_se = "06";}
                                
                            }
                            // CONDIONAL PARA PRIMER CICLO Y SEGUNDO CICLO
                            if($codigo_modalidad >="03" and $codigo_modalidad <= "04")
                            {
                                $codigo_se = "07";
                            }
                            // CONDIONAL PARA PRIMER CICLO Y SEGUNDO CICLO
                            if($codigo_modalidad >="05" and $codigo_modalidad <= "05")
                            {
                                $codigo_se = "08";
                            }
                            // CONDIONAL PARA EL BACHILLERATO GENERAL
                            if($codigo_modalidad == "06")
                            {
                                    $codigo_se = "09";
                            }
                            // CONDIONAL PARA EL BACHILLERATO TECNICO 
                            if($codigo_modalidad == "07")
                            {
                                    $codigo_se = "10";
                            }
                            // CONDIONAL PARA EL BACHILLERATO TECNICO  TERCER AÑO CONTADOR
                            if($codigo_modalidad == "09")
                            {
                                    $codigo_se = "11";
                            }
                            // CONDIONAL PARA EL TERCER CICLO NOCTURNA MF.
                            if($codigo_modalidad == "10")
                            {
                                    $codigo_se = "13";
                            }
                            // CONDIONAL PARA EL BACHILLERATO GENERAL  NOCTURNA MF.
                            if($codigo_modalidad == "11")
                            {
                                    $codigo_se = "14";
                            }
							// CONDIONAL PARA EDUCACIÓN PARVULARIA - ESTANDAR DE DESARROLLO.
							if($codigo_modalidad == "13")
							{
								if($codigo_grado == '4P'){$codigo_se = "16";}
                                if($codigo_grado == '5P'){$codigo_se = "17";}
                                if($codigo_grado == '6P'){$codigo_se = "18";}
							}
							// CONDIONAL PARA EDUCACIÓN BASICA - ESTANDAR DE DESARROLLO.
							if($codigo_modalidad == "14")
							{
								if($codigo_grado == '01'){$codigo_se = "19";}
							}
                            if($_SESSION['codigo_perfil'] == '06'){
                            
                            }
                 ///////////////////////////////////////////////////////////////////////////////////////////////
                 //// SI TODAS LAS ASIGNATURAS ES IGUAL A YES
                 ///////////////////////////////////////////////////////////////////////////////////////////////
                 if($TodasLasAsignaturas == "yes"){
                    $numero = 1;
                     $query_todas = "SELECT codigo as codigo_asignatura, nombre as nombre_asignatura, ordenar
                                FROM asignatura
                                  WHERE imprimir = 'true' and estatus = 'true' and codigo_servicio_educativo = '$codigo_se'
                                    ORDER BY codigo_servicio_educativo, codigo_area, id_asignatura";
                     $consulta_todas = $dblink -> query($query_todas);
                    // recorrer las asignaturas
                            while($listado = $consulta_todas -> fetch(PDO::FETCH_BOTH))
                                {
                                 // Nombres de los campos de la tabla.
                                  $codigo_asignatura = trim($listado['codigo_asignatura']);
								  $ordenar = trim($listado['ordenar']);
                                                          // VERFICAR SI NO EXISTE ASIGNATURA.
                                    $query_buscar = "SELECT * FROM a_a_a_bach_o_ciclo WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_bach_o_ciclo = '$codigo_modalidad' and codigo_grado = '$codigo_grado' and codigo_asignatura = '$codigo_asignatura'";
                                   // Ejecutamos el Query.
                                   $consulta_buscar = $dblink -> query($query_buscar);
                   
                                   if($consulta_buscar -> rowCount() != 0){
                                       $respuestaOK = false;
                                       $contenidoOK = "";
                                       $mensajeError = "Si Existe";
                                   }else{
                                   // proceso para grabar el registro
                                       $query = "INSERT INTO a_a_a_bach_o_ciclo (codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_asignacion, codigo_grado, codigo_asignatura, orden) VALUES ('$codigo_ann_lectivo','$codigo_modalidad','$codigo_modalidad','$codigo_grado','$codigo_asignatura','$ordenar')";
                                   // Ejecutamos el Query.
                                        $consulta = $dblink -> query($query);
                                    // variables de retorno.
                                       $respuestaOK = true;
                                       $contenidoOK = $query;
                                       $mensajeError = "Si Registro";
                                   }
                                   // Incrementar para el numero de orden.
                                   $numero++;
                                } // final del while para recorrer las asignaturas
                 }else{
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    // PROCESO PARA GUARDAR UNA SOLA ASIGNATURA.
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                        // VERFICAR SI NO EXISTE ASIGNATURA.
                        $query = "SELECT * FROM a_a_a_bach_o_ciclo WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_bach_o_ciclo = '$codigo_modalidad' and codigo_grado = '$codigo_grado' and codigo_asignatura = '$codigo_asignatura'";
                       // Ejecutamos el Query.
                       $consulta = $dblink -> query($query);
       
                       if($consulta -> rowCount() != 0){
                           $respuestaOK = false;
                           $contenidoOK = "";
                           $mensajeError = "Si Existe";
                       }else{
                       // proceso para grabar el registro
                           $query = "INSERT INTO a_a_a_bach_o_ciclo (codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_asignacion, codigo_grado, codigo_asignatura) VALUES ('$codigo_ann_lectivo','$codigo_modalidad','$codigo_modalidad','$codigo_grado','$codigo_asignatura')";
                       // Ejecutamos el Query.
                       $consulta = $dblink -> query($query);
                           $respuestaOK = true;
                           $contenidoOK = "";
                           $mensajeError = "Si Registro";
                       }                    
                 }
                 

			break;
			case 'ActualizarCS':		
				// armar variables y consulta Query.
				$codigo_aa[] = $_POST["codigo_aa"];
				$codigo_asignatura[] = $_POST["codigo_asignatura"];
				$codigo_sirai[] = $_POST["codigo_sirai"];
				$orden[] = $_POST["orden"];
				
				// Variales.
				$fila = $_POST["fila"];
			
				$fila = $fila - 1;

				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila;$i++){
					$codigo_a = $codigo_aa[0][$i];
					$codigo_asig = $codigo_asignatura[0][$i];
					$codigo_cs = $codigo_sirai[0][$i];
					$orden_ = $orden[0][$i];
					
					// armar sql para actualizar tabla alumno_matricula.
					$query_aa = "UPDATE a_a_a_bach_o_ciclo SET
										codigo_sirai = '$codigo_cs',
										orden = '$orden_'
											WHERE id_asignacion = '$codigo_a'";
					// Ejecutamos el Query.
					$consulta_aa = $dblink -> query($query_aa);

					// armar sql para actualizar tabla nota.
					$query_aa_nota = "UPDATE nota SET
						orden = '$orden_'
						WHERE codigo_asignatura = '$codigo_asig'";
					// Ejecutamos el Query.
					$consulta_aa_nota = $dblink -> query($query_aa_nota);
				}

				$respuestaOK = true;
				$contenidoOK = 'Registros Actualizados.';
				$mensajeError =  'Si Registro';
			break;		
			case 'eliminar_aaa':
				$query = sprintf("DELETE FROM a_a_a_bach_o_ciclo WHERE id_asignacion = %s",
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
			case 'editar_grado':
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_grado_ano, nombre, codigo FROM grado_ano WHERE id_grado_ano = ".$_POST['id_x']. " ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_grado_ano = trim($listado['id_grado_ano']);
					$nombre = trim($listado['nombre']);
					$codigo = trim($listado['codigo']);
					
					$datos[$fila_array]["id_grado"] = $id_grado_ano;
					$datos[$fila_array]["codigo_grado"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'modificar_grado':
				$id_grado = $_POST['id_x'];
				$nombre = strtoupper($_POST['nombre_grado']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE grado_ano SET nombre = '$nombre' WHERE id_grado_ano = ". $id_grado;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'addGrado':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = strtoupper($_POST['nombre_grado']);
				 $codigo_grado = ($_POST['codigo_grado']);
				 $query = "SELECT id_grado_ano, nombre, codigo FROM grado_ano WHERE codigo = '".$codigo_grado. "' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO grado_ano (nombre, codigo) VALUES ('$nombre','$codigo_grado')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'eliminar_grado_seccion':
				// consultar el registro antes de agregarlo.
				 $id_ = ($_POST['id_']);
					$query = "SELECT * FROM organizacion_grados_secciones WHERE id_grados_secciones = '$id_'";
					// Ejecutamos el Query.
						$consulta = $dblink -> query($query);

					 while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                                {
                                 // Nombres de los campos de la tabla.
                                  $codigo_modalidad = trim($listado['codigo_bachillerato']);
								  $codigo_grado = trim($listado['codigo_grado']);
								  $codigo_seccion = trim($listado['codigo_seccion']);
								  $codigo_turno = trim($listado['codigo_turno']);
								  $codigo_ann_lectivo = trim($listado['codigo_ann_lectivo']);
                                } // final del while para recorrer las asignaturas
				//	BUSCAR EN LA MARICULA SI EXISTE UN DATO.
						$query_matricula = "SELECT * FROM alumno_matricula WHERE codigo_grado = '$codigo_grado' and codigo_bach_o_ciclo = '$codigo_modalidad' and codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_seccion = '$codigo_seccion' and codigo_turno = '$codigo_turno'";
						$consulta_matricula = $dblink -> query($query_matricula);
				//
				if($consulta_matricula -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este grado ya tiene registros";
					$mensajeError = "Este grado ya tiene registros";
				}else{
					// Armamos el query
					$query_eliminar = "DELETE FROM organizacion_grados_secciones WHERE id_grados_secciones = '$id_'";

					// Ejecutamos el query
						$count = $dblink -> exec($query_eliminar);

					// Validamos que se haya actualizado el registro
					if($count != 0){
						$respuestaOK = true;
						$mensajeError = 'Se ha eliminado el registro correctamente'.$query;

						$contenidoOK = 'Se ha Eliminado '.$count.' Registro(s).';

					}else{
						$mensajeError = 'No se ha eliminado el registro'.$query;
					}
				}
			break;	
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (AÑO LECTIVO)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoAnnLectivo':
				// Armamos el query.
				$query = "SELECT id_annlectivo, nombre, codigo, descripcion, fecha_inicio, fecha_fin, estatus FROM ann_lectivo ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo_annlectivo"] = $codigo;	
					}
				}
			break;
			case 'BuscarAnnLectivo':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT id_annlectivo, nombre, codigo, descripcion, fecha_inicio, fecha_fin, estatus FROM ann_lectivo ORDER BY codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$codigo = trim($listado['codigo']);
					$nombre = trim($listado['nombre']);
					$id_annlectivo = trim($listado['id_annlectivo']);
					$descripcion = trim($listado['descripcion']);
					$fecha_inicio = trim($listado['fecha_inicio']);
					$fecha_fin = trim($listado['fecha_fin']);
					$estatus = trim($listado['estatus']);
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_annlectivo
							.'<td class=centerTXT>'.$codigo
							.'<td class=centerTXT>'.$nombre
							.'<td class=centerTXT>'.$descripcion
							.'<td class=centerTXT>'.$fecha_inicio
							.'<td class=centerTXT>'.$fecha_fin
							.'<td class=centerTXT>'.$estatus
							.'<td class = centerTXT><a data-accion=editar_annlectivo class="btn btn-xs btn-primary" href='.$listado['id_annlectivo'].'>Editar</a>'
							.'<td class = centerTXT><a data-accion=eliminar_annlectivo class="btn btn-xs btn-primary" href='.$listado['id_annlectivo'].'>Eliminar</a>'
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'editar_annlectivo':
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_annlectivo, nombre, codigo, descripcion, fecha_inicio, fecha_fin, estatus FROM ann_lectivo WHERE id_annlectivo = ".$_POST['id_x']. " ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_annlectivo = trim($listado['id_annlectivo']);
					$nombre = trim($listado['nombre']);
					$codigo = trim($listado['codigo']);
					$descripcion = trim($listado['descripcion']);
					$fecha_inicio = trim($listado['fecha_inicio']);
					$fecha_fin = trim($listado['fecha_fin']);
					$estatus = trim($listado['estatus']);
					
					$datos[$fila_array]["id_annlectivo"] = $id_annlectivo;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$datos[$fila_array]["descripcion"] = $descripcion;
					$datos[$fila_array]["fecha_inicio"] = $fecha_inicio;
					$datos[$fila_array]["fecha_fin"] = $fecha_fin;
					$datos[$fila_array]["estatus"] = $estatus;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'modificar_annlectivo':
				$id_annlectivo = $_POST['id_x'];
				$descripcion = strtoupper($_POST['descripcion']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE ann_lectivo SET
						descripcion = '$descripcion'
							WHERE id_annlectivo = ". $id_annlectivo;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'addAnnLectivo':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = strtoupper($_POST['nombreAnnLectivo']);
				 $codigo_annlectivo = ($_POST['codigoAnnLectivo']);
				 $descripcion = ($_POST['descripcion']);
				 $fecha_inicio = ($_POST['fecha_inicio']);
				 $fecha_fin = ($_POST['fecha_fin']);
				 $estatus = ($_POST['estatus']);

				 if($estatus == "yes"){$estatus = 1;}else{$estatus = 0;}
				 // Ar,ar qieru àra evañiar-
				 $query = "SELECT id_annlectivo, nombre, codigo FROM ann_lectivo WHERE codigo = '".$codigo_annlectivo. "' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "No Registro";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO ann_lectivo (nombre, codigo, descripcion, fecha_inicio, fecha_fin, estatus) VALUES ('$nombre','$codigo_annlectivo','$descripcion','$fecha_inicio','$fecha_fin','$estatus')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado.".$query;
					$mensajeError = "Si Registro";
				}
			break;
			case 'eliminar_annlectivo':
				// Armamos el query
				$query = sprintf("DELETE FROM ann_lectivo WHERE id_annlectivo = %s",
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
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (SECCIONES)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoSeccion':
				// Armamos el query.
				$query = "SELECT id_seccion, nombre, codigo FROM seccion ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo_seccion"] = $codigo;	
					}
				}
			break;
			case 'BuscarGradoSeccion':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_ann_lectivo = trim($_POST["codigo_annlectivo"]);
				$codigo_modalidad = trim($_POST["codigo_modalidad"]);
				// Armamos el query.
					$query = "SELECT org.id_grados_secciones, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_ann_lectivo, org.codigo_turno,
							ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
							FROM organizacion_grados_secciones org 
								INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
								INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
								INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
								INNER JOIN turno tur ON tur.codigo = org.codigo_turno
									WHERE org.codigo_ann_lectivo = '$codigo_ann_lectivo' and org.codigo_bachillerato = '$codigo_modalidad' ORDER BY org.codigo_ann_lectivo, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_turno";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$codigo_ann_lectivo = trim($listado['codigo_ann_lectivo']);
					$nombre_ann_lectivo = trim($listado['nombre_ann_lectivo']);
					$nombre_modalidad = trim($listado['nombre_modalidad']);
					$codigo_modalidad = trim($listado['codigo_bachillerato']);
					$codigo_grado = trim($listado['codigo_grado']);
					$nombre_grado = trim($listado['nombre_grado']);
					$nombre_seccion = trim($listado['nombre_seccion']);
					$codigo_seccion = trim($listado['codigo_seccion']);
					$codigo_turno = trim($listado['codigo_turno']);
					$nombre_turno = trim($listado['nombre_turno']);
					$id_ = trim($listado['id_grados_secciones']);
					//$id_ = trim($listado['id_grados_secciones']) . "-" . $codigo_modalidad . "-" . $codigo_grado . "-" . $codigo_seccion . "-" . $codigo_turno . "-" . $codigo_ann_lectivo;
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_
							.'<td class=centerTXT>'.$codigo_ann_lectivo
							.'<td class=centerTXT>'.$nombre_ann_lectivo
							.'<td class=centerTXT>'.$codigo_modalidad
							.'<td class=centerTXT>'.$nombre_modalidad
							.'<td class=centerTXT>'.$codigo_grado
							.'<td class=centerTXT>'.$nombre_grado
							.'<td class=centerTXT>'.$codigo_seccion
							.'<td class=centerTXT>'.$nombre_seccion
							.'<td class=centerTXT>'.$codigo_turno
							.'<td class=centerTXT>'.$nombre_turno
							.'<td class = centerTXT><a data-accion=editar_seccion class="btn btn-xs btn-primary" href='.$listado['id_grados_secciones'].'>Editar</a>'
							.'<td class = centerTXT><a data-accion=eliminar_grado_seccion class="btn btn-xs btn-primary" href='.$listado['id_grados_secciones'].'>Eliminar</a>'
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'editar_seccion':
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_seccion, nombre, codigo FROM seccion WHERE id_seccion = ".$_POST['id_x']. " ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_seccion = trim($listado['id_seccion']);
					$nombre = trim($listado['nombre']);
					$codigo = trim($listado['codigo']);
					
					$datos[$fila_array]["id_seccion"] = $id_seccion;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'modificar_seccion':
				$id_seccion = $_POST['id_x'];
				$nombre = strtoupper($_POST['nombre_seccion']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE seccion SET nombre = '$nombre' WHERE id_seccion = ". $id_seccion;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarGradoSeccion':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $codigo_ann_lectivo = ($_POST['lstannlectivoGradoSeccion']);
				 $codigo_modalidad = ($_POST['lstmodalidadGradoSeccion']);
				 $codigo_grado = $_POST["lstgradoseccion"];
				 $codigo_seccion = $_POST["lstseccion"];
				 $codigo_turno = $_POST["lstturno"];
				 $query = "SELECT * FROM organizacion_grados_secciones WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_bachillerato = '$codigo_modalidad' and codigo_grado = '$codigo_grado' and codigo_seccion = '$codigo_seccion' and codigo_turno = '$codigo_turno'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "";
					$mensajeError = "Si Existe";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO organizacion_grados_secciones (codigo_ann_lectivo, codigo_bachillerato, codigo_grado, codigo_seccion, codigo_turno) VALUES ('$codigo_ann_lectivo','$codigo_modalidad','$codigo_grado','$codigo_seccion','$codigo_turno')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "";
					$mensajeError = "Si Registro";
				}
			break;
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (ORGANIZAR PERSONAL DOCENTE O ADMINISTRATIVO)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarPM':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_annlectivo = $_POST["codigo_annlectivo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				// Armamos el query.
					 $query = "SELECT orgpda.id_organizar_planta_docente_ciclos, orgpda.codigo_bachillerato, orgpda.codigo_ann_lectivo, orgpda.codigo_turno, orgpda.codigo_docente,
								ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, btrim(p.nombres || p.apellidos) as nombre_personal, p.id_personal
							FROM organizar_planta_docente_ciclos orgpda
								INNER JOIN ann_lectivo ann ON ann.codigo = orgpda.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgpda.codigo_bachillerato
								INNER JOIN personal p ON p.id_personal = orgpda.codigo_docente
									WHERE orgpda.codigo_bachillerato = '$codigo_modalidad' and orgpda.codigo_ann_lectivo = '$codigo_annlectivo'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$codigo_ann_lectivo = trim($listado['codigo_ann_lectivo']);
					$nombre_ann_lectivo = trim($listado['nombre_ann_lectivo']);
					$nombre_modalidad = trim($listado['nombre_modalidad']);
					$codigo_modalidad = trim($listado['codigo_bachillerato']);
					$codigo_personal = trim($listado["id_personal"]);
					$nombre_personal = trim($listado["nombre_personal"]);

					$id_ = trim($listado['id_organizar_planta_docente_ciclos']);
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_
							.'<td class=centerTXT>'.$codigo_ann_lectivo
							.'<td class=centerTXT>'.$nombre_ann_lectivo
							.'<td class=centerTXT>'.$codigo_modalidad
							.'<td class=centerTXT>'.$nombre_modalidad
							.'<td class=centerTXT>'.$codigo_personal
							.'<td class=centerTXT>'.$nombre_personal
							.'<td class = centerTXT><a data-accion=editar_pda class="btn btn-xs btn-primary" href='.$id_.'>Editar</a>'
							.'<td class = centerTXT><a data-accion=eliminar_pda class="btn btn-xs btn-primary" href='.$id_.'>Eliminar</a>'
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
		case 'GuardarPM':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $codigo_ann_lectivo = ($_POST['lstannlectivoPM']);
				 $codigo_modalidad = ($_POST['lstmodalidadPM']);
				 $codigo_personal = $_POST["lstpersonalPM"];
				 $codigo_turno = $_POST["lstturnoPM"];
				// armar query.
				 $query = "SELECT * FROM organizar_planta_docente_ciclos WHERE codigo_docente = '$codigo_personal' and codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_bachillerato = '$codigo_modalidad' and codigo_turno = '$codigo_turno'";
				 // Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "";
					$mensajeError = "Si Existe";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO organizar_planta_docente_ciclos (codigo_ann_lectivo, codigo_bachillerato, codigo_turno, codigo_docente) VALUES ('$codigo_ann_lectivo','$codigo_modalidad','$codigo_turno','$codigo_personal')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "";
					$mensajeError = "Si Registro";
				}
			break;
			case 'eliminar_pda':
				// Armamos el query
				$query = sprintf("DELETE FROM organizar_planta_docente_ciclos WHERE id_organizar_planta_docente_ciclos = %s",
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
				$mensajeError = 'No Registro';
			break;
		}
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicación';}
}
else{
	$mensajeError = 'No se puede establecer conexión con la base de datos';
}	// FIN DE LA CONDICON PRINCRIPAL
// CONDICIONES RESULTADO DEL JSON Y DATA[]
if($_POST['accion'] == "BuscarHorarios" || $_POST['accion'] == "GuardarHorarios" || $_POST['accion'] == "ActualizarHorarios"  || $_POST['accion'] == "EliminarHorarios"  
	|| $_POST['accion'] == "BuscarAnnLectivo" || $_POST['accion'] == "addAnnLectivo" || $_POST['accion'] == "BuscarGrado" 
	|| $_POST['accion'] == "addGrado" || $_POST['accion'] == "modificar_annlectivo" || $_POST['accion'] == "BuscarAnnLectivoModalidad" || $_POST['accion'] == "modificar_modalidad" 
	|| $_POST['accion'] == "eliminar_modalidad" || $_POST['accion'] == "GuardarAnnLectivoModalidad" || $_POST['accion'] == "BuscarAA" || $_POST['accion'] == "GuardarAA" 
	|| $_POST['accion'] == "eliminar_aaa" || $_POST['accion'] == "BuscarPM" || $_POST['accion'] == "GuardarPM" || $_POST['accion'] == "eliminar_pda" 
	|| $_POST['accion'] == "ActualizarCS" || $_POST['accion'] == "eliminar_grado_seccion" )
{
// Armamos array para convertir a JSON
	$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
			echo json_encode($salidaJson);
}
// data[]
if($_POST['accion'] == "EditarHorarios" || $_POST['accion'] == "editar_annlectivo" || $_POST['accion'] == "editar_seccion" || $_POST['accion'] == "BuscarCodigoSeccion" 
	|| $_POST['accion'] == "BuscarCodigoAnnLectivo" || $_POST['accion'] == "editar_grado" || $_POST['accion'] == "BuscarCodigoGrado" || $_POST['accion'] == "BuscarCodigoModalidad")
{
// Armamos array para convertir a JSON
	echo json_encode($datos);
}

?>