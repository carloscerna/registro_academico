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
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
// buscar la consulta y la ejecuta.
consultas(9,0,$codigo_all,'','','',$db_link,'');
//  imprimir datos del bachillerato.
     while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
            {
            $print_bachillerato = convertirtexto('Modalidad: '.trim($row['nombre_bachillerato']));
            $nombre_modalidad = convertirtexto(trim($row['nombre_bachillerato']));
            $print_grado = convertirtexto('Grado:     '.trim($row['nombre_grado']));
            $nombre_grado = convertirtexto(trim($row['nombre_grado']));
            $print_seccion = convertirtexto('Sección:  '.trim($row['nombre_seccion']));
            $nombre_seccion = convertirtexto(trim($row['nombre_seccion']));
            $print_ann_lectivo = convertirtexto('Año Lectivo: '.trim($row['nombre_ann_lectivo']));
            $nombre_ann_lectivo = convertirtexto(trim($row['nombre_ann_lectivo']));
            $print_periodo = convertirtexto('Período: _____');
	    break;
            }    
    // CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
       // buscar la consulta y la ejecuta.
       consultas_docentes(1,0,$codigo_all,'','','',$db_link,'');
       $print_nombre_docente = "";
       while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
           {
               $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
               
               if (!mb_check_encoding($print_nombre_docente, 'LATIN1')){
                   $print_nombre_docente = mb_convert_encoding($print_nombre_docente,'LATIN1');
               }
          
           }        
// variables y consulta a la tabla.
      consultas(4,0,$codigo_all,'','','',$db_link,'');
class PDF extends FPDF
{
//Cabecera de página
function Header()
{
        //  Variables globales.
        global $print_bachillerato, $print_grado, $print_seccion, $print_ann_lectivo, $print_nombre_docente;
//Logo
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/'.$_SESSION['logo_uno'];
    $this->Image($img,10,5,12,15);
    //Arial bold 15
    $this->SetFont('Arial','B',14);
    //Movernos a la derecha
    //$this->Cell(20);
    //Título
    $this->Cell(250,5,convertirtexto('MINISTERIO DE EDUCACIÓN'),0,1,'C');
    $this->Cell(250,5,'DATOS DE MATRICULA',0,1,'C');
    $this->SetFont('Arial','',9);
    // Imprimir Modalidad y Asignatura.
    $this->RoundedRect(34, 16, 130, 6, 1.5, '1234', '');
    $this->RotatedText(35,20.5,$print_bachillerato,0);
    // Nombre Docente.
    $this->RoundedRect(34, 22, 130, 6, 1.5, '1234', '');
    $this->RotatedText(35,26,'Nombre Docente: ' . $print_nombre_docente,0);
// Generar el cuadro en donde se ubicara el grado, sección y año lectivo.
    $this->RoundedRect(229, 10, 35, 20, 3.5, '1234', '');
    $this->RotatedText(230,15,$print_grado,0);
    $this->RotatedText(230,19,$print_seccion,0);
    $this->RotatedText(230,23,$print_ann_lectivo,0);
//    $this->Line(10,20,200,20);
    //Salto de línea
    //$this->Ln(20);
}
function Footer()
{
    //Posición: a 1,5 cm del final
    $this->SetY(-10);
    //Arial italic 8
    $this->SetFont('Arial','I',8);
    //Crear ubna línea
    $this->Line(10,285,200,285);
    //Número de página
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
//Tabla coloreada
function FancyTable($header)
{
    //Colores, ancho de línea y fuente en negrita
    $this->SetFillColor(211,211,211);
    $this->SetTextColor(0);
    $this->SetDrawColor(0,0,0);
    $this->SetLineWidth(.3);
    $this->SetFont('','B');
    //Cabecera
    $w=array(5,25,45,17,30,20,18,15,40,17,20,15,20,60); //determina el ancho de las columnas
    $w1=array(160,187); //determina el ancho de las columnas
// linea 1 de la tabla fancy <header class="
    $header1=array('INFORMACIÓN DEL ESTUDIANTE','INFORMACIÓN DEL RESPONSABLE');
    for($J=0;$J<count($header1);$J++)
        $this->Cell($w1[$J],5,convertirtexto($header1[$J]),1,0,'C',1);
    $this->Ln();
    // lINEA 2 DE LA TABLA FANCY HEADER.
    for($i=0;$i<count($header);$i++)
        $this->Cell($w[$i],5,convertirtexto($header[$i]),1,0,'C',1);
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
    $pdf=new PDF('L','mm','Legal');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    
    $data = array();
//Títulos de las columnas
    $header=array('Nº','NIE | id','Nombre del Estudiante','F.Nac.','Datos/PN: N.º/F/T/L','D/M/D','DUI','Familiar','Responsable','F.Nac.','D/M/D','Nº.Tel.','D/M/D','Dirección');
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

// CONDICION PARA LOS RETIRADOS.

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','B',13); // I : Italica; U: Normal;
    $pdf->SetY(30);
    $pdf->SetX(10);
    $pdf->ln();

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
//  imprimir datos del bachillerato.
    $w1=array(5,6.5); //determina el ancho de las columnas

// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;

    $pdf->FancyTable($header); // Solo carge el encabezado de la tabla porque medaba error el cargas los datos desde la consulta.

    $w=array(5,25,45,17,30,20,18,15,40,17,20,15,20,60); //determina el ancho de las columnas
    $w2=array(5.8,12,5); //determina el alto de las columnas

    $fill = false; $i=1; $m = 0; $f = 0; $suma = 0; $generom = ''; $generof = '';
        while($row = $result -> fetch(PDO::FETCH_BOTH))
            {
                // Definimos el tipo de fuente, estilo y tamaño.
                    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
                // Información del Estudiante.
                    $codigo_nie_y_id = trim($row["codigo_nie"])  . ' | ' .  trim($row["id_alumno"]);
                    $nombre_completo = cambiar_de_del($row['nombre_completo_alumno']);
                    $direccion = cambiar_de_del($row['direccion_alumno']);
                    $fecha_nacimiento = cambiaf_a_normal(trim($row['fecha_nacimiento']));
                    $datos_pn = trim($row['pn_numero']) . ' | ' . trim($row['pn_folio']) . ' | ' . trim($row['pn_tomo']) . ' | ' . trim($row['pn_libro']);
                    $codigoDepartamentoNacimiento = trim($row["codigo_departamento_pn"]);
                    $codigoMunicipioNacimiento = trim($row["codigo_municipio_pn"]);
                // INFORMACIÓN DEL DOMICILIO DEL ESTUDIANTE.
                    $codigoDepartamentoDomicilio = trim($row["codigo_departamento"]);
                    $codigoMunicipioDomicilio = trim($row["codigo_municipio"]);
                // FILTAR POR DISTRITO.
                    $stmt = $db_link->prepare("SELECT codigo_departamento, nombre_departamento, codigo_municipio, nombre_municipio, codigo_distrito as codigo, nombre_distrito as descripcion
                    FROM elsalvador
                        WHERE codigo_municipio = :CodigoMunicipio and codigo_departamento = :CodigoDepartamento
                            ORDER BY codigo_departamento");
                // Ejectuar consulta
                    $stmt->execute(['CodigoMunicipio' => $codigoMunicipioNacimiento, 'CodigoDepartamento' => $codigoDepartamentoNacimiento]); 
                    $ElSalvador = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                //  Ejecutar Query.
                    foreach ($ElSalvador as $row_d_m) {
                        $nombres_d_m = substr(strtolower(trim($row_d_m["nombre_departamento"])),0,4) . "/" . substr(strtolower(trim($row_d_m["nombre_municipio"])),0,4) . "/" . substr(strtolower(trim($row_d_m["descripcion"])),0,4);
                    }       
                // Información del Responsable.
                    $encargado_dui = trim($row['encargado_dui']);
                    $familiar = trim($row['nombre_tipo_parentesco']);
                    $nombre_encargado = cambiar_de_del($row['nombres']);
                    $encargado_fecha_nacimiento = cambiaf_a_normal(trim($row['encargado_fecha_nacimiento']));
                    $telefono_encargado = trim($row['telefono_encargado']);
                    $codigoDepartamento = trim($row["encargado_departamento"]);
                    $codigoMunicipio = trim($row["encargado_municipio"]);
                // FILTAR POR DISTRITO.
                    $stmt = $db_link->prepare("SELECT codigo_departamento, nombre_departamento, codigo_municipio, nombre_municipio, codigo_distrito as codigo, nombre_distrito as descripcion
                    FROM elsalvador
                        WHERE codigo_municipio = :CodigoMunicipio and codigo_departamento = :CodigoDepartamento
                            ORDER BY codigo_departamento");
                // Ejectuar consulta
                    $stmt->execute(['CodigoMunicipio' => $codigoMunicipio, 'CodigoDepartamento' => $codigoDepartamento]); 
                    $ElSalvador = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                //  Ejecutar Query.
                $nombres_d_m_e = "";
                    foreach ($ElSalvador as $row_d_m) {
                        $nombres_d_m_e = substr(strtolower(trim($row_d_m["nombre_departamento"])),0,4) . "/" . substr(strtolower(trim($row_d_m["nombre_municipio"])),0,4) . "/" . substr(strtolower(trim($row_d_m["descripcion"])),0,4);
                    }     
                // FILTAR POR DISTRITO.
                    $stmt = $db_link->prepare("SELECT codigo_departamento, nombre_departamento, codigo_municipio, nombre_municipio, codigo_distrito as codigo, nombre_distrito as descripcion
                    FROM elsalvador
                        WHERE codigo_municipio = :CodigoMunicipio and codigo_departamento = :CodigoDepartamento
                            ORDER BY codigo_departamento");
                // Ejectuar consulta
                    $stmt->execute(['CodigoMunicipio' => $codigoMunicipioDomicilio, 'CodigoDepartamento' => $codigoDepartamentoDomicilio]); 
                    $ElSalvador = $stmt->fetchAll(PDO::FETCH_ASSOC); 
                //  Ejecutar Query.
                $nombres_d_m_d = "";
                    foreach ($ElSalvador as $row_d_m) {
                        $nombres_d_m_d = substr(strtolower(trim($row_d_m["nombre_departamento"])),0,4) . "/" . substr(strtolower(trim($row_d_m["nombre_municipio"])),0,4) . "/" . substr(strtolower(trim($row_d_m["descripcion"])),0,4);
                    }       
                // Cell Información del Estudiante.
                    $pdf->Cell($w[0],$w2[0],$i,'0',0,'C',$fill);       // núermo correlativo
                    $pdf->Cell($w[1],$w2[0],$codigo_nie_y_id,'LR',0,'C',$fill); // NIE
                    $pdf->Cell($w[2],$w2[0],$nombre_completo,'R',0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                    $pdf->Cell($w[3],$w2[0],$fecha_nacimiento,'R',0,'C',$fill);   // Nombre + apellido_materno + apellido_paterno
                    $pdf->Cell($w[4],$w2[0],$datos_pn,'R',0,'C',$fill);   // datos de pn, numero, folio, tomo y libro.
                    $pdf->Cell($w[5],$w2[0],$nombres_d_m,'R',0,'C',$fill);   // datos de pn, numero, folio, tomo y libro.

                // Cell Información del Responsable.
                    $pdf->Cell($w[6],$w2[0],$encargado_dui,'R',0,'L',$fill);
                    $pdf->Cell($w[7],$w2[0],$familiar,'R',0,'C',$fill);
                    $pdf->Cell($w[8],$w2[0],$nombre_encargado,'R',0,'L',$fill);    // responsable
                    $pdf->Cell($w[9],$w2[0],$encargado_fecha_nacimiento,'R',0,'C',$fill);    // nombre del encargado
                    $pdf->Cell($w[10],$w2[0],$nombres_d_m_e,'R',0,'C',$fill);   // datos de pn, numero, folio, tomo y libro.
                    $pdf->Cell($w[11],$w2[0],$telefono_encargado,'R',0,'R',$fill);    // nombre del encargado
                // Dirección
                    $pdf->Cell($w[12],$w2[0],$nombres_d_m_d,'R',0,'C',$fill);   // datos de pn, numero, folio, tomo y libro.
                    $pdf->MultiCell($w[13],$w2[2],$direccion,'RT','J',$fill);
                // Contar caracteres
                    $total_caracteres = strlen(trim($direccion));
                        if($total_caracteres > 50){
                            $i=$i+1;            
                        }else if($total_caracteres > 100){
                            $i=$i+1;
                        }
            //$pdf->ln();
                if($i == 25 || $i == 50 || $i == 75){
                  $pdf->Cell(array_sum($w),0,'','T');   
                  $pdf->AddPage();
                  $pdf->SetY(30);
                  $pdf->SetX(10);
                  $pdf->Ln(); 
                  $pdf->FancyTable($header);
                }
                $fill=!$fill;
                $i=$i+1;
            }
            $pdf->Cell(array_sum($w),0,'','T');
            $pdf->ln();
// Cierre de la Línea Final.        
    $pdf->Cell(array_sum($w)+(9*10),0,'','T');
// Salida del pdf.
    $modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
    $print_nombre = trim($nombre_modalidad) . ' - ' . trim($nombre_grado) . ' ' . trim($nombre_seccion) . ' - ' . trim($nombre_ann_lectivo) . '-DM.pdf';
    $pdf->Output($print_nombre,$modo);