$(function(){
// funcionalidad del botón Actualizar
$('#goCancelar').on('click',function(){
                $('#accion_buscar').val('BuscarLista');
                $("#goBuscar").prop("disabled",false);
                $("#goActualizar").prop("disabled",true);
                $("#lstannlectivo").prop("disabled",false);
                $("#lstmodalidad").prop("disabled",false);
                $("#lstgradoseccion").prop("disabled",false);
					 $('#listaDatosMatriculaOK').empty();
					 PasarFoco();
        });
// funcionalidad del botón Actualizar
$('#goActualizar').on('click',function(){
	$('#accion_buscar').val('ActualizarDatosMatricula');
   var accion_ok = 'ActualizarDatosMatricula';
   // Información de la Página 1.                               
      var $objCuerpoTabla=$("#tablaDatosMatricula").children().prev().parent();          
      var codigo_alumno_ = []; var codigo_matricula_ = []; var codigo_seccion_turno_ = [];          
      var sobreedad_ = []; var repitente_ = []; var retirado_ = []; var nuevo_ingreso_ = [];          
      var pn_ = []; var certificado_ = []; var imprimir_foto_ = [];          
                
      var fila = 0;          
   // recorre el contenido de la tabla.
      $objCuerpoTabla.find("tbody tr").each(function(){
         var codigo_alumno =$(this).find('td').eq(1).find("input[name='codigo_alumno']").val();                       
         var codigo_matricula =$(this).find('td').eq(2).find("input[name='codigo_matricula']").val();                       
         var codigo_seccion_turno =$(this).find('td').eq(5).find("select[name='seccion_turno']").val();
			// Indicadores.
			var chkSobreedad =$(this).find('td').eq(6).find('input[type="checkbox"]').is(':checked');
         var chkRepitente =$(this).find('td').eq(7).find('input[type="checkbox"]').is(':checked');
			var chkRetirado =$(this).find('td').eq(8).find('input[type="checkbox"]').is(':checked');
			var chkNuevoIngreso =$(this).find('td').eq(9).find('input[type="checkbox"]').is(':checked');
			var chkPn =$(this).find('td').eq(10).find('input[type="checkbox"]').is(':checked');
			var chkCertificado =$(this).find('td').eq(11).find('input[type="checkbox"]').is(':checked');
			var chkImprimirFoto =$(this).find('td').eq(12).find('input[type="checkbox"]').is(':checked');
			// Color de filas.                                
         $(this).css("background-color", "#ECF8E0");                       
			// dar valor a las arrays.
            codigo_alumno_[fila]=codigo_alumno;            
            codigo_matricula_[fila]=codigo_matricula;            
            codigo_seccion_turno_[fila]= codigo_seccion_turno;            
            sobreedad_[fila] = chkSobreedad;            
            repitente_[fila] = chkRepitente;            
            retirado_[fila] = chkRetirado;            
            nuevo_ingreso_[fila] = chkNuevoIngreso;            
            pn_[fila] = chkPn;            
            certificado_[fila] = chkCertificado;            
            imprimir_foto_[fila] = chkImprimirFoto;            

            fila = fila + 1;            
      });
	// ejecutar Ajax.. ACTUALIZA5 INDICADORES DE MATRICULA.
      $.ajax({
			beforeSend: function(){       
			},
           cache: false,                     
           type: "POST",                     
           dataType: "json",                     
           url:"php_libs/soporte/phpAjaxIndicadoresMatricula.php",                     
           data: {                     
                   fila: fila,                     
                   accion: accion_ok, codigo_alumno: codigo_alumno_, codigo_matricula: codigo_matricula_,                       
                   codigo_seccion_turno: codigo_seccion_turno_,                     
                   sobreedad: sobreedad_, repitente: repitente_, retirado: retirado_, nuevo_ingreso: nuevo_ingreso_,                     
                   pn: pn_, certificado: certificado_, imprimir_foto: imprimir_foto_
                   },                     
           success: function(response) {                     
                   if (response.respuesta === true) {                     
                       // lIMPIAR LOS VALORES DE LAS TABLAS.                     
                       $('#listaDatosMatriculaOK').empty();                     
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
$('#formDatosMatricula').validate({
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
		        var str = $('#formDatosMatricula').serialize();
		        $.ajax({
		            beforeSend: function(){
		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/phpAjaxIndicadoresMatricula.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){
		            	// Validar mensaje de error
		            	if(response.respuesta === false){
		            		toastr.error("No hay Registros");
                        $('#listaDatosMatriculaOK').empty();
		            	}
		            	else{
                           if(response.mensaje == "Si Registro"){
                              // Mostrar resultado cuando se ha encontra registros.
                                 toastr.info("Registros Encontrados");
                                 $('#listaDatosMatriculaOK').empty();
                                 $('#listaDatosMatriculaOK').append(response.contenido);

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