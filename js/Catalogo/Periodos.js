$(document).ready(function() {
    cargarOpcionesDependiente("#lstmodalidad", "includes/cargar-bachillerato.php",{annlectivo: "0"}); // Ahora carga Bachillerato
    cargarPeriodos(); // Cargar lista de períodos

    $("#formPeriodo").submit(function(event) {
        event.preventDefault();
        let idPeriodo = $("#idPeriodo").val(); // Para editar
        let codigoModalidad = $("#lstmodalidad").val();
        let cantidadPeriodos = $("#cantidad_periodos").val();

        let action = idPeriodo ? "editar" : "guardar"; // Determinar si es edición o guardado

        $.ajax({
            url: "php_libs/soporte/Catalogo/Periodos.php",
            type: "POST",
            data: { action, id: idPeriodo, lstmodalidad: codigoModalidad, cantidad_periodos: cantidadPeriodos },
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
                        "<tr><td>" + item.id + "</td><td>" + item.codigo_modalidad +  "</td><td>" + item.nombre_modalidad + "</td><td>" + item.cantidad_periodos + "</td>" +
                        "<td><button class='btn btn-primary editar' data-id='" + item.id + "' data-modalidad='" + item.codigo_modalidad + 
                        "' data-periodos='" + item.cantidad_periodos + "'>Editar</button> " +
                        "<button class='btn btn-danger eliminar' data-id='" + item.id + "'>Eliminar</button></td></tr>"
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

        $("#idPeriodo").val(id);
        $("#lstmodalidad").val(modalidad);
        $("#cantidad_periodos").val(periodos);
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
});