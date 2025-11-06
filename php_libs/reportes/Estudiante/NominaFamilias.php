<?php
// <-- VERSIÓN REFACTORIZADA Y SEGURA: Familias.php -->

// Establecer la zona horaria correcta para El Salvador
date_default_timezone_set('America/El_Salvador');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

/**
 * Clase FPDF personalizada para el reporte de Familias.
 */
class PDF_Familias extends FPDF {
    function Header() {
        $logoPath = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . ($_SESSION['logo_uno'] ?? 'logo_default.png');
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 10, 8, 15);
        }

        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 6, convertirtexto($_SESSION['institucion']), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 8, convertirtexto('INFORME DE GRUPOS FAMILIARES'), 0, 1, 'C');
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d');
        $mes = $meses[date('n') - 1];
        $anio = date('Y');
        $fechaFormateada = "Santa Ana, $dia de $mes de $anio";
        $horaFormateada = date('g:i a');
        $textoFooter = "$fechaFormateada - $horaFormateada | Pagina " . $this->PageNo() . ' de {nb}';
        $this->Cell(0, 10, convertirtexto($textoFooter), 0, 0, 'C');
    }
}

/**
 * Calcula la edad (en años) a partir de la fecha de nacimiento.
 * @param string|null $fecha_nacimiento Fecha en formato 'YYYY-MM-DD'
 * @return int Edad en años.
 */
function calcularEdades(?string $fecha_nacimiento): int {
    if (empty($fecha_nacimiento) || $fecha_nacimiento === '0000-00-00') {
        return 0; // Se considera 0 si no hay fecha
    }
    try {
        $fechaNac = new DateTime($fecha_nacimiento);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNac);
        return $edad->y;
    } catch (Exception $e) {
        return 0; // Error al parsear fecha
    }
}


/**
 * Obtiene todos los datos para el informe de familias.
 */
function obtenerDatosFamilias(PDO $pdo, string $codigo_ann_lectivo): array {
    $datos = [];

    // --- Se mantiene la consulta con 'am.codigo_turno' ---
    $query_estudiantes = "SELECT ae.dui AS dui_encargado, TRIM(ae.nombres) AS nombre_encargado,
                          TRIM(a.nombre_completo || ' ' || a.apellido_paterno || ' ' || a.apellido_materno) AS nombre_estudiante,
                          TRIM(g.nombre) || ' ' || TRIM(s.nombre) AS grado_seccion, a.codigo_genero, a.fecha_nacimiento,
                          am.codigo_turno
                          FROM public.alumno_encargado ae
                          INNER JOIN public.alumno a ON a.id_alumno = ae.codigo_alumno
                          INNER JOIN public.alumno_matricula am ON am.codigo_alumno = a.id_alumno
                          INNER JOIN public.grado_ano g ON g.codigo = am.codigo_grado
                          INNER JOIN public.seccion s ON s.codigo = am.codigo_seccion
                          WHERE am.codigo_ann_lectivo = :codigo_ann_lectivo AND am.retirado = 'f' AND ae.encargado = 't' AND ae.dui IS NOT NULL AND ae.dui != ''
                          ORDER BY nombre_encargado, nombre_estudiante";
    
    $stmt = $pdo->prepare($query_estudiantes);
    $stmt->bindParam(':codigo_ann_lectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
    $stmt->execute();
    $datos['lista_completa'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para resumen de encargados
    $query_encargados = "SELECT DISTINCT ae.dui, ae.codigo_genero, cf.descripcion AS parentesco
                         FROM public.alumno_encargado ae
                         INNER JOIN public.catalogo_familiar cf ON ae.codigo_familiar = cf.codigo
                         WHERE ae.encargado = 't' AND ae.dui IS NOT NULL AND ae.dui != ''
                         AND ae.codigo_alumno IN (SELECT m.codigo_alumno FROM public.alumno_matricula m WHERE m.codigo_ann_lectivo = :codigo_ann_lectivo AND m.retirado = 'f')";
    $stmt = $pdo->prepare($query_encargados);
    $stmt->bindParam(':codigo_ann_lectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
    $stmt->execute();
    $datos['encargados_resumen'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfFamilias(array $datos) {
    // --- PROCESAMIENTO DE DATOS PARA RESÚMENES ---
    $total_estudiantes = count($datos['lista_completa']);
    $total_masculino = 0;
    $total_femenino = 0;
    $familias_agrupadas = [];

    // Array para resumen de turnos
    $resumen_turnos = [
        'Matutino'   => ['M' => 0, 'F' => 0, 'Total' => 0],
        'Vespertino' => ['M' => 0, 'F' => 0, 'Total' => 0],
        'Nocturna'   => ['M' => 0, 'F' => 0, 'Total' => 0],
    ];

    // Array para resumen de edades
    $resumen_edades = [
        '0-5'   => ['M' => 0, 'F' => 0, 'Total' => 0], '6-9'   => ['M' => 0, 'F' => 0, 'Total' => 0],
        '10-12' => ['M' => 0, 'F' => 0, 'Total' => 0], '13-15' => ['M' => 0, 'F' => 0, 'Total' => 0],
        '16-17' => ['M' => 0, 'F' => 0, 'Total' => 0], '18-24' => ['M' => 0, 'F' => 0, 'Total' => 0],
        '25-54' => ['M' => 0, 'F' => 0, 'Total' => 0], '55-64' => ['M' => 0, 'F' => 0, 'Total' => 0],
        '65+'   => ['M' => 0, 'F' => 0, 'Total' => 0],
        'Total_Ninez' => ['M' => 0, 'F' => 0, 'Total' => 0],
        'Total_Adol'  => ['M' => 0, 'F' => 0, 'Total' => 0],
        'Total_Adult' => ['M' => 0, 'F' => 0, 'Total' => 0],
    ];

    // --- NUEVO (Paso 1): Inicializar array para cruce Turno/Edad ---
    $categorias_edad_grupos = ['Total_Ninez', 'Total_Adol', 'Total_Adult', 'Gran_Total'];
    $turnos_grupos = ['Matutino', 'Vespertino', 'Nocturna'];
    $resumen_turno_edad = [];

    foreach ($turnos_grupos as $turno) {
        foreach ($categorias_edad_grupos as $grupo) {
            $resumen_turno_edad[$turno][$grupo] = ['M' => 0, 'F' => 0, 'Total' => 0];
        }
    }
    // --- FIN NUEVO ---


    foreach ($datos['lista_completa'] as $row) {
        $familias_agrupadas[$row['dui_encargado']] = true; 
        
        $genero_key = 'F'; // Default Femenino
        if (trim($row['codigo_genero']) == '01') { 
            $total_masculino++;
            $genero_key = 'M';
        }
        if (trim($row['codigo_genero']) == '02') { 
            $total_femenino++;
        }

        // Clasificación por turno
        $codigo_turno = trim($row['codigo_turno']);
        $turno_key = null;
        
        if ($codigo_turno == '01' || $codigo_turno == '04') { $turno_key = 'Matutino'; }
        elseif ($codigo_turno == '02') { $turno_key = 'Vespertino'; }
        elseif ($codigo_turno == '03') { $turno_key = 'Nocturna'; }

        if ($turno_key) {
            $resumen_turnos[$turno_key][$genero_key]++;
            $resumen_turnos[$turno_key]['Total']++;
        }

        // Cálculo y clasificación por edades
        $edad = calcularEdades($row['fecha_nacimiento'] ?? null);
        $categoria = null;
        $grupo = null;

        if ($edad >= 0 && $edad <= 5)       { $categoria = '0-5';   $grupo = 'Total_Ninez'; }
        elseif ($edad >= 6 && $edad <= 9)   { $categoria = '6-9';   $grupo = 'Total_Ninez'; }
        elseif ($edad >= 10 && $edad <= 12) { $categoria = '10-12'; $grupo = 'Total_Ninez'; }
        elseif ($edad >= 13 && $edad <= 15) { $categoria = '13-15'; $grupo = 'Total_Adol';  }
        elseif ($edad >= 16 && $edad <= 17) { $categoria = '16-17'; $grupo = 'Total_Adol';  }
        elseif ($edad >= 18 && $edad <= 24) { $categoria = '18-24'; $grupo = 'Total_Adult'; }
        elseif ($edad >= 25 && $edad <= 54) { $categoria = '25-54'; $grupo = 'Total_Adult'; }
        elseif ($edad >= 55 && $edad <= 64) { $categoria = '55-64'; $grupo = 'Total_Adult'; }
        elseif ($edad >= 65)                { $categoria = '65+';   $grupo = 'Total_Adult'; }
        
        if ($categoria && $grupo) {
            $resumen_edades[$categoria][$genero_key]++;
            $resumen_edades[$categoria]['Total']++;
            $resumen_edades[$grupo][$genero_key]++;
            $resumen_edades[$grupo]['Total']++;
        }

        // --- NUEVO (Paso 2): Combinación Turno/Edad ---
        if ($turno_key && $grupo) {
            $resumen_turno_edad[$turno_key][$grupo][$genero_key]++;
            $resumen_turno_edad[$turno_key][$grupo]['Total']++;
            // Total general por turno
            $resumen_turno_edad[$turno_key]['Gran_Total'][$genero_key]++;
            $resumen_turno_edad[$turno_key]['Gran_Total']['Total']++;
        }
        // --- FIN NUEVO ---
    }
    $total_familias = count($familias_agrupadas);

    // Procesamiento de encargados
    $resumen_enc_genero = ['M' => 0, 'F' => 0];
    $resumen_parentesco = [];
    foreach ($datos['encargados_resumen'] as $encargado) {
        if (trim($encargado['codigo_genero']) == '01') { $resumen_enc_genero['M']++; }
        if (trim($encargado['codigo_genero']) == '02') { $resumen_enc_genero['F']++; }
        $parentesco = trim($encargado['parentesco']);
        if (!isset($resumen_parentesco[$parentesco])) { $resumen_parentesco[$parentesco] = 0; }
        $resumen_parentesco[$parentesco]++;
    }
    ksort($resumen_parentesco);

    // --- GENERACIÓN DEL PDF ---
    $pdf = new PDF_Familias('L', 'mm', 'Letter');
    $pdf->AliasNbPages();
    $pdf->AddPage();

    // Tabla principal (Nómina)
    $header = ['N#', 'Nombre del Encargado', 'DUI', 'Nombre del Estudiante', 'Grado y Sección'];
    $widths = [10, 75, 25, 75, 45];
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(50, 50, 50); $pdf->SetTextColor(255);
    for ($i = 0; $i < count($header); $i++) { $pdf->Cell($widths[$i], 7, convertirtexto($header[$i]), 1, 0, 'C', true); }
    $pdf->Ln();

    $pdf->SetFont('', '', 8);
    $pdf->SetFillColor(240, 240, 240); $pdf->SetTextColor(0);
    $fill = false;
    $previousDui = null;
    foreach ($datos['lista_completa'] as $index => $row) {
        $encargadoToShow = ($row['dui_encargado'] !== $previousDui) ? $row['nombre_encargado'] : '';
        $duiToShow = ($row['dui_encargado'] !== $previousDui) ? $row['dui_encargado'] : '';
        $previousDui = $row['dui_encargado'];

        $pdf->Cell($widths[0], 6, $index + 1, 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[1], 6, convertirtexto($encargadoToShow), 'LR', 0, 'L', $fill);
        $pdf->Cell($widths[2], 6, $duiToShow, 'LR', 0, 'C', $fill);
        $pdf->Cell($widths[3], 6, convertirtexto($row['nombre_estudiante']), 'LR', 0, 'L', $fill);
        $pdf->Cell($widths[4], 6, convertirtexto($row['grado_seccion']), 'LR', 0, 'L', $fill);
        $pdf->Ln();
        $fill = !$fill;
    }
    $pdf->Cell(array_sum($widths), 0, '', 'T');
    
    // Página de Resúmenes (Familias y Encargados)
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirtexto('Resumen General de Estudiantes por Familia'), 0, 1, 'C');
    $pdf->Ln(5);
    
    $header_summary = ['Descripción', 'Cantidad Total', 'Promedio por Familia'];
    $data_summary = [
        ['Estudiantes Masculinos', $total_masculino, ($total_familias > 0) ? number_format($total_masculino / $total_familias, 2) : 0],
        ['Estudiantes Femeninos', $total_femenino, ($total_familias > 0) ? number_format($total_femenino / $total_familias, 2) : 0],
        ['Subtotal de Estudiantes', $total_estudiantes, ($total_familias > 0) ? number_format($total_estudiantes / $total_familias, 2) : 0]
    ];
    $pdf->SetFont('Arial','B',10); $pdf->SetFillColor(230,230,230); $w = [80, 40, 50];
    for($i=0;$i<count($header_summary);$i++) { $pdf->Cell($w[$i],7,convertirtexto($header_summary[$i]),1,0,'C',true); }
    $pdf->Ln(); $pdf->SetFont('','',9);
    foreach($data_summary as $row) { $pdf->Cell($w[0],6,convertirtexto($row[0]),'LR',0,'L'); $pdf->Cell($w[1],6,$row[1],'LR',0,'C'); $pdf->Cell($w[2],6,$row[2],'LR',0,'C'); $pdf->Ln(); }
    $pdf->Cell(array_sum($w),0,'','T');

    $pdf->Ln(15);
    $pdf->SetFont('Arial', 'B', 12); $pdf->Cell(0, 10, convertirtexto('Resumen de Encargados Principales'), 0, 1, 'C'); $pdf->Ln(5);
    $y_pos_initial = $pdf->GetY();
    $pdf->SetX(60);
    $header_gen = ['Género del Encargado', 'Cantidad']; $data_gen = [['Masculino', $resumen_enc_genero['M']], ['Femenino', $resumen_enc_genero['F']]];
    $w_gen = [50, 25];
    $pdf->SetFont('Arial','B',10);
    for($i=0;$i<count($header_gen);$i++) { $pdf->Cell($w_gen[$i],7,convertirtexto($header_gen[$i]),1,0,'C',true); }
    $pdf->Ln(); $pdf->SetFont('','',9);
    foreach($data_gen as $row) { $pdf->SetX(60); $pdf->Cell($w_gen[0],6,convertirtexto($row[0]),'LR',0,'L'); $pdf->Cell($w_gen[1],6,$row[1],'LR',0,'C'); $pdf->Ln(); }
    $pdf->SetX(60); $pdf->Cell(array_sum($w_gen),0,'','T');
    $pdf->SetY($y_pos_initial);
    $pdf->SetX(145);
    $header_par = ['Parentesco', 'Cantidad']; $data_par = []; foreach($resumen_parentesco as $p => $c){ $data_par[] = [$p, $c]; }
    $w_par = [50, 25];
    $pdf->SetFont('Arial','B',10);
    for($i=0;$i<count($header_par);$i++) { $pdf->Cell($w_par[$i],7,convertirtexto($header_par[$i]),1,0,'C',true); }
    $pdf->Ln(); $pdf->SetFont('','',9);
    foreach($data_par as $row) { $pdf->SetX(145); $pdf->Cell($w_par[0],6,convertirtexto($row[0]),'LR',0,'L'); $pdf->Cell($w_par[1],6,$row[1],'LR',0,'C'); $pdf->Ln(); }
    $pdf->SetX(145); $pdf->Cell(array_sum($w_par),0,'','T');


    // PÁGINA Resumen por Turnos
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirtexto('Resumen de Población Estudiantil por Turno'), 0, 1, 'C');
    $pdf->Ln(5);
    $header_turno = ['Turno', 'Masculino', 'Femenino', 'Total'];
    $w_turno = [80, 30, 30, 30]; 
    $pdf->SetFont('Arial','B',10); $pdf->SetFillColor(230,230,230);
    $pdf->SetX(50); 
    for($i=0;$i<count($header_turno);$i++) { $pdf->Cell($w_turno[$i], 7, convertirtexto($header_turno[$i]), 1, 0, 'C', true); }
    $pdf->Ln();
    $pdf->SetFont('','',9);
    $fill = false;
    $turnos_data = [
        'Matutino (incluye Jornada Completa)' => $resumen_turnos['Matutino'],
        'Vespertino' => $resumen_turnos['Vespertino'],
        'Nocturna'   => $resumen_turnos['Nocturna'],
    ];
    foreach($turnos_data as $label => $data) {
        $pdf->SetFillColor(245,245,245); $pdf->SetX(50);
        $pdf->Cell($w_turno[0], 6, convertirtexto($label), 'LR', 0, 'L', $fill);
        $pdf->Cell($w_turno[1], 6, $data['M'], 'LR', 0, 'C', $fill);
        $pdf->Cell($w_turno[2], 6, $data['F'], 'LR', 0, 'C', $fill);
        $pdf->Cell($w_turno[3], 6, $data['Total'], 'LR', 0, 'C', $fill);
        $pdf->Ln(); $fill = !$fill;
    }
    $pdf->SetFont('Arial','B',10); $pdf->SetFillColor(50, 50, 50); $pdf->SetTextColor(255);
    $pdf->SetX(50);
    $pdf->Cell($w_turno[0], 7, convertirtexto("TOTAL GENERAL"), 1, 0, 'R', true);
    $pdf->Cell($w_turno[1], 7, $total_masculino, 1, 0, 'C', true);
    $pdf->Cell($w_turno[2], 7, $total_femenino, 1, 0, 'C', true);
    $pdf->Cell($w_turno[3], 7, $total_estudiantes, 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetTextColor(0);

    // --- NUEVA PÁGINA (Paso 3): Resumen por Turno y Edad ---
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirtexto('Resumen de Población por Turno y Grupo de Edad'), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Función auxiliar interna para dibujar las tablas de cruce
    $dibujarTablaTurnoEdad = function(string $titulo_turno, array $datos_turno) use ($pdf) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, convertirtexto("Turno: $titulo_turno"), 0, 1, 'L');
        
        $header = ['Grupo de Edad', 'Masculino', 'Femenino', 'Total'];
        $w = [80, 30, 30, 30];
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(230,230,230);
        $pdf->SetX(50); // Centrar
        for($i=0;$i<count($header);$i++) { $pdf->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',true); }
        $pdf->Ln();
        
        $pdf->SetFont('','',9);
        
        $grupos_labels = [
            'Total_Ninez' => 'Niñez (0-12)',
            'Total_Adol'  => 'Adolescencia (13-17)',
            'Total_Adult' => 'Adultez (18+)'
        ];
        
        $fill = false;
        foreach($grupos_labels as $key => $label) {
            $pdf->SetFillColor(245,245,245);
            $pdf->SetX(50);
            $pdf->Cell($w[0], 6, convertirtexto($label), 'LR', 0, 'L', $fill);
            $pdf->Cell($w[1], 6, $datos_turno[$key]['M'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[2], 6, $datos_turno[$key]['F'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[3], 6, $datos_turno[$key]['Total'], 'LR', 0, 'C', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
        
        // Fila Total
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFillColor(230,230,230);
        $pdf->SetX(50);
        $pdf->Cell($w[0], 7, convertirtexto("Total $titulo_turno"), 1, 0, 'R', true);
        $pdf->Cell($w[1], 7, $datos_turno['Gran_Total']['M'], 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, $datos_turno['Gran_Total']['F'], 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, $datos_turno['Gran_Total']['Total'], 1, 0, 'C', true);
        $pdf->Ln(10); // Espacio entre tablas
    };

    // Dibujar las tablas de cruce
    $dibujarTablaTurnoEdad('Matutino (incluye Jornada Completa)', $resumen_turno_edad['Matutino']);
    $dibujarTablaTurnoEdad('Vespertino', $resumen_turno_edad['Vespertino']);
    $dibujarTablaTurnoEdad('Nocturna', $resumen_turno_edad['Nocturna']);
    // --- FIN NUEVA PÁGINA ---


    // PÁGINA Resumen por Edades (Global)
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirtexto('Resumen de la Población Estudiantil según su Edad (Global)'), 0, 1, 'C');
    $pdf->Ln(5);

    $dibujarTablaEdad = function(string $titulo, array $categorias, array $datos, string $total_grupo) use ($pdf) {
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(0, 8, convertirtexto($titulo), 0, 1, 'L');
        $header = ['Grupo de Edad', 'Masculino', 'Femenino', 'Total'];
        $w = [80, 30, 30, 30];
        $pdf->SetFont('Arial','B',10);
        $pdf->SetFillColor(230,230,230);
        $pdf->SetX(50); 
        for($i=0;$i<count($header);$i++) { $pdf->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',true); }
        $pdf->Ln();
        $pdf->SetFont('','',9);
        $fill = false;
        foreach($categorias as $key => $label) {
            $pdf->SetFillColor(245,245,245);
            $pdf->SetX(50);
            $pdf->Cell($w[0], 6, convertirtexto($label), 'LR', 0, 'L', $fill);
            $pdf->Cell($w[1], 6, $datos[$key]['M'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[2], 6, $datos[$key]['F'], 'LR', 0, 'C', $fill);
            $pdf->Cell($w[3], 6, $datos[$key]['Total'], 'LR', 0, 'C', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
        $pdf->SetFont('Arial','B',9);
        $pdf->SetFillColor(230,230,230);
        $pdf->SetX(50);
        $pdf->Cell($w[0], 7, convertirtexto("Total $titulo"), 1, 0, 'R', true);
        $pdf->Cell($w[1], 7, $datos[$total_grupo]['M'], 1, 0, 'C', true);
        $pdf->Cell($w[2], 7, $datos[$total_grupo]['F'], 1, 0, 'C', true);
        $pdf->Cell($w[3], 7, $datos[$total_grupo]['Total'], 1, 0, 'C', true);
        $pdf->Ln(10);
    };

    $cat_ninez = [
        '0-5'   => 'Primera infancia (0-5 años)', 
        '6-9'   => 'Infancia media (6-9 años)', 
        '10-12' => 'Infancia tardía (10-12 años)'
    ];
    $dibujarTablaEdad('Niñez (0 a 12 años)', $cat_ninez, $resumen_edades, 'Total_Ninez');
    $cat_adol = [
        '13-15' => 'Adolescencia temprana (13-15 años)', 
        '16-17' => 'Adolescencia tardía (16-17 años)'
    ];
    $dibujarTablaEdad('Adolescencia (13 a 17 años)', $cat_adol, $resumen_edades, 'Total_Adol');
    $cat_adult = [
        '18-24' => 'Juventud adulta (18-24 años)', 
        '25-54' => 'Adultez plena (25-54 años)', 
        '55-64' => 'Adultez madura (55-64 años)', 
        '65+'   => 'Tercera edad (65+ años)'
    ];
    $dibujarTablaEdad('Adultez (18 años o más)', $cat_adult, $resumen_edades, 'Total_Adult');
    
    // Tabla de Gran Total (Global)
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, convertirtexto('Total General de Estudiantes (Global)'), 0, 1, 'C');
    $pdf->SetX(50);
    $header_total = ['Descripción', 'Masculino', 'Femenino', 'Total General'];
    $w_total = [80, 30, 30, 30];
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(230,230,230);
    for($i=0;$i<count($header_total);$i++) { $pdf->Cell($w_total[$i],7,convertirtexto($header_total[$i]),1,0,'C',true); }
    $pdf->Ln();
    $pdf->SetFont('','',9);
    $pdf->SetX(50);
    $pdf->Cell($w_total[0], 6, convertirtexto("Total Niñez (0-12)"), 'LR', 0, 'L');
    $pdf->Cell($w_total[1], 6, $resumen_edades['Total_Ninez']['M'], 'LR', 0, 'C');
    $pdf->Cell($w_total[2], 6, $resumen_edades['Total_Ninez']['F'], 'LR', 0, 'C');
    $pdf->Cell($w_total[3], 6, $resumen_edades['Total_Ninez']['Total'], 'LR', 0, 'C');
    $pdf->Ln();
    $pdf->SetX(50);
    $pdf->Cell($w_total[0], 6, convertirtexto("Total Adolescencia (13-17)"), 'LR', 0, 'L');
    $pdf->Cell($w_total[1], 6, $resumen_edades['Total_Adol']['M'], 'LR', 0, 'C');
    $pdf->Cell($w_total[2], 6, $resumen_edades['Total_Adol']['F'], 'LR', 0, 'C');
    $pdf->Cell($w_total[3], 6, $resumen_edades['Total_Adol']['Total'], 'LR', 0, 'C');
    $pdf->Ln();
    $pdf->SetX(50);
    $pdf->Cell($w_total[0], 6, convertirtexto("Total Adultez (18+)"), 'LR', 0, 'L');
    $pdf->Cell($w_total[1], 6, $resumen_edades['Total_Adult']['M'], 'LR', 0, 'C');
    $pdf->Cell($w_total[2], 6, $resumen_edades['Total_Adult']['F'], 'LR', 0, 'C');
    $pdf->Cell($w_total[3], 6, $resumen_edades['Total_Adult']['Total'], 'LR', 0, 'C');
    $pdf->Ln();
    $pdf->SetFont('Arial','B',10);
    $pdf->SetFillColor(50, 50, 50); $pdf->SetTextColor(255);
    $pdf->SetX(50);
    $pdf->Cell($w_total[0], 7, convertirtexto("TOTAL GENERAL"), 1, 0, 'R', true);
    $pdf->Cell($w_total[1], 7, $total_masculino, 1, 0, 'C', true);
    $pdf->Cell($w_total[2], 7, $total_femenino, 1, 0, 'C', true);
    $pdf->Cell($w_total[3], 7, $total_estudiantes, 1, 0, 'C', true);
    $pdf->Ln();
    $pdf->SetTextColor(0);

    $pdf->Output('I', 'NominaFamilias.pdf');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }
    
    $codigo_ann_lectivo = $_GET["ann_lectivo"] ?? null;
    if (!$codigo_ann_lectivo) { throw new Exception("Falta el parámetro del año lectivo."); }

    $datosReporte = obtenerDatosFamilias($dblink, $codigo_ann_lectivo);

    if (empty($datosReporte['lista_completa'])) {
        echo "No se encontraron datos de familias para el año lectivo seleccionado.";
        exit;
    }

    generarPdfFamilias($datosReporte);

} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>