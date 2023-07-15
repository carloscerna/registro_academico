// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";

// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // BLOQUE PARA ADMINISTRAR LAS ASIGNATURAS.
                ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 // Validar Formulario para la buscque de registro segun el criterio.   
 $('#formA1').validate({
    submitHandler: function(){
                // Serializar los datos, toma todos los Id del formulario con su respectivo valor.
        var str = $('#formA1').serialize();

        $.ajax({
            cache: false,
            type: "POST",
            dataType: "json",
            url: "php_libs/soporte/phpAjaxMantenimiento_1.inc.php",
            data: str + "&accion=" + accion + "&id=" + Math.random(),
            success: function(response){
                // Validar mensaje de error
                if(response.respuesta === false){
                                                        if (response.mensaje == "Si Existe") {
                                                              alertify.log("El Registro Ya Existe.");
                                                              $('#lstcodigose').focus();
                                                        }
                }
                else{
                                                        // Si el valor si existe compararlo con mensaje error.
                                                        if (response.mensaje == "Si Registro") {
                                                            alertify.success("Registro(s) Almacenado.");
                                                            $('#lstcodigose').focus();
                                                        }
                                                        
                                                        if (response.mensaje == "No Registro") {
                                                            alertify.log("Registro(s) No Almacenados.");
                                                            $('#lstcodigose').focus();
                                                        }
                                                        if (response.mensaje == "Registro Actualizado") {
                                                            alertify.log("Registro(s) Actualizado.");
                                                            $('#lstcodigose').focus();
                                                            $('#accion_asignatura').val("BuscarRegistro");
                                                        }
                                        }
                                         // borrar información de los textbox.
                                             $('#editarAsignatura').dialog('close');
                                             $('#listaAsignatura').empty();
                                        },
        });
                                        return false;
                                        },
                                        errorPlacement: function(error, element) {
                                            error.appendTo(element.prev("span").append());
                                        }
                                    });
        // BLOQUE PARA NUEVO REGISTRO (Asignatura)
$('#goNuevoAsignatura').on('click',function(){
                // BUSCAR EL ÚLTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
                        accion = 'BuscarCodigoAsignatura';
                // Llamar al archivo php para hacer la consulta y presentar los datos.
                        $.post("php_libs/soporte/phpAjaxMantenimiento_1.inc.php",  {accion: accion},
                          function(data) {
                                // si es exitosa la operación
            $('#txtcodigoasignatura').val(data[0].codigo_asignatura);
                          },"json");
                // Ocultar botón actualizar y mostrar botón guardar.
                        $('#accion_asignatura').val("GuardarAsignatura");
                        accion = $("#accion_asignatura").val();
                // Abrimos el Formulario
                        $('#editarAsignatura').dialog({
                            title:'Agregar Registro',
                            autoOpen:true
                        });
});
        // BLOQUE QUE DE ATRIBUTOS AL CUADRO DE DIÁLOGO (Asignatura)
$('#editarAsignatura').dialog({
    autoOpen: false,
    modal:true,
                show:'fade',
    width: 'auto',
    height: 'auto',
    draggable: true,
                resizable: true,
                position: 'auto',
    close:function(){
                        $('#formA1 input[type="text"]').val('');
                        $('#formA1 textarea').val('');
                        // Ñstcodigo servicio educativo.
                       /* var select0 = $('#lstcodigose_m');
                        select0.val($('option:first', select0).val());
                        var select1 = $('#lstcodigocc');
                        select1.val($('option:first', select1).val());
                        var select2 = $('#lstcodigoarea');
                        select2.val($('option:first', select2).val());
                         */                        
    }
});
}); // FIN DEL FUNCTION.

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