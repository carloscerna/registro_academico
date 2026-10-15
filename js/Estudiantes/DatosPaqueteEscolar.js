$(function(){
    // --- FUNCIONALIDAD DE LOS BOTONES ---

    // Funcionalidad del botón Cancelar
    $('#goCancelar').on('click', function(){
        $("#goBuscar").prop("disabled", false);
        $("#goActualizar").prop("disabled", true);
        $("#goResumen").prop("disabled", true);
        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
        $('#listaDatosPaqueteEscolarOK').empty();
        $('#divTabla').hide();
        $('#lstannlectivo').focus();
    });

    // Funcionalidad del botón Actualizar / Guardar Paquetes
    $('#goActualizar').on('click', function(){
        var accion_ok = 'ActualizarDatosPaqueteEscolar';
        
        var id_alumno_ = [],
            id_alumno_paquete_ = [],
            posee_tablet_ = [], tablet_serie_ = [],
            posee_computadora_portatil_ = [], computadora_serie_ = [],
            posee_utiles_escolares_ = [],
            posee_zapatos_ = [], talla_zapatos_ = [],
            posee_blusas_ = [], talla_blusas_ = [],
            posee_camisa_ = [], talla_camisa_ = [],
            posee_pantalon_ = [], talla_pantalon_ = [],
            posee_short_ = [], talla_short_ = [],
            estado_entrega_ = [], fecha_entrega_ = [], observacion_ = [];
        var posee_falda_ = [], talla_falda_ = [];
        var fila = 0;
        
        $("#tablaDatosPaqueteEscolar tbody tr").each(function(){
            var $row = $(this);
            
            id_alumno_[fila] = $row.data('id-alumno');
            id_alumno_paquete_[fila] = $row.data('id-alumno-paquete') || 0;
            
            posee_tablet_[fila] = $row.find("input[name='chk_tablet']").is(':checked');
            tablet_serie_[fila] = $row.find("input[name='txt_tablet_serie']").val();
            
            posee_computadora_portatil_[fila] = $row.find("input[name='chk_computadora']").is(':checked');
            computadora_serie_[fila] = $row.find("input[name='txt_computadora_serie']").val();
            
            posee_utiles_escolares_[fila] = $row.find("input[name='chk_utiles']").is(':checked');
            
            posee_zapatos_[fila] = $row.find("input[name='chk_zapatos']").is(':checked');
            talla_zapatos_[fila] = $row.find("input[name='txt_talla_zapatos']").val();
            
            posee_blusas_[fila] = $row.find("input[name='chk_blusas']").is(':checked');
            talla_blusas_[fila] = $row.find("input[name='txt_talla_blusas']").val();
            
            posee_camisa_[fila] = $row.find("input[name='chk_camisa']").is(':checked');
            talla_camisa_[fila] = $row.find("input[name='txt_talla_camisa']").val();
            
            posee_pantalon_[fila] = $row.find("input[name='chk_pantalon']").is(':checked');
            talla_pantalon_[fila] = $row.find("input[name='txt_talla_pantalon']").val();
            
            posee_short_[fila] = $row.find("input[name='chk_short']").is(':checked');
            talla_short_[fila] = $row.find("input[name='txt_talla_short']").val();
            
            estado_entrega_[fila] = $row.find("select[name='lst_estado_entrega']").val();
            fecha_entrega_[fila] = $row.find("input[name='txt_fecha_entrega']").val();
            observacion_[fila] = $row.find("input[name='txt_observacion']").val();
            
            posee_falda_[fila] = $row.find("input[name='chk_falda']").is(':checked');
            talla_falda_[fila] = $row.find("input[name='txt_talla_falda']").val();

            fila++;
        });

        $.ajax({
            cache: false,                     
            type: "POST",                     
            dataType: "json",                     
            url: "php_libs/soporte/PhpDatosPaqueteEscolar.php",
            data: {                     
                accion: accion_ok, 
                total_filas: fila,
                anio_lectivo: $('#lstannlectivo').val(),
                id_alumno: id_alumno_,
                id_alumno_paquete: id_alumno_paquete_,
                posee_tablet: posee_tablet_,
                tablet_serie: tablet_serie_,
                posee_computadora_portatil: posee_computadora_portatil_,
                computadora_serie: computadora_serie_,
                posee_utiles_escolares: posee_utiles_escolares_,
                posee_zapatos: posee_zapatos_,
                talla_zapatos: talla_zapatos_,
                posee_blusas: posee_blusas_,
                talla_blusas: talla_blusas_,
                posee_camisa: posee_camisa_,
                talla_camisa: talla_camisa_,
                posee_pantalon: posee_pantalon_,
                talla_pantalon: talla_pantalon_,
                posee_short: posee_short_,
                talla_short: talla_short_,
                estado_entrega: estado_entrega_,
                fecha_entrega: fecha_entrega_,
                observacion: observacion_,
                posee_falda: posee_falda_,
                talla_falda: talla_falda_,
            },                     
            success: function(response) {                     
                if (response.respuesta === true) {                     
                    toastr.success("Paquetes escolares actualizados correctamente.");
                    $('#goCancelar').click();
                } else {
                    toastr.error(response.mensaje || "Hubo un error al actualizar.");
                }
            },
            error: function() {
                toastr.error("Error de comunicación con el servidor.");
            }
        });    
    });

    // Búsqueda de registros
    $('#formDatosPaqueteEscolar').validate({
        rules:{
            lstannlectivo: {required: true},
            lstmodalidad: {required: true},
            lstgradoseccion: {required: true},
        },
        messages: {
            lstannlectivo: "Seleccione un año lectivo.",
            lstmodalidad: "Seleccione una modalidad.",
            lstgradoseccion: "Seleccione un grado.",
        },	
        submitHandler: function(form){
            var str = $(form).serialize();
            $.ajax({
                beforeSend: function(){
                    $('#goBuscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
                },
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/PhpDatosPaqueteEscolar.php",
                data: str,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        $('#listaDatosPaqueteEscolarOK').empty();
                        $('#divTabla').hide();
                        $("#goResumen").prop("disabled", true);
                    } else {
                        toastr.success("Registros de estudiantes cargados.");
                        $('#listaDatosPaqueteEscolarOK').html(response.contenido);
                        
                        // --- ACTUALIZAR RESUMEN ---
                        if(response.resumen) {
                            $('#resumenTablet').text(response.resumen.tablet || 0);
                            $('#resumenLaptop').text(response.resumen.laptop || 0);
                            $('#resumenUtiles').text(response.resumen.utiles || 0);
                            $('#resumenZapatos').text(response.resumen.zapatos || 0);
                            $('#resumenCompletos').text(response.resumen.completos || 0);
                            $('#resumenParciales').text(response.resumen.parciales || 0);
                            $('#resumenPendientes').text(response.resumen.pendientes || 0);
                            $('#resumenTotal').text(response.resumen.total || 0);
                            $("#goResumen").prop("disabled", false);
                        }

                        $('.table-responsive').scrollTop(0);
                        $('#divTabla').show();
                        
                        $('[data-toggle="tooltip"]').tooltip();

                        $("#goActualizar").prop("disabled", false);
                        $("#goBuscar").prop("disabled", true);
                        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", true);
                    }
                },
                error:function(){
                   toastr.error("Error de Ajax al buscar los datos.");
                },
                complete: function() {
                    $('#goBuscar').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar Registros');
                }
            });
            return false;
        },
    });


// --- FUNCIONALIDAD DEL BOTÓN IMPRIMIR REPORTE ---
$('#goImprimir').on('click', function(){
    var annlectivo = $('#lstannlectivo').val();
    var modalidad = $('#lstmodalidad').val();
    var gradoseccion = $('#lstgradoseccion').val(); // Ejemplo: '010101' (Grado + Sección + Turno)

    // Concatenar el parámetro "todos" según el estándar del backend
    var todos = modalidad + gradoseccion + annlectivo;

    // Abrir el reporte en una nueva pestaña
    var url = "php_libs/reportes/Estudiante/imprimir_paquete_escolar.php?todos=" + todos + "&lstannlectivo=" + annlectivo;
    window.open(url, '_blank');
});

// --- DENTRO DE $('#goCancelar').on('click', ...) ---
// Recuerda desactivar el botón al cancelar:
$("#goImprimir").prop("disabled", true);

// --- DENTRO DEL success DE $.ajax (Al buscar registros) ---
// Activa el botón únicamente cuando se encuentren registros:
$("#goImprimir").prop("disabled", false);

});