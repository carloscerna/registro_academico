let tablaDimension;

function abrirDimension(codigo_area) {
    $('#codigo_area').val(codigo_area);
    $('#codigo_dim').val('');
    $('#descripcion_dim').val('');
    $('#modalDimension').modal('show');

    // Cargar el DataTable
    tablaDimension = $('#tablaDimension').DataTable({
        destroy: true,
        ajax: {
            url: 'php_libs/soporte/Catalogo/Dimension.php',
            type: 'POST',
            data: {accion: 'listar', codigo_area: codigo_area},
            dataSrc: 'data'
        },
        columns: [
            {data: 'id_'},
            {data: 'codigo'},
            {data: 'descripcion'},
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="obtenerDimension(${data.id_})">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarDimension(${data.id_})">Eliminar</button>
                    `;
                }
            }
        ]
    });
}



$('#formDimension').submit(function(e) {
    e.preventDefault();
    $.post('php_libs/soporte/catalogo/dimension.php', $(this).serialize() + '&accion=guardar', function(res) {
        tablaDimension.ajax.reload();
        limpiarDimension();
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Registro guardado correctamente'
        });
    }, 'json');
});
function obtenerDimension(id) {
    $.post('php_libs/soporte/catalogo/dimension.php', {id_: id, accion: 'obtener'}, function(data) {
        $('#id_').val(data.id_);
        $('#codigo_dim').val(data.codigo);
        $('#descripcion_dim').val(data.descripcion);
    }, 'json');
}

function eliminarDimension(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Esta acción eliminará el registro.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('php_libs/soporte/catalogo/dimension.php', {id_: id, accion: 'eliminar'}, function(res) {
                tablaDimension.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: 'Registro eliminado correctamente'
                });
            }, 'json');
        }
    });
}


function limpiarDimension() {
    $('#id_').val('');
    $('#codigo_dim').val('');
    $('#descripcion_dim').val('');
}
