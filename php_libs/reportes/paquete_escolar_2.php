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
    $por_genero = true;
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))// buscar la consulta y la ejecuta.
            {
            $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
            $print_codigo_grado = trim($row['codigo_grado']);
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
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 11
    $this->SetFont('Arial','B',10);
    //Título
    $this->RotatedText(25,10,utf8_decode('Listado de entrega de '),0);
    $this->RotatedText(25,15,$_SESSION['institucion'],0);
    $this->RotatedText(220,15,utf8_decode('Código: ').$_SESSION['codigo'],0);
    $this->RotatedText(25,20,'Departamento: '.$_SESSION['nombre_departamento'],0);
    $this->RotatedText(100,20,'Fecha:__________________',0);
    $this->RotatedText(150,20,'Nombre Proveedor ',0);
    $this->RotatedText(25,25,utf8_decode('Fecha Límite para entregar el Producto_________________________________________'),0);
}

//Pie de página
function Footer()
{
  // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');
//
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Número de página
    $this->Line(10,205,50,205);
    $this->Cell(20,10,'Firma y Sello del Proveedor',0,0,'L');
    $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}       ',0,0,'R');
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('Arial','B',8);
    //Cabecera
    $w=array(5,75,10,10,70,20,25,40); //determina el ancho de las columnas
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
    $pdf=new PDF('L','mm','Letter');
    $data = array();
//Títulos de las columnas
    $header=array('Nº','Nombre de Alumnos/as','Grado','Género','Nombre de Padre, Madre o Encargado','DUI','Fecha de Entrega','Firma de Quién Recibe');
    $pdf->AliasNbPages();
    $pdf->AddPage();
// Fijamos la posición de X y Y.
    $pdf->SetY(27);
    $pdf->SetX(10);
// buscar la consulta y la ejecuta.
    if($por_genero == 'true'){
        consultas(9,0,$codigo_all,'','','',$db_link,'');
    }else{
        consultas(4,0,$codigo_all,'','','',$db_link,'');
    }
  
// Cargar el encabezao de la tabla.
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
//  mostrar los valores de la consulta
    $w=array(5,75,10,10,70,20,25,40); //determina el ancho de las columnas
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $incremento_fila = 15;
	while($row = $result -> fetch(PDO::FETCH_BOTH))// buscar la consulta y la ejecuta.
            {
            $pdf->Cell($w[0],10,$i,'LR',0,'C',$fill);       // núermo correlativo
            $pdf->Cell($w[1],10,utf8_decode(($row['apellido_alumno'])),'LR',0,'L',$fill);    // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[2],10,utf8_decode($print_codigo_grado),'LR',0,'C',$fill);    // Grado.
            $pdf->Cell($w[3],10,utf8_decode(strtoupper(trim($row['genero']))),'LR',0,'C',$fill);    // Género
            $pdf->Cell($w[4],10,utf8_decode(trim($row['nombres'])),'LR',0,'L',$fill);    // nombre del encargado
            $pdf->Cell($w[5],10,trim($row['dui']),'LR',0,'C',$fill);    // número de dui
            $pdf->Cell($w[6],10,' ','LR',0,'C',$fill);  // fecha de entrega.
            $pdf->Cell($w[7],10,' ','LR',0,'C',$fill);  // firma de quien recibe.
            
            // Salto de Línea.
            $pdf->ln();
		if($i == $incremento_fila){
		   $pdf->Cell(array_sum($w),0,'','T');  $pdf->AddPage();
                   // Incrementar el valor de la fila.
                    $incremento_fila = $incremento_fila + 15;
                    // Fijamos la posición de X y Y.
                        $pdf->SetY(27);
                        $pdf->SetX(10);
                        $pdf->FancyTable($header);}
            // cambiamos el fondo de la lineas e incrementamos $i=fila.
            $fill=!$fill;
            $i=$i+1;
            }
                $pdf->Cell(array_sum($w),0,'','T');
                  // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetX(10);
                      $pdf->Cell($w[0],10,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],10,'','LR',0,'l',$fill);  // nombre del alumno.
                        $pdf->Cell($w[2],10,'','LR',0,'C',$fill);    // Grado.
                        $pdf->Cell($w[3],10,'','LR',0,'C',$fill);    // Género
                        $pdf->Cell($w[4],10,'','LR',0,'L',$fill);    // nombre del encargado
                        $pdf->Cell($w[5],10,'','LR',0,'C',$fill);    // número de dui
                        $pdf->Cell($w[6],10,' ','LR',0,'C',$fill);  // fecha de entrega.
                        $pdf->Cell($w[7],10,' ','LR',0,'C',$fill);  // firma de quien recibe.
                      $pdf->Ln();   
                      $fill=!$fill;
                      
                      // Salto de Línea.
		if($numero == $incremento_fila){
		   $pdf->Cell(array_sum($w),0,'','T');  $pdf->AddPage();
                   // Incrementar el valor de la fila.
                    $incremento_fila = $incremento_fila + 15;
                    // Fijamos la posición de X y Y.
                        $pdf->SetY(27);
                        $pdf->SetX(10);
		   $pdf->FancyTable($header);}
                  }
		// Cerrando Línea Final.
		$pdf->Cell(array_sum($w),0,'','T');
// Salida del pdf.
    $pdf->Output();
?>