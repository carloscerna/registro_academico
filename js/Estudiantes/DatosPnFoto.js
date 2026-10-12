$(function(){
    let tablaDataTable = null;
    let cropper = null; // Instancia global de Cropper.js

    // --- MANEJO DE BUSQUEDA Y DATATABLE (Mantiene la estructura previa) ---
    $('#goCancelar').on('click', function(){
        $("#goBuscar").prop("disabled", false);
        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
        if (tablaDataTable) {
            tablaDataTable.destroy();
            tablaDataTable = null;
        }
        $('#tablaDatosPnFoto tbody').empty();
        $('#divTabla').hide();
        $('#lstannlectivo').focus();
    });

    $('#formDatosPnFoto').validate({
        rules: {
            lstannlectivo: { required: true },
            lstmodalidad: { required: true },
            lstgradoseccion: { required: true }
        },
        submitHandler: function(form){
            var str = $(form).serialize();$.ajax({
                beforeSend: function(){
                    $('#goBuscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
                },
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/PhpDatosPnFoto.php",
                data: str,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        if (tablaDataTable) tablaDataTable.clear().draw();
                        $('#divTabla').hide();
                    } else {
                        toastr.success("Registros encontrados.");
                        $('#divTabla').show();
                        cargarDataTable(response.data);
                    }
                },
                error: function(){
                   toastr.error("Error AJAX al buscar registros.");
                },
                complete: function() {
                    $('#goBuscar').prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                }
            });
            return false;
        }
    });

    function cargarDataTable(dataset) {
        if (tablaDataTable) tablaDataTable.destroy();

        tablaDataTable = $('#tablaDatosPnFoto').DataTable({
            data: dataset,
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
            columns: [
                { 
                    data: null, 
                    className: "text-center align-middle",
                    render: (data, type, row, meta) => meta.row + 1
                },
                { 
                    data: null,
                    className: "align-middle",
                    render: (data) => `<strong>${data.id_alumno}</strong> - ${data.nie} - ${data.apellido_alumno}`
                },
                { 
                    data: "pn_info",
                    className: "text-center align-middle",
                    render: function (pn, type, row) {
                        let html = '';
                        if (pn.existe) {
                            html += (pn.tipo === 'pdf')
                                ? `<a href="${pn.url}" target="_blank" class="me-2"><img src="img/pdf_icon.png" class="pn-preview" alt="PDF"></a>`
                                : `<a href="${pn.url}" target="_blank" class="me-2"><img src="${pn.url}" class="pn-preview" alt="Partida"></a>`;
                        } else {
                            html += `<img src="img/NoDisponible.jpg" class="pn-preview me-2" alt="No disponible">`;
                        }
                        html += `<button class="btn btn-sm btn-outline-primary upload-btn" data-id-alumno="${row.id_alumno}" data-type="pn"><i class="fas fa-upload"></i> Subir</button>`;
                        return html;
                    }
                },
                { 
                    data: "foto_info",
                    className: "text-center align-middle",
                    render: function (foto, type, row) {
                        return `
                            <img id="foto-${row.id_alumno}" src="${foto.url}" class="student-photo me-2" alt="Foto">
                            <button class="btn btn-sm btn-outline-secondary upload-btn" data-id-alumno="${row.id_alumno}" data-type="foto"><i class="fas fa-upload"></i> Subir</button>
                        `;
                    }
                }
            ]
        });
    }

    // --- CONFIGURACIÓN DEL MODAL DE SUBIDA ---
    $('#tablaDatosPnFoto').on('click', '.upload-btn', function(){
        var alumnoId = $(this).data('id-alumno');
        var uploadType = $(this).data('type');$('#alumnoId').val(alumnoId);
        $('#uploadType').val(uploadType);
        
        // Limpiar visor de corte previo
        destruirCropper();
        $('#uploadForm')[0].reset();
        $('#cropperWrapper').hide();
        $('.progress').hide();
        
        if (uploadType === 'foto') {
            $('#uploadModalLabel').text('Subir / Recortar Fotografía del Estudiante');
            $('#fileInput').attr('accept', 'image/jpeg, image/png, image/gif');
            $('#fileHelpText').text('Seleccione una imagen (JPG, PNG). Podrá recortarla antes de guardar.');
        } else {
            $('#uploadModalLabel').text('Subir Partida de Nacimiento');
            $('#fileInput').attr('accept', '.pdf, image/jpeg, image/png');
            $('#fileHelpText').text('Formatos permitidos: PDF, JPG, PNG.');
        }
        
        var uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
        uploadModal.show();
    });

    // Destruir instancia de Cropper si existe
    function destruirCropper() {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
    }

    // Limpiar al cerrar el modal
    $('#uploadModal').on('hidden.bs.modal', function () {
        destruirCropper();
        $('#cropperWrapper').hide();
    });

    // --- DETECTAR SELECCIÓN DE ARCHIVO Y ACTIVAR CROPPER ---
    $('#fileInput').on('change', function(e) {
        var files = e.target.files;
        var uploadType = $('#uploadType').val();

        destruirCropper();

        if (files && files.length > 0) {
            var file = files[0];
            
            // Si es fotografía (o imagen de Partida de Nacimiento), inicializamos la vista previa interactiva
            if (file.type.match(/^image\//)) {
                var reader = new FileReader();
                reader.onload = function(evt) {
                    $('#imageToCrop').attr('src', evt.target.result);
                    $('#cropperWrapper').show();

                    var imageElement = document.getElementById('imageToCrop');
                    
                    // Inicializar Cropper.js
                    cropper = new Cropper(imageElement, {
                        aspectRatio: (uploadType === 'foto') ? 3 / 4 : NaN, // Aspect ratio de foto carnet para fotos (3:4)
                        viewMode: 1,
                        autoCropArea: 0.9,
                        responsive: true,
                        restore: false
                    });
                };
                reader.readAsDataURL(file);
            } else {
                // Si es un archivo PDF
                $('#cropperWrapper').hide();
            }
        }
    });

    // CONTROLES DE EDICIÓN
    $('#btnRotateLeft').on('click', () => cropper && cropper.rotate(-90));
    $('#btnRotateRight').on('click', () => cropper && cropper.rotate(90));
    $('#btnZoomIn').on('click', () => cropper && cropper.zoom(0.1));
    $('#btnZoomOut').on('click', () => cropper && cropper.zoom(-0.1));
    $('#btnResetCrop').on('click', () => cropper && cropper.reset());

    // --- ENVÍO DE DATOS (CON RECORTES APLICADOS) ---
    $('#uploadForm').on('submit', function(e){
        e.preventDefault();
        
        var uploadType = $('#uploadType').val();
        var formData = new FormData(this);
        
        // Si hay una imagen activa en el Cropper, extraemos el recorte recortado
        if (cropper && $('#cropperWrapper').is(':visible')) {
            var canvas = cropper.getCroppedCanvas({
                width: 300, // Ancho estándar optimizado para carnet
                height: 400,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            canvas.toBlob(function(blob) {
                // Reemplazamos el archivo original por el archivo recortado
                formData.set('file_to_upload', blob, 'foto_recortada.jpg');
                enviarFormularioAjax(formData);
            }, 'image/jpeg', 0.9);
        } else {
            // Envío normal de archivos (PDFs o imágenes sin recortar)
            enviarFormularioAjax(formData);
        }
    });

    function enviarFormularioAjax(formData) {
        var progressBar = $('.progress-bar');
        var progressContainer = $('.progress');

        $.ajax({
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percent = parseInt((evt.loaded / evt.total) * 100);
                        progressBar.width(percent + '%').text(percent + '%');
                    }
                }, false);
                return xhr;
            },
            beforeSend: function() {
                progressContainer.show();
                progressBar.width('0%').text('0%');
                $('#btnSaveUpload').prop('disabled', true);
            },
            type: 'POST',
            url: 'php_libs/soporte/PhpDatosPnFoto.php',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response){
                if (response.respuesta === true) {
                    toastr.success(response.mensaje || "Archivo guardado correctamente.");
                    
                    var uploadModalEl = document.getElementById('uploadModal');
                    var modalInstance = bootstrap.Modal.getInstance(uploadModalEl);
                    if (modalInstance) modalInstance.hide();
                    
                    // Actualizar tabla dinámicamente simulando click de búsqueda
                    $('#formDatosPnFoto').submit();
                } else {
                    toastr.error(response.mensaje || "Error al subir el archivo.");
                }
            },
            error: function(){
                toastr.error("Error de conexión al procesar la solicitud.");
            },
            complete: function() {
                progressContainer.hide();
                $('#btnSaveUpload').prop('disabled', false);
            }
        });
    }
});