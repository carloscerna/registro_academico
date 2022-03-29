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
      $conducta = $_REQUEST['lstconducta'];
      $codigo_matricula = $_REQUEST['txtcodmatricula'];
      $codigo_alumno = $_REQUEST['txtidalumno'];
      $estudias = $_REQUEST['lstestudia'];
      $traslado = $_REQUEST['txttraslado'];;
      $mostrar_traslado = $_REQUEST["chktraslado"];
      $firma = $_REQUEST["chkfirma"];
      $sello = $_REQUEST["chksello"];
      $db_link = $dblink;
// buscar la consulta y la ejecuta.
    consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
	    break;
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
    $this->Line(115,225,200,225);
    
    //Firma Director.
    if($firma == 'yes'){
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
	$this->Image($img,120,210,70,15);
    }
    if($sello == 'yes'){
	$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
	$this->Image($img_sello,95,200,30,30);
    }
    
    //Nombre Director
    $this->RotatedText(120,230,cambiar_de_del($_SESSION['nombre_director']),0,1,'C');
    $this->RotatedText(140,235,'Director(a)',0,1,'C');
    
       // ARMAR pie de página.
	$style6 = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => '0', 'color' => array(0,0,0));
	$this->CurveDraw(0, 267, 120, 270, 155, 250, 225, 250, null, $style6);
	$this->CurveDraw(0, 266, 120, 269, 155, 249, 225, 249, null, $style6);	
}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(20, 20);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
//Títulos de las columnas
    $pdf->AliasNbPages();
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetY(20);
    $pdf->SetX(15);
// Diseño de Lineas y Rectangulos.
    $pdf->SetFillColor(224);
    $pdf->RoundedRect(45, 55, 155, 8, 2, '1234', 'DF');
    $pdf->RoundedRect(105, 65, 35, 8, 2, '1234', '');
    //$pdf->RoundedRect(168, 65, 32, 8, 2, '1234', '');
    $pdf->RoundedRect(53, 75, 147, 8, 2, '1234', '');
    $pdf->RoundedRect(55, 85, 30, 8, 2, '1234', '');
    $pdf->RoundedRect(55, 95, 30, 8, 2, '1234', '');
    $pdf->RoundedRect(55, 105, 30, 8, 2, '1234', '');
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
//  mostrar los valores de la consulta
    $w=array(60,25,125); //determina el ancho de las columnas
    $w2=array(8,12); //determina el ancho de las columnas
// Variables.
    $fill = false; $i=1;  $promedio_institucional = 0; $promedio_paes = 0; $promedio_final = 0; $pi=0;
// Consultar y Ejecutar el Query.
      consultas_alumno(3,0,$codigo_all,$codigo_alumno,$codigo_matricula,'',$db_link,'');      
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            // Definimos el tipo de fuente, estilo y tamaño.
            $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
             $pdf->SetXY(15,45);
             $pdf->MultiCell(180,8,utf8_decode("El/la suscrito(a) Director(a) CERTIFICA que él/la: "));
             

             $pdf->RotatedText(20,60,'Alumno(a): ',0);
             $pdf->SetFont('Arial','IB',13);
             $pdf->RotatedText(50,60,utf8_decode(trim($row['apellido_alumno'])),0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);
        
	     $pdf->RotatedText(20,70,utf8_decode('Número de Identificación Estudiantil (NIE): '),0);
             $pdf->SetFont('Arial','B',13);
             $pdf->RotatedText(110,70,utf8_decode(trim($row['codigo_nie'])),0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);

       /*  $pdf->RotatedText(150,70,utf8_decode('N.° DUI: '),0);
             $pdf->SetFont('Arial','B',13);
             $pdf->RotatedText(170,70,utf8_decode(trim($row['encargado_dui'])),0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);
*/
	     $pdf->RotatedText(30,80,utf8_decode('Modalidad: '),0);
             $pdf->SetFont('Arial','B',12);
             $pdf->RotatedText(55,80,$print_bachillerato,0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);

	     $pdf->RotatedText(30,90,utf8_decode('Grado: '),0);
             $pdf->SetFont('Arial','B',12);
             $pdf->RotatedText(57,90,$print_grado,0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);
	     
	     $pdf->RotatedText(30,100,utf8_decode('Sección: '),0);
             $pdf->SetFont('Arial','B',12);
             $pdf->RotatedText(57,100,$print_seccion,0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);
	     
	     $pdf->RotatedText(30,110,utf8_decode('Año Lectivo: '),0);
             $pdf->SetFont('Arial','B',12);
             $pdf->RotatedText(57,110,$print_ann_lectivo,0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);

             
            $pdf->SetXY(20,120);
	    $pdf->MultiCell(180,10,utf8_decode($estudias).utf8_decode(" en esta institución y demostrando ".$conducta." conducta hacia sus compañeros y maestros."));
            
	    if($mostrar_traslado == "yes"){
	    $pdf->ln();
	    $pdf->MultiCell(180,10,utf8_decode($traslado));}
	    
            $pdf->ln();
            $pdf->MultiCell(180,10,utf8_decode("Y para los usos que el(la) interesado(a) estime conveniente se extiende la presente constancia en ".$_SESSION['se_extiende']." de Santa Ana a los ". strtolower(num2letras($dia))." días de ".$mes." de ".strtolower(num2letras($año))));
						
              	$fill=!$fill;	
              		$i++;
		break;
            }
            // despues del bucle.
// Salida del pdf.
    $pdf->Output();
?>