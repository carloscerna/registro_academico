<?php
// Limpiar caché del servidor
clearstatcache();

// Configuración de encabezados y codificación nativa del sistema
header("Content-Type: application/json; charset=utf-8");

// Inicialización de variables JSON estándar de tu arquitectura
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = array();
$auditoriaOK = array("total" => 0, "con_datos" => 0, "sin_datos" => 0);

// Definición de la ruta raíz del servidor
$path_root = trim($_SERVER['DOCUMENT_ROOT']);

// Inclusión de la librería de funciones principales y conexión PDO ($dblink)
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");

// 1. Validar conexión con la base de datos PostgreSQL
if ($errorDbConexion == false) {
    
    // Validamos que existan variables en el bloque POST
    if (isset($_POST) && !empty($_POST)) {
        
        if (!empty($_POST['accion_buscar'])) {
            $_POST['accion'] = $_POST['accion_buscar'];
        }

        switch ($_POST['accion']) {
            
            case 'ConsolidarMesInstitucional':
                // Captura de parámetros sanitizados
                $codigo_ann_lectivo = trim($_POST['lstannlectivo']); 
                $mes_consultar = (int)$_POST['lstmes'];

                // =========================================================================
                // QUERY 1: OBTENER INFRAESTRUCTURA DESDE public.organizacion_grados_secciones
                // =========================================================================
                // Nota: Relacionamos con una tabla intermedia hipotética 'encargado_grado' 
                // para obtener el 'id_encargado_grado' indispensable en la consulta de deméritos.
                $query_secciones = "SELECT 
                                        eg.id_encargado_grado,
                                        ogs.codigo_bachillerato, 
                                        ogs.codigo_grado, 
                                        ogs.codigo_seccion, 
                                        ogs.codigo_turno,
                                        btrim(g.nombre || ' ' || s.nombre || ' - ' || t.nombre) as nombre_seccion_completa
                                    FROM public.organizacion_grados_secciones ogs
                                    INNER JOIN public.encargado_grado eg 
                                        ON eg.codigo_bachillerato = ogs.codigo_bachillerato 
                                       AND eg.codigo_grado = ogs.codigo_grado 
                                       AND eg.codigo_seccion = ogs.codigo_seccion 
                                       AND eg.codigo_turno = ogs.codigo_turno
                                       AND eg.codigo_ann_lectivo = ogs.codigo_ann_lectivo
                                    INNER JOIN grado_ano g ON g.codigo = ogs.codigo_grado
                                    INNER JOIN seccion s ON s.codigo = ogs.codigo_seccion
                                    INNER JOIN turno t ON t.codigo = ogs.codigo_turno
                                    WHERE ogs.codigo_ann_lectivo = :ann
                                    ORDER BY ogs.codigo_grado, ogs.codigo_seccion, ogs.codigo_turno";

                $stmt_secciones = $dblink->prepare($query_secciones);
                $stmt_secciones->execute(array(':ann' => $codigo_ann_lectivo));

                if ($stmt_secciones->rowCount() > 0) {
                    
                    $total_secciones = 0;
                    $secciones_con_datos = 0;
                    $secciones_sin_datos = 0;

                    while ($seccion = $stmt_secciones->fetch(PDO::FETCH_ASSOC)) {
                        $total_secciones++;
                        
                        $id_encargado = $seccion['id_encargado_grado'];
                        $c_bach       = $seccion['codigo_bachillerato'];
                        $c_grado      = $seccion['codigo_grado'];
                        $c_seccion    = $seccion['codigo_seccion'];
                        $c_turno      = $seccion['codigo_turno'];

                        // =========================================================================
                        // QUERY 2: CALCULAR MATRÍCULA ACTUAL DESDE public.alumno_matricula
                        // =========================================================================
                        // Usamos la bandera nativa 'retirado = false' en vez de codigos de estatus externos
                        $query_mat = "SELECT 
                                        COUNT(CASE WHEN al.genero = 'm' THEN 1 END) as mat_h,
                                        COUNT(CASE WHEN al.genero = 'f' THEN 1 END) as mat_m
                                      FROM public.alumno_matricula am
                                      INNER JOIN public.alumno al ON al.id_alumno = am.codigo_alumno
                                      WHERE am.codigo_ann_lectivo = :ann 
                                        AND am.codigo_bach_o_ciclo = :bach
                                        AND am.codigo_grado = :gra 
                                        AND am.codigo_seccion = :sec 
                                        AND am.codigo_turno = :tur
                                        AND am.retirado = false"; 

                        $stmt_mat = $dblink->prepare($query_mat);
                        $stmt_mat->execute(array(
                            ':ann'  => $codigo_ann_lectivo,
                            ':bach' => $c_bach,
                            ':gra'  => $c_grado,
                            ':sec'  => $c_seccion,
                            ':tur'  => $c_turno
                        ));
                        $res_mat = $stmt_mat->fetch(PDO::FETCH_ASSOC);

                        // =========================================================================
                        // QUERY 3: CONSULTAR TABLA REAL public.alumnos_demeritos
                        // =========================================================================
                        // Importante: Tu tabla define 'codigo_ann_lectivo' como character(2), lo que implica
                        // que probablemente guardes '26' en lugar de '2026'. Aplicamos un substr protectivo.
                        $ann_corto = substr($codigo_ann_lectivo, -2); 

                        $query_datos = "SELECT * FROM public.alumnos_demeritos 
                                        WHERE id_encargado_grado = :id_encargado
                                          AND mes_evaluacion = :mes
                                          AND codigo_ann_lectivo = :ann_corto";

                        $stmt_datos = $dblink->prepare($query_datos);
                        $stmt_datos->execute(array(
                            ':id_encargado' => $id_encargado,
                            ':mes'          => $mes_consultar,
                            ':ann_corto'    => $ann_corto
                        ));

                        // Estructura de contingencia (Valores en 0 si no se ha registrado el mes)
                        $tiene_datos = false;
                        $dem_m = 0; $dem_h = 0;
                        $c_a = 0;   $c_b = 0;   $c_c = 0; $c_d = 0;
                        $red_m = 0; $red_h = 0;
                        $r_a = 0;   $r_b = 0;   $r_c = 0;
                        $rec_m = 0; $rec_h = 0;

                        if ($stmt_datos->rowCount() > 0) {
                            $tiene_datos = true;
                            $secciones_con_datos++;
                            $row_d = $stmt_datos->fetch(PDO::FETCH_ASSOC);
                            
                            // Mapeo uno a uno con las columnas exactas de tu estructura física
                            $dem_h = $row_d['total_demeritos_hombres'];
                            $dem_m = $row_d['total_demeritos_mujeres'];
                            $c_a   = $row_d['dem_causal_a'];
                            $c_b   = $row_d['dem_causal_b'];
                            $c_c   = $row_d['dem_causal_c'];
                            $c_d   = $row_d['dem_causal_d'];
                            
                            $red_h = $row_d['redenciones_hombres'];
                            $red_m = $row_d['redenciones_mujeres'];
                            $r_a   = $row_d['redencion_opcion_a'];
                            $r_b   = $row_d['redencion_opcion_b'];
                            $r_c   = $row_d['redencion_opcion_c'];
                            
                            $rec_h = $row_d['reconocimientos_hombres'];
                            $rec_m = $row_d['reconocimientos_mujeres'];
                        } else {
                            $secciones_sin_datos++;
                        }

                        // Construcción de la matriz de datos para renderizar la tabla gerencial
                        $contenidoOK[] = array(
                            "nombre_seccion" => $seccion['nombre_seccion_completa'],
                            "tiene_datos"    => $tiene_datos,
                            "mat_h"          => $res_mat['mat_h'] ?? 0,
                            "mat_m"          => $res_mat['mat_m'] ?? 0,
                            "dem_h"          => $dem_h,
                            "dem_m"          => $dem_m,
                            "c_a"            => $c_a,
                            "c_b"            => $c_b,
                            "c_c"            => $c_c,
                            "c_d"            => $c_d,
                            "red_h"          => $red_h,
                            "red_m"          => $red_m,
                            "r_a"            => $r_a,
                            "r_b"            => $r_b,
                            "r_c"            => $r_c,
                            "rec_h"          => $rec_h,
                            "rec_m"          => $rec_m
                        );
                    }

                    // Datos finales para los bloques informativos KPI superiores
                    $auditoriaOK = array(
                        "total"     => $total_secciones,
                        "con_datos" => $secciones_con_datos,
                        "sin_datos" => $secciones_sin_datos
                    );

                    $respuestaOK = true;
                    $mensajeError = "Consolidado institucional generado correctamente.";

                } else {
                    $respuestaOK = false;
                    $mensajeError = "No se encontraron registros de planificación en la tabla de organización de grados y secciones.";
                }
            break;

            default:
                $mensajeError = 'La acción solicitada no es válida en este módulo.';
            break;
        }
    } else {
        $mensajeError = 'Parámetros POST vacíos.';
    }
} else {
    $mensajeError = 'Error crítico: No se pudo establecer comunicación con el motor PostgreSQL.';
}

// Retorno JSON formateado bajo los estándares de tu sistema
$salidaJson = array(
    "respuesta" => $respuestaOK,
    "mensaje"   => $mensajeError,
    "contenido" => $contenidoOK,
    "auditoria" => $auditoriaOK
);

echo json_encode($salidaJson);
exit;
?>