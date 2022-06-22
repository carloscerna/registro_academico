$(function(){
// funcionalidad del botón Actualizar
$('#goCancelar').on('click',function(){
                $('#accion_buscar').val('BuscarLista');
                $("#goBuscar").prop("disabled",false);
                $("#goActualizar").prop("disabled",true);
                $("#lstannlectivo").prop("disabled",false);
                $("#lstmodalidad").prop("disabled",false);
                $("#lstgradoseccion").prop("disabled",false);
					 $('#listaPnOK').empty();
					 PasarFoco();
        });
// funcionalidad del botón Actualizar
$('#goActualizar').on('click',function(){
	$('#accion_buscar').val('ActualizarDatosPn');
	var lstgrado = $('#lstgradoseccion').val();
   var accion_ok = 'ActualizarDatosPn';
   // Información de la Página 1.                               
      var $objCuerpoTabla=$("#tablaDatosPn").children().prev().parent();          
		var codigo_alumno_ = []; var direccion_ = []; var telefono_alumno_ = []; var telefono_celular_ = []; 
      var telefono_encargado_ = []; 
                
      var fila = 0;          
   // recorre el contenido de la tabla.
      $objCuerpoTabla.find("tbody tr").each(function(){
			var codigo_alumno = $(this).find('td').eq(1).html();
			// input text
         var direccion =$(this).find('td').eq(3).find("textarea[name=direccion]").val();
         var telefono_encargado =$(this).find('td').eq(4).find("input[name=telefono_encargado]").val();
         var telefono_alumno =$(this).find('td').eq(5).find("input[name=telefono_alumno]").val();
         var telefono_celular =$(this).find('td').eq(6).find("input[name=telefono_celular]").val();
			// Color de filas.                                
         $(this).css("background-color", "#ECF8E0");                       
			// dar valor a las arrays.
             codigo_alumno_[fila]=codigo_alumno;           
				 
             direccion_[fila] = direccion;           
             telefono_encargado_[fila] = telefono_encargado;           
             telefono_alumno_[fila] = telefono_alumno;           
             telefono_celular_[fila] = telefono_celular;           

            fila = fila + 1;            
      });
	// ejecutar Ajax.. ACTUALIZA5 INDICADORES DE MATRICULA.
      $.ajax({
			beforeSend: function(){       
			},
           cache: false,                     
           type: "POST",                     
           dataType: "json",                     
           url:"php_libs/soporte/phpAjaxDatosMasivos.php",                     
           data: {                     
                  accion: accion_ok, codigo_alumno: codigo_alumno_, fila: fila, direccion: direccion_, 
                  telefono_alumno: telefono_alumno_, telefono_celular: telefono_celular_, telefono_encargado: telefono_encargado_,
                   },                     
           success: function(response) {                     
                   if (response.respuesta === true) {                     
                       // lIMPIAR LOS VALORES DE LAS TABLAS.                     
                       $('#listaPnOK').empty();                     
                       $('#accion_buscar').val('BuscarLista');                     
                       toastr.success("Registros Actualizados...");                     
                           $("#goBuscar").prop("disabled",false);                     
                           $("#goActualizar").prop("disabled",true);                     
                           $("#lstannlectivo").prop("disabled",false);                     
                           $("#lstmodalidad").prop("disabled",false);                     
                           $("#lstgradoseccion").prop("disabled",false);                     
                           PasarFoco();                     
                   }                     
           }                     
      });    
});

// BUSQUEDA DE REGISRO PARA ACTUALIZAR LAS NOTAS.
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
		            url:"php_libs/soporte/phpAjaxDatosMasivos.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){
		            	// Validar mensaje de error
		            	if(response.respuesta === false){
		            		toastr.error("No hay Registros");
                        $('#listaPnOK').empty();
		            	}
		            	else{
                           if(response.mensaje == "Si Registro"){
                              // Mostrar resultado cuando se ha encontra registros.
                                 toastr.info("Registros Encontrados");
                                 $('#listaPnOK').empty();
                                 $('#listaPnOK').append(response.contenido);

                                 $("#goActualizar").prop("disabled",false);
                                 $("#goBuscar").prop("disabled",true);
                                 $("#lstannlectivo").prop("disabled",true);
                                 $("#lstmodalidad").prop("disabled",true);
                                 $("#lstgradoseccion").prop("disabled",true);
                           }else{
                                 toastr.error("No Se Encontraron Registros.");
									}
								}
		            },
							error:function(){
							   toastr.error("Error de Ajax.");
							}
					});
               return false;
				},
		});
});
// Pasar foco cuando seleccionar un encargado.
function PasarFoco()
   {
       $('#lstannlectivo').focus();
   }