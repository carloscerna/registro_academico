// variables globales
let CodigoPersonal = ""; // Considera si esta variable global es realmente necesaria o se puede pasar como parámetro.

$(function () { // INICIO DEL FUNCTION.
  $(document).ready(function () {
    cargarRegistros();
  });

  // Función para mostrar vista previa de imagen
  function mostrarVistaPrevia(input, idPreview, fileNameSpanId) {
    const file = input.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function (e) {
        $(idPreview).attr("src", e.target.result).show();
        $("#" + fileNameSpanId).text(file.name).hide(); // Ocultar el span si hay un nuevo archivo
      };
      reader.readAsDataURL(file);
    } else {
      // Si el input file se limpia, ocultar vista previa y span de nombre
      $(idPreview).attr("src", "").hide();
      $("#" + fileNameSpanId).text("").hide();
    }
  }

  // CORRECCIÓN: Hacer resetInput GLOBAL para que pueda ser llamado desde onclick en HTML.
  window.resetInput = function(inputId, previewId, fileNameSpanId) {
    $("#" + inputId).val("");
    $("#" + previewId).attr("src", "").hide();
    $("#" + fileNameSpanId).text("").hide(); // Ocultar el nombre del archivo también
  }

  // Eventos para mostrar la vista previa en cada input file
  $("#logo_uno").change(function () {
    mostrarVistaPrevia(this, "#preview_logo_uno", "current_logo_uno_name");
  });
  $("#logo_dos").change(function () {
    mostrarVistaPrevia(this, "#preview_logo_dos", "current_logo_dos_name");
  });
  $("#logo_tres").change(function () {
    mostrarVistaPrevia(this, "#preview_logo_tres", "current_logo_tres_name");
  });
  $("#imagen_firma_director").change(function () {
    mostrarVistaPrevia(this, "#preview_firma_director", "current_firma_director_name");
  });
  $("#imagen_sello_director").change(function () {
    mostrarVistaPrevia(this, "#preview_sello_director", "current_sello_director_name");
  });

  // *** CAMBIOS CLAVE PARA SELECT2 EN MODALES (Inic. y Destrucción) ***

  // Evento para cuando el modal se muestra (antes de que la transición termine)
  $('#modalRegistro').on('show.bs.modal', function (e) {
    // (Re)inicializar Select2 con dropdownParent
    // Esto asegura que Select2 esté fresco y su dropdown aparezca dentro del modal.
    $('#nombre_director').select2({
      theme: 'bootstrap-5',
      width: '100%',
      minimumResultsForSearch: 0, // Permitir búsqueda incluso con pocos resultados
      dropdownParent: $('#modalRegistro') // Asegurar que el dropdown está dentro del modal
    });
    $('#codigo_encargado_registro').select2({
      theme: 'bootstrap-5',
      width: '100%',
      minimumResultsForSearch: 0, // Permitir búsqueda incluso con pocos resultados
      dropdownParent: $('#modalRegistro') // Asegurar que el dropdown está dentro del modal
    });

    // Siempre cargar las opciones de personal cuando el modal se abre
    cargarPersonalOptions();
  });

  // Evento para cuando el modal está completamente oculto
  $('#modalRegistro').on('hidden.bs.modal', function () {
      // Destruir las instancias de Select2 para evitar conflictos al reabrir el modal
      if ($('#nombre_director').data('select2')) {
          $('#nombre_director').select2('destroy');
      }
      if ($('#codigo_encargado_registro').data('select2')) {
          $('#codigo_encargado_registro').select2('destroy');
      }
            console.log("Modal completamente oculto. Recargando registros.");
      cargarRegistros(); // Recargar la tabla SOLO después de que el modal esté completamente oculto
  });
  // *** FIN DE CAMBIOS CLAVE PARA SELECT2 EN MODALES ***

  // Evento para el botón "Nuevo Registro"
  $("#btnNuevoRegistro").click(function () {
    $("#formInstitucion")[0].reset(); // Limpiar el formulario
    $("#id_institucion").val(""); // Asegurarse de que el ID esté vacío para una nueva inserción

    // Resetear vistas previas y nombres de archivos al abrir para nuevo registro
    resetInput('logo_uno', 'preview_logo_uno', 'current_logo_uno_name');
    resetInput('logo_dos', 'preview_logo_dos', 'current_logo_dos_name');
    resetInput('logo_tres', 'preview_logo_tres', 'current_logo_tres_name');
    resetInput('imagen_firma_director', 'preview_firma_director', 'current_firma_director_name');
    resetInput('imagen_sello_director', 'preview_sello_director', 'current_sello_director_name');
    
    // Resetear Select2 a su estado por defecto
    // Es importante que Select2 ya esté inicializado (por el 'show.bs.modal' anterior)
    $('#nombre_director').val('').trigger('change');
    $('#codigo_encargado_registro').val('').trigger('change');
  });

  // GUARDAR O ACTUALIZAR REGISTRO.
  $("#formInstitucion").submit(function (event) {
    event.preventDefault();
    let formData = new FormData(this); // Captura todos los datos y archivos

    // Lógica para añadir los nombres de archivo actuales al formData
    // Esto es CRUCIAL para que PHP sepa qué mantener si no se sube un nuevo archivo.
    // Solo si estamos editando un registro existente (id_institucion tiene valor)
    if ($("#id_institucion").val() !== "") {
      const fileInputs = [
        { fileId: 'logo_uno', spanId: 'current_logo_uno_name', formDataName: 'current_logo_uno_name' },
        { fileId: 'logo_dos', spanId: 'current_logo_dos_name', formDataName: 'current_logo_dos_name' },
        { fileId: 'logo_tres', spanId: 'current_logo_tres_name', formDataName: 'current_logo_tres_name' },
        { fileId: 'imagen_firma_director', spanId: 'current_firma_director_name', formDataName: 'current_imagen_firma_director_name' },
        { fileId: 'imagen_sello_director', spanId: 'current_sello_director_name', formDataName: 'current_imagen_sello_director_name' }
      ];

      fileInputs.forEach(field => {
        // Si no se seleccionó un nuevo archivo (files.length === 0)
        // Y el span con el nombre del archivo actual está visible (es decir, había un archivo)
        // entonces lo enviamos a PHP para que lo mantenga.
        if ($("#" + field.fileId).get(0).files.length === 0 && $("#" + field.spanId).is(":visible")) {
          formData.append(field.formDataName, $("#" + field.spanId).text());
        }
        // Si el span no está visible, significa que el usuario eliminó el archivo o no había uno.
        // En este caso, no enviamos el current_file_name, y PHP lo interpretará como NULL.
      });
    }

    $.ajax({
      url: "php_libs/soporte/institucion/informacionGeneral.php?action=procesar",
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
          }).then(() => {
            $("#modalRegistro").modal("hide"); // Dispara el evento 'hidden.bs.modal'
            // La función cargarRegistros() ahora se llama en el evento 'hidden.bs.modal'
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

  // Función para cargar los registros en la tabla
  function cargarRegistros() {
    console.log("Cargando registros..."); // Log para depuración
    $.ajax({
      url: "php_libs/soporte/institucion/informacionGeneral.php",
      type: "POST",
      data: { action: "listar" },
      dataType: "json",
      success: function (response) {
        if (response.response) {
          $("#tablaInstitucion tbody").html(response.data); // Asume que response.data contiene el HTML de las filas
        } else {
          Swal.fire("Error", response.message, "error");
          $("#tablaInstitucion tbody").html("<tr><td colspan='5' class='text-center'>Error al cargar los registros: " + response.message + "</td></tr>");
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        Swal.fire("Error", "Error al cargar los registros: " + textStatus + " - " + errorThrown, "error");
        $("#tablaInstitucion tbody").html("<tr><td colspan='5' class='text-center'>Error de conexión al cargar registros.</td></tr>");
      }
    });
  }

  // ELIMINAR REGISTRO.
  window.eliminarRegistro = function (id) {
    Swal.fire({
      title: '¿Está seguro?',
      text: "¡No podrá revertir esto!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Sí, eliminarlo!'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "php_libs/soporte/institucion/informacionGeneral.php",
          type: "POST",
          data: { id: id, action: "eliminar" },
          dataType: "json",
          success: function (response) {
            if (response.response) {
              Swal.fire(
                '¡Eliminado!',
                'El registro ha sido eliminado.',
                'success'
              );
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
  };

  // EDITAR REGISTRO CARGAR DATOS AL MODAL.
  window.editarRegistro = function (id) {
    // Asegurarse de que las opciones de personal estén cargadas ANTES de intentar establecer el valor.
    cargarPersonalOptions().then(() => { // La promesa asegura que las opciones ya están en el DOM.
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
            $("#id_institucion").val(response.data.id_institucion);
            $("#codigo_institucion").val(response.data.codigo_institucion);
            $("#nombre_institucion").val(response.data.nombre_institucion);
            $("#direccion_institucion").val(response.data.direccion_institucion);
            $("#codigo_municipio").val(response.data.codigo_municipio);
            $("#codigo_departamento").val(response.data.codigo_departamento);
            $("#telefono").val(response.data.telefono_uno);

            // >>>>> INICIO DE CAMBIO: Manejo robusto de IDs de Select2 <<<<<
            // Asegurarse de que los IDs sean strings válidos o vacíos
            const directorId = (response.data.nombre_director === null || typeof response.data.nombre_director === 'undefined') ? '' : String(response.data.nombre_director).trim();
            const encargadoId = (response.data.codigo_encargado_registro === null || typeof response.data.codigo_encargado_registro === 'undefined') ? '' : String(response.data.encargada_registro_academico).trim();
            // >>>>> FIN DE CAMBIO <<<<<

            $("#codigo_turno").val(response.data.codigo_turno);
            $("#codigo_sector").val(response.data.codigo_sector);
            $("#numero_acuerdo").val(response.data.numero_acuerdo);
            $("#nombre_base_datos").val(response.data.dbname);

            // Manejo de previsualización y nombres de archivo actuales
            const imageFields = [
              { id: 'logo_uno', preview: '#preview_logo_uno', nameSpan: '#current_logo_uno_name', dataField: 'logo_uno' },
              { id: 'logo_dos', preview: '#preview_logo_dos', nameSpan: '#current_logo_dos_name', dataField: 'logo_dos' },
              { id: 'logo_tres', preview: '#preview_logo_tres', nameSpan: '#current_logo_tres_name', dataField: 'logo_tres' },
              { id: 'imagen_firma_director', preview: '#preview_firma_director', nameSpan: '#current_firma_director_name', dataField: 'imagen_firma_director' },
              { id: 'imagen_sello_director', preview: '#preview_sello_director', nameSpan: '#current_sello_director_name', dataField: 'imagen_sello_director' }
            ];

            imageFields.forEach(field => {
              if (response.data[field.dataField]) {
                const fullPath = response.data[field.dataField];
                // Asumiendo que la ruta es relativa desde el directorio web (ej: "uploads/imagen.jpg")
                // Ajusta la ruta base según tu configuración de servidor web
                const relativeWebPath = '/registro_academico/img/' + fullPath;
                $(field.preview).attr("src", relativeWebPath).show();
                
                // Extraer solo el nombre del archivo de la ruta completa (si es una URL)
                const fileName = fullPath.split('/').pop();
                $(field.nameSpan).text(fileName).show(); // Mostrar el nombre del archivo actual
              } else {
                $(field.preview).hide();
                $(field.nameSpan).text("").hide();
              }
              // Limpiar el input file cada vez que se abre el modal para edición.
              // Esto evita que se envíen archivos antiguos si el usuario no selecciona uno nuevo.
              $("#" + field.id).val(""); 
            });

            // Mostrar el modal
            $("#modalRegistro").modal("show");

            // *** ESTABLECER LOS VALORES DE SELECT2 DESPUÉS DE QUE EL MODAL HA SIDO COMPLETAMENTE MOSTRADO ***
            // Usamos .one() para asegurar que este bloque solo se ejecute una vez por cada llamada a .modal("show")
            $('#modalRegistro').one('shown.bs.modal', function () {
              // >>>>> INICIO DE CAMBIO: Lógica mejorada para establecer Select2 y mensajes <<<<<
              // Aplicar valor para nombre_director
              if (directorId && $('#nombre_director option[value="' + directorId + '"]').length > 0) {
                $('#nombre_director').val(directorId).trigger('change');
                console.log("Director ID aplicado:", directorId);
              } else {
                if (directorId) { // Si directorId tiene un valor pero no se encontró en las opciones
                    console.warn(`ID de director '${directorId}' no encontrado en las opciones, limpiando.`);
                } else { // Si directorId es vacío o nulo
                    console.log("ID de director vacío o nulo, limpiando Select2.");
                }
                $('#nombre_director').val('').trigger('change'); // Limpiar Select2
              }

              // Aplicar valor para codigo_encargado_registro
              if (encargadoId && $('#codigo_encargado_registro option[value="' + encargadoId + '"]').length > 0) {
                $('#codigo_encargado_registro').val(encargadoId).trigger('change');
                console.log("Encargado ID aplicado:", encargadoId);
              } else {
                if (encargadoId) { // Si encargadoId tiene un valor pero no se encontró en las opciones
                    console.warn(`ID de encargado '${encargadoId}' no encontrado en las opciones, limpiando.`);
                } else { // Si encargadoId es vacío o nulo
                    console.log("ID de encargado vacío o nulo, limpiando Select2.");
                }
                $('#codigo_encargado_registro').val('').trigger('change'); // Limpiar Select2
              }
              // >>>>> FIN DE CAMBIO <<<<<
            });

          } else {
            Swal.fire("Error", response.message, "error");
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          Swal.fire("Error", "Error al obtener los datos para editar: " + textStatus + " - " + errorThrown, "error");
        }
      });
    }).catch(error => { // Manejar errores de la promesa de cargarPersonalOptions
        console.error("Error al cargar opciones de personal antes de editar:", error);
        Swal.fire("Error", "No se pudieron cargar las opciones de personal.", "error");
    });
  };

  // Función para cargar las opciones de personal para los select2
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
            resolve(); // Resolver la promesa una vez que los datos se hayan cargado
          } else {
            console.error("Error: la clave 'results' no se encontró en la respuesta de datos personales.", data);
            reject("Formato de datos inválido");
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("Error al cargar personal:", textStatus, errorThrown);
          $("#nombre_director").empty().append('<option value="">Error al cargar datos</option>');
          $("#codigo_encargado_registro").empty().append('<option value="">Error al cargar datos</option>');
          reject("Error de AJAX al cargar personal");
        }
      });
    });
  }

}); // FIN DEL FUNCTION.