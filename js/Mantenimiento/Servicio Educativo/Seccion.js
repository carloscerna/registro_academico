// id de user global
var idUser_ok = 0;
var accion_seccion_ok = 'noAccion';
var accion_seccion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_se = "";
var texto_se = "";
var msjEtiqueta = "";
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
//
    $("#AlertSESeccion").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
           //
    // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
    //
    $("#NavServicioEducativo ul.nav > li > a").on("click", function () {
        $TextoTab = $(this).text();
  
        
        if($TextoTab == "Sección"){
            // Borrar información de la Tabla.
                $('#listaContenidoSESeccion').empty();
        }else{
            //alert("Nav-Tab " + $TextoTab);
        }
      });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (Seccion CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
	    $("#VentanaSeccion").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaSeccion")[0].reset();
                $('#formVentanaSeccion').trigger("reset");
				$("label.error").remove();
                accion_seccion = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS SeccionS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
        // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS (ASIGANTURAS)
        //
		$('body').on('click','#listaContenidoSESeccion a',function (e){
			e.preventDefault();
			// Id Usuario
    			Id_Editar_Eliminar = $(this).attr('href');
	    		accion_ok = $(this).attr('data-accion');
                    // EDITAR LA Seccion
                    if($(this).attr('data-accion') == 'EditarSeccion'){
                        // Valor de la acción
                            $('#accion_seccion').val('ActualizarSeccion');
                            accion_seccion = 'EditarSeccion';
                            
                            // obtener el valor del id.
                            var id_ = $(this).parent().parent().children('td:eq(2)').text();
                            
                            // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  { id_: id_, accion: accion_seccion},
                                function(data) {
                                // Llenar el formulario con los datos del registro seleccionado tabs-1
                                // Datos Generales
                                    $('#IdSeccion').val(data[0].id_seccion);
                                    $('#CodigoSeccion').val(data[0].codigo);
                                    $('#DescripcionSeccion').val(data[0].nombre);
                                    // Abrir ventana modal.
                                    $('#VentanaSeccion').modal("show");
                                    $("label[for=LblTituloSeccion]").text("Seccion | Actualizar");
                                    // reestablecer el accion_seccion a=ActulizarSeccion.
                                    accion_seccion = "ActualizarSeccion";
                                },"json");
                    }
                    // ELIMINAR REGISTRO Seccion.
                    if($(this).attr('data-accion') == 'eliminar_Seccion'){
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
                                    url:"php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",                     
                                    data: {                     
                                            accion_buscar: 'eliminar_Seccion', codigo_id_: Id_Editar_Eliminar,
                                            },                     
                                    success: function(response) {                     
                                            if (response.respuesta === true) {                     		
                                                // Asignamos valor a la variable acción
                                                    $('#accion_seccion').val('BuscarSeccion');
                                                    var codigo_se = $("#lstcodigose").val();
                                                    accion_seccion = 'BuscarSeccion';
                                                    //
                                                    //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                                                    //
                                                    if(codigo_se == "00"){
                                                        $("#AlertSE").css("display", "block");
                                                        return;
                                                    }
                                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_seccion, codigo_se: codigo_se},
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
	$("#checkBoxAllSESeccion").on("change", function () {
		$("#listadoContenidoSE tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoSESeccion tbody").on("change", "input[type='checkbox'].case", function () {
	  if ($("#listadoContenidoSESeccion tbody input[type='checkbox'].case").length == $("#listadoContenidoSESeccion tbody input[type='checkbox'].case:checked").length) {
		  $("#checkBoxAllSESeccion").prop("checked", true);
	  } else {
		  $("#checkBoxAllSESeccion").prop("checked", false);
	  }
	 });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
        //
        //  funcion click
        //
            $('#goBuscarSESeccion').on('click',function(){
                // Asignamos valor a la variable acción
                    $('#accion_seccion').val('BuscarSeccion');
                    codigo_se = $("#lstcodigose").val();
                    accion_seccion = 'BuscarSeccion';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_seccion},
                        function(response) {
                        if (response.respuesta === true) {
                            toastr["info"]('Registros Encontrados', "Sistema");
                        }
                        if (response.respuesta === false) {
                            toastr["warning"]('Registros No Encontrados', "Sistema");
                        }                                                                                    // si es exitosa la operación
                            $('#listaContenidoSESeccion').empty();
                            $('#listaContenidoSESeccion').append(response.contenido);
                        },"json");
            });
            //////////////////////////////////////////////////////////////////////////////////
            /* VER #CONTROLES CREADOS */
            //////////////////////////////////////////////////////////////////////////////////
            $('#goNuevoSESeccion').on('click', function(){
                // Abrir ventana modal.
                    $('#VentanaSeccion').modal("show");
                    $("label[for=LblTituloSeccion]").text("Seccion | Nuevo");
                // BUSCAR Y GENERAR NUEVO CODIGO PARA LA ASIGNATURA.
                //
                // BUSCAR EL ÚLTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
                    accion_seccion = 'BuscarCodigoSeccion';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_seccion},
                                function(data) {
                                    // si es exitosa la operación
                                        $('#CodigoSeccion').val(data[0].codigo);
                                },"json");
                    // RETORNAR EL VALOR DEL ACCION SEGUN ETIQUETA LABEL.
                        msjEtiqueta = $("label[for=LblTituloSeccion]").text();
                            if(msjEtiqueta == "Seccion | Actualizar")
                            {
                                accion_seccion = "ActualizarSeccion";
                            }else{
                                accion_seccion = "GuardarSeccion";
                            }
            });
            //
            // ENVIO DE DATOS Y VALIDAR INFORMACION DEL FORM
            //
            $('#goGuardarSeccion').on( 'click', function () {
                // enviar form
                    $('#formVentanaSeccion').submit();
            });
            //	  
            // Validar Formulario para la buscque de registro segun el criterio.   
            // PARA GUARDAR O ACTUALIZAR.
            $('#formVentanaSeccion').validate({
                ignore:"",
                rules:{
                        DescripcionSeccion: {required: true, minlength: 1},
                        CodigoSeccion:{required: true, minlength: 2},
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
                            invalidHandler: function() {
                                setTimeout(function() {
                                    toastr.error("Faltan Datos...");
                            });            
                        },
                    submitHandler: function(){	
                        var str = $('#formVentanaSeccion').serialize();
                        //alert(str);
                    ///////////////////////////////////////////////////////////////			
                    // Inicio del Ajax. guarda o Actualiza los datos del Formualrio.
                    ///////////////////////////////////////////////////////////////
                        $.ajax({
                            beforeSend: function(){

                            },
                            cache: false,
                            type: "POST",
                            dataType: "json",
                            url:"php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",
                            data:str + "&accion=" + accion_seccion + "&id=" + Math.random(),
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Abrir ventana modal.
                                         $('#VentanaSeccion').modal("hide");
                                         $("#formVentanaSeccion")[0].reset();
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_seccion').val('BuscarSeccion');
                                        accion_seccion = 'BuscarSeccion';
                                        $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_seccion, codigo_se: codigo_se},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoSESeccion').empty();
                                                    $('#listaContenidoSESeccion').append(response.contenido);
                                            },"json");
                                    }               
                            },
                        });
                    },
           });
}); // FIN DEL FUNCTION.
//
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
