// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
var accion = "";
var Id_Editar_Eliminar = 0;
var Accion_Editar_Eliminar = "noAccion";
var codigo_area = "";
var CodigoIndicador = "";
var CodigoDimension = "";
var CodigoSubDimension = "";
var codigo_se = "";
var texto_se = "";
var msjEtiqueta = "";
// INICIO DE LA FUNCION PRINCIPAL.
$(function(){
//
//  INVISILBLE TODOS LOS MENSAJES.
//
    $("#AlertSE").css("display", "none");
    //
//  OPCIONES PARA EL TAB NAV
//
    $(document).ready(function () {
           //
    // ÑO,ÒAR DATPS DEPÈNDIENTE DEL TAB DE NAV
    //
    $("#NavServicioEducativo ul.nav > li > a").on("click", function () {
        $TextoTab = $(this).text();
  
        
        if($TextoTab == "Asignaturas"){
            // Borrar información de la Tabla.
                $('#listaContenidoSE').empty();
            // Select a 00...
                $("#lstcodigose").val('00')
  
        }else{
            //alert("Nav-Tab " + $TextoTab);
        }
      });
        //
        // SELECFT ON ONCHANGE
        //
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // BUSCAR REGISTROS (ASIGNATURA CREADAS)
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // funcion onchange.
        $('#lstcodigose').on('change', function() {
            $("#AlertSE").css("display", "none");
          });
        // funcion onchange.
        $('#lstArea').on('change', function() {
            CodigoArea = $("#lstArea").val();

            var miselect=$("#lstDimension");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
            miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            
            $.post("includes/cargar-area-dimension.php", {CodigoArea: CodigoArea},
                function(data) {
                    miselect.empty();
                    miselect.append("<option value='00'>Seleccionar...</option>");
                    for (var i=0; i<data.length; i++) {
                        if(CodigoDimension == data[i].codigo){
                            miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                        }else{
                            miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
                    }
            }, "json");  
          });
        // funcion onchange.
        $('#lstDimension').on('change', function() {
            CodigoArea = $("#lstArea").val();
            CodigoDimension = $("#lstDimension").val();
            // seelct a modificar o rellenar
            var miselect=$("#lstSubDimension");
            /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
            miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
            // ajax.
            $.post("includes/cargar-area-subdimension.php", {CodigoArea: CodigoArea, CodigoDimension: CodigoDimension},
                function(data) {
                    miselect.empty();
                    miselect.append("<option value='00'>Seleccionar...</option>");
                    for (var i=0; i<data.length; i++) {
                        if(CodigoSubDimension == data[i].codigo){
                            miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                        }else{
                            miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                        }
                    }
            }, "json");  
 
        // funcion onchange.
        $('#lstSubDimension').on('change', function() {
                // seelct a modificar o rellenar
                var miselect=$("#lstIndicadorCalificacion");
                /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                // ajax.
                $.post("includes/cargar-cc.php", 
                    function(data) {
                        miselect.empty();
                        miselect.append("<option value='00'>Seleccionar...</option>");
                        for (var i=0; i<data.length; i++) {
                            if(CodigoIndicador == data[i].codigo){
                                miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                            }else{
                                miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                            }
                        }
                }, "json");  
            //
            // BUSCAR Y GENERAR NUEVO CODIGO PARA LA ASIGNATURA.
            //
                    // BUSCAR EL ÚLTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
                    accion = 'BuscarCodigoAsignatura';
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion},
                                function(data) {
                                    // si es exitosa la operación
                                        $('#CodigoAsignatura').val(data[0].codigo_asignatura);
                                },"json");
                    // RETORNAR EL VALOR DEL ACCION SEGUN ETIQUETA LABEL.
                    msjEtiqueta = $("label[for=LblTitulo]").text();
                            if(msjEtiqueta == "Asignatura | Actualizar")
                            {
                                accion = "ActualizarAsignatura";
                            }else{
                                accion = "GuardarAsignatura";
                            }
          });
          });
        ///////////////////////////////////////////////////
		// funcionalidad del botón que abre el formulario
		///////////////////////////////////////////////////
	    $("#VentanaAsignatura").on('hidden.bs.modal', function () {
            // Limpiar variables Text, y textarea
				$("#formVentanaAsignatura")[0].reset();
                $('#formVentanaAsignatura').trigger("reset");
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
		$('body').on('click','#listaContenidoSE a',function (e){
			e.preventDefault();
			// Id Usuario
    			Id_Editar_Eliminar = $(this).attr('href');
	    		accion_ok = $(this).attr('data-accion');
                    // EDITAR LA ASIGNATURA
                    if($(this).attr('data-accion') == 'editar_asignatura'){
                        // Valor de la acción
                            $('#accion_asignatura').val('ActualizarAsignatura');
                            accion = 'EditarAsignatura';
                            
                            // obtener el valor del id.
                            var id_ = $(this).parent().parent().children('td:eq(2)').text();
                            
                            // Llamar al archivo php para hacer la consulta y presentar los datos.
                            $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  { id_: id_, accion: accion},
                                function(data) {
                                // Llenar el formulario con los datos del registro seleccionado tabs-1
                                // Datos Generales
                                    codigo_se = $("#lstcodigose").val();
                                    texto_se = $("#lstcodigose option:selected").html();
                                    $("#TextoSE").text(texto_se);
                                    //
                                    listar_CodigoEstatus(data[0].codigo_estatus);
                                    listar_CodigoAreaAsignatura(data[0].codigo_area);
                                    listar_CodigoAreaAsignaturaDimension(data[0].codigo_area_dimension);
                                    listar_CodigoAreaAsignaturaSubdimension(data[0].codigo_area, data[0].codigo_area_dimension, data[0].codigo_area_subdimension);
                                    listar_CodigoIndicadorCalificacion(data[0].codigo_cc);
                                    //
                                    $('#IdAsignatura').val(data[0].id_asignatura);
                                    $('#CodigoAsignatura').val(data[0].codigo);
                                    $('#OrdenAsignatura').val(data[0].ordenar);
                                    $('#DescripcionAsignatura').val(data[0].nombre);
                                    // Abrir ventana modal.
                                    $('#VentanaAsignatura').modal("show");
                                    $("label[for=LblTitulo]").text("Asignatura | Actualizar");
                                    // reestablecer el accion a=ActulizarAsignatura.
                                    accion = "ActualizarAsignatura";
                                },"json");
                    }
                    // ELIMINAR REGISTRO ASIGNATURA.
                    if($(this).attr('data-accion') == 'eliminar_asignatura'){
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
                                    url:"php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",                     
                                    data: {                     
                                            accion_buscar: 'eliminar_asignatura', codigo_id_: Id_Editar_Eliminar,
                                            },                     
                                    success: function(response) {                     
                                            if (response.respuesta === true) {                     		
                                                // Asignamos valor a la variable acción
                                                    $('#accion_asignatura').val('BuscarAsignatura');
                                                    var codigo_se = $("#lstcodigose").val();
                                                    accion = 'BuscarAsignatura';
                                                    //
                                                    //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                                                    //
                                                    if(codigo_se == "00"){
                                                        $("#AlertSE").css("display", "block");
                                                        return;
                                                    }
                                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion, codigo_se: codigo_se},
                                                        function(response) {
                                                        if (response.respuesta === true) {
                                                            toastr["info"]('Registros Encontrados', "Sistema");
                                                        }
                                                        if (response.respuesta === false) {
                                                            toastr["warning"]('Registros No Encontrados', "Sistema");
                                                        }                                                                                    // si es exitosa la operación
                                                            $('#listaContenidoSE').empty();
                                                            $('#listaContenidoSE').append(response.contenido);
                                                        },"json");
                                            }
                                            if (response.respuesta === false) {                     		
                                                toastr["info"]('Registro no Eliminado... El còdigo está está activo en la Tabla Notas.', "Sistema");
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
	$("#checkBoxAllSE").on("change", function () {
		$("#listadoContenidoSE tbody input[type='checkbox'].case").prop("checked", this.checked);
	});
	
	$("#listadoContenidoSE tbody").on("change", "input[type='checkbox'].case", function () {
	  if ($("#listadoContenidoSE tbody input[type='checkbox'].case").length == $("#listadoContenidoSE tbody input[type='checkbox'].case:checked").length) {
		  $("#checkBoxAllSE").prop("checked", true);
	  } else {
		  $("#checkBoxAllSE").prop("checked", false);
	  }
	 });	
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// ACTIVAR Y DESACTIVAR CHECKBOX DE LA TABLA.
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////     
        //
        //  funcion click
        //
            $('#goBuscarSE').on('click',function(){
                // Asignamos valor a la variable acción
                    $('#accion_asignatura').val('BuscarAsignatura');
                    codigo_se = $("#lstcodigose").val();
                    accion = 'BuscarAsignatura';
                    //
                    //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                    //
                    if(codigo_se == "00"){
                        $("#AlertSE").css("display", "block");
                        $("#TextoAlert").text("Debe Seleccionar un Servicio Educativo para Buscar.");
                        return;
                    }
                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                    $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion, codigo_se: codigo_se},
                        function(response) {
                        if (response.respuesta === true) {
                            toastr["info"]('Registros Encontrados', "Sistema");
                        }
                        if (response.respuesta === false) {
                            toastr["warning"]('Registros No Encontrados', "Sistema");
                        }                                                                                    // si es exitosa la operación
                            $('#listaContenidoSE').empty();
                            $('#listaContenidoSE').append(response.contenido);
                        },"json");
            });
            //////////////////////////////////////////////////////////////////////////////////
            /* VER #CONTROLES CREADOS */
            //////////////////////////////////////////////////////////////////////////////////
            $('#goNuevoSE').on('click', function(){
                texto_se = $("#lstcodigose option:selected").html();
                codigo_se = $("#lstcodigose").val();
                accion = 'GuardarAsignatura';
                $('#accion_asignatura').val('GuardarAsignatura');

                //
                //  CONDICONAR EL SELECT SERVICIO EDUCATIVO.
                //
                if(codigo_se == "00"){
                    $("#AlertSE").css("display", "block");
                    $("#TextoAlert").text("Debe Seleccionar un Servicio Educativo para Crear uno Nuevo.");
                    return;
                }else{
                    $("#TextoSE").text(texto_se);
                    // buscare codigo estatus
                    listar_CodigoEstatus();
                    listar_CodigoAreaAsignatura();
                }
                // Abrir ventana modal.
                $('#VentanaAsignatura').modal("show");
                $("label[for=LblTitulo]").text("Asignatura | Nuevo");
            });
            //
            // ENVIO DE DATOS Y VALIDAR INFORMACION DEL FORM
            //
            $('#goGuardarAsignatura').on( 'click', function () {
                // enviar form
                    $('#formVentanaAsignatura').submit();
            });
            //	  
            // Validar Formulario para la buscque de registro segun el criterio.   
            // PARA GUARDAR O ACTUALIZAR.
            $('#formVentanaAsignatura').validate({
                ignore:"",
                rules:{
                        DescripcionAsignatura: {required: true, minlength: 4},
                        CodigoAsignatura:{required: true, minlength: 2},
                        lstIndicadorCalificacion: {required: true},
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
                                    toastr.error("Faltan Datos...");
                            });            
                        },
                    submitHandler: function(){	
                        var str = $('#formVentanaAsignatura').serialize();
                        //alert(str);
                    // VALIDAR LOS SELECT...
                    //
                        if($('#lstArea').val() == '00'){
                            alert("Debe seleccionar Area de la Asignatura.");
                            return;
                        }
                        if($('#lstDimension').val() == '00'){
                            alert("Debe seleccionar Dimensión de la Asignatura.");
                            return;
                        }
                        if($('#lstSubDimension').val() == '00'){
                            alert("Debe seleccionar Subdimensión de la Asignatura.");
                            return;
                        }
                    ///////////////////////////////////////////////////////////////			
                    // Inicio del Ajax. guarda o Actualiza los datos del Formualrio.
                    ///////////////////////////////////////////////////////////////
                        $.ajax({
                            beforeSend: function(){

                            },
                            cache: false,
                            type: "POST",
                            dataType: "json",
                            url:"php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",
                            data:str + "&CodigoSE=" + codigo_se + "&accion=" + accion + "&id=" + Math.random(),
                            success: function(response){
                                // Validar mensaje de error
                                if(response.respuesta == false){
                                    toastr["error"](response.mensaje, "Sistema");
                                }
                                else{
                                    toastr["success"](response.mensaje, "Sistema");
                                    // Abrir ventana modal.
                                         $('#VentanaAsignatura').modal("hide");
                                         $("#formVentanaAsignatura")[0].reset();
                                    // Llamar al archivo php para hacer la consulta y presentar los datos.
                                        $('#accion_asignatura').val('BuscarAsignatura');
                                        accion = 'BuscarAsignatura';
                                        $.post("php_libs/soporte/Mantenimiento/Servicio Educativo/phpAjaxServicioEducativo.php",  {accion: accion, codigo_se: codigo_se},
                                            function(response) {
                                                if (response.respuesta === true) {
                                                    toastr["info"]('Registros Encontrados', "Sistema");
                                                }
                                                if (response.respuesta === false) {
                                                    toastr["warning"]('Registros No Encontrados', "Sistema");
                                                }                                                                                    // si es exitosa la operación
                                                    $('#listaContenidoSE').empty();
                                                    $('#listaContenidoSE').append(response.contenido);
                                            },"json");
                                    }               
                            },
                        });
                    },
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
    ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo_estatus
////////////////////////////////////////////////////////////
function listar_CodigoEstatus(CodigoEstatus){
    var miselect=$("#lstEstatus");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    
    $.post("includes/cargar_estatus.php",
        function(data) {
            miselect.empty();
            for (var i=0; i<data.length; i++) {
                if(CodigoEstatus == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
   ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo_area_asignatura
////////////////////////////////////////////////////////////
function listar_CodigoAreaAsignatura(CodigoAreaAsignatura){
    var miselect=$("#lstArea");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    //
    $.post("includes/cargar-area-asignatura.php",
        function(data) {
            miselect.empty();
            miselect.append("<option value='00'>Seleccionar...</option>");
            for (var i=0; i<data.length; i++) {
                if(CodigoAreaAsignatura == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
   ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo_area_dimension
////////////////////////////////////////////////////////////
function listar_CodigoAreaAsignaturaDimension(CodigoAreaDimension){
    var miselect=$("#lstDimension");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    //
    $.post("includes/cargar-area-dimension.php", {CodigoArea: CodigoAreaDimension},
        function(data) {
            miselect.empty();
            miselect.append("<option value='00'>Seleccionar...</option>");
            for (var i=0; i<data.length; i++) {
                if(CodigoAreaDimension == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
   ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo_area_subdimension
////////////////////////////////////////////////////////////
function listar_CodigoAreaAsignaturaSubdimension(CodigoArea, CodigoAreaDimension, CodigoSubdimension){
    var miselect=$("#lstSubDimension");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    //
    $.post("includes/cargar-area-subdimension.php", {CodigoArea: CodigoArea, CodigoDimension: CodigoAreaDimension},
        function(data) {
            miselect.empty();
            miselect.append("<option value='00'>Seleccionar...</option>");
            for (var i=0; i<data.length; i++) {
                if(CodigoSubdimension == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}
   ///////////////////////////////////////////////////////////////////////
// TODAS LAS TABLAS VAN HA ESTAR EN ASIGNATURA.*******************
// FUNCION LISTAR TABLA catalogo Indicador Calificaciones
////////////////////////////////////////////////////////////
function listar_CodigoIndicadorCalificacion(CodigoIndicadorCalificacion){
    var miselect=$("#lstIndicadorCalificacion");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
    //
    $.post("includes/cargar-cc.php",
        function(data) {
            miselect.empty();
            miselect.append("<option value='00'>Seleccionar...</option>");
            for (var i=0; i<data.length; i++) {
                if(CodigoIndicadorCalificacion == data[i].codigo){
                    miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
                }else{
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
            }
    }, "json");    
}