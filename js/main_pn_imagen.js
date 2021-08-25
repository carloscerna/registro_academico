$(function(){
           // funcionalidad del botón Actualizar
		$('#goCancelar').on('click',function(){
                $('#accion_buscar').val('BuscarLista');
                $("#goBuscar").prop("disabled",false);
                $("#goActualizar").prop("disabled",true);
                $("#lstannlectivo").prop("disabled",false);
                $("#lstmodalidad").prop("disabled",false);
                $("#lstgradoseccion").prop("disabled",false);
                $('#listaDatosPnOK').empty();
        });
      // funcionalidad del botón Actualizar
		$('#goActualizar').on('click',function(){
                $('#accion_buscar').val('ActualizarDatosPn');
                var lstgrado = $('#lstgradoseccion').val();
                var accion_ok = 'ActualizarDatosPn';
                               
                var $objCuerpoTabla=$("#tablaDatosPnImagen").children().prev().parent();
                var codigo_alumno_ = []; 
                var original_= []; var codigo_matricula_ = [];
                var fila = 0;
                // recorre el contenido de la tabla.
                $objCuerpoTabla.find("tbody tr").each(function(){
                     var codigo_alumno = $(this).find('td').eq(1).html();
							var nombre_estudio = "input[name=e"+codigo_alumno+"]:checked";                                                                
                     var original =$(this).find('td').eq(4).find(nombre_estudio).val();
                     var codigo_matricula =$(this).find('td').eq(5).find("input[name=codigo_matricula]").val();
                     $(this).css("background-color", "#ECF8E0");                                
                                //alert(codigo_alumno+' '+codigo_nie+' '+codigo_genero + ' ' + fecha_nacimiento + ' ' + edad + ' ' + numero + ' ' + folio + ' ' + tomo +' ' + libro+ ' ' + estudio_parvularia+ ' ' +nombre_estudio)                                
                // dar valor a las arrays.
                        codigo_alumno_[fila]=codigo_alumno;

                        original_[fila] = original;
                        codigo_matricula_[fila] = codigo_matricula;

                        fila = fila + 1;

                });
                        $.ajax({
    		            beforeSend: function(){       
		            },
                                cache: false,
                                type: "POST",
                                dataType: "json",
                                url:"php_libs/soporte/phpAjaxDatosPnImagen.php",
                                data: {
                                        accion: accion_ok, codigo_alumno: codigo_alumno_, fila: fila,
                                        original: original_, codigo_grado: lstgrado, codigo_matricula: codigo_matricula_,
                                        },
                                success: function(response) {
                                        if (response.respuesta === true) {
                                            //code
                                            $('#listaDatosPnOK').empty();
                                            $('#accion_buscar').val('BuscarDatosPn');
														  toastr.success("Registros Actualizados.");
                                                $("#goBuscar").prop("disabled",false);
                                                $("#goActualizar").prop("disabled",true);
                                                $("#lstannlectivo").prop("disabled",false);
                                                $("#lstmodalidad").prop("disabled",false);
                                                $("#lstgradoseccion").prop("disabled",false);
                                        }
                                }
                            });                
        });

        // BUSQUEDA DE REGISRO PARA ACTUALIZAR DATOS DE LA PARTIDA DE NACIMIENTO.
		$('#formDatosPn').validate({
			rules:{
                  lstannlectivo: {required: true},
                  lstmodalidad: {required: true},
						lstgradoseccion: {required: true},
                },
			messages: {
					lstannlectivo: "Seleccione un año lectivo.",
				},	
					errorElement: "em",
					errorPlacement: function ( error, element ) {
					// Add the `invalid-feedback` class to the error element
					error.addClass( "invalid-feedback" );
					if ( element.prop( "type" ) === "checkbox" ) {
						error.insertAfter( element.next( "label" ) );
					} else {
						error.insertAfter( element );
					}
				},
					highlight: function ( element, errorClass, validClass ) {
					$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
				},
					unhighlight: function (element, errorClass, validClass) {
					$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
				},			
		    submitHandler: function(){
		        var str = $('#formDatosPn').serialize();

		        $.ajax({
		            beforeSend: function(){
		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/phpAjaxDatosPnImagen.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){
							// Validar mensaje de error
		            	if(response.respuesta === false){
		            		toastr.error(":(");
                        $('#listaDatosPnOK').empty();
		            	}
		            	else{
                                if(response.mensaje == "Si Registro"){
                                // Mostrar resultado cuando se ha encontra registros.
												toastr.info("Registros encontrados...");
                                        $('#listaDatosPnOK').empty();
                                        $('#listaDatosPnOK').append(response.contenido);

                                        $("#goBuscar").prop("disabled",true);
                                        $("#goActualizar").prop("disabled",false);
                                        $("#goDatosPn").prop("disabled",true);
                                        $("#lstannlectivo").prop("disabled",true);
                                        $("#lstmodalidad").prop("disabled",true);
                                        $("#lstgradoseccion").prop("disabled",true);
                                        $("#lstasignatura").prop("disabled",true);

                                   }else{
                                      toastr.error(":(");
												}
							}
								},
		            error:function(){
                                toastr.error(":(");
		            },
					});
	},
});
});