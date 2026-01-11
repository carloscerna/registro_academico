<?php
// 1. MODO SILENCIOSO Y COMPATIBILIDAD (Vital para PHP 8.3)
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

// Función auxiliar para caracteres (Reemplazo de utf8_decode)
if (!function_exists('utf8_decode_fix')) {
    function utf8_decode_fix($texto) {
        if (is_null($texto)) return '';
        return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
    }
}

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
// Consulta SQL actualizada con el nuevo ordenamiento.
$query = "
    SELECT
        p.nombres,
        p.apellidos,
        cc.descripcion AS nombre_cargo,
        p.codigo_cargo,
        p.dui,
        p.nit,
        p.nip,
        p.telefono_celular,
        p.codigo_genero
    FROM
        personal p
    INNER JOIN
        catalogo_cargo cc ON p.codigo_cargo = cc.codigo
    WHERE
        p.codigo_cargo <> '03' -- Excluir docentes
        AND p.codigo_estatus = '01' -- Solo personal activo
    ORDER BY
        p.codigo_cargo, p.apellidos, p.nombres
";

$stmt = $db_link->prepare($query);
$stmt->execute();
$personal_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar los datos para los resúmenes
$resumen_genero = ['M' => 0, 'F' => 0];
$resumen_cargo = [];

foreach ($personal_data as $persona) {
    // CORRECCIÓN: Uso de trim seguro con ?? ''
    if (trim($persona['codigo_genero'] ?? '') == '01') {
        $resumen_genero['M']++;
    } else if (trim($persona['codigo_genero'] ?? '') == '02') {
        $resumen_genero['F']++;
    }
    
    $cargo = $persona['nombre_cargo'];
    if (!isset($resumen_cargo[$cargo])) {
        $resumen_cargo[$cargo] = 0;
    }
    $resumen_cargo[$cargo]++;
}

// Creación del PDF
class PDF extends FPDF
{
    // Cabecera de página - MODIFICADA
    function Header()
    {
        // Logo
        if(isset($_SESSION['logo_uno']) && file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'])){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
            $this->Image($img,10,5,25); 
        }
        
        // Configuración de la fuente
        $this->SetFont('Arial','B',14);
        
        // Título de la Institución (Con Fix)
        $this->Cell(0,6,utf8_decode_fix($_SESSION['institucion']),0,1,'C');
        
        // Título del Reporte
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,utf8_decode_fix('NÓMINA DE PERSONAL ADMINISTRATIVO'),0,1,'C');
        
        $this->Ln(5);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode_fix('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabla de resumen
    function SummaryTable($header, $data)
    {
        $startX = $this->GetX();
        $this->SetFillColor(230,230,230);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B', 10);
        $w = array(50, 25); 
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode_fix($header[$i]),1,0,'C',true);
        $this->Ln();
        $this->SetFillColor(245,245,245);
        $this->SetTextColor(0);
        $this->SetFont('','', 9);
        $fill = true;
        foreach($data as $row)
        {
            $this->SetX($startX);
            $this->Cell($w[0],6,utf8_decode_fix($row[0]),'LR',0,'L',$fill);
            $this->Cell($w[1],6,$row[1],'LR',0,'C',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->SetX($startX);
        $this->Cell(array_sum($w),0,'','T');
    }
    
    // Tabla detallada - MODIFICADA
    function DetailTable($header, $data)
    {
        $this->SetFont('Arial','B',8);
        $w = array(8, 65, 47, 25, 25, 20); 
        
        $this->SetFillColor(50,50,50);
        $this->SetTextColor(255);
        
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],7,utf8_decode_fix($header[$i]),1,0,'C',true);
        $this->Ln();
        
        $this->SetFillColor(240,240,240);
        $this->SetTextColor(0);
        $this->SetFont('','', 7); 
        
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0],6,$row[0],'LR',0,'C',$fill);
            $this->Cell($w[1],6,utf8_decode_fix($row[1]),'LR',0,'L',$fill);
            $this->Cell($w[2],6,utf8_decode_fix($row[2]),'LR',0,'L',$fill);
            $this->Cell($w[3],6,$row[3],'LR',0,'C',$fill);
            $this->Cell($w[4],6,$row[4],'LR',0,'C',$fill);
            $this->Cell($w[5],6,$row[5],'LR',0,'C',$fill); 
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w),0,'','T');
    }
}

// Creación del objeto de la clase heredada
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Título de la sección de resúmenes
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode_fix('Resúmenes Generales'),0,1,'C');
$pdf->Ln(2);

// Crear datos para las tablas de resumen
$data_genero = [['Masculino', $resumen_genero['M']], ['Femenino', $resumen_genero['F']]];
$data_cargos = [];
ksort($resumen_cargo);
foreach($resumen_cargo as $cargo => $cantidad){ $data_cargos[] = [$cargo, $cantidad]; }

// Lógica para mostrar tablas de resumen
$pdf->SetFont('Arial','B',11);
$pdf->Cell(97, 10, utf8_decode_fix('Distribución por Género'), 0, 0, 'C');
$pdf->Cell(97, 10, utf8_decode_fix('Distribución por Cargo'), 0, 1, 'C');

$y_pos_initial = $pdf->GetY();
$pdf->SetX(25); 
$pdf->SummaryTable(['Género', 'Cantidad'], $data_genero);
$y_pos_end_1 = $pdf->GetY();

$pdf->SetY($y_pos_initial);
$pdf->SetX(115); 
$pdf->SummaryTable(['Cargo', 'Cantidad'], $data_cargos);
$y_pos_end_2 = $pdf->GetY();

$pdf->SetY(max($y_pos_end_1, $y_pos_end_2) + 10);

// Título de la sección de detalle
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode_fix('Nómina Detallada'),0,1,'C');
$pdf->Ln(5);

// Preparar datos para la tabla detallada - MODIFICADA
$header_detalle = ['N#', 'Nombre Completo', 'Cargo', 'DUI', 'NIT', 'Teléfono'];
$data_detalle = [];
foreach($personal_data as $index => $persona){
    $data_detalle[] = [
        $index + 1,
        // CORRECCIÓN: trim seguro concatenado
        trim($persona['nombres'] ?? '') . ' ' . trim($persona['apellidos'] ?? ''),
        $persona['nombre_cargo'],
        $persona['dui'],
        $persona['nit'],
        $persona['telefono_celular']
    ];
}

$pdf->DetailTable($header_detalle, $data_detalle);

// Salida del PDF con nombre de archivo
$pdf->Output('I', 'NominaAdministrativos.pdf');
?>