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
    $codigo_all = $_REQUEST["todos"] ?? '';
    $db_link = $dblink;

// buscar la consulta y la ejecuta.
    consultas(9,0,$codigo_all,'','','',$db_link,'');

// Inicializar variables
    $print_bachillerato = ''; $nombre_modalidad = '';
    $print_grado = '';        $nombre_grado = '';
    $print_seccion = '';      $nombre_seccion = '';
    $print_ann_lectivo = '';  $nombre_ann_lectivo = '';
    $print_periodo = '';      $print_nombre_docente = '';

//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = convertirtexto('Modalidad: '.trim((string)$row['nombre_bachillerato']));
            $nombre_modalidad = convertirtexto(trim((string)$row['nombre_bachillerato']));
            $print_grado = convertirtexto('Grado:     '.trim((string)$row['nombre_grado']));
            $nombre_grado = convertirtexto(trim((string)$row['nombre_grado']));
            $print_seccion = convertirtexto('Sección:  '.trim((string)$row['nombre_seccion']));
            $nombre_seccion = convertirtexto(trim((string)$row['nombre_seccion']));
            $print_ann_lectivo = convertirtexto('Año Lectivo: '.trim((string)$row['nombre_ann_lectivo']));
            $nombre_ann_lectivo = convertirtexto(trim((string)$row['nombre_ann_lectivo']));
            $print_periodo = convertirtexto('Período: _____');
	        break;
            }    
    // CAPTURAR EL NOMBRE DEL DOCENTE
       consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
       
       while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
           {
               $print_nombre_docente = cambiar_de_del(trim((string)$row['nombre_docente']));
               if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
                   $print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
               }
           }        

// variables y consulta a la tabla principal.
      consultas(4,0,$codigo_all,'','','',$db_link,'');

class PDF extends FPDF
{
    //Cabecera de página
    function Header()
    {
            global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_nombre_docente;
            
            $logo = $_SESSION['logo_uno'] ?? 'logo_default.png'; 
            $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$logo;
            if(file_exists($img)){
                $this->Image($img,10,5,12,15);
            }
            
            $this->SetFont('Arial','B',14);
            $this->Cell(250,5,convertirtexto('MINISTERIO DE EDUCACIÓN'),0,1,'C');
            $this->Cell(250,5,'DATOS DE MATRICULA',0,1,'C');
            $this->SetFont('Arial','',9);
            
            // Imprimir Modalidad y Docente
            $this->RoundedRect(34, 16, 130, 6, 1.5, '1234', '');
            $this->RotatedText(35,20.5,$print_bachillerato,0);
            
            $this->RoundedRect(34, 22, 130, 6, 1.5, '1234', '');
            $this->RotatedText(35,26,'Nombre Docente: ' . $print_nombre_docente,0);
            
            // Cuadro Grado/Sección
            $this->RoundedRect(229, 10, 35, 20, 3.5, '1234', '');
            $this->RotatedText(230,15,$print_grado,0);
            $this->RotatedText(230,19,$print_seccion,0);
            $this->RotatedText(230,23,$print_ann_lectivo,0);
    }

    function Footer()
    {
        $this->SetY(-10);
        $this->SetFont('Arial','I',8);
        $this->Line(10,285,200,285);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

    function FancyTable($header)
    {
        $this->SetFillColor(211,211,211);
        $this->SetTextColor(0);
        $this->SetDrawColor(0,0,0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        
        $w=array(5,25,45,17,30,20,18,15,40,17,20,15,20,60); 
        $w1=array(160,187); 
        
        $header1=array('INFORMACIÓN DEL ESTUDIANTE','INFORMACIÓN DEL RESPONSABLE');
        for($J=0;$J<count($header1);$J++)
            $this->Cell($w1[$J],5,convertirtexto($header1[$J]),1,0,'C',1);
        $this->Ln();
        
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],5,convertirtexto($header[$i]),1,0,'C',1);
        $this->Ln();
        
        $this->SetFillColor(224,235,255);
        $this->SetTextColor(0);
        $this->SetFont('');
    }
}

// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
    $pdf->SetMargins(5, 5, 5);
    $pdf->SetAutoPageBreak(true,5);
    
    $header=array('Nº','NIE | id','Nombre del Estudiante','F.Nac.','Datos/PN: N.º/F/T/L','D/M/D','DUI','Familiar','Responsable','F.Nac.','D/M/D','Nº.Tel.','D/M/D','Dirección');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

    $pdf->SetFont('Arial','B',13); 
    $pdf->SetY(30);
    $pdf->SetX(10);
    $pdf->ln();

    $pdf->SetFont('Arial','',8); 
    $pdf->FancyTable($header); 

    $w=array(5,25,45,17,30,20,18,15,40,17,20,15,20,60); 
    $w2=array(5.8,12,5); 

    $fill = false; 
    $i=1; 
    
    // ANCHOS MÁXIMOS PERMITIDOS PARA TEXTOS QUE SE SOLAPAN
    // Se resta un margen de seguridad (ej. 2mm)
    $ancho_max_estudiante = $w[2] - 1; // 45 - 1 = 44mm
    $ancho_max_responsable = $w[8] - 1; // 40 - 1 = 39mm

    while($row = $result -> fetch(PDO::FETCH_BOTH))
    {
        $pdf->SetFont('Arial','',8); 
        
        // --- PREPARACIÓN DE DATOS ---
        $codigo_nie_y_id = trim((string)$row["codigo_nie"])  . ' | ' .  trim((string)$row["id_alumno"]);
        $nombre_completo = cambiar_de_del((string)$row['nombre_completo_alumno']);
        $direccion = cambiar_de_del((string)$row['direccion_alumno']);
        $fecha_nacimiento = cambiaf_a_normal(trim((string)$row['fecha_nacimiento']));
        
        $pn_num = trim((string)($row['pn_numero'] ?? ''));
        $pn_fol = trim((string)($row['pn_folio'] ?? ''));
        $pn_tom = trim((string)($row['pn_tomo'] ?? ''));
        $pn_lib = trim((string)($row['pn_libro'] ?? ''));
        $datos_pn = "$pn_num | $pn_fol | $pn_tom | $pn_lib";
        
        $codigoDepartamentoNacimiento = trim((string)($row["codigo_departamento_pn"] ?? ''));
        $codigoMunicipioNacimiento = trim((string)($row["codigo_municipio_pn"] ?? ''));
        $codigoDepartamentoDomicilio = trim((string)($row["codigo_departamento"] ?? ''));
        $codigoMunicipioDomicilio = trim((string)($row["codigo_municipio"] ?? ''));
        
        // --- LOGICA PARA EVITAR QUE EL NOMBRE SE MONTE (ESTUDIANTE) ---
        // Recortamos el texto hasta que quepa en el ancho definido
        while($pdf->GetStringWidth($nombre_completo) > $ancho_max_estudiante){
            $nombre_completo = substr($nombre_completo, 0, -1);
        }

        // --- FILTROS Y CONSULTAS DE UBICACIÓN (Estudiante) ---
        $stmt = $db_link->prepare("SELECT nombre_departamento, nombre_municipio, nombre_distrito as descripcion FROM elsalvador WHERE codigo_municipio = :mun AND codigo_departamento = :dep");
        $stmt->execute(['mun' => $codigoMunicipioNacimiento, 'dep' => $codigoDepartamentoNacimiento]); 
        $row_loc = $stmt->fetch(PDO::FETCH_ASSOC);
        $nombres_d_m = $row_loc ? substr(strtolower(trim($row_loc["nombre_departamento"])),0,4)."/".substr(strtolower(trim($row_loc["nombre_municipio"])),0,4)."/".substr(strtolower(trim($row_loc["descripcion"])),0,4) : "";

        // --- DATOS RESPONSABLE ---
        $encargado_dui = trim((string)($row['encargado_dui'] ?? ''));
        $familiar = trim((string)($row['nombre_tipo_parentesco'] ?? ''));
        $nombre_encargado = cambiar_de_del((string)$row['nombres']);
        $encargado_fecha_nacimiento = cambiaf_a_normal(trim((string)($row['encargado_fecha_nacimiento'] ?? '')));
        $telefono_encargado = trim((string)($row['telefono_encargado'] ?? ''));
        $codigoDepartamento = trim((string)($row["encargado_departamento"] ?? ''));
        $codigoMunicipio = trim((string)($row["encargado_municipio"] ?? ''));

        // --- LOGICA PARA EVITAR QUE EL NOMBRE SE MONTE (RESPONSABLE) ---
        while($pdf->GetStringWidth($nombre_encargado) > $ancho_max_responsable){
            $nombre_encargado = substr($nombre_encargado, 0, -1);
        }

        // --- FILTROS Y CONSULTAS DE UBICACIÓN (Responsable) ---
        $stmt->execute(['mun' => $codigoMunicipio, 'dep' => $codigoDepartamento]); 
        $row_loc = $stmt->fetch(PDO::FETCH_ASSOC);
        $nombres_d_m_e = $row_loc ? substr(strtolower(trim($row_loc["nombre_departamento"])),0,4)."/".substr(strtolower(trim($row_loc["nombre_municipio"])),0,4)."/".substr(strtolower(trim($row_loc["descripcion"])),0,4) : "";

        // --- FILTROS Y CONSULTAS DE UBICACIÓN (Domicilio) ---
        $stmt->execute(['mun' => $codigoMunicipioDomicilio, 'dep' => $codigoDepartamentoDomicilio]); 
        $row_loc = $stmt->fetch(PDO::FETCH_ASSOC);
        $nombres_d_m_d = $row_loc ? substr(strtolower(trim($row_loc["nombre_departamento"])),0,4)."/".substr(strtolower(trim($row_loc["nombre_municipio"])),0,4)."/".substr(strtolower(trim($row_loc["descripcion"])),0,4) : "";

        // --- IMPRESIÓN DE CELDAS ---
        // Guardamos la posición Y inicial para saber la altura
        $y_inicio = $pdf->GetY();
        $x_inicio = $pdf->GetX();

        // 1. Imprimir Estudiante
        $pdf->Cell($w[0],$w2[0],$i,'0',0,'C',$fill);
        $pdf->Cell($w[1],$w2[0],$codigo_nie_y_id,'LR',0,'C',$fill);
        $pdf->Cell($w[2],$w2[0],$nombre_completo,'R',0,'L',$fill); 
        $pdf->Cell($w[3],$w2[0],$fecha_nacimiento,'R',0,'C',$fill);   
        $pdf->Cell($w[4],$w2[0],$datos_pn,'R',0,'C',$fill);   
        $pdf->Cell($w[5],$w2[0],$nombres_d_m,'R',0,'C',$fill);   

        // 2. Imprimir Responsable
        $pdf->Cell($w[6],$w2[0],$encargado_dui,'R',0,'L',$fill);
        $pdf->Cell($w[7],$w2[0],$familiar,'R',0,'C',$fill);
        $pdf->Cell($w[8],$w2[0],$nombre_encargado,'R',0,'L',$fill);    
        $pdf->Cell($w[9],$w2[0],$encargado_fecha_nacimiento,'R',0,'C',$fill);    
        $pdf->Cell($w[10],$w2[0],$nombres_d_m_e,'R',0,'C',$fill);   
        $pdf->Cell($w[11],$w2[0],$telefono_encargado,'R',0,'R',$fill);    
        
        // 3. Dirección
        $pdf->Cell($w[12],$w2[0],$nombres_d_m_d,'R',0,'C',$fill);   
        
        // MultiCell se imprime al final de la fila. 
        // IMPORTANTE: Al ser multicell, el cursor bajará.
        $pdf->MultiCell($w[13],$w2[2],$direccion,'RT','J',$fill);
        
        // --- CORRECCIÓN NUMERACIÓN ---
        // Se ELIMINÓ el bloque "if($total_caracteres > 50){ $i=$i+1; }" que causaba el salto de números.
        // Ahora el número de la lista ($i) es estrictamente 1 por alumno.

        // Control de salto de página
        // Usamos GetY() para un control más seguro que contar filas manualmente,
        // ya que el MultiCell varía la altura.
        if($pdf->GetY() > 330){ // Aproximadamente el final de una hoja legal apaisada (355mm total - margen)
            $pdf->Cell(array_sum($w),0,'','T');   
            $pdf->AddPage();
            $pdf->SetY(30);
            $pdf->SetX(10);
            $pdf->Ln(); 
            $pdf->FancyTable($header);
        }

        $fill=!$fill;
        $i++;
    }
    
    $pdf->Cell(array_sum($w),0,'','T');
    $pdf->ln();
    $pdf->Cell(array_sum($w)+(9*10),0,'','T');

    $modo = 'I'; 
    $print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-DM.pdf';
    $pdf->Output($print_nombre,$modo);
?>