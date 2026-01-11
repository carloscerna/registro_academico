<?php
// <-- VERSIÓN BLINDADA PHP 8.3 -->
ob_start(); // Inicia el búfer para atrapar errores

// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

// FIX LOCAL: Función segura para convertir texto si la global falla
if (!function_exists('convertirTextoSafe')) {
    function convertirTextoSafe($texto) {
        if (function_exists('convertirTexto')) {
            return convertirTexto($texto ?? '');
        }
        // Fallback simple si no existe la función
        return mb_convert_encoding((string)($texto ?? ''), 'ISO-8859-1', 'UTF-8');
    }
}

// Validación inicial de ID
if (!isset($_POST['id_x']) || empty($_POST['id_x'])) {
    echo json_encode(["error" => "ID de alumno no proporcionado"]);
    exit;
}

// armando el Query. PARA LA TABLA ALUMNO.
$query = "SELECT id_alumno, apellido_materno, apellido_paterno, nombre_completo, codigo_nie, direccion_alumno,
	    telefono_alumno, telefono_celular, codigo_departamento, codigo_municipio, partida_nacimiento, fecha_nacimiento, nacionalidad, distancia,
	    pn_numero, pn_folio, pn_tomo, pn_libro, codigo_transporte, medicamento, direccion_email, edad, certificado,
	    partida_nacimiento, tarjeta_vacunacion, genero, foto, estudio_parvularia, codigo_estado_civil,
	    codigo_estado_familiar, codigo_actividad_economica, codigo_apoyo_educativo, codigo_discapacidad, ruta_pn,
	    ruta_pn_vuelto, codigo_zona_residencia, tiene_hijos, cantidad_hijos, codigo_genero, codigo_estatus, dui, pasaporte, codigo_nacionalidad, retornado,
      posee_pn, presenta_pn, codigo_etnia, codigo_diagnostico, embarazada, codigo_tipo_vivienda, codigo_canton, codigo_caserio, servicio_energia, recoleccion_basura,
      codigo_abastecimiento, codigo_departamento_pn, codigo_municipio_pn, codigo_distrito_pn, codigo_distrito
	  FROM alumno
	  WHERE id_alumno = " . $_POST['id_x'];

// armando el Query. PARA LA TABLA HISTORIAL ALUMNO.
$query_historial = "SELECT id_alumno_bitacora, codigo_alumno, fecha_ob, historial
	  FROM alumno_historial
	  WHERE codigo_alumno = " . $_POST['id_x'] . " ORDER BY fecha_ob";

// armando el Query. PARA LA TABLA ALUMNO ENCARGADO.
$query_encargado = "SELECT id_alumno_encargado, codigo_alumno, nombres, lugar_trabajo, profesion_oficio, dui, telefono,
                    direccion, encargado, institucion_proviene, fecha_nacimiento, codigo_nacionalidad, codigo_familiar, codigo_zona, codigo_departamento, codigo_municipio, codigo_distrito
            FROM alumno_encargado WHERE codigo_alumno = " . $_POST['id_x'] . " order by id_alumno_encargado";

// Ejecutamos las consultas
try {
    $consulta = $dblink->query($query);
    $consulta_historial = $dblink->query($query_historial);
    $consulta_encargado = $dblink->query($query_encargado);
} catch (PDOException $e) {
    ob_end_clean();
    echo json_encode(["error" => "Error de base de datos: " . $e->getMessage()]);
    exit;
}

// Inicializando el array
$datos = []; 
$fila_array = 0;
$codigo_institucion = $_SESSION['codigo_institucion'] ?? '00000'; // Protección de sesión

// Recorriendo la Tabla ALUMNO
while ($listado = $consulta->fetch(PDO::FETCH_BOTH)) {
    // PROTECCIÓN PHP 8: trim($var ?? '') en TODOS los campos
    
    // campo de la foto.
    $url_foto = trim($listado['foto'] ?? '');
    $url_pn = trim($listado['ruta_pn'] ?? '');
    
    // Validacion ruta archivo
    $archivo_origen = $path_root . "/registro_academico/img/Pn/" . $codigo_institucion . "/" . $url_pn;
    
    if (empty($url_pn) || !file_exists($archivo_origen)) {
        $url_pn = "foto_no_disponible.jpg";
    } else {
        $url_pn = $codigo_institucion . "/" . $url_pn;
    }

    $id = trim($listado['id_alumno'] ?? '');

    // Rellenando el array con protección
    $datos[$fila_array]["nombre_completo"] = trim($listado['nombre_completo'] ?? '');
    $datos[$fila_array]["apellido_materno"] = trim($listado['apellido_materno'] ?? '');
    $datos[$fila_array]["apellido_paterno"] = trim($listado['apellido_paterno'] ?? '');
    $datos[$fila_array]["direccion_alumno"] = trim($listado['direccion_alumno'] ?? '');
    $datos[$fila_array]["telefono_residencia"] = trim($listado['telefono_alumno'] ?? '');
    $datos[$fila_array]["telefono_celular"] = trim($listado['telefono_celular'] ?? '');
    $datos[$fila_array]["codigo_nie"] = trim($listado['codigo_nie'] ?? '');
    $datos[$fila_array]["medicamento"] = trim($listado['medicamento'] ?? '');

    $datos[$fila_array]["fecha_nacimiento"] = trim($listado['fecha_nacimiento'] ?? '');
    $datos[$fila_array]["partida_nacimiento"] = trim($listado['partida_nacimiento'] ?? '');
    $datos[$fila_array]["edad"] = trim($listado['edad'] ?? '');
    $datos[$fila_array]["dui"] = trim($listado['dui'] ?? '');
    $datos[$fila_array]["pasaporte"] = trim($listado['pasaporte'] ?? '');
    $datos[$fila_array]["codigo_nacionalidad"] = trim($listado['codigo_nacionalidad'] ?? '');
    $datos[$fila_array]["retornado"] = trim($listado['retornado'] ?? '');
    $datos[$fila_array]["posee_pn"] = trim($listado['posee_pn'] ?? '');
    $datos[$fila_array]["presenta_pn"] = trim($listado['presenta_pn'] ?? '');
    $datos[$fila_array]["codigo_departamento_pn"] = trim($listado['codigo_departamento_pn'] ?? '');
    $datos[$fila_array]["codigo_municipio_pn"] = trim($listado['codigo_municipio_pn'] ?? '');
    $datos[$fila_array]["codigo_distrito_pn"] = trim($listado['codigo_distrito_pn'] ?? '');

    $datos[$fila_array]["pn_numero"] = trim($listado['pn_numero'] ?? '');
    $datos[$fila_array]["pn_folio"] = trim($listado['pn_folio'] ?? '');
    $datos[$fila_array]["pn_tomo"] = trim($listado['pn_tomo'] ?? '');
    $datos[$fila_array]["pn_libro"] = trim($listado['pn_libro'] ?? '');

    $datos[$fila_array]["codigo_genero"] = trim($listado['codigo_genero'] ?? '');
    $datos[$fila_array]["codigo_etnia"] = trim($listado['codigo_etnia'] ?? '');

    $datos[$fila_array]["codigo_diagnostico"] = trim($listado['codigo_diagnostico'] ?? '');
    $datos[$fila_array]["codigo_servicio_apoyo_educativo"] = trim($listado['codigo_apoyo_educativo'] ?? '');

    $datos[$fila_array]["codigo_estado_civil"] = trim($listado['codigo_estado_civil'] ?? '');
    $datos[$fila_array]["codigo_departamento"] = trim($listado['codigo_departamento'] ?? '');
    $datos[$fila_array]["codigo_municipio"] = trim($listado['codigo_municipio'] ?? '');
    $datos[$fila_array]["codigo_distrito"] = trim($listado['codigo_distrito'] ?? '');
    $datos[$fila_array]["codigo_estado_familiar"] = trim($listado['codigo_estado_familiar'] ?? '');
    $datos[$fila_array]["codigo_actividad_economica"] = trim($listado['codigo_actividad_economica'] ?? '');
    $datos[$fila_array]["codigo_tipo_discapacidad"] = trim($listado['codigo_discapacidad'] ?? '');

    $datos[$fila_array]["direccion_email"] = trim($listado['direccion_email'] ?? '');
    $datos[$fila_array]["cantidad_hijos"] = trim($listado['cantidad_hijos'] ?? '');

    $datos[$fila_array]["codigo_zona_residencia"] = trim($listado['codigo_zona_residencia'] ?? '');
    
    $datos[$fila_array]["embarazada"] = trim($listado['embarazada'] ?? '');
    $datos[$fila_array]["codigo_tipo_vivienda"] = trim($listado['codigo_tipo_vivienda'] ?? '');
    $datos[$fila_array]["codigo_canton"] = trim($listado['codigo_canton'] ?? '');
    $datos[$fila_array]["codigo_caserio"] = trim($listado['codigo_caserio'] ?? '');
    $datos[$fila_array]["servicio_energia"] = trim($listado['servicio_energia'] ?? '');
    $datos[$fila_array]["recoleccion_basura"] = trim($listado['recoleccion_basura'] ?? '');
    $datos[$fila_array]["codigo_abastecimiento"] = trim($listado['codigo_abastecimiento'] ?? '');
    
    $datos[$fila_array]["codigo_estatus"] = trim($listado['codigo_estatus'] ?? '');
    
    // IDs y Rutas
    $datos[$fila_array]["id_alumno"] = $id;
    $datos[$fila_array]["codigo_institucion"] = $codigo_institucion;
    $datos[$fila_array]["url_foto"] = $url_foto;
    $datos[$fila_array]["url_pn"] = $url_pn;
    $datos[$fila_array]["archivo_origen"] = $archivo_origen;

    $fila_array++;
}

// Recorriendo la Tabla ENCARGADO
if ($consulta_encargado->rowCount() != 0) {
    while ($listadoEncargado = $consulta_encargado->fetch(PDO::FETCH_BOTH)) {
        // PROTECCIÓN PHP 8
        $datos[$fila_array]["id_alumno_encargado"] = trim($listadoEncargado['id_alumno_encargado'] ?? '');
        $datos[$fila_array]["nombres"] = trim($listadoEncargado['nombres'] ?? '');
        $datos[$fila_array]["lugar_trabajo"] = trim($listadoEncargado['lugar_trabajo'] ?? '');
        $datos[$fila_array]["profesion"] = trim($listadoEncargado['profesion_oficio'] ?? '');
        $datos[$fila_array]["dui"] = trim($listadoEncargado['dui'] ?? '');
        $datos[$fila_array]["telefono"] = trim($listadoEncargado['telefono'] ?? '');
        $datos[$fila_array]["direccion"] = trim($listadoEncargado['direccion'] ?? '');
        $datos[$fila_array]["encargado_bollean"] = trim($listadoEncargado['encargado'] ?? '');

        $datos[$fila_array]["fecha_nacimiento"] = trim($listadoEncargado['fecha_nacimiento'] ?? '');
        $datos[$fila_array]["codigo_nacionalidad"] = trim($listadoEncargado['codigo_nacionalidad'] ?? '');
        $datos[$fila_array]["codigo_familiar"] = trim($listadoEncargado['codigo_familiar'] ?? '');
        $datos[$fila_array]["codigo_zona"] = trim($listadoEncargado['codigo_zona'] ?? '');
        $datos[$fila_array]["codigo_departamento"] = trim($listadoEncargado['codigo_departamento'] ?? '');
        $datos[$fila_array]["codigo_municipio"] = trim($listadoEncargado['codigo_municipio'] ?? '');
        $datos[$fila_array]["codigo_distrito"] = trim($listadoEncargado['codigo_distrito'] ?? '');
        
        $fila_array++;
    }
}

// Recorriendo la Tabla HISTORIAL
$num = 1;
if ($consulta_historial->rowCount() != 0) {
    while ($listadoHistorial = $consulta_historial->fetch(PDO::FETCH_BOTH)) {
        // PROTECCIÓN PHP 8
        $id_bitacora = trim($listadoHistorial['id_alumno_bitacora'] ?? '');
        $cod_alumno = trim($listadoHistorial['codigo_alumno'] ?? '');
        $fecha_raw = trim($listadoHistorial['fecha_ob'] ?? '');
        $historial_raw = trim($listadoHistorial['historial'] ?? '');
        
        $fecha_ob = function_exists('cambiaf_a_normal') ? cambiaf_a_normal($fecha_raw) : $fecha_raw;
        $historial = convertirTextoSafe($historial_raw);

        $datos[$fila_array]["todos"] = '<tr><td>' . trim((string)$num) . '<td>' . $id_bitacora . '<td>' . $cod_alumno . '<td>' . $fecha_ob .
            '<td><textarea class=form-control rows=2 disabled>' . $historial . '</textarea></td>'
            . '<td class = centerTXT><a data-accion=editar class="btn btn-xs btn-primary" href=' . $id_bitacora . '>Editar</a>'
            . '<td><a data-accion=eliminarHistorial class="btn btn-xs btn-warning" href=' . $id_bitacora . '>Eliminar</a></tr>';

        $fila_array++;
        $num++;
    }
} else {
    $datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
}

// FINALIZAR Y ENVIAR JSON
ob_end_clean(); // Limpiar cualquier warning previo
echo json_encode($datos);
?>