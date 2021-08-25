<?php
// variables/conexion.
    $host = 'localhost';
    $port = 5432;
    $database = 'registro_academico_10428';
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
    $cantidad_hermanos = 0;
    $total_estudiantes = 0;
    $total_familias = 0;
    $num = 0;
    $hermanos = false;
    $matriz_id_hermanos = array();
    $codigo_ann_lectivo = '20';
    $organizacion_secciones_grados = array();
    $query_grados_secciones = "SELECT org.codigo_grado, org.codigo_seccion, org.codigo_bachillerato, gr.nombre as nombre_grado, sec.nombre as nombre_seccion
                                FROM organizacion_grados_secciones org
                                INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
                                INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
                                WHERE codigo_ann_lectivo = '$codigo_ann_lectivo'
                                ORDER BY codigo_grado, codigo_seccion";
    // Ejecutar consulta.
    $result_grado_seccion = $dblink -> query($query_grados_secciones);
        // CREAR MATRIZ PARA QUE LOS APELLIDOS NO SE REPITAN.
        // select que busca todos los apellidos de estudiantes.
        $query_listado_completo = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as solo_apellidos,
        translate(btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') as sin_tilde,
        ae.nombres, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo,
        bach.nombre as nombre_bachillerato,
        am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion, am.codigo_ann_lectivo,  
        am.retirado, am.id_alumno_matricula
        FROM alumno a
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
        INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
        INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
        INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
        WHERE am.codigo_ann_lectivo = '20' 
        ORDER BY solo_apellidos ASC, codigo_bach_o_ciclo, codigo_grado, codigo_seccion, codigo_turno";
        $result_ = $dblink -> query($query_listado_completo);
        while($row_r = $result_ -> fetch(PDO::FETCH_BOTH))
        {
            $sin_tilde[] = trim($row_r['sin_tilde']);
    
        }  
        // Eliminar valores repetidos
            $solo_apellidos = array_values(array_unique($sin_tilde));
            $familias = count($solo_apellidos);
    // Extraer valore de la consulta.
        // CREAR LA TABLA.
        print utf8_decode("<h3>COMPLEJO EDUCATIVO COLONIA RÍO ZARCO</h3><br>");
        print utf8_decode("<h4>Nómina de Alumnas Iguales o Mayores a 10 años</h4>");
            print "<table border=1>";
                print "<tbody>";
                print "<tr>";
                print ("<th>N°</th><th>Código Alumno</th><th>NIE</th><th>Nombre del alumno/a</th><th>Grado</th><th>Sección</th><th>Edad</th><th>Hermanos</th>");
                print "</tr>";
				 while($row_ = $result_grado_seccion -> fetch(PDO::FETCH_BOTH))
				 {
                     $hermanos = false;
                    $nombre_grado = trim($row_['nombre_grado']);
                    $nombre_seccion = trim($row_['nombre_seccion']);
                    $codigo_grado =  $row_['codigo_grado'];
                    $codigo_seccion = $row_['codigo_seccion'];
                    $todos  = $row_['codigo_bachillerato'] . $row_['codigo_grado'] . $row_['codigo_seccion'] . $codigo_ann_lectivo;
                    //print 'Grado: ' . $nombre_grado . utf8_decode(' Sección: ') . $nombre_seccion . '<br>';


                    $query = "SELECT a.codigo_nie, a.edad, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                        a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, 
                        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as solo_apellidos,
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
                                     while($row_r = $result_ -> fetch(PDO::FETCH_BOTH))
                                     {
                                        $num++;
                                        $total_estudiantes++;
                                        $codigo_alumno = (trim($row_r['id_alumno']));
                                        $codigo_nie = (trim($row_r['codigo_nie']));
                                        $nombre_completo = (trim($row_r['apellido_alumno']));
                                        $solo_apellidos = (trim($row_r['solo_apellidos']));
                                        $edad = (trim($row_r['edad']));
                                        // armar query para verificar si tiene hermanos.
                                        $query_hermanos = "SELECT a.codigo_nie, a.edad, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                                            btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,  
                                            am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
                                            gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, am.id_alumno_matricula, sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno 
                                            FROM alumno a INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
                                            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
                                            INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
                                            INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
                                            INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
                                            INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
                                            WHERE am.codigo_ann_lectivo = '20' and  translate(btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') = translate('$solo_apellidos','áéíóúÁÉÍÓÚ','aeiouAEIOU') and id_alumno <> $codigo_alumno
                                            ORDER BY apellido_alumno ASC";
                                        // ejecutar query
                                            $result_hermanos = $dblink -> query($query_hermanos);
                                        // 
                                        if($result_hermanos -> rowCount() != 0){
                                            $hermanos = true;
                                            $cantidad_hermanos = $result_hermanos -> rowCount();
                                                while($listados = $result_hermanos -> fetch(PDO::FETCH_BOTH))
                                                {
                                                    $codigo_alumno_hermano = trim($listados['id_alumno']);
                                                    $matriz_id_hermanos[] = $codigo_alumno_hermano;     // Lleno matriz.
                                                }
                                        }else{
                                            // Reset variables.
                                            $hermanos = false; $cantidad_hermanos = 0;
                                        }
                                        // validar la salida
                                        if($hermanos == true){
                                            print "<tr>";
                                            print "<td>$num</td><td>$codigo_alumno</td><td>$codigo_nie</td><td>$nombre_completo</td><td>".($nombre_grado)."</td><td>".($nombre_seccion)."</td><td>$edad</td><td>$cantidad_hermanos</td>";
                                            print "</tr>";
                                        }else{
                                        // Imprimir valores
                                            print "<tr>";
                                            print "<td>$num</td><td>$codigo_alumno</td><td>$codigo_nie</td><td>$nombre_completo</td><td>".($nombre_grado)."</td><td>".($nombre_seccion)."</td><td>$edad</td><td>$cantidad_hermanos</td>";
                                            print "</tr>";
                                            $total_familias++;
                                        }
                                     }  // DO WHILE HERMANOS
                                     // valor num a cero.
                                        $num = 0;
                 }  // do while de grado sección y turno.
        print "</tbody>";
        print "</table>";
        print "<b>TOTAL DE ESTUDIANTES: $total_estudiantes</b><br>";
        print "<b>TOTAL DE FAMILIAS: $familias</b><br>";
?>