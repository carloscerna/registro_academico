// id de user global
var idUser_ok = 0;
var accion_gst = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo = "";
var codigo_modalidad = "";
var msjEtiqueta = "";
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
    //  
    $("#AlertSeGST").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        var miselect=$("#lstAnnLectivoSeGST");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/cargar-ann-lectivo.php",
            function(data) {
                miselect.empty();
                miselect.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
                }
        }, "json");
        //
        // CUANDO EL VALOR DE ANNLECTIVO CAMBIA.
        //
        $("#lstAnnLectivoSeGST").change(function ()
        {
            // LISTADO DE LAS MODALIDES
            var miselect2=$("#lstModalidadSeGST");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            //        
                $("#lstAnnLectivoSeGST option:selected").each(function () {
                    elegido=$(this).val();
                        annlectivo=$("#lstAnnLectivoSeGST").val();
                        $.post("includes/cargar-bachillerato.php", { annlectivo: annlectivo },
                        function(data){
                                miselect2.empty();
                                miselect2.append("<option value='00'>Seleccionar...</option>");
                                for (var i=0; i<data.length; i++) {
                                miselect2.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                }
                    }, "json");		
                });
        });
        // CUANDO EL VALOR DE NIVEL O MODALIDAD CAMBIE.
        $("#lstModalidadSeGST").change(function () {
            $("#lstModalidadSeGST option:selected").each(function () {
                elegido=$(this).val();
                modalidad=$("#lstModalidadSeGST").val();
                // validar
                    if(modalidad == "00"){
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoSeGST').empty();
                        // limpiar select
                        var miselect3=$("#lstSeGST");
                        var miselect4=$("#lstGradoSeGST");
                        var miselect5=$("#lstSeccionSeGST");
                        var miselect6=$("#lstTurnoSeGST");
                            miselect3.empty();
                            miselect4.empty();
                            miselect5.empty();
                            miselect6.empty();
                    }else{
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoSeGST').empty();
                        // LISTAR PARA EL SERVIICO EDUCATIVO - COMPONENTES DE ESTUDIOS.
                        var miselect3=$("#lstSeGST");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect3.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-servicio-educativo.php",
                            function(data) {
                            miselect3.empty();
                            miselect3.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect3.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }			
                        }, "json");
                        // LISTAR PARA EL SERVIICO EDUCATIVO - grado.
                        var miselect4=$("#lstGradoSeGST");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect4.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-grado.php",
                            function(data) {
                            miselect4.empty();
                            miselect4.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect4.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }			
                        }, "json");
                        // LISTAR PARA EL SERVIICO EDUCATIVO - SECCION
                        var miselect5=$("#lstSeccionSeGST");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect5.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-seccion.php",
                            function(data) {
                            miselect5.empty();
                            miselect5.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect5.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }			
                        }, "json");
                        // LISTAR PARA EL SERVIICO EDUCATIVO - turno
                        var miselect6=$("#lstTurnoSeGST");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect6.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-turno.php",
                            function(data) {
                            miselect6.empty();
                            miselect6.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect6.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }			
                        }, "json");
                    }
            });
        });
        ////////////////////////////////////////////////////////////////////////////
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //////////////////////////////////////////////////////////////////////////
    $("#NavOrganizacionAsignacion ul.nav > li > a").on("click", function () {
        TextoTab = $(this).text();
        //alert(TextoTab);
        if(TextoTab == "Grado/Sección/Turno"){
            // Borrar información de la Tabla.
                $('#listaContenidoSeGST').empty();
                $("#AlertSeGST").css("display", "none");
            // Select a 00...
                $("#lstAnnLectivoSeGST").val('00')
                $("#lstModalidadSeGST").val('00')
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
        $('#lstAnnLectivoSeGST').on('change', function() {
            $("#AlertSeGST").css("display", "none");
        });
        // Nivel o SeGST.
        $('#lstModalidadSeGST').on('change', function() {
            $("#AlertSeGST").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentanaSeGST").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaSeGST")[0].reset();
                $('#formVentanaSeGST').trigger("reset");
				$("label.error").remove();
                accion = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS)
    //
    $('body').on('click','#listaContenidoSeGST a',function (e){
        e.preventDefault();
        // Id Usuario
            Id_Editar_Eliminar = $(this).attr('href');
            accion_ok = $(this).attr('data-accion');
                // EDITAR LA ASIGNATURA
                if($(this).attr('data-accion') == 'EditarSeGST'){
                }
                // ELIMINAR REGISTRO ASIGNATURA.
                if($(this).attr('data-accion') == 'EliminarSeGST'){
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
                                        accion_buscar: 'EliminarSeGST', id_: Id_Editar_Eliminar,
                                        },                     
                                success: function(response) {                     
                                        if (response.respuesta === true) {                     		
                                            // Asignamos valor a la variable acción
                                                $('#accion_modalidad').val('BuscarSeGST');
                                                accion_modalidad = 'BuscarSeGST';
                                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                                    function(response) {
                                                        if (response.respuesta === true) {
                                                            toastr["info"]('Registros Encontrados', "Sistema");
                                                        }
                                                        if (response.respuesta === false) {
                                                            toastr["warning"]('Registros No Encontrados', "Sistema");
                                                        }                                                                                    // si es exitosa la operación
                                                            $('#listaContenidoSeGST').empty();
                                                            $('#listaContenidoSeGST').append(response.contenido);
                                                    },"json");
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
	$("#checkBoxAllSeGST").on("change", function () {
		$("#listadoContenidoSeGST tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoSeGST tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoSeGST tbody input[type='checkbox'].case").length == $("#listadoContenidoSeGST tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllSeGST").prop("checked", true);
        } else {
            $("#checkBoxAllSeGST").prop("checked", false);
        }
    });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
    //
    //  funcion click
    //
        $('#goBuscarSeGST').on('click',function(){
            // Asignamos valor a la variable acción
                codigo_annlectivo = $("#lstAnnLectivoSeGST").val();
                codigo_modalidad = $("#lstModalidadSeGST").val();
                accion = 'BuscarSeGST';
                //
                //  CONDICONAR EL SELECT ...
                //
                if(codigo_annlectivo == "00"){
                    $("#AlertSeGST").css("display", "block");
                    $("#TextoAlertSeGST").text("Debe Seleccionar Año Lectivo para Buscar.");
                    return;
                }
                if(codigo_modalidad == "00"){
                    $("#AlertSeGST").css("display", "block");
                    $("#TextoAlertSeGST").text("Debe Seleccionar la Modalidad para Buscar.");
                    return;
                }
                // Llamar al archivo php para hacer la consulta y presentar los datos.
                $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                    function(response) {
                    if (response.respuesta === true) {
                        toastr["info"]('Registros Encontrados', "Sistema");
                    }
                    if (response.respuesta === false) {
                        toastr["error"]('Registros No Encontrados', "Sistema");
                    }                                                                                    // si es exitosa la operación
                        $('#listaContenidoSeGST').empty();
                        $('#listaContenidoSeGST').append(response.contenido);
                    },"json");
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goGuardarSeGST').on('click', function(){
            codigo_annlectivo = $("#lstAnnLectivoSeGST").val();
            codigo_modalidad = $("#lstSeGST").val();
            accion = 'GuardarSeGST';
                $('#accion_modalidad').val('GuardarSeGST');
            //
            //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
            //
            if(codigo_annlectivo == "00"){
                $("#AlertSeGST").css("display", "block");
                $("#TextoAlertSeGST").text("Debe Seleccionar un Año Lectivo para Guardar un Nivel.");
                return;
            }
            if(codigo_modalidad == "00"){
                $("#AlertSeGST").css("display", "block");
                $("#TextoAlertSeGST").text("Debe Seleccionar un Nivel para Guardar.");
                return;
            }
            // enviar form
                $('#FormSeGST').submit();
        });

        //	  
        // Validar Formulario para la buscque de registro segun el criterio.   
        // PARA GUARDAR O ACTUALIZAR.
        $('#FormSeGST').validate({
            ignore:"",
            rules:{
                    lstAnnLectivoSeGST: {required: true},
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
                    var str = $('#FormSeGST').serialize();
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
                        data:str + "&accion=" + accion + "&id=" + Math.random() + "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_modalidad=" + codigo_modalidad,
                        success: function(response){
                            // Validar mensaje de error
                            if(response.respuesta == false){
                                toastr["error"](response.mensaje, "Sistema");
                            }
                            else{
                                toastr["success"](response.mensaje, "Sistema");
                                // Reiniciar los valores del Formulario.
                                    //$("#FormSeGST").trigger("reset");
                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                    $('#accion_modalidad').val('BuscarSeGST');
                                    accion = 'BuscarSeGST';
                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                        function(response) {
                                            if (response.respuesta === true) {
                                                toastr["info"]('Registros Encontrados', "Sistema");
                                            }
                                            if (response.respuesta === false) {
                                                toastr["warning"]('Registros No Encontrados', "Sistema");
                                            }                                                                                    // si es exitosa la operación
                                                $('#listaContenidoSeGST').empty();
                                                $('#listaContenidoSeGST').append(response.contenido);
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