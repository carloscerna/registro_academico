<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
$pdo=$dblink;
try {
    $stmt = $pdo->query("
        SELECT
            u.id_usuario,
            u.nombre,
            cp.descripcion AS descripcion,
            cp.codigo_perfil
        FROM public.usuarios u
        INNER JOIN public.personal p ON u.id_usuario = up.id_usuario_bigint
        INNER JOIN public.catalogo_perfil cp ON up.codigo_perfil = cp.codigo_perfil
        ORDER BY u.nombre
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener los usuarios: ' . $e->getMessage()]);
}