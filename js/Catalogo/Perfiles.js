// Variable global para la instancia de DataTables
let profilesTable;

$(document).ready(function() {
    // Inicialización de DataTables
    profilesTable = $('#profilesTable').DataTable({
        "processing": true, // Mostrar indicador de procesamiento
        "serverSide": false, // False si todos los datos se cargan de una vez
        "ajax": {
            "url": "php_libs/soporte/catalogo/phpAjaxCatalogoPerfiles.inc.php",
            "type": "POST",
            "data": { "action": "getAllProfiles" }, // Acción para obtener todos los perfiles
            "dataSrc": "data" // La propiedad en la respuesta JSON que contiene los datos
        },
        "columns": [
            { "data": "id_perfil" },
            { "data": "codigo" },
            { "data": "descripcion" },
            { 
                "data": "is_active",
                "render": function(data, type, row) {
                    return data ? '<span class="badge badge-success">Sí</span>' : '<span class="badge badge-danger">No</span>';
                }
            },
            {
                "data": null,
                "orderable": false, // Deshabilitar ordenación para esta columna
                "render": function(data, type, row) {
                    return `
                        <button class="btn btn-info btn-sm edit-btn" data-id="${row.id_perfil}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm delete-btn" data-id="${row.id_perfil}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        "responsive": true,
        "autoWidth": false,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Spanish.json" // Idioma español
        },
        "order": [[1, "asc"]] // Ordenar por la columna "Código" por defecto
    });

    // Inicialización de Toast de SweetAlert2
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    // --- Eventos del Formulario y Modal ---

    // Manejar clic en botón "Nuevo Perfil"
    $('#createProfileBtn').on('click', function() {
        $('#profileForm')[0].reset(); // Resetear el formulario
        $('#profileId').val(''); // Limpiar el ID oculto
        $('#profileCodigo').prop('readonly', true);
        $('#profileModalLabel').text('Nuevo Perfil'); // Cambiar título del modal
        $('#profileIsActive').prop('checked', true); // Activo por defecto


        // === NUEVO: Obtener y rellenar el siguiente código ===
        $.ajax({
            url: 'php_libs/soporte/catalogo/phpAjaxCatalogoPerfiles.inc.php',
            type: 'POST',
            dataType: 'json',
            data: { "action": "getNextProfileCodigo" },
            success: function(response) {
                if (response.success && response.next_codigo) {
                    $('#profileCodigo').val(response.next_codigo);
                    // Opcional: Hacer el campo de código de solo lectura si es autogenerado
                    // $('#profileCodigo').prop('readonly', true);
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error al obtener el siguiente código.'
                    });
                    console.error("Error al obtener next_codigo:", response.message || 'Respuesta inesperada.');
                    $('#profileCodigo').val(''); // Asegurarse de que esté vacío si hay error
                    // $('#profileCodigo').prop('readonly', false); // Si lo hiciste readonly
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de red al obtener siguiente código.'
                });
                console.error("AJAX Error al obtener next_codigo:", textStatus, errorThrown, jqXHR.responseText);
                $('#profileCodigo').val(''); // Asegurarse de que esté vacío si hay error
                // $('#profileCodigo').prop('readonly', false); // Si lo hiciste readonly
            }
        });
        // ===================================================


        $('#profileModal').modal('show'); // Mostrar el modal
    });

    // Manejar envío del formulario (Crear/Editar)
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize(); // Serializar los datos del formulario

        $.ajax({
            url: 'php_libs/soporte/catalogo/phpAjaxCatalogoPerfiles.inc.php',
            type: 'POST',
            dataType: 'json',
            data: formData + '&action=saveProfile', // Añadir la acción de guardar
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    $('#profileModal').modal('hide'); // Ocultar modal
                    profilesTable.ajax.reload(null, false); // Recargar DataTables
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Error desconocido al guardar.'
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de comunicación con el servidor.'
                });
                console.error("AJAX Error al guardar perfil:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });

    // Manejar clic en botón "Editar" (delegación de eventos)
    $('#profilesTable tbody').on('click', '.edit-btn', function() {
        const profileId = $(this).data('id');

        $.ajax({
            url: 'php_libs/soporte/catalogo/phpAjaxCatalogoPerfiles.inc.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'getProfileById', id_perfil: profileId },
            success: function(response) {
                if (response.success && response.data) {
                    const profile = response.data;
                    $('#profileId').val(profile.id_perfil);
                    $('#profileCodigo').val(profile.codigo);
                    $('#profileDescripcion').val(profile.descripcion);
                    $('#profileIsActive').prop('checked', profile.is_active);

                    $('#profileModalLabel').text('Editar Perfil'); // Cambiar título del modal
                    $('#profileModal').modal('show'); // Mostrar modal con los datos cargados
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Perfil no encontrado.'
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de comunicación al cargar perfil.'
                });
                console.error("AJAX Error al obtener perfil:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });

    // Manejar clic en botón "Eliminar" (delegación de eventos)
    $('#profilesTable tbody').on('click', '.delete-btn', function() {
        const profileId = $(this).data('id');

        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esto!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'php_libs/soporte/catalogo/phpAjaxCatalogoPerfiles.inc.php',
                    type: 'POST',
                    dataType: 'json',
                    data: { action: 'deleteProfile', id_perfil: profileId },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            profilesTable.ajax.reload(null, false); // Recargar DataTables
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Error al eliminar perfil.'
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Error de comunicación con el servidor.'
                        });
                        console.error("AJAX Error al eliminar perfil:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            }
        });
    });
});