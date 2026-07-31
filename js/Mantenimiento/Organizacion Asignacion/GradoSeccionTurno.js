// Variables globales
var idUser_ok = 0;
var accion_gst = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo = "";
var codigo_modalidad = "";
var msjEtiqueta = "";

// INICIO DE LA FUNCIÓN PRINCIPAL
$(function () {

    // Ocultar alertas iniciales
    $("#AlertSeGST").hide();

    // ---------------------------------------------------------------------
    // 1. CARGA INICIAL DE SELECTS PRINCIPALES (Año Lectivo y Modalidad)
    // ---------------------------------------------------------------------
    var miselectAnn = $("#lstAnnLectivoSeGST");
    var miselectMod = $("#lstModalidadSeGST");

    // Cargar Año Lectivo al iniciar
    if (miselectAnn.length) {
        cargarOpciones(miselectAnn, "includes/cargar-ann-lectivo.php");
    }

    // Cuando cambia Año Lectivo -> Cargar Modalidades asociadas
    miselectAnn.on('change', function () {
        $("#AlertSeGST").hide();
        let idAnnLectivo = $(this).val();
        
        if (idAnnLectivo !== "00" && idAnnLectivo !== "") {
            cargarOpcionesDependiente(miselectMod, "includes/cargar-bachillerato.php", { annlectivo: idAnnLectivo });
        } else {
            miselectMod.empty().append('<option value="00">Seleccionar...</option>');
            $('#listaContenidoSeGST').empty();
        }
    });

    // Cuando cambia Modalidad -> Resetear o Limpiar
    miselectMod.on('change', function () {
        $("#AlertSeGST").hide();
        if ($(this).val() === "00") {
            $('#listaContenidoSeGST').empty();
            $("#lstSeGST, #lstGradoSeGST, #lstSeccionSeGST, #lstTurnoSeGST").empty();
        }
    });

    // ---------------------------------------------------------------------
    // 2. NAVEGACIÓN Y LIMPIEZA DE MODAL
    // ---------------------------------------------------------------------
    $("#NavOrganizacionAsignacion ul.nav > li > a").on("click", function () {
        if ($(this).text().trim() === "Grado/Sección/Turno") {
            $('#listaContenidoSeGST').empty();
            $("#AlertSeGST").hide();
            $("#lstAnnLectivoSeGST").val('00');
            $("#lstModalidadSeGST").val('00');
        }
    });

    // Al cerrar la ventana modal -> Resetear formulario y limpiar errores
    $("#VentanaSeGST").on('hidden.bs.modal', function () {
        $("#formVentanaSeGST")[0].reset();
        $("label.error, em.invalid-feedback").remove();
        $(".is-invalid, .is-valid").removeClass("is-invalid is-valid");
        accion = "";
    });

    // ---------------------------------------------------------------------
    // 3. EVENTO CLIC EN TABLA (EDITAR / ELIMINAR)
    // ---------------------------------------------------------------------
    $('body').on('click', '#listaContenidoSeGST a', function (e) {
        e.preventDefault();
        
        Id_Editar_Eliminar = $(this).attr('href');
        var accion_ok = $(this).attr('data-accion');

        // --- ACCIÓN: EDITAR ---
        if (accion_ok === 'EditarSeGST') {
            $('#accion_gst').val('EditarSeGST');
            accion = 'EditarGST';

            // Obtener el ID del registro desde la fila (TD eq:2)
            var id_ = $(this).closest('tr').children('td:eq(2)').text().trim();

            $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", 
                { id_: id_, accion: accion },
                function (data) {
                    if (data && data.length > 0) {
                        // Extraer texto y valor seleccionados actualmente en los filtros principales
                        var texto_annlectivo_gst = $("#lstAnnLectivoSeGST option:selected").text();
                        var texto_modalidad_gst = $("#lstModalidadSeGST option:selected").text();

                        // MOSTRAR DATOS EN LOS CAMPOS DEL MODAL
                        // (Si usaste el HTML optimizado con <input readonly id="infoAnnLectivo">)
                        if ($('#infoAnnLectivo').length) {
                            $('#infoAnnLectivo').val(texto_annlectivo_gst);
                            $('#infoModalidad').val(texto_modalidad_gst);
                        } else {
                            // Si mantuviste los <span> originales
                            $("#TextoAnnLectivoSeGST").text("Año lectivo: " + texto_annlectivo_gst);
                            $("#TextoModalidadesSeGST").text("Modalidad: " + texto_modalidad_gst);
                        }

                        // Cargar selects internos del Modal con el valor preseleccionado
                        listar_CodigoSeGST(data[0].codigo_se);
                        listar_CodigoTurnoGST(data[0].codigo_turno);

                        // Configurar encabezado del Modal
                        $("label[for=LblTituloSeGST]").text("Grado/Sección/Turno | Actualizar");
                        accion = "ActualizarSeGST";
                        accion_gst = "ActualizarGST";

                        // Abrir Modal
                        $('#VentanaSeGST').modal("show");
                    } else {
                        toastr["error"]("No se encontraron los datos del registro.", "Sistema");
                    }
                }, "json"
            );
        }

        // --- ACCIÓN: ELIMINAR ---
        if (accion_ok === 'EliminarSeGST') {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-danger' },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: '¿Qué desea hacer?',
                text: '¡Eliminar el registro seleccionado!',
                type: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, ¡Eliminar!',
                cancelButtonText: 'No, ¡Cancelar!',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url: "php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                        data: { accion: 'EliminarSeGST', id_: Id_Editar_Eliminar },
                        success: function (response) {
                            if (response.respuesta === true) {
                                toastr["success"]("Registro eliminado correctamente", "Sistema");
                                // Recargar Tabla
                                ejecutarBusquedaTabla();
                            } else {
                                toastr["error"]("No se pudo eliminar el registro", "Sistema");
                            }
                        }
                    });
                }
            });
        }
    });

    // ---------------------------------------------------------------------
    // 4. BÚSQUEDA Y GUARDADO
    // ---------------------------------------------------------------------
    $('#goBuscarSeGST').on('click', function () {
        codigo_annlectivo = $("#lstAnnLectivoSeGST").val();
        codigo_modalidad = $("#lstModalidadSeGST").val();

        if (codigo_annlectivo === "00" || !codigo_annlectivo) {
            $("#AlertSeGST").show().find("#TextoAlertSeGST").text("Debe Seleccionar Año Lectivo para Buscar.");
            return;
        }
        if (codigo_modalidad === "00" || !codigo_modalidad) {
            $("#AlertSeGST").show().find("#TextoAlertSeGST").text("Debe Seleccionar la Modalidad para Buscar.");
            return;
        }

        ejecutarBusquedaTabla();
    });

    function ejecutarBusquedaTabla() {
        codigo_annlectivo = $("#lstAnnLectivoSeGST").val();
        codigo_modalidad = $("#lstModalidadSeGST").val();
        accion = 'BuscarSeGST';

        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", 
            { accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad },
            function (response) {
                if (response.respuesta === true) {
                    toastr["info"]('Registros Encontrados', "Sistema");
                } else {
                    toastr["warning"]('Registros No Encontrados', "Sistema");
                }
                $('#listaContenidoSeGST').empty().html(response.contenido);
            }, "json"
        );
    }

    // Botón Actualizar dentro del Modal
    $('#goActualizarSeGST').on('click', function () {
        codigo_annlectivo = $("#lstAnnLectivoSeGST").val();
        codigo_modalidad = $("#lstModalidadSeGST").val();
        var codigo_servicio_educativo = $("#formVentanaSeGST select[name=lstSeGST]").val();

        if (codigo_servicio_educativo === "00" || codigo_servicio_educativo === "" || codigo_servicio_educativo === null) {
            toastr["warning"]("Debe seleccionar un Servicio Educativo para Guardar.", "Sistema");
            return;
        }

        accion = 'ActualizarSeGST';
        $('#accion_gst').val('ActualizarSeGST');
        $('#formVentanaSeGST').submit();
    });

    // ---------------------------------------------------------------------
    // 5. VALIDACIÓN Y ENVÍO DEL FORMULARIO DEL MODAL (JQUERY VALIDATE)
    // ---------------------------------------------------------------------
    $('#formVentanaSeGST').validate({
        ignore: "",
        rules: {
            lstSeGST: { required: true },
            lstTurnoSeGST: { required: true }
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            error.insertAfter(element.closest('.input-group').length ? element.closest('.input-group') : element);
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        submitHandler: function () {
            var str = $('#formVentanaSeGST').serialize();

            $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                data: str + "&accion=" + accion + "&id=" + Math.random() + "&id_=" + Id_Editar_Eliminar + "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_modalidad=" + codigo_modalidad,
                success: function (response) {
                    if (response.respuesta === false) {
                        toastr["error"](response.mensaje, "Sistema");
                    } else {
                        toastr["success"](response.mensaje || "Registro actualizado con éxito", "Sistema");
                        $('#VentanaSeGST').modal("hide");
                        ejecutarBusquedaTabla(); // Refrescar la tabla automáticamente
                    }
                },
                error: function () {
                    toastr["error"]("Ocurrió un error al procesar la solicitud en el servidor.", "Sistema");
                }
            });
        }
    });

}); // FIN DEL READY

// ---------------------------------------------------------------------
// FUNCIONES EXTERNAS DE CARGA DE SELECTS (Servicio Educativo y Turno)
// ---------------------------------------------------------------------
function listar_CodigoSeGST(CodigoSeGST) {
    var miselect = $("#formVentanaSeGST select[name=lstSeGST]");
    miselect.empty().append('<option value="">Cargando...</option>');

    $.post("includes/cargar-servicio-educativo.php", function (data) {
        miselect.empty().append('<option value="00">Seleccionar...</option>');
        $.each(data, function (i, item) {
            var isSelected = (CodigoSeGST == item.codigo) ? 'selected' : '';
            miselect.append('<option value="' + item.codigo + '" ' + isSelected + '>' + item.descripcion + '</option>');
        });
    }, "json");
}

function listar_CodigoTurnoGST(CodigoTurnoGST) {
    var miselect = $("#formVentanaSeGST select[name=lstTurnoSeGST]");
    miselect.empty().append('<option value="">Cargando...</option>');

    $.post("includes/cargar-turno.php", function (data) {
        miselect.empty().append('<option value="00">Seleccionar...</option>');
        $.each(data, function (i, item) {
            var isSelected = (CodigoTurnoGST == item.codigo) ? 'selected' : '';
            miselect.append('<option value="' + item.codigo + '" ' + isSelected + '>' + item.descripcion + '</option>');
        });
    }, "json");
}