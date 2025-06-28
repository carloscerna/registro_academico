<?php
session_name('demoUI');
session_start();

header('Content-Type: application/json;charset=utf-8');

// Asegurarse de que el usuario está logueado y tiene permisos de administrador (ej. '01' o '99')
// Ajusta esta lógica de permisos según tus necesidades reales.
// Por ejemplo, solo el '01' o '99' deberían acceder a este CRUD.
$allowed_profiles = ['01', '99']; // Perfiles con permiso para administrar menús

if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] !== true || !in_array($_SESSION['codigo_perfil'], $allowed_profiles)) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requieren permisos de administración.']);
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
        case 'getAllMenuItems':
            // Obtener todos los elementos de menú para la tabla (sin importar perfil, para administración)
            $query = "SELECT id, parent_id, text, icon, url, order_index, is_active FROM menu_items ORDER BY parent_id ASC NULLS FIRST, order_index ASC";
            $stmt = $dblink->prepare($query);
            $stmt->execute();
            $menuItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['success' => true, 'data' => $menuItems];
            break;

        case 'getMenuItemById':
            // Obtener un elemento de menú específico por ID (para editar)
            $id = $_GET['id'] ?? null;
            if ($id === null) {
                $response = ['success' => false, 'message' => 'ID de elemento de menú no proporcionado.'];
                break;
            }
            $query = "SELECT id, parent_id, text, icon, url, order_index, is_active FROM menu_items WHERE id = :id";
            $stmt = $dblink->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $menuItem = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($menuItem) {
                $response = ['success' => true, 'data' => $menuItem];
            } else {
                $response = ['success' => false, 'message' => 'Elemento de menú no encontrado.'];
            }
            break;

        case 'createMenuItem':
            // Crear un nuevo elemento de menú
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $text = trim($_POST['text'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $order_index = (int)($_POST['order_index'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false; // Checkbox value

            if (empty($text) || empty($icon) || empty($url)) {
                $response = ['success' => false, 'message' => 'Todos los campos obligatorios deben ser llenados.'];
                break;
            }

            $query = "INSERT INTO menu_items (parent_id, text, icon, url, order_index, is_active) VALUES (:parent_id, :text, :icon, :url, :order_index, :is_active)";
            $stmt = $dblink->prepare($query);
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->bindParam(':text', $text, PDO::PARAM_STR);
            $stmt->bindParam(':icon', $icon, PDO::PARAM_STR);
            $stmt->bindParam(':url', $url, PDO::PARAM_STR);
            $stmt->bindParam(':order_index', $order_index, PDO::PARAM_INT);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Elemento de menú creado exitosamente.'];
            } else {
                $response = ['success' => false, 'message' => 'Error al crear el elemento de menú.'];
            }
            break;

        case 'updateMenuItem':
            // Actualizar un elemento de menú existente
            $id = $_POST['id'] ?? null;
            $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
            $text = trim($_POST['text'] ?? '');
            $icon = trim($_POST['icon'] ?? '');
            $url = trim($_POST['url'] ?? '');
            $order_index = (int)($_POST['order_index'] ?? 0);
            $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : false;

            if ($id === null || empty($text) || empty($icon) || empty($url)) {
                $response = ['success' => false, 'message' => 'ID y todos los campos obligatorios deben ser llenados.'];
                break;
            }

            $query = "UPDATE menu_items SET parent_id = :parent_id, text = :text, icon = :icon, url = :url, order_index = :order_index, is_active = :is_active, updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            $stmt = $dblink->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->bindParam(':text', $text, PDO::PARAM_STR);
            $stmt->bindParam(':icon', $icon, PDO::PARAM_STR);
            $stmt->bindParam(':url', $url, PDO::PARAM_STR);
            $stmt->bindParam(':order_index', $order_index, PDO::PARAM_INT);
            $stmt->bindParam(':is_active', $is_active, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Elemento de menú actualizado exitosamente.'];
            } else {
                $response = ['success' => false, 'message' => 'Error al actualizar el elemento de menú.'];
            }
            break;

        case 'deleteMenuItem':
            // Eliminar un elemento de menú
            $id = $_POST['id'] ?? null;
            if ($id === null) {
                $response = ['success' => false, 'message' => 'ID de elemento de menú no proporcionado.'];
                break;
            }

            $query = "DELETE FROM menu_items WHERE id = :id";
            $stmt = $dblink->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Elemento de menú eliminado exitosamente.'];
            } else {
                $response = ['success' => false, 'message' => 'Error al eliminar el elemento de menú.'];
            }
            break;

        default:
            $response = ['success' => false, 'message' => 'Acción no reconocida.'];
            break;
    }
} catch (PDOException $e) {
    error_log("Error en phpAjaxMenuItems.inc.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]; // En producción, evita mostrar $e->getMessage()
}

echo json_encode($response);
exit();
?>