// id de user global
var idUser_ok = 0;
var accion_aag  = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo = "";
var codigo_modalidad = "";
var msjEtiqueta = "";
var codigo_grado_se = "";
var codigo_asignatura = "";
var codigo_servicio_educativo = "";
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
    //  
    $("#AlertAAG").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        var miselect=$("#lstAnnLectivoAAG");
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
        $("#lstAnnLectivoAAG").change(function ()
        {
            // LISTADO DE LAS MODALIDES
            var miselect2=$("#lstModalidadAAG");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            //        
                $("#lstAnnLectivoAAG option:selected").each(function () {
                        annlectivo=$("#lstAnnLectivoAAG").val();
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
        $("#lstModalidadAAG").change(function () {
            $("#lstModalidadAAG option:selected").each(function () {
                // limpiar select componenete del plan d estudio.
                var miselect5=$("#lstAAG");
                    miselect5.empty();
                //
                codigo_annlectivo=$("#lstAnnLectivoAAG").val();
                modalidad=$("#lstModalidadAAG").val();
                // validar
                    if(modalidad == "00"){
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoAAG').empty();
                        // limpiar select
                        var miselect3=$("#lstAnnLectivoAAG");
                        var miselect4=$("#lstModalidad");
                        var miselect5=$("#lstGradoAAG");
                        var miselect5=$("#lstAAG");
                            miselect4.empty();
                            miselect5.empty();
                            miselect6.empty();
                    }else{
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoAAG').empty();
                        // LISTAR PARA EL SERVIICO EDUCATIVO - COMPONENTES DE ESTUDIOS.
                        var miselect4=$("#lstGradoAAG");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect4.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-nombre-grado-se.php",{codigo_modalidad: modalidad, codigo_annlectivo: codigo_annlectivo},
                            function(data) {
                            miselect4.empty();
                            miselect4.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect4.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }			
                        }, "json");
                    }
            });
        });
        // CUANDO EL VALOR DE NIVEL O GRADO - SERVICIO EDUCATIVO CAMBIE.
        $("#lstGradoAAG").change(function () {
            $("#lstGradoAAG option:selected").each(function () {
                codigo_annlectivo=$("#lstAnnLectivoAAG").val();
                modalidad=$("#lstModalidadAAG").val();
                codigo_grado_se = this.value;
                // validar
                    if(modalidad == "00"){
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoAAG').empty();
                        // limpiar select
                        var miselect3=$("#lstAnnLectivoAAG");
                        var miselect4=$("#lstModalidad");
                        var miselect5=$("#lstGradoAAG");
                        var miselect5=$("#lstAAG");
                            miselect4.empty();
                            miselect5.empty();
                            miselect6.empty();
                    }else{
                        // borrar el contenido de la Tabla.
                            $('#listaContenidoAAG').empty();
                        // LISTAR PARA EL SERVIICO EDUCATIVO - COMPONENTES DE ESTUDIOS.
                        var miselect=$("#lstAAG");
                        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                        
                        $.post("includes/cargar-nombre-asignatura.php",{codigo_modalidad: modalidad, codigo_annlectivo: codigo_annlectivo, codigo_grado_se: codigo_grado_se},
                            function(data) {
                            miselect.empty();
                            miselect.append("<option value='00'>Seleccionar...</option>");
                            for (var i=0; i<data.length; i++) {
                                miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
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
        if(TextoTab == "Asignaturas/Niveles"){
            // Borrar información de la Tabla.
                $('#listaContenidoAAG').empty();
                $("#AlertAAG").css("display", "none");
            // Select a 00...
                $("#lstAnnLectivoAAG").val('00')
                $("#lstModalidadAAG").val('00')
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
        $('#lstAnnLectivoAAG').on('change', function() {
            $("#AlertAAG").css("display", "none");
        });
        // Nivel o SeGST.
        $('#lstModalidadAAG').on('change', function() {
            $("#AlertAAG").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentanaAAG").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaAAG")[0].reset();
                $('#formVentanaAAG').trigger("reset");
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
    $('body').on('click','#listaContenidoAAG a',function (e){
        e.preventDefault();
        // Id Usuario
            Id_Editar_Eliminar = $(this).attr('href');
            accion_ok = $(this).attr('data-accion');
                // EDITAR LA ASIGNATURA
                if($(this).attr('data-accion') == 'EditarAAG'){
                        // Valor de la acción
                        $('#accion_aag').val('EditarAAG');
                        accion = 'EditarAAG';
                        
                        // obtener el valor del id.
                        var id_ = $(this).parent().parent().children('td:eq(2)').text();
                        
                        // Llamar al archivo php para hacer la consulta y presentar los datos.
                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  { id_: id_, accion: accion},
                            function(data) {
                            // Llenar el formulario con los datos del registro seleccionado tabs-1
                            // Datos Generales
                                texto_annlectivo_aag = $("#lstAnnLectivoAAG option:selected").html();
                                codigo_annlectivo_aag = $("#lstAnnLectivoAAG option:selected").val();
                                texto_modalidad_aag = $("#lstModalidadAAG option:selected").html();
                                codigo_modalidad_aag = $("#lstModalidadAAG option:selected").val();
                                //
                                $("#TextoAnnLectivoAAG").text(texto_annlectivo_aag);
                                $("#TextoModalidadesAAG").text(texto_modalidad_aag);
                                //
                                listar_CodigoAAG(data[0].codigo_docente);
                                listar_CodigoTurnoAAG(data[0].codigo_turno);
                                //
                                // Abrir ventana modal.
                                $('#VentanaAAG').modal("show");
                                $("label[for=LblTituloAAG]").text("Docente/Nivel | Actualizar");
                                // reestablecer el accion a=ActulizarAsignatura.
                                accion_aag = "ActualizarAAG";
                            },"json");
                }
                // ELIMINAR REGISTRO ASIGNATURA.
                if($(this).attr('data-accion') == 'EliminarAAG'){
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
                                        accion_buscar: 'EliminarAAG', id_: Id_Editar_Eliminar,
                                        },                     
                                success: function(response) {                     
                                        if (response.respuesta === true) {                     		
                                            toastr["info"]('Registros Eliminados', "Sistema");
                                            // Asignamos valor a la variable acción
                                                $('#accion_aag').val('BuscarAAG');
                                                accion = 'BuscarAAG';
                                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad, codigo_grado_se: codigo_grado_se},
                                                    function(response) {
                                                        if (response.respuesta === true) {
                                                            toastr["info"]('Registros Encontrados', "Sistema");
                                                        }
                                                        if (response.respuesta === false) {
                                                            toastr["warning"]('Registros No Encontrados', "Sistema");
                                                        }                                                                                    // si es exitosa la operación
                                                            $('#listaContenidoAAG').empty();
                                                            $('#listaContenidoAAG').append(response.contenido);
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
	$("#checkBoxAllAAG").on("change", function () {
		$("#listadoContenidoAAG tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoAAG tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoAAG tbody input[type='checkbox'].case").length == $("#listadoContenidoAAG tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllAAG").prop("checked", true);
        } else {
            $("#checkBoxAllAAG").prop("checked", false);
        }
    });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
    //
    //  funcion click
    //
        $('#goBuscarAAG').on('click',function(){
            // Asignamos valor a la variable acción
                codigo_annlectivo = $("#lstAnnLectivoAAG").val();
                codigo_modalidad = $("#lstModalidadAAG").val();
                codigo_grado_se = $("#lstGradoAAG").val();
                accion = 'BuscarAAG';
                // DESACTIVAR MENSAJE
                    $("#AlertAAG").css("display", "none");
                //
                //  CONDICONAR EL SELECT ...
                //
                if(codigo_annlectivo == "00"){
                    $("#AlertAAG").css("display", "block");
                    $("#TextoAlertAAG").text("Debe Seleccionar Año Lectivo para Buscar.");
                    return;
                }
                if(codigo_modalidad == "00"){
                    $("#AlertAAG").css("display", "block");
                    $("#TextoAlertAAG").text("Debe Seleccionar la Modalidad para Buscar.");
                    return;
                }
                if(codigo_grado_se == "00"){
                    $("#AlertAAG").css("display", "block");
                    $("#TextoAlertAAG").text("Debe Seleccionar un Grado para Buscar.");
                    return;
                }
                // Llamar al archivo php para hacer la consulta y presentar los datos.
                $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                    {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad, codigo_grado_se: codigo_grado_se},
                    function(response) {
                    if (response.respuesta === true) {
                        toastr["info"]('Registros Encontrados', "Sistema");
                    }
                    if (response.respuesta === false) {
                        toastr["error"]('Registros No Encontrados', "Sistema");
                    }                                                                                    // si es exitosa la operación
                        $('#listaContenidoAAG').empty();
                        $('#listaContenidoAAG').append(response.contenido);
                    },"json");
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goGuardarAAG').on('click', function(){
            // Asignamos valor a la variable acción
            codigo_annlectivo = $("#lstAnnLectivoAAG").val();
            codigo_modalidad = $("#lstModalidadAAG").val();
            codigo_grado_se = $("#lstGradoAAG").val();
            codigo_asignatura = $("#lstAAG").val();
            accion = 'GuardarAAG';
            // DESACTIVAR MENSAJE
            $("#AlertAAG").css("display", "none");
            //
            //  CONDICONAR EL SELECT ...
            //
            if(codigo_annlectivo == "00"){
                $("#AlertAAG").css("display", "block");
                $("#TextoAlertAAG").text("Debe Seleccionar Año Lectivo.");
                return;
            }
            if(codigo_modalidad == "00"){
                $("#AlertAAG").css("display", "block");
                $("#TextoAlertAAG").text("Debe Seleccionar la Modalidad.");
                return;
            }
            if(codigo_grado_se == "00"){
                $("#AlertAAG").css("display", "block");
                $("#TextoAlertAAG").text("Debe Seleccionar un Grado.");
                return;
            }
            if($('#TodasLasAsignaturas').is(":checked")) {

            }else{
                if(codigo_asignatura == "00"){
                    $("#AlertAAG").css("display", "block");
                    $("#TextoAlertAAG").text("Debe Seleccionar una Asignatura.");
                    return;
                }
            }

            // enviar form
                $('#FormAAG').submit();
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* ACTUALIZAR DATOS DE LA ASIGNATURA #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goActualizarAAG').on('click', function(){
            codigo_annlectivo = $("#lstAnnLectivoAAG").val();
            codigo_modalidad = $("#lstModalidadAAG").val();
            codigo_docente = $("#formVentanaAAG select[name=lstDocenteNivel]").val();
            codigo_turno = $("#formVentanaAAG select[name=lstTurnoAAG]").val();
            accion = 'ActualizarAAG';
                $('#accion_aag').val('ActualizarAAG');
            // enviar form
                $('#formVentanaAAG').submit();
        });
        //	  
        // Validar Formulario para la buscque de registro segun el criterio.   
        // ACTUALIZAR
        $('#formVentanaAAG').validate({
            ignore:"",
            rules:{
                    lstDocenteNivel: {required: true},
                    lstTurnoAAG: {required: true},
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
                    var str = $('#formVentanaAAG').serialize();
                    //alert(str);
                ///////////////////////////////////////////////////////////////			
                // Inicio del Ajax. guarda o Actualiza los datos del Formualrio.
                ///////////////////////////////////////////////////////////////
                    $.ajax({
                        beforeSend: function(){
                            // Información de la tabla para actualizar código sirai.
                                var $objCuerpoTabla=$("#listaContenidoAAG").children().prev().parent();
                                var codigo_aa_ = []; var codigo_sirai_ = []; var orden_ = []; var codigo_asignatura_ = [];
                                var fila = 0;
                            // recorre el contenido de la tabla.
                                $objCuerpoTabla.find("tbody tr").each(function(){
                                    var codigo_aa = $(this).find('td').eq(1).html();
                                    var codigo_asignatura =$(this).find('td').eq(8).html();
                                    var codigo_sirai =$(this).find('td').eq(10).find("input[name='codigo_sirai']").val();
                                    var orden =$(this).find('td').eq(11).find("input[name='orden']").val();
                            // dar valor a las arrays.
                                codigo_asignatura_[fila]= codigo_asignatura;
                                codigo_aa_[fila]= codigo_aa;
                                    codigo_sirai_[fila]=codigo_sirai;
                                    orden_[fila]=orden;

                                    fila = fila + 1;
                            });
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
                                $('#VentanaAAG').modal("hide");
                                // Reiniciar los valores del Formulario.
                                    $("#formVentanaAAG").trigger("reset");
                                // Llamar al archivo php para hacer la consulta y presentar los datos.
                                    $('#accion_aag').val('BuscarAAG');
                                    accion = 'BuscarAAG';
                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                        function(response) {
                                            if (response.respuesta === true) {
                                                toastr["info"]('Registros Encontrados', "Sistema");
                                            }
                                            if (response.respuesta === false) {
                                                toastr["warning"]('Registros No Encontrados', "Sistema");
                                            }                                                                                    // si es exitosa la operación
                                                $('#listaContenidoAAG').empty();
                                                $('#listaContenidoAAG').append(response.contenido);
                                                //
                                                $("#AlertAAG").css("display", "none");
                                        },"json");
                                }               
                        },
                    });
                },
        });
        // PARA GUARDAR O ACTUALIZAR.
        $('#FormAAG').validate({
            ignore:"",
            rules:{
                    lstAnnLectivoAAG: {required: true},
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
                    var str = $('#FormAAG').serialize();
                    //alert(str);
                ///////////////////////////////////////////////////////////////			
                // Inicio del Ajax. guarda o Actualiza los datos del Formualrio.
                ///////////////////////////////////////////////////////////////
                    $.ajax({
                        beforeSend: function(){
                            if($('#TodasLasAsignaturas').is(":checked")) {TodasLasAsignaturas = 'yes';}else{TodasLasAsignaturas = "no"}
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url:"php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                        data:str + "&accion=" + accion + "&id=" + Math.random() + "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_modalidad=" + codigo_modalidad + "&TodasLasAsignaturas=" + TodasLasAsignaturas,
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
                                    $('#accion_aag').val('BuscarAAG');
                                    accion = 'BuscarAAG';
                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad, codigo_grado_se: codigo_grado_se},
                                        function(response) {
                                            if (response.respuesta === true) {
                                                toastr["info"]('Registros Encontrados', "Sistema");
                                            }
                                            if (response.respuesta === false) {
                                                toastr["warning"]('Registros No Encontrados', "Sistema");
                                            }                                                                                    // si es exitosa la operación
                                                $('#listaContenidoAAG').empty();
                                                $('#listaContenidoAAG').append(response.contenido);
                                                //
                                                $("#AlertAAG").css("display", "none");
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
// FUNCION LISTAR TABLA personal
////////////////////////////////////////////////////////////
function listar_CodigoAAG(CodigoAAG){
    var miselect=$("#formVentanaAAG select[name=lstDocenteNivel]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_nombre_personal.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(CodigoAAG == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
 ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN organizaciones grado-seccion-turno.*******************
// FUNCION LISTAR TABLA turno
////////////////////////////////////////////////////////////
function listar_CodigoTurnoAAG(CodigoTurnoAAG){
    var miselect=$("#formVentanaAAG select[name=lstTurnoAAG]");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_turno.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(CodigoTurnoAAG == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}