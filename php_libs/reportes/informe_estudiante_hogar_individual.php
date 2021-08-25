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
  $crear_archivos = "no";

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
	/*   consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
	   $print_nombre_docente = "";
	   while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
		   {
			   $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
			   
			   if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
				   $print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
			   }
		  
		   }  */
// buscar la consulta y la ejecuta.
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
	$this->SetDrawColor(10,29,210);			// COLOR DE LA LÍNEA.
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
		$this->Line(19,15,210,15);								// LINEA EN VERTICAL
        $this->SetFont('Times','B',11);							// TAMAÑO DE FUENTE 14. NEGRITA.
        $this->SetXY(20,5);
        $this->Cell(25,5,$nombre_institucion,0,1,'L');			// NOMBRE INSTITUCIÓN.
        $this->SetX(20);
		$this->Cell(25,5,$boleta_etiqueta,0,0,'L');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
		$this->SetX(150);
		//$this->Cell(25,5,"Docente: ".$print_nombre_docente,0,0,'L');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
        $this->SetX(-65);
        $this->Cell(55,5,$titulo_principal,0,1,'R');			// TITULO PRINCIPAL, BOLETA, GRADO SECCIÓN AÑO.
	/////////////////////////////////////////////////////////////////////////////////////////////
	// PRINT VALORES FIJOS Y ETIQUETAS NO CAMBIAN.
	$this->Image($img,7,5,12,15);				//LOGO.
    $this->SetLineWidth(.3);					//GROSOR
    /*$this->RotatedText(30,27,'Nombre',0);		// LABEL NOMBRE
    $this->RotatedText(30,37,'NIE',0);			// ALBEL NIE.
    $this->RoundedRect(50, 22, 130, 7, 1.5, '1234','');	// para el nombre
    $this->RoundedRect(50, 31, 35, 7, 1.5, '1234','');	// para el nie*/
}

//Pie de p�gina
function Footer()
{
	// Variables.
	global $registro_docente, $firma, $sello, $print_codigo_alumno, $print_codigo_matricula, $print_codigo_bachillerato, $meses, $dia, $mes, $año;
	/*//Firma Director.
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
		$this->SetY(-20);				//Posici�n: a 1,5 cm del final
		$this->Line(10,245,80,245);		//Crear una l�nea de la primera firma
		$this->Line(120,245,190,245);	//Crear una l�nea de la segunda firma.
		$this->Line(5,265,203,265);		//Crear una l�nea FINAL.
		$this->RotatedText(50,255,$registro_docente,0,1,'C');		// NOMBRE DEL DOCENTE.
		if(isset($img_firma)){$this->Image($img_firma,120,225,70,15);}						// IMAGEN FIRMA
		if(isset($img_firma)){$this->Image($img_sello,80,225,30,30);}						// IMAGEN SELLO
    	$this->RotatedText(130,250,$nombre_director,0,1,'C');	    // Nombre Director
		$this->RotatedText(140,255,'Director(a)',0,1,'C');			// ETIQUETA DIRECTOR.*/

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
    $ancho=array(100,100); //determina el ancho de las columnas
    $alto=array(5,12);
	// ARRAY PARA LAS DIFERENTES ETIQUETAS.
		$etiqueta_encabezado = array('CRITERIOS','DATOS DEL ESTUDIANTE');
	// encabezado table.
	///////////////////////////////////////////////////////////////////////////////////////
    // LABEL. resultado final.
    $this->SetXY(5,20);
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
// NOMINA DE ALUMNOS DE AÑO LECTIVO, GRADO Y SECCIÓN.
//  mostrar los valores de la consulta para listado de las notas o solo una.
			consultas(4,0,$codigo_all,'','','',$db_link,'');
			// RECORRER LA CONSULTA, NOMINA DE ALUMNOS.
				$codigo_alumno_listado = array(); $codigo_matricula_listado = array();
				while($row_listado = $result -> fetch(PDO::FETCH_BOTH)) // bucle para la recorrer las asignaturas.
            		{	
						$codigo_alumno_listado[] = $row_listado['codigo_alumno'];
						$codigo_matricula_listado[] = $row_listado['codigo_matricula'];
					}
	// condicionar el ancho y ALTO de cada columna.
        $ancho=array(100,100); //determina el ancho de las columnas
		$alto=array(5,12,4); //determina el alto de las columnas
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
			$pdf->SetY(15);
			$pdf->SetX(5);
		// variales para la boleta.
			$fill = false; $i=1;  $suma = 0; $aprobado_reprobado = array(); $salto_pagina = 1; $estudiantes_faltantes = 0;
			$array_nie_nombre_error = array();
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
        $foto = trim($row['foto']);
        $genero_estudiante = strtoupper(trim($row['genero']));
        $fecha_nacimiento_estudiante = trim($row['fecha_nacimiento']);
    // DATOS DEL REFERENTE O RESPONSABLE.
        $nombre_responsable = utf8_decode(trim($row['nombres']));
        $nombre_tipo_parentesco = trim($row['nombre_tipo_parentesco']);
        $nombre_sexo = trim($row['nombre_sexo']);
        if($nombre_sexo == "Masculino"){$nombre_sexo = "M";}else{$nombre_sexo = "F";}
        $encargado_dui = trim($row['encargado_dui']);
        $encargado_fecha_nacimiento = trim($row['encargado_fecha_nacimiento']);
        $encargado_direccion = utf8_decode(trim($row['encargado_direccion']));
        $encargado_telefono = trim($row['encargado_telefono']);
	// imprimir la foto en la boleta
		/*if ($chkfoto == 'yes'){
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$foto)){$fotos = 'foto_no_disponible.png';}else{$fotos = $foto;}
				$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png'.'/'.$fotos;
				$pdf->image($img,197,6,21,27);
		}*/
	///////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////		
		// IMPRIME LA PRIMERA ASIGNATURA Y CREA LO NECESARIO.
			$pdf->AddPage();
			$pdf->SetFont('Times','',10); // I : Italica; U: Normal;
			// dibujar encabezado de la tabla.
			$pdf->SetXY(5,35);
			$pdf->FancyTable($header);
			///////////////////////////////////////////////////////////////////////////////////////////////////
			/////CALCULAR EL ALTO PARA TODA LA FILA.///////////////////////////////////////////////////////////////////////////////////////////
			///////////////////////////////////////////////////////////////////////////////////////////////////
					$cellWidth=45;//wrapped cell width
					$cellHeight=5;//normal one-line cell height
					
					//check whether the text is overflowing
					if($pdf->GetStringWidth($nombre_completo_alumno) < $cellWidth){
						//if not, then do nothing
						$line=1;
					}else{
						//if it is, then calculate the height needed for wrapped cell
						//by splitting the text to fit the cell width
						//then count how many lines are needed for the text to fit the cell
						
                        $textLength=strlen($nombre_completo_alumno);	//total text length
						$errMargin=0;		//cell width error margin, just in case
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
								$tmpString=substr($nombre_completo_alumno,$startChar,$maxChar);
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
           
                ///////////////////////////////////////////////////////////////////////////////////////////////////
                ///////////////////////////////////////////////////////////////////////////////////////////////////
                // set tipo de letras
                $pdf->SetFont('Times','',10); // I : Italica; U: Normal;
                // IMPRIMIR DATOS EN PANTALLA.
                $pdf->Cell($ancho[0],$alto[0],'NIE','R',0,1,$fill);
                    $pdf->Cell($ancho[1],$alto[0],$numero_identificacion_estudiantil,'R',1,1,$fill);
                $pdf->Cell($ancho[0],$alto[0],'NOMBRE DEL ESTUDIANTE','R',0,1,$fill);
                        $pdf->Cell($ancho[1],$alto[0],$nombre_completo_alumno,'R',1,'L',$fill);	// Nombre dle estudiante.
                $pdf->SetFont('Times','',9); // I : Italica; U: Normal;
            ///////////////////////////////////////////////////////////////////////////////////////////////////
                $pdf->Cell($ancho[0],$alto[0],'SEXO','R',0,1,$fill);
                    $pdf->Cell($ancho[1],$alto[0],$genero_estudiante,'R',1,1,$fill);
                $pdf->Cell($ancho[0],$alto[0],'FECHA DE NACIMIENTO','R',0,1,$fill);
                $pdf->Cell($ancho[1],$alto[0],$fecha_nacimiento_estudiante,'R',1,1,$fill);
                
            ///////////////////////////////////////////////////////////////////////////////////////////////////
            $pdf->SetFont('Times','B',12); // I : Italica; U: Normal;
                $pdf->Cell(($ancho[0]*2),$alto[0],utf8_decode('INFORMACIÓN DEL RESPONSABLE'),1,1,1,$fill);
            $pdf->SetFont('Times','B',10); // I : Italica; U: Normal;
                $array_datos_responsable = array($nombre_responsable,$nombre_tipo_parentesco,$nombre_sexo,$encargado_dui,$encargado_fecha_nacimiento,$encargado_direccion,$encargado_telefono);
                $array_etiqueta_responsable = array('REFERENTE','TIPO DE PARENTESCO','SEXO','N.º DE DUI','FECHA DE NACIMIENTO','DIRECCIÓN','N.º DE CONTACTO');
                $pdf->SetFont('Times','',8); // I : Italica; U: Normal;
    //                $pdf->MultiCell($ancho[5],$alto[0],$nombre_responsable,'R','L',$fill);	// Nombre dle estudiante.
                $numero_ancho = 1;
                for($hh=0;$hh<count($array_datos_responsable);$hh++)
                    {
                        $pdf->Cell($ancho[$numero_ancho],$alto[0],utf8_decode($array_etiqueta_responsable[$hh]),'R',0,'L',$fill);
                        $pdf->Cell($ancho[$numero_ancho],$alto[0],$array_datos_responsable[$hh],'R',1,'L',$fill);
                    }
                ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                $array_datos_hogar_limpio = array('*','*','*','*','*','*','*','*','*','*','*','*','*');
                $array_etiqueta_actualizacion_estudiante = array('Zona de Referencia','Cuantos Dormitorios tiene su casa','En su Hogar, Cuenta con lo siguiente','Cuenta con servicio de energía Electrica en su casa'
                ,'Cual es la fuente principal de abastecimiento de agua en su casa','Cual es el material principal del piso de su casa','Qué tipo de servicio sanitario tiene su casa','Tiene algún tipo de conexión a internet residencial','Distancia al centro educativo (Km.)',
                'Puede Sintonizar en su casa Canal 10','Sintoniza la franja Educativa','Cantidad de Personas que viven con el estudiante','Viven personas menores de 18 con el estudiante');
                $numero_ancho = 1;
                $pdf->SetFont('Times','B',12); // I : Italica; U: Normal;
                $pdf->Cell(($ancho[0]*2),$alto[0],utf8_decode('ACTUALIZACIÓN DE DATOS - información del Hogar'),1,1,1,$fill);
                $pdf->SetFont('Times','',10); // I : Italica; U: Normal;
                // DATOS DEL RESPONSABLE. BUSCAR EN LA TABLA alumno_hogar.
                    $query_alumno_hogar = "SELECT * FROM alumno_hogar WHERE codigo_alumno = '$codigo_alumno' and codigo_nie = '$numero_identificacion_estudiantil'";
                // Ejecutamos el Query.
                    $consulta_hogar = $dblink -> query($query_alumno_hogar);
                // Verificar si existen registros.
                if($consulta_hogar -> rowCount() != 0){
                    // convertimos el objeto
                    while($listado_hogar = $consulta_hogar -> fetch(PDO::FETCH_BOTH))
                    {
                        $zona_residencia_hogar = trim($listado_hogar['catalogo_zona_residencia']);
                        $cantidad_dormitorios_hogar = trim($listado_hogar['cantidad_dormitorios']);
                        $cuenta_hogar = substr(utf8_decode(trim($listado_hogar['catalogo_hogar'])),0,15);
                        $servicio_energia_hogar = trim($listado_hogar['servicio_energia']);
                        $catalogo_abastecimiento_agua_hogar = trim($listado_hogar['catalogo_abastecimiento_agua']);
                        $catalogo_material_piso_hogar = trim($listado_hogar['catalogo_material_piso']);
                        $catalogo_tipo_servicio_sanitario_hogar = trim($listado_hogar['catalogo_tipo_servicio_sanitario']);
                        $conexion_internet_hogar = trim($listado_hogar['conexion_internet']);
                        $distancia_centro_educativo_hogar = trim($listado_hogar['distancia_centro_educativo']);
                        $sintonizar_canal_hogar = trim($listado_hogar['sintonizar_canal']);
                        $sintonizar_franja_educativa_hogar = trim($listado_hogar['sintonizar_franja_educativa']);
                        $cantidad_viven_estudiante_hogar = trim($listado_hogar['cantidad_viven_estudiante']);
                        $viven_personas_menores_hogar = trim($listado_hogar['viven_personas_menores']);
                    }
                    // CONSTRUIR ARRAY PARA CAPTAR LOS DATOS DE LA TABLA ALUMNO_HOGAR.
                    $array_datos_hogar = array($zona_residencia_hogar,$cantidad_dormitorios_hogar,$cuenta_hogar,$servicio_energia_hogar,
                                        $catalogo_abastecimiento_agua_hogar,$catalogo_material_piso_hogar,$catalogo_tipo_servicio_sanitario_hogar,
                                        $conexion_internet_hogar,$distancia_centro_educativo_hogar,$sintonizar_canal_hogar,$sintonizar_franja_educativa_hogar,
                                        $cantidad_viven_estudiante_hogar,$viven_personas_menores_hogar);
                        for($hh=0;$hh<count($array_datos_hogar);$hh++)
                        {
                            $pdf->Cell($ancho[$numero_ancho],$alto[0],utf8_decode($array_etiqueta_actualizacion_estudiante[$hh]),1,0,'L',$fill);    
                                if(empty($array_datos_hogar[$hh])){
                                    $pdf->Cell($ancho[$numero_ancho],$alto[0],$array_datos_hogar[$hh],1,1,'L',$fill);    
                                }else{
                                    $pdf->Cell($ancho[$numero_ancho],$alto[0],$array_datos_hogar[$hh],1,1,'L',$fill);
                                }
                        }
                }else{
                    
							for($hh=0;$hh<count($array_datos_hogar_limpio);$hh++)
							{
                                $pdf->Cell($ancho[$numero_ancho],$alto[0],utf8_decode($array_etiqueta_actualizacion_estudiante[$hh]),1,0,'L',$fill);    
								$pdf->Cell($ancho[$numero_ancho],$alto[0],$array_datos_hogar_limpio[$hh],1,1,'L',$fill);
							}
					//
                }
} // BUCLE QUE RECORRE EL ESTUDIANTE SELECCIONADO A PARTIR DE LA NÓMINA.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// TERMINA EL FOR QUE RECORRER LA NOMINA DE ESTUDIANES.
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Salida del pdf.
	if($crear_archivos == "si"){
		// Verificar si Existe el directorio archivos.
			$codigo_modalidad = $print_codigo_bachillerato;
			$nombre_ann_lectivo = $print_ann_lectivo;
		// Tipo de Carpeta a Grabar.
			$codigo_destino = 1;
			$nuevo_grado = replace_3(trim($print_grado));
			CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
		// verificar si existe el grado y sección.
		$new_carpeta = "Encuesta Individual";
		// verificar si existe el grado y sección.
		if(!file_exists($DestinoArchivo . $new_carpeta))
		{
			// Para Nóminas. Escolanadamente.
				mkdir ($DestinoArchivo . $new_carpeta);
				chmod($DestinoArchivo . $new_carpeta,07777);
		}
		$NuevoDestinoArchivo = $DestinoArchivo . "$new_carpeta/";	
				
			$modo = 'F'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
			$print_nombre = $NuevoDestinoArchivo . trim($nombre_completo_alumno) . '.pdf';
			
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