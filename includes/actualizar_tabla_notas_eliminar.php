<?php
// iniciar sesssion.
//session_name('demoUI');
//session_start();
// omitir errores.
ini_set("display_error", true);

//
//
//
// variables/conexion.
    $host = 'localhost';
    $port = 5432;
    $database = 'registro_academico_10391';
    $username = 'postgres';
    $password = 'Orellana';
//Construimos el DSN//
try{
    $dsn = "pgsql:host=$host;port=$port;dbname=$database";
}catch(PDOException $e) {
         echo  $e->getMessage();
         $errorDbConexion = true;   
     }
// Creamos el objeto
    $dblink = new PDO($dsn, $username, $password);
// Validar la conexión.
    if(!$dblink){
     // Variable que indica el status de la conexión a la base de datos
        $errorDbConexion = true;   
    };
// 024P0119 , 025P0119 , 025P0219 , 026P0119 . 026P0219
$todos='0522'; // MODALIDAD - GRADO - SECCION - ANN LECTIVO
$codigo_asignatura = array('73','74');
$num = 0;
// datos de la tabla de facturas_compras.
        $query = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
		    a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, 
		    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
		    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, am.id_alumno_matricula,
		    sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno
		    FROM alumno a
			INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
			INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
			INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
			INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
			WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_ann_lectivo) = '".$todos.
			"' ORDER BY apellido_alumno, am.codigo_grado, am.codigo_seccion ASC";
            
            //WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$todos.
      $result_ = $dblink -> query($query);
    // Extraer valore de la consulta.
				 while($row_ = $result_ -> fetch(PDO::FETCH_BOTH))
				 {
                    $num++;
                    $codigo_alumno = $row_['id_alumno'];
                    $codigo_alumno_matricula = $row_['id_alumno_matricula'];
                    $nombres = trim($row_['apellidos_alumno']);

                    for ($i=0; $i < count($codigo_asignatura); $i++) { 
                        $query_eliminar = "DELETE FROM nota WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_alumno_matricula' and codigo_asignatura = '$codigo_asignatura[$i]'";
                        $result_consulta_eliminar_notas = $dblink -> query($query_eliminar);
                            if($result_consulta_eliminar_notas)
                            {
                                print $num . "-" .  $codigo_alumno . " " . $codigo_alumno_matricula . " " .$nombres . " " . $codigo_asignatura[$i] . "<br>";
                            }
                        
                    }
                 }
                 
                 
?>