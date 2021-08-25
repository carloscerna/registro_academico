<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");
// variables y consulta a la tabla.
      $id_libro = $_REQUEST["Id_libro"];
      $db_link = $dblink;
// 	Armar el Query.
	$query = "SELECT * from libro_promocion_imagen WHERE id_libro_imagen = ". $id_libro;
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	$consulta_libro = $dblink -> query($query);
	$num_registros = $consulta_libro -> rowCount();
						
		if($num_registros !=0){
			$respuestaOK = true;
			$mensajeError = "Si Existe";
										
				while($listadoLibro = $consulta_libro -> fetch(PDO::FETCH_BOTH))
					{
						// recopilar los valores de los campos.
							$id_libro_imagen = trim($listadoLibro['id_libro_imagen']);
							$imagen_frente = trim($listadoLibro['imagen_frente']);
							$imagen_vuelto = trim($listadoLibro['imagen_vuelto']);
					}
			// salida del while.										
		}
class PDF extends FPDF
{
	
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Letter');
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
		if($imagen_frente === ""){$imagen_frente = "no.jpg";}
		if($imagen_vuelto === ""){$imagen_vuelto = "no.jpg";}
		
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png/libro/'.$imagen_frente)){
			$img_f = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png/libro/'.$imagen_frente;
			$pdf->Image($img_f,1,1,280,210);
			$pdf->AddPage();
		}else{
			$img_f = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/no.jpg';
			$pdf->Image($img_f,1,1,280,210);
			$pdf->AddPage();
		}
	// Imagen Vuelto.
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png/libro/'.$imagen_vuelto)){
			$img_v = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png/libro/'.$imagen_vuelto;
			$pdf->Image($img_v,1,1,280,210);
		}else{
			$img_v = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/no.jpg';
			$pdf->Image($img_v,1,1,280,210);
		}	
// Salida del pdf.
    $pdf->Output();
?>