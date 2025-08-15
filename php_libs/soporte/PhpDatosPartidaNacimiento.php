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

// Función para generar las opciones del select de género
function generar_genero_options($db, $valor_seleccionado) {
    $options = '';
    $query = "SELECT codigo, descripcion FROM catalogo_genero ORDER BY codigo";
    $stmt = $db->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($row['codigo'] == $valor_seleccionado) ? 'selected' : '';
        $options .= "<option value='{$row['codigo']}' $selected>{$row['descripcion']}</option>";
    }
    return $options;
}

// Validar conexión con la base de datos
if($errorDbConexion == false){
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'BuscarListaPn':
				// Declarar Variables y Crear consulta Query.
                $codigo_annlectivo = $_POST["lstannlectivo"];
                $codigo_modalidad = $_POST["lstmodalidad"];
                
                $grado_seccion_turno = $_POST["lstgradoseccion"];
                $codigo_grado = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno = substr($grado_seccion_turno, 4, 2);
						
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                            a.codigo_genero, a.fecha_nacimiento, a.edad, a.pn_numero, a.pn_folio, a.pn_tomo, a.pn_libro, a.estudio_parvularia
							FROM alumno a
							INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
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
                    $resumen_genero = ['M' => 0, 'F' => 0];
                    $resumen_parvularia = ['si' => 0, 'no' => 0];
                    $resumen_edades = [];

					while($listado = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$num++;
                        
                        // Contar para los resúmenes
                        if(trim($listado['codigo_genero']) == '01') { $resumen_genero['M']++; } else { $resumen_genero['F']++; }
                        if($listado['estudio_parvularia'] == 't') { $resumen_parvularia['si']++; } else { $resumen_parvularia['no']++; }
                        $edad = $listado['edad'];
                        if(!isset($resumen_edades[$edad])) { $resumen_edades[$edad] = 0; }
                        $resumen_edades[$edad]++;

                        // Preparar campos para la tabla
                        $genero_select = generar_genero_options($dblink, trim($listado['codigo_genero']));
                        $fecha_nacimiento = isset($listado['fecha_nacimiento']) ? date('Y-m-d', strtotime($listado['fecha_nacimiento'])) : '';
                        $chk_parvularia = ($listado['estudio_parvularia'] == 't') ? 'checked' : '';

						$contenidoOK .= '<tr data-id-alumno="' . $listado['id_alumno'] . '">
                            <td class="text-center">' . $num . '</td>
                            <td>' . $listado['apellido_alumno'] . '</td>
                            <td><input type="text" name="codigo_nie" class="form-control form-control-sm" value="' . htmlspecialchars($listado['codigo_nie']) . '"></td>
                            <td><select name="codigo_genero" class="form-select form-select-sm">' . $genero_select . '</select></td>
                            <td><input type="date" name="fecha_nacimiento" class="form-control form-control-sm" value="' . $fecha_nacimiento . '"></td>
                            <td class="text-center">' . $listado['edad'] . '</td>
                            <td><input type="text" name="numero_pn" class="form-control form-control-sm" value="' . htmlspecialchars($listado['pn_numero']) . '"></td>
                            <td><input type="text" name="folio_pn" class="form-control form-control-sm" value="' . htmlspecialchars($listado['pn_folio']) . '"></td>
                            <td><input type="text" name="tomo_pn" class="form-control form-control-sm" value="' . htmlspecialchars($listado['pn_tomo']) . '"></td>
                            <td><input type="text" name="libro_pn" class="form-control form-control-sm" value="' . htmlspecialchars($listado['pn_libro']) . '"></td>
                            <td class="text-center"><div class="form-check d-flex justify-content-center"><input class="form-check-input" type="checkbox" name="estudio_parvularia" ' . $chk_parvularia . '></div></td>
                        </tr>';
					}
                    
                    ksort($resumen_edades); // Ordenar el resumen de edades
                    $resumenOK = [
                        'genero' => $resumen_genero,
                        'parvularia' => $resumen_parvularia,
                        'edades' => $resumen_edades
                    ];
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = false;
					$mensajeError =  'No se encontraron registros para este grupo.';
				}
			break;

			case 'ActualizarDatosPn':
                $total_filas = $_POST['total_filas'];
                $codigos_alumnos = $_POST['codigo_alumno'];
                
                $dblink->beginTransaction();
                try {
                    for ($i = 0; $i < $total_filas; $i++) {
                        $id_alumno = $codigos_alumnos[$i];
                        
                        // Recoger datos del POST
                        $codigo_nie = $_POST['codigo_nie'][$i];
                        $codigo_genero = $_POST['codigo_genero'][$i];
                        $fecha_nacimiento = !empty($_POST['fecha_nacimiento'][$i]) ? $_POST['fecha_nacimiento'][$i] : null;
                        $numero_pn = $_POST['numero_pn'][$i];
                        $folio_pn = $_POST['folio_pn'][$i];
                        $tomo_pn = $_POST['tomo_pn'][$i];
                        $libro_pn = $_POST['libro_pn'][$i];
                        $estudio_parvularia = ($_POST['estudio_parvularia'][$i] === 'true') ? 't' : 'f';

                        // Calcular edad en el servidor
                        $edad = 0;
                        if($fecha_nacimiento){
                            $fecha_nac = new DateTime($fecha_nacimiento);
                            $hoy = new DateTime();
                            $edad = $hoy->diff($fecha_nac)->y;
                        }
                        
                        $query_update = "UPDATE alumno SET
                            codigo_nie = :codigo_nie,
                            codigo_genero = :codigo_genero,
                            fecha_nacimiento = :fecha_nacimiento,
                            edad = :edad,
                            pn_numero = :numero_pn,
                            pn_folio = :folio_pn,
                            pn_tomo = :tomo_pn,
                            pn_libro = :libro_pn,
                            estudio_parvularia = :estudio_parvularia
                            WHERE id_alumno = :id_alumno";
                        
                        $stmt_update = $dblink->prepare($query_update);
                        $stmt_update->bindParam(':codigo_nie', $codigo_nie);
                        $stmt_update->bindParam(':codigo_genero', $codigo_genero);
                        $stmt_update->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                        $stmt_update->bindParam(':edad', $edad, PDO::PARAM_INT);
                        $stmt_update->bindParam(':numero_pn', $numero_pn);
                        $stmt_update->bindParam(':folio_pn', $folio_pn);
                        $stmt_update->bindParam(':tomo_pn', $tomo_pn);
                        $stmt_update->bindParam(':libro_pn', $libro_pn);
                        $stmt_update->bindParam(':estudio_parvularia', $estudio_parvularia);
                        $stmt_update->bindParam(':id_alumno', $id_alumno);
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
        "resumen" => $resumenOK);

echo json_encode($salidaJson);
?>
