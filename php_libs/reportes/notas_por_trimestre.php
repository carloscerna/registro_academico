<?php
// <-- VERSIÓN FINAL CON AJUSTES DETALLADOS -->

date_default_timezone_set('America/El_Salvador');
ini_set('display_errors', 1); error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_TRIMESTRE', 28); // Un poco menos para dar espacio al Top 5

/**
 * Clase FPDF personalizada (con MultiCell en encabezado).
 */
class PDF_NotasTrimestre extends FPDF {
    public $datosEncabezado = [];
    public $asignaturasHeader = [];
    public $nombrePeriodo = '';
    public $notaMinima = 5.0; // Valor por defecto
function Header() {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) { $this->Image($logoPath, 10, 8, 15); }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, convertirtexto($_SESSION['institucion']), 0, 1, 'C');
        $this->Cell(0, 6, convertirtexto('INFORME DE NOTAS POR ' . strtoupper($this->nombrePeriodo)), 0, 1, 'C');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(80, 5, 'Modalidad: ' . convertirtexto($this->datosEncabezado['nombre_bachillerato']), 0, 0, 'L');
        $this->Cell(40, 5, 'Grado: ' . convertirtexto($this->datosEncabezado['nombre_grado']), 0, 0, 'L');
        $this->Cell(30, 5, 'Seccion: ' . convertirtexto($this->datosEncabezado['nombre_seccion']), 0, 0, 'L');
        $this->Cell(0, 5, 'Ano Lectivo: ' . convertirtexto($this->datosEncabezado['nombre_ann_lectivo']), 0, 1, 'L');
        
        $this->Ln(5);
    }

function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d'); $mes = $meses[date('n') - 1]; $anio = date('Y');
        $fechaFormateada = "Santa Ana, $dia de $mes de $anio";
        $horaFormateada = date('g:i a');
        $textoFooter = "$fechaFormateada - $horaFormateada | Pagina " . $this->PageNo() . ' de {nb}';
        $this->Cell(0, 10, convertirtexto($textoFooter), 0, 0, 'C');
    }

    function TablaEncabezado() {
        $this->SetFillColor(220, 220, 220); $this->SetTextColor(0);
        $this->SetFont('Arial', 'B', 7);
        $y1 = $this->GetY();
        $this->Cell(8, 10, 'N#', 1, 0, 'C', true); // Altura 10 para dos líneas
        $this->Cell(15, 10, 'NIE', 1, 0, 'C', true); // Nueva columna NIE
        $this->Cell(65, 10, 'APELLIDO - NOMBRE', 1, 0, 'C', true); // Ancho ajustado

        $numAsignaturas = count($this->asignaturasHeader);
        // Ancho disponible: 190 (total usable) - 8 (N) - 15 (NIE) - 65 (Nombre) - 10 (TP) = 92
        $anchoAsignatura = ($numAsignaturas > 0) ? (92 / $numAsignaturas) : 10;

        $x_inicio_asig = $this->GetX(); // Guardar X antes de asignaturas

        foreach ($this->asignaturasHeader as $asignatura) {
             $x1 = $this->GetX(); // Posición X actual
             $y1 = $this->GetY(); // Posición Y actual
             // Usar MultiCell para permitir el ajuste automático
             $this->MultiCell($anchoAsignatura, 5, convertirtexto($asignatura['nombre']), 1, 'C', true);
             // Regresar a la posición Y original y moverse a la derecha para la siguiente celda
             $this->SetXY($x1 + $anchoAsignatura, $y1);
        }
        // Asegurarse de estar en la posición Y correcta después de MultiCell
        $this->SetXY($x_inicio_asig + ($anchoAsignatura * $numAsignaturas), $y1);

        $this->Cell(10, 10, 'T.P.', 1, 1, 'C', true); // Total Puntos
    }
}

/**
 * Obtiene todos los datos necesarios, incluyendo nota mínima.
 */
function obtenerDatosPorTrimestre(PDO $pdo, string $codigoAll, string $campoNota): array {
    $datos = ['encabezado' => [], 'notas' => [], 'asignaturas' => []];

    // Consulta de encabezado (añadir codigo_bach_o_ciclo)
    $sqlEncabezado = "SELECT btrim(bach.nombre) as nombre_bachillerato, am.codigo_bach_o_ciclo, btrim(gan.nombre) as nombre_grado, btrim(sec.nombre) as nombre_seccion, ann.nombre as nombre_ann_lectivo
                      FROM alumno_matricula am
                      INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                      INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                      INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                      INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                      WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all LIMIT 1";
    $stmt = $pdo->prepare($sqlEncabezado); $stmt->bindParam(':codigo_all', $codigoAll); $stmt->execute();
    $datos['encabezado'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener nota mínima si tenemos el código de modalidad
    if ($datos['encabezado'] && isset($datos['encabezado']['codigo_bach_o_ciclo'])) {
        $codigoModalidad = $datos['encabezado']['codigo_bach_o_ciclo'];
        $sqlNotaMin = "SELECT calificacion_minima FROM catalogo_periodos WHERE codigo_modalidad = :codigo_modalidad LIMIT 1";
        $stmtMin = $pdo->prepare($sqlNotaMin);
        $stmtMin->bindParam(':codigo_modalidad', $codigoModalidad);
        $stmtMin->execute();
        $resultMin = $stmtMin->fetch(PDO::FETCH_ASSOC);
        $datos['encabezado']['nota_minima'] = $resultMin['calificacion_minima'] ?? 5.0; // Usar 5.0 si no se encuentra
    } else {
        $datos['encabezado']['nota_minima'] = 5.0; // Valor por defecto si falla encabezado
    }


    // Consulta de asignaturas (corregida)
    $sqlAsignaturas = "SELECT DISTINCT asig.codigo, asig.nombre, asig.ordenar FROM asignatura asig INNER JOIN nota n ON asig.codigo = n.codigo_asignatura INNER JOIN alumno_matricula am ON n.codigo_matricula = am.id_alumno_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all ORDER BY asig.ordenar";
    $stmtAsig = $pdo->prepare($sqlAsignaturas); $stmtAsig->bindParam(':codigo_all', $codigoAll); $stmtAsig->execute();
    if($stmtAsig->rowCount() > 0) {
        $datos['asignaturas'] = array_map(function($item) { unset($item['ordenar']); return $item; }, $stmtAsig->fetchAll(PDO::FETCH_ASSOC));
    } else { $datos['asignaturas'] = []; }

    // Consulta principal para notas (añadir codigo_nie)
    if (!in_array($campoNota, ['nota_p_p_1', 'nota_p_p_2', 'nota_p_p_3', 'nota_final'])) { throw new Exception("..."); }
    $sqlNotas = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as nombre_completo, n.codigo_asignatura, n.$campoNota as nota_periodo FROM alumno a INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' INNER JOIN nota n ON am.id_alumno_matricula = n.codigo_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all ORDER BY nombre_completo, n.codigo_asignatura";
    $stmtNotas = $pdo->prepare($sqlNotas); $stmtNotas->bindParam(':codigo_all', $codigoAll); $stmtNotas->execute();
    $datos['notas'] = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfPorTrimestre(array $datos, string $nombrePeriodo) {
    if (empty($datos['asignaturas'])) { throw new Exception("..."); }

    $notaMinima = floatval($datos['encabezado']['nota_minima']); // Usar nota mínima dinámica

    // --- PROCESAR Y PIVOTAR DATOS ---
    $alumnosNotas = [];
    foreach ($datos['notas'] as $nota) {
        $idAlumno = $nota['id_alumno'];
        if (!isset($alumnosNotas[$idAlumno])) {
            $alumnosNotas[$idAlumno]['nombre'] = $nota['nombre_completo'];
            $alumnosNotas[$idAlumno]['nie'] = $nota['codigo_nie']; // Guardar NIE
            $alumnosNotas[$idAlumno]['notas'] = [];
            foreach ($datos['asignaturas'] as $asig) { $alumnosNotas[$idAlumno]['notas'][$asig['codigo']] = ''; }
        }
        if(isset($alumnosNotas[$idAlumno]['notas'][$nota['codigo_asignatura']])) {
            $alumnosNotas[$idAlumno]['notas'][$nota['codigo_asignatura']] = floatval($nota['nota_periodo']);
        }
    }

    // --- GENERAR PDF ---
    $pdf = new PDF_NotasTrimestre('P', 'mm', 'Letter');
    $pdf->SetMargins(10, 15, 10); $pdf->SetAutoPageBreak(true, 15); $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];
    $pdf->asignaturasHeader = $datos['asignaturas'];
    $pdf->nombrePeriodo = $nombrePeriodo;
    $pdf->notaMinima = $notaMinima; // Pasar nota mínima a la clase PDF si se necesita allí

    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8); $fill = false; $numFila = 0;
    $numAsignaturas = count($datos['asignaturas']);
    $anchoAsignatura = 92 / $numAsignaturas;

    $sumatorias = array_fill_keys(array_column($datos['asignaturas'], 'codigo'), 0);
    $numAlumnosValidos = 0;
    $alumnosPuntajes = []; // Array para guardar puntajes para el Top 5

    foreach ($alumnosNotas as $idAlumno => $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_TRIMESTRE == 0) { $pdf->AddPage(); $pdf->TablaEncabezado(); $pdf->SetFont('Arial', '', 8); }
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);

        $pdf->Cell(8, 6, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(15, 6, $alumno['nie'], 1, 0, 'C', true); // Columna NIE
        $pdf->Cell(65, 6, convertirtexto($alumno['nombre']), 1, 0, 'L', true); // Ancho ajustado

        $totalPuntosAlumno = 0;
        $tieneNotas = false;
        foreach ($datos['asignaturas'] as $asig) {
            $nota = $alumno['notas'][$asig['codigo']];
            $notaVal = ($nota !== '') ? floatval($nota) : null;

            if($notaVal !== null && $notaVal >= 0) { // Considerar nota 0 como válida
                 if($notaVal < $notaMinima && $notaVal > 0) { $pdf->SetTextColor(255, 0, 0); } // Colorear solo si > 0 y < minima
                 $pdf->Cell($anchoAsignatura, 6, number_format($notaVal, 2), 1, 0, 'C', true);
                 $pdf->SetTextColor(0);
                 if ($notaVal > 0) { // Solo sumar puntos si la nota es mayor a 0
                     $totalPuntosAlumno += $notaVal;
                     if(isset($sumatorias[$asig['codigo']])) { $sumatorias[$asig['codigo']] += $notaVal; }
                     $tieneNotas = true;
                 }
            } else {
                 $pdf->Cell($anchoAsignatura, 6, '', 1, 0, 'C', true);
            }
        }
        
        if ($tieneNotas) $numAlumnosValidos++;

        $pdf->Cell(10, 6, ($totalPuntosAlumno > 0) ? number_format($totalPuntosAlumno, 2) : '0.00', 1, 1, 'C', true); // Mostrar 0.00 si no hay notas

        // Guardar datos para el Top 5
        $alumnosPuntajes[] = [
            'id' => $idAlumno,
            'nie' => $alumno['nie'],
            'nombre' => $alumno['nombre'],
            'puntaje' => $totalPuntosAlumno
        ];

        $fill = !$fill; $numFila++;
    }

    // Filas de TOTAL y PROMEDIO
    $pdf->SetFont('Arial','B', 8);
    $pdf->Cell(88, 6, 'TOTAL DE PUNTOS', 1, 0, 'R', true); // 8+15+65
    foreach($datos['asignaturas'] as $asig) { $suma = $sumatorias[$asig['codigo']] ?? 0; $pdf->Cell($anchoAsignatura, 6, ($suma > 0) ? number_format($suma, 2) : '', 1, 0, 'C', true); }
    $pdf->Cell(10, 6, '', 1, 1, 'C', true);
    $pdf->Cell(88, 6, 'PROMEDIO', 1, 0, 'R', true);
    foreach($datos['asignaturas'] as $asig) { $suma = $sumatorias[$asig['codigo']] ?? 0; $promedio = ($numAlumnosValidos > 0 && $suma > 0) ? round($suma / $numAlumnosValidos) : ''; $pdf->Cell($anchoAsignatura, 6, $promedio, 1, 0, 'C', true); }
    $pdf->Cell(10, 6, '', 1, 1, 'C', true);

    // --- TABLA TOP 5 (SOLO SI ES NOTA FINAL) ---
    if ($nombrePeriodo == 'Nota Final') {
        // Ordenar alumnos por puntaje descendente
        usort($alumnosPuntajes, function($a, $b) {
            return $b['puntaje'] <=> $a['puntaje']; // <=> para PHP 7+
        });

        // Tomar los primeros 5
        $top5 = array_slice($alumnosPuntajes, 0, 5);

        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 7, 'Alumnos con Mayor Puntaje (Nota Final)', 0, 1, 'C');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(220, 220, 220);
        $pdf->Cell(10, 7, 'N#', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'NIE', 1, 0, 'C', true); // Columna NIE
        $pdf->Cell(100, 7, 'Nombre del Alumno', 1, 0, 'C', true);
        $pdf->Cell(20, 7, 'Puntaje', 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 8);
        $fillTop = false;
        foreach ($top5 as $index => $topAlumno) {
            $pdf->SetFillColor($fillTop ? 240 : 255, $fillTop ? 240 : 255, $fillTop ? 240 : 255);
            $pdf->Cell(10, 6, $index + 1, 1, 0, 'C', true);
            $pdf->Cell(20, 6, $topAlumno['nie'], 1, 0, 'C', true); // Mostrar NIE
            $pdf->Cell(100, 6, convertirtexto($topAlumno['nombre']), 1, 0, 'L', true);
            $pdf->Cell(20, 6, number_format($topAlumno['puntaje'], 2), 1, 1, 'C', true);
            $fillTop = !$fillTop;
        }
    }

    $pdf->Output('Notas_por_Trimestre.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    // ... (Validación de conexión y parámetros sin cambios) ...
    if ($errorDbConexion) { throw new Exception("..."); }
    $codigo_all = $_GET["todos"] ?? null; $periodoSeleccionado = $_GET["lsttrimestres"] ?? 'nota_p_p_1';
    $mapPeriodos = ['nota_p_p_1' => ['columna' => 'nota_p_p_1', 'nombre' => 'Trimestre 1'], 'nota_p_p_2' => ['columna' => 'nota_p_p_2', 'nombre' => 'Trimestre 2'], 'nota_p_p_3' => ['columna' => 'nota_p_p_3', 'nombre' => 'Trimestre 3'], 'nota_final' => ['columna' => 'nota_final', 'nombre' => 'Nota Final']];
    if (!$codigo_all || !isset($mapPeriodos[$periodoSeleccionado])) { throw new Exception("..."); }
    $campoNotaDB = $mapPeriodos[$periodoSeleccionado]['columna']; $nombrePeriodoLegible = $mapPeriodos[$periodoSeleccionado]['nombre'];

    $datosReporte = obtenerDatosPorTrimestre($dblink, $codigo_all, $campoNotaDB);

    if (empty($datosReporte['notas'])) { echo "No se encontraron notas para este grupo y período."; exit; }
    if (empty($datosReporte['asignaturas'])) { echo "No se encontraron asignaturas configuradas para este grupo."; exit; }

    generarPdfPorTrimestre($datosReporte, $nombrePeriodoLegible);

} catch (PDOException $e) { /* ... */ } catch (Exception $e) { /* ... */ }
?>