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
	$fecha_desde = $_REQUEST['fecha_desde'];
	$fecha_hasta = $_REQUEST['fecha_hasta'];
    // Para el reporte anual tomamos todo el año o el rango seleccionado
	$nombre_ann_lectivo = substr($_REQUEST['fecha_desde'],0,4);
	$codigo_contratacion = $_REQUEST['codigo_contratacion'];
	$codigo_turno = $_REQUEST['codigo_turno'];
	$codigo_contratacion_turno = $codigo_contratacion . $codigo_turno;
	$db_link = $dblink;

	// Calcular el Disponible según Tipo de Contratación.
	$calculo_horas = 5;
	if($codigo_contratacion == "05"){ // PAGADOS POR EL CDE.
		$calculo_horas = 8;
	}

// ============================================================================================
// 1. OPTIMIZACIÓN: CARGAR TODOS LOS DATOS EN MEMORIA (Evita el error Time Out)
// ============================================================================================

    // Listado de Personal
    $query_nombres_personal = "SELECT ps.codigo_personal, p.nombres, p.apellidos, 
                btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_c
                FROM personal_salario ps
                INNER JOIN personal p ON p.id_personal = ps.codigo_personal
                WHERE p.codigo_estatus = '01' and btrim(ps.codigo_tipo_contratacion || ps.codigo_turno) = '$codigo_contratacion_turno'
                ORDER BY nombre_c";
    $consulta_nombres = $dblink -> query($query_nombres_personal);
    
    // Arrays para guardar la data
    $personal_data = array();
    while($row = $consulta_nombres->fetch(PDO::FETCH_ASSOC)){
        $personal_data[] = $row;
    }

    // Consulta Masiva de Licencias (Una sola consulta para todos)
    // Agrupamos por personal y tipo de licencia para sumarizar
    $matriz_licencias = array();
    $query_sum_global = "SELECT codigo_personal, codigo_licencia_permiso, sum(dia) as dia, sum(hora) as hora, sum(minutos) as minutos 
                     FROM personal_licencias_permisos 
                     WHERE fecha >= '$fecha_desde' AND fecha <= '$fecha_hasta' 
                     AND codigo_contratacion = '$codigo_contratacion' 
                     AND codigo_turno = '$codigo_turno'
                     GROUP BY codigo_personal, codigo_licencia_permiso";
    
    $consulta_sum = $dblink->query($query_sum_global);
    while($row = $consulta_sum->fetch(PDO::FETCH_ASSOC)){
        // Guardamos: [id_personal][id_tipo_licencia] = array(datos)
        $matriz_licencias[$row['codigo_personal']][$row['codigo_licencia_permiso']] = $row;
    }

    // Obtener info encabezado (turno, contratacion)
    $query_info = "SELECT tur.nombre as nombre_turno, tc.nombre as nombre_contratacion 
                   FROM turno tur, tipo_contratacion tc 
                   WHERE tur.codigo = '$codigo_turno' AND tc.codigo = '$codigo_contratacion'";
    $consulta_info = $dblink->query($query_info);
    $info_header = $consulta_info->fetch(PDO::FETCH_ASSOC);
    $nombre_turno = trim($info_header['nombre_turno']);
    $nombre_contratacion = trim($info_header['nombre_contratacion']);

// ============================================================================================
// CONFIGURACIÓN DE CÓDIGOS (AJUSTAR SEGÚN TU BASE DE DATOS)
// ============================================================================================
    // Define aquí qué código de la BD corresponde a cada columna del reporte nuevo
    $COD_90_DIAS_GOCE     = '02'; // Enfermedad / Incapacidad con goce
    $COD_SIN_GOCE_SUELDO  = '09'; // Enfermedad sin goce (Verifica este código en tu BD)
    $COD_20_DIAS_PARIENTE = '03'; // Duelo o pariente
    $COD_5_DIAS_PERSONAL  = '04'; // Motivos personales con goce
    $COD_60_DIAS_SIN_GOCE = '06'; // Motivos personales sin goce (Verifica este código)
    $COD_MATERNIDAD       = '05'; // Maternidad
    $COD_LLEGADAS_TARDIAS = '07'; // Llegadas tardías
    $COD_INJUSTIFICADA    = '08'; // Inasistencia sin justificar

// ============================================================================================
// CLASE PDF
// ============================================================================================
class PDF extends FPDF
{
function Header(){
    global $nombre_ann_lectivo, $nombre_turno, $nombre_contratacion;
    
    // --- 1. LOGOS Y TITULOS ---
    $this->SetFont('Arial','B',10);
    $this->Cell(0,5,'MINISTERIO DE EDUCACION',0,1,'C');
    $this->Cell(0,5,'DIRECCION DEPARTAMENTAL DE EDUCACION DE SANTA ANA',0,1,'C');
    $this->Cell(0,5,'CONTROL ANUAL DE ASISTENCIAS Y PERMISOS DEL PERSONAL DOCENTE',0,1,'C');
    
    $this->Ln(2);
    
    // --- 2. DATOS INSTITUCIONALES ---
    $this->SetFont('Arial','B',8);
    $this->Cell(35,5,mb_convert_encoding('CÓDIGO',"ISO-8859-1","UTF-8"),0,0,'L');
    $this->SetFont('Arial','',8);
    $this->Cell(100,5,': '.$_SESSION['codigo'],0,1,'L');
    
    $this->SetFont('Arial','B',8);
    $this->Cell(35,5,'CENTRO EDUCATIVO',0,0,'L');
    $this->SetFont('Arial','',8);
    $this->Cell(100,5,': '.mb_convert_encoding($_SESSION['institucion'],"ISO-8859-1","UTF-8"),0,1,'L');
    
    $this->SetFont('Arial','B',8);
    $this->Cell(35,5,'TURNO',0,0,'L');
    $this->SetFont('Arial','',8);
    $this->Cell(100,5,': '.$nombre_turno,0,0,'L');

    $this->SetX(230);
    $this->SetFont('Arial','B',10);
    $this->Cell(20,5,mb_convert_encoding('AÑO: '.$nombre_ann_lectivo,"ISO-8859-1","UTF-8"),0,1,'L');
    
	// Logo Derecha (Escudo) - Ajusta la ruta si es necesario
    $img = $_SESSION['path_root'].'/registro_academico/img/logo_mined.png'; 
    if(file_exists($img)) $this->Image($img,240,10,25);

    $this->Ln(2);
    
    // ====================================================================
    // 3. CONFIGURACIÓN DE DIMENSIONES (AJUSTA AQUÍ TUS TAMAÑOS)
    // ====================================================================
    
    $this->SetFillColor(255,255,255);
    $this->SetLineWidth(0.2);
    $this->SetFont('Arial','B',6);
    
    $x = $this->GetX();
    $y = $this->GetY();
    
    // --- ANCHOS DE COLUMNAS (Aumentados según tu petición) ---
    // Total disponible hoja carta horizontal ~250mm a 260mm
    
    $w_no  = 8;   // Antes 6 (Aumentado un poco)
    $w_nom = 85;  // Antes 70 (AUMENTADO BASTANTE para nombres largos)
    
    // Grupos de datos (g1..g8)
    $w_g1 = 20;   // 90 dias (Antes 16) - AUMENTADO
    $w_g2 = 20;   // Sin goce (Igual)
    $w_g3 = 15;   // 20 dias (Igual)
    $w_g4 = 24;   // 5 dias (Igual)
    $w_g5 = 24;   // 60 dias (Igual)
    $w_g6 = 14;   // 112 dias (Antes 10) - AUMENTADO
    $w_g7 = 16;   // Tardias (Igual)
    $w_g8 = 24;   // Injust (Igual)
    
    // GUARDAR ANCHOS EN GLOBAL PARA QUE EL CUERPO DE LA TABLA COINCIDA
    $GLOBALS['W_COLS'] = array($w_no, $w_nom, $w_g1, $w_g2, $w_g3, $w_g4, $w_g5, $w_g6, $w_g7, $w_g8);

    // --- ALTOS DE FILAS (Modifica estos valores para cambiar la altura) ---
    $h1 = 8; // Títulos Superiores
    $h2 = 18; // Descripciones (Texto largo)
    $h3 = 5;  // Unidades (DIAS/HORAS)
    
    // Altura total calculada automáticamente (Esto arregla la línea cortada)
    $h_total = $h1 + $h2 + $h3; 

    // ====================================================================
    // DIBUJADO DE LA TABLA
    // ====================================================================

    // --- COLUMNAS IZQUIERDAS (No y Nombre) ---
    // Usamos $h_total para asegurar que la línea baje hasta el final
    
    // Columna No
    $this->Rect($x, $y, $w_no, $h_total); 
    $this->SetXY($x, $y + ($h_total/2) - 2); // Centrado vertical calculado
    $this->Cell($w_no, 4, 'No', 0, 0, 'C');
    
    // Columna Nombre
    $this->Rect($x + $w_no, $y, $w_nom, $h_total);
    $this->SetXY($x + $w_no, $y + ($h_total/2) - 2); // Centrado vertical calculado
    $this->Cell($w_nom, 4, 'NOMBRE DEL DOCENTE', 0, 0, 'C');

    $curX = $x + $w_no + $w_nom;

    // --- FILA 1: TÍTULOS SUPERIORES ---
    // Usamos Rect() manual para mantener control total de bordes

    $this->Rect($curX, $y, $w_g1, $h1); $this->SetXY($curX, $y);
    $this->MultiCell($w_g1, 3, "CON GOCE DE\nSUELDO 90 DIAS", 0, 'C'); 
    $curX += $w_g1;

    $this->Rect($curX, $y, $w_g2, $h1); $this->SetXY($curX, $y);
    $this->MultiCell($w_g2, 3, "SIN GOCE DE\nSUELDO", 0, 'C');
    $curX += $w_g2;

    $this->Rect($curX, $y, $w_g3, $h1); $this->SetXY($curX, $y);
    $this->MultiCell($w_g3, 3, "20 DIAS\nCON GOCE", 0, 'C');
    $curX += $w_g3;

    $this->Rect($curX, $y, $w_g4, $h1); $this->SetXY($curX, $y);
    $this->MultiCell($w_g4, 3, "5 DIAS CON GOCE", 0, 'C');
    $curX += $w_g4;

    $this->Rect($curX, $y, $w_g5, $h1); $this->SetXY($curX, $y);
    $this->MultiCell($w_g5, 3, "60 DIAS SIN GOCE", 0, 'C');
    $curX += $w_g5;

    $this->Rect($curX, $y, $w_g6, $h1); $this->SetXY($curX, $y);
    $this->MultiCell($w_g6, 3, "112 DIAS\nCON GOCE", 0, 'C');
    $curX += $w_g6;
    
    // Celdas vacías superiores (Tardias e Injustificadas)
    // Solo dibujamos lineas Izq, Der, Top (LRT) para dejar abierto abajo hacia la descripcion
    //$this->Cell($w_g7, $h1, '', 'LRT', 0, 'C'); 
    //$this->Cell($w_g8, $h1, '', 'LRT', 0, 'C');

    // --- FILA 2: DESCRIPCIONES LARGAS ---
    $this->SetY($y + $h1);
    $curX = $x + $w_no + $w_nom; // Reiniciar X
    $this->SetFont('Arial','',6); // Letra más pequeña

    // Función rápida para celda descripcion con borde completo
    // FPDF MultiCell dibuja el borde si pasamos '1'
    
    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g1, 3, "Enfermedad con\nCertificado\nMedico e\nIncapacidades\nMedicas con\ngoce", 1, 'C');
    $curX += $w_g1;
    
    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g2, 3, "Enfermedad con\nCertificado\nMedico e\nIncapacidades\nMedicas sin\ngoce", 1, 'C');
    $curX += $w_g2;

    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g3, 3, "Enfermedad\nde \nPariente\nCercano \no\nDuelo", 1, 'C');
    $curX += $w_g3;

    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g4, 4.5, "Permiso \npor motivos\nPersonales \ncon goce", 1, 'C');
    $curX += $w_g4;

    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g5, 4.5, "Permiso \npor motivos\nPersonales \nSin goce", 1, 'C');
    $curX += $w_g5;

    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g6, 4.5, "\nMATERNI\nDAD 112\nDIAS", 1, 'C');
    $curX += $w_g6;

    // Tardias (Cerramos el cuadro superior con Rect, y escribimos texto)
    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g7, 6, "\nLLEGADAS\nTARDIAS", 1, 'C');
    // Forzamos el rectángulo para asegurar que cierre bien con el de arriba
    $this->Rect($curX, $y, $w_g7, $h1+$h2); 
    $curX += $w_g7;

    // Injustificadas
    $this->SetXY($curX, $y+$h1);
    $this->MultiCell($w_g8, 6, "\nINASISTENCIA SIN\nJUSTIFICAR", 1, 'C');
    $this->Rect($curX, $y, $w_g8, $h1+$h2);

    // --- FILA 3: UNIDADES (DIAS, HORAS) ---
    $y_units = $y + $h1 + $h2;
    $curX = $x + $w_no + $w_nom;
    
    $this->SetFont('Arial','B',6);
    $this->SetXY($curX, $y_units);
    
    // Dibujar celdas pequeñas de unidades
    $this->Cell($w_g1/2, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g1/2, $h3, 'HORAS', 1, 0, 'C');
    $this->Cell($w_g2/2, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g2/2, $h3, 'HORAS', 1, 0, 'C');
    
    $this->Cell($w_g3, $h3, 'DIAS', 1, 0, 'C');
    
    $this->Cell($w_g4/3, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g4/3, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g4/3, $h3, 'MIN', 1, 0, 'C');
    
    $this->Cell($w_g5/3, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g5/3, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g5/3, $h3, 'MIN', 1, 0, 'C');
    
    $this->Cell($w_g6, $h3, 'DIAS', 1, 0, 'C');
    
    $this->Cell($w_g7/2, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g7/2, $h3, 'MIN', 1, 0, 'C');
    
    $this->Cell($w_g8/3, $h3, 'DIAS', 1, 0, 'C'); $this->Cell($w_g8/3, $h3, 'HORAS', 1, 0, 'C'); $this->Cell($w_g8/3, $h3, 'MIN', 1, 0, 'C');

    $this->Ln($h3);
}
function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

// ============================================================================================
// GENERACIÓN DEL REPORTE
// ============================================================================================

    $pdf=new PDF('L','mm','Letter'); 
    $pdf->AliasNbPages();
    $pdf->AddPage();
    $pdf->SetFont('Arial','',8);

    $num = 1;
    $fill = false; // Alternar colores si quieres
    
    // Recuperamos los anchos definidos en el Header para alinear columnas
    // $w_no, $w_nom, $w_g1, $w_g2, $w_g3, $w_g4, $w_g5, $w_g6, $w_g7, $w_g8
    $w = $GLOBALS['W_COLS']; 

    // Helper para obtener datos de la matriz precargada y formatear
    // Retorna array(D, H, M)
    function getVals($matriz, $id_p, $cod_lic, $calc_h){
        $d = 0; $h = 0; $m = 0;
        if(isset($matriz[$id_p][$cod_lic])){
            $row = $matriz[$id_p][$cod_lic];
            $d = $row['dia'];
            $h = $row['hora'];
            $m = $row['minutos'];
        }
        // Convertimos todo a minutos totales y luego separamos según formato
        $total_min = ($d * $calc_h * 60) + ($h * 60) + $m;
        
        if($total_min == 0) return array('','',''); // Celdas vacías si es 0
        
        // Usamos las funciones incluidas en tu sistema (segundosToCadena...)
        // Nota: tu funcion 'segundosToCadenaD' devuelve el numero de días
        $res_d = segundosToCadenaD($total_min, $calc_h);
        $res_h = segundosToCadenaH($total_min, $calc_h);
        $res_m = segundosToCadenaM($total_min, $calc_h);
        
        // Limpieza visual: si es 0 no imprimir
        if($res_d == 0) $res_d = '';
        if($res_h == 0) $res_h = '';
        if($res_m == 0) $res_m = '';
        
        return array($res_d, $res_h, $res_m);
    }

    foreach($personal_data as $docente){
        $id_p = $docente['codigo_personal'];
        $nombre = $docente['nombre_c'];
        
        // Obtener valores para cada columna
        $val_90 = getVals($matriz_licencias, $id_p, $COD_90_DIAS_GOCE, $calculo_horas);
        $val_sin = getVals($matriz_licencias, $id_p, $COD_SIN_GOCE_SUELDO, $calculo_horas);
        $val_20 = getVals($matriz_licencias, $id_p, $COD_20_DIAS_PARIENTE, $calculo_horas);
        $val_5  = getVals($matriz_licencias, $id_p, $COD_5_DIAS_PERSONAL, $calculo_horas);
        $val_60 = getVals($matriz_licencias, $id_p, $COD_60_DIAS_SIN_GOCE, $calculo_horas);
        $val_mat = getVals($matriz_licencias, $id_p, $COD_MATERNIDAD, $calculo_horas);
        $val_tar = getVals($matriz_licencias, $id_p, $COD_LLEGADAS_TARDIAS, $calculo_horas);
        $val_inj = getVals($matriz_licencias, $id_p, $COD_INJUSTIFICADA, $calculo_horas);

        // --- IMPRIMIR FILA ---
        $pdf->Cell($w[0], 6, $num, 1, 0, 'C');
        $pdf->Cell($w[1], 6, mb_convert_encoding($nombre,"ISO-8859-1","UTF-8"), 1, 0, 'L');
        
        // 90 dias (D, H)
        $sub = $w[2]/2;
        $pdf->Cell($sub, 6, $val_90[0], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_90[1], 1, 0, 'C');

        // Sin goce (D, H)
        $sub = $w[3]/2;
        $pdf->Cell($sub, 6, $val_sin[0], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_sin[1], 1, 0, 'C');

        // 20 dias (D)
        $pdf->Cell($w[4], 6, $val_20[0], 1, 0, 'C');

        // 5 dias (D, H, M)
        $sub = $w[5]/3;
        $pdf->Cell($sub, 6, $val_5[0], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_5[1], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_5[2], 1, 0, 'C');

        // 60 dias (D, H, M)
        $sub = $w[6]/3;
        $pdf->Cell($sub, 6, $val_60[0], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_60[1], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_60[2], 1, 0, 'C');

        // 112 dias (D)
        $pdf->Cell($w[7], 6, $val_mat[0], 1, 0, 'C');

        // Tardias (H, M)
        $sub = $w[8]/2;
        $pdf->Cell($sub, 6, $val_tar[1], 1, 0, 'C'); // Ojo: Tardias suele ser Horas y Minutos, no dias. Uso indice 1 y 2
        $pdf->Cell($sub, 6, $val_tar[2], 1, 0, 'C');

        // Injust (D, H, M)
        $sub = $w[9]/3;
        $pdf->Cell($sub, 6, $val_inj[0], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_inj[1], 1, 0, 'C');
        $pdf->Cell($sub, 6, $val_inj[2], 1, 0, 'C');

        $pdf->Ln();
        $num++;
    }

// ============================================================================================
// SECCIÓN DE FIRMAS (AL FINAL DEL REPORTE)
// ============================================================================================
    
    // Verificar espacio disponible en la página
    if($pdf->GetY() > 160){ // Si estamos muy abajo, saltar página
        $pdf->AddPage();
    }
    
    $pdf->Ln(25); // Espacio entre tabla y firmas
    $y_sig = $pdf->GetY();
    
    $pdf->SetFont('Arial','',10);
    
    // --- Firma Izquierda (Director) ---
    $x_left = 30;
    $w_sig = 80;
    $pdf->SetXY($x_left, $y_sig);
    $pdf->Cell(5, 5, 'F', 0, 0, 'L'); // La "F" al inicio
    $pdf->Line($x_left + 5, $y_sig + 4, $x_left + $w_sig, $y_sig + 4); // Linea de firma
    
    $pdf->SetXY($x_left, $y_sig + 6);
    $pdf->Cell($w_sig, 5, 'Director:__________________________________', 0, 1, 'L');
    $pdf->SetX($x_left);
    $pdf->Cell($w_sig, 5, 'Telefono:__________________________________', 0, 1, 'L');

    // --- Firma Derecha (Subdirector) ---
    $x_right = 160;
    $pdf->SetXY($x_right, $y_sig);
    $pdf->Cell(5, 5, 'F', 0, 0, 'L'); // La "F" al inicio
    $pdf->Line($x_right + 5, $y_sig + 4, $x_right + $w_sig, $y_sig + 4); // Linea de firma
    
    $pdf->SetXY($x_right, $y_sig + 6);
    $pdf->Cell($w_sig, 5, 'Subdirector:________________________________', 0, 1, 'L');
    $pdf->SetX($x_right);
    $pdf->Cell($w_sig, 5, 'Telefono:___________________________________', 0, 1, 'L');


    $pdf->Output();
?>