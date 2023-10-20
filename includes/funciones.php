<?php
/////////////////////////////////////////////////////////////////////////////////////////
// Crear Array para carpetas principales en donde se guardaran los archivos.
/////////////////////////////////////////////////////////////////////////////////////////
function CrearDirectorios($ruta_url,$nombre_ann_lectivo,$codigo_modalidad,$codigo_destino,$numero_periodo){
	global $DestinoArchivo;
	$codigo_institucion = $_SESSION["codigo"];
	// Crear Carpeta en C:\TempSistemaRegistro/
	$TempSistema = "c:/TempSistemaRegistro"; $CarpetaArchivo = "Carpetas";
	if(!file_exists($TempSistema)){
		mkdir ($TempSistema);
		chmod($TempSistema,07777);
	}
 // Crearcarpeta en C:\TempSistemaRegistro/Carpetas
 if(!file_exists($TempSistema."/".$CarpetaArchivo)){
	mkdir ($TempSistema."/".$CarpetaArchivo);
	chmod($TempSistema."/".$CarpetaArchivo,07777);
}
	/*
		ORDEN DE LOS DIRECTORES SEGÚN MATRIZ.
		0 - Archivos
		1 - Nominas
		2 - Cuadro Calificaciones.
		3 - Exportar Calificaciones - Siges
		4 - Boleta de Calificaciones.
		5 - 
	*/
	$DestinoArchivo = "";
	$nombre_directorios = array("$TempSistema/$CarpetaArchivo/$codigo_institucion/",
						"$TempSistema/$CarpetaArchivo/$codigo_institucion/Nominas/",
						"$TempSistema/$CarpetaArchivo/$codigo_institucion/Cuadros_Calificaciones/",
						"$TempSistema/$CarpetaArchivo/$codigo_institucion/Exportar_Calificaciones_SIGES/",
						"$TempSistema/$CarpetaArchivo/$codigo_institucion/Boleta Calificaciones/");
	$nombre_modalidad = array("Educacion Inicial/","Parvularia/","Educacion_Basica/","Educacion_Basica_Tercer_Ciclo/","Educacion_Media_General/","Educacion_Media_Tecnico/");
	$nombre_modalidad_escribir = "";
	$nombre_ann_lectivo = $nombre_ann_lectivo . "/";
	$numero_periodo = $numero_periodo . "/";
// Definir Ruta URL en C:\TempSistemaRegistro/Carpetas
	$ruta_url = $TempSistema . "/" . $CarpetaArchivo . "/";
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Asignarle valor a Nombre Modalidad.
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//print $codigo_modalidad;
	switch ($codigo_modalidad) 
    {
    case "01":
        $nombre_modalidad_escribir = $nombre_modalidad[0];
        break;
    case "02":
        $nombre_modalidad_escribir = $nombre_modalidad[1];
        break;
    case "03":
        $nombre_modalidad_escribir = $nombre_modalidad[2];
        break;
    case "04":
        $nombre_modalidad_escribir = $nombre_modalidad[2];
        break;
    case "05":
        $nombre_modalidad_escribir = $nombre_modalidad[3];
        break;
    case "06":
        $nombre_modalidad_escribir = $nombre_modalidad[4];
        break;
    case "07":
        $nombre_modalidad_escribir = $nombre_modalidad[5];
        break;
    case "08":
        $nombre_modalidad_escribir = $nombre_modalidad[5];
        break;
    case "09":
        $nombre_modalidad_escribir = $nombre_modalidad[5];
        break;			
    default:
        //$nombre_modalidad_escribir = "/registro_academico/Archivos/";
    }
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// CREAR CARPETAS 
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////	
	if(!file_exists($nombre_directorios[0]))
		{
			// Crear el Directorio Principal Archvos...
			mkdir ( $nombre_directorios[0]);
			chmod( $nombre_directorios[0],07777);
				// Crear el Subdirectorio Año Lectivo y Educación Básica o Media.
				// Para Nóminas. Escolanadamente.
					mkdir ( $nombre_directorios[1]);
					chmod( $nombre_directorios[1],07777);
						mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir);
						chmod ( $nombre_directorios[1].$nombre_modalidad_escribir,07777);
							mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo);
							chmod ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				// Para Control de Actividades Notas.
					mkdir ( $nombre_directorios[2]);
					chmod( $nombre_directorios[2],07777);
						mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir);
						chmod ( $nombre_directorios[2].$nombre_modalidad_escribir,07777);
							mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo);
							chmod ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				// Para Exportar Notas SIRAI
					mkdir ( $nombre_directorios[3]);
					chmod( $nombre_directorios[3],07777);
						mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir);
						chmod ( $nombre_directorios[3].$nombre_modalidad_escribir,07777);
							mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo);
							chmod ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
		}
		// proceso para las nòminas.
		if($codigo_destino === 1){
			if(!file_exists( $nombre_directorios[1])){
					// Para Nóminas. Escolanadamente.
						mkdir ( $nombre_directorios[1]);
						chmod ( $nombre_directorios[1],07777);
							mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir);
							chmod ( $nombre_directorios[1].$nombre_modalidad_escribir,07777);
								mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[1].$nombre_modalidad_escribir)){
					// Para Nóminas. Escolanadamente.
							mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir);
							chmod ( $nombre_directorios[1].$nombre_modalidad_escribir,07777);
								mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo)){
					// Para Nóminas. Escolanadamente.
								mkdir ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
		}
		
		if($codigo_destino === 2){
			// proceso para el control de actividades..
			if(!file_exists( $nombre_directorios[2])){
					// Para Nóminas. Escolanadamente.
						mkdir ( $nombre_directorios[2]);
						chmod ( $nombre_directorios[2],07777);
							mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir);
							chmod ( $nombre_directorios[2].$nombre_modalidad_escribir,07777);
								mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[2].$nombre_modalidad_escribir)){
					// Para Nóminas. Escolanadamente.
					print ( $nombre_directorios[2].$nombre_modalidad_escribir);
							mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir);
							chmod ( $nombre_directorios[2].$nombre_modalidad_escribir,07777);
								mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo)){
					// Para Nóminas. Escolanadamente.
								mkdir ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
		}
	// En el caso que ex EXPORTAR NOTAS SIGES, se crean los directorios o carpetas respectivas.
		if($codigo_destino === 3){
			// proceso para el control de actividades..
			if(!file_exists( $nombre_directorios[3])){
					// Para Nóminas. Escolanadamente.
						mkdir ( $nombre_directorios[3]);
						chmod ( $nombre_directorios[3],07777);
							mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir);
							chmod ( $nombre_directorios[3].$nombre_modalidad_escribir,07777);
								mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[3].$nombre_modalidad_escribir)){
					// Para Nóminas. Escolanadamente.
							mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir);
							chmod ( $nombre_directorios[3].$nombre_modalidad_escribir,07777);
								mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo)){
					// Para Nóminas. Escolanadamente.
								mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo);
								chmod ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
				}
			if(!file_exists( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo.$numero_periodo)){
					// Para Nóminas. Escolanadamente. PERIODO
								mkdir ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo.$numero_periodo);
								chmod ( $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo.$numero_periodo,07777);
				}				
		}
// En el caso que es BOLETA DE CALIFIACIONES, se crean los directorios o carpetas respectivas.
if($codigo_destino === 4){
	// proceso para el control de actividades..
	if(!file_exists( $nombre_directorios[4])){
			// Para Nóminas. Escolanadamente.
				mkdir ( $nombre_directorios[4]);
				chmod ( $nombre_directorios[4],07777);
					mkdir ( $nombre_directorios[4].$nombre_modalidad_escribir);
					chmod ( $nombre_directorios[4].$nombre_modalidad_escribir,07777);
						mkdir ( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo);
						chmod ( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
		}
	if(!file_exists( $nombre_directorios[4].$nombre_modalidad_escribir)){
			// Para Nóminas. Escolanadamente.
					mkdir ( $nombre_directorios[4].$nombre_modalidad_escribir);
					chmod ( $nombre_directorios[4].$nombre_modalidad_escribir,07777);
						mkdir ( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo);
						chmod ( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
		}
	if(!file_exists( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo)){
			// Para Nóminas. Escolanadamente.
						mkdir ( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo);
						chmod ( $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo,07777);
		}
}	
	// Cóndicionar la ruta del archivos destino.
	switch($codigo_destino)
	{
		case 1: // Nóminas
			$DestinoArchivo =  $nombre_directorios[1].$nombre_modalidad_escribir.$nombre_ann_lectivo;
			break;
		case 2:	// Control de Actividades.
			$DestinoArchivo =  $nombre_directorios[2].$nombre_modalidad_escribir.$nombre_ann_lectivo;
			break;
		case 3: // Notas SIGES.
			$DestinoArchivo =  $nombre_directorios[3].$nombre_modalidad_escribir.$nombre_ann_lectivo.$numero_periodo;
			break;
		case 4: // BOLETA DE CALIFICACIONES.
			$DestinoArchivo =  $nombre_directorios[4].$nombre_modalidad_escribir.$nombre_ann_lectivo;
			break;
	}
	
	return $DestinoArchivo;
}

/////////////////////////////////////////////////////////////////////////////////////////
//				**	conversor
/////////////////////////////////////////////////////////////////////////////////////////
function segundosToCadenaD($min, $calculo_horas)
{
	// Base 5 u 8 horas.
		$min_x_dia = $calculo_horas * 60;
	// calculos
		$dias = floor($min/$min_x_dia);
		$horas = $min % $min_x_dia;
		$residuo_dias = $horas % $min_x_dia;
		$horas = floor($residuo_dias / 60);
		$residuo_minutos = $residuo_dias % 60;
		$minutos = $residuo_minutos;
			return $dias;
}

function segundosToCadenaH($min, $calculo_horas)
{
	// Base 5 u 8 horas.
		$min_x_dia = $calculo_horas * 60;
	// calculos
		$dias = floor($min/$min_x_dia);
		$horas = $min % $min_x_dia;
		$residuo_dias = $horas % $min_x_dia;
		$horas = floor($residuo_dias/60);
		$residuo_minutos = $residuo_dias%60;
		$minutos = $residuo_minutos;
			return $horas;
}

function segundosToCadenaM($min, $calculo_horas)
{
	// Base 5 u 8 horas.
	$min_x_dia = $calculo_horas * 60;
	// calculos
	$dias = floor($min/$min_x_dia);
	$horas = $min%$min_x_dia;
	$residuo_dias = $horas%$min_x_dia;
	$horas = floor($residuo_dias/60);
	$residuo_minutos = $residuo_dias%60;
	$minutos = $residuo_minutos;
		return $minutos;
}

function segundosToCadena($min, $calculo_horas, $formato)
{
	// Base 5 u 8 horas.
	$min_x_dia = $calculo_horas * 60;
	// calculos
		$cadena = '';
		$dias = floor($min/$min_x_dia);
		$horas = $min%$min_x_dia;
		$residuo_dias = $horas%$min_x_dia;
		$horas = floor($residuo_dias/60);
		$residuo_minutos = $residuo_dias%60;
		$minutos = $residuo_minutos;
		if($formato == 1){
			$cadena = $dias.'d'.$horas.'h'.$minutos.'m';
		}else{
			$cadena = $dias.' días '.$horas.' horas '.$minutos.' minutos';
		}
			return $cadena;
}

function segundosToCadenaHorasMinustos($min)
{
	$cadena = '';
	$dias = floor($min/300);
	$horas = $min%300;
	$residuo_dias = $horas%300;
	$horas = floor($residuo_dias/60);
	$residuo_minutos = $residuo_dias%60;
	$minutos = $residuo_minutos;
	$cadena = $horas.'h '.$minutos.'m';
		return $cadena;
}

function conversor_segundos($seg_ini) {
	// Convertir a segundos.
		$horas = floor($seg_ini/3600);
		$minutos = floor(($seg_ini-($horas*3600))/60);
		$segundos = $seg_ini-($horas*3600)-($minutos*60);
//echo $horas.?h:?.$minutos.?m:?.$segundos.?s';
}
/////////////////////////////////////////////////////////////////////////////////////////
//				**	Cambiar grado o bachillerato.
/////////////////////////////////////////////////////////////////////////////////////////
/* function genera_año_lectivo()
{
	$consulta=pg_query("SELECT nombre, codigo FROM ann_lectivo ORDER BY codigo");

	// Voy imprimiendo el primer select compuesto por los paises
	   echo "<select name='lstannlectivo' id='annlectivo' autofocus='autofocus'>";
	   echo "<option value='0'>Seleccionar...</option>";
		while($registro=pg_fetch_assoc($consulta)){
		   echo "<option value='".$registro['codigo']."'>".$registro['nombre']."</option>";}
	   echo "</select>";
}

function genera_bach()
{
	$consulta=pg_query("SELECT id_bachillerato_ciclo, nombre as nombre_bachillerato, codigo as codigo_bachillerato FROM bachillerato_ciclo ORDER BY codigo");

	// Voy imprimiendo el primer select compuesto por los paises
	   echo "<select name='lstbachillerato' id='bach' onChange='cargaContenido(this.id)'>";
	   echo "<option value='0'>Seleccionar...</option>";
		while($registro=pg_fetch_assoc($consulta)){
		   echo "<option value='".$registro['codigo_bachillerato']."'>".$registro['nombre_bachillerato']."</option>";}
	   echo "</select>";
} */
/////////////////////////////////////////////////////////////////////////////////////////
//				**	Calcular la Extraedad o Sobreedad.
/////////////////////////////////////////////////////////////////////////////////////////
	function calcular_sobreedad($edad,$grado)
	{
		$edad_ok = false;
		$sobreedad = "";
		
		if($edad >= 9 && $grado == "01" ){	// 7
		$edad_ok = true;}
		
		if($edad >= 10 && $grado == "02" ){ // 8
		$edad_ok = true;}
		
		if($edad >= 11 && $grado == "03" ){ // 9
		$edad_ok = true;}
		
		if($edad >= 12 && $grado == "04" ){	// 10
		$edad_ok = true;}
		
		if($edad >= 13 && $grado == "05" ){	// 11
		$edad_ok = true;}
		
		if($edad >= 14 && $grado == "06" ){		// 12
		$edad_ok = true;}
		
		if($edad >= 15 && $grado == "07" ){	// 13
		$edad_ok = true;}
		
		if($edad >= 16 && $grado == "08" ){	// 14
		$edad_ok = true;}
		
		if($edad >= 17 && $grado == "09" ){	// 15
		$edad_ok = true;}

    if($edad >= 18 && $grado == "10" ){	// 16
		$edad_ok = true;}		
		
		if($edad >= 19 && $grado == "11" ){	// 17
		$edad_ok = true;}		

		if($edad >= 20 && $grado == "12" ){	// 18
		$edad_ok = true;}
		
		if($edad_ok == true){$sobreedad = "t";}else{$sobreedad = "f";}
		
		return $sobreedad;
	}

	function calcular_sobreedad_($edad,$grado)
	{
		global $sobreedad;
			$edad_ok = false;
			$sobreedad = "";
		
		if($edad >= 8 && $grado == "01" ){	// 7
		$edad_ok = true;}
		
		if($edad >= 9 && $grado == "02" ){ // 8
		$edad_ok = true;}
		
		if($edad >= 10 && $grado == "03" ){ // 9
		$edad_ok = true;}
		
		if($edad >= 11 && $grado == "04" ){	// 10
		$edad_ok = true;}
		
		if($edad >= 12 && $grado == "05" ){	// 11
		$edad_ok = true;}
		
		if($edad >= 13 && $grado == "06" ){		// 12
		$edad_ok = true;}
		
		if($edad >= 14 && $grado == "07" ){	// 13
		$edad_ok = true;}
		
		if($edad >= 15 && $grado == "08" ){	// 14
		$edad_ok = true;}
		
		if($edad >= 16 && $grado == "09" ){	// 15
		$edad_ok = true;}

    if($edad >= 17 && $grado == "10" ){	// 16
		$edad_ok = true;}		
		
		if($edad >= 18 && $grado == "11" ){	// 17
		$edad_ok = true;}		

		if($edad >= 19 && $grado == "12" ){	// 18
		$edad_ok = true;}
		
		if($edad_ok == true){$sobreedad = "t";}else{$sobreedad = "f";}
		
		return $sobreedad;
	}
	function calcular_sobreedad_escala($edad,$grado)
	{
		global $sobreedad_escala;
			$sobreedad_escala = 1;
		
		if($edad >= 8 && $grado == "01" ){	// 7
				if($edad == 8){

				}else if($edad == 9){
					$sobreedad_escala = 2;
				}else if($edad == 10){
					$sobreedad_escala = 3;
				}else{
					$sobreedad_escala = 4;
				}
			}
		
		if($edad >= 9 && $grado == "02" ){ // 8
			if($edad == 9){

			}else if($edad == 10){
				$sobreedad_escala = 2;
			}else if($edad == 11){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 10 && $grado == "03" ){ // 9
			if($edad == 10){

			}else if($edad == 11){
				$sobreedad_escala = 2;
			}else if($edad == 12){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 11 && $grado == "04" ){	// 10
			if($edad == 11){

			}else if($edad == 12){
				$sobreedad_escala = 2;
			}else if($edad == 13){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 12 && $grado == "05" ){	// 11
			if($edad == 12){

			}else if($edad == 13){
				$sobreedad_escala = 2;
			}else if($edad == 14){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 13 && $grado == "06" ){		// 12
			if($edad == 13){

			}else if($edad == 14){
				$sobreedad_escala = 2;
			}else if($edad == 15){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 14 && $grado == "07" ){	// 13
			if($edad == 14){

			}else if($edad == 15){
				$sobreedad_escala = 2;
			}else if($edad == 16){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 15 && $grado == "08" ){	// 14
			if($edad == 15){

			}else if($edad == 16){
				$sobreedad_escala = 2;
			}else if($edad == 17){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		if($edad >= 16 && $grado == "09" ){	// 15
			if($edad == 16){

			}else if($edad == 17){
				$sobreedad_escala = 2;
			}else if($edad == 18){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}

    if($edad >= 17 && $grado == "10" ){	// 16
		if($edad == 17){

		}else if($edad == 18){
			$sobreedad_escala = 2;
		}else if($edad == 19){
			$sobreedad_escala = 3;
		}else{
			$sobreedad_escala = 4;
		}
	}		
		
		if($edad >= 18 && $grado == "11" ){	// 17
			if($edad == 18){

			}else if($edad == 19){
				$sobreedad_escala = 2;
			}else if($edad == 20){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}		

		if($edad >= 19 && $grado == "12" ){	// 18
			if($edad == 19){

			}else if($edad == 20){
				$sobreedad_escala = 2;
			}else if($edad == 21){
				$sobreedad_escala = 3;
			}else{
				$sobreedad_escala = 4;
			}
		}
		
		return $sobreedad_escala;
	}
/////////////////////////////////////////////////////////////////////////////////////////
//				**	verificar la nota mayor, la final o recuperación.
/////////////////////////////////////////////////////////////////////////////////////////
	function verificar_nota($nota_final, $recuperacion)
	{
		$nota = 0;
		
		if($nota_final < 5 && $recuperacion != 0){
			// calcular la nota entre dos.
			$nota = round(($nota_final+$recuperacion)/2,0);}
			
			// no calcular la nota entre dos.
			//$nota = number_format($recuperacion,0);}

		else{
			$nota = number_format($nota_final,0);}
		
		return $nota;
	}
// verificar para media
	function verificar_nota_media($nota_final, $recuperacion)
	{
		$nota = 0;
		
		if($nota_final < 6 && $recuperacion != 0){
			// calcular la nota entre dos.
		$nota = round(($nota_final+$recuperacion)/2,0);}
		else{
			$nota = number_format($nota_final,0);}
		
		return $nota;
	}
/////////////////////////////////////////////////////////////////////////////////////////
//				**	cambiar el Del por del ó De por de.
/////////////////////////////////////////////////////////////////////////////////////////
	function cambiar_de_del($de_por_de)
	{
		$ver = ""; $vere = ""; $nombre = ""; $nuevonombre = ""; $cambiar = 0;	$nuevo_de_del = "";
		$nombre_m = utf8_decode(trim($de_por_de));
		//$nombre = ucwords(mb_strtolower($nombre_m,'ISO-8859-1'));
		$nombre = mb_convert_case($nombre_m, MB_CASE_TITLE, "ISO-8859-1"); 
		//$str = mb_convert_case($str, MB_CASE_UPPER, "UTF-8"); echo $str; // Muestra MARY HAD A LITTLE LAMB AND SHE LOVED IT SO
				
		for($i=0;$i<=strlen($nombre);$i++)
		{
			$ver = substr($nombre,$i,1);
			$nuevonombre = $nuevonombre . $ver;
			
			if(substr($nombre,$i,1) == " ")
			{
				if(substr($nombre,$i,4) == " De ")
					{
						$nuevonombre = $nuevonombre . " de ";
						$i = $i + 3;
							$cambiar = 1;
					}

				if(substr($nombre,$i,4) == " La ")
					{
						$nuevonombre = $nuevonombre . " la ";
						$i = $i + 3;
							$cambiar = 1;
					}

				if(substr($nombre,$i,5) == " Del ")
					{
						$nuevonombre = $nuevonombre . " del ";
						$i = $i + 4;
							$cambiar = 1;
					}
				
				/*if(substr($nombre,$i,1) == " ")
					{
						$nuevonombre = trim($nuevonombre) . "_";
						//$i = $i + 0;
							$cambiar = 1;
					}	*/
			}	// fin del primer if...
		}	// fin del for.
		
		if($cambiar == 1)
			{
				$nuevo_de_del = $nuevonombre;
				$cambiar = 0;
			}
			else
			{
				$nuevo_de_del = $nombre;
			}
		return $nuevo_de_del;
	}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function cambiar_de_del_2($de_por_de)
	{
		$ver = ""; $vere = ""; $nombre = ""; $nuevonombre = ""; $cambiar = 0;	$nuevo_de_del = "";
		$nombre = mb_convert_case($de_por_de, MB_CASE_TITLE, "UTF-8");
		//		$nombre = ucwords(strtolower($de_por_de));

		//$nombre = mb_convert_case(trim($de_por_de), MB_CASE_TITLE, "ISO-8859-1");
				
		for($i=0;$i<=strlen($nombre);$i++)
		{
			$ver = substr($nombre,$i,1);
			$nuevonombre = $nuevonombre . $ver;
			
			if(substr($nombre,$i,1) == " ")
			{
				if(substr($nombre,$i,4) == " De ")
					{
						$nuevonombre = $nuevonombre . " de ";
						$i = $i + 3;
							$cambiar = 1;
					}

				if(substr($nombre,$i,4) == " La ")
					{
						$nuevonombre = $nuevonombre . " la ";
						$i = $i + 3;
							$cambiar = 1;
					}

				if(substr($nombre,$i,5) == " Del ")
					{
						$nuevonombre = $nuevonombre . " del ";
						$i = $i + 4;
							$cambiar = 1;
					}
				
				/*if(substr($nombre,$i,1) == " ")
					{
						$nuevonombre = trim($nuevonombre) . "_";
						//$i = $i + 0;
							$cambiar = 1;
					}	*/
			}	// fin del primer if...
		}	// fin del for.
		
		if($cambiar == 1)
			{
				$nuevo_de_del = $nuevonombre;
				$cambiar = 0;
			}
			else
			{
				$nuevo_de_del = $nombre;
			}
		return $nuevo_de_del;
	}

/////////////////////////////////////////////////////////////////////////////////////////
//				**	Bloque de Código NIE.
/////////////////////////////////////////////////////////////////////////////////////////
	function codigos_nie($nie)
	{
		$nuevo_codigo = ""; $codigo_mas = ""; $iv = 0;
		
					if(strlen(trim($nie)) == 4){
						$codigo_mas = "000000" . trim($nie);}
					
					if(strlen(trim($nie)) == 5){
						$codigo_mas = "00000" . trim($nie);}
						
					if(strlen(trim($nie)) == 6){
						$codigo_mas = "0000" . trim($nie);}
						
					if(strlen(trim($nie)) == 7){
						$codigo_mas = "000" . trim($nie);}
						
					if(strlen(trim($nie)) == 8){
						$codigo_mas = "00" . trim($nie);}
						
					if(strlen(trim($nie)) == 9){
						$codigo_mas = "0" . trim($nie);}
					
					$nuevo_codigo = $codigo_mas;					
					
					return $nuevo_codigo;
	}
////////////////////////////////////////////////////
//	bloque de año y sección.
////////////////////////////////////////////////////
function cambiar_grado($grado)
	{
	$nuevo_grado = "";
	//				
	switch ($grado)
		{
			case "Primero":
			$nuevo_grado = "primer"; break;
			case "Segundo":
					$nuevo_grado = "segundo"; break;
				case "Tercero":
					$nuevo_grado = "tercer"; break;
				case "Cuarto":
					$nuevo_grado = "cuarto"; break;
				case "Quinto":
					$nuevo_grado = "quinto"; break;
				case "Sexto":
					$nuevo_grado = "sexto"; break;
				case "Séptimo":
					$nuevo_grado = "séptimo"; break;
				case "Octavo":
					$nuevo_grado = "octavo"; break;
				case "Noveno":
					$nuevo_grado = "noveno"; break;
			}
				return $nuevo_grado;
	}
	////////////////////////////////////////////////////
//	Obtener el Grado superior a partir del actual.
////////////////////////////////////////////////////
function grado_superior($grado)
	{
		$nuevo_grado = "";			
	switch ($grado)
	{
		case "Kinder":
			$nuevo_grado = "preparatoria"; break;
		case "Preparatoria":
				$nuevo_grado = "primer grado"; break;
		case "Primero":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Segundo":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Tercero":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Cuarto":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Quinto":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Sexto":
			//$nuevo_grado = "séptimo"; break;
			$nuevo_grado = "grado inmediato superior"; break;
		case "Séptimo":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Octavo":
			$nuevo_grado = "grado inmediato superior"; break;
		case "Noveno":
			$nuevo_grado = ""; break;
		}
					return $nuevo_grado;
	}
////////////////////////////////////////////////////
//contar promovidos 
////////////////////////////////////////////////////

function contar_promovidos_aprobados($x1){
 global $contar_aprobados, $contar_reprobados;
    	if($x1 >= 5){$contar_aprobados++;}
    	if($x1 < 5){$contar_reprobados++;}
    return ;
}
////////////////////////////////////////////////////
//contar promovidos masculino BASICA
////////////////////////////////////////////////////

function contar_promovidos($x1, $x2, $nota_e){
 global $contar_p_m, $contar_r_m, $contar_p_f, $contar_r_f, $si_aprobado, $no_aprobado;;
 
    	if($x1 == 'm' && $x2 >= 5){$contar_p_m++;}
    	if($x1 == 'm' && $x2 < 5){$contar_r_m++;}
    
    	if($x1 == 'f' && $x2 >= 5){$contar_p_f++;}
    	if($x1 == 'f' && $x2 < 5){$contar_r_f++;}
	
		
	if($x2 >= $nota_e){$si_aprobado++;}
	if($x2 < $nota_e){$no_aprobado++;}
    return ;
}
////////////////////////////////////////////////////
//contar promovidos masculino MEDIA
////////////////////////////////////////////////////

function contar_promovidos_media($x1, $x2, $nota_e){
	global $contar_p_m, $contar_r_m, $contar_p_f, $contar_r_f, $si_aprobado, $no_aprobado;;
	//
		if($x1 == 'm' && $x2 >= 6){$contar_p_m++;}
		if($x1 == 'm' && $x2 < 6){$contar_r_m++;}
	//
		if($x1 == 'f' && $x2 >= 6){$contar_p_f++;}
		if($x1 == 'f' && $x2 < 6){$contar_r_f++;}
	//	
		if($x2 >= $nota_e){$si_aprobado++;}
		if($x2 < $nota_e){$no_aprobado++;}
			return ;
}
////////////////////////////////////////////////////
//Aprobados o Reprobados
////////////////////////////////////////////////////
function cambiar_aprobado_reprobado_m($ap_re){
    $ap_res = '';
		if($ap_re !=0){
			if($ap_re >= 6){$ap_res = "A";}else{$ap_res = "R";}}
		else{
			$ap_res = ' ';}
    return $ap_res;
}
////////////////////////////////////////////////////
//Aprobados o Reprobados
////////////////////////////////////////////////////
function cambiar_aprobado_reprobado_b($ap_re){
	$ap_res = '';
		if($ap_re !=0){
			if($ap_re >= 5){$ap_res = "A";}else{$ap_res = "R";}}
		else{
			$ap_res = ' ';}
    //
    return $ap_res;
}

////////////////////////////////////////////////////
//  conceptos bueno, muy bueno y excelente.
////////////////////////////////////////////////////
function cambiar_concepto($concepto){
    $conceptos = '';
		if($concepto == 0){$conceptos = "";}	
		if($concepto >= 1 && $concepto <5){$conceptos = "B";}
		if($concepto >= 5 && $concepto < 7){$conceptos = "B";}
		if($concepto >= 7 && $concepto < 9){$conceptos = "MB";}
		if($concepto >= 9 && $concepto <= 10){$conceptos = "E";}
    //
    return $conceptos;
}

function cambiar_concepto_letras($concepto){
    $conceptos = '';
		if($concepto < 5 ){$conceptos = "Regular";}
    if($concepto >= 5 && $concepto <= 6){$conceptos = "Bueno";}
    if($concepto >= 7 && $concepto <= 8){$conceptos = "Muy Bueno";}
    if($concepto >= 9 && $concepto <= 10){$conceptos = "Excelente";}
    //
    return $conceptos;
}

function cambiar_concepto_letras_prepa($concepto){
    $conceptos = '';
		if($concepto < 5 ){$conceptos = "";}
    if($concepto >= 5 && $concepto <= 6){$conceptos = "DB";}
    if($concepto >= 7 && $concepto <= 8){$conceptos = "DM";}
    if($concepto >= 9 && $concepto <= 10){$conceptos = "DA";}
    //
    return $conceptos;
}
////////////////////////////////////////////////////
//Convierte fecha de mysql a normal
////////////////////////////////////////////////////
/*
function cambiaf_a_normal($fecha)
{
    $cad = preg_split('/ /',$fecha);
    $sub_cad = preg_split('/-/',$cad[0]);
    $fecha_formateada = $sub_cad[2].'/'.$sub_cad[1].'/'.$sub_cad[0];
    return $fecha_formateada;
}

/* antigua funcion
function cambiaf_a_normal($fecha){
    ereg("([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})", $fecha, $mifecha);
    $lafecha=$mifecha[3]."/".$mifecha[2]."/".$mifecha[1];
    return $lafecha;
}*/

////////////////////////////////////////////////////
//Convierte fecha de normal a mysql
////////////////////////////////////////////////////
function cambiaf_a_mysql($fecha)
{
    $cad = preg_split('/ /',$fecha);
    $sub_cad = preg_split('/-/',$cad[0]);
    //$cad_hora = preg_split('/:/',$cad[1]);
    //$hora_formateada = $cad[0].':'.$cad_hora[1].':'.$cad_hora[2];
    $fecha_formateada = $sub_cad[0];
    //print $fecha_formateada = $sub_cad[2].$sub_cad[1].$sub_cad[0];
    return $fecha_formateada;
}

/* antigua forma.
function cambiaf_a_mysql($fecha){
    ereg("([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})", $fecha, $mifecha);
    $lafecha=$mifecha[3]."-".$mifecha[2]."-".$mifecha[1];
    return $lafecha;
} */ 

// Calcula la edad (formato: año/mes/dia)
/*function edad($edad){
date_default_timezone_set('America/Mexico_City');
    list($dia,$mes,$anio) = explode("/",$edad);
    $dia_dif = date("d") - $dia;
    $mes_dif = date("m") - $mes;
    $anio_dif = date("Y") - $anio;
        
    if ($dia_dif < 0 || $mes_dif < 0)
        $anio_dif--;
        return $anio_dif;
}
*/
// Pasar el formato de fecha a dd/mm/yyyy.
/*function fechaespanol($fecha)
{
    $data=split("-",$fecha);
    $retval=$data[2]."-".$data[1]."-".$data[0];
    return $retval;
}
/*
// fecha año/mes/dia.
function fechaYMD()
{
// Inciar variable global datos y mensajes de error.
    date_default_timezone_set('America/El_Salvador');
    $day=date("d");
    $month=date("m");
    $year=date("Y");
    $date=$day."/".$month."/".$year;
    $fecha=$year."-".$month."-".$day;
    
    return $fecha;
}*/

?>