function abrirSubdimension(codigo_area, codigo_dimension) {
    $('#codigo_area_sub').val(codigo_area);
    $('#codigo_dimension_sub').val(codigo_dimension);
    $('#id_sub').val('');
    $('#descripcion_sub').val('');
    cargarSiguienteCodigoSub(codigo_area, codigo_dimension);

    $('#modalSubdimension').modal('show');

    tablaSubdimension = $('#tablaSubdimension').DataTable({
        destroy: true,
        ajax: {
            url: 'php_libs/soporte/Catalogo/subdimension.php',
            type: 'POST',
            data: {
                accion: 'listar',
                codigo_area: codigo_area,
                codigo_dimension: codigo_dimension
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'codigo' },
            { data: 'descripcion' },
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-info" onclick="editarSubdimension(${data.id_}, '${data.descripcion}')">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarSubdimension(${data.id_})">Eliminar</button>
                    `;
                }
            }
        ]
    });
}

function cargarSiguienteCodigoSub(area, dimension) {
    $.post('php_libs/soporte/catalogo/subdimension.php', {
        accion: 'siguiente_codigo',
        codigo_area: area,
        codigo_dimension: dimension
    }, function(res) {
        $('#codigo_sub').val(res.codigo);
    }, 'json');
}

function guardarSubdimension() {
    $.ajax({
        url: 'php_libs/soporte/catalogo/subdimension.php',
        type: 'POST',
        data: $('#formSubdimension').serialize() + '&accion=guardar',
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                tablaSubdimension.ajax.reload();
                $('#descripcion_sub').val('');
                cargarSiguienteCodigoSub($('#codigo_area_sub').val(), $('#codigo_dimension_sub').val());
                Swal.fire('Guardado', 'Registro guardado correctamente', 'success');
            } else {
                Swal.fire('Error', res.message || 'No se pudo guardar', 'error');
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseText, 'error');
        }
    });
}

function editarSubdimension(id, descripcion) {
    $('#id_sub').val(id);
    $('#descripcion_sub').val(descripcion);
}

function eliminarSubdimension(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: 'El registro se eliminará permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('php_libs/soporte/catalogo/subdimension.php', {accion: 'eliminar', id_: id}, function(res) {
                if (res.status === 'success') {
                    tablaSubdimension.ajax.reload();
                    Swal.fire('Eliminado', 'Registro eliminado correctamente', 'success');
                } else {
                    Swal.fire('Error', res.message || 'No se pudo eliminar', 'error');
                }
            }, 'json');
        }
    });
}
