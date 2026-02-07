<?php
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: application/json;charset=utf-8");
// sleep(0); // Opcional

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$resumenOK = []; // Array para los datos del resumen

// CORRECCIÓN PHP 8: Asegurar string antes de trim
$path_root = trim((string)$_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

// Función para generar las opciones del select de género
function generar_genero_options($db, $valor_seleccionado) {
    $options = '';
    $query = "SELECT codigo, descripcion FROM catalogo_genero ORDER BY codigo";
    // Usamos prepare/execute o query directo controlando errores
    if($db) {
        $stmt = $db->query($query);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Comparación estricta o asegurando tipos
            $selected = ($row['codigo'] == $valor_seleccionado) ? 'selected' : '';
            $options .= "<option value='{$row['codigo']}' $selected>{$row['descripcion']}</option>";
        }
    }
    return $options;
}

// Validar conexión con la base de datos
if($errorDbConexion == false){
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
        
        // CORRECCIÓN PHP 8: Validar existencia de clave 'accion'
        $accion = $_POST['accion'] ?? '';

		// Verificamos las variables de acción
		switch ($accion) {
			case 'BuscarListaPn':
				// Declarar Variables y Crear consulta Query.
                // CORRECCIÓN PHP 8: Operador ?? para evitar undefined keys
                $codigo_annlectivo = $_POST["lstannlectivo"] ?? '';
                $codigo_modalidad = $_POST["lstmodalidad"] ?? '';
                $grado_seccion_turno = $_POST["lstgradoseccion"] ?? '';
                
                // Validar longitud antes de substr
                if(strlen($grado_seccion_turno) >= 6) {
                    $codigo_grado = substr($grado_seccion_turno, 0, 2);
                    $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                    $codigo_turno = substr($grado_seccion_turno, 4, 2);
                } else {
                    $codigo_grado = ''; $codigo_seccion = ''; $codigo_turno = '';
                }
						
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
                        
                        // CORRECCIÓN PHP 8: trim((string)...) para evitar error con null
                        $genero_code = trim((string)$listado['codigo_genero']);
                        
                        // Contar para los resúmenes
                        if($genero_code == '01') { $resumen_genero['M']++; } else { $resumen_genero['F']++; }
                        
                        if($listado['estudio_parvularia'] == 't') { $resumen_parvularia['si']++; } else { $resumen_parvularia['no']++; }
                        
                        $edad = $listado['edad']; // Asumimos que edad viene calculado o es int/string
                        if(!isset($resumen_edades[$edad])) { $resumen_edades[$edad] = 0; }
                        $resumen_edades[$edad]++;

                        // Preparar campos para la tabla
                        $genero_select = generar_genero_options($dblink, $genero_code);
                        
                        // Validar fecha
                        $fecha_nacimiento = !empty($listado['fecha_nacimiento']) ? date('Y-m-d', strtotime($listado['fecha_nacimiento'])) : '';
                        
                        $chk_parvularia = ($listado['estudio_parvularia'] == 't') ? 'checked' : '';

                        // htmlspecialchars((string)...) es vital en PHP 8.1+
						$contenidoOK .= '<tr data-id-alumno="' . $listado['id_alumno'] . '">
                            <td class="text-center">' . $num . '</td>
                            <td>' . htmlspecialchars((string)$listado['apellido_alumno']) . '</td>
                            <td><input type="text" name="codigo_nie" class="form-control form-control-sm" value="' . htmlspecialchars((string)$listado['codigo_nie']) . '"></td>
                            <td><select name="codigo_genero" class="form-select form-select-sm">' . $genero_select . '</select></td>
                            <td><input type="date" name="fecha_nacimiento" class="form-control form-control-sm" value="' . $fecha_nacimiento . '"></td>
                            <td class="text-center">' . htmlspecialchars((string)$listado['edad']) . '</td>
                            <td><input type="text" name="numero_pn" class="form-control form-control-sm" value="' . htmlspecialchars((string)$listado['pn_numero']) . '"></td>
                            <td><input type="text" name="folio_pn" class="form-control form-control-sm" value="' . htmlspecialchars((string)$listado['pn_folio']) . '"></td>
                            <td><input type="text" name="tomo_pn" class="form-control form-control-sm" value="' . htmlspecialchars((string)$listado['pn_tomo']) . '"></td>
                            <td><input type="text" name="libro_pn" class="form-control form-control-sm" value="' . htmlspecialchars((string)$listado['pn_libro']) . '"></td>
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
                $total_filas = isset($_POST['total_filas']) ? (int)$_POST['total_filas'] : 0;
                $codigos_alumnos = $_POST['codigo_alumno'] ?? [];
                
                // Arrays de datos (uso de operador null coalescing para seguridad)
                $array_nies = $_POST['codigo_nie'] ?? [];
                $array_generos = $_POST['codigo_genero'] ?? [];
                $array_fechas = $_POST['fecha_nacimiento'] ?? [];
                $array_pn_num = $_POST['numero_pn'] ?? [];
                $array_pn_fol = $_POST['folio_pn'] ?? [];
                $array_pn_tom = $_POST['tomo_pn'] ?? [];
                $array_pn_lib = $_POST['libro_pn'] ?? [];
                $array_parvu = $_POST['estudio_parvularia'] ?? [];

                if ($total_filas > 0 && count($codigos_alumnos) > 0) {
                    $dblink->beginTransaction();
                    try {
                        for ($i = 0; $i < $total_filas; $i++) {
                            // Validar que exista el índice
                            if (!isset($codigos_alumnos[$i])) continue;

                            $id_alumno = $codigos_alumnos[$i];
                            
                            // Recoger datos del POST con seguridad de índice
                            $codigo_nie = $array_nies[$i] ?? '';
                            $codigo_genero = $array_generos[$i] ?? '';
                            if($codigo_genero == '01') {
                                $genero = 'm'; // Valor por defecto
                            }else{
                                $genero = 'f';
                            }
                            $fecha_nacimiento = !empty($array_fechas[$i]) ? $array_fechas[$i] : null;
                            
                            $numero_pn = $array_pn_num[$i] ?? '';
                            $folio_pn = $array_pn_fol[$i] ?? '';
                            $tomo_pn = $array_pn_tom[$i] ?? '';
                            $libro_pn = $array_pn_lib[$i] ?? '';
                            
                            // Manejo de checkbox/booleano enviado como string
                            $val_parvularia = $array_parvu[$i] ?? 'false';
                            $estudio_parvularia = ($val_parvularia === 'true') ? 't' : 'f';

                            // Calcular edad en el servidor
                            $edad = 0;
                            if($fecha_nacimiento){
                                try {
                                    $fecha_nac = new DateTime($fecha_nacimiento);
                                    $hoy = new DateTime();
                                    $edad = $hoy->diff($fecha_nac)->y;
                                } catch (Exception $e) {
                                    $edad = 0; // Fecha inválida
                                }
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
                                estudio_parvularia = :estudio_parvularia,
                                genero = :genero
                                WHERE id_alumno = :id_alumno";
                            
                            $stmt_update = $dblink->prepare($query_update);
                            $stmt_update->bindParam(':codigo_nie', $codigo_nie);
                            $stmt_update->bindParam(':codigo_genero', $codigo_genero);
                            // PDO maneja null correctamente para fechas
                            $stmt_update->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                            $stmt_update->bindParam(':edad', $edad, PDO::PARAM_INT);
                            $stmt_update->bindParam(':numero_pn', $numero_pn);
                            $stmt_update->bindParam(':folio_pn', $folio_pn);
                            $stmt_update->bindParam(':tomo_pn', $tomo_pn);
                            $stmt_update->bindParam(':libro_pn', $libro_pn);
                            $stmt_update->bindParam(':estudio_parvularia', $estudio_parvularia);
                            $stmt_update->bindParam(':id_alumno', $id_alumno);
                            $stmt_update->bindParam(':genero', $genero);
                            $stmt_update->execute();
                        }
                        
                        $dblink->commit();
                        $respuestaOK = true;
                        $mensajeError = 'Registros actualizados correctamente.';

                    } catch (Exception $e) {
                        // CORRECCIÓN PHP 8: Verificar transacción antes de rollback
                        if ($dblink->inTransaction()) {
                            $dblink->rollBack();
                        }
                        $respuestaOK = false;
                        $mensajeError = 'Error al actualizar: ' . $e->getMessage();
                    }
                } else {
                     $mensajeError = 'No se recibieron datos para actualizar.';
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