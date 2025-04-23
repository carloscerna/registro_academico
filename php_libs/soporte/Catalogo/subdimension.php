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
//
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $codigo_area = $_POST['codigo_area'];
        $codigo_dimension = $_POST['codigo_dimension'];

        $stmt = $pdo->prepare("SELECT id_, codigo, descripcion FROM catalogo_area_subdimension WHERE codigo_area = ? AND codigo_dimension = ?");
        $stmt->execute([$codigo_area, $codigo_dimension]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['data' => $data]);
        break;

    case 'guardar':
        $id = $_POST['id_'];
        $descripcion = $_POST['descripcion'];
        $codigo_area = $_POST['codigo_area'];
        $codigo_dimension = $_POST['codigo_dimension'];

        if (empty($id)) {
            // Nuevo registro
            $codigo = siguienteCodigo($pdo, $codigo_area, $codigo_dimension);
            $sql = "INSERT INTO catalogo_area_subdimension (codigo, descripcion, codigo_area, codigo_dimension) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$codigo, $descripcion, $codigo_area, $codigo_dimension]);
        } else {
            // Actualizar
            $sql = "UPDATE catalogo_area_subdimension SET descripcion=? WHERE id_=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$descripcion, $id]);
        }

        echo json_encode(['status' => 'success']);
        break;

    case 'eliminar':
        $id = $_POST['id_'];
        $stmt = $pdo->prepare("DELETE FROM catalogo_area_subdimension WHERE id_=?");
        $stmt->execute([$id]);

        echo json_encode(['status' => 'success']);
        break;

    case 'siguiente_codigo':
        $codigo = siguienteCodigo($pdo, $_POST['codigo_area'], $_POST['codigo_dimension']);
        echo json_encode(['codigo' => $codigo]);
        break;
}

function siguienteCodigo($pdo, $codigo_area, $codigo_dimension) {
    $stmt = $pdo->prepare("SELECT MAX(CAST(codigo AS INTEGER)) FROM catalogo_area_subdimension WHERE codigo_area = ? AND codigo_dimension = ?");
    $stmt->execute([$codigo_area, $codigo_dimension]);
    $ultimo = $stmt->fetchColumn();
    $nuevo = $ultimo ? $ultimo + 1 : 1;
    return str_pad($nuevo, 3, "0", STR_PAD_LEFT);
}
