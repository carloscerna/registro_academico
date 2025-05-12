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
$query_asignaturas = "SELECT codigo, nombre FROM asignatura WHERE codigo_bachillerato = :codigo_bachillerato";
$stmt_asignaturas = $pdo->prepare($query_asignaturas);
$stmt_asignaturas->bindParam(':codigo_bachillerato', $codigo_bachillerato);
$stmt_asignaturas->execute();
$asignaturas = $stmt_asignaturas->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener los datos de los estudiantes y sus calificaciones
$sql = "
    SELECT 
        a.codigo_nie,
        a.codigo_genero,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as nombre_estudiante,
        asig.codigo AS codigo_asignatura,
        asig.nombre AS nombre_asignatura,
        " . ($cantidad_periodos > 0 ? ", " . implode(", ", array_map(function ($i) {
            return "n.nota_a1_$i AS a1_$i, n.nota_a2_$i AS a2_$i, n.nota_a3_$i AS a3_$i, n.nota_r_$i AS r_$i, n.nota_p_p_$i AS pp_$i";
        }, range(1, $cantidad_periodos))) : "") . "
        , n.recuperacion, n.nota_recuperacion_2, n.nota_final
    FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
    WHERE am.codigo_bach_o_ciclo = :codigo_bachillerato
    AND am.codigo_grado = :codigo_grado
    AND am.codigo_seccion = :codigo_seccion
    AND am.codigo_ann_lectivo = :codigo_annlectivo
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

$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 8);

// Encabezados de la tabla
$pdf->Cell(8, 8, 'N.º', 1);
$pdf->Cell(15, 8, 'NIE', 1);
$pdf->Cell(55, 8, 'Estudiante', 1);

// Encabezados dinámicos (asignaturas y períodos)
$x = $pdf->GetX();
$y = $pdf->GetY();
foreach ($asignaturas as $asignatura) {
    $pdf->SetXY($x, $y);
    $pdf->Cell(30, 8, convertirTexto($asignatura['nombre']), 1, 0, 'C'); // Título de la asignatura
    $x += 30;
}
$pdf->Ln();

$x = 8 + 15 + 55; // Posición X inicial para las subcolumnas
$y = $pdf->GetY();
foreach ($asignaturas as $asignatura) {
    $pdf->SetXY($x, $y);
    if ($cantidad_periodos > 0) {
        for ($i = 1; $i <= $cantidad_periodos; $i++) {
            $pdf->Cell(6, 4, $i, 1, 0, 'C'); // Período
        }
    }
    $pdf->Cell(6, 4, 'R1', 1, 0, 'C');
    $pdf->Cell(6, 4, 'R2', 1, 0, 'C');
    $pdf->Cell(6, 4, 'NF', 1, 0, 'C');
    $x += 30;
}
$pdf->Ln();

// Datos de la tabla
$pdf->SetFont('Arial', '', 8);
$contador = 1;
foreach ($data as $row) {
    $pdf->Cell(8, 6, $contador, 1);
    $pdf->Cell(15, 6, $row['codigo_nie'], 1);
    $pdf->Cell(55, 6, convertirTexto($row['nombre_estudiante']), 1);

    $x = 8 + 15 + 55;
    $y = $pdf->GetY();
    foreach ($asignaturas as $asignatura) {
        $pdf->SetXY($x, $y);
        if ($cantidad_periodos > 0) {
            for ($i = 1; $i <= $cantidad_periodos; $i++) {
                $pdf->Cell(6, 6, ($row['codigo_asignatura'] == $asignatura['codigo']) ? ($row["a1_$i"] == 0 ? '' : round($row["pp_$i"], 1)) : '', 1, 0, 'C');
            }
        }
        $pdf->Cell(6, 6, ($row['codigo_asignatura'] == $asignatura['codigo']) ? ($row["recuperacion"] == 0 ? '' : $row["recuperacion"]) : '', 1, 0, 'C');
        $pdf->Cell(6, 6, ($row['codigo_asignatura'] == $asignatura['codigo']) ? ($row["nota_recuperacion_2"] == 0 ? '' : $row["nota_recuperacion_2"]) : '', 1, 0, 'C');
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(6, 6, ($row['codigo_asignatura'] == $asignatura['codigo']) ? ($row["nota_final"] == 0 ? '' : round($row["nota_final"], 0)) : '', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $x += 30;
    }
    $pdf->Ln();
    $contador++;
}

// Salida del PDF
$pdf->Output('informe_por_modalidad.pdf', 'I');

function convertirTextos($texto)
{
    $texto = mb_strtolower($texto, "ISO-8859-1"); // Convierte todo a minúsculas
    $texto = mb_strtoupper(mb_substr($texto, 0, 1, "ISO-8859-1"), "ISO-8859-1") . mb_substr($texto, 1, null, "ISO-8859-1");
    return $texto;
}
?>