$(function(){
    // --- LÓGICA PARA CARGAR SELECTS DEPENDIENTES ---
    // (Esta lógica se mantiene en tus otros archivos JS y no se repite aquí)

    // --- FUNCIONALIDAD DE LOS BOTONES ---

    // Funcionalidad del botón Cancelar
    $('#goCancelar').on('click',function(){
        $("#goBuscar").prop("disabled", false);
        $("#goActualizar").prop("disabled", true);
        $("#goResumen").prop("disabled", true); // Desactivar botón de resumen
        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
        $('#listaDatosMatriculaOK').empty();
        $('#divTabla').hide();
        $('#lstannlectivo').focus();
    });

    // Funcionalidad del botón Actualizar
    $('#goActualizar').on('click',function(){
        var accion_ok = 'ActualizarDatosMatricula';
        
        var codigo_matricula_ = [], codigo_seccion_turno_ = [], sobreedad_ = [], repitente_ = [], 
            retirado_ = [], nuevo_ingreso_ = [], pn_ = [], certificado_ = [], imprimir_foto_ = [];
        
        var fila = 0;
        
        $("#tablaDatosMatricula tbody tr").each(function(){
            var $row = $(this);
            
            codigo_matricula_[fila] = $row.data('id-matricula'); 
            codigo_seccion_turno_[fila] = $row.find("select[name='seccion_turno']").val();
            sobreedad_[fila] = $row.find("input[name='chksobreedad']").is(':checked');
            repitente_[fila] = $row.find("input[name='chkrepitente']").is(':checked');
            retirado_[fila] = $row.find("input[name='chkretirado']").is(':checked');
            nuevo_ingreso_[fila] = $row.find("input[name='chknuevoingreso']").is(':checked');
            pn_[fila] = $row.find("input[name='chkpn']").is(':checked');
            certificado_[fila] = $row.find("input[name='chkcertificado']").is(':checked');
            imprimir_foto_[fila] = $row.find("input[name='chkimprimirfoto']").is(':checked');
            
            fila++;
        });

        $.ajax({
            cache: false,                     
            type: "POST",                     
            dataType: "json",                     
            url: "php_libs/soporte/PhpDatosMatricula.php",
            data: {                     
                accion: accion_ok, 
                total_filas: fila,
                codigo_matricula: codigo_matricula_, 
                codigo_seccion_turno: codigo_seccion_turno_, 
                sobreedad: sobreedad_, 
                repitente: repitente_, 
                retirado: retirado_,
                nuevo_ingreso: nuevo_ingreso_,
                pn: pn_,
                certificado: certificado_,
                imprimir_foto: imprimir_foto_
            },                     
            success: function(response) {                     
                if (response.respuesta === true) {                     
                    toastr.success("Registros actualizados correctamente.");
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
    $('#formDatosMatricula').validate({
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
                url: "php_libs/soporte/PhpDatosMatricula.php",
                data: str,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        $('#listaDatosMatriculaOK').empty();
                        $('#divTabla').hide();
                        $("#goResumen").prop("disabled", true); // Desactivar resumen si no hay datos
                    } else {
                        toastr.success("Registros encontrados.");
                        $('#listaDatosMatriculaOK').html(response.contenido);
                        
                        // --- LÓGICA PARA ACTUALIZAR EL PANEL DE RESUMEN ---
                        if(response.resumen) {
                            $('#resumenSobreedad').text(response.resumen.sobreedad || 0);
                            $('#resumenRepitente').text(response.resumen.repitente || 0);
                            $('#resumenRetirado').text(response.resumen.retirado || 0);
                            $('#resumenNuevoIngreso').text(response.resumen.nuevo_ingreso || 0);
                            $('#resumenTotal').text(response.resumen.total || 0);
                            $("#goResumen").prop("disabled", false); // Activar botón de resumen
                        }
                        // --- FIN DE LA LÓGICA DEL PANEL ---

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
                    $('#goBuscar').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                }
            });
            return false;
        },
    });
});
