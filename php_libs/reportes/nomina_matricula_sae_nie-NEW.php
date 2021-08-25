<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Archivos que se incluyen.
     include($path_root."/registro_academico/includes/funciones.php");
     include($path_root."/registro_academico/includes/consultas.php");
     include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
     include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
     header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
     $codigo_all = $_REQUEST["todos"];
     $db_link = $dblink;
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while ($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {

                $nombre_modalidad = utf8_decode(trim($row['nombre_bachillerato']));

                $nombre_grado = utf8_decode(trim($row['nombre_grado']));

                $nombre_seccion = utf8_decode(trim($row['nombre_seccion']));

                $nombre_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));


            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
	    break;
            }
	    
class PDF extends FPDF
{
//Cabecera de página
function Header(){
    //Logo
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_uno']; $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(150,4,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(190,4,utf8_decode('Nómina de Alumnos/as'),0,1,'C');
    $this->Line(10,20,200,20);}

//Pie de página
function Footer(){
  // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');						
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y "); $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');}

//Tabla coloreada
function FancyTable($header){
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(224,235,255);$this->SetTextColor(0);$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);$this->SetFont('','B');
    //Cabecera
    $w=array(10,12,15,80,76); //determina el ancho de las columnas
    $w2=array(10,5,12,80,75.5); //determina el ancho de las columnas
       
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],6,utf8_decode($header[$i]),1,0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);$this->SetTextColor(0);$this->SetFont('');
    //Datos
    $fill=false;}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter'); $data = array();
//Títulos de las columnas
    $header=array('Nº','Id','N I E','Nombre de Alumnos/as','ASIGNATURAS PENDIENTES DE APROBACIÓN 2020');
    $pdf->AliasNbPages(); $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(18); $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
//  imprimir datos del bachillerato.
    $pdf->Cell(105,10,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,10,$print_grado,0,0,'L');
    $pdf->Cell(20,10,$print_seccion,0,0,'L');
    $pdf->Cell(35,10,$print_ann_lectivo,0,1,'L');
// Salto de línea.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    //cabecera
    $w=array(10,12,15,80,76); //determina el ancho de las columnas
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $cambiar = true;
        while ($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $codigo_alumno = trim($row['id_alumno']);
                // revisar si hay asignaturas pendientes.
                $query_asignaturas_pendientes = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                a.genero, a.id_alumno as cod_alumno, am.id_alumno_matricula as codigo_matricula, am.codigo_bach_o_ciclo,
                bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion,
                n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.nota_final, n.codigo_asignatura, 
                asig.nombre as nombre_asignatura, asig.codigo_cc, asig.codigo_area,
                ae.codigo_alumno, ae.encargado,
                round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, 
                round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, 
                round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4+n.nota_p_p_5),1) as total_puntos_nocturna
                    FROM alumno a 
                    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
                    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
                    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
                    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
                    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
                    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
                    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
                    INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
                    INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
                        WHERE am.codigo_ann_lectivo = '20' 
                            and a.id_alumno = '$codigo_alumno'
                                and n.codigo_alumno = a.id_alumno ORDER BY apellido_alumno ASC";
            // Ejecutamos el Query. PARA LA TABLA EMPLEADOS. WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '$codigo_all' 
                $result_asignaturas_pendientes = $dblink -> query($query_asignaturas_pendientes);
            // recorrer asignaturas pendientes.
            //  crear matriz.
                $nombre_asignaturas_pendientes = array();
                while($row_ap = $result_asignaturas_pendientes -> fetch(PDO::FETCH_BOTH))
				{
                    // Variables.
                        //$nombre_asignatura = utf8_decode(substr(trim($row_ap['nombre_asignatura']),0,4));
                            $nombre_asignatura = utf8_decode(trim($row_ap['nombre_asignatura']));
                        // Cambiar la letras del nombre de la Asignatura.
                            if($nombre_asignatura == "Lenguaje y Literatura" || $nombre_asignatura == "Lenguaje"){
                                $nombre_asignatura = "L";
                            }
                            if($nombre_asignatura == "Estudios Sociales" || $nombre_asignatura == utf8_decode("Estudios Sociales y Cívica")){
                                $nombre_asignatura = "ES";
                            }
                            if($nombre_asignatura == utf8_decode("Moral, Urbanidad y Cívica") ||  $nombre_asignatura == utf8_decode("Moral, Urbanidad y Cívica.")){
                                $nombre_asignatura = "MUCi";
                            }
                            if($nombre_asignatura == utf8_decode("Matemática") || $nombre_asignatura == utf8_decode("Matematicas")){
                                $nombre_asignatura = "M";
                            }
                            if($nombre_asignatura == "Ciencia, Salud y Medio Ambiente" || $nombre_asignatura == "Ciencias Naturales"){
                                $nombre_asignatura = "CN";
                            }
                            if($nombre_asignatura == utf8_decode("Educación Artística")){
                                $nombre_asignatura = "EA";
                            }
                            if($nombre_asignatura == utf8_decode("Inglés") || $nombre_asignatura == "Idioma Extranjero"){
                                $nombre_asignatura = "I";
                            }
                            if($nombre_asignatura == utf8_decode("Educación Física")){
                                $nombre_asignatura = "EF";
                            }
                            if($nombre_asignatura == utf8_decode("Orientacion para la vida") || $nombre_asignatura == utf8_decode("Orientación para la Vida")){
                                $nombre_asignatura = "OPV";
                            }
                            if($nombre_asignatura == utf8_decode("Laboratorio de Creatividad")){
                                $nombre_asignatura = "LAB";
                            }
                            if($nombre_asignatura == utf8_decode("Práctica")){
                                $nombre_asignatura = "P";
                            }
                            if($nombre_asignatura == utf8_decode("Seminarios")){
                                $nombre_asignatura = "S";
                            }
                            if($nombre_asignatura == utf8_decode("Tecnología")){
                                $nombre_asignatura = "T";
                            }
                        $codigo_area = trim($row_ap['codigo_area']);
                        $codigo_modalidad = trim($row_ap['codigo_bach_o_ciclo']);
                        $codigo_matricula = trim($row_ap['codigo_matricula']);
                        $nota_final = $row_ap['nota_final']; 
                        $total_puntos_basica = $row_ap['total_puntos_basica']; 
                        $total_puntos_media = $row_ap['total_puntos_media']; 
					// VALIDAR SI LA NOTA FINAL ES MAYOR DE.... DEPENDIENDO DE LA MODALDIAD.
					// EVALUAR EN EL CASO DE EDUCACIÓN BASICA, TERCER CICLO Y NOCTURNMA
					if($codigo_area == '01' || $codigo_area == '02' || $codigo_area == '03' || $codigo_area == '08'){
						if($codigo_modalidad == '03' || $codigo_modalidad == '04' || $codigo_modalidad == '05' || $codigo_modalidad == '10'){
							if(round($row_ap['nota_final'],0) < 5){
								$guardar_registro = 1;
                                $nombre_asignaturas_pendientes[] = $nombre_asignatura . ":".$total_puntos_basica . " ";
							}else{
								$guardar_registro = 0;
							}
						}else if($codigo_modalidad == '06' || $codigo_modalidad == '07' || $codigo_modalidad == '08' || $codigo_modalidad == '09' || $codigo_modalidad == '11'){
							if(round($row_ap['nota_final'],0) < 6){
								$guardar_registro = 1;
                                $nombre_asignaturas_pendientes[] = $nombre_asignatura . ":".$total_puntos_media . " ";
							}else{
								$guardar_registro = 0;
							}
						}
					}else{
						// No guardar registro
							$guardar_registro = 0;
					}

					//
					if($guardar_registro == 1){
						// BACHILLERATO
						if($codigo_modalidad >= '06' && $codigo_modalidad <= '09'){

						}
						// BACHILLERATO NOCTURNA
						if($codigo_modalidad >= '10' && $codigo_modalidad <= '12'){

						}	
						////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
					}
				}    //  FIN DEL WHILE. FINAL DEL RECORRIDO DE ASIGNATURAS REPROBADAS

            $pdf->Cell($w[0],5,$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],5,trim($row['id_alumno']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[2],5,trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[3],5,utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
            // Imprimir valores de matriz
            $separado_por_comas = implode(",", $nombre_asignaturas_pendientes);
            // Contar si hay elementos en la matriz.
            if(count($nombre_asignaturas_pendientes) > 0){
                $update_matricula = "UPDATE alumno_matricula SET imprimir_foto = 'true' WHERE id_alumno_matricula = '$codigo_matricula' and codigo_alumno = '$codigo_alumno'";
                    $result_matricula = $dblink -> query($update_matricula);
            }
            // Imprimir en la celda.
            $pdf->Cell($w[4],5,$separado_por_comas,'LR',0,'C',$fill);  // col1
            //$pdf->MultiCell($w[4],5,$separado_por_comas,'LR',1,'C',$fill,2);  // col1
                  
            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;	// conteo de i++
        } //cierre del do while.          
             // rellenar con las lineas que faltan y colocar total de puntos y promedio.
                $numero = $i;
                $linea_faltante =  45 - $numero;
                $numero_p = $numero - 1;
		     for($i=0;$i<=$linea_faltante;$i++){
				$pdf->SetX(10);
				$pdf->Cell($w[0],5,$numero++,'LR',0,'C',$fill);  // N| de Orden.
				$pdf->Cell($w[1],5,'','LR',0,'l',$fill);  // nombre del alumno.
				$pdf->Cell($w[2],5,'','LR',0,'C',$fill);  // id
                $pdf->Cell($w[3],5,'','LR',0,'C',$fill);  // NIE
	    
				$pdf->Cell($w[4],5,'','LR',0,'C',$fill);  // col1
				$pdf->Ln();   
				$fill=!$fill;}
// Cerrando Línea Final.
   $pdf->Cell(array_sum($w),0,'','T');
   $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
// Salida del pdf.
    $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
    $print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-AP.pdf';
    $pdf->Output($print_nombre,$modo);
?>