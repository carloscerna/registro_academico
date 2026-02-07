<?php
// <-- ARCHIVO CORREGIDO: TOMA DE TALLAS CON JOIN A ENCARGADO -->

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/El_Salvador');

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_TALLAS', 25); 

class PDF_TomaTalla extends FPDF {
    public $datosEncabezado = [];
    
    function Header() {
        // Logo
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 15);
        }

        // Título Principal
        $this->SetFont('Arial', 'B', 14);
        $this->SetXY(28, 10);
        $this->Cell(0, 10, convertirtexto('TOMA DE TALLAS'), 0, 1, 'C');

        // Datos Informativos
        $this->SetFont('Arial', 'B', 9);
        $this->SetX(28);
        $this->Cell(35, 5, convertirtexto('CENTRO EDUCATIVO:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, convertirtexto($_SESSION['institucion']), 0, 1, 'L');

        // Línea 2
        $this->SetX(28);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(15, 5, convertirtexto('CÓDIGO:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $codigoInfra = $_SESSION['codigo_infraestructura'] ?? '________________'; 
        $this->Cell(35, 5, $codigoInfra, 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(15, 5, 'FECHA:', 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, date('d/m/Y'), 0, 1, 'L');

        // Línea 3
        $this->SetX(28);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(15, 5, 'GRADO:', 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(50, 5, convertirtexto($this->datosEncabezado['grado']), 0, 0, 'L');

        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 5, convertirtexto('SECCIÓN:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 5, convertirtexto($this->datosEncabezado['seccion']), 0, 1, 'L');
        
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . ' de {nb}', 0, 0, 'C');
    }

    function TablaEncabezado() {
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(0);
        $this->SetFont('Arial', 'B', 7);
        
        // No | Grado/Sec | Sexo | NIE | Nombre | Celular | T.Cam | T.Pant | T.Zap
        $this->Cell(8, 8, 'No.', 1, 0, 'C', true);
        $this->Cell(20, 8, convertirtexto("GRADO/SEC"), 1, 0, 'C', true);
        $this->Cell(8, 8, 'SEX', 1, 0, 'C', true);
        $this->Cell(15, 8, 'NIE', 1, 0, 'C', true);
        $this->Cell(60, 8, 'NOMBRE DEL ESTUDIANTE', 1, 0, 'C', true);
        $this->Cell(20, 8, 'CELULAR', 1, 0, 'C', true); 
        $this->Cell(20, 8, 'T. CAMISA', 1, 0, 'C', true);
        $this->Cell(25, 8, 'T. PANT/FALDA', 1, 0, 'C', true);
        $this->Cell(20, 8, 'T. ZAPATOS', 1, 0, 'C', true);
        $this->Ln();
    }
}

function obtenerDatosTallas(PDO $pdo, string $codigoAll): array {
    $datos = ['encabezado' => [], 'alumnos' => []];
    
    // 1. Encabezado
    $sqlEncabezado = "SELECT btrim(bach.nombre) as bachillerato, btrim(gan.nombre) as grado, btrim(sec.nombre) as seccion, ann.nombre as ann_lectivo 
                      FROM alumno_matricula am 
                      INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
                      INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
                      INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
                      INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
                      WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ? LIMIT 1";
    
    $stmt = $pdo->prepare($sqlEncabezado); 
    $stmt->execute([$codigoAll]); 
    $encabezadoData = $stmt->fetch(PDO::FETCH_ASSOC); 
    if($encabezadoData){ $datos['encabezado'] = $encabezadoData; }

    // 2. Alumnos con LEFT JOIN a alumno_encargado
    // CORRECCIÓN: Se une alumno (a) con alumno_encargado (ae) usando a.id_alumno = ae.codigo_alumno
    // Se filtra ae.encargado = 't' para obtener solo al responsable principal.
    
    $sqlAlumnos = "SELECT 
                        a.codigo_nie, 
                        a.genero, 
                        ae.telefono as telefono_encargado,
                        btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno 
                   FROM alumno a 
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' 
                   LEFT JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
                   WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ? 
                   ORDER BY apellido_alumno ASC";
    
    $stmt = $pdo->prepare($sqlAlumnos); 
    $stmt->execute([$codigoAll]); 
    $datos['alumnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $datos;
}

function generarPdfTomaTalla(array $datos) {
    $pdf = new PDF_TomaTalla('P', 'mm', 'Letter');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];

    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8);
    $fill = false; 
    $numFila = 0;
    $totalAlumnos = count($datos['alumnos']);

    $gradoCorto = isset($datos['encabezado']['grado']) ? substr($datos['encabezado']['grado'], 0, 10) : '';
    $seccionCorta = $datos['encabezado']['seccion'] ?? '';
    $columnaGradoSeccion = convertirtexto($gradoCorto . ' "' . $seccionCorta . '"');

    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_TALLAS == 0) { 
            $pdf->AddPage(); 
            $pdf->TablaEncabezado(); 
            $pdf->SetFont('Arial', '', 8); 
        }

        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        
        $pdf->Cell(8, 7, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(20, 7, $columnaGradoSeccion, 1, 0, 'C', true);
        
        // Sexo
        $sexo = isset($alumno['genero']) ? $alumno['genero'] : '';
        $pdf->Cell(8, 7, $sexo, 1, 0, 'C', true);

        // NIE
        $pdf->Cell(15, 7, $alumno['codigo_nie'], 1, 0, 'C', true);
        
        // Nombre (cortado a 35 caracteres para evitar desbordes)
        $pdf->Cell(60, 7, convertirtexto(substr($alumno['apellido_alumno'], 0, 35)), 1, 0, 'L', true);

        // Celular Encargado (Este dato ahora viene de la tabla correcta)
        $tel = isset($alumno['telefono_encargado']) ? $alumno['telefono_encargado'] : '';
        $pdf->Cell(20, 7, $tel, 1, 0, 'C', true);

        // Tallas vacías
        $pdf->Cell(20, 7, '', 1, 0, 'C', true);
        $pdf->Cell(25, 7, '', 1, 0, 'C', true);
        $pdf->Cell(20, 7, '', 1, 0, 'C', true);
        
        $pdf->Ln(); 
        $fill = !$fill; 
        $numFila++;
    }

    // Pie de página
    if ($pdf->GetY() > 220) {
        $pdf->AddPage();
    } else {
        $pdf->Ln(10);
    }

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(25, 6, convertirtexto('MATRÍCULA:'), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(15, 6, $totalAlumnos, 'B', 0, 'C'); 
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(40, 6, convertirtexto('ALUMNOS TALLADOS:'), 0, 0, 'R');
    $pdf->Cell(15, 6, '', 'B', 0, 'C');
    
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(40, 6, convertirtexto('ALUMNOS PENDIENTES:'), 0, 0, 'R');
    $pdf->Cell(15, 6, '', 'B', 1, 'C');

    $pdf->Ln(15); 

    $pdf->SetX(60);
    $pdf->Cell(10, 6, 'F.', 0, 0, 'R');
    $pdf->Cell(80, 6, '', 'B', 1, 'C');
    
    $pdf->SetX(60);
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->MultiCell(90, 4, convertirtexto("FIRMA DIRECTOR/A DEL CENTRO EDUCATIVO\nO DOCENTE ENCARGADO DE GRADO"), 0, 'C');
    $pdf->SetX(60);
    $pdf->Cell(90, 4, 'SELLO', 0, 1, 'C');

    $pdf->Output('Listado_Toma_Tallas.pdf', 'I');
}

try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }
    $codigo_all = $_GET["todos"] ?? null;
    if (!$codigo_all) { throw new Exception("Faltan parámetros."); }
    $datosReporte = obtenerDatosTallas($dblink, $codigo_all);
    if (empty($datosReporte['alumnos'])) { echo "No se encontraron alumnos."; exit; }
    generarPdfTomaTalla($datosReporte);
} catch (Exception $e) {
    echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>