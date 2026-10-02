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
 * Calcula la diferencia entre hora inicio y fin, determinando si equivale a días u horas.
 */
function calcular_tiempo() {
    const horaDesde = $('#HoraDesde').val();
    const horaHasta = $('#HoraHasta').val();
    const tipoContratacion = $('#lstTipoContratacion option:selected').val() || "00";

    if (!horaDesde || !horaHasta) return;

    const [hInicio, mInicio] = horaDesde.split(':').map(Number);
    const [hFin, mFin] = horaHasta.split(':').map(Number);

    let minutosInicio = hInicio * 60 + mInicio;
    let minutosFin = hFin * 60 + mFin;

    // Si la hora final es menor a la inicial, se asume cambio de día
    if (minutosFin < minutosInicio) {
        minutosFin += 24 * 60;
    }

    let diferenciaMinutos = minutosFin - minutosInicio;
    let horas = Math.floor(diferenciaMinutos / 60);
    let minutos = diferenciaMinutos % 60;
    let dias = 0;

    // Extraer código de tipo de contratación (ej. "05" para personal administrativo/CDE)
    const codigoTipo = tipoContratacion.substring(0, 2);
    const umbralHorasDia = (codigoTipo === "05") ? 8 : 5; // 8 horas para administrativos, 5 para docentes

    if (horas >= umbralHorasDia) {
        dias = 1;
        horas = 0;
        minutos = 0;
    }

    const tiempoTexto = `${dias} Día(s) ${horas} Horas ${minutos} Minutos.`;

    // Asignar valores a la interfaz y campos ocultos
    $("#SpanDiasHoras").text(tiempoTexto);
    $("#Dia").val(dias);
    $("#Hora").val(horas);
    $("#Minutos").val(minutos);
}