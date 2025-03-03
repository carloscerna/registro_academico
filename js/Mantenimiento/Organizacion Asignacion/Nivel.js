// id de user global
var idUser_ok = 0;
var accion_modalidad = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_annlectivo = "";
var codigo_modalidad = "";
var msjEtiqueta = "";
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
//
    $("#AlertModalidad").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
        var miselect=$("#lstAnnLectivoModalidad");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/cargar-ann-lectivo.php",
            function(data) {
                miselect.empty();
                miselect.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
                }
        }, "json");
        // LISTADO DE LAS MODALIDES
            var miselect2=$("#lstModalidad");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            //        
                $.post("includes/cargar-modalidad.php",
                function(data){
                        miselect2.empty();
                        miselect2.append("<option value='00'>Seleccionar...</option>");
                        for (var i=0; i<data.length; i++) {
                            miselect2.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
                }, "json");			
        // LISTAR PARA EL SERVIICO EDUCATIVO - COMPONENTES DE ESTUDIOS.
            var miselect3=$("#lstModalidadServicioEducativo");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
            miselect3.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            
            $.post("includes/cargar-servicio-educativo.php",
                function(data) {
                miselect3.empty();
                miselect3.append("<option value='00'>Seleccionar...</option>");
                for (var i=0; i<data.length; i++) {
                    miselect3.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }			
            }, "json");
        //
        // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
        //
    $("#NavOrganizacionAsignacion ul.nav > li > a").on("click", function () {
        $TextoTab = $(this).text();
        
        if($TextoTab == "Nivel"){
            // Borrar información de la Tabla.
                $('#listaContenidoModalidad').empty();
                $("#AlertModalidad").css("display", "none");
            // Select a 00...
                $("#lstAnnLectivoModalidad").val('00')
                $("#lstModalidad").val('00')
        }else{
            //alert("Nav-Tab " + $TextoTab);
        }
    });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (HORARIOS CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // funcion onchange.
        $('#lstAnnLectivoModalidad').on('change', function() {
            $("#AlertModalidad").css("display", "none");
        });
        // Nivel o Modalidad.
        $('#lstModalidad').on('change', function() {
            $("#AlertModalidad").css("display", "none");
        });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
        $("#VentaModalidad").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaModalidad")[0].reset();
                $('#formVentanaModalidad').trigger("reset");
				$("label.error").remove();
                accion = "";
            // 
		});
    });
    //
    // FUNCIONALIDAD DE LOS DIFERENTES BOTONES
    //
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // BLOQUE PARA ADMINISTRAR LAS ASIGNATURAS.
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
        // BLOQUE EXTRAER INFORMACIÓN DEL REGISTROS (ASIGANTURAS)
        //
		$('body').on('click','#listaContenidoModalidad a',function (e){
			e.preventDefault();
			// Id Usuario
                Id_Editar_Eliminar = $(this).attr('href');
                accion_ok = $(this).attr('data-accion');
                    // EDITAR LA ASIGNATURA
                    if($(this).attr('data-accion') == 'EditarModalidad'){
                    }
                    // ELIMINAR REGISTRO ASIGNATURA.
                    if($(this).attr('data-accion') == 'EliminarModalidad'){
                        //	ENVIAR MENSAJE CON SWEETALERT 2, PARA CONFIRMAR SI ELIMINA EL REGISTRO.
                        const swalWithBootstrapButtons = Swal.mixin({
                            customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-danger'
                            },
                            buttonsStyling: false
                        })
                        //
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
                            // PROCESO PARA ELIMINAR REGISTRO.
                                    // ejecutar Ajax.. 
                                    $.ajax({
                                    cache: false,                     
                                    type: "POST",                     
                                    dataType: "json",                     
                                    url:"php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",                     
                                    data: {                     
                                            accion_buscar: 'EliminarModalidad', id_: Id_Editar_Eliminar,
                                            },                     
                                    success: function(response) {                     
                                            if (response.respuesta === true) {                     		
                                                // Asignamos valor a la variable acción
                                                    $('#accion_modalidad').val('BuscarModalidad');
                                                    accion_modalidad = 'BuscarModalidad';
                                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                                        function(response) {
                                                            if (response.respuesta === true) {
                                                                toastr["info"]('Registros Encontrados', "Sistema");
                                                            }
                                                            if (response.respuesta === false) {
                                                                toastr["warning"]('Registros No Encontrados', "Sistema");
                                                            }                                                                                    // si es exitosa la operación
                                                                $('#listaContenidoModalidad').empty();
                                                                $('#listaContenidoModalidad').append(response.contenido);
                                                        },"json");
                                            }
                                    }                     
                                    });
                            //////////////////////////////////////
                            } else if (
                            /* Read more about handling dismissals below */
                            result.dismiss === Swal.DismissReason.cancel
                            ) {
                            swalWithBootstrapButtons.fire(
                                'Cancelar',
                                'Su Registro no ha sido Eliminado :)',
                                'error'
                            )
                            }
                        })
                    }
        });
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$("#checkBoxAllModalidad").on("change", function () {
		$("#listadoContenidoModalidad tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoModalidad tbody").on("change", "input[type='checkbox'].case", function () {
        if ($("#listadoContenidoModalidad tbody input[type='checkbox'].case").length == $("#listadoContenidoModalidad tbody input[type='checkbox'].case:checked").length) {
            $("#checkBoxAllModalidad").prop("checked", true);
        } else {
            $("#checkBoxAllModalidad").prop("checked", false);
        }
    });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
        //
        //  funcion click
        //
            $('#goBuscarModalidad').on('click',function(){
                // Asignamos valor a la variable acción
                    $('#accion_modalidad').val('BuscarModalidad');
                    codigo_annlectivo = $("#lstAnnLectivoModalidad").val();
                    codigo_modalidad = $("#lstModalidad").val();
                    accion = 'BuscarModalidad';
                    //
                    //  CONDICONAR EL SELECT HORARIOS DE PERIODOS..
                    //
                    if(codigo_annlectivo == "00"){
                        $("#AlertModalidad").css("display", "block");
                        $("#TextoAlertModalidad").text("Debe Seleccionar Año Lectivo para Buscar.");
                        return;
                    }
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                    $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                        function(response) {
                        if (response.respuesta === true) {
                            toastr["info"]('Registros Encontrados', "Sistema");
                        }
                        if (response.respuesta === false) {
                            toastr["error"]('Registros No Encontrados', "Sistema");
                        }                                                                                    // si es exitosa la operación
                            $('#listaContenidoModalidad').empty();
                            $('#listaContenidoModalidad').append(response.contenido);
                        },"json");
            });
            //////////////////////////////////////////////////////////////////////////////////
            /* VER #CONTROLES CREADOS */
            //////////////////////////////////////////////////////////////////////////////////
            $('#goGuardarModalidad').on('click', function(){
                codigo_annlectivo = $("#lstAnnLectivoModalidad").val();
                codigo_modalidad = $("#lstModalidad").val();
                accion = 'GuardarModalidad';
                    $('#accion_modalidad').val('GuardarModalidad');
                //
                //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                //
                if(codigo_annlectivo == "00"){
                    $("#AlertModalidad").css("display", "block");
                    $("#TextoAlertModalidad").text("Debe Seleccionar un Año Lectivo para Guardar un Nivel.");
                    return;
                }
                if(codigo_modalidad == "00"){
                    $("#AlertModalidad").css("display", "block");
                    $("#TextoAlertModalidad").text("Debe Seleccionar un Nivel para Guardar.");
                    return;
                }
                // enviar form
                    $('#FormModalidad').submit();
            });

            //	  
            // Validar Formulario para la buscque de registro segun el criterio.   
            // PARA GUARDAR O ACTUALIZAR.
            $('#FormModalidad').validate({
                ignore:"",
                rules:{
                        lstAnnLectivoModalidad: {required: true},
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
                            invalidHandler: function() {
                                setTimeout(function() {
                                    toastr["error"]("Falta Información en el Formulario.", "Sistema");
                            });            
                        },
                    submitHandler: function(){	
                        var str = $('#FormModalidad').serialize();
                        //alert(str);
                    ///////////////////////////////////////////////////////////////			
                    // Inicio del Ajax. guarda o Actualiza los datos del Formualrio.
                    ///////////////////////////////////////////////////////////////
                        $.ajax({
                            beforeSend: function(){

                            },
                            cache: false,
                            type: "POST",
                            dataType: "json",
                            url:"php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                            data:str + "&accion=" + accion + "&id=" + Math.random() + "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_modalidad=" + codigo_modalidad,
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Reiniciar los valores del Formulario.
                                        //$("#FormModalidad").trigger("reset");
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_modalidad').val('BuscarModalidad');
                                        accion = 'BuscarModalidad';
                                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoModalidad').empty();
                                                    $('#listaContenidoModalidad').append(response.contenido);
                                            },"json");
                                    }               
                            },
                        });
                    },
            });
                    //////////////////////////////////////////////////////////////////////////////////////////////
        // GUARDAR EL ORDEN PARA EL NIVEL.
        //////////////////////////////////////////////////////////////////////////////////////////////
        $("#goActualizarOrden").on('click',function () {
            var accion = "ActualizarOrden";
            // Información de la tabla para actualizar código sirai.
                var $objCuerpoTabla=$("#listadoContenidoModalidad").children().prev().parent();
                var id_nivel_ = []; var orden_ = []; 
                var fila = 0;
            // recorre el contenido de la tabla.
                $objCuerpoTabla.find("tbody tr").each(function(){
                    var id_ = $(this).find('td').eq(2).html();
                    var orden =$(this).find('td').eq(6).find("input[name='orden']").val();
                // dar valor a las arrays.
                    id_nivel_[fila] = id_;
                    orden_[fila] = orden;
                        fila = fila + 1;
                });
                //
                $.ajax({
                    beforeSend: function(){       
                    },
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    url:"php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",
                    data: {
                            accion: accion, orden: orden_, fila: fila, 
                            id_nivel: id_nivel_
                            },
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Reiniciar los valores del Formulario.
                                        //$("#FormModalidad").trigger("reset");
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_modalidad').val('BuscarModalidad');
                                        accion = 'BuscarModalidad';
                                        $.post("php_libs/soporte/Mantenimiento/Organizacion Asignacion/phpAjaxOrganizacionAsignacion.php",  {accion: accion, codigo_annlectivo: codigo_annlectivo, codigo_modalidad: codigo_modalidad},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoModalidad').empty();
                                                    $('#listaContenidoModalidad').append(response.contenido);
                                            },"json");
                                    }               
                            },
                });
        }); 
}); // FIN DEL FUNCTION.
//
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