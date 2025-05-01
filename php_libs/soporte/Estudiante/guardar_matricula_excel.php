
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
$errores = [];

foreach ($alumnos as $a) {
    $nie = trim($a['codigo_nie']);
    $apellido_paterno = trim($a['apellido_paterno']);
    $apellido_materno = trim($a['apellido_materno']);
    $nombre_completo = trim($a['nombre_completo']);

    try {
        $stmt = $dblink->prepare("SELECT id_alumno FROM alumno WHERE codigo_nie = ?");
        $stmt->execute([$nie]);

        if ($stmt->rowCount() === 0) {
            $query_insert_alumno = "INSERT INTO alumno (codigo_nie, apellido_paterno, apellido_materno, nombre_completo)
                        VALUES (:codigo_nie, :apellido_paterno, :apellido_materno, :nombre_completo)
                        RETURNING id_alumno";
                        $stmt_insert = $dblink->prepare($query_insert_alumno);
                        $stmt_insert->bindParam(":codigo_nie", $nie);
                        $stmt_insert->bindParam(":apellido_paterno", $apellido_paterno);
                        $stmt_insert->bindParam(":apellido_materno", $apellido_materno);
                        $stmt_insert->bindParam(":nombre_completo", $nombre_completo);
                        $stmt_insert->execute();
                        $idAlumno = $stmt_insert->fetchColumn();
// Obtenemos el id de user para edici�n
$query_ultimo = "SELECT id_alumno from alumno ORDER BY id_alumno DESC LIMIT 1 OFFSET 0";
// Ejecutamos el Query.
$consulta = $dblink -> query($query_ultimo);

while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
{
    // obtenemos el �ltimo c�digo asignado.
    $idAlumno = $listado['id_alumno'];
}


            /*
            $stmt_insert = $dblink->prepare("INSERT INTO alumno (codigo_nie, apellido_paterno, apellido_materno, nombre_completo)
                                             VALUES (?, ?, ?, ?) RETURNING id_alumno");
            if ($stmt_insert->execute([$nie, $apellido_paterno, $apellido_materno, $nombre_completo])) {
                $result = $stmt_insert->fetch(PDO::FETCH_ASSOC);
                if ($result && isset($result['id_alumno'])) {
                    $idAlumno = $result['id_alumno'];
                } else {
                    throw new Exception("No se devolvió el id_alumno.");
                }
            } else {
                $error = $stmt_insert->errorInfo();
                throw new Exception("Error al insertar alumno: " . $error[2]);
            }*/
        } else {
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fila && isset($fila['id_alumno'])) {
                $idAlumno = $fila['id_alumno'];
            } else {
                throw new Exception("No se pudo obtener el id_alumno del NIE: $nie");
            }
        }

        $stmt_check = $dblink->prepare("SELECT 1 FROM alumno_matricula WHERE codigo_alumno = ? AND codigo_bach_o_ciclo = ? AND codigo_grado = ? AND codigo_seccion = ? AND codigo_ann_lectivo = ?");
        $stmt_check->execute([$idAlumno, $modalidad, $codigo_grado, $codigo_seccion, $annLectivo]);

        if ($stmt_check->rowCount() > 0) {
            $ya_existian++;
            continue;
        }

        $dblink->beginTransaction();
            $query_insert_matricula = "INSERT INTO alumno_matricula (codigo_alumno)
                           VALUES (:codigo_alumno)
                           RETURNING id_alumno_matricula";
                $stmt_matricula = $dblink->prepare($query_insert_matricula);
                $stmt_matricula->bindParam(":codigo_alumno", $idAlumno);
                $stmt_matricula->execute();
                $id_matricula = $stmt_matricula->fetchColumn();

/*
        $stmt_insert_m = $dblink->prepare("INSERT INTO alumno_matricula (codigo_alumno) VALUES (?) RETURNING id_alumno_matricula");
        if ($stmt_insert_m->execute([$idAlumno])) {
            $mat_result = $stmt_insert_m->fetch(PDO::FETCH_ASSOC);
            if ($mat_result && isset($mat_result['id_alumno_matricula'])) {
                $id_matricula = $mat_result['id_alumno_matricula'];
            } else {
                throw new Exception("No se devolvió el id_alumno_matricula.");
            }
        } else {
            $error = $stmt_insert_m->errorInfo();
            throw new Exception("Error al insertar alumno_matricula: " . $error[2]);
        }
*/
        $stmt_update_m = $dblink->prepare("UPDATE alumno_matricula SET codigo_bach_o_ciclo = ?, codigo_grado = ?, codigo_seccion = ?, codigo_turno = ?, codigo_ann_lectivo = ?, certificado = true, pn = true WHERE id_alumno_matricula = ?");
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
                $encargado = ($i == 1) ? true : false;
                $stmt_encargado = $dblink->prepare("INSERT INTO alumno_encargado (codigo_alumno, encargado) VALUES (?, ?)");
                $stmt_encargado->execute([$idAlumno, $encargado]);
            }
        }

        $dblink->commit();
        $matriculados++;

    } catch (Exception $e) {
        $dblink->rollBack();
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
