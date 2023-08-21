// id de user global
var idUser_ok = 0;
var accion_dn = 'noAccion';
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
    $("#AlertDN").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        var miselect=$("#lstAnnLectivoDN");
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
        $("#lstAnnLectivoDN").change(function ()
        {
            // LISTADO DE LAS MODALIDES
            var miselect2=$("#lstModalidadDN");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            //        
                $("#lstAnnLectivoDN option:selected").each(function () {
                    elegido=$(this).val();
                        annlectivo=$("#lstAnnLectivoDN").val();
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
        $("#lstModalidadDN").change(function () {
            $("#lstModalidadDN option:selected").each(function () {
                elegido=$(this).val();
                modalidad=$("#lstModalidadDN").val();
                // validar
                    if(modalidad == "00"){
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoDN').empty();
                        // limpiar select
                        var miselect3=$("#lstAnnLectivoDN");
                        var miselect4=$("#lstModalidad");
                        var miselect5=$("#lstDocenteNivel");
                            miselect4.empty();
                            miselect5.empty();
                    }else{
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoDN').empty();
                        // LISTAR PARA EL SERVIICO EDUCATIVO - COMPONENTES DE ESTUDIOS.
                        var miselect4=$("#lstDocenteNivel");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect4.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar_nombre_personal.php",
                            function(data) {
                            miselect4.empty();
                            miselect4.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect4.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }			
                        }, "json");

                        // LISTAR PARA EL SERVIICO EDUCATIVO - turno
                        var miselect5=$("#lstTurnoDN");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect5.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-turno.php",
                            function(data) {
                            miselect5.empty();
                            miselect5.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect5.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
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
        if(TextoTab == "Docente/Nivel"){
            // Borrar información de la Tabla.
                $('#listaContenidoDN').empty();
                $("#AlertDN").css("display", "none");
            // Select a 00...
                $("#lstAnnLectivoDN").val('00')
                $("#lstModalidadDN").val('00')
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
        $('#lstAnnLectivoDN').on('change', function() {
            $("#AlertDN").css("display", "none");
        });
        // Nivel o SeGST.
        $('#lstModalidadDN').on('change', function() {
            $("#AlertDN").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentanaDN").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaDN")[0].reset();
                $('#formVentanaDN').trigger("reset");
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
    $('body').on('click','#listaContenidoDN a',function (e){
        e.preventDefault();
        // Id Usuario
            Id_Editar_Eliminar = $(this).attr('href');
            accion_ok = $(this).attr('data-accion');
                // EDITAR LA ASIGNATURA
                if($(this).attr('data-accion') == 'EditarDN'){
                        // Valor de la acción
                        $('#accion_dn').val('EditarDN');
                        accion = 'EditarDN';
                        
                        // obtener el valor del id.
                        var id_ = $(this).parent().parent().children('td:eq(2)').text();
                        
                        // Llamar al archivo php para hacer la consulta y presentar los datos.
                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  { id_: id_, accion: accion},
                            function(data) {
                            // Llenar el formulario con los datos del registro seleccionado tabs-1
                            // Datos Generales
                                texto_annlectivo_dn = $("#lstAnnLectivoDN option:selected").html();
                                codigo_annlectivo_dn = $("#lstAnnLectivoDN option:selected").val();
                                texto_modalidad_dn = $("#lstModalidadDN option:selected").html();
                                codigo_modalidad_dn = $("#lstModalidadDN option:selected").val();
                                //
                                $("#TextoAnnLectivoDN").text(texto_annlectivo_dn);
                                $("#TextoModalidadesDN").text(texto_modalidad_dn);
                                //
                                listar_CodigoDN(data[0].codigo_se);
                                //
                                // Abrir ventana modal.
                                $('#VentanaDN').modal("show");
                                $("label[for=LblTituloDN]").text("Docente/Nivel | Actualizar");
                                // reestablecer el accion a=ActulizarAsignatura.
                                accion_dn = "ActualizarDN";
                            },"json");
                }
                // ELIMINAR REGISTRO ASIGNATURA.
                if($(this).attr('data-accion') == 'EliminarDN'){
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
                                        accion_buscar: 'EliminarDN', id_: Id_Editar_Eliminar,
                                        },                     
                                success: function(response) {                     
                                        if (response.respuesta === true) {                     		
                                            toastr["info"]('Registros Eliminados', "Sistema");
                                            // Asignamos valor a la variable acción
                                                $('#accion_dn').val('BuscarDN');
                                                accion = 'BuscarDN';
                                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                                    function(response) {
                                                        if (response.respuesta === true) {
                                                            toastr["info"]('Registros Encontrados', "Sistema");
                                                        }
                                                        if (response.respuesta === false) {
                                                            toastr["warning"]('Registros No Encontrados', "Sistema");
                                                        }                                                                                    // si es exitosa la operación
                                                            $('#listaContenidoDN').empty();
                                                            $('#listaContenidoDN').append(response.contenido);
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
	$("#checkBoxAllDN").on("change", function () {
		$("#listadoContenidoDN tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoDN tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoDN tbody input[type='checkbox'].case").length == $("#listadoContenidoDN tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllDN").prop("checked", true);
        } else {
            $("#checkBoxAllDN").prop("checked", false);
        }
    });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
    //
    //  funcion click
    //
        $('#goBuscarDN').on('click',function(){
            // Asignamos valor a la variable acción
                codigo_annlectivo = $("#lstAnnLectivoDN").val();
                codigo_modalidad = $("#lstModalidadDN").val();
                accion = 'BuscarDN';
                //
                //  CONDICONAR EL SELECT ...
                //
                if(codigo_annlectivo == "00"){
                    $("#AlertDN").css("display", "block");
                    $("#TextoAlertDN").text("Debe Seleccionar Año Lectivo para Buscar.");
                    return;
                }
                if(codigo_modalidad == "00"){
                    $("#AlertDN").css("display", "block");
                    $("#TextoAlertDN").text("Debe Seleccionar la Modalidad para Buscar.");
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
                        $('#listaContenidoDN').empty();
                        $('#listaContenidoDN').append(response.contenido);
                    },"json");
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goGuardarDN').on('click', function(){
            codigo_annlectivo = $("#lstAnnLectivoDN").val();
            codigo_modalidad = $("#lstModalidadDN").val();
            codigo_dn = $("#lstDocenteNivel").val();
            codigo_turno = $("#lstTurnoDN").val();
            accion = 'GuardarDN';
                $('#accion_dn').val('GuardarDN');
            //
            //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
            //
            if(codigo_annlectivo == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Año Lectivo para Guardar un Nivel.");
                return;
            }
            if(codigo_modalidad == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Nivel para Guardar.");
                return;
            }
            if(codigo_turno == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Turno para Guardar.");
                return;
            }
            if(codigo_dn == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Docente para Guardar.");
                return;
            }
            // enviar form
                $('#FormDN').submit();
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goActualizarDN').on('click', function(){
            codigo_annlectivo = $("#lstAnnLectivoDN").val();
            codigo_modalidad = $("#lstModalidadDN").val();
            codigo_servicio_educativo = $("#formVentanaDN select[name=lstDN]").val();
            accion = 'ActualizarDN';
                $('#accion_dn').val('ActualizarDN');
            //
            //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
            //
            if(codigo_annlectivo == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Año Lectivo para Guardar un Nivel.");
                return;
            }
            if(codigo_modalidad == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Nivel para Guardar.");
                return;
            }
            if(codigo_servicio_educativo == "00"){
                $("#AlertDN").css("display", "block");
                $("#TextoAlertDN").text("Debe Seleccionar un Servicio Educativo para Guardar.");
                return;
            }
            // enviar form
                $('#formVentanaDN').submit();
        });
        //	  
        // Validar Formulario para la buscque de registro segun el criterio.   
        // ACTUALIZAR
        $('#formVentanaDN').validate({
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
                    var str = $('#formVentanaDN').serialize();
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
                                $('#VentanaDN').modal("hide");
                                // Reiniciar los valores del Formulario.
                                    $("#formVentanaDN").trigger("reset");
                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                    $('#accion_dn').val('BuscarDN');
                                    accion = 'BuscarDN';
                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                        function(response) {
                                            if (response.respuesta === true) {
                                                toastr["info"]('Registros Encontrados', "Sistema");
                                            }
                                            if (response.respuesta === false) {
                                                toastr["warning"]('Registros No Encontrados', "Sistema");
                                            }                                                                                    // si es exitosa la operación
                                                $('#listaContenidoDN').empty();
                                                $('#listaContenidoDN').append(response.contenido);
                                        },"json");
                                }               
                        },
                    });
                },
        });
        // PARA GUARDAR O ACTUALIZAR.
        $('#FormDN').validate({
            ignore:"",
            rules:{
                    lstAnnLectivoDN: {required: true},
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
                    var str = $('#FormDN').serialize();
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
                                    $('#accion_dn').val('BuscarDN');
                                    accion = 'BuscarDN';
                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                        function(response) {
                                            if (response.respuesta === true) {
                                                toastr["info"]('Registros Encontrados', "Sistema");
                                            }
                                            if (response.respuesta === false) {
                                                toastr["warning"]('Registros No Encontrados', "Sistema");
                                            }                                                                                    // si es exitosa la operación
                                                $('#listaContenidoDN').empty();
                                                $('#listaContenidoDN').append(response.contenido);
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
function listar_CodigoDN(CodigoDN){
    var miselect=$("#formVentanaDN select[name=lstDocenteNivel]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_nombre_personal.php",
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