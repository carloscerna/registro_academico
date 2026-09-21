<?php
// Ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Archivos que se incluyen
include_once($path_root . "/registro_academico/includes/funciones.php");
include_once($path_root . "/registro_academico/includes/consultas.php");
include_once($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

// Llamar a la librería FPDF
include_once($path_root . "/registro_academico/php_libs/fpdf/fpdf.php");

// Cambiar cabecera
header("Content-Type: text/html; charset=UTF-8");

// Variables sanitizadas para PHP 8
$fecha_desde = $_REQUEST['fecha_desde'] ?? '';
$fecha_hasta = $_REQUEST['fecha_hasta'] ?? '';
$nombre_ann_lectivo = !empty($fecha_desde) ? substr($fecha_desde, 0, 4) : date('Y');
$codigo_contratacion = $_REQUEST['codigo_contratacion'] ?? '';
$codigo_turno = $_REQUEST['codigo_turno'] ?? '';
$codigo_contratacion_turno = trim($codigo_contratacion . $codigo_turno);
$db_link = $dblink;

// Obtener código de la institución desde sesión o consulta general
$codigo_institucion = $_SESSION['codigo_institucion'] ?? $_SESSION['codigo'] ?? '';

// Calcular el Disponible según Tipo de Contratación
$calculo_horas = ($codigo_contratacion === "05") ? 8 : 5; // 05 = PAGADOS POR EL CDE

// ============================================================================================
// 1. CARGA DE DATOS EN MEMORIA (CONSULTAS PREPARADAS PARA PHP 8 + PDO)
// ============================================================================================

// Listado de Personal
$query_nombres_personal = "SELECT ps.codigo_personal, p.nombres, p.apellidos, 
            btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_c
            FROM personal_salario ps
            INNER JOIN personal p ON p.id_personal = ps.codigo_personal
            WHERE p.codigo_estatus = '01' 
            AND btrim(ps.codigo_tipo_contratacion || ps.codigo_turno) = :contratacion_turno
            ORDER BY nombre_c";

$stmt_nombres = $dblink->prepare($query_nombres_personal);
$stmt_nombres->execute([':contratacion_turno' => $codigo_contratacion_turno]);
$personal_data = $stmt_nombres->fetchAll(PDO::FETCH_ASSOC);

// Consulta Masiva de Licencias
$matriz_licencias = array();
$query_sum_global = "SELECT codigo_personal, codigo_licencia_permiso, sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos 
                 FROM personal_licencias_permisos 
                 WHERE fecha >= :fecha_desde AND fecha <= :fecha_hasta 
                 AND codigo_contratacion = :codigo_contratacion 
                 AND codigo_turno = :codigo_turno
                 GROUP BY codigo_personal, codigo_licencia_permiso";

$stmt_sum = $dblink->prepare($query_sum_global);
$stmt_sum->execute([
    ':fecha_desde' => $fecha_desde,
    ':fecha_hasta' => $fecha_hasta,
    ':codigo_contratacion' => $codigo_contratacion,
    ':codigo_turno' => $codigo_turno
]);

while ($row = $stmt_sum->fetch(PDO::FETCH_ASSOC)) {
    $matriz_licencias[$row['codigo_personal']][$row['codigo_licencia_permiso']] = $row;
}

// Obtener info encabezado
$query_info = "SELECT tur.nombre as nombre_turno, tc.nombre as nombre_contratacion 
               FROM turno tur, tipo_contratacion tc 
               WHERE tur.codigo = :codigo_turno AND tc.codigo = :codigo_contratacion";

$stmt_info = $dblink->prepare($query_info);
$stmt_info->execute([
    ':codigo_turno' => $codigo_turno,
    ':codigo_contratacion' => $codigo_contratacion
]);
$info_header = $stmt_info->fetch(PDO::FETCH_ASSOC) ?: ['nombre_turno' => '', 'nombre_contratacion' => ''];
$nombre_turno = trim($info_header['nombre_turno']);
$nombre_contratacion = trim($info_header['nombre_contratacion']);

// Determinar el/los mes(es) del informe para el pie de página
$meses_nombre = [
    '01' => 'ENERO', '02' => 'FEBRERO', '03' => 'MARZO', '04' => 'ABRIL',
    '05' => 'MAYO', '06' => 'JUNIO', '07' => 'JULIO', '08' => 'AGOSTO',
    '09' => 'SEPTIEMBRE', '10' => 'OCTUBRE', '11' => 'NOVIEMBRE', '12' => 'DICIEMBRE'
];

$mes_ini = !empty($fecha_desde) ? substr($fecha_desde, 5, 2) : '';
$mes_fin = !empty($fecha_hasta) ? substr($fecha_hasta, 5, 2) : '';

if ($mes_ini !== '' && $mes_fin !== '') {
    if ($mes_ini === $mes_fin) {
        $texto_mes = "MES DE " . ($meses_nombre[$mes_ini] ?? '');
    } else {
        $texto_mes = "PERIODO DE " . ($meses_nombre[$mes_ini] ?? '') . " A " . ($meses_nombre[$mes_fin] ?? '');
    }
} else {
    $texto_mes = "PERIODO ANUAL " . $nombre_ann_lectivo;
}

// Guardar en globales para el PDF
$GLOBALS['nombre_ann_lectivo'] = $nombre_ann_lectivo;
$GLOBALS['nombre_turno']       = $nombre_turno;
$GLOBALS['nombre_contratacion']= $nombre_contratacion;
$GLOBALS['codigo_institucion'] = $codigo_institucion;
$GLOBALS['texto_mes']          = $texto_mes;
$GLOBALS['fecha_desde']        = $fecha_desde;
$GLOBALS['fecha_hasta']        = $fecha_hasta;

// ============================================================================================
// CONFIGURACIÓN DE CÓDIGOS DE LICENCIAS
// ============================================================================================
$COD_90_DIAS_GOCE     = '02'; // Enfermedad / Incapacidad con goce
$COD_SIN_GOCE_SUELDO  = '09'; // Enfermedad sin goce
$COD_20_DIAS_PARIENTE = '03'; // Duelo o pariente
$COD_5_DIAS_PERSONAL  = '04'; // Motivos personales con goce
$COD_60_DIAS_SIN_GOCE = '06'; // Motivos personales sin goce
$COD_MATERNIDAD       = '05'; // Maternidad
$COD_LLEGADAS_TARDIAS = '07'; // Llegadas tardías
$COD_INJUSTIFICADA    = '08'; // Inasistencia sin justificar

// Función helper local para evitar conversiones deprecadas
function convertEncoding($str) {
    return mb_convert_encoding($str ?? '', 'ISO-8859-1', 'UTF-8');
}

// ============================================================================================
// CLASE PDF
// ============================================================================================
class PDF extends FPDF
{
    function Header()
    {
        $nombre_ann_lectivo = $GLOBALS['nombre_ann_lectivo'];
        $nombre_turno       = $GLOBALS['nombre_turno'];
        $codigo_institucion = $GLOBALS['codigo_institucion'];

        // --- 1. LOGOS Y TÍTULOS ---
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'MINISTERIO DE EDUCACION', 0, 1, 'C');
        $this->Cell(0, 5, 'DIRECCION DEPARTAMENTAL DE EDUCACION DE SANTA ANA', 0, 1, 'C');
        $this->Cell(0, 5, 'CONTROL ANUAL DE ASISTENCIAS Y PERMISOS DEL PERSONAL DOCENTE', 0, 1, 'C');

        $this->Ln(2);

        // --- 2. DATOS INSTITUCIONALES ---
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(35, 5, convertEncoding('CÓDIGO'), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(100, 5, ': ' . $codigo_institucion, 0, 1, 'L');

        $this->SetFont('Arial', 'B', 8);
        $this->Cell(35, 5, 'CENTRO EDUCATIVO', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(100, 5, ': ' . convertEncoding($_SESSION['institucion'] ?? $_SESSION['nombre_institucion'] ?? ''), 0, 1, 'L');

        $this->SetFont('Arial', 'B', 8);
        $this->Cell(35, 5, 'TURNO', 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(100, 5, ': ' . convertEncoding($nombre_turno), 0, 0, 'L');

        $this->SetX(230);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(20, 5, convertEncoding('AÑO: ' . $nombre_ann_lectivo), 0, 1, 'L');

        // Logo
        $img = ($_SESSION['path_root'] ?? '') . '/registro_academico/img/logo_mined.png';
        if (file_exists($img)) {
            $this->Image($img, 240, 10, 25);
        }

        $this->Ln(2);

        // --- 3. CONFIGURACIÓN DE DIMENSIONES Y TABLA ---
        $this->SetFillColor(255, 255, 255);
        $this->SetLineWidth(0.2);
        $this->SetFont('Arial', 'B', 6);

        $x = $this->GetX();
        $y = $this->GetY();

        $w_no  = 8;
        $w_nom = 85;

        $w_g1 = 20;
        $w_g2 = 20;
        $w_g3 = 15;
        $w_g4 = 24;
        $w_g5 = 24;
        $w_g6 = 14;
        $w_g7 = 16;
        $w_g8 = 24;

        $GLOBALS['W_COLS'] = array($w_no, $w_nom, $w_g1, $w_g2, $w_g3, $w_g4, $w_g5, $w_g6, $w_g7, $w_g8);

        $h1 = 8;
        $h2 = 18;
        $h3 = 5;
        $h_total = $h1 + $h2 + $h3;

        // Columnas principales
        $this->Rect($x, $y, $w_no, $h_total);
        $this->SetXY($x, $y + ($h_total / 2) - 2);
        $this->Cell($w_no, 4, 'No', 0, 0, 'C');

        $this->Rect($x + $w_no, $y, $w_nom, $h_total);
        $this->SetXY($x + $w_no, $y + ($h_total / 2) - 2);
        $this->Cell($w_nom, 4, 'NOMBRE DEL DOCENTE', 0, 0, 'C');

        $curX = $x + $w_no + $w_nom;

        // Fila 1: Títulos
        $this->Rect($curX, $y, $w_g1, $h1); $this->SetXY($curX, $y);
        $this->MultiCell($w_g1, 3, "CON GOCE DE\nSUELDO 90 DIAS", 0, 'C'); $curX += $w_g1;

        $this->Rect($curX, $y, $w_g2, $h1); $this->SetXY($curX, $y);
        $this->MultiCell($w_g2, 3, "SIN GOCE DE\nSUELDO", 0, 'C'); $curX += $w_g2;

        $this->Rect($curX, $y, $w_g3, $h1); $this->SetXY($curX, $y);
        $this->MultiCell($w_g3, 3, "20 DIAS\nCON GOCE", 0, 'C'); $curX += $w_g3;

        $this->Rect($curX, $y, $w_g4, $h1); $this->SetXY($curX, $y);
        $this->MultiCell($w_g4, 3, "5 DIAS CON GOCE", 0, 'C'); $curX += $w_g4;

        $this->Rect($curX, $y, $w_g5, $h1); $this->SetXY($curX, $y);
        $this->MultiCell($w_g5, 3, "60 DIAS SIN GOCE", 0, 'C'); $curX += $w_g5;

        $this->Rect($curX, $y, $w_g6, $h1); $this->SetXY($curX, $y);
        $this->MultiCell($w_g6, 3, "112 DIAS\nCON GOCE", 0, 'C'); $curX += $w_g6;

        // Fila 2: Descripciones
        $this->SetY($y + $h1);
        $curX = $x + $w_no + $w_nom;
        $this->SetFont('Arial', '', 6);

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g1, 3, "Enfermedad con\nCertificado\nMedico e\nIncapacidades\nMedicas con\ngoce", 1, 'C');
        $curX += $w_g1;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g2, 3, "Enfermedad con\nCertificado\nMedico e\nIncapacidades\nMedicas sin\ngoce", 1, 'C');
        $curX += $w_g2;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g3, 3, "Enfermedad\nde \nPariente\nCercano \no\nDuelo", 1, 'C');
        $curX += $w_g3;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g4, 4.5, "Permiso \npor motivos\nPersonales \ncon goce", 1, 'C');
        $curX += $w_g4;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g5, 4.5, "Permiso \npor motivos\nPersonales \nSin goce", 1, 'C');
        $curX += $w_g5;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g6, 4.5, "\nMATERNI\nDAD 112\nDIAS", 1, 'C');
        $curX += $w_g6;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g7, 6, "\nLLEGADAS\nTARDIAS", 1, 'C');
        $this->Rect($curX, $y, $w_g7, $h1 + $h2);
        $curX += $w_g7;

        $this->SetXY($curX, $y + $h1);
        $this->MultiCell($w_g8, 6, "\nINASISTENCIA SIN\nJUSTIFICAR", 1, 'C');
        $this->Rect($curX, $y, $w_g8, $h1 + $h2);

        // Fila 3: Unidades
        $y_units = $y + $h1 + $h2;
        $curX = $x + $w_no + $w_nom;

        $this->SetFont('Arial', 'B', 6);
        $this->SetXY($curX, $y_units);

        $this->Cell($w_g1 / 2, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g1 / 2, $h3, 'HORAS', 1, 0, 'C');
        $this->Cell($w_g2 / 2, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g2 / 2, $h3, 'HORAS', 1, 0, 'C');
        $this->Cell($w_g3, $h3, 'DIAS', 1, 0, 'C');
        $this->Cell($w_g4 / 3, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g4 / 3, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g4 / 3, $h3, 'MIN', 1, 0, 'C');
        $this->Cell($w_g5 / 3, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g5 / 3, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g5 / 3, $h3, 'MIN', 1, 0, 'C');
        $this->Cell($w_g6, $h3, 'DIAS', 1, 0, 'C');
        $this->Cell($w_g7 / 2, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g7 / 2, $h3, 'MIN', 1, 0, 'C');
        $this->Cell($w_g8 / 3, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g8 / 3, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g8 / 3, $h3, 'MIN', 1, 0, 'C');

        $this->Ln($h3);
    }

    function Footer()
    {
        $texto_mes   = $GLOBALS['texto_mes'];
        $fecha_desde = $GLOBALS['fecha_desde'];
        $fecha_hasta = $GLOBALS['fecha_hasta'];

        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        
        // Imprimir el mes/rango a la izquierda y número de página a la derecha
        $rango_fechas = !empty($fecha_desde) && !empty($fecha_hasta) ? " ($fecha_desde al $fecha_hasta)" : "";
        $this->Cell(130, 10, convertEncoding("INFORME CORRESPONDIENTE AL " . $texto_mes . $rango_fechas), 0, 0, 'L');
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
    }
}

// ============================================================================================
// GENERACIÓN DEL REPORTE EN PDF
// ============================================================================================

$pdf = new PDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);

$num = 1;
$w = $GLOBALS['W_COLS'];

// Función auxiliar compatible con PHP 8
function getVals($matriz, $id_p, $cod_lic, $calc_h)
{
    $d = 0; $h = 0; $m = 0;
    if (isset($matriz[$id_p][$cod_lic])) {
        $row = $matriz[$id_p][$cod_lic];
        $d = (int)($row['dia'] ?? 0);
        $h = (int)($row['hora'] ?? 0);
        $m = (int)($row['minutos'] ?? 0);
    }

    $total_min = ($d * $calc_h * 60) + ($h * 60) + $m;

    if ($total_min === 0) return array('', '', '');

    $res_d = segundosToCadenaD($total_min, $calc_h);
    $res_h = segundosToCadenaH($total_min, $calc_h);
    $res_m = segundosToCadenaM($total_min, $calc_h);

    return array(
        ($res_d == 0) ? '' : $res_d,
        ($res_h == 0) ? '' : $res_h,
        ($res_m == 0) ? '' : $res_m
    );
}

foreach ($personal_data as $docente) {
    $id_p   = $docente['codigo_personal'];
    $nombre = $docente['nombre_c'];

    // Valores calculados
    $val_90  = getVals($matriz_licencias, $id_p, $COD_90_DIAS_GOCE, $calculo_horas);
    $val_sin = getVals($matriz_licencias, $id_p, $COD_SIN_GOCE_SUELDO, $calculo_horas);
    $val_20  = getVals($matriz_licencias, $id_p, $COD_20_DIAS_PARIENTE, $calculo_horas);
    $val_5   = getVals($matriz_licencias, $id_p, $COD_5_DIAS_PERSONAL, $calculo_horas);
    $val_60  = getVals($matriz_licencias, $id_p, $COD_60_DIAS_SIN_GOCE, $calculo_horas);
    $val_mat = getVals($matriz_licencias, $id_p, $COD_MATERNIDAD, $calculo_horas);
    $val_tar = getVals($matriz_licencias, $id_p, $COD_LLEGADAS_TARDIAS, $calculo_horas);
    $val_inj = getVals($matriz_licencias, $id_p, $COD_INJUSTIFICADA, $calculo_horas);

    // IMPRIMIR FILA
    $pdf->Cell($w[0], 6, $num, 1, 0, 'C');
    $pdf->Cell($w[1], 6, convertEncoding($nombre), 1, 0, 'L');

    // 90 Días
    $sub = $w[2] / 2;
    $pdf->Cell($sub, 6, $val_90[0], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_90[1], 1, 0, 'C');

    // Sin Goce
    $sub = $w[3] / 2;
    $pdf->Cell($sub, 6, $val_sin[0], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_sin[1], 1, 0, 'C');

    // 20 Días
    $pdf->Cell($w[4], 6, $val_20[0], 1, 0, 'C');

    // 5 Días
    $sub = $w[5] / 3;
    $pdf->Cell($sub, 6, $val_5[0], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_5[1], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_5[2], 1, 0, 'C');

    // 60 Días
    $sub = $w[6] / 3;
    $pdf->Cell($sub, 6, $val_60[0], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_60[1], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_60[2], 1, 0, 'C');

    // Maternidad
    $pdf->Cell($w[7], 6, $val_mat[0], 1, 0, 'C');

    // Tardías (H, M)
    $sub = $w[8] / 2;
    $pdf->Cell($sub, 6, $val_tar[1], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_tar[2], 1, 0, 'C');

    // Injustificadas (D, H, M)
    $sub = $w[9] / 3;
    $pdf->Cell($sub, 6, $val_inj[0], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_inj[1], 1, 0, 'C');
    $pdf->Cell($sub, 6, $val_inj[2], 1, 0, 'C');

    $pdf->Ln();
    $num++;
}

// ============================================================================================
// SECCIÓN DE FIRMAS
// ============================================================================================
if ($pdf->GetY() > 160) {
    $pdf->AddPage();
}

$pdf->Ln(25);
$y_sig = $pdf->GetY();
$pdf->SetFont('Arial', '', 10);

// Firma Director
$x_left = 30;
$w_sig  = 80;
$pdf->SetXY($x_left, $y_sig);
$pdf->Cell(5, 5, 'F', 0, 0, 'L');
$pdf->Line($x_left + 5, $y_sig + 4, $x_left + $w_sig, $y_sig + 4);

$pdf->SetXY($x_left, $y_sig + 6);
$pdf->Cell($w_sig, 5, 'Director:__________________________________', 0, 1, 'L');
$pdf->SetX($x_left);
$pdf->Cell($w_sig, 5, 'Telefono:__________________________________', 0, 1, 'L');

// Firma Subdirector
$x_right = 160;
$pdf->SetXY($x_right, $y_sig);
$pdf->Cell(5, 5, 'F', 0, 0, 'L');
$pdf->Line($x_right + 5, $y_sig + 4, $x_right + $w_sig, $y_sig + 4);

$pdf->SetXY($x_right, $y_sig + 6);
$pdf->Cell($w_sig, 5, 'Subdirector:________________________________', 0, 1, 'L');
$pdf->SetX($x_right);
$pdf->Cell($w_sig, 5, 'Telefono:___________________________________', 0, 1, 'L');

$pdf->Output();
?>