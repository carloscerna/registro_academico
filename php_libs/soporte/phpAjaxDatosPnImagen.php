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
									a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad, a.partida_nacimiento, a.ruta_pn,
									a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.codigo_nie, a.codigo_genero,
									am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.ann_anterior,
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
                        $imagen_frente = (trim($listado['ruta_pn']));

                        // Imagen de la partida de Nacimiento.
                        if($imagen_frente === null){$imagen_frente = 'img/NoDisponible.jpg';}else{$imagen_frente = 'img/Pn/10391/'.$imagen_frente;}
						// Imagen de la partida de Nacimiento.
/*						if($imagen_frente === null)
						{
							$imagen_frente = 'img/NoDisponible.jpg';
						}
						else{
							$imagen_frente = $path_root . "registro_academico/img/Pn/10391/".$imagen_frente;
							if(!file_exists($imagen_frente))
							{
								$imagen_frente = 'img/NoDisponible.jpg';
							}
						}                        
  */                      
						$estudio_parvularia = $listado['partida_nacimiento'];
						if($estudio_parvularia == "1"){$estudio = "01";}else{$estudio="02";}
						

						// Crear Check para Estuido Parvularia
						if ($estudio == "01"){
							$estudio = "<input type=radio name=e$id_alumno value=1 checked> Si
								    <input type=radio name=e$id_alumno value=0> No
							";}
							
						if ($estudio == "02"){
							$estudio = "<input type=radio name=e$id_alumno value=1> Si
								    <input type=radio name=e$id_alumno value=0 checked> No
							";}
							
						// Armar Cadena con resultado de las respectivas variables.	
						$contenidoOK .= '<tr class='.$color_tabla.'><td class=centerTXT>'.$num
							.'<td class=centerTXT>'.$id_alumno
							.'<td>'.$apellido_alumno
                            .'<td class=centerTXT>'.'<img src='.$imagen_frente.' width= 450 heigth=450 />'
							.'<td>'.$estudio
							.'<td class = centerTXT><input type = hidden name=codigo_matricula value = '.$codigo_matricula.'>'
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
				$codigo_grado = substr($_REQUEST["codigo_grado"],0,2);
				$codigo_alumno[] = $_POST["codigo_alumno"];
				$codigo_matricula[] = $_POST["codigo_matricula"];
				$fila = $_POST["fila"];
				
				$original[] = $_POST["original"];


				
				$fila = $fila - 1;

				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila;$i++){
					$codigo_a = $codigo_alumno[0][$i];
					$codigo_m = $codigo_matricula[0][$i];

					// Validar Variables. Estudio_Parvularia.
					$original_p = $original[0][$i];
					
					// armar sql. para acutlizar tabla alumno.
					$query = "UPDATE alumno SET
						partida_nacimiento = '$original_p'
						WHERE id_alumno = '$codigo_a'";
					
					// Ejecutamos el Query.
					$consulta = $dblink -> query($query);
				}

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