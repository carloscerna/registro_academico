<?php
//session_name('demoUI');
//session_start();
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: text/html;charset=ISO-8859-1");
// Insertar y actualizar tabla de usuarios
sleep(0);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$contenidoOK2 = "";
$contenidoOK3 = "";
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
			case 'BuscarLista':
				// Declarar Variables y Crear consulta Query.
						$codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,6) . $_REQUEST["lstannlectivo"];
				  
						 // Información Académica.
						 $codigo_modalidad = substr($codigo_all,0,2);
						 $codigo_grado = substr($codigo_all,2,2);
						 $codigo_seccion_turno = substr($codigo_all,4,4);
						 $codigo_annlectivo = trim($_REQUEST["lstannlectivo"]);
						// Variable para el color de las filas.
						$color_tabla = "success";
			
			// armar query.	
			$query_listado = "SELECT a.estudio_parvularia, a.apellido_materno, a.apellido_paterno, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
				 btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
				 ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
				 a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero,
				 a.codigo_discapacidad, a.codigo_zona_residencia, a.codigo_departamento, a.codigo_municipio, a.codigo_actividad_economica, a.codigo_estado_civil,
				 a.codigo_estado_familiar, a.tiene_hijos, a.cantidad_hijos,
				 a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
				 am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
				 am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
				 am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion,
				 am.codigo_turno, am.ann_anterior
				 FROM alumno a
				 INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
				 INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
				 INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
				 INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
				 INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
				 INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
				 INNER JOIN turno tur ON tur.codigo = am.codigo_turno
				 WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_turno || am.codigo_ann_lectivo) = '".$codigo_all.
				 "' ORDER BY apellido_alumno ASC";

			// armando el Query para optener el grado y sección.
			$query_grado_seccion = "SELECT orgs.codigo_bachillerato, orgs.codigo_ann_lectivo, orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_turno,
					sec.nombre as nombre_seccion, grd.nombre as nombre_grado, tur.nombre as nombre_turno
					from organizacion_grados_secciones orgs
					INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgs.codigo_bachillerato
					INNER JOIN ann_lectivo ann ON ann.codigo = orgs.codigo_ann_lectivo
					INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
					INNER JOIN grado_ano grd ON grd.codigo = orgs.codigo_grado
					INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
					where orgs.codigo_bachillerato = '".$codigo_modalidad."' and orgs.codigo_ann_lectivo = '".$codigo_annlectivo.
					"' ORDER BY orgs.codigo_grado, orgs.codigo_seccion";
					
				$query_estado_familiar = "SELECT codigo, nombre FROM catalogo_estado_familiar ORDER BY CODIGO";
				$query_estado_civil = "SELECT codigo, nombre FROM catalogo_estado_civil ORDER BY CODIGO";
				$query_actividad_economica = "SELECT codigo, nombre FROM catalogo_actividad_economica ORDER BY CODIGO";
				$query_tipo_discapacidad = "SELECT codigo, nombre FROM catalogo_tipo_de_discapacidad ORDER BY CODIGO";
				$query_zona_residencia = "SELECT codigo, nombre FROM catalogo_zona_residencia ORDER BY codigo";
				$query_departamento = "SELECT codigo, nombre from departamento ORDER BY codigo";

				// Ejecutamos el Query.
					$consulta = $dblink -> query($query_listado);
					
				if($consulta -> rowCount() != 0){
					$respuestaOK = true; $color_fila = '<tr style=background:#FFFFFF><td>';
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// Variable para el color de las filas de la tabla.
						//if($color_tabla == "success"){$color_tabla="active";}else{$color_tabla="success";}
						
						$num++;
						$id_alumno = $listado['id_alumno'];
                        $codigo_nie = $listado['codigo_nie'];
						$codigo_matricula = (trim($listado['cod_matricula']));
						$apellido_alumno = (trim($listado['apellido_alumno']));
						$codigo_grado_seccion_turno_tbl = (trim($listado['codigo_grado'])) .(trim($listado['codigo_seccion'])) . (trim($listado['codigo_turno']));
						$codigo_grado_tbl = (trim($listado['codigo_grado']));
					//
					// Crear rutina para captar los valores de seccion y turno.
					//
						$lst_seccion_turno = '<select name="seccion_turno" class="custom-select">';
					// Ejecutamos el Query Grado - Sección.
						$consulta_seccion_turno = $dblink -> query($query_grado_seccion);
					// Recorriendo la Tabla con PDO::
					      while($listado_g = $consulta_seccion_turno -> fetch(PDO::FETCH_BOTH))
						{
						 // Nombres de los campos de la tabla.
						 $codigo_grado = trim($listado_g['codigo_grado']); $descripcion_grado = trim($listado_g['nombre_grado']);
						 $codigo_seccion = trim($listado_g['codigo_seccion']); $descripcion_seccion = trim($listado_g['nombre_seccion']);
						 $codigo_turno = trim($listado_g['codigo_turno']); $descripcion_turno = trim($listado_g['nombre_turno']);
						 $codigo_grado_seccion_turno_ = $codigo_grado . $codigo_seccion . $codigo_turno;
						 
						   // Rellenar el select. comprobando el grado primero, y después seccion y turno.
						 if($codigo_grado == $codigo_grado_tbl){
						   if($codigo_grado_seccion_turno_tbl == $codigo_grado_seccion_turno_){
							$lst_seccion_turno .="<option value=$codigo_seccion$codigo_turno selected>".$descripcion_seccion . " - ". $descripcion_turno;	
						   }else{
							$lst_seccion_turno .="<option value=$codigo_seccion$codigo_turno>".$descripcion_seccion . " - ". $descripcion_turno;
						   }
						 }
						}
						// cerrar el select de grado sección.
						$lst_seccion_turno .="</select>";
					///////////////////////////////////////////////////////////////////////////////////
					// TODOS LOS INDICADORES
					///////////////////////////////////////////////////////////////////////////////////
						// armar variables para la sobreedad.
						if($listado['sobreedad'] == 't'){
                            $sobreedad = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
                            $sobreedad = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }
						// armar variables para la repitencia.
						if($listado['repitente'] == 't'){
                            $repitente = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
                            $repitente = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }
						// armar variables para retirado
						if($listado['retirado'] == 't'){
							$color_fila = '<tr style=background:#2980B9><td>';
                            $retirado = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
							$color_fila = '<tr style=background:##17202A><td>';
                            $retirado = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }
						// armar variables para nuevo ingreso.
						if($listado['nuevo_ingreso'] == 't'){
                            $nuevo_ingreso = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
                            $nuevo_ingreso = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }                          
						// armar variables para Partida de Nacimiento..
						if($listado['pn'] == 't'){
                            $pn = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
                            $pn = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }
						// armar variables para certificado
						if($listado['certificado'] == 't'){
                            $certificado = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
                            $certificado = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }
						// armar variables para nuevo ingreso.
						if($listado['imprimir_foto'] == 't'){
                            $imprimir_foto = "checked data-toggle=tooltip data-placement=top title='Check Igual Si'";
                          }else{
                            $imprimir_foto = "data-toggle=tooltip data-placement=top title='Check Igual Si'";;
                          }                          
						// INFORMACIÓN PARA LA PRIMERA PÁGINA.
						$contenidoOK .= $color_fila.$num
							.'<td><input type = hidden name=codigo_alumno value = '.$id_alumno.'>'
							.'<td><input type = hidden name=codigo_matricula value = '.$codigo_matricula.'>'
                            .'<td>'.$codigo_nie
							.'<td>'.$apellido_alumno
							.'<td>'.$lst_seccion_turno
							.'<td><input type=checkbox name=chksobreedad'.$codigo_matricula.' id=chksobreedad'.$codigo_matricula.' value=""' .$sobreedad.'>'
							.'<td><input type=checkbox name=chkrepitente'.$codigo_matricula.' id=chkrepitente'.$codigo_matricula.' value=""' .$repitente.'>'
							.'<td><input type=checkbox name=chkretirado'.$codigo_matricula.' id=chkretirado'.$codigo_matricula.' value=""' .$retirado.'>'
							.'<td><input type=checkbox name=chknuevoingreso'.$codigo_matricula.' id=chknuevoingreso'.$codigo_matricula.' value=""' .$nuevo_ingreso.'>'
							.'<td><input type=checkbox name=chkpn'.$codigo_matricula.' id=chkpn'.$codigo_matricula.' value=""' .$pn.'>'
							.'<td><input type=checkbox name=chkcertificado'.$codigo_matricula.' id=chkcertificado'.$codigo_matricula.' value=""' .$certificado.'>'
							.'<td><input type=checkbox name=chkimprimirfoto'.$codigo_matricula.' id=chkimprimirfoto'.$codigo_matricula.' value=""' .$imprimir_foto.'>'
							;
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = true;
					$contenidoOK = '';
					$mensajeError =  'No Registro';
				}
			break;

			case 'ActualizarDatosMatricula':		
				// armar variables y consulta Query.
				$codigo_alumno[] = $_POST["codigo_alumno"];
				$codigo_matricula[] = $_POST["codigo_matricula"];
				$codigo_seccion_turno[] = $_POST["codigo_seccion_turno"];

				$sobreedad[] = $_POST["sobreedad"];
				$repitente[] = $_POST["repitente"];
				$retirado[] = $_POST["retirado"];
				$nuevo_ingreso[] = $_POST["nuevo_ingreso"];
				$pn[] = $_POST["pn"];
				$certificado[] = $_POST["certificado"];
				$imprimir_foto[] = $_POST["imprimir_foto"];
				
				// Variales.
				$fila = $_POST["fila"];
			
				$fila = $fila - 1;

				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila;$i++){
					$codigo_a = $codigo_alumno[0][$i];
					$codigo_m = $codigo_matricula[0][$i];
					
					$codigo_st = $codigo_seccion_turno[0][$i];
					$codigo_s = substr($codigo_st,0,2);
					$codigo_t = substr($codigo_st,2,2);
					
					$s = $sobreedad[0][$i];
					$r = $repitente[0][$i];
					$re = $retirado[0][$i];
					$n = $nuevo_ingreso[0][$i];
					$p = $pn[0][$i];
					$c = $certificado[0][$i];
					$im = $imprimir_foto[0][$i];
					// armar sql para actualizar tabla alumno_matricula.
					$query_matricula = "UPDATE alumno_matricula SET
										codigo_seccion = '$codigo_s',
										codigo_turno = '$codigo_t',
										sobreedad = '$s',
										repitente = '$r',
										retirado = '$re',
										nuevo_ingreso = '$n',
										pn = '$p',
										certificado = '$c',
										imprimir_foto = '$im'
											WHERE codigo_alumno = '$codigo_a' and id_alumno_matricula = '$codigo_m'";
					// Ejecutamos el Query.
					$consulta_matricula = $dblink -> query($query_matricula);
				}

				$respuestaOK = true;
				$contenidoOK = 'Registros Actualizados.';
				$mensajeError =  'Si Registro';
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

// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK,
		"contenido2" => $contenidoOK2,
		"contenido3" => $contenidoOK3);

echo json_encode($salidaJson);
?>