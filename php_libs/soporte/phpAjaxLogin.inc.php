<?php
// limpiar cache.
clearstatcache();
// cambiar a utf-8.
header("Content-Type: text/html;charset=iso-8859-1");
// Variable para la conexión.
$errorDbConexion = false;
// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
	include($path_root."/registro_academico/includes/mainFunctions_login.php");

// Validar conexión con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'BuscarUser':
				// validar si hay datos en los parametros.
					$nombre = trim($_POST['txtnombre']);
					$password_usuario = trim($_POST['txtpassword']);
					//$codigo_infraestructura = trim($_POST['txtcodigoinstitucion']);
				// armar la consulta.
					 $query = "SELECT u.nombre, u.id_usuario, u.base_de_datos, u.codigo_escuela, u.codigo_perfil, u.codigo_personal, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_personal,
							cat_perfil.descripcion as nombre_perfil
							FROM usuarios u
							INNER JOIN personal p ON p.id_personal = u.codigo_personal
							INNER JOIN catalogo_perfil cat_perfil ON cat_perfil.codigo = u.codigo_perfil
							WHERE u.nombre= '$nombre' and u.password= '$password_usuario' LIMIT 1";
        
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$contenidoOK ="Si";
					$mensajeError = "Se ha consultado el registro correctamente ";
					
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
							$_SESSION['userLogin'] = true;
							$_SESSION['userNombre'] = trim($listado['nombre']);
							$_SESSION['userID'] = $listado['id_usuario'];
							$_SESSION['dbname'] = trim($listado['base_de_datos']);
							$_SESSION['codigo_escuela'] = trim($listado['codigo_escuela']);
							$_SESSION['codigo_perfil'] = trim($listado['codigo_perfil']);
							$_SESSION['codigo_personal'] = trim($listado['codigo_personal']);
							$_SESSION['nombre_personal'] = (trim($listado['nombre_personal']));
							$_SESSION['nombre_perfil'] = (trim($listado['nombre_perfil']));
					}
					
					 // Conectarse a la nueva base de datos.
						// ruta de los archivos con su carpeta
							$path_root=trim($_SERVER['DOCUMENT_ROOT']);
						// Incluimos el archivo de funciones y conexión a la base de datos
							include($path_root."/registro_academico/includes/mainFunctions_.php");
							include($path_root."/registro_academico/includes/funciones.php");
							// Validar conexión.
							if($errorDbConexion == false){
                            ////////////////////////////////////////////////////////
                            // Actualizar datos del Docente y otros.
                            ////////////////////////////////////////////////////////
                            // armar la consulta.
                           $query = "SELECT u.nombre, u.id_usuario, u.base_de_datos, u.codigo_escuela, u.codigo_perfil, u.codigo_personal, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_personal,
    							cat_perfil.descripcion as nombre_perfil
    							FROM usuarios u
    							INNER JOIN personal p ON p.id_personal = u.codigo_personal
    							INNER JOIN catalogo_perfil cat_perfil ON cat_perfil.codigo = u.codigo_perfil
    							WHERE u.nombre= '$nombre' and u.password= '$password_usuario' LIMIT 1";
                            // Ejecutamos el Query.
                                $consulta = $dblink -> query($query);
                            ///////////////////////////////////////////////////////////////////////////////////////////
                            // RECARDATOS DEL INICIO DE SESION
                            ///////////////////////////////////////////////////////////////////////////////////////////
                                while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                                {
                                        $_SESSION['userLogin'] = true;
                                        $_SESSION['userNombre'] = trim($listado['nombre']);
                                        $_SESSION['userID'] = $listado['id_usuario'];
                                        $_SESSION['dbname'] = trim($listado['base_de_datos']);
                                        $_SESSION['codigo_escuela'] = trim($listado['codigo_escuela']);
                                        $_SESSION['codigo_perfil'] = trim($listado['codigo_perfil']);
                                        $_SESSION['codigo_personal'] = trim($listado['codigo_personal']);
                                        $_SESSION['nombre_personal'] = (trim($listado['nombre_personal']));
                                        $_SESSION['nombre_perfil'] = (trim($listado['nombre_perfil']));
                                }                                
                            ///////////////////////////////////////////////////////////////////////////////////////////
							// Obtener datos de la institución.
                            ///////////////////////////////////////////////////////////////////////////////////////////
								$consulta = "SELECT inf.id_institucion, inf.codigo_departamento, inf.codigo_municipio, inf.codigo_institucion, inf.nombre_institucion, inf.direccion_institucion, inf.telefono_uno, depa.codigo, depa.nombre as nombre_departamento, mu.codigo, mu.codigo_departamento, mu.nombre as nombre_municipio, pd.nombre_completo, inf.numero_acuerdo,
											inf.se_extiende_la_presente, inf.dia_entrega, inf.logo_uno, inf.logo_dos, inf.imagen_firma, inf.imagen_sello
											from informacion_institucion inf
												INNER JOIN planta_docente pd ON pd.codigo = inf.nombre_director
												INNER JOIN departamento depa ON depa.codigo = inf.codigo_departamento
												INNER JOIN municipio mu ON mu.codigo = inf.codigo_municipio and mu.codigo = inf.codigo_municipio and mu.codigo_departamento = inf.codigo_departamento
													WHERE inf.codigo_departamento = depa.codigo and codigo_institucion = '$_SESSION[codigo_escuela]' LIMIT 1";
							// Ejecutamos la consulta.
								$respuesta = $dblink -> query($consulta);
									if($respuesta -> rowCount() !=0){
										$userData = $respuesta -> fetch(PDO::FETCH_ASSOC);
										// Crear variable global para utilizar con informes y Formularios.
											//$_SESSION['userNombre'] = trim($userData['nombre_institucion']);
											$_SESSION['institucion'] = trim($userData['nombre_institucion']);
											$_SESSION['direccion'] = (trim($userData['direccion_institucion']));
											$_SESSION['codigo'] = trim($userData['codigo_institucion']);
											$_SESSION['telefono'] = trim($userData['telefono_uno']);
											$_SESSION['codigo_municipio'] = trim($userData['codigo_municipio']);
											$_SESSION['nombre_municipio'] = utf8_encode(trim($userData['nombre_municipio']));
											$_SESSION['codigo_departamento'] = trim($userData['codigo_departamento']);
											$_SESSION['nombre_departamento'] = utf8_encode(trim($userData['nombre_departamento']));
											$_SESSION['nombre_director'] = (trim($userData['nombre_completo']));
											$_SESSION['numero_acuerdo'] = trim($userData['numero_acuerdo']);
											$_SESSION['se_extiende'] = trim($userData['se_extiende_la_presente']);
											$_SESSION['dia_entrega'] = utf8_decode(trim($userData['dia_entrega']));
											$_SESSION['logo_uno'] = trim($userData['logo_uno']);
											$_SESSION['logo_dos'] = trim($userData['logo_dos']);
											$_SESSION['imagen_firma'] = trim($userData['imagen_firma']);
											$_SESSION['imagen_sello'] = trim($userData['imagen_sello']);
											$_SESSION['codigo_institucion'] = trim($userData['codigo_institucion']);
										//Guardamos dos variables de sesión que nos auxiliará para saber si se está o no "logueado" un usuario 
											$_SESSION["autentica"] = "SI";
                                        // Variables para la Matricula en el caso que sea NUEVA.
                                            $_SESSION["s_codigo_ann_lectivo"] = "01";
                                            $_SESSION["s_codigo_modalidad"] = "01";
                                            $_SESSION["s_codigo_grado_seccion_turno"] = "01";
									} // Validar si hay archivos.
									else{
										$respuestaOK = false;
										$contenidoOK = "Error Institucion";
										$mensajeError = "No Existen datos de la institución.";}
							} // Validar conexión.
							else{
								$respuestaOK = false;
								$contenidoOK = "Error dbname";
								$mensajeError = "No Existe la base de datos";}
				}
				else{
					$respuestaOK = false;
					$contenidoOK = 'Error Usuario';
					$mensajeError = 'Este usuario no existe.';
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
	$respuestaOK = false;
	$contenidoOK = "Error dbname";
	$mensajeError = "No Existe la base de datos";}

// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>