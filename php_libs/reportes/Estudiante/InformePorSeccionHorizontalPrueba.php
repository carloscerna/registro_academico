<?php
// Rutas y conexión
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";
include($path_root . "/registro_academico/includes/funciones.php");
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
header("Content-Type: text/html; charset=UTF-8");

$pdo = $dblink;

// Variables desde el formulario
$modalidad    = $_GET['modalidad'];
$gradoseccion = $_GET['gradoseccion'];
$annlectivo   = $_GET['annlectivo'];
$asignatura   = $_GET['asignatura'] ?? null; // opcional
$periodo      = $_GET['periodo']    ?? null; // opcional

// Obtener cantidad de períodos desde catalogo_periodos
$sqlPeriodo = "SELECT cantidad_periodos FROM catalogo_periodos WHERE codigo_modalidad = ? LIMIT 1";
$cant_periodos = 3;
if ($stmt = $pdo->prepare($sqlPeriodo)) {
    $stmt->execute([$modalidad]);
    $cant_periodos = (int) $stmt->fetchColumn() ?: 3;
}
$calif_minima = 6;

// Construir codigo_all
$codigo_all = $modalidad . substr($gradoseccion, 0, 6) . $annlectivo; 

// Obtener nombre del docente encargado
$query_encargado = "
    SELECT btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente,
           eg.codigo_docente,
           p.firma AS firma_docente_filename
    FROM encargado_grado eg
    INNER JOIN personal p ON eg.codigo_docente = p.id_personal
    INNER JOIN bachillerato_ciclo bach ON eg.codigo_bachillerato = bach.codigo
    INNER JOIN ann_lectivo ann ON eg.codigo_ann_lectivo = ann.codigo
    INNER JOIN grado_ano gann ON eg.codigo_grado = gann.codigo
    INNER JOIN seccion sec ON eg.codigo_seccion = sec.codigo
    INNER JOIN turno tur ON eg.codigo_turno = tur.codigo
    WHERE btrim(bach.codigo || gann.codigo || sec.codigo || tur.codigo || ann.codigo ) = :codigo_all
      AND eg.encargado = 't'
    ORDER BY p.nombres
";
$stmt_encargado = $pdo->prepare($query_encargado);
$stmt_encargado->execute([':codigo_all' => $codigo_all]);
$nombre_encargado = '';
$firma_docente_filename = '';
if ($row = $stmt_encargado->fetch(PDO::FETCH_ASSOC)) {
    $nombre_encargado = convertirtexto(trim($row['nombre_docente']));
    $firma_docente_filename = trim($row['firma_docente_filename']);
}


// Clase PDF
class PDF extends FPDF {
    var $nombre_encargado;
    var $firma_docente_filename;

    function Header() {
        $this->SetFont('Arial','B',12);
        // Header del PDF
        $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno']; //Logo
        $nombre_institucion = convertirtexto($_SESSION['institucion']);
        $this->  Image($img, 10, 10, 20); // Logo
        $this->SetXY(30, 10);
        $this->Cell(0, 6, convertirtexto($nombre_institucion), 0, 1, 'L');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,convertirtexto('Informe Académico - por Estudiante'),0,1,'C');
        $this->Ln(4);
    }
    function Footer() {
    global $registro_docente, $firma, $sello;

    $nombre_director = cambiar_de_del($_SESSION['nombre_director'] ?? 'Director');

    // Paths
    $img_firma_director = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_firma'] ?? '');
    $img_sello = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_sello'] ?? '');

    // Firma docente encargado
    $firma_encargado_path = '';
    if (!empty($this->firma_docente_filename) && isset($_SESSION['codigo_institucion'])) {
        $firma_encargado_path = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/firmas/' . $_SESSION['codigo_institucion'] . '/' . $this->firma_docente_filename;
    }

    $this->SetY(-45); // Posicionar desde abajo
    $y = $this->GetY();

    // Firmas - líneas horizontales
    $this->Line(10, $y + 25, 80, $y + 25);   // Línea Docente
    $this->Line(120, $y + 25, 190, $y + 25); // Línea Director
    $this->Line(5, $y + 42, 203, $y + 42);   // Línea final horizontal

    // Firma del Docente Encargado
    if (!empty($this->nombre_encargado)) {
        if (!empty($firma_encargado_path) && file_exists($firma_encargado_path)) {
            $this->Image($firma_encargado_path, 25, $y + 10, 40, 12);
        }
        $this->SetXY(10, $y + 27);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(70, 5, convertirTexto($this->nombre_encargado), 0, 2, 'L');
        $this->Cell(70, 5, "Docente encargado de la sección", 0, 0, 'L');
    } else {
        $this->SetXY(10, $y + 27);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(70, 5, convertirTexto($registro_docente ?? ''), 0, 2, 'L');
        $this->Cell(70, 5, "Encargado Registro Académico", 0, 0, 'L');
    }

    // Firma del Director
    // Imagen de firma del director
    if (file_exists($img_firma_director)) {
        $this->Image($img_firma_director, 135, $y - 5, 30, 36); // Firma centrada horizontalmente
    }

    // Imagen del sello
    if (file_exists($img_sello)) {
        $this->Image($img_sello, 170, $y + 2, 25, 25); // Sello ligeramente detrás
    }

    // Nombre del director
    $this->SetXY(120, $y + 27);
    $this->SetFont('Arial', 'I', 8);
    $this->Cell(70, 5, convertirTexto($nombre_director), 0, 2, 'C');
    $this->Cell(70, 5, "Director(a)", 0, 0, 'C');
}


    // Function to add student data in the specified 2-column, 2-row format
    function addEstudiante($nie, $nombre, $modalidad, $grado_seccion_turno, $ann_lectivo) {
        $this->SetFont('Arial','',10);
        
        // Row 1: NIE and Nombre
        $this->Cell(35,6,'NIE:',0,0,'L');
        $this->Cell(60,6,convertirtexto($nie),0,0,'L');
        $this->Cell(35,6,'Nombre:',0,0,'L');
        $this->Cell(100,6,convertirtexto($nombre),0,1,'L'); 

        // Row 2: Modalidad, Grado/Secc/Turno, Año Lectivo
        $this->Cell(35,6,'Modalidad:',0,0,'L');
        $this->Cell(60,6,convertirtexto($modalidad),0,0,'L');
        $this->Cell(35,6,'Grado/Secc/Turno:',0,0,'L');
        $this->Cell(60,6,convertirtexto($grado_seccion_turno),0,0,'L');
        $this->Cell(35,6,'Año Lectivo:',0,0,'L');
        $this->Cell(60,6,convertirtexto($ann_lectivo),0,1,'L');
        
        $this->Ln(2);
    }

    function addTabla($asignaturas, $cant_periodos, $minima = 6) {
        // Headers font size (increased to 8pt for better readability)
        $this->SetFont('Arial','B',8); 
        
        // Column widths adjustments
        $col_notas_indiv = 6; // Reduced individual note column width (A1, A2, A3, R, PP, R1, R2, NF)
        $col_asignatura_width = 80; // Increased width for Asignatura column
        $col_res_width = 20; // Increased width for 'Res.' column

        // Calculate total width for dynamic positioning
        $total_period_columns_width = $col_notas_indiv * 5 * $cant_periodos;
        $total_final_columns_width = $col_notas_indiv * 3; // R1, R2, NF

        $this->Cell($col_asignatura_width,10,'Asignatura',1,0,'C');

        for ($p = 1; $p <= $cant_periodos; $p++) {
            $this->Cell($col_notas_indiv * 5, 5, "PERIODO $p", 1, 0, 'C');
        }
        $this->Cell($total_final_columns_width, 10, 'Final', 1, 0, 'C'); 
        $this->Cell($col_res_width, 10, 'Res.', 1, 0, 'C'); 
        $this->Ln();

        // Second row of headers for individual period components
        $this->Cell($col_asignatura_width,5,'',0,0); // Spacer for Asignatura column
        for ($i = 0; $i < $cant_periodos; $i++) {
            foreach (['A1','A2','A3','R','PP'] as $et) {
                $this->Cell($col_notas_indiv,5,$et,1,0,'C');
            }
        }
        foreach (['R1','R2','NF'] as $et) {
            $this->Cell($col_notas_indiv,5,$et,1,0,'C');
        }
        $this->Cell($col_res_width, 5, '', 1, 0, 'C'); // Spacer for Res. column
        $this->Ln();

        // Data font size (increased to 8pt for better readability)
        $this->SetFont('Arial','',8); 
        
       foreach ($asignaturas as $nombre => $periodos) {
            $x = $this->GetX();
            $y = $this->GetY();
            
            // Estimar la altura que ocupará el nombre con MultiCell
            $this->SetFont('Arial','',8);
            $nb = $this->NbLines($col_asignatura_width, convertirtexto($nombre));
            $rowHeight = 5 * $nb; // Ajusta 5 según la altura de línea deseada

            // Imprimir MultiCell para Asignatura
            $this->MultiCell($col_asignatura_width, 5, convertirtexto($nombre), 1, 'L');

            // Establecer posición para las demás celdas de la fila
            $this->SetXY($x + $col_asignatura_width, $y);

            // Notas por período
            for ($i = 1; $i <= $cant_periodos; $i++) {
                foreach (['A1','A2','A3','R','PP'] as $et) {
                    $nota = $periodos[$i][$et] ?? '';
                    $this->Cell($col_notas_indiv, $rowHeight, $nota, 1, 0, 'C');
                }
            }

            // R1, R2, NF
            foreach (['R1','R2','NF'] as $et) {
                $nota = $periodos['Final'][$et] ?? '';
                $this->Cell($col_notas_indiv, $rowHeight, $nota, 1, 0, 'C');
            }

            // Resultado
            $resultado = $periodos['Resultado'] ?? '';
            $this->Cell($col_res_width, $rowHeight, $resultado, 1, 0, 'C');

            $this->Ln();
        }

    }
    function NbLines($w, $txt) {
    $cw = &$this->CurrentFont['cw'];
    if ($w == 0)
        $w = $this->w - $this->rMargin - $this->x;
    $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
    $s = str_replace("\r", '', $txt);
    $nb = strlen($s);
    if ($nb > 0 and $s[$nb - 1] == "\n")
        $nb--;
    $sep = -1;
    $i = 0;
    $j = 0;
    $l = 0;
    $nl = 1;
    while ($i < $nb) {
        $c = $s[$i];
        if ($c == "\n") {
            $i++;
            $sep = -1;
            $j = $i;
            $l = 0;
            $nl++;
            continue;
        }
        if ($c == ' ')
            $sep = $i;
        $l += $cw[$c];
        if ($l > $wmax) {
            if ($sep == -1) {
                if ($i == $j)
                    $i++;
            } else
                $i = $sep + 1;
            $sep = -1;
            $j = $i;
            $l = 0;
            $nl++;
        } else
            $i++;
    }
    return $nl;
}
}

// Obtener datos descriptivos de modalidad, grado, sección, turno y año lectivo
$sqlDesc = "
    SELECT
        bach.nombre as nombre_modalidad,
        gann.nombre as nombre_grado,
        sec.nombre as nombre_seccion,
        tur.nombre as nombre_turno,
        ann.nombre as nombre_annlectivo
    FROM bachillerato_ciclo bach, grado_ano gann, seccion sec, turno tur, ann_lectivo ann
    WHERE bach.codigo = ?
    AND gann.codigo = SUBSTRING(?, 1, 2)
    AND sec.codigo = SUBSTRING(?, 3, 2)
    AND tur.codigo = SUBSTRING(?, 5, 2)
    AND ann.codigo = ?
";
$stmtDesc = $pdo->prepare($sqlDesc);
$stmtDesc->execute([$modalidad, $gradoseccion, $gradoseccion, $gradoseccion, $annlectivo]);
$desc_data = $stmtDesc->fetch(PDO::FETCH_ASSOC);

$nombre_modalidad = $desc_data['nombre_modalidad'] ?? '';
$nombre_grado = $desc_data['nombre_grado'] ?? '';
$nombre_seccion = $desc_data['nombre_seccion'] ?? '';
$nombre_turno = $desc_data['nombre_turno'] ?? '';
$nombre_grado_seccion_turno = $nombre_grado . ' ' . $nombre_seccion . ' ' . $nombre_turno;
$nombre_annlectivo = $desc_data['nombre_annlectivo'] ?? '';


// Obtener estudiantes
$sqlEst = "SELECT a.id_alumno, a.codigo_nie,
        TRIM(a.nombre_completo) AS nombres,
        TRIM(a.apellido_paterno) AS apellido_paterno,
        TRIM(a.apellido_materno) AS apellido_materno
        FROM alumno a
        INNER JOIN alumno_matricula am ON am.codigo_alumno = a.id_alumno
        WHERE am.codigo_bach_o_ciclo = ?
        AND CONCAT(am.codigo_grado, am.codigo_seccion, am.codigo_turno) = ?
        AND am.codigo_ann_lectivo = ?
        AND am.retirado = false
        ORDER BY nombres, apellido_paterno, apellido_materno"; 
$st = $pdo->prepare($sqlEst);
$st->execute([$modalidad, $gradoseccion, $annlectivo]);
$estudiantes = $st->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new PDF("L", "mm", "A4"); // Orientación Horizontal

// Set footer properties
$pdf->nombre_encargado = $nombre_encargado;
$pdf->firma_docente_filename = $firma_docente_filename;

// Construct the filename using descriptive names
$report_filename = "Informe_" . limpiar_cadena($nombre_modalidad) . "_" . limpiar_cadena($nombre_grado) . "_" . limpiar_cadena($nombre_seccion) . "_" . limpiar_cadena($nombre_annlectivo) . ".pdf";

foreach ($estudiantes as $est) {
    $pdf->AddPage();

    // Reconstruct the full name for display: nombre_completo apellido_paterno apellido_materno
    $full_student_name = trim($est['nombres'] . ' ' . $est['apellido_paterno'] . ' ' . $est['apellido_materno']);
    $full_student_name = preg_replace('/\s+/', ' ', $full_student_name); // Replace multiple spaces with a single one


    $pdf->addEstudiante(
        $est['codigo_nie'],
        $full_student_name,
        $nombre_modalidad,
        $nombre_grado_seccion_turno,
        $nombre_annlectivo
    );

$sqlNotas = "
    SELECT
        asig.nombre AS asignatura,
        n.nota_a1_1, n.nota_a2_1, n.nota_a3_1, n.nota_r_1, n.nota_p_p_1,
        n.nota_a1_2, n.nota_a2_2, n.nota_a3_2, n.nota_r_2, n.nota_p_p_2,
        n.nota_a1_3, n.nota_a2_3, n.nota_a3_3, n.nota_r_3, n.nota_p_p_3,
        n.nota_a1_4, n.nota_a2_4, n.nota_a3_4, n.nota_r_4, n.nota_p_p_4,
        n.nota_a1_5, n.nota_a2_5, n.nota_a3_5, n.nota_r_5, n.nota_p_p_5,
        n.recuperacion, n.nota_recuperacion_2, n.nota_final
    FROM alumno a
    INNER JOIN alumno_matricula am
        ON a.id_alumno = am.codigo_alumno
        AND am.retirado = 'f'
        AND am.codigo_ann_lectivo = :annlectivo
    INNER JOIN nota n
        ON n.codigo_alumno = a.id_alumno
        AND n.codigo_matricula = am.id_alumno_matricula
    INNER JOIN asignatura asig
        ON asig.codigo = n.codigo_asignatura
    WHERE a.id_alumno = :id_alumno
    ORDER BY n.orden
";
$rows = $pdo->prepare($sqlNotas);
$rows->execute([
    ':id_alumno' => $est['id_alumno'],
    ':annlectivo' => $annlectivo
]);

    // Preparar estructura por asignatura
    $asignaturas = [];
    foreach ($rows as $r) {
        $nombreAsignatura = $r['asignatura'] ?? 'Desconocida';
        
        // Initialize the array for the current asignatura if it doesn't exist
        if (!isset($asignaturas[$nombreAsignatura])) {
            $asignaturas[$nombreAsignatura] = [];
        }

        // Loop through all possible periods and add their data
        // Ensure that the loop only adds data for existing periods based on $cant_periodos
        for ($p = 1; $p <= $cant_periodos; $p++) {
            $asignaturas[$nombreAsignatura][] = [
                'A1' => $r["nota_a1_$p"] ?? null,
                'A2' => $r["nota_a2_$p"] ?? null,
                'A3' => $r["nota_a3_$p"] ?? null,
                'R' => $r["nota_r_$p"] ?? null,
                'PP' => $r["nota_p_p_$p"] ?? null
            ];
        }
        // Add final notes and recuperations to the first period's data
        // Ensure index 0 exists before assigning
        if (isset($asignaturas[$nombreAsignatura][0])) {
            $asignaturas[$nombreAsignatura][0]['R1'] = $r['recuperacion'];
            $asignaturas[$nombreAsignatura][0]['R2'] = $r['nota_recuperacion_2'];
            $asignaturas[$nombreAsignatura][0]['NF'] = $r['nota_final'];
        }
    }

    $pdf->addTabla($asignaturas, $cant_periodos, $calif_minima);
}

// Helper function to clean string for filename (remove special chars and accents)
function limpiar_cadena($cadena) {
    // Attempt to convert to ASCII, transliterating characters
    $cadena = iconv('UTF-8', 'ASCII//TRANSLIT', $cadena); 
    // Remove any characters that are not alphanumeric or underscore
    $cadena = preg_replace('/[^a-zA-Z0-9\s_.-]/', '', $cadena); 
    // Replace spaces with underscores
    $cadena = str_replace(' ', '_', $cadena); 
    // Remove any multiple underscores
    $cadena = preg_replace('/_+/', '_', $cadena);
    // Trim leading/trailing underscores
    $cadena = trim($cadena, '_');
    return $cadena;
}

$pdf->Output("I", $report_filename);