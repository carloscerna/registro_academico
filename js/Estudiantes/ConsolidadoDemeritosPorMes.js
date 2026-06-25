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
                    } else {
                        toastr.success("Consolidado institucional generado con éxito.");
                        
                        // --- ACTUALIZAR TARJETAS DE AUDITORÍA DOCENTE ---
                        if(response.auditoria) {
                            $('#cardTotalSecciones').text(response.auditoria.total || 0);
                            $('#cardSeccionesConDatos').text(response.auditoria.con_datos || 0);
                            $('#cardSeccionesSinDatos').text(response.auditoria.sin_datos || 0);
                            $('#divCardsAuditoria').removeClass('d-none');
                        }

                        // --- INYECTAR FILAS EN LA TABLA MATRIZ ---
                        var tbody = $('#listaDatosConsolidadoOK');
                        tbody.empty();

                        // Recorremos las secciones devueltas por el backend
                        $.each(response.contenido, function(index, reg){
                            // Totales calculados al vuelo por fila (sección)
                            var total_mat = parseInt(reg.mat_h) + parseInt(reg.mat_m);
                            var total_dem = parseInt(reg.dem_h) + parseInt(reg.dem_m);
                            var total_causales = parseInt(reg.c_a) + parseInt(reg.c_b) + parseInt(reg.c_c) + parseInt(reg.c_d);
                            var total_red = parseInt(reg.red_h) + parseInt(reg.red_m);
                            var total_opciones = parseInt(reg.r_a) + parseInt(reg.r_b) + parseInt(reg.r_c);
                            var total_rec = parseInt(reg.rec_h) + parseInt(reg.rec_m);

                            // Badge de estatus según la entrega de datos
                            var badgeEstatus = (reg.tiene_datos === true) 
                                ? '<span class="badge bg-success px-2 py-1"><i class="fas fa-check me-1"></i> Completo</span>' 
                                : '<span class="badge bg-danger px-2 py-1"><i class="fas fa-clock me-1"></i> Pendiente</span>';

                            tbody.append(`
                                <tr>
                                    <td class="fw-bold text-start ps-2 text-uppercase bg-light">${reg.nombre_seccion}</td>
                                    <td>${badgeEstatus}</td>
                                    <td>${reg.mat_m}</td><td>${reg.mat_h}</td><td class="table-secondary fw-bold">${total_mat}</td>
                                    <td>${reg.dem_m}</td><td>${reg.dem_h}</td><td class="table-secondary fw-bold">${total_dem}</td>
                                    <td>${reg.c_a}</td><td>${reg.c_b}</td><td>${reg.c_c}</td><td>${reg.c_d}</td><td class="table-secondary fw-bold">${total_causales}</td>
                                    <td>${reg.red_m}</td><td>${reg.red_h}</td><td class="table-secondary fw-bold">${total_red}</td>
                                    <td>${reg.r_a}</td><td>${reg.r_b}</td><td>${reg.r_c}</td><td class="table-secondary fw-bold">${total_opciones}</td>
                                    <td>${reg.rec_m}</td><td>${reg.rec_h}</td><td class="table-secondary fw-bold">${total_rec}</td>
                                </tr>
                            `);
                        });

                        // Desplazar el scroll arriba y mostrar la tabla armada
                        $('.table-responsive').scrollTop(0);
                        $('#divTabla').fadeIn();
                        
                        // Bloquear campos de búsqueda para mantener la integridad de la consulta actual
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