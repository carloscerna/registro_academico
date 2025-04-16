<?php
// crud.php
// Archivo único que maneja las operaciones: listar, procesar (insertar/actualizar), obtener y eliminar.

// Conexión (se espera que "conexion.php" cree la variable $pdo)
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Indicamos que la respuesta es de tipo JSON (excepto en listar, pero la convertiremos a JSON también)
    header('Content-Type: application/json; charset=utf-8');
// Verificamos que se haya enviado el parámetro action
if (!isset($_REQUEST['action'])) {
    echo json_encode([
        "response" => false,
        "message"  => "No se especificó acción",
        "error"    => "Parámetro action faltante"
    ]);
    exit();
}

$action = $_REQUEST["action"];
 
// pasar la conexion
$pdo = $dblink;
switch ($action) {
  case 'listar':
    // Se consultan los registros y se arma un HTML para la tabla
    $query = $pdo->query("SELECT id_institucion, codigo_institucion, nombre_institucion, telefono_uno FROM informacion_institucion");
    $html = "";
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $html .= "<tr>
                    <td>{$row['codigo_institucion']}</td>
                    <td>{$row['nombre_institucion']}</td>
                    <td>{$row['telefono_uno']}</td>
                    <td>
                      <button class='btn btn-warning btn-sm' onclick='editarRegistro({$row["id_institucion"]})'>Editar</button>
                      <button class='btn btn-danger btn-sm' onclick='eliminarRegistro({$row["id_institucion"]})'>Eliminar</button>
                    </td>
                  </tr>";
    }

    echo json_encode([
        "response" => true,
        "message"  => "Listado obtenido correctamente",
        "error"    => "",
        "data"     => $html
    ]);
    break;

  case 'procesar':
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Recogemos todos los campos (existentes y nuevos)
        $nombre_director           = $_POST['nombre_director'] ?? null;
        $codigo_institucion        = $_POST['codigo_institucion'] ?? null;
        $nombre_institucion        = $_POST['nombre_institucion'] ?? null;
        $direccion_institucion     = $_POST['direccion_institucion'] ?? null;
        $codigo_municipio          = $_POST['codigo_municipio'] ?? null;
        $codigo_departamento       = $_POST['codigo_departamento'] ?? null;
        $telefono                  = $_POST['telefono'] ?? null;
        // Directorio donde se subirán las imágenes
        // Obtener la ruta física raíz
        $path_root = trim($_SERVER['DOCUMENT_ROOT']);  // Ejemplo: "C:/wamp64/www"
        // Define la carpeta dentro de tu proyecto donde deseas guardar las imágenes
        $uploadsDir = "/registro_academico/img/"; // Asegúrate de incluir la barra inicial
        $uploadDir = "/registro_academico/img/"; // Asegúrate de incluir la barra inicial
        // Ruta completa donde guardar la imagen
        $destino = $path_root . $uploadsDir;

        // Asegúrate de que el directorio exista
        if (!is_dir($destino)) {
            mkdir($destino, 0777, true);
        }
        function processFileUpload($fileField, $uploadDir, $path_root) {
            if (isset($_FILES[$fileField]) && $_FILES[$fileField]['name'] != "") {
                // Generamos un nombre único concatenando un ID y el nombre original
                $filename = uniqid() . "_" . basename($_FILES[$fileField]['name']);
                // Concatenamos la ruta física: DOCUMENT_ROOT + carpeta de uploads
                $targetFile = $path_root . $uploadDir . $filename;
                
                // Si el directorio no existe, lo creamos (recuerda que $uploadDir debe incluir la barra inicial)
                if (!is_dir($path_root . $uploadDir)) {
                    mkdir($path_root . $uploadDir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES[$fileField]["tmp_name"], $targetFile)) {
                    // Devuelve la ruta relativa sin la ruta física (por ejemplo, "uploads/123456_logo_uno.jpg")
                    // Si deseas no incluir el prefijo de carpeta del proyecto, debes ajustarlo.
                    //return trim($uploadDir, "/") . "/" . $filename;
                    return "/". $filename;
                }
            }
            return "";
        }
        // Procesar cada imagen
            $logo_uno = processFileUpload("logo_uno", $uploadDir, $path_root);
            $logo_dos = processFileUpload("logo_dos", $uploadDir, $path_root);
            $logo_tres = processFileUpload("logo_tres", $uploadDir, $path_root);
            $imagen_firma_director = processFileUpload("imagen_firma_director", $uploadDir, $path_root);
            $imagen_sello_director = processFileUpload("imagen_sello_director", $uploadDir, $path_root);

        /*
            // Supongamos que estás procesando el campo "imagen_firma_director"
            if(isset($_FILES["logo_uno"]) && $_FILES["logo_uno"]["name"] != ""){
                // Genera un nombre único (puedes agregar más lógica si lo necesitas)
                $fileName = uniqid() . "_" . basename($_FILES["logo_uno"]["name"]);
                // Ruta destino completa
                $targetPath = $destino . $fileName;
                
                if(move_uploaded_file($_FILES["logo_uno"]["tmp_name"], $targetPath)){
                    // Almacena solamente la parte relativa
                    $relativePath_logo_uno = "/" . $fileName;
                }
            } else {
                $relativePath_logo_uno = "";
            }
            // Supongamos que estás procesando el campo "imagen_firma_director"
            if(isset($_FILES["logo_dos"]) && $_FILES["logo_dos"]["name"] != ""){
                // Genera un nombre único (puedes agregar más lógica si lo necesitas)
                $fileName = uniqid() . "_" . basename($_FILES["logo_dos"]["name"]);
                // Ruta destino completa
                $targetPath = $destino . $fileName;
                
                if(move_uploaded_file($_FILES["logo_dos"]["tmp_name"], $targetPath)){
                    // Almacena solamente la parte relativa
                    $relativePath_logo_dos = "/" . $fileName;
                }
            } else {
                $relativePath_logo_dos = "";
            }
            // Supongamos que estás procesando el campo "imagen_firma_director"
            if(isset($_FILES["logo_tres"]) && $_FILES["logo_tres"]["name"] != ""){
                // Genera un nombre único (puedes agregar más lógica si lo necesitas)
                $fileName = uniqid() . "_" . basename($_FILES["logo_tres"]["name"]);
                // Ruta destino completa
                $targetPath = $destino . $fileName;
                
                if(move_uploaded_file($_FILES["logo_tres"]["tmp_name"], $targetPath)){
                    // Almacena solamente la parte relativa
                    $relativePath_logo_tres = "/" . $fileName;
                }
            } else {
                $relativePath_logo_tres = "";
            }
            // Supongamos que estás procesando el campo "imagen_firma_director"
            if(isset($_FILES["imagen_firma_director"]) && $_FILES["imagen_firma_director"]["name"] != ""){
                // Genera un nombre único (puedes agregar más lógica si lo necesitas)
                $fileName = uniqid() . "_" . basename($_FILES["imagen_firma_director"]["name"]);
                // Ruta destino completa
                $targetPath = $destino . $fileName;
                
                if(move_uploaded_file($_FILES["imagen_firma_director"]["tmp_name"], $targetPath)){
                    // Almacena solamente la parte relativa
                    $relativePath_firma = "/" . $fileName;
                }
            } else {
                $relativePath_firma = "";
            }
            // Supongamos que estás procesando el campo "imagen_firma_director"
            if(isset($_FILES["imagen_sello_director"]) && $_FILES["imagen_sello_director"]["name"] != ""){
                // Genera un nombre único (puedes agregar más lógica si lo necesitas)
                $fileName = uniqid() . "_" . basename($_FILES["imagen_sello_director"]["name"]);
                // Ruta destino completa
                $targetPath = $destino . $fileName;
                
                if(move_uploaded_file($_FILES["imagen_sello_director"]["tmp_name"], $targetPath)){
                    // Almacena solamente la parte relativa
                    $relativePath_sello = "/" . $fileName;
                }
            } else {
                $relativePath_sello = "";
            }*/
        // Procesar cada archivo
        $codigo_encargado_registro = $_POST['codigo_encargado_registro'] ?? null;
        $codigo_turno              = $_POST['codigo_turno'] ?? null;
        $codigo_sector             = $_POST['codigo_sector'] ?? null;
        $numero_acuerdo            = $_POST['numero_acuerdo'] ?? null;
        $nombre_base_datos         = $_POST['nombre_base_datos'] ?? null;

        try {
            if (isset($_POST["id_institucion"]) && !empty($_POST["id_institucion"])) {
                // Actualización
                $sql = "UPDATE informacion_institucion SET 
                            nombre_director = :nombre_director,
                            codigo_institucion = :codigo_institucion,
                            nombre_institucion = :nombre_institucion,
                            direccion_institucion = :direccion_institucion,
                            codigo_municipio = :codigo_municipio,
                            codigo_departamento = :codigo_departamento,
                            telefono_uno = :telefono,
                            logo_uno = :logo_uno,
                            logo_dos = :logo_dos,
                            logo_tres = :logo_tres,
                            codigo_encargado_registro = :codigo_encargado_registro,
                            codigo_turno = :codigo_turno,
                            codigo_sector = :codigo_sector,
                            numero_acuerdo = :numero_acuerdo,
                            nombre_base_datos = :nombre_base_datos,
                            imagen_firma_director = :imagen_firma_director,
                            imagen_sello_director = :imagen_sello_director
                        WHERE id_institucion = :id_institucion";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":id_institucion", $_POST["id_institucion"]);
                $operation = "actualizado";
            } else {
                // Inserción
                $sql = "INSERT INTO informacion_institucion 
                          (nombre_director, codigo_institucion, nombre_institucion, direccion_institucion, codigo_municipio, codigo_departamento, telefono_uno, logo_uno, logo_dos, logo_tres, codigo_encargado_registro, codigo_turno, codigo_sector, numero_acuerdo, nombre_base_datos, imagen_firma_director, imagen_sello_director)
                        VALUES 
                          (:nombre_director, :codigo_institucion, :nombre_institucion, :direccion_institucion, :codigo_municipio, :codigo_departamento, :telefono, :logo_uno, :logo_dos, :logo_tres, :codigo_encargado_registro, :codigo_turno, :codigo_sector, :numero_acuerdo, :nombre_base_datos, :imagen_firma_director, :imagen_sello_director)";
                $stmt = $pdo->prepare($sql);
                $operation = "guardado";
            }
            // Asignamos los parámetros
            $stmt->bindParam(":nombre_director", $nombre_director);
            $stmt->bindParam(":codigo_institucion", $codigo_institucion);
            $stmt->bindParam(":nombre_institucion", $nombre_institucion);
            $stmt->bindParam(":direccion_institucion", $direccion_institucion);
            $stmt->bindParam(":codigo_municipio", $codigo_municipio);
            $stmt->bindParam(":codigo_departamento", $codigo_departamento);
            $stmt->bindParam(":telefono", $telefono);
            //$stmt->bindParam(":logo_uno", $relativePath_logo_uno);
            //$stmt->bindParam(":logo_dos", $relativePath_logo_dos);
            //$stmt->bindParam(":logo_tres", $relativePath_logo_tres);
            $stmt->bindParam(":codigo_encargado_registro", $codigo_encargado_registro);
            $stmt->bindParam(":codigo_turno", $codigo_turno);
            $stmt->bindParam(":codigo_sector", $codigo_sector);
            $stmt->bindParam(":numero_acuerdo", $numero_acuerdo);
            $stmt->bindParam(":nombre_base_datos", $nombre_base_datos);
            $stmt->bindParam(":logo_uno", $logo_uno);
            $stmt->bindParam(":logo_dos", $logo_dos);
            $stmt->bindParam(":logo_tres", $logo_tres);
            $stmt->bindParam(":imagen_firma_director", $imagen_firma_director);
            $stmt->bindParam(":imagen_sello_director", $imagen_sello_director);

           // $stmt->bindParam(":imagen_firma_director", $relativePath_firma);
           // $stmt->bindParam(":imagen_sello_director", $relativePath_sello);

            if ($stmt->execute()) {
                echo json_encode([
                    "response" => true,
                    "message"  => "Registro $operation correctamente",
                    "error"    => ""
                ]);
            } else {
                echo json_encode([
                    "response" => false,
                    "message"  => "Error al procesar el registro",
                    "error"    => "Error en la consulta"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al procesar el registro",
                "error"    => $e->getMessage()
            ]);
        }
    }
    break;

  case 'obtener':
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST["id"];
        try {
            $sql = "SELECT * FROM informacion_institucion WHERE id_institucion = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            //
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            // Asegúrate de que el $uploadsDir concuerde con el que usaste al guardar (sin DOCUMENT_ROOT)
            $baseURL = $protocol . $host . "/registro_academico/img";              
              // Si existen imágenes, convierte la ruta relativa a absoluta
              if (!empty($data['logo_uno'])) {
                  $data['logo_uno'] = $baseURL . $data['logo_uno'];
              }
              if (!empty($data['logo_dos'])) {
                  $data['logo_dos'] = $baseURL . $data['logo_dos'];
              }
              if (!empty($data['logo_tres'])) {
                  $data['logo_tres'] = $baseURL . $data['logo_tres'];
              }
              
              if (!empty($data['imagen_sello_director'])) {
                  $data['imagen_sello_director'] = $baseURL . $data['imagen_sello_director'];
              }
              if (!empty($data['imagen_firma_director'])) {
                $data['imagen_firma_director'] = $baseURL . $data['imagen_firma_director'];
            }
            //
            if ($data) {
                echo json_encode([
                    "response" => true,
                    "message"  => "Registro obtenido",
                    "error"    => "",
                    "data"     => $data
                ]);
            } else {
                echo json_encode([
                    "response" => false,
                    "message"  => "Registro no encontrado",
                    "error"    => ""
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al obtener el registro",
                "error"    => $e->getMessage()
            ]);
        }
    }
    break;

  case 'eliminar':
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST["id"];
        try {
            $sql = "DELETE FROM informacion_institucion WHERE id_institucion = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":id", $id);
            if ($stmt->execute()) {
                echo json_encode([
                    "response" => true,
                    "message"  => "Registro eliminado correctamente",
                    "error"    => ""
                ]);
            } else {
                echo json_encode([
                    "response" => false,
                    "message"  => "Error al eliminar el registro",
                    "error"    => "Error en la consulta"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al eliminar el registro",
                "error"    => $e->getMessage()
            ]);
        }
    }
    break;

  default:
    echo json_encode([
        "response" => false,
        "message"  => "Acción no definida",
        "error"    => "Parámetro action desconocido"
    ]);
    break;
}