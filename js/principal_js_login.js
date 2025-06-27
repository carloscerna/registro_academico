// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
   
$(function(){       
    // Validar Formulario para la búsqueda de registro según el criterio.   
	$('#formLogin').validate({
        rules:{
            txtnombre: {required: true, minlength: 4},
            txtpassword: {required: true, minlength: 4},
        },
		errorElement: "em",
		errorPlacement: function ( error, element ) {
			// Add the `invalid-feedback` class to the error element
			error.addClass( "invalid-feedback" );
			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.next( "label" ) );
			} else {
				error.insertAfter( element );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
		},
	    submitHandler: function(){
	        // Serializar los datos, toma todos los Id del formulario con su respectivo valor.
	        var str = $('#formLogin').serialize();
	        $.ajax({
	            beforeSend: function(){
	                // Opcional: Mostrar un spinner o deshabilitar el botón de login
	                // Puedes usar un modal de carga con SweetAlert2 también:
                    Swal.fire({
                        title: 'Iniciando Sesión...',
                        html: 'Por favor, espere un momento.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
	            },
	            cache: false,
	            type:"POST",
	            dataType: "json", // Esperamos una respuesta JSON del servidor
	            url:"php_libs/soporte/phpAjaxLogin.inc.php",
	            data:str + "&id=" + Math.random(), // id random para evitar cache en navegadores antiguos
	            success: function(response){
	                // Siempre cerrar cualquier alerta de carga de SweetAlert2
	                Swal.close(); 

	            	// Validar la respuesta del servidor
	            	if(response.respuesta === false){
                        // Usamos el mensaje de error directamente desde la respuesta del servidor
                        error_swal(response.mensaje);
	            	}
	            	else{
                        // Si la operación es exitosa
                        ok_swal(response.mensaje); // Pasamos el mensaje de éxito del backend si lo hay
                        // Pequeño retardo para que el usuario vea el mensaje de éxito antes de redirigir
                        setTimeout(function() {
                            window.location.href = 'index.php'; // Redireccionar al usuario
                        }, 1500); // Redireccionar después de 1.5 segundos
	            	}
	            },
	            error:function(jqXHR, textStatus, errorThrown){
                    // Siempre cerrar cualquier alerta de carga de SweetAlert2
                    Swal.close();

                    let errorMessage = "Ocurrió un error inesperado. Por favor, inténtalo de nuevo.";
                    if (jqXHR.status === 0) {
                        errorMessage = "No se pudo conectar al servidor. Verifique su conexión a internet.";
                    } else if (jqXHR.status == 404) {
                        errorMessage = "La página de autenticación no se encontró [404].";
                    } else if (jqXHR.status == 500) {
                        errorMessage = "Error interno del servidor [500]. Por favor, contacte al soporte.";
                    } else if (textStatus === 'parsererror') {
                        errorMessage = "Error al parsear la respuesta del servidor (JSON inválido).";
                    } else if (textStatus === 'timeout') {
                        errorMessage = "Tiempo de espera agotado. El servidor tardó demasiado en responder.";
                    } else if (textStatus === 'abort') {
                        errorMessage = "La solicitud fue abortada.";
                    } else {
                        errorMessage = "Error desconocido: " + errorThrown;
                    }
                    error_swal(errorMessage); // Mostrar un mensaje de error más detallado
	            }
	        });
            return false; // Prevenir el envío normal del formulario
	    }
	});
});
		
// Funciones para mostrar notificaciones con SweetAlert2

function ok_swal(mensaje = "Conexión Exitosa."){ // Agregamos un mensaje por defecto
    Swal.fire({
        icon: 'success',
        title: '¡Éxito!',
        text: mensaje,
        timer: 1500, // Cierra automáticamente después de 1.5 segundos
        showConfirmButton: false
    });
}

// Función general para mostrar mensajes de advertencia o error
function error_swal(mensaje = "Ha ocurrido un error."){ // Agregamos un mensaje por defecto
    Swal.fire({
        icon: 'error',
        title: '¡Error!',
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

// Las funciones específicas de error_usuario, error_institucion, error_dbname
// NO son necesarias con el manejo unificado que implementamos, ya que el backend
// envía el mensaje específico en 'response.mensaje'.
// Dejamos las de toastr comentadas por si las necesitas como referencia de la antigua implementación.
/*
function ok() {
    toastr.success("Conexión Exitosa.");
    return false;
}

function error_usuario() {
    toastr.warning("Usuario o Contraseña Incorrecta.");
    return false;
}

function error_dbname() {
    toastr.error("NO Existe la base de datos.");
    return false;
}

function error_institucion() {
    toastr.error("La Institución no ha sido creada.");
    return false;
}
*/