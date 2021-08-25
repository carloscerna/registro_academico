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
// variable de la conexión dbf.
  $db_link = $dblink;
// buscar la consulta y la ejecuta.
  consultas(18,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));

            $nombre_grado = utf8_decode(trim($row['nombre_grado']));
            $nombre_seccion = utf8_decode(trim($row['nombre_seccion']));

            $print_codigo_grado = (trim($row['codigo_grado']));
            $print_codigo_bachillerato = (trim($row['codigo_bachillerato']));
            $print_nombre_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));

            $codigo_ann_lectivo = (trim($row['codigo_ann_lectivo']));
            
            $codigo_bachillerato = $row['codigo_bachillerato'];
	    break;
            }
// variables y consulta a la tabla.
      $nota_p_p = $_REQUEST["lsttri"];
      if($nota_p_p == "nota_p_p_1"){$trimestre = "Timestre 1";}
      if($nota_p_p == "nota_p_p_2"){$trimestre = "Timestre 2";}
      if($nota_p_p == "nota_p_p_3"){$trimestre = "Timestre 3";}
      if($nota_p_p == "nota_p_p_4"){$trimestre = "Timestre 4";}
      if($nota_p_p == "nota_final"){$trimestre = "Nota Final";}

class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,5,4,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',14);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(200,7,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(200,7,utf8_decode('INFORME DE NOTAS POR TRIMESTRE O PERÍODO'),0,1,'C');
    $this->Line(0,20,250,20);
}

//Pie de página
function Footer()
{
    //Posición: a 1,5 cm del final
    $this->SetY(-20);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Número de página
    $this->SetY(-10);
    $this->Cell(0,6,utf8_decode('Página ').$this->PageNo().'/{nb}',0,0,'C');
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,255,255);
    $this->SetTextColor(0);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(5,70,10,10,10,10,10,10,10,10,10,10,10,10,10,10); //determina el ancho de las columnas
    $w2=array(5,12); //determina el ancho de las columnas
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
    $pdf->SetY(20);
    $pdf->SetX(5);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $w=array(5,70,10); //determina el ancho de las columnas
    $w2=array(7,12); //determina el alto de las columnas
////////////////////////////////////////////////////////////////////
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
	$total_asignaturas = 0;	
        while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
            {
        $total_asignaturas = (trim($row['total_asignaturas']));
            }
// COLOCAR ENCABEZANDO A LA BOLETA DE CALIFICACIÓN.		
      	if($print_codigo_bachillerato >= '03' and $print_codigo_bachillerato <= '05')
	    	{
			$nivel_educacion = "Educación Básica";
		}elseif($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '03')
		{
			$nivel_educacion = "Educación Parvularia";
		}else{
			// Validar Bachillerato.
			if($print_codigo_bachillerato == '06'){
				$nivel_educacion = "Educación Media - General";
			}
			if($print_codigo_bachillerato == '07'){
				$nivel_educacion = "Educación Media - Técnico";
			}
			
			if($print_codigo_bachillerato == '08' or $print_codigo_bachillerato == '09'){
				$nivel_educacion = "Educación Media - Contaduría";
			}
			if($print_codigo_bachillerato == '10'){
				$nivel_educacion = "Educación Básica - TERCER CICLO - NOCTURNA";
			}
			if($print_codigo_bachillerato == '11'){
				$nivel_educacion = "Educación Media - General - NOCTURNA";
			}		
				// Validar grado de educaci�n Media.
				if($print_codigo_grado == '10'){
					$print_grado_media = "Primer año";
				}
				if($print_codigo_grado == '11'){
					$print_grado_media = "Segundo año";
				}
				if($print_codigo_grado == '12'){
					$print_grado_media = "Tercer año";
				}
      }
// buscar la consulta y la ejecuta.
$codigo_b_g_a = $print_codigo_bachillerato . $print_codigo_grado . $codigo_ann_lectivo;
$j=0; $data=array();
consultas(19,0,$codigo_b_g_a,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_nombre_asignatura -> fetch(PDO::FETCH_BOTH))
        {
            $data[$j] = substr(utf8_decode(trim($row['nombre_asignatura'])),0,5);
              $j++;
        }
// hacer nuevamente la consulta.
      consultas(5,0,$codigo_all,'','','',$db_link,'');
// Definimos el tipo de fuente, estilo y tamaño.
            $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
             $pdf->SetY(22);
                 //  imprimir datos del bachillerato.
                $pdf->Cell(80,$w2[0],$print_bachillerato,0,0,'L');
                $pdf->Cell(40,$w2[0],$print_grado,0,0,'L');
                $pdf->Cell(20,$w2[0],$print_seccion,0,0,'L');
                $pdf->Cell(20,$w2[0],$print_ann_lectivo,0,0,'L');

             $pdf->Cell(45,$w2[0],$trimestre,0,0,'R');
            $pdf->ln();
//  mostrar los valores de la consulta
            // dibujar encabezado de la tabla.
            $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
            $header1=array('Nº','Apellido - Nombre');
            $tp=array('TP');
            $header = array_merge($header1,$data,$tp);
            $pdf->FancyTable($header);

    $fill = false; $i=1;  $numero_linea = 1;
    // Matrices para la sumatoria de las asignaturas.
      $sumatoria_lenguaje = array();
      $sumatoria_matematica = array();
      $sumatoria_ciencias = array();
      $sumatoria_sociales = array();
      $sumatoria_ingles = array();
      $sumatoria_fisica = array();
      
      $sumatoria_1 = array();
      $sumatoria_2 = array();
      $sumatoria_3 = array();
      $sumatoria_4 = array();
      $sumatoria_5 = array();
      $sumatoria_6 = array();
      
      $total_puntos = array();
	  // Crear Martiz para Camptura NOMBRES Y Total de Puntos.
    $datos[] = array("nombre"=>'', "puntaje"=>'');
      //RECORRER LA CONSULTA PARA QUE MUESTRE LAS NOTAS.
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            // >Impresión de la primera asignatura. primer ciclo, segundo y tercero.
                if ($i == 1)
				{
          $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
					$pdf->Cell($w[0],$w2[0],$numero_linea,0,0,'C',$fill);
					$pdf->Cell($w[1],$w2[0],utf8_decode(trim($row['apellido_alumno'])),0,0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
					$pdf->Cell($w[2],$w2[0],trim($row[$nota_p_p]),0,0,'C',$fill);
					// Agregar valor a matriz. NOMBRE.
					$datos[$numero_linea]['nombre'] = utf8_decode(trim($row['apellido_alumno']));
				}
                // Acumular valores
                if ($print_nombre_bachillerato == 'Primer Ciclo' || $print_nombre_bachillerato == 'Segundo Ciclo' || $print_nombre_bachillerato == 'Tercer Ciclo' || $print_nombre_bachillerato == 'Bachillerato General')
                  {
                    if($row['codigo_asignatura'] == '00' || $row['codigo_asignatura'] == '01' || $row['codigo_asignatura'] == '15'){
                      $sumatoria_lenguaje[] = $row[$nota_p_p];
                      $total_puntos[] = $row[$nota_p_p];}
                    else{
                      // sumar promedios de asignaturas.
					    if($i == 1){$sumatoria_lenguaje[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 2){$sumatoria_matematica[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 3){$sumatoria_ciencias[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 4){$sumatoria_sociales[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 5){$sumatoria_fisica[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 6){$sumatoria_ingles[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                      
                      if($print_nombre_bachillerato == 'Bachillerato General')
                      {
                        if($i == 7){$sumatoria_1[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 8){$sumatoria_2[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 9){$sumatoria_3[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 10){$sumatoria_4[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 11){$sumatoria_5[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 12){$sumatoria_6[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                      }
                      // Imprimir el valor de la nota segun periodo o trimestre.  
                        if($i == 6 || $i == 12){
                          $pdf->Cell($w[2],$w2[0],trim($row[$nota_p_p]),'R',0,'C',$fill);
                        }else{
								if($i<> 1){
									$pdf->Cell($w[2],$w2[0],trim($row[$nota_p_p]),0,0,'C',$fill);
								}
							}						  
                    }
                  }    
			// En el Caso de 1º, 2º y 3º Ciclo.
               if ($print_nombre_bachillerato == 'Primer Ciclo' || $print_nombre_bachillerato == 'Segundo Ciclo' || $print_nombre_bachillerato == 'Tercer Ciclo' || $print_nombre_bachillerato == 'Bachillerato General'){
                if ($i == 12){
                   // Imprimir total de puntos y limpiar la matriz.
                   $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
                   $pdf->Cell($w[2],$w2[0],round(array_sum($total_puntos),1),0,0,'C',$fill);
                   $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                   // Agregar valor a matriz. TOTAL DE PUNTOS POR NOMBRE
						$datos[$numero_linea]['puntaje'] = round(array_sum($total_puntos),1);
                   // Limpiar matriz.
				   $total_puntos = array();
                   //////////////////////////////////////////////////////////////////////                    
                    $numero_linea++;
                    $pdf->Ln();
                  // salto de pagina.
                    if($numero_linea == 30 && $i == 12){
                      $pdf->Cell(205,0,'','T');
                      $pdf->AddPage();
                      $pdf->SetXY(5,25);
                      $pdf->FancyTable($header);}
                      $i = 1;
                      $fill=!$fill;
                  }
                   else{
                // acumulador para el numero de asignaturas
                    $i++;
                }}

               if ($codigo_bachillerato == '07')
               {
                if($row['codigo_asignatura'] == '00' || $row['codigo_asignatura'] == '01' || $row['codigo_asignatura'] == '15'){
                      $sumatoria_lenguaje[] = $row[$nota_p_p];
                      $total_puntos[] = $row[$nota_p_p];}
                    else{
                      // sumar promedios de asignaturas.
                        if($i == 2){$sumatoria_matematica[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 3){$sumatoria_ciencias[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 4){$sumatoria_sociales[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 5){$sumatoria_fisica[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 6){$sumatoria_ingles[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}}
			
                        if($i == 7){$sumatoria_1[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 8){$sumatoria_2[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 9){$sumatoria_3[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 10){$sumatoria_4[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 11){$sumatoria_5[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        
		    if($i>=2){$pdf->Cell($w[2],$w2[0],trim($row[$nota_p_p]),0,0,'C',$fill);}
                    
                if ($i == 13)
                {
                    $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
                    $pdf->Cell($w[2],$w2[0],round(array_sum($total_puntos),1),0,0,'C',$fill);
                    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                    $total_puntos = array();
                   
                   //////////////////////////////////////////////////////////////////////
                // salto de pagina.
                    $numero_linea++;
                    $pdf->Ln();
                  // salto de pagina.
                    if($numero_linea == 30 && $i == 13){
                      $pdf->Cell(205,0,'','T');
                      $pdf->AddPage();
                      $pdf->SetXY(5,25);
                      $pdf->FancyTable($header);}
                    $i = 1;
                    $fill=!$fill;
                  }
                   else{
                // acumulador para el numero de asignaturas
                    $i++;}
                }

               if ($codigo_bachillerato >= '08')
               {
                 if($row['codigo_asignatura'] == '24' || $row['codigo_asignatura'] == '01' || $row['codigo_asignatura'] == '15'){
                      $sumatoria_lenguaje[] = $row[$nota_p_p];
                      $total_puntos[] = $row[$nota_p_p];}
                    else{
                      // sumar promedios de asignaturas.
                        if($i == 2){$sumatoria_matematica[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 3){$sumatoria_ciencias[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 4){$sumatoria_sociales[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 5){$sumatoria_fisica[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}
                        if($i == 6){$sumatoria_ingles[] = $row[$nota_p_p]; $total_puntos[] = $row[$nota_p_p];}}
			
		  if($i>=2){$pdf->Cell($w[2],$w2[0],trim($row[$nota_p_p]),0,0,'C',$fill);}
                  
                if ($i == 7)
                {
                    $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
                    $pdf->Cell($w[2],$w2[0],round(array_sum($total_puntos),1),0,0,'C',$fill);
                    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                    $total_puntos = array();
                   
                   //////////////////////////////////////////////////////////////////////
                // salto de pagina.
                    $numero_linea++;
                    $pdf->Ln();
                  // salto de pagina.
                    if($numero_linea == 33 && $i == 13){$pdf->Cell(205,0,'','T');$pdf->AddPage();$pdf->FancyTable($header);}
                    $i = 1;
                    $fill=!$fill;
                  }
                   else
                   {
                // acumulador para el numero de asignaturas
                    $i++;
                    }
                }                 
             } // este recorre nombre por nombre (bucle)
             
        // Presentar la sumatoria y el promedio de la asignatura.
            $pdf->Cell(205,0,'','T');
            $pdf->Ln(2);
            $pdf->SetFont('Arial','b',9); 
            $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'TOTAL DE PUNTOS',0,0,'R',$fill);   
            $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_lenguaje),0,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_matematica),0,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_ciencias),0,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_sociales),0,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_fisica),0,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_ingles),0,0,'C',$fill);
            
            if($print_nombre_bachillerato == 'Bachillerato General' || $print_nombre_bachillerato == 'Bachillerato Técnico Vocacional Comercial')
            {
                $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_1),0,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_2),0,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_3),0,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_4),0,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],array_sum($sumatoria_5),0,0,'C',$fill);
            }
            $pdf->Ln();
            $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'PROMEDIO',0,0,'R',$fill);   
            $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_lenguaje)/$numero_linea,0),1,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_matematica)/$numero_linea,0),1,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_ciencias)/$numero_linea,0),1,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_sociales)/$numero_linea,0),1,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_fisica)/$numero_linea,0),1,0,'C',$fill);
            $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_ingles)/$numero_linea,0),1,0,'C',$fill);

            if($print_nombre_bachillerato == 'Bachillerato General' || $print_nombre_bachillerato == 'Bachillerato Técnico Vocacional Comercial')
            {
                $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_1)/$numero_linea,0),1,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_2)/$numero_linea,0),1,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_3)/$numero_linea,0),1,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_4)/$numero_linea,0),1,0,'C',$fill);
                $pdf->Cell($w[2],$w2[0],round(array_sum($sumatoria_5)/$numero_linea,0),1,0,'C',$fill);
            }
			
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			// CONSTRUIR TABLA CON LOS ALUMNOS QUE HAN OBTENIDO MAYOR PUNTAJE.
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // salto de pagina.

			if($numero_linea >= 30)
			{
				//$pdf->AddPage();
			}else{
				if($numero_linea <= 23 or $numero_linea >=23){$pdf->AddPage();}			
			}
			// Contruir matriz con los puntos.
				foreach ($datos as $key => $row) {
				    $aux[$key] = $row['puntaje'];}
			// Ordenar de forma ASC o DES
				array_multisort($aux, SORT_DESC, $datos);
			// Presentar los primeros 5 lugages.
			// Linea en blanco
			$pdf->Ln(12);
			$pdf->Cell($w[1]+$w[2]+$w[2]+20,$w2[0],utf8_decode('Alumnos con Mayor Puntaje'),1,1,'C',$fill);
			$pdf->Cell($w[2],$w2[0],utf8_decode('Nº'),1,0,'C',$fill);
			$pdf->Cell($w[1]+20,$w2[0],utf8_decode('Nombre del Alumno'),1,0,'C',$fill);
			$pdf->Cell($w[2],$w2[0],utf8_decode('Ptos.'),1,1,'C',$fill);
			// Recorrer los primeros 5 valores.
			 for($jj=0;$jj <=4; $jj++){
				 // Cambiar color de fondo.
					 $fill=!$fill;
				 // Pasar valores de matriz a variables.
				 $nombre_puntaje = $datos[$jj]['nombre'];
				 $puntos_puntaje = $datos[$jj]['puntaje'];
				 // Imprimir en Pantalla.
				 $pdf->Cell($w[2],$w2[0],$jj+1,1,0,'C',$fill);
				 $pdf->Cell($w[1]+20,$w2[0],$nombre_puntaje,1,0,'L',$fill);
				 $pdf->Cell($w[2],$w2[0],$puntos_puntaje,1,1,'C',$fill);
			 }
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Construir el nombre del archivo.
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$nombre_archivo = utf8_decode('Promedio por Período: ' . $nombre_grado . ' ' . $nombre_seccion . '.pdf');
// Salida del pdf.
    $pdf->Output($nombre_archivo,'I');
?>