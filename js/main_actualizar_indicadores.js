 
$(function(){       
  // BLOQUE PARA ACTUALIZAR REPITENTES
     $('#goActualizarRepitentes').on('click',function(){
     // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
      var codigo_annlectivo = $("#lstannlectivo").val();
	// abrir caja de dialogo.		        
  $("label[for='NombreArchivo']").text('Repitentes. ');
	// mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
		$('#myModal').modal('show');
     // Llamar al archivo php para hacer la consulta y presentar los datos.
      $.post("php_libs/soporte/ActualizarRepitentes.php",{codigo_annlectivo: codigo_annlectivo},
       function() {
         toastr.success("Registro(s) Actualizados...");
       });

     });

  // BLOQUE PARA NUEVO INGRESO
     $('#goActualizarNuevoIngreso').on('click',function(){
     // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
      var codigo_annlectivo = $("#lstannlectivo").val();
	  // abrir caja de dialogo.		        
      $("label[for='NombreArchivo']").text('Nuevo Ingreso. ');
	  // mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
		$('#myModal').modal('show');
     // Llamar al archivo php para hacer la consulta y presentar los datos.
      $.post("php_libs/soporte/ActualizarNuevoIngreso.php",{codigo_annlectivo: codigo_annlectivo},
       function() {
         toastr.success("Registro(s) Actualizados...");
       });

     });
     
  // BLOQUE PARA NUEVO REGISTRO (Asignatura)
     $('#goActualizarPN').on('click',function(){
     // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
      var codigo_annlectivo = $("#lstannlectivo").val();
	// abrir caja de dialogo.		        
  $("label[for='NombreArchivo']").text('Partida de Nacimiento. ');
	// mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
		$('#myModal').modal('show');
     // Llamar al archivo php para hacer la consulta y presentar los datos.

     $.post("php_libs/soporte/ActualizarPN.php",{codigo_annlectivo: codigo_annlectivo},
       function() {
			toastr.success("Registro(s) Actualizados...");
       });

     });

  // BLOQUE MEMORIA ESTADISTICA
  $('#goVerMemoriaEstadistica').on('click',function(){
    // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
     var codigo_annlectivo = $("#lstannlectivo").val();
                  varenviar = "/registro_academico/php_libs/reportes/memoria_estadistica.php?lstannlectivo="+codigo_annlectivo;
               // Ejecutar la funci�n
                  AbrirVentana(varenviar);        
    });


    });
     

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}