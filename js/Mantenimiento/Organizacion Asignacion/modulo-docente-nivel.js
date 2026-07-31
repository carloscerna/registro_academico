const DocenteNivelModule = (() => {
    // Estado del Módulo (Aislado de la ventana global)
    const state = {
        urlAjax: "php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
        idEditarEliminar: 0,
        accion: ""
    };

    // Referencias a Selectores DOM
    const $elements = {
        alert: $("#AlertDN"),
        textoAlert: $("#TextoAlertDN"),
        tableBody: $('#listaContenidoDN'),
        formMain: $('#FormDN'),
        formModal: $('#formVentanaDN'),
        modal: $('#VentanaDN'),
        selectAnnLectivo: $("#lstAnnLectivoDN"),
        selectModalidad: $("#lstModalidadDN"),
        selectDocente: $("#lstDocenteNivel"),
        selectTurno: $("#lstTurnoDN")
    };

    // Inicialización del Módulo
    const init = () => {
        bindEvents();
        cargarInitSelects();
    };

    const cargarInitSelects = async () => {
        await AppService.cargarSelect($elements.selectAnnLectivo, "includes/cargar-ann-lectivo.php", {}, 'codigo', 'nombre');
    };

    const bindEvents = () => {
        // Al cambiar Año Lectivo -> Cargar Modalidades
        $elements.selectAnnLectivo.on('change', async function () {
            $elements.alert.hide();
            const annlectivo = $(this).val();
            if (annlectivo && annlectivo !== "00") {
                await AppService.cargarSelect($elements.selectModalidad, "includes/cargar-bachillerato.php", { annlectivo }, 'codigo', 'nombre');
            } else {
                $elements.selectModalidad.empty().append('<option value="00">Seleccionar...</option>');
            }
        });

        // Al cambiar Modalidad -> Cargar Docentes y Turnos
        $elements.selectModalidad.on('change', async function () {
            $elements.alert.hide();
            const modalidad = $(this).val();
            $elements.tableBody.empty();

            if (modalidad !== "00") {
                AppService.cargarSelect($elements.selectDocente, "includes/cargar_nombre_personal.php");
                AppService.cargarSelect($elements.selectTurno, "includes/cargar-turno.php");
            }
        });

        // Buscar
        $('#goBuscarDN').on('click', buscarRegistros);

        // Guardar desde el panel principal
        $('#goGuardarDN').on('click', () => $elements.formMain.submit());

        // Eventos Delegados en Tabla (Editar / Eliminar)
        $elements.tableBody.on('click', 'a', handleTableActions);

        // Reset del modal al cerrarse
        $elements.modal.on('hidden.bs.modal', () => {
            $elements.formModal[0].reset();
            $elements.formModal.find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
            state.accion = "";
        });

        // Validaciones de Formularios
        initValidations();
    };

    // Validación y Envió de Búsquedas
    const buscarRegistros = async () => {
        const codigo_annlectivo = $elements.selectAnnLectivo.val();
        const codigo_modalidad = $elements.selectModalidad.val();

        if (codigo_annlectivo === "00" || codigo_modalidad === "00") {
            $elements.alert.show();
            $elements.textoAlert.text("Debe seleccionar Año Lectivo y Modalidad para Buscar.");
            return;
        }

        $elements.alert.hide();
        const response = await AppService.apiPost(state.urlAjax, {
            accion: 'BuscarDN',
            codigo_annlectivo,
            codigo_modalidad
        });

        if (response.respuesta) {
            ToastService.info('Registros Encontrados');
            $elements.tableBody.html(response.contenido);
        } else {
            ToastService.warning('Registros No Encontrados');
            $elements.tableBody.empty();
        }
    };

    // Acciones Editar y Eliminar
    const handleTableActions = async function (e) {
        e.preventDefault();
        const $link = $(this);
        const accionBtn = $link.attr('data-accion');
        state.idEditarEliminar = $link.attr('href');

        if (accionBtn === 'EditarDN') {
            const id_ = $link.closest('tr').find('td:eq(2)').text();
            state.accion = 'EditarDN';

            const data = await AppService.apiPost(state.urlAjax, { id_, accion: state.accion });
            if (data && data.length > 0) {
                $("#TextoAnnLectivoDN").text($elements.selectAnnLectivo.find("option:selected").text());
                $("#TextoModalidadesDN").text($elements.selectModalidad.find("option:selected").text());

                await AppService.cargarSelect("#formVentanaDN select[name=lstDocenteNivel]", "includes/cargar_nombre_personal.php");
                $("#formVentanaDN select[name=lstDocenteNivel]").val(data[0].codigo_docente);

                await AppService.cargarSelect("#formVentanaDN select[name=lstTurnoDN]", "includes/cargar-turno.php");
                $("#formVentanaDN select[name=lstTurnoDN]").val(data[0].codigo_turno);

                $("label[for=LblTituloDN]").text("Docente/Nivel | Actualizar");
                $elements.modal.modal("show");
            }
        }

        if (accionBtn === 'EliminarDN') {
            const confirmed = await ConfirmService.askDelete();
            if (confirmed) {
                const res = await AppService.apiPost(state.urlAjax, { accion_buscar: 'EliminarDN', id_: state.idEditarEliminar });
                if (res.respuesta) {
                    ToastService.info('Registro Eliminado');
                    buscarRegistros();
                }
            }
        }
    };

    // Validaciones JQuery Validate
    const initValidations = () => {
        $elements.formMain.validate({
            rules: { lstAnnLectivoDN: { required: true } },
            submitHandler: async () => {
                const formData = $elements.formMain.serialize() + 
                    `&accion=GuardarDN&codigo_annlectivo=${$elements.selectAnnLectivo.val()}&codigo_modalidad=${$elements.selectModalidad.val()}`;
                
                const response = await AppService.apiPost(state.urlAjax, formData);
                if (response.respuesta) {
                    ToastService.success(response.mensaje);
                    buscarRegistros();
                } else {
                    ToastService.error(response.mensaje);
                }
            }
        });
    };

    return { init };
})();

// Inicializar cuando el DOM esté listo
$(document).ready(() => {
    DocenteNivelModule.init();
});