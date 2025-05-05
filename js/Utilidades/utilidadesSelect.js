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