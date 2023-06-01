<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");
// COLOCAR UN LIMITE A LA MEMORIA PARA LA CREACIÓN DE LA HOJA DE CÁLCULO.
set_time_limit(0);
ini_set("memory_limit","1024M");
    // Variable
   // $nombre_archivo = $_REQUEST['nombre_archivo'];
    $codigo_alumno = $_REQUEST['codigo_alumno'];    
    $codigo_institucion = $_SESSION["codigo_institucion"];
    // Obtenemos el id de user para edici�n
    $query_nombre_archivo = "SELECT id_alumno, ruta_pn from alumno WHERE id_alumno = '$codigo_alumno'";
    // Ejecutamos el Query.
    $consulta = $dblink -> query($query_nombre_archivo);

    while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
    {
        // obtenemos el �ltimo c�digo asignado.
        $nombre_archivo = $listado['ruta_pn'];
    }    
    
class PDF extends FPDF
{
	
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
    $pdf->SetY(0);
    $pdf->SetX(0);

	// Imagen del Frente.
		// Validar si existe.
		if($nombre_archivo === ""){$print_pn = "no.jpg";}
        
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/Pn/'.$codigo_institucion.'/'.$nombre_archivo)){
			$img_v = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/Pn/'.$codigo_institucion.'/'.$nombre_archivo;
			$pdf->Image($img_v,1,1,210,280);
		}else{
			$img_v = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/no.jpg';
			$pdf->Image($img_v,1,1,210,280);
		}	
// Salida del pdf.
    $pdf->Output();
?>