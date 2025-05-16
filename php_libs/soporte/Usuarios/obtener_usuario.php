<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
$pdo=$dblink;
$id_usuario = isset($_GET['id']) ? $_GET['id'] : null;

if ($id_usuario) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                u.id_usuario_bigint,
                u.nombre,
                up.codigo_perfil
            FROM public.usuarios u
            INNER JOIN public.usuarios_personal up ON u.id_usuario_bigint = up.id_usuario_bigint
            WHERE u.id_usuario_bigint = :id
        ");
        $stmt->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            echo json_encode($usuario);
        } else {
            echo json_encode(['error' => 'Usuario no encontrado.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener el usuario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID de usuario no proporcionado.']);
}
?>