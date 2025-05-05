$(document).ready(function () {
    let tabla = $('#tablaPeriodos').DataTable({
        ajax: {
            url: 'php_libs/soporte/Catalogo/Periodos.php',
            type: 'POST',
            data: { action: 'listar' },
            dataSrc: ''
        },
        columns: [
            { data: 'id' },
            { data: 'modalidad' },
            { data: 'cantidad_periodos' },
            { data: 'a1' },
            { data: 'a2' },
            { data: 'po' },
            {
                data: null,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-warning btnEditar" data-id="${data.id}">Editar</button>
                        <button class="btn btn-sm btn-danger btnEliminar" data-id="${data.id}">Eliminar</button>
                    `;
                }
            }
        ]
    });

    let modal = new bootstrap.Modal(document.getElementById('modalPeriodo'));
    let form = $('#formPeriodo')[0];

    $('#btnAgregar').click(() => {
        form.reset();
        $('#id').val('');
        modal.show();
    });

    $(document).ready(function() {
        // Cargar modalidades al abrir el modal
        $('#modalPeriodo').on('show.bs.modal', function() {
            $.ajax({
                url: 'includes/cargar-bachillerato.php',
                method: 'GET',
                success: function(data) {
                    $('#lstmodalidad').html(data);  // Poner las opciones en el select
                }
            });
        });
    
        // Validar el formulario al enviarlo
        $('#formPeriodo').on('submit', function(event) {
            event.preventDefault(); // Evitar la recarga de la página
    
            let a1 = parseFloat($('#a1').val());
            let a2 = parseFloat($('#a2').val());
            let po = parseFloat($('#po').val());
    
            // Validación de los porcentajes
            if (a1 < 0 || a1 > 35 || a2 < 0 || a2 > 35 || po < 0 || po > 30) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Los porcentajes no son válidos. A1 debe ser ≤ 35%, A2 ≤ 35% y PO ≤ 30%.'
                });
                return;
            }
    
            // Validar la suma de los porcentajes
            if ((a1 + a2 + po) > 100) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La suma de A1, A2 y PO no puede ser mayor que 100%.'
                });
                return;
            }
    
            // Enviar formulario si la validación es correcta
            $.ajax({
                url: 'php_libs/soporte/Catalogo/Periodos.php',
                method: 'POST',
                data: $(this).serialize() + '&action=guardar',
                success: function(response) {
                    let res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: res.message
                        }).then(function() {
                            $('#modalPeriodo').modal('hide');  // Cerrar el modal
                            // Actualizar solo la tabla DataTables
                            $('#dataTable').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al guardar los datos.'
                    });
                }
            });
        });
    });
    

    // Editar
    $('#tablaPeriodos').on('click', '.btnEditar', function () {
        const id = $(this).data('id');

        $.post('php_libs/soporte/Catalogo/Periodos.php', { action: 'obtener', id }, function (response) {
            const data = JSON.parse(response);
            if (data.success) {
                const p = data.data;
                $('#id').val(p.id);
                $('#modalidad').val(p.modalidad);
                $('#cantidad_periodos').val(p.cantidad_periodos);
                $('#a1').val(p.a1);
                $('#a2').val(p.a2);
                $('#po').val(p.po);
                modal.show();
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        });
    });

    // Eliminar
    $('#tablaPeriodos').on('click', '.btnEliminar', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                $.post('php_libs/soporte/Catalogo/Periodos.php', { action: 'eliminar', id }, function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        Swal.fire('Eliminado', res.message, 'success');
                        tabla.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                });
            }
        });
    });
});
