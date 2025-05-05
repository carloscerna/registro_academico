<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// armando el Query.
$pdo=$dblink;
try {
    // Verificar si se recibió el año lectivo
    if (!isset($_REQUEST['annlectivo'])) {
        echo json_encode(["error" => "Año lectivo no proporcionado"]);
        exit;
    }

    $annLectivo = $_REQUEST['annlectivo'];
    $codigoPerfil = $_SESSION['codigo_perfil'];
    $codigoPersonal = $_SESSION['codigo_personal'];
    
    // Definir la consulta según el perfil del usuario
    $query = "";
    if ($codigoPerfil == '06') {
        $query = "SELECT DISTINCT eg.codigo_ann_lectivo, eg.codigo_bachillerato as codigo, bach.nombre AS nombre
                  FROM encargado_grado eg
                  INNER JOIN bachillerato_ciclo bach ON bach.codigo = eg.codigo_bachillerato
                  WHERE eg.codigo_ann_lectivo = :annLectivo 
                  AND eg.codigo_docente = :codigoPersonal
                  ORDER BY eg.codigo_bachillerato";
    } elseif ($codigoPerfil == '04' || $codigoPerfil == '05') {
        $query = "SELECT DISTINCT orgpd.codigo_ann_lectivo, orgpd.codigo_bachillerato as codigo, bach.nombre AS nombre
                  FROM organizar_planta_docente_ciclos orgpd
                  INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgpd.codigo_bachillerato
                  WHERE orgpd.codigo_ann_lectivo = :annLectivo 
                  ORDER BY orgpd.codigo_bachillerato";
    } elseif ($codigoPerfil == '01') {
        $query = "SELECT organnciclo.codigo_ann_lectivo, organnciclo.codigo_bachillerato as codigo, bach.nombre AS nombre
                  FROM organizar_ann_lectivo_ciclos organnciclo
                  INNER JOIN bachillerato_ciclo bach ON bach.codigo = organnciclo.codigo_bachillerato
                  WHERE organnciclo.codigo_ann_lectivo = :annLectivo
                  ORDER BY organnciclo.ordenar";
    } else {
        echo json_encode(["error" => "Perfil no autorizado: " . $codigoPerfil . "Código Personal: " . $codigoPersonal]);
        exit;
    }

    // Preparar la consulta con PDO
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':annLectivo', $annLectivo, PDO::PARAM_INT);
    
    // Vincular parámetro si aplica
    if ($codigoPerfil == '06' || $codigoPerfil == '05') {
        $stmt->bindParam(':codigoPersonal', $codigoPersonal, PDO::PARAM_INT);
    }

    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar los datos en formato JSON
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener los datos: " . $e->getMessage()]);
}
/*
if(isset($_POST["annlectivo"])){
// VALIDAR SI ES UN DOCENTE
if($_SESSION['codigo_perfil'] == '06'){
	// ir a la tabla carga docente.
	 $query = "SELECT DISTINCT eg.codigo_ann_lectivo, eg.codigo_bachillerato, bach.nombre as nombre_bachillerato
			from encargado_grado eg
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = eg.codigo_bachillerato
			where eg.codigo_ann_lectivo = '".$_POST["annlectivo"]. "' and eg.codigo_docente = '".$_SESSION['codigo_personal']."' ORDER BY eg.codigo_bachillerato";
	}
	// VALIDAR CÓDIGO DEL PERFIL REGISTRO ACADÉMICO MEDIA o BASICA.
	if($_SESSION['codigo_perfil'] == '05' || $_SESSION['codigo_perfil'] == '06'){
	 $query = "SELECT DISTINCT orgpd.codigo_ann_lectivo, orgpd.codigo_bachillerato, bach.nombre as nombre_bachillerato
			from organizar_planta_docente_ciclos orgpd
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgpd.codigo_bachillerato
			where orgpd.codigo_ann_lectivo = '".$_POST["annlectivo"]. "' and orgpd.codigo_docente = '".$_SESSION['codigo_personal']."' ORDER BY orgpd.codigo_bachillerato";
	}
	if($_SESSION['codigo_perfil'] == '01'){
	 $query = "SELECT organnciclo.codigo_ann_lectivo, organnciclo.codigo_bachillerato, bach.nombre as nombre_bachillerato
			from organizar_ann_lectivo_ciclos organnciclo
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = organnciclo.codigo_bachillerato
			where organnciclo.codigo_ann_lectivo = '".$_POST["annlectivo"]."' ORDER BY organnciclo.ordenar";	
	}
	// Ejecutamos el Query.
	   $consulta = $dblink -> query($query);
	// Inicializando el array
	$datos=array(); $fila_array = 0;
	// Recorriendo la Tabla con PDO::
		  while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
		{
			 // Nombres de los campos de la tabla.
		 $codigo = trim($listado['codigo_bachillerato']); $descripcion = trim($listado['nombre_bachillerato']);
		 // Rellenando la array.
				$datos[$fila_array]["codigo"] = $codigo;
				$datos[$fila_array]["descripcion"] = ($descripcion);
				$fila_array++;
			}
	// Enviando la matriz con Json.
	echo json_encode($datos);
}else{
	$pdo=$dblink;
// Consulta para obtener las modalidades
$query = "SELECT id_bachillerato_ciclo, nombre, codigo, codigo_estatus FROM public.bachillerato_ciclo WHERE codigo_estatus = '01' ORDER BY id_bachillerato_ciclo ASC, ordenar ASC";
$result = $pdo->query($query);

// Preparar las opciones del select
$options = '';
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $options .= "<option value='" . $row['codigo'] . "'>" . $row['nombre'] . " (" . $row['codigo'] . ")</option>";
}

// Devolver las opciones como respuesta
echo $options;
}*/