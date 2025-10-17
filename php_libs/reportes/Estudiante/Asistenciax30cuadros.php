<?php
// <-- VERSIÓN REFACTORIZADA Y SEGURA: Asistenciax30cuadros.php -->

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
 * Clase FPDF personalizada para el reporte de Asistencia x 30.
 */
class PDF_Asistencia30 extends FPDF {
    private $datosEncabezado = [];

    public function setDatosEncabezado(array $datos) {
        $this->datosEncabezado = $datos;
    }

    function Header() {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 20, 15, 12, 15);
        }

        $this->SetFont('Arial', 'B', 13);
        $this->Text(35, 15, convertirtexto($_SESSION['institucion']));
        $this->Text(35, 20, convertirtexto('_______________________________________________________________'));
        
        $this->SetFont('Arial', '', 9);
        $this->Text(35, 26, 'Modalidad: ' . convertirtexto($this->datosEncabezado['bachillerato']));
        $this->Text(35, 33, 'Nombre Asignatura: _________________________________________');
        $this->Text(162, 33, 'Nombre Docente: _________________________________________');

        $this->RoundedRect(230, 11, 35, 15, 3.5, '');
        $this->Text(232, 16, 'Grado: ' . convertirtexto($this->datosEncabezado['grado']));
        $this->Text(232, 20, 'Seccion: ' . convertirtexto($this->datosEncabezado['seccion']));
        $this->Text(232, 24, 'Ano Lectivo: ' . convertirtexto($this->datosEncabezado['ann_lectivo']));
        
        $this->SetY(40);
    }

// ### FUNCIÓN FOOTER CORREGIDA ###
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);

        // Array de meses en español
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        
        // Construir la fecha en el formato deseado
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $fechaFormateada = "Santa Ana, $dia de $mes de $anio";

        // Construir la cadena completa del pie de página
        $textoFooter = "$fechaFormateada, Pagina " . $this->PageNo() . ' de {nb}';
        
        // Imprimir la celda, aplicando convertirtexto() para manejar tildes
        $this->Cell(0, 10, convertirtexto($textoFooter), 0, 0, 'C');
    }
    // #################################

    function TablaEncabezado() {
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.2);
        $this->SetFont('Arial', 'B', 8);

        $this->Cell(8, 10, 'N', 1, 0, 'C', true);
        $this->Cell(16, 10, 'NIE', 1, 0, 'C', true);
        $y_inicial = $this->GetY();
        $x_pos = $this->GetX();
        $this->MultiCell(66, 5, "Nombre de Alumnos/as\n(Orden Alfabético por Apellido)", 1, 'C', true);
        $this->SetXY($x_pos + 66, $y_inicial);

        // Ancho disponible: 279 (Letter L) - 30 (márgenes) - 8 - 16 - 66 = 159mm
        $anchoDia = 159 / NUMERO_CUADROS;
        
        for ($i = 0; $i < NUMERO_CUADROS; $i++) {
            $this->Cell($anchoDia, 10, '', 1, 0, 'C', true);
        }
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
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();
    $pdf->setDatosEncabezado($datos['encabezado']);
    
    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8);
    $fill = false;
    $numFila = 0;
    $anchoDia = 159 / NUMERO_CUADROS;

    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_30 == 0) {
            $pdf->AddPage();
            $pdf->TablaEncabezado();
            $pdf->SetFont('Arial', '', 8);
        }
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        
        $pdf->Cell(8, 6, $numFila + 1, 'LR', 0, 'C', $fill);
        $pdf->Cell(16, 6, trim($alumno['codigo_nie']), 'LR', 0, 'C', $fill);
        $pdf->Cell(66, 6, convertirtexto(trim($alumno['apellido_alumno'])), 'LR', 0, 'L', $fill);
        
        for ($i = 0; $i < NUMERO_CUADROS; $i++) {
            $pdf->Cell($anchoDia, 6, '', 1, 0, 'C', $fill);
        }
        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    // Rellenar filas vacías
    $filasEnPagina = $numFila % FILAS_POR_PAGINA_30;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA_30;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA_30 : FILAS_POR_PAGINA_30 - $filasEnPagina;
    
    for ($i = 0; $i < $filasFaltantes; $i++) {
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(8, 6, $numFila + 1, 'LRB', 0, 'C', $fill);
        $pdf->Cell(16, 6, '', 'LRB', 0, 'C', $fill);
        $pdf->Cell(66, 6, '', 'LRB', 0, 'L', $fill);
        for ($j = 0; $j < NUMERO_CUADROS; $j++) {
            $pdf->Cell($anchoDia, 6, '', 1, 0, 'C', $fill);
        }
        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    $pdf->Output('Asistencia_30_Cuadros.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }

    $codigo_all = $_GET["todos"] ?? null;
    if (!$codigo_all) { throw new Exception("Faltan parámetros para generar el reporte."); }

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