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

// variables y consulta a la tabla.
      $codigo_all = $_REQUEST["todos"];
      $codigo_matricula = $_REQUEST['txtcodmatricula'];
      $codigo_alumno = $_REQUEST['txtidalumno'];
      $db_link = $dblink;
      consultas_alumno(2,0,$codigo_all,$codigo_alumno,$codigo_matricula,$db_link,'');
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
    //$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    //$this->Image($img,5,4,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',14);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(200,7,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(200,7,utf8_decode('Teléfono: 2441-4383'),0,1,'C');
    $this->Line(0,20,250,20);
}

//Pie de página
function Footer()
{
    //Posición: a 1,5 cm del final
    $this->SetY(-20);
    //Arial italic 8
    $this->SetFont('Arial','I',10);
    //Crear una línea de la primera firma.
    $this->Line(115,230,200,230);
    //Crear una línea
    $this->Line(0,270,220,270);

    //Firma Director.
    //$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/firma.png';
    //$this->Image($img,120,215,70,15);
    //Nombre Director
    $this->SetXY(110,230);
    $this->Cell(80,6,utf8_decode($_SESSION['nombre_director']),0,1,'C');
    $this->Cell(220,6,'                                              Director',0,1,'C');
    //Número de página
    $this->SetY(-10);
    $this->Cell(0,6,'cerz_10391@hotmail.com',0,0,'C');

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
/*    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,$header[$i],1,0,'C',1);
    $this->Ln();*/

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

//  imprimir datos del bachillerato.
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $bach = utf8_decode(trim($row['nombre_bachillerato']));
            $grado = utf8_decode(trim($row['nombre_grado']));
            $seccion = trim($row['nombre_seccion']);
            $annlectivo = trim($row['nombre_ann_lectivo']);
                break;
            }
//  mostrar los valores de la consulta
    $w=array(60,25,125); //determina el ancho de las columnas
    $w2=array(8,12); //determina el ancho de las columnas
// actualizar la consulta.
    consultas_alumno(2,0,$codigo_all,$codigo_alumno,$codigo_matricula,$db_link,'');
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
             $pdf->Cell(100,$w2[0],$bach,0,1,'L');
             $pdf->SetFont('Arial','',12);
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],utf8_decode('Año: '),0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$grado,0,1,'L');
             $pdf->SetFont('Arial','',12);             
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],utf8_decode('Sección: '),0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$seccion,0,1,'L');
             $pdf->SetFont('Arial','',12);                          
             
             $pdf->SetX(30);
             $pdf->Cell(30,$w2[0],utf8_decode('Año Lectivo: '),0,0,'L');
             $pdf->SetFont('Arial','B',12);
             $pdf->Cell(100,$w2[0],$annlectivo,0,1,'L');
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
                
              
              if($bach == "Bachillerato General")
              	{
              		if($i == 11){
                            $pdf->Ln();
                            $pdf->MultiCell(180,10,("Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en ".$_SESSION['institucion']." de Santa Ana a los ". strtolower(num2letras($dia)).utf8_decode(" días de ").$mes." de ".strtolower(num2letras($año))));
                            $i = 0; $pdf->AddPage();}
              	}

              if($bach == "Bachillerato Técnico Vocacional Comercial")
              	{
              	    if($i == 13){
              		$pdf->Ln();
              		$pdf->MultiCell(180,10,"Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en ".$_SESSION['institucion']." de Santa Ana a los ". strtolower(num2letras($dia)).utf8_decode(" días de ").$mes." de ".strtolower(num2letras($año)));
              		$i = 0; $pdf->AddPage();}
              	}
              	$fill=!$fill;	
              		$i++;
            }
            // despues del bucle.
            $pdf->Ln();
            $pdf->MultiCell(180,10,("Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en ".utf8_decode($_SESSION['institucion'])." de Santa Ana a los ". strtolower(num2letras($dia)).utf8_decode(" días de ").$mes." de ".strtolower(num2letras($año))));
            //$pdf->Cell(215,0,'','T');

// Salida del pdf.
    $pdf->Output();
?>