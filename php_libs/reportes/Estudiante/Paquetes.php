<?php
// <-- VERSIÓN REFACTORIZADA CON COLUMNA NIE Y DUI FIJO -->

ini_set('display_errors', 1);
error_reporting(E_ALL);

// --- INCLUDES Y CONFIGURACIÓN ---
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/includes/funciones.php";
require_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";

define('FILAS_POR_PAGINA', 10); 
define('ALTURA_FILA', 12);      

/**
 * Clase FPDF personalizada para el reporte de Paquete Escolar.
 */
class PDF_Paquete extends FPDF {
    private $datosReporte = [];

    public function setDatos(array $datos) {
        $this->datosReporte = $datos;
    }
function Header() {
        $params = $this->datosReporte['parametros'];
        $encabezado = $this->datosReporte['encabezado'];

        // --- 1. Títulos principales ---
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 5, convertirtexto("FORMULARIO DE RECEPCIÓN DE BIENES POR PARTE DE LOS PADRES, MADRES, RESPONSABLES"), 0, 1, "C");
        $this->Cell(0, 5, convertirtexto("PROGRAMA DE DOTACIÓN DE UNIFORMES, ZAPATOS Y ÚTILES ESCOLARES AÑO " . $encabezado['nombre_ann_lectivo']), 0, 1, "C");
        
        $this->Ln(5);

        // --- 2. Bloque de Datos Generales ---
        
        // Fila 1: Rubro y Departamento
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'RUBRO:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(125, 5, convertirtexto($params['rubro']), 0, 0, 'L');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'DEPARTAMENTO:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        // NOTA: Asegúrate de que esta variable de sesión exista o la consultaremos en la BD
        $this->Cell(0, 5, convertirtexto($_SESSION['nombre_departamento']), 0, 1, 'L');

        // Fila 2: Fecha y Municipio (El municipio ahora es la agrupación grande, ej: San Salvador Este)
        $fecha = ($params['mostrarFecha'] === 'yes' && !empty($params['fecha'])) ? date("d/m/Y", strtotime($params['fecha'])) : '__________________';
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'FECHA:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(125, 5, $fecha, 0, 0, 'L');
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'MUNICIPIO:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, convertirtexto($_SESSION['nombre_municipio']), 0, 1, 'L');

        // Fila 3: (NUEVA) Distrito (Ej: Soyapango, Ilopango, etc.)
        // Aquí usaremos una variable temporal $distrito. 
        // Cuando me pases las tablas, llenaremos esta variable correctamente desde la BD.
        $distrito = isset($_SESSION['nombre_distrito']) ? $_SESSION['nombre_distrito'] : 'DISTRITO PENDIENTE'; 

        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, '', 0, 0, 'L'); // Espacio vacío a la izquierda (debajo de fecha) o puedes poner otro dato
        $this->Cell(125, 5, '', 0, 0, 'L'); // Espacio vacío
        
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'DISTRITO:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, convertirtexto($distrito), 0, 1, 'L');

        // Fila 4: Código C.E. y Grado
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, convertirtexto('CÓDIGO DEL C.E.:'), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(125, 5, $_SESSION['codigo'], 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'GRADO:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, convertirtexto($encabezado['nombre_grado']), 0, 1, 'L');
        
        // Fila 5: Nombre C.E. y Sección
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, 'NOMBRE DEL C.E.:', 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(125, 5, convertirtexto($_SESSION['institucion']), 0, 0, 'L');
        $this->SetFont('Arial', '', 9);
        $this->Cell(35, 5, convertirtexto('SECCIÓN:'), 0, 0, 'L');
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 5, convertirtexto($encabezado['nombre_seccion']), 0, 1, 'L');
        
        $this->Ln(5);
    }

    function Footer() {
        // ... (Tu pie de página original o uno nuevo) ...
    }

    function TablaEncabezado() {
        $this->SetFillColor(220, 220, 220);
        $this->SetTextColor(0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(.2);
        $this->SetFont('Arial', 'B', 7); // Fuente un poco más pequeña para que quepan los encabezados
        $this->SetY(62); // Posición fija para el inicio de la tabla

        foreach ($this->datosReporte['header'] as $key => $columna) {
            $this->Cell($this->datosReporte['widths'][$key], ALTURA_FILA, convertirtexto($columna), 1, 0, 'C', true);
        }
        $this->Ln();
    }
}

/**
 * Obtiene todos los datos para el reporte.
 */
function obtenerDatosPaquete(PDO $pdo, array $params): array {
    $datos = ['encabezado' => [], 'alumnos' => [], 'parametros' => $params];

    $sqlEncabezado = "SELECT btrim(gan.nombre) as nombre_grado, btrim(sec.nombre) as nombre_seccion, ann.nombre as nombre_ann_lectivo 
                      FROM alumno_matricula am
                      INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                      INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                      INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                      WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ?
                      LIMIT 1";
    $stmt = $pdo->prepare($sqlEncabezado);
    $stmt->execute([$params['codigoAll']]);
    $datos['encabezado'] = $stmt->fetch(PDO::FETCH_ASSOC);

    $sqlAlumnos = "SELECT btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                   a.codigo_nie, lower(a.genero) as genero,
                   btrim(ae.nombres) as nombre_encargado, ae.dui
                   FROM alumno a
                   INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                   LEFT JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
                   WHERE btrim(am.codigo_bach_o_ciclo::text || am.codigo_grado::text || am.codigo_seccion::text || am.codigo_ann_lectivo::text || am.codigo_turno::text) = ?
                   ORDER BY apellido_alumno";
    $stmt = $pdo->prepare($sqlAlumnos);
    $stmt->execute([$params['codigoAll']]);
    $datos['alumnos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $datos;
}

/**
 * Genera el PDF del reporte.
 */
function generarPdfPaquete(array $datos) {
    $pdf = new PDF_Paquete('L', 'mm', 'Letter');
    $pdf->SetMargins(10, 15, 10);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AliasNbPages();

    // Configuración condicional de la tabla
    $rubro = $datos['parametros']['rubro'];
    $esUtiles = in_array($rubro, ["Útiles Escolares", "Familias", "Libro de ESMATE", "Libro de Lenguaje"]);
    
    // --- CAMBIOS EN ANCHOS Y ENCABEZADOS ---
    // Total disponible aprox: 255mm - 260mm. 
    // Nuevos anchos: 8(N)+18(NIE)+70(Nom)+8(M)+8(F)+12(Tal)+66(Enc)+25(DUI)+40(Fir) = 255mm
    
    if ($esUtiles) {
        $datos['header'] = ['Nº', 'NIE', 'NOMBRE DEL ESTUDIANTE', 'M', 'F', 'CICLO', 'NOMBRE DEL PADRE/MADRE O RESPONSABLE', 'Nº DUI', 'FIRMA'];
        $datos['widths'] = [8, 18, 70, 8, 8, 12, 66, 25, 40];
    } else {
        $datos['header'] = ['Nº', 'NIE', 'NOMBRE DEL ESTUDIANTE', 'M', 'F', 'TALLA', 'NOMBRE DEL PADRE/MADRE O RESPONSABLE', 'Nº DUI', 'FIRMA'];
        $datos['widths'] = [8, 18, 70, 8, 8, 12, 66, 25, 40];
    }
    
    $pdf->setDatos($datos);
    $pdf->AddPage();
    $pdf->TablaEncabezado();

    $pdf->SetFont('Arial', '', 8);
    $fill = false;
    $numFila = 0;
    
    foreach ($datos['alumnos'] as $alumno) {
        if ($numFila > 0 && $numFila % FILAS_POR_PAGINA == 0) {
            $pdf->AddPage();
            $pdf->TablaEncabezado();
            $pdf->SetFont('Arial', '', 8);
        }

        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        
        // 0. Número correlativo
        $pdf->Cell($datos['widths'][0], ALTURA_FILA, $numFila + 1, 1, 0, 'C', true);

        // 1. NUEVA COLUMNA: NIE
        $pdf->Cell($datos['widths'][1], ALTURA_FILA, $alumno['codigo_nie'], 1, 0, 'C', true);

        // 2. Nombre Estudiante
        $pdf->Cell($datos['widths'][2], ALTURA_FILA, convertirtexto($alumno['apellido_alumno']), 1, 0, 'L', true);
        
        // 3 y 4. Género
        $pdf->Cell($datos['widths'][3], ALTURA_FILA, ($alumno['genero'] == 'm' ? 'M' : ''), 1, 0, 'C', true);
        $pdf->Cell($datos['widths'][4], ALTURA_FILA, ($alumno['genero'] == 'f' ? 'F' : ''), 1, 0, 'C', true);
        
        // 5. Talla o Ciclo
        $pdf->Cell($datos['widths'][5], ALTURA_FILA, '', 1, 0, 'C', true); 

        // 6. Nombre Encargado
        $pdf->Cell($datos['widths'][6], ALTURA_FILA, convertirtexto($alumno['nombre_encargado']), 1, 0, 'L', true);

        // 7. COLUMNA MODIFICADA: Solo DUI
        $pdf->Cell($datos['widths'][7], ALTURA_FILA, $alumno['dui'], 1, 0, 'C', true);
        
        // 8. Firma
        $pdf->Cell($datos['widths'][8], ALTURA_FILA, '', 1, 0, 'C', true);

        $pdf->Ln();
        $fill = !$fill;
        $numFila++;
    }

    // Rellenar filas vacías (Si es necesario completar la página)
    $filasEnPagina = $numFila % FILAS_POR_PAGINA;
    if ($filasEnPagina == 0 && $numFila > 0) $filasEnPagina = FILAS_POR_PAGINA;
    $filasFaltantes = ($numFila == 0) ? FILAS_POR_PAGINA : FILAS_POR_PAGINA - $filasEnPagina;
    
    for ($i = 0; $i < $filasFaltantes; $i++) {
        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
        foreach ($datos['widths'] as $width) {
            $pdf->Cell($width, ALTURA_FILA, '', 1, 0, 'C', true);
        }
        $pdf->Ln();
        $fill = !$fill;
    }

    $pdf->Output('Paquete_Escolar.pdf', 'I');
}

// --- PUNTO DE ENTRADA DEL SCRIPT ---
try {
    if ($errorDbConexion) { throw new Exception("No se puede conectar a la base de datos."); }

    $params = [
        'codigoAll' => $_GET["todos"] ?? null,
        'fecha' => $_GET["fechapaquete"] ?? null,
        'rubro' => $_GET["rubro"] ?? 'Paquete de Útiles Escolares',
        'mostrarFecha' => $_GET["chkfechaPaquete"] ?? 'no',
        'mostrarNIE' => $_GET["chkNIEPaquete"] ?? 'no'
    ];
    
    if (!$params['codigoAll']) { throw new Exception("Faltan parámetros para generar el reporte."); }

    $datosReporte = obtenerDatosPaquete($dblink, $params);

    if (empty($datosReporte['alumnos'])) {
        echo "No se encontraron alumnos para este grupo. Verifique los filtros.";
        exit;
    }

    generarPdfPaquete($datosReporte);

} catch (Exception $e) {
    header("Content-Type: text/html; charset=UTF-8");
    echo "<h1>Error al generar el reporte</h1>";
    echo "<p>Detalles del error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>