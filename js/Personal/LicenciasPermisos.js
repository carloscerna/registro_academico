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
// Inicialización de fechas predeterminadas
    const now = new Date();
    const today = now.toISOString().split('T')[0];
    const todayNow = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-01`;
    const todayInicio = `${now.getFullYear()}-01-01`;

    $('#FechaTipoLicencia').val(today);
    //
    //  INVISILBLE TODOS LOS MENSAJES.
    //  
 // Ocultar alertas iniciales
    $("#AlertLicenciasPermisos, #AlertReportes").hide();
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
        //
        // CUANDO cambien...
        //
        $("#lstTipoLicencia").change(function ()
        {
            // BuscarLicenciasPermisos
                BuscarLicenciasPermisos();
            // focus().
                $("#FechaTipoLicencia").focus();
        }); // opcion del change.
        ////
        ////
        //  CUANDO EL CHECK SE ACTIVE O DESACTIVE.
        //
            var check;
  // Habilitar/Deshabilitar campo de días según checkbox
    $("#CheckDias").on("change", function () {
        const esIncapacidad = $(this).is(":checked");
        $("#DiasLicenciaPermiso").prop("disabled", !esIncapacidad);
        if (esIncapacidad) {
            $("#DiasLicenciaPermiso").focus();
        } else {
            $("#DiasLicenciaPermiso").val("1");
        }
    });
        ////////////////////////////////////////////////////////////////////////////
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //////////////////////////////////////////////////////////////////////////
    $("#NavLicenciasPermisos ul.nav > li > a").on("click", function () {
        TextoTab = $(this).text();
        if(TextoTab == "Licencias y Permisos"){
            // Borrar información de la Tabla.
                $('#listaContenidoLicenciasPermiso').empty();
                $("#AlertLicenciasPermisos").css("display", "none");
        }
        if(TextoTab == "Reportes"){
            // Borrar información de la Tabla.
                $("#AlertReportes").css("display", "none");
            // Actualizar Fecha.
                $('#FechaAñoLectivo').val(today_inicio);
                $('#FechaLicenciaDesde').val(today_now);
                $('#FechaLicenciaHasta').val(today);
            //
            var miselect=$("#lstTipoContratacionReporte");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			//
			$.post("includes/Personal/Catalogos/Contratacion.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
                        if(i == 0){
                            miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                        }else{
                            miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
						
					}
			}, "json");
            //
            var miselect1=$("#lstTurnoReporte");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect1.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			//
			$.post("includes/Personal/Catalogos/Turno.php",
				function(data) {
					miselect1.empty();
					for (var i=0; i<data.length; i++) {
                        if(i == 0){
                            miselect1.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                        }else{
                            miselect1.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
						
					}
			}, "json");
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
                if($(this).attr('data-accion') == 'EditarLicenciaPermiso'){
                        // Valor de la acción
                        accion = 'EditarLicenciasPermisos';
                        // obtener el valor del id.
                        var id_ = $(this).parent().parent().children('td:eq(2)').text();
                        // Llamar al archivo php para hacer la consulta y presentar los datos.
                        $.post("php_libs/soporte/Personal/LicenciasPermisos.php",  { id_: id_, accion: accion},
                            function(data) {
                            // Llenar el formulario con los datos del registro seleccionado tabs-1
                            // Datos Generales
                                /*texto_annlectivo_aag = $("#lstAnnLectivoAAG option:selected").html();
                                codigo_annlectivo_aag = $("#lstAnnLectivoAAG option:selected").val();
                                texto_modalidad_aag = $("#lstModalidadAAG option:selected").html();
                                codigo_modalidad_aag = $("#lstModalidadAAG option:selected").val();
                                */
                                //
                                //$("#TextoAnnLectivoAAG").text(texto_annlectivo_aag);
                                //$("#TextoModalidadesAAG").text(texto_modalidad_aag);
                                //
                                //listar_CodigoAAG(data[0].codigo_docente);
                                //listar_CodigoTurnoAAG(data[0].codigo_turno);
                                //
                                // Abrir ventana modal.
                                $('#VentanaLicenciasPermisos').modal("show");
                                $("label[for=LblTituloLicenciasPermisos]").text("Licencias | Actualizar");
                                // reestablecer el accion a=ActulizarAsignatura.
                                accion = "ActualizarLicenciasPermisos";
                            },"json");
                }
                // ELIMINAR REGISTRO ASIGNATURA.
                if($(this).attr('data-accion') == 'EliminarLicenciaPermiso'){
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
                                url:"php_libs/soporte/Personal/LicenciasPermisos.php",
                                data: {                     
                                        accion: 'EliminarLicenciaPermiso', id_: Id_Editar_Eliminar,
                                        },                     
                                success: function(response) {                     
                                        if (response.respuesta === true) {                     		
                                            toastr["info"]('Registros Eliminados', "Sistema");
                                        // BuscarLicenciasPermisos
                                            BuscarLicenciasPermisos();
                                        // focus().
                                            $("#FechaTipoLicencia").focus();

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
		$("#listadoContenidoLicenciasPermiso tbody input[type='checkbox'].case").prop("checked", this.checked);
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
        //////////////////////////////////////////////////////////////////////////////////
        /* VER #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goGuardarLicenciaPermiso').on('click', function(){
            // Asignamos valor a la variable acción
            codigo_personal = $("#lstPersonal").val();
            accion = 'GuardarLicenciasPermisos';
            // DESACTIVAR MENSAJE
            $("#AlertLicenciasPermisos").css("display", "none");
            //
            //  CONDICONAR EL SELECT ...
            //
            if(codigo_personal == "00"){
                $("#AlertLicenciasPermisos").css("display", "block");
                $("#TextoAlertLicenciasPermisos").text("Debe Seleccionar un nombre de Docente o Personal Administrativo.");
                    return;
            }
            // enviar form
                $('#FormLicenciasPermisos').submit();
        });
        //////////////////////////////////////////////////////////////////////////////////
        /* ACTUALIZAR DATOS DE LA ASIGNATURA #CONTROLES CREADOS */
        //////////////////////////////////////////////////////////////////////////////////
        $('#goActualizarLicenciasPermisos').on('click', function(){
            // Asignamos valor a la variable acción
            codigo_personal = $("#lstPersonal").val();
            accion = 'GuardarLicenciasPermisos';
            // DESACTIVAR MENSAJE
            $("#AlertLicenciasPermisos").css("display", "none");
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
                    var str = $('#formVentanaLicenciasPermisos').serialize();
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
     // Envío del formulario principal mediante AJAX
    $('#FormLicenciasPermisos').validate({
        ignore: "",
        rules: {
            lstPersonal: { required: true }
        },
        submitHandler: function (form) {
            const formData = $(form).serialize();
            const diasIncapacidad = $("#CheckDias").is(":checked") ? $("#DiasLicenciaPermiso").val() : 1;
            const accion = 'GuardarLicenciasPermisos';

            $.ajax({
                type: "POST",
                url: "php_libs/soporte/Personal/LicenciasPermisos.php",
                data: `${formData}&accion=${accion}&DiasIncapacidad=${diasIncapacidad}&id=${Math.random()}`,
                dataType: "json",
                success: function (response) {
                    if (response.respuesta === false) {
                        toastr.error(response.mensaje || "Error al procesar la solicitud", "Sistema");
                    } else {
                        toastr.success(response.mensaje || "Registro guardado correctamente", "Sistema");
                        BuscarLicenciasPermisos();
                        $("#FechaTipoLicencia").focus();
                    }
                },
                error: function () {
                    toastr.error("Ocurrió un error en la comunicación con el servidor.", "Sistema");
                }
            });
        }
    });
	// Información dependiendo del nombres para Imprimir..
        $("#goImprimirLicenciaPermiso").on('click',function () {
            var fecha = $('#FechaTipoLicencia').val();
            var codigo_personal = $('#lstPersonal').val();
            var codigo_contratacion = $('#lstTipoContratacion').val();
            
            // construir la variable con el url.
            varenviar = "/registro_academico/php_libs/reportes/Personal/LicenciasPermisosDetalle.php?&fecha=" + fecha + "&codigo_contratacion=" + codigo_contratacion + "&codigo_personal=" + codigo_personal;
            // Ejecutar la función
            AbrirVentana(varenviar);                                
        });
        // Información dependiendo del nombres para Imprimir..
        $("#goImprimirLicenciasPermisos").on('click',function () {
            var fecha = $('#FechaAñoLectivo').val();
            var fecha_desde = $('#FechaLicenciaDesde').val();
            var fecha_hasta = $('#FechaLicenciaHasta').val();
            var codigo_turno = $('#lstTurnoReporte').val();
            var codigo_contratacion = $('#lstTipoContratacionReporte').val();
            
            // construir la variable con el url.
            varenviar = "/registro_academico/php_libs/reportes/Personal/LicenciasPermisos.php?&fecha_inicio=" + fecha + 
                        "&fecha_desde=" + fecha_desde + "&fecha_hasta=" + fecha_hasta + "&codigo_turno=" + codigo_turno +
                        "&codigo_contratacion=" + codigo_contratacion;
            // Ejecutar la función
            AbrirVentana(varenviar);                                
        });                    
        // Información dependiendo del nombres para Imprimir..
        $("#goImprimirLL").on('click',function () {
            var fecha = $('#FechaAñoLectivo').val();
            var fecha_desde = $('#FechaLicenciaDesde').val();
            var fecha_hasta = $('#FechaLicenciaHasta').val();
            var codigo_turno = $('#lstTurnoReporte').val();
            var codigo_contratacion = $('#lstTipoContratacionReporte').val();
            
            // construir la variable con el url.
            varenviar = "/registro_academico/php_libs/reportes/Personal/LlegadasTardes.php?&fecha_inicio=" + fecha + 
                        "&fecha_desde=" + fecha_desde + "&fecha_hasta=" + fecha_hasta + "&codigo_turno=" + codigo_turno +
                        "&codigo_contratacion=" + codigo_contratacion;
            // Ejecutar la función
            AbrirVentana(varenviar);                                
        });   
        
        var tablaLicencias;

$(document).ready(function() {

    // 1. Contador de Checkboxes Seleccionados para Borrado Masivo
    $(document).on('change', '.case, #checkBoxAllLicenciasPermiso', function() {
        if (this.id === 'checkBoxAllLicenciasPermiso') {
            $('.case').prop('checked', this.checked);
        }
        
        var totalSeleccionados = $('.case:checked').length;
        $('#badgeSeleccionados').text(totalSeleccionados);
    });

    // 2. Acción de Borrado Masivo (Eliminar Varios Registros)
    $('#goEliminarLicenciasPermiso').click(function() {
        var seleccionados = [];
        $('.case:checked').each(function() {
            seleccionados.push($(this).attr('id').replace('chk', ''));
        });

        if (seleccionados.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Atención',
                text: 'Por favor, selecciona al menos un registro para eliminar.'
            });
            return;
        }

        Swal.fire({
            title: '¿Confirmas la eliminación?',
            text: 'Se eliminarán ' + seleccionados.length + ' registros seleccionados. Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Iterar o enviar array de IDs al servidor
                var promesas = seleccionados.map(function(id) {
                    return $.post("php_libs/soporte/Personal/LicenciasPermisos.php", {
                        accion: 'EliminarLicenciaPermiso',
                        id_: id
                    });
                });

                Promise.all(promesas).then(function() {
                    Swal.fire('¡Eliminados!', 'Los registros se han eliminado con éxito.', 'success');
                    $('#checkBoxAllLicenciasPermiso').prop('checked', false);
                    $('#badgeSeleccionados').text('0');
                    // Recargar datos de la tabla
                    if (typeof buscarLicencias === 'function') {
                        buscarLicencias();
                    }
                }).catch(function(error) {
                    Swal.fire('Error', 'Ocurrió un inconveniente al eliminar los registros.', 'error');
                });
            }
        });
    });
});




// ==========================================
// 1. CARGAR DATOS EN EL MODAL PARA EDITAR
// ==========================================
$(document).on('click', '.btnEditarLicencia', function() {
    // Obtener el ID guardado en el atributo data-id del botón
    var idRegistro = $(this).data('id');

    // Opción A: Cargar directamente desde los atributos de la fila de la tabla
    var fila = $(this).closest('tr');
    var fecha = fila.find('.col-fecha').text().trim();
    var horaInicio = fila.find('.col-hora-inicio').text().trim();
    var horaFin = fila.find('.col-hora-fin').text().trim();
    var dias = fila.find('.col-dias').text().trim();
    var horas = fila.find('.col-horas').text().trim();
    var minutos = fila.find('.col-minutos').text().trim();
    var observacion = $(this).data('observacion') || '';

    // Asignar los valores a los campos de la ventana modal
    $('#id_licencia_permiso_modal').val(idRegistro);
    $('#IdHorarios').val(idRegistro);
    $('#FechaInicio').val(fecha);
    $('#ModalHoraDesde').val(horaInicio);
    $('#ModalHoraHasta').val(horaFin);
    $('#ModalDia').val(dias);
    $('#ModalHora').val(horas);
    $('#ModalMinutos').val(minutos);
    $('#ModalObservacion').val(observacion);

    // Abrir la ventana modal programáticamente
    $('#VentanaLicenciasPermisos').modal('show');
});

// ==========================================
// 2. CORREGIR EL CIERRE DE LA VENTANA MODAL
// ==========================================
// Evento para cerrar la modal con el botón de cancelar, la 'X' o por código
$(document).on('click', '[data-dismiss="modal"], #VentanaLicenciasPermisos1', function() {
    $('#VentanaLicenciasPermisos').modal('hide');
});

}); // FIN DEL FUNCTION.


// Función auxiliar para inicializar o reiniciar DataTables
function inicializarTablaDataTables() {
    if ($.fn.DataTable.isDataTable('#listadoContenidoLicenciasPermiso')) {
        $('#listadoContenidoLicenciasPermiso').DataTable().destroy();
    }

    tablaLicencias = $('#listadoContenidoLicenciasPermiso').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        pageLength: 10,
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Deshabilitar orden en checkbox y acciones
        ]
    });
}

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
// TODAS LAS TABLAS VAN HA ESTAR EN personal licencias.*******************
// FUNCION LISTAR TABLA personal
////////////////////////////////////////////////////////////
async function callerFun(){
    console.log("Llamada!!");
    // esperar que termine la funcion FEchaInicio
        await FechaInicioFin();
        console.log("Después que termine Carga de LstTipoContratación");
    // Llamar TipoLicencia Permiso.
        await TipoLicenciaPermiso();
        console.log("Después que termine Carga de LstTipoLicenciaPermiso");
    // BuscarLicenciasPermisos
        BuscarLicenciasPermisos();
    // Llamada tiempo 12 y 14.
        calcular_tiempo_12_24();
    // Calcular tiempo.
        calcular_tiempo();

}
function TipoLicenciaPermiso() {
    return new Promise((resolve,reject)=>{
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
                resolve();
        }, "json");
    });
}

/**
 * Consulta y actualiza el listado de permisos en la tabla
 */
function BuscarLicenciasPermisos() {
    const codigoPersonal = $("#lstPersonal").val();
    const codigoContratacion = $('#lstTipoContratacion option:selected').val();
    const codigoLicencia = $('#lstTipoLicencia option:selected').val();
    const fecha = $("#FechaTipoLicencia").val();

    if (!codigoPersonal || codigoPersonal === "00") return;

    $.ajax({
        type: "POST",
        url: "php_libs/soporte/Personal/LicenciasPermisos.php",
        data: {
            accion: "BuscarLicenciasPermisos",
            codigo_personal: codigoPersonal,
            codigo_contratacion: codigoContratacion,
            codigo_licencia: codigoLicencia,
            fecha: fecha
        },
        dataType: "json",
        success: function (data) {
            if (Array.isArray(data) && data.length >= 2) {
                $('#listaContenidoLicenciasPermiso').html(data[0]);
                $("#SpanDisponible").text(data[1]["Disponible"] || 0);
                $("#SpanUtilizado").text(data[1]["Utilizado"] || 0);
                $("#SpanDiasLicencia").text(data[1]["DiasLicencia"] || 0);
            }
        }
    });
}


function AbrirVentana(url)
{
    window.open(url, '_blank');
        return false;
}