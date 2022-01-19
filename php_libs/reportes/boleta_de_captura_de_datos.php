<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_academico/includes/funciones.php");
    include($path_root."/registro_academico/includes/consultas.php");
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include($path_root."/registro_academico/php_libs/fpdf/fpdf.php");
    include($path_root."/registro_academico/php_libs/fpdf/force_justify.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
    $codigo_all = $_REQUEST["todos"];
    $db_link = $dblink;
// buscar la consulta y la ejecuta.
    consultas(4,0,$codigo_all,'','','',$db_link,'');

// CAPTURAR EL NOMBRE DEL RESPONSABLES DE LA SECCIÓN.
     $query_docente = "SELECT eg.encargado, eg.codigo_ann_lectivo, eg.codigo_grado, eg.codigo_seccion, eg.codigo_bachillerato, eg.codigo_docente, eg.imparte_asignatura,
        btrim(p.nombres || cast(' ' as VARCHAR) || p.apellidos) as nombre_docente,
        gan.nombre as nombre_grado, sec.nombre as nombre_seccion, ann.nombre as nombre_ann_lectivo
		FROM encargado_grado eg
        INNER JOIN personal p ON p.id_personal = eg.codigo_docente
        INNER JOIN grado_ano gan ON gan.codigo = eg.codigo_grado
        INNER JOIN seccion sec ON sec.codigo = eg.codigo_seccion
        INNER JOIN ann_lectivo ann ON ann.codigo = eg.codigo_ann_lectivo
		WHERE btrim(eg.codigo_bachillerato || eg.codigo_grado || eg.codigo_seccion || eg.codigo_ann_lectivo || eg.codigo_turno) = '".$codigo_all."'";
	
        $result_docente = $db_link -> query($query_docente) or die ("Consulta Fallida!!!");
	
        $print_nombre_docente = ""; $print_nombre_ciclo = "";
        while($row = $result_docente -> fetch(PDO::FETCH_BOTH))
            {
            $print_nombre_docente = cambiar_de_del(trim($row['nombre_docente']));
            $print_grado = trim($row['nombre_grado']);
            $print_seccion = trim($row['nombre_seccion']);
            $print_ann_lectivo = trim($row['nombre_ann_lectivo']);

            if($row['codigo_bachillerato'] == '04')
            {
                $print_nombre_ciclo = utf8_decode('2do Ciclo de Educación Básica');
            }
	    if($row['codigo_bachillerato'] == '03')
            {
                $print_nombre_ciclo = utf8_decode('1er Ciclo de Educación Básica');
            }
	    if($row['codigo_bachillerato'] == '05')
            {
                $print_nombre_ciclo = utf8_decode('3do Ciclo de Educación Básica');
            }
            }              


//************************************************************************************************************************
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
    
/////////////////////////////////////////////////////////////////////////////////////////
// configuración de colores para la linea
    $pdf->SetDrawcolor(0,0,0);
    //Restauración de colores y fuentes
    $pdf->SetFillColor(224,235,255);
    $pdf->SetTextColor(0);
    $pdf->SetFont('');
    
    $pdf->AliasNbPages();
    $pdf->SetFont('Arial','',12);
    $pdf->AddPage();

// Aqui mandamos texto a imprimir o al documento.
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetY(10);
    $pdf->SetX(10);

    //Títulos de las columnas
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(350,5,utf8_decode('MINISTERIO DE EDUCACIÓN'),0,1,'C');
    $pdf->Cell(350,5,utf8_decode('BOLETA DE CAPTURA DE DATOS PARA LA ASIGACIÓN DEL NIE'),0,1,'C');
    
    $pdf->SetFont('Arial','',9);
    $pdf->RotatedText(290,8,$print_nombre_ciclo,0);
    
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
//  imprimir datos del bachillerato.
    $w1=array(5.5,6.5); //determina el ancho de las columnas
    

    $query = "SELECT a.estudio_parvularia, a.id_alumno, a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
            btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, a.nombre_completo,
            btrim(a.nombre_completo || CAST(' ' AS VARCHAR) || a.apellido_paterno  || CAST(' ' AS VARCHAR) || a.apellido_materno) as nombre_completo_alumno,
            ae.codigo_alumno, ae.nombres, ae.encargado, ae.dui as encargado_dui, ae.telefono, ae.fecha_nacimiento as encargado_fecha_nacimiento, ae.direccion as encargado_direccion, ae.telefono as encargado_telefono,
            a.foto, a.pn_folio, a.pn_tomo, a.pn_numero, a.pn_libro, a.fecha_nacimiento, a.direccion_alumno, telefono_alumno, a.edad, a.genero, a.estudio_parvularia, a.codigo_discapacidad, a.codigo_apoyo_educativo, a.codigo_actividad_economica, a.codigo_estado_familiar, a.partida_nacimiento,
            am.imprimir_foto, am.pn, am.repitente, am.sobreedad, am.retirado, am.codigo_bach_o_ciclo, am.certificado, am.ann_anterior,
            am.nuevo_ingreso, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, bach.codigo as codigo_bachillerato,
            am.codigo_grado, gan.nombre as nombre_grado, am.codigo_seccion, sec.nombre as nombre_seccion, am.id_alumno_matricula as codigo_matricula, am.codigo_turno,
            am.observaciones, am.id_alumno_matricula as cod_matricula,
            cat_f.descripcion as nombre_tipo_parentesco,
            cat_g.descripcion as nombre_sexo,
            tur.nombre as nombre_turno
                FROM alumno a
                    INNER JOIN alumno_encargado ae ON a.id_alumno = ae.codigo_alumno and ae.encargado = 't'
                    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
                    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
                    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
                    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
                    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
                    INNER JOIN turno tur ON tur.codigo = am.codigo_turno
                    INNER JOIN catalogo_familiar cat_f ON cat_f.codigo = ae.codigo_familiar
                    INNER JOIN catalogo_genero cat_g ON cat_g.codigo = ae.codigo_genero
                     WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '$codigo_all'
                    ORDER BY apellido_alumno ASC";
 $result_encabezado = $db_link -> query($query);                    
    while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
        {
            if (substr($codigo_all,2,2) == '53P' or substr($codigo_all,2,2) == '63P'){
                $pdf->Cell(60,$w1[0],utf8_decode('Código de infraestructura: ').$_SESSION['codigo'],0,0,'L');
                $pdf->Cell(30,$w1[0],'Departamento: 02 ',0,0,'L');
                $pdf->Cell(30,$w1[0],'Municipio: 10 ',0,0,'L');
                $pdf->Cell(50,$w1[0],'Distrito: 02',0,0,'L');
		$pdf->Cell(30,$w1[0],'Inicial Lactantes ',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
		$pdf->SetX(220);
                $pdf->Cell(15,$w1[0],'Inicial 1 ',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
		$pdf->SetX(260);
                $pdf->Cell(15,$w1[0],'Inicial 2 ',0,0,'L');                      
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
		$pdf->SetX(300);
                $pdf->Cell(15,$w1[0],'Inicial 3',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');       
		
		$pdf->Ln();
                $pdf->Cell(25,$w1[0],'Parvularia 4 ',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
                $pdf->Cell(25,$w1[0],'Parvularia 5 ',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
                $pdf->Cell(25,$w1[0],'Parvularia 6 ',0,0,'L');                      
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
		$pdf->SetX(130);
                $pdf->Cell(35,$w1[0],utf8_decode('Sección Integrada: '),0,0,'L');
                $pdf->Cell(10,$w1[0],'SI',0,0,'L');
		$pdf->Cell(5,$w1[0],' ',1,0,'L');       
                $pdf->Cell(10,$w1[0],'NO',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');   
                
                $pdf->ln();
                $pdf->Cell(50,$w1[0],utf8_decode('Sección atentidad por MINED: '),0,0,'L');
                $pdf->Cell(10,$w1[0],'SI',0,0,'L');
		$pdf->Cell(5,$w1[0],'   ',1,0,'L');
                $pdf->Cell(10,$w1[0],'NO',0,0,'L');
                $pdf->Cell(5,$w1[0],' ',1,0,'L');
		$pdf->SetX(100);
                $pdf->Cell(50,$w1[0],utf8_decode('Sección atendida por ISNA '),0,0,'L');
                $pdf->Cell(5,$w1[0],'   ',1,0,'L');
		$pdf->SetX(170);
                $pdf->Cell(50,$w1[0],utf8_decode('Sección atendida por MSPAS '),0,0,'L');
                $pdf->Cell(5,$w1[0],'   ',1,0,'L');
		$pdf->SetX(230);
                $pdf->Cell(50,$w1[0],utf8_decode('Sección atendida por Alcaldía '),0,0,'L');
                $pdf->Cell(5,$w1[0],'   ',1,0,'L');         

                $pdf->ln();         
                $pdf->Cell(45,$w1[0],utf8_decode('Sección atendida por ONG '),0,0,'L');
                $pdf->Cell(5,$w1[0],'   ',1,0,'L');         
                $pdf->Cell(130,$w1[0],'Nombre de ONG: _______________________________________________',0,0,'L');
                $pdf->Cell(30,$w1[0],utf8_decode('Nombre del Centro Educativo: '.$_SESSION['institucion']),0,0,'L');

                $pdf->ln();
		$pdf->Cell(20,$w1[0],'Turno: ',0,0,'L');
	    
		$pdf->Circle(23,44.5,2);
		$pdf->Cell(20,$w1[0],utf8_decode('Mañana '),0,0,'L');
		
		$pdf->Circle(43,44.5,2);
		$pdf->Cell(20,$w1[0],'Tarde ',0,0,'L');
		
		$pdf->Cell(20,$w1[0],'Sector: ',0,0,'L');
		
		$pdf->Circle(83,44.5,2);
		$pdf->Cell(20,$w1[0],utf8_decode('Público '),0,0,'L');
		
		$pdf->Circle(103,44.5,2);
		$pdf->Cell(20,$w1[0],'Privado ',0,0,'L');
    
                $pdf->Cell(80,$w1[0],utf8_decode('Profesor(a), Educador(a) Responsable de la sección: ').$print_nombre_docente,0,0,'L');        

                $pdf->ln(); 
                $pdf->Cell(205,$w1[0],'Nombre Director: '.$_SESSION['nombre_director'],0,0,'L');
                $pdf->Cell(90,$w1[0],'Firma Director: ____________________________',0,0,'L');
                $pdf->Cell(20,$w1[0],'Sello',0,0,'L');
            }
        else{               
            $pdf->Cell(60,$w1[0],utf8_decode('Código de infraestructura: ').$_SESSION['codigo'],0,0,'L');
            $pdf->Cell(40,$w1[0],'Departamento: 02 ',0,0,'L');
            $pdf->Cell(30,$w1[0],'Municipio: 10 ',0,0,'L');
            $pdf->Cell(30,$w1[0],'Distrito: 02',0,0,'L');
            $pdf->Cell(30,$w1[0],'Grado: '.trim($row['codigo_grado']),0,0,'L');
            $pdf->Cell(30,$w1[0],utf8_decode('Sección: '.trim($row['codigo_seccion'])),0,0,'L');
            $pdf->Cell(27,$w1[0],'Aula Alternativa ',0,0,'L');                       
            $pdf->Cell(5,$w1[0],' ',1,0,'L');
            $pdf->Cell(31,$w1[0],utf8_decode('Sección Integrada '),0,0,'L');
            $pdf->Cell(5,$w1[0],' ',1,0,'L');
                
            $pdf->ln();
            $pdf->Cell(35,$w1[0],utf8_decode('E. Acelerada: Año 1 '),0,0,'L');
            $pdf->Cell(5,$w1[0],'   ',1,0,'L');
            $pdf->Cell(15,$w1[0],'',0,0,'L');           
            $pdf->Cell(12,$w1[0],utf8_decode('Año 2 '),0,0,'L');
            $pdf->Cell(5,$w1[0],'   ',1,0,'L');         
            $pdf->Cell(25,$w1[0],'',0,0,'L');           
            $pdf->Cell(30,$w1[0],utf8_decode('Nombre del Centro Educativo: '.$_SESSION['institucion']),0,0,'L');

            $pdf->ln(); 
            $pdf->Cell(20,$w1[0],'Turno: ',0,0,'L');
	    
            $pdf->Circle(23,33.5,2);
            $pdf->Cell(20,$w1[0],utf8_decode('Mañana '),0,0,'L');
            
            $pdf->Circle(43,33.5,2);
            $pdf->Cell(20,$w1[0],'Tarde ',0,0,'L');
            
            $pdf->Circle(63,33.5,2);
            $pdf->Cell(20,$w1[0],'Noche ',0,0,'L');
            
            $pdf->Circle(83,33.5,2);
            $pdf->Cell(100,$w1[0],'Fin de Semana ',0,0,'L');
    
            $pdf->Cell(130,$w1[0],utf8_decode('Docente responsable de la sección ').$print_nombre_docente,0,0,'L');}
            $pdf->Ln();
                break;
            }
            
                     
                     $result = $db_link -> query($query);
// CONDICIONAR SI ES PARVULARIA PARA DIFERENTE ENCABEZADO.
if (substr($codigo_all,2,2) == '53P' or substr($codigo_all,2,2) == '63P'){
// Colocar datos de cabecera.
   $pdf->encabezado_parvularia();
//  posición inicial en donde comienza a imprimir los datos.
    $pdf->SetXY(5,70);
}else{
// Colocar datos de cabecera.
   $pdf->encabezado();
//  posición inicial en donde comienza a imprimir los datos.
    $pdf->SetXY(5,50);   
}
//  mostrar los valores de la consulta
    $w=array(5,21,60,60,9,20,9,12,93,10); //determina el ancho de las columnas
    $w2=array(6); //determina el ancho de las columnas
// Definimos el tipo de fuente, estilo y tamaño.
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    
    $fill=false; $i=1; $m = 0; $f = 0; $suma = 0; $generom = ''; $generof = '';
        while($row = $result -> fetch(PDO::FETCH_BOTH))
         {
            // PRIMER GRUPO DE DATOS. NUMERO CORRELATIVO, NIE, NOMBRE Y APELLIDOS.
            $pdf->Cell($w[0],$w2[0],$i,'LR',0,'C',$fill);       // núermo correlativo
            $pdf->Cell($w[1],$w2[0],trim($row['codigo_nie']),'LR',0,'C',$fill); // NIE
            $pdf->Cell($w[3],$w2[0],cambiar_de_del(trim($row['nombre_completo'])),'LR',0,'L',$fill);    // nombre del encargado
            $pdf->Cell($w[2],$w2[0],cambiar_de_del(trim($row['apellidos_alumno'])),'LR',0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
            
            // SEGUNDO GRUPO DE DATOS FECHA DE NACIMIENTO, SEXO Y GRADO.
            $fecha = cambiaf_a_normal(trim($row['fecha_nacimiento']));
            $DD = substr($fecha,0,2);
            $MM = substr($fecha,3,2);
            $AAAA = substr($fecha,6,4);
            
            $pdf->Cell($w[4],$w2[0],$DD,'LR',0,'C',$fill);   // dia
            $pdf->Cell($w[4],$w2[0],$MM,'LR',0,'C',$fill);   // mes
            $pdf->Cell($w[4],$w2[0],$AAAA,'LR',0,'C',$fill);   // año
            
            if ($row['genero'] == 'm'){$generom = 'X';}else{$generom = '';}
            if ($row['genero'] == 'f'){$generof = 'X';}else{$generof = '';}
            
            $pdf->Cell($w[6],$w2[0],$generom,'LR',0,'C',$fill); // genero masculino
            $pdf->Cell($w[6],$w2[0],$generof,'LR',0,'C',$fill); // genero femenino

            if ($row['codigo_grado'] == '53P' || $row['codigo_grado'] == '63P'){
            $pdf->Cell($w[7],$w2[0],trim($row['codigo_grado']).'-'.trim($row['edad']),'LR',0,'C',$fill);    // grado    
            }else{
            $pdf->Cell($w[7],$w2[0],trim($row['codigo_grado']),'LR',0,'C',$fill);   // grado
            }
            
            // TERCER BLOQUE, PADRE, MADRE O ENCARGADO.
//            $pdf->Cell($w[8],$w2[0],cambiar_de_del(trim($row['nombres'])). ' ' .trim($row['dui']),'LR',0,'L',$fill);    // responsable
            $pdf->Cell($w[8],$w2[0],cambiar_de_del(trim($row['nombres'])),'LR',0,'L',$fill);    // responsable
            // CUARTO BLOQUE QUE COMPRA SI EN DE PARVULARIA U CICLOS.
            if (substr($codigo_all,2,2) == '53P' || substr($codigo_all,2,2) == '63p'){
                if($row['pn'] == 't'){
                    $pdf->Cell($w[5],$w2[0],'X','LR',0,'C',$fill);
                    $pdf->Cell($w[5],$w2[0],'','LR',1,'C',$fill);}
                        // numero
                if($row['pn'] == 'f'){
                    $pdf->Cell($w[5],$w2[0],'','LR',0,'C',$fill);
                    $pdf->Cell($w[5],$w2[0],'X','LR',1,'C',$fill);} 
            }
            else{
               $pdf->Cell($w[9],$w2[0],trim($row['pn_numero']),'LR',0,'C',$fill);  // numero
               $pdf->Cell($w[9],$w2[0],trim($row['pn_folio']),'LR',0,'C',$fill);   // folio
               $pdf->Cell($w[9],$w2[0],trim($row['pn_tomo']),'LR',0,'C',$fill);    // tomo
               $pdf->Cell($w[9],$w2[0],trim($row['pn_libro']),'LR',0,'C',$fill);  // libro
               $pdf->ln();
            }
            // PARA EL SALTO DE LINEA.
                if($i == 23 || $i == 50)
                {
                	$pdf->Cell(336,0,'','T');
			$pdf->SetMargins(5, 20, 5);
			$pdf->AddPage();
                        
                        // CONDICIONAR SI ES PARVULARIA PARA DIFERENTE ENCABEZADO.
                           if (substr($codigo_all,2,2) == '53P' || substr($codigo_all,2,2) == '63'){
                        // colocar datos de cabecera.
                           $pdf->encabezado_parvularia_2();
                        //  posición inicial en donde comienza a imprimir los datos.
                           $pdf->SetXY(5,40);
                           }else{
                        // colocar datos de cabecera.
                           $pdf->encabezado_2();
                        //  posición inicial en donde comienza a imprimir los datos.
                           $pdf->SetXY(5,30);                              
                           }
               }
               $fill=!$fill;
               $i=$i+1;
         }          // FIN DEL WHILE
            
	    // COMPLETAR LINEAS HASTA EL 25.
	    if($i < 23)
	    {
		// rellenar con las lineas que faltan y colocar total de puntos y promedio.
          	$numero = $i;
                $linea_faltante =  23 - $numero;
                $numero_p = $numero - 1;
                
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                      $pdf->Cell($w[0],5.8,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      
                        $pdf->Cell($w[1],$w2[0],'','LR',0,'C',$fill); // NIE
                        $pdf->Cell($w[3],$w2[0],'','LR',0,'L',$fill);    // nombre del encargado
                        $pdf->Cell($w[2],$w2[0],'','LR',0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                        
                        $pdf->Cell($w[4],$w2[0],'','LR',0,'C',$fill);   // fecha
                        $pdf->Cell($w[4],$w2[0],'','LR',0,'C',$fill);   // fecha
                        $pdf->Cell($w[4],$w2[0],'','LR',0,'C',$fill);   // fecha
                        
                        
                        $pdf->Cell($w[6],$w2[0],'','LR',0,'C',$fill); // genero masculino
                        $pdf->Cell($w[6],$w2[0],'','LR',0,'C',$fill); // genero femenino
                        
                        $pdf->Cell($w[7],$w2[0],'','LR',0,'C',$fill);   // grado
                     
                        
                        $pdf->Cell($w[8],$w2[0],'','LR',0,'L',$fill);    // responsable
                        
                        // CONDICIONAR SI ES PARVULARIA PARA DIFERENTE ENCABEZADO.
                        if (substr($codigo_all,2,2) == '53P' || substr($codigo_all,2,2) == '63P'){
                           $pdf->Cell($w[5],$w2[0],'','LR',0,'C',$fill);  // si
                           $pdf->Cell($w[5],$w2[0],'','LR',0,'C',$fill);   // no
                        }
                        else{
                           $pdf->Cell($w[9],$w2[0],'','LR',0,'C',$fill);  // numero
                           $pdf->Cell($w[9],$w2[0],'','LR',0,'C',$fill);   // folio
                           $pdf->Cell($w[9],$w2[0],'','LR',0,'C',$fill);    // tomo
                           $pdf->Cell($w[9],$w2[0],'','LR',1,'C',$fill);  // libro
                        }
                        $fill=!$fill;
                  }
                	$pdf->Cell(array_sum($w)+$w[9]*4,0,'','T');
			$pdf->SetMargins(5, 20, 5);
			$pdf->AddPage();
   
                        // CONDICIONAR SI ES PARVULARIA PARA DIFERENTE ENCABEZADO.
                           if (substr($codigo_all,2,2) == '53P' || substr($codigo_all,2,2) == '63P'){
                       // colocar datos de cabecera.
                           $pdf->encabezado_parvularia_2();
                        //  posición inicial en donde comienza a imprimir los datos.
                           $pdf->SetXY(5,40);
                           }
                           else{
                        // colocar datos de cabecera.
                           $pdf->encabezado_2();
                        //  posición inicial en donde comienza a imprimir los datos.
                           $pdf->SetXY(5,30);
                           }
	    }
	    
            // rellenar con las lineas que faltan y colocar total de puntos y promedio.
		$numero = $i;
          	$numero = $numero;
                $linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;               
                
                for($i=0;$i<=$linea_faltante;$i++)
                  {
                      $pdf->Cell($w[0],5.8,$numero++,'LR',0,'C',$fill);  // N| de Orden.
                      
                        $pdf->Cell($w[1],$w2[0],'','LR',0,'C',$fill); // NIE
                        $pdf->Cell($w[3],$w2[0],'','LR',0,'L',$fill);    // nombre del encargado
                        $pdf->Cell($w[2],$w2[0],'','LR',0,'L',$fill);   // Nombre + apellido_materno + apellido_paterno
                        
                        $pdf->Cell($w[4],$w2[0],'','LR',0,'C',$fill);   // fecha
                        $pdf->Cell($w[4],$w2[0],'','LR',0,'C',$fill);   // fecha
                        $pdf->Cell($w[4],$w2[0],'','LR',0,'C',$fill);   // fecha
                        
                        
                        $pdf->Cell($w[6],$w2[0],'','LR',0,'C',$fill); // genero masculino
                        $pdf->Cell($w[6],$w2[0],'','LR',0,'C',$fill); // genero femenino
                        
                        $pdf->Cell($w[7],$w2[0],'','LR',0,'C',$fill);   // grado
                     
                        
                        $pdf->Cell($w[8],$w2[0],'','LR',0,'L',$fill);    // responsable
                        
                        // CONDICIONAR SI ES PARVULARIA PARA DIFERENTE ENCABEZADO.
                        if (substr($codigo_all,2,2) == '53P' || substr($codigo_all,2,2) == '63P'){
                           $pdf->Cell($w[5],$w2[0],'','LR',0,'C',$fill);  // si
                           $pdf->Cell($w[5],$w2[0],'','LR',1,'C',$fill);   // no
                        }
                        else{
                           $pdf->Cell($w[9],$w2[0],'','LR',0,'C',$fill);  // numero
                           $pdf->Cell($w[9],$w2[0],'','LR',0,'C',$fill);   // folio
                           $pdf->Cell($w[9],$w2[0],'','LR',0,'C',$fill);    // tomo
                           $pdf->Cell($w[9],$w2[0],'','LR',1,'C',$fill);  // libro
                        }
                        $fill=!$fill;
                  }
            $pdf->Cell(336,0,'','T');
            $pdf->ln();
// Construir el nombre del archivo.
$nombre_archivo = $print_nombre_docente.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '.pdf';
// Salida del pdf.
	$modo = 'I'; // Envia al navegador (I), Descarga el archivo (D), Guardar el fichero en un local(F).
	$pdf->Output($nombre_archivo,$modo);
?>