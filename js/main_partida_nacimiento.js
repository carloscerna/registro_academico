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
		var codigo_alumno_ = []; var codigo_nie_ = []; var codigo_genero_ = []; var fecha_nacimiento_ = []; var edad_ = [];
      var numero_ = []; var folio_ = []; var tomo_ = []; var libro_ = []; var estudio_parvularia_= []; var codigo_matricula_ = [];
                
      var fila = 0;          
   // recorre el contenido de la tabla.
      $objCuerpoTabla.find("tbody tr").each(function(){
			var codigo_alumno = $(this).find('td').eq(1).html();
         
         var codigo_nie =$(this).find('td').eq(3).find("input[name='codigo_nie']").val();
			var chkGenero =$(this).find('td').eq(4).find('input[type="checkbox"]').is(':checked');
			var fecha_nacimiento =$(this).find('td').eq(5).find("input[name=fecha_nacimiento]").val();
         var edad =$(this).find('td').eq(6).find("input[name=edad]").val();
			// input text
         var numero =$(this).find('td').eq(7).find("input[name=numero]").val();
         var folio =$(this).find('td').eq(8).find("input[name=folio]").val();
         var tomo =$(this).find('td').eq(9).find("input[name=tomo]").val();
         var libro =$(this).find('td').eq(10).find("input[name=libro]").val();
			
			var chkEstudioParvularia =$(this).find('td').eq(11).find('input[type="checkbox"]').is(':checked');							  
			var codigo_matricula =$(this).find('td').eq(12).find("input[name='codigo_matricula']").val();                       
			
			
         
			// Color de filas.                                
         $(this).css("background-color", "#ECF8E0");                       
			// dar valor a las arrays.
             codigo_alumno_[fila]=codigo_alumno;           
             codigo_nie_[fila]=codigo_nie;           
             codigo_genero_[fila]= chkGenero;
             fecha_nacimiento_[fila]= fecha_nacimiento;           
             edad_[fila]= edad;           
             numero_[fila] = numero;           
             folio_[fila] = folio;           
             tomo_[fila] = tomo;           
             libro_[fila] = libro;           
             estudio_parvularia_[fila] = chkEstudioParvularia;
             codigo_matricula_[fila] = codigo_matricula;                     

            fila = fila + 1;            
      });
	// ejecutar Ajax.. ACTUALIZA5 INDICADORES DE MATRICULA.
      $.ajax({
			beforeSend: function(){       
			},
           cache: false,                     
           type: "POST",                     
           dataType: "json",                     
           url:"php_libs/soporte/phpAjaxDatosPn.php",                     
           data: {                     
                  accion: accion_ok, codigo_alumno: codigo_alumno_, fila: fila, codigo_nie: codigo_nie_, codigo_genero: codigo_genero_,
                  fecha_nacimiento: fecha_nacimiento_, edad: edad_, numero: numero_, folio: folio_, tomo: tomo_, libro: libro_,
						estudio_parvularia: estudio_parvularia_, codigo_grado: lstgrado, codigo_matricula: codigo_matricula_,
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
		            url:"php_libs/soporte/phpAjaxDatosPn.php",
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
   // Mensaje de Carga de Ajax.
   function configureLoadingScreen(screen){
      $(document)
          .ajaxStart(function () {
              screen.fadeIn();
          })
          .ajaxStop(function () {
              screen.fadeOut();
          });
  }