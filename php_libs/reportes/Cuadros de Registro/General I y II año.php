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
  $valor_x_encabezado = false;
  $contar_evaluar = 6;
// variables para retenidos y promovidos.
    $total_matricula_inicial_masculino = 0;
    $total_matricula_final_femenino = 0;
    $total_matricula_retirados_masculino = 0;
    $total_matricula_retirados_femenino = 0;
		$total_promovidos_f= 0;
    $total_promovidos_m=0;
    $total_retenidos_f=0;
    $total_retenidos_m=0;
    $retirados_m_f=0;
// Establecer formato para la fecha.
  date_default_timezone_set('America/El_Salvador');
  setlocale(LC_TIME,'es_SV');
// CREAR MATRIZ DE MESES Y FECH.
  $meses = array("enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre");
//Crear una línea. Fecha con getdate();
  $hoy = getdate();
  $NombreDia = $hoy["wday"];  // dia de la semana Nombre.
  $dia = $hoy["mday"];    // dia de la semana
  $mes = $hoy["mon"];     // mes
  $año = $hoy["year"];    // año
  $total_de_dias = cal_days_in_month(CAL_GREGORIAN, (int)$mes, $año);
  $NombreMes = $meses[(int)$mes - 1];
// buscar la consulta y la ejecuta.
  consultas(9,0,$codigo_all,'','','',$db_link,'');
//  almacenar variables de datos del bachillerato.
  global $result_encabezado;
  while($row = $result_encabezado -> fetch(PDO::FETCH_BOTH))
      {
          $print_bachillerato = trim($row['nombre_bachillerato']);
          $print_grado = trim($row['nombre_grado']);
          $print_seccion = trim($row['nombre_seccion']);
          $print_ann_lectivo = trim($row['nombre_ann_lectivo']);
      
          $codigo_bachillerato = trim($row['codigo_bach_o_ciclo']);
          $codigo_grado = trim($row['codigo_grado']);
          $codigo_seccion = trim($row['codigo_seccion']);
          $codigo_ann_lectivo = trim($row['codigo_ann_lectivo']);
          $codigo_turno = trim($row['codigo_turno']);
        break;
      }
// Obtener el Encargado de Grado.
$codigo_all_ = substr($codigo_all,0,8);
$query_encargado = "SELECT eg.id_encargado_grado, eg.encargado, btrim(p.nombres || CAST(' ' AS VARCHAR) || p.apellidos) as nombre_docente, eg.codigo_docente, bach.nombre, gann.nombre, sec.nombre, ann.nombre
            FROM encargado_grado eg
              INNER JOIN personal p ON eg.codigo_docente = p.id_personal
              INNER JOIN bachillerato_ciclo bach ON eg.codigo_bachillerato = bach.codigo
              INNER JOIN ann_lectivo ann ON eg.codigo_ann_lectivo = ann.codigo
              INNER JOIN grado_ano gann ON eg.codigo_grado = gann.codigo
              INNER JOIN seccion sec ON eg.codigo_seccion = sec.codigo
                WHERE btrim(bach.codigo || gann.codigo || sec.codigo || ann.codigo) = '".$codigo_all_."' and eg.encargado = 't' ORDER BY p.nombres";
//consulta para las notas finales y nombre de asignaturas.
  $query = "SELECT DISTINCT a.codigo_nie, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno || CAST(', ' AS VARCHAR) || a.nombre_completo) as apellido_alumno,
            a.nombre_completo, btrim(a.apellido_paterno || CAST(' ' AS VARCHAR) || a.apellido_materno) as apellidos_alumno, 
            am.codigo_bach_o_ciclo, am.pn, bach.nombre as nombre_bachillerato, am.codigo_ann_lectivo, ann.nombre as nombre_ann_lectivo, am.codigo_grado, am.id_alumno_matricula as codigo_matricula,
            gan.nombre as nombre_grado, am.codigo_seccion, am.retirado, a.genero,
            sec.nombre as nombre_seccion, ae.codigo_alumno, id_alumno, n.codigo_alumno, n.codigo_asignatura, asig.nombre AS n_asignatura, n.nota_final, n.recuperacion, asig.nombre as nombre_asignatura, aaa.orden, n.nota_recuperacion_2,
            asig.codigo_area
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
                  WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_all."'
                    and aaa.codigo_ann_lectivo = '".substr($codigo_all,6,2)."' ORDER BY apellido_alumno, aaa.orden ASC";
                    
 //consulta para obtener el total de alumnos.
 $query_total_alumnos = "SELECT count(*) as total_alumnos
  FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f'
    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
      WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";

//consulta para obtener el total de alumnos masculino.
$query_total_alumnos_matricula_inicial_masculino = "SELECT count(*) as total_alumnos_matricula_inicial_masculino
  FROM alumno a
    INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and a.genero = 'm'
    INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
    INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
    INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
    INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
      WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";

//consulta para obtener el total de alumnos femenino.
$query_total_alumnos_matricula_inicial_femenino = "SELECT count(*) as total_alumnos_matricula_inicial_femenino
FROM alumno a
  INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and a.genero = 'f'
  INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
  INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
  INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
  INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
    WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";


//consulta para obtener el total de alumnos masculino. RETIRNADOS
$query_total_alumnos_retirados_masculino = "SELECT count(*) as total_alumnos_retirados_masculino
FROM alumno a
INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and a.genero = 'm' and am.retirado = 't'
INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";

//consulta para obtener el total de alumnos FEMEFINO RETIRADOS
$query_total_alumnos_retirados_femenino = "SELECT count(*) as total_alumnos_retirados_femenino
FROM alumno a
INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno  and a.genero = 'f' and am.retirado = 't'
INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";


//consulta para obtener el total de alumnos masculino.
 $query_total_alumnos_m = "SELECT count(*) as total_alumnos_masculino
FROM alumno a
INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' and a.genero = 'm'
INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";
        
//consulta para obtener el total de alumnos femenino.
 $query_total_alumnos_f = "SELECT count(*) as total_alumnos_femenino
FROM alumno a
INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' and a.genero = 'f'
INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo  || am.codigo_turno) = '".$codigo_all."'";

//  mostrar los valores de la consulta
$result = $db_link -> query($query);
$result_encabezado = $db_link -> query($query);
$result_asignaturas = $db_link -> query($query);
$result_encargado = $db_link -> query($query_encargado);
$result_total_alumnos = $db_link -> query($query_total_alumnos);
$result_total_alumnos_m = $db_link -> query($query_total_alumnos_m);
$result_total_alumnos_f = $db_link -> query($query_total_alumnos_f);
$result_promovidos_retenidos = $db_link -> query($query);
$result_total_alumnos_matricula_inicial_masculino = $db_link -> query($query_total_alumnos_matricula_inicial_masculino);
$result_total_alumnos_matricula_inicial_femenino = $db_link -> query($query_total_alumnos_matricula_inicial_femenino);

$result_total_alumnos_retirados_masculino = $db_link -> query($query_total_alumnos_retirados_masculino);
$result_total_alumnos_retirados_femenino = $db_link -> query($query_total_alumnos_retirados_femenino);
//  cuenta el total de alumnos para colocar en la estadistica.
$ji = 1; $total_alumnos_masculino = 0; $total_promovidos_m = 0; $total_promovidos_f = 0; $contar_p_m = 0; $generos = ''; $notas = 0;
$total_promovidos_m = 0; $total_promovidos_f = 0; $contar_r_m = 0; $total_retenidos_m = 0; $contar_r_f = 0; $contar_p_f = 0; $total_retenidos_f = 0;
$nueva_nota_final = 0;

while($rows_promovidos_retenidos = $result_promovidos_retenidos -> fetch(PDO::FETCH_BOTH))
  {
    $codigo_matricula = $rows_promovidos_retenidos['codigo_matricula'];
    $codigo_alumno = $rows_promovidos_retenidos['codigo_alumno'];
    $apellido_alumno = $rows_promovidos_retenidos['apellido_alumno'];
    $generos = $rows_promovidos_retenidos['genero'];

    $nota_r_1 = $rows_promovidos_retenidos['recuperacion'];
    $nota_r_2 = $rows_promovidos_retenidos['nota_recuperacion_2'];
    $nota_final = $rows_promovidos_retenidos['nota_final'];
    $CodigoArea = trim($rows_promovidos_retenidos['codigo_area']);
// CALCULO DE LA NOTA FINAL EN RELACIÓN A LA RECUPERACIÓN UNO Y DOS.    
  if($nota_r_1 != 0){
        $nueva_nota_final = round(($nota_final + $nota_r_1)/2,0);
          if($nueva_nota_final < 6){
              if($nota_r_2 != 0){
                  $nueva_nota_final = round(($nota_final + $nota_r_2)/2,0);
              }
          }
        $notas = $nueva_nota_final;
      }
  else{
            $notas = number_format($rows_promovidos_retenidos['nota_final'],0);
      } //
/////////////////////////////////////////////////////////////////////////////////////////////
            switch($CodigoArea){
              case ($CodigoArea == "01" || $CodigoArea == "03"): 
                contar_promovidos_media($generos, $notas, $contar_evaluar);
              break;
            }
            ////////////////////////////////////////////////////////////////////
            //////// CONTAR CUANTAS ASIGNATURAS TIENE CADA MODALIDAD.
            //////////////////////////////////////////////////////////////////
            // buscar la consulta y la ejecuta.
            //consulta_contar(1,0,$codigo_all,'','','',$db_link,'');
            $query_asig = "SELECT count(*) as total_asignaturas FROM a_a_a_bach_o_ciclo
                  WHERE btrim(codigo_bach_o_ciclo || codigo_grado || codigo_ann_lectivo) = '".substr($codigo_all,0,4) . substr($codigo_all,6,2) ."'";
              // ejecutar la consulta.
              $result = $db_link -> query($query_asig);
              // EJECUTAR CONDICIONES PARA EL NOMBRE DEL NIVEL Y EL N�MERO DE ASIGNATURAS.
              $total_asignaturas = 0;	
            while($row = $result -> fetch(PDO::FETCH_BOTH))	// RECORRER PARA EL CONTEO DE Nº DE ASIGNATURAS.
                {
                    $total_asignaturas = (trim($row['total_asignaturas']));
                }
          $salir = 1; $nombre_asignatura = array(); $nombre_bachillerato = ""; $nombre_seccion = "";
          // QUERY PARA BUSCAR LOS NOMBRES DE LAS ASIGNATURAS
          $query_nombres_asignaturas = "SELECT aaa.codigo_asignatura, asig.nombre as nombre_asignatura, bach.nombre as nombre_bachillerato, gr.nombre as nombre_grado 
                FROM a_a_a_bach_o_ciclo aaa 
                INNER JOIN asignatura asig ON asig.codigo = aaa.codigo_asignatura 
                INNER JOIN bachillerato_ciclo bach ON bach.codigo = aaa.codigo_bach_o_ciclo
                INNER JOIN grado_ano gr ON gr.codigo = aaa.codigo_grado
                        WHERE btrim(aaa.codigo_bach_o_ciclo || aaa.codigo_grado || aaa.codigo_ann_lectivo) = '".substr($codigo_all,0,4) . substr($codigo_all,6,2) ."' ORDER BY aaa.orden";
                          " ORDER BY aaa.codigo_asignatura";
          $result_asignaturas = $db_link -> query($query_nombres_asignaturas);
          while($rows = $result_asignaturas -> fetch(PDO::FETCH_BOTH))
            {
                    $nombre_asignatura[] = convertirtexto(trim($rows['nombre_asignatura']));
                    $nombre_bachillerato = trim($rows['nombre_bachillerato']);
                    $nombre_grado = trim($rows['nombre_grado']);
            }
             //print_r($nombre_asignatura);/
            //contar el total de promovidos segun genero y si contar_p_m es mayor igual que cinco.
            $promocion = "No";
            if($ji == $total_asignaturas)
              {
                if($contar_r_m > 0)
                  {$total_retenidos_m++;
                  $promocion = "No";
                }
                else{
                  if($contar_p_m > 0)
                    {$total_promovidos_m++;
                      $promocion = "Si";
                    }
                  }
                
                if($contar_r_f > 0)
                  {$total_retenidos_f++;
                    $promocion = "No";
                  }
                else{
                  if($contar_p_f > 0)
                    {$total_promovidos_f++;
                      $promocion = "Si";
                    }
                  }
                  
                $contar_r_m = 0;
                $contar_p_m = 0;
                
                $contar_r_f = 0;
                $contar_p_f = 0;
              }		
              
            // Incremento del Número.
            if($ji == $total_asignaturas){
              $ji = 1;
                // actualizar estatus PROMOCIONEN ALUMNO MATRICULA.
                if($promocion == "Si"){
                  $codigo_promocion = 3;
                }else{
                  $codigo_promocion = 4;
                }
                //print "<br>" . "Apellido - Nombres: " . $apellido_alumno . " Código Alumno: " . $codigo_alumno . " Código Matricula: " . $codigo_matricula . " Promoción: " . $promocion;
                $query_update_matricula = "UPDATE alumno_matricula SET codigo_resultado = '$codigo_promocion' WHERE id_alumno_matricula = '$codigo_matricula' and codigo_alumno = '$codigo_alumno'";
                  $result_uddate_matricula = $db_link -> query($query_update_matricula);
            }else{
              $ji++;
            }
        }        
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
            $this->MultiCell(43,4,$txt,0,'C');
        $this->Rotate(0);
    }
    function RotatedTextMultiCellAspectos($x,$y,$txt,$angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);
        $this->SetXY($x,$y);
            $this->MultiCell(42,3,$txt,0,'C');
        $this->Rotate(0);
    }
    function RotatedTextMultiCellDireccion($x,$y,$txt,$angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);
        $this->SetXY($x,$y);
            $this->MultiCell(90,4,convertirtexto($txt),0,'J');
        $this->Rotate(0);
    }
    //Cabecera de página
    function Header()
    {
      global $nombre_asignatura, $valor_x_encabezado, $total_asignaturas, $print_bachillerato;
      // dParte superior izquierda.
        $this->SetFont('Arial','',8); // I : Italica; U: Normal;
        $this->Cell(343,8,convertirtexto($print_bachillerato),0,0,'R');
        
        if($valor_x_encabezado == true)
        {    
          // PRIEMRA PARTE DEL RECTANGULO.
          $this->Rect(10,5,232,50);
          // segunda PARTE DEL RECTANGULO. numero de orden
              $this->Rect(10,5,7,50);
              $this->RotatedText(15,38,convertirtexto('N° de Orden'),90);
          // segunda PARTE DEL RECTANGULO. numero de orden
              $this->Rect(17,5,20,50);
              $this->RotatedText(30,38,convertirtexto('N° de NIE'),90);
          // tercera PARTE DEL RECTANGULO.   nombre del alumno
              //$this->Rect(17,45,85,50);
              $this->SetFont('Arial','',11); // I : Italica; U: Normal;
              $this->SetXY(38,25);
              $this->SetFillColor(255,255,255);
              $this->MultiCell(65,8,convertirtexto('Nombre de los Alumnos(as) en orden alfabético de apellidos'),0,2,'C',true);
          // cuarta PARTE DEL RECTANGULO. asignatura
              $this->SetFont('Arial','',13); // I : Italica; U: Normal;
              //$this->Rect(127,45,70,7);
              $this->SetXY(37,5);
              $this->Cell(65,8,'AREA ASIGNATURA',1,2,'C');
              $this->SetXY(102,5);
              $this->Cell(70,8,'BASICA',1,2,'C',true);
          // quinta PARTE DEL RECTANGULO. educacion moral y civica
              $this->SetFont('Arial','',9); // I : Italica; U: Normal;
              $this->SetXY(172,5);
              $this->Cell(30,8,'COMPLEMENTARIA',1,2,'C',true);
          // sexta PARTE DEL RECTANGULO. educacion moral y civica
              //$this->Rect(205,45,50,7);
              $this->SetXY(202,5);
              $this->SetFont('Arial','',7); // I : Italica; U: Normal;
              $this->Cell(40,8,'COMPETENCIAS CIUDADANAS',1,2,'C',true);
              $this->SetFont('Arial','',8); // I : Italica; U: Normal;
              //$this->Cell(60,3,convertirtexto('Aspectos de la Conducta'),0,2,'C');
          // cuarta PARTE DEL RECTANGULO. asignaturas nombres
              $espacio = 0;
              for($ix=0;$ix<=$total_asignaturas-1;$ix++){
                if($ix >= 0 && $ix <= 9){
                  $this->Rect(102+$espacio,13,10,42);
                  $this->RotatedTextMultiCellAspectos(105+$espacio,54,$nombre_asignatura[$ix],90);}
                else{
                  $this->Rect(102+$espacio,13,10,42);
                  $this->RotatedTextMultiCellAspectos(102+$espacio,54,$nombre_asignatura[$ix],90);}
                $espacio = $espacio + 10;}
          // cuarta PARTE DEL RECTANGULO. calificacion
              $espacio = 0;
              $this->SetFont('Arial','',7); // I : Italica; U: Normal;
              $this->SetFont('Arial','b',13);
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
        $this->Ln();
        //Restauración de colores y fuentes
        $this->SetFillColor(244,246,244);
        $this->SetTextColor(0);
        $this->SetFont('');
        //Datos
        //$fill=true;
    }
}
//************************************************************************************************************************
//	verificar si existe el grado.
//************************************************************************************************************************
//consulta para obtener el total de alumnos masculino.
     $query_verificar = "SELECT a.id_alumno as total_alumnos_masculino
        FROM alumno a
          INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno and am.retirado = 'f' and a.genero = 'm'
          INNER JOIN bachillerato_ciclo bach ON bach.codigo = am.codigo_bach_o_ciclo
          INNER JOIN grado_ano gan ON gan.codigo = am.codigo_grado
          INNER JOIN seccion sec ON sec.codigo = am.codigo_seccion
          INNER JOIN ann_lectivo ann ON ann.codigo = am.codigo_ann_lectivo
            WHERE btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_ann_lectivo || am.codigo_turno) = '".$codigo_all."'";

		    $result_verificar = $db_link -> query($query_verificar);
		    $verificar = $result_verificar -> rowCount();

          if($verificar != 0)	// IF PRINCIPAL QUE VERIFICA SI HAY REGISTROS.
          {
//************************************************************************************************************************						
// Creando el Informe.
    $pdf=new PDF('L','mm','Legal');
    #Establecemos los márgenes izquierda, arriba y derecha: 
    $pdf->SetMargins(5, 5, 5);
    #Establecemos el margen inferior: 
    $pdf->SetAutoPageBreak(true,5);
//Títulos de las columnas
    $header=array('');
    $pdf->AliasNbPages();
    $pdf->AddPage();
                                                     
//  cuenta el total de alumnos para colocar en la estadistica.
    $total_alumnos_masculino = 0;
	while($rows_total_alumnos_m = $result_total_alumnos_m -> fetch(PDO::FETCH_BOTH))
    {
     		$total_alumnos_masculino = trim($rows_total_alumnos_m['total_alumnos_masculino']);
    }

//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
    $total_alumnos_matricula_inicial_masculino = 0;
    while($rows_total_alumnos_m = $result_total_alumnos_matricula_inicial_masculino -> fetch(PDO::FETCH_BOTH))
        {
            $total_alumnos_matricula_inicial_masculino = trim($rows_total_alumnos_m['total_alumnos_matricula_inicial_masculino']);
        }
//  cuenta el total de alumnos para colocar en la estadistica MATRICULA INICIAL..
        $total_alumnos_matricula_inicial_femenino = 0;
        while($rows_total_alumnos_f = $result_total_alumnos_matricula_inicial_femenino -> fetch(PDO::FETCH_BOTH))
        {
            $total_alumnos_matricula_inicial_femenino = trim($rows_total_alumnos_f['total_alumnos_matricula_inicial_femenino']);
        }
 //  cuenta el total de alumnos para colocar en la estadistica RETIRADOS.
        $total_alumnos_retirados_masculino = 0;
        while($rows_total_alumnos_m = $result_total_alumnos_retirados_masculino -> fetch(PDO::FETCH_BOTH))
        {
            $total_alumnos_retirados_masculino = trim($rows_total_alumnos_m['total_alumnos_retirados_masculino']);
        }
//  cuenta el total de alumnos para colocar en la estadistica RETIRADOS..
    $total_alumnos_retirados_femenino = 0;
    while($rows_total_alumnos_f = $result_total_alumnos_retirados_femenino -> fetch(PDO::FETCH_BOTH))
        {
            $total_alumnos_retirados_femenino = trim($rows_total_alumnos_f['total_alumnos_retirados_femenino']);
        }
//  cuenta el total de alumnos para colocar en la estadistica.
    $total_alumnos_femenino = 0;
    while($rows_total_alumnos_f = $result_total_alumnos_f -> fetch(PDO::FETCH_BOTH))
    {
     		$total_alumnos_femenino = trim($rows_total_alumnos_f['total_alumnos_femenino']);
    }
//  cuenta el total de alumnos para colocar en la estadistica.
    $total_alumnos = 0;
	while($rows_total_alumnos = $result_total_alumnos -> fetch(PDO::FETCH_BOTH))
    {
     		$total_alumnos = trim($rows_total_alumnos['total_alumnos']);
    }
//  Nombre del Encargado.
    $nombre_encargado = '';
	while($rows_encargado = $result_encargado -> fetch(PDO::FETCH_BOTH))
    {
     		$nombre_encargado = trim($rows_encargado['nombre_docente']);
     		$codigo_docente = trim($rows_encargado['codigo_docente']);
    }
//  cuenta el numero de asignaturas y asigna el valor a una matriz.    
/////////////////////////////////////////////////////////////////////////////////////////
// Consulta para grabar o actualizar en la tabla estadistica_grados
$codigo_all_ = substr($codigo_all,0,8);
     $query_estadistica = "SELECT esta.genero, esta.matricula_inicial, esta.retirados, esta.matricula_final, esta.promovidos, esta.retenidos,
     		esta.codigo_docente, esta.codigo_grado, esta.codigo_seccion, esta.codigo_bachillerato_ciclo, esta.codigo_ann_lectivo
		FROM estadistica_grados esta
		INNER JOIN personal p ON esta.codigo_docente = p.id_personal
		INNER JOIN bachillerato_ciclo bach ON bach.codigo = esta.codigo_bachillerato_ciclo
		INNER JOIN grado_ano gan ON gan.codigo = esta.codigo_grado
		INNER JOIN seccion sec ON sec.codigo = esta.codigo_seccion
		INNER JOIN ann_lectivo ann ON ann.codigo = esta.codigo_ann_lectivo
		WHERE btrim(esta.codigo_bachillerato_ciclo || esta.codigo_grado || esta.codigo_seccion || esta.codigo_ann_lectivo) = '".$codigo_all_."' ORDER BY id_estadistica_grado";

		$result_estadistica = $db_link -> query($query_estadistica);
		$fila = $result_estadistica -> rowCount();

		if($fila == 0)
		{
			$query_insert_estadistica = "INSERT INTO estadistica_grados (genero, codigo_bachillerato_ciclo, codigo_ann_lectivo, codigo_grado, codigo_seccion, codigo_docente, matricula_final, retenidos, promovidos) VALUES ('Masculino','$codigo_bachillerato','$codigo_ann_lectivo','$codigo_grado','$codigo_seccion',$codigo_docente,$total_alumnos_masculino,$total_retenidos_m,$total_promovidos_m)";
			$result_insert = $db_link -> query($query_insert_estadistica);
			$query_insert_estadistica = "INSERT INTO estadistica_grados (genero, codigo_bachillerato_ciclo, codigo_ann_lectivo, codigo_grado, codigo_seccion, codigo_docente, matricula_final, retenidos, promovidos) VALUES ('Femenino','$codigo_bachillerato','$codigo_ann_lectivo','$codigo_grado','$codigo_seccion',$codigo_docente,$total_alumnos_femenino,$total_retenidos_f,$total_promovidos_f)";
			$result_insert = $db_link -> query($query_insert_estadistica);
		}
		else
		{
			$query_update_estadistica = "UPDATE estadistica_grados SET
		  	matricula_final = $total_alumnos_masculino,
			  retenidos = $total_retenidos_m,
			  promovidos = $total_promovidos_m
			    WHERE genero = 'Masculino' and codigo_bachillerato_ciclo = '$codigo_bachillerato' and codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_grado = '$codigo_grado' and codigo_seccion = '$codigo_seccion'";
      $result_udpate = $db_link -> query($query_update_estadistica);

			$query_update_estadistica = "UPDATE estadistica_grados SET
			matricula_final = $total_alumnos_femenino,
			retenidos = $total_retenidos_f,
			promovidos = $total_promovidos_f
			WHERE genero = 'Femenino' and codigo_bachillerato_ciclo = '$codigo_bachillerato' and codigo_ann_lectivo = '$codigo_ann_lectivo' and codigo_grado = '$codigo_grado' and codigo_seccion = '$codigo_seccion'";
			$result_udpate = $db_link -> query($query_update_estadistica);
		}
/////////////////////////////////////////////////////////////////////////////////////////
// Sumar datos de matricula inicial, retirados masculino y femenino
//  cuenta el total de alumnos para colocar en la estadistica.
   $matricula_inicial_m_f = array(); $retirados_m_f = array();
//	actualizar el query
    $result_estadistica = $db_link -> query($query_estadistica);
	$fila = $result_estadistica -> rowCount();
	
	if($fila !=0)
	{
		while($rows_total_matricula = $result_estadistica -> fetch(PDO::FETCH_BOTH))
		{
		  $matricula_inicial_m_f[] = $rows_total_matricula['matricula_inicial'];
		  $retirados_m_f[] = $rows_total_matricula['retirados'];
		}
	}
/////////////////////////////////////////////////////////////////////////////////////////
// configuración de colores par ala linea
    $pdf->SetDrawcolor(0,0,0);
/////////////////////////////////////////////////////////////////////////////////////////
// Imprimir el primer encabezado REGISTRO DE EVALUACION....
    $pdf->SetXY(70,10);
    $pdf->SetFont('Arial','',18); // I : Italica; U: Normal;
    $pdf->Cell(235,14,convertirtexto('REGISTRO DE EVALUACIÓN DEL RENDIMIENTO ESCOLAR DE EDUCACIÓN MEDIA'),0,0,'L');
    
    $pdf->SetXY(80,25);
    $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
    $pdf->Cell(235,5,convertirtexto('CUADRO FINAL DE EVALUACIÓN DE'),0,2,'L');
    $pdf->Cell(235,5,convertirtexto('NOMBRE DEL CENTRO EDUCATIVO:'),0,2,'L');
    $pdf->Cell(235,5,convertirtexto('DIRECCIÓN:'),0,2,'L');
    $pdf->Cell(235,5,'DEPARTAMENTO:',0,2,'L');
    
// Imprimir el primer encabezado EL ESCUDO DE EL SALVADOR... Y TEXTO. 3 LINEAS
    $img = $_SERVER['DOCUMENT_ROOT'].'/registro_academico/img/escudo.jpg';
    $pdf->Image($img,35,10,20,20);
    $pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
    $pdf->SetXY(15,30);
    $pdf->Cell(60,4,convertirtexto('República de El Salvador'),0,2,'C');
    $pdf->Cell(60,4,convertirtexto('Ministerio de Educación'),0,2,'C');
    $pdf->Cell(60,4,convertirtexto('Dirección Nacional de Educación Media'),0,2,'C');
// PRIEMRA PARTE DEL RECTANGULO.
    $pdf->Rect(10,45,232,50);
// segunda PARTE DEL RECTANGULO. numero de orden
    $pdf->Rect(10,45,7,50);
    $pdf->RotatedText(15,80,convertirtexto('N° de Orden'),90);
// segunda PARTE DEL RECTANGULO. numero de orden
    $pdf->Rect(17,45,20,50);
    $pdf->RotatedText(30,80,convertirtexto('N° de NIE'),90);
// tercera PARTE DEL RECTANGULO.   nombre del alumno
    //$pdf->Rect(17,45,85,50);
    $pdf->SetFont('Arial','',11); // I : Italica; U: Normal;
    $pdf->SetXY(38,65);
    $pdf->SetFillColor(255,255,255);
    $pdf->MultiCell(65,8,convertirtexto('Nombre de los Alumnos(as) en orden alfabético de apellidos'),0,2,'C',true);
// cuarta PARTE DEL RECTANGULO. asignatura
    $pdf->SetFont('Arial','',13); // I : Italica; U: Normal;
    //$pdf->Rect(127,45,70,7);
    $pdf->SetXY(37,45);
    $pdf->Cell(65,8,'AREA ASIGNATURA',1,2,'C');
    $pdf->SetXY(102,45);
    $pdf->Cell(70,8,'BASICA',1,2,'C',true);
// quinta PARTE DEL RECTANGULO. educacion moral y civica
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    $pdf->SetXY(172,45);
    $pdf->Cell(30,8,'COMPLEMENTARIA',1,2,'C',true);
// sexta PARTE DEL RECTANGULO. educacion moral y civica
    //$pdf->Rect(205,45,50,7);
    $pdf->SetXY(202,45);
    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
    $pdf->Cell(40,8,'COMPETENCIAS CIUDADANAS',1,2,'C',true);
    $pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
// cuarta PARTE DEL RECTANGULO. asignaturas nombres
    $espacio = 0;
    for($ix=0;$ix<=$total_asignaturas-1;$ix++){
      if($ix >= 0 && $ix <= 9){
        $pdf->Rect(102+$espacio,53,10,42);
        $pdf->RotatedTextMultiCellAspectos(105+$espacio,95,$nombre_asignatura[$ix],90);}
      else{
        $pdf->Rect(102+$espacio,53,10,42);
        $pdf->RotatedTextMultiCellAspectos(102+$espacio,95,$nombre_asignatura[$ix],90);}
      $espacio = $espacio + 10;}
// cuarta PARTE DEL RECTANGULO. calificacion
    $espacio = 0;
    $pdf->SetFont('Arial','',7); // I : Italica; U: Normal;
    $pdf->SetFont('Arial','b',13);
// cuarta PARTE DEL RECTANGULO. aspectos de la conducta
// cuarta PARTE DEL RECTANGULO. Escala de Calificación.  y texto.
    $pdf->Rect(260,45,90,9);
    $pdf->SetFillColor(235, 237, 239);
    $pdf->Rect(260,45,90,9,"F");
    $pdf->SetFont('Arial','',9); // I : Italica; U: Normal;
    
    $pdf->SetXY(260,45);
    $pdf->Cell(90,4.5,convertirtexto('ESCALA DE VALORACIÓN PARA LAS'),'LRT',2,'C');
    $pdf->Cell(90,4.5,convertirtexto('COMPETENCIAS CIUDADANAS'),'LRB',2,'C');
		
		$pdf->Cell(30,8,'E: Excelente',1,0,'L');
		$pdf->Cell(30,8,'MB: Muy Bueno',1,0,'L');
		$pdf->Cell(30,8,'B: Bueno',1,1,'L');
		
		$pdf->SetFont('Arial','',8); // I : Italica; U: Normal;
		$pdf->SetX(260);
		$pdf->Cell(30,5,'Dominio alto de la','LRT',0,'L');
		$pdf->Cell(30,5,'Dominio medio de la','LRT',0,'L');
		$pdf->Cell(30,5,'Dominio bajo de la','LRT',1,'L');
		$pdf->SetX(260);
		
		$pdf->Cell(30,5,'competencia','LRB',0,'L');
		$pdf->Cell(30,5,'competencia','LRB',0,'L');
		$pdf->Cell(30,5,'competencia','LRB',0,'L');
		$pdf->SetFont('Arial','',10); // I : Italica; U: Normal;
// cuarta PARTE DEL RECTANGULO. ESTADISTICA Y TEXTO.
    $pdf->Rect(260,80,90,30); // fila principal
      $pdf->Rect(260,80,18,30); // columna del sexo
			
    $pdf->Rect(260,80,90,15); // fila del sexo, matricula inicial....
			
    $pdf->Rect(260,95,90,5); // FILA MASCULINO
      $pdf->Rect(290,80,15,30); // COLUMNA
			
    $pdf->Rect(260,100,90,5); // FILA FEMENONO
      $pdf->Rect(305,80,15,30); // COLUMNA
			
      $pdf->Rect(320,80,15,30);
    
    $pdf->SetXY(260,80);
    $pdf->Cell(90,5,convertirtexto('ESTADÍSTICA'),1,2,'C', true);
    $pdf->SetXY(258,88);
    $pdf->Cell(20,5,'SEXO',0,0,'C');
    $pdf->SetFont('Arial','',7);
    $pdf->SetXY(276,86);
    $pdf->MultiCell(15,4.5,'Matricula Inicial',0,'C');
    $pdf->SetXY(276,96);
    $pdf->SetFont('Arial','',10);
	if($fila !=0)
	{
		$pdf->Cell(15,4.5,$total_alumnos_matricula_inicial_masculino,0,0,'C');
		$pdf->SetXY(276,101);
		$pdf->Cell(15,4.5,$total_alumnos_matricula_inicial_femenino,0,0,'C');
		$pdf->SetXY(276,106);
		$pdf->Cell(15,4.5,$total_alumnos_matricula_inicial_masculino+$total_alumnos_matricula_inicial_femenino,0,0,'C');		
	}else{
		$pdf->Cell(15,4.5,'xxxx',0,0,'C');
		$pdf->SetXY(275,101);
		$pdf->Cell(15,4.5,'',0,0,'C');
		$pdf->SetXY(275,106);
		$pdf->Cell(15,4.5,'',0,0,'C');		
	}

    $pdf->SetXY(290,86);
    $pdf->SetFont('Arial','',7);
    $pdf->Cell(15,4.5,'Retirados',0,0,'C');
    $pdf->SetXY(290,96);
    $pdf->SetFont('Arial','',10);
	
	if($fila !=0)
	{
    $pdf->Cell(15,4.5,$total_alumnos_retirados_masculino,0,0,'C');
    $pdf->SetXY(290,101);
    $pdf->Cell(15,4.5,$total_alumnos_retirados_femenino,0,0,'C');
    $pdf->SetXY(290,106);
    $pdf->Cell(15,4.5,$total_alumnos_retirados_masculino+$total_alumnos_retirados_femenino,0,0,'C');		
	}else{
    $pdf->Cell(15,4.5,'',0,0,'C');
    $pdf->SetXY(280,101);
    $pdf->Cell(15,4.5,'',0,0,'C');
    $pdf->SetXY(280,106);
    $pdf->Cell(15,4.5,'',0,0,'C');				
	}

    $pdf->SetXY(305,86);
    $pdf->SetFont('Arial','',7);
    $pdf->MultiCell(15,4.5,'Matricula Final',0,'C');
    $pdf->SetXY(320,86);
    $pdf->Cell(15,4.5,'Promovidos',0,0,'C');
    $pdf->SetXY(320,96);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(15,4.5,$total_promovidos_m,0,0,'C');
    $pdf->SetXY(320,101);
    $pdf->Cell(15,4.5,$total_promovidos_f,0,0,'C');
    $pdf->SetXY(320,106);
    $pdf->Cell(15,4.5,$total_promovidos_f+$total_promovidos_m,0,0,'C');
    $pdf->SetXY(335,86);
    $pdf->SetFont('Arial','',7);
    $pdf->Cell(15,4.5,'Retenidos',0,0,'C');
    $pdf->SetXY(335,96);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(15,4.5,$total_retenidos_m,0,0,'C');
    $pdf->SetXY(335,101);
    $pdf->Cell(15,4.5,$total_retenidos_f,0,0,'C');
    $pdf->SetXY(335,106);
    $pdf->Cell(15,4.5,$total_retenidos_f+$total_retenidos_m,0,0,'C');
       
    // Masculino, Femenio y Total.
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY(260,95);
    $pdf->Cell(15,5,'MASCULINO',0,0,'L');
    $pdf->SetFont('Arial','',10);
    $pdf->SetXY(305,96);
    $pdf->Cell(15,5,$total_alumnos_masculino,0,0,'C');
    $pdf->SetFont('Arial','',8);
    $pdf->SetXY(260,100);
    $pdf->Cell(15,5,'FEMENINO',0,0,'L');
    $pdf->SetXY(305,101);
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(15,5,$total_alumnos_femenino,0,0,'C');
    $pdf->SetXY(260,105);
    $pdf->Cell(15,5,'TOTAL',0,0,'L');
    $pdf->SetXY(305,106);
    $pdf->Cell(15,5,$total_alumnos,0,0,'C');
    
    // PROMOVIDOS Y RETENIDOS.
    $pdf->SetFont('Arial','',11);
		$pdf->Rect(290,155,50,0);
    $pdf->SetXY(260,150);
		$pdf->Cell(11,5,'PROMOVIDOS:',0,0,'L');
		$pdf->SetXY(280,150);
    if(($total_promovidos_f + $total_promovidos_m) == 0){
      $pdf->Cell(65,5,strtolower("cero"),0,0,'C');
    }else{
      $pdf->Cell(65,5,strtolower(convertirTexto(num2letras($total_promovidos_f+$total_promovidos_m))),0,0,'C');
    }
		
    $pdf->Rect(290,170,50,0);
		
		$pdf->SetXY(260,165);
		$pdf->Cell(11,5,'RETENIDOS:',0,0,'L');
		$pdf->SetXY(280,165);
    $total_retenidos_m_f = $total_retenidos_f + $total_retenidos_m;
    if($total_retenidos_m_f == 0){
      $pdf->Cell(65,5,"ninguno",0,0,'C');  
    }else{
      $pdf->Cell(65,5,strtolower(convertirtexto(num2letras($total_retenidos_f+$total_retenidos_m))),0,0,'C');  
    }
///////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////
//  INICIO PARA MOSTRAR LOS DATOS DE LA TABLA.
    //Datos de la institucion.
            $nombre_director =  $_SESSION['nombre_director'];
            $pdf->SetXY(152,24.3);
            $pdf->SetFont('Arial','b',11); // I : Italica; U: Normal;
            $pdf->Cell(85,5.5,convertirtexto($nombre_grado .' SECCIÓN: '.$print_seccion.'    CÓDIGO DE INFRAESTRUCTURA: '.$_SESSION['codigo']),0,2,'L');
            $pdf->Cell(140,6,cambiar_de_del($_SESSION['institucion']),0,2,'C');
            $pdf->SetXY(105,34.5);
            $pdf->Cell(85,6,convertirtexto($_SESSION['direccion']).'                       MUNICIPIO: '.$_SESSION['nombre_municipio'],0,2,'L');
            $pdf->SetXY(115,39.3);
            $pdf->Cell(85,6,convertirtexto($_SESSION['nombre_departamento'].'                   Nº de acuerdo de creación: ').$_SESSION['numero_acuerdo'],0,2,'L');    
//Datos para nombres, asignaturas.
    $pdf->SetXY(10,95);
    $pdf->SetFont('Arial','',10);
    $fill = true;$i=1;  $suma = 0; $numero = 1; $nota_concepto = 0; $conteo_alumnos = 0; 
     $total_puntos_01_array = array(); $total_puntos_02_array = array(); $total_puntos_03_array = array();
     $total_puntos_04_array = array(); $total_puntos_05_array = array(); $total_puntos_06_array = array();
		 $total_puntos_07_array = array(); $nota_final_ = 0;
     // Define el alto de la fila.
     $h=array(5); //determina el ancho de las columnas
     $result = $db_link -> query($query);
		while($row = $result -> fetch(PDO::FETCH_BOTH))
          {
            switch($i)
            {
              case 1:
                // para el color de las filas.
				        $fill=!$fill;
                $pdf->SetX(10);
                  $pdf->Cell(7,$h[0],$numero,1,0,'C',$fill);  // N| de Orden.
                  $pdf->Cell(20,$h[0],trim($row['codigo_nie']),1,0,'R',$fill);  // N| de Orden.
                  $pdf->Cell(65,$h[0],cambiar_de_del(trim($row['apellido_alumno'])),1,0,'l',$fill);  // nombre del alumno.
                  // camibar color menor de 5.
                    $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                      if($nota_final_ < 6 ){
                        $pdf->SetTextColor(255,0,0);
                          $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                        $pdf->SetTextColor(0,0,0);
                      }else{
                        $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                      }
                    $total_puntos_01_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
										$conteo_alumnos++;
                    break;  // nota final
              case 2:
                $total_puntos_02_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                if($nota_final_ < 6 ){
                  $pdf->SetTextColor(255,0,0);
                    $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                  $pdf->SetTextColor(0,0,0);
                }else{
                  $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                }
                //$pdf->Cell(10,$h[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),1,0,'C');
              break;
              case 3:
                $total_puntos_03_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                if($nota_final_ < 6 ){
                  $pdf->SetTextColor(255,0,0);
                    $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                  $pdf->SetTextColor(0,0,0);
                }else{
                  $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                }
                //$pdf->Cell(10,$h[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),1,0,'C');
              break;
              case 4:
                $total_puntos_04_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                if($nota_final_ < 6 ){
                  $pdf->SetTextColor(255,0,0);
                    $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                  $pdf->SetTextColor(0,0,0);
                }else{
                  $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                }
                //$pdf->Cell(10,$h[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),1,0,'C');
              break;              
              case 5:
                $total_puntos_05_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                if($nota_final_ < 6 ){
                  $pdf->SetTextColor(255,0,0);
                    $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                  $pdf->SetTextColor(0,0,0);
                }else{
                  $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                }
                //$pdf->Cell(10,$h[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),1,0,'C');
              break;              
              case 6:
                $total_puntos_06_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                if($nota_final_ < 6 ){
                  $pdf->SetTextColor(255,0,0);
                    $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                  $pdf->SetTextColor(0,0,0);
                }else{
                  $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                }
                //$pdf->Cell(10,$h[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),1,0,'C');
              break;              
              case 7:
                $total_puntos_07_array[] = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $nota_final_ = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                if($nota_final_ < 6 ){
                  $pdf->SetTextColor(255,0,0);
                    $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                  $pdf->SetTextColor(0,0,0);
                }else{
                  $pdf->Cell(10,$h[0],$nota_final_,1,0,'C',$fill); 
                }
                //$pdf->Cell(10,$h[0],verificar_nota_media($row['nota_final'],$row['recuperacion']),1,0,'C');
              break;              
              case 8:
              	$nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $concepto_asignatura = cambiar_concepto($nota_concepto);
                $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;
              case 9:
                $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $concepto_asignatura = cambiar_concepto($nota_concepto);
                $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;                
              case 10:
                $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $concepto_asignatura = cambiar_concepto($nota_concepto);
                $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;                
              case 11:
                $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $concepto_asignatura = cambiar_concepto($nota_concepto);
                $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;
              case 12:
                $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                $concepto_asignatura = cambiar_concepto($nota_concepto);
                $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;
              case 13:
                  $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                  $concepto_asignatura = cambiar_concepto($nota_concepto);
                  $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;
              case 14:
                  $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                  $concepto_asignatura = cambiar_concepto($nota_concepto);
                  $pdf->Cell(10,$h[0],$concepto_asignatura,1,1,'C',$fill); break;
              case 15:
                /*
                    $nota_concepto = verificar_nota_media($row['nota_final'],$row['recuperacion']);
                    $concepto_asignatura = cambiar_concepto($nota_concepto);
                    $pdf->Cell(10,$h[0],$concepto_asignatura,1,0,'C',$fill); break;*/
                }
               
            
              // Salto de página.
              if($numero == 23 && $i == $total_asignaturas){
                $valor_x_encabezado = true;
                $pdf->Addpage();
                // cuarta PARTE DEL RECTANGULO. cuadro de la firma.
                    $pdf->Rect(270,150,50,40);
                    $pdf->RotatedText(290,187,'SELLO',0);
                //Crear una línea. Lugar. Line(x hacia la izq.,y - mueven hacia abajo,x1 hacia a la izq.,y1 mueven hacia abajo)
                    $pdf->RotatedText(255,35,'Lugar:',0);
                    $direccion_local = ($_SESSION['direccion']);
                    $pdf->RotatedTextMultiCellDireccion(290-((strlen($_SESSION['direccion']))/2),37,($direccion_local),0);
                    $pdf->Line(250,45,350,45);
										$pdf->SetY(55);
                //Crear una línea. Fecha.
                    $pdf->RotatedText(255,60,'Fecha:',0);
                    //$pdf->RotatedText(265,67,strtolower(num2letras($dia))." de ".$mes." de ".strtolower(num2letras($año)),0);
                    $pdf->RotatedText(265,67,trim($_SESSION['dia_entrega'])." de ".$NombreMes." de ".convertirtexto(strtolower(num2letras($año))),0);
                    $pdf->Line(250,70,350,70);
                //Crear una línea. F. Docente.
                    $pdf->RotatedText(255,92,'F:',0);
                    $pdf->Line(250,95,350,95);
                    $pdf->RotatedText(285-((strlen(trim($nombre_encargado)))/2),99,cambiar_de_del($nombre_encargado),0);
                    $pdf->RotatedText(285-((strlen('Docente'))/2),105,'Docente',0);
                //Crear una línea. F. Director.
                    $pdf->RotatedText(255,122,'F:',0);
                    $pdf->Line(250,125,350,125);
                    $pdf->RotatedText(285-((strlen(trim($nombre_director)))/2),132,cambiar_de_del($nombre_director),0);
                    $pdf->RotatedText(290-((strlen('Director'))/2),137,'Director',0);
              }              
              // Incremento del Número.
                if($i == $total_asignaturas){$numero++;$i = 1;}else{$i++;}
          }	// final del while.
		
	// Línea diagonal para los cuadros. en la primera página.
		if($numero <= 23 or $numero == 23)
		{
		 // Colocar línea diagonal si es menor a 19.
		    $valor_y1 = 0;
		    $linea_faltante =  23 - $numero;
        $numero_p = $numero - 1;
		
		   $valor_y1 = $pdf->GetY();
		   $pdf->Line(17,$valor_y1,247,210);

	      	for($i=0;$i<=$linea_faltante;$i++)
                  {
		   // Para el fondo de la fila.
		      $fill=!$fill;		    
                    $pdf->SetX(10);
                      $pdf->Cell(7,$h[0],$numero++,1,0,'C',$fill);  // N| de Orden.
                      $pdf->Cell(110,$h[0],'',1,0,'l');  // nombre del alumno.
                      //$pdf->Cell(20,6,'',1,0,'C');  // NIE
                      $pdf->Cell(10,$h[0],'',1,0,'C');  // nota final
                      
                      for($j=0;$j<=10;$j++){$pdf->Cell(10,$h[0],'',1,0,'C');}
                      $pdf->Ln();

                  }
             // Salto de página.
                $valor_x_encabezado = true;
                $pdf->Addpage();
				$pdf->SetY(75);
                // cuarta PARTE DEL RECTANGULO. cuadro de la firma.
                    $pdf->Rect(270,150,50,40);
                    $pdf->RotatedText(290,187,'SELLO',0);
                //Crear una línea. Lugar. Line(x hacia la izq.,y - mueven hacia abajo,x1 hacia a la izq.,y1 mueven hacia abajo)
                    $pdf->RotatedText(250,35,'Lugar:',0);
                    $pdf->RotatedTextMultiCellDireccion(290-((strlen($_SESSION['direccion']))/2),37,$_SESSION['direccion'],0);
                    $pdf->Line(250,45,350,45);
                    $pdf->SetY(55);
                //Crear una línea. Fecha.
                    $pdf->RotatedText(250,60,'Fecha:',0);
                    $pdf->RotatedText(265,67,trim($_SESSION['dia_entrega'])." de ".$NombreMes." de ".convertirtexto(strtolower(num2letras($año))),0);
                    $pdf->Line(250,70,350,70);
                //Crear una línea. F. Docente.
                    $pdf->RotatedText(250,92,'F:',0);
                    $pdf->Line(250,95,350,95);
                    $pdf->RotatedText(285-((strlen(trim($nombre_encargado)))/2),99,cambiar_de_del($nombre_encargado),0);
                    $pdf->RotatedText(285-((strlen('Docente'))/2),105,'Docente',0);
                //Crear una línea. F. Director.
                    $pdf->RotatedText(250,122,'F:',0);
                    $pdf->Line(250,125,350,125);
                    $pdf->RotatedText(285-((strlen(trim($nombre_director)))/2),132,cambiar_de_del($nombre_director),0);
                    $pdf->RotatedText(290-((strlen('Director'))/2),137,'Director',0);
		}
         // Línea diagonal para los cuadros. en la segunda página. 
		if($numero > 23){
			// Colocar línea diagonal si es menor a 19.
                $valor_y1 = 0;
				$linea_faltante =  50 - $numero;
                $numero_p = $numero - 1;
			//colocar la linea diagonal cuando es mayor de 23.
		  $valor_y1 = $pdf->GetY();
      $valor_x1 = $pdf->GetX();
		  $pdf->Line(17,$valor_y1,242,190);
		}
		// Escribir líneas faltantes.  
		for($i=0;$i<=$linea_faltante;$i++)
                  {
						// Para el fondo de la fila.
						$fill=!$fill;
						$pdf->SetX(10);
						$pdf->Cell(7,$h[0],$numero++,1,0,'C',$fill);  // N| de Orden.
            $pdf->Cell(20,$h[0],'',1,0,'C');  // NIE
						$pdf->Cell(65,$h[0],'',1,0,'l');  // nombre del alumno.

						//$pdf->Cell(10,$h[0],'',1,0,'C');  // nota final
                      
						for($j=0;$j<=13;$j++){$pdf->Cell(10,$h[0],'',1,0,'C');}
						$pdf->Ln();

                  }
           // Ultimas lineas....
              $pdf->SetX(10);
                $pdf->Cell(92,$h[0],'TOTAL DE PUNTOS',1,0,'R');  // TOTAL DE PUNTOS
                  $pdf->Cell(10,$h[0],array_sum($total_puntos_01_array),1,0,'C');
                  $pdf->Cell(10,$h[0],array_sum($total_puntos_02_array),1,0,'C');
                  $pdf->Cell(10,$h[0],array_sum($total_puntos_03_array),1,0,'C');
                  $pdf->Cell(10,$h[0],array_sum($total_puntos_04_array),1,0,'C');
                  $pdf->Cell(10,$h[0],array_sum($total_puntos_05_array),1,0,'C');
                  $pdf->Cell(10,$h[0],array_sum($total_puntos_06_array),1,0,'C');
									$pdf->Cell(10,$h[0],array_sum($total_puntos_07_array),1,0,'C');
                  
                  for($j=0;$j<=6;$j++){$pdf->Cell(10,$h[0],'',1,0,'C');}
                    $pdf->Ln();
										$pdf->SetX(10);
										$pdf->Cell(92,$h[0],'PROMEDIO',1,0,'R');  // PROMEDIO
										$pdf->SetTextColor(255,0,0);
										$pdf->SetFont('Arial','B',10);
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_01_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_02_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_03_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_04_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_05_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_06_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->Cell(10,$h[0],number_format(array_sum($total_puntos_07_array)/$conteo_alumnos,0),1,0,'C');
										$pdf->SetTextColor(0);
										$pdf->SetFont('');
                  
                  for($j=0;$j<=6;$j++){$pdf->Cell(10,$h[0],'',1,0,'C');}
                    $pdf->Ln();   
// Construir el nombre del archivo.
	$nombre_archivo = $print_bachillerato.' '.$print_grado.' '.$print_seccion.'-'.$print_ann_lectivo . '.pdf';
// Salida del pdf.
    $pdf->Output($nombre_archivo,'I');
}	// IF PRINCIPAL QUE VERIFICA SI HAY REGISTROS.