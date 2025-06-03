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
$calif_minima = 6; // Calificación mínima para aprobar

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
        // Encabezado del PDF
        $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno']; //Logo
        $nombre_institucion = convertirtexto($_SESSION['institucion']);
        $this->Image($img, 10, 10, 20); // Logo

        // Nombre de la institución
        $this->SetXY(30, 10);
        $this->Cell(0, 6, $nombre_institucion, 0, 1, 'L');

        // Título del informe en dos líneas, centrado
        $this->SetFont('Arial','B',10); // Negrita para el título
        $this->Cell(0, 5, convertirtexto('INFORME ACADÉMICO - POR ESTUDIANTE'), 0, 1, 'C'); // Primera línea del título
        $this->Ln(1);
    }
    function Footer() {
        global $registro_docente, $firma, $sello;

        $nombre_director = cambiar_de_del($_SESSION['nombre_director'] ?? 'Director');

        // Rutas de imágenes
        $img_firma_director = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_firma'] ?? '');
        $img_sello = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['imagen_sello'] ?? '');

        $this->SetY(-45); // Posicionar desde abajo
        $y = $this->GetY();

        // Firmas - líneas horizontales
        $this->Line(10, $y + 25, 80, $y + 25);   // Línea Docente
        $this->Line(120, $y + 25, 190, $y + 25); // Línea Director
        $this->Line(5, $y + 42, 203, $y + 42);   // Línea final horizontal

        // Firma del Docente Encargado
        if (!empty($this->nombre_encargado)) {
            if (!empty($firma_encargado_path) && file_exists($firma_encargado_path)) {
                // Ajustar tamaño de la firma del docente
                $this->Image($firma_encargado_path, 28, $y + 12, 35, 10); // Reducido de 40x12 a 35x10
            }
            $this->SetXY(10, $y + 27);
            $this->SetFont('Arial', 'I', 7); // Reducido de 8 a 7
            $this->Cell(70, 4, ($this->nombre_encargado), 0, 2, 'L');
            $this->Cell(70, 4, convertirTexto("Docente encargado de la sección"), 0, 0, 'L');
        } else {
            $this->SetXY(10, $y + 27);
            $this->SetFont('Arial', 'I', 7); // Reducido de 8 a 7
            $this->Cell(70, 4, ($registro_docente ?? ''), 0, 2, 'L');
            $this->Cell(70, 4, convertirTexto("Encargado Registro Académico"), 0, 0, 'L');
        }

        // Firma del Director
        // Imagen de firma del director
        if (file_exists($img_firma_director)) {
            // Ajustar tamaño de la firma del director
            $this->Image($img_firma_director, 140, $y - 2, 25, 30); // Reducido de 30x36 a 25x30
        }

        // Imagen del sello
        if (file_exists($img_sello)) {
            // Ajustar tamaño del sello
            $this->Image($img_sello, 160, $y + 5, 20, 20); // Reducido de 25x25 a 20x20
        }

        // Nombre del director
        $this->SetXY(120, $y + 27);
        $this->SetFont('Arial', 'I', 7); // Reducido de 8 a 7
        $this->Cell(70, 4, ($nombre_director), 0, 2, 'C');
        $this->Cell(70, 4, "Director(a)", 0, 0, 'C');
    }


    // Función para añadir datos del estudiante en el formato especificado
    function addEstudiante($nie, $nombre, $modalidad, $grado_seccion_turno, $ann_lectivo, $foto, $codigo_genero) {
        $this->SetFont('Arial','',10);
        
        // --- Colocación de la Imagen ---
        $imgWidth = 25;
        $imgHeight = 30;
        $imgX = $this->GetPageWidth() - 10 - $imgWidth; // 10mm de margen derecho
        $imgY = $this->GetY()- 20; // Posición Y actual para el bloque del estudiante

        $photoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/fotos/' . ($_SESSION['codigo_institucion'] ?? '') . '/' . $foto;
        $avatarPathMale = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/avatar_masculino.png';
        $avatarPathFemale = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/avatar_femenino.png';

        $displayImgPath = '';
        if (!empty($foto) && file_exists($photoPath)) {
            $displayImgPath = $photoPath;
        } else {
            if ($codigo_genero == '01') { // Masculino
                $displayImgPath = $avatarPathMale;
            } else { // Femenino o cualquier otro caso
                $displayImgPath = $avatarPathFemale;
            }
        }

        // Colocar la imagen
        if (!empty($displayImgPath) && file_exists($displayImgPath)) {
            $this->Image($displayImgPath, $imgX, $imgY, $imgWidth, $imgHeight);
        }
        // --- Fin Colocación de la Imagen ---

        // --- Colocación del Texto ---
        // Ajustar celdas de texto para evitar superposición con la imagen
        // El texto se colocará desde la izquierda, hasta la posición X de la imagen
        $textBlockWidth = $imgX - $this->GetX() - 5; // Ancho disponible para el texto (5mm de padding)
        
        // Fila 1: NIE y Nombre.
        $this->SetX(30); // Asegurarse de que el texto comienza en la misma línea que la imagen
        $this->Cell(15,6,'Nombre:',0,0,'L');
        $this->Cell($textBlockWidth / 2 - 35,6,convertirtexto($nombre),0,0,'L'); // Ancho ajustado para el valor Nombre
        $this->Cell(10,6,'NIE:',0,0,'L');
        $this->Cell(30,6,convertirtexto($nie),0,0,'L'); // Ancho ajustado para el valor NIE
        $this->Cell(25,6,convertirTexto('Año Lectivo:'),0,0,'L');
        $this->Cell($textBlockWidth / 2 - 25,6,convertirTexto($ann_lectivo),0,1,'L');

        // Fila 2: Modalidad, Grado/Secc/Turno, Año Lectivo
        $this->SetX(30); // Asegurarse de que el texto comienza en la misma línea que la imagen
        $this->Cell(20,6,'Modalidad:',0,0,'L');
        $this->Cell($textBlockWidth / 2 - 20,6,convertirtexto($modalidad),0,0,'L');
        $this->Cell(35,6,'Grado/Secc/Turno:',0,0,'L');
        $this->Cell($textBlockWidth / 2 - 35,6,convertirtexto($grado_seccion_turno),0,1,'L');
        
        // Mover el cursor hacia abajo más allá de la altura de la imagen para asegurar que el siguiente contenido comience debajo de ella
        $this->SetY(max($this->GetY(), $imgY + $imgHeight + 2)); // 2mm de padding debajo de la imagen
        $this->Ln(2); // Salto de línea adicional
    }

    function addTabla($asignaturas, $cant_periodos, $minima = 6) {
        // Tamaño de fuente para los encabezados (aumentado a 8pt para mejor legibilidad)
        $this->SetFont('Arial','B',8); 
        $this->SetFillColor(230, 230, 230); // Gris claro para la columna PP

        // Ajustes de ancho de columna
        $col_notas_indiv = 6; // Ancho reducido para columnas de notas individuales (A1, A2, A3, R, PP, R1, R2, NF)
        $col_asignatura_width = 85; // Ancho aumentado para la columna de Asignatura (80 + 5)
        $col_res_width = 20; // Ancho aumentado para la columna 'Res.'

        // Calcular ancho total para posicionamiento dinámico
        $total_period_columns_width = $col_notas_indiv * 5 * $cant_periodos;
        $total_final_columns_width = $col_notas_indiv * 3; // R1, R2, NF

        $this->Cell($col_asignatura_width,15,'Asignatura',1,0,'C');

        for ($p = 1; $p <= $cant_periodos; $p++) {
            $this->Cell($col_notas_indiv * 5, 10, "PERIODO $p", 1, 0, 'C');
        }
        $this->Cell($total_final_columns_width, 10, 'Final', 1, 0, 'C'); 
        $this->Cell($col_res_width, 10, 'Res.', 'LRT', 0, 'C'); 
        $this->Ln();

        // Segunda fila de encabezados para componentes individuales del período
        $this->Cell($col_asignatura_width,5,'',0,0); // Espaciador para la columna Asignatura
        for ($i = 0; $i < $cant_periodos; $i++) {
            foreach (['A1','A2','A3','R','PP'] as $et) {
                if ($et == 'PP') {
                    $this->Cell($col_notas_indiv,5,$et,1,0,'C',true); // true para rellenar con color
                } else {
                    $this->Cell($col_notas_indiv,5,$et,1,0,'C');
                }
            }
        }
        foreach (['R1','R2','NF'] as $et) {
            $this->Cell($col_notas_indiv,5,$et,1,0,'C');
        }
        $this->Cell($col_res_width, 5, '', 'LRB', 0, 'C'); // Espaciador para la columna Res.
        $this->Ln();

        // Tamaño de fuente para los datos (aumentado a 8pt para mejor legibilidad)
        $this->SetFont('Arial','',8); 
        
        // Determinar la altura base de la fila según el número de asignaturas
        $num_asignaturas = count($asignaturas);
        $baseRowHeight = 5; // Altura por defecto
        if ($num_asignaturas <= 10) {
            $baseRowHeight = 10; // Duplicar la altura si hay 10 o menos asignaturas
        }

        foreach ($asignaturas as $nombre => $data_asignatura) {
            $x = $this->GetX();
            $y = $this->GetY();
            
            // Estimar la altura que ocupará el nombre con MultiCell
            $this->SetFont('Arial','',8);
            $nb = $this->NbLines($col_asignatura_width, convertirtexto($nombre));
            $rowHeight = $baseRowHeight * $nb; // Usar la altura base de fila determinada dinámicamente

            // Imprimir MultiCell para Asignatura
            $this->MultiCell($col_asignatura_width, $baseRowHeight, convertirtexto($nombre), 1, 'L'); // Usar $baseRowHeight aquí

            // Establecer posición para las demás celdas de la fila
            $this->SetXY($x + $col_asignatura_width, $y);

            // Notas por período
            for ($i = 1; $i <= $cant_periodos; $i++) {
                foreach (['A1','A2','A3','R','PP'] as $et) {
                    $nota = $data_asignatura[$i][$et] ?? '';
                    if ($et == 'PP') {
                        $this->SetFillColor(230, 230, 230); // Gris claro para la columna PP
                        $this->Cell($col_notas_indiv, $rowHeight, $nota, 1, 0, 'C', true); // true para rellenar
                        $this->SetFillColor(255, 255, 255); // Restablecer color de relleno
                    } else {
                        $this->Cell($col_notas_indiv, $rowHeight, $nota, 1, 0, 'C');
                    }
                }
            }

            // R1, R2, NF
            foreach (['R1','R2','NF'] as $et) {
                $nota = $data_asignatura['Final'][$et] ?? '';
                $this->Cell($col_notas_indiv, $rowHeight, $nota, 1, 0, 'C');
            }

            // Resultado
            $resultado = $data_asignatura['Resultado'] ?? '';
            if ($resultado == 'Reprobado') {
                $this->SetTextColor(255, 0, 0); // Rojo
            } else {
                $this->SetTextColor(0, 0, 0); // Negro
            }
            $this->Cell($col_res_width, $rowHeight, $resultado, 1, 0, 'C');
            $this->SetTextColor(0, 0, 0); // Restablecer color de texto

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
$nombre_grado_seccion_turno = trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' ' . trim($nombre_turno);
$nombre_annlectivo = trim($desc_data['nombre_annlectivo']) ?? '';


// Obtener estudiantes (ahora incluyendo foto y codigo_genero)
$sqlEst = "SELECT a.id_alumno, a.codigo_nie, a.foto, a.codigo_genero,
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

// Establecer propiedades del pie de página
$pdf->nombre_encargado = $nombre_encargado;
$pdf->firma_docente_filename = $firma_docente_filename;

// Construir el nombre del archivo usando nombres descriptivos
$report_filename = "Informe_" . limpiar_cadena($nombre_modalidad) . "_" . limpiar_cadena($nombre_grado) . "_" . limpiar_cadena($nombre_seccion) . "_" . limpiar_cadena($nombre_annlectivo) . ".pdf";

foreach ($estudiantes as $est) {
    $pdf->AddPage();

    // Reconstruir el nombre completo para mostrar: nombre_completo apellido_paterno apellido_materno
    $full_student_name = trim($est['nombres'] . ' ' . $est['apellido_paterno'] . ' ' . $est['apellido_materno']);
    $full_student_name = preg_replace('/\s+/', ' ', $full_student_name); // Reemplazar múltiples espacios con uno solo


    $pdf->addEstudiante(
        $est['codigo_nie'],
        $full_student_name,
        $nombre_modalidad,
        $nombre_grado_seccion_turno,
        $nombre_annlectivo,
        $est['foto'], // Pasar el nombre del archivo de la foto
        $est['codigo_genero']  // Pasar el código de género del estudiante
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
        
        // Inicializar el array para la asignatura actual si no existe
        if (!isset($asignaturas[$nombreAsignatura])) {
            $asignaturas[$nombreAsignatura] = [];
        }

        // Recorrer todos los períodos posibles y añadir sus datos
        // Asegurarse de que el bucle solo añade datos para los períodos existentes según $cant_periodos
        for ($p = 1; $p <= $cant_periodos; $p++) {
            // Asignar datos del período directamente al número del período
            $asignaturas[$nombreAsignatura][$p] = [
                'A1' => ($r["nota_a1_$p"] == 0 || $r["nota_a1_$p"] === null) ? '' : number_format($r["nota_a1_$p"], 1),
                'A2' => ($r["nota_a2_$p"] == 0 || $r["nota_a2_$p"] === null) ? '' : number_format($r["nota_a2_$p"], 1),
                'A3' => ($r["nota_a3_$p"] == 0 || $r["nota_a3_$p"] === null) ? '' : number_format($r["nota_a3_$p"], 1),
                'R' => ($r["nota_r_$p"] == 0 || $r["nota_r_$p"] === null) ? '' : number_format($r["nota_r_$p"], 1),
                'PP' => ($r["nota_p_p_$p"] == 0 || $r["nota_p_p_$p"] === null) ? '' : number_format($r["nota_p_p_$p"], 1)
            ];
        }
        // Añadir notas finales y recuperaciones directamente a la clave 'Final' dentro de la asignatura
        $recuperacion = ($r['recuperacion'] == 0 || $r['recuperacion'] === null) ? '' : number_format($r['recuperacion'], 1);
        $nota_recuperacion_2 = ($r['nota_recuperacion_2'] == 0 || $r['nota_recuperacion_2'] === null) ? '' : number_format($r['nota_recuperacion_2'], 1);
        $nota_final = ($r['nota_final'] == 0 || $r['nota_final'] === null) ? '' : (int)round($r['nota_final']); // NF como entero

        $asignaturas[$nombreAsignatura]['Final'] = [
            'R1' => $recuperacion,
            'R2' => $nota_recuperacion_2,
            'NF' => $nota_final
        ];

        // Calcular el resultado
        $resultado = '';
        if ($r['nota_final'] !== null && $r['nota_final'] !== '') { // Solo calcular si nota_final existe y no está en blanco
            if ($r['nota_final'] < $calif_minima) {
                $resultado = 'Reprobado';
            } else {
                $resultado = 'Aprobado';
            }
        }
        $asignaturas[$nombreAsignatura]['Resultado'] = $resultado;
    }

    $pdf->addTabla($asignaturas, $cant_periodos, $calif_minima);
}

// Función auxiliar para limpiar cadenas para nombres de archivo (eliminar caracteres especiales y acentos)
function limpiar_cadena($cadena) {
    // Intentar convertir a ASCII, transliterando caracteres
    $cadena = iconv('UTF-8', 'ASCII//TRANSLIT', $cadena); 
    // Eliminar cualquier carácter que no sea alfanumérico o guion bajo
    $cadena = preg_replace('/[^a-zA-Z0-9\s_.-]/', '', $cadena); 
    // Reemplazar espacios con guiones bajos
    $cadena = str_replace(' ', '_', $cadena); 
    // Eliminar guiones bajos múltiples
    $cadena = preg_replace('/_+/', '_', $cadena);
    // Eliminar guiones bajos al principio/final
    $cadena = trim($cadena, '_');
    return $cadena;
}

$pdf->Output("I", $report_filename);
