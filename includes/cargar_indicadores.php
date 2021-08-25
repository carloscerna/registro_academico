<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// variables
    $ann_lectivo = $_REQUEST["ann_lectivo"];
// armando el Query. total masculino.
$query_masculino = "SELECT count(genero) as total_masculino FROM alumno_matricula am 
        INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
        WHERE btrim(codigo_ann_lectivo) = '$ann_lectivo' and a.genero = 'm'";
// armando el Query. total femenino.
$query_femenino = "SELECT count(genero) as total_femenino FROM alumno_matricula am 
        INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
        WHERE btrim(codigo_ann_lectivo) = '$ann_lectivo' and a.genero = 'f'";
// armando el Query. total masculino. RETIRADO
$query_masculino_retirado = "SELECT count(genero) as total_masculino_retirado FROM alumno_matricula am 
        INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
        WHERE btrim(codigo_ann_lectivo) = '$ann_lectivo' and a.genero = 'm' and am.retirado = 't'";
// armando el Query. total femenino. RETIRADO.
$query_femenino_retirado = "SELECT count(genero) as total_femenino_retirado FROM alumno_matricula am 
        INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
        WHERE btrim(codigo_ann_lectivo) = '$ann_lectivo' and a.genero = 'f' and am.retirado = 't'";
// amrando el Query, total de familias (Aproximadamente).
$query_listado_completo = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
    btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as solo_apellidos,
    translate(btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') as sin_tilde,
    ae.nombres, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo,
    bach.nombre as nombre_bachillerato,
    am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion, am.codigo_ann_lectivo,  
    am.retirado, am.id_alumno_matricula
    FROM alumno a
    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
    WHERE am.codigo_ann_lectivo = '$ann_lectivo' 
    ORDER BY solo_apellidos ASC, codigo_bach_o_ciclo, codigo_grado, codigo_seccion, codigo_turno";
// armando el Query. total masculino. Docentes
$query_masculino_docentes = "SELECT count(codigo_genero) as total_masculino FROM personal 
        WHERE codigo_estatus = '01' and codigo_genero = '01' and codigo_cargo = '03'";
// armando el Query. total femenino. Docentes
$query_femenino_docentes = "SELECT count(codigo_genero) as total_femenino FROM personal 
        WHERE codigo_estatus = '01' and codigo_genero = '02' and codigo_cargo = '03'";
// Ejecutamos el Query.
    $consulta_m = $dblink -> query($query_masculino);
    $consulta_f = $dblink -> query($query_femenino);
// Ejecutamos el Query.
    $consulta_m_r = $dblink -> query($query_masculino_retirado);
    $consulta_f_r = $dblink -> query($query_femenino_retirado);
// Inicializando el array
    $datos=array(); $fila_array = 0; $sin_tilde = array(); $solo_apellidos = array();
// Ejecutamos el Query. Total de Familias.
    $result_familias = $dblink -> query($query_listado_completo);
// Ejecutamos el Query. Docentes
    $consulta_m_d = $dblink -> query($query_masculino_docentes);
    $consulta_f_d = $dblink -> query($query_femenino_docentes);
// Recorriendo la Tabla con PDO:: MASCULINO
while($listado = $consulta_m -> fetch(PDO::FETCH_BOTH))
    {
    // Nombres de los campos de la tabla.
        $total_masculino = trim($listado['total_masculino']);
    // Rellenando la array.
        $datos[$fila_array]["total_masculino"] = $total_masculino;
    }
// Recorriendo la Tabla con PDO:: FEMENINO.
while($listado = $consulta_f -> fetch(PDO::FETCH_BOTH))
{
// Nombres de los campos de la tabla.
 $total_femenino = trim($listado['total_femenino']);
// Rellenando la array.
 $datos[$fila_array]["total_femenino"] = $total_femenino;
}
// Recorriendo la Tabla con PDO:: MASCULINO:: RETIRADO.
while($listado = $consulta_m_r -> fetch(PDO::FETCH_BOTH))
    {
    // Nombres de los campos de la tabla.
        $total_masculino_retirado = trim($listado['total_masculino_retirado']);
    // Rellenando la array.
        $datos[$fila_array]["total_masculino_retirado"] = $total_masculino_retirado;
    }
// Recorriendo la Tabla con PDO:: FEMENINO:: RETIRADO
while($listado = $consulta_f_r -> fetch(PDO::FETCH_BOTH))
{
// Nombres de los campos de la tabla.
 $total_femenino_retirado = trim($listado['total_femenino_retirado']);
// Rellenando la array.
 $datos[$fila_array]["total_femenino_retirado"] = $total_femenino_retirado;
}
//  Rellenano el array, através del While.
while($row_r = $result_familias -> fetch(PDO::FETCH_BOTH))
{
    $sin_tilde[] = trim($row_r['sin_tilde']);

}
// Eliminar valores repetidos del array.
    $solo_apellidos = array_values(array_unique($sin_tilde));
    $familias = count($solo_apellidos);  
        $datos[$fila_array]['total_familias'] = $familias;
// Recorriendo la Tabla con PDO:: MASCULINO DOCENTES
while($listado = $consulta_m_d -> fetch(PDO::FETCH_BOTH))
    {
    // Nombres de los campos de la tabla.
        $total_masculino = trim($listado['total_masculino']);
    // Rellenando la array.
        $datos[$fila_array]["total_masculino_docentes"] = $total_masculino;
    }
// Recorriendo la Tabla con PDO:: FEMENINO. DOCENTES
while($listado = $consulta_f_d -> fetch(PDO::FETCH_BOTH))
{
// Nombres de los campos de la tabla.
 $total_femenino = trim($listado['total_femenino']);
// Rellenando la array.
 $datos[$fila_array]["total_femenino_docentes"] = $total_femenino;
}
// Enviando la matriz con Json.
echo json_encode($datos);	
?>