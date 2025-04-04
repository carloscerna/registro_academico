<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Archivos que se incluyen.
     include($path_root."/registro_academico/includes/funciones.php");
     include($path_root."/registro_academico/includes/consultas.php");
     include $path_root."/registro_academico/includes/mainFunctions_conexion.php";
// Llamar a la libreria fpdf
     include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
     header("Content-Type: text/html; charset=UTF-8");    
//
    $fecha_mes = $_REQUEST["FechaMes"];//$_REQUEST["fechaMes"];
    $fecha_ann = $_REQUEST["lstannlectivo"]; //$_REQUEST["fechaAnn"];
    $quincena = "Q1";
// variables y consulta a la tabla.
     $codigo_all = $_REQUEST["todos"];
     $db_link = $dblink;
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
//        $total_de_dias = date('t');    // total de dias.
        $total_de_dias = cal_days_in_month(CAL_GREGORIAN, (int)$fecha_mes, $año);
        $NombreMes = $meses[(int)$fecha_mes - 1];

// definimos 2 array uno para los nombre de los dias y otro para los nombres de los meses
    $nombresDias = array("D", "L", "M", "M", "J", "V", "S" );
    $nombresMeses = array(1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
// ARMANR FECHA DEPENDIENDO DE LA QUINCENA
    if($quincena == "Q1"){
        $fecha_inicio = $año . '-' . $fecha_mes . '-01'; 
        $fecha_fin = $año . '-' . $fecha_mes . '-'.$total_de_dias; 
    }
// establecemos la fecha de inicio
    $inicio =  DateTime::createFromFormat('Y-m-d', $fecha_inicio, new DateTimeZone('America/El_Salvador'));
// establecemos la fecha final (fecha de inicio + dias que queramos)
    $fin =  DateTime::createFromFormat('Y-m-d', $fecha_fin, new DateTimeZone('America/El_Salvador'));
// definier el número de días dependiendo de la quincena.
    $fin = $fin->modify( '+1 day' );
// creamos el periodo de fechas
    $periodo = new DatePeriod($inicio, new DateInterval('P1D') ,$fin);
// Crear Matriz para el # de dia y nombre del dia.
    $nombreDia_a = array(); $numeroDia_a = array();
// recorremos las dechas del periodo
    foreach($periodo as $date){
    // definimos la variables para verlo mejor
        $nombreDia = $nombresDias[$date->format("w")];
        $nombreMes = $nombresMeses[$date->format("n")];
        $numeroDia = $date->format("j");
        $anyo = $date->format("Y");
    // mostramos los datos
        $nombreDia_a[] = $nombreDia;
        $numeroDia_a[] = $numeroDia;
    }
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
    while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
    {
        $print_bachillerato = convertirtexto('Modalidad: '.trim($row['nombre_bachillerato']));
        $nombre_modalidad = convertirtexto(trim($row['nombre_bachillerato']));
        $print_grado = convertirtexto('Grado:     '.trim($row['nombre_grado']));
        $nombre_grado = convertirtexto(trim($row['nombre_grado']));
        $print_seccion = convertirtexto('Sección:  '.trim($row['nombre_seccion']));
        $nombre_seccion = convertirtexto(trim($row['nombre_seccion']));
        $print_ann_lectivo = convertirtexto('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
        $nombre_ann_lectivo = convertirtexto(trim($row['nombre_ann_lectivo']));
        $print_periodo = convertirtexto('Período: _____');
        break;
    }
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_periodo, $pagina_impar, $NombreMes;
        //Logo
    	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
        $this->Image($img,20,15,12,15);
        //Arial bold 15
        $this->SetFont('Arial','B',13);
        //Título
        $this->RotatedText(35,15,convertirtexto($_SESSION['institucion']),0);
        $this->RotatedText(35,20,'Lista de Asistencia - Mes: '. strtoupper($NombreMes),0,1,'L');
        $this->SetFont('Arial','',9);
        // Imprimir Modalidad y Asignatura.
        $this->RoundedRect(34, 22, 130, 6, 1.5, '1234', '');
        $this->RotatedText(35,26,$print_bachillerato,0);
        $this->RoundedRect(34, 29, 100, 6, 1.5, '1234', '');
        $this->RotatedText(35,33,'Nombre Asignatura: ',0);
	    $this->RoundedRect(160, 29, 100, 6, 1.5, '1234', '');
        $this->RotatedText(162,33,'Nombre Docente: ',0);
    // Generar el cuadro en donde se ubicara el grado, sección y año lectivo.
        $this->RoundedRect(230, 11, 35, 18, 3.5, '1234', '');
        $this->RotatedText(232,16,$print_grado,0);
        $this->RotatedText(232,20,$print_seccion,0);
        $this->RotatedText(232,24,$print_ann_lectivo,0);
        $this->RotatedText(232,28,$print_periodo,0);
    // Ubicación en donde empezará a imprimirlos valores.
    $this->SetY(35);
}
//Pie de página
function Footer()
{
    //
  // Establecer formato para la fecha.
  // 
   date_default_timezone_set('America/El_Salvador');
   setlocale(LC_TIME, 'spanish');
    //Posición: a 1,5 cm del final
    $this->SetY(-15);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb} '.$fecha,0,0,'C');
}
//Tabla coloreada
function FancyTable($header)
{
    global $nombreDia_a, $numeroDia_a, $total_de_dias;
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(6,14,70,170); //determina el ancho de las columnas
    $w1=array(5.66); //determina el ancho de las columnas
    
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',1);
        // Coloca las lineas de los cuadros.
            $this->SetFillColor(255,255,255);
            $this->SetTextColor(0);
            for($j=0;$j<=$total_de_dias-1;$j++){
                if($nombreDia_a[$j] == "S" || $nombreDia_a[$j] == "D"){
                    $this->SetFillColor(213, 216, 220);
                        $this->Cell($w1[0],7,$nombreDia_a[$j],1,0,'C',1);
                }else{
                    $this->SetFillColor(255,255,255);
                        $this->Cell($w1[0],7,$nombreDia_a[$j],1,0,'C',1);
                }
            }
              $this->Ln();
            $this->Cell($w[0],7,'','LBR',0,'C',1);
            $this->Cell($w[1],7,'','LBR',0,'C',1);
        $this->Cell($w[2],7,convertirtexto('(Orden Alfabético por Apellido)'),'LBR',0,'C',1);
        $this->SetFillColor(255,255,255);
        for($j=0;$j<=$total_de_dias-1;$j++)
        if($nombreDia_a[$j] == "S" || $nombreDia_a[$j] == "D"){
            $this->SetFillColor(213, 216, 220);
                $this->Cell($w1[0],7,$numeroDia_a[$j],'1',0,'C',1);
        }else{
            $this->SetFillColor(255,255,255);
                $this->Cell($w1[0],7,$numeroDia_a[$j],'1',0,'C',1);
        }
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
    $pdf=new PDF('L','mm','Letter');
    $data = array();
    #Establecemos los márgenes izquierda, arriba y derecha:
    $pdf->SetMargins(10, 15, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,10);
//Títulos de las columnas
    $header=array('Nº','NIE','Nombre de Alumnos/as');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',13); // I : Italica; U: Normal;
    $pdf->ln();
  // variables y consulta a la tabla.
      consultas(4,0,$codigo_all,'','','',$db_link,'');
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    $w=array(6,14,70,5.66); //determina el ancho de las columnas
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
                    $pdf->Cell($w[0],6,$i,'LR',0,'C',$fill);        // núermo correlativo
                    $pdf->Cell($w[1],6,trim($row['codigo_nie']),'LR',0,'C',$fill);        // núermo correlativo
                    $pdf->Cell($w[2],6,convertirtexto(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
                $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
                for($j=0;$j<=$total_de_dias-1;$j++){
                    if($nombreDia_a[$j] == "S" || $nombreDia_a[$j] == "D"){
                        $pdf->SetFillColor(255, 255, 255);
                            $pdf->Cell($w[3],6,'','LR',0,'C',$fill);
                    }else{
                        $pdf->SetFillColor(213, 216, 220);
                            $pdf->Cell($w[3],6,'','1',0,'C',$fill);
                    }
                }
                    $pdf->ln();
                if($i==25 || $i == 50 || $i == 75){
                    $pdf->Cell(array_sum($w)+$w[3]*29,0,'','T');
                    $pdf->AddPage();
                    $pdf->FancyTable($header);
                }
                $fill=!$fill;
                $i=$i+1;
            }
           // rellenar cuando i sea menor que 25.
           if($i<26){
            $menor_de_25 = $i;
            $linea_faltante = 25 - $menor_de_25;
            $numero_p = $menor_de_25 - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
                      $pdf->Cell($w[0],6,$menor_de_25++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],6,'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],6,'','LR',0,'l',$fill);  // nombre del alumno.
			for($j=0;$j<=$total_de_dias-1;$j++){
                if($nombreDia_a[$j] == "S" || $nombreDia_a[$j] == "D"){
                    $pdf->SetFillColor(255, 255, 255);
                        $pdf->Cell($w[3],6,'','LR',0,'C',$fill);
                }else{
                    $pdf->SetFillColor(213, 216, 220);
                        $pdf->Cell($w[3],6,'','1',0,'C',$fill);
                }
            }
            $pdf->Ln();   
            $fill=!$fill;
            // Salto de Línea.
            if($i==25 || $i == 50 || $i == 75){
                $pdf->Cell(array_sum($w)+$w[3]*29,0,'','T');
                $pdf->AddPage();
                $pdf->FancyTable($header);}
            }
           }
           else{
          // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
                      $pdf->Cell($w[0],6,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],6,'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],6,'','LR',0,'l',$fill);  // nombre del alumno.
                for($j=0;$j<=$total_de_dias-1;$j++){
                    if($nombreDia_a[$j] == "S" || $nombreDia_a[$j] == "D"){
                        $pdf->SetFillColor(255, 255, 255);
                            $pdf->Cell($w[3],6,'','LR',0,'C',$fill);
                    }else{
                        $pdf->SetFillColor(213, 216, 220);
                            $pdf->Cell($w[3],6,'','1',0,'C',$fill);
                    }
                }               
                $pdf->Ln();   
                $fill=!$fill;
                      // Salto de Línea.
        		if($i==25 || $i == 50 || $i == 75){
				    $pdf->Cell(array_sum($w)+$w[3]*29,0,'','T');
				    $pdf->AddPage();
				    $pdf->FancyTable($header);}
                }
           }    // IF QUE AGREGAR LAS LINEAS FALTANTES.
$pdf->Cell(array_sum($w)+$w[3]*29,0,'','T');
// Salida del pdf.
    $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
    $print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-AS.pdf';
    $pdf->Output($print_nombre,$modo);