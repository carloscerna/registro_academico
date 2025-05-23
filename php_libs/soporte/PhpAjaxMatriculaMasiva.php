<?php
clearstatcache();
header("Content-Type: application/json; charset=utf-8");
sleep(0);

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

if ($errorDbConexion == false) {
    if (isset($_POST) && !empty($_POST)) {
        if (!empty($_POST['accion_buscar'])) {
            $_POST['accion'] = $_POST['accion_buscar'];
        }

        switch ($_POST['accion']) {
            case 'CrearMatricula':
                $codigo_alumno = $_POST["codigo_alumno_"];
                $chk_matricula = $_POST["chk_matricula_"];
                $fila = $_POST["fila"];
                $codigo_annlectivo = trim($_POST["lstannlectivo"]);
                $codigo_modalidad = trim($_POST["lstmodalidad"]);
                $codigo_gradoseccion = trim($_POST["lstgradoseccion"]);
                $codigo_grado = substr($codigo_gradoseccion, 0, 2);
                $codigo_seccion = substr($codigo_gradoseccion, 2, 2);
                $codigo_turno = substr($codigo_gradoseccion, 4, 2);
                $pn = true;
                $certificado = true;
                $codigo_todos = $codigo_modalidad . $codigo_grado . $codigo_annlectivo;
                $x_matriculas = 0;
                $no_matriculas = 0;
                $no_seleccionado = 0;

                for ($i = 0; $i < $fila; $i++) {
                    $codigo_alumnos = $codigo_alumno[$i];
                    $chk_matricular = $chk_matricula[$i];

                    if ($chk_matricular == "true") {
                        $query = "SELECT 1 FROM alumno_matricula 
                                  WHERE btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo) = :codigo_todos 
                                  AND codigo_alumno = :codigo_alumno";
                        $stmt = $dblink->prepare($query);
                        $stmt->bindParam(":codigo_todos", $codigo_todos);
                        $stmt->bindParam(":codigo_alumno", $codigo_alumnos);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            $no_matriculas++;
                            continue;
                        }

                        try {
                            $query_insert = "INSERT INTO alumno_matricula (codigo_alumno) VALUES (:codigo_alumno) RETURNING id_alumno_matricula";
                            $stmt = $dblink->prepare($query_insert);
                            $stmt->bindParam(":codigo_alumno", $codigo_alumnos);
                            $stmt->execute();
                            $fila_matricula = $stmt->fetchColumn();

                            $query_update = "UPDATE alumno_matricula SET 
                                codigo_bach_o_ciclo = :modalidad,
                                codigo_grado = :grado,
                                codigo_seccion = :seccion,
                                codigo_turno = :turno,
                                codigo_ann_lectivo = :ann,
                                certificado = :certificado,
                                pn = :pn
                                WHERE codigo_alumno = :codigo_alumno AND id_alumno_matricula = :id_matricula";
                            $stmt = $dblink->prepare($query_update);
                            $stmt->execute([
                                ':modalidad' => $codigo_modalidad,
                                ':grado' => $codigo_grado,
                                ':seccion' => $codigo_seccion,
                                ':turno' => $codigo_turno,
                                ':ann' => $codigo_annlectivo,
                                ':certificado' => $certificado,
                                ':pn' => $pn,
                                ':codigo_alumno' => $codigo_alumnos,
                                ':id_matricula' => $fila_matricula
                            ]);

                            $stmt = $dblink->prepare("SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo 
                                WHERE codigo_bach_o_ciclo = :modalidad AND codigo_ann_lectivo = :ann AND codigo_grado = :grado");
                            $stmt->execute([
                                ':modalidad' => $codigo_modalidad,
                                ':ann' => $codigo_annlectivo,
                                ':grado' => $codigo_grado
                            ]);

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $codigo_asignatura = $row["codigo_asignatura"];

                                $stmtInsert = $dblink->prepare("INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula)
                                    VALUES (:asignatura, :alumno, :matricula)");
                                $stmtInsert->execute([
                                    ':asignatura' => $codigo_asignatura,
                                    ':alumno' => $codigo_alumnos,
                                    ':matricula' => $fila_matricula
                                ]);
                            }

                            $x_matriculas++;
                        } catch (PDOException $e) {
                            $mensajeError = "Error al guardar matrícula: " . $e->getMessage();
                        }
                    } else {
                        $no_seleccionado++;
                    }
                }

                $respuestaOK = $x_matriculas > 0;
                $contenidoOK = "$x_matriculas matrícula(s) exitosa(s). ";
                if ($no_matriculas > 0) {
                    $contenidoOK .= "$no_matriculas ya estaban matriculados. ";
                }
                if ($no_seleccionado > 0) {
                    $contenidoOK .= "$no_seleccionado alumno(s) no seleccionados.";
                }
            break;

            default:
                $mensajeError = 'Esta acción no se encuentra disponible';
            break;
        }
    } else {
        $mensajeError = 'No se puede ejecutar la aplicación';
    }
} else {
    $mensajeError = 'No se puede establecer conexión con la base de datos';
}

echo json_encode([
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError,
    "contenido" => $contenidoOK
]);