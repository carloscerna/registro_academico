$(function(){
    // --- LÓGICA PARA CARGAR SELECTS DEPENDIENTES ---
    // (Esta lógica se mantiene en tus otros archivos JS y no se repite aquí)

    // --- FUNCIONALIDAD DE LOS BOTONES Y FORMULARIOS ---

    // Funcionalidad del botón Cancelar
    $('#goCancelar').on('click',function(){
        $("#goBuscar").prop("disabled", false);
        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
        $('#listaPnFotoOK').empty();
        $('#divTabla').hide();
        $('#lstannlectivo').focus();
    });

    // Búsqueda de registros
    $('#formDatosPnFoto').validate({
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
                url: "php_libs/soporte/PhpDatosPnFoto.php",
                data: str,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        $('#listaPnFotoOK').empty();
                        $('#divTabla').hide();
                    } else {
                        toastr.success("Registros encontrados.");
                        $('#listaPnFotoOK').html(response.contenido);
                        $('.table-responsive').scrollTop(0);
                        $('#divTabla').show();
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

    // --- LÓGICA DEL MODAL DE SUBIDA DE ARCHIVOS ---

    // 1. Abrir y configurar el modal al hacer clic en un botón "Subir"
    $('#listaPnFotoOK').on('click', '.upload-btn', function(){
        var alumnoId = $(this).data('id-alumno');
        var uploadType = $(this).data('type'); // 'foto' o 'pn'
        
        // Configurar el formulario del modal
        $('#alumnoId').val(alumnoId);
        $('#uploadType').val(uploadType);
        
        // Ajustar el título y el tipo de archivo aceptado
        if(uploadType === 'foto'){
            $('#uploadModalLabel').text('Subir Fotografía del Estudiante');
            $('#fileInput').attr('accept', 'image/jpeg, image/png, image/gif');
        } else {
            $('#uploadModalLabel').text('Subir Partida de Nacimiento (PDF)');
            $('#fileInput').attr('accept', '.pdf');
        }
        
        // Resetear el formulario y la barra de progreso
        $('#uploadForm')[0].reset();
        $('.progress').hide();
        $('.progress-bar').css('width', '0%').text('0%');
        
        // Abrir el modal
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
        uploadModal.show();
    });

    // 2. Manejar el envío del formulario de subida de archivos
    $('#uploadForm').on('submit', function(e){
        e.preventDefault();
        
        var formData = new FormData(this);
        var progressBar = $('.progress-bar');
        var progressContainer = $('.progress');

        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total;
                        percentComplete = parseInt(percentComplete * 100);
                        progressBar.width(percentComplete + '%');
                        progressBar.text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            beforeSend: function() {
                progressContainer.show();
                progressBar.width('0%');
            },
            cache: false,
            type: 'POST',
            url: 'php_libs/soporte/PhpDatosPnFoto.php',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response){
                if(response.respuesta === true){
                    toastr.success(response.mensaje || "Archivo subido correctamente.");
                    
                    // Actualizar la imagen en la tabla dinámicamente
                    var alumnoId = $('#alumnoId').val();
                    var uploadType = $('#uploadType').val();
                    
                    if(uploadType === 'foto'){
                        $('#foto-' + alumnoId).attr('src', response.filepath + '?t=' + new Date().getTime());
                    } else {
                        // Para el PDF, simplemente recargamos la fila para mostrar el nuevo enlace/imagen
                        $('#goBuscar').click(); // Simula una nueva búsqueda para refrescar la tabla
                    }
                    
                    // Ocultar el modal
                    var uploadModal = bootstrap.Modal.getInstance(document.getElementById('uploadModal'));
                    uploadModal.hide();

                } else {
                    toastr.error(response.mensaje || "Error al subir el archivo.");
                }
            },
            error: function(){
                toastr.error("Error de comunicación al subir el archivo.");
            },
            complete: function() {
                progressContainer.hide();
            }
        });
    });
});
