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

// Validar conexi�n con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acci�n
		switch ($_POST['accion']) {
			case 'BuscarNotas':
				// Declarar Variables y Crear consulta Query.
						$codigo_all = $_REQUEST["lstmodalidad"] . substr($_REQUEST["lstgradoseccion"],0,4) . $_REQUEST["lstannlectivo"];
						$periodo = $_REQUEST["lstperiodo"];
						$codigo_asignatura = substr($_REQUEST["lstasignatura"],0,3);
						$codigo_modalidad = $_REQUEST["lstmodalidad"];
						// Armar variable para el valor de la Nota, que influye sobre la recuperaci�n y el promedio final.
						if($codigo_modalidad >= '06'){$nota_evaluar = 6;}else{$nota_evaluar = 5;}
				  		// Variable para el min y max del number.
						$valor_min_max = array("min=0 max= 10 maxlength=5","min=0 max= 10 maxlength=5");
						$valor_m_m = "";
						 // Informaci�n Acad�mica.
						 $codigo_bachillerato = substr($codigo_all,0,2);
						 $codigo_grado = substr($codigo_all,2,2);
						 $codigo_seccion = substr($codigo_all,4,2);
						 $codigo_annlectivo = substr($codigo_all,6,2);
						 
						if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
						if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
						if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
						if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4";}
						if($periodo == "Recuperacion"){$nota_p_p = "recuperacion";}
						if($periodo == "Nota PAES"){$nota_p_p = "nota_paes";}
				// Armar la consulta para las diferencias opciones de actualiaci�n de notas.
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno, 
							  am.codigo_bach_o_ciclo, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, 
							  sec.nombre as nombre_seccion, am.id_alumno_matricula, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.recuperacion, n.nota_final, n.nota_paes, n.codigo_matricula, n.id_notas, n.codigo_asignatura, asig.nombre as nombre_asignatura
							  FROM alumno a 
							  INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
							  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
							  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
							  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
							  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
							  INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
							  INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
							  WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all."' and n.codigo_asignatura = '".$codigo_asignatura."'
								  ORDER BY apellido_alumno, n.codigo_asignatura, n.id_notas ASC";
				
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
						$codigo_matricula = $listado['codigo_matricula'];
						$codigo_asignatura = $listado['codigo_asignatura'];
						$nie = $listado['codigo_nie'];
						$apellido_alumno = (trim($listado['apellido_alumno']));
						$nota_ = (trim($listado[$nota_p_p]));
						$nota_final = trim($listado['nota_final']);
						
						//VALIDAR EL MIN Y MAX DEL CAMPO NOTA.
						if($codigo_asignatura == '31'){$valor_m_m = $valor_min_max[1];}else{$valor_m_m = $valor_min_max[0];}
						// Rellenar variable dependiendo de la Consulta.
						// S� se ha seleccionado Recuperaci�n evaluar y presentar s�lo los registros necesarios.
						// CAMBIAR COLOR DE LA FILA SEGUN NOTA.
						if($nota_ == 0){
							$color_fila = '<tr style=background:#2980B9><td>';
							}else{
							$color_fila = '<tr style=background:##FBFCFC><td>';
							}
						
						if($periodo == 'Recuperacion' and $nota_final < $nota_evaluar)
						{
							$contenidoOK .= $color_fila.$num
								.'<td><input type=hidden name=codigo_alumno size=1 value = '.$id_alumno.'>'
								.'<td><input type=hidden name=codigo_matricula size=1 value = '.$codigo_matricula.'>'
								.'<td><input type=hidden name=codigo_asignatura size=1 value = '.$codigo_asignatura.'>'
								.'<td>'.$nie
								.'<td>'.$apellido_alumno
								.'<td>'.$nota_final
								."<td style='width: 40%'><div class='d-flex justify-content-end'><input type=text name=nota $valor_m_m value = $nota_ onkeypress='return validarCualquierNumero();' class='form-control decimal-1-places text-right' style='width:40%'></div>"
								;
						}
						// Imprimir� cuando se refiera al per�odo 1, 2 , 3 ,4, etc.
						if($periodo != 'Recuperacion')
						{
							$contenidoOK .= $color_fila.$num
								.'<td><input type=hidden name=codigo_alumno size=1 value = '.$id_alumno.'>'
								.'<td><input type=hidden name=codigo_matricula size=1 value = '.$codigo_matricula.'>'
								.'<td><input type=hidden name=codigo_asignatura size=1 value = '.$codigo_asignatura.'>'
								.'<td>'.$nie
								.'<td>'.$apellido_alumno
								.'<td>'.$nota_final
								."<td style='width: 40%'><div class='d-flex justify-content-end'><input type=text name=nota $valor_m_m value = $nota_ onkeypress='return validarCualquierNumero();' class='form-control decimal-1-places text-right' style='width:40%'></div>"
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
				// armar variables y consulta Query.
				$codigo_alumno[] = $_POST["codigo_alumno_"];
				$codigo_matricula[] = $_POST["codigo_matricula_"];
				$codigo_asignatura[] = $_POST["codigo_asignatura_"];
				$nota[] = $_POST["nota_"];
				$fila = $_POST["fila"];
				$periodo = $_POST["periodo"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				
				if($periodo == "Periodo 1"){$nota_p_p = "nota_p_p_1";}
				if($periodo == "Periodo 2"){$nota_p_p = "nota_p_p_2";}
				if($periodo == "Periodo 3"){$nota_p_p = "nota_p_p_3";}
				if($periodo == "Periodo 4"){$nota_p_p = "nota_p_p_4";}
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
					if ($codigo_modalidad >= '03' && $codigo_modalidad <= '05'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig')
							                WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					if ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig')
							                WHERE codigo_alumno = '$codigo_a' and codigo_matricula = '$codigo_m' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
						// CALCULO PARA NOCTURNA 5 MODULOS.
						if ($codigo_modalidad >= '10' and $codigo_modalidad <= '12'){
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
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>