// Variables globales
let id_ = 0;
let NIE = 0;
let tablaEstudiantes = ""; // Renombrado para claridad

// Menú de acciones (HTML string)
const menu_group = `
<div class="dropdown">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
        Acciones
    </button>
    <div class="dropdown-menu">
        <a class="historial dropdown-item fas fa-flag text-success" href="#"> Ver Historial</a>
        <div class="dropdown-divider"></div>
        <a class="editar dropdown-item fal fa-user-edit" href="#"> Editar</a>
        <a class="expediente dropdown-item far fa-id-card" href="#"> Expediente</a>
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

        // --- CARGAR HISTORIAL ---
        $(tbody).on("click", "a.historial", function(e){
            e.preventDefault();
            let data = tabla.row($(this).parents("tr")).data();
            let id_estudiante = data.id_alumno; //

            // Limpiar y mostrar spinner
            $("#contenidoHistorial").html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i> Cargando historial...</div>');
            $("#modalHistorial").modal("show");

            $.ajax({
                type: "POST",
                url: "php_libs/soporte/EstudiantesBuscar.php", //
                data: { accion_buscar: 'BuscarHistorial', id_estudiante: id_estudiante },
                dataType: "json",
                success: function(response) {
                    if (response.respuesta) {
                        let html = `
                        <table class="table table-sm table-striped table-bordered table-hover">
                            <thead class="bg-navy text-white text-center">
                                <tr>
                                    <th>Código</th>
                                    <th>Año Lectivo</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>`;
                        
                        response.data.forEach(reg => {
                            // IMPORTANTE: Añadimos la clase 'fila-historial' y el atributo 'data-id'
                            html += `
                            <tr class="fila-historial" data-id="${reg.id_alumno_matricula}" style="cursor:pointer;">
                                <td class="text-center">${reg.codigo_ann_lectivo}</td>
                                <td>${reg.nombre_annlectivo}</td>
                                <td>${reg.nombre_grado}</td>
                                <td class="text-center">${reg.nombre_seccion}</td>
                                <td class="text-center">
                                    <span class="badge ${reg.condicion === 'Activo' ? 'bg-success' : 'bg-danger'}">
                                        ${reg.condicion}
                                    </span>
                                </td>
                            </tr>`;
                        });
                        
                        html += `</tbody></table>`;
                        $("#contenidoHistorial").html(html);
                    } else {
                        $("#contenidoHistorial").html(`<div class="alert alert-info text-center">${response.mensaje}</div>`);
                    }
                },
                error: function() {
                    $("#contenidoHistorial").html('<div class="alert alert-danger text-center">Error de comunicación con el servidor.</div>');
                }
            });
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

        // Variable global para el gráfico y evitar duplicados
        let chartRendimiento = null;

        function cargarGraficaRendimiento(idMatricula) {
            $.ajax({
                type: "POST",
                url: "php_libs/soporte/EstudiantesBuscar.php",
                data: { accion_buscar: 'BuscarRendimiento', id_matricula: idMatricula },
                dataType: "json",
                success: function(res) {
                    if (res.respuesta) {
                        const ctx = document.getElementById('graficaRendimiento').getContext('2d');
                        
                        // Extraer etiquetas (materias) y datos (notas)
                        const etiquetas = res.data.notas.map(n => n.materia);
                        const valores = res.data.notas.map(n => n.nota_final);

                        // Mostrar Resumen de Texto
                        let colorEstado = res.data.estado === 'APROBADO' ? 'text-success' : 'text-danger';
                        $("#infoResumen").html(`
                            <div class="row text-center mb-3">
                                <div class="col-6"><h5>Promedio Global: <b>${res.data.promedio_global}</b></h5></div>
                                <div class="col-6"><h5>Estado: <b class="${colorEstado}">${res.data.estado}</b></h5></div>
                            </div>
                        `);

                        // Destruir gráfico anterior si existe
                        if (chartRendimiento) chartRendimiento.destroy();

                        chartRendimiento = new Chart(ctx, {
                            type: 'bar', // Puedes cambiarlo a 'radar'
                            data: {
                                labels: etiquetas,
                                datasets: [{
                                    label: 'Nota Final por Asignatura',
                                    data: valores,
                                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                                    borderColor: 'rgb(54, 162, 235)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                scales: { y: { beginAtZero: true, max: 10 } }
                            }
                        });
                    }
                }
            });
        }

$(document).on("click", ".fila-historial", function() {
    let idMatricula = $(this).data("id");
    
    console.log("Cargando rendimiento para matrícula ID: " + idMatricula);
    
    // 1. Buscamos el botón de la pestaña por su nuevo ID
    let el = document.querySelector('#tab-rendimiento-btn');
    
    if (el) {
        // 2. Usamos getOrCreateInstance para evitar el error de 'closest'
        let tabRendimiento = bootstrap.Tab.getOrCreateInstance(el);
        tabRendimiento.show();
        
        // 3. Llamamos a la función de la gráfica
        if (typeof cargarGraficaRendimiento === "function") {
            cargarGraficaRendimiento(idMatricula);
        }
    } else {
        console.error("No se encontró el botón de la pestaña #tab-rendimiento-btn");
    }
});

});

// Función auxiliar
function AbrirVentana(url) {
    window.open(url, '_blank');
    return false;
}