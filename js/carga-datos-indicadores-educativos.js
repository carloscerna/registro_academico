// variables globables.
var annlectivo;
// Carga la INformaci�n de Tabla A�o Lectivo.
	$(document).ready(function()
	{
        var ver_ann_lectivo = "si";
        var miselect=$("#lstannlectivo");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/cargar-ann-lectivo.php",{verificar_ann_lectivo: ver_ann_lectivo},
            function(data) {
                miselect.empty();
                miselect.append('<option value="">Seleccionar...</option>');
                for (var i=0; i<data.length; i++) {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
                }
                // COLOAR VALOR EN LA ETIQUETA DE EXPORTAR HOJA DE CALCULO.
                var nombre_ann_lectivo = $("#lstannlectivo option:selected").html();
                var codigo_ann_lectivo = $("#lstannlectivo option:selected").val();
                $("label[for='NombreAnnLectivo']").text(nombre_ann_lectivo);
                $("label[for='CodigoAnnLectivo']").text(codigo_ann_lectivo);
                        /* VACIAMOS EN LA ETIQUETA RESPECTIVA.*/       
                        annlectivo=$("#lstannlectivo").val();
                        $.post("includes/cargar_indicadores.php", { ann_lectivo: annlectivo },
                                function(data) {
                                    // TOTAL DE ALUMNOS MASCULINO Y FEMENINO
                                    var femenino = Number(data[0].total_femenino);
                                    var masculino = Number(data[0].total_masculino);
                                    // TOTAL DE ALUMNOS MASCULINO Y FEMENINO RETIRADOS.
                                    var femenino_retirado = Number(data[0].total_femenino_retirado);
                                    var masculino_retirado = Number(data[0].total_masculino_retirado);
                                    // TOTAL DE ALUMNOS MASCULINO Y FEMENINO RETIRADOS.
                                    var total_femenino =  femenino - femenino_retirado;
                                    var total_masculino = masculino - masculino_retirado;
                                    // TOTAL DE ALUMNOS.
                                    var total_estudiantes = (total_masculino + total_femenino);
                                // COLOAR VALOR EN LA ETIQUETA PARA LOS INDICADORES MASCULINO Y FEMENINO.  
                                    $("label[for='totalEstudiantesFemenino']").text(total_femenino); 
                                    $("label[for='totalEstudiantesMasculino']").text(total_masculino); 
                                    $("label[for='totalEstudiantes']").text(total_estudiantes); 
                        }, "json");                                                                       
        }, "json");                                     
	});
// ACTUALIZAR LOS VALORES CUANDO CAMBIAL EL A�O LECTIVO.
$(document).ready(function()
{                      
    // Parametros para el lstmuncipio.
    $("#lstannlectivo").change(function () {
        annlectivo=$("#lstannlectivo").val();
        /* VACIAMOS EN LA ETIQUETA RESPECTIVA.*/       
        $.post("includes/cargar_indicadores.php", { ann_lectivo: annlectivo },
            function(data) {
                var total_femenino = Number(data[0].total_femenino);
                var total_masculino = Number(data[0].total_masculino);
                var total_estudiantes = (total_femenino + total_masculino);
                var total_familias = data[0].total_familias;
                var total_femenino_docentes = Number(data[0].total_femenino_docentes);
                var total_masculino_docentes = Number(data[0].total_masculino_docentes);
                var total_docentes = (total_femenino_docentes + total_masculino_docentes);
                
            // COLOAR VALOR EN LA ETIQUETA PARA LOS INDICADORES MASCULINO Y FEMENINO.  
                $("label[for='totalEstudiantesFemenino']").text(total_femenino); 
                $("label[for='totalEstudiantesMasculino']").text(total_masculino); 
                $("label[for='totalEstudiantes']").text(total_estudiantes);                 
                $("label[for='TotalFamilias']").text(total_familias);              
                $("label[for='totalDocentesFemenino']").text(total_femenino_docentes); 
                $("label[for='totalDocentesMasculino']").text(total_masculino_docentes);    
                $("label[for='totalDocentes']").text(total_docentes);              
            }, "json");        
    }); // FIN DE LA FUNCION
}); // FIN DEL DOCUMENT READY