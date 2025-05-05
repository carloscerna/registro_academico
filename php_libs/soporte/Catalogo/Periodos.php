<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Indicamos que la respuesta es de tipo JSON (excepto en listar, pero la convertiremos a JSON también)
    header('Content-Type: application/json; charset=utf-8');
//
$pdo = $dblink;
try {
    if (!isset($_POST["action"])) {
        echo json_encode(["error" => "Acción no definida"]);
        exit;
    }

    $action = $_POST["action"];

    // 📌 Guardar período
    if ($action === "guardar") {
        if (!isset($_POST['lstmodalidad']) || !isset($_POST['cantidad_periodos'])) {
            echo json_encode(["error" => "Parámetros insuficientes"]);
            exit;
        }

        $codigoModalidad = $_POST['lstmodalidad'];
        $cantidadPeriodos = $_POST['cantidad_periodos'];

        $query = "INSERT INTO catalogo_periodo (codigo_modalidad, cantidad_periodos) VALUES (:codigoModalidad, :cantidadPeriodos)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':codigoModalidad', $codigoModalidad, PDO::PARAM_INT);
        $stmt->bindParam(':cantidadPeriodos', $cantidadPeriodos, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => "Período guardado correctamente"]);
    }

    // 📌 Editar período
    elseif ($action === "editar") {
        if (!isset($_POST['id']) || !isset($_POST['lstmodalidad']) || !isset($_POST['cantidad_periodos'])) {
            echo json_encode(["error" => "Parámetros insuficientes"]);
            exit;
        }

        $idPeriodo = $_POST['id'];
        $codigoModalidad = $_POST['lstmodalidad'];
        $cantidadPeriodos = $_POST['cantidad_periodos'];

        $query = "UPDATE catalogo_periodo SET codigo_modalidad = :codigoModalidad, cantidad_periodos = :cantidadPeriodos WHERE id = :idPeriodo";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idPeriodo', $idPeriodo, PDO::PARAM_INT);
        $stmt->bindParam(':codigoModalidad', $codigoModalidad, PDO::PARAM_INT);
        $stmt->bindParam(':cantidadPeriodos', $cantidadPeriodos, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => "Período actualizado correctamente"]);
    }

    // 📌 Listar períodos
    elseif ($action === "listar") {
        $query = "SELECT id, codigo_modalidad, cantidad_periodos FROM catalogo_periodo ORDER BY id";
        $stmt = $pdo->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($result);
    }

    // 📌 Eliminar período
    elseif ($action === "eliminar") {
        if (!isset($_POST['id'])) {
            echo json_encode(["error" => "ID no proporcionado"]);
            exit;
        }

        $idPeriodo = $_POST['id'];
        $query = "DELETE FROM catalogo_periodo WHERE id = :idPeriodo";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idPeriodo', $idPeriodo, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => "Período eliminado correctamente"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Error en la operación: " . $e->getMessage()]);
}
?>