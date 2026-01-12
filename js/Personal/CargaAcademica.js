/**
 * CargaAcademica.js - Versión Bootstrap 5
 */

// Variables Globales
var IdRegistroSeleccionado = 0;
var TipoEliminacion = ""; // 'EG' o 'CD'
var codigo_docente, codigo_ann_lectivo;

$(document).ready(function () {

    // --- BOTÓN CANCELAR / CERRAR ---
    $('#goCancelar').on('click', function () {
        $("#goCABuscar").prop("disabled", false);
        $("#lstannlectivo, #lstCodigoPersonal").prop("disabled", false);
        
        // Ocultar Panel de Gestión y Resetear
        $('#CargaAcademicaContenedor').fadeOut();
        $(this).hide(); // Ocultar botón cerrar
        $('#listaEG, #listaCD').empty();
    });

    // --- BOTÓN IMPRIMIR ---
    $('#goCAImprimir').on('click', function () {
        let ann = $('#lstannlectivo').val();
        let docente = $('#lstCodigoPersonal').val();
        
        if(!docente || docente === '00'){
            toastr.warning("Seleccione un docente primero.");
            return;
        }
        
        let url = "/registro_academico/php_libs/reportes/informe_carga_docente.php?codigo_annlectivo=" + ann + "&codigo_docente=" + docente;
        window.open(url, '_blank');
    });


    // ==============================================================
    // 1. CARGA INICIAL DE COMBOS (AÑO Y DOCENTES)
    // ==============================================================
    
    // Cargar Año Lectivo (Usando tu utilidad existente o carga directa)
    // Nota: Asumo que tienes un archivo "includes/cargar_ann_lectivo.php" o similar.
    // Si usas "cargarDatosSelect.js", asegúrate de llamarlo aquí.
    cargarAnnLectivo();

    // Evento: Cuando cambia el año, cargar docentes
    $("#lstannlectivo").on("change", function () {
        let ann = $(this).val();
        cargarDocentes(ann);
    });

    // Función para cargar Años (Si no la tienes en utilidades)
    function cargarAnnLectivo() {
        $.ajax({
            url: "includes/cargar_ann_lectivo.php", // Asegúrate que este archivo exista
            type: "POST",
            dataType: "json",
            success: function (data) {
                let opts = "";
                // Detectar año actual para seleccionarlo por defecto
                let currentYear = new Date().getFullYear();
                
                $.each(data, function (i, item) {
                    let selected = (item.nombre == currentYear) ? "selected" : "";
                    opts += `<option value="${item.codigo}" ${selected}>${item.nombre}</option>`;
                });
                
                $("#lstannlectivo").html(opts);
                // Una vez cargado el año, cargamos los docentes del ese año
                $("#lstannlectivo").trigger("change");
            }
        });
    }

    // Función para cargar Docentes
    function cargarDocentes(ann) {
        let selectDocente = $("#lstCodigoPersonal");
        
        // Limpiar Select2 antes de cargar
        selectDocente.html('<option value="00">Cargando...</option>');
        
        $.ajax({
            url: "includes/cargar_nombre_personal_docente.php",
            type: "POST",
            dataType: "json",
            data: { annlectivo: ann },
            success: function (data) {
                selectDocente.empty();
                selectDocente.append('<option value="00">Seleccione una opción</option>');
                
                $.each(data, function (i, item) {
                    selectDocente.append(`<option value="${item.codigo}">${item.descripcion}</option>`);
                });
                
                // Actualizar Select2 visualmente
                selectDocente.trigger('change'); 
            },
            error: function() {
                selectDocente.html('<option value="00">Error al cargar</option>');
            }
        });
    }

    // --- BOTÓN BUSCAR CARGA (ABRIR PANEL) ---
    $('#goCABuscar').on('click', function () {
        codigo_docente = $("#lstCodigoPersonal").val();
        codigo_ann_lectivo = $("#lstannlectivo").val();

        if (!codigo_docente || codigo_docente === "00") {
            toastr.warning("Por favor, seleccione un docente.");
            return;
        }

        // Bloquear selects superiores
        $("#goCABuscar").prop("disabled", true);
        $("#lstannlectivo, #lstCodigoPersonal").prop("disabled", true);
        $("#goCancelar").show();

        // Mostrar Panel Principal
        $('#CargaAcademicaContenedor').fadeIn();

        // Cargar Modalidades (Llenar selects de las pestañas)
        cargarModalidadesDocente();
        
        // Cargar Listados Iniciales
        BuscarEncargadoGrado();
        BuscarCargarAcademica();
    });

   // ==============================================================
    // PESTAÑA 1: ENCARGADO DE GRADO (Actualizado)
    // ==============================================================
    
    $('#goAgregarEG').on('click', function () {
        let modalidad = $("#lstCodigoModalidad").val();
        let gst = $("#lstCodigoGSTEG").val();
        let eg1 = $('#EG1').is(":checked") ? 'yes' : 'no';
        let ia1 = $('#IA1').is(":checked") ? 'yes' : 'no';

        if (!gst || gst === '00') {
            toastr.error("⚠️ Debe seleccionar Grado - Sección - Turno.");
            return;
        }

        $.ajax({
            url: "php_libs/soporte/Personal/CrearEG.php",
            type: "POST",
            dataType: "json",
            data: {
                accion: "GuardarEG",
                codigo_docente: codigo_docente,
                codigo_annlectivo: codigo_ann_lectivo,
                codigo_modalidad: modalidad,
                codigo_gst: gst,
                encargado_grado: eg1,
                imparte_asignatura: ia1
            },
            success: function (resp) {
                if (resp.respuesta) {
                    // Éxito: Mensaje verde y recarga
                    toastr.success("✅ " + resp.mensaje);
                    BuscarEncargadoGrado(); 
                } else {
                    // Error/Advertencia: Mensaje naranja/rojo directo del PHP
                    // Ya no usamos 'if mensaje == Si Existe', mostramos lo que diga el servidor
                    toastr.warning(resp.mensaje); 
                }
            }
        });
    });

    // Botón Buscar (Refrescar) EG
    $('#goBuscarEG').on('click', function () {
        BuscarEncargadoGrado();
    });

  // ==============================================================
    // GUARDAR ASIGNATURA (CARGA ACADÉMICA)
    // ==============================================================
    $('#goAgregarCD').on('click', function () {
        let modalidad = $("#lstCodigoModalidadCD").val();
        let gst = $("#lstCodigoGSTCD").val();
        let asig = $("#lstCodigoAsignaturaCD").val();

        // Validaciones básicas
        if (!asig || asig === '00') {
            toastr.error("⚠️ Seleccione una asignatura válida.");
            return;
        }

        $.ajax({
            url: "php_libs/soporte/Personal/CrearCD.php",
            type: "POST",
            dataType: "json",
            data: {
                accion: "GuardarCD",
                codigo_docente: codigo_docente,
                codigo_annlectivo: codigo_ann_lectivo,
                codigo_modalidad: modalidad,
                codigo_gst: gst,
                codigo_asignatura: asig
            },
            success: function (resp) {
                if (resp.respuesta) {
                    // ÉXITO: Usamos el mensaje que viene del PHP (✅ Asignatura asignada...)
                    toastr.success(resp.mensaje);
                    
                    // Recargamos la tabla para ver el cambio
                    BuscarCargarAcademica(); 
                    
                    // Opcional: Limpiar el select de asignatura para agregar otra rápida
                    // $("#lstCodigoAsignaturaCD").val('00').trigger('change');
                } else {
                    // ERROR: Usamos el mensaje del PHP (⚠️ Esta asignatura ya está...)
                    // IMPORTANTE: Antes buscabas en 'resp.contenido', ahora es 'resp.mensaje'
                    toastr.warning(resp.mensaje);
                }
            },
            error: function () {
                toastr.error("❌ Error de comunicación con el servidor.");
            }
        });
    });

    // Botón Buscar (Refrescar) CD
    $('#goBuscarCD').on('click', function () {
        BuscarCargarAcademica();
    });

    // ==============================================================
    // PESTAÑA 3: HOJA DE CÁLCULO
    // ==============================================================
    $('#goCrearHC').on('click', function () {
        let t1 = $('#T1').is(":checked") ? 'yes' : 'no';
        let t2 = $('#T2').is(":checked") ? 'yes' : 'no';
        let t3 = $('#T3').is(":checked") ? 'yes' : 'no';
        let t4 = $('#T4').is(":checked") ? 'yes' : 'no';
        let pendiente = $('#Pendiente').is(":checked") ? 'yes' : 'no';
        
        let urlDestino = (pendiente === 'yes') ? "php_libs/soporte/Personal/CrearHCPendientes.php" : "php_libs/soporte/Personal/CrearHC.php";

        $('#loadingExcel').show();
        $('#Informacion').hide().empty();
        $('#InformacionError').hide().empty();

        $.ajax({
            url: urlDestino,
            type: "POST",
            dataType: "json",
            data: {
                codigo_annlectivo: codigo_ann_lectivo,
                codigo_docente: codigo_docente,
                t1: t1, t2: t2, t3: t3, t4: t4
            },
            success: function (resp) {
                $('#loadingExcel').hide();
                if (resp.respuesta) {
                    toastr.success("Archivo creado exitosamente.");
                    $('#Informacion').html(resp.contenido).show();
                    // Agregar log a la tabla
                    $('#listaArchivoOK').append(`<tr><td><i class="fas fa-check text-success"></i> ${resp.mensaje}</td></tr>`);
                } else {
                    toastr.error("Error al crear archivo.");
                    $('#InformacionError').html(resp.mensaje).show();
                }
            },
            error: function () {
                $('#loadingExcel').hide();
                toastr.error("Error de conexión al generar Excel.");
            }
        });
    });

    // ==============================================================
    // MANEJO DE ELIMINACIÓN (MODAL COMPARTIDO)
    // ==============================================================
    
    // Delegación de eventos para botones eliminar en Tablas AJAX
    $('body').on('click', 'a[data-accion="eliminarEG"]', function (e) {
        e.preventDefault();
        IdRegistroSeleccionado = $(this).attr('href');
        TipoEliminacion = 'EG';
        var myModal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
        myModal.show();
    });

    $('body').on('click', 'a[data-accion="eliminarCD"]', function (e) {
        e.preventDefault();
        IdRegistroSeleccionado = $(this).attr('href');
        TipoEliminacion = 'CD';
        var myModal = new bootstrap.Modal(document.getElementById('modalConfirmarEliminar'));
        myModal.show();
    });

    // Confirmar Eliminación
    $('#btnConfirmarEliminar').on('click', function () {
        let urlAPI = (TipoEliminacion === 'EG') ? "php_libs/soporte/Personal/CrearEG.php" : "php_libs/soporte/Personal/CrearCD.php";
        let dataAPI = (TipoEliminacion === 'EG') ? { accion: 'eliminarEG', id_eg: IdRegistroSeleccionado } : { accion: 'eliminarCD', id_cd: IdRegistroSeleccionado };

        // Cerrar modal
        var modalEl = document.getElementById('modalConfirmarEliminar');
        var modalInstance = bootstrap.Modal.getInstance(modalEl);
        modalInstance.hide();

        $.ajax({
            url: urlAPI,
            type: "POST",
            dataType: "json",
            data: dataAPI,
            success: function (resp) {
                if (resp.respuesta) {
                    toastr.info("Registro eliminado.");
                    // Recargar la tabla correspondiente
                    if (TipoEliminacion === 'EG') BuscarEncargadoGrado();
                    else BuscarCargarAcademica();
                } else {
                    toastr.error("No se pudo eliminar.");
                }
            }
        });
    });

// ==============================================================
    // EVENTOS DE CARGA EN CASCADA (Modalidad -> Grado/Seccion)
    // ==============================================================

    // Pestaña 1: Encargado de Grado
    $("#lstCodigoModalidad").on("change", function () {
        let modalidad = $(this).val();
        let ann = $("#lstannlectivo").val();
        let selectDestino = $("#lstCodigoGSTEG"); // Select de Grado-Seccion

        console.log("Cargando grados para Modalidad:", modalidad); // DEBUG

        if(modalidad !== '00'){
            cargarGradosSecciones(ann, modalidad, selectDestino);
        } else {
            selectDestino.html('<option value="00">Seleccione Modalidad primero</option>');
        }
    });

    // Pestaña 2: Carga Académica
    $("#lstCodigoModalidadCD").on("change", function () {
        let modalidad = $(this).val();
        let ann = $("#lstannlectivo").val();
        let selectDestino = $("#lstCodigoGSTCD");

        if(modalidad !== '00'){
            cargarGradosSecciones(ann, modalidad, selectDestino);
        } else {
            selectDestino.html('<option value="00">Seleccione Modalidad primero</option>');
        }
    });

// ==============================================================
    // EVENTO: CARGAR ASIGNATURAS CON DETALLE VISUAL
    // ==============================================================
    
    $("#lstCodigoGSTCD").on("change", function () {
        let gst = $(this).val();
        let modalidad = $("#lstCodigoModalidadCD").val();
        let ann = $("#lstannlectivo").val();
        let selectAsignatura = $("#lstCodigoAsignaturaCD");

        if (gst !== '00') {
            cargarAsignaturasVisual(ann, modalidad, gst, selectAsignatura);
        } else {
            selectAsignatura.html('<option value="00">Seleccione Grado primero</option>');
        }
    });


}); // Fin Document Ready

// --- FUNCIONES AUXILIARES ---

function cargarModalidadesDocente() {
    let ann = $("#lstannlectivo").val();
    let cod = $("#lstCodigoPersonal").val();
    
    // Llenar selects de modalidad en ambas pestañas
    $.post("includes/cargar_nombre_personal_docente.php", { 
        annlectivo: ann, 
        cd: true, 
        codigo_personal: cod 
    }, function (data) {
        let opts = "<option value='00'>Seleccionar...</option>";
        for (let i = 0; i < data.length; i++) {
            opts += `<option value="${data[i].codigo}">${data[i].descripcion}</option>`;
        }
        $("#lstCodigoModalidad").html(opts);
        $("#lstCodigoModalidadCD").html(opts);
    }, "json");
}

function BuscarEncargadoGrado() {
    $('#listaEG').html('<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary spinner-border-sm"></div> Cargando...</td></tr>');
    
    $.ajax({
        url: "php_libs/soporte/Personal/CrearEG.php",
        type: "POST",
        dataType: "json",
        data: {
            accion: "BuscarEG",
            codigo_docente: codigo_docente,
            codigo_annlectivo: codigo_ann_lectivo
        },
        success: function (resp) {
            $('#listaEG').empty();
            if (resp.respuesta) {
                $('#listaEG').html(resp.contenido);
            } else {
                $('#listaEG').html('<tr><td colspan="7" class="text-center text-muted">No se encontraron registros.</td></tr>');
            }
        }
    });
}

function BuscarCargarAcademica() {
    $('#listaCD').html('<tr><td colspan="6" class="text-center"><div class="spinner-border text-primary spinner-border-sm"></div> Cargando...</td></tr>');

    $.ajax({
        url: "php_libs/soporte/Personal/CrearCD.php",
        type: "POST",
        dataType: "json",
        data: {
            accion: "BuscarCD",
            codigo_docente: codigo_docente,
            codigo_asignatura: $("#lstCodigoAsignaturaCD").val(),
            codigo_gst: $("#lstCodigoGSTCD").val(),
            codigo_modalidad: $("#lstCodigoModalidadCD").val(),
            codigo_annlectivo: codigo_ann_lectivo
        },
        success: function (resp) {
            $('#listaCD').empty();
            if (resp.respuesta) {
                $('#listaCD').html(resp.contenido);
            } else {
                $('#listaCD').html('<tr><td colspan="6" class="text-center text-muted">No se encontraron asignaturas.</td></tr>');
            }
        }
    });
}

// FUNCIÓN GENÉRICA PARA CARGAR GRADOS (CON DETALLES VISUALES)
function cargarGradosSecciones(ann, modalidad, elementoSelect) {
    // Mensaje de carga
    elementoSelect.html('<option value="00">Buscando grados...</option>');

    $.ajax({
        url: "includes/cargar-grado-seccion.php", 
        type: "POST",
        dataType: "json",
        data: { annlectivo: ann, modalidad: modalidad }, // Usamos 'annlectivo' corregido
        success: function (data) {
            elementoSelect.empty();
            
            if(data.length > 0){
                elementoSelect.append('<option value="00">Seleccione un Grado...</option>');
                
                $.each(data, function (i, item) {
                    let textoOpcion = "";
                    let claseEstilo = "";

                    // LÓGICA VISUAL "DETALLE SUPER"
                    if (item.ocupado) {
                        // Si ya tiene encargado: Candado Rojo + Nombre
                        textoOpcion = `🔒 ${item.descripcion} (Asignado a: ${item.encargado})`;
                    } else {
                        // Si está libre: Check Verde + Texto limpio
                        textoOpcion = `✅ ${item.descripcion} (Disponible)`;
                    }

                    // Creamos la opción
                    // Nota: No lo deshabilitamos (disabled) porque quizás quieras agregar
                    // una asignatura a ese grado aunque ya tenga encargado.
                    elementoSelect.append(`<option value="${item.codigo}">${textoOpcion}</option>`);
                });
                
            } else {
                elementoSelect.append('<option value="00">⚠️ No hay grados configurados</option>');
            }
            
            // Refrescar Select2 para que renderice los cambios si es necesario
            // (Select2 detecta cambios en el DOM automáticamente, pero esto asegura)
            elementoSelect.trigger('change.select2');
        },
        error: function () {
            elementoSelect.html('<option value="00">❌ Error de conexión</option>');
        }
    });
}

    // FUNCIÓN PARA PINTAR ASIGNATURAS (CANDADOS Y CHECKS)
    function cargarAsignaturasVisual(ann, modalidad, gst, elementoSelect) {
        elementoSelect.html('<option value="00">Buscando asignaturas...</option>');

        $.ajax({
            url: "includes/cargar-asignatura.php",
            type: "POST",
            dataType: "json",
            data: { 
                annlectivo: ann, 
                modalidad: modalidad, 
                codigo_gst: gst 
            },
            success: function (data) {
                elementoSelect.empty();
                
                if (data.length > 0) {
                    elementoSelect.append('<option value="00">Seleccione una Asignatura...</option>');
                    
                    $.each(data, function (i, item) {
                        let textoOpcion = "";
                        
                        if (item.ocupado) {
                            // Materia Ocupada: Rojo + Candado
                            textoOpcion = `🔒 ${item.descripcion} (Prof: ${item.encargado})`;
                        } else {
                            // Materia Libre: Verde + Check
                            textoOpcion = `✅ ${item.descripcion}`;
                        }

                        // Agregamos la opción
                        elementoSelect.append(`<option value="${item.codigo}">${textoOpcion}</option>`);
                    });

                } else {
                    elementoSelect.append('<option value="00">⚠️ No hay asignaturas registradas</option>');
                }

                // Refrescar Select2
                elementoSelect.trigger('change.select2');
            },
            error: function () {
                elementoSelect.html('<option value="00">❌ Error al cargar</option>');
            }
        });
    }