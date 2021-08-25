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
      $fecha = $_REQUEST["fecha"];
	  $codigo_empleado = $_REQUEST['lstcodigoempleado'];
	  $tipo_planilla = $_REQUEST['lsttipo'];
// armando el Query. PARA LA TABLA HISTORIAL.
	$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
						cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_d.porcentaje as porcentaje, cat_r.codigo, cat_r.descripcion as nombre_rubro,
						p.nombres, p.apellidos, cat_cargo.descripcion as nombre_cargo, p.dui, p.nit, p.tiempo_servicio
						  FROM personal_salario ps
							  INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
							  INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
							  INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
							  INNER JOIN personal p ON p.id_personal = ps.codigo_personal
							  INNER JOIN catalogo_cargo cat_cargo ON cat_cargo.codigo = p.codigo_cargo
							  WHERE ps.codigo_personal = '".
							  $codigo_empleado."' ORDER BY p.nombres";
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	$consulta_personal = $dblink -> query($query_personal);
// verificar si hay registros.
	$num_registros = $consulta_personal -> rowCount();
	
// condificionar si hay registros.
	if($num_registros !== 0){
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
		//$dia = strftime("%d");		// El Día.
		$dato = explode("-",$fecha);
		$dato_entero = (int)$dato[1];
		$dia = (int)$dato[2];
        $mes = $meses[$dato_entero-1];     // El Mes.
		//$mes = $meses[date('n')-1];     // El Mes.
		//$año = strftime("%Y");		// El Año.
        $año = (int)$dato[0];

class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
		global $nombre_rubro, $mes, $pagina;
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
    $pdf->SetFillColor(224,235,255);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
//  mostrar los valores de la consulta
	$encabezado = array("SOLICITUD DE COTIZACIÓN DE BIENES Y SERVICIOS","ORDEN DE COMPRA DE BIENES Y SERVICIOS","ACTA DE RECEPCIÓN DE BIENES Y SERVICIOS");
    $w=array(10,70,20,60,40,100,5); //determina el ancho de las columnas
    $w2=array(6,12); //determina el ancho de las columnas
// Variables. y arrays.
    $fill = true; $i=1; $descuento_porcentaje = 0; $num_registros = 0; $pagina = 0; $label_tipo_planilla = ""; $tipo_salario = 0;
//	colocar la Y, en la ubicación
	$pdf->SetXY(15,35);
        while($Planilla = $consulta_personal -> fetch(PDO::FETCH_BOTH))
            {
			// Declaramos variables.
				$nombre_completo = (trim($Planilla['nombres'])) . ' ' . (trim($Planilla['apellidos']));
				$nombre_cargo = utf8_decode(trim($Planilla['nombre_cargo']));
				$tiempo_servicio = trim($Planilla['tiempo_servicio']);
				$nombre_contratacion = utf8_decode(trim($Planilla['nombre_contratacion']));
				$dui = (trim($Planilla['dui']));
				$nit = (trim($Planilla['nit']));
				$salario = $Planilla['salario'];
				$descuento_porcentaje = ($Planilla['porcentaje'] / 100);
				$renta = $salario * $descuento_porcentaje;
				$liquido = number_format($salario - $renta,2);
				$decimales_liquido = explode(".",$liquido);
				$decimales = explode(".",number_format($salario,2));
				// Calcular los días a partir del tiempo de servicio.
				if($tipo_planilla == "03"){
					if($tiempo_servicio >= 1 and $tiempo_servicio <= 3){$dias = 15;}
					if($tiempo_servicio >3 and $tiempo_servicio <= 10){$dias = 19;}
					if($tiempo_servicio > 10){$dias = 21;}
					// Calcular aguinaldo.
						$aguinaldo = round(($salario / 30) * $dias,2);
				}
				// Condición para el tótulo tipo planilla.
				if($tipo_planilla == "01"){$label_tipo_planilla = $nombre_cargo . ' ' . $nombre_contratacion;$tipo_salario = number_format($salario,2);
				$decimales = explode(".",$tipo_salario);}
				if($tipo_planilla == "02"){$label_tipo_planilla = utf8_decode("INDEMNIZACIÓN"); $tipo_salario = $salario; $decimales = explode(".",number_format($tipo_salario,2));}
				if($tipo_planilla == "03"){$label_tipo_planilla = "AGUINALDO"; $tipo_salario = $aguinaldo; $decimales = explode(".",number_format($tipo_salario,2));}
				
				
				
			//	Escribir encabezado
				$pdf->SetX(60);
					$pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
					$pdf->Cell($w[5],$w2[1],utf8_decode($encabezado[0]),'',1,'C');
			// Primer bloque.
				$pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
				$pdf->Ln();
				$pdf->SetX(130);
				$pdf->Cell($w[1],$w2[1],cambiar_de_del($_SESSION['nombre_departamento']) . ", ". $dia . " de " . $mes . " de " . $año ,'',0,'R');
				$pdf->Ln();				
				$pdf->Cell($w[1],$w2[1],utf8_decode("Señor(a): ".$_SESSION['institucion']),'',1,'L');
				$pdf->MultiCell(180,10,utf8_decode("Atentamente le(s) solicito COTIZAR al  ".$_SESSION['institucion']." los suministros que se detallan a continuación: Plazo de Entrega inmediata."));
			// Segundo bloque Información de pago nombre y firma.
				$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
				$pdf->Cell($w[2],$w2[1],'Cantidad',1,0,'C',$fill);	// Cantidad
				$pdf->MultiCell($w[2],$w2[0],'Unidad de Medida',1,'T','C',$fill);	// Unidad de Medida
				$pdf->SetXY(60,103);
				$pdf->Cell($w[5],$w2[1],utf8_decode('Descripción'),1,0,'C',$fill);	// Descripcion
				$pdf->MultiCell($w[2],$w2[0],'Precio Unitario',1,'T','C',$fill);	// Precio Unitario
				$pdf->SetXY(180,103);
				$pdf->MultiCell($w[2],$w2[0],'Precio Total',1,'T','C',$fill);	// Precio Total
			////
				$fill = false;
				$pdf->Cell($w[2],$w2[1],'1',1,0,'C',$fill);	// Cantidad
				$pdf->Cell($w[2],$w2[1],'',1,0,'C',$fill);	// Unidad de Medida
				$pdf->Cell($w[5],$w2[1],$label_tipo_planilla,1,0,'L',$fill);	// Descripcion
				$pdf->Cell($w[2],$w2[1],'',1,0,'C',$fill);	// Precio Unitario
				$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);	// Precio Total
			//// TOTAL EN LETRAS
				$pdf->Cell($w[2]+$w[2],$w2[1],'Total en letras',1,0,'C',$fill);	// total en letras
				$pdf->Cell($w[5],$w2[1],utf8_decode((num2letras($tipo_salario)) . ' '.$decimales[1]. '/100 de dólares de US.'),1,0,'L',$fill);	// Descripcion
				$pdf->Cell($w[2],$w2[1],'Total',1,0,'C',$fill);	// Precio Unitario
				$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);	// Precio Total
				$pdf->ln(); // Aplicar salto de línea para el siguiente registro.
				
				// Cambiar el color de fondo y incrementar el valor de $i.	
              	$fill=!$fill;	
              	$i++;
            }
            // despues del bucle.
				// Cuarto Bloque datos personales. nombre, dui y nit.
			    $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
					$pdf->Cell($w[1],$w2[0],'Suministrante:',0,1,'L');
					$pdf->Ln();
					$pdf->Cell($w[3],$w2[0],cambiar_de_del($nombre_completo),'B',0,'L');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],($nombre_cargo),'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'','B',1,'L');
			// cuarto bloque nombre, cargo y firma.
				$pdf->Cell($w[3],$w2[0],'Nombre',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Cargo',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Firma',0,1,'C');
				$pdf->Ln();

				// Datos del Director.
				$YY = $pdf->GetY();
				$pdf->SetY($YY+20);
				$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
					$pdf->Cell($w[3],$w2[0],$_SESSION['nombre_director'],'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'Presidente del C.D.E.','B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'','B',1,'L');
			// cuarto bloque nombre, cargo y firma.
				$pdf->Cell($w[3],$w2[0],'Nombre',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Cargo',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Firma',0,0,'C');
				
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// SEGUNDA PAGINA.
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	colocar la Y, en la ubicación
	$pdf->AddPage();
	$pdf->SetXY(15,35);
	$fill = true;
			//	Escribir encabezado
				$pdf->SetX(60);
					$pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
					$pdf->Cell($w[5],$w2[1],utf8_decode($encabezado[1]),'',1,'C');
			// Primer bloque.
				$pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
				$pdf->Ln();
				$pdf->SetX(130);
				$pdf->Cell($w[1],$w2[1],cambiar_de_del($_SESSION['nombre_departamento']) . ", ". $dia . " de " . $mes . " de " . $año ,'',0,'R');
				$pdf->Ln();				
				$pdf->Cell($w[1],$w2[1],utf8_decode("Señor(a): ".$_SESSION['institucion']),'',1,'L');
				$pdf->MultiCell(180,10,utf8_decode("Atentamente le(s) solicito ENTREGAR al  ".$_SESSION['institucion']." los suministros que se detallan a continuación: Plazo de Entrega inmediata."));
			// Segundo bloque Información de pago nombre y firma.
				$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
				$pdf->Cell($w[2],$w2[1],'Cantidad',1,0,'C',$fill);	// Cantidad
				$pdf->MultiCell($w[2],$w2[0],'Unidad de Medida',1,'T','C',$fill);	// Unidad de Medida
				$pdf->SetXY(60,103);
				$pdf->Cell($w[5],$w2[1],utf8_decode('Descripción'),1,0,'C',$fill);	// Descripcion
				$pdf->MultiCell($w[2],$w2[0],'Precio Unitario',1,'T','C',$fill);	// Precio Unitario
				$pdf->SetXY(180,103);
				$pdf->MultiCell($w[2],$w2[0],'Precio Total',1,'T','C',$fill);	// Precio Total
			////
				$fill = false;
				$pdf->Cell($w[2],$w2[1],'1',1,0,'C',$fill);	// Cantidad
				$pdf->Cell($w[2],$w2[1],'',1,0,'C',$fill);	// Unidad de Medida
				$pdf->Cell($w[5],$w2[1],$label_tipo_planilla,1,0,'L',$fill);	// Descripcion
				$pdf->Cell($w[2],$w2[1],'',1,0,'C',$fill);	// Precio Unitario
				$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);	// Precio Total
			//// TOTAL EN LETRAS
				$pdf->Cell($w[2]+$w[2],$w2[1],'Total en letras',1,0,'C',$fill);	// total en letras
				$pdf->Cell($w[5],$w2[1],utf8_decode((num2letras($tipo_salario)) . ' '.$decimales[1]. '/100 de dólares de US.'),1,0,'L',$fill);	// Descripcion
				$pdf->Cell($w[2],$w2[1],'Total',1,0,'C',$fill);	// Precio Unitario
				$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);	// Precio Total
            // despues del bucle.
				// Cuarto Bloque datos personales. nombre, dui y nit.
			    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
					$pdf->MultiCell(180,7,utf8_decode('Para efectos de cobre presentar esta orden de compra original y copia de factura de consumidor final a nombre de C.D.E. '.$_SESSION['institucion']),0,'J');
					$pdf->ln(); // Aplicar salto de línea para el siguiente registro.
					$pdf->Cell($w[1],$w2[0],'Encargado(a) de Compras:',0,1,'L');
					$pdf->Ln();
					$pdf->Cell($w[3],$w2[0],cambiar_de_del(' '),'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],('Consejal de C.D.E.'),'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'','B',1,'L');
			// cuarto bloque nombre, cargo y firma.
			$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
				$pdf->Cell($w[3],$w2[0],'Nombre',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Cargo',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Firma',0,1,'C');
				$pdf->Ln();

				// Datos del Director.
				$YY = $pdf->GetY();
				$pdf->SetY($YY+20);
				$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
					$pdf->Cell($w[3],$w2[0],cambiar_de_del($nombre_completo),'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],($nombre_cargo),'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'','B',1,'L');
				
			// cuarto bloque nombre, cargo y firma.
				$pdf->Cell($w[3],$w2[0],'Nombre',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Cargo',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Firma',0,0,'C');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// TERCERA PAGINA.
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	colocar la Y, en la ubicación
	$pdf->AddPage();
	$pdf->SetXY(15,35);
	$fill = true;
			//	Escribir encabezado
				$pdf->SetX(60);
					$pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
					$pdf->Cell($w[5],$w2[1],utf8_decode($encabezado[2]),'',1,'C');
			// Primer bloque.
				$pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
				$pdf->Ln();
				$pdf->SetX(130);
				$pdf->Cell($w[1],$w2[1],cambiar_de_del($_SESSION['nombre_departamento']) . ", ". $dia . " de " . $mes . " de " . $año ,'',0,'R');
				$pdf->Ln();				
				
				$pdf->MultiCell(180,10,utf8_decode("El Suscrito hace constar que he recibido de acuerdo a lo convenido con: Los bienes y servicios que se detallan a continuación."));
			// Segundo bloque Información de pago nombre y firma.
				$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
				$pdf->Cell($w[2],$w2[1],'Cantidad',1,0,'C',$fill);	// Cantidad
				$pdf->MultiCell($w[2],$w2[0],'Unidad de Medida',1,'T','C',$fill);	// Unidad de Medida
				$pdf->SetXY(60,91);
				$pdf->Cell($w[5],$w2[1],utf8_decode('Descripción'),1,0,'C',$fill);	// Descripcion
				$pdf->MultiCell($w[2],$w2[0],'Precio Unitario',1,'T','C',$fill);	// Precio Unitario
				$pdf->SetXY(180,91);
				$pdf->MultiCell($w[2],$w2[0],'Precio Total',1,'T','C',$fill);	// Precio Total
			////
				$fill = false;
				$pdf->Cell($w[2],$w2[1],'1',1,0,'C',$fill);	// Cantidad
				$pdf->Cell($w[2],$w2[1],'',1,0,'C',$fill);	// Unidad de Medida
				$pdf->Cell($w[5],$w2[1],$label_tipo_planilla,1,0,'L',$fill);	// Descripcion
				$pdf->Cell($w[2],$w2[1],'',1,0,'C',$fill);	// Precio Unitario
				if($tipo_planilla == "01"){$pdf->Cell($w[2],$w2[1],'$ '.$liquido,1,1,'C',$fill);}	// Precio Total
				if($tipo_planilla == "02"){$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);}	// Precio Total
				if($tipo_planilla == "03"){$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);}	// Precio Total
			//// TOTAL EN LETRAS
				$pdf->Cell($w[2]+$w[2],$w2[1],'Total en letras',1,0,'C',$fill);	// total en letras
				if($tipo_planilla == "01"){
					$pdf->Cell($w[5],$w2[1],utf8_decode((num2letras($liquido)) . ' '.$decimales_liquido[1]. '/100 de dólares de US.'),1,0,'L',$fill);	// Descripcion
					$pdf->Cell($w[2],$w2[1],'Total',1,0,'C',$fill);	// Precio Unitario
					$pdf->Cell($w[2],$w2[1],'$ '.$liquido,1,1,'C',$fill);}	// Precio Total
				if($tipo_planilla == "02"){
					$pdf->Cell($w[5],$w2[1],utf8_decode((num2letras($tipo_salario)) . ' '.$decimales[1]. '/100 de dólares de US.'),1,0,'L',$fill);	// Descripcion
					$pdf->Cell($w[2],$w2[1],'Total',1,0,'C',$fill);	// Precio Unitario
					$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);}	// Precio Total
				if($tipo_planilla == "03"){
					$pdf->Cell($w[5],$w2[1],utf8_decode((num2letras($tipo_salario)) . ' '.$decimales[1]. '/100 de dólares de US.'),1,0,'L',$fill);	// Descripcion
					$pdf->Cell($w[2],$w2[1],'Total',1,0,'C',$fill);	// Precio Unitario
					$pdf->Cell($w[2],$w2[1],'$ '.$tipo_salario,1,1,'C',$fill);}	// Precio Total
            // despues del bucle.
				// Observaciones:
				$pdf->ln(); // Aplicar salto de línea para el siguiente registro.
				$pdf->Cell($w[1],$w2[0],'Observaciones:',0,1,'L');
				$pdf->Cell(180,$w2[0],'','B',1,'L');
				$pdf->Cell(180,$w2[0],'','B',1,'L');
				$pdf->Cell(180,$w2[0],'','B',1,'L');
				$pdf->Ln();
				// Datos del Director.
				$YY = $pdf->GetY();
				$pdf->SetY($YY+20);
				$pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
					$pdf->Cell($w[3],$w2[0],$_SESSION['nombre_director'],'B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'Presidente del C.D.E.','B',0,'C');
					$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
					$pdf->Cell($w[3],$w2[0],'','B',1,'L');
			// cuarto bloque nombre, cargo y firma.
				$pdf->Cell($w[3],$w2[0],'Nombre',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Cargo',0,0,'C');
				$pdf->Cell($w[6],$w2[0],'',0,0,'L');	// pequeño espacio
				$pdf->Cell($w[3],$w2[0],'Firma',0,0,'C');
				
	
	}	// condición para mostrar los registros.
	else{
			print "No existen registros.";
	} // condición que no muestra el mensaje que no existen registros...
// Salida del pdf.
    $pdf->Output();
?>