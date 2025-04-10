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
	$mensajeError = "Registros Encontrados";
	$contenidoOK = "";
// Calcular el tiempo de ejeución del script.	
	$tiempo_inicio = microtime(true); //true es para que sea calculado en segundos
// variables y consulta a la tabla.
  $codigo_all = $_REQUEST["todos"];		// codigro - 
  $codigoBachGradoAnnLectivo = $_REQUEST["todos"];		// codigro - 
  $codigo_alumno = $_REQUEST["txtidalumno"];
  $codigo_matricula = $_REQUEST["txtcodmatricula"];
  $codigo_ann_lectivo = "";
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
	$registro_docente = "";//$_SESSION['nombre_personal']; //
	$periodo_trimestre = "TRIMESTRE";
	$conteo_reprobadas = array();
	$conteo_aprobadas = array();
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
////////////////////////////////////////////////////////////////////
//////// DATOS DEL ENCABEZADO O CODIOS Y NOMBRES DE MODALIDAD, GRADO, SECCION Y TURNO.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta. en consultas.php numeral 18.
		$query =  "SELECT  
				a.id_alumno AS codigo_alumno, 
				am.id_alumno_matricula AS codigo_matricula,
				am.codigo_ann_lectivo as codigo_ann_lectivo, 
				ann.nombre AS nombre_ann_lectivo,  
				am.codigo_grado, 
				gan.nombre AS nombre_grado,
				am.codigo_turno, 
				tur.nombre AS nombre_turno,
				am.codigo_seccion, 
				sec.nombre AS nombre_seccion,
				bach.codigo AS codigo_bachillerato, 
				bach.nombre AS nombre_bachillerato,
				(CONCAT(TRIM(bach.nombre), ' ', TRIM(gan.nombre), ' ', TRIM(sec.nombre))) AS descripcion_completa
			FROM alumno a
			INNER JOIN alumno_encargado ae 
				ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
			INNER JOIN alumno_matricula am 
				ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
			INNER JOIN bachillerato_ciclo bach 
				ON bach.codigo = am.codigo_bach_o_ciclo
			INNER JOIN grado_ano gan 
				ON gan.codigo = am.codigo_grado
			INNER JOIN seccion sec 
				ON sec.codigo = am.codigo_seccion
			INNER JOIN ann_lectivo ann 
				ON ann.codigo = am.codigo_ann_lectivo
			INNER JOIN turno tur 
				ON tur.codigo = am.codigo_turno
    		WHERE TRIM(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = :codigo_bachillerato
				LIMIT 1";
			$stmt = $db_link->prepare($query);
				$stmt->bindParam(':codigo_bachillerato', $codigo_all);
				$stmt->execute();
			// recorrer la consulta para extraer valores
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					// Extraer los campos necesarios
					$nivel_educacion = $row['descripcion_completa'];
					$nombre_ann_lectivo = $row['nombre_ann_lectivo'];
					$nombre_grado = $row['nombre_grado'];
					$nombre_seccion = $row['nombre_seccion'];
					$nombre_bachillerato = $row['nombre_bachillerato'];
					$codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);
					$codigo_bachillerato = $row['codigo_bachillerato'];
				}

// buscar la consulta y la ejecuta. en consultas.php numeral 18.
	$codigo_bach_grado_ann = substr($codigoBachGradoAnnLectivo,0,2) . substr($codigoBachGradoAnnLectivo,2,2) . substr($codigoBachGradoAnnLectivo,6,2);
	consultas(19,0,$codigo_bach_grado_ann,'','','',$db_link,'');
	while($row = $result_nombre_asignatura -> fetch(PDO::FETCH_BOTH))
		{
			$codigo_servicio_educativo = $row['codigo_servicio_educativo'];
				break;
		}
// ENCARGADO DE GRADO, NOMBRE DEL DOCENTE.
	$query_encargado = "SELECT eg.id_encargado_grado, eg.encargado, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, 
		eg.codigo_docente, bach.nombre, gann.nombre, sec.nombre, ann.nombre, tur.nombre
		FROM encargado_grado eg 
		INNER JOIN personal p ON eg.codigo_docente = p.id_personal 
		INNER JOIN bachillerato_ciclo bach ON eg.codigo_bachillerato = bach.codigo 
		INNER JOIN ann_lectivo ann ON eg.codigo_ann_lectivo = ann.codigo 
		INNER JOIN grado_ano gann ON eg.codigo_grado = gann.codigo 
		INNER JOIN seccion sec ON eg.codigo_seccion = sec.codigo 
		INNER JOIN turno tur ON eg.codigo_turno = tur.codigo
			WHERE btrim(bach.codigo || gann.codigo || sec.codigo || ann.codigo || tur.codigo) = '$codigo_all' and eg.encargado = 't' ORDER BY p.nombres";
				$result_encargado = $db_link -> query($query_encargado);
//  Nombre del Encargado.
	$nombre_encargado = '';
	while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
	{
			$nombre_encargado = convertirtexto(trim($rows_encargado['nombre_docente']));
			$codigo_docente = trim($rows_encargado['codigo_docente']);
			
	}
// buscar la consulta y la ejecuta.
  consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
	$total_asignaturas = 0;	
        while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
            {
				$total_asignaturas = (trim($row['total_asignaturas']));
            }
class PDF extends FPDF
{
//Cabecera de p�gina
function Header()
{
	// variables globales.
	global $nivel_educacion, $print_codigo_grado, $print_seccion, $print_grado_media, $nombre_ann_lectivo, $codigo_bachillerato, $print_grado;
	$nombre_institucion = convertirtexto($_SESSION['institucion']);
    // Ancho de la linea y color.
    $this->SetLineWidth(.7);				// GROSOR.
	$this->SetDrawColor(10,29,247);			// COLOR DE LA LÍNEA.
	$this->SetFont('Times','B',13);			// TAMAÑO DE FUENTE 14. NEGRITA.
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];		//Logo
	$boleta_etiqueta = convertirtexto('Boleta de Calificación - ' . ' Año Lectivo ' . $nombre_ann_lectivo);		// etiqueta Boleta de Calificación
	// Título principal.
			$titulo_principal = convertirtexto($nivel_educacion);
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
	global $registro_docente, $firma, $sello, $print_codigo_alumno, $print_codigo_matricula, $codigo_bachillerato, $meses, $dia, $mes, $año, $nombre_encargado;
	//Firma Director.
		$nombre_director = cambiar_de_del($_SESSION['nombre_director']);
    if($firma == 'yes'){
		$img_firma = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_firma'];
    	}
    if($sello == 'yes'){
		$img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['imagen_sello'];
		//$img_sello_registro = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/sello_registro_cerz.jpg';
    }	
    $this->SetFont('Arial','I',8);	    //Arial italic 8    
	// PRINT A PANTALLA.
		// NOCTURNA BASÍCA Y MEDIA.
		$this->SetY(-20);				//Posici�n: a 1,5 cm del final
		$this->Line(10,245,80,245);		//Crear una l�nea de la primera firma
		$this->Line(120,245,190,245);	//Crear una l�nea de la segunda firma.
		$this->Line(5,265,203,265);		//Crear una l�nea FINAL.
		if($nombre_encargado != ""){
			$this->RotatedText(10,250,$nombre_encargado,0);		// NOMBRE DEL DOCENTE, Sub-director o Encargado de Registro Académico.
			$this->RotatedText(10,255,convertirtexto('Docente encargado de la sección'),0);			// ETIQUETA DIRECTOR.
			
		}else{

			$this->RotatedText(10,250,$registro_docente,0);		// NOMBRE DEL DOCENTE, Sub-director o Encargado de Registro Académico.
			$this->RotatedText(10,255,convertirtexto('Encargado Registro Académico'),0);			// ETIQUETA DIRECTOR.
		}

		if(isset($img_firma)){$this->Image($img_firma,120,225,70,15);}						// IMAGEN FIRMA
		if(isset($img_firma)){$this->Image($img_sello,165,225,30,30);}						// IMAGEN SELLO
		//if(isset($img_firma)){$this->Image($img_sello_registro,85,225,32,32);}						// IMAGEN SELLO
    	$this->RotatedText(130,250,$nombre_director,0);	    // Nombre Director
		$this->RotatedText(140,255,'Director(a)',0);			// ETIQUETA DIRECTOR.
    //N�mero de p�gina y fecha
    $this->SetY(-15);
    $this->SetX(10);
    $fecha = date("l, F jS Y ");
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}' .' - ' . $fecha .' - ' . 'Id_a: ' . $print_codigo_alumno . ' Id_m: ' . $print_codigo_matricula,0,0,'C');
}
//Tabla coloreada
function FancyTable($header)
{
  global $codigo_bachillerato;
    //Colores, ancho de l�nea y fuente en negrita
		$this->SetFillColor(229,229,229);
		$this->SetTextColor(0,0,0);
		$this->SetDrawColor(128,0,0);
		$this->SetLineWidth(.3);
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// VALOR3ES Y ENCABEZADO PARA TODOS LOS GRADOS
	////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ARRAY PARA LAS DIFERENTES ETIQUETAS.
		$etiquetas_boleta_de_notas = array('ÁREA - A S I G N A T U R A S','M O D U L O S','RESULTADO FINAL','PERÍODOS','TRIMESTRE');
		$etiqueta_resultado_final = array('T. P.','P. P.','REPO.','N. F.');
		$etiqueta_numeros = array("1","2","3","4","5",);
		$etiqueta_resultado = array("R");
	// encabezado de la boleta.
	// LINES O RECTÁNGULOS.
		$this->RoundedRect(5, 40, 203, 10, 0.5, '');	// primer cuadro.
		$this->RoundedRect(5, 40, 80, 10, 0.5, '');		// para el nombre de la asignatura
    if($codigo_bachillerato >= '01' and  $codigo_bachillerato <= '05')
    {
		$n_label = 6; $n_etiqueta = 4;}
	else if($codigo_bachillerato >= '06' and  $codigo_bachillerato <= '09')
	{
		$n_label = 8; $n_etiqueta = 3;}
	else if($codigo_bachillerato == '20' || $codigo_bachillerato == '13')
	{
		$n_label = 8; $n_etiqueta = 3;
	}else{
		$n_label = 8; $n_etiqueta = 4;}
	// medidas para NOCTURNA POR EL N-º DE MODULOS.
	// crear las columnas de TRIMESTRE, PERIODO O MODULOS.
		$x = 85; $n_e = 0; $n_r = 0; $n_f = 0;
		$ancho_t = 10; $ancho_r = 5; $ancho_f = 12;
		for($j=1;$j<=14;$j++){
			if($j<=10){
				if(($j % 2) == 1){
					$this->RoundedRect($x, 45, $ancho_t, 5, 0.5, '');
					if($n_label >= $j){
						$this->RotatedText($x+4,49,$etiqueta_numeros[$n_e],0);
					}
					$x = $x + $ancho_t;
					$n_e++;
				}
				if(($j % 2) == 1){
					$this->SetFont('Times','B',10);
					$this->RoundedRect($x, 45, $ancho_r, 5, 0.5, '');
					if($n_label >= $j){
						$this->RotatedText($x+1,49,$etiqueta_resultado[0],0);
					}
					$x = $x + $ancho_r;
					$this->SetFont('Times','',10);
				}
			}

			if($j>=11){
				$this->RoundedRect($x, 45, $ancho_f, 5, 0.5, '');
				$this->RotatedText($x+2,49,$etiqueta_resultado_final[$n_f],0);
				$x = $x + $ancho_f;
				$n_f++;
			}
		}
	///////////////////////////////////////////////////////////////////////////////////////
	// LABEL. resultado final.
		$this->RoundedRect(85, 40, 75, 10, 0.5, '');	// para los periodos, trimestres o modulos.
		$this->SetFont('Times','B',14);
		$this->RotatedText(12,47,convertirtexto($etiquetas_boleta_de_notas[0]),0);		  
		$this->SetFont('Times','B',12);
		$this->RotatedText(110,44,convertirtexto($etiquetas_boleta_de_notas[$n_etiqueta]),0);		  
		$this->RotatedText(165,44,convertirtexto($etiquetas_boleta_de_notas[2]),0);		  
		$this->SetFont('Times','B',10);
	///////////////////////////////////////////////////////////////////////////////////////
    //Restauraci�n de colores y fuentes
		$this->SetFillColor(212, 230, 252);
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
			consultas_alumno(2,0,$codigo_all,$codigo_alumno,$codigo_matricula,$codigo_ann_lectivo,$db_link,'');
			$codigo_alumno_listado[] = $codigo_alumno;
			$codigo_matricula_listado[] = $codigo_matricula;
		}
	else
		{
			//consultas(4,0,$codigo_all,'','','',$db_link,'');
			// Ejecutar la consulta
			$query = "SELECT 
				btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
				a.id_alumno AS codigo_alumno, 
				am.id_alumno_matricula AS codigo_matricula
			FROM alumno AS a
			INNER JOIN alumno_encargado AS ae ON a.id_alumno = ae.codigo_alumno AND ae.encargado = 't'
			INNER JOIN alumno_matricula AS am ON a.id_alumno = am.codigo_alumno AND am.retirado = 'f'
			WHERE TRIM(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = :codigo_bachillerato
			ORDER BY apellido_alumno ASC";

			$stmt = $db_link->prepare($query);
			$stmt->bindParam(':codigo_bachillerato', $codigo_all);
			$stmt->execute();

			// Guardar resultados en una matriz
			$resultados = [];
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$resultados[] = [
					'codigo_alumno' => $row['codigo_alumno'],
					'codigo_matricula' => $row['codigo_matricula']
				];
			}
		}
	// condicionar el ancho y ALTO de cada columna.
		$ancho=array(80,10,5,12,155,110); //determina el ancho de las columnas
		$alto=array(5,12); //determina el alto de las columnas
		//
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
// Supongamos que $resultados ya contiene los datos extraídos previamente.
foreach ($resultados as $fila) {
    // Extraer datos de cada fila
	    $codigo_alumno = $fila['codigo_alumno'];
    	$codigo_matricula = $fila['codigo_matricula'];
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
		// Cambiar el MensajeError Json
			$mensajeError = "Archivos Creados: ".$nombre_grado." - ".$nombre_seccion;
	}
		// Coordenadas de iNICIO.
			$pdf->SetY(40);
			$pdf->SetX(5);
		// variales para la boleta.
			$fill = false; $i=1;  $suma = 0; $aprobado_reprobado = array();
// *************************************************************************************************************************
// ejecutar consulta. que proviene de la nomina. SE CREA LA ARRAY() CODIGO_ALUMNO_LISTADO Y CODIGO_MATRICULA_LISTADO.
// *************************************************************************************************************************
// *************************************************************************************************************************
	//consultas_alumno(2,0,$codigo_all,$codigo_alumno,$codigo_matricula,$codigo_ann_lectivo,$db_link,'');
	$query = "SELECT DISTINCT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
	a.apellido_paterno, a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.foto, a.codigo_genero,
	am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
	gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, am.id_alumno_matricula as codigo_matricula, am.id_alumno_matricula as cod_matricula,
	sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura,
	n.nota_p_p_1, n.nota_p_p_2, n.nota_p_p_3, n.nota_p_p_4, n.nota_p_p_5, n.nota_final, n.recuperacion, n.nota_paes, n.alertas,
	n.indicador_p_p_1, n.indicador_p_p_2, n.indicador_p_p_3, n.indicador_final, nota_recuperacion_2,
	round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3),1) as total_puntos_basica, 
	round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4),1) as total_puntos_media, 
	round((n.nota_p_p_1+n.nota_p_p_2+n.nota_p_p_3+n.nota_p_p_4+n.nota_p_p_5),1) as total_puntos_nocturna,
	aaa.orden, asig.codigo_cc, asig.codigo_area, cat_area.descripcion as nombre_area,
	cat_area_di.descripcion as descripcion_area_dimension 
			FROM alumno a
			INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
			INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' and am.id_alumno_matricula = '$codigo_matricula'
			INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
			INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
			INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
			INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
			INNER JOIN nota n ON n.codigo_alumno = a.id_alumno
			INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
			INNER JOIN catalogo_cc_asignatura cat_cc ON cat_cc.codigo = asig.codigo_cc
			INNER JOIN catalogo_area_asignatura cat_area ON cat_area.codigo = asig.codigo_area
			INNER JOIN catalogo_area_dimension cat_area_di ON cat_area_di.codigo = asig.codigo_area_dimension
			INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = asig.codigo and aaa.orden <> 0 ".
				"WHERE a.id_alumno = '".$codigo_alumno."' and codigo_matricula = '".$codigo_matricula.
				"' and aaa.codigo_ann_lectivo = '".$codigo_ann_lectivo."' ORDER BY asig.codigo_area ASC";
				$result = $db_link -> query($query);
// *************************************************************************************************************************
	
// *************************************************************************************************************************
//	INICIA EL WHILE CON RESPECTO AL VALOR DE LA NOMINA ( CODIGO ALUMNO, CODIGO MATRICULA.)
// *************************************************************************************************************************
// MATRIZ NOMBRE DE LOS CAMPOS. unset( $animales[0] ); BORRAR MATRIZ
	$nombre_campos = array('nota_p_p_1','nota_p_p_2','nota_p_p_3','nota_p_p_4','nota_p_p_5');//,'total_puntos_basica','nota_final','recuperacion');
while($row = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
{
	// variables a utilizar.
		$conteo_aprobadas = 0;
		$nombre_completo_alumno = convertirtexto(trim($row['apellido_alumno']));
		$numero_identificacion_estudiantil = trim($row['codigo_nie']);
		$print_codigo_alumno = $row['codigo_alumno'];
		$print_codigo_matricula = $row['cod_matricula'];
		$nombre_asignatura = convertirtexto(trim($row['n_asignatura']));
		$codigo_genero = trim($row['codigo_genero']);
		$fotos = trim($row['foto']);
		$codigo_institucion = $_SESSION['codigo_institucion'];
		$codigo_area = convertirtexto(trim($row['codigo_area']));
		$descripcion_area = (trim($row['nombre_area']));

	// imprimir la foto en la boleta
		if ($chkfoto == 'yes'){
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fotos/'.$codigo_institucion.'/'.$fotos))
		      {
				$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/fotos/'.$codigo_institucion.'/'.$fotos;	
		      }else if($codigo_genero == '01'){
					$fotos = 'avatar_masculino.png';
					$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$fotos;
				}
				else{
					$fotos = 'avatar_femenino.png';
					$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$fotos;
				}
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
	// DAR FORMATO. -1 en la matriz
		//Colores, ancho de l�nea y fuente en negrita
		$pdf->SetFillColor(200,200,200);
		$pdf->SetTextColor(0);
		$pdf->SetFont('Times','B',12);
		//print_r($catalogo_area_asignatura_codigo);
	//	print $descripcion_area;
		//exit;
		// LINEA DE DIVISIÓN - PARA EL ÁREA BÁSICA.
		if($catalogo_area_asignatura_codigo[0] == $codigo_area){
			if($catalogo_area_basica == true){
				$pdf->Cell(203,6,strtoupper(convertirtexto($catalogo_area_asignatura_area[0])),1,1,'L',true);
				$catalogo_area_basica = false;
			}
		}
		// LINEA DE DIVISIÓN - PARA EL ÁREA FORMATIVA.
		if($catalogo_area_asignatura_codigo[1] == $codigo_area){
			if($catalogo_area_formativa == true){
				$pdf->Cell(203,6,strtoupper(convertirtexto($catalogo_area_asignatura_area[1])),1,1,'L',true);
				$catalogo_area_formativa = false;
			}
		}
		// LINEA DE DIVISIÓN - PARA EL ÁREA TÉCNICA.
		if($catalogo_area_asignatura_codigo[2] == $codigo_area){
			if($catalogo_area_tecnica == true){
				$pdf->Cell(203,6,strtoupper(convertirtexto($catalogo_area_asignatura_area[2])),1,1,'L',true);
				$catalogo_area_tecnica = false;
			}
		}
		// LINEA DE DIVISIÓN - PARA EL ÁREA COMPETENCIAS CIUDADANAS.
		if($catalogo_area_asignatura_codigo[6] == $codigo_area){
			if($catalogo_area_cc == true){
				$pdf->Cell(203,6,strtoupper(convertirtexto($catalogo_area_asignatura_area[6])),1,1,'L',true);
				$catalogo_area_cc = false;
			}
		}
		
		// LINEA DE DIVISIÓN - PARA EL ÁREA COMPLEMENTARIA.
		if($catalogo_area_asignatura_codigo[7] == $codigo_area){
			if($catalogo_area_complementaria == true){
				$pdf->Cell(203,6,strtoupper(convertirtexto($catalogo_area_asignatura_area[7])),1,1,'L',true);
				$catalogo_area_complementaria = false;
			}
		}
		//Restauraci�n de colos y fuentes
		$pdf->SetFillColor(212, 230, 252);
		$pdf->SetTextColor(0);
		$pdf->SetFont('Times','',10);	
			// CAMBIAR EL $CELLWIDTH SI ES BACHILLERATA TECNICO Y ÁREA TÉCNICA.
			// CODIGO AREA = "03"
				if ($codigo_area == "03" && $codigo_bachillerato == "15") {
					$AnchoColumna = 155;
					$AnchoColumnaAsignaturaCell = 4;
				}elseif($codigo_area == "03" && $codigo_bachillerato == "10") {
					$AnchoColumna = 155;
					$AnchoColumnaAsignaturaCell = 4;
				}
				else {
					$AnchoColumna = 80;
					$AnchoColumnaAsignaturaCell = 0;
				}
					$cellWidth= $AnchoColumna;//wrapped cell width
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
					//	
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
					$pdf->MultiCell($ancho[$AnchoColumnaAsignaturaCell],$alto[0],$nombre_asignatura,0,'L',$fill);	//Nombre de la Asignatura.
					$pdf->SetXY($xPos + $ancho[0], $yPos);
					if ($chkfoto == 'yes'){
						$pdf->image($img,187,7,21,27);	// foto o imagen del estudiante.
					}
				// CONCEPTO O CALIFICACIÓN
					$concepto_calificacion = trim($row['codigo_cc']);
				// IMPRIMIR NOTA TRIMESTRE 1, 2, 3 Y CALCULAR CONCEPTO.
				for($ii=0;$ii<count($nombre_campos);$ii++){
					// Extraer el valor.
					$calificacion_ = $row[$nombre_campos[$ii]];
					// EVALUAR LA CALIFICACIÓN.
					if($codigo_area == "03" and $codigo_bachillerato == "15"){

					}elseif($codigo_area == "03" and $codigo_bachillerato == "10"){

					}
					else{
						if($concepto_calificacion == '01'){
							if($calificacion_ != 0){
								$pdf->Cell($ancho[1],($line * $alto[0]),$calificacion_,0,0,'C',$fill);
							}else{
								$pdf->Cell($ancho[1],($line * $alto[0]),'',0,0,'C',$fill);
							}	// NOTA 1 (TRIMESTRE, PERIODO O MODULO)
						}
					}
						//	SI ES DE CONCEPTO.
						if($concepto_calificacion == '02'){
							if($calificacion_ != 0){$pdf->Cell($ancho[1],($line * $alto[0]),cambiar_concepto($calificacion_),0,0,'C',$fill);}else{$pdf->Cell($ancho[1],($line * $alto[0]),'',0,0,'C',$fill);}	// NOTA 1 (TRIMESTRE, PERIODO O MODULO)
						}							
					// CAMBIAR COLOR SI ES DIFERENTE A "A - APROBADO".
						if($codigo_bachillerato >= '01' and  $codigo_bachillerato <= '05')
						{
							$AR = cambiar_aprobado_reprobado_b($calificacion_);}
						else if($codigo_bachillerato >= '06' and  $codigo_bachillerato <= '09')
						{
							$AR = cambiar_aprobado_reprobado_m($calificacion_);}
						else if($codigo_bachillerato == '10'){
							$AR = cambiar_aprobado_reprobado_b($calificacion_);
						}else{
							$AR = cambiar_aprobado_reprobado_m($calificacion_);
						}
						if($codigo_servicio_educativo == '20' || $codigo_servicio_educativo == '13'){
							$AR = cambiar_aprobado_reprobado_media_contable($calificacion_, $descripcion_area);
						}
					// cambiar COLOR.
						if($codigo_area == "03" && $codigo_bachillerato == "15"){

						}elseif($codigo_area == "03" and $codigo_bachillerato == "10"){

						}
						else{
							if($AR == "A"){
								$pdf->Cell($ancho[2],($line * $alto[0]),$AR,'R',0,'C',$fill);
							}else{
								$pdf->SetFont('Arial','B',9);
								$pdf->SetTextColor(255, 25, 0);
								$pdf->Cell($ancho[2],($line * $alto[0]),$AR,'R',0,'C',$fill);
								$pdf->SetFont('');
								$pdf->SetTextColor(0,0,0);
							}
						}
				}	// FOR
				//
				// VALORES RESTANTES. total de puntos, nota_final, recuperacion.
				//
					if($codigo_area == "03" && $codigo_bachillerato == "15"){
							if(verificar_nota_media_contable($row['nota_final'],$row['recuperacion']) < 3){
								$pdf->SetLineWidth(.3);				// GROSOR.
								$pdf->SetDrawColor(255, 0, 0);			// COLOR DE LA LÍNEA.
								$pdf->SetFont('Arial','B',9);
								$pdf->SetTextColor(255, 25, 0);
									$pdf->Cell($ancho[5],$line*$alto[0],"",0,0,0);
									$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media_contable($row['nota_final'],$row['recuperacion']) . ' Rep',1,0,'C',$fill);
								$pdf->SetFont('');
								$pdf->SetTextColor(0,0,0);
								$pdf->SetLineWidth(0.1);				// GROSOR.
								$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
							}else{
								$pdf->SetLineWidth(0.1);				// GROSOR.
								$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
								$pdf->SetFont('');
								$pdf->SetTextColor(0,0,0);
									$pdf->Cell($ancho[5],$line*$alto[0],"",0,0,0);
									$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media_contable($row['nota_final'],$row['recuperacion']) . ' Apr ',0,0,'C',$fill);
							}
					}else if($codigo_area == "03" && $codigo_bachillerato == "10"){
						if(verificar_nota_media_contable($row['nota_final'],$row['recuperacion']) < 3){
							$pdf->SetLineWidth(.3);				// GROSOR.
							$pdf->SetDrawColor(255, 0, 0);			// COLOR DE LA LÍNEA.
							$pdf->SetFont('Arial','B',9);
							$pdf->SetTextColor(255, 25, 0);
								$pdf->Cell($ancho[5],$line*$alto[0],"",0,0,0);
								$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media_contable($row['nota_final'],$row['recuperacion']) . ' Rep',1,0,'C',$fill);
							$pdf->SetFont('');
							$pdf->SetTextColor(0,0,0);
							$pdf->SetLineWidth(0.1);				// GROSOR.
							$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
						}else{
							$pdf->SetLineWidth(0.1);				// GROSOR.
							$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
							$pdf->SetFont('');
							$pdf->SetTextColor(0,0,0);
								$pdf->Cell($ancho[5],$line*$alto[0],"",0,0,0);
								$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media_contable($row['nota_final'],$row['recuperacion']) . ' Apr ',0,0,'C',$fill);
						}
					}
					else{
						if($row['total_puntos_nocturna'] != 0){$pdf->Cell($ancho[3],($line * $alto[0]),trim($row['total_puntos_nocturna']),'L',0,'C',$fill);}else{$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);}
						if($row['nota_final'] != 0){$pdf->Cell($ancho[3],($line * $alto[0]),trim($row['nota_final']),0,0,'C',$fill);}else{$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);}
						if($row['recuperacion'] != 0){$pdf->Cell($ancho[3],($line * $alto[0]),trim($row['recuperacion']),0,0,'C',$fill);}else{$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);}
					//	if($row['nota_recuperacion_2'] != 0){$pdf->Cell($ancho[3],($line * $alto[0]),trim($row['nota_recuperacion_2']),0,0,'C',$fill);}else{$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);}

						if(verificar_nota($row['nota_final'],$row['recuperacion'] != 0,$row['nota_recuperacion_2'] != 0)){
							// CONDICIÓN PARA EDUCACIÓN BÁSICA I Y II CICLO.
							if($codigo_bachillerato >= '01' and  $codigo_bachillerato <= '05')
							{
								if(verificar_nota($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) < 5){
									$pdf->SetLineWidth(.3);				// GROSOR.
									$pdf->SetDrawColor(255, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('Arial','B',9);
									$pdf->SetTextColor(255, 25, 0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Rep',1,0,'C',$fill);
										$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
								}else{
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Apr ',0,0,'C',$fill);
										$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);
										}
								$concepto_calificacion = trim($row['codigo_cc']);
								if(verificar_nota($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) > 5){
									$conteo_aprobadas++;
								}
							}	// CONDICION PARA EDUCACIÓN MEDIA TURNO REGULAR
							if($codigo_bachillerato >= '06' and  $codigo_bachillerato <= '09')
							{
								if(verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) < 6){
									$pdf->SetLineWidth(.3);				// GROSOR.
									$pdf->SetDrawColor(255, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('Arial','B',9);
									$pdf->SetTextColor(255, 25, 0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Rep',1,0,'C',$fill);
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
								}else{
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Apr ',0,0,'C',$fill);
								}
							}	// CONDICION PARA EDUCACIÓN BÁSICA Y III CICLO MODALIDADES FLEXIBLES.
							if($codigo_bachillerato == '10' || $codigo_bachillerato == '12')
							{
								if(verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) < 5){
									$pdf->SetLineWidth(.3);				// GROSOR.
									$pdf->SetDrawColor(255, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('Arial','B',9);
									$pdf->SetTextColor(255, 25, 0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Rep',1,0,'C',$fill);
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
								}else{
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Apr ',0,0,'C',$fill);
								}
							}	// CONDICION PARA EDUCACIÓN MEDIA NOCTURNA - MODALIDADES FLEXIBLES
							if($codigo_bachillerato == '11' or $codigo_bachillerato == "15")
							{
								if(verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) < 5){
									$pdf->SetLineWidth(.3);				// GROSOR.
									$pdf->SetDrawColor(255, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('Arial','B',9);
									$pdf->SetTextColor(255, 25, 0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Rep',1,0,'C',$fill);
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
								}else{
									$pdf->SetLineWidth(0.1);				// GROSOR.
									$pdf->SetDrawColor(0, 0, 0);			// COLOR DE LA LÍNEA.
									$pdf->SetFont('');
									$pdf->SetTextColor(0,0,0);
										$pdf->Cell($ancho[3],($line * $alto[0]),verificar_nota_media($row['nota_final'],$row['recuperacion'],$row['nota_recuperacion_2']) . ' Apr ',0,0,'C',$fill);
								}
							}	// CONDICION PARA EDUCACIÓN MEDIA NOCTURNA - MODALIDADES FLEXIBLES
						}else{
							$pdf->Cell($ancho[3],($line * $alto[0]),'',0,0,'C',$fill);
						}
					}
					// VERIFICAR LA CALIFICACIÓN CON RESPECTO A LA RECUPERACIÓN.

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
					$leyenda_2 = "APROBADO PARA EL GRADO INMEDIATO SUPERIOR.";
				if($codigo_bachillerato >= '01' and  $codigo_bachillerato <= '05')
				{
					$leyenda = " Trimestre >= 5.";}
				else if($codigo_bachillerato >= '06' and  $codigo_bachillerato <= '09' || $codigo_bachillerato == '15')
				{
					$leyenda = " Período >= 6.";
				}
				else if($codigo_bachillerato == '10'){
					$leyenda = " Modulo >= 5.";
				}else if($codigo_bachillerato == '12'){
					$leyenda = " Modulo >= 5.";
				}else{
					$leyenda = " Modulo >= 6.";
				}
				if($codigo_servicio_educativo == "20" || $codigo_servicio_educativo == "13"){
					$pdf->SetFont('Arial','',6);
					$leyenda = " Modulo >= .3.";
					$pdf->Cell(120,3,'Nota. Para Aprobar cada asignatura por '.convertirtexto($leyenda),0,1,'L');
					$pdf->Cell(160,3,'Si alguna ASIGNATURA aparece en BLANCO consulte con el DOCENTE que la imparte.',0,1,'L');
					$pdf->Cell(40,3,'A = Aprobado; R = Reprobado ',0,1,'L');
				}else{
					$pdf->SetFont('Arial','',9);
					$pdf->Cell(120,$alto[0],'Nota. Para Aprobar cada asignatura por '.convertirtexto($leyenda),0,1,'L');
					$pdf->Cell(160,$alto[0],'Si alguna ASIGNATURA aparece en BLANCO consulte con el DOCENTE que la imparte.',0,1,'L');
					$pdf->Cell(40,$alto[0],'A = Aprobado; R = Reprobado ',0,1,'L');
				}
				// LEYENDA GRADO INMEDIATO SUPERIOR
				if($codigo_bachillerato >= '01' and  $codigo_bachillerato <= '05')
				{
					if($conteo_aprobadas > 7){
						$pdf->Cell(40,$alto[0],$leyenda_2,0,1,'L');
					}
				}
				else if($codigo_bachillerato >= '06' and  $codigo_bachillerato <= '09' || $codigo_bachillerato == "15")
				{
					$AR = cambiar_aprobado_reprobado_m($calificacion_);}
				else if($codigo_bachillerato == '10'){
					$AR = cambiar_aprobado_reprobado_b($calificacion_);
				}else{
					$AR = cambiar_aprobado_reprobado_m($calificacion_);
				}
				if($codigo_servicio_educativo == "20" || $codigo_servicio_educativo == "13"){
					$AR = cambiar_aprobado_reprobado_media_contable($calificacion_, $descripcion_area);
				}
				
		}
			  $i++;			// acumulador para el numero de asignaturas
} // BUCLE QUE RECORRE EL ESTUDIANTE SELECCIONADO A PARTIR DE LA NÓMINA.
//	REESTABLACER VARIABLES O MATRICES UTILIZADAS.
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
			$codigo_modalidad = $codigo_bachillerato;
			$nombre_ann_lectivo = $codigo_ann_lectivo;
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
	$nombre_archivo = $nombre_bachillerato.' '.$nombre_grado.' '.$nombre_seccion.'-'.$codigo_ann_lectivo . '.pdf';
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

function CalcularTiempo($tiempo_inicio){
	global $nombre_archivo;
      // Calcular el tiempo.
	  $tiempo_fin = microtime(true); //true es para que sea calculado en segundos
	  $duration = $tiempo_fin - $tiempo_inicio;
      $hours = (int)($duration/60/60);
      $minutes = (int)($duration/60)-$hours*60;
	  $seconds = (int)$duration-$hours*60*60-$minutes*60;
	  $contenidoOK = "<p><strong>Nombre del Archivo: " . $nombre_archivo . "</strong></p>"
	  . "<p>" . "Tiempo empleado: " . $minutes . " minutos " . $seconds . " segundos" . "</p>";
	  print $contenidoOK;
	  exit;
}