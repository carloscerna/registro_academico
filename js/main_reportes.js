// main_reportes.js MEJORADO Y COMPLETO

// Objeto de configuración para las URLs de los reportes.
// ¡Mantener y añadir nuevos reportes es ahora mucho más fácil!
const reportConfig = {
    // Nóminas
    'orden': { url: '/registro_academico/php_libs/reportes/Estudiante/Nomina.php' },
    'control_actividades': { url: '/registro_academico/php_libs/reportes/Estudiante/ControlActividades.php' },
    // CÓDIGO CORREGIDO
        'asistencia': { url: '/registro_academico/php_libs/reportes/Estudiante/Asistencia.php', params: ['lstannlectivo', 'lstFechaMes'] },
    // ▼▼▼ LÍNEA NUEVA ▼▼▼
        'asistenciax30': { url: '/registro_academico/php_libs/reportes/Estudiante/Asistenciax30cuadros.php' },
    'cuadro-de-promocion': { 
        isAjax: true, // Indica que no abre una ventana, sino que hace una llamada AJAX
        url: '/registro_academico/php_libs/soporte/CrearCuadrodePromocion.php' 
    },
    'paquete_escolar_02': { 
        url: '/registro_academico/php_libs/reportes/Estudiante/Paquetes.php' 
    },
      'familias': { url: '/registro_academico/php_libs/reportes/Estudiante/Familias.php', params: ['lstannlectivo'] },
// ▼▼▼ LÍNEA MODIFICADA ▼▼▼
    'firmas': { url: '/registro_academico/php_libs/reportes/Estudiante/Firmas.php', params: ['tituloFirmas'] },
    // Notas
    'boleta_notas': { url: '/registro_academico/php_libs/reportes/boleta_de_notas.php', params: ['chksello', 'chkfirma', 'chkfoto'] },
    'por_trimestre': { url: '/registro_academico/php_libs/reportes/notas_por_trimestre.php', params: ['lsttri'] },
    'por_asignatura': { url: '/registro_academico/php_libs/reportes/notas_trimestre_por_asignatura_basica.php', params: ['lstasignatura'] },
    'cuadro_promocion': { url: '/registro_academico/php_libs/reportes/Cuadros de Registro/Basica II y III Ciclo.php' },
    'certificados': { url: '/registro_academico/php_libs/reportes/certificados_2018.php' }
    // ... Agrega aquí las demás configuraciones de reportes
};

$(function() {
    // --- 1. LÓGICA DE CARGA INICIAL DE FILTROS ---
    cargarOpciones("#lstannlectivo", "includes/cargar-ann-lectivo.php");

// --- ESTABLECER EL MES ACTUAL POR DEFECTO ---
    const hoy = new Date();
    const mesActual = (hoy.getMonth() + 1).toString().padStart(2, '0'); // Obtiene mes (ej: "09", "10")
    $('#lstFechaMes').val(mesActual);

    $("#lstannlectivo").on('change', function() {
        const idAnnLectivo = $(this).val();
        $("#lstmodalidad").empty().append('<option value="">Cargando...</option>');
        if (idAnnLectivo) {
            cargarOpcionesDependiente("#lstmodalidad", "includes/cargar-bachillerato.php", { annlectivo: idAnnLectivo });
        } else {
            $("#lstmodalidad").empty().append('<option value="">Seleccione un año</option>');
        }
    });
    
    // Cargar asignaturas cuando se seleccione un grado
     $("#lstcodigoGrado").on('change', function () {
        const miselect = $("#lstasignatura");
        const annlectivo = $("#lstannlectivo").val();
        const modalidad = $("#lstmodalidad").val();
        const codigoGrado = $(this).val();
      
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>');
      
        if(codigoGrado && annlectivo && modalidad){
            $.post("includes/cargar-asignatura.php", { elegido: codigoGrado, annlectivo: annlectivo, modalidad: modalidad }, function(data) {
                miselect.empty().append('<option value="">Seleccione asignatura</option>');
                data.forEach(item => miselect.append(`<option value="${item.codigo}">${item.descripcion}</option>`));
            }, "json");
        }
    });

    // --- 2. VALIDACIÓN Y BÚSQUEDA DE GRUPOS ---
    $('#formFiltros').validate({
        rules: {
            lstannlectivo: { required: true },
            lstmodalidad: { required: true }
        },
        messages: {
            lstannlectivo: "Seleccione un año lectivo.",
            lstmodalidad: "Seleccione una modalidad."
        },
        errorElement: "div",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            error.insertAfter(element);
        },
        highlight: (element) => $(element).addClass("is-invalid"),
        unhighlight: (element) => $(element).removeClass("is-invalid"),
        submitHandler: function(form) {
                // Combina los datos del formulario con el parámetro de acción que falta.
            const formData = $(form).serialize() + '&accion_buscar=BuscarUser';

            $.ajax({
                url: "php_libs/soporte/phpAjaxReportes.inc.php",
                type: "POST",
                data: formData,
                dataType: "json",
                beforeSend: () => $('#panelResultados').removeClass('d-none'),
                success: function(response) {
                    if (response.respuesta) {
                        $('#listaResultados').html(response.contenido);
                        
                        const selectGrado = $("#lstcodigoGrado");
                        selectGrado.empty().append('<option value="">Seleccione grado</option>');
                        response.codigoGrado.forEach(item => {
                            selectGrado.append(`<option value="${item.codigo}">${item.descripcion}</option>`);
                        });

                        $("#lblTituloResultados").text(response.mensaje);
                        toastr.success("Grupos encontrados.", "Éxito");
                    } else {
                        $('#listaResultados').empty();
                        toastr.error(response.mensaje, "Error");
                    }
                },
                error: () => toastr.error('Error de comunicación con el servidor.', 'Error Fatal')
            });
            return false;
        }
    });

    // --- 3. MANEJADOR GENÉRICO PARA MOSTRAR/OCULTAR PANELES DE OPCIONES ---
    $('.report-selector').on('change', function() {
        const selector = $(this);
        const group = selector.data('group');
        const targetId = selector.find('option:selected').data('bs-target');

        $(`.report-options.${group}`).addClass('d-none'); // Oculta todos los de su grupo
        if (targetId) {
            $(targetId).removeClass('d-none'); // Muestra el específico
        }
    });

    // --- 4. DELEGACIÓN DE EVENTOS PARA GENERAR REPORTES ---
    $('#listaResultados').on('click', 'a.report-link', function(e) {
        e.preventDefault();

        const link = $(this);
        const reportType = link.data('report-type'); // 'nominas' o 'notas'
        const reportKey = $(`select[data-group='${reportType}']`).val();
        const reportCode = link.data('report-code'); // ej: '0301011801'
        
        if (!reportKey) {
            toastr.warning(`Por favor, seleccione un tipo de reporte de "${reportType}".`, 'Atención');
            return;
        }
        
        // ▼▼▼ LÓGICA NUEVA PARA MANEJAR EL CASO ESPECIAL DE PAQUETE ESCOLAR ▼▼▼
        if (reportKey === 'paquete_escolar_02') {
            const rubroValor = $('#lstRubro').val();
            let urlDestino = '/registro_academico/php_libs/reportes/Estudiante/Paquetes.php'; // URL por defecto

            // Si el rubro es '05' (Familias), cambiamos la URL
            if (rubroValor === '05') {
                urlDestino = '/registro_academico/php_libs/reportes/paquete_familias.php';
            }

            // Recolectamos todos los parámetros del panel
            const params = new URLSearchParams({
                todos: reportCode,
                fechapaquete: $('#FechaPaquete').val(),
                rubro: $('#lstRubro option:selected').text(), // El texto del rubro
                chkfechaPaquete: $('#chkfechaPaquete').is(':checked') ? 'yes' : 'no',
                chkNIEPaquete: $('#chkNIEPaquete').is(':checked') ? 'yes' : 'no'
            });

            const finalUrl = `${urlDestino}?${params.toString()}`;
            console.log("Abriendo URL de Paquete Escolar:", finalUrl);
            window.open(finalUrl, '_blank');
            return; // Detenemos la ejecución para no continuar con la lógica genérica
        }
        // ▲▲▲ FIN DE LA LÓGICA ESPECIAL ▲▲▲
        
        const config = reportConfig[reportKey];
        if (!config) {
            toastr.error(`No hay una configuración definida para el reporte "${reportKey}".`, 'Error de Configuración');
            return;
        }

        // Si es una llamada AJAX (ej. para generar un Excel)
        if (config.isAjax) {
            $.ajax({
                url: config.url,
                type: "POST",
                data: { todos: reportCode },
                dataType: "json",
                beforeSend: () => toastr.info("Generando archivo, por favor espere..."),
                success: (res) => {
                    if(res.respuesta) toastr.success(res.contenido || res.mensaje, "Archivo Generado");
                    else toastr.error(res.mensaje, "Error al generar");
                },
                error: () => toastr.error('No se pudo generar el archivo.', 'Error Fatal')
            });
        } else { // Si es para abrir una nueva ventana (PDF)
            let params = new URLSearchParams({ todos: reportCode });

            // Añadir parámetros adicionales desde la configuración
            if (config.params) {
                config.params.forEach(paramId => {
                    const el = $(`#${paramId}`);
                    let value;
                    if (el.is(':checkbox')) {
                        value = el.is(':checked') ? 'yes' : 'no';
                    } else {
                        value = el.val();
                    }
                    if(value) params.append(paramId, value);
                });
            }

            const finalUrl = `${config.url}?${params.toString()}`;
            console.log("Abriendo URL:", finalUrl); // Para depuración
            window.open(finalUrl, '_blank');
        }
    });

});