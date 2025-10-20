<?php
// <-- NUEVO ARCHIVO: CrearNominaEstudiante.php -->

// Set timezone
date_default_timezone_set('America/El_Salvador');
header('Content-Type: application/json; charset=utf-8'); // Output JSON

// --- INCLUDES ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/funciones_2.php"; // Needed for cambiaf_a_normal, replace_3 etc.
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php"; // Needed for $dblink and CrearDirectorios
require_once $path_root . "/registro_academico/vendor/autoload.php"; // PhpSpreadsheet

// Load PhpSpreadsheet classes
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Default JSON response
$respuesta = [
    "respuesta" => false,
    "mensaje" => "Petición inválida.",
    "contenido" => ""
];

try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }

    $codigo_all = $_REQUEST["todos"] ?? null; // Acepta GET o POST
    if (!$codigo_all) { throw new Exception("Faltan parámetros (código de grupo)."); }

    // --- 1. OBTENER DATOS DEL ENCABEZADO ---
    $sqlHeader = "SELECT btrim(bach.nombre) as nombre_bachillerato, btrim(gan.nombre) as nombre_grado,
                  btrim(sec.nombre) as nombre_seccion, ann.nombre as nombre_ann_lectivo,
                  bach.codigo as codigo_bachillerato, ann.codigo as codigo_ann_lectivo
                  FROM alumno_matricula am
                  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                  WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all
                  LIMIT 1";
    $stmtHeader = $dblink->prepare($sqlHeader);
    $stmtHeader->bindParam(':codigo_all', $codigo_all);
    $stmtHeader->execute();
    $headerData = $stmtHeader->fetch(PDO::FETCH_ASSOC);

    if (!$headerData) { throw new Exception("No se encontraron datos para el encabezado del grupo."); }

    $nombre_grado = $headerData['nombre_grado'];
    $nombre_seccion = $headerData['nombre_seccion'];
    $codigo_bachillerato = $headerData['codigo_bachillerato'];
    $nombre_ann_lectivo = $headerData['nombre_ann_lectivo'];

    // --- 2. OBTENER DATOS SIMPLIFICADOS DE ESTUDIANTES ---
    $sqlData = "SELECT
            a.codigo_nie,
            btrim(trim(a.apellido_paterno) || ' ' || trim(a.apellido_materno) || ', ' || a.nombre_completo) as apellido_alumno,
            EXTRACT(YEAR FROM AGE(a.fecha_nacimiento)) as edad,
            CASE WHEN a.codigo_genero = '01' THEN 'M' ELSE 'F' END as genero_estudiante
            FROM alumno a
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
            WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all
            ORDER BY apellido_alumno";

    $stmtData = $dblink->prepare($sqlData);
    $stmtData->bindParam(':codigo_all', $codigo_all);
    $stmtData->execute();
    $studentData = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    if (empty($studentData)) { throw new Exception("No se encontraron estudiantes para este grupo."); }

    // --- 3. CREAR UN NUEVO ARCHIVO EXCEL ---
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Escribir encabezados del reporte
    $sheet->setCellValue('A1', 'Institución:');
    $sheet->setCellValue('B1', $_SESSION['institucion'] ?? '');
    $sheet->setCellValue('A2', 'Modalidad:');
    $sheet->setCellValue('B2', $headerData['nombre_bachillerato']);
    $sheet->setCellValue('A3', 'Grado:');
    $sheet->setCellValue('B3', $nombre_grado);
    $sheet->setCellValue('C3', 'Sección:');
    $sheet->setCellValue('D3', $nombre_seccion);
    $sheet->setCellValue('E3', 'Año Lectivo:');
    $sheet->setCellValue('F3', $nombre_ann_lectivo);
    $sheet->mergeCells('B1:F1'); // Combinar celdas para el nombre largo

    // Estilo básico para encabezados
    $headerStyle = [
        'font' => ['bold' => true, 'size' => 10],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
    ];
    $sheet->getStyle('A1:A3')->applyFromArray($headerStyle);
    $sheet->getStyle('C3')->applyFromArray($headerStyle);
    $sheet->getStyle('E3')->applyFromArray($headerStyle);

    // Escribir encabezados de la tabla
    $sheet->setCellValue('A5', 'Nº');
    $sheet->setCellValue('B5', 'NIE');
    $sheet->setCellValue('C5', 'Nombre del Estudiante');
    $sheet->setCellValue('D5', 'Edad');
    $sheet->setCellValue('E5', 'Género');

    // Estilo para encabezados de tabla
    $tableHeaderStyle = [
        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
        'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ];
    $sheet->getStyle('A5:E5')->applyFromArray($tableHeaderStyle);

    // Llenar datos de estudiantes
    $num = 0;
    $fila_excel = 5; // Fila inicial de datos
    foreach ($studentData as $row) {
        $num++;
        $fila_excel++;
        $sheet->setCellValue("A" . $fila_excel, $num);
        $sheet->setCellValueExplicit("B" . $fila_excel, $row['codigo_nie'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue("C" . $fila_excel, $row['apellido_alumno']);
        $sheet->setCellValue("D" . $fila_excel, $row['edad']);
        $sheet->setCellValue("E" . $fila_excel, $row['genero_estudiante']);
    }

    // Ajustar ancho de columnas
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(50);
    $sheet->getColumnDimension('D')->setWidth(8);
    $sheet->getColumnDimension('E')->setWidth(10);

    // --- 4. GUARDAR EL ARCHIVO ---
    $codigo_destino = 1; // Mantenemos el mismo destino que la nómina completa
    CrearDirectorios($path_root, $nombre_ann_lectivo, $codigo_bachillerato, $codigo_destino, "");

    if (empty($DestinoArchivo) || !is_dir($DestinoArchivo)) {
        throw new Exception("El directorio de destino no es válido o no se pudo crear: " . ($DestinoArchivo ?? 'No definido'));
    }

    // Nombre de archivo ligeramente diferente
    $nombre_archivo_base = replace_3("NominaSimple-" . $codigo_bachillerato . "-". $nombre_grado ."-".$nombre_seccion.".xlsx");
    $rutaCompletaArchivo = $DestinoArchivo . $nombre_archivo_base;

    $writer = new Xlsx($spreadsheet);
    $writer->save($rutaCompletaArchivo);

    if (!file_exists($rutaCompletaArchivo)) { throw new Exception("No se pudo guardar el archivo Excel en el servidor."); }
    chmod($rutaCompletaArchivo, 0777);

    $respuesta['respuesta'] = true;
    $respuesta['mensaje'] = "Archivo Excel (Nómina Simple) generado correctamente.";
    $respuesta['contenido'] = "Archivo guardado como: " . basename($rutaCompletaArchivo);

} catch (PDOException $e) {
    $respuesta['mensaje'] = "Error de Base de Datos: " . $e->getMessage();
} catch (Exception $e) {
    $respuesta['mensaje'] = "Error: " . $e->getMessage();
}

// Enviar respuesta JSON
echo json_encode($respuesta);
?>