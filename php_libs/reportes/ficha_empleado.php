<?php
// iniciar sesssion.
    session_name('demoUI');
    session_start();
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexión a la base de datos
    include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Llamar a la libreria fpdf
    include("$path_root/registro_academico/php_libs/fpdf/fpdf.php");
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// Validar conexión con la base de datos
if($errorDbConexion == false){
	// Validamos qe existan las variables post
	if(isset($_REQUEST) && !empty($_REQUEST)){
            // armando el Query para la Tabla Empleados.
            $query = "SELECT emp.id_empleado, emp.nombres, emp.apellidos, emp.direccion, emp.tel_residencia, emp.tel_celular, emp.cod_genero,
            emp.cod_estado_civil, emp.cod_nivel_escolaridad, emp.email, emp.num_dui, emp.fecha_nacimiento, emp.edad,
            emp.num_nit, emp.cod_depa, emp.cod_muni, emp.cod_afiliado, emp.numero_provisional, emp.num_lic_conducir, emp.cod_estatus, emp.url_foto, emp.cod_motorista, emp.num_carnet_motorista,
            cat_gen.descripcion as nombre_genero, cat_civil.descripcion as nombre_estado_civil,
            cat_depa.descripcion as nombre_departamento, cat_muni.descripcion as nombre_municipio,
            cat_afp.descripcion as nombre_afiliado
                FROM empleados emp
                    INNER JOIN catalogo_genero cat_gen ON cat_gen.codigo = emp.cod_genero
                    INNER JOIN catalogo_estado_civil cat_civil ON cat_civil.codigo = emp.cod_estado_civil
                    INNER JOIN catalogo_departamento cat_depa ON cat_depa.codigo = emp.cod_depa
                    INNER JOIN catalogo_municipio cat_muni ON cat_muni.codigo = emp.cod_muni and cat_depa.codigo = cat_muni.codigo_departamento
                    INNER JOIN catalogo_afp cat_afp ON cat_afp.codigo = emp.cod_afiliado
                    WHERE emp.id_empleado = ".
                        $_REQUEST['id_user'];

            // armando el Query para la Tabla Empleados.
            $query_historial = "SELECT codigo_empleado, fecha_ob, historial
                                    FROM empleados_bitacora
                                        WHERE codigo_empleado = ".
                                            $_REQUEST['id_user'];
        // Ejecutamos el Query. Tabla Bitacora.
           $consulta_historial = $dblink -> query($query_historial);   
        // Ejecutamos el Query. Tabla Empleados
           $consulta = $dblink -> query($query);
           
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
                    $this->MultiCell(90,4,$txt,0,'L');
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
            {                                //
                // Establecer formato para la fecha.
                    date_default_timezone_set('America/El_Salvador');
                    setlocale(LC_TIME, 'spanish');
                  //Número de página
                  $fecha = date("l, F jS Y ");
                  
                //Logo
                $img = $_SERVER['DOCUMENT_ROOT'].'/interfaz_usuario/img/'.$_SESSION['logo_empresa'];
                $this->Image($img,7,6,12,11);
                //Arial bold 15
                $this->AddFont('Comic');
                $this->SetFont('Comic','',12);
                $this->SetTextColor(0,0,0);
                $this->Cell(130,7,$_SESSION['nombre_empresa'],0,1,'C');
                $this->SetTextColor(0,0,0);
                $this->Rect(5,5,135,215);
                //Crear una línea
                $this->Line(5,20,140,20);
                $this->SetXY(5,25);
                
                $this->SetFont('Comic','',7);
                $this->RotatedText(95,18,$fecha.'       Page '.$this->PageNo().'/{nb}',0);
            }
            
            //Pie de página
            function Footer()
            {                                 
                  //Posición: a 1,5 cm del final
                  $this->SetY(-15);
            }
            
            //Tabla coloreada
            function FancyTable($header)
            {
                //Colores, ancho de línea y fuente en negrita
                $this->SetFillColor(255,0,0);
                $this->SetTextColor(255);
                $this->SetDrawColor(128,0,0);
                $this->SetLineWidth(.3);
                $this->SetFont('','');
                //Cabecera
                $w=array(65,20,12,18,20); //determina el ancho de las columnas
                $w2=array(5,12); //determina el ancho de las columnas
                for($i=0;$i<count($header);$i++)
                    $this->Cell($w[$i],7,$header[$i],1,0,'C',1);
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
                $pdf=new PDF('P','mm',array(145,225));
                #Establecemos los márgenes izquierda, arriba y derecha: 
                $pdf->SetMargins(5, 5, 5);
                #Establecemos el margen inferior: 
                $pdf->SetAutoPageBreak(true,5);
                
            //Títulos de las columnas
                $header=array('');
                $pdf->AliasNbPages();
                $pdf->AddPage();
            
            // Aqui mandamos texto a imprimir o al documento.
            // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Times','B',10); // I : Italica; U: Normal;
                $pdf->SetY(5);
                $pdf->SetX(5);
                $pdf->Ln(); $pdf->Ln();
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
            
            // crear lineas y rectángulos.
                //Crear una línea de la primera firma a 55 cm.
                $pdf->Line(5,65,140,65);
                //Crear una línea de la primera firma a 85 cm.
                $pdf->Line(5,105,140,105);
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;    
            // colocar etiquetas y cuadros que sean necesarios.
                $pdf->SetTextColor(0,0,0);
                $pdf->RotatedText(55,26,'DATOS GENERALES',0);
                $pdf->SetTextColor(0,0,0);
            // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
            // para el rectángulo del label bajar uno, uno menos del margen izq. y de alto 5, ancho depende del campo.
                $pdf->RotatedText(117,28,'Cod.',0);
                
                $pdf->RotatedText(8,30,'Apellidos',0);
                $pdf->Rect(7,31,50,5);
                
                $pdf->RotatedText(60,30,'Nombres',0);
                $pdf->Rect(59,31,50,5);
            
                $pdf->RotatedText(8,40,'Teléfono: Casa',0);
                $pdf->Rect(7,41,20,5);
            
                $pdf->RotatedText(35,40,'Célular',0);
                $pdf->Rect(34,41,20,5);
                
                $pdf->RotatedText(60,40,'Género',0);
                $pdf->Rect(59,41,20,5);

                $pdf->RotatedText(85,40,'Estado Civil',0);
                $pdf->Rect(84,41,20,5);
                
                $pdf->RotatedText(8,50,'Dirección',0);
                $pdf->Rect(7,51,100,10);
            /////////////////////////////////////////////////////////////////////////////////////////////////////////    
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
                // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;
                $pdf->SetTextColor(0,0,0);
                $pdf->RotatedText(55,70,'DOCUMENTOS Y OTROS',0);
                $pdf->SetTextColor(0,0,0);
                // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
            
                $pdf->RotatedText(8,75,'Nº DUI',0);
                $pdf->Rect(7,76,30,5);
                
                $pdf->RotatedText(41,75,'Fecha Nacimiento',0);
                $pdf->Rect(40,76,30,5);
            
                $pdf->RotatedText(75,75,'Edad',0);
                $pdf->Rect(74,76,20,5);
            
                $pdf->RotatedText(98,75,'Nº NIT',0);
                $pdf->Rect(97,76,30,5);
            
                $pdf->RotatedText(8,85,'Departamento',0);
                $pdf->Rect(7,86,30,5);
            
                $pdf->RotatedText(41,85,'Municipio',0);
                $pdf->Rect(40,86,30,5);
            
                $pdf->RotatedText(75,85,'Afiliado a:',0);
                $pdf->Rect(74,86,20,5);
                
                $pdf->RotatedText(98,85,'Nº NUP',0);
                $pdf->Rect(97,86,30,5);

                $pdf->RotatedText(8,95,'Código Motorista',0);
                $pdf->Rect(7,96,30,5);                
                
                $pdf->RotatedText(41,95,'Nº Carnet Motorista',0);
                $pdf->Rect(40,96,30,5);
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',10); // I : Italica; U: Normal;    
            // colocar etiquetas y cuadros que sean necesarios.
                $pdf->SetTextColor(0,0,0);
                $pdf->RotatedText(60,110,'HISTORIAL',0);
                $pdf->SetTextColor(0,0,0);
            // Definimos el tipo de fuente, estilo y tamaño.
                $pdf->SetFont('Comic','',8); // I : Italica; U: Normal;
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // CONSTRUIR LA TABLA, PARA LOS DATOS QUE PROVIENEN DEL HISTORIAL.
                // Rectangulo - Principal
                $pdf->Rect(6,115,133,10);
                // Para la Fecha.
                $pdf->RotatedText(10,120,'Fecha',0);
                $pdf->Rect(6,115,20,10);
                // Para el Historial.
                $pdf->RotatedText(70,120,'Historial',0);
                $pdf->Rect(6,115,133,10);
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Colocar los Labels.
        // Recorriendo la Tabla Empleados con PDO::
        while($listado = $consulta -> fetch(PDO::FETCH_BOTH))
	{
            // Generar variables para el nombre del archivo.
            $nombre_archivo = ucwords(strtolower(trim($listado['apellidos']))) .'-'.ucwords(strtolower(trim($listado['nombres'])));
            // Colocar los datos generales

            $apellido_c = ucwords(strtolower(trim($listado['apellidos'])));
            $nombre_c = ucwords(strtolower(trim($listado['nombres'])));

            $nombre_utf8 = $nombre_c;
            
            $pdf->RotatedText(8,35,$apellido_c,0);
            $pdf->RotatedText(60,35,$nombre_utf8,0);
            $pdf->RotatedText(8,45,ucwords(strtolower(trim($listado['tel_residencia']))),0);
            $pdf->RotatedText(35,45,ucwords(strtolower(trim($listado['tel_celular']))),0);
            
            $pdf->RotatedText(60,45,ucwords(strtolower(trim($listado['nombre_genero']))),0);
            $pdf->RotatedText(85,45,ucwords(strtolower(trim($listado['nombre_estado_civil']))),0);
            
            $pdf->RotatedTextMulticell(8,52,ucwords(strtolower(trim($listado['direccion']))),0);            

            // ruta de la foto. cod_motorista, num_motorista.
            $foto = trim($listado['url_foto']);
            /////////////////////COLOCAR LA FOTO.////////////////////////////////////////////////////////////////////////////////////
                // Colocar foto del alumno/a.
                $nofoto = 'nofoto.jpg';
                $imagen_o_foto = $foto;
                
                if($imagen_o_foto == 'nofoto.jpg')
                {
                    $img = $_SERVER['DOCUMENT_ROOT'].'/interfaz_usuario/img/'.$imagen_o_foto;    
                }else
                {
                    if (file_exists($_SERVER['DOCUMENT_ROOT'].'/interfaz_usuario/img/png/'.$imagen_o_foto)){
                        $img = $_SERVER['DOCUMENT_ROOT'].'/interfaz_usuario/img/png/'.$imagen_o_foto;
                    }else{
                        $img = $_SERVER['DOCUMENT_ROOT'].'/interfaz_usuario/img/'.$nofoto;    
                    }
                }
                $pdf->Image($img,118,31,20,26);
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Colocar los DOCUMENTOS Y OTROS.
                $pdf->RotatedText(8,80,ucwords(strtolower(trim($listado['num_dui']))),0);
                $pdf->RotatedText(41,80,cambiaf_a_normal(ucwords(strtolower(trim($listado['fecha_nacimiento'])))),0);
                $pdf->RotatedText(75,80,ucwords(strtolower(trim($listado['edad']))),0);
                $pdf->RotatedText(98,80,ucwords(strtolower(trim($listado['num_nit']))),0);
                
                $pdf->RotatedText(8,90,ucwords(strtolower(trim($listado['nombre_departamento']))),0);
                $pdf->RotatedText(41,90,ucwords(strtolower(trim($listado['nombre_municipio']))),0);
                $pdf->RotatedText(75,90,ucwords(strtolower(trim($listado['nombre_afiliado']))),0);
                $pdf->RotatedText(98,90,ucwords(strtolower(trim($listado['numero_provisional']))),0);
                
                $pdf->RotatedText(8,100,ucwords(strtolower(trim($listado['cod_motorista']))),0);
                $pdf->RotatedText(41,100,ucwords(strtolower(trim($listado['num_carnet_motorista']))),0);
        }
        /////////////////////////////////////////////////////////////////////////////////////////////////////////
        // fill y color. y num.
        $fill = false; $num = 1;
        $pdf->SetFillColor(224,235,255);
        //  establecer sext x y y
            $pdf-> SetXY(6,125);
        // Recorriendo la Tabla Bitacora con PDO::
        while($listado_historial = $consulta_historial -> fetch(PDO::FETCH_BOTH))
	{   
            if($num == 20){$pdf->AddPage();$num = 1;$pdf->SetXY(7,25);}else{
                $pdf->Cell(20,5,trim($listado_historial['fecha_ob']),'LB',0,'L',$fill);
                $pdf->MultiCell1(113,5,trim($listado_historial['historial']),'LBR','J',$fill,300);
                $pdf->ln(-.2);
            }
            
            // cambiar el fill
            //$fill=!$fill;
            $num++;
        }
            // Salida del pdf.
                $pdf->Output();
        }   
}

?>