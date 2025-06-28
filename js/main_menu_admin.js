$(document).ready(function() {
    let menuItemsTable; // Variable para la instancia de DataTables
    let allMenuItems = []; // Para almacenar todos los items y poblar el select de padres

    // Inicializar SweetAlert2 por si no está ya configurado globalmente
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    // Función para cargar los elementos del menú y poblar el select de padres
    function loadMenuItemsAndParents() {
        $.ajax({
            url: 'php_libs/soporte/menu/phpAjaxMenuItems.inc.php',
            type: 'GET',
            data: { action: 'getAllMenuItems' },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    allMenuItems = response.data; // Guardar todos los items
                    populateParentSelect(allMenuItems); // Poblar el select de padres
                    if (menuItemsTable) {
                        menuItemsTable.clear().rows.add(allMenuItems).draw(); // Recargar DataTables
                    } else {
                        initializeDataTable(allMenuItems); // Inicializar DataTables por primera vez
                    }
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Error al cargar los elementos del menú.'
                    });
                    console.error("Error al cargar menú:", response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de comunicación con el servidor al cargar el menú.'
                });
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    }

    // Función para poblar el select de "Elemento Padre"
    function populateParentSelect(items, currentItemId = null) {
        const $select = $('#menuItemParent');
        $select.empty();
        $select.append('<option value="">-- Sin Padre (Menú Principal) --</option>');

        items.forEach(item => {
            // No permitir que un elemento sea padre de sí mismo
            // Y no permitir que un elemento sea padre de uno de sus descendientes (simplificado)
            if (item.id != currentItemId) {
                $select.append(`<option value="${item.id}">${item.text}</option>`);
            }
        });
    }

    // Función para inicializar DataTables
    function initializeDataTable(data) {
        menuItemsTable = $('#menuItemsTable').DataTable({
            data: data,
            columns: [
                { data: 'id' },
                { data: 'text' },
                { data: 'icon' },
                { data: 'url' },
                {
                    data: 'parent_id',
                    render: function(data, type, row) {
                        if (data) {
                            const parent = allMenuItems.find(item => item.id == data);
                            return parent ? parent.text : 'N/A';
                        }
                        return 'N/A';
                    }
                },
                { data: 'order_index' },
                {
                    data: 'is_active',
                    render: function(data, type, row) {
                        return data ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-danger">No</span>';
                    }
                },
                {
                    data: null,
                    defaultContent: `
                        <button class="btn btn-sm btn-info edit-btn" data-bs-toggle="modal" data-bs-target="#menuItemModal"><i class="fas fa-edit"></i> Editar</button>
                        <button class="btn btn-sm btn-danger delete-btn"><i class="fas fa-trash"></i> Eliminar</button>
                    `,
                    orderable: false,
                    searchable: false
                }
            ],
            responsive: true,
            lengthChange: false,
            autoWidth: false,
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"]
        });
    }

    // Cargar los datos iniciales al cargar la página
    loadMenuItemsAndParents();

    // Evento para el botón "Nuevo Elemento"
    $('#btnNewMenuItem').on('click', function() {
        $('#menuItemModalLabel').text('Nuevo Elemento de Menú');
        $('#menuItemForm')[0].reset(); // Limpiar el formulario
        $('#menuItemId').val(''); // Asegurarse de que el ID esté vacío para creación
        $('#menuItemIsActive').prop('checked', true); // Por defecto activo
        populateParentSelect(allMenuItems); // Recargar el select de padres
    });

    // Evento para los botones "Editar" (delegado porque los botones se añaden dinámicamente)
    $('#menuItemsTable tbody').on('click', '.edit-btn', function() {
        const data = menuItemsTable.row($(this).parents('tr')).data();
        $('#menuItemModalLabel').text('Editar Elemento de Menú');
        $('#menuItemId').val(data.id);
        $('#menuItemText').val(data.text);
        $('#menuItemIcon').val(data.icon);
        $('#menuItemUrl').val(data.url);
        $('#menuItemOrder').val(data.order_index);
        $('#menuItemIsActive').prop('checked', data.is_active);

        // Poblar el select de padres, excluyendo el propio elemento que se está editando
        populateParentSelect(allMenuItems, data.id);
        $('#menuItemParent').val(data.parent_id); // Seleccionar el padre actual
    });

    // Evento para los botones "Eliminar" (delegado)
    $('#menuItemsTable tbody').on('click', '.delete-btn', function() {
        const data = menuItemsTable.row($(this).parents('tr')).data();
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¡No podrás revertir la eliminación de "${data.text}"!`,
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
                    data: { action: 'deleteMenuItem', id: data.id },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Toast.fire({
                                icon: 'success',
                                title: response.message
                            });
                            loadMenuItemsAndParents(); // Recargar la tabla y padres
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
                            title: 'Error de comunicación al eliminar.'
                        });
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            }
        });
    });

    // Manejar el envío del formulario (Crear/Editar)
    $('#menuItemForm').on('submit', function(e) {
        e.preventDefault(); // Evitar el envío normal del formulario

        const formData = new FormData(this);
        const itemId = $('#menuItemId').val();
        const action = itemId ? 'updateMenuItem' : 'createMenuItem'; // Determinar si es crear o actualizar
        formData.append('action', action);

        // Asegurarse de que el checkbox de is_active siempre envíe un valor (0 o 1)
        if (!$('#menuItemIsActive').is(':checked')) {
            formData.set('is_active', '0');
        } else {
            formData.set('is_active', '1');
        }
        
        // Si parent_id es vacío, enviar como null
        if (formData.get('parent_id') === '') {
            formData.set('parent_id', ''); // El backend lo convertirá a null
        }

        $.ajax({
            url: 'php_libs/soporte/menu/phpAjaxMenuItems.inc.php',
            type: 'POST',
            data: formData,
            processData: false, // Importante para FormData
            contentType: false, // Importante para FormData
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Toast.fire({
                        icon: 'success',
                        title: response.message
                    });
                    $('#menuItemModal').modal('hide'); // Cerrar el modal
                    loadMenuItemsAndParents(); // Recargar la tabla y padres
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: response.message || 'Error al guardar el elemento.'
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Toast.fire({
                    icon: 'error',
                    title: 'Error de comunicación al guardar.'
                });
                console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
            }
        });
    });

    // Asegurarse de que el modal se cierre correctamente con Bootstrap 5
    // Ya no se usa data-dismiss, ahora es data-bs-dismiss
    // No se necesita JS adicional para cerrar el modal si usas data-bs-dismiss="modal" en los botones.
    // Si necesitas hacer algo al cerrar el modal:
    $('#menuItemModal').on('hidden.bs.modal', function () {
        $('#menuItemForm')[0].reset();
        $('#menuItemId').val('');
        $('#menuItemIsActive').prop('checked', true);
        populateParentSelect(allMenuItems); // Asegurarse de que el select de padres se resetee
    });
});