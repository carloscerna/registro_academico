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
  $codigo_all = $_REQUEST["todos"];		// codigro - 
  $codigo_alumno = $_REQUEST["txtidalumno"];
  $codigo_matricula = $_REQUEST["txtcodmatricula"];
  if(isset($_REQUEST["codigo_ann_lectivo"])){
	$codigo_ann_lectivo = $_REQUEST["codigo_ann_lectivo"];
  }
  $crear_archivos = "no";
  $firma = $_REQUEST["chkfirma"];
  $sello = $_REQUEST["chksello"];
  $chkfoto = $_REQUEST["chkfoto"];
  $crear_archivos = $_REQUEST["chkCrearArchivoPdf"];
  $print_uno = $_REQUEST["print_uno"]; // variable para imprimir un solo registro.
// variables a utilizar en el encabezado de la tabla para las notas.
	$registro_docente = "Docente";
	$periodo_trimestre = "TRIMESTRE";
	$conteo_reprobadas = array();
	$conteo_aprobadas = array();
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
//////// crear matriz para la tabla CATALOGO_AREA_ASIGNATURA.
//////////////////////////////////////////////////////////////////
$catalogo_area_asignatura_codigo = array();	// matriz para los diferentes código y descripción.
$catalogo_area_asignatura_area = array();
$catalogo_area_basica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_formativa = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_tecnica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_edps = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_edecr = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_edre = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_complementaria = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_cc = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
$catalogo_area_alertas = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
// buscar la consulta y la ejecuta.
$query = "SELECT * FROM catalogo_area_asignatura ORDER BY codigo";
$result_catalogo_area = $db_link -> query($query);	    // ejecutar la consulta.
// recorrer consulta.
while($row = $result_catalogo_area -> fetch(PDO::FETCH_BOTH))
	{
		$catalogo_area_asignatura_codigo[] = trim($row['codigo']);
		$catalogo_area_asignatura_area[] = trim($row['descripcion']);
	}
	
	/*print_r($catalogo_area_asignatura_codigo);
	print "<br>";
	print_r($catalogo_area_asignatura_area);
	*/
////////////////////////////////////////////////////////////////////
//////// DATOS DEL ENCABEZADO O CODIOS Y NOMBRES DE MODALIDAD, GRADO, SECCION Y TURNO.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
	consultas(18,0,$codigo_all,'','','',$db_link,'');
	while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
		{
			$print_bachillerato = mb_convert_encoding(trim($row['nombre_bachillerato']),"ISO-8859-1","UTF-8");
			$print_grado = (trim($row['nombre_grado']));
			$print_seccion = mb_convert_encoding(trim($row['nombre_seccion']),"ISO-8859-1","UTF-8");
			$print_ann_lectivo = mb_convert_encoding(trim($row['nombre_ann_lectivo']),"ISO-8859-1","UTF-8");
			$print_codigo_grado = (trim($row['codigo_grado']));
			$print_codigo_bachillerato = (trim($row['codigo_bachillerato']));
			$print_codigo_alumno = $row['codigo_alumno'];
			$print_codigo_matricula = $row['codigo_matricula'];
				break;
		}
////////////////////////////////////////////////////////////////////
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
  consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
	$total_asignaturas = 0;	
        while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
            {
				$total_asignaturas = (trim($row['total_asignaturas']));
            }
// COLOCAR ENCABEZANDO A LA BOLETA DE CALIFICACIÓN.		
      	if($print_codigo_bachillerato == "13" || $print_codigo_bachillerato == "14" || $print_codigo_bachillerato == "16")
	    	{
            $nivel_educacion = "Educación Parvularia";
            }
			if($print_codigo_bachillerato == "14")
	    	{
            $nivel_educacion = "Educación Básica - Estándar de Desarrollo";
            }
			if($print_codigo_bachillerato == "16" || $print_codigo_bachillerato == "17")
	    	{
            $nivel_educacion = "Educación Básica - Segundo y Tercero";
            }
class PDF extends FPDF
{
//Cabecera de p�gina
function Header()
{
	// variables globales.
	global $nivel_educacion, $print_codigo_grado, $print_seccion, $print_grado_media, $print_ann_lectivo, $print_codigo_bachillerato, $print_grado;
	$nombre_institucion = mb_convert_encoding($_SESSION['institucion'],"ISO-8859-1","UTF-8");
    // Ancho de la linea y color.
    $this->SetLineWidth(.7);				// GROSOR.
	$this->SetDrawColor(10,29,247);			// COLOR DE LA LÍNEA.
	$this->SetFont('Times','B',13);			// TAMAÑO DE FUENTE 14. NEGRITA.
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];		//Logo
	$boleta_etiqueta = mb_convert_encoding('Boleta de Calificación - ' . ' Año Lectivo ' . $print_ann_lectivo,"ISO-8859-1","UTF-8");		// etiqueta Boleta de Calificación
	// Título principal.
	if($nivel_educacion == 'Educación Básica' or $nivel_educacion == 'Educación Básica - TERCER CICLO - NOCTURNA')
		{
			$titulo_principal = mb_convert_encoding("$nivel_educacion - $print_grado - $print_seccion","ISO-8859-1","UTF-8");
	}else
		{
			$titulo_principal = mb_convert_encoding("$nivel_educacion - $print_grado - $print_seccion","ISO-8859-1","UTF-8");
		}
	/////////////////////////////////////////////////////////////////////////////////////////////
	// IMPRIMIR VALORES. para el encabezado principal.
		$this->RoundedRect(185, 5, 25, 30, 3.5, '1234','');		// RECTANGULO de la foto.
		$this->Line(27,20,185,20);								// LINEA EN VERTICAL
		$this->Cell(205,5,$nombre_institucion,0,1,'C');			// NOMBRE INSTITUCIÓN.
		$this->Cell(205,5,$boleta_etiqueta,0,1,'C');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
		$this->SetFont('Times','B',12);							// TAMAÑO DE FUENTE 14. NEGRITA.
		$this->Cell(205,5,$titulo_principal,0,1,'C');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
	/////////////////////////////////////////////////////////////////////////////////////////////
	// PRINT VALORES FIJOS Y ETIQUETAS NO CAMBIAN.
	$this->Image($img,7,6,20,25);				//LOGO.
    $this->SetLineWidth(.3);					//GROSOR
    $this->RotatedText(30,27,'Nombre',0);		// LABEL NOMBRE
    $this->RotatedText(30,37,'NIE',0);			// ALBEL NIE.
    $this->RoundedRect(50, 22, 130, 7, 1.5, '1234','');	// para el nombre
    $this->RoundedRect(50, 31, 35, 7, 1.5, '1234','');	// para el nie
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
    $this->Line(5,265,203,265);		//Crear una l�nea FINAL.
	// PRINT A PANTALLA.
		// NOCTURNA BASÍCA Y MEDIA.
	/*	$this->SetY(-20);				//Posici�n: a 1,5 cm del final
		$this->Line(10,245,80,245);		//Crear una l�nea de la primera firma
		$this->Line(120,245,190,245);	//Crear una l�nea de la segunda firma.
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
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}' .' - ' . $fecha .' - ' . 'Id_a: ' . $print_codigo_alumno . ' Id_m: ' . $print_codigo_matricula,0,0,'C');
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
	// ARRAY PARA LAS DIFERENTES ETIQUETAS.
		$etiquetas_boleta_de_notas = array('A S I G N A T U R A S','M O D U L O S','RESULTADO FINAL','PERÍODOS','TRIMESTRE');
		$etiqueta_resultado_final = array('T. P.','P. P.','REPO.','N. F.');
		$etiqueta_numeros = array("1","2","3","N.F.");
		$etiqueta_resultado = array("");
	// encabezado de la boleta.
	// LINES O RECTÁNGULOS.
		$this->RoundedRect(5, 40, 203, 10, 0.5, '');	// primer cuadro.
		$this->RoundedRect(5, 40, 140, 10, 0.5, '');		// para el nombre de la asignatura
    if($print_codigo_bachillerato == '13' || $print_codigo_bachillerato == '14' || $print_codigo_bachillerato == "16")
    {
		$n_label = 6; $n_etiqueta = 4;}
	// medidas para NOCTURNA POR EL N-º DE MODULOS.
	// crear las columnas de TRIMESTRE, PERIODO O MODULOS.
		$x = 145; $n_e = 0; $n_r = 0; $n_f = 0;
		$ancho_t = 10; $ancho_r = 5; $ancho_f = 12;
		for($j=0;$j<=3;$j++){
            $this->RoundedRect($x, 45, $ancho_t, 5, 0.5, '');
            $this->RotatedText($x+4,49,$etiqueta_numeros[$n_e],0);
                $x = $x + $ancho_t;
					$n_e++;
		}
	///////////////////////////////////////////////////////////////////////////////////////
	// LABEL. resultado final.
		$this->RoundedRect(145, 40, 40, 10, 0.5, '');	// para los periodos, trimestres o modulos.
		$this->SetFont('Times','B',14);
		$this->RotatedText(61,47,$etiquetas_boleta_de_notas[0],0);		  
		$this->SetFont('Times','B',12);
		$this->RotatedText(150,44,mb_convert_encoding($etiquetas_boleta_de_notas[$n_etiqueta],"ISO-8859-1","UTF-8"),0);		  
		//$this->RotatedText(165,44,$etiquetas_boleta_de_notas[2],0);		  
		$this->SetFont('Times','B',10);
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
// NOMINA DE ALUMNOS DE AÑO LECTIVO, GRADO Y SECCIÓN.
//  mostrar los valores de la consulta para listado de las notas o solo una.
	if($print_uno == 'yes')
		{
			consultas_alumno(2,0,$codigo_all,$codigo_alumno,$codigo_matricula,$print_ann_lectivo,$db_link,'');
			$codigo_alumno_listado[] = $codigo_alumno;
			$codigo_matricula_listado[] = $codigo_matricula;
		}
	else
		{
			consultas(4,0,$codigo_all,'','','',$db_link,'');
			// RECORRER LA CONSULTA, NOMINA DE ALUMNOS.
				$codigo_alumno_listado = array(); $codigo_matricula_listado = array();
				while($row_listado = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
            		{	
						$codigo_alumno_listado[] = $row_listado['codigo_alumno'];
						$codigo_matricula_listado[] = $row_listado['codigo_matricula'];
					}
		}
	// condicionar el ancho y ALTO de cada columna.
		$ancho=array(140,10,5,12); //determina el ancho de las columnas
		$alto=array(5,12); //determina el alto de las columnas
//************************************************************************************************************************
//************************************************************************************************************************
//	CREAR FOR PARA RECORRER EL LISTADO Y ASÍ OBTENER LA BOLETA DE NOTAS.
// Creando el Informe. cuando va al navegador.
	if($crear_archivos == 'no')
	{
			$pdf=new PDF('P','mm','Letter');	// Formato Letter
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
for($listado=0;$listado<count($codigo_alumno_listado);$listado++)
{
	// Creando el Informe. cuando va a la carpeta.
	if($crear_archivos == 'si')
	{
		$pdf=new PDF('P','mm','Letter');	// Formato Letter
		#Establecemos los m�rgenes izquierda, arriba y derecha: 
			$pdf->SetMargins(5, 5, 5);
		#Establecemos el margen inferior: 
			$pdf->SetAutoPageBreak(true,5);
		//T�tulos de las columnas
			$header=array('');
			$pdf->AliasNbPages();
	}
		// Coordenadas de iNICIO.
			$pdf->SetY(40);
			$pdf->SetX(5);
		// variales para la boleta.
			$fill = false; $i=1;  $suma = 0; $aprobado_reprobado = array(); $contar_linea = 0;
// *************************************************************************************************************************
// ejecutar consulta. que proviene de la nomina. SE CREA LA ARRAY() CODIGO_ALUMNO_LISTADO Y CODIGO_MATRICULA_LISTADO.
// *************************************************************************************************************************
// *************************************************************************************************************************
	consultas_alumno(2,0,$codigo_all,$codigo_alumno_listado[$listado],$codigo_matricula_listado[$listado],$print_ann_lectivo,$db_link,'');
// *************************************************************************************************************************
	
// *************************************************************************************************************************
//	INICIA EL WHILE CON RESPECTO AL VALOR DE LA NOMINA ( CODIGO ALUMNO, CODIGO MATRICULA.)
// *************************************************************************************************************************
// MATRIZ NOMBRE DE LOS CAMPOS. unset( $animales[0] ); BORRAR MATRIZ
	$nombre_campos = array('indicador_p_p_1','indicador_p_p_2','indicador_p_p_3','alertas');//,'total_puntos_basica','nota_final','recuperacion');
while($row = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
{
	// variables a utilizar.
		$nombre_completo_alumno = mb_convert_encoding(trim($row['apellido_alumno']),"ISO-8859-1","UTF-8");
		$numero_identificacion_estudiantil = trim($row['codigo_nie']);
		$print_codigo_alumno = $row['codigo_alumno'];
		$print_codigo_matricula = $row['cod_matricula'];
		$codigo_matricula = $row['cod_matricula'];
		$codigo_alumno = $row['codigo_alumno'];
		$nombre_asignatura = mb_convert_encoding(trim($row['n_asignatura']),"ISO-8859-1","UTF-8");
		$foto = trim($row['foto']);
	// imprimir la foto en la boleta
		if ($chkfoto == 'yes'){
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$foto)){$fotos = 'foto_no_disponible.png';}else{$fotos = $foto;}
				$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$fotos;
				$pdf->image($img,197,6,21,27);
        }
    // SALTO DE LINEA.
        if($contar_linea == 36){
			$pdf->AddPage();
			$pdf->SetFont('Times','B',13);	// cambiar de tama�o de letra para el nombre y el nie.
			$pdf->RotatedText(53,27,$nombre_completo_alumno,0);
			$pdf->RotatedText(53,36,$numero_identificacion_estudiantil,0);
			$pdf->SetFont('Times','',10); // I : Italica; U: Normal;
			// dibujar encabezado de la tabla.
			$pdf->SetY(50);
            $pdf->FancyTable($header);
            $contar_linea = 0;
        }
	///////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////		
		// IMPRIME LA PRIMERA ASIGNATURA Y CREA LO NECESARIO.
		if ($i == 1)
		{
			$pdf->AddPage();
			$pdf->SetFont('Times','B',13);	// cambiar de tama�o de letra para el nombre y el nie.
			$pdf->RotatedText(53,27,$nombre_completo_alumno,0);
			$pdf->RotatedText(53,36,$numero_identificacion_estudiantil,0);
			$pdf->SetFont('Times','',10); // I : Italica; U: Normal;
			// dibujar encabezado de la tabla.
			$pdf->SetY(50);
			$pdf->FancyTable($header);
			//
			// Colocar la PROMOCIÓN A 3 para poder matricularlo en el año superior solo para PARVULARIA.
			//
				$codigo_promocion = 3;
				$query_update_matricula = "UPDATE alumno_matricula SET codigo_resultado = '$codigo_promocion' WHERE id_alumno_matricula = '$codigo_matricula' and codigo_alumno = '$codigo_alumno'";
				$result_uddate_matricula = $db_link -> query($query_update_matricula);
			//
			//	FIN DEL PROCESO PARA ASIGNATURA EL VALOR A LA MATRICULA.
			//
        }
            ///////////////////////////////////////////////////////////////////////////////////////////////////
            /////VERIFICAR ENCABEZADO de AREA DE ASIGNATURAS///////////////////////////////////////////////////
            ///////////////////////////////////////////////////////////////////////////////////////////////////		
            /*	"01"	"Básica                                                                     " 0
                "02"	"Formativa                                                                  " 1
                "03"	"Técnica                                                                    " 2
                "04"	"Experiencia y Desarrollo Personal y Social                                 " 3
                "05"	"Experiencia y Desarrollo de la Expresión, Comunicación y Representación    " 4
                "06"	"Experiencia y Desarrollo de la Relación con el Entorno                     " 5
                "07"	"Competencias Ciudadanas                                                    " 6
                "08"	"Complementaria                                                             " 7
                "09"	"Alertas                                                                    " 8
            */                                                                 
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // TABLA CATALOGO_CC_ASIGNATURA.
            /*    1	"01"	"Calificación             "
                2	"02"	"Concepto                 "
                3	"03"	"Indicador                "
            */
            // DAR FORMATO.
                //Colores, ancho de l�nea y fuente en negrita
                $pdf->SetFillColor(200,200,200);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Times','B',13);
                // PARA EL ÁREA EXPERIENCIA Y DESARROLLO PERSONAL Y SOCIAL.
                    if($catalogo_area_asignatura_codigo[3] == trim($row['codigo_area'])){
                        if($catalogo_area_edps == true){
                            $pdf->Cell(203,7,mb_convert_encoding($catalogo_area_asignatura_area[3],"ISO-8859-1","UTF-8"),1,1,'C',true);
                            $catalogo_area_edps = false;
                        }
                    }
                // PARA EL ÁREA Experiencia y Desarrollo de la Expresión, Comunicación y Representación 
                if($catalogo_area_asignatura_codigo[4] == trim($row['codigo_area'])){
                    if($catalogo_area_edecr == true){
                        $pdf->Cell(203,7,mb_convert_encoding($catalogo_area_asignatura_area[4],"ISO-8859-1","UTF-8"),1,1,'C',true);
                        $catalogo_area_edecr = false;
                    }
                }
                // PARA EL ÁREA Experiencia y Desarrollo de la Relación con el Entorno
                if($catalogo_area_asignatura_codigo[5] == trim($row['codigo_area'])){
                    if($catalogo_area_edre == true){
                        $pdf->Cell(203,7,mb_convert_encoding($catalogo_area_asignatura_area[5],"ISO-8859-1","UTF-8"),1,1,'C',true);
                        $catalogo_area_edre = false;
                    }
                }
                // PARA LAS ALERTAS
                if($catalogo_area_asignatura_codigo[8] == trim($row['codigo_area'])){
                    if($catalogo_area_alertas == true){
                        $pdf->Cell(203,7,mb_convert_encoding($catalogo_area_asignatura_area[8],"ISO-8859-1","UTF-8"),1,1,'C',true);
                        $catalogo_area_alertas = false;
                    }
                } 
                //Restauraci�n de colores y fuentes
                $pdf->SetFillColor(224,235,255);
                $pdf->SetTextColor(0);
                $pdf->SetFont('Times','',10);	
			///////////////////////////////////////////////////////////////////////////////////////////////////
			/////NOMBRE DE LA ASIGNATURA Y CAMBIO DE CONCEPTOS.///////////////////////////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////////////////////////////////
					$cellWidth=140;//wrapped cell width
					$cellHeight=5;//normal one-line cell height
					
					//check whether the text is overflowing
					if($pdf->GetStringWidth($nombre_asignatura) < $cellWidth){
						//if not, then do nothing
						$line=1;
					}else{
						//if it is, then calculate the height needed for wrapped cell
						//by splitting the text to fit the cell width
						//then count how many lines are needed for the text to fit the cell
						
						$textLength=strlen($nombre_asignatura);	//total text length
						$errMargin=10;		//cell width error margin, just in case
						$startChar=0;		//character start position for each line
						$maxChar=0;			//maximum character in a line, to be incremented later
						$textArray=array();	//to hold the strings for each line
						$tmpString="";		//to hold the string for a line (temporary)
						
						while($startChar < $textLength){ //loop until end of text
							//loop until maximum character reached
							while( 
							$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
							($startChar+$maxChar) < $textLength ) {
								$maxChar++;
								$tmpString=substr($nombre_asignatura,$startChar,$maxChar);
							}
							//move startChar to next line
							$startChar=$startChar+$maxChar;
							//then add it into the array so we know how many line are needed
							array_push($textArray,$tmpString);
							//reset maxChar and tmpString
							$maxChar=0;
							$tmpString='';
							
						}
						//get number of line
						$line=count($textArray);
					}
				$xPos = $pdf->GetX();	// valor actual de X.
				$yPos = $pdf->GetY();	// Valor actual de Y.
				$pdf->MultiCell($ancho[0],$alto[0],$nombre_asignatura,0,'L',$fill);	//Nombre de la Asignatura.
				$pdf->SetXY($xPos + $ancho[0], $yPos);
			///////////////////////////////////////////////////////////////////////////////////////////////////
				// INDICADOR
			///////////////////////////////////////////////////////////////////////////////////////////////////
				// IMPRIMIR NOTA TRIMESTRE 1, 2, 3 Y CALCULAR CONCEPTO.
				for($ii=0;$ii<count($nombre_campos);$ii++){
					// Extraer el valor.
                    $indicador_ = $row[$nombre_campos[$ii]];
                    $pdf->Cell($ancho[1],($line * $alto[0]),$indicador_,'R',0,'C',$fill);
					// cambiar COLOR.
						/*if($indicador_ == "S" || $indicador_ == "P"){
							$pdf->Cell($ancho[1],($line * $alto[0]),$indicador_,'R',0,'C',$fill);
						}else{
							$pdf->SetFont('Arial','B',9);
							$pdf->SetTextColor(255, 25, 0);
							$pdf->Cell($ancho[1],($line * $alto[0]),$indicador_,'R',0,'C',$fill);
							$pdf->SetFont('');
							$pdf->SetTextColor(0,0,0);
						}*/
				}	// FOR
				// VALORES RESTANTES. total de puntos, nota_final, recuperacion.
					if($row['indicador_final'] != 0){
						$pdf->Cell($ancho[1],($line * $alto[0]),trim($row['indicador_final']),0,0,'C',$fill);
					}else{
						$pdf->Cell($ancho[1],($line * $alto[0]),trim($row['indicador_final']),0,0,'C',$fill);}
				// SALTO DE LINEA Y CAMBIO DE COLOR DE RELLENO.
					$pdf->Ln();
						$fill=!$fill;
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			 if ($i == $total_asignaturas)
			 {
				$pdf->Cell(203,0,'','T');
				$pdf->SetFont('','',10);
				$pdf->Ln();
				// evaluar etiqueta leyenda.
				if($print_codigo_bachillerato == '13' || $print_codigo_bachillerato == '14' || $print_codigo_bachillerato == "16")
				{
					$leyenda = "INDICADORES.";
                }
				$pdf->Cell(120,$alto[0],'INDICADORES '.mb_convert_encoding($leyenda,"ISO-8859-1","UTF-8"),0,1,'L');
				$pdf->Cell(160,$alto[0],'.',0,1,'L');
                $pdf->Cell(40,$alto[0],'',0,1,'L');

               	//Firma Director.
                    $nombre_director = cambiar_de_del($_SESSION['nombre_director']);
                    if($firma == 'yes'){
                        $img_firma = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
                        }
                    if($sello == 'yes'){
                        $img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
                    }
                $pdf->SetY(-20);				//Posici�n: a 1,5 cm del final
                $pdf->Line(10,245,80,245);		//Crear una l�nea de la primera firma
                $pdf->Line(120,245,190,245);	//Crear una l�nea de la segunda firma.
                $pdf->RotatedText(50,255,$registro_docente,0);		// NOMBRE DEL DOCENTE.
                if(isset($img_firma)){$pdf->Image($img_firma,120,225,70,15);}						// IMAGEN FIRMA
                if(isset($img_firma)){$pdf->Image($img_sello,80,225,30,30);}						// IMAGEN SELLO
                $pdf->RotatedText(130,250,$nombre_director,0);	    // Nombre Director
                $pdf->RotatedText(140,255,'Director(a)',0);			// ETIQUETA DIRECTOR.
            }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
              $i++;			// acumulador para el numero de asignaturas
              $contar_linea++;  // acumular el número de línea.
} // BUCLE QUE RECORRE EL ESTUDIANTE SELECCIONADO A PARTIR DE LA NÓMINA.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//	REESTABLACER VARIABLES O MATRICES UTILIZADAS.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$catalogo_area_basica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_formativa = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_tecnica = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_edps = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_edecr = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_edre = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_complementaria = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_cc = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	$catalogo_area_alertas = true;		// Variable lógica para colocar el SEPRADOR DE ASIGNATURAS.
	// Salida del pdf.
	if($crear_archivos == "si"){
		// Verificar si Existe el directorio archivos.
			$codigo_modalidad = $print_codigo_bachillerato;
			$nombre_ann_lectivo = $print_ann_lectivo;
		// Tipo de Carpeta a Grabar.
			$codigo_destino = 4;
			$nuevo_grado = replace_3(trim($print_grado));
			CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
		// verificar si existe el grado y sección.
		if(!file_exists($DestinoArchivo . $nuevo_grado . ' ' . trim($print_seccion)))
		{
			// Para Nóminas. Escolanadamente.
				mkdir ($DestinoArchivo . $nuevo_grado . ' ' . trim($print_seccion));
				chmod($DestinoArchivo . $nuevo_grado . ' ' . trim($print_seccion),07777);
		}
			$NuevoDestinoArchivo = $DestinoArchivo . $nuevo_grado . ' ' . trim($print_seccion) . "/";
		
			$modo = 'F'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
			$print_nombre = $NuevoDestinoArchivo . trim($nombre_completo_alumno) . '.pdf';
			
			//$print_nombre = $path_root . '/registro_academico/temp/' . trim($nombre_completo_alumno) . ' ' . trim($print_grado) . ' ' . trim($print_seccion) . '.pdf';
			$pdf->Output($print_nombre,$modo);
	}			
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}	// TERMINA EL FOR QUE RECORRER LA NOMINA DE ESTUDIANES.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($crear_archivos == "no"){
// Construir el nombre del archivo.
	$nombre_archivo = $print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '.pdf';
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