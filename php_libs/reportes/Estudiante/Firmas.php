<?php
// <-- VERSIÓN FINAL CON TÍTULO DINÁMICO -->

ini_set('display_errors', 1);
error_reporting(E_ALL);

// ▼▼▼ LÍNEA NUEVA Y CRÍTICA ▼▼▼
date_default_timezone_set('America/El_Salvador');

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_FIRMAS', 30);

/**
 * Clase FPDF personalizada para el reporte de Firmas.
 */
class PDF_Firmas extends FPDF {
    public $datosEncabezado = [];
    public $tituloPersonalizado = 'Nómina de Alumnos/as para Firmas'; // Título por defecto

    function Header() {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 15);
        }

        $this->SetFont('Arial', 'B', 12);
        $this->Text(28, 12, convertirtexto($_SESSION['institucion']));
        
        // ▼▼▼ USAR EL TÍTULO PERSONALIZADO ▼▼▼
        $this->Text(28, 18, convertirtexto($this->tituloPersonalizado));
        
        // Información del grado
        $this->SetFont('Arial', '', 9);
        $this->SetXY(10, 25);
        $this->Cell(80, 5, 'Modalidad: ' . convertirtexto($this->datosEncabezado['bachillerato']), 0, 0, 'L');
        $this->Cell(40, 5, 'Grado: ' . convertirtexto($this->datosEncabezado['grado']), 0, 0, 'L');
        $this->Cell(30, 5, 'Seccion: ' . convertirtexto($this->datosEncabezado['seccion']), 0, 0, 'L');
        $this->Cell(0, 5, 'Ano Lectivo: ' . convertirtexto($this->datosEncabezado['ann_lectivo']), 0, 1, 'L');
        
        $this->SetY(40);
    }

    // ... (El resto de la clase PDF no cambia) ...
// ### FUNCIÓN FOOTER MEJORADA CON HORA LOCAL ###
function Footer() {
    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 8);

    // Array de meses en español
    $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    
    // 1. Construir la fecha
    $dia = date('d');
    $mes = $meses[date('n') - 1];
    $anio = date('Y');
    $fechaFormateada = "Santa Ana, $dia de $mes de $anio";

    // 2. Obtener la hora local formateada
    //    g: Hora en formato 12h sin ceros iniciales (1-12)
    //    i: Minutos con ceros iniciales (00-59)
    //    a: "am" o "pm" en minúsculas
    $horaFormateada = date('g:i a');

    // 3. Construir la cadena completa del pie de página
    $textoFooter = "$fechaFormateada - $horaFormateada | Pagina " . $this->PageNo() . ' de {nb}';
    
    // Imprimir la celda, aplicando convertirtexto() para manejar tildes
    $this->Cell(0, 10, convertirtexto($textoFooter), 0, 0, 'C');
}
    // #################################
    function TablaEncabezado() {
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(0);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 7, 'N', 1, 0, 'C', true);
        $this->Cell(20, 7, 'NIE', 1, 0, 'C', true);
        $this->Cell(80, 7, 'Nombre de Alumnos/as', 1, 0, 'C', true);
        $this->Cell(80, 7, 'FIRMA', 1, 0, 'C', true);
        $this->Ln();
    }
}

/**
 * Obtiene los datos (sin cambios en esta función).
 */
function obtenerDatosFirmas(PDO $pdo, string $codigoAll): array {
    // ... (Esta función no necesita cambios) ...
    $datos = ['encabezado' => [], 'alumnos' => []];
    $sqlEncabezado = "SELECT btrim(bach.nombre) as bachillerato, btrim(gan.nombre) as grado, btrim(sec.nombre) as seccion, ann.nombre as ann_lectivo FROM alumno_matricula am INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ? LIMIT 1";
    $stmt = $pdo->prepare($sqlEncabezado); $stmt->execute([$codigoAll]); $encabezadoData = $stmt->fetch(PDO::FETCH_ASSOC); if($encabezadoData){ $datos['encabezado'] = $encabezadoData; }
    $sqlAlumnos = "SELECT a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno FROM alumno a INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ? ORDER BY apellido_alumno ASC";
    $stmt = $pdo->prepare($sqlAlumnos); $stmt->execute([$codigoAll]); $datos['alumnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfFirmas(array $datos, ?string $titulo) {
    $pdf = new PDF_Firmas('P', 'mm', 'Letter');
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];

    // ▼▼▼ ASIGNAR EL TÍTULO SI EXISTE ▼▼▼
    if (!empty($titulo)) {
        $pdf->tituloPersonalizado = $titulo;
    }
    
    $pdf->AddPage();
    $pdf->TablaEncabezado();

    // ... (El resto de la función para generar las filas no cambia) ...
    $pdf->SetFont('Arial', '', 9);
    $fill = false; $numFila = 0;
    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_FIRMAS == 0) { $pdf->AddPage(); $pdf->TablaEncabezado(); $pdf->SetFont('Arial', '', 9); }
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(10, 7, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(20, 7, $alumno['codigo_nie'], 1, 0, 'C', true);
        $pdf->Cell(80, 7, convertirtexto($alumno['apellido_alumno']), 1, 0, 'L', true);
        $pdf->Cell(80, 7, '', 1, 0, 'C', true);
        $pdf->Ln(); $fill = !$fill; $numFila++;
    }
    $filasEnPagina = $numFila % FILAS_POR_PAGINA_FIRMAS; if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA_FIRMAS;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA_FIRMAS : FILAS_POR_PAGINA_FIRMAS - $filasEnPagina;
    for ($i = 0; $i < $filasFaltantes; $i++) {
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(10, 7, $numFila + 1, 'LRB', 0, 'C', $fill);
        $pdf->Cell(20, 7, '', 'LRB', 0, 'C', $fill);
        $pdf->Cell(80, 7, '', 'LRB', 0, 'L', $fill);
        $pdf->Cell(80, 7, '', 'LRB', 0, 'C', $fill);
        $pdf->Ln(); $fill = !$fill; $numFila++;
    }

    $pdf->Output('Listado_Firmas.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }

    $codigo_all = $_GET["todos"] ?? null;
    // ▼▼▼ CAPTURAR EL NUEVO PARÁMETRO DEL TÍTULO ▼▼▼
    $titulo_firmas = $_GET["tituloFirmas"] ?? null;
    
    if (!$codigo_all) { throw new Exception("Faltan parámetros para generar el reporte."); }

    $datosReporte = obtenerDatosFirmas($dblink, $codigo_all);

    if (empty($datosReporte['alumnos'])) {
        echo "No se encontraron alumnos para este grupo. Verifique los filtros.";
        exit;
    }

    // ▼▼▼ PASAR EL TÍTULO A LA FUNCIÓN DE GENERACIÓN ▼▼▼
    generarPdfFirmas($datosReporte, $titulo_firmas);

} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>