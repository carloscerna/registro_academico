<?php
// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluir la librería FPDF
require_once $_SERVER['DOCUMENT_ROOT'] . "/registro_academico/php_libs/fpdf/fpdf.php";
include($path_root . "/registro_academico/includes/funciones.php");
// Incluir el archivo de conexión a la base de datos
include($_SERVER['DOCUMENT_ROOT'] . "/registro_academico/includes/mainFunctions_conexion.php");
// cambiar a utf-8.
header("Content-Type: text/html; charset=UTF-8");

$pdo = $dblink;

// --- OBTENER DATOS DEL GET ---
// Asegúrate de sanitizar y validar las entradas ($_GET)
$codigo_annlectivo = $_GET['annlectivo'];
$nombre_annlectivo = $_GET['nombre_annlectivo'] ?? 'Desconocido';
$calificacion_minima = $_GET['calificacionMinima'] ?? 6; // Valor por defecto 6

// --- CLASE PDF PERSONALIZADA PARA REPETIR ENCABEZADOS ---
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
        if (isset($_SESSION['logo_uno'])) {
            $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno'];
            $this->Image($img, 10, 8, 20);
        }

        // Títulos
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(30, 10);
        $this->Cell(0, 6, convertirtexto($this->nombre_institucion), 0, 1, 'L');
        $this->SetX(30);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 6, convertirtexto('Reporte General de Recuperaciones y Notas Finales'), 0, 1, 'L');
        $this->SetX(30);
        $this->Cell(0, 6, convertirtexto("Año Lectivo: " . $this->nombre_annlectivo), 0, 1, 'L');
        $this->Ln(10);

        // Encabezados de la tabla
        $this->SetFont('Arial', 'B', 7);
        $this->SetFillColor(200, 220, 240); // Azul suave
        $this->Cell($this->cellWidths['num'], 6, convertirTexto('N°'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nie'], 6, convertirTexto('NIE'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['nombre'], 6, 'Nombre del Estudiante', 1, 0, 'C', true);
        $this->Cell($this->cellWidths['modalidad'], 6, 'Modalidad', 1, 0, 'C', true);
        $this->Cell($this->cellWidths['grado'], 6, 'Grado', 1, 0, 'C', true);
        $this->Cell($this->cellWidths['seccion'], 6, convertirTexto('Sección'), 1, 0, 'C', true);
        $this->Cell($this->cellWidths['asig'], 6, 'Asignatura', 1, 0, 'C', true);
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
        $this->Cell(0, 10, convertirTexto('Página ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    // Función para dibujar celdas de notas con colores
    function CellNota($width, $height, $text, $calificacion_minima)
    {
        $val = floatval($text);
        $display = ($val == 0 && $text !== "0") ? "" : $val;

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
// --- FIN DE LA CLASE PDF ---


// --- CONSULTA SQL PRINCIPAL ---
// Esta consulta busca en TODO el año lectivo, TODOS los grados y secciones.
$sql = "
    SELECT
        a.codigo_nie,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as nombre_estudiante,
        asig.nombre AS nombre_asignatura,
        n.recuperacion,
        n.nota_recuperacion_2,
        n.nota_final,
        cm.nombre AS nombre_modalidad,
        cg.nombre AS nombre_grado,
        cs.nombre AS nombre_seccion
    FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
    
    -- === INICIO DE LA CORRECCIÓN ===
    -- Se cambió 'catalogo_modalidad' por 'catalogo_bach_o_ciclo'
    INNER JOIN bachillerato_ciclo cm ON cm.codigo = am.codigo_bach_o_ciclo
    -- === FIN DE LA CORRECCIÓN ===
    
    INNER JOIN grado_ano cg ON cg.codigo = am.codigo_grado
    INNER JOIN seccion cs ON cs.codigo = am.codigo_seccion
    WHERE am.codigo_ann_lectivo = :codigo_annlectivo
      -- El filtro clave: mostrar solo si tiene nota en R1, R2 o NF
      AND (n.recuperacion > 0 OR n.nota_recuperacion_2 > 0)
    ORDER BY
        cm.nombre, cg.nombre, cs.nombre, a.apellido_paterno, a.apellido_materno, a.nombre_completo, asig.nombre
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':codigo_annlectivo' => $codigo_annlectivo]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- CONFIGURACIÓN DEL DOCUMENTO PDF ---
$pdf = new PDF('L', 'mm', 'Letter'); // Landscape, mm, Letter
$pdf->AliasNbPages();

// Definir anchos de columnas
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

// Pasar datos para el encabezado
$pdf->SetReportHeaderData($_SESSION['institucion'], $nombre_annlectivo);

$pdf->AddPage();
$pdf->SetFont('Arial', '', 7);
$pdf->SetMargins(10, 10, 10);

// --- DIBUJAR LOS DATOS ---
$i = 1;
foreach ($data as $row) {
    // --- ¡¡INICIO DE LA CORRECCIÓN!! ---
    // Reseteamos el color del texto a negro al inicio de CADA fila.
    $pdf->SetTextColor(0, 0, 0); 
    // --- FIN DE LA CORRECCIÓN ---
    // FPDF maneja el salto de página y la redibujado del Header() automáticamente
    $pdf->Cell($widths['num'], 6, $i, 1, 0, 'C');
    $pdf->Cell($widths['nie'], 6, $row['codigo_nie'], 1, 0, 'C');
    $pdf->Cell($widths['nombre'], 6, convertirtexto($row['nombre_estudiante']), 1, 0, 'L');
    $pdf->Cell($widths['modalidad'], 6, convertirtexto($row['nombre_modalidad']), 1, 0, 'L');
    $pdf->Cell($widths['grado'], 6, convertirtexto($row['nombre_grado']), 1, 0, 'L');
    $pdf->Cell($widths['seccion'], 6, convertirtexto($row['nombre_seccion']), 1, 0, 'C');
    $pdf->Cell($widths['asig'], 6, convertirtexto($row['nombre_asignatura']), 1, 0, 'L');
    
    // Usar la función personalizada para colorear notas
    $pdf->CellNota($widths['nota'], 6, $row['recuperacion'], $calificacion_minima);
    $pdf->CellNota($widths['nota'], 6, $row['nota_recuperacion_2'], $calificacion_minima);
    $pdf->CellNota($widths['nota'], 6, $row['nota_final'], $calificacion_minima);
    
    $pdf->Ln();
    $i++;
}

// --- FINALIZAR PDF ---
$pdf->Output("I", "reporte_recuperaciones.pdf");
?>