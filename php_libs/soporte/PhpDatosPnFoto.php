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
$filepath = ""; // Para devolver la ruta del archivo subido

// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

// Validar conexión con la base de datos
if($errorDbConexion == false){
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'BuscarListaPnFoto':
				// Declarar Variables y Crear consulta Query.
                $codigo_annlectivo = $_POST["lstannlectivo"];
                $codigo_modalidad = $_POST["lstmodalidad"];
                
                $grado_seccion_turno = $_POST["lstgradoseccion"];
                $codigo_grado = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno = substr($grado_seccion_turno, 4, 2);
						
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                            a.foto, a.ruta_pn, a.codigo_genero
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
					while($listado = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$num++;
                        $id_alumno = $listado['id_alumno'];
                        
                        // Lógica para la imagen de la Partida de Nacimiento
                        $pn_path_server = $path_root . '/registro_academico/img/Pn/10391/' . trim($listado['ruta_pn']);
                        $pn_path_web = 'img/Pn/10391/' . trim($listado['ruta_pn']);
                        if (!empty(trim($listado['ruta_pn'])) && file_exists($pn_path_server)) {
                            $pn_img = "<a href='{$pn_path_web}' target='_blank'><img src='img/pdf_icon.png' class='pn-preview' alt='Ver PDF'></a>";
                        } else {
                            $pn_img = "<img src='img/NoDisponible.jpg' class='pn-preview' alt='No disponible'>";
                        }

                        // Lógica para la foto del estudiante
                        $foto_path_server = $path_root . '/registro_academico/img/fotos/10391/' . $listado['foto'];
                        $foto_path_web = 'img/fotos/10391/' . $listado['foto'];
                        if (!empty($listado['foto']) && file_exists($foto_path_server)) {
                            $foto_img = "<img id='foto-{$id_alumno}' src='{$foto_path_web}' class='student-photo' alt='Foto'>";
                        } else {
                            $avatar = (trim($listado['codigo_genero']) == '01') ? 'img/avatar_masculino.png' : 'img/avatar_femenino.png';
                            $foto_img = "<img id='foto-{$id_alumno}' src='{$avatar}' class='student-photo' alt='Avatar'>";
                        }

						$contenidoOK .= '<tr>
                            <td class="text-center">' . $num . '</td>
                            <td>' . $id_alumno . " - " . $listado['codigo_nie'] . ' - ' . $listado['apellido_alumno'] . '</td>
                            <td class="text-center">
                                ' . $pn_img . '
                                <button class="btn btn-sm btn-outline-primary upload-btn" data-id-alumno="' . $id_alumno . '" data-type="pn">Subir</button>
                            </td>
                            <td class="text-center">
                                ' . $foto_img . '
                                <button class="btn btn-sm btn-outline-secondary upload-btn" data-id-alumno="' . $id_alumno . '" data-type="foto">Subir</button>
                            </td>
                        </tr>';
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = false;
					$mensajeError =  'No se encontraron registros para este grupo.';
				}
			break;

			case 'SubirArchivo':
                $alumno_id = $_POST['alumno_id'] ?? 0;
                $upload_type = $_POST['upload_type'] ?? '';

                if (empty($alumno_id) || empty($upload_type) || !isset($_FILES['file_to_upload'])) {
                    $mensajeError = "Faltan datos para subir el archivo.";
                    break;
                }

                $file = $_FILES['file_to_upload'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $mensajeError = "Error al subir el archivo: " . $file['error'];
                    break;
                }

                $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_filename = "alumno_" . $alumno_id . "_" . time() . "." . $file_ext;

                if ($upload_type === 'foto') {
                    $target_dir = $path_root . "/registro_academico/img/fotos/10391/";
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    $db_column = 'foto';
                } else { // 'pn'
                    $target_dir = $path_root . "/registro_academico/img/Pn/10391/";
                    $allowed_exts = ['pdf'];
                    $db_column = 'ruta_pn';
                }

                if (!in_array($file_ext, $allowed_exts)) {
                    $mensajeError = "Tipo de archivo no permitido. Permitidos: " . implode(', ', $allowed_exts);
                    break;
                }

                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
                    // Actualizar la base de datos
                    $query_update = "UPDATE alumno SET {$db_column} = :filename WHERE id_alumno = :id_alumno";
                    $stmt_update = $dblink->prepare($query_update);
                    $stmt_update->bindParam(':filename', $new_filename);
                    $stmt_update->bindParam(':id_alumno', $alumno_id);
                    
                    if($stmt_update->execute()){
                        $respuestaOK = true;
                        $mensajeError = "Archivo subido exitosamente.";
                        $filepath = str_replace($path_root, '', $target_dir . $new_filename);
                    } else {
                        $mensajeError = "El archivo se subió, pero no se pudo actualizar la base de datos.";
                    }
                } else {
                    $mensajeError = "Error al mover el archivo subido.";
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
        "filepath" => $filepath);

echo json_encode($salidaJson);
?>
