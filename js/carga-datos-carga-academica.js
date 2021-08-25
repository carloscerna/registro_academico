// variables globables.
var annlectivo;
// Carga la INformación de Tabla Año Lectivo.
	$(document).ready(function()
	{
			var ver_ann_lectivo = "si";
			var miselect=$("#lstannlectivo");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-ann-lectivo.php",{verificar_ann_lectivo: ver_ann_lectivo},
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
					}
                                        // COLOAR VALOR EN LA ETIQUETA DE EXPORTAR HOJA DE CALCULO.
                                        var nombre_ann_lectivo = $("#lstannlectivo option:selected").html();
                                        var codigo_ann_lectivo = $("#lstannlectivo option:selected").val();
                                        $("label[for='NombreAnnLectivo']").text(nombre_ann_lectivo);
                                        $("label[for='CodigoAnnLectivo']").text(codigo_ann_lectivo);
                                        
                                        // Ver datos de la modalidad.
                                        // PARA LA PESTAÑA ENCARGADO GRADO.
                                        var miselect1=$("#lstCodigoModalidad");
                                        annlectivo=$("#lstannlectivo").val();
                                	$.post("includes/cargar-bachillerato.php", { annlectivo: annlectivo },
                                	       function(data){
                                		miselect1.empty();
                                                miselect1.append("<option value=00>Seleccionar...</option>");
                                		for (var i=0; i<data.length; i++) {
                                			miselect1.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                		}
                                
                                                /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                                                // FORM PRINCIPAL DE LA BUSQUEDA MOSTRAR INFORMACIÓN.
                                                var miselect=$("#lstCodigoPersonal");
                                                miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                                                
                                                annlectivo=$("#lstannlectivo").val();
                                                $.post("includes/cargar_nombre_personal_docente.php", { annlectivo: annlectivo },
                                                        function(data) {
                                                                miselect.empty();
                                                                for (var i=0; i<data.length; i++) {
                                                                        miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                                }
                                                        // COLOAR VALOR EN LA ETIQUETA DE EXPORTAR HOJA DE CALCULO.
                                                                var nombre_docente = $("#lstCodigoPersonal option:selected").html();
                                                                $("label[for='NombreDocente']").text(nombre_docente); 
                                                }, "json");                                                                       
                                                
                                        }, "json");
			}, "json");                                     
	});

	// Información del Código Personal.
	$(document).ready(function()
	{
	// Parametros para el lstmuncipio.
	$("#lstCodigoPersonal").change(function () {
                // COLOAR VALOR EN LA ETIQUETA DE EXPORTAR HOJA DE CALCULO.
                        var nombre_docente = $("#lstCodigoPersonal option:selected").html();
                        $("label[for='NombreDocente']").text(nombre_docente); 
	});
	});
        
	// ACTUALIZAR LOS VALORES CUANDO CAMBIAL EL AÑO LECTIVO.
	$(document).ready(function()
	{                      
                
                // Parametros para el lstmuncipio.
                $("#lstannlectivo").change(function () {
                                // Ver datos de la modalidad.
                                        // PARA LA PESTAÑA ENCARGADO GRADO.
                                        var miselect1=$("#lstCodigoModalidad");
                                        annlectivo=$("#lstannlectivo").val();
                                	$.post("includes/cargar-bachillerato.php", { annlectivo: annlectivo },
                                	       function(data){
                                		miselect1.empty();
                                                miselect1.append("<option value=00>Seleccionar...</option>");
                                		for (var i=0; i<data.length; i++) {
                                			miselect1.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                		}
                                
                                                /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
                                                // FORM PRINCIPAL DE LA BUSQUEDA MOSTRAR INFORMACIÓN.
                                                var miselect=$("#lstCodigoPersonal");
                                                miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                                                
                                                annlectivo=$("#lstannlectivo").val();
                                                $.post("includes/cargar_nombre_personal_docente.php", { annlectivo: annlectivo },
                                                        function(data) {
                                                                miselect.empty();
                                                                for (var i=0; i<data.length; i++) {
                                                                        miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                                }
                                                        // COLOAR VALOR EN LA ETIQUETA DE EXPORTAR HOJA DE CALCULO.
                                                                var nombre_docente = $("#lstCodigoPersonal option:selected").html();
                                                                $("label[for='NombreDocente']").text(nombre_docente); 
                                                }, "json");                                                                       
                                                
                                        }, "json");        
                }); // FIN DE LA FUNCION
	}); // FIN DEL DOCUMENT READY