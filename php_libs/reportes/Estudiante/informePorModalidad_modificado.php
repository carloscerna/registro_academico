<?php
//dss//// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
// Incluir la librería FPDF
require_once $_SERVER['DOCUMENT_ROOT'] . "/registro_academico/php_libs/fpdf/fpdf.php";
include($path_root . "/registro_academico/includes/funciones.php");
// Incluir el archivo de conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . "/registro_academico/includes/mainFunctions_conexion.php");
// cambiar a utf-8.
header("Content-Type: text/html; charset=UTF-8");


$pdo = $dblink;

// Obtener los datos necesarios para el informe
// Asegúrate de sanitizar y validar las entradas ($_GET) para prevenir inyecciones SQL
$modalidad = $_GET['modalidad'];
$gradoseccion = $_GET['gradoseccion'];
$annlectivo = $_GET['annlectivo'];

// Construcción de códigos desde el formulario
$codigo_all = $modalidad . substr($gradoseccion, 0, 4) . $annlectivo;
$codigo_bachillerato = substr($codigo_all, 0, 2);
$codigo_grado = substr($codigo_all, 2, 2);
$codigo_seccion = substr($codigo_all, 4, 2);
$codigo_annlectivo = substr($codigo_all, 6, 2);

// Obtener la cantidad de períodos para la modalidad
$query_periodos = "SELECT cantidad_periodos FROM catalogo_periodos WHERE codigo_modalidad = :codigo_modalidad";
$stmt_periodos = $pdo->prepare($query_periodos);
$stmt_periodos->bindParam(':codigo_modalidad', $modalidad);
$stmt_periodos->execute();
$cantidad_periodos = $stmt_periodos->fetchColumn();

// Obtener las asignaturas de la modalidad
$sql_asignaturas = "
    SELECT
        TRIM(a.codigo_asignatura) AS codigo,
        asig.nombre AS nombre,
        asig.codigo_cc AS codigo_cc,
        cc.descripcion AS nombre_cc
    FROM a_a_a_bach_o_ciclo a
    INNER JOIN asignatura asig ON asig.codigo = a.codigo_asignatura
    INNER JOIN catalogo_cc_asignatura cc ON cc.codigo = asig.codigo_cc
    WHERE a.codigo_ann_lectivo = :annlectivo
    AND a.codigo_bach_o_ciclo = :modalidad
    AND a.codigo_grado = :grado
    ORDER BY a.orden
";

$stmt_asignaturas = $pdo->prepare($sql_asignaturas);

// Separar grado y sección del código
$codigo_grado = substr($gradoseccion, 0, 2);
//$codigo_seccion = substr($gradoseccion, 2, 2);

$stmt_asignaturas->execute([
    ':annlectivo' => substr($annlectivo, -2), // Tomar los últimos dos dígitos del año
    ':modalidad' => substr($modalidad, 0, 2),   // Tomar los primeros dos dígitos de la modalidad
    ':grado' => $codigo_grado,
]);

$asignaturas = $stmt_asignaturas->fetchAll(PDO::FETCH_ASSOC);

// Obtener la calificación mínima desde el formulario
$calificacion_minima = $_GET['calificacionMinima'] ?? 6; // Valor por defecto 6

// Consulta para obtener los datos de los estudiantes y sus calificaciones
$sql = "
    SELECT
        a.codigo_nie,
        a.codigo_genero AS codigo_genero,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as nombre_estudiante,
        asig.codigo AS codigo_asignatura,
        asig.nombre AS nombre_asignatura,
        asig.codigo_cc,
        n.nota_p_p_1,
        n.nota_p_p_2,
        n.nota_p_p_3,
        n.nota_p_p_4,
        n.nota_p_p_5,
        n.recuperacion,
        n.nota_recuperacion_2,
        n.nota_final
    FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
    WHERE am.codigo_bach_o_ciclo = :codigo_bachillerato
    AND am.codigo_grado = :codigo_grado
    AND am.codigo_seccion = :codigo_seccion
    AND am.codigo_ann_lectivo = :codigo_annlectivo
    AND asig.codigo IN (" . implode(',', array_map(function($a) { return "'" . $a['codigo'] . "'"; }, $asignaturas)) . ")
    ORDER BY a.apellido_paterno, a.apellido_materno, a.nombre_completo, asig.nombre
";


$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':codigo_bachillerato' => $codigo_bachillerato,
    ':codigo_grado' => $codigo_grado,
    ':codigo_seccion' => $codigo_seccion,
    ':codigo_annlectivo' => $codigo_annlectivo
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear el objeto PDF
$pdf = new FPDF('L', 'mm', array(215.9, 330.2)); // Oficio, Landscape
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);

// Configuración de márgenes
$pdf->SetMargins(5, 5, 5); // Izquierdo: 20mm, Superior: 30mm, Derecho: 15mm



// Header del PDF
$img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno']; //Logo
$nombre_institucion = convertirtexto($_SESSION['institucion']);
$pdf->  Image($img, 10, 10, 20); // Logo
$pdf->SetXY(30, 10);
$pdf->Cell(0, 6, convertirtexto($nombre_institucion), 0, 1, 'L');

// Datos del formulario (asumiendo que los recibes por GET o POST)
$modalidad = $_GET['modalidad'] ?? '';
$nombre_modalidad = $_GET['nombre_modalidad'] ?? '';
$nombre_grado = $_GET['nombre_grado'] ?? '';
$nombre_seccion = $_GET['nombre_seccion'] ?? '';
$nombre_turno = $_GET['nombre_turno'] ?? '';
$nombre_annlectivo = $_GET['nombre_annlectivo'] ?? '';
$pdf->SetX(30);
$pdf->Cell(0, 4, convertirtexto("Modalidad: " . $nombre_modalidad), 0, 1, 'L');
$pdf->SetX(30);
$pdf->Cell(0, 4, convertirtexto("Grado/Sección/Turno: " . $nombre_grado), 0, 1, 'L');
$pdf->SetX(30);
$pdf->Cell(0, 4, convertirtexto("Año Lectivo: " . $nombre_annlectivo), 0, 1, 'L');

$pdf->Ln(10);

// AGRUPAR LOS REGISTROS POR ESTUDIANTE (usando codigo_nie como índice)
$groupedData = [];
foreach ($data as $row) {
    $nie = $row['codigo_nie'];
    if (!isset($groupedData[$nie])) {
        $groupedData[$nie] = [
            'codigo_nie'       => $row['codigo_nie'],
            'nombre_estudiante'=> $row['nombre_estudiante'],
            'codigo_genero'      => $row['codigo_genero'],
            'subjects'         => []  // aquí se guardarán los registros por asignatura
        ];
    }
    // Usamos el código de la asignatura para indexar el registro
    $subjectCode = trim($row['codigo_asignatura']);
    $groupedData[$nie]['subjects'][$subjectCode] = [
        'nota_p_p_1'         => $row['nota_p_p_1'],
        'nota_p_p_2'         => $row['nota_p_p_2'],
        'nota_p_p_3'         => $row['nota_p_p_3'],
        'nota_p_p_4'         => $row['nota_p_p_4'],
        'nota_p_p_5'         => $row['nota_p_p_5'],
        'recuperacion'       => $row['recuperacion'],
        'nota_recuperacion_2'=> $row['nota_recuperacion_2'],
        'nota_final'         => $row['nota_final'],
        'codigo_cc'          => $row['codigo_cc']
    ];
}

// Definir anchos de columnas
$numWidth      = 5;               // N.º
$nieWidth      = 15;              // Código NIE
$nameWidth     = 75;              // Nombre del Estudiante
$cellWidth     = 6;               // Cada subcolumna de evaluación
$subjectWidth  = ($cantidad_periodos + 3) * $cellWidth; // Ajusta el ancho total según los períodos

// Determinar el número máximo de asignaturas por página según $cantidad_periodos
$asignaturasPorPagina = ($cantidad_periodos == 3) ? 6 : (($cantidad_periodos == 4) ? 5 : 4);

// División de asignaturas en grupos
$chunks = array_chunk($asignaturas, $asignaturasPorPagina);
$totalPaginas = count($chunks);

// Configuración de la generación del PDF
$pdf->SetFont('Arial', 'B', 7);

// Colores personalizados
$lightBlue = [200, 220, 240]; // Azul suave para encabezados
$lightGreen = [220, 240, 220]; // Verde suave para el total
$softGray = [230, 230, 230]; // Gris suave para notas bajas

$page = 1;
foreach ($chunks as $asignaturasSubset) {
    // Agregar una nueva página para cada conjunto de asignaturas
    if ($page > 1) {
        $pdf->AddPage();
    }

    // --- Encabezado de la tabla ---
    // Determinar la altura máxima según el nombre más largo de la asignatura
        $maxHeight = 12; // Definimos una altura mínima estándar
        $lineBreaks = [];

        foreach ($asignaturasSubset as $asig) {
            $pdf->SetFont('Arial', 'B', 7);
            $asigNombre = wordwrap($asig['nombre'], 30, "\n", true);
            $numLines = substr_count($asigNombre, "\n") + 1;
            $lineBreaks[] = max(2, $numLines); // Asegurar al menos 2 líneas para uniformidad
        }

        // Ajustamos la altura máxima en función del nombre más largo
        $maxHeight = max($lineBreaks) * 6; // Se multiplica para dar espacio uniforme

        // Primera fila del encabezado (Nombre Asignatura)
        $pdf->SetFillColor(...$lightBlue); // Set light blue for header
        $pdf->Cell($numWidth + $nieWidth + $nameWidth, $maxHeight, 'Nombre Asignatura', 1, 0, 'C', true);

        // Asegurar que cada asignatura ocupe correctamente su espacio sin desbordarse
        $xPos = $pdf->GetX();
        $yPos = $pdf->GetY();

        foreach ($asignaturasSubset as $index => $asig) {
            $pdf->SetXY($xPos, $yPos);
            $pdf->SetFont('Arial', 'B', 6);
            $nombreAsignatura = convertirTexto(wordwrap($asig['nombre'], 30, "\n", true));

            // Usamos una celda contenedora con altura uniforme
            $pdf->Cell(($cantidad_periodos + 3) * $cellWidth, $maxHeight, '', 1, 0, 'C', true); // Fill with light blue
            $pdf->SetXY($xPos, $yPos);
            $pdf->MultiCell(($cantidad_periodos + 3) * $cellWidth, 6, $nombreAsignatura, 0, 'C');

            $xPos += ($cantidad_periodos + 3) * $cellWidth;
        }
        $pdf->Ln();

    // SEGUNDO FINAL
    $pdf->SetFont('Arial', 'B', 8); // Reducimos el tamaño de la fuente si es necesario
    // Segunda fila: N.º, Código NIE y Nombre del Estudiante
    $pdf->SetFillColor(...$lightBlue); // Set light blue for header
    $pdf->Cell($numWidth, 6, convertirTexto('N°'), 1, 0, 'C', true);
    $pdf->Cell($nieWidth, 6, convertirTexto('NIE'), 1, 0, 'C', true);
    $pdf->Cell($nameWidth, 6, 'Nombre del Estudiante', 1, 0, 'C', true);

    // Subencabezados dinámicos (períodos)
    $subHeaders = [];
    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $subHeaders[] = "P$i";
    }
    $subHeaders[] = 'R1';
    $subHeaders[] = 'R2';
    $subHeaders[] = 'NF';

    foreach ($asignaturasSubset as $asig) {
        foreach ($subHeaders as $sub) {
            $pdf->Cell($cellWidth, 6, $sub, 1, 0, 'C', true); // Fill with light blue
        }
    }
    $pdf->Ln();

    // --- Datos de los estudiantes ---
    $i = 1;
    foreach ($groupedData as $student) {
        // Reset color and fill for student data rows
        $pdf->SetTextColor(0, 0, 0); // Reset student name to black
        $pdf->SetFillColor(255, 255, 255); // Reset fill color for data cells

        $pdf->Cell($numWidth, 6, $i, 1, 0, 'C');
        $pdf->Cell($nieWidth, 6, $student['codigo_nie'], 1, 0, 'C');
        $pdf->Cell($nameWidth, 6, convertirTexto($student['nombre_estudiante']), 1, 0, 'L');

        foreach ($asignaturasSubset as $asig) {
            $subjectCode = trim((string)$asig['codigo']);
            if (isset($student['subjects'][$subjectCode])) {
                $subjData = $student['subjects'][$subjectCode];
                for ($j = 1; $j <= $cantidad_periodos; $j++) {
                    $field = "nota_p_p_$j";
                    $val = floatval($subjData[$field]);
                    $display = ($val == 0 && $subjData[$field] !== "0") ? "" : $val; // Only empty if original is not "0"

                    if ($display !== "" && $val < $calificacion_minima) {
                        $pdf->SetTextColor(255, 0, 0); // Rojo para texto
                        $pdf->SetFillColor(...$softGray); // Gris suave para fondo
                        $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C', true); // Fill enabled
                    } else {
                        $pdf->SetTextColor(0, 0, 0); // Negro para texto
                        $pdf->SetFillColor(255, 255, 255); // Blanco para fondo (sin relleno)
                        $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C', false); // Fill disabled
                    }
                }

                // Campos fijos (recuperaciones y nota final)
                foreach (['recuperacion', 'nota_recuperacion_2', 'nota_final'] as $field) {
                    $val = floatval($subjData[$field]);
                    $display = ($val == 0 && $subjData[$field] !== "0") ? "" : $val; // Only empty if original is not "0"

                    if ($display !== "" && $val < $calificacion_minima) {
                        $pdf->SetTextColor(255, 0, 0); // Rojo para texto
                        $pdf->SetFillColor(...$softGray); // Gris suave para fondo
                        $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C', true); // Fill enabled
                    } else {
                        $pdf->SetTextColor(0, 0, 0); // Negro para texto
                        $pdf->SetFillColor(255, 255, 255); // Blanco para fondo (sin relleno)
                        $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C', false); // Fill disabled
                    }
                }

            } else {
                // Si la asignatura no tiene datos para el estudiante, dibujar celdas vacías
                $pdf->SetTextColor(0, 0, 0); // Reset a negro
                $pdf->SetFillColor(255, 255, 255); // Reset a blanco (sin relleno)
                for ($j = 0; $j < $cantidad_periodos + 3; $j++) {
                    $pdf->Cell($cellWidth, 6, '', 1, 0, 'C', false); // Sin fondo
                }
            }
        }
        $pdf->Ln();
        $i++;
    }

    $page++;
}

// Reset text and fill color after main table
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(255, 255, 255); // Blanco

// ---- CALCULAR TOTAL DE ASIGNATURAS CON CODIGO_CC = '01' ----
$totalAsignaturasCC01 = 0;
foreach ($asignaturas as $asig) {
    if (trim($asig['codigo_cc']) == '01') {
        $totalAsignaturasCC01++;
    }
}

// ---- CALCULAR APROBADOS Y REPROBADOS (RESUMEN GENERAL POR GENERO) ----
$aprobadosSum = ['01' => 0, '02' => 0]; // Masculino ('01'), Femenino ('02')
$reprobadosSum = ['01' => 0, '02' => 0];

foreach ($groupedData as $student) {
    $codigo_genero = $student['codigo_genero'];
    $studentReprobatedAnyCoreSubject = false; // Flag to track if this student failed ANY core subject

    foreach ($student['subjects'] as $subjectCode => $subjData) {
        $codigo_cc = trim($subjData['codigo_cc']);
        // Only consider subjects with codigo_cc = '01' for the general pass/fail calculation
        if ($codigo_cc == '01') {
            // Check if nota_final is a valid number and then compare
            if (is_numeric($subjData['nota_final'])) {
                if (floatval($subjData['nota_final']) < $calificacion_minima) {
                    $studentReprobatedAnyCoreSubject = true;
                    break; // No need to check other subjects for this student, they are already considered reprobated
                }
            }
        }
    }

    if ($studentReprobatedAnyCoreSubject) {
        $reprobadosSum[$codigo_genero]++;
    } else {
        $aprobadosSum[$codigo_genero]++;
    }
}

// ---- GENERAR EL CUADRO DE RESUMEN GENERAL ----
$pdf->AddPage();
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 6, 'Resumen de Resultados Generales:', 0, 1, 'L');

// Tabla de Aprobados/Reprobados por genero
$pdf->SetFillColor(...$lightBlue); // Set light blue for summary table header
$pdf->Cell(40, 6, 'Estado', 1, 0, 'C', true);
$pdf->Cell(30, 6, 'Masculino', 1, 0, 'C', true);
$pdf->Cell(30, 6, 'Femenino', 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows
$pdf->Cell(40, 6, 'Aprobados', 1, 0, 'C');
$pdf->Cell(30, 6, $aprobadosSum['01'], 1, 0, 'C');
$pdf->Cell(30, 6, $aprobadosSum['02'], 1, 1, 'C');

$pdf->Cell(40, 6, 'Reprobados', 1, 0, 'C');
$pdf->Cell(30, 6, $reprobadosSum['01'], 1, 0, 'C');
$pdf->Cell(30, 6, $reprobadosSum['02'], 1, 1, 'C');

// ---- DETERMINAR LOS MEJORES 5 ----
$ranking = [];

foreach ($groupedData as $student) {
    $totalPuntos = 0;
    foreach ($student['subjects'] as $subjectCode => $subjData) {
        // Only sum points from subjects with codigo_cc = '01' for ranking
        if (trim($subjData['codigo_cc']) == '01') {
            // Ensure nota_final is a valid number before adding
            if (is_numeric($subjData['nota_final'])) {
                $totalPuntos += floatval($subjData['nota_final']);
            }
        }
    }
    $ranking[] = [
        'codigo_nie' => $student['codigo_nie'],
        'nombre_estudiante' => $student['nombre_estudiante'],
        'total_puntos' => $totalPuntos
    ];
}

// Ordenar por mayor total de puntos
usort($ranking, function ($a, $b) {
    return $b['total_puntos'] - $a['total_puntos'];
});

// Obtener los primeros 5 mejores
$ranking = array_slice($ranking, 0, 5);

// ---- IMPRIMIR LA TABLA TOP 5 ----
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 6, 'Top 5 Mejores Calificaciones (por asignaturas tipo 01):', 0, 1, 'L');

$pdf->SetFillColor(...$lightBlue); // Set light blue for Top 5 table header
$pdf->Cell(10, 6, convertirTexto('N°'), 1, 0, 'C', true);
$pdf->Cell(30, 6, 'NIE', 1, 0, 'C', true);
$pdf->Cell(80, 6, 'Nombre del Estudiante', 1, 0, 'C', true);
$pdf->Cell(30, 6, 'TP', 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows
foreach ($ranking as $index => $student) {
    $pdf->Cell(10, 6, $index + 1, 1, 0, 'C');
    $pdf->Cell(30, 6, $student['codigo_nie'], 1, 0, 'C');
    $pdf->Cell(80, 6, convertirTexto($student['nombre_estudiante']), 1, 0, 'L');
    $pdf->Cell(30, 6, $student['total_puntos'], 1, 1, 'C');
}


// ---- EJEMPLO DE USO DE LA FUNCIÓN PARA EL GRÁFICO ----
$rutaImagenGrafico = $path_root . "/registro_academico/img/grafico_nombres_multilinea.png";
$configGrafico = [
    'width' => 700,
    'height' => 500, // Podría necesitar más altura por las etiquetas en dos líneas
    'title' => convertirTexto('Ranking Estudiantil Detallado (Nombres en 2 Líneas)'),
    'maxItems' => 5,
    // 'fontPath' => '/ruta/a/tu/fuente/arial.ttf', // ¡ALTAMENTE RECOMENDADO!
    'padding' => ['top' => 60, 'right' => 30, 'bottom' => 120, 'left' => 50], // ¡MUY IMPORTANTE: Aumentar 'bottom' para múltiples líneas!
    'fontSize' => 11,
    'xAxisLabelFontSizeOffset' => -2, // ej. 11-2 = 9pt para nombres
    'xAxisLabelAngle' => 0,           // Mantener en 0 para múltiples líneas
    'xAxisLabelsMaxLines' => 2,       // Mostrar hasta 2 líneas
    'xAxisLabelsCharsPerLine' => 18,  // Caracteres aprox. antes de cortar para la siguiente línea
    'xAxisLabelLineSpacing' => 3,     // Espacio extra entre líneas de un mismo nombre
    'xAxisLabelMarginTop' => 8,       // Espacio entre el eje y la primera línea del nombre
];

// Generar el gráfico
if (isset($ranking, $path_root) && generarGraficoRankingEstudiantes($ranking, $rutaImagenGrafico, $configGrafico)) {
    $pdf->Ln(10);
    $pdf->Image($rutaImagenGrafico, 60, $pdf->GetY() + 5, 150); // Ancho 150, alto se calculará automáticamente para mantener proporción
    $pdf->Ln(5); // Espacio después del gráfico
    $pdf->Cell(0, 6, convertirTexto($configGrafico['title'] . ':'), 0, 1, 'L');
} else {
    // If graph generation fails, you might want to log it or display a message
    // error_log("Hubo un error al generar el gráfico.");
}


// -------- NUEVA SECCIÓN: CUADROS RESUMEN POR CADA ASIGNATURA POR PERIODO (3 COLUMNAS) --------
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, convertirTexto('Resumen de Aprobados y Reprobados por Asignatura y Período:'), 0, 1, 'C');

foreach ($asignaturas as $asig) {
    $pdf->Ln(5); // Espacio entre asignaturas
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 6, 'Asignatura: ' . convertirTexto($asig['nombre']), 0, 1, 'L');
    $pdf->Cell(0, 6, 'Tipo de Contenido (CC): ' . convertirTexto(trim($asig['nombre_cc'])) . ' (' . trim($asig['codigo_cc']) . ')', 0, 1, 'L');
    $pdf->Ln(2); // Small space before period summaries

    // Define the fields for period summaries (P1, P2, P3... only)
    $evaluationFields = [];
    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $evaluationFields[] = "nota_p_p_$i";
    }

    // Calculate dimensions for each mini-table
    $miniTableWidth = 90; // Width for each period summary table
    $miniTableHeight = (5 * 5) + 5; // Approx 5 rows * 5 height + 5 spacing (Title + 4 rows of data + Total row)
    $columnsPerRow = 3;
    $columnSpacing = 5;

    $startYForSubject = $pdf->GetY(); // Capture Y position where subject period tables begin
    $startMarginX = $pdf->GetX(); // Capture starting X margin (which is the current left margin)

    $currentColumn = 0;
    foreach ($evaluationFields as $field) {
        $xPos = $startMarginX + ($currentColumn % $columnsPerRow) * ($miniTableWidth + $columnSpacing);
        $yPos = $startYForSubject + floor($currentColumn / $columnsPerRow) * ($miniTableHeight + $columnSpacing);

        // Check for page break before starting a new row of tables
        // Estimate the space needed for the next subject block (header + at least one row of tables)
        $spaceNeededForNextSubjectBlock = 2 * 6 + 2 + $miniTableHeight; // Subject name lines + spacing + 1 mini-table row
        if ($pdf->GetY() + $spaceNeededForNextSubjectBlock > $pdf->GetPageHeight()) {
            $pdf->AddPage();
            // Reset Y to new page's start for the subject content
            $startYForSubject = $pdf->GetY();
            $yPos = $startYForSubject;
            $xPos = $startMarginX; // Reset X to left margin
            $currentColumn = 0; // Reset column counter for new page (as we are on a new page)
        } else if ($currentColumn % $columnsPerRow == 0 && $currentColumn != 0) {
            // This is a new row of columns on the *same* page
            // Just ensure yPos is correctly calculated based on currentColumn
            $yPos = $startYForSubject + floor($currentColumn / $columnsPerRow) * ($miniTableHeight + $columnSpacing);
            // Ensure we don't start a table below the page break trigger
            if ($yPos + $miniTableHeight > $pdf->GetPageHeight()) {
                 $pdf->AddPage();
                 $startYForSubject = $pdf->GetY();
                 $yPos = $startYForSubject;
                 $xPos = $startMarginX;
                 $currentColumn = 0;
            }
        }

        $pdf->SetXY($xPos, $yPos); // Set cursor for the mini-table header

        $pdf->SetFont('Arial', 'B', 8);
        $periodDisplayName = strtoupper(str_replace('nota_p_p_', 'P', $field));
        $pdf->SetFillColor(...$lightBlue); // Set light blue for period summary header
        $pdf->Cell($miniTableWidth, 5, convertirTexto('Evaluación: ') . $periodDisplayName, 1, 1, 'C', true); // Title for the mini-table

        $aprobadosPeriod = ['01' => 0, '02' => 0];
        $reprobadosPeriod = ['01' => 0, '02' => 0];
        $notEvaluatedPeriod = ['01' => 0, '02' => 0];

        foreach ($groupedData as $student) {
            $codigo_genero = $student['codigo_genero'];
            if (!isset($aprobadosPeriod[$codigo_genero])) {
                $codigo_genero = '01';
            }
            $subjectCode = trim((string)$asig['codigo']);

            if (isset($student['subjects'][$subjectCode])) {
                $subjData = $student['subjects'][$subjectCode];
                $grade = $subjData[$field];

                if (is_numeric($grade)) {
                    if (floatval($grade) < $calificacion_minima) {
                        $reprobadosPeriod[$codigo_genero]++;
                    } else {
                        $aprobadosPeriod[$codigo_genero]++;
                    }
                } else {
                    $notEvaluatedPeriod[$codigo_genero]++;
                }
            } else {
                $notEvaluatedPeriod[$codigo_genero]++;
            }
        }

        $pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows
        $pdf->SetFont('Arial', '', 7);
        $currentY = $pdf->GetY(); // Capture Y after title cell
        $pdf->SetXY($xPos, $currentY); // Reset X for data rows
        $pdf->Cell(30, 5, 'Estado', 1, 0, 'C');
        $pdf->Cell(20, 5, 'M', 1, 0, 'C'); // Masculine
        $pdf->Cell(20, 5, 'F', 1, 0, 'C'); // Femenine
        $pdf->Cell(20, 5, 'Total', 1, 1, 'C');

        $totalAprobados = $aprobadosPeriod['01'] + $aprobadosPeriod['02'];
        $totalReprobados = $reprobadosPeriod['01'] + $reprobadosPeriod['02'];
        $totalNotEvaluated = $notEvaluatedPeriod['01'] + $notEvaluatedPeriod['02'];

        $currentY = $pdf->GetY(); // Capture Y for next row
        $pdf->SetXY($xPos, $currentY);
        $pdf->Cell(30, 5, 'Aprobados', 1, 0, 'C');
        $pdf->Cell(20, 5, $aprobadosPeriod['01'], 1, 0, 'C');
        $pdf->Cell(20, 5, $aprobadosPeriod['02'], 1, 0, 'C');
        $pdf->Cell(20, 5, $totalAprobados, 1, 1, 'C');

        $currentY = $pdf->GetY();
        $pdf->SetXY($xPos, $currentY);
        $pdf->Cell(30, 5, 'Reprobados', 1, 0, 'C');
        $pdf->Cell(20, 5, $reprobadosPeriod['01'], 1, 0, 'C');
        $pdf->Cell(20, 5, $reprobadosPeriod['02'], 1, 0, 'C');
        $pdf->Cell(20, 5, $totalReprobados, 1, 1, 'C');

        $currentY = $pdf->GetY();
        $pdf->SetXY($xPos, $currentY);
        $pdf->Cell(30, 5, 'No Evaluados', 1, 0, 'C');
        $pdf->Cell(20, 5, $notEvaluatedPeriod['01'], 1, 0, 'C');
        $pdf->Cell(20, 5, $notEvaluatedPeriod['02'], 1, 0, 'C');
        $pdf->Cell(20, 5, $totalNotEvaluated, 1, 1, 'C');

        $totalEnrolledForThisEval = $totalAprobados + $totalReprobados + $totalNotEvaluated;
        $currentY = $pdf->GetY();
        $pdf->SetXY($xPos, $currentY);
        $pdf->SetFillColor(...$lightGreen); // Set light green for Total Nomina
        $pdf->Cell(30, 5, 'Total Nomina', 1, 0, 'C', true); // Fill with light green
        $pdf->Cell(60, 5, $totalEnrolledForThisEval, 1, 1, 'C', true); // Spanning three columns for total, fill with light green
        $pdf->SetFillColor(255, 255, 255); // Reset fill color


        $currentColumn++;
    }
    // After all periods for a subject are printed, move Y cursor down past the lowest table in the current row
    // Calculate the max Y reached by any column in the current row
    $maxYInRow = $startYForSubject + ceil(count($evaluationFields) / $columnsPerRow) * ($miniTableHeight + $columnSpacing);
    $pdf->SetY($maxYInRow);
    //$pdf->SetX($pdf->GetLMargin()); // Changed to GetLMargin() again, as the previous solutions failed to resolve.
                                   // If this specific GetLMargin() call still causes an error, your FPDF version
                                   // does NOT expose it. In that case, you'll need to manually set X to 10.
                                   // For now, I'm using GetLMargin() as it's the standard intended way.
    $pdf->Ln(5); // Add a little extra space between subjects

    // Add a page break if needed for the next subject header
    if ($pdf->GetY() + 40 > $pdf->GetPageHeight()) { // Estimate 40 units for next subject header block
        $pdf->AddPage();
    }
}


// ---- Tabla detallada con asignaturas y su tipo (moved to end for better flow) ----
$pdf->AddPage();
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(80, 6, 'Listado de Asignaturas Detallado:', 0, 1, 'L');

$pdf->SetFillColor(...$lightBlue); // Set light blue for detailed assignments table header
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 6, '#', 1, 0, 'C', true);
$pdf->Cell(140, 6, 'Nombre Asignatura', 1, 0, 'C', true);
$pdf->Cell(30, 6, convertirTexto('Código CC'), 1, 0, 'C', true);
$pdf->Cell(30, 6, convertirTexto('Nombre CC'), 1, 1, 'C', true);

$pdf->SetFillColor(255, 255, 255); // Reset fill color for data rows
$contador = 1;
foreach ($asignaturas as $asig) {
    $pdf->Cell(10, 6, $contador++, 1, 0, 'C');
    $pdf->Cell(140, 6, convertirTexto($asig['nombre']), 1, 0, 'L');
    $pdf->Cell(30, 6, trim($asig['codigo_cc']), 1, 0, 'C');
    $pdf->Cell(30, 6, trim(convertirTexto($asig['nombre_cc'])), 1, 1, 'C');
}

// ---- FINALIZAR PDF ----
$pdf->Output("I", "informe.pdf");


function convertirTextos($texto)
{
    $texto = mb_strtolower($texto, "ISO-8859-1"); // Convierte todo a minúsculas
    $texto = mb_strtoupper(mb_substr($texto, 0, 1, "ISO-8859-1"), "ISO-8859-1") . mb_substr($texto, 1, null, "ISO-8859-1");
    return $texto;
}

/**
 * Genera un gráfico de barras para el ranking de estudiantes y lo guarda como imagen.
 *
 * @param array $rankingData Array con los datos de los estudiantes. Cada elemento debe tener 'nombre_estudiante' y 'total_puntos'.
 * @param string $outputPath Ruta completa donde se guardará la imagen PNG.
 * @param array $config Configuraciones opcionales para el gráfico (ancho, alto, colores, etc.).
 * @return bool True si la imagen se generó correctamente, False en caso contrario.
 */
function generarGraficoRankingEstudiantes(array $rankingData, string $outputPath, array $config = []): bool
{
    // --- 1. Configuración y Valores por Defecto ---
    $defaults = [
        'width' => 400,
        'height' => 300,
        'title' => 'Ranking de Estudiantes',
        'maxItems' => null,
        'colors' => [ /* ... (colores como antes) ... */
            'background' => [255, 255, 255], 'text' => [0, 0, 0],
            'axis' => [150, 150, 150], 'valueOnBar' => [0, 0, 0],
        ],
        'barColors' => [ /* ... (colores de barra como antes) ... */
            [52, 152, 219], [231, 76, 60], [46, 204, 113],
            [241, 196, 15], [155, 89, 182], [26, 188, 156]
        ],
        'padding' => ['top' => 40, 'right' => 30, 'bottom' => 60, 'left' => 60],
        'barSpacing' => 20,
        'fontPath' => null,
        'fontSize' => 10,
        'showValuesOnBars' => true,
        'valueOnBarFontSizeOffset' => -2,
        'valueOnBarMarginBottom' => 5,
        'xAxisLabelFontSizeOffset' => -3,
        'xAxisLabelAngle' => 0,           // Para múltiples líneas, es mejor mantenerlo en 0
        'xAxisLabelMarginTop' => 5,
        'xAxisLabelsMaxLines' => 2,       // Changed to 2 as per the earlier graph config
        'xAxisLabelsCharsPerLine' => 18,  // Changed to 18 as per the earlier graph config
        'xAxisLabelLineSpacing' => 3,     // Changed to 3 as per the earlier graph config
    ];

    $cfg = array_replace_recursive($defaults, $config);

    // IMPORTANTE: Si usas múltiples líneas, asegúrate que padding['bottom'] sea suficiente.
    // La lógica automática de ajuste de padding para texto angulado se omite para simplificar.
    // Debes configurarlo manualmente.

    // --- 2. Preparación de Datos --- (Sin cambios)
    // ... (código de preparación de datos) ...
    if (empty($rankingData)) { error_log("generarGraficoRankingEstudiantes: No hay datos."); return false; }
    if ($cfg['maxItems'] !== null && count($rankingData) > $cfg['maxItems']) {
        $rankingData = array_slice($rankingData, 0, $cfg['maxItems']);
    }
    if (empty($rankingData)) { error_log("generarGraficoRankingEstudiantes: No hay datos después de maxItems."); return false; }
    $points = array_column($rankingData, 'total_puntos');
    if(empty($points)) { error_log("generarGraficoRankingEstudiantes: No se encontraron 'total_puntos'."); return false; }
    $maxValue = max($points);
    if ($maxValue == 0) $maxValue = 1;
    $numBars = count($rankingData);


    // --- 3. Creación de la Imagen --- (Sin cambios)
    // ... (código de creación de imagen y colores base) ...
    $image = imagecreatetruecolor($cfg['width'], $cfg['height']);
    if (!$image) { error_log("generarGraficoRankingEstudiantes: Falló imagecreatetruecolor."); return false; }
    $colorBackground = imagecolorallocate($image, ...$cfg['colors']['background']);
    $colorText = imagecolorallocate($image, ...$cfg['colors']['text']);
    $colorAxis = imagecolorallocate($image, ...$cfg['colors']['axis']);
    $colorValueOnBar = imagecolorallocate($image, ...$cfg['colors']['valueOnBar']);
    imagefill($image, 0, 0, $colorBackground);


    // --- 4. Área de Dibujo y Cálculo de Barras --- (Sin cambios significativos, yBase y drawableHeight dependen de padding)
    // ... (código de área de dibujo y cálculo de barras) ...
    $drawableWidth = $cfg['width'] - $cfg['padding']['left'] - $cfg['padding']['right'];
    $drawableHeight = $cfg['height'] - $cfg['padding']['top'] - $cfg['padding']['bottom'];
    if ($numBars > 0) {
        $totalSpacing = ($numBars - 1) * $cfg['barSpacing'];
        $barWidth = ($drawableWidth - $totalSpacing) / $numBars;
        if ($barWidth < 5) $barWidth = 5;
    } else { $barWidth = $drawableWidth; }
    $xOffset = $cfg['padding']['left'];
    $yBase = $cfg['height'] - $cfg['padding']['bottom'];
    imageline($image, $cfg['padding']['left'], $yBase, $cfg['width'] - $cfg['padding']['right'], $yBase, $colorAxis);
    imageline($image, $cfg['padding']['left'], $cfg['padding']['top'], $cfg['padding']['left'], $yBase, $colorAxis);


    // --- 5. Dibujar Título del Gráfico --- (Sin cambios)
    // ... (código del título) ...
    if (!empty($cfg['title'])) {
        $titleFontSize = $cfg['fontSize'] + 2;
        if ($cfg['fontPath'] && file_exists($cfg['fontPath'])) {
            $titleBox = imagettfbbox($titleFontSize, 0, $cfg['fontPath'], $cfg['title']);
            $titleWidth = $titleBox[2] - $titleBox[0];
            imagettftext($image, $titleFontSize, 0, (int)(($cfg['width'] - $titleWidth) / 2), $cfg['padding']['top'] - 15, $colorText, $cfg['fontPath'], $cfg['title']);
        } else {
            $titleWidth = imagefontwidth(5) * strlen($cfg['title']);
            imagestring($image, 5, (int)(($cfg['width'] - $titleWidth) / 2), $cfg['padding']['top'] - 25, $cfg['title'], $colorText);
        }
    }

    // --- 6. Dibujar Barras, Etiquetas y Valores ---
    $valueOnBarActualFontSize = $cfg['fontSize'] + $cfg['valueOnBarFontSizeOffset'];
    if ($cfg['fontPath'] && $valueOnBarActualFontSize < 6) $valueOnBarActualFontSize = 6;
    elseif (!$cfg['fontPath'] && $valueOnBarActualFontSize < 1) $valueOnBarActualFontSize = 1;

    $xAxisLabelActualFontSize = $cfg['fontSize'] + $cfg['xAxisLabelFontSizeOffset'];
    $gdLabelFontSize = 3; // Para imagestring
    if ($cfg['fontPath'] && file_exists($cfg['fontPath'])) {
        if ($xAxisLabelActualFontSize < 6) $xAxisLabelActualFontSize = 6;
    } else {
        if ($xAxisLabelActualFontSize < 1) $xAxisLabelActualFontSize = 1;
        // Mapeo simple a tamaños de GD (1-5)
        if ($xAxisLabelActualFontSize <= 7) $gdLabelFontSize = 1;
        elseif ($xAxisLabelActualFontSize <= 9) $gdLabelFontSize = 2;
        elseif ($xAxisLabelActualFontSize <= 11) $gdLabelFontSize = 3;
        else $gdLabelFontSize = 4;
    }


    foreach ($rankingData as $index => $student) {
        // ... (Dibujo de la barra como antes) ...
        $currentBarColorComponents = $cfg['barColors'][$index % count($cfg['barColors'])];
        $barColor = imagecolorallocate($image, ...$currentBarColorComponents);
        $barHeight = ($student['total_puntos'] / $maxValue) * $drawableHeight;
        $barHeight = max(0, $barHeight);
        $x1 = $xOffset + ($index * ($barWidth + $cfg['barSpacing']));
        $y1 = $yBase - $barHeight;
        $x2 = $x1 + $barWidth;
        $y2 = $yBase -1;
        imagefilledrectangle($image, (int)$x1, (int)$y1, (int)$x2, (int)$y2, $barColor);


        // --- MODIFICADO: Etiquetas del Eje X (Nombres de Estudiantes en múltiples líneas) ---
        $studentName = convertirTexto($student['nombre_estudiante']);

        $nameLines = [$studentName]; // Por defecto, una sola línea
        if ($cfg['xAxisLabelsMaxLines'] > 1 && $cfg['xAxisLabelAngle'] == 0) { // Multi-línea funciona mejor horizontal
            $wrappedName = wordwrap($studentName, $cfg['xAxisLabelsCharsPerLine'], "\n", true);
            $tempLines = explode("\n", $wrappedName);
            $nameLines = array_slice($tempLines, 0, $cfg['xAxisLabelsMaxLines']);
        } elseif ($cfg['xAxisLabelsMaxLines'] > 1 && $cfg['xAxisLabelAngle'] != 0) {
            // Opcional: advertir que la combinación no es idealmente soportada
            error_log("Advertencia: Las etiquetas del eje X en múltiples líneas con ángulo pueden no mostrarse correctamente.");
        }

        // Posición Y inicial para la primera línea de la etiqueta
        $currentLineTextY = $yBase + $cfg['xAxisLabelMarginTop'];

        foreach ($nameLines as $lineIndex => $lineText) {
            if (empty(trim($lineText))) continue; // Omitir líneas vacías

            if ($cfg['fontPath'] && file_exists($cfg['fontPath'])) { // Fuente TTF
                // La posición Y para imagettftext es la línea base de la fuente
                $lineBaselineY = $currentLineTextY + $xAxisLabelActualFontSize;
                if ($lineIndex > 0) { // Para la segunda línea y subsecuentes
                    // Añadir altura de fuente anterior y espaciado
                    $lineBaselineY += ($lineIndex * ($xAxisLabelActualFontSize + $cfg['xAxisLabelLineSpacing']));
                }

                $textBox = imagettfbbox($xAxisLabelActualFontSize, 0, $cfg['fontPath'], $lineText);
                $textLineWidth = abs($textBox[2] - $textBox[0]);

                // Para texto angulado, la X es el inicio. Para horizontal, se centra.
                $lineTextX = ($cfg['xAxisLabelAngle'] == 0) ?
                             ($x1 + ($barWidth - $textLineWidth) / 2) :
                             $x1; // Simplificado para texto angulado

                imagettftext(
                    $image, $xAxisLabelActualFontSize, $cfg['xAxisLabelAngle'],
                    (int)$lineTextX, (int)$lineBaselineY,
                    $colorText, $cfg['fontPath'], $lineText
                );
            } else { // Fuente GD (el ángulo se ignora para múltiples líneas)
                $gdFontLineHeight = imagefontheight($gdLabelFontSize);
                // La posición Y para imagestring es la esquina superior del texto
                $lineTopY = $currentLineTextY;
                if ($lineIndex > 0) {
                    $lineTopY += ($lineIndex * ($gdFontLineHeight + $cfg['xAxisLabelLineSpacing']));
                }

                $textLineWidth = imagefontwidth($gdLabelFontSize) * strlen($lineText);
                $lineTextX = $x1 + ($barWidth - $textLineWidth) / 2;

                imagestring(
                    $image, $gdLabelFontSize,
                    (int)$lineTextX, (int)$lineTopY,
                    $lineText, $colorText
                );
            }
        }


        // --- Mostrar valores encima de las barras (sin cambios significativos) ---
        if ($cfg['showValuesOnBars']) {
            // ... (código para valores sobre las barras como antes) ...
            $valueString = (string)$student['total_puntos'];
            $textYPosValue = $y1 - $cfg['valueOnBarMarginBottom'];
            if ($textYPosValue < $cfg['padding']['top'] + $valueOnBarActualFontSize) { // Evitar que se salga por arriba
                $textYPosValue = $cfg['padding']['top'] + $valueOnBarActualFontSize;
                 // Podrías optar por dibujar dentro de la barra si está muy alta
            }

            if ($cfg['fontPath'] && file_exists($cfg['fontPath'])) {
                $valueBox = imagettfbbox($valueOnBarActualFontSize, 0, $cfg['fontPath'], $valueString);
                $valueWidth = $valueBox[2] - $valueBox[0];
                imagettftext( $image, $valueOnBarActualFontSize, 0, (int)($x1 + ($barWidth - $valueWidth) / 2), (int)$textYPosValue, $colorValueOnBar, $cfg['fontPath'], $valueString );
            } else {
                $gdValueFontSize = 2; /* ... mapeo de tamaño ... */
                $valueWidth = imagefontwidth($gdValueFontSize) * strlen($valueString);
                imagestring( $image, $gdValueFontSize, (int)($x1 + ($barWidth - $valueWidth) / 2), (int)($textYPosValue - $valueOnBarActualFontSize), $valueString, $colorValueOnBar );
            }
        }
    }

    // --- 7. Guardar Imagen y Limpiar --- (Sin cambios)
    // ... (código para guardar y destruir imagen) ...
    $success = imagepng($image, $outputPath);
    imagedestroy($image);
    if (!$success) { error_log("generarGraficoRankingEstudiantes: Falló imagepng en {$outputPath}."); }
    return $success;
}