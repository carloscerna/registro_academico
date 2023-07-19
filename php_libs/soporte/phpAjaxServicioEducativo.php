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
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos

include($path_root."/registro_web/includes/mainFunctions_conexion.php");

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
			////////////// BLOQUE DE REGISTRO GESTION (ASIGNATURA)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoAsignatura':
				// Armamos el query.
				$query = "SELECT (codigo)::int FROM asignatura ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo_asignatura"] = $codigo + 1;	
					}
				}
			break;			
			case 'BuscarAsignatura':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_se_post = $_POST["codigo_se"];
				// Armamos el query.
					$query = "SELECT asig.id_asignatura, asig.nombre, asig.codigo as codigo_asignatura, asig.codigo_servicio_educativo, asig.codigo_cc, asig.codigo_servicio_educativo, asig.codigo_area, asig.estatus, asig.ordenar,
							cat_se.descripcion as nombre_servicio_educativo, 
							cat_cc.descripcion as nombre_cc, cat_cc.codigo,
							cat_area.descripcion as nombre_area, cat_area.codigo
							FROM asignatura asig
							INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = asig.codigo_servicio_educativo
							INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
							INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
							WHERE asig.codigo_servicio_educativo = '$codigo_se_post'
								ORDER BY asig.estatus DESC, asig.codigo_area";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$codigo = trim($listado['codigo_asignatura']);
					$nombre = trim($listado['nombre']);
					$id_ = trim($listado['id_asignatura']);
					$codigo_se = trim($listado['codigo_servicio_educativo']);
					$nombre_se = trim($listado['nombre_servicio_educativo']);
					$codigo_cc = trim($listado['codigo_cc']);
					$nombre_cc = trim($listado['nombre_cc']);
					$codigo_area = trim($listado['codigo_area']);
					$nombre_area = trim($listado['nombre_area']);
					$estatus = trim($listado['estatus']);
					$ordenar = trim($listado['ordenar']);
					$num++;
					// VARIABLES ESTATUS.
						if($estatus == 1){
							$estatus = "<td><span class='badge badge-pill badge-info'>Activo</span></td>";
						}else{
							$estatus = "<td><span class='badge badge-pill badge-danger'>Inactivo</span></td>";
						}
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo
							<td>$nombre_area
							<td>$nombre
							<td>$ordenar
							$estatus
							<td><a data-accion=editar_asignatura class='btn btn-xs btn-info' href=$id_ tabindex='-1' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fad fa-edit'></i></a>
							<a data-accion=eliminar_asignatura class='btn btn-xs btn-warning' href=$id_ tabindex='-1' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fad fa-trash'></i></a>
							";
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}else{
					$contenidoOK = "No Existen Registros.";
					$mensajeError = "No existen registros.";
				}
			break;
			case 'GuardarAsignatura':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $codigo_se = ($_POST['lstcodigose_m']);
				 $codigo_cc = ($_POST['lstcodigocc']);
				 $codigo_area = ($_POST['lstcodigoarea']);
				 $nombre_asignatura = ($_POST['txtasignatura']);
				 $codigo_asignatura = ($_POST['txtcodigoasignatura']);
				 $partes_dividida = ($_POST['sppartes']);
				 $estatus_asignatura = ($_POST['lstEstatusA']);
				 
				 $query = "SELECT * FROM asignatura WHERE codigo = '".$codigo_asignatura. "' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "";
					$mensajeError = "Si Existe";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO asignatura (nombre, codigo, codigo_servicio_educativo, codigo_cc, codigo_area, partes_dividida, estatus) VALUES ('$nombre_asignatura','$codigo_asignatura','$codigo_se','$codigo_cc','$codigo_area',$partes_dividida, '$estatus_asignatura')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "";
					$mensajeError = "Si Registro";
				}
			break;
			case 'editar_asignatura':
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_asignatura, nombre, codigo, codigo_servicio_educativo, codigo_area, codigo_cc, partes_dividida, estatus, ordenar FROM asignatura WHERE id_asignatura = ".$_POST['id_x']. " ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_asignatura = trim($listado['id_asignatura']);
					$nombre = trim($listado['nombre']);
					$codigo = trim($listado['codigo']);
					$codigo_se = trim($listado['codigo_servicio_educativo']);
					$codigo_cc = trim($listado['codigo_cc']);
					$codigo_area = trim($listado['codigo_area']);
					$partes_dividida = trim($listado['partes_dividida']);
					$estatus_asignatura = trim($listado['estatus']);
					$ordenar = trim($listado['ordenar']);
					
					$datos[$fila_array]["id_asignatura"] = $id_asignatura;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$datos[$fila_array]["codigo_cc"] = $codigo_cc;
					$datos[$fila_array]["codigo_area"] = $codigo_area;
					$datos[$fila_array]["codigo_se"] = $codigo_se;
					$datos[$fila_array]["partes_dividida"] = $partes_dividida;
					$datos[$fila_array]["estatus_asignatura"] = $estatus_asignatura;
					$datos[$fila_array]["ordenar"] = $ordenar;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'modificar_asignatura':
				$id_asignatura = $_POST['id_asignatura'];
				$nombre = trim($_POST['txtasignatura']);
				$codigo_se = ($_POST['lstcodigose_m']);
				$codigo_cc = ($_POST['lstcodigocc']);
				$codigo_area = ($_POST['lstcodigoarea']);
				$partes_dividida = ($_POST['sppartes']);
				$estatus_asignatura = ($_POST['lstEstatusA']);
				$ordenar = ($_POST['txtordenar']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE asignatura SET
								nombre = '$nombre',
								codigo_servicio_educativo = '$codigo_se',
								codigo_cc = '$codigo_cc',
								codigo_area = '$codigo_area',
								partes_dividida = '$partes_dividida',
								estatus = '$estatus_asignatura',
								ordenar = '$ordenar'
									WHERE id_asignatura = ". $id_asignatura;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Registro Actualizado";
			break;
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (MODALIDAD)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoModalidad':
				// Armamos el query.
				$query = "SELECT id_bachillerato_ciclo, nombre, codigo FROM bachillerato_ciclo ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo_modalidad"] = $codigo;	
					}
				}
			break;
			case 'BuscarModalidad':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT id_bachillerato_ciclo, nombre, codigo FROM bachillerato_ciclo ORDER BY codigo";
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
					$id_modalidad = trim($listado['id_bachillerato_ciclo']);
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_modalidad
							.'<td class=centerTXT>'.$codigo
							.'<td class=centerTXT>'.$nombre
							.'<td class = centerTXT><a data-accion=editar_modalidad class="btn btn-xs btn-primary" href='.$listado['id_bachillerato_ciclo'].'>Editar</a>'
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
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
			case 'addModalidad':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = strtoupper($_POST['nombre_modalidad']);
				 $codigo_modalidad = ($_POST['codigo_modalidad']);
				 $query = "SELECT id_bachillerato_ciclo, nombre, codigo FROM bachillerato_ciclo WHERE codigo = '".$codigo_modalidad. "' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO bachillerato_ciclo (nombre, codigo) VALUES ('$nombre','$codigo_modalidad')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;	
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (GRADO)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoGrado':
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
			case 'BuscarGrado':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT id_grado_ano, nombre, codigo FROM grado_ano ORDER BY codigo";
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
					$id_grado = trim($listado['id_grado_ano']);
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_grado
							.'<td class=centerTXT>'.$codigo
							.'<td class=centerTXT>'.$nombre
							.'<td class = centerTXT><a data-accion=editar_grado class="btn btn-xs btn-primary" href='.$listado['id_grado_ano'].'>Editar</a>'
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
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
				$id_annlectivo = $_POST['IdAnnLectivo'];
				$descripcion = strtoupper($_POST['descripcion']);
				$estatus = trim($_POST['estatus']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE ann_lectivo SET
						descripcion = '$descripcion',
						estatus = '$estatus'
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
			case 'BuscarSeccion':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT id_seccion, nombre, codigo FROM seccion ORDER BY codigo";
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
					$id_seccion = trim($listado['id_seccion']);
					$num++;
						    
						$contenidoOK .= '<tr>
							<td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_seccion
							.'<td class=centerTXT>'.$codigo
							.'<td class=centerTXT>'.$nombre
							.'<td class = centerTXT><a data-accion=editar_seccion class="btn btn-xs btn-primary" href='.$listado['id_seccion'].'>Editar</a>'
							.'<td class = centerTXT><a data-accion=eliminar_seccion class="btn btn-xs btn-primary" href='.$listado['id_seccion'].'>Eliminar</a>'
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
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
			case 'addSeccion':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = strtoupper($_POST['nombre_seccion']);
				 $codigo_seccion = ($_POST['codigo_seccion']);
				 $query = "SELECT id_seccion, nombre, codigo FROM seccion WHERE codigo = '".$codigo_seccion. "' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO seccion (nombre, codigo) VALUES ('$nombre','$codigo_seccion')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;	
			default:
				$mensajeError = 'Esta acción no se encuentra disponible';
			break;
		}
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicación';}
}
else{
	$mensajeError = 'No se puede establecer conexión con la base de datos';}

if($_POST['accion'] == "eliminar_annlectivo" || $_POST['accion'] == "BuscarSeccion" || $_POST['accion'] == "modificar_seccion" || $_POST['accion'] == "addSeccion" || $_POST['accion'] == "BuscarAnnLectivo" || $_POST['accion'] == "addAnnLectivo" || $_POST['accion'] == "BuscarGrado" || $_POST['accion'] == "addGrado" || $_POST['accion'] == "modificar_annlectivo" || $_POST['accion'] == "BuscarModalidad" || $_POST['accion'] == "modificar_modalidad" || $_POST['accion'] == "addModalidad" || $_POST['accion'] == "modificar_grado" || $_POST['accion'] == "BuscarAsignatura" || $_POST['accion'] == "GuardarAsignatura" || $_POST['accion'] == "modificar_asignatura") {
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
echo json_encode($salidaJson);
}

if($_POST['accion'] == "editar_modalidad" || $_POST['accion'] == "editar_annlectivo" || $_POST['accion'] == "editar_seccion" || $_POST['accion'] == "BuscarCodigoSeccion" || $_POST['accion'] == "BuscarCodigoAnnLectivo" || $_POST['accion'] == "editar_grado" || $_POST['accion'] == "BuscarCodigoGrado" || $_POST['accion'] == "BuscarCodigoModalidad" || $_POST['accion'] == "BuscarCodigoAsignatura" || $_POST['accion'] == "editar_asignatura") {
// Armamos array para convertir a JSON
echo json_encode($datos);
}

?>