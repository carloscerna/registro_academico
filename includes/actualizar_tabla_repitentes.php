<?php
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
    
    $num = 0;
    $codigo_ann_lectivo = '19';
    $codigo_ann_lectivo_an = '18';
    $repitente = "No";
    $organizacion_secciones_grados = array();
    $query_ann = "SELECT codigo_alumno, codigo_grado from alumno_matricula where codigo_ann_lectivo = '19' ORDER BY codigo_grado";

    $result_ann = $dblink -> query($query_ann);
    // Extraer valore de la consulta.
        // CREAR LA TABLA.
        print utf8_decode("<h3>COMPLEJO EDUCATIVO COLONIA RÍO ZARCO</h3><br>");
        print utf8_decode("<h4>Acutalizar Repitentes</h4>");
            print "<table border=1>";
                print "<tbody>";
                print "<tr>";
                print utf8_decode("<th>N°</th><th>Código alumno/a</th><th>Grado</th><th>Repitente (si o no)</th>");
                print "</tr>";
				 while($row_ = $result_ann -> fetch(PDO::FETCH_BOTH))
				 {
                    $codigo_alumno = trim($row_['codigo_alumno']);
                    $codigo_grado = trim($row_['codigo_grado']);
                        $num++;
                        
                      $busqueda_re = "SELECT * FROM alumno_matricula WHERE codigo_grado = '$codigo_grado' and codigo_alumno = '$codigo_alumno' and codigo_ann_lectivo = '$codigo_ann_lectivo_an'";
                      $result_ = $dblink -> query($busqueda_re);
                        // Extraer valore de la consulta.
                            $verificar = $result_ -> rowCount();
                            if($verificar != 0)	// IF PRINCIPAL QUE VERIFICA SI HAY REGISTROS.
                            {
                                $repitente = utf8_decode("Sí");
                                    $query_actualizar_re = "UPDATE alumno_matricula SET repitente = 'true' WHERE codigo_grado = '$codigo_grado' and codigo_alumno = '$codigo_alumno' and codigo_ann_lectivo = '$codigo_ann_lectivo'";
                                        $result_re = $dblink -> query($query_actualizar_re) or die("No se puedo realizar la actualiación.");
                            }else{
                                $repitente = "No";
                            }

                

                        // Mostrar datos.
                        print '<td>'. $num . '</td>' . '<td>' . $codigo_alumno . '</td>' . '<td>' . $codigo_grado . '</td>' . '<td>' . $repitente . '</td>';
                        print "</tr>";                        
                        //if($num == 10){break;}
                 }
        print "</tbody>";
        print "</table>";
?>