<?php
// 1. LIMPIEZA DE BÚFER Y SILENCIO (VITAL PARA PDFS)
ob_start(); 
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

// 2. FUNCIÓN DE COMPATIBILIDAD LOCAL (Seguro contra fallos de utf8)
if (!function_exists('utf8_decode_fix')) {
    function utf8_decode_fix($texto) {
        if (is_null($texto)) return '';
        return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
    }
}

// Wrapper para convertirTexto si no existe o falla
if (!function_exists('convertirTextoSafe')) {
    function convertirTextoSafe($texto) {
        if (function_exists('convertirtexto')) {
            return convertirtexto($texto); // Usa la global si existe
        }
        return utf8_decode_fix($texto); // Usa la local si no
    }
}

// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluir la librería FPDF
require_once $_SERVER['DOCUMENT_ROOT'] . "/registro_academico/php_libs/fpdf/fpdf.php";
include($path_root . "/registro_academico/includes/funciones.php");
include($_SERVER['DOCUMENT_ROOT'] . "/registro_academico/includes/mainFunctions_conexion.php");

// cambiar a utf-8.
header("Content-Type: text/html; charset=UTF-8");

$pdo = $dblink;

// --- OBTENER DATOS DEL GET CON PROTECCIÓN ---
// Si no viene el parámetro, evitamos que truene, pero la consulta vendrá vacía.
$codigo_annlectivo = $_REQUEST['annlectivo'] ?? ''; 
$nombre_annlectivo = $_REQUEST['nombre_annlectivo'] ?? 'Año Lectivo';
$calificacion_minima = $_REQUEST['calificacionMinima'] ?? 6;

// --- CLASE PDF ---
class PDF extends FPDF
{
    protected $nombre_institucion;
    protected $nombre_annlectivo;
    protected $cellWidths;
    protected $softGray;

    function SetReportHeaderData($institucion, $annlectivo)
    {
        $this->nombre_institucion = $institucion;
        $this->nombre_annlectivo = $annlectivo;
    }

    function SetColumnWidths($widths)
    {
        $this->cellWidths = $widths;
    }

    function SetColors($colors)
    {
        $this->softGray = $colors['softGray'];
    }

    // Cabecera de página
    function Header()
    {
        // Logo
        if (isset($_SESSION['logo_uno']) && file_exists($_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno'])) {
            $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno'];
            $this->Image($img, 10, 8, 20);
        }

        // Títulos
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(30, 10);
        $this->Cell(0, 6, utf8_decode_fix($this->nombre_institucion), 0, 1, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, utf8_decode_fix('Reporte General de Recuperaciones y Notas Finales'), 0, 1, 'L');
        $this->SetX(30);
        $this->Cell(0, 6, utf8_decode_fix("Año Lectivo: " . $this->nombre_annlectivo), 0, 1, 'L');
        $this->Ln(10);

        // Encabezados de la tabla
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(200, 220, 240); // Azul suave
        $this->Cell($this->cellWidths['num'], 6, utf8_decode_fix('N°'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nie'], 6, utf8_decode_fix('NIE'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nombre'], 6, utf8_decode_fix('Nombre del Estudiante'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['modalidad'], 6, utf8_decode_fix('Modalidad'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['grado'], 6, utf8_decode_fix('Grado'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['seccion'], 6, utf8_decode_fix('Sección'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['asig'], 6, utf8_decode_fix('Asignatura'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nota'], 6, 'R1', 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nota'], 6, 'R2', 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nota'], 6, 'NF', 1, 0, 'C', true);
        $this->Ln();
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode_fix('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Función para dibujar celdas de notas con colores
    function CellNota($width, $height, $text, $calificacion_minima)
    {
        $val = floatval($text);
        // Fix: asegurar que mostramos 0 si es un cero real numérico
        $display = ($val == 0 && $text !== "0" && $text !== 0) ? "" : $val;

        if ($display !== "" && $val < $calificacion_minima) {
            $this->SetTextColor(255, 0, 0); // Rojo
            $this->SetFillColor(...$this->softGray); // Gris suave
            $this->Cell($width, $height, $display, 1, 0, 'C', true);
        } else {
            $this->SetTextColor(0, 0, 0); // Negro
            $this->SetFillColor(255, 255, 255); // Blanco
            $this->Cell($width, $height, $display, 1, 0, 'C', false);
        }
    }
}

// --- FUNCIÓN DE CÁLCULO ---
function calcularNotaFinalCalculada($r1, $r2) {
    $rec_1 = floatval($r1);
    $rec_2 = floatval($r2);

    $nota_calculada = 0;

    if ($rec_2 <= 0) {
        $nota_calculada = $rec_1;
    } else {
        $nota_calculada = ($rec_1 + $rec_2) / 2;
    }

    return round($nota_calculada, 0);
}

// --- CONSULTA SQL PRINCIPAL ---
// Nota: Se agregan COALESCE y casting seguro para evitar errores de nulos en PostgreSQL
$sql = "
    SELECT
        a.codigo_nie,
        TRIM(a.apellido_paterno) || ' ' || TRIM(a.apellido_materno) || ', ' || TRIM(a.nombre_completo) as nombre_estudiante,
        asig.nombre AS nombre_asignatura,
        n.recuperacion,
        n.nota_recuperacion_2,
        n.nota_final,
        cm.nombre AS nombre_modalidad,
        cg.nombre AS nombre_grado,
        cs.nombre AS nombre_seccion,
        cp.calificacion_minima AS calificacion_minima_mod
    FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
    INNER JOIN catalogo_periodos cp ON cp.codigo_modalidad = am.codigo_bach_o_ciclo
    INNER JOIN bachillerato_ciclo cm ON cm.codigo = am.codigo_bach_o_ciclo
    INNER JOIN grado_ano cg ON cg.codigo = am.codigo_grado
    INNER JOIN seccion cs ON cs.codigo = am.codigo_seccion
    WHERE am.codigo_ann_lectivo = :codigo_annlectivo
      AND (n.recuperacion > 0 OR n.nota_recuperacion_2 > 0)
    ORDER BY
        cm.nombre, cg.nombre, cs.nombre, a.apellido_paterno, a.apellido_materno, a.nombre_completo, asig.nombre
";

// Si no hay código, no ejecutamos para evitar error fatal, o manejamos el error.
if(!empty($codigo_annlectivo)){
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':codigo_annlectivo' => $codigo_annlectivo]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error en Consulta: " . $e->getMessage());
    }
} else {
    $data = []; // Array vacío si no hay parámetro
}

// --- CONFIGURACIÓN DEL DOCUMENTO PDF ---
$pdf = new PDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();

$widths = [
    'num' => 8,
    'nie' => 15,
    'nombre' => 60,
    'modalidad' => 65,
    'grado' => 15,
    'seccion' => 15,
    'asig' => 40,
    'nota' => 10
];
$pdf->SetColumnWidths($widths);
$pdf->SetColors(['softGray' => [230, 230, 230]]);

// Pasar datos para el encabezado (Manejo de sesión vacía)
$inst = $_SESSION['institucion'] ?? 'Nombre Institución';
$pdf->SetReportHeaderData($inst, $nombre_annlectivo);

$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);
$pdf->SetMargins(10, 10, 10);

// --- DIBUJAR LOS DATOS ---
$i = 1;

if (count($data) > 0) {
    foreach ($data as $row) {
        $pdf->SetTextColor(0, 0, 0); 
        
        // Protección contra nulos en calificacion
        $calif_min_dinamica = floatval($row['calificacion_minima_mod'] ?? 6);
        
        $pdf->Cell($widths['num'], 6, $i, 1, 0, 'C');
        $pdf->Cell($widths['nie'], 6, trim($row['codigo_nie'] ?? ''), 1, 0, 'C');
        
        // Uso de utf8_decode_fix en lugar de convertirtexto directo para evitar errores
        $pdf->Cell($widths['nombre'], 6, utf8_decode_fix($row['nombre_estudiante'] ?? ''), 1, 0, 'L');
        $pdf->Cell($widths['modalidad'], 6, utf8_decode_fix($row['nombre_modalidad'] ?? ''), 1, 0, 'L');
        $pdf->Cell($widths['grado'], 6, utf8_decode_fix($row['nombre_grado'] ?? ''), 1, 0, 'L');
        $pdf->Cell($widths['seccion'], 6, utf8_decode_fix($row['nombre_seccion'] ?? ''), 1, 0, 'C');
        $pdf->Cell($widths['asig'], 6, utf8_decode_fix($row['nombre_asignatura'] ?? ''), 1, 0, 'L');
        
        $r1 = $row['recuperacion'] ?? 0;
        $r2 = $row['nota_recuperacion_2'] ?? 0;
        
        $nueva_nota_final = calcularNotaFinalCalculada($r1, $r2);
        
        $pdf->CellNota($widths['nota'], 6, $r1, $calif_min_dinamica);
        $pdf->CellNota($widths['nota'], 6, $r2, $calif_min_dinamica);
        $pdf->CellNota($widths['nota'], 6, $nueva_nota_final, $calif_min_dinamica);
        
        $pdf->Ln();
        $i++;
    }
} else {
    // MENSAJE SI NO HAY DATOS
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, utf8_decode_fix('No se encontraron registros de recuperación para este Año Lectivo.'), 0, 1, 'C');
}

// 4. LIMPIEZA FINAL Y SALIDA
ob_end_clean(); // Borramos cualquier basura que PHP haya soltado antes
$pdf->Output("I", "reporte_recuperaciones.pdf");
?>