<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// Valores del Post.
    $NumeroCondicion = $_REQUEST["NumeroCondicion"];
    $codigoDepartamento = $_REQUEST["CodigoDepartamento"];
    $codigoMunicipio = $_REQUEST["CodigoMunicipio"];
    $codigoDistrito = $_REQUEST["CodigoDistrito"];
// consulta PDO.
try{
    switch ($NumeroCondicion) {
        case 1:
            // FILTRAR DEPARTAMENTOS.
                $dblink = $dblink->prepare("SELECT distinct codigo_departamento as codigo, nombre_departamento as descripcion FROM elsalvador
                    ORDER BY codigo_departamento"); 
            break;
        case 2:
            // FILTRA PARA LOS MUNICIPIOS.
                $dblink = $dblink->prepare("SELECT distinct codigo_municipio AS codigo, nombre_municipio AS descripcion FROM elsalvador WHERE codigo_departamento = '$codigoDepartamento'
                    ORDER BY codigo_municipio");
            break;
        case 3:
            // FILTAR POR DISTRITO.
                $dblink = $dblink->prepare("SELECT codigo_departamento, nombre_departamento, codigo_municipio, nombre_municipio, codigo_distrito as codigo, nombre_distrito as descripcion
                    FROM elsalvador
                        WHERE codigo_municipio = '$codigoMunicipio' and codigo_departamento = '$codigoDepartamento'
                            ORDER BY codigo_departamento");
            break;
        case 4:
            // FILTAR POR CANTÓN.
                $dblink = $dblink->prepare("SELECT codigo_departamento, codigo_municipio, codigo_distrito, codigo, descripcion
                    FROM catalogo_canton
                        WHERE codigo_departamento = '$codigoDepartamento' and codigo_nuevo_municipio = '$codigoMunicipio' and codigo_distrito = '$codigoDistrito'
                            ORDER BY codigo");
            break;
    }
// Ejectuar consulta
    $dblink->execute(); 
    $ElSalvador = $dblink->fetchAll(PDO::FETCH_ASSOC); 
// Enviar datos.
    echo json_encode($ElSalvador);
}
catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}