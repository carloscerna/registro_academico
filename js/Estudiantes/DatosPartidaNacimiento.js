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
        $('#listaPnOK').empty();
        $('#divTabla').hide();
        $('#lstannlectivo').focus();
    });

    // Funcionalidad del botón Actualizar
    $('#goActualizar').on('click',function(){
        var accion_ok = 'ActualizarDatosPn';
        
        var codigo_alumno_ = [], codigo_nie_ = [], codigo_genero_ = [], fecha_nacimiento_ = [],
            numero_pn_ = [], folio_pn_ = [], tomo_pn_ = [], libro_pn_ = [], estudio_parvularia_ = [];
        
        var fila = 0;
        
        $("#tablaDatosPn tbody tr").each(function(){
            var $row = $(this);
            
            codigo_alumno_[fila] = $row.data('id-alumno');
            codigo_nie_[fila] = $row.find("input[name='codigo_nie']").val();
            codigo_genero_[fila] = $row.find("select[name='codigo_genero']").val();
            fecha_nacimiento_[fila] = $row.find("input[name='fecha_nacimiento']").val();
            numero_pn_[fila] = $row.find("input[name='numero_pn']").val();
            folio_pn_[fila] = $row.find("input[name='folio_pn']").val();
            tomo_pn_[fila] = $row.find("input[name='tomo_pn']").val();
            libro_pn_[fila] = $row.find("input[name='libro_pn']").val();
            estudio_parvularia_[fila] = $row.find("input[name='estudio_parvularia']").is(':checked');
            
            fila++;
        });

        $.ajax({
            cache: false,                     
            type: "POST",                     
            dataType: "json",                     
            url: "php_libs/soporte/PhpDatosPartidaNacimiento.php",
            data: {                     
                accion: accion_ok, 
                total_filas: fila,
                codigo_alumno: codigo_alumno_,
                codigo_nie: codigo_nie_,
                codigo_genero: codigo_genero_,
                fecha_nacimiento: fecha_nacimiento_,
                numero_pn: numero_pn_,
                folio_pn: folio_pn_,
                tomo_pn: tomo_pn_,
                libro_pn: libro_pn_,
                estudio_parvularia: estudio_parvularia_
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
    $('#formDatosPn').validate({
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
                url: "php_libs/soporte/PhpDatosPartidaNacimiento.php",
                data: str,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        $('#listaPnOK').empty();
                        $('#divTabla').hide();
                        $("#goResumen").prop("disabled", true);
                    } else {
                        toastr.success("Registros encontrados.");
                        $('#listaPnOK').html(response.contenido);
                        
                        // Lógica para actualizar el panel de resumen
                        if(response.resumen) {
                            $('#resumenMasculino').text(response.resumen.genero.M || 0);
                            $('#resumenFemenino').text(response.resumen.genero.F || 0);
                            $('#resumenParvulariaSi').text(response.resumen.parvularia.si || 0);
                            $('#resumenParvulariaNo').text(response.resumen.parvularia.no || 0);
                            
                            // Construir dinámicamente el resumen de edades
                            var edadesContainer = $('#resumenEdadesContainer');
                            edadesContainer.empty();
                            var edadesList = $('<ul class="list-group"></ul>');
                            if(Object.keys(response.resumen.edades).length > 0){
                                $.each(response.resumen.edades, function(edad, cantidad){
                                    edadesList.append('<li class="list-group-item d-flex justify-content-between align-items-center">' + edad + ' años<span class="badge bg-secondary rounded-pill">' + cantidad + '</span></li>');
                                });
                            } else {
                                edadesList.append('<li class="list-group-item text-center">No hay datos de edades.</li>');
                            }
                            edadesContainer.append(edadesList);

                            $("#goResumen").prop("disabled", false);
                        }

                        $('.table-responsive').scrollTop(0);
                        $('#divTabla').show();
                        
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
