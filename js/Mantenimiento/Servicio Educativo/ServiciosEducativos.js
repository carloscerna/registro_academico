// id de user global
var idUser_ok = 0;
var accion_servicios_educativos_ok = 'noAccion';
var accion_servicios_educativos = "";
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
    $("#AlertSe").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
           //
    // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
    //
    $("#NavServicioEducativo ul.nav > li > a").on("click", function () {
        $TextoTab = $(this).text();
  
        
        if($TextoTab == "Servicios Educativos"){
            // Borrar información de la Tabla.
                $('#listaContenidoSe').empty();
        }else{
            //alert("Nav-Tab " + $TextoTab);
        }
      });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (Se CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
	    $("#VentanaSe").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaSe")[0].reset();
                $('#formVentanaSe').trigger("reset");
				$("label.error").remove();
                accion_servicios_educativos = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS SeS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
        // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS (ASIGANTURAS)
        //
		$('body').on('click','#listaContenidoSe a',function (e){
			e.preventDefault();
			// Id Usuario
    			Id_Editar_Eliminar = $(this).attr('href');
	    		accion_ok = $(this).attr('data-accion');
                    // EDITAR LA ServiciosEducativos
                    if($(this).attr('data-accion') == 'EditarSe'){
                        // Valor de la acción
                            $('#accion_servicios_educativos').val('ActualizarSe');
                            accion_servicios_educativos = 'EditarSe';
                            
                            // obtener el valor del id.
                            var id_ = $(this).parent().parent().children('td:eq(2)').text();
                            
                            // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  { id_: id_, accion: accion_servicios_educativos},
                                function(data) {
                                // Llenar el formulario con los datos del registro seleccionado tabs-1
                                // Datos Generales
                                    $('#IdServiciosEducativos').val(data[0].id_servicio_educativo);
                                    $('#CodigoServiciosEducativos').val(data[0].codigo);
                                    $('#DescripcionServiciosEducativos').val(data[0].nombre);
                                    // Abrir ventana modal.
                                    $('#VentanaSe').modal("show");
                                    $("label[for=LblTituloSe]").text("ServiciosEducativos | Actualizar");
                                    // reestablecer el accion_servicios_educativos a=ActulizarServiciosEducativos.
                                    accion_servicios_educativos = "ActualizarSe";
                                },"json");
                    }
                    // ELIMINAR REGISTRO ServiciosEducativos.
                    if($(this).attr('data-accion') == 'eliminar_ServiciosEducativos'){
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
                                            accion_buscar: 'eliminar_ServiciosEducativos', codigo_id_: Id_Editar_Eliminar,
                                            },                     
                                    success: function(response) {                     
                                            if (response.respuesta === true) {                     		
                                                // Asignamos valor a la variable acción
                                                    $('#accion_servicios_educativos').val('BuscarSe');
                                                    var codigo_se = $("#lstcodigose").val();
                                                    accion_servicios_educativos = 'BuscarSe';
                                                    //
                                                    //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                                                    //
                                                    if(codigo_se == "00"){
                                                        $("#AlertSE").css("display", "block");
                                                        return;
                                                    }
                                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_servicios_educativos, codigo_se: codigo_se},
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
	$("#checkBoxAllSe").on("change", function () {
		$("#listadoContenidoSE tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoSe tbody").on("change", "input[type='checkbox'].case", function () {
	  if ($("#listadoContenidoSe tbody input[type='checkbox'].case").length == $("#listadoContenidoSe tbody input[type='checkbox'].case:checked").length) {
		  $("#checkBoxAllSe").prop("checked", true);
	  } else {
		  $("#checkBoxAllSe").prop("checked", false);
	  }
	 });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
        //
        //  funcion click
        //
            $('#goBuscarSe').on('click',function(){
                // Asignamos valor a la variable acción
                    $('#accion_servicios_educativos').val('BuscarSe');
                    codigo_se = $("#lstcodigose").val();
                    accion_servicios_educativos = 'BuscarSe';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_servicios_educativos},
                        function(response) {
                        if (response.respuesta === true) {
                            toastr["info"]('Registros Encontrados', "Sistema");
                        }
                        if (response.respuesta === false) {
                            toastr["warning"]('Registros No Encontrados', "Sistema");
                        }                                                                                    // si es exitosa la operación
                            $('#listaContenidoSe').empty();
                            $('#listaContenidoSe').append(response.contenido);
                        },"json");
            });
            //////////////////////////////////////////////////////////////////////////////////
            /* VER #CONTROLES CREADOS */
            //////////////////////////////////////////////////////////////////////////////////
            $('#goNuevoSe').on('click', function(){
                // Abrir ventana modal.
                    $('#VentanaSe').modal("show");
                    $("label[for=LblTituloSe]").text("Servicios Educativos | Nuevo");
                // BUSCAR Y GENERAR NUEVO CODIGO PARA LA ASIGNATURA.
                //
                // BUSCAR EL ÚLTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
                    accion_servicios_educativos = 'BuscarCodigoSe';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_servicios_educativos},
                                function(data) {
                                    // si es exitosa la operación
                                        $('#CodigoServiciosEducativos').val(data[0].codigo);
                                },"json");
                    // RETORNAR EL VALOR DEL ACCION SEGUN ETIQUETA LABEL.
                        msjEtiqueta = $("label[for=LblTituloSe]").text();
                            if(msjEtiqueta == "Servicios Educativos | Actualizar")
                            {
                                accion_servicios_educativos = "ActualizarSe";
                            }else{
                                accion_servicios_educativos = "GuardarSe";
                            }
            });
            //
            // ENVIO DE DATOS Y VALIDAR INFORMACION DEL FORM
            //
            $('#goGuardarSe').on( 'click', function () {
                // enviar form
                    $('#formVentanaSe').submit();
            });
            //	  
            // Validar Formulario para la buscque de registro segun el criterio.   
            // PARA GUARDAR O ACTUALIZAR.
            $('#formVentanaSe').validate({
                ignore:"",
                rules:{
                        DescripcionServiciosEducativos: {required: true, minlength: 1},
                        CodigoServiciosEducativos:{required: true, minlength: 2},
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
                        var str = $('#formVentanaSe').serialize();
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
                            data:str + "&accion=" + accion_servicios_educativos + "&id=" + Math.random(),
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Abrir ventana modal.
                                        $('#VentanaSe').modal("hide");
                                        $("#formVentanaSe")[0].reset();
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_servicios_educativos').val('BuscarSe');
                                        accion_servicios_educativos = 'BuscarSe';
                                        $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_servicios_educativos, codigo_se: codigo_se},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoSe').empty();
                                                    $('#listaContenidoSe').append(response.contenido);
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
