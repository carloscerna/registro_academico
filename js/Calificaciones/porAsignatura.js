let tablaNotas;
let dataNotas = [];
let periodo = '';
let codigoModalidad = '';

$(document).ready(function () {
    $('#lstannlectivo, #lstmodalidad, #lstgradoseccion, #lstasignatura').on('change', function () {
        if (tablaNotas) tablaNotas.clear().draw();
    });

    $('#lstperiodo').on('change', function () {
        if (formularioCompleto()) {
            Swal.fire({
                title: 'Cargando notas...',
                timerProgressBar: true,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    cargarNotas();
                }
            });
        }
    });

    $('#btnGuardar').on('click', function () {
        guardarNotas();
    });
});

function formularioCompleto() {
    return $('#lstannlectivo').val() &&
           $('#lstmodalidad').val() &&
           $('#lstgradoseccion').val() &&
           $('#lstasignatura').val() &&
           $('#lstperiodo').val();
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
            Swal.close();
            dataNotas = response.data ?? [];
            construirTabla();
        },
        error: function () {
            Swal.close();
            Swal.fire('Error', 'No se pudo cargar la información de notas.', 'error');
        }
    });
}

function renderInput(rowIndex, campo, valor, editable = true) {
    const attrs = editable ? '' : 'readonly tabindex="-1" style="background-color:#e9ecef;"';
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

function construirTabla() {
    if (tablaNotas) {
        tablaNotas.destroy();
        $('#tablaNotas').empty();
    }

    tablaNotas = $('#tablaNotas').DataTable({
        data: dataNotas,
        columns: [
            { data: 'id_notas', visible: false },
            { data: 'codigo_cc', visible: false },
            { data: 'codigo_nie', title: 'NIE' },
            { data: 'nombre_completo', title: 'NOMBRE DEL ESTUDIANTE' },
            {
                data: 'nota_a1', title: 'A1',
                render: (data, type, row, meta) => renderInput(meta.row, 'a1', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_a2', title: 'A2',
                render: (data, type, row, meta) => renderInput(meta.row, 'a2', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_a3', title: 'A3',
                render: (data, type, row, meta) => renderInput(meta.row, 'a3', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_r', title: 'NOTA R',
                render: (data, type, row, meta) =>
                    renderInput(meta.row, 'r', data, row.codigo_cc === '01')
            },
            {
                data: 'nota_pp', title: 'NOTA PP',
                render: (data, type, row, meta) =>
                    renderInput(meta.row, 'pp', data, ['02', '03', '04'].includes(row.codigo_cc))
            },
            {
                data: null, title: 'RESULTADO',
                render: (data, type, row) => {
                    const nota_pp = parseFloat(row.pp) || 0;
                    const califMinima = parseFloat($('#calificacionMinima').val()) || 6;
                    const resultado = nota_pp >= califMinima ? 'Aprobado' : 'Reprobado';
                    const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
                    return `<span class="${clase}">${resultado}</span>`;
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

    recalcularNotasIniciales();
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

function getColIndex(title) {
    let index = -1;
    tablaNotas.columns().every(function (i) {
        if (this.header().textContent.trim() === title) index = i;
    });
    return index;
}

function recalcularNotasIniciales() {
    const califMinima = parseFloat($('#calificacionMinima').val()) || 6;

    dataNotas.forEach((fila, row) => {
        if (fila.codigo_cc === '01') {
            const { a1, a2, a3, r } = obtenerValoresFilaDOM(row);
            let nota_pp = (a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30);
            if (r >= 0.1 && r <= 10) {
                nota_pp = (a1 < a2)
                    ? (r * 0.35) + (a2 * 0.35) + (a3 * 0.30)
                    : (a1 * 0.35) + (r * 0.35) + (a3 * 0.30);
            }
            nota_pp = Math.round(nota_pp * 10) / 10;
            fila.pp = nota_pp;
            $('input[data-row="' + row + '"][data-campo="pp"]').val(nota_pp);

            const notaPP = parseFloat(fila.pp) || 0;
            const resultado = notaPP >= califMinima ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html('<span class="' + clase + '">' + resultado + '</span>');
        }

        if (fila.codigo_cc === '02') {
            const notaPP = parseFloat(fila.pp) || 0;
            const resultado = notaPP >= califMinima ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html('<span class="' + clase + '">' + resultado + '</span>');
        }
        
        if (fila.codigo_cc === '04') {
            const nota_pp = parseFloat(fila.pp) || 0;
            const resultado = nota_pp >= 3 ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html(`<span class="${clase}">${resultado}</span>`);
        }
        
    });
}
    
/*
    dataNotas.forEach((fila, row) => {
        const codigo = fila.codigo_cc;

        if (fila.codigo_cc !== '01') return;

        const { a1, a2, a3, r } = obtenerValoresFilaDOM(row);
        let nota_pp = (a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30);

        if (r >= 0.1 && r <= 10) {
            nota_pp = (a1 < a2)
                ? (r * 0.35) + (a2 * 0.35) + (a3 * 0.30)
                : (a1 * 0.35) + (r * 0.35) + (a3 * 0.30);
        }

        nota_pp = Math.round(nota_pp * 10) / 10;
        fila.pp = nota_pp;
        $(`input[data-row="${row}"][data-campo="pp"]`).val(nota_pp);

        const inputR = $(`input[data-row="${row}"][data-campo="r"]`);
        if (nota_pp < califMinima) {
            inputR.prop('readonly', false).removeAttr('tabindex');
        } else if (!inputR.val() || parseFloat(inputR.val()) === 0) {
            inputR.prop('readonly', true).attr('tabindex', '-1').val('');
            fila.r = '';
        }

        // Actualizar visual del resultado al cargar
            const resultado = fila.pp >= califMinima ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html(`<span class="${clase}">${resultado}</span>`);

    });*/


$(document).on('input', '.campoNota', function () {
    const input = $(this);
    const row = parseInt(input.data('row'));
    const campo = input.data('campo');
    let valor = parseFloat(input.val());
    const fila = dataNotas[row];
    const califMinima = parseFloat($('#calificacionMinima').val()) || 6;

    // Validaciones
    if (['a1', 'a2', 'a3', 'pp'].includes(campo)) {
        if (isNaN(valor) || valor < 0.1 || valor > 10) {
            input.addClass('is-invalid');
            return;
        } else {
            input.removeClass('is-invalid');
        }
    }

    // Solo código_cc = '01' recalcula automáticamente
    if (fila.codigo_cc === '01') {
        if (campo === 'r') {
            if (input.val().trim() !== '') {
                if (isNaN(valor) || valor < 0.1 || valor > 10) {
                    input.addClass('is-invalid');
                    return;
                } else {
                    input.removeClass('is-invalid');
                }
            } else {
                input.removeClass('is-invalid');
                valor = 0;
            }
        }

        // Leer valores actuales directamente desde el DOM
        const { a1, a2, a3, r } = obtenerValoresFilaDOM(row);
        let nota_pp;
        let usoRecuperacion = false;

        // Recalculo de nota_pp
        if (campo === 'r' && input.val().trim() === '') {
            nota_pp = (a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30);
        } else if (campo === 'r' && r > 0 && r <= 10) {
            usoRecuperacion = true;
            nota_pp = (a1 < a2)
                ? (r * 0.35) + (a2 * 0.35) + (a3 * 0.30)
                : (a1 * 0.35) + (r * 0.35) + (a3 * 0.30);
        } else {
            nota_pp = (a1 * 0.35) + (a2 * 0.35) + (a3 * 0.30);
        }

        nota_pp = Math.round(nota_pp * 10) / 10;

        // Actualizar valores en objeto de datos
        fila.a1 = a1;
        fila.a2 = a2;
        fila.a3 = a3;
        fila.r = r;
        fila.pp = nota_pp;
        // Reflejar en input de nota_pp
        if (fila.codigo_cc === '01') {
            $(`input[data-row="${row}"][data-campo="pp"]`).val(nota_pp);
        }
        

        // Activar o desactivar nota_r con lógica correcta
        const inputRecup = $(`input[data-row="${row}"][data-campo="r"]`);

        if (nota_pp >= califMinima && !usoRecuperacion) {
            inputRecup.prop('readonly', true).attr('tabindex', '-1').val('');
            fila.r = '';
        } else {
            inputRecup.prop('readonly', false).removeAttr('tabindex');
        }
            // Actualizar columna Resultado manualmente SIN redibujar
                const resultado = nota_pp >= califMinima ? 'Aprobado' : 'Reprobado';
                const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
                const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
                $(celda).html(`<span class="${clase}">${resultado}</span>`);
    }
    // solo par el c´ldigo = '01' recalcula automáticamente    
    if (campo === 'pp' && ['02'].includes(fila.codigo_cc)) {
        if (isNaN(valor) || valor < 0.1 || valor > 10) {
            input.addClass('is-invalid');
            return;
        } else {
            input.removeClass('is-invalid');
        }
    
        fila.pp = valor;
    
        // Actualizar visualmente el resultado
        const resultado = valor >= califMinima ? 'Aprobado' : 'Reprobado';
        const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
        const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
        $(celda).html(`<span class="${clase}">${resultado}</span>`);
    }

    if (campo === 'pp' && fila.codigo_cc === '04') {
        if (isNaN(valor) || valor < 1 || valor > 5) {
            input.addClass('is-invalid');
            return;
        } else {
            input.removeClass('is-invalid');
        }
    
        fila.pp = valor;
    
        const resultado = valor >= 3 ? 'Aprobado' : 'Reprobado';
        const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
        const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
        $(celda).html(`<span class="${clase}">${resultado}</span>`);
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
