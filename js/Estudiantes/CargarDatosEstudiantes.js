/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////PARA LA MATRICULA. /////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Carga Datos General del Estudiante cuando sea Nuevo o Edición.
// Variables publicas.
var CodigoNacionalidad = '54'
var CodigoDepartamento = "02";
var CodigoMunicipio = "02";
var CodigoDistrito = "01";
var CodigoCanton = '07';
var url_data = "includes/cargar_elsalvador.php";
var condicion = 0;

//
//
/*
$(document).ready(function()
{
	if($("#accion").val() == "AgregarNuevoEstudiante"){
			// Seleccionar los SELECT de la Matricula.
			var html_ann = $("#valor_ann").val();
			var html_modalidad = $("#valor_modalidad").val();
			var html_gst = $("#valor_gst").val();
			var ver_ann_lectivo = "si";
			
			var miselect=$("#lstannlectivo");		
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... 		
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
*/
/*
$(document).ready(function()
{
	if($("#accion").val() == "AgregarNuevoEstudiante"){
			// Seleccionar los SELECT de la Matricula.
			var html_ann = $("#valor_ann").val();
			var html_modalidad = $("#valor_modalidad").val();
			var html_gst = $("#valor_gst").val();
			// MODALIDAD.
			var miselect_modalidad=$("#lstmodalidad");		
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... 		
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
*/
/*
$(document).ready(function()
{
	if($("#accion").val() == "AgregarNuevoEstudiante"){
			// Seleccionar los SELECT de la Matricula.
			var html_ann = $("#valor_ann").val();
			var html_modalidad = $("#valor_modalidad").val();
			var html_gst = $("#valor_gst").val();
			// GRADO, SECCIÓN Y TURNO..
			var miselect_gst=$("#lstgradoseccion");		
			/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... 		
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
//////*////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// EN EL CASO QUE SOLO SE HA EDITAR.
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        				
/*
$(document).ready(function()
{
	if($("#accion").val() != "AgregarNuevoEstudiante"){
		var miselect=$("#lstannlectivo");		
		var ver_ann_lectivo = "si";
		/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... 		
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
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... 
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
       /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... 
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
*/
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
// FUNCION PARA LLAMAR A LAS DIFENTES TABLAS CON SUS RESPECTIVOS DATOS PARA RELLENAR LOS SELECT.
$(document).ready(function(){
	url_data = "includes/cargarCatalogosDatos.php";
	// llenar select. Nacionalidad.
		var nacionalidad = $('#lstNacionalidadE').attr('name'); 
		condicion = 1;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, nacionalidad, CodigoNacionalidad);
	// llenar select. Sexo o Genero.
		var genero = $('#lstgenero').attr('name'); 
		condicion = 2;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, genero);
	// llenar select. Etnia.
		var etnia = $('#lstEtnia').attr('name'); 
		condicion = 3;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, etnia);
	// llenar select. Diagnostico.
		var discapacidad = $('#lstTipoDiscapacidad').attr('name'); 
		condicion = 4;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, discapacidad);
	// llenar select. Diagnostico.
		var diagnostico = $('#lstDiagnostico').attr('name'); 
		condicion = 5;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, diagnostico);
	// llenar select. Servicio Apoyo Educativo.
		var apoyoEducativo = $('#lstServicioApoyoEducativo').attr('name'); 
		condicion = 6;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, apoyoEducativo);
	// llenar select. Actividad Económica.
		var actividadEconomica = $('#LstActividadEconomica').attr('name'); 
		condicion = 7;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, actividadEconomica);
	// llenar select. Estado Familiar.
		var estadoFamiliar = $('#LstEstadoFamiliar').attr('name'); 
		condicion = 8;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, estadoFamiliar);
	// llenar select. Estado Civil.
		var estadoCivil = $('#lstEstadoCivil').attr('name'); 
		condicion = 9;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, estadoCivil);
	// llenar select. Zona Residencia. Estudiante.
		var zonaResidencia = $('#lstZonaResidencia').attr('name'); 
		condicion = 10;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, zonaResidencia);
	// llenar select. Tipo de Vivienda.
		var tipoVivienda = $('#LstTipoVivienda').attr('name'); 
		condicion = 11;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, tipoVivienda);
	// llenar select. Abastecimiento de agua.
		var abastecimientoAgua = $('#lstAbastecimientoAgua').attr('name'); 
		condicion = 12;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, abastecimientoAgua);
	// llenar select. Estatus.
		var estatus = $('#lstEstatus').attr('name'); 
		condicion = 13;	// DEPARTAMENTOS y lst.
			Catalogos(url_data, condicion, estatus);
	//
	//	INFORMACIÓN DE LOS RESPONSABLES.
	//	PADRE.
		// llenar select. Nacionalidad.
		var nacionalidad = $('#lstNacionalidadP').attr('name'); 
		condicion = 1;	// 
			Catalogos(url_data, condicion, nacionalidad, CodigoNacionalidad);
		// llenar select. Estado Familiar.
		var estadoFamiliar = $('#lstEstadoFamiliarP').attr('name'); 
		condicion = 14;	// 
			Catalogos(url_data, condicion, estadoFamiliar, '08');
		// llenar select. Zona.
		var zona = $('#lstZonaResidenciaP').attr('name'); 
		condicion = 10;	// 
			Catalogos(url_data, condicion, zona);
	//	MADRE.
		// llenar select. Nacionalidad.
		var nacionalidad = $('#lstNacionalidadM').attr('name'); 
		condicion = 1;	// 
			Catalogos(url_data, condicion, nacionalidad, CodigoNacionalidad);
		// llenar select. Estado Familiar.
		var estadoFamiliar = $('#lstEstadoFamiliarM').attr('name'); 
		condicion = 14;	// 
			Catalogos(url_data, condicion, estadoFamiliar, '03');
		// llenar select. Zona.
		var zona = $('#lstZonaResidenciaM').attr('name'); 
		condicion = 10;	// 
			Catalogos(url_data, condicion, zona);
	//	OTRO.
		// llenar select. Nacionalidad.
		var nacionalidad = $('#lstNacionalidadO').attr('name'); 
		condicion = 1;	// 
			Catalogos(url_data, condicion, nacionalidad, CodigoNacionalidad);
		// llenar select. Estado Familiar.
		var estadoFamiliar = $('#lstEstadoFamiliarO').attr('name'); 
		condicion = 14;	// 
			Catalogos(url_data, condicion, estadoFamiliar, '10');
		// llenar select. Zona.
		var zona = $('#lstZonaResidenciaO').attr('name'); 
		condicion = 10;	// 
			Catalogos(url_data, condicion, zona);
	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	//	DATOS PARA LA PARTIDA DE NACIMIENTO.
	// DESARROLLO PARA CARGA DE DATOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS. VISTA ELSALVADOR.
		url_data = "includes/cargar_elsalvador.php";
	// llenar select.
		condicion = 1;		// DEPARTAMENTOS y lst.
		var selectDepartamento = $('#lstDepartamentoPN').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
	// llenar select.
		condicion = 2; 	// MUNICIPIOS.
		var selectMunicipio = $('#lstMunicipioPN').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
	// llenar select
		condicion = 3; 	// DISTRITOS.
		var selectDistrito = $('#lstDistritoPN').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
 	//  RELLENAR RESIDENCIA DEL ESTUDIANTE.
	// llenar select.
		condicion = 1; 	// DEPARTAMENTOS.
		var selectDepartamento = $('#lstDepartamento').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
	// llenar select.
		condicion = 2; // MUNICIPIOS.
		var selectMunicipio = $('#lstMunicipio').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
	// llenar select
		condicion = 3;		// DISTRITOS.
		var selectDistrito = $('#lstDistrito').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
	//	llenar select
		condicion = 4;		// Cantón.
		var selectCanton = $('#lstCanton').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectCanton);
 	//  RELLENAR RESIDENCIA DEL PADRE.
		// llenar select.
		condicion = 1; 	// DEPARTAMENTOS.
		var selectDepartamento = $('#lstDepartamentoP').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
	// llenar select.
		condicion = 2; // MUNICIPIOS.
		var selectMunicipio = $('#lstMunicipioP').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
	// llenar select
		condicion = 3;		// DISTRITOS.
		var selectDistrito = $('#lstDistritoP').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
 	//  RELLENAR RESIDENCIA DEL MADRE.
		// llenar select.
		condicion = 1; 	// DEPARTAMENTOS.
		var selectDepartamento = $('#lstDepartamentoM').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
	// llenar select.
		condicion = 2; // MUNICIPIOS.
		var selectMunicipio = $('#lstMunicipioM').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
	// llenar select
		condicion = 3;		// DISTRITOS.
		var selectDistrito = $('#lstDistritoM').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
 	//  RELLENAR RESIDENCIA DEL OTRO.
		// llenar select.
		condicion = 1; 	// DEPARTAMENTOS.
		var selectDepartamento = $('#lstDepartamentoO').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
	// llenar select.
		condicion = 2; // MUNICIPIOS.
		var selectMunicipio = $('#lstMunicipioO').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
	// llenar select
		condicion = 3;		// DISTRITOS.
		var selectDistrito = $('#lstDistritoO').attr('name'); 
		ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
});

//	EVENTO CHANGE -- PARA LOS DATOS DEL ESTUDIANTE Y RESIDENCIA DEL ESTUDIANTE. --
//	DESARROLLO PARA CARGA DE DATOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS. VISTA ELSALVADOR.
$(document).ready(function()
{
	//	CUANDO CAMBIE EL DEPARTAMENTO.
	$("#lstDepartamentoPN").change(function () {
		$("#lstDepartamentoPN option:selected").each(function () {
			CodigoDepartamento = $(this).val(); CodigoMunicipio = "";
			//	limpiar select y rellenar.
				var selectMunicipio = $('#lstMunicipioPN').attr('name'); 
				condicion = 2;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
		});
			// buscar el distrito segun Municipio.
				CodigoMunicipio = $("#lstMunicipioPN").val();
				var selectDistrito = $('#lstDistritoPN').attr('name');  CodigoDistrito = "";
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
	});
	//	CUANDO CAMBIE EL MUNICIPIO
	$("#lstMunicipioPN").change(function () {
		$("#lstMunicipioPN option:selected").each(function () {
			CodigoDepartamento = $("#lstDepartamentoPN").val(); CodigoMunicipio = $(this).val(); CodigoDistrito = "";
			//	limpiar select y rellenar.
			var selectDistrito = $('#lstDistritoPN').attr('name'); 
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
		});
	});
	// RESIDENCIA DEL ESTUDIANTE.
	//	CUANDO CAMBIE EL DEPARTAMENTO.
	$("#lstDepartamento").change(function () {
		$("#lstDepartamento option:selected").each(function () {
			CodigoDepartamento = $(this).val(); CodigoMunicipio = "";
			//	limpiar select y rellenar.
				var selectMunicipio = $('#lstMunicipio').attr('name'); 
				condicion = 2;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
		});
			// buscar el distrito segun Municipio.
				CodigoMunicipio = $("#lstMunicipio").val();
				var selectDistrito = $('#lstDistrito').attr('name');  CodigoDistrito = "";
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
			//
				CodigoDistrito = $("#lstDistrito").val(); CodigoDistrito = $(this).val(); 
				//	limpiar select y rellenar.
				var selectCanton = $('#lstCanton').attr('name'); 
					condicion = 4;
					ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectCanton);
	});
	//	CUANDO CAMBIE EL MUNICIPIO
	$("#lstMunicipio").change(function () {
		$("#lstMunicipio option:selected").each(function () {
			CodigoDepartamento = $("#lstDepartamento").val(); CodigoMunicipio = $(this).val(); CodigoDistrito = "";
			//	limpiar select y rellenar.
			var selectDistrito = $('#lstDistrito').attr('name'); 
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
		});
			// buscar el cantón según el Distrito.
			CodigoDepartamento = $("#lstDepartamento").val();
			CodigoDistrito = $("#lstDistrito").val();
			var selectCanton = $('#lstCanton').attr('name'); 
			condicion = 4;
			ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectCanton);
	});
	//	CUANDO CAMBIE EL DISTRITO
	$("#lstDistrito").change(function () {
		$("#lstDistrito option:selected").each(function () {
			CodigoDepartamento = $("#lstDepartamento").val(); CodigoDistrito = $("#lstDistrito").val(); CodigoMunicipio = $("#lstMunicipio").val();
			//	limpiar select y rellenar.
			var selectCanton = $('#lstCanton').attr('name'); 
				condicion = 4;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectCanton);
		});
	});
	//
	// RESIDENCIA DEL padre.
	//	CUANDO CAMBIE EL DEPARTAMENTO.
	$("#lstDepartamentoP").change(function () {
		$("#lstDepartamentoP option:selected").each(function () {
			CodigoDepartamento = $(this).val(); CodigoMunicipio = "";
			//	limpiar select y rellenar.
				var selectMunicipio = $('#lstMunicipioP').attr('name'); 
				condicion = 2;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
		});
			// buscar el distrito segun Municipio.
				CodigoMunicipio = $("#lstMunicipioP").val();
				var selectDistrito = $('#lstDistritoP').attr('name');  CodigoDistrito = "";
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
	});
	//	CUANDO CAMBIE EL MUNICIPIO
	$("#lstMunicipioP").change(function () {
		$("#lstMunicipioP option:selected").each(function () {
			CodigoDepartamento = $("#lstDepartamentoP").val(); CodigoMunicipio = $(this).val(); CodigoDistrito = "";
			//	limpiar select y rellenar.
			var selectDistrito = $('#lstDistritoP').attr('name'); 
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
		});
	});
//
	// RESIDENCIA DEL Madre.
	//	CUANDO CAMBIE EL DEPARTAMENTO.
	$("#lstDepartamentoM").change(function () {
		$("#lstDepartamentoM option:selected").each(function () {
			CodigoDepartamento = $(this).val(); CodigoMunicipio = "";
			//	limpiar select y rellenar.
				var selectMunicipio = $('#lstMunicipioM').attr('name'); 
				condicion = 2;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
		});
			// buscar el distrito segun Municipio.
				CodigoMunicipio = $("#lstMunicipioM").val();
				var selectDistrito = $('#lstDistritoM').attr('name');  CodigoDistrito = "";
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
	});
	//	CUANDO CAMBIE EL MUNICIPIO
	$("#lstMunicipioM").change(function () {
		$("#lstMunicipioM option:selected").each(function () {
			CodigoDepartamento = $("#lstDepartamentoM").val(); CodigoMunicipio = $(this).val(); CodigoDistrito = "";
			//	limpiar select y rellenar.
			var selectDistrito = $('#lstDistritoM').attr('name'); 
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
		});
	});
//
	// RESIDENCIA DEL otro.
	//	CUANDO CAMBIE EL DEPARTAMENTO.
	$("#lstDepartamentoO").change(function () {
		$("#lstDepartamentoO option:selected").each(function () {
			CodigoDepartamento = $(this).val(); CodigoMunicipio = "";
			//	limpiar select y rellenar.
				var selectMunicipio = $('#lstMunicipioO').attr('name'); 
				condicion = 2;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
		});
			// buscar el distrito segun Municipio.
				CodigoMunicipio = $("#lstMunicipioO").val();
				var selectDistrito = $('#lstDistritoO').attr('name');  CodigoDistrito = "";
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
	});
	//	CUANDO CAMBIE EL MUNICIPIO
	$("#lstMunicipioO").change(function () {
		$("#lstMunicipioO option:selected").each(function () {
			CodigoDepartamento = $("#lstDepartamentoO").val(); CodigoMunicipio = $(this).val(); CodigoDistrito = "";
			//	limpiar select y rellenar.
			var selectDistrito = $('#lstDistritoO').attr('name'); 
				condicion = 3;
				ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
		});
	});
});
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// FUNCIONES.
//	
// FUNCIONES PARA VER LOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS.
function ElSalvador(url_data, Condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, select, CodigoCanton)	{
	var Oselect = $('#'+select);
	$.ajax({ 
		url: url_data, 
		type: 'GET', 
		dataType: 'json', 
		data: '&NumeroCondicion='+Condicion+"&CodigoDepartamento="+CodigoDepartamento+"&CodigoMunicipio="+CodigoMunicipio+"&CodigoDistrito="+CodigoDistrito,
		success: function(data) 
		{ 
			// NOMBRE Y VALORES DEL CAMPO DE LA TABLA.
			switch (Condicion) {
				case 1:
					var BuscarVariable = CodigoDepartamento;
					break;
				case 2:
					var BuscarVariable = CodigoMunicipio;
					break;
				case 3:
					var BuscarVariable = CodigoDistrito;
					break;
				case 4:
					var BuscarVariable = CodigoCanton;
					break;
			}
			//
			Oselect.empty(); // Limpia el select 
			$.each(data, function(index, elsalvador)
			{
				if(elsalvador.codigo.trim() == BuscarVariable){
					Oselect.append('<option value=' + elsalvador.codigo+ ' selected>' + elsalvador.descripcion + '</option>');
				}else{	
					Oselect.append('<option value=' + elsalvador.codigo + '>' + elsalvador.descripcion + '</option>');
				}
				
			});
		}, 
		error: function() {
			console.log('Error al cargar...');
		}});
}
// CATALOGOS TABLAS.
// FUNCIONES PARA VER LOS DEPARTAMENTOS, MUNICIPIOS Y DISTRITOS.
function Catalogos(url_data, Condicion, select, CodigoValor)	{
	var Oselect = $('#'+select);
	$.ajax({ 
		url: url_data, 
		type: 'GET', 
		dataType: 'json', 
		data: '&NumeroCondicion='+Condicion,
		success: function(data) 
		{ 
			// NOMBRE DEL OBJETO A RELLENAR y VARIABLE.
			BuscarVariable = CodigoValor;
			//
			Oselect.empty(); // Limpia el select 
			$.each(data, function(index, catalogos)
			{
				if(catalogos.codigo.trim() == BuscarVariable){
					Oselect.append('<option value=' + catalogos.codigo+ ' selected>' + catalogos.descripcion + '</option>');
				}else{	
					Oselect.append('<option value=' + catalogos.codigo + '>' + catalogos.descripcion + '</option>');
				}
				
			});
		}, 
		error: function() {
			console.log('Error al cargar...');
		}});
}