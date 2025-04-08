<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
 include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Variables.
// Validate and sanitize input
$modalidad = filter_input(INPUT_POST, 'modalidad', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$annlectivo = filter_input(INPUT_POST, 'annlectivo', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$codigo_grado = filter_input(INPUT_POST, 'elegido', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
$codigo_grado = substr($codigo_grado, 0,2);

if (empty($modalidad) || empty($annlectivo)) {
    die("Invalid input: modalidad and annlectivo are required.");
}
/*
// Use prepared statements to prevent SQL injection
$query = "
    SELECT DISTINCT ON (aaa.codigo_asignatura) 
        trim(aaa.codigo_asignatura) as codigo_asignatura, 
        aaa.codigo_grado, 
        aaa.codigo_sirai, 
        asi.nombre as nombre_asignatura
    FROM 
        a_a_a_bach_o_ciclo aaa
    INNER JOIN 
        asignatura asi ON asi.codigo = aaa.codigo_asignatura
    WHERE 
        aaa.codigo_bach_o_ciclo = :modalidad
        AND aaa.codigo_ann_lectivo = :annlectivo
		AND aaa.codigo_grado = :codigo_grado
    ORDER BY 
        aaa.codigo_asignatura;
";
try {
    // Assuming $pdo is your PDO connection object
    $stmt = $dblink->prepare($query);
    $stmt->execute([
        ':modalidad' => $modalidad,
        ':annlectivo' => $annlectivo,
		':codigo_grado' => $codigo_grado,
    ]);
    // Fetch results
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Handle results (e.g., return JSON or display in HTML)
    echo json_encode($results);
} catch (PDOException $e) {
    // Handle any errors that occur during execution
    die("Database error: " . $e->getMessage());
}*/

// armando el Query.
// VALIDAR SI ES UN DOCENTE O ES EL ADMINISTRADOR.
/*
if($_SESSION['codigo_perfil'] == '06'){
// ir a la tabla carga docente.
$query = "SELECT DISTINCT cd.codigo_bachillerato, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_turno, cd.codigo_asignatura, grd.nombre as nombre_grado, asi.codigo, asi.nombre as nombre_asignatura 
				from carga_docente cd 
				INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato 
				INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo 
				INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado 
				INNER JOIN turno tur ON tur.codigo = cd.codigo_turno 
				INNER JOIN asignatura asi ON asi.codigo = cd.codigo_asignatura and asig.estatus = '1'
				where btrim(cd.codigo_grado || cd.codigo_seccion || cd.codigo_turno) = '".$_POST["elegido"]."' and cd.codigo_bachillerato = '".$_POST["modalidad"]."' and cd.codigo_ann_lectivo = '".$_POST["annlectivo"]. "' and cd.codigo_docente = '".$_SESSION['codigo_personal']."' ORDER BY asi.codigo";
}else{
$query = "SELECT DISTINCT ON (aaa.codigo_asignatura) aaa.codigo_asignatura, aaa.codigo_grado, aaa.codigo_sirai, asi.nombre as nombre_asignatura
				FROM a_a_a_bach_o_ciclo aaa
					INNER JOIN asignatura asi ON asi.codigo = aaa.codigo_asignatura
	 					WHERE aaa.codigo_bach_o_ciclo = '".$_POST["modalidad"].
	 						"' and aaa.codigo_ann_lectivo = '".$_POST["annlectivo"].
	 						"' ORDER BY aaa.codigo_asignatura";
}
							
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
			$codigo_asignatura = trim($listado['codigo_asignatura']); $nombre_asignatura = trim($listado['nombre_asignatura']);
	 // Rellenando la array.
		    $datos[$fila_array]["codigo"] = $codigo_asignatura;
			$datos[$fila_array]["descripcion"] = ($nombre_asignatura);
			  $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);
*/
if ($_SESSION['codigo_perfil'] == '06') {
    // Consulta segura con consulta preparada
    $query = "SELECT DISTINCT cd.codigo_bachillerato, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_turno, cd.codigo_asignatura,
                     grd.nombre AS nombre_grado, asi.codigo, asi.nombre AS nombre_asignatura
              FROM carga_docente cd
              INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
              INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
              INNER JOIN grado_ano grd ON grd.codigo = cd.codigo_grado
              INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
              INNER JOIN asignatura asi ON asi.codigo = cd.codigo_asignatura AND asi.estatus = '1'
              WHERE CONCAT(cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno) = :elegido
              AND cd.codigo_bachillerato = :modalidad
              AND cd.codigo_ann_lectivo = :annlectivo
              AND cd.codigo_docente = :codigo_docente
              ORDER BY asi.codigo";
} else {
    $query = "SELECT DISTINCT ON (aaa.codigo_asignatura) aaa.codigo_asignatura, aaa.codigo_grado, aaa.codigo_sirai, asi.nombre AS nombre_asignatura
              FROM a_a_a_bach_o_ciclo aaa
              INNER JOIN asignatura asi ON asi.codigo = aaa.codigo_asignatura
              WHERE aaa.codigo_bach_o_ciclo = :modalidad
              AND aaa.codigo_ann_lectivo = :annlectivo
              ORDER BY aaa.codigo_asignatura";
}

// **Consulta preparada con PDO**
$consulta = $dblink->prepare($query);

// **Vincular parámetros de manera segura**
//$consulta->bindParam(':elegido', $_POST["elegido"], PDO::PARAM_STR);
$consulta->bindParam(':modalidad', $_POST["modalidad"], PDO::PARAM_STR);
$consulta->bindParam(':annlectivo', $_POST["annlectivo"], PDO::PARAM_STR);
//$consulta->bindParam(':codigo_docente', $_SESSION['codigo_personal'], PDO::PARAM_STR);

$consulta->execute();

// **Inicializar el array**
$datos = [];
while ($listado = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $datos[] = [
        "codigo" => trim($listado['codigo_asignatura']),
        "descripcion" => trim($listado['nombre_asignatura'])
    ];
}

// **Envío de la respuesta en formato JSON**
echo json_encode($datos);