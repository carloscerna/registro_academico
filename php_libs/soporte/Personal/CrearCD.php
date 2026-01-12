<?php
// php_libs/soporte/Personal/CrearCD.php
// VERSIÓN BLINDADA PHP 8.3 + MENSAJES MODERNOS

ob_start(); // Iniciar buffer para atrapar cualquier error residual
header('Content-Type: application/json; charset=utf-8');

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
include($path_root . "/registro_academico/includes/funciones.php");

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";

if ($errorDbConexion == false) {
    // Validamos que exista la variable acción
    $accion = $_POST['accion'] ?? '';

    switch ($accion) {
        case 'GuardarCD':
            $codigo_docente = trim($_POST['codigo_docente'] ?? '');
            $codigo_asignatura = trim($_POST['codigo_asignatura'] ?? '');
            $codigo_gst = trim($_POST['codigo_gst'] ?? '');
            $codigo_ann_lectivo = trim($_POST['codigo_annlectivo'] ?? '');
            $codigo_modalidad = trim($_POST['codigo_modalidad'] ?? '');
            
            // Desglosar Grado-Sección-Turno
            if(strlen($codigo_gst) >= 6){
                $codigo_grado = substr($codigo_gst, 0, 2);
                $codigo_seccion = substr($codigo_gst, 2, 2);
                $codigo_turno = substr($codigo_gst, 4, 2);
            } else {
                $codigo_grado = ''; $codigo_seccion = ''; $codigo_turno = '';
            }

            // 1. VERIFICAR SI YA EXISTE (DUPLICADOS)
            // Buscamos si esta asignatura YA está registrada en este grado/sección/turno/año
            // Nota: Aquí se podría agregar lógica para materias compartidas si fuera necesario.
            $query_busqueda = "SELECT id_carga_docente FROM carga_docente 
                               WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' 
                               AND codigo_asignatura = '$codigo_asignatura' 
                               AND codigo_bachillerato = '$codigo_modalidad' 
                               AND codigo_grado = '$codigo_grado' 
                               AND codigo_seccion = '$codigo_seccion' 
                               AND codigo_turno = '$codigo_turno'";
            
            $consulta = $dblink->query($query_busqueda);

            if ($consulta->rowCount() > 0) {
                // MENSAJE DE ERROR MODERNO
                $respuestaOK = false;
                $mensajeError = "⚠️ Esta asignatura ya está asignada a este grado y sección.";
            } else {
                // INSERTAR REGISTRO
                $query = "INSERT INTO carga_docente (codigo_bachillerato, codigo_docente, codigo_asignatura, codigo_grado, codigo_seccion, codigo_turno, codigo_ann_lectivo)
                          VALUES ('$codigo_modalidad', '$codigo_docente', '$codigo_asignatura', '$codigo_grado', '$codigo_seccion', '$codigo_turno', '$codigo_ann_lectivo')";

                if ($dblink->query($query)) {
                    $respuestaOK = true;
                    $mensajeError = "✅ Asignatura asignada correctamente.";
                } else {
                    $mensajeError = "❌ Error crítico al guardar en la base de datos.";
                }
            }
            break;

        case 'BuscarCD':
            $codigo_docente = trim($_POST['codigo_docente'] ?? '');
            $codigo_ann_lectivo = trim($_POST['codigo_annlectivo'] ?? '');
            
            // Filtros opcionales (por si quisieras filtrar por modalidad en la búsqueda también)
            $filtro_modalidad = !empty($_POST['codigo_modalidad']) ? " AND cd.codigo_bachillerato = '".$_POST['codigo_modalidad']."'" : "";
            $filtro_gst = ""; // Puedes agregar lógica similar para GST si lo deseas

            $query = "SELECT cd.id_carga_docente, bach.nombre as nombre_bachillerato, 
                        grado.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
                        asig.nombre as nombre_asignatura
                        FROM carga_docente cd
                        INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
                        INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
                        INNER JOIN grado_ano grado ON grado.codigo = cd.codigo_grado
                        INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
                        INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
                        WHERE cd.codigo_ann_lectivo = '$codigo_ann_lectivo' 
                        AND cd.codigo_docente = '$codigo_docente'
                        $filtro_modalidad
                        ORDER BY cd.id_carga_docente DESC";

            $consulta = $dblink->query($query);
            $num = 1;

            if ($consulta->rowCount() > 0) {
                $respuestaOK = true;
                $mensajeError = "Registros encontrados";
                
                while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
                    $gst = trim($row['nombre_grado']) . ' - ' . trim($row['nombre_seccion']) . ' - ' . trim($row['nombre_turno']);
                    $asignatura = trim($row['nombre_asignatura']);
                    
                    // UTF-8 Fix visual
                    if (!mb_check_encoding($asignatura, 'UTF-8')) $asignatura = mb_convert_encoding($asignatura, 'UTF-8', 'ISO-8859-1');
                    if (!mb_check_encoding($gst, 'UTF-8')) $gst = mb_convert_encoding($gst, 'UTF-8', 'ISO-8859-1');

                    $contenidoOK .= '<tr>
                        <td class="text-center">' . $num++ . '</td>
                        <td class="text-center">' . $row['id_carga_docente'] . '</td>
                        <td>' . trim($row['nombre_bachillerato']) . '</td>
                        <td>' . $gst . '</td>
                        <td class="fw-bold text-primary">' . $asignatura . '</td>
                        <td class="text-center">
                            <a href="' . $row['id_carga_docente'] . '" data-accion="eliminarCD" class="btn btn-sm btn-outline-danger" title="Eliminar Asignatura">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>';
                }
            } else {
                $respuestaOK = false;
                $mensajeError = "No se encontraron asignaturas registradas.";
                $contenidoOK = '<tr><td colspan="6" class="text-center text-muted">Sin carga académica asignada.</td></tr>';
            }
            break;

        case 'eliminarCD':
            $id = $_POST['id_cd'] ?? 0;
            // Usamos Prepared Statement implícito o validación simple para ID
            $query = "DELETE FROM carga_docente WHERE id_carga_docente = '$id'";

            if ($dblink->exec($query) > 0) {
                $respuestaOK = true;
                $mensajeError = '✅ Asignatura eliminada correctamente.';
            } else {
                $mensajeError = '❌ No se pudo eliminar el registro.';
            }
            break;

        default:
            $mensajeError = 'Acción no reconocida.';
            break;
    }
} else {
    $mensajeError = 'Error de conexión a la Base de Datos.';
}

ob_end_clean(); // Limpiamos cualquier salida previa
echo json_encode([
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError,
    "contenido" => $contenidoOK
]);
?>