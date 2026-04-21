<?php

// 1. CONFIGURACIÓN DE RUTAS (Corrección para PHP 8)
$path_root = trim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''));

// Incluimos archivos necesarios
require_once($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

// URL PARA GUARDAR LAS IMAGENES
$url_ = "/registro_academico/img/Pn/";
$SistemaSiscarad = "C:/wamp64/www/siscarad/public/img/Pn/";
$url_respaldo_pn = "d:/registro_academico/img/Pn/";
$random = rand();
$respuestaOK = false;
$url_archivo = "";
$mensajeError = "No hay archivo o error en la carga.";
$contenidoOK = "";

// 2. CAPTURA DE SESIÓN SEGURA
$Id_ = $_SESSION["Id_A"] ?? null;
$codigo_institucion = $_SESSION["codigo_institucion"] ?? null;

if (!$Id_ || !$codigo_institucion) {
    die(json_encode(["respuesta" => false, "mensaje" => "Sesión inválida o expirada."]));
}

// 3. PROCESAMIENTO DEL ARCHIVO
if (is_array($_FILES) && count($_FILES) > 0 && isset($_FILES["file"])) {
    $allowed_types = [
        "image/pjpeg", "image/jpeg", "image/png", 
        "image/gif", "image/jpg", "application/pdf"
    ];

    if (in_array($_FILES["file"]["type"], $allowed_types)) {
        
        // Crear carpeta si no existe
        $target_path = $path_root . $url_ . $codigo_institucion . "/";
        if (!file_exists($target_path)) {
            mkdir($target_path, 0777, true);
        }

        $nombreArchivo = $random . "_" . basename($_FILES["file"]["name"]);
        $archivo_validar_pdf = ($_FILES['file']['type'] == 'application/pdf');

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_path . $nombreArchivo)) {
            $respuestaOK = true;
            $mensajeError = "Archivo cargado correctamente.";
            
            // Lógica de respaldo (opcional, asegurar que las rutas existan)
            if (file_exists($url_respaldo_pn . $codigo_institucion)) {
                copy($target_path . $nombreArchivo, $url_respaldo_pn . $codigo_institucion . "/" . $nombreArchivo);
            }

            if ($archivo_validar_pdf) {
                $contenidoOK = "pdf";
            } else {
                $mensajeError = "Cargado Archivo IMAGEN...";
                $contenidoOK = "img";
            }

            // 4. ACTUALIZAR BASE DE DATOS (Usando la conexión $dblink existente)
            $query = "UPDATE alumno SET ruta_pn = :nombre WHERE id_alumno = :id";
            $stmt = $dblink->prepare($query);
            $stmt->execute([
                ':nombre' => $nombreArchivo,
                ':id' => $Id_
            ]);

            $url_archivo = ".." . $url_ . $codigo_institucion . "/" . $nombreArchivo;
        } else {
            $mensajeError = "Error al mover el archivo al servidor.";
        }
    } else {
        $mensajeError = "Tipo de archivo no permitido.";
    }
}

// 5. SALIDA JSON
$salidaJson = [
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError,
    "url" => $url_archivo,
    "contenido" => $contenidoOK
];

header('Content-Type: application/json');
echo json_encode($salidaJson);