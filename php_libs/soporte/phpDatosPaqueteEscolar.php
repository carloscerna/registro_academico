<?php
// Limpiar cache.
clearstatcache();
header("Content-Type: application/json;charset=utf-8");
sleep(0);

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$resumenOK = [];

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
    
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
include($path_root . "/registro_academico/includes/funciones.php");

if ($errorDbConexion == false) {
    if (isset($_POST) && !empty($_POST)) {
        if (!empty($_POST['accion_buscar'])) {
            $_POST['accion'] = $_POST['accion_buscar'];
        }

        switch ($_POST['accion']) {
            case 'BuscarListaPaqueteEscolar':
                $codigo_annlectivo = $_POST["lstannlectivo"];
                $codigo_modalidad = $_POST["lstmodalidad"];
                
                $grado_seccion_turno = $_POST["lstgradoseccion"];
                $codigo_grado = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno = substr($grado_seccion_turno, 4, 2);
                        
                // Obtener lista de alumnos matriculados junto con su paquete asignado en el año lectivo
                $query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                            pe.id_alumno_paquete, pe.posee_tablet, pe.tablet_serie, pe.posee_computadora_portatil, pe.computadora_serie,
                            pe.posee_utiles_escolares, pe.posee_zapatos, pe.talla_zapatos,
                            pe.posee_blusas, pe.talla_blusas, pe.posee_camisa, pe.talla_camisa,
                            pe.posee_pantalon, pe.talla_pantalon, pe.posee_short, pe.talla_short,
                            pe.estado_entrega, pe.fecha_entrega, pe.observacion
                            FROM alumno a
                            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                            LEFT JOIN alumno_paquete_escolar pe ON a.id_alumno = pe.id_alumno AND pe.anio_lectivo = :annlectivo_num
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
                
                // Convertir el código de año lectivo si es necesario o castearlo como integer
                $ann_num = (int)$codigo_annlectivo;
                $stmt->bindParam(':annlectivo_num', $ann_num, PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() != 0) {
                    $respuestaOK = true;
                    $num = 0;
                    
                    // Inicializar métricas para el resumen
                    $count_tablet = 0;
                    $count_laptop = 0;
                    $count_utiles = 0;
                    $count_zapatos = 0;
                    $count_completos = 0;
                    $count_parciales = 0;
                    $count_pendientes = 0;

                    while ($listado = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $num++;
                        
                        $chk_tablet = ($listado['posee_tablet'] == 't') ? 'checked' : '';
                        if ($listado['posee_tablet'] == 't') $count_tablet++;
                        
                        $chk_computadora = ($listado['posee_computadora_portatil'] == 't') ? 'checked' : '';
                        if ($listado['posee_computadora_portatil'] == 't') $count_laptop++;
                        
                        $chk_utiles = ($listado['posee_utiles_escolares'] == 't') ? 'checked' : '';
                        if ($listado['posee_utiles_escolares'] == 't') $count_utiles++;
                        
                        $chk_zapatos = ($listado['posee_zapatos'] == 't') ? 'checked' : '';
                        if ($listado['posee_zapatos'] == 't') $count_zapatos++;

                        $chk_blusas = ($listado['posee_blusas'] == 't') ? 'checked' : '';
                        $chk_camisa = ($listado['posee_camisa'] == 't') ? 'checked' : '';
                        $chk_pantalon = ($listado['posee_pantalon'] == 't') ? 'checked' : '';
                        $chk_short = ($listado['posee_short'] == 't') ? 'checked' : '';

                        $chk_falda = ($listado['posee_falda'] == 't') ? 'checked' : '';

                        // Conteo del estado de la entrega
                        $estado = $listado['estado_entrega'] ?? 'Pendiente';
                        if ($estado == 'Entregado Completo') $count_completos++;
                        elseif ($estado == 'Entregado Parcial') $count_parciales++;
                        else $count_pendientes++;

                        // Opciones de estado
                        $opt_pendiente = ($estado == 'Pendiente') ? 'selected' : '';
                        $opt_parcial = ($estado == 'Entregado Parcial') ? 'selected' : '';
                        $opt_completo = ($estado == 'Entregado Completo') ? 'selected' : '';

                        $select_estado = '<select name="lst_estado_entrega" class="form-select form-select-sm">
                            <option value="Pendiente" ' . $opt_pendiente . '>Pendiente</option>
                            <option value="Entregado Parcial" ' . $opt_parcial . '>Parcial</option>
                            <option value="Entregado Completo" ' . $opt_completo . '>Completo</option>
                        </select>';

                        $id_paquete = $listado['id_alumno_paquete'] ?? 0;

                        $contenidoOK .= '<tr data-id-alumno="' . $listado['id_alumno'] . '" data-id-alumno-paquete="' . $id_paquete . '">
                            <td class="text-center">' . $num . '</td>
                            <td>' . $listado['codigo_nie'] . ' - ' . $listado['apellido_alumno'] . '</td>
                            
                            <!-- Tablet y Serie -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_tablet" ' . $chk_tablet . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-serie" name="txt_tablet_serie" value="' . htmlspecialchars($listado['tablet_serie'] ?? '') . '" placeholder="Serie">
                            </td>

                            <!-- Laptop y Serie -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_computadora" ' . $chk_computadora . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-serie" name="txt_computadora_serie" value="' . htmlspecialchars($listado['computadora_serie'] ?? '') . '" placeholder="Serie">
                            </td>

                            <!-- Útiles -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" name="chk_utiles" ' . $chk_utiles . '>
                                </div>
                            </td>

                            <!-- Zapatos -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_zapatos" ' . $chk_zapatos . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-talla mx-auto" name="txt_talla_zapatos" value="' . htmlspecialchars($listado['talla_zapatos'] ?? '') . '" placeholder="Talla">
                            </td>

                            <!-- Blusas -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_blusas" ' . $chk_blusas . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-talla mx-auto" name="txt_talla_blusas" value="' . htmlspecialchars($listado['talla_blusas'] ?? '') . '" placeholder="Talla">
                            </td>

                            <!-- Camisa -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_camisa" ' . $chk_camisa . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-talla mx-auto" name="txt_talla_camisa" value="' . htmlspecialchars($listado['talla_camisa'] ?? '') . '" placeholder="Talla">
                            </td>

                            <!-- Pantalón -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_pantalon" ' . $chk_pantalon . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-talla mx-auto" name="txt_talla_pantalon" value="' . htmlspecialchars($listado['talla_pantalon'] ?? '') . '" placeholder="Talla">
                            </td>

                            <!-- Short -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center mb-1">
                                    <input class="form-check-input" type="checkbox" name="chk_short" ' . $chk_short . '>
                                </div>
                                <input type="text" class="form-control form-control-sm input-talla mx-auto" name="txt_talla_short" value="' . htmlspecialchars($listado['talla_short'] ?? '') . '" placeholder="Talla">
                            </td>
                            <!-- Celda de Falda -->
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center mb-1">
                                        <input class="form-check-input" type="checkbox" name="chk_falda" ' . $chk_falda . '>
                                    </div>
                                    <input type="text" class="form-control form-control-sm input-talla mx-auto" name="txt_talla_falda" value="' . htmlspecialchars($listado['talla_falda'] ?? '') . '" placeholder="Talla">
                                </td>
                            <!-- Estado Entrega -->
                            <td>' . $select_estado . '</td>

                            <!-- Fecha Entrega -->
                            <td><input type="date" class="form-control form-control-sm" name="txt_fecha_entrega" value="' . htmlspecialchars($listado['fecha_entrega'] ?? '') . '"></td>

                            <!-- Observaciones -->
                            <td><input type="text" class="form-control form-control-sm" name="txt_observacion" value="' . htmlspecialchars($listado['observacion'] ?? '') . '" placeholder="Notas"></td>
                        </tr>';
                    }
                    
                    $resumenOK = [
                        'tablet' => $count_tablet,
                        'laptop' => $count_laptop,
                        'utiles' => $count_utiles,
                        'zapatos' => $count_zapatos,
                        'completos' => $count_completos,
                        'parciales' => $count_parciales,
                        'pendientes' => $count_pendientes,
                        'total' => $num
                    ];
                    $mensajeError = "Si Registro";
                } else {
                    $respuestaOK = false;
                    $mensajeError = 'No se encontraron registros para este grupo.';
                }
                break;

            case 'ActualizarDatosPaqueteEscolar':
                $total_filas = $_POST['total_filas'];
                $anio_lectivo = (int)$_POST['anio_lectivo'];
                
                $id_alumnos = $_POST['id_alumno'];
                $id_paquetes = $_POST['id_alumno_paquete'];

                $dblink->beginTransaction();
                try {
                    for ($i = 0; $i < $total_filas; $i++) {
                        $id_alumno = $id_alumnos[$i];
                        $id_paquete = (int)$id_paquetes[$i];

                        $posee_tablet = ($_POST['posee_tablet'][$i] === 'true') ? 't' : 'f';
                        $tablet_serie = $_POST['tablet_serie'][$i];
                        
                        $posee_computadora = ($_POST['posee_computadora_portatil'][$i] === 'true') ? 't' : 'f';
                        $computadora_serie = $_POST['computadora_serie'][$i];

                        $posee_utiles = ($_POST['posee_utiles_escolares'][$i] === 'true') ? 't' : 'f';

                        $posee_zapatos = ($_POST['posee_zapatos'][$i] === 'true') ? 't' : 'f';
                        $talla_zapatos = $_POST['talla_zapatos'][$i];

                        $posee_blusas = ($_POST['posee_blusas'][$i] === 'true') ? 't' : 'f';
                        $talla_blusas = $_POST['talla_blusas'][$i];

                        $posee_camisa = ($_POST['posee_camisa'][$i] === 'true') ? 't' : 'f';
                        $talla_camisa = $_POST['talla_camisa'][$i];

                        $posee_pantalon = ($_POST['posee_pantalon'][$i] === 'true') ? 't' : 'f';
                        $talla_pantalon = $_POST['talla_pantalon'][$i];

                        $posee_short = ($_POST['posee_short'][$i] === 'true') ? 't' : 'f';
                        $talla_short = $_POST['talla_short'][$i];

                        $posee_falda = ($_POST['posee_falda'][$i] === 'true') ? 't' : 'f';
                        $talla_falda = $_POST['talla_falda'][$i];

                        $estado_entrega = $_POST['estado_entrega'][$i];
                        $fecha_entrega = !empty($_POST['fecha_entrega'][$i]) ? $_POST['fecha_entrega'][$i] : NULL;
                        $observacion = $_POST['observacion'][$i];

                        // Insertar o actualizar según si existe el registro previo
                        if ($id_paquete > 0) {
                            $query_save = "UPDATE alumno_paquete_escolar SET
                                posee_tablet = :posee_tablet,
                                tablet_serie = :tablet_serie,
                                posee_computadora_portatil = :posee_computadora_portatil,
                                computadora_serie = :computadora_serie,
                                posee_utiles_escolares = :posee_utiles_escolares,
                                posee_zapatos = :posee_zapatos,
                                talla_zapatos = :talla_zapatos,
                                posee_blusas = :posee_blusas,
                                talla_blusas = :talla_blusas,
                                posee_camisa = :posee_camisa,
                                talla_camisa = :talla_camisa,
                                posee_pantalon = :posee_pantalon,
                                talla_pantalon = :talla_pantalon,
                                posee_short = :posee_short,
                                talla_short = :talla_short,
                                posee_falda = :posee_falda,
                                talla_falda = :talla_falda,
                                estado_entrega = :estado_entrega,
                                fecha_entrega = :fecha_entrega,
                                observacion = :observacion,
                                updated_at = CURRENT_TIMESTAMP
                                WHERE id_alumno_paquete = :id_alumno_paquete";
                            
                            $stmt_save = $dblink->prepare($query_save);
                            $stmt_save->bindParam(':id_alumno_paquete', $id_paquete);
                        } else {
                            $query_save = "INSERT INTO alumno_paquete_escolar (
                                id_alumno, anio_lectivo, posee_tablet, tablet_serie,
                                posee_computadora_portatil, computadora_serie, posee_utiles_escolares,
                                posee_zapatos, talla_zapatos, posee_blusas, talla_blusas,
                                posee_camisa, talla_camisa, posee_pantalon, talla_pantalon,
                                posee_short, talla_short, posee_falda, talla_falda, estado_entrega, fecha_entrega, observacion
                            ) VALUES (
                                :id_alumno, :anio_lectivo, :posee_tablet, :tablet_serie,
                                :posee_computadora_portatil, :computadora_serie, :posee_utiles_escolares,
                                :posee_zapatos, :talla_zapatos, :posee_blusas, :talla_blusas,
                                :posee_camisa, :talla_camisa, :posee_pantalon, :talla_pantalon,
                                :posee_short, :talla_short, :posee_falda, :talla_falda, :estado_entrega, :fecha_entrega, :observacion
                            )";
                            
                            $stmt_save = $dblink->prepare($query_save);
                            $stmt_save->bindParam(':id_alumno', $id_alumno);
                            $stmt_save->bindParam(':anio_lectivo', $anio_lectivo, PDO::PARAM_INT);
                        }

                        // Binding común
                        $stmt_save->bindParam(':posee_tablet', $posee_tablet);
                        $stmt_save->bindParam(':tablet_serie', $tablet_serie);
                        $stmt_save->bindParam(':posee_computadora_portatil', $posee_computadora);
                        $stmt_save->bindParam(':computadora_serie', $computadora_serie);
                        $stmt_save->bindParam(':posee_utiles_escolares', $posee_utiles);
                        $stmt_save->bindParam(':posee_zapatos', $posee_zapatos);
                        $stmt_save->bindParam(':talla_zapatos', $talla_zapatos);
                        $stmt_save->bindParam(':posee_blusas', $posee_blusas);
                        $stmt_save->bindParam(':talla_blusas', $talla_blusas);
                        $stmt_save->bindParam(':posee_camisa', $posee_camisa);
                        $stmt_save->bindParam(':talla_camisa', $talla_camisa);
                        $stmt_save->bindParam(':posee_pantalon', $posee_pantalon);
                        $stmt_save->bindParam(':talla_pantalon', $talla_pantalon);
                        $stmt_save->bindParam(':posee_short', $posee_short);
                        $stmt_save->bindParam(':talla_short', $talla_short);
                        $stmt_save->bindParam(':posee_falda', $posee_falda);
                        $stmt_save->bindParam(':talla_falda', $talla_falda);
                        $stmt_save->bindParam(':estado_entrega', $estado_entrega);
                        $stmt_save->bindParam(':fecha_entrega', $fecha_entrega);
                        $stmt_save->bindParam(':observacion', $observacion);

                        $stmt_save->execute();
                    }
                    
                    $dblink->commit();
                    $respuestaOK = true;
                    $mensajeError = 'Paquetes escolares actualizados correctamente.';

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
    } else {
        $mensajeError = 'No se puede ejecutar la aplicación';
    }
} else {
    $mensajeError = 'No se puede establecer conexión con la base de datos';
}

$salidaJson = array(
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError,
    "contenido" => $contenidoOK,
    "resumen" => $resumenOK
);

echo json_encode($salidaJson);
?>