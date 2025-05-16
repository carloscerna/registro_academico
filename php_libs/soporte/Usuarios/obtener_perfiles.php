<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
$pdo=$dblink;
try {
    $stmt = $pdo->query("SELECT codigo_perfil, descripcion FROM public.catalogo_perfil ORDER BY descripcion");
    $perfiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($perfiles);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener los perfiles: ' . $e->getMessage()]);
}
?>