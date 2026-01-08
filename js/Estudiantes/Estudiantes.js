// Variables globales
let id_ = 0;
let NIE = 0;
let tablaEstudiantes = ""; // Renombrado para claridad

// Menú de acciones (HTML string)
const menu_group = `
<div class="dropdown">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        ...
    </button>
    <div class="dropdown-menu">
        <a class="editar dropdown-item fal fa-user-edit" href="#"> Editar</a>
        <a class="expediente dropdown-item far fa-id-card" href="#"> Expediente</a>
        <a class="imprimir-portada dropdown-item fas fa-address-card" href="#"> Portada</a>
        <a class="imprimir-portada-promocion dropdown-item fas fa-address-card" href="#"> Portada Promoción</a>
        <div class="dropdown-divider"></div>
        <a class="eliminar dropdown-item fas fa-user-slash text-danger" href="#"> Eliminar</a>
    </div>
</div>`;

$(function(){ 
    // Inicializar al cargar
    $(document).ready(function(){
        listar();
    });		

    // ------------------------------------------------------------------------
    // FUNCIÓN LISTAR (DataTables)
    // ------------------------------------------------------------------------
    const listar = function(){
        const buscartodos = "BuscarTodos";
        
        tablaEstudiantes = jQuery("#listadoEstudiantes").DataTable({
            "responsive": true,
            "processing": true, // Muestra mensaje de carga
            "serverSide": false, // Cambiar a true si tienes miles de registros y paginas en backend
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            "destroy": true,
            "ajax":{
                method: "POST",
                url: "php_libs/soporte/EstudiantesBuscar.php",
                data: {"accion_buscar": buscartodos},
                dataSrc: "data" // DataTables leerá response.data
            },
            "columns":[
                { data: null, defaultContent: menu_group, orderable: false, width: "5%" },
                { data: "id_alumno" },
                { data: "codigo_nie" },
                { data: "nombre_completo_apellidos" },
                { data: "fecha_nacimiento" },
                { data: "edad" },
                { 
                    data: "estatus",
                    render: function(data, type, row) {
                        // Ejemplo visual opcional
                        return `<span class="badge bg-info text-dark">${data}</span>`;
                    }
                },
                { data: "nombres" }, // Encargado
                { data: "fecha_nacimiento_encargado" },
                { data: "nombre_familiar" },
                { data: "dui" },
                { data: "direccion" },
                { data: "telefono" },
            ],
            "language": idioma_espanol,
            "order": [[ 1, "desc" ]] // Ordenar por ID descendente por defecto
        });
        
        // Activar listeners de botones
        acciones_tabla("#listadoEstudiantes tbody", tablaEstudiantes);
    };

    // ------------------------------------------------------------------------
    // CONFIGURACIÓN IDIOMA
    // ------------------------------------------------------------------------
    const idioma_espanol = {
        "sProcessing":     "Procesando...",
        "sLengthMenu":     "Mostrar _MENU_ registros",
        "sZeroRecords":    "No se encontraron resultados",
        "sEmptyTable":     "Ningún dato disponible en esta tabla",
        "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
        "sInfoEmpty":      "Mostrando 0 de 0 registros",
        "sInfoFiltered":   "(filtrado de _MAX_ registros)",
        "sSearch":         "Buscar:",
        "oPaginate": {
            "sFirst":    "Primero",
            "sLast":     "Último",
            "sNext":     "Siguiente",
            "sPrevious": "Anterior"
        }
    };	  

    // ------------------------------------------------------------------------
    // MANEJO DE ACCIONES (BOTONES DENTRO DE LA TABLA)
    // ------------------------------------------------------------------------
    const acciones_tabla = function(tbody, tabla){
        
        // --- EDITAR ---
        $(tbody).on("click", "a.editar", function(e){
            e.preventDefault();
            let data = tabla.row($(this).parents("tr")).data();
            id_ = data.id_alumno; // Usar nombre de propiedad es más seguro que índice array
            let accion = "EditarRegistro";
            window.location.href = `EditarNuevoEstudiante.php?id=${id_}&accion=${accion}`;
        });

        // --- EXPEDIENTE ---
        $(tbody).on("click", "a.expediente", function(e){
            e.preventDefault();
            let data = tabla.row($(this).parents("tr")).data();
            id_ = data.id_alumno;
            AbrirVentana(`/registro_academico/php_libs/reportes/ficha_alumno.php?id_user=${id_}`);
        });

        // --- PORTADA ---
        $(tbody).on("click", "a.imprimir-portada", function(e){
            e.preventDefault();
            let data = tabla.row($(this).parents("tr")).data();
            id_ = data.id_alumno;
            AbrirVentana(`/registro_academico/php_libs/reportes/Estudiante/Portada.php?txtidalumno=${id_}`);
        });

        // --- PORTADA PROMOCIÓN ---
        $(tbody).on("click", "a.imprimir-portada-promocion", function(e){
            e.preventDefault();
            let data = tabla.row($(this).parents("tr")).data();
            id_ = data.id_alumno;
            AbrirVentana(`/registro_academico/php_libs/reportes/Estudiante/PortadaPromocion.php?txtidalumno=${id_}`);
        });

        // --- ELIMINAR ---
        $(tbody).on("click", "a.eliminar", function(e){
            e.preventDefault();
            let data = tabla.row($(this).parents("tr")).data();
            id_ = data.id_alumno;

            Swal.fire({
                title: '¿Está seguro?',
                text: "¡Eliminará el estudiante seleccionado permanentemente!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        cache: false,                     
                        type: "POST",                     
                        dataType: "json",
                        // OJO: Asegúrate que esta ruta sea correcta, en el código original decía 'NuevoEditarEstudiante.php' 
                        // pero la lógica la pusimos en 'EstudiantesBuscar.php'. Ajusta según tu estructura real.
                        // Si 'EstudiantesBuscar.php' es quien tiene el CASE 'eliminarEstudiante', usa ese archivo.
                        url: "php_libs/soporte/EstudiantesBuscar.php",                      
                        data: {                     
                            accion_buscar: 'eliminarEstudiante', 
                            id_estudiante: id_ 
                        },                     
                        success: function(response) {                     
                            if (response.respuesta === true) {     
                                Swal.fire('Eliminado!', response.mensaje, 'success');                		
                                // Recargar tabla sin recargar página
                                tablaEstudiantes.ajax.reload();			                  
                            } else {
                                Swal.fire('Error', response.mensaje, 'error');
                            }               
                        },
                        error: function(){
                            Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
                        }
                    });
                }
            })
        });
    };

    // ------------------------------------------------------------------------
    // BOTÓN NUEVO ESTUDIANTE
    // ------------------------------------------------------------------------
    $('#goNuevoUser').on('click', function () {
        let accion = "AgregarNuevoEstudiante";
        id_ = 0;
        window.location.href = `EditarNuevoEstudiante.php?id=${id_}&accion=${accion}`;
    });	  
});

// Función auxiliar
function AbrirVentana(url) {
    window.open(url, '_blank');
    return false;
}