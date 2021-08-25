<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/funciones_2.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// Inicializamos variables de mensajes y JSON
    $respuestaOK = true;
    $mensajeError = "Registros Encontrados";
    $contenidoOK = "";
    // variables y consulta a la tabla.
    $crear_archivos = "no";
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
    $crear_archivos = $_REQUEST["chkCrearArchivoPdf"];
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
  //  imprimir datos del bachillerato.
          while ($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
              {
              $print_bachillerato = utf8_decode('Modalidad: '.trim($row['nombre_bachillerato']));
              $nombre_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
              $codigo_modalidad = $row['codigo_bach_o_ciclo'];
              $print_grado = utf8_decode('Grado: '.trim($row['nombre_grado']));
              $nombre_grado = utf8_decode(trim($row['nombre_grado']));
              $print_seccion = utf8_decode('Sección: '.trim($row['nombre_seccion']));
              $nombre_seccion = utf8_decode(trim($row['nombre_seccion']));
              $print_ann_lectivo = utf8_decode('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
              $nombre_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
              $print_turno = utf8_decode('Turno: '.trim($row['nombre_turno']));
              $nombre_turno = utf8_decode(trim($row['nombre_turno']));
          break;
              }
    // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
       // buscar la consulta y la ejecuta.
       consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
       $print_nombre_docente = "";
       while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
           {
               $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
               
               if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
                   $print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
               }
          
           }    
// buscar la consulta y la ejecuta.
    consultas(4,0,$codigo_all,'','','',$db_link,'');
      
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $print_nombre_docente;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',13);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(150,4,utf8_decode($_SESSION['institucion']),0,1,'C');
    $this->Cell(190,4,utf8_decode('Nómina de Alumnos/as'),0,1,'C');
    $this->SetFont('Arial','B',11);
    $this->Cell(190,4,'Docente Responsable: '.$print_nombre_docente,0,1,'C');
    $this->Line(10,22,200,22);
}

//Pie de página
function Footer()
{
  // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');						
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
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
    $w=array(10,15,70,80,25,25,25); //determina el ancho de las columnas
    $w_linea_1=array(10,15,70,80,75); //determina el ancho de las columnas

    $header_linea_1 = array('','','','','N.º de Teléfono');
    for($ij=0;$ij<count($header_linea_1);$ij++){
        $this->Cell($w_linea_1[$ij],7,utf8_decode($header_linea_1[$ij]),1,0,'C',1);
    }
    $this->Ln();    
    //  Cabecera secundaria
    for($i=0;$i<count($header);$i++){
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',1);
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
//Títulos de las columnas
    $header=array('Nº','N I E','Nombre del alumno','Padre/Madre o Encargado','Encargado','Residencia','Estudiante');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(20);
    $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    //  imprimir datos del bachillerato.
    $pdf->Cell(130,10,$print_bachillerato,0,0,'L');
    $pdf->Cell(40,10,$print_grado,0,0,'L');
    $pdf->Cell(20,10,$print_seccion,0,0,'L');
    $pdf->ln(6);
    $pdf->Cell(40,8,$print_ann_lectivo,0,0,'L');
    $pdf->Cell(20,8,$print_turno,0,0,'L');
    // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
       // buscar la consulta y la ejecuta.
       consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
       $print_nombre_docente = "";
       while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
           {
               $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
               
               if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
                   $print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
               }
          
           }        

    $pdf->ln();
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

//  mostrar los valores de la consulta
    $w=array(10,15,70,80,25,25,25); //determina el ancho de las columnas
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $repitentem = 0; $repitentef = 0; $totalrepitente = 0; $sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
            $pdf->Cell($w[0],5.8,$i,'LR',0,'C',$fill);        // núermo correlativo
            $pdf->Cell($w[1],5.8,trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
            $pdf->Cell($w[2],5.8,utf8_decode(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[3],5.8,utf8_decode(trim($row['nombres'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
            $pdf->Cell($w[4],5.8,$row['encargado_telefono'],'LR',0,'C',$fill);  // telefono encargado
            $pdf->Cell($w[5],5.8,$row['telefono_alumno'],'LR',0,'C',$fill);  // telefono casa
            $pdf->Cell($w[6],5.8,strtoupper($row['telefono_celular']),'LR',0,'C',$fill);    // telefono celular

            $pdf->Ln();
            $fill=!$fill;
            $i=$i+1;
                
        // Salto de Línea.
        	if($i == 25|| $i == 50){$pdf->Cell(array_sum($w),0,'','B');$pdf->AddPage();$pdf->Ln(4);$pdf->FancyTable($header);}
        } //cierre del do while.
          // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          /*	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                    $pdf->SetX(10);
                        $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
                      $pdf->Cell($w[0],5.8,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],5.8,'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],5.8,'','LR',0,'l',$fill);  // nombre del encargado
                      $pdf->Cell($w[3],5.8,'','LR',0,'C',$fill);  // NIE
                      //$pdf->Cell($w[3],5.8,'','LR',0,'C',$fill);  // telefono alumno
                      $pdf->Cell($w[4],5.8,'','LR',0,'C',$fill);  // telefono encargado
                      $pdf->Ln();   
                      $fill=!$fill;                      
                      // Salto de Línea.
        		      //  if($numero == 39 || $numero == 82){$pdf->Cell(array_sum($w),0,'','B');$pdf->AddPage();$pdf->Ln(4);$pdf->FancyTable($header);}
                  }
*/
		// Cerrando Línea Final.
		$pdf->Cell(array_sum($w),0,'','T');
        $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;

        if($crear_archivos == "si"){
            // Verificar si Existe el directorio archivos.
                //$codigo_modalidad = $nombre_bachillerato;
            // Tipo de Carpeta a Grabar.
                $codigo_destino = 1;
                $nuevo_grado = replace_3(trim($print_grado));
                CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
            // verificar si existe el grado y sección.
            if(!file_exists($DestinoArchivo))
            {
                // Para Nóminas. Escolanadamente.
                    mkdir ($DestinoArchivo);
                    chmod ($DestinoArchivo,07777);
            }
                $NuevoDestinoArchivo = $DestinoArchivo . "/";
            
                $modo = 'F'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
                $nombre_archivo = $print_nombre_docente . ' ' . $nombre_grado . ' ' . $nombre_seccion . utf8_decode("- N.º TELEFONO.pdf");
                $print_nombre = $NuevoDestinoArchivo . trim($nombre_archivo);
                
                //$print_nombre = $path_root . '/registro_academico/temp/' . trim($nombre_completo_alumno) . ' ' . trim($print_grado) . ' ' . trim($print_seccion) . '.pdf';
                $pdf->Output($print_nombre,$modo);
        }	
        // 
        if($crear_archivos == "no"){
            // Construir el nombre del archivo.
                $nombre_archivo = $print_nombre_docente . ' ' . $nombre_grado . ' ' . $nombre_seccion . "- N.º TELEFONO.pdf";
            // Salida del pdf.
                $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
                $pdf->Output($nombre_archivo,$modo);
            }else{
                // Armamos array para convertir a JSON
                $salidaJson = array("respuesta" => $respuestaOK,
                    "mensaje" => $mensajeError,
                    "contenido" => $contenidoOK
                );	
            // enviar el Json
                echo json_encode($salidaJson);
            }
?>