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
  consultas(15,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
	    break;
            }
    // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
       // buscar la consulta y la ejecuta.
	consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
        $print_nombre_docente = "";
        while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
            {
		 $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
		 
		if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
			$print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
		}
           
            }        
        
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $print_nombre_docente;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(150,6,utf8_decode($_SESSION['institucion']).' - ESTADISTICA MENSUAL',0,1,'C');
    $this->SetFont('Arial','',11);
    $this->Cell(15);
    $this->Cell(50,4,utf8_decode('Nómina de Alumnos/as'),0,0,'L');
    $this->Cell(100,4,'Docente Responsable: '.$print_nombre_docente,0,1,'L');
    $this->Line(10,20,200,20);
    //Salto de línea
   // $this->Ln(20);
}

//Pie de página
function Footer()
{
  //
  // Establecer formato para la fecha.
  // 
   date_default_timezone_set('America/El_Salvador');
   setlocale(LC_TIME, 'spanish');
	//
							
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    //$this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(10,15,85,20,10,10,10,10,10,10,10); //determina el ancho de las columnas
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    //Datos
    $fill=false;
}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Legal');
    $data = array();
//Títulos de las columnas
    $header=array('Nº','N I E','Nombre de Alumnos/as','F.Nac.','Edad','G.','So.','Rep.','Ret.','N.I.','P.N.');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(20);
    $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// buscar la consulta y la ejecuta.
	consultas(8,0,$codigo_all,'','','',$db_link,'');
// Contar el número de registros.
	$fila = $result -> rowCount();
// Evaluar si existen registros.
    if($result -> rowCount() != 0){
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    //  imprimir datos del bachillerato.
    $pdf->Cell(100,10,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,10,$print_grado,0,0,'L');
    $pdf->Cell(20,10,$print_seccion,0,0,'L');
    $pdf->Cell(20,10,$print_ann_lectivo,0,0,'L');

    $pdf->ln();
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;

    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

    $w=array(10,15,85,20,10,10,10,10,10,10,10); //determina el ancho de las columnas
    $ancho_libro = array(5.05);
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $repitentem = 0; $repitentef = 0; $totalrepitente = 0; $sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
    $nuevoingresom = 0; $nuevoingresof = 0;
    $retiradom = 0; $retiradof = 0; $totalretirados = 0;
    
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $pdf->Cell($w[0],$ancho_libro[0],$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],$ancho_libro[0],trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[2],$ancho_libro[0],utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[3],$ancho_libro[0],cambiaf_a_normal($row['fecha_nacimiento'],'LR',0,'C',$fill));  // edad
            $pdf->Cell($w[4],$ancho_libro[0],$row['edad'],'LR',0,'C',$fill);  // edad
            $pdf->Cell($w[5],$ancho_libro[0],strtoupper($row['genero']),'LR',0,'C',$fill);    // genero
	    
	    $si_o_no = utf8_decode('Sí');

            if (($row['sobreedad']) == 't'){$pdf->Cell($w[6],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[6],$ancho_libro[0],'','LR',0,'C',$fill);}
            if (($row['repitente']) == 't'){$pdf->Cell($w[7],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[7],$ancho_libro[0],'','LR',0,'C',$fill);} 
            
            if (($row['retirado']) == 't'){$pdf->Cell($w[8],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[8],$ancho_libro[0],'','LR',0,'C',$fill);}
            if (($row['nuevo_ingreso']) == 't'){$pdf->Cell($w[9],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[9],$ancho_libro[0],'','LR',0,'C',$fill);}
            if (($row['partida_nacimiento']) == 't'){$pdf->Cell($w[10],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[10],$ancho_libro[0],'','LR',0,'C',$fill);}

            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;
                
            if($row['genero'] == 'm')
            {
                $m++;
                if ($row['repitente'] == 't'){$repitentem++;}
                if ($row['sobreedad'] == 't'){$sobreedadm++;}
                if ($row['nuevo_ingreso'] == 't'){$nuevoingresom++;}
                if ($row['retirado'] == 't'){$retiradom++;}}
                else{
                    $f++;
                    if ($row['repitente'] == 't'){$repitentef++;}
                    if ($row['sobreedad'] == 't'){$sobreedadf++;}
                    if ($row['nuevo_ingreso'] == 't'){$nuevoingresof++;}
                    if ($row['retirado'] == 't'){$retiradof++;}
                    }
                    
        // Salto de Línea.
        		if($i > 50 || $i == 100){
				$pdf->Cell(array_sum($w),0,'','B');
				$pdf->AddPage();
				//  imprimir datos del bachillerato.
				$pdf->Cell(100,10,$print_bachillerato,0,0,'L');
				$pdf->Cell(40,10,$print_grado,0,0,'L');
				$pdf->Cell(20,10,$print_seccion,0,0,'L');
				$pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
				$pdf->ln();
				$pdf->FancyTable($header);}
        
        } //cierre del do while.          

          // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetX(10);
                      $pdf->Cell($w[0],$ancho_libro[0],$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],$ancho_libro[0],'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],$ancho_libro[0],'','LR',0,'C',$fill);  // NIE
                      $pdf->Cell($w[3],$ancho_libro[0],'','LR',0,'C',$fill);  // NIE
                      $pdf->Cell($w[4],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[5],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[6],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[7],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[8],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[9],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[10],$ancho_libro[0],'','LR',0,'C',$fill);  // P.N.
                      $pdf->Ln();   
                      $fill=!$fill;
                      
                      // Salto de Línea.
		      if($numero > 50){
		        $pdf->Cell(array_sum($w),0,'','B');
			 $pdf->AddPage();
			 //  imprimir datos del bachillerato.
		            $pdf->Cell(100,10,$print_bachillerato,0,0,'L');
		            $pdf->Cell(40,10,$print_grado,0,0,'L');
		            $pdf->Cell(20,10,$print_seccion,0,0,'L');
		            $pdf->Cell(35,10,$print_ann_lectivo,0,0,'L');
				$pdf->ln();
			 $pdf->FancyTable($header);}
                  }

		// Cerrando Línea Final.
			//$pdf->Cell(array_sum($w),0,'','T');
						
						
        // Imprimir datos de suma de masculino y femenino.
        		
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $suma=$m+$f;
            $pdf->ln(6);
            $pdf->SetX(30);
            $pdf->Cell(160,7,'ESTADISTICA',1,0,'C',TRUE);
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->Cell(40,7,'',1,0,'C');
            $pdf->Cell(40,7,'Masculino',1,0,'C');
            $pdf->Cell(40,7,'Femenino',1,0,'C');
            $pdf->Cell(40,7,'Total',1,0,'C');

            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'MATRICULA MAX.',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$m,1,0,'C');
            $pdf->Cell(40,7,$f,1,0,'C');
            $pdf->Cell(40,7,$suma,1,0,'C');

        // Imprimir datos de alumnos repitentes.
            $totalrepitente = $repitentem + $repitentef;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'REPITENTES',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$repitentem,1,0,'C');
            $pdf->Cell(40,7,$repitentef,1,0,'C');
            $pdf->Cell(40,7,$totalrepitente,1,0,'C');

        // Imprimir datos de alumnos de sobreedad
            $totalsobreedad = $sobreedadm + $sobreedadf;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'SOBREEDAD',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$sobreedadm,1,0,'C');
            $pdf->Cell(40,7,$sobreedadf,1,0,'C');
            $pdf->Cell(40,7,$totalsobreedad,1,0,'C');

// Imprimir datos de alumnos de sobreedad
            $totalnuevoingreso = $nuevoingresom + $nuevoingresof;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'NUEVO INGRESO',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$nuevoingresom,1,0,'C');
            $pdf->Cell(40,7,$nuevoingresof,1,0,'C');
            $pdf->Cell(40,7,$totalnuevoingreso,1,0,'C');

// Imprimir datos de alumnos de sobreedad
            $totalretirados = $retiradom + $retiradof;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'RETIRADOS',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$retiradom,1,0,'C');
            $pdf->Cell(40,7,$retiradof,1,0,'C');
            $pdf->Cell(40,7,$totalretirados,1,0,'C');
            
            $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
// Salida del pdf.
     $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D).
     $print_nombre = $print_grado . ' ' . $print_seccion . '.pdf';
     $pdf->Output($print_nombre,$modo);			
    }   // condicion si existen registros.
else{
    // si no existen registros.
    $pdf->Cell(150,7,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
	$pdf->Output();
}    
?>