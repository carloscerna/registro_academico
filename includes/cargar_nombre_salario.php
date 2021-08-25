<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// armando el Query.
$query = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
						cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_d.porcentaje as porcentaje, cat_r.codigo, cat_r.descripcion as nombre_rubro,
						p.nombres, p.apellidos
						  FROM personal_salario ps
							  INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
							  INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
							  INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
							  INNER JOIN personal p ON p.id_personal = ps.codigo_personal and p.codigo_estatus = '01'
							  WHERE ps.codigo_rubro = '".
							  $_POST['codigo_rubro']."' ORDER BY p.nombres";
// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
         // Nombres de los campos de la tabla.
	 $codigo = $listado['codigo_personal']; $descripcion = $listado['nombres'] . ' ' . $listado['apellidos'];
	 // Rellenando la array.
         $datos[$fila_array]["codigo"] = $codigo;
	 $datos[$fila_array]["descripcion"] = ($descripcion);
	   $fila_array++;
        }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>