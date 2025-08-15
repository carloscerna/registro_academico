<?php
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: application/json;charset=utf-8");
sleep(0);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$resumenOK = []; // Array para los datos del resumen

// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

// Validar conexión con la base de datos
if($errorDbConexion == false){
	// Validamos que existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'BuscarListaMatricula':
				// Declarar Variables y Crear consulta Query.
                $codigo_annlectivo = $_POST["lstannlectivo"];
                $codigo_modalidad = $_POST["lstmodalidad"];
                
                $grado_seccion_turno = $_POST["lstgradoseccion"];
                $codigo_grado = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno = substr($grado_seccion_turno, 4, 2);
						
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                            am.id_alumno_matricula, am.codigo_seccion, am.codigo_turno,
                            am.sobreedad, am.repitente, am.retirado, am.nuevo_ingreso, am.pn, am.certificado, am.imprimir_foto
							FROM alumno a
							INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
							WHERE am.codigo_bach_o_ciclo = :modalidad AND am.codigo_grado = :grado AND am.codigo_seccion = :seccion AND am.codigo_ann_lectivo = :annlectivo AND am.codigo_turno = :turno
							ORDER BY apellido_alumno ASC";
				
                $stmt = $dblink->prepare($query);
                $stmt->bindParam(':modalidad', $codigo_modalidad);
                $stmt->bindParam(':grado', $codigo_grado);
                $stmt->bindParam(':seccion', $codigo_seccion);
                $stmt->bindParam(':annlectivo', $codigo_annlectivo);
                $stmt->bindParam(':turno', $codigo_turno);
                $stmt->execute();

				if($stmt->rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
                    
                    // Inicializar contadores para el resumen
                    $count_sobreedad = 0;
                    $count_repitente = 0;
                    $count_retirado = 0;
                    $count_nuevo_ingreso = 0;

                    // Obtener todas las secciones y turnos para el grado actual
                    $query_secciones = "SELECT s.codigo as codigo_seccion, s.nombre as nombre_seccion, t.codigo as codigo_turno, t.nombre as nombre_turno
                                        FROM organizacion_grados_secciones org
                                        INNER JOIN seccion s ON s.codigo = org.codigo_seccion
                                        INNER JOIN turno t ON t.codigo = org.codigo_turno
                                        WHERE org.codigo_ann_lectivo = :annlectivo AND org.codigo_bachillerato = :modalidad AND org.codigo_grado = :grado
                                        ORDER BY s.nombre, t.nombre";
                    $stmt_secciones = $dblink->prepare($query_secciones);
                    $stmt_secciones->bindParam(':annlectivo', $codigo_annlectivo);
                    $stmt_secciones->bindParam(':modalidad', $codigo_modalidad);
                    $stmt_secciones->bindParam(':grado', $codigo_grado);
                    $stmt_secciones->execute();
                    $secciones_disponibles = $stmt_secciones->fetchAll(PDO::FETCH_ASSOC);

					while($listado = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$num++;
                        
                        // Lógica para asignar estilo de fondo a celdas individuales y contar para el resumen
                        $style_sobreedad = '';
                        if ($listado['sobreedad'] == 't') {
                            $style_sobreedad = 'style="background-color: #bee5eb;"';
                            $count_sobreedad++;
                        }
                        $style_repitente = '';
                        if ($listado['repitente'] == 't') {
                            $style_repitente = 'style="background-color: #ffeeba;"';
                            $count_repitente++;
                        }
                        $style_retirado = '';
                        if ($listado['retirado'] == 't') {
                            $style_retirado = 'style="background-color: #f5c6cb;"';
                            $count_retirado++;
                        }
                        $style_nuevo_ingreso = '';
                        if ($listado['nuevo_ingreso'] == 't') {
                            $style_nuevo_ingreso = 'style="background-color: #c3e6cb;"';
                            $count_nuevo_ingreso++;
                        }

                        // Crear el select de sección y turno para esta fila
                        $select_seccion_turno = '<select name="seccion_turno" class="form-select form-select-sm">';
                        $valor_actual = $listado['codigo_seccion'] . $listado['codigo_turno'];
                        foreach($secciones_disponibles as $seccion){
                            $valor_option = $seccion['codigo_seccion'] . $seccion['codigo_turno'];
                            $selected = ($valor_option == $valor_actual) ? 'selected' : '';
                            $select_seccion_turno .= "<option value='{$valor_option}' {$selected}>{$seccion['nombre_seccion']} - {$seccion['nombre_turno']}</option>";
                        }
                        $select_seccion_turno .= '</select>';

                        // Crear los checkboxes
                        $chk_sobreedad = ($listado['sobreedad'] == 't') ? 'checked' : '';
                        $chk_repitente = ($listado['repitente'] == 't') ? 'checked' : '';
                        $chk_retirado = ($listado['retirado'] == 't') ? 'checked' : '';
                        $chk_nuevo_ingreso = ($listado['nuevo_ingreso'] == 't') ? 'checked' : '';
                        $chk_pn = ($listado['pn'] == 't') ? 'checked' : '';
                        $chk_certificado = ($listado['certificado'] == 't') ? 'checked' : '';
                        $chk_imprimir_foto = ($listado['imprimir_foto'] == 't') ? 'checked' : '';

						$contenidoOK .= '<tr data-id-matricula="' . $listado['id_alumno_matricula'] . '">
                            <td class="text-center">' . $num . '</td>
                            <td>' . $listado['codigo_nie'] . ' - ' . $listado['apellido_alumno'] . '</td>
                            <td>' . $select_seccion_turno . '</td>
                            <td class="text-center" ' . $style_sobreedad . '><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chksobreedad" ' . $chk_sobreedad . '></div></td>
                            <td class="text-center" ' . $style_repitente . '><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chkrepitente" ' . $chk_repitente . '></div></td>
                            <td class="text-center" ' . $style_retirado . '><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chkretirado" ' . $chk_retirado . '></div></td>
                            <td class="text-center" ' . $style_nuevo_ingreso . '><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chknuevoingreso" ' . $chk_nuevo_ingreso . '></div></td>
                            <td class="text-center"><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chkpn" ' . $chk_pn . '></div></td>
                            <td class="text-center"><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chkcertificado" ' . $chk_certificado . '></div></td>
                            <td class="text-center"><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="chkimprimirfoto" ' . $chk_imprimir_foto . '></div></td>
                        </tr>';
					}
                    
                    // Llenar el array de resumen
                    $resumenOK = [
                        'sobreedad' => $count_sobreedad,
                        'repitente' => $count_repitente,
                        'retirado' => $count_retirado,
                        'nuevo_ingreso' => $count_nuevo_ingreso,
                        'total' => $num
                    ];
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = false;
					$mensajeError =  'No se encontraron registros para este grupo.';
				}
			break;

			case 'ActualizarDatosMatricula':
                 $total_filas = $_POST['total_filas'];
                $codigos_matricula = $_POST['codigo_matricula'];
                $codigos_seccion_turno = $_POST['codigo_seccion_turno'];
                
                $dblink->beginTransaction();
                try {
                    for ($i = 0; $i < $total_filas; $i++) {
                        $id_matricula = $codigos_matricula[$i];
                        
                        // Extraer código de sección y turno
                        $codigo_seccion = substr($codigos_seccion_turno[$i], 0, 2);
                        $codigo_turno = substr($codigos_seccion_turno[$i], 2, 2);

                        // Convertir valores de checkbox de 'true'/'false' a 't'/'f' para la DB
                        $sobreedad = ($_POST['sobreedad'][$i] === 'true') ? 't' : 'f';
                        $repitente = ($_POST['repitente'][$i] === 'true') ? 't' : 'f';
                        $retirado = ($_POST['retirado'][$i] === 'true') ? 't' : 'f';
                        $nuevo_ingreso = ($_POST['nuevo_ingreso'][$i] === 'true') ? 't' : 'f';
                        $pn = ($_POST['pn'][$i] === 'true') ? 't' : 'f';
                        $certificado = ($_POST['certificado'][$i] === 'true') ? 't' : 'f';
                        $imprimir_foto = ($_POST['imprimir_foto'][$i] === 'true') ? 't' : 'f';
                        
                        $query_update = "UPDATE alumno_matricula SET
                            codigo_seccion = :codigo_seccion,
                            codigo_turno = :codigo_turno,
                            sobreedad = :sobreedad,
                            repitente = :repitente,
                            retirado = :retirado,
                            nuevo_ingreso = :nuevo_ingreso,
                            pn = :pn,
                            certificado = :certificado,
                            imprimir_foto = :imprimir_foto
                            WHERE id_alumno_matricula = :id_matricula";
                        
                        $stmt_update = $dblink->prepare($query_update);
                        $stmt_update->bindParam(':codigo_seccion', $codigo_seccion);
                        $stmt_update->bindParam(':codigo_turno', $codigo_turno);
                        $stmt_update->bindParam(':sobreedad', $sobreedad);
                        $stmt_update->bindParam(':repitente', $repitente);
                        $stmt_update->bindParam(':retirado', $retirado);
                        $stmt_update->bindParam(':nuevo_ingreso', $nuevo_ingreso);
                        $stmt_update->bindParam(':pn', $pn);
                        $stmt_update->bindParam(':certificado', $certificado);
                        $stmt_update->bindParam(':imprimir_foto', $imprimir_foto);
                        $stmt_update->bindParam(':id_matricula', $id_matricula);
                        $stmt_update->execute();
                    }
                    
                    $dblink->commit();
                    $respuestaOK = true;
                    $mensajeError = 'Registros actualizados correctamente.';

                } catch (Exception $e) {
                    $dblink->rollBack();
                    $respuestaOK = false;
                    $mensajeError = 'Error al actualizar: ' . $e->getMessage();
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
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK,
        "resumen" => $resumenOK); // Añadir el resumen a la respuesta JSON

echo json_encode($salidaJson);
?>
