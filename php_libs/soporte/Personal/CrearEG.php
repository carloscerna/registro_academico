<?php
// php_libs/soporte/Personal/CrearEG.php
// VERSIÓN BLINDADA PHP 8.3

ob_start(); // Buffer para atrapar errores
header('Content-Type: application/json; charset=utf-8');

// Ajuste de ruta (usando la lógica que ya tienes)
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";

// Validar conexión
if($errorDbConexion == false) {
    
    $accion = $_POST['accion'] ?? ''; // Protección nulos

    switch ($accion) {
		case 'GuardarEG':
            $codigo_docente = trim($_POST['codigo_docente'] ?? '');
            $codigo_gst = trim($_POST['codigo_gst'] ?? '');
            $codigo_ann_lectivo = trim($_POST['codigo_annlectivo'] ?? '');
            $codigo_modalidad = trim($_POST['codigo_modalidad'] ?? '');
            
            if(strlen($codigo_gst) >= 6){
                $codigo_grado = substr($codigo_gst,0,2);
                $codigo_seccion = substr($codigo_gst,2,2);
                $codigo_turno = substr($codigo_gst,4,2);
            } else {
                $codigo_grado = ''; $codigo_seccion = ''; $codigo_turno = '';
            }

            $encargado_grado = ($_POST['encargado_grado'] == 'yes') ? '1' : '0';
            $imparte_asignatura = ($_POST['imparte_asignatura'] == 'yes') ? '1' : '0';

            // 1. VERIFICAR DUPLICADOS
            $query_busqueda = "SELECT id_encargado_grado from encargado_grado
                        WHERE codigo_ann_lectivo = '$codigo_ann_lectivo' 
                        AND codigo_bachillerato = '$codigo_modalidad'
                        AND codigo_grado = '$codigo_grado' 
                        AND codigo_seccion = '$codigo_seccion' 
                        AND codigo_turno = '$codigo_turno' 
                        AND encargado = '$encargado_grado' 
                        AND codigo_docente = '$codigo_docente'";
            
            $consulta = $dblink->query($query_busqueda);

            if($consulta->rowCount() > 0){
                // MENSAJE MODERNO: Descriptivo y con emoji para alerta visual
                $respuestaOK = false;
                $mensajeError = "⚠️ Atención: Este grado ya se encuentra asignado a este docente con el mismo rol.";
            } else {
                $query = "INSERT INTO encargado_grado (codigo_docente, codigo_ann_lectivo, codigo_bachillerato, codigo_grado, codigo_seccion, codigo_turno, encargado, imparte_asignatura)
                VALUES ('$codigo_docente','$codigo_ann_lectivo','$codigo_modalidad','$codigo_grado','$codigo_seccion','$codigo_turno','$encargado_grado','$imparte_asignatura')";

                if($dblink->query($query)){
                    $respuestaOK = true;
                    $mensajeError = "Registro guardado correctamente.";
                } else {
                    $mensajeError = "Error crítico al guardar en la base de datos.";
                }
            }
        break;

        case 'BuscarEG':
            $codigo_docente = trim($_POST['codigo_docente'] ?? '');
            $codigo_ann_lectivo = trim($_POST['codigo_annlectivo'] ?? '');
            
            $query = "SELECT eg.id_encargado_grado, bach.nombre as nombre_bachillerato, 
                        grado.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
                        eg.encargado, eg.imparte_asignatura
                        from encargado_grado eg
                        INNER JOIN bachillerato_ciclo bach ON bach.codigo = eg.codigo_bachillerato
                        INNER JOIN grado_ano grado ON grado.codigo = eg.codigo_grado
                        INNER JOIN seccion sec ON sec.codigo = eg.codigo_seccion
                        INNER JOIN turno tur ON tur.codigo = eg.codigo_turno
                        WHERE eg.codigo_ann_lectivo = '$codigo_ann_lectivo' and eg.codigo_docente = '$codigo_docente'
                        ORDER BY eg.id_encargado_grado DESC";

            $consulta = $dblink->query($query);
            $num = 1;

            if($consulta->rowCount() > 0){
                $respuestaOK = true;
                $mensajeError = "Registros encontrados";
                
                while($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
                    $si_encargado = ($row['encargado'] == '1') ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>';
                    $si_imparte = ($row['imparte_asignatura'] == '1') ? '<span class="badge bg-info text-dark">Sí</span>' : '<span class="badge bg-secondary">No</span>';
                    $gst = $row['nombre_grado'] . ' - ' . $row['nombre_seccion'] . ' - ' . $row['nombre_turno'];

                    $contenidoOK .= '<tr>
                        <td class="text-center">'.$num++.'</td>
                        <td class="text-center">'.$row['id_encargado_grado'].'</td>
                        <td>'.trim($row['nombre_bachillerato']).'</td>
                        <td>'.$gst.'</td>
                        <td class="text-center">'.$si_encargado.'</td>
                        <td class="text-center">'.$si_imparte.'</td>
                        <td class="text-center">
                            <a href="'.$row['id_encargado_grado'].'" data-accion="eliminarEG" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>';
                }
            } else {
                $respuestaOK = false;
                $mensajeError = "No se encontraron registros asignados.";
            }
        break;

        case 'eliminarEG':
            $id = $_POST['id_eg'] ?? 0;
            $query = "DELETE FROM encargado_grado WHERE id_encargado_grado = '$id'";
            
            if($dblink->exec($query) > 0){
                $respuestaOK = true;
                $mensajeError = 'Registro eliminado correctamente.';
            } else {
                $mensajeError = 'No se pudo eliminar el registro. Puede estar vinculado a otros datos.';
            }
        break;
    }
}

ob_end_clean(); // Limpiar buffer
echo json_encode([
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError,
    "contenido" => $contenidoOK
]);
?>