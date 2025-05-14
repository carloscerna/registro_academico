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
        // Cargar Año Lectivo primero
            cargarOpciones(miselect, "includes/cargar-ann-lectivo.php");
        //
        // CUANDO EL VALOR DE ANNLECTIVO CAMBIA.
        //
        var miselect2=$("#lstModalidadSeGST");
            // Cuando el usuario seleccione un Año Lectivo, se carga la Modalidad
            $(miselect).change(function() {
                let idAnnLectivo = $(this).val();
                cargarOpcionesDependiente(miselect2, "includes/cargar-bachillerato.php", { annlectivo: idAnnLectivo });
            });
    // Cuando el usuario seleccione una Modalidad, cargamos Grado-Sección-Turno con dos variables
        $(miselect2).change(function() {
            let idAnnLectivo = $(miselect).val();  // Año Lectivo seleccionado
            let idModalidad = $(this).val();  // Modalidad seleccionada
            cargarOpcionesMultiples("#lstgradoseccion", "includes/cargar-grado-seccion.php", { annlectivo: idAnnLectivo, modalidad: idModalidad });
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
                        // Valor de la acción
                        $('#accion_gst').val('EditarSeGST');
                        accion = 'EditarGST';
                        
                        // obtener el valor del id.
                        var id_ = $(this).parent().parent().children('td:eq(2)').text();
                        
                        // Llamar al archivo php para hacer la consulta y presentar los datos.
                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  { id_: id_, accion: accion},
                            function(data) {
                            // Llenar el formulario con los datos del registro seleccionado tabs-1
                            // Datos Generales
                                texto_annlectivo_gst = $("#lstAnnLectivoSeGST option:selected").html();
                                codigo_annlectivo_gst = $("#lstAnnLectivoSeGST option:selected").val();
                                texto_modalidad_gst = $("#lstModalidadSeGST option:selected").html();
                                codigo_modalidad_gst = $("#lstModalidadSeGST option:selected").val();
                                //
                                $("#TextoAnnLectivoSeGST").text(texto_annlectivo_gst);
                                $("#TextoModalidadesSeGST").text(texto_modalidad_gst);
                                //
                                listar_CodigoSeGST(data[0].codigo_se);
                                listar_CodigoTurnoGST(data[0].codigo_turno);
                                //
                                // Abrir ventana modal.
                                $('#VentanaSeGST').modal("show");
                                $("label[for=LblTituloSeGST]").text("Grado/Sección/Turno | Actualizar");
                                // RETORNAR EL VALOR DEL ACCION SEGUN ETIQUETA LABEL.
                                msjEtiqueta = $("label[for=LblTituloSeGST]").text();
                                if(msjEtiqueta == "Modalidad | Actualizar")
                                {
                                    accion = "ActualizarSeGST";
                                }else{
                                    accion = "GuardarSeGST";
                                }
                                // reestablecer el accion a=ActulizarAsignatura.
                                accion_gst = "ActualizarGST";
                            },"json");
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
                                        accion: 'EliminarSeGST', id_: Id_Editar_Eliminar,
                                        },                     
                                success: function(response) {                     
                                        if (response.respuesta === true) {                     		
                                            // Asignamos valor a la variable acción
                                                $('#accion_modalidad').val('BuscarSeGST');
                                                accion = 'BuscarSeGST';
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
            codigo_modalidad = $("#lstModalidadSeGST").val();
            accion = 'GuardarSeGST';
                $('#accion_gst').val('GuardarSeGST');
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
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goActualizarSeGST').on('click', function(){
            codigo_annlectivo = $("#lstAnnLectivoSeGST").val();
            codigo_modalidad = $("#lstModalidadSeGST").val();
            codigo_servicio_educativo = $("#formVentanaSeGST select[name=lstSeGST]").val();
            accion = 'ActualizarSeGST';
                $('#accion_gst').val('ActualizarSeGST');
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
            if(codigo_servicio_educativo == "00"){
                $("#AlertSeGST").css("display", "block");
                $("#TextoAlertSeGST").text("Debe Seleccionar un Servicio Educativo para Guardar.");
                return;
            }
            // enviar form
                $('#formVentanaSeGST').submit();
        });
        //	  
        // Validar Formulario para la buscque de registro segun el criterio.   
        // ACTUALIZAR
        $('#formVentanaSeGST').validate({
            ignore:"",
            rules:{
                    lstSeGST: {required: true},
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
                    var str = $('#formVentanaSeGST').serialize();
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
                        data:str + "&accion=" + accion + "&id=" + Math.random() + "&id_=" + Id_Editar_Eliminar,
                        success: function(response){
                            // Validar mensaje de error
                            if(response.respuesta == false){
                                toastr["error"](response.mensaje, "Sistema");
                            }
                            else{
                                toastr["success"](response.mensaje, "Sistema");
                                // Abrir ventana modal.
                                $('#VentanaSeGST').modal("hide");
                                // Reiniciar los valores del Formulario.
                                    $("#formVentana1SeGST").trigger("reset");
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
 ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN organizaciones grado-seccion-turno.*******************
// FUNCION LISTAR TABLA catalogo_servicio_educativo
////////////////////////////////////////////////////////////
function listar_CodigoSeGST(CodigoSeGST){
    var miselect=$("#formVentanaSeGST select[name=lstSeGST]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar-servicio-educativo.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(CodigoSeGST == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
function listar_CodigoTurnoGST(CodigoTurnoGST){
        var miselect=$("#formVentanaSeGST select[name=lstTurnoSeGST]");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/cargar-turno.php",
            function(data) {
                miselect.empty();
                for (var i=0; i<data.length; i++) {
                    if(CodigoTurnoGST == data[i].codigo){
                        miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                    }else{
                        miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                    }
                }
        }, "json");    
}