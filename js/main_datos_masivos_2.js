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
		var codigo_alumno_ = [];
		var id_p_ = []; var nombres_p_=[]; var dui_p_=[]; var chkencargado_p_ = []; var telefono_p_ = []; var fecha_n_p_ = [];
		var id_m_ = []; var nombres_m_=[]; var dui_m_=[]; var chkencargado_m_ = []; var telefono_m_ = []; var fecha_n_m_ = [];
		var id_o_ = []; var nombres_o_=[]; var dui_o_=[]; var chkencargado_o_ = []; var telefono_o_ = []; var fecha_n_o_ = [];
                
      var fila = 0;          
   // recorre el contenido de la tabla.
      $objCuerpoTabla.find("tbody tr").each(function(){
			var codigo_alumno = $(this).find('td').eq(1).html();
			// input text DATOS DEL PADRE
				var id_p =$(this).find('td').eq(3).find("input[name=id_p]").val();
				var chkencargado_p =$(this).find('td').eq(3).find('input[type="radio"]').is(':checked');
    	     	var nombres_p =$(this).find('td').eq(3).find("input[name=nombres_p]").val();
				 var dui_p =$(this).find('td').eq(3).find("input[name=dui_p]").val();
				 var fecha_n_p =$(this).find('td').eq(3).find("input[name=fecha_nacimiento_p]").val();
				 var telefono_p =$(this).find('td').eq(3).find("input[name=telefono_p]").val();
			// input text DATOS DEL MADRE
				var id_m =$(this).find('td').eq(4).find("input[name=id_m]").val();
				var chkencargado_m =$(this).find('td').eq(4).find('input[type="radio"]').is(':checked');
        	 	var nombres_m =$(this).find('td').eq(4).find("input[name=nombres_m]").val();
				 var dui_m =$(this).find('td').eq(4).find("input[name=dui_m]").val();
				 var fecha_n_m =$(this).find('td').eq(4).find("input[name=fecha_nacimiento_m]").val();
				 var telefono_m =$(this).find('td').eq(4).find("input[name=telefono_m]").val();
			// input text DATOS DEL OTRO
				var id_o =$(this).find('td').eq(5).find("input[name=id_o]").val();
				var chkencargado_o =$(this).find('td').eq(5).find('input[type="radio"]').is(':checked');
        	 	var nombres_o =$(this).find('td').eq(5).find("input[name=nombres_o]").val();
				 var dui_o =$(this).find('td').eq(5).find("input[name=dui_o]").val();
				 var fecha_n_o =$(this).find('td').eq(5).find("input[name=fecha_nacimiento_o]").val();
				 var telefono_o =$(this).find('td').eq(5).find("input[name=telefono_o]").val();
			// Color de filas.                                
         $(this).css("background-color", "#ECF8E0");                       
			// dar valor a las arrays.
             codigo_alumno_[fila]=codigo_alumno;           
				 
             id_p_[fila] = id_p;
				 nombres_p_[fila] = nombres_p;
				 dui_p_[fila] = dui_p;
				 fecha_n_p_[fila] = fecha_n_p;
				 telefono_p_[fila] = telefono_p;
				 chkencargado_p_[fila] = chkencargado_p;

				 id_m_[fila] = id_m;
				 nombres_m_[fila] = nombres_m;
				 dui_m_[fila] = dui_m;
				 fecha_n_m_[fila] = fecha_n_m;
				 telefono_m_[fila] = telefono_m;
				 chkencargado_m_[fila] = chkencargado_m;

				 id_o_[fila] = id_o;
				 nombres_o_[fila] = nombres_o;
				 dui_o_[fila] = dui_o;
				 fecha_n_o_[fila] = fecha_n_o;
				 telefono_o_[fila] = telefono_o;
				 chkencargado_o_[fila] = chkencargado_o;
				 
             fila = fila + 1;            
      });
	// ejecutar Ajax.. ACTUALIZA5 INDICADORES DE MATRICULA.
      $.ajax({
			beforeSend: function(){       
			},
           cache: false,                     
           type: "POST",                     
           dataType: "json",                     
           url:"php_libs/soporte/phpAjaxDatosMasivos-2.php",                     
           data: {                     
                  accion: accion_ok, codigo_alumno: codigo_alumno_, fila: fila,
						id_p: id_p_, nombres_p: nombres_p_, dui_p: dui_p_, chkencargado_p: chkencargado_p_, telefono_p: telefono_p_, fecha_n_p: fecha_n_p_,
						id_m: id_m_, nombres_m: nombres_m_, dui_m: dui_m_, chkencargado_m: chkencargado_m_, telefono_o: telefono_o_, fecha_n_m: fecha_n_m_,
						id_o: id_o_, nombres_o: nombres_o_, dui_o: dui_o_, chkencargado_o: chkencargado_o_, telefono_m: telefono_m_, fecha_n_o: fecha_n_o_,
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
		            url:"php_libs/soporte/phpAjaxDatosMasivos-2.php",
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