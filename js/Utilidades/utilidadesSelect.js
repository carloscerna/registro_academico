// Función para cargar opciones en un select de forma independiente
function cargarOpciones(selector, url) {
    $.ajax({
        url: url,
        type: "GET",
        dataType: "json",
        success: function(data) {
            $(selector).empty().append('<option value="">Seleccione...</option>');
            $.each(data, function(index, item) {
                $(selector).append('<option value="'+item.codigo+'">'+item.nombre+'</option>');
            });
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar datos desde " + url + ": " + error);
        }
    });
}

// Función para cargar opciones en un select de forma dependiente
function cargarOpcionesDependiente(selector, url, parametros) {
    $.ajax({
        url: url,
        type: "GET",
        data: parametros,
        dataType: "json",
        success: function(data) {
            $(selector).empty().append('<option value="">Seleccione...</option>');
            $.each(data, function(index, item) {
                $(selector).append('<option value="'+item.codigo+'">'+item.nombre+'</option>');
            });
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar datos desde " + url + ": " + error);
        }
    });
}
// Función para cargar opciones en un select con múltiples parámetros
function cargarOpcionesMultiples(selector, url, parametros) {
    $.ajax({
        url: url,
        type: "GET",
        data: parametros,
        dataType: "json",
        success: function(data) {
            $(selector).empty().append('<option value="">Seleccione...</option>');
            $.each(data, function(index, item) {
                $(selector).append('<option value="'+item.codigo+'">'+item.nombre+'</option>');
            });
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar datos desde " + url + ": " + error);
        }
    });
}
// 📌 Función para cargar el número de períodos según la modalidad
function cargarPeriodosPorModalidad(selector, idModalidad) {
    $.ajax({
        url: "includes/cargar-periodos-cantidad.php",
        type: "GET",
        data: { modalidad: idModalidad },
        dataType: "json",
        success: function(data) {
            $(selector).empty().append('<option value="">Seleccione...</option>');
            // Pasar el valor de calificación Mínima.
            $("#calificacionMinima").val(data.calificacion_minima);

            if (data.cantidad_periodos) {
                for (let i = 1; i <= data.cantidad_periodos; i++) {
                    $(selector).append('<option value="'+i+'">Período ' + i + '</option>');
                }
                 // 📌 Agregar opciones de recuperación
                 $(selector).append('<option value="Recuperación">Recuperación 1 y 2</option>');
                 ///$(selector).append('<option value="Recuperación 2">Recuperación 2</option>');
 
            } else {
                $(selector).append('<option value="">No hay períodos registrados</option>');
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener cantidad de períodos: " + error);
        }
    });
}