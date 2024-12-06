/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////PARA LA MATRICULA. /////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Carga Datos General del Estudiante cuando sea Nuevo o Edición.
// Variables publicas.
var CodigoDepartamento = "02";
var CodigoMunicipio = "02";
var CodigoDistrito = "01";
var url_data = "includes/cargar_elsalvador.php";
var condicion = 0;
//
//

$(document).ready(function()
{
	if($("#accion").val() == "AgregarNuevoEstudiante"){
			// Seleccionar los SELECT de la Matricula.
			var html_ann = $("#valor_ann").val();
			var html_modalidad = $("#valor_modalidad").val();
			var html_gst = $("#valor_gst").val();
			var ver_ann_lectivo = "si";
			
			var miselect=$("#lstannlectivo");		
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */		
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');		
			$.post("includes/cargar-ann-lectivo.php",{verificar_ann_lectivo: ver_ann_lectivo},		
				function(data) {
					miselect.empty();		
					miselect.append("<option value=00>Seleccionar...</option>");		
					for (var i=0; i<data.length; i++) {
						// SI ES NUEVO ESTUDIANTE.
							if($("#accion").val() == "AgregarNuevoEstudiante"){
//								alert(html_ann + "data: "+ data[i].codigo);
								if(data[i].codigo == $.trim(html_ann)){
									miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].nombre + '</option>');
								}else{
									miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
								}
							}
					}		
			}, "json");
	}
});
$(document).ready(function()
{
	if($("#accion").val() == "AgregarNuevoEstudiante"){
			// Seleccionar los SELECT de la Matricula.
			var html_ann = $("#valor_ann").val();
			var html_modalidad = $("#valor_modalidad").val();
			var html_gst = $("#valor_gst").val();
			// MODALIDAD.
			var miselect_modalidad=$("#lstmodalidad");		
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */		
			miselect_modalidad.find('option').remove().end().append('<option value="">Cargando...</option>').val('');		
			 $.post("includes/cargar-bachillerato.php", { annlectivo: $.trim(html_ann) },
				function(data) {
					miselect_modalidad.empty();		
					miselect_modalidad.append("<option value=00>Seleccionar...</option>");		
					for (var i=0; i<data.length; i++) {
						// SI ES NUEVO ESTUDIANTE.
							if($("#accion").val() == "AgregarNuevoEstudiante"){
								if(data[i].codigo == $.trim(html_modalidad)){
									miselect_modalidad.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
								}else{
									miselect_modalidad.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
								}
							}
					}		
			}, "json");
	}
});

$(document).ready(function()
{
	if($("#accion").val() == "AgregarNuevoEstudiante"){
			// Seleccionar los SELECT de la Matricula.
			var html_ann = $("#valor_ann").val();
			var html_modalidad = $("#valor_modalidad").val();
			var html_gst = $("#valor_gst").val();
			// GRADO, SECCIÓN Y TURNO..
			var miselect_gst=$("#lstgradoseccion");		
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */		
			miselect_gst.find('option').remove().end().append('<option value="">Cargando...</option>').val('');		
			 $.post("includes/cargar-grado-seccion.php", { elegido: $.trim(html_modalidad), ann: $.trim(html_ann)},
				function(data) {
					miselect_gst.empty();		
					miselect_gst.append("<option value=00>Seleccionar...</option>");		
					for (var i=0; i<data.length; i++) {
						// SI ES NUEVO ESTUDIANTE.
							if($("#accion").val() == "AgregarNuevoEstudiante"){
								if(data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno == $.trim(html_gst)){
									miselect_gst.append('<option value="' + data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno + '" selected>' + data[i].descripcion_grado + ' ' + data[i].descripcion_seccion + ' - ' + data[i].descripcion_turno + '</option>');
								}else{
									miselect_gst.append('<option value="' + data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno + '">' + data[i].descripcion_grado + ' ' + data[i].descripcion_seccion + ' - ' + data[i].descripcion_turno + '</option>');
								}
							}
					}		
			}, "json");			
	}
});
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// EN EL CASO QUE SOLO SE HA EDITAR.
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        				
$(document).ready(function()
{
	if($("#accion").val() != "AgregarNuevoEstudiante"){
		var miselect=$("#lstannlectivo");		
		var ver_ann_lectivo = "si";
		/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */		
		miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');		
		$.post("includes/cargar-ann-lectivo.php",{verificar_ann_lectivo: ver_ann_lectivo},		
			function(data) {
				miselect.empty();		
				miselect.append("<option value=00>Seleccionar...</option>");		
				for (var i=0; i<data.length; i++) {
					miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
					}		
		}, "json");
	}
	// Información del año lectivo y modalidad.
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
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
	// Carga la INformación de Tabla Estatus.
	$(document).ready(function()
	{
			var miselect=$("#lstEstatus");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_estatus.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla Genero.
	$(document).ready(function()
	{
			var miselect=$("#lstgenero");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_genero.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla Estado Civil.
	$(document).ready(function()
	{
			var miselect=$("#lstEstadoCivil");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_estado_civil.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla Nivel Escolaridad.
	$(document).ready(function()
	{
			var miselect=$("#LstEstadoFamiliar");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_estado_familiar.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla AFP
	$(document).ready(function()
	{
			var miselect=$("#LstActividadEconomica");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_actividad_economica.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla Tipo de Discapacidad
	$(document).ready(function()
	{
			var miselect=$("#lstTipoDiscapacidad");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_tipo_discapacidad.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla Servicio de Apoyo Educativo.
	$(document).ready(function()
	{
			var miselect=$("#lstServicioApoyoEducativo");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_servicio_apoyo_educativo.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});       
	// DESARROLLO PARA CARGA DE DATOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS. VISTA ELSALVADOR.
		url_data = "includes/cargar_elsalvador.php";
	// llenar select.
		condicion = 1;	// DEPARTAMENTOS.
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito);
	// llenar select.
		condicion = 2;	// MUNICIPIOS.
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito);
	// llenar select
		condicion = 3;	// DISTRITOS.
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito);
 	// 
	//	EVENTO CHANGE
	//	DESARROLLO PARA CARGA DE DATOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS. VISTA ELSALVADOR.
		$(document).ready(function()
		{
			//	CUANDO CAMBIE EL DEPARTAMENTO.
			$("#lstDepartamentoPN").change(function () {
				$("#lstDepartamentoPN option:selected").each(function () {
					CodigoDepartamento = $(this).val(); CodigoMunicipio = "";
					//	limpiar select y rellenar.
						var select = $('#lstDistritoPN'); select.empty();
						condicion = 2;
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito);
				});
					// buscar el distrito segun Municipio.
						CodigoMunicipio = $("#lstMunicipioPN").val(); CodigoDistrito = "";
						condicion = 3;
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito);
			});
			//	CUANDO CAMBIE EL MUNICIPIO
			$("#lstMunicipioPN").change(function () {
				$("#lstMunicipioPN option:selected").each(function () {
					CodigoDepartamento = $("#lstDepartamentoPN").val(); CodigoMunicipio = $(this).val(); CodigoDistrito = "";
					//	limpiar select y rellenar.
						var select = $('#lstDistritoPN'); select.empty();
						condicion = 3;
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito);
				});
			});
		});


	$(document).ready(function()
	{
			var miselect=$("#lstZonaResidencia");
			var miselectP=$("#lstZonaResidenciaP");
			var miselectM=$("#lstZonaResidenciaM");
			var miselectOtro=$("#lstZonaResidenciaO");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectP.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectM.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectOtro.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_zona_residencia.php",
				function(data) {
					miselect.empty();
					miselectP.empty();
					miselectM.empty();
					miselectOtro.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
						miselectP.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
						miselectM.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
						miselectOtro.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
	// Carga la INformación de Tabla Departamento
	$(document).ready(function()
	{
	    // REllenar el select Departamento.
		var miselect=$("#lstDepartamento");
		var miselectPadre=$("#lstDepartamentoP");
		var miselectMadre=$("#lstDepartamentoM");
		var miselectOtro=$("#lstDepartamentoO");
	    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
		miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
		miselectPadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
		miselectMadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
		miselectOtro.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
		$.post("includes/cargar_departamento.php",
		    function(data) {
			miselect.empty();
			miselectPadre.empty();
			miselectMadre.empty();
			miselectOtro.empty();
			    for (var i=0; i<data.length; i++) {
					if(i== 1){
						miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectPadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectMadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectOtro.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectPadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectMadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectOtro.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
						
				}
			}, "json");
	    // REllenar el select Municipio con un Código específico 02 - Santa Ana.
		var miselectMEstudiante=$("#lstMunicipio");
		var miselectMPadre=$("#lstMunicipioP");
		var miselectMMadre=$("#lstMunicipioM");
		var miselectMOtro=$("#lstMunicipioO");
	    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
		miselectMEstudiante.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
		miselectMPadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
		miselectMMadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
		miselectMOtro.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
  		    departamento="02";
			$.post("includes/cargar_municipio.php", { departamento: departamento },
			    function(data){
			    miselectMEstudiante.empty();
				miselectMPadre.empty();
				miselectMMadre.empty();
				miselectMOtro.empty();
				for (var i=0; i<data.length; i++) {
							if(i== 9){
						miselectMEstudiante.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectMPadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectMMadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectMOtro.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselectMEstudiante.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectMPadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectMMadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectMOtro.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
				}
			}, "json");		
		// REllenar el select Municipio con un Código específico 02 - Santa Ana. Seleccionar Cantón
		var miselectC=$("#lstCanton");
	    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
		miselectC.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
  		    departamento="02";
			municipio = "10"
			$.post("includes/cargar_canton.php", { departamento: departamento, municipio: municipio},
			    function(data){
			    miselectC.empty();
				for (var i=0; i<data.length; i++) {
							if(i== 9){
						miselectC.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselectC.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
				}
			}, "json");			
	});
	
	// Carga la INformación de Tabla Departamento
	$(document).ready(function()
	{
		// Parametros para el lstmuncipio.
		$("#lstDepartamento").change(function () {
					var miselect=$("#lstMunicipio");
				/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
				miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
				
			$("#lstDepartamento option:selected").each(function () {
					elegido=$(this).val();
					departamento=$("#lstDepartamento").val();
					$.post("includes/cargar_municipio.php", { departamento: departamento },
						function(data){
						miselect.empty();
						for (var i=0; i<data.length; i++) {
							miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
						}
				}, "json");			
			});
		});
			// Parametros para el lstmuncipio.
			$("#lstMunicipio").change(function () {
	    	    var miselectC=$("#lstCanton");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
				miselectC.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstMunicipio option:selected").each(function () {
				municipio=$(this).val();
				departamento=$("#lstDepartamento").val();
				$.post("includes/cargar_canton.php", { departamento: departamento, municipio: municipio },
				       function(data){
							miselectC.empty();
							for (var i=0; i<data.length; i++) {
								miselectC.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");			
	    });
	});
	});
  	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstEstadoFamiliarP");
			var miselectMadre=$("#lstEstadoFamiliarM");
			var miselectOtro=$("#lstEstadoFamiliarO");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectMadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectOtro.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-familiar.php",
				function(data) {
					miselect.empty();
					miselectMadre.empty();
					miselectOtro.empty();
					for (var i=0; i<data.length; i++) {
					if(i== 7){
						miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectMadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						miselectOtro.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectMadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						miselectOtro.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");
	});
		// Carga la INformación de Tabla Departamento
	$(document).ready(function()
	{
		// Parametros para el lstmuncipio.
	$("#lstDepartamentoP").change(function () {
	    	    var miselect=$("#lstMunicipioP");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstDepartamentoP option:selected").each(function () {
				elegido=$(this).val();
				departamento=$("#lstDepartamentoP").val();
				$.post("includes/cargar_municipio.php", { departamento: departamento },
				       function(data){
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");			
	    });
	});
	});
	////////////////////////////////////////////////////////////////////////////////////////////////
	// Carga la INformación MADRE.
	////////////////////////////////////////////////////////////////////////////////////////////////
	$(document).ready(function()
	{
			var miselect=$("#lstNacionalidadE");
			var miselectMadre=$("#lstNacionalidadM");
			var miselectPadre=$("#lstNacionalidadP");
			var miselectOtro=$("#lstNacionalidadO");
			//
			var theValue = '54';
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectMadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectPadre.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			miselectOtro.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-nacionalidad.php",
				function(data) {
					miselect.empty();
					miselectMadre.empty();
					miselectPadre.empty();
					miselectOtro.empty();
					for (var i=0; i<data.length; i++) {
						if (data[i].codigo == theValue){
							miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
							miselectMadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
							miselectPadre.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
							miselectOtro.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}else{
							miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
							miselectMadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
							miselectPadre.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
							miselectOtro.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
						}
					}
			}, "json");

	});
	// Carga la INformación de Tabla Diagnostico Clinico
	$(document).ready(function()
	{
			var miselect=$("#lstDiagnostico");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_diagnostico.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
// Carga la INformación de Tabla Departamento
	$(document).ready(function()
	{
	    // REllenar el select Departamento.
		var miselect=$("#lstDepartamentoM");
	    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
		miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
		$.post("includes/cargar_departamento.php",
		    function(data) {
			miselect.empty();
			    for (var i=0; i<data.length; i++) {
				if(i== 1){
						miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
				}
			}, "json");
	    // REllenar el select Municipio con un Código específico 02 - Santa Ana.
		var miselectM=$("#lstMunicipioM");
	    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
		miselectM.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
  		    departamento="02";
			$.post("includes/cargar_municipio.php", { departamento: departamento },
			    function(data){
			    miselectM.empty();
				for (var i=0; i<data.length; i++) {
				    if(i== 9){
						miselectM.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselectM.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
				}
			}, "json");			
	});
// Carga la INformación de Tabla Departamento
	$(document).ready(function()
	{
		// Parametros para el lstmuncipio.
	$("#lstDepartamentoM").change(function () {
	    	    var miselect=$("#lstMunicipioM");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstDepartamentoM option:selected").each(function () {
				elegido=$(this).val();
				departamento=$("#lstDepartamentoM").val();
				$.post("includes/cargar_municipio.php", { departamento: departamento },
				       function(data){
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");			
	    });
	});
	});
// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			// CARGAR TIPO DE VIVIENDA
			var miselect=$("#LstTipoVivienda");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_tipo_de_vivienda.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
					if(i== 9){
						miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");

			// CARGAR CANTÓN
			var miselect1=$("#lstCanton");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect1.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_canton.php",
				function(data) {
					miselect1.empty();
					for (var i=0; i<data.length; i++) {
					if(i== 6){
						miselect1.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect1.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");

			// CARGAR CASERIO
			var miselect2=$("#lstCaserio");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect2.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_caserio.php",
				function(data) {
					miselect2.empty();
					for (var i=0; i<data.length; i++) {
					if(i== 9){
						miselect2.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect2.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");
			// CARGAR ABASTYECIMIENTO
			var miselect3=$("#lstAbastecimientoAgua");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect3.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_abastecimiento.php",
				function(data) {
					miselect3.empty();
					for (var i=0; i<data.length; i++) {
					if(i== 1){
						miselect3.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect3.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");
	});
	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstEtnia");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_etnia.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
// FUNCIONES PARA VER LOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS.
	function ElSalvador(url_data, Condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito){
		$.ajax({ 
			url: url_data, 
			type: 'GET', 
			dataType: 'json', 
			data: '&NumeroCondicion='+Condicion+"&CodigoDepartamento="+CodigoDepartamento+"&CodigoMunicipio="+CodigoMunicipio+"&CodigoDistrito="+CodigoDistrito,
			success: function(data) 
			{ 
				switch (Condicion) {
					case 1:
						var BuscarVariable = CodigoDepartamento;
						var select = $('#lstDepartamentoPN'); 										
						break;
					case 2:
						var BuscarVariable = CodigoMunicipio;
						var select = $('#lstMunicipioPN'); 										
						break;
					case 3:
						var BuscarVariable = CodigoDistrito;
						var select = $('#lstDistritoPN'); 										
						break;
				}
				//
				select.empty(); // Limpia el select 
				$.each(data, function(index, elsalvador)
				{
					if(elsalvador.codigo == BuscarVariable){
					//if(elsalvador.codigo_distrito == CodigoDistrito){
						select.append('<option value=' + elsalvador.codigo+ ' selected>' + elsalvador.descripcion + '</option>');
					}else{	
						select.append('<option value=' + elsalvador.codigo + '>' + elsalvador.descripcion + '</option>');
					}
					
				});
			}, 
			error: function() {
				console.log('Error al cargar...');
			}});
	}