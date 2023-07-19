1// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";

// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
//
    $("#AlertSE").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        //
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //
        $("#NavServicioEducativo ul.nav > li > a").on("click", function () {
            $TextoTab = $(this).text();

            if($TextoTab == "Asignaturas"){
                // Borrar información de la Tabla.
                    $('#listaContenidoSE').empty();
                // Select a 00...
                    $("#lstcodigose").val('00')

            }else{
                //alert("Nav-Tab " + $TextoTab);
            }
        });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (ASIGNATURA CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // funcion onchange.
        $('#lstcodigose').on('change', function() {
            $("#AlertSE").css("display", "none");
          });
    });
//
//
//
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES

    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS ASIGNATURAS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$("#checkBoxAllSE").on("change", function () {
		$("#listadoContenidoSE tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoSE tbody").on("change", "input[type='checkbox'].case", function () {
	  if ($("#listadoContenidoSE tbody input[type='checkbox'].case").length == $("#listadoContenidoSE tbody input[type='checkbox'].case:checked").length) {
		  $("#checkBoxAllSE").prop("checked", true);
	  } else {
		  $("#checkBoxAllSE").prop("checked", false);
	  }
	 });	
    

        //
        //  funcion click
        //
        $('#goBuscarSE').on('click',function(){
			// Asignamos valor a la variable acción
                $('#accion_asignatura').val('BuscarAsignatura');
                var codigo_se = $("#lstcodigose").val();
                accion = 'BuscarAsignatura';
                //
                //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                //
                if(codigo_se == "00"){
                    $("#AlertSE").css("display", "block");
                    return;
                }
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