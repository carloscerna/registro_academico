<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// 
 try {
	 // Verificar si los parámetros existen
	 if (!isset($_REQUEST["modalidad"]) || !isset($_REQUEST["annlectivo"])) {
		 echo json_encode(["error" => "Parámetros insuficientes"]);
		 exit;
	 }
 
	 $codigoBachillerato = $_REQUEST["modalidad"];
	 $codigoAnnLectivo = $_REQUEST["annlectivo"];
	 $codigoPerfil = $_SESSION['codigo_perfil'];
	 $codigoPersonal = trim($_SESSION['codigo_personal']);
 
	 // Determinar la consulta según el perfil
	 if ($codigoPerfil == '06') {	// Docente.
		 $query = "SELECT DISTINCT cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno,
				CONCAT(cd.codigo_grado,cd.codigo_seccion,cd.codigo_turno) AS codigo,
				CONCAT(grd.nombre || ' - ' || sec.nombre || ' - ' || tur.nombre) AS nombre
			FROM carga_docente cd
			INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
			INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
			WHERE cd.codigo_bachillerato = :codigoBachillerato 
			AND cd.codigo_ann_lectivo = :codigoAnnLectivo
			and cd.codigo_docente = :codigoPersonal
			ORDER BY cd.codigo_grado, cd.codigo_seccion";
	 } elseif ($codigoPerfil == '04' || $codigoPerfil == '05' || $codigoPerfil == '01') { // Registro Académico Básica y Media.
			$query = "SELECT 
				CONCAT(orgs.codigo_grado,orgs.codigo_seccion,orgs.codigo_turno) AS codigo,
				CONCAT(grd.nombre || ' - ' || sec.nombre || ' - ' || tur.nombre) AS nombre
					FROM organizacion_grados_secciones orgs
					INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
					INNER JOIN grado_ano grd ON grd.codigo = orgs.codigo_grado
					INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
					WHERE orgs.codigo_bachillerato = :codigoBachillerato 
					AND orgs.codigo_ann_lectivo = :codigoAnnLectivo
					ORDER BY orgs.codigo_grado, orgs.codigo_seccion";
	}else {
		echo json_encode(["error" => "Perfil no autorizado: " . $codigoPerfil . "Código Personal: " . $codigoPersonal]);
		exit;
	}
 
	 // Preparar la consulta con parámetros seguros
	 $stmt = $dblink->prepare($query);
	 $stmt->bindParam(':codigoBachillerato', $codigoBachillerato, PDO::PARAM_INT);
	 $stmt->bindParam(':codigoAnnLectivo', $codigoAnnLectivo, PDO::PARAM_INT);
	 
	 if ($codigoPerfil == '06') {
		 $stmt->bindParam(':codigoPersonal', $codigoPersonal, PDO::PARAM_INT);
	 }
 
	 $stmt->execute();
	 $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
	 // Enviar respuesta en formato JSON
	 echo json_encode($result);
 } catch (Exception $e) {
	 echo json_encode(["error" => "Error al obtener los datos: " . $e->getMessage()]);
 }