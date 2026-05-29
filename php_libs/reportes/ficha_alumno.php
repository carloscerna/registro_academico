<?php
// Ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Archivos que se incluyen.
include($path_root . "/registro_academico/includes/funciones.php");
include($path_root . "/registro_academico/includes/consultas.php");
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

// Llamar a la librería fpdf
include($path_root . "/registro_academico/php_libs/fpdf/fpdf.php");

// Cambiar a UTF-8 para salida HTML previa si es necesario
header("Content-Type: text/html; charset=UTF-8");    

// Inicialización de variables para control de errores en PHP 8.x
$codigo_all = '';
$codigo_matricula = '';
$codigo_alumno = isset($_REQUEST['id_user']) ? intval($_REQUEST['id_user']) : 0;
$db_link = $dblink;

// Variables por defecto por si la consulta no devuelve datos
$codigo_zona = '';
$estudio_parvularia = '';
$tiene_hijos = '';
$cantidad_hijos = '';
$codigo_actividad_economica = '';
$codigo_discapacidad = '';
$codigo_estado_familiar = '';
$foto = 'foto_no_disponible.jpg';

// Función alternativa y moderna a utf8_decode para compatibilidad con PHP 8.2+
if (!function_exists('safe_utf8_decode')) {
    function safe_utf8_decode($string) {
        $string = $string ?? ''; // Previene errores de tipo null en PHP 8.x
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($string, 'ISO-8859-1', 'UTF-8');
        } elseif (function_exists('iconv')) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);
        }
        return $string;
    }
}

class PDF extends FPDF
{
    // Rotar texto función TEXT()
    function RotatedText($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    // Rotar texto función MultiCell()
    function RotatedTextMultiCell($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->SetXY($x, $y);
        $this->MultiCell(90, 4, $txt, 0, 'L');
        $this->Rotate(0);
    }

    function RotatedTextMultiCellAspectos($x, $y, $txt, $angle)
    {
        $this->Rotate($angle, $x, $y);
        $this->SetXY($x, $y);
        $this->MultiCell(43, 3, $txt, 0, 'L');
        $this->Rotate(0);
    }

    // Cabecera de página
    function Header()
    {
        // Logo
        $logo_uno = $_SESSION['logo_uno'] ?? '';
        $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $logo_uno;
        
        if (!empty($logo_uno) && file_exists($img)) {
            $this->Image($img, 7, 6, 8, 11);
        }
        
        $this->AddFont('Comic');
        $this->SetFont('Comic', '', 12);
        $this->SetTextColor(0, 0, 255);
        
        $institucion = isset($_SESSION['institucion']) ? trim($_SESSION['institucion']) : '';
        $this->Cell(135, 7, safe_utf8_decode($institucion), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);
        $this->Rect(5, 5, 135, 215);
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-20);
    }

    // Tabla coloreada
    function FancyTable($header)
    {
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('', '');
        
        $w = array(65, 20, 12, 18, 20); // Ancho de las columnas
        for ($i = 0; $i < count($header); $i++) {
            $this->Cell($w[$i], 7, safe_utf8_decode($header[$i]), 1, 0, 'C', 1);
        }
        
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
    }
}

// ************************************************************************************************************************
// Creando el Informe.
$pdf = new PDF('P', 'mm', array(145, 225));
$pdf->SetMargins(5, 5, 5);
$pdf->SetAutoPageBreak(true, 5);

$header = array('Entidad', 'Serv.Educativo', 'Sección', 'Año Lectivo', 'Estatus');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Times', 'B', 10);
$pdf->SetY(5);
$pdf->SetX(5);
$pdf->Ln();

$pdf->SetFont('Comic', '', 8);

// Crear líneas y rectángulos estáticos
$pdf->Line(5, 55, 140, 55);
$pdf->Line(5, 95, 140, 95);
$pdf->Line(5, 141, 140, 141);
$pdf->Line(5, 181, 140, 181);
$pdf->Rect(117, 20, 22, 28); // Cuadro foto

$pdf->SetFont('Comic', '', 10);    
$pdf->SetTextColor(255, 0, 0);
$pdf->RotatedText(55, 16, 'DATOS GENERALES', 0);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Comic', '', 8);
$pdf->RotatedText(117, 18, 'Id:', 0);
$pdf->RotatedText(8, 20, 'Apellido Paterno', 0);
$pdf->Rect(7, 21, 50, 5);

$pdf->RotatedText(60, 20, 'Apellido Materno', 0);
$pdf->Rect(59, 21, 50, 5);

$pdf->RotatedText(8, 30, 'Nombres', 0);
$pdf->Rect(7, 31, 50, 5);

$pdf->RotatedText(60, 30, safe_utf8_decode('Teléfono: Casa'), 0);
$pdf->Rect(59, 31, 20, 5);

$pdf->RotatedText(90, 30, safe_utf8_decode('Célular'), 0);
$pdf->Rect(89, 31, 20, 5);

$pdf->RotatedText(8, 40, safe_utf8_decode('Dirección'), 0);
$pdf->Rect(7, 41, 100, 10);

$pdf->SetFont('Comic', '', 10);
$pdf->RotatedText(108, 53, 'NIE:', 0);

// Sección de partida de nacimiento
$pdf->SetTextColor(255, 0, 0);
$pdf->RotatedText(35, 59, 'DATOS DE PARTIDA DE NACIMIENTO', 0);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Comic', '', 8);
$pdf->RotatedText(8, 65, 'Departamento', 0);
$pdf->Rect(7, 66, 30, 5);

$pdf->RotatedText(41, 65, 'Municipio', 0);
$pdf->Rect(40, 66, 30, 5);

$pdf->RotatedText(75, 65, safe_utf8_decode('Género'), 0);
$pdf->Rect(74, 66, 20, 5);

$pdf->RotatedText(98, 65, 'Estado Civil', 0);
$pdf->Rect(97, 66, 20, 5);

$pdf->RotatedText(8, 75, 'Nacionalidad', 0);
$pdf->Rect(7, 76, 30, 5);

$pdf->RotatedText(41, 75, 'Transporte', 0);
$pdf->Rect(40, 76, 30, 5);

$pdf->RotatedText(75, 75, 'Distancia', 0);
$pdf->Rect(74, 76, 20, 5);

$pdf->RotatedText(8, 85, 'Fecha de Nacimiento', 0);
$pdf->Rect(7, 86, 30, 5);

$pdf->RotatedText(41, 85, 'Edad', 0);
$pdf->Rect(40, 86, 10, 5);

$pdf->RotatedText(54, 85, safe_utf8_decode('Número'), 0);
$pdf->Rect(53, 86, 10, 5);

$pdf->RotatedText(67, 85, 'Folio', 0);
$pdf->Rect(66, 86, 10, 5);

$pdf->RotatedText(80, 85, 'Tomo', 0);
$pdf->Rect(79, 86, 10, 5);

$pdf->RotatedText(93, 85, 'Libro', 0);
$pdf->Rect(92, 86, 10, 5);

// Consulta Principal Alumno
$query_a = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
            a.apellido_paterno, a.apellido_materno, a.nombre_completo, 
            a.direccion_alumno, telefono_alumno, a.fecha_nacimiento, a.edad, a.pn_tomo, a.pn_libro, a.pn_numero, a.pn_folio,
            a.nacionalidad, a.transporte, a.distancia, a.genero, cat_ec.nombre as estado_civil,
            a.estudio_parvularia, a.tiene_hijos, a.cantidad_hijos, a.codigo_actividad_economica, a.codigo_discapacidad, a.codigo_estado_familiar,
            a.foto, depa.nombre as nombre_departamento, muni.nombre as nombre_municipio, cat_z_r.codigo as codigo_zona_residencia
            FROM alumno a
            INNER JOIN catalogo_zona_residencia cat_z_r ON cat_z_r.codigo = a.codigo_zona_residencia
            INNER JOIN catalogo_estado_civil cat_ec ON cat_ec.codigo = a.codigo_estado_civil
            INNER JOIN departamento depa ON depa.codigo = a.codigo_departamento 
            INNER JOIN municipio muni ON muni.codigo = a.codigo_municipio AND a.codigo_departamento = muni.codigo_departamento
            WHERE id_alumno = ? ORDER BY apellido_alumno";

$stmt = $db_link->prepare($query_a);
$stmt->execute([$codigo_alumno]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $pdf->RotatedText(122, 18, ucwords(strtolower(trim($row['id_alumno'] ?? ''))), 0);
    $pdf->RotatedText(8, 25, cambiar_de_del($row['apellido_paterno'] ?? ''), 0);
    $pdf->RotatedText(60, 25, cambiar_de_del($row['apellido_materno'] ?? ''), 0);
    $pdf->RotatedText(8, 35, cambiar_de_del($row['nombre_completo'] ?? ''), 0);
    $pdf->RotatedText(60, 35, trim($row['telefono_alumno'] ?? ''), 0);
    $pdf->RotatedTextMulticell(8, 42, cambiar_de_del($row['direccion_alumno'] ?? ''), 0);
    
    $pdf->SetFont('Comic', '', 10);
    $pdf->RotatedText(118, 53, trim($row['codigo_nie'] ?? ''), 0);
    
    $pdf->SetFont('Comic', '', 8);
    $genero = (trim($row['genero'] ?? '') == 'm') ? 'Masculino' : 'Femenino';
    
    $pdf->RotatedText(8, 70, trim($row['nombre_departamento'] ?? ''), 0);
    $pdf->RotatedText(41, 70, trim($row['nombre_municipio'] ?? ''), 0);
    $pdf->RotatedText(75, 70, $genero, 0);
    $pdf->RotatedText(98, 70, trim($row['estado_civil'] ?? ''), 0);
    
    $pdf->RotatedText(8, 80, cambiar_de_del($row['nacionalidad'] ?? ''), 0);
    $pdf->RotatedText(41, 80, cambiar_de_del($row['transporte'] ?? ''), 0);
    $pdf->RotatedText(75, 80, trim($row['distancia'] ?? ''), 0);
    
    $pdf->RotatedText(8, 90, cambiaf_a_normal(trim($row['fecha_nacimiento'] ?? '')), 0);
    $pdf->RotatedText(41, 90, trim($row['edad'] ?? ''), 0);
    $pdf->RotatedText(54, 90, trim($row['pn_numero'] ?? ''), 0);
    $pdf->RotatedText(67, 90, trim($row['pn_folio'] ?? ''), 0);
    $pdf->RotatedText(80, 90, trim($row['pn_tomo'] ?? ''), 0);
    $pdf->RotatedText(93, 90, trim($row['pn_libro'] ?? ''), 0);
    
    // Mapeo a variables externas
    $codigo_zona = trim($row['codigo_zona_residencia'] ?? '');
    $estudio_parvularia = trim($row['estudio_parvularia'] ?? '');
    $tiene_hijos = trim($row['tiene_hijos'] ?? '');
    $cantidad_hijos = trim($row['cantidad_hijos'] ?? '');
    $codigo_actividad_economica = trim($row['codigo_actividad_economica'] ?? '');
    $codigo_discapacidad = trim($row['codigo_discapacidad'] ?? '');
    $codigo_estado_familiar = trim($row['codigo_estado_familiar'] ?? '');
    
    if (!empty($row['foto'])) {
        $foto = trim($row['foto']);
    }
}

// Renderizado de la Foto
$img_foto = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/png/' . $foto;
if (file_exists($img_foto)) {
    $pdf->Image($img_foto, 118, 21, 20, 26);
}

// Información de Encargados
$pdf->SetFont('Comic', '', 10);    
$pdf->SetTextColor(255, 0, 0);
$pdf->RotatedText(30, 100, safe_utf8_decode('INFORMACIÓN DEL PADRE, MADRE O ENCARGADO'), 0);
$pdf->RotatedText(110, 105, 'PADRE', 0);
$pdf->RotatedText(110, 145, 'MADRE', 0);
$pdf->RotatedText(110, 185, 'ENCARGADO', 0);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetFont('Comic', '', 8);

$eje_y_etiqueta = 108;
$eje_y_etiqueta_2 = 118;
$eje_y_rectangulo = 109;
$eje_y_rectangulo_2 = 119;
$eje_y_etiqueta_3 = 128;
$eje_y_rectangulo_3 = 129;

for ($i = 0; $i <= 2; $i++) {
    $pdf->RotatedText(8, $eje_y_etiqueta, 'Nombre', 0);
    $pdf->Rect(7, $eje_y_rectangulo, 60, 5);

    $pdf->RotatedText(71, $eje_y_etiqueta, 'DUI', 0);
    $pdf->Rect(70, $eje_y_rectangulo, 25, 5);

    $pdf->RotatedText(8, $eje_y_etiqueta_2, 'Lugar de Trabajo', 0);
    $pdf->Rect(7, $eje_y_rectangulo_2, 60, 5);

    $pdf->RotatedText(71, $eje_y_etiqueta_2, safe_utf8_decode('Profesión u Oficio'), 0);
    $pdf->Rect(70, $eje_y_rectangulo_2, 60, 5);

    $pdf->RotatedText(8, $eje_y_etiqueta_3, safe_utf8_decode('Dirección'), 0);
    $pdf->Rect(7, $eje_y_rectangulo_3, 100, 10);

    $pdf->RotatedText(111, $eje_y_etiqueta_3, safe_utf8_decode('Teléfono'), 0);
    $pdf->Rect(110, $eje_y_rectangulo_3, 20, 5);

    $eje_y_etiqueta += 40;
    $eje_y_rectangulo += 40;
    $eje_y_etiqueta_2 += 40;
    $eje_y_rectangulo_2 += 40;
    $eje_y_etiqueta_3 += 40;
    $eje_y_rectangulo_3 += 40;
}

// Consulta de Encargados
$query_e = "SELECT ae.nombres, ae.dui, ae.lugar_trabajo, ae.profesion_oficio, ae.telefono, ae.direccion
            FROM alumno_encargado ae
            WHERE ae.codigo_alumno = ? ORDER BY ae.id_alumno_encargado";

$stmt_e = $db_link->prepare($query_e);
$stmt_e->execute([$codigo_alumno]);

$eje_y_campo_1 = 113;
$eje_y_campo_2 = 123;
$eje_y_campo_3 = 130;   
$eje_y_campo_4 = 132;   

while ($row = $stmt_e->fetch(PDO::FETCH_ASSOC)) {
    $pdf->RotatedText(8, $eje_y_campo_1, cambiar_de_del($row['nombres'] ?? ''), 0);
    $pdf->RotatedText(71, $eje_y_campo_1, trim($row['dui'] ?? ''), 0);
    $pdf->RotatedText(8, $eje_y_campo_2, cambiar_de_del($row['lugar_trabajo'] ?? ''), 0);
    $pdf->RotatedText(71, $eje_y_campo_2, cambiar_de_del($row['profesion_oficio'] ?? ''), 0);
    $pdf->RotatedTextMulticell(8, $eje_y_campo_3, cambiar_de_del($row['direccion'] ?? ''), 0);
    $pdf->RotatedText(113, $eje_y_campo_4, trim($row['telefono'] ?? ''), 0);
    
    $eje_y_campo_1 += 40;
    $eje_y_campo_2 += 40;
    $eje_y_campo_3 += 40;
    $eje_y_campo_4 += 40;
}

// ************************************************************************************************************************
// AGREGAR LA SEGUNDA PÁGINA
$pdf->AddPage();

$pdf->SetFont('Comic', '', 10);
$pdf->SetY(55);
$pdf->SetX(8);
$pdf->Ln();

$pdf->SetTextColor(255, 0, 0);
$pdf->RotatedText(60, 22, 'OTROS', 0);
$pdf->RotatedText(45, 60, 'ESTUDIOS REALIZADOS', 0);
$pdf->SetTextColor(0, 0, 0);

$pdf->Line(5, 18, 140, 18);
$pdf->Line(5, 55, 140, 55);

$pdf->SetFont('Comic', '', 8);    
$pdf->RotatedText(8, 30, safe_utf8_decode('Actividad Económica'), 0);
$pdf->Rect(50, 27, 10, 5);
$pdf->RotatedText(52, 30.5, $codigo_actividad_economica, 0);

$pdf->RotatedText(80, 30, 'Tiene Hijos', 0);
$pdf->RotatedText(110, 30, 'Si', 0);
$pdf->RotatedText(125, 30, 'No', 0);
$pdf->Rect(115, 27, 4, 4);
$pdf->Rect(130, 27, 4, 4);
if ($tiene_hijos === 't') {
    $pdf->RotatedText(116, 30, 'X', 0);
} else {
    $pdf->RotatedText(131, 30, 'X', 0);
}

$pdf->RotatedText(8, 37, 'Tipo de discapacidad', 0);
$pdf->Rect(50, 33, 10, 5);
$pdf->RotatedText(52, 36.5, $codigo_discapacidad, 0);

$pdf->RotatedText(80, 37, 'Si tiene cantidad', 0);
$pdf->Rect(110, 33, 15, 5);
$pdf->RotatedText(115, 36.5, $cantidad_hijos, 0);

$pdf->RotatedText(8, 44, 'Estado Familiar', 0);
$pdf->Rect(50, 39, 10, 5);
$pdf->RotatedText(52, 42.5, $codigo_estado_familiar, 0);

$pdf->RotatedText(80, 44, 'Estudio Parvularia', 0);
$pdf->RotatedText(110, 44, 'Si', 0);
$pdf->RotatedText(125, 44, 'No', 0);
$pdf->Rect(115, 41, 4, 4);
$pdf->Rect(130, 41, 4, 4);
if ($estudio_parvularia === 't') {
    $pdf->RotatedText(116, 44, 'X', 0);
} else {
    $pdf->RotatedText(131, 44, 'X', 0);
}

$pdf->RotatedText(8, 50, 'Zona de Residencia', 0);
$pdf->RotatedText(45, 50, 'Urbana', 0);
$pdf->RotatedText(60, 50, 'Rural', 0);
$pdf->Rect(55, 47, 4, 4);
$pdf->Rect(70, 47, 4, 4);
if ($codigo_zona === '01') {
    $pdf->RotatedText(56, 50, 'X', 0);
}
if ($codigo_zona === '02') {
    $pdf->RotatedText(71, 50, 'X', 0);
}

// Historial de Matrículas / Estudios realizados
$query_m = "SELECT nom_esc.nombre as nombre_escuela, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo
            FROM alumno_matricula am
            INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
            INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
            INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
            INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
            INNER JOIN catalogo_escuelas nom_esc ON nom_esc.codigo = am.codigo_institucion
            WHERE am.codigo_alumno = ? ORDER BY nombre_ann_lectivo";

$stmt_m = $db_link->prepare($query_m);
$stmt_m->execute([$codigo_alumno]);

$fill = false; 
$linea_estudio = 0;
$pdf->FancyTable($header); 
$w = array(65, 20, 12, 18, 20); 
$pdf->Ln();

while ($row = $stmt_m->fetch(PDO::FETCH_ASSOC)) {
    $linea_estudio++;
    $pdf->Cell($w[0], 6, cambiar_de_del($row['nombre_escuela'] ?? ''), 'LR', 0, 'L', $fill);
    $pdf->Cell($w[1], 6, cambiar_de_del($row['nombre_grado'] ?? ''), 'LR', 0, 'C', $fill);
    $pdf->Cell($w[2], 6, trim($row['nombre_seccion'] ?? ''), 'LR', 0, 'C', $fill);
    $pdf->Cell($w[3], 6, trim($row['nombre_ann_lectivo'] ?? ''), 'LR', 0, 'C', $fill);
    $pdf->Cell($w[4], 6, '', 'LR', 1, 'C', $fill);
    $fill = !$fill;
}

// Rellenar líneas vacías en la tabla
$linea_faltante = 20 - $linea_estudio;
for ($li = 0; $li <= $linea_faltante; $li++) {
    $pdf->Cell($w[0], 6, '', 'LR', 0, 'L', $fill);
    $pdf->Cell($w[1], 6, '', 'LR', 0, 'C', $fill);
    $pdf->Cell($w[2], 6, '', 'LR', 0, 'C', $fill);
    $pdf->Cell($w[3], 6, '', 'LR', 0, 'C', $fill);
    $pdf->Cell($w[4], 6, '', 'LR', 1, 'C', $fill);
    $fill = !$fill;
}

// Cerrar Línea Final de la Tabla y enviar PDF
$pdf->Cell(array_sum($w), 0, '', 'T');
$pdf->Output();
?>