<?php
// <-- VERSIÓN REFACTORIZADA Y SEGURA: Familias.php -->

// Establecer la zona horaria correcta para El Salvador
date_default_timezone_set('America/El_Salvador');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

/**
 * Clase FPDF personalizada para el reporte de Familias.
 */
class PDF_Familias extends FPDF {
    function Header() {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 15);
        }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, convertirtexto($_SESSION['institucion']), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, convertirtexto('INFORME DE GRUPOS FAMILIARES'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $fechaFormateada = "Santa Ana, $dia de $mes de $anio";
        $horaFormateada = date('g:i a');
        $textoFooter = "$fechaFormateada - $horaFormateada | Pagina " . $this->PageNo() . ' de {nb}';
        $this->Cell(0, 10, convertirtexto($textoFooter), 0, 0, 'C');
    }
}

/**
 * Obtiene todos los datos para el informe de familias.
 */
function obtenerDatosFamilias(PDO $pdo, string $codigo_ann_lectivo): array {
    $datos = [];

    // Consulta principal
    $query_estudiantes = "SELECT ae.dui AS dui_encargado, TRIM(ae.nombres) AS nombre_encargado,
                          TRIM(a.nombre_completo || ' ' || a.apellido_paterno || ' ' || a.apellido_materno) AS nombre_estudiante,
                          TRIM(g.nombre) || ' ' || TRIM(s.nombre) AS grado_seccion, a.codigo_genero
                          FROM public.alumno_encargado ae
                          INNER JOIN public.alumno a ON a.id_alumno = ae.codigo_alumno
                          INNER JOIN public.alumno_matricula am ON am.codigo_alumno = a.id_alumno
                          INNER JOIN public.grado_ano g ON g.codigo = am.codigo_grado
                          INNER JOIN public.seccion s ON s.codigo = am.codigo_seccion
                          WHERE am.codigo_ann_lectivo = :codigo_ann_lectivo AND am.retirado = 'f' AND ae.encargado = 't' AND ae.dui IS NOT NULL AND ae.dui != ''
                          ORDER BY nombre_encargado, nombre_estudiante";
    $stmt = $pdo->prepare($query_estudiantes);
    $stmt->bindParam(':codigo_ann_lectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
    $stmt->execute();
    $datos['lista_completa'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para resumen de encargados
    $query_encargados = "SELECT DISTINCT ae.dui, ae.codigo_genero, cf.descripcion AS parentesco
                         FROM public.alumno_encargado ae
                         INNER JOIN public.catalogo_familiar cf ON ae.codigo_familiar = cf.codigo
                         WHERE ae.encargado = 't' AND ae.dui IS NOT NULL AND ae.dui != ''
                         AND ae.codigo_alumno IN (SELECT m.codigo_alumno FROM public.alumno_matricula m WHERE m.codigo_ann_lectivo = :codigo_ann_lectivo AND m.retirado = 'f')";
    $stmt = $pdo->prepare($query_encargados);
    $stmt->bindParam(':codigo_ann_lectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
    $stmt->execute();
    $datos['encargados_resumen'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfFamilias(array $datos) {
    // --- PROCESAMIENTO DE DATOS PARA RESÚMENES ---
    $total_estudiantes = count($datos['lista_completa']);
    $total_masculino = 0;
    $total_femenino = 0;
    $familias_agrupadas = [];

    foreach ($datos['lista_completa'] as $row) {
        $familias_agrupadas[$row['dui_encargado']] = true; // Solo para contar familias únicas
        if (trim($row['codigo_genero']) == '01') { $total_masculino++; }
        if (trim($row['codigo_genero']) == '02') { $total_femenino++; }
    }
    $total_familias = count($familias_agrupadas);

    $resumen_enc_genero = ['M' => 0, 'F' => 0];
    $resumen_parentesco = [];
    foreach ($datos['encargados_resumen'] as $encargado) {
        if (trim($encargado['codigo_genero']) == '01') { $resumen_enc_genero['M']++; }
        if (trim($encargado['codigo_genero']) == '02') { $resumen_enc_genero['F']++; }
        $parentesco = trim($encargado['parentesco']);
        if (!isset($resumen_parentesco[$parentesco])) { $resumen_parentesco[$parentesco] = 0; }
        $resumen_parentesco[$parentesco]++;
    }
    ksort($resumen_parentesco);

    // --- GENERACIÓN DEL PDF ---
    $pdf = new PDF_Familias('L', 'mm', 'Letter');
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Tabla principal
    $header = ['N#', 'Nombre del Encargado', 'DUI', 'Nombre del Estudiante', 'Grado y Sección'];
    $widths = [10, 75, 25, 75, 45];
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(50, 50, 50); $pdf->SetTextColor(255);
    for ($i = 0; $i < count($header); $i++) { $pdf->Cell($widths[$i], 7, convertirtexto($header[$i]), 1, 0, 'C', true); }
    $pdf->Ln();

    $pdf->SetFont('', '', 8);
    $pdf->SetFillColor(240, 240, 240); $pdf->SetTextColor(0);
    $fill = false;
    $previousDui = null;
    foreach ($datos['lista_completa'] as $index => $row) {
        $encargadoToShow = ($row['dui_encargado'] !== $previousDui) ? $row['nombre_encargado'] : '';
        $duiToShow = ($row['dui_encargado'] !== $previousDui) ? $row['dui_encargado'] : '';
        $previousDui = $row['dui_encargado'];

        $pdf->Cell($widths[0], 6, $index + 1, 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[1], 6, convertirtexto($encargadoToShow), 'LR', 0, 'L', $fill);
        $pdf->Cell($widths[2], 6, $duiToShow, 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[3], 6, convertirtexto($row['nombre_estudiante']), 'LR', 0, 'L', $fill);
        $pdf->Cell($widths[4], 6, convertirtexto($row['grado_seccion']), 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }
    $pdf->Cell(array_sum($widths), 0, '', 'T');
    
    // Página de Resúmenes
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirtexto('Resumen General de Estudiantes por Familia'), 0, 1, 'C');
    $pdf->Ln(5);
    
    $header_summary = ['Descripción', 'Cantidad Total', 'Promedio por Familia'];
    $data_summary = [
        ['Estudiantes Masculinos', $total_masculino, ($total_familias > 0) ? number_format($total_masculino / $total_familias, 2) : 0],
        ['Estudiantes Femeninos', $total_femenino, ($total_familias > 0) ? number_format($total_femenino / $total_familias, 2) : 0],
        ['Subtotal de Estudiantes', $total_estudiantes, ($total_familias > 0) ? number_format($total_estudiantes / $total_familias, 2) : 0]
    ];
    $pdf->SetFont('Arial','B',10); $pdf->SetFillColor(230,230,230); $w = [80, 40, 50];
    for($i=0;$i<count($header_summary);$i++) { $pdf->Cell($w[$i],7,convertirtexto($header_summary[$i]),1,0,'C',true); }
    $pdf->Ln(); $pdf->SetFont('','',9);
    foreach($data_summary as $row) { $pdf->Cell($w[0],6,convertirtexto($row[0]),'LR',0,'L'); $pdf->Cell($w[1],6,$row[1],'LR',0,'C'); $pdf->Cell($w[2],6,$row[2],'LR',0,'C'); $pdf->Ln(); }
    $pdf->Cell(array_sum($w),0,'','T');

    // Resumen de Encargados
    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(0, 10, convertirtexto('Resumen de Encargados Principales'), 0, 1, 'C'); $pdf->Ln(5);

    $y_pos_initial = $pdf->GetY();
    $pdf->SetX(60);
    $header_gen = ['Género del Encargado', 'Cantidad']; $data_gen = [['Masculino', $resumen_enc_genero['M']], ['Femenino', $resumen_enc_genero['F']]];
    $w_gen = [50, 25];
    $pdf->SetFont('Arial','B',10);
    for($i=0;$i<count($header_gen);$i++) { $pdf->Cell($w_gen[$i],7,convertirtexto($header_gen[$i]),1,0,'C',true); }
    $pdf->Ln(); $pdf->SetFont('','',9);
    foreach($data_gen as $row) { $pdf->SetX(60); $pdf->Cell($w_gen[0],6,convertirtexto($row[0]),'LR',0,'L'); $pdf->Cell($w_gen[1],6,$row[1],'LR',0,'C'); $pdf->Ln(); }
    $pdf->SetX(60); $pdf->Cell(array_sum($w_gen),0,'','T');

    $pdf->SetY($y_pos_initial);
    $pdf->SetX(145);
    $header_par = ['Parentesco', 'Cantidad']; $data_par = []; foreach($resumen_parentesco as $p => $c){ $data_par[] = [$p, $c]; }
    $w_par = [50, 25];
    $pdf->SetFont('Arial','B',10);
    for($i=0;$i<count($header_par);$i++) { $pdf->Cell($w_par[$i],7,convertirtexto($header_par[$i]),1,0,'C',true); }
    $pdf->Ln(); $pdf->SetFont('','',9);
    foreach($data_par as $row) { $pdf->SetX(145); $pdf->Cell($w_par[0],6,convertirtexto($row[0]),'LR',0,'L'); $pdf->Cell($w_par[1],6,$row[1],'LR',0,'C'); $pdf->Ln(); }
    $pdf->SetX(145); $pdf->Cell(array_sum($w_par),0,'','T');

    $pdf->Output('I', 'NominaFamilias.pdf');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }
    
    $codigo_ann_lectivo = $_GET["lstannlectivo"] ?? null;
    if (!$codigo_ann_lectivo) { throw new Exception("Falta el parámetro del año lectivo."); }

    $datosReporte = obtenerDatosFamilias($dblink, $codigo_ann_lectivo);

    if (empty($datosReporte['lista_completa'])) {
        echo "No se encontraron datos de familias para el año lectivo seleccionado.";
        exit;
    }

    generarPdfFamilias($datosReporte);

} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>