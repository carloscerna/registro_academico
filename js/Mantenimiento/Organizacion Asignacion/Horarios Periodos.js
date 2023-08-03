// id de user global
var idUser_ok = 0;
var accion_horarios = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo_horarios = "";
var texto_annlectivo_horarios = "";
var codigo_modalidad_horarios = "";
var texto_modalidad_horarios = "";
var msjEtiqueta = "";
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
//
    $("#AlertHorarios").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        //
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //
    $("#NavOrganizacionAsignacion ul.nav > li > a").on("click", function () {
        $TextoTab = $(this).text();
        
        if($TextoTab == "Horarios"){
            // Borrar información de la Tabla.
                $('#listaContenidoHorarios').empty();
                $("#AlertHorarios").css("display", "none");
            // Select a 00...
                $("#LstAnnLectivo").val('00')
                $("#LstNivel").val('00')
        }else{
            //alert("Nav-Tab " + $TextoTab);
        }
    });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (HORARIOS CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // funcion onchange.
        $('#lstAnnLectivoHorarios').on('change', function() {
            $("#AlertHorarios").css("display", "none");
        });
        // Nivel o Modalidad.
        $('#lstModalidadHorarios').on('change', function() {
            $("#AlertHorarios").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentanaHorariosPeriodos").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaHorarios")[0].reset();
                $('#formVentanaHorarios').trigger("reset");
				$("label.error").remove();
                accion = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS ASIGNATURAS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
        // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS (ASIGANTURAS)
        //
		$('body').on('click','#listaContenidoHorarios a',function (e){
			e.preventDefault();
			// Id Usuario
                Id_Editar_Eliminar = $(this).attr('href');
                accion_ok = $(this).attr('data-accion');
                    // EDITAR LA ASIGNATURA
                    if($(this).attr('data-accion') == 'EditarHorarios'){
                        // Valor de la acción
                            $('#accion_horarios').val('EditarHorarios');
                            accion = 'EditarHorarios';
                            
                            // obtener el valor del id.
                            var id_ = $(this).parent().parent().children('td:eq(2)').text();
                            
                            // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  { id_: id_, accion: accion},
                                function(data) {
                                // Llenar el formulario con los datos del registro seleccionado tabs-1
                                // Datos Generales
                                    texto_annlectivo_horarios = $("#lstAnnLectivoHorarios option:selected").html();
                                    codigo_annlectivo_horarios = $("#lstAnnLectivoHorarios option:selected").val();
                                    texto_modalidad_horarios = $("#lstModalidadHorarios option:selected").html();
                                    codigo_modalidad_horarios = $("#lstModalidadHorarios option:selected").val();
                                    //
                                    $("#TextoAnnLectivoHorarios").text(texto_annlectivo_horarios);
                                    $("#TextoModalidadesHorarios").text(texto_modalidad_horarios);
                                    //
                                    listar_CodigoEstatus(data[0].codigo_estatus);
                                    listar_CodigoPeriodos(data[0].codigo_periodo);
                                    //
                                    $('#IdHorarios').val(data[0].id_);
                                    $('#FechaInicio').val(data[0].fecha_desde);
                                    $('#FechaFin').val(data[0].fecha_hasta);
                                    $('#FechaRA').val(data[0].fecha_registro_academico);
                                    // Abrir ventana modal.
                                    $('#VentanaHorariosPeriodos').modal("show");
                                    $("label[for=LblTituloHorarios]").text("Horarios | Actualizar");
                                    // reestablecer el accion a=ActulizarAsignatura.
                                    accion_horarios = "ActualizarHorarios";
                                },"json");
                    }
                    // ELIMINAR REGISTRO ASIGNATURA.
                    if($(this).attr('data-accion') == 'EliminarHorarios'){
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
                                    url:"php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",                     
                                    data: {                     
                                            accion_buscar: 'eliminar_asignatura', codigo_id_: Id_Editar_Eliminar,
                                            },                     
                                    success: function(response) {                     
                                            if (response.respuesta === true) {                     		
                                                // Asignamos valor a la variable acción
                                                    $('#accion_horarios').val('BuscarHorarios');
                                                    accion = 'BuscarHorarios';
                                                    //
                                                    //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                                                    //
                                                    if(codigo_se == "00"){
                                                        $("#AlertSE").css("display", "block");
                                                        return;
                                                    }
                                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion},
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
	$("#checkBoxAllHorarios").on("change", function () {
		$("#listadoContenidoHorarios tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoHorarios tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoHorarios tbody input[type='checkbox'].case").length == $("#listadoContenidoHorarios tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllHorarios").prop("checked", true);
        } else {
            $("#checkBoxAllHorarios").prop("checked", false);
        }
    });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
        //
        //  funcion click
        //
            $('#goBuscarHorarios').on('click',function(){
                // Asignamos valor a la variable acción
                    $('#accion_horarios').val('BuscarHorarios');
                    codigo_annlectivo_horarios = $("#lstAnnLectivoHorarios").val();
                    codigo_modalidad_horarios = $("#lstModalidadHorarios").val();
                    accion = 'BuscarHorarios';
                    //
                    //  CONDICONAR EL SELECT HORARIOS DE PERIODOS..
                    //
                    if(codigo_annlectivo_horarios == "00"){
                        $("#AlertHorarios").css("display", "block");
                        $("#TextoAlertHorarios").text("Debe Seleccionar Año Lectivo para Buscar.");
                        return;
                    }
                    if(codigo_modalidad_horarios == "00"){
                        $("#AlertHorarios").css("display", "block");
                        $("#TextoAlertHorarios").text("Debe Seleccionar un Nivel para Buscar.");
                        return;
                    }
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo_horarios, codigo_modalidad: codigo_modalidad_horarios},
                        function(response) {
                        if (response.respuesta === true) {
                            toastr["info"]('Registros Encontrados', "Sistema");
                        }
                        if (response.respuesta === false) {
                            toastr["error"]('Registros No Encontrados', "Sistema");
                        }                                                                                    // si es exitosa la operación
                            $('#listaContenidoHorarios').empty();
                            $('#listaContenidoHorarios').append(response.contenido);
                        },"json");
            });
            //////////////////////////////////////////////////////////////////////////////////
            /* VER #CONTROLES CREADOS */
            //////////////////////////////////////////////////////////////////////////////////
            $('#goNuevoHorarios').on('click', function(){
                texto_annlectivo_horarios = $("#lstAnnLectivoHorarios option:selected").html();
                codigo_annlectivo_horarios = $("#lstAnnLectivoHorarios option:selected").val();
                texto_modalidad_horarios = $("#lstModalidadHorarios option:selected").html();
                codigo_modalidad_horarios = $("#lstModalidadHorarios option:selected").val();
                accion = 'GuardarHorarios';
                $('#accion_horarios').val('GuardarHorarios');

                //
                //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                //
                if(codigo_annlectivo_horarios == "00"){
                    $("#AlertHorarios").css("display", "block");
                    $("#TextoAlertHorarios").text("Debe Seleccionar un Año Lectivo para Crear uno Nuevo Horario.");
                    return;
                }
                if(codigo_modalidad_horarios == "00"){
                    $("#AlertHorarios").css("display", "block");
                    $("#TextoAlertHorarios").text("Debe Seleccionar un Nivel para Buscar.");
                    return;
                }
                
                    $("#TextoAnnLectivoHorarios").text(texto_annlectivo_horarios);
                    $("#TextoModalidadesHorarios").text(texto_modalidad_horarios);
                    // buscare codigo estatus
                        listar_CodigoEstatus();
                        listar_CodigoPeriodos();
                
                // Abrir ventana modal.
                $('#VentanaHorariosPeriodos').modal("show");
                $("label[for=LblTituloHorarios]").text("Horarios | Nuevo");
                    // RETORNAR EL VALOR DEL ACCION SEGUN ETIQUETA LABEL.
                    msjEtiqueta = $("label[for=LblTituloHorarios]").text();
                    if(msjEtiqueta == "Horarios | Actualizar")
                    {
                        accion_horarios = "ActualizarHorarios";
                    }else{
                        accion_horarios = "GuardarHorarios";
                    }
            });
            //
            // ENVIO DE DATOS Y VALIDAR INFORMACION DEL FORM para guardar o Actualizar.
            //
            $('#goGuardarHorarios').on( 'click', function () {
                // enviar form
                    $('#formVentanaHorarios').submit();
            });
            //	  
            // Validar Formulario para la buscque de registro segun el criterio.   
            // PARA GUARDAR O ACTUALIZAR.
            $('#formVentanaHorarios').validate({
                ignore:"",
                rules:{
                        FechaRA: {required: true},
                        FechaFin: {required: true},
                        FechaInicio: {required: true},
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
                        var str = $('#formVentanaHorarios').serialize();
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
                            url:"php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                            data:str + "&accion=" + accion_horarios + "&id=" + Math.random() + "&codigo_annlectivo=" + codigo_annlectivo_horarios + "&codigo_modalidad=" + codigo_modalidad_horarios,
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Abrir ventana modal.
                                        $('#VentanaHorariosPeriodos').modal("hide");
                                        $("#formVentanaHorarios")[0].reset();
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_horarios').val('BuscarHorarios');
                                        accion = 'BuscarHorarios';
                                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo_horarios, codigo_modalidad: codigo_modalidad_horarios},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoHorarios').empty();
                                                    $('#listaContenidoHorarios').append(response.contenido);
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
function listar_CodigoEstatus(CodigoEstatus){
    var miselect=$("#lstHorarios");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_estatus.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(CodigoEstatus == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
 ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo_estatus
////////////////////////////////////////////////////////////
function listar_CodigoPeriodos(CodigoPeriodos){
    var miselect=$("#lstPeriodosHorarios");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_periodos.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(CodigoPeriodos == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}