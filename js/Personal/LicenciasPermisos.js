// id de user global
var idUser_ok = 0;
var accion_aag  = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_personal = "";
var msjEtiqueta = "";
var codigo_tipo_contratacion = "";
var miselect2 = "";
var miselect3 = "";

// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
    // Escribir la fecha actual.
    var now = new Date();
                
    var day = ("0" + now.getDate()).slice(-2);
    var month = ("0" + (now.getMonth() + 1)).slice(-2);
    
    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
        $('#FechaTipoLicencia').val(today);
//
//  INVISILBLE TODOS LOS MENSAJES.
    //  
    $("#AlertLicenciasPermisos").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        //
        // CUANDO cambien
        //
        $("#lstPersonal").change(function ()
        {
            accion = "BuscarContratacion";
            // LISTADO DE LAS MODALIDES
                miselect2=$("#lstTipoContratacion");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            //        
                $("#lstPersonal option:selected").each(function () {
                        codigo_personal=$("#lstPersonal").val();
                        $.post("php_libs/soporte/Personal/LicenciasPermisos.php", { accion: accion, codigo_personal: codigo_personal },
                        function(data){
                                miselect2.empty();
                                for (var i=0; i<data.length; i++) {
                                    if(i == 0){
                                        miselect2.append('<option value="' + data[i].codigo_tipo_contratacion + data[i].codigo_turno + '" selected>' + data[i].nombre_contratacion + ' - ' + data[i].nombre_turno + '</option>');
                                    }else{
                                        miselect2.append('<option value="' + data[i].codigo_tipo_contratacion + data[i].codigo_turno + '">' + data[i].nombre_contratacion + ' - ' + data[i].nombre_turno + '</option>');
                                    }                                    
                                }
                                //
                                FechaInicioFin();
                    }, "json");		
                });
                // LLamada alcular tiempo a 12 horas, tiempo transcurrido
                    callerFun();
                // focus().
                    $("#FechaTipoLicencia").focus();
            }); // opcion del change...
        //
        // CUANDO cambien...
        //
        $("#lstTipoContratacion").change(function ()
        {
            // Fecha Inicio Fin.
                FechaInicioFin();
            // LLamada alcular tiempo a 12 horas
                callerFun();
            // focus().
                $("#FechaTipoLicencia").focus();
        }); // opcion del change.
        ////
        ////
        //  CUANDO EL CHECK SE ACTIVE O DESACTIVE.
        //
            if($('input[name="CheckDias"]:checked'))
            {
                // checked
                    //$("#DiasLicenciaPermiso").attr("disabled","false");
            }else{
                // unchecked
                    //$("#DiasLicenciaPermiso").attr("disabled","true");
            }
            var check;
                $("#CheckDias").on("click", function(){
                    check = $("#CheckDias").is(":checked");
                    if(check) {
                        $("#DiasLicenciaPermiso").prop("disabled",false);
                        $("#DiasLicenciaPermiso").focus();
                    } else {
                        $("#DiasLicenciaPermiso").prop("disabled",true);
                        $("#lstPersonal").focus();
                        $("#DiasLicenciaPermiso").val("");
                    }
                }); 
        ////////////////////////////////////////////////////////////////////////////
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //////////////////////////////////////////////////////////////////////////
    $("#NavNavLicenciasPermisos ul.nav > li > a").on("click", function () {
        TextoTab = $(this).text();
        //alert(TextoTab);
        if(TextoTab == "Licencias y Permisos"){
            // Borrar información de la Tabla.
                $('#listaContenidoLicenciasPermiso').empty();
                $("#AlertLicenciasPermisos").css("display", "none");
            // Select a 00...
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
        $('#lstPersonal').on('change', function() {
            $("#AlertLicenciasPermisos").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentanaLicenciasPermisos").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaLicenciasPermisos")[0].reset();
                $('#formVentanaLicenciasPermisos').trigger("reset");
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
    $('body').on('click','#listaContenidoLicenciasPermiso a',function (e){
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
	$("#checkBoxAllLicenciasPermiso").on("change", function () {
		$("#listadoContenidoAAG tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#checkBoxAllLicenciasPermiso tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#checkBoxAllLicenciasPermiso tbody input[type='checkbox'].case").length == $("#checkBoxAllLicenciasPermiso tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllLicenciasPermiso").prop("checked", true);
        } else {
            $("#checkBoxAllLicenciasPermiso").prop("checked", false);
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
        $('#goGuardarLicenciasPermisos').on('click', function(){
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
            if(codigo_asignatura == "00"){
                $("#AlertAAG").css("display", "block");
                $("#TextoAlertAAG").text("Debe Seleccionar una Asignatura.");
                return;
            }
            // enviar form
                $('#FormAAG').submit();
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* ACTUALIZAR DATOS DE LA ASIGNATURA #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goActualizarLicenciasPermisos').on('click', function(){
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
        $('#formVentanaLicenciasPermisos').validate({
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
        $('#FormLicenciasPermisos').validate({
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
// TODAS LAS TABLAS VAN HA ESTAR EN .*******************
// FUNCION FECHA INICIO Y FIN.
////////////////////////////////////////////////////////////
function FechaInicioFin() {
    return new Promise((resolve,reject)=>{
        accion = "BuscarContratacion";
        codigo_personal = $("#lstPersonal").val();
        codigo_tipo_contratacion = $('#lstTipoContratacion option:selected').val();
        $.post("php_libs/soporte/Personal/LicenciasPermisos.php", { codigo_personal: codigo_personal, accion: accion, codigo_contratacion: codigo_tipo_contratacion},
            function(data){
                for (var i=0; i<data.length; i++) {
                    if (data[i].codigo_tipo_contratacion+data[i].codigo_turno == codigo_tipo_contratacion) {
                            $('#HoraDesde').val(data[i].horario_inicio);
                            $('#HoraHasta').val(data[i].horario_fin);
                           // console.log(data[i].codigo_tipo_contratacion+ " " + data[i].codigo_turno);
                           // console.log(data[i].horario_inicio + " " + data[i].horario_fin);
                    }
                }
                resolve();
            }, "json");			
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
async function callerFun(){
    console.log("Llamada!!");
    // esperar que termine la funcion FEchaInicio
        await FechaInicioFin();
        console.log("Después que termine Carga de LstTipoContratación");
    // Llamar TipoLicencia Permiso.
        TipoLicenciaPermiso();
    // Llamada tiempo 12 y 14.
        calcular_tiempo_12_24();
    // Calcular tiempo.
        calcular_tiempo();
}
function TipoLicenciaPermiso() {
    // REVISAR
        miselect3=$("#lstTipoLicencia");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect3.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    //
        $.post("includes/Personal/Catalogos/TipoLicenciaPermiso.php",
            function(data) {
                miselect3.empty();
                for (var i=0; i<data.length; i++) {
                    if(i == 0){
                        miselect3.append('<option value="' + data[i].codigo + '" selected>' +   data[i].descripcion + '</option>');
                    }else{
                        miselect3.append('<option value="' + data[i].codigo + '">' +   data[i].descripcion + '</option>');
                    }
                }
        }, "json");
    
}