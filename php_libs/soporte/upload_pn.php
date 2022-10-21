<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// URL PARA GUARDAR LAS IMAGENES.
    $url_ = "/registro_academico/img/Pn/";
    $url_respaldo_pn = "d:/registro_academico/img/Pn/";
    $random = rand();
// Variables
    $Id_ = $_SESSION["Id_A"];
    $codigo_institucion = $_SESSION["codigo_institucion"];
if (is_array($_FILES) && count($_FILES) > 0) {
    if (($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/png")
        || ($_FILES["file"]["type"] == "image/gif")) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $path_root . $url_ .$_FILES['file']['name'])) {
            //  Eliminar archivo anterior.
            // Armamos el query PARA ELIMINAR LA IMAGEN O SEA EL ARCHIVO.
                $query_file = "SELECT * FROM alumno WHERE id_alumno = $Id_";
            // Ejecutamos el query
                $resultadoQuery = $dblink -> query($query_file);				
                while($listado = $resultadoQuery -> fetch(PDO::FETCH_BOTH))
                {
                    $nombreArchivo = trim($listado['ruta_pn']);
                    $id_alumno = trim($listado['id_alumno']);
                }
            // REGISTRO CON UNLINK().
                if(!empty($nombreArchivo)){
                    if(file_exists($path_root.$url_.$nombreArchivo)){
                        unlink($path_root.$url_.$nombreArchivo);				// imagen original.
                    }
                }
                // cperta del codigo de la institución
                if(!empty($nombreArchivo)){
                    if(file_exists($path_root.$url_.$codigo_institucion."/".$nombreArchivo)){
                        unlink($path_root.$url_.$codigo_institucion."/".$nombreArchivo);				// imagen original.
                    }
                }
                // CARPETA respaldo de PN en unidad D:
                if(file_exists($url_respaldo_pn.$codigo_institucion."/".$nombreArchivo)){
                    unlink($url_respaldo_pn.$codigo_institucion."/".$nombreArchivo);				// imagen original.
                }
            // Variables de la imagen.
                $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = "foto-".$codigo_institucion ."-".$id_alumno."-".$random.".".$extension;
            //  VERIFICAR SI EXISTE EL DIRECTORIO POR EL ID PERSONAL.
                if(!file_exists($path_root.$url_.$codigo_institucion)){
                    // Crear el Directorio Principal Archvos...
                        mkdir ($path_root.$url_.$codigo_institucion."/");
                        chmod($path_root.$url_.$codigo_institucion."/",07777);
                }
                // carpeta local c
                    rename($path_root.$url_.$_FILES['file']['name'],$path_root.$url_.$codigo_institucion."/".$nombreArchivo);
                // respaldo d
                    rename($path_root.$url_.$_FILES['file']['name'],$url_respaldo_pn.$codigo_institucion."/".$nombreArchivo);
            // Guardar el nombre de la imagen. en la tabla.            
            // Armar query. para actualizar el nombre del archivo de la ruta foto.
                $query = "UPDATE alumno SET ruta_pn = '".$nombreArchivo."' WHERE id_alumno = ". $Id_;
            // Ejecutamos el Query.
                $consulta = $dblink -> query($query);
            //echo "../registro_academico/img/png/".$_FILES['file']['name'];
                echo "../registro_academico/img/Pn/".$codigo_institucion."/".$nombreArchivo;
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>