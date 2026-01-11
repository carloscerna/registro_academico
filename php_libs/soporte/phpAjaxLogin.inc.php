<?php
// 1. INICIAR BUFFER: Captura cualquier echo, warning o espacio en blanco accidental.
ob_start();

// Iniciar sesión
if (session_status() == PHP_SESSION_NONE) {
    session_name('demoUI');
    session_start();
}

// Configurar encabezado JSON
header("Content-Type: application/json;charset=utf-8");

$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación.";
$contenidoOK = "";

// Ruta raíz
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// 2. USO DE INCLUDE_ONCE: Evita errores fatales si ya se cargó el archivo.
// Asumimos que mainFunctions_.php conecta a la BD y define $dblink
include_once($path_root . "/registro_academico/includes/mainFunctions_.php");
include_once($path_root . "/registro_academico/includes/funciones.php");

// Verificamos conexión inicial
global $dblink;
global $errorDbConexion;

try {
    if ($errorDbConexion === false && isset($dblink)) {
        // Validar datos POST
        if (isset($_POST['accion_buscar']) && $_POST['accion_buscar'] === 'BuscarUser') {
            
            // 3. SANITIZACIÓN PARA PHP 8
            $nombre = filter_input(INPUT_POST, 'txtnombre', FILTER_SANITIZE_SPECIAL_CHARS);
            $password_ingresado = $_POST['txtpassword'] ?? ''; // No filtrar passwords, pueden tener caracteres especiales

            if (!empty($nombre) && !empty($password_ingresado)) {
                
                // Consulta optimizada para PostgreSQL
                $query = "SELECT u.nombre, u.id_usuario, u.base_de_datos, u.codigo_escuela, u.codigo_perfil, u.codigo_personal,
                                 TRIM(p.nombres) || ' ' || TRIM(p.apellidos) as nombre_personal,
                                 cat_perfil.descripcion as nombre_perfil, u.password as hashed_password
                          FROM usuarios u
                          INNER JOIN personal p ON p.id_personal = u.codigo_personal
                          INNER JOIN catalogo_perfil cat_perfil ON cat_perfil.codigo = u.codigo_perfil
                          WHERE u.nombre = :nombre LIMIT 1";

                $stmt = $dblink->prepare($query);
                $stmt->bindValue(':nombre', trim($nombre), PDO::PARAM_STR);
                $stmt->execute();
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    // 4. VERIFICACIÓN DE PASSWORD
                    // Importante: PostgreSQL devuelve espacios en blanco en campos CHAR, el trim es vital.
                    $stored_hash = trim($usuario['hashed_password']);
                    
                    if (password_verify($password_ingresado, $stored_hash)) {
                        
                        // LOGIN EXITOSO - Guardar sesión
                        $_SESSION['userLogin'] = true;
                        $_SESSION['userNombre'] = trim($usuario['nombre']);
                        $_SESSION['userID'] = $usuario['id_usuario'];
                        $_SESSION['dbname'] = trim($usuario['base_de_datos']);
                        $_SESSION['codigo_escuela'] = trim($usuario['codigo_escuela']);
                        $_SESSION['codigo_perfil'] = trim($usuario['codigo_perfil']);
                        $_SESSION['codigo_personal'] = trim($usuario['codigo_personal']);
                        $_SESSION['nombre_personal'] = trim($usuario['nombre_personal']);
                        $_SESSION['nombre_perfil'] = trim($usuario['nombre_perfil']);
                        $_SESSION['autentica'] = "SI";

                        $respuestaOK = true;
                        $contenidoOK = "Si";
                        $mensajeError = "Conexión Exitosa.";

                        // 5. MANEJO DE CAMBIO DE BASE DE DATOS (Punto Crítico)
                        /* Problema: No podemos hacer include() de nuevo si mainFunctions define funciones.
                           Solución ideal: Tener una función conectar_db($nombre_db).
                           Solución parche (Migración): Asumimos que la conexión sigue activa o reusamos $dblink si es la misma base.
                        */
                        
                        // Si necesitas cambiar de BD, lo ideal es cerrar y reabrir conexión manualmente aquí,
                        // pero por ahora usaremos la conexión existente para buscar la institución.
                        
                        if ($dblink) {
                            $query_institucion = "SELECT 
                                    inf.id_institucion, inf.codigo_departamento, inf.codigo_municipio, inf.codigo_institucion, 
                                    inf.nombre_institucion, inf.direccion_institucion, inf.telefono_uno, inf.numero_acuerdo,
                                    inf.se_extiende_la_presente, inf.dia_entrega, inf.logo_uno, inf.logo_dos, inf.imagen_firma, inf.imagen_sello,
                                    depa.descripcion as nombre_departamento,
                                    dis.descripcion as nombre_distrito, 
                                    mu.descripcion as nombre_municipio, 
                                    TRIM(p.nombres) || ' ' || TRIM(p.apellidos) as nombre_completo
                                FROM informacion_institucion inf
                                INNER JOIN personal p ON p.id_personal = CAST(inf.nombre_director AS INTEGER)
                                INNER JOIN catalogo_departamentos depa ON depa.codigo = inf.codigo_departamento
                                INNER JOIN catalogo_municipios mu ON mu.codigo = inf.codigo_municipio AND mu.codigo_departamento = inf.codigo_departamento
                                INNER JOIN catalogo_distritos dis ON dis.codigo = inf.codigo_distrito 
                                    AND dis.codigo_municipio = inf.codigo_municipio 
                                    AND dis.codigo_departamento = inf.codigo_departamento
                                WHERE inf.codigo_institucion = :codigo_institucion LIMIT 1";

                            $stmt_inst = $dblink->prepare($query_institucion);
                            $stmt_inst->bindValue(':codigo_institucion', $_SESSION['codigo_escuela'], PDO::PARAM_STR);
                            $stmt_inst->execute();
                            $institucionData = $stmt_inst->fetch(PDO::FETCH_ASSOC);

                            if ($institucionData) {
                                $_SESSION['institucion'] = trim($institucionData['nombre_institucion']);
                                $_SESSION['direccion'] = trim($institucionData['direccion_institucion']);
                                $_SESSION['codigo_institucion'] = trim($institucionData['codigo_institucion']);
                                $_SESSION['telefono'] = trim($institucionData['telefono_uno']);
                                $_SESSION['codigo_municipio'] = trim($institucionData['codigo_municipio']);
                                // Usamos utf8_encode solo si conversion es necesaria, sino directo
                                $_SESSION['nombre_municipio'] = trim($institucionData['nombre_municipio']); 
                                $_SESSION['codigo_departamento'] = trim($institucionData['codigo_departamento']);
                                $_SESSION['nombre_departamento'] = trim($institucionData['nombre_departamento']);
                                $_SESSION['nombre_distrito'] = trim($institucionData['nombre_distrito']);
                                $_SESSION['nombre_director'] = trim($institucionData['nombre_completo']);
                                $_SESSION['numero_acuerdo'] = trim($institucionData['numero_acuerdo']);
                                $_SESSION['se_extiende'] = trim($institucionData['se_extiende_la_presente']);
                                $_SESSION['dia_entrega'] = trim($institucionData['dia_entrega']);
                                $_SESSION['logo_uno'] = trim($institucionData['logo_uno']);
                                $_SESSION['logo_dos'] = trim($institucionData['logo_dos']);
                                $_SESSION['imagen_firma'] = trim($institucionData['imagen_firma']);
                                $_SESSION['imagen_sello'] = trim($institucionData['imagen_sello']);
                                
                                // Valores por defecto
                                $_SESSION["s_codigo_ann_lectivo"] = "01";
                                $_SESSION["s_codigo_modalidad"] = "01";
                                $_SESSION["s_codigo_grado_seccion_turno"] = "01";
                            } else {
                                // Login válido pero sin institución asignada
                                // Permitimos entrar pero avisando (o puedes bloquear aquí)
                            }
                        }
                    } else {
                        $mensajeError = 'Usuario o Contraseña incorrecta.';
                    }
                } else {
                    $mensajeError = 'Usuario no encontrado.';
                }
            } else {
                $mensajeError = 'Datos vacíos.';
            }
        }
    } else {
        $mensajeError = "Error al conectar con la base de datos.";
    }
} catch (Exception $e) {
    $mensajeError = "Error del servidor: " . $e->getMessage();
}

// Armar respuesta
$salidaJson = array(
    "respuesta" => $respuestaOK,
    "mensaje" => $mensajeError,
    "contenido" => $contenidoOK
);

// 6. LIMPIEZA FINAL Y ENVÍO
// Esto borra cualquier Warning de PHP que haya salido antes y deja solo el JSON
ob_end_clean(); 

echo json_encode($salidaJson);
exit;
?>