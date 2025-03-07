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
					$query = "SELECT asig.id_asignatura, asig.nombre, asig.codigo as codigo_asignatura, asig.codigo_servicio_educativo, asig.codigo_cc, 
							asig.codigo_servicio_educativo, asig.codigo_area, asig.estatus, asig.ordenar, asig.codigo_estatus,
							cat_se.descripcion as nombre_servicio_educativo, 
							cat_cc.descripcion as nombre_cc, cat_cc.codigo,
							cat_area.descripcion as nombre_area, cat_area.codigo
							FROM asignatura asig
							INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = asig.codigo_servicio_educativo
							INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
							INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
							WHERE asig.codigo_servicio_educativo = '$codigo_se_post'
								ORDER BY asig.estatus DESC, asig.ordenar ASC, asig.codigo_area";
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
					$estatus = trim($listado['codigo_estatus']);
					$ordenar = trim($listado['ordenar']);
					$num++;
					// VARIABLES ESTATUS.
						if($estatus == '01'){
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
							<td><input type=number id=orden name=orden value ='$ordenar' class=form-control>
							$estatus
							<td><a data-accion=editar_asignatura class='btn btn-xs btn-info' href=$id_-$codigo tabindex='-1' data-toggle='tooltip' data-placement='top' title='Editar'><i class='fad fa-edit'></i></a>
							<a data-accion=eliminar_asignatura class='btn btn-xs btn-warning' href=$id_-$codigo tabindex='-1' data-toggle='tooltip' data-placement='top' title='Eliminar'><i class='fad fa-trash'></i></a>
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
					$codigo_se = ($_POST['CodigoSE']);
					$codigo_cc = ($_POST['lstIndicadorCalificacion']);
					$codigo_area = ($_POST['lstArea']);
					$nombre_asignatura = ($_POST['DescripcionAsignatura']);
					$codigo_asignatura = ($_POST['CodigoAsignatura']);
					$codigo_dimension = ($_POST['lstDimension']);
					$codigo_subdimension = ($_POST['lstSubDimension']);
					$estatus_asignatura = ($_POST['lstAsignaturaEstatus']);
					$orden = ($_POST['OrdenAsignatura']);
				// VALIDAR ESTATUS CON FALSE O TRUE
					if($estatus_asignatura == '01'){
						$estatus = true;
					}else{
						$estatus = false;
					}
				 // verificar si existe la asignatura con respoecto al codigo.
				 	$query = "SELECT * FROM asignatura WHERE codigo = '$codigo_asignatura' ORDER BY codigo ";
				 // Ejecutamos el Query.
					$consulta = $dblink -> query($query);

					if($consulta -> rowCount() != 0){
						$respuestaOK = false;
						$contenidoOK = "";
						$mensajeError = "Si Existe";
					}else{
				// proceso para grabar el registro
					$query = "INSERT INTO asignatura (nombre, codigo, codigo_servicio_educativo, codigo_cc, codigo_area, codigo_estatus, codigo_area_dimension, codigo_area_subdimension, ordenar, estatus) 
					VALUES ('$nombre_asignatura','$codigo_asignatura','$codigo_se','$codigo_cc','$codigo_area','$estatus_asignatura','$codigo_dimension','$codigo_subdimension','$orden','$estatus')";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
						$respuestaOK = true;
						$contenidoOK = $query;
						$mensajeError = "Si Registro";
				}
			break;
			case 'EditarAsignatura':
				$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_asignatura, nombre, codigo, codigo_servicio_educativo, codigo_area, codigo_cc, estatus, ordenar,
								codigo_estatus, codigo_area_dimension, codigo_area_subdimension			
								FROM asignatura 
								WHERE id_asignatura = '$id_' ORDER BY codigo";
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
					$codigo_area_dimension = trim($listado['codigo_area_dimension']);
					$codigo_area_subdimension = trim($listado['codigo_area_subdimension']);
					//$partes_dividida = trim($listado['partes_dividida']);
					$estatus_asignatura = trim($listado['estatus']);
					$codigo_estatus = trim($listado['codigo_estatus']);
					$ordenar = trim($listado['ordenar']);
					
					$datos[$fila_array]["id_asignatura"] = $id_asignatura;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$datos[$fila_array]["codigo_cc"] = $codigo_cc;
					$datos[$fila_array]["codigo_area"] = $codigo_area;
					$datos[$fila_array]["codigo_area_dimension"] = $codigo_area_dimension;
					$datos[$fila_array]["codigo_area_subdimension"] = $codigo_area_subdimension;
					$datos[$fila_array]["codigo_se"] = $codigo_se;
					//$datos[$fila_array]["partes_dividida"] = $partes_dividida;
					$datos[$fila_array]["estatus_asignatura"] = $estatus_asignatura;
					$datos[$fila_array]["codigo_estatus"] = $codigo_estatus;
					$datos[$fila_array]["ordenar"] = $ordenar;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarAsignatura':
				$id_ = $_REQUEST['IdAsignatura'];
				$codigo_se = ($_POST['CodigoSE']);
				$codigo_cc = ($_POST['lstIndicadorCalificacion']);
				$codigo_area = ($_POST['lstArea']);
				$nombre_asignatura = ($_POST['DescripcionAsignatura']);
				$codigo_asignatura = ($_POST['CodigoAsignatura']);
				$codigo_dimension = ($_POST['lstDimension']);
				$codigo_subdimension = ($_POST['lstSubDimension']);
				$estatus_asignatura = ($_POST['lstAsignaturaEstatus']);
				$orden = ($_POST['OrdenAsignatura']);
			   // VALIDAR ESTATUS CON FALSE O TRUE
					if($estatus_asignatura == '01'){
						$estatus = '1';
					}else{
						$estatus = '0';
					}
				// Armamos el query y iniciamos variables.
					$query = "UPDATE asignatura SET
								nombre = '$nombre_asignatura',
								codigo_servicio_educativo = '$codigo_se',
								codigo_cc = '$codigo_cc',
								codigo_area = '$codigo_area',
								codigo_area_dimension = '$codigo_dimension',
								codigo_area_subdimension = '$codigo_subdimension',
								codigo_estatus = '$estatus_asignatura',
								estatus = '$estatus',
								ordenar = '$orden'
									WHERE id_asignatura = ". $id_;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado." . $query;
					$mensajeError = "Registro Actualizado";
			break;
			case 'eliminar_asignatura':
				$codigo_id_ = trim($_REQUEST['codigo_id_']);
				$codigo_id_ = explode("-",$codigo_id_);
				$id_ = $codigo_id_[0];
				$codigo_asignatura = $codigo_id_[1];
				$count = 0;
				// BUSCAR EN LA TABLA NOTA, PARA revisar si no existe el registro del codigo de la asignatura.
					$query_buscar = "SELECT id_notas, codigo_asignatura FROM nota WHERE codigo_asignatura = '$codigo_asignatura' LIMIT 1";
				// Ejecutamos el query
					$consulta_buscar = $dblink -> query($query_buscar);					
				// Validamos que se haya actualizado el registro
					if($consulta_buscar -> rowCount() != 0){
						$mensajeError = 'No se ha eliminado el registro'.$query_buscar;
							break;
					}else{
						// Armamos el query
						$query = "DELETE FROM asignatura WHERE id_asignatura = '$id_'";

						// Ejecutamos el query
							$count = $dblink -> exec($query);
						
						// Validamos que se haya actualizado el registro
						if($count != 0){
							$respuestaOK = true;
							$mensajeError = 'Se ha eliminado el registro correctamente'.$query;

							$contenidoOK = 'Se ha Eliminado '.$count.' Registro(s).';

						}else{
							$mensajeError = 'No se ha eliminado el registro '. $query . " - " . $query_buscar;
						}
					}
			break;
			case 'ActualizarOrden':		
				// armar variables y consulta Query.
				$id_asignatura[] = $_POST["id_asignatura"];
				$codigo_asignatura[] = $_POST["codigo_asignatura"];
				$estatus[] = $_POST["estatus"];
				$orden[] = $_POST["orden"];
				// Variales.
				$fila = $_POST["fila"];
					$fila = $fila - 1;
				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila;$i++){
					$id_asignatura_ = trim($id_asignatura[0][$i]);
					$codigo_asignatura_ = trim($codigo_asignatura[0][$i]);
					$estatus_ = trim($estatus[0][$i]);
					$orden_ = $orden[0][$i];
					// cambiar estatus
						if($estatus_ == "Activo"){
							$codigo_estatus = "01";
						}else{
							$codigo_estatus = "02";
						}
					// armar sql para actualizar tabla a_a_a_bacho_o_ciclo
						$query_aaa = "UPDATE a_a_a_bach_o_ciclo SET orden = '$orden_' WHERE codigo_asignatura = '$codigo_asignatura_'";
							$consulta_aaa = $dblink -> query($query_aaa); // Ejecutamos el Query.
					// armar sql para actualizar tabla nota.
						$query_aa_nota = "UPDATE nota SET orden = '$orden_' WHERE codigo_asignatura = '$codigo_asignatura_'";
							$consulta_aa_nota = $dblink -> query($query_aa_nota); 				// Ejecutamos el Query.
					// armar sql para actualizar tabla asignatura.
						$query_asignatura = "UPDATE asignatura SET ordenar = '$orden_' WHERE id_asignatura = '$id_asignatura_' and codigo_estatus = '$codigo_estatus'";
							$consulta_asignatura = $dblink -> query($query_asignatura); 				// Ejecutamos el Query.
				}

				$respuestaOK = true;
				$contenidoOK = '';
				$mensajeError =  'Registro Actualizado';
			break;
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (MODALIDAD)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoModalidad':
				// Armamos el query.
				$query = "SELECT (codigo)::int FROM bachillerato_ciclo ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo"] = $codigo + 1;	
					}
				}
			break;
			case 'BuscarModalidad':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT * FROM bachillerato_ciclo ORDER BY codigo";
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
					$id_ = trim($listado['id_bachillerato_ciclo']);
					$estatus = trim($listado['codigo_estatus']);
					// VARIABLES ESTATUS.
					if($estatus == '01'){
						$estatus = "<td><span class='badge badge-pill badge-info'>Activo</span></td>";
					}else{
						$estatus = "<td><span class='badge badge-pill badge-danger'>Inactivo</span></td>";
					}
					$num++;
						    
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo
							<td>$nombre
							$estatus
							<td><a data-accion=EditarModalidad class='btn btn-xs btn-info' href=$id_>Editar</a>"
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'EditarModalidad':
					$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT * FROM bachillerato_ciclo WHERE id_bachillerato_ciclo = '$id_' ORDER BY codigo ";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
					if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_ = trim($listado['id_bachillerato_ciclo']);
					$nombre = trim($listado['nombre']);
					$codigo = trim($listado['codigo']);
					$estatus = trim($listado['codigo_estatus']);
					
					$datos[$fila_array]["id_Modalidad"] = $id_;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$datos[$fila_array]["codigo_estatus"] = $estatus;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarModalidad':
				$id_ = $_POST['IdModalidad'];
				$nombre = htmlspecialchars(trim($_POST['DescripcionModalidad']));
				$codigo_estatus = $_POST["lstModalidadEstatus"];
				// Armamos el query y iniciamos variables.
					$query = "UPDATE bachillerato_ciclo SET nombre = '$nombre', codigo_estatus = '$codigo_estatus'
								WHERE id_bachillerato_ciclo = '$id_'";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
						$respuestaOK = true;
						$contenidoOK = "Registro Actualizado.";
						$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarModalidad':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = ($_POST['DescripcionModalidad']);
				 $codigo_modalidad = ($_POST['CodigoModalidad']);
				 $codigo_estatus = $_POST["lstModalidadEstatus"];
				 $query = "SELECT id_bachillerato_ciclo, nombre, codigo FROM bachillerato_ciclo WHERE codigo = '".$codigo_modalidad. "' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO bachillerato_ciclo (nombre, codigo, codigo_estatus) VALUES ('$nombre','$codigo_modalidad','$codigo_estatus')";
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
				$query = "SELECT * FROM grado_ano ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo"] = $codigo;
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
					$id_ = trim($listado['id_grado_ano']);
					$num++;
					//
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo
							<td>$nombre
							<td><a data-accion=EditarGrado class='btn btn-xs btn-info' href=$id_>Editar</a>"
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'EditarGrado':
				$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT * FROM grado_ano WHERE id_grado_ano = '$id_' ORDER BY codigo";
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
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarGrado':
				$id_ = $_POST['IdGrado'];
				$nombre = htmlspecialchars($_POST['DescripcionGrado']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE grado_ano SET 
						nombre = '$nombre' 
							WHERE id_grado_ano = $id_";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Registro Actualizado.";
			break;
			case 'GuardarGrado':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = htmlspecialchars(trim($_POST['DescripcionGrado']));
				 $codigo = ($_POST['CodigoGrado']);
				 $query = "SELECT * FROM grado_ano WHERE codigo = '$codigo' ORDER BY codigo ";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO grado_ano (nombre, codigo) 
								VALUES ('$nombre','$codigo')";
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
				$query = "SELECT (codigo)::int FROM ann_lectivo ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo"] = $codigo + 1;	
					}
				}
			break;
			case 'BuscarAnnLectivo':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT * FROM ann_lectivo ORDER BY codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_ = trim($listado['id_annlectivo']);
					$codigo = trim($listado['codigo']);
					$nombre_año = trim($listado['nombre']);
					$descripcion = trim($listado['descripcion']);
					$estatus = trim($listado['codigo_estatus']);
					$num++;

					// VARIABLES ESTATUS.
					if($estatus == '01'){
						$estatus = "<td><span class='badge badge-pill badge-info'>Activo</span></td>";
					}else{
						$estatus = "<td><span class='badge badge-pill badge-danger'>Inactivo</span></td>";
					}
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo
							<td>$nombre_año
							<td>$descripcion
							$estatus
							<td><a data-accion=EditarAnnLectivo class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href=$id_><i class='fas fa-edit'></i></a>"
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'EditarAnnLectivo':
				$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT * FROM ann_lectivo WHERE id_annlectivo = '$id_' ORDER BY codigo";
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
					$codigo_estatus = trim($listado['codigo_estatus']);
					
					$datos[$fila_array]["id_"] = $id_annlectivo;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre_año"] = $nombre;
					$datos[$fila_array]["descripcion"] = $descripcion;
					$datos[$fila_array]["fecha_inicio"] = $fecha_inicio;
					$datos[$fila_array]["fecha_fin"] = $fecha_fin;
					$datos[$fila_array]["codigo_estatus"] = $codigo_estatus;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarAnnLectivo':
				$id_ = $_POST['IdAnnLectivo'];
				$nombre_año = htmlspecialchars(trim($_POST['AnnLectivo']));
				$descripcion = htmlspecialchars(trim($_POST['DescripcionAnnLectivo']));
				$codigo_estatus = trim($_POST['lstAnnLectivo']);
				$fecha_inicio = ($_POST['FechaInicio']);
				$fecha_fin = ($_POST['FechaFin']);
				// VALIDAR ESTATUS CON FALSE O TRUE
					if($codigo_estatus == '01'){
						$estatus = '1';
					}else{
						$estatus = '0';
					}
				// Armamos el query y iniciamos variables.
					$query = "UPDATE ann_lectivo SET
							nombre = '$nombre_año',
							descripcion = '$descripcion',
							codigo_estatus = '$codigo_estatus',
							estatus = '$estatus',
							fecha_inicio = '$fecha_inicio',
							fecha_fin = '$fecha_fin'
								WHERE id_annlectivo = ". $id_;
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarAnnLectivo':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre_año = htmlspecialchars(trim($_POST['AnnLectivo']));
				 $codigo = ($_POST['CodigoAnnLectivo']);
				 $descripcion = ($_POST['DescripcionAnnLectivo']);
				 $fecha_inicio = ($_POST['FechaInicio']);
				 $fecha_fin = ($_POST['FechaFin']);
				 $codigo_estatus = ($_POST['lstAnnLectivo']);

				// VALIDAR ESTATUS CON FALSE O TRUE
					if($codigo_estatus == '01'){
						$estatus = true;
					}else{
						$estatus = false;
					}
				 // Ar,ar qieru àra evañiar-
				 $query = "SELECT * FROM ann_lectivo WHERE codigo = '$codigo' ORDER BY codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "No Registro";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO ann_lectivo (nombre, codigo, descripcion, fecha_inicio, fecha_fin, estatus, codigo_estatus)
							VALUES ('$nombre_año','$codigo','$descripcion','$fecha_inicio','$fecha_fin','$estatus','$codigo_estatus')";
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
				$query = "SELECT (codigo)::int FROM seccion ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo"] = $codigo + 1;	
					}
				}
			break;
			case 'BuscarSeccion':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT * FROM seccion ORDER BY codigo";
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
					$id_ = trim($listado['id_seccion']);
					$num++;
						    
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo
							<td>$nombre
							<td><a data-accion=EditarSeccion class='btn btn-xs btn-info' href=$id_>Editar</a>"
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'EditarSeccion':
				$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT * FROM seccion WHERE id_seccion = '$id_' ORDER BY codigo";
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
			case 'ActualizarSeccion':
				$id_ = $_POST['IdSeccion'];
				$nombre = strtoupper(htmlspecialchars($_POST['DescripcionSeccion']));
				// Armamos el query y iniciamos variables.
					$query = "UPDATE seccion SET nombre = '$nombre' WHERE id_seccion=$id_";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarSeccion':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = strtoupper(htmlspecialchars($_POST['DescripcionSeccion']));
				 $codigo = ($_POST['CodigoSeccion']);
				 $query = "SELECT * FROM seccion WHERE codigo = '$codigo' or nombre = '$nombre' ORDER BY codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO seccion (nombre, codigo) VALUES ('$nombre','$codigo')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;	
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (SERVICIOS EDUCATIVOS)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarCodigoSe':
				// Armamos el query.
				$query = "SELECT (codigo)::int FROM catalogo_servicio_educativo ORDER BY codigo DESC LIMIT 1";
				// Ejecutamos el Query.
				$fila_array = 0;
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$codigo = trim($listado['codigo']);
						$datos[$fila_array]["codigo"] = $codigo + 1;	
					}
				}
			break;
			case 'BuscarSe':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				// Armamos el query.
					$query = "SELECT * FROM catalogo_servicio_educativo ORDER BY codigo";
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
					$nombre = trim($listado['descripcion']);
					$id_ = trim($listado['id_servicio_educativo']);
					$estatus = trim($listado['codigo_estatus']);
					// VARIABLES ESTATUS.
					if($estatus == '01'){
						$estatus = "<td><span class='badge badge-pill badge-info'>Activo</span></td>";
					}else{
						$estatus = "<td><span class='badge badge-pill badge-danger'>Inactivo</span></td>";
					}
					$num++;
						    
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo
							<td>$nombre
							$estatus
							<td><a data-accion=EditarSe class='btn btn-xs btn-info' href=$id_>Editar</a>"
							;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'EditarSe':
				$id_ = $_REQUEST['id_'];
				// Armamos el query y iniciamos variables.
					$query = "SELECT * FROM catalogo_servicio_educativo WHERE id_servicio_educativo = '$id_' ORDER BY codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_ = trim($listado['id_servicio_educativo']);
					$nombre = trim($listado['descripcion']);
					$codigo = trim($listado['codigo']);
					$estatus = trim($listado['codigo_estatus']);

					$datos[$fila_array]["id_servicio_educativo"] = $id_;
					$datos[$fila_array]["codigo"] = $codigo;
					$datos[$fila_array]["nombre"] = $nombre;
					$datos[$fila_array]["codigo_estatus"] = $estatus;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarSe':
				$id_ = $_POST['IdServiciosEducativos'];
				$nombre = trim(htmlspecialchars($_POST['DescripcionServiciosEducativos']));
				$codigo_estatus = $_POST['lstSEEstatus'];
				// Armamos el query y iniciamos variables.
					$query = "UPDATE catalogo_servicio_educativo SET descripcion = '$nombre', codigo_estatus = '$codigo_estatus' 
								WHERE id_servicio_educativo = '$id_'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarSe':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $nombre = trim(htmlspecialchars($_POST['DescripcionServiciosEducativos']));
				 $codigo = ($_POST['CodigoServiciosEducativos']);
				 $codigo_estatus = $_POST['lstSEEstatus'];
				 $query = "SELECT * FROM catalogo_servicio_educativo WHERE codigo = '$codigo' or descripcion = '$nombre' ORDER BY codigo";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Este registro ya Existe.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO catalogo_servicio_educativo (descripcion, codigo, codigo_estatus) VALUES ('$nombre','$codigo','$codigo_estatus')";
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

if($_POST['accion'] == "eliminar_annlectivo" || $_POST['accion'] == "BuscarSeccion" 
	|| $_POST['accion'] == "ActualizarSeccion" || $_POST['accion'] == "GuardarSeccion" 
	|| $_POST['accion'] == "BuscarAnnLectivo" || $_POST['accion'] == "GuardarAnnLectivo"
	|| $_POST['accion'] == "BuscarGrado" || $_POST['accion'] == "GuardarGrado"
	|| $_POST['accion'] == "ActualizarAnnLectivo" || $_POST['accion'] == "BuscarModalidad"
	|| $_POST['accion'] == "ActualizarModalidad" || $_POST['accion'] == "GuardarModalidad" 
	|| $_POST['accion'] == "ActualizarGrado"
	|| $_POST['accion'] == "BuscarAsignatura"
	|| $_POST['accion'] == "GuardarAsignatura"
	|| $_POST['accion'] == "ActualizarAsignatura"
	|| $_POST['accion'] == "ActualizarOrden"
	|| $_POST['accion'] == "eliminar_asignatura"
	|| $_POST['accion'] == "BuscarSe" || $_POST['accion'] == "GuardarSe"
	|| $_POST['accion'] == "ActualizarSe"
	) {
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
	echo json_encode($salidaJson);
}

if($_POST['accion'] == "EditarModalidad" || $_POST['accion'] == "EditarAnnLectivo"
|| $_POST['accion'] == "EditarSeccion" || $_POST['accion'] == "BuscarCodigoSeccion" 
|| $_POST['accion'] == "BuscarCodigoAnnLectivo" || $_POST['accion'] == "EditarGrado" 
|| $_POST['accion'] == "BuscarCodigoGrado" || $_POST['accion'] == "BuscarCodigoModalidad" 
|| $_POST['accion'] == "BuscarCodigoAsignatura" || $_POST['accion'] == "EditarAsignatura"
|| $_POST['accion'] == "BuscarCodigoSe"  || $_POST['accion'] == "EditarSe") {
	// Armamos array para convertir a JSON
	//echo "Datos procesados...";
	echo json_encode($datos);
}

?>