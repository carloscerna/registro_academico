<?php
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// pasar la conexion
$pdo = $dblink;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Devuelve áreas para el filtro
    $stmt = $pdo->query("SELECT codigo, descripcion FROM catalogo_area_asignatura ORDER BY codigo");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Devuelve datos jerárquicos filtrados
    $codigo_area = $_POST['codigo_area'] ?? '';

    $sql = "
        SELECT 
            aa.codigo || ' - ' || aa.descripcion AS area,
            ad.codigo || ' - ' || ad.descripcion AS dimension,
            sd.codigo || ' - ' || sd.descripcion AS subdimension
        FROM catalogo_area_asignatura aa
        LEFT JOIN catalogo_area_dimension ad ON ad.codigo_area = aa.codigo
        LEFT JOIN catalogo_area_subdimension sd ON sd.codigo_area = ad.codigo_area AND sd.codigo_dimension = ad.codigo
    ";

    if (!empty($codigo_area)) {
        $sql .= " WHERE aa.codigo = :codigo_area";
    }

    $sql .= " ORDER BY aa.codigo, ad.codigo, sd.codigo";

    $stmt = $pdo->prepare($sql);
    if (!empty($codigo_area)) {
        $stmt->bindParam(':codigo_area', $codigo_area);
    }
    $stmt->execute();

    echo json_encode(['data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}
