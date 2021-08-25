CREATE Function TempIndicadoresEducativos (varchar) Returns Void
as
$$
-- Eliminar Tabla Temp Indicadores Educativos.
	DELETE from temp_indicadores_educativos;
-- Insertar Valores en la Tabla Temp Indicadores Educativos.
INSERT INTO temp_indicadores_educativos
	SELECT DISTINCT btrim(org_gs.codigo_turno || org_gs.codigo_bachillerato || org_gs.codigo_ann_lectivo) as codigo_t_m_a,
		bach.nombre as nombre_modalidad, tur.nombre as nombre_turno, ann.nombre as nombre_ann_lectivo
		FROM organizacion_grados_secciones org_gs
		INNER JOIN ann_lectivo ann ON ann.codigo = org_gs.codigo_ann_lectivo 
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = org_gs.codigo_bachillerato 
		INNER JOIN turno tur ON tur.codigo = org_gs.codigo_turno
			WHERE codigo_ann_lectivo = $1 ORDER BY codigo_t_m_a;
-- Mostrar Valores de la Tabla Temp Indicadores Educativos.
	SELECT * from temp_indicadores_educativos ORDER BY codigo_modalidad_turno_ann_lectivo
$$
Language SQL

SELECT TempIndicadoresEducativos('20')

SELECT * FROM catalogo_indicadores_educativos ORDER BY codigo

SELECT count(codigo_genero) as total_ FROM alumno_matricula am
INNER JOIN alumno a ON a.id_alumno = am.codigo_alumno
WHERE btrim(codigo_bach_o_ciclo || codigo_turno || codigo_ann_lectivo) = '010220' and a.codigo_genero = '01'
and am.codigo_turno = '01'

	SELECT DISTINCT btrim(org_gs.codigo_turno || org_gs.codigo_bachillerato || org_gs.codigo_ann_lectivo) as codigo_m_t_a,
		org_gs.codigo_bachillerato, org_gs.codigo_turno,org_gs.codigo_ann_lectivo,
		bach.nombre as nombre_modalidad, tur.nombre as nombre_turno, ann.nombre as nombre_ann_lectivo
		FROM organizacion_grados_secciones org_gs
		INNER JOIN ann_lectivo ann ON ann.codigo = org_gs.codigo_ann_lectivo 
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = org_gs.codigo_bachillerato 
		INNER JOIN turno tur ON tur.codigo = org_gs.codigo_turno
			WHERE codigo_ann_lectivo = '20' ORDER BY codigo_m_t_a
			
			SELECT * from temp_indicadores_educativos ORDER BY codigo_modalidad_turno_ann_lectivo