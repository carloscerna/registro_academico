<?php
//session_name('demoUI');
//session_start();
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header('Content-Type: application/json');
// Insertar y actualizar tabla de usuarios
sleep(0);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicaci�n";
$contenidoOK = "";
// ruta de los archivos con su carpeta
	$path_root = trim($_SERVER['DOCUMENT_ROOT']);
	include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
	$pdo=$dblink;

$response = [];

try {
    $accion = $_POST['action'] ?? '';

    if ($accion === 'buscarNotas') {
        // Construcción de códigos desde el formulario
        $codigo_all = $_POST["codigo_modalidad"] . substr($_POST["codigo_grado_seccion"], 0, 4) . $_POST["codigo_ann_lectivo"];
        $codigo_bachillerato = substr($codigo_all, 0, 2);
		$codigo_grado = substr($codigo_all, 2, 2);
        $codigo_seccion = substr($codigo_all, 4, 2);
        $codigo_annlectivo = substr($codigo_all, 6, 2);
        $codigo_asignatura = trim($_POST["codigo_asignatura"]);
        $periodo = $_POST["periodo"];

        $sql = "
            SELECT 
                n.id_notas,
                a.codigo_nie, 
                TRIM(CONCAT_WS(' ', a.apellido_paterno, a.apellido_materno, ', ', a.nombre_completo)) AS nombre_estudiante, 
                asig.nombre AS nombre_asignatura, asig.codigo_cc,
                n.nota_a1_$periodo, 
                n.nota_a2_$periodo, 
                n.nota_a3_$periodo, 
                n.nota_r_$periodo, 
                n.nota_p_p_$periodo
            FROM alumno a  
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
            INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
            INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
            WHERE am.codigo_bach_o_ciclo = :codigo_bachillerato  
            AND am.codigo_grado = :codigo_grado 
            AND am.codigo_seccion = :codigo_seccion 
            AND am.codigo_ann_lectivo = :codigo_annlectivo
            AND n.codigo_asignatura = :codigo_asignatura 
            ORDER BY nombre_estudiante ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':codigo_bachillerato' => $codigo_bachillerato,
            ':codigo_grado' => $codigo_grado,
            ':codigo_seccion' => $codigo_seccion,
            ':codigo_annlectivo' => $codigo_annlectivo,
            ':codigo_asignatura' => $codigo_asignatura
        ]);

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id_notas' => $row['id_notas'],
                'codigo_nie' => trim($row['codigo_nie']),
                'nombre_completo' => $row['nombre_estudiante'],
                'nota_a1' => $row["nota_a1_$periodo"],
                'nota_a2' => $row["nota_a2_$periodo"],
                'nota_a3' => $row["nota_a3_$periodo"],
                'nota_r'  => $row["nota_r_$periodo"],
                'nota_pp' => $row["nota_p_p_$periodo"],
				'codigo_cc' => trim($row['codigo_cc'])
            ];
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    if ($accion === 'guardarNotas') {
        $notas = $_POST['notas'] ?? [];
        $periodo = $_POST['periodo'];

        $pdo->beginTransaction();

        $sql = "
            UPDATE nota
            SET 
                nota_a1_$periodo = :a1,
                nota_a2_$periodo = :a2,
                nota_a3_$periodo = :a3,
                nota_r_$periodo  = :r,
                nota_p_p_$periodo = :pp
            WHERE id_notas = :id
        ";
        $stmt = $pdo->prepare($sql);

        foreach ($notas as $nota) {
            $stmt->execute([
                ':a1' => $nota['a1'],
                ':a2' => $nota['a2'],
                ':a3' => $nota['a3'],
                ':r'  => $nota['r'],
                ':pp' => $nota['pp'],
                ':id' => $nota['id_notas']
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'mensaje' => 'Notas actualizadas con éxito.']);
        exit;
    }

    echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida.']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
