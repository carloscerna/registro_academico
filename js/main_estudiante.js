// id de user global
var id_ = 0;
var NIE = 0;
var tabla = "";
var menu_group = '<div class="dropdown">'+
					'<button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">...'+
					'</button>'+
						'<div class="dropdown-menu">'+
							'<a class="editar dropdown-item fal fa-user-edit" href="#"> Editar'+
							'</a>'+
							'<a class="expediente dropdown-item far fa-id-card" href="#"> Expediente'+
							'</a>'+
							'<a class="imprimir-portada dropdown-item fas fa-address-card" href="#"> Portada'+
							'</a>'+
							'<a class="eliminar dropdown-item fas fa-user-slash" href="#"> Eliminar'+
							'</a>'+
						'</div>'+
				'</div>';
$(function(){ // iNICIO DEL fUNCTION.
///////////////////////////////////////////////////////////////////////////////
// FUNCION QUE CARGA LA TABLA COMPLETA CON LOS REGISTROS
///////////////////////////////////////////////////////////////////////////////
		$(document).ready(function(){
			listar();
		});		
///////////////////////////////////////////////////////////////////////////////
//	FUNCION LISTAR BUSQUEDA DE LOS REGISTROS
///////////////////////////////////////////////////////////////////////////////
var listar = function(){
		// Varaible de Entornos.php
			var buscartodos = "BuscarTodos";
		// Tabla que contrendrá los registros.
			tabla = jQuery("#listadoEstudiantes").DataTable({
				"lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
				"destroy": true,
				"ajax":{
					method:"POST",
					url:"php_libs/soporte/EstudiantesBuscar.php",
					data: {"accion_buscar": buscartodos}
				},
				"columns":[
					{
						data: null,
						defaultContent: menu_group,
						orderable: false
					},
					{"data":"id_alumno"},
                    {"data":"codigo_nie"},
					{"data":"nombre_completo_apellidos"},
                    {"data":"fecha_nacimiento"},
					{"data":"edad"},
					{"data":"estatus"},
					{"data":"nombres"},
					{"data":"fecha_nacimiento_encargado"},
					{"data":"nombre_familiar"},
					{"data":"dui"},
					{"data":"direccion"},
					{"data":"telefono"},
				],
				// LLama a los diferentes mensajes que están en español.
				"language": idioma_espanol
		});
			obtener_data_editar("#listadoEstudiantes tbody", tabla);
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

var obtener_data_editar = function(tbody, tabla){
///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
// ventana modal. EDITAR REGISTRO
///////////////////////////////////////////////////////////////////////////////
	$(tbody).on("click","a.editar",function(){
		var data = tabla.row($(this).parents("tr")).data();
		console.log(data); console.log(data[0]);
		
		id_ = data[0];
		accion = "EditarRegistro";	// variable global
			window.location.href = 'editar_Nuevo_Estudiante.php?id='+id_+"&accion="+accion;
	});
///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
// ventana modal. IMPRIMIR EXPEDIENTE
///////////////////////////////////////////////////////////////////////////////
	$(tbody).on("click","a.expediente",function(){
		var data = tabla.row($(this).parents("tr")).data();
		console.log(data); console.log(data[0]);
		
		id_ = data[0];
			// construir la variable con el url.
               varenviar = "/registro_academico/php_libs/reportes/ficha_alumno.php?id_user="+id_;
            // Ejecutar la función
               AbrirVentana(varenviar);
	});
///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
// ventana modal. IMPRIMIR PORTADA
///////////////////////////////////////////////////////////////////////////////	  
	$(tbody).on("click","a.imprimir-portada",function(){
		var data = tabla.row($(this).parents("tr")).data();
		console.log(data); console.log(data[0]);
		
		id_ = data[0];
			// construir la variable con el url.
               varenviar = "/registro_academico/php_libs/reportes/alumno_portada.php?txtidalumno="+id_;
            // Ejecutar la función
               AbrirVentana(varenviar);
	});
	///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
// ventana modal. ELIMINAR REGISTRO
///////////////////////////////////////////////////////////////////////////////
$(tbody).on("click","a.eliminar",function(){
	var data = tabla.row($(this).parents("tr")).data();
	console.log(data); console.log(data[1]);
	id_ = data[0];
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
						// ejecutar Ajax.. 
						$.ajax({
						cache: false,                     
						type: "POST",                     
						dataType: "json",                     
						url:"php_libs/soporte/NuevoEditarEstudiante.php",                     
						data: {                     
								accion_buscar: 'eliminarEstudiante', id_estudiante: id_,
								},                     
						success: function(response) {                     
								if (response.respuesta === true) {                     		
									toastr["info"](response.mensaje, "Sistema");
									window.location.href = 'estudiantes.php';				                  
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
					'Su Archivo no ha sido Eliminado :)',
					'error'
				)
				}
			})
});


}; // Funcion principal dentro del DataTable.
///////////////////////////////////////////////////////////////////////////////
//	FUNCION que al dar clic buscar el registro para posterior mente abri una
// ventana modal. GENERAR NUEVO ESTUDIANTE
///////////////////////////////////////////////////////////////////////////////	  
$('#goNuevoUser').on( 'click', function () {
		accion = "AgregarNuevoEstudiante";	// variable global
		id_ = 0;
			window.location.href = 'editar_Nuevo_Estudiante.php?id='+id_+"&accion="+accion;
});	  
});	// final de FUNCTION.

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}
