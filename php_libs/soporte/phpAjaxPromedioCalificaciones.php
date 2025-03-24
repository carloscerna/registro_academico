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
$titulo_tabla = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexi�n a la base de datos

include($path_root."/registro_academico/includes/mainFunctions_conexion.php");

// Validar conexi�n con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acci�n
		switch ($_POST['accion']) {
			case 'BuscarEstudiante':
				// Declarar Variables y Crear consulta Query.
                    $codigo_all = $_REQUEST['todos'];
                    // Informaci�n Acad�mica.
                    $codigo_bachillerato = substr($codigo_all,0,2);
                    $codigo_grado = substr($codigo_all,2,2);
                    $codigo_seccion = substr($codigo_all,4,2);
                    $codigo_annlectivo = substr($codigo_all,6,2);
				// Armar la consulta para las diferencias opciones de actualiaci�n de notas.
			    $query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno, 
							  am.codigo_bach_o_ciclo, bach.nombre as nombre_modalidad, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado,
                              am.codigo_seccion, am.codigo_turno,  sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula
							  FROM alumno a 
							  INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
							  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
							  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
							  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
							  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
                              INNER JOIN turno tur ON tur.codigo = am.codigo_turno
							  WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_turno || am.codigo_ann_lectivo) = '".$codigo_all."'
								  ORDER BY apellido_alumno ASC";
				// Ejecutamos el Query.
				$consulta = $dblink -> query($query);

				if($consulta -> rowCount() != 0){
					$respuestaOK = true; 
					$num = 0;
					// convertimos el objeto
					while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
					{
						// Para cambiar el color de las filas.
						$num++;
						$codigo_alumno = $listado['id_alumno'];
						$codigo_matricula = $listado['codigo_matricula'];
						$codigo_nie = $listado['codigo_nie'];
                        $apellido_alumno = (trim($listado['apellido_alumno']));
                        $titulo_tabla = trim($listado['nombre_modalidad']) . ' - ' . trim($listado['nombre_grado']) . ' - ' . trim($listado['nombre_seccion']);
						
						$contenidoOK .= '<tr><td>'.$num
								.'<input type=hidden name=codigo_alumno value = '.$codigo_alumno.'>'
								.'<input type=hidden name=codigo_matricula value = '.$codigo_matricula.'>'
								.'<td>'.$codigo_nie
								.'<td>'.$apellido_alumno
								;							
					}	// Recorrido de la Tabla.
					$mensajeError = "Nómina Encontrada.";
				} // If que cuenta.
				else{
					$respuestaOK = true;
					$contenidoOK = '
						<tr id="sinDatos">
							<td colspan="4">No Hay Registros de este alumno...</td>
						</tr>
					'.$query;
					$mensajeError =  'Nómina no encontrada';
				}
			break;

			case 'CalcularPromedios':			
				// armar variables y consulta Query.
				$codigo_alumno[] = $_POST["codigo_alumno_"];
				$codigo_matricula[] = $_POST["codigo_matricula_"];
				$fila = $_POST["fila"];
				$codigo_modalidad = $_POST["codigo_modalidad"];
				$codigo_annlectivo = $_POST["codigo_annlectivo"];
				$codigo_grado = $_POST['codigo_grado'];

				// recorrer la array para extraer los datos.
				for($i=0;$i<=$fila-1;$i++){
					$codigo_a = $codigo_alumno[0][$i];
					$codigo_m = $codigo_matricula[0][$i];
					$codigo_asig = $codigo_asignatura[0][$i];

				// Actualizar nota final. PARA EDUCACIÓN BÁSICA.
					if ($codigo_modalidad >= '01' && $codigo_modalidad <= '05'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3)/3,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asig')
							                WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					if ($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) as promedio
							from nota WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asig')
							                WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
					if ($codigo_modalidad == '15' || $codigo_modalidad == '009'){
						$query_nota_final = "UPDATE nota SET
							nota_final = (select round((nota_p_p_1 + nota_p_p_2 + nota_p_p_3 + nota_p_p_4)/4,0) as promedio
								from nota WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asig')
							    	WHERE codigo_alumno = '$codigo_alumno' and codigo_matricula = '$codigo_matricula' and codigo_asignatura = '$codigo_asig'";
					// Ejectuamos query.
						$consulta_nota_final = $dblink -> query($query_nota_final);
					}
				}
					
				$respuestaOK = true;
				$contenidoOK = 'Registros Actualizados.';
				$mensajeError =  'Si Registro';
			break;
		
			default:
				$mensajeError = 'Esta acci�n no se encuentra disponible';
			break;
		}
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicaci�n';}
}
else{
	$mensajeError = 'No se puede establecer conexi�n con la base de datos';}

// Armamos array para convertir a JSON
$salidaJson = [
	"respuesta" => $respuestaOK,
	"mensaje" => $mensajeError,
	"contenido" => $contenidoOK,
	"titulo_tabla" => $titulo_tabla
];

echo json_encode($salidaJson);

function Promedio(){
    echo "Calcular sólo de concepto";
    
    $n1 = "E";
    $n2 = "";
    $n3 = "";
    $cadena = $n1.$n2.$n3;
    $cadena_e = substr_count($cadena,"E");
    $cadena_mb = substr_count($cadena,"MB");
    $cadena_b = substr_count($cadena,"B");
    $ni = "";
    
    echo "Cadena E: " . $cadena_e . "<br>";
    echo "Cadena MB: " . $cadena_mb . "<br>";
    echo "Cadena B: " . $cadena_b . "<br>";
    
    
    // evaluar con respecto a su evaluación conceptual.
    if($cadena_e >= 1 and $cadena_e <= 3){
        $ni = "E";
        echo "nota institucional es igual: " . $ni;
    }else if($cadena_mb >= 1 and $cadena_mb <= 3){
        $ni = "MB";
        echo "nota institucional es igual: " . $ni;
    }else if($cadena_b >= 1 and $cadena_b <= 3){
        $ni = "B";
        echo "nota institucional es igual: " . $ni;
    }else{
        $ni = "";
        echo "nota institucional está vacía: " . $ni;
    }
    
    if($ni == "E"){
        $ni = 10;
    }else if($ni == "MB"){
        $ni = 8;
    }else if($ni == "B"){
        $ni = 6;
    }else{
        $ni = 0;
    }
    echo "nota institucional está vacía: " . $ni;
}