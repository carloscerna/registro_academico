<?php
// cambiar a utf-8.
header("Content-Type: text/html;charset=iso-8859-1");
// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "Registro no encontrado";
$contenidoOK = "";
$titulo_tabla = "";
$todos = "";
$codigo_modalidad = "";
$codigo_alumno = "";
$codigo_matricula = "";
$codigo_grado = "";
$periodo = "";
$observacion = "";
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
			case 'BuscarEstudiante':
				$codigo_annlectivo = $_REQUEST["lstannlectivo"];
				$codigo_nie = $_REQUEST["codigo_nie"];
				// Modificar forma de búsqueda.
				$query_estudiante = "SELECT am.id_alumno_matricula, am.codigo_alumno, am.codigo_grado, am.codigo_bach_o_ciclo, am.codigo_seccion, am.codigo_turno,
									btrim(a.nombre_completo || CAST(' ' as VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
									bach.nombre as nombre_modalidad, gr.nombre as nombre_grado, sec.nombre as nombre_seccion, cat_tur.nombre as nombre_turno
									FROM alumno_matricula am
									INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
									INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
									INNER JOIN grado_ano gr ON gr.codigo = am.codigo_grado 
									INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
									INNER JOIN turno cat_tur ON cat_tur.codigo = am.codigo_turno 
									WHERE am.codigo_alumno = (select id_alumno from alumno a where codigo_nie = '$codigo_nie') AND
									am.codigo_ann_lectivo = '$codigo_annlectivo'";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query_estudiante);
                // Recorrer la consulta.
				if($consulta -> rowCount() != 0){
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$nombre_estudiante = (trim($listado['nombre_completo_alumno']));
						$codigo_alumno = $listado['codigo_alumno'];
						$codigo_matricula = $listado['id_alumno_matricula'];
						$nombre_modalidad = $listado['nombre_modalidad'];
						$nombre_grado = $listado['nombre_grado'];
						$codigo_grado = $listado['codigo_grado'];
						$nombre_seccion = $listado['nombre_seccion'];
						$nombre_turno = $listado['nombre_turno'];
						$codigo_seccion = $listado['codigo_seccion'];
						$codigo_turno = $listado['codigo_turno'];
						$codigo_modalidad = $listado['codigo_bach_o_ciclo'];
						
						$titulo_tabla = $nombre_estudiante . " - " . $nombre_modalidad . " - " . $nombre_grado . " '" . $nombre_seccion . "' - " . $nombre_turno;
						$todos = $codigo_modalidad . $codigo_grado . $codigo_seccion . $codigo_annlectivo . $codigo_turno;
					}
					// Mensaje
					$respuestaOK = true;
					$mensajeError = "Estudiante Encontrado.";
				}else{
					// Mensaje
					$respuestaOK = false;
					$mensajeError = "Estudiante NO Encontrado!";
				}
			break;
			case 'BuscarCalificacion':
				// Declarar Variables y Crear consulta Query.
					$codigo_nie = $_REQUEST["codigo_nie"];
					$codigo_alumno = $_REQUEST["codigo_alumno"];	
					$codigo_matricula = $_REQUEST["codigo_matricula"];
					$codigo_annlectivo = $_REQUEST["codigo_annlectivo"];
					$codigo_grado = $_REQUEST["codigo_grado"];
                    if(isset($_REQUEST["codigo_periodo"])){$periodo = $_REQUEST["codigo_periodo"];}else{$nota_p_p = "nota_p_p_1";}
                // Armar variable para el valor de la Nota, que influye sobre la recuperaci�n y el promedio final.
                    //if($codigo_modalidad >= '06'){$nota_evaluar = 6;}else{$nota_evaluar = 5;}
                // Variable para el min y max del number.
                    $valor_min_max = array("min=0 max= 10 maxlength=5","min=0 max= 10 maxlength=5");
					$valor_m_m = ""; $observacion_ = "observacion_1";
				// Extraer nombre de los campos según el periodo seleccionado.
					NombreCampos($periodo);
				// Armar la consulta para las diferencias opciones de actualiaci�n de notas.
					$query_calificacion = "SELECT n.id_notas, n.codigo_asignatura, asig.nombre AS nombre_asignatura,
						n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.recuperacion, n.nota_recuperacion_2, n.nota_institucional, n.nota_final,
						n.observacion_1, n.observacion_2, n.observacion_3, n.observacion_4, n.observacion_5, n.observacion_r1, n.observacion_r2,
						asig.codigo_area, cat_area.descripcion as nombre_area
						FROM nota n
						INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura 
						INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area 
						INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = n.codigo_asignatura AND aaa.codigo_ann_lectivo = '$codigo_annlectivo' AND aaa.codigo_grado = '$codigo_grado'
						WHERE n.codigo_alumno ='$codigo_alumno' and n.codigo_matricula = '$codigo_matricula'
						ORDER BY aaa.orden";	
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query_calificacion);
                // Recorrer la consulta.
				if($consulta -> rowCount() != 0){
					$respuestaOK = true; $fondo = array("info","success");
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// Variables e información de la consulta.
                        $num++;
						$nombre_area = trim($listado['nombre_area']);						
						$codigo_area = trim($listado['codigo_area']);						
						$nombre_asignatura = trim($listado['nombre_asignatura']);
						$id_notas = $listado['id_notas'];
                        $nota_ = (trim($listado[$nota_p_p]));
						$observacion_ = (trim($listado[$observacion]));
						// CAMBIAR COLOR DE LA FILA SEGUN NOTA.
						if($nota_ == 0){
							$color_fila = '<tr style=background:#2980B9><td>';
							}else{
							$color_fila = '<tr style=background:##FBFCFC><td>';
							}
						
						if($periodo == 'R1' || $periodo == "R2")
						{
							if($codigo_area == '01'){
								$contenidoOK .= $color_fila.$num
                                .'<input type=hidden name=id_notas value = '.$id_notas.'>'
                                .'<td>'.$nombre_area
                                .'<td>'.$nombre_asignatura
                                ."<td><div class='d-flex justify-content-end'><input type=text name=nota $valor_m_m value = '$nota_' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>"
                                ."<td><div class='d-flex justify-content-end'><input type=text name=observacion class=form-control  value = $observacion_></div>"
								;
							}
						}
						// Imprimir� cuando se refiera al per�odo 1, 2 , 3 ,4, etc.
						if($periodo != 'R1' && $periodo != 'R2')
						{
							$contenidoOK .= $color_fila.$num
								.'<input type=hidden name=id_notas value = '.$id_notas.'>'
                                .'<td>'.$nombre_area
                                .'<td>'.$nombre_asignatura
                                ."<td><div class='d-flex justify-content-start'><input type=text name=nota $valor_m_m value = '$nota_' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>"
                                ."<td><div class='d-flex justify-content-end'><input type=text name=observacion class=form-control value = $observacion_></div>"
								;							
						}
					}	// Recorrido de la Tabla.
					$mensajeError = "Calificaciones Encontradas.";
				} // If que cuenta.
				else{
					$respuestaOK = false;
					$contenidoOK = '
						<tr id="sinDatos">
							<td colspan="4">No Hay Registros de este alumno...</td>
						</tr>
					'.$query;
					$mensajeError =  'Calificaicones No Encontradas';
				}
			break;

			case 'ActualizarCalificaciones':			
				// DECLARAR ARRAY ULTIZADAS EN LA ACTULIZACIÓN
				$codigo_alumno = array(); $codigo_matricula = array(); $codigo_asignatura = array(); $codigo_modalidad = array();
				$id_notas = array(); $nota = array();
				// armar variables y consulta Query.
				$id_notas[] = $_POST["id_notas_"];
				$nota[] = $_POST["nota_"];
				$fila = $_POST["fila"];
				$periodo = $_POST["periodo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				// Extraer el nombre del campio periodo y observación para actualizar en la tabla Nota.
					NombreCampos($periodo);
				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila-1;$i++){
					$id_notas_ = $id_notas[0][$i];
					$nota_ = $nota[0][$i];
					// CONDICIONAR SI ES MAYOR DE 10.0
                    if($nota_ > 10){
                        $nota_ = 1;
                    }
					// armar sql. actualización de calificación en la tabla nota.
						$query = "UPDATE nota SET $nota_p_p = '$nota_' WHERE id_notas = '$id_notas_'";
					// Ejecutamos el Query.
						$consulta = $dblink -> query($query);
				}

				// recorrer la array para extraer los datos.
				for($k=0;$k<=$fila-1;$k++){
					//
					$id_notas_ = $id_notas[0][$k];
					// Actualizar nota final.
					if ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE id_notas = '$id_notas_')
							                WHERE id_notas = '$id_notas_'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					if ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) as promedio
							from nota WHERE id_notas = '$id_notas_')
							                WHERE id_notas = '$id_notas_'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}

					// CALCULO PARA NOCTURNA 5 MODULOS.
					if ($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4 + nota_p_p_5)/5,0) as promedio
							from nota WHERE id_notas = '$id_notas_')
							                WHERE id_notas = '$id_notas_'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
				}
					
				$respuestaOK = true;
				$contenidoOK = '';
				$mensajeError =  'Registros Actualizados.';
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
		"codigo_alumno" => $codigo_alumno,
		"codigo_grado" => $codigo_grado
	);
// 	encode por JSON.
echo json_encode($salidaJson);

function NombreCampos($periodo){
	global $periodo, $observacion, $nota_p_p;

	if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1"; $observacion = "observacion_1";}
	if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2"; $observacion = "observacion_2";}
	if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3"; $observacion = "observacion_3";}
	if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4"; $observacion = "observacion_4";}
	if($periodo == "Periodo 5"){$nota_p_p = "nota_p_p_5"; $observacion = "observacion_5";}
	if($periodo == "R1"){$nota_p_p = "recuperacion"; $observacion = "observacion_r1";}
	if($periodo == "R2"){$nota_p_p = "nota_recuperacion_2"; $observacion = "observacion_r2";}
	if($periodo == "Nota PAES"){$nota_p_p = "nota_paes"; }
	return true;
}
?>