// id de user global
var id_ = 0;
var buscartodos = "";
var accion = 'noAccion';
var chktraslado = "no";
var chksello = "no";
var chkfirma = "no";
var chkfoto = "no";
var tableA = "";
$(function(){ // INICIO DEL FUNCTION.
            // Escribir la fecha actual.
                var now = new Date();                
                var day = ("0" + now.getDate()).slice(-2);
                var month = ("0" + (now.getMonth() + 1)).slice(-2);
                var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
				
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
			
			//$("#lstmodalidad").val("00");
			//$("#lstgradoseccion").val("00");
            listar();
            VerPortafolio();	
		}
		if($("#accion").val() == "AgregarNuevoEstudiante"){
			NuevoRegistro();
			// Variables accion para guardar datos.
			accion = $("#accion").val();
			// OCULTAR TAB BITACORA MATRICULA
			$("#bitacora-tab").hide();
			// cambiar texto de label y enlace.
			$("label[for='txtEdicionNuevo']").text("Nuevo Registro");
			$("label[for='iEdicionNuevo']").text("Nuevo");
			// DESACTIVAR BOTONES O ACTIV AR
			$("#goImprimirPortada").prop("disabled","true");
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
		$(".card-img-top-2").attr("src", "../registro_academico/img/NoDisponible.jpg");
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
					if(data[0].url_pn == "foto_no_disponible.jpg")
					{
						$(".card-img-top-2").attr("src", "../registro_academico/img/NoDisponible.jpg");	
					}else{
						$(".card-img-top-2").attr("src", "../registro_academico/img/Pn/" + data[0].url_pn);	
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
                    $('#numero_pn').val(data[0].pn_numero);
                    $('#folio_pn').val(data[0].pn_folio);
                    $('#tomo_pn').val(data[0].pn_tomo);
                    $('#libro_pn').val(data[0].pn_libro);
                    /// Seleccionar genero de la base de datos guardado.
                    //$('#').val(data[0].);
                    $('#lstgenero').val(data[0].codigo_genero);
                    $('#lstEstadoCivil').val(data[0].codigo_estado_civil);
                    $('#lstEstatus').val(data[0].codigo_estatus);
                    $('#lstEstadoFamiliar').val(data[0].codigo_estado_familiar);
                    $('#lstActividadEconomica').val(data[0].codigo_actividad_economica);
                    $('#lstTipoDiscapacidad').val(data[0].codigo_tipo_discapacidad);
					// I SELECT ACTIVIDAD ECONOMICA..
					$('#lstZonaResidencia').val(data[0].codigo_zona_residencia);
                    $('#lstServicioApoyoEducativo').val(data[0].codigo_servicio_apoyo_educativo);

							/// Seleccionar genero de la base de datos guardado.
                            var miselect_departamento=$("#lstDepartamento");
                            var codigo_departamento = data[0].codigo_departamento;
							$("#lstDepartamento").val(codigo_departamento);
							/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
							miselect_departamento.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
	                            $.post("includes/cargar_departamento.php",
                                    function(data){
										miselect_departamento.empty();
                                            for (var i=0; i<data.length; i++) {
                                                if(codigo_departamento == data[i].codigo){
                                                    miselect_departamento.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
                                                }else{
                                                    miselect_departamento.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                }
                                                }
                                            }, "json");   
                            /// Seleccionar municipio en base al departamento guardado.
                            var miselect=$("#lstMunicipio");
                            var codigo_municipio = data[0].codigo_municipio;
							/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
							miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
								$.post("includes/cargar_municipio.php", { departamento: codigo_departamento },
                                    function(data){
                                        miselect.empty();
                                            for (var i=0; i<data.length; i++) {
                                                if(codigo_municipio == data[i].codigo){
													miselect.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
                                                        }else{
                                                            miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                        }
                                                    }
                                                }, "json");
					/* **********************************************************************************************************************************/	
					/* DATOS DEL RESPONSABLE - MADRE*/
						// blorque para data[1].
                            $('#txtidep').val(data[1].id_alumno_encargado);
                            $('#nombrep').val(data[1].nombres);
                            $('#lugarp').val(data[1].lugar_trabajo);
                            $('#pop').val(data[1].profesion);
                            $('#duip').val(data[1].dui);
                            $('#telefonop').val(data[1].telefono);
                            $('#direccionp').val(data[1].direccion);
                            $('#txtfechanacimientop').val(data[1].fecha_nacimiento);
                            ////
                            //$('#').val(data[1].);
                            $('#lstNacionalidadP').val(data[1].codigo_nacionalidad);
                            $('#lstEstadoFamiliarP').val(data[1].codigo_familiar);
                            $('#lstZonaResidenciaP').val(data[1].codigo_zona); 					

								    /// Seleccionar genero de la base de datos guardado.
                                    var miselect_departamento_p=$("#lstDepartamentoP");
                                    var codigo_departamento_p = data[1].codigo_departamento;
									$("#lstDepartamentoP").val(codigo_departamento_p);
									/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
									miselect_departamento_p.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
										$.post("includes/cargar_departamento.php",
                                            function(data){
												miselect_departamento_p.empty();
                                                    for (var i=0; i<data.length; i++) {
                                                        if(codigo_departamento_p == data[i].codigo){
                                                            miselect_departamento_p.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
                                                        }else{
                                                            miselect_departamento_p.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                        }
														}
													}, "json");   
                                    /// Seleccionar municipio en base al departamento guardado.
                                    var miselect_p=$("#lstMunicipioP");
                                    var codigo_municipio_p = data[1].codigo_municipio;
									/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
									miselect_p.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
											//departamento=$("#lstDepartamento").val();
												$.post("includes/cargar_municipio.php", { departamento: codigo_departamento_p },
															function(data){
																miselect_p.empty();
																for (var i=0; i<data.length; i++) {
																			if(codigo_municipio_p == data[i].codigo){
																				miselect_p.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
																			}else{
																				miselect_p.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
																			}
																	}
											}, "json");		 							  
							        // chekear responsable.
										if (data[1].encargado_bollean == "1") {
												$("#encargadop").prop("checked", true);
										}
					// bloque para data[2].
						$('#txtidem').val(data[2].id_alumno_encargado);                
						$('#nombrem').val(data[2].nombres);                
						$('#lugarm').val(data[2].lugar_trabajo);                
						$('#pom').val(data[2].profesion);                
						$('#duim').val(data[2].dui);                
						$('#telefonom').val(data[2].telefono);                
						$('#direccionm').val(data[2].direccion);                
						$('#txtfechanacimientom').val(data[2].fecha_nacimiento);
                        //// GLOQUE DE SELECT
                        $('#lstNacionalidadM').val(data[2].codigo_nacionalidad);
                        $('#lstEstadoFamiliarM').val(data[2].codigo_familiar);
                        $('#lstZonaResidenciaM').val(data[2].codigo_zona); 	

								    /// Seleccionar genero de la base de datos guardado.
                                    var miselect_departamento_m=$("#lstDepartamentoM");
                                    var codigo_departamento_m = data[2].codigo_departamento;
									$("#lstDepartamento").val(codigo_departamento_m);
									/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
									miselect_departamento_m.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
	                                    $.post("includes/cargar_departamento.php",
                                            function(data){
												miselect_departamento_m.empty();
                                                    for (var i=0; i<data.length; i++) {
                                                        if(codigo_departamento_m == data[i].codigo){
                                                            miselect_departamento_m.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
                                                        }else{
                                                            miselect_departamento_m.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                        }
                                                       }
                                                   }, "json");   
                                    /// Seleccionar municipio en base al departamento guardado.
                                    var miselect_m=$("#lstMunicipioM");
                                    var codigo_municipio_m = data[2].codigo_municipio;
									/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
									miselect_m.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
										//departamento=$("#lstDepartamento").val();
											$.post("includes/cargar_municipio.php", { departamento: codigo_departamento_m },
														function(data){
															miselect_m.empty();
															for (var i=0; i<data.length; i++) {
																		if(codigo_municipio_m == data[i].codigo){
																			miselect_m.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
																		}else{
																			miselect_m.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
																		}
																}
										}, "json");                                                                 
								   //**************chekear responsable.*************************************************************
										if (data[2].encargado_bollean == "1") {
												$("#encargadom").prop("checked", true);
										}		
									// blorque para data[3].
										$('#txtideo').val(data[3].id_alumno_encargado);
										$('#nombreo').val(data[3].nombres);
										$('#lugaro').val(data[3].lugar_trabajo);
										$('#poo').val(data[3].profesion);
										$('#duio').val(data[3].dui);
										$('#telefonoo').val(data[3].telefono);
										$('#direcciono').val(data[3].direccion);
									// chekear responsable.
										if (data[3].encargado_bollean == "1") {
												$("#encargadoo").prop("checked", true);
										}
										
										
										$('#txtfechanacimientoo').val(data[3].fecha_nacimiento);
                                    ///
                                    //// SELECT
                                        $('#lstNacionalidadO').val(data[3].codigo_nacionalidad);
                                        $('#lstEstadoFamiliarO').val(data[3].codigo_familiar);
                                        $('#lstZonaResidenciaO').val(data[3].codigo_zona); 	
                                    
								    /// Seleccionar genero de la base de datos guardado.
                                    var miselect_departamento_o=$("#lstDepartamentoO");
                                    var codigo_departamento_o = data[3].codigo_departamento;
									$("#lstDepartamentoO").val(codigo_departamento_o);
									/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
									miselect_departamento_o.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
	                                    $.post("includes/cargar_departamento.php",
                                            function(data){
												miselect_departamento_o.empty();
                                                    for (var i=0; i<data.length; i++) {
                                                        if(codigo_departamento_o == data[i].codigo){
                                                            miselect_departamento_o.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
                                                        }else{
                                                            miselect_departamento_o.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                        }
                                                       }
                                                   }, "json");   
                                    /// Seleccionar municipio en base al departamento guardado.
                                    var miselect_o=$("#lstMunicipioO");
                                    var codigo_municipio_o = data[3].codigo_municipio;
									/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
									miselect_o.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
                                                                //departamento=$("#lstDepartamento").val();
                                                                 $.post("includes/cargar_municipio.php", { departamento: codigo_departamento_o },
                                                                                function(data){
                                                                                 miselect_o.empty();
                                                                                   for (var i=0; i<data.length; i++) {
                                                                                                if(codigo_municipio_o == data[i].codigo){
                                                                                                   miselect_o.append('<option value="' + data[i].codigo + '" selected>' + data[i].descripcion + '</option>');             
                                                                                                }else{
                                                                                                   miselect_o.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                                                                                                }
                                                                                      }
                                                                }, "json");						
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
		// construir la variable con el url.
           varenviar = "/registro_academico/php_libs/reportes/alumno_constancia.php?todos="+idtodos_ok+"&lstconducta="+lstconducta+"&lstestudia="+lstestudio+"&chktraslado="+chktraslado+"&txttraslado="+txttraslado+"&chksello="+chksello+"&chkfirma="+chkfirma+"&txtcodmatricula="+txtcodigomatricula+"&txtidalumno="+id_alumno;
		// Ejecutar la función
           AbrirVentana(varenviar);
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
				url:"php_libs/soporte/NuevoEditarEstudiante.php",
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
						/*
						$('.nav-tabs a small.required').remove();
							var validatePane = $('.tab-content.tab-validate .tab-pane:has(input.error)').each(function() {
								var id = $(this).attr('id');
								$('.nav-tabs').find('a[href="#' + id + '"]').append(' <small class="required">***</small>');
							});*/
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
		            url:"php_libs/soporte/NuevoEditarEstudiante.php",
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
    $("#Imprimir").click(function() {     
       // construir la variable con el url.
	   var nombre_archivo = $("label[for='Pn']").text();
	   var id_alumno = $("#id_user").val();
       varenviar = "/registro_academico/php_libs/reportes/imprimir_partida_nacimiento.php?nombre_archivo="+nombre_archivo+"&codigo_alumno="+id_alumno;
       // Ejecutar la función
       AbrirVentana2(varenviar);
	});
////////////////////////////////////////////////////
////// SUBMIT para el botón guardar
////////////////////////////////////////////////////
$("#goGuardar").click(function() {     
	$('#formUsers').submit();
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