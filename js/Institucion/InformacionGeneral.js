// variables globales (considera si 'CodigoPersonal' es realmente necesaria como global)
let CodigoPersonal = "";

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
        $("#" + fileNameSpanId).text(file.name).hide(); // Ocultar el nombre del archivo si hay una vista previa
      };
      reader.readAsDataURL(file);
    } else {
      $(idPreview).attr("src", "").hide();
      $("#" + fileNameSpanId).text("").hide(); // Ocultar si no se selecciona ningún archivo
    }
  }

  // Hacer resetInput GLOBAL para que pueda ser llamado desde onclick en HTML.
  window.resetInput = function(inputId, previewId, fileNameSpanId) {
    $("#" + inputId).val("");
    $("#" + previewId).attr("src", "").hide();
    $("#" + fileNameSpanId).text("").hide(); // Asegurarse de ocultar el nombre del archivo actual
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

  // Evento para cuando el modal se muestra (antes de que la transición termine)
  $('#modalRegistro').on('show.bs.modal', function (e) {
    // (Re)inicializar Select2 para todos los dropdowns
    // Asegurarse de que Select2 se inicialice correctamente cada vez que el modal se abre
    const select2Options = {
      theme: 'bootstrap-5',
      width: '100%', // Ancho del 100%
      minimumResultsForSearch: 0, // Muestra siempre el campo de búsqueda
      dropdownParent: $('#modalRegistro') // Importante para que el dropdown aparezca dentro del modal
    };

    $('#nombre_director').select2(select2Options);
    $('#codigo_encargado_registro').select2(select2Options);
    $('#selectDepartamento').select2(select2Options);
    $('#selectMunicipio').select2(select2Options);
    $('#selectDistrito').select2(select2Options);

    // Cargar opciones para los Select2 de personal y ubicacion
    cargarPersonalOptions();
    cargarDepartamentos(); // Cargar departamentos al abrir el modal
  });

  // Evento para cuando el modal está completamente oculto
  $('#modalRegistro').on('hidden.bs.modal', function () {
      console.log("Modal completamente oculto. Recargando registros.");
      cargarRegistros(); // Recargar la tabla al cerrar el modal

      // Destruir las instancias de Select2 para evitar conflictos al reabrir el modal
      // Es crucial para que Select2 se reinicialice sin problemas.
      const select2Elements = ['#nombre_director', '#codigo_encargado_registro', '#selectDepartamento', '#selectMunicipio', '#selectDistrito'];
      select2Elements.forEach(selector => {
          if ($(selector).data('select2')) { // Verificar si Select2 está inicializado
              $(selector).select2('destroy');
          }
      });
  });

  // Evento para el botón "Nuevo Registro"
  $("#btnNuevoRegistro").click(function () {
    $("#formInstitucion")[0].reset(); // Resetear el formulario HTML
    $("#id_institucion").val(""); // Asegurar que el ID oculto esté vacío

    // Resetear vistas previas y nombres de archivos
    resetInput('logo_uno', 'preview_logo_uno', 'current_logo_uno_name');
    resetInput('logo_dos', 'preview_logo_dos', 'current_logo_dos_name');
    resetInput('logo_tres', 'preview_logo_tres', 'current_logo_tres_name');
    resetInput('imagen_firma_director', 'preview_firma_director', 'current_firma_director_name');
    resetInput('imagen_sello_director', 'preview_sello_director', 'current_sello_director_name');
    
    // Resetear Select2 de personal y ubicación a su estado inicial de "Seleccione..."
    // Asegurarse de que Select2 sepa que su valor ha cambiado
    $('#nombre_director').val('').trigger('change');
    $('#codigo_encargado_registro').val('').trigger('change');

    $('#selectDepartamento').val('').trigger('change');
    $('#selectMunicipio').empty().append('<option value="">Seleccione el municipio...</option>').trigger('change');
    $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change');
  });

  // Evento de cambio para el selector de Departamento
  $('#selectDepartamento').on('change', function() {
      const codigoDepartamento = $(this).val();
      if (codigoDepartamento) {
          cargarMunicipios(codigoDepartamento);
          // Limpiar y resetear distrito al cambiar de departamento
          $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change'); 
      } else {
          // Limpiar ambos si no hay departamento seleccionado
          $('#selectMunicipio').empty().append('<option value="">Seleccione el municipio...</option>').trigger('change');
          $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change');
      }
  });

  // Evento de cambio para el selector de Municipio
  $('#selectMunicipio').on('change', function() {
      const codigoMunicipio = $(this).val();
      const codigoDepartamento = $('#selectDepartamento').val(); // Necesitamos también el departamento
      if (codigoMunicipio && codigoDepartamento) {
          cargarDistritos(codigoDepartamento, codigoMunicipio);
      } else {
          // Limpiar distrito si no hay municipio seleccionado
          $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change');
      }
  });


  // GUARDAR O ACTUALIZAR REGISTRO.
  $("#formInstitucion").submit(function (event) {
    event.preventDefault();
    let formData = new FormData(this);

    // Aplicar limpiarTexto a los campos de texto antes de enviar
    formData.set('codigo_institucion', limpiarTexto(formData.get('codigo_institucion')));
    formData.set('nombre_institucion', limpiarTexto(formData.get('nombre_institucion')));
    formData.set('telefono', limpiarTexto(formData.get('telefono')));
    formData.set('numero_acuerdo', limpiarTexto(formData.get('numero_acuerdo')));
    formData.set('nombre_base_datos', limpiarTexto(formData.get('nombre_base_datos')));
    formData.set('direccion_institucion', limpiarTexto(formData.get('direccion_institucion')));


    if ($("#id_institucion").val() !== "") {
      const fileInputs = [
        { fileId: 'logo_uno', spanId: 'current_logo_uno_name', formDataName: 'current_logo_uno_name' },
        { fileId: 'logo_dos', spanId: 'current_logo_dos_name', formDataName: 'current_logo_dos_name' },
        { fileId: 'logo_tres', spanId: 'current_logo_tres_name', formDataName: 'current_logo_tres_name' },
        { fileId: 'imagen_firma_director', spanId: 'current_firma_director_name', formDataName: 'current_imagen_firma_director_name' },
        { fileId: 'imagen_sello_director', spanId: 'current_sello_director_name', formDataName: 'current_imagen_sello_director_name' }
      ];

      fileInputs.forEach(field => {
        // Si no se seleccionó un nuevo archivo Y el span de nombre actual está visible (es decir, había un archivo existente)
        if ($("#" + field.fileId).get(0).files.length === 0 && $("#" + field.spanId).is(":visible")) {
          formData.append(field.formDataName, $("#" + field.spanId).text());
        }
      });
    }

    $.ajax({
      url: "php_libs/soporte/institucion/informacionGeneral.php?action=procesar",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
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
            $("#modalRegistro").modal("hide");
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
    console.log("Cargando registros...");
    $.ajax({
      url: "php_libs/soporte/institucion/informacionGeneral.php",
      type: "POST",
      data: { action: "listar" },
      dataType: "json",
      success: function (response) {
        if (response.response) {
          $("#tablaInstitucion tbody").html(response.data);
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
    // Cargar personal y ubicación ANTES de obtener los datos del registro.
    // Esto asegura que las opciones de los selects estén disponibles para Select2.
    Promise.all([
        cargarPersonalOptions(),
        cargarDepartamentos() // Cargamos los departamentos primero
    ]).then(() => {
        $.ajax({
            url: "php_libs/soporte/institucion/informacionGeneral.php",
            type: "POST",
            data: { id: id, action: "obtener" },
            dataType: "json",
            success: function (response) {
                if (response.response) {
                    $("#id_institucion").val(response.data.id_institucion);
                    $("#codigo_institucion").val(limpiarTexto(response.data.codigo_institucion));
                    $("#nombre_institucion").val(limpiarTexto(response.data.nombre_institucion));
                    $("#telefono").val(limpiarTexto(response.data.telefono_uno));
                    $("#codigo_turno").val(limpiarTexto(response.data.codigo_turno));
                    $("#codigo_sector").val(limpiarTexto(response.data.codigo_sector));
                    $("#numero_acuerdo").val(limpiarTexto(response.data.numero_acuerdo));
                    $("#nombre_base_datos").val(limpiarTexto(response.data.nombre_base_datos));
                    $("#direccion_institucion").val(limpiarTexto(response.data.direccion_institucion));

                    // Establecer Select2 de personal
                    // Convertir a String y trim para asegurar compatibilidad y limpiar espacios.
                    const directorId = (response.data.nombre_director === null || typeof response.data.nombre_director === 'undefined') ? '' : String(response.data.nombre_director).trim();
                    const encargadoId = (response.data.codigo_encargado_registro === null || typeof response.data.codigo_encargado_registro === 'undefined') ? '' : String(response.data.codigo_encargado_registro).trim();
                    
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
                            // `fullPath` ya viene como URL completa desde PHP (e.g., http://localhost/registro_academico/img/unique_filename.jpg)
                            $(field.preview).attr("src", fullPath).show();
                            // Extraer el nombre del archivo de la URL
                            const fileName = fullPath.substring(fullPath.lastIndexOf('/') + 1);
                            $(field.nameSpan).text(fileName).show();
                        } else {
                            $(field.preview).hide();
                            $(field.nameSpan).text("").hide(); // Asegurarse de ocultar el nombre también
                        }
                        $("#" + field.id).val(""); // Limpiar el input file por si el usuario quiere subir uno nuevo
                    });

                    // *** Lógica para Select2 de Ubicación ***
                    const currentDepartamento = (response.data.codigo_departamento === null || typeof response.data.codigo_departamento === 'undefined') ? '' : String(response.data.codigo_departamento).trim();
                    const currentMunicipio = (response.data.codigo_municipio === null || typeof response.data.codigo_municipio === 'undefined') ? '' : String(response.data.codigo_municipio).trim();
                    const currentDistrito = (response.data.codigo_distrito === null || typeof response.data.codigo_distrito === 'undefined') ? '' : String(response.data.codigo_distrito).trim();
                    // Mostrar el modal ANTES de intentar seleccionar los valores de Select2.
                    $("#modalRegistro").modal("show");

                    // Usar 'shown.bs.modal' para asegurar que el modal esté completamente visible y Select2 esté listo.
                    $('#modalRegistro').one('shown.bs.modal', function () { // Usar .one() para que se dispare solo una vez
                        // Seleccionar Select2 de personal
                        if (directorId && $('#nombre_director option[value="' + directorId + '"]').length > 0) {
                            $('#nombre_director').val(directorId).trigger('change');
                        } else {
                            $('#nombre_director').val('').trigger('change');
                        }

                        if (encargadoId && $('#codigo_encargado_registro option[value="' + encargadoId + '"]').length > 0) {
                            $('#codigo_encargado_registro').val(encargadoId).trigger('change');
                        } else {
                            $('#codigo_encargado_registro').val('').trigger('change');
                        }

                        // Encadenar promesas para dropdowns de ubicación para asegurar la carga secuencial
                        if (currentDepartamento) {
                            $('#selectDepartamento').val(currentDepartamento).trigger('change');
                            // Cuando los municipios se carguen para el departamento seleccionado, entonces seleccionar el municipio
                            cargarMunicipios(currentDepartamento).then(() => {
                                if (currentMunicipio && $('#selectMunicipio option[value="' + currentMunicipio + '"]').length > 0) {
                                    $('#selectMunicipio').val(currentMunicipio).trigger('change');
                                    // Cuando los distritos se carguen para el municipio seleccionado, entonces seleccionar el distrito
                                    cargarDistritos(currentDepartamento, currentMunicipio).then(() => {
                                        // Asegurarse de que currentDistrito esté limpio de espacios.
                                        const trimmedDistrito = currentDistrito ? String(currentDistrito).trim() : ''; // Convertir a string por si no lo es.
                                        if (trimmedDistrito && $('#selectDistrito option[value="' + trimmedDistrito + '"]').length > 0) {
                                            $('#selectDistrito').val(trimmedDistrito).trigger('change');
                                        } else {
                                            console.warn(`ID de distrito '${trimmedDistrito}' no encontrado o nulo/vacío. Reseteando.`);
                                            $('#selectDistrito').val('').trigger('change');
                                        }
                                    }).catch(error => {
                                        console.error("Error al cargar distritos:", error);
                                        $('#selectDistrito').val('').trigger('change');
                                    });
                                } else {
                                    console.warn(`ID de municipio '${currentMunicipio}' no encontrado o nulo/vacío. Reseteando.`);
                                    $('#selectMunicipio').val('').trigger('change');
                                    $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change');
                                }
                            }).catch(error => {
                                console.error("Error al cargar municipios:", error);
                                $('#selectMunicipio').val('').trigger('change');
                                $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change');
                            });
                        } else {
                            console.warn(`ID de departamento '${currentDepartamento}' no encontrado o nulo/vacío. Reseteando.`);
                            $('#selectDepartamento').val('').trigger('change');
                            $('#selectMunicipio').empty().append('<option value="">Seleccione el municipio...</option>').trigger('change');
                            $('#selectDistrito').empty().append('<option value="">Seleccione el distrito...</option>').trigger('change');
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
    }).catch(error => {
        console.error("Error al cargar opciones de personal o departamentos antes de editar:", error);
        Swal.fire("Error", "No se pudieron cargar las opciones necesarias. " + error, "error");
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
            reject("Formato de datos inválido en personal");
          }
        },
        error: function (jqXHR, textStatus, errorThrown) {
          console.error("Error al cargar personal:", textStatus, errorThrown);
          $("#nombre_director").empty().append('<option value="">Error al cargar datos</option>');
          $("#codigo_encargado_registro").empty().append('<option value="">Error al cargar datos</option>');
          reject("Error de AJAX al cargar personal: " + textStatus);
        }
      });
    });
  }

  // --- Funciones para Cargar Departamentos, Municipios, Distritos ---
  function cargarDepartamentos() {
      return new Promise((resolve, reject) => {
          $.ajax({
              url: 'includes/cargar_elsalvador.php',
              type: 'POST',
              data: { NumeroCondicion: 1 },
              dataType: 'json',
              success: function (data) {
                  const select = $('#selectDepartamento');
                  select.empty().append('<option value="">Seleccione el departamento...</option>');
                  // Verificar si data es un array y tiene elementos
                  if (Array.isArray(data) && data.length > 0) {
                      $.each(data, function (i, item) {
                          select.append('<option value="' + item.codigo + '">' + item.descripcion + '</option>');
                      });
                  } else {
                      console.warn("No se recibieron departamentos o el formato es incorrecto.", data);
                  }
                  resolve();
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  console.error("Error al cargar departamentos:", textStatus, errorThrown);
                  $('#selectDepartamento').empty().append('<option value="">Error al cargar datos</option>');
                  reject("Error al cargar departamentos: " + textStatus);
              }
          });
      });
  }

  function cargarMunicipios(codigoDepartamento) {
      return new Promise((resolve, reject) => {
          $.ajax({
              url: 'includes/cargar_elsalvador.php',
              type: 'POST',
              data: { NumeroCondicion: 2, CodigoDepartamento: codigoDepartamento },
              dataType: 'json',
              success: function (data) {
                  const select = $('#selectMunicipio');
                  select.empty().append('<option value="">Seleccione el municipio...</option>');
                   // Verificar si data es un array y tiene elementos
                  if (Array.isArray(data) && data.length > 0) {
                    $.each(data, function (i, item) {
                        select.append('<option value="' + item.codigo + '">' + item.descripcion + '</option>');
                    });
                  } else {
                      console.warn("No se recibieron municipios o el formato es incorrecto para Dpto:", codigoDepartamento, data);
                  }
                  resolve();
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  console.error("Error al cargar municipios:", textStatus, errorThrown);
                  $('#selectMunicipio').empty().append('<option value="">Error al cargar datos</option>');
                  reject("Error al cargar municipios: " + textStatus);
              }
          });
      });
  }

  function cargarDistritos(codigoDepartamento, codigoMunicipio) {
      return new Promise((resolve, reject) => {
          $.ajax({
              url: 'includes/cargar_elsalvador.php',
              type: 'POST',
              data: { NumeroCondicion: 3, CodigoDepartamento: codigoDepartamento, CodigoMunicipio: codigoMunicipio },
              dataType: 'json',
              success: function (data) {
                  const select = $('#selectDistrito');
                  select.empty().append('<option value="">Seleccione el distrito...</option>');
                   // Verificar si data es un array y tiene elementos
                  if (Array.isArray(data) && data.length > 0) {
                    $.each(data, function (i, item) {
                        //select.append('<option value="' + item.codigo + '">' + item.descripcion + '</option>');
                        // Aplicar trim() a item.codigo y item.descripcion
                        select.append('<option value="' + String(item.codigo).trim() + '">' + String(item.descripcion).trim() + '</option>');
                    });
                  } else {
                      console.warn("No se recibieron distritos o el formato es incorrecto para Dpto:", codigoDepartamento, "Muni:", codigoMunicipio, data);
                  }
                  select.trigger('change'); // NOTIFICAR A SELECT2
                  resolve();
              },
              error: function (jqXHR, textStatus, errorThrown) {
                  console.error("Error al cargar distritos:", textStatus, errorThrown);
                  $('#selectDistrito').empty().append('<option value="">Error al cargar datos</option>');
                  reject("Error al cargar distritos: " + textStatus);
              }
          });
      });
  }

  // Función para limpiar espacios y caracteres no deseados
  function limpiarTexto(texto) {
    if (typeof texto !== 'string') return ''; // Asegura que es una cadena
    
    // Elimina espacios normales, no separables, BOMs, etc., al principio y final
    return texto.replace(/^[\\s\\uFEFF\\xA0]+|[\\s\\uFEFF\\xA0]+$/g, '');
  }

}); // FIN DEL FUNCTION.