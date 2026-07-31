<?php
// Limpiar cache.
clearstatcache();

// Configuración de encabezado
header("Content-Type: text/html;charset=iso-8859-1");

// Variables de respuesta por defecto
$datos = array();
$respuestaOK = false;
$mensajeError = "No Registro";
$contenidoOK = "";

// Ruta raíz
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

// Validar conexión con la base de datos
if ($errorDbConexion == false) {
    if (isset($_POST) && !empty($_POST)) {
        
        if (!empty($_POST['accion_buscar'])) {
            $_POST['accion'] = $_POST['accion_buscar'];
        }

        $accion = $_POST['accion'] ?? '';

        switch ($accion) {
            
            /***************************************************************************************************
             * BLOQUE: REGISTRO ORGANIZACIÓN HORARIOS DE EXÁMENES POR PERÍODO
             ***************************************************************************************************/
            case 'BuscarHorarios':
                $codigo_annlectivo = $_POST['codigo_annlectivo'] ?? '';
                $codigo_modalidad = $_POST['codigo_modalidad'] ?? '';

                $query = "SELECT pc.id_, pc.codigo_modalidad, pc.codigo_estatus, pc.fecha_desde, pc.fecha_hasta, pc.fecha_registro_academico,
                            ann.nombre as descripcion_annlectivo,
                            bach.codigo, bach.nombre as descripcion_modalidad,
                            cat_p.codigo, cat_p.descripcion as descripcion_periodo
                          FROM periodo_calendario pc
                          INNER JOIN ann_lectivo ann ON ann.codigo = pc.codigo_annlectivo
                          INNER JOIN bachillerato_ciclo bach ON bach.codigo = pc.codigo_modalidad
                          INNER JOIN catalogo_periodo cat_p ON cat_p.id_ = pc.codigo_periodo
                          WHERE codigo_annlectivo = :annlectivo AND codigo_modalidad = :modalidad
                          ORDER BY cat_p.codigo";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':annlectivo' => $codigo_annlectivo,
                    ':modalidad'  => $codigo_modalidad
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $num = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $num++;
                        $id_ = trim($listado['id_']);
                        $nombre_periodo = trim($listado['descripcion_periodo']);
                        $codigo_estatus = trim($listado['codigo_estatus']);
                        $fecha_desde = cambiaf_a_normal(trim($listado['fecha_desde']));
                        $fecha_hasta = cambiaf_a_normal(trim($listado['fecha_hasta']));
                        $fecha_registro_academico = cambiaf_a_normal(trim($listado['fecha_registro_academico']));

                        $estatus = ($codigo_estatus == '01') 
                            ? "<td><span class='badge badge-pill badge-info'>Activo</span></td>" 
                            : "<td><span class='badge badge-pill badge-danger'>Inactivo</span></td>";

                        $contenidoOK .= "<tr>
                            <td><input type='checkbox' class='case' name='chk$id_' id='chk$id_'></td>
                            <td>$num</td>
                            <td>$id_</td>
                            <td>$nombre_periodo</td>
                            <td>$fecha_desde</td>
                            <td>$fecha_hasta</td>
                            <td>$fecha_registro_academico</td>
                            $estatus
                            <td>
                                <a data-accion='EditarHorarios' class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href='$id_'><i class='fas fa-edit'></i></a>
                                <a data-accion='EliminarHorarios' class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href='$id_'><i class='fas fa-trash'></i></a>
                            </td>
                        </tr>";
                    }
                    $mensajeError = "Se ha consultado el registro correctamente ";
                }
                break;

            case 'EditarHorarios':
                $id_ = $_REQUEST['id_'] ?? '';

                $query = "SELECT * FROM periodo_calendario WHERE id_ = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $fila_array = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $datos[$fila_array]["id_"] = trim($listado['id_']);
                        $datos[$fila_array]["codigo_estatus"] = trim($listado['codigo_estatus']);
                        $datos[$fila_array]["codigo_periodo"] = trim($listado['codigo_periodo']);
                        $datos[$fila_array]["fecha_desde"] = trim($listado['fecha_desde']);
                        $datos[$fila_array]["fecha_hasta"] = trim($listado['fecha_hasta']);
                        $datos[$fila_array]["fecha_registro_academico"] = trim($listado['fecha_registro_academico']);
                        $fila_array++;
                    }
                    $mensajeError = "Se ha consultado el registro correctamente ";
                }
                break;

            case 'ActualizarHorarios':
                $id_ = $_POST['IdHorarios'] ?? '';
                $codigo_estatus = $_REQUEST['lstHorarios'] ?? '';
                $codigo_periodo = $_POST['lstPeriodosHorarios'] ?? '';
                $FechaInicio = $_POST['FechaInicio'] ?? '';
                $FechaFin = $_POST['FechaFin'] ?? '';
                $FechaRA = $_POST['FechaRA'] ?? '';
                $estatus = ($codigo_estatus == '01') ? "1" : "0";

                $query = "UPDATE periodo_calendario 
                          SET fecha_desde = :fecha_inicio,
                              fecha_hasta = :fecha_fin,
                              fecha_registro_academico = :fecha_ra,
                              codigo_periodo = :codigo_periodo,
                              codigo_estatus = :codigo_estatus,
                              estatus = :estatus
                          WHERE id_ = :id";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':fecha_inicio'   => $FechaInicio,
                    ':fecha_fin'      => $FechaFin,
                    ':fecha_ra'       => $FechaRA,
                    ':codigo_periodo' => $codigo_periodo,
                    ':codigo_estatus' => $codigo_estatus,
                    ':estatus'        => $estatus,
                    ':id'             => $id_
                ]);

                $respuestaOK = true;
                $contenidoOK = "Registro Actualizado.";
                $mensajeError = "Se ha consultado el registro correctamente ";
                break;

            case 'GuardarHorarios':
                $codigo_annlectivo = $_POST['codigo_annlectivo'] ?? '';
                $codigo_modalidad = $_POST['codigo_modalidad'] ?? '';
                $codigo_estatus = $_REQUEST['lstHorarios'] ?? '';
                $codigo_periodo = $_POST['lstPeriodosHorarios'] ?? '';
                $FechaInicio = $_POST['FechaInicio'] ?? '';
                $FechaFin = $_POST['FechaFin'] ?? '';
                $FechaRA = $_POST['FechaRA'] ?? '';
                $estatus = ($codigo_estatus == '01') ? "1" : "0";

                $query = "SELECT * FROM periodo_calendario 
                          WHERE codigo_annlectivo = :annlectivo AND codigo_modalidad = :modalidad AND codigo_periodo = :periodo";
                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':annlectivo' => $codigo_annlectivo,
                    ':modalidad'  => $codigo_modalidad,
                    ':periodo'    => $codigo_periodo
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = false;
                    $contenidoOK = "Este registro ya Existe";
                    $mensajeError = "El Nivel y Periodo ya Existen.";
                } else {
                    $queryInsert = "INSERT INTO periodo_calendario 
                        (codigo_annlectivo, codigo_modalidad, fecha_desde, fecha_hasta, fecha_registro_academico, codigo_periodo, codigo_estatus, estatus) 
                        VALUES (:annlectivo, :modalidad, :fecha_inicio, :fecha_fin, :fecha_ra, :periodo, :codigo_estatus, :estatus)";
                    
                    $stmtInsert = $dblink->prepare($queryInsert);
                    $stmtInsert->execute([
                        ':annlectivo'    => $codigo_annlectivo,
                        ':modalidad'     => $codigo_modalidad,
                        ':fecha_inicio'  => $FechaInicio,
                        ':fecha_fin'     => $FechaFin,
                        ':fecha_ra'      => $FechaRA,
                        ':periodo'       => $codigo_periodo,
                        ':codigo_estatus'=> $codigo_estatus,
                        ':estatus'       => $estatus
                    ]);

                    $respuestaOK = true;
                    $contenidoOK = "Registro Agregado";
                    $mensajeError = "Si Registro";
                }
                break;

            case 'EliminarHorarios':
                $id_ = $_REQUEST['id_'] ?? '';

                $query = "DELETE FROM periodo_calendario WHERE id_ = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                $count = $stmt->rowCount();
                if ($count > 0) {
                    $respuestaOK = true;
                    $mensajeError = "Se ha eliminado el registro correctamente";
                    $contenidoOK = "Se ha Eliminado $count Registro(s).";
                } else {
                    $mensajeError = "No se ha eliminado el registro";
                }
                break;

            /***************************************************************************************************
             * BLOQUE: REGISTRO ORGANIZACIÓN MODALIDAD Y AÑO LECTIVO
             ***************************************************************************************************/
            case 'BuscarModalidad':
                $codigo_annlectivo = $_POST['codigo_annlectivo'] ?? '';

                $query = "SELECT orgac.id_organizar_ann_lectivo_ciclos, orgac.codigo_ann_lectivo, orgac.codigo_bachillerato, orgac.codigo_servicio_educativo, orgac.ordenar,
                            cat_se.descripcion as descripcion_se,
                            ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad
                          FROM organizar_ann_lectivo_ciclos orgac
                          INNER JOIN ann_lectivo ann ON ann.codigo = orgac.codigo_ann_lectivo
                          INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgac.codigo_bachillerato
                          INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = orgac.codigo_servicio_educativo
                          WHERE orgac.codigo_ann_lectivo = :annlectivo 
                          ORDER BY orgac.ordenar ASC, orgac.id_organizar_ann_lectivo_ciclos";

                $stmt = $dblink->prepare($query);
                $stmt->execute([':annlectivo' => $codigo_annlectivo]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $num = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $num++;
                        $id_ = trim($listado['id_organizar_ann_lectivo_ciclos']);
                        $codigo_modalidad = trim($listado['codigo_bachillerato']);
                        $nombre_modalidad = trim($listado['nombre_modalidad']);
                        $nombre_se = trim($listado['descripcion_se']);
                        $orden = trim($listado["ordenar"]);

                        $contenidoOK .= "<tr>
                            <td><input type='checkbox' class='case' name='chk$id_' id='chk$id_'></td>
                            <td>$num</td>
                            <td>$id_</td>
                            <td>$codigo_modalidad</td>
                            <td>$nombre_modalidad</td>
                            <td>$nombre_se</td>
                            <td><input type='number' name='orden' id='orden' class='form-control' value='$orden'></td>
                            <td>
                                <a data-accion='EliminarModalidad' class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href='$id_'><i class='fas fa-trash'></i></a>
                            </td>
                        </tr>";
                    }
                    $mensajeError = "Si Registro";
                }
                break;

            case 'GuardarModalidad':
                $codigo_annlectivo = $_POST['codigo_annlectivo'] ?? '';
                $codigo_modalidad = $_POST['codigo_modalidad'] ?? '';
                $codigo_se = $_REQUEST['lstModalidadServicioEducativo'] ?? '';

                $query = "SELECT * FROM organizar_ann_lectivo_ciclos 
                          WHERE codigo_ann_lectivo = :annlectivo AND codigo_bachillerato = :modalidad AND codigo_servicio_educativo = :se";
                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':annlectivo' => $codigo_annlectivo,
                    ':modalidad'  => $codigo_modalidad,
                    ':se'         => $codigo_se
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = false;
                    $contenidoOK = "Este registro ya Existe";
                    $mensajeError = "Nivel ya fue Guardado para el Año Lectivo.";
                } else {
                    $queryInsert = "INSERT INTO organizar_ann_lectivo_ciclos (codigo_ann_lectivo, codigo_bachillerato, codigo_servicio_educativo) 
                                    VALUES (:annlectivo, :modalidad, :se)";
                    $stmtInsert = $dblink->prepare($queryInsert);
                    $stmtInsert->execute([
                        ':annlectivo' => $codigo_annlectivo,
                        ':modalidad'  => $codigo_modalidad,
                        ':se'         => $codigo_se
                    ]);

                    $respuestaOK = true;
                    $contenidoOK = "Registro Agregado";
                    $mensajeError = "Registro Guardado.";
                }
                break;

            case 'EliminarModalidad':
                $id_ = $_REQUEST["id_"] ?? '';

                $query = "DELETE FROM organizar_ann_lectivo_ciclos WHERE id_organizar_ann_lectivo_ciclos = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                $count = $stmt->rowCount();
                if ($count > 0) {
                    $respuestaOK = true;
                    $mensajeError = 'Se ha eliminado el registro correctamente';
                    $contenidoOK = 'Se ha Eliminado ' . $count . ' Registro(s).';
                } else {
                    $mensajeError = 'No se ha eliminado el registro';
                }
                break;

            case 'ActualizarOrden':
                $id_nivel = $_POST["id_nivel"] ?? array();
                $orden = $_POST["orden"] ?? array();
                $fila = ($_POST["fila"] ?? 1) - 1;

                $query_aaa = "UPDATE organizar_ann_lectivo_ciclos SET ordenar = :orden WHERE id_organizar_ann_lectivo_ciclos = :id";
                $stmt = $dblink->prepare($query_aaa);

                for ($i = 0; $i <= $fila; $i++) {
                    $id_nivel_ = trim($id_nivel[0][$i]);
                    $orden_ = $orden[0][$i];

                    $stmt->execute([
                        ':orden' => $orden_,
                        ':id'    => $id_nivel_
                    ]);
                }

                $respuestaOK = true;
                $contenidoOK = '';
                $mensajeError = 'Registro Actualizado';
                break;

            /***************************************************************************************************
             * BLOQUE: REGISTRO ORGANIZACIÓN GRADOS, SECCIÓN Y TURNO
             ***************************************************************************************************/
            case 'BuscarSeGST':
                $codigo_ann_lectivo = trim($_POST["codigo_annlectivo"] ?? '');
                $codigo_modalidad = trim($_POST["codigo_modalidad"] ?? '');

                $query = "SELECT org.id_grados_secciones, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_ann_lectivo, org.codigo_turno, 
                            cat_se.descripcion as descripcion_se, ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, 
                            gr.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
                          FROM organizacion_grados_secciones org 
                          INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
                          INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
                          INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
                          INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
                          INNER JOIN turno tur ON tur.codigo = org.codigo_turno
                          INNER JOIN catalogo_servicio_educativo cat_se ON cat_se.codigo = org.codigo_servicio_educativo
                          WHERE org.codigo_ann_lectivo = :annlectivo AND org.codigo_bachillerato = :modalidad
                          ORDER BY org.codigo_ann_lectivo, org.codigo_bachillerato, org.codigo_grado, org.codigo_seccion, org.codigo_turno";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':annlectivo' => $codigo_ann_lectivo,
                    ':modalidad'  => $codigo_modalidad
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $num = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $num++;
                        $id_ = trim($listado['id_grados_secciones']);
                        $nombre_modalidad = trim($listado['nombre_modalidad']);
                        $nombre_grado = trim($listado['nombre_grado']);
                        $nombre_seccion = trim($listado['nombre_seccion']);
                        $nombre_turno = trim($listado['nombre_turno']);
                        $nombre_se = trim($listado['descripcion_se']);

                        $contenidoOK .= "<tr>
                            <td><input type='checkbox' class='case' name='chk$id_' id='chk$id_'></td>
                            <td>$num</td>
                            <td>$id_</td>
                            <td>$nombre_modalidad</td>
                            <td>$nombre_se</td>
                            <td>$nombre_grado</td>
                            <td>$nombre_seccion</td>
                            <td>$nombre_turno</td>
                            <td>
                                <a data-accion='EditarSeGST' class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href='$id_'><i class='fas fa-edit'></i></a>
                                <a data-accion='EliminarSeGST' class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href='$id_'><i class='fas fa-trash'></i></a>
                            </td>
                        </tr>";
                    }
                    $mensajeError = "Si Registro";
                }
                break;

            case 'EditarGST':
                $id_ = trim($_REQUEST['id_'] ?? '');

                $query = "SELECT id_grados_secciones, codigo_servicio_educativo, codigo_turno
                          FROM organizacion_grados_secciones
                          WHERE id_grados_secciones = :id";

                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $fila_array = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $datos[$fila_array]["id_"] = trim($listado['id_grados_secciones']);
                        $datos[$fila_array]["codigo_se"] = trim($listado['codigo_servicio_educativo']);
                        $datos[$fila_array]["codigo_turno"] = trim($listado['codigo_turno']);
                        $fila_array++;
                    }
                    $mensajeError = "Se ha consultado el registro correctamente ";
                }
                break;

            case 'ActualizarSeGST':
                $id_ = $_POST['id_'] ?? '';
                $codigo_se = trim($_POST['lstSeGST'] ?? '');
                $codigo_turno = $_POST["lstTurnoSeGST"] ?? '';

                $query = "UPDATE organizacion_grados_secciones 
                          SET codigo_servicio_educativo = :se, codigo_turno = :turno
                          WHERE id_grados_secciones = :id";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':se'    => $codigo_se,
                    ':turno' => $codigo_turno,
                    ':id'    => $id_
                ]);

                $respuestaOK = true;
                $contenidoOK = "Registro Actualizado.";
                $mensajeError = "Se ha consultado el registro correctamente ";
                break;

            case 'GuardarSeGST':
                $codigo_ann_lectivo = $_POST['lstAnnLectivoSeGST'] ?? '';
                $codigo_modalidad = $_POST['lstModalidadSeGST'] ?? '';
                $codigo_grado = $_POST["lstGradoSeGST"] ?? '';
                $codigo_seccion = $_POST["lstSeccionSeGST"] ?? '';
                $codigo_turno = $_POST["lstTurnoSeGST"] ?? '';
                $codigo_servicio_educativo = $_POST["lstSeGST"] ?? '';

                $query = "SELECT * FROM organizacion_grados_secciones 
                          WHERE codigo_ann_lectivo = :annlectivo AND codigo_bachillerato = :modalidad 
                          AND codigo_grado = :grado AND codigo_seccion = :seccion 
                          AND codigo_turno = :turno AND codigo_servicio_educativo = :se";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':annlectivo' => $codigo_ann_lectivo,
                    ':modalidad'  => $codigo_modalidad,
                    ':grado'      => $codigo_grado,
                    ':seccion'    => $codigo_seccion,
                    ':turno'      => $codigo_turno,
                    ':se'         => $codigo_servicio_educativo
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = false;
                    $contenidoOK = "";
                    $mensajeError = "Si Existe";
                } else {
                    $queryInsert = "INSERT INTO organizacion_grados_secciones 
                        (codigo_ann_lectivo, codigo_bachillerato, codigo_grado, codigo_seccion, codigo_turno, codigo_servicio_educativo) 
                        VALUES (:annlectivo, :modalidad, :grado, :seccion, :turno, :se)";

                    $stmtInsert = $dblink->prepare($queryInsert);
                    $stmtInsert->execute([
                        ':annlectivo' => $codigo_ann_lectivo,
                        ':modalidad'  => $codigo_modalidad,
                        ':grado'      => $codigo_grado,
                        ':seccion'    => $codigo_seccion,
                        ':turno'      => $codigo_turno,
                        ':se'         => $codigo_servicio_educativo
                    ]);

                    $respuestaOK = true;
                    $contenidoOK = "";
                    $mensajeError = "Si Registro";
                }
                break;

            case 'EliminarSeGST':
                $id_ = $_POST['id_'] ?? '';

                $query = "DELETE FROM organizacion_grados_secciones WHERE id_grados_secciones = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                $count = $stmt->rowCount();
                if ($count > 0) {
                    $respuestaOK = true;
                    $mensajeError = 'Se ha eliminado el registro correctamente';
                    $contenidoOK = 'Se ha Eliminado ' . $count . ' Registro(s).';
                } else {
                    $mensajeError = 'No se ha eliminado el registro';
                }
                break;

            /***************************************************************************************************
             * BLOQUE: GESTIÓN DE SECCIONES
             ***************************************************************************************************/
            case 'BuscarCodigoSeccion':
                $query = "SELECT id_seccion, nombre, codigo FROM seccion ORDER BY codigo DESC LIMIT 1";
                $stmt = $dblink->query($query);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $listado = $stmt->fetch(PDO::FETCH_BOTH);
                    $datos[0]["codigo_seccion"] = trim($listado['codigo']);
                }
                break;

            /***************************************************************************************************
             * BLOQUE: GESTIÓN PLANTA DOCENTE
             ***************************************************************************************************/
            case 'BuscarDN':
                $codigo_annlectivo = $_POST["codigo_annlectivo"] ?? '';
                $codigo_modalidad = $_POST["codigo_modalidad"] ?? '';

                $query = "SELECT orgpda.id_organizar_planta_docente_ciclos, orgpda.codigo_bachillerato, orgpda.codigo_ann_lectivo, orgpda.codigo_turno, orgpda.codigo_docente, 
                            ann.nombre as nombre_ann_lectivo, bach.nombre as nombre_modalidad, 
                            btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_personal, p.id_personal,
                            tur.nombre as nombre_turno
                          FROM organizar_planta_docente_ciclos orgpda
                          INNER JOIN ann_lectivo ann ON ann.codigo = orgpda.codigo_ann_lectivo
                          INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgpda.codigo_bachillerato
                          INNER JOIN personal p ON p.id_personal = orgpda.codigo_docente
                          INNER JOIN turno tur ON tur.codigo = orgpda.codigo_turno
                          WHERE orgpda.codigo_bachillerato = :modalidad AND orgpda.codigo_ann_lectivo = :annlectivo AND p.codigo_estatus = '01'";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':modalidad'  => $codigo_modalidad,
                    ':annlectivo' => $codigo_annlectivo
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $num = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $num++;
                        $id_ = trim($listado['id_organizar_planta_docente_ciclos']);
                        $nombre_modalidad = trim($listado['nombre_modalidad']);
                        $nombre_personal = trim($listado["nombre_personal"]);
                        $nombre_turno = trim($listado["nombre_turno"]);

                        $contenidoOK .= "<tr>
                            <td><input type='checkbox' class='case' name='chk$id_' id='chk$id_'></td>
                            <td>$num</td>
                            <td>$id_</td>
                            <td>$nombre_modalidad</td>
                            <td>$nombre_personal</td>
                            <td>$nombre_turno</td>
                            <td>
                                <a data-accion='EditarDN' class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href='$id_'><i class='fas fa-edit'></i></a>
                                <a data-accion='EliminarDN' class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href='$id_'><i class='fas fa-trash'></i></a>
                            </td>
                        </tr>";
                    }
                    $mensajeError = "Si Registro";
                }
                break;

            case 'GuardarDN':
                $codigo_ann_lectivo = $_POST['lstAnnLectivoDN'] ?? '';
                $codigo_modalidad = $_POST['lstModalidadDN'] ?? '';
                $codigo_personal = $_POST["lstDocenteNivel"] ?? '';
                $codigo_turno = $_POST["lstTurnoDN"] ?? '';

                $query = "SELECT * FROM organizar_planta_docente_ciclos 
                          WHERE codigo_docente = :docente AND codigo_ann_lectivo = :annlectivo 
                          AND codigo_bachillerato = :modalidad AND codigo_turno = :turno";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':docente'   => $codigo_personal,
                    ':annlectivo'=> $codigo_ann_lectivo,
                    ':modalidad' => $codigo_modalidad,
                    ':turno'     => $codigo_turno
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = false;
                    $contenidoOK = "";
                    $mensajeError = "El Docente ya están este Nivel y Turno.";
                } else {
                    $queryInsert = "INSERT INTO organizar_planta_docente_ciclos 
                        (codigo_ann_lectivo, codigo_bachillerato, codigo_turno, codigo_docente) 
                        VALUES (:annlectivo, :modalidad, :turno, :docente)";

                    $stmtInsert = $dblink->prepare($queryInsert);
                    $stmtInsert->execute([
                        ':annlectivo' => $codigo_ann_lectivo,
                        ':modalidad'  => $codigo_modalidad,
                        ':turno'      => $codigo_turno,
                        ':docente'    => $codigo_personal
                    ]);

                    $respuestaOK = true;
                    $contenidoOK = "";
                    $mensajeError = "Registro Guardado";
                }
                break;

            case 'EditarDN':
                $id_ = trim($_REQUEST['id_'] ?? '');

                $query = "SELECT * FROM organizar_planta_docente_ciclos WHERE id_organizar_planta_docente_ciclos = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $fila_array = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $datos[$fila_array]["id_"] = trim($listado['id_organizar_planta_docente_ciclos']);
                        $datos[$fila_array]["codigo_docente"] = trim($listado['codigo_docente']);
                        $datos[$fila_array]["codigo_turno"] = trim($listado['codigo_turno']);
                        $fila_array++;
                    }
                    $mensajeError = "Se ha consultado el registro correctamente ";
                }
                break;

            case 'ActualizarDN':
                $id_ = trim($_REQUEST['id_'] ?? '');
                $codigo_docente = trim($_POST['lstDocenteNivel'] ?? '');
                $codigo_turno = trim($_POST['lstTurnoDN'] ?? '');

                $query = "UPDATE organizar_planta_docente_ciclos 
                          SET codigo_docente = :docente, codigo_turno = :turno
                          WHERE id_organizar_planta_docente_ciclos = :id";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':docente' => $codigo_docente,
                    ':turno'   => $codigo_turno,
                    ':id'      => $id_
                ]);

                $respuestaOK = true;
                $contenidoOK = "Registro Actualizado.";
                $mensajeError = "Se ha consultado el registro correctamente ";
                break;

            case 'EliminarDN':
                $id_ = $_POST['id_'] ?? '';

                $query = "DELETE FROM organizar_planta_docente_ciclos WHERE id_organizar_planta_docente_ciclos = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $mensajeError = 'Se ha eliminado el registro correctamente';
                    $contenidoOK = 'Se ha Eliminado Registro(s).';
                } else {
                    $mensajeError = 'No se ha eliminado el registro';
                }
                break;

            /***************************************************************************************************
             * BLOQUE: ASIGNACIÓN DE ASIGNATURAS A GRADOS (AAG)
             ***************************************************************************************************/
            case 'BuscarAAG':
                $codigo_annlectivo = $_POST["codigo_annlectivo"] ?? '';
                $codigo_modalidad = $_POST["codigo_modalidad"] ?? '';
                $codigo_grado_se_post = explode("-", $_POST["codigo_grado_se"] ?? '');
                $codigo_grado = $codigo_grado_se_post[0] ?? '';

                $query = "SELECT DISTINCT aaa.codigo_asignacion, aaa.id_asignacion, aaa.orden, 
                            asig.codigo as codigo_asignatura, asig.nombre as nombre_asignatura,
                            cat_area_di.descripcion as descripcion_area_dimension, 
                            cat_area_subdi.descripcion as descripcion_area_subdimension,
                            cat_area.descripcion as nombre_area
                          FROM a_a_a_bach_o_ciclo aaa 
                          INNER JOIN ann_lectivo ann ON ann.codigo = aaa.codigo_ann_lectivo 
                          INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo 
                          INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado 
                          INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura 
                          INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area 
                          INNER JOIN catalogo_area_dimension cat_area_di ON cat_area_di.codigo = asig.codigo_area_dimension
                          INNER JOIN catalogo_area_subdimension cat_area_subdi ON cat_area_subdi.codigo = asig.codigo_area_subdimension
                          WHERE aaa.codigo_bach_o_ciclo = :modalidad AND aaa.codigo_ann_lectivo = :annlectivo AND aaa.codigo_grado = :grado
                          ORDER BY aaa.orden";

                $stmt = $dblink->prepare($query);
                $stmt->execute([
                    ':modalidad'  => $codigo_modalidad,
                    ':annlectivo' => $codigo_annlectivo,
                    ':grado'      => $codigo_grado
                ]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $num = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_BOTH)) {
                        $num++;
                        $id_ = trim($listado['id_asignacion']);
                        $nombre_area = trim($listado["nombre_area"]);
                        $nombre_area_dimension = trim($listado['descripcion_area_dimension']);
                        $nombre_area_subdimension = trim($listado['descripcion_area_subdimension']);
                        $nombre_asignatura = trim($listado["nombre_asignatura"]);
                        $orden = trim($listado["orden"]);
                        $codigo_asignatura = trim($listado["codigo_asignatura"]);

                        if ($nombre_area_dimension == "Ninguno") {
                            $nombre_area_dimension_subdimension_asignatura = $nombre_area . " - " . $nombre_asignatura;
                        } else {
                            $nombre_area_dimension_subdimension_asignatura = $nombre_area . "-" . $nombre_area_dimension . "-" . $nombre_area_subdimension . "-" . $nombre_asignatura;
                        }

                        $contenidoOK .= "<tr>
                            <td><input type='checkbox' class='case' name='chk$id_' id='chk$id_'></td>
                            <td>$num</td>
                            <td>$id_</td>
                            <td>$codigo_asignatura</td>
                            <td>$nombre_area_dimension_subdimension_asignatura</td>
                            <td>$nombre_asignatura</td>
                            <td>$orden</td>
                            <td>
                                <a data-accion='EditarAAG' class='btn btn-xs btn-info' data-toggle='tooltip' data-placement='top' title='Editar' href='$id_'><i class='fas fa-edit'></i></a>
                                <a data-accion='EliminarAAG' class='btn btn-xs btn-warning' data-toggle='tooltip' data-placement='top' title='Eliminar' href='$id_'><i class='fas fa-trash'></i></a>
                            </td>
                        </tr>";
                    }
                    $mensajeError = "Si Registro";
                }
                break;

            case 'GuardarAAG':
                $codigo_annlectivo = $_POST['lstAnnLectivoAAG'] ?? '';
                $codigo_modalidad = $_POST['lstModalidadAAG'] ?? '';
                $codigo_grado_se = explode("-", $_POST["lstGradoAAG"] ?? '');
                $codigo_grado = $codigo_grado_se[0] ?? '';
                $codigo_servicio_educativo = $codigo_grado_se[1] ?? '';
                $codigo_asignatura = $_POST["lstAAG"] ?? '';
                $TodasLasAsignaturas = $_POST["TodasLasAsignaturas"] ?? 'no';

                if ($TodasLasAsignaturas == "yes") {
                    $query_todas = "SELECT codigo as codigo_asignatura, nombre as nombre_asignatura, ordenar
                                    FROM asignatura
                                    WHERE imprimir = 'true' AND estatus = 'true' AND codigo_servicio_educativo = :se
                                    ORDER BY codigo_servicio_educativo, codigo_area, id_asignatura";

                    $stmt_todas = $dblink->prepare($query_todas);
                    $stmt_todas->execute([':se' => $codigo_servicio_educativo]);

                    $query_buscar = "SELECT * FROM a_a_a_bach_o_ciclo 
                                     WHERE codigo_ann_lectivo = :annlectivo AND codigo_bach_o_ciclo = :modalidad 
                                     AND codigo_grado = :grado AND codigo_asignatura = :asignatura";
                    $stmt_buscar = $dblink->prepare($query_buscar);

                    $query_insert = "INSERT INTO a_a_a_bach_o_ciclo (codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_asignacion, codigo_grado, codigo_asignatura, orden) 
                                     VALUES (:annlectivo, :modalidad, :asignacion, :grado, :asignatura, :orden)";
                    $stmt_insert = $dblink->prepare($query_insert);

                    while ($listado = $stmt_todas->fetch(PDO::FETCH_BOTH)) {
                        $cod_asig = trim($listado['codigo_asignatura']);
                        $ordenar = trim($listado['ordenar']);

                        $stmt_buscar->execute([
                            ':annlectivo' => $codigo_annlectivo,
                            ':modalidad'  => $codigo_modalidad,
                            ':grado'      => $codigo_grado,
                            ':asignatura' => $cod_asig
                        ]);

                        if ($stmt_buscar->rowCount() == 0) {
                            $stmt_insert->execute([
                                ':annlectivo' => $codigo_annlectivo,
                                ':modalidad'  => $codigo_modalidad,
                                ':asignacion' => $codigo_modalidad,
                                ':grado'      => $codigo_grado,
                                ':asignatura' => $cod_asig,
                                ':orden'      => $ordenar
                            ]);
                        }
                    }

                    $respuestaOK = true;
                    $contenidoOK = "Registros Procesados";
                    $mensajeError = "Si Registro";

                } else {
                    $query_buscar = "SELECT * FROM a_a_a_bach_o_ciclo 
                                     WHERE codigo_ann_lectivo = :annlectivo AND codigo_bach_o_ciclo = :modalidad 
                                     AND codigo_grado = :grado AND codigo_asignatura = :asignatura";
                    
                    $stmt_buscar = $dblink->prepare($query_buscar);
                    $stmt_buscar->execute([
                        ':annlectivo' => $codigo_annlectivo,
                        ':modalidad'  => $codigo_modalidad,
                        ':grado'      => $codigo_grado,
                        ':asignatura' => $codigo_asignatura
                    ]);

                    if ($stmt_buscar->rowCount() > 0) {
                        $respuestaOK = false;
                        $contenidoOK = "";
                        $mensajeError = "El Registro del Componente ya Existe.";
                    } else {
                        $query_insert = "INSERT INTO a_a_a_bach_o_ciclo (codigo_ann_lectivo, codigo_bach_o_ciclo, codigo_asignacion, codigo_grado, codigo_asignatura) 
                                         VALUES (:annlectivo, :modalidad, :asignacion, :grado, :asignatura)";
                        
                        $stmt_insert = $dblink->prepare($query_insert);
                        $stmt_insert->execute([
                            ':annlectivo' => $codigo_annlectivo,
                            ':modalidad'  => $codigo_modalidad,
                            ':asignacion' => $codigo_modalidad,
                            ':grado'      => $codigo_grado,
                            ':asignatura' => $codigo_asignatura
                        ]);

                        $respuestaOK = true;
                        $contenidoOK = "";
                        $mensajeError = "El Registro fue Guardado Correctamente.";
                    }
                }
                break;

            case 'ActualizarAAG':
                $codigo_aa = $_POST["codigo_aa"] ?? array();
                $codigo_asignatura = $_POST["codigo_asignatura"] ?? array();
                $codigo_sirai = $_POST["codigo_sirai"] ?? array();
                $orden = $_POST["orden"] ?? array();
                $fila = ($_POST["fila"] ?? 1) - 1;

                $query_aa = "UPDATE a_a_a_bach_o_ciclo SET codigo_sirai = :sirai, orden = :orden WHERE id_asignacion = :id";
                $stmt_aa = $dblink->prepare($query_aa);

                $query_aa_nota = "UPDATE nota SET orden = :orden WHERE codigo_asignatura = :asignatura";
                $stmt_nota = $dblink->prepare($query_aa_nota);

                for ($i = 0; $i <= $fila; $i++) {
                    $codigo_a = $codigo_aa[0][$i];
                    $codigo_asig = $codigo_asignatura[0][$i];
                    $codigo_cs = $codigo_sirai[0][$i];
                    $orden_ = $orden[0][$i];

                    $stmt_aa->execute([
                        ':sirai' => $codigo_cs,
                        ':orden' => $orden_,
                        ':id'    => $codigo_a
                    ]);

                    $stmt_nota->execute([
                        ':orden'      => $orden_,
                        ':asignatura' => $codigo_asig
                    ]);
                }

                $respuestaOK = true;
                $contenidoOK = 'Registros Actualizados.';
                $mensajeError = 'Si Registro';
                break;

            case 'EliminarAAG':
                $id_ = $_POST['id_'] ?? '';

                $query = "DELETE FROM a_a_a_bach_o_ciclo WHERE id_asignacion = :id";
                $stmt = $dblink->prepare($query);
                $stmt->execute([':id' => $id_]);

                if ($stmt->rowCount() > 0) {
                    $respuestaOK = true;
                    $mensajeError = 'Se ha eliminado el registro correctamente';
                    $contenidoOK = 'Se ha Eliminado Registro(s).';
                } else {
                    $mensajeError = 'No se ha eliminado el registro';
                }
                break;

            default:
                $mensajeError = 'No Registro';
                break;
        }
    } else {
        $mensajeError = 'No se puede ejecutar la aplicación';
    }
} else {
    $mensajeError = 'No se puede establecer conexión con la base de datos';
}

// Retorno JSON de respuestas
$acciones_generales = [
    "BuscarHorarios", "GuardarHorarios", "ActualizarHorarios", "EliminarHorarios",
    "BuscarModalidad", "GuardarModalidad", "EliminarModalidad", "BuscarSeGST",
    "ActualizarSeGST", "GuardarSeGST", "EliminarSeGST", "BuscarDN", "GuardarDN",
    "ActualizarDN", "EliminarDN", "BuscarAAG", "GuardarAAG", "EliminarAAG", "ActualizarOrden", "ActualizarAAG"
];

$acciones_datos = ["EditarHorarios", "EditarGST", "EditarDN", "BuscarCodigoSeccion"];

if (in_array($_POST['accion'] ?? '', $acciones_generales)) {
    echo json_encode([
        "respuesta" => $respuestaOK,
        "mensaje"   => $mensajeError,
        "contenido" => $contenidoOK
    ]);
} elseif (in_array($_POST['accion'] ?? '', $acciones_datos)) {
    echo json_encode($datos);
}
?>