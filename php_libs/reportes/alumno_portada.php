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
      $codigo_alumno = $_REQUEST['txtidalumno'];
      $db_link = $dblink;
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
        $mes = $meses[date('n')-1];     // El Mes.
		$año = strftime("%Y");		// El Año.

class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
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
    
//N�mero de p�gina y fecha
    $this->SetY(-15);
    $this->SetX(10);
    $fecha = date("l, F jS Y ");
    $this->SetFont('Arial','',10);
    $this->Cell(0,10,'Digitalizado: ' . $fecha,0,0,'R');
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
//	Agregar el tipo de letra.	
	$pdf->AddFont('Comic','','comic.php');
	$pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetY(20);
    $pdf->SetX(15);
// Diseño de Lineas y Rectangulos.
    $pdf->SetFillColor(224);
    $pdf->RoundedRect(45, 55, 155, 8, 2, '1234', 'DF'); // nombres y apellidos
    $pdf->RoundedRect(105, 65, 35, 8, 2, '1234', '');   // NIE
    $pdf->RoundedRect(90, 75, 35, 8, 2, '1234', '');   // Fecha de Nacimiento
    $pdf->RoundedRect(90, 85, 50, 8, 2, '1234', '');   // Departamento de nacimiento
    $pdf->RoundedRect(90, 95, 95, 8, 2, '1234', '');   // Municipio de Nacimiento
    
    $pdf->RoundedRect(35, 105, 20, 8, 2, '1234', '');   // Pn numero
    $pdf->RoundedRect(77, 105, 20, 8, 2, '1234', '');   // Pn folio
    $pdf->RoundedRect(125, 105, 20, 8, 2, '1234', '');   // Pn libro
    $pdf->RoundedRect(172, 105, 20, 8, 2, '1234', '');   // Pn tomo
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
//  mostrar los valores de la consulta
    $w=array(60,25,125); //determina el ancho de las columnas
    $w2=array(8,12); //determina el ancho de las columnas
// Variables.
    $fill = false; $i=1;  $promedio_institucional = 0; $promedio_paes = 0; $promedio_final = 0; $pi=0;
// Consultar y Ejecutar el Query.
      $query = "SELECT a.id_alumno, a.codigo_nie, a.apellido_paterno, a.apellido_materno, a.nombre_completo, a.codigo_departamento, a.codigo_municipio, a.fecha_nacimiento,
                a.pn_numero, a.pn_folio, a.pn_tomo, a.pn_libro
                ,depa.nombre as nombre_departamento, depa.codigo,
                muni.nombre as nombre_municipio
                    FROM alumno a
                INNER JOIN departamento depa ON depa.codigo = a.codigo_departamento 
                INNER JOIN municipio muni ON muni.codigo = a.codigo_municipio and a.codigo_departamento = muni.codigo_departamento
                    WHERE id_alumno = '$codigo_alumno'";
        $result = $db_link -> query($query);
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                // variables.
                    $apellido_paterno = trim($row['apellido_paterno']);
                    $apellido_materno = trim($row['apellido_materno']);
                    $nombre_completo = trim($row['nombre_completo']);
                    $nombre_departamento = utf8_decode(trim($row['nombre_departamento']));
                    $nombre_municipio = utf8_decode(trim($row['nombre_municipio']));
                    $fecha_nacimiento = cambiaf_a_normal(trim($row['fecha_nacimiento']));
                    
                    // Datos de partida de nacimiento
                    $pn_numero = trim($row['pn_numero']);
                    $pn_folio = trim($row['pn_folio']);
                    $pn_tomo = trim($row['pn_tomo']);
                    $pn_libro = trim($row['pn_libro']);
                    
                    $apellido_alumno = $apellido_paterno . ' ' . $apellido_materno . ', ' . $nombre_completo;
                
            // Definimos el tipo de fuente, estilo y tamaño.
            $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
             $pdf->SetXY(15,45);
             

             $pdf->RotatedText(20,60,'Alumno(a): ',0);
             $pdf->SetFont('Arial','IB',13);
             $pdf->RotatedText(50,60,utf8_decode(trim($apellido_alumno)),0);   // Nombre + apellido_materno + apellido_paterno
             $pdf->SetFont('Arial','',12);
        
             $pdf->RotatedText(20,70,utf8_decode('Número de Identificación Estudiantil (NIE): '),0);
             $pdf->SetFont('Arial','B',13);
             $pdf->RotatedText(112,70,utf8_decode(trim($row['codigo_nie'])),0);   // NIE
             $pdf->SetFont('Arial','',12);
			// Fecha de nacimiento
            $pdf->RotatedText(20,80,utf8_decode('Fecha de Nacimiento: '),0);
             $pdf->SetFont('Arial','B',13);
             $pdf->RotatedText(95,80,$fecha_nacimiento,0);   // NIE
             $pdf->SetFont('Arial','',12);

			// Departamento de nacimiento
            $pdf->RotatedText(20,90,utf8_decode('Departamento de Nacimiento: '),0);
             $pdf->SetFont('Arial','B',13);
             $pdf->RotatedText(95,90,$nombre_departamento,0);   // NIE
             $pdf->SetFont('Arial','',12);
            
			// Municipio  de nacimiento
            $pdf->RotatedText(20,100,utf8_decode('Municipio de Nacimiento: '),0);
             $pdf->SetFont('Arial','B',13);
             $pdf->RotatedText(95,100,$nombre_municipio,0);   // NIE
             $pdf->SetFont('Arial','',12);
             // Número de Partida de Nacimiento
                $pdf->RotatedText(20,110,utf8_decode('Nº P.N.'),0);
                $pdf->SetFont('Arial','B',13);
                $pdf->RotatedText(39,110,$pn_numero,0);   // p.n.
                $pdf->SetFont('Arial','',12);
             // Folio
                $pdf->RotatedText(60,110,utf8_decode('Nº Folio'),0);
                $pdf->SetFont('Arial','B',13);
                $pdf->RotatedText(80,110,$pn_folio,0);   // folio
                $pdf->SetFont('Arial','',12);
             // Tomo
                $pdf->RotatedText(105,110,utf8_decode('Nº Tomo'),0);
                $pdf->SetFont('Arial','B',13);
                $pdf->RotatedText(132,110,$pn_tomo,0);   // tomo
                $pdf->SetFont('Arial','',12);
             // Libro
                $pdf->RotatedText(152,110,utf8_decode('Nº Libro'),0);
                $pdf->SetFont('Arial','B',13);
                $pdf->RotatedText(178,110,$pn_libro,0);   // tomo
                $pdf->SetFont('Arial','',12);
                
            $pdf->SetFont('Comic','',56);
            $pdf->RotatedText(180,180,utf8_decode(trim($row['id_alumno'])),270);   // NIE
            
            			
              	$fill=!$fill;	
              		$i++;
		break;
            }
            // despues del bucle.
// Salida del pdf.
    $pdf->Output();
?>