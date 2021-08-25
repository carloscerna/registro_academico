<?php
header ('Content-type: text/html; charset=utf-8');
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
include($path_root."/registro_academico/includes/funciones.php");
    set_time_limit(0);
    ini_set("memory_limit","2000M");
// variables. del post.
    $nombre_archivo = "CE14753-ER.xlsx";
    //$nombre_archivo = "CE10391-ER.xlsx";
	$ruta = $path_root.'/registro_academico/formatos_hoja_de_calculo/' . $nombre_archivo;
// variable de la conexi�n dbf.
    $db_link = $dblink;
// Inicializando el array
$datos=array(); $fila_array = 0;
$datos_error = array();
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Read\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Time zone.
    //echo date('H:i:s') . " Set Time Zone"."<br />";
    //date_default_timezone_set('America/El_Salvador');
// set codings.
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Leemos un archivo Excel 2007
   $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $ruta;
	 $fila = 2;
    $objPHPExcel = $objReader->load($origen);
// VARIABLES.
    $mensaje = array("NUMERO DE NIE NO EXISTE.","*");
// N�mero de hoja.
   $numero_de_hoja = 0;
	$numero = 5;	
// 	Recorre el numero de hojas que contenga el libro
       $objPHPExcel->setActiveSheetIndex($numero_de_hoja);
		//	BUCLE QUE RECORRE TODA LA CUADRICULA DE LA HOJA DE CALCULO.
		while($objPHPExcel->getActiveSheet()->getCell("A".$fila)->getValue() != "")
		  {
			 // Extraer datos de la Hoja de Cálculo.
				$codigo_nie = $objPHPExcel->getActiveSheet()->getCell("B".$fila)->getValue();               // N.º NIE
                $nombre_alumno = $objPHPExcel->getActiveSheet()->getCell("C".$fila)->getValue();            // NOMBRE ALUMNO
                $nombre_responsable = $objPHPExcel->getActiveSheet()->getCell("D".$fila)->getValue();        // NOMBRE RESPONSABLE.
                $nombre_tipo_parentesco = $objPHPExcel->getActiveSheet()->getCell("E".$fila)->getValue();            // TIPO PARENTESCO
                // TIPO PARENTESCO
                /*
                    1	"01"	"Abuela      "
                    2	"02"	"Sobrino     "
                    3	"03"	"Madre       "
                    4	"04"	"Hija        "
                    5	"05"	"Hijo        "
                    6	"06"	"Madrastra   "
                    7	"07"	"Primo       "
                    8	"08"	"Padre       "
                    9	"09"	"Prima       "
                    10	"10"	"Sin dato    "
                    11	"11"	"Padrastro   "
                    12	"12"	"Tía         "
                    13	"13"	"Hermano     "
                    14	"14"	"Tío         "
                    15	"15"	"Abuelo      "
                    16	"16"	"Hermana     "
                    17	"17"	"Cónyuge     "
                    18	"18"	"Sobrina     "
                */
                $jj = 0;
                $tipo_a = array('01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18');
                $catalogo_tipo_parentesco = array("Abuala","Sobrino","Madre","Hija","Hijo","Madrastra","Primo","Padre","Prima","Sin dato","Padrastro","Tía","Hermano","Tío","Abuelo","Hermana","Cónyuge","Sobrina");
                foreach($catalogo_tipo_parentesco as $parentesco)
                {
                //	print $parentesco . '<br>';
                   if($parentesco == $nombre_tipo_parentesco)
                   {
                       $codigo_tipo_parentesco = $tipo_a[$jj];
                   }
                // contador del array que contiene el código.
                   $jj++;
                }
                // CODIGO GENERO
                $sexo = $objPHPExcel->getActiveSheet()->getCell("F".$fila)->getValue();                     // SEXO
                if($sexo == "Masculino"){$codigo_genero = "01";}else{$codigo_genero = "02";}
                $numero_dui = $objPHPExcel->getActiveSheet()->getCell("G".$fila)->getValue();        // DUI

                // ZONA RESIDENCIA.
                $zona_residencia = $objPHPExcel->getActiveSheet()->getCell("K".$fila)->getValue();        // ZONA RESIDENCIA
                if($zona_residencia == "Urbana"){$codigo_zona = "01";}else{$codigo_zona = "02";}

                $numero_dormitorio = $objPHPExcel->getActiveSheet()->getCell("L".$fila)->getValue();        // CUANTOS DORMITORIOS TIENE SU CASA
                $hogar_cuenta_con = $objPHPExcel->getActiveSheet()->getCell("M".$fila)->getValue();        // EN SU HOGAR, CUENTA CON LO SIGUIENTE

                $servicio_energia = $objPHPExcel->getActiveSheet()->getCell("N".$fila)->getValue();        // CUENTA CON SERVICIO DE ENERGIA ELECTRICA EN SU CASA
                if($servicio_energia == "No"){$bollean_servicio_energia = 'false';}else{$bollean_servicio_energia = 'true';}

                // ABASTECIMIENTO DE AGUA.
                $abastecimiento_agua = $objPHPExcel->getActiveSheet()->getCell("O".$fila)->getValue();        // CUAL ES LA FUENTE PRINCIPAL DE ABASTECIMIENTO DE AGUA DE SU CASA
                /*1	"01"	"Acarreo (río, lago, nacim"
                2	"02"	"Aguas lluvias            "
                3	"03"	"Otros                    "
                4	"04"	"Pila, chorro público o ca"
                5	"05"	"Pipa                     "
                6	"06"	"Pozo                     "
                7	"07"	"Servicio de agua por cañe"
                */
                $jj = 0;
                $tipo_a = array('01','02','03','04','05','06','07');
                $catalogo_abastecimiento_agua = array("Acarrero (río, lago, nacimiento de agua)","Agua Lluvias","Otros","Pila, chorro público o cantarea","Pipa","Pozo","Servicio de agua por cañería interna a la casa");
                foreach($catalogo_abastecimiento_agua as $agua)
                {
                //	print $parentesco . '<br>';
                   if($agua == $abastecimiento_agua)
                   {
                       $codigo_abastecimiento_agua = $tipo_a[$jj];
                   }
                // contador del array que contiene el código.
                   $jj++;
                }

                // MATERIAL PRINCIPAL DEL PISO DE SU CASA.
                $material_piso = $objPHPExcel->getActiveSheet()->getCell("P".$fila)->getValue();        // CUAL ES EL MATERIAL PRINCIPAL DEL PISO DE SU CASA
                /*
                    1	"01"	"Cemento                  "
                    2	"02"	"Ladrillos de barro       "
                    3	"03"	"Ladrillos de Cerámica    "
                    4	"04"	"Otros                    "
                    5	"05"	"Tierra                   "
                */
                $jj = 0;
                $tipo_a = array('01','02','03','04','05');
                $catalogo_piso = array("Cemento","Ladrillos de barro","Ladrillos de Cerámica","Otros","Tierra");
                foreach($catalogo_piso as $piso)
                {
                //	print $parentesco . '<br>';
                   if($piso == $material_piso)
                   {
                       $codigo_material_piso = $tipo_a[$jj];
                   }
                // contador del array que contiene el código.
                   $jj++;
                }

                // TIO DE SERVICIO SANITARIO.
                $servicio_sanitario = $objPHPExcel->getActiveSheet()->getCell("Q".$fila)->getValue();        // QUE TIPO DE SERVICIO SANITARIO TIENE SU CASA
                /*1	"01"	"Tasa conectada a sistema "
                2	"02"	"Tasa conectada a fosa sép"
                3	"03"	"Letrina de foso          "
                4	"04"	"Otros                    "
                */
                $jj = 0;
                $tipo_a = array('01','02','03','04');
                $catalogo_servicio = array("Tasa conectada a sistema de alcantarillado","Tasa conectada a fosa séptica","Letrina de foso","Otros");
                foreach($catalogo_servicio as $sanitario)
                {
                //	print $parentesco . '<br>';
                   if($sanitario == $servicio_sanitario)
                   {
                       $codigo_servicio_sanitario = $tipo_a[$jj];
                   }
                // contador del array que contiene el código.
                   $jj++;
                }
                $conexion_internet = $objPHPExcel->getActiveSheet()->getCell("R".$fila)->getValue();        // TIENE ALGUN TIPO DE CONEXION A INTERNET RESIDENCIAL
                if(trim($conexion_internet) == 'No'){$bollean_conexion_internet = 'f';}else{$bollean_conexion_internet = 't';}
                /*1	"01"	"Claro          "
                2	"02"	"Digicel        "
                3	"03"	"Flynet         "
                4	"04"	"Japi           "
                5	"05"	"Movistar       "
                6	"06"	"Otro           "
                7	"07"	"Tigo           "*/
                $company_listado = $objPHPExcel->getActiveSheet()->getCell("S".$fila)->getValue();        // DISTANCIA AL CENTRO EDUCATIVO
                $jj = 0;
                $tipo_a = array('01','02','03','04','05','06','07');
                $catalogo_company = array('Claro','Digicel','Flynet','Japi','Movistar','Otro','Tigo');
                foreach($catalogo_company as $company)
                {
                //	print $parentesco . '<br>';
                   if($company == $company_listado)
                   {
                       $codigo_company_internet = $tipo_a[$jj];
                   }
                // contador del array que contiene el código.
                   $jj++;
                }


                $distancia = $objPHPExcel->getActiveSheet()->getCell("T".$fila)->getValue();        // DISTANCIA AL CENTRO EDUCATIVO
                $sintoniza = $objPHPExcel->getActiveSheet()->getCell("U".$fila)->getValue();        // PUEDE SINTONIZAR EN SU  CASA CANAL 10
                if($sintoniza == "No"){$bollean_sintoniza = 'false';}else{$bollean_sintoniza = 'true';}

                $franja_educativa = $objPHPExcel->getActiveSheet()->getCell("V".$fila)->getValue();        // SINTONIZA LA FRANJA EDUCATIVA 
                if($franja_educativa == "No"){$bollean_franja_educativa = 'false';}else{$bollean_franja_educativa = 'true';}

                $cantidad_personas = $objPHPExcel->getActiveSheet()->getCell("W".$fila)->getValue();        // CANTIDAD DE PERSONAS QUE VIVEN CON EL ESTUDIANTE
                $viven_personas_menores = $objPHPExcel->getActiveSheet()->getCell("X".$fila)->getValue();        // VIVEN PERSONAS MENORES DE 18 AÑOS
                if($viven_personas_menores == "No"){$bollean_viven_personas_menores = 'false';}else{$bollean_viven_personas_menores = 'true';}

                // datos del encargado.
                $fecha_nacimiento = $objPHPExcel->getActiveSheet()->getCell("H".$fila)->getFormattedValue();        // FECHA DE NACIMIENTO
                //$excelTimestamp = 43106; //valor recogido de la celda del archivo excel
                //$fecha_nacimiento = ($fecha_nacimiento_);
                


                $direccion = $objPHPExcel->getActiveSheet()->getCell("I".$fila)->getValue();        // DIRECCION
                $numero_contacto = $objPHPExcel->getActiveSheet()->getCell("J".$fila)->getValue();        // NUMERO DE CONTACTO

                // VERIFICAR SI EXISTE EL ALUMNO, COMPARANDOLO CON EL NÚMERO DE NIE.
                    $query_nie = "SELECT * FROM alumno WHERE codigo_nie = '$codigo_nie'";
				// Ejecutamos el Query.
                    $consulta_nie = $dblink -> query($query_nie);
                // Verificar si existen registros.
                if($consulta_nie -> rowCount() != 0){
                    // convertimos el objeto
                    while($listados = $consulta_nie -> fetch(PDO::FETCH_BOTH))
                    {
                        $codigo_nie = trim($listados['codigo_nie']);
                        $codigo_alumno = trim($listados['id_alumno']);
                    }
                    // IMPRIMIR MENSAJES.
                    print "<hr>";
                    print "<br>"."<b> ACTUALIZACIÓN DE DATOS DEL RESPONSABLE </b>";
                    print "<br>"."N-º de NIE: " . $codigo_nie;
                    print "<br>"."Código alumno: " . $codigo_alumno;
                    print "<br>"."Nombre del estudiante: " . $nombre_alumno;
                    print "<br>"."";
                    print "<br>"."<b>DATOS DEL RESPONSABLE</b>";
                    print "<br>"."Nombre del responsable: "  . $nombre_responsable;
                    print "<br>"."Tipo paarentesco: " . $codigo_tipo_parentesco;
                    print "<br>"."N.º de DUI: " . $numero_dui;
                    print "<br>"."Sexo: " . $codigo_genero;
                    print "<br>"."Fecha de nacimiento: " . $fecha_nacimiento;
                    print "<br>"."Direción: " . $direccion;
                    print "<br>"."N.º de contacto: " . $numero_contacto;
                    print "<br>"."";
                    print "<br>"."";
                    print "<br>"."<b>HOGAR</b>";
                    print "<br>"."Zona residencia: " . $codigo_zona;
                    print "<br>"."¿Cuantos dormitorios tiene su casa?: " . $numero_dormitorio;
                    print "<br>"."Hogar, cuenta con: " . $hogar_cuenta_con;
                    print "<br>"."Cuenta con servicio de energía: " . $bollean_servicio_energia . ' - ' . $servicio_energia;
                    print "<br>"."Abastecimiento de agua: " . $codigo_abastecimiento_agua . ' - ' . $abastecimiento_agua;
                    print "<br>"."Tipo de piso: " . $codigo_material_piso . ' - ' . $material_piso;
                    print "<br>"."Tipo de servicio sanitario: " . $codigo_servicio_sanitario . ' - ' . $servicio_sanitario;
                    print "<br>"."Posee conexión a INTERNET: " . $bollean_conexion_internet . " - " .$conexion_internet;
                    print "<br>"."Compañía que proporciona el INTERNET: " . $codigo_company_internet;
                    print "<br>"."Km. de la casa al centro Escolar: " . $distancia;
                    print "<br>"."Sintozina el canal 10: " . $bollean_sintoniza;
                    print "<br>"."Sintozina la franja educativa: " . $bollean_franja_educativa;
                    print "<br>"."Cantidad de personas que viven: " . $cantidad_personas;
                    print "<br>"."Viven pesonas menores de 18 años: " . $bollean_viven_personas_menores;
                    print "<br>". $mensaje[1];
                    print "<br>";
                    print "<hr>";
                // VERIFICAR SI EXISTE EN ALUMNO_HOGAR
                    $query_hogar = "SELECT * FROM alumno_hogar WHERE codigo_nie = '$codigo_nie' and codigo_alumno = $codigo_alumno LIMIT 1";
                        // Ejecutamos el Query.
                        $consulta_hogar = $dblink -> query($query_hogar);
                        // Verificar si existen registros.
                        if($consulta_hogar -> rowCount() != 0){                    
                            // ACTUALIZAR EL REGISTRO.
                              print "<b>**REGISTRO ACTUALIZADO.**</b>";

                        }else{
                            // GUARDAR EL REGISTRO
                            print "<br>" . $query_hogar_guardar = "INSERT INTO alumno_hogar (codigo_alumno, codigo_nie, cantidad_dormitorios, catalogo_hogar, servicio_energia, catalogo_abastecimiento_agua, catalogo_material_piso, catalogo_tipo_servicio_sanitario, conexion_internet, distancia_centro_educativo, sintonizar_canal, sintonizar_franja_educativa, cantidad_viven_estudiante, viven_personas_menores, catalogo_zona_residencia, codigo_company)
                                                        VALUES ('$codigo_alumno','$codigo_nie','$numero_dormitorio','$hogar_cuenta_con','$bollean_servicio_energia','$codigo_abastecimiento_agua','$codigo_material_piso','$codigo_servicio_sanitario','$bollean_conexion_internet','$distancia','$bollean_sintoniza','$bollean_franja_educativa','$cantidad_personas','$bollean_viven_personas_menores','$codigo_zona','$codigo_company_internet')";
                            $consulta_hogar_guardar = $dblink -> query($query_hogar_guardar);
                            print "<b>/ / REGISTRO GUARDADO./ /</b>";
                            // ACTUALIZAR DATOS DEL ENCARGADO.
                            $query_update_encargado = "UPDATE alumno_encargado SET ";

                        }
                }else{
                    // IMPRIMIR MENSAJES.
                    //print "N.º de Fila:" .  $fila . " - " . $nombre_alumno . ' <b>'. $mensaje[0] . '</b>';
                    //print "<br>";
                    // 
                    $datos_error[] = $fila . " - " .$codigo_nie . " - " . $nombre_alumno;
                }
			// CONTARDOR PRINCIPAL PARA LA FILA DE LA HOJA DE CALCULO.
         	$fila++;

        }	// FIN DEL WHILE PRINCIPAL DE L AHOJA DE CALCULO.

//
// DESPUES DEL WHILE.
//
$hh= 0;
print "<br><b>ERROR DE DATOS <br></b>";
print "<hr>";
foreach ($datos_error as $value) {

    print $value;
    print "<br>";
}

/*
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
						///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
								$query_p = "SELECT id_productos, codigo, substring(codigo from 1 for 3)::int as codigo_cargo_numero_entero
											FROM catalogo_productos ORDER BY codigo_cargo_numero_entero DESC LIMIT 1";
							// Ejecutamos el Query.
									$consulta_p = $dblink -> query($query_p);
									// Verificar si existen registros.
									if($consulta_p -> rowCount() != 0){
										// convertimos el objeto
										while($listados = $consulta_p -> fetch(PDO::FETCH_BOTH))
										{
											$codigo_entero_p = trim($listados['codigo_cargo_numero_entero']) + 1;
											$codigo_string_p = (string) $codigo_entero_p;
											$codigo_nuevo_p = codigos_nuevos($codigo_string_p);
										}
										// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
										$query_cat = "INSERT INTO catalogo_productos (codigo, descripcion, existencia, codigo_categoria) VALUES ('$codigo_nuevo_p','$nombre','$cantidad','$codigo_nuevo')";
										$consulta_cat = $dblink -> query($query_cat);
									}
									else{
											$codigo_nuevo_p = "001";
										// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
										$query_cat = "INSERT INTO catalogo_productos (codigo, descripcion, existencia, codigo_categoria) VALUES ('$codigo_nuevo_p','$nombre','$cantidad','$codigo_nuevo')";
										$consulta_cat = $dblink -> query($query_cat);}
										
										
													// condici�n
			if((int) $codigo_categoria === $numero){
				$codigo_producto = $codigo_producto + 1;
				
			}else{
				$codigo_producto = 1;
				$numero = $numero + 1;
			}
			$objPHPExcel->getActiveSheet()->SetCellValue("B".$fila, $codigo_producto);
				// Armar query para guardar en la tabla CATALOGO_PRODUCTOS.
				/*$query = "UPDATE asignatura SET nombre = '$descripcion', codigo_cc = '$codigo_cc'  WHERE id_asignatura = $id";
                $consulta = $dblink -> query($query);
                */