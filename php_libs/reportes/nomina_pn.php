<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Archivos que se incluyen.
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
        while ($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
	    break;
            }
//************************************************************************************************************************
class PDF extends FPDF
{
	
}
// Creando el Informe.
$pdf=new PDF('P','mm','Letter');
#Establecemos los márgenes izquierda, arriba y derecha: 
$pdf->SetMargins(20, 20);
#Establecemos el margen inferior: 
$pdf->SetAutoPageBreak(true,5);
//Títulos de las columnas
$pdf->AliasNbPages();
// buscar la consulta y la ejecuta.
    while ($row = $result -> fetch(PDO::FETCH_BOTH))
        {       
             $nombre_archivo = trim($row['ruta_pn']);  // NIE    
			// Validar si existe.
		    if($nombre_archivo === ""){
                $print_pn = "no.jpg";
            
            }else{
                if(file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/Pn/10391/'.$nombre_archivo)){
                    $pdf->AddPage();
                    // Aqui mandamos texto a imprimir o al documento.
                    // Definimos el tipo de fuente, estilo y tamaño.
                    $pdf->SetY(0);
                    $pdf->SetX(0);
                    //
                    //print $nombre_archivo  . "<br>";            
                    $img_v = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/Pn/10391/'.$nombre_archivo;
                    $pdf->Image($img_v,1,1,210,280);
                }else{
                    $img_v = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/no.jpg';
                    //$pdf->Image($img_v,1,1,210,280);
                }	
            }

                
    } //cierre del do while.    
// Construir el nombre del archivo.
$nombre_archivo = $print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '.pdf';
// Salida del pdf.
$modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
$pdf->Output($nombre_archivo,$modo);
?>