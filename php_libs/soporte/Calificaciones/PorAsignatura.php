<?php
//session_name('demoUI');
//session_start();
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header('Content-Type: application/json');
// Insertar y actualizar tabla de usuarios
sleep(0);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicaci�n";
$contenidoOK = "";
// ruta de los archivos con su carpeta
	$path_root = trim($_SERVER['DOCUMENT_ROOT']);
	include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
	$pdo=$dblink;

$response = [];

try {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'buscarNotas') {
        // Construcción de códigos desde el formulario
        $codigo_all = $_POST["modalidad"] . substr($_POST["gradoseccion"], 0, 4) . $_POST["annlectivo"];
        $codigo_bachillerato = substr($codigo_all, 0, 2);
		$codigo_grado = substr($codigo_all, 2, 2);
        $codigo_seccion = substr($codigo_all, 4, 2);
        $codigo_annlectivo = substr($codigo_all, 6, 2);
        $codigo_asignatura = trim($_POST["asignatura"]);
        $periodo = $_POST["periodo"];

        $sql = "
            SELECT 
                n.id_notas,
                a.codigo_nie, 
                a.id_alumno,
                TRIM(CONCAT_WS(' ', a.apellido_paterno, a.apellido_materno, ', ', a.nombre_completo)) AS nombre_estudiante, 
                asig.nombre AS nombre_asignatura, asig.codigo_cc,
                n.nota_a1_$periodo, 
                n.nota_a2_$periodo, 
                n.nota_a3_$periodo, 
                n.nota_r_$periodo, 
                n.nota_p_p_$periodo
            FROM alumno a  
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
            INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
            INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
            WHERE am.codigo_bach_o_ciclo = :codigo_bachillerato  
            AND am.codigo_grado = :codigo_grado 
            AND am.codigo_seccion = :codigo_seccion 
            AND am.codigo_ann_lectivo = :codigo_annlectivo
            AND n.codigo_asignatura = :codigo_asignatura 
            ORDER BY nombre_estudiante ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':codigo_bachillerato' => $codigo_bachillerato,
            ':codigo_grado' => $codigo_grado,
            ':codigo_seccion' => $codigo_seccion,
            ':codigo_annlectivo' => $codigo_annlectivo,
            ':codigo_asignatura' => $codigo_asignatura
        ]);

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id_notas' => $row['id_notas'],
                'codigo_nie' => trim($row['codigo_nie']),
                'nombre_completo' => $row['nombre_estudiante'],
                'nota_a1' => $row["nota_a1_$periodo"],
                'nota_a2' => $row["nota_a2_$periodo"],
                'nota_a3' => $row["nota_a3_$periodo"],
                'nota_r'  => $row["nota_r_$periodo"],
                'nota_pp' => $row["nota_p_p_$periodo"],
				'codigo_cc' => trim($row['codigo_cc']),
                'id_alumno' => $row["id_alumno"],
            ];
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    elseif ($accion === "guardarNotas") {
        $notas = $_POST["notas"] ?? [];
        $periodo = $_POST["periodo"];
        $codigo_modalidad = $_POST["codigo_modalidad"];
    
        // Obtener cantidad de períodos
        $query = "SELECT cantidad_periodos FROM catalogo_periodos WHERE codigo_modalidad = :codigo_modalidad LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":codigo_modalidad", $codigo_modalidad);
        $stmt->execute();
        $cantidad_periodos = $stmt->fetchColumn();
    
        foreach ($notas as $nota) {
            $id = $nota["id_notas"];
            $codigo_cc = $nota["codigo_cc"];
    
            $a1 = isset($nota["nota_a1"]) && is_numeric($nota["nota_a1"]) ? floatval($nota["nota_a1"]) : 0;
            $a2 = isset($nota["nota_a2"]) && is_numeric($nota["nota_a2"]) ? floatval($nota["nota_a2"]) : 0;
            $a3 = isset($nota["nota_a3"]) && is_numeric($nota["nota_a3"]) ? floatval($nota["nota_a3"]) : 0;
            $r  = isset($nota["nota_r"])  && is_numeric($nota["nota_r"])  ? floatval($nota["nota_r"])  : 0;
            $pp = isset($nota["nota_pp"]) && is_numeric($nota["nota_pp"]) ? floatval($nota["nota_pp"]) : 0;
            
            // Actualizar campos dinámicos según período
            $campos = [
                "nota_a1_$periodo"   => $a1,
                "nota_a2_$periodo"   => $a2,
                "nota_a3_$periodo"   => $a3,
                "nota_r_$periodo"    => $r,
                "nota_p_p_$periodo"  => $pp
            ];
    
            foreach ($campos as $campo => $valor) {
                $query = "UPDATE nota SET $campo = :valor WHERE id_notas = :id";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(":valor", $valor);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
            }
    
            // Calcular nota_final
            if ($codigo_cc === '04') {
                $nota_final = $pp; // Solo se usa nota_p_p_1
            } else {
                // Calcular promedio entre todos los períodos
                $sumQuery = "SELECT ";
                $sumaPartes = [];
                for ($i = 1; $i <= $cantidad_periodos; $i++) {
                    $sumaPartes[] = "COALESCE(nota_p_p_$i, 0)";
                }
                $sumQuery .= implode(" + ", $sumaPartes) . " AS suma FROM nota WHERE id_notas = :id";
                $stmt = $pdo->prepare($sumQuery);
                $stmt->bindParam(":id", $id, PDO::PARAM_INT);
                $stmt->execute();
                $suma = $stmt->fetchColumn();
                $nota_final = round($suma / $cantidad_periodos, 0);
            }
    
            $query = "UPDATE nota SET nota_final = :nota_final WHERE id_notas = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(":nota_final", $nota_final);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    
        echo json_encode(["success" => true, "mensaje" => "Notas actualizadas correctamente."]);
    }
    elseif ($accion === "buscarNotasRecuperacion") {
        // Construcción de códigos desde el formulario
        $codigo_all = $_POST["modalidad"] . substr($_POST["gradoseccion"], 0, 4) . $_POST["annlectivo"];
        $codigo_bachillerato = substr($codigo_all, 0, 2);
		$codigo_grado = substr($codigo_all, 2, 2);
        $codigo_seccion = substr($codigo_all, 4, 2);
        $codigo_annlectivo = substr($codigo_all, 6, 2);
        $codigo_asignatura = trim($_POST["asignatura"]);

        $sql = "
            SELECT 
                n.id_notas,
                a.codigo_nie, 
                TRIM(CONCAT_WS(' ', a.apellido_paterno, a.apellido_materno, ', ', a.nombre_completo)) AS nombre_estudiante, 
                asig.nombre AS nombre_asignatura, asig.codigo_cc,
                n.recuperacion, n.nota_recuperacion_2, n.nota_final
            FROM alumno a  
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
            INNER JOIN nota n ON n.codigo_alumno = a.id_alumno AND am.id_alumno_matricula = n.codigo_matricula
            INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
            WHERE am.codigo_bach_o_ciclo = :codigo_bachillerato  
            AND am.codigo_grado = :codigo_grado 
            AND am.codigo_seccion = :codigo_seccion 
            AND am.codigo_ann_lectivo = :codigo_annlectivo
            AND n.codigo_asignatura = :codigo_asignatura 
            ORDER BY nombre_estudiante ASC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':codigo_bachillerato' => $codigo_bachillerato,
            ':codigo_grado' => $codigo_grado,
            ':codigo_seccion' => $codigo_seccion,
            ':codigo_annlectivo' => $codigo_annlectivo,
            ':codigo_asignatura' => $codigo_asignatura
        ]);

        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id_notas' => $row['id_notas'],
                'codigo_nie' => trim($row['codigo_nie']),
                'nombre_completo' => $row['nombre_estudiante'],
                'nota_recuperacion' => $row["recuperacion"],
                'nota_recuperacion_2' => $row["nota_recuperacion_2"],
                'nota_final' => $row["nota_final"],
				'codigo_cc' => trim($row['codigo_cc'])
            ];
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    elseif($accion === "GuardarNotaRecuperacion"){
        try {
            $datos = $_POST['datos'];

            $pdo->beginTransaction(); // 🔒 Transacción

            $query = "
                UPDATE nota
                SET 
                    recuperacion = :recuperacion,
                    nota_recuperacion_2 = :nota_recuperacion_2,
                    nota_final = :nota_final
                WHERE id_notas = :id_notas
            ";
            $stmt = $pdo->prepare($query);

            foreach ($datos as $fila) {
                $stmt->execute([
                    ':recuperacion' => $fila['recuperacion'] ?? 0,
                    ':nota_recuperacion_2' => $fila['nota_recuperacion_2'] ?? 0,
                    ':nota_final' => $fila['nota_final'] ?? 0,
                    ':id_notas' => $fila['id_notas']
                ]);
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'mensaje' => 'Notas de recuperación actualizadas correctamente.'
            ]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al guardar: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    // NUEVO: Acción para buscar periodos habilitados (AJUSTADO PARA DESCRIPCIÓN Y FECHA)
    elseif ($accion === 'buscarPeriodosHabilitados') {
        $codigo_modalidad = $_POST["modalidad"];
        $codigo_annlectivo = $_POST["annlectivo"];
        $fecha_actual = date('Y-m-d'); // Obtener la fecha actual del servidor en formato YYYY-MM-DD
        $codigoPerfil = $_SESSION['codigo_perfil'] ?? '';
        // FILTER POR CODIGO PERFIL PARA DOCENTES, REGISTRO ACADEMICO Y ADMINISTRATIVO
        if ($codigoPerfil == "01" || $codigoPerfil == "04" || $codigoPerfil == "05") {
            $sql = "
                SELECT
                    pc.id_,
                    pc.codigo_periodo,
                    cp.descripcion,
                    c.calificacion_minima
                FROM public.periodo_calendario pc
                INNER JOIN public.catalogo_periodo cp ON pc.codigo_periodo = cp.id_
                INNER JOIN public.catalogo_periodos c ON pc.codigo_modalidad = c.codigo_modalidad
                WHERE pc.codigo_modalidad = :codigo_modalidad
                AND pc.codigo_annlectivo = :codigo_annlectivo
                AND pc.estatus = 't' 
                ORDER BY pc.codigo_periodo ASC
            ";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                ':codigo_modalidad' => $codigo_modalidad,
                ':codigo_annlectivo' => $codigo_annlectivo
        ]);
        } else if ($codigoPerfil == "06") {
            $sql = "
                SELECT
                    pc.id_,
                    pc.codigo_periodo,
                    cp.descripcion,
                    c.calificacion_minima
                FROM public.periodo_calendario pc
                INNER JOIN public.catalogo_periodo cp ON pc.codigo_periodo = cp.id_
                INNER JOIN public.catalogo_periodos c ON pc.codigo_modalidad = c.codigo_modalidad
                WHERE pc.codigo_modalidad = :codigo_modalidad
                AND pc.codigo_annlectivo = :codigo_annlectivo
                AND pc.estatus = 't' 
                AND pc.fecha_registro_academico >= :fecha_actual 
                ORDER BY pc.codigo_periodo ASC
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':codigo_modalidad' => $codigo_modalidad,
            ':codigo_annlectivo' => $codigo_annlectivo,
            ':fecha_actual' => $fecha_actual
        ]);
        }
        



        $data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = [
                'id_' => $row['id_'],
                'codigo_periodo' => trim($row['codigo_periodo']),
                'descripcion_periodo' => trim($row['descripcion']), // Incluir la descripción
                'calificacionMinima' => trim($row['calificacion_minima']) // Incluir la descripción
            ];
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    else{
        echo json_encode(['success' => false, 'mensaje' => 'Acción no reconocida.']);
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}