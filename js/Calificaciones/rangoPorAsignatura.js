$(document).ready(function () {
    let tablaRango = null;
    let graficoRangos = null;

    $('#btnRangoCalificaciones').on('click', function () {
        const annLectivo = $('#lstannlectivo').val();
        const modalidad = $('#lstmodalidad').val();
        const gradoSeccion = $('#lstgradoseccion').val();
        const periodo = $('#lstperiodoC').val();

        $.ajax({
            url: 'php_libs/soporte/rango_calificaciones.php',
            type: 'POST',
            dataType: 'json',
            data: { annLectivo, modalidad, gradoSeccion, periodo },
            beforeSend: function () {
                console.log('Enviando datos...');
            },
            success: function (response) {
                if (response.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la consulta',
                        text: response.error,
                        footer: `<pre style="text-align:left;">Campo: ${response.campo_periodo || ''}\nCódigo ALL: ${response.codigo_all || ''}</pre>`
                    });
                    return;
                }

                if (Array.isArray(response) && response.length > 0) {
                    const textoAnio = $('#lstannlectivo option:selected').text();
                    const textoModalidad = $('#lstmodalidad option:selected').text();
                    const textoGrado = $('#lstgradoseccion option:selected').text();
                    const textoPeriodo = $('#lstperiodoC option:selected').text();
                    const titulo = `Rangos de Calificaciones - ${textoAnio} - ${textoModalidad} - ${textoGrado} - ${textoPeriodo}`;

                    // Mostrar resumen
                    $('#resumenFiltros').html(`
                        Año Lectivo: ${textoAnio} |
                        Modalidad: ${textoModalidad} |
                        Grado/Sección: ${textoGrado} |
                        Período: ${textoPeriodo}
                    `);

                    // Mostrar modal
                    $('#modalRangoCalificaciones').modal('show');
                    $('#graficaRangos').hide();
                    $('#toggleGrafica').text('Mostrar Gráfica');

                    // Limpiar y poblar tabla
                    if (tablaRango) {
                        tablaRango.clear().destroy();
                    }

                    $('#tablaRango tbody').empty();
                    response.forEach(item => {
                        $('#tablaRango tbody').append(`
                            <tr>
                                <td>${item.nombre_asignatura}</td>
                                <td>${item.menor_5}</td>
                                <td>${item.entre_5_7}</td>
                                <td>${item.mayor_7}</td>
                            </tr>
                        `);
                    });

                    // Re-inicializar DataTable
                    tablaRango = $('#tablaRango').DataTable({
                        destroy: true,
                        dom: 'Bfrtip',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                title: titulo,
                                text: '<i class="fa fa-file-excel"></i>',
                                className: 'btn btn-success'
                            },
                            {
                                extend: 'pdfHtml5',
                                title: titulo,
                                text: '<i class="fa fa-file-pdf"></i>',
                                className: 'btn btn-danger',
                                orientation: 'landscape',
                                pageSize: 'A4'
                            },
                            {
                                extend: 'print',
                                title: titulo,
                                text: '<i class="fa fa-print"></i>',
                                className: 'btn btn-secondary'
                            }
                        ],
                        language: {
                            url: 'php_libs/idioma/es_es.json'
                        }
                    });

                    // Preparar datos para gráfica
                    const nombresAsignaturas = [];
                    const menores5 = [], entre5y7 = [], mayores7 = [];

                    response.forEach(row => {
                        const total = parseInt(row.menor_5) + parseInt(row.entre_5_7) + parseInt(row.mayor_7);
                        nombresAsignaturas.push(row.nombre_asignatura);
                        if (total > 0) {
                            menores5.push(((row.menor_5 / total) * 100).toFixed(1));
                            entre5y7.push(((row.entre_5_7 / total) * 100).toFixed(1));
                            mayores7.push(((row.mayor_7 / total) * 100).toFixed(1));
                        } else {
                            menores5.push(0);
                            entre5y7.push(0);
                            mayores7.push(0);
                        }
                    });

                    // Generar gráfica
                    if (graficoRangos) {
                        graficoRangos.destroy();
                    }

                    const ctx = document.getElementById('graficaRangos').getContext('2d');
                    graficoRangos = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: nombresAsignaturas,
                            datasets: [
                                {
                                    label: '< 5',
                                    data: menores5,
                                    backgroundColor: 'rgba(255, 99, 132, 0.6)'
                                },
                                {
                                    label: '5 - 6.99',
                                    data: entre5y7,
                                    backgroundColor: 'rgba(255, 206, 86, 0.6)'
                                },
                                {
                                    label: '≥ 7',
                                    data: mayores7,
                                    backgroundColor: 'rgba(75, 192, 192, 0.6)'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' },
                                title: {
                                    display: true,
                                    text: `Rangos de Calificaciones - ${textoPeriodo}`
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 45
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Porcentaje (%)'
                                    },
                                    max: 100
                                }
                            }
                        }
                    });
                        // Evento para alternar visibilidad de la gráfica
                        $('#toggleGrafica').off('click').on('click', function () {
                            const canvas = $('#graficaRangos');
                            if (canvas.is(':visible')) {
                                canvas.hide();
                                $(this).text('Mostrar Gráfica');
                            } else {
                                canvas.show();
                                $(this).text('Ocultar Gráfica');
                            }
                        });
                    
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: 'No se encontraron datos para los filtros seleccionados.'
                    });
                }
            },
            error: function (xhr, status, error) {
                console.error('Error AJAX:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error AJAX',
                    text: `Estado: ${status}, Error: ${error}`
                });
            }
        });
    });
});
