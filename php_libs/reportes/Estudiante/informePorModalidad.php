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
        asig.nombre AS nombre
    FROM a_a_a_bach_o_ciclo a
    INNER JOIN asignatura asig ON asig.codigo = a.codigo_asignatura  
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
        a.codigo_genero,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as nombre_estudiante,
        asig.codigo AS codigo_asignatura,
        asig.nombre AS nombre_asignatura,
        n.nota_p_p_1,
        n.nota_p_p_2,
        n.nota_p_p_3,
        n.nota_p_p_4,
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

// Obtener la cantidad de períodos para la modalidad
// AGRUPAR LOS REGISTROS POR ESTUDIANTE (usando codigo_nie como índice)
$groupedData = [];
foreach ($data as $row) {
    $nie = $row['codigo_nie'];
    if (!isset($groupedData[$nie])) {
        $groupedData[$nie] = [
            'codigo_nie'       => $row['codigo_nie'],
            'nombre_estudiante'=> $row['nombre_estudiante'],
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
        'recuperacion'       => $row['recuperacion'],
        'nota_recuperacion_2'=> $row['nota_recuperacion_2'],
        'nota_final'         => $row['nota_final']
    ];
}


// Definir anchos
$cellWidth    = 6;              // para cada subcolumna
$subjectWidth = 7 * $cellWidth;   // cada asignatura ocupará 7 subcolumnas (42 mm)

// Encabezado del PDF (dos niveles)
$pdf->SetFont('Arial', 'B', 7);

// Primera fila del encabezado: Datos generales y nombres de asignaturas
$pdf->Cell(10, 6, 'N°', 1, 0, 'C');
$pdf->Cell(30, 6, 'Código NIE', 1, 0, 'C');
$pdf->Cell(60, 6, 'Nombre del Estudiante', 1, 0, 'C');
foreach ($asignaturas as $asig) {
    $pdf->Cell($subjectWidth, 6, $asig['nombre'], 1, 0, 'C');
}
$pdf->Ln();

// Segunda fila (subencabezados por asignatura)
$pdf->Cell(10, 6, '', 0, 0);
$pdf->Cell(30, 6, '', 0, 0);
$pdf->Cell(60, 6, '', 0, 0);
$subHeaders = ['P1', 'P2', 'P3', 'P4', 'REC', 'NR2', 'NF'];
foreach ($asignaturas as $asig) {
    foreach ($subHeaders as $sub) {
        $pdf->Cell($cellWidth, 6, $sub, 1, 0, 'C');
    }
}
$pdf->Ln();

// Definir anchos: las celdas de evaluaciones miden 6 mm cada una
$cellWidth    = 6;              // Ancho para cada subcolumna (6 mm)
$subjectWidth = 7 * $cellWidth;   // Cada asignatura ocupará 7 celdas (42 mm)

// --- Datos de los estudiantes (cada uno en una sola fila) ---
$i = 1;
foreach ($groupedData as $student) {
    // Datos generales del estudiante
    $pdf->Cell(10, 6, $i, 1, 0, 'C');
    $pdf->Cell(30, 6, $student['codigo_nie'], 1, 0, 'C');
    $pdf->Cell(60, 6, $student['nombre_estudiante'], 1, 0, 'L');
    
    // Recorrer las asignaturas en el mismo orden definido en $asignaturas
    foreach ($asignaturas as $asig) {
        // Forzar la clave a string sin espacios
        $subjectCode = trim((string)$asig['codigo']);
        if (isset($student['subjects'][$subjectCode])) {
            $subjData = $student['subjects'][$subjectCode];
            
            // Definimos el orden de los campos (siempre son 7)
            $fields = [
                'nota_p_p_1',      // Periodo 1
                'nota_p_p_2',      // Periodo 2
                'nota_p_p_3',      // Periodo 3
                'nota_p_p_4',      // Periodo 4
                'recuperacion',    // Recuperación
                'nota_recuperacion_2', // Recuperación 2
                'nota_final'       // Nota final
            ];
            
            foreach ($fields as $field) {
                // Obtenemos el valor (asumimos que es numérico o bien una cadena numérica)
                $val = $subjData[$field];
                // Si el valor es 0 (o equivalente) se muestra en blanco
                if (floatval($val) == 0) {
                    $display = "";
                } else {
                    $display = $val;
                }
                
                // Para los campos de periodos y la nota final, si existe un valor (no es blanco)
                // y es menor que la calificación mínima, se pinta en rojo
                if (in_array($field, ['nota_p_p_1', 'nota_p_p_2', 'nota_p_p_3', 'nota_p_p_4', 'nota_final']) &&
                    $display !== "" && floatval($val) < $calificacion_minima) {
                    $pdf->SetTextColor(255, 0, 0); // Rojo
                }
                
                $pdf->Cell($cellWidth, 6, $display, 1, 0, 'C');
                $pdf->SetTextColor(0, 0, 0); // Volver al color negro (por defecto)
            }
        } else {
            // Si el estudiante no tiene datos para esta asignatura, se imprimen 7 celdas en blanco
            for ($j = 0; $j < 7; $j++) {
                $pdf->Cell($cellWidth, 6, '', 1, 0, 'C');
            }
        }
    }
    $pdf->Ln();
    $i++;
}

$pdf->Output("I", "informe.pdf");// Establecer la fuente para los datos

function convertirTextos($texto)
{
    $texto = mb_strtolower($texto, "ISO-8859-1"); // Convierte todo a minúsculas
    $texto = mb_strtoupper(mb_substr($texto, 0, 1, "ISO-8859-1"), "ISO-8859-1") . mb_substr($texto, 1, null, "ISO-8859-1");
    return $texto;
}