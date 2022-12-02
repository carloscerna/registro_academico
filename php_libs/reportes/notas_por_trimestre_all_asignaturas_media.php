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
    $db_link = $dblink;
    $j = 0;  $data = array();
// buscar la consulta y la ejecuta.
    consultas(5,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
	    
	        $print_codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
            $print_codigo_grado = trim($row['codigo_grado']);
            $codigo_seccion = trim($row['codigo_seccion']);
            $codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);

            $data[$j] = substr(utf8_decode(trim($row['n_asignatura'])),0,20);
            $j++;
            }
//  variable para cambiar e imprimir las demás asignaturas.
    $cambiar_asignaturas = 0;
////////////////////////////////////////////////////////////////////
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
  consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL NÚMERO DE ASIGNATURAS.
	$total_asignaturas = 0;	
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
		        $total_asignaturas = (trim($row['total_asignaturas']));
            }
		
      	    if($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '05')
	    {
		$nivel_educacion = "Educación Básica";
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
		// Validar grado de educación Media.
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
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,5,4,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',14);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(300,7,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(300,7,'INFORME DE NOTAS POR PERIODO TODAS LAS ASIGNATURAS',0,1,'C');
    $this->Line(0,20,320,20);
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
    $this->Cell(0,6,utf8_decode(('Página ')).$this->PageNo().'/{nb}',0,0,'C');
}

//********************************************************************************************************************************
function cuadro($data)
{
    global $cambiar_asignaturas, $total_asignaturas;
// ARMAR LA CUADRICULA POR SEGMENTOS.

// PRIMERA PARTE DEL RECTANGULO.
    $this->Rect(6,35,301,20);
// segunda PARTE DEL RECTANGULO. numero de orden
    $this->Rect(6,35,5,20);
    $this->SetFont('Arial','',9); // I : Italica; U: Normal;
    $this->RotatedText(10,54,utf8_decode('N° de Orden'),90);
// tercera PARTE DEL RECTANGULO.   nombre del alumno
    $this->Rect(11,35,80,20);
    $this->SetFont('Arial','',12); // I : Italica; U: Normal;
    $this->SetXY(18,40);
    $this->SetFillColor(255,255,255);
    $this->MultiCell(60,8,'Nombre de los Alumnos(as)',0,2,'C',true);
// cuarta PARTE DEL RECTANGULO. asignatura
    $this->SetFont('Arial','',10); // I : Italica; U: Normal;
    //$this->Rect(91,35,224,20);
    //$this->Rect(91,35,224,5);
    $this->SetXY(160,35);
    $this->Cell(80,5,'ASIGNATURA',0,2,'C');
// QUINTA PARTE DEL RECTANGULO. NOMBRE DE LAS ASIGNATURAS
    $x1 = 91; $y1 = 40; $x2 = 36;
    $mover_x = 0; $relleno = false;
    if($cambiar_asignaturas == 0){
    for($i=0;$i<=5;$i++)
    {
        $this->SetFont('Arial','',10); // I : Italica; U: Normal;
        $this->Rect($x1+$mover_x,$y1,$x2,5);
        $this->SetXY($x1+$mover_x,$y1);
        
        //cambiar el color del relleno.
        if($i == 0){$relleno = true; $this->SetFillColor(255,165,0);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 1){$relleno = true; $this->SetFillColor(0,250,114);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 2){$relleno = true; $this->SetFillColor(50,205,50);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 3){$relleno = true; $this->SetFillColor(72,118,255);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 4){$relleno = true; $this->SetFillColor(238,238,0);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 5){$relleno = true; $this->SetFillColor(202,255,112);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        $mover_x = $mover_x + 36;
	$this->SetFillColor(224,235,255);
    }
    }
    
    if($cambiar_asignaturas == 1){
    for($i=6;$i<=$total_asignaturas-1;$i++)
    {
        $this->SetFont('Arial','',10); // I : Italica; U: Normal;
        $this->Rect($x1+$mover_x,$y1,$x2,5);
        $this->SetXY($x1+$mover_x,$y1);
                
        //cambiar el color del relleno.
        if($i == 6){$relleno = true; $this->SetFillColor(255,165,0);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 7){$relleno = true; $this->SetFillColor(0,250,114);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 8){$relleno = true; $this->SetFillColor(50,205,50);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 9){$relleno = true; $this->SetFillColor(72,118,255);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        //if($i == 10){$relleno = true; $this->SetFillColor(202,255,112);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
	    //if($i == 11){$relleno = true; $this->SetFillColor(202,255,111);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        $mover_x = $mover_x + 36;
	$this->SetFillColor(224,235,255);
    }
    }
    
// SEXTA PARTE DEL RECTANGULO. NOMBRE DE LOS TRIMESTRES
    $x1 = 91; $y1 = 45; $x2 = 36;
    $mover_x = 0;
    for($i=0;$i<=5;$i++)
    {
        $this->SetFont('Arial','',10); // I : Italica; U: Normal;
        $this->Rect($x1+$mover_x,$y1,$x2,5);
        $this->SetXY($x1+$mover_x,$y1);
        $this->Cell(30,5,utf8_decode('Período'),0,2,'L');
        
        $mover_x = $mover_x + 36;
    }

// SEPTIMA PARTE DEL RECTANGULO. NOMBRE DE LOS TRIMESTRES
    $x1 = 91; $y1 = 50; $x2 = 6;
    $mover_x = 0;
    $datos=array('1','2','3','4','TP','NF');
    
    $this->SetFont('Arial','',8); // I : Italica; U: Normal;
    for($i=0;$i<=5;$i++)
    {    
            for($J=0;$J<=5;$J++)
            {
                $this->Rect($x1+$mover_x,$y1,$x2,5);
                $this->SetXY($x1+$mover_x,$y1);
                $this->Cell(3,5,$datos[$J],0,2,'L');
                $mover_x = $mover_x + 6;
            }
    }
    
// OCTAVA PARTE DEL RECTANGULO. Total de Puntos.
    $this->Rect(307,35,8,20);
    $this->SetFont('Arial','',9); // I : Italica; U: Normal;
    $this->RotatedText(312,54,'T.Puntos',90);
}
//********************************************************************************************************************************
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
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
    
// Definimos el tipo de fuente, estilo y tamaño.
        $w2=array(5); //determina el ancho de las columnas
            $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
             $pdf->SetY(22);
             $pdf->Cell(110,$w2[0],'Modalidad: '.$pdf->SetFont('Arial','B',10).$print_bachillerato.$pdf->SetFont(''),0,1,'L');
             $pdf->Cell(100,$w2[0],'Grado: '.$pdf->SetFont('Arial','B',10).$print_grado.$pdf->SetFont('Arial','',10),0,0,'L');
             $pdf->Cell(100,$w2[0],utf8_decode('Sección: ').$print_seccion,0,0,'L');
             $pdf->Cell(30,$w2[0],utf8_decode('Año Lectivo: ').$print_ann_lectivo,0,0,'L');
            $pdf->ln();

//  imprime el encabezado.
    $pdf->cuadro($data);

//  mostrar los valores de la consulta
    // dibujar encabezado de la tabla.
    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
    $pdf->SetFillColor(224,235,255);
    //Cabecera
    $w=array(5,80,6,8); //determina el ancho de las columnas
    $w2=array(7,12,70,50,8); //determina el ancho de las columnas
    $numero_linea = 1; $i = 1; $fill = true; $total_puntos = 0;
    $pdf->SetXY(6,55);
    
// hacer nuevamente la consulta.
      consultas(5,0,$codigo_all,'','','',$db_link,'');
    // recorrer las asignaturas con las notas.
	while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            // >Impresión de la primera asignatura. primer ciclo, segundo y tercero.
                if ($i == 1){
                    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                    $pdf->Cell($w[0],$w2[0],$numero_linea,0,0,'C',$fill);
                    $pdf->Cell($w[1],$w2[0],utf8_decode(trim($row['apellido_alumno'])),0,0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    if($row['nota_p_p_1'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_1']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_2'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_2']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_3'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_3']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_4'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_4']),1),0,0,'C',$fill);}
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row['total_puntos_media']),1),0,0,'C',$fill);
                    
                    $pdf->SetFont('Arial','B',7); // I : Italica; U: Normal;
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_final']),1),1,0,'C',$fill);
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    
                    //acumular el total de puntos.
                    $total_puntos = $total_puntos + $row['total_puntos_media'];
                }
                
                if ($i >= 2 && $i<=6)
                {
                    if($row['nota_p_p_1'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_1']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_2'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_2']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_3'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_3']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_4'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_4']),1),0,0,'C',$fill);}
                    
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row['total_puntos_media']),1),0,0,'C',$fill);
                    
                    $pdf->SetFont('Arial','B',7); // I : Italica; U: Normal;
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_final']),1),1,0,'C',$fill);
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    
                    if ($i >= 2 && $i<=6)
                    {
                        //acumular el total de puntos.
                        $total_puntos = $total_puntos + $row['total_puntos_media'];
                    }
                }

                
            // validar $i = 11
                if($i == $total_asignaturas)
                {
                    // imprimir el total de puntos.
                    $pdf->SetFont('Arial','B',8); // I : Italica; U: Normal;
                    $pdf->Cell($w[3],$w2[0],number_format($total_puntos,1),1,0,'C',$fill);
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    
                    $pdf->ln();
                    $numero_linea++;
                    $i=1;
                    $total_puntos = 0;
                    $pdf->SetX(6);
               // para el color de las filas.
                       $fill=!$fill;
                            //validar salto
                            if($numero_linea == 21)
                            {
                                $pdf->AddPage();
                                $pdf->cuadro($data);
                                $pdf->SetX(6);
                            }
                }
                else{
                    // acumular valor de $i para cambiar de asignatura.    
                        $i++;}
        }
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////CREAR EL SEGUNDO GRUPO DE ASIGNATURAS./////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$cambiar_asignaturas = 1;
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
    $pdf->SetY(20);
    $pdf->SetX(6);
    
// Definimos el tipo de fuente, estilo y tamaño.
        $w2=array(5); //determina el ancho de las columnas
            $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
             $pdf->SetY(22);
             $pdf->Cell(110,$w2[0],'Modalidad: '.$pdf->SetFont('Arial','B',10).$print_bachillerato.$pdf->SetFont(''),0,1,'L');
             $pdf->Cell(100,$w2[0],'Grado: '.$pdf->SetFont('Arial','B',10).$print_grado.$pdf->SetFont('Arial','',10),0,0,'L');
             $pdf->Cell(100,$w2[0],utf8_decode('Sección: ').$print_seccion,0,0,'L');
             $pdf->Cell(30,$w2[0],utf8_decode('Año Lectivo: ').$print_ann_lectivo,0,0,'L');
            $pdf->ln();

//  imprime el encabezado.
    $pdf->cuadro($data);

//  mostrar los valores de la consulta
    // dibujar encabezado de la tabla.
    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
    $pdf->SetFillColor(224,235,255);
    //Cabecera
    $w=array(5,80,6,8); //determina el ancho de las columnas
    $w2=array(7,12,70,50,8); //determina el ancho de las columnas
    $numero_linea = 1; $i = 1; $fill = true; //$total_puntos = 0;
    $pdf->SetXY(6,55);
    
// hacer nuevamente la consulta.
      consultas(5,0,$codigo_all,'','','',$db_link,'');
    // recorrer las asignaturas con las notas.
	while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            // >Impresión de la primera asignatura. primer ciclo, segundo y tercero.
                if ($i == 7){
                    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                    $pdf->Cell($w[0],$w2[0],$numero_linea,0,0,'C',$fill);
                    $pdf->Cell($w[1],$w2[0],utf8_decode(trim($row['apellido_alumno'])),0,0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    if($row['nota_p_p_1'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_1']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_2'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_2']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_3'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_3']),1),0,0,'C',$fill);}
                    if($row['nota_p_p_4'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_4']),1),0,0,'C',$fill);}
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row['total_puntos_media']),1),0,0,'C',$fill);
                    
                    $pdf->SetFont('Arial','B',7); // I : Italica; U: Normal;
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_final']),1),1,0,'C',$fill);
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    
                    //acumular el total de puntos.
                    $total_puntos = $total_puntos + $row['total_puntos_media'];
                }
                
                if ($i >= 8 && $i<=$total_asignaturas)
                {
                    if($i >= 8 && $i<=10){
                        if($row['nota_p_p_1'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_1']),1),0,0,'C',$fill);}
                        if($row['nota_p_p_2'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_2']),1),0,0,'C',$fill);}
                        if($row['nota_p_p_3'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_3']),1),0,0,'C',$fill);}
                        if($row['nota_p_p_4'] == 0){$pdf->Cell($w[2],$w2[0],'',0,0,0,'C',$fill);}else{$pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_p_p_4']),1),0,0,'C',$fill);}
                        
                        $pdf->Cell($w[2],$w2[0],number_format(trim($row['total_puntos_media']),1),0,0,'C',$fill);
                        
                        $pdf->SetFont('Arial','B',7); // I : Italica; U: Normal;
                        $pdf->Cell($w[2],$w2[0],number_format(trim($row['nota_final']),1),1,0,'C',$fill);
                        $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    }

                    
                    if ($i >= 8 && $i<=10)
                    {
                        //acumular el total de puntos.
                        $total_puntos = $total_puntos + $row['total_puntos_media'];
                    }
                }
            // validar $i = 11
                if($i == $total_asignaturas)
                {
                    // imprimir el total de puntos.
                    $pdf->SetFont('Arial','B',8); // I : Italica; U: Normal;
                    $pdf->Cell($w[3],$w2[0],number_format($total_puntos,1),1,0,'C',$fill);
                    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
                    
                    $pdf->ln();
                    $numero_linea++;
                    $i=1;
                    $total_puntos = 0;
                    $pdf->SetX(6);
               // para el color de las filas.
                       $fill=!$fill;
                            //validar salto
                            if($numero_linea == 21)
                            {
                                $pdf->AddPage();
                                $pdf->cuadro($data);
                                $pdf->SetX(6);
                            }
                }
                else{
                    // acumular valor de $i para cambiar de asignatura.    
                        $i++;}
        }
        $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
        $print_nombre = "Calificaciones - " . $print_grado . "-" . $print_seccion . '.pdf';
        
        //$print_nombre = $path_root . '/registro_academico/temp/' . trim($nombre_completo_alumno) . ' ' . trim($print_grado) . ' ' . trim($print_seccion) . '.pdf';
        $pdf->Output($print_nombre,$modo);
?>