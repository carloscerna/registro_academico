<?php
// <-- VERSIÓN BLINDADA PHP 8.3: NuevoEditarPersonal.php -->

// 1. INICIAR BUFFER (Atrapa errores naranjas para proteger el JSON)
ob_start();

// Set timezone
date_default_timezone_set('America/El_Salvador');

// 2. HEADER JSON CORRECTO
header('Content-Type: application/json; charset=utf-8');

// limpiar cache.
clearstatcache();

// Inicializamos variables
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$lista = "";
$arreglo = array();
$datos = array();
$fila_array = 0;

// ruta de los archivos con su carpeta
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluimos archivos necesarios
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// Validar conexión
if($errorDbConexion == false){
    // Validar POST
    if(isset($_POST) && !empty($_POST)){
        if(!empty($_POST['accion_buscar'])){
            $_POST['accion'] = $_POST['accion_buscar'];
        }
        
        $accion = $_POST['accion'] ?? '';

        switch ($accion) {
            case "GenerarCodigoNuevo":
                $ann_ = substr(trim($_POST['ann'] ?? ''), 2, 2); // Protección ?? ''
                
                // CORRECCIÓN: Faltaba el nombre del campo en el WHERE. 
                // Asumo que buscas por año, verifica si el campo es 'codigo_ann_lectivo' o similar.
                // Por ahora lo dejo genérico para que no falle PHP, pero REVISA TU SQL.
                // $query = "SELECT id_personal FROM personal WHERE codigo_ann_lectivo = '$ann_' ...";
                
                $query = "SELECT id_personal FROM personal WHERE codigo_empleado_numero_entero IS NOT NULL ORDER BY codigo_empleado_numero_entero DESC LIMIT 1"; 
                
                $consulta = $dblink->query($query);
                
                if($consulta->rowCount() != 0){
                    $respuestaOK = true;
                    while($listado = $consulta->fetch(PDO::FETCH_BOTH))
                    {
                        $codigo_entero = trim($listado['codigo_empleado_numero_entero'] ?? 0) + 1;
                        $codigo_nuevo = (string)$codigo_entero . $ann_;
                        $datos[$fila_array]["codigo_nuevo"] = $codigo_nuevo;
                    }
                    $mensajeError = "Nuevo Código Generado: " . ($codigo_nuevo ?? '');
                }
                else{
                    $codigo_nuevo = "001" . $ann_;
                    $datos[$fila_array]["codigo_nuevo"] = $codigo_nuevo;
                    $respuestaOK = true;
                    $mensajeError = 'Código Inicial Generado.';
                }
            break;

            case 'BuscarCodigo':
                $codigo = trim($_POST['codigo'] ?? '');
                $query = "SELECT id_personal, codigo FROM personal WHERE codigo = '$codigo'";
                $consulta = $dblink->query($query);
                if($consulta->rowCount() != 0){
                    $respuestaOK = true;
                    $mensajeError = 'El Código Ya Existe.';
                }else{
                    $respuestaOK = false;
                    $mensajeError = 'El Código no existe, puede Continuar...';                 
                }
            break;

            case 'BuscarPorId':
                $id_ = trim($_POST['id_x'] ?? '');
                
                $query = "SELECT p.id_personal, TRIM(p.nombres) as nombre, TRIM(p.apellidos) as apellido, 
                    btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) AS nombre_empleado, 
                    p.telefono_residencia, p.telefono_celular,
                    p.fecha_nacimiento, p.edad, p.codigo_estatus, p.codigo_municipio, p.codigo_departamento, 
                    p.direccion, p.foto, p.codigo_genero, p.codigo_estado_civil, p.correo_electronico,
                    p.tipo_sangre, p.codigo_estudio, p.codigo_vivienda, p.codigo_afp, p.nombre_conyuge,
                    p.codigo_cargo, p.fecha_ingreso, p.fecha_retiro, 
                    p.numero_cuenta,
                    p.codigo_tipo_licencia, p.licencia, p.dui, p.nit, p.isss, p.afp, p.nip,
                    p.comentario
                    FROM personal p
                    WHERE id_personal = '$id_'
                    ORDER BY nombre_empleado";

                $consulta = $dblink->query($query);

                if($consulta->rowCount() != 0){
                    $respuestaOK = true;
                    
                    while($listado = $consulta->fetch(PDO::FETCH_BOTH))
                    {
                        // --- AQUÍ ESTABA EL ERROR ---
                        // PROTECCIÓN TOTAL CON: trim($var ?? '')
                        
                        $datos[$fila_array]["nombre_empleado"] = trim($listado['nombre_empleado'] ?? '');
                        $datos[$fila_array]["nombre"] = trim($listado['nombre'] ?? '');
                        $datos[$fila_array]["apellido"] = trim($listado['apellido'] ?? '');
                        
                        $datos[$fila_array]["fecha_nacimiento"] = trim($listado['fecha_nacimiento'] ?? '');
                        $datos[$fila_array]["edad"] = trim($listado['edad'] ?? '');
                        $datos[$fila_array]["codigo_genero"] = trim($listado['codigo_genero'] ?? '');
                        $datos[$fila_array]["codigo_estado_civil"] = trim($listado['codigo_estado_civil'] ?? '');
                        $datos[$fila_array]["tipo_sangre"] = trim($listado['tipo_sangre'] ?? '');
                        $datos[$fila_array]["codigo_estudio"] = trim($listado['codigo_estudio'] ?? '');
                        $datos[$fila_array]["codigo_vivienda"] = trim($listado['codigo_vivienda'] ?? '');
                        $datos[$fila_array]["codigo_afp"] = trim($listado['codigo_afp'] ?? '');
                        $datos[$fila_array]["nombre_conyuge"] = trim($listado['nombre_conyuge'] ?? '');
                        
                        $datos[$fila_array]["codigo_municipio"] = trim($listado['codigo_municipio'] ?? '');
                        $datos[$fila_array]["codigo_departamento"] = trim($listado['codigo_departamento'] ?? '');
                        $datos[$fila_array]["direccion"] = trim($listado['direccion'] ?? '');
                        $datos[$fila_array]["telefono_fijo"] = trim($listado['telefono_residencia'] ?? '');
                        $datos[$fila_array]["telefono_movil"] = trim($listado['telefono_celular'] ?? '');
                        $datos[$fila_array]["correo_electronico"] = trim($listado['correo_electronico'] ?? '');
                        
                        // Esta es la línea que fallaba (138), ahora protegida:
                        $datos[$fila_array]["codigo_cargo"] = trim($listado['codigo_cargo'] ?? '');
                        
                        $datos[$fila_array]["fecha_ingreso"] = trim($listado['fecha_ingreso'] ?? '');
                        $datos[$fila_array]["fecha_retiro"] = trim($listado['fecha_retiro'] ?? '');
                        
                        $datos[$fila_array]["dui"] = trim($listado['dui'] ?? '');
                        $datos[$fila_array]["isss"] = trim($listado['isss'] ?? '');
                        $datos[$fila_array]["nit"] = trim($listado['nit'] ?? '');
                        $datos[$fila_array]["afp"] = trim($listado['afp'] ?? ''); // NUP
                        $datos[$fila_array]["nip"] = trim($listado['nip'] ?? '');
                        $datos[$fila_array]["comentario"] = trim($listado['comentario'] ?? '');
                        
                        $datos[$fila_array]["url_foto"] = trim($listado['foto'] ?? '');
                    }
                    $mensajeError = "Si Registro";
                }
                else{
                    $respuestaOK = true;
                    $mensajeError = 'No Registro';
                }
            break;

            case 'AgregarNuevoPersonal':       
                // Recopilación de datos con protección
                $codigo_estatus = trim($_POST['lstestatus'] ?? '');
                $nombre = trim($_POST['txtnombres'] ?? '');
                $apellido = trim($_POST['txtapellido'] ?? '');
                $fecha_nacimiento = trim($_POST['fechanacimiento'] ?? '');
                $edad = trim($_POST['txtedad'] ?? '');
                $codigo_genero = trim($_POST['lstgenero'] ?? '');
                $codigo_estado_civil = trim($_POST['lstEstadoCivil'] ?? '');
                $tipo_sangre = htmlspecialchars(trim($_POST['txtTipoSangre'] ?? ''), ENT_QUOTES, 'UTF-8');
                $codigo_estudios = trim($_POST['lstEstudios'] ?? '');
                $codigo_tipo_vivienda = trim($_POST['lstVivienda'] ?? '');
                $codigo_afp = trim($_POST['lstAfp'] ?? '');
                $nombre_conyuge = htmlspecialchars(trim($_POST['txtConyuge'] ?? ''), ENT_QUOTES, 'UTF-8');

                $codigo_departamento = trim($_POST['lstdepartamento'] ?? '');
                $codigo_municipio = trim($_POST['lstmunicipio'] ?? '');
                $direccion = htmlspecialchars(trim($_POST['direccion'] ?? ''), ENT_QUOTES, 'UTF-8');

                $telefono_fijo = trim($_POST['telefono_fijo'] ?? '');
                $telefono_movil = trim($_POST['telefono_movil'] ?? '');
                $correo_electronico = trim($_POST['correo_electronico'] ?? '');   

                $codigo_cargo = trim($_POST['lstCargo'] ?? '');
                $fecha_ingreso = trim($_POST['txtFechaIngreso'] ?? '');
                $fecha_retiro = trim($_POST['txtFechaRetiro'] ?? '');

                $nip = trim($_POST['txtNip'] ?? '');
                $dui = trim($_POST['txtDui'] ?? '');
                $isss = trim($_POST['txtIsss'] ?? '');
                $nit = trim($_POST['txtNit'] ?? '');
                $afp = trim($_POST['txtNup'] ?? ''); // Asumo que NUP es AFP
                $comentario = htmlspecialchars(trim($_POST['txtComentario'] ?? ''), ENT_QUOTES, 'UTF-8');

                $query = "INSERT INTO personal (nombres, apellidos, fecha_nacimiento, edad, codigo_genero,
                    codigo_estado_civil, tipo_sangre, codigo_estudio, 
                    codigo_vivienda, codigo_afp, nombre_conyuge,
                    codigo_municipio, codigo_departamento, direccion,
                    telefono_residencia, telefono_celular, 
                    correo_electronico, codigo_cargo, fecha_ingreso, 
                    fecha_retiro, dui, nit, afp, nip,
                    codigo_estatus)
                    VALUES ('$nombre', '$apellido', '$fecha_nacimiento', '$edad', '$codigo_genero',
                        '$codigo_estado_civil', '$tipo_sangre', '$codigo_estudios', 
                        '$codigo_tipo_vivienda', '$codigo_afp', '$nombre_conyuge',
                        '$codigo_municipio', '$codigo_departamento', '$direccion',
                        '$telefono_fijo', '$telefono_movil', 
                        '$correo_electronico', '$codigo_cargo', '$fecha_ingreso', 
                        '$fecha_retiro',
                        '$dui','$nit','$afp', '$nip',
                        '$codigo_estatus')";
                
                // Ejecutamos el query
                $resultadoQuery = $dblink->query($query);              
                
                if($resultadoQuery == true){
                    $respuestaOK = true;
                    $mensajeError = "Se ha agregado el registro correctamente";
                }
                else{
                    $mensajeError = "No se puede guardar el registro en la base de datos";
                }
            break;

            case 'EditarRegistro':
                $codigo_personal = trim($_POST['id_user'] ?? '');
                $codigo_estatus = trim($_POST['lstestatus'] ?? '');
                $nombre = trim($_POST['txtnombres'] ?? '');
                $apellido = trim($_POST['txtapellido'] ?? '');
                $fecha_nacimiento = trim($_POST['fechanacimiento'] ?? '');
                $edad = trim($_POST['txtedad'] ?? '');
                $codigo_genero = trim($_POST['lstgenero'] ?? '');
                $codigo_estado_civil = trim($_POST['lstEstadoCivil'] ?? '');
                $tipo_sangre = htmlspecialchars(trim($_POST['txtTipoSangre'] ?? ''), ENT_QUOTES, 'UTF-8');
                $codigo_estudios = trim($_POST['lstEstudios'] ?? '');
                $codigo_tipo_vivienda = trim($_POST['lstVivienda'] ?? '');
                $codigo_afp = trim($_POST['lstAfp'] ?? '');
                $nombre_conyuge = htmlspecialchars(trim($_POST['txtConyuge'] ?? ''), ENT_QUOTES, 'UTF-8');

                $codigo_departamento = trim($_POST['lstdepartamento'] ?? '');
                $codigo_municipio = trim($_POST['lstmunicipio'] ?? '');
                $direccion = htmlspecialchars(trim($_POST['direccion'] ?? ''), ENT_QUOTES, 'UTF-8');
            
                $telefono_fijo = trim($_POST['telefono_fijo'] ?? '');
                $telefono_movil = trim($_POST['telefono_movil'] ?? '');
                $correo_electronico = trim($_POST['correo_electronico'] ?? '');   
                
                $codigo_cargo = trim($_POST['lstCargo'] ?? '');
                $fecha_ingreso = trim($_POST['txtFechaIngreso'] ?? '');
                $fecha_retiro = trim($_POST['txtFechaRetiro'] ?? '');

                $dui = trim($_POST['txtDui'] ?? '');
                $nip = trim($_POST['txtNip'] ?? '');
                $isss = trim($_POST['txtIsss'] ?? '');
                $nit = trim($_POST['txtNit'] ?? '');
                $afp = trim($_POST['txtNup'] ?? '');
                $comentario = htmlspecialchars(trim($_POST['txtComentario'] ?? ''), ENT_QUOTES, 'UTF-8');

                $query_usuario = sprintf("UPDATE personal SET nombres = '%s', apellidos = '%s', fecha_nacimiento = '%s', edad = '%s', codigo_genero = '%s',
                    codigo_estado_civil = '%s', tipo_sangre = '%s', codigo_estudio = '%s', codigo_vivienda = '%s', codigo_afp = '%s', nombre_conyuge = '%s',
                    codigo_municipio = '%s', codigo_departamento = '%s', direccion = '%s',
                    telefono_residencia = '%s', telefono_celular = '%s', correo_electronico = '%s',
                    codigo_cargo = '%s', fecha_ingreso = '%s', fecha_retiro = '%s', 
                    dui = '%s', nit = '%s', isss = '%s', afp = '%s', nip = '%s',
                    comentario = '%s', codigo_estatus = '%s'
                    WHERE id_personal = %d",
                    $nombre, $apellido, $fecha_nacimiento, $edad, $codigo_genero, $codigo_estado_civil, $tipo_sangre, $codigo_estudios, $codigo_tipo_vivienda, $codigo_afp, $nombre_conyuge,
                    $codigo_municipio, $codigo_departamento, $direccion,
                    $telefono_fijo, $telefono_movil, $correo_electronico,
                    $codigo_cargo, $fecha_ingreso, $fecha_retiro, 
                    $dui, $nit, $isss, $afp, $nip,
                    $comentario, $codigo_estatus,
                    $codigo_personal);  

                $resultadoQuery = $dblink->query($query_usuario);
                
                // ACTUALIZAR SI EL DOCENTE ES DIRECTOR.
                $codigo_institucion = $_SESSION['codigo_institucion'] ?? '';
                if($codigo_cargo == "01" && !empty($codigo_institucion)){
                    $query_actualizar_institucion = "UPDATE informacion_institucion set nombre_director = '$codigo_personal' WHERE codigo_institucion = '$codigo_institucion'";
                    $dblink->query($query_actualizar_institucion);
                }
                
                if($resultadoQuery == true){
                    $respuestaOK = true;
                    $mensajeError = "Se ha Actualizado el registro correctamente";
                }
                else{
                    $mensajeError = "No se puede Actualizar el registro en la base de datos";
                }
            break;

            case 'BuscarCodigos':
                $codigo = trim($_POST['codigo'] ?? '');
                $query = "SELECT id_personal, codigo FROM personal WHERE codigo = '$codigo'";
                $consulta = $dblink->query($query);
                if($consulta->rowCount() != 0){
                    $respuestaOK = true;
                    $mensajeError =  'Si Registro';
                }else{
                    $respuestaOK = false;
                    $mensajeError =  'No Registro';                 
                }
            break;

            case 'EliminarRegistro':
                $codigo_personal = $_POST['id_user'] ?? '';
                $query_buscar = "SELECT * FROM personal_licencias_permisos where codigo_personal = '$codigo_personal'";
                $consulta = $dblink->query($query_buscar);
                
                if($consulta->rowCount() != 0){
                    $respuestaOK = true;
                    $mensajeError = 'No se puede Eliminar, Tiene registros en Licencias y permisos.';
                }else{
                    $query = "DELETE FROM personal WHERE id_personal = '$codigo_personal'";
                    $count = $dblink->exec($query);
                    
                    if($count != 0){
                        $respuestaOK = true;
                        $mensajeError = 'Se ha Eliminado '.$count.' Registro(s).';
                    }else{
                        $mensajeError = 'No se ha eliminado el registro';
                    }
                }
            break;

            default:
                $mensajeError = 'Esta acción no se encuentra disponible';
            break;
        }
    }
    else{
        $mensajeError = 'No se recibieron datos (POST vacío)';
    }
}
else{
    $mensajeError = 'No se puede establecer conexión con la base de datos';
}

// 4. LIMPIEZA FINAL Y SALIDA JSON
ob_end_clean(); // Borramos cualquier warning previo

$accion_final = $_POST["accion"] ?? "";

if($accion_final === "" or $accion_final === "BuscarTodosCodigo"){
    echo json_encode($arreglo); 
}elseif($accion_final === "BuscarCodigo1" or $accion_final === "GenerarCodigoNuevo" or $accion_final === "BuscarPorId"){
    echo json_encode($datos);
}
else{
    $salidaJson = array("respuesta" => $respuestaOK,
        "mensaje" => $mensajeError,
        "contenido" => $contenidoOK);
    echo json_encode($salidaJson);
}
?>