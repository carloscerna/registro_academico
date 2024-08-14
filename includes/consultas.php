<?php
function consultas_docentes($ejecutar,$cerrar,$codigo_bachillerato,$codigo_grado,$codigo_seccion,$codigo_annlectivo,$db_link,$result)
{
    global $result_docente, $result_encabezado, $codigo_encargado, $print_nombre_docente;
    if($ejecutar == 1)
    {
    	// CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
        $query_docente = "SELECT eg.encargado, eg.codigo_ann_lectivo, eg.codigo_grado, eg.codigo_seccion, eg.codigo_bachillerato, eg.codigo_docente, eg.imparte_asignatura, eg.codigo_turno,
            btrim(p.nombres || cast(' ' as VARCHAR) || p.apellidos) as nombre_docente
                FROM encargado_grado eg
                    INNER JOIN personal p ON p.id_personal = eg.codigo_docente
                        WHERE btrim(eg.codigo_bachillerato || eg.codigo_grado || eg.codigo_seccion || eg.codigo_ann_lectivo || eg.codigo_turno) = '$codigo_bachillerato' and eg.encargado = 't'";
        // Ejecutamos el Query. Docente Encargado de la sección.
	    $result_docente = $db_link -> query($query_docente);
        // revisar si existe el docente.
        while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
        {
            $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
        }      
    }
    // EXTRAER CON RESPECTO AL AÑO LECTIVO
    if($ejecutar == 2)
    {
	// CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
        $query_docente = "SELECT eg.encargado, eg.codigo_ann_lectivo, eg.codigo_grado, eg.codigo_seccion, eg.codigo_bachillerato, eg.codigo_docente, eg.imparte_asignatura, eg.codigo_turno,
                        btrim(p.nombres || cast(' ' as VARCHAR) || p.apellidos) as nombre_docente
	                        FROM encargado_grado eg
                                INNER JOIN personal p ON p.id_personal = eg.codigo_docente
	                                WHERE eg.codigo_ann_lectivo = '".$codigo_bachillerato."' and eg.encargado = 't' ORDER BY eg.codigo_bachillerato, eg.codigo_grado, eg.codigo_seccion, eg.codigo_ann_lectivo";
	
	// ejecutar la consulta.
            // Ejecutamos el Query. Tabla Bitacora.
	    $result_docente = $db_link -> query($query_docente);
    }
    
    //se liberan recursos y se cierra la conexión
    if($cerrar == 1)
    {
    	pg_free_result($result_docente);
    	pg_close($db_link);
    }
}

function consultas($ejecutar,$cerrar,$codigo_bachillerato,$codigo_grado,$codigo_seccion,$codigo_annlectivo,$db_link,$result)
{
    global $result, $result_encabezado, $codigo_encargado, $por_genero, $result_indicadores, $result_nombre_asignatura;

    if($ejecutar == 1)
    {
    // variable con la consulta para el encargado de grado.
        $query = "SELECT eg.id_encargado_grado, pd.nombre_completo as nombre_completo, pd.codigo as codigos_docente, eg.codigo_encargado,
                    bach.nombre as nombre_bachillerato, gann.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_año_lectivo,
     		    bach.codigo as codigo_bachillerato, ann.codigo as codigo_ann_lectivo, gann.codigo as codigo_grado, sec.codigo as codigo_seccion
                    FROM encargado_grado eg
                    INNER JOIN planta_docente pd ON eg.codigo_docente = pd.codigo
		    INNER JOIN bachillerato_ciclo bach ON bach.codigo = eg.codigo_bachillerato
		    INNER JOIN ann_lectivo ann ON ann.codigo = eg.codigo_ann_lectivo
		    INNER JOIN grado_ano gann ON gann.codigo = eg.codigo_grado
		    INNER JOIN seccion sec ON sec.codigo = eg.codigo_seccion
                    WHERE ".
                    "eg.codigo_bachillerato = '".$codigo_bachillerato.
                    "' and eg.codigo_grado = '".$codigo_grado.
                    "' and eg.codigo_seccion = '".$codigo_seccion.
                    "' and eg.codigo_ann_lectivo = '".$codigo_annlectivo.
                    "' ORDER BY pd.nombre_completo";

    // ejecutar la consulta.
        $result = $db_link -> query($query);

    // variable del encargado.
	while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $codigo_encargado = $row['codigos_docente'];
                break;
            }
    }
    
    if($ejecutar == 2)
    {
        global $codigo_encargado;
        $query = "SELECT esta.id_estadistica_grado as id_esta, pd.nombre_completo, esta.genero, esta.matricula_inicial, esta.retirados, esta.matricula_final, esta.promovidos, esta.retenidos,
     		esta.codigo_docente, esta.codigo_grado, esta.codigo_seccion, esta.codigo_bachillerato_ciclo, esta.codigo_año_lectivo,
     		bach.nombre as nombre_bachillerato, sec.nombre as nombre_seccion, gan.nombre as nombre_grado, ann.nombre as nombre_ann_lectivo
		FROM estadistica_grados esta
		INNER JOIN planta_docente pd ON pd.codigo = '".$codigo_encargado."'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = esta.codigo_bachillerato_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = esta.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = esta.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = esta.codigo_año_lectivo
		WHERE ".
                "esta.codigo_bachillerato_ciclo = '".$codigo_bachillerato.
                "' and esta.codigo_grado = '".$codigo_grado.
                "' and esta.codigo_seccion = '".$codigo_seccion.
                "' and esta.codigo_año_lectivo = '".$codigo_annlectivo."'";

    // ejecutar la consulta.
	$result = $db_link -> query($query);
	$result_encabezado = $db_link -> query($query);
    }

    // para los diferntes listados a imprimir
    if($ejecutar == 3)
    {
	$query = "SELECT orgs.codigo_bachillerato, orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_año_lectivo, orgs.codigo_turno,
		bach.nombre as nombre_bachillerato, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo, tur.nombre as nombre_turno
		FROM organizacion_grados_secciones orgs
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = orgs.codigo_bachillerato
		INNER JOIN grado_ano gan ON gan.codigo = orgs.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = orgs.codigo_año_lectivo
		INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno".
		" WHERE ".
                "orgs.codigo_bachillerato = '".$codigo_bachillerato.
                "' and orgs.codigo_año_lectivo = '".$codigo_annlectivo.
                "' ORDER BY orgs.codigo_grado, orgs.codigo_seccion ASC";        
        
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
  
    // para los diferntes listados a imprimir
    if($ejecutar == 4)
    {
	$order = ' ORDER BY apellido_alumno ASC';
	
	if($por_genero == 'true'){
	    $order = ' ORDER BY a.genero, apellido_alumno ASC';
	}
	
     $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
       btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo, a.apellido_paterno, a.apellido_materno,
       btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno  || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
       ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui as encargado_dui, ae.telefono as telefono_encargado, ae.fecha_nacimiento as encargado_fecha_nacimiento, ae.direccion as encargado_direccion, ae.telefono as encargado_telefono,
       a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad, a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.partida_nacimiento, a.telefono_celular,
       a.codigo_departamento, a.codigo_municipio, cat_gs.descripcion as genero_estudiante,
       am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.ann_anterior,
       am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, bach.codigo as codigo_bachillerato,
       am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula, am.codigo_turno,
       am.observaciones, am.id_alumno_matricula as cod_matricula,
       ae.codigo_departamento as encargado_departamento, ae.codigo_municipio as encargado_municipio,
       cat_f.descripcion as nombre_tipo_parentesco,
       cat_g.descripcion as nombre_sexo,
       tur.nombre as nombre_turno
           FROM alumno a
               INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
               INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
               INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
               INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
               INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
               INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
               INNER JOIN turno tur ON tur.codigo = am.codigo_turno
               INNER JOIN catalogo_familiar cat_f ON cat_f.codigo = ae.codigo_familiar
               INNER JOIN catalogo_genero cat_g ON cat_g.codigo = ae.codigo_genero
               INNER JOIN catalogo_genero cat_gs ON cat_gs.codigo = a.codigo_genero
                WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
		"'".$order;
            // Ejecutamos el Query. Tabla Bitacora. and am.retirado = 'f'
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
		
    }  

    // para los diferntes listados para notas.
    if($ejecutar == 5)
    {
     $query = "SELECT DISTINCT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
		a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.genero, a.foto,
		am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, am.codigo_turno, am.fecha_ingreso,
		gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, bach.codigo as codigo_bachillerato,
		sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_final, n.recuperacion, n.nota_paes, n.orden,
		id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula, tur.nombre as nombre_turno,
		round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, 
        round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, aaa.orden, aaa.codigo_ann_lectivo
		FROM alumno a
		INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
		INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
		INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
        INNER JOIN turno tur ON tur.codigo = am.codigo_turno
		INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = asig.codigo and aaa.orden <> 0 ".
		"WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato. 
		"' and aaa.codigo_ann_lectivo = '".substr($codigo_bachillerato,6,2)."' ORDER BY apellido_alumno, n.orden ASC";
    
    // ejecutar la consulta.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
    }  

        if($ejecutar == 6)
    {
        $query = "SELECT aaa.codigo_asignatura, asig.nombre as nombre_asignatura
                FROM a_a_a_bach_o_ciclo aaa
                INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura and asig.concepto_calificacion = 'Calificación'
                WHERE aaa.codigo_bach_o_ciclo = '".$codigo_bachillerato."' and aaa.codigo_ann_lectivo = '".$codigo_annlectivo."'".
                " ORDER BY aaa.codigo_asignatura";
                
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

        if($ejecutar == 7)
    {
       $query = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
		    a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, 
		    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
		    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, 
		    sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_final, n.recuperacion, n.nota_recuperacion_2,
		    round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media
		    FROM alumno a
			INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
			INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
			INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
			INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
			INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
			INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura ".
			"WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato."' and n.codigo_asignatura = '".$codigo_grado.
			"' ORDER BY apellido_alumno, n.codigo_asignatura ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }    
    
    // para los diferntes listados a imprimir
    if($ejecutar == 8)
    {
        $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
                ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.partida_nacimiento,
                a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
                am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
                am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
                am.codigo_turno
		FROM alumno a
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
		"' ORDER BY apellido_alumno ASC";
    // ejecutar la consulta.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
    }  

     // para los diferntes listados a imprimir por género.
    if($ejecutar == 9)
    {
      $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
                ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.ruta_pn,
				a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
                am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.codigo_turno,
                am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
					FROM alumno a
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
				INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and am.retirado = 'f'
				INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
				INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
				INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
				INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '$codigo_bachillerato'
					ORDER BY apellido_alumno ASC";
		
            // Ejecutamos el Query. Tabla Bitacora.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
	    $result_indicadores = $db_link -> query($query);
    }


     // para los diferntes listados a imprimir por género.
    if($ejecutar == 10)
    {
        $query = "SELECT a.foto, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno, a.nombre_completo, a.apellido_paterno, a.apellido_materno, a.fecha_nacimiento,
		            a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombres,
                    a.codigo_genero,
                    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
		            gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, tur.nombre as nombre_turno,
		            sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno
                        FROM alumno a
                        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' and am.imprimir_foto = 't'
                        INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                        INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                        INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                        INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                        WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
                        "' ORDER BY apellido_alumno ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    
     // para los diferntes listados a imprimir
    if($ejecutar == 11)
    {
        $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
		btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno  || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
                ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, 
                am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
                am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula,
		am.observaciones
		FROM alumno a
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                WHERE am.codigo_ann_lectivo = '".substr($codigo_bachillerato,6,2).
		"' ORDER BY apellido_alumno ASC";
    // ejecutar la consulta.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
    }

     // para los diferntes listados a imprimir por género.
    if($ejecutar == 12)
    {
        $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
                ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero,
		a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
                am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
                am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion
		FROM alumno a
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                WHERE a.edad >= 5 and a.fecha_nacimiento >= '01/01/1999' and a.fecha_nacimiento <= '19/05/2014' and btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_bachillerato.
		"' ORDER BY edad DESC";
		
		
    // ejecutar la consulta.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
    }
         // para los diferntes listados Indicadores Educativos.
    if($ejecutar == 13)
    {
        $query = "SELECT org.codigo_bachillerato as codigo_modalidad, org.codigo_grado, org.codigo_seccion, org.codigo_ann_lectivo, org.codigo_turno,
                sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo, gan.nombre as nombre_grado, bach.nombre as nombre_bachillerato, tur.nombre as nombre_turno
                    FROM organizacion_grados_secciones org
                        INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
                        INNER JOIN grado_ano gan ON gan.codigo = org.codigo_grado
                        INNER JOIN seccion sec ON sec.codigo = org.codigo_seccion
                        INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
                        INNER JOIN turno tur ON tur.codigo = org.codigo_turno
                            WHERE org.codigo_ann_lectivo = '$codigo_bachillerato'
                                ORDER BY codigo_grado, codigo_bachillerato, codigo_seccion, codigo_turno";
    // ejecutar la consulta.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
    } 

 
          // para los diferntes listados Indicadores Educativos.
    if($ejecutar == 14)
    {
         $query = "SELECT DISTINCT ROW(org.codigo_grado), org.codigo_bachillerato, org.codigo_grado, org.codigo_ann_lectivo, org.codigo_turno,
                    gan.nombre as nombre_grado, ann.nombre as nombre_ann_lectivo, tur.nombre as nombre_turno, bach.nombre as nombre_modalidad
			            FROM organizacion_grados_secciones org
                            INNER JOIN grado_ano gan ON gan.codigo = org.codigo_grado
                            INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
                            INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
                            INNER JOIN turno tur ON tur.codigo = org.codigo_turno
                                WHERE codigo_ann_lectivo = '".$codigo_bachillerato.
	                			 "' ORDER BY org.codigo_bachillerato, org.codigo_grado, org.codigo_ann_lectivo";
            /*
            CONSULTA PARA LE MEMORIA ESTADISTICA
            SELECT DISTINCT ROW(org.codigo_bachillerato), org.codigo_bachillerato, org.codigo_grado, org.codigo_ann_lectivo,
                                gan.nombre as nombre_grado, ann.nombre as nombre_ann_lectivo,  bach.nombre as nombre_modalidad
                                    FROM organizacion_grados_secciones org
                                        INNER JOIN grado_ano gan ON gan.codigo = org.codigo_grado
                                        INNER JOIN ann_lectivo ann ON ann.codigo = org.codigo_ann_lectivo
                                        INNER JOIN bachillerato_ciclo bach ON bach.codigo = org.codigo_bachillerato
                                            WHERE codigo_ann_lectivo = '21' ORDER BY org.codigo_bachillerato, org.codigo_grado, org.codigo_ann_lectivo
                */

                                 // ejecutar la consulta.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
    }
    
    
    // para los diferntes listados a imprimir por género.
    // PARA LOS DATOS ESTADISTICOS QUE PRESENTE LA MATRICULA MAXIMA.
    if($ejecutar == 15)
    {
       $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
                ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero,
				a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
                am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
                am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion
					FROM alumno a
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
				INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
				INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
				INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
				INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
				INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
					"' ORDER BY apellido_alumno ASC";
		
            // Ejecutamos el Query. Tabla Bitacora.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
	    $result_indicadores = $db_link -> query($query);
    }

// para los diferntes listados a imprimir por género.
    if($ejecutar == 16)
    {
      $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
                ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero,
				a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
                am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
                am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion
					FROM alumno a
                INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
				INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and am.retirado = 'f'
				INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
				INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
				INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
				INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
					"' ORDER BY apellido_alumno ASC";
		
            // Ejecutamos el Query. Tabla Bitacora.
	    $result = $db_link -> query($query);
	    $result_encabezado = $db_link -> query($query);
	    $result_indicadores = $db_link -> query($query);
    }
    
	// PARA CONSULTAR LAS ASIGNATURAS QUE TIENEN NOTA DE RECUPERACION DIFERENTE DE 0.
	if($ejecutar == 17)
    {
        $query = "SELECT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
		    a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, 
		    am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
		    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, 
		    sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_final, n.recuperacion,
		    round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media,
            asig.codigo_servicio_educativo
		    FROM alumno a
			INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
			INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
			INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
			INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
			INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
			INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura ".
			"WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_ann_lectivo) = '".$codigo_bachillerato."' and n.recuperacion <> 0".
			" ORDER BY am.codigo_grado, am.codigo_seccion, n.codigo_asignatura, apellido_alumno ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
/* SOLO PARA EL ENCABEZADO **************************************************************************************************************************/
/* SOLO PARA EL ENCABEZADO **************************************************************************************************************************/

if($ejecutar == 18)
{
        $query = "SELECT  a.id_alumno as codigo_alumno, am.id_alumno_matricula as codigo_matricula,
                am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, 
                am.codigo_grado, gan.nombre as nombre_grado,
                am.codigo_turno, tur.nombre as nombre_turno,
                am.codigo_seccion, sec.nombre as nombre_seccion,
                bach.codigo as codigo_bachillerato, bach.nombre as nombre_bachillerato
                FROM alumno a
			    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
			    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
                INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato."' LIMIT 1";

// ejecutar la consulta.
    $result_encabezado = $db_link -> query($query);
}  
/* SOLO PARA EL ENCABEZADO **************************************************************************************************************************/
/* SOLO PARA EL ENCABEZADO **************************************************************************************************************************/

/* SOLO PARA EL ENCABEZADO NOMBRE ASIGNATURA **************************************************************************************************************************/
/* SOLO PARA EL ENCABEZADO NOMBRE ASIGNATURA **************************************************************************************************************************/

if($ejecutar == 19)
{
   $query = "SELECT aaa.codigo_asignatura, aaa.orden, asig.nombre as nombre_asignatura, asig.codigo_servicio_educativo
    FROM a_a_a_bach_o_ciclo aaa 
        INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura 
        WHERE btrim(aaa.codigo_bach_o_ciclo || aaa.codigo_grado || codigo_ann_lectivo) = '$codigo_bachillerato' 
        ORDER BY aaa.orden";

// ejecutar la consulta.
    $result_nombre_asignatura = $db_link -> query($query);
}  
/* SOLO PARA EL ENCABEZADO NOMBRE ASIGNATURA **************************************************************************************************************************/
/* SOLO PARA EL ENCABEZADO NOMBRE ASIGNATURA **************************************************************************************************************************/
//se liberan recursos y se cierra la conexión
    if($cerrar == 1)
    {
    	pg_free_result($result);
    	pg_close($db_link);
    }
    //
    // para los diferntes listados a imprimirÇ
    // EXPORTAR INFORMACION A ARCHIVO DE EXCEL.
    if($ejecutar == 20)
    {
    $order = ' ORDER BY apellido_alumno ASC';

    if($por_genero == 'true'){
        $order = ' ORDER BY a.genero, apellido_alumno ASC';
    }

    $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
    btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
    btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno  || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
    ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui as encargado_dui, ae.telefono as telefono_encargado, ae.fecha_nacimiento as encargado_fecha_nacimiento, ae.direccion as encargado_direccion, ae.telefono as encargado_telefono,
    a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad, a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.partida_nacimiento, a.telefono_celular,
    a.codigo_departamento, a.codigo_municipio, a.codigo_genero,
    am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.ann_anterior,
    am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, bach.codigo as codigo_bachillerato,
    am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula, am.codigo_turno,
    am.observaciones, am.id_alumno_matricula as cod_matricula,
    ae.codigo_departamento as encargado_departamento, ae.codigo_municipio as encargado_municipio,
    cat_f.descripcion as nombre_tipo_parentesco,
    cat_g.descripcion as nombre_sexo,
    tur.nombre as nombre_turno
        FROM alumno a
            INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
            INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
            INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
            INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
            INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
            INNER JOIN turno tur ON tur.codigo = am.codigo_turno
            INNER JOIN catalogo_familiar cat_f ON cat_f.codigo = ae.codigo_familiar
            INNER JOIN catalogo_genero cat_g ON cat_g.codigo = ae.codigo_genero
                WHERE a.edad >= '8' and a.edad <= '12' and a.codigo_genero = '02' 
                and btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
        "'".$order;
            // Ejecutamos el Query. Tabla Bitacora. and am.retirado = 'f'
        $result = $db_link -> query($query);
        $result_encabezado = $db_link -> query($query);
        
    }  

    // EXPORTAR INFORMACION A ARCHIVO DE EXCEL.
    if($ejecutar == 21)
    {
    $order = ' ORDER BY apellido_alumno ASC';

    if($por_genero == 'true'){
        $order = ' ORDER BY a.genero, apellido_alumno ASC';
    }

    $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
    btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
    btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno  || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
    ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui as encargado_dui, ae.telefono as telefono_encargado, ae.fecha_nacimiento as encargado_fecha_nacimiento, ae.direccion as encargado_direccion, ae.telefono as encargado_telefono,
    a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad, a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.partida_nacimiento, a.telefono_celular,
    a.codigo_departamento, a.codigo_municipio, a.codigo_genero,
    am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.ann_anterior,
    am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, bach.codigo as codigo_bachillerato,
    am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula, am.codigo_turno,
    am.observaciones, am.id_alumno_matricula as cod_matricula,
    ae.codigo_departamento as encargado_departamento, ae.codigo_municipio as encargado_municipio,
    cat_f.descripcion as nombre_tipo_parentesco,
    cat_g.descripcion as nombre_sexo,
    tur.nombre as nombre_turno
        FROM alumno a
            INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
            INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
            INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
            INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
            INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
            INNER JOIN turno tur ON tur.codigo = am.codigo_turno
            INNER JOIN catalogo_familiar cat_f ON cat_f.codigo = ae.codigo_familiar
            INNER JOIN catalogo_genero cat_g ON cat_g.codigo = ae.codigo_genero
                WHERE a.edad > '10' and a.codigo_genero = '02' and btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
        "'".$order;
            // Ejecutamos el Query. Tabla Bitacora. and am.retirado = 'f'
        $result = $db_link -> query($query);
        $result_encabezado = $db_link -> query($query);
        
    }  
    // PARA LA SOBREEDADD
  // para los diferntes listados a imprimir por género.
  //
  if($ejecutar == 22)
  {
   $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
              btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
              ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
              a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.ruta_pn,
              a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
              am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.codigo_turno,
              am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
              am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, tur.nombre as nombre_turno
                  FROM alumno a
              INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
              INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and am.retirado = 'f' and am.sobreedad = 't'
              INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
              INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
              INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
              INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
              INNER JOIN turno tur ON tur.codigo = am.codigo_turno
              WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_bachillerato.
                  "' ORDER BY apellido_alumno ASC";
      
          // Ejecutamos el Query. Tabla Bitacora.
      $result = $db_link -> query($query);
      $result_encabezado = $db_link -> query($query);
      $result_indicadores = $db_link -> query($query);
  }

}   // FIN DE LA FUNCION CONSULTAS

function consultas_alumno($ejecutar,$cerrar,$buscar_nombre,$codigo_alumno,$codigo_matricula,$codigo_ann_lectivo, $db_link,$result)
{
    global $result;
    // consultar por nombre de alumno.
       if($ejecutar == 1)
    {
        $query = "SELECT a.id_alumno, a.codigo_nie, a.apellido_paterno, a.apellido_materno, a.nombre_completo, 
          am.codigo_bach_o_ciclo, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, 
          am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula,
          btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno
          FROM alumno a
          INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
          INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
          INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
          INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
          INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
          WHERE nombre_completo like '%".$buscar_nombre."%' ORDER BY apellido_paterno ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }            

    
    if($ejecutar == 2)
    {
     $query = "SELECT DISTINCT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
		    a.apellido_paterno, a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.foto, a.codigo_genero,
            am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
            gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, am.id_alumno_matricula as codigo_matricula, am.id_alumno_matricula as cod_matricula,
            sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura,
            n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.nota_final, n.recuperacion, n.nota_paes, n.alertas,
            n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final,
            round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, 
            round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, 
            round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4+n.nota_p_p_5),1) as total_puntos_nocturna,
            aaa.orden, asig.codigo_cc, asig.codigo_area, cat_area.descripcion as nombre_area 
                    FROM alumno a
                    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' and am.id_alumno_matricula = '$codigo_matricula'
                    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno
                    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
                    INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
                    INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
                    INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = asig.codigo and aaa.orden <> 0 ".
                        "WHERE a.id_alumno = '".$codigo_alumno."' and codigo_matricula = '".$codigo_matricula.
                        "' and aaa.codigo_ann_lectivo = '".substr($codigo_ann_lectivo,2,2)."' ORDER BY aaa.orden ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

    // consultas utilizadas en la ficha. tabla alumno
    if($ejecutar == 3)
    {
      $query_a = "SELECT  a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
            btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_a_pm,
		    a.apellido_paterno, a.apellido_materno, a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno,
		    a.direccion_alumno, telefono_alumno, a.fecha_nacimiento, a.edad, a.pn_tomo, a.pn_libro, a.pn_numero, a.pn_folio,
		    a.nacionalidad, a.transporte, a.distancia,
		    a.codigo_departamento, a.codigo_municipio, a.genero, a.codigo_estado_civil, cat_ec.nombre as estado_civil,
		    a.estudio_parvularia, a.tiene_hijos, a.cantidad_hijos, a.codigo_actividad_economica, a.codigo_discapacidad, a.codigo_estado_familiar,
		    a.foto,
            ae.encargado, ae.dui as encargado_dui,
		    depa.nombre as nombre_departamento, depa.codigo,
		    muni.nombre as nombre_municipio,
		    cat_z_r.codigo as codigo_zona_residencia
		FROM alumno a
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN catalogo_zona_residencia cat_z_r ON cat_z_r.codigo = a.codigo_zona_residencia
		INNER JOIN catalogo_estado_civil cat_ec ON cat_ec.codigo = a.codigo_estado_civil
		INNER JOIN departamento depa ON depa.codigo = a.codigo_departamento 
		INNER JOIN municipio muni ON muni.codigo = a.codigo_municipio and a.codigo_departamento = muni.codigo_departamento
		WHERE id_alumno = $codigo_alumno ORDER BY apellido_alumno";
    // ejecutar la consulta.
        $result = $db_link -> query($query_a);
    }

    // consultas utilizadas en la ficha. tabla encargado
    if($ejecutar == 4)
    {
         $query = "SELECT a.id_alumno, ae.nombres, ae.dui, ae.lugar_trabajo, ae.profesion_oficio, ae.telefono, ae.direccion
		FROM alumno a
		INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno
		WHERE id_alumno = '".$codigo_alumno."' ORDER BY ae.id_alumno_encargado";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }    

    // consultas utilizadas en la ficha. tabla estudios realizados
    if($ejecutar == 5)
    {
        $query = "SELECT am.id_alumno_matricula, nom_esc.nombre as nombre_escuela,
		bach.nombre as nombre_bachillerato,
		gan.nombre as nombre_grado,
		sec.nombre as nombre_seccion,
		ann.nombre as nombre_ann_lectivo
		FROM alumno_matricula am
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
		INNER JOIN catalogo_escuelas nom_esc ON nom_esc.codigo = am.codigo_institucion
		WHERE am.codigo_alumno = '".$codigo_alumno."' ORDER BY nombre_ann_lectivo";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    
//se liberan recursos y se cierra la conexión
    if($cerrar == 1)
    {
    	pg_free_result($result);
    	pg_close($db_link);
    }
}


function consulta_docente($ejecutar,$cerrar,$codigo_annlectivo,$codigo_docentes,$codigo_contratacion,$db_link,$result,$fecha_desde,$fecha_hasta,$codigo_licencia_permiso,$codigo_turno)
{
    global $result, $result_saldo;
     // para los diferntes listados a imprimir Licencias y Permisos de docentes.
    if($ejecutar == 1)
    {
	 $query = "SELECT lp.id_licencia_permiso, lp.codigo_año_lectivo, lp.fecha, lp.codigo_docente, lp.codigo_contratacion, lp.codigo_licencia_o_permiso, lp.dia, lp.hora, lp.minutos, lp.observacion,
			ann.nombre as nombre_ann_lectivo, btrim(p.nombres || cast(' ' as VARCHAR) || p.apellidos) as nombre_docente, tlp.nombre as nombre_licencia_permiso,
			tur.nombre as nombre_turno
		FROM licencias_permisos lp
		INNER JOIN ann_lectivo ann ON ann.codigo = lp.codigo_año_lectivo
		INNER JOIN personal pd ON pd.id_personal = lp.codigo_docente
		INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
		INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_o_permiso
		INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
		WHERE lp.codigo_año_lectivo = '".$codigo_annlectivo."' and lp.codigo_docente = '".$codigo_docentes."' and lp.codigo_contratacion = '".$codigo_contratacion."'".
		" ORDER BY lp.codigo_licencia_o_permiso, lp.fecha ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);;
    }    

        // conteo de los datos.
    if($ejecutar == 2)
    {
	$query = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos
		FROM licencias_permisos 
		WHERE codigo_docente = '".$codigo_docentes."' and codigo_año_lectivo = '".$codigo_annlectivo."' and fecha >= '".$fecha_desde."' and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_o_permiso = '".$codigo_licencia_permiso."' and codigo_turno = '".$codigo_turno."'";
        
	$query_saldo = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos
		FROM licencias_permisos 
		WHERE codigo_docente = '".$codigo_docentes."' and codigo_año_lectivo = '".$codigo_annlectivo."' and fecha >= '2013-01-01'"." and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_o_permiso = '".$codigo_licencia_permiso."' and codigo_turno = '".$codigo_turno."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    /*
    if($ejecutar == 2)
    {
	print $query = "SELECT lp.id_licencia_permiso, lp.codigo_año_lectivo, lp.fecha, lp.codigo_docente, lp.codigo_contratacion, lp.codigo_licencia_o_permiso, lp.dia, lp.hora, lp.minutos, lp.observacion,
			ann.nombre as nombre_ann_lectivo, pd.nombre_completo, tlp.nombre as nombre_licencia_permiso
		FROM licencias_permisos lp
		INNER JOIN ann_lectivo ann ON ann.codigo = lp.codigo_año_lectivo
		INNER JOIN planta_docente pd ON pd.codigo = lp.codigo_docente
		INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
		INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_o_permiso
		WHERE lp.codigo_año_lectivo = '".$codigo_annlectivo."' and lp.fecha >= '".$fecha_desde."' and lp.fecha <= '".$fecha_hasta."' and lp.codigo_contratacion = '".$codigo_contratacion."'".
		" ORDER BY lp.fecha, lp.codigo_contratacion ASC";
    // ejecutar la consulta.
        $result = pg_query($db_link, $query);
        $result_encabezado = pg_query($db_link, $query);
    } */   

    // tabla planta docente.
    if($ejecutar == 3)
    {
	$query = "SELECT codigo, nombre_completo from planta_docente ORDER BY codigo";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
        $result_encabezado = pg_query($db_link, $query);
    }    

    // tabla tipo de licencia o permiso
    if($ejecutar == 4)
    {
	$query = "SELECT codigo, nombre, saldo from tipo_licencia_o_permiso ORDER BY codigo";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
        $result_encabezado = pg_query($db_link, $query);
    }    

    // conteo de los datos.
    if($ejecutar == 5)
    {   
	$query = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos
		FROM licencias_permisos 
		WHERE codigo_docente = '".$codigo_docentes."' and codigo_año_lectivo = '".$codigo_annlectivo."' and fecha >= '2013-01-01'"." and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_o_permiso = '".$codigo_licencia_permiso."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    // optener el nombre del tipo de contratación.
    if($ejecutar == 6)
    {   
	$query = "SELECT codigo, nombre
		FROM tipo_contratacion 
		WHERE codigo = '".$codigo_contratacion."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    // optener el nombre del año lectivo.
    if($ejecutar == 7)
    {   
	$query = "SELECT codigo, nombre
		FROM ann_lectivo 
		WHERE codigo = '".$codigo_annlectivo."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

    // optener el nombre del año lectivo.
    if($ejecutar == 8)
    {   
	$query = "SELECT codigo, nombre
		FROM turno 
		WHERE codigo = '".$codigo_turno."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    // optener el salario docente_salario.
    if($ejecutar == 9)
    {   
	$query = "SELECT ds.id_docente_salario, ds.codigo_docente, ds.codigo_tipo_contratacion, ds.salario,
		pd.nombre_completo, tc.nombre as nombre_contratacion
		FROM docente_salario ds
		INNER JOIN planta_docente pd ON pd.codigo = ds.codigo_docente
                INNER JOIN tipo_contratacion tc ON tc.codigo = ds.codigo_tipo_contratacion
		WHERE ds.codigo_docente = '".$codigo_docentes."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

        // tabla planta docente.
    if($ejecutar == 10)
    {
	$query = "SELECT lp.dia, lp.hora, lp.minutos, lp.fecha, lp.codigo_contratacion, pd.nit, pd.nip, pd.nombre_completo as nombre_docente, lp.observacion, lp.codigo_docente, ds.salario
		FROM licencias_permisos lp
                INNER JOIN planta_docente pd ON pd.codigo = lp.codigo_docente
                INNER JOIN docente_salario ds ON ds.codigo_docente = lp.codigo_docente and ds.codigo_tipo_contratacion = lp.codigo_contratacion
		WHERE lp.codigo_docente = '".$codigo_docentes."' and lp.codigo_año_lectivo = '".$codigo_annlectivo."' and lp.fecha >= '".$fecha_desde."' and lp.fecha <= '".$fecha_hasta."' and lp.codigo_contratacion = '".$codigo_contratacion."' and lp.codigo_licencia_o_permiso = '".$codigo_licencia_permiso."' and lp.codigo_turno = '".$codigo_turno."'"
                ." order by pd.codigo, lp.fecha";    
        $result = $db_link -> query($query);
        $result_encabezado = pg_query($db_link, $query);
    }    
    // saldo desde el mes de enero.
    if($ejecutar == 11)
    {       
	$query = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos
		FROM licencias_permisos 
		WHERE codigo_docente = '".$codigo_docentes."' and codigo_año_lectivo = '".$codigo_annlectivo."' and fecha >= '2013-01-01'"." and fecha <= '".$fecha_hasta."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_o_permiso = '".$codigo_licencia_permiso."' and codigo_turno = '".$codigo_turno."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    
        // saldo desde el mes de enero.
    if($ejecutar == 12)
    {       
	 $query = "SELECT sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos
		FROM licencias_permisos 
		WHERE codigo_docente = '".$codigo_docentes."' and codigo_año_lectivo = '".$codigo_annlectivo."' and codigo_contratacion = '".$codigo_contratacion."' and codigo_licencia_o_permiso = '".$codigo_licencia_permiso."'";

    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

     // para los diferntes listados a imprimir Licencias y Permisos de docentes.
    if($ejecutar == 13)
    {
	$query = "SELECT lp.id_licencia_permiso, lp.codigo_año_lectivo, lp.fecha, lp.codigo_docente, lp.codigo_contratacion, lp.codigo_licencia_o_permiso, lp.dia, lp.hora, lp.minutos, lp.observacion,
			ann.nombre as nombre_ann_lectivo, pd.nombre_completo, tlp.nombre as nombre_licencia_permiso,
			tur.nombre as nombre_turno
		FROM licencias_permisos lp
		INNER JOIN ann_lectivo ann ON ann.codigo = lp.codigo_año_lectivo
		INNER JOIN planta_docente pd ON pd.codigo = lp.codigo_docente
		INNER JOIN tipo_contratacion tc ON tc.codigo = lp.codigo_contratacion
		INNER JOIN tipo_licencia_o_permiso tlp ON tlp.codigo = lp.codigo_licencia_o_permiso
		INNER JOIN turno tur ON tur.codigo = lp.codigo_turno
		WHERE lp.codigo_año_lectivo = '".$codigo_annlectivo."' and lp.codigo_docente = '".$codigo_docentes."' and lp.codigo_contratacion = '".$codigo_contratacion."' and lp.codigo_licencia_o_permiso = '".$codigo_licencia_permiso."'".
		" ORDER BY lp.codigo_licencia_o_permiso, lp.fecha ASC";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }   
}

function consulta_otras_tablas($ejecutar,$cerrar,$codigo,$db_link,$result)
{
    global $result;
     // tabla catalogo estado familiar.
    if($ejecutar == 1)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM catalogo_estado_familiar";
        }else{
            $query = "SELECT codigo, nombre FROM catalogo_estado_familiar WHERE codigo = '".$codigo."'";}
                
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

         // tabla catalogo de actividad economica.
    if($ejecutar == 2)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM catalogo_actividad_economica";
        }else{
            $query = "SELECT codigo, nombre FROM catalogo_actividad_economica WHERE codigo = '".$codigo."'";}
                
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    
     // tabla catalogo servicio de apoyo educativo.
    if($ejecutar == 3)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM catalogo_servicios_de_apoyo_educativo";
        }else{
            $query = "SELECT codigo, nombre FROM catalogo_servicios_de_apoyo_educativo WHERE codigo = '".$codigo."'";}
                
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    // tabla catalogo estado familiar.
    if($ejecutar == 4)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM catalogo_tipo_de_discapacidad";
        }else{
            $query = "SELECT codigo, nombre FROM catalogo_tipo_de_discapacidad WHERE codigo = '".$codigo."'";}
    
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    if($ejecutar == 5)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM turno";
        }else{
            $query = "SELECT codigo, nombre FROM turno WHERE codigo = '".$codigo."'";}
    
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }

    if($ejecutar == 6)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM catalogo_estado_civil";
        }else{
            $query = "SELECT codigo, nombre FROM catalogo_estado_civil WHERE codigo = '".$codigo."'";}
    
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
    
         // tabla catalogo nombre de escuelas.
    if($ejecutar == 7)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM nombre_escuelas ORDER BY codigo";
        }else{
            $query = "SELECT codigo, nombre FROM nombre_escuelas WHERE codigo = '".$codigo."'";}
                
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }


         // tabla catalogo nombre de escuelas.
    if($ejecutar == 8)
    {
        if($codigo == ''){
            $query = "SELECT codigo, nombre FROM catalogo_zona_residencia ORDER BY codigo";
        }else{
            $query = "SELECT codigo, nombre FROM catalogo_zona_residencia WHERE codigo = '".$codigo."'";}
                
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    }
}

////////////// CONSULTAS PARA CONTAR.
function consulta_contar($ejecutar,$cerrar,$codigo_all,$codigo_grado,$codigo_seccion,$codigo_annlectivo,$db_link,$result)
{
    global $result, $result_saldo;
    
    // tabla catalogo estado familiar.
    if($ejecutar == 1)
    {
	//  imprimir datos del bachillerato.
	$modalidad_ann_lectivo = substr($codigo_all,0,2) . substr($codigo_all,2,2) . substr($codigo_all,6,2);
        $query = "SELECT count(*) as total_asignaturas FROM a_a_a_bach_o_ciclo
	    		    WHERE btrim(codigo_bach_o_ciclo || codigo_grado ||codigo_ann_lectivo) = '$modalidad_ann_lectivo' and codigo_asignatura <> ''";
          
    // ejecutar la consulta.
    	    $result = $db_link -> query($query);
    }
}

////////////// CONSULTAS PARA CONTAR.
function consulta_consolidados($ejecutar,$cerrar,$codigo_all,$codigo_grado,$codigo_seccion,$codigo_annlectivo,$db_link,$result)
{
    global $result, $result_saldo;
    
    // tabla catalogo estado familiar.
    if($ejecutar == 1)
    {
	//  imprimir datos del bachillerato.
	$modalidad_ann_lectivo = substr($codigo_all,0,2) . substr($codigo_all,2,2);
            $query = "SELECT  *  FROM organizacion_grados_secciones
	    		    WHERE btrim(codigo_bachillerato || codigo_ann_lectivo) = '".$modalidad_ann_lectivo."'".
			    "ORDER BY codigo_grado, codigo_seccion, codigo_turno";
          
    // ejecutar la consulta.
    	    $result = $db_link -> query($query);
    }
    // tabla catalogo estado familiar.
    if($ejecutar == 2)
    {
	// extraer número de asignaturas.
            $query = "SELECT count(*) as total_asignaturas FROM a_a_a_bach_o_ciclo
	    		    WHERE btrim(codigo_bach_o_ciclo || codigo_grado ||codigo_ann_lectivo) = '".$codigo_all."'";
          
    // ejecutar la consulta.
    	    $result = $db_link -> query($query);
    }
    // tabla catalogo estado familiar.
    if($ejecutar == 3)
    {
    $query = "SELECT a.id_alumno, a.genero, am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado,
	    gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, bach.codigo as codigo_bachillerato, sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura,
	    asig.nombre AS n_asignatura, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_final, n.recuperacion, n.nota_paes, id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
	    tur.nombre as nombre_turno
		FROM alumno a 
		INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
		INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula 
		INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
		INNER JOIN turno tur ON tur.codigo = am.codigo_turno
		WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '$codigo_all' and asig.codigo_cc = '01' ORDER BY a.id_alumno, n.codigo_asignatura ASC";
          
    // ejecutar la consulta.
    	    $result = $db_link -> query($query);
    }
}

function consulta_indicadores($ejecutar,$cerrar,$codigo_all,$codigo_grado,$codigo_seccion,$codigo_annlectivo,$db_link,$result)
{
    global $result, $result_encabezado, $codigo_encargado, $por_genero, $result_indicadores;

     // total masculino.
    if($ejecutar == 1)
    {
        $query = "SELECT count(genero) as total_masculino from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm'";
	    
	    $result_indicadores = $db_link -> query($query);
    }

     // total femenino.
    if($ejecutar == 2)
    {
        $query = "SELECT count(genero) as total_femenino from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
    // total masculino deserción.
    if($ejecutar == 3)
    {
        $query = "SELECT count(retirado) as total_masculino_desercion from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.retirado = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino deserción.
    if($ejecutar == 4)
    {
        $query = "SELECT count(retirado) as total_femenino_desercion from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.retirado = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total masculino deserción.
    if($ejecutar == 5)
    {
        $query = "SELECT count(repitente) as total_masculino_repitente from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.repitente = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino deserción.
    if($ejecutar == 6)
    {
        $query = "SELECT count(repitente) as total_femenino_repitente from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.repitente = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
 
         // total masculino sobreedad.
    if($ejecutar == 7)
    {
        $query = "SELECT count(sobreedad) as total_masculino_sobreedad from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.sobreedad = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino sobreedad.
    if($ejecutar == 8)
    {
        $query = "SELECT count(sobreedad) as total_femenino_sobreedad from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.sobreedad = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
   
   //*************************************************************
   //		CONSULTA DE GRADOS PARA CONSOLIDAR RESULTADOS
      // total masculino.
    if($ejecutar == 9)
    {
        $query = "SELECT count(genero) as total_masculino from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm'";
	    
	    $result_indicadores = $db_link -> query($query);
    }

     // total femenino.
    if($ejecutar == 10)
    {
        $query = "SELECT count(genero) as total_femenino from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
    // total masculino deserción.
    if($ejecutar == 11)
    {
        $query = "SELECT count(retirado) as total_masculino_desercion from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.retirado = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino deserción.
    if($ejecutar == 12)
    {
        $query = "SELECT count(retirado) as total_femenino_desercion from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.retirado = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total masculino deserción.
    if($ejecutar == 13)
    {
        $query = "SELECT count(repitente) as total_masculino_repitente from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.repitente = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino deserción.
    if($ejecutar == 14)
    {
        $query = "SELECT count(repitente) as total_femenino_repitente from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.repitente = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
 
         // total masculino deserción.
    if($ejecutar == 15)
    {
        $query = "SELECT count(sobreedad) as total_masculino_sobreedad from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado ||codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.sobreedad = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino deserción.
    if($ejecutar == 16)
    {
        $query = "SELECT count(sobreedad) as total_femenino_sobreedad from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.sobreedad = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
      // total masculino nuevo ingreso.
    if($ejecutar == 17)
    {
        $query = "SELECT count(nuevo_ingreso) as total_masculino_nuevo_ingreso from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'm' and am.nuevo_ingreso = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
        // total femenino nuevo ingreso.
    if($ejecutar == 18)
    {
        $query = "SELECT count(nuevo_ingreso) as total_femenino_nuevo_ingreso from alumno_matricula am 
	    INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
	    where btrim(codigo_bach_o_ciclo || codigo_grado || codigo_seccion || codigo_ann_lectivo || codigo_turno) = '$codigo_all' and a.genero = 'f' and am.nuevo_ingreso = 't'";
	    
	    $result_indicadores = $db_link -> query($query);
    }
    
}