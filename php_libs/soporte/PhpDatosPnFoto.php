<?php
// limpiar cache
clearstatcache();
header("Content-Type: application/json;charset=utf-8");

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$data = [];
$filepath = "";

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
    
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
include($path_root . "/registro_academico/includes/funciones.php");

if ($errorDbConexion === false) {
    if (isset($_POST) && !empty($_POST)) {
        if (!empty($_POST['accion_buscar'])) {
            $_POST['accion'] = $_POST['accion_buscar'];
        }

        switch ($_POST['accion']) {
            case 'BuscarListaPnFoto':
                $codigo_annlectivo = $_POST["lstannlectivo"] ?? '';
                $codigo_modalidad  = $_POST["lstmodalidad"] ?? '';
                $grado_seccion_turno = $_POST["lstgradoseccion"] ?? '';

                $codigo_grado   = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno   = substr($grado_seccion_turno, 4, 2);
						
                $query = "SELECT a.id_alumno, a.codigo_nie, 
                                 btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) AS apellido_alumno,
                                 a.foto, a.ruta_pn, a.codigo_genero
                          FROM alumno a
                          INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                          WHERE am.codigo_bach_o_ciclo = :modalidad 
                            AND am.codigo_grado = :grado 
                            AND am.codigo_seccion = :seccion 
                            AND am.codigo_ann_lectivo = :annlectivo 
                            AND am.codigo_turno = :turno
                          ORDER BY apellido_alumno ASC";
				
                $stmt = $dblink->prepare($query);
                $stmt->bindParam(':modalidad', $codigo_modalidad);
                $stmt->bindParam(':grado', $codigo_grado);
                $stmt->bindParam(':seccion', $codigo_seccion);
                $stmt->bindParam(':annlectivo', $codigo_annlectivo);
                $stmt->bindParam(':turno', $codigo_turno);
                $stmt->execute();

                $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (count($alumnos) > 0) {
                    $respuestaOK = true;
                    $mensajeError = "Registros encontrados";

                    foreach ($alumnos as $listado) {
                        $id_alumno = $listado['id_alumno'];
                        $ruta_pn   = trim($listado['ruta_pn'] ?? '');
                        $foto      = trim($listado['foto'] ?? '');
                        $genero    = trim($listado['codigo_genero'] ?? '');

                        // Lógica Partida de Nacimiento (PDF e Imágenes)
                        $pn_info = [
                            'existe' => false,
                            'tipo'   => 'ninguno',
                            'url'    => ''
                        ];

                        if (!empty($ruta_pn)) {
                            $pn_server = $path_root . '/registro_academico/img/Pn/10391/' . $ruta_pn;
                            if (file_exists($pn_server)) {
                                $ext = strtolower(pathinfo($ruta_pn, PATHINFO_EXTENSION));
                                $pn_info['existe'] = true;
                                $pn_info['url'] = 'img/Pn/10391/' . $ruta_pn;
                                $pn_info['tipo'] = ($ext === 'pdf') ? 'pdf' : 'imagen';
                            }
                        }

                        // Lógica Foto del Estudiante (solo imágenes)
                        $foto_info = [
                            'existe' => false,
                            'url'    => ''
                        ];

                        if (!empty($foto)) {
                            $foto_server = $path_root . '/registro_academico/img/fotos/10391/' . $foto;
                            if (file_exists($foto_server)) {
                                $foto_info['existe'] = true;
                                $foto_info['url'] = 'img/fotos/10391/' . $foto;
                            }
                        }

                        if (!$foto_info['existe']) {
                            $foto_info['url'] = ($genero === '01') 
                                ? 'img/avatar_masculino.png' 
                                : 'img/avatar_femenino.png';
                        }

                        $data[] = [
                            'id_alumno'       => $id_alumno,
                            'nie'             => $listado['codigo_nie'],
                            'apellido_alumno' => $listado['apellido_alumno'],
                            'pn_info'         => $pn_info,
                            'foto_info'       => $foto_info
                        ];
                    }
                } else {
                    $respuestaOK = false;
                    $mensajeError = 'No se encontraron registros para este grupo.';
                }
            break;

            case 'SubirArchivo':
                $alumno_id   = $_POST['alumno_id'] ?? 0;
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

                $file_ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $new_filename = "alumno_" . $alumno_id . "_" . time() . "." . $file_ext;

                if ($upload_type === 'foto') {
                    $target_dir   = $path_root . "/registro_academico/img/fotos/10391/";
                    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                    $db_column    = 'foto';
                } else { // 'pn' -> Soporta PDF e Imágenes
                    $target_dir   = $path_root . "/registro_academico/img/Pn/10391/";
                    $allowed_exts = ['pdf', 'jpg', 'jpeg', 'png'];
                    $db_column    = 'ruta_pn';
                }

                if (!in_array($file_ext, $allowed_exts)) {
                    $mensajeError = "Tipo de archivo no permitido. Permitidos: " . implode(', ', $allowed_exts);
                    break;
                }

                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                if (move_uploaded_file($file['tmp_name'], $target_dir . $new_filename)) {
                    $query_update = "UPDATE alumno SET {$db_column} = :filename WHERE id_alumno = :id_alumno";
                    $stmt_update  = $dblink->prepare($query_update);
                    $stmt_update->bindParam(':filename', $new_filename);
                    $stmt_update->bindParam(':id_alumno', $alumno_id);
                    
                    if ($stmt_update->execute()) {
                        $respuestaOK  = true;
                        $mensajeError = "Archivo subido exitosamente.";
                        $filepath     = str_replace($path_root . '/registro_academico/', '', $target_dir . $new_filename);
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
} else {
    $mensajeError = 'No se puede establecer conexión con la base de datos';
}

echo json_encode([
    "respuesta" => $respuestaOK,
    "mensaje"   => $mensajeError,
    "data"      => $data,
    "filepath"  => $filepath
]);
?>