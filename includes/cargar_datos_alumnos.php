<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
  include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
  include($path_root."/registro_academico/includes/funciones.php");
// variables que traer el ID DEL ALUMNO.

// armando el Query. PARA LA TABLA ALUMNO.
$query = "SELECT id_alumno, apellido_materno, apellido_paterno, nombre_completo, codigo_nie, direccion_alumno,
	    telefono_alumno, telefono_celular, codigo_departamento, codigo_municipio, partida_nacimiento, fecha_nacimiento, nacionalidad, distancia,
	    pn_numero, pn_folio, pn_tomo, pn_libro, codigo_transporte, medicamento, direccion_email, edad, certificado,
	    partida_nacimiento, tarjeta_vacunacion, genero, foto, estudio_parvularia, codigo_estado_civil,
	    codigo_estado_familiar, codigo_actividad_economica, codigo_apoyo_educativo, codigo_discapacidad, ruta_pn,
	    ruta_pn_vuelto, codigo_zona_residencia, tiene_hijos, cantidad_hijos, codigo_genero, codigo_estatus, dui, pasaporte, codigo_nacionalidad, retornado,
      posee_pn, presenta_pn, codigo_etnia, codigo_diagnostico, embarazada, codigo_tipo_vivienda, codigo_canton, codigo_caserio, servicio_energia, recoleccion_basura,
      codigo_abastecimiento, codigo_departamento_pn, codigo_municipio_pn, codigo_distrito_pn, codigo_distrito
	  FROM alumno
	  WHERE id_alumno = ".
	  $_POST['id_x'];
// armando el Query. PARA LA TABLA HISTORIAL ALUMNO.
$query_historial = "SELECT id_alumno_bitacora, codigo_alumno, fecha_ob, historial
	  FROM alumno_historial
	  WHERE codigo_alumno = ".
	  $_POST['id_x']." ORDER BY fecha_ob";
// armando el Query. PARA LA TABLA ALUMNO ENCARGADO.
$query_encargado = "SELECT id_alumno_encargado, codigo_alumno, nombres, lugar_trabajo, profesion_oficio, dui, telefono,
                    direccion, encargado, institucion_proviene, fecha_nacimiento, codigo_nacionalidad, codigo_familiar, codigo_zona, codigo_departamento, codigo_municipio, codigo_distrito
            FROM alumno_encargado WHERE codigo_alumno = ". $_POST['id_x']." order by id_alumno_encargado";
// Ejecutamos el Query. PARA LA TABLA HISTORIAL ALUMNOS.
   $consulta_historial = $dblink -> query($query_historial);
// Ejecutamos el Query. PARA LA TABLA ALUMNO.
   $consulta = $dblink -> query($query);
// Ejecutamos el Query. PARA LA TABLA ALUMNO ENCARGADO.
   $consulta_encargado = $dblink -> query($query_encargado);
// Ejecutamos el Query. para la tabla alumno matricula.
  // $consulta_historial_matricula = $dblink -> query($query_alumno_matricula);
// Inicializando el array
$datos=[]; $fila_array = 0;
$codigo_institucion = $_SESSION['codigo_institucion'];
// Recorriendo la Tabla con PDO::
      while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
   // campo de la foto.
    $url_foto = trim($listado['foto']);
    $url_pn = trim($listado['ruta_pn']);
    $archivo_origen = $path_root."/registro_academico/img/Pn/".$codigo_institucion."/".$url_pn;
    if(!file_exists($archivo_origen)){
      $url_pn = "foto_no_disponible.jpg";
    }else{
      $url_pn = $codigo_institucion."/".$url_pn;
    }
    
	 // campo del indice.
	 $id = trim($listado['id_alumno']);
	 
         // Nombres de los campos de la tabla. primer tabs.
	 $nombre_completo = (trim($listado['nombre_completo']));
	 $apellido_materno = (trim($listado['apellido_materno']));
	 $apellido_paterno = (trim($listado['apellido_paterno']));
	 $codigo_nie = (trim($listado['codigo_nie']));
	 $direccion_alumno = (trim($listado['direccion_alumno']));
	 $telefono_alumno = trim($listado['telefono_alumno']);
	 $telefono_celular = trim($listado['telefono_celular']);
	 $direccion_email = trim($listado['direccion_email']);
	 $medicamento = trim($listado['medicamento']);
	 $cantidad_hijos = trim($listado['cantidad_hijos']);

	 // Nombres de los campos de la tabla. segundo tabs.
	 $fecha_nacimiento = trim($listado['fecha_nacimiento']);
   $partida_nacimiento = trim($listado['partida_nacimiento']);
	 $edad = trim($listado['edad']);
   $dui = trim($listado['dui']);
   $pasaporte = trim($listado['pasaporte']);
   $codigo_nacionalidad = trim($listado['codigo_nacionalidad']);
   $retornado = trim($listado['retornado']);
// DATOS DE PARTIDA DE NACIMIENTO.
   $posee_pn = trim($listado['posee_pn']);
   $presenta_pn = trim($listado['presenta_pn']);
	 $pn_numero = trim($listado['pn_numero']);
	 $pn_folio = trim($listado['pn_folio']);
	 $pn_tomo = trim($listado['pn_tomo']);
	 $pn_libro = trim($listado['pn_libro']);
   $codigo_departamento_pn = trim($listado['codigo_departamento_pn']);
	 $codigo_municipio_pn = trim($listado['codigo_municipio_pn']);
   $codigo_distrito_pn = trim($listado['codigo_distrito_pn']);
	 $codigo_genero = trim($listado['codigo_genero']);
   $codigo_etnia = trim($listado['codigo_etnia']);
	 $codigo_tipo_discapacidad = trim($listado['codigo_discapacidad']);
// RESIDENCIA.
   $codigo_diagnostico = trim($listado['codigo_diagnostico']);
   $codigo_servicio_apoyo_educativo = trim($listado['codigo_apoyo_educativo']);
	 $codigo_estado_civil = trim($listado['codigo_estado_civil']);
	 $codigo_departamento = trim($listado['codigo_departamento']);
	 $codigo_municipio = trim($listado['codigo_municipio']);
   $codigo_distrito = trim($listado['codigo_distrito']);
	 $codigo_estado_familiar = trim($listado['codigo_estado_familiar']);
	 
	 $codigo_actividad_economica = trim($listado['codigo_actividad_economica']);
	 $codigo_zona_residencia = trim($listado['codigo_zona_residencia']);
//           
  $embarazada = trim($listado['embarazada']);
  $codigo_tipo_vivienda = trim($listado['codigo_tipo_vivienda']);
  $codigo_canton = trim($listado['codigo_canton']);
  $codigo_caserio = trim($listado['codigo_caserio']);
  $servicio_energia = trim($listado['servicio_energia']);
  $recoleccion_basura = trim($listado['recoleccion_basura']);
  $codigo_abastecimiento = trim($listado['codigo_abastecimiento']);
         // Nombres de los campos de la tabla. tercer tab.
            $codigo_estatus = trim($listado['codigo_estatus']);
         
	 // Debera crerase en las tablas correspondientes los campos para poder rellenar dicha informaci�n.

         
	 // Rellenando la array. primer tabs-1
    $datos[$fila_array]["nombre_completo"] = $nombre_completo;
	 $datos[$fila_array]["apellido_materno"] = $apellido_materno;
	 $datos[$fila_array]["apellido_paterno"] = $apellido_paterno;
	 $datos[$fila_array]["direccion_alumno"] = $direccion_alumno;
	 $datos[$fila_array]["telefono_residencia"] = $telefono_alumno;
	 $datos[$fila_array]["telefono_celular"] = $telefono_celular;
	 $datos[$fila_array]["codigo_nie"] = $codigo_nie;
	 $datos[$fila_array]["medicamento"] = $medicamento;

	 // Rellenando la array. segundo tabs-2
	 $datos[$fila_array]["fecha_nacimiento"] = $fecha_nacimiento;
   $datos[$fila_array]["partida_nacimiento"] = $partida_nacimiento;
	 $datos[$fila_array]["edad"] = $edad;
   $datos[$fila_array]["dui"] = $dui;
   $datos[$fila_array]["pasaporte"] = $pasaporte;
   $datos[$fila_array]["codigo_nacionalidad"] = $codigo_nacionalidad;
   $datos[$fila_array]["retornado"] = $retornado;
   $datos[$fila_array]["posee_pn"] = $posee_pn;
   $datos[$fila_array]["presenta_pn"] = $presenta_pn;
   $datos[$fila_array]["codigo_departamento_pn"] = $codigo_departamento_pn;
   $datos[$fila_array]["codigo_municipio_pn"] = $codigo_municipio_pn;
   $datos[$fila_array]["codigo_distrito_pn"] = $codigo_distrito_pn;


	 $datos[$fila_array]["pn_numero"] = $pn_numero;
	 $datos[$fila_array]["pn_folio"] = $pn_folio;
	 $datos[$fila_array]["pn_tomo"] = $pn_tomo;
	 $datos[$fila_array]["pn_libro"] = $pn_libro;

  $datos[$fila_array]["codigo_genero"] = $codigo_genero;
  $datos[$fila_array]["codigo_etnia"] = $codigo_etnia;

  $datos[$fila_array]["codigo_diagnostico"] = $codigo_diagnostico;
  $datos[$fila_array]["codigo_servicio_apoyo_educativo"] = $codigo_servicio_apoyo_educativo;

	 $datos[$fila_array]["codigo_estado_civil"] = $codigo_estado_civil;
	 $datos[$fila_array]["codigo_departamento"] = $codigo_departamento;
	 $datos[$fila_array]["codigo_municipio"] = $codigo_municipio;
   $datos[$fila_array]["codigo_distrito"] = $codigo_distrito;
	 $datos[$fila_array]["codigo_estado_familiar"] = $codigo_estado_familiar;
	 $datos[$fila_array]["codigo_actividad_economica"] = $codigo_actividad_economica;
	 $datos[$fila_array]["codigo_tipo_discapacidad"] = $codigo_tipo_discapacidad;

   $datos[$fila_array]["direccion_email"] = $direccion_email;
   $datos[$fila_array]["cantidad_hijos"] = $cantidad_hijos;
	 
	 $datos[$fila_array]["codigo_zona_residencia"] = $codigo_zona_residencia;
   //
   $datos[$fila_array]["embarazada"] = $embarazada;
   $datos[$fila_array]["codigo_tipo_vivienda"] = $codigo_tipo_vivienda;
   $datos[$fila_array]["codigo_canton"] = $codigo_canton;
   $datos[$fila_array]["codigo_caserio"] = $codigo_caserio;
   $datos[$fila_array]["servicio_energia"] = $servicio_energia;
   $datos[$fila_array]["recoleccion_basura"] = $recoleccion_basura;
   $datos[$fila_array]["codigo_abastecimiento"] = $codigo_abastecimiento;
         // Rellenado la array, tercer tabs-3. Documentos presentados.
         $datos[$fila_array]["codigo_estatus"] = $codigo_estatus;         
         // Rellenando la array, cuarto tabs-
	 // compo Id.
	 $datos[$fila_array]["id_alumno"] = $id;
   // foto url
          $datos[$fila_array]["codigo_institucion"] = $codigo_institucion;
         $datos[$fila_array]["url_foto"] = $url_foto;
         $datos[$fila_array]["url_pn"] = $url_pn;
         $datos[$fila_array]["archivo_origen"] = $archivo_origen;
	 // Incrementar el valor del array.
	   $fila_array++;
        }
// Recorriendo la Tabla con PDO::        
         // Rellenando la array. cuarto tabs-4. Padre/Madre/Encargado.
	 // Debera crerase en las tablas correspondientes los campos para poder rellenar dicha informaci�n.
            if($consulta_encargado -> rowCount() != 0){		
                while($listadoEncargado = $consulta_encargado -> fetch(PDO::FETCH_BOTH))
                  {
                    $id_alumno_encargado = trim($listadoEncargado['id_alumno_encargado']);
                    $nombres = trim($listadoEncargado['nombres']);
                    $lugar_trabajo = trim($listadoEncargado['lugar_trabajo']);
                    $profesion = trim($listadoEncargado['profesion_oficio']);
                    $dui = trim($listadoEncargado['dui']);
                    $telefono = trim($listadoEncargado['telefono']);
                    $direccion = trim($listadoEncargado['direccion']);
                    $encargado_bollean = trim($listadoEncargado['encargado']);
                    
                    $fecha_nacimiento = trim($listadoEncargado['fecha_nacimiento']);
                    $codigo_nacionalidad = trim($listadoEncargado['codigo_nacionalidad']);
                    $codigo_familiar = trim($listadoEncargado['codigo_familiar']);
                    $codigo_zona = trim($listadoEncargado['codigo_zona']);
                    $codigo_departamento = trim($listadoEncargado['codigo_departamento']);
                    $codigo_municipio = trim($listadoEncargado['codigo_municipio']);
                    $codigo_distrito = trim($listadoEncargado['codigo_distrito']);
                    // pasar a la matriz.
                        $datos[$fila_array]["id_alumno_encargado"] = $id_alumno_encargado;
                        $datos[$fila_array]["nombres"] = $nombres;
                        $datos[$fila_array]["lugar_trabajo"] = $lugar_trabajo;
                        $datos[$fila_array]["profesion"] = $profesion;
                        $datos[$fila_array]["dui"] = $dui;
                        $datos[$fila_array]["telefono"] = $telefono;
                        $datos[$fila_array]["direccion"] = $direccion;
                        $datos[$fila_array]["encargado_bollean"] = $encargado_bollean;
                        
                        $datos[$fila_array]["fecha_nacimiento"] = $fecha_nacimiento;
                        $datos[$fila_array]["codigo_nacionalidad"] = $codigo_nacionalidad;
                        $datos[$fila_array]["codigo_familiar"] = $codigo_familiar;
                        $datos[$fila_array]["codigo_zona"] = $codigo_zona;
                        $datos[$fila_array]["codigo_departamento"] = $codigo_departamento;
                        $datos[$fila_array]["codigo_municipio"] = $codigo_municipio;
                        $datos[$fila_array]["codigo_distrito"] = $codigo_distrito;
                    // Incrementar el valor del array.
                    $fila_array++;
                  }
            }
// Recorriendo la Tabla con PDO::
    $num = 1;
	if($consulta_historial -> rowCount() != 0){		
            while($listadoHistorial = $consulta_historial -> fetch(PDO::FETCH_BOTH))
              {
                  // recopilar los valores de los campos.
                  $id_bitacora = trim($listadoHistorial['id_alumno_bitacora']);
                  $cod_alumno = trim($listadoHistorial['codigo_alumno']);
                  $fecha_ob = cambiaf_a_normal(trim($listadoHistorial['fecha_ob']));
                  $historial = convertirTexto(trim($listadoHistorial['historial']));
                  
                  // pasar a la matriz.
		  $datos[$fila_array]["todos"] = '<tr><td>'.trim($num).'<td>'.$id_bitacora.'<td>'.$cod_alumno.'<td>'.$fecha_ob.
		    '<td><textarea class=form-control rows=2 disabled>'.$historial.'</textarea></td>'
		    .'<td class = centerTXT><a data-accion=editar class="btn btn-xs btn-primary" href='.$listadoHistorial['id_alumno_bitacora'].'>Editar</a>'
		    .'<td><a data-accion=eliminarHistorial class="btn btn-xs btn-warning" href='.$listadoHistorial['id_alumno_bitacora'].'>Eliminar</a></tr>';
      
               // Incrementar el valor del array.
                 $fila_array++; $num++;
              }
        }
	else{
            $datos[$fila_array]["no_registros"] = '<tr><td> No se encontraron registros.</td>';
        }
// Enviando la matriz con Json.
echo json_encode($datos);	