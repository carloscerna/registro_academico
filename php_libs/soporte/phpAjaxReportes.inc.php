<?php
// Usar el estándar moderno UTF-8 y declarar que la salida es JSON.
header('Content-Type: application/json; charset=utf-8');

// Definir la estructura de la respuesta JSON por defecto.
$respuesta = [
    "respuesta" => false,
    "mensaje" => "Petición inválida.",
    "contenido" => "",
    "codigoGrado" => []
];

// Incluir archivos necesarios.
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";

// Verificar si la conexión a la BD falló.
if ($errorDbConexion) {
    $respuesta['mensaje'] = 'Error: No se puede conectar a la base de datos.';
    echo json_encode($respuesta);
    exit;
}

// Obtener datos del POST de forma segura.
$accion = $_POST['accion_buscar'] ?? $_POST['accion'] ?? null;
$codigo_annlectivo = $_POST['lstannlectivo'] ?? null;
$codigo_modalidad = $_POST['lstmodalidad'] ?? null;

// Validar que la acción y los datos necesarios existan.
if ($accion !== 'BuscarUser' || !$codigo_annlectivo || !$codigo_modalidad) {
    $respuesta['mensaje'] = 'Error: Faltan parámetros para realizar la búsqueda.';
    echo json_encode($respuesta);
    exit;
}

try {
    // --- CONSULTA SEGURA CON PREPARED STATEMENTS ---
    // El SQL tiene '?' como marcadores de posición para las variables.
    $sql = "SELECT orgs.codigo_bachillerato, orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_ann_lectivo, orgs.codigo_turno,
            btrim(per.nombres || ' ' || per.apellidos) as nombre_docente,
            bach.nombre as nombre_bachillerato, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, 
            ann.nombre as nombre_ann_lectivo, tur.nombre as nombre_turno
            FROM organizacion_grados_secciones orgs
            INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgs.codigo_bachillerato
            INNER JOIN grado_ano gan ON gan.codigo = orgs.codigo_grado
            INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
            INNER JOIN ann_lectivo ann ON ann.codigo = orgs.codigo_ann_lectivo
            INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
            INNER JOIN encargado_grado encargado ON encargado.codigo_bachillerato = orgs.codigo_bachillerato 
                AND encargado.codigo_grado = orgs.codigo_grado 
                AND encargado.codigo_seccion = orgs.codigo_seccion 
                AND encargado.codigo_ann_lectivo = orgs.codigo_ann_lectivo
            INNER JOIN personal per ON per.id_personal = encargado.codigo_docente
            WHERE orgs.codigo_bachillerato = ? AND orgs.codigo_ann_lectivo = ?
            ORDER BY orgs.codigo_grado, orgs.codigo_seccion ASC";

    // 1. Preparar la consulta.
    $stmt = $dblink->prepare($sql);
    
    // 2. Ejecutar la consulta pasando las variables de forma segura en un array.
    $stmt->execute([$codigo_modalidad, $codigo_annlectivo]);

    if ($stmt->rowCount() > 0) {
        $respuesta['respuesta'] = true;
        $num = 0;
        $contenidoHTML = "";
        $datosGrados = [];
        $nombre_bachillerato = "";

        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $num++;
            $nombre_bachillerato = $fila['nombre_bachillerato']; // Guardar para el mensaje final.

		// Código Corregido (la solución):
		$report_code = trim($fila['codigo_bachillerato']) . trim($fila['codigo_grado']) . trim($fila['codigo_seccion']) . trim($fila['codigo_ann_lectivo']) . trim($fila['codigo_turno']);

            // Construir el HTML para la fila de la tabla.
            $contenidoHTML .= '<tr>'
                . '<td>' . $num . '</td>'
                . '<td><label class="fw-bold">' . htmlspecialchars($fila['nombre_grado'] . ' - ' . $fila['nombre_seccion'] . ' - ' . $fila['nombre_turno']) . '</label>'
                . '<br><small>Encargado: <span class="badge bg-secondary">' . htmlspecialchars($fila['nombre_docente']) . '</span></small>'
                . '</td>'
                // --- BOTONES COMPATIBLES CON EL NUEVO JS ---
                . '<td><a href="#" class="btn btn-sm btn-info report-link" data-report-type="nominas" data-report-code="' . htmlspecialchars($report_code) . '" title="Imprimir Nómina"><i class="fas fa-print"></i> Imprimir</a></td>'
                . '<td><a href="#" class="btn btn-sm btn-info report-link" data-report-type="notas" data-report-code="' . htmlspecialchars($report_code) . '" title="Imprimir Reporte de Notas"><i class="fas fa-print"></i> Imprimir</a></td>'
                . '</tr>';

            // Almacenar grados para el dropdown (se filtrarán duplicados después).
            $datosGrados[] = [
                "codigo" => trim($fila['codigo_grado']),
                "descripcion" => trim($fila['nombre_grado'])
            ];
        }

        $respuesta['mensaje'] = 'Se encontraron ' . $num . ' grupos para: ' . htmlspecialchars($nombre_bachillerato);
        $respuesta['contenido'] = $contenidoHTML;

        // Filtrar grados duplicados para el select de 'Por Asignatura'.
        $uniqueData = [];
        foreach ($datosGrados as $item) {
            $uniqueData[$item['codigo']] = $item;
        }
        $respuesta['codigoGrado'] = array_values($uniqueData);

    } else {
        $respuesta['respuesta'] = true; // La consulta fue exitosa, pero no arrojó resultados.
        $respuesta['mensaje'] = "No se encontraron registros para los filtros seleccionados.";
        $respuesta['contenido'] = '<tr><td colspan="4" class="text-center">No hay grupos para mostrar.</td></tr>';
    }

} catch (PDOException $e) {
    // En un entorno de producción, registrarías este error en un archivo de log.
    // error_log($e->getMessage());
    $respuesta['mensaje'] = "Error Crítico: No se pudo procesar la solicitud.";
}

// Imprimir la respuesta final en formato JSON.
echo json_encode($respuesta);
?>