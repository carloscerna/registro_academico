<?php
session_name('demoUI');
//session_start();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: text/html; charset=utf-8");
// Insertar y actualizar tabla de usuarios
//sleep(1);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
$encabezado = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos

include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");
include($path_root."/registro_academico/includes/consultas.php");
// Validar conexión con la base de datos
if($errorDbConexion == false)
{
	// Validamos qe existan las variables post
	if(isset($_REQUEST) && !empty($_REQUEST))
	{
		if(!empty($_POST['accion']))
		{
				// Verificamos las variables de acción
			switch ($_POST['accion']) {
				case 'GraficoIndicadores':
                  // variables y consulta a la tabla.
    $codigo_ann_lectivo = $_REQUEST["codigo_ann_lectivo"];
    $db_link = $dblink;
    $codigo_all_indicadores = array(); $nombre_grado = array(); $nombre_seccion = array(); $nombre_modalidad = array(); $nombre_ann_lectivo = array();
    $codigo_grado_tabla = array(); $codigo_grado_comparar = array(); $nombre_modalidad_consolidad = array(); $nombre_turno = array(); $nombre_turno_consolidado = array();
// Inicializando el array
    $datos=array(); $fila_array = 0;
// buscar la consulta y la ejecuta.
  consultas(13,0,$codigo_ann_lectivo,'','','',$db_link,'');
//  captura de datos para información individual de grado y sección.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
        {
            $print_bachillerato = utf8_decode(''.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(''.trim($row['nombre_grado']));
            $print_seccion = utf8_decode(''.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
			// Variables
			$codigo_modalidad = trim($row[0]);
			$codigo_grado = trim($row['codigo_grado']);
    	    $codigo_seccion = trim($row['codigo_seccion']);
			$codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);
			$codigo_turno = trim($row['codigo_turno']);
			// Array
			$nombre_grado[] = utf8_decode($row['nombre_grado']);
			$nombre_seccion[] = $row['nombre_seccion'];
			$nombre_modalidad[] = $row['nombre_bachillerato'];
			$nombre_ann_lectivo[] = $row['nombre_ann_lectivo'];
			$nombre_turno[] = $row['nombre_turno'];
	    	// modalidad, grado, sección, año lectivo.
	    	$codigo_all_indicadores[] = $codigo_modalidad . $codigo_grado . $codigo_seccion . $codigo_ann_lectivo . $codigo_turno;
        }
		//print_r($codigo_all_indicadores);
		
	    // buscar la consulta y la ejecuta.
  consultas(14,0,$codigo_ann_lectivo,'','','',$db_link,'');
//  captura de datos para información individual de grado y sección.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
        {
	    	$codigo_grado = trim($row['codigo_grado']);
	    	$codigo_modalidad_consolidado = trim($row[1]);
			// arrays
			$nombre_modalidad_consolidado[] = trim($row['nombre_modalidad']);
	    	$nombre_grado_consolidado[] = utf8_decode($row['nombre_grado']);
			$nombre_ann_lectivo[] = $row['nombre_ann_lectivo'];
			$nombre_turno_consolidado[] = $row['nombre_turno'];
	    	// modalidad, grado y año lectivo.
	    	$codigo_indicadores[] = $codigo_modalidad_consolidado . $codigo_grado . $codigo_ann_lectivo;
        }
//  captura de datos para información individual de grado y sección.
		$query_turno = "SELECT * FROM turno ORDER BY codigo";
		// ejecutar la consulta.
		$result_turno = $db_link -> query($query_turno);
		while($row = $result_turno -> fetch(PDO::FETCH_BOTH))
		{
			$codigo_turno_bucle[] = trim($row['codigo']);
			$nombre_turno_bucle[] = trim($row['nombre']);
		}
// Evaluar si existen registros.
	if($result -> rowCount() != 0)
	{
		for($jh=0;$jh<=count($nombre_turno_bucle)-1;$jh++)
		{
			// Variables para los diferentes cálculos.
				$i=0; $m = 0; $f = 0; $suma = 0; $n_a = 0;
				$contador_tabla_grado = 0;
				$repitentem = 0; $repitentef = 0; $totalrepitente = 0;
				$sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
				$total_masculino_final = 0; $total_femenino_final = 0; $total_final = 0;
				$total_general_masculino = 0; $total_general_femenino = 0; $total_general = 0;
			// recorrer la tabla. SEGUN AÑO, MODALIDAD, GRADO, SECCIÓN	 
			for($j=0;$j<=count($codigo_all_indicadores)-1;$j++)
			{
				if($codigo_turno_bucle[$jh] == substr($codigo_all_indicadores[$j],8,2))
                {
					$i=$i+1; // Variables para el salto de página y el control de número de líneas.					
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(1,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino = $row_indicadores['total_masculino'];
						}
					// consultar y mostrar valores de matricula. m y f.
					consulta_indicadores(2,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_femenino = $row_indicadores['total_femenino'];
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(3,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
						$total_masculino_desercion = $row_indicadores['total_masculino_desercion'];
						}
					// consultar y mostrar valores de matricula. m y f. desercion
					consulta_indicadores(4,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_desercion = $row_indicadores['total_femenino_desercion'];
						// femenino + masculino desercion
						}		    
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(5,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_repitente = $row_indicadores['total_masculino_repitente'];
						}
					// consultar y mostrar valores de matricula. m y f. repitente
					consulta_indicadores(6,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_repitente = $row_indicadores['total_femenino_repitente'];
						// femenino + masculino desercion
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(7,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_masculino_sobreedad = $row_indicadores['total_masculino_sobreedad'];
						}
					// consultar y mostrar valores de matricula. m y f. sobreedad
					consulta_indicadores(8,0,$codigo_all_indicadores[$j],'','','',$db_link,'');
						while($row_indicadores = $result_indicadores -> fetch(PDO::FETCH_BOTH))
						{
							$total_femenino_sobreedad = $row_indicadores['total_femenino_sobreedad'];
						// femenino + masculino desercion
						}
					// calcular la matricula final.
						$total_masculino_final = $total_masculino - $total_masculino_desercion;
						$total_femenino_final = $total_femenino - $total_femenino_desercion;
						$total_final = $total_masculino_final + $total_femenino_final;
						$total_general_masculino = $total_general_masculino + $total_masculino_final;
						$total_general_femenino = $total_general_femenino + $total_femenino_final;
						$total_general = $total_general_masculino + $total_general_femenino;
					// variables.
						$print_grado = $nombre_grado[$j]; $print_seccion = $nombre_seccion[$j];
                        // iNFORMACIÓN DE LOS PRIMEROS DATOS.
					// Incrementar el valor del array.
					   $datos[$fila_array]["Matricula"] = "<tr><td>" . $i . "<td>" . $print_grado .  "<td>" . $print_seccion . "<td>" . $total_masculino_final . "<td>" . $total_femenino_final . "<td>" . $total_final . "</tr>";
					// Incrementar el valor del array.
						$fila_array++;
				} // if condiciones para imprimir dependiendo del codigo turno.
			}	// for codigo_all_indicadores
		}	// for codigo_turno
    } // condición de vacío en la tabla.    
else{
    // si no existen registros.
    $datos[$fila_array] = ("NO EXISTEN REGISTROS EN LA TABLA.");
}
				break;

			default:
				$mensajeError = 'Esta acción no se encuentra disponible';
			break;
			}
		}	// condición de la busqueda del nùmero de DUI.
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicación';
}
}
else{
	$mensajeError = 'No se puede establecer conexión con la base de datos';}
// Enviar datos.
echo json_encode($datos);
?>