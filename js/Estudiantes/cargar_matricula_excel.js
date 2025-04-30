$(document).ready(function () {
    // Mostrar el modal
    $("#goMatriculaImportar").click(function () {
      $("#modalImportarExcel").modal("show");
    });
  

    $('#archivoExcel').on('change', function () {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName);
      });

        // Procesar el formulario Excel
        $("#formImportarExcel").on("submit", function (e) {
        e.preventDefault();
            // Validar selects principales
            const annLectivo = $("#lstannlectivo").val();
            const modalidad = $("#lstmodalidad").val();
            const gradoSeccion = $("#lstgradoseccion").val();

            if (!annLectivo || !modalidad || !gradoSeccion) {
                Swal.fire({
                icon: "warning",
                title: "Datos incompletos",
                text: "Debe seleccionar Año Lectivo, Modalidad y Grado/Sección/Turno antes de importar."
                });
                return;
            }

            let formData = new FormData(this);

            formData.append("lstannlectivo", annLectivo);
            formData.append("lstmodalidad", modalidad);
            formData.append("lstgradoseccion", gradoSeccion);

    //
      $.ajax({
        url: "php_libs/soporte/Estudiante/procesar_excel_matricula.php",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        beforeSend: function () {
          Swal.fire({
            title: "Procesando archivo...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
          });
          $("#tablaVistaExcel tbody").empty();
        },
        success: function (response) {
          Swal.close();
  
          if (!response.exito) {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: response.mensaje
            });
            return;
          }
  
          let datos = response.datos;
          let encontrados = 0;
          let $tbody = $("#tablaVistaExcel tbody");
          
          datos.forEach((item, index) => {
            if (item.encontrado) encontrados++;
          
            const rowClass = item.encontrado ? 'bg-warning' : 'bg-success text-white';
          
            $tbody.append(`
              <tr class="${rowClass}">
                <td>${index + 1}</td>
                <td>${item.codigo_nie}</td>
                <td>${item.apellido_paterno}</td>
                <td>${item.apellido_materno}</td>
                <td>${item.nombre_completo}</td>
                <td class="text-center">
                  ${item.encontrado ? '<span class="badge badge-dark">Sí</span>' : '<span class="badge badge-light">No</span>'}
                </td>
              </tr>
            `);
          });
          
          Swal.fire({
            icon: 'info',
            title: 'Resultado del Excel',
            html: `<b>${encontrados}</b> estudiantes encontrados en la base de datos.`
          });
          
        },
        error: function (xhr, status, error) {
          Swal.close();
          Swal.fire({
            icon: "error",
            title: "Error al procesar",
            text: error
          });
        }
      });
    });



    //
    //
    $("#btnGuardarMatriculaExcel").click(function () {
        let alumnos = [];
      
        $("#tablaVistaExcel tbody tr").each(function () {
          const codigo_nie = $(this).find("td").eq(1).text().trim();
          const apellido_paterno = $(this).find("td").eq(2).text().trim();
          const apellido_materno = $(this).find("td").eq(3).text().trim();
          const nombre_completo = $(this).find("td").eq(4).text().trim();
          const encontrado = $(this).hasClass("bg-warning");
      
          if (encontrado && codigo_nie !== "") {
            alumnos.push({
              codigo_nie,
              apellido_paterno,
              apellido_materno,
              nombre_completo
            });
          }
        });
      
        if (alumnos.length === 0) {
          Swal.fire({
            icon: "info",
            title: "Sin estudiantes válidos",
            text: "No hay estudiantes encontrados para guardar matrícula."
          });
          return;
        }
      
        Swal.fire({
          title: "¿Desea guardar matrícula para los alumnos encontrados?",
          icon: "question",
          showCancelButton: true,
          confirmButtonText: "Sí, guardar",
          cancelButtonText: "Cancelar"
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: "php_libs/soporte/Estudiante/guardar_matricula_excel.php",
              method: "POST",
              dataType: "json",
              data: {
                accion: "GuardarDesdeExcel",
                alumnos: alumnos,
                lstannlectivo: $("#lstannlectivo").val(),
                lstmodalidad: $("#lstmodalidad").val(),
                lstgradoseccion: $("#lstgradoseccion").val()
              },
              beforeSend: function () {
                Swal.fire({
                  title: "Guardando matrículas...",
                  allowOutsideClick: false,
                  didOpen: () => Swal.showLoading()
                });
              },
              success: function (response) {
                Swal.close();
                if (response.respuesta) {
                  Swal.fire("¡Éxito!", response.contenido, "success");
                } else {
                  Swal.fire("Error", response.contenido, "error");
                }
              },
              error: function () {
                Swal.close();
                Swal.fire("Error", "No se pudo procesar la matrícula.", "error");
              }
            });
          }
        });
      });
      
      
  });
  
  