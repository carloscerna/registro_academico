<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Variables de la imagen.
    $query_a = "SELECT id_alumno FROM alumno ORDER BY id_alumno";
    $result_a = $dblink -> query($query_a);
    $existe = 0;
// Recorre para veriricar si existe la partida de nacimiento.
	while($row_ = $result_a -> fetch(PDO::FETCH_BOTH))
        {
            $id_ = $row_['id_alumno'];
            $nombreArchivo = "Pn-".$_SESSION["codigo_institucion"]."-".$id_.".jpg";
            $ruta = $path_root."/registro_academico/img/Pn/".$nombreArchivo;
    
            $existe = is_file($ruta);
            
            // Imprimir 
            print $nombreArchivo . " - " . $ruta . "<br>";
            
            if(!is_file($ruta))
            {
                // Armar query.
                    $query = "UPDATE alumno SET partida_nacimiento = '$existe' WHERE id_alumno = ". $id_;
                    print "<br>" . $query;
                // Ejecutamos el Query.
                    $consulta = $dblink -> query($query);        
            }else{
                // Armar query.
                    $query = "UPDATE alumno SET partida_nacimiento = '$existe' WHERE id_alumno = ". $id_;
                    print "<br>" . $query;
                // Ejecutamos el Query.
                    $consulta = $dblink -> query($query);                        
            }
        }
?>