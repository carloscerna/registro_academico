<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
$pdo=$dblink;
try {
    if (!isset($_GET["modalidad"])) {
        echo json_encode(["error" => "Modalidad no proporcionada"]);
        exit;
    }

    $modalidad = $_GET["modalidad"];

    $query = "SELECT cantidad_periodos FROM catalogo_periodos WHERE codigo_modalidad = :modalidad LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':modalidad', $modalidad, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result ?: ["cantidad_periodos" => 0]); // Si no hay datos, devuelve 0
} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener períodos: " . $e->getMessage()]);
}
?>