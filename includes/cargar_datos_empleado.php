<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
// armando el Query. PARA LA TABLA EMPLEADOS.
$query = "SELECT emp.id_empleado, emp.nombres, emp.apellidos, emp.direccion, emp.tel_residencia, emp.tel_celular, emp.cod_genero,
	  emp.cod_estado_civil, emp.cod_nivel_escolaridad, emp.email, emp.num_dui, emp.fecha_nacimiento, emp.edad,
	  emp.num_nit, emp.cod_depa, emp.cod_muni, emp.cod_afiliado, emp.numero_provisional, emp.num_lic_conducir, emp.cod_estatus, emp.url_foto,
	  emp.cod_motorista, emp.num_carnet_motorista
	  FROM empleados emp
	  WHERE emp.id_empleado = ".
	  $_POST['id_x'];
// armando el Query. PARA LA TABLA HISTORIAL.
$query_historial = "SELECT id_empleado_bitacora, codigo_empleado, fecha_ob, historial
	  FROM empleados_bitacora
	  WHERE codigo_empleado = ".
	  $_POST['id_x']." ORDER BY fecha_ob";;
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
   $consulta_historial = $dblink -> query($query_historial);
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
   $consulta = $dblink -> query($query);
// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
	 // campo de la foto.
	 $url_foto = trim($listado['url_foto']);
	 // campo del indice.
	 $id = trim($listado['id_empleado']);
	 
         // Nombres de los campos de la tabla. primer tabs.
	 $nombres = utf8_encode(trim($listado['nombres']));
	 $apellidos = utf8_encode(trim($listado['apellidos']));
	 $direccion = utf8_encode(trim($listado['direccion']));
	 $tel_res = trim($listado['tel_residencia']);
	 $tel_cel = trim($listado['tel_celular']);
	 $cod_genero = trim($listado['cod_genero']);
	 $cod_estado_civil = trim($listado['cod_estado_civil']);
	 $cod_nivel_escolaridad = trim($listado['cod_nivel_escolaridad']);
	 $email = trim($listado['email']);
	 
	 // Nombres de los campos de la tabla. segundo tabs.
	 $dui = trim($listado['num_dui']);
	 $f_nac = trim($listado['fecha_nacimiento']);
	 $edad = trim($listado['edad']);
	 $nit = trim($listado['num_nit']);
	 $cod_depa = trim($listado['cod_depa']);
	 $cod_muni = trim($listado['cod_muni']);
	 $cod_afiliado = trim($listado['cod_afiliado']);
	 $n_prov = trim($listado['numero_provisional']);
	 $n_lic_con = trim($listado['num_lic_conducir']);
         
         // Nombres de los campos de la tabla. tercer tab.
	 $estatus = trim($listado['cod_estatus']);
	 $cod_motorista = trim($listado['cod_motorista']);
	 $cod_carnet_motorista = trim($listado['num_carnet_motorista']);
         
	 // Rellenando la array. primer tabs-1
         $datos[$fila_array]["nombres"] = $nombres;
	 $datos[$fila_array]["apellidos"] = $apellidos;
	 $datos[$fila_array]["direccion"] = $direccion;
	 $datos[$fila_array]["tel_residencia"] = $tel_res;
	 $datos[$fila_array]["tel_celular"] = $tel_cel;
	 $datos[$fila_array]["cod_genero"] = $cod_genero;
	 $datos[$fila_array]["cod_estado_civil"] = $cod_estado_civil;
	 $datos[$fila_array]["cod_nivel_escolaridad"] = $cod_nivel_escolaridad;
	 $datos[$fila_array]["email"] = $email;
	 
	 // Rellenando la array. segundo tabs-2
         $datos[$fila_array]["num_dui"] = $dui;
	 $datos[$fila_array]["fecha_nacimiento"] = $f_nac;
	 $datos[$fila_array]["edad"] = $edad;
	 $datos[$fila_array]["num_nit"] = $nit;
	 $datos[$fila_array]["cod_depa"] = $cod_depa;
	 $datos[$fila_array]["cod_muni"] = $cod_muni;
	 $datos[$fila_array]["cod_afiliado"] = $cod_afiliado;
	 $datos[$fila_array]["num_prov"] = $n_prov;
	 $datos[$fila_array]["num_lic_conducir"] = $n_lic_con;
	 
         // Rellenando la array. tercer tabs-3
         $datos[$fila_array]["cod_estatus"] = $estatus;
	 $datos[$fila_array]["cod_motorista"] = $cod_motorista;
	 $datos[$fila_array]["cod_carnet_motorista"] = $cod_carnet_motorista;
         
	 // compo Id.
	 $datos[$fila_array]["id_empleado"] = $id;
	 // foto url
         $datos[$fila_array]["url_foto"] = $url_foto;

	 // Incrementar el valor del array.
	   $fila_array++;
        }
        
// Recorriendo la Tabla con PDO::
    $num = 1;
	if($consulta_historial -> rowCount() != 0){		
            while($listadoHistorial = $consulta_historial -> fetch(PDO::FETCH_BOTH))
              {
                  // recopilar los valores de los campos.
                  $id_bitacora = trim($listadoHistorial['id_empleado_bitacora']);
                  $cod_empleado = trim($listadoHistorial['codigo_empleado']);
                  $fecha_ob = cambiaf_a_normal(trim($listadoHistorial['fecha_ob']));
                  $historial = utf8_encode(trim($listadoHistorial['historial']));
                  
                  // pasar a la matriz.
		  $datos[$fila_array]["todos"] = '<tr><td>'.trim($num).'<td>'.$id_bitacora.'<td>'.$cod_empleado.'<td>'.$fecha_ob.
		    '<td><textarea class=form-control rows=2 disabled>'.$historial.'</textarea></td>'
		    .'<td class = centerTXT><a data-accion=editar class="btn btn-mini btn-primary" href='.$listadoHistorial['id_empleado_bitacora'].'>Editar</a>'
		    .'<td><a data-accion=eliminarHistorial class="btn btn-mini btn-warning" href='.$listadoHistorial['id_empleado_bitacora'].'>Eliminar</a></tr>';
      
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