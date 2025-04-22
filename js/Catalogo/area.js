$(document).ready(function() {
    let tabla = $('#tablaAreas').DataTable({
        ajax: {
            url: 'php_libs/soporte/Catalogo/area.php',
            type: 'POST',
            data: {accion: 'listar'},
            dataSrc: 'data'
        },
        columns: [
            {data: 'id_area_asignatura'},
            {data: 'codigo'},
            {data: 'descripcion'},
            {
                data: null,
                render: function(data) {
                    return `
                        <button class="btn btn-sm btn-warning" onclick="obtener(${data.id_area_asignatura})">Editar</button>
                        <button class="btn btn-sm btn-danger" onclick="eliminar(${data.id_area_asignatura})">Eliminar</button>
                        <button class="btn btn-sm btn-info" onclick="abrirDimension('${data.codigo}')">Área Dimensión</button>
                    `;
                }
                
            }
        ]
    });

    $('#formAreaAsignatura').submit(function(e) {
        e.preventDefault();
        $.post('php_libs/soporte/Catalogo/area.php',$(this).serialize() + '&accion=guardar', function(res) {
            tabla.ajax.reload();
            limpiarFormulario();
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: 'Registro guardado correctamente'
            });
        }, 'json');
    });
});

function obtener(id) {
    $.post('php_libs/soporte/Catalogo/area.php', {id_area_asignatura: id, accion: 'obtener'}, function(data) {
        $('#id_area_asignatura').val(data.id_area_asignatura);
        $('#codigo').val(data.codigo);
        $('#descripcion').val(data.descripcion);
    }, 'json');
}

function eliminar(id) {
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
            $.post('php_libs/soporte/catalogo/area.php', {id_area_asignatura: id, accion: 'eliminar'}, function(res) {
                $('#tablaAreas').DataTable().ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: 'Registro eliminado correctamente'
                });
            }, 'json');
        }
    });
}

function limpiarFormulario() {
    $('#id_area_asignatura').val('');
    $('#codigo').val('');
    $('#descripcion').val('');
}