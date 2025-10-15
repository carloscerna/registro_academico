<?php
// <-- VERSIÓN FINAL CON AJUSTES VISUALES COMPLETOS -->

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN INICIAL ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA', 25);

/**
 * Clase FPDF final, ajustada para replicar el diseño de la imagen.
 */
class PDF_Asistencia extends FPDF {
    private $datosEncabezado = [];
    private $diasDelMes = [];

    public function setDatosEncabezado(array $datos) {
        $this->datosEncabezado = $datos;
    }

    public function setDiasDelMes(array $dias) {
        $this->diasDelMes = $dias;
    }

    function Header() {
        // --- Encabezado replicando el diseño de la imagen ---
        $this->SetFont('Arial', 'B', 12);
        
        // Logo
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 15);
        }

        // Títulos
        $this->Text(28, 12, convertirtexto('COMPLEJO EDUCATIVO COLONIA RÍO ZARCO'));
        $this->SetFont('Arial', 'B', 11);
        $this->Text(28, 18, 'Lista de Asistencia - Mes: ' . strtoupper($this->datosEncabezado['nombre_mes']));
        
        // Cajas de información
        $this->SetFont('Arial', '', 9);
        $this->SetXY(10, 22);
        $this->Cell(140, 6, convertirTexto('Modalidad: ' . $this->datosEncabezado['bachillerato']), 1, 0, 'L');
        $this->SetXY(10, 29);
        $this->Cell(100, 6, 'Nombre Asignatura:', 1, 0, 'L');
        $this->Cell(100, 6, 'Nombre Docente:', 1, 0, 'L');

        // Cuadro de Grado/Sección a la derecha
        $this->SetXY(215, 8);
        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 27, '', 1, 0, 'L');
        $this->Text(217, 13, convertirTexto('Grado: ' . $this->datosEncabezado['grado']));
        $this->Text(217, 18, convertirTexto('Sección: ' . $this->datosEncabezado['seccion']));
        $this->Text(217, 23, convertirTexto('Año Lectivo: ' . $this->datosEncabezado['ann_lectivo']));
        $this->Text(217, 28, convertirTexto('Período: ____________'));
        
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
        $this->SetFillColor(220, 220, 220); // Gris del encabezado
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.2);
        $this->SetFont('Arial', 'B', 8);

        $y_inicial = $this->GetY();
        $this->Cell(8, 10, 'N', 1, 0, 'C', true);
        $this->Cell(16, 10, 'NIE', 1, 0, 'C', true);

        $x_pos = $this->GetX();
        $this->MultiCell(66, 5, "Nombre de Alumnos/as\n(Orden Alfabético por Apellido)", 1, 'C', true);
        $this->SetXY($x_pos + 66, $y_inicial);

        // Ancho disponible: 279mm (Letter L) - 15mm (márgenes) - 8 - 16 - 66 = 174mm
        $anchoDia = 174 / count($this->diasDelMes);
        
        $this->SetFont('Arial', 'B', 7);
        foreach ($this->diasDelMes as $dia) {
            $this->Cell($anchoDia, 5, $dia['nombreDia'], 1, 0, 'C', true);
        }

        $this->SetXY($x_pos + 66, $y_inicial + 5);
        foreach ($this->diasDelMes as $dia) {
             $this->Cell($anchoDia, 5, $dia['numeroDia'], 1, 0, 'C', true);
        }
        $this->Ln(5);
    }
}

function obtenerDatosAsistencia(PDO $pdo, string $codigoAll, string $mes, string $ann_lectivo): array {
    // ... (Esta función no necesita cambios, la dejamos como estaba) ...
    $datos = ['encabezado' => [], 'alumnos' => [], 'calendario' => []];
    $sqlEncabezado = "SELECT btrim(bach.nombre) as bachillerato, btrim(gan.nombre) as grado, 
                      btrim(sec.nombre) as seccion, ann.nombre as ann_lectivo
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
    if ($encabezado) { $datos['encabezado'] = $encabezado; }
    $sqlAlumnos = "SELECT a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno
                   FROM alumno a
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                   WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ?
                   ORDER BY apellido_alumno";
    $stmt = $pdo->prepare($sqlAlumnos);
    $stmt->execute([$codigoAll]);
    $datos['alumnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    $datos['encabezado']['nombre_mes'] = $meses[(int)$mes - 1];
    $totalDias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, (int)$ann_lectivo);
    $nombresDias = ["D", "L", "M", "M", "J", "V", "S"];
    for ($d = 1; $d <= $totalDias; $d++) {
        $fecha = new DateTime("$ann_lectivo-$mes-$d");
        $datos['calendario'][] = ['numeroDia' => $d, 'nombreDia' => $nombresDias[$fecha->format('w')]];
    }
    return $datos;
}

function generarPdfAsistencia(array $datos) {
    $pdf = new PDF_Asistencia('L', 'mm', 'Letter');
    $pdf->SetMargins(10, 15, 5);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();

    $pdf->setDatosEncabezado($datos['encabezado']);
    $pdf->setDiasDelMes($datos['calendario']);
    
    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8);
    
    $numFila = 0;
    $fill = false; // Para controlar el color de fondo alterno
    $anchoDia = 174 / count($datos['calendario']);

    // Colores
    $colorFilaGris = [240, 240, 240];
    $colorBlanco = [255, 255, 255];
    $colorFinDeSemana = [220, 220, 220];

    // Bucle para alumnos
    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA == 0) {
            $pdf->AddPage();
            $pdf->TablaEncabezado();
            $pdf->SetFont('Arial', '', 8);
        }
        
        // Establecer color de fondo para la fila (blanco o gris claro)
        $pdf->SetFillColor($fill ? $colorFilaGris[0] : $colorBlanco[0], $fill ? $colorFilaGris[1] : $colorBlanco[1], $fill ? $colorFilaGris[2] : $colorBlanco[2]);
        
        $pdf->Cell(8, 6, $numFila + 1, 'LR', 0, 'C', true);
        $pdf->Cell(16, 6, trim($alumno['codigo_nie']), 'LR', 0, 'C', true);
        $pdf->Cell(66, 6, convertirtexto(trim($alumno['apellido_alumno'])), 'LR', 0, 'L', true);
        
        foreach ($datos['calendario'] as $dia) {
            $esFinDeSemana = in_array($dia['nombreDia'], ['S', 'D']);
            // Si es fin de semana, usar el color de fin de semana. Si no, mantener el color de la fila.
            $pdf->SetFillColor($esFinDeSemana ? $colorFinDeSemana[0] : ($fill ? $colorFilaGris[0] : $colorBlanco[0]), $esFinDeSemana ? $colorFinDeSemana[1] : ($fill ? $colorFilaGris[1] : $colorBlanco[1]), $esFinDeSemana ? $colorFinDeSemana[2] : ($fill ? $colorFilaGris[2] : $colorBlanco[2]));
            $pdf->Cell($anchoDia, 6, '', 1, 0, 'C', true);
        }

        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    // Bucle para rellenar filas vacías
    $filasEnPagina = $numFila % FILAS_POR_PAGINA;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA : FILAS_POR_PAGINA - $filasEnPagina;
    
    for ($i = 0; $i < $filasFaltantes; $i++) {
        $pdf->SetFillColor($fill ? $colorFilaGris[0] : $colorBlanco[0], $fill ? $colorFilaGris[1] : $colorBlanco[1], $fill ? $colorFilaGris[2] : $colorBlanco[2]);
        $pdf->Cell(8, 6, $numFila + 1, 'LRB', 0, 'C', true);
        $pdf->Cell(16, 6, '', 'LRB', 0, 'C', true);
        $pdf->Cell(66, 6, '', 'LRB', 0, 'L', true);
        foreach ($datos['calendario'] as $dia) {
            $esFinDeSemana = in_array($dia['nombreDia'], ['S', 'D']);
            $pdf->SetFillColor($esFinDeSemana ? $colorFinDeSemana[0] : ($fill ? $colorFilaGris[0] : $colorBlanco[0]), $esFinDeSemana ? $colorFinDeSemana[1] : ($fill ? $colorFilaGris[1] : $colorBlanco[1]), $esFinDeSemana ? $colorFinDeSemana[2] : ($fill ? $colorFilaGris[2] : $colorBlanco[2]));
            $pdf->Cell($anchoDia, 6, '', 1, 0, 'C', true);
        }
        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }
    
    $nombreArchivo = "Asistencia - {$datos['encabezado']['grado']} {$datos['encabezado']['seccion']} - {$datos['encabezado']['nombre_mes']}.pdf";
    $pdf->Output($nombreArchivo, 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }
    $codigo_all = $_GET["todos"] ?? null;
    $fecha_mes = $_GET["lstFechaMes"] ?? null;
    $fecha_ann = $_GET["lstannlectivo"] ?? null;
    if (!$codigo_all || !$fecha_mes || !$fecha_ann) { throw new Exception("Faltan parámetros para generar el reporte."); }
    $datosReporte = obtenerDatosAsistencia($dblink, $codigo_all, $fecha_mes, $fecha_ann);
    if (empty($datosReporte['alumnos'])) {
        echo "No se encontraron alumnos para este grupo. Verifique los filtros.";
        exit;
    }
    generarPdfAsistencia($datosReporte);
} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>