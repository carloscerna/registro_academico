// id de user global
var IdEG = 0;
var IdCD = 0;
var Accion_Editar_Eliminar = "noAccion";
var accion = "BuscarCD";              
var codigo_docente = "";
var codigo_asignatura = "";
var codigo_modalidad = "";
var codigo_gst = "";
var codigo_ann_lectivo = "";
//
$(function(){
// funcionalidad del botón Actualizar
		$('#goCancelar').on('click',function(){
                $("#goCABuscar").prop("disabled",false);
				$("#lstannlectivo").prop("disabled",false);
                $("#lstCodigoPersonal").prop("disabled",false);
                // Borrar antes.
                $('#listaEG').empty();
                $('#listaCD').empty();
				$('#Informacion').empty();
				$('#InformacionError').empty();
                // Abrimos el Formulario
                $('#CargaAcademicaContenedor').hide();
				$('#Informacion').hide();
				$('#InformacionError').hide();
        });
// Funcionalidad para Imprimir por Asignatura.
        $('#goCAImprimir').on('click',function(){
                var codigo_annlectivo = $('#lstannlectivo').val();
                var codigo_docente = $('#lstCodigoPersonal').val();
                // Informe de la Carga Académica
                        varenviar = "/registro_academico/php_libs/reportes/informe_carga_docente.php?codigo_annlectivo="+codigo_annlectivo+"&codigo_docente="+codigo_docente;
                // Ejecutar la función
                        AbrirVentana(varenviar);                        
        });
// ////////////////////////////////////////////////////////////////        
// funcionalidad del botón que abre el formulario
// BUSCA LA INFORMACIÓN DEL CODIGO DEL DOCENTE SELECCIONADO.
                $('#goCABuscar').on('click',function(){
				// Desactivar botones.
                $("#goCABuscar").prop("disabled",true);
				$("#lstannlectivo").prop("disabled",true);
                $("#lstCodigoPersonal").prop("disabled",true);
                // Abrimos el Formulario
                   $('#CargaAcademicaContenedor').show();
                // Borrar antes.
                        $('#listaEG').empty();
                        $('#listaCD').empty();
                // Rellenar los datos de TAB-2, Carga Docente.
                        // Ver datos de la modalidad.
                           var miselect1=$("#lstCodigoModalidadCD");
                           var miselect2=$("#lstCodigoModalidad");
                           var cd = true;
                           var annlectivo=$("#lstannlectivo").val();
                           var codigo_personal = $("#lstCodigoPersonal").val();
                           $("label[for='NombreAnnLectivo']").text(annlectivo);
                               	$.post("includes/cargar_nombre_personal_docente.php", { annlectivo: annlectivo, cd: cd, codigo_personal: codigo_personal,},
                               	       function(data){
                                        // RELLENA LOS DATOS DE LA MODALIDAD PARA LA CARGA DOCENTE.
                               		miselect1.empty();
                                        miselect1.append("<option value=00>Seleccionar...</option>");
                               		for (var i=0; i<data.length; i++) {
                               			miselect1.append('<option value="' + data[i].codigo + '">' + data[i].descripcion + '</option>');
                               		}
                                        // RELLENAR LOS DATOS DE MODALIDAD PARA ENCARGADO DE GRADO.
                            		miselect2.empty();
                                        miselect2.append("<option value=00>Seleccionar...</option>");
                               		for (var j=0; j<data.length; j++) {
                               			miselect2.append('<option value="' + data[j].codigo + '">' + data[j].descripcion + '</option>');
                               		}
                                       },"json");

                });                
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// RELLENAR DATOS DE TAB-1, ENCARGADO DE GRADO O IMPARTE ASIGNATURA EN GRADO.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$("#lstCodigoModalidad").change(function () {
	    	    var miselect=$("#lstCodigoGSTEG");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstCodigoModalidad option:selected").each(function () {
				elegido=$(this).val();
				annlectivo=$("#lstannlectivo").val();
				$.post("includes/cargar-grado-seccion.php", { ann: annlectivo, elegido: elegido},
				       function(data){
					miselect.empty();
                                        miselect.append("<option value=00>Seleccionar...</option>");
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno + '">' + data[i].descripcion_grado + " - " + data[i].descripcion_seccion + " - " + data[i].descripcion_turno + '</option>');
					}
			}, "json");			
	    });
	});
//////////////////////////////////////////////////////////////////////////////////
// BUSCAR ENCARGADO DE GRADO O IMPARTE ASIGNTURA EN OTROS GRADOS.
////////////////////////////////////////////////////////////////////////////////////
$('#goBuscarEG').on('click',function(){              
  // cambiar el valor de la variable accion.              
          var accion = "BuscarEG";              
          var codigo_docente = $("#lstCodigoPersonal").val();              
          var codigo_ann_lectivo = $("#lstannlectivo").val();              
  $.ajax({		      
      beforeSend: function(){		      
          // abrir caja de dialogo.		      
            $("label[for='lblOk']").text("");                    
            $("label[for='lblEG']").text("");                    
            $('#listaEG').empty();                    
      },		      
      cache: false,		      
      type: "POST",		      
      dataType: "json",		      
      url:"php_libs/soporte/Personal/CrearEG.php",		      
      data: "&codigo_annlectivo=" + codigo_ann_lectivo + "&codigo_docente=" + codigo_docente + "&accion=" + accion + "&id=" +  Math.random(),		      
      success: function(response){		      
      	// Validar mensaje de error		      
      	if(response.respuesta === false){		      
                          if(response.mensaje === "No Existe"){              
                                  toastr.info("Registros No Encontrados.");              
                                  $('#listaEG').empty();              
                                  $('#listaEG').append(response.contenido);              
                          }              
      	}		      
      	else if(response.respuesta === true && response.mensaje === 'Si Existe')		      
                          {              
                                  $('#listaEG').empty();              
                                  $('#listaEG').append(response.contenido);              
                                  toastr.success("Registro Encontrado.");              
                          }                                                      
                          // abrir caja de dialogo.              

      },		      
      error:function(){		      
                  toastr.error(":(");                                    
          //alert('Error de la función.');		      

      }		      
  });		      
  });                     
////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////proceso de los botones al dar clic.//////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////
// GO GUARDAR ENCARGADO DE GRADO*************************************************************************
$('#goAgregarEG').on('click',function(){              
  // cambiar el valor de la variable accion.              
    var accion = "GuardarEG";              
	var codigo_docente = $("#lstCodigoPersonal").val();              
    var codigo_modalidad = $("#lstCodigoModalidad").val();              
    var codigo_gst = $("#lstCodigoGSTEG").val();              
    var codigo_ann_lectivo = $("#lstannlectivo").val();                              
  //variables checked              
	var eg1 = "no"; var ia1 = "no";              
	if($('#EG1').is(":checked")) {eg1 = 'yes';}                     
	if($('#IA1').is(":checked")) {ia1 = 'yes';}                             
// Veirificar check.                                
    if(codigo_gst === null || codigo_gst === '00'){                 
       toastr.error("Debe Seleccionar - Grado - Sección - Turno.");   
		    return;
    }
// INICIO DE AJAX.	
	$.ajax({		      
      beforeSend: function(){		      
		// abrir caja de dialogo.		      
            $('#listaEG').empty();              
            $("label[for='lblOk']").text("");              
            $("label[for='lblEG']").text("");              
      },		      
      cache: false,		      
      type: "POST",		      
      dataType: "json",		      
      url:"php_libs/soporte/Personal/CrearEG.php",		      
      data: "&encargado_grado=" + eg1 + "&imparte_asignatura=" + ia1 +  "&codigo_annlectivo=" + codigo_ann_lectivo + "&codigo_docente=" + codigo_docente +  		      
            "&codigo_modalidad=" + codigo_modalidad + "&codigo_gst=" + codigo_gst + "&accion=" + accion + "&id=" +  Math.random(),              
      success: function(response){		      
      	// Validar mensaje de error		      
      	if(response.respuesta === false){		      
           if(response.mensaje === "Si Existe"){                             
                   toastr.error("Registro Ya Existe.");                             
                   $("label[for='lblEG']").text("Registro Ya Existe.");                             
           }                             
      	}		      
      	else if(response.respuesta === true && response.mensaje === 'Si Save')		      
           {                             
                   toastr.success("Registro Guardado.");                             
                   $("label[for='lblEG']").text("Registro Guardado.");                             
           }                                                                     
      },		      
      error:function(){		      
                  toastr.error(":(");                                    
      }		      
	});		                
});
//////////////////////////////////////////////////////////////////////////////////
// TAB-1. Extracciòn del valor que va utilizar para Eliminar y Edición de Registros
////////////////////////////////////////////////////////////////////////////////////
$('body').on('click','#listaEG a',function (e){
	e.preventDefault();        
	// Id Usuario 	    
		IdEG = $(this).attr('href');		
		Accion_Editar_Eliminar = $(this).attr('data-accion');		
    // Rturina per editar el registro.        
       if( Accion_Editar_Eliminar == 'editar')                         
		{
			// código script pendiente
		}else if(Accion_Editar_Eliminar == 'eliminarEG'){
			// mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
			$('#myModalEliminarEG').modal('show');
		}

});
/**** BOTON ELIMINAR Y CANCELAR DEL MODAL. /*****/
$('#goEliminarEG').on('click',function(){              
	$('#myModalEliminarEG').modal('hide');
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Diálogo confirmación de eliminación
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$.ajax({
		cache: false,     
		type: "POST",     
		dataType: "json",     
        url:"php_libs/soporte/Personal/CrearEG.php",           
		data:"accion=" + Accion_Editar_Eliminar + "&id_eg=" + IdEG + "&id=" + Math.random(),     
		success: function(response){     
		    // Validar mensaje de error     
		       	if(response.respuesta === false){     
		       		toastr.error(response.mensaje);     
		       	}     
		       	else{     
             // si es exitosa la operación           
		            $('#listaEG').empty();     
		            $('#listaEG').append(response.contenido);
					toastr.info("Registro Eliminado.");
					}	
		       },     
		       error:function(){     
		           toast.error(':(');     
		       }     
	});     
});
/**** BOTON ELIMINAR Y CANCELAR DEL MODAL. /*****/
$('#goCerrarEG').on('click',function(){              
	$('#myModalEliminarEG').modal('hide');
});     
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// RELLENAR DATOS DE TAB-2, CARGA DOCENTE.
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// SELECT UTILIZANDOS.
	$("#lstCodigoModalidadCD").change(function () {
	    	    var miselect=$("#lstCodigoGSTCD");
		    /* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</option>').val('');
			
   		$("#lstCodigoModalidadCD option:selected").each(function () {
				elegido=$(this).val();
				annlectivo=$("#lstannlectivo").val();
				$.post("includes/cargar-grado-seccion.php", { ann: annlectivo, elegido: elegido},
				       function(data){
					miselect.empty();
                                        miselect.append("<option value=00>Seleccionar...</option>");
					for (var i=0; i<data.length; i++) {
						miselect.append('<option value="' + data[i].codigo_grado + data[i].codigo_seccion + data[i].codigo_turno + '">' + data[i].descripcion_grado + " - " + data[i].descripcion_seccion + " - " + data[i].descripcion_turno + '</option>');
					}
			}, "json");			
	    });
	});
// Parametros para la asignatura.
	    $("#lstCodigoGSTCD").change(function () {
		      var miselect=$("#lstCodigoAsignaturaCD");
		      	/* VACIAMOS EL SELECT Y PONEMOS UNA OPCION QUE DIGA CARGANDO... */
			miselect.find('option').remove().end().append('<option value="">Cargando...</op.tion>').val('');
			
			 $("#lstCodigoGSTCD option:selected").each(function () {
					 elegido=$(this).val();
					 bach=$("#lstCodigoModalidadCD").val();
					 ann=$("#lstannlectivo").val();
					 $.post("includes/cargar-asignatura.php", { elegido: elegido, annlectivo: ann, modalidad: bach },
					 function(data){
					  miselect.empty();
					    for (var j=0; j<data.length; j++) {
						miselect.append('<option value="' + data[j].codigo_asignatura + '">' + data[j].nombre_asignatura + '</option>');
					    }
				 }, "json");			
		 });
	    });
/************************************************************************************************
//      GO BUSCAR CARGA DOCENTE (ACADEMICA)
/*************************************************************************************************/
$('#goBuscarCD').on('click',function(){              
    // cambiar el valor de la variable accion.              
        accion = "BuscarCD";              
        codigo_docente = $("#lstCodigoPersonal").val();              
        codigo_asignatura = $("#lstCodigoAsignaturaCD").val();              
        codigo_modalidad = $("#lstCodigoModalidadCD").val();              
        codigo_gst = $("#lstCodigoGSTCD").val();              
        codigo_ann_lectivo = $("#lstannlectivo").val();            
    // llamar funcion.
        BuscarCargarAcademica();                            
  });
// ************************************************************************************************
// GO GUARDAR CARGA DOCENTE            //
// ************************************************************************************************
$('#goAgregarCD').on('click',function(){
// cambiar el valor de la variable accion.
    accion = "GuardarCD";
    codigo_docente = $("#lstCodigoPersonal").val();
    codigo_asignatura = $("#lstCodigoAsignaturaCD").val();
    codigo_modalidad = $("#lstCodigoModalidadCD").val();
    codigo_gst = $("#lstCodigoGSTCD").val();
    codigo_ann_lectivo = $("#lstannlectivo").val();
        
$.ajax({
    beforeSend: function(){
        // abrir caja de dialogo.
                $('#listaCD').empty();
                $("label[for='lblOk']").text("");
                $("label[for='lblCD']").text("");
    },
    cache: false,
    type: "POST",
    dataType: "json",
    url:"php_libs/soporte/Personal/CrearCD.php",
    data: "&codigo_annlectivo=" + codigo_ann_lectivo + "&codigo_docente=" + codigo_docente +  "&codigo_asignatura=" + codigo_asignatura +
                                        "&codigo_modalidad=" + codigo_modalidad + "&codigo_gst=" + codigo_gst + "&accion=" + accion + "&id=" +  Math.random(),
    success: function(response){
        // Validar mensaje de error
        if(response.respuesta === false){
            if(response.mensaje === "Si Existe"){
                toastr.error("Registro Ya Existe.");
                $("label[for='lblCD']").text("Registro Ya Existe.");
            }
        }
        else if(response.respuesta === true && response.mensaje === 'Si Save')
            {
                toastr.success("Registro Guardado.");
                $("label[for='lblCD']").text("Registro Guardado.");
                accion = "BuscarCD";
                BuscarCargarAcademica();
            }                                        
    },
    error:function(){
                toastr.error(":(");                      
    }
});
});
/*//////////////////////////////////////////////////////////////////////////////////////////////////////*/
// //////////////////////////////////////////////////////////////////////////////////*/
// Extracciòn del valor que va utilizar para Eliminar y Edición de Registros////////////////////////////////////////////////////////////////////////////////////
$('body').on('click','#listaCD a',function (e){
	e.preventDefault();
	// Id Usuario
	IdCD = $(this).attr('href');
	Accion_Editar_Eliminar = $(this).attr('data-accion');
    // Rturina per editar el registro.        
       if( Accion_Editar_Eliminar == 'editar')                         
		{	
			}else if(Accion_Editar_Eliminar == 'eliminarCD'){
				$('#myModalEliminarCD').modal('show');
        }
	});
/**** BOTON ELIMINAR Y CANCELAR DEL MODAL. /*****/
$('#goEliminarCD').on('click',function(){              
	$('#myModalEliminarCD').modal('hide');
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Diálogo confirmación de eliminación
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$.ajax({
		cache: false,     
		type: "POST",     
		dataType: "json",     
        url:"php_libs/soporte/Personal/CrearCD.php",           
		data:"accion=" + Accion_Editar_Eliminar + "&id_cd=" + IdCD + "&id=" + Math.random(),     
		success: function(response){     
		    // Validar mensaje de error     
		       	if(response.respuesta === false){     
		       		toastr.error(response.mensaje);     
		       	}     
		       	else{     
             // si es exitosa la operación
                toastr.info("Registro Eliminado.");
                BuscarCargarAcademica();
					}	
		       },     
		       error:function(){     
		           toast.error(':(');     
		       }     
	});     
});
/**** BOTON ELIMINAR Y CANCELAR DEL MODAL. /*****/
$('#goCerrarCD').on('click',function(){              
	$('#myModalEliminarCD').modal('hide');
}); 
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////            
// funcionalidad del botón crear HOJA DE CALCULO.
$('#goCrearHC').on('click',function(){                
        var codigo_annlectivo = $("#lstannlectivo").val();
		var nombre_personal = $("#lstCodigoPersonal option:selected").text();
		var codigo_personal = $("#lstCodigoPersonal").val();                
        var pendiente = "no";
        //variables checked                
         var trimestre_1 = "no"; var trimestre_2 = "no"; var trimestre_3 = "no"; var trimestre_4 = "no";                
                if($('#T1').is(":checked")) {trimestre_1 = 'yes';}                
                if($('#T2').is(":checked")) {trimestre_2 = 'yes';}                
                if($('#T3').is(":checked")) {trimestre_3 = 'yes';}                
                if($('#T4').is(":checked")) {trimestre_4 = 'yes';}   
                if($('#Pendiente').is(":checked")) {pendiente = 'yes';}                
        // Evaluar Ejecución de Asignatutra Pendiente o Actual.
        if(pendiente == "yes")
        {
            $.ajax({		        
                beforeSend: function(){		        
                // abrir caja de dialogo.		        
                    $("label[for='NombreArchivo']").text(nombre_personal);
                // mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
                    $('#myModal').modal('show');
                // Ocultar div de mensajes.
                    $('#Informacion').hide();
                    $('#InformacionError').hide();
                    $('#Informacion').empty();
                    $('#InformacionError').empty();
                },		        
                cache: false,		        
                type: "POST",		        
                dataType: "json",		        
                url:"php_libs/soporte/Personal/CrearHCPendientes.php",		        
                data: "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_docente=" + codigo_personal + "&t1=" + trimestre_1 + "&t2=" + trimestre_2 + "&t3=" + trimestre_3 + "&t4=" + trimestre_4 + "&id=" +  Math.random(),		        
                success: function(response){		        
                    // Validar mensaje de error		        
                    if(response.respuesta === false){		        
                        toastr.info(response.mensaje);
                        $('#InformacionError').show();
                        $("#InformacionError").append(response.contenido);                
                    }		        
                    else{		        
                    if (response.respuesta ==  true) {                
                        toastr.success("Archivo Creado.");
                        $('#Informacion').show();
                        $("#Informacion").append(response.contenido);
                        $("label[for='NombreDescarga']").text(response.mensaje);
                        // Información de la Tabla.
                        $('#listaArchivoOK').append("<tr><td>Archivo Creado.</tr></td>");
                        $('#listaArchivoOK').append("<tr><td class=text-success>"+response.mensajeErrorTabla+"</tr></td>");
                    }                                                        
                    }                               
                },		        
                error:function(){		        
                toastr.error(":(");               
                }		        
            });		
        }else{
            $.ajax({		        
                beforeSend: function(){		        
                // abrir caja de dialogo.		        
                    $("label[for='NombreArchivo']").text(nombre_personal);
                // mostra rel modal. que contiene el mensaje del nombre del archivo y mensajes de veririvación o actualización.
                    $('#myModal').modal('show');
                // Ocultar div de mensajes.
                    $('#Informacion').hide();
                    $('#InformacionError').hide();
                    $('#Informacion').empty();
                    $('#InformacionError').empty();
                },		        
                cache: false,		        
                type: "POST",		        
                dataType: "json",		        
                url:"php_libs/soporte/Personal/CrearHC.php",		        
                data: "&codigo_annlectivo=" + codigo_annlectivo + "&codigo_docente=" + codigo_personal + "&t1=" + trimestre_1 + "&t2=" + trimestre_2 + "&t3=" + trimestre_3 + "&t4=" + trimestre_4 + "&id=" +  Math.random(),		        
                success: function(response){		        
                    // Validar mensaje de error		        
                    if(response.respuesta === false){		        
                        toastr.info(response.mensaje);
                        $('#InformacionError').show();
                        $("#InformacionError").append(response.contenido);                
                    }		        
                    else{		        
                    if (response.respuesta ==  true) {                
                        toastr.success("Archivo Creado.");
                        $('#Informacion').show();
                        $("#Informacion").append(response.contenido);
                        $("label[for='NombreDescarga']").text(response.mensaje);
                        // Información de la Tabla.
                        $('#listaArchivoOK').append("<tr><td>Archivo Creado.</tr></td>");
                        $('#listaArchivoOK').append("<tr><td class=text-success>"+response.mensajeErrorTabla+"</tr></td>");
                    }                                                        
                    }                               
                },		        
                error:function(){		        
                toastr.error(":(");               
                }		        
            });		   
        }   // FIN DEL INF ASIGNTUAS PENDIENTES     
});
/**** BOTON PARA DESCARGAR EL ARCHIVO CREADO. /*****/
$('#goDescargar1').on('click',function(){              
	var urlDescarga = $("#NombreDescarga").text();
	alert(urlDescarga);
    location.href = urlDescarga;

}); 
});     // FINAL DE LA FUNCIÓN.
// Abrir Ventana Emergente.
function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}
// llamar a la buscar de Carga Académica.
function BuscarCargarAcademica() {
    // inicio del ajax
  $.ajax({		      
    beforeSend: function(){		      
        // abrir caja de dialogo.		      
                $("label[for='lblOk']").text("");              
                $("label[for='lblCD']").text("");              
                $('#listaCD').empty();              
    },		      
    cache: false,		      
    type: "POST",		      
    dataType: "json",		      
    url:"php_libs/soporte/Personal/CrearCD.php",		      
    data: "&codigo_annlectivo=" + codigo_ann_lectivo + "&codigo_docente=" + codigo_docente +  "&codigo_asignatura=" + codigo_asignatura +		      
          "&codigo_modalidad=" + codigo_modalidad + "&codigo_gst=" + codigo_gst + "&accion=" + accion + "&id=" +  Math.random(),              
    success: function(response){		      
        // Validar mensaje de error		      
        if(response.respuesta === false){		      
          if(response.mensaje === "No Existe"){              
              toastr.info("Registros No Encontrados.");              
              $('#listaCD').empty();              
              $('#listaCD').append(response.contenido);              
          }              
        }		      
        else if(response.respuesta === true && response.mensaje === 'Si Existe')		      
          {              
          $('#listaCD').empty();              
          $('#listaCD').append(response.contenido);              
          toastr.success("Registro Encontrado.");              
          }                                                      
    },		      
    error:function(){		      
      toastr.error(":(");                                    
    }		      
});
}