// id de user global
var idUser_ok = 0;
var accion_modalidad_ok = 'noAccion';
var accion_modalidad = "";
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
    $("#AlertSEModalidad").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
           //
    // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
    //
    $("#NavServicioEducativo ul.nav > li > a").on("click", function () {
        $TextoTab = $(this).text();
  
        
        if($TextoTab == "Nivel"){
            // Borrar información de la Tabla.
                $('#listaContenidoSEModalidad').empty();
        }else{
            //alert("Nav-Tab " + $TextoTab);
        }
      });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (Modalidad CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////

        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
	    $("#VentanaModalidad").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaModalidad")[0].reset();
                $('#formVentanaModalidad').trigger("reset");
				$("label.error").remove();
                accion_modalidad = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS ModalidadS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
        // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS (ASIGANTURAS)
        //
		$('body').on('click','#listaContenidoSEModalidad a',function (e){
			e.preventDefault();
			// Id Usuario
    			Id_Editar_Eliminar = $(this).attr('href');
	    		accion_ok = $(this).attr('data-accion');
                    // EDITAR LA Modalidad
                    if($(this).attr('data-accion') == 'EditarModalidad'){
                        // Valor de la acción
                            $('#accion_Modalidad').val('ActualizarModalidad');
                            accion_modalidad = 'EditarModalidad';
                            
                            // obtener el valor del id.
                            var id_ = $(this).parent().parent().children('td:eq(2)').text();
                            
                            // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  { id_: id_, accion: accion_modalidad},
                                function(data) {
                                // Llenar el formulario con los datos del registro seleccionado tabs-1
                                // Datos Generales
                                    $('#IdModalidad').val(data[0].id_Modalidad);
                                    $('#CodigoModalidad').val(data[0].codigo);
                                    $('#DescripcionModalidad').val(data[0].nombre);
                                    //
                                    listar_CodigoEstatusModalidad(data[0].codigo_estatus);
                                    // Abrir ventana modal.
                                    $('#VentanaModalidad').modal("show");
                                    $("label[for=LblTituloModalidad]").text("Modalidad | Actualizar");
                                    // reestablecer el accion_modalidad a=ActulizarModalidad.
                                    accion_modalidad = "ActualizarModalidad";
                                },"json");
                    }
                    // ELIMINAR REGISTRO Modalidad.
                    if($(this).attr('data-accion') == 'eliminar_Modalidad'){
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
                                            accion_buscar: 'eliminar_Modalidad', codigo_id_: Id_Editar_Eliminar,
                                            },                     
                                    success: function(response) {                     
                                            if (response.respuesta === true) {                     		
                                                // Asignamos valor a la variable acción
                                                    $('#accion_Modalidad').val('BuscarModalidad');
                                                    var codigo_se = $("#lstcodigose").val();
                                                    accion_modalidad = 'BuscarModalidad';
                                                    //
                                                    //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                                                    //
                                                    if(codigo_se == "00"){
                                                        $("#AlertSE").css("display", "block");
                                                        return;
                                                    }
                                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_modalidad, codigo_se: codigo_se},
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
	$("#checkBoxAllSEModalidad").on("change", function () {
		$("#listadoContenidoSE tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoSEModalidad tbody").on("change", "input[type='checkbox'].case", function () {
	  if ($("#listadoContenidoSEModalidad tbody input[type='checkbox'].case").length == $("#listadoContenidoSEModalidad tbody input[type='checkbox'].case:checked").length) {
		  $("#checkBoxAllSEModalidad").prop("checked", true);
	  } else {
		  $("#checkBoxAllSEModalidad").prop("checked", false);
	  }
	 });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
        //
        //  funcion click
        //
            $('#goBuscarSEModalidad').on('click',function(){
                // Asignamos valor a la variable acción
                    $('#accion_Modalidad').val('BuscarModalidad');
                    codigo_se = $("#lstcodigose").val();
                    accion_modalidad = 'BuscarModalidad';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_modalidad},
                        function(response) {
                        if (response.respuesta === true) {
                            toastr["info"]('Registros Encontrados', "Sistema");
                        }
                        if (response.respuesta === false) {
                            toastr["warning"]('Registros No Encontrados', "Sistema");
                        }                                                                                    // si es exitosa la operación
                            $('#listaContenidoSEModalidad').empty();
                            $('#listaContenidoSEModalidad').append(response.contenido);
                        },"json");
            });
            //////////////////////////////////////////////////////////////////////////////////
            /* VER #CONTROLES CREADOS */
            //////////////////////////////////////////////////////////////////////////////////
            $('#goNuevoSEModalidad').on('click', function(){
                // Abrir ventana modal.
                    $('#VentanaModalidad').modal("show");
                    $("label[for=LblTituloModalidad]").text("Modalidad | Nuevo");
                //
                    listar_CodigoEstatusModalidad();
                // BUSCAR Y GENERAR NUEVO CODIGO PARA LA ASIGNATURA.
                //
                // BUSCAR EL ÚLTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
                    accion = 'BuscarCodigoModalidad';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion},
                                function(data) {
                                    // si es exitosa la operación
                                        $('#CodigoModalidad').val(data[0].codigo);
                                },"json");
                    // RETORNAR EL VALOR DEL ACCION SEGUN ETIQUETA LABEL.
                        msjEtiqueta = $("label[for=LblTituloModalidad]").text();
                            if(msjEtiqueta == "Modalidad | Actualizar")
                            {
                                accion_modalidad = "ActualizarModalidad";
                            }else{
                                accion_modalidad = "GuardarModalidad";
                            }
            });
            //
            // ENVIO DE DATOS Y VALIDAR INFORMACION DEL FORM
            //
            $('#goGuardarModalidad').on( 'click', function () {
                // enviar form
                    $('#formVentanaModalidad').submit();
            });
            //	  
            // Validar Formulario para la buscque de registro segun el criterio.   
            // PARA GUARDAR O ACTUALIZAR.
            $('#formVentanaModalidad').validate({
                ignore:"",
                rules:{
                        DescripcionModalidad: {required: true, minlength: 4},
                        CodigoModalidad:{required: true, minlength: 2},
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
                        var str = $('#formVentanaModalidad').serialize();
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
                            data:str + "&accion=" + accion_modalidad + "&id=" + Math.random(),
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Abrir ventana modal.
                                         $('#VentanaModalidad').modal("hide");
                                         $("#formVentanaModalidad")[0].reset();
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_Modalidad').val('BuscarModalidad');
                                        accion_modalidad = 'BuscarModalidad';
                                        $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion_modalidad, codigo_se: codigo_se},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoSEModalidad').empty();
                                                    $('#listaContenidoSEModalidad').append(response.contenido);
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
    ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo_estatus
////////////////////////////////////////////////////////////
function listar_CodigoEstatusModalidad(CodigoEstatus){
    var miselect2=$("#lstModalidadEstatus");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_estatus.php",
        function(data) {
            miselect2.empty();
            miselect2.append("<option value='00'>Seleccionar...</option>");
            for (var i=0; i<data.length; i++) {
                if(CodigoEstatus == data[i].codigo){
                    miselect2.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect2.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}