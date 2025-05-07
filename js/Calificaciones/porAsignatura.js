let tablaNotas;
let dataNotas = [];
let periodo = '';
let codigoModalidad = '';

$(document).ready(function () {
    $('#lstannlectivo, #lstmodalidad, #lstgradoseccion, #lstasignatura, #lstperiodo').on('change', function () {
        if (formularioCompleto()) {
            cargarNotas();
        }
    });

    $('#btnGuardar').on('click', function () {
        guardarNotas();
    });
});

function formularioCompleto() {
    return $('#lstannlectivo').val() && $('#lstmodalidad').val() &&
           $('#lstgradoseccion').val() && $('#lstasignatura').val() && $('#lstperiodo').val();
}

function cargarNotas() {
    periodo = $('#lstperiodo').val();
    codigoModalidad = $('#lstmodalidad').val();

    $.ajax({
        url: 'php_libs/soporte/Calificaciones/PorAsignatura.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'buscarNotas',
            modalidad: codigoModalidad,
            gradoseccion: $('#lstgradoseccion').val(),
            annlectivo: $('#lstannlectivo').val(),
            asignatura: $('#lstasignatura').val(),
            periodo: periodo
        },
        success: function (response) {
            dataNotas = response.data ?? [];
            construirTabla();
        },
        error: function () {
            Swal.fire('Error', 'No se pudo cargar la información de notas.', 'error');
        }
    });
}

function construirTabla() {
    if (tablaNotas) {
        tablaNotas.destroy();
        $('#tablaNotas').empty();
    }

    tablaNotas = $('#tablaNotas').DataTable({
        data: dataNotas,
        columns: [
            { data: 'id_notas', visible: false }, // oculto
            { data: 'codigo_cc', visible: false }, // oculto
            { data: 'codigo_nie', title: 'NIE' },
            { data: 'nombre_completo', title: 'NOMBRE DEL ESTUDIANTE' },
            {
                data: 'a1', title: 'A1',
                render: (data, type, row, meta) => renderInput(meta.row, 'a1', data)
            },
            {
                data: 'a2', title: 'A2',
                render: (data, type, row, meta) => renderInput(meta.row, 'a2', data)
            },
            {
                data: 'a3', title: 'A3',
                render: (data, type, row, meta) => renderInput(meta.row, 'a3', data)
            },
            {
                data: 'r', title: 'NOTA R',
                render: (data, type, row, meta) => renderInput(meta.row, 'r', data)
            },
            {
                data: 'pp', title: 'NOTA PP',
                render: (data, type, row, meta) => renderInput(meta.row, 'pp', data, row.codigo_cc !== '01')
            }
        ],
        paging: false,
        searching: false,
        info: false,
        ordering: false,
        language: {
            emptyTable: 'No hay datos disponibles'
        }
    });
}

function renderInput(rowIndex, campo, valor, editable = true) {
    const readOnly = editable ? '' : 'readonly';
    return `<input 
        type="number" 
        class="form-control form-control-sm campoNota"
        data-row="${rowIndex}" 
        data-campo="${campo}"
        value="${valor ?? ''}" 
        ${readOnly}
    >`;
}

$(document).on('input', '.campoNota', function () {
    const input = $(this);
    const row = parseInt(input.data('row'));
    const campo = input.data('campo');
    const valor = parseFloat(input.val()) || 0;

    dataNotas[row][campo] = valor;

    // Si campo es A1/A2/A3 y código_cc = 01 => recalcula PP
    const fila = dataNotas[row];
    if (['a1', 'a2', 'a3'].includes(campo) && fila.codigo_cc === '01') {
        const a1 = parseFloat(fila.a1) || 0;
        const a2 = parseFloat(fila.a2) || 0;
        const a3 = parseFloat(fila.a3) || 0;
        fila.pp = Math.round((a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30));
        // actualiza input de nota_pp en DOM
        $(`input[data-row="${row}"][data-campo="pp"]`).val(fila.pp);
    }
});

function guardarNotas() {
    if (!dataNotas.length) {
        Swal.fire('Atención', 'No hay datos para guardar.', 'warning');
        return;
    }

    $.ajax({
        url: 'php_libs/soporte/Calificaciones/PorAsignatura.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'guardarNotas',
            periodo: periodo,
            codigo_modalidad: codigoModalidad,
            notas: dataNotas
        },
        success: function (res) {
            if (res.success) {
                Swal.fire('Éxito', res.mensaje ?? 'Notas guardadas correctamente.', 'success');
                cargarNotas();
            } else {
                Swal.fire('Error', res.mensaje ?? 'Ocurrió un problema al guardar.', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'Fallo al comunicar con el servidor.', 'error');
        }
    });
}
