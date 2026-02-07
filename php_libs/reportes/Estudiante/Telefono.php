<?php
// ruta de los archivos con su carpeta
    // PHP 8: trim((string)...)
    $path_root=trim((string)$_SERVER['DOCUMENT_ROOT']);

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
    // PHP 8: Uso de operador null coalescing (??) para evitar "Undefined array key"
    $codigo_all = $_REQUEST["todos"] ?? '';
    $crear_archivos = $_REQUEST["chkCrearArchivoPdf"] ?? 'no';
    $db_link = $dblink;

    // Inicializar variables globales para evitar advertencias en PHP 8
    $nombreNivel = ''; $nombreGrado = ''; $nombreSeccion = ''; 
    $nombreTurno = ''; $nombreAñoLectivo = ''; $print_nombre_docente = '';

// Establecer formato para la fecha.
    date_default_timezone_set('America/El_Salvador');
    setlocale(LC_TIME,'es_SV');

// CREAR MATRIZ DE MESES Y FECH.
    $meses = ["enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
    $hoy = getdate();
    $NombreDia = $hoy["wday"];  
    $dia = $hoy["mday"];    
    $mes = $hoy["mon"];     
    $año = $hoy["year"];    
    $NombreMes = $meses[(int)$mes - 1];
    $nombresDias = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
    $nombresMeses = [1=>"Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
    $fecha = convertirTexto("Santa Ana, $nombresDias[$NombreDia] $dia de $nombresMeses[$mes] de $año");
    setlocale(LC_MONETARY,"es_ES");

// buscar los datos de la sección y extraer el codigo del nivel.
    $codigo_nivel = substr($codigo_all,0,2);
    consultas(13,0,$codigo_all,'','','',$db_link,''); 
    
    // Declarar globales tras la consulta
    global $nombreNivel, $nombreGrado, $nombreSeccion, $nombreTurno, $nombreAñolectivo, $print_periodo;

// CAPTURAR EL NOMBRE DEL DOCENTE
    consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
    global $result_docente, $print_nombre_docente; 

// buscar la consulta principal
    consultas(4,0,$codigo_all,'','','',$db_link,'');

class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
        global $print_nombre_docente, $nombreNivel, $nombreGrado, $nombreSeccion, $nombreAñoLectivo, $nombreTurno;
        //Logo
        $logo = $_SESSION['logo_uno'] ?? 'logo_default.jpg';
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$logo;
        if(file_exists($img)){
            $this->Image($img,5,5,12,18);
        }
        
        //Fuentes
        $this->SetFont('PoetsenOne','',16);
        $this->Cell(200,6,convertirtexto($_SESSION['institucion']),0,1,'C');
        $this->SetFont('PoetsenOne','',11);
        $this->Cell(200,4,convertirtexto('Nómina de Estudiantes y Padre/madre/Responsable - Acta de Compromiso y Autorización Fotografía'),0,1,'C');
        //$this->Cell(200,4,convertirtexto('Primera Asamblea General Padre/Madre/Responsable - Asistencia'),0,1,'C');
        
        // Docente
        $this->SetXY(25,20);
        $this->SetFont('Arial','B',11);
            $this->Write(6,"Docente Encargado: ");
        $this->SetFont('Comic','',12);
            $this->Write(6,$print_nombre_docente);   
        
        // Nivel
        $this->SetXY(10,25);
        $this->SetFont('Arial','B',11);
            $this->Write(6,"Nivel: ");
        $this->SetFont('Comic','U',11);
            $this->Write(6,$nombreNivel);
        
        // Año Lectivo
        $this->SetXY(160,25);
        $this->SetFont('Arial','B',11);
            $this->Write(6,convertirTexto("Año Lectivo: "));
        $this->SetFont('Comic','U',11);
            $this->Write(6,$nombreAñoLectivo);
        
        // Grado
        $this->SetXY(10,30);
        $this->SetFont('Arial','B',11);
            $this->Write(6,"Grado: ");
        $this->SetFont('Comic','U',11);
            $this->Write(6,$nombreGrado);
        
        // Sección
        $this->SetXY(120,30);
        $this->SetFont('Arial','B',11);
            $this->Write(6,convertirTexto("Sección: "));
        $this->SetFont('Comic','U',11);
            $this->Write(6,"'$nombreSeccion'");
        
        // Turno
        $this->SetXY(160,30);
        $this->SetFont('Arial','B',11);
            $this->Write(6,convertirTexto("Turno: "));
        $this->SetFont('Comic','U',11);
            $this->Write(6,$nombreTurno);
        
        $this->Line(5,25,210,25);
    }

    //Pie de página
    function Footer()
    {
        global $fecha;
        $this->SetY(-10);
        $this->SetFont('Arial','I',8);
        $this->Line(5,270,210,270);
        $this->Cell(0,10,convertirTexto('Página ').$this->PageNo().'/{nb}       '.$fecha,0,0,'C');
    }

    //Tabla coloreada
    function FancyTable($header)
    {
        $this->SetFillColor(128,128,128);
        $this->SetTextColor(255);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        
        // PHP 8: CORRECCIÓN DE ANCHOS
        // Definimos 7 anchos para las 7 columnas (Total aprox 195mm)
        // No, NIE, Alumno, Tel Alum, Encargado, Tel Enc, Firma
        $w = array(5, 15, 55, 20, 55, 20, 25); 

        //  Cabecera principal
        for($i=0;$i<count($header);$i++){
            $this->Cell($w[$i],7,convertirtexto($header[$i]),1,0,'C',1);
        }
        $this->Ln();
        
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
    }
}

//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('P','mm','Letter');
    $data = [];
    
    // Fuentes (Asegúrate que estos archivos existan en la carpeta fonts)
    $pdf->AddFont('Comic','','comic.php');
    $pdf->AddFont('Alte','','AlteHaasGroteskRegular.php');
    $pdf->AddFont('Alte','B','AlteHaasGroteskBold.php');
    $pdf->AddFont('PoetsenOne','','PoetsenOne-Regular.php');

    // Títulos de las columnas (7 columnas)
    $header=array('Nº','N I E','Nombre del alumno', 'Tel. Est.', 'Padre/Madre/Enc.', 'Tel. Enc.', 'Firma');
    
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

    // Inicio contenido
    $pdf->SetFont('Arial','B',14);
    $pdf->SetY(30);
    $pdf->SetX(10);
    $pdf->ln();
    
    $pdf->SetFont('Arial','',8); 
    $pdf->FancyTable($header); 

    // Anchos coincidentes con FancyTable para el loop de datos
    $w = array(5, 15, 55, 20, 55, 20, 25); 
    $fill=false; 
    $i=1;

    while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
        // --- LIMPIEZA DE DATOS (PHP 8 Safe) ---
        $codigo_nie = trim((string)$row['codigo_nie']);
        
        $apellido_alumno_full = convertirtexto(trim((string)$row['apellido_alumno']));
        $nombres_encargado_full = convertirtexto(trim((string)$row['nombres']));
        
        $telefono_alumno = trim((string)$row['telefono_celular']);
        $telefono_encargado = trim((string)$row['telefono_encargado']);

        // --- LÓGICA DE RECORTE ---
        
        // 1. Ajustar Nombre Alumno (Indice 2, Ancho 55)
        $ancho_max_alumno = $w[2] - 2; 
        $alumno_imprimir = $apellido_alumno_full;
        while($pdf->GetStringWidth($alumno_imprimir) > $ancho_max_alumno){
            $alumno_imprimir = substr($alumno_imprimir, 0, -1);
        }

        // 2. Ajustar Nombre Encargado (Indice 4, Ancho 55)
        $ancho_max_encargado = $w[4] - 2;
        $encargado_imprimir = $nombres_encargado_full;
        while($pdf->GetStringWidth($encargado_imprimir) > $ancho_max_encargado){
            $encargado_imprimir = substr($encargado_imprimir, 0, -1);
        }

        // --- IMPRESIÓN (7 CELDAS) ---
        
        $pdf->Cell($w[0], 6.8, $i, 'LR', 0, 'C', $fill);          // 0: Correlativo
        $pdf->Cell($w[1], 6.8, $codigo_nie, 'LR', 0, 'C', $fill); // 1: NIE
        $pdf->Cell($w[2], 6.8, $alumno_imprimir, 'LR', 0, 'L', $fill); // 2: Alumno
        $pdf->Cell($w[3], 6.8, $telefono_alumno, 'LR', 0, 'C', $fill); // 3: Tel Alumno
        $pdf->Cell($w[4], 6.8, $encargado_imprimir, 'LR', 0, 'L', $fill); // 4: Encargado
        $pdf->Cell($w[5], 6.8, $telefono_encargado, 'LR', 0, 'C', $fill); // 5: Tel Encargado
        $pdf->Cell($w[6], 6.8, "", 'LR', 0, 'C', $fill);          // 6: Firma
        
        $pdf->Ln();
        $fill = !$fill;
        $i++;
        
        // Salto de página automático controlado
        if($pdf->GetY() > 250){ // Aprox al final de la página
            $pdf->Cell(array_sum($w),0,'','T');
            $pdf->AddPage();
            $pdf->Ln(4);
            $pdf->FancyTable($header);
        }
    }
    
    // Cerrando Línea Final.
    $pdf->Cell(array_sum($w),0,'','T');
    $pdf->SetFont('Arial','',9); 

    if($crear_archivos == "si"){
        // Lógica de guardado en servidor
        $codigo_destino = 1;
        // Validación PHP 8 para variables globales usadas en CrearDirectorios
        $nombre_ann_lectivo = $nombreAñoLectivo ?? date('Y');
        $codigo_modalidad = $codigo_modalidad ?? '0'; // Valor por defecto si no existe

        // Llamada a función externa (asegúrate que CrearDirectorios sea compatible también)
        CrearDirectorios($path_root, $nombre_ann_lectivo, $codigo_modalidad, $codigo_destino, "");
        
        if(isset($DestinoArchivo) && !file_exists($DestinoArchivo))
        {
            mkdir ($DestinoArchivo, 0777, true); // 0777 y recursive=true
        }
        
        if(isset($DestinoArchivo)) {
            $NuevoDestinoArchivo = $DestinoArchivo . "/";
            $modo = 'F'; 
            $nombre_archivo = trim($print_nombre_docente) . ' ' . trim($nombreGrado) . ' ' . trim($nombreSeccion) . convertirtexto("- N.º TELEFONO.pdf");
            $print_nombre = $NuevoDestinoArchivo . trim($nombre_archivo);
            $pdf->Output($print_nombre, $modo);
        }
    }   
    
    if($crear_archivos == "no"){
        // Salida al navegador
        $nombre_archivo = trim($nombreNivel) . ' - ' . trim($nombreGrado) . ' ' . trim($nombreSeccion) . ' - ' . trim($nombreAñoLectivo) . ' - ' . trim($nombreTurno) . '-Nomina-Matricula.pdf';
        $modo = 'I'; 
        $pdf->Output($nombre_archivo,$modo);
    } else {
        // Respuesta JSON si se creó archivo
        $salidaJson = array("respuesta" => $respuestaOK,
            "mensaje" => $mensajeError,
            "contenido" => $contenidoOK
        );  
        echo json_encode($salidaJson);
    }
?>