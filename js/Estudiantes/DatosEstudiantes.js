$(function(){
    // Funcionalidad del botón Cancelar
    $('#goCancelar').on('click',function(){
        $("#goBuscar").prop("disabled", false);
        $("#goActualizar").prop("disabled", true);
        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
        $('#listaEstudiantesOK').empty();
        $('#divTabla').hide();
        $('#lstannlectivo').focus();
    });

    // Funcionalidad del botón Actualizar
    $('#goActualizar').on('click',function(){
        var accion_ok = 'ActualizarDatosEstudiantes';
        
        var codigo_alumno_ = [], direccion_ = [], telefono_encargado_ = [], telefono_alumno_ = [], telefono_celular_ = [];
        var fila = 0;
        
        $("#tablaDatosEstudiantes tbody tr").each(function(){
            var $row = $(this);
            codigo_alumno_[fila] = $row.find('td').eq(1).data('id-alumno'); // Obtener el ID del estudiante
            direccion_[fila] = $row.find("textarea[name='direccion']").val();
            telefono_encargado_[fila] = $row.find("input[name='telefono_encargado']").val();
            telefono_alumno_[fila] = $row.find("input[name='telefono_alumno']").val();
            telefono_celular_[fila] = $row.find("input[name='telefono_celular']").val();
            fila++;
        });

        $.ajax({
            cache: false,                     
            type: "POST",                     
            dataType: "json",                     
            url: "php_libs/soporte/PhpDatosEstudiantes.php",
            data: {                     
                accion: accion_ok, 
                total_filas: fila,
                codigo_alumno: codigo_alumno_, 
                direccion: direccion_, 
                telefono_encargado: telefono_encargado_,
                telefono_alumno: telefono_alumno_, 
                telefono_celular: telefono_celular_,
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
    $('#formDatosEstudiantes').validate({
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
                url: "php_libs/soporte/PhpDatosEstudiantes.php",
                data: str,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        $('#listaEstudiantesOK').empty();
                        $('#divTabla').hide();
                    } else {
                        toastr.success("Registros encontrados.");
                        $('#listaEstudiantesOK').html(response.contenido);
                        $('.table-responsive').scrollTop(0); // Volver al inicio del scroll
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
                    $('#goBuscar').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar Registros');
                }
            });
            return false;
        },
    });
});
