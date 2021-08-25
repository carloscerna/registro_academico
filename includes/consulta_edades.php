<?php
// iniciar sesssion.
//session_name('demoUI');
//session_start();
// omitir errores.
ini_set("display_error", true);
// variables/conexion.
    $host2 = 'localhost';
    $port2 = 5432;
    $database2 = 'registro_academico';
    $username2 = 'postgres';
    $password2 = 'Orellana';
//Construimos el DSN//
try{
    $dsn2 = "pgsql:host=$host2;port=$port2;dbname=$database2";
}catch(PDOException $e) {
         echo  $e->getMessage();
         $errorDbConexion2 = true;   
     }
// Creamos el objeto
    $dblink2 = new PDO($dsn2, $username2, $password2);
// Validar la conexión.
    if(!$dblink2){
     // Variable que indica el status de la conexión a la base de datos
        $errorDbConexion2 = true;   
    };

//
//
//
// variables/conexion.
    $host = 'localhost';
    $port = 5432;
    $database = 'registro_academico';
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
$codigo_asignatura = array('236','237','238','239','240');
$codigo_asignatura_anterior = array('09','10','11','12','13');
$todos='03010118';
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
			WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$todos.
			"' ORDER BY apellido_alumno ASC";
            
      $result_ = $dblink -> query($query);
    // Extraer valore de la consulta.
				 while($row_ = $result_ -> fetch(PDO::FETCH_BOTH))
				 {
                    $num++;
                    $codigo_alumno = $row_['id_alumno'];
                    $codigo_alumno_matricula = $row_['id_alumno_matricula'];
                    $nombres = trim($row_['apellidos_alumno']);
                    print $num . "-" . $codigo_alumno . " " . $codigo_alumno_matricula . " " .$nombres . " " ."<br>";
                    
                   // $query_insert = "INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES ('$codigo_asignatura',$codigo_alumno,$codigo_alumno_matricula)";
					//			$result_consulta_insert_notas = $dblink -> query($query_insert);
                    for($ii;$ii<count($codigo_asignatura_anterior);$ii++)
                    {
                        $query_update = "UPDATE nota SET codigo_asignatura = '$codigo_asignatura[$ii]' WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_alumno_matricula' and codigo_asignatura = '$codigo_asignatura_anterior[$ii]'";
                            $result_update = $dblink -> query($query_update);                        
                    }

                 }
                 
                 
?>