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
//
$codigo_genero = '01';
$codigo_estatus = '01';

$matriculados = 0;
$ya_existian = 0;
$errores = [];

foreach ($alumnos as $a) {
    $nie = trim($a['codigo_nie']);
    $apellido_paterno = trim($a['apellido_paterno']);
    $apellido_materno = trim($a['apellido_materno']);
    $nombre_completo = trim($a['nombre_completo']);

    try {
        // [MODIFICACIÓN 1] Iniciamos la transacción SIEMPRE al entrar al intento, 
        // para cubrir tanto alumnos nuevos como antiguos.
        $dblink->beginTransaction(); 

        $stmt = $dblink->prepare("SELECT id_alumno FROM alumno WHERE codigo_nie = ?");
        $stmt->execute([$nie]);
        
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($fila) {
            $idAlumno = $fila['id_alumno'];
            //echo "ID Alumno: " . $idAlumno;
        } else {
            // [MODIFICACIÓN] Quitamos el beginTransaction de aquí porque ya está arriba

            $query_insert_alumno = "INSERT INTO alumno (codigo_nie, apellido_paterno, apellido_materno, nombre_completo, codigo_genero, codigo_estatus)
                                    VALUES (:codigo_nie, :apellido_paterno, :apellido_materno, :nombre_completo, :codigo_genero, :codigo_estatus)
                                    RETURNING id_alumno";
            $stmt_insert = $dblink->prepare($query_insert_alumno);
            $stmt_insert->bindParam(":codigo_nie", $nie);
            $stmt_insert->bindParam(":apellido_paterno", $apellido_paterno);
            $stmt_insert->bindParam(":apellido_materno", $apellido_materno);
            $stmt_insert->bindParam(":nombre_completo", $nombre_completo);
            $stmt_insert->bindParam(":codigo_genero", $codigo_genero);
            $stmt_insert->bindParam(":codigo_estatus", $codigo_estatus);

            if (!$stmt_insert->execute()) {
                throw new Exception("Error al ejecutar la inserción del alumno.");
            }

            $idAlumno = $stmt_insert->fetchColumn();
            if (!$idAlumno) {
                throw new Exception("Error al obtener el ID del alumno tras la inserción.");
            }
        }

        $stmt_check = $dblink->prepare("SELECT 1 FROM alumno_matricula WHERE codigo_alumno = ? AND codigo_bach_o_ciclo = ? AND codigo_grado = ? AND codigo_seccion = ? AND codigo_ann_lectivo = ?");
        $stmt_check->execute([$idAlumno, $modalidad, $codigo_grado, $codigo_seccion, $annLectivo]);

        if ($stmt_check->rowCount() > 0) {
            // [MODIFICACIÓN 2] Si ya existe, debemos CERRAR la transacción abierta antes de saltar al siguiente.
            // Usamos rollBack porque no hicimos cambios.
            if ($dblink->inTransaction()) {
                $dblink->rollBack();
            }
            $ya_existian++;
            continue;
        }

        //print "Id alumno: " . $idAlumno;
        $query_insert_matricula = "INSERT INTO alumno_matricula (codigo_alumno) VALUES (:codigo_alumno) RETURNING id_alumno_matricula";
        $stmt_matricula = $dblink->prepare($query_insert_matricula);
        $stmt_matricula->bindParam(":codigo_alumno", $idAlumno);

        if (!$stmt_matricula->execute()) {
            throw new Exception("Error al ejecutar la inserción de la matrícula.");
        }

        $id_matricula = $stmt_matricula->fetchColumn();
        if (!$id_matricula) {
            throw new Exception("Error al obtener el ID de la matrícula.");
        }


        $stmt_update_m = $dblink->prepare("UPDATE alumno_matricula SET codigo_bach_o_ciclo = ?, codigo_grado = ?, codigo_seccion = ?, codigo_turno = ?, codigo_ann_lectivo = ? WHERE id_alumno_matricula = ?");
        $stmt_update_m->execute([$modalidad, $codigo_grado, $codigo_seccion, $codigo_turno, $annLectivo, $id_matricula]);

        $stmt_asign = $dblink->prepare("SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = ? AND codigo_ann_lectivo = ? AND codigo_grado = ?");
        $stmt_asign->execute([$modalidad, $annLectivo, $codigo_grado]);

        while ($row = $stmt_asign->fetch(PDO::FETCH_ASSOC)) {
            $stmt_nota = $dblink->prepare("INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES (?, ?, ?)");
            $stmt_nota->execute([$row['codigo_asignatura'], $idAlumno, $id_matricula]);
        }

        $stmt_verif = $dblink->prepare("SELECT COUNT(*) FROM alumno_encargado WHERE codigo_alumno = ?");
        $stmt_verif->execute([$idAlumno]);
        if ($stmt_verif->fetchColumn() == 0) {
            for ($i = 1; $i <= 3; $i++) {
                $encargado = ($i == 1) ? 1 : 0;
                $stmt_encargado = $dblink->prepare("INSERT INTO alumno_encargado (codigo_alumno, encargado) VALUES (?, ?)");
                $stmt_encargado->execute([$idAlumno, $encargado]);
            }
        }

        $dblink->commit();
        $matriculados++;

    } catch (Exception $e) {
        // [MODIFICACIÓN 3] Validamos si hay transacción antes de hacer rollback
        if ($dblink->inTransaction()) {
            $dblink->rollBack();
        }
        $errores[] = "Error con NIE $nie: " . $e->getMessage();
        continue;
    }
}

$respuesta['respuesta'] = true;
$respuesta['mensaje'] = 'Proceso completado';
$respuesta['contenido'] = "$matriculados matrícula(s) realizadas. $ya_existian ya estaban registradas.";

if (!empty($errores)) {
    $respuesta['contenido'] .= " Errores: " . implode(" | ", $errores);
}

echo json_encode($respuesta);
?>