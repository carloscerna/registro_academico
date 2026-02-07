<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Cargar el autoload de Composer para PhpSpreadsheet
require $path_root . "/registro_academico/vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=utf-8');

$response = ['exito' => false, 'mensaje' => '', 'datos' => []];

// Validar inputs
if (!isset($_FILES['archivoExcel']) || $_FILES['archivoExcel']['error'] !== 0) {
    $response['mensaje'] = 'Archivo no recibido o con error.';
    echo json_encode($response); exit;
}

if (empty($_POST['filaInicio']) || empty($_POST['colNIE']) || empty($_POST['colNombre']) ||
    empty($_POST['lstannlectivo']) || empty($_POST['lstmodalidad']) || empty($_POST['lstgradoseccion'])) {
    $response['mensaje'] = 'Faltan datos requeridos del formulario.';
    echo json_encode($response); exit;
}

// Función: convertir letra de columna (A, B, C...) a índice numérico (0, 1, 2...)
function colLetraAIndice($letra) {
    // CORRECCIÓN PHP 8: Asegurar que $letra sea string antes de trim
    $letra = strtoupper(trim((string)$letra));
    $col = 0;
    for ($i = 0; $i < strlen($letra); $i++) {
        $col = $col * 26 + (ord($letra[$i]) - ord('A') + 1);
    }
    return $col - 1;
}

// Leer parámetros
$colNIE = colLetraAIndice($_POST['colNIE']);
$colNombre = colLetraAIndice($_POST['colNombre']);
$filaInicio = intval($_POST['filaInicio']);

// Leer selects destino
$annLectivo = $_POST['lstannlectivo'];
$modalidad = $_POST['lstmodalidad'];
$gradoSeccion = $_POST['lstgradoseccion'];

try {
    $archivoTmp = $_FILES['archivoExcel']['tmp_name'];
    $documento = IOFactory::load($archivoTmp);
    $hoja = $documento->getActiveSheet();

    include_once $path_root . "/registro_academico/includes/mainFunctions_conexion.php";
    if ($errorDbConexion) {
        $response['mensaje'] = 'Error de conexión a la base de datos.';
        echo json_encode($response); exit;
    }

    $datos = [];
    $coincidencias = 0;

    foreach ($hoja->getRowIterator($filaInicio) as $fila) {
        $rowIndex = $fila->getRowIndex(); 

        $cellNIE = $hoja->getCellByColumnAndRow($colNIE + 1, $rowIndex)->getValue();
        $cellNombre = $hoja->getCellByColumnAndRow($colNombre + 1, $rowIndex)->getValue();

        // ▼▼▼ CORRECCIÓN CRÍTICA PARA PHP 8 ▼▼▼
        // getValue() puede devolver null. En PHP 8, trim(null) causa error.
        // Forzamos (string) para que null se convierta en "" (vacío).
        $codigo_nie = trim((string)$cellNIE);
        $nombre_completo_raw = trim((string)$cellNombre);

        if (empty($codigo_nie) || empty($nombre_completo_raw)) continue;

        // Separar nombre
        $apellido_paterno = '';
        $apellido_materno = '';
        $nombre_completo = '';

        if (strpos($nombre_completo_raw, ',') !== false) {
            // Usamos una variable temporal para el explode
            $partesNombre = explode(',', $nombre_completo_raw, 2);
            
            // Validamos que el array tenga los elementos esperados
            if (count($partesNombre) >= 2) {
                $apellidos = trim($partesNombre[0]);
                $nombres = trim($partesNombre[1]);

                $apellidos_parts = explode(' ', $apellidos);
                $apellido_paterno = $apellidos_parts[0] ?? '';
                $apellido_materno = $apellidos_parts[1] ?? ''; // Operador ?? evita error si no existe segundo apellido
                $nombre_completo = $nombres;
            } else {
                // Fallback si la coma existe pero no separó bien
                $nombre_completo = $nombre_completo_raw;
            }
        } else {
            // Si no hay coma, asignamos todo al nombre (o ajusta según tu lógica)
            $nombre_completo = $nombre_completo_raw;
        }

        // Buscar NIE en la BD
        $stmt = $dblink->prepare("SELECT codigo_nie FROM alumno WHERE codigo_nie = ? LIMIT 1");
        $stmt->execute([$codigo_nie]);
        $existe = $stmt->rowCount() > 0;
        if ($existe) $coincidencias++;

        // Agregar resultado
        $datos[] = [
            'codigo_nie' => $codigo_nie,
            'apellido_paterno' => $apellido_paterno,
            'apellido_materno' => $apellido_materno,
            'nombre_completo' => $nombre_completo,
            'encontrado' => $existe
        ];
    }

    $response['exito'] = true;
    $response['mensaje'] = "Procesado correctamente. Coincidencias: $coincidencias.";
    $response['datos'] = $datos;

} catch (Exception $e) {
    $response['mensaje'] = 'Error al procesar el archivo: ' . $e->getMessage();
}

echo json_encode($response);
?>