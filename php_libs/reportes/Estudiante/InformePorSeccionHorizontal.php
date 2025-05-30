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
    $firma_docente_filename = $row['firma_docente_filename'];
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
        global $registro_docente, $firma, $sello; // Remove codigo_all, nombre_encargado as they are now properties

        $nombre_director = cambiar_de_del($_SESSION['nombre_director'] ?? 'Director');

        // Images paths
        $img_firma_director = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_firma'] ?? '');
        $img_sello = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_sello'] ?? '');
        
        // Teacher signature path
        $firma_encargado_path = '';
        if (!empty($this->firma_docente_filename) && isset($_SESSION['codigo_institucion'])) {
            $firma_encargado_path = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/firmas/' . $_SESSION['codigo_institucion'] . '/' . $this->firma_docente_filename;
        }

        $this->SetY(-40); // Subir posición final
        $this->SetFont('Arial', 'I', 8);
        
        // Líneas para firmas
        $this->Line(10, $this->GetY() + 15, 80, $this->GetY() + 15);    // Encargado
        $this->Line(120, $this->GetY() + 15, 190, $this->GetY() + 15);  // Director
        $this->Line(5, $this->GetY() + 35, 203, $this->GetY() + 35);    // Línea final

        // Docente encargado
        if (!empty($this->nombre_encargado)) {
            // Draw signature if exists
            if (!empty($firma_encargado_path) && file_exists($firma_encargado_path)) {
                $this->Image($firma_encargado_path, 25, $this->GetY() + 5, 40, 10); // Adjust position and size as needed
            }
            $this->SetXY(10, $this->GetY() + 18); // Adjust Y after potential image
            $this->Cell(70, 5, convertirTexto($this->nombre_encargado), 0, 2, 'L');
            $this->Cell(70, 5, convertirTexto("Docente encargado de la sección"), 0, 0, 'L');
        } else {
            $this->SetXY(10, $this->GetY() + 18);
            $this->Cell(70, 5, convertirTexto($registro_docente ?? ''), 0, 2, 'L');
            $this->Cell(70, 5, convertirTexto("Encargado Registro Académico"), 0, 0, 'L');
        }

        // Firma Director
        $this->SetXY(120, $this->GetY());
        $this->Cell(70, 5, convertirTexto($nombre_director), 0, 2, 'C');
        $this->Cell(70, 5, convertirTexto("Director(a)"), 0, 0, 'C');

        // Insertar imágenes si están habilitadas (Director's signature and seal)
        if ($firma === 'yes' && file_exists($img_firma_director)) {
            $this->Image($img_firma_director, 120, $this->GetY() - 20, 70, 15);
        }
        if ($sello === 'yes' && file_exists($img_sello)) {
            $this->Image($img_sello, 165, $this->GetY() - 20, 30, 30);
        }
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
            $current_x = $this->GetX();
            $current_y = $this->GetY();
            
            // Calculate height needed for MultiCell for 'Asignatura' name
            // Use a temporary FPDF instance or save/restore state to get height without affecting current document
            $temp_pdf = new FPDF(); // Create a dummy PDF to calculate string height
            $temp_pdf->AddPage();
            $temp_pdf->SetFont('Arial','',8); // Match font settings
            $temp_pdf->MultiCell($col_asignatura_width, 6, convertirtexto($nombre), 0, 'L', false);
            $text_height = $temp_pdf->GetY(); // Get the calculated height
            $cell_height = max($text_height, 6); // Ensure a minimum height if text is short
            $temp_pdf = null; // Destroy temporary object

            // Draw the Asignatura cell (MultiCell)
            $this->MultiCell($col_asignatura_width,$cell_height,convertirtexto($nombre),1,'L');

            // Store current X and Y after MultiCell
            $post_multicell_x = $this->GetX();
            $post_multicell_y = $this->GetY();

            // Set cursor to the right of the MultiCell, at the original Y position, for the rest of the row
            $this->SetXY($current_x + $col_asignatura_width, $current_y);
            
            for ($p = 0; $p < $cant_periodos; $p++) {
                $data = $periodos[$p] ?? [];
                foreach (['a1','a2','a3','r','pp'] as $k) {
                    $v = (isset($data[$k]) && $data[$k] != 0) ? number_format($data[$k],1) : ''; // Blanks for 0
                    $this->Cell($col_notas_indiv,$cell_height,$v,1,0,'C');
                }
            }
            $r1 = $periodos[0]['r1'] ?? 0;
            $r2 = $periodos[0]['r2'] ?? 0;
            $nf = $periodos[0]['nota_final'] ?? 0;
            
            foreach ([$r1,$r2,$nf] as $v) {
                $value_to_display = ($v != 0) ? number_format($v,1) : ''; // Blanks for 0
                $this->Cell($col_notas_indiv,$cell_height,$value_to_display,1,0,'C');
            }
            
            $res = ($nf >= $minima) ? 'Aprobado' : 'Reprobado';
            if ($res === 'Reprobado') {
                $this->SetTextColor(255,0,0); // Red color for Reprobado
            }
            $this->Cell($col_res_width,$cell_height,$res,1,0,'C'); // Use increased width for 'Res.'
            $this->SetTextColor(0,0,0); // Reset color to black
            
            // After drawing all cells for the row, set Y to the new Y position from MultiCell
            $this->SetY($post_multicell_y); 
        }
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
    ORDER BY asig.nombre -- Added ORDER BY for consistent subject display
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
                'a1' => $r["nota_a1_$p"] ?? null,
                'a2' => $r["nota_a2_$p"] ?? null,
                'a3' => $r["nota_a3_$p"] ?? null,
                'r' => $r["nota_r_$p"] ?? null,
                'pp' => $r["nota_p_p_$p"] ?? null
            ];
        }
        // Add final notes and recuperations to the first period's data
        // Ensure index 0 exists before assigning
        if (isset($asignaturas[$nombreAsignatura][0])) {
            $asignaturas[$nombreAsignatura][0]['r1'] = $r['recuperacion'];
            $asignaturas[$nombreAsignatura][0]['r2'] = $r['nota_recuperacion_2'];
            $asignaturas[$nombreAsignatura][0]['nota_final'] = $r['nota_final'];
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