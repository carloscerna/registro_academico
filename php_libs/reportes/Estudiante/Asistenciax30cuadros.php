<?php
// <-- VERSIÓN REFACTORIZADA: Control de Punteo (30 Cuadros) -->

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_30', 25);
define('NUMERO_CUADROS', 30);

/**
 * Clase FPDF personalizada para el reporte de Control de Punteo / Asistencia x 30.
 */
class PDF_Asistencia30 extends FPDF {
    private $datosEncabezado = [];

    public function setDatosEncabezado(array $datos) {
        $this->datosEncabezado = $datos;
    }

    function Header() {
        // Logo Institucional
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 18, 8, 9);
        }

        // Títulos Principales
        $this->SetXY(30, 8);
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(31, 78, 120);
        $this->Cell(180, 5, convertirtexto($_SESSION['institucion'] ?? 'INSTITUCIÓN EDUCATIVA'), 0, 1, 'L');

        $this->SetX(30);
        $this->SetFont('Arial', 'B', 9.5);
        $this->SetTextColor(80, 80, 80);
        $this->Cell(180, 4, convertirtexto('CONTROL DE PUNTEO Y ACTIVIDADES (30 REGISTROS)'), 0, 1, 'L');

        // Cuadro Lateral de Grado / Sección / Año (Derecha)
        $this->SetXY(215, 8);
        $this->SetFillColor(245, 247, 250);
        $this->SetDrawColor(200, 200, 200);
        $this->Rect(215, 8, 48, 22, 'DF');

        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(31, 78, 120);
        $this->Text(217, 13, convertirTexto('Grado: ' . $this->datosEncabezado['grado']));
        $this->Text(217, 18, convertirTexto('Sección: ' . $this->datosEncabezado['seccion']));
        $this->Text(217, 23, convertirTexto('Año Lectivo: ' . $this->datosEncabezado['ann_lectivo']));

        // Cuadros de Datos de la Asignatura / Docente (Izquierda)
        $this->SetY(18);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);

        $this->SetX(15);
        $this->Cell(195, 4.5, convertirTexto('Modalidad: ' . $this->datosEncabezado['bachillerato']), 1, 1, 'L');

        $this->SetX(15);
        $this->Cell(97.5, 4.5, 'Nombre Asignatura:', 1, 0, 'L');
        $this->Cell(97.5, 4.5, 'Nombre Docente:', 1, 1, 'L');

        $this->SetY(32);
    }

    function Footer() {
        // Zona Horaria Local de El Salvador
        date_default_timezone_set('America/El_Salvador');

        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->SetDrawColor(200, 200, 200);

        $this->Line(15, $this->GetY() - 2, 263, $this->GetY() - 2);

        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $hora = date('g:i A');

        $fechaFormateada = "Generado en Santa Ana, $dia de $mes de $anio a las $hora";

        $this->Cell(180, 6, convertirtexto($fechaFormateada), 0, 0, 'L');
        $this->Cell(68, 6, convertirtexto('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    function TablaEncabezado() {
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(.2);

        $this->SetFillColor(31, 78, 120);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);

        $y_inicial = $this->GetY();
        $this->Cell(8, 10, 'N°', 1, 0, 'C', true);
        $this->Cell(16, 10, 'NIE', 1, 0, 'C', true);

        $x_pos = $this->GetX();
        $this->MultiCell(66, 5, convertirTexto("Nombre de Alumnos/as\n(Orden Alfabético por Apellido)"), 1, 'C', true);
        $this->SetXY($x_pos + 66, $y_inicial);

        // Ancho total disponible: 279 (Carta Horizontal) - 30 (Márgenes L/R de 15) - 8 - 16 - 66 = 159 mm
        $anchoCuadro = 159 / NUMERO_CUADROS; // 5.3 mm por cuadro

        $this->SetFillColor(217, 226, 236);
        $this->SetTextColor(31, 78, 120);
        $this->SetFont('Arial', 'B', 7);

        // Filas numeradas de las 30 casillas de punteo
        for ($i = 1; $i <= NUMERO_CUADROS; $i++) {
            $this->Cell($anchoCuadro, 10, $i, 1, 0, 'C', true);
        }

        $this->SetTextColor(0, 0, 0);
        $this->Ln();
    }
}

/**
 * Obtiene los datos de encabezado y alumnos.
 */
function obtenerDatosAsistencia30(PDO $pdo, string $codigoAll): array {
    $datos = ['encabezado' => [], 'alumnos' => []];

    $sqlEncabezado = "SELECT btrim(bach.nombre) as nombre_bachillerato, btrim(gan.nombre) as nombre_grado, 
                      btrim(sec.nombre) as nombre_seccion, ann.nombre as nombre_ann_lectivo
                      FROM alumno_matricula am
                      INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                      INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                      INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                      INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                      WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ?
                      LIMIT 1";
    $stmt = $pdo->prepare($sqlEncabezado);
    $stmt->execute([$codigoAll]);
    $encabezado = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($encabezado) {
        $datos['encabezado']['bachillerato'] = $encabezado['nombre_bachillerato'];
        $datos['encabezado']['grado'] = $encabezado['nombre_grado'];
        $datos['encabezado']['seccion'] = $encabezado['nombre_seccion'];
        $datos['encabezado']['ann_lectivo'] = $encabezado['nombre_ann_lectivo'];
    }

    $sqlAlumnos = "SELECT a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno
                   FROM alumno a
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                   WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ?
                   ORDER BY apellido_alumno";
    $stmt = $pdo->prepare($sqlAlumnos);
    $stmt->execute([$codigoAll]);
    $datos['alumnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfAsistencia30(array $datos) {
    $pdf = new PDF_Asistencia30('L', 'mm', 'Letter');
    $pdf->SetMargins(15, 12, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();
    $pdf->setDatosEncabezado($datos['encabezado']);

    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8);
    $fill = false;
    $numFila = 0;
    $anchoCuadro = 159 / NUMERO_CUADROS;

    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_30 == 0) {
            $pdf->AddPage();
            $pdf->TablaEncabezado();
            $pdf->SetFont('Arial', '', 8);
            $fill = false;
        }

        // Color Intercalado Suave y Elegante
        if ($fill) {
            $pdf->SetFillColor(217, 226, 236);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(8, 6.2, $numFila + 1, 1, 0, 'C', true);

        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell(16, 6.2, trim($alumno['codigo_nie']), 1, 0, 'C', true);

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(66, 6.2, convertirtexto(trim($alumno['apellido_alumno'])), 1, 0, 'L', true);

        for ($i = 0; $i < NUMERO_CUADROS; $i++) {
            $pdf->Cell($anchoCuadro, 6.2, '', 1, 0, 'C', true);
        }

        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    // Rellenar filas vacías para mantener la estética uniforme
    $filasEnPagina = $numFila % FILAS_POR_PAGINA_30;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA_30;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA_30 : FILAS_POR_PAGINA_30 - $filasEnPagina;

    for ($i = 0; $i < $filasFaltantes; $i++) {
        if ($fill) {
            $pdf->SetFillColor(217, 226, 236);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(8, 6.2, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(16, 6.2, '', 1, 0, 'C', true);
        $pdf->Cell(66, 6.2, '', 1, 0, 'L', true);

        for ($j = 0; $j < NUMERO_CUADROS; $j++) {
            $pdf->Cell($anchoCuadro, 6.2, '', 1, 0, 'C', true);
        }

        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    $nombreArchivo = 'Control_Punteo_' . trim($datos['encabezado']['grado'] ?? '') . '_' . trim($datos['encabezado']['seccion'] ?? '') . '.pdf';
    $pdf->Output(convertirtexto($nombreArchivo), 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if (isset($errorDbConexion) && $errorDbConexion) { 
        throw new Exception("No se puede conectar a la base de datos."); 
    }

    $codigo_all = $_GET["todos"] ?? null;
    if (!$codigo_all) { 
        throw new Exception("Faltan parámetros para generar el reporte."); 
    }

    $datosReporte = obtenerDatosAsistencia30($dblink, $codigo_all);

    if (empty($datosReporte['alumnos'])) {
        echo "No se encontraron alumnos para este grupo. Verifique los filtros.";
        exit;
    }

    generarPdfAsistencia30($datosReporte);

} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>