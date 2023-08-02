<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
	include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Ar,ar Qieru cpm varoab�e-
	$ciclo = $_POST["elegido"];
	$query = "SELECT ga.codigo as codigo_grado, ga.nombre as nombre_grado from grado_ano ga ORDER BY codigo_grado";

// Ejecutamos el Query.
   $consulta = $dblink -> query($query);
// Inicializando el array
	$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
    while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
		{
			// Nombres de los campos de la tabla.
			   $codigo = trim($listado['codigo_grado']); $descripcion = $listado['nombre_grado'];
		  // EDUCACI�N INICIAL
		   if($ciclo == '01'){
			 if($codigo == 'I1' || $codigo == 'I2' || $codigo == 'I3')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;
			 }
		   }
		  // PARUVULARIA
		   if($ciclo == '02'){
			 if($codigo == '4P' || $codigo == '5P' || $codigo == '6P')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;
			 }
		   } 
		   // para primer ciclo
		   if($ciclo == '03'){
			 if($codigo == '01' || $codigo == '02' || $codigo == '03')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;			 
			 }
		   }
		   // para segundo ciclo
		   if($ciclo == '04'){
			 if($codigo == '04' || $codigo == '05' || $codigo == '06')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;			 
			 }
		   }
		   // para tercer ciclo
		   if($ciclo == '05'){
			 if($codigo == '07' || $codigo == '08' || $codigo == '09')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;			 
			 }
		   }
		   // para bachillerato general y t�cnico.
		   if($ciclo == '06' || $ciclo == '07'){
			 if($codigo == '10' || $codigo == '11')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;			 
			 }
		   }
		   // para primer ciclo
		   if($ciclo == '08' || $ciclo == '09'){
			 if($codigo == '12')
			 {
			   // Rellenando la array.
				   $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;			 
			 }
		   }
		   // para TERCER CICLO - MF-NOCTURNA
		   if($ciclo == '10'){
			 if($codigo == '07' || $codigo == '08' || $codigo == '09')
			 {
			   // Rellenando la array.
				  $datos[$fila_array]["codigo"] = $codigo;
				   $datos[$fila_array]["descripcion"] = ($descripcion);
				   $fila_array++;			 
			 }
		   }
				// EDUCACIÓN PARUVULARIA - ESTÁNDAR DE DESARROLLO
				if($ciclo == '13'){
					if($codigo == '4P' || $codigo == '5P' || $codigo == '6P')
					{
						// Rellenando la array.
							$datos[$fila_array]["codigo"] = $codigo;
							$datos[$fila_array]["descripcion"] = ($descripcion);
							$fila_array++;
					}
					} 		   
        }	// fin del while
// Enviando la matriz con Json.
echo json_encode($datos);
?>