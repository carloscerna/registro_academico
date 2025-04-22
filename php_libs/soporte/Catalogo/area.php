<?php

// Conexión (se espera que "conexion.php" cree la variable $pdo)
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

$accion = $_REQUEST["accion"];
 
// pasar la conexion
$pdo = $dblink;

switch ($accion) {
    case 'listar':
        $stmt = $pdo->query("SELECT * FROM catalogo_area_asignatura ORDER BY id_area_asignatura ASC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['data' => $data]);
        break;

    case 'guardar':
        if (empty($_POST['id_area_asignatura'])) {
            $codigo = siguienteCodigo($pdo);
            $sql = "INSERT INTO catalogo_area_asignatura (codigo, descripcion) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $_POST['descripcion']]);
        } else {
            $sql = "UPDATE catalogo_area_asignatura SET descripcion=? WHERE id_area_asignatura=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$_POST['descripcion'], $_POST['id_area_asignatura']]);
        }
        echo json_encode(['status' => 'success']);
        break;

    case 'obtener':
        $id = $_POST['id_area_asignatura'];
        $stmt = $pdo->prepare("SELECT * FROM catalogo_area_asignatura WHERE id_area_asignatura = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($data);
        break;

    case 'eliminar':
        $id = $_POST['id_area_asignatura'];
        $stmt = $pdo->prepare("DELETE FROM catalogo_area_asignatura WHERE id_area_asignatura = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'success']);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
}
function siguienteCodigo($pdo) {
    $stmt = $pdo->query("SELECT MAX(CAST(codigo AS INTEGER)) FROM catalogo_area_asignatura");
    $ultimo = $stmt->fetchColumn();
    $nuevo = $ultimo ? $ultimo + 1 : 1;
    return str_pad($nuevo, 3, "0", STR_PAD_LEFT);
}
