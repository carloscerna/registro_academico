<?php
header('Content-Type: application/json');

// Conexión a PostgreSQL
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$action = $_POST['action'] ?? '';
$db = $dblink;
$pdo = $dblink;
try {
    switch ($action) {
        case 'listar':
            $stmt = $db->query("SELECT id, modalidad, cantidad_periodos, a1, a2, po FROM catalogo_periodos ORDER BY id");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'obtener':
            $id = $_POST['id'] ?? 0;
            $stmt = $db->prepare("SELECT * FROM catalogo_periodos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $registro = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($registro) {
                echo json_encode(['success' => true, 'data' => $registro]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
            }
            break;

        case 'guardar':
           // Recibimos los datos del formulario
                $id = isset($_POST['id']) ? $_POST['id'] : null;
                $modalidad = $_POST['modalidad'];
                $cantidad_periodos = $_POST['cantidad_periodos'];
                $a1 = $_POST['a1'];
                $a2 = $_POST['a2'];
                $po = $_POST['po'];

                // Validación de los porcentajes (lo hacemos también en el backend por seguridad)
                if ($a1 < 0 || $a1 > 35 || $a2 < 0 || $a2 > 35 || $po < 0 || $po > 30 || ($a1 + $a2 + $po) > 100) {
                    echo json_encode(['status' => 'error', 'message' => 'Los porcentajes no son válidos. A1 ≤ 35%, A2 ≤ 35%, PO ≤ 30% y la suma no debe exceder 100%.']);
                    exit();
                }

                // Si el ID está presente, actualizamos el registro
                if ($id) {
                    $query = "UPDATE catalogo_periodos SET modalidad = :modalidad, cantidad_periodos = :cantidad_periodos, a1 = :a1, a2 = :a2, po = :po WHERE id = :id";
                    $stmt = $pdo->prepare($query);
                    $stmt->bindParam(':id', $id);
                } else {
                    // Si no hay ID, insertamos un nuevo registro
                    $query = "INSERT INTO catalogo_periodos (modalidad, cantidad_periodos, a1, a2, po) VALUES (:modalidad, :cantidad_periodos, :a1, :a2, :po)";
                    $stmt = $pdo->prepare($query);
                }

                // Ejecutamos la consulta
                $stmt->bindParam(':modalidad', $modalidad);
                $stmt->bindParam(':cantidad_periodos', $cantidad_periodos);
                $stmt->bindParam(':a1', $a1);
                $stmt->bindParam(':a2', $a2);
                $stmt->bindParam(':po', $po);

                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Periodo guardado correctamente.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Hubo un error al guardar el periodo.']);
                }

        case 'eliminar':
            $id = $_POST['id'] ?? 0;
            $stmt = $db->prepare("DELETE FROM catalogo_periodos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
}
