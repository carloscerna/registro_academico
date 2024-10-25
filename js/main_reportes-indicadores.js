// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
   
$(function(){       
                // Validar Formulario para la buscque de registro segun el criterio.   
		$('#form').validate({
		    submitHandler: function(){

            // Serializar los datos, toma todos los Id del formulario con su respectivo valor.
		        var str = $('#form').serialize();
		        //alert(str);

		        $.ajax({
		            beforeSend: function(){
		                $('#ajaxLoader').show();
		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/phpAjaxReportesIndicadores.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){

		            	// Validar mensaje de error
		            	if(response.respuesta == false){
		            		alert(response.mensaje);
		            	}
		            	else{
		            	// si es exitosa la operaci�n
                                   var varbach = $('#lstannlectivo').val();
                                   varenviar = "/registro_academico/php_libs/reportes/Estadisticos/IndicadoresEducativos.php?lstannlectivo="+varbach;
                                // Ejecutar la funci�n
                                   AbrirVentana(varenviar);
                                }
		            	 $('#ajaxLoader').hide();
		            },
		            error:function(){
		                alert('ERROR GENERAL DEL SISTEMA, INTENTE MAS TARDE --- fase del ajax de mostrar registros.');
		            }
		        });
                                return false;
		    },
		    errorPlacement: function(error, element) {
		        error.appendTo(element.prev("span").append());
		    }
		});

		// Extracci�n del valor que va utilizar para Eliminar y Edici�n de Registros
		$('body').on('click','#listaUsuariosOK a',function (e){
			e.preventDefault();
                        // Limpiar el listado de usuarios.
                        //$('#listaUsuariosOK').empty();

			// valor de la variable proveniente del resultado del query.
			reporte_ok = $(this).attr('href');
			accion_ok = $(this).attr('data-accion');
                         
                        // Ajax hide. y controlar que informe se va a presentar.
                        $('#ajaxLoader').hide();
                        var varbach = $('#lstmodalidad').val();
                        // valores de los combo.
                        var lsttrimestre = $('#lsttrimestres').val();

                        // bloque para los diferentes informes.
                        ////////////////////////////////////////////////////
                        if (accion_ok == 'listados_01') {
                                // construir la variable con el url.
                                varenviar = "/registro_academico/php_libs/reportes/ap-re-asignatura.php?todos="+reporte_ok+"&lstrimestre="+lsttrimestre;
                                // Ejecutar la funci�n
                                AbrirVentana(varenviar);
                        }                     
                        // bloque para los diferentes informes de notas.
                        ////////////////////////////////////////////////////
                        if (accion_ok == 'listados_02') {
                                // construir la variable con el url.
                                varenviar = "/registro_academico/php_libs/reportes/promedio-asignatura.php?todos="+reporte_ok+"&lstrimestre="+lsttrimestre;
                                // Ejecutar la funci�n
                                AbrirVentana(varenviar);
                        }       
		});
});

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}