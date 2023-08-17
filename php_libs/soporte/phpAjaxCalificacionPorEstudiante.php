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
$codigo_institucion = "";
$codigo_genero = "";
$url_foto = "";
$calificacion_A1 = 0; $calificacion_A2 = 0; $calificacion_PO = 0; $calificacion_RE = 0;
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
				$codigo_institucion = $_SESSION['codigo_institucion'];
				// Modificar forma de búsqueda.
				$query_estudiante = "SELECT am.id_alumno_matricula, am.codigo_alumno, am.codigo_grado, am.codigo_bach_o_ciclo, am.codigo_seccion, am.codigo_turno,
									btrim(a.nombre_completo || CAST(' ' as VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno, a.foto, a.codigo_genero,
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
						$url_foto = $listado['foto'];
						$codigo_genero = $listado['codigo_genero'];
						
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
						n.nota_a1_1, n.nota_a2_1, n.nota_a3_1, n.nota_r_1, n.nota_p_p_1, n.observacion_1,
						n.nota_a1_2, n.nota_a2_2, n.nota_a3_2, n.nota_r_2, n.nota_p_p_2, n.observacion_2,
						n.nota_a1_3, n.nota_a2_3, n.nota_a3_3, n.nota_r_3, n.nota_p_p_3, n.observacion_3,
						n.nota_a1_4, n.nota_a2_4, n.nota_a3_4, n.nota_r_4, n.nota_p_p_4, n.observacion_4,
						n.nota_a1_5, n.nota_a2_5, n.nota_a3_5, n.nota_r_5, n.nota_p_p_5, n.observacion_5,
						n.recuperacion, n.nota_recuperacion_2, n.nota_institucional, n.nota_final, n.observacion_r1, n.observacion_r2,
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
						$actividad_1 = $listado[$calificacion_A1];
						$actividad_2 = $listado[$calificacion_A2];
						$actividad_3 = $listado[$calificacion_PO];
						$actividad_re = $listado[$calificacion_RE];
						$observacion_ = (trim($listado[$observacion]));
						// CAMBIAR COLOR DE LA FILA SEGUN NOTA.
						if($nota_ == 0){
							$color_fila = '<tr style=background:#C9FC98><td>';
							}else{
							$color_fila = '<tr style=background:##FBFCFC><td>';
							}
						//
						if($periodo == 'R1' || $periodo == "R2")
						{
							if($codigo_area == '01'){
								$contenidoOK .= "$color_fila$num
                                <input type=hidden name=id_notas value = '$id_notas'>
                                <td>$nombre_area
                                <td>$nombre_asignatura
								<td><div class='d-flex justify-content-end'><input type=text name=nota_a1 value = '$actividad_1' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
								<td><div class='d-flex justify-content-end'><input type=text name=nota_a2 value = '$actividad_2' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
								<td><div class='d-flex justify-content-end'><input type=text name=nota_a3 value = '$actividad_3' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
								<td><div class='d-flex justify-content-end'><input type=text name=nota_re value = '$actividad_re' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
                                <td><div class='d-flex justify-content-end'><input type=text name=nota value = '$nota_' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
                                <td><div class='d-flex justify-content-end'><input type=text name=observacion class=form-control  value=$observacion_></div>"
								;
							}
						}
						// Imprimir� cuando se refiera al per�odo 1, 2 , 3 ,4, etc.
						if($periodo != 'R1' && $periodo != 'R2')
						{
							$contenidoOK .= "$color_fila$num
								<input type=hidden name=id_notas value = '$id_notas'>
                                <td>$nombre_area
                                <td>$nombre_asignatura
								<td><div class='d-flex justify-content-end'><input type=text name=nota_a1 value='$actividad_1' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
								<td><div class='d-flex justify-content-end'><input type=text name=nota_a2 value='$actividad_2' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
								<td><div class='d-flex justify-content-end'><input type=text name=nota_a3 value='$actividad_3' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
								<td><div class='d-flex justify-content-end'><input type=text name=nota_re value='$actividad_re' onkeypress='return validarCualquierNumero(this);' class='form-control decimal-1-places text-right' style='width:40%'></div>
                                <td class='bg-cyan'><div class='d-flex justify-content-end'><input type=text name=nota value='$nota_' class='form-control' readonly></div>
                                <td><div class='d-flex justify-content-end'><input type=text name=observacion class=form-control value = $observacion_></div>"
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
				$nota_a1[] = $_POST["nota_a1"];
				$nota_a2[] = $_POST["nota_a2"];
				$nota_a3[] = $_POST["nota_a3"];
				$nota_re[] = $_POST["nota_re"];

				$fila = $_POST["fila"];
				$periodo = $_POST["periodo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				// Extraer el nombre del campio periodo y observación para actualizar en la tabla Nota.
					NombreCampos($periodo);
				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila-1;$i++){
					$id_notas_ = $id_notas[0][$i];
					$nota_a1_ = $nota_a1[0][$i];
					$nota_a2_ = $nota_a2[0][$i];
					$nota_a3_ = $nota_a3[0][$i];
					$nota_re_ = $nota_re[0][$i];
					// CONDICIONAR SI ES MAYOR DE 10.0
                    /*if($nota_ > 10){
                        $nota_ = 1;
                    }*/
					// armar sql. actualización de calificación en la tabla nota.
						$query = "UPDATE nota SET 
									$calificacion_A1 = '$nota_a1_',
									$calificacion_A2 = '$nota_a2_',
									$calificacion_PO = '$nota_a3_',
									$calificacion_RE = '$nota_re_' 
										WHERE id_notas = '$id_notas_'";
					// Ejecutamos el Query.
						$consulta = $dblink -> query($query);
				}
				// recorrer la array para extraer los datos.
				// ACTUALIZACION DE LA CALIFICACION FINAL.
				for($k=0;$k<=$fila-1;$k++){
					//
					$id_notas_ = $id_notas[0][$k];
					// Actualizar nota final. EDUCACION BASICA Y TERCER CICLO
					if ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){
						// Actualizar nota por promedio.
							$query_calificacion_periodo = "UPDATE nota SET
															$nota_p_p = (select round(($calificacion_A1 * 0.35) + ($calificacion_A2 * 0.35) + ($calificacion_PO * 0.30),0) as promedio_periodo 
																FROM nota WHERE id_notas = '$id_notas_') WHERE id_notas = '$id_notas_'";
						// Ejectuamos query.
							$consulta_nota_periodo = $dblink -> query($query_calificacion_periodo);											
						// Actualización Nota Final.
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE id_notas = '$id_notas_')
								WHERE id_notas = '$id_notas_'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					// EDUCACIÓN MEDIA GENERAL Y TECNICO
					if ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) as promedio
							from nota WHERE id_notas = '$id_notas_')
									WHERE id_notas = '$id_notas_'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					// CALCULO PARA NOCTURNA 5 MODULOS. NOCTURNA
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
		"codigo_grado" => $codigo_grado,
		"url_foto" => $url_foto,
		"codigo_genero" => $codigo_genero,
		"codigo_institucion" => $codigo_institucion
	);
// 	encode por JSON.
echo json_encode($salidaJson);

function NombreCampos($periodo){
	$numero_periodo = 1; // Valor por defecto del período.
	$campo_periodos = array('','nota_p_p_');	// Matriz para el nombre del campo períodos.
	$campo_actividades = array('nota_a1_','nota_a2_','nota_a3_','nota_r_');	// Matriz para el nombre del campo Actividades.
	$campo_observaciones = array('','observacion_');	// Mattriz para el nombre del campo Observación.

	global $periodo, $observacion, $nota_p_p, $calificacion_A1, $calificacion_A2, $calificacion_PO, $calificacion_RE;

	switch ($periodo) {
		case 'Periodo 1':
			$numero_periodo = 1;
		break;
		case 'Periodo 2':
			$numero_periodo = 2;
		break;
		case 'Periodo 3':
			$numero_periodo = 3;
		break;
		case 'Periodo 4':
			$numero_periodo = 4;
		break;
		case 'Periodo 5':
			$numero_periodo = 5;
		break;
	}
	// cambiar el nombre del cambipo segun el valor de Periodo y Numero Periodo.
		$nota_p_p = $campo_periodos[1] . $numero_periodo;
		$calificacion_A1 = $campo_actividades[0] . $numero_periodo;
		$calificacion_A2 = $campo_actividades[1] . $numero_periodo;
		$calificacion_PO = $campo_actividades[2] . $numero_periodo;
		$calificacion_RE = $campo_actividades[3] . $numero_periodo;
		$observacion = $campo_observaciones[1] . $numero_periodo;
			if($periodo == "R1"){$nota_p_p = "recuperacion"; $observacion = "observacion_r1";}
			if($periodo == "R2"){$nota_p_p = "nota_recuperacion_2"; $observacion = "observacion_r2";}
			if($periodo == "Nota PAES"){$nota_p_p = "nota_paes"; }
	return true;
}
?>