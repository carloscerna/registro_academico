/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////PARA LA MATRICULA. /////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Carga la INformación de Tabla Año Lectivo.
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
			var miselect=$("#lstEstadoFamiliar");
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
			var miselect=$("#lstActividadEconomica");
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
 	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstZonaResidencia");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_zona_residencia.php",
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
		var miselect=$("#lstDepartamento");
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
		var miselectM=$("#lstMunicipio");
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
	});
// INFORMACION CARGAR EN DATOS DE LOS RESPONSABLES, PADRE, MADRE O ENCARGADO.
// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstNacionalidadP");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-nacionalidad.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
  	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstEstadoFamiliarP");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-familiar.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
					if(i== 7){
						miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");
	});
 	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstZonaResidenciaP");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_zona_residencia.php",
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
		var miselect=$("#lstDepartamentoP");
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
		var miselectP=$("#lstMunicipioP");
	    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
		miselectP.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
  		    departamento="02";
			$.post("includes/cargar_municipio.php", { departamento: departamento },
			    function(data){
			    miselectP.empty();
				for (var i=0; i<data.length; i++) {
				    							if(i== 9){
						miselectP.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselectP.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
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
			var miselect=$("#lstNacionalidadM");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-nacionalidad.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
        	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstEstadoFamiliarM");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-familiar.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						if(i== 2){
						miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');
						}
						else{
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');	
						}
					}
			}, "json");
	});

        	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstZonaResidenciaM");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_zona_residencia.php",
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
	
	//
	//
	//
		// Carga la INformación encargado Otro.
	$(document).ready(function()
	{
			var miselect=$("#lstNacionalidadO");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-nacionalidad.php",
				function(data) {
					miselect.empty();
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
					}
			}, "json");
	});
        	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstEstadoFamiliarO");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar-familiar.php",
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
	});

        	// Carga la INformación de Tabla Zona Residencia
	$(document).ready(function()
	{
			var miselect=$("#lstZonaResidenciaO");
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
			$.post("includes/cargar_zona_residencia.php",
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
		var miselect=$("#lstDepartamentoO");
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
		var miselectM=$("#lstMunicipioO");
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
	$("#lstDepartamentoO").change(function () {
	    	    var miselect=$("#lstMunicipioO");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstDepartamentoO option:selected").each(function () {
				elegido=$(this).val();
				departamento=$("#lstDepartamentoO").val();
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