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
      $codigo_all = $_REQUEST["todos"];
	  $aprobado_reprobado = $_REQUEST["aprobado_reprobado"];
      $db_link = $dblink;
	  $listadoigual = "si";
	  $nota_evaluar = 5;
	  $cantidad_asignaturas_evaluar = 6;
	  $compara_ap_re = "Aprobados";
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  almacenar variables de datos del bachillerato.
        while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
				$print_bachillerato = trim($row['nombre_bachillerato']);
				$print_grado = utf8_decode(trim($row['nombre_grado']));
				$print_seccion = trim($row['nombre_seccion']);
				$print_ann_lectivo = trim($row['nombre_ann_lectivo']);
	    
				$codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
				$codigo_grado = trim($row['codigo_grado']);
				$codigo_seccion = trim($row['codigo_seccion']);
				$codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);
					break;
            }
// Evaluar si es de basica o de media para la nota promedio.
// Evaluar nota para obtener aprobados y reprobados de los distintos Modalidades.
	if($codigo_bachillerato == '06')
		{
			$nota_evaluar = 6;
			$cantidad_asignaturas_evaluar = 9;
		}
	// Modalidad Técnico.
	if($codigo_bachillerato == '07')
		{
			$nota_evaluar = 6;
			$cantidad_asignaturas_evaluar = 11;
		}
	// tercer año
	if($codigo_bachillerato >= '08' and $codigo_bachillerato <= '09')
		{
			$nota_evaluar = 6;
			$cantidad_asignaturas_evaluar = 5;
		}		
////////////////////////////////////////////////////////////////////
//////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
//////////////////////////////////////////////////////////////////
// buscar la consulta y la ejecuta.
  consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
// EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL NÚMERO DE ASIGNATURAS.
	$total_asignaturas = 0;	
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
				$total_asignaturas = (trim($row['total_asignaturas']));
            }
	if($codigo_bachillerato == '06')
		{
			//$total_asignaturas = $total_asignaturas - 2;
		}
	// Modalidad Técnico.
	if($codigo_bachillerato == '07')
		{
			//$total_asignaturas = $total_asignaturas - 2;
		}
	// tercer año
	if($codigo_bachillerato == '08' or $codigo_bachillerato <= '09')
		{
			//$total_asignaturas = $total_asignaturas - 2;
		}		
class PDF extends FPDF
{
	// rotar texto funcion TEXT()
function RotatedText($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
  $this->Text($x,$y,$txt);
	$this->Rotate(0);
}
//Cabecera de página
function Header()
{
    //Logo
//    $this->Image('../registro_academico/img/logo.jpg',10,8,33);
    //Arial bold 15
    $this->SetFont('Arial','B',14);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(350,5,utf8_decode('MINISTERIO DE EDUCACIÓN'),0,1,'C');
    $this->Cell(350,5,'PREMATRICULA',0,1,'C');
    //$this->Line(10,20,200,20);
		$this->Cell(350,10,utf8_decode($_SESSION['institucion']),0,1,'C');
    //Salto de línea
    //$this->Ln(20);
}

//Pie de página
function Footer()
{
    //Posición: a 1,5 cm del final
    $this->SetY(-7);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}

// Crear el encabezado del encargado.
function EncabezadoEncargado($header2)
{
//Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
		//Cabecera
		$w=array(5,84,28,30,33,75,32,50); //determina el ancho de las columnas
		$h=array(5,15); //determina el ancho de las columnas
			
		for($i=0;$i<count($header2);$i++)
			$this->Cell($w[$i],$h[1],utf8_decode($header2[$i]),1,0,'C',1);
			
			$y=$this->GetY();
			$x=$this->GetX();  
			$this->SetFont('Arial','B',9);
		
		$this->Ln();
		//Restauración de colores y fuentes
		$this->SetFillColor(224,235,255);
		$this->SetTextColor(0);
		$this->SetFont('');
		//Datos
		$fill=false;
		$this->SetFont('Arial','',9);
	}	

//Tabla coloreada
function FancyTable()
{
    //Colores, ancho de línea y fuente en negrita
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetDrawColor(0,0,0);
		$this->SetLineWidth(.3);
		$this->SetFont('Arial','B',8);
		// Crear Rectangulos.
		$this->SetFillColor(224,248,252);
		$this->Rect(5,30,347,15,true); // Principal.
		$this->SetFillColor(255);
		$this->Rect(5,30,7,15); // Número.
		$this->Rect(5,30,22,15); // Id.
		$this->Rect(5,30,47,15);	// NIE
		$this->Rect(5,30,97,15);	// APellidos
		$this->Rect(5,30,112,15);	// Genero
		$this->Rect(5,30,132,15);	// Fecha PN
		$this->Rect(5,30,152,15);	// Estado Civil
		$this->Rect(5,30,177,15);	// Nacionalidad
		$this->Rect(5,30,197,15);	// Partida de Nacimiento
		$this->Rect(5,30,217,15);	// Medio de Transporte al C.E.
		$this->Rect(5,30,237,15);	// Distancia al C.E.
		$this->Rect(5,30,302,15);	// Direccion
		$this->Rect(5,30,322,15);	// Codigo Departamento/Municipio
		$this->Rect(5,30,347,15);	// Telefono residencia Celular
		
		// Crear las demás lineas para establecer el cuadro completo de 11.
		$y_cuadro = 45; $fill=false; $this->SetFillColor(224,248,252);
		for($ji=0;$ji<=10;$ji++)
		{
			if($ji == 0)
				{
					$this->Rect(5,$y_cuadro,347,15,$fill); // Principal.
					$this->Rect(5,$y_cuadro,7,15); // Número.
					$this->Rect(5,$y_cuadro,22,15); // Id.
					$this->Rect(5,$y_cuadro,47,15);	// NIE
					$this->Rect(5,$y_cuadro,97,15);	// APellidos
					$this->Rect(5,$y_cuadro,112,15);	// Genero
					$this->Rect(5,$y_cuadro,132,15);	// Fecha PN
					$this->Rect(5,$y_cuadro,152,15);	// Estado Civil
					$this->Rect(5,$y_cuadro,177,15);	// Nacionalidad
					$this->Rect(5,$y_cuadro,197,15);	// Partida de Nacimiento
					$this->Rect(5,$y_cuadro,217,15);	// Medio de Transporte al C.E.
					$this->Rect(5,$y_cuadro,237,15);	// Distancia al C.E.
					$this->Rect(5,$y_cuadro,302,15);	// Direccion
					$this->Rect(5,$y_cuadro,322,15);	// Codigo Departamento/Municipio
					$this->Rect(5,$y_cuadro,347,15);	// Telefono residencia Celular
					$y_cuadro = $y_cuadro + 15;
				}else{
					$this->Rect(5,$y_cuadro,347,15,$fill); // Principal.
					$this->Rect(5,$y_cuadro,7,15); // Número.
					$this->Rect(5,$y_cuadro,22,15); // Id.
					$this->Rect(5,$y_cuadro,47,15);	// NIE
					$this->Rect(5,$y_cuadro,97,15);	// APellidos
					$this->Rect(5,$y_cuadro,112,15);	// Genero
					$this->Rect(5,$y_cuadro,132,15);	// Fecha PN
					$this->Rect(5,$y_cuadro,152,15);	// Estado Civil
					$this->Rect(5,$y_cuadro,177,15);	// Nacionalidad
					$this->Rect(5,$y_cuadro,197,15);	// Partida de Nacimiento
					$this->Rect(5,$y_cuadro,217,15);	// Medio de Transporte al C.E.
					$this->Rect(5,$y_cuadro,237,15);	// Distancia al C.E.
					$this->Rect(5,$y_cuadro,302,15);	// Direccion
					$this->Rect(5,$y_cuadro,322,15);	// Codigo Departamento/Municipio
					$this->Rect(5,$y_cuadro,347,15);	// Telefono residencia Celular
					$y_cuadro = $y_cuadro + 15;
				}
					$fill=!$fill;	// Cambiar el color del fondo.
		}
		//Cabecera tamaño de ancho y alto y array para los encabezados.
		$contenido_encabezado = array('Nº','Id','Número de Identificación Estudiantil (NIE)','Nombre Completo del Alumno/a (Por Apellidos)','Género M/F','Fecha de nacimiento dd/mm/aaaa','Estado Civil*','Nacionalidad','Partida Nacimiento (Si/No)'
									  ,'Medio de Transporte al C.E.','Distancia de residencia al C.E. (Km.)','Dirección','Código Depar./Municipio*','Teléfono/Residencia/Celular');
		//un arreglo con su medida  a lo ancho
		$this->SetWidths(array(7,15,25,50,15,20,20,25,20,20,20,65,20,25));
		//un arreglo con alineacion de cada celda
		$this->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
		// Ubicación de la Información y tipo de letra.
      	$y=$this->GetY();
		$x=$this->GetX();
		$this->SetXY($x,$y);
		$this->SetFont('Arial','B',8);
		//OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
		$this->Row(array(utf8_decode($contenido_encabezado[0]),utf8_decode($contenido_encabezado[1]),utf8_decode($contenido_encabezado[2]),utf8_decode($contenido_encabezado[3]),utf8_decode($contenido_encabezado[4]),utf8_decode($contenido_encabezado[5])
						 ,utf8_decode($contenido_encabezado[6]),utf8_decode($contenido_encabezado[7]),utf8_decode($contenido_encabezado[8]),utf8_decode($contenido_encabezado[9]),utf8_decode($contenido_encabezado[10])
						 ,utf8_decode($contenido_encabezado[11]),utf8_decode($contenido_encabezado[12]),utf8_decode($contenido_encabezado[13])));
		//Restauración de colores y fuentes
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetFont('');
		//Datos
		$fill=false;
		$this->SetFont('Arial','',9);
	}
	
function FancyTable2()
{
    //Colores, ancho de línea y fuente en negrita
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetDrawColor(0,0,0);
		$this->SetLineWidth(.3);
		$this->SetFont('Arial','B',8);
		// Crear Rectangulos.
		$this->SetFillColor(224,248,252);
		$this->Rect(5,30,347,15,true); // Principal.
		$this->SetFillColor(255);
		$this->Rect(5,30,7,15); // Número.
		$this->Rect(5,30,22,15); // zona residencia
		$this->Rect(5,30,32,15);	// repite grado
		$this->Rect(5,30,42,15);	//  estudio parvularia
		$this->Rect(5,30,52,15);	// tipo de discapaciad
		$this->Rect(5,30,62,15);	// actividad economica
		$this->Rect(5,30,72,15);	// estado familiar
		$this->Rect(5,30,82,15);	// tiene hios
		$this->Rect(5,30,92,15);	// si, cantiadad hijos
		$this->Rect(5,30,107,15);	// Año en que estudio el Grado Anterior
		$this->Rect(5,30,142,15);	// nombre del padre
		$this->Rect(5,30,162,15);	// lugra de trabajo
		$this->Rect(5,30,182,15);	// profesion u oficio
		$this->Rect(5,30,207,15);	// nº dui
		$this->Rect(5,30,227,15);	// telefono
		$this->Rect(5,30,292,15);	// direccion
		$this->Rect(5,30,312,15);	// responsable
		$this->Rect(5,30,347,15);	// firma
		
		// Crear las demás lineas para establecer el cuadro completo de 11.
		$y_cuadro = 45; $fill=false; $this->SetFillColor(224,248,252);
		for($ji=0;$ji<=10;$ji++)
		{
			if($ji == 0)
				{
					$this->Rect(5,$y_cuadro,347,15,$fill); // Principal.
					$this->Rect(5,$y_cuadro,7,15); // Número.
					$this->Rect(5,$y_cuadro,22,15); // 
					$this->Rect(5,$y_cuadro,32,15);	// 
					$this->Rect(5,$y_cuadro,42,15);	// 
					$this->Rect(5,$y_cuadro,52,15);	// 
					$this->Rect(5,$y_cuadro,62,15);	// 
					$this->Rect(5,$y_cuadro,72,15);	// 
					$this->Rect(5,$y_cuadro,82,15);	// 
					$this->Rect(5,$y_cuadro,92,15);	//
					$this->Rect(5,$y_cuadro,107,15);	//
					$this->Rect(5,$y_cuadro,142,15);	//  nombre del padre
					$this->Rect(5,$y_cuadro,162,15);	//  lugar de trabajo
					$this->Rect(5,$y_cuadro,182,15);	//  profesion u oficio
					$this->Rect(5,$y_cuadro,207,15);	//  nº dui
					$this->Rect(5,$y_cuadro,227,15);	//  telefono
					$this->Rect(5,$y_cuadro,292,15);	//  direccion
					$this->Rect(5,$y_cuadro,312,15);	//  responsable
					$this->Rect(5,$y_cuadro,347,15);	//  firma
					$y_cuadro = $y_cuadro + 15;
				}else{
					$this->Rect(5,$y_cuadro,347,15,$fill); // Principal.
					$this->Rect(5,$y_cuadro,7,15); // Número.
					$this->Rect(5,$y_cuadro,22,15); // 
					$this->Rect(5,$y_cuadro,32,15);	// 
					$this->Rect(5,$y_cuadro,42,15);	// 
					$this->Rect(5,$y_cuadro,52,15);	// 
					$this->Rect(5,$y_cuadro,62,15);	// 
					$this->Rect(5,$y_cuadro,72,15);	// 
					$this->Rect(5,$y_cuadro,82,15);	// 
					$this->Rect(5,$y_cuadro,92,15);	//
					$this->Rect(5,$y_cuadro,107,15);	//
					$this->Rect(5,$y_cuadro,142,15);	//  nombre del padre
					$this->Rect(5,$y_cuadro,162,15);	//  lugar de trabajo
					$this->Rect(5,$y_cuadro,182,15);	//  profesion u oficio
					$this->Rect(5,$y_cuadro,207,15);	//  nº dui
					$this->Rect(5,$y_cuadro,227,15);	//  telefono
					$this->Rect(5,$y_cuadro,292,15);	//  direccion
					$this->Rect(5,$y_cuadro,312,15);	//  responsable
					$this->Rect(5,$y_cuadro,347,15);	//  firma
					$y_cuadro = $y_cuadro + 15;
				}
					$fill=!$fill;	// Cambiar el color del fondo.
		}
		//Cabecera tamaño de ancho y alto y array para los encabezados.
		$contenido_encabezado = array('Nº','Zona Resid. Urb./Rur.','Rep. Grad.Sí/No','Estu. Parv. Sí/No','Tipo Discap.','Activ. Econó.','Est. Fam.','Tiene Hijos','Sí, Cant. Hijos','Año est. Grado Ant.','Nombre del Padre/Madre/Encargado','Lugar de Trabajo','Profesión u Oficio','Nº DUI','Teléfono','Dirección','Responsable Padre/Madre/Otro','FIRMA');
		//un arreglo con su medida  a lo ancho
		$this->SetWidths(array(7,15,10,10,10,10,10,10,10,15,35,20,20,25,20,65,20,35));
		//un arreglo con alineacion de cada celda
		$this->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
		// Ubicación de la Información y tipo de letra.
      	$y=$this->GetY();
		$x=$this->GetX();
		$this->SetXY($x,$y);
		$this->SetFont('Arial','B',8);
		//OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
		$this->Row(array(utf8_decode($contenido_encabezado[0]),utf8_decode($contenido_encabezado[1]),utf8_decode($contenido_encabezado[2]),utf8_decode($contenido_encabezado[3]),utf8_decode($contenido_encabezado[4]),utf8_decode($contenido_encabezado[5])
						 ,utf8_decode($contenido_encabezado[6]),utf8_decode($contenido_encabezado[7]),utf8_decode($contenido_encabezado[8]),utf8_decode($contenido_encabezado[9]),utf8_decode($contenido_encabezado[10])
						 ,($contenido_encabezado[11]),utf8_decode($contenido_encabezado[12]),utf8_decode($contenido_encabezado[13])
						 ,utf8_decode($contenido_encabezado[14]),utf8_decode($contenido_encabezado[15]),utf8_decode($contenido_encabezado[16]),utf8_decode($contenido_encabezado[17])));
		//Restauración de colores y fuentes
		$this->SetFillColor(255);
		$this->SetTextColor(0);
		$this->SetFont('');
		//Datos
		$fill=false;
		$this->SetFont('Arial','',9);
}	
}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(false,5);
    
    $data = array();
//Títulos de las columnas
    //$header=array('Nº','ID','N I E','Apellido de Alumnos/as');
    $pdf->AliasNbPages();
    $pdf->AddPage();
    // Obtener el Encargado de Grado.
    $query_encargado = "SELECT eg.id_encargado_grado, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, eg.codigo_encargado, bach.nombre, gann.nombre, sec.nombre, ann.nombre
								FROM encargado_grado eg
						INNER JOIN personal p ON eg.codigo_docente = p.id_personal
						INNER JOIN bachillerato_ciclo bach ON eg.codigo_bachillerato = bach.codigo
						INNER JOIN ann_lectivo ann ON eg.codigo_ann_lectivo = ann.codigo
						INNER JOIN grado_ano gann ON eg.codigo_grado = gann.codigo
						INNER JOIN seccion sec ON eg.codigo_seccion = sec.codigo
							WHERE btrim(eg.codigo_bachillerato || eg.codigo_grado || eg.codigo_seccion || eg.codigo_ann_lectivo || eg.codigo_turno) = '$codigo_all' and encargado = 't' ORDER BY nombre_docente";

    if($listadoigual == "si"){
    $query = "SELECT DISTINCT a.codigo_nie, a.id_alumno, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno, 
        a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_genero, a.fecha_nacimiento, a.codigo_nacionalidad, a.partida_nacimiento,
        a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, codigo_transporte, a.distancia, a.direccion_alumno, a.codigo_departamento, a.codigo_municipio, a.telefono_alumno, a.telefono_celular, a.codigo_estado_familiar, a.codigo_actividad_economica,
		a.codigo_discapacidad, 
		am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, 
        am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, am.retirado, gan.nombre as nombre_grado, am.codigo_seccion, am.repitente,
        sec.nombre as nombre_seccion, ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, ae.lugar_trabajo, ae.profesion_oficio, ae.direccion as direccion_encargado, ae.telefono, am.certificado, am.tarjeta_vacunacion, a.direccion_alumno, a.telefono_alumno,
		n.nota_final, n.recuperacion, aaa.orden,
		cat_zona.nombre as nombre_zona, cat_catalogo_discapacidad.codigo as codigo_discapacidad, cat_catalogo_discapacidad.nombre as nombre_discapacidad, cat_actividad_economica.nombre as nombre_actividad_economica,
		cat_estado_civil.nombre as nombre_estado_civil, cat_estado_familiar.nombre as nombre_estado_familiar, a.tiene_hijos, a.cantidad_hijos,
		cat_genero.descripcion as nombre_genero,
		cat_nacionalidad.descripcion as nombre_nacionalidad,
		cat_transporte.descripcion as nombre_transporte
		FROM alumno a
		INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
		INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
		INNER JOIN catalogo_zona_residencia cat_zona ON cat_zona.codigo = a.codigo_zona_residencia
		INNER JOIN catalogo_tipo_de_discapacidad cat_catalogo_discapacidad ON cat_catalogo_discapacidad.codigo = a.codigo_discapacidad
		INNER JOIN catalogo_actividad_economica cat_actividad_economica ON cat_actividad_economica.codigo = a.codigo_actividad_economica
		INNER JOIN catalogo_estado_civil cat_estado_civil ON cat_estado_civil.codigo = a.codigo_estado_civil
		INNER JOIN catalogo_estado_familiar cat_estado_familiar ON cat_estado_familiar.codigo = a.codigo_estado_familiar
		INNER JOIN catalogo_genero cat_genero ON cat_genero.codigo = a.codigo_genero
		INNER JOIN catalogo_nacionalidad cat_nacionalidad ON cat_nacionalidad.codigo = a.codigo_nacionalidad
		INNER JOIN catalogo_transporte cat_transporte ON cat_transporte.codigo = a.codigo_transporte
   		INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
   		INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = asig.codigo and aaa.orden <> 0 
		WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '$codigo_all' and nota_final <> 0
		ORDER BY apellido_alumno, aaa.orden ASC";}

    if($listadoigual == "no"){
      $query = "SELECT a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno, 
        a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.edad, a.genero, a.estudio_parvularia,
        a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, 
        am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, am.retirado, gan.nombre as nombre_grado, am.codigo_seccion, 
        sec.nombre as nombre_seccion, ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui, am.certificado, am.tarjeta_vacunacion, a.direccion_alumno, a.telefono_alumno,
	n.nota_final, n.recuperacion
		FROM alumno a
		INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
		INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
		INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
		WHERE am.codigo_bach_o_ciclo = '".$bach."' and am.codigo_ann_lectivo = '".$ann."' and am.codigo_grado = '".$grado."' and am.codigo_seccion between '".$secciondesde."' and '".$seccionhasta."'
		ORDER BY apellido_alumno ASC";}

		$result = $db_link -> query($query) or die("Consulta Fallida - listado igual si"); 
		$result_encabezado = $db_link -> query($query) or die("Consulta Fallida - Encabezado");
		$result_encargado = $db_link -> query($query_encargado) or die("Consulta Fallida - Encargado"); 

//  Nombre del Encargado.
    $nombre_encargado = '';
    //  imprimir datos del bachillerato.
     while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
            {
              $nombre_encargado = cambiar_de_del(trim($rows_encargado['nombre_docente']));
              $codigo_docente = trim($rows_encargado['codigo_encargado']);
                break;
            }
        
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',13); // I : Italica; U: Normal;
    $pdf->SetY(30);
    $pdf->SetX(5);
    //$pdf->ln();

		$bachillerato = ""; $grado = ""; $seccion = "";
		
// Obtener nombre del bachillerato, grado y sección.
//  imprimir datos del bachillerato.
     while($rows = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
              $bachillerato = trim($rows['nombre_bachillerato']);
              $grado = cambiar_de_del(trim($rows['nombre_grado']));    				
              $seccion = trim($rows['nombre_seccion']);
              	break;
            }            	
// Crear el encabezando a la izquierda y derecha.
		$pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
		$pdf->Rect(5,5,100,20);	// Izq.
		$pdf->RotatedText(10,9,'DATOS DE MATRICULA 2022',0);
		
		$pdf->Rect(250,5,100,20);	// Der.
		$pdf->RotatedText(260,9,'DATOS DE MATRICULA 2023',0);
					
		$pdf->SetFont('Arial','B',9); // I : Italica; U: Normal;
			
		// cuadro de la izquierda.
		$pdf->RotatedText(8,15,'Ciclo: '.$bachillerato,0);
		$pdf->RotatedText(8,23,('Grado: ').($grado),0);
		$pdf->RotatedText(50,23,utf8_decode('Sección: ').($seccion),0);
		$pdf->RotatedText(8,29,'Docente: '.$nombre_encargado,0);
		$pdf->RotatedText(70,9,$aprobado_reprobado,0);
			
		// cuadro de la derecha.
		$pdf->RotatedText(255,15,'Ciclo: ___________________________________',0);
		$pdf->RotatedText(255,23,'Grado: _____________________',0);
		$pdf->RotatedText(315,23,utf8_decode('Sección: _______'),0);
		$pdf->RotatedText(255,29,'Docente: _________________________________',0);
		
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable(); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

//  mostrar los valores de la consulta
    $result = $db_link -> query($query) or die("Consulta Fallida");
    $w=array(7,15,25,60,20,20,10,100,10,70); //determina el ancho de las columnas
    $h=array(10,10,4); //determina el ancho de las columnas  
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $generom = ''; $generof = ''; $incrementar = 0;
    	$y=$pdf->GetY();
        $x=$pdf->GetX();

//  cuenta el total de alumnos para colocar en la estadistica.
    $ji = 1; $total_aprobados = 0; $contar_aprobados = 0; $total_reprobados = 0; $contar_p_m = 0; $generos = ''; $notas = 0; $nombre_resultado = array();
    $total_promovidos_m = 0; $total_promovidos_f = 0; $contar_r_m = 0; $total_retenidos_m = 0; $contar_r_f = 0; $contar_p_f = 0; $total_retenidos_f = 0;
    $si_aprobado = 0; $no_aprobado = 0;
    $nota_recuperacion = 0; $nota_final = 0;
    $yy = $pdf->GetY();
    // INICIO DEL BUCLE PARA EMPEZAR A MOSTRAR LOS DATOS.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
        $generos = $row['genero'];  
        // nota final y recuperación.
          $nota_recuperacion = $row['recuperacion'];
          $nota_final = $row['nota_final'];
        // VALIDAR CUAL TOMAR SI NOTA FINAL O RECUPERACIÓN.
            if($nota_final < 5 && $nota_recuperacion != 0){
              $notas = round(($nota_final+$nota_recuperacion)/2,0);
              }
                else{
                 $notas = $nota_final;
                }

		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato >= '03' and $codigo_bachillerato <= '05')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
              	contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
              case 3:
              	contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
              case 4:
              	contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
              case 5:
              	contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
              case 6:
              	contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            }
		}
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato == '06')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 6:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 7:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 8:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 9:
					contar_promovidos($generos, $notas, $nota_evaluar);
				break;
            }
		}
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato == '07')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 6:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 7:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 8:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 9:
					contar_promovidos($generos, $notas, $nota_evaluar);
				break;
				case 10:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 11:
					contar_promovidos($generos, $notas, $nota_evaluar);
				break;
            }
		}
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato == '08' or $codigo_bachillerato == '09')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            }
		}

		// Condiiconar para Educación Indicial y parvularia. 		
		if($codigo_bachillerato >= '01' and $codigo_bachillerato <= '02')
		{
			$si_aprobado = 1;
			$cantidad_asignaturas_evaluar = 1;
		}
			//print $si_aprobado;
     //contar el total de promovidos segun genero y si contar_p_m es mayor igual que cinco.
     if($ji == $total_asignaturas)
     {
       // Imprimir valores si $contar_p_m >= 6 ó $contar_p_f >= 6. // y para mostrar los aprobados.
       if($aprobado_reprobado == "Aprobados"){
			if($si_aprobado == $cantidad_asignaturas_evaluar)
			{
				// Asignar valores a variables.
				$numero = $i;
				$id_alumno = trim($row['id_alumno']);
				$codigo_nie = trim($row['codigo_nie']);
				$apellido_alumno = cambiar_de_del(trim($row['apellido_alumno']));
				

				$nombre_estado_civil = trim($row['nombre_estado_civil']);
				$nombre_estado_familiar = trim($row['nombre_estado_familiar']);
				$tiene_hijos = trim($row['tiene_hijos']);
				$cantidad_hijos = trim($row['cantidad_hijos']);
				$nombre_genero = substr(trim($row['nombre_genero']),0,1);
				$fecha_nacimiento = cambiaf_a_normal(trim($row['fecha_nacimiento']));
				$nombre_nacionalidad = trim($row['nombre_nacionalidad']);
				$partida_nacimiento = trim($row['partida_nacimiento']);
				$nombre_transporte = trim($row['nombre_transporte']);
				$distancia = trim($row['distancia']);
				$direccion = ucfirst(strtolower(trim($row['direccion_alumno'])));
				$codigo_departamento_municipio = trim($row['codigo_departamento']) . trim($row['codigo_municipio']);
				$telefono_alumno_celular = trim($row['telefono_alumno']) . '/' .trim($row['telefono_celular']);
				 
				 if($partida_nacimiento == '1'){$partida_nacimiento = "Sí";}else{$partida_nacimiento = "No";}
				 // imprimir en pantalla.
				 //Cabecera tamaño de ancho y alto y array para los encabezados.
				 $contenido_tabla = array($numero,$id_alumno,$codigo_nie,$apellido_alumno,$nombre_genero,$fecha_nacimiento,$nombre_estado_civil,$nombre_nacionalidad,$partida_nacimiento,$nombre_transporte,$distancia,$direccion,$codigo_departamento_municipio,$telefono_alumno_celular);
				 //un arreglo con su medida  a lo ancho
				$pdf->SetWidths(array(7,15,25,50,15,20,20,25,20,20,20,65,20,25));
				 //un arreglo con alineacion de cada celda
				 $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
	 //			$yy = $pdf->GetY();
				 $pdf->SetY($yy + $incrementar);
				 // Ubicación de la Información y tipo de letra.
				 $pdf->SetFont('Arial','',9.5);
				 //OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
				 $pdf->Row(array(utf8_decode($contenido_tabla[0]),utf8_decode($contenido_tabla[1]),utf8_decode($contenido_tabla[2]),($contenido_tabla[3]),utf8_decode($contenido_tabla[4]),utf8_decode($contenido_tabla[5])
								 ,utf8_decode($contenido_tabla[6]),utf8_decode($contenido_tabla[7]),utf8_decode($contenido_tabla[8]),utf8_decode($contenido_tabla[9]),utf8_decode($contenido_tabla[10]),utf8_decode($contenido_tabla[11]),utf8_decode($contenido_tabla[12])
								 ,utf8_decode($contenido_tabla[13])));			
				 // CORTE PARA COLOCAR DE NUEVO EL ENCABEZADO AL PRINCIPIO DE LA SIGUIENTE PÁGINA.
					 if($i == 11 or $i == 22 or $i == 33 or $i == 44)
					 {
						 $pdf->AddPage();
						 $pdf->SetY(30);
						 $pdf->SetX(5);
						 $pdf->FancyTable();
						 $incrementar = 0;
					 }else{
						 //  $fill=!$fill;
						 $incrementar = $incrementar + 15;
					 }
					 $i=$i+1;
			}	// fin del if que define los aprobados se han masculino o femenino.
       }
       
       if($aprobado_reprobado == "Reprobados"){
			if($no_aprobado >= 1)
			{
// Asignar valores a variables.
				$numero = $i;
				$id_alumno = trim($row['id_alumno']);
				$codigo_nie = trim($row['codigo_nie']);
				$apellido_alumno = cambiar_de_del(trim($row['apellido_alumno']));
				

				$nombre_estado_civil = trim($row['nombre_estado_civil']);
				$nombre_estado_familiar = trim($row['nombre_estado_familiar']);
				$tiene_hijos = trim($row['tiene_hijos']);
				$cantidad_hijos = trim($row['cantidad_hijos']);
				$nombre_genero = substr(trim($row['nombre_genero']),0,1);
				$fecha_nacimiento = cambiaf_a_normal(trim($row['fecha_nacimiento']));
				$nombre_nacionalidad = trim($row['nombre_nacionalidad']);
				$partida_nacimiento = trim($row['partida_nacimiento']);
				$nombre_transporte = trim($row['nombre_transporte']);
				$distancia = trim($row['distancia']);
				$direccion = ucfirst(strtolower(trim($row['direccion_alumno'])));
				$codigo_departamento_municipio = trim($row['codigo_departamento']) . trim($row['codigo_municipio']);
				$telefono_alumno_celular = trim($row['telefono_alumno']) . '/' .trim($row['telefono_celular']);
				 
				 if($partida_nacimiento == '1'){$partida_nacimiento = "Sí";}else{$partida_nacimiento = "No";}
				 // imprimir en pantalla.
				 //Cabecera tamaño de ancho y alto y array para los encabezados.
				 $contenido_tabla = array($numero,$id_alumno,$codigo_nie,$apellido_alumno,$nombre_genero,$fecha_nacimiento,$nombre_estado_civil,$nombre_nacionalidad,$partida_nacimiento,$nombre_transporte,$distancia,$direccion,$codigo_departamento_municipio,$telefono_alumno_celular);
				 //un arreglo con su medida  a lo ancho
				$pdf->SetWidths(array(7,15,25,50,15,20,20,25,20,20,20,65,20,25));
				 //un arreglo con alineacion de cada celda
				 $pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
	 //			$yy = $pdf->GetY();
				 $pdf->SetY($yy + $incrementar);
				 // Ubicación de la Información y tipo de letra.
				 $pdf->SetFont('Arial','',9.5);
				 //OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
				 $pdf->Row(array(utf8_decode($contenido_tabla[0]),utf8_decode($contenido_tabla[1]),utf8_decode($contenido_tabla[2]),($contenido_tabla[3]),utf8_decode($contenido_tabla[4]),utf8_decode($contenido_tabla[5])
								 ,utf8_decode($contenido_tabla[6]),utf8_decode($contenido_tabla[7]),utf8_decode($contenido_tabla[8]),utf8_decode($contenido_tabla[9]),utf8_decode($contenido_tabla[10]),utf8_decode($contenido_tabla[11]),utf8_decode($contenido_tabla[12])
								 ,utf8_decode($contenido_tabla[13])));			
				 // CORTE PARA COLOCAR DE NUEVO EL ENCABEZADO AL PRINCIPIO DE LA SIGUIENTE PÁGINA.
					 if($i == 11 or $i == 22 or $i == 33 or $i == 44)
					 {
						 $pdf->AddPage();
						 $pdf->SetY(30);
						 $pdf->SetX(5);
						 $pdf->FancyTable();
						 $incrementar = 0;
					 }else{
						 //  $fill=!$fill;
						 $incrementar = $incrementar + 15;
					 }
					 $i=$i+1;	 
			}	// fin del if que define los aprobados se han masculino o femenino.
       }       // finde la condicion reprobado.
     } 	// fin del if.
       // Incremento del Número. para evaluar cada alumno con sus respectivas asignaturas.
          if($ji == $total_asignaturas){$ji = 1;$si_aprobado = 0;$no_aprobado=0;}else{$ji++;}
    } //fin del while			
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////SEGUNDA PARTE DE LOS DATOS////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$pdf->AddPage();
	$pdf->SetY(30);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->FancyTable2(); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

//  mostrar los valores de la consulta
    $result = $db_link -> query($query) or die("Consulta Fallida");
    $w=array(7,15,25,60,20,20,10,100,10,70); //determina el ancho de las columnas
    $h=array(10,10,4); //determina el ancho de las columnas  
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $generom = ''; $generof = ''; $incrementar = 0;
    	$y=$pdf->GetY();
        $x=$pdf->GetX();

//  cuenta el total de alumnos para colocar en la estadistica.
    $ji = 1; $total_aprobados = 0; $contar_aprobados = 0; $total_reprobados = 0; $contar_p_m = 0; $generos = ''; $notas = 0; $nombre_resultado = array();
    $total_promovidos_m = 0; $total_promovidos_f = 0; $contar_r_m = 0; $total_retenidos_m = 0; $contar_r_f = 0; $contar_p_f = 0; $total_retenidos_f = 0;
    $si_aprobado = 0; $no_aprobado = 0;
    $yy = $pdf->GetY();
    // INICIO DEL BUCLE PARA EMPEZAR A MOSTRAR LOS DATOS.
     while($row = $result -> fetch(PDO::FETCH_BOTH))
    {   
        $generos = $row['genero'];  
        // nota final y recuperación.
          $nota_recuperacion = $row['recuperacion'];
          $nota_final = $row['nota_final'];
        // VALIDAR CUAL TOMAR SI NOTA FINAL O RECUPERACIÓN.
            if($nota_final < 5 && $nota_recuperacion != 0){
              $notas = round(($nota_final+$nota_recuperacion)/2,0);
              }
                else{
                 $notas = $nota_final;
                }
		
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato >= '03' and $codigo_bachillerato <= '05')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 6:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            }
		}
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato == '06')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 6:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 7:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 8:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 9:
					contar_promovidos($generos, $notas, $nota_evaluar);
				break;
            }
		}
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato == '07')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 6:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 7:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 8:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 9:
					contar_promovidos($generos, $notas, $nota_evaluar);
				break;
				case 10:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 11:
					contar_promovidos($generos, $notas, $nota_evaluar);
				break;
            }
		}
		// Condicionar para primer, segundo y tercer ciclo.
		if($codigo_bachillerato == '08' or $codigo_bachillerato == '09')
		{
            switch($ji){
            	case 1:
            		contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            	case 2:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 3:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 4:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
				case 5:
					contar_promovidos($generos, $notas, $nota_evaluar);
        		  break;
            }
		}

		// Condiiconar para Educación Indicial y parvularia. 		
		if($codigo_bachillerato >= '01' and $codigo_bachillerato <= '02')
		{
			$si_aprobado = 1;
			$cantidad_asignaturas_evaluar = 1;
		}		
     //contar el total de promovidos segun genero y si contar_p_m es mayor igual que cinco.
     if($ji == $total_asignaturas)
     {
       // Imprimir valores si $contar_p_m >= 6 ó $contar_p_f >= 6. // y para mostrar los aprobados.
       if($aprobado_reprobado == "Aprobados"){
			if($si_aprobado == $cantidad_asignaturas_evaluar)
			{
				// Asignar valores a variables.
				$numero = $i;
				$nombre_zona = trim($row['nombre_zona']);
				$repitente = "";
				$ann_estudio_anterior = "";
				$estudio_parvularia = trim($row['estudio_parvularia']);
				$codigo_discapacidad = trim($row['codigo_discapacidad']);
				$codigo_actividad_economica = trim($row['codigo_actividad_economica']);
				$codigo_estado_familiar = trim($row['codigo_estado_familiar']);
				$tiene_hijos = trim($row['tiene_hijos']);
				$cantidad_hijos = trim($row['cantidad_hijos']);
				$lugar_trabajo = cambiar_de_del(trim($row['lugar_trabajo']));
				$profesion_u_oficio = cambiar_de_del(trim($row['profesion_oficio']));
				$dui = trim($row['dui']);
				$telefono = trim($row['telefono']);
				$direccion = trim($row['direccion_encargado']);
				
				$nombre_encargado = cambiar_de_del($row['nombres']);
				if($tiene_hijos == '1'){$tiene_hijos = "Sí";}else{$tiene_hijos = "No";}
				if($estudio_parvularia == '1'){$estudio_parvularia = "Sí";}else{$estudio_parvularia = "No";}
				 // imprimir en pantalla.  //Cabecera tamaño de ancho y alto y array para los encabezados.
				 $contenido_tabla = array($numero,$nombre_zona,$repitente,$estudio_parvularia,$codigo_discapacidad,$codigo_actividad_economica,$codigo_estado_familiar,$tiene_hijos,$cantidad_hijos,$ann_estudio_anterior,$nombre_encargado,$lugar_trabajo,$profesion_u_oficio,$dui,$telefono,$direccion,'','');
				 //un arreglo con su medida  a lo ancho
				$pdf->SetWidths(array(7,15,10,10,10,10,10,10,10,15,35,20,20,25,20,65,20,35));
				//un arreglo con alineacion de cada celda
				$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
	 //			$yy = $pdf->GetY();
				 $pdf->SetY($yy + $incrementar);
				 // Ubicación de la Información y tipo de letra.
				 $pdf->SetFont('Arial','',9.5);
				 //OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
				 $pdf->Row(array(utf8_decode($contenido_tabla[0]),utf8_decode($contenido_tabla[1]),utf8_decode($contenido_tabla[2]),utf8_decode($contenido_tabla[3]),utf8_decode($contenido_tabla[4]),utf8_decode($contenido_tabla[5])
								 ,utf8_decode($contenido_tabla[6]),utf8_decode($contenido_tabla[7]),utf8_decode($contenido_tabla[8]),($contenido_tabla[9]),($contenido_tabla[10]),($contenido_tabla[11]),($contenido_tabla[12])
								 ,utf8_decode($contenido_tabla[13]),utf8_decode($contenido_tabla[14])));			
				 // CORTE PARA COLOCAR DE NUEVO EL ENCABEZADO AL PRINCIPIO DE LA SIGUIENTE PÁGINA.
					 if($i == 11 or $i == 22 or $i == 33 or $i == 44)
					 {
						 $pdf->AddPage();
						 $pdf->SetY(30);
						 $pdf->SetX(5);
						 $pdf->FancyTable2();
						 $incrementar = 0;
					 }else{
						 //  $fill=!$fill;
						 $incrementar = $incrementar + 15;
					 }
					 $i=$i+1;
			}	// fin del if que define los aprobados se han masculino o femenino.
       }	// fin de la condicion de aprobado
       
       if($aprobado_reprobado == "Reprobados"){
       if($no_aprobado >= 1)
       {
// Asignar valores a variables.
				$numero = $i;
				$nombre_zona = trim($row['nombre_zona']);
				$repitente = "";
				$ann_estudio_anterior = "";
				$estudio_parvularia = trim($row['estudio_parvularia']);
				$codigo_discapacidad = trim($row['codigo_discapacidad']);
				$codigo_actividad_economica = trim($row['codigo_actividad_economica']);
				$codigo_estado_familiar = trim($row['codigo_estado_familiar']);
				$tiene_hijos = trim($row['tiene_hijos']);
				$cantidad_hijos = trim($row['cantidad_hijos']);
				$lugar_trabajo = cambiar_de_del(trim($row['lugar_trabajo']));
				$profesion_u_oficio = cambiar_de_del(trim($row['profesion_oficio']));
				$dui = trim($row['dui']);
				$telefono = trim($row['telefono']);
				$direccion = trim($row['direccion_encargado']);
				
				$nombre_encargado = cambiar_de_del($row['nombres']);
				if($tiene_hijos == '1'){$tiene_hijos = "Sí";}else{$tiene_hijos = "No";}
				if($estudio_parvularia == '1'){$estudio_parvularia = "Sí";}else{$estudio_parvularia = "No";}
				 // imprimir en pantalla.  //Cabecera tamaño de ancho y alto y array para los encabezados.
				 $contenido_tabla = array($numero,$nombre_zona,$repitente,$estudio_parvularia,$codigo_discapacidad,$codigo_actividad_economica,$codigo_estado_familiar,$tiene_hijos,$cantidad_hijos,$ann_estudio_anterior,$nombre_encargado,$lugar_trabajo,$profesion_u_oficio,$dui,$telefono,$direccion,'','');
				 //un arreglo con su medida  a lo ancho
				$pdf->SetWidths(array(7,15,10,10,10,10,10,10,10,15,35,20,20,25,20,65,20,35));
				//un arreglo con alineacion de cada celda
				$pdf->SetAligns(array('C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'));
	 //			$yy = $pdf->GetY();
				 $pdf->SetY($yy + $incrementar);
				 // Ubicación de la Información y tipo de letra.
				 $pdf->SetFont('Arial','',9.5);
				 //OTro arreglo pero con el contenido utf8_decode es para que escriba bien los acentos. 
				 $pdf->Row(array(utf8_decode($contenido_tabla[0]),utf8_decode($contenido_tabla[1]),utf8_decode($contenido_tabla[2]),utf8_decode($contenido_tabla[3]),utf8_decode($contenido_tabla[4]),utf8_decode($contenido_tabla[5])
								 ,utf8_decode($contenido_tabla[6]),utf8_decode($contenido_tabla[7]),utf8_decode($contenido_tabla[8]),($contenido_tabla[9]),($contenido_tabla[10]),($contenido_tabla[11]),($contenido_tabla[12])
								 ,utf8_decode($contenido_tabla[13]),utf8_decode($contenido_tabla[14])));			
				 // CORTE PARA COLOCAR DE NUEVO EL ENCABEZADO AL PRINCIPIO DE LA SIGUIENTE PÁGINA.
					 if($i == 11 or $i == 22 or $i == 33 or $i == 44)
					 {
						 $pdf->AddPage();
						 $pdf->SetY(30);
						 $pdf->SetX(5);
						 $pdf->FancyTable2();
						 $incrementar = 0;
					 }else{
						 //  $fill=!$fill;
						 $incrementar = $incrementar + 15;
					 }
					 $i=$i+1;
       }	// fin del if que define los aprobados se han masculino o femenino.
       }    // fin de la condicion de reprobado.   
     } 	// fin del if.
       // Incremento del Número. para evaluar cada alumno con sus respectivas asignaturas.
          if($ji == $total_asignaturas){$ji = 1;$si_aprobado = 0;$no_aprobado=0;}else{$ji++;}
    } //fin del while

// Construir el nombre del archivo.
	$nombre_archivo = $print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '.pdf';
// Salida del pdf.
    $pdf->Output($nombre_archivo,'I');
?>