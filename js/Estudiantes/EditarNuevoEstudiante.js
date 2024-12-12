// id de user global
var id_ = 0;
var buscartodos = "";
var accion = 'noAccion';
var chktraslado = "no";
var chksello = "no";
var chkfirma = "no";
var chkfoto = "no";
var tableA = "";
var chkCrearArchivoPdf = "no";
// IDENTIFICAR QUE TAG INICIAN CON DISPLAY NONE.
$(document).ready(function(){
	var display =  $("#ImagenPortafolio").css("display");
		if(display!="none")
		{
			$("#ImagenPortafolio").attr("style", "display:none");
		}
		var display_1 =  $("#iframePDF").css("display");
		if(display_1!="none")
		{
			$("#iframePDF").attr("style", "display:none");
		}
		var display_2 =  $("#CargarArchivoFoto").css("display");
		if(display_2!="none")
		{
			$("#CargarArchivoFoto").attr("style", "display:none");
		}
		var display_3 =  $("#CargarArchivoFotoPN").css("display");
		if(display_3!="none")
		{
			$("#CargarArchivoFotoPN").attr("style", "display:none");
		}
		var display_4 =  $("#iframePDFPn").css("display");
		if(display_4!="none")
		{
			$("#iframePDFPn").attr("style", "display:none");
		}
});
$(function(){ // INICIO DEL FUNCTION.
	// Escribir la fecha actual.
		var now = new Date();                
		var day = ("0" + now.getDate()).slice(-2);
		var month = ("0" + (now.getMonth() + 1)).slice(-2);
		var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
	//	
		var day_M = ("20");
		var today_M = now.getFullYear()+"-"+(month)+"-"+(day_M) ;
		//alert(today);
		$('#txtfechanacimiento').val(today);
		$('#txtfechanacimientop').val(today);
		$('#txtfechanacimientom').val(today);
		$('#txtfechanacimientoo').val(today);
		$('#txtfechanacimientoo').val(today);
		$('#txtfechaMatricula').val(today_M);
	///////////////////////////////////////////////////////////////////////////////
	// FUNCION QUE CARGA LA TABLA COMPLETA CON LOS REGISTROS
	///////////////////////////////////////////////////////////////////////////////
		$(document).ready(function(){
			if($("#accion").val() == "EditarRegistro"){
				// Variables Principales.
				id_ = $("#id_user").val();
				accion = $("#accion").val();
				// OCULTAR TAB BITACORA MATRICULA
				//$("#matricular-tab").hide();
				// cambiar texto de label y enlace.
				$("label[for='txtEdicionNuevo']").text("Edición");
				$("label[for='iEdicionNuevo']").text("Edición");
				// Dejar Año lectivo, Modalidad y Grado Sección Turno. con value = 00.
				$("#lstannlectivo").val("00");
				var miselect_o1=$("#lstmodalidad");
				miselect_o1.append('<option value="00" selected>...</option>');             
				var miselect_g1=$("#lstgradoseccion");
				miselect_g1.append('<option value="00" selected>...</option>');
				//
				$("#CargarArchivoFoto").css("display","block");
				$("#fileup").attr("disabled",false);		// Botón Subir Imagen Portafolio
				//
				$("#CargarArchivoFotoPN").css("display","block");
				$("#fileupPN").attr("disabled",false);		// Botón Subir Imagen Portafolio
				listar();
				VerPortafolio();	
			}
			if($("#accion").val() == "AgregarNuevoEstudiante"){
				NuevoRegistro();
				// Variables accion para guardar datos.
				accion = $("#accion").val();
				// OCULTAR TAB BITACORA MATRICULA
				$("#bitacora-tab").hide();
				$("#digitalizacion-tab").hide();
				// cambiar texto de label y enlace.
				$("label[for='txtEdicionNuevo']").text("Nuevo Registro");
				$("label[for='iEdicionNuevo']").text("Nuevo");
				// DESACTIVAR BOTONES O ACTIV AR
				$("#goImprimirPortada").prop("disabled","true");
				//
				$("#CargarArchivoFoto").css("display","none");
				$("#fileup").attr("disabled",true);		// Botón Subir Imagen Portafolio
				//
				$("#CargarArchivoFotoPN").css("display","none");
				$("#fileupPN").attr("disabled",true);		// Botón Subir Imagen Portafolio
			}				
		});
	//////////////////////////////////////////////////////////////////////////////////
	/* INICIO DE LA FUNCION PARA NUEVO REGISTRO */
	//////////////////////////////////////////////////////////////////////////////////
	var NuevoRegistro = function(){
		//alert($("#accion").val());
		
		// FOTO DEL ALUMNO.
			$(".card-img-top").attr("src", "../registro_academico/img/avatar_masculino.png");		
		// IMAGEN PARTIDA DE NACIMIENTO.		
			$(".card-img-top-PN").attr("src", "../registro_academico/img/NoDisponible.jpg");
		//
			$("#CargarArchivoFoto").css("display","none");
		//
			$("#CargarArchivoFotoPN").css("display","none");
	};
	//////////////////////////////////////////////////////////////////////////////////
	/* INICIO DE LA FUNCION PARA MOSTRAR LOS DATOS DEL ALUMNO */
	//////////////////////////////////////////////////////////////////////////////////
		var listar = function(){
			// DETARMINAR QUE SE VA EJECUTAR.	
				$.post("includes/cargar_datos_alumnos.php",  { id_x: id_ },
					function(data){
					// Cargar valores a los objetos Llenar el formulario con los datos del registro seleccionado.
					// Modificar label en la tabs-8.
						$("label[for='NombreUser']").text(data[0].nombre_completo + ' ' + data[0].apellido_paterno + ' ' + data[0].apellido_materno);
					// FOTO DEL ALUMNO.
						if(data[0].url_foto == "foto_no_disponible.jpg")
						{
							if(data[0].codigo_genero == "01"){
								$(".card-img-top").attr("src", "../registro_academico/img/avatar_masculino.png");
							}else{
								$(".card-img-top").attr("src", "../registro_academico/img/avatar_femenino.png");
							}
						}else{
							$(".card-img-top").attr("src", "../registro_academico/img/fotos/" + data[0].codigo_institucion + "/" + data[0].url_foto);	
						}
					// IMAGEN PARTIDA DE NACIMIENTO.
					let text = data[0].url_pn;
					const myExtension = text.split(".");
					ruta_imagen = "../registro_academico/img/Pn/" + data[0].url_pn;
						if(data[0].url_pn == "foto_no_disponible.jpg")
						{
							$(".card-img-top-PN").attr("src", "../registro_academico/img/NoDisponible.jpg");	
						}else{
							//alert(myExtension[1]);
							if(myExtension[1] == "pdf"){
								$('#iframePDFPn').attr('src',ruta_imagen)
								$("#iframePDFPn").css("display","block");		// Botón Ver
								$(".card-img-top-PN").css("display","none");
							}else{
								$(".card-img-top-PN").attr("src", ruta_imagen);	
								$(".card-img-top-PN").css("display","block");		// Botón Ver
								$("#iframePDFPn").css("display","none");
							}
							
						}				
					// datos para el card TITLE - INFORMACIÓN GENERAL
						$('#txtcodigo').val(id_);
						$('#txtnombres').val(data[0].nombre_completo);
						$('#apellido_materno').val(data[0].apellido_materno);
						$('#apellido_paterno').val(data[0].apellido_paterno);
						$('#direccion_alumno').val(data[0].direccion_alumno);
						$('#telresidencia').val(data[0].telefono_residencia);
						$('#telcelular').val(data[0].telefono_celular);
						$('#nie').val(data[0].codigo_nie);
						$('#email_alumno').val(data[0].email);
						$('#medicamento').val(data[0].medicamento);                                    
						$('#txtfechanacimiento').val(data[0].fecha_nacimiento);
						$('#dui').val(data[0].dui);
						$('#pasaporte_otro').val(data[0].pasaporte);
						$('#lstNacionalidadE').val(data[0].codigo_nacionalidad);
						$('#lstRetornado').val(data[0].retornado);
					// Modificar el Card del Title
					var nombres = data[0].nombre_completo + " " + data[0].apellido_paterno + " " + data[0].apellido_materno + " - " + " NIE: " + data[0].codigo_nie + " - Id: " + id_;
					$("label[for='LblNombre']").text(nombres);
						// Partida de Nacimiento Si o No.
						if (data[0].partida_nacimiento == "1") {
							$("#pn_boolean_si").prop("checked", true);
						}
						if (data[0].partida_nacimiento == "0") {
							$("#pn_boolean_no").prop("checked", true);
						}
						// Nombre del Archivo de la Partida Nacimiento.
						$("label[for='Pn']").text(data[0].url_pn);
						$('#txtedad').val(data[0].edad);
						$('#edad_enviar').val(data[0].edad);
						$('#lstPnPosee').val(data[0].posee_pn);
						$('#lstPnPresenta').val(data[0].presenta_pn);
						$('#numero_pn').val(data[0].pn_numero);
						$('#folio_pn').val(data[0].pn_folio);
						$('#tomo_pn').val(data[0].pn_tomo);
						$('#libro_pn').val(data[0].pn_libro);
						// llenar select.
						condicion = 1; 	// DEPARTAMENTOS.
						CodigoDepartamento = data[0].codigo_departamento_pn;
						var selectDepartamento = $('#lstDepartamentoPN').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
						// llenar select.
						condicion = 2; 	// MUNICIPIOS.
						CodigoMunicipio = data[0].codigo_municipio_pn;
						var selectMunicipio = $('#lstMunicipioPN').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
					// llenar select
						condicion = 3; 	// DISTRITOS.
						CodigoDistrito = data[0].codigo_distrito_pn;
						var selectDistrito = $('#lstDistritoPN').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
						/// Seleccionar genero de la base de datos guardado.
						$('#lstgenero').val(data[0].codigo_genero);
						$('#lstEtnia').val(data[0].codigo_etnia);
						$('#lstEstadoCivil').val(data[0].codigo_estado_civil);
						$('#lstEstatus').val(data[0].codigo_estatus);
						$('#LstEstadoFamiliar').val(data[0].codigo_estado_familiar);
						$('#LstActividadEconomica').val(data[0].codigo_actividad_economica);
						$('#lstTipoDiscapacidad').val(data[0].codigo_tipo_discapacidad);
						$('#lstServicioApoyoEducativo').val(data[0].codigo_servicio_apoyo_educativo);
						$('#lstDiagnostico').val(data[0].codigo_diagnostico);
						//
						$('#lstEmbarazada').val(data[0].embarazada);
						// residencia
						$('#lstZonaResidencia').val(data[0].codigo_zona_residencia);
						$('#email_alumno').val(data[0].direccion_email);
						$('#CantidadHijos').val(data[0].cantidad_hijos);
						//
						$('#LstTipoVivienda').val(data[0].codigo_tipo_vivienda);
						$('#lstCaserio').val(data[0].codigo_caserio);
						$('#lstServicioEnergia').val(data[0].servicio_energia);
						$('#lstRecoleccionBasura').val(data[0].recoleccion_basura);
						$('#lstAbastecimientoAgua').val(data[0].codigo_abastecimiento);							
						// llenar select.
						condicion = 1; 	// DEPARTAMENTOS.
						CodigoDepartamento = data[0].codigo_departamento;
						var selectDepartamento = $('#lstDepartamento').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
						// llenar select.
						condicion = 2; 	// MUNICIPIOS.
						CodigoMunicipio = data[0].codigo_municipio;
						var selectMunicipio = $('#lstMunicipio').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
						// llenar select
						condicion = 3; 	// DISTRITOS.
						CodigoDistrito = data[0].codigo_distrito;
						var selectDistrito = $('#lstDistrito').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
						//	llenar select
						condicion = 4;		// Cantón.
						var selectCanton = $('#lstCanton').attr('name'); 
						ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectCanton);
						/* **********************************************************************************************************************************/	
						//
							var letraMayuscula = ["P","M","O"]; var num = 1;
							var letraMinuscula = ["p","m","o"];
							for (let index = 0; index <= letraMayuscula.length; index++) {
								/* DATOS DEL RESPONSABLE - PADRE, MADRE U OTRO.*/
									$('#txtide'+letraMinuscula[index]).val(data[num].id_alumno_encargado);
									$('#nombre'+letraMinuscula[index]).val(data[num].nombres);
									$('#lugar'+letraMinuscula[index]).val(data[num].lugar_trabajo);
									$('#po'+letraMinuscula[index]).val(data[num].profesion);
									$('#dui'+letraMinuscula[index]).val(data[num].dui);
									$('#telefono'+letraMinuscula[index]).val(data[num].telefono);
									$('#direccion'+letraMinuscula[index]).val(data[num].direccion);
									$('#txtfechanacimiento'+letraMinuscula[index]).val(data[num].fecha_nacimiento);
									////
									$('#lstNacionalidad'+letraMayuscula[index]).val(data[num].codigo_nacionalidad);
									$('#lstEstadoFamiliar'+letraMayuscula[index]).val(data[num].codigo_familiar);
									$('#lstZonaResidencia'+letraMayuscula[index]).val(data[num].codigo_zona); 					
									// llenar select.
									condicion = 1; 	// DEPARTAMENTOS.
									CodigoDepartamento = data[num].codigo_departamento;
									var selectDepartamento = $('#lstDepartamento'+letraMayuscula[index]).attr('name'); 
									//console.log("Código Departamento: "+data[num].codigo_departamento)
									ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDepartamento);
									// llenar select.
									condicion = 2; 	// MUNICIPIOS.
									CodigoMunicipio = data[num].codigo_municipio;
									var selectMunicipio = $('#lstMunicipio'+letraMayuscula[index]).attr('name'); 
									//console.log("Código Municipio: "+data[num].codigo_municipio)
									ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectMunicipio);
									// llenar select
									condicion = 3; 	// DISTRITOS.
									CodigoDistrito = data[num].codigo_distrito;
									var selectDistrito = $('#lstDistrito'+letraMayuscula[index]).attr('name'); 
									//console.log("Código Distrito: " + data[num].codigo_distrito)
									ElSalvador(url_data, condicion, CodigoDepartamento, CodigoMunicipio, CodigoDistrito, selectDistrito);
									// chekear responsable.
										if (data[index].encargado_bollean == "1") {
												$("#encargado"+letraMinuscula[num]).prop("checked", true);
										}								
									// 
										num++;
							}
					/* LLENAR LA TABLA MATRICULA*/		
						listarMatriculaAlumno();
					/* FINAL DEL DATA QUE BUSCAR EL REGISTRO*/		
					}, "json");
		}; /* FINAL DE LA FUNCION LISTA(); */

	///////////////////////////////////////////////////////////////////////////////
	//	FUNCION LISTAR BUSQUEDA DE LOS REGISTROS
	///////////////////////////////////////////////////////////////////////////////
	var listarMatriculaAlumno = function(){
	// Varaible de Entornos.php
		buscartodos = "BuscarTodos";
	// Menu contextual"
		menuContextual = 	'<div class="dropdown">' +
								'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">...</button>' +
								'<div class="dropdown-menu">' +
									' <a class="imprimir-notas dropdown-item" href="#">Boleta de Calificación</a>' + 
									' <a class="imprimir-constancia-conducta dropdown-item" href="#">Constancia</a>' + 
									' <a class="imprimir-constancia-paes dropdown-item" href="#">PAES</a>' +
									' <a class="imprimir-titulo-tramite dropdown-item" href="#">Título en Trámite</a>' +
									' <a class="eliminar-matricula dropdown-item" href="#">Eliminar Matricula</a>' + 
								'</div>' +
							'</div>';
				// Tabla que contrendrá los registros.
				tableA = jQuery("#listadoMatricula").DataTable({
					"responsive": true,
					"lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
					"destroy": true,
					"ajax":{
						method:"POST",
						url:"php_libs/soporte/EstudiantesBuscarMatricula.php",
						data: {"accion_buscar": buscartodos, "id_x": id_}
					},
					"columns":[
						{
							data: null,
							defaultContent: menuContextual,
							orderable: false
						},      // BOTON DE ACCIÓN PARA LA MATRICULA.
						{"data":"id_alumno_matricula"}, // ID MATRICULA.
						{"data":"todos"},
						{"data":"fecha_ingreso"},
						{"data":"retirado",
							render: function(data, type, row){
								if(data == 'false'){
									return "<span class='badge badge-pill badge-danger'>Si</span>";
								}else{
									return "<span class='badge badge-pill badge-info'>No</span>";
								}
							}
						},
						{"data":"nombre_todos"},
					],
					// LLama a los diferentes mensajes que están en español.
					"language": idioma_espanol
			});
				obtener_data_editar("#listadoMatricula tbody", tableA);
		};
	///////////////////////////////////////////////////////////////////////////////
	// CONFIGURACIÓN DEL IDIOMA AL ESPAÑOL.
	///////////////////////////////////////////////////////////////////////////////
	var idioma_espanol = {
				"sProcessing":     "Procesando...",
				"sLengthMenu":     "Mostrar _MENU_ registros",
				"sZeroRecords":    "No se encontraron resultados",
				"sEmptyTable":     "Ningún dato disponible en esta tabla",
				"sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
				"sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
				"sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
				"sInfoPostFix":    "",
				"sSearch":         "Buscar:",
				"sUrl":            "",
				"sInfoThousands":  ",",
				"sLoadingRecords": "Cargando...",
				"oPaginate": {
				"sFirst":    "Primero",
				"sLast":     "Último",
				"sNext":     "Siguiente",
				"sPrevious": "Anterior"
				},
				"oAria": {
					"sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
					"sSortDescending": ": Activar para ordenar la columna de manera descendente"
				}
			};	   
	var obtener_data_editar = function(tbody, tableA){
	///////////////////////////////////////////////////////////////////////////////
	//	FUNCION que al dar clic buscar el registro para posterior mente abri una
	// ventana modal. BOLETA DE NOTAS
	///////////////////////////////////////////////////////////////////////////////	  
		$(tbody).on("click","a.imprimir-notas",function(){
			var data = tableA.row($(this).parents("tr")).data();
			console.log(data); console.log(data[0]);
				// pasar el valor a variables.
			var id_alumno = id_;
			var txtcodigomatricula = data[0];
			var idtodos_ok = data[16];
			var print_uno = 'yes';
			var chkCrearArchivoPdf = "no";
			//variables checked                        
			if($('#chktraslado').is(":checked")) {chktraslado = 'yes';}else{chktraslado = 'no';}                        
			if($('#chkfirma').is(":checked")) {chkfirma = 'yes';}else {chkfirma = 'no';}
			if($('#chksello').is(":checked")) {chksello = 'yes';}else {chksello = 'no';}
			if($('#chkfoto').is(":checked")) {chkfoto = 'yes';}else {chkfoto = 'no';}         
			if($('#chkCrearArchivoPdf').is(":checked")) {chkCrearArchivoPdf = "si";}                                                                                           
			// construir la variable con el url.
				varenviar = "/registro_academico/php_libs/reportes/boleta_de_notas.php?todos="+idtodos_ok+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno+"&chkfoto="+chkfoto+"&print_uno="+print_uno+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
			// Ejecutar la función
				AbrirVentana(varenviar);
		});
	///////////////////////////////////////////////////////////////////////////////
	//	FUNCION que al dar clic buscar el registro para posterior mente abri una
	// ventana modal. CONSTANCIA DE ESTUDIO Y CONDUCTA.
	///////////////////////////////////////////////////////////////////////////////
		$(tbody).on("click","a.imprimir-constancia-conducta",function(){
			var data = tableA.row($(this).parents("tr")).data();
			console.log(data); console.log(data[0]);
		// pasar el valor a variables.
			var id_alumno = id_;
			var lstconducta = $('#lstconducta').val();
			var lstestudio = $('#lstestudia').val();
			var txttraslado = $('#txttraslado').val();
			var txtcodigomatricula = data[0];
			var idtodos_ok = data[16];
			//variables checked                        
			if($('#chktraslado').is(":checked")) {chktraslado = 'yes';}else{chktraslado = 'no';}                        
			if($('#chkfirma').is(":checked")) {chkfirma = 'yes';}else {chkfirma = 'no';}
			if($('#chksello').is(":checked")) {chksello = 'yes';}else {chksello = 'no';}
			if($('#chkfoto').is(":checked")) {chkfoto = 'yes';}else {chkfoto = 'no';}                                                       
			if($('#chkCrearArchivoPdf').is(":checked")) {chkCrearArchivoPdf = "si";}    
			// construir la variable con el url.
				$url_ruta = "/registro_academico/php_libs/reportes/Estudiante/EstudioConducta.php?todos="+idtodos_ok+"&lstconducta="+lstconducta+"&lstestudia="+lstestudio+"&chktraslado="+chktraslado+"&txttraslado="+txttraslado+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
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
                        data: "todos="+ idtodos_ok + "&id=" + Math.random()+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno+"&chkfoto="+chkfoto+"&chkCrearArchivoPdf="+chkCrearArchivoPdf,
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
						varenviar = "/registro_academico/php_libs/reportes/Estudiante/EstudioConducta.php?todos="+idtodos_ok+"&lstconducta="+lstconducta+"&lstestudia="+lstestudio+"&chktraslado="+chktraslado+"&txttraslado="+txttraslado+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno+"&chkCrearArchivoPdf="+chkCrearArchivoPdf;
					// Ejecutar la función
							AbrirVentana(varenviar);
                }                                                   
		});
	///////////////////////////////////////////////////////////////////////////////
	//	FUNCION que al dar clic buscar el registro para posterior mente abri una
	// ventana modal. IMPRIMIR CONSTANCIA PAES
	///////////////////////////////////////////////////////////////////////////////	  
		$(tbody).on("click","a.imprimir-constancia-paes",function(){
			var data = tableA.row($(this).parents("tr")).data();
			console.log(data); console.log(data[0]);
			
			var id_alumno = id_;
			var lstconducta = $('#lstconducta').val();
			var lstestudio = $('#lstestudia').val();
			var txttraslado = $('#txttraslado').val();
			var txtcodigomatricula = data[0];
			var idtodos_ok = data[16];
			//variables checked                        
			if($('#chktraslado').is(":checked")) {chktraslado = 'yes';}else{chktraslado = 'no';}                        
			if($('#chkfirma').is(":checked")) {chkfirma = 'yes';}else {chkfirma = 'no';}
			if($('#chksello').is(":checked")) {chksello = 'yes';}else {chksello = 'no';}
			if($('#chkfoto').is(":checked")) {chkfoto = 'yes';}else {chkfoto = 'no';}
			if($('#chkCrearArchivoPdf').is(":checked")) {chkCrearArchivoPdf = "si";}                                                       
			// construir la variable con el url.
				varenviar = "/registro_academico/php_libs/reportes/constancia_paes.php?todos="+idtodos_ok+"&lstconducta="+lstconducta+"&lstestudia="+lstestudio+"&chktraslado="+chktraslado+"&txttraslado="+txttraslado+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno;
			// Ejecutar la función
			AbrirVentana(varenviar);
		});
	///////////////////////////////////////////////////////////////////////////////
	//	FUNCION que al dar clic buscar el registro para posterior mente abri una
	// ventana modal. CONSTANCIA DE TITULO EN TRÁMITE.
	///////////////////////////////////////////////////////////////////////////////
	$(tbody).on("click","a.imprimir-titulo-tramite",function(){
		var data = tableA.row($(this).parents("tr")).data();
		console.log(data); console.log(data[0]);
	// pasar el valor a variables.
		var id_alumno = id_;
		var lstconducta = $('#lstconducta').val();
		var lstestudio = $('#lstestudia').val();
		var txttraslado = $('#txttraslado').val();
		var txtcodigomatricula = data[0];
		var idtodos_ok = data[16];
		var codigo_modalidad = data[1];
		var codigo_grado = data[2];
		var mensaje = "Registro Encontrado.";	
		var respuestaOK = true;
		//variables checked                        
		if($('#chktraslado').is(":checked")) {chktraslado = 'yes';}else{chktraslado = 'no';}                        
		if($('#chkfirma').is(":checked")) {chkfirma = 'yes';}else {chkfirma = 'no';}
		if($('#chksello').is(":checked")) {chksello = 'yes';}else {chksello = 'no';}
		if($('#chkfoto').is(":checked")) {chkfoto = 'yes';}else {chkfoto = 'no';}                                                       

		// Validar
		/* if(codigo_modalidad == '06' && codigo_grado == '11' && codigo_modalidad == '11'){

		}else if(codigo_modalidad == '09' && codigo_grado == '12'){

		}else{
			respuestaOK = false;
			mensaje = "El Estudiante no es de 2.º o 3.º Año de Educación Media.";
			toastr["error"](mensaje, "Sistema");
		} */
		// Enviar.
		if(respuestaOK == true){
			varenviar = "/registro_academico/php_libs/reportes/estudiante_tramite_titulo.php?todos="+idtodos_ok+"&lstconducta="+lstconducta+"&lstestudia="+lstestudio+"&chktraslado="+chktraslado+"&txttraslado="+txttraslado+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno;
			toastr["info"](mensaje, "Sistema");
			AbrirVentana2(varenviar);
		}		
	});
	///////////////////////////////////////////////////////////////////////////////
	//	FUNCION que al dar clic buscar el registro para posterior mente abri una
	// ventana modal. IMPRIMIR CONSTANCIA PAES
	///////////////////////////////////////////////////////////////////////////////	  
		$(tbody).on("click","a.eliminar-matricula",function(){
			var data = tableA.row($(this).parents("tr")).data();
			console.log(data); console.log(data[0]);
			
			accion = "eliminarMatricula";
			var id_alumno = id_;
			var txtcodigomatricula = data[0];

			//	ENVIAR MENSAJE CON SWEETALERT 2, PARA CONFIRMAR SI ELIMINA EL REGISTRO.
			const swalWithBootstrapButtons = Swal.mixin({
				customClass: {
				confirmButton: 'btn btn-success',
				cancelButton: 'btn btn-danger'
				},
				buttonsStyling: false
			})

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
				///////////////////////////////////////////////////////////////			
				// Inicio del Ajax. guarda o Actualiza los datos del Formualrio.
				///////////////////////////////////////////////////////////////
				$.ajax({
					beforeSend: function(){
						
					},
					cache: false,
					type: "POST",
					dataType: "json",
					url:"php_libs/soporte/Estudiante/NuevoEditarEstudiante.php",
					data:"&id=" + Math.random() + "&codigo_alumno=" + id_alumno + "&codigo_matricula=" + txtcodigomatricula + "&accion=" + accion,
					success: function(response){
						// Validar mensaje de error
						if(response.respuesta === false){
							alert(response.mensaje);
						}
						else{
							// si es exitosa la operación. Validar mensajes.
										if (response.mensaje == 'Matricula Borrada') {
												toastr.success("Registro Eliminado.");
												$('#listaMatricula').empty();
												listarMatriculaAlumno();
										}
								}               
					},
				}); // ajax para eliminar matricula.
				//////////////////////////////////////
				} else if (
				/* Read more about handling dismissals below */
				result.dismiss === Swal.DismissReason.cancel
				) {
				swalWithBootstrapButtons.fire(
					'Cancelar',
					'Su Archivo no ha sido Eliminado :)',
					'error'
				)
				}
			})
		
		});
	}; // Funcion principal dentro del DataTable.
	///////////////////////////////////////////////////////
	// Validar Formulario, para posteriormente Guardar o Modificarlo.
	//////////////////////////////////////////////////////
		$('#formUsers').validate({
			ignore:"",
			rules:{
					txtnombres: {required: true, minlength: 4},
					apellido_materno: {required: true, minlength: 2},
					lstannlectivo: {required: true},
					lstmodalidad: {required: true},
					lstgradoseccion: {required: true},
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
				var str = $('#formUsers').serialize();
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
						url:"php_libs/soporte/Estudiante/NuevoEditarEstudiante.php",
						data:str + "&id=" + Math.random(),
						success: function(response){
							// Validar mensaje de error
							if(response.respuesta === false){
								alert(response.mensaje);
							}
							else{
								// si es exitosa la operación. Validar mensajes.
											if (response.mensaje == 'Si Update') {
													toastr.success("Registro Actualizado.");
													window.location.href = 'estudiantes.php';
											}
											if (response.mensaje == 'Si Save') {
													toastr.success("Registro Guardado.");
													window.location.href = 'estudiantes.php';
											}                                        
											// Cerrar diálogo, Ocultar Imagen gif, vaciar lista.
													// Agregar el valor al textarea traslado.
													$('#txttraslado').val('El cual solicita traslado al Centro Educativo que usted dirige por cambio de domicilio.');
													// desactivar checkbox, traslado, firma, sello, certificado, pn.
													$("#chktraslado").prop("checked", false);
													$("#chkfirma").prop("checked", false);
													$("#chksello").prop("checked", false);
													$("#chkcertificado").prop("checked", false);
													$("#chkpn").prop("checked", false);
													$("#chkfoto").prop("checked", false);
													$("label[for='lbl_edad_y_mes']").text('');
									}               
						},
					});
				},
	});
	////////////////////////////////////////////////////
	////// Imprimir Partida de Nacimiento.
	////////////////////////////////////////////////////
		$("#goImprimirPN").click(function() {     
		// construir la variable con el url.
			var nombre_archivo = $("label[for='Pn']").text();
			var id_alumno = $("#id_user").val();
			varenviar = "/registro_academico/php_libs/reportes/imprimir_partida_nacimiento.php?nombre_archivo="+nombre_archivo+"&codigo_alumno="+id_alumno;
		// Ejecutar la función
			AbrirVentana2(varenviar);
		});
	////////////////////////////////////////////////////
	////// SUBMIT para el botón buscar otro estudiante.
	////////////////////////////////////////////////////
	$("#goBuscar").click(function() {     
		window.location.href = 'Estudiantes.php';
	});
	////////////////////////////////////////////////////
	////// SUBMIT para el botón guardar
	////////////////////////////////////////////////////
	$("#goGuardar").click(function() {     
		$("#formUsers").submit();
	});
}); // fin de la funcion principal ************************************/

// Pasar foco cuando seleccionar un encargado.
function PasarFoco()
   {
       $('#nombrep').focus();
   }
   function PasarFocoPadre()
   {
	$('#txtfechanacimientop').focus();
   }
   function PasarFocoMadre()
   {
	$('#txtfechanacimientom').focus();
   }
   function PasarFocoOtros()
   {
	$('#txtfechanacimientoo').focus();
   }
///////////////////////////////////////////////////////////
// Convertir a mayúsculas cuando abandone el input.
////////////////////////////////////////////////////////////
   function conMayusculas(field)
   {
        field.value = field.value.toUpperCase();
   }
///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
//  ABRE LA VENTANA EN EL MISMO NAVEGADOR.
///////////////////////////////////////////////////////////////////////////////	  
function AbrirVentana(url)
{
	window.open(url, '_blank');
	// Documentación.
	// https://informaticapc.com/tutorial-javascript/ventana-del-navegador-web.php
    //window.open(url,"VentanaInformes","height=500,width=700,left=300,location=yes,menubar=no,resizable=no,scrollbars=yes,status=no,titlebar=yes,top=100" );
    return false;
}
///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
//  ABRE LA VENTANA EN EL MISMO NAVEGADOR.
///////////////////////////////////////////////////////////////////////////////	  
function AbrirVentana2(url)
{
	//window.open(url, '_blank');
	// Documentación.
	// https://informaticapc.com/tutorial-javascript/ventana-del-navegador-web.php
    window.open(url,"VentanaInformes","height=500,width=700,left=300,location=yes,menubar=no,resizable=no,scrollbars=yes,status=no,titlebar=yes,top=100" );
    return false;
}