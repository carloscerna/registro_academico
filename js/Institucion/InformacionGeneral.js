$(function(){ // iNICIO DEL fUNCTION.
  $(document).ready(function () {
    cargarRegistros();
  });
 // Función para mostrar vista previa de imagen
 function mostrarVistaPrevia(input, idPreview) {
  const file = input.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      $(idPreview).attr("src", e.target.result).show();
    };
    reader.readAsDataURL(file);
  }
}

// Función para resetear el input file y ocultar la vista previa
function resetInput(inputId, previewId) {
  $("#" + inputId).val("");
  $("#" + previewId).attr("src", "").hide();
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

// Al hacer clic en "Nuevo registro", reinicia el formulario y oculta las vistas previas
$("#btnNuevoRegistro").on("click", function(){
  $("#formInstitucion")[0].reset();
  $("#id_institucion").val("");
  $("img[id^='preview_']").attr("src", "").hide();
});

//
$('#modalRegistro').on('hidden.bs.modal', function () {
  // Remover el foco del botón de cierre si está presente
  $('.btn-close').blur();
});
// GUARDAR O ACTUALIZAR REGISTRO.
$("#formInstitucion").submit(function (event) {
  event.preventDefault();
  let formData = new FormData(this); // Captura todos los datos y archivos

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
              });
              cargarRegistros();
              $("#modalRegistro").modal("hide");
          } else {
              Swal.fire("Error", response.message + " - " + response.error, "error");
          }
      },
      error: function () {
          Swal.fire("Error", "Error en la operación", "error");
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
    data: {action: "listar"},
    success: function (response) {
      if(response.response) {
        $("#dataInstituciones").html(response.data);
        if ($.fn.DataTable.isDataTable('#instituciones')) {
          $('#instituciones').DataTable().destroy();
        }
        $('#instituciones').DataTable();
      } else {
        Swal.fire("Error", response.message + " - " + response.error, "error");
      }
    },
    error: function () {
      Swal.fire("Error", "Error al cargar los registros", "error");
    }
  });
}
// EDITAR REGISTRO CARGAR DATOS AL MODAL.
function editarRegistro(id) {
  $.ajax({
      url: "php_libs/soporte/institucion/informaciongeneral.php",
      type: "POST",
      data: { id: id, action: "obtener" },
      dataType: "json",
      success: function (response) {
          if (response.response) {
              // Asignar todos los campos recibidos al formulario
          $("#nombre_director").val(response.data.nombre_director);
          $("#codigo_institucion").val(response.data.codigo_institucion);
          $("#nombre_institucion").val(response.data.nombre_institucion);
          $("#direccion_institucion").val(response.data.direccion_institucion);
          $("#codigo_municipio").val(response.data.codigo_municipio);
          $("#codigo_departamento").val(response.data.codigo_departamento);
          $("#telefono").val(response.data.telefono_uno);

          $("#codigo_encargado_registro").val(response.data.codigo_encargado_registro);
          $("#codigo_turno").val(response.data.codigo_turno);
          $("#codigo_sector").val(response.data.codigo_sector);
          $("#numero_acuerdo").val(response.data.numero_acuerdo);
          $("#nombre_base_datos").val(response.data.nombre_base_datos);
          // Para logos y otras imágenes: se muestra la vista previa si existe el archivo
          
          if(response.data.logo_uno){
            $("#preview_logo_uno").attr("src", response.data.logo_uno).show();
          }else {
            $("#preview_logo_uno").hide();
          }
        
          if(response.data.logo_dos){
            $("#preview_logo_dos").attr("src", response.data.logo_dos).show();
          }else {
            $("#preview_logo_dos").hide();
        }
          if(response.data.logo_tres){
            $("#preview_logo_tres").attr("src", response.data.logo_tres).show();
          }
          else {
            $("#preview_logo_tres").hide();
        }
          if(response.data.imagen_firma_director){
            $("#preview_firma_director").attr("src", response.data.imagen_firma_director).show();
          }else {
            $("#preview_firma_director").hide();
        }
        
          if(response.data.imagen_sello_director){
            $("#preview_sello_director").attr("src", response.data.imagen_sello_director).show();
          }else {
            $("#preview_sello_director").hide();
        }
        

          $("#id_institucion").val(response.data.id_institucion);

              $("#modalRegistro").modal("show");
          } else {
              Swal.fire("Error", response.message, "error");
          }
      },
      error: function () {
          Swal.fire("Error", "Error al obtener los datos para editar", "error");
      }
  });
}
// EOMINAR REGISTROS
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
              data: { id: id, action: "eliminar" },
              dataType: "json",
              success: function (response) {
                  if (response.response) {
                      Swal.fire("Eliminado", response.message, "success");
                      cargarRegistros();
                  } else {
                      Swal.fire("Error", response.message + " - " + response.error, "error");
                  }
              },
              error: function () {
                  Swal.fire("Error", "Error al eliminar el registro", "error");
              }
          });
      }
  });
}