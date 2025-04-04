<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include $path_root."/registro_academico/php_libs/fpdf/fpdf.php";
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
    $print_nombre_docente = "";  
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
// buscar los datos de la sección y extraer el codigo del nivel.
    $codigo_nivel = substr($codigo_all,0,2);
        consultas(13,0,$codigo_all,'','','',$db_link,''); // valor 13 en consultas.
//  imprimir datos del grado en general. extrar la información de la cosulta del archivo consultas.php
    global $nombreNivel, $nombreGrado, $nombreSeccion, $nombreTurno, $nombreAñolectivo, $print_periodo;
// CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
	consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
        global $result_docente, $print_nombre_docente; 
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
    global $print_nombre_docente, $nombreNivel, $nombreGrado, $nombreSeccion, $nombreAñoLectivo, $nombreTurno;
    //Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,15,20);
    //Arial bold 15
    $this->SetFont('PoetsenOne','',16);
    //Título - Nuevo Encabezado incluir todo lo que sea necesario.
    $this->Cell(200,6,convertirtexto($_SESSION['institucion']),0,1,'C');
    $this->Cell(200,4,convertirtexto('Nómina de Estudiantes'),0,1,'C');
    // Nombre del docente u otros.
    $this->SetXY(25,20);
    $this->SetFont('Arial','B',11);
        $this->Write(6,"Docente Encargado: ");
    $this->SetFont('Comic','',12);
        $this->Write(6,$print_nombre_docente);   
    // 
    $this->SetXY(10,25);
    $this->SetFont('Arial','B',11);
        $this->Write(6,"Nivel: ");
    $this->SetFont('Comic','U',11);
        $this->Write(6,$nombreNivel);
    // Año Lectivo.
    $this->SetXY(170,25);
    $this->SetFont('Arial','B',11);
        $this->Write(6,convertirTexto("Año Lectivo: "));
    $this->SetFont('Comic','U',11);
        $this->Write(6,$nombreAñoLectivo);
    // Nombre Nivel.
    $this->SetXY(10,30);
    $this->SetFont('Arial','B',11);
        $this->Write(6,"Grado: ");
    $this->SetFont('Comic','U',11);
        $this->Write(6,$nombreGrado);
    // Nombre Sección.
    $this->SetXY(120,30);
    $this->SetFont('Arial','B',11);
        $this->Write(6,convertirTexto("Sección: "));
    $this->SetFont('Comic','U',11);
        $this->Write(6,"'$nombreSeccion'");
    // Nombre turno.
    $this->SetXY(160,30);
    $this->SetFont('Arial','B',11);
        $this->Write(6,convertirTexto("Turno: "));
    $this->SetFont('Comic','U',11);
        $this->Write(6,$nombreTurno);
    //
    $this->Line(5,25,210,25);
    //Salto de línea
}

//Pie de página
function Footer()
{
    global $fecha;
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(5,270,210,270);
    //Número de página y fecha.
    $this->Cell(0,10,convertirTexto('Página ').$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
}

//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(0,0,0);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $this->Ln();
    $w=[10,15,85,20,10,10,10,10,10,10,10]; //determina el ancho de las columnas
    for($i=0;$i<count($header);$i++){
        $this->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',1);
    }
    //
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
    $pdf=new PDF('P','mm','Letter');
#Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
#Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    $data = [];
// Tipos de fuente.
    $pdf->AddFont('Comic','','comic.php');
    $pdf->AddFont('Alte','','AlteHaasGroteskRegular.php');
    $pdf->AddFont('Alte','B','AlteHaasGroteskBold.php');
    $pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
//Títulos de las columnas
    $header=['Nº','N I E','Nombre de Alumnos/as','F.Nac.','Edad','G.','So.','Rep.','Ret.','N.I.','P.N.'];
    $pdf->AliasNbPages();
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
    $pdf->SetXY(10,30);
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
// buscar la consulta y la ejecuta.
	consultas(4,0,$codigo_all,'','','',$db_link,'');
// Definir ancho(s) de las columna(as) y alto de las filas.
    $w=[10,15,85,20,10,10,10,10,10,10,10]; //determina el ancho de las columnas
    $wEncabezado = [200,100,20];
    $ancho_libro = [5.05];
// Contar el número de registros.
    global $result;
	$fila = $result -> rowCount();
// Evaluar si existen registros.
    if($result -> rowCount() != 0){
        //
        $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.
        //
        $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $repitentem = 0; $repitentef = 0; $totalrepitente = 0; $sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
        $nuevoingresom = 0; $nuevoingresof = 0;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $pdf->Cell($w[0],$ancho_libro[0],$i,'LR',0,'C',$fill);        // núermo correlativo
                $pdf->Cell($w[1],$ancho_libro[0],trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
                $pdf->Cell($w[2],$ancho_libro[0],convertirtexto(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
                $pdf->Cell($w[3],$ancho_libro[0],cambiaf_a_normal($row['fecha_nacimiento']),'LR',0,'C',$fill);  // edad
                $pdf->Cell($w[4],$ancho_libro[0],$row['edad'],'LR',0,'C',$fill);  // edad
                $pdf->Cell($w[5],$ancho_libro[0],strtoupper($row['genero']),'LR',0,'C',$fill);    // genero
                //
                $si_o_no = convertirtexto('Sí');
                //
                if(($row['sobreedad']) == 't'){$pdf->Cell($w[6],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[5],$ancho_libro[0],'','LR',0,'C',$fill);}
                if(($row['repitente']) == 't'){$pdf->Cell($w[7],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[6],$ancho_libro[0],'','LR',0,'C',$fill);} 
                //
                if(($row['retirado']) == 't'){$pdf->Cell($w[8],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[7],$ancho_libro[0],'','LR',0,'C',$fill);}
                if(($row['nuevo_ingreso']) == 't'){$pdf->Cell($w[9],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[8],$ancho_libro[0],'','LR',0,'C',$fill);}
                if(($row['partida_nacimiento']) == 't'){$pdf->Cell($w[9],$ancho_libro[0],$si_o_no,'LR',0,'C',$fill);}else{$pdf->Cell($w[8],$ancho_libro[0],'','LR',0,'C',$fill);}
                //
                $pdf->Ln();
                $fill=!$fill;
                $i+=1;
                //                
            if($row['genero'] == 'm')
            {
                $m++;
                if ($row['repitente'] == 't'){$repitentem++;}
                if ($row['sobreedad'] == 't'){$sobreedadm++;}
                if ($row['nuevo_ingreso'] == 't'){$nuevoingresom++;}}
                else{
                    $f++;
                    if ($row['repitente'] == 't'){$repitentef++;}
                    if ($row['sobreedad'] == 't'){$sobreedadf++;}
                    if ($row['nuevo_ingreso'] == 't'){$nuevoingresof++;}}
                // Salto de Línea.
        		if($i == 39 || $i == 76)
                {
                    $pdf->Cell(array_sum($w),0,'','B');
                    $pdf->AddPage();
                    $pdf->FancyTable($header);
                }
        } //cierre del do while.          
          // rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                for($i=0;$i<=$linea_faltante;$i++)
                    {
                      $pdf->Cell($w[0],$ancho_libro[0],$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      $pdf->Cell($w[1],$ancho_libro[0],'','LR',0,'l',$fill);  // nombre del alumno.
                      $pdf->Cell($w[2],$ancho_libro[0],'','LR',0,'C',$fill);  // NIE
                      $pdf->Cell($w[3],$ancho_libro[0],'','LR',0,'C',$fill);  // NIE
                      $pdf->Cell($w[4],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[5],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[6],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[7],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[8],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[9],$ancho_libro[0],'','LR',0,'C',$fill);  // nota final
                      $pdf->Cell($w[10],$ancho_libro[0],'','LR',0,'C',$fill);  // P.N.
                      $pdf->Ln();   
                      $fill=!$fill;
                      // Salto de Línea.
                        if($numero == 39 || $numero == 76)
                            {
                                $pdf->Cell(array_sum($w),0,'','B');
                                $pdf->AddPage();
                                $pdf->FancyTable($header);
                            }
                    }
		// Cerrando Línea Final.
			$pdf->Cell(array_sum($w),0,'','T');
        // Imprimir datos de suma de masculino y femenino.
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $suma=$m+$f;
            $pdf->ln(6);
            $pdf->SetX(30);
            $pdf->Cell(160,7,'ESTADISTICA',1,0,'C',TRUE);
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->Cell(40,7,'',1,0,'C');
            $pdf->Cell(40,7,'Masculino',1,0,'C');
            $pdf->Cell(40,7,'Femenino',1,0,'C');
            $pdf->Cell(40,7,'Total',1,0,'C');
            //
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'MATRICULA',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$m,1,0,'C');
            $pdf->Cell(40,7,$f,1,0,'C');
            $pdf->Cell(40,7,$suma,1,0,'C');
        // Imprimir datos de alumnos repitentes.
            $totalrepitente = $repitentem + $repitentef;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'REPITENTES',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$repitentem,1,0,'C');
            $pdf->Cell(40,7,$repitentef,1,0,'C');
            $pdf->Cell(40,7,$totalrepitente,1,0,'C');
        // Imprimir datos de alumnos de sobreedad
            $totalsobreedad = $sobreedadm + $sobreedadf;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'SOBREEDAD',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$sobreedadm,1,0,'C');
            $pdf->Cell(40,7,$sobreedadf,1,0,'C');
            $pdf->Cell(40,7,$totalsobreedad,1,0,'C');
        // Imprimir datos de alumnos de sobreedad
            $totalnuevoingreso = $nuevoingresom + $nuevoingresof;
            $pdf->ln();
            $pdf->SetX(30);
            $pdf->SetFont('Arial','B',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,'NUEVO INGRESO',1,0,'C');
            $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
            $pdf->Cell(40,7,$nuevoingresom,1,0,'C');
            $pdf->Cell(40,7,$nuevoingresof,1,0,'C');
            $pdf->Cell(40,7,$totalnuevoingreso,1,0,'C');
                $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
// Salida del pdf.
    $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
    $print_nombre = trim($nombreNivel) . ' - ' . trim($nombreGrado) . ' ' . trim($nombreSeccion) . ' - ' . trim($nombreAñolectivo) . ' - ' . trim($nombreTurno) . '-Nomina.pdf';
    $pdf->Output($print_nombre,$modo);
    }   // condicion si existen registros.
else{
    // si no existen registros.
    $pdf->Cell(150,7,$fila.' NO EXISTEN REGISTROS EN LA TABLA.',1,0,'L');
	$pdf->Output();
}    