<?php
// Configuración de encabezados para respuesta JSON/UTF-8
header("Content-Type: application/json; charset=utf-8");

// Definición de rutas e inclusión de dependencias
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Al incluir mainFunctions_conexion.php, este archivo se encarga de iniciar la sesión (session_start)
include_once($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
include_once($path_root . "/registro_academico/includes/funciones.php");

// Verificación de seguridad: asegurar que la sesión esté iniciada si por alguna razón no lo hizo el include
if (session_status() === PHP_SESSION_NONE) {
    session_name('demoUI');
    session_start();
}

// Inicialización de variables de respuesta
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$encabezado = "";
$Accion = "";
$datos = array();

// Validar que la conexión a la base de datos esté activa
if (isset($errorDbConexion) && $errorDbConexion === false) {

    // Validar recepción de datos vía POST / REQUEST
    if (!empty($_REQUEST) && !empty($_POST['accion'])) {
        $Accion = $_POST['accion'];

        switch ($Accion) {

            case 'BuscarContratacion':
                $codigo_personal = isset($_POST['codigo_personal']) ? (int)$_POST['codigo_personal'] : 0;

                try {
                    if (isset($_SESSION['codigo_perfil']) && $_SESSION['codigo_perfil'] == '03') {
                        // Obtener el turno de la persona si es Director/Subdirector
                        $stmtTurno = $dblink->prepare("SELECT codigo_turno FROM personal_responsable_licencia WHERE codigo_personal = :cod_personal");
                        $stmtTurno->execute([':cod_personal' => $codigo_personal]);
                        $codigo_turno = $stmtTurno->fetchColumn() ?: '';

                        $query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario, ps.codigo_turno,
                                                    cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro,
                                                    tur.codigo as codigo_turno, tur.nombre as nombre_turno, cat_h.inicio as horario_inicio, cat_h.fin as horario_fin
                                            FROM personal_salario ps
                                            INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
                                            INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
                                            INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
                                            INNER JOIN turno tur ON tur.codigo = ps.codigo_turno
                                            INNER JOIN catalogo_horario cat_h ON cat_h.codigo = ps.codigo_horario
                                            WHERE ps.codigo_personal = :cod_personal AND ps.codigo_turno = :cod_turno 
                                            ORDER BY ps.codigo_personal";
                        $stmtPersonal = $dblink->prepare($query_personal);
                        $stmtPersonal->execute([':cod_personal' => $codigo_personal, ':cod_turno' => $codigo_turno]);
                    } else {
                        $query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario, ps.codigo_turno,
                                                    cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro,
                                                    tur.codigo as codigo_turno, tur.nombre as nombre_turno, cat_h.inicio as horario_inicio, cat_h.fin as horario_fin
                                            FROM personal_salario ps
                                            INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
                                            INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
                                            INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
                                            INNER JOIN turno tur ON tur.codigo = ps.codigo_turno
                                            INNER JOIN catalogo_horario cat_h ON cat_h.codigo = ps.codigo_horario
                                            WHERE ps.codigo_personal = :cod_personal 
                                            ORDER BY ps.codigo_personal";
                        $stmtPersonal = $dblink->prepare($query_personal);
                        $stmtPersonal->execute([':cod_personal' => $codigo_personal]);
                    }

                    $fila_array = 0;
                    if ($stmtPersonal->rowCount() > 0) {
                        while ($row = $stmtPersonal->fetch(PDO::FETCH_ASSOC)) {
                            $datos[$fila_array] = array(
                                "id_personal_salario"     => trim($row['id_personal_salario']),
                                "codigo_personal"         => trim($row['codigo_personal']),
                                "codigo_rubro"            => trim($row['codigo_rubro']),
                                "codigo_tipo_descuento"   => trim($row['codigo_tipo_descuento']),
                                "codigo_tipo_contratacion"=> trim($row['codigo_tipo_contratacion']),
                                "nombre_contratacion"     => trim($row['nombre_contratacion']),
                                "codigo_turno"            => trim($row['codigo_turno']),
                                "nombre_turno"            => trim($row['nombre_turno']),
                                "salario"                 => trim($row['salario']),
                                "horario_inicio"          => trim($row['horario_inicio']),
                                "horario_fin"             => trim($row['horario_fin'])
                            );
                            $fila_array++;
                        }
                    } else {
                        $datos[0]["no_registros"] = '<tr><td colspan="5">No se encontraron registros.</td></tr>';
                    }
                    $respuestaOK = true;
                } catch (PDOException $e) {
                    $mensajeError = "Error al consultar la contratación: " . $e->getMessage();
                }
                break;

            case 'GuardarLicenciasPermisos':
                $codigo_personal = isset($_POST['lstPersonal']) ? (int)$_POST['lstPersonal'] : 0;
                $codigo_contratacion = substr($_POST['lstTipoContratacion'] ?? '', 0, 2);
                $codigo_turno = substr($_POST['lstTipoContratacion'] ?? '', 2, 2);
                $codigo_licencia = $_POST['lstTipoLicencia'] ?? '';
                $fecha_inicio = $_POST['FechaTipoLicencia'] ?? date('Y-m-d');
                $DiasIncapacidad = isset($_POST['DiasIncapacidad']) ? (int)$_POST['DiasIncapacidad'] : 1;

                if ($DiasIncapacidad <= 1) {
                    $fecha_fin = $fecha_inicio;
                } else {
                    $diasSumar = $DiasIncapacidad - 1;
                    $fecha_fin = date('Y-m-d', strtotime("+$diasSumar day", strtotime($fecha_inicio)));
                }

                $dia = (int)($_POST['Dia'] ?? 0);
                $hora = (int)($_POST['Hora'] ?? 0);
                $minutos = (int)($_POST['Minutos'] ?? 0);
                $hora_inicio = $_POST['HoraDesde'] ?? '';
                $hora_fin = $_POST['HoraHasta'] ?? '';
                $observacion = trim($_POST['TxtObservacion'] ?? '');

                try {
                    // Verificar si ya existe un registro idéntico en esa fecha
                    $sqlBusqueda = "SELECT COUNT(*) FROM personal_licencias_permisos 
                                    WHERE fecha = :fecha 
                                      AND codigo_personal = :cod_personal 
                                      AND codigo_contratacion = :cod_contratacion 
                                      AND codigo_turno = :cod_turno 
                                      AND codigo_licencia_permiso = :cod_licencia";

                    $stmtBusqueda = $dblink->prepare($sqlBusqueda);
                    $stmtBusqueda->execute([
                        ':fecha'             => $fecha_inicio,
                        ':cod_personal'      => $codigo_personal,
                        ':cod_contratacion'  => $codigo_contratacion,
                        ':cod_turno'         => $codigo_turno,
                        ':cod_licencia'      => $codigo_licencia
                    ]);

                    if ($stmtBusqueda->fetchColumn() > 0) {
                        $respuestaOK = false;
                        $mensajeError = "El registro ya existe para la fecha especificada.";
                    } else {
                        // Bucle para insertar por rango de días
                        $datetime1 = new DateTime($fecha_inicio);
                        $datetime2 = new DateTime($fecha_fin);
                        $interval = $datetime1->diff($datetime2);
                        $diasTotales = $interval->days;

                        $sqlInsert = "INSERT INTO personal_licencias_permisos 
                                        (fecha, dia, hora, minutos, codigo_personal, codigo_licencia_permiso, codigo_turno, observacion, hora_inicio, hora_fin, codigo_contratacion, estado) 
                                      VALUES 
                                        (:fecha, :dia, :hora, :minutos, :cod_personal, :cod_licencia, :cod_turno, :observacion, :hora_inicio, :hora_fin, :cod_contratacion, 'A')";

                        $stmtInsert = $dblink->prepare($sqlInsert);

                        for ($i = 0; $i <= $diasTotales; $i++) {
                            $fechaActual = clone $datetime1;
                            $fechaActual->modify("+$i day");
                            $fechaFormatted = $fechaActual->format('Y-m-d');

                            $stmtInsert->execute([
                                ':fecha'            => $fechaFormatted,
                                ':dia'              => $dia,
                                ':hora'             => $hora,
                                ':minutos'          => $minutos,
                                ':cod_personal'     => $codigo_personal,
                                ':cod_licencia'     => $codigo_licencia,
                                ':cod_turno'        => $codigo_turno,
                                ':observacion'      => $observacion,
                                ':hora_inicio'      => $hora_inicio,
                                ':hora_fin'         => $hora_fin,
                                ':cod_contratacion' => $codigo_contratacion
                            ]);
                        }

                        $respuestaOK = true;
                        $mensajeError = "Registro guardado correctamente.";
                    }
                } catch (PDOException $e) {
                    $respuestaOK = false;
                    $mensajeError = "Error al guardar el registro: " . $e->getMessage();
                }
                break;

            case 'ActualizarLyP':
                $id = (int)($_POST['id_'] ?? 0);
                $fecha = $_POST['fecha'] ?? '';
                $dia = (int)($_POST['dia'] ?? 0);
                $hora = (int)($_POST['hora'] ?? 0);
                $minutos = (int)($_POST['minutos'] ?? 0);
                $codigo_licencia = $_POST['codigo_licencia'] ?? '';
                $observacion = trim($_POST['observaciones'] ?? '');
                $hora_inicio = $_POST['hora_inicio'] ?? '';
                $hora_fin = $_POST['hora_fin'] ?? '';

                try {
                    $sqlUpdate = "UPDATE personal_licencias_permisos 
                                  SET fecha = :fecha, dia = :dia, hora = :hora, minutos = :minutos, 
                                      codigo_licencia_permiso = :cod_licencia, observacion = :observacion, 
                                      hora_inicio = :hora_inicio, hora_fin = :hora_fin 
                                  WHERE id_licencia_permiso = :id";

                    $stmtUpdate = $dblink->prepare($sqlUpdate);
                    $stmtUpdate->execute([
                        ':fecha'         => $fecha,
                        ':dia'           => $dia,
                        ':hora'          => $hora,
                        ':minutos'       => $minutos,
                        ':cod_licencia'  => $codigo_licencia,
                        ':observacion'   => $observacion,
                        ':hora_inicio'   => $hora_inicio,
                        ':hora_fin'      => $hora_fin,
                        ':id'            => $id
                    ]);

                    $respuestaOK = true;
                    $mensajeError = "Registro actualizado correctamente.";
                } catch (PDOException $e) {
                    $respuestaOK = false;
                    $mensajeError = "Error al actualizar el registro: " . $e->getMessage();
                }
                break;

            case 'BuscarLicenciasPermisos':
                $codigo_personal = (int)($_POST['codigo_personal'] ?? 0);
                $fecha_anio = substr($_POST['fecha'] ?? date('Y'), 0, 4);
                $codigo_contratacion = $_POST['codigo_contratacion'] ?? '';
                $codigo_tipo_contratacion = substr($codigo_contratacion, 0, 2);
                $codigo_tipo_licencia = $_POST['codigo_licencia'] ?? '';

                $calculo_horas = ($codigo_tipo_contratacion === "05") ? 8 : 5;

                try {
                    // Consulta de licencias registradas
                    $sqlLicencias = "SELECT lp.id_licencia_permiso, lp.codigo_personal, lp.fecha, lp.codigo_contratacion, 
                                            lp.observacion, lp.dia, lp.hora, lp.minutos, lp.codigo_licencia_permiso, 
                                            lp.codigo_turno, lp.hora_inicio, lp.hora_fin,
                                            btrim(p.nombres || ' ' || p.apellidos) as nombre_docente
                                     FROM personal_licencias_permisos lp
                                     INNER JOIN personal p ON p.id_personal = lp.codigo_personal
                                     WHERE lp.codigo_personal = :cod_personal 
                                       AND btrim(lp.codigo_contratacion || lp.codigo_turno) = :cod_contratacion 
                                       AND TO_CHAR(lp.fecha, 'YYYY') = :anio
                                       AND lp.codigo_licencia_permiso = :cod_licencia
                                     ORDER BY lp.fecha";

                    $stmtLicencias = $dblink->prepare($sqlLicencias);
                    $stmtLicencias->execute([
                        ':cod_personal'     => $codigo_personal,
                        ':cod_contratacion' => $codigo_contratacion,
                        ':anio'             => $fecha_anio,
                        ':cod_licencia'     => $codigo_tipo_licencia
                    ]);

                    // Consulta de saldos del catálogo
                    $stmtCat = $dblink->query("SELECT codigo, nombre, saldo, minutos FROM tipo_licencia_o_permiso WHERE codigo = '$codigo_tipo_licencia'");
                    $catData = $stmtCat->fetch(PDO::FETCH_ASSOC);

                    $saldoDias = $catData['saldo'] ?? 0;
                    $minutosMaximos = $saldoDias * $calculo_horas * 60;

                    $j = 0;
                    $num = 1;
                    $tramite_dia = array();
                    $tramite_hora = array();
                    $tramite_minutos = array();

                    if ($stmtLicencias->rowCount() > 0) {
                        while ($row = $stmtLicencias->fetch(PDO::FETCH_ASSOC)) {
                            $id_ = $row['id_licencia_permiso'];
                            $fecha_fmt = isset($row['fecha']) ? date('d/m/Y', strtotime($row['fecha'])) : '';

                            $datos[$j][] = "<tr>
                                <td><input type='checkbox' class='case' name='chk{$id_}' id='chk{$id_}'></td>
                                <td>{$num}</td>
                                <td>{$id_}</td>
                                <td>{$fecha_fmt}</td>
                                <td>{$row['hora_inicio']}</td>
                                <td>{$row['hora_fin']}</td>
                                <td>{$row['dia']}</td>
                                <td>{$row['hora']}</td>
                                <td>{$row['minutos']}</td>
                                <td>
                                    <a data-accion='EditarLicenciaPermiso' class='btn btn-xs btn-info' data-toggle='tooltip' title='Editar' href='{$id_}'><i class='fas fa-edit'></i></a>
                                    <a data-accion='EliminarLicenciaPermiso' class='btn btn-xs btn-warning' data-toggle='tooltip' title='Eliminar' href='{$id_}'><i class='fas fa-trash'></i></a>
                                </td>
                            </tr>";

                            $total_min = ($row['dia'] * $calculo_horas * 60) + ($row['hora'] * 60) + $row['minutos'];
                            $tramite_dia[] = segundosToCadenaD($total_min, $calculo_horas);
                            $tramite_hora[] = segundosToCadenaH($total_min, $calculo_horas);
                            $tramite_minutos[] = segundosToCadenaM($total_min, $calculo_horas);

                            $num++;
                        }

                        $sub_dia = array_sum($tramite_dia);
                        $sub_hora = array_sum($tramite_hora);
                        $sub_min = array_sum($tramite_minutos);

                        $minutos_utilizados = ($sub_dia * $calculo_horas * 60) + ($sub_hora * 60) + $sub_min;
                        $minutos_disponibles = $minutosMaximos - $minutos_utilizados;

                        $j++;
                        $datos[$j]["Disponible"] = segundosToCadena($minutos_disponibles, $calculo_horas, 1);
                        $datos[$j]["Utilizado"]  = segundosToCadena($minutos_utilizados, $calculo_horas, 1);
                        $datos[$j]["DiasLicencia"] = segundosToCadena($minutosMaximos, $calculo_horas, 1);
                    } else {
                        $datos[$j][] = "<tr><td colspan='10'><span class='badge badge-dark'>No se encontraron registros</span></td></tr>";
                        $j++;
                        $datos[$j]["Disponible"] = segundosToCadena($minutosMaximos, $calculo_horas, 1);
                        $datos[$j]["Utilizado"]  = segundosToCadena(0, $calculo_horas, 1);
                        $datos[$j]["DiasLicencia"] = segundosToCadena($minutosMaximos, $calculo_horas, 1);
                    }
                    $respuestaOK = true;
                } catch (PDOException $e) {
                    $respuestaOK = false;
                    $mensajeError = "Error en la búsqueda: " . $e->getMessage();
                }
                break;

            case 'EliminarLicenciaPermiso':
                $id_ = (int)($_REQUEST['id_'] ?? 0);

                try {
                    $stmtDelete = $dblink->prepare("DELETE FROM personal_licencias_permisos WHERE id_licencia_permiso = :id");
                    $stmtDelete->execute([':id' => $id_]);

                    if ($stmtDelete->rowCount() > 0) {
                        $respuestaOK = true;
                        $mensajeError = 'Se ha eliminado el registro correctamente.';
                        $contenidoOK = 'Se eliminó 1 registro.';
                    } else {
                        $respuestaOK = false;
                        $mensajeError = 'No se encontró el registro para eliminar.';
                    }
                } catch (PDOException $e) {
                    $respuestaOK = false;
                    $mensajeError = 'Error al eliminar: ' . $e->getMessage();
                }
                break;

            default:
                $mensajeError = 'Esta acción no se encuentra disponible.';
                break;
        }
    } else {
        $mensajeError = 'No se recibieron parámetros válidos.';
    }
} else {
    $mensajeError = 'No se pudo establecer la conexión con la base de datos.';
}

// Retorno unificado en formato JSON
if (in_array($Accion, ['EditarLicenciasPermisos', 'BuscarLicenciasPermisos', 'BuscarContratacion'])) {
    echo json_encode($datos);
} else {
    echo json_encode([
        "respuesta" => $respuestaOK,
        "mensaje"   => $mensajeError,
        "contenido" => $contenidoOK,
        "encabezado"=> $encabezado
    ]);
}
?>