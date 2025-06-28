<?php
session_name('demoUI');
session_start();

header('Content-Type: application/json;charset=utf-8');

// Asegurarse de que el usuario está logueado y tiene permisos de administrador (ej. '01' o '99')
$allowed_profiles = ['01', '99']; // Perfiles con permiso para administrar permisos

if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] !== true || !in_array($_SESSION['codigo_perfil'], $allowed_profiles)) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administración de menú.']);
    exit();
}

// Incluir la conexión a la base de datos
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_.php"); // Aquí se establece $dblink y $errorDbConexion

// Verificar si hubo un error de conexión
if ($errorDbConexion || !$dblink) {
    $errorMessage = $_SESSION['db_connection_error_message'] ?? 'No se pudo conectar a la base de datos.';
    echo json_encode(['success' => false, 'message' => $errorMessage]);
    unset($_SESSION['db_connection_error_message']);
    exit();
}

// Inicializar respuesta
$response = ['success' => false, 'message' => 'Acción no válida.'];

// Determinar la acción a realizar
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'getAllProfiles':
            // Obtener todos los perfiles disponibles para mostrar en la interfaz
            $query = "SELECT codigo_perfil, nombre_perfil FROM perfiles ORDER BY nombre_perfil ASC";
            $stmt = $dblink->prepare($query);
            $stmt->execute();
            $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $profiles];
            break;

        case 'getPermissionsByMenuItem':
            // Obtener los perfiles que tienen permiso para un menu_item específico
            $menu_item_id = $_GET['menu_item_id'] ?? null;
            if ($menu_item_id === null) {
                $response = ['success' => false, 'message' => 'ID de elemento de menú no proporcionado.'];
                break;
            }
            $query = "SELECT profile_id FROM profile_menu_permissions WHERE menu_item_id = :menu_item_id";
            $stmt = $dblink->prepare($query);
            $stmt->bindParam(':menu_item_id', $menu_item_id, PDO::PARAM_INT);
            $stmt->execute();
            $assigned_profiles = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Obtener solo los IDs de perfil
            $response = ['success' => true, 'data' => $assigned_profiles];
            break;

        case 'saveMenuItemPermissions':
            // Guardar los permisos para un menu_item: añadir o eliminar en la tabla de permisos
            $menu_item_id = $_POST['menu_item_id'] ?? null;
            // Los IDs de perfil seleccionados vendrán como un array (o una cadena vacía si no hay ninguno)
            $selected_profiles = isset($_POST['selected_profiles']) ? (array)$_POST['selected_profiles'] : [];

            if ($menu_item_id === null) {
                $response = ['success' => false, 'message' => 'ID de elemento de menú no proporcionado.'];
                break;
            }

            // Iniciar una transacción para asegurar la atomicidad
            $dblink->beginTransaction();

            // 1. Eliminar todos los permisos existentes para este menu_item_id
            $query_delete = "DELETE FROM profile_menu_permissions WHERE menu_item_id = :menu_item_id";
            $stmt_delete = $dblink->prepare($query_delete);
            $stmt_delete->bindParam(':menu_item_id', $menu_item_id, PDO::PARAM_INT);
            $stmt_delete->execute();

            // 2. Insertar los nuevos permisos seleccionados
            if (!empty($selected_profiles)) {
                $placeholders = [];
                $values = [];
                foreach ($selected_profiles as $profile_id) {
                    $placeholders[] = '(?, ?)';
                    $values[] = $profile_id;
                    $values[] = $menu_item_id;
                }
                $query_insert = "INSERT INTO profile_menu_permissions (profile_id, menu_item_id) VALUES " . implode(', ', $placeholders);
                $stmt_insert = $dblink->prepare($query_insert);
                $stmt_insert->execute($values);
            }

            $dblink->commit(); // Confirmar la transacción
            $response = ['success' => true, 'message' => 'Permisos actualizados exitosamente.'];
            break;

        default:
            $response = ['success' => false, 'message' => 'Acción no reconocida para permisos.'];
            break;
    }
} catch (PDOException $e) {
    if ($dblink->inTransaction()) {
        $dblink->rollBack(); // Revertir la transacción si algo falla
    }
    error_log("Error en phpAjaxMenuPermissions.inc.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error en la base de datos al gestionar permisos: ' . $e->getMessage()]; // Evita mostrar $e->getMessage() en producción
}

echo json_encode($response);
exit();
?>