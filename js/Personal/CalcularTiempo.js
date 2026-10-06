/**
 * Convierte horas de formato 24h a 12h para representación visual.
 */
function calcular_tiempo_12_24() {
    const horaDesdeVal = $('#HoraDesde').val();
    const horaHastaVal = $('#HoraHasta').val();

    if (!horaDesdeVal || !horaHastaVal) return;

    const formatear12h = (hora24) => {
        let [hh, mm] = hora24.split(':').map(Number);
        const meridian = hh >= 12 ? 'PM' : 'AM';
        hh = hh % 12 || 12;
        const hhStr = hh < 10 ? '0' + hh : hh;
        const mmStr = mm < 10 ? '0' + mm : mm;
        return `${hhStr}:${mmStr} ${meridian}`;
    };

    $("#SpanHoraDesde").text(formatear12h(horaDesdeVal));
    $("#SpanHoraHasta").text(formatear12h(horaHastaVal));
}

/**
 * Calcula la diferencia entre hora inicio y fin, convirtiendo automáticamente
 * minutos a horas y horas a días según el tipo de personal.
 */
function calcular_tiempo() {
    const horaDesde = $('#HoraDesde').val();
    const horaHasta = $('#HoraHasta').val();
    
    // Obtener el valor seleccionado del tipo de contratación
    const tipoContratacion = $('#lstTipoContratacion option:selected').val() || "00";

    if (!horaDesde || !horaHasta) return;

    // 1. Extraer horas y minutos de las entradas de tiempo
    const [hInicio, mInicio] = horaDesde.split(':').map(Number);
    const [hFin, mFin] = horaHasta.split(':').map(Number);

    // 2. Convertir horas de inicio y fin a minutos totales del día
    let minutosInicio = hInicio * 60 + mInicio;
    let minutosFin = hFin * 60 + mFin;

    // Si la hora final es menor a la inicial, se asume que cruzó la medianoche
    if (minutosFin < minutosInicio) {
        minutosFin += 24 * 60;
    }

    // 3. Diferencia total en minutos
    let diferenciaMinutos = minutosFin - minutosInicio;

    // 4. Determinar la jornada laboral en horas según el código de contratación
    // Códigos 01, 02, 03 (Docentes, Directores y Subdirectores) -> 5 horas por día
    // Demás códigos -> 8 horas por día
    const codigoTipo = tipoContratacion.substring(0, 2);
    const codigosDocentes = ['01', '02', '03'];
    const horasPorDia = codigosDocentes.includes(codigoTipo) ? 5 : 8;

    // Total de minutos necesarios para completar 1 día de permiso
    const minutosPorDia = horasPorDia * 60;

    // 5. Cálculo exacto y acumulativo de Días, Horas y Minutos
    const dias = Math.floor(diferenciaMinutos / minutosPorDia);
    const minutosRestantesTrasDias = diferenciaMinutos % minutosPorDia;

    const horas = Math.floor(minutosRestantesTrasDias / 60);
    const minutos = minutosRestantesTrasDias % 60;

    // 6. Construir texto descriptivo para el usuario
    const tiempoTexto = `${dias} Día(s) ${horas} Horas ${minutos} Minutos.`;

    // 7. Asignar resultados a la interfaz y a los campos ocultos del formulario
    $("#SpanDiasHoras").text(tiempoTexto);
    $("#Dia").val(dias);
    $("#Hora").val(horas);
    $("#Minutos").val(minutos);
}

$(document).ready(function() {

    // Vincular la función calcular_tiempo a los eventos 'change', 'input' y 'blur'
    // Esto garantiza compatibilidad total con el selector de hora nativo de Chrome
    $('#HoraDesde, #HoraHasta').on('change input blur', function() {
        calcular_tiempo_12_24();
        calcular_tiempo();
    });

    // Recalcular automáticamente si el usuario cambia el tipo de contratación
    $('#lstTipoContratacion').on('change', function() {
        calcular_tiempo();
    });

});