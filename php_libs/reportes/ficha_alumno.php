<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
      $codigo_all = '';
      $codigo_matricula = '';
      $codigo_alumno = $_REQUEST['id_user'];
    $db_link = $dblink;
      
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

// rotar texto funcion MultiCell()
function RotatedTextMultiCell($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
        $this->MultiCell(90,4,$txt,0,'L');
	$this->Rotate(0);
}

function RotatedTextMultiCellAspectos($x,$y,$txt,$angle)
{
	//Text rotated around its origin
	$this->Rotate($angle,$x,$y);
	$this->SetXY($x,$y);
        $this->MultiCell(43,3,$txt,0,'L');
	$this->Rotate(0);
}
//Cabecera de página
function Header()
{
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,7,6,8,11);
    //Arial bold 15
    $this->AddFont('Comic');
    $this->SetFont('Comic','',12);
    $this->SetTextColor(0,0,255);
    $this->Cell(135,7,utf8_decode(TRIM($_SESSION['institucion'])),0,1,'C');
    $this->SetTextColor(0,0,0);
    $this->Rect(5,5,135,215);
}

//Pie de página
function Footer()
{
    //Posición: a 1,5 cm del final
    $this->SetY(-20);
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','');
    //Cabecera
    $w=array(65,20,12,18,20); //determina el ancho de las columnas
    $w2=array(5,12); //determina el ancho de las columnas
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'C',1);
    //Restauración de colores y fuentes

    $this->SetFillColor(224,235,255);
    $this->SetTextColor(0);
    $this->SetFont('');
    //Datos
    $fill=false;
}

}
//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm',array(145,225));
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Times','B',10); // I : Italica; U: Normal;
    $pdf->SetY(5);
    $pdf->SetX(5);
    $pdf->Ln();
/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;

// crear lineas y rectángulos.
    //Crear una línea de la primera firma a 55 cm.
    $pdf->Line(5,55,140,55);
    //Crear una línea de la primera firma a 85 cm.
    $pdf->Line(5,95,140,95);
    //Crear una línea de la primera firma a 140 cm.
    $pdf->Line(5,141,140,141);
    //Crear una línea de la primera firma a 175 cm.
    $pdf->Line(5,181,140,181);
    // cuarta PARTE DEL RECTANGULO. cuadro de la foto..
    $pdf->Rect(117,20,22,28);
/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;    
// colocar etiquetas y cuadros que sean necesarios.
    $pdf->SetTextColor(255,0,0);
    $pdf->RotatedText(55,16,'DATOS GENERALES',0);
    $pdf->SetTextColor(0,0,0);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
// para el rectángulo del label bajar uno, uno menos del margen izq. y de alto 5, ancho depende del campo.
    $pdf->RotatedText(117,18,'Id:',0);
    
    $pdf->RotatedText(8,20,'Apellido Paterno',0);
    $pdf->Rect(7,21,50,5);
    
    $pdf->RotatedText(60,20,'Apellido Materno',0);
    $pdf->Rect(59,21,50,5);

    $pdf->RotatedText(8,30,'Nombres',0);
    $pdf->Rect(7,31,50,5);

    $pdf->RotatedText(60,30,utf8_decode('Teléfono: Casa'),0);
    $pdf->Rect(59,31,20,5);
    
    $pdf->RotatedText(90,30,utf8_decode('Célular'),0);
    $pdf->Rect(89,31,20,5);
    
    $pdf->RotatedText(8,40,utf8_decode('Dirección'),0);
    $pdf->Rect(7,41,100,10);

    // Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;
    
    $pdf->RotatedText(108,53,'NIE:',0);
/////////////////////////////////////////////////////////////////////////////////////////////////////////    
/////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;
    $pdf->SetTextColor(255,0,0);
    $pdf->RotatedText(35,59,'DATOS DE PARTIDA DE NACIMIENTO',0);
    $pdf->SetTextColor(0,0,0);
    // Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;

    $pdf->RotatedText(8,65,'Departamento',0);
    $pdf->Rect(7,66,30,5);
    
    $pdf->RotatedText(41,65,'Municipio',0);
    $pdf->Rect(40,66,30,5);

    $pdf->RotatedText(75,65,utf8_decode('Género'),0);
    $pdf->Rect(74,66,20,5);

    $pdf->RotatedText(98,65,'Estado Civil',0);
    $pdf->Rect(97,66,20,5);

    $pdf->RotatedText(8,75,'Nacionalidad',0);
    $pdf->Rect(7,76,30,5);

    $pdf->RotatedText(41,75,'Transporte',0);
    $pdf->Rect(40,76,30,5);

    $pdf->RotatedText(75,75,'Distancia',0);
    $pdf->Rect(74,76,20,5);
/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
    $pdf->RotatedText(8,85,'Fecha de Nacimiento',0);
    $pdf->Rect(7,86,30,5);
    
    $pdf->RotatedText(41,85,'Edad',0);
    $pdf->Rect(40,86,10,5);

    $pdf->RotatedText(54,85,utf8_decode('Número'),0);
    $pdf->Rect(53,86,10,5);

    $pdf->RotatedText(67,85,'Folio',0);
    $pdf->Rect(66,86,10,5);

    $pdf->RotatedText(80,85,'Tomo',0);
    $pdf->Rect(79,86,10,5);

    $pdf->RotatedText(93,85,'Libro',0);
    $pdf->Rect(92,86,10,5);
/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
 $query_a = "SELECT  a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
		    a.apellido_paterno, a.apellido_materno, a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno,
		    a.direccion_alumno, telefono_alumno, a.fecha_nacimiento, a.edad, a.pn_tomo, a.pn_libro, a.pn_numero, a.pn_folio,
		    a.nacionalidad, a.transporte, a.distancia,
		    a.codigo_departamento, a.codigo_municipio, a.genero, a.codigo_estado_civil, cat_ec.nombre as estado_civil,
		    a.estudio_parvularia, a.tiene_hijos, a.cantidad_hijos, a.codigo_actividad_economica, a.codigo_discapacidad, a.codigo_estado_familiar,
		    a.foto,
		    depa.nombre as nombre_departamento, depa.codigo,
		    muni.nombre as nombre_municipio,
		    cat_z_r.codigo as codigo_zona_residencia
		FROM alumno a
		INNER JOIN catalogo_zona_residencia cat_z_r ON cat_z_r.codigo = a.codigo_zona_residencia
		INNER JOIN catalogo_estado_civil cat_ec ON cat_ec.codigo = a.codigo_estado_civil
		INNER JOIN departamento depa ON depa.codigo = a.codigo_departamento 
		INNER JOIN municipio muni ON muni.codigo = a.codigo_municipio and a.codigo_departamento = muni.codigo_departamento
		WHERE id_alumno = $codigo_alumno ORDER BY apellido_alumno";
    // ejecutar la consulta.
        $result = $db_link -> query($query_a);
    //consultas_alumno(3,0,$codigo_all,$codigo_alumno,$codigo_matricula,$db_link,'',''); 
             while($row = $result -> fetch(PDO::FETCH_BOTH))
            {   
		$pdf->RotatedText(122,18,ucwords(strtolower(trim($row['id_alumno']))),0);
		$pdf->RotatedText(8,25,cambiar_de_del($row['apellido_paterno']),0);
		$pdf->RotatedText(60,25,cambiar_de_del($row['apellido_materno']),0);
		$pdf->RotatedText(8,35,cambiar_de_del($row['nombre_completo']),0);
		$pdf->RotatedText(60,35,trim($row['telefono_alumno']),0);
                $pdf->RotatedTextMulticell(8,42,cambiar_de_del($row['direccion_alumno']),0);
		
		// Definimos el tipo de fuente, estilo y tamaño.
		$pdf->SetFont('Comic','',10); // I : Italica; U: Normal;
		$pdf->RotatedText(118,53,trim($row['codigo_nie']),0);
		// Definimos el tipo de fuente, estilo y tamaño.
		$pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
		
		//Condicionar el genero.
		if(trim($row['genero']) == 'm'){$genero = 'Masculino';}else{$genero = 'Femenino';}
		
		$pdf->RotatedText(8,70,trim($row['nombre_departamento']),0);
		$pdf->RotatedText(41,70,trim($row['nombre_municipio']),0);
		$pdf->RotatedText(75,70,$genero,0);
		$pdf->RotatedText(98,70,trim($row['estado_civil']),0);
		
		$pdf->RotatedText(8,80,cambiar_de_del($row['nacionalidad']),0);
		$pdf->RotatedText(41,80,cambiar_de_del($row['transporte']),0);
		$pdf->RotatedText(75,80,trim($row['distancia']),0);
		
		$pdf->RotatedText(8,90,cambiaf_a_normal(trim($row['fecha_nacimiento'])),0);
		$pdf->RotatedText(41,90,trim($row['edad']),0);
		$pdf->RotatedText(54,90,trim($row['pn_numero']),0);
		$pdf->RotatedText(67,90,trim($row['pn_folio']),0);
		$pdf->RotatedText(80,90,trim($row['pn_tomo']),0);
		$pdf->RotatedText(93,90,trim($row['pn_libro']),0);
		
		// valores a variables para otros.
		$codigo_zona = trim($row['codigo_zona_residencia']);
		$estudio_parvularia = trim($row['estudio_parvularia']);
		$tiene_hijos = trim($row['tiene_hijos']);
		$cantidad_hijos = trim($row['cantidad_hijos']);
		$codigo_actividad_economica = trim($row['codigo_actividad_economica']);
		$codigo_discapacidad = trim($row['codigo_discapacidad']);
		$codigo_estado_familiar = trim($row['codigo_estado_familiar']);
		
		// ruta de la foto.
		$foto = trim($row['foto']);
            }
/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////COLOCAR LA FOTO.////////////////////////////////////////////////////////////////////////////////////
    // Colocar foto del alumno/a.
    $imagen_o_foto = $foto;
    
    if($imagen_o_foto == 'foto_no_disponible.jpg')
    {
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png/'.$imagen_o_foto;
    }else
    {
	$img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/png/'.$imagen_o_foto;
    }
    $pdf->Image($img,118,21,20,26);

/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;    
// colocar etiquetas y cuadros que sean necesarios.
    $pdf->SetTextColor(255,0,0);
    $pdf->RotatedText(30,100,utf8_decode('INFORMACIÓN DEL PADRE, MADRE O ENCARGADO'),0);
// colocar etiquetas y cuadros que sean necesarios.
    $pdf->RotatedText(110,105,'PADRE',0);
    $pdf->RotatedText(110,145,'MADRE',0);
    $pdf->RotatedText(110,185,'ENCARGADO',0);
    $pdf->SetTextColor(0,0,0);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
    
    // numero de columna.
    $eje_y_etiqueta = 108;
    $eje_y_etiqueta_2 = 118;
    $eje_y_rectangulo = 109;
    $eje_y_rectangulo_2 = 119;
    $eje_y_etiqueta_3 = 128;
    $eje_y_rectangulo_3 = 129;
    
    for($i=0;$i<=2;$i++)
    {
	$pdf->RotatedText(8,$eje_y_etiqueta,'Nombre',0);
	$pdf->Rect(7,$eje_y_rectangulo,60,5);
    
	$pdf->RotatedText(71,$eje_y_etiqueta,'DUI',0);
	$pdf->Rect(70,$eje_y_rectangulo,25,5);

	$pdf->RotatedText(8,$eje_y_etiqueta_2,'Lugar de Trabajo',0);
	$pdf->Rect(7,$eje_y_rectangulo_2,60,5);

	$pdf->RotatedText(71,$eje_y_etiqueta_2,utf8_decode('Profesión u Oficio'),0);
	$pdf->Rect(70,$eje_y_rectangulo_2,60,5);

	$pdf->RotatedText(8,$eje_y_etiqueta_3,utf8_decode('Dirección'),0);
	$pdf->Rect(7,$eje_y_rectangulo_3,100,10);

	$pdf->RotatedText(111,$eje_y_etiqueta_3,utf8_decode('Teléfono'),0);
	$pdf->Rect(110,$eje_y_rectangulo_3,20,5);
    
	$eje_y_etiqueta = $eje_y_etiqueta + 40;
	$eje_y_rectangulo = $eje_y_rectangulo + 40;
	
	$eje_y_etiqueta_2 = $eje_y_etiqueta_2 + 40;
	$eje_y_rectangulo_2 = $eje_y_rectangulo_2 + 40;
	
	$eje_y_etiqueta_3 = $eje_y_etiqueta_3 + 40;
	$eje_y_rectangulo_3 = $eje_y_rectangulo_3 + 40;
    }
/////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
//  construir consulta.
        $query = "SELECT a.id_alumno, ae.nombres, ae.dui, ae.lugar_trabajo, ae.profesion_oficio, ae.telefono, ae.direccion
		FROM alumno a
		INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno
		WHERE id_alumno = $codigo_alumno ORDER BY ae.id_alumno_encargado";
    // ejecutar la consulta.
        $result = $db_link -> query($query);

    //consultas_alumno(4,0,$codigo_all,$codigo_alumno,$codigo_matricula,$db_link,'');
    $eje_y_campo_1 = 113;
    $eje_y_campo_2 = 123;
    $eje_y_campo_3 = 130;   // para la dirección.
    $eje_y_campo_4 = 132;   // para el número de telefono.
    
     while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
		    $pdf->RotatedText(8,$eje_y_campo_1,cambiar_de_del($row['nombres']),0);
		    $pdf->RotatedText(71,$eje_y_campo_1,trim($row['dui']),0);
		    $pdf->RotatedText(8,$eje_y_campo_2,cambiar_de_del($row['lugar_trabajo']),0);
		    $pdf->RotatedText(71,$eje_y_campo_2,cambiar_de_del($row['profesion_oficio']),0);
		    $pdf->RotatedTextMulticell(8,$eje_y_campo_3,cambiar_de_del($row['direccion']),0);
		    $pdf->RotatedText(113,$eje_y_campo_4,trim($row['telefono']),0);
		    
		    $eje_y_campo_1 = $eje_y_campo_1 + 40;
		    $eje_y_campo_2 = $eje_y_campo_2 + 40;
		    $eje_y_campo_3 = $eje_y_campo_3 + 40;
                    $eje_y_campo_4 = $eje_y_campo_4 + 40;
            }
/////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////AGREGAR LA SEGUNDA PÁGINA//////////////////////////////////////////////////////////////////////////////////
//Títulos de las columnas
    $header=array('Entidad','Serv.Educativo','Sección','Año Lectivo','Estatus');
    $pdf->AliasNbPages();
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;
    $pdf->SetY(55);
    $pdf->SetX(8);
    $pdf->Ln();
    
// crear lineas y rectángulos.
// colocar etiquetas y cuadros que sean necesarios.
    $pdf->SetTextColor(255,0,0);
    $pdf->RotatedText(60,22,'OTROS',0);
    $pdf->RotatedText(45,60,'ESTUDIOS REALIZADOS',0);
    $pdf->SetTextColor(0,0,0);
    //Crear una línea de la primera firma a 55 cm.
    $pdf->Line(5,18,140,18);
    //Crear una línea de la primera firma a 85 cm.
    $pdf->Line(5,55,140,55);
    
    $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;    
    $pdf->RotatedText(8,30,utf8_decode('Actividad Económica'),0);
    $pdf->Rect(50,27,10,5);
    $pdf->RotatedText(52,30.5,$codigo_actividad_economica,0);
    
    $pdf->RotatedText(80,30,'Tiene Hijos',0);
    $pdf->RotatedText(110,30,'Si',0);
    $pdf->RotatedText(125,30,'No',0);
    $pdf->Rect(115,27,4,4);
    $pdf->Rect(130,27,4,4);
    if($tiene_hijos == 't'){$pdf->RotatedText(116,30,'X',0);}else{$pdf->RotatedText(131,30,'X',0);}
    

    $pdf->RotatedText(8,37,'Tipo de discapacidad',0);
    $pdf->Rect(50,33,10,5);
    $pdf->RotatedText(52,36.5,$codigo_discapacidad,0);
    
    $pdf->RotatedText(80,37,'Si tiene cantidad',0);
    $pdf->Rect(110,33,15,5);
    $pdf->RotatedText(115,36.5,$cantidad_hijos,0);
    
    $pdf->RotatedText(8,44,'Estado Familiar',0);
    $pdf->Rect(50,39,10,5);
    $pdf->RotatedText(52,42.5,$codigo_estado_familiar,0);
    
    $pdf->RotatedText(80,44,'Estudio Parvularia',0);
    $pdf->RotatedText(110,44,'Si',0);
    $pdf->RotatedText(125,44,'No',0);
    $pdf->Rect(115,41,4,4);
    $pdf->Rect(130,41,4,4);
    if($estudio_parvularia == 't'){$pdf->RotatedText(116,44,'X',0);}else{$pdf->RotatedText(131,44,'X',0);}

    $pdf->RotatedText(8,50,'Zona de Residencia',0);
    $pdf->RotatedText(45,50,'Urbana',0);
    $pdf->RotatedText(60,50,'Rural',0);
    $pdf->Rect(55,47,4,4);
    $pdf->Rect(70,47,4,4);
    if($codigo_zona == '01'){$pdf->RotatedText(56,50,'X',0);}
    if($codigo_zona == '02'){$pdf->RotatedText(71,50,'X',0);}
/////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////PRESENTAR LOS ESTUDIOS REALIZADOS Y EL ESTATUS.	/////////////////////////////////////
    $query = "SELECT am.id_alumno_matricula, nom_esc.nombre as nombre_escuela,
		bach.nombre as nombre_bachillerato,
		gan.nombre as nombre_grado,
		sec.nombre as nombre_seccion,
		ann.nombre as nombre_ann_lectivo
		FROM alumno_matricula am
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
		INNER JOIN catalogo_escuelas nom_esc ON nom_esc.codigo = am.codigo_institucion
		WHERE am.codigo_alumno = $codigo_alumno ORDER BY nombre_ann_lectivo";
    // ejecutar la consulta.
        $result = $db_link -> query($query);
    //consultas_alumno(5,0,$codigo_all,$codigo_alumno,$codigo_matricula,$db_link,'');
    
    $fill=false; $linea_estudio = 0; $li = 0;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    $w=array(65,20,12,18,20); //determina el ancho de las columnas
    $pdf->Ln();
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
		$linea_estudio++;
	    $pdf->Cell($w[0],6,cambiar_de_del($row['nombre_escuela']),'LR',0,'L',$fill);	// Entidad.
            $pdf->Cell($w[1],6,cambiar_de_del($row['nombre_grado']),'LR',0,'C',$fill);	// Servicio educativo
            $pdf->Cell($w[2],6,trim($row['nombre_seccion']),'LR',0,'C',$fill);	// Sección
            $pdf->Cell($w[3],6,trim($row['nombre_ann_lectivo']),'LR',0,'C',$fill);	// Año Lectivo.
            $pdf->Cell($w[4],6,'','LR',1,'C',$fill);	// Estatus
	    $fill=!$fill;
	    }
	    
	    $linea_faltante = 20 - $linea_estudio;
	    for($li;$li<=$linea_faltante;$li++)
	    {
	    $pdf->Cell($w[0],6,'','LR',0,'L',$fill);	// Entidad.
            $pdf->Cell($w[1],6,'','LR',0,'C',$fill);	// Servicio educativo
            $pdf->Cell($w[2],6,'','LR',0,'C',$fill);	// Sección
            $pdf->Cell($w[3],6,'','LR',0,'C',$fill);	// Año Lectivo.
            $pdf->Cell($w[4],6,'','LR',1,'C',$fill);	// Estatus
	    $fill=!$fill;
	    }

// Cerrando Línea Final.
    $pdf->Cell(array_sum($w),0,'','T');
// Salida del pdf.
    $pdf->Output();
?>