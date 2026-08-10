<?php
// Ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Archivos que se incluyen
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/consultas.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

header("Content-Type: text/html; charset=UTF-8");    

// Variables y consulta a la tabla
$codigo_all = $_REQUEST["todos"] ?? '';
$db_link = $dblink;

if (empty($codigo_all) || !$db_link) {
    die("Error: Parámetros o conexión no válidos.");
}

// Buscar la consulta de encabezado
consultas(9, 0, $codigo_all, '', '', '', $db_link, '');

$print_bachillerato = ''; $nombre_modalidad = '';
$print_grado = '';        $nombre_grado = '';
$print_seccion = '';      $nombre_seccion = '';
$print_ann_lectivo = '';  $nombre_ann_lectivo = '';

while ($row = $result_encabezado->fetch(PDO::FETCH_BOTH)) {
    $print_bachillerato = convertirtexto('Modalidad: ' . trim((string)$row['nombre_bachillerato']));
    $nombre_modalidad = convertirtexto(trim((string)$row['nombre_bachillerato']));
    $print_grado = convertirtexto('Grado: ' . trim((string)$row['nombre_grado']));
    $nombre_grado = convertirtexto(trim((string)$row['nombre_grado']));
    $print_seccion = convertirtexto('Sección: ' . trim((string)$row['nombre_seccion']));
    $nombre_seccion = convertirtexto(trim((string)$row['nombre_seccion']));
    $print_ann_lectivo = convertirtexto('Año Lectivo: ' . trim((string)$row['nombre_ann_lectivo']));
    $nombre_ann_lectivo = convertirtexto(trim((string)$row['nombre_ann_lectivo']));
    break;
}    

// Capturar el nombre del docente
$print_nombre_docente = '';
consultas_docentes(1, 0, $codigo_all, '', '', '', $db_link, '');
while ($row = $result_docente->fetch(PDO::FETCH_BOTH)) {
    $print_nombre_docente = cambiar_de_del(trim((string)$row['nombre_docente']));
    if (!mb_check_encoding($print_nombre_docente, 'ISO-8859-1')) {
        $print_nombre_docente = mb_convert_encoding($print_nombre_docente, 'ISO-8859-1', 'UTF-8');
    }
}        

class PDF extends FPDF
{
    function Header()
    {
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_nombre_docente;
        
        $logo = $_SESSION['logo_uno'] ?? 'logo_default.png'; 
        $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $logo;
        if (file_exists($img)) {
            $this->Image($img, 10, 6, 12, 14);
        }
        
        // Título Institucional
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(31, 78, 120);
        $this->SetXY(25, 6);
        $this->Cell(240, 4, convertirtexto($_SESSION['institucion'] ?? 'MINISTERIO DE EDUCACIÓN'), 0, 1, 'L');
        
        $this->SetX(25);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(80, 80, 80);
        $this->Cell(240, 4, convertirtexto('DATOS GENERALES DE MATRÍCULA DE ESTUDIANTES'), 0, 1, 'L');

        // Cuadro Lateral de Grado / Sección (Derecha)
        $this->SetXY(285, 6);
        $this->SetFillColor(245, 247, 250);
        $this->SetDrawColor(200, 200, 200);
        $this->Rect(285, 6, 60, 22, 'DF');

        $this->SetFont('Arial', 'B', 8);
        $this->SetTextColor(31, 78, 120);
        $this->Text(287, 11, $print_grado);
        $this->Text(287, 16, $print_seccion);
        $this->Text(287, 21, $print_ann_lectivo);

        // Bloque Modalidad / Docente (Izquierda)
        $this->SetY(16);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(0, 0, 0);

        $this->SetX(25);
        $this->Cell(255, 4.5, $print_bachillerato, 1, 1, 'L');
        
        $this->SetX(25);
        $this->Cell(255, 4.5, convertirtexto('Nombre Docente: ' . $print_nombre_docente), 1, 1, 'L');

        $this->SetY(30);
    }

    function Footer()
    {
        date_default_timezone_set('America/El_Salvador');

        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->SetDrawColor(200, 200, 200);

        $this->Line(10, $this->GetY() - 2, 345, $this->GetY() - 2);

        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $hora = date('g:i A');

        $fechaFormateada = "Generado en Santa Ana, $dia de $mes de $anio a las $hora";

        $this->Cell(200, 6, convertirtexto($fechaFormateada), 0, 0, 'L');
        $this->Cell(145, 6, convertirtexto('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    function CellTruncada($w, $h, $txt, $border = 0, $ln = 0, $align = '', $fill = false)
    {
        $txt = convertirtexto($txt);
        $anchoDisponible = $w - 1.5;
        
        $fontSize = $this->FontSizePt;
        while ($this->GetStringWidth($txt) > $anchoDisponible && $fontSize > 5.5) {
            $fontSize -= 0.5;
            $this->SetFontSize($fontSize);
        }

        if ($this->GetStringWidth($txt) > $anchoDisponible) {
            while ($this->GetStringWidth($txt . '..') > $anchoDisponible && strlen($txt) > 0) {
                $txt = substr($txt, 0, -1);
            }
            $txt .= '..';
        }

        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill);
        $this->SetFontSize(8);
    }

    function ImprimirEncabezadosTabla($w)
    {
        $this->SetDrawColor(180, 180, 180);
        $this->SetLineWidth(.2);

        $this->SetFillColor(31, 78, 120);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 8);

        $w_estudiante = $w[0] + $w[1] + $w[2] + $w[3] + $w[4] + $w[5];
        $w_responsable = $w[6] + $w[7] + $w[8] + $w[9] + $w[10] + $w[11];
        $w_domicilio = $w[12] + $w[13];

        $this->Cell($w_estudiante, 5, convertirtexto('INFORMACIÓN DEL ESTUDIANTE'), 1, 0, 'C', true);
        $this->Cell($w_responsable, 5, convertirtexto('INFORMACIÓN DEL RESPONSABLE'), 1, 0, 'C', true);
        $this->Cell($w_domicilio, 5, convertirtexto('DOMICILIO Y DIRECCIÓN'), 1, 1, 'C', true);

        $this->SetFillColor(217, 226, 236);
        $this->SetTextColor(31, 78, 120);
        $this->SetFont('Arial', 'B', 7);

        $headers = ['Nº', 'NIE | ID', 'Nombre del Estudiante', 'F.Nac.', 'Partida (N/F/T/L)', 'D/M/D Nac.', 'DUI', 'Parentesco', 'Nombre Responsable', 'F.Nac.', 'D/M/D Resp.', 'Teléfono', 'D/M/D Dom.', 'Dirección de Domicilio'];

        for ($i = 0; $i < count($headers); $i++) {
            $this->Cell($w[$i], 5, convertirtexto($headers[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetTextColor(0, 0, 0);
    }
}

// --- Generación del Documento ---
$pdf = new PDF('L', 'mm', 'Legal');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->AliasNbPages();
$pdf->AddPage();

$w = array(7, 20, 48, 16, 24, 20, 18, 16, 45, 16, 20, 16, 20, 55);
$pdf->ImprimirEncabezadosTabla($w);

consultas(4, 0, $codigo_all, '', '', '', $db_link, '');
$stmtLoc = $db_link->prepare("SELECT nombre_departamento, nombre_municipio, nombre_distrito as descripcion FROM elsalvador WHERE codigo_municipio = :mun AND codigo_departamento = :dep");

function obtenerDMD($stmt, $mun, $dep) {
    if (empty($mun) || empty($dep)) return "-";
    $stmt->execute(['mun' => $mun, 'dep' => $dep]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return substr(trim($row["nombre_departamento"]), 0, 3) . '/' .
               substr(trim($row["nombre_municipio"]), 0, 3) . '/' .
               substr(trim($row["descripcion"]), 0, 3);
    }
    return "-";
}

$fill = false;
$i = 1;

// ACUMULADORES ESTADÍSTICOS
$conteoM = 0;
$conteoF = 0;
$edades = [];

while ($row = $result->fetch(PDO::FETCH_BOTH)) {
    if ($pdf->GetY() > 175) {
        $pdf->AddPage();
        $pdf->ImprimirEncabezadosTabla($w);
        $fill = false;
    }

    if ($fill) {
        $pdf->SetFillColor(217, 226, 236);
    } else {
        $pdf->SetFillColor(255, 255, 255);
    }

    $codigo_nie_y_id = trim((string)$row["codigo_nie"]) . ' | ' . trim((string)$row["id_alumno"]);
    $nombre_completo = cambiar_de_del((string)$row['nombre_completo_alumno']);
    $direccion = cambiar_de_del((string)$row['direccion_alumno']);
    $fecha_nacimiento = limpia_fecha(trim((string)$row['fecha_nacimiento']));

    // --- CONTEO GÉNERO Y EDAD ---
    $genero = strtoupper(trim((string)($row['genero'] ?? $row['sexo'] ?? 'M')));
    if ($genero == 'F' || $genero == 'FEMENINO') {
        $conteoF++;
    } else {
        $conteoM++;
    }

    if (!empty($fecha_nacimiento) && $fecha_nacimiento != '0000-00-00') {
        $fnDate = new DateTime($fecha_nacimiento);
        $hoy = new DateTime();
        $edadCalculada = $hoy->diff($fnDate)->y;
        $edades[$edadCalculada] = ($edades[$edadCalculada] ?? 0) + 1;
    }

    $pn_num = trim((string)($row['pn_numero'] ?? ''));
    $pn_fol = trim((string)($row['pn_folio'] ?? ''));
    $pn_tom = trim((string)($row['pn_tomo'] ?? ''));
    $pn_lib = trim((string)($row['pn_libro'] ?? ''));
    $datos_pn = ($pn_num != '') ? "$pn_num/$pn_fol/$pn_tom/$pn_lib" : "-";

    $dmd_nac = obtenerDMD($stmtLoc, trim((string)($row["codigo_municipio_pn"] ?? '')), trim((string)($row["codigo_departamento_pn"] ?? '')));
    $dmd_resp = obtenerDMD($stmtLoc, trim((string)($row["encargado_municipio"] ?? '')), trim((string)($row["encargado_departamento"] ?? '')));
    $dmd_dom = obtenerDMD($stmtLoc, trim((string)($row["codigo_municipio"] ?? '')), trim((string)($row["codigo_departamento"] ?? '')));

    $encargado_dui = trim((string)($row['encargado_dui'] ?? ''));
    $familiar = trim((string)($row['nombre_tipo_parentesco'] ?? ''));
    $nombre_encargado = cambiar_de_del((string)$row['nombres']);
    $encargado_fecha_nacimiento = limpia_fecha(trim((string)($row['encargado_fecha_nacimiento'] ?? '')));
    $telefono_encargado = trim((string)($row['telefono_encargado'] ?? ''));

    // Imprimir Fila
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w[0], 6, $i, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[1], 6, $codigo_nie_y_id, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[2], 6, $nombre_completo, 1, 0, 'L', $fill);
    $pdf->Cell($w[3], 6, limpia_fecha_formato($fecha_nacimiento), 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[4], 6, $datos_pn, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[5], 6, $dmd_nac, 1, 0, 'C', $fill);
    $pdf->Cell($w[6], 6, $encargado_dui, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[7], 6, $familiar, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[8], 6, $nombre_encargado, 1, 0, 'L', $fill);
    $pdf->Cell($w[9], 6, limpia_fecha_formato($encargado_fecha_nacimiento), 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[10], 6, $dmd_resp, 1, 0, 'C', $fill);
    $pdf->Cell($w[11], 6, $telefono_encargado, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[12], 6, $dmd_dom, 1, 0, 'C', $fill);
    $pdf->CellTruncada($w[13], 6, $direccion, 1, 0, 'L', $fill);

    $pdf->Ln();
    $fill = !$fill;
    $i++;
}

// --- TABLA RESUMEN ESTADÍSTICO AL FINAL ---
if ($pdf->GetY() > 140) {
    $pdf->AddPage();
} else {
    $pdf->Ln(4);
}

$totalAlumnos = $conteoM + $conteoF;
$totalAlumnos = $totalAlumnos > 0 ? $totalAlumnos : 1;

ksort($edades); // Ordenar las edades de menor a mayor
$textoEdades = [];
foreach ($edades as $edadKey => $cant) {
    $textoEdades[] = "$edadKey años: $cant";
}
$cadenaEdades = count($textoEdades) > 0 ? implode("  |  ", $textoEdades) : "Sin registro de fecha";

// Dibujar Cuadro Resumen
$pdf->SetFillColor(245, 247, 250);
$pdf->SetDrawColor(31, 78, 120);
$pdf->SetLineWidth(0.3);

$yEstadistica = $pdf->GetY();
$pdf->Rect(10, $yEstadistica, 335, 20, 'DF');

$pdf->SetFont('Arial', 'B', 8.5);
$pdf->SetTextColor(31, 78, 120);
$pdf->Text(13, $yEstadistica + 5, convertirtexto("RESUMEN ESTADÍSTICO DE SECCIÓN"));

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);

// Texto de Totales
$textoTotales = "Total Alumnos: " . ($i - 1) . "   ( Masculino: $conteoM  |  Femenino: $conteoF )";
$pdf->Text(13, $yEstadistica + 10, convertirtexto($textoTotales));

// Texto de Edades
$pdf->Text(13, $yEstadistica + 15, convertirtexto("Rango de Edades: " . $cadenaEdades));

// Barra de Porcentajes (% M / % F)
$porcM = round(($conteoM / $totalAlumnos) * 100, 1);
$porcF = round(($conteoF / $totalAlumnos) * 100, 1);

$xBarra = 240;
$yBarra = $yEstadistica + 4;
$anchoBarraMax = 95;

$anchoM = ($conteoM / $totalAlumnos) * $anchoBarraMax;
$anchoF = ($conteoF / $totalAlumnos) * $anchoBarraMax;

// Barra Azul Masculino
$pdf->SetFillColor(52, 152, 219);
if ($anchoM > 0) {
    $pdf->Rect($xBarra, $yBarra, $anchoM, 6, 'F');
}

// Barra Roja Femenino
$pdf->SetFillColor(231, 76, 60);
if ($anchoF > 0) {
    $pdf->Rect($xBarra + $anchoM, $yBarra, $anchoF, 6, 'F');
}

// % Textos sobre las barras
$pdf->SetFont('Arial', 'B', 7);
$pdf->SetTextColor(255, 255, 255);

if ($anchoM > 10) {
    $pdf->SetXY($xBarra, $yBarra + 1);
    $pdf->Cell($anchoM, 4, $porcM . '%', 0, 0, 'C');
}

if ($anchoF > 10) {
    $pdf->SetXY($xBarra + $anchoM, $yBarra + 1);
    $pdf->Cell($anchoF, 4, $porcF . '%', 0, 0, 'C');
}

// Funciones Auxiliares de Limpieza de Fechas
function limpia_fecha($f) {
    return trim((string)$f);
}
function limpia_fecha_formato($f) {
    if (empty($f) || $f == '0000-00-00') return '-';
    return date('d/m/Y', strtotime($f));
}

// Salida
$print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-DM.pdf';
$pdf->Output(convertirtexto($print_nombre), 'I');
?>