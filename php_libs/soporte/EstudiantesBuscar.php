<?php
// Configuración de cabeceras para JSON y UTF-8
header("Content-Type: application/json; charset=UTF-8");
clearstatcache();
sleep(0); // Opcional, solo si quieres simular latencia

// Ruta raiz
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Incluimos conexión (Asumiendo que $dblink es un objeto PDO)
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// Inicializamos respuesta por defecto
$response = [
    "respuesta" => false,
    "mensaje"   => "Error desconocido",
    "contenido" => "",
    "data"      => [] // DataTables espera "data" para arrays
];

// Validar conexión
if($errorDbConexion == false){
    
    // PHP 8: Uso de Null Coalescing Operator (??) para evitar "Undefined array key"
    // Aceptamos tanto POST como REQUEST por flexibilidad
    $accion = $_REQUEST['accion_buscar'] ?? $_REQUEST['accion'] ?? '';

    if(!empty($accion)){
        switch ($accion) {
            case 'BuscarTodos':
                // Consulta optimizada. Nota: CAST y btrim son funciones de PostgreSQL, asumo que usas Postgres.
                $query = "SELECT 
                            a.id_alumno, 
                            to_char(a.fecha_nacimiento,'dd/mm/yyyy') as fecha_nacimiento, 
                            a.edad, 
                            btrim(a.nombre_completo || ' ' || a.apellido_paterno || ' ' || a.apellido_materno) as nombre_completo_apellidos, 
                            a.codigo_nie, 
                            a.codigo_estatus, 
                            cat_est.descripcion as estatus,
                            am.nombres, 
                            am.dui, 
                            to_char(am.fecha_nacimiento,'dd/mm/yyyy') as fecha_nacimiento_encargado, 
                            cat_familiar.descripcion as nombre_familiar, 
                            am.direccion, 
                            am.telefono
                        FROM alumno a
                        INNER JOIN catalogo_estatus cat_est ON cat_est.codigo = a.codigo_estatus
                        INNER JOIN alumno_encargado am ON am.codigo_alumno = a.id_alumno
                        INNER JOIN catalogo_familiar cat_familiar ON cat_familiar.codigo = am.codigo_familiar
                        WHERE am.encargado = true
                        ORDER BY a.id_alumno DESC";
                
                try {
                    $consulta = $dblink->prepare($query);
                    $consulta->execute();
                    
                    // Obtener resultados
                    $resultado = $consulta->fetchAll(PDO::FETCH_ASSOC);

                    if($resultado){
                        $response["respuesta"] = true;
                        $response["mensaje"] = "Registros Encontrados";
                        // DataTables espera el array dentro de "data"
                        $response["data"] = $resultado; 
                    } else {
                        $response["respuesta"] = true;
                        $response["mensaje"] = "No hay registros";
                        $response["data"] = [];
                    }
                } catch (PDOException $e) {
                    $response["mensaje"] = "Error en BD: " . $e->getMessage();
                }
                break;

            case 'eliminarEstudiante': // CORREGIDO: Coincide con JS 'eliminarEstudiante'
                // PHP 8: Validación estricta de variables
                $id_user = $_REQUEST['id_estudiante'] ?? 0;

                if($id_user > 0){
                    try {
                        // SEGURIDAD: Sentencia Preparada (Evita SQL Injection)
                        $query = "DELETE FROM alumno WHERE id_alumno = :id";
                        $stmt = $dblink->prepare($query);
                        
                        if($stmt->execute([':id' => $id_user])){
                            $count = $stmt->rowCount();
                            if($count > 0){
                                $response["respuesta"] = true;
                                $response["mensaje"] = "Registro eliminado correctamente.";
                                $response["contenido"] = "Se eliminaron $count registros.";
                            } else {
                                $response["mensaje"] = "No se encontró el registro para eliminar.";
                            }
                        } else {
                            $response["mensaje"] = "Error al ejecutar la eliminación.";
                        }
                    } catch (PDOException $e) {
                        $response["mensaje"] = "Error crítico: " . $e->getMessage();
                    }
                } else {
                    $response["mensaje"] = "ID de estudiante no válido.";
                }
                break;
            
            default:
                $response["mensaje"] = "Acción '$accion' no disponible.";
                break;
        }
    } else {
        $response["mensaje"] = "No se definió ninguna acción.";
    }
} else {
    $response["mensaje"] = "No se puede establecer conexión con la base de datos.";
}

// Salida JSON limpia
echo json_encode($response);
exit;
?>