<?php
clearstatcache();
header("Content-Type: text/html;charset=iso-8859-1");
// Inicializamos variables de mensajes y JSON
	$respuestaOK = false;
	$mensajeError = "No se puede ejecutar la aplicación";
	$contenidoOK = "";
	$codigo_grado = "";
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
			case 'BuscarUser':
				// Armar Colores
				$statusTipo = ["01" => "btn-success", "02" => "btn-warning", "03" => "btn-danger"];
				// Armamos el query.
				$codigo_annlectivo = trim($_POST['lstannlectivo']);
				$codigo_modalidad = trim($_POST['lstmodalidad']);
				//
				if(empty($codigo_modalidad)){

				}else{
					$query = "SELECT orgs.codigo_bachillerato, orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_ann_lectivo, orgs.codigo_turno, encargado.codigo_docente,
						btrim(per.nombres || CAST(' ' AS VARCHAR) || per.apellidos) as nombre_docente,
						bach.nombre as nombre_bachillerato, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo, tur.nombre as nombre_turno
						FROM organizacion_grados_secciones orgs
						INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgs.codigo_bachillerato
						INNER JOIN grado_ano gan ON gan.codigo = orgs.codigo_grado
						INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
						INNER JOIN ann_lectivo ann ON ann.codigo = orgs.codigo_ann_lectivo
						INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
						INNER JOIN encargado_grado encargado ON encargado.codigo_bachillerato = orgs.codigo_bachillerato and encargado.codigo_grado = orgs.codigo_grado and encargado.codigo_seccion = orgs.codigo_seccion and encargado.codigo_ann_lectivo = orgs.codigo_ann_lectivo
						INNER JOIN personal per ON per.id_personal = encargado.codigo_docente
						 	WHERE orgs.codigo_bachillerato = '$codigo_modalidad' and orgs.codigo_ann_lectivo = '$codigo_annlectivo'
								ORDER BY orgs.codigo_grado, orgs.codigo_seccion ASC";
				}
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);
				//
				
				if($consulta -> rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
				        // rellenar antes el $contenidoOK.
					$contenidoOK = "";
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						$num++; $codigo_grado = $listado['codigo_grado']; $codigo_seccion = $listado['codigo_seccion'];
						$codigo_modalidad = $listado['codigo_bachillerato']; $codigo_annlectivo = trim($listado['codigo_ann_lectivo']);
						$codigo_turno = $listado['codigo_turno']; $nombre_bachillerato = $listado['nombre_bachillerato'];
						$nombreGrado = trim($listado['nombre_grado']);
						$nombreSeccion = trim($listado['nombre_seccion']);
						$nombreTurno = trim($listado['nombre_turno']);
						$nombreDocente = trim($listado['nombre_docente']);
						//
						$todos = $codigo_modalidad.$codigo_grado.$codigo_seccion.$codigo_annlectivo.$codigo_turno;
						//
						$contenidoOK .= '<tr><td style="width: 5px">'.$num.'</td>'
							.'<td style="width: 25px"><label>'. $nombreGrado.' - ' . $nombreSeccion . ' - '. $nombreTurno.'</label>
								<br>Encargado:
								<span class="badge rounded-pill bg-dark">
									<span "class=visually-hidden">'.$nombreDocente.'</span>
								</span>
							</td>'
							.'<td style="width: 35px"><a data-accion=listados_01 class="btn btn-md btn-secondary data-toggle="tooltip" data-placement="top" title="Ver o Imprimir" href='.$todos.'><span class="fas fa-print"></span></a>'.'</td>'
							.'<td style="width: 35px"><a data-accion=listados_02 class="btn btn-md btn-secondary data-toggle="tooltip" data-placement="top" title="Ver o Imprimir" href='.$todos.'><span class="fas fa-print"></span></a>'.'</td>'
							.'</tr>';
					// matriz para los grados.
					$datosGrados[] = [
						"codigo" => trim($listado['codigo_grado']),
						"descripcion" => trim($listado['nombre_grado'])
						];
					}
					$mensajeError = $nombre_bachillerato;
					// quitar datos duplicados de la matriz $datosGrados[]
					// Filtrar duplicados con un array asociativo
						$uniqueData = [];
						foreach ($datosGrados as $item) {
							$uniqueData[$item['codigo']] = $item; // Sobreescribe duplicados
						}
					// Convertir el array asociativo de nuevo a un array indexado
					$filteredData = array_values($uniqueData);

				}
				else{
					$respuestaOK = true;
					$contenidoOK = '
						<tr id="sinDatos">
							<td colspan="8" class="centerTXT">No Hay Registros.</td>
						</tr>
					'.$query;
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
// Armamos array para convertir a JSON
$salidaJson = [
	"respuesta" => $respuestaOK,
	"mensaje" => $mensajeError,
	"contenido" => $contenidoOK,
	"codigoGrado" => $filteredData
];
echo json_encode($salidaJson);