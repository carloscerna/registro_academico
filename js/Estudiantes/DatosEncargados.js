$(function(){
// Funcionalidad del botón Cancelar
$('#goCancelar').on('click',function(){
    $('#accion_buscar').val('BuscarLista');
    $("#goBuscar").prop("disabled", false);
    $("#goActualizar").prop("disabled", true);
    $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
    $('#listaPnOK').empty();
    $('#tabstabla').hide();
    $('#lstannlectivo').focus();
});

// Funcionalidad del botón Actualizar
$('#goActualizar').on('click',function(){
    $('#accion_buscar').val('ActualizarDatosEncargados');
    var accion_ok = 'ActualizarDatosEncargados';
    
    // Arrays para almacenar todos los datos de la tabla
    var codigo_alumno_ = [];
    // Datos Padre
    var id_p_ = [], nombres_p_ = [], dui_p_ = [], chkencargado_p_ = [], telefono_p_ = [], fecha_n_p_ = [], genero_p_ = [], familiar_p_ = [];
    // Datos Madre
    var id_m_ = [], nombres_m_ = [], dui_m_ = [], chkencargado_m_ = [], telefono_m_ = [], fecha_n_m_ = [], genero_m_ = [], familiar_m_ = [];
    // Datos Otro
    var id_o_ = [], nombres_o_ = [], dui_o_ = [], chkencargado_o_ = [], telefono_o_ = [], fecha_n_o_ = [], genero_o_ = [], familiar_o_ = [];
    
    var fila = 0;
    
    // Recorrer el cuerpo de la tabla para extraer los datos
    $("#tablaDatosPn tbody tr").each(function(){
        var $row = $(this);
        codigo_alumno_[fila] = $row.find('td').eq(1).text().trim();

        // Extraer datos del Padre
        var $padreTd = $row.find('td').eq(2);
        id_p_[fila] = $padreTd.find("input[name^='id_p']").val();
        chkencargado_p_[fila] = $padreTd.find("input[type='radio']").is(':checked');
        nombres_p_[fila] = $padreTd.find("input[name^='nombres_p']").val();
        dui_p_[fila] = $padreTd.find("input[name^='dui_p']").val();
        fecha_n_p_[fila] = $padreTd.find("input[name^='fecha_nacimiento_p']").val();
        telefono_p_[fila] = $padreTd.find("input[name^='telefono_p']").val();
        genero_p_[fila] = $padreTd.find("select[name^='genero_p']").val();       // Nuevo
        familiar_p_[fila] = $padreTd.find("select[name^='familiar_p']").val(); // Nuevo

        // Extraer datos de la Madre
        var $madreTd = $row.find('td').eq(3);
        id_m_[fila] = $madreTd.find("input[name^='id_m']").val();
        chkencargado_m_[fila] = $madreTd.find("input[type='radio']").is(':checked');
        nombres_m_[fila] = $madreTd.find("input[name^='nombres_m']").val();
        dui_m_[fila] = $madreTd.find("input[name^='dui_m']").val();
        fecha_n_m_[fila] = $madreTd.find("input[name^='fecha_nacimiento_m']").val();
        telefono_m_[fila] = $madreTd.find("input[name^='telefono_m']").val();
        genero_m_[fila] = $madreTd.find("select[name^='genero_m']").val();       // Nuevo
        familiar_m_[fila] = $madreTd.find("select[name^='familiar_m']").val(); // Nuevo

        // Extraer datos de Otro
        var $otroTd = $row.find('td').eq(4);
        id_o_[fila] = $otroTd.find("input[name^='id_o']").val();
        chkencargado_o_[fila] = $otroTd.find("input[type='radio']").is(':checked');
        nombres_o_[fila] = $otroTd.find("input[name^='nombres_o']").val();
        dui_o_[fila] = $otroTd.find("input[name^='dui_o']").val();
        fecha_n_o_[fila] = $otroTd.find("input[name^='fecha_nacimiento_o']").val();
        telefono_o_[fila] = $otroTd.find("input[name^='telefono_o']").val();
        genero_o_[fila] = $otroTd.find("select[name^='genero_o']").val();       // Nuevo
        familiar_o_[fila] = $otroTd.find("select[name^='familiar_o']").val(); // Nuevo
        
        fila++;
    });

    // Enviar los datos al servidor vía AJAX
    $.ajax({
        beforeSend: function(){
            // Puedes mostrar un spinner de carga aquí si lo deseas
        },
        cache: false,                     
        type: "POST",                     
        dataType: "json",                     
        url: "php_libs/soporte/PhpDatosEncargados.php", // Asegúrate de que el nombre del archivo PHP sea correcto
        data: {                     
            accion: accion_ok, 
            total_filas: fila,
            codigo_alumno: codigo_alumno_,
            // Datos del Padre
            id_p: id_p_, nombres_p: nombres_p_, dui_p: dui_p_, chkencargado_p: chkencargado_p_, telefono_p: telefono_p_, fecha_n_p: fecha_n_p_, genero_p: genero_p_, familiar_p: familiar_p_,
            // Datos de la Madre
            id_m: id_m_, nombres_m: nombres_m_, dui_m: dui_m_, chkencargado_m: chkencargado_m_, telefono_m: telefono_m_, fecha_n_m: fecha_n_m_, genero_m: genero_m_, familiar_m: familiar_m_,
            // Datos de Otro
            id_o: id_o_, nombres_o: nombres_o_, dui_o: dui_o_, chkencargado_o: chkencargado_o_, telefono_o: telefono_o_, fecha_n_o: fecha_n_o_, genero_o: genero_o_, familiar_o: familiar_o_,
        },                     
        success: function(response) {                     
            if (response.respuesta === true) {                     
                toastr.success("Registros actualizados correctamente.");
                $('#goCancelar').click(); // Simular clic en cancelar para resetear la interfaz
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
    errorElement: "em",
    errorPlacement: function (error, element) {
        error.addClass("invalid-feedback");
        error.insertAfter(element);
    },
    highlight: function (element) {
        $(element).addClass("is-invalid").removeClass("is-valid");
    },
    unhighlight: function (element) {
        $(element).addClass("is-valid").removeClass("is-invalid");
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
            url: "php_libs/soporte/PhpDatosEncargados.php",
            data: str,
            success: function(response){
                if(response.respuesta === false){
                    toastr.warning(response.mensaje || "No se encontraron registros.");
                    $('#listaPnOK').empty();
                    $('#tabstabla').hide();
                } else {
                    toastr.success("Registros encontrados.");
                    $('#listaPnOK').html(response.contenido);
					// === LÍNEA AÑADIDA PARA CORREGIR EL SCROLL ===
                    $('.table-responsive').scrollTop(0);
                    
                    $('#tabstabla').show();
                    
                    // Deshabilitar controles de búsqueda y habilitar actualización
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
