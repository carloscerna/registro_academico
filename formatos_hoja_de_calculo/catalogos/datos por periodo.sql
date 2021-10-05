SELECT DISTINCT a.id_alumno, a.codigo_nie, btrim(a.nombre_completo || CAST(' ' as VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
                            am.id_alumno_matricula as codigo_matricula, am.codigo_bach_o_ciclo as codigo_modalidad,
                            n.codigo_asignatura, n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.nota_final, n.observacion_1, n.observacion_2, n.observacion_3, n.observacion_4, n.observacion_5,
                            asig.nombre as nombre_asignatura, asig.codigo_area, cat_area.descripcion as nombre_area, asig.codigo_cc, cat_cc.descripcion as nombre_concepto,
							bach.nombre as nombre_modalidad, gr.codigo as codigo_grado, gr.nombre as nombre_grado, sec.codigo as codigo_seccion, sec.nombre as nombre_seccion, cat_tur.codigo as codigo_turno, cat_tur.nombre as nombre_turno,
							aaa.orden
                            FROM alumno a
								INNER JOIN alumno_matricula am ON am.codigo_alumno = a.id_alumno and am.codigo_ann_lectivo = '20'
								INNER JOIN nota n ON n.codigo_matricula = am.id_alumno_matricula
								INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
								INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
								INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
								INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
								INNER JOIN grado_ano gr ON gr.codigo = am.codigo_grado
								INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
								INNER JOIN turno cat_tur ON cat_tur.codigo = am.codigo_turno
								INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = n.codigo_asignatura and aaa.orden <> 0
									WHERE n.nota_p_p_1 = 0
										ORDER BY codigo_modalidad, codigo_grado, codigo_seccion, aaa.orden