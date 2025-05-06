<?php
//session_name('demoUI');
//session_start();
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: text/html;charset=iso-8859-1");
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
	//
	try {
		if (!isset($_POST["action"])) {
			echo json_encode(["error" => "Acción no definida"]);
			exit;
		}
	
		$action = $_POST["action"];
	
		// 📌 Listar la nómina de estudiantes con sus calificaciones
		if ($action === "listarNomina") {
			if (!isset($_POST["modalidad"]) || !isset($_POST["asignatura"]) || !isset($_POST["periodo"])) {
				echo json_encode(["error" => "Parámetros insuficientes"]);
				exit;
			}
	
			if (!isset($_POST["modalidad"]) || !isset($_POST["gradoseccion"]) || !isset($_POST["annlectivo"]) || !isset($_POST["asignatura"]) || !isset($_POST["periodo"])) {
				echo json_encode(["error" => "Parámetros insuficientes"]);
				exit;
			}
		
			$codigo_all = $_POST["modalidad"] . substr($_POST["gradoseccion"], 0, 4) . $_POST["annlectivo"];
			$codigo_bachillerato = substr($codigo_all, 0, 2);
			$codigo_grado = substr($codigo_all, 2, 2);
			$codigo_seccion = substr($codigo_all, 4, 2);
			$codigo_annlectivo = substr($codigo_all, 6, 2);
			$codigo_asignatura = $_POST["asignatura"];
			$periodo = $_POST["periodo"];
		
			$query = "SELECT 
						n.id_notas,
						a.codigo_nie, 
						TRIM(CONCAT_WS(' ', a.apellido_paterno, a.apellido_materno, ', ', a.nombre_completo)) AS NombreEstudiante, 
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
					ORDER BY NombreEstudiante ASC";
		
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':codigo_bachillerato', $codigo_bachillerato, PDO::PARAM_INT);
			$stmt->bindParam(':codigo_grado', $codigo_grado, PDO::PARAM_INT);
			$stmt->bindParam(':codigo_seccion', $codigo_seccion, PDO::PARAM_INT);
			$stmt->bindParam(':codigo_annlectivo', $codigo_annlectivo, PDO::PARAM_INT);
			$stmt->bindParam(':codigo_asignatura', $codigo_asignatura, PDO::PARAM_INT);
			$stmt->execute();
		
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			echo json_encode($result);
		}
	
		// 📌 Actualizar calificaciones
		elseif ($action === "ActualizarCalificaciones") {
			if (!isset($_POST["idNota"]) || !isset($_POST["campo"]) || !isset($_POST["valor"])) {
				echo json_encode(["error" => "Parámetros insuficientes"]);
				exit;
			}
	
			$idNota = $_POST["idNota"];
			$campo = $_POST["campo"];
			$valor = $_POST["valor"];
	
			$query = "UPDATE nota SET $campo = :valor WHERE id_notas = :idNota";
			$stmt = $pdo->prepare($query);
			$stmt->bindParam(':valor', $valor, PDO::PARAM_STR);
			$stmt->bindParam(':idNota', $idNota, PDO::PARAM_INT);
			$stmt->execute();
	
			echo json_encode(["success" => "Calificación actualizada"]);
		}
	
	} catch (Exception $e) {
		echo json_encode(["error" => "Error: " . $e->getMessage()]);
	}