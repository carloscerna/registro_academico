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
					$codigo_annlectivo = $_REQUEST['codigo_annlectivo'];
					$codigo_docente = $_REQUEST['codigo_docente'];
					$db_link = $dblink;
						// armando el Query. PARA LA TABLA HISTORIAL.
						$query_cd = "SELECT cd.id_carga_docente, cd.codigo_bachillerato, cd.codigo_asignatura, cd.codigo_ann_lectivo, cd.codigo_grado, cd.codigo_seccion, cd.codigo_turno, cd.codigo_docente,
											bach.nombre as nombre_bachillerato, grado.nombre as nombre_grado, sec.nombre as nombre_seccion, tur.nombre as nombre_turno,
											asig.nombre as nombre_asignatura, asig.codigo, ann.nombre as nombre_ann_lectivo
											from carga_docente cd
											INNER JOIN bachillerato_ciclo bach ON bach.codigo = cd.codigo_bachillerato
											INNER JOIN asignatura asig ON asig.codigo = cd.codigo_asignatura
											INNER JOIN ann_lectivo ann ON ann.codigo = cd.codigo_ann_lectivo
											INNER JOIN personal pd ON pd.id_personal = (cd.codigo_docente)::int
											INNER JOIN grado_ano grado ON grado.codigo = cd.codigo_grado
											INNER JOIN seccion sec ON sec.codigo = cd.codigo_seccion
											INNER JOIN turno tur ON tur.codigo = cd.codigo_turno
												WHERE cd.codigo_ann_lectivo = '$codigo_annlectivo' and cd.codigo_docente = '$codigo_docente' 
												 ORDER BY cd.codigo_bachillerato, cd.codigo_grado, cd.codigo_seccion, asig.codigo";
						// Query para revisar la tabla personal.
							$query_nombres_personal = "SELECT p.id_personal as codigo_personal, p.nombres, p.apellidos, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_c FROM personal p WHERE p.codigo_estatus = '01' and p.id_personal = '$codigo_docente' ORDER BY nombre_c";
						// Ejecutamos el Query. PARA LA TABLA EMPLEADOS.
							$consulta_cd = $dblink -> query($query_cd);
							$consulta_encabezado = $dblink -> query($query_nombres_personal);
							$consulta_nombres = $dblink -> query($query_nombres_personal);
						// Obtener el encabezado.
							    while($listadoPersonalE = $consulta_encabezado -> fetch(PDO::FETCH_BOTH))
							      {
									$nombre_docente = utf8_decode(trim($listadoPersonalE['nombre_c']));
									break;
								  }
						// Obtener el nombre del año lectivo..
							    while($listadoCD = $consulta_cd -> fetch(PDO::FETCH_BOTH))
							      {
									$nombre_ann_lectivo = utf8_encode(trim($listadoCD['nombre_ann_lectivo']));
									break;
								  }
class PDF extends FPDF
{
//Cabecera de página
function Header(){
    //Logo
    global $nombre_mes, $nombre_docente, $nombre_ann_lectivo, $nombre_turno;
    $img = $_SESSION['path_root'].'/registro_academico/img/'.$_SESSION['logo_dos']; $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',12);
    //Movernos a la derecha
    $this->Cell(20);
    //Título
    $this->Cell(250,4,utf8_decode('CARGA ACADÉMICA  -  ') . ($nombre_docente),0,1,'C');
    $this->SetFont('Arial','B',10);
    $this->SetX(30);
    $this->Cell(130,4,'CENTRO EDUCATIVO: '.utf8_decode($_SESSION['institucion']),0,0,'L');
    $this->Cell(40,4,utf8_decode('CÓDIGO: '.$_SESSION['codigo']),0,0,'L');
    $this->SetX(210);
    $this->Cell(20,4,utf8_decode('AÑO LECTIVO: '.$nombre_ann_lectivo),0,1,'L');
    $this->SetXY(0,0);
}
//Pie de página
function Footer(){
  // Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME, 'spanish');						
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $fecha = date("l, F jS Y "); $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
    }
    
//Tabla coloreada
function FancyTable($header){
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,255,255);$this->SetTextColor(0);$this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);$this->SetFont('','B',10);
    //Cabecera
    //(numero, nie, nombre, edad, genero, col1,col2,col3,col4,col5,col6)
    $w=array(5,50); //determina el ancho de las columnas
    $w2=array(5,80,50,120); //determina el ancho de las columnas

    // primera fila
    $this->Cell($w2[0],5,utf8_decode('Nº'),1,0,'C',1);
    $this->Cell($w2[1],5,'MODALIDAD',1,0,'C',1);
    $this->Cell($w2[2],5,utf8_decode('GRADO - SECCIÓN - TURNO'),1,0,'C',1);
    $this->Cell($w2[3],5,'NOMBRE ASIGNATURA',1,0,'C',1);
    $this->LN();
    
    $this->Ln();
    //Restauración de colores y fuentes
    $this->SetFillColor(255,255,255);$this->SetTextColor(0);$this->SetFont('');
    //Datos
    $fill=false;}
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Letter'); $data = array();
//Títulos de las columnas
    $header=array('','','');
    $pdf->AliasNbPages(); $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(20); $pdf->SetX(10);

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// Salto de línea.
    $pdf->ln();
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
    //cabecera
    //(numero, nie, nombre, edad, genero, col1,col2,col3,col4,col5,col6)
    $w=array(5,50); //determina el ancho de las columnas
	$w2=array(5,80,50,120); //determina el ancho de las columnas
	
    $fill=false; $num=1;
   //lazo while.
   $consulta_cd = $dblink -> query($query_cd);
	while($listadoCD = $consulta_cd -> fetch(PDO::FETCH_BOTH))
		{
			// recopilar los valores de los campos.
				$id_carga_docente = utf8_decode(trim($listadoCD['id_carga_docente']));
				$nombre_modalidad = utf8_decode(trim($listadoCD['nombre_bachillerato']));
				$nombre_gst = utf8_decode(trim($listadoCD['nombre_grado'])) . ' ' . trim($listadoCD['nombre_seccion']) . ' ' . trim($listadoCD['nombre_turno']);
				$nombre_asignatura = "'" . trim($listadoCD['codigo_asignatura']) . "'" . " ".utf8_decode(trim($listadoCD['nombre_asignatura']));
			// Imprimir valores
				$pdf->Cell($w2[0],6,$num,1,0,'C',1);
				$pdf->Cell($w2[1],6,$nombre_modalidad,1,0,'L',1);
				$pdf->Cell($w2[2],6,$nombre_gst,1,0,'C',1);
				$pdf->Cell($w2[3],6,$nombre_asignatura,1,1,'L',1);			
		  // Aumentar el valor
				  $num++;
											  
		}
   $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// Salida del pdf.
    $pdf->Output();
?>