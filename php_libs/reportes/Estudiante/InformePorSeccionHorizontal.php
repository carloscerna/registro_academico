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
// Adjusting substr length based on common usage of gradoseccion being 6 chars (GGSS TT)
$codigo_all = $modalidad . substr($gradoseccion, 0, 6) . $annlectivo; 

// Obtener nombre del docente encargado (revisado por el usuario)
$query_encargado = "
    SELECT btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente,
           eg.codigo_docente
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
if ($row = $stmt_encargado->fetch(PDO::FETCH_ASSOC)) {
    $nombre_encargado = convertirtexto(trim($row['nombre_docente']));
}


// Clase PDF
class PDF extends FPDF {
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
    global $registro_docente, $firma, $sello, $codigo_all, $nombre_encargado;

    $nombre_director = cambiar_de_del($_SESSION['nombre_director'] ?? 'Director');

    // Imágenes firma/sello (si están activadas)
    if ($firma === 'yes') {
        $img_firma = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_firma'] ?? '');
    }
    if ($sello === 'yes') {
        $img_sello = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_sello'] ?? '');
    }

    $this->SetY(-40); // Subir posición final
    $this->SetFont('Arial', 'I', 8);
    
    // Líneas para firmas
    $this->Line(10, $this->GetY() + 15, 80, $this->GetY() + 15);    // Encargado
    $this->Line(120, $this->GetY() + 15, 190, $this->GetY() + 15);  // Director
    $this->Line(5, $this->GetY() + 35, 203, $this->GetY() + 35);    // Línea final

    // Docente encargado
    if (!empty($nombre_encargado)) {
        $this->SetXY(10, $this->GetY() + 18);
        $this->Cell(70, 5, convertirTexto($nombre_encargado), 0, 2, 'L');
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

    // Insertar imágenes si están habilitadas
    if (isset($img_firma) && file_exists($img_firma)) {
        $this->Image($img_firma, 120, $this->GetY() - 20, 70, 15);
    }
    if (isset($img_sello) && file_exists($img_sello)) {
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
            
            // Determine the height needed for the MultiCell for the 'Asignatura' name.
            // Temporarily set X and Y to calculate height without drawing.
            $this->SetXY($current_x, $current_y);
            $this->MultiCell($col_asignatura_width,6,convertirtexto($nombre),0,'L',false);
            $new_y = $this->GetY();
            $cell_height = $new_y - $current_y;
            
            // Now, draw the Asignatura cell and then the rest of the row, all with the calculated height.
            $this->SetXY($current_x, $current_y); // Reset Y to start of row
            $this->MultiCell($col_asignatura_width,$cell_height,convertirtexto($nombre),1,'L');

            // Move cursor to the right of the Asignatura MultiCell, at the original Y position.
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
            
            $this->Ln(); // New line
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
$nombre_grado_seccion_turno = ($desc_data['nombre_grado'] ?? '') . ' ' . ($desc_data['nombre_seccion'] ?? '') . ' ' . ($desc_data['nombre_turno'] ?? '');
$nombre_annlectivo = $desc_data['nombre_annlectivo'] ?? '';


// Obtener estudiantes
$sqlEst = "SELECT a.id_alumno, a.codigo_nie,
        TRIM(CONCAT_WS(', ', a.apellido_paterno, a.apellido_materno, a.nombre_completo)) AS nombre_completo_formato
        FROM alumno a
        INNER JOIN alumno_matricula am ON am.codigo_alumno = a.id_alumno
        WHERE am.codigo_bach_o_ciclo = ?
        AND CONCAT(am.codigo_grado, am.codigo_seccion, am.codigo_turno) = ?
        AND am.codigo_ann_lectivo = ?
        AND am.retirado = false
        ORDER BY nombre_completo_formato"; // Order by the new format
$st = $pdo->prepare($sqlEst);
$st->execute([$modalidad, $gradoseccion, $annlectivo]);
$estudiantes = $st->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new PDF("L", "mm", "A4"); // Orientación Horizontal

foreach ($estudiantes as $est) {
    $pdf->AddPage();
    $pdf->addEstudiante(
        $est['codigo_nie'],
        $est['nombre_completo_formato'],
        $nombre_modalidad, // Changed order based on image
        $nombre_grado_seccion_turno, // Changed order based on image
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
        
        $asignaturas[$nombreAsignatura] = [];

        for ($p = 1; $p <= $cant_periodos; $p++) {
            $asignaturas[$nombreAsignatura][] = [
                'a1' => $r["nota_a1_$p"],
                'a2' => $r["nota_a2_$p"],
                'a3' => $r["nota_a3_$p"],
                'r' => $r["nota_r_$p"],
                'pp' => $r["nota_p_p_$p"]
            ];
        }
        // Add final notes and recuperations to the first period's data
        $asignaturas[$nombreAsignatura][0]['r1'] = $r['recuperacion'];
        $asignaturas[$nombreAsignatura][0]['r2'] = $r['nota_recuperacion_2'];
        $asignaturas[$nombreAsignatura][0]['nota_final'] = $r['nota_final'];
    }

    $pdf->addTabla($asignaturas, $cant_periodos, $calif_minima);
}

$pdf->Output("I", "Informe_Seccion_Horizontal.pdf");