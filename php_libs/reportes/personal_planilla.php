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
      $codigo_rubro = $_REQUEST['lstrubro'];
	  $tipo_planilla = $_REQUEST['lsttipo'];
	  $nombre_mes = $_REQUEST['lstmes'];
// armando el Query. PARA LA TABLA HISTORIAL.
	$query_personal = "SELECT ps.id_personal_salario, ps.codigo_personal, ps.codigo_rubro, ps.codigo_tipo_contratacion, ps.codigo_tipo_descuento, ps.salario,
						cat_c.codigo, cat_c.nombre as nombre_contratacion, cat_d.codigo, cat_d.descripcion as nombre_descuento, cat_d.porcentaje as porcentaje, cat_r.codigo, cat_r.descripcion as nombre_rubro,
						p.nombres, p.apellidos, p.tiempo_servicio
						  FROM personal_salario ps
							  INNER JOIN tipo_contratacion cat_c ON cat_c.codigo = ps.codigo_tipo_contratacion
							  INNER JOIN catalogo_tipo_descuento cat_d ON cat_d.codigo = ps.codigo_tipo_descuento
							  INNER JOIN catalogo_rubro cat_r ON cat_r.codigo = ps.codigo_rubro
							  INNER JOIN personal p ON p.id_personal = ps.codigo_personal and p.codigo_estatus = '01'
							  WHERE ps.codigo_rubro = '".
							  $codigo_rubro."' and ps.codigo_tipo_contratacion >= '04' ORDER BY p.nombres";
// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
	$consulta_encabezado = $dblink -> query($query_personal);
	$consulta_personal = $dblink -> query($query_personal);
// verificar si hay registros.
	$num_registros = $consulta_encabezado -> rowCount();
	
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
		$dia = strftime("%d");		// El Día.
		$dato = explode("-",$fecha);
		$dato_entero = (int)$dato[1];
        $mes = $meses[$dato_entero-1];     // El Mes.
		$nombre_mes = $meses[$nombre_mes-1];     // El Mes.
		//$mes = $meses[date('n')-1];     // El Mes.
		$año = strftime("%Y");		// El Año.
//	definir el encabezado.
        while($Encabezado = $consulta_encabezado -> fetch(PDO::FETCH_BOTH))
            {
			// Declaramos variables para completar el título
				$nombre_rubro = 'Planilla de Pagos de '. utf8_decode(trim($Encabezado['nombre_rubro']));
			}

class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
		global $nombre_rubro, $mes, $tipo_planilla, $nombre_mes;
		$label_tipo_planilla = "";
		// Condición para el tótulo tipo planilla.
		if($tipo_planilla == "01"){$label_tipo_planilla = "MES: ". strtoupper($nombre_mes);}
		if($tipo_planilla == "02"){$label_tipo_planilla = "INDEMNIZACIÓN";}
		if($tipo_planilla == "03"){$label_tipo_planilla = "AGUINALDO";}
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

		// Valores en Pantalla.
			$this->SetFont('Arial','B',14); // I : Italica; U: Normal;
			$this->SetFillColor(224,235,255);
			$this->RoundedRect(140, 32, 65, 8, 2, '1234', 'DF');
			$this->RotatedText(55,45,$nombre_rubro,0);
			$this->RotatedText(143,38,utf8_decode($label_tipo_planilla),0);
		// Generar el cuadro que contiene la información de la cuadricula, nº, nombre, actividades  y %.
			$this->RoundedRect(10, 50, 200, 10, .5, '');    // Principal
			$this->RoundedRect(10, 50, 10, 10, .5, '');    // Nº
			$this->RoundedRect(20, 50, 70, 10, .5, '');    // Nombres
			$this->RotatedText(11, 55, utf8_decode('Nº'), 0);    // Nº
			$this->RotatedText(35, 55, utf8_decode('Nombres'), 0);    // Nombres
		// Línea Horizontal. Sueldo
			$this->RoundedRect(90, 50, 20, 10, .5, '');
			$this->RotatedText(93, 55, utf8_decode('Sueldo'), 0);    // Sueldo
		// Línea Horizontal. Renta.
			$this->RoundedRect(110, 50, 20, 10, .5, '');    // Renta.
			$this->RotatedText(114, 55, utf8_decode('Renta'), 0);    // Renta
		// Línea Horizontal. Liquido.
			$this->RoundedRect(130, 50, 20, 10, .5, '');    // Liquido
			$this->RotatedText(132, 55, utf8_decode('Liquido'), 0);    // Liquido
		// Línea Horizontal. Firma
			$this->RoundedRect(150, 50, 60, 10, .5, '');    
			$this->RotatedText(170, 55, utf8_decode('FIRMA'), 0);    
    }

//Pie de página
function Footer()
{
    global $firma, $sello;
    //Posición: a 1,5 cm del final
    $this->SetY(-20);
    //Arial italic 8
    $this->SetFont('Arial','I',12);
    //Crear una línea de la primera firma.
    $this->Line(15,180,90,180); // Tesorero
	$this->Line(115,180,200,180); // Consejal
    $this->Line(65,225,150,225); // Presidente CDE.
	
    //Nombre Director (Presidente)
    $this->RotatedText(70,230,cambiar_de_del($_SESSION['nombre_director']),0,1,'C');
    $this->RotatedText(90,235,'Presidente',0,1,'C');

    //Nombre Tesorero)
    $this->RotatedText(25,185,cambiar_de_del('ANA JULIA ARGUETA DE RIVAS'),0,1,'C');
    $this->RotatedText(45,190,'Tesorero',0,1,'C');

    //Nombre Consejal
    $this->RotatedText(115+22,185,cambiar_de_del('JORGE ARMANDO ANAYA'),0,1,'C');
    $this->RotatedText(103+33,190,'Consejal de Maestros',0,1,'C');
    
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
    $w=array(10,70,20,60); //determina el ancho de las columnas
    $w2=array(8,12); //determina el ancho de las columnas
// Variables. y arrays.
    $fill = false; $i=1; $descuento_porcentaje = 0; $num_registros = 0;
	$salario_total = array(); $renta_total = array(); $liquido_total = array(); $aguinaldo_total = array();
//	colocar la Y, en la ubicación
	$pdf->SetXY(10,60);
        while($Planilla = $consulta_personal -> fetch(PDO::FETCH_BOTH))
            {
			// Declaramos variables.
				$nombre_completo = (trim($Planilla['nombres'])) . ' ' . (trim($Planilla['apellidos']));
				$tiempo_servicio = $Planilla["tiempo_servicio"];
				$salario = $Planilla['salario'];
				$descuento_porcentaje = ($Planilla['porcentaje'] / 100);
				$renta = $salario * $descuento_porcentaje;
				$liquido = $salario - $renta;

				// salario, renta y liquido totales guardar en matriz.
				$salario_total[] = $salario; $renta_total[] = $renta; $liquido_total[] = $liquido;
				// Calcular los días a partir del tiempo de servicio.
                // Reforma de Ley Código de Trabajo
                /* La forma en la que se saca cálculo del aguinaldo en El Salvador es de acuerdo con el tiempo que tiene el empleado de laborar para una empresa y el salario devengado.
                    El artículo 198 del Código establece que el aguinaldo se calcula así:
                    - Para quien tenga uno y tres años de servicio, la prestación equivale al salario de 15 días.
                    - Entre tres y diez años de servicio, equivale al salario de 19 días.
                    - Para quien tenga diez o más años de servicio, la prestación equivale al salario de 21 días.
                */
				if($tipo_planilla == "03"){
					if($tiempo_servicio >= 1 and $tiempo_servicio <= 3){$dias = 15;}
					if($tiempo_servicio >= 3 and $tiempo_servicio <= 10){$dias = 19;}
					if($tiempo_servicio >= 10){$dias = 21;}
					// Calcular aguinaldo.
						$aguinaldo = round(($salario / 30) * $dias,2);
						$aguinaldo_total[] = $aguinaldo;
				}
            // Definimos el tipo de fuente, estilo y tamaño.
             $pdf->SetX(10);
				$pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
                $pdf->Cell($w[0],$w2[1],$i,'LR',0,'C',$fill);        // número correlativo
                $pdf->Cell($w[1],$w2[1],cambiar_de_del($nombre_completo),'LR',0,'L',$fill);
				$pdf->Cell($w[2],$w2[1],'$ '. number_format($salario,2),'LR',0,'L',$fill);
				// EVALUAR EL TIPO DE PLANILLA PARA PRESENTAR DATOS CALCULADOS DE LA RENTA.
				if($tipo_planilla == "01"){
					$pdf->Cell($w[2],$w2[1],'$ '. number_format($renta,2),'LR',0,'L',$fill);
					$pdf->Cell($w[2],$w2[1],'$ '. number_format($liquido,2),'LR',0,'L',$fill);}
				if($tipo_planilla == "02"){
					$pdf->Cell($w[2],$w2[1],' ','LR',0,'L',$fill);
					$pdf->Cell($w[2],$w2[1],'$ '. number_format($salario,2),'LR',0,'L',$fill);}
				if($tipo_planilla == "03"){
					$pdf->Cell($w[2],$w2[1],' ','LR',0,'L',$fill);
					$pdf->Cell($w[2],$w2[1],'$ '. number_format($aguinaldo,2),'LR',0,'L',$fill);}
					
				$pdf->Cell($w[3],$w2[1],'','LR',0,'L',$fill); // firma
				$pdf->ln(); // Aplicar salto de línea para el siguiente registro.
				
				// Cambiar el color de fondo y incrementar el valor de $i.	
              	$fill=!$fill;	
              	$i++;
            }
            // despues del bucle.
			// COLOCAR LOS TOTALES.
			$pdf->SetX(10);
				$pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
                $pdf->Cell($w[0],$w2[1],'','LBR',0,'C',$fill);        // número correlativo
                $pdf->Cell($w[1],$w2[1],'TOTAL','LBR',0,'R',$fill);
				$pdf->Cell($w[2],$w2[1],'$ '. number_format(array_sum($salario_total),2),'LBR',0,'L',$fill);
				// EVALUAR EL TIPO DE PLANILLA PARA PRESENTAR DATOS CALCULADOS DE LA RENTA.
				if($tipo_planilla == "01"){
					$pdf->Cell($w[2],$w2[1],'$ '. number_format(array_sum($renta_total),2),'LBR',0,'L',$fill);
					$pdf->Cell($w[2],$w2[1],'$ '. number_format(array_sum($liquido_total),2),'LBR',0,'L',$fill);}
				if($tipo_planilla == "02"){
					$pdf->Cell($w[2],$w2[1],' ','LBR',0,'L',$fill);
					$pdf->Cell($w[2],$w2[1],'$ '. number_format(array_sum($salario_total),2),'LBR',0,'L',$fill);}
				if($tipo_planilla == "03"){
					$pdf->Cell($w[2],$w2[1],' ','LBR',0,'L',$fill);
					$pdf->Cell($w[2],$w2[1],'$ ' . number_format(array_sum($aguinaldo_total),2),'LBR',0,'L',$fill);}
				$pdf->Cell($w[3],$w2[1],'','LBR',0,'L',$fill); // firma
// Salida del pdf.
    $pdf->Output();				
	}	// condición para mostrar los registros.
	else{
		print "No Existen Registros...";
			//$pdf->Cell(1,1,"No existen registros.",0,0,'C');
	} // condición que no muestra el mensaje que no existen registros...
?>