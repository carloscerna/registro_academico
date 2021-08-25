<?php
    //set_time_limit(0);
    //ini_set("memory_limit","512M");
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
    $nota_p = $_REQUEST["lstrimestre"];
    $db_link = $dblink;
    $j = 0;  $k = 0; $data = array();
    $codigo_modalidad = array(); $codigo_grado = array(); $codigo_seccion = array(); $codigo_ann_lectivo = array();
// buscar la consulta y la ejecuta.
    consulta_consolidados(1,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos de la modalidad.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
	    $codigo_modalidad[$j] = trim($row['codigo_bachillerato']);
	    $print_codigo_bachillerato = trim($row['codigo_bachillerato']);
            $codigo_grado[$j] = trim($row['codigo_grado']);
            $codigo_seccion[$j] = trim($row['codigo_seccion']);
            $codigo_ann_lectivo[$j] = trim($row['codigo_ann_lectivo']);
	    
	    $j++;
            }
////////////////////////////////////////////////////////////////////
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// asignar valor cuantas asignaturas hay en total.
    if($codigo_modalidad[0] >= 3 && $codigo_modalidad[0] <= 5)
    {
	$total_asignaturas = 6;
    }

    if($codigo_modalidad[0] == 6)
    {
	$total_asignaturas = 11;
    }
    
      if($codigo_modalidad[0] == 7)
    {
	$total_asignaturas = 13;
    }
    if($codigo_modalidad[0] == 9)
    {
	$total_asignaturas = 7;
    }
// buscar la consulta y la ejecuta.
    $codigo_ = $codigo_modalidad[0] . $codigo_grado[0] . $codigo_seccion[0] . $codigo_ann_lectivo[0];
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL NÚMERO DE ASIGNATURAS.
    consulta_consolidados(3,0,$codigo_,'','','',$db_link,'');
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
		$data[$k] = substr(utf8_decode(trim($row['n_asignatura'])),0,15);
		$nombre_modalidad = utf8_decode(trim($row['nombre_bachillerato']));
		$k++;
		
		if($k == 6){break;}
	    }
// Colocar la leyenda del Período.
   if($nota_p == "nota_p_p_1"){
      $periodo = "Trimestre 1";
   }
   if($nota_p == "nota_p_p_2"){
      $periodo = "Trimestre 2";
   }
   if($nota_p == "nota_p_p_3"){
      $periodo = "Trimestre 3";
   }
   if($nota_p == "nota_final"){
      $periodo = "Nota Final";
   }
class PDF extends FPDF
{
// rotar texto funcion TEXT()
function RotatedText($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
	$this->Rotate(0);
}

// rotar texto funcion MultiCell()
function RotatedTextMultiCell($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
        $this->MultiCell(33,4,$txt,0,'L');
	$this->Rotate(0);
}

function RotatedTextMultiCellAspectos($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
        $this->MultiCell(43,3,$txt,0,'L');
	$this->Rotate(0);
}

//Cabecera de página
function Header()
{
   global $periodo, $nombre_modalidad;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,5,4,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(200,7,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(200,7,'PROMEDIO POR ASIGNATURA ( NOTAS ) - '.utf8_decode($periodo),0,1,'C');
    $this->Cell(200,7,$nombre_modalidad,0,1,'C');
    $this->Line(0,30,280,30);
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
    $this->Cell(0,6,'Nº '.$this->PageNo().'/{nb}',0,0,'C');

}

//********************************************************************************************************************************
function cuadro($data)
{
// ARMAR LA CUADRICULA POR SEGMENTOS.

// PRIMERA PARTE DEL RECTANGULO.
    $this->SetFillColor(255,255,255);
    $this->Rect(6,35,200,20);
// segunda PARTE DEL RECTANGULO. numero de orden
    $this->Rect(6,35,5,20);
    $this->SetFont('Arial','',9); // I : Italica; U: Normal;
    $this->RotatedText(7,50,utf8_decode('Nº'),0);
// tercera PARTE DEL RECTANGULO.   nombre del grado
    $this->Rect(11,35,18,20);
    $this->SetFont('Arial','',12); // I : Italica; U: Normal;
    $this->RotatedText(13,50,'Grado',0);
// cuarta PARTE DEL RECTANGULO.   nombre del grado
    $this->Rect(29,35,12,20);
    $this->SetFont('Arial','',8); // I : Italica; U: Normal;
    $this->RotatedText(30,50,utf8_decode('Sección'),0);
// quinta PARTE DEL RECTANGULO.   nombre del grado
    $this->Rect(41,35,15,20);
    $this->SetFont('Arial','',8); // I : Italica; U: Normal;
    $this->RotatedText(44,50,'Turno',0);
// sexto PARTE DEL RECTANGULO. asignaturas básicas
    $this->SetFont('Arial','',10); // I : Italica; U: Normal;
    $this->RotatedText(100,39,utf8_decode('A S I G N A T U R A S      B Á S I C A S'),0);
// QUINTA PARTE DEL RECTANGULO. NOMBRE DE LAS ASIGNATURAS
    $x1 = 56; $y1 = 40; $x2 = 25; $i = 0;
    $mover_x = 0; $relleno = false;
    for($i=0;$i<=5;$i++)
    {
        $this->SetFont('Arial','',9); // I : Italica; U: Normal;
        $this->Rect($x1+$mover_x,$y1,$x2,5);
        $this->SetXY($x1+$mover_x,$y1);
        
        //cambiar el color del relleno.
        if($i == 0){$relleno = true; $this->SetFillColor(255,165,0);$this->Cell($x2,5,$data[$i],0,2,'C',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 1){$relleno = true; $this->SetFillColor(0,250,114);$this->Cell($x2,5,$data[$i],0,2,'C',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 2){$relleno = true; $this->SetFillColor(50,205,50);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 3){$relleno = true; $this->SetFillColor(72,118,255);$this->Cell($x2,5,$data[$i],0,2,'C',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 4){$relleno = true; $this->SetFillColor(238,238,0);$this->Cell($x2,5,$data[$i],0,2,'C',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 5){$relleno = true; $this->SetFillColor(202,255,112);$this->Cell($x2,5,$data[$i],0,2,'C',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        $mover_x = $mover_x + 25;
    }
    
    // RESTRABLECER EL COLOR DE RELLENO.
    $this->SetFillColor(255,255,255);
    
// SEXTA PARTE DEL RECTANGULO. NOMBRE DE LOS TRIMESTRES
    $x1 = 56; $y1 = 45; $x2 = 25;
    $mover_x = 0;
    $datos=array('Promedio');

    for($i=0;$i<=5;$i++)
    {
	    $this->SetFont('Arial','',7); // I : Italica; U: Normal;
	    $this->Rect($x1+$mover_x,$y1,$x2,5);
	    $this->SetXY($x1+$mover_x,$y1);
	    $this->Cell(30,5,$datos[0],0,2,'L');
        
	    $mover_x = $mover_x + 25;
    }

// SEPTIMA PARTE DEL RECTANGULO.
    $x1 = 56; $y1 = 50; $x2 = 25;
    $mover_x = 0;
    $datos=array('');
    
    $this->SetFont('Arial','',8); // I : Italica; U: Normal;
    for($i=0;$i<=4;$i++)
    {    
                $this->Rect($x1+$mover_x,$y1,$x2,5);
                $this->SetXY($x1+$mover_x,$y1);
                $this->Cell(3,5,$datos[0],0,2,'L');
                $mover_x = $mover_x + 25;
    }
}
//********************************************************************************************************************************
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
    $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
    $pdf->SetY(20);
    $pdf->SetX(6);

//  hACEMOS La llamda del encabezado del cuadro.
    $pdf->cuadro($data);
//  mostrar los valores de la consulta
    // dibujar encabezado de la tabla.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    //$pdf->SetFillColor(224,235,255);
    $pdf->SetFillColor(238,235,235);
    //Cabecera
    $w=array(5,15,25,18,12); //determina el ancho de las columnas
    $w2=array(10,18); //determina el ancho de las columnas
    $numero_linea = 1; $i = 1; $fill = true; $total_reprobadas = 0; $num = 0; $bucle = 0; $salto = false;
    $pdf->SetXY(6,55);
    
// EJECUTAR N VECES EL BLOQUE DEL CICLO CORRESPONDIENTE.
// buscar la consulta y la ejecuta.
for($bucle=0;$bucle<=count($codigo_modalidad)-1;$bucle++)
{
    $codigo_ = $codigo_modalidad[$bucle] . $codigo_grado[$bucle] . $codigo_seccion[$bucle] . $codigo_ann_lectivo[$bucle];
    $num++; 
    $pdf->SetX(6);
    $valor_grado = $codigo_grado[$bucle];
// variable para el conteo.
    $conteo_01 = 0; $conteo_02 = 0; $conteo_03 = 0; $conteo_04 = 0; $conteo_05 = 0; $conteo_06 = 0;
// variables del campo de la nota.
    $promedio_a_01 = 0; $promedio_a_02 = 0; $promedio_a_03 = 0; $promedio_a_04 = 0;
    $promedio_a_05 = 0; $promedio_a_06 = 0;
   // para el total de asignaturas.
   $promedios_a_01 = array(); $promedios_a_02 = array(); $promedios_a_03 = array(); $promedios_a_04 = array();
   $promedios_a_05 = array(); $promedios_a_06 = array();
   
   consulta_consolidados(3,0,$codigo_,'','','',$db_link,'');
   $turno = "";
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
		switch ($i) {
		    case 1:
			// EXTRAER LOS NOMBRES DE GRADO, SECCION
			$nombre_g = utf8_decode(trim($row['nombre_grado']));
			$nombre_s = trim($row['nombre_seccion']);
			$turno = $row['nombre_turno'];
			// obtener la nota promedio.
			$promedios_a_01[$conteo_01] = $row[$nota_p];
			break;
		    case 2:
			// obtener la nota promedio.
			$promedios_a_02[$conteo_02] = $row[$nota_p];
			break;
		    case 3:
			// obtener la nota promedio.
			$promedios_a_03[$conteo_03] = $row[$nota_p];
			break;
		    case 4:
			// obtener la nota promedio.
			$promedios_a_04[$conteo_04] = $row[$nota_p];
			break;
		    case 5:
			// obtener la nota promedio.
			$promedios_a_05[$conteo_05] = $row[$nota_p];
			break;
		    case 6:
			// obtener la nota promedio.
			$promedios_a_06[$conteo_06] = $row[$nota_p];
			break;
		}
		// SALTAR A AL BLOQUE DE ASIGNATURA SIGUEINTE.
		 if($i == $total_asignaturas){
			$i=1;
			$conteo_01++;
			$conteo_02++;
			$conteo_03++;
			$conteo_04++;
			$conteo_05++;
			$conteo_06++;}
		    else{
                    // acumular valor de $i para cambiar de asignatura.    
                        $i++;}
	    }
	    // PRIMERA ASIGNATURA 1º Y 2º CICLO - LENGUAJE, TERCER CICLO - LENGUAJE Y LITERATURA
	    $pdf->Cell($w[0],$w2[0],$num,1,0,'C',$fill);
	    $pdf->Cell($w[3],$w2[0],$nombre_g,1,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],$nombre_s,1,0,'C',$fill);
	    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
	    $pdf->Cell($w[1],$w2[0],$turno,1,0,'L',$fill);
	    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
	    // PRIMER BLOQUE DE LA ASIGNATURA y calculo del promedio.   
		$promedio_a_01 = round(array_sum($promedios_a_01)/count($promedios_a_01),1);
		$promedio_a_02 = round(array_sum($promedios_a_02)/count($promedios_a_02),1);
		$promedio_a_03 = round(array_sum($promedios_a_03)/count($promedios_a_03),1);
		$promedio_a_04 = round(array_sum($promedios_a_04)/count($promedios_a_04),1);
		$promedio_a_05 = round(array_sum($promedios_a_05)/count($promedios_a_05),1);
		$promedio_a_06 = round(array_sum($promedios_a_06)/count($promedios_a_06),1);
		
		$pdf->Cell($w[2],$w2[0],$promedio_a_01,1,0,'C',$fill);
		$pdf->Cell($w[2],$w2[0],$promedio_a_02,1,0,'C',$fill);
		$pdf->Cell($w[2],$w2[0],$promedio_a_03,1,0,'C',$fill);
		$pdf->Cell($w[2],$w2[0],$promedio_a_04,1,0,'C',$fill);
		$pdf->Cell($w[2],$w2[0],$promedio_a_05,1,0,'C',$fill);
		$pdf->Cell($w[2],$w2[0],$promedio_a_06,1,1,'C',$fill);
// cambiar al false el $fill.
    $fill=!$fill;
// LINEA FINAL DEL FOR.
}
// Construir el nombre del archivo.
   $nombre_archivo = 'Consolidados - '.$codigo_modalidad[0];
// Salida del pdf.
   $pdf->Output($nombre_archivo,'I');  
?>