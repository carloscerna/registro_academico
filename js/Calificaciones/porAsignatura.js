let tablaNotas;
let dataNotas = [];
let periodo = '';
let codigoModalidad = '';

$(document).ready(function () {
// Cuando cualquiera de estos selectores cambie, limpiar la tabla de notas
    $('#lstannlectivo, #lstmodalidad, #lstgradoseccion').on('change', function () {
        if (tablaNotas) tablaNotas.clear().draw();
        // Llamar a cargarPeriodosHabilitados cuando cambian annlectivo o modalidad
        if ($('#lstmodalidad').val() && $('#lstannlectivo').val()) {
            cargarPeriodosHabilitados();
        } else {
            // Si no hay valores en annlectivo o modalidad, limpiar y deshabilitar lstperiodo
            $('#lstperiodoC').empty().append('<option value="">Seleccione Periodo</option>').prop('disabled', true);
        }
    });

    // Nuevo: Cuando lstasignatura cambie, cargar los períodos habilitados.
    // También se puede agregar a lstgradoseccion para que los periodos se carguen antes.
    $('#lstasignatura').on('change', function () {
        // Asegurarse de que ya se hayan seleccionado annlectivo, modalidad, y gradoseccion
        if ($('#lstannlectivo').val() && $('#lstmodalidad').val() && $('#lstgradoseccion').val()) {
            cargarPeriodosHabilitados();
        } else {
            // Si falta alguna selección, limpiar y deshabilitar lstperiodo
            $('#lstperiodoC').empty().append('<option value="">Seleccione Periodo</option>').prop('disabled', true);
        }
        if (tablaNotas) tablaNotas.clear().draw();
    });
    // cuando cambia el valor del período.
    $('#lstperiodoC').on('change', function () {
        const valor = $(this).val();
    
        if (valor === 'Recuperación') { // esto servirá para el 1 y 2.
            cargarNotasRecuperacion(); // función nueva
        } else if (formularioCompleto()) {
            Swal.fire({ title: 'Cargando notas...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            cargarNotas();
        }
    });

    $('#btnGuardar').on('click', function () {
        $('#btnGuardar').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Guardando...');
        $('#btnGuardar').prop('disabled', true);
            guardarNotas();
        $('#btnGuardar').html('<i class="bi bi-save me-2"></i> Guardar Calificaciones');
        $('#btnGuardar').prop('disabled', false);
            
    });

    $('#btnGenerarInforme').on('click', function() {
        let modalidad = $('#lstmodalidad').val();
        let nombre_modalidad = $('#lstmodalidad option:selected').text();
        let gradoseccion = $('#lstgradoseccion').val();
        let nombre_grado = $('#lstgradoseccion option:selected').text();
        let annlectivo = $('#lstannlectivo').val();
        let nombre_annlectivo = $('#lstannlectivo option:selected').text();
        let asignatura = $('#lstasignatura').val();
        let periodo = $('#lstperiodoC').val();
        let calificacionMinima = $('#calificacionMinima').val();
    
        //  ✅ Validación de datos
        if (!modalidad || !gradoseccion || !annlectivo || !asignatura) {
            Swal.fire('Error', 'Por favor, complete todos los campos antes de generar el informe.', 'warning');
            return;
        }
    
        //  ✅ URL completa y correcta
        let url = '/registro_academico/php_libs/reportes/Estudiante/informePorAsignatura.php?modalidad=' + modalidad +
                  '&nombre_modalidad=' + encodeURIComponent(nombre_modalidad) +
                  '&gradoseccion=' + gradoseccion +
                  '&nombre_grado=' + encodeURIComponent(nombre_grado) +
                  '&annlectivo=' + annlectivo +
                  '&nombre_annlectivo=' + encodeURIComponent(nombre_annlectivo) +
                  '&asignatura=' + asignatura +
                  '&calificacionMinima=' + calificacionMinima +
                  '&periodo=' + periodo;
    
        window.open(url, '_blank');
    });
    // Informe por Nivel.
    $('#btnGenerarInformePorNivel').on('click', function() {
        let modalidad = $('#lstmodalidad').val();
        let nombre_modalidad = $('#lstmodalidad option:selected').text();
        let gradoseccion = $('#lstgradoseccion').val();
        let nombre_grado = $('#lstgradoseccion option:selected').text();
        let annlectivo = $('#lstannlectivo').val();
        let nombre_annlectivo = $('#lstannlectivo option:selected').text();
        let asignatura = $('#lstasignatura').val();
        let periodo = $('#lstperiodoC').val();
        let calificacionMinima = $('#calificacionMinima').val();
    
        //  ✅ Validación de datos
        if (!modalidad || !gradoseccion || !annlectivo) {
            Swal.fire('Error', 'Por favor, complete todos los campos antes de generar el informe.', 'warning');
            return;
        }
    
        //  ✅ URL completa y correcta
        let url = '/registro_academico/php_libs/reportes/Estudiante/informePorModalidad_modificado.php?modalidad=' + modalidad +
                  '&nombre_modalidad=' + encodeURIComponent(nombre_modalidad) +
                  '&gradoseccion=' + gradoseccion +
                  '&nombre_grado=' + encodeURIComponent(nombre_grado) +
                  '&annlectivo=' + annlectivo +
                  '&nombre_annlectivo=' + encodeURIComponent(nombre_annlectivo) +
                  '&asignatura=' + asignatura +
                  '&calificacionMinima=' + calificacionMinima +
                  '&periodo=' + periodo;
    
        window.open(url, '_blank');
    });

    $('#btnInformeHorizontalSeccion').on('click', function () {
        const modalidad = $('#lstmodalidad').val();
        const gradoseccion = $('#lstgradoseccion').val();
        const annlectivo = $('#lstannlectivo').val();
    
        if (!modalidad || !gradoseccion || !annlectivo) {
            Swal.fire('Atención', 'Debes seleccionar Año Lectivo, Modalidad y Grado/Sección.', 'warning');
            return;
        }
    
        const url = `php_libs/reportes/Estudiante/informePorSeccionHorizontal.php?modalidad=${modalidad}&gradoseccion=${gradoseccion}&annlectivo=${annlectivo}`;
        window.open(url, '_blank');
    });
    
    // NUEVO: Event listener para el botón de informe individual en la tabla
    $(document).on('click', '.btn-informe-individual', function() {
        const id_alumno = $(this).data('id-alumno');
        const modalidad = $('#lstmodalidad').val();
        const gradoseccion = $('#lstgradoseccion').val();
        const annlectivo = $('#lstannlectivo').val();

        if (!modalidad || !gradoseccion || !annlectivo || !id_alumno) {
            Swal.fire('Error', 'Faltan datos para generar el informe individual.', 'warning');
            return;
        }

        // Llama al InformePorSeccionHorizontal.php con el id_alumno específico
        const url = `php_libs/reportes/Estudiante/InformePorSeccionHorizontal.php?modalidad=${modalidad}&gradoseccion=${gradoseccion}&annlectivo=${annlectivo}&id_alumno=${id_alumno}`;
        window.open(url, '_blank');
    });

    $('#btnCerrarRecuperacion').on('click', function () {
    new bootstrap.Modal(document.getElementById('modalRecuperacion')).hide();
    });

$('#btnGuardarRecuperacion').on('click', function () {
    if (!recuperacionData || !recuperacionData.length) {
        Swal.fire('Atención', 'No hay datos de recuperación para guardar.', 'warning');
        return;
    }

    const datosAGuardar = recuperacionData.map(alumno => ({
        id_notas: alumno.id_notas,
        recuperacion: parseFloat(alumno.recuperacion) || 0,
        nota_recuperacion_2: parseFloat(alumno.nota_recuperacion_2) || 0,
        nota_final: parseFloat(alumno.nota_final) || 0
    }));

    $.ajax({
        url: 'php_libs/soporte/Calificaciones/PorAsignatura.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'GuardarNotaRecuperacion',
            datos: datosAGuardar
        },
        success: function (res) {
            if (res.success) {
                Swal.fire('Éxito', res.mensaje ?? 'Notas de recuperación guardadas correctamente.', 'success');
            } else {
                Swal.fire('Error', res.mensaje ?? 'Hubo un problema al guardar.', 'error');
            }
        },
        error: function () {
            Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
        }
    });
});



});

function formularioCompleto() {
    return $('#lstannlectivo').val() &&
           $('#lstmodalidad').val() &&
           $('#lstgradoseccion').val() &&
           $('#lstasignatura').val() &&
           $('#lstperiodoC').val();
}

function cargarNotas() {
    periodo = $('#lstperiodoC').val();
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
            },
            {
                data: null, title: 'Acciones',
                render: (data, type, row) => {
                    return `<button type="button" class="btn btn-sm btn-primary btn-informe-individual" data-id-alumno="${row.id_alumno}">
                                <i class="fas fa-file-pdf"></i> 
                            </button>`;
                }
            }
        ],
        data: dataNotas,
        scrollY: '400px',
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
            //$(`input[data-row="${row}"][data-campo="pp"]`).val(nota_pp);
        
            // 🛠️ Activar/desactivar input de recuperación
            const inputR = $(`input[data-row="${row}"][data-campo="r"]`);
            const r_actual = parseFloat(inputR.val()) || 0;
        
            if (nota_pp >= califMinima && r_actual === 0) {
                inputR.prop('readonly', true).attr('tabindex', '-1').val('');
                fila.r = '';
            } else {
                inputR.prop('readonly', false).removeAttr('tabindex');
            }
        
            // Actualizar resultado visual
            const resultado = nota_pp >= califMinima ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html(`<span class="${clase}">${resultado}</span>`);
        }
        

        if (fila.codigo_cc === '02') {
            const notaPP = parseFloat(fila.nota_pp) || 0;
            const resultado = notaPP >= califMinima ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html('<span class="' + clase + '">' + resultado + '</span>');
        }
        
        if (fila.codigo_cc === '04') {
            const notaPP = parseFloat(fila.nota_pp) || 0;
            const resultado = nota_pp >= 3 ? 'Aprobado' : 'Reprobado';
            const clase = resultado === 'Aprobado' ? 'text-success fw-bold' : 'text-danger fw-bold';
            const celda = tablaNotas.cell(row, getColIndex('RESULTADO')).node();
            $(celda).html(`<span class="${clase}">${resultado}</span>`);
        }
        
    });
}

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
        
    // Activar/desactivar nota_r correctamente
        const inputRecup = $(`input[data-row="${row}"][data-campo="r"]`);
        const r_actual = parseFloat(inputRecup.val()) || 0;

        if (nota_pp >= califMinima && r_actual === 0) {
            inputRecup.prop('readonly', true).attr('tabindex', '-1').val('');
            fila.r = '';
        } else {
            inputRecup.prop('readonly', false).removeAttr('tabindex');
        }
              // Actualizar resultado visual
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
    
        const resultado = valor >= 4 ? 'Aprobado' : 'Reprobado';
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

    sincronizarDatosDesdeDOM(); // 🟢 Aquí se asegura que todo esté actualizado

    $.ajax({
        url: 'php_libs/soporte/Calificaciones/PorAsignatura.php',
        type: 'POST',
        dataType: 'json',
        data: {
            accion: 'guardarNotas',
            periodo: periodo,
            codigo_modalidad: codigoModalidad,
            notas: dataNotas.map(nota => ({
                id_notas: nota.id_notas,
                nota_a1: parseFloat(nota.a1) || 0,
                nota_a2: parseFloat(nota.a2) || 0,
                nota_a3: parseFloat(nota.a3) || 0,
                nota_r:  parseFloat(nota.r)  || 0,
                nota_pp: parseFloat(nota.pp) || 0,
                codigo_cc: nota.codigo_cc
            }))
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

function sincronizarDatosDesdeDOM() {
    dataNotas.forEach((fila, row) => {
        fila.a1 = parseFloat($(`input[data-row="${row}"][data-campo="a1"]`).val()) || 0;
        fila.a2 = parseFloat($(`input[data-row="${row}"][data-campo="a2"]`).val()) || 0;
        fila.a3 = parseFloat($(`input[data-row="${row}"][data-campo="a3"]`).val()) || 0;
        fila.r  = parseFloat($(`input[data-row="${row}"][data-campo="r"]`).val())  || 0;
        fila.pp = parseFloat($(`input[data-row="${row}"][data-campo="pp"]`).val()) || 0;
    });
}

function cargarNotasRecuperacion() {
    const califMinima = parseFloat($('#calificacionMinima').val()) || 6;
    if (tablaNotas) tablaNotas.clear().draw();
    $.ajax({
        url: 'php_libs/soporte/Calificaciones/PorAsignatura.php',
        method: 'POST',
        dataType: 'json',
        data: {
            accion: 'buscarNotasRecuperacion',
            modalidad: $('#lstmodalidad').val(),
            gradoseccion: $('#lstgradoseccion').val(),
            annlectivo: $('#lstannlectivo').val(),
            asignatura: $('#lstasignatura').val()
        },
        success: function (response) {
            // ✅ Filtrar: mostrar si reprobado o si ya tiene valores en r1 o r2
            let alumnos = response.data ?? [];
            alumnos = alumnos.filter(alumno => {
                const nf = parseFloat(alumno.nota_final) || 0;
                const r1 = parseFloat(alumno.nota_recuperacion) || 0;
                const r2 = parseFloat(alumno.nota_recuperacion_2) || 0;
                return nf < califMinima || r1 > 0 || r2 > 0;
            });

            // ✅ Guardar en variable global
            recuperacionData = alumnos.map(al => ({
                id_notas: al.id_notas,
                id_alumno: al.id_alumno,
                nombre_completo: al.nombre_completo,
                nota_final_original: parseFloat(al.nota_final) || 0,
                recuperacion: parseFloat(al.nota_recuperacion) || 0,
                nota_recuperacion_2: parseFloat(al.nota_recuperacion_2) || 0,
                nota_final: parseFloat(al.nota_final) || 0
            }));

            // ✅ Mostrar u ocultar elementos
            $('#leyendaRecuperacion').toggle(alumnos.length > 0);
            $('#contenedorTablaRecuperacion').toggle(alumnos.length > 0);
            $('#btnGuardarRecuperacion').toggle(alumnos.length > 0);

            // ✅ Construir tabla
            const tbody = $('#tablaRecuperacion tbody');
            tbody.empty();

            if (alumnos.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted">Todos los estudiantes están aprobados. 🎉</td></tr>');
            } else {
                alumnos.forEach((alumno, i) => {
                    const nf = parseFloat(alumno.nota_final) || 0;
                    const r1 = parseFloat(alumno.nota_recuperacion) || 0;
                    const r2 = parseFloat(alumno.nota_recuperacion_2) || 0;

                    const resultado = nf >= califMinima ? 'Aprobado' : 'Reprobado';
                    const clase = nf >= califMinima ? 'text-success fw-bold' : 'text-danger fw-bold';

                    // Activar R2 si nota_final < califMinima o r2 > 0, y r1 > 0
                    let activarR2 = (r2 > 0 || nf < califMinima) && r1 > 0;

                    tbody.append(`
                        <tr data-index="${i}">
                            <td>${i + 1}</td>
                            <td>${alumno.nombre_completo}</td>
                            <td>
                                <input type="number" step="0.1" class="form-control form-control-sm inputRecuperacion"
                                    data-index="${i}" data-tipo="r1" value="${r1 > 0 ? r1 : ''}">
                            </td>
                            <td>
                                <input type="number" step="0.1" class="form-control form-control-sm inputRecuperacion"
                                    data-index="${i}" data-tipo="r2" value="${r2 > 0 ? r2 : ''}"
                                    ${activarR2 ? '' : 'readonly tabindex="-1"'}>
                            </td>
                            <td class="notaFinal">${(nf || 0).toFixed(1)}</td>
                            <td class="resultado ${clase}">${resultado}</td>
                        </tr>
                    `);
                });
            }

            const modal = new bootstrap.Modal(document.getElementById('modalRecuperacion'), {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();

        },
        error: function () {
            Swal.fire('Error', 'No se pudieron cargar las notas de recuperación.', 'error');
        }
    });
}


$(document).on('input', '.inputRecuperacion', function () {
    const index = $(this).data('index');
    const tipo = $(this).data('tipo');
    const input = $(this);
    const valor = parseFloat(input.val());
    const califMinima = parseFloat($('#calificacionMinima').val()) || 6;

    const alumno = recuperacionData[index];
    const fila = $(`tr[data-index="${index}"]`);
    const inputR2 = fila.find(`input[data-tipo="r2"]`);

    // Validación del input: numérico entre 0 y 10
    if (isNaN(valor) || valor < 0 || valor > 10) {
        input.addClass('is-invalid');
        return;
    } else {
        input.removeClass('is-invalid');
    }

    // Asignar valor al campo correspondiente
    if (tipo === 'r1') alumno.recuperacion = valor;
    if (tipo === 'r2') alumno.nota_recuperacion_2 = valor;

   // Leer valores actuales
        const original = parseFloat(alumno.nota_final_original) || 0;
        const r1 = parseFloat(alumno.recuperacion) || 0;
        const r2 = parseFloat(alumno.nota_recuperacion_2) || 0;

        let notaFinalCalculada = original;

        // Evaluar si se debe recalcular
        if (r1 > 0) {
            const calcR1 = (original + r1) / 2;
            notaFinalCalculada = calcR1;

            if (calcR1 >= califMinima) {
                // Aprueba con R1, desactivar R2
                inputR2.val('').prop('readonly', true).attr('tabindex', '-1');
                alumno.nota_recuperacion_2 = 0;
            } else {
                // No aprueba con R1, evaluar R2
                inputR2.prop('readonly', false).removeAttr('tabindex');

                if (r2 > 0) {
                    const calcR2 = (original + r2) / 2;
                    notaFinalCalculada = calcR2;
                }
            }
        } else {
            // Si r1 = 0 o vacío → resetear R2 y volver a original
            inputR2.val('').prop('readonly', true).attr('tabindex', '-1');
            alumno.nota_recuperacion_2 = 0;
            notaFinalCalculada = original;
        }


    // Redondear y actualizar objeto
    alumno.nota_final = Math.round(notaFinalCalculada * 10) / 10;

    // Actualizar visual
        const nfCell = fila.find('.notaFinal');
        nfCell.text(alumno.nota_final.toFixed(1));

        // Resaltar si reprobado
        if (alumno.nota_final < califMinima) {
            nfCell
                .addClass('bg-danger text-white fw-bold')
                .removeClass('bg-success');
        } else {
            nfCell
                .removeClass('bg-danger text-white fw-bold')
                .addClass('bg-success text-white fw-bold');
        }


    fila.find('.resultado')
        .text(alumno.nota_final >= califMinima ? 'Aprobado' : 'Reprobado')
        .removeClass('text-success text-danger')
        .addClass(alumno.nota_final >= califMinima ? 'text-success fw-bold' : 'text-danger fw-bold');
});

function cerrarModalRecuperacion() {
    document.activeElement?.blur();

    // Cerrar el modal manualmente
    const modal = bootstrap.Modal.getInstance(document.getElementById('modalRecuperacion'));
    if (modal) {
        modal.hide();
    }

    // Restablecer el select de periodo al primer valor
    const selectPeriodo = document.getElementById('lstperiodoC');
    if (selectPeriodo && selectPeriodo.options.length > 0) {
        selectPeriodo.selectedIndex = 0;

        // Si querés que dispare automáticamente el evento 'change'
        const event = new Event('change');
        selectPeriodo.dispatchEvent(event);
    }
}

// Función para cargar los períodos habilitados
function cargarPeriodosHabilitados() {
    const modalidad = $('#lstmodalidad').val();
    const annlectivo = $('#lstannlectivo').val();

    // Solo si ambos tienen un valor seleccionado
    if (modalidad && annlectivo) {
        $.ajax({
            url: 'php_libs/soporte/Calificaciones/PorAsignatura.php',
            method: 'POST',
            dataType: 'json',
            data: {
                accion: 'buscarPeriodosHabilitados',
                modalidad: modalidad,
                annlectivo: annlectivo
            },
            success: function (response) {
                const lstPeriodo = $('#lstperiodoC');
                lstPeriodo.empty(); // Limpiar opciones actuales
                lstPeriodo.append('<option value="">Seleccione Periodo</option>'); // Opción por defecto

                if (response.success && response.data.length > 0) {
                    response.data.forEach(p => {
                        lstPeriodo.append(`<option value="${p.codigo_periodo}">${p.descripcion_periodo}</option>`);
                        // Pasar el valor de calificación Mínima.
                            $("#calificacionMinima").val(p.calificacionMinima);
                    });

                    // Añadir la opción de Recuperación
                    //lstPeriodo.append('<option value="Recuperación">Recuperación</option>');
                    lstPeriodo.prop('disabled', false); // Habilitar el select
                } else {
                    lstPeriodo.append('<option value="">No hay periodos disponibles</option>');
                    lstPeriodo.prop('disabled', true); // Deshabilitar si no hay periodos
                }
            },
            error: function () {
                Swal.fire('Error', 'No se pudieron cargar los períodos habilitados.', 'error');
                $('#lstperiodoC').empty().append('<option value="">Error al cargar</option>').prop('disabled', true);
            }
        });
    } else {
        // Si no hay modalidad o año lectivo, limpiar y deshabilitar lstperiodoC
        $('#lstperiodoC').empty().append('<option value="">Seleccione Periodo</option>').prop('disabled', true);
    }
}