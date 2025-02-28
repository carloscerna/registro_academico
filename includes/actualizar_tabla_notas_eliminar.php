<?php
// iniciar sesssion.
//session_name('demoUI');
//session_start();
// omitir errores.
set_time_limit(0);
ini_set("memory_limit","2000M");
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
$todos='0302012401'; // MODALIDAD - GRADO - SECCION - ANN LECTIVO
$codigo_asignatura = array('1110');
$num = 0;
$codigo_asignatura_array = array(); // Educación Básica de 1.º a 6.º.
$todos='134P25'; //0507012501 0404012501
$codigo_bachillerato = substr($todos,0,2);
$codigo_grado = substr($todos,2,2);
$codigo_annlectivo = substr($todos,4,2);
$num = 0;

// buscar asignaturas dependiendo del Ciclo y Grado.
// Consultar a la tabla codigo asignatura, para generar el codigo individual de cada una de ellas segun el ciclo o bachillerato.
	$query_consulta_asignatura = "SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = '".$codigo_bachillerato."' and codigo_ann_lectivo = '".$codigo_annlectivo."' and codigo_grado = '".$codigo_grado."' ORDER BY codigo_asignatura ASC";
		$result_consulta = $dblink -> query($query_consulta_asignatura);
		while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
		{
			$codigo_asignatura_array[] = $row['codigo_asignatura'];
		}
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
			WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$todos.
			"' ORDER BY apellido_alumno, am.codigo_grado, am.codigo_seccion ASC";
            
            //WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$todos.
      $result_ = $dblink -> query($query);
    // Extraer valore de la consulta.
				 while($row_ = $result_ -> fetch(PDO::FETCH_BOTH))
				 {
                    $num++;
                    $codigo_nie = $row_['codigo_nie'];
                    $codigo_alumno = $row_['id_alumno'];
                    $codigo_alumno_matricula = $row_['id_alumno_matricula'];
                    $nombres = trim($row_['apellido_alumno']);

                    for ($i=0; $i < count($codigo_asignatura_array); $i++) { 
                      $query_eliminar = "DELETE FROM nota WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_alumno_matricula' and codigo_asignatura = '$codigo_asignatura_array[$i]'";
                        
                        $result_consulta_eliminar_notas = $dblink -> query($query_eliminar);
                        print $num . "Eliminar-" . $codigo_nie . "-" .  $codigo_alumno . " " . $codigo_alumno_matricula . " " .$nombres . " " . $codigo_asignatura_array[$i] . "<br>";
                            if($result_consulta_eliminar_notas)
                            {
                                //
                                
                            }
                        
                    }
                    // eliminar matricula
                    print $query_eliminar_matricula = "DELETE FROM alumno_matricula where codigo_alumno = '$codigo_alumno' and id_alumno_matricula = '$codigo_alumno_matricula'";
                    $result_consulta_eliminar_matricula = $dblink -> query($query_eliminar_matricula);
                 }
                 
                 
?>