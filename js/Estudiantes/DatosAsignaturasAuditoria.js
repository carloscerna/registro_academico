$(function(){
    // Restablecer la pantalla al hacer clic en Cancelar
    $('#goCancelar').on('click',function(){
        $("#goBuscar").prop("disabled", false);
        $("#goActualizar").prop("disabled", true);
        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
        $('#listaPnOK').empty();
        $('#tabstabla').hide();
        $('#lstannlectivo').focus();
    });

// Funcionalidad del botón Actualizar Masivo con Barra de Progreso Soportada
$('#goActualizar').on('click', function(){
    let infoFiltros = $("#formAsignaturasAuditoria").serialize();

    if(!confirm("¿Desea iniciar la sincronización y depuración de la carga académica para este grado?")) {
        return;
    }

    // 1. Solicitamos la lista de alumnos pertenecientes a la sección
    $.ajax({
        cache: false,
        type: "POST",
        dataType: "json",
        url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
        data: infoFiltros + "&accion=ObtenerMatriculasSeccion",
        beforeSend: function() {
            $('#goActualizar, #goCancelar').prop('disabled', true);
            $('#contenedorProgresoSincro').fadeIn();
            ajustarBarra(0, "Preparando lista de estudiantes...");
        },
        success: async function(res) {
            if(res.respuesta && res.contenido.length > 0) {
                let listaAlumnos = res.contenido;
                let totalAlumnos = listaAlumnos.length;
                let procesadosExito = 0;

                // 2. Iteramos en bucle asíncrono ordenado para simular la barra de progreso real
                for (let i = 0; i < totalAlumnos; i++) {
                    let alumno = listaAlumnos[i];
                    let porcentaje = Math.round(((i + 1) / totalAlumnos) * 100);
                    
                    ajustarBarra(porcentaje, "Sincronizando estudiante " + (i + 1) + " de " + totalAlumnos);

                    // Enviamos la petición individual sincrónica por alumno
                    await $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
                        data: infoFiltros + "&accion=SincronizarUnEstudiante&id_alumno_matricula=" + alumno.id_alumno_matricula + "&codigo_alumno=" + alumno.codigo_alumno
                    }).then(response => {
                        if(response.respuesta) procesadosExito++;
                    }).catch(() => {
                        console.error("Error al procesar la matrícula: " + alumno.id_alumno_matricula);
                    });
                }

                // 3. Render final del proceso
                toastr.success("Sincronización masiva finalizada con éxito. Alumnos depurados: " + procesadosExito);
                setTimeout(() => {
                    $('#contenedorProgresoSincro').fadeOut(300, function(){
                        ajustarBarra(0, "");
                        // Reactivamos la búsqueda para actualizar la tabla completa a Verde (Completo)
                        $("#goBuscar").prop("disabled", false);
                        $('#goBuscar').click();
                    });
                }, 1500);

            } else {
                toastr.error("No se pudo estructurar el mapa de estudiantes para procesar la barra.");
                resetBotonesAccion();
            }
        },
        error: function() {
            toastr.error("Error de comunicación de red al preparar la carga.");
            resetBotonesAccion();
        }
    });
});

// Función auxiliar para actualizar la barra de progreso
function ajustarBarra(porcentaje, texto) {
    $('#textoProgresoEstatus').text(texto);
    $('#porcentajeProgresoTxt').text(porcentaje + "%");
    $('#barraProgresoSincro').css('width', porcentaje + '%').attr('aria-valuenow', porcentaje).text(porcentaje + "%");
}

function resetBotonesAccion() {
    $('#goCancelar').prop('disabled', false).click();
    $('#contenedorProgresoSincro').hide();
}

    // Validación del Formulario de Búsqueda (Auditoría Inicial)
    $("#formAsignaturasAuditoria").validate({
        rules: {
            lstannlectivo: { required: true },
            lstmodalidad: { required: true },
            lstgradoseccion: { required: true }
        },
        submitHandler: function(form) {
            let infoFiltros = $(form).serialize();
            let dataPayload = infoFiltros + "&accion=BuscarLista";

            $.ajax({
                beforeSend: function(){
                    $('#goBuscar').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Auditando...');
                },
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
                data: dataPayload,
                success: function(response){
                    if(response.respuesta === false){
                        toastr.warning(response.mensaje || "No se encontraron registros.");
                        $('#listaPnOK').empty();
                        $('#tabstabla').hide();
                    } else {
                        toastr.success("Auditoría de carga generada.");
                        $('#listaPnOK').html(response.contenido);
                        $('.table-responsive').scrollTop(0);
                        $('#tabstabla').show();
                        
                        // Bloquear filtros para evitar desajustes en la actualización masiva
                        $("#goActualizar").prop("disabled", false);
                        $("#goBuscar").prop("disabled", true);
                        $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", true);
                    }
                },
                error: function(){
                    toastr.error("Error de Ajax al buscar la nómina.");
                },
                complete: function() {
                    $('#goBuscar').html('<i class="fas fa-search"></i> Buscar Registros');
                }
            });
            return false;
        },
    });


    // EVENTO: Capturar clic en el botón de ver detalle individual de la fila
    $('#listaPnOK').on('click', '.btn-ver-detalle', function(){
        let id_matricula = $(this).data('matricula');
        let nombre_estudiante = $(this).data('nombre');
        let nie = $(this).data('nie');

        // Seteamos el título del modal con la información del alumno seleccionado
        $('#modalTituloNombre').html('<i class="fas fa-user-search"></i> Diagnóstico: ' + nombre_estudiante + ' (NIE: ' + nie + ')');

        $.ajax({
            beforeSend: function(){
                $('#modalCuerpoDetalle').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted">Construyendo comparación de carga académica...</p></div>');
                $('#modalDetalleEstudiante').modal('show'); // Desplegamos el cuadro modal
            },
            cache: false,
            type: "POST",
            dataType: "json",
            url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
            data: {
                accion: 'VerDetalleAlumno',
                id_matricula: id_matricula
            },
            success: function(response){
                if(response.respuesta === true){
                    $('#modalCuerpoDetalle').html(response.contenido);
                } else {
                    $('#modalCuerpoDetalle').html('<div class="alert alert-danger">' + response.mensaje + '</div>');
                }
            },
            error: function(){
                $('#modalCuerpoDetalle').html('<div class="alert alert-danger">Error crítico de comunicación con el backend de auditoría.</div>');
            }
        });
    });

});