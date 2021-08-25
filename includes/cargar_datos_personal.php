<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// variables que traer el ID DEL ALUMNO.

// armando el Query. PARA LA TABLA ALUMNO.
$query = "SELECT id_personal, nombres, apellidos, direccion, dui, nip, nit, telefono_residencia, telefono_celular,
			edad, codigo_cargo, codigo_especialidad, codigo_estatus, codigo_zona_residencia,
			codigo_departamento, codigo_municipio, codigo_genero, fecha_nacimiento, codigo_estado_civil, foto
		FROM personal
			WHERE id_personal = ".
			$_POST['id_x'];
// armando el Query. PARA LA TABLA HISTORIAL ALUMNO.
$query_historial = "SELECT id_personal_bitacora, codigo_personal, fecha_ob, historial
	  FROM personal_historial
	  WHERE id_personal_bitacora = ".
	  $_POST['id_x']." ORDER BY fecha_ob";
// armando el Query. PARA LA TABLA TIPO DE CONTRATACIÓN..
$query_contratacion = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
						cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_r.codigo, cat_r.descripcion as nombre_rubro
						FROM personal_salario ps
						 INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
						 INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
						 INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
						 WHERE ps.codigo_personal = '".
						 $_POST['id_x']."' ORDER BY ps.codigo_personal";
// Ejecutamos el Query. PARA LA TABLA HISTORIAL ALUMNOS.
   $consulta_historial = $dblink -> query($query_historial);
// Ejecutamos el Query. PARA LA TABLA ALUMNO.
   $consulta = $dblink -> query($query);
// Ejecutamos el Query. PARA LA TABLA personal salario.
	$consulta_contratacion = $dblink -> query($query_contratacion);	
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
	 // campo de la foto.
	 $url_foto = trim($listado['foto']);
	 // campo del indice.
	 $id = trim($listado['id_personal']);
	 
         // Nombres de los campos de la tabla. primer tabs.
	 $nombres = (trim($listado['nombres']));
	 $apellidos = (trim($listado['apellidos']));
	 $dui = (trim($listado['dui']));
	 $nit = (trim($listado['nit']));
	 $nip = (trim($listado['nip']));
	 
	 $direccion = (trim($listado['direccion']));
	 $telefono_residencia = trim($listado['telefono_residencia']);
	 $telefono_celular = trim($listado['telefono_celular']);
	 	 
	 // Nombres de los campos de la tabla. segundo tabs.
	 $fecha_nacimiento = trim($listado['fecha_nacimiento']);
	 $edad = trim($listado['edad']);
	 $codigo_genero = trim($listado['codigo_genero']);
	 $codigo_cargo = trim($listado['codigo_cargo']);
	 $codigo_especialidad = trim($listado['codigo_especialidad']);
	 
	 $codigo_estado_civil = trim($listado['codigo_estado_civil']);
	 $codigo_departamento = trim($listado['codigo_departamento']);
	 $codigo_municipio = trim($listado['codigo_municipio']);
	 $codigo_zona_residencia = trim($listado['codigo_zona_residencia']);
         
	// Nombres de los campos de la tabla. tercer tab.
     $codigo_estatus = trim($listado['codigo_estatus']);
         
	 // Debera crerase en las tablas correspondientes los campos para poder rellenar dicha información.
	 // Rellenando la array. primer tabs-1
     $datos[$fila_array]["nombres"] = $nombres;
	 $datos[$fila_array]["apellidos"] = $apellidos;
	 $datos[$fila_array]["dui"] = $dui;
	 $datos[$fila_array]["nit"] = $nit;
	 $datos[$fila_array]["nip"] = $nip;
	 $datos[$fila_array]["direccion"] = $direccion;
	 $datos[$fila_array]["telefono_residencia"] = $telefono_residencia;
	 $datos[$fila_array]["telefono_celular"] = $telefono_celular;

	 $datos[$fila_array]["codigo_cargo"] = $codigo_cargo;
	 $datos[$fila_array]["codigo_especialidad"] = $codigo_especialidad;
	 
	 // Rellenando la array. segundo tabs-2
	 $datos[$fila_array]["fecha_nacimiento"] = $fecha_nacimiento;
	 $datos[$fila_array]["edad"] = $edad;
     $datos[$fila_array]["codigo_genero"] = $codigo_genero;
	 $datos[$fila_array]["codigo_estado_civil"] = $codigo_estado_civil;
	 $datos[$fila_array]["codigo_departamento"] = $codigo_departamento;
	 $datos[$fila_array]["codigo_municipio"] = $codigo_municipio;
	 $datos[$fila_array]["codigo_zona_residencia"] = $codigo_zona_residencia;
	 
     // Rellenado la array, tercer tabs-3. Documentos presentados.
     $datos[$fila_array]["codigo_estatus"] = $codigo_estatus;         
     // Rellenando la array, cuarto tabs-
	 // compo Id.
	 $datos[$fila_array]["id_personal"] = $id;
	 // foto url
         $datos[$fila_array]["url_foto"] = $url_foto;

	 // Incrementar el valor del array.
	   $fila_array++;
        }

// Recorriendo la Tabla con PDO:: HISTORIAL
    $num = 1;
	if($consulta_historial -> rowCount() != 0){		
            while($listadoHistorial = $consulta_historial -> fetch(PDO::FETCH_BOTH))
              {
                  // recopilar los valores de los campos.
                  $id_bitacora = trim($listadoHistorial['id_personal_bitacora']);

                  $fecha_ob = cambiaf_a_normal(trim($listadoHistorial['fecha_ob']));
                  $historial = utf8_encode(trim($listadoHistorial['historial']));
                  
                  // pasar a la matriz.
					$datos[$fila_array]["todos"] = '<tr><td>'.trim($num).'<td>'.$id_bitacora.'<td>'.$fecha_ob.
					'<td><textarea class=form-control rows=2 disabled>'.$historial.'</textarea></td>'
					.'<td class = centerTXT><a data-accion=editar class="btn btn-xs btn-primary" href='.$listadoHistorial['id_personal_bitacora'].'>Editar</a>'
					.'<td><a data-accion=eliminarHistorial class="btn btn-xs btn-warning" href='.$listadoHistorial['id_personal_bitacora'].'>Eliminar</a></tr>';
      
               // Incrementar el valor del array.
					$fila_array++; $num++;
              }
        }
	else{
            $datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
        }

// Recorriendo la Tabla con PDO:: PERSONAL SALARIO
    $num = 1;
	if($consulta_contratacion -> rowCount() != 0){		
		while($listadoPersonal = $consulta_contratacion -> fetch(PDO::FETCH_BOTH))
			  {
				  // recopilar los valores de los campos.
					  $id_personal_salario = trim($listadoPersonal['id_personal_salario']);
					  
					  $nombre_descuento = trim($listadoPersonal['nombre_descuento']);
					  $nombre_rubro = trim($listadoPersonal['nombre_rubro']);
					  $nombre_contratacion = trim($listadoPersonal['nombre_contratacion']);
					  $salario = trim($listadoPersonal['salario']);
									                    
				// pasar a la matriz.
					$datos[$fila_array]["todos_contratacion"] = '<tr><td>'.trim($num).'<td>'.$id_personal_salario.'<td>'.$nombre_rubro.'<td>'.$nombre_contratacion.
					  '<td>'.$nombre_descuento.'<td>'.$salario.'</td>'
					  .'<td class = centerTXT><a data-accion=editar class="btn btn-xs btn-primary" href='.$listadoPersonal['id_personal_salario'].'>Editar</a>'
					  .'<td><a data-accion=eliminarContratacion class="btn btn-xs btn-warning" href='.$listadoPersonal['id_personal_salario'].'>Eliminar</a></tr>';      
               // Incrementar el valor del array.
					$fila_array++; $num++;
              }
        }
	else{
            $datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
        }		
// Enviando la matriz con Json.
echo json_encode($datos);	
?>