$(document).ready(function () {
  var tablaAlumnos;

  // Buscar estudiantes
  $("#goBuscar").on('click', function (e) {
    e.preventDefault();

    var annLectivo = $("#lstannlectivo").val();
    var modalidad = $("#lstmodalidad").val();
    var gradoSeccion = $("#lstgradoseccion").val();

    if (annLectivo === "" || modalidad === "" || gradoSeccion === "") {
      Swal.fire({
        icon: 'warning',
        title: 'Campos Incompletos',
        text: 'Debe seleccionar Año Lectivo, Modalidad y Grado/Sección/Turno.'
      });
      return;
    }

    Swal.fire({
      title: 'Buscando estudiantes...',
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading()
      }
    });

    if (tablaAlumnos) {
      tablaAlumnos.destroy();
      $("#tablaAlumnos tbody").empty();
      tablaAlumnos = null;
    }

    tablaAlumnos = $("#tablaAlumnos").DataTable({
      "ajax": {
        "url": "php_libs/soporte/Estudiante/BuscarMatricula.php",
        "type": "POST",
        "dataType": "json",
        "data": {
          "accion_buscar": "BuscarLista",
          "lstannlectivo": annLectivo,
          "lstmodalidad": modalidad,
          "lstgradoseccion": gradoSeccion
        },
        "dataSrc": function (json) {
          Swal.close();
          if (!json.respuesta) {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: json.mensaje
            });
            return [];
          }
          return json.contenido;
        },
        "error": function (xhr, error, thrown) {
          Swal.close();
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo cargar la información: ' + thrown
          });
        }
      },
      "columns": [
        { "data": null },
        { "data": "id_alumno" },
        { "data": "codigo_nie" },
        { "data": "apellido_alumno" },
        {
          "data": null,
          "render": function (data, type, row) {
            return '<input type="checkbox" class="form-check-input alumno-seleccionado" value="' + row.id_alumno + '">';
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

  // Cancelar: ocultar tabla y destruir DataTable
  $("#goCancelar").on('click', function () {
    if (tablaAlumnos) {
      tablaAlumnos.destroy();
      $("#tablaAlumnos tbody").empty();
      tablaAlumnos = null;
    }
    $("#Resultados").hide();
  });

  // Seleccionar/Deseleccionar todos
  $(document).on('change', '#selectAllAlumnos', function () {
    var isChecked = $(this).is(':checked');
    $('.alumno-seleccionado').prop('checked', isChecked);
  });

  // Crear matrícula
  $('#goCrearMatricula').on('click', function () {
    Swal.fire({
      title: '¿Está seguro?',
      text: "Se guardará la matrícula para los alumnos seleccionados.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, guardar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (!result.isConfirmed) return;

      const lstannlectivoD = $('#lstannlectivoD').val();
      const lstmodalidadD = $('#lstmodalidadD').val();
      const lstgradoseccionD = $('#lstgradoseccionD').val();
      let codigo_alumno_ = [], chkmatricula_ = [], fila = 0;

      if (!lstannlectivoD || !lstmodalidadD || !lstgradoseccionD) {
        if (!lstannlectivoD) toastr.error("Debe ingresar el Año Lectivo");
        if (!lstmodalidadD) toastr.error("Debe ingresar la Modalidad");
        if (!lstgradoseccionD) toastr.error("Debe ingresar Grado-Sección y Turno");
        return;
      }

      $("#tablaLista tbody tr").each(function () {
        const codigo_alumno = $(this).find('td').eq(1).html();
        const chkMatricula = $(this).find('td').eq(4).find('input[type="checkbox"]').is(':checked');
        codigo_alumno_[fila] = codigo_alumno;
        chkmatricula_[fila] = chkMatricula;
        fila++;
      });

      Pace.start();

      $.ajax({
        cache: false,
        type: "POST",
        dataType: "json",
        url: "php_libs/soporte/phpAjaxMatriculaMasiva.php",
        data: {
          accion: "CrearMatricula",
          codigo_alumno_: codigo_alumno_,
          fila: fila,
          chk_matricula_: chkmatricula_,
          lstannlectivo: lstannlectivoD,
          lstmodalidad: lstmodalidadD,
          lstgradoseccion: lstgradoseccionD
        },
        success: function (response) {
          if (response.respuesta) {
            toastr.success(response.contenido);
          } else {
            toastr.error(response.contenido);
          }
        }
      });
    });
  });
});
// carlos los segundos select para guardar la matrícula.
$(document).ready(function () {
  // Funciones para cargar Año Lectivo, Modalidad y Grado para DESTINO
  function cargarAnnLectivoDestino() {
    const $annLectivoD = $("#lstannlectivoD");

    $annLectivoD.html('<option value="">Cargando...</option>');

    $.post("includes/cargar-ann-lectivo.php", function (data) {
      $annLectivoD.empty().append('<option value="">Seleccionar...</option>');
      $.each(data, function (i, item) {
        $annLectivoD.append(`<option value="${item.codigo}">${item.nombre}</option>`);
      });
    }, "json");
  }

  function cargarModalidadDestino(annlectivo) {
    const $modalidadD = $("#lstmodalidadD");

    $modalidadD.html('<option value="">Cargando...</option>');

    $.post("includes/cargar-bachillerato.php", { annlectivo: annlectivo }, function (data) {
      $modalidadD.empty().append('<option value="">Seleccionar...</option>');
      $.each(data, function (i, item) {
        $modalidadD.append(`<option value="${item.codigo}">${item.descripcion}</option>`);
      });
    }, "json");
  }

  function cargarGradoSeccionDestino(modalidad, annlectivo) {
    const $gradoSeccionD = $("#lstgradoseccionD");

    $gradoSeccionD.html('<option value="">Cargando...</option>');

    $.post("includes/cargar-grado-seccion.php", { elegido: modalidad, ann: annlectivo }, function (data) {
      $gradoSeccionD.empty().append('<option value="">Seleccionar...</option>');
      $.each(data, function (i, item) {
        $gradoSeccionD.append(`<option value="${item.codigo_grado}${item.codigo_seccion}${item.codigo_turno}">
          ${item.descripcion_grado} ${item.descripcion_seccion} - ${item.descripcion_turno}
        </option>`);
      });
    }, "json");
  }

  // Cargar Año Lectivo al cargar página
  cargarAnnLectivoDestino();

  // Evento: cambiar Año Lectivo Destino -> cargar Modalidad
  $("#lstannlectivoD").change(function () {
    const annlectivo = $(this).val();
    if (annlectivo) {
      cargarModalidadDestino(annlectivo);
      $("#lstgradoseccionD").empty().append('<option value="">Seleccionar...</option>');
    } else {
      $("#lstmodalidadD, #lstgradoseccionD").empty().append('<option value="">Seleccionar...</option>');
    }
  });

  // Evento: cambiar Modalidad Destino -> cargar Grado/Sección/Turno
  $("#lstmodalidadD").change(function () {
    const modalidad = $(this).val();
    const annlectivo = $("#lstannlectivoD").val();
    if (modalidad && annlectivo) {
      cargarGradoSeccionDestino(modalidad, annlectivo);
    } else {
      $("#lstgradoseccionD").empty().append('<option value="">Seleccionar...</option>');
    }
  });
});
