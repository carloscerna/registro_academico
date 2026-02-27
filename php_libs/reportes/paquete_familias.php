<?php
// Ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Archivos que se incluyen
include($path_root . "/registro_academico/includes/funciones.php");
include($path_root . "/registro_academico/includes/consultas.php");
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
include($path_root . "/registro_academico/php_libs/fpdf/fpdf.php");

// Cabecera de tipo de contenido
header("Content-Type: text/html; charset=UTF-8");    

// Variables y limpieza de datos para PHP 8.x
$codigo_all = $_REQUEST["todos"] ?? "";
$codigo_ann_lectivo = substr((string)$codigo_all, 6, 2);
$fechapaquete = cambiaf_a_normal($_REQUEST["fechapaquete"] ?? "");
$chkfechaPaquete = $_REQUEST["chkfechaPaquete"] ?? "";
$chkNIEPaquete = $_REQUEST["chkNIEPaquete"] ?? "";

// Reemplazo de utf8_decode por mb_convert_encoding
$rubro_raw = trim((string)($_REQUEST["rubro"] ?? ""));
$rubro = mb_convert_encoding($rubro_raw, 'ISO-8859-1', 'UTF-8');

$db_link = $dblink;
$por_genero = true;

// Ejecutar consulta de encabezado
consultas(16, 0, $codigo_all, '', '', '', $db_link, '');

// Inicialización de variables para evitar Warnings
$nombre_ann_lectivo = "";
$nombre_grado = "";
$nombre_seccion = "";
$porciones = [];
$codigo_grado = "";

if ($result_encabezado) {
    while ($row = $result_encabezado->fetch(PDO::FETCH_BOTH)) {
        $nombre_ann_lectivo = trim((string)$row['nombre_ann_lectivo']);
        $nombre_grado = trim((string)$row['nombre_grado']);
        $nombre_seccion = trim((string)$row['nombre_seccion']);
        $nombre_bach_raw = trim((string)$row['nombre_bachillerato']);
        $nombre_bachillerato = mb_convert_encoding($nombre_bach_raw, 'ISO-8859-1', 'UTF-8');
        $porciones = explode(" ", $nombre_bachillerato);
        $codigo_grado = trim((string)$row['codigo_grado']);
        break;
    }
}

class PDF extends FPDF
{
    function Header()
    {
        global $nombre_ann_lectivo, $nombre_grado, $nombre_seccion, $fechapaquete, $rubro, $chkfechaPaquete, $porciones, $codigo_grado;

        $this->SetFont('Arial', 'B', 11);
        $titulo = mb_convert_encoding('PROGRAMA DE DOTACIÓN DE PAQUETES ESCOLARES AÑO ' . $nombre_ann_lectivo, 'ISO-8859-1', 'UTF-8');
        $this->RotatedText(75, 15, $titulo, 0);

        $this->SetFont('Arial', '', 10);
        $this->RotatedText(20, 20, 'RUBRO: ', 0);
        $this->RotatedText(20, 25, 'FECHA: ', 0);
        $this->RotatedText(20, 30, mb_convert_encoding('CÓDIGO DEL C.E.: ', 'ISO-8859-1', 'UTF-8'), 0);
        $this->RotatedText(20, 35, mb_convert_encoding('NOMBRE DEL C.E.: ', 'ISO-8859-1', 'UTF-8'), 0);

        $this->SetFont('Arial', 'B', 10);
        $this->RotatedText(40, 20, $rubro, 0);
        
        if ($chkfechaPaquete == "yes") {
            $this->RotatedText(40, 25, $fechapaquete, 0);  
        } else {
            $this->RotatedText(40, 25, "_____________________________", 0);  
        }
        
        $inst = mb_convert_encoding(($_SESSION['institucion'] ?? ""), 'ISO-8859-1', 'UTF-8');
        $this->RotatedText(55, 30, ($_SESSION['codigo'] ?? ""), 0);
        $this->RotatedText(55, 35, $inst, 0);

        $this->SetFont('Arial', '', 10);
        $this->RotatedText(200, 20, 'DEPARTAMENTO: ', 0);
        $this->RotatedText(200, 25, 'MUNICIPIO: ', 0);
        $this->RotatedText(200, 30, 'GRADO: ', 0);       
        $this->RotatedText(200, 35, mb_convert_encoding('SECCIÓN: ', 'ISO-8859-1', 'UTF-8'), 0);
        
        $this->SetFont('Arial', 'B', 10);
        $dep = mb_convert_encoding(($_SESSION['nombre_departamento'] ?? ""), 'ISO-8859-1', 'UTF-8');
        $mun = mb_convert_encoding(($_SESSION['nombre_municipio'] ?? ""), 'ISO-8859-1', 'UTF-8');
        $this->RotatedText(232, 20, $dep, 0);
        $this->RotatedText(227, 25, $mun, 0);

        $grad_format = mb_convert_encoding($nombre_grado, 'ISO-8859-1', 'UTF-8');
        if (in_array($codigo_grado, ['4P', '5P', '6P']) || ($codigo_grado >= '01' && $codigo_grado <= '09')) {
            $this->RotatedText(220, 30, $grad_format, 0);  
        } else {
            $ext = $porciones[1] ?? "";
            $this->RotatedText(220, 30, $grad_format . " - " . $ext, 0);     
        }
        
        $this->RotatedText(220, 35, $nombre_seccion, 0);

        $this->SetY(37);
        $this->SetX(10);
        $leyenda = "El padre, madre, estudiante o responsable del estudiante quién suscribe, garantiza que: a) los bienes serán exclusivamente para uso del estudiante al que está destinado, b) serán utilizados para asistir y permanecer en el Centro Educativo durante el año lectivo (Art. 56 de la Constitución de la República y Art. 87 de la LEPINA):";
        $this->MultiCell(253, 4, mb_convert_encoding($leyenda, 'ISO-8859-1', 'UTF-8'), 0, 'J');
    }

    function FancyTable($header)
    {
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', 'B', 8);
        $w = array(5, 75, 12, 15, 80, 25, 45);
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, mb_convert_encoding($header[$i], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', 1);
        }
        $this->Ln();
    }
}

// Configuración del PDF
$pdf = new PDF('L', 'mm', 'Letter');
$pdf->SetMargins(10, 20, 5);
$pdf->SetAutoPageBreak(true, 10);

// Lógica de encabezados según rubro
if ($rubro_raw === "Paquete de Útiles Escolares") {
    $header = array('Nº', 'NOMBRE DEL ESTUDIANTE', 'Gén.', 'CICLO', 'Nombre del Responsable', 'No. DUI/NIE', 'FIRMA');  
} elseif ($rubro_raw === "Familias") {
    $header = array('Nº', 'NOMBRE DEL ESTUDIANTE', 'Gén.', 'Hermano', 'Nombre del Responsable', 'No. DUI/NIE', 'FIRMA');
} else {
    $header = array('Nº', 'NOMBRE DEL ESTUDIANTE', 'Gén.', 'TALLA', 'Nombre del Responsable', 'No. DUI/NIE', 'FIRMA');
}

$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetY(50);
$pdf->SetX(10);

// Query principal optimizado
$query_listado_completo = "SELECT a.id_alumno, a.codigo_nie, 
    btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
    translate(btrim(a.apellido_paterno || ' ' || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') as sin_tilde
    FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
    WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '$codigo_all'
    ORDER BY sin_tilde ASC";

$result_ = $dblink->query($query_listado_completo);
$sin_tilde = [];
while ($row_r = $result_->fetch(PDO::FETCH_BOTH)) {
    $sin_tilde[] = trim((string)$row_r['sin_tilde']);
}  

$solo_apellidos = array_values(array_unique($sin_tilde));
$pdf->FancyTable($header);

$w = array(5, 75, 6, 6, 15, 80, 25, 45); 
$alto = 9;
$fill = false; 
$i = 1; 
$num = 0;

foreach ($solo_apellidos as $apellido_busqueda) {
    $query_hermanos = "SELECT a.codigo_nie, a.genero, 
        btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
        am.codigo_grado, sec.nombre as nombre_seccion, ae.nombres as nombre_encargado, ae.dui
        FROM alumno a 
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't' 
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' 
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
        WHERE am.codigo_ann_lectivo = '$codigo_ann_lectivo' 
        AND translate(btrim(a.apellido_paterno || ' ' || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') = '$apellido_busqueda' 
        ORDER BY apellido_alumno ASC";

    $res_h = $dblink->query($query_hermanos);
    $total_h = $res_h->rowCount();

    if ($total_h > 0) {
        $count_h = 0;
        while ($row = $res_h->fetch(PDO::FETCH_BOTH)) {
            $pdf->SetFillColor($total_h > 1 ? 179 : 255, $total_h > 1 ? 226 : 255, $total_h > 1 ? 255 : 255);
            $fill = ($total_h > 1);

            $count_h++;
            $correlativo = ($count_h == 1) ? ++$num : '';
            
            $pdf->Cell($w[0], $alto, $correlativo, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], $alto, mb_convert_encoding($row['apellido_alumno'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', $fill);
            
            $gen = strtolower(trim((string)$row['genero']));
            $pdf->Cell($w[2], $alto, ($gen == 'm' ? 'M' : ''), 1, 0, 'C', $fill);
            $pdf->Cell($w[3], $alto, ($gen == 'f' ? 'F' : ''), 1, 0, 'C', $fill);

            $pdf->Cell($w[4], $alto, trim($row['codigo_grado']) . "-" . trim($row['nombre_seccion']), 1, 0, 'C', $fill);
            $pdf->Cell($w[5], $alto, mb_convert_encoding(trim($row['nombre_encargado']), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L', $fill);
            
            $doc = ($chkNIEPaquete == "yes") ? $row['codigo_nie'] : $row['dui'];
            $pdf->Cell($w[6], $alto, trim((string)$doc), 1, 0, 'C', $fill);
            $pdf->Cell($w[7], $alto, '', 1, 0, 'C', $fill);
            
            $pdf->Ln();

            if ($i % 15 == 0) {
                $pdf->AddPage();
                $pdf->SetY(50);
                $pdf->SetX(10);
                $pdf->FancyTable($header);
            }
            $i++;
        }
    }
}

// Rellenar con filas vacías si es necesario
$restantes = 15 - (($i - 1) % 15);
if ($restantes < 15) {
    for ($j = 0; $j < $restantes; $j++) {
        $pdf->Cell($w[0], $alto, '', 1, 0, 'C', false);
        $pdf->Cell($w[1], $alto, '', 1, 0, 'L', false);
        $pdf->Cell($w[2], $alto, '', 1, 0, 'C', false);
        $pdf->Cell($w[3], $alto, '', 1, 0, 'C', false);
        $pdf->Cell($w[4], $alto, '', 1, 0, 'C', false);
        $pdf->Cell($w[5], $alto, '', 1, 0, 'L', false);
        $pdf->Cell($w[6], $alto, '', 1, 0, 'C', false);
        $pdf->Cell($w[7], $alto, '', 1, 0, 'C', false);
        $pdf->Ln();
    }
}

$pdf->Output();
?>