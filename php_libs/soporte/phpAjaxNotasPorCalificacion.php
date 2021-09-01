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
$titulo_tabla = "";
$todos = "";
$codigo_modalidad = "";
$codigo_alumno = "";
$codigo_matricula = "";
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
        if(!empty($_POST['accion_actualizar'])){
			$_POST['accion'] = $_POST['accion_actualizar'];
		}
		// Verificamos las variables de acci�n
		switch ($_POST['accion']) {
			case 'BuscarNotas':
				// Declarar Variables y Crear consulta Query.
                    $codigo_nie = $_REQUEST["codigo_nie"];
                    if(isset($_REQUEST["lstperiodo"])){$periodo = $_REQUEST["lstperiodo"];}else{$nota_p_p = "nota_p_p_1";}
                    $codigo_annlectivo = $_REQUEST["lstannlectivo"];
                // Armar variable para el valor de la Nota, que influye sobre la recuperaci�n y el promedio final.
                    //if($codigo_modalidad >= '06'){$nota_evaluar = 6;}else{$nota_evaluar = 5;}
                // Variable para el min y max del number.
                    $valor_min_max = array("min=0 max= 10 maxlength=5","min=0 max= 10 maxlength=5");
                    $valor_m_m = ""; $observacion_ = "observacion_1";
                // Informaci�n Acad�mica.    
                    if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1"; $observacion = "observacion_1";}
                    if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2"; $observacion = "observacion_2";}
                    if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3"; $observacion = "observacion_3";}
                    if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4"; $observacion = "observacion_4";}
                    if($periodo == "Periodo 5"){$nota_p_p = "nota_p_p_5"; $observacion = "observacion_5";}
                    if($periodo == "Recuperacion"){$nota_p_p = "recuperacion";}
                    if($periodo == "Nota PAES"){$nota_p_p = "nota_paes";}
				// Armar la consulta para las diferencias opciones de actualiaci�n de notas.
					$query = "SELECT DISTINCT a.id_alumno, a.codigo_nie, btrim(a.nombre_completo || CAST(' ' as VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
                            am.id_alumno_matricula as codigo_matricula, am.codigo_bach_o_ciclo as codigo_modalidad,
                            n.codigo_asignatura, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.nota_final, n.observacion_1, n.observacion_2, n.observacion_3, n.observacion_4, n.observacion_5,
                            asig.nombre as nombre_asignatura, asig.codigo_area, cat_area.descripcion as nombre_area, asig.codigo_cc, cat_cc.descripcion as nombre_concepto,
							bach.nombre as nombre_modalidad, gr.codigo as codigo_grado, gr.nombre as nombre_grado, sec.codigo as codigo_seccion, sec.nombre as nombre_seccion, cat_tur.codigo as codigo_turno, cat_tur.nombre as nombre_turno,
							aaa.orden
                            FROM alumno a
								INNER JOIN alumno_matricula am ON am.codigo_alumno = a.id_alumno and am.codigo_ann_lectivo = '$codigo_annlectivo'
								INNER JOIN nota n ON n.codigo_matricula = am.id_alumno_matricula
								INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
								INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
								INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = am.codigo_grado
								INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
								INNER JOIN turno cat_tur ON cat_tur.codigo = am.codigo_turno
								INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = n.codigo_asignatura and aaa.orden <> 0
									WHERE codigo_nie = '$codigo_nie'
										ORDER BY aaa.orden";	
				
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
                // Recorrer la consulta.
				if($consulta -> rowCount() != 0){
					$respuestaOK = true; $fondo = array("info","success"); $num_fondo = 0;
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// Para cambiar el color de las filas.
						if($num_fondo == 0){$num_fondo = 1;}else{$num_fondo = 0;}
                        $num++;
                        $nombre_area = trim($listado['nombre_area']);
						$id_alumno = $listado['id_alumno'];
						$codigo_alumno = $listado['id_alumno'];
						$codigo_matricula = $listado['codigo_matricula'];
                        $codigo_asignatura = $listado['codigo_asignatura'];
						$codigo_modalidad = $listado['codigo_modalidad'];
						$codigo_grado = $listado['codigo_grado'];
						$codigo_seccion = $listado['codigo_seccion'];
						$codigo_turno = $listado['codigo_turno'];
                        $nombre_asignatura = trim($listado['nombre_asignatura']);
						$nie = $listado['codigo_nie'];
						$apellido_alumno = (trim($listado['nombre_completo_alumno']));
                        $nota_ = (trim($listado[$nota_p_p]));
                        $observacion_ = (trim($listado[$observacion]));
						$nota_final = trim($listado['nota_final']);
						// ENVIAR TITULO DE LOS DATOS AL CARD TITULO.
						$nombre_modalidad = trim($listado['nombre_modalidad']);
						$nombre_grado = trim($listado['nombre_grado']);
						$nombre_seccion = trim($listado['nombre_seccion']);
						$nombre_turno = trim($listado['nombre_turno']);
						$titulo_tabla = $apellido_alumno . " - " . $nombre_modalidad . " - " . $nombre_grado . " '" . $nombre_seccion . "' - " . $nombre_turno;
						$todos = $codigo_modalidad . $codigo_grado . $codigo_seccion . $codigo_annlectivo . $codigo_turno;
						//VALIDAR EL MIN Y MAX DEL CAMPO NOTA.
						if($codigo_asignatura == '31'){$valor_m_m = $valor_min_max[1];}else{$valor_m_m = $valor_min_max[0];}
						// Rellenar variable dependiendo de la Consulta.
						// S� se ha seleccionado Recuperaci�n evaluar y presentar s�lo los registros necesarios.
						// CAMBIAR COLOR DE LA FILA SEGUN NOTA.
						if($nota_ == 0){
							$color_fila = '<tr style=background:#2980B9><td>';
							}else{
							$color_fila = '<tr style=background:##FFFFFF><td>';
							}
						
						if($periodo == 'Recuperacion' and $nota_final < $nota_evaluar)
						{
							$contenidoOK .= $color_fila.$num
								.'<input type=hidden name=codigo_alumno size=1 value = '.$id_alumno.'>'
								.'<input type=hidden name=codigo_matricula size=1 value = '.$codigo_matricula.'>'
                                .'<input type=hidden name=codigo_asignatura size=1 value = '.$codigo_asignatura.'>'
                                .'<input type=hidden name=codigo_asignatura size=1 value = '.$codigo_modalidad.'>'
                                .'<td>'.$nombre_area
                                .'<td>'.$nombre_asignatura
                                ."<td><div class='d-flex justify-content-end'><input type=text name=nota $valor_m_m value = $nota_ onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>"
                                ."<td><div class='d-flex justify-content-end'><input type=text name=observacion class=form-control  value = $observacion_></div>"
								;
						}
						// Imprimir� cuando se refiera al per�odo 1, 2 , 3 ,4, etc.
						if($periodo != 'Recuperacion')
						{
							$contenidoOK .= $color_fila.$num
								.'<input type=hidden name=codigo_alumno size=1 value = '.$id_alumno.'>'
								.'<input type=hidden name=codigo_matricula size=1 value = '.$codigo_matricula.'>'
                                .'<input type=hidden name=codigo_asignatura size=1 value = '.$codigo_asignatura.'>'
                                .'<input type=hidden name=codigo_modalidad size=1 value = '.$codigo_modalidad.'>'
                                .'<td>'.$nombre_area
                                .'<td>'.$nombre_asignatura
                                ."<td><div class='d-flex justify-content-start'><input type=text name=nota $valor_m_m value = $nota_ onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>"
                                ."<td><div class='d-flex justify-content-end'><input type=text name=observacion class=form-control value = $observacion_></div>"
								;							
						}
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

			case 'ActualizarNotas':			
				// DECLARAR ARRAY ULTIZADAS EN LA ACTULIZACIÓN
				$codigo_alumno = array(); $codigo_matricula = array(); $codigo_asignatura = array(); $codigo_modalidad = array(); $nota = array();
				// armar variables y consulta Query.
				$codigo_alumno[] = $_POST["codigo_alumno_"];
				$codigo_matricula[] = $_POST["codigo_matricula_"];
                $codigo_asignatura[] = $_POST["codigo_asignatura_"];
                $codigo_modalidad[] = $_POST["codigo_modalidad_"];
				$nota[] = $_POST["nota_"];
				$fila = $_POST["fila"];
				$periodo = $_POST["periodo"];
				//$codigo_modalidad = $_POST["codigo_modalidad"];
				
                if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1"; $observacion = "observacion_1";}
                if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2"; $observacion = "observacion_2";}
                if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3"; $observacion = "observacion_3";}
                if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4"; $observacion = "observacion_4";}
                if($periodo == "Periodo 5"){$nota_p_p = "nota_p_p_5"; $observacion = "observacion_5";}
				if($periodo == "Recuperacion"){$nota_p_p = "recuperacion";}
				if($periodo == "Nota PAES"){$nota_p_p = "nota_paes";}
				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila-1;$i++){
					$codigo_a = $codigo_alumno[0][$i];
					$codigo_m = $codigo_matricula[0][$i];
					$codigo_asig = $codigo_asignatura[0][$i];
					$nota_ = $nota[0][$i];
					// CONDICIONAR SI ES MAYOR DE 10.0
                    if($nota_ > 10){
                        $nota_ = 1;
                    }
					// armar sql.
					    $query = "UPDATE nota SET $nota_p_p = '$nota_' WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig'";
					// Ejecutamos el Query.
						$consulta = $dblink -> query($query);
				}

				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila-1;$i++){
					$codigo_a = $codigo_alumno[0][$i];
					$codigo_m = $codigo_matricula[0][$i];
					$codigo_asig = $codigo_asignatura[0][$i];

				// Actualizar nota final.
					if ($codigo_modalidad[0][$i] >= '03' && $codigo_modalidad[0][$i] <= '05'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig')
							                WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					if ($codigo_modalidad[0][$i] >= '06' && $codigo_modalidad[0][$i] <= '09'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig')
							                WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
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
		"contenido" => $contenidoOK,
		"titulo_tabla" => $titulo_tabla,
		"todos" => $todos,
		"codigo_modalidad" => $codigo_modalidad,
		"codigo_matricula" => $codigo_matricula,
		"codigo_alumno" => $codigo_alumno
	);
// 	encode por JSON.
echo json_encode($salidaJson);
?>