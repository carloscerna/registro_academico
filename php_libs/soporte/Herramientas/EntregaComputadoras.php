<?php
// process_excel.php
header('Content-Type: application/json');
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Cargar el autoload de Composer para PhpSpreadsheet
require $path_root."/registro_academico/vendor/autoload.php";
//
// Ruta absoluta donde deseas guardar el archivo modificado
$targetDirectory = 'C:/TempSistemaRegistro';

// Verificar si el directorio existe, si no, crearlo
if (!is_dir($targetDirectory)) {
    mkdir($targetDirectory, 0777, true); // Crear el directorio con permisos de escritura
}

// Nombre del archivo modificado
$outputFileName = 'archivo_modificado.xlsx';

// Ruta completa para guardar el archivo
$outputFilePath = $targetDirectory . '/' . $outputFileName;
//
ini_set('display_errors', 0);
error_reporting(E_ALL);
//
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Validar la subida del archivo Excel
if (!isset($_FILES['excelFile'])) {
    echo json_encode(['status' => 'error', 'message' => 'No se envió ningún archivo.']);
    exit;
}

$file = $_FILES['excelFile'];
if ($file['error'] !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'Error al subir el archivo.']);
    exit;
}

$tmpName  = $file['tmp_name'];
$fileName = $file['name'];
$extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
if ($extension !== 'xlsx') {
    echo json_encode(['status' => 'error', 'message' => 'Solo se permiten archivos con extensión .xlsx']);
    exit;
}

try {
    $spreadsheet = IOFactory::load($tmpName);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error cargando el archivo: ' . $e->getMessage()]);
    exit;
}

// Recuperar parámetros enviados desde el formulario
$readColumn  = strtoupper(trim($_POST['readColumn']));   // Columna donde se encuentran los NIE, p. ej. "A"
$nieStartRow = intval($_POST['nieStartRow']);             // Fila de inicio para leer el listado de NIE
$startColumn = strtoupper(trim($_POST['startColumn']));    // Columna donde escribir resultados, p. ej. "B"
$codigo_ann_lectivo = $_POST['codigo_ann_lectivo'] ?? 0;

// Obtener la hoja activa
$worksheet = $spreadsheet->getActiveSheet();

// Consulta: se extraerán nombre_grado y nombre_seccion para un NIE dado
$query = "SELECT 
            a.codigo_nie, 
            a.id_alumno, 
            am.codigo_bach_o_ciclo, 
            bach.nombre as nombre_bachillerato,
            gan.nombre as nombre_grado, 
            am.codigo_seccion, 
            sec.nombre as nombre_seccion, 
            am.retirado,
            tur.nombre as nombre_turno
          FROM alumno a 
          INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
          INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
          INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
          INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
          INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
          INNER JOIN turno tur ON tur.codigo = am.codigo_turno
          WHERE a.codigo_nie = :nie 
          AND am.codigo_ann_lectivo = :codigo_ann_lectivo";

// Convertir la columna de escritura a índice numérico para facilitar la asignación
$startIndex = Coordinate::columnIndexFromString($startColumn);

// Iterar sobre las filas del listado de NIE
$currentRow = $nieStartRow;
$procesados = 0;
while ( true ) {
    $celdaNie = $readColumn . $currentRow;
    $nie = trim($worksheet->getCell($celdaNie)->getValue());
    // Finaliza la lectura si se encuentra una celda vacía
    if (empty($nie)) {
        break;
    }
    
    // Preparar y ejecutar la consulta para el NIE actual
    $stmt = $dblink->prepare($query);
    $stmt->bindValue(':nie', $nie);
    $stmt->bindValue(':codigo_ann_lectivo', $codigo_ann_lectivo);
    $stmt->execute();
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Definir las celdas para escribir los resultados en la misma fila
    $cellGrado   = Coordinate::stringFromColumnIndex($startIndex)     . $currentRow;
    $cellSeccion = Coordinate::stringFromColumnIndex($startIndex + 1) . $currentRow;
    
    if ($resultado) {
        // Escribimos los datos obtenidos
        $worksheet->setCellValue($cellGrado, $resultado['nombre_grado']);
        $worksheet->setCellValue($cellSeccion, $resultado['nombre_seccion']);
    } else {
        // Si no se encontró el registro, se puede escribir una marca o dejar vacío
        $worksheet->setCellValue($cellGrado, "No encontrado");
        $worksheet->setCellValue($cellSeccion, "No encontrado");
    }
    
    $procesados++;
    $currentRow++;
}

// Guardar el archivo Excel modificado
$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$outputFile = 'archivo_modificado.xlsx';

try {
    $writer->save($outputFile);
    //
    // Verificar si el archivo original existe antes de copiarlo
        if (file_exists($outputFile)) {
            if (copy($outputFile, $outputFilePath)) {
                echo "El archivo se copió exitosamente a: $outputFilePath";
            } else {
                echo "Error al copiar el archivo.";
            }
        } else {
            echo "El archivo original no existe: $outputFile";
        }
    //
    echo json_encode([
        'status' => 'success',
        'message' => "Se procesaron $procesados registros.",
        'download_url' => $outputFile  // URL para descargar el archivo generado
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar el archivo: ' . $e->getMessage()]);
}