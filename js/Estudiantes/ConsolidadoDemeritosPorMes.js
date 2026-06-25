$(function(){
    // =========================================================================
    // 1. DETECCIÓN AUTOMÁTICA DEL MES ACTUAL
    // =========================================================================
    function seleccionarMesActual() {
        // Obtener el mes actual del sistema (1 = Enero, 2 = Febrero, ..., 11 = Noviembre)
        var fechaActual = new Date();
        var mesActual = fechaActual.getMonth() + 1; 

        // Si estamos en Diciembre (12), por año escolar lo dejamos por defecto en Noviembre (11)
        if (mesActual > 11) {
            mesActual = 11;
        }

        // Asignar el valor de forma automática al select de la vista
        $('#lstmes').val(mesActual);
    }

    // Ejecutar la selección al cargar el documento
    seleccionarMesActual();

    // =========================================================================
    // 2. FUNCIONALIDAD DEL BOTÓN CANCELAR / LIMPIAR
    // =========================================================================
    $('#goCancelar').on('click', function(){
        $("#goBuscar").prop("disabled", false);
        $("#lstannlectivo, #lstmes").prop("disabled", false);
        
        // Limpiar contenido dinámico
        $('#listaDatosConsolidadoOK').empty();
        $('#divTabla').hide();
        
        // Ocultar y reiniciar tarjetas de auditoría
        $('#divCardsAuditoria').addClass('d-none');
        $('#cardTotalSecciones').text('0');
        $('#cardSeccionesConDatos').text('0');
        $('#cardSeccionesSinDatos').text('0');
        
        // Resetear formulario y reenfocar año
        $('#formDatosMatricula')[0].reset();
        seleccionarMesActual();
        $('#lstannlectivo').focus();
    });

    // =========================================================================
    // 3. VALIDACIÓN Y PROCESAMIENTO AJAX (BUSCAR CONSOLIDADO)
    // =========================================================================
    $('#formDatosMatricula').validate({
        rules:{
            lstannlectivo: { required: true },
            lstmes: { required: true }
        },
        messages: {
            lstannlectivo: "Por favor, seleccione un año lectivo.",
            lstmes: "Por favor, seleccione el mes a consultar."
        },	
        submitHandler: function(form){
            // Serializamos los parámetros (accion_buscar, lstannlectivo, lstmes)
            var str = $(form).serialize();
            
            $.ajax({
                beforeSend: function(){
                    // Deshabilitar controles y mostrar animación de carga en el botón corporativo
                    $('#goBuscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Consolidando...');
                    $('#divCardsAuditoria').addClass('d-none');
                    $('#divTabla').hide();
                },
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/Estudiante/ConsolidadoDemeritosPorMes.php", // Tu ruta centralizada de soporte
                data: str,
               success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros académicos para el período seleccionado.");
                        $('#listaDatosConsolidadoOK').empty();
                        $('#totalesConsolidadoMensual').hide();
                    } else {
                        toastr.success("Consolidado institucional generado con éxito.");
                        
                        // --- ACTUALIZAR TARJETAS DE AUDITORÍA DOCENTE ---
                        if(response.auditoria) {
                            $('#cardTotalSecciones').text(response.auditoria.total || 0);
                            $('#cardSeccionesConDatos').text(response.auditoria.con_datos || 0);
                            $('#cardSeccionesSinDatos').text(response.auditoria.sin_datos || 0);
                            $('#divCardsAuditoria').removeClass('d-none');
                        }

                        // --- VARIABLES ACUMULADORAS PARA EL PIE DE TABLA ---
                        var g_mat_m = 0, g_mat_h = 0;
                        var g_dem_m = 0, g_dem_h = 0;
                        var g_c_a = 0, g_c_b = 0, g_c_c = 0, g_c_d = 0;
                        var g_red_m = 0, g_red_h = 0;
                        var g_r_a = 0, g_r_b = 0, g_r_c = 0;
                        var g_rec_m = 0, g_rec_h = 0;

                        var tbody = $('#listaDatosConsolidadoOK');
                        tbody.empty();

                        // Recorremos las secciones devueltas por el backend
                        $.each(response.contenido, function(index, reg){
                            // Conversiones seguras a enteros por fila
                            var mat_h = parseInt(reg.mat_h) || 0;
                            var mat_m = parseInt(reg.mat_m) || 0;
                            var dem_h = parseInt(reg.dem_h) || 0;
                            var dem_m = parseInt(reg.dem_m) || 0;
                            var c_a   = parseInt(reg.c_a) || 0;
                            var c_b   = parseInt(reg.c_b) || 0;
                            var c_c   = parseInt(reg.c_c) || 0;
                            var c_d   = parseInt(reg.c_d) || 0;
                            var red_h = parseInt(reg.red_h) || 0;
                            var red_m = parseInt(reg.red_m) || 0;
                            var r_a   = parseInt(reg.r_a) || 0;
                            var r_b   = parseInt(reg.r_b) || 0;
                            var r_c   = parseInt(reg.r_c) || 0;
                            var rec_h = parseInt(reg.rec_h) || 0;
                            var rec_m = parseInt(reg.rec_m) || 0;

                            // Totales por fila (sección)
                            var total_mat = mat_h + mat_m;
                            var total_dem = dem_h + dem_m;
                            var total_causales = c_a + c_b + c_c + c_d;
                            var total_red = red_h + red_m;
                            var total_opciones = r_a + r_b + r_c;
                            var total_rec = rec_h + rec_m;

                            // Acumulación Vertical para los Totales Generales
                            g_mat_m += mat_m; g_mat_h += mat_h;
                            g_dem_m += dem_m; g_dem_h += dem_h;
                            g_c_a += c_a; g_c_b += c_b; g_c_c += c_c; g_c_d += c_d;
                            g_red_m += red_m; g_red_h += red_h;
                            g_r_a += r_a; g_r_b += r_b; g_r_c += r_c;
                            g_rec_m += rec_m; g_rec_h += rec_h;

                            // Badge de estatus según la entrega de datos
                            var badgeEstatus = (reg.tiene_datos === true) 
                                ? '<span class="badge bg-success px-2 py-1"><i class="fas fa-check me-1"></i> Completo</span>' 
                                : '<span class="badge bg-danger px-2 py-1"><i class="fas fa-clock me-1"></i> Pendiente</span>';

                            tbody.append(`
                                <tr>
                                    <td class="fw-bold text-start ps-2 text-uppercase bg-light">${reg.nombre_seccion}</td>
                                    <td>${badgeEstatus}</td>
                                    <td>${mat_m}</td><td>${mat_h}</td><td class="table-secondary fw-bold">${total_mat}</td>
                                    <td>${dem_m}</td><td>${dem_h}</td><td class="table-secondary fw-bold">${total_dem}</td>
                                    <td>${c_a}</td><td>${c_b}</td><td>${c_c}</td><td>${c_d}</td><td class="table-secondary fw-bold">${total_causales}</td>
                                    <td>${red_m}</td><td>${red_h}</td><td class="table-secondary fw-bold">${total_red}</td>
                                    <td>${r_a}</td><td>${r_b}</td><td>${r_c}</td><td class="table-secondary fw-bold">${total_opciones}</td>
                                    <td>${rec_m}</td><td>${rec_h}</td><td class="table-secondary fw-bold">${total_rec}</td>
                                </tr>
                            `);
                        });

                        // --- INYECTAR DATOS EN EL PIE DE TABLA (TFOOT) ---
                        $('#tot_mat_m').text(g_mat_m); $('#tot_mat_h').text(g_mat_h); $('#tot_mat_t').text(g_mat_m + g_mat_h);
                        $('#tot_dem_m').text(g_dem_m); $('#tot_dem_h').text(g_dem_h); $('#tot_dem_t').text(g_dem_m + g_dem_h);
                        $('#tot_c_a').text(g_c_a); $('#tot_c_b').text(g_c_b); $('#tot_c_c').text(g_c_c); $('#tot_c_d').text(g_c_d);
                        $('#tot_causales_t').text(g_c_a + g_c_b + g_c_c + g_c_d);
                        $('#tot_red_m').text(g_red_m); $('#tot_red_h').text(g_red_h); $('#tot_red_t').text(g_red_m + g_red_h);
                        $('#tot_r_a').text(g_r_a); $('#tot_r_b').text(g_r_b); $('#tot_r_c').text(g_r_c);
                        $('#tot_opciones_t').text(g_r_a + g_r_b + g_r_c);
                        $('#tot_rec_m').text(g_rec_m); $('#tot_rec_h').text(g_rec_h); $('#tot_rec_t').text(g_rec_m + g_rec_h);

                        // Mostrar la tabla y el pie de tabla calculado
                        $('.table-responsive').scrollTop(0);
                        $('#totalesConsolidadoMensual').show();
                        $('#divTabla').fadeIn();
                        
                        // Bloquear campos de búsqueda para mantener la integridad
                        $("#goBuscar").prop("disabled", true);
                        $("#lstannlectivo, #lstmes").prop("disabled", true);
                    }
                },
                error: function(){
                   toastr.error("Error de comunicación por Ajax al compilar el consolidado mensual.");
                },
                complete: function() {
                    // Restablecer el botón de búsqueda a su estado original
                    $('#goBuscar').html('<i class="fas fa-search me-1"></i> Consolidar Información');
                }
            });
            return false;
        }
    });
});