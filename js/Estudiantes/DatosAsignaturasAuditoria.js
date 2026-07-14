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

    // Funcionalidad del botón Actualizar Masivo con SweetAlert2 y Barra de Progreso Soportada
$('#goActualizar').on('click', function(){
    // Capturamos los filtros principales uno por uno de forma segura
    let annlectivo = $('#lstannlectivo').val();
    let modalidad = $('#lstmodalidad').val();
    let gradoseccion = $('#lstgradoseccion').val();

    // CAMBIO A SWEETALERT2: Alerta estilizada de confirmación antes de proceder
    Swal.fire({
        title: '¿Iniciar actualización masiva?',
        text: "Se sincronizarán las asignaturas oficiales y se depurarán las cargas incorrectas para todos los estudiantes de esta sección.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: '<i class="fas fa-check"></i> Sí, actualizar',
        cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
        focusCancel: true
    }).then((result) => {
        // Si el usuario confirma la acción en SweetAlert
        if (result.isConfirmed) {
            
            // 1. Solicitamos la lista completa de alumnos pertenecientes a la sección
            $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
                data: {
                    accion: 'ObtenerMatriculasSeccion',
                    lstannlectivo: annlectivo,
                    lstmodalidad: modalidad,
                    lstgradoseccion: gradoseccion
                },
                beforeSend: function() {
                    // Deshabilitamos todos los controles durante el proceso para evitar dobles clics
                    $('#goActualizar, #goBuscar, #goCancelar').prop('disabled', true);
                    $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", true);
                    $('#contenedorProgresoSincro').fadeIn();
                    ajustarBarra(0, "Preparando lista de estudiantes...");
                },
                success: async function(res) {
                    if(res.respuesta && res.contenido.length > 0) {
                        let listaAlumnos = res.contenido;
                        let totalAlumnos = listaAlumnos.length;
                        let procesadosExito = 0;

                        // 2. Iteramos en bucle asíncrono ordenado uno por uno
                        for (let i = 0; i < totalAlumnos; i++) {
                            let alumno = listaAlumnos[i];
                            let porcentaje = Math.round(((i + 1) / totalAlumnos) * 100);
                            
                            ajustarBarra(porcentaje, "Sincronizando estudiante " + (i + 1) + " de " + totalAlumnos);

                            // Enviamos la petición individual asegurando TODOS los filtros necesarios en el POST
                            await $.ajax({
                                type: "POST",
                                dataType: "json",
                                url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
                                data: {
                                    accion: 'SincronizarUnEstudiante',
                                    lstannlectivo: annlectivo,
                                    lstmodalidad: modalidad,
                                    lstgradoseccion: gradoseccion,
                                    id_alumno_matricula: alumno.id_alumno_matricula,
                                    codigo_alumno: alumno.codigo_alumno
                                }
                            }).then(response => {
                                if(response.respuesta === true) {
                                    procesadosExito++;
                                } else {
                                    console.warn("Aviso en matrícula " + alumno.id_alumno_matricula + ": " + response.mensaje);
                                }
                            }).catch(err => {
                                console.error("Error crítico de red en matrícula: " + alumno.id_alumno_matricula);
                            });
                        }

                        // Mensaje final usando un SweetAlert tipo Toast (pequeño y elegante en la esquina superior)
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: '¡Sincronización finalizada!',
                            text: 'Se depuraron con éxito ' + procesadosExito + ' estudiantes.',
                            showConfirmButton: false,
                            timer: 3500,
                            timerProgressBar: true
                        });
                        
                        setTimeout(() => {
                            $('#contenedorProgresoSincro').fadeOut(300, function(){
                                ajustarBarra(0, "");
                                
                                // REACTIVACIÓN TOTAL DE LA INTERFAZ
                                $("#goBuscar, #goCancelar").prop("disabled", false);
                                $("#goActualizar").prop("disabled", true); // Queda deshabilitado hasta una nueva búsqueda
                                $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
                                
                                // Forzamos el refresco visual automático de la tabla para ver las nóminas en verde
                                $('#goBuscar').click(); 
                            });
                        }, 1000);

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ops...',
                            text: res.mensaje || "No se encontraron estudiantes activos para procesar en esta sección."
                        });
                        restablecerInterfazError();
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Red',
                        text: "Error crítico de comunicación al preparar la carga académica."
                    });
                    restablecerInterfazError();
                }
            });

        }
    });
});

// Función auxiliar para actualizar dinámicamente la barra de progreso
function ajustarBarra(porcentaje, texto) {
    $('#textoProgresoEstatus').text(texto);
    $('#porcentajeProgresoTxt').text(porcentaje + "%");
    $('#barraProgresoSincro').css('width', porcentaje + '%').attr('aria-valuenow', porcentaje).text(porcentaje + "%");
}

// Función de rescate en caso de error intermedio, devolviendo el control al usuario
function restablecerInterfazError() {
    $('#contenedorProgresoSincro').hide();
    $("#goBuscar, #goCancelar").prop("disabled", false);
    $("#goActualizar").prop("disabled", false);
    $("#lstannlectivo, #lstmodalidad, #lstgradoseccion").prop("disabled", false);
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

        // EVENTO: Eliminar de forma quirúrgica una asignatura individual desde adentro del modal
        $('#modalCuerpoDetalle').on('click', '.btn-eliminar-materia-manual', function(){
            let btn = $(this);
            let id_matricula = btn.data('matricula');
            let codigo_asignatura = btn.data('asignatura');

            Swal.fire({
                title: '¿Quitar esta asignatura?',
                text: "Esta acción removerá de forma permanente el registro de esta materia para este estudiante.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
                        data: {
                            accion: 'EliminarAsignaturaIndividual',
                            id_matricula: id_matricula,
                            codigo_asignatura: codigo_asignatura
                        },
                        success: function(response){
                            if(response.respuesta === true){
                                Swal.fire({
                                    toast: true,
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Materia eliminada',
                                    showConfirmButton: false,
                                    timer: 2000
                                });

                                // REFRESCAR EL MODAL: Volvemos a mandar la petición para re-renderizar las listas del modal actualizado
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    url: "php_libs/soporte/PhpDatosAsignaturasAuditoria.php",
                                    data: { accion: 'VerDetalleAlumno', id_matricula: id_matricula },
                                    success: function(resModal){
                                        if(resModal.respuesta === true){
                                            $('#modalCuerpoDetalle').html(resModal.contenido);
                                        }
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'No se pudo eliminar',
                                    text: response.mensaje
                                });
                            }
                        },
                        error: function(){
                            toastr.error("Error de comunicación con el servidor al intentar eliminar.");
                        }
                    });
                }
            });
        });

});