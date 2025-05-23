<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
    $codigo_all = $_REQUEST["todos"];
    $cod_por_asignatura = $_REQUEST["lstasignatura"];
    $db_link = $dblink;
    $j = 0;  $data = array();
// buscar la consulta y la ejecuta.
    consultas(18,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = convertirtexto('Modalidad: '.trim($row['nombre_bachillerato']));
            $codigo_bachillerato = trim($row['codigo_bachillerato']);
            $print_grado = convertirtexto('Grado:     '.trim($row['nombre_grado']));
            $nombre_grado = convertirtexto(trim($row['nombre_grado']));
            $print_seccion = convertirtexto('Sección:  '.trim($row['nombre_seccion']));
            $nombre_seccion = convertirtexto(trim($row['nombre_seccion']));
            $print_ann_lectivo = convertirtexto('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
            $codigo_grado = convertirtexto(trim($row['codigo_grado']));
	    break;
            }

// buscar el nombre de la asignatura.
   consultas(7,0,$codigo_all,$cod_por_asignatura,'','',$db_link,'');     
//  imprimir datos del bachillerato.
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $nombre_asignatura = convertirtexto(trim($row['n_asignatura']));
	    break;
            }
            try {
                // Consulta para obtener el Encargado de Grado
                $query_encargado = "
                    SELECT 
                        cd.id_carga_docente, 
                        TRIM(p.nombres || ' ' || p.apellidos) AS nombre_docente, 
                        cd.codigo_docente, 
                        bach.nombre AS bach_nombre, 
                        gann.nombre AS grado_nombre, 
                        sec.nombre AS seccion_nombre, 
                        ann.nombre AS ann_nombre
                    FROM carga_docente cd
                    INNER JOIN personal p ON cd.codigo_docente::int = p.id_personal
                    INNER JOIN bachillerato_ciclo bach ON cd.codigo_bachillerato = bach.codigo
                    INNER JOIN ann_lectivo ann ON cd.codigo_ann_lectivo = ann.codigo
                    INNER JOIN grado_ano gann ON cd.codigo_grado = gann.codigo
                    INNER JOIN seccion sec ON cd.codigo_seccion = sec.codigo
                    WHERE TRIM(cd.codigo_bachillerato || cd.codigo_grado || cd.codigo_seccion || cd.codigo_ann_lectivo || cd.codigo_turno) = :codigo_all
                      AND cd.codigo_asignatura = :cod_por_asignatura
                    ORDER BY nombre_docente";
                
                // Preparar y ejecutar la consulta usando parámetros vinculados
                $stmt = $db_link->prepare($query_encargado);
                $stmt->execute([
                    ':codigo_all'         => $codigo_all,
                    ':cod_por_asignatura'  => $cod_por_asignatura
                ]);
                
                // Se asume que solo se necesita el primer resultado
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $nombre_encargado = cambiar_de_del(trim($row['nombre_docente']));
                    $codigo_docente  = trim($row['codigo_docente']);
                } else {
                    // Manejo del caso en que no se encuentre ningún registro
                    $nombre_encargado = '';
                    $codigo_docente  = '';
                    // Se podría registrar un log o definir otro comportamiento según el contexto.
                }
                
            } catch (PDOException $e) {
                error_log("Consulta Fallida - Encargado: " . $e->getMessage());
                die("Consulta Fallida - Encargado");
            }
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    //  Variables globales.
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_periodo, $pagina_impar, $nombre_asignatura, $nombre_encargado;
        //Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
        $this->Image($img,5,5,25,30);
        //Arial bold 15
        $this->SetFont('Arial','B',13);
        //Título
        $this->RotatedText(35,10,convertirtexto($_SESSION['institucion']),0);
        $this->RotatedText(35,15,convertirtexto('Notas - Por Asignatura - Todos Los Períodos'),0);
        
        $this->SetFont('Arial','',9);
        // Imprimir Modalidad y Asignatura.
        $this->RoundedRect(34, 16, 130, 6, 1.5, '');
        $this->RotatedText(35,20.5,$print_bachillerato,0);
        // Nombre Asignatura.
        $this->RoundedRect(34, 22, 130, 6, 1.5, '');
        $this->RotatedText(35,26,'Nombre Asignatura: '.$nombre_asignatura,0);
        // Nombre Docente
        $this->RoundedRect(34, 28, 130, 6, 1.5, '');
        $this->RotatedText(35,32.5,'Nombre Docente: ' . $nombre_encargado,0);
        
    // Generar el cuadro en donde se ubicara el grado, sección y año lectivo.
        $this->RoundedRect(169, 10, 35, 20, 3.5, '');
        $this->RotatedText(170,15,$print_grado,0);
        $this->RotatedText(170,19,$print_seccion,0);
        $this->RotatedText(170,23,$print_ann_lectivo,0);
        $this->RotatedText(170,27,$print_periodo,0);
    //Colores, ancho de línea y fuente en negrita
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetDrawColor(0,0,0);
		$this->SetLineWidth(.3);
		$this->SetFont('Arial','B',8);
		// Crear Rectangulos.
		$this->SetFillColor(224,248,252);
		$this->Rect(5,40,207,15,true); // Principal.
		$this->SetFillColor(255);
        $w=array(8,15,85,10,10,10,10,10,10,10,10,10,10); //determina el ancho de las columnas
        $linea_ancho = 0;
        for ($xx=0; $xx < count($w); $xx++) { 
            if($xx == 0){
                $this->Rect(5,40,8,15); // Número.
                $linea_ancho = $linea_ancho + $w[$xx];
            }else{
                $linea_ancho = $linea_ancho + $w[$xx];
                $this->Rect(5,40,$linea_ancho,15); // Número.
            }
            
        }
		/* $this->Rect(5,40,8,15); // Número.
        $this->Rect(5,40,28,15); // NIE.
		$this->Rect(5,40,113,15); // Orden Alfabetico
		$this->Rect(5,40,123,15); // P1
		$this->Rect(5,40,133,15); // P2
		$this->Rect(5,40,143,15); // P3
		$this->Rect(5,40,153,15); // P4
		$this->Rect(5,40,163,15); // total puntos
		$this->Rect(5,40,173,15); // Promedio 4 periodos
		$this->Rect(5,40,183,15); // Nota recuperacion
        $this->Rect(5,40,193,15); // nota recuperacion 2
		$this->Rect(5,40,203,15); // nota final
		$this->Rect(5,40,217,15); //porcentaje institucional */
		// Establecer ubicación Y.
		$this->SetY(40);
		//Cabecera tamaño de ancho y alto y array para los encabezados.
		$contenido_encabezado = array('Nº','NIE','Orden Alfabético (Por Apellidos - nombres)','P1','P2','P3','P4','TP','PP 4','NR1','NR2','NF', '%Ins');
		//un arreglo con su medida  a lo ancho
		$this->SetWidths(array(8,15,85,10,10,10,10,10,10,10,10,10,10));
		//un arreglo con alineacion de cada celda
		$this->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C'));
		// Ubicación de la Información y tipo de letra.
      	$y=$this->GetY();
		$x=$this->GetX();
		$this->SetXY($x,$y);
		$this->SetFont('Arial','B',8);
		//OTro arreglo pero con el contenido convertirtexto es para que escriba bien los acentos. 
		$this->Row(array(convertirtexto($contenido_encabezado[0]),convertirtexto($contenido_encabezado[1]),convertirtexto($contenido_encabezado[2]),convertirtexto($contenido_encabezado[3]),convertirtexto($contenido_encabezado[4]),convertirtexto($contenido_encabezado[5])
						 ,convertirtexto($contenido_encabezado[6]),convertirtexto($contenido_encabezado[7]),convertirtexto($contenido_encabezado[8]),convertirtexto($contenido_encabezado[9]),convertirtexto($contenido_encabezado[10]),convertirtexto($contenido_encabezado[11]),
                            convertirtexto($contenido_encabezado[12])));
		//Restauración de colores y fuentes
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetFont('');
		//Datos
		$fill=false;
		$this->SetFont('Arial','',9);
}

//Pie de página
function Footer()
{
  // Establecer formato para la fecha.
  // 
  	date_default_timezone_set('America/El_Salvador');
   	setlocale(LC_TIME, 'spanish');
  //
    //Posición: a 1,5 cm del final
    $this->SetY(-15);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
}

//Tabla coloreada
function FancyTable($header)
    {   
    }
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetXY(5,55);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $w=array(8,15,85,10,10,10,10,10,10,10,10,10,10); //determina el ancho de las columnas
    $h=array(7,12); //determina el alto de las columnas    
    // colores del fondo, texto, línea.
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    $numero_linea = 1; $fill=false; $porcentaje_institucional = 0; $incremento_fila = 30; $i = 1;
       consultas(7,0,$codigo_all,$cod_por_asignatura,'','',$db_link,'');
        while($row = $result -> fetch(PDO::FETCH_BOTH))
        {
            // >Impresión de los promedios para las asignaturas.
            $pdf->SetX(5);
				$pdf->SetFont('Arial','',9);
                $pdf->Cell($w[0],$h[0],$i,0,0,'C',$fill);
                $pdf->SetFont('Arial','',9);
                $pdf->Cell($w[1],$h[0],(trim($row['codigo_nie'])),0,0,'L',$fill);   // CODIGO NIE
				$pdf->Cell($w[2],$h[0],convertirtexto(trim($row['apellido_alumno'])),0,0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                if(trim($row['nota_p_p_1']) == 0){$pdf->Cell($w[3],$h[0],'',0,0,'C',$fill);}else{$pdf->Cell($w[3],$h[0],trim($row['nota_p_p_1']),0,0,'C',$fill);}
                if(trim($row['nota_p_p_2']) == 0){$pdf->Cell($w[4],$h[0],'',0,0,'C',$fill);}else{$pdf->Cell($w[4],$h[0],trim($row['nota_p_p_2']),0,0,'C',$fill);}
                if(trim($row['nota_p_p_3']) == 0){$pdf->Cell($w[5],$h[0],'',0,0,'C',$fill);}else{$pdf->Cell($w[5],$h[0],trim($row['nota_p_p_3']),0,0,'C',$fill);}
                if(trim($row['nota_p_p_4']) == 0){$pdf->Cell($w[6],$h[0],'',0,0,'C',$fill);}else{$pdf->Cell($w[6],$h[0],trim($row['nota_p_p_4']),0,0,'C',$fill);}
				// total de Puntos
				$pdf->SetFont('Arial','B',10);
                $pdf->Cell($w[7],$h[0],trim($row['total_puntos_media']),0,0,'C',$fill);
                // Promedio Cuatro Peridos
                $pdf->SetFont('Arial','B',10);
                if(trim($row['nota_final']) == 0){$pdf->Cell($w[8],$h[0],'',0,0,'C',$fill);}else{$pdf->Cell($w[8],$h[0],trim($row['nota_final']),0,0,'C',$fill);}
                // Calcular el porcentaje institucional.
                $porcentaje_institucional = round($row['nota_final'] * 0.75,1);
                // Nota Recuperación, Recuperación 2 y Final.
						if(trim($row['recuperacion']) <> 0){$pdf->Cell($w[9],$h[0],trim($row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[9],$h[0],'',0,0,'C',$fill);}
                        if(trim($row['nota_recuperacion_2']) <> 0){$pdf->Cell($w[9],$h[0],trim($row['nota_recuperacion_2']),0,0,'C',$fill);}else{$pdf->Cell($w[9],$h[0],'',0,0,'C',$fill);}
                        // calculo de la calificacion
						if(verificar_nota($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2'] != 0)){
                            $nota_ = verificar_nota($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']);
                                if($nota_ < 6){
                                    $nota_ = verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']);
                                }
                            $pdf->Cell($w[10],$h[0],$nota_,0,0,'C',$fill);
                        }else{
                            $pdf->Cell($w[10],$h[0],'',0,0,'C',$fill);
                        }
                // Porcentaje Institucional
                $pdf->SetFont('Arial','',10);
                  $pdf->Cell($w[11],$h[0],$porcentaje_institucional,0,0,'C',$fill);
                $pdf->Ln();
                if($i >= $incremento_fila){
                    $pdf->Cell(array_sum($w),0,'','T');  $pdf->AddPage();
                      // Incrementar el valor de la fila.
                       $incremento_fila = $incremento_fila + 30;
                       // Fijamos la posición de X y Y.
                           $pdf->SetY(55);
                           $pdf->SetX(10);
                           $pdf->FancyTable($header);}  
               
               // cambiamos el fondo de la lineas e incrementamos $i=fila.
                 $fill=!$fill;
                 $i=$i+1;
        }
        $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
        $print_nombre = "Calificaciones - " . $nombre_grado . "-" . $nombre_asignatura . "-" . $nombre_seccion . '.pdf';
        
        //$print_nombre = $path_root . '/registro_academico/temp/' . trim($nombre_completo_alumno) . ' ' . trim($print_grado) . ' ' . trim($print_seccion) . '.pdf';
        $pdf->Output($print_nombre,$modo);