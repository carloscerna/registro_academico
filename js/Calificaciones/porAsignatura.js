$(function(){
        $(document).ready(function() {

                $("#lstperiodo").change(function() {
                    let idModalidad = $("#lstmodalidad").val();
                    let idGradoSeccion = $("#lstgradoseccion").val();
                    let idAnnLectivo = $("#lstannlectivo").val();
                    let idAsignatura = $("#lstasignatura").val();
                    let idPeriodo = $(this).val();
            
                    $.ajax({
                        url: "php_libs/soporte/Calificaciones/PorAsignatura.php",
                        type: "POST",
                        data: { 
                            action: "listarNomina", 
                            modalidad: idModalidad, 
                            gradoseccion: idGradoSeccion, 
                            annlectivo: idAnnLectivo, 
                            asignatura: idAsignatura, 
                            periodo: idPeriodo 
                        },
                        dataType: "json",
                        success: function(data) {
                                let tabla = $("#tablaNomina").DataTable({
                                        destroy: true,  // 📌 Asegura que no haya instancias previas de DataTables
                                        columns: [
                                            { data: "id_notas", visible: false },  // 📌 Campo oculto
                                            { data: "codigo_cc", visible: false },  // 📌 Campo oculto
                                            { data: "codigo_nie" },
                                            { data: "NombreEstudiante" },
                                            { data: "nota_a1_" + idPeriodo },
                                            { data: "nota_a2_" + idPeriodo },
                                            { data: "nota_a3_" + idPeriodo },
                                            { data: "nota_r_" + idPeriodo },
                                            { data: "nota_p_p_" + idPeriodo }
                                        ],
                                        paging: true,
                                        searching: true,
                                        order: [[2, "asc"]],
                                        language: { url: "php_libs/idioma/es_es.json" }
                                    });
                                //
                                tabla.clear().draw();
                                $.each(data, function(index, item) {
                                let rowHtml = [
                                        '<td style="display:none;" class="codigoCC">' + item.codigo_cc + '</td>',  // 📌 Campo oculto
                                        '<td style="display:none;" class="idNotas">' + item.id_notas + '</td>', // 📌 Campo oculto
                                        '<td>' + item.codigo_nie + '</td>',
                                        '<td>' + item.NombreEstudiante + '</td>',
                                        `<td><input type="number" class="notaInput" data-campo="nota_a1_${idPeriodo}" value="${item["nota_a1_" + idPeriodo]}"></td>`,
                                        `<td><input type="number" class="notaInput" data-campo="nota_a2_${idPeriodo}" value="${item["nota_a2_" + idPeriodo]}"></td>`,
                                        `<td><input type="number" class="notaInput" data-campo="nota_a3_${idPeriodo}" value="${item["nota_a3_" + idPeriodo]}"></td>`,
                                        `<td><input type="number" class="notaInput" data-campo="nota_r_${idPeriodo}" value="${item["nota_r_" + idPeriodo]}"></td>`,
                                        `<td><input type="number" class="notaPP" data-campo="nota_p_p_${idPeriodo}" value="${item["nota_p_p_" + idPeriodo]}" readonly></td>`
                                    ].join('');
                
                                    tabla.row.add($(rowHtml)).draw();
                                });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al obtener nómina: " + error);
                        }
                    });
                });
            });
});

function AbrirVentana(url)
{
    window.open(url, '_blank');
    return false;
}

// Mensaje de Carga de Ajax.
function configureLoadingScreen(screen){
        $(document)
        .ajaxStart(function () {
        screen.fadeIn();
})
        .ajaxStop(function () {
        screen.fadeOut();
        });
}