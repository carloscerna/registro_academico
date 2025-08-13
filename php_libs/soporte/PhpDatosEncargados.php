<?php
// limpiar cache.
clearstatcache();
// cambiar a utf-8.
header("Content-Type: application/json;charset=utf-8");
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
include($path_root."/registro_academico/includes/funciones.php");

// Función para generar las opciones de un select
function generar_select_options($db, $tabla, $valor_seleccionado) {
    $options = '';
    $query = "SELECT codigo, descripcion FROM $tabla ORDER BY codigo";
    $stmt = $db->query($query);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $selected = ($row['codigo'] == $valor_seleccionado) ? 'selected' : '';
        $options .= "<option value='{$row['codigo']}' $selected>{$row['descripcion']}</option>";
    }
    return $options;
}


// Validar conexión con la base de datos
if($errorDbConexion == false){
	// Validamos que existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'BuscarLista':
				// Declarar Variables y Crear consulta Query.
                $codigo_annlectivo = $_POST["lstannlectivo"];
                $codigo_modalidad = $_POST["lstmodalidad"];
                
                // Extraer los códigos usando substr
                $grado_seccion_turno = $_POST["lstgradoseccion"];
                $codigo_grado = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno = substr($grado_seccion_turno, 4, 2);
						
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno
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
					
                    $options_genero = generar_select_options($dblink, 'catalogo_genero', '');
                    $options_familiar = generar_select_options($dblink, 'catalogo_familiar', '');

					while($listado = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$num++;
						$id_alumno = $listado['id_alumno'];
						
                        // Concatenar id_alumno, NIE y nombre en una sola celda.
                        $info_estudiante = "{$listado['id_alumno']} - {$listado['codigo_nie']} - {$listado['apellido_alumno']}";
                        $contenidoOK .= "<tr><td>$num</td><td>{$info_estudiante}</td>";

                        // Obtener los 3 encargados (Padre, Madre, Otro)
                        $query_encargados = "SELECT id_alumno_encargado, nombres, dui, encargado, telefono, fecha_nacimiento, codigo_genero, codigo_familiar 
                                             FROM alumno_encargado WHERE codigo_alumno = :id_alumno ORDER BY id_alumno_encargado LIMIT 3";
                        $stmt_encargados = $dblink->prepare($query_encargados);
                        $stmt_encargados->bindParam(':id_alumno', $id_alumno);
                        $stmt_encargados->execute();
                        $encargados = $stmt_encargados->fetchAll(PDO::FETCH_ASSOC);

                        $tipos = ['p', 'm', 'o'];
                        for ($i = 0; $i < 3; $i++) {
                            $enc = $encargados[$i] ?? null;
                            $tipo = $tipos[$i];

                            $id_encargado = $enc['id_alumno_encargado'] ?? '';
                            $nombres = $enc['nombres'] ?? '';
                            $dui = $enc['dui'] ?? '';
                            $telefono = $enc['telefono'] ?? '';
                            $fecha_nacimiento = isset($enc['fecha_nacimiento']) ? date('Y-m-d', strtotime($enc['fecha_nacimiento'])) : '';
                            $es_encargado = (isset($enc['encargado']) && $enc['encargado'] == 't') ? 'checked' : '';
                            
                            $genero_seleccionado = $enc['codigo_genero'] ?? '';
                            $familiar_seleccionado = $enc['codigo_familiar'] ?? '';

                            $options_genero_html = generar_select_options($dblink, 'catalogo_genero', $genero_seleccionado);
                            $options_familiar_html = generar_select_options($dblink, 'catalogo_familiar', $familiar_seleccionado);

                            $contenidoOK .= "<td>
                                <input type='hidden' name='id_{$tipo}[]' value='{$id_encargado}'>
                                <div class='encargado-grid'>
                                    <div class='form-check full-width'>
                                        <input class='form-check-input' type='radio' name='encargado_{$id_alumno}' value='{$id_encargado}' {$es_encargado}>
                                        <label class='form-check-label'>Responsable Principal</label>
                                    </div>
                                    <label>Nombre:</label><input type='text' name='nombres_{$tipo}[]' class='form-control form-control-sm' value='{$nombres}'>
                                    <label>DUI:</label><input type='text' name='dui_{$tipo}[]' class='form-control form-control-sm' value='{$dui}'>
                                    <label>Teléfono:</label><input type='text' name='telefono_{$tipo}[]' class='form-control form-control-sm' value='{$telefono}'>
                                    <label>F. Nac:</label><input type='date' name='fecha_nacimiento_{$tipo}[]' class='form-control form-control-sm' value='{$fecha_nacimiento}'>
                                    <label>Género:</label><select name='genero_{$tipo}[]' class='form-select form-select-sm'>{$options_genero_html}</select>
                                    <label>Parentesco:</label><select name='familiar_{$tipo}[]' class='form-select form-select-sm'>{$options_familiar_html}</select>
                                </div>
                            </td>";
                        }
                        $contenidoOK .= '</tr>';
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = false;
					$mensajeError =  'No se encontraron registros para este grupo.';
				}
			break;

			case 'ActualizarDatosEncargados':
                $total_filas = $_POST['total_filas'];
                $codigo_alumnos = $_POST['codigo_alumno'];

                $dblink->beginTransaction();
                try {
                    for ($i = 0; $i < $total_filas; $i++) {
                        // CORRECCIÓN: Extraer solo el ID del estudiante de la cadena de texto.
                        $info_completa_alumno = $codigo_alumnos[$i];
                        $partes = explode(' - ', $info_completa_alumno, 2);
                        $codigo_a = trim($partes[0]);
                        
                        // Procesar Padre, Madre y Otro
                        $tipos = ['p', 'm', 'o'];
                        foreach($tipos as $tipo){
                            $id_encargado = $_POST["id_$tipo"][$i];
                            $nombres = $_POST["nombres_$tipo"][$i];
                            $dui = $_POST["dui_$tipo"][$i];
                            $telefono = $_POST["telefono_$tipo"][$i];
                            $fecha_nacimiento = !empty($_POST["fecha_n_$tipo"][$i]) ? $_POST["fecha_n_$tipo"][$i] : null;
                            $codigo_genero = $_POST["genero_$tipo"][$i];
                            $codigo_familiar = $_POST["familiar_$tipo"][$i];
                            
                            // Determinar si es el encargado principal
                            $es_encargado = (isset($_POST["chkencargado_$tipo"][$i]) && $_POST["chkencargado_$tipo"][$i] == 'true') ? 't' : 'f';

                            if(!empty($id_encargado)){
                                $query_update = "UPDATE alumno_encargado SET 
                                                    nombres = :nombres, 
                                                    dui = :dui, 
                                                    telefono = :telefono, 
                                                    fecha_nacimiento = :fecha_nacimiento, 
                                                    codigo_genero = :codigo_genero, 
                                                    codigo_familiar = :codigo_familiar, 
                                                    encargado = :encargado
                                                WHERE id_alumno_encargado = :id_encargado AND codigo_alumno = :codigo_alumno";
                                
                                $stmt_update = $dblink->prepare($query_update);
                                $stmt_update->bindParam(':nombres', $nombres);
                                $stmt_update->bindParam(':dui', $dui);
                                $stmt_update->bindParam(':telefono', $telefono);
                                $stmt_update->bindParam(':fecha_nacimiento', $fecha_nacimiento);
                                $stmt_update->bindParam(':codigo_genero', $codigo_genero);
                                $stmt_update->bindParam(':codigo_familiar', $codigo_familiar);
                                $stmt_update->bindParam(':encargado', $es_encargado);
                                $stmt_update->bindParam(':id_encargado', $id_encargado);
                                $stmt_update->bindParam(':codigo_alumno', $codigo_a);
                                $stmt_update->execute();
                            }
                        }
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
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>
