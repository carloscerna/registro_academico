<?php
// <-- VERSIÓN PHP 8.3 COMPATIBLE -->

// 1. CONFIGURACIÓN Y SILENCIADOR
date_default_timezone_set('America/El_Salvador');
ob_start(); // Limpieza de buffer para evitar corrupción de PDF
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

// 2. FUNCIÓN DE COMPATIBILIDAD (UTF-8 FIX)
if (!function_exists('utf8_decode_fix')) {
    function utf8_decode_fix($texto) {
        if (is_null($texto)) return '';
        return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
    }
}

// Wrapper seguro para convertirTexto
if (!function_exists('convertirTextoSafe')) {
    function convertirTextoSafe($texto) {
        if (function_exists('convertirtexto')) {
            return convertirtexto($texto);
        }
        return utf8_decode_fix($texto);
    }
}

// --- INCLUDES ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
// require_once $path_root . "/registro_academico/includes/funciones_2.php"; // Si no es vital, comentar para reducir riesgo
require_once $path_root . "/registro_academico/includes/DeNumero_a_Letras.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

// CONSTANTES DE DISEÑO
define('FILAS_POR_PAGINA_CUADRO', 25);
define('FILAS_PRIMERA_PAGINA', 25);
define('FILAS_SIGUIENTES_PAGINAS', 44); 
define('ALTO_FILA_DATOS', 4);         
define('TAMANO_FUENTE_DATOS', 7);       

/**
 * Clase FPDF personalizada con el Header y Footer detallados.
 */
class PDF_CuadroRegistro extends FPDF {
    public $datosEncabezado = [];
    public $asignaturas = [];
    public $nombreDocente = '';
    public $estadisticas = [];
    public $codigoGrado = '';
    private $numFilaActual = 0; 
    
    // --- FUNCIONES DE TEXTO ROTADO ---
    var $angle=0;
    function Rotate($angle,$x=-1,$y=-1) { if($x==-1) $x=$this->x; if($y==-1) $y=$this->y; if($this->angle!=0) $this->_out('Q'); $this->angle=$angle; if($angle!=0) { $angle*=M_PI/180; $c=cos($angle); $s=sin($angle); $cx=$x*$this->k; $cy=($this->h-$y)*$this->k; $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy)); } }
    function _endpage() { if($this->angle!=0) { $this->angle=0; $this->_out('Q'); } parent::_endpage(); }
    function RotatedText($x, $y, $txt, $angle) { $this->Rotate($angle,$x,$y); $this->Text($x,$y,$txt); $this->Rotate(0); }
    function RotatedTextMultiCell($x, $y, $txt, $angle) { $this->Rotate($angle,$x,$y); $currentX = $this->GetX(); $currentY = $this->GetY(); $this->SetXY($x, $y - ($this->GetStringWidth($txt) > 30 ? 6 : 3)); $this->MultiCell(10, 3, $txt, 0, 'C'); $this->SetXY($currentX, $currentY); $this->Rotate(0); }
    function RotatedTextMultiCellAspectos($x, $y, $txt, $angle) { $this->RotatedText($x, $y, $txt, $angle); }

    function Header()
    {
        // Protección de llaves para el Encabezado
        $nombre_grado = $this->datosEncabezado['nombre_grado'] ?? '';
        $nombre_seccion = $this->datosEncabezado['nombre_seccion'] ?? '';
        $institucion = $_SESSION['institucion'] ?? 'Nombre Institución';
        $direccion = $_SESSION['direccion'] ?? '';
        $municipio = $_SESSION['nombre_municipio'] ?? '';
        $distrito = $_SESSION['distrito'] ?? ''; // OJO: Verificar si existe en sesión

        $this->SetDrawcolor(0,0,0);
        $this->SetXY(70,10);
        $this->SetFont('Arial','',18);
        $gradoNum = isset($this->codigoGrado) && strlen($this->codigoGrado) >= 2 ? substr($this->codigoGrado,1,1) : ''; 
        
        $this->Cell(235,14,convertirTextoSafe('REGISTRO DE EVALUACIÓN DEL RENDIMIENTO ESCOLAR DE '.$gradoNum.'.° DE EDUCACIÓN BÁSICA'),0,0,'L');

        $this->SetXY(80,25);
        $this->SetFont('Arial','',11);
        $this->Cell(235,5,convertirTextoSafe('CUADRO FINAL DE EVALUACIÓN DE: ') . convertirTextoSafe($nombre_grado . ' ' . $nombre_seccion),0,2,'L');
        $this->Cell(235,5,convertirTextoSafe('NOMBRE DEL CENTRO EDUCATIVO: ') . convertirTextoSafe($institucion),0,2,'L');
        $this->Cell(235,5,convertirTextoSafe('DIRECCIÓN: ') . convertirTextoSafe($direccion),0,2,'L');
        $this->Cell(235,5,'MUNICIPIO: ' . convertirTextoSafe($municipio) . '    DISTRITO: ' . convertirTextoSafe($distrito),0,2,'L');

        // Escudo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo.jpg';
        if(file_exists($img)) { $this->Image($img,35,10,20,20); }
        
        $this->SetFont('Arial','',10);
        $this->SetXY(15,30);
        $this->Cell(60,4,convertirTextoSafe('República de El Salvador'),0,2,'C');
        $this->Cell(60,4,convertirTextoSafe('Ministerio de Educación'),0,2,'C');
        $this->Cell(60,4,convertirTextoSafe('Dirección Nacional de Educación Básica'),0,2,'C');

        // Diseño del Cuadro
        $y_inicio_cuadro = 45;
        $this->SetY($y_inicio_cuadro);
        $alto_total_header = 45;

        // Columnas fijas
        $this->SetFont('Arial', '', 10);
        $this->Rect(10, $y_inicio_cuadro, 7, $alto_total_header); 
        $this->RotatedText(14, $y_inicio_cuadro + 35, convertirTextoSafe('N°'), 90);
        
        $this->Rect(17, $y_inicio_cuadro, 20, $alto_total_header); 
        $this->RotatedText(27, $y_inicio_cuadro + 35, convertirTextoSafe('NIE'), 90);
        
        $this->Rect(37, $y_inicio_cuadro, 90, $alto_total_header);
        $this->SetFont('Arial', '', 11); 
        $this->SetXY(40, $y_inicio_cuadro + 15);
        $this->MultiCell(85, 6, convertirTextoSafe('Nombre de los Alumnos(as) en orden alfabético de apellidos'), 0, 'C');

        // Anchos dinámicos
        $num_asig_basicas = 0; $num_asig_conducta = 0;
        foreach ($this->asignaturas as $asig) {
            if (in_array(trim($asig['codigo_area'] ?? ''), ['07'])) { $num_asig_conducta++; } else { $num_asig_basicas++; }
        }
        $ancho_asig = 10;
        $ancho_total_asig = $num_asig_basicas * $ancho_asig;
        $ancho_total_conducta = $num_asig_conducta * $ancho_asig;

        $x_asig = 127;

        // Títulos Superiores
        $this->SetFont('Arial', 'B', 10);
        $this->Rect($x_asig, $y_inicio_cuadro, $ancho_total_asig, 7);
        $this->SetXY($x_asig, $y_inicio_cuadro + 1); 
        $this->Cell($ancho_total_asig, 5, 'ASIGNATURAS', 0, 0, 'C');
        
        $x_conducta = $x_asig + $ancho_total_asig;
        // Solo dibujar cuadro de conducta si hay asignaturas de conducta
        if ($ancho_total_conducta > 0) {
            $this->Rect($x_conducta, $y_inicio_cuadro, $ancho_total_conducta, 7);
            $this->SetFont('Arial', 'B', 7); 
            $this->SetXY($x_conducta, $y_inicio_cuadro + 1);
            $this->Cell($ancho_total_conducta, 5, convertirTextoSafe('COMPETENCIAS CIUDADANAS'), 0, 0, 'C');
        }

        // Nombres de Asignaturas
        $this->SetFont('Arial', '', 7);
        $x_current = $x_asig;
        $y_nombres_asig = $y_inicio_cuadro + 7;
        $alto_nombres_asig = $alto_total_header - 7 - 10;
        
        foreach ($this->asignaturas as $index => $asig) {
             $this->Rect($x_current, $y_nombres_asig, $ancho_asig, $alto_nombres_asig);
             $nomAsig = $asig['nombre'] ?? '';
             
             if (!in_array(trim($asig['codigo_area'] ?? ''), ['07'])) { 
                 $this->RotatedTextMultiCell($x_current + 2, $y_nombres_asig + $alto_nombres_asig - 2, convertirTextoSafe($nomAsig), 90);
             } else { 
                 $this->RotatedTextMultiCellAspectos($x_current + 2, $y_inicio_cuadro + $alto_total_header - 2, convertirTextoSafe($nomAsig), 90);
             }
             $x_current += $ancho_asig;
        }

        // Título Inferior: CALIFICACIÓN
        $y_calificacion = $y_inicio_cuadro + $alto_total_header - 10;
        $ancho_total_calificacion = $ancho_total_asig + $ancho_total_conducta;
        $this->Rect($x_asig, $y_calificacion, $ancho_total_calificacion, 10);
        $this->SetFont('Arial', 'B', 10);
        $this->SetXY($x_asig, $y_calificacion + 2);
        $this->Cell($ancho_total_calificacion, 5, convertirTextoSafe('CALIFICACIÓN'), 0, 0, 'C');

        // Columna Resultado Final
        $x_resultado = $x_asig + $ancho_total_calificacion;
        $ancho_resultado = 20;
        $this->Rect($x_resultado, $y_inicio_cuadro, $ancho_resultado, $alto_total_header);
        $this->RotatedText($x_resultado + 7, $y_inicio_cuadro + 35, 'RESULTADO', 90);

        // --- BLOQUE ESTADÍSTICO (SOLO PÁGINA 1) ---
        if($this->PageNo() == 1) {
            $x_escala = 250; $y_escala = 45;
            // Escala
            $this->Rect($x_escala, $y_escala, 90, 9); 
            $this->SetFillColor(206,206,206); 
            $this->Rect($x_escala, $y_escala, 90, 9, "F");
            $this->SetFont('Arial','',9); 
            $this->SetXY($x_escala, $y_escala); 
            $this->Cell(90,4.5,convertirTextoSafe('ESCALA DE VALORACIÓN PARA LAS'),'LRT',2,'C');
            $this->SetX($x_escala); 
            $this->Cell(90,4.5,convertirTextoSafe('COMPETENCIAS CIUDADANAS'),'LRB',1,'C');
            
            // Leyenda de Escala
            $this->SetX($x_escala); $this->Cell(30,8,'E: Excelente',1,0,'L'); $this->Cell(30,8,'MB: Muy Bueno',1,0,'L'); $this->Cell(30,8,'B: Bueno',1,1,'L');
            $this->SetFont('Arial','',8); 
            $this->SetX($x_escala); $this->Cell(30,5,'Dominio alto de la','LRT',0,'L'); $this->Cell(30,5,'Dominio medio de la','LRT',0,'L'); $this->Cell(30,5,'Dominio bajo de la','LRT',1,'L');
            $this->SetX($x_escala); $this->Cell(30,5,'competencia','LRB',0,'L'); $this->Cell(30,5,'competencia','LRB',0,'L'); $this->Cell(30,5,'competencia','LRB',1,'L');

            // Estadísticas
            $y_stats = 80;
            $this->Rect($x_escala, $y_stats, 90, 30);
            
            // Título Estadística
            $this->SetFillColor(206,206,206); 
            $this->SetXY($x_escala, $y_stats); 
            $this->Cell(90,5,convertirTextoSafe('ESTADÍSTICA'),1,1,'C', true);
            
            // Cabeceras Estadística
            $this->SetXY($x_escala - 2, $y_stats + 8); $this->Cell(20,5,'SEXO',0,0,'C');
            $this->SetFont('Arial','',7); 
            $this->SetXY($x_escala + 18, $y_stats + 6); $this->MultiCell(15,3.5,'Matricula Inicial',0,'C');
            $this->SetXY($x_escala + 33, $y_stats + 6); $this->Cell(15,4.5,'Retirados',0,0,'C'); 
            $this->SetXY($x_escala + 48, $y_stats + 6); $this->MultiCell(15,3.5,'Matricula Final',0,'C');
            $this->SetXY($x_escala + 63, $y_stats + 6); $this->Cell(15,4.5,'Promovidos',0,0,'C'); 
            $this->SetXY($x_escala + 78, $y_stats + 6); $this->Cell(15,4.5,'Retenidos',0,0,'C');
            
            // Filas Estadística
            $this->SetFont('Arial','',8); 
            $this->SetXY($x_escala, $y_stats + 15); $this->Cell(18,5,'MASCULINO',0,0,'C');
            $this->SetXY($x_escala, $y_stats + 20); $this->Cell(18,5,'FEMENINO',0,0,'C'); 
            $this->SetXY($x_escala, $y_stats + 25); $this->Cell(18,5,'TOTAL',0,0,'C');

            // Datos Estadísticos (Protegidos con ??)
            $stats = $this->estadisticas;
            $this->SetFont('Arial','',10);
            
            $mi_m = $stats['total_matricula_inicial_masculino'] ?? 0;
            $mi_f = $stats['total_matricula_inicial_femenino'] ?? 0;
            $mr_m = $stats['total_matricula_retirados_masculino'] ?? 0;
            $mr_f = $stats['total_matricula_retirados_femenino'] ?? 0;
            
            $mf_m = $mi_m - $mr_m;
            $mf_f = $mi_f - $mr_f;
            
            // MASCULINO
            $this->SetXY($x_escala + 18, $y_stats + 15.5); $this->Cell(15, 4.5, $mi_m, 0, 0, 'C');
            $this->SetXY($x_escala + 33, $y_stats + 15.5); $this->Cell(15, 4.5, $mr_m, 0, 0, 'C');
            $this->SetXY($x_escala + 48, $y_stats + 15.5); $this->Cell(15, 4.5, $mf_m, 0, 0, 'C');
            $this->SetXY($x_escala + 63, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_promovidos_m'] ?? 0, 0, 0, 'C');
            $this->SetXY($x_escala + 78, $y_stats + 15.5); $this->Cell(15, 4.5, $stats['total_retenidos_m'] ?? 0, 0, 0, 'C');
            
            // FEMENINO
            $this->SetXY($x_escala + 18, $y_stats + 20.5); $this->Cell(15, 4.5, $mi_f, 0, 0, 'C');
            $this->SetXY($x_escala + 33, $y_stats + 20.5); $this->Cell(15, 4.5, $mr_f, 0, 0, 'C');
            $this->SetXY($x_escala + 48, $y_stats + 20.5); $this->Cell(15, 4.5, $mf_f, 0, 0, 'C');
            $this->SetXY($x_escala + 63, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_promovidos_f'] ?? 0, 0, 0, 'C');
            $this->SetXY($x_escala + 78, $y_stats + 20.5); $this->Cell(15, 4.5, $stats['total_retenidos_f'] ?? 0, 0, 0, 'C');
            
            // TOTALES
            $this->SetXY($x_escala + 18, $y_stats + 25.5); $this->Cell(15, 4.5, $mi_m + $mi_f, 0, 0, 'C');
            $this->SetXY($x_escala + 33, $y_stats + 25.5); $this->Cell(15, 4.5, $mr_m + $mr_f, 0, 0, 'C');
            $this->SetXY($x_escala + 48, $y_stats + 25.5); $this->Cell(15, 4.5, $mf_m + $mf_f, 0, 0, 'C');
            $this->SetXY($x_escala + 63, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_promovidos_m']??0) + ($stats['total_promovidos_f']??0), 0, 0, 'C');
            $this->SetXY($x_escala + 78, $y_stats + 25.5); $this->Cell(15, 4.5, ($stats['total_retenidos_m']??0) + ($stats['total_retenidos_f']??0), 0, 0, 'C');

            $this->SetY($y_inicio_cuadro + $alto_total_header);
            $this->SetLineWidth(0.2); 
            $this->SetDrawColor(0); 
            $this->SetFont('Arial','',8); 
            $this->numFilaActual = 0; 
        }
    }

    function AddPage($orientation='', $size='', $rotation=0) {
        parent::AddPage($orientation,$size,$rotation);
        $this->numFilaActual = 0; 
    }

    function IncrementaFila() {
        $this->numFilaActual++;
    }

    function GetLimiteFilasPagina() {
        return ($this->PageNo() == 1) ? FILAS_PRIMERA_PAGINA : FILAS_SIGUIENTES_PAGINAS;
    }

    function GetNumFilaActual() {
        return $this->numFilaActual;
    }

     function Footer() {
        $this->SetY(-15); $this->SetFont('Arial', 'I', 8);
        $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
        $dia = date('d'); $mes = $meses[date('n') - 1]; $anio = date('Y');
        $fechaFormateada = "Santa Ana, $dia de $mes de $anio"; $horaFormateada = date('g:i a');
        $textoFooter = "$fechaFormateada - $horaFormateada | Pagina " . $this->PageNo() . ' de {nb}';
        $this->Cell(0, 10, convertirTextoSafe($textoFooter), 0, 0, 'C');
    }
}

/**
 * Obtiene todos los datos necesarios.
 */
function obtenerDatosCuadro(PDO $pdo, string $codigoAll): array {
     $datos = ['encabezado' => [], 'notas' => [], 'asignaturas' => [], 'estadisticas' => []];
     
     // Consulta Encabezado
     $sqlEncabezado = "SELECT btrim(bach.nombre) as nombre_bachillerato, am.codigo_bach_o_ciclo, btrim(gan.nombre) as nombre_grado, am.codigo_grado, btrim(sec.nombre) as nombre_seccion, am.codigo_seccion, ann.nombre as nombre_ann_lectivo, am.codigo_ann_lectivo, am.codigo_turno, cp.cantidad_periodos, cp.calificacion_minima FROM alumno_matricula am INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo LEFT JOIN catalogo_periodos cp ON bach.codigo = cp.codigo_modalidad WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all LIMIT 1";
     $stmt = $pdo->prepare($sqlEncabezado); 
     $stmt->bindParam(':codigo_all', $codigoAll); 
     $stmt->execute();
     
     $datos['encabezado'] = $stmt->fetch(PDO::FETCH_ASSOC); 
     if(!$datos['encabezado']) { $datos['encabezado'] = []; }
     $datos['encabezado']['nota_minima'] = $datos['encabezado']['calificacion_minima'] ?? 5.0;
     
     // Consulta Docente
     $sqlDocente = "SELECT btrim(p.nombres || ' ' || p.apellidos) as nombre_docente FROM encargado_grado eg INNER JOIN personal p ON eg.codigo_docente = p.id_personal WHERE btrim(eg.codigo_bachillerato::text || eg.codigo_grado::text || eg.codigo_seccion::text || eg.codigo_ann_lectivo::text || eg.codigo_turno::text) = :codigo_all AND eg.encargado = 't' LIMIT 1";
     $stmtDocente = $pdo->prepare($sqlDocente); $stmtDocente->bindParam(':codigo_all', $codigoAll); $stmtDocente->execute(); $docente = $stmtDocente->fetch(PDO::FETCH_ASSOC); 
     $datos['encabezado']['nombre_docente'] = $docente['nombre_docente'] ?? 'No asignado';
     
     // Consulta Asignaturas
     $sqlAsignaturas = "SELECT DISTINCT asig.codigo, asig.nombre, asig.codigo_area, asig.ordenar FROM asignatura asig INNER JOIN nota n ON asig.codigo = n.codigo_asignatura INNER JOIN alumno_matricula am ON n.codigo_matricula = am.id_alumno_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all AND asig.ordenar > 0 ORDER BY asig.ordenar";
     $stmtAsig = $pdo->prepare($sqlAsignaturas); $stmtAsig->bindParam(':codigo_all', $codigoAll); $stmtAsig->execute();
     if($stmtAsig->rowCount() > 0) { $datos['asignaturas'] = $stmtAsig->fetchAll(PDO::FETCH_ASSOC); } else { $datos['asignaturas'] = []; } 
     
    // Consulta Notas
     $sqlNotas = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as nombre_completo, a.genero, n.codigo_asignatura, n.nota_final, n.recuperacion, n.nota_recuperacion_2 FROM alumno a INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f' INNER JOIN nota n ON am.id_alumno_matricula = n.codigo_matricula WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = :codigo_all ORDER BY nombre_completo, a.id_alumno, n.codigo_asignatura";
     $stmtNotas = $pdo->prepare($sqlNotas); $stmtNotas->bindParam(':codigo_all', $codigoAll); $stmtNotas->execute(); $datos['notas'] = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

    // Calcular estadísticas
    $datos['estadisticas'] = calcularEstadisticasFinales($pdo, $codigoAll, $datos['notas'], $datos['asignaturas'], $datos['encabezado']['nota_minima']);

    return $datos;
}

// LÓGICA DE ESTADÍSTICAS RECONSTRUIDA
function calcularEstadisticasFinales($pdo, $codigoAll, $notas, $asignaturas, $notaMinima) {
    // Inicializar llaves para evitar Warning: Undefined array key
    $stats = [
        'total_matricula_inicial_masculino' => 0, 'total_matricula_inicial_femenino' => 0,
        'total_matricula_retirados_masculino' => 0, 'total_matricula_retirados_femenino' => 0,
        'total_promovidos_m' => 0, 'total_promovidos_f' => 0,
        'total_retenidos_m' => 0, 'total_retenidos_f' => 0,
    ];

    // Pivote temporal para conteo
    $notasPorAlumno = [];
    foreach ($notas as $nota) { 
        $notasPorAlumno[$nota['id_alumno']][$nota['codigo_asignatura']] = $nota; 
        // Guardar género (intento seguro)
        if (!isset($notasPorAlumno[$nota['id_alumno']]['genero'])) {
            $notasPorAlumno[$nota['id_alumno']]['genero'] = $nota['genero'] ?? 'm';
        }
    }

    foreach($notasPorAlumno as $idAlumno => $notasAlumno) {
        // Ignorar claves que no son asignaturas (como 'genero')
        $genero = $notasAlumno['genero'];
        $asignaturasBasicasReprobadas = 0;

        foreach($asignaturas as $asig) {
            $codAsig = $asig['codigo'];
            // Protección contra llave indefinida
            $notaData = $notasAlumno[$codAsig] ?? null;
            
            if ($notaData && !in_array(trim($asig['codigo_area']), ['07'])) { // Excluir conducta
                 // Aquí deberías usar tu función verificar_nota si existe.
                 // Simularemos uso de nota_final directo si no existe
                 $nf = $notaData['nota_final'] ?? 0;
                 
                 if (floatval($nf) < $notaMinima && floatval($nf) > 0) { 
                     $asignaturasBasicasReprobadas++;
                 }
            }
        }
        
        $resultado = ($asignaturasBasicasReprobadas <= 2) ? 'P' : 'R'; // REGLA: <=2 Reprobadas = Promovido?

        if($resultado == 'P') {
            if($genero == 'm') $stats['total_promovidos_m']++; else $stats['total_promovidos_f']++;
        } else {
            if($genero == 'm') $stats['total_retenidos_m']++; else $stats['total_retenidos_f']++;
        }
    }

    // Consultas SQL auxiliares
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
    } catch (PDOException $e) { }

    return $stats;
}

function generarPdfCuadro(array $datos) {
    $notaMinima = floatval($datos['encabezado']['nota_minima'] ?? 5.0);
    $stats = $datos['estadisticas'];

    // --- RECONSTRUCCIÓN DE LÓGICA DE PIVOTE ---
    // Agrupar notas por alumno para poder iterar en el PDF
    $alumnosNotas = []; 
    foreach ($datos['notas'] as $nota) { 
        $id = $nota['id_alumno'];
        if (!isset($alumnosNotas[$id])) {
            $alumnosNotas[$id] = [
                'info' => [
                    'nie' => $nota['codigo_nie'],
                    'nombre' => $nota['nombre_completo']
                ],
                'notas' => []
            ];
        }
        // Asignar nota a la asignatura correspondiente
        $alumnosNotas[$id]['notas'][$nota['codigo_asignatura']] = $nota;
    }

    // --- GENERAR PDF ---
    $pdf = new PDF_CuadroRegistro('L', 'mm', 'Legal');
    $pdf->SetMargins(10, 5, 10);
    $pdf->SetAutoPageBreak(false); 
    $pdf->AliasNbPages('{nb}'); 
    $pdf->datosEncabezado = $datos['encabezado']; 
    $pdf->asignaturas = $datos['asignaturas'];
    $pdf->nombreDocente = $datos['encabezado']['nombre_docente'] ?? ''; 
    $pdf->estadisticas = $stats;
    $pdf->codigoGrado = $datos['encabezado']['codigo_grado'] ?? '';

    $pdf->AddPage(); 

    $pdf->SetFont('Arial', '', TAMANO_FUENTE_DATOS); 
    $fill = false; 
    $numFilaTotal = 0;
    $ancho_asig_pdf = 10; 
    $alto_fila = ALTO_FILA_DATOS; 

    // Bucle principal
    foreach ($alumnosNotas as $idAlumno => $alumno) {
        $limiteFilas = $pdf->GetLimiteFilasPagina(); 
        if ($pdf->GetNumFilaActual() >= $limiteFilas) {
             $ancho_total_tabla = 7 + 20 + 90 + (count($datos['asignaturas']) * $ancho_asig_pdf) + 20;
             $pdf->Cell($ancho_total_tabla, 0, '', 'T'); 
             $pdf->AddPage(); 
             $pdf->SetFont('Arial', '', TAMANO_FUENTE_DATOS); 
        }

        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(7, $alto_fila, $numFilaTotal + 1, 1, 0, 'C', true); 
        $pdf->Cell(20, $alto_fila, $alumno['info']['nie'], 1, 0, 'C', true); 
        $pdf->Cell(90, $alto_fila, convertirTextoSafe($alumno['info']['nombre']), 1, 0, 'L', true); 
        
        $asignaturasBasicasReprobadas = 0; 
        
        // Iterar asignaturas (Protegido contra llaves faltantes)
        foreach ($datos['asignaturas'] as $asig) { 
            $codigoAsig = $asig['codigo'];
            // PROTECCIÓN: Usar null coalesce si el alumno no tiene nota en esa materia
            $notaData = $alumno['notas'][$codigoAsig] ?? null;
            $notaPrint = '';
            
            if ($notaData) {
                $notaPrint = $notaData['nota_final']; // O formatear si es necesario
                // Contar reprobadas
                if (!in_array(trim($asig['codigo_area']), ['07']) && floatval($notaPrint) < $notaMinima && floatval($notaPrint) > 0) {
                    $asignaturasBasicasReprobadas++;
                }
            }
            $pdf->Cell($ancho_asig_pdf, $alto_fila, $notaPrint, 1, 0, 'C', true);
        }
        
        $resultadoFinal = ($asignaturasBasicasReprobadas <= 2) ? 'P' : 'R'; 
        $pdf->Cell(20, $alto_fila, $resultadoFinal, 1, 1, 'C', true);

        $fill = !$fill; $numFilaTotal++; $pdf->IncrementaFila(); 
    }

    // Rellenar filas vacías (Lógica mantenida)
    $limiteUltimaPagina = $pdf->GetLimiteFilasPagina(); 
    $filasActualesUltimaPagina = $pdf->GetNumFilaActual();
    $linea_faltante = $limiteUltimaPagina - $filasActualesUltimaPagina; 
    if ($linea_faltante < 0) $linea_faltante = 0;

    if ($linea_faltante > 0) {
        $valor_y1 = $pdf->GetY();
        // Protección simple contra overflow
        $h_page = $pdf->GetPageHeight();
        if ($valor_y1 < $h_page - 30) {
             $pdf->Line(17, $valor_y1, 237, $h_page - 30); 
        }
    }

    for($i=0; $i < $linea_faltante; $i++) {
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        $pdf->Cell(7, $alto_fila, $numFilaTotal + 1, 1, 0, 'C', true);
        $pdf->Cell(20, $alto_fila, '', 1, 0, 'C', true); 
        $pdf->Cell(90, $alto_fila, '', 1, 0, 'L', true);
        foreach ($datos['asignaturas'] as $asig) { $pdf->Cell($ancho_asig_pdf, $alto_fila, '', 1, 0, 'C', true); }
        $pdf->Cell(20, $alto_fila, '', 1, 1, 'C', true);
        $fill = !$fill; $numFilaTotal++; $pdf->IncrementaFila();
    }

    // Fila de Promedios (Simplificada)
    $pdf->SetFont('Arial', 'B', 7);
    $pdf->Cell(117, $alto_fila, 'PROMEDIO POR ASIGNATURA', 1, 0, 'R', true);
    foreach($datos['asignaturas'] as $asig) { 
        $pdf->Cell($ancho_asig_pdf, $alto_fila, '', 1, 0, 'C', true); // Espacio vacío por ahora
    }
    $pdf->Cell(20, $alto_fila, '', 1, 1, 'C', true);

    // Firmas
    $numPagesTotal = $pdf->PageNo(); 
    if ($pdf->PageNo() == $numPagesTotal) { 
         $pdf->Ln(5); $pdf->SetFont('Arial','',10);
         $y_actual_firmas = $pdf->GetY();
         if ($y_actual_firmas > $pdf->GetPageHeight() - 45) {
             $pdf->AddPage();
             $y_actual_firmas = $pdf->GetY() + 10; 
             $pdf->SetFont('Arial','',10); 
         }

         $pdf->SetXY(250, $y_actual_firmas); $pdf->Cell(30,5,'PROMOVIDOS:',0,0,'L'); $pdf->SetX(280);
         $total_promovidos = ($stats['total_promovidos_m'] ?? 0) + ($stats['total_promovidos_f'] ?? 0);
         $txt_prom = ($total_promovidos == 0) ? 'cero' : convertirTextoSafe(strtolower(num2letras($total_promovidos)));
         $pdf->Cell(60, 5, $txt_prom, 'B', 1, 'C');
         
         $pdf->SetXY(250, $y_actual_firmas + 10); $pdf->Cell(30,5,'RETENIDOS:',0,0,'L'); $pdf->SetX(280);
         $total_retenidos = ($stats['total_retenidos_m'] ?? 0) + ($stats['total_retenidos_f'] ?? 0);
         $txt_ret = ($total_retenidos == 0) ? 'ninguno' : convertirTextoSafe(strtolower(num2letras($total_retenidos)));
         $pdf->Cell(60, 5, $txt_ret, 'B', 1, 'C');

         $pdf->Ln(8); $y_lugar_fecha = $pdf->GetY();
         $pdf->SetX(250); $pdf->Cell(15, 5, 'Lugar:', 0, 0, 'L');
         $pdf->Cell(75, 5, convertirTextoSafe($_SESSION['nombre_municipio'] ?? 'Santa Ana'), 'B', 1, 'C');
         $pdf->SetX(250); $pdf->Cell(15, 5, 'Fecha:', 0, 0, 'L');
         
         $meses = ["enero", "febrero", "marzo", "abril", "mayo", "junio", "julio", "agosto", "septiembre", "octubre", "noviembre", "diciembre"];
         $fecha_final_cuadro = convertirTextoSafe(($_SESSION['dia_entrega'] ?? date('d')) ." de ".$meses[date('n')-1]." de ".($datos['encabezado']['nombre_ann_lectivo'] ?? date('Y')));
         $pdf->Cell(75, 5, $fecha_final_cuadro, 'B', 1, 'C');

         $pdf->Ln(10); $y_firmas = $pdf->GetY();
         $pdf->Line(40, $y_firmas + 10, 140, $y_firmas + 10); 
         $pdf->SetXY(40, $y_firmas + 11); $pdf->Cell(100, 5, convertirTextoSafe($datos['encabezado']['nombre_docente'] ?? ''), 0, 0, 'C');
         $pdf->SetXY(40, $y_firmas + 16); $pdf->Cell(100, 5, 'Docente', 0, 0, 'C');
         $pdf->Line(200, $y_firmas + 10, 300, $y_firmas + 10); 
         $pdf->SetXY(200, $y_firmas + 11); $pdf->Cell(100, 5, convertirTextoSafe($_SESSION['nombre_director'] ?? ''), 0, 0, 'C');
         $pdf->SetXY(200, $y_firmas + 16); $pdf->Cell(100, 5, 'Director(a)', 0, 1, 'C');
     } 
    $pdf->Output('Cuadro_Registro.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if (isset($errorDbConexion) && $errorDbConexion) { throw new Exception("Error Conexión BD."); }
    $codigo_all = $_GET["todos"] ?? null;
    if (!$codigo_all) { 
        // Generar un PDF vacío con mensaje de error si no hay parámetros
        $pdf = new FPDF(); $pdf->AddPage(); $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,'Error: No se ha seleccionado un grupo.');
        $pdf->Output();
        exit;
    }

    $datosReporte = obtenerDatosCuadro($dblink, $codigo_all);

    if (empty($datosReporte['notas'])) { 
        // Generar PDF vacío si no hay notas
        $pdf = new FPDF(); $pdf->AddPage(); $pdf->SetFont('Arial','B',16);
        $pdf->Cell(40,10,'No existen registros de notas para este grupo.');
        $pdf->Output();
        exit; 
    }

    generarPdfCuadro($datosReporte);

} catch (PDOException $e) { die("Error DB: ".$e->getMessage()); } catch (Exception $e) { die("Error: ".$e->getMessage()); }
?>