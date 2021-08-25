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
    $solo_apellidos = array();
    $codigo_ann_lectivo = '20';
    $organizacion_grados_secciones = array();
    // ORGANIZACIÓN ANUAL, MODALIDAD, GRADIO, SECCIÓN Y TURNO.
    $query_grados_secciones = "SELECT org.codigo_grado, org.codigo_seccion, org.codigo_bachillerato, gr.nombre as nombre_grado, sec.nombre as nombre_seccion
                                FROM organizacion_grados_secciones org
                                INNER JOIN grado_ano gr ON gr.codigo = org.codigo_grado
                                INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
                                WHERE codigo_ann_lectivo = '$codigo_ann_lectivo'
                                ORDER BY codigo_grado, codigo_seccion";
    // EJECUTAR CONSULTA.
    $result_grado_seccion = $dblink -> query($query_grados_secciones);
    // CREAR MATRIZ PARA QUE LOS APELLIDOS NO SE REPITAN.
    while($listado = $result_grado_seccion -> fetch(PDO::FETCH_BOTH))
    {
        $organizacion_grados_secciones[] = trim($listado['codigo_grado']) . trim($listado['codigo_seccion']);

    }  
    //print_r($organizacion_grados_secciones);
    //exit;

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
        WHERE am.codigo_ann_lectivo = '20' and btrim(am.codigo_grado || am.codigo_seccion) = '$organizacion_grados_secciones[1]'
        ORDER BY solo_apellidos ASC, codigo_bach_o_ciclo, codigo_grado, codigo_seccion, codigo_turno";
    $result_ = $dblink -> query($query_listado_completo);
    // CREAR MATRIZ PARA QUE LOS APELLIDOS NO SE REPITAN.
    while($row_r = $result_ -> fetch(PDO::FETCH_BOTH))
    {
        $sin_tilde[] = trim($row_r['sin_tilde']);

    }  
    // Eliminar valores repetidos
        $solo_apellidos = array_values(array_unique($sin_tilde));

    // Extraer valore de la consulta.
        // CREAR LA TABLA.
        print utf8_decode("<h3>COMPLEJO EDUCATIVO COLONIA RÍO ZARCO</h3><br>");
        print utf8_decode("<h4>Nómina de Alumnas Iguales o Mayores a 10 años</h4>");
            print "<table border=1>";
                print "<tbody>";
                print "<tr>";
                print ("<th>N°</th><th>Código Alumno</th><th>NIE</th><th>Nombre del alumno/a</th><th>Grado</th><th>Sección</th><th>Encargado</th><th>Firma</th>");
                print "</tr>";
                        // Extraer valore de la consulta.
                                     for($hh=0;$hh<count($solo_apellidos);$hh++)
                                     {
                                         // Valor de la matriz
                                            $solo_apellidos_busqueda = $solo_apellidos[$hh];
                                            $total_estudiantes++; $cantidad_hermanos = 0;
                                        // armar query para verificar si tiene hermanos.
                                        $query_hermanos = "SELECT a.codigo_nie, a.edad, a.genero, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                                            btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo, 
                                            btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as solo_apellidos, 
                                            am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
                                            gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, am.id_alumno_matricula, sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno,
                                            ae.nombres as nombre_encargado, ae.dui
                                            FROM alumno a 
                                            INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
                                            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
                                            INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
                                            INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
                                            INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
                                            INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
                                            WHERE am.codigo_ann_lectivo = '20' and
                                            translate(btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') = translate('$solo_apellidos_busqueda','áéíóúÁÉÍÓÚ','aeiouAEIOU') 
                                            ORDER BY solo_apellidos ASC, am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion";
                                        // ejecutar query
                                            $result_hermanos = $dblink -> query($query_hermanos);
                                        // 
                                        if($result_hermanos -> rowCount() != 0){
                                            $hermanos = true;
                                            //$cantidad_hermanos = $result_hermanos -> rowCount();
                                                while($listados = $result_hermanos -> fetch(PDO::FETCH_BOTH))
                                                {
                                                    // datos apra el listado.
                                                    $cantidad_hermanos++;
                                                    //
                                                    $codigo_alumno = (trim($listados['id_alumno']));
                                                    $codigo_nie = (trim($listados['codigo_nie']));
                                                    $nombre_completo = strtoupper((trim($listados['apellido_alumno'])));
                                                    $nombre_grado = (trim($listados['nombre_grado']));
                                                    $nombre_seccion = (trim($listados['nombre_seccion']));
                                                    $nombre_encargado = (trim($listados['nombre_encargado']));

                                                    // Verficar si hay mas de un hermano.
                                                    if($cantidad_hermanos == 1){
                                                        $num++; 
                                                        $total_familias++;
                                                        print "<tr>";
                                                        print "<td>$num</td><td>$codigo_alumno</td><td>$codigo_nie</td><td>$nombre_completo</td><td>".($nombre_grado)."</td><td>".($nombre_seccion)."</td><td>".($nombre_encargado)."</td><td>$cantidad_hermanos";
                                                        print "</tr>";
                                                    }else if($cantidad_hermanos == 2){
                                                        $total_familias--;
                                                        print "<tr>";
                                                        print "<td></td><td>$codigo_alumno</td><td>$codigo_nie</td><td>$nombre_completo</td><td>".($nombre_grado)."</td><td>".($nombre_seccion)."</td><td>".($nombre_encargado)."</td><td>$cantidad_hermanos";
                                                        print "</tr>";
                                                    }else{
                                                        print "<tr>";
                                                        print "<td></td><td>$codigo_alumno</td><td>$codigo_nie</td><td>$nombre_completo</td><td>".($nombre_grado)."</td><td>".($nombre_seccion)."</td><td>".($nombre_encargado)."</td><td>$cantidad_hermanos";
                                                        print "</tr>";
                                                    }
                                                }
                                        }else{
                                            // Reset variables.
                                            $hermanos = false; $cantidad_hermanos = 0;
                                        }
                                        // TOTAL FAMILIAS
                                        if($hermanos == false){
                                            $total_familias++;
                                        }
                                     }  // DO WHILE HERMANOS
        print "</tbody>";
        print "</table>";
        print "<b>TOTAL DE FAMILIAS: $total_familias</b><br>";
        print "<br>";
?>