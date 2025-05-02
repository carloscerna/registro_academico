// Carga la INformación de Tabla Año Lectivo.
$(document).ready(function()
{
  var ver_ann_lectivo = "si";
        var miselect=$("#lstannlectivo");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
        $.post("includes/cargar-ann-lectivo.php",{verificar_ann_lectivo: ver_ann_lectivo},
            function(data) {
                miselect.empty();
                miselect.append('<option value="">Seleccionar...</option>');
                for (var i=0; i<data.length; i++) {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].nombre + '</option>');
                }
        }, "json");
});

// Información del año lectivo y modalidad.
$(document).ready(function()
{

// Parametros para el año lectivo.
$("#lstannlectivo").change(function () {
            var miselect=$("#lstmodalidad");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
       $("#lstannlectivo option:selected").each(function () {
            elegido=$(this).val();
            annlectivo=$("#lstannlectivo").val();
            $.post("includes/cargar-bachillerato.php", { annlectivo: annlectivo },
                   function(data){
                miselect.empty();
                miselect.append('<option value="">Seleccionar...</option>');
                for (var i=0; i<data.length; i++) {
                    miselect.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                }
        }, "json");			
    });
});
    
    // Parametros para el grado y sección, al seleccionar el bachillerato.
    $("#lstmodalidad").change(function () {
            var miselect=$("#lstgradoseccion");
            var lblturno=$("#lblturno");
        /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
        
       $("#lstmodalidad option:selected").each(function () {
            lblturno.empty();
            elegido=$(this).val();
            ann=$("#lstannlectivo").val();
            $.post("includes/cargar-grado-seccion.php", { elegido: elegido, ann: ann },
                   function(data){
                miselect.empty();
                miselect.append('<option value="">Seleccionar...</option>');
                for (var i=0; i<data.length; i++) {
                    miselect.append('<option value="' + data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno + '">' + data[i].descripcion_grado + ' ' + data[i].descripcion_seccion + ' - ' + data[i].descripcion_turno + '</option>');
                }
     // Cambiar los valores del Select de Periodo O Trimestre.
         bach=$("#lstmodalidad").val();
         var milstperiodo=$("#lstperiodo");
         milstperiodo.empty();
         //alert(bach);
         // Condiciones para Educación Básica y Tercer Ciclo.
         if (bach >= '03' && bach <='05'){
                    milstperiodo.append('<option value="Periodo 1">Trimestre 1</option>');
                    milstperiodo.append('<option value="Periodo 2">Trimestre 2</option>');
                    milstperiodo.append('<option value="Periodo 3">Trimestre 3</option>');
                    milstperiodo.append('<option value="Recuperacion">Recuperación</option>');		
         }

         // Condiciones para Educación Media..
         if (bach >= '06'){
                    milstperiodo.append('<option value="Periodo 1">Período 1</option>');
                    milstperiodo.append('<option value="Periodo 2">Período 2</option>');
                    milstperiodo.append('<option value="Periodo 3">Período 3</option>');
                    milstperiodo.append('<option value="Periodo 4">Período 4</option>');
                    milstperiodo.append('<option value="Recuperacion">Recuperación</option>');
         }			 
        }, "json");
            // seleccionar la asignatura.
            var miselect_2=$("#lstasignatura");
              elegido = $("#lstgradoseccion").val();
              bach=$("#lstmodalidad").val();
              ann=$("#lstannlectivo").val();				
            $.post("includes/cargar-asignatura.php", { elegido: elegido, annlectivo: ann, modalidad: bach },
                 function(data){
                  miselect_2.empty();
                    for (var j=0; j<data.length; j++) {
                    miselect_2.append('<option value="' + data[j].codigo + '">' + data[j].descripcion + '</option>');
                    }	
             }, "json");			
    });
    });
    // Parametros para la asignatura.
    $("#lstgradoseccion").change(function () {
          var miselect=$("#lstasignatura");
              /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
        miselect.find('option').remove().end().append('<option value="">Cargando...</op.tion>').val('');
        
         $("#lstgradoseccion option:selected").each(function () {
                 elegido=$(this).val();
                 bach=$("#lstmodalidad").val();
                 ann=$("#lstannlectivo").val();
                 $.post("includes/cargar-asignatura.php", { elegido: elegido, annlectivo: ann, modalidad: bach },
                 function(data){
                  miselect.empty();
                    for (var j=0; j<data.length; j++) {
                    miselect.append('<option value="' + data[j].codigo + '">' + data[j].descripcion + '</option>');
                    }
             }, "json");
                          // Cambiar los valores del Select de Periodo O Trimestre.
            bach=$("#lstmodalidad").val();
            var milstperiodo=$("#lstperiodo");
            var grado = $('#lstgradoseccion').val();
            milstperiodo.empty();
         //alert(bach);
         // Condiciones para Educación Básica y Tercer Ciclo.
         if (bach >= '03' && bach <='05'){
                    milstperiodo.append('<option value="Periodo 1">Trimestre 1</option>');
                    milstperiodo.append('<option value="Periodo 2">Trimestre 2</option>');
                    milstperiodo.append('<option value="Periodo 3">Trimestre 3</option>');
                    milstperiodo.append('<option value="Recuperacion">Recuperación</option>');		
         }

         // Condiciones para Educación Media..
         if (bach >= '06'){
                    milstperiodo.append('<option value="Periodo 1">Período 1</option>');
                    milstperiodo.append('<option value="Periodo 2">Período 2</option>');
                    milstperiodo.append('<option value="Periodo 3">Período 3</option>');
                    milstperiodo.append('<option value="Periodo 4">Período 4</option>');
                    milstperiodo.append('<option value="Recuperacion">Recuperación</option>');
         }
         
                    // Verificar la variables grado seccion.
                    if(grado.substring(0,2) == '11')
                        {
                            milstperiodo.append('<option value="Nota PAES">Nota PAES</option>');
                        }					 
     });
    });
});