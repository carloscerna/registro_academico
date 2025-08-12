<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Archivos que se incluyen.
     include($path_root."/registro_academico/includes/funciones.php");
     include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
     require($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
     header("Content-Type: text/html; charset=UTF-8");    

// Conexión a la base de datos.
     $db_link = $dblink;
// Obtener el año lectivo desde el request.
    $codigo_ann_lectivo = $_REQUEST["ann_lectivo"];

// Consulta SQL para la tabla principal de estudiantes.
$query_estudiantes = "
    SELECT
        ae.dui AS dui_encargado,
        TRIM(ae.nombres) AS nombre_encargado,
        TRIM(a.nombre_completo || ' ' || a.apellido_paterno || ' ' || a.apellido_materno) AS nombre_estudiante,
        TRIM(g.nombre) || ' ' || TRIM(s.nombre) AS grado_seccion,
        a.codigo_genero
    FROM
        public.alumno_encargado ae
    INNER JOIN
        public.alumno a ON a.id_alumno = ae.codigo_alumno
    INNER JOIN
        public.alumno_matricula am ON am.codigo_alumno = a.id_alumno
    INNER JOIN
        public.grado_ano g ON g.codigo = am.codigo_grado
    INNER JOIN
        public.seccion s ON s.codigo = am.codigo_seccion
    WHERE
        am.codigo_ann_lectivo = :codigo_ann_lectivo
        AND am.retirado = 'f'
        AND ae.encargado = 't'
        AND ae.dui IS NOT NULL AND ae.dui != ''
    ORDER BY
        nombre_encargado, nombre_estudiante
";

$stmt_estudiantes = $db_link->prepare($query_estudiantes);
$stmt_estudiantes->bindParam(':codigo_ann_lectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
$stmt_estudiantes->execute();
$familia_data = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);

// Agrupar estudiantes y contar totales para el primer resumen
$familias_agrupadas = [];
$total_estudiantes = 0;
$total_masculino = 0;
$total_femenino = 0;

foreach ($familia_data as $row) {
    $dui = $row['dui_encargado'];
    if (!isset($familias_agrupadas[$dui])) {
        $familias_agrupadas[$dui] = ['nombre_encargado' => $row['nombre_encargado'], 'estudiantes' => []];
    }
    $familias_agrupadas[$dui]['estudiantes'][] = ['nombre' => $row['nombre_estudiante'], 'grado' => $row['grado_seccion']];
    
    $total_estudiantes++;
    if (trim($row['codigo_genero']) == '01') { $total_masculino++; } else if (trim($row['codigo_genero']) == '02') { $total_femenino++; }
}
$total_familias = count($familias_agrupadas);

// NUEVA CONSULTA para el resumen de encargados
$query_encargados = "
    SELECT DISTINCT
        ae.dui,
        ae.codigo_genero,
        cf.descripcion AS parentesco
    FROM
        public.alumno_encargado ae
    INNER JOIN
        public.catalogo_familiar cf ON ae.codigo_familiar = cf.codigo
    WHERE
        ae.encargado = 't' AND ae.dui IS NOT NULL AND ae.dui != ''
        AND ae.codigo_alumno IN (SELECT m.codigo_alumno FROM public.alumno_matricula m WHERE m.codigo_ann_lectivo = :codigo_ann_lectivo AND m.retirado = 'f')
";
$stmt_encargados = $db_link->prepare($query_encargados);
$stmt_encargados->bindParam(':codigo_ann_lectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
$stmt_encargados->execute();
$encargados_data = $stmt_encargados->fetchAll(PDO::FETCH_ASSOC);

// Procesar datos para el nuevo resumen de encargados
$resumen_encargado_genero = ['M' => 0, 'F' => 0];
$resumen_parentesco = [];

foreach($encargados_data as $encargado){
    if(trim($encargado['codigo_genero']) == '01'){ $resumen_encargado_genero['M']++; } else if(trim($encargado['codigo_genero']) == '02'){ $resumen_encargado_genero['F']++; }
    
    $parentesco = trim($encargado['parentesco']);
    if(!isset($resumen_parentesco[$parentesco])){ $resumen_parentesco[$parentesco] = 0; }
    $resumen_parentesco[$parentesco]++;
}
ksort($resumen_parentesco);

// Creación del PDF
class PDF extends FPDF
{
    var $widths;
    var $header;

    function Header()
    {
        if(isset($_SESSION['logo_uno']) && file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'])){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
            $this->Image($img,10,5,25);
        }
        $this->SetFont('Arial','B',14);
        $this->Cell(0,6,utf8_decode($_SESSION['institucion']),0,1,'C');
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,utf8_decode('INFORME DE GRUPOS FAMILIARES'),0,1,'C');
        $this->Ln(5);
        if(!empty($this->header)){
            $this->DrawHeader();
        }
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }
    
    function DrawHeader()
    {
        $this->SetFont('Arial','B',9);
        $this->SetFillColor(50,50,50);
        $this->SetTextColor(255);
        for($i=0;$i<count($this->header);$i++)
            $this->Cell($this->widths[$i],7,utf8_decode($this->header[$i]),1,0,'C',true);
        $this->Ln();
    }

    function CreateTable($header, $data)
    {
        $this->header = $header;
        $this->widths = array(10, 80, 30, 80, 50);
        $this->DrawHeader();
        $this->SetFillColor(240,240,240);
        $this->SetTextColor(0);
        $this->SetFont('','', 8);
        $fill = false;
        $previousDui = null;
        foreach($data as $row)
        {
            $encargadoToShow = '';
            $duiToShow = '';
            if ($row[2] !== $previousDui) {
                $encargadoToShow = $row[1];
                $duiToShow = $row[2];
                $previousDui = $row[2];
            }
            $this->Cell($this->widths[0],6,$row[0],'LR',0,'C',$fill);
            $this->Cell($this->widths[1],6,utf8_decode($encargadoToShow),'LR',0,'L',$fill);
            $this->Cell($this->widths[2],6,utf8_decode($duiToShow),'LR',0,'C',$fill);
            $this->Cell($this->widths[3],6,utf8_decode($row[3]),'LR',0,'L',$fill);
            $this->Cell($this->widths[4],6,utf8_decode($row[4]),'LR',0,'L',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($this->widths),0,'','T');
    }
    
    function FinalSummaryTable($header, $data)
    {
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(230,230,230);
        $this->SetTextColor(0);
        $w = array(60, 40, 50);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();
        $this->SetFont('','',9);
        foreach($data as $row)
        {
            $this->Cell($w[0],6,utf8_decode($row[0]),'LR',0,'L');
            $this->Cell($w[1],6,$row[1],'LR',0,'C');
            $this->Cell($w[2],6,$row[2],'LR',0,'C');
            $this->Ln();
        }
        $this->Cell(array_sum($w),0,'','T');
    }
    
    // Función de resumen simple - CORREGIDA
    function SimpleSummaryTable($header, $data)
    {
        $startX = $this->GetX(); // Guardar posición X inicial
        $this->SetFont('Arial','B',10);
        $this->SetFillColor(230,230,230);
        $w = array(50, 25);
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',true);
        $this->Ln();
        $this->SetFont('','',9);
        foreach($data as $row)
        {
            $this->SetX($startX); // Restaurar X para cada fila
            $this->Cell($w[0],6,utf8_decode($row[0]),'LR',0,'L');
            $this->Cell($w[1],6,$row[1],'LR',0,'C');
            $this->Ln();
        }
        $this->SetX($startX); // Restaurar X para la línea final
        $this->Cell(array_sum($w),0,'','T');
    }
}

// Creación del objeto de la clase heredada en HORIZONTAL
$pdf = new PDF('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();

// Preparar y crear la tabla principal
$header = ['N#', 'Nombre del Encargado', 'DUI', 'Nombre del Estudiante', 'Grado y Sección'];
$data_list = [];
foreach($familia_data as $index => $row){
    $data_list[] = [$index + 1, $row['nombre_encargado'], $row['dui_encargado'], $row['nombre_estudiante'], $row['grado_seccion']];
}
$pdf->CreateTable($header, $data_list);

// --- INICIO DE LA SECCIÓN DE RESÚMENES EN NUEVA PÁGINA ---
$pdf->AddPage(); // Forzar una nueva página para los resúmenes

// --- SECCIÓN DE RESUMEN DE ESTUDIANTES ---
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode('Resumen General de Estudiantes por Familia'),0,1,'C');
$pdf->Ln(5);

$avg_masculino = ($total_familias > 0) ? number_format($total_masculino / $total_familias, 2) : 0;
$avg_femenino = ($total_familias > 0) ? number_format($total_femenino / $total_familias, 2) : 0;
$avg_total = ($total_familias > 0) ? number_format($total_estudiantes / $total_familias, 2) : 0;

$header_summary = ['Descripción', 'Cantidad Total', 'Promedio por Familia'];
$data_summary = [
    ['Estudiantes Masculinos', $total_masculino, $avg_masculino],
    ['Estudiantes Femeninos', $total_femenino, $avg_femenino],
    ['Subtotal de Estudiantes', $total_estudiantes, $avg_total]
];
$pdf->FinalSummaryTable($header_summary, $data_summary);

// --- SECCIÓN DE RESUMEN DE ENCARGADOS ---
$pdf->Ln(15);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode('Resumen de Encargados Principales'),0,1,'C');
$pdf->Ln(5);

// Preparar datos para las nuevas tablas
$header_enc_genero = ['Género del Encargado', 'Cantidad'];
$data_enc_genero = [
    ['Masculino', $resumen_encargado_genero['M']],
    ['Femenino', $resumen_encargado_genero['F']]
];

$header_parentesco = ['Parentesco', 'Cantidad'];
$data_parentesco = [];
foreach($resumen_parentesco as $parentesco => $cantidad){
    $data_parentesco[] = [$parentesco, $cantidad];
}

// Dibujar las nuevas tablas de resumen una al lado de la otra
$y_pos_initial = $pdf->GetY();
$pdf->SetX(60); // Posición X ajustada para la primera tabla
$pdf->SimpleSummaryTable($header_enc_genero, $data_enc_genero);

$pdf->SetY($y_pos_initial);
$pdf->SetX(145); // Posición X ajustada para la segunda tabla
$pdf->SimpleSummaryTable($header_parentesco, $data_parentesco);
// --- FIN DE LA NUEVA SECCIÓN ---


// Salida del PDF con nombre de archivo
$pdf->Output('I', 'NominaFamilias.pdf');
?>
