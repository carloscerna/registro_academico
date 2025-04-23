<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Indicamos que la respuesta es de tipo JSON (excepto en listar, pero la convertiremos a JSON también)
    header('Content-Type: application/json; charset=utf-8');
// Verificamos que se haya enviado el parámetro action
if (!isset($_REQUEST['accion'])) {
    echo json_encode([
        "response" => false,
        "message"  => "No se especificó acción",
        "error"    => "Parámetro action faltante"
    ]);
    exit();
}
 
// pasar la conexion
$pdo = $dblink;

$accion = $_POST['accion'] ?? '';
 
function siguienteCodigo($pdo, $codigo_area) {
    $stmt = $pdo->prepare("SELECT MAX(CAST(codigo AS INTEGER)) FROM catalogo_area_dimension WHERE codigo_area = ?");
    $stmt->execute([$codigo_area]);
    $ultimo = $stmt->fetchColumn();
    $nuevo = $ultimo ? $ultimo + 1 : 1;
    return str_pad($nuevo, 3, "0", STR_PAD_LEFT);
}


    $accion = $_POST['accion'] ?? '';
    try {
        switch ($accion) {
            case 'listar':
                $stmt = $pdo->prepare("SELECT id_, codigo, descripcion, codigo_area FROM catalogo_area_dimension WHERE codigo_area = ?");
                $stmt->execute([$_POST['codigo_area']]);
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['data' => $data]);
                break;
        
            case 'guardar':
                $codigo_area = $_POST['codigo_area'];
            
                if (empty($_POST['id_'])) {
                    $codigo = siguienteCodigo($pdo, $codigo_area);
                    $sql = "INSERT INTO catalogo_area_dimension (codigo, descripcion, codigo_area) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$codigo, $_POST['descripcion'], $codigo_area]);
                } else {
                    $sql = "UPDATE catalogo_area_dimension SET descripcion=? WHERE id_=?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$_POST['descripcion'], $_POST['id_']]);
                }
            
                echo json_encode(['status' => 'success']);
                break;
        
            case 'obtener':
                $id = $_POST['id_'];
                $stmt = $pdo->prepare("SELECT * FROM catalogo_area_dimension WHERE id_ = ?");
                $stmt->execute([$id]);
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode($data);
                break;
        
            case 'eliminar':
                $id = $_POST['id_'];
                $stmt = $pdo->prepare("DELETE FROM catalogo_area_dimension WHERE id_ = ?");
                $stmt->execute([$id]);
                echo json_encode(['status' => 'success']);
                break;
            case 'siguiente_codigo':
                $codigo = siguienteCodigo($pdo, $_POST['codigo_area']);
                echo json_encode(['codigo' => $codigo]);
                break;
            default:
                echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}