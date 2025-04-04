<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/funciones_2.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// Inicializamos variables de mensajes y JSON
    $respuestaOK = true;
    $mensajeError = "Registros Encontrados";
    $contenidoOK = "";
    // variables y consulta a la tabla.
    $crear_archivos = "no";
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
    $crear_archivos = $_REQUEST["chkCrearArchivoPdf"];
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
// buscar la consulta y la ejecuta.
    consultas(4,0,$codigo_all,'','','',$db_link,'');
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
    $this->Cell(200,4,convertirtexto('Nómina de Estudiantes - Matricula 2025'),0,1,'C');
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
    $this->SetXY(160,25);
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
    $this->SetFillColor(128,128,128);
    $this->SetTextColor(255);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(5,15,65,65,25,25); //determina el ancho de las columnas
    $w_linea_1=array(5,15,65,65,25,25); //determina el ancho de las columnas

    $header_linea_1 = array('','','','','N.º de Teléfono','');
    for($ij=0;$ij<count($header_linea_1);$ij++){
        $this->Cell($w_linea_1[$ij],7,convertirtexto($header_linea_1[$ij]),0,0,'C',1);
    }
    $this->Ln();    
    //  Cabecera secundaria
    for($i=0;$i<count($header);$i++){
        $this->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',1);
    }
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
    $data = [];
// Tipos de fuente.
    $pdf->AddFont('Comic','','comic.php');
    $pdf->AddFont('Alte','','AlteHaasGroteskRegular.php');
    $pdf->AddFont('Alte','B','AlteHaasGroteskBold.php');
    $pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');
//Títulos de las columnas
    $header=array('Nº','N I E','Nombre del alumno','Padre/Madre o Encargado','Encargado','Firma');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();
// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',14); // I : Italica; U: Normal;
    $pdf->SetY(30);
    $pdf->SetX(10);
    // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
    $pdf->ln();
    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

//  mostrar los valores de la consulta
    $w=array(5,15,65,65,25,25); //determina el ancho de las columnas
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $repitentem = 0; $repitentef = 0; $totalrepitente = 0; $sobreedadm = 0; $sobreedadf = 0; $totalsobreedad = 0;
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                $pdf->Cell($w[0],5.8,$i,'LR',0,'C',$fill);        // núermo correlativo
                $pdf->Cell($w[1],5.8,trim($row['codigo_nie']),'LR',0,'C',$fill);  // NIE
                $pdf->Cell($w[2],5.8,convertirtexto(trim($row['apellido_alumno'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
                $pdf->Cell($w[3],5.8,convertirtexto(trim($row['nombres'])),'LR',0,'L',$fill); // Nombre + apellido_materno + apellido_paterno
                $pdf->Cell($w[4],5.8,$row['telefono_encargado'],'LR',0,'C',$fill);  // telefono encargado
                $pdf->Cell($w[5],5.8,"",'LR',0,'C',$fill);  // telefono casa
                //
                $pdf->Ln();
                $fill=!$fill;
                $i=$i+1;
        // Salto de Línea.
        	if($i == 35|| $i == 70){$pdf->Cell(array_sum($w),0,'','B');$pdf->AddPage();$pdf->Ln(4);$pdf->FancyTable($header);}
        } //cierre del do while.
		// Cerrando Línea Final.
		$pdf->Cell(array_sum($w),0,'','T');
        $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;

        if($crear_archivos == "si"){
            // Verificar si Existe el directorio archivos.
                //$codigo_modalidad = $nombre_bachillerato;
            // Tipo de Carpeta a Grabar.
                $codigo_destino = 1;
                $nuevo_grado = replace_3(trim($print_grado));
                CrearDirectorios($path_root,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,"");
            // verificar si existe el grado y sección.
            if(!file_exists($DestinoArchivo))
            {
                // Para Nóminas. Escolanadamente.
                    mkdir ($DestinoArchivo);
                    chmod ($DestinoArchivo,07777);
            }
                $NuevoDestinoArchivo = $DestinoArchivo . "/";
            
                $modo = 'F'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
                $nombre_archivo = $print_nombre_docente . ' ' . $nombre_grado . ' ' . $nombre_seccion . convertirtexto("- N.º TELEFONO.pdf");
                $print_nombre = $NuevoDestinoArchivo . trim($nombre_archivo);
                
                //$print_nombre = $path_root . '/registro_academico/temp/' . trim($nombre_completo_alumno) . ' ' . trim($print_grado) . ' ' . trim($print_seccion) . '.pdf';
                $pdf->Output($print_nombre,$modo);
        }	
        // 
        if($crear_archivos == "no"){
            // Construir el nombre del archivo.
            $nombre_archivo = trim($nombreNivel) . ' - ' . trim($nombreGrado) . ' ' . trim($nombreSeccion) . ' - ' . trim($nombreAñolectivo) . ' - ' . trim($nombreTurno) . '-Nomina-Matricula.pdf';
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