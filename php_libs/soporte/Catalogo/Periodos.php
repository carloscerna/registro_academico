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
        if (!isset($_POST['lstmodalidad']) || !isset($_POST['cantidad_periodos']) || !isset($_POST['calificacion_minima'])) {
            echo json_encode(["error" => "Parámetros insuficientes"]);
            exit;
        }

        $codigoModalidad = $_POST['lstmodalidad'];
        $cantidadPeriodos = $_POST['cantidad_periodos'];
        $calificacionMinima = $_POST['calificacion_minima'];

        // 📌 Validar si ya existe el código de modalidad en la tabla (SOLO PARA GUARDAR)
        $queryValidacion = "SELECT COUNT(*) FROM catalogo_periodos WHERE codigo_modalidad = :codigoModalidad";
        $stmtValidacion = $pdo->prepare($queryValidacion);
        $stmtValidacion->bindParam(':codigoModalidad', $codigoModalidad, PDO::PARAM_INT);
        $stmtValidacion->execute();
        $existe = $stmtValidacion->fetchColumn();

        if ($existe > 0) {
            echo json_encode(["error" => "Ya existe un período registrado para esta modalidad"]);
            exit;
        }

        $query = "INSERT INTO catalogo_periodos (codigo_modalidad, cantidad_periodos, calificacion_minima) VALUES (:codigoModalidad, :cantidadPeriodos, :calificacionMinima)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':codigoModalidad', $codigoModalidad, PDO::PARAM_INT);
        $stmt->bindParam(':cantidadPeriodos', $cantidadPeriodos, PDO::PARAM_INT);
        $stmt->bindParam(':calificacionMinima', $calificacionMinima, PDO::PARAM_INT);
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
        $calificacionMinima = $_POST['calificacion_minima']; // Nuevo campo

        $query = "UPDATE catalogo_periodos SET codigo_modalidad = :codigoModalidad, cantidad_periodos = :cantidadPeriodos, calificacion_minima = :calificacionMinima WHERE id = :idPeriodo";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idPeriodo', $idPeriodo, PDO::PARAM_INT);
        $stmt->bindParam(':codigoModalidad', $codigoModalidad, PDO::PARAM_INT);
        $stmt->bindParam(':cantidadPeriodos', $cantidadPeriodos, PDO::PARAM_INT);
        $stmt->bindParam(':calificacionMinima', $calificacionMinima, PDO::PARAM_INT);  // Nuevo campo
        $stmt->execute();

        echo json_encode(["success" => "Período actualizado correctamente"]);
    }

    // 📌 Listar períodos
    elseif ($action === "listar") {
        $query = "SELECT cp.id, cp.codigo_modalidad, cp.cantidad_periodos, TRIM(bc.nombre) AS nombre_modalidad, cp.calificacion_minima  -- Nuevo campo
                FROM catalogo_periodos cp
                INNER JOIN bachillerato_ciclo bc ON cp.codigo_modalidad = bc.codigo
                ORDER BY cp.id";

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
        $query = "DELETE FROM catalogo_periodos WHERE id = :idPeriodo";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idPeriodo', $idPeriodo, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["success" => "Período eliminado correctamente"]);
    }

} catch (Exception $e) {
    echo json_encode(["error" => "Error en la operación: " . $e->getMessage()]);
}