<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// URL PARA GUARDAR LAS IMAGENES.   
    $url_ = "/registro_academico/img/fotos/";
    $SistemaSiscarad = "C:/wamp64/www/siscarad/public/img/fotos/";
    $url_respaldo_fotos = "d:/registro_academico/img/fotos/";
    $random = rand();
// VARIABLES.
    //$Id_ = $_SESSION["Id_A"];
    $codigo_institucion = $_SESSION["codigo_institucion"];
// verificar si existe la imagen.
if (is_array($_FILES) && count($_FILES) > 0) {
    if (($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/png")
        || ($_FILES["file"]["type"] == "image/gif")) {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $path_root . $url_ .$_FILES['file']['name'])) {
            // Variables de la imagen.
                $id_personal = $_SESSION["id_personal"];
            //  Eliminar archivo anterior.
            // Armamos el query PARA ELIMINAR LA IMAGEN O SEA EL ARCHIVO.
                $query_file = "SELECT * FROM personal WHERE id_personal = $id_personal";
            // Ejecutamos el query
                $resultadoQuery = $dblink -> query($query_file);				
                while($listado = $resultadoQuery -> fetch(PDO::FETCH_BOTH))
                {
                    $nombreArchivo = trim($listado['foto']);
                    $id_personal = trim($listado['id_personal']);
                }
            // REGISTRO CON UNLINK(). para eliminar el archivo.
                if(!empty($nombreArchivo)){
                    if(file_exists($path_root.$url_.$codigo_institucion."/".$nombreArchivo)){
                        unlink($path_root.$url_.$codigo_institucion."/".$nombreArchivo);				// imagen original.
                    }
                    // CARPETA SISCARAD
                    if(file_exists($SistemaSiscarad.$codigo_institucion."/".$nombreArchivo)){
                        unlink($SistemaSiscarad.$codigo_institucion."/".$nombreArchivo);				// imagen original.
                    }
                    // CARPETA respaldo de fotos en unidad D:
                    if(file_exists($url_respaldo_fotos.$codigo_institucion."/".$nombreArchivo)){
                        unlink($url_respaldo_fotos.$codigo_institucion."/".$nombreArchivo);				// imagen original.
                    }
                }
            // Capturar nombre temporal.
                $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = "foto-".$id_personal."-".$random.".".$extension;
            //  VERIFICAR SI EXISTE EL DIRECTORIO POR EL ID PERSONAL.
            if(!file_exists($path_root.$url_.$codigo_institucion)){
                // Crear el Directorio Principal Archvos...
                    mkdir ($path_root.$url_.$codigo_institucion."/");
                    chmod($path_root.$url_.$codigo_institucion."/",07777);
            }
            //  VERIFICAR SI EXISTE EL DIRECTORIO POR EL ID PERSONAL. SISCARAD
            if(!file_exists($SistemaSiscarad.$codigo_institucion)){
                // Crear el Directorio Principal Archvos...
                    mkdir($SistemaSiscarad.$codigo_institucion."/");
                    chmod($SistemaSiscarad.$codigo_institucion."/",07777);
            }
            //  VERIFICAR SI EXISTE EL DIRECTORIO POR EL ID PERSONAL. UNIDAD DE RESPALDO D:
            if(!file_exists($url_respaldo_fotos.$codigo_institucion)){
                // Crear el Directorio Principal Archvos...
                    mkdir($url_respaldo_fotos.$codigo_institucion."/");
                    chmod($url_respaldo_fotos.$codigo_institucion."/",07777);
            }
            //  renombrar archivo y la ubicación por defecto.
                rename($path_root.$url_.$_FILES['file']['name'],$path_root.$url_.$nombreArchivo);
            // UTILIZACIÓN DE LAS HERRAMIENTAS GD CON IMAGE.
                //  Abrir foto original
                if($_FILES["file"]["type"] == "image/jpeg"){
                    $original = imagecreatefromjpeg($path_root.$url_.$nombreArchivo);
                }else if($_FILES["file"]["type"] == "image/png"){
                    $original = imagecreatefrompng($path_root.$url_.$nombreArchivo);
                }
            //  OBETNER COORD3ENADAS ANCHO Y ALTO.
                $ancho_original = imagesx( $original );
                $alto_original = imagesy( $original );
                    //  ****************************************************************************************************************
                    //  ****************************************************************************************************************
                    //  Crear un lienzo vacio (foto destino Small)
                        $ancho_nuevo = 210; // $small.
                        $alto_nuevo = round( $ancho_nuevo * $alto_original / $ancho_original );

                        $copia = imagecreatetruecolor( $ancho_nuevo , $alto_nuevo );
                    //  Copiar orignal --> copia
                        imagecopyresampled($copia, $original, 0,0,0,0, $ancho_nuevo, $alto_nuevo, $ancho_original, $alto_original );
                    //  Exportar y guardar imagen.
                        imagejpeg($copia, $path_root.$url_.$codigo_institucion."/".$nombreArchivo, 100);
                    //  COPIAR FOTO EN SISCARAD /PUBLIC/IMG/fotos/codigo insstitucion
                        imagejpeg($copia, $SistemaSiscarad.$codigo_institucion."/".$nombreArchivo, 100);
                    //  COPIAR FOTO EN UNIDAD DE DISCO D:/REGISTRO-ACADEMICO/IMG/FOTOS
                        imagejpeg($copia, $url_respaldo_fotos.$codigo_institucion."/".$nombreArchivo, 100);
                    //  ****************************************************************************************************************
                    //  ****************************************************************************************************************
            // UTILIZACIÓN DE LAS HERRAMIENTAS GD CON IMAGE.
                // Guardar el nombre de la imagen. en la tabla.            
            // Armar query. para actualizar el nombre del archivo de la ruta foto.
                $query = "UPDATE personal SET foto = '".$nombreArchivo."' WHERE id_personal = ". $id_personal;
            // Ejecutamos el Query.
                $consulta = $dblink -> query($query);
                    echo "../registro_academico/img/fotos/".$codigo_institucion."/".$nombreArchivo;
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