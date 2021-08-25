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
	  $nombre_mes = $_REQUEST['lstmes'];
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
		$nombre_mes = $meses[$nombre_mes - 1];
		//$mes = $meses[date('n')-1];     // El Mes.
		$año = strftime("%Y");		// El Año.


class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
		global $nombre_rubro, $mes;
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
    //Crear una línea de la primera firma.
    $this->Line(15,210,90,210); // Tesorero
	$this->Line(115,210,200,210); // Consejal
    $this->Line(65,245,150,245); // Presidente CDE.
	
    //Nombre Director (Presidente)
    $this->RotatedText(70,250,cambiar_de_del($_SESSION['nombre_director']),0,1,'C');
    $this->RotatedText(90,255,'Presidente',0,1,'C');

    //Nombre Tesorero)
    $this->RotatedText(25,215,cambiar_de_del('ANA JULIA ARGUETA DE RIVAS'),0,1,'C');
    $this->RotatedText(45,220,'Tesorero(a)',0,1,'C');

    //Nombre Consejal
    $this->RotatedText(115+22,215,cambiar_de_del('JORGE ARMANDO ANAYA'),0,1,'C');
    $this->RotatedText(103+33,220,'Consejal de Maestros',0,1,'C');
    
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
    $w=array(10,70,20,60,40); //determina el ancho de las columnas
    $w2=array(6,12); //determina el ancho de las columnas
// Variables. y arrays.
    $fill = false; $i=1; $descuento_porcentaje = 0; $num_registros = 0; $label_tipo_planilla = ""; $tipo_salario = 0;
//	colocar la Y, en la ubicación
	$pdf->SetXY(15,50);
        while($Planilla = $consulta_personal -> fetch(PDO::FETCH_BOTH))
            {
			// Declaramos variables.
				$nombre_completo = (trim($Planilla['nombres'])) . ' ' . (trim($Planilla['apellidos']));
				$nombre_cargo = (trim($Planilla['nombre_cargo']));
				$nombre_contratacion = (trim($Planilla['nombre_contratacion']));
				$dui = (trim($Planilla['dui']));
				$nit = (trim($Planilla['nit']));
				$tiempo_servicio = trim($Planilla["tiempo_servicio"]);
				$salario = $Planilla['salario'];
				$descuento_porcentaje = ($Planilla['porcentaje'] / 100);
				$renta = $salario * $descuento_porcentaje;
				$liquido = $salario - $renta;
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
				if($tipo_planilla == "01"){$label_tipo_planilla = "Salario del mes de " . ($nombre_mes);$tipo_salario = number_format($salario,2);
				$decimales = explode(".",$tipo_salario);}
				if($tipo_planilla == "02"){$label_tipo_planilla = "INDEMNIZACIÓN"; $tipo_salario = $salario; $decimales = explode(".",number_format($tipo_salario,2));}
				if($tipo_planilla == "03"){$label_tipo_planilla = "AGUINALDO"; $tipo_salario = $aguinaldo; $decimales = explode(".",number_format($tipo_salario,2));}

			// Imprimir el valor del recibo
				$pdf->RotatedText(170,38,"Por $ ". number_format($tipo_salario,2),0);
			// Primer bloque.
				$pdf->MultiCell(180,10,utf8_decode("Recibí del Consejo Directivo Escolar del ".$_SESSION['institucion']." ubicada en la ". $_SESSION['se_extiende']
												   ." Cantón Camones de Santa Ana, la cantidad de " . strtolower(num2letras($tipo_salario)) . " "
												   .$decimales[1]. "/100 de dólares, en concepto de pago de " . $label_tipo_planilla . " en el cargo de " . $nombre_cargo . " " . $nombre_contratacion . "."));
			// Segundo bloque. Lugar y Fecha.
				$pdf->Ln();
				$pdf->SetX(50);
				$pdf->Cell($w[1],$w2[1],"Lugar y Fecha: ".cambiar_de_del($_SESSION['nombre_departamento']) . ", ". $dia . " de " . $mes . " de " . $año ,'',0,'L',$fill);
				$pdf->Ln();
            // Definimos el tipo de fuente, estilo y tamaño.
				$pdf->SetFont('Arial','B',12); // I : Italica; U: Normal;
			// Tercer bloque Información de pago nombre y firma.
				$pdf->SetX(40);
					$pdf->Cell($w[4],$w2[1],'Monto',1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],'Renta ' . $descuento_porcentaje*100 . '%',1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],utf8_decode('Líquido'),1,1,'C',$fill);
				$pdf->SetX(40);
				if($tipo_planilla == "01"){
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($tipo_salario,2),1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($renta,2),1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($liquido,2),1,1,'C',$fill);}
				if($tipo_planilla == "02"){
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($tipo_salario,2),1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],' ' ,1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($tipo_salario,2),1,1,'C',$fill);}
				if($tipo_planilla == "03"){
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($tipo_salario,2),1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],' ',1,0,'C',$fill);
					$pdf->Cell($w[4],$w2[1],'$ '. number_format($aguinaldo,2),1,1,'C',$fill);}

			// Cuarto Bloque datos personales. nombre, dui y nit.
			    $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
				$pdf->Ln();
				$actualY = $pdf->GetY();
				$pdf->SetY($actualY+15);
					$pdf->Cell($w[1],$w2[0],cambiar_de_del($nombre_completo),'T',1,'L',$fill);
					$pdf->Cell($w[1],$w2[0],utf8_decode('Nº de DUI: ') . $dui,0,1,'L',$fill);
					$pdf->Cell($w[1],$w2[0],utf8_decode('Nº de NIT: ') . $nit,0,1,'L',$fill);

				$pdf->ln(); // Aplicar salto de línea para el siguiente registro.
				
				// Cambiar el color de fondo y incrementar el valor de $i.	
              	$fill=!$fill;	
              	$i++;
            }
            // despues del bucle.
	}	// condición para mostrar los registros.
	else{
			print "No existen registros.";
	} // condición que no muestra el mensaje que no existen registros...
// Salida del pdf.
    $pdf->Output();
?>