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
			case 'BuscarLista':
				// Declarar Variables y Crear consulta Query.
						$codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,4) . $_REQUEST["lstannlectivo"];
						$codigo_modalidad = $_REQUEST["lstmodalidad"];
						// Armar variable para el valor de la Nota, que influye sobre la recuperación y el promedio final.
						if($codigo_modalidad >= '06'){$nota_evaluar = 6;}else{$nota_evaluar = 5;}
				  		// Variable para el min y max del number.
						$valor_min_max = array("min=0 max= 10 maxlength=5","min=0 max= 10 maxlength=5");
						$valor_m_m = "";
						 // Información Académica.
						 $codigo_bachillerato = substr($codigo_all,0,2);
						 $codigo_grado = substr($codigo_all,2,2);
						 $codigo_seccion = substr($codigo_all,4,2);
						 $codigo_annlectivo = substr($codigo_all,6,2);
				// Armar la consulta para las diferencias opciones de actualiación de notas.
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno, 
							  am.codigo_bach_o_ciclo, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion
							  FROM alumno a 
							  INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
							  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
							  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
							  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
							  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
							  WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."'
								  ORDER BY apellido_alumno ASC";
				
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true; $fondo = array("info","success"); $num_fondo = 0;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// Para cambiar el color de las filas.
						if($num_fondo == 0){$num_fondo = 1;}else{$num_fondo = 0;}
						$num++;
						$id_alumno = $listado['id_alumno'];
						$nie = $listado['codigo_nie'];
						$apellido_alumno = (trim($listado['apellido_alumno']));
						// Rellenar variable dependiendo de la Consulta.
						// Sí se ha seleccionado Recuperación evaluar y presentar sólo los registros necesarios.
							$contenidoOK .= '<tr><td>'.$num
								//.'<td><input type=hidden name=codigo_alumno size=1 value = '.$id_alumno.'>'
                                .'<td>'.$id_alumno
								.'<td>'.$nie
								.'<td>'.$apellido_alumno
								."<td><div class='d-flex justify-content-end'><input type=checkbox name=matricula class='form-control'></div>"
								;
					}	// Recorrido de la Tabla.
					$mensajeError = "Si Registro";
				} // If que cuenta.
				else{
					$respuestaOK = true;
					$contenidoOK = '
						<tr id="sinDatos">
							<td colspan="4">No Hay Registros de este alumno...</td>
						</tr>
					'.$query;
					$mensajeError =  'No Registro';
				}
			break;

			case 'CrearMatricula':			
				// armar variables y consulta Query.
				$codigo_alumno[] = $_POST["codigo_alumno_"];
                $chk_matricula[] = $_POST["chk_matricula_"];
				$fila = $_POST["fila"];
                $codigo_annlectivo = trim($_POST["lstannlectivo"]);
				$codigo_modalidad = trim($_POST["lstmodalidad"]);
                $codigo_gradoseccion = trim($_POST["lstgradoseccion"]);
                $codigo_grado = substr($codigo_gradoseccion,0,2);
                $codigo_seccion = substr($codigo_gradoseccion,2,2);
                $codigo_turno = substr($codigo_gradoseccion,4,2);
                $pn = true;
                $certificado = true;
                $codigo_todos = $codigo_modalidad . $codigo_grado . $codigo_annlectivo;
                $x_matriculas = 0;
                $no_matriculas = 0;
                $no_seleccionado = 0;
                ////////////////////////////////////////////////////////////////////
				// recorrer la array para extraer los datos.
                ////////////////////////////////////////////////////////////////////
				for($i=0;$i<=$fila-1;$i++){
					$codigo_alumnos = $codigo_alumno[0][$i];
                    $chk_matricular = $chk_matricula[0][$i];
                    ////////////////////////////////////////////////////////////////////
                    // VALORAR SI CHK_MATRICULAR ES IGUAL A TRUE
                    ////////////////////////////////////////////////////////////////////
                    if($chk_matricular == "true")
                    {
                        ////////////////////////////////////////////////////////////////////
                        // CONSULTAR SI LA MATRICULA EXISTE.
                        ////////////////////////////////////////////////////////////////////
                        $query = "SELECT a.id_alumno, am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_turno, am.codigo_ann_lectivo
                              FROM alumno a
                              INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                              WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_todos."' and am.codigo_alumno = ".$codigo_alumnos;
                        // Ejecutamos el Query para saber si la matricula existe.
                        $consulta_ = $dblink -> query($query);
                        if($consulta_ -> rowCount() != 0){
                            // contar no matriculas.
                            $no_matriculas++;
                        }else{
                        // graba el codigo del alumno en la tabla alumno matricula. para poder generar el codigo matricula.
                            $query_matricula = "INSERT INTO alumno_matricula (codigo_alumno) VALUES (".$codigo_alumnos.")";
                        // Ejecutamos el Query.
                            $consulta = $dblink -> query($query_matricula);
                        // Consultar a la tabla el ultimo ingresado de la tabla alumno matricula, para que pueda generar el codigo de la matricula.				
                            $query_consulta_matricula = "SELECT codigo_alumno, id_alumno_matricula from alumno_matricula where codigo_alumno = ".$codigo_alumnos." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
                        // Ejecutamos el Query.
                            $result_consulta = $dblink -> query($query_consulta_matricula);
                                while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                    {$fila_alumno = $row["codigo_alumno"]; $fila_matricula = $row["id_alumno_matricula"];}
                        // Actualizar la tabla alumno_matricula con codigos de bachillerato, grado, seccion, año lectivo y año lectivo.
                            $query_update_matricula = "UPDATE alumno_matricula SET codigo_bach_o_ciclo = '$codigo_modalidad',
                                codigo_grado = '$codigo_grado',
                                codigo_seccion = '$codigo_seccion',
                                codigo_ann_lectivo ='$codigo_annlectivo',
                                certificado = '$certificado',
                                pn = '$pn',
                                codigo_turno = '$codigo_turno'
                                    WHERE codigo_alumno = ".$fila_alumno." and id_alumno_matricula = ".$fila_matricula;
                        // Ejecutar Query.
                            $consulta = $dblink -> query($query_update_matricula);
                        // Consultar a la tabla alumno_matricula y codigo bachillerato o ciclo.
                            $query_consulta = "SELECT codigo_bach_o_ciclo from alumno_matricula where codigo_alumno = ".$codigo_alumnos." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
                            $result_consulta = $dblink -> query($query_consulta);
                                while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                    {$fila_codigo_bachillerato = $row["codigo_bach_o_ciclo"];}
                        // Consultar a la tabla codigo asignatura, para generar el codigo individual de cada una de ellas segun el ciclo o bachillerato.
                            $query_consulta_asignatura = "SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = '".$fila_codigo_bachillerato."' and codigo_ann_lectivo = '".$codigo_annlectivo."' and codigo_grado = '".$codigo_grado."' ORDER BY codigo_asignatura ASC";
                                $result_consulta = $dblink -> query($query_consulta_asignatura);
                                    while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                    {
                                        $fila_codigo_asignatura = $row["codigo_asignatura"];      
                                        $query_insert = "INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES ('$fila_codigo_asignatura',$fila_alumno,$fila_matricula)";
                                        $result_consulta_insert_notas = $dblink -> query($query_insert);
                                    }
                        // contar matriculas realizadas
                            $x_matriculas++;
                        }
                    }else{// validar solo que sean CHK_MATRICULAR IGUAL A TRUE.
                        // cuaando haya sido seleccionado.
                           $no_seleccionado++;
                        }
                    // Valorar los Mensajes. NINGUN REGISTRO SELECCIONADO.
                       if($no_seleccionado !=0){
                            $respuestaOK = false;
                            $contenidoOK = "Ningún registro seleccionado.";
                            $mensajeError = "...";
                       }
                    // Valorar los Mensajes. matricula ya existente.
                       if($no_matriculas !=0){
                            $respuestaOK = false;
                            $contenidoOK = $no_matriculas . " - Matricula(s) Ya Existente.";
                            $mensajeError = "...";
                       }
                    // Valorar los Mensajes. Matricula exitosa.
                       if($x_matriculas !=0){
                            $respuestaOK = true;
                            $contenidoOK = $x_matriculas . " - Matricula(s) Exitosas.";
                            $mensajeError = "...";
                       }                        
                }// FIn DEL FOR PRINCIPAL
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
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>