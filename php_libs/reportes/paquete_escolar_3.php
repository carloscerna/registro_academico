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
    $fechapaquete = cambiaf_a_normal($_REQUEST["fechapaquete"]);
    $chkfechaPaquete = ($_REQUEST["chkfechaPaquete"]);
    $chkNIEPaquete = ($_REQUEST["chkNIEPaquete"]);
    $rubro = utf8_decode(trim($_REQUEST["rubro"]));
    $db_link = $dblink;
    $por_genero = true;
  
    consultas(16,0,$codigo_all,'','','',$db_link,'');

//  imprimir datos del bachillerato.
    while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))// buscar la consulta y la ejecuta.
        {
          $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
          
          $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
          $print_codigo_grado = trim($row['codigo_grado']);
          $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
          $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
          
          $nombre_ann_lectivo = trim($row['nombre_ann_lectivo']);
          $nombre_grado = trim($row['nombre_grado']);
          $nombre_seccion = trim($row['nombre_seccion']);
          $nombre_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
          $porciones = explode(" ", $nombre_bachillerato);
          
          $codigo_grado = trim($row['codigo_grado']);
            break;
        }

class PDF extends FPDF
{
//Cabecera de página
function Header()
{
  global $nombre_ann_lectivo, $nombre_grado, $nombre_seccion, $fechapaquete, $rubro, $chkfechaPaquete, $porciones, $codigo_grado;
    //Logo
    //$img = $_SERVER['DOCUMENT_ROOT'].'/registro_web/img/'.$_SESSION['logo_uno'];
    //$this->Image($img,10,5,12,15);
    //Arial bold 11
    $this->SetFont('Arial','B',11);
    //Título
    if($rubro == "Sin Texto"){
      $this->RotatedText(75,15,utf8_decode('NÓMINA DE ESTUDIANTES (PADRE DE FAMILIA O ENCARGADO) - AÑO LECTIVO - '.$nombre_ann_lectivo),0);
    }else{
      $this->RotatedText(75,15,utf8_decode('PROGRAMA DE DOTACIÓN DE PAQUETES ESCOLARES AÑO '.$nombre_ann_lectivo),0);
    }
    
    $this->SetFont('Arial','',10);
    // primera columna de datos, RUBRO, FECHA, CODIGO DEL C.E., NOMBRE DEL C.E.
    $this->RotatedText(20,20,'RUBRO: ',0);
    $this->RotatedText(20,25,'FECHA: ',0);
    $this->RotatedText(20,30,utf8_decode('CÓDIGO DEL C.E.: '),0);
    $this->RotatedText(20,35,utf8_decode('NOMBRE DEL C.E.: '),0);

    $this->SetFont('Arial','B',10);
    if($rubro == "Sin Texto"){
      $this->RotatedText(40,20,"_____________________________",0);
    }else{
      $this->RotatedText(40,20,$rubro,0);
    }
    
    if($chkfechaPaquete == "yes"){
      $this->RotatedText(40,25,$fechapaquete,0);  
    }else{
      $this->RotatedText(40,25,"_____________________________",0);  
    }
    
    $this->RotatedText(55,30,utf8_decode($_SESSION['codigo']),0);
    $this->RotatedText(55,35,utf8_decode($_SESSION['institucion']),0);
    $this->SetFont('Arial','',10);
    // segunda columna de datos, DEPARTAMENTO, MUNICIPIO, GRADO, SECCION.
    $this->RotatedText(200,20,'DEPARTAMENTO: ',0);
    $this->RotatedText(200,25,'MUNICIPIO: ',0);
    $this->RotatedText(200,30,'GRADO: ',0);       
    $this->RotatedText(200,35,utf8_decode('SECCIÓN: '),0);
    
    $this->SetFont('Arial','B',10);
    $this->RotatedText(232,20,$_SESSION['nombre_departamento'],0);
    $this->RotatedText(227,25,$_SESSION['nombre_municipio'],0);
    // VERIFDICAR EL NOMBRE DEL GRADO PARA LA HOJA.
    if($codigo_grado == '4P' or $codigo_grado == '5P' or $codigo_grado == '6P'){
      $this->RotatedText(220,30,utf8_decode($nombre_grado),0);  
    }else if($codigo_grado >= '01' and $codigo_grado <='09'){
      $this->RotatedText(220,30,utf8_decode($nombre_grado),0);  
    }else{
      $this->RotatedText(220,30,utf8_decode($nombre_grado) . " - " . $porciones[1],0);     
      }
    // Nombre de la sección.
    $this->RotatedText(220,35,$nombre_seccion,0);
    $this->SetFont('Arial','',10);
    // Texto de Ley.
    // Fijamos la posición de X y Y.
    $this->SetY(37);
    $this->SetX(10);
    $texto_1 = utf8_decode("El padre, madre, estudiante o responsable del estudiane quién suscribe, garantiza que: a) los bienes serán exclusivamente para uso del estudiante al que está destinado, b) serán utilizados para asistir y permanecer en el Centro Educativo durante el año lectivo (Art. 56 de la Constitución de la República y Art. 87 de la LEPINA):");
    $this->MultiCell1(253,4,$texto_1,0,'J',0,3);

    //$this->RotatedText(10,35,utf8_decode($texto_1),0);
}

//Pie de página
function Footer()
{
 /* // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');

    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Número de página
    $this->Line(10,205,50,205);
    $this->Cell(20,10,'Firma y Sello del Proveedor',0,0,'L');
    $this->Cell(0,10,utf8_decode('Página ').$this->PageNo().'/{nb}       ',0,0,'R');
*/
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,255,255);
    $this->SetTextColor(0);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('Arial','B',8);
    //Cabecera
    $w=array(5,75,12,15,80,25,45); //determina el ancho de las columnas
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',1);
    $this->Ln();
    //Restauración de colores y fuentes
    //$this->SetFillColor(224,235,255);
    $this->SetFillColor(255,255,255);
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
    $pdf->SetMargins(10, 20, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,10);
    $data = array();
//Títulos de las columnas
    //if(utf8_encode($rubro) === "Confección de Primer Uniforme"){
      //$header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','TALLA','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');
   // }

    if(utf8_encode($rubro) === "Paquete de Útiles Escolares" || utf8_encode($rubro) === "" || utf8_encode($rubro) === "Familias" || utf8_encode($rubro) === "Libro de ESMATE" || utf8_encode($rubro) === "Libro de Lenguaje" || utf8_encode($rubro) === "Sin Texto"){
       $header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','CICLO','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');  
    }else{
      $header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','TALLA','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');
    }
    
    $pdf->AliasNbPages();
    $pdf->AddPage();
// Fijamos la posición de X y Y.
    $pdf->SetY(50);
    $pdf->SetX(10);
// buscar la consulta y la ejecuta.
        consultas(16,0,$codigo_all,'','','',$db_link,'');
// Cargar el encabezao de la tabla.
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
//  mostrar los valores de la consulta
    $w=array(5,75,6,6,15,80,25,45); //determina el ancho de las columnas
    // 0 - NUMERO.
    // 1 - NOMBRE DLE ESTUDIANTE.
    // 2 - GENERO
    $alto = array(9); // alto de cada fila.
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $incremento_fila = 15;
    while($row = $result -> fetch(PDO::FETCH_BOTH))// buscar la consulta y la ejecuta.
        {
            $pdf->Cell($w[0],$alto[0],$i,1,0,'C',$fill);       // núermo correlativo
            $pdf->Cell($w[1],$alto[0],utf8_decode(($row['apellido_alumno'])),1,0,'L',$fill);    // Nombre + apellido_materno + apellido_paterno
            // SEPARA LA COLUMAN DEL GENERO
            if($row['genero'] == "m"){
              $pdf->Cell($w[2],$alto[0],utf8_decode(strtoupper(trim($row['genero']))),1,0,'C',$fill);    // Masculino
              $pdf->Cell($w[2],$alto[0],'',1,0,'C',$fill);    // Femenino
            }else{
              $pdf->Cell($w[2],$alto[0],'',1,0,'C',$fill);    // Femenino
              $pdf->Cell($w[2],$alto[0],utf8_decode(strtoupper(trim($row['genero']))),1,0,'C',$fill);    // Masculino              
            }
            // TALLA
            $pdf->Cell($w[4],$alto[0],' ',1,0,'C',$fill);  // fecha de entrega.
            
            $pdf->Cell($w[5],$alto[0],utf8_decode(trim($row['nombres'])),1,0,'L',$fill);    // nombre del encargado
            
            if($chkNIEPaquete == "yes"){          
              $pdf->Cell($w[6],$alto[0],trim($row['codigo_nie']),1,0,'C',$fill);    // código nie
            }else{
              $pdf->Cell($w[6],$alto[0],trim($row['dui']),1,0,'C',$fill);    // número de dui
            }
            
            $pdf->Cell($w[7],$alto[0],'',1,0,'C',$fill);    // número de dui
            
            // Salto de Línea.
            $pdf->ln();
                if($i >= $incremento_fila){
                 $pdf->Cell(array_sum($w),0,'','T');  $pdf->AddPage();
                   // Incrementar el valor de la fila.
                    $incremento_fila = $incremento_fila + 15;
                    // Fijamos la posición de X y Y.
                        $pdf->SetY(50);
                        $pdf->SetX(10);
                        $pdf->FancyTable($header);}  
            
            // cambiamos el fondo de la lineas e incrementamos $i=fila.
              $fill=!$fill;
              $i=$i+1;
        }
///////////////////////////////////////////////////////////////////////////////////////
            // rellenar con las lineas que faltan y colocar total de puntos y promedio.
            //////////////////////////////////////////////////////////////////////////////////////
          	$numero = $i;
            if($i <= 15){$linea_faltante =  15 - $numero;}
            if($i > 15){$linea_faltante =  30 - $numero;}
            if($i > 30){$linea_faltante =  45 - $numero;}
            if($i > 45){$linea_faltante =  60 - $numero;}
                
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                      $pdf->Cell($w[0],$alto[0],$numero++,1,0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],$alto[0],'',1,0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],$alto[0],'',1,0,'C',$fill);    // Femenino
                      $pdf->Cell($w[2],$alto[0],'',1,0,'C',$fill);    // Masculino              
                      // TALLA o CICLO
                      $pdf->Cell($w[4],$alto[0],' ',1,0,'C',$fill);  // fecha de entrega.
                      
                      $pdf->Cell($w[5],$alto[0],'',1,0,'L',$fill);    // nombre del encargado
                      $pdf->Cell($w[6],$alto[0],'',1,0,'C',$fill);    // número de dui
                      $pdf->Cell($w[7],$alto[0],'',1,0,'C',$fill);    // número de dui											
                      $pdf->Ln();   
                      $fill=!$fill;
                  }
                $pdf->Cell(array_sum($w),0,'','T');     
// Salida del pdf.
    $pdf->Output();
?>