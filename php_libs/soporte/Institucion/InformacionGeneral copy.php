<?php
// crud.php
// Archivo único que maneja las operaciones: listar, procesar (insertar/actualizar), obtener y eliminar.

// Conexión (se espera que "conexion.php" cree la variable $pdo)
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
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
$pdo = $dblink; // Asumiendo que $dblink es tu objeto PDO de la conexión

switch ($action) {
  case 'listar':
    // Se consultan los registros y se arma un HTML para la tabla
    $query = $pdo->query("SELECT id_institucion, codigo_institucion, nombre_institucion, telefono_uno FROM informacion_institucion");
    $html = "";
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $html .= "<tr>";
        $html .= "<td>" . htmlspecialchars($row['id_institucion']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['codigo_institucion']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['nombre_institucion']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['telefono_uno']) . "</td>";
        $html .= "<td class='text-center'>";
        $html .= "<button type='button' class='btn btn-warning btn-sm' onclick='editarRegistro(" . $row['id_institucion'] . ")' data-bs-toggle='modal' data-bs-target='#modalRegistro'><i class='fad fa-pencil'></i></button> ";
        $html .= "<button type='button' class='btn btn-danger btn-sm' onclick='eliminarRegistro(" . $row['id_institucion'] . ")'><i class='fad fa-trash'></i></button>";
        $html .= "</td>";
        $html .= "</tr>";
    }
    echo json_encode([
        "response" => true,
        "message"  => "Registros listados correctamente",
        "data"     => $html
    ]);
    break;

  case 'obtener':
    if (isset($_REQUEST['id'])) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM informacion_institucion WHERE id_institucion = ?");
            $stmt->execute([$_REQUEST['id']]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($data) {
                echo json_encode([
                    "response" => true,
                    "message"  => "Registro obtenido correctamente",
                    "data"     => $data
                ]);
            } else {
                echo json_encode([
                    "response" => false,
                    "message"  => "Registro no encontrado",
                    "error"    => "ID no válido"
                ]);
            }
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al obtener el registro",
                "error"    => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "response" => false,
            "message"  => "ID no especificado",
            "error"    => "Parámetro ID faltante"
        ]);
    }
    break;

  case 'procesar':
    $id_institucion = $_REQUEST['id_institucion'] ?? ''; // Vacío si es nuevo registro

    // Recuperar otros datos del formulario
    $codigo_institucion = $_REQUEST['codigo_institucion'];
    $nombre_institucion = $_REQUEST['nombre_institucion'];
    $direccion_institucion = $_REQUEST['direccion_institucion'];
    $codigo_municipio = $_REQUEST['codigo_municipio'];
    $codigo_departamento = $_REQUEST['codigo_departamento'];
    $telefono = $_REQUEST['telefono'];
    $nombre_director = $_REQUEST['nombre_director'];
    $codigo_encargado_registro = $_REQUEST['codigo_encargado_registro'];
    $codigo_turno = $_REQUEST['codigo_turno'];
    $codigo_sector = $_REQUEST['codigo_sector'];
    $numero_acuerdo = $_REQUEST['numero_acuerdo'];
    $nombre_base_datos = $_REQUEST['nombre_base_datos'];

    // Directorio de subida (asegúrate de que exista y tenga permisos de escritura)
    $path_uploads_dir = $path_root . "/registro_academico/img/";
    // Asegurarse de que el directorio exista
    if (!is_dir($path_uploads_dir)) {
        mkdir($path_uploads_dir, 0777, true);
    }

    // Variables para los nombres de los archivos que se guardarán en la base de datos
    $logo_uno_path = null;
    $logo_dos_path = null;
    $logo_tres_path = null;
    $firma_director_path = null;
    $sello_director_path = null;

    // ----- Lógica para manejar archivos al actualizar/insertar -----
    $current_files_db = [];
    if (!empty($id_institucion)) { // Si estamos editando un registro existente
        try {
            $stmt_get_current_files = $pdo->prepare("SELECT logo_uno, logo_dos, logo_tres, imagen_firma_director, imagen_sello_director FROM informacion_institucion WHERE id_institucion = ?");
            $stmt_get_current_files->execute([$id_institucion]);
            $current_files_db = $stmt_get_current_files->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al obtener archivos existentes para actualización",
                "error"    => $e->getMessage()
            ]);
            exit();
        }
    }

    // Función auxiliar para procesar cada archivo
    // $file_input_name: nombre del campo file en el formulario (ej. 'logo_uno')
    // $current_db_value: la ruta del archivo que ya está en la DB para este campo
    // $current_post_name: el nombre del campo POST enviado desde JS si el archivo existe pero no se cambió (ej. 'current_logo_uno_name')
    // $upload_dir: la ruta física del directorio de subidas
    function handleFileUpload($file_input_name, $current_db_value, $current_post_name, $upload_dir) {
        // 1. Se subió un nuevo archivo?
        if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK) {
            $filename = basename($_FILES[$file_input_name]['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $target_file)) {
                // Eliminar el archivo antiguo del disco si existía y ahora hay uno nuevo
                if ($current_db_value && file_exists($upload_dir . basename($current_db_value))) {
                   // unlink($upload_dir . basename($current_db_value));
                }
                //return "img/" . $filename; // Retorna la ruta relativa para la DB
                return $filename; // Retorna la ruta relativa para la DB
            } else {
                // Error al mover el nuevo archivo
                echo json_encode(["response" => false, "message" => "Error al subir archivo: " . $file_input_name, "error" => $_FILES[$file_input_name]['error']]);
                exit();
            }
        } 
        // 2. No se subió un nuevo archivo, pero estamos en modo edición.
        //    ¿El JavaScript nos dice que había un archivo existente y no se modificó/eliminó?
        else {
            // Verifica si el campo 'current_post_name' fue enviado y tiene un valor (no vacío)
            // Esto significa que en el JS, el span `current_logo_uno_name` estaba visible y contenía un nombre de archivo.
            // Es decir, el usuario no cambió ni eliminó el archivo existente.
            if (isset($_POST[$current_post_name]) && !empty($_POST[$current_post_name])) {
                // Retornar el valor que ya estaba en la base de datos para este campo.
                // No se necesita eliminar ni subir nada.
                return $current_db_value;
            }
            // 3. No se subió un nuevo archivo y el JS NO envió el current_post_name con valor,
            //    o el current_post_name se envió VACÍO (lo que indica que el usuario lo eliminó).
            else {
                // Si había un archivo antiguo en la DB y el usuario lo eliminó (o no se envió current_post_name con valor),
                // borrar el archivo físico y retornar null para la DB.
                if ($current_db_value && file_exists($upload_dir . basename($current_db_value))) {
                    //unlink($upload_dir . basename($current_db_value));
                }
                return null; // El archivo fue eliminado o nunca existió, se guarda NULL en la DB
            }
        }
    }

    // Procesar cada uno de los campos de archivo
    $logo_uno_path = handleFileUpload('logo_uno', $current_files_db['logo_uno'] ?? null, 'current_logo_uno_name', $path_uploads_dir);
    $logo_dos_path = handleFileUpload('logo_dos', $current_files_db['logo_dos'] ?? null, 'current_logo_dos_name', $path_uploads_dir);
    $logo_tres_path = handleFileUpload('logo_tres', $current_files_db['logo_tres'] ?? null, 'current_logo_tres_name', $path_uploads_dir);
    $firma_director_path = handleFileUpload('imagen_firma_director', $current_files_db['imagen_firma_director'] ?? null, 'current_imagen_firma_director_name', $path_uploads_dir);
    $sello_director_path = handleFileUpload('imagen_sello_director', $current_files_db['imagen_sello_director'] ?? null, 'current_imagen_sello_director_name', $path_uploads_dir);

    if (empty($id_institucion)) {
        // --- INSERCIÓN ---
        try {
            $sql = "INSERT INTO informacion_institucion (
                        codigo_institucion, nombre_institucion, direccion_institucion, 
                        codigo_municipio, codigo_departamento, telefono_uno, 
                        nombre_director, encargada_registro_academico, codigo_turno, 
                        codigo_sector, numero_acuerdo, dbname,
                        logo_uno, logo_dos, logo_tres, imagen_firma_director, imagen_sello_director
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $codigo_institucion, $nombre_institucion, $direccion_institucion,
                $codigo_municipio, $codigo_departamento, $telefono,
                $nombre_director, $codigo_encargado_registro, $codigo_turno,
                $codigo_sector, $numero_acuerdo, $nombre_base_datos,
                $logo_uno_path, $logo_dos_path, $logo_tres_path, $firma_director_path, $sello_director_path
            ]);

            echo json_encode([
                "response" => true,
                "message"  => "Registro insertado correctamente",
                "error"    => ""
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al insertar el registro",
                "error"    => $e->getMessage()
            ]);
        }
    } else {
        // --- ACTUALIZACIÓN ---
        try {
            $sql = "UPDATE informacion_institucion SET 
                        codigo_institucion = ?, nombre_institucion = ?, direccion_institucion = ?, 
                        codigo_municipio = ?, codigo_departamento = ?, telefono_uno = ?, 
                        nombre_director = ?, encargada_registro_academico = ?, codigo_turno = ?, 
                        codigo_sector = ?, numero_acuerdo = ?, dbname = ?,
                        logo_uno = ?, logo_dos = ?, logo_tres = ?, imagen_firma_director = ?, imagen_sello_director = ?
                    WHERE id_institucion = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $codigo_institucion, $nombre_institucion, $direccion_institucion,
                $codigo_municipio, $codigo_departamento, $telefono,
                $nombre_director, $codigo_encargado_registro, $codigo_turno,
                $codigo_sector, $numero_acuerdo, $nombre_base_datos,
                $logo_uno_path, $logo_dos_path, $logo_tres_path, $firma_director_path, $sello_director_path, // <--- usar las variables con los paths finales
                $id_institucion
            ]);

            echo json_encode([
                "response" => true,
                "message"  => "Registro actualizado correctamente",
                "error"    => ""
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al actualizar el registro",
                "error"    => $e->getMessage()
            ]);
        }
    }
    break;

  case 'eliminar':
    if (isset($_REQUEST['id'])) {
        try {
            // Opcional: Obtener los nombres de archivo para eliminarlos del disco antes de borrar el registro
            $stmt_get_files = $pdo->prepare("SELECT logo_uno, logo_dos, logo_tres, imagen_firma_director, imagen_sello_director FROM informacion_institucion WHERE id_institucion = ?");
            $stmt_get_files->execute([$_REQUEST['id']]);
            $files_to_delete = $stmt_get_files->fetch(PDO::FETCH_ASSOC);

            // Eliminar los archivos del disco
            $upload_dir = $path_root . "/registro_academico/uploads/";
            if ($files_to_delete) {
                foreach ($files_to_delete as $file_path) {
                    if ($file_path && file_exists($upload_dir . basename($file_path))) {
                        unlink($upload_dir . basename($file_path));
                    }
                }
            }

            $stmt = $pdo->prepare("DELETE FROM informacion_institucion WHERE id_institucion = ?");
            if ($stmt->execute([$_REQUEST['id']])) {
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

    case 'listarPersonal':
        try {
            $stmt = $pdo->query("SELECT id_personal AS id, CONCAT(nombres, ' ', apellidos) AS text FROM personal ORDER BY text");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["results" => $data]);
        } catch (PDOException $e) {
            echo json_encode([
                "response" => false,
                "message"  => "Error al listar el personal",
                "error"    => $e->getMessage()
            ]);
        }
        break;
    
  default:
    echo json_encode([
        "response" => false,
        "message"  => "Acción no definida",
        "error"    => "Acción inválida"
    ]);
    break;
}
?>