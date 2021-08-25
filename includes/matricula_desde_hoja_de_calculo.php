<?php
$respuestaOK = true;
$contenidoOK = "Matricula satisfactoria.";
$mensajeError = "";
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
include($path_root."/registro_web/includes/mainFunctions_conexion.php");
include($path_root."/registro_web/includes/funciones.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
   $ruta = '../files/matricula/' . trim($_REQUEST["nombre_archivo_"]);
   $codigo_annlectivo = trim($_REQUEST["codigo_annlectivo"]);
   $codigo_modalidad = trim($_REQUEST["codigo_modalidad"]);
   $codigo_grado_seccion = trim($_REQUEST["codigo_grado_seccion"]);
   $codigo_grado = substr($codigo_grado_seccion,0,2);
   $codigo_seccion = substr($codigo_grado_seccion,2,2);
   $codigo_turno = substr($codigo_grado_seccion,4,2);
   //print "1: ".$codigo_modalidad . "2: ". $codigo_grado_seccion;
   //print "<br>Codigo grado: $codigo_grado";
   //print "<br>Codigo seccion: $codigo_seccion";
   //print "<br>Codigo turno: $codigo_turno";
// variable de la conexión dbf.
    $db_link = $dblink;
// Inicializando el array
$datos=array(); $fila_array = 0;
// call the autoload
    require $path_root."/registro_web/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Read\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Time zone.
    //echo date('H:i:s') . " Set Time Zone"."<br />";
    //date_default_timezone_set('America/El_Salvador');
// set codings.
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007
   $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
	 $fila = 11;
    $objPHPExcel = $objReader->load($origen);

// Número de hoja.
   $numero_de_hoja = 0;
	$numero = 5;	
// 	Recorre el numero de hojas que contenga el libro
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue() != "")
		  {
			 //  DATOS GENERALES.
				$nie = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getCalculatedValue();
            $nombre_completo = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getCalculatedValue();
            $partes = explode(" ", $nombre_completo);

            $apellido_paterno = trim(str_replace(","," ",$partes[0]));
            $apellido_materno = trim(str_replace(","," ",$partes[1]));
            $nombre_completo = trim($partes[2]) . " " . trim($partes[3]);

            $codigo_genero = "01";
				$fecha_nacimiento = "01/01/2019";
            /////////////////////////////////////////////////////////////////////////////////////////////////////
				// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
            /////////////////////////////////////////////////////////////////////////////////////////////////////
               $codigo_estatus = '01';
               $encargado_p = array("Otro");
               if ($encargado_p[0] == 'Padre'){$en = 't';}else{$en = 'f';}
					if ($encargado_p[0] == 'Madre'){$en1 = 't';}else{$en1 = 'f';}
					if ($encargado_p[0] == 'Otro'){$en2 = 't';}else{$en2 = 'f';}
					
					// cambiar el valor para genero por el campo que lo toma como "m" o "f"
					if($codigo_genero == '01'){$genero = "m";}else{$genero = "f";}
					$encargado = array($en,$en1,$en2);
					///////////////////////////////////////////////////////////////////////////////////////////////////
               // EVALUAR SI EXISTE.
               //////////////////////////////////////////////////////////////////////////////////////////////////
               	$query_v = "SELECT * from alumno WHERE codigo_nie = '$nie'";
					// Ejecutamos el Query.
               	$consulta_v = $dblink -> query($query_v);
                  if($consulta_v -> rowCount() != 0){                  
                     $contenidoOK = "Matricula Ya existe.";
                     $respuestaOK = false;
                  }else{
                       $query = "INSERT INTO alumno (apellido_materno, apellido_paterno, nombre_completo, codigo_nie, fecha_nacimiento, genero, codigo_genero, codigo_estatus)
                           VALUES ('$apellido_materno','$apellido_paterno','$nombre_completo','$nie',
                           '$fecha_nacimiento','$genero','$codigo_genero','$codigo_estatus')";
            
                        // Ejecutamos el query
                        $resultadoQuery = $dblink -> query($query);
                        // Obtenemos el id de user para edición
                        $query_ultimo = "SELECT id_alumno from alumno ORDER BY id_alumno DESC LIMIT 1 OFFSET 0";
                        // Ejecutamos el Query.
                        $consulta = $dblink -> query($query_ultimo);
                        // Recorremos la consulta para el ultimo id y posteriormente guardarlo en alumno_encargado.
                        while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                        {
                           // obtenemos el último código asignado.
                           $codigo_alumno = $listado['id_alumno'];
                        }
                        // Agregar valores para el encargado.
                        // Actualizar valores del encargado.
                        for ($i=0;$i<=2;$i++){
                           $query_encargado ="INSERT INTO alumno_encargado (codigo_alumno,encargado, fecha_nacimiento) VALUES ($codigo_alumno,'$encargado[$i]','$fecha_nacimiento')";
                           // Ejecutamos el query guardar los datos en la tabla alumno..
                              $resultadoQueryEncargado = $dblink -> query($query_encargado);				
                           }
                     /////////////////////////////////////////////////////////////////////////////////////////////////////
                     // Armar query para guardar en la tabla NOTA
                     /////////////////////////////////////////////////////////////////////////////////////////////////////         
                  // armar variables.  TABS-1
                     $codigo_todos = $codigo_modalidad.$codigo_grado.$codigo_annlectivo;
                     $pn = "t";
                     $certificado = "t";
                  
                     // armar query.	
                     $query = "SELECT a.estudio_parvularia, a.apellido_materno, a.apellido_paterno, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                         btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo, a.codigo_estatus,
                         ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.telefono,
                         a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero,
                         a.id_alumno as cod_alumno, am.id_alumno_matricula as cod_matricula,
                         am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado,
                         am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo,
                         am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion,
                         am.codigo_turno, tur.nombre as nombre_turno
                         FROM alumno a
                         INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                         INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno
                         INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                         INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                         INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                         INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                         INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                         WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_ann_lectivo) = '".$codigo_todos."' and am.codigo_alumno = '".$codigo_alumno.
                         "' ORDER BY apellido_alumno ASC";
                  
                     // Ejecutamos el Query.
                     $consulta = $dblink -> query($query);
                     if($consulta -> rowCount() != 0){
                        $num = 0;
                        // convertimos el objeto
                        while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                        {
                           $num++;
                           $id_alumno = $listado['id_alumno'];
                           $nombre_grado = (trim($listado['nombre_grado']));
                           $nombre_seccion = (trim($listado['nombre_seccion']));
                           $nombre_modalidad = (trim($listado['nombre_bachillerato']));
                           $nombre_turno = (trim($listado['nombre_turno']));
                           $apellidos_alumno = (trim($listado['apellido_alumno']));
                           
                           $todos_busqueda = $apellidos_alumno . " - " . $nombre_modalidad . " - " . $nombre_grado . " - " . $nombre_seccion . " - " . $nombre_turno;
                           
                          /* $contenidoOK .= '<tr><td class=centerTXT>'.$num
                              .'<td class=centerTXT>'.$todos_busqueda
                              ;*/
                        }
                        $mensajeError = "Matricula Ya Existe.";
                        $respuestaOK = false;
                     }
                     else{
                        // Extraer el código estatus u otros datos.
                        while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
                           {
                              $codigo_estatus = $listado['codigo_estatus'];
                           }
                        // graba el codigo del alumno en la tabla alumno matricula. para poder generar el codigo matricula.
                           $query_matricula = "INSERT INTO alumno_matricula (codigo_alumno) VALUES (".$codigo_alumno.")";
                        // Ejecutamos el Query.
                           $consulta = $dblink -> query($query_matricula);
                     
                        // Consultar a la tabla el ultimo ingresado de la tabla alumno matricula, para que pueda generar el codigo de la matricula.				
                           $query_consulta_matricula = "SELECT codigo_alumno, id_alumno_matricula from alumno_matricula where codigo_alumno = ".$codigo_alumno." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
                        // Ejecutamos el Query.
                           $result_consulta = $dblink -> query($query_consulta_matricula);
                              while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                 {$fila_alumno = $row{0}; $fila_matricula = $row{1};}
                            
                        // Actualizar la tabla alumno_matricula con codigos de bachillerato, grado, seccion, año lectivo y año lectivo.
                           $query_update_matricula = "UPDATE alumno_matricula SET codigo_bach_o_ciclo = '$codigo_modalidad',
                              codigo_grado = '$codigo_grado',
                              codigo_seccion = '$codigo_seccion',
                              codigo_ann_lectivo ='$codigo_annlectivo',
                              certificado = '$certificado',
                              pn = '$pn',
                              codigo_turno = '$codigo_turno'
                           WHERE codigo_alumno = ".$fila_alumno." and id_alumno_matricula = ".$fila_matricula;
                        // Ejecutar Query.
                           $consulta = $dblink -> query($query_update_matricula);
               
                        // Consultar a la tabla alumno_matricula y codigo bachillerato o ciclo.
                           $query_consulta = "SELECT codigo_bach_o_ciclo from alumno_matricula where codigo_alumno = ".$codigo_alumno." ORDER BY id_alumno_matricula DESC LIMIT 1 OFFSET 0";
                           $result_consulta = $dblink -> query($query_consulta);
                              while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                                 {$fila_codigo_bachillerato = $row{0};}
               
                        // Consultar a la tabla codigo asignatura, para generar el codigo individual de cada una de ellas segun el ciclo o bachillerato.
                        $query_consulta_asignatura = "SELECT codigo_asignatura FROM a_a_a_bach_o_ciclo WHERE codigo_bach_o_ciclo = '".$fila_codigo_bachillerato."' and codigo_ann_lectivo = '".$codigo_annlectivo."' and codigo_grado = '".$codigo_grado."' ORDER BY codigo_asignatura ASC";
                           $result_consulta = $dblink -> query($query_consulta_asignatura);
                              while($row = $result_consulta -> fetch(PDO::FETCH_BOTH))
                              {
                                 $fila_codigo_asignatura = $row{0};      
                                 $query_insert = "INSERT INTO nota (codigo_asignatura, codigo_alumno, codigo_matricula) VALUES ('$fila_codigo_asignatura',$fila_alumno,$fila_matricula)";
                                 $result_consulta_insert_notas = $dblink -> query($query_insert);
                              }
                                 
                        // variables de response.
                        $respuestaOK = true;
                        $contenidoOK = "Matricula satisfactoria.";
                        $mensajeError = "Alumno(a) Matriculado.".$query_consulta_asignatura;
                     }
                  }// EVALUAR SI EL REGISTRO YA EXISTE.
            /////////////////////////////////////////////////////////////////////////////////////////////////////
				// MOSTRAR DATOS EN PANTALLA
            /////////////////////////////////////////////////////////////////////////////////////////////////////
         	$fila++;
            if($respuestaOK)
            {
              // print "Apellido Paterno: $apellido_paterno Apellido Materno: $apellido_materno Nombres: $nombre_completo Codigo Genero: $codigo_genero F.N.: $fecha_nacimiento NIE: $nie";
               //print "<br>";   
            }else{
               //print $contenidoOK;
               //print "<br>";   
            }
            
           // print $contenidoOK;
            //print $mensajeError;
            //print "<br>";
		}	// FIN DEL WHILE PRINCIPAL DE L AHOJA DE CALCULO.
// Enviando la matriz con Json.
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
echo json_encode($salidaJson);
?>