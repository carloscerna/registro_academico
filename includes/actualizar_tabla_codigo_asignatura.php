<?php
// variables/conexion.
    $host = 'localhost';
    $port = 5432;
    $database = 'registro_academico_10420';
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
    
//$codigo_asignatura = array('09','10','11','12','13');
$codigo_asignatura = array('70','71','72','73','74');
$nuevo_codigo_asignatura = array('241','242','243','244','245');
//$nuevo_codigo_asignatura = array('243','244','245','246','247');
$codigo_ann_lectivo = '20'; $codigo_bach_o_ciclo = '05';

$query_alumno_matricula = "SELECT codigo_bach_o_ciclo, codigo_ann_lectivo, codigo_grado, codigo_seccion, codigo_alumno, id_alumno_matricula
                            FROM alumno_matricula WHERE codigo_bach_o_ciclo = '$codigo_bach_o_ciclo' and codigo_ann_lectivo = '$codigo_ann_lectivo'";
// Ejecutamos el Query.
	$result_consulta = $dblink -> query($query_alumno_matricula);
		while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
				{
                    $codigo_alumno = $row{4};
                    $codigo_matricula = $row{5};
                    
                    print "codigo alumno: " . $codigo_alumno;
                    print "codigo matricula: " . $codigo_matricula;
                    print "<br>";
                    
                    for($jj=0;$jj<count($nuevo_codigo_asignatura);$jj++)
                    {
                        print $query_actualizar_codigo_a = "UPDATE nota SET codigo_asignatura = '$nuevo_codigo_asignatura[$jj]' WHERE codigo_asignatura = '$codigo_asignatura[$jj]' and codigo_matricula = '$codigo_matricula' and codigo_alumno = '$codigo_alumno'";
                        print "<br>";
                    // Ejecutamos el Query.
                        $result_consulta_a = $dblink -> query($query_actualizar_codigo_a);
                    }
                    

                }

?>