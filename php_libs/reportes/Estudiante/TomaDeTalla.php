<?php
// <-- NUEVO ARCHIVO: TOMA DE TALLAS -->

ini_set('display_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/El_Salvador');

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_TALLAS', 25); // Ajustado para dar espacio al pie de firma

/**
 * Clase FPDF personalizada para el reporte de Toma de Tallas.
 */
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

        // Datos Informativos (Estilo Formulario según imagen)
        $this->SetFont('Arial', 'B', 10);
        $this->SetX(28);
        $this->Cell(40, 6, convertirtexto('CENTRO EDUCATIVO:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, convertirtexto($_SESSION['institucion']), 0, 1, 'L');

        // Línea 2: Código y Fecha
        $this->SetX(28);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 6, convertirtexto('CÓDIGO:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        // Asumimos el código de infraestructura de la sesión o lo dejamos en blanco si no existe variable
        $codigoInfra = $_SESSION['codigo_infraestructura'] ?? '________________'; 
        $this->Cell(40, 6, $codigoInfra, 0, 0, 'L');
        
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(15, 6, 'FECHA:', 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, date('d/m/Y'), 0, 1, 'L');

        // Línea 3: Grado y Sección
        $this->SetX(28);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(15, 6, 'GRADO:', 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(60, 6, convertirtexto($this->datosEncabezado['grado']), 0, 0, 'L');

        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 6, convertirtexto('SECCIÓN:'), 0, 0, 'L');
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, convertirtexto($this->datosEncabezado['seccion']), 0, 1, 'L');
        
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
        $this->SetFont('Arial', 'B', 8);
        
        // Anchos de columnas ajustados para carta vertical (aprox 195mm ancho útil)
        // No | Grado Sección | Sexo | Nombre | Talla
        
        $this->Cell(10, 8, 'No.', 1, 0, 'C', true);
        $this->Cell(35, 8, convertirtexto("GRADO\nSECCIÓN"), 1, 0, 'C', true); // Multilínea visual simple
        $this->Cell(15, 8, convertirtexto("SEXO\nM o F"), 1, 0, 'C', true);
        $this->Cell(105, 8, 'NOMBRE', 1, 0, 'C', true);
        $this->Cell(30, 8, 'TALLA', 1, 0, 'C', true);
        $this->Ln();
    }
}

/**
 * Obtiene los datos de la base de datos.
 * SE AGREGA 'genero' A LA CONSULTA SQL.
 */
function obtenerDatosTallas(PDO $pdo, string $codigoAll): array {
    $datos = ['encabezado' => [], 'alumnos' => []];
    
    // 1. Datos del Encabezado (Igual que Firmas.php)
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

    // 2. Datos de Alumnos (Se agrega a.genero)
    // NOTA: Verifica si tu columna en la BD se llama 'genero' o 'sexo'. Aquí uso 'genero'.
    $sqlAlumnos = "SELECT a.codigo_nie, a.genero, 
                   btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno 
                   FROM alumno a 
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' 
                   WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ? 
                   ORDER BY apellido_alumno ASC";
    
    $stmt = $pdo->prepare($sqlAlumnos); 
    $stmt->execute([$codigoAll]); 
    $datos['alumnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfTomaTalla(array $datos) {
    $pdf = new PDF_TomaTalla('P', 'mm', 'Letter');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];

    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 9);
    $fill = false; 
    $numFila = 0;
    $totalAlumnos = count($datos['alumnos']);

    // Preparamos texto corto para Grado/Seccion en la tabla
    // Ejemplo: "1er Año A" (ajusta según tus datos reales)
    $gradoCorto = isset($datos['encabezado']['grado']) ? substr($datos['encabezado']['grado'], 0, 15) : '';
    $seccionCorta = $datos['encabezado']['seccion'] ?? '';
    $columnaGradoSeccion = convertirtexto($gradoCorto . ' "' . $seccionCorta . '"');

    foreach ($datos['alumnos'] as $alumno) {
        // Control de salto de página
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_TALLAS == 0) { 
            $pdf->AddPage(); 
            $pdf->TablaEncabezado(); 
            $pdf->SetFont('Arial', '', 9); 
        }

        $pdf->SetFillColor($fill ? 245 : 255, $fill ? 245 : 255, $fill ? 245 : 255);
        
        // 1. No.
        $pdf->Cell(10, 7, $numFila + 1, 1, 0, 'C', true);
        
        // 2. Grado Sección (Repetitivo según imagen)
        $pdf->Cell(35, 7, $columnaGradoSeccion, 1, 0, 'C', true);
        
        // 3. Sexo
        $sexo = isset($alumno['genero']) ? $alumno['genero'] : ''; // Asegúrate que venga de la BD
        $pdf->Cell(15, 7, $sexo, 1, 0, 'C', true);
        
        // 4. Nombre
        $pdf->Cell(105, 7, convertirtexto($alumno['apellido_alumno']), 1, 0, 'L', true);
        
        // 5. Talla (Vacío para llenar a mano)
        $pdf->Cell(30, 7, '', 1, 0, 'C', true);
        
        $pdf->Ln(); 
        $fill = !$fill; 
        $numFila++;
    }

    // Rellenar filas vacías si quedan pocas para completar la página (opcional, visual)
    $filasEnPagina = $numFila % FILAS_POR_PAGINA_TALLAS;
    if ($filasEnPagina > 0) {
        $filasFaltantes = FILAS_POR_PAGINA_TALLAS - $filasEnPagina;
        // Solo rellenamos si faltan poquitas, para que no se vea cortado, 
        // pero en este reporte quizás prefieras dejarlo libre para la firma inmediatamente.
        // Si quieres rellenar descomenta el bucle similar a Firmas.php
    }

    // --- SECCIÓN DE RESUMEN Y FIRMAS (Al final del listado) ---
    // Verificar si queda espacio suficiente, si no, nueva página
    if ($pdf->GetY() > 220) {
        $pdf->AddPage();
    } else {
        $pdf->Ln(10);
    }

    $pdf->SetFont('Arial', 'B', 10);
    
    // Resumen numérico
    // MATRÍCULA: ___  ALUMNOS TALLADOS: ___  ALUMNOS PENDIENTES: ___
    $pdf->Cell(30, 8, convertirtexto('MATRÍCULA:'), 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(20, 8, $totalAlumnos, 'B', 0, 'C'); // Borde inferior para simular línea
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(45, 8, convertirtexto('ALUMNOS TALLADOS:'), 0, 0, 'R');
    $pdf->Cell(20, 8, '', 'B', 0, 'C'); // Espacio vacío para llenar
    
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(45, 8, convertirtexto('ALUMNOS PENDIENTES:'), 0, 0, 'R');
    $pdf->Cell(20, 8, '', 'B', 1, 'C'); // Espacio vacío para llenar

    $pdf->Ln(20); // Espacio vertical para la firma

    // Área de Firma
    // F ________________________
    // FIRMA DIRECTOR/A DEL CENTRO EDUCATIVO...
    $pdf->SetX(60); // Centrar bloque de firma
    $pdf->Cell(10, 6, 'F.', 0, 0, 'R');
    $pdf->Cell(80, 6, '', 'B', 1, 'C'); // Línea de firma
    
    $pdf->SetX(60);
    $pdf->SetFont('Arial', 'B', 8);
    // MultiCell para que el texto largo de la firma se ajuste si es necesario
    $pdf->MultiCell(90, 4, convertirtexto("FIRMA DIRECTOR/A DEL CENTRO EDUCATIVO\nO DOCENTE ENCARGADO DE GRADO"), 0, 'C');
    $pdf->SetX(60);
    $pdf->Cell(90, 4, 'SELLO', 0, 1, 'C');

    $pdf->Output('Listado_Toma_Tallas.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }

    $codigo_all = $_GET["todos"] ?? null;
    
    if (!$codigo_all) { throw new Exception("Faltan parámetros para generar el reporte."); }

    $datosReporte = obtenerDatosTallas($dblink, $codigo_all);

    if (empty($datosReporte['alumnos'])) {
        echo "No se encontraron alumnos para este grupo. Verifique los filtros.";
        exit;
    }

    generarPdfTomaTalla($datosReporte);

} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>