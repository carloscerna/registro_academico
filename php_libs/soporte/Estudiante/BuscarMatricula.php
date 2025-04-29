<?php
header("Content-Type: application/json; charset=utf-8");
clearstatcache();
sleep(0);

$respuesta = [
  "respuesta" => false,
  "mensaje" => "No se puede ejecutar la aplicación",
  "contenido" => []
];

$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

if ($errorDbConexion) {
  $respuesta['mensaje'] = "Error de conexión a la base de datos.";
  echo json_encode($respuesta);
  exit;
}

if (isset($_POST) && !empty($_POST)) {
  if (!empty($_POST['accion_buscar'])) {
    $_POST['accion'] = $_POST['accion_buscar'];
  }

  switch ($_POST['accion']) {
    case 'BuscarLista':
      try {
        $codigo_all = $_POST["lstmodalidad"] . substr($_POST["lstgradoseccion"], 0, 6) . $_POST["lstannlectivo"];

        $query = "SELECT 
            a.id_alumno, 
            a.codigo_nie, 
            btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) AS apellido_alumno
          FROM alumno a
          INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
          INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
          INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
          INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
          INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
          WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_turno || am.codigo_ann_lectivo) = :codigo_all
          ORDER BY apellido_alumno ASC";

        $stmt = $dblink->prepare($query);
        $stmt->bindParam(":codigo_all", $codigo_all, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
          $respuesta['respuesta'] = true;
          $respuesta['mensaje'] = "Datos encontrados.";
          $respuesta['contenido'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
          $respuesta['mensaje'] = "No hay registros de alumnos.";
        }
      } catch (Exception $e) {
        $respuesta['mensaje'] = "Error en la consulta: " . $e->getMessage();
      }
    break;
  }
}

echo json_encode($respuesta);
