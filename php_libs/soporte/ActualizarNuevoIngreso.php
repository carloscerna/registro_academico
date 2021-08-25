<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    $db_link = $dblink;
    $codigo_annlectivo = $_REQUEST["codigo_annlectivo"];
    
    $num = 0;
    $codigo_ann_lectivo = $codigo_annlectivo;
    $codigo_ann_lectivo_an = $codigo_ann_lectivo - 1;
    $nuevo_ingreso = "No";
    $organizacion_secciones_grados = array();
    $query_ann = "SELECT codigo_alumno, codigo_grado from alumno_matricula where codigo_ann_lectivo = '$codigo_ann_lectivo' ORDER BY codigo_grado";

    print $codigo_ann_lectivo;
    print $codigo_ann_lectivo_an;
    
    $result_ann = $dblink -> query($query_ann);
    // Extraer valore de la consulta.
        // CREAR LA TABLA.
        print utf8_decode("<h3>COMPLEJO EDUCATIVO COLONIA RÍO ZARCO</h3><br>");
        print utf8_decode("<h4>Acutalizar Nuevo Ingreso</h4>");
            print "<table border=1>";
                print "<tbody>";
                print "<tr>";
                print utf8_decode("<th>N°</th><th>Código alumno/a</th><th>Grado</th><th>Nuevo Ingreso (si o no)</th>");
                print "</tr>";
				 while($row_ = $result_ann -> fetch(PDO::FETCH_BOTH))
				 {
                    $codigo_alumno = trim($row_['codigo_alumno']);
                    $codigo_grado = trim($row_['codigo_grado']);
                        $num++;
                        
                      $busqueda_re = "SELECT * FROM alumno_matricula WHERE codigo_alumno = '$codigo_alumno' and codigo_ann_lectivo = '$codigo_ann_lectivo_an'";
                      $result_ = $dblink -> query($busqueda_re);
                        // Extraer valore de la consulta.
                            $verificar = $result_ -> rowCount();
                            if($verificar != 0)	// IF PRINCIPAL QUE VERIFICA SI HAY REGISTROS.
                            {
                                $nuevo_ingreso = "No";
                                   $query_actualizar_re = "UPDATE alumno_matricula SET nuevo_ingreso = 'false' WHERE codigo_grado = '$codigo_grado' and codigo_alumno = '$codigo_alumno' and codigo_ann_lectivo = '$codigo_ann_lectivo'";
                                        $result_re = $dblink -> query($query_actualizar_re) or die("No se puedo realizar la actualiación.");
                            }else{
                                $nuevo_ingreso = utf8_decode("Sí");
                                   $query_actualizar_re = "UPDATE alumno_matricula SET nuevo_ingreso = 'true' WHERE codigo_grado = '$codigo_grado' and codigo_alumno = '$codigo_alumno' and codigo_ann_lectivo = '$codigo_ann_lectivo'";
                                        $result_re = $dblink -> query($query_actualizar_re) or die("No se puedo realizar la actualiación.");
                    }
                        // Mostrar datos.
                        print '<td>'. $num . '</td>' . '<td>' . $codigo_alumno . '</td>' . '<td>' . $codigo_grado . '</td>' . '<td>' . $nuevo_ingreso . '</td>';
                        print "</tr>";                        
                        //if($num == 10){break;}
                 }
        print "</tbody>";
        print "</table>";
?>