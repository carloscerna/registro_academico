$(document).ready(function () {
      // Inicializa DataTable
      $('#tablaNomina').DataTable({
        columns: [
          { data: 'nie', title: 'Código NIE' },
          { data: 'nombre', title: 'Nombre' }
        ],
            language: {
                processing:     "Procesando...",
                search:         "Buscar:",
                lengthMenu:    "Mostrar _MENU_ registros",
                info:           "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty:      "Mostrando 0 a 0 de 0 registros",
                infoFiltered:   "(filtrado de _MAX_ registros totales)",
                infoPostFix:    "",
                loadingRecords: "Cargando...",
                zeroRecords:    "No se encontraron registros",
                emptyTable:     "No hay datos disponibles",
                paginate: {
                    first:      "Primero",
                    previous:   "Anterior",
                    next:       "Siguiente",
                    last:       "Último"
                },
                aria: {
                    sortAscending:  ": activar para ordenar ascendentemente",
                    sortDescending: ": activar para ordenar descendentemente"
                }
              }
      });
      

    // Cargar Año Lectivo
    $.post('php_libs/soporte/Herramientas/ControladorExcel.php', { accion: 'ann_lectivo' }, function (res) {
        if (res.success) {
            let opciones = '<option value="">Seleccione...</option>';
            res.data.forEach(row => {
                opciones += `<option value="${row.codigo}">${row.descripcion}</option>`;
            });
            $('#ann_lectivo').html(opciones);
        } else {
            alert("Error al cargar años lectivos: " + res.message);
        }
    }, 'json');

    // Cargar Bachillerato
    $('#ann_lectivo').on('change', function () {
        const codigo = $(this).val();
        if (codigo !== '') {
            $.post('php_libs/soporte/Herramientas/ControladorExcel.php', {
                accion: 'bachillerato',
                codigo_ann_lectivo: codigo
            }, function (res) {
                if (res.success) {
                    let opciones = '<option value="">Seleccione...</option>';
                    res.data.forEach(row => {
                        opciones += `<option value="${row.codigo_bachillerato}">${row.nombre}</option>`;
                    });
                    $('#bachillerato').html(opciones);
                    $('#grupo').html('<option value="">Seleccione...</option>');
                }
            }, 'json');
        }
    });

    // Cargar Grupo (grado/sección/turno)
    $('#bachillerato').on('change', function () {
        const bach = $(this).val();
        const ann = $('#ann_lectivo').val();
        if (bach !== '' && ann !== '') {
            $.post('php_libs/soporte/Herramientas/ControladorExcel.php', {
                accion: 'grupo',
                codigo_ann_lectivo: ann,
                codigo_bachillerato: bach
            }, function (res) {
                if (res.success) {
                    let opciones = '<option value="">Seleccione...</option>';
                    res.data.forEach(row => {
                        const value = `${row.codigo_grado}|${row.codigo_seccion}|${row.codigo_turno}`;
                        const texto = `${row.grado} - ${row.seccion} - ${row.turno}`;
                        opciones += `<option value="${value}">${texto}</option>`;
                    });
                    $('#grado').html(opciones);
                }
            }, 'json');
        }
    });

    $('#formExcel').on('submit', function (e) {
        e.preventDefault();
    
        let formData = new FormData(this);
        formData.append('accion', 'procesar_excel'); // 🔥 Aquí se indica al PHP qué hacer
        formData.append('codigo_ann_lectivo', $('#ann_lectivo').val());
        formData.append('codigo_bachillerato', $('#bachillerato').val());
        formData.append('codigo_grupo', $('#grado').val());
    
        Swal.fire({
            title: 'Procesando archivo...',
            html: 'Por favor espere un momento.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    
        $.ajax({
            url: 'php_libs/soporte/Herramientas/ControladorExcel.php', 
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                Swal.close(); // ❌ Cierra el loading
    
                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Procesado!',
                        text: 'Haz clic en descargar para obtener el archivo modificado.',
                        showCancelButton: true,
                        confirmButtonText: 'Descargar',
                        cancelButtonText: 'Cerrar'
                    }).then(result => {
                        if (!result.isConfirmed) return;

  // Petición AJAX para obtener el blob
  const downloadUrl = 'php_libs/soporte/Herramientas/descargarExcel.php';
        console.log('Descarga AJAX POST a:', downloadUrl);
  $.ajax({
    url: downloadUrl,
    method: 'POST',
    data: { file: res.archivo },
    xhrFields: {
      responseType: 'blob'
    },
    success: function(data, status, xhr) {
      // Crear objeto URL y forzar download
      const blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });
      const downloadUrl = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = downloadUrl;
      a.download = res.archivo;
      document.body.appendChild(a);
      a.click();
      a.remove();
      URL.revokeObjectURL(downloadUrl);
    },
    error: function(xhr) {
      Swal.fire('Error', 'No se pudo descargar el archivo: ' + xhr.responseText, 'error');
    }
  });
                    });
                    // recarga DataTable
                    let table = $('#tablaNomina').DataTable();
                    table.clear().rows.add(res.data).draw();
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            },
            error: function (xhr, status, errorThrown) {
                Swal.close(); // Cierra el loading
            
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el servidor',
                    html: `
                        <p><strong>Status:</strong> ${status}</p>
                        <p><strong>Error:</strong> ${errorThrown}</p>
                        <p><strong>Respuesta:</strong> ${xhr.responseText}</p>
                        <b>${response.message}</b><br><small>${response.consulta ?? ''}</small>
                    `,
                    width: 600,
                    scrollbarPadding: false
                });
            }
            
        });
    });
    
    
});
