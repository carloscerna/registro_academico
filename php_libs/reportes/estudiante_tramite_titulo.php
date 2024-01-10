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
// Inicializamos variables de mensajes y JSON
    $respuestaOK = true;
    $mensajeError = "";
    $contenidoOK = "";
// variables y consulta a la tabla.
      $codigo_all = $_REQUEST["todos"];
      $conducta = $_REQUEST['lstconducta'];
      $codigo_matricula = $_REQUEST['txtcodmatricula'];
      $codigo_alumno = $_REQUEST['txtidalumno'];
      $estudias = $_REQUEST['lstestudia'];
      $traslado = $_REQUEST['txttraslado'];;
      $mostrar_traslado = $_REQUEST["chktraslado"];
      $firma = $_REQUEST["chkfirma"];
      $sello = $_REQUEST["chksello"];
      $db_link = $dblink;
// buscar la consulta y la ejecuta.
    consultas(18,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
            $print_grado = utf8_decode(trim($row['nombre_grado']));
            $print_seccion = utf8_decode(trim($row['nombre_seccion']));
            $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
            $codigo_modalidad = (trim($row['codigo_bachillerato']));
            $codigo_grado = (trim($row['codigo_grado']));
            }
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
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo-sv.png';
    $this->Image($img,95,15,26,26);
    //Arial bold 14
        $this->SetFont('Arial','B',12);
    //Título
        $this->SetY(45);
        $this->Cell(180,5,utf8_decode('MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA'),0,1,'C');
        $this->Cell(180,5,utf8_decode('REPÚBLICA DE EL SALVADOR'),0,1,'C');
        $this->Cell(180,5,utf8_decode('DIRECCIÓN DEPARTAMENTAL DE EDUCACIÓN DE SANTA ANA'),0,1,'C');
        $this->ln();
        $this->Cell(180,5,utf8_decode($_SESSION['institucion']),0,1,'C');
}

//Pie de página
function Footer()
{
    global $firma, $sello;
    //Posición: a 1,5 cm del final
    $this->SetY(-30);
    //Arial italic 8
    $this->SetFont('Arial','I',12);
    //Firma Director.
    if($firma == 'yes'){
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
	$this->Image($img,80,232,70,15);
    }
    if($sello == 'yes'){
	$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
	$this->Image($img_sello,125,225,30,30);
    }
    //Nombre Director
        //$this->Line(80,249,140,249);   // Línea firma director.
        $this->Cell(180,5,cambiar_de_del($_SESSION['nombre_director']),0,1,'C');
        $this->Cell(180,5,'Director(a) del Centro Educativo',0,1,'C');
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
    $pdf->SetXY(15,80);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',12); // I : Italica; U: Normal;
//  mostrar los valores de la consulta
    $w=array(60,25,125); //determina el ancho de las columnas
    $w2=array(8,12); //determina el ancho de las columnas
// Variables.
    $fill = false; $i=1;
    $nombre_departamento = cambiar_de_del($_SESSION['nombre_departamento']);
    $nombre_municipio = cambiar_de_del($_SESSION['nombre_municipio']);
    $porciones = explode(" ", $print_bachillerato);
    $nombre_modalidad = cambiar_de_del(trim($porciones[1]));
    $año_anterior = $año;
/**********************************************************************************************************************************************************/
// Consultar y Ejecutar el Query.
    consultas_alumno(3,0,$codigo_all,$codigo_alumno,$codigo_matricula,'',$db_link,'');      
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $nombre_estudiante = trim($row['nombre_a_pm']);
                // Crear variales para los respectivos párrafos.
                $primer_parrafo = utf8_decode('El infrascrito(a) director(a) del '.$_SESSION['institucion'].' del municipio de ' . $nombre_municipio. ', Departamento de ' . $nombre_departamento.'.');
                $segundo_parrafo = utf8_decode('HACE CONSTAR QUE: '. $nombre_estudiante .', Con Número de Identificación Estudiantil (NIE): '.trim($row['codigo_nie'])
                 .' ha culminado satisfactoriamente sus estudios de bachillerato en la modalidad de '. $nombre_modalidad .'  en este Centro Educativo, dando cumplimiento a todos los requisitos exigidos por el  Ministerio de Educación para la legalización del Título de Bachillerato '.$nombre_modalidad) . '.';
                $tercer_parrafo = utf8_decode('Por tanto, su título que le acredita como Bachiller de la República, se encuentra en trámite de legalización. Ante ello, el Ministerio de Educación, Ciencia y Tecnología está haciendo las gestiones pertinentes con base a la solicitud enviada por nuestra institución'
                .', para la emisión del respectivo título en la mayor brevedad posible, el cual tendrá validez a partir del 12 de diciembre del año '. $año_anterior .'.');
                $cuarto_parrafo = utf8_decode('Y para los usos que el/la interesado(a) estime conveniente, se le extiende la presente constancia, en el municipio de '. $nombre_municipio . '  departamento de '. $nombre_departamento.', '
                . 'a los '. strtolower(num2letras($dia)).' días de '.$mes.' de '.strtolower(num2letras($año))).'.';
                // Imprimir párrafos en pantallas
                $pdf->MultiCell(0,8,$primer_parrafo,0,"J");
                $pdf->ln();
                $pdf->MultiCell(0,8,$segundo_parrafo,0,"J");
                $pdf->ln();
                $pdf->MultiCell(0,8,$tercer_parrafo,0,"J");
                $pdf->ln();
                $pdf->MultiCell(0,8,$cuarto_parrafo,0,"J");
		            break;
            }
// despues del bucle.
/**********************************************************************************************************************************************************/
// Construir el nombre del archivo.
    $nombre_archivo = $nombre_estudiante . '.pdf';
// Salida del pdf.
    $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
    $pdf->Output($nombre_archivo,$modo);
?>