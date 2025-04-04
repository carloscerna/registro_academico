<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
  include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
//
    include($path_root."/registro_academico/includes/funciones.php");
// cambiar a utf-8.
header("Content-Type: text/html; charset=UTF-8");    
// Valores del Post.
    $NumeroCondicion = $_REQUEST["NumeroCondicion"];
// consulta PDO.
    switch ($NumeroCondicion) {
        case 1:
            // TABLA NACIONALIDAD.
            $stmt = $dblink->prepare("SELECT codigo, gentilicio as descripcion FROM catalogo_nacionalidad ORDER BY codigo");
            break;
        case 2:
            // TABLA GENERO.
            $stmt = $dblink->prepare("SELECT codigo, descripcion FROM catalogo_genero"); 
            break;
        case 3:
            // TABLA ETNIA.
            $stmt = $dblink->prepare("SELECT codigo, descripcion from catalogo_etnia ORDER BY codigo");
            break;
        case 4:
            // TABLA DISCAPACIDAD.
            $stmt = $dblink->prepare("SELECT codigo, nombre as descripcion from catalogo_tipo_de_discapacidad ORDER BY codigo");
            break;
        case 5:
            // TABLA DIAGNOSTICO.
            $stmt = $dblink->prepare("SELECT codigo, descripcion from catalogo_diagnostico ORDER BY codigo");
            break;
        case 6:
            // TABLA SERVICIO APOYO EDUCATIVO.
            $stmt = $dblink->prepare("SELECT codigo, nombre AS descripcion from catalogo_servicios_de_apoyo_educativo ORDER BY codigo");
            break;
        case 7:
            // TABLA ACTIVIDAD ECONOMICA.
            $stmt = $dblink->prepare("SELECT codigo, nombre AS descripcion FROM catalogo_actividad_economica ORDER BY codigo");
            break;
        case 8:
            // TABLA ESTADO FAMILIAR.
            $stmt = $dblink->prepare("SELECT codigo, nombre as descripcion FROM catalogo_estado_familiar ORDER BY codigo");
            break;
        case 9:
            // TABLA ESTADO CIVIL.
            $stmt = $dblink->prepare("SELECT codigo, nombre AS descripcion from catalogo_estado_civil ORDER BY codigo");
            break;
        case 10:
            // TABLA ZONA RESIDENCIA.
            $stmt = $dblink->prepare("SELECT codigo, nombre as descripcion from catalogo_zona_residencia ORDER BY codigo");
            break;
        case 11:
            // TABLA TIPO DE VIVIENDA.
            $stmt = $dblink->prepare("SELECT codigo, nombre as descripcion from catalogo_vivienda ORDER BY codigo");
            break;
        case 12:
            // TABLA ABASTECIMIENTO DE AGUA.
            $stmt = $dblink->prepare("SELECT codigo, descripcion from catalogo_abastecimiento ORDER BY codigo");
            break;
        case 13:
            // TABLA Estatus
            $stmt = $dblink->prepare("SELECT codigo, descripcion from catalogo_estatus ORDER BY codigo");
            break;
        case 14:
            // TABLA Estatus
            $stmt = $dblink->prepare("SELECT codigo, descripcion from catalogo_familiar ORDER BY codigo");
            break;
    }
// 
try {
    // Ejectuar consulta
    $stmt->execute(); 
    $Catalogo = $stmt->fetchAll(PDO::FETCH_ASSOC); 
    // Enviar datos.
    echo json_encode($Catalogo);
} catch (\Throwable $th) {
    echo "Error: " . $th->getMessage();
}