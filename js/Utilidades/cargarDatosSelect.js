// Carga la INformación de Tabla Año Lectivo.
$(document).ready(function() {
    // Cargar Año Lectivo primero
    cargarOpciones("#lstannlectivo", "includes/cargar-ann-lectivo.php");

    // Cuando el usuario seleccione un Año Lectivo, se carga la Modalidad
    $("#lstannlectivo").change(function() {
        let idAnnLectivo = $(this).val();
        cargarOpcionesDependiente("#lstmodalidad", "includes/cargar-bachillerato.php", { annlectivo: idAnnLectivo });
    });

    // Cuando el usuario seleccione una Modalidad, cargamos Grado-Sección-Turno con dos variables
    $("#lstmodalidad").change(function() {
        let idAnnLectivo = $("#lstannlectivo").val();  // Año Lectivo seleccionado
        let idModalidad = $(this).val();  // Modalidad seleccionada
        cargarOpcionesMultiples("#lstgradoseccion", "includes/cargar-grado-seccion.php", { annlectivo: idAnnLectivo, modalidad: idModalidad });
    });
    // Continúa para Asignatura y Período
    $("#lstgradoseccion").change(function() {
        let idGradoSeccion = $(this).val();
        let idAnnLectivo = $("#lstannlectivo").val();  // Año Lectivo seleccionado
        let idModalidad = $("#lstmodalidad").val();  // Modalidad seleccionada
        cargarOpcionesMultiples("#lstasignatura", "includes/cargar-asignatura.php", { codigo_grado_seccion_turno: idGradoSeccion, annlectivo: idAnnLectivo, modalidad: idModalidad});
    });
    // continua para 
    $("#lstasignatura").change(function() {
        let idAsignatura = $(this).val();
        cargarOpcionesDependiente("#lstperiodo", "includes/cargar-periodo.php", { asignatura: idAsignatura });
    });

});