$(document).ready(function () {
/*----------Funcion para obtener el tiempo en dias y horas------------*/
function calcular_tiempo_12_24() {
// variables
  var hours = 0;
  var meridian = "";
  var minutes = "";
  var hours_1 = 0;
  var meridian_1 = "";
  var minutes_1 = "";
// fecha 1.
  timeSplit = $('#HoraDesde').val();
  timeSplit = timeSplit.split(':');
// fecha 2.
  timeSplit_1 = $('#HoraHasta').val();
  timeSplit_1 = timeSplit_1.split(':');
// matriz 0 fecha
  hours = timeSplit[0];
  minutes = timeSplit[1];
// matriz 1 fecha
  hours_1 = timeSplit_1[0];
  minutes_1 = timeSplit_1[1];
// condiconamiento de 24 a 12 formato. 1
  if (hours > 12) {
    meridian = 'PM';
    hours -= 12;
  } else if (hours < 12) {
    meridian = 'AM';
    if (hours == 0) {
      hours = 12;
    }
  } else {
    meridian = 'PM';
  }
  // condiconamiento de 24 a 12 formato. 2
  if (hours_1 > 12) {
    meridian_1 = 'PM';
    hours_1 -= 12;
  } else if (hours_1 < 12) {
    meridian_1 = 'AM';
    if (hours_1 == 0) {
      hours_1 = 12;
    }
  } else {
    meridian_1 = 'PM';
  }
  //alert(hours + ':' + minutes + ' ' + meridian);
  
  $("label[for='LblHoraDesde']").text(hours + ':' + minutes + ' ' + meridian);
  $("label[for='LblHoraHasta']").text(hours_1 + ':' + minutes_1 + ' ' + meridian_1);
}
/*----------Funcion para obtener el tiempo en dias y horas------------*/
function calcular_tiempo() {
  /*----------Funcion PARA EL FORMATO 12 HORAS------------*/
  /*timeSplit = $('#hora_1_hasta').val();
  timeSplit = timeSplit.split(':');
  var hours = 0;
  var meridian = "";
  var minutes = "";
  // matriz
    hours = timeSplit[0];
    minutes = timeSplit[1];
    if (hours > 12) {
      meridian = 'PM';
      hours -= 12;
    } else if (hours < 12) {
      meridian = 'AM';
      if (hours == 0) {
        hours = 12;
      }
    } else {
      meridian = 'PM';
    }
    // Reemplazar label.
      $("label[for='end-time_2']").text(hours + ':' + minutes + ' ' + meridian);
/*----------Funcion PARA EL FORMATO 12 HORAS------------*/

// valor de la fecha
var tiempo_1_desde = $('#hora_1_desde').val();
var tiempo_1_hasta = $('#hora_1_hasta').val();
var dia = 0;

var codigo_tipo_contratacion = $('#lstContratacion_1 option:selected').val();
codigo_tipo_contratacion = codigo_tipo_contratacion.substr(0,2);
// VALIDAR CON RESPECTO A LA CONDICIONAL DE LA CONTRATACIÓN.
    if(codigo_tipo_contratacion == "05"){
      inicioMinutos = parseInt(tiempo_1_desde.substr(3,2));
      inicioHoras = parseInt(tiempo_1_desde.substr(0,2));
        
      finMinutos = parseInt(tiempo_1_hasta.substr(3,2));
      finHoras = parseInt(tiempo_1_hasta.substr(0,2));
      
        transcurridoMinutos = finMinutos - inicioMinutos;
        transcurridoHoras = finHoras - inicioHoras;
        
        if (transcurridoMinutos < 0) {
          transcurridoHoras--;
          transcurridoMinutos = 60 + transcurridoMinutos;
        }
        
        horas = transcurridoHoras.toString();
        minutos = transcurridoMinutos.toString();
        
        if (horas.length < 2) {
          horas = ""+horas;
        }
      
        if(horas >= 8){
          dia = 1;
          horas = 0;
          minutos = 0;
          tiempo_calculado_1 = dia;
        }
        
        if(horas < 8){
          
          tiempo_calculado_1 = horas+":"+minutos;
          }
      
      // Pasar los valores.
        $("#tiempo_calculado_1").val(tiempo_calculado_1);
        $("#dia").val(dia);
        $("#hora").val(horas);
        $("#minutos").val(minutos);
    }else{
      inicioMinutos = parseInt(tiempo_1_desde.substr(3,2));
      inicioHoras = parseInt(tiempo_1_desde.substr(0,2));
        
      finMinutos = parseInt(tiempo_1_hasta.substr(3,2));
      finHoras = parseInt(tiempo_1_hasta.substr(0,2));
      
        transcurridoMinutos = finMinutos - inicioMinutos;
        transcurridoHoras = finHoras - inicioHoras;
        
        if (transcurridoMinutos < 0) {
          transcurridoHoras--;
          transcurridoMinutos = 60 + transcurridoMinutos;
        }
        
        horas = transcurridoHoras.toString();
        minutos = transcurridoMinutos.toString();
        
        if (horas.length < 2) {
          horas = ""+horas;
        }
      
        if(horas == 5){
          dia = 1;
          horas = 0;
          minutos = 0;
          tiempo_calculado_1 = dia;
        }
        
        if(horas < 5){
          
          tiempo_calculado_1 = horas+":"+minutos;
          }
      
      // Pasar los valores.
        $("#tiempo_calculado_1").val(tiempo_calculado_1);
        $("#dia").val(dia);
        $("#hora").val(horas);
        $("#minutos").val(minutos);
    }
}

function calcular_tiempo_m() {
var tiempo_1_desde = $('#hora_1_desde_m').val();
var tiempo_1_hasta = $('#hora_1_hasta_m').val();

var dia = 0;

inicioMinutos = parseInt(tiempo_1_desde.substr(3,2));
inicioHoras = parseInt(tiempo_1_desde.substr(0,2));
  
finMinutos = parseInt(tiempo_1_hasta.substr(3,2));
finHoras = parseInt(tiempo_1_hasta.substr(0,2));

  transcurridoMinutos = finMinutos - inicioMinutos;
  transcurridoHoras = finHoras - inicioHoras;
  
  if (transcurridoMinutos < 0) {
    transcurridoHoras--;
    transcurridoMinutos = 60 + transcurridoMinutos;
  }
  
  horas = transcurridoHoras.toString();
  minutos = transcurridoMinutos.toString();
  
  if (horas.length < 2) {
    horas = ""+horas;
  }

  if(horas == 5){
    dia = 1;
    horas = 0;
    minutos = 0;
    tiempo_calculado_1 = dia;
  }
  
  if(horas < 5){
    
    tiempo_calculado_1 = horas+":"+minutos;
    }

// Pasar los valores.
  $("#tiempo_calculado_1_m").val(tiempo_calculado_1);
  $("#dia_m").val(dia);
  $("#hora_m").val(horas);
  $("#minutos_m").val(minutos);
  }
      // REACULTAR A FORMATO 12 H LABEL.
    calcular_tiempo_12_24();
});