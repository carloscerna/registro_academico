// id de user global
var idUser_ok = 0;
var accion_contratacion = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo = "";
var codigo_modalidad = "";
var msjEtiqueta = "";
var codigo_personal = 0;
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
    //  
    $("#AlertContratacion").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        //
        // CARGAR DESDE CATALOGO...
        //        
            var miselect=$("#lstTipoContratacion");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
            miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            
            $.post("includes/Personal/Catalogos/Contratacion.php",
                function(data) {
                    miselect.empty();
                    miselect.append("<option value='00'>Seleccionar...</option>");
                    for (var i=0; i<data.length; i++) {
                        miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                    }
            }, "json");
        //
        // CARGAR DESDE CATALOGO...
        //        
        var miselect2=$("#lstRubro");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/Personal/Catalogos/Rubro.php",
            function(data) {
                miselect2.empty();
                miselect2.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect2.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
        }, "json");
        //
        // CARGAR DESDE CATALOGO...
        //        
        var miselect3=$("#lstHorario");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect3.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/Personal/Catalogos/Horario.php",
            function(data) {
                miselect3.empty();
                miselect3.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect3.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
        }, "json");
        //
        // CARGAR DESDE CATALOGO...
        //        
        var miselect4=$("#lstTurno");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect4.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/Personal/Catalogos/Turno.php",
            function(data) {
                miselect4.empty();
                miselect4.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect4.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
        }, "json");
        //
        // CARGAR DESDE CATALOGO...
        //        
        var miselect5=$("#lstDescuento");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect5.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/Personal/Catalogos/Descuento.php",
            function(data) {
                miselect5.empty();
                miselect5.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect5.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
        }, "json");
        ////////////////////////////////////////////////////////////////////////////
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //////////////////////////////////////////////////////////////////////////
    $("#NavContratacion ul.nav > li > a").on("click", function () {
        TextoTab = $(this).text();
        //alert(TextoTab);
        if(TextoTab == "Contratación"){
            // Borrar información de la Tabla.
                $('#listaContenidoContratacion').empty();
                $("#AlertContratacion").css("display", "none");
            // Select a 00...
                $("#lstRubro").val('00')
                $("#lstTipoContratacion").val('00')
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
        $('#lstRubro').on('change', function() {
            $("#AlertContratacion").css("display", "none");
        });
        // ...
        $('#lstTipoContratacion').on('change', function() {
            $("#AlertContratacion").css("display", "none");
        });
        // ...
        $('#lstTurno').on('change', function() {
            $("#AlertContratacion").css("display", "none");
        });
        // ...
        $('#lstHorario').on('change', function() {
            $("#AlertContratacion").css("display", "none");
        });
        // ...
        $('#lstDescuento').on('change', function() {
            $("#AlertContratacion").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentanaContratacion").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaContratacion")[0].reset();
                $('#formVentanaContratacion').trigger("reset");
				$("label.error").remove();
                accion_contratacion = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS)
    //
    $('body').on('click','#listaContenidoContratacion a',function (e){
        e.preventDefault();
        // Id Usuario
            Id_Editar_Eliminar = $(this).attr('href');
            accion_ok = $(this).attr('data-accion');
            codigo_personal = $("#id_user").val();
                // EDITAR LA ASIGNATURA
                if($(this).attr('data-accion') == 'EditarContratacion'){
                        // Valor de la acción
                            accion_contratacion = 'EditarContratacion';
                        // obtener el valor del id.
                            var id_ = $(this).parent().parent().children('td:eq(2)').text();
                        // Llamar al archivo php para hacer la consulta y presentar los datos.
                        $.post("php_libs/soporte/Personal/Contratacion.php",  { id_: id_, accion: accion_contratacion, codigo_personal: codigo_personal},
                            function(data) {
                            // Llenar el formulario con los datos del registro seleccionado tabs-1
                            // Datos Generales
                                //
                                $("#TextoNombreContratacion").text("Nombre: " + data[0].nombre_personal);
                                $("#TextoCargoContratacion").text("Cargo: " + data[0].nombre_cargo);
                                //
                                $("#FechaContratacion").val(data[0].fecha)
                                listar_Rubro(data[0].codigo_rubro);
                                listar_Contratacion(data[0].codigo_tipo_contratacion);
                                listar_Turno(data[0].codigo_turno);
                                listar_Horario(data[0].codigo_horario);
                                listar_Descuento(data[0].codigo_tipo_descuento);
                                $("#SalarioContratacion").val(data[0].salario)
                                //
                                // Abrir ventana modal.
                                $('#VentanaContratacion').modal("show");
                                $("label[for=LblTituloContratacion]").text("Contratación | Actualizar");
                                // reestablecer el accion a=ActulizarAsignatura.
                                accion_contratacion = "ActualizarContratacion";
                            },"json");
                }
                // ELIMINAR REGISTRO ASIGNATURA.
                if($(this).attr('data-accion') == 'EliminarContratacion'){
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
                                url:"php_libs/soporte/Personal/Contratacion.php",                     
                                data: {                     
                                        accion: 'EliminarContratacion', id_: Id_Editar_Eliminar,
                                        codigo_personal: codigo_personal,
                                        },                     
                                success: function(response) {                     
                                        if (response.respuesta === true) {                     		
                                            // Buscar nuevamente información, después de eliminar.
                                                BuscarContratacion();
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
	$("#checkBoxAllContratacion").on("change", function () {
		$("#checkBoxAllContratacion tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoContratacion tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoContratacion tbody input[type='checkbox'].case").length == $("#listadoContenidoContratacion tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllContratacion").prop("checked", true);
        } else {
            $("#checkBoxAllContratacion").prop("checked", false);
        }
    });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
    //
    //  funcion click
    //
        $('#goBuscarContratacion').on('click',function(){
            // Funciones...
                BuscarContratacion();
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goGuardarContratacion').on('click', function(){
            accion_contratacion = 'GuardarContratacion';
            //
                CondicionesSelect();
            // enviar form
                $('#FormContratacion').submit();
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goActualizarContratacion').on('click', function(){
            accion_contratacion = 'ActualizarContratacion';
            // funcion...
                //CondicionesSelect();
            // enviar form
                $('#formVentanaContratacion').submit();
        });
        //	  
        // Validar Formulario para la buscque de registro segun el criterio.   
        // ACTUALIZAR
        $('#formVentanaContratacion').validate({
            ignore:"",
            rules:{
                    lstRubro: {required: true},
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
                                toastr["error"]("Falta Información en el Formulario.", "Sistema");
                        });            
                    },
                submitHandler: function(){	
                    var str = $('#formVentanaContratacion').serialize();
                    codigo_personal = $("#id_user").val();
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
                        url:"php_libs/soporte/Personal/Contratacion.php",
                        data:str + "&accion=" + accion + "&id=" + Math.random() + "&id_=" + Id_Editar_Eliminar + "&codigo_personal=" + codigo_personal,
                        success: function(response){
                            // Validar mensaje de error
                            if(response.respuesta == false){
                                toastr["error"](response.mensaje, "Sistema");
                            }
                            else{
                                toastr["success"](response.mensaje, "Sistema");
                                // Abrir ventana modal.
                                    $('#VentanaContratacion').modal("hide");
                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                    BuscarContratacion();
                                }               
                        },
                    });
                },
        });
        // PARA GUARDAR O ACTUALIZAR.
        $('#FormContratacion').validate({
            ignore:"",
            rules:{
                    lstRubro: {required: true},
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
                                toastr["error"]("Falta Información en el Formulario.", "Sistema");
                        });            
                    },
                submitHandler: function(){	
                    var str = $('#FormContratacion').serialize()
                    codigo_personal = $("#id_user").val();
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
                        url:"php_libs/soporte/Personal/Contratacion.php",
                        data:str + "&accion=" + accion_contratacion + "&id=" + Math.random() + "&codigo_personal=" + codigo_personal,
                        success: function(response){
                            // Validar mensaje de error
                            if(response.respuesta == false){
                                toastr["error"](response.mensaje, "Sistema");
                            }
                            else{
                                toastr["success"](response.mensaje, "Sistema");
                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                    BuscarContratacion();
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
// DIFERENTES TIPO SDE FUNCIONES...
//
function BuscarContratacion(){
    // Asignamos valor a la variable acción
        codigo_personal = $("#id_user").val();
        codigo_rubro = $("#lstRubro").val();
        accion_contratacion = 'BuscarContratacion';
    // Llamar al archivo php para hacer la consulta y presentar los datos.
        $.post("php_libs/soporte/Personal/Contratacion.php",  {accion: accion_contratacion, 
            codigo_personal: codigo_personal},
        function(response) {
        if (response.respuesta === true) {
            toastr["info"]('Registros Encontrados', "Sistema");
        }
        if (response.respuesta === false) {
            toastr["error"]('Registros No Encontrados', "Sistema");
        }                                                                                    // si es exitosa la operación
            $('#listaContenidoContratacion').empty();
            $('#listaContenidoContratacion').append(response.contenido);
        },"json");
}
//
function CondicionesSelect(){
    codigo_rubro = $("#lstRubro").val();
    codigo_tipo_contratacion = $("#lstTipoContratacion").val();
    codigo_turno = $("#lstTurno").val();
    codigo_horario = $("#lstHorario").val();
    codigo_descuento = $("#lstDescuento").val();
        //
        //  CONDICONAR EL SELECT ...
        //
        if(codigo_rubro == "00"){
            $("#AlertContratacion").css("display", "block");
            $("#TextoAlertContratacion").text("Debe Seleccionar Rubro.");
            return;
        }
        if(codigo_tipo_contratacion == "00"){
            $("#AlertContratacion").css("display", "block");
            $("#TextoAlertContratacion").text("Debe Seleccionar Tipo Contratación.");
            return;
        }
        if(codigo_turno == "00"){
            $("#AlertContratacion").css("display", "block");
            $("#TextoAlertContratacion").text("Debe Seleccionar Turno.");
            return;
        }
        if(codigo_horario == "00"){
            $("#AlertContratacion").css("display", "block");
            $("#TextoAlertContratacion").text("Debe Seleccionar Horario.");
            return;
        }
        if(codigo_descuento == "00"){
            $("#AlertContratacion").css("display", "block");
            $("#TextoAlertContratacion").text("Debe Seleccionar Descuento.");
            return;
        }
}
 ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN organizaciones grado-seccion-turno.*******************
// FUNCION LISTAR TABLA catalogos...
////////////////////////////////////////////////////////////
function listar_Contratacion(Codigo){
    var miselect=$("#formVentanaContratacion select[name=lstContratacion]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/Personal/Catalogos/Contratacion.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(Codigo == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
function listar_Rubro(Codigo){
    var miselect=$("#formVentanaContratacion select[name=lstRubro]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/Personal/Catalogos/Rubro.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(Codigo == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
function listar_Turno(Codigo){
    var miselect=$("#formVentanaContratacion select[name=lstTurno]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/Personal/Catalogos/Turno.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(Codigo == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
function listar_Horario(Codigo){
    var miselect=$("#formVentanaContratacion select[name=lstHorario]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/Personal/Catalogos/Horario.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(Codigo == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
function listar_Descuento(Codigo){
    var miselect=$("#formVentanaContratacion select[name=lstDescuento]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/Personal/Catalogos/Descuento.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(Codigo == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}