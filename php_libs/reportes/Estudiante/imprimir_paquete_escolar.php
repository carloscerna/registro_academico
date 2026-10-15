<?php
// <-- IMPRESIÓN Y REPORTE DE PAQUETES ESCOLARES -->

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA', 20);

function esVerdadero($valor): bool {
    if (is_bool($valor)) return $valor;
    $v = strtolower(trim((string)$valor));
    return ($v === 't' || $v === 'true' || $v === '1' || $v === 'si' || $v === 's');
}

class PDF_PaqueteEscolar extends FPDF {
    private $datosEncabezado = [];
    private $estadisticas = [];

    public function setDatosEncabezado(array $datos) { $this->datosEncabezado = $datos; }
    public function setEstadisticas(array $est) { $this->estadisticas = $est; }

    function Header() {
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
        $this->Cell(185, 4, convertirtexto('CONTROL DE ENTREGA DE PAQUETES ESCOLARES Y TECNOLOGÍA'), 0, 1, 'L');

        // Cuadro Info Derecha
        $this->SetXY(215, 6);
        $this->SetFillColor(245, 247, 250);
        $this->SetDrawColor(200, 200, 200);
        $this->Rect(215, 6, 54, 25, 'DF');

        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(31, 78, 120);
        $this->Text(218, 11, convertirTexto('Grado: ' . ($this->datosEncabezado['grado'] ?? '')));
        $this->Text(218, 16, convertirTexto('Sección: ' . ($this->datosEncabezado['seccion'] ?? '')));
        $this->Text(218, 21, convertirTexto('Año Lectivo: ' . ($this->datosEncabezado['ann_lectivo'] ?? '')));

        // Encabezado Izquierda
        $this->SetY(16);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);

        $this->SetX(10);
        $this->Cell(200, 4.5, convertirTexto('Modalidad: ' . ($this->datosEncabezado['bachillerato'] ?? '')), 1, 1, 'L');

        // Resumen Rápido
        $this->SetX(10);
        $textoEstadistica = "Resumen | Tablets: {$this->estadisticas['tablet']} | Laptops: {$this->estadisticas['laptop']} | Útiles: {$this->estadisticas['utiles']} | Zapatos: {$this->estadisticas['zapatos']} | Blusas: {$this->estadisticas['blusas']} | Camisas: {$this->estadisticas['camisa']} | Faldas: {$this->estadisticas['falda']} | Pantalones: {$this->estadisticas['pantalon']} | Shorts: {$this->estadisticas['short']}";
        $this->Cell(200, 4.5, convertirTexto($textoEstadistica), 1, 1, 'L');

        $this->SetY(33);
    }

    function Footer() {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->SetDrawColor(220, 220, 220);

        $this->Line(10, $this->GetY() - 2, 269, $this->GetY() - 2);

        date_default_timezone_set('America/El_Salvador');
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $fechaFormateada = "Generado en Santa Ana, " . date('d') . " de " . $meses[date('n') - 1] . " de " . date('Y') . " a las " . date('g:i A');

        $this->Cell(180, 6, convertirtexto($fechaFormateada), 0, 0, 'L');
        $this->Cell(79, 6, convertirtexto('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    function TablaEncabezado() {
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(.2);
        
        $y_inicial = $this->GetY();

        $this->SetFillColor(31, 78, 120);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 7);

        // Fila Superior
        $this->Cell(7, 10, 'N°', 1, 0, 'C', true);
        $this->Cell(15, 10, 'NIE', 1, 0, 'C', true);
        $this->Cell(48, 10, convertirTexto('Nombre del Estudiante'), 1, 0, 'C', true);
        
        $this->Cell(34, 5, convertirTexto('Tecnología'), 1, 0, 'C', true);
        $this->Cell(30, 5, convertirTexto('Útiles / Calzado'), 1, 0, 'C', true);
        $this->Cell(55, 5, convertirTexto('Uniformes (Tallas)'), 1, 0, 'C', true);

        $this->Cell(18, 10, 'Estado', 1, 0, 'C', true);
        $this->Cell(16, 10, 'Fecha', 1, 0, 'C', true);
        $this->Cell(26, 10, 'Observaciones', 1, 1, 'C', true);

        // Sub-encabezados Fila Inferior
        $this->SetXY(80, $y_inicial + 5);
        $this->SetFillColor(52, 101, 148);

        $this->Cell(17, 5, 'Tab./Serie', 1, 0, 'C', true);
        $this->Cell(17, 5, 'Lap./Serie', 1, 0, 'C', true);

        $this->Cell(12, 5, 'Útiles', 1, 0, 'C', true);
        $this->Cell(18, 5, 'Zap./Talla', 1, 0, 'C', true);

        $this->Cell(11, 5, 'Blusa', 1, 0, 'C', true);
        $this->Cell(11, 5, 'Camisa', 1, 0, 'C', true);
        $this->Cell(11, 5, 'Falda', 1, 0, 'C', true);
        $this->Cell(11, 5, 'Pant.', 1, 0, 'C', true);
        $this->Cell(11, 5, 'Short', 1, 0, 'C', true);

        $this->SetTextColor(0, 0, 0);
        $this->Ln(5);
    }
}

function obtenerDatosPaqueteEscolar(PDO $pdo, string $codigoAll, string $ann_lectivo): array {
    $datos = [
        'encabezado' => [], 
        'alumnos' => [], 
        'estadisticas' => [
            'tablet' => 0, 'laptop' => 0, 'utiles' => 0, 'zapatos' => 0,
            'blusas' => 0, 'camisa' => 0, 'falda' => 0, 'pantalon' => 0, 'short' => 0, 'total' => 0
        ],
        'tallas_detalle' => [
            'zapatos' => [], 'blusas' => [], 'camisa' => [], 'falda' => [], 'pantalon' => [], 'short' => []
        ]
    ];
    
    $codigo_modalidad = substr($codigoAll, 0, 2);
    $codigo_grado     = substr($codigoAll, 2, 2);
    $codigo_seccion   = substr($codigoAll, 4, 2);
    $codigo_turno     = substr($codigoAll, 6, 2);
    $codigo_annlectivo = substr($codigoAll, 8, 2);

    $sqlEncabezado = "SELECT btrim(bach.nombre) as bachillerato, btrim(gan.nombre) as grado, 
                      btrim(sec.nombre) as seccion, ann.nombre as ann_lectivo
                      FROM alumno_matricula am
                      INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                      INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                      INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                      INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                      WHERE am.codigo_bach_o_ciclo = :modalidad 
                        AND am.codigo_grado = :grado 
                        AND am.codigo_seccion = :seccion 
                        AND am.codigo_ann_lectivo = :annlectivo 
                        AND am.codigo_turno = :turno
                      LIMIT 1";
                      
    $stmt = $pdo->prepare($sqlEncabezado);
    $stmt->execute([
        ':modalidad' => $codigo_modalidad,
        ':grado' => $codigo_grado,
        ':seccion' => $codigo_seccion,
        ':annlectivo' => $codigo_annlectivo,
        ':turno' => $codigo_turno
    ]);
    
    $encabezado = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($encabezado) { $datos['encabezado'] = $encabezado; }

    $ann_num = (int)$ann_lectivo; 

    $sqlAlumnos = "SELECT a.codigo_nie, 
                          btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                          pe.posee_tablet, pe.tablet_serie, pe.posee_computadora_portatil, pe.computadora_serie,
                          pe.posee_utiles_escolares, pe.posee_zapatos, pe.talla_zapatos,
                          pe.posee_blusas, pe.talla_blusas, pe.posee_camisa, pe.talla_camisa,
                          pe.posee_falda, pe.talla_falda, pe.posee_pantalon, pe.talla_pantalon, 
                          pe.posee_short, pe.talla_short, pe.estado_entrega, pe.fecha_entrega, pe.observacion
                   FROM alumno a
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                   LEFT JOIN alumno_paquete_escolar pe ON a.id_alumno = pe.id_alumno 
                        AND (pe.anio_lectivo = :ann_num OR pe.anio_lectivo::text = :ann_str)
                   WHERE am.codigo_bach_o_ciclo = :modalidad 
                     AND am.codigo_grado = :grado 
                     AND am.codigo_seccion = :seccion 
                     AND am.codigo_ann_lectivo = :annlectivo 
                     AND am.codigo_turno = :turno
                   ORDER BY apellido_alumno ASC";
                   
    $stmt = $pdo->prepare($sqlAlumnos);
    $stmt->execute([
        ':modalidad' => $codigo_modalidad,
        ':grado' => $codigo_grado,
        ':seccion' => $codigo_seccion,
        ':annlectivo' => $codigo_annlectivo,
        ':turno' => $codigo_turno,
        ':ann_num' => $ann_num,
        ':ann_str' => $ann_lectivo
    ]);
    
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $datos['alumnos'] = $alumnos;

    // Conteo y agrupación por tallas para el resumen
    foreach ($alumnos as $al) {
        if (esVerdadero($al['posee_tablet'] ?? false)) $datos['estadisticas']['tablet']++;
        if (esVerdadero($al['posee_computadora_portatil'] ?? false)) $datos['estadisticas']['laptop']++;
        if (esVerdadero($al['posee_utiles_escolares'] ?? false)) $datos['estadisticas']['utiles']++;
        
        $prendas = ['zapatos', 'blusas', 'camisa', 'falda', 'pantalon', 'short'];
        foreach ($prendas as $prenda) {
            $campo_bool = 'posee_' . $prenda;
            $campo_talla = 'talla_' . $prenda;
            
            if (esVerdadero($al[$campo_bool] ?? false)) {
                $datos['estadisticas'][$prenda]++;
                $talla = !empty(trim($al[$campo_talla] ?? '')) ? strtoupper(trim($al[$campo_talla])) : 'S/T';
                if (!isset($datos['tallas_detalle'][$prenda][$talla])) {
                    $datos['tallas_detalle'][$prenda][$talla] = 0;
                }
                $datos['tallas_detalle'][$prenda][$talla]++;
            }
        }
    }
    $datos['estadisticas']['total'] = count($alumnos);

    return $datos;
}

function generarPdfPaqueteEscolar(array $datos) {
    $pdf = new PDF_PaqueteEscolar('L', 'mm', 'Letter');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();

    $pdf->setDatosEncabezado($datos['encabezado']);
    $pdf->setEstadisticas($datos['estadisticas']);
    
    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 7);
    
    $numFila = 0;
    $fill = false;
    $colorGris = [245, 247, 250];
    $colorBlanco = [255, 255, 255];

    foreach ($datos['alumnos'] as $al) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA == 0) {
            $pdf->AddPage();
            $pdf->TablaEncabezado();
            $pdf->SetFont('Arial', '', 7);
        }
        
        $pdf->SetFillColor($fill ? $colorGris[0] : $colorBlanco[0], $fill ? $colorGris[1] : $colorBlanco[1], $fill ? $colorGris[2] : $colorBlanco[2]);
        
        // Datos Básicos
        $pdf->Cell(7, 6, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(15, 6, trim($al['codigo_nie'] ?? ''), 1, 0, 'C', true);
        $pdf->Cell(48, 6, convertirtexto(trim($al['apellido_alumno'] ?? '')), 1, 0, 'L', true);
        
        // Tecnología
        $txtTab = esVerdadero($al['posee_tablet'] ?? false) ? (!empty($al['tablet_serie']) ? $al['tablet_serie'] : 'Sí') : 'No';
        $txtLap = esVerdadero($al['posee_computadora_portatil'] ?? false) ? (!empty($al['computadora_serie']) ? $al['computadora_serie'] : 'Sí') : 'No';

        $pdf->Cell(17, 6, convertirtexto($txtTab), 1, 0, 'C', true);
        $pdf->Cell(17, 6, convertirtexto($txtLap), 1, 0, 'C', true);

        // Útiles y Zapatos
        $txtUti = esVerdadero($al['posee_utiles_escolares'] ?? false) ? 'Sí' : 'No';
        $txtZap = esVerdadero($al['posee_zapatos'] ?? false) ? (!empty($al['talla_zapatos']) ? $al['talla_zapatos'] : 'Sí') : 'No';

        $pdf->Cell(12, 6, convertirtexto($txtUti), 1, 0, 'C', true);
        $pdf->Cell(18, 6, convertirtexto($txtZap), 1, 0, 'C', true);

        // Uniformes
        $txtBlu = esVerdadero($al['posee_blusas'] ?? false) ? (!empty($al['talla_blusas']) ? $al['talla_blusas'] : 'Sí') : 'No';
        $txtCam = esVerdadero($al['posee_camisa'] ?? false) ? (!empty($al['talla_camisa']) ? $al['talla_camisa'] : 'Sí') : 'No';
        $txtFal = esVerdadero($al['posee_falda'] ?? false) ? (!empty($al['talla_falda']) ? $al['talla_falda'] : 'Sí') : 'No';
        $txtPan = esVerdadero($al['posee_pantalon'] ?? false) ? (!empty($al['talla_pantalon']) ? $al['talla_pantalon'] : 'Sí') : 'No';
        $txtSho = esVerdadero($al['posee_short'] ?? false) ? (!empty($al['talla_short']) ? $al['talla_short'] : 'Sí') : 'No';

        $pdf->Cell(11, 6, convertirtexto($txtBlu), 1, 0, 'C', true);
        $pdf->Cell(11, 6, convertirtexto($txtCam), 1, 0, 'C', true);
        $pdf->Cell(11, 6, convertirtexto($txtFal), 1, 0, 'C', true);
        $pdf->Cell(11, 6, convertirtexto($txtPan), 1, 0, 'C', true);
        $pdf->Cell(11, 6, convertirtexto($txtSho), 1, 0, 'C', true);

        // Estado, Fecha y Observación
        $pdf->Cell(18, 6, convertirtexto($al['estado_entrega'] ?? 'Pendiente'), 1, 0, 'C', true);
        $pdf->Cell(16, 6, $al['fecha_entrega'] ?? '', 1, 0, 'C', true);
        $pdf->Cell(26, 6, convertirtexto($al['observacion'] ?? ''), 1, 1, 'L', true);

        $fill = !$fill;
        $numFila++;
    }

    // Fila Totales
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->SetFillColor(217, 225, 242);
    $pdf->Cell(70, 6, 'TOTALES ENTREGADOS', 1, 0, 'R', true);
    $pdf->Cell(17, 6, $datos['estadisticas']['tablet'], 1, 0, 'C', true);
    $pdf->Cell(17, 6, $datos['estadisticas']['laptop'], 1, 0, 'C', true);
    $pdf->Cell(12, 6, $datos['estadisticas']['utiles'], 1, 0, 'C', true);
    $pdf->Cell(18, 6, $datos['estadisticas']['zapatos'], 1, 0, 'C', true);
    $pdf->Cell(11, 6, $datos['estadisticas']['blusas'], 1, 0, 'C', true);
    $pdf->Cell(11, 6, $datos['estadisticas']['camisa'], 1, 0, 'C', true);
    $pdf->Cell(11, 6, $datos['estadisticas']['falda'], 1, 0, 'C', true);
    $pdf->Cell(11, 6, $datos['estadisticas']['pantalon'], 1, 0, 'C', true);
    $pdf->Cell(11, 6, $datos['estadisticas']['short'], 1, 0, 'C', true);
    $pdf->Cell(60, 6, '', 1, 1, 'C', true);

    // --- SECCIÓN DE RESUMEN DESGLOSADO POR TALLAS ---
    if ($pdf->GetY() > 150) {
        $pdf->AddPage();
    } else {
        $pdf->Ln(4);
    }

    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetFillColor(31, 78, 120);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->Cell(259, 5, convertirTexto('DESGLOSE DETALLADO DE TALLAS ENTREGADAS EN EL GRUPO'), 1, 1, 'C', true);

    $items_prendas = [
        'zapatos'  => 'Calzado (Zapatos)',
        'blusas'   => 'Blusas',
        'camisa'   => 'Camisas',
        'falda'    => 'Faldas',
        'pantalon' => 'Pantalones',
        'short'    => 'Shorts'
    ];

    $pdf->SetFont('Arial', '', 7);
    $pdf->SetTextColor(0, 0, 0);

    foreach ($items_prendas as $llave => $titulo) {
        $pdf->SetFillColor(240, 244, 248);
        $pdf->SetFont('Arial', 'B', 7);
        $pdf->Cell(50, 4.5, convertirTexto($titulo), 1, 0, 'L', true);
        
        $pdf->SetFont('Arial', '', 7);
        $tallas_str = "";
        if (!empty($datos['tallas_detalle'][$llave])) {
            ksort($datos['tallas_detalle'][$llave]);
            foreach ($datos['tallas_detalle'][$llave] as $talla => $cant) {
                $tallas_str .= "Talla {$talla}: {$cant} | ";
            }
            $tallas_str = rtrim($tallas_str, " | ");
        } else {
            $tallas_str = "Sin entregas registradas";
        }
        
        $pdf->Cell(209, 4.5, convertirTexto($tallas_str), 1, 1, 'L');
    }

    $nombreArchivo = "Paquetes Escolares - " . ($datos['encabezado']['grado'] ?? '') . " " . ($datos['encabezado']['seccion'] ?? '') . ".pdf";
    $pdf->Output($nombreArchivo, 'I');
}

// --- PUNTO DE ENTRADA ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }
    $codigo_all = $_GET["todos"] ?? null;
    $fecha_ann = $_GET["lstannlectivo"] ?? null;
    
    if (!$codigo_all || !$fecha_ann) { 
        throw new Exception("Faltan parámetros requeridos para generar el reporte."); 
    }
    
    $datosReporte = obtenerDatosPaqueteEscolar($dblink, $codigo_all, $fecha_ann);
    
    if (empty($datosReporte['alumnos'])) {
        echo "No se encontraron alumnos para este grupo. Verifique los filtros.";
        exit;
    }
    generarPdfPaqueteEscolar($datosReporte);
} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>