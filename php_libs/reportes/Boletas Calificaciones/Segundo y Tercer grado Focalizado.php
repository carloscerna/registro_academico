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
	$conteo_reprobadas = [];
	$conteo_aprobadas = [];
// Establecer formato para la fecha.
	date_default_timezone_set('America/El_Salvador');
	setlocale(LC_TIME,'es_SV');
// CREAR MATRIZ DE MESES Y FECH.
	$meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
//Crear una línea. Fecha con getdate();
	$hoy = getdate();
	$NombreDia = $hoy["wday"];  // dia de la semana Nombre.
	$dia = $hoy["mday"];    // dia de la semana
	$mes = $hoy["mon"];     // mes
	$año = $hoy["year"];    // año
	$total_de_dias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, $año);
	$NombreMes = $meses[(int)$mes - 1];
// definimos 2 array uno para los nombre de los dias y otro para los nombres de los meses
	$nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
	$nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
	$fecha = convertirTexto("Santa Ana, $nombresDias[$NombreDia] $dia de $nombresMeses[$mes] de $año");
	setlocale(LC_MONETARY,"es_ES");
// variable de la conexi�n dbf.
    $db_link = $dblink;
// buscar los datos de la sección y extraer el codigo del nivel.
	$codigo_nivel = substr($codigo_all,0,2);
		consultas(13,0,$codigo_all,'','','',$db_link,''); // valor 13 en consultas.
//  imprimir datos del grado en general. extrar la información de la cosulta del archivo consultas.php
	global $nombreNivel, $nombreGrado, $nombreSeccion, $nombreTurno, $nombreAñolectivo, $print_periodo;
// CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
	consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
		global $result_docente, $print_nombre_docente; 
////////////////////////////////////////////////////////////////////
//////// crear matriz para la tabla CATALOGO_AREA_ASIGNATURA.
//////////////////////////////////////////////////////////////////
	$catalogo_area_asignatura_codigo = [];	// matriz para los diferentes código y descripción.
	$catalogo_area_asignatura_area = [];
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
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
  consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
	$total_asignaturas = 0;	
class PDF extends FPDF
{
//Cabecera de p�gina
function Header()
{
	global $print_nombre_docente, $nombreNivel, $nombreGrado, $nombreSeccion, $nombreAñoLectivo, $nombreTurno;
	$TamañoTexto = [10,11,12,13,14,15,16];
	// decoración.
    $this->SetLineWidth(.4);				// GROSOR.
	$this->SetDrawColor(10,29,247);			// COLOR DE LA LÍNEA.
	$this->RoundedRect(250, 5, 20, 25, 3.5, '1234','');		// RECTANGULO de la foto.
	$this->Line(20,20,250,20);								// LINEA EN VERTICAL
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,5,5,15,20);
    //Arial bold 15
    $this->SetFont('PoetsenOne','',$TamañoTexto[4]);
    //Título - Nuevo Encabezado incluir todo lo que sea necesario.
	$this->SetXY(20,5);
    	$this->Cell(200,5,convertirtexto($_SESSION['institucion']),0,1,'L');
	$this->SetXY(20,10);
    	$this->Cell(200,5,convertirtexto("Boleta de Calificaciones - Año Lectivo $nombreAñoLectivo"),0,1,'L');
    // Nombre Nivel.
    $this->SetXY(20,15);
    $this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5,"Nivel: ");
    $this->SetFont('Comic','U',$TamañoTexto[0]);
        $this->Write(5,$nombreNivel);
    // Nombre Nivel.
    //$this->SetXY(25,20);
    $this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5," - Grado: ");
    $this->SetFont('Comic','U',$TamañoTexto[0]);
        $this->Write(5,$nombreGrado);
    // Nombre Sección.
    //$this->SetXY(120,20);
    $this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5,convertirTexto(" - Sección: "));
    $this->SetFont('Comic','U',11);
        $this->Write(5,"'$nombreSeccion'");
    // Nombre turno.
    //$this->SetXY(160,20);
    $this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5,convertirTexto(" - Turno: "));
    $this->SetFont('Comic','U',$TamañoTexto[0]);
        $this->Write(5,$nombreTurno);
    // Detalle escala de colores, Indicadores.
    $this->SetXY(20,20);
	$this->SetLineWidth(.3);				// GROSOR.
	$this->SetDrawColor(0,0,0);			// COLOR DE LA LÍNEA. rgb(0,0,0);
    $this->SetFont('Arial','B',$TamañoTexto[0]);	// sobresaliente
        $this->Write(5,convertirTexto("Detalle de colores Indicadores: Sobresaliente:  "));
	$this->SetFillColor(167,245,174); // color rellen rgb(167,245,174);
    $this->SetFont('Comic','',$TamañoTexto[0]);
        $this->Cell(5,5,'',1,0,"L",true);
	$this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5,convertirTexto("Satisfactorio:  "));
	$this->SetFillColor(249,202,242); // color rellen rgb(249,202,242);
    $this->SetFont('Comic','',$TamañoTexto[0]);
        $this->Cell(5,5,'',1,0,"L",true);
	$this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5,convertirTexto("En proceso:  "));
	$this->SetFillColor(235,242,162); // color rellen rgb(235,242,162);
    $this->SetFont('Comic','',$TamañoTexto[0]);
        $this->Cell(5,5,'',1,0,"L",true);
	$this->SetFont('Arial','B',$TamañoTexto[0]);
        $this->Write(5,convertirTexto("No lo hace:  "));
	$this->SetFillColor(204,209,211); // color rellen rgb(204,209,211);
    $this->SetFont('Comic','',$TamañoTexto[0]);
        $this->Cell(5,5,'',1,0,"L",true);
}
//Pie de p�gina
function Footer()
{
	// Variables.
	global $fecha;
	//Posición: a 1,5 cm del final
	$this->SetY(-10);
	//Arial italic 8
	$this->SetFont('Arial','I',8);
	//Crear ubna línea
	$this->Line(5,270,210,270);
	//Número de página y fecha.
	$this->Cell(0,10,convertirTexto('Página ').$this->PageNo().'/{nb}       '.$fecha,0,0,'R');
}
//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de l�nea y fuente en negrita
		$this->SetFillColor(229,229,229);	/// rgb(229,229,229);
		$this->SetTextColor(0,0,0);
		$this->SetDrawColor(0,0,0);		// rgb(128,0,0);
		$this->SetLineWidth(.3);
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// VALOR3ES Y ENCABEZADO PARA TODOS LOS GRADOS
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ARRAY PARA LAS DIFERENTES ETIQUETAS.
		$etiquetaComponente = ['COMPONENTE DEL PLAN DE ESTUDIO'];
		$etiquetaAreaTrimestre = ["Area","TRIMESTRE"];
		$etiquetaNumeroTrimestre = ["1","2","3"];
		$etiquetaNombreArea = ["Lenguaje","Matemática","Ciencia y Tecnología","Estudios Sociales","Educación Física","Educación Artística","Inglés"];
	// LINES O RECTÁNGULOS.
		$this->RoundedRect(5, 30, 270, 10, 0.5, '');	// primer cuadro.
		$this->RoundedRect(5, 30, 165, 10, 0.5, '');	// para el nombre de la asignatura
	// Etiquete Componente del plan de estudio.
		$this->SetXY(10,33); $valorEtiquetaX = 0;
		$this->SetFont('Alte','',14);
			$this->Write(5,$etiquetaComponente[0]);
	// Etiquete Componente del plan de estudio.
		$this->SetXY(150,30); $valorEtiquetaX = 0;
		$this->SetFont('Alte','',8);
			$this->Write(5,$etiquetaAreaTrimestre[0]);
	// Etiquete Componente del plan de estudio.
		$this->SetXY(150,35); $valorEtiquetaX = 0;
		$this->SetFont('Alte','',8);
			$this->Write(5,$etiquetaAreaTrimestre[1]);
	// Imprimir Etiquete NOmbre Area.
		$this->SetXY(170,30); $valorEtiquetaX = 0;
		$this->SetFont('Times','',7);
		for($j=0;$j<=count($etiquetaNombreArea) -1;$j++){
			// CAMBIAR EL COLOR DE LAS AREAS DE CADA COMPONENTE DE ESTUDIO.
			switch ($j) {
				case 0:	// Lenguaje
					$this->SetFillColor(231,221,255);
					break;
				case 1:	// Matemática.
					$this->SetFillColor(191,214,65);
					break;
				case 2:	// Ciencia y Tecnología.
					$this->SetFillColor(125,218,88);
					break;
				case 3:	// Estudios Sociales
					$this->SetFillColor(226,234,244);
					break;
				case 4:
					$this->SetFillColor(152,245,249);
					break;
				case 5:
					$this->SetFillColor(255,236,161);
					break;
				case 6:
					$this->SetFillColor(254,153,0);
					break;
				default:
					$this->SetFillColor(255,255,255);
					break;
			}
        	    $this->Cell(15,5,convertirTexto($etiquetaNombreArea[$j]),0,0,"C",true);
			$valorEtiquetaX = $this->GetX();
			$this->SetXY($valorEtiquetaX,30);
		}
	// segunda fila de lineas por el numero de componentes del area.
		$this->SetXY(170,35);
		for($j=0;$j<=count($etiquetaNombreArea) -1;$j++){
			$this->Cell(5,5,'1',1,0,"C",false);
			$this->Cell(5,5,'2',1,0,"C",false);
			$this->Cell(5,5,'3',1,0,"C",false);
		}
	///////////////////////////////////////////////////////////////////////////////////////
    //Restauraci�n de colores y fuentes
		$this->SetFillColor(224,235,255);	// rgb(224,235,255);
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
	$TamañoTexto = [10,11,12,13,14,15,16];
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
		$ancho=[165,10,5,12]; //determina el ancho de las columnas
		$alto=[3.5,12]; //determina el alto de las columnas
//************************************************************************************************************************
//************************************************************************************************************************
//	CREAR FOR PARA RECORRER EL LISTADO Y ASÍ OBTENER LA BOLETA DE NOTAS.
// Creando el Informe. cuando va al navegador.
	if($crear_archivos == 'no')
	{
			$pdf=new PDF('L','mm','Letter');	// Formato Letter
		#Establecemos los m�rgenes izquierda, arriba y derecha: 
			$pdf->SetMargins(5, 5, 5);
		#Establecemos el margen inferior: 
			$pdf->SetAutoPageBreak(true,5);
		// Tipos de fuente.
			$pdf->AddFont('Comic','','comic.php');
			$pdf->AddFont('Alte','','AlteHaasGroteskRegular.php');
			$pdf->AddFont('Alte','B','AlteHaasGroteskBold.php');
			$pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
		//T�tulos de las columnas
			$header=[''];
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
		$pdf=new PDF('L','mm','Letter');	// Formato Letter
		#Establecemos los m�rgenes izquierda, arriba y derecha: 
			$pdf->SetMargins(5, 5, 5);
		#Establecemos el margen inferior: 
			$pdf->SetAutoPageBreak(true,5);
		// Tipos de fuente.
			$pdf->AddFont('Comic','','comic.php');
			$pdf->AddFont('Alte','','AlteHaasGroteskRegular.php');
			$pdf->AddFont('Alte','B','AlteHaasGroteskBold.php');
			$pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
		//T�tulos de las columnas
			$header=[''];
			$pdf->AliasNbPages();
	}
		// Coordenadas de iNICIO.
			$pdf->SetY(40);
			$pdf->SetX(5);
		// variales para la boleta.
			$fill = false; $i=1;  $suma = 0; $aprobado_reprobado = array(); $contar_linea = 0; $ContaLineaY = 0;
// *************************************************************************************************************************
// ejecutar consulta. que proviene de la nomina. SE CREA LA ARRAY() CODIGO_ALUMNO_LISTADO Y CODIGO_MATRICULA_LISTADO.
// *************************************************************************************************************************
// *************************************************************************************************************************
	consultas_alumno(2,0,$codigo_all,$codigo_alumno_listado[$listado],$codigo_matricula_listado[$listado],$nombreAñoLectivo,$db_link,'');
// *************************************************************************************************************************
// *************************************************************************************************************************
//	INICIA EL WHILE CON RESPECTO AL VALOR DE LA NOMINA ( CODIGO ALUMNO, CODIGO MATRICULA.)
// *************************************************************************************************************************
// MATRIZ NOMBRE DE LOS CAMPOS. unset( $animales[0] ); BORRAR MATRIZ
	$nombre_campos = ['indicador_p_p_1','indicador_p_p_2','indicador_p_p_3'];//,'total_puntos_basica','nota_final','recuperacion');
	$codigoAreaComponente = ['15','16','17','18','19','20'];
	while($row = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
	{
		// variables a utilizar.
			$nombre_completo_alumno = mb_convert_encoding(trim($row['apellido_alumno']),"ISO-8859-1","UTF-8");
			$numero_identificacion_estudiantil = trim($row['codigo_nie']);
			$print_codigo_alumno = $row['codigo_alumno'];
			$print_codigo_matricula = $row['cod_matricula'];
			$codigo_matricula = $row['cod_matricula'];
			$codigo_alumno = $row['codigo_alumno'];
			$codigoArea = trim($row['codigo_area']);
			$nombre_asignatura = mb_convert_encoding(trim($row['n_asignatura']),"ISO-8859-1","UTF-8");
			$NombreAsignatura = explode("-",$nombre_asignatura);
			$NombreAsignaturas = trim($NombreAsignatura[1]);
			$foto = trim($row['foto']);
		// imprimir la foto en la boleta
			if ($chkfoto == 'yes'){
				if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$foto)){$fotos = 'foto_no_disponible.png';}else{$fotos = $foto;}
					$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$fotos;
					$pdf->image($img,197,6,21,27);
			}
		///////////////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////////////		
			// IMPRIME LA PRIMERA ASIGNATURA Y CREA LO NECESARIO.
			if ($i == 1)
			{	
				$pdf->AddPage();
				// Nombre Estudiante.
				$pdf->SetXY(20,25);
				$pdf->SetFont('Arial','B',$TamañoTexto[0]);
					$pdf->Write(5,"Estudiante: ");
				$pdf->SetFont('Comic','U',$TamañoTexto[0]);
					$pdf->Write(5,$nombre_completo_alumno);
				// NIE.
				$pdf->SetFont('Arial','B',$TamañoTexto[0]);
					$pdf->Write(5," - NIE: ");
				$pdf->SetFont('Comic','U',$TamañoTexto[0]);
					$pdf->Write(5,$numero_identificacion_estudiantil);
				$pdf->SetFont('Times','',10); // I : Italica; U: Normal;
				// dibujar encabezado de la tabla.
				$pdf->SetY(30);
				$pdf->FancyTable($header);
				$pdf->SetY(40);
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
				/////NOMBRE DE LA ASIGNATURA Y CAMBIO DE CONCEPTOS.///////////////////////////////////////////////////////////////////////////////////////////
				///////////////////////////////////////////////////////////////////////////////////////////////////
					$cellWidth=165;//wrapped cell width
					$cellHeight=4;//normal one-line cell height
					//check whether the text is overflowing
					if($pdf->GetStringWidth($NombreAsignaturas) < $cellWidth){
						//if not, then do nothing
						$line=1;
					}else{
						//if it is, then calculate the height needed for wrapped cell
						//by splitting the text to fit the cell width
						//then count how many lines are needed for the text to fit the cell
						$textLength=strlen($NombreAsignaturas);	//total text length
						$errMargin=10;		//cell width error margin, just in case
						$startChar=0;		//character start position for each line
						$maxChar=0;			//maximum character in a line, to be incremented later
						$textArray=[];	//to hold the strings for each line
						$tmpString="";		//to hold the string for a line (temporary)
						//					
						while($startChar < $textLength){ //loop until end of text
							//loop until maximum character reached
							while( 
							$pdf->GetStringWidth( $tmpString ) < ($cellWidth-$errMargin) &&
							($startChar+$maxChar) < $textLength ) {
								$maxChar++;
								$tmpString=substr($NombreAsignaturas,$startChar,$maxChar);
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
					$pdf->SetFillColor(221,233,254); // color rellen rgb(221,233,254);
					$pdf->SetFont('Times','',9); // I : Italica; U: Normal;
					$xPos = $pdf->GetX();	// valor actual de X.
					$yPos = $pdf->GetY();	// Valor actual de Y.
					$pdf->MultiCell($ancho[0],$alto[0],$NombreAsignaturas,1,'L',$fill);	//Nombre de la Asignatura.
						// MOVER EL VALOR DE X PARA COLOCAR EN DIFERENTE POSICIÓN DEPENDIENTE DEL AREA,
						// LENGUAJE, MATEMATICA, CIENCIA Y TECNOLOGIA, ESTUDIOS SOCIALES, EDUCACIÓN FISICA, EDUCACIÓN ARTÍSTICA, INGLES.
						$AreaComponenteX = 0;
						switch ($codigoArea) {
							case "15":
								$pdf->SetXY($xPos + $ancho[0], $yPos);	// Posición del Indicador.
								break;
							case "16":
								$pdf->SetXY($xPos + $ancho[0] + 15,$yPos);
								break;
							case "17":
								$pdf->SetXY($xPos + $ancho[0] + 30,$yPos);
								break;
							case "18":
								$pdf->SetXY($xPos + $ancho[0] + 45,$yPos);
								break;
							case "19":
								$pdf->SetXY($xPos + $ancho[0] + 60,$yPos);
								break;
							case "20":
								$pdf->SetXY($xPos + $ancho[0] + 75,$yPos);
								break;
							case "21":
								$pdf->SetXY($xPos + $ancho[0] + 90,$yPos);
								break;
						}

				///////////////////////////////////////////////////////////////////////////////////////////////////
					// INDICADOR
				///////////////////////////////////////////////////////////////////////////////////////////////////
					for($ii=0;$ii<count($nombre_campos);$ii++){
						// Extraer el valor.
						$indicador_ = convertirTexto($row[$nombre_campos[$ii]]);	// en el caso puede ser una letra o palabra.
						// cambiar COLOR.
						switch ($indicador_) {
							case 'Sobresaliente':
							$pdf->SetFillColor(167,245,174); // color rellen rgb(167,245,174);
								break;
							case "Satisfactorio":
								$pdf->SetFillColor(249,202,242); // color rellen rgb(249,202,242);
								break;
							case "En proceso":
								$pdf->SetFillColor(235,242,162); // color rellen rgb(235,242,162);
								break;
							case "No lo hace":
								$pdf->SetFillColor(204,209,211); // color rellen rgb(204,209,211);
								break;
							default:
								$pdf->SetFillColor(255,255,255); // color rellen rgb(255,255,255);
								break;
						}
						//	imprimri el color correspondiente dependiendo de la evaluación.
							//$pdf->Cell($ancho[1],($line * $alto[0]),$indicador_ .$yPos,'LBR',0,'L',$fill);
							// Imprimir color del Indicador.
								$pdf->Cell($ancho[2],$line * $alto[0],'',1,0,'L',true);
						// reset color de relleno.
					}	// FOR
						// salto de pagina.
						if($yPos >= 190){
							$pdf->AddPage();
							$pdf->SetFont('Times','',10); // I : Italica; U: Normal;
							// dibujar encabezado de la tabla.
							$pdf->SetY(30);
							$pdf->FancyTable($header);
						}
					// VALORES RESTANTES. total de puntos, nota_final, recuperacion.
					/*
						if($row['indicador_final'] != 0){
							$pdf->Cell($ancho[1],($line * $alto[0]),trim($row['indicador_final']),0,0,'C',$fill);
						}else{
							$pdf->Cell($ancho[1],($line * $alto[0]),trim($row['indicador_final']),0,0,'C',$fill);}
						*/
					// SALTO DE LINEA Y CAMBIO DE COLOR DE RELLENO.
						$pdf->Ln();
							$fill=!$fill;
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$i++;			// acumulador para el numero de asignaturas
				$contar_linea++;  // acumular el número de línea.
	} // BUCLE QUE RECORRE EL ESTUDIANTE SELECCIONADO A PARTIR DE LA NÓMINA.
		// INFORMACION DEL DIRECTOR, ESCUELA Y DOCENTE.
		$pdf->SetY(-15);
		$pdf->SetX(5);
		$pdf->SetFont("Arial","",7);
		//Firma Director.
			$img_firma = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];;
			$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];;
			$nombreDirector =  convertirTexto($_SESSION['nombre_director']);
		// PRINT A PANTALLA.
			$pdf->Cell(60,4,$nombreDirector,0,0,"L",false);		// NOMBRE DEL Director.
			$pdf->Cell(60,4,$print_nombre_docente,0,1,"L",false);		// NOMBRE DEL Docente.
			if(isset($img_firma)){$pdf->Image($img_firma,20,190,15,15);}						// IMAGEN FIRMA
			if(isset($img_sello)){$pdf->Image($img_sello,40,190,15,15);}						// IMAGEN SELLO

			$pdf->Cell(60,4,'Director(a)',0,0,"L",false);			// ETIQUETA DIRECTOR.
			$pdf->Cell(60,4,'Docente',0,0,"L",false);			// ETIQUETA Docente.
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
	$nombre_archivo = $nombreNivel.' '.$nombreGrado.' '.$nombreSeccion.'-'.$nombreAñolectivo . '.pdf';
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