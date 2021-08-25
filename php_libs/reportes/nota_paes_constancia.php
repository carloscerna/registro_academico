<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
    $codigo_all = $_REQUEST["todos"];
    $firma = $_REQUEST["chkfirma"];
    $sello = $_REQUEST["chksello"];
    $db_link = $dblink;
    $j = 0;
// buscar la consulta y la ejecuta.
  consultas(5,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
	    
	    $print_codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
            $print_codigo_grado = trim($row['codigo_grado']);
            $codigo_seccion = trim($row['codigo_seccion']);
            $codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);

            $data[$j] = utf8_decode(substr(trim($row['n_asignatura']),0,20));
            $j++;
            }

            //
	    // Establecer formato para la fecha.
	    // 
		date_default_timezone_set('America/El_Salvador');
		setlocale(LC_TIME,'es_SV');
	    //
		//$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
                $meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
                //Salida: Viernes 24 de Febrero del 2012		
		//Crear una línea. Fecha.
		$dia = strftime("%d");		// El Día.
                $mes = $meses[date('n')-1];     // El Mes.
		$año = strftime("%Y");		// El Año.
                    
class PDF extends FPDF
{
   //Cabecera de página
    function Header()
    {
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,5,4,20,26);
    //Arial bold 14
        $this->SetFont('Arial','B',14);
    //Título
	//$0titulo1 = utf8_decode("Educación Parvularia - Básica - Tercer Ciclo y Bachillerato.");
        $this->RotatedText(30,10,utf8_decode($_SESSION['institucion']),0);
    //Arial bold 13
        $this->SetFont('Arial','B',12);
	$this->RotatedText(30,17,utf8_decode($_SESSION['direccion']),0);
	
    // Teléfono.
	if(empty($_SESSION['telefono'])){
	    $this->RotatedText(30,24,'',0,1,'C');    
	}else{
	    $this->RotatedText(30,24,utf8_decode('Teléfono: ').$_SESSION['telefono'],0,1,'C');
	}
    // ARMAR ENCABEZADO.
	$style6 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0));
	$this->CurveDraw(0, 37, 120, 40, 155, 20, 225, 20, null, $style6);
	$this->CurveDraw(0, 36, 120, 39, 155, 19, 225, 19, null, $style6);	
    }

//Pie de página
function Footer()
{
    global $firma, $sello;
    //Posición: a 1,5 cm del final
    $this->SetY(-20);
    //Arial italic 8
    $this->SetFont('Arial','I',12);
    //Crear una línea de la primera firma.
    $this->Line(115,242,200,242);
    
    //Firma Director.
    if($firma == 'yes'){
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
	$this->Image($img,120,225,70,15);
    }
    if($sello == 'yes'){
	$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
	$this->Image($img_sello,95,225,30,30);
    }
    
    //Nombre Director
    $this->RotatedText(120,247,cambiar_de_del($_SESSION['nombre_director']),0,1,'C');
    $this->RotatedText(140,253,'Director(a)',0,1,'C');
    
       // ARMAR pie de página.
	$style6 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0));
	$this->CurveDraw(0, 267, 120, 270, 155, 250, 225, 250, null, $style6);
	$this->CurveDraw(0, 266, 120, 269, 155, 249, 225, 249, null, $style6);	
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(60,25,185); //determina el ancho de las columnas
    $w2=array(7,12); //determina el ancho de las columnas

     // encabezado de la boleta.

       $this->Cell($w[0],$w2[0],'','LBRT',0,'L',1);
       $this->Cell($w[1],$w2[0],'Promedio','LTR',0,'C',1);
       $this->Cell($w[1],$w2[0],'',1,0,'C',1);
       $this->Cell($w[1],$w2[0],'',1,0,'C',1);
       $this->Cell($w[1],$w2[0],'',1,0,'C',1);
       $this->Cell($w[1],$w2[0],'Resultado',1,0,'C',1);
       $this->Ln();
       
       $this->Cell($w[0],$w2[0],'ASIGNATURAS','LBR',0,'L',1);
       $this->Cell($w[1],$w2[0],'Institucional','LBR',0,'C',1);
       $this->Cell($w[1],$w2[0],'75%.','LBR',0,'C',1);
       $this->Cell($w[1],$w2[0],'PAES','LBR',0,'C',1);
       $this->Cell($w[1],$w2[0],'25%','LBR',0,'C',1);
       $this->Cell($w[1],$w2[0],'Final','LBR',0,'C',1);
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
    $pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(15, 5, 10);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(20);
    $pdf->SetX(15);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;

//  mostrar los valores de la consulta
    $w=array(60,25,125); //determina el ancho de las columnas
    $w2=array(8,12); //determina el ancho de las columnas
// actualizar la consulta.
    consultas(5,0,$codigo_all,'','','',$db_link,'');
    $fill = false; $i=1;  $promedio_institucional = 0; $promedio_paes = 0; $promedio_final = 0; $pi=0;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            if ($i == 1){
            // Definimos el tipo de fuente, estilo y tamaño.
            $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
             $pdf->SetXY(15,40);
             $pdf->MultiCell(180,8,("El suscrito Director del ".utf8_decode($_SESSION['institucion'])." de Santa Ana hace constar que: "));
             
             $pdf->SetX(30);
             $pdf->Cell(15,$w2[0],'Alumno(a): ',0,0,'R');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell($w[2],$w2[0],utf8_decode(trim($row['apellido_alumno'])),0,0,'L');   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);
                        
             $pdf->Cell(10,$w2[0],'NIE: ',0,0,'L');
             $pdf->SetFont('Arial','B',14);
             $pdf->Cell(20,$w2[0],trim($row['codigo_nie']),0,1,'L');
             $pdf->SetFont('Arial','',12);
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],'Modalidad: ',0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$print_bachillerato,0,1,'L');
             $pdf->SetFont('Arial','',12);
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],utf8_decode('Año: '),0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$print_grado,0,1,'L');
             $pdf->SetFont('Arial','',12);             
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],utf8_decode('Sección: '),0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$print_seccion,0,1,'L');
             $pdf->SetFont('Arial','',12);                          
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],utf8_decode('Año Lectivo: '),0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$print_ann_lectivo,0,1,'L');
             $pdf->SetFont('Arial','',12);             
             
            $pdf->ln();
            $pdf->MultiCell(180,8,utf8_decode("Obtuvo en las asignaturas básicas, que se evalúan en la Prueba de Aprendizajes y Aptitudes para Egresados de Educación Media (PAES) los siguientes resultados con carácter promocional."));
						$pdf->ln();
						
            // dibujar encabezado de la tabla.
              $pdf->FancyTable($header);

	    // Nombre de la asignatura, nota final y paes.
            	$pdf->Cell($w[0],$w2[0],utf8_decode(trim($row['n_asignatura'])),0,0,'L',$fill);  
							
	    // Calcular promedio Institucional.
		if($row['nota_final'] != 0)
		{
                    $pdf->Cell($w[1],$w2[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);
                    $pi = verificar_nota_media($row['nota_final'],$row['recuperacion']);                    
                    $promedio_institucional = number_format($pi * 0.75,2);
                    $pdf->Cell($w[1],$w2[0],$promedio_institucional,0,0,'C',$fill);
		}else{
		    $pdf->Cell($w[1],$w2[0],'',0,0,'C',$fill);
		}
										
	    // Calcular promedio PAES.
		if($row['nota_paes'] != 0)
		{
                    $pdf->Cell($w[1],$w2[0],trim($row['nota_paes'])*4,0,0,'C',$fill);
                    $promedio_paes = number_format($row['nota_paes'],2);
                    $pdf->Cell($w[1],$w2[0],$promedio_paes,0,0,'C',$fill);
                    $promedio_final = round($promedio_institucional + $promedio_paes,0);
                    $pdf->Cell($w[1],$w2[0],$promedio_final,0,1,'C',$fill);
		}else{
		    $pdf->Cell($w[1],$w2[0],'',0,1,'C',$fill);
		}
            }	// if de cierre de la condicion.      
                           
              if($i >= 2 && $i <=4)
              	{
              	// Nombre de la asignatura, nota final y paes.
            	    $pdf->Cell($w[0],$w2[0],utf8_decode(trim($row['n_asignatura'])),0,0,'L',$fill);  
		// Calcular promedio Institucional.
                    if($row['nota_final'] != 0)
                    {
			$pdf->Cell($w[1],$w2[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);
			$pi = verificar_nota_media($row['nota_final'],$row['recuperacion']);                    
			$promedio_institucional = number_format($pi * 0.75,2);
			$pdf->Cell($w[1],$w2[0],$promedio_institucional,0,0,'C',$fill);
                    }else{
			$pdf->Cell($w[1],$w2[0],'',0,0,'C',$fill);
			}
								
		// Calcular promedio PAES.
                    if($row['nota_paes'] != 0)
                    {
			$pdf->Cell($w[1],$w2[0],trim($row['nota_paes'])*4,0,0,'C',$fill);
			$promedio_paes = number_format($row['nota_paes'],2);
			$pdf->Cell($w[1],$w2[0],$promedio_paes,0,0,'C',$fill);
			$promedio_final = round($promedio_institucional + $promedio_paes,0);
			$pdf->Cell($w[1],$w2[0],$promedio_final,0,1,'C',$fill);
                    }else{
			$pdf->Cell($w[1],$w2[0],'',0,1,'C',$fill);
		    }
              	}
              
              if($print_codigo_bachillerato == "06")
              	{
              		if($i == 11){
                            $pdf->Ln();
                            $pdf->MultiCell(180,10,("Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en ".utf8_decode($_SESSION['institucion'])." de Santa Ana a los ". strtolower(num2letras($dia)).utf8_decode(" días de ").$mes." de ".strtolower(num2letras($año))));
                            $i = 0; $pdf->AddPage();}
              	}

              if($print_codigo_bachillerato == "07")
              	{
              	    if($i == 13){
              		$pdf->Ln();
              		$pdf->MultiCell(180,10,"Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en ".utf8_decode($_SESSION['institucion'])." de Santa Ana a los ". strtolower(num2letras($dia)).utf8_decode(" días de ").$mes." de ".strtolower(num2letras($año)));
              		$i = 0; $pdf->AddPage();}
              	}
              	$fill=!$fill;	
              	$i++;
            }
            // despues del bucle. WHILE
            $pdf->Cell(215,0,'','T');
// Salida del pdf.
    $pdf->Output();
?>