<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// URL PARA GUARDAR LAS IMAGENES.
    $url_ = "/registro_academico/img/portafolio/";
    $small = 'thumbails/';
    $large = 'large/';
    $random = rand();
    $respuestaOK = true;
    $url_archivo = "";
    $mensajeError = "No hay archivo.";
    $contenidoOK = "";
// verificar si existe la imagen.
if (is_array($_FILES) && count($_FILES) > 0) {
    if (($_FILES["file"]["type"] == "image/pjpeg")
        || ($_FILES["file"]["type"] == "image/jpeg")
        || ($_FILES["file"]["type"] == "image/png")
        || ($_FILES["file"]["type"] == "image/gif") 
        || ($_FILES["file"]["type"] == "image/jpg")
        || ($_FILES['file']['type']) == 'application/pdf') {
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $path_root . $url_ . $_FILES['file']['name'])) {
            // Variables de la imagen.
                $id_personal = $_SESSION["id_personal"];
                $id_portafolio = $_SESSION['id_portafolio'];
                $codigo_institucion = $_SESSION["codigo_institucion"];
            // Armamos el query PARA ELIMINAR LA IMAGEN O SEA EL ARCHIVO.
                $query_file = "SELECT * FROM personal_portafolio WHERE id_ = $id_portafolio";
            // Ejecutamos el query
                $resultadoQuery = $dblink -> query($query_file);				
                while($listado = $resultadoQuery -> fetch(PDO::FETCH_BOTH))
                {
                    $nombreArchivo = trim($listado['url_imagen']);
                    $id_personal = trim($listado['id_personal']);
                }
            // REGISTRO CON UNLINK(). 
                if(!empty($nombreArchivo)){
                    if(file_exists($path_root.$url_.$codigo_institucion."/".$nombreArchivo)){
                        unlink($path_root.$url_.$codigo_institucion."/".$nombreArchivo);				// imagen original.
                    }
                    if(file_exists($path_root.$url_.$codigo_institucion."/".$small."/".$nombreArchivo)){
                        unlink($path_root.$url_.$codigo_institucion."/".$small."/".$nombreArchivo);	// imagen small
                    }
                    if(file_exists($path_root.$url_.$codigo_institucion."/".$large."/".$nombreArchivo)){
                        unlink($path_root.$url_.$codigo_institucion."/".$large."/".$nombreArchivo);	// imagen large	
                    }
                }
            //  Captura extensión del archivo tempral.
                $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = "Portafolio"."-".$id_personal."-".$id_portafolio."-".$random.".".$extension;
            //  VERIFICAR SI EXISTE EL DIRECTORIO POR EL ID PERSONAL.
                if(!file_exists($path_root.$url_.$codigo_institucion)){
                    // Crear el Directorio Principal Archvos...
                        mkdir ($path_root.$url_.$codigo_institucion."/");
                        chmod($path_root.$url_.$codigo_institucion."/",07777);
                }
            //  VERIFICAR SI EXISTE EL DIRECTORIO. SMALL
                if(!file_exists($path_root.$url_.$codigo_institucion."/".$small)){
                    // Crear el Directorio Principal Archvos...
                        mkdir ($path_root.$url_.$codigo_institucion."/".$small."/");
                        chmod($path_root.$url_.$codigo_institucion."/".$small."/",07777);
                }
            //  VERIFICAR SI EXISTE EL DIRECTORIO. SMALL
                if(!file_exists($path_root.$url_.$codigo_institucion."/".$large)){
                    // Crear el Directorio Principal Archvos...
                        mkdir ($path_root.$url_.$codigo_institucion."/".$large."/");
                        chmod($path_root.$url_.$codigo_institucion."/".$large."/",07777);
                }
            //  MOVER A LA CARPETA QUE SE VA CREANDO DE CADA USUARIO.
                rename($path_root.$url_."/".$_FILES['file']['name'],$path_root.$url_.$codigo_institucion."/".$nombreArchivo);
            
            // UTILIZACIÓN DE LAS HERRAMIENTAS GD CON IMAGE.
                // VALIDAR SI EL ARCHIVO ES PDF O IMAGEN
                $archivo_validar_pdf = false;
                //  Abrir foto original
                    if($_FILES["file"]["type"] == "image/jpeg"){
                        $original = imagecreatefromjpeg($path_root.$url_.$codigo_institucion."/".$nombreArchivo);
                    }else if($_FILES["file"]["type"] == "image/png"){
                        $original = imagecreatefrompng($path_root.$url_.$codigo_institucion."/".$nombreArchivo);
                    }else if(($_FILES['file']['type']) == 'application/pdf'){
                        $mensajeError = "Cargado Archivo PDF...";
                        $contenidoOK = "pdf";
                        $archivo_validar_pdf = true;
                            //copy($path_root.$url_."/".$codigo_institucion."/".$nombreArchivo,$path_root.$url_.$codigo_institucion."/".$large."/".$nombreArchivo);
                            //copy($path_root.$url_."/".$codigo_institucion."/".$nombreArchivo,$path_root.$url_.$codigo_institucion."/".$small."/".$nombreArchivo);
                    }
            // validar si el archivo es PDF O IMAGEN
            if($archivo_validar_pdf == false){
                $mensajeError = "Cargado Archivo IMAGEN...";
                $contenidoOK = "img";
                //  OBETNER COORD3ENADAS ANCHO Y ALTO.
                    $ancho_original = imagesx( $original );
                    $alto_original = imagesy( $original );
                        //  ****************************************************************************************************************
                        //  ****************************************************************************************************************
                        //  Crear un lienzo vacio (foto destino Small)
                            $ancho_nuevo = 168; // $small.
                            $alto_nuevo = round( $ancho_nuevo * $alto_original / $ancho_original );

                            $copia = imagecreatetruecolor( $ancho_nuevo , $alto_nuevo );
                        //  Copiar orignal --> copia
                            imagecopyresampled($copia, $original, 0,0,0,0, $ancho_nuevo, $alto_nuevo, $ancho_original, $alto_original );
                        //  Exportar y guardar imagen.
                            imagejpeg( $copia, $path_root.$url_.$codigo_institucion."/".$small."/".$nombreArchivo, 100);
                        //  ****************************************************************************************************************
                        //  ****************************************************************************************************************

                        //  ****************************************************************************************************************
                        //  ****************************************************************************************************************
                        //  Crear un lienzo vacio (foto destino $large)
                            $ancho_nuevo = 1024; // $large.
                            $alto_nuevo = round( $ancho_nuevo * $alto_original / $ancho_original );

                            $copia = imagecreatetruecolor( $ancho_nuevo , $alto_nuevo );
                        //  Copiar orignal --> copia
                            imagecopyresampled($copia, $original, 0,0,0,0, $ancho_nuevo, $alto_nuevo, $ancho_original, $alto_original );
                        //  Exportar y guardar imagen.
                            imagejpeg( $copia, $path_root.$url_.$codigo_institucion."/".$large."/".$nombreArchivo, 100);
                        //  ****************************************************************************************************************
                        //  ****************************************************************************************************************
                // UTILIZACIÓN DE LAS HERRAMIENTAS GD CON IMAGE.
            }
            // Guardar el nombre de la imagen. en la tabla.            
            // Armar query. para actualizar el nombre del archivo de la ruta foto.
                $query = "UPDATE personal_portafolio SET url_imagen = '".$nombreArchivo."' WHERE id_ = ". $id_portafolio;
            // Ejecutamos el Query.
                $consulta = $dblink -> query($query);
                    $url_archivo = "..".$url_.$codigo_institucion."/".$nombreArchivo;
                    // Armamos array para convertir a JSON
                    $salidaJson = array("respuesta" => $respuestaOK,
                        "mensaje" => $mensajeError,
                        "url" => $url_archivo,
                        "contenido" => $contenidoOK);
                    echo json_encode($salidaJson);
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