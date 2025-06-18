// variables globales
let CodigoPersonal = ""; // Considera si esta variable global es realmente necesaria o se puede pasar como parámetro.

$(function () { // INICIO DEL FUNCTION.
  $(document).ready(function () {
    cargarRegistros();

    // Inicializar Select2 una vez al cargar el documento, sin dropdownParent inicialmente.
    // Esto maneja la renderización inicial. dropdownParent se establecerá al abrir el modal.
    $('#nombre_director').select2({
      theme: 'bootstrap-5',
      width: 'resolve',
      minimumResultsForSearch: 0 // Permitir búsqueda incluso con pocos resultados
    });
    $('#codigo_encargado_registro').select2({
      theme: 'bootstrap-5',
      width: 'resolve',
      minimumResultsForSearch: 0 // Permitir búsqueda incluso con pocos resultados
    });
  });

  // Función para mostrar vista previa de imagen
  function mostrarVistaPrevia(input, idPreview) {
    const file = input.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        $(idPreview).attr("src", e.target.result).show();
      };
      reader.readAsDataURL(file);
    } else {
      $(idPreview).attr("src", "").hide(); // Ocultar si no se selecciona ningún archivo
    }
  }

  // Función para resetear el input file, ocultar la vista previa y el nombre del archivo
  function resetInput(inputId, previewId, fileNameSpanId) {
    $("#" + inputId).val("");
    $("#" + previewId).attr("src", "").hide();
    $("#" + fileNameSpanId).text("").hide(); // Ocultar el nombre del archivo también
  }

  // Eventos para mostrar la vista previa en cada input file
  $("#logo_uno").change(function () {
    mostrarVistaPrevia(this, "#preview_logo_uno");
  });
  $("#logo_dos").change(function () {
    mostrarVistaPrevia(this, "#preview_logo_dos");
  });
  $("#logo_tres").change(function () {
    mostrarVistaPrevia(this, "#preview_logo_tres");
  });
  $("#imagen_firma_director").change(function () {
    mostrarVistaPrevia(this, "#preview_firma_director");
  });
  $("#imagen_sello_director").change(function () {
    mostrarVistaPrevia(this, "#preview_sello_director");
  });

  // Al hacer clic en "Nuevo registro", reinicia el formulario y oculta las vistas previas y nombres de archivo
  $("#btnNuevoRegistro").on("click", function () {
    $("#formInstitucion")[0].reset();
    $("#id_institucion").val("");
    $("img[id^='preview_']").attr("src", "").hide();
    $("span[id^='current_']").text("").hide(); // Ocultar los nombres de archivo actuales

    // Resetear los campos Select2 para un nuevo registro
    $('#nombre_director').val('').trigger('change');
    $('#codigo_encargado_registro').val('').trigger('change');
    
    // Cargar la lista de personal para un nuevo registro
    cargarPersonalOptions();
  });

  // Llamarla al abrir el modal
  $('#modalRegistro').on('show.bs.modal', function (e) {
    // Asegurarse de que las listas de personal se carguen cada vez que se muestre el modal
      cargarPersonalOptions();
    // Set dropdownParent cuando se muestra el modal
    $('#nombre_director').select2({
      dropdownParent: $('#modalRegistro'),
      minimumResultsForSearch: 0,
      width: "100%"
    });
    $('#codigo_encargado_registro').select2({
      dropdownParent: $('#modalRegistro'),
      minimumResultsForSearch: 0,
      width: "100%"
    });
    

  });

  // GUARDAR O ACTUALIZAR REGISTRO.
  $("#formInstitucion").submit(function (event) {
    event.preventDefault();
    let formData = new FormData(this); // Captura todos los datos y archivos

    // Añadir los nombres de archivo actuales al formData si existen y no se ha seleccionado un nuevo archivo
    // Esto es CRUCIAL para que PHP sepa qué mantener si no se sube un nuevo archivo.
    // Solo si estamos editando un registro existente (es decir, id_institucion tiene valor)
    if ($("#id_institucion").val() !== "") {
        if ($("#logo_uno").get(0).files.length === 0 && $("#current_logo_uno").is(":visible")) {
            formData.append('current_logo_uno_name', $("#current_logo_uno").text());
        }
        if ($("#logo_dos").get(0).files.length === 0 && $("#current_logo_dos").is(":visible")) {
            formData.append('current_logo_dos_name', $("#current_logo_dos").text());
        }
        if ($("#logo_tres").get(0).files.length === 0 && $("#current_logo_tres").is(":visible")) {
            formData.append('current_logo_tres_name', $("#current_logo_tres").text());
        }
        if ($("#imagen_firma_director").get(0).files.length === 0 && $("#current_firma_director_name").is(":visible")) {
            formData.append('current_imagen_firma_director_name', $("#current_firma_director_name").text());
        }
        if ($("#imagen_sello_director").get(0).files.length === 0 && $("#current_sello_director_name").is(":visible")) {
            formData.append('current_imagen_sello_director_name', $("#current_sello_director_name").text());
        }
    }


    $.ajax({
      url: "php_libs/soporte/institucion/informaciongeneral.php?action=procesar",
      type: "POST",
      data: formData,
      processData: false, // Necesario para FormData
      contentType: false, // Necesario para FormData
      dataType: "json",
      success: function (response) {
        if (response.response) {
        Swal.fire({
          title: "Éxito",
          text: response.message,
          icon: "success",
          timer: 2000,
          showConfirmButton: false
        }).then(() => { // Usar .then() para asegurar que el modal se cierre y luego se carguen los registros.
            cargarRegistros(); // <<<--- ESTO ES CRUCIAL
            $("#modalRegistro").modal("hide"); // Se cierra el modal después del Swal.fire
        });
        } else {
          Swal.fire("Error", response.message + " - " + response.error, "error");
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        Swal.fire("Error", "Error en la operación: " + textStatus + " - " + errorThrown, "error");
      }
    });
  });

}); // FIN DEL FUNCTION.


// CARGAR DATOS
function cargarRegistros() {
  $.ajax({
    cache: false,
    url: "php_libs/soporte/institucion/informaciongeneral.php",
    type: "POST",
    dataType: "json",
    data: {
      action: "listar"
    },
    success: function (response) {
      if (response.response) {
        $("#dataInstituciones").html(response.data);
        if ($.fn.DataTable.isDataTable('#instituciones')) {
          $('#instituciones').DataTable().destroy();
        }
        $('#instituciones').DataTable();
      } else {
        Swal.fire("Error", response.message + " - " + response.error, "error");
      }
    },
    error: function (jqXHR, textStatus, errorThrown) {
      Swal.fire("Error", "Error al cargar los registros: " + textStatus + " - " + errorThrown, "error");
    }
  });
}

// EDITAR REGISTRO CARGAR DATOS AL MODAL.
function editarRegistro(id) {
  // Load personal options first, then populate form
  cargarPersonalOptions().then(() => {
    $.ajax({
      url: "php_libs/soporte/institucion/informacionGeneral.php",
      type: "POST",
      data: {
        id: id,
        action: "obtener"
      },
      dataType: "json",
      success: function (response) {
        if (response.response) {
          // Asignar todos los campos recibidos al formulario
          $("#id_institucion").val(response.data.id_institucion);
          $("#codigo_institucion").val(response.data.codigo_institucion);
          $("#nombre_institucion").val(response.data.nombre_institucion);
          $("#direccion_institucion").val(response.data.direccion_institucion);
          $("#codigo_municipio").val(response.data.codigo_municipio);
          $("#codigo_departamento").val(response.data.codigo_departamento);
          $("#telefono").val(response.data.telefono_uno);

          $("#codigo_turno").val(response.data.codigo_turno);
          $("#codigo_sector").val(response.data.codigo_sector);
          $("#numero_acuerdo").val(response.data.numero_acuerdo);
          $("#nombre_base_datos").val(response.data.dbname);

          // >>>>>> MODIFICACIÓN CLAVE AQUÍ PARA ARCHIVOS <<<<<<
          // Para logos y otras imágenes: mostrar preview y el nombre del archivo si existe
          const imageFields = [
              { id: 'logo_uno', preview: '#preview_logo_uno', nameSpan: '#current_logo_uno_name', dataField: 'logo_uno' },
              { id: 'logo_dos', preview: '#preview_logo_dos', nameSpan: '#current_logo_dos_name', dataField: 'logo_dos' },
              { id: 'logo_tres', preview: '#preview_logo_tres', nameSpan: '#current_logo_tres_name', dataField: 'logo_tres' },
              { id: 'imagen_firma_director', preview: '#preview_firma_director', nameSpan: '#current_firma_director_name', dataField: 'imagen_firma_director' },
              { id: 'imagen_sello_director', preview: '#preview_sello_director', nameSpan: '#current_sello_director_name', dataField: 'imagen_sello_director' }
          ];

          imageFields.forEach(field => {
              if (response.data[field.dataField]) {
                  $(field.preview).attr("src", response.data[field.dataField]).show();
                  // Extraer solo el nombre del archivo de la ruta completa (si es una URL)
                  const fileName = response.data[field.dataField].split('/').pop();
                  $(field.nameSpan).text(fileName).show();
              } else {
                  $(field.preview).hide();
                  $(field.nameSpan).text("").hide();
              }
          });
          // >>>>>> FIN MODIFICACIÓN CLAVE <<<<<<
          $("#modalRegistro").modal("show");
          // *** APLICAR LOS VALORES DE SELECT2 DESPUÉS DE QUE EL MODAL SE MUESTRE COMPLETO ***
          // Usar un pequeño setTimeout para asegurar que Select2 tenga tiempo de renderizarse
          // O, aún mejor, usar el evento 'shown.bs.modal'
          $('#modalRegistro').one('shown.bs.modal', function () { // Usar 'one' para que se dispare una sola vez
            // Aplicar valor para nombre_director
            const directorId = response.data.nombre_director; // Suponiendo que `nombre_director` de la DB es el ID
            if ($('#nombre_director option[value="' + directorId + '"]').length > 0) {
              $('#nombre_director').val(directorId).trigger('change');
              console.log("Director ID aplicado:", directorId);
            } else {
              console.warn(`ID de director ${directorId} no encontrado en las opciones, limpiando.`);
              $('#nombre_director').val('').trigger('change');
            }

            // Aplicar valor para codigo_encargado_registro
            const encargadoId = String(response.data.encargada_registro_academico).trim(); // Suponiendo que `nombre_director` de la DB es el ID
            if ($('#codigo_encargado_registro option[value="' + encargadoId + '"]').length > 0) {
              $('#codigo_encargado_registro').val(encargadoId).trigger('change');
              console.log("Encargado ID aplicado:", encargadoId);
            } else {
              console.warn(`ID de encargado ${encargadoId} no encontrado en las opciones, limpiando.`);
              $('#codigo_encargado_registro').val('').trigger('change');
            }
          });
        } else {
          Swal.fire("Error", response.message, "error");
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        Swal.fire("Error", "Error al obtener los datos para editar: " + textStatus + " - " + errorThrown, "error");
      }
    });
  });
}
// ELIMINAR REGISTROS
function eliminarRegistro(id) {
  Swal.fire({
    title: "¿Eliminar registro?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "php_libs/soporte/institucion/informaciongeneral.php",
        type: "POST",
        data: {
          id: id,
          action: "eliminar"
        },
        dataType: "json",
        success: function (response) {
          if (response.response) {
            Swal.fire("Eliminado", response.message, "success");
            cargarRegistros();
          } else {
            Swal.fire("Error", response.message + " - " + response.error, "error");
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          Swal.fire("Error", "Error al eliminar el registro: " + textStatus + " - " + errorThrown, "error");
        }
      });
    }
  });
}

// Unified function to load personal options for both selects
function cargarPersonalOptions() {
  return new Promise((resolve, reject) => {
    $.ajax({
      url: 'php_libs/soporte/institucion/informacionGeneral.php?action=listarPersonal',
      type: 'POST',
      dataType: 'json',
      success: function (data) {
        if (data.results) {
          var directorSelect = $("#nombre_director");
          var encargadoSelect = $("#codigo_encargado_registro");

          directorSelect.empty().append('<option value="">Seleccione el director...</option>');
          encargadoSelect.empty().append('<option value="">Seleccione el encargado...</option>');

          $.each(data.results, function (i, item) {
            directorSelect.append('<option value="' + item.id + '">' + item.text + '</option>');
            encargadoSelect.append('<option value="' + item.id + '">' + item.text + '</option>');
          });
          resolve(); // Resolve the promise once data is loaded
        } else {
          console.error("Error: 'results' key not found in personal data response.", data);
          reject("Invalid data format");
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error al cargar personal:", textStatus, errorThrown);
        $("#nombre_director").empty().append('<option value="">Error al cargar datos</option>');
        $("#codigo_encargado_registro").empty().append('<option value="">Error al cargar datos</option>');
        reject(errorThrown);
      }
    });
  });
}

// Función para resetear el input file, ocultar la vista previa y el nombre del archivo
function resetInput(inputId, previewId, fileNameSpanId) {
    $("#" + inputId).val("");
    $("#" + previewId).attr("src", "").hide();
    $("#" + fileNameSpanId).text("").hide(); // Ocultar el nombre del archivo también
}