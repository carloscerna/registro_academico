<?php
session_name('demoUI');
session_start();

// Asegurarse de que el usuario está logueado
if (!isset($_SESSION['userLogin']) || $_SESSION['userLogin'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autenticado.']);
    exit();
}

// Incluir la conexión a la base de datos
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
include($path_root . "/registro_academico/includes/mainFunctions_.php");

// Verificar la conexión a la base de datos
if (!isset($dblink) || !($dblink instanceof PDO)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error: Conexión a la base de datos no establecida.']);
    exit();
}

$action = $_POST['action'] ?? '';
$codigo_perfil = $_SESSION['codigo_perfil'] ?? null;
$codigo_personal = $_SESSION['codigo_personal'] ?? null; // Asegurarse de que el código del docente esté disponible

$response = ['success' => false, 'message' => 'Acción no válida.'];

try {
    switch ($action) {
        case 'getAcademicYears':
            // Lógica para decidir qué consulta usar para ann_lectivo
            if (in_array($codigo_perfil, ['01', '04', '05'])) {
                $sql = "SELECT codigo, nombre FROM ann_lectivo ORDER BY codigo DESC";
            } else {
                $sql = "SELECT codigo, nombre FROM ann_lectivo WHERE estatus = 't' ORDER BY codigo DESC";
            }

            $stmt = $dblink->prepare($sql);
            $stmt->execute();
            $years = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = ['success' => true, 'years' => $years];
            break;

        case 'getTeacherGradesAndSections':
            $codigo_ann_lectivo = $_POST['codigo_ann_lectivo'] ?? '';

            if (empty($codigo_personal) || empty($codigo_ann_lectivo)) {
                throw new Exception("Faltan parámetros para obtener grados/secciones del docente.");
            }

            // SQL para obtener GRADOS/SECCIONES/TURNOS únicos que un docente tiene asignados en un año lectivo
            // Asumo que `seccion` es un campo en carga_docente y no hay una tabla `seccion` aparte con `nombre_seccion`
            // Si hay una tabla `seccion` y necesitas su nombre, avísame.
            $sql = "SELECT DISTINCT
                        cd.codigo_grado || '-' || cd.codigo_seccion || '-' || cd.codigo_turno AS id_combinado,
                        grd.nombre || ' - ' || cd.codigo_seccion || ' - ' || tur.nombre AS nombre_combinado, grd.nombre, tur.nombre,
						cd.codigo_seccion
                    FROM carga_docente cd
                    INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
                    INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
                    WHERE cd.codigo_docente = :codigoPersonal
                    AND cd.codigo_ann_lectivo = :codigoAnnLectivo
                    ORDER BY grd.nombre, cd.codigo_seccion, tur.nombre";
            
            $stmt = $dblink->prepare($sql);
            $stmt->bindParam(':codigoPersonal', $codigo_personal, PDO::PARAM_STR);
            $stmt->bindParam(':codigoAnnLectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
            $stmt->execute();
            $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response = ['success' => true, 'grades' => $grades];
            break;

        case 'getTeacherIndicators':
            $codigo_ann_lectivo = $_POST['codigo_ann_lectivo'] ?? '';
            $codigo_grado_seccion_turno = $_POST['codigo_grado_seccion_turno'] ?? '';

            if (empty($codigo_personal) || empty($codigo_ann_lectivo) || empty($codigo_grado_seccion_turno)) {
                throw new Exception("Faltan parámetros para obtener los indicadores del docente.");
            }

            // Aquí irían las consultas para obtener los indicadores reales
            // Por ahora, devolveremos datos de ejemplo o un marcador de posición
            // YA TENEMOS LA CONSULTA DE CARGA_DOCENTE, PERO NECESITAMOS LA DE ESTUDIANTES Y NOTAS
            
            // Ejemplo de cómo descomponer codigo_grado_seccion_turno si lo necesitas
            list($codigo_grado, $codigo_seccion, $codigo_turno) = explode('-', $codigo_grado_seccion_turno);

            // TODO: AQUI NECESITAMOS LAS QUERIES REALES PARA LOS INDICADORES
            // Necesito la estructura de tus tablas de estudiantes, matrícula y notas.
            
            $indicators_data = [
                'total_students' => 0, // Por ahora 0, espera tu schema
                'assigned_grades' => 0, // Esto se podría calcular de carga_docente
                'approval_rate' => 0, // Por ahora 0, espera tu schema de notas
                'low_performance_students' => 0, // Por ahora 0, espera tu schema de notas
                'grades' => [], // Lista de grados asignados (quizás puedas reutilizar lo de getTeacherGradesAndSections)
                'performance_by_grade' => [] // Datos para el gráfico, espera tu schema de notas
            ];
            
            // Ejemplo de obtención de "assigned_grades" y "grades" para el año y docente
            $sql_assigned_grades = "SELECT COUNT(DISTINCT cd.codigo_grado || cd.codigo_seccion || cd.codigo_turno) AS count_grades
                                    FROM carga_docente cd
                                    WHERE cd.codigo_docente = :codigoPersonal AND cd.codigo_ann_lectivo = :codigoAnnLectivo";
            $stmt_assigned = $dblink->prepare($sql_assigned_grades);
            $stmt_assigned->bindParam(':codigoPersonal', $codigo_personal, PDO::PARAM_STR);
            $stmt_assigned->bindParam(':codigoAnnLectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
            $stmt_assigned->execute();
            $indicators_data['assigned_grades'] = $stmt_assigned->fetchColumn();

            // Reutilizar la lógica de getTeacherGradesAndSections para la lista 'grades'
            $sql_grades_list = "SELECT DISTINCT
                                    cd.codigo_grado || '-' || cd.codigo_seccion || '-' || cd.codigo_turno AS id_combinado,
                                    grd.nombre AS nombre_grado,
                                    cd.codigo_seccion AS nombre_seccion, -- Asumo codigo_seccion es el nombre, si hay tabla secciones, cambiar
                                    tur.nombre AS nombre_turno,
                                    (SELECT COUNT(m.codigo_alumno) FROM alumno_matricula m WHERE m.codigo_ann_lectivo = cd.codigo_ann_lectivo AND m.codigo_grado = cd.codigo_grado AND m.codigo_seccion = cd.codigo_seccion) AS estudiantes_count
                                FROM carga_docente cd
                                INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
                                INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
                                WHERE cd.codigo_docente = :codigoPersonal
                                AND cd.codigo_ann_lectivo = :codigoAnnLectivo
                                ORDER BY nombre_grado, nombre_seccion, nombre_turno";
            $stmt_grades_list = $dblink->prepare($sql_grades_list);
            $stmt_grades_list->bindParam(':codigoPersonal', $codigo_personal, PDO::PARAM_STR);
            $stmt_grades_list->bindParam(':codigoAnnLectivo', $codigo_ann_lectivo, PDO::PARAM_STR);
            $stmt_grades_list->execute();
            $indicators_data['grades'] = $stmt_grades_list->fetchAll(PDO::FETCH_ASSOC);

            $response = ['success' => true, 'data' => $indicators_data];
            break;

        default:
            // "Acción no válida" ya está configurada por defecto
            break;
    }
} catch (Exception $e) {
    error_log("Error en phpAjaxDashboard.inc.php: " . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()];
}

header('Content-Type: application/json;charset=utf-8');
echo json_encode($response);
exit();
?>