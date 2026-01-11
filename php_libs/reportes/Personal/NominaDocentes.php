<?php
// 1. SUPRIMIR WARNINGS y DEFINIR FUNCIÓN DE COMPATIBILIDAD
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

if (!function_exists('utf8_decode_fix')) {
    function utf8_decode_fix($texto) {
        if (is_null($texto)) return '';
        // Convierte de UTF-8 (Base de datos) a ISO-8859-1 (Lo que pide FPDF)
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
// Consulta SQL actualizada para filtrar solo docentes.
$query = "
    SELECT
        p.nombres,
        p.apellidos,
        p.dui,
        p.nit,
        p.nip,
        p.telefono_celular,
        p.codigo_genero
    FROM
        personal p
    WHERE
        p.codigo_cargo = '03' -- FILTRO PARA SOLO DOCENTES
        AND p.codigo_estatus = '01' -- Solo personal activo
    ORDER BY
        p.apellidos, p.nombres
";

$stmt = $db_link->prepare($query);
$stmt->execute();
$personal_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar los datos para el resumen de género
$resumen_genero = ['M' => 0, 'F' => 0];

foreach ($personal_data as $persona) {
    // CORRECCIÓN: trim seguro
    if (trim($persona['codigo_genero'] ?? '') == '01') {
        $resumen_genero['M']++;
    } else if (trim($persona['codigo_genero'] ?? '') == '02') {
        $resumen_genero['F']++;
    }
}

// Creación del PDF
class PDF extends FPDF
{
    // Variables para almacenar los anchos y encabezados de la tabla detallada
    var $widths;
    var $header_detalle;
    var $print_detail_header = false; // Bandera para controlar la impresión del encabezado

    // Cabecera de página - MODIFICADA
    function Header()
    {
        if(isset($_SESSION['logo_uno']) && file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'])){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
            $this->Image($img,10,5,20);
        }
        
        $this->SetFont('Arial','B',14);
        // CORRECCIÓN: utf8_decode_fix
        $this->Cell(0,6,utf8_decode_fix($_SESSION['institucion']),0,1,'C');
        
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,utf8_decode_fix('NÓMINA DE PERSONAL DOCENTE'),0,1,'C');
        
        $this->Ln(5);

        // Si la bandera está activa, dibuja el encabezado de la tabla de detalle
        if ($this->print_detail_header) {
            $this->DrawDetailHeader();
        }
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,utf8_decode_fix('Página ').$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabla de resumen
    function SummaryTable($header, $data, $total_row)
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
        $this->SetFont('','B', 10);
        $this->Cell($w[0],7,utf8_decode_fix($total_row[0]),'T',0,'L',false);
        $this->Cell($w[1],7,$total_row[1],'T',0,'C',false);
        $this->Ln();
        $this->SetX($startX);
        $this->Cell(array_sum($w),0,'','T');
    }
    
    // Función para dibujar solo el encabezado de la tabla detallada
    function DrawDetailHeader()
    {
        $this->SetFont('Arial','B',8);
        $this->SetFillColor(50,50,50);
        $this->SetTextColor(255);
        
        for($i=0;$i<count($this->header_detalle);$i++)
            $this->Cell($this->widths[$i],7,utf8_decode_fix($this->header_detalle[$i]),1,0,'C',true);
        $this->Ln();
    }

    // Tabla detallada - MODIFICADA para manejar saltos de página
    function DetailTable($header, $data)
    {
        // Guardar encabezado y anchos en variables de la clase
        $this->header_detalle = $header;
        $this->widths = array(8, 75, 12, 25, 25, 25, 20); 
        
        // Activar la bandera para que el Header() sepa que debe dibujar el encabezado de la tabla
        $this->print_detail_header = true;
        
        // Dibujar el primer encabezado (lo hacemos a través de la función Header para consistencia)
        // La función AddPage llama a Header(), pero la primera página ya fue añadida.
        // Por eso, para la primera página, lo llamamos explícitamente después de los resúmenes.
        $this->DrawDetailHeader();
        
        $this->SetFillColor(240,240,240);
        $this->SetTextColor(0);
        $this->SetFont('','', 7);
        
        $fill = false;
        foreach($data as $row)
        {
            // FPDF maneja el salto de página automático.
            // La función Header() se encargará de dibujar el encabezado de la tabla.
            $this->Cell($this->widths[0],6,$row[0],'LR',0,'C',$fill); // N#
            $this->Cell($this->widths[1],6,utf8_decode_fix($row[1]),'LR',0,'L',$fill); // Nombre
            $this->Cell($this->widths[2],6,$row[2],'LR',0,'C',$fill); // Género
            $this->Cell($this->widths[3],6,$row[3],'LR',0,'C',$fill); // DUI
            $this->Cell($this->widths[4],6,$row[4],'LR',0,'C',$fill); // NIT
            $this->Cell($this->widths[5],6,$row[5],'LR',0,'C',$fill); // NIP
            $this->Cell($this->widths[6],6,$row[6],'LR',0,'C',$fill); // Teléfono
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($this->widths),0,'','T');
        
        // Desactivar la bandera al terminar la tabla
        $this->print_detail_header = false;
    }
}

// Creación del objeto de la clase heredada
$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Título de la sección de resúmenes
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode_fix('Resumen General por Género'),0,1,'C');
$pdf->Ln(2);

// Crear datos para la tabla de resumen
$data_genero = [['Masculino', $resumen_genero['M']], ['Femenino', $resumen_genero['F']]];
$total_genero_row = ['Total', $resumen_genero['M'] + $resumen_genero['F']];

// Lógica para mostrar tabla de resumen (centrada)
$pdf->SetX(70); 
$pdf->SummaryTable(['Género', 'Cantidad'], $data_genero, $total_genero_row);

$pdf->SetY($pdf->GetY() + 10);

// Título de la sección de detalle
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10,utf8_decode_fix('Nómina Detallada'),0,1,'C');
$pdf->Ln(5);

// Preparar datos para la tabla detallada
$header_detalle = ['N#', 'Nombre Completo', 'Gén.', 'DUI', 'NIT', 'NIP', 'Teléfono'];
$data_detalle = [];
foreach($personal_data as $index => $persona){
    // CORRECCIÓN: trim seguro
    $genero_letra = (trim($persona['codigo_genero'] ?? '') == '01') ? 'M' : 'F';
    
    $data_detalle[] = [
        $index + 1,
        trim($persona['nombres'] ?? '') . ' ' . trim($persona['apellidos'] ?? ''),
        $genero_letra,
        $persona['dui'],
        $persona['nit'],
        $persona['nip'],
        $persona['telefono_celular']
    ];
}

$pdf->DetailTable($header_detalle, $data_detalle);

// Salida del PDF con nombre de archivo
$pdf->Output('I', 'NominaDocentes.pdf');
?>