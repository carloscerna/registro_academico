$(function(){
      // funcionalidad del botón Actualizar
		$('#goCancelar').on('click',function(){
               $('#accion_buscar').val('BuscarLista');
               $("#goBuscar").prop("disabled",false);
					$("#goCancelar").prop("disabled",false);
               $("#lstannlectivo").prop("disabled",false);
               $("#lstmodalidad").prop("disabled",false);
               $("#lstgradoseccion").prop("disabled",false);
					$('#listaMatriculaMasivaOK').empty();
					// Mostrar Destino.
					$("#Destino").hide();					
        });
/////////////////////////////////////////////////////////////////////////////////////////////////////
// BUSQUEDA DE REGISRO PARA MATRICULAR PARA EL GRADO INMEDIATO SUPERIOR O REPITENTE.
///////////////////////////////////////////////////////////////////////////////////////////////////// 
		$('#form').validate({
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
		        var str = $('#form').serialize();
		        $.ajax({
		            beforeSend: function(){
							$('#tabstabla').show();
		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/phpAjaxMatriculaMasiva.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){
		            	// Validar mensaje de error
		            	if(response.respuesta === false){
		            		error_usuario();
                        $('#listaMatriculaMasivaOK').empty();
		            	}
		            	else{
                                if(response.mensaje == "Si Registro"){
                                // Mostrar resultado cuando se ha encontra registros.
                                      notificacion_nota();
                                        $('#listaMatriculaMasivaOK').empty();
                                        $('#listaMatriculaMasivaOK').append(response.contenido);
													 $("#Destino").show();
                                        $("#goNotasBuscar").prop("disabled",true);
                                        $("#lstannlectivo").prop("disabled",true);
                                        $("#lstmodalidad").prop("disabled",true);
                                        $("#lstgradoseccion").prop("disabled",true);
                                   }else{
                                      notificacion_nota_error();}
												  Pace.stop();
							}
		            },
							error:function(){
							   error_usuario();
							}
					});
				},
		});
/////////////////////////////////////////////////////////////////////////////////////////////////////
// GUARDAR LA MATRICULA.
/////////////////////////////////////////////////////////////////////////////////////////////////////
$('#goCrearMatricula').on('click',function()
{
	$('#accion_buscar').val('CrearMatricula');
	var lstannlectivoD = $('#lstannlectivoD').val();
	var lstmodalidadD = $('#lstmodalidadD').val();
	var lstgradoseccionD = $('#lstgradoseccionD').val();
	var accion_ok = 'CrearMatricula';
                               
   var $objCuerpoTabla=$("#tablaLista").children().prev().parent();
   var codigo_alumno_ = [];  var chkmatricula_ = []; 
   var fila = 0;
	///////////////////////////////////////////////////////////////////////////////////////////////////
   // Verificar si los select estan llenos.
	///////////////////////////////////////////////////////////////////////////////////////////////////
	var pasar = true; var errorlstannlectivo = false; var errorlstmodalidad = false; var errorlstgradoseccion = false;
	if(lstannlectivoD == ""){pasar = false; errorlstannlectivo = true;}
	if(lstmodalidadD == ""){pasar = false; errorlstmodalidad = true;}
	if(lstgradoseccionD == ""){pasar = false; errorlstgradoseccion = true;}
	
	
	if(pasar == true)
	{
					 ///////////////////////////////////////////////////////////////////////////////////////////////////
                // recorre el contenido de la tabla.
					 ///////////////////////////////////////////////////////////////////////////////////////////////////
               $objCuerpoTabla.find("tbody tr").each(function(){
							var codigo_alumno =$(this).find('td').eq(1).html();
                     //var codigo_alumno =$(this).find('td').eq(1).find("input[name='codigo_alumno']").html();
                     var chkMatricula =$(this).find('td').eq(4).find('input[type="checkbox"]').is(':checked');
                     // Color de cada fila.           
                     $(this).css("background-color", "#ECF8E0");
					 ///////////////////////////////////////////////////////////////////////////////////////////////////
                // VALORES DE LA MATRIZ QUE VIAJAN POR EL POST
					 ///////////////////////////////////////////////////////////////////////////////////////////////////
                        codigo_alumno_[fila]=codigo_alumno;
                        chkmatricula_[fila]=chkMatricula;
                        fila = fila + 1;
					});
					 ///////////////////////////////////////////////////////////////////////////////////////////////////
                // AJAX
					 ///////////////////////////////////////////////////////////////////////////////////////////////////								
						$.ajax({
							beforeSend: function(){
								Pace.start();
							},
                                cache: false,
                                type: "POST",
                                dataType: "json",
                                url:"php_libs/soporte/phpAjaxMatriculaMasiva.php",
                                data: {
                                        accion: accion_ok, codigo_alumno_: codigo_alumno_, fila: fila,chk_matricula_: chkmatricula_,
													 lstannlectivo: lstannlectivoD, lstmodalidad: lstmodalidadD, lstgradoseccion: lstgradoseccionD
                                        },
                                success: function(response) {
												// CUANDO NINGUN REGISTRO HA SIDO SELECCIONADO.
												// O MATRICULA EXISTENTE.
													if (response.respuesta == false) {
                                            toastr.error(response.contenido);
                                        }
												
                                        if (response.respuesta == true) {
                                            toastr.success(response.contenido);
                                        }
                                }// Fin del success.
                  }); // Fin del Ajax.
	}else{
		//crear mensajes de error
		if(errorlstannlectivo == true){toastr.error("Debe ingresar el Año Lectivo"); $("#lstannlectivoD").focus();}
		if(errorlstmodalidad == true){toastr.error("Debe ingresar la Modalidad"); $("#lstmodalidadD").focus();}
		if(errorlstgradoseccion == true){toastr.error("Debe ingresar Grado-Sección y Turno"); $("#lstgradoseccionD").focus();}
	}
});
/////////////////////////////////////////////////////////////////////////////////////////////////////
// FIN GUARDAR LA MATRICULA.
/////////////////////////////////////////////////////////////////////////////////////////////////////

});

// Pasar foco cuando seleccionar un encargado.
function PasarFoco()
   {
       $('#lstannlectivo').focus();
   }
/*
		--------------------------------------------------------------------------------
		| EJEMPLO Y SCRIPT ADAPTADO AL ESPAÑOL POR http://blog.reaccionestudio.com/    |
		--------------------------------------------------------------------------------
		|	VISÍTANOS !!!                                                              |
		--------------------------------------------------------------------------------
*/			
			function notificacion_nota(){
				toastr.info("Registros Encontrados."); 
				return false;
			}
			
			function ok_nota(){
				toastr.success("Notas Actualizadas."); 
				return false;
			}
			
			function error(){
				toastr.warning("Debe Seleccionar: Modalidad, Grado y Sección."); 
				return false; 
			}
			function notificacion_nota_error(){
				toastr.warning("Registros no encontrados."); 
				return false; 
			}