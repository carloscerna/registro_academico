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
  $codigo_alumno = $_REQUEST["txtidalumno"];
  $codigo_matricula = $_REQUEST["txtcodmatricula"];
  $firma = $_REQUEST["chkfirma"];
  $sello = $_REQUEST["chksello"];
  $chkfoto = $_REQUEST["chkfoto"];
  $print_uno = $_REQUEST["print_uno"]; // variable para imprimir un solo registro.
// variables a utilizar en el encabezado de la tabla para las notas.
      $registro_docente = "Docente";
      $periodo_trimestre = "TRIMESTRE";
      $conteo_reprobadas = array();
      $conteo_aprobadas = array();
// variable de la conexión dbf.
    $db_link = $dblink;
// buscar la consulta y la ejecuta.
  consultas(5,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
	    $print_codigo_grado = utf8_decode(trim($row['codigo_grado']));
	    $print_codigo_bachillerato = (trim($row['codigo_bachillerato']));
	    $print_codigo_alumno = $row['codigo_alumno'];
	    $print_codigo_matricula = $row['cod_matricula'];
	    break;
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
	
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $nivel_educacion, $print_codigo_grado, $print_seccion, $print_grado_media, $print_ann_lectivo;
    // Ancho de la linea.
        $this->SetLineWidth(.7);
	$this->SetDrawColor(10,29,247);
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,7,6,20,25);
    //Arial bold 15
    $this->SetFont('Times','B',14);
    //Rectangulo para la foto.
    $this->RoundedRect(195, 5, 25, 30, 3.5, '1234','');

    //Título
    $this->Cell(200,7,utf8_decode($_SESSION['institucion']),0,1,'C');
    // cambiar el titulo, si es basica o media.
    if($nivel_educacion == 'Educación Básica'){
	$this->Cell(200,7,'Boleta de Notas - '.$print_ann_lectivo.' - '.$nivel_educacion.' - '.$print_codigo_grado.' - '."'".$print_seccion."'",0,1,'C');
    }else{
	$this->Cell(200,7,'Boleta de Notas - '.$print_ann_lectivo.' - '.$nivel_educacion.' - '.$print_grado_media.' - '."'".$print_seccion."'",0,1,'C');
    }
    // linea horizontal y rectangulo para el encabezado.
    $this->Line(27,20,195,20);
    $this->SetLineWidth(.3);
    $this->RotatedText(30,27,'Nombre',0);
    $this->RotatedText(30,37,'NIE',0);
    $this->RoundedRect(50, 22, 140, 7, 1.5, '1234','');	// para el nombre
    $this->RoundedRect(50, 31, 35, 7, 1.5, '1234','');	// para el nie
}

//Pie de página
function Footer()
{
    global $registro_docente, $firma, $sello, $print_codigo_alumno, $print_codigo_matricula;
    //Posición: a 1,5 cm del final
    $this->SetY(-15);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear una línea de la primera firma.
    $this->Line(15,148,90,148);
    //Crear una línea de la segunda firma.
    $this->Line(120,148,200,148);
    //Crear una línea
    $this->Line(5,155,230,155);
    //Nombre Docente
    $this->SetX(15);
    $this->Cell(75,6,$registro_docente,0,0,'C');

    //Firma Director.
    if($firma == 'yes'){
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
	$this->Image($img,130,130,70,15);
    }
    if($sello == 'yes'){
	$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
	$this->Image($img_sello,95,125,30,30);
    }
    //Nombre Director
    $this->RotatedText(130,151,cambiar_de_del($_SESSION['nombre_director']),0,1,'C');
    $this->RotatedText(140,154,'Director(a)',0,1,'C');
    //Número de página y fecha
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
		
    $this->SetY(-10);
    $this->SetX(10);
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'L');
    $this->SetX(80);
    $this->Cell(0,10,$fecha,0,0,'L');
    $this->Cell(0,10,'Id_a:'.$print_codigo_alumno.' Id_m:'.$print_codigo_matricula,0,0,'R');
}

//Tabla coloreada
function FancyTable($header)
{
  global $print_codigo_bachillerato;
     
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(229,229,229);
    $this->SetTextColor(0,0,0);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    //Cabecera
    $w=array(180,10,80,85,10,12,88,42); //determina el ancho de las columnas
    $w2=array(5,12); //determina el ancho de las columnas

     // encabezado de la boleta.
   if($print_codigo_bachillerato >= '01' and  $print_codigo_bachillerato <= '05')
    {
        $this->RoundedRect(5, 40, 215, 10, 1, '1234', '');	// primer cuadro.
	$this->RoundedRect(5, 40, 90, 10, 0.5, '1234','');	// para el nombre de la asignatura
	$this->RoundedRect(5, 40, 90, 70, 0.5, '1234','');	// para el nombre de la asignatura y las filas
	$this->RoundedRect(95, 40, 80, 10, 0.5, '1234','');	// para los periodos o trimestres
	    $this->RoundedRect(95, 45, 80, 5, 0.5, '1234','');	// para las divisiones del los trimestres
		$this->RoundedRect(95, 45, 80, 5, 0.5, '1234','');	// para las divisiones del los trimestres
		$ancho = 13.33;
		    for($j=0;$j<=5;$j++){
                    $this->RoundedRect(95, 45, $ancho, 5, 0.5, '');
		    $ancho = $ancho + 13.33;			// crear 6 columnas.
		    }
	$this->RoundedRect(175, 40, 45, 10, 0.5, '');		// para el resultado final
	$this->RoundedRect(175, 40, 45, 70, 0.5, '');		// para el resultado final y las filas
		$this->RoundedRect(175, 45, 45, 5, 0.5, '');	// para las divisiones del los trimestres
		$ancho = 11.25;
		    for($j=0;$j<=3;$j++){
                    $this->RoundedRect(175, 45, $ancho, 5, 0.5, '');
		    $ancho = $ancho + 11.25;}			// crear 6 columnas.
    
    // Colocar nombres en los cuadros.
	$this->SetFont('Times','B',10);
	$this->RotatedText(25,45,'A S I G N A T U R A S',0);
    // Colocar trimestre
	$this->RotatedText(120,44,'T R I M E S T R E',0);
                $this->RotatedText(101,49,'1',0);
		$this->RotatedText(128,49,'2',0);
		$this->RotatedText(155,49,'3',0);

                $this->RotatedText(111,49,'Resul.',0);
		$this->RotatedText(138,49,'Resul.',0);
		$this->RotatedText(163,49,'Resul.',0);		
    // Colocar nombre de resultados finales.
	$this->RotatedText(180,44,'RESULTADO FINAL',0);
	    $this->RotatedText(177,49,'T.P.',0);
	    $this->RotatedText(188,49,'P.3.P.',0);
	    $this->RotatedText(198,49,'REPO.',0);
	    $this->RotatedText(210,49,'N.F.',0);		
    }else{
	//$pdf->RoundedRect(55, 105, 30, 8, 2, '1234', '');
	$this->RoundedRect(5, 40, 215, 10, 0.5, '');	// primer cuadro.
	$this->RoundedRect(5, 40, 70, 10, 0.5, '');	// para el nombre de la asignatura
	$this->RoundedRect(75, 40, 100, 10, 0.5, '');	// para los periodos o trimestres
	    $this->RoundedRect(75, 45, 100, 5, 0.5, '');	// para las divisiones del los trimestres
		$this->RoundedRect(75, 45, 100, 5, 0.5, '');	// para las divisiones del los trimestres
		$ancho = 12.5;
		    for($j=0;$j<=6;$j++){
                    $this->RoundedRect(75, 45, $ancho, 5, 0.5, '');
		    $ancho = $ancho + 12.5;			// crear 8 columnas.
		    }
	$this->RoundedRect(175, 40, 45, 10, 0.5, '');		// para el resultado final
		$this->RoundedRect(175, 45, 45, 5, 0.5, '');	// para las divisiones del los trimestres
		$ancho = 11.25;
		    for($j=0;$j<=3;$j++){
                    $this->RoundedRect(175, 45, $ancho, 5, 0.5, '');
		    $ancho = $ancho + 11.25;}			// crear 4 columnas.
    // Colocar nombres en los cuadros.
	$this->SetFont('Times','B',10);
	$this->RotatedText(25,45,'A S I G N A T U R A S',0);
    // Colocar trimestre
	$this->RotatedText(120,44,'P E R I O D O S',0);
                $this->RotatedText(79,49,'1',0);
		$this->RotatedText(105,49,'2',0);
		$this->RotatedText(130,49,'3',0);
		$this->RotatedText(155,49,'4',0);

                $this->RotatedText(89,49,'Resul.',0);
		$this->RotatedText(115,49,'Resul.',0);
		$this->RotatedText(139,49,'Resul.',0);
		$this->RotatedText(165,49,'Resul.',0);		
    // Colocar nombre de resultados finales.
	$this->RotatedText(180,44,'RESULTADO FINAL',0);
	    $this->RotatedText(177,49,'T.P.',0);
	    $this->RotatedText(188,49,'P.4.P.',0);
	    $this->RotatedText(198,49,'REPO.',0);
	    $this->RotatedText(210,49,'N.F.',0);		
    } 
    //Restauración de colores y fuentes
    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('Times','',10);
    //Datos
    $fill=false;
}
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal'); // Tamaño Legal
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetY(40);
    $pdf->SetX(5);
//  mostrar los valores de la consulta para listado de las notas o solo una.
    if($print_uno == 'yes')
	{consultas_alumno(2,0,$codigo_all,$codigo_alumno,$codigo_matricula,$db_link,'');}
    else
	{consultas(5,0,$codigo_all,'','','',$db_link,'');}
  // condicionar el ancho de cada columna.
  if ($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '05'){
    $w=array(180,10,80,90,14.6,12,11); //determina el ancho de las columnas
    $w2=array(5,12); //determina el ancho de las columnas
  }else{
    $w=array(160,10,80,70,13,12,11); //determina el ancho de las columnas
    $w2=array(5,12); //determina el ancho de las columnas
  }

    $fill = false; $i=1;  $suma = 0; $aprobado_reprobado = array();
        while($row = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
            {
		
		// variables a utilizar.
		    $nombre_completo_alumno = utf8_decode(trim($row['apellido_alumno']));
		    $numero_identificacion_estudiantil = trim($row['codigo_nie']);
		    $print_codigo_alumno = $row['codigo_alumno'];
		    $print_codigo_matricula = $row['cod_matricula'];
		    $nombre_asignatura = utf8_decode(trim($row['n_asignatura']));
		    $foto = trim($row['foto']);
		// imprimir la foto en la boleta
                    if ($chkfoto == 'yes'){
                        if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$foto)){$fotos = 'foto_no_disponible.png';}else{$fotos = $foto;}
                            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$fotos;
                            $pdf->image($img,197,6,21,27);
                    }
            if ($i == 1){
								$pdf->AddPage();
				$pdf->SetFont('Times','B',13);	// cambiar de tamaño de letra para el nombre y el nie.
					$pdf->RotatedText(53,27,$nombre_completo_alumno,0);
					$pdf->RotatedText(53,36,$numero_identificacion_estudiantil,0);
				$pdf->SetFont('Times','',10); // I : Italica; U: Normal;
					// dibujar encabezado de la tabla.
				$pdf->SetY(50);
            $pdf->FancyTable($header);
	    
            // >Impresión de la primera asignatura. primer ciclo, segundo y tercero.
            if ($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '05'){
                $aprobado_reprobado[0] = cambiar_aprobado_reprobado_b($row['nota_p_p_1']);
                $aprobado_reprobado[1] = cambiar_aprobado_reprobado_b($row['nota_p_p_2']);
                $aprobado_reprobado[2] = cambiar_aprobado_reprobado_b($row['nota_p_p_3']);
                $aprobado_reprobado[3] = cambiar_aprobado_reprobado_b($row['nota_p_p_4']);
                $aprobado_reprobado[4] = cambiar_aprobado_reprobado_b($row['nota_final']);                
                }
            else{
                $aprobado_reprobado[0] = cambiar_aprobado_reprobado_m($row['nota_p_p_1']);
                $aprobado_reprobado[1] = cambiar_aprobado_reprobado_m($row['nota_p_p_2']);
                $aprobado_reprobado[2] = cambiar_aprobado_reprobado_m($row['nota_p_p_3']);
                $aprobado_reprobado[3] = cambiar_aprobado_reprobado_m($row['nota_p_p_4']);
		if($row['recuperacion'] != 0){
		$aprobado_reprobado[4] = cambiar_aprobado_reprobado_b(($row['nota_final']+$row['recuperacion']/2));}
		  else{$aprobado_reprobado[4] = cambiar_aprobado_reprobado_b($row['nota_final']);}
                }

		///////////////////////////////////////////////////////////////////////////////////////////////////
		/////NOMBRE DE LA ASIGNATURA Y CAMBIO DE CONCEPTOS.///////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////
                $pdf->Cell($w[3],$w2[0],$nombre_asignatura,0,0,'L',$fill);	//Nombre de la Asignatura.
                
                if ($i>=7){
		     if($print_bachillerato == "Preparatoria")
			{
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_1']),0,0,'C',$fill);
			    if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
			    
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_2']),0,0,'C',$fill);
			    if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			    
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_3']),0,0,'C',$fill);
			    if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}    
			    
			    // Para Media.
			    if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= '09'){
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_4']),0,0,'C',$fill);
			    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
			    }
			    
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_final']),0,0,'C',$fill);
			    
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['recuperacion']),0,0,'C',$fill);
			    if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}	
			}
			else{
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_1']),0,0,'C',$fill);
			    if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_2']),0,0,'C',$fill);
			    if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_3']),0,0,'C',$fill);
			    if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
			    
			    // Para Media.
			    if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= '09'){
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_4']),0,0,'C',$fill);
			    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
			    }
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_final']),0,0,'C',$fill);
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['recuperacion']),0,0,'C',$fill);
			    if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}
			}
                      
                }else{
		        if($print_bachillerato == "Preparatoria")
			{
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_1']),0,0,'C',$fill);
				if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_2']),0,0,'C',$fill);
				if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_3']),0,0,'C',$fill);
				if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
				
    			    // Para Media.
			    if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= '09'){
				$pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_4']),0,0,'C',$fill);
				    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}}
				$pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_final']),0,0,'C',$fill);
				$pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['recuperacion']),0,0,'C',$fill);
				    if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}	
			}
			else{
			if($row['nota_p_p_1'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_1']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
                       
                        if($row['nota_p_p_2'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_2']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}

			if($row['nota_p_p_3'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_3']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
                   	
			// Para Media.
			if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= '09'){
                   	if($row['nota_p_p_4'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_4']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
                        if($row['total_puntos_media'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['total_puntos_media']),'L',0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			}
			
                        if ($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '05'){
			if($row['total_puntos_basica'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['total_puntos_basica']),'L',0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
                        }
                        
			if($row['nota_final'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['nota_final']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
		        if($row['recuperacion'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[6],$w2[0],verificar_nota($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
                        //if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}
			}	// del condicional preparatoria.
                        }
                                           
			$pdf->Ln();
			$fill=!$fill;
		} //ESTO HACE EL CALCULO PARA LA PRIMERA LINEA DEL REGISTRO.
                ////////////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////////////////////////////////////////////////////////////////////////
            else{ // ESTO ES PARA LA SEGUNDA LINEA EN ADELANTE.

            if ($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '05')
              {
                $aprobado_reprobado[0] = cambiar_aprobado_reprobado_b($row['nota_p_p_1']);
                $aprobado_reprobado[1] = cambiar_aprobado_reprobado_b($row['nota_p_p_2']);
                $aprobado_reprobado[2] = cambiar_aprobado_reprobado_b($row['nota_p_p_3']);
                $aprobado_reprobado[3] = cambiar_aprobado_reprobado_b($row['nota_p_p_4']);
                if($row['recuperacion'] != 0){
		$verificarnotarecupercion = number_format($row['nota_final']+$row['recuperacion']/2,0);
		$aprobado_reprobado[4] = cambiar_aprobado_reprobado_b($verificarnotarecupercion);}
		  else{$aprobado_reprobado[4] = cambiar_aprobado_reprobado_b($row['nota_final']);}
            
                $pdf->Cell($w[3],$w2[0],$nombre_asignatura,0,0,'L',$fill);
                
                
		if ($i>=7)
		{
		if($print_bachillerato == "Preparatoria")
			{
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_1']),0,0,'C',$fill);
			    if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_2']),0,0,'C',$fill);
			    if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_3']),0,0,'C',$fill);
			    if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
			    
			    if ($print_codigo_bachillerato == '06' || $print_codigo_bachillerato == '07' || $print_codigo_bachillerato == '08' || $print_codigo_bachillerato == "09"){
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_4']),0,0,'C',$fill);
			    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
			    }
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_final']),0,0,'C',$fill);
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['recuperacion']),0,0,'C',$fill);
			    			if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[6],$w2[0],verificar_nota($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
				//if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}	
			}
			else{
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_1']),0,0,'C',$fill);
			    if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_2']),0,0,'C',$fill);
			    if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_3']),0,0,'C',$fill);
			    if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
			    
			    if ($print_codigo_bachillerato == '06' || $print_codigo_bachillerato == '07' || $print_codigo_bachillerato == '08' || $print_codigo_bachillerato == "09"){
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto($row['nota_p_p_4']),0,0,'C',$fill);
			    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
			    }
                      	
                      	if($row['total_puntos_basica'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['total_puntos_basica']),'L',0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
                      		$pdf->Cell($w[6],$w2[0],cambiar_concepto($row['nota_final']),0,0,'C',$fill);
                      		$pdf->Cell($w[6],$w2[0],cambiar_concepto($row['recuperacion']),0,0,'C',$fill);
				if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[6],$w2[0],verificar_nota($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
                      	//if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}                          
                	}
		}
                else
                	{
		
		if($print_bachillerato == "Preparatoria")
			{
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_1']),0,0,'C',$fill);
			    if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_2']),0,0,'C',$fill);
			    if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_3']),0,0,'C',$fill);
			    if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
			    
			    if ($print_codigo_bachillerato == '06' || $print_codigo_bachillerato == '07' || $print_codigo_bachillerato == '08' || $print_codigo_bachillerato == "09"){
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_p_p_4']),0,0,'C',$fill);
			    }
			    
			    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}    
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['nota_final']),0,0,'C',$fill);
			    $pdf->Cell($w[4],$w2[0],cambiar_concepto_letras_prepa($row['recuperacion']),0,0,'C',$fill);
			    			if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[6],$w2[0],verificar_nota($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
				//if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}	
			}
			else{
			if($row['nota_p_p_1'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_1']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
                        
			if($row['nota_p_p_2'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_2']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}

			if($row['nota_p_p_3'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_3']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
                   	
                        // condicion para el bachillerato
			if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= "09")
                        {
                            if($row['nota_p_p_4'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_4']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                            if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
			}
                        
                        // condición para basica.
			if($row['total_puntos_basica'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['total_puntos_basica']),'L',0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if($row['nota_final'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['nota_final']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if($row['recuperacion'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[6],$w2[0],verificar_nota($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
                        //if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}
                    }
		}
            }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////// CONDICIONES PARA EDUCACIÓN MEDIA.
        if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= '09')
            {
                $pdf->Cell($w[3],$w2[0],$nombre_asignatura,0,0,'L',$fill);
                // Obtener el concepto de aprobado y reprobado e introducirlo en una matriz.    
                    $aprobado_reprobado[0] = cambiar_aprobado_reprobado_m($row['nota_p_p_1']);
		    $aprobado_reprobado[1] = cambiar_aprobado_reprobado_m($row['nota_p_p_2']);
		    $aprobado_reprobado[2] = cambiar_aprobado_reprobado_m($row['nota_p_p_3']);
		    $aprobado_reprobado[3] = cambiar_aprobado_reprobado_m($row['nota_p_p_4']);
                		                				
		if($row['recuperacion'] != 0){
		    $verificarnotarecupercion = number_format($row['nota_final']+$row['recuperacion']/2,0);
		    $aprobado_reprobado[4] = cambiar_aprobado_reprobado_m($verificarnotarecupercion);}
		  else{$aprobado_reprobado[4] = cambiar_aprobado_reprobado_m($row['nota_final']);}
                
                    // colocar la el concepto para la conducta.
                    if(trim($row['codigo_asignatura'] == '30')){
                      if($row['nota_p_p_1'] != 0){$pdf->Cell($w[4],$w2[0],cambiar_concepto(trim($row['nota_p_p_1'])),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                    }
                    else{
                      if($row['nota_p_p_1'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_1']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                    }                   
                        if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
                    
		    // colocar la el concepto para la conducta.
                    if(trim($row['codigo_asignatura'] == '30')){
                    	if($row['nota_p_p_2'] != 0){$pdf->Cell($w[4],$w2[0],cambiar_concepto(trim($row['nota_p_p_2'])),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
		    }
		    else{
			if($row['nota_p_p_2'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_2']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
		    }
                        if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}

		    // colocar la el concepto para la conducta.
                    if(trim($row['codigo_asignatura'] == '30')){
			if($row['nota_p_p_3'] != 0){$pdf->Cell($w[4],$w2[0],cambiar_concepto(trim($row['nota_p_p_3'])),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
		    }
		    else{
			if($row['nota_p_p_3'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_3']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
		    }
                        if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
                   	
                     // colocar la el concepto para la conducta.
		     if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= "09"){
                    if(trim($row['codigo_asignatura'] == '30')){
			if($row['nota_p_p_4'] != 0){$pdf->Cell($w[4],$w2[0],cambiar_concepto(trim($row['nota_p_p_4'])),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
		    }
		    else{
			if($row['nota_p_p_4'] != 0){$pdf->Cell($w[4],$w2[0],trim($row['nota_p_p_4']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
		    }
                        if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,0,'C',$fill);$pdf->SetFont('');}
		     }
									
			if($row['total_puntos_media'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['total_puntos_media']),'L',0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if($row['nota_final'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['nota_final']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if($row['recuperacion'] != 0){$pdf->Cell($w[6],$w2[0],trim($row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			if(verificar_nota_media($row['nota_final'],$row['recuperacion'] != 0)){$pdf->Cell($w[6],$w2[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($w[6],$w2[0],'',0,0,'C',$fill);}
			//if($aprobado_reprobado[4] == "A"){$pdf->Cell($w[6],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[6],$w2[0],$aprobado_reprobado[4],0,0,'C',$fill);$pdf->SetFont('');}                                                
            }

                $pdf->Ln();
                $fill=!$fill;
                }	// if de cierre de la condicion.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             if ($print_codigo_bachillerato >= '06' and $print_codigo_bachillerato <= '09')
	     {
               if ($i==$total_asignaturas){
		///////////////////////////////////////////////////////////////////////
		// revisar si hay registros de segunda matricula para este alumno.
		//////////////////////////////////////////////////////////////////////
		$query_2m = "SELECT dosm.id_segunda_matricula, dosm.codigo_asignatura, dosm.codigo_alumno, dosm.codigo_matricula, dosm.nota_p_p_1, dosm.nota_p_p_2, dosm.nota_p_p_3, dosm.nota_p_p_4,
				    asig.nombre as nombre_asignatura, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno
				    FROM alumno_segunda_matricula dosm
				    INNER JOIN asignatura asig ON asig.codigo = dosm.codigo_asignatura
				    INNER JOIN alumno a ON a.id_alumno = dosm.codigo_alumno
				    where dosm.codigo_alumno = '".$print_codigo_alumno."' and dosm.codigo_matricula = '".$print_codigo_matricula."'";
		// ejecutar la consulta.
		
		$result_2m = $db_link -> query($query_2m);
		$fila_2m = $result_2m -> rowCount();
		
		if($fila_2m !=0)
		{
		    $pdf->SetFont('','B',10);
		    $yy = $pdf->gety();
		    $pdf->RoundedRect(5,$yy, 215, 5, 0.5, '');	// primer cuadro.
		    $pdf->RotatedText(80,$yy+4,'ASIGNATURA(S) EN SEGUNDA MATRICULA',0);
		    $pdf->SetFont('','',10);
		    $pdf->ln();
		   while ($row2m=pg_fetch_assoc($result_2m)) // bucle para la recorrer las asignaturas.
		    {
			$aprobado_reprobado[0] = cambiar_aprobado_reprobado_m($row2m['nota_p_p_1']);
			$aprobado_reprobado[1] = cambiar_aprobado_reprobado_m($row2m['nota_p_p_2']);
			$aprobado_reprobado[2] = cambiar_aprobado_reprobado_m($row2m['nota_p_p_3']);
			$aprobado_reprobado[3] = cambiar_aprobado_reprobado_m($row2m['nota_p_p_4']);
		    
			$pdf->Cell($w[3],$w2[0],trim($row2m['nombre_asignatura']),0,0,'L',$fill);

			if($row['nota_p_p_1'] != 0){$pdf->Cell($w[4],$w2[0],trim($row2m['nota_p_p_1']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[0] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[0],0,0,'C',$fill);$pdf->SetFont('');}
                        if($row['nota_p_p_2'] != 0){$pdf->Cell($w[4],$w2[0],trim($row2m['nota_p_p_2']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[1] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[1],0,0,'C',$fill);$pdf->SetFont('');}
			if($row['nota_p_p_3'] != 0){$pdf->Cell($w[4],$w2[0],trim($row2m['nota_p_p_3']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
                        if($aprobado_reprobado[2] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[2],0,0,'C',$fill);$pdf->SetFont('');}
			
			if ($print_codigo_bachillerato == '06' || $print_codigo_bachillerato == '07' || $print_codigo_bachillerato == '08' || $print_codigo_bachillerato == "09"){
			    if($row['nota_p_p_4'] != 0){$pdf->Cell($w[4],$w2[0],trim($row2m['nota_p_p_4']),0,0,'C',$fill);}else{$pdf->Cell($w[4],$w2[0],'',0,0,'C',$fill);}
			    if($aprobado_reprobado[3] == "A"){$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,1,'C',$fill);}else{$pdf->SetFont('Arial','B',9);$pdf->Cell($w[5],$w2[0],$aprobado_reprobado[3],0,1,'C',$fill);$pdf->SetFont('');}			
			    }
		    }
		}
		    $pdf->Cell(215,0,'','T');
                    $pdf->SetFont('','B',8);
                    $pdf->Ln();
                    $pdf->Cell(140,$w2[0],('Nota. Para Aprobar cada asignatura por Período >= 6.'),0,0,'L');
                    $pdf->Cell(40,$w2[0],'A = Aprobado; R = Reprobado',0,1,'L');
		    
		   // $pdf->AddPage();
		       if($print_uno == 'no') {
			  //$pdf->AddPage();
		       }
		    $i = 0;}
	     }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($print_codigo_bachillerato >= '03' and $print_codigo_bachillerato <= '05'){

             // IMPRIMIR ASPECTOS DE LA CONDUCTA - EDUCACIÓN BÁSICA.
             if ($i == 6){
                 //Colores, ancho de línea y fuente en negrita
                $pdf->SetFillColor(255,255,255);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Times','B',12);
                $pdf->Cell(215,$w2[0],'EDUCACIÓN MORAL Y CÍVICA - Aspectos de la Conducta',1,0,'C',true);
                $pdf->Ln();
                    //Restauración de colores y fuentes
                $pdf->SetFillColor(224,235,255);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Times','',10);}

             if ($i == $total_asignaturas){
                     $pdf->Cell(215,0,'','T');
                     $pdf->SetFont('','B',10);
                     $pdf->Ln();
                     $pdf->Cell(120,$w2[0],'Nota. Para Aprobar cada asignatura por Trimestre >= 5.',0,1,'L');
		     $pdf->Cell(160,$w2[0],'Sí alguna ASIGNATURA aparece en BLANCO consulte con el DOCENTE que la imparte.',0,0,'L');
                     $pdf->Cell(40,$w2[0],'A = Aprobado; R = Reprobado',0,1,'L');
                 //     $pdf->AddPage();
		    if($print_uno == 'no') {
			 // $pdf->AddPage();
		       }
               $i = 0;}
             }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             if ($print_bachillerato == 'Preparatoria'){
             if ($i== $total_asignaturas){
               $pdf->Cell(215,0,'','T');
                     $pdf->SetFont('','B',10);
                     $pdf->Ln();
                     $pdf->Cell(110,$w2[0],'Nota. DB-Domini Bajo; DM-Dominio Medio; DA-Dominio Alto',0,1,'L');
                     //$pdf->Cell(110,$w2[0],'A = Aprobado; R = Reprobado',0,1,'L');
               //$pdf->AddPage();
	       	      if($print_uno == 'no') {
			  //$pdf->AddPage();
		       }
               $i = 0;}
             }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             // acumulador para el numero de asignaturas
              $i++;
            }
            // despues del bucle.
            $pdf->Cell(215,0,'','T');
// Construir el nombre del archivo.
	$nombre_archivo = $print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo;
// Salida del pdf.
    $pdf->Output($nombre_archivo,'I');
?>