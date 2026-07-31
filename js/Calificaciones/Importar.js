//Iniciamos nuestra función jquery.
$(function(){
// funcionalidad del botón que actualiza el directorio file.                
	$('#goActualizarDirectorio').on('click',function(){
		// Elminar mensaje de Actualizar Archivo.
		$('#MensajeImportar').empty();
		$.post("includes/cargar-nombre-archivos.php",
				function(data) {
					$('#listaArchivosOK').empty();                                  
					var filas = data.length;                                  
					if (filas != 0 ) {                                  
							for (fila=0;fila<filas;fila++) {                                  
									$('#listaArchivosOK').append(data[fila].archivo);                                  
							}                                                                                  
					}else{                                  
							$('#listaArchivosOK').append(data[fila].archivo);                                  
					}                                  
			}, "json");
		toastr.info("Directorio Actualizado.");
	});		
// ***************************************************************************************************                
// LLAMAR AL ARCHIVO IMPORTAR HOJA DE CALCULO PARA QUE ACTUALICE LAS NOTAS SEGÚN PERIODO O TRIMESTRE.                
// **************************************************************************************************                
$('body').on('click','#listaArchivosOK a',function (e)                
{                
   // estas dos lineas no cambian                       
	e.preventDefault();                       
	accion_ok = $(this).attr('data-accion');
	valor_check = $('input:radio[name=customHoja]:checked').val();
   // obtener el valor del nombre del archivo.
    var url_archivo = "xxx";
    var url_archivo_data = false;
	var nombre_archivo = $(this).parent().parent().children('td:eq(0)').text();                       
   // condicionar si existe selecciona periodo o trimestre.                      
	var modalidad = $('#lstmodalidad option:selected').val();
	var periodo = $('#lstperiodo option:selected').val();
	var grado = $('#lstparvulariagrado option:selected').val();
   // Al seleccionar dentro de la tabla.
    if($(this).attr('data-accion') == 'goActualizarOk'){
		// si no ha se seleccionada nada.
		if(valor_check == undefined){
			toastr.error(":( Debe Seleccionar Opciones.");
			return;
		}
		// mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
		$('#myModal').modal('show');
		// valores a la consola
			console.log("valor: " + valor_check + " Archivo: " + nombre_archivo + " Modalidad: " + modalidad + " Grado: " + grado + " Periodo: " + periodo);
			$("label[for='NombreArchivo']").text(nombre_archivo);
			$("label[for='VerificarActualizar']").text("Verificando...");
		/*/
			CAMBIAR LA URL DEL ARCHIVO
		*/
		// ASIGNATURAS PENDIENTES
		if(valor_check == "Actualizar"){
			url_archivo = "includes/importar_calificaciones_asignaturas_pendientes.php";
		}
		//
		if(valor_check == "Calculo"){
			url_archivo = "includes/importar_notas_hoja_calculo.php";
		}
		if(valor_check == "Promedios" && modalidad == "01"){
			url_archivo = "includes/Calificaciones/ImportarParvularia.php";
		}
		if(valor_check == "Promedios" && modalidad == "02"){
			url_archivo = "includes/importar_notas_hoja_calculo_basica_tercer_ciclo.php";
		}
		if(valor_check == "Promedios" && modalidad == "03"){
			url_archivo = "includes/importar_notas_hoja_calculo_educacion_media.php";
		}
		/*
		 *VERIFICAR ARCHIVOS ANTES DE INICAR LA ACTUALIZACIÓN.
		 */
		if(valor_check == "Calculo" || valor_check == "Promedios" || valor_check == "Actualizar"){
			$.ajax({
				cache: false,		
				type: "POST",		
				dataType: "json",		
				url: "includes/verificar_importar_notas_hoja_calculo.php",		
				data: "nombre_archivo_=" + nombre_archivo + "&periodo_=" + periodo + "&valor_check=" + valor_check+ "&grado=" + grado + "&modalidad=" + modalidad + "&id="+Math.random(),		
				success: function(data){		
				// validar		
					if (data[0].registro == "No_registro") {		
						toastr.error("Archivo Incorrecto...");
						url_archivo_data = false;
						return;
					}
					if (data[0].registro == "Si_registro") {		
						$("label[for='VerificarActualizar']").text("Actualizando...");
						$("#imagenGif").attr("src","img/ajax-loader.gif");
						url_archivo_data = true;
						console.log(url_archivo_data);
						// Comenzar el proceso del AJAX PARA EL NUEVO ARCHIVO.

						// Antes de lanzar el AJAX de actualización:
						var porcentaje = 0;
						$("label[for='VerificarActualizar']").text("Leyendo libro de cálculo...");
						$("#barraProgreso").css("width", "10%").text("10%").attr("aria-valuenow", 10);

						// Timer para simular el avance de lectura mientras PHP procesa
						var animacionProgreso = setInterval(function() {
							if (porcentaje < 85) {
								porcentaje += 15;
								$("#barraProgreso").css("width", porcentaje + "%").text(porcentaje + "%").attr("aria-valuenow", porcentaje);
								
								if (porcentaje >= 40 && porcentaje < 70) {
									$("label[for='VerificarActualizar']").text("Calculando promedios y procesando notas...");
								} else if (porcentaje >= 70) {
									$("label[for='VerificarActualizar']").text("Actualizando registros en la base de datos...");
								}
							}
						}, 400); // Avanza cada 400ms
						// Ejecutar AJAX de importación
						$.ajax({
							cache: false,		
							type: "POST",		
							dataType: "json",		
							url: url_archivo,		
							data: "nombre_archivo_=" + nombre_archivo + "&periodo_=" + periodo + "&valor_check=" + valor_check + "&grado=" + grado + "&modalidad=" + modalidad + "&id=" + Math.random(),		
							success: function(data){		
								// Detener la animación programada
								clearInterval(animacionProgreso);

								if (data && data[0] && data[0].registro == "Si_registro") {		
									// Llevar la barra inmediatamente al 100%
									$("#barraProgreso").css("width", "100%").text("100%").attr("aria-valuenow", 100);
									$("label[for='VerificarActualizar']").text("¡Importación completada!");

									toastr.success("Hoja de Cálculo Actualizada.");

									// Esperar medio segundo para que el usuario vea el 100% y luego cerrar el modal
									setTimeout(function(){
										$('#myModal').modal('hide');
										// Resetear barra para la próxima
										$("#barraProgreso").css("width", "0%").text("0%");
									}, 800);

									// Imprimir el resumen al fondo
									$('#MensajeImportar').empty();
									var totalActualizados = Object.keys(data).length - 1;

									var htmlResumen = "<div class='alert alert-success mt-2'>";
									htmlResumen += "<strong>Archivo Procesado:</strong> " + data[0].nombre_archivo + "<br>";
									htmlResumen += "<strong>Total de registros actualizados:</strong> " + totalActualizados + " alumnos.";
									htmlResumen += "</div>";

									$('#MensajeImportar').append(htmlResumen);
								} else {
									$('#myModal').modal('hide');
									toastr.error("No se pudo procesar el archivo.");
								}		
							},		
							error: function(){		
								clearInterval(animacionProgreso);
								$('#myModal').modal('hide');
								toastr.error("Ocurrió un fallo durante la actualización.");		
							}		
						});
					}
				},		
				error:function(){		
					toastr.error(":(");		
				}		
			}); // Cierre de Ajax.		
		}
	} // If Data-accion - Actualizar.
            // ***************************************************************************************************
			// Mandar datos para eliminar un registro.
            // ***************************************************************************************************
			       if($(this).attr('data-accion') == 'goEliminarOk'){
					// Elminar mensaje de Actualizar Archivo.
						$('#MensajeImportar').empty();
					// Llamar al archivo php para hacer la consulta y presentar los datos.
						$.post("includes/borrar_hoja_calculo.php",  { nombre_archivo_: nombre_archivo},
						function(data_borrar) {
							// validar
							if (data_borrar[0].registro == "Si_registro") {
								toastr.info("Hoja de Calculo Borrada."); 
                                // Volver a cargar la información de los archivos.
                                    $.post("includes/cargar-nombre-archivos.php",
                                        function(data) {
                                            $('#listaArchivosOK').empty();
                                            var filas = data.length;
                                            if (filas !== 0 ) {
                                                for (fila=0;fila<filas;fila++) {
													$('#listaArchivosOK').append(data[fila].archivo);
                                                }                                                
                                            }else{
                                                    $('#listaArchivosOK').append(data[fila].archivo);
                                                }
                                        }, "json");
							}
						}, "json");
			       }                                             	
});
});