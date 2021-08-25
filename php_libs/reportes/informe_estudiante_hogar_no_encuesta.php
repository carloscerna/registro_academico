<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
	include($path_root."/registro_academico/includes/funciones.php");
	include($path_root."/registro_academico/includes/funciones_2.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");
// Inicializamos variables de mensajes y JSON
	$respuestaOK = true;
	$mensajeError = "Si Save";
	$contenidoOK = "Si Save";
// variables y consulta a la tabla.
  //$codigo_all = substr($_REQUEST["todos"],6,2);		// codigro - Extraer el año lectivo.
  $codigo_all = $_REQUEST["todos"];		// codigro - Extraer el año lectivo.
  $crear_archivos = "no";
  $crear_archivos = $_REQUEST["chkCrearArchivoPdf"];
// Establecer formato para la fecha.
	date_default_timezone_set('America/El_Salvador');
	setlocale(LC_TIME,'es_SV');
	//$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","S�bado");
    $meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
	$dia = strftime("%d");		// El Día.
    $mes = $meses[date('n')-1];     // El Mes.
	$año = strftime("%Y");		// El Año.
// variable de la conexi�n dbf.
    $db_link = $dblink;
////////////////////////////////////////////////////////////////////
//////// DATOS DEL ENCABEZADO O CODIOS Y NOMBRES DE MODALIDAD, GRADO, SECCION Y TURNO.
//////////////////////////////////////////////////////////////////
    // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
       // buscar la consulta y la ejecuta.
       $new_codigo_all = array(); 
	   consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
	   $print_nombre_docente = "";
	   while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
		   {
			   $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
			   
			   if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
				   $print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
               }
               // CONSTRUIR EL NUEVO CODIGO ALL PARA VER TODOS LOS GRADOS CON RESPECTO AL RESPONSABLE.
                    //$new_codigo_all[] = trim($row['codigo_bachillerato']) . trim($row['codigo_grado']) .trim($row['codigo_seccion']) .trim($row['codigo_ann_lectivo']) .trim($row['codigo_turno']);
           }  
           
 // ENCABEZADO PRO GRADO Y SECCIÓN.
 consultas(4,0,$codigo_all,'','','',$db_link,'');
 while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
     {
         $print_bachillerato = utf8_decode(trim($row['nombre_bachillerato']));
         $print_grado = (trim($row['nombre_grado']));
         $print_seccion = utf8_decode(trim($row['nombre_seccion']));
         $print_ann_lectivo = utf8_decode(trim($row['nombre_ann_lectivo']));
         $print_codigo_grado = (trim($row['codigo_grado']));
         $print_codigo_bachillerato = (trim($row['codigo_bachillerato']));
         $print_codigo_alumno = $row['codigo_alumno'];
         $print_codigo_matricula = $row['cod_matricula'];
         $print_turno = $row['nombre_turno'];
             break;
     }
////////////////////////////////////////////////////////////////////
// COLOCAR ENCABEZANDO A LA BOLETA DE CALIFICACIÓN.		
 if($print_codigo_bachillerato >= '03' and $print_codigo_bachillerato <= '05')
 {
 $nivel_educacion = "Educación Básica";
 }elseif($print_codigo_bachillerato >= '01' and $print_codigo_bachillerato <= '03')
 {
 $nivel_educacion = "Educación Parvularia";
 }else{
 // Validar Bachillerato.
 if($print_codigo_bachillerato == '06'){
     $nivel_educacion = "Educación Media - General";
 }
 if($print_codigo_bachillerato == '07'){
     $nivel_educacion = "Educación Media - Técnico";
 }

 if($print_codigo_bachillerato == '08' or $print_codigo_bachillerato == '09'){
     $nivel_educacion = "Educación Media - Contaduría";
 }
 if($print_codigo_bachillerato == '10'){
     $nivel_educacion = "Educación Básica - TERCER CICLO - NOCTURNA";
 }
 if($print_codigo_bachillerato == '11'){
     $nivel_educacion = "Educación Media - General - NOCTURNA";
 }		
     // Validar grado de educaci�n Media.
     if($print_codigo_grado == '10'){
         $print_grado_media = "Primer año";
     }
     if($print_codigo_grado == '11'){
         $print_grado_media = "Segundo año";
     }
     if($print_codigo_grado == '12'){
         $print_grado_media = "Tercer año";
     }
 }

class PDF extends FPDF
{
//Cabecera de p�gina
function Header()
{
	// variables globales.
    global $nivel_educacion, $print_codigo_grado, $print_seccion, $print_grado_media, $print_ann_lectivo, $print_codigo_bachillerato, $print_grado, $print_nombre_docente;
    
	$nombre_institucion = utf8_decode($_SESSION['institucion']);
    // Ancho de la linea y color.
    $this->SetLineWidth(.7);				// GROSOR.
	$this->SetDrawColor(10,29,247);			// COLOR DE LA LÍNEA.
	$this->SetFont('Times','B',12);			// TAMAÑO DE FUENTE 14. NEGRITA.
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];		//Logo
	$boleta_etiqueta = utf8_decode('Actualización de Datos del Estudiante - ' . ' Año Lectivo ' . $print_ann_lectivo);		// etiqueta Boleta de Calificación
	// Título principal.
	if($nivel_educacion == 'Educación Básica' or $nivel_educacion == 'Educación Básica - TERCER CICLO - NOCTURNA')
		{
			$titulo_principal = utf8_decode($nivel_educacion).' - '. utf8_decode($print_grado) .' - '."'".$print_seccion."'";
	}else
		{
			$titulo_principal = utf8_decode($nivel_educacion).' - '. strtolower(utf8_decode($print_grado)) .' - '."'".$print_seccion."'";
		}
	/////////////////////////////////////////////////////////////////////////////////////////////
	// IMPRIMIR VALORES. para el encabezado principal.
		$this->Line(19,15,200,15);								// LINEA EN VERTICAL
        $this->SetFont('Times','B',11);							// TAMAÑO DE FUENTE 14. NEGRITA.
        $this->SetXY(20,5);
        $this->Cell(25,5,$nombre_institucion,0,1,'L');			// NOMBRE INSTITUCIÓN.
        $this->SetX(20);
		$this->Cell(5,5,$boleta_etiqueta,0,1,'L');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
		$this->SetX(25);
		$this->Cell(120,5,"Docente: ".$print_nombre_docente,0,0,'L');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
        $this->Cell(55,5,$titulo_principal,0,1,'R');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
        $this->SetX(25);
        $this->Cell(55,5,'ESTUDIANTES PENDIENTES DE HACER LA ENCUESTA',0,1,'L');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
	/////////////////////////////////////////////////////////////////////////////////////////////
	// PRINT VALORES FIJOS Y ETIQUETAS NO CAMBIAN.
	$this->Image($img,7,5,12,15);				//LOGO.
    $this->SetLineWidth(.3);					//GROSOR
}

//Pie de p�gina
function Footer()
{
	// Variables.
	global $registro_docente, $firma, $sello, $print_codigo_alumno, $print_codigo_matricula, $print_codigo_bachillerato, $meses, $dia, $mes, $año;
	//Firma Director.
		$nombre_director = cambiar_de_del($_SESSION['nombre_director']);
    if($firma == 'yes'){
		$img_firma = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
    	}
    if($sello == 'yes'){
		$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
    }	
    $this->SetFont('Arial','I',8);	    //Arial italic 8    
	// PRINT A PANTALLA.
		// NOCTURNA BASÍCA Y MEDIA.
	/*	$this->SetY(-20);				//Posici�n: a 1,5 cm del final
		$this->Line(10,245,80,245);		//Crear una l�nea de la primera firma
		$this->Line(120,245,190,245);	//Crear una l�nea de la segunda firma.
		$this->Line(5,265,203,265);		//Crear una l�nea FINAL.
		$this->RotatedText(50,255,$registro_docente,0,1,'C');		// NOMBRE DEL DOCENTE.
		if(isset($img_firma)){$this->Image($img_firma,120,225,70,15);}						// IMAGEN FIRMA
		if(isset($img_firma)){$this->Image($img_sello,80,225,30,30);}						// IMAGEN SELLO
    	$this->RotatedText(130,250,$nombre_director,0,1,'C');	    // Nombre Director
		$this->RotatedText(140,255,'Director(a)',0,1,'C');			// ETIQUETA DIRECTOR.
*/
    //N�mero de p�gina y fecha
    $this->SetY(-15);
    $this->SetX(10);
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}' .' - ' . $fecha,0,0,'C');
}

//Tabla coloreada
function FancyTable($header)
{
  global $print_codigo_bachillerato;
    //Colores, ancho de l�nea y fuente en negrita
		$this->SetFillColor(229,229,229);
		$this->SetTextColor(0,0,0);
		$this->SetDrawColor(128,0,0);
		$this->SetLineWidth(.3);
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// VALOR3ES Y ENCABEZADO PARA TODOS LOS GRADOS
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    $ancho=array(20,20,20,30,80); //determina el ancho de las columnas
    $alto=array(5,12);
	// ARRAY PARA LAS DIFERENTES ETIQUETAS.
		$etiqueta_encabezado = array('Grado','Sección','Turno','NIE','ESTUDIANTE');
	// encabezado table.
	///////////////////////////////////////////////////////////////////////////////////////
    // LABEL. resultado final.
    $this->SetXY(5,25);
        for($jj=0;$jj<count($etiqueta_encabezado);$jj++)
        {
            $this->Cell($ancho[$jj],$alto[0],utf8_decode($etiqueta_encabezado[$jj]),1,0,'C');
        }
    $this->ln();
	///////////////////////////////////////////////////////////////////////////////////////
    //Restauraci�n de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('Times','',10);
    //Datos
    	$fill=false;
}

}
//************************************************************************************************************************
//**PRIMER PASO - CREAR CONSULTAS
//************************************************************************************************************************
// INICIAR EL FOR
//for($xh=0;$xh<count($new_codigo_all);$xh++)
//{
   
    //************************************************************************************************************************
//************************************************************************************************************************
//	CREAR FOR PARA RECORRER EL LISTADO Y ASÍ OBTENER LA BOLETA DE NOTAS.
// Creando el Informe. cuando va al navegador.
	if($crear_archivos == 'no')
	{
		$pdf=new PDF('P','mm','Letter');	// Formato Tamaño Legal (8.5" x 14")
		#Establecemos los m�rgenes izquierda, arriba y derecha: 
			$pdf->SetMargins(5, 5, 5);
		#Establecemos el margen inferior: 
			$pdf->SetAutoPageBreak(true,5);
		//T�tulos de las columnas
			$header=array('');
			$pdf->AliasNbPages();
	}
//************************************************************************************************************************
// CREAR LAS DIFERENTES BOLETAS DEPENDE DE LA ARRAY CREADA.
//************************************************************************************************************************
	// Creando el Informe. cuando va a la carpeta.
	if($crear_archivos == 'si')
	{
		$pdf=new PDF('P','mm','Letter');	// Formato Tamaño Legal (8.5" x 14")
		#Establecemos los m�rgenes izquierda, arriba y derecha: 
			$pdf->SetMargins(5, 5, 5);
		#Establecemos el margen inferior: 
			$pdf->SetAutoPageBreak(true,5);
		//T�tulos de las columnas
			$header=array('');
			$pdf->AliasNbPages();
	}
		// Coordenadas de iNICIO.
			$pdf->SetY(45);
			$pdf->SetX(5);
		// variales para la boleta.
			$fill = false; $i=1;  $suma = 0; $salto_pagina = 1; $estudiantes_faltantes = 0;
			$array_nie_nombre_error = array();
        ///////////////////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////////////////		
            // IMPRIME LA PRIMERA ASIGNATURA Y CREA LO NECESARIO.
            if ($salto_pagina == 1)
            {
                $pdf->AddPage();
                $pdf->SetFont('Times','',10); // I : Italica; U: Normal;
                // dibujar encabezado de la tabla.
                $pdf->SetXY(5,45);
                $pdf->FancyTable($header);
            }
	// condicionar el ancho y ALTO de cada columna.
        $ancho=array(20,20,20,30,80); //determina el ancho de las columnas
		$alto=array(5,12,4); //determina el alto de las columnas
// *************************************************************************************************************************
// ejecutar consulta. que proviene de la nomina. SE CREA LA ARRAY() CODIGO_ALUMNO_LISTADO Y CODIGO_MATRICULA_LISTADO.
// *************************************************************************************************************************
// *************************************************************************************************************************
    consultas(4,0,$codigo_all,'','','',$db_link,'');
    $fill = false;
// *************************************************************************************************************************
	
// *************************************************************************************************************************
//	INICIA EL WHILE CON RESPECTO AL VALOR DE LA NOMINA ( CODIGO ALUMNO, CODIGO MATRICULA.)
// *************************************************************************************************************************
// MATRIZ NOMBRE DE LOS CAMPOS. unset( $animales[0] ); BORRAR MATRIZ
    while($row = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
    {
        // variables a utilizar.
            $nombre_completo_alumno = utf8_decode(ltrim($row['nombre_completo_alumno']));
            $numero_identificacion_estudiantil = trim($row['codigo_nie']);
            $codigo_alumno = $row['codigo_alumno'];
            $codigo_matricula = $row['cod_matricula'];

                    // DATOS DEL RESPONSABLE. BUSCAR EN LA TABLA alumno_hogar.
                        $query_alumno_hogar = "SELECT * FROM alumno_hogar WHERE codigo_alumno = '$codigo_alumno' and codigo_nie = '$numero_identificacion_estudiantil'";
                    // Ejecutamos el Query.
                        $consulta_hogar = $dblink -> query($query_alumno_hogar);
                    // Verificar si existen registros.
                    if($consulta_hogar -> rowCount() == 0){
                        // ESTUDIANTES QUE NO HAN HECHO LA ENCUESTA.
                        $estudiantes_faltantes++;
                        $pdf->Cell($ancho[0],$alto[0],utf8_decode($print_grado),0,0,'L');
                        $pdf->Cell($ancho[1],$alto[0],($print_seccion),0,0,'L');
                        $pdf->Cell($ancho[2],$alto[0],($print_turno),0,0,'L');
                        $pdf->Cell($ancho[3],$alto[0],utf8_decode($numero_identificacion_estudiantil),0,0,'L');
                        $pdf->Cell($ancho[4],$alto[0],($nombre_completo_alumno),0,1,'L');
                    }
    } // BUCLE QUE RECORRE EL ESTUDIANTE SELECCIONADO A PARTIR DE LA NÓMINA.
    //print "<br>$print_bachillerato - $print_codigo_grado";
    $pdf->ln();
    $pdf->Cell($ancho[0],$alto[0],utf8_decode('N.º de Estudiantes qe no han Hecho la ENCUESTA -->  ') . ($estudiantes_faltantes),0,0,'L');
    // reestablecer valor de variables.
      //  $salto_pagina = 1;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//}   // TERMINA EL FOR QUE RECORRER los docentes encargados de grado.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Salida del pdf.
	if($crear_archivos == "si"){
		// Verificar si Existe el directorio archivos.
			$codigo_modalidad = $print_bachillerato;
			$nombre_ann_lectivo = $print_ann_lectivo;
		// Tipo de Carpeta a Grabar.
			$codigo_destino = 1;
			$nuevo_grado = replace_3(trim($print_grado));
            CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
            $new_carpeta = "No Encuesta";
		// verificar si existe el grado y sección.
		if(!file_exists($DestinoArchivo . $new_carpeta))
		{
			// Para Nóminas. Escolanadamente.
				mkdir ($DestinoArchivo . $new_carpeta);
				chmod($DestinoArchivo . $new_carpeta,07777);
		}
		$NuevoDestinoArchivo = $DestinoArchivo . "$new_carpeta/";	
		//$NuevoDestinoArchivo = $DestinoArchivo . $nuevo_grado . ' ' . trim($print_seccion) . "/";
			$nombre_archivo = $print_nombre_docente.' - '. $print_bachillerato . utf8_decode($print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '- NO Encuesta-.pdf');
			$modo = 'F'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
			$print_nombre = $NuevoDestinoArchivo . trim($nombre_archivo) . '.pdf';
			
			//$print_nombre = $path_root . '/registro_academico/temp/' . trim($nombre_completo_alumno) . ' ' . trim($print_grado) . ' ' . trim($print_seccion) . '.pdf';
			$pdf->Output($print_nombre,$modo);
	}			
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($crear_archivos == "no"){
// Construir el nombre del archivo.
	$nombre_archivo = utf8_decode($print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '.pdf');
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