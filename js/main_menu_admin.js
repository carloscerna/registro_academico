// Variable global para almacenar el ID del elemento que se está editando (opcional, pero útil)
let currentEditingItemId = null;

// Inicialización de la tabla de DataTables
let menuItemsTable;

$(document).ready(function() {
    // Inicializar la tabla al cargar la página
    initializeDataTable();
    // Cargar elementos del menú y padres al cargar la página
    loadMenuItemsAndParents();
    // Cargar perfiles para la pestaña de permisos
    loadAllProfiles();

    // Manejar el clic en el botón "Nuevo Elemento"
    $('#createMenuItemBtn').on('click', function() {

        // Resetear el formulario
        $('#menuItemForm')[0].reset();
        $('#menuItemId').val(''); // Limpiar el ID oculto para una nueva entrada
        currentEditingItemId = null; // Resetear el ID de edición actual (IMPORTANTE)

        $('#menuItemParent').val(''); // Resetear el select de padres
        $('#menuItemIsActive').prop('checked', true); // Marcar como activo por defecto

        $('#menuItemModalLabel').text('Nuevo Elemento del Menú');
        $('#permissionsTab').hide(); // Ocultar la pestaña de permisos para nuevos elementos
        $('#permissionsTabLink').parent().hide(); // Ocultar el enlace de la pestaña

        // Activar la pestaña de Detalles al abrir el modal de creación
        new bootstrap.Tab(document.getElementById('detailsTabLink')).show();

        // Limpiar las casillas de permisos para un nuevo elemento
        $('#permissionsCheckboxes input.permission-checkbox').prop('checked', false);
    });

    // Manejar el clic en los botones "Editar" de la tabla
    $('#menuItemsTable tbody').on('click', '.edit-btn', function() {
        var data = menuItemsTable.row($(this).parents('tr')).data();
        console.log("DEBUG (Edit Btn Click): Fila seleccionada:", data);
         // === DEPURACIÓN: Verifica el ID de la fila en la consola ===
    console.log("DEBUG (Edit Btn Click): Datos de la fila:", data);
    console.log("DEBUG (Edit Btn Click): ID del elemento a editar:", data.id);
    // =========================================================

        currentEditingItemId = data.id; // Establecer el ID global

        // Rellenar los campos del formulario
        $('#menuItemId').val(data.id);
        $('#menuItemText').val(data.text);
        $('#menuItemIcon').val(data.icon);
        $('#menuItemUrl').val(data.url);
        $('#menuItemParent').val(data.parent_id);
        $('#menuItemOrder').val(data.order_index);
        $('#menuItemIsActive').prop('checked', data.is_active == 1);

        // Establecer el título del modal y mostrar la pestaña de permisos
        $('#menuItemModalLabel').text('Editar Elemento del Menú');
        $('#permissionsTab').show();
        $('#permissionsTabLink').parent().show();

        // Cargar permisos para el elemento seleccionado
        loadPermissions(data.id);

        // Activar la pestaña de Detalles
        new bootstrap.Tab(document.getElementById('detailsTabLink')).show();
    });

    // Manejar el envío del formulario (crear/actualizar)
    $('#menuItemForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData();

        // === CAPTURA EL ID DEL ELEMENTO AQUÍ PARA ACCEDER EN EL CALLBACK ===
        const itemIdToSave = $('#menuItemId').val();

        formData.append('action', itemIdToSave ? 'updateMenuItem' : 'createMenuItem');
        formData.append('id', itemIdToSave);
        formData.append('text', $('#menuItemText').val());
        formData.append('icon', $('#menuItemIcon').val());
        formData.append('url', $('#menuItemUrl').val());
        formData.append('parent_id', $('#menuItemParent').val());
        formData.append('order_index', $('#menuItemOrder').val());
        formData.append('is_active', $('#menuItemIsActive').is(':checked') ? 1 : 0);

        // Recolectar los IDs de los perfiles seleccionados
        const selectedProfileIds = [];
        $('#permissionsCheckboxes input.permission-checkbox:checked').each(function() {
            selectedProfileIds.push($(this).val());
        });

        // Añadir cada ID de perfil seleccionado individualmente a FormData
        // Esto asegura que PHP reciba un array en $_POST['selected_profiles']
        selectedProfileIds.forEach(profileId => {
            formData.append('selected_profiles[]', profileId);
        });

        // Debugging: Imprime el contenido de formData en la consola
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        // Realizar la solicitud AJAX
        $.ajax({
            url: 'php_libs/soporte/menu/phpAjaxMenuItems.inc.php', // El script unificado para ítems y permisos
            type: 'POST',
            data: formData,
            processData: false, // ¡IMPORTANTE! No procesar el FormData
            contentType: false, // ¡IMPORTANTE! No establecer el tipo de contenido (FormData lo hace automáticamente)
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    $('#menuItemModal').modal('hide');
                    loadMenuItemsAndParents(); // Recargar la tabla de elementos del menú

                    // Si necesitas hacer algo con los permisos después de guardar,
                    // y el backend devuelve el nuevo ID para elementos creados (response.newId),
                    // puedes usarlo aquí. De lo contrario, loadMenuItemsAndParents()
                    // debería ser suficiente para refrescar la UI.
                    // Si la línea 261 era loadPermissions(itemId);, ahora itemIdToSave está disponible.
                    // Si ya no es necesaria, puedes eliminarla.
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Error desconocido al guardar.'
                    });
                    console.error("Error al guardar elemento:", response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de comunicación con el servidor al guardar.'
                });
                console.error("AJAX Error al guardar:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });

    // Manejar el clic en los botones "Eliminar" de la tabla
    $('#menuItemsTable tbody').on('click', '.delete-btn', function() {
        var data = menuItemsTable.row($(this).parents('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¡No podrás revertir esto! Eliminarás "${data.text}".`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarlo!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'php_libs/soporte/menu/phpAjaxMenuItems.inc.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'deleteMenuItem',
                        id: data.id
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            loadMenuItemsAndParents();
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: response.message || 'Error al eliminar el elemento.'
                            });
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Toast.fire({
                            icon: 'error',
                            title: 'Error de comunicación con el servidor al eliminar.'
                        });
                        console.error("AJAX Error al eliminar:", textStatus, errorThrown);
                    }
                });
            }
        });
    });
});

// Función para inicializar o re-inicializar la tabla de DataTables
function initializeDataTable() {
    if ($.fn.DataTable.isDataTable('#menuItemsTable')) {
        menuItemsTable.destroy();
    }
    menuItemsTable = $('#menuItemsTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "language": {
            "url": "php_libs/idioma/es_es.json"
        },
        "ajax": {
            "url": "php_libs/soporte/menu/phpAjaxMenuItems.inc.php",
            "type": "POST",
            "data": { "action": "getAllMenuItems" },
            "dataSrc": "data"
        },
        "columns": [
            { "data": "id" },          // Columna 0
            { "data": "text" },        // Columna 1
            {
                "data": "icon",
                "render": function(data, type, row) {
                    // Asumiendo que 'data' es 'home', 'user', etc.
                    // y que quieres usar iconos sólidos de Font Awesome 5
                    if (data) {
                        // Si los datos incluyen el prefijo (ej. 'fas fa-home'), úsalo directamente
                        if (data.startsWith('fa-') || data.startsWith('fas fa-') || data.startsWith('far fa-') || data.startsWith('fab fa-')) {
                            return `<i class="${data}"></i>`;
                        }
                        // Si los datos son solo el nombre del icono (ej. 'home'), añade el prefijo 'fas fa-'
                        return `<i class="fas fa-${data}"></i>`;
                    }
                    return ''; // Si no hay icono
                }
              },
            { "data": "url" },         // Columna 3
            { "data": "parent_id" },   // Columna 4
            { "data": "order_index" }, // Columna 5
            { "data": "is_active",     // Columna 6 (con renderizado para checkbox visual)
              "render": function(data, type, row) {
                    return data == 1 ? '<i class="fas fa-check-circle text-success"></i> Sí' : '<i class="fas fa-times-circle text-danger"></i> No';
                }
            },
            // Las columnas 'created_at' y 'updated_at' no las incluiremos aquí si no se muestran en la tabla.
            // Si necesitas mostrarlas, añade: { "data": "created_at" }, { "data": "updated_at" }
            { "data": null,           // Columna 7: Acciones (botones)
              "defaultContent": '<div class="btn-group" role="group" aria-label="Acciones">' +
                                  '<button type="button" class="btn btn-warning btn-sm edit-btn" data-bs-toggle="modal" data-bs-target="#menuItemModal"><i class="fas fa-edit"></i></button>' +
                                  '<button type="button" class="btn btn-danger btn-sm delete-btn"><i class="fas fa-trash"></i></button>' +
                                '</div>'
            }
        ],
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Deshabilita el ordenamiento en la columna de Acciones (índice 7)
        ]
    });
}

// Cargar elementos del menú y poblar la tabla
function loadMenuItemsAndParents() {
    $.ajax({
        url: 'php_libs/soporte/menu/phpAjaxMenuItems.inc.php',
        type: 'POST',
        dataType: 'json',
        data: { action: 'getMenuItemsAndParents' },
        success: function(response) {
            const $selectParent = $('#menuItemParent');
            $selectParent.empty(); // Limpiar opciones existentes
            $selectParent.append('<option value="">-- Sin Padre --</option>'); // Opción por defecto (para items de nivel superior)

            if (response.success && response.data) {
                $.each(response.data, function(index, item) {
                    // Si estamos editando, excluimos el elemento actual de la lista de padres
                    // para evitar que un elemento sea padre de sí mismo.
                    if (item.id != currentEditingItemId) {
                        $selectParent.append($('<option>', {
                            value: item.id,
                            text: item.text
                        }));
                    }
                });
            } else {
                console.error("Error al cargar elementos del menú para select: " + (response.message || 'Respuesta inesperada del servidor.'));
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al cargar elementos del menú.'
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error en la solicitud AJAX para cargar elementos padre:", textStatus, errorThrown, jqXHR.responseText);
            Toast.fire({
                icon: 'error',
                title: 'Error de red o servidor al cargar elementos padre.'
            });
        }
    });
}

// Función para poblar el select de elementos padres
function populateParentSelect(menuItems) {
    const parentSelect = $('#menuItemParent');
    parentSelect.empty();
    parentSelect.append($('<option>', {
        value: '',
        text: '--- Ninguno (Es un Padre) ---'
    })); // Opción por defecto

    menuItems.forEach(item => {
        // No permitir que un elemento sea su propio padre
        if (item.id !== currentEditingItemId) {
            parentSelect.append($('<option>', {
                value: item.id,
                text: item.text
            }));
        }
    });

    // Si se está editando un elemento, seleccionar su padre actual
    if (currentEditingItemId !== null) {
        const currentItemData = menuItems.find(item => item.id == currentEditingItemId);
        if (currentItemData && currentItemData.parent_id) {
            parentSelect.val(currentItemData.parent_id);
        }
    }
}

// Función para cargar todos los perfiles disponibles
function loadAllProfiles() {
    $.ajax({
        url: 'php_libs/soporte/menu/phpAjaxMenuPermissions.inc.php', // Utiliza el script de permisos solo para cargar perfiles
        type: 'GET',
        dataType: 'json',
        data: { action: 'getAllProfiles' },
        success: function(response) {
            if (response.success) {
                const permissionsCheckboxes = $('#permissionsCheckboxes');
                permissionsCheckboxes.empty(); // Limpiar checkboxes existentes

                response.data.forEach(profile => {
                    const checkboxHtml = `
                        <div class="form-check">
                            <input class="form-check-input permission-checkbox" type="checkbox" value="${profile.codigo_perfil}" id="profileCheck${profile.id_perfil}">
                            <label class="form-check-label" for="profileCheck${profile.id_perfil}">
                                ${profile.nombre_perfil} (${profile.codigo_perfil})
                            </label>
                        </div>
                    `;
                    permissionsCheckboxes.append(checkboxHtml);
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al cargar perfiles.'
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Toast.fire({
                icon: 'error',
                title: 'Error de comunicación al cargar perfiles.'
            });
            console.error("AJAX Error al cargar perfiles:", textStatus, errorThrown);
        }
    });
}

// Función para cargar los permisos de un elemento de menú específico
function loadPermissions(menuItemId) {
    // Asegurarse de que todas las casillas estén desmarcadas primero
    $('#permissionsCheckboxes input.permission-checkbox').prop('checked', false);

    $.ajax({
        url: 'php_libs/soporte/menu/phpAjaxMenuPermissions.inc.php', // Script de permisos
        type: 'GET',
        dataType: 'json',
        data: {
            action: 'getPermissionsByMenuItem',
            menu_item_id: menuItemId
        },
        success: function(response) {
            if (response.success) {
                response.data.forEach(profileId => {
                    // Marcar las casillas correspondientes a los perfiles asignados
                    $(`#permissionsCheckboxes input.permission-checkbox[value="${profileId}"]`).prop('checked', true);
                });
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'Error al cargar permisos del elemento.'
                });
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Toast.fire({
                icon: 'error',
                title: 'Error de comunicación al cargar permisos.'
            });
            console.error("AJAX Error al cargar permisos:", textStatus, errorThrown);
        }
    });
}