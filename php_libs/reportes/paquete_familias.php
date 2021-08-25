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
    $codigo_ann_lectivo = substr($codigo_all,6,2);
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
    $this->RotatedText(75,15,utf8_decode('PROGRAMA DE DOTACIÓN DE PAQUETES ESCOLARES AÑO '.$nombre_ann_lectivo),0);
    $this->SetFont('Arial','',10);
    // primera columna de datos, RUBRO, FECHA, CODIGO DEL C.E., NOMBRE DEL C.E.
    $this->RotatedText(20,20,'RUBRO: ',0);
    $this->RotatedText(20,25,'FECHA: ',0);
    $this->RotatedText(20,30,utf8_decode('CÓDIGO DEL C.E.: '),0);
    $this->RotatedText(20,35,utf8_decode('NOMBRE DEL C.E.: '),0);

    $this->SetFont('Arial','B',10);
    $this->RotatedText(40,20,$rubro,0);
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
    $pdf->SetMargins(10, 20, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,10);
    $data = array();
//Títulos de las columnas
    //if(utf8_encode($rubro) === "Confección de Primer Uniforme"){
      //$header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','TALLA','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');
   // }

    if(utf8_encode($rubro) === "Paquete de Útiles Escolares"){
       $header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','CICLO','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');  
    }else if(utf8_encode($rubro) === "Familias"){
      $header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','Hermano','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');
    }else{
        $header=array('Nº','NOMBRE DEL ESTUDIANTE','Género','TALLA','Nombre del Padre/Madre/Responsable o Estudiante','No. DUI o NIE','FIRMA');
    }
    
    $pdf->AliasNbPages();
    $pdf->AddPage();
// Fijamos la posición de X y Y.
    $pdf->SetY(50);
    $pdf->SetX(10);
// buscar la consulta y la ejecuta.
    //consultas(16,0,$codigo_all,'','','',$db_link,'');
// select que busca todos los apellidos de estudiantes.
    $solo_apellidos = array(); $sin_tilde = array();
    $query_listado_completo = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
        btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as solo_apellidos,
        translate(btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') as sin_tilde,
        ae.nombres, gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo,
        bach.nombre as nombre_bachillerato,
        am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion, am.codigo_ann_lectivo,  
        am.retirado, am.id_alumno_matricula
        FROM alumno a
        INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
        INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
        INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
        INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
        INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
    WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '$codigo_all'
        ORDER BY solo_apellidos ASC, codigo_bach_o_ciclo, codigo_grado, codigo_seccion, codigo_turno";
            $result_ = $dblink -> query($query_listado_completo);
// CREAR MATRIZ PARA QUE LOS APELLIDOS NO SE REPITAN.
    while($row_r = $result_ -> fetch(PDO::FETCH_BOTH))
    {
        $sin_tilde[] = trim($row_r['sin_tilde']);
    }  
// Eliminar valores repetidos
    $solo_apellidos = array_values(array_unique($sin_tilde));
    //print_r($solo_apellidos);
    //exit;
// Cargar el encabezao de la tabla.
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
//  mostrar los valores de la consulta
    $w=array(5,75,6,6,15,80,25,45); //determina el ancho de las columnas
    // 0 - NUMERO.
    // 1 - NOMBRE DLE ESTUDIANTE.
    // 2 - GENERO
    $alto = array(9); // alto de cada fila.
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $incremento_fila = 15; $num = 0;
    // Extraer valore de la consulta.
    for($hh=0;$hh<count($solo_apellidos);$hh++)
    {
        $solo_apellidos_busqueda = $solo_apellidos[$hh];
        $cantidad_hermanos = 0;
         // armar query para verificar si tiene hermanos.
         $query_hermanos = "SELECT a.codigo_nie, a.edad, a.genero, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
            btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo, 
            btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as solo_apellidos, 
            am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
            gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, am.id_alumno_matricula, sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno,
            ae.nombres as nombre_encargado, ae.dui
                FROM alumno a 
                    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't' 
                    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' 
                    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo 
                    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado 
                    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion 
                    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo 
            WHERE am.codigo_ann_lectivo = '$codigo_ann_lectivo' and
                    translate(btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno),'áéíóúÁÉÍÓÚ','aeiouAEIOU') = translate('$solo_apellidos_busqueda','áéíóúÁÉÍÓÚ','aeiouAEIOU') 
                        ORDER BY solo_apellidos ASC, am.codigo_bach_o_ciclo, am.codigo_grado, am.codigo_seccion";
     // ejecutar query
         $result_hermanos = $dblink -> query($query_hermanos);
     // 
     if($result_hermanos -> rowCount() != 0){
         $hermanos = true;
         $total_hermanos = $result_hermanos -> rowCount();
             while($row = $result_hermanos -> fetch(PDO::FETCH_BOTH))
             {
                 // cambiar el color de relleno cuanto el total de hermanos sea mayor a 1.
                 if($total_hermanos > 1){
                    $pdf->SetFillColor(212,212,212);
                    $fill = true;
                 }
                 // datos apra el listado.
                 $cantidad_hermanos++;
                 // Verficar si hay mas de un hermano.
                 if($cantidad_hermanos == 1){
                    
                    $num++; 
                    $pdf->Cell($w[0],$alto[0],$num,1,0,'C',$fill);       // núermo correlativo
                 }else{
                    $pdf->Cell($w[0],$alto[0],'',1,0,'C',$fill);       // núermo correlativo
                 }
                    
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
                    $pdf->Cell($w[4],$alto[0],trim($row['codigo_grado'])."-".trim($row['nombre_seccion']),1,0,'C',$fill);  // fecha de entrega.
                    
                    $pdf->Cell($w[5],$alto[0],utf8_decode(trim($row['nombre_encargado'])),1,0,'L',$fill);    // nombre del encargado
                    
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


                        if($total_hermanos > 1){
                            $fill=true;
                        }else{
                            // cambiamos el fondo de la lineas e incrementamos $i=fila.
                            $fill=!$fill;
                        }
                    $i=$i+1;
            }   // WHILE RESULT HERMANOS
            // reestablcer color de fondo.
                $pdf->SetFillColor(255,255,255);
        }   // IF RESULT HERMANOS.
    }   // FOR QUE PROVIENE DE LA MATRIZ
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
                      $pdf->Cell($w[0],$alto[0],'',1,0,'C',$fill);  // N| de Orden.
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