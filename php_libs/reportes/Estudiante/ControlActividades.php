<?php
// Define root path
define('ROOT_PATH', trim($_SERVER['DOCUMENT_ROOT']));

// Include necessary files
require_once ROOT_PATH . "/registro_academico/includes/funciones.php";
require_once ROOT_PATH . "/registro_academico/includes/consultas.php";
require_once ROOT_PATH . "/registro_academico/includes/mainFunctions_conexion.php";
require_once ROOT_PATH . "/registro_academico/php_libs/fpdf/fpdf.php";

// Cambiar a utf-8
header("Content-Type: text/html; charset=UTF-8");    

// Variables y consulta
$print_nombre = "";
$codigo_all = isset($_REQUEST["todos"]) ? $_REQUEST["todos"] : '';

if (empty($codigo_all)) {
    die("Error: 'todos' parameter is missing.");
}

$db_link = $dblink;
if (!$db_link) {
    die("Error: Database connection failed.");
}

// Buscar la consulta y ejecutarla
consultas(9, 0, $codigo_all, '', '', '', $db_link, '');

// Imprimir datos del bachillerato
while ($row = $result_encabezado->fetch(PDO::FETCH_BOTH)) {
    $print_bachillerato = convertirtexto('Modalidad: ' . trim($row['nombre_bachillerato']));
    $nombre_modalidad = convertirtexto(trim($row['nombre_bachillerato']));
    $print_grado = convertirtexto('Grado:     ' . trim($row['nombre_grado']));
    $nombre_grado = convertirtexto(trim($row['nombre_grado']));
    $print_seccion = convertirtexto('Sección:  ' . trim($row['nombre_seccion']));
    $nombre_seccion = convertirtexto(trim($row['nombre_seccion']));
    $print_ann_lectivo = convertirtexto('Año Lectivo: ' . trim($row['nombre_ann_lectivo']));
    $nombre_ann_lectivo = convertirtexto(trim($row['nombre_ann_lectivo']));
    $print_periodo = convertirtexto('Período: _____');
    $codigo_grado = trim($row['codigo_grado']);
    break;
}

class PDF extends FPDF
{
    // Rotar texto función Text()
    function RotatedText($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    // Cabecera de página
    function Header()
    {
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_periodo;

        // Logo
        $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($img)) {
            $this->Image($img, 10, 8, 12, 15);
        }

        // Títulos
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(31, 78, 120);
        $this->RotatedText(25, 12, convertirtexto($_SESSION['institucion'] ?? 'INSTITUCIÓN EDUCATIVA'), 0);
        $this->RotatedText(25, 17, 'Control de Actividades Evaluativas', 0);
        
        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', '', 8.5);

        // Cuadros de Información Docente/Asignatura
        $this->RoundedRect(24, 18, 133, 5, 1, '1234', '');
        $this->RotatedText(25, 21.8, $print_bachillerato, 0);

        $this->RoundedRect(24, 23.5, 133, 5, 1, '1234', '');
        $this->RotatedText(25, 27.2, 'Nombre Asignatura: ', 0);

        $this->RoundedRect(24, 29, 133, 5, 1, '1234', '');
        $this5 = $this->RotatedText(25, 32.7, 'Nombre Docente: ', 0);
        
        // Cuadro Lateral Grado/Sección/Año
        $this->RoundedRect(160, 8, 45, 26, 2, '1234', '');
        $this->SetFont('Arial', 'B', 8);
        $this->RotatedText(162, 13, $print_grado, 0);
        $this->RotatedText(162, 18, $print_seccion, 0);
        $this->RotatedText(162, 23, $print_ann_lectivo, 0);
        
        $this->SetFont('Arial', '', 8);
        $this->RotatedText(162, 28, $print_periodo, 0);

        // Encabezados de la Tabla / Cuadrícula
        $this->RoundedRect(10, 35, 195, 55, .5, '');     // Marco Principal
        $this->RoundedRect(10, 35, 6, 55, .5, '');      // Nº
        $this->RoundedRect(16, 35, 14, 55, .5, '');     // NIE
        $this->RoundedRect(30, 35, 70, 55, .5, '');     // Nombre
        
        $this->SetFont('Arial', 'B', 7.5);
        $this->RotatedText(14.5, 75, convertirtexto('Nº de Orden'), 90);
        $this->RotatedText(25.5, 75, convertirtexto('N I E'), 90);
        $this->RotatedText(45, 70, convertirtexto('Nombre de Alumnos/as (Orden Alfabético)'), 0);

        // Bloque Superior de Actividades y Porcentajes
        $this->RoundedRect(100, 35, 105, 5, .5, '');
        $this->RotatedText(128, 39, convertirtexto('PRUEBAS Y ACTIVIDADES REALIZADAS'), 0);

        $this->RoundedRect(100, 40, 105, 5, .5, '');
        $this->RotatedText(70, 44, convertirtexto('PORCENTAJES (%)'), 0);

        // Columnas Verticales de Actividades (11 columnas)
        $mov_izq = 100;
        $ancho_col = 9.54;
        for ($j = 0; $j < 11; $j++) {
            $this->RoundedRect($mov_izq, 40, $ancho_col, 50, .5, '');
            $mov_izq += $ancho_col;
        }

        $this->SetY(90);
        $this->SetFillColor(245, 247, 250);
        $this->SetTextColor(0);
    }

    // Pie de página con Hora Local Salvadoreña
    function Footer()
    {
        date_default_timezone_set('America/El_Salvador');

        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->SetDrawColor(200, 200, 200);

        $this->Line(10, $this->GetY() - 2, 205, $this->GetY() - 2);

        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $hora = date('g:i A');

        $fechaFormateada = "Generado en Santa Ana, $dia de $mes de $anio a las $hora";

        $this->Cell(130, 6, convertirtexto($fechaFormateada), 0, 0, 'L');
        $this->Cell(65, 6, convertirtexto('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }
}

// --- Generación del Documento ---
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 15);
$pdf->SetTitle("Control de Actividades: " . $codigo_grado . $nombre_seccion);
$pdf->SetSubject("Estudiantes");
$pdf->AliasNbPages();
$pdf->AddPage();

// Consulta de Alumnos
consultas(4, 0, $codigo_all, '', '', '', $db_link, '');
// --- Generación de Celdas y Filas ---
$w = array(6, 14, 70, 9.54); // Anchos: N°, NIE, Nombre, 11 Celdas de Actividades

$fill = false;
$i = 0;

while ($row = $result->fetch(PDO::FETCH_BOTH)) {
    $i++;
    $codigo_nie = trim($row['codigo_nie']);
    $apellido_alumno = trim($row['apellido_alumno']);

    // Configurar color de relleno suave cuando $fill es true
    if ($fill) {
        $pdf->SetFillColor(217, 226, 245); // Azul/Gris muy suave y legible
    } else {
        $pdf->SetFillColor(255, 255, 255); // Blanco
    }

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w[0], 6.8, $i, 1, 0, 'C', true);

    $pdf->SetFont('Arial', '', 7.5);
    $pdf->Cell($w[1], 6.8, $codigo_nie, 1, 0, 'C', true);

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell($w[2], 6.8, convertirtexto($apellido_alumno), 1, 0, 'L', true);

    // 11 Celdas de evaluación/actividad
    for ($j = 0; $j < 11; $j++) {
        $pdf->Cell($w[3], 6.8, '', 1, 0, 'C', true);
    }

    $pdf->Ln();
    $fill = !$fill; // Alternar estado

    // Salto de página a los 25 alumnos
    if ($i % 25 == 0) {
        $pdf->AddPage();
        $fill = false; // Reiniciar en blanco al inicio de nueva página
    }
}

// Rellenar filas vacías para completar la tabla estética (hasta 25 filas)
$lineas_restantes = 25 - ($i % 25);
if ($lineas_restantes < 25 && $lineas_restantes > 0) {
    for ($k = 0; $k < $lineas_restantes; $k++) {
        $i++;

        if ($fill) {
            $pdf->SetFillColor(217, 226, 245);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell($w[0], 6.8, $i, 1, 0, 'C', true);
        $pdf->Cell($w[1], 6.8, '', 1, 0, 'C', true);
        $pdf->Cell($w[2], 6.8, '', 1, 0, 'L', true);

        for ($j = 0; $j < 11; $j++) {
            $pdf->Cell($w[3], 6.8, '', 1, 0, 'C', true);
        }

        $pdf->Ln();
        $fill = !$fill;
    }
}

// Salida en el navegador
$modo = 'I';
$print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-CA.pdf';
$pdf->Output(convertirtexto($print_nombre), $modo);