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
    // Usamos .trim() para limpiar espacios en blanco alrededor del texto del TAB
    var TextoTab = $(this).text().trim();
    if (TextoTab === "Licencias y Permisos") {
        // Borrar información de la Tabla
        $('#listaContenidoLicenciasPermiso').empty();
        $("#AlertLicenciasPermisos").css("display", "none");
    }

    if (TextoTab === "Reportes") {
        // Ocultar alertas y actualizar fechas
        $("#AlertReportes").css("display", "none");
        $('#FechaAñoLectivo').val(today_inicio);
        $('#FechaLicenciaDesde').val(today_now);
        $('#FechaLicenciaHasta').val(today);

        // --- 1. CARGA DEL SELECT: Tipo de Contratación ---
        var miselect = $("#lstTipoContratacionReporte");
        miselect.html('<option value="">Cargando...</option>');

        $.post("includes/Personal/Catalogos/Contratacion.php", function (data) {
            miselect.empty();

            // Opción por defecto
            miselect.append('<option value="">-- Seleccione Tipo de Contratación --</option>');

            // Comprobar que los datos devueltos sean un arreglo válido
            if (Array.isArray(data) && data.length > 0) {
                $.each(data, function (index, item) {
                    // Validar si las propiedades se llaman 'codigo' y 'descripcion'
                    var valCodigo = item.codigo || item.id || "";
                    var valTexto = item.descripcion || item.nombre || "";

                    miselect.append('<option value="' + valCodigo + '">' + valTexto + '</option>');
                });
            } else {
                miselect.html('<option value="">No se encontraron datos</option>');
            }
        }, "json")
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar Contratación:", textStatus, errorThrown);
            console.log("Respuesta recibida del servidor:", jqXHR.responseText);
            miselect.html('<option value="">Error al cargar opciones</option>');
        });


        // --- 2. CARGA DEL SELECT: Turno ---
        var miselect1 = $("#lstTurnoReporte");
        miselect1.html('<option value="">Cargando...</option>');

        $.post("includes/Personal/Catalogos/Turno.php", function (data) {
            miselect1.empty();

            miselect1.append('<option value="">-- Seleccione Turno --</option>');

            if (Array.isArray(data) && data.length > 0) {
                $.each(data, function (index, item) {
                    var valCodigo = item.codigo || item.id || "";
                    var valTexto = item.descripcion || item.nombre || "";

                    miselect1.append('<option value="' + valCodigo + '">' + valTexto + '</option>');
                });
            } else {
                miselect1.html('<option value="">No se encontraron datos</option>');
            }
        }, "json")
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar Turno:", textStatus, errorThrown);
            miselect1.html('<option value="">Error al cargar opciones</option>');
        });
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
    
        


$(document).ready(function () {

    // Evento al hacer clic o activar la pestaña "Licencias y Permisos"
    $('#pills-licencias-permisos-tab').on('click shown.bs.tab', function () {
        // Borrar información de la tabla y ocultar alertas
        $('#listaContenidoLicenciasPermiso').empty();
        $("#AlertLicenciasPermisos").css("display", "none");
    });

    // Evento al hacer clic o activar la pestaña "Reportes"
    $('#pills-reportes-tab').on('click shown.bs.tab', function () {
        
        // 1. Ocultar alertas y actualizar fechas de reportes
        $("#AlertReportes").css("display", "none");
        $('#FechaAñoLectivo').val(todayInicio);
        $('#FechaLicenciaDesde').val(todayNow);
        $('#FechaLicenciaHasta').val(today);

        // 2. Cargar Select: Tipo de Contratación
        var selectContratacion = $("#lstTipoContratacionReporte");
        selectContratacion.html('<option value="">Cargando...</option>');

        $.post("includes/Personal/Catalogos/Contratacion.php", function (data) {
            selectContratacion.empty();
            selectContratacion.append('<option value="">-- Seleccione Tipo de Contratación --</option>');

            if (Array.isArray(data) && data.length > 0) {
                $.each(data, function (index, item) {
                    var codigo = item.codigo || item.id || "";
                    var descripcion = item.descripcion || item.nombre || "";
                    
                    selectContratacion.append('<option value="' + codigo + '">' + descripcion + '</option>');
                });
            } else {
                selectContratacion.html('<option value="">No se encontraron registros</option>');
            }
        }, "json")
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar Contratación:", textStatus, errorThrown);
            selectContratacion.html('<option value="">Error al cargar opciones</option>');
        });

        // 3. Cargar Select: Turno
        var selectTurno = $("#lstTurnoReporte");
        selectTurno.html('<option value="">Cargando...</option>');

        $.post("includes/Personal/Catalogos/Turno.php", function (data) {
            selectTurno.empty();
            selectTurno.append('<option value="">-- Seleccione Turno --</option>');

            if (Array.isArray(data) && data.length > 0) {
                $.each(data, function (index, item) {
                    var codigo = item.codigo || item.id || "";
                    var descripcion = item.descripcion || item.nombre || "";

                    selectTurno.append('<option value="' + codigo + '">' + descripcion + '</option>');
                });
            } else {
                selectTurno.html('<option value="">No se encontraron registros</option>');
            }
        }, "json")
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("Error al cargar Turno:", textStatus, errorThrown);
            selectTurno.html('<option value="">Error al cargar opciones</option>');
        });

    });

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
                        BuscarLicenciasPermisos();
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


$(document).ready(function () {

    // =========================================================================
    // 1. FUNCIÓN PARA OBTENER EL SALDO DE LA BD Y ACTUALIZAR LA GRÁFICA
    // =========================================================================
    function consultarYActualizarGrafico() {
        // Obtener el ID del empleado seleccionado y el tipo de contratación
        var idPersonal = $('#lstPersonal').val() || $('#id_personal').val();
        var codigoContratacion = $('#lstTipoContratacion option:selected').val() || '01';
        var idTipoLicencia = $('#lstTipoLicencia option:selected').val();

        // Validar que se haya seleccionado un empleado o tipo de licencia
        if (!idPersonal) {
            return;
        }

      // Petición AJAX para obtener el saldo y actualizar la interfaz HTML
$.post("php_libs/soporte/Personal/LicenciasPermisos.php", {
    accion: 'ConsultarSaldoEmpleado',
    id_personal: idPersonal,
    id_tipo_licencia: idTipoLicencia
}, function (response) {

    if (response.respuesta) {
        // 1. Extraer variables del JSON de respuesta
        var horasPorDia = response.horas_por_dia;           // 5 u 8
        var minutosConsumidos = response.minutos_consumidos; // Total en minutos
        var diasLimite = response.dias_limite;               // Límite de días
        var codigoCargo = response.codigo_cargo;             // "02", "03", etc.

        // Desglose formateado
        var diasUtil = response.dias_consumidos;
        var horasUtil = response.horas_consumidas;
        var minutosUtil = response.minutos_consumidos_res;

        // 2. Actualizar etiquetas de texto en el HTML
        $("#SpanDiasLicencia").text(diasLimite + "d");
        $("#SpanUtilizado").text(diasUtil + "d " + horasUtil + "h " + minutosUtil + "m");

        // 3. Actualizar la etiqueta visual del tipo de jornada
        if (horasPorDia === 5) {
            $("#badgeTipoJornada")
                .removeClass("badge-info")
                .addClass("badge-primary")
                .html('<i class="fas fa-chalkboard-teacher mr-1"></i> Jornada Docente (5 hrs/día)');
        } else {
            $("#badgeTipoJornada")
                .removeClass("badge-primary")
                .addClass("badge-info")
                .html('<i class="fas fa-user-tie mr-1"></i> Jornada Administrativa (8 hrs/día)');
        }

        // 4. Calcular el porcentaje de uso y actualizar la barra de progreso (ProgressBar)
        var minutosPorDia = horasPorDia * 60;
        var minutosLimiteTotal = diasLimite * minutosPorDia;

        // Calcular porcentaje consumido (limitado al 100% para evitar desbordamiento gráfico)
        var porcentaje = 0;
        if (minutosLimiteTotal > 0) {
            porcentaje = Math.round((minutosConsumidos / minutosLimiteTotal) * 100);
        }
        var porcentajeBarra = Math.min(porcentaje, 100);

        // Actualizar el elemento ProgressBar en el HTML
        var $barra = $("#barraProgresoTiempo");
        $barra.css("width", porcentajeBarra + "%");
        $barra.attr("aria-valuenow", porcentajeBarra);
        $("#textoPorcentaje").text(porcentaje + "% Consumido");

        // Cambiar el color de la barra según el nivel de consumo
        $barra.removeClass("bg-success bg-warning bg-danger");
        if (porcentaje < 60) {
            $barra.addClass("bg-success");  // Verde
        } else if (porcentaje < 85) {
            $barra.addClass("bg-warning");  // Amarillo
        } else {
            $barra.addClass("bg-danger");   // Rojo (Agotado / Sobregirado)
        }

        // 5. Calcular tiempo disponible restante
        var minutosDisponibles = Math.max(0, minutosLimiteTotal - minutosConsumidos);
        var diasDisp = Math.floor(minutosDisponibles / minutosPorDia);
        var horasDisp = Math.floor((minutosDisponibles % minutosPorDia) / 60);

        $("#SpanDisponible").text(diasDisp + "d " + horasDisp + "h");

    } else {
        console.warn("La respuesta del servidor fue negativa:", response.mensaje);
    }

}, "json")
.fail(function (jqXHR, textStatus, errorThrown) {
    console.error("Error al procesar la petición AJAX:", textStatus, errorThrown);
});
    }

// =========================================================================
    // 2. ¿DÓNDE LLAMAMOS A LA FUNCIÓN? (DISPARADORES DE EVENTOS)
    // =========================================================================

    // A. Al cambiar la opción del combo del personal o tipo de licencia
    $(document).on('change', '#lstPersonal, #id_personal, #lstTipoLicencia', function () {
        consultarYActualizarGrafico();
    });

    // B. Al activar la pestaña "Licencias y Permisos"
    $('#pills-licencias-permisos-tab').on('click shown.bs.tab', function () {
        consultarYActualizarGrafico();
    });

    // C. Ejecución automática inicial al cargar la página por primera vez
    consultarYActualizarGrafico();

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
/**
 * Realiza la búsqueda de licencias y permisos por AJAX y renderiza la DataTable.
 */
function BuscarLicenciasPermisos() {
    const codigoPersonal = $("#lstPersonal").val();
    const codigoContratacion = $('#lstTipoContratacion option:selected').val();
    const codigoLicencia = $('#lstTipoLicencia option:selected').val();
    const fecha = $("#FechaTipoLicencia").val();

    // Validación: Si no hay personal seleccionado o es "00", se interrumpe la ejecución
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
                
                // 1. Si la tabla ya era una DataTable, destruimos la instancia previa
                if ($.fn.DataTable.isDataTable('#listadoContenidoLicenciasPermiso')) {
                    $('#listadoContenidoLicenciasPermiso').DataTable().destroy();
                }

                // 2. Inyectar las filas HTML recibidas desde PHP en el <tbody>
                $('#listaContenidoLicenciasPermiso').html(data[0]);

                // 3. Re-inicializar DataTable sobre los nuevos datos cargados
                inicializarTablaLicencias();

                // 4. Actualizar las etiquetas de saldo disponible, utilizado y total
                $("#SpanDisponible").text(data[1]["Disponible"] || 0);
                $("#SpanUtilizado").text(data[1]["Utilizado"] || 0);
                $("#SpanDiasLicencia").text(data[1]["DiasLicencia"] || 0);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error al consultar las licencias:", error);
        }
    });
}


/**
 * Renderiza gráficamente el saldo de tiempo consumido vs. disponible.
 * 
 * @param {number} minutosUtilizados - Minutos totales consumidos por el empleado.
 * @param {number} diasLimite - Días máximos de licencia permitidos al año.
 * @param {string} codigoContratacion - Código de contratación ('01', '02', '03' para 5h; otros para 8h).
 */
function consultarYActualizarGrafico() {
    var idPersonal = $('#lstPersonal').val() || $('#id_personal').val();
    var idTipoLicencia = $('#lstTipoLicencia').val() || $('#id_tipo_licencia').val();
    
    // Capturar la fecha seleccionada en el input o asignar la fecha de hoy
    var fechaLicencia = $('#FechaTipoLicencia').val();

    if (!idPersonal || !idTipoLicencia) return;

    $.post("php_libs/soporte/Personal/LicenciasPermisos.php", {
        accion: 'ConsultarSaldoEmpleado',
        id_personal: idPersonal,
        id_tipo_licencia: idTipoLicencia,
        fecha_licencia: fechaLicencia
    }, function (response) {

        if (response.respuesta) {
            var horasPorDia = response.horas_por_dia;
            var minutosConsumidos = response.minutos_consumidos;
            var diasLimite = response.dias_limite;
            var minutosLimiteTotal = response.minutos_limite_total;

            // 1. Mostrar etiquetas numéricas
            $("#SpanDiasLicencia").text(diasLimite + "d 0h 0m");
            $("#SpanUtilizado").text(response.dias_consumidos + "d " + response.horas_consumidas + "h " + response.minutos_consumidos_res + "m");

            // 2. Cálculo matemático exacto del porcentaje consumido en el año
            var porcentaje = 0;
            if (minutosLimiteTotal > 0) {
                porcentaje = Math.round((minutosConsumidos / minutosLimiteTotal) * 100);
            }

            var porcentajeAnchoBarra = Math.min(porcentaje, 100);

            // 3. Renderizar barra de progreso
            var $barra = $("#barraProgresoTiempo");
            $barra.css("width", porcentajeAnchoBarra + "%");
            $barra.attr("aria-valuenow", porcentajeAnchoBarra);
            $("#textoPorcentaje").text(porcentaje + "% Consumido en el año " + response.anio);

            // 4. Asignar colores según el consumo anual
            $barra.removeClass("bg-success bg-warning bg-danger");
            if (porcentaje < 60) {
                $barra.addClass("bg-success");  // Verde
            } else if (porcentaje < 85) {
                $barra.addClass("bg-warning");  // Amarillo
            } else {
                $barra.addClass("bg-danger");   // Rojo
            }

            // 5. Tiempo disponible restante para el año en curso
            var minutosPorDia = horasPorDia * 60;
            var minutosDisponibles = Math.max(0, minutosLimiteTotal - minutosConsumidos);
            var diasDisp = Math.floor(minutosDisponibles / minutosPorDia);
            var restoDisp = minutosDisponibles % minutosPorDia;
            var horasDisp = Math.floor(restoDisp / 60);
            var minDisp = restoDisp % 60;

            $("#SpanDisponible").text(diasDisp + "d " + horasDisp + "h " + minDisp + "m");
        }

    }, "json");
}

// Escuchar cambios también en el input de la fecha
$(document).on('change', '#FechaTipoLicencia', function () {
    consultarYActualizarGrafico();
});

// Ejemplo de prueba:
// actualizarGraficoSaldo(600, 5, '01'); // Docente (5h/día -> 5 días = 1500 min). 600 min consumidos = 40% (Verde)


// Variable global para almacenar la referencia del DataTable
var tablaLicenciasDT = null;

/**
 * Inicializa la tabla de licencias y permisos con el plugin DataTables.
 */
function inicializarTablaLicencias() {
    // Si la tabla ya fue inicializada previamente como DataTable, se destruye para permitir la recarga limpia.
    if ($.fn.DataTable.isDataTable('#listadoContenidoLicenciasPermiso')) {
        $('#listadoContenidoLicenciasPermiso').DataTable().destroy();
    }

    // Inicialización del plugin con configuración personalizada
    $('#listadoContenidoLicenciasPermiso').DataTable({
        "pageLength": 10,                 // Número de registros visibles por página
        "lengthMenu": [5, 10, 25, 50],     // Menú desplegable para seleccionar cantidad de registros
        "responsive": true,               // Hace que la tabla se adapte a pantallas pequeñas
        "autoWidth": false,               // Desactiva el cálculo de anchos para permitir el control por CSS
        "order": [[3, "desc"]],           // Ordena por defecto por la columna "Fecha" (índice 3) en orden descendente
        "columnDefs": [
            { "orderable": false, "targets": [0, 9] } // Desactiva la ordenación en el Checkbox (0) y en Acciones (9)
        ],
        "language": {                     // Configuración del idioma a español
            "processing":     "Procesando...",
            "search":         "Buscar:",
            "lengthMenu":     "Mostrar _MENU_ registros",
            "info":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty":      "Mostrando 0 a 0 de 0 registros",
            "infoFiltered":   "(filtrado de _MAX_ registros totales)",
            "zeroRecords":    "No se encontraron registros coincidentes",
            "emptyTable":     "No hay licencias ni permisos registrados",
            "paginate": {
                "first":      "Primero",
                "previous":   "Anterior",
                "next":       "Siguiente",
                "last":       "Último"
            }
        }
    });
}

/**
 * 1. Carga los datos recibidos del JSON en la Ventana Modal para editar
 * @param {number} idRegistro - ID de la licencia o permiso a consultar
 */
function CargarDatosEditar(idRegistro) {
    $.ajax({
        type: "POST",
        url: "php_libs/soporte/Personal/LicenciasPermisos.php",
        data: {
            accion: "EditarLicenciasPermisos",
            id_: idRegistro
        },
        dataType: "json",
        success: function (response) {
            if (response.respuestaOK) {
                // Obtenemos el objeto del registro (o directamente response si viene directo)
                const reg = response.registro || response;

                // Asignar valores a los campos HTML de la Ventana Modal
                $("#id_licencia_permiso_modal").val(reg.id_licencia_permiso);
                $("#IdHorarios").val(reg.id_licencia_permiso);
                $("#FechaInicio").val(reg.fecha);
                $("#ModalHoraDesde").val(reg.hora_inicio);
                $("#ModalHoraHasta").val(reg.hora_fin);
                $("#ModalDia").val(reg.dia);
                $("#ModalHora").val(reg.hora);
                $("#ModalMinutos").val(reg.minutos);
                $("#ModalObservacion").val(reg.observacion);

                // Mostrar la Ventana Modal de edición
                $("#VentanaLicenciasPermisos").modal("show");
            } else {
                alert("Error al cargar la información: " + response.mensajeError);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error en la solicitud de edición:", error);
        }
    });
}

// 2. Eventos que se ejecutan cuando el documento HTML está listo
$(document).ready(function () {

    // Evento al hacer clic en las acciones de la tabla para editar
    $(document).on("click", "a[data-accion='EditarLicenciaPermiso'], a[data-accion='EditarLicenciasPermisos']", function (e) {
        e.preventDefault();
        const idRegistro = $(this).attr("href");
        CargarDatosEditar(idRegistro);
    });

    // Evento al presionar el botón "Actualizar Cambios" dentro de la ventana modal
    $("#goGuardarModal").on("click", function () {
        ActualizarLicenciaPermiso();
    });

});

/**
 * Envía los datos actualizados de la ventana modal al backend PHP
 * e informa el resultado mediante SweetAlert2
 */
function ActualizarLicenciaPermiso() {
    // 1. Obtener los datos individuales de los campos de la modal
    const idLicencia = $("#id_licencia_permiso_modal").val();
    const fecha = $("#FechaInicio").val();
    const horaInicio = $("#ModalHoraDesde").val();
    const horaFin = $("#ModalHoraHasta").val();
    const dia = $("#ModalDia").val();
    const hora = $("#ModalHora").val();
    const minutos = $("#ModalMinutos").val();
    const observacion = $("#ModalObservacion").val();

    // Validar que exista el ID
    if (!idLicencia) {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'No se ha identificado el ID del registro a actualizar.',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    // 2. Realizar la petición AJAX
    $.ajax({
        type: "POST",
        url: "php_libs/soporte/Personal/LicenciasPermisos.php",
        data: {
            accion: "ActualizarLyP",
            id_licencia_permiso_modal: idLicencia,
            FechaInicio: fecha,
            ModalHoraDesde: horaInicio,
            ModalHoraHasta: horaFin,
            ModalDia: dia,
            ModalHora: hora,
            ModalMinutos: minutos,
            ModalObservacion: observacion
        },
        dataType: "json",
        success: function (response) {
            // Comprobar si la respuesta fue exitosa
            if (response.respuesta === true || response.respuestaOK === true) {
                // Ocultar el modal de edición
                $("#VentanaLicenciasPermisos").modal("hide");

                // Mensaje elegante con SweetAlert2
                Swal.fire({
                    icon: 'success',
                    title: '¡Actualizado!',
                    text: response.mensaje || 'El registro se actualizó correctamente.',
                    confirmButtonText: 'Aceptar',
                    timer: 2500, // Se cierra automáticamente en 2.5 segundos
                    timerProgressBar: true
                }).then(() => {
                    // Recargar la tabla y refrescar el progressbar/saldos de tiempo
                    if (typeof BuscarLicenciasPermisos === "function") {
                        BuscarLicenciasPermisos();
                    }
                });

            } else {
                // Alerta de error si el servidor devuelve false
                Swal.fire({
                    icon: 'error',
                    title: 'Error al actualizar',
                    text: response.mensaje || response.mensajeError || 'No se pudo actualizar el registro.',
                    confirmButtonText: 'Aceptar'
                });
            }
        },
        error: function (xhr, status, error) {
            console.error("Error en el servidor al actualizar:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error de comunicación',
                text: 'Ocurrió un error al procesar la solicitud en el servidor.',
                confirmButtonText: 'Aceptar'
            });
        }
    });
}

/**
 * Calcula la diferencia entre Hora Inicio y Hora Fin en el Modal
 * y asigna los valores calculados a las casillas de Días, Horas y Minutos.
 */
function calcularDiferenciaTiempoModal() {
    const horaDesde = $("#ModalHoraDesde").val();
    const horaHasta = $("#ModalHoraHasta").val();

    // Validar que ambos campos tengan un valor asignado
    if (!horaDesde || !horaHasta) return;

    // Convertir horas "HH:MM" a minutos desde inicio del día
    const [hInicio, mInicio] = horaDesde.split(':').map(Number);
    const [hFin, mFin] = horaHasta.split(':').map(Number);

    const minutosInicio = (hInicio * 60) + mInicio;
    const minutosFin = (hFin * 60) + mFin;

    // Calcular la diferencia total en minutos
    let diferenciaMinutos = minutosFin - minutosInicio;

    // Si la hora de fin es menor a la de inicio, asumimos que no es una diferencia válida
    if (diferenciaMinutos < 0) {
        diferenciaMinutos = 0;
    }

    // Determinar las horas de la jornada laboral (8 horas para tipo '05', 5 horas para el resto)
    const codigoContratacion = $('#lstTipoContratacion option:selected').val() || '';
    const tipoContratacion = codigoContratacion.substring(0, 2);
    const horasJornada = (tipoContratacion === "05") ? 8 : 5;
    const minutosJornada = horasJornada * 60;

    // Calcular Días, Horas y Minutos
    const dias = Math.floor(diferenciaMinutos / minutosJornada);
    const minutosRestantesDia = diferenciaMinutos % minutosJornada;

    const horas = Math.floor(minutosRestantesDia / 60);
    const minutos = minutosRestantesDia % 60;

    // Asignar los valores calculados a los inputs del modal
    $("#ModalDia").val(dias);
    $("#ModalHora").val(horas);
    $("#ModalMinutos").val(minutos);
}

// Escuchar eventos en el documento
$(document).ready(function () {

    // Vincular el evento change e input a los campos de hora de la ventana modal
    $(document).on("change input", "#ModalHoraDesde, #ModalHoraHasta", function () {
        calcularDiferenciaTiempoModal();
    });

});


function AbrirVentana(url)
{
    window.open(url, '_blank');
        return false;
}