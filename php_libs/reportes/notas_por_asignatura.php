<?php
// <-- VERSIÓN REFACTORIZADA Y UNIFICADA: notas_por_asignatura.php -->

date_default_timezone_set('America/El_Salvador');
ini_set('display_errors', 1); error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/funciones_2.php"; // Para verificar_nota, etc.
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_ASIGNATURA', 28); // Ajustar según necesidad

/**
 * Clase FPDF personalizada.
 */
class PDF_NotasAsignatura extends FPDF {
    public $datosEncabezado = [];
    public $nombreAsignatura = '';
    public $nombreDocente = '';
    public $numPeriodos = 3; // Valor por defecto

    function Header() {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) { $this->Image($logoPath, 10, 8, 15); }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, convertirtexto($_SESSION['institucion']), 0, 1, 'C');
        $this->Cell(0, 6, convertirtexto('INFORME DE NOTAS POR ASIGNATURA'), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, 'Asignatura: ' . convertirtexto($this->nombreAsignatura), 0, 1, 'L');
        $this->Cell(0, 5, 'Docente: ' . convertirtexto($this->nombreDocente), 0, 1, 'L');
        $this->Cell(80, 5, 'Modalidad: ' . convertirtexto($this->datosEncabezado['nombre_bachillerato']), 0, 0, 'L');
        $this->Cell(40, 5, 'Grado: ' . convertirtexto($this->datosEncabezado['nombre_grado']), 0, 0, 'L');
        $this->Cell(30, 5, 'Seccion: ' . convertirtexto($this->datosEncabezado['nombre_seccion']), 0, 0, 'L');
        $this->Cell(0, 5, 'Ano Lectivo: ' . convertirtexto($this->datosEncabezado['nombre_ann_lectivo']), 0, 1, 'L');
        
        $this->Ln(3);
    }

    function Footer() { /* ... Implementa el footer estándar ... */ }

    function TablaEncabezado() {
        $this->SetFillColor(220, 220, 220); $this->SetTextColor(0);
        $this->SetFont('Arial', 'B', 7);
        $this->Cell(8, 7, 'N#', 1, 0, 'C', true);
        $this->Cell(15, 7, 'NIE', 1, 0, 'C', true);
        $this->Cell(65, 7, 'APELLIDO - NOMBRE', 1, 0, 'C', true);

        // Columnas de Período dinámicas
        $anchoPeriodo = 10;
        for ($i = 1; $i <= $this->numPeriodos; $i++) {
            $this->Cell($anchoPeriodo, 7, 'P' . $i, 1, 0, 'C', true);
        }

        // Columnas de Resultados
        $this->Cell(10, 7, 'TP', 1, 0, 'C', true);  // Total Puntos
        $this->Cell(10, 7, 'PROM', 1, 0, 'C', true); // Promedio
        $this->Cell(10, 7, 'NR1', 1, 0, 'C', true); // Nota Recuperación 1
        $this->Cell(10, 7, 'NR2', 1, 0, 'C', true); // Nota Recuperación 2
        $this->Cell(10, 7, 'NF', 1, 1, 'C', true);  // Nota Final
    }
}

/**
 * Obtiene todos los datos necesarios.
 */
function obtenerDatosPorAsignatura(PDO $pdo, string $codigoAll, string $codigoAsignatura): array {
    $datos = ['encabezado' => [], 'notas' => [], 'asignatura_info' => []];

    // Consulta de encabezado y datos de período/nota mínima
    $sqlEncabezado = "SELECT btrim(bach.nombre) as nombre_bachillerato, am.codigo_bach_o_ciclo, btrim(gan.nombre) as nombre_grado, btrim(sec.nombre) as nombre_seccion, ann.nombre as nombre_ann_lectivo,
                      cp.cantidad_periodos, cp.calificacion_minima
                      FROM alumno_matricula am
                      INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                      INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                      INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                      INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                      LEFT JOIN catalogo_periodos cp ON bach.codigo = cp.codigo_modalidad -- Unir con catalogo_periodos
                      WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all LIMIT 1";
    $stmt = $pdo->prepare($sqlEncabezado); $stmt->bindParam(':codigo_all', $codigoAll); $stmt->execute();
    $datos['encabezado'] = $stmt->fetch(PDO::FETCH_ASSOC);
    // Establecer valores por defecto si no se encuentran en catalogo_periodos
    $datos['encabezado']['nota_minima'] = $datos['encabezado']['calificacion_minima'] ?? 5.0;
    $datos['encabezado']['num_periodos'] = $datos['encabezado']['cantidad_periodos'] ?? 3;


    // Consulta nombre de asignatura y docente
    $sqlAsigDocente = "SELECT asig.nombre as nombre_asignatura, btrim(p.nombres || ' ' || p.apellidos) as nombre_docente
                      FROM asignatura asig
                      LEFT JOIN carga_docente cd ON asig.codigo = cd.codigo_asignatura
                          AND btrim(cd.codigo_bachillerato::text || cd.codigo_grado::text || cd.codigo_seccion::text || cd.codigo_ann_lectivo::text || cd.codigo_turno::text) = :codigo_all
                      LEFT JOIN personal p ON cd.codigo_docente::int = p.id_personal
                      WHERE asig.codigo = :codigo_asignatura LIMIT 1";
    $stmtAsig = $pdo->prepare($sqlAsigDocente);
    $stmtAsig->bindParam(':codigo_all', $codigoAll);
    $stmtAsig->bindParam(':codigo_asignatura', $codigoAsignatura);
    $stmtAsig->execute();
    $datos['asignatura_info'] = $stmtAsig->fetch(PDO::FETCH_ASSOC);

    // Consulta principal para notas de la asignatura seleccionada
    $sqlNotas = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as nombre_completo,
                 n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5,
                 n.nota_final, n.recuperacion, n.nota_recuperacion_2
                 FROM alumno a
                 INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                 INNER JOIN nota n ON am.id_alumno_matricula = n.codigo_matricula AND n.codigo_asignatura = :codigo_asignatura
                 WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all
                 ORDER BY nombre_completo";
    $stmtNotas = $pdo->prepare($sqlNotas);
    $stmtNotas->bindParam(':codigo_all', $codigoAll);
    $stmtNotas->bindParam(':codigo_asignatura', $codigoAsignatura);
    $stmtNotas->execute();
    $datos['notas'] = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfPorAsignatura(array $datos) {
    $notaMinima = floatval($datos['encabezado']['nota_minima']);
    $numPeriodos = intval($datos['encabezado']['num_periodos']);
    
    // --- GENERAR PDF ---
    $pdf = new PDF_NotasAsignatura('P', 'mm', 'Letter');
    $pdf->SetMargins(10, 15, 10); $pdf->SetAutoPageBreak(true, 15); $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];
    $pdf->nombreAsignatura = $datos['asignatura_info']['nombre_asignatura'] ?? 'Desconocida';
    $pdf->nombreDocente = $datos['asignatura_info']['nombre_docente'] ?? 'No asignado';
    $pdf->numPeriodos = $numPeriodos; // Pasar número de períodos a la clase PDF

    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8); $fill = false; $numFila = 0;
    $anchoPeriodo = 10;
    
    $sumatorias = array_fill(1, $numPeriodos + 5, 0); // Índices 1-numPeriodos + TP, PROM, NR1, NR2, NF
    $numAlumnosValidos = 0;
    $alumnosPuntajesNF = []; // Array para guardar puntajes NF para el Top 5

    foreach ($datos['notas'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_ASIGNATURA == 0) { $pdf->AddPage(); $pdf->TablaEncabezado(); $pdf->SetFont('Arial', '', 8); }
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);

        $pdf->Cell(8, 6, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(15, 6, $alumno['codigo_nie'], 1, 0, 'C', true);
        $pdf->Cell(65, 6, convertirtexto($alumno['nombre_completo']), 1, 0, 'L', true);

        $totalPuntosAlumno = 0;
        $numNotasPeriodoValidas = 0;
        $tieneNotas = false;

        // Notas de Período dinámicas
        for ($i = 1; $i <= $numPeriodos; $i++) {
            $campoNota = 'nota_p_p_' . $i;
            $notaVal = isset($alumno[$campoNota]) ? floatval($alumno[$campoNota]) : null;

            if($notaVal !== null && $notaVal >= 0) {
                 if($notaVal < $notaMinima && $notaVal > 0) { $pdf->SetTextColor(255, 0, 0); }
                 $pdf->Cell($anchoPeriodo, 6, number_format($notaVal, 2), 1, 0, 'C', true);
                 $pdf->SetTextColor(0);
                 if ($notaVal > 0) {
                     $totalPuntosAlumno += $notaVal;
                     $sumatorias[$i] += $notaVal;
                     $numNotasPeriodoValidas++;
                     $tieneNotas = true;
                 }
            } else {
                 $pdf->Cell($anchoPeriodo, 6, '', 1, 0, 'C', true);
            }
        }

        // TP y PROM
        $promedioAlumno = ($numNotasPeriodoValidas > 0) ? ($totalPuntosAlumno / $numNotasPeriodoValidas) : 0;
        $pdf->Cell(10, 6, ($totalPuntosAlumno > 0) ? number_format($totalPuntosAlumno, 2) : '0.00', 1, 0, 'C', true);
        $pdf->Cell(10, 6, ($promedioAlumno > 0) ? number_format($promedioAlumno, 2) : '0.00', 1, 0, 'C', true);
        if($tieneNotas) {
             $sumatorias[$numPeriodos + 1] += $totalPuntosAlumno; // Suma TP
             $sumatorias[$numPeriodos + 2] += $promedioAlumno;    // Suma PROM
             $numAlumnosValidos++;
        }

        // NR1, NR2
        $nr1 = floatval($alumno['recuperacion']);
        $nr2 = floatval($alumno['nota_recuperacion_2']);
        if($nr1 > 0 && $nr1 < $notaMinima) { $pdf->SetTextColor(255, 0, 0); }
        $pdf->Cell(10, 6, ($nr1 > 0) ? number_format($nr1, 2) : '', 1, 0, 'C', true); $pdf->SetTextColor(0);
        if($nr2 > 0 && $nr2 < $notaMinima) { $pdf->SetTextColor(255, 0, 0); }
        $pdf->Cell(10, 6, ($nr2 > 0) ? number_format($nr2, 2) : '', 1, 0, 'C', true); $pdf->SetTextColor(0);
        if($nr1 > 0) $sumatorias[$numPeriodos + 3] += $nr1;
        if($nr2 > 0) $sumatorias[$numPeriodos + 4] += $nr2;

        // NF (calculada)
        $notaFinalVerificada = verificar_nota($alumno['nota_final'], $alumno['recuperacion'], $alumno['nota_recuperacion_2']);
        $nfVal = floatval($notaFinalVerificada);
        if($nfVal > 0 && $nfVal < $notaMinima) { $pdf->SetTextColor(255, 0, 0); }
        $pdf->Cell(10, 6, ($nfVal > 0) ? number_format($nfVal, 2) : '', 1, 1, 'C', true); $pdf->SetTextColor(0);
        if($nfVal > 0) $sumatorias[$numPeriodos + 5] += $nfVal;
        
        // Guardar datos para Top 5 (usando NF calculada)
        $alumnosPuntajesNF[] = ['id' => $alumno['id_alumno'], 'nie' => $alumno['codigo_nie'], 'nombre' => $alumno['nombre_completo'], 'puntaje' => $nfVal];

        $fill = !$fill; $numFila++;
    }

    // Filas de TOTAL y PROMEDIO
    $pdf->SetFont('Arial','B', 8);
    $pdf->Cell(88, 6, 'TOTAL DE PUNTOS', 1, 0, 'R', true); // 8+15+65
    for ($i = 1; $i <= $numPeriodos + 5; $i++) { $suma = $sumatorias[$i] ?? 0; $pdf->Cell(10, 6, ($suma > 0) ? number_format($suma, 2) : '', 1, 0, 'C', true); }
    $pdf->Ln();
    $pdf->Cell(88, 6, 'PROMEDIO', 1, 0, 'R', true);
    for ($i = 1; $i <= $numPeriodos + 5; $i++) { $suma = $sumatorias[$i] ?? 0; $promedio = ($numAlumnosValidos > 0 && $suma > 0) ? round($suma / $numAlumnosValidos) : ''; $pdf->Cell(10, 6, $promedio, 1, 0, 'C', true); }
    $pdf->Ln();

    // --- TABLA TOP 5 (basado en NF calculada) ---
    usort($alumnosPuntajesNF, function($a, $b) { return $b['puntaje'] <=> $a['puntaje']; });
    $top5 = array_slice($alumnosPuntajesNF, 0, 5);

    $pdf->Ln(10); $pdf->SetFont('Arial', 'B', 10); $pdf->Cell(0, 7, 'Alumnos con Mejor Nota Final en la Asignatura', 0, 1, 'C'); $pdf->Ln(2);
    $pdf->SetFont('Arial', 'B', 8); $pdf->SetFillColor(220, 220, 220);
    $pdf->Cell(10, 7, 'N#', 1, 0, 'C', true); $pdf->Cell(20, 7, 'NIE', 1, 0, 'C', true); $pdf->Cell(100, 7, 'Nombre del Alumno', 1, 0, 'C', true); $pdf->Cell(20, 7, 'Nota Final', 1, 1, 'C', true);
    $pdf->SetFont('Arial', '', 8); $fillTop = false;
    foreach ($top5 as $index => $topAlumno) {
        $pdf->SetFillColor($fillTop ? 240 : 255, $fillTop ? 240 : 255, $fillTop ? 240 : 255);
        $pdf->Cell(10, 6, $index + 1, 1, 0, 'C', true);
        $pdf->Cell(20, 6, $topAlumno['nie'], 1, 0, 'C', true);
        $pdf->Cell(100, 6, convertirtexto($topAlumno['nombre']), 1, 0, 'L', true);
        $pdf->Cell(20, 6, ($topAlumno['puntaje'] > 0) ? number_format($topAlumno['puntaje'], 2) : '', 1, 1, 'C', true);
        $fillTop = !$fillTop;
    }

    $pdf->Output('Notas_por_Asignatura.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("..."); }
    $codigo_all = $_GET["todos"] ?? null;
    $codigo_asignatura = $_GET["lstasignatura"] ?? null; // Parámetro de la asignatura

    if (!$codigo_all || !$codigo_asignatura) { throw new Exception("Faltan parámetros (grupo o asignatura)."); }

    $datosReporte = obtenerDatosPorAsignatura($dblink, $codigo_all, $codigo_asignatura);

    if (empty($datosReporte['notas'])) { echo "No se encontraron notas para esta asignatura y grupo."; exit; }

    generarPdfPorAsignatura($datosReporte);

} catch (PDOException $e) { /* ... */ } catch (Exception $e) { /* ... */ }
?>