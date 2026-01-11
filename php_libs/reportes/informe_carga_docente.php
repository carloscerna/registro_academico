<?php
// <-- VERSIÓN BLINDADA PHP 8.3: informe_carga_docente.php -->

// 1. LIMPIEZA DE BÚFER (VITAL PARA FPDF)
ob_start();
// Silenciar errores deprecated para que no rompan el PDF
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
ini_set('display_errors', 0);

// 2. FUNCIÓN DE COMPATIBILIDAD (UTF-8 FIX)
if (!function_exists('utf8_decode_fix')) {
    function utf8_decode_fix($texto) {
        if (is_null($texto)) return '';
        // Convierte de UTF-8 (Base de Datos) a ISO-8859-1 (FPDF)
        return mb_convert_encoding((string)$texto, 'ISO-8859-1', 'UTF-8');
    }
}

// 3. FUNCIÓN REEMPLAZO DE UTF8_ENCODE
if (!function_exists('utf8_encode_fix')) {
    function utf8_encode_fix($texto) {
        if (is_null($texto)) return '';
        // Convierte de ISO-8859-1 a UTF-8
        return mb_convert_encoding((string)$texto, 'UTF-8', 'ISO-8859-1');
    }
}

// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// archivos que se incluyen.
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/consultas.php");
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
require($path_root."/registro_academico/php_libs/fpdf/fpdf.php"); // Usar require para asegurar carga

// cambiar a utf-8.
header("Content-Type: text/html; charset=UTF-8");

// variables y consulta a la tabla.
// Protección básica contra nulos en $_REQUEST
$codigo_annlectivo = $_REQUEST['codigo_annlectivo'] ?? '';
$codigo_docente = $_REQUEST['codigo_docente'] ?? '';
$db_link = $dblink;

// armando el Query. PARA LA TABLA HISTORIAL.
$query_cd = "SELECT DISTINCT cd.id_carga_docente, cd.codigo_bachillerato, cd.codigo_asignatura, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno, cd.codigo_docente,
                    bach.nombre as nombre_bachillerato, grado.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
                    asig.nombre as nombre_asignatura, asig.codigo, ann.nombre as nombre_ann_lectivo
                    from carga_docente cd
                    INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
                    INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
                    INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
                    INNER JOIN personal pd ON pd.id_personal = (cd.codigo_docente)::int
                    INNER JOIN grado_ano grado ON grado.codigo = cd.codigo_grado
                    INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
                    INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
                        WHERE cd.codigo_ann_lectivo = '$codigo_annlectivo' and cd.codigo_docente = '$codigo_docente' 
                            ORDER BY cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, asig.codigo";

// Query para revisar la tabla personal.
$query_nombres_personal = "SELECT p.id_personal as codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_c FROM personal p WHERE p.codigo_estatus = '01' and p.id_personal = '$codigo_docente' ORDER BY nombre_c";

// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
try {
    $consulta_cd = $dblink->query($query_cd);
    $consulta_encabezado = $dblink->query($query_nombres_personal);
} catch (PDOException $e) {
    ob_end_clean();
    die("Error en consulta: " . $e->getMessage());
}

$nombre_docente = '';
$nombre_ann_lectivo = '';

// Obtener el encabezado.
if ($consulta_encabezado->rowCount() > 0) {
    while ($listadoPersonalE = $consulta_encabezado->fetch(PDO::FETCH_BOTH)) {
        // CORRECCIÓN: utf8_decode -> utf8_decode_fix
        $nombre_docente = utf8_decode_fix(trim($listadoPersonalE['nombre_c'] ?? ''));
        break;
    }
}

// Obtener el nombre del año lectivo.
// Nota: Se ejecuta consulta_cd aquí solo para sacar el nombre, luego se debe re-ejecutar o mover el puntero
// Como PDO no tiene data_seek fácil, ejecutamos fetch una vez y guardamos el dato, o consultamos aparte.
// Mejor estrategia: Sacar el nombre del primer row y luego resetear o usar fetchAll.
// Dado que abajo se vuelve a hacer "$consulta_cd = $dblink -> query($query_cd);", está bien iterar aquí.

while ($listadoCD = $consulta_cd->fetch(PDO::FETCH_BOTH)) {
    // CORRECCIÓN: utf8_encode -> utf8_encode_fix o mb_convert_encoding
    // Si la DB ya está en UTF8, NO necesitas encode. Si está en LATIN1, sí.
    // Asumiendo que PostgreSQL suele estar en UTF8, utf8_encode rompería los caracteres.
    // Lo dejaré "limpio" (sin encode) primero. Si salen símbolos raros, usa utf8_encode_fix.
    $nombre_ann_lectivo = trim($listadoCD['nombre_ann_lectivo'] ?? ''); 
    break;
}

// Re-bobinar la consulta principal para la tabla (o volver a ejecutarla)
$consulta_cd = $dblink->query($query_cd); 

class PDF extends FPDF
{
    //Cabecera de página
    function Header(){
        //Logo
        global $nombre_docente, $nombre_ann_lectivo;
        
        $logo_dos = $_SESSION['logo_dos'] ?? 'no_image.png';
        // Ajuste de ruta de imagen (session path root puede variar)
        $path_img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $logo_dos;
        
        if (file_exists($path_img)) {
            $this->Image($path_img, 10, 5, 12, 15);
        }
        
        //Arial bold 15
        $this->SetFont('Arial','B',12);
        //Movernos a la derecha
        $this->Cell(20);
        //Título
        // CORRECCIÓN: utf8_decode_fix
        $this->Cell(250, 4, utf8_decode_fix('CARGA ACADÉMICA  -  ') . ($nombre_docente), 0, 1, 'C');
        $this->SetFont('Arial','B',10);
        $this->SetX(30);
        
        $institucion = $_SESSION['institucion'] ?? '';
        $codigo = $_SESSION['codigo'] ?? '';
        
        $this->Cell(130, 4, 'CENTRO EDUCATIVO: ' . utf8_decode_fix($institucion), 0, 0, 'L');
        $this->Cell(40, 4, utf8_decode_fix('CÓDIGO: ' . $codigo), 0, 0, 'L');
        $this->SetX(210);
        // El año lectivo suele venir en UTF8 de la DB, para FPDF necesitamos decodificarlo a ISO
        $this->Cell(20, 4, utf8_decode_fix('AÑO LECTIVO: ' . $nombre_ann_lectivo), 0, 1, 'L');
        $this->SetXY(0,0);
    }

    //Pie de página
    function Footer(){
        // Establecer formato para la fecha.
        date_default_timezone_set('America/El_Salvador');
        // setlocale(LC_TIME, 'spanish'); // Cuidado con setlocale en Windows/Linux, a veces falla.
        
        //Posición: a 1,5 cm del final
        $this->SetY(-10);
        //Arial italic 8
        $this->SetFont('Arial','I',8);
        //Crear una línea
        $this->Line(10, 285, 200, 285);
        //Número de página
        $fecha = date("d/m/Y H:i:s"); // Formato estándar seguro
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}       ' . $fecha, 0, 0, 'C');
    }
    
    //Tabla coloreada
    function FancyTable($header){
        //Colores, ancho de línea y fuente en negrita
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B',10);
        
        $w2 = array(5, 80, 50, 120); //determina el ancho de las columnas

        // primera fila
        $this->Cell($w2[0], 5, utf8_decode_fix('Nº'), 1, 0, 'C', 1);
        $this->Cell($w2[1], 5, 'MODALIDAD', 1, 0, 'C', 1);
        $this->Cell($w2[2], 5, utf8_decode_fix('GRADO - SECCIÓN - TURNO'), 1, 0, 'C', 1);
        $this->Cell($w2[3], 5, 'NOMBRE ASIGNATURA', 1, 0, 'C', 1);
        $this->LN();
        
        $this->Ln();
        //Restauración de colores y fuentes
        $this->SetFillColor(255,255,255);
        $this->SetTextColor(0);
        $this->SetFont('');
    }
}

//************************************************************************************************************************
// Creando el Informe.
$pdf = new PDF('L','mm','Letter'); 
$pdf->AliasNbPages(); 
$pdf->SetFont('Arial','',12);
$pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
$pdf->SetFont('Arial','B',14); 
$pdf->SetY(20); 
$pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
$pdf->SetFont('Arial','',10); 
// Salto de línea.
$pdf->ln();
$pdf->SetFont('Arial','',10); 
$pdf->FancyTable([]); // Cargar encabezado

$w2 = array(5, 80, 50, 120); 
$fill = false; 
$num = 1;

// lazo while.
while ($listadoCD = $consulta_cd->fetch(PDO::FETCH_BOTH)) {
    // recopilar los valores de los campos con protección
    // CORRECCIÓN: utf8_decode_fix
    
    // $id_carga = trim($listadoCD['id_carga_docente'] ?? '');
    
    $nombre_modalidad = utf8_decode_fix(trim($listadoCD['nombre_bachillerato'] ?? ''));
    
    $nombre_gst = utf8_decode_fix(trim($listadoCD['nombre_grado'] ?? '')) . ' ' . 
                  trim($listadoCD['nombre_seccion'] ?? '') . ' ' . 
                  trim($listadoCD['nombre_turno'] ?? '');
                  
    $nombre_asignatura = "'" . trim($listadoCD['codigo_asignatura'] ?? '') . "'" . " " . 
                         utf8_decode_fix(trim($listadoCD['nombre_asignatura'] ?? ''));
    
    // Imprimir valores
    $pdf->Cell($w2[0], 6, $num, 1, 0, 'C', 1);
    $pdf->Cell($w2[1], 6, $nombre_modalidad, 1, 0, 'L', 1);
    $pdf->Cell($w2[2], 6, $nombre_gst, 1, 0, 'C', 1);
    $pdf->Cell($w2[3], 6, $nombre_asignatura, 1, 1, 'L', 1);            
    
    // Aumentar el valor
    $num++;
}

$pdf->SetFont('Arial','',10); 

// 4. SALIDA FINAL
ob_end_clean(); // Borrar cualquier basura del buffer
$pdf->Output();
?>