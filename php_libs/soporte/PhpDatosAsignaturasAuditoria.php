<?php
clearstatcache();
header("Content-Type: application/json;charset=utf-8");

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

if ($errorDbConexion == false) {
    if (isset($_POST['accion'])) {
        $accion = $_POST['accion'];
        
        // Parámetros de los filtros compartidos por el JS
        $ann_lectivo  = isset($_POST['lstannlectivo']) ? trim($_POST['lstannlectivo']) : '';
        $modalidad    = isset($_POST['lstmodalidad']) ? trim($_POST['lstmodalidad']) : '';
        $gradoseccion = isset($_POST['lstgradoseccion']) ? trim($_POST['lstgradoseccion']) : '';
        
        // Desglose del código compuesto: GGSSTT (Grado, Sección, Turno)
        $grado   = substr($gradoseccion, 0, 2);
        $seccion = substr($gradoseccion, 2, 2);
        $turno   = substr($gradoseccion, 4, 2);

        switch ($accion) {
            case 'BuscarLista':
                try {
                    // 1. Obtener la malla curricular teórica oficial para este grado
                    $q_malla = "SELECT DISTINCT RTRIM(codigo_asignatura) as asig 
                                FROM a_a_a_bach_o_ciclo 
                                WHERE codigo_ann_lectivo = :ann 
                                  AND codigo_bach_o_ciclo = :mod 
                                  AND codigo_grado = :grado";
                    $stmt_malla = $dblink->prepare($q_malla);
                    $stmt_malla->execute([':ann' => $ann_lectivo, ':mod' => $modalidad, ':grado' => $grado]);
                    $malla_oficial = $stmt_malla->fetchAll(PDO::FETCH_COLUMN);

                    if (empty($malla_oficial)) {
                        $respuestaOK = false;
                        $mensajeError = "No se ha configurado ninguna asignatura para este Plan de Estudios en a_a_a_bach_o_ciclo.";
                        break;
                    }

                    // 2. Obtener la nómina de estudiantes matriculados
                    $q_alumnos = "SELECT am.id_alumno_matricula, am.codigo_alumno,
                                         RTRIM(a.codigo_nie) as nie,
                                         RTRIM(a.apellido_paterno) || ' ' || RTRIM(a.apellido_materno) || ', ' || RTRIM(a.nombre_completo) as nombre_estudiante
                                  FROM alumno_matricula am
                                  INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
                                  WHERE am.codigo_ann_lectivo = :ann
                                    AND am.codigo_bach_o_ciclo = :mod
                                    AND am.codigo_grado = :grado
                                    AND am.codigo_seccion = :seccion
                                    AND am.codigo_turno = :turno
                                    AND am.retirado = false
                                  ORDER BY a.apellido_paterno, a.apellido_materno, a.nombre_completo";
                    
                    $stmt_alumnos = $dblink->prepare($q_alumnos);
                    $stmt_alumnos->execute([
                        ':ann' => $ann_lectivo, ':mod' => $modalidad, 
                        ':grado' => $grado, ':seccion' => $seccion, ':turno' => $turno
                    ]);
                    $estudiantes = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($estudiantes)) {
                        $respuestaOK = false;
                        $mensajeError = "No hay alumnos matriculados en la sección seleccionada.";
                        break;
                    }

                    // 3. Auditar la carga de cada estudiante uno por uno
                    $html = "";
                    $correlativo = 1;

                    foreach ($estudiantes as $est) {
                        // Obtener las materias reales que posee actualmente en la tabla 'nota'
                        $q_notas = "SELECT RTRIM(codigo_asignatura) FROM nota WHERE codigo_matricula = :mat";
                        $stmt_notas = $dblink->prepare($q_notas);
                        $stmt_notas->execute([':mat' => $est['id_alumno_matricula']]);
                        $materias_actuales = $stmt_notas->fetchAll(PDO::FETCH_COLUMN);

                        // Determinar discrepancias comparando arrays
                        $faltantes = array_diff($malla_oficial, $materias_actuales);
                        $sobrantes = array_diff($materias_actuales, $malla_oficial);

                        $total_oficial  = count($malla_oficial);
                        $total_estudiante = count($materias_actuales);

                        // Definir etiquetas de estado visuales
                        if (empty($faltantes) && empty($sobrantes)) {
                            $badge_status = '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Completo ('.$total_estudiante.' de '.$total_oficial.')</span>';
                        } else {
                            $badge_status = '<span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> Carga Incorrecta ('.$total_estudiante.' de '.$total_oficial.')</span>';
                        }

                        // Construcción de los textos descriptivos de lo que falta o sobra
                        $txt_detalles = "";
                        if (!empty($faltantes)) {
                            $txt_detalles .= "<div class='text-danger'><strong>Faltan (".count($faltantes)."):</strong> ".implode(', ', $faltantes)."</div>";
                        }
                        if (!empty($sobrantes)) {
                            $txt_detalles .= "<div class='text-warning'><strong>Sobran (".count($sobrantes)."):</strong> ".implode(', ', $sobrantes)."</div>";
                        }
                        if (empty($faltantes) && empty($sobrantes)) {
                            $txt_detalles = "<span class='text-muted font-italic'>Carga limpia y alineada al plan oficial</span>";
                        }

                    // Renderizamos las celdas de la tabla incluyendo el botón azul de detalle a la par del NIE
                        $html .= "<tr>
                            <td class='text-center align-middle'>{$correlativo}</td>
                            <td class='text-center align-middle'>
                                <div class='d-flex align-items-center justify-content-center'>
                                    <span class='mr-2'><strong>{$est['nie']}</strong></span>
                                    <button type='button' class='btn btn-info btn-sm btn-ver-detalle' 
                                            data-matricula='{$est['id_alumno_matricula']}' 
                                            data-nombre='{$est['nombre_estudiante']}'
                                            data-nie='{$est['nie']}'
                                            title='Ver asignaturas detalladas'>
                                        <i class='fas fa-eye'></i>
                                    </button>
                                </div>
                            </td>
                            <td class='align-middle'>{$est['nombre_estudiante']}</td>
                            <td class='text-center align-middle'>{$badge_status}</td>
                            <td class='align-middle' style='font-size:0.85rem;'>{$txt_detalles}</td>
                        </tr>";
                        
                        $correlativo++;
                    }

                    $contenidoOK = $html;
                    $respuestaOK = true;
                    $mensajeError = "Auditoría generada con éxito.";

                } catch (Exception $e) {
                    $respuestaOK = false;
                    $mensajeError = "Error en la consulta: " . $e->getMessage();
                }
            break;

            case 'ActualizarDatosAsignaturas':
                try {
                    $dblink->beginTransaction();

                    // 1. Re-obtener la malla teórica oficial
                    $q_malla = "SELECT DISTINCT RTRIM(codigo_asignatura) as asig FROM a_a_a_bach_o_ciclo WHERE codigo_ann_lectivo = :ann AND codigo_bach_o_ciclo = :mod AND codigo_grado = :grado";
                    $stmt_malla = $dblink->prepare($q_malla);
                    $stmt_malla->execute([':ann' => $ann_lectivo, ':mod' => $modalidad, ':grado' => $grado]);
                    $malla_oficial = $stmt_malla->fetchAll(PDO::FETCH_COLUMN);

                    // 2. Re-obtener los alumnos matriculados de la sección
                    $q_alumnos = "SELECT id_alumno_matricula, codigo_alumno FROM alumno_matricula WHERE codigo_ann_lectivo = :ann AND codigo_bach_o_ciclo = :mod AND codigo_grado = :grado AND codigo_seccion = :seccion AND codigo_turno = :turno AND retirado = false";
                    $stmt_alumnos = $dblink->prepare($q_alumnos);
                    $stmt_alumnos->execute([':ann' => $ann_lectivo, ':mod' => $modalidad, ':grado' => $grado, ':seccion' => $seccion, ':turno' => $turno]);
                    $estudiantes = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

                    $insertados = 0;
                    $eliminados = 0;

                    foreach ($estudiantes as $est) {
                        $q_notas = "SELECT RTRIM(codigo_asignatura) FROM nota WHERE codigo_matricula = :mat";
                        $stmt_notas = $dblink->prepare($q_notas);
                        $stmt_notas->execute([':mat' => $est['id_alumno_matricula']]);
                        $materias_actuales = $stmt_notas->fetchAll(PDO::FETCH_COLUMN);

                        $faltantes = array_diff($malla_oficial, $materias_actuales);
                        $sobrantes = array_diff($materias_actuales, $malla_oficial);

                        // ACCIÓN A: Insertar las asignaturas faltantes
                        if (!empty($faltantes)) {
                            $q_ins = "INSERT INTO nota (codigo_matricula, codigo_alumno, codigo_asignatura, nota_p_p_1, nota_p_p_2, nota_p_p_3, nota_p_p_4, nota_p_p_5, nota_final) 
                                      VALUES (:mat, :alumno, :asig, 0, 0, 0, 0, 0, 0)";
                            $stmt_ins = $dblink->prepare($q_ins);
                            foreach ($faltantes as $f_asig) {
                                $stmt_ins->execute([
                                    ':mat' => $est['id_alumno_matricula'],
                                    ':alumno' => $est['codigo_alumno'],
                                    ':asig' => $f_asig
                                ]);
                                $insertados++;
                            }
                        }

                        // ACCIÓN B: Eliminar las asignaturas sobrantes
                        if (!empty($sobrantes)) {
                            // Validamos que se eliminen SOLO si no poseen notas reales (ej. periodos en 0) por seguridad
                            $q_del = "DELETE FROM nota WHERE codigo_matricula = :mat AND codigo_asignatura = :asig AND nota_p_p_1 = 0 AND nota_p_p_2 = 0 AND nota_p_p_3 = 0";
                            $stmt_del = $dblink->prepare($q_del);
                            foreach ($sobrantes as $s_asig) {
                                $stmt_del->execute([
                                    ':mat' => $est['id_alumno_matricula'],
                                    ':asig' => $s_asig
                                ]);
                                $eliminados++;
                            }
                        }
                    }

                    $dblink->commit();
                    $respuestaOK = true;
                    $mensajeError = "Sincronización finalizada. Se crearon {$insertados} materias faltantes y se removieron {$eliminados} registros huérfanos/sobrantes.";

                } catch (Exception $e) {
                    $dblink->rollBack();
                    $respuestaOK = false;
                    $mensajeError = "Error en la base de datos al actualizar: " . $e->getMessage();
                }
            break;

            // =========================================================================
            // MODIFICACIÓN EN: case 'VerDetalleAlumno' -> Incluir Nombre de Asignatura
            // =========================================================================
            case 'VerDetalleAlumno':
                try {
                    $id_matricula = isset($_POST['id_matricula']) ? trim($_POST['id_matricula']) : '';

                    // 1. Obtener la info de la sección del alumno
                    $q_info = "SELECT codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_grado 
                            FROM alumno_matricula WHERE id_alumno_matricula = :mat";
                    $stmt_info = $dblink->prepare($q_info);
                    $stmt_info->execute([':mat' => $id_matricula]);
                    $info = $stmt_info->fetch(PDO::FETCH_ASSOC);

                    // CORREGIDO: Se agrega ab.orden al SELECT para satisfacer el estándar estricto de PostgreSQL para DISTINCT
                    $q_malla = "SELECT DISTINCT RTRIM(a.codigo) as asig, RTRIM(a.nombre) as nombre_asignatura, ab.orden
                                FROM a_a_a_bach_o_ciclo ab
                                INNER JOIN asignatura a ON RTRIM(a.codigo) = RTRIM(ab.codigo_asignatura)
                                WHERE ab.codigo_ann_lectivo = :ann 
                                AND ab.codigo_bach_o_ciclo = :mod 
                                AND ab.codigo_grado = :grado
                                ORDER BY ab.orden, nombre_asignatura";
                    $stmt_malla = $dblink->prepare($q_malla);
                    $stmt_malla->execute([':ann' => $info['codigo_ann_lectivo'], ':mod' => $info['codigo_bach_o_ciclo'], ':grado' => $info['codigo_grado']]);
                    $malla_oficial = $stmt_malla->fetchAll(PDO::FETCH_ASSOC);

                    // Extraemos solo los códigos para las comparaciones de array más veloces
                    $codigos_malla = array_column($malla_oficial, 'asig');

                    // 2. Obtener las materias reales que posee en 'nota' con sus nombres oficiales
                    $q_notas = "SELECT RTRIM(n.codigo_asignatura) as asig, RTRIM(a.nombre) as nombre_asignatura
                                FROM nota n
                                LEFT JOIN asignatura a ON RTRIM(a.codigo) = RTRIM(n.codigo_asignatura)
                                WHERE n.codigo_matricula = :mat
                                ORDER BY n.codigo_asignatura";
                    $stmt_notas = $dblink->prepare($q_notas);
                    $stmt_notas->execute([':mat' => $id_matricula]);
                    $materias_actuales = $stmt_notas->fetchAll(PDO::FETCH_ASSOC);
                    
                    $codigos_actuales = array_column($materias_actuales, 'asig');

                    // 3. Construcción del HTML Comparativo para el modal
                        $html_modal = "<div class='row'>
                            <div class='col-md-6'>
                                <h6 class='font-weight-bold text-primary'><i class='fas fa-graduation-cap'></i> Plan Oficial Teórico (".count($malla_oficial).")</h6>
                                <ul class='list-group' style='max-height: 400px; overflow-y: auto;'>";
                                foreach ($malla_oficial as $m) {
                                    $existe = in_array($m['asig'], $codigos_actuales);
                                    $clase_check = $existe ? 'list-group-item-success' : 'list-group-item-danger';
                                    $icono = $existe ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-times-circle text-danger"></i> (Falta)';
                                    $html_modal .= "<li class='list-group-item d-flex justify-content-between align-items-center p-2 {$clase_check}' style='font-size:0.8rem;'>
                                        <span><strong>{$m['asig']}</strong> - {$m['nombre_asignatura']}</span>
                                        <span class='ml-2'>{$icono}</span>
                                    </li>";
                                }
                        $html_modal .= "</ul></div>
                            <div class='col-md-6'>
                                <h6 class='font-weight-bold text-success'><i class='fas fa-folder-open'></i> Carga Real en Base Datos (".count($materias_actuales).")</h6>
                                <ul class='list-group' style='max-height: 400px; overflow-y: auto;'>";
                                foreach ($materias_actuales as $n) {
                                    $es_valida = in_array($n['asig'], $codigos_malla);
                                    $clase_nota = $es_valida ? '' : 'list-group-item-warning font-weight-bold';
                                    
                                    // MODIFICADO: Si la materia sobra, le metemos un botón de eliminación manual directo
                                    if($es_valida) {
                                        $badge_accion = '<span class="badge badge-secondary">Correcta</span>';
                                    } else {
                                        $badge_accion = "<button type='button' class='btn btn-danger btn-xs btn-eliminar-materia-manual' 
                                                            data-matricula='{$id_matricula}' 
                                                            data-asignatura='{$n['asig']}' 
                                                            title='Forzar eliminación de esta materia huerfana'>
                                                            <i class='fas fa-trash-alt'></i> Quitar
                                                        </button>";
                                    }

                                    $html_modal .= "<li class='list-group-item d-flex justify-content-between align-items-center p-2 {$clase_nota}' style='font-size:0.8rem;'>
                                        <span><strong>".($n['asig'] ?: 'S/C')."</strong> - ".($n['nombre_asignatura'] ?? 'Asignatura Desconocida / Código Corrupto')."</span>
                                        <span class='ml-2'>{$badge_accion}</span>
                                    </li>";
                                }
                        $html_modal .= "</ul></div></div>";

                        $contenidoOK = $html_modal;
                        $respuestaOK = true;
                        $mensajeError = "";
                    } catch (Exception $e) {
                        $respuestaOK = false;
                        $mensajeError = "Error al extraer el desglose: " . $e->getMessage();
                    }
                break;

            // =========================================================================
            // NUEVO CASE OPTIMIZADO: `ObtenerMatriculasSeccion` (Para armar la cola en JS)
            // =========================================================================
            case 'ObtenerMatriculasSeccion':
                try {
                    $q_alumnos = "SELECT id_alumno_matricula, codigo_alumno FROM alumno_matricula WHERE codigo_ann_lectivo = :ann AND codigo_bach_o_ciclo = :mod AND codigo_grado = :grado AND codigo_seccion = :seccion AND codigo_turno = :turno AND retirado = false";
                    $stmt_alumnos = $dblink->prepare($q_alumnos);
                    $stmt_alumnos->execute([':ann' => $ann_lectivo, ':mod' => $modalidad, ':grado' => $grado, ':seccion' => $seccion, ':turno' => $turno]);
                    $estudiantes = $stmt_alumnos->fetchAll(PDO::FETCH_ASSOC);

                    $respuestaOK = true;
                    $contenidoOK = $estudiantes; // Retornamos el array crudo al JS
                } catch (Exception $e) {
                    $respuestaOK = false;
                    $mensajeError = $e->getMessage();
                }
            break;

            // =========================================================================
            // NUEVO CASE OPTIMIZADO: `SincronizarUnEstudiante` (Llamado unitario progresivo)
            // =========================================================================
            case 'SincronizarUnEstudiante':
                try {
                    $id_mat = $_POST['id_alumno_matricula'];
                    $cod_al = $_POST['codigo_alumno'];

                    // Obtener malla oficial teórica
                    $q_malla = "SELECT DISTINCT RTRIM(codigo_asignatura) as asig FROM a_a_a_bach_o_ciclo WHERE codigo_ann_lectivo = :ann AND codigo_bach_o_ciclo = :mod AND codigo_grado = :grado";
                    $stmt_malla = $dblink->prepare($q_malla);
                    $stmt_malla->execute([':ann' => $ann_lectivo, ':mod' => $modalidad, ':grado' => $grado]);
                    $malla_oficial = $stmt_malla->fetchAll(PDO::FETCH_COLUMN);

                    // Obtener lo que tiene en notas
                    $q_notas = "SELECT RTRIM(codigo_asignatura) FROM nota WHERE codigo_matricula = :mat";
                    $stmt_notas = $dblink->prepare($q_notas);
                    $stmt_notas->execute([':mat' => $id_mat]);
                    $materias_actuales = $stmt_notas->fetchAll(PDO::FETCH_COLUMN);

                    $faltantes = array_diff($malla_oficial, $materias_actuales);
                    $sobrantes = array_diff($materias_actuales, $malla_oficial);

                    $dblink->beginTransaction();
                    
                    // Inserta faltantes
                    if (!empty($faltantes)) {
                        $q_ins = "INSERT INTO nota (codigo_matricula, codigo_alumno, codigo_asignatura, nota_p_p_1, nota_p_p_2, nota_p_p_3, nota_p_p_4, nota_p_p_5, nota_final) VALUES (:mat, :alumno, :asig, 0, 0, 0, 0, 0, 0)";
                        $stmt_ins = $dblink->prepare($q_ins);
                        foreach ($faltantes as $f_asig) {
                            $stmt_ins->execute([':mat' => $id_mat, ':alumno' => $cod_al, ':asig' => $f_asig]);
                        }
                    }

                    // Remueve sobrantes limpios
                    if (!empty($sobrantes)) {
                        $q_del = "DELETE FROM nota WHERE codigo_matricula = :mat AND codigo_asignatura = :asig AND nota_p_p_1 = 0 AND nota_p_p_2 = 0 AND nota_p_p_3 = 0";
                        $stmt_del = $dblink->prepare($q_del);
                        foreach ($sobrantes as $s_asig) {
                            $stmt_del->execute([':mat' => $id_mat, ':asig' => $s_asig]);
                        }
                    }

                    $dblink->commit();
                    $respuestaOK = true;
                } catch (Exception $e) {
                    if($dblink->inTransaction()) $dblink->rollBack();
                    $respuestaOK = false;
                    $mensajeError = $e->getMessage();
                }
            break;

            // =========================================================================
            // NUEVO CASE: `EliminarAsignaturaIndividual` (Para remover registros rebeldes)
            // =========================================================================
            case 'EliminarAsignaturaIndividual':
                try {
                    $id_mat = isset($_POST['id_matricula']) ? trim($_POST['id_matricula']) : '';
                    $cod_asig = isset($_POST['codigo_asignatura']) ? trim($_POST['codigo_asignatura']) : '';

                    // Si el código viene vacío (así como el que viste en la imagen), lo buscamos de forma segura
                    if(empty($cod_asig) || $cod_asig == 'S/C') {
                        $q_del = "DELETE FROM nota WHERE codigo_matricula = :mat AND (codigo_asignatura IS NULL OR codigo_asignatura = '' OR codigo_asignatura = 'S/C')";
                        $stmt_del = $dblink->prepare($q_del);
                        $stmt_del->execute([':mat' => $id_mat]);
                    } else {
                        $q_del = "DELETE FROM nota WHERE codigo_matricula = :mat AND codigo_asignatura = :asig";
                        $stmt_del = $dblink->prepare($q_del);
                        $stmt_del->execute([':mat' => $id_mat, ':asig' => $cod_asig]);
                    }

                    $respuestaOK = true;
                    $mensajeError = "Asignatura removida correctamente del expediente del alumno.";
                } catch (Exception $e) {
                    $respuestaOK = false;
                    $mensajeError = "No se pudo eliminar la asignatura: " . $e->getMessage();
                }
            break;
        }
    }
}

$salidaJson = array("respuesta" => $respuestaOK, "mensaje" => $mensajeError, "contenido" => $contenidoOK);
echo json_encode($salidaJson);
?>