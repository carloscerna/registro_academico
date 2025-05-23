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
					$('#listaNotasExportarOK').empty();	// Limpiar tabla Información Exportar.
					//variables checked
					var TodasLasAsignaturas = ""; 
					if($('#TodasLasAsignaturas').is(":checked")) {TodasLasAsignaturas = 'yes';}
					var Exportar = {};
					Exportar['NombreAnnLectivo'] = $("#lstannlectivo option:selected").text();	// Año Lectivo
					Exportar['NombreNivel'] = $("#lstmodalidad option:selected").text();	// Nivel
					Exportar['NombreGST'] = $("#lstgradoseccion option:selected").text(); // Grado Seccion Turno
					Exportar['NombreAsignatura'] = $("#lstasignatura option:selected").text();	// Asignatura
					Exportar['NombrePeriodo'] = $("#lstperiodo option:selected").text();	// Periodo
					console.log(Exportar);
					var DatosExportar = JSON.stringify(Exportar);
				$.ajax({
					beforeSend: function(){
						$('#tabstabla').show();
					},
					cache: false,
					type: "POST",
					dataType: "json",
					url:"php_libs/soporte/ExportarCalificacionesTodos.php",
					data: str + "&id=" + Math.random() + "&TodasLasAsignaturas=" + TodasLasAsignaturas + "&Exportar=" + DatosExportar,
						success: function(response){
								// Validar mensaje de error
								if(response.respuesta == true){
									toastr["info"](response.mensaje, "Sistema");
										$('#listaNotasExportarOK').empty();
										$('#listaNotasExportarOK').append(response.contenido);
								}
							},
			error:function(){
				$('#listaNotasExportarOK').empty();
				toastr["warning"]('Archivo no Creado', "Sistema");
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