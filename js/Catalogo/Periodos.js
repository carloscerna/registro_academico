$(document).ready(function () {
    // Inicializar DataTable
    let tabla = $('#tablaPeriodos').DataTable({
        ajax: {
            url: 'php_libs/soporte/Catalogo/Periodos.php',
            type: 'POST',
            data: { accion: 'listar' },
            dataSrc: ''
        },
        columns: [
            { data: 'id' },
            { data: 'nombre_modalidad' },
            { data: 'cantidad_periodos' },
            { data: 'ponderacion_a1' },
            { data: 'ponderacion_a2' },
            { data: 'ponderacion_po' },
            { data: 'fecha_creacion' },
            {
                data: null,
                render: function (data) {
                    return `
                        <button class="btn btn-sm btn-primary btnEditar" data-id="${data.id}">Editar</button>
                        <button class="btn btn-sm btn-danger btnEliminar" data-id="${data.id}">Eliminar</button>
                    `;
                }
            }
        ],
        language: {
            url: 'php_libs/idioma/es_es.json'
        }
    });

    // Cargar modalidades en el select
    function cargarModalidades() {
        $.post('includes/cargar-bachillerato.php', { codigo_ann_lectivo: '2024' }, function (data) {
            let opciones = '<option value="">Seleccione</option>';
            data.forEach(m => {
                opciones += `<option value="${m.codigo_bachillerato}">${m.nombre_bachillerato}</option>`;
            });
            $('#codigo_modalidad').html(opciones);
        }, 'json');
    }

    cargarModalidades();

    // Guardar o actualizar
    $('#formCatalogoPeriodos').submit(function (e) {
        e.preventDefault();

        const datos = $(this).serialize() + '&accion=guardar';

        $.post('includes/catalogo_periodos_crud.php', datos, function (res) {
            if (res.success) {
                Swal.fire('Éxito', res.message, 'success');
                tabla.ajax.reload();
                $('#formCatalogoPeriodos')[0].reset();
                $('#id').val('');
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    // Botón limpiar
    $('#btnLimpiar').click(function () {
        $('#formCatalogoPeriodos')[0].reset();
        $('#id').val('');
    });

    // Editar
    $('#tablaPeriodos tbody').on('click', '.btnEditar', function () {
        const id = $(this).data('id');

        $.post('includes/catalogo_periodos_crud.php', { accion: 'obtener', id }, function (res) {
            if (res.success) {
                const d = res.data;
                $('#id').val(d.id);
                $('#codigo_modalidad').val(d.codigo_modalidad);
                $('#cantidad_periodos').val(d.cantidad_periodos);
                $('#ponderacion_a1').val(d.ponderacion_a1);
                $('#ponderacion_a2').val(d.ponderacion_a2);
                $('#ponderacion_po').val(d.ponderacion_po);
                $('html, body').animate({ scrollTop: 0 }, 300);
            } else {
                Swal.fire('Error', res.message, 'error');
            }
        }, 'json');
    });

    // Eliminar
    $('#tablaPeriodos tbody').on('click', '.btnEliminar', function () {
        const id = $(this).data('id');

        Swal.fire({
            title: '¿Eliminar registro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('includes/catalogo_periodos_crud.php', { accion: 'eliminar', id }, function (res) {
                    if (res.success) {
                        Swal.fire('Eliminado', res.message, 'success');
                        tabla.ajax.reload();
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                }, 'json');
            }
        });
    });
});
