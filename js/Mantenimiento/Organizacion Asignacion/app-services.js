/**
 * Servidor y Clientes de Servicios Base
 */
const AppService = {
    // Petición genérica con Fetch API / Async-Await
    async apiPost(url, data) {
        try {
            const response = await $.ajax({
                url: url,
                type: 'POST',
                data: data,
                dataType: 'json'
            });
            return response;
        } catch (error) {
            console.error('Error en la petición:', error);
            ToastService.error('Ocurrió un error en la comunicación con el servidor.');
            throw error;
        }
    },

    // Cargar opciones en Selects dinámicamente
    async cargarSelect(selector, url, params = {}, valueKey = 'codigo', textKey = 'descripcion', defaultText = 'Seleccionar...') {
        const $select = $(selector);
        $select.html(`<option value="">Cargando...</option>`);
        
        try {
            const data = await this.apiPost(url, params);
            $select.empty().append(`<option value="00">${defaultText}</option>`);
            
            if (Array.isArray(data)) {
                data.forEach(item => {
                    const text = item[textKey] || item.nombre;
                    $select.append(new Option(text, item[valueKey]));
                });
            }
            return data;
        } catch (e) {
            $select.html(`<option value="00">Error al cargar</option>`);
        }
    }
};

/**
 * Notificaciones y Alertas centralizadas
 */
const ToastService = {
    success: (msg) => toastr["success"](msg, "Sistema"),
    info: (msg) => toastr["info"](msg, "Sistema"),
    warning: (msg) => toastr["warning"](msg, "Sistema"),
    error: (msg) => toastr["error"](msg, "Sistema")
};

const ConfirmService = {
    async askDelete(text = 'Eliminar el Registro Seleccionado!') {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: { confirmButton: 'btn btn-success me-2', cancelButton: 'btn btn-danger' },
            buttonsStyling: false
        });

        const result = await swalWithBootstrapButtons.fire({
            title: '¿Qué desea hacer?',
            text: text,
            showCancelButton: true,
            confirmButtonText: 'Sí, Eliminar!',
            cancelButtonText: 'No, Cancelar!',
            reverseButtons: true,
            allowOutsideClick: false,
            type: 'question'
        });

        return result.value;
    }
};