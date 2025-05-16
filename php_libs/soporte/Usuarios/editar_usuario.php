<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$id_usuario = $_POST['id_usuario'] ?? null;
$nombre_usuario = $_POST['nombre_usuario'] ?? '';
$password = $_POST['password'] ?? '';
$id_perfil = $_POST['id_perfil'] ?? '';
$pdo=$dblink;
if ($id_usuario && !empty($nombre_usuario) && !empty($id_perfil)) {
    try {
        $pdo->beginTransaction();

        // Actualizar la tabla usuarios
        $stmt_usuario = $pdo->prepare("UPDATE public.usuarios SET nombre = :nombre, password = CASE WHEN :password != '' THEN :password_hash ELSE password END WHERE id_usuario_bigint = :id");
        $stmt_usuario->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $stmt_usuario->bindParam(':nombre', $nombre_usuario);
        $stmt_usuario->bindParam(':password', $password);
        $stmt_usuario->bindValue(':password_hash', !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null);
        $stmt_usuario->execute();

        // Actualizar la tabla usuarios_personal
        $stmt_personal = $pdo->prepare("UPDATE public.usuarios_personal SET codigo_perfil = :codigo_perfil WHERE id_usuario_bigint = :id_usuario");
        $stmt_personal->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_personal->bindParam(':codigo_perfil', $id_perfil);
        $stmt_personal->execute();

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos requeridos.']);
}
?>