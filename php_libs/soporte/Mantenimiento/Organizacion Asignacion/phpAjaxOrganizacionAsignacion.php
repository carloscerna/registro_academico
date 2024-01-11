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
			case 'BuscarModalidad':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_annlectivo = $_POST['codigo_annlectivo'];
				$codigo_modalidad = $_POST['codigo_modalidad'];
				// Armamos el query.
					$query = "SELECT orgac.id_organizar_ann_lectivo_ciclos, orgac.codigo_ann_lectivo, orgac.codigo_bachillerato, orgac.codigo_servicio_educativo,
							cat_se.descripcion as descripcion_se,
							ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad
								FROM organizar_ann_lectivo_ciclos orgac
								INNER JOIN ann_lectivo ann ON ann.codigo = orgac.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgac.codigo_bachillerato
								INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = orgac.codigo_servicio_educativo
								WHERE orgac.codigo_ann_lectivo = '$codigo_annlectivo' ORDER BY orgac.id_organizar_ann_lectivo_ciclos";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
				//
				if($consulta -> rowCount() != 0){
					$mensaje = "Si Registro";
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// variablesç
						$id_ = trim($listado['id_organizar_ann_lectivo_ciclos']);
						$codigo_modalidad = trim($listado['codigo_bachillerato']);
						$nombre_modalidad = trim($listado['nombre_modalidad']);
						$nombre_se = trim($listado['descripcion_se']);
						$num++;
					// variables Json
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$codigo_modalidad
							<td>$nombre_modalidad
							<td>$nombre_se
							<td><a data-accion=EliminarModalidad class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href=$id_><i class='fas fa-trash'></i></a>"
						;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'GuardarModalidad':
				// consultar el registro antes de agregarlo.
					// Armamos el query y iniciamos variables.
					$codigo_annlectivo = $_POST['codigo_annlectivo'];
					$codigo_modalidad = $_POST['codigo_modalidad'];
					$codigo_se = $_REQUEST['lstModalidadServicioEducativo'];
						$query = "SELECT * FROM organizar_ann_lectivo_ciclos 
							WHERE codigo_ann_lectivo = '$codigo_annlectivo' and codigo_bachillerato = '$codigo_modalidad' and codigo_servicio_educativo = '$codigo_se'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
				//
				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "Este registro ya Existe";
					$mensajeError = "Nivel ya fue Guardado para el Año Lectivo.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO organizar_ann_lectivo_ciclos (codigo_ann_lectivo, codigo_bachillerato, codigo_servicio_educativo) 
						VALUES ('$codigo_annlectivo','$codigo_modalidad','$codigo_se')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Agregado";
					$mensajeError = "Registro Guardado.";
				}
			break;
			case 'EliminarModalidad':
				// Armamos el query
				$id_ = $_REQUEST["id_"];
					$query = "DELETE FROM organizar_ann_lectivo_ciclos WHERE id_organizar_ann_lectivo_ciclos = '$id_'";
				// Ejecutamos el query
					$count = $dblink -> exec($query);
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Se ha eliminado el registro correctamente';

					$contenidoOK = 'Se ha Eliminado '.$count.' Registro(s).';

				}else{
					$mensajeError = 'No se ha eliminado el registro';
				}
			break;
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO ORGANIZACION GRADOS,SECCIÓN Y TURNO.
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarSeGST':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_ann_lectivo = trim($_POST["codigo_annlectivo"]);
				$codigo_modalidad = trim($_POST["codigo_modalidad"]);
				// Armamos el query.
					$query = "SELECT org.id_grados_secciones, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_ann_lectivo, org.codigo_turno, cat_se.descripcion as descripcion_se,
							ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
							FROM organizacion_grados_secciones org 
								INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
								INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
								INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
								INNER JOIN turno tur ON tur.codigo = org.codigo_turno
								INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = org.codigo_servicio_educativo
									WHERE org.codigo_ann_lectivo = '$codigo_ann_lectivo' and org.codigo_bachillerato = '$codigo_modalidad'
										ORDER BY org.codigo_ann_lectivo, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_turno";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
				//
				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// variables
						$id_ = trim($listado['id_grados_secciones']);
						$nombre_modalidad = trim($listado['nombre_modalidad']);
						$nombre_grado = trim($listado['nombre_grado']);
						$nombre_seccion = trim($listado['nombre_seccion']);
						$nombre_turno = trim($listado['nombre_turno']);
						$nombre_se = trim($listado['descripcion_se']);
							$num++;
						//
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$nombre_modalidad
							<td>$nombre_se
							<td>$nombre_grado
							<td>$nombre_seccion
							<td>$nombre_turno
							<td><a data-accion=EditarSeGST class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href=$id_><i class='fas fa-edit'></i></a>
							<a data-accion=EliminarSeGST class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href=$id_><i class='fas fa-trash'></i></a>"
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'EditarGST':
				$id_ = trim($_REQUEST['id_']);
				// Armamos el query y iniciamos variables.
					$query = "SELECT id_grados_secciones, codigo_servicio_educativo, codigo_turno
								FROM organizacion_grados_secciones
									WHERE id_grados_secciones = '$id_'";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_ = trim($listado['id_grados_secciones']);
					$codigo_se = trim($listado['codigo_servicio_educativo']);
					$codigo_turno = trim($listado['codigo_turno']);
					
					$datos[$fila_array]["id_"] = $id_;
					$datos[$fila_array]["codigo_se"] = $codigo_se;
					$datos[$fila_array]["codigo_turno"] = $codigo_turno;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarSeGST':
				$id_ = $_POST['id_'];
				$codigo_se = trim($_POST['lstSeGST']);
				$codigo_turno = $_POST["lstTurnoSeGST"];
				// Armamos el query y iniciamos variables.
					$query = "UPDATE organizacion_grados_secciones 
								SET codigo_servicio_educativo = '$codigo_se', codigo_turno = '$codigo_turno'
								WHERE id_grados_secciones = $id_";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'GuardarSeGST':
				// consultar el registro antes de agregarlo.
				// Armamos el query y iniciamos variables.
				 $codigo_ann_lectivo = ($_POST['lstAnnLectivoSeGST']);
				 $codigo_modalidad = ($_POST['lstModalidadSeGST']);
				 $codigo_grado = $_POST["lstGradoSeGST"];
				 $codigo_seccion = $_POST["lstSeccionSeGST"];
				 $codigo_turno = $_POST["lstTurnoSeGST"];
				 $codigo_servicio_educativo = $_POST["lstSeGST"];
				 $query = "SELECT * FROM organizacion_grados_secciones 
				 			WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_bachillerato = '$codigo_modalidad' 
								and codigo_grado = '$codigo_grado' and codigo_seccion = '$codigo_seccion' and codigo_turno = '$codigo_turno' and codigo_servicio_educativo = '$codigo_servicio_educativo'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "";
					$mensajeError = "Si Existe";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO organizacion_grados_secciones 
								(codigo_ann_lectivo, codigo_bachillerato, codigo_grado, codigo_seccion, codigo_turno, codigo_servicio_educativo) 
									VALUES ('$codigo_ann_lectivo','$codigo_modalidad','$codigo_grado','$codigo_seccion','$codigo_turno','$codigo_servicio_educativo')";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "";
					$mensajeError = "Si Registro";
				}
			break;
			case 'EliminarSeGST':
				// Armamos el query
				$id_ = $_POST['id_'];
					$query = "DELETE FROM organizacion_grados_secciones WHERE id_grados_secciones = '$id_'";
				// Ejecutamos el query
					$count = $dblink -> exec($query);
				// Validamos que se haya actualizado el registro
				if($count != 0){
					$respuestaOK = true;
					$mensajeError = 'Se ha eliminado el registro correctamente';

					$contenidoOK = 'Se ha Eliminado '.$count.' Registro(s).';

				}else{
					$mensajeError = 'No se ha eliminado el registro';
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
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (ORGANIZAR PERSONAL DOCENTE O ADMINISTRATIVO)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarDN':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_annlectivo = $_POST["codigo_annlectivo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				// Armamos el query.
					$query = "SELECT orgpda.id_organizar_planta_docente_ciclos, orgpda.codigo_bachillerato, orgpda.codigo_ann_lectivo, orgpda.codigo_turno, orgpda.codigo_docente,
								ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_personal, p.id_personal,
								tur.nombre as nombre_turno
									FROM organizar_planta_docente_ciclos orgpda
										INNER JOIN ann_lectivo ann ON ann.codigo = orgpda.codigo_ann_lectivo
										INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgpda.codigo_bachillerato
										INNER JOIN personal p ON p.id_personal = orgpda.codigo_docente
										INNER JOIN turno tur ON tur.codigo = orgpda.codigo_turno
											WHERE orgpda.codigo_bachillerato = '$codigo_modalidad' and orgpda.codigo_ann_lectivo = '$codigo_annlectivo' and p.codigo_estatus = '01'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
				// RowCount()
				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// variables
							$nombre_ann_lectivo = trim($listado['nombre_ann_lectivo']);
							$nombre_modalidad = trim($listado['nombre_modalidad']);
							$nombre_personal = trim($listado["nombre_personal"]);
							$nombre_turno = trim($listado["nombre_turno"]);
							$id_ = trim($listado['id_organizar_planta_docente_ciclos']);
							$num++;
						// contenido
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$nombre_modalidad
							<td>$nombre_personal
							<td>$nombre_turno
							<td><a data-accion=EditarDN class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href=$id_><i class='fas fa-edit'></i></a>
							<a data-accion=EliminarDN class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href=$id_><i class='fas fa-trash'></i></a>"
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'GuardarDN':
				// Armamos el query y iniciamos variables.
					$codigo_ann_lectivo = ($_POST['lstAnnLectivoDN']);
					$codigo_modalidad = ($_POST['lstModalidadDN']);
					$codigo_personal = $_POST["lstDocenteNivel"];
					$codigo_turno = $_POST["lstTurnoDN"];
				// armar query.
					 $query = "SELECT * FROM organizar_planta_docente_ciclos 
						WHERE codigo_docente = '$codigo_personal' and codigo_ann_lectivo = '$codigo_ann_lectivo' 
							and codigo_bachillerato = '$codigo_modalidad' and codigo_turno = '$codigo_turno'";
				// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
				// RowCount()
				if($consulta -> rowCount() != 0){
					$respuestaOK = false;
					$contenidoOK = "";
					$mensajeError = "El Docente ya están este Nivel y Turno.";
				}else{
				// proceso para grabar el registro
					$query = "INSERT INTO organizar_planta_docente_ciclos 
							(codigo_ann_lectivo, codigo_bachillerato, codigo_turno, codigo_docente) 
								VALUES ('$codigo_ann_lectivo','$codigo_modalidad','$codigo_turno','$codigo_personal')";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "";
					$mensajeError = "Registro Guardado";
				}
			break;
			case 'EditarDN':
				$id_ = trim($_REQUEST['id_']);
					$query = "SELECT * FROM organizar_planta_docente_ciclos 
								WHERE id_organizar_planta_docente_ciclos = '$id_'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$fila_array = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
					// variables
					$id_ = trim($listado['id_organizar_planta_docente_ciclos']);
					$codigo_docente = trim($listado['codigo_docente']);
					$codigo_turno = trim($listado['codigo_turno']);
					
					$datos[$fila_array]["id_"] = $id_;
					$datos[$fila_array]["codigo_docente"] = $codigo_docente;
					$datos[$fila_array]["codigo_turno"] = $codigo_turno;
					$fila_array++;
					}
					$mensajeError = "Se ha consultado el registro correctamente ";
				}
			break;
			case 'ActualizarDN':
				$id_ = trim($_REQUEST['id_']);
				$codigo_docente = trim($_POST['lstDocenteNivel']);
				$codigo_turno = trim($_POST['lstTurnoDN']);
				// Armamos el query y iniciamos variables.
					$query = "UPDATE organizar_planta_docente_ciclos 
								SET codigo_docente = '$codigo_docente', codigo_turno = '$codigo_turno'
									WHERE id_organizar_planta_docente_ciclos = '$id_'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
					$respuestaOK = true;
					$contenidoOK = "Registro Actualizado.";
					$mensajeError = "Se ha consultado el registro correctamente ";
			break;
			case 'EliminarDN':
				// Variable
				$id_ = $_POST['id_'];
				// Armamos el query
					$query = "DELETE FROM organizar_planta_docente_ciclos 
								WHERE id_organizar_planta_docente_ciclos = '$id_'";
				// Ejecutamos el query
					$consulta = $dblink -> query($query);
				// Validamos que se haya actualizado el registro
					if($consulta -> rowCount() != 0){
						$respuestaOK = true;
						$mensajeError = 'Se ha eliminado el registro correctamente';
						$contenidoOK = 'Se ha Eliminado Registro(s).';
					}else{
						$mensajeError = 'No se ha eliminado el registro';
					}
			break;
			///////////////////////////////////////////////////////////////////////////////////////////////////
			////////////// BLOQUE DE REGISTRO GESTION (ASIGNACION DE ASIGNATURAS A GRADOS)
			///////////////////////////////////////////////////////////////////////////////////////////////////
			case 'BuscarAAG':
				// Armar Colores
				$statusTipo = array ("01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger");
				$codigo_annlectivo = $_POST["codigo_annlectivo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				$codigo_grado_se_post = explode("-",$_POST["codigo_grado_se"]);
				$codigo_grado = $codigo_grado_se_post[0];
				$codigo_se = $codigo_grado_se_post[1];
				// Armamos el query.
					$query = "SELECT DISTINCT aaa.codigo_asignacion, aaa.id_asignacion, aaa.orden, 
						asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura,
						cat_area_di.descripcion as descripcion_area_dimension, cat_area_subdi.descripcion as descripcion_area_subdimension,
						cat_area.descripcion as nombre_area
						FROM a_a_a_bach_o_ciclo aaa 
						INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo 
						INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo 
						INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura 
						INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area 
						INNER JOIN catalogo_area_dimension cat_area_di ON cat_area_di.codigo = asig.codigo_area_dimension
						INNER JOIN catalogo_area_subdimension cat_area_subdi ON cat_area_subdi.codigo =  asig.codigo_area_subdimension
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
						$id_ = trim($listado['id_asignacion']);
						$nombre_area = trim($listado["nombre_area"]);
						$nombre_area_dimension = trim($listado['descripcion_area_dimension']);
						$nombre_area_subdimension = trim($listado['descripcion_area_subdimension']);
						$nombre_asignatura = trim($listado["nombre_asignatura"]);
						$orden = trim($listado["orden"]);
					// validar el nombre de la asignatura con su area, dimension y subdimension.
						if($nombre_area_dimension == "Ninguno"){
							$nombre_area_dimension_subdimension_asignatura = $nombre_area . " - " . $nombre_asignatura;
						}else{
							$nombre_area_dimension_subdimension_asignatura = $nombre_area . "-" . $nombre_area_dimension . "-" . $nombre_area_subdimension . "-" . $nombre_asignatura;
						}
					//
					//
						$num++;
						$contenidoOK .= "<tr>
							<td><input type=checkbox class=case name=chk$id_ id=chk$id_>
							<td>$num
							<td>$id_
							<td>$nombre_area_dimension_subdimension_asignatura
							<td>$nombre_asignatura
							<td>$orden
							<td><a data-accion=EditarAAG class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href=$id_><i class='fas fa-edit'></i></a>
							<a data-accion=EliminarAAG class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href=$id_><i class='fas fa-trash'></i></a>"
							;
					}
					$mensajeError = "Si Registro";
				}
			break;
			case 'GuardarAAG':
				// consultar el registro antes de agregarlo.
					$TodasLasAsignaturas = "no";
				// Armamos el query y iniciamos variables.
					$codigo_annlectivo = ($_POST['lstAnnLectivoAAG']);
					$codigo_modalidad = ($_POST['lstModalidadAAG']);
					$codigo_grado_se = explode("-", $_POST["lstGradoAAG"]);
					$codigo_grado = $codigo_grado_se[0];
					$codigo_servicio_educativo = $codigo_grado_se[1];
					$codigo_asignatura = $_POST["lstAAG"];
					$TodasLasAsignaturas = $_POST["TodasLasAsignaturas"];
                 ///////////////////////////////////////////////////////////////////////////////////////////////
                 //// SI TODAS LAS ASIGNATURAS ES IGUAL A YES
                 ///////////////////////////////////////////////////////////////////////////////////////////////
				if($TodasLasAsignaturas == "yes"){
					$numero = 1;
					$query_todas = "SELECT codigo as codigo_asignatura, nombre as nombre_asignatura, ordenar
								FROM asignatura
								WHERE imprimir = 'true' and estatus = 'true' and codigo_servicio_educativo = '$codigo_servicio_educativo'
									ORDER BY codigo_servicio_educativo, codigo_area, id_asignatura";
					$consulta_todas = $dblink -> query($query_todas);
					// recorrer las asignaturas
							while($listado = $consulta_todas -> fetch(PDO::FETCH_BOTH))
								{
								// Nombres de los campos de la tabla.
								$codigo_asignatura = trim($listado['codigo_asignatura']);
								$ordenar = trim($listado['ordenar']);
														// VERFICAR SI NO EXISTE ASIGNATURA.
									$query_buscar = "SELECT * FROM a_a_a_bach_o_ciclo 
												WHERE codigo_ann_lectivo = '$codigo_annlectivo' and codigo_bach_o_ciclo = '$codigo_modalidad' and codigo_grado = '$codigo_grado' 
												and codigo_asignatura = '$codigo_asignatura'";
								// Ejecutamos el Query.
								$consulta_buscar = $dblink -> query($query_buscar);
				
								if($consulta_buscar -> rowCount() != 0){
									$respuestaOK = false;
									$contenidoOK = "";
									$mensajeError = "Si Existe";
								}else{
								// proceso para grabar el registro
									$query = "INSERT INTO a_a_a_bach_o_ciclo (codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_asignacion, codigo_grado, codigo_asignatura, orden) 
									VALUES ('$codigo_annlectivo','$codigo_modalidad','$codigo_modalidad','$codigo_grado','$codigo_asignatura','$ordenar')";
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
						$query = "SELECT * FROM a_a_a_bach_o_ciclo 
									WHERE codigo_ann_lectivo = '$codigo_annlectivo' and codigo_bach_o_ciclo = '$codigo_modalidad' and codigo_grado = '$codigo_grado' and codigo_asignatura = '$codigo_asignatura'";
						// Ejecutamos el Query.
						$consulta = $dblink -> query($query);
		
						if($consulta -> rowCount() != 0){
							$respuestaOK = false;
							$contenidoOK = "";
							$mensajeError = "El Registro del Componente ya Existe.";
						}else{
						// proceso para grabar el registro
							$query = "INSERT INTO a_a_a_bach_o_ciclo (codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_asignacion, codigo_grado, codigo_asignatura) 
									VALUES ('$codigo_annlectivo','$codigo_modalidad','$codigo_modalidad','$codigo_grado','$codigo_asignatura')";
						// Ejecutamos el Query.
						$consulta = $dblink -> query($query);
							$respuestaOK = true;
							$contenidoOK = "";
							$mensajeError = "El Registro fue Guardado Correctamente.";
						}                    
					}
			break;
			case 'ActualizarAAG':		
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
			case 'EliminarAAG':
				// Variable
				$id_ = $_POST['id_'];
				// Armamos el query
					$query = "DELETE FROM a_a_a_bach_o_ciclo WHERE id_asignacion = '$id_'";
				// Ejecutamos el query
					$consulta = $dblink -> query($query);
				// Validamos que se haya actualizado el registro
					if($consulta -> rowCount() != 0){
						$respuestaOK = true;
						$mensajeError = 'Se ha eliminado el registro correctamente';
						$contenidoOK = 'Se ha Eliminado Registro(s).';
					}else{
						$mensajeError = 'No se ha eliminado el registro';
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
	|| $_POST['accion'] == "BuscarModalidad" || $_POST['accion'] == "GuardarModalidad" || $_POST['accion'] == "EliminarModalidad" || $_POST['accion'] == "BuscarSeGST"
	|| $_POST['accion'] == "ActualizarSeGST" || $_POST['accion'] == "GuardarSeGST" || $_POST['accion'] == "EliminarSeGST"  
	|| $_POST['accion'] == "BuscarDN" || $_POST['accion'] == "GuardarDN" || $_POST['accion'] == "ActualizarDN"
	|| $_POST["accion"] == "BuscarAAG" || $_POST['accion'] == "GuardarAAG" || $_POST['accion'] == "EliminarAAG")
{
// Armamos array para convertir a JSON
	$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
			echo json_encode($salidaJson);
}
// data[]
if($_POST['accion'] == "EditarHorarios" || $_POST['accion'] == "EditarGST" || $_POST['accion'] == "EditarDN")
{
// Armamos array para convertir a JSON
	echo json_encode($datos);
}

?>