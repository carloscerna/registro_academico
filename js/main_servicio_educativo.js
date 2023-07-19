// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";

// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS ASIGNATURAS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (ASIGNATURA CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $('#goBuscarSE').on('click',function(){
			// Asignamos valor a la variable acción
                $('#accion_asignatura').val('BuscarAsignatura');
                var codigo_se = $("#lstcodigose").val();
                accion = 'BuscarAsignatura';
                
                // Llamar al archivo php para hacer la consulta y presentar los datos.
                $.post("php_libs/soporte/phpAjaxServicioEducativo.php",  {accion: accion, codigo_se: codigo_se},
                    function(response) {
                    if (response.respuesta === true) {
                        toastr["info"]('Registros Encontrados', "Sistema");
                    }
                    if (response.respuesta === false) {
                        toastr["warning"]('Registros No Encontrados', "Sistema");
                    }                                                                                    // si es exitosa la operación
                        $('#listaContenidoSE').empty();
                        $('#listaContenidoSE').append(response.contenido);
                    },"json");
		});


}); // FIN DEL FUNCTION.

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