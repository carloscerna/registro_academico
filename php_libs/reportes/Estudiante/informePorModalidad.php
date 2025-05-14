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
// 1. Incluir pChart (asegúrate de instalarla y configurar autoloading)
include($path_root."/registro_academico/php_libs/pChart/pChart/class/pData.class.php");
include($path_root."/registro_academico/php_libs/pChart/pChart/class/pDraw.class.php");
include($path_root."/registro_academico/php_libs/pChart/pChart/class/pImage.class.php");

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
        a.codigo_genero,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as nombre_estudiante,
        asig.codigo AS codigo_asignatura,
        asig.nombre AS nombre_asignatura
        ";

if ($cantidad_periodos > 0) {
    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $sql .= ", n.nota_p_p_$i AS pp_$i";
    }
}
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
//var_dump($data);
// Obtener la calificación mínima desde el formulario
$calificacion_minima = $_GET['calificacionMinima'] ?? 6; // Valor por defecto 6

// Crear el objeto PDF
$pdf = new FPDF('L', 'mm', array(215.9, 330.2)); // Oficio, Landscape
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8);

// Header del PDF
$img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno']; //Logo
$nombre_institucion = convertirtexto($_SESSION['institucion']);
$pdf->  Image($img, 10, 10, 20); // Logo
$pdf->SetXY(30, 10);
$pdf->Cell(0, 6, convertirtexto($nombre_institucion), 0, 1, 'L');

// Datos del formulario (asumiendo que los recibes por GET o POST)
$modalidad = $_GET['modalidad'] ?? ''; // O $_POST, dependiendo de cómo los envíes
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

// Obtener la cantidad de períodos para la modalidad
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
        $pdf->Cell($numWidth + $nieWidth + $nameWidth, $maxHeight, 'Nombre Asignatura', 1, 0, 'C');

        // Asegurar que cada asignatura ocupe correctamente su espacio sin desbordarse
        $xPos = $pdf->GetX();
        $yPos = $pdf->GetY();

        foreach ($asignaturasSubset as $index => $asig) {
            $pdf->SetXY($xPos, $yPos);
            $pdf->SetFont('Arial', 'B', 6);
            $nombreAsignatura = convertirTexto(wordwrap($asig['nombre'], 30, "\n", true));

            // Usamos una celda contenedora con altura uniforme
            $pdf->Cell(($cantidad_periodos + 3) * $cellWidth, $maxHeight, '', 1, 0, 'C');
            $pdf->SetXY($xPos, $yPos);
            $pdf->MultiCell(($cantidad_periodos + 3) * $cellWidth, 6, $nombreAsignatura, 0, 'C');
            
            $xPos += ($cantidad_periodos + 3) * $cellWidth; 
        }
        $pdf->Ln();

    // SEGUNDO FINAL
    $pdf->SetFont('Arial', 'B', 8); // Reducimos el tamaño de la fuente si es necesario
    // Segunda fila: N.º, Código NIE y Nombre del Estudiante
    $pdf->Cell($numWidth, 6, convertirTexto('N°'), 1, 0, 'C');
    $pdf->Cell($nieWidth, 6, convertirTexto('NIE'), 1, 0, 'C');
    $pdf->Cell($nameWidth, 6, 'Nombre del Estudiante', 1, 0, 'C');

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
            $pdf->Cell($cellWidth, 6, $sub, 1, 0, 'C');
        }
    }
    $pdf->Ln();

    // --- Datos de los estudiantes ---
    $i = 1;
    foreach ($groupedData as $student) {
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
                    $display = ($val == 0) ? "" : $val;

                    if ($display !== "" && $val < $calificacion_minima) {
                        $pdf->SetTextColor(255, 0, 0);
                    }

                    $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C');
                    $pdf->SetTextColor(0, 0, 0);
                }

                // Campos fijos (recuperaciones y nota final)
                foreach (['recuperacion', 'nota_recuperacion_2', 'nota_final'] as $field) {
                    $val = floatval($subjData[$field]);
                    $display = ($val == 0) ? "" : $val;

                    if ($field == 'nota_final' && $display !== "" && $val < $calificacion_minima) {
                        $pdf->SetTextColor(255, 0, 0);
                    }

                    $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C');
                    $pdf->SetTextColor(0, 0, 0);
                }

            } else {
                for ($j = 0; $j < $cantidad_periodos + 3; $j++) {
                    $pdf->Cell($cellWidth, 6, '', 1, 0, 'C');
                }
            }
        }
        $pdf->Ln();
        $i++;
    }

    $page++;
}

// ---- CALCULAR TOTAL DE ASIGNATURAS CON CODIGO_CC = '01' ----
$pdf->AddPage();
$totalAsignaturasCC01 = 0;
foreach ($stmt_asignaturas as $asig) {
    if (trim($asig['codigo_cc']) == '01') {
        $totalAsignaturasCC01++;
    }
}

// ---- CALCULAR APROBADOS Y REPROBADOS ----
$aprobadosSum = ['01' => 0, '02' => 0]; // Masculino ('01'), Femenino ('02')
$reprobadosSum = ['01' => 0, '02' => 0];

foreach ($groupedData as $student) {
    $codigo_genero = $student['codigo_genero'];
    $reprobado = false; // Bandera para marcar si el estudiante tiene al menos una asignatura reprobada

    foreach ($student['subjects'] as $subjectCode => $subjData) {
        $codigo_cc = trim($subjData['codigo_cc']);
        if ($codigo_cc == '01') {
            if ($subjData['nota_final'] < 5) {
                $reprobado = true;
            }
        }
    }

    // Si el estudiante tiene al menos una asignatura reprobada, cuenta como reprobado
    if ($reprobado) {
        $reprobadosSum[$codigo_genero]++;
    } else {
        $aprobadosSum[$codigo_genero]++;
    }
}

// ---- GENERAR EL CUADRO DE RESUMEN ----
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(80, 6, 'Resumen de Resultados:', 0, 1, 'L');

// Tabla de Aprobados/Reprobados por año
$pdf->Cell(40, 6, 'Estado', 1, 0, 'C');
$pdf->Cell(30, 6, 'Masculino', 1, 0, 'C');
$pdf->Cell(30, 6, 'Femenino', 1, 1, 'C');

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
        // Eliminamos espacios en codigo_cc antes de comparar
        if (trim($subjData['codigo_cc']) == '01') {
            $totalPuntos += $subjData['nota_final'];
        }
        if ($codigo_cc == '04' && $subjData['nota_final'] < 3) {
            $reprobado = true;
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
$pdf->Cell(80, 6, 'Top 5 Mejores Calificaciones:', 0, 1, 'L');

$pdf->Cell(10, 6, 'N°', 1, 0, 'C');
$pdf->Cell(30, 6, 'NIE', 1, 0, 'C');
$pdf->Cell(80, 6, 'Nombre del Estudiante', 1, 0, 'C');
$pdf->Cell(30, 6, 'TP', 1, 1, 'C');

foreach ($ranking as $index => $student) {
    $pdf->Cell(10, 6, $index + 1, 1, 0, 'C');
    $pdf->Cell(30, 6, $student['codigo_nie'], 1, 0, 'C');
    $pdf->Cell(80, 6, convertirTexto($student['nombre_estudiante']), 1, 0, 'L');
    $pdf->Cell(30, 6, $student['total_puntos'], 1, 1, 'C');
}


// Agregar nueva página para los resúmenes
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(80, 6, 'Resumen de Asignaturas:', 0, 1, 'L');

// ---- Calcular cantidad de asignaturas por tipo ----
$cantidadAsignaturas = ['01' => 0, '02' => 0, '04' => 0];

foreach ($asignaturas as $asig) {
    $codigo_cc = trim($asig['codigo_cc']);
    if (isset($cantidadAsignaturas[$codigo_cc])) {
        $cantidadAsignaturas[$codigo_cc]++;
    }
}

// Tabla de resumen de cantidad de asignaturas por código_cc
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(40, 6, 'Codigo CC', 1, 0, 'C');
$pdf->Cell(50, 6, 'Cantidad de Asignaturas', 1, 1, 'C');

foreach ($cantidadAsignaturas as $codigo_cc => $cantidad) {
    $pdf->Cell(40, 6, $codigo_cc, 1, 0, 'C');
    $pdf->Cell(50, 6, $cantidad, 1, 1, 'C');
}

// ---- Tabla detallada con asignaturas y su tipo ----
$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(80, 6, 'Listado de Asignaturas:', 0, 1, 'L');

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(10, 6, '#', 1, 0, 'C');
$pdf->Cell(140, 6, 'Nombre Asignatura', 1, 0, 'C');
$pdf->Cell(30, 6, convertirTexto('Código CC'), 1, 0, 'C');
$pdf->Cell(30, 6, convertirTexto('Nombre'), 1, 1, 'C');

$contador = 1;
foreach ($asignaturas as $asig) {
    $pdf->Cell(10, 6, $contador++, 1, 0, 'C');
    $pdf->Cell(140, 6, convertirTexto($asig['nombre']), 1, 0, 'L');
    $pdf->Cell(30, 6, trim($asig['codigo_cc']), 1, 0, 'C');
    $pdf->Cell(30, 6, trim(convertirTexto($asig['nombre_cc'])), 1, 1, 'C');
}


// 2. Preparar los datos para el gráfico
$data = new pData();
$nombres = [];
$puntos = [];
foreach ($ranking as $student) {
    $nombres[] = $student['nombre_estudiante'];
    $puntos[] = $student['total_puntos'];
}
$data->addPoints($puntos, "Puntos");
$data->addPoints($nombres, "Nombres");
$data->setSerieDescription("Puntos", "Puntos");
$data->setSerieDescription("Nombres", "Nombres");
$data->setAbscissa("Nombres");

// 3. Crear el gráfico
$grafico = new pImage(700, 230, $data);
$grafico->setGraphArea(50, 30, 680, 200);
$grafico->drawScale();
$grafico->drawBarChart();

// 4. Guardar el gráfico como imagen
$grafico_path = "top_5_grafico.png";
$grafico->Render($grafico_path);

// 5. Insertar la imagen en el PDF
$pdf->Image($grafico_path, 10, $pdf->GetY(), 200);

// 6. Eliminar la imagen (opcional)
unlink($grafico_path);

// ---- FINALIZAR PDF ----
$pdf->Output("I", "informe.pdf");


function convertirTextos($texto)
{
    $texto = mb_strtolower($texto, "ISO-8859-1"); // Convierte todo a minúsculas
    $texto = mb_strtoupper(mb_substr($texto, 0, 1, "ISO-8859-1"), "ISO-8859-1") . mb_substr($texto, 1, null, "ISO-8859-1");
    return $texto;
}