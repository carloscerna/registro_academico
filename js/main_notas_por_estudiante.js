// Variables Globales.
var accion_buscar = "";

$(function(){
// funcionalidad del botón Actualizar
// Funcionalidad para Imprimir por Asignatura.
$('#goNotasImprimir').on('click',function(){
    var chkfirma = "no"; var chksello = "no"; var chkfoto = "no"; var chkCrearArchivoPdf = "no"; var codigo_modalidad = ""; var todos = "";
    var print_uno = 'yes'; var codigo_alumno = ""; var codigo_matricula = "";
    codigo_modalidad = $("#codigo_bachillerato").val();
    todos = $("#todos").val();
    codigo_alumno = $("#codigo_alumno").val();
    codigo_matricula = $("#codigo_matricula").val();
    if($('#chktraslado').is(":checked")) {chktraslado = "yes";}
    if($('#chkfirmas').is(":checked")) {chkfirma = "yes";}
    if($('#chksellos').is(":checked")) {chksello = "yes";}
    if($('#chkfoto').is(":checked")) {chkfoto = "yes";}
    if($('#chkCrearArchivoPdf').is(":checked")) {chkCrearArchivoPdf = "si";}
    // EDUCACIÓN BASICA - BOLETA DE NOTAS
    if(codigo_modalidad >= '03' && codigo_modalidad <='12'){
        if(chkCrearArchivoPdf == "si")
        {
                $.ajax({
                        beforeSend: function(){
                                $('#myModal').modal('show');
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url:"php_libs/reportes/boleta_de_notas.php",
                        data: "todos="+ todos + "&id=" + Math.random()+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+codigo_matricula+"&txtidalumno="+codigo_alumno+"&chkfoto="+chkfoto+"&chkCrearArchivoPdf="+chkCrearArchivoPdf+"&print_uno="+print_uno,
                        success: function(response){
                            // Validar mensaje de error
                            if(response.respuesta === false){
                                toastr["error"](response.mensaje, "Sistema");
                            }
                            else{
                                toastr["info"](response.mensaje, "Sistema");}
                        },
                        error:function(){
                            toastr["error"](response.mensaje, "Sistema");;
                        }
                    });
        }else if(chkCrearArchivoPdf == "no"){
                // construir la variable con el url.
                        varenviar = "/registro_academico/php_libs/reportes/boleta_de_notas.php?todos="+todos+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+codigo_matricula+"&txtidalumno="+codigo_alumno+"&chkfoto="+chkfoto+"&chkCrearArchivoPdf="+chkCrearArchivoPdf+"&print_uno="+print_uno;
                // Ejecutar la función
                        AbrirVentana(varenviar);
        }
}
});
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
// funcionalidad del botón Actualizar
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
$('#goNotasActualizar').on('click',function(){
// variables.
    accion_buscar = 'ActualizarCalificaciones';
    var periodo = $('#LstPeriodo').val();
    var codigo_modalidad = $("#codigo_bachillerato").val();
    var $objCuerpoTabla=$("#tablaNotas").children().prev().parent();
    var nota_ = []; var id_notas_ = []; var fila = 0;
// recorre el contenido de la tabla.
$objCuerpoTabla.find("tbody tr").each(function(){
    var id_notas =$(this).find('td').eq(0).find("input[name='id_notas']").val();
    var nota =$(this).find('td').eq(3).find("input[name='nota']").val();
    // dar valor a las arrays.
        nota_[fila]=nota;
        id_notas_[fila]=id_notas;
        fila = fila + 1;
}); // FIN DE RECORRIDO DE LA TABLA.
// INICIA EJECUCIÓN Y ENVIO DE DATOS POR AJAX.
$.ajax({
    beforeSend: function(){
},
    cache: false,
    type: "POST",
    dataType: "json",
    url:"php_libs/soporte/phpAjaxCalificacionPorEstudiante.php",
    data: {
            accion: accion_buscar, fila: fila, periodo: periodo, id_notas_: id_notas_, nota_:nota_, codigo_modalidad: codigo_modalidad
            },
    success: function(response) {
            if (response.respuesta === true) {
                // Mensaje del Sistema.
                toastr["success"](response.mensaje, "Sistema");
            }
    }
});                
});
////////////////////////////////////////////////////////////////////////////////////////////////
// BUSQUEDA DE CALIFICACIONES DEPENDIENDO DEL PERIODO.
////////////////////////////////////////////////////////////////////////////////////////////////        
// Parametros para el grado y sección, al seleccionar el bachillerato.
    $("#LstPeriodo").change(function () {	
        // validar si está vacio.
        if($("#LstPeriodo").val() == ""){
            $('#listaCalificacionPorEstudianteOK').empty();
            return false;
        }
        accion_buscar = 'BuscarCalificacion';
        // Crear variables
        codigo_nie = $("#codigo_nie").val();
        codigo_alumno = $("#codigo_alumno").val();
        codigo_matricula = $("#codigo_matricula").val();
        codigo_grado = $("#codigo_grado").val();
        codigo_annlectivo = $("#lstannlectivo").val();
        codigo_periodo = $("#LstPeriodo").val();
        str = "codigo_nie="+codigo_nie+"&codigo_alumno="+codigo_alumno+"&codigo_matricula="+codigo_matricula+"&codigo_grado="+codigo_grado+"&codigo_annlectivo="+codigo_annlectivo+"&codigo_periodo="+codigo_periodo+"&accion_buscar="+accion_buscar;
        // Ejecutar Ajax
        $.ajax({
            beforeSend: function(){
                $('#listaCalificacionPorEstudianteOK').empty();
            },
            cache: false,
            type: "POST",
            dataType: "json",
            url:"php_libs/soporte/phpAjaxCalificacionPorEstudiante.php",
            data: str + "&id=" + Math.random(),
            success: function(response){
                // Validar respuesta
                if(response.respuesta === false){
                    toastr["error"](response.mensaje, "Sistema");
                    //$('#listaCalificacionPorEstudianteOK').empty();
                }
                if(response.respuesta === true){
                // Mostrar resultado cuando se ha encontra registros.
                    toastr["info"](response.mensaje, "Sistema");
                        //$('#listaCalificacionPorEstudianteOK').empty();
                        $('#listaCalificacionPorEstudianteOK').append(response.contenido);
                        // activar botón guardar.
                        $("#goNotasActualizar").attr("disabled",false);
                        // activar botón imprimir.
                        $("#goNotasImprimir").attr("disabled",false);
                    }
            },
                error:function(){
                    toastr["error"](response.mensaje, "Sistema");
            }
    });
});
////////////////////////////////////////////////////////////////////////////////////////////////
// BUSQUEDA DE REGISRO PARA ACTUALIZAR LAS NOTAS.
////////////////////////////////////////////////////////////////////////////////////////////////        
$('#formCalificacionPorEstudiante').validate({
rules:
    {
        codigo_nie: {required: true},
    },
messages:
    {
        codigo_nie: "Ingrese un Número de NIE.",
    },	
        errorElement: "em",
        errorPlacement: function ( error, element ) {
        // Add the `invalid-feedback` class to the error element
        error.addClass( "invalid-feedback" );
        if ( element.prop( "type" ) === "checkbox" ) {
            error.insertAfter( element.next( "label" ) );
        } else {
            error.insertAfter( element );
        }
        },
            highlight: function ( element, errorClass, validClass ) {
            $( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
        },
            unhighlight: function (element, errorClass, validClass) {
            $( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
        },
submitHandler: function(){
    var str = $('#formCalificacionPorEstudiante').serialize();
    $.ajax({
        beforeSend: function(){
            $('#tabstabla').show();
        },
        cache: false,
        type: "POST",
        dataType: "json",
        url:"php_libs/soporte/phpAjaxCalificacionPorEstudiante.php",
        data:str + "&id=" + Math.random(),
        success: function(response){
            // Validar respuesta
            if(response.respuesta === false){
                toastr["error"](response.mensaje, "Sistema");
                $('#listaCalificacionPorEstudianteOK').empty();
            }
            if(response.respuesta === true){
            // Mostrar resultado cuando se ha encontra registros.
                toastr["info"](response.mensaje, "Sistema");
                    $('#listaCalificacionPorEstudianteOK').empty();
                    $('#listaCalificacionPorEstudianteOK').append(response.contenido);
                    // etiqueta CARD TITULO TABLA.
                    $("label[for='titulo_tabla']").text(response.titulo_tabla);
                    $("#todos").val(response.todos);
                    $("#codigo_bachillerato").val(response.codigo_modalidad);
                    $("#codigo_matricula").val(response.codigo_matricula);
                    $("#codigo_alumno").val(response.codigo_alumno);
                    $("#codigo_grado").val(response.codigo_grado);
                    // activar botón guardar.
                    $("#goNotasActualizar").attr("disabled",true);
                    // activar botón imprimir.
                    $("#goNotasImprimir").attr("disabled",true);
                    // Rellenar Lstperiodo
                    var miLstPeriodo=$("#LstPeriodo");
                        miLstPeriodo.empty();
                        // Condiciones para Parvularia y Educación Básica (I, II Y III).
                        if(response.codigo_modalidad >= '01' && response.codigo_modalidad <= '05' || response.codigo_modalidad == '12'){
                            miLstPeriodo.append('<option value="" selected>Seleccionar...</option>');
                            miLstPeriodo.append('<option value="Periodo 1">Trimestre 1</option>');
                            miLstPeriodo.append('<option value="Periodo 2">Trimestre 2</option>');
                            miLstPeriodo.append('<option value="Periodo 3">Trimestre 3</option>');
                            miLstPeriodo.append('<option value="R1">Recuperación 1</option>');
                            miLstPeriodo.append('<option value="R2">Recuperación 2</option>');
                        }
                        // Condiciones para Educación Media.
                        if(response.codigo_modalidad >= '06' && response.codigo_modalidad <= '09'){
                            miLstPeriodo.append('<option value="" selected>Seleccionar...</option>');
                            miLstPeriodo.append('<option value="Periodo 1">Período 1</option>');
                            miLstPeriodo.append('<option value="Periodo 2">Período 2</option>');
                            miLstPeriodo.append('<option value="Periodo 3">Período 3</option>');
                            miLstPeriodo.append('<option value="Periodo 4">Período 4</option>');
                            miLstPeriodo.append('<option value="R1">Recuperación 1</option>');
                            miLstPeriodo.append('<option value="R2">Recuperación 2</option>');
                        }
                        // Condiciones para Educación Básica de Adultos (Nocturna).
                        if(response.codigo_modalidad >= '10' && response.codigo_modalidad <= '11'){
                            miLstPeriodo.append('<option value="" selected>Seleccionar...</option>');
                            miLstPeriodo.append('<option value="Periodo 1">Período 1</option>');
                            miLstPeriodo.append('<option value="Periodo 2">Período 2</option>');
                            miLstPeriodo.append('<option value="Periodo 3">Período 3</option>');
                            miLstPeriodo.append('<option value="Periodo 4">Período 4</option>');
                            miLstPeriodo.append('<option value="Periodo 5">Período 5</option>');
                            miLstPeriodo.append('<option value="R1">Recuperación 1</option>');
                            miLstPeriodo.append('<option value="R2">Recuperación 2</option>');
                        }
                }
        },
            error:function(){
                toastr["error"](response.mensaje, "Sistema");
        }
});
    return false;
    },
});
});
////////////////////////////////////////////////////////////////////////////////////////////////        
////////////////////////////////////////////////////////////////////////////////////////////////        
function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}
// Pasar foco cuando seleccionar un encargado.
function PasarFoco()
   {
       $('#codigo_nie').focus();
   }
// Mensaje de Carga de Ajax.
function configureLoadingScreen(screen){
    $(document)
        .ajaxStart(function () {
            screen.fadeIn();
        })
        .ajaxStop(function () {
            screen.fadeOut();
        });
}