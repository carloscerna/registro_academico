$(document).ready(function() {
    var tablaAlumnos;
  
    $("#goBuscar").on('click', function(e) {
      e.preventDefault();
  
      var annLectivo = $("#lstannlectivo").val();
      var modalidad = $("#lstmodalidad").val();
      var gradoSeccion = $("#lstgradoseccion").val();
  
      if (tablaAlumnos) {
        tablaAlumnos.destroy();
        $("#tablaAlumnos tbody").empty();
      }
  
      tablaAlumnos = $("#tablaAlumnos").DataTable({
        "ajax": {
          "url": "php_libs/soporte/Estudiante/BuscarMatricula.php",
          "type": "POST",
          "data": {
            "accion_buscar": "BuscarLista",
            "lstannlectivo": annLectivo,
            "lstmodalidad": modalidad,
            "lstgradoseccion": gradoSeccion
          },
          "dataSrc": ""
        },
        "columns": [
          { "data": null },
          { "data": "id_alumno" },
          { "data": "codigo_nie" },
          { "data": "apellido_alumno" },
          {
            "data": null,
            "render": function(data, type, row) {
              return '<input type="checkbox" class="form-check-input alumno-seleccionado" value="'+row.id_alumno+'">';
            }
          }
        ],
        "responsive": true,
        "autoWidth": false,
        "language": {
          "url": "php_libs/idioma/es_es.json"
        },
        "columnDefs": [
          {
            "targets": 0,
            "render": function (data, type, row, meta) {
              return meta.row + 1;
            }
          }
        ]
      });
  
      $("#Resultados").show();
    });
  });
  