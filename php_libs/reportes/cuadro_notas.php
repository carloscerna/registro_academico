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
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
	    break;
            }
      
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    //Logo
//    $this->Image('../registro_academico/img/'.$_SESSION['logo_uno'],10,8,33);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Título
    $this->Cell(250,4,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(250,4,'Cuadro de Registro de Notas Por Trimestre',0,1,'C');
    //Salto de línea
   // $this->Ln(20);
}

//Pie de página
function Footer()
{
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,15,10,12,15);		
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    //Número de página
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb} '.$fecha,0,0,'C');
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
    $w=array(5,20,80,40,40,40,15,15,15); //determina el ancho de las columnas
    $w1=array(10,15); //determina el ancho de las columnas
    
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),'LTR',0,'C',1);
    $this->Ln();    
        $this->Cell($w[0],7,'','LBR',0,'C',1);
        $this->Cell($w[1],7,utf8_decode('(Código)'),'LBR',0,'C',1);
        $this->Cell($w[2],7,utf8_decode('(Orden Alfabético por Apellido)'),'LBR',0,'C',1);

    $num = 1;
    for($j=0;$j<=2;$j++)
        for($i=0;$i<=3;$i++){
            if ($num == 4){
                $this->Cell($w1[0],7,'Prom.','1',0,'C',1);
                $num = 0;}
            else{
                $this->Cell($w1[0],7,$num,'1',0,'C',1);}
            $num++;}

        $this->Cell($w1[1],7,'Puntos','LBR',0,'C',1);   // lineas para el total de puntos
        $this->Cell($w1[1],7,'Final','LBR',0,'C',1);    // lineas para el total de puntos
        $this->Cell($w1[1],7,'Repo.','LBR',0,'C',1);    // lineas para el total de puntos
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
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 15, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,10);
    
    $data = array();
//Títulos de las columnas
    $header=array('Nº','N I E','Nombre de Alumnos/as','Primer Trimestre','Segundo Trimestre','Tercer Trimestre','Total','Prom.','Nota');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
    
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
//  $pdf->SetY(15);
    //$pdf->SetX(10);
      consultas(4,0,$codigo_all,'','','',$db_link,'');
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    //  imprimir datos del bachillerato.
    $pdf->Cell(140,8,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,8,$print_grado,0,0,'L');
    $pdf->Cell(20,8,$print_seccion,0,0,'L');
    $pdf->Cell(35,8,$print_ann_lectivo,0,0,'L');
    $pdf->ln();
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;

    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

//  mostrar los valores de la consulta
    $w=array(5,20,80,10,15); //determina el ancho de las columnas
    
    $fill = false; $i=1;
     while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $pdf->Cell($w[0],6.3,$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],6.3,trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[2],6.3,utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno

            for($j=0;$j<=2;$j++)
                for($K=0;$K<=3;$K++){
                    $pdf->Cell($w[3],6.3,'','LR',0,'C',$fill);}   // lineas de ancoho 10.
            for($j=0;$j<=2;$j++)    //lineas de ancho 12
                $pdf->Cell($w[4],6.3,'','LR',0,'C',$fill);    // lineas de ancoho 12.
            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;
                if($i==26 || $i == 51){
		  $pdf->Cell(array_sum($w)+(10*11)+(15*2),0,'','T');
		  $pdf->AddPage();
		      $pdf->Cell(140,8,$print_bachillerato,0,0,'L');
		      $pdf->Cell(40,8,$print_grado,0,0,'L');
		      $pdf->Cell(20,8,$print_seccion,0,0,'L');
		      $pdf->Cell(35,8,$print_ann_lectivo,0,0,'L');
		      $pdf->Ln();
		  $pdf->FancyTable($header);}
            }
            
                // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                      $pdf->Cell($w[0],6.3,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],6.3,'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],6.3,'','LR',0,'C',$fill);  // NIE

			for($j=0;$j<=2;$j++)
			    for($K=0;$K<=3;$K++){
			        $pdf->Cell($w[3],6.3,'','LR',0,'C',$fill);}   // lineas de ancoho 10.
			         for($j=0;$j<=2;$j++)    //lineas de ancho 12
			              $pdf->Cell($w[4],6.3,'','LR',0,'C',$fill);    // lineas de ancoho 12.

                      $pdf->Ln();   
                      $fill=!$fill;
                      
                      // Salto de Línea.
		      if($i==25 || $i == 50){
		           $pdf->Cell(array_sum($w)+(10*11)+(15*2),0,'','T');
			   $pdf->AddPage();
		           $pdf->Cell(140,8,$print_bachillerato,0,0,'L');
		           $pdf->Cell(40,8,$print_grado,0,0,'L');
		           $pdf->Cell(20,8,$print_seccion,0,0,'L');
		           $pdf->Cell(35,8,$print_ann_lectivo,0,0,'L');
		           $pdf->Ln();
			   $pdf->FancyTable($header);}
                  }
                  
            $pdf->Cell(array_sum($w)+(10*11)+(15*2),0,'','T');
// Salida del pdf.
    $pdf->Output();
?>