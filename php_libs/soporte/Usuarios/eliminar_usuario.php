<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$id_usuario = $_POST['id'] ?? null;
$pdo=$dblink;
if ($id_usuario) {
    try {
        $pdo->beginTransaction();

        // Eliminar de la tabla usuarios_personal (primero por la llave foránea)
        $stmt_personal = $pdo->prepare("DELETE FROM public.usuarios_personal WHERE id_usuario_bigint = :id");
        $stmt_personal->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt_personal->execute();

        // Eliminar de la tabla usuarios
        $stmt_usuario = $pdo->prepare("DELETE FROM public.usuarios WHERE id_usuario_bigint = :id");
        $stmt_usuario->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt_usuario->execute();

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado.']);
}
?>