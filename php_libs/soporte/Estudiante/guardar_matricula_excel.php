<?php
header('Content-Type: application/json; charset=utf-8');
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

$respuesta = ['respuesta' => false, 'mensaje' => 'No se pudo procesar', 'contenido' => ''];

if (!isset($_POST['accion']) || $_POST['accion'] !== 'GuardarDesdeExcel') {
    $respuesta['mensaje'] = 'Acción no válida.';
    echo json_encode($respuesta); exit;
}

$alumnos = $_POST['alumnos'] ?? [];
$annLectivo = $_POST['lstannlectivo'] ?? '';
$modalidad = $_POST['lstmodalidad'] ?? '';
$gradoSeccion = $_POST['lstgradoseccion'] ?? '';

if (empty($alumnos) || empty($annLectivo) || empty($modalidad) || empty($gradoSeccion)) {
    $respuesta['mensaje'] = 'Faltan datos requeridos.';
    echo json_encode($respuesta); exit;
}

$codigo_grado = substr($gradoSeccion, 0, 2);
$codigo_seccion = substr($gradoSeccion, 2, 2);
$codigo_turno = substr($gradoSeccion, 4, 2);


$matriculados = 0;
$ya_existian = 0;

foreach ($alumnos as $a) {
    $nie = trim($a['codigo_nie']);
    $apellido_paterno = trim($a['apellido_paterno']);
    $apellido_materno = trim($a['apellido_materno']);
    $nombre_completo = trim($a['nombre_completo']);

    // Buscar o insertar alumno
    $stmt = $dblink->prepare("SELECT id_alumno FROM alumno WHERE codigo_nie = ?");
    $stmt->execute([$nie]);

    if ($stmt->rowCount() === 0) {
       // $insertAlumno = $dblink->prepare("INSERT INTO alumno (codigo_nie, apellido_paterno, apellido_materno, nombre_completo) VALUES (?, ?, ?, ?) RETURNING id_alumno");
       // $insertAlumno->execute([$nie, $apellido_paterno, $apellido_materno, $nombre_completo]);
        //$idAlumno = $dblink->lastInsertId();
        $insertAlumno = $dblink->prepare("INSERT INTO alumno (codigo_nie, apellido_paterno, apellido_materno, nombre_completo) VALUES (?, ?, ?, ?) RETURNING id_alumno");
        $insertAlumno->execute([$nie, $apellido_paterno, $apellido_materno, $nombre_completo]);
        $idAlumno = $stmt->fetch(PDO::FETCH_ASSOC)['id_alumno'];
    } else {
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        $idAlumno = $fila['id_alumno'];
    }

    // Verificar matrícula exacta
    $stmt_check = $dblink->prepare("SELECT 1 FROM alumno_matricula WHERE codigo_alumno = ? AND codigo_bach_o_ciclo = ? AND codigo_grado = ? AND codigo_seccion = ? AND codigo_ann_lectivo = ?");
    $stmt_check->execute([$idAlumno, $modalidad, $codigo_grado, $codigo_seccion, $annLectivo]);

    if ($stmt_check->rowCount() > 0) {
        $ya_existian++;
        continue;
    }

    // Insertar matrícula
    $dblink->beginTransaction();
    try {
        try {
            // Preparar la consulta con RETURNING para obtener el ID generado
            $stmt = $dblink->prepare("INSERT INTO alumno_matricula (codigo_alumno) VALUES (?) RETURNING id_alumno_matricula");
            
            // Ejecutar la consulta y obtener el ID generado
            $stmt->execute([$idAlumno]);
            $id_matricula = $stmt->fetch(PDO::FETCH_ASSOC)['id_alumno_matricula'];
        
            echo "Matrícula creada con éxito. ID generado: " . $id_matricula;
        } catch (Exception $e) {
            echo "Error al insertar la matrícula: " . $e->getMessage();
        }
//        $stmt_insert_m = $dblink->prepare("INSERT INTO alumno_matricula (codigo_alumno) VALUES (?)");
  //      $stmt_insert_m->execute([$idAlumno]);
    //    $id_matricula = $dblink->lastInsertId();

        $stmt_update_m = $dblink->prepare("UPDATE alumno_matricula SET codigo_bach_o_ciclo = ?, codigo_grado = ?, codigo_seccion = ?, codigo_turno = ?, codigo_ann_lectivo = ?, certificado = true, pn = true WHERE id_alumno_matricula = ?");
        $stmt_update_m->execute([$modalidad, $codigo_grado, $codigo_seccion, $codigo_turno, $annLectivo, $id_matricula]);

        // Insertar asignaturas y notas
        $stmt_asign = $dblink->prepare("SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = ? AND codigo_ann_lectivo = ? AND codigo_grado = ?");
        $stmt_asign->execute([$modalidad, $annLectivo, $codigo_grado]);

        while ($row = $stmt_asign->fetch(PDO::FETCH_ASSOC)) {
            $stmt_nota = $dblink->prepare("INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES (?, ?, ?)");
            $stmt_nota->execute([$row['codigo_asignatura'], $idAlumno, $id_matricula]);
        }

        // Verificar e insertar en alumno_encargado si es necesario
        $stmt_verif = $dblink->prepare("SELECT COUNT(*) FROM alumno_encargado WHERE codigo_alumno = ?");
        $stmt_verif->execute([$idAlumno]);
        if ($stmt_verif->fetchColumn() == 0) {
            for ($i = 1; $i <= 3; $i++) {
                $encargado = ($i == 1) ? true : false;
                $stmt_encargado = $dblink->prepare("INSERT INTO alumno_encargado (codigo_alumno, encargado) VALUES (?, ?)");
                $stmt_encargado->execute([$idAlumno, $encargado]);
            }
        }

        $dblink->commit();
        $matriculados++;
    } catch (Exception $e) {
        $dblink->rollBack();
        continue;
    }
}

$respuesta['respuesta'] = true;
$respuesta['mensaje'] = 'Proceso completado';
$respuesta['contenido'] = "$matriculados matrícula(s) realizadas. $ya_existian ya estaban registradas.";
echo json_encode($respuesta);
?>
