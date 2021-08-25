$(document).ready(function(){
    //al enviar el formulario
    $('#botonGuardarBase').click(function(){
        // Variables.
        var id_alumno = $("#txtIdAlumno").val();
        //obtenemos el nombre del archivo
        var nuevo_nombre = $("#nuevo_nombre").val();
        var nuevo_nombre_pn = $("#nuevo_nombre_pn").val();
        // valor del button radio.
        var imagen_foto_pn = $('input:radio[name=imagen_foto_pn]:checked').val();        
        var message = "";
        //hacemos la petición ajax  
        $.ajax({
		        cache: false,
		        type: "POST",
		        dataType: "json",
		        url:"php_libs/subir_archivo_dbf.php",
		        data: "Id_Alumno="+ id_alumno + "&nuevo_nombre=" + nuevo_nombre + "&nuevo_nombre_pn=" + nuevo_nombre_pn + "&imagen_foto_pn=" + imagen_foto_pn + "&id=" + Math.random(),
                    beforeSend: function(){
                        message = $("<span class='before'>Subiendo la imagen, por favor espere...</span>");
                        showMessage(message);
                    },
                    //una vez finalizado correctamente
                    success: function(response){
                        message = $("<span 'class=bg-success text-white'>La imagen se guardo en la Base de Datos.</span>");
                        showMessage(message);
                        if(isImage(fileExtension))
                            {
                                                                    //Verificar si es el FRENTE o VUELTO
                                    if(imagen_foto_pn === "foto"){
                                        $('#fotos').removeAttr('scr');
                                        $('#fotos').attr('src','img/png/fotos/' + response.contenido);
                                    }
                                    
                                    if(imagen_foto_pn === "pn"){
                                        $('#fotosPn').removeAttr('scr');
                                        $('#fotosPn').attr('src','img/png/Pn/' + response.contenido);                                        
                                    }
                                
                                $("#botonGuardarBase").prop("disabled",true);
                                $("#botonGuardarFoto").prop("disabled",true);
                            }
            },
            //si ha ocurrido un error
            error: function(){
                message = $("<span class='error'>Ha ocurrido un error.</span>");
                showMessage(message);
            }
        });
    });

    $(".messages").hide();
    //queremos que esta variable sea global
    var fileExtension = "";
    //función que observa los cambios del campo file y obtiene información
    $(':file').change(function()
    {
        //obtenemos un array con los datos del archivo
        var file = $("#imagen")[0].files[0];
        //obtenemos el nombre del archivo
        var fileName = file.name;
        //obtenemos la extensión del archivo
        fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1);
        //obtenemos el tamaño del archivo
        var fileSize = file.size;
        //obtenemos el tipo de archivo image/png ejemplo
        var fileType = file.type;
        //mensaje con la información del archivo
        showMessage("<span class='info'>Archivo para subir: "+fileName+", peso total: "+fileSize+" bytes.</span>");
        $("#botonGuardarFoto").prop("disabled",false);
    });

    //al enviar el formulario
    $('#botonGuardarFoto').click(function(){
        // Variables.        
     /*   var data = new FormData($('input[name^="archivo"]'));     
        jQuery.each($('input[name^="archivo"]')[0].files, function(i, file) {
            data.append(i, file);
        });*/
        
        // The Javascript
            var fileInput = document.getElementById('imagen');
            var file = fileInput.files[0];
            var formData = new FormData();
            formData.append('file', file);

                // valor del button radio.
        var imagen_foto_pn = $('input:radio[name=imagen_foto_pn]:checked').val();
        var message = "";
        //hacemos la petición ajax  
        $.ajax({
            url: 'php_libs/subir_archivo_foto.php',  
            type: 'POST',
            // Form data //datos del formulario
            data: formData,
            //necesario para subir archivos via ajax
            cache: false,
            contentType: false,
            processData: false,
            //mientras enviamos el archivo
            beforeSend: function(){
                message = $("<span class='before'>Subiendo la imagen, por favor espere...</span>");
                showMessage(message);
            },
            //una vez finalizado correctamente
            success: function(data){
                message = $("<span class=bg-success text-white>La imagen ha subido correctamente.</span>");
                showMessage(message);
                if(isImage(fileExtension))
                {
                    // Evaluar la viariable para la foto.
                    if(imagen_foto_pn == "foto"){
                        $('#fotografia').removeAttr('scr');
                        $('#fotografia').attr('src','img/png/' + data);
                        $('#fotos').removeAttr('scr');
                        $('#fotos').attr('src','img/png/' + data);
                    }
                    // Evaluar la viariable para la foto.
                    if(imagen_foto_pn == "pn"){
                        $('#fotosPn').removeAttr('scr');
                        $('#fotosPn').attr('src','img/png/' + data);
                    }
                    // Elementos comunes.
                        $("#botonGuardarBase").prop("disabled",false);
                        $("#botonGuardarFoto").prop("disabled",true);
                        $("#nuevo_nombre_pn").val(data);
                }
            },
            //si ha ocurrido un error
            error: function(){
                message = $("<span class='error'>Ha ocurrido un error.</span>");
                showMessage(message);
            }
        });
    });
});

//como la utilizamos demasiadas veces, creamos una función para 
//evitar repetición de código
function showMessage(message){
    $(".messages").html("").show();
    $(".messages").html(message);
}

//comprobamos si el archivo a subir es una imagen
//para visualizarla una vez haya subido
function isImage(extension)
{
    switch(extension.toLowerCase()) 
    {
        case 'jpg': case 'gif': case 'png': case 'jpeg':
            return true;
        break;
        default:
            return false;
        break;
    }
}