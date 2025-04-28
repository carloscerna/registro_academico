<?php
header("Content-Type: application/json; charset=UTF-8");
sleep(0);

include($_SERVER['DOCUMENT_ROOT']."/registro_academico/includes/mainFunctions_conexion.php");
print $_POST["accion_buscar"];
if($errorDbConexion == false){
    if(isset($_POST['accion_buscar']) && $_POST['accion_buscar'] == "BuscarLista"){

        $codigo_all = $_POST["lstmodalidad"] . substr($_POST["lstgradoseccion"],0,4) . $_POST["lstannlectivo"];
print $codigo_all;
       print  $query = "SELECT a.id_alumno, a.codigo_nie, 
                  btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno
                  FROM alumno a 
                  INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                  WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_turno || am.codigo_ann_lectivo) = :codigo_all
                  ORDER BY apellido_alumno ASC";

        $stmt = $dblink->prepare($query);
        $stmt->bindParam(":codigo_all", $codigo_all);
        $stmt->execute();

        $datos = [];

        while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            $datos[] = $row;
        }

        echo json_encode($datos);
        exit;
    }
}

// Si hay error
echo json_encode([]);