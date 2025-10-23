<?php
// <-- VERSIÓN FINAL CON DISEÑO DETALLADO INTEGRADO -->

date_default_timezone_set('America/El_Salvador');
ini_set('display_errors', 1); error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/funciones_2.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/includes/DeNumero_a_Letras.php"; // Necesario para num2letras
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_CUADRO', 23); // Ajusta según el espacio real que ocupa el diseño

/**
 * Clase FPDF personalizada con el Header y Footer detallados.
 */
class PDF_CuadroRegistro extends FPDF {
    public $datosEncabezado = [];
    public $asignaturas = [];
    public $nombreDocente = '';
    public $estadisticas = []; // Para acceder en el Footer si es necesario

    // --- FUNCIONES DE TEXTO ROTADO ---
    var $angle=0;
    function Rotate($angle,$x=-1,$y=-1) { /* ... código ... */ }
    function _endpage() { /* ... código ... */ }
    function RotatedText($x, $y, $txt, $angle) { /* ... código ... */ }
    function RotatedTextMultiCell($x, $y, $txt, $angle) { /* ... código ... */ }
    function RotatedTextMultiCellAspectos($x, $y, $txt, $angle) { /* ... código ... */ }
    function RotatedTextMultiCellDireccion($x, $y, $txt, $angle) { /* ... código ... */ } // Asegúrate que esta función exista
    // --- FIN FUNCIONES TEXTO ROTADO ---


    function Header()
    {
        // --- Encabezado General (Nombre Institución, MINED, etc.) ---
        $this->SetDrawcolor(0,0,0);
        $this->SetXY(70,10);
        $this->SetFont('Arial','',18);
        $this->Cell(235,14,convertirtexto('REGISTRO DE EVALUACIÓN DEL RENDIMIENTO ESCOLAR DE '.substr($this->datosEncabezado['codigo_grado'],1,1).'.° DE EDUCACIÓN BÁSICA'),0,0,'L');

        $this->SetXY(80,25);
        $this->SetFont('Arial','',11);
        $this->Cell(235,5,convertirtexto('CUADRO FINAL DE EVALUACIÓN DE'),0,2,'L');
        $this->Cell(235,5,convertirtexto('NOMBRE DEL CENTRO EDUCATIVO: ') . convertirtexto($_SESSION['institucion']),0,2,'L');
        $this->Cell(235,5,convertirtexto('DIRECCIÓN: ') . convertirtexto($_SESSION['direccion']),0,2,'L');
        $this->Cell(235,5,'MUNICIPIO: ' . convertirtexto($_SESSION['nombre_municipio']),0,2,'L');

        // Escudo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo.jpg';
        $this->Image($img,35,10,20,20);
        $this->SetFont('Arial','',10);
        $this->SetXY(15,30);
        $this->Cell(60,4,convertirtexto('República de El Salvador'),0,2,'C');
        $this->Cell(60,4,convertirtexto('Ministerio de Educación'),0,2,'C');
        $this->Cell(60,4,convertirtexto('Dirección Nacional de Educación Básica'),0,2,'C');

        // --- Inicio del Diseño Detallado del Cuadro ---
        $y_inicio_cuadro = 45; // Posición Y fija
        $this->SetY($y_inicio_cuadro);

        // Columnas fijas (N#, NIE, Nombre)
        $alto_total_header = 50;
        $this->Rect(10, $y_inicio_cuadro, 7, $alto_total_header); $this->RotatedText(14, $y_inicio_cuadro + 35, convertirtexto('N°'), 90);
        $this->Rect(17, $y_inicio_cuadro, 20, $alto_total_header); $this->RotatedText(27, $y_inicio_cuadro + 35, convertirtexto('NIE'), 90);
        $this->Rect(37, $y_inicio_cuadro, 90, $alto_total_header);
        $this->SetFont('Arial', '', 11); $this->SetXY(40, $y_inicio_cuadro + 15);
        $this->MultiCell(85, 6, convertirtexto('Nombre de los Alumnos(as) en orden alfabético de apellidos'), 0, 'C');

        // Calcular anchos dinámicos para asignaturas
        $num_asig_basicas = 0; $num_asig_conducta = 0;
        foreach ($this->asignaturas as $asig) {
            if (in_array(trim($asig['codigo_area']), ['07'])) { $num_asig_conducta++; } else { $num_asig_basicas++; }
        }
        $ancho_asig = 10;
        $ancho_total_asig = $num_asig_basicas * $ancho_asig;
        $ancho_total_conducta = $num_asig_conducta * $ancho_asig;

        $x_asig = 127; // Posición X inicial asignaturas

        // Títulos Superiores: ASIGNATURA y COMPETENCIAS CIUDADANAS
        $this->SetFont('Arial', 'B', 10);
        $this->Rect($x_asig, $y_inicio_cuadro, $ancho_total_asig, 7);
        $this->SetXY($x_asig, $y_inicio_cuadro + 1); $this->Cell($ancho_total_asig, 5, 'ASIGNATURAS', 0, 0, 'C');

        $x_conducta = $x_asig + $ancho_total_asig;
        $this->Rect($x_conducta, $y_inicio_cuadro, $ancho_total_conducta, 7);
        $this->SetFont('Arial', 'B', 7); $this->SetXY($x_conducta, $y_inicio_cuadro + 1);
        $this->Cell($ancho_total_conducta, 5, convertirtexto('COMPETENCIAS CIUDADANAS'), 0, 0, 'C');

        // Nombres de Asignaturas (Rotados) y sus Rectángulos
        $this->SetFont('Arial', '', 7);
        $x_current = $x_asig;
        $y_nombres_asig = $y_inicio_cuadro + 7;
        $alto_nombres_asig = $alto_total_header - 7 - 10;

        foreach ($this->asignaturas as $asig) {
            $this->Rect($x_current, $y_nombres_asig, $ancho_asig, $alto_nombres_asig);
             // *** AJUSTA LA POSICIÓN Y DEL TEXTO ROTADO SI ES NECESARIO ***
            $this->RotatedTextMultiCell($x_current + 1, $y_nombres_asig + $alto_nombres_asig - 1, convertirtexto($asig['nombre']), 90);
            $x_current += $ancho_asig;
        }

        // Título Inferior: CALIFICACIÓN
        $y_calificacion = $y_inicio_cuadro + $alto_total_header - 10;
        $ancho_total_calificacion = $ancho_total_asig + $ancho_total_conducta;
        $this->Rect($x_asig, $y_calificacion, $ancho_total_calificacion, 10);
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY($x_asig, $y_calificacion + 2);
        $this->Cell($ancho_total_calificacion, 5, convertirtexto('CALIFICACIÓN'), 0, 0, 'C');

        // Columna Resultado Final
        $x_resultado = $x_asig + $ancho_total_calificacion;
        $ancho_resultado = 20;
        $this->Rect($x_resultado, $y_inicio_cuadro, $ancho_resultado, $alto_total_header);
        $this->RotatedText($x_resultado + 7, $y_inicio_cuadro + 35, 'RESULTADO', 90);

        // --- Sección Derecha (Escala, Estadísticas) ---
        // Escala de Calificación Competencias Ciudadanas
        $x_escala = 250; $y_escala = 45;
        $this->Rect($x_escala, $y_escala, 90, 9);
        $this->SetFillColor(206,206,206); $this->Rect($x_escala, $y_escala, 90, 9, "F");
        $this->SetFont('Arial','',9);
        $this->SetXY($x_escala, $y_escala); $this->Cell(90,4.5,convertirtexto('ESCALA DE VALORACIÓN PARA LAS'),'LRT',2,'C');
        $this->SetX($x_escala); $this->Cell(90,4.5,convertirtexto('COMPETENCIAS CIUDADANAS'),'LRB',1,'C');

        $this->SetX($x_escala); $this->Cell(30,8,'E: Excelente',1,0,'L'); $this->Cell(30,8,'MB: Muy Bueno',1,0,'L'); $this->Cell(30,8,'B: Bueno',1,1,'L');

        $this->SetFont('Arial','',8); $this->SetX($x_escala);
        $this->Cell(30,5,'Dominio alto de la','LRT',0,'L'); $this->Cell(30,5,'Dominio medio de la','LRT',0,'L'); $this->Cell(30,5,'Dominio bajo de la','LRT',1,'L');
        $this->SetX($x_escala); $this->Cell(30,5,'competencia','LRB',0,'L'); $this->Cell(30,5,'competencia','LRB',0,'L'); $this->Cell(30,5,'competencia','LRB',1,'L');

        // Tabla de Estadísticas
        $y_stats = 80;
        $this->Rect($x_escala, $y_stats, 90, 30); // Principal
        $this->Rect($x_escala, $y_stats, 18, 30); // Col Sexo
        $this->Rect($x_escala, $y_stats, 90, 15); // Fila Títulos
        $this->Rect($x_escala, $y_stats + 15, 90, 5); // Fila Masc
        $this->Rect($x_escala + 30, $y_stats, 15, 30); // Col Mat Ini
        $this->Rect($x_escala, $y_stats + 20, 90, 5); // Fila Fem
        $this->Rect($x_escala + 45, $y_stats, 15, 30); // Col Ret
        $this->Rect($x_escala + 60, $y_stats, 15, 30); // Col Mat Fin
        $this->Rect($x_escala + 75, $y_stats, 15, 30); // Col Prom

        $this->SetFillColor(206,206,206);
        $this->SetXY($x_escala, $y_stats); $this->Cell(90,5,convertirtexto('ESTADÍSTICA'),1,1,'C', true);
        $this->SetXY($x_escala - 2, $y_stats + 8); $this->Cell(20,5,'SEXO',0,0,'C');

        $this->SetFont('Arial','',7);
        $this->SetXY($x_escala + 18, $y_stats + 6); $this->MultiCell(15,3.5,'Matricula Inicial',0,'C');
        $this->SetXY($x_escala + 33, $y_stats + 6); $this->Cell(15,4.5,'Retirados',0,0,'C');
        $this->SetXY($x_escala + 48, $y_stats + 6); $this->MultiCell(15,3.5,'Matricula Final',0,'C');
        $this->SetXY($x_escala + 63, $y_stats + 6); $this->Cell(15,4.5,'Promovidos',0,0,'C');
        $this->SetXY($x_escala + 78, $y_stats + 6); $this->Cell(15,4.5,'Retenidos',0,0,'C');

        $this->SetFont('Arial','',8);
        $this->SetXY($x_escala, $y_stats + 15); $this->Cell(18,5,'MASCULINO',0,0,'C');
        $this->SetXY($x_escala, $y_stats + 20); $this->Cell(18,5,'FEMENINO',0,0,'C');
        $this->SetXY($x_escala, $y_stats + 25); $this->Cell(18,5,'TOTAL',0,0,'C');

        // Llenar datos de estadísticas (ACCEDE A $this->estadisticas)
        $stats = $this->estadisticas;
        $this->SetFont('Arial','',10);
        // Mat Inicial
        $this->SetXY($x_escala + 18, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_matricula_inicial_masculino'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 18, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_matricula_inicial_femenino'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 18, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_matricula_inicial_masculino'] ?? 0) + ($stats['total_matricula_inicial_femenino'] ?? 0), 0, 0, 'C');
        // Retirados
        $this->SetXY($x_escala + 33, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_matricula_retirados_masculino'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 33, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_matricula_retirados_femenino'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 33, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_matricula_retirados_masculino'] ?? 0) + ($stats['total_matricula_retirados_femenino'] ?? 0), 0, 0, 'C');
        // Mat Final
        $mat_final_m = ($stats['total_matricula_inicial_masculino'] ?? 0) - ($stats['total_matricula_retirados_masculino'] ?? 0);
        $mat_final_f = ($stats['total_matricula_inicial_femenino'] ?? 0) - ($stats['total_matricula_retirados_femenino'] ?? 0);
        $this->SetXY($x_escala + 48, $y_stats + 15.5); $this->Cell(15, 4.5, $mat_final_m, 0, 0, 'C');
        $this->SetXY($x_escala + 48, $y_stats + 20.5); $this->Cell(15, 4.5, $mat_final_f, 0, 0, 'C');
        $this->SetXY($x_escala + 48, $y_stats + 25.5); $this->Cell(15, 4.5, $mat_final_m + $mat_final_f, 0, 0, 'C');
        // Promovidos
        $this->SetXY($x_escala + 63, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_promovidos_m'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 63, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_promovidos_f'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 63, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_promovidos_m'] ?? 0) + ($stats['total_promovidos_f'] ?? 0), 0, 0, 'C');
        // Retenidos
        $this->SetXY($x_escala + 78, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_retenidos_m'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 78, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_retenidos_f'] ?? '0', 0, 0, 'C');
        $this->SetXY($x_escala + 78, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_retenidos_m'] ?? 0) + ($stats['total_retenidos_f'] ?? 0), 0, 0, 'C');

        // --- Fin del Diseño Detallado ---

        // Posición inicial para datos (debajo del encabezado)
        $this->SetY($y_inicio_cuadro + $alto_total_header);
        $this->SetLineWidth(0.2); $this->SetDrawColor(0);
    }
}

// ... (Función obtenerDatosCuadro sin cambios) ...
// ... (Función obtenerDatosCuadro sin cambios significativos, ya obtiene los datos necesarios) ...
function obtenerDatosCuadro(PDO $pdo, string $codigoAll): array {
     $datos = ['encabezado' => [], 'notas' => [], 'asignaturas' => [], 'estadisticas' => []];
     // Consulta Encabezado (con nota mínima y num periodos)
     $sqlEncabezado = "SELECT btrim(bach.nombre) as nombre_bachillerato, am.codigo_bach_o_ciclo, btrim(gan.nombre) as nombre_grado, am.codigo_grado, btrim(sec.nombre) as nombre_seccion, am.codigo_seccion, ann.nombre as nombre_ann_lectivo, am.codigo_ann_lectivo, am.codigo_turno, cp.cantidad_periodos, cp.calificacion_minima FROM alumno_matricula am INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo LEFT JOIN catalogo_periodos cp ON bach.codigo = cp.codigo_modalidad WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all LIMIT 1";
     $stmt = $pdo->prepare($sqlEncabezado); $stmt->bindParam(':codigo_all', $codigoAll); $stmt->execute();
     $datos['encabezado'] = $stmt->fetch(PDO::FETCH_ASSOC); if(!$datos['encabezado']) { throw new Exception("..."); }
     $datos['encabezado']['nota_minima'] = $datos['encabezado']['calificacion_minima'] ?? 5.0;
     // Consulta Docente
     $sqlDocente = "SELECT btrim(p.nombres || ' ' || p.apellidos) as nombre_docente FROM encargado_grado eg INNER JOIN personal p ON eg.codigo_docente = p.id_personal WHERE btrim(eg.codigo_bachillerato::text || eg.codigo_grado::text || eg.codigo_seccion::text || eg.codigo_ann_lectivo::text || eg.codigo_turno::text) = :codigo_all AND eg.encargado = 't' LIMIT 1";
     $stmtDocente = $pdo->prepare($sqlDocente); $stmtDocente->bindParam(':codigo_all', $codigoAll); $stmtDocente->execute(); $docente = $stmtDocente->fetch(PDO::FETCH_ASSOC); $datos['encabezado']['nombre_docente'] = $docente['nombre_docente'] ?? 'No asignado';
     // Consulta Asignaturas
     $sqlAsignaturas = "SELECT DISTINCT asig.codigo, asig.nombre, asig.codigo_area, asig.ordenar FROM asignatura asig INNER JOIN nota n ON asig.codigo = n.codigo_asignatura INNER JOIN alumno_matricula am ON n.codigo_matricula = am.id_alumno_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all AND asig.ordenar > 0 ORDER BY asig.ordenar";
     $stmtAsig = $pdo->prepare($sqlAsignaturas); $stmtAsig->bindParam(':codigo_all', $codigoAll); $stmtAsig->execute();
     if($stmtAsig->rowCount() > 0) { $datos['asignaturas'] = $stmtAsig->fetchAll(PDO::FETCH_ASSOC); } else { $datos['asignaturas'] = []; } // Mantener ordenar aquí
     if(empty($datos['asignaturas'])) { throw new Exception("..."); }
    // Consulta Notas
     $sqlNotas = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as nombre_completo, a.genero, n.codigo_asignatura, n.nota_final, n.recuperacion, n.nota_recuperacion_2 FROM alumno a INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' INNER JOIN nota n ON am.id_alumno_matricula = n.codigo_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all ORDER BY nombre_completo, a.id_alumno, n.codigo_asignatura";
     $stmtNotas = $pdo->prepare($sqlNotas); $stmtNotas->bindParam(':codigo_all', $codigoAll); $stmtNotas->execute(); $datos['notas'] = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);
     // Estadísticas (NECESITA IMPLEMENTACIÓN DETALLADA)
     $stats = calcularEstadisticasFinales($pdo, $codigoAll, $datos['notas'], $datos['asignaturas'], $datos['encabezado']['nota_minima']);
     $datos['estadisticas'] = $stats;
     return $datos;
}

// *** ¡REVISA Y COMPLETA ESTA FUNCIÓN CON TU LÓGICA EXACTA! ***
function calcularEstadisticasFinales($pdo, $codigoAll, $notas, $asignaturas, $notaMinima) {
    $stats = [
        'total_matricula_inicial_masculino' => 0, 'total_matricula_inicial_femenino' => 0,
        'total_matricula_retirados_masculino' => 0, 'total_matricula_retirados_femenino' => 0,
        'total_promovidos_m' => 0, 'total_promovidos_f' => 0, 'total_retenidos_m' => 0, 'total_retenidos_f' => 0,
    ];

    // 1. Calcular promovidos/retenidos procesando $notas
    $alumnosResultados = []; // [id_alumno => 'P' o 'R']
    $notasPorAlumno = [];
    foreach ($notas as $nota) { $notasPorAlumno[$nota['id_alumno']][$nota['codigo_asignatura']] = $nota; }

    foreach($notasPorAlumno as $idAlumno => $notasAlumno) {
        $asignaturasBasicasReprobadas = 0;
        $genero = $notasAlumno[array_key_first($notasAlumno)]['genero']; // Obtener género del primer registro

        foreach($asignaturas as $asig) {
            $notaData = $notasAlumno[$asig['codigo']] ?? null;
            if ($notaData && !in_array(trim($asig['codigo_area']), ['07'])) { // Ignorar conducta
                 $notaFinalVerificada = verificar_nota($notaData['nf'], $notaData['nr1'], $notaData['nr2']);
                 if (floatval($notaFinalVerificada) < $notaMinima) {
                     if (in_array(trim($asig['codigo_area']), ['01', '03'])) { // Ajusta códigos de área básica
                         $asignaturasBasicasReprobadas++;
                     }
                 }
            }
        }
        $resultado = ($asignaturasBasicasReprobadas <= 2) ? 'P' : 'R'; // AJUSTA ESTA REGLA
        $alumnosResultados[$idAlumno] = $resultado;

        if($resultado == 'P') {
            if($genero == 'm') $stats['total_promovidos_m']++; else $stats['total_promovidos_f']++;
        } else {
            if($genero == 'm') $stats['total_retenidos_m']++; else $stats['total_retenidos_f']++;
        }
    }

    // 2. Consultar Matrícula Inicial y Retirados
    $sqlMatIni = "SELECT genero, COUNT(*) as total FROM alumno a JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all GROUP BY genero";
    $stmtMatIni = $pdo->prepare($sqlMatIni); $stmtMatIni->bindParam(':codigo_all', $codigoAll); $stmtMatIni->execute();
    while($row = $stmtMatIni->fetch(PDO::FETCH_ASSOC)) {
        if($row['genero'] == 'm') $stats['total_matricula_inicial_masculino'] = $row['total'];
        if($row['genero'] == 'f') $stats['total_matricula_inicial_femenino'] = $row['total'];
    }

    $sqlRet = "SELECT genero, COUNT(*) as total FROM alumno a JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all AND am.retirado = 't' GROUP BY genero";
    $stmtRet = $pdo->prepare($sqlRet); $stmtRet->bindParam(':codigo_all', $codigoAll); $stmtRet->execute();
     while($row = $stmtRet->fetch(PDO::FETCH_ASSOC)) {
        if($row['genero'] == 'm') $stats['total_matricula_retirados_masculino'] = $row['total'];
        if($row['genero'] == 'f') $stats['total_matricula_retirados_femenino'] = $row['total'];
    }

    return $stats;
}


/**
 * Genera el PDF del reporte con el diseño detallado.
 */
function generarPdfCuadro(array $datos) {
    $notaMinima = floatval($datos['encabezado']['nota_minima']);
    $stats = $datos['estadisticas']; // Pasar estadísticas a la clase

    // --- PROCESAR Y PIVOTAR DATOS DE NOTAS ---
    $alumnosNotas = [];
     foreach ($datos['notas'] as $nota) {
// ▼▼▼ CORRECTION HERE ▼▼▼
        // Use null coalescing (??) for each specific key before passing to the function
        $nf_param = $notaData['nf'] ?? null;
        $nr1_param = $notaData['nr1'] ?? null;
        $nr2_param = $notaData['nr2'] ?? null;

        // Call verificar_nota with guaranteed values (or null)
        $notaFinalVerificada = verificar_nota($nf_param, $nr1_param, $nr2_param);
        // ▲▲▲ END CORRECTION ▲▲▲

        $idAlumno = $nota['id_alumno'];
        if (!isset($alumnosNotas[$idAlumno])) { $alumnosNotas[$idAlumno] = ['info' => ['nombre' => $nota['nombre_completo'], 'nie' => $nota['codigo_nie'], 'genero' => $nota['genero']], 'notas' => []]; }
        $alumnosNotas[$idAlumno]['notas'][$nota['codigo_asignatura']] = ['nf' => $nota['nota_final'], 'nr1' => $nota['recuperacion'], 'nr2' => $nota['nota_recuperacion_2']];
    }

    // --- GENERAR PDF ---
    $pdf = new PDF_CuadroRegistro('L', 'mm', 'Legal');
    $pdf->SetMargins(10, 5, 10); $pdf->SetAutoPageBreak(true, 15); $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];
    $pdf->asignaturas = $datos['asignaturas'];
    $pdf->nombreDocente = $datos['encabezado']['nombre_docente'];
    $pdf->estadisticas = $stats; // Pasar estadísticas

    $pdf->AddPage();
    // El Header() dibuja el encabezado complejo y la estructura de la tabla

    $pdf->SetFont('Arial', '', 7); $fill = false; $numFila = 0;
    $ancho_asig_pdf = 10;
    $promediosAsignatura = array_fill_keys(array_column($datos['asignaturas'], 'codigo'), ['suma' => 0, 'contador' => 0]);

    foreach ($alumnosNotas as $idAlumno => $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_CUADRO == 0) { $pdf->AddPage(); $pdf->SetFont('Arial', '', 7); }
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);

        $pdf->Cell(7, 5, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(20, 5, $alumno['info']['nie'], 1, 0, 'C', true);
        $pdf->Cell(90, 5, convertirtexto($alumno['info']['nombre']), 1, 0, 'L', true);

        $asignaturasBasicasReprobadas = 0;

       foreach ($datos['asignaturas'] as $asig) {
        // Use default array with null values if assignment data doesn't exist for the student
        $notaData = $alumno['notas'][$asig['codigo']] ?? null; // Si no hay notas para esta asignatura, $notaData será null

        // ▼▼▼ CORRECCIÓN DEFINITIVA CON isset() ▼▼▼
        $nf_param = null;
        $nr1_param = null;
        $nr2_param = null;

        // Verificar si $notaData es un array y si las claves existen
        if (is_array($notaData)) {
            $nf_param = isset($notaData['nf']) ? $notaData['nf'] : null;
            $nr1_param = isset($notaData['nr1']) ? $notaData['nr1'] : null;
            $nr2_param = isset($notaData['nr2']) ? $notaData['nr2'] : null;
        }

        // Llamar a verificar_nota con valores seguros (o null)
        $notaFinalVerificada = verificar_nota($nf_param, $nr1_param, $nr2_param);
        // ▲▲▲ FIN CORRECCIÓN ▲▲▲

        $nfVal = floatval($notaFinalVerificada);
        $displayNota = '';

        // ... (resto de la lógica para mostrar la nota en la celda, calcular stats, etc.) ...
         if ($nfVal >= 0) { // Mostrar nota 0 si existe
             if (trim($asig['codigo_area']) == '07') { $displayNota = cambiar_concepto($nfVal); }
             else {
                $displayNota = number_format($nfVal, 0);
                if($nfVal < $notaMinima && $nfVal >= 0) { // Colorear si reprobada (incluye 0 si es menor que nota minima)
                    $pdf->SetTextColor(255, 0, 0);
                    if (in_array(trim($asig['codigo_area']), ['01', '03'])) { $asignaturasBasicasReprobadas++; }
                }
                // Solo promediar notas > 0 o según tu criterio
                if(isset($promediosAsignatura[$asig['codigo']]) && $nfVal > 0) {
                     $promediosAsignatura[$asig['codigo']]['suma'] += $nfVal;
                     $promediosAsignatura[$asig['codigo']]['contador']++;
                }
             }
        }
        $pdf->Cell($ancho_asig_pdf, 5, $displayNota, 1, 0, 'C', true);
        $pdf->SetTextColor(0); // Reset text color
    }
        $resultadoFinal = ($asignaturasBasicasReprobadas <= 2) ? 'P' : 'R'; // AJUSTA LÓGICA
        $pdf->Cell(20, 5, $resultadoFinal, 1, 1, 'C', true);

        $fill = !$fill; $numFila++;
    }

    // Rellenar filas vacías
    $filasEnPagina = $numFila % FILAS_POR_PAGINA_CUADRO;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA_CUADRO;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA_CUADRO : FILAS_POR_PAGINA_CUADRO - $filasEnPagina;

    for ($i = 0; $i < $filasFaltantes; $i++) {
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(7, 5, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(20, 5, '', 1, 0, 'C', true);
        $pdf->Cell(90, 5, '', 1, 0, 'L', true);
        foreach ($datos['asignaturas'] as $asig) { $pdf->Cell($ancho_asig_pdf, 5, '', 1, 0, 'C', true); }
        $pdf->Cell(20, 5, '', 1, 1, 'C', true);
        $fill = !$fill; $numFila++;
    }


    // Fila de Promedios por Asignatura
     $pdf->SetFont('Arial', 'B', 7);
     $pdf->Cell(117, 5, 'PROMEDIO POR ASIGNATURA', 1, 0, 'R', true); // 7+20+90
     foreach($datos['asignaturas'] as $asig) {
         $promData = $promediosAsignatura[$asig['codigo']] ?? ['suma' => 0, 'contador' => 0];
         $promedio = ($promData['contador'] > 0) ? round($promData['suma'] / $promData['contador']) : '';
         $displayPromedio = (trim($asig['codigo_area']) == '07' && $promedio !== '') ? cambiar_concepto($promedio) : $promedio;
         $pdf->Cell($ancho_asig_pdf, 5, $displayPromedio, 1, 0, 'C', true);
     }
     $pdf->Cell(20, 5, '', 1, 1, 'C', true); // Celda vacía para Resultado


    // --- SECCIÓN DE FIRMAS (RECONSTRUIDA DE TU CÓDIGO) ---
    $pdf->Ln(5); // Espacio antes de firmas
    $pdf->SetFont('Arial','',11);
    // Promovidos y Retenidos en letras
    $pdf->Rect(280,155+ ($pdf->GetY()-100),60,0); // Ajustar Y basado en posición actual
    $pdf->SetXY(250,$pdf->GetY());
    $pdf->Cell(30,5,'PROMOVIDOS:',0,0,'L');
    $pdf->SetX(280);
    $total_promovidos = ($stats['total_promovidos_m'] ?? 0) + ($stats['total_promovidos_f'] ?? 0);
    $pdf->Cell(60, 5, ($total_promovidos == 0) ? 'cero' : convertirtexto(strtolower(num2letras($total_promovidos))), 0, 1, 'C');

    $pdf->Rect(280,165+ ($pdf->GetY()-105),60,0); // Ajustar Y
    $pdf->SetXY(250,$pdf->GetY());
    $pdf->Cell(30,5,'RETENIDOS:',0,0,'L');
    $pdf->SetX(280);
    $total_retenidos = ($stats['total_retenidos_m'] ?? 0) + ($stats['total_retenidos_f'] ?? 0);
    $pdf->Cell(60, 5, ($total_retenidos == 0) ? 'ninguno' : convertirtexto(strtolower(num2letras($total_retenidos))), 0, 1, 'C');

    // Firmas
    $pdf->Ln(10);
    $y_firmas = $pdf->GetY();
    $pdf->Line(40, $y_firmas + 10, 140, $y_firmas + 10); // Línea Docente
    $pdf->SetXY(40, $y_firmas + 11);
    $pdf->Cell(100, 5, convertirtexto($datos['encabezado']['nombre_docente']), 0, 0, 'C');
    $pdf->SetXY(40, $y_firmas + 16);
    $pdf->Cell(100, 5, 'Docente', 0, 0, 'C');

    $pdf->Line(200, $y_firmas + 10, 300, $y_firmas + 10); // Línea Director
    $pdf->SetXY(200, $y_firmas + 11);
    $pdf->Cell(100, 5, convertirtexto($_SESSION['nombre_director']), 0, 0, 'C');
    $pdf->SetXY(200, $y_firmas + 16);
    $pdf->Cell(100, 5, 'Director(a)', 0, 1, 'C');

    $pdf->Output('Cuadro_Registro.pdf', 'I');
}


// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    // ... (Validaciones sin cambios) ...
    if ($errorDbConexion) { throw new Exception("..."); } $codigo_all = $_GET["todos"] ?? null; if (!$codigo_all) { throw new Exception("..."); }

    $datosReporte = obtenerDatosCuadro($dblink, $codigo_all);

    if (empty($datosReporte['notas'])) { echo "No se encontraron notas para este grupo."; exit; }

    generarPdfCuadro($datosReporte);

} catch (PDOException $e) { /*...*/ } catch (Exception $e) { /*...*/ }
?>