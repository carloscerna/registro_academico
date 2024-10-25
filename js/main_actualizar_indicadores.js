 
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
                  varenviar = "/registro_academico/php_libs/reportes/Estadisticos/Memoria.php?lstannlectivo="+codigo_annlectivo;
               // Ejecutar la funci�n
                  AbrirVentana(varenviar);        
    });

  // BLOQUE SOBREEDAD
    $('#goVerSobreEdad').on('click',function(){
      // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
        var codigo_annlectivo = $("#lstannlectivo").val();
      // abrir caja de dialogo.		        
          $("label[for='NombreArchivo']").text('Creando Archivo de Sobreedad. ');
      // mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
          $('#myModal').modal('show');
          $.post("php_libs/reportes/CrearNominasSobreedad.php",{codigo_annlectivo: codigo_annlectivo, tipo_archivo: 'sobreedad'},
            function() {
			        toastr.success("Archio Creado...");
       });
      });

  // BLOQUE IGUAL 9 AÑOS Y FECHA DE NACIMIENTO 6/4/2022
  $('#goVerIgualNueveFemenino').on('click',function(){
    // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
      var codigo_annlectivo = $("#lstannlectivo").val();
    // abrir caja de dialogo.		        
        $("label[for='NombreArchivo']").text('Creando Archivo Igual 9 años y antes de Fecha de Nacimiento. ');
    // mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
        $('#myModal').modal('show');
        $.post("php_libs/reportes/CrearNominasSobreedad.php",{codigo_annlectivo: codigo_annlectivo, tipo_archivo: 'Nueve Year'},
          function() {
            toastr.success("Archio Creado...");
     });
    });

  // BLOQUE MAYOR 10 AÑOS SOLO FEMENINO
  $('#goVerMayorDiezFemenino').on('click',function(){
    // BUSCAR EL �LTINMO DE LA ASIGNATURA PARA ASIGNARLE A UN NUEVO REGISTRO.
      var codigo_annlectivo = $("#lstannlectivo").val();
    // abrir caja de dialogo.		        
        $("label[for='NombreArchivo']").text('Creando Archivo Mayor 10 años');
    // mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
        $('#myModal').modal('show');
        $.post("php_libs/reportes/CrearNominasSobreedad.php",{codigo_annlectivo: codigo_annlectivo, tipo_archivo: 'Ten Year'},
          function() {
            toastr.success("Archio Creado...");
     });
    });

}); // FIN DE LA FUNCION
     

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}