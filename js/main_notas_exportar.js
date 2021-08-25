$(function(){
// BUSQUEDA DE REGISRO PARA EXPORTAR LAS NOTAS.       
$('#formExportarNotas').validate({
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
		        var str = $('#formExportarNotas').serialize();
				  //variables checked
               var TodasLasAsignaturas = ""; 
					if($('#TodasLasAsignaturas').is(":checked")) {TodasLasAsignaturas = 'yes';}
					// Ajax.
		        $.ajax({
		            beforeSend: function(){
                    		 $('#tabstabla').show();
		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/exportar_siges_xlsx.php",
		            data:str + "&id=" + Math.random() + "&TodasLasAsignaturas=" + TodasLasAsignaturas,
		            success: function(response){
		            	// Validar mensaje de error
		            	if(response.respuesta == true){
		            		toastr.success("Archivo creado con Éxito...");
                        		$('#listaNotasExportarOK').empty();
								$('#listaNotasExportarOK').append("<tr><td>Archivo Creado.</tr></td>");
								$('#listaNotasExportarOK').append("<tr><td class=text-success>"+response.mensaje+"</tr></td>");
						}
					//	$('#myModalExportar').modal('hide');
					$('#loader').hide();
						},
						error:function(){
							toastr.error("Error en Ajax");      
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