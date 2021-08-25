// Variables globales
var accion = "";
var codigo_modalidad = "";
var codigo_annlectivo = "";
var codigo_gradoseccion = "";
var todos = "";
/* ***********************************************************************************************************/
// FUNCIONES DEL DOCUMENT READY
$(document).ready(function(){
    var ver_ann_lectivo = "si";
    var miselect=$("#lstannlectivo");
/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
    miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
// LLAMAR AL AJAX, PARA CARGA DE DATOS.    
    $.post("includes/cargar-ann-lectivo.php",{verificar_ann_lectivo: ver_ann_lectivo},
        function(data) {
            miselect.empty();
            miselect.append('<option value="">Seleccionar...</option>');
            for (var i=0; i<data.length; i++) {
                miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
            }
    }, "json");
});
// Información del año lectivo y modalidad.
$(document).ready(function(){
    // Parametros para el año lectivo.
    $("#lstannlectivo").change(function () {
        var miselect=$("#lstmodalidad");
    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');

        $("#lstannlectivo option:selected").each(function () {
            elegido=$(this).val();
            annlectivo=$("#lstannlectivo").val();
            $.post("includes/cargar-bachillerato.php", { annlectivo: annlectivo },
                function(data){
                miselect.empty();
                miselect.append('<option value="">Seleccionar...</option>');
            for (var i=0; i<data.length; i++) {
                miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
            }
            }, "json");			
        });
    });
	    // Parametros para el grado y sección, al seleccionar el bachillerato.
	    $("#lstmodalidad").change(function () {
				var miselect=$("#lstgradoseccion");
				var lblturno=$("#lblturno");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstmodalidad option:selected").each(function () {
				lblturno.empty();
				elegido=$(this).val();
				ann=$("#lstannlectivo").val();
				$.post("includes/cargar-grado-seccion.php", { elegido: elegido, ann: ann },
				       function(data){
					miselect.empty();
					miselect.append('<option value="">Seleccionar...</option>');
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno + '">' + data[i].descripcion_grado + ' ' + data[i].descripcion_seccion + ' - ' + data[i].descripcion_turno + '</option>');
					}
			}, "json");
		    });
	    });
});
/* ***********************************************************************************************************/
/* DECLARACIÓN DE FUNCIONES **********************************************************************************************************/
$(function(){    
// Parametros para el grado y sección, al seleccionar el bachillerato.
$("#lstgradoseccion").change(function () {
// Variables.
codigo_modalidad = $('#lstmodalidad').val();
codigo_annlectivo = $('#lstannlectivo').val();
codigo_gradoseccion = $('#lstgradoseccion').val();
todos = codigo_modalidad + codigo_gradoseccion + codigo_annlectivo;
accion = 'BuscarEstudiante';
// Ejecutar Ajax.
    $.ajax({
        beforeSend: function(){
            $('#listaOK').empty();
    },
    cache: false,
    type: "POST",
    dataType: "json",
    url:"php_libs/soporte/phpAjaxPromedioCalificaciones.php",
    data: {
            accion: accion, todos: todos,
            },
    success: function(response) {
            if (response.respuesta === true) {
                // etiqueta CARD TITULO TABLA.
                $("label[for='titulo_tabla']").text(response.titulo_tabla);
                $('#listaOK').append(response.contenido);
                toastr["info"](response.mensaje, "Sistema");
                $("#goCalcularPromedios").prop("disabled",false);
            }
    }
    });  
}); // FUNCIÓN LSTGRADOSECCION

// funcionalidad del botón Actualizar
$('#goCalcularPromedios').on('click',function(){
// Variables.
    accion = 'CalcularPromedios'; var codigo_alumno_ = []; var codigo_matricula_ = []; var codigo_nie_ = []; var fila = 0;
    codigo_annlectivo = $("#lstannlectivo").val();
    codigo_grado = $("#lstgradoseccion").val();
    codigo_modalidad = $("#lstmodalidad").val();
// Prepar objeto Tabla y Recorrerla
    var $objCuerpoTabla=$("#tablaEstudiantes").children().prev().parent();

    $objCuerpoTabla.find("tbody tr").each(function(){
        var codigo_alumno = $(this).find('td').eq(0).find("input[name='codigo_alumno']").val();
        var codigo_matricula = $(this).find('td').eq(0).find("input[name='codigo_matricula']").val();
        var codigo_nie = $(this).find('td').eq(1).html();
        // dar valor a las arrays.
            codigo_alumno_[fila] = codigo_alumno;
            codigo_matricula_[fila] = codigo_matricula;
            codigo_nie_[fila] = codigo_nie;
            fila = fila + 1;
    }); // FIN DE RECORRIDO DE LA TABLA.
// Ejecutar Ajax.
        $.ajax({
            beforeSend: function(){
                
        },
        cache: false,
        type: "POST",
        dataType: "json",
        url:"php_libs/soporte/phpAjaxPromedioCalificaciones.php",
        data: {
                accion: accion, fial: fila, codigo_alumno_: codigo_alumno_, codigo_matricula_: codigo_matricula_, codigo_nie_: codigo_nie_,
                codigo_grado: codigo_gradoseccion, codigo_modalidad: codigo_modalidad, codigo_annlectivo: codigo_annlectivo
                },
        success: function(response) {
                if (response.respuesta === true) {

                }
        }
        });                
});

}); // FIN DE LAS FUNCIONES PRINCIPALES.
// Pasar foco cuando seleccionar un encargado.
function PasarFoco()
   {
       $('#lstannlectivo').focus();
   }
