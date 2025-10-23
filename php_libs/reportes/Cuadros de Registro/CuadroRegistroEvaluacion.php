<?php
// <-- VERSIÓN FINAL COMPLETA CON DISEÑO DETALLADO Y LLAMADA A ESTADÍSTICAS -->

date_default_timezone_set('America/El_Salvador');
ini_set('display_errors', 1); error_reporting(E_ALL); // Mantener para depuración inicial

// --- INCLUDES ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/funciones_2.php";
require_once $path_root . "/registro_academico/includes/DeNumero_a_Letras.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA_CUADRO', 25);
// Define filas para primera página y siguientes
define('FILAS_PRIMERA_PAGINA', 25);
define('FILAS_SIGUIENTES_PAGINAS', 44); // AJUSTA ESTE NÚMERO (45-50?) según pruebas
define('ALTO_FILA_DATOS', 4);         // Altura reducida
define('TAMANO_FUENTE_DATOS', 7);       // Tamaño fuente reducido

/**
 * Clase FPDF personalizada con el Header y Footer detallados.
 */
class PDF_CuadroRegistro extends FPDF {
    public $datosEncabezado = [];
    public $asignaturas = [];
    public $nombreDocente = '';
    public $estadisticas = [];
    public $codigoGrado = '';
    private $numFilaActual = 0; // Contador de filas en la página actual
    // --- FUNCIONES DE TEXTO ROTADO ---
    var $angle=0;
    function Rotate($angle,$x=-1,$y=-1) { if($x==-1) $x=$this->x; if($y==-1) $y=$this->y; if($this->angle!=0) $this->_out('Q'); $this->angle=$angle; if($angle!=0) { $angle*=M_PI/180; $c=cos($angle); $s=sin($angle); $cx=$x*$this->k; $cy=($this->h-$y)*$this->k; $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy)); } }
    function _endpage() { if($this->angle!=0) { $this->angle=0; $this->_out('Q'); } parent::_endpage(); }
    function RotatedText($x, $y, $txt, $angle) { $this->Rotate($angle,$x,$y); $this->Text($x,$y,$txt); $this->Rotate(0); }
    function RotatedTextMultiCell($x, $y, $txt, $angle) { $this->Rotate($angle,$x,$y); $currentX = $this->GetX(); $currentY = $this->GetY(); $this->SetXY($x, $y - ($this->GetStringWidth($txt) > 30 ? 6 : 3)); $this->MultiCell(10, 3, $txt, 0, 'C'); $this->SetXY($currentX, $currentY); $this->Rotate(0); }
    function RotatedTextMultiCellAspectos($x, $y, $txt, $angle) { $this->RotatedText($x, $y, $txt, $angle); }
    function RotatedTextMultiCellDireccion($x, $y, $txt, $angle){ $this->RotatedText($x, $y, $txt, $angle);}
    // --- FIN FUNCIONES TEXTO ROTADO ---

    // ### FUNCIÓN HEADER DETALLADA Y CORREGIDA ###
    function Header()
    {
        // --- Encabezado General (Nombre Institución, MINED, etc. - Adaptado de tu original) ---
        $this->SetDrawcolor(0,0,0);
        $this->SetXY(70,10);
        $this->SetFont('Arial','',18);
        $gradoNum = isset($this->codigoGrado) ? substr($this->codigoGrado,1,1) : '?'; // Obtener número de grado
        $this->Cell(235,14,convertirtexto('REGISTRO DE EVALUACIÓN DEL RENDIMIENTO ESCOLAR DE '.$gradoNum.'.° DE EDUCACIÓN BÁSICA'),0,0,'L');

        $this->SetXY(80,25);
        $this->SetFont('Arial','',11);
        $this->Cell(235,5,convertirtexto('CUADRO FINAL DE EVALUACIÓN DE: ') . ($this->datosEncabezado['nombre_grado'] ?? '') . ' ' . ($this->datosEncabezado['nombre_seccion'] ?? ''),0,2,'L');
        $this->Cell(235,5,convertirtexto('NOMBRE DEL CENTRO EDUCATIVO: ') . convertirtexto($_SESSION['institucion'] ?? ''),0,2,'L');
        $this->Cell(235,5,convertirtexto('DIRECCIÓN: ') . convertirtexto($_SESSION['direccion'] ?? ''),0,2,'L');
        $this->Cell(235,5,'MUNICIPIO: ' . convertirtexto($_SESSION['nombre_municipio'] ?? '') . '    DISTRITO: ' . convertirtexto($_SESSION['distrito'] ?? ''),0,2,'L');

        // Escudo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo.jpg';
        if(file_exists($img)) { $this->Image($img,35,10,20,20); }
        $this->SetFont('Arial','',10);
        $this->SetXY(15,30);
        $this->Cell(60,4,convertirtexto('República de El Salvador'),0,2,'C');
        $this->Cell(60,4,convertirtexto('Ministerio de Educación'),0,2,'C');
        $this->Cell(60,4,convertirtexto('Dirección Nacional de Educación Básica'),0,2,'C');

        // --- Inicio del Diseño Detallado del Cuadro (Adaptado de tu original) ---
        $y_inicio_cuadro = 45;
        $this->SetY($y_inicio_cuadro);
        $alto_total_header = 45;

        // Columnas fijas (N#, NIE, Nombre)
        $this->SetFont('Arial', '', 10);
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

        $x_asig = 127;

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
        foreach ($this->asignaturas as $index => $asig) {
             $this->Rect($x_current, $y_nombres_asig, $ancho_asig, $alto_nombres_asig);
             if (!in_array(trim($asig['codigo_area']), ['07'])) { // Básicas
                 // Ajusta Y para RotatedTextMultiCell
                 $this->RotatedTextMultiCell($x_current + 2, $y_nombres_asig + $alto_nombres_asig - 2, convertirtexto($asig['nombre']), 90);
             } else { // Conducta
                 // Ajusta Y para RotatedTextMultiCellAspectos
                 $this->RotatedTextMultiCellAspectos($x_current + 2, $y_inicio_cuadro + $alto_total_header - 2, convertirtexto($asig['nombre']), 90);
             }
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
    if($this->PageNo() == 1) {
        $x_escala = 250; $y_escala = 45;
        // Escala
        $this->Rect($x_escala, $y_escala, 90, 9); $this->SetFillColor(206,206,206); $this->Rect($x_escala, $y_escala, 90, 9, "F");
        $this->SetFont('Arial','',9); $this->SetXY($x_escala, $y_escala); $this->Cell(90,4.5,convertirtexto('ESCALA DE VALORACIÓN PARA LAS'),'LRT',2,'C');
        $this->SetX($x_escala); $this->Cell(90,4.5,convertirtexto('COMPETENCIAS CIUDADANAS'),'LRB',1,'C');
        $this->SetX($x_escala); $this->Cell(30,8,'E: Excelente',1,0,'L'); $this->Cell(30,8,'MB: Muy Bueno',1,0,'L'); $this->Cell(30,8,'B: Bueno',1,1,'L');
        $this->SetFont('Arial','',8); $this->SetX($x_escala); $this->Cell(30,5,'Dominio alto de la','LRT',0,'L'); $this->Cell(30,5,'Dominio medio de la','LRT',0,'L'); $this->Cell(30,5,'Dominio bajo de la','LRT',1,'L');
        $this->SetX($x_escala); $this->Cell(30,5,'competencia','LRB',0,'L'); $this->Cell(30,5,'competencia','LRB',0,'L'); $this->Cell(30,5,'competencia','LRB',1,'L');

        // Estadísticas
        $y_stats = 80;
        // ... (Rectángulos de estadísticas) ...
         $this->Rect($x_escala, $y_stats, 90, 30); $this->Rect($x_escala, $y_stats, 18, 30); $this->Rect($x_escala, $y_stats, 90, 15); $this->Rect($x_escala, $y_stats + 15, 90, 5);
         $this->Rect($x_escala + 18, $y_stats, 15, 30); $this->Rect($x_escala, $y_stats + 20, 90, 5); $this->Rect($x_escala + 33, $y_stats, 15, 30);
         $this->Rect($x_escala + 48, $y_stats, 15, 30); $this->Rect($x_escala + 63, $y_stats, 15, 30); $this->Rect($x_escala + 78, $y_stats, 15, 30);

        $this->SetFillColor(206,206,206); $this->SetXY($x_escala, $y_stats); $this->Cell(90,5,convertirtexto('ESTADÍSTICA'),1,1,'C', true);
        $this->SetXY($x_escala - 2, $y_stats + 8); $this->Cell(20,5,'SEXO',0,0,'C');
        $this->SetFont('Arial','',7); $this->SetXY($x_escala + 18, $y_stats + 6); $this->MultiCell(15,3.5,'Matricula Inicial',0,'C');
        $this->SetXY($x_escala + 33, $y_stats + 6); $this->Cell(15,4.5,'Retirados',0,0,'C'); $this->SetXY($x_escala + 48, $y_stats + 6); $this->MultiCell(15,3.5,'Matricula Final',0,'C');
        $this->SetXY($x_escala + 63, $y_stats + 6); $this->Cell(15,4.5,'Promovidos',0,0,'C'); $this->SetXY($x_escala + 78, $y_stats + 6); $this->Cell(15,4.5,'Retenidos',0,0,'C');
        $this->SetFont('Arial','',8); $this->SetXY($x_escala, $y_stats + 15); $this->Cell(18,5,'MASCULINO',0,0,'C');
        $this->SetXY($x_escala, $y_stats + 20); $this->Cell(18,5,'FEMENINO',0,0,'C'); $this->SetXY($x_escala, $y_stats + 25); $this->Cell(18,5,'TOTAL',0,0,'C');

        // Llenar datos de estadísticas (ACCEDE A $this->estadisticas)
        $stats = $this->estadisticas;
        $this->SetFont('Arial','',10);
         // ... (Código para llenar la tabla de estadísticas con los valores de $stats) ...
         $mat_final_m = ($stats['total_matricula_inicial_masculino'] ?? 0) - ($stats['total_matricula_retirados_masculino'] ?? 0);
         $mat_final_f = ($stats['total_matricula_inicial_femenino'] ?? 0) - ($stats['total_matricula_retirados_femenino'] ?? 0);
         $this->SetXY($x_escala + 18, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_matricula_inicial_masculino'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 18, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_matricula_inicial_femenino'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 18, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_matricula_inicial_masculino'] ?? 0) + ($stats['total_matricula_inicial_femenino'] ?? 0), 0, 0, 'C');
         $this->SetXY($x_escala + 33, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_matricula_retirados_masculino'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 33, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_matricula_retirados_femenino'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 33, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_matricula_retirados_masculino'] ?? 0) + ($stats['total_matricula_retirados_femenino'] ?? 0), 0, 0, 'C');
         $this->SetXY($x_escala + 48, $y_stats + 15.5); $this->Cell(15, 4.5, $mat_final_m, 0, 0, 'C');
         $this->SetXY($x_escala + 48, $y_stats + 20.5); $this->Cell(15, 4.5, $mat_final_f, 0, 0, 'C');
         $this->SetXY($x_escala + 48, $y_stats + 25.5); $this->Cell(15, 4.5, $mat_final_m + $mat_final_f, 0, 0, 'C');
         $this->SetXY($x_escala + 63, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_promovidos_m'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 63, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_promovidos_f'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 63, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_promovidos_m'] ?? 0) + ($stats['total_promovidos_f'] ?? 0), 0, 0, 'C');
         $this->SetXY($x_escala + 78, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_retenidos_m'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 78, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_retenidos_f'] ?? '0', 0, 0, 'C');
         $this->SetXY($x_escala + 78, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_retenidos_m'] ?? 0) + ($stats['total_retenidos_f'] ?? 0), 0, 0, 'C');

        // Posición inicial para datos (debajo del encabezado)
        $this->SetY($y_inicio_cuadro + $alto_total_header);
        $this->SetLineWidth(0.2); $this->SetDrawColor(0); $this->SetFont('Arial','',8); // Restaurar
        $this->numFilaActual = 0; // Reiniciar contador de filas para la página
}
    }

// Sobreescribir AddPage para reiniciar el contador de filas
    function AddPage($orientation='', $size='', $rotation=0) {
        parent::AddPage($orientation,$size,$rotation);
        $this->numFilaActual = 0; // Reiniciar al añadir nueva página
    }

    // Incrementar contador de filas
    function IncrementaFila() {
        $this->numFilaActual++;
    }

    // Obtener límite de filas para la página actual
    function GetLimiteFilasPagina() {
        return ($this->PageNo() == 1) ? FILAS_PRIMERA_PAGINA : FILAS_SIGUIENTES_PAGINAS;
    }

    // Obtener número de fila actual en la página
    function GetNumFilaActual() {
        return $this->numFilaActual;
    }

     function Footer() {
        $this->SetY(-15); $this->SetFont('Arial', 'I', 8);
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d'); $mes = $meses[date('n') - 1]; $anio = date('Y');
        $fechaFormateada = "Santa Ana, $dia de $mes de $anio"; $horaFormateada = date('g:i a');
        $textoFooter = "$fechaFormateada - $horaFormateada | Pagina " . $this->PageNo() . ' de {nb}';
        $this->Cell(0, 10, convertirtexto($textoFooter), 0, 0, 'C');
    }
}

/**
 * Obtiene todos los datos necesarios.
 */
function obtenerDatosCuadro(PDO $pdo, string $codigoAll): array {
    // ... (Código para obtener $datos['encabezado'], $datos['asignaturas'], $datos['notas'] sin cambios) ...
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
     if($stmtAsig->rowCount() > 0) { $datos['asignaturas'] = $stmtAsig->fetchAll(PDO::FETCH_ASSOC); } else { $datos['asignaturas'] = []; } // Mantener ordenar
     if(empty($datos['asignaturas'])) { throw new Exception("..."); }
    // Consulta Notas
     $sqlNotas = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as nombre_completo, a.genero, n.codigo_asignatura, n.nota_final, n.recuperacion, n.nota_recuperacion_2 FROM alumno a INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' INNER JOIN nota n ON am.id_alumno_matricula = n.codigo_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all ORDER BY nombre_completo, a.id_alumno, n.codigo_asignatura";
     $stmtNotas = $pdo->prepare($sqlNotas); $stmtNotas->bindParam(':codigo_all', $codigoAll); $stmtNotas->execute(); $datos['notas'] = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

    // Llamar a la función para calcular estadísticas
    $datos['estadisticas'] = calcularEstadisticasFinales($pdo, $codigoAll, $datos['notas'], $datos['asignaturas'], $datos['encabezado']['nota_minima']);

    return $datos;
}

// *** ¡REVISA Y COMPLETA ESTA FUNCIÓN CON TU LÓGICA EXACTA! ***
function calcularEstadisticasFinales($pdo, $codigoAll, $notas, $asignaturas, $notaMinima) {
    $stats = [
        'total_matricula_inicial_masculino' => 0, 'total_matricula_inicial_femenino' => 0,
        'total_matricula_retirados_masculino' => 0, 'total_matricula_retirados_femenino' => 0,
        'total_promovidos_m' => 0, 'total_promovidos_f' => 0,
        'total_retenidos_m' => 0, 'total_retenidos_f' => 0,
    ];

    // 1. Calcular promovidos/retenidos procesando $notas
    $alumnosResultados = []; // [id_alumno => 'P' o 'R']
    $notasPorAlumno = [];
    foreach ($notas as $nota) { $notasPorAlumno[$nota['id_alumno']][$nota['codigo_asignatura']] = $nota; }

    foreach($notasPorAlumno as $idAlumno => $notasAlumno) {
        $asignaturasBasicasReprobadas = 0;
        // Asegúrate de obtener el género de forma segura
        $genero = $notasAlumno[array_key_first($notasAlumno)]['genero'] ?? null;
        if($genero === null && !empty($notasAlumno)){ // Intenta obtenerlo de otra nota si la primera no lo tiene
             foreach($notasAlumno as $n) { if(isset($n['genero'])) { $genero = $n['genero']; break; } }
        }
        $genero = $genero ?? 'm'; // Asume masculino como último recurso

        foreach($asignaturas as $asig) {
            $notaData = $notasAlumno[$asig['codigo']] ?? null;
            // *** ¡IMPORTANTE! Revisa si Conducta (area 07) debe excluirse del conteo de reprobadas ***
            if ($notaData && !in_array(trim($asig['codigo_area']), ['07'])) {
                 $notaFinalVerificada = verificar_nota($notaData['nf'] ?? null, $notaData['nr1'] ?? null, $notaData['nr2'] ?? null);
                 // *** ¡IMPORTANTE! Revisa si la nota 0 cuenta como reprobada ***
                 if (floatval($notaFinalVerificada) < $notaMinima && floatval($notaFinalVerificada) >= 0) { // Considerar nota 0?
                     // *** ¡IMPORTANTE! Revisa qué áreas cuentan como básicas para reprobación ***
                     if (in_array(trim($asig['codigo_area']), ['01', '03'])) { // Ejemplo: areas 01 y 03
                         $asignaturasBasicasReprobadas++;
                     }
                 }
            }
        }
        // *** ¡IMPORTANTE! AJUSTA ESTA REGLA DE PROMOCIÓN SEGÚN MINED / TU INSTITUCIÓN ***
        // Por ejemplo: ¿Son MÁXIMO 2 reprobadas? ¿O menos de 3? ¿Alguna asignatura específica causa retención?
        $resultado = ($asignaturasBasicasReprobadas <= 2) ? 'P' : 'R';
        $alumnosResultados[$idAlumno] = $resultado;

        // Contar por género
        if($resultado == 'P') {
            if($genero == 'm') $stats['total_promovidos_m']++; else $stats['total_promovidos_f']++;
        } else {
            if($genero == 'm') $stats['total_retenidos_m']++; else $stats['total_retenidos_f']++;
        }
    }

    // 2. Consultar Matrícula Inicial y Retirados (Parece correcto)
    try {
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
    } catch (PDOException $e) { error_log("Error en consulta de estadísticas: " . $e->getMessage()); }

    return $stats;
}


/**
 * Genera el PDF del reporte adaptando el bucle original y usando el Header detallado.
 */
function generarPdfCuadro(array $datos) {
    // ... (Código para obtener $notaMinima, $stats, procesar $alumnosNotas sin cambios) ...
    $notaMinima = floatval($datos['encabezado']['nota_minima'] ?? 5.0);
    $stats = $datos['estadisticas'];
    $alumnosNotas = [];
    foreach ($datos['notas'] as $nota) { $idAlumno = $nota['id_alumno']; if (!isset($alumnosNotas[$idAlumno])) { $alumnosNotas[$idAlumno] = ['info' => ['nombre' => $nota['nombre_completo'], 'nie' => $nota['codigo_nie'], 'genero' => $nota['genero']], 'notas' => []]; } $alumnosNotas[$idAlumno]['notas'][$nota['codigo_asignatura']] = ['nf' => $nota['nota_final'], 'nr1' => $nota['recuperacion'], 'nr2' => $nota['nota_recuperacion_2']]; }

    // --- GENERAR PDF ---
    $pdf = new PDF_CuadroRegistro('L', 'mm', 'Legal');
    $pdf->SetMargins(10, 5, 10); $pdf->SetAutoPageBreak(true, 15); $pdf->AliasNbPages();
    $pdf->datosEncabezado = $datos['encabezado'];
    $pdf->asignaturas = $datos['asignaturas'];
    $pdf->nombreDocente = $datos['encabezado']['nombre_docente'];
    $pdf->estadisticas = $stats;
    $pdf->codigoGrado = $datos['encabezado']['codigo_grado'] ?? '';

    $pdf->AddPage(); // Dibuja el Header complejo automáticamente

    $pdf->SetFont('Arial', '', 7); $fill = false; $numFila = 0;
    $ancho_asig_pdf = 10;
    $promediosAsignatura = array_fill_keys(array_column($datos['asignaturas'], 'codigo'), ['suma' => 0, 'contador' => 0]);

    // Bucle principal adaptado de tu original
    foreach ($alumnosNotas as $idAlumno => $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA_CUADRO == 0) { $pdf->AddPage(); $pdf->SetFont('Arial', '', 7); } // AddPage redibuja Header
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);

        $pdf->Cell(7, 5, $numFila + 1, 1, 0, 'C', true); // N#
        $pdf->Cell(20, 5, $alumno['info']['nie'], 1, 0, 'C', true); // NIE
        $pdf->Cell(90, 5, convertirtexto($alumno['info']['nombre']), 1, 0, 'L', true); // Nombre

        $asignaturasBasicasReprobadas = 0;

        foreach ($datos['asignaturas'] as $asig) {
            $notaData = $alumno['notas'][$asig['codigo']] ?? ['nf' => null, 'nr1' => null, 'nr2' => null];
            $notaFinalVerificada = verificar_nota($notaData['nf'] ?? null, $notaData['nr1'] ?? null, $notaData['nr2'] ?? null);
            $nfVal = floatval($notaFinalVerificada);
            $displayNota = '';

            if ($nfVal >= 0) {
                 if (trim($asig['codigo_area']) == '07') { $displayNota = cambiar_concepto($nfVal); }
                 else {
                    $displayNota = number_format($nfVal, 0);
                     // *** REVISA ESTA LÓGICA DE COLOREADO Y CONTEO ***
                    if($nfVal < $notaMinima && $nfVal >= 0) { // Colorear si reprobada (incluye 0?)
                        $pdf->SetTextColor(255, 0, 0);
                        // *** REVISA CÓDIGOS DE ÁREA BÁSICA ***
                        if (in_array(trim($asig['codigo_area']), ['01', '03'])) { $asignaturasBasicasReprobadas++; }
                    }
                    // Sumar para promedio solo si nota > 0 (o >= 0?)
                    if(isset($promediosAsignatura[$asig['codigo']]) && $nfVal > 0) {
                         $promediosAsignatura[$asig['codigo']]['suma'] += $nfVal;
                         $promediosAsignatura[$asig['codigo']]['contador']++;
                    }
                 }
            }
            $pdf->Cell($ancho_asig_pdf, 5, $displayNota, 1, 0, 'C', true);
            $pdf->SetTextColor(0); // Restaurar color
        }
         // *** REVISA ESTA LÓGICA DE PROMOCIÓN ***
        $resultadoFinal = ($asignaturasBasicasReprobadas <= 2) ? 'P' : 'R';
        $pdf->Cell(20, 5, $resultadoFinal, 1, 1, 'C', true);

        $fill = !$fill; $numFila++;
    }

    // --- Rellenar filas vacías ---
    // ... (código similar al anterior, usando FILAS_POR_PAGINA_CUADRO) ...
// --- Rellenar filas vacías (Adaptado de tu original) ---
    $filasEnPagina = $numFila % FILAS_POR_PAGINA_CUADRO;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA_CUADRO;

    // ▼▼▼ CALCULAR linea_faltante PRIMERO ▼▼▼
    $linea_faltante = ($numFila == 0) ? FILAS_POR_PAGINA_CUADRO : FILAS_POR_PAGINA_CUADRO - $filasEnPagina;
    if ($linea_faltante < 0) $linea_faltante = 0; // Asegurar que no sea negativo

    // Dibujar línea diagonal si hay espacio (Ahora $linea_faltante existe)
    if ($linea_faltante > 0 && $numFila < FILAS_POR_PAGINA_CUADRO) {
        $valor_y1 = $pdf->GetY();
        // Ajusta las coordenadas X2, Y2 de la línea según el tamaño Legal y márgenes
        $pdf->Line(17, $valor_y1, 237, $pdf->GetPageHeight() - $pdf->bMargin - 25);
    }

    // Bucle para rellenar las filas
    for($i=0; $i < $linea_faltante; $i++) { // Usar $linea_faltante
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(7, 5, $numFila + 1, 1, 0, 'C', true);
        $pdf->Cell(20, 5, '', 1, 0, 'C', true);
        $pdf->Cell(90, 5, '', 1, 0, 'L', true);
        foreach ($datos['asignaturas'] as $asig) { $pdf->Cell($ancho_asig_pdf, 5, '', 1, 0, 'C', true); }
        $pdf->Cell(20, 5, '', 1, 1, 'C', true);
        $fill = !$fill; $numFila++;
    }


    // --- Fila de Promedios por Asignatura ---
     $pdf->SetFont('Arial', 'B', 7);
     $pdf->Cell(117, 5, 'PROMEDIO POR ASIGNATURA', 1, 0, 'R', true); // 7+20+90
     foreach($datos['asignaturas'] as $asig) {
         $promData = $promediosAsignatura[$asig['codigo']] ?? ['suma' => 0, 'contador' => 0];
         $promedio = ($promData['contador'] > 0) ? number_format(round($promData['suma'] / $promData['contador']), 0) : ''; // Redondeado
         $displayPromedio = (trim($asig['codigo_area']) == '07' && $promedio !== '') ? cambiar_concepto($promedio) : $promedio;
         $pdf->Cell($ancho_asig_pdf, 5, $displayPromedio, 1, 0, 'C', true);
     }
     $pdf->Cell(20, 5, '', 1, 1, 'C', true);

// --- SECCIÓN DE FIRMAS (AL FINAL, SOLO SI ES LA ÚLTIMA PÁGINA) ---
    // Obtenemos el número total de páginas REAL después de procesar todo.
     $numPagesTotal = $pdf->PageNo(); // El número de página actual es el total.

     if ($pdf->PageNo() == $numPagesTotal) { // Dibuja solo en la última página
         $pdf->Ln(5); $pdf->SetFont('Arial','',10);
         $y_actual_firmas = $pdf->GetY();

         // Controlar salto si las firmas no caben
         if ($y_actual_firmas > $pdf->GetPageHeight() - 45) { // Ajusta el 45
           //  $pdf->AddPage();
             $y_actual_firmas = $pdf->GetY() + 10; // Reiniciar Y y bajar un poco
             $pdf->SetFont('Arial','',10); // Restaurar fuente
         }

         // Promovidos y Retenidos en letras
         $pdf->SetXY(250, $y_actual_firmas); $pdf->Cell(30,5,'PROMOVIDOS: '.$numPagesTotal . $pdf->pageNo(),0,0,'L'); $pdf->SetX(280);
         $total_promovidos = ($stats['total_promovidos_m'] ?? 0) + ($stats['total_promovidos_f'] ?? 0);
         $pdf->Cell(60, 5, ($total_promovidos == 0) ? 'cero' : convertirtexto(strtolower(num2letras($total_promovidos))), 'B', 1, 'C');
         $pdf->SetXY(250, $y_actual_firmas + 10); $pdf->Cell(30,5,'RETENIDOS:',0,0,'L'); $pdf->SetX(280);
         $total_retenidos = ($stats['total_retenidos_m'] ?? 0) + ($stats['total_retenidos_f'] ?? 0);
         $pdf->Cell(60, 5, ($total_retenidos == 0) ? 'ninguno' : convertirtexto(strtolower(num2letras($total_retenidos))), 'B', 1, 'C');

         // Lugar y Fecha
         $pdf->Ln(8); $y_lugar_fecha = $pdf->GetY();
         $pdf->SetX(250); $pdf->Cell(15, 5, 'Lugar:', 0, 0, 'L');
         $pdf->Cell(75, 5, convertirtexto($_SESSION['nombre_municipio'] ?? 'Santa Ana'), 'B', 1, 'C');
         $pdf->SetX(250); $pdf->Cell(15, 5, 'Fecha:', 0, 0, 'L');
         $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
         $fecha_final_cuadro = convertirtexto(($_SESSION['dia_entrega'] ?? date('d')) ." de ".$meses[date('n')-1]." de ".($datos['encabezado']['nombre_ann_lectivo'] ?? date('Y')));
         $pdf->Cell(75, 5, $fecha_final_cuadro, 'B', 1, 'C');

         // Firmas
         $pdf->Ln(10); $y_firmas = $pdf->GetY();
         $pdf->Line(40, $y_firmas + 10, 140, $y_firmas + 10); // Línea Docente
         $pdf->SetXY(40, $y_firmas + 11); $pdf->Cell(100, 5, convertirtexto($datos['encabezado']['nombre_docente']), 0, 0, 'C');
         $pdf->SetXY(40, $y_firmas + 16); $pdf->Cell(100, 5, 'Docente', 0, 0, 'C');
         $pdf->Line(200, $y_firmas + 10, 300, $y_firmas + 10); // Línea Director
         $pdf->SetXY(200, $y_firmas + 11); $pdf->Cell(100, 5, convertirtexto($_SESSION['nombre_director']), 0, 0, 'C');
         $pdf->SetXY(200, $y_firmas + 16); $pdf->Cell(100, 5, 'Director(a)', 0, 1, 'C');
     } // Fin del if (es la última página)
    $pdf->Output('Cuadro_Registro.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("Error Conexión BD."); }
    $codigo_all = $_GET["todos"] ?? null;
    if (!$codigo_all) { throw new Exception("Faltan parámetros (código de grupo)."); }

    $datosReporte = obtenerDatosCuadro($dblink, $codigo_all);

    if (empty($datosReporte['notas'])) { echo "No se encontraron notas para este grupo."; exit; }

    generarPdfCuadro($datosReporte);

} catch (PDOException $e) { /*...*/ } catch (Exception $e) { /*...*/ }
?>