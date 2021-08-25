var accion = "";
var BarMatricula = ""; var BarMatriculaNuevoIngreso = "";
var total_matricula = 0;
var annlectivo = "";

$(function(){    
      // funcionalidad del botón Actualizar
	$("#lstannlectivo").change(function () {
			annlectivo=$("#lstannlectivo").val();
        	accion = "GraficoIndicadores";
// INICIO DE CREACIÓN DEL GRÁFICO.
	$.ajax({
			cache: false,
			type: "POST",
			dataType: "json",
			url:"php_libs/soporte/CrearGraficoIndicadores.php",
			data: {
					accion: accion, codigo_ann_lectivo: annlectivo,
					},
						success: function(data) {
							// Variables y Matrices.
							var nombre_m = []; var matricula_m = []; var matricula_f = []; var repitente_m = []; var repitente_f = []; var retirado_m = []; var retirado_f = []; var sobreedad_m = []; var sobreedad_f = [];
							var nuevo_ingreso_m = []; var nuevo_ingreso_f = [];
							var nombre_modalidad = []; var nombre_turno = []; 
							//
							var matricula_ciclo_m = []; var matricula_ciclo_f = [];
							var total_matricula_ciclo_m = []; var total_matricula_ciclo_f = [];
							var total_matricula_m_f = [];
							// Recorremos la matriz DATA para obtener los valores.
							for(var i in data){
								if(typeof data[i].nombre_modalidad == "undefined"){
									
								}else{
									if(data[i].matricula_masculino > 0){
										if(data[i].nombre_modalidad == 'Parvularia'){nombre_m.push('Parv.')};
										if(data[i].nombre_modalidad == 'Primer Ciclo'){nombre_m.push('I Ciclo')};
										if(data[i].nombre_modalidad == 'Segundo Ciclo'){nombre_m.push('II Ciclo')};
										if(data[i].nombre_modalidad == 'Tercer Ciclo'){nombre_m.push('III Ciclo')};
										if(data[i].nombre_modalidad == 'Bachillerato General'){nombre_m.push('General')};
										if(data[i].nombre_modalidad == 'Bachillerato Técnico Vocacional Comercial'){nombre_m.push('TVC')};
										if(data[i].nombre_modalidad == 'Bachillerato Técnico Vocacional Comercial Opción Contaduría'){nombre_m.push('TVC Con.')};
										if(data[i].nombre_modalidad == 'TERCER CICLO - MF-NOCTURNA'){nombre_m.push('III Ciclo N.')};
										if(data[i].nombre_modalidad == 'Bachillerato General - MF - Nocturna'){nombre_m.push('General N.')};
										// Modalidad y turno.
										nombre_modalidad.push(data[i].nombre_modalidad);
										nombre_turno.push(data[i].nombre_turno);
										// Matricula masculino y femenino.
										matricula_m.push(data[i].matricula_masculino);
										matricula_f.push(data[i].matricula_femenino);
										// Nuevo Ingreso masculino y femenino.
										nuevo_ingreso_m.push(data[i].nuevo_ingreso_masculino);
										nuevo_ingreso_f.push(data[i].nuevo_ingreso_femenino);
										// Retirados masculino y femenino.
										retirado_m.push(data[i].retirado_masculino);
										retirado_f.push(data[i].retirado_femenino);
										// Retirados masculino y femenino.
										repitente_m.push(data[i].repitente_masculino);
										repitente_f.push(data[i].repitente_femenino);
										// Sobreedad masculino y femenino.
										sobreedad_m.push(data[i].sobreedad_masculino);
										sobreedad_f.push(data[i].sobreedad_femenino);
										// Total turno.
										total_matricula_ciclo_m.push(data[i].matricula_ciclo_m);
										total_matricula_ciclo_f.push(data[i].matricula_ciclo_f);
										total_matricula_m_f.push(data[i].matricula_ciclo);
									}
								}
							}
							// sumar valores de la matricula masculino, femenino y total.
								document.getElementById("MatriculaMasculino").innerHTML = matricula_m.reduce(getSum);
								document.getElementById("MatriculaFemenino").innerHTML = matricula_f.reduce(getSum);
								var total = parseInt(matricula_m.reduce(getSum)) + parseInt(matricula_f.reduce(getSum));
								document.getElementById("MatriculaTotal").innerHTML = total;

								// Return with commas in between
								var numberWithCommas = function(x) {
									return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
								};
								// VALORES DE LOS ARRAY
								var dataMatricula_m = matricula_m;
								var dataMatricula_f = matricula_f;
								var dataRetirados_m = retirado_m;
								var dataRetirados_f = retirado_f;
								var dates = nombre_m;
								// LIMPIAR LA TABLA CON DATES.
									$('#listaMatricula').empty();
									var nombres_turno = ["Matutino","Vespertino","Nocturna"];
									var linea = ""; var linea_numero = 0;
									var total_matricula = 0; var total_masculino = 0; var total_femenino = 0; var total_modalidad = 0;
									var total_retirado_masculino = 0; var total_retirado_femenino = 0;
									// Recorremos la matriz Nombre Modalidad para obtener los valores.
									for(var i in nombre_m){
										// VARIABLES PARA LA TABLA.
											linea_numero++;
											// retirados.
											total_retirado_masculino += retirado_m[i];
											total_retirado_femenino += retirado_f[i];
											// matricula
											//console.log("Retirado Masculino: " + retirado_m[i]);
											// Matricula - Retirados.
											total_matricula = (matricula_m[i] + matricula_f[i]) - (retirado_m[i] + retirado_f[i]);
											matricula_ciclo_m = matricula_m[i] - retirado_m[i];
											matricula_ciclo_f = matricula_f[i] - retirado_f[i];
											// consolidado
											total_masculino += matricula_m[i];
											total_femenino += matricula_f[i];
											//resumen 
											total_modalidad += total_matricula;
											// Imprimir el sub-total.
											if(nombre_m[i] == "Sub-Total"){
												linea = "<tr><td>" +  "<td>" + nombre_modalidad[i] + "<td>" + "<td class=text-right>" + total_matricula_ciclo_m + "<td class=text-right>" + total_matricula_ciclo_f + "<td class=text-right>" + total_matricula_m_f +"</tr>";
											}else{
												linea = "<tr><td>" + linea_numero + "<td>" + nombre_modalidad[i] + "<td>" + nombre_turno[i] + "<td class=text-right>" + matricula_ciclo_m + "<td class=text-right>" + matricula_ciclo_f + "<td class=text-right>" + total_matricula +"</tr>";
											}
													// RELLENAR VALORES EN LA TABLA                     
														$('#listaMatricula').append(linea);													
											}
											total_masculino = total_masculino - total_retirado_masculino;
											total_femenino = total_femenino - total_retirado_masculino;
											total_modalidad = (total_masculino + total_femenino);
											//console.log("Retirado Masculino: " + total_retirado_masculino);
											//console.log("Retirado Masculino: " + total_retirado_femenino);
											// Crear Cadena
												linea = "<tr><td>" + "<td>" +  "<td>" + "<td class='text-right font-weight-bolder'>" + total_masculino + "<td class='text-right font-weight-bolder'>" + total_femenino + "<td class='text-right font-weight-bolder'>" + total_modalidad +"</tr>";
											// RELLENAR VALORES EN LA TABLA                     
												$('#listaMatricula').append(linea);
								// Chart.defaults.global.elements.rectangle.backgroundColor = '#FF0000';

								var bar_ctx = document.getElementById('GraficoMatricula');
								var bar_chart = new Chart(bar_ctx, {
									type: 'bar',
									data: {
										labels: dates,
										datasets: [
										{
											label: 'Masculino',
											data: dataMatricula_m,
														backgroundColor: "#512DA8",
														hoverBackgroundColor: "#7E57C2",
														hoverBorderWidth: 0
										},
										{
											label: 'Femenino',
											data: dataMatricula_f,
														backgroundColor: "#FFA000",
														hoverBackgroundColor: "#FFCA28",
														hoverBorderWidth: 0
										},
										{
											label: 'Retirado Masculino',
											data: dataRetirados_m,
														backgroundColor: "#2980B9",
														hoverBackgroundColor: "#5DADE2",
														hoverBorderWidth: 0
										},
										{
											label: 'Retirado Femenino',
											data: dataRetirados_f,
														backgroundColor: "#C0392B",
														hoverBackgroundColor: "#E74C3C",
														hoverBorderWidth: 0
										},
										]
									},
									options: {
											animation: {
											duration: 10,
										},
										tooltips: {
													mode: 'label',
										callbacks: {
										label: function(tooltipItem, data) { 
											return data.datasets[tooltipItem.datasetIndex].label + ": " + numberWithCommas(tooltipItem.yLabel);
										}
										}
										},
										scales: {
										xAxes: [{ 
											stacked: true, 
											gridLines: { display: false },
											}],
										yAxes: [{ 
											stacked: true, 
											ticks: {
													callback: function(value) { return numberWithCommas(value); },
													}, 
											}],
										}, // scales
										legend: {display: true},
										title: {
											display: true, 
											text: 'Estadística - Turno: Matutino, Vespertino y Nocturna'
										}
									} // options
								}
								);
							PasarFoco();
						}	// FIN DEL SUCCESS.
		});	// FINAL DEL AJAX.
    });
});

// Pasar foco cuando seleccionar un encargado.
function PasarFoco()
   {
       $('#lstannlectivo').focus();
   }
// Sumar Valores de la matriz
function getSum(total, num) {
    total_matricula = parseInt(total) + parseInt(num);
    return parseInt(total) + parseInt(num);
}
