$(document).ready(function () {
    // Cargar Año Lectivo
    $.post('php_libs/soporte/Herramientas/ControladorExcel.php', { accion: 'ann_lectivo' }, function (res) {
        if (res.success) {
            let opciones = '<option value="">Seleccione...</option>';
            res.data.forEach(row => {
                opciones += `<option value="${row.codigo}">${row.descripcion}</option>`;
            });
            $('#ann_lectivo').html(opciones);
        } else {
            alert("Error al cargar años lectivos: " + res.message);
        }
    }, 'json');

    // Cargar Bachillerato
    $('#ann_lectivo').on('change', function () {
        const codigo = $(this).val();
        if (codigo !== '') {
            $.post('php_libs/soporte/Herramientas/ControladorExcel.php', {
                accion: 'bachillerato',
                codigo_ann_lectivo: codigo
            }, function (res) {
                if (res.success) {
                    let opciones = '<option value="">Seleccione...</option>';
                    res.data.forEach(row => {
                        opciones += `<option value="${row.codigo_bachillerato}">${row.nombre}</option>`;
                    });
                    $('#bachillerato').html(opciones);
                    $('#grupo').html('<option value="">Seleccione...</option>');
                }
            }, 'json');
        }
    });

    // Cargar Grupo (grado/sección/turno)
    $('#bachillerato').on('change', function () {
        const bach = $(this).val();
        const ann = $('#ann_lectivo').val();
        if (bach !== '' && ann !== '') {
            $.post('php_libs/soporte/Herramientas/ControladorExcel.php', {
                accion: 'grupo',
                codigo_ann_lectivo: ann,
                codigo_bachillerato: bach
            }, function (res) {
                if (res.success) {
                    let opciones = '<option value="">Seleccione...</option>';
                    res.data.forEach(row => {
                        const value = `${row.codigo_grado}|${row.codigo_seccion}|${row.codigo_turno}`;
                        const texto = `${row.grado} - ${row.seccion} - ${row.turno}`;
                        opciones += `<option value="${value}">${texto}</option>`;
                    });
                    $('#grado').html(opciones);
                }
            }, 'json');
        }
    });
});
