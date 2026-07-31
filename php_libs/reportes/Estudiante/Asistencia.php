<?php
// <-- VERSIÓN MEJORADA Y MODERNIZADA -->

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
 * Clase FPDF con TablaEncabezado integrada correctamente
 */
class PDF_Asistencia extends FPDF {
    private $datosEncabezado = [];
    private $diasDelMes = [];
    private $estadisticas = ['M' => 0, 'F' => 0, 'Total' => 0];

    public function setDatosEncabezado(array $datos) {
        $this->datosEncabezado = $datos;
    }

    public function setDiasDelMes(array $dias) {
        $this->diasDelMes = $dias;
    }

    public function setEstadisticas(array $est) {
        $this->estadisticas = $est;
    }

    function Header() {
        // --- Logo y Títulos ---
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 12, 5, 10);
        }

        $this->SetXY(25, 6);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(31, 78, 120);
        $this->Cell(185, 4, convertirtexto('COMPLEJO EDUCATIVO COLONIA RÍO ZARCO'), 0, 1, 'L');

        $this->SetX(25);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(80, 80, 80);
        $this->Cell(185, 4, 'LISTA DE ASISTENCIA - MES: ' . strtoupper($this->datosEncabezado['nombre_mes']), 0, 1, 'L');

        // Cuadro Grado/Sección (Derecha)
        $this->SetXY(215, 6);
        $this->SetFillColor(245, 247, 250);
        $this->SetDrawColor(200, 200, 200);
        $this->Rect(215, 6, 54, 29, 'DF');

        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(31, 78, 120);
        $this->Text(218, 11, convertirTexto('Grado: ' . $this->datosEncabezado['grado']));
        $this->Text(218, 16, convertirTexto('Sección: ' . $this->datosEncabezado['seccion']));
        $this->Text(218, 21, convertirTexto('Año Lectivo: ' . $this->datosEncabezado['ann_lectivo']));
        
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Text(218, 27, convertirTexto('Período: ____________'));

        // Cajas de Información (Izquierda)
        $this->SetY(16);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);

        $this->SetX(10);
        $this->Cell(200, 4.5, convertirTexto('Modalidad: ' . $this->datosEncabezado['bachillerato']), 1, 1, 'L');
        
        $this->SetX(10);
        $this->Cell(100, 4.5, 'Nombre Asignatura:', 1, 0, 'L');
        $this->Cell(100, 4.5, 'Nombre Docente:', 1, 1, 'L');

        // --- FILA DE ESTADÍSTICA DE GÉNERO CON PORCENTAJES ---
        $this->SetX(10);
        $total = $this->estadisticas['Total'] > 0 ? $this->estadisticas['Total'] : 1;
        $m = $this->estadisticas['M'];
        $f = $this->estadisticas['F'];

        // Cálculo de Porcentajes (redondeado a 1 decimal o entero)
        $porcM = round(($m / $total) * 100, 1);
        $porcF = round(($f / $total) * 100, 1);

        $textoEstadistica = "Estadística de Matriculados: Masculino (M): $m  |  Femenino (F): $f  |  Total: {$this->estadisticas['Total']}";
        $this->Cell(130, 5, convertirTexto($textoEstadistica), 1, 0, 'L');

        // Dibujo de Barra Bicolor
        $xBarra = 140;
        $yBarra = $this->GetY();
        $this->Cell(70, 5, '', 1, 1, 'L'); // Marco contenedor

        $anchoBarraMax = 66;
        $anchoM = ($m / $total) * $anchoBarraMax;
        $anchoF = ($f / $total) * $anchoBarraMax;

        // 1. Color Masculino (Azul) + Porcentaje
        $this->SetFillColor(52, 152, 219);
        if ($anchoM > 0) {
            $this->Rect($xBarra + 2, $yBarra + 1, $anchoM, 3, 'F');
        }

        // 2. Color Femenino (Rojo/Rosa) + Porcentaje
        $this->SetFillColor(231, 76, 60);
        if ($anchoF > 0) {
            $this->Rect($xBarra + 2 + $anchoM, $yBarra + 1, $anchoF, 3, 'F');
        }

        // 3. Impresión de Porcentajes sobre/dentro de la barra
        $this->SetFont('Arial', 'B', 6);
        $this->SetTextColor(255, 255, 255); // Texto Blanco sobre el color

        // Porcentaje Masculino (si el espacio lo permite)
        if ($anchoM > 8) {
            $this->SetXY($xBarra + 2, $yBarra + 0.5);
            $this->Cell($anchoM, 4, $porcM . '%', 0, 0, 'C');
        }

        // Porcentaje Femenino (si el espacio lo permite)
        if ($anchoF > 8) {
            $this->SetXY($xBarra + 2 + $anchoM, $yBarra + 0.5);
            $this->Cell($anchoF, 4, $porcF . '%', 0, 0, 'C');
        }

        // Restaurar color de texto por defecto
        $this->SetTextColor(0, 0, 0);
        $this->SetY(37);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->SetDrawColor(220, 220, 220);

        $this->Line(10, $this->GetY() - 2, 269, $this->GetY() - 2);

        // --- ESTABLECER ZONA HORARIA LOCAL DE EL SALVADOR ---
        date_default_timezone_set('America/El_Salvador');

        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $hora = date('g:i A'); // Ahora imprimirá la hora exacta en CST (GMT-6)

        $fechaFormateada = "Generado en Santa Ana, $dia de $mes de $anio a las $hora";

        $this->Cell(180, 6, convertirtexto($fechaFormateada), 0, 0, 'L');
        $this->Cell(79, 6, convertirtexto('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    // ### MÉTODO TABLAENCABEZADO DENTRO DE LA CLASE ###
    function TablaEncabezado() {
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(.2);
        
        $y_inicial = $this->GetY();

        $this->SetFillColor(31, 78, 120);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);

        $this->Cell(8, 10, 'N°', 1, 0, 'C', true);
        $this->Cell(16, 10, 'NIE', 1, 0, 'C', true);

        $x_pos = $this->GetX();
        $this->MultiCell(66, 5, convertirTexto("Nombre de Alumnos/as\n(Orden Alfabético por Apellido)"), 1, 'C', true);
        $this->SetXY($x_pos + 66, $y_inicial);

        $anchoDia = 174 / count($this->diasDelMes);
        
        // Fila 1: Nombres de los Días
        $this->SetFont('Arial', 'B', 7);
        foreach ($this->diasDelMes as $dia) {
            $esFinDeSemana = in_array($dia['nombreDia'], ['Sa', 'Do']);
            
            if ($esFinDeSemana) {
                $this->SetFillColor(120, 130, 140);
                $this->SetTextColor(255, 255, 255);
            } else {
                $this->SetFillColor(217, 225, 242);
                $this->SetTextColor(0, 0, 0);
            }

            $this->Cell($anchoDia, 5, $dia['nombreDia'], 1, 0, 'C', true);
        }

        // Fila 2: Números de los Días
        $this->SetXY($x_pos + 66, $y_inicial + 5);
        foreach ($this->diasDelMes as $dia) {
            $esFinDeSemana = in_array($dia['nombreDia'], ['Sa', 'Do']);
            
            if ($esFinDeSemana) {
                $this->SetFillColor(180, 190, 200);
                $this->SetTextColor(0, 0, 0);
            } else {
                $this->SetFillColor(235, 240, 250);
                $this->SetTextColor(0, 0, 0);
            }

            $this->Cell($anchoDia, 5, $dia['numeroDia'], 1, 0, 'C', true);
        }

        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);
    }
} // <--- CIERRE DE LA CLASE PDF_Asistencia
function obtenerDatosAsistencia(PDO $pdo, string $codigoAll, string $mes, string $ann_lectivo): array {
    $datos = ['encabezado' => [], 'alumnos' => [], 'calendario' => [], 'estadisticas' => ['M' => 0, 'F' => 0, 'Total' => 0]];
    
    // Consulta de encabezado...
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

    // Consulta de Alumnos trayendo el campo genero
    $sqlAlumnos = "SELECT a.codigo_nie, a.genero,
                          btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno
                   FROM alumno a
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                   WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ?
                   ORDER BY apellido_alumno";
    $stmt = $pdo->prepare($sqlAlumnos);
    $stmt->execute([$codigoAll]);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos['alumnos'] = $alumnos;

    // Conteo de Masculino y Femenino
    $totalM = 0;
    $totalF = 0;
    foreach ($alumnos as $al) {
        $gen = strtoupper(trim($al['genero'] ?? ''));
        if ($gen === 'M' || $gen === '1' || $gen === 'MASCULINO') {
            $totalM++;
        } elseif ($gen === 'F' || $gen === '2' || $gen === 'FEMENINO') {
            $totalF++;
        }
    }

    $datos['estadisticas']['M'] = $totalM;
    $datos['estadisticas']['F'] = $totalF;
    $datos['estadisticas']['Total'] = count($alumnos);

    // Días del mes...
    $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
    $datos['encabezado']['nombre_mes'] = $meses[(int)$mes - 1];
    
    $totalDias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, (int)$ann_lectivo);
    $nombresDias = ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"];
    
    for ($d = 1; $d <= $totalDias; $d++) {
        $fecha = new DateTime("$ann_lectivo-$mes-$d");
        $datos['calendario'][] = [
            'numeroDia' => $d, 
            'nombreDia' => $nombresDias[$fecha->format('w')]
        ];
    }
    return $datos;
}
function generarPdfAsistencia(array $datos) {
    $pdf = new PDF_Asistencia('L', 'mm', 'Letter');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();

    $pdf->setDatosEncabezado($datos['encabezado']);
    $pdf->setDiasDelMes($datos['calendario']);
    $pdf->setEstadisticas($datos['estadisticas']); // <--- AÑADIDO AQUÍ
    
    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8);
    
    $numFila = 0;
    $fill = false;
    $anchoDia = 174 / count($datos['calendario']);

    // Definición de Colores
    $colorFilaGris = [245, 247, 250];
    $colorBlanco = [255, 255, 255];
    $colorFinDeSemana = [226, 226, 226]; // Gris más marcado para Sábado y Domingo

    // Bucle para alumnos
    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA == 0) {
            $pdf->AddPage();
            $pdf->TablaEncabezado();
            $pdf->SetFont('Arial', '', 8);
        }
        
        $pdf->SetFillColor($fill ? $colorFilaGris[0] : $colorBlanco[0], $fill ? $colorFilaGris[1] : $colorBlanco[1], $fill ? $colorFilaGris[2] : $colorBlanco[2]);
        
        $pdf->Cell(8, 6, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(16, 6, trim($alumno['codigo_nie']), 1, 0, 'C', true);
        $pdf->Cell(66, 6, convertirtexto(trim($alumno['apellido_alumno'])), 1, 0, 'L', true);
        
        foreach ($datos['calendario'] as $dia) {
            // Evaluamos Sa o Do
            $esFinDeSemana = in_array($dia['nombreDia'], ['Sa', 'Do']);
            
            $pdf->SetFillColor(
                $esFinDeSemana ? $colorFinDeSemana[0] : ($fill ? $colorFilaGris[0] : $colorBlanco[0]), 
                $esFinDeSemana ? $colorFinDeSemana[1] : ($fill ? $colorFilaGris[1] : $colorBlanco[1]), 
                $esFinDeSemana ? $colorFinDeSemana[2] : ($fill ? $colorFilaGris[2] : $colorBlanco[2])
            );
            
            $pdf->Cell($anchoDia, 6, '', 1, 0, 'C', true);
        }

        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    // Bucle para rellenar filas vacías de la página
    $filasEnPagina = $numFila % FILAS_POR_PAGINA;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA : FILAS_POR_PAGINA - $filasEnPagina;
    
    for ($i = 0; $i < $filasFaltantes; $i++) {
        $pdf->SetFillColor($fill ? $colorFilaGris[0] : $colorBlanco[0], $fill ? $colorFilaGris[1] : $colorBlanco[1], $fill ? $colorFilaGris[2] : $colorBlanco[2]);
        
        $pdf->Cell(8, 6, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(16, 6, '', 1, 0, 'C', true);
        $pdf->Cell(66, 6, '', 1, 0, 'L', true);
        
        foreach ($datos['calendario'] as $dia) {
            $esFinDeSemana = in_array($dia['nombreDia'], ['Sa', 'Do']);
            $pdf->SetFillColor(
                $esFinDeSemana ? $colorFinDeSemana[0] : ($fill ? $colorFilaGris[0] : $colorBlanco[0]), 
                $esFinDeSemana ? $colorFinDeSemana[1] : ($fill ? $colorFilaGris[1] : $colorBlanco[1]), 
                $esFinDeSemana ? $colorFinDeSemana[2] : ($fill ? $colorFilaGris[2] : $colorBlanco[2])
            );
            $pdf->Cell($anchoDia, 6, '', 1, 0, 'C', true);
        }
        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }
    
    $nombreArchivo = "Asistencia - {$datos['encabezado']['grado']} {$datos['encabezado']['seccion']} - {$datos['encabezado']['nombre_mes']}.pdf";
    $pdf->Output($nombreArchivo, 'I');
}

// --- PUNTO DE ENTRADA ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }
    $codigo_all = $_GET["todos"] ?? null;
    $fecha_mes = $_GET["lstFechaMes"] ?? null;
    $fecha_ann = $_GET["lstannlectivo"] ?? null;
    
    if (!$codigo_all || !$fecha_mes || !$fecha_ann) { 
        throw new Exception("Faltan parámetros para generar el reporte."); 
    }
    
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