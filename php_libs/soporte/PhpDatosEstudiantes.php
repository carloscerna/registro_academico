<?php
// limpiar cache.
clearstatcache();
// Script para ejecutar AJAX
// cambiar a utf-8.
header("Content-Type: application/json;charset=utf-8");
sleep(0);

// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
    
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");

// Validar conexión con la base de datos
if($errorDbConexion == false){
	// Validamos que existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion_buscar'])){
			$_POST['accion'] = $_POST['accion_buscar'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'BuscarListaEstudiantes':
				// Declarar Variables y Crear consulta Query.
                $codigo_annlectivo = $_POST["lstannlectivo"];
                $codigo_modalidad = $_POST["lstmodalidad"];
                
                $grado_seccion_turno = $_POST["lstgradoseccion"];
                $codigo_grado = substr($grado_seccion_turno, 0, 2);
                $codigo_seccion = substr($grado_seccion_turno, 2, 2);
                $codigo_turno = substr($grado_seccion_turno, 4, 2);
						
				$query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) as apellido_alumno,
                            a.direccion_alumno, a.telefono_alumno, a.telefono_celular,
                            ae.telefono as telefono_encargado
							FROM alumno a
							INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
                            LEFT JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
							WHERE am.codigo_bach_o_ciclo = :modalidad AND am.codigo_grado = :grado AND am.codigo_seccion = :seccion AND am.codigo_ann_lectivo = :annlectivo AND am.codigo_turno = :turno
							ORDER BY apellido_alumno ASC";
				
                $stmt = $dblink->prepare($query);
                $stmt->bindParam(':modalidad', $codigo_modalidad);
                $stmt->bindParam(':grado', $codigo_grado);
                $stmt->bindParam(':seccion', $codigo_seccion);
                $stmt->bindParam(':annlectivo', $codigo_annlectivo);
                $stmt->bindParam(':turno', $codigo_turno);
                $stmt->execute();

				if($stmt->rowCount() != 0){
					$respuestaOK = true;
					$num = 0;
					while($listado = $stmt->fetch(PDO::FETCH_ASSOC))
					{
						$num++;
						$contenidoOK .= '<tr>
                            <td>' . $num . '</td>
                            <td data-id-alumno="' . $listado['id_alumno'] . '">' . $listado['codigo_nie'] . ' - ' . $listado['apellido_alumno'] . '</td>
                            <td><textarea name="direccion" class="form-control form-control-sm" rows="2">' . htmlspecialchars($listado['direccion_alumno']) . '</textarea></td>
                            <td><input type="text" name="telefono_encargado" class="form-control form-control-sm phone-mask" maxlength="9" value="' . htmlspecialchars($listado['telefono_encargado']) . '"></td>
                            <td><input type="text" name="telefono_alumno" class="form-control form-control-sm phone-mask" maxlength="9" value="' . htmlspecialchars($listado['telefono_alumno']) . '"></td>
                            <td><input type="text" name="telefono_celular" class="form-control form-control-sm phone-mask" maxlength="9" value="' . htmlspecialchars($listado['telefono_celular']) . '"></td>
                        </tr>';
					}
					$mensajeError = "Si Registro";
				}
				else{
					$respuestaOK = false;
					$mensajeError =  'No se encontraron registros para este grupo.';
				}
			break;

			case 'ActualizarDatosEstudiantes':
                $total_filas = $_POST['total_filas'];
                $codigos_alumnos = $_POST['codigo_alumno'];
                $direcciones = $_POST['direccion'];
                $telefonos_encargados = $_POST['telefono_encargado'];
                $telefonos_alumnos = $_POST['telefono_alumno'];
                $telefonos_celulares = $_POST['telefono_celular'];

                $dblink->beginTransaction();
                try {
                    for ($i = 0; $i < $total_filas; $i++) {
                        $id_alumno = $codigos_alumnos[$i];
                        
                        // 1. Actualizar tabla alumno
                        $query_alumno = "UPDATE alumno SET
                            direccion_alumno = :direccion,
                            telefono_alumno = :telefono_residencia,
                            telefono_celular = :telefono_celular
                            WHERE id_alumno = :id_alumno";
                        
                        $stmt_alumno = $dblink->prepare($query_alumno);
                        $stmt_alumno->bindParam(':direccion', $direcciones[$i]);
                        $stmt_alumno->bindParam(':telefono_residencia', $telefonos_alumnos[$i]);
                        $stmt_alumno->bindParam(':telefono_celular', $telefonos_celulares[$i]);
                        $stmt_alumno->bindParam(':id_alumno', $id_alumno);
                        $stmt_alumno->execute();

                        // 2. Actualizar teléfono del encargado principal
                        $query_encargado = "UPDATE alumno_encargado SET telefono = :telefono
                                            WHERE codigo_alumno = :id_alumno AND encargado = 't'";
                        
                        $stmt_encargado = $dblink->prepare($query_encargado);
                        $stmt_encargado->bindParam(':telefono', $telefonos_encargados[$i]);
                        $stmt_encargado->bindParam(':id_alumno', $id_alumno);
                        $stmt_encargado->execute();
                    }
                    
                    $dblink->commit();
                    $respuestaOK = true;
                    $mensajeError = 'Registros actualizados correctamente.';

                } catch (Exception $e) {
                    $dblink->rollBack();
                    $respuestaOK = false;
                    $mensajeError = 'Error al actualizar: ' . $e->getMessage();
                }
			break;
		
			default:
				$mensajeError = 'Esta acción no se encuentra disponible';
			break;
		}
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicación';}
}
else{
	$mensajeError = 'No se puede establecer conexión con la base de datos';}

// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>
