<?php
// 1. Configuración Inicial y Rutas
    $path_root = trim($_SERVER['DOCUMENT_ROOT']);
    
    // Iniciar sesión si no está iniciada (Estándar PHP 8)
    if (session_status() === PHP_SESSION_NONE) {
        //session_start();
    }

// 2. Inclusión de librerías
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
    include($path_root."/registro_academico/includes/DeNumero_a_Letras.php");
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");

// 3. Variables Iniciales (Con validación PHP 8 usando ??)
    $respuestaOK = true;
    $mensajeError = "";
    $contenidoOK = "";

    // Captura segura de variables
    $codigo_all       = $_REQUEST["todos"] ?? '';
    $conducta         = $_REQUEST['lstconducta'] ?? '';
    $codigo_matricula = $_REQUEST['txtcodmatricula'] ?? '';
    $codigo_alumno    = $_REQUEST['txtidalumno'] ?? '';
    $estudias         = $_REQUEST['lstestudia'] ?? '';
    $traslado         = $_REQUEST['txttraslado'] ?? '';
    $mostrar_traslado = $_REQUEST["chktraslado"] ?? '';
    $firma            = $_REQUEST["chkfirma"] ?? '';
    $sello            = $_REQUEST["chksello"] ?? '';
    
    $db_link = $dblink;

// 4. Ejecución de Consulta de Encabezado
    consultas(18, 0, $codigo_all, '', '', '', $db_link, '');
    
    // Inicializar variables para evitar errores
    $print_bachillerato = ''; 
    $print_grado = '';
    $print_seccion = '';
    $print_ann_lectivo = '';
    $codigo_modalidad = '';
    $codigo_grado = '';

    while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH)) {
        // Usamos textoBD porque estos datos vienen de la base de datos (ISO)
        // y los queremos en UTF-8 para manipularlos en PHP
        $print_bachillerato = textoBD(trim($row['nombre_bachillerato']));
        $print_grado        = textoBD(trim($row['nombre_grado']));
        $print_seccion      = textoBD(trim($row['nombre_seccion']));
        $print_ann_lectivo  = textoBD(trim($row['nombre_ann_lectivo']));
        $codigo_modalidad   = trim($row['codigo_bachillerato']);
        $codigo_grado       = trim($row['codigo_grado']);
    }

// 5. Configuración de Fecha (Reemplazo de strftime para PHP 8)
    date_default_timezone_set('America/El_Salvador');
    
    $meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
    
    $dia = date("d");
    $mes = $meses[(int)date('n') - 1]; 
    $año = date("Y");

// 6. Clase PDF
class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
        // Recuperamos variables de sesión y las pasamos a UTF-8 por seguridad
        $institucion = isset($_SESSION['institucion']) ? textoBD($_SESSION['institucion']) : 'Institución Desconocida';
        $nombre_distrito = isset($_SESSION['nombre_distrito']) ? textoBD($_SESSION['nombre_distrito']) : ''; 
        
        //Logo
        $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo-sv.png';
        if(file_exists($img)){
            $this->Image($img, 95, 15, 26, 26);
        }

        //Arial bold 12
        $this->SetFont('Arial','B',12);
        
        //Título
        $this->SetY(45);
        // Usamos textoPDF para IMPRIMIR (UTF-8 -> ISO)
        // Nota: Escribimos las tildes normales aquí
        $this->Cell(180, 5, textoPDF('MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA'), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('REPÚBLICA DE EL SALVADOR'), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('DIRECCIÓN DEPARTAMENTAL DE EDUCACIÓN DE SANTA ANA'), 0, 1, 'C');
        
        // AGREGANDO EL DISTRITO
        if(!empty($nombre_distrito)){
            $this->Cell(180, 5, textoPDF($nombre_distrito), 0, 1, 'C');
        }

        $this->ln(2); 
        $this->Cell(180, 5, textoPDF($institucion), 0, 1, 'C');
    }

    //Pie de página
    function Footer()
    {
        global $firma, $sello;
        
        $imagen_firma = $_SESSION['imagen_firma'] ?? '';
        $imagen_sello = $_SESSION['imagen_sello'] ?? '';
        $nombre_director = $_SESSION['nombre_director'] ?? '';

        $this->SetY(-30);
        $this->SetFont('Arial','I',12);
        
        //Firma
        if($firma == 'yes' && !empty($imagen_firma)){
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_firma;
            if(file_exists($img)){
                $this->Image($img, 80, 232, 70, 15);
            }
        }
        
        // Sello
        if($sello == 'yes' && !empty($imagen_sello)){
            $img_sello = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$imagen_sello;
            if(file_exists($img_sello)){
                $this->Image($img_sello, 125, 225, 30, 30);
            }
        }

        //Nombre Director
        // Limpiamos el nombre (por si viene de BD en ISO)
        $director_clean = textoBD($nombre_director);
        if(function_exists('cambiar_de_del')){
            $director_clean = cambiar_de_del($director_clean);
        }
        
        $this->Cell(180, 5, ($director_clean), 0, 1, 'C');
        $this->Cell(180, 5, textoPDF('Director(a) del Centro Educativo'), 0, 1, 'C');
    }
}

//************************************************************************************************************************
// 7. Configuración del documento PDF
    $pdf = new PDF('P','mm','Letter');
    $pdf->SetMargins(20, 20);
    $pdf->SetAutoPageBreak(true, 5);
    $pdf->AliasNbPages();
    $pdf->AddPage();

    $pdf->SetXY(15,80);
    $pdf->SetFont('Arial','',12); 

//  Preparación de variables globales
    $nombre_departamento = textoBD($_SESSION['nombre_departamento'] ?? '');
    $nombre_municipio    = textoBD($_SESSION['nombre_municipio'] ?? '');
    
    if(function_exists('cambiar_de_del')){
        $nombre_departamento = cambiar_de_del($nombre_departamento);
        $nombre_municipio = cambiar_de_del($nombre_municipio);
    }

    // Procesar modalidad
    $nombre_modalidad = '';
    if(!empty($print_bachillerato)){
        $porciones = explode(" ", $print_bachillerato);
        // Validar índice para PHP 8
        $raw_modalidad = $porciones[1] ?? $print_bachillerato;
        
        if(function_exists('cambiar_de_del')){
            $nombre_modalidad = cambiar_de_del(trim($raw_modalidad));
        } else {
            $nombre_modalidad = trim($raw_modalidad);
        }
    }
    
    $año_anterior = $año;

/**********************************************************************************************************************************************************/
// 8. Consultar y Generar Párrafos
    consultas_alumno(3, 0, $codigo_all, $codigo_alumno, $codigo_matricula, '', $db_link, '');      
    
    $nombre_estudiante = 'Estudiante'; 

    while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
        // 1. LIMPIEZA DE DATOS (ISO -> UTF-8)
        $nombre_estudiante = textoBD(trim($row['nombre_a_pm']));
        $codigo_nie = textoBD(trim($row['codigo_nie']));
        
        $institucion_nombre = textoBD($_SESSION['institucion'] ?? '');

        // 2. CONSTRUCCIÓN DE PÁRRAFOS (Todo en UTF-8)
        // Puedes usar tildes con confianza aquí
        
        $primer_parrafo = 'El infrascrito(a) director(a) del '.$institucion_nombre.' del municipio de ' . $nombre_municipio. ', Departamento de ' . $nombre_departamento.'.';
        
        $segundo_parrafo = 'HACE CONSTAR QUE: '. $nombre_estudiante .', Con Número de Identificación Estudiantil (NIE): '.$codigo_nie
            .' ha culminado satisfactoriamente sus estudios de bachillerato en la modalidad de '. textoBD($nombre_modalidad) .' en este Centro Educativo, dando cumplimiento a todos los requisitos exigidos por el Ministerio de Educación para la legalización del Título de Bachillerato '. textoBD($nombre_modalidad) . '.';
        
        $tercer_parrafo = 'Por tanto, su título que le acredita como Bachiller de la República, se encuentra en trámite de legalización. Ante ello, el Ministerio de Educación, Ciencia y Tecnología está haciendo las gestiones pertinentes con base a la solicitud enviada por nuestra institución'
            .', para la emisión del respectivo título en la mayor brevedad posible, el cual tendrá validez a partir del 26 de noviembre del año '. $año_anterior .'.';
        
        $cuarto_parrafo = 'Y para los usos que el/la interesado(a) estime conveniente, se le extiende la presente constancia, en el municipio de '. $nombre_municipio . ' departamento de '. $nombre_departamento.', '
            . 'a los '. strtolower(num2letras($dia)).' días de '.$mes.' de '.strtolower(num2letras($año)).'.';
        
        // 3. IMPRESIÓN (UTF-8 -> ISO para PDF)
        $pdf->MultiCell(0, 8, textoPDF($primer_parrafo), 0, "J");
        $pdf->ln();
        $pdf->MultiCell(0, 8, textoPDF($segundo_parrafo), 0, "J");
        $pdf->ln();
        $pdf->MultiCell(0, 8, textoPDF($tercer_parrafo), 0, "J");
        $pdf->ln();
        $pdf->MultiCell(0, 8, textoPDF($cuarto_parrafo), 0, "J");
        
        break; 
    }

// 9. Salida del PDF
    $nombre_archivo = $nombre_estudiante . '.pdf';
    // Sanitizar nombre de archivo
    $nombre_archivo = preg_replace('/[^A-Za-z0-9 \-]/', '', $nombre_archivo) . '.pdf';

    $modo = 'I'; 
    $pdf->Output($nombre_archivo, $modo);
?>