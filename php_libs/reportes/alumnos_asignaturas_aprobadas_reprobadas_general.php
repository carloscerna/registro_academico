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
    $nota_p = $_REQUEST["lsttri"];
    $db_link = $dblink;
    $j = 0;  $data = array();
// buscar la consulta y la ejecuta.
    consultas(5,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = trim($row['nombre_seccion']);
            $print_ann_lectivo = trim($row['nombre_ann_lectivo']);
	    
	    $print_codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
	    $codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
            $print_codigo_grado = trim($row['codigo_grado']);
            $codigo_seccion = trim($row['codigo_seccion']);
            $codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);

            $data[$j] = substr(trim($row['n_asignatura']),0,4);
            $j++;
            }

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
// Colocar la leyenda del Período.
   if($nota_p == "nota_p_p_1"){
      $periodo = "Período 1";
   }
   if($nota_p == "nota_p_p_2"){
      $periodo = "Período 2";
   }
   if($nota_p == "nota_p_p_3"){
      $periodo = "Período 3";
   }
   if($nota_p == "nota_p_p_4"){
      $periodo = "Período 4";
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
      global $periodo;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,5,4,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(200,7,$_SESSION['institucion'],0,1,'C');
    $this->Cell(200,7,'INFORME ASIGNATURAS APROBADAS O REPROBADAS - '.utf8_decode($periodo),0,1,'C');
    $this->Line(0,20,280,20);
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
    $this->Cell(0,6,utf8_decode('Nº ').$this->PageNo().'/{nb}',0,0,'C');

}

//********************************************************************************************************************************
function cuadro($data)
{
// ARMAR LA CUADRICULA POR SEGMENTOS.

// PRIMERA PARTE DEL RECTANGULO.
    $this->Rect(6,35,227,20);
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
    $this->Rect(91,35,126,20);
    $this->Rect(91,35,126,5);
    $this->SetXY(120,35);
    $this->Cell(30,5,'ASIGNATURA',0,2,'C');
// QUINTA PARTE DEL RECTANGULO. NOMBRE DE LAS ASIGNATURAS
    $x1 = 91; $y1 = 40; $x2 = 14;
    $mover_x = 0; $relleno = false;
    for($i=0;$i<=8;$i++)
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
        if($i == 6){$relleno = true; $this->SetFillColor(225,204,0);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 7){$relleno = true; $this->SetFillColor(204,153,255);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        if($i == 8){$relleno = true; $this->SetFillColor(204,255,255);$this->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$this->SetFillColor(255,255,255);$relleno = false;}
        
        $mover_x = $mover_x + 14;
    }
// SEXTA PARTE DEL RECTANGULO. NOMBRE DE LOS TRIMESTRES
    $x1 = 91; $y1 = 45; $x2 = 14;
    $mover_x = 0;
    for($i=0;$i<=8;$i++)
    {
        $this->SetFont('Arial','',10); // I : Italica; U: Normal;
        $this->Rect($x1+$mover_x,$y1,$x2,5);
        $this->SetXY($x1+$mover_x,$y1);
        $this->Cell(30,5,'',0,2,'L');
        
        $mover_x = $mover_x + 14;
    }

// SEPTIMA PARTE DEL RECTANGULO.
    $x1 = 91; $y1 = 50; $x2 = 7;
    $mover_x = 0;
    $datos=array('NF','C');
    
    $this->SetFont('Arial','',10); // I : Italica; U: Normal;
    for($i=0;$i<=8;$i++)
    {    
            for($J=0;$J<=1;$J++)
            {
                $this->Rect($x1+$mover_x,$y1,$x2,5);
                $this->SetXY($x1+$mover_x,$y1);
                $this->Cell(3,5,$datos[$J],0,2,'L');
                $mover_x = $mover_x + 7;
            }
    }
    
// OCTAVA PARTE DEL RECTANGULO. Total de Puntos.
    $this->Rect(217,35,8,20);
    $this->SetFont('Arial','',9); // I : Italica; U: Normal;
    $this->RotatedText(222,54,'T.Repo',90);

// NOVENA PARTE DEL RECTANGULO. DERECHO A RECUPERACIÓN. (D. A  R.)
    $this->Rect(225,35,8,20);
    $this->SetFont('Arial','',9); // I : Italica; U: Normal;
    $this->RotatedText(230,54,'D. a R.',90);
}
//********************************************************************************************************************************
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Letter');
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
             $pdf->Cell(50,$w2[0],utf8_decode('Sección: ').$print_seccion,0,0,'L');
             $pdf->Cell(30,$w2[0],utf8_decode('Año Lectivo: ').$print_ann_lectivo,0,0,'L');
            $pdf->ln();

    $pdf->cuadro($data);
//  mostrar los valores de la consulta
    // dibujar encabezado de la tabla.
    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
    $pdf->SetFillColor(224,235,255);
    //Cabecera
    $w=array(5,80,7,8,12); //determina el ancho de las columnas
    $w2=array(8,12,70,50,8); //determina el ancho de las columnas
    $numero_linea = 1; $i = 1; $fill = true; $total_reprobadas = 0;
    $pdf->SetXY(6,55);
    
// hacer nuevamente la consulta.
// variables del campo de la nota.
   //$nota_p = 'nota_p_p_2';
   $alumnos_aprobados_masculino = 0;
   $alumnos_reprobados_masculino = 0;
   $alumnos_reprobados_femenino = 0;
   $alumnos_aprobados_femenino = 0;
   
   // para el total de asignaturas.
   $aprobada_lenguaje = array(); $reprobada_lenguaje = array();
   $aprobada_matematica = array(); $reprobada_matematica = array();
   $aprobada_sociales = array(); $reprobada_sociales = array();
   $aprobada_ciencias = array(); $reprobada_ciencias = array();
   $aprobada_ingles = array(); $reprobada_ingles = array();
   $aprobada_informatica = array(); $reprobada_informatica = array();
   $aprobada_orientacion = array(); $reprobada_orientacion = array();
   $aprobada_habilitacion = array(); $reprobada_habilitacion = array();
   $aprobada_seminario = array(); $reprobada_seminario = array();
   
   $tapa = 0; $tapf = 0; $tapl = 0; $tapm = 0; $taps = 0; $tapc = 0;$tapi = 0; $trpl = 0; $trpf = 0; $trpc = 0; $trpm = 0; $trps = 0; $trpi = 0; $trpf = 0; $trpa = 0;
   $tapo = 0; $trpo = 0; $taph = 0; $trph = 0; $tapse = 0; $trpse = 0;
   $arep = 0; $aapro = 0;
   
   // aprobadas masculino
   $taplm = 0; $taplf = 0; $trelm = 0; $trelf = 0;
   $tapmm = 0; $tapmf = 0; $tremm = 0; $tremf = 0;
   $tapcm = 0; $tapcf = 0; $trecm = 0; $trecf = 0;
   $tapsm = 0; $tapsf = 0; $tresm = 0; $tresf = 0;
   $tapam = 0; $tapaf = 0; $tream = 0; $treaf = 0;
   $tapfm = 0; $tapff = 0; $trefm = 0; $treff = 0;
   
   $tapom = 0; $tapof = 0; $treom = 0; $treof = 0;
   $taphm = 0; $taphf = 0; $trehm = 0; $trehf = 0;
   $tapsem = 0; $tapsef = 0; $tresem = 0; $tresef = 0;
   
      consultas(5,0,$codigo_all,'','','',$db_link,'');
    // recorrer las asignaturas con las notas.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            // >Impresión de la primera asignatura. primer ciclo, segundo y tercero.
                if ($i == 1){
                    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                    $pdf->Cell($w[0],$w2[0],$numero_linea,0,0,'C',$fill);
                    $pdf->Cell($w[1],$w2[0],utf8_decode(trim($row['apellido_alumno'])),0,0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row[$nota_p]),1),0,0,'C',$fill);
                    
                    
                    if($row[$nota_p] < 6){
                        $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
                        $pdf->Cell($w[2],$w2[0],cambiar_aprobado_reprobado_m(number_format(trim($row[$nota_p])),1),0,0,'C',$fill);
                        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                    }else{
                        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                        $pdf->Cell($w[2],$w2[0],cambiar_aprobado_reprobado_m(number_format(trim($row[$nota_p])),1),0,0,'C',$fill);
                        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;                        
                    }
                    //acumular el total reprobadas
                        if($row[$nota_p] < 6){
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   $reprobada_lenguaje[] = $total_reprobadas;
			   
			      if ($row['genero'] == 'm'){$trelm++;}
			      if ($row['genero'] == 'f'){$trelf++;}
			}
			else{
			   $tapl++;
			   $aprobada_lenguaje[] = $tapl;
			   
			      if ($row['genero'] == 'm'){$taplm++;}
			      if ($row['genero'] == 'f'){$taplf++;}
			}
                }
                
                if ($i >= 2 && $i<=9)
               {   
                    $pdf->Cell($w[2],$w2[0],number_format(trim($row[$nota_p]),1),0,0,'C',$fill);
                    
                    if($row['nota_final'] < 6){
                        $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
                        $pdf->Cell($w[2],$w2[0],cambiar_aprobado_reprobado_m(number_format(trim($row[$nota_p])),1),0,0,'C',$fill);
                        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                    }else{
                        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                        $pdf->Cell($w[2],$w2[0],cambiar_aprobado_reprobado_m(number_format(trim($row[$nota_p])),1),0,0,'C',$fill);
                        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;                        
                    }
                    
                    if ($i >= 2 && $i<=9)
                    {
		  //acumular el valor - matematica.
		     if($i == 2){
                        if($row[$nota_p] < 6){
	    		      if ($row['genero'] == 'm'){$tremm++;}
			      if ($row['genero'] == 'f'){$tremf++;}
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   $trpm++;
			   $reprobada_matematica[] = $trpm;}
			else{
			   $tapm++;
			   $aprobada_matematica[] = $tapm;
			   
			      if ($row['genero'] == 'm'){$tapmm++;}
			      if ($row['genero'] == 'f'){$tapmf++;}
			}
		     }
		     
                    //acumular el valor - ciencias.
		     if($i == 3){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$trecm++;}
			      if ($row['genero'] == 'f'){$trecf++;}
			      
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   $trpc++;
			   $reprobada_ciencias[] = $trpc;}
			else{
			   $tapc++;
			   $aprobada_ciencias[] = $tapc;
	       		      if ($row['genero'] == 'm'){$tapcm++;}
			      if ($row['genero'] == 'f'){$tapcf++;}
			      
			}
		     }

                    //acumular el valor - estudios sociales.
		     if($i == 4){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$tresm++;}
			      if ($row['genero'] == 'f'){$tresf++;}
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   $trps++;
			   $reprobada_sociales[] = $trps;}
			else{
			   $taps++;
			   $aprobada_sociales[] = $taps;
			   
			      if ($row['genero'] == 'm'){$tapsm++;}
			      if ($row['genero'] == 'f'){$tapsf++;}
			}
		     }

                    //acumular el valor - educaciòn artística o ingles.
		     if($i == 5){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$tream++;}
			      if ($row['genero'] == 'f'){$treaf++;}
			      
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   if ($codigo_bachillerato == '05'){
			   $total_reprobadas = $total_reprobadas + 1;
			   }
			   $trpa++;
			   $reprobada_ingles[] = $trpa;}
			else{
			   $tapa++;
			   $aprobada_ingles[] = $tapa;
			   
			      if ($row['genero'] == 'm'){$tapam++;}
			      if ($row['genero'] == 'f'){$tapaf++;}
			}
		     }

                    //acumular el valor - educacion fisica.
		     if($i == 6){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$trefm++;}
			      if ($row['genero'] == 'f'){$treff++;}
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   // 
			   $trpf++;
			   $reprobada_informatica[] = $trpf;}
			else{
			   $tapf++;
			   $aprobada_informatica[] = $tapf;
			   
			      if ($row['genero'] == 'm'){$tapfm++;}
			      if ($row['genero'] == 'f'){$tapff++;}
			}
		     }
                         //acumular el valor - Orientacion para la Vida.
		     if($i == 7){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$treom++;}
			      if ($row['genero'] == 'f'){$treof++;}
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   // 
			   $trpo++;
			   $reprobada_orientacion[] = $trpo;}
			else{
			   $tapo++;
			   $aprobada_orientacion[] = $tapo;
			   
			      if ($row['genero'] == 'm'){$tapom++;}
			      if ($row['genero'] == 'f'){$tapof++;}
			}
		     }
                         //acumular el valor - Orientacion para la Vida.
		     if($i == 8){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$trehm++;}
			      if ($row['genero'] == 'f'){$trehf++;}
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   // 
			   $trph++;
			   $reprobada_habilitacion[] = $trph;}
			else{
			   $tapo++;
			   $aprobada_habilitacion[] = $taph;
			   
			      if ($row['genero'] == 'm'){$taphm++;}
			      if ($row['genero'] == 'f'){$taphf++;}
			}
		     }
                         //acumular el valor - Orientacion para la Vida.
		     if($i == 9){
                        if($row[$nota_p] < 6){
			      if ($row['genero'] == 'm'){$tresem++;}
			      if ($row['genero'] == 'f'){$tresef++;}
			   
			   // Este dato se toma en cuenta pra saber si han pasado todas en limpio.
			   $total_reprobadas = $total_reprobadas + 1;
			   
			   // 
			   $trpse++;
			   $reprobada_seminario[] = $trpse;}
			else{
			   $tapo++;
			   $aprobada_seminario[] = $tapse;
			   
			      if ($row['genero'] == 'm'){$tapsem++;}
			      if ($row['genero'] == 'f'){$tapsef++;}
			}
		     }
                    }
                }

            // validar $i = 11
                if($i == $total_asignaturas)
                {
                    if($total_reprobadas > 2){$derecho_recuperacion = 'No';}else{$derecho_recuperacion = "Si";}
                    // imprimir el total de puntos.
                    $pdf->SetFont('Arial','B',10); // I : Italica; U: Normal;
                    $pdf->Cell($w[3],$w2[0],number_format($total_reprobadas,1),1,0,'C',$fill);
                    $pdf->Cell($w[3],$w2[0],$derecho_recuperacion,1,0,'C',$fill);
                    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
                     // dar valor a la variable que verifica el bachillerato.
		     if ($codigo_bachillerato >= '06' && $codigo_bachillerato <= '10'){
			if($total_reprobadas >= 1)
			{
			   if ($row['genero'] == 'm'){$alumnos_reprobados_masculino++;}
			   if ($row['genero'] == 'f'){$alumnos_reprobados_femenino++;}
			}else{
			   if ($row['genero'] == 'm'){$alumnos_aprobados_masculino++;}
			   if ($row['genero'] == 'f'){$alumnos_aprobados_femenino++;}
			}
		     }
                    $pdf->ln();
                    $numero_linea++;
                    $i=1;
                    $total_reprobadas = 0;
                    $pdf->SetX(6);
               // para el color de las filas.
                       $fill=!$fill;
                            //validar salto
                            if($numero_linea == 20)
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
// Agregar al final total de reprobados por asignatura y por todas.
// QUINTA PARTE DEL RECTANGULO. NOMBRE DE LAS ASIGNATURAS
    $x1 = $pdf->GetX()+85; $y1 = $pdf->GetY(); $x2 = 14;
    $mover_x = 0; $relleno = false;
    for($i=0;$i<=8;$i++)
    {
        $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
        $pdf->Rect($x1+$mover_x,$y1,$x2,5);
        $pdf->SetXY($x1+$mover_x,$y1);
        
        //cambiar el color del relleno.
        if($i == 0){$relleno = true; $pdf->SetFillColor(255,165,0);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        if($i == 1){$relleno = true; $pdf->SetFillColor(0,250,114);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        if($i == 2){$relleno = true; $pdf->SetFillColor(50,205,50);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        if($i == 3){$relleno = true; $pdf->SetFillColor(72,118,255);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        if($i == 4){$relleno = true; $pdf->SetFillColor(238,238,0);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        if($i == 5){$relleno = true; $pdf->SetFillColor(202,255,112);$pdf->Cell($x2,5,$data[$i],0,1,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
	if($i == 6){$relleno = true; $pdf->SetFillColor(202,255,112);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
	if($i == 7){$relleno = true; $pdf->SetFillColor(202,255,112);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        if($i == 8){$relleno = true; $pdf->SetFillColor(202,255,112);$pdf->Cell($x2,5,$data[$i],0,2,'L',$relleno);}else{$pdf->SetFillColor(255,255,255);$relleno = false;}
        $mover_x = $mover_x + 14;
    }

// SEPTIMA PARTE DEL RECTANGULO.
    $x1 = 91; $y1 = $pdf->gety(); $x2 = 7;
    $mover_x = 0;
    $datos=array('M','F');
    
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    for($i=0;$i<=8;$i++)
    {    
            for($J=0;$J<=1;$J++)
            {
                $pdf->Rect($x1+$mover_x,$y1,$x2,5);
                $pdf->SetXY($x1+$mover_x,$y1);
		
		if($i == 5){
		$pdf->Cell(3,5,$datos[$J],0,1,'L');  
		}else{
                $pdf->Cell(3,5,$datos[$J],0,2,'L');}
                $mover_x = $mover_x + 7;
            }
    }
        // Presentar la sumatoria y el promedio de la asignatura.
            $pdf->Ln();
            $pdf->SetFont('Arial','b',9); 
            $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'APROBADAS',0,0,'R',$fill);   
            $pdf->Cell($w[2],$w2[0],$taplm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$taplf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapmm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapmf,0,0,'C',$fill);
	    
            $pdf->Cell($w[2],$w2[0],$tapcm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapcf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapsm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapsf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapam,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapaf,0,0,'C',$fill);
	    
            $pdf->Cell($w[2],$w2[0],$tapfm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapff,0,0,'C',$fill);
	    
	    $pdf->Cell($w[2],$w2[0],$tapom,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapof,0,0,'C',$fill);

	    $pdf->Cell($w[2],$w2[0],$taphm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$taphf,0,0,'C',$fill);

	    $pdf->Cell($w[2],$w2[0],$tapsem,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapsef,0,1,'C',$fill);
	    ////////////////////////////////////////////////////////////////
	    $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'REPROBADAS',0,0,'R',$fill);   
            $pdf->Cell($w[2],$w2[0],$trelm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$trelf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tremm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tremf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$trecm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$trecf,0,0,'C',$fill);
	    
            $pdf->Cell($w[2],$w2[0],$tresm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tresf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tream,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$treaf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$trefm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$treff,0,0,'C',$fill);
	    
	    $pdf->Cell($w[2],$w2[0],$treom,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$treof,0,0,'C',$fill);
	    
	    $pdf->Cell($w[2],$w2[0],$trehm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$trehf,0,0,'C',$fill);
	    
	    $pdf->Cell($w[2],$w2[0],$tresem,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tresef,0,1,'C',$fill);
	    ///////////////////////////////////////////////////////////////
	    $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'APROBADAS + REPROBADAS',0,0,'R',$fill);
            $pdf->Cell($w[2],$w2[0],$taplm+$trelm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$taplf+$trelf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapmm+$tremm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapmf+$tremf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapcm+$trecm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapcf+$trecf,0,0,'C',$fill);
	    
            $pdf->Cell($w[2],$w2[0],$tapsm+$tresm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapsf+$tresf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapam+$tream,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapaf+$treaf,0,0,'C',$fill);

            $pdf->Cell($w[2],$w2[0],$tapfm+$trefm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapff+$treff,0,0,'C',$fill);
	    
	    $pdf->Cell($w[2],$w2[0],$tapom+$treom,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapof+$treof,0,0,'C',$fill);

	    $pdf->Cell($w[2],$w2[0],$taphm+$trehm,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$taphf+$trehf,0,0,'C',$fill);
	    
	    $pdf->Cell($w[2],$w2[0],$tapsem+$tresem,0,0,'C',$fill);
	    $pdf->Cell($w[2],$w2[0],$tapsef+$tresef,0,1,'C',$fill);
	    //////////////////////////////////////////////////////////////////	    
	    $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'TOTAL',0,0,'R',$fill);	    
	    $pdf->Cell($w[4],$w2[0],count($aprobada_lenguaje)+ count($reprobada_lenguaje),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_matematica) + count($reprobada_matematica),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_ciencias) + count($reprobada_ciencias),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_sociales) + count($reprobada_sociales),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_ingles) + count($reprobada_ingles),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_informatica) + count($reprobada_informatica),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_orientacion) + count($reprobada_orientacion),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_habilitacion) + count($reprobada_habilitacion),0,0,'C',$fill);
	    $pdf->Cell($w[4],$w2[0],count($aprobada_seminario) + count($reprobada_seminario),0,1,'C',$fill);
	    
// Total de alumnos aprobados y reprobados, por masculino y femenino.
	    $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'TOTAL DE ALUMNOS',0,0,'R',$fill);	
	    $pdf->Cell(40,$w2[0],'Masculino',0,0,'C',$fill);
	    $pdf->Cell(40,$w2[0],'Femenino',0,1,'C',$fill);
	    
	    $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'Aprobados(as)',0,0,'R',$fill);	
	    $pdf->Cell(40,$w2[0],$alumnos_aprobados_masculino,0,0,'C',$fill);
	    $pdf->Cell(40,$w2[0],$alumnos_aprobados_femenino,0,1,'C',$fill);

	    $pdf->Cell($w[0],$w2[0],'',0,0,'C',$fill);
            $pdf->Cell($w[1],$w2[0],'Reprobados(as)',0,0,'R',$fill);	
	    $pdf->Cell(40,$w2[0],$alumnos_reprobados_masculino,0,0,'C',$fill);
	    $pdf->Cell(40,$w2[0],$alumnos_reprobados_femenino,0,1,'C',$fill);
	    $pdf->Cell(205,0,'','T');	// LINEA.
// Construir el nombre del archivo.
	$nombre_archivo = $print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo;
// Salida del pdf.
    $pdf->Output($nombre_archivo,'I');  
?>