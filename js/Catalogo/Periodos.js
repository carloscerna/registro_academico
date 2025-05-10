$(document).ready(function() {
    cargarOpcionesDependiente("#lstmodalidad", "includes/cargar-bachillerato.php",{annlectivo: "0"}); // Ahora carga Bachillerato
    cargarPeriodos(); // Cargar lista de períodos

    $("#formPeriodo").submit(function(event) {
        event.preventDefault();
        let idPeriodo = $("#idPeriodo").val(); // Para editar
        let codigoModalidad = $("#lstmodalidad").val();
        let cantidadPeriodos = $("#cantidad_periodos").val();
        let calificacionMinima = $("#calificacion_minima").val();  // Nuevo campo


        let action = idPeriodo ? "editar" : "guardar"; // Determinar si es edición o guardado

        $.ajax({
            url: "php_libs/soporte/Catalogo/Periodos.php",
            type: "POST",
            data: { 
                action: action, 
                id: idPeriodo, 
                lstmodalidad: codigoModalidad, 
                cantidad_periodos: cantidadPeriodos,
                calificacion_minima: calificacionMinima  // Nuevo campo
            },
            dataType: "json",
            success: function(response) {
                if (response.success) {
                    Swal.fire("Éxito", response.success, "success");
                    $("#formPeriodo")[0].reset();
                    $("#idPeriodo").val(""); // Limpiar ID después de edición
                    cargarPeriodos();
                } else {
                    Swal.fire("Error", response.error, "error");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al guardar/editar período: " + error);
            }
        });
    });

    function cargarPeriodos() {
        $.ajax({
            url: "php_libs/soporte/Catalogo/Periodos.php",
            type: "POST",
            data: { action: "listar" },
            dataType: "json",
            success: function(data) {
                let tbody = $("#tablaPeriodos tbody").empty();
                $.each(data, function(index, item) {
                    tbody.append(
                        "<tr><td>" + item.id + "</td><td>" + item.codigo_modalidad +  "</td><td>" + item.nombre_modalidad + "</td><td>" + item.cantidad_periodos + "</td><td>" + item.calificacion_minima + "</td>" +
                        "<td>" +
                        "<button class='btn btn-primary btn-sm editar' data-id='" + item.id + "' data-modalidad='" + item.codigo_modalidad + 
                        "' data-periodos='" + item.cantidad_periodos + "' data-calificacion='" + item.calificacion_minima + "' " +
                        "title='Editar'>" +  // Agregamos el title
                        "<i class='fas fa-edit'></i>" +  // Icono de Font Awesome
                        "</button> " +
                        "<button class='btn btn-danger btn-sm eliminar' data-id='" + item.id + "' " +
                        "title='Eliminar'>" +  // Agregamos el title
                        "<i class='fas fa-trash-alt'></i>" +  // Icono de Font Awesome
                        "</button>" +
                        "</td></tr>"
                    );
                });
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener períodos: " + error);
            }
        });
    }

    // Capturar evento de edición
    $(document).on("click", ".editar", function() {
        let id = $(this).data("id");
        let modalidad = $(this).data("modalidad");
        let periodos = $(this).data("periodos");
        let calificacion = $(this).data("calificacion");

        $("#idPeriodo").val(id);
        $("#lstmodalidad").val(modalidad);
        $("#cantidad_periodos").val(periodos);
        $("#calificacion_minima").val(calificacion);
        $("#action").val("editar");

        // Mostrar SweetAlert
        Swal.fire({
            title: "Editando Período",
            text: "Se cargarán los datos para editar.",
            icon: "info",
            confirmButtonText: "Entendido"
        });

        // Desplazamiento suave hacia el formulario
        $('html, body').animate({
            scrollTop: $("#formPeriodo").offset().top - 50
        }, 500);

        // Cambiar el nombre del botón a "Actualizar"
        $("button[type='submit']").text("Actualizar");

        // Desactivar el select de modalidad
        $("#lstmodalidad").prop("disabled", true);

        // Mostrar el botón "Cancelar" (si es necesario, asegúrate de que exista en el HTML)
        $("#btnCancelar").show();
    });

    $(document).on("click", ".eliminar", function() {
        let idPeriodo = $(this).data("id");
        Swal.fire({
            title: "¿Eliminar este período?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "php_libs/soporte/Catalogo/Periodos.php",
                    type: "POST",
                    data: { action: "eliminar", id: idPeriodo },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire("Eliminado", response.success, "success");
                            cargarPeriodos();
                        } else {
                            Swal.fire("Error", response.error, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error al eliminar período: " + error);
                    }
                });
            }
        });
    });
    // Evento para el botón "Cancelar"
    $("#btnCancelar").click(function() {
        resetForm(); // Usamos la función resetForm()
    });

    // Función para restablecer el formulario
    function resetForm() {
        $("#formPeriodo")[0].reset();
        $("#idPeriodo").val("");
        $("#action").val("guardar");
        $("button[type='submit']").text("Guardar");
        $("#lstmodalidad").prop("disabled", false);
        $("#btnCancelar").hide();
    }

    // Inicialmente, ocultar el botón "Cancelar"
    $("#btnCancelar").hide();
});