    // Archivo: js/cargarDatos.js
$(document).ready(function() {
    cargarAnnLectivo();
    cargarModalidad();
    cargarGradoSeccion();
    cargarAsignatura();
});

// Cargar Año Lectivo
function cargarAnnLectivo() {
    let miselect = $("#lstannlectivo");
    miselect.empty().append('<option value="">Cargando...</option>');

    $.post("includes/cargar-ann-lectivo.php", { verificar_ann_lectivo: "si" }, function(data) {
        miselect.empty().append('<option value="">Seleccionar...</option>');
        $.each(data, function(i, item) {
            miselect.append(`<option value="${item.codigo}">${item.nombre}</option>`);
        });
    }, "json");
}

// Cargar Modalidad al cambiar Año Lectivo
$("#lstannlectivo").change(function() {
    let miselect = $("#lstmodalidad");
    miselect.empty().append('<option value="">Cargando...</option>');

    $.post("includes/cargar-bachillerato.php", { annlectivo: $(this).val() }, function(data) {
        miselect.empty().append('<option value="">Seleccionar...</option>');
        $.each(data, function(i, item) {
            miselect.append(`<option value="${item.codigo}">${item.descripcion}</option>`);
        });
    }, "json");
});

// Cargar Grado - Sección - Turno al cambiar Modalidad
$("#lstmodalidad").change(function() {
    let miselect = $("#lstgradoseccion");
    miselect.empty().append('<option value="">Cargando...</option>');

    $.post("includes/cargar-grado-seccion.php", { elegido: $(this).val(), ann: $("#lstannlectivo").val() }, function(data) {
        miselect.empty().append('<option value="">Seleccionar...</option>');
        $.each(data, function(i, item) {
            miselect.append(`<option value="${item.codigo_grado}${item.codigo_seccion}${item.codigo_turno}">${item.descripcion_grado} ${item.descripcion_seccion} - ${item.descripcion_turno}</option>`);
        });

        cargarPeriodos();
    }, "json");
});

// Cargar Asignaturas al cambiar Grado - Sección
$("#lstgradoseccion").change(function() {
    let miselect = $("#lstasignatura");
    miselect.empty().append('<option value="">Cargando...</option>');

    $.post("includes/cargar-asignatura.php", {
        elegido: $(this).val(),
        annlectivo: $("#lstannlectivo").val(),
        modalidad: $("#lstmodalidad").val()
    }, function(data) {
        miselect.empty().append('<option value="">Seleccionar...</option>');
        $.each(data, function(i, item) {
            miselect.append(`<option value="${item.codigo}">${item.descripcion}</option>`);
        });

        cargarPeriodos();
    }, "json");
});

// Cargar Períodos de acuerdo a la modalidad
function cargarPeriodos() {
    let bach = $("#lstmodalidad").val();
    let milstperiodo = $("#lstperiodo");
    milstperiodo.empty();

    if (bach >= '03' && bach <= '05') {
        milstperiodo.append('<option value="Periodo 1">Trimestre 1</option>');
        milstperiodo.append('<option value="Periodo 2">Trimestre 2</option>');
        milstperiodo.append('<option value="Periodo 3">Trimestre 3</option>');
        milstperiodo.append('<option value="Recuperacion">Recuperación</option>');
    } else if (bach >= '06') {
        milstperiodo.append('<option value="Periodo 1">Período 1</option>');
        milstperiodo.append('<option value="Periodo 2">Período 2</option>');
        milstperiodo.append('<option value="Periodo 3">Período 3</option>');
        milstperiodo.append('<option value="Periodo 4">Período 4</option>');
        milstperiodo.append('<option value="Recuperacion">Recuperación</option>');
    }

    if ($("#lstgradoseccion").val().substring(0,2) == '11') {
        milstperiodo.append('<option value="Nota PAES">Nota PAES</option>');
    }
}