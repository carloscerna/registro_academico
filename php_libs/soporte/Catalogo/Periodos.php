<?php
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$accion = $_POST['accion'] ?? '';
$db=$dblink;
if ($accion == 'listar') {
    $sql = "SELECT * FROM catalogo_periodos ORDER BY id_periodo ASC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($accion == 'obtener') {
    $id = $_POST['id_periodo'];
    $sql = "SELECT * FROM catalogo_periodos WHERE id_periodo = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
    exit;
}

if ($accion == 'guardar') {
    $id_periodo = $_POST['id_periodo'] ?? '';
    $modalidad = trim($_POST['modalidad']);
    $cantidad = intval($_POST['cantidad']);
    $a1 = floatval($_POST['ponderacion_a1']);
    $a2 = floatval($_POST['ponderacion_a2']);
    $po = floatval($_POST['ponderacion_po']);

    // Validación: suma de ponderaciones debe ser 100
    $suma_ponderaciones = $a1 + $a2 + $po;
    if ($suma_ponderaciones !== 100.0) {
        echo json_encode(['error' => 'La suma de las ponderaciones debe ser 100.']);
        exit;
    }

    // Validación: no permitir duplicados de modalidad
    if (empty($id_periodo)) {
        $sql_check = "SELECT COUNT(*) FROM catalogo_periodos WHERE modalidad = :modalidad";
    } else {
        $sql_check = "SELECT COUNT(*) FROM catalogo_periodos WHERE modalidad = :modalidad AND id_periodo != :id";
    }
    $stmt_check = $db->prepare($sql_check);
    $stmt_check->bindParam(':modalidad', $modalidad);
    if (!empty($id_periodo)) {
        $stmt_check->bindParam(':id', $id_periodo);
    }
    $stmt_check->execute();
    $existe = $stmt_check->fetchColumn();

    if ($existe > 0) {
        echo json_encode(['error' => 'Ya existe un registro para esa modalidad.']);
        exit;
    }

    // Insertar o actualizar
    if (empty($id_periodo)) {
        $sql = "INSERT INTO catalogo_periodos (modalidad, cantidad, ponderacion_a1, ponderacion_a2, ponderacion_po)
                VALUES (:modalidad, :cantidad, :a1, :a2, :po)";
        $stmt = $db->prepare($sql);
    } else {
        $sql = "UPDATE catalogo_periodos SET modalidad = :modalidad, cantidad = :cantidad,
                ponderacion_a1 = :a1, ponderacion_a2 = :a2, ponderacion_po = :po WHERE id_periodo = :id";
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id', $id_periodo);
    }

    $stmt->bindParam(':modalidad', $modalidad);
    $stmt->bindParam(':cantidad', $cantidad);
    $stmt->bindParam(':a1', $a1);
    $stmt->bindParam(':a2', $a2);
    $stmt->bindParam(':po', $po);

    try {
        $stmt->execute();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
    }
    exit;
}

if ($accion == 'eliminar') {
    $id = $_POST['id_periodo'];
    $sql = "DELETE FROM catalogo_periodos WHERE id_periodo = :id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    echo 'OK';
    exit;
}
