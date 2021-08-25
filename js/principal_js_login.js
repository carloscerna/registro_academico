// id de user global
var idUser_ok = 0;
var accion_ok = 'noAccion';
   
$(function(){       
                // Validar Formulario para la buscque de registro segun el criterio.   
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
		            },
		            cache: false,
		            type: "POST",
		            dataType: "json",
		            url:"php_libs/soporte/phpAjaxLogin.inc.php",
		            data:str + "&id=" + Math.random(),
		            success: function(response){
		            	// Validar mensaje de error
		            	if(response.respuesta === false){
                                        if(response.contenido == "Error Usuario"){
                                                error_usuario();        
                                        }
                                        if(response.contenido == "Error Institucion"){
                                                error_institucion();        
                                        }
		            	}
		            	else{
					// si es exitosa la operación
					ok();
					window.location.href = 'index.php';
				}
		            },
				error:function(){
                                error_dbname();
		            }
		        });
                                return false;
		    },
		});
});
			
			function ok(){
				toastr.success("Conexión Exitosa."); 
				return false;
			}
		
			function error_usuario(){
				toastr.warning("Usuario o Contraseña Incorrecta."); 
				return false; 
			}
                        function error_dbname(){
				toastr.error("NO Existe la base de datos."); 
				return false; 
			}
                       function error_institucion(){
				toastr.error("La Institución no ha sido creada."); 
				return false; 
			}