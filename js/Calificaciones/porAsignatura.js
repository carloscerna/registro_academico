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
                data: 'nota_a1', title: 'A1',
                render: (data, type, row, meta) =>
                    renderInput(meta.row, 'a1', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_a2', title: 'A2',
                render: (data, type, row, meta) =>
                    renderInput(meta.row, 'a2', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_a3', title: 'A3',
                render: (data, type, row, meta) =>
                    renderInput(meta.row, 'a3', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_r', title: 'NOTA R',
                render: (data, type, row, meta) => {
                    const activarRecuperacion = row.codigo_cc === '01' && row.pp < parseFloat($('#calificacionMinima').val() || 6);
                    return renderInput(meta.row, 'r', data, activarRecuperacion);
                }
            },
            {
                data: 'nota_pp', title: 'NOTA PP',
                render: (data, type, row, meta) => {
                    const editable = ['02', '03', '04'].includes(row.codigo_cc);
                    return renderInput(meta.row, 'pp', data, editable);
                }
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
    const attrs = editable ? '' : 'readonly tabindex="-1"';
    return `<input 
        type="number" 
        step="0.1"
        min="0.1"
        max="10"
        class="form-control form-control-sm campoNota"
        data-row="${rowIndex}" 
        data-campo="${campo}"
        value="${valor ?? ''}" 
        ${attrs}
    >`;
}


function obtenerValoresFilaDOM(row) {
    const getInputVal = (campo) => {
        const val = $(`input[data-row="${row}"][data-campo="${campo}"]`).val();
        return parseFloat(val) || 0;
    };

    return {
        a1: getInputVal('a1'),
        a2: getInputVal('a2'),
        a3: getInputVal('a3'),
        r: getInputVal('r')
    };
}

$(document).on('input', '.campoNota', function () {
    const input = $(this);
    const row = parseInt(input.data('row'));
    const campo = input.data('campo');
    let valor = parseFloat(input.val());

    if (['a1', 'a2', 'a3', 'r', 'pp'].includes(campo)) {
        if (isNaN(valor) || valor < 0.1 || valor > 10) {
            input.addClass('is-invalid');
            return;
        } else {
            input.removeClass('is-invalid');
        }
    }

    const fila = dataNotas[row];
    if (fila.codigo_cc !== '01') return;

    // Leer todos los valores actuales desde el DOM
    const { a1, a2, a3,r } = obtenerValoresFilaDOM(row);
    const califMinima = parseFloat($('#calificacionMinima').val()) || 6;
    let nota_pp;

    if (campo === 'r' && input.val() === '') {
        // Restaurar cálculo original si se borra nota_r
        nota_pp = (a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30);
    } else if (campo === 'r' && r > 0 && r <= 10) {
        // Aplicar fórmula de recuperación
        if (a1 < a2) {
            nota_pp = (r * 0.35) + (a2 * 0.35) + (a3 * 0.30);
        } else {
            nota_pp = (a1 * 0.35) + (r * 0.35) + (a3 * 0.30);
        }
    } else {
        // Cálculo regular A1+A2+A3
        nota_pp = (a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30);
    }

    nota_pp = Math.round(nota_pp * 10) / 10;
    fila.a1 = a1;
    fila.a2 = a2;
    fila.a3 = a3;
    fila.r = r;
    fila.pp = nota_pp;

    // Actualizar visualmente nota_pp
    $(`input[data-row="${row}"][data-campo="pp"]`).val(nota_pp);

    // Activar o desactivar campo nota_r
// Activar o desactivar campo nota_r SOLO si aún no tiene valor
const inputRecup = $(`input[data-row="${row}"][data-campo="r"]`);
const nota_r_input_val = inputRecup.val().trim();

if ((nota_r_input_val === '' || parseFloat(nota_r_input_val) === 0) && nota_pp >= califMinima) {
    // Si nota_r no se ha usado y ya se aprueba, desactivarla
    inputRecup.prop('readonly', true).attr('tabindex', '-1').val('');
    fila.r = '';
} else {
    // Dejarla activa si ya está siendo usada o nota_pp no alcanza
    inputRecup.prop('readonly', false).removeAttr('tabindex');
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
