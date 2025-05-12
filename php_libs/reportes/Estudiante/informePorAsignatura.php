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
$asignatura = $_GET['asignatura'];
$periodo = $_GET['periodo'];

// Construcción de códigos desde el formulario (igual que en PorAsignatura.php)
$codigo_all = $modalidad . substr($gradoseccion, 0, 4) . $annlectivo;
$codigo_bachillerato = substr($codigo_all, 0, 2);
$codigo_grado = substr($codigo_all, 2, 2);
$codigo_seccion = substr($codigo_all, 4, 2);
$codigo_annlectivo = substr($codigo_all, 6, 2);
$codigo_asignatura = trim($asignatura);

// Obtener la cantidad de períodos para la modalidad
$query_periodos = "SELECT cantidad_periodos FROM catalogo_periodos WHERE codigo_modalidad = :codigo_modalidad";
$stmt_periodos = $pdo->prepare($query_periodos);
$stmt_periodos->bindParam(':codigo_modalidad', $modalidad);
$stmt_periodos->execute();
$cantidad_periodos = $stmt_periodos->fetchColumn();

$select_columns = [
    "a.codigo_nie",
    "a.codigo_genero",
    "btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as nombre_estudiante",
    "asig.nombre AS nombre_asignatura"
];
$join_columns = [];

if ($cantidad_periodos > 0) {
    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $select_columns = array_merge($select_columns, [
            "n.nota_a1_$i AS a1_$i",
            "n.nota_a2_$i AS a2_$i",
            "n.nota_a3_$i AS a3_$i",
            "n.nota_r_$i AS r_$i",
            "n.nota_p_p_$i AS pp_$i"
        ]);
    }
}

$select_columns = array_merge($select_columns, [
    "n.recuperacion",
    "n.nota_recuperacion_2",
    "n.nota_final"
]);

$sql = "
    SELECT 
        " . implode(", ", $select_columns) . "
    FROM alumno a  
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
    WHERE am.codigo_bach_o_ciclo = :codigo_bachillerato  
    AND am.codigo_grado = :codigo_grado 
    AND am.codigo_seccion = :codigo_seccion 
    AND am.codigo_ann_lectivo = :codigo_annlectivo
    AND n.codigo_asignatura = :codigo_asignatura 
    ORDER BY nombre_estudiante ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':codigo_bachillerato' => $codigo_bachillerato,
    ':codigo_grado' => $codigo_grado,
    ':codigo_seccion' => $codigo_seccion,
    ':codigo_annlectivo' => $codigo_annlectivo,
    ':codigo_asignatura' => $codigo_asignatura
]);

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la calificación mínima desde el formulario
$calificacion_minima = $_GET['calificacionMinima'] ?? 6; // Valor por defecto 6

// Crear el objeto PDF
$pdf = new FPDF('L'); // ✅ 'L' para landscape (horizontal)
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 8); // ✅ Tamaño de fuente 8

// Header del PDF
$img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno']; //Logo
$nombre_institucion = convertirtexto($_SESSION['institucion']);
$pdf->Image($img, 10, 10, 20); // Logo
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

$pdf->Ln(); // Espacio después del header
// ✅ Verificación de si hay datos
if (!empty($data)) {
    $pdf->Cell(0, 4, 'Informe de Calificaciones - Asignatura: ' . convertirTexto($data[0]['nombre_asignatura']), 0, 1, 'C');
} else {
    $pdf->Cell(0, 4, 'Informe de Calificaciones - Asignatura: No hay datos disponibles', 0, 1, 'C');
}
$pdf->Ln();

// Encabezados de la tabla (dinámicos)
$pdf->Cell(8, 8, 'N.º', 1); // ✅ Columna N.º
$pdf->Cell(15, 8, 'NIE', 1);
$pdf->Cell(55, 8, 'Estudiante', 1);
if ($cantidad_periodos > 0) {
    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $pdf->Cell(8, 8, "A1", 1);
        $pdf->Cell(8, 8, "A2", 1);
        $pdf->Cell(8, 8, "A3", 1);
        $pdf->Cell(8, 8, "R", 1);
        $pdf->SetFillColor(200, 200, 200);
        $pdf->Cell(10, 8, "N PP", 1, 0, 'C', 1); // Sombreado en encabezado
        $pdf->SetFillColor(255, 255, 255);
    }
}
$pdf->Cell(10, 8, "R1", 1); // ✅ R1
$pdf->Cell(10, 8, "R2", 1); // ✅ R2
$pdf->Cell(10, 8, "NF", 1); // ✅ NF
$pdf->Ln();

// Datos de la tabla (dinámicos)
$pdf->SetFont('Arial', '', 8);
$contador = 1; // ✅ Inicializar el contador

// Variables para el resumen
$resumen = ['NF' => ['M' => ['aprobados' => 0, 'reprobados' => 0], 'F' => ['aprobados' => 0, 'reprobados' => 0], 'total' => ['aprobados' => 0, 'reprobados' => 0]]];
if ($cantidad_periodos > 0) {
    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $resumen[$i] = ['M' => ['aprobados' => 0, 'reprobados' => 0], 'F' => ['aprobados' => 0, 'reprobados' => 0], 'total' => ['aprobados' => 0, 'reprobados' => 0]];
    }
}

foreach ($data as $row) {
    $pdf->Cell(8, 6, $contador, 1); // ✅ Mostrar el contador
    $pdf->Cell(15, 6, $row['codigo_nie'], 1);
    $pdf->SetFont('Arial', '', 6.5);
    $pdf->Cell(55, 6, (convertirTexto($row['nombre_estudiante'])), 1); // ✅ Nombre estudiante
    $pdf->SetFont('Arial', '', 8);

    if ($cantidad_periodos > 0) {
        for ($i = 1; $i <= $cantidad_periodos; $i++) {
            // Mostrar nota o blanco si es 0
            $nota_a1 = ($row["a1_$i"] == 0) ? '' : $row["a1_$i"];
            $nota_a2 = ($row["a2_$i"] == 0) ? '' : $row["a2_$i"];
            $nota_a3 = ($row["a3_$i"] == 0) ? '' : $row["a3_$i"];
            $nota_r = ($row["r_$i"] == 0) ? '' : $row["r_$i"];

            $pdf->Cell(8, 6, $nota_a1, 1);
            $pdf->Cell(8, 6, $nota_a2, 1);
            $pdf->Cell(8, 6, $nota_a3, 1);
            $pdf->Cell(8, 6, $nota_r, 1);

            // Sombreado y color rojo si es menor que la calificación mínima
            $pdf->SetFillColor(243, 243, 243);
            if ($row["pp_$i"] < $calificacion_minima) {
                $pdf->SetTextColor(255, 0, 0); // Rojo
            }
            $nota_pp = ($row["pp_$i"] == 0) ? '' : $row["pp_$i"];
            $pdf->Cell(10, 6, $nota_pp, 1, 0, "C", 1);
            $pdf->SetTextColor(0, 0, 0); // Restablecer color
            $pdf->SetFillColor(255, 255, 255);

            // Actualizar resumen de períodos
            $genero = ($row['codigo_genero'] == '01') ? 'M' : 'F';
            if ($row["pp_$i"] >= $calificacion_minima) {
                $resumen[$i][$genero]['aprobados']++;
                $resumen[$i]['total']['aprobados']++;
            } else {
                $resumen[$i][$genero]['reprobados']++;
                $resumen[$i]['total']['reprobados']++;
            }
        }
    }
    // Mostrar R1 y R2 o blanco si es 0
    $r1 = ($row["recuperacion"] == 0) ? '' : $row["recuperacion"];
    $r2 = ($row["nota_recuperacion_2"] == 0) ? '' : $row["nota_recuperacion_2"];

    $pdf->Cell(10, 6, $r1, 1);
    $pdf->Cell(10, 6, $r2, 1);
    $pdf->Cell(10, 6, $row["nota_final"], 1);

    // Actualizar resumen de Nota Final
    $genero = ($row['codigo_genero'] == '01') ? 'M' : 'F';
    if ($row["nota_final"] >= $calificacion_minima) {
        $resumen['NF'][$genero]['aprobados']++;
        $resumen['NF']['total']['aprobados']++;
    } else {
        $resumen['NF'][$genero]['reprobados']++;
        $resumen['NF']['total']['reprobados']++;
    }

    $pdf->Ln();
    $contador++; // ✅ Incrementar el contador
}

$pdf->Ln(5);

// Resumen de Aprobados y Reprobados por Período
if ($cantidad_periodos > 0) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 4, convertirTexto('Resumen de Aprobados y Reprobados por Período:'), 0, 1, 'L');
    $pdf->Ln(2);

    for ($i = 1; $i <= $cantidad_periodos; $i++) {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(0, 4, convertirTexto('Período ') . $i . ':', 0, 1, 'L');
        $pdf->SetFont('Arial', '', 8);

        $pdf->Cell(20, 4, convertirTexto('Género'), 1);
        $pdf->Cell(20, 4, 'Aprobados', 1);
        $pdf->Cell(20, 4, 'Reprobados', 1);
        $pdf->Cell(20, 4, 'Total', 1);
        $pdf->Ln();

        $pdf->Cell(20, 4, 'Masculino', 1);
        $pdf->Cell(20, 4, $resumen[$i]['M']['aprobados'], 1);
        $pdf->Cell(20, 4, $resumen[$i]['M']['reprobados'], 1);
        $pdf->Cell(20, 4, $resumen[$i]['M']['aprobados'] + $resumen[$i]['M']['reprobados'], 1);
        $pdf->Ln();

        $pdf->Cell(20, 4, 'Femenino', 1);
        $pdf->Cell(20, 4, $resumen[$i]['F']['aprobados'], 1);
        $pdf->Cell(20, 4, $resumen[$i]['F']['reprobados'], 1);
        $pdf->Cell(20, 4, $resumen[$i]['F']['aprobados'] + $resumen[$i]['F']['reprobados'], 1);
        $pdf->Ln();

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(20, 4, 'Total', 1);
        $pdf->Cell(20, 4, $resumen[$i]['total']['aprobados'], 1);
        $pdf->Cell(20, 4, $resumen[$i]['total']['reprobados'], 1);
        $pdf->Cell(20, 4, $resumen[$i]['total']['aprobados'] + $resumen[$i]['total']['reprobados'], 1);
        $pdf->Ln();
        $pdf->Ln(2);
    }
}

// Resumen de Nota Final
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 4, convertirTexto('Resumen de Aprobados y Reprobados de Nota Final:'), 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('Arial', '', 8);
$pdf->Cell(20, 4, convertirTexto('Género'), 1);
$pdf->Cell(20, 4, 'Aprobados', 1);
$pdf->Cell(20, 4, 'Reprobados', 1);
$pdf->Cell(20, 4, 'Total', 1);
$pdf->Ln();

$pdf->Cell(20, 4, 'Masculino', 1);
$pdf->Cell(20, 4, $resumen['NF']['M']['aprobados'], 1);
$pdf->Cell(20, 4, $resumen['NF']['M']['reprobados'], 1);
$pdf->Cell(20, 4, $resumen['NF']['M']['aprobados'] + $resumen['NF']['M']['reprobados'], 1);
$pdf->Ln();

$pdf->Cell(20, 4, 'Femenino', 1);
$pdf->Cell(20, 4, $resumen['NF']['F']['aprobados'], 1);
$pdf->Cell(20, 4, $resumen['NF']['F']['reprobados'], 1);
$pdf->Cell(20, 4, $resumen['NF']['F']['aprobados'] + $resumen['NF']['F']['reprobados'], 1);
$pdf->Ln();

$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(20, 4, 'Total', 1);
$pdf->Cell(20, 4, $resumen['NF']['total']['aprobados'], 1);
$pdf->Cell(20, 4, $resumen['NF']['total']['reprobados'], 1);
$pdf->Cell(20, 4, $resumen['NF']['total']['aprobados'] + $resumen['NF']['total']['reprobados'], 1);
$pdf->Ln();

// Salida del PDF (lo muestra en el navegador)
$pdf->Output('informe_asignatura.pdf', 'I');

function convertirTextos($texto) {
    $texto = mb_strtolower($texto, "ISO-8859-1"); // Convierte todo a minúsculas
    $texto = mb_strtoupper(mb_substr($texto, 0, 1, "ISO-8859-1"), "ISO-8859-1") . mb_substr($texto, 1, null, "ISO-8859-1");
    return $texto;
}