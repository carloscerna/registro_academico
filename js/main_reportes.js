// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
   
$(function(){       
// Validar Formulario para la buscque de registro segun el criterio.   
$('#formEmpleados').validate({
rules:{
        lstannlectivo: { required: true },
        lstmodalidad: { required: true }
        },
messages: {
        lstannlectivo: "Seleccione un año lectivo.",
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
        // Serializar los datos, toma todos los Id del formulario con su respectivo valor.
                var str = $('#formEmpleados').serialize();
                                        // AJAX
                $.ajax({
                        beforeSend: function(){
                        $('#tabstabla').show();
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url:"php_libs/soporte/phpAjaxReportes.inc.php",
                        data:str + "&id=" + Math.random(),
                        success: function(response){
                        // Validar mensaje de error
                        if(response.respuesta == false){
                                toastr["error"](response.mensaje, "Sistema");
                        }
                        else{
                        // si es exitosa la operación
                                $('#listaUsuariosOK').empty();
                                $('#listaUsuariosOK').append(response.contenido);
                        //	LblPortafolio.
                                $("label[for='LblSeleccione']").text(response.mensaje);
                                toastr["info"](response.mensaje, "Sistema");
                        }
                        },
                        error:function(){
                        toastr["error"]('ERROR GENERAL DEL SISTEMA, INTENTE MAS TARDE', "Sistema");
                        }
                });
                        return false;
                }
});
/* ***************************************************************************** */
// Extracciòn del valor que va utilizar para Eliminar y Edición de Registros
$('body').on('click','#listaUsuariosOK a',function (e){
        e.preventDefault();
// valor de la variable proveniente del resultado del query.
        reporte_ok = $(this).attr('href');
        accion_ok = $(this).attr('data-accion');
// Ajax hide. y controlar que informe se va a presentar.
        var ann_lectivo = $('#lstannlectivo').val();
        var varbach = $('#lstmodalidad').val();
        var LstNombreModalidad = $('#lstmodalidad option:selected').text();
        var lstlist_nominas = $("#lstlist option:selected").val();

        var lstlist_fechames = $("#lstFechaMes option:selected").val();
        var lstlist_notas = $("#lstnotas option:selected").val();
        var lstlist_notas_paes = $("#lstpaes option:selected").val();
        var lstasignatura = $('#lstasignatura option:selected').val();
// valores de los combo.
        var lsttrimestre = $('#lsttrimestres option:selected').val();
        var txtcodigomatricula = 0;
        var id_alumno = 0;
        var print_uno = 'no';
 //var url_fotos = $("#url_foto").val();
        var lstcarnet = $("#lstcarnet option:selected").val();
        var lstpre = $("#lstpre option:selected").val();
        var lstprinter = $("#lstcertificado_printer option:selected").val();
        var rubro = $("#lstRubro option:selected").text();
        var rubroValor = $("#lstRubro option:selected").val();
        var fechapaquete = $("#FechaPaquete").val();	                                
//variables checked para la boleta de notas individual y todas.
        var chktraslado = "no"; var chkfirma = "no"; var chksello = "no"; var chkfoto = "no"; var chkCrearArchivoPdf = "no";
        var chkfechaPaquete = "no"; var chkNIEPaquete = "no";
        if($('#chktraslado').is(":checked")) {chktraslado = "yes";}
        if($('#chkfirmas').is(":checked")) {chkfirma = "yes";}
        if($('#chksellos').is(":checked")) {chksello = "yes";}
        if($('#chkfoto').is(":checked")) {chkfoto = "yes";}
        if($('#chkCrearArchivoPdf').is(":checked")) {chkCrearArchivoPdf = "si";}
// Datos para los Carnet.
        if($('#chkfirma').is(":checked")) {chkfirma = "yes";}
        if($('#chksello').is(":checked")) {chksello = "yes";}
// Datos para el Informe que Paquete Escolar
        if($('#chkfechaPaquete').is(":checked")) {chkfechaPaquete = "yes";}
        if($('#chkNIEPaquete').is(":checked")) {chkNIEPaquete = "yes";}
// bloque para los diferentes informes.
////////////////////////////////////////////////////
if (lstlist_nominas == 'orden' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}

if (lstlist_nominas == 'libro_registro' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_libro_registro.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}

if (lstlist_nominas == 'control_actividades' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/control_de_actividades.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}

if (lstlist_nominas == 'asistencia' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_asistencia.php?todos="+reporte_ok+"&lstannlectivo="+ann_lectivo+"&FechaMes="+lstlist_fechames;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'asistencia-cuadros' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_asistencia_.php?todos="+reporte_ok+"&lstannlectivo="+ann_lectivo;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'genero' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_genero.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'para_firmas' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_pn.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'nomina_estadistica' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_para_estadistica.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'para_sae_nie' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_matricula_sae_nie.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'para_siges' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nomina_matricula_siges.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'para_firmas_retiro_documentos' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/informe_retiro_documentos.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'boleta_de_datos' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/boleta_de_captura_de_datos.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}                        
if (lstlist_nominas == 'datos_matricula' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/datos_matricula.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}                        
if (lstlist_nominas == 'informe_hogar' && $(this).attr('data-accion') == 'listados_01') {
        // VARIABLE PARA LA URL(INFORME)
        $url_ruta = "php_libs/reportes/informe_estudiante_hogar.php";

        if(chkCrearArchivoPdf == "si")
        {
                $.ajax({
                        beforeSend: function(){
                                //  $('#myModal').modal('show');
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url: $url_ruta,
                        data: "todos="+ reporte_ok + "&id=" + Math.random()+"&chkCrearArchivoPdf="+chkCrearArchivoPdf,
                        success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta === false){
                                        toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                        toastr["info"](response.mensaje, "Sistema");}
                        },
                        error:function(){
                                error_();
                        }
                        });
        }else if(chkCrearArchivoPdf == "no"){
                // construir la variable con el url.
                        varenviar = "/registro_academico/php_libs/reportes/informe_estudiante_hogar.php?todos="+reporte_ok+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
                // Ejecutar la función
                        AbrirVentana(varenviar);
        }
}                        
if (lstlist_nominas == 'informe_hogar_no_encuesta' && $(this).attr('data-accion') == 'listados_01') {
        // VARIABLE PARA LA URL(INFORME)
        $url_ruta = "php_libs/reportes/informe_estudiante_hogar_no_encuesta.php";

        if(chkCrearArchivoPdf == "si")
        {
                $.ajax({
                        beforeSend: function(){
                                //  $('#myModal').modal('show');
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url: $url_ruta,
                        data: "todos="+ reporte_ok + "&id=" + Math.random()+"&chkCrearArchivoPdf="+chkCrearArchivoPdf,
                        success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta === false){
                                        toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                        toastr["info"](response.mensaje, "Sistema");}
                        },
                        error:function(){
                                error_();
                        }
                        });
        }else if(chkCrearArchivoPdf == "no"){
                // construir la variable con el url.
                        varenviar = "/registro_academico/php_libs/reportes/informe_estudiante_hogar_no_encuesta.php?todos="+reporte_ok+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
                // Ejecutar la función
                        AbrirVentana(varenviar);
        }
}
if (lstlist_nominas == 'informe_hogar_individual' && $(this).attr('data-accion') == 'listados_01') {
        // VARIABLE PARA LA URL(INFORME)
        $url_ruta = "php_libs/reportes/informe_estudiante_hogar_individual.php";

        if(chkCrearArchivoPdf == "si")
        {
                $.ajax({
                        beforeSend: function(){
                                //  $('#myModal').modal('show');
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url: $url_ruta,
                        data: "todos="+ reporte_ok + "&id=" + Math.random()+"&chkCrearArchivoPdf="+chkCrearArchivoPdf,
                        success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta === false){
                                        toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                        toastr["info"](response.mensaje, "Sistema");}
                        },
                        error:function(){
                                error_();
                        }
                        });
        }else if(chkCrearArchivoPdf == "no"){
                // construir la variable con el url.
                        varenviar = "/registro_academico/php_libs/reportes/informe_estudiante_hogar_individual.php?todos="+reporte_ok+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
                // Ejecutar la función
                        AbrirVentana(varenviar);
        }
}              
if (lstlist_nominas == 'cuadro_notas' && $(this).attr('data-accion') == 'listados_01') {
        // Validar la Modalidad.
        if(varbach >= '03' && varbach <= '14')
        {
                if(varbach >= '03' && varbach <= '05'){
                // construir la variable con el url.
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_notas.php?todos="+reporte_ok;
                }
                if(varbach == '06' || varbach == '07' || varbach == '08' || varbach == '09'){
                // construir la variable con el url.
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_notas_media.php?todos="+reporte_ok;
                }else{
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_notas.php?todos="+reporte_ok;
                }
        }
        // Ejecutar la función
        AbrirVentana(varenviar);
}                        
if (lstlist_nominas == 'telefono_alumno' && $(this).attr('data-accion') == 'listados_01') {
        // VARIABLE PARA LA URL(INFORME)
        $url_ruta = "php_libs/reportes/telefono_alumno.php";
        // Ajax.
        if(chkCrearArchivoPdf == "si")
                {
                        $.ajax({
                                cache: false,
                                type: "POST",
                                dataType: "json",
                                url: $url_ruta,
                                data: "todos="+ reporte_ok + "&id=" + Math.random()+"&chkCrearArchivoPdf="+chkCrearArchivoPdf,
                                success: function(response){
                                        // Validar mensaje de error
                                        if(response.respuesta === false){
                                        toastr["error"](response.mensaje, "Sistema");
                                        }
                                        else{
                                        toastr["info"](response.mensaje, "Sistema");
                                        }
                                },
                                error:function(){
                                        toastr["error"](response.mensaje, "Sistema");
                                }
                                });
                }else if(chkCrearArchivoPdf == "no"){
                        // construir la variable con el url.
                                varenviar = "/registro_academico/php_libs/reportes/telefono_alumno.php?todos="+reporte_ok+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
                        // Ejecutar la función
                                AbrirVentana(varenviar);
                }
}                        
if (lstlist_nominas == 'paquete_escolar_00' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/paquete_familias.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'paquete_escolar_01' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/paquete_escolar_2.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_nominas == 'paquete_escolar_02' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        if(rubroValor == '05'){
                varenviar = "/registro_academico/php_libs/reportes/paquete_familias.php?todos="+reporte_ok+"&fechapaquete="+fechapaquete+"&rubro="+rubro+"&chkfechaPaquete="+chkfechaPaquete+"&chkNIEPaquete="+chkNIEPaquete;
        }else{
                varenviar = "/registro_academico/php_libs/reportes/paquete_escolar_3.php?todos="+reporte_ok+"&fechapaquete="+fechapaquete+"&rubro="+rubro+"&chkfechaPaquete="+chkfechaPaquete+"&chkNIEPaquete="+chkNIEPaquete;
        }                                
        // Ejecutar la función
        AbrirVentana(varenviar);
}                        
if (lstlist_nominas == 'ficha_alumno_listado' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/ficha_alumno_listado.php?todos="+reporte_ok;
        // Ejecutar la función
        AbrirVentana(varenviar);
}                        
if (lstlist_nominas == 'pre-matricula' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/informe_prematricula.php?todos="+reporte_ok+"&aprobado_reprobado="+lstpre;
        // Ejecutar la función
        AbrirVentana(varenviar);
}                        
if (lstlist_nominas == 'carnet-estudiantil' && $(this).attr('data-accion') == 'listados_01') {
        // construir la variable con el url.
        if(lstcarnet == "carnet_frente"){
                varenviar = "/registro_academico/php_libs/reportes/informe_carnet_uno.php?todos="+reporte_ok+"&chksello="+chksello+"&chkfirma="+chkfirma;
                //varenviar = "/registro_academico/php_libs/reportes/informe_carnet.php?todos="+reporte_ok+"&chksello="+chksello+"&chkfirma="+chkfirma+"&path_foto="+url_fotos;
        }
        if(lstcarnet == "carnet_vuelto"){
                varenviar = "/registro_academico/php_libs/reportes/informe_carnet_vuelto.php?todos="+reporte_ok+"&chksello="+chksello+"&chkfirma="+chkfirma;
        }
        // Ejecutar la función
        AbrirVentana(varenviar);
}   
if (lstlist_nominas == 'hoja-de-calculo' && $(this).attr('data-accion') == 'listados_01') {
        $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                //url:"php_libs/soporte/CrearNominas.php",
                url:"php_libs/soporte/CrearNominas.php",
                data: "todos="+ reporte_ok + "&id=" + Math.random(),
                success: function(response){
                        // Validar mensaje de error
                        if(response.respuesta === false){
                                toastr["error"](response.mensaje, "Sistema");
                        }
                        else{
                                toastr["info"](response.contenido, "Sistema");}
                },
                error:function(){
                        error_();
                }
                });
        
        // construir la variable con el url.
        //varenviar = "/registro_academico/php_libs/soporte/CrearNominas.php?todos="+reporte_ok;
        // Ejecutar la función
        //AbrirVentana(varenviar);
}
if (lstlist_nominas == 'cuadro-de-promocion' && $(this).attr('data-accion') == 'listados_01') {
        // crear variable para el nivel o modalidad.
        $url_ = "php_libs/soporte/CrearCuadrodePromocion.php";                
                switch(LstNombreModalidad)
                {
                        case "Educación Básica - Estándar de Desarrollo":
                                $url_ = "php_libs/soporte/CrearCuadroRegistroEvaluacionEstandarBasicaParvularia.php";
                        break;
                        case "Educación Parvularia - Estándar de Desarrollo":
                                $url_ = "php_libs/soporte/CrearCuadroRegistroEvaluacionEstandarBasicaParvularia.php";
                        break;
                        case "Educación Básica - Segundo y Tercer Grado Focalizado":
                                $url_ = "php_libs/soporte/CrearCuadroRegistroEvaluacionEstandarBasicaParvularia.php";
                        break;
                        default:
                                $url_ = "php_libs/soporte/CrearCuadrodePromocion.php";                
                        break;
                }
        $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                url: $url_,
                data: "todos="+ reporte_ok + "&id=" + Math.random(),
                success: function(response){
                        // Validar mensaje de error
                        if(response.respuesta === false){
                                toastr.error(response.mensaje, "Sistema de Registro Académico");
                        }
                        else{
                                toastr.options.showEasing = 'swing';
                                toastr.options.hideEasing = 'linear';
                                toastr.info(response.contenido, "Sistema de Registro Académico");}
                },
                error:function(){
                        error_();
                }
                });
}
if (lstlist_nominas == 'hoja-de-calculo-caracterizacion' && $(this).attr('data-accion') == 'listados_01') {
        $.ajax({
                cache: false,
                type: "POST",
                dataType: "json",
                url:"php_libs/soporte/CrearNominaCaracterizacion.php",
                data: "todos="+ reporte_ok + "&id=" + Math.random(),
                success: function(response){
                        // Validar mensaje de error
                        if(response.respuesta === false){
                                toastr["error"](response.mensaje, "Sistema");
                        }
                        else{
                                toastr["info"](response.contenido, "Sistema");}
                },
                error:function(){
                        error_();
                }
                });
        
        // construir la variable con el url.
        //varenviar = "/registro_academico/php_libs/soporte/CrearNominas.php?todos="+reporte_ok;
        // Ejecutar la función
        //AbrirVentana(varenviar);
}                                   
if (lstlist_nominas == 'cuadro-de-notas-hoja-de-calculo' && $(this).attr('data-accion') == 'listados_01') {

                // EDUCACIÓN BASICA - BOLETA DE CALIFICACIÓN EDUCACIÓN BÁSICA Y MEDIA.
                if(varbach >= '03' && varbach <='12'){
                        $.ajax({
                                cache: false,
                                type: "POST",
                                dataType: "json",
                                url:"php_libs/soporte/CrearCuadroDeNotasHojaDeCalculo.php",
                                data: "todos="+ reporte_ok + "&id=" + Math.random(),
                                success: function(response){
                                        // Validar mensaje de error
                                        if(response.respuesta === false){
                                                toastr["error"](response.mensaje, "Sistema");
                                        }
                                        else{
                                                toastr["info"](response.mensaje, "Sistema");}
                                },
                                error:function(){
                                        error_();
                                }
                                });      
                }else{
                        $.ajax({
                                cache: false,
                                type: "POST",
                                dataType: "json",
                                url:"php_libs/soporte/CrearIndicadores.php",
                                data: "todos="+ reporte_ok + "&id=" + Math.random(),
                                success: function(response){
                                        // Validar mensaje de error
                                        if(response.respuesta === false){
                                                toastr["error"](response.mensaje, "Sistema");
                                        }
                                        else{
                                                toastr["info"](response.mensaje, "Sistema");}
                                },
                                error:function(){
                                        error_();
                                }
                                });                        
                }

}
// bloque para los diferentes informes de notas.
////////////////////////////////////////////////////
if (lstlist_notas == 'boleta_notas' && $(this).attr('data-accion') == 'listados_02') {
        
        parvularia_seccion = reporte_ok.substring(2,2);
        // Verificar si el Parvularia... primeros grados y segundo y tercero focalizado.
        if(varbach >= '13' && varbach <= '14' || varbach == '17' || varbach == '16' || varbach == '18'){
                switch (varbach) {
                        case '16':
                                $url_ruta = "php_libs/reportes/Boletas Calificaciones/Segundo y Tercer grado Focalizado.php";
                                break;
                
                        default:
                                $url_ruta = "php_libs/reportes/Boletas Calificaciones/Parvularia y Primeros grados.php";
                }
                
        }
        // EDUCACIÓN BASICA - BOLETA DE CALIFICACIÓN EDUCACIÓN BÁSICA Y MEDIA.
        if(varbach >= '03' && varbach <='12' || varbach == '15'){
                // VARIABLE PARA LA URL(INFORME)
                $url_ruta = "php_libs/reportes/boleta_de_notas.php";
        }
                if(chkCrearArchivoPdf == "si")
                {
                $.ajax({
                        beforeSend: function(){
                                $('#myModal').modal('show');
                        },
                        cache: false,
                        type: "POST",
                        dataType: "json",
                        url: $url_ruta,
                        data: "todos="+ reporte_ok + "&id=" + Math.random()+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno+"&chkfoto="+chkfoto+"&chkCrearArchivoPdf="+chkCrearArchivoPdf+"&print_uno="+print_uno,
                        success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta === false){
                                toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                toastr["info"](response.mensaje, "Sistema");
                                }
                        },
                        error:function(){
                                toastr["error"](response.mensaje, "Sistema");
                        }
                        });
                }else if(chkCrearArchivoPdf == "no"){
                        // construir la variable con el url.
                                varenviar = "/registro_academico/"+$url_ruta+"?todos="+reporte_ok+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno+"&chkfoto="+chkfoto+"&chkCrearArchivoPdf="+chkCrearArchivoPdf+"&print_uno="+print_uno;
                        // Ejecutar la función
                                AbrirVentana(varenviar);
                }

}

if (lstlist_notas == 'por_trimestre' && $(this).attr('data-accion') == 'listados_02') {
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/notas_por_trimestre.php?todos="+reporte_ok+"&lsttri="+lsttrimestre;
        // Ejecutar la función
        AbrirVentana(varenviar);
}

if (lstlist_notas == 'todos_trimestre' && $(this).attr('data-accion') == 'listados_02') {
                if(varbach >= '03' && varbach <= '05'){
                varenviar = "/registro_academico/php_libs/reportes/notas_por_trimestre_all_asignaturas.php?todos="+reporte_ok;
                }

        if(varbach >= '06' && varbach <= '09')
        {
        varenviar = "/registro_academico/php_libs/reportes/notas_por_trimestre_all_asignaturas_media.php?todos="+reporte_ok;
                if(varbach >= '07'){
                varenviar = "/registro_academico/php_libs/reportes/notas_por_trimestre_all_asignaturas_media_tvc.php?todos="+reporte_ok;
                }
                if(varbach >= '09'){
                varenviar = "/registro_academico/php_libs/reportes/notas_por_trimestre_all_asignaturas_media_tvc_tercero.php?todos="+reporte_ok;
                }
        }
        // Ejecutar la función
        AbrirVentana(varenviar);
}
if (lstlist_notas == 'aprobados_reprobados' && $(this).attr('data-accion') == 'listados_02') {
                if(varbach >= '03' && varbach <= '05'){
                varenviar = "/registro_academico/php_libs/reportes/alumnos_asignaturas_aprobadas_reprobadas.php?todos="+reporte_ok+"&lsttri="+lsttrimestre;
                }

        if(varbach >= '06' && varbach <= '09')
        {
        varenviar = "/registro_academico/php_libs/reportes/alumnos_asignaturas_aprobadas_reprobadas_general.php?todos="+reporte_ok+"&lsttri="+lsttrimestre;
                if(varbach >= '07'){
                varenviar = "/registro_academico/php_libs/reportes/alumnos_asignaturas_aprobadas_reprobadas_tvc.php?todos="+reporte_ok+"&lsttri="+lsttrimestre;
                }
                if(varbach >= '09'){
                varenviar = "/registro_academico/php_libs/reportes/alumnos_asignaturas_aprobadas_reprobadas_tvc_tercero.php?todos="+reporte_ok+"&lsttri="+lsttrimestre;
                }
        }
        // Ejecutar la función
        AbrirVentana(varenviar);
}

        if (lstlist_notas == 'nota_paes' && $(this).attr('data-accion') == 'listados_02') {
        
        if(lstlist_notas_paes == "nota_paes_listado"){
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nota_paes_listado.php?todos="+reporte_ok+"&chksello="+chksello+"&chkfirma="+chkfirma;
        }
        
        if(lstlist_notas_paes == "nota_paes_constancias"){
        // construir la variable con el url.
        varenviar = "/registro_academico/php_libs/reportes/nota_paes_constancia.php?todos="+reporte_ok+"&chksello="+chksello+"&chkfirma="+chkfirma;
        }
        // Ejecutar la función
        AbrirVentana(varenviar);
}

        if (lstlist_notas == 'por_asignatura' && $(this).attr('data-accion') == 'listados_02') {
                if(varbach >= '03' && varbach <= '05'){
                varenviar = "/registro_academico/php_libs/reportes/notas_trimestre_por_asignatura_basica.php?todos="+reporte_ok+"&lstasignatura="+lstasignatura;
                }

        if(varbach >= '06' && varbach <= '09' || varbach == '15' || varbach == '10')
        {
                varenviar = "/registro_academico/php_libs/reportes/notas_trimestre_por_asignatura_media.php?todos="+reporte_ok+"&lstasignatura="+lstasignatura;
        }
        // Ejecutar la función
        AbrirVentana(varenviar);
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////                        
////////////////PROCESO PARA CUADRO DE PROMOCION Y CERTIFICADOS//////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (lstlist_notas == 'cuadro_promocion' && $(this).attr('data-accion') == 'listados_02') {
        console.log(ann_lectivo);
        if(ann_lectivo >= "18" && ann_lectivo <= "20"){
                if(varbach >= '03' && varbach <= '05'){
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_de_promocion_2018.php?todos="+reporte_ok;
                }else if(varbach == '06'){
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_de_promocion_general.php?todos="+reporte_ok;
                }else if(varbach == '07'){
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_de_promocion_tecnico.php?todos="+reporte_ok;
                }else if(varbach == '09'){
                        varenviar = "/registro_academico/php_libs/reportes/cuadro_de_promocion_tecnico_tercero.php?todos="+reporte_ok;
                }                
        }else if(ann_lectivo >= "21"){  // LOS CUADROS DE REGISTRO DE EVALUACION. 
                if(varbach >= '03' && varbach <= '05'){ // EDUCACIÓN BÁSICA.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/Basica II y III Ciclo.php?todos="+reporte_ok;
                }else if(varbach == '06'){ // EDUCACIÓN MEDIA - BACHILLERATO GENERAL.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/General I y II año.php?todos="+reporte_ok;
                }else if(varbach == '07'){ // EDUCACIÓN MEDIA - BACHILLERATO TECNICO.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/Tecnico II año.php?todos="+reporte_ok;
                }else if(varbach == '09'){ // EDUCACIÓN MEDIA - BACHILLERATO TECNICO VOCACIONAL COMERCIAL.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/Tecnico III año.php?todos="+reporte_ok;
                }else if(varbach == '10'){ // EDUCACIÓN BASICA - III CICLO.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/Basica II y III Ciclo Nocturna.php?todos="+reporte_ok;
                }else if(varbach == '11'){ // EDUCACIÓN MEDIA - BACHILLERATO GENERAL NOCTURNA.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/General I y II año Nocturna.php?todos="+reporte_ok;
                }else if(varbach == '15'){ // EDUCACIÓN MEDIA - BACHILLERATO TECNICO VOCACIONAL AUXILIAR CONTABLE.
                        varenviar = "/registro_academico/php_libs/reportes/Cuadros de Registro/Tecnico I y II año Modular.php?todos="+reporte_ok;
                }   
        }else{
                varenviar = "/registro_academico/php_libs/reportes/cuadro_de_promocion_2015.php?todos="+reporte_ok;
        }
        // Ejecutar la función
                AbrirVentana(varenviar);
}

if (lstlist_notas == 'certificados' && $(this).attr('data-accion') == 'listados_02') {
        if(varbach >= '01' && varbach <= '02'){
                // construir la variable con el url.
                varenviar = "/registro_academico/php_libs/reportes/certificados_parvularia.php?todos="+reporte_ok+"&printer="+lstprinter;
        }
        if(varbach >= '03' && varbach <= '05'){
                // construir la variable con el url.
                varenviar = "/registro_academico/php_libs/reportes/certificados_2018.php?todos="+reporte_ok+"&printer="+lstprinter;
        }
                // Ejecutar la función
                AbrirVentana(varenviar);
}
});
});

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}

function ok_(){
        toastr.success("Registros Encontrados."); 
	return false;
}
			
function error_(){
        toastr.error("Revisar la información."); 
	return false; 
}