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
    //
        // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS (ASIGANTURAS)
        //
		$('body').on('click','#listaContenidoSE a',function (e){
			e.preventDefault();
                        // Limpiar el listado de usuarios.
                        //$('#listaUsuariosOK').empty();
			// Id Usuario
    			Id_Editar_Eliminar = $(this).attr('href');
	    		accion_ok = $(this).attr('data-accion');
                        // EDITAR LA ASIGNATURA
                       if($(this).attr('data-accion') == 'editar_asignatura'){
                          // Ocultar botón actualizar y mostrar botón guardar.
                                $('#goAsignaturaActualizar').hide();
                                $('#goAsignaturaGuardar').show();
		        		    // Valor de la acción
				                $('#accion_asignatura').val('modificar_asignatura');
                                accion = 'editar_asignatura';
                                alertify.log("Registro Seleccionado para Editar.");
                                
                                // obtener el valor del id.
                                var id_ = $(this).parent().parent().children('td:eq(1)').text();
                                
                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                $.post("php_libs/soporte/phpAjaxMantenimiento_1.inc.php",  { id_x: id_, accion: accion},
                                  function(data) {
                                    alertify.success("Registros Encontrados."); 
                                    // Llenar el formulario con los datos del registro seleccionado tabs-1
                                    // Datos Generales
                                     $('#txtasignatura').val(data[0].nombre);
                                     $('#txtcodigoasignatura').val(data[0].codigo);
                                     $('#txtordenar').val(data[0].ordenar);
                                     $('#lstcodigose_m option[value='+data[0].codigo_se+']').attr('selected',true);
                                     $('#lstcodigocc option[value='+data[0].codigo_cc+']').attr('selected',true);
                                     $('#lstcodigoarea option[value='+data[0].codigo_area+']').attr('selected',true);
                                     $("#id_asignatura").val(Id_Editar_Eliminar);
                                     $('#sppartes').val(data[0].partes_dividida);

                                        if(data[0].estatus_asignatura == '1')
                                        {
                                                $('#lstEstatusA option[value=true]').attr('selected',true);
                                        }else{
                                                $('#lstEstatusA option[value=false]').attr('selected',true);
                                        }
                                     //$('#lstEstatusA').val(data[0].estatus_asignatura);
                                     // Desactivar casillas
                                     $('#listaAsignatura').empty();
                                     // Cambiar el valor de acción.
                                     accion = $("#accion_asignatura").val();
                                  },"json");
                                
                                // Abrimos el Formulario
                                    $('#editarAsignatura').dialog({
                                        title:'Editar Registro.',
                                        autoOpen:true
                                    });
                       }
                       // ELIMINAR REGISTRO ASIGNATURA.
                       if($(this).attr('data-accion') == 'eliminar_asignatura'){
                            //	ENVIAR MENSAJE CON SWEETALERT 2, PARA CONFIRMAR SI ELIMINA EL REGISTRO.
                            const swalWithBootstrapButtons = Swal.mixin({
                                customClass: {
                                confirmButton: 'btn btn-success',
                                cancelButton: 'btn btn-danger'
                                },
                                buttonsStyling: false
                            })
                            //
                            swalWithBootstrapButtons.fire({
                                title: '¿Qué desea hacer?',
                                text: 'Eliminar el Registro Seleccionado!',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, Eliminar!',
                                cancelButtonText: 'No, Cancelar!',
                                reverseButtons: true,
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                allowEnterKey: false,
                                stopKeydownPropagation: false,
                                closeButtonAriaLabel: 'Cerrar Alerta',
                                type: 'question'
                            }).then((result) => {
                                if (result.value) {
                                // PROCESO PARA ELIMINAR REGISTRO.
                                        // ejecutar Ajax.. 
                                        $.ajax({
                                        cache: false,                     
                                        type: "POST",                     
                                        dataType: "json",                     
                                        url:"php_libs/soporte/phpAjaxServicioEducativo.php",                     
                                        data: {                     
                                                accion_buscar: 'eliminar_asignatura', codigo_id_: Id_Editar_Eliminar,
                                                },                     
                                        success: function(response) {                     
                                                if (response.respuesta === true) {                     		
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
                                                }
                                                if (response.respuesta === false) {                     		
                                                    toastr["info"]('Registro no Eliminado... El còdigo está está activo en la Tabla Notas.', "Sistema");
                                                }
                                        }                     
                                        });
                                //////////////////////////////////////
                                } else if (
                                /* Read more about handling dismissals below */
                                result.dismiss === Swal.DismissReason.cancel
                                ) {
                                swalWithBootstrapButtons.fire(
                                    'Cancelar',
                                    'Su Registro no ha sido Eliminado :)',
                                    'error'
                                )
                                }
                            })
                       }
                });
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
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
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