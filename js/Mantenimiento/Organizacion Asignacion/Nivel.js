// id de user global
var idUser_ok = 0;
var accion_dn = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo = "";
var codigo_modalidad = "";
var msjEtiqueta = "";

// INICIO DE LA FUNCION PRINCIPAL.
$(function(){

    // INVISILBLE TODOS LOS MENSAJES.
    $("#AlertDN").css("display", "none");

    // OPCIONES PARA EL TAB NAV
    $(document).ready(function () {
        var miselect = $("#lstAnnLectivoDN");
        
        // Cargar Año Lectivo primero
        cargarOpciones(miselect, "includes/cargar-ann-lectivo.php");

        // CUANDO EL VALOR DE ANNLECTIVO CAMBIA.
        var miselect2 = $("#lstModalidadDN");
        $(miselect).change(function() {
            let idAnnLectivo = $(this).val();
            cargarOpcionesDependiente(miselect2, "includes/cargar-bachillerato.php", { annlectivo: idAnnLectivo });
        });

        // CUANDO EL VALOR DE NIVEL O MODALIDAD CAMBIE.
        $("#lstModalidadDN").change(function () {
            var modalidad = $(this).val();

            if (modalidad == "00") {
                // borrar el contenido de la Tabla.
                $('#listaContenidoDN').empty();
                // limpiar selects
                $("#lstDocenteNivel").empty();
                $("#lstTurnoDN").empty();
            } else {
                // borrar el contenido de la Tabla.
                $('#listaContenidoDN').empty();

                // LISTAR PARA EL SERVIICO EDUCATIVO - COMPONENTES DE ESTUDIOS (DOCENTES)
                var miselectDocente = $("#lstDocenteNivel");
                miselectDocente.html('<option value="">Cargando...</option>');
                
                $.post("includes/cargar_nombre_personal.php", function(data) {
                    miselectDocente.empty().append("<option value='00'>Seleccionar...</option>");
                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            miselectDocente.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
                    }
                }, "json");

                // LISTAR PARA EL SERVIICO EDUCATIVO - TURNO
                var miselectTurno = $("#lstTurnoDN");
                miselectTurno.html('<option value="">Cargando...</option>');
                
                $.post("includes/cargar_turno.php", function(data) {
                    miselectTurno.empty().append("<option value='00'>Seleccionar...</option>");
                    if (data && data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            miselectTurno.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
                    }
                }, "json");
            }
        });

        ////////////////////////////////////////////////////////////////////////////
        // EXTRAER DATOS DEPENDIENTE DEL TAB DE NAV
        //////////////////////////////////////////////////////////////////////////
        $("#NavOrganizacionAsignacion ul.nav > li > a").on("click", function () {
            var TextoTab = $(this).text();
            if (TextoTab == "Docente/Nivel") {
                // Borrar información de la Tabla.
                $('#listaContenidoDN').empty();
                $("#AlertDN").css("display", "none");
                // Select a 00...
                $("#lstAnnLectivoDN").val('00');
                $("#lstModalidadDN").val('00');
            }
        });

        // BUSCAR REGISTROS (HORARIOS CREADAS)
        $('#lstAnnLectivoDN').on('change', function() {
            $("#AlertDN").css("display", "none");
        });

        $('#lstModalidadDN').on('change', function() {
            $("#AlertDN").css("display", "none");
        });

        // Funcionalidad del botón que abre / cierra el formulario modal
        $("#VentanaDN").on('hidden.bs.modal', function () {
            $("#formVentanaDN")[0].reset();
            $('#formVentanaDN').trigger("reset");
            $("label.error").remove();
            accion = "";
        });
    });

    // BLOQUE EXTRAER INFORMACIÓN DE LOS REGISTROS
    $('body').on('click', '#listaContenidoDN a', function (e) {
        e.preventDefault();
        
        Id_Editar_Eliminar = $(this).attr('href');
        var accion_ok = $(this).attr('data-accion');

     // EDITAR REGISTRO
if (accion_ok == 'EditarDN') {
    $('#accion_dn').val('EditarDN');
    accion = 'EditarDN';
    
    var id_ = $(this).parent().parent().children('td:eq(2)').text();
    
    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", { id_: id_, accion: accion }, function(data) {
        var texto_annlectivo_dn = $("#lstAnnLectivoDN option:selected").html();
        var texto_modalidad_dn = $("#lstModalidadDN option:selected").html();
        
        // CORRECCIÓN AQUÍ: Apuntar a los valores visibles del modal
        $("#valAnnLectivoDN").text(texto_annlectivo_dn);
        $("#valModalidadDN").text(texto_modalidad_dn);
        
        listar_CodigoDN(data[0].codigo_docente);
        listar_CodigoTurnoDN(data[0].codigo_turno);
        
        $('#VentanaDN').modal("show");
        $("label[for=LblTituloDN]").text("Docente/Nivel | Actualizar");
        accion_dn = "ActualizarDN";
    }, "json");
}

        // ELIMINAR REGISTRO
        if (accion_ok == 'EliminarDN') {
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            });

            swalWithBootstrapButtons.fire({
                title: '¿Qué desea hacer?',
                text: 'Eliminar el Registro Seleccionado!',
                showCancelButton: true,
                confirmButtonText: 'Sí, Eliminar!',
                cancelButtonText: 'No, Cancelar!',
                reverseButtons: true,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                stopKeydownPropagation: false,
                closeButtonAriaLabel: 'Cerrar Alerta',
                type: 'question'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        cache: false,                     
                        type: "POST",                     
                        dataType: "json",                     
                        url: "php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",                     
                        data: { accion_buscar: 'EliminarDN', id_: Id_Editar_Eliminar },                     
                        success: function(response) {                     
                            if (response.respuesta === true) {                     		
                                toastr["info"]('Registros Eliminados', "Sistema");
                                $('#accion_dn').val('BuscarDN');
                                accion = 'BuscarDN';
                                
                                $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", { accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad }, function(response) {
                                    if (response.respuesta === true) {
                                        toastr["info"]('Registros Encontrados', "Sistema");
                                    } else {
                                        toastr["warning"]('Registros No Encontrados', "Sistema");
                                    }
                                    $('#listaContenidoDN').empty().append(response.contenido);
                                }, "json");
                            }
                        }                     
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    swalWithBootstrapButtons.fire(
                        'Cancelar',
                        'Su Registro no ha sido Eliminado :)',
                        'error'
                    );
                }
            });
        }
    });

    // ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
    $("#checkBoxAllDN").on("change", function () {
        $("#listadoContenidoDN tbody input[type='checkbox'].case").prop("checked", this.checked);
    });
	
    $("#listadoContenidoDN tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoDN tbody input[type='checkbox'].case").length == $("#listadoContenidoDN tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllDN").prop("checked", true);
        } else {
            $("#checkBoxAllDN").prop("checked", false);
        }
    });	

    // ACCIÓN BUSCAR
    $('#goBuscarDN').on('click', function(){
        codigo_annlectivo = $("#lstAnnLectivoDN").val();
        codigo_modalidad = $("#lstModalidadDN").val();
        accion = 'BuscarDN';

        if (codigo_annlectivo == "00") {
            $("#AlertDN").css("display", "block");
            $("#TextoAlertDN").text("Debe Seleccionar Año Lectivo para Buscar.");
            return;
        }
        if (codigo_modalidad == "00") {
            $("#AlertDN").css("display", "block");
            $("#TextoAlertDN").text("Debe Seleccionar la Modalidad para Buscar.");
            return;
        }

        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", { accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad }, function(response) {
            if (response.respuesta === true) {
                toastr["info"]('Registros Encontrados', "Sistema");
            } else {
                toastr["error"]('Registros No Encontrados', "Sistema");
            }
            $('#listaContenidoDN').empty().append(response.contenido);
        }, "json");
    });

    // ACCIÓN GUARDAR
    $('#goGuardarDN').on('click', function(){
        codigo_annlectivo = $("#lstAnnLectivoDN").val();
        codigo_modalidad = $("#lstModalidadDN").val();
        var codigo_dn = $("#lstDocenteNivel").val();
        var codigo_turno = $("#lstTurnoDN").val();
        
        accion = 'GuardarDN';
        $('#accion_dn').val('GuardarDN');

        if (codigo_annlectivo == "00") {
            $("#AlertDN").css("display", "block");
            $("#TextoAlertDN").text("Debe Seleccionar un Año Lectivo para Guardar un Nivel.");
            return;
        }
        if (codigo_modalidad == "00") {
            $("#AlertDN").css("display", "block");
            $("#TextoAlertDN").text("Debe Seleccionar un Nivel para Guardar.");
            return;
        }
        if (codigo_turno == "00" || !codigo_turno) {
            $("#AlertDN").css("display", "block");
            $("#TextoAlertDN").text("Debe Seleccionar un Turno para Guardar.");
            return;
        }
        if (codigo_dn == "00" || !codigo_dn) {
            $("#AlertDN").css("display", "block");
            $("#TextoAlertDN").text("Debe Seleccionar un Docente para Guardar.");
            return;
        }

        $('#FormDN').submit();
    });

    // ACCIÓN ACTUALIZAR
    $('#goActualizarDN').on('click', function(){
        codigo_annlectivo = $("#lstAnnLectivoDN").val();
        codigo_modalidad = $("#lstModalidadDN").val();
        
        accion = 'ActualizarDN';
        $('#accion_dn').val('ActualizarDN');

        $('#formVentanaDN').submit();
    });

    // VALIDACIÓN Y ENVÍO DEL FORMULARIO MODAL (ACTUALIZAR)
    $('#formVentanaDN').validate({
        ignore: "",
        rules: {
            lstDocenteNivel: { required: true },
            lstTurnoDN: { required: true },
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.next("label"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        invalidHandler: function() {
            setTimeout(function() {
                toastr["error"]("Falta Información en el Formulario.", "Sistema");
            });            
        },
        submitHandler: function(){	
            var str = $('#formVentanaDN').serialize();
            $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                data: str + "&accion=" + accion + "&id=" + Math.random() + "&id_=" + Id_Editar_Eliminar,
                success: function(response){
                    if(response.respuesta == false){
                        toastr["error"](response.mensaje, "Sistema");
                    } else {
                        toastr["success"](response.mensaje, "Sistema");
                        $('#VentanaDN').modal("hide");
                        $("#formVentanaDN").trigger("reset");

                        $('#accion_dn').val('BuscarDN');
                        accion = 'BuscarDN';
                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", { accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad }, function(response) {
                            if (response.respuesta === true) {
                                toastr["info"]('Registros Encontrados', "Sistema");
                            } else {
                                toastr["warning"]('Registros No Encontrados', "Sistema");
                            }
                            $('#listaContenidoDN').empty().append(response.contenido);
                        }, "json");
                    }               
                },
            });
        },
    });

    // VALIDACIÓN Y ENVÍO DEL FORMULARIO PRINCIPAL (GUARDAR)
    $('#FormDN').validate({
        ignore: "",
        rules: {
            lstAnnLectivoDN: { required: true },
        },
        errorElement: "em",
        errorPlacement: function (error, element) {
            error.addClass("invalid-feedback");
            if (element.prop("type") === "checkbox") {
                error.insertAfter(element.next("label"));
            } else {
                error.insertAfter(element);
            }
        },
        highlight: function (element) {
            $(element).addClass("is-invalid").removeClass("is-valid");
        },
        unhighlight: function (element) {
            $(element).addClass("is-valid").removeClass("is-invalid");
        },
        invalidHandler: function() {
            setTimeout(function() {
                toastr["error"]("Falta Información en el Formulario.", "Sistema");
            });            
        },
        submitHandler: function(){	
            var str = $('#FormDN').serialize();
            $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                url: "php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                data: str + "&accion=" + accion + "&id=" + Math.random() + "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_modalidad=" + codigo_modalidad,
                success: function(response){
                    if(response.respuesta == false){
                        toastr["error"](response.mensaje, "Sistema");
                    } else {
                        toastr["success"](response.mensaje, "Sistema");
                        $('#accion_dn').val('BuscarDN');
                        accion = 'BuscarDN';
                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php", { accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad }, function(response) {
                            if (response.respuesta === true) {
                                toastr["info"]('Registros Encontrados', "Sistema");
                            } else {
                                toastr["warning"]('Registros No Encontrados', "Sistema");
                            }
                            $('#listaContenidoDN').empty().append(response.contenido);
                        }, "json");
                    }               
                },
            });
        },
    });
}); // FIN DEL FUNCTION PRINCIPAL

// Pantalla de Carga de Ajax
function configureLoadingScreen(screen){
    $(document)
        .ajaxStart(function () {
            screen.fadeIn();
        })
        .ajaxStop(function () {
            screen.fadeOut();
        });
}

// FUNCIONES AUXILIARES PARA EDICIÓN
function listar_CodigoDN(CodigoDN){
    var miselect = $("#formVentanaDN select[name=lstDocenteNivel]");
    miselect.html('<option value="">Cargando...</option>');
    
    $.post("includes/cargar_nombre_personal.php", function(data) {
        miselect.empty();
        if (data && data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                if (CodigoDN == data[i].codigo) {
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                } else {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
        }
    }, "json");    
}

function listar_CodigoTurnoDN(CodigoTurnoDN){
    var miselect = $("#formVentanaDN select[name=lstTurnoDN]");
    miselect.html('<option value="">Cargando...</option>');
    
    $.post("includes/cargar_turno.php", function(data) {
        miselect.empty();
        if (data && data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                if (CodigoTurnoDN == data[i].codigo) {
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                } else {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
        }
    }, "json");    
}