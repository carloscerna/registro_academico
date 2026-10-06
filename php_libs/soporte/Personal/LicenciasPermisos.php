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
    // Lectura de los parámetros recibidos por POST
    $id_licencia = (int)($_POST['id_licencia_permiso_modal'] ?? 0);
    $fecha       = $_POST['FechaInicio'] ?? '';
    $hora_inicio = $_POST['ModalHoraDesde'] ?? '';
    $hora_fin    = $_POST['ModalHoraHasta'] ?? '';
    $dia         = (int)($_POST['ModalDia'] ?? 0);
    $hora        = (int)($_POST['ModalHora'] ?? 0);
    $minutos     = (int)($_POST['ModalMinutos'] ?? 0);
    $observacion = trim($_POST['ModalObservacion'] ?? '');

    // Validación de parámetros de entrada
    if ($id_licencia <= 0) {
        echo json_encode([
            'respuesta' => false,
            'mensaje'   => 'No se recibieron parámetros válidos.',
            'contenido' => '',
            'encabezado' => ''
        ]);
        exit;
    }

    try {
        $sql = "UPDATE personal_licencias_permisos 
                SET fecha = :fecha,
                    hora_inicio = :hora_inicio,
                    hora_fin = :hora_fin,
                    dia = :dia,
                    hora = :hora,
                    minutos = :minutos,
                    observacion = :observacion
                WHERE id_licencia_permiso = :id";

        $stmt = $dblink->prepare($sql);
        $stmt->execute([
            ':fecha'       => $fecha,
            ':hora_inicio' => $hora_inicio,
            ':hora_fin'    => $hora_fin,
            ':dia'         => $dia,
            ':hora'        => $hora,
            ':minutos'     => $minutos,
            ':observacion' => $observacion,
            ':id'          => $id_licencia
        ]);

        echo json_encode([
            'respuesta' => true,
            'mensaje'   => 'El registro se actualizó correctamente.',
            'contenido' => '',
            'encabezado' => ''
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'respuesta' => false,
            'mensaje'   => 'Error en la base de datos: ' . $e->getMessage(),
            'contenido' => '',
            'encabezado' => ''
        ]);
    }
    exit;
    break;

case 'ConsultarSaldoEmpleado':
    $idPersonal = $_POST['id_personal'] ?? 0;
    $idTipoLicencia = $_POST['id_tipo_licencia'] ?? 0;
    
    // Obtener la fecha del input; si viene vacía, tomar la fecha actual del servidor
    $fechaInput = $_POST['fecha_licencia'] ?? date('Y-m-d');
    
    // Extraer únicamente el año (ejemplo: "2026")
    $anioConsulta = date('Y', strtotime($fechaInput));

    try {
        // A. Obtener el saldo oficial en DÍAS desde la tabla tipo_licencia_o_permiso
        $sqlLicencia = "SELECT id_tipo_licencia_o_permiso, codigo, nombre, saldo 
                        FROM tipo_licencia_o_permiso 
                        WHERE id_tipo_licencia_o_permiso = :id_tipo_licencia";
        
        $stmtLicencia = $dblink->prepare($sqlLicencia);
        $stmtLicencia->execute([':id_tipo_licencia' => $idTipoLicencia]);
        $datosLicencia = $stmtLicencia->fetch(PDO::FETCH_ASSOC);

        $diasLimite = $datosLicencia ? intval($datosLicencia['saldo']) : 0;
        $nombreLicencia = $datosLicencia ? trim($datosLicencia['nombre']) : 'Sin especificar';

        // B. Consultar el código de cargo para determinar la jornada (5h u 8h)
        $sqlCargo = "SELECT p.id_personal, c.codigo AS codigo_cargo 
                     FROM personal p
                     LEFT JOIN catalogo_cargo c ON p.codigo_cargo = c.codigo
                     WHERE p.id_personal = :id_personal";
        
        $stmtCargo = $dblink->prepare($sqlCargo);
        $stmtCargo->execute([':id_personal' => $idPersonal]);
        $datosPersonal = $stmtCargo->fetch(PDO::FETCH_ASSOC);

        $codigoCargo = trim($datosPersonal['codigo_cargo'] ?? '09');
        
        // Jornada: '02' y '03' -> 5 horas docentes; demás -> 8 horas administrativas
        $horasPorDia = in_array($codigoCargo, ['02', '03']) ? 5 : 8;
        $minutosPorDia = $horasPorDia * 60; // 300 o 480 minutos por día

        // Límite total de minutos según la jornada y días autorizados
        $minutosLimiteTotal = $diasLimite * $minutosPorDia;

        // C. Consultar consumo filtrado por empleado, tipo de licencia Y AÑO ACTUAL
        $sqlSaldo = "SELECT 
                        COALESCE(SUM(dia), 0) AS total_dias,
                        COALESCE(SUM(hora), 0) AS total_horas,
                        COALESCE(SUM(minutos), 0) AS total_minutos_extra
                     FROM personal_licencias_permisos 
                     WHERE codigo_personal = :id_personal 
                       AND codigo_licencia_permiso = :id_tipo_licencia
                       AND EXTRACT(YEAR FROM fecha) = :anio";

        $stmtSaldo = $dblink->prepare($sqlSaldo);
        $stmtSaldo->execute([
            ':id_personal'     => $idPersonal,
            ':id_tipo_licencia' => $idTipoLicencia,
            ':anio'             => $anioConsulta
        ]);
        $resultadoSaldo = $stmtSaldo->fetch(PDO::FETCH_ASSOC);

        // Convertir consumo a minutos globales del año
        $diasReg = intval($resultadoSaldo['total_dias']);
        $horasReg = intval($resultadoSaldo['total_horas']);
        $minutosReg = intval($resultadoSaldo['total_minutos_extra']);

        $minutosConsumidos = ($diasReg * $minutosPorDia) + ($horasReg * 60) + $minutosReg;

        // D. Desglose exacto de lo utilizado para el frontend
        $diasConsumidos = intdiv($minutosConsumidos, $minutosPorDia);
        $restoMinutos = $minutosConsumidos % $minutosPorDia;
        $horasConsumidas = intdiv($restoMinutos, 60);
        $minutosConsumidosFinal = $restoMinutos % 60;

        // E. Limpieza de salida y retorno de respuesta JSON
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'respuesta'              => true,
            'anio'                   => $anioConsulta,
            'nombre_licencia'        => $nombreLicencia,
            'codigo_cargo'           => $codigoCargo,
            'horas_por_dia'          => $horasPorDia,
            'minutos_consumidos'     => $minutosConsumidos,
            'dias_consumidos'        => $diasConsumidos,
            'horas_consumidas'       => $horasConsumidas,
            'minutos_consumidos_res' => $minutosConsumidosFinal,
            'dias_limite'            => $diasLimite,
            'minutos_limite_total'   => $minutosLimiteTotal
        ]);
        
        exit;

    } catch (PDOException $e) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'respuesta' => false,
            'mensaje'   => 'Error en la consulta: ' . $e->getMessage()
        ]);
        exit;
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

        $num = 1;
        $tramite_dia = array();
        $tramite_hora = array();
        $tramite_minutos = array();
        $filasHtml = "";

        if ($stmtLicencias->rowCount() > 0) {
            while ($row = $stmtLicencias->fetch(PDO::FETCH_ASSOC)) {
                $id_ = $row['id_licencia_permiso'];
                $fecha_fmt = isset($row['fecha']) ? date('d/m/Y', strtotime($row['fecha'])) : '';

                // Construcción de la fila con exactamente 10 columnas (<td>)
                $filasHtml .= "<tr>
                    <td class='text-center'><input type='checkbox' class='case' name='chk{$id_}' id='chk{$id_}'></td>
                    <td class='text-center'>{$num}</td>
                    <td class='text-center'>{$id_}</td>
                    <td class='text-center'>{$fecha_fmt}</td>
                    <td class='text-center'>{$row['hora_inicio']}</td>
                    <td class='text-center'>{$row['hora_fin']}</td>
                    <td class='text-center'>{$row['dia']}</td>
                    <td class='text-center'>{$row['hora']}</td>
                    <td class='text-center'>{$row['minutos']}</td>
                    <td class='text-center'>
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

            // Índice 0: Cadena HTML de las filas creadas
            $datos[0] = $filasHtml;

            // Índice 1: Arreglo asociativo con los saldos de tiempo
            $datos[1]["Disponible"]   = segundosToCadena($minutos_disponibles, $calculo_horas, 1);
            $datos[1]["Utilizado"]    = segundosToCadena($minutos_utilizados, $calculo_horas, 1);
            $datos[1]["DiasLicencia"] = segundosToCadena($minutosMaximos, $calculo_horas, 1);

        } else {
            // SI NO HAY REGISTROS: Se envía la cadena HTML vacía para dejar que DataTables controle la notificación visual sin alterar el número de columnas
            $datos[0] = "";

            $datos[1]["Disponible"]   = segundosToCadena($minutosMaximos, $calculo_horas, 1);
            $datos[1]["Utilizado"]    = segundosToCadena(0, $calculo_horas, 1);
            $datos[1]["DiasLicencia"] = segundosToCadena($minutosMaximos, $calculo_horas, 1);
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

case 'EditarLicenciasPermisos':
    // 1. Recepción y validación del ID
    $id_ = (int)($_REQUEST['id_'] ?? 0);

    if ($id_ <= 0) {
        echo json_encode([
            'respuestaOK' => false,
            'mensajeError' => 'ID de registro no válido.'
        ]);
        exit;
    }

    try {
        // 2. Consulta a la base de datos
        $sql = "SELECT id_licencia_permiso, codigo_personal, fecha, codigo_contratacion, 
                       observacion, dia, hora, minutos, codigo_licencia_permiso, 
                       codigo_turno, hora_inicio, hora_fin
                FROM personal_licencias_permisos 
                WHERE id_licencia_permiso = :id";

        $stmt = $dblink->prepare($sql);
        $stmt->execute([':id' => $id_]);

        if ($stmt->rowCount() > 0) {
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            // Formatear la fecha para input type="date" (YYYY-MM-DD)
            if (!empty($registro['fecha'])) {
                $registro['fecha'] = date('Y-m-d', strtotime($registro['fecha']));
            }

            // 3. Respuesta exitosa con los datos del registro
            echo json_encode([
                'respuestaOK' => true,
                'registro'    => $registro
            ]);
        } else {
            echo json_encode([
                'respuestaOK' => false,
                'mensajeError' => 'No se encontró el registro solicitado.'
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            'respuestaOK' => false,
            'mensajeError' => 'Error en la base de datos: ' . $e->getMessage()
        ]);
    }
    exit;
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
if (in_array($Accion, ['EditarLicenciasPermisos', 'BuscarLicenciasPermisos', 'BuscarContratacion','ConsultarSaldoEmpleado'])) {
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