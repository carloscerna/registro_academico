<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
	$printer = $_REQUEST["printer"];       
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
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
  $this->MultiCell(25,4,$txt,0,'L');
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
	global $nombre_certificados, $printer;
	// Imprimir el primer encabezado EL ESCUDO DE EL SALVADOR... Y TEXTO. 3 LINEAS
	if($printer == 0){
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$nombre_certificados;
    $this->Image($img,0,0,279.4,215.9);
	}
}

//Pie de página
function Footer()
{   
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(255,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(180,10,80,85,10,12,88,32); //determina el ancho de las columnas
    $w2=array(5,12); //determina el ancho de las columnas
     // encabezado de la boleta.   
       $this->Ln();
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
    $pdf=new PDF('L','mm','Letter');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
//	Agregar el tipo de letra.	
	$pdf->AddFont('Comic','','comic.php');
	$pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    //$pdf->AddPage();
// Obtener el Encargado de Grado.
    $query_encargado = "SELECT eg.id_encargado_grado, eg.codigo_encargado, bach.nombre, gann.nombre, sec.nombre, ann.nombre,
						btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente
                FROM encargado_grado eg
                INNER JOIN personal p ON eg.codigo_docente = p.id_personal
				INNER JOIN bachillerato_ciclo bach ON eg.codigo_bachillerato = bach.codigo
				INNER JOIN ann_lectivo ann ON eg.codigo_ann_lectivo = ann.codigo
				INNER JOIN grado_ano gann ON eg.codigo_grado = gann.codigo
				INNER JOIN seccion sec ON eg.codigo_seccion = sec.codigo
                WHERE btrim(bach.codigo || gann.codigo || sec.codigo || ann.codigo) = '".$codigo_all."' and eg.encargado = 't' ORDER BY p.nombres";		      
//consulta para las notas finales y nombre de asignaturas.
  $query = "SELECT DISTINCT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
                a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno,
                a.nombre_completo, btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
                am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, 
                gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, a.genero,
                sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, n.nota_final, n.recuperacion, asig.nombre as nombre_asignatura, aaa.orden, aaa.codigo_ann_lectivo
                  FROM alumno a
                    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
                    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                    INNER JOIN nota n ON n.codigo_alumno = a.id_alumno and am.id_alumno_matricula = n.codigo_matricula
                    INNER JOIN asignatura asig ON asig.codigo = n.codigo_asignatura
                    INNER JOIN a_a_a_bach_o_ciclo aaa ON aaa.codigo_asignatura = asig.codigo and aaa.orden <> 0 
                      WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo) = '".$codigo_all
                      ."' and aaa.codigo_ann_lectivo = '".substr($codigo_all,6,2)."' ORDER BY apellido_alumno, aaa.orden ASC";
//  mostrar los valores de la consulta
	$result = $db_link -> query($query);
	$result_encabezado = $db_link -> query($query);
	$result_encargado = $db_link -> query($query_encargado);
/////////////////////////////////////////////////////////////////////////////////////////
// configuración de colores par ala linea
    $pdf->SetDrawcolor(0,0,0);
/////////////////////////////////////////////////////////////////////////////////////////
// declara matriz para los certificados dependiendo sus valores de Preparatoria, Primer y Segundo Ciclo, Tercer ciclo (7º y 8ª) y Tercer Ciclo (9ª)
   $medida_bach = ''; $nombre_certificado = array('parvularia.png','basica-1-6.png','basica-7-8.png','basica-9.png');
   if($codigo_bachillerato == '01'){$medida_bach = '01'; $nombre_certificados = $nombre_certificado[0];}
   if($codigo_bachillerato == '02'){$medida_bach = '02'; $nombre_certificados = $nombre_certificado[0];}
   if($codigo_bachillerato == '03' || $codigo_bachillerato == '04'){$medida_bach = '03'; $nombre_certificados = $nombre_certificado[1];}
   if($codigo_bachillerato == '05'){$medida_bach = '05'; $nombre_certificados = $nombre_certificado[2];}
   if($codigo_bachillerato == '05' and $codigo_grado == '09'){$medida_bach = '59'; $nombre_certificados = $nombre_certificado[3];}
   // Creando la página.
  // $pdf->AddPage();
//	Consulta para las medidas.   
   $query = "SELECT m.id_medidas, m.fila, m.columna, m.descripcion, m.codigo_modalidad,
				cat_f.descripcion as nombre_fuente, cat_e.descripcion as nombre_estilo, cat_t.descripcion as tamano_fuente
			FROM medidas m
			INNER JOIN catalogo_fuente cat_f ON cat_f.codigo = m.codigo_fuente
			INNER JOIN catalogo_estilo cat_e ON cat_e.codigo = m.codigo_estilo
			INNER JOIN catalogo_tamano cat_t ON cat_t.codigo = m.codigo_tamano
			WHERE m.codigo_modalidad = '".$medida_bach."' and m.printer = ".$printer." ORDER BY m.id_medidas";
// ejecutar la consulta para las medida de los certificados.
    $xf = array(); $yc = array(); $nombre_fuente = array(); $nombre_estilo = array(); $tamaño_fuente = array(); 
	$result_medidas = $db_link -> query($query);
	while($row_medidas = $result_medidas -> fetch(PDO::FETCH_BOTH))
	{
		// Medidas con respecto a certificados de Primero y Segundo Ciclo.
		if($row_medidas['codigo_modalidad'] == $medida_bach)
			{	
				// printer 0; asignacion del valor de la fila y columna a la matriz.
			    $xf[] = $row_medidas['fila'];  $yc[] = $row_medidas['columna'];
				$nombre_fuente[] = trim($row_medidas['nombre_fuente']);
				$tamaño_fuente[] = trim($row_medidas['tamano_fuente']);
				if(trim($row_medidas['nombre_estilo']) == 'Normal')
				{
					$nombre_estilo[] = "";
				}else{
					$nombre_estilo[] = trim($row_medidas['nombre_estilo']);
				}
			}
	}
///////////////////////////////////////////////////////////////////////////////////////////////////
//  INICIO PARA MOSTRAR LOS DATOS DE LA TABLA.
//	Información de la Institución.
	$nombre_institucion = ($_SESSION['institucion']);
	$nombre_director =  ($_SESSION['nombre_director']);
//  Nombre del Encargado.
    $nombre_encargado = ''; $dividir_codigo_nie = ""; $iv = 1; $col_div = 0;
//	Recorrer la tabla en donde se encuentra el nombre del encargado.
	    while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
	    {
			$nombre_encargado = cambiar_de_del(trim($rows_encargado['nombre_docente']));
	    }
	    global $xf,$yc;
		$i=1; $var_f_e_t = 0;
//	Recorrer la tabla en donde se encuentra las notas y las generales.
		while($row = $result -> fetch(PDO::FETCH_BOTH))
			{
				if($i == 1)
					{
						$pdf->Addpage();
					// Configurar Fuente. 0 Y nombre de la institución.
						$pdf->SetFont($nombre_fuente[$var_f_e_t],$nombre_estilo[$var_f_e_t],$tamaño_fuente[$var_f_e_t]);
						$pdf->RotatedText($xf[0],$yc[0],cambiar_de_del($nombre_institucion),0);
					// Configurar Fuente. 1 y nombre del alumno.
						$pdf->SetFont($nombre_fuente[1],$nombre_estilo[1],$tamaño_fuente[1]);
						$nombre_completo_alumno = ($row['nombre_completo_alumno']);
						$pdf->RotatedText($xf[1],$yc[1],cambiar_de_del($nombre_completo_alumno),0);
					// Configurar Fuente. 2 y número de NIE
					   $pdf->SetFont($nombre_fuente[2],$nombre_estilo[2],$tamaño_fuente[2]);
					   $dividir_codigo_nie = trim($row['codigo_nie']);
					   for($iv = 0; $iv<=strlen($dividir_codigo_nie);$iv++)
						{
							$pdf->RotatedText($xf[2]+$col_div,$yc[2],substr($dividir_codigo_nie,$iv,1),0);
							$col_div = $col_div + 3;
						}
							$col_div = 0;
					// Configurar Fuente. 3 y grado.
						$pdf->SetFont($nombre_fuente[3],$nombre_estilo[3],$tamaño_fuente[3]);
						$nombre_grado = trim(($row['nombre_grado']));
						$pdf->RotatedText($xf[3],$yc[3],strtolower(cambiar_de_del(cambiar_grado($nombre_grado))),0);
					// Configurar Sección. 4 y sección.
						$pdf->SetFont($nombre_fuente[4],$nombre_estilo[4],$tamaño_fuente[4]);
						$pdf->RotatedText($xf[4],$yc[4],"'".trim($row['nombre_seccion'])."'",0);         	                 	
					}
					//	Nota Final.
					// Configurar Lenguaje o Lenguaje y Literatura. 5 .
						$nota_calificación = verificar_nota($row['nota_final'],$row['recuperacion']);
					// Concepto de la Nota. se hace cuando $i == 7.
						$nota_concepto = verificar_nota($row['nota_final'],$row['recuperacion']);
						$concepto_asignatura = cambiar_concepto_letras($nota_concepto);
					// Condicionar para imprimir en pantalla.
						switch ($i) {
							case 1:
								$pdf->SetFont($nombre_fuente[5],$nombre_estilo[5],$tamaño_fuente[5]);
								$pdf->RotatedText($xf[5],$yc[5],$nota_calificación,0);         	                 	
								break;
							case 2:
								$pdf->SetFont($nombre_fuente[6],$nombre_estilo[6],$tamaño_fuente[6]);
								$pdf->RotatedText($xf[6],$yc[6],$nota_calificación,0);         	                 	
								break;
							case 3:
								$pdf->SetFont($nombre_fuente[7],$nombre_estilo[7],$tamaño_fuente[7]);
								$pdf->RotatedText($xf[7],$yc[7],$nota_calificación,0);         	                 	
								break;
							case 4:
								$pdf->SetFont($nombre_fuente[8],$nombre_estilo[8],$tamaño_fuente[8]);
								$pdf->RotatedText($xf[8],$yc[8],$nota_calificación,0);         	                 	
								break;
							case 5:
								$pdf->SetFont($nombre_fuente[9],$nombre_estilo[9],$tamaño_fuente[9]);
								$pdf->RotatedText($xf[9],$yc[9],$nota_calificación,0);         	                 	
								break;
							case 6:
								$pdf->SetFont($nombre_fuente[10],$nombre_estilo[10],$tamaño_fuente[10]);
								$pdf->RotatedText($xf[10],$yc[10],$nota_calificación,0);         	                 	
								break;
							case 7: // moral urbanidad y civica
								$pdf->SetFont($nombre_fuente[25],$nombre_estilo[25],$tamaño_fuente[10]);
								$pdf->RotatedText($xf[25],$yc[25],$nota_calificación,0);         	                 	
								break;
							case 8:
								$pdf->SetFont($nombre_fuente[11],$nombre_estilo[11],$tamaño_fuente[11]);
								$pdf->RotatedText($xf[11],$yc[11],$concepto_asignatura,0);         	                 	
								break;
							case 9:
								$pdf->SetFont($nombre_fuente[12],$nombre_estilo[12],$tamaño_fuente[12]);
								$pdf->RotatedText($xf[12],$yc[12],$concepto_asignatura,0);         	                 	
								break;
							case 10:
								$pdf->SetFont($nombre_fuente[13],$nombre_estilo[13],$tamaño_fuente[13]);
								$pdf->RotatedText($xf[13],$yc[13],$concepto_asignatura,0);         	                 	
								break;
							case 11:
								$pdf->SetFont($nombre_fuente[14],$nombre_estilo[14],$tamaño_fuente[14]);
								$pdf->RotatedText($xf[14],$yc[14],$concepto_asignatura,0);         	                 	
								break;
							case 12:
								$pdf->SetFont($nombre_fuente[15],$nombre_estilo[15],$tamaño_fuente[15]);
								$pdf->RotatedText($xf[15],$yc[15],$concepto_asignatura,0);         	                 	
								break;
						}
					// Incremento del Número. // Salto de página.
					 if($i == 12)
					 {
					// Configurar Fuente. 3 y grado inmediato superior.
						$pdf->SetFont($nombre_fuente[16],$nombre_estilo[16],$tamaño_fuente[16]);
						$pdf->RotatedText($xf[16],$yc[16],grado_superior($nombre_grado),0);
					// Se extiende la presente
						$pdf->SetFont($nombre_fuente[17],$nombre_estilo[17],$tamaño_fuente[17]);
						$pdf->RotatedText($xf[17],$yc[17],utf8_decode(trim($_SESSION['se_extiende'])),0);
					// Municipio.
						$pdf->SetFont($nombre_fuente[18],$nombre_estilo[18],$tamaño_fuente[18]);
						$pdf->RotatedText($xf[18],$yc[18],$_SESSION['nombre_municipio'],0);
					// Departamento.
						$pdf->SetFont($nombre_fuente[19],$nombre_estilo[19],$tamaño_fuente[19]);
						$pdf->RotatedText($xf[19],$yc[19],cambiar_de_del($_SESSION['nombre_departamento']),0);
					// Fecha en letras. dia de entrega
						$pdf->SetFont($nombre_fuente[19],$nombre_estilo[19],$tamaño_fuente[19]);
					// comparar fecha día de entrega cuando sea igual a veintuno.
						if($_SESSION['dia_entrega'] === "veintiuno"){
							$pdf->RotatedText($xf[20],$yc[20],utf8_decode('veintiún'),0);	
						}else{
							$pdf->RotatedText($xf[20],$yc[20],trim($_SESSION['dia_entrega']),0);}
					// Fecha en letras. mes	
						$pdf->SetFont($nombre_fuente[21],$nombre_estilo[21],$tamaño_fuente[21]);
						$pdf->RotatedText($xf[21],$yc[21],$mes,0);
					// Fecha en letras. año.
						$pdf->SetFont($nombre_fuente[22],$nombre_estilo[22],$tamaño_fuente[22]);
						$pdf->RotatedText($xf[22],$yc[22],utf8_decode(strtolower(num2letras($año))),0);
					// Crear una línea. F. Docente.
						$pdf->SetFont($nombre_fuente[23],$nombre_estilo[23],$tamaño_fuente[23]);
						$pdf->RotatedText($xf[23]-((strlen(trim($nombre_encargado)))/2),$yc[23],trim($nombre_encargado),0);
					// Crear una línea. F. Director.
						$pdf->SetFont($nombre_fuente[24],$nombre_estilo[24],$tamaño_fuente[24]);
						$pdf->RotatedText($xf[24]-((strlen(trim($nombre_director)))/2),$yc[24],cambiar_de_del(trim($nombre_director)),0);
					// Salto de Página e inicializar variables.
						//$pdf->Addpage();
							$i = 1;
							$var_f_e_t = 0;
							$interlineado = 40;
					}else
						{$i++;
						 $var_f_e_t++;}   
	      } // do while.	
// Salida del pdf.
    $pdf->Output();
?>