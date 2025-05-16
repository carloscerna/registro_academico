<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$nombre_usuario = $_POST['nombre_usuario'] ?? '';
$password = $_POST['password'] ?? '';
$id_perfil = $_POST['id_perfil'] ?? '';
$pdo=$dblink;
if (!empty($nombre_usuario) && !empty($id_perfil)) {
    try {
        $pdo->beginTransaction();

        // Insertar en la tabla usuarios
        $stmt_usuario = $pdo->prepare("INSERT INTO public.usuarios (nombre, password) VALUES (:nombre, :password)");
        $stmt_usuario->bindParam(':nombre', $nombre_usuario);
        $stmt_usuario->bindParam(':password', password_hash($password, PASSWORD_DEFAULT)); // Encriptar la contraseña
        $stmt_usuario->execute();
        $id_nuevo_usuario = $pdo->lastInsertId('usuarios_id_usuario_bigint_seq'); // Obtener el ID insertado

        // Insertar en la tabla usuarios_personal
        $stmt_personal = $pdo->prepare("INSERT INTO public.usuarios_personal (id_usuario_bigint, codigo_perfil) VALUES (:id_usuario, :codigo_perfil)");
        $stmt_personal->bindParam(':id_usuario', $id_nuevo_usuario, PDO::PARAM_INT);
        $stmt_personal->bindParam(':codigo_perfil', $id_perfil);
        $stmt_personal->execute();

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Usuario creado exitosamente.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al crear el usuario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos requeridos.']);
}
?>