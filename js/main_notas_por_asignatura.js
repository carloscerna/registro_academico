$(function(){
           // funcionalidad del botón Actualizar
		$('#goNotasCancelar').on('click',function(){
                $('#accion_buscar').val('BuscarNotas');
                $("#goNotasBuscar").prop("disabled",false);
                $("#goNotasActualizar").prop("disabled",true);
                $("#lstannlectivo").prop("disabled",false);
                $("#lstmodalidad").prop("disabled",false);
                $("#lstgradoseccion").prop("disabled",false);
                $("#lstasignatura").prop("disabled",false);
                $("#lstperiodo").prop("disabled",false);
                $('#listaNotasPorAsignaturaOK').empty();
        });

        // Funcionalidad para Imprimir por Asignatura.
        $('#goNotasImprimir').on('click',function(){
                var codigo_modalidad = $('#lstmodalidad').val();
                var codigo_gst = $('#lstgradoseccion').val();
                codigo_grado_seccion = codigo_gst.substring(0,4);
                var todos = $('#lstmodalidad').val() + codigo_grado_seccion + $('#lstannlectivo').val();
                var codigo_asignatura = $('#lstasignatura').val();

                if(codigo_modalidad >= '03' && codigo_modalidad <= '05'){
                        varenviar = "/registro_academico/php_libs/reportes/notas_trimestre_por_asignatura_basica.php?todos="+todos+"&lstasignatura="+codigo_asignatura;
                }

                if(codigo_modalidad >= '06' && codigo_modalidad <= '09'){
                        varenviar = "/registro_academico/php_libs/reportes/notas_trimestre_por_asignatura_media.php?todos="+todos+"&lstasignatura="+codigo_asignatura;
                }
                // Ejecutar la función
                        AbrirVentana(varenviar);                        
        });
        
      // funcionalidad del botón Actualizar
	$('#goNotasActualizar').on('click',function(){
                $('#accion_buscar').val('ActualizarNotas');
                var codigo_modalidad = $('#lstmodalidad').val();
                var accion_ok = 'ActualizarNotas';
                var periodo = $('#lstperiodo').val();
                               
                var $objCuerpoTabla=$("#tablaNotas").children().prev().parent();
                var codigo_alumno_ = []; var codigo_matricula_ = []; var codigo_asignatura_ = []; var nota_ = [];               
                var fila = 0;
                // recorre el contenido de la tabla.
                $objCuerpoTabla.find("tbody tr").each(function(){
                                var codigo_alumno =$(this).find('td').eq(1).find("input[name='codigo_alumno']").val();
                                var codigo_matricula =$(this).find('td').eq(2).find("input[name='codigo_matricula']").val();
                                var codigo_asignatura =$(this).find('td').eq(3).find("input[name='codigo_asignatura']").val();
                                //var codigo_alumno = $(this).find('td').eq(1).html();
                                //var codigo_matricula = $(this).find('td').eq(2).html();
                                //var codigo_asignatura =$(this).find('td').eq(3).html();
                                var nota =$(this).find('td').eq(6).find("input[name='nota']").val();
                                
                                $(this).css("background-color", "#ECF8E0");
                // dar valor a las arrays.
                        codigo_alumno_[fila]=codigo_alumno;
                        codigo_matricula_[fila]=codigo_matricula;
                        codigo_asignatura_[fila]=codigo_asignatura;
                        nota_[fila]=nota;
                        fila = fila + 1;

                });
                  $.ajax({
							beforeSend: function(){
													Pace.start();
		            },
                                cache: false,
                                type: "POST",
                                dataType: "json",
                                url:"php_libs/soporte/phpAjaxNotasPorAsignatura.inc.php",
                                data: {
                                        accion: accion_ok, codigo_alumno_: codigo_alumno_, codigo_matricula_: codigo_matricula_, codigo_asignatura_: codigo_asignatura_, nota_: nota_, fila: fila, periodo: periodo, codigo_modalidad: codigo_modalidad,
                                        },
                                success: function(response) {
                                        if (response.respuesta === true) {
                                            //code
                                            $('#listaNotasPorAsignaturaOK').empty();
                                            $('#accion_buscar').val('BuscarNotas');
                                            ok_nota();
                                                $("#goNotasBuscar").prop("disabled",false);
                                                $("#goNotasActualizar").prop("disabled",true);
                                                $("#lstannlectivo").prop("disabled",false);
                                                $("#lstmodalidad").prop("disabled",false);
                                                $("#lstgradoseccion").prop("disabled",false);
                                                $("#lstasignatura").prop("disabled",false);
                                                $("#lstperiodo").prop("disabled",false);
                                        }
                                }
                            });                
        });

        // BUSQUEDA DE REGISRO PARA ACTUALIZAR LAS NOTAS.
        
		$('#formNotasPorAsignatura').validate({
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
		        var str = $('#formNotasPorAsignatura').serialize();
		        $.ajax({
		            beforeSend: function(){
                                $('#tabstabla').show();

		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/phpAjaxNotasPorAsignatura.inc.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){
		            	// Validar mensaje de error
		            	if(response.respuesta === false){
		            		error_usuario();
                                        $('#listaNotasPorAsignaturaOK').empty();
		            	}
		            	else{
                                if(response.mensaje == "Si Registro"){
                                // Mostrar resultado cuando se ha encontra registros.
                                      notificacion_nota();
                                        $('#listaNotasPorAsignaturaOK').empty();
                                        $('#listaNotasPorAsignaturaOK').append(response.contenido);

                                        $("#goNotasActualizar").prop("disabled",false);
                                        $("#goNotasBuscar").prop("disabled",true);
                                        $("#lstannlectivo").prop("disabled",true);
                                        $("#lstmodalidad").prop("disabled",true);
                                        $("#lstgradoseccion").prop("disabled",true);
                                        $("#lstasignatura").prop("disabled",true);
                                        $("#lstperiodo").prop("disabled",true);

                                   }else{
                                      notificacion_nota_error();}
												  Pace.stop();
				}
		            },
								error:function(){
                                error_usuario();
		            }
        });
               return false;
				},
		});
});

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}


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