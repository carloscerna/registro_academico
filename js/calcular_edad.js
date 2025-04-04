/*----------Funcion para obtener la edad------------*/
function calcular_edad(fecha) {
var fechaActual = new Date();
var diaActual = fechaActual.getDate();
var mmActual = fechaActual.getMonth() + 1;
var yyyyActual = fechaActual.getFullYear();
FechaNac = fecha.split("-");
var diaCumple = FechaNac[2];
var mmCumple = FechaNac[1];
var yyyyCumple = FechaNac[0];
//alert(FechaNac);
//retiramos el primer cero de la izquierda
if (mmCumple.substr(0,1) === 0) {
mmCumple= mmCumple.substring(1, 2);
}
//retiramos el primer cero de la izquierda
if (diaCumple.substr(0, 1) === 0) {
diaCumple = diaCumple.substring(1, 2);
}
var edad = yyyyActual - yyyyCumple;

//validamos si el mes de cumpleaños es menor al actual
//o si el mes de cumpleaños es igual al actual
//y el dia actual es menor al del nacimiento
//De ser asi, se resta un año
if ((mmActual < mmCumple) || (mmActual == mmCumple && diaActual < diaCumple)) {
edad--;
}
//return edad;
//Calcular edad más mes.
var edad_mes =  mmActual - mmCumple;
var text_edad_mes = "";

if (edad_mes > 0)
	{
		text_edad_mes = "Usted tiene " + edad + " años y " + edad_mes + " mes(es)";
	}
else
	{
		if (edad_mes < 0)
		{
			text_edad_mes = "Usted tiene " + (edad) + " años y " + ((12-mmCumple)+mmActual) + " mes(es)";
		}
		else
		{
			text_edad_mes = "Usted tiene " + edad + " años exactos";
		}
	}
// Pasar los valores al tabs-2. PN.
	$("label[for='lbl_edad_y_mes']").text(text_edad_mes);
	$('#txtedad').val(edad);
	$('#edad_enviar').val(edad);

//
	const inputFecha = convertirFecha(fecha); //document.getElementById('fecha').value;
	const resultado = document.getElementById('resultado');
	
	console.log(inputFecha);
	if (validarFecha(inputFecha)) {
		resultado.textContent = "Fecha válida";
		resultado.style.color = "green";
	} else {
		resultado.textContent = "Fecha no válida";
		resultado.style.color = "red";
		const fechaInput = document.getElementById('txtfechanacimiento');
		fechaInput.value = '';
		fechaInput.focus();
	}
}


function convertirFecha(fecha) {
	// Dividir la cadena de fecha en partes
	const partes = fecha.split('-');
	const anio = partes[0];
	const mes = partes[1];
	const dia = partes[2];
  
	// Reorganizar las partes en el formato dd/mm/yyyy
	const fechaConvertida = `${dia}/${mes}/${anio}`;
	return fechaConvertida;
  }
  
function validarFecha(fecha) {
	// Expresión regular para el formato dd/mm/yyyy
	const regex = /^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/(19|20)\d{2}$/;

	// Comprobar si la fecha coincide con el patrón de la regex
	if (!fecha.match(regex)) {
		return false;
	}

	// Separar los componentes de la fecha
	const partes = fecha.split('/');
	const dia = parseInt(partes[0], 10);
	const mes = parseInt(partes[1], 10);
	const anio = parseInt(partes[2], 10);

	// Crear un objeto Date con los componentes de la fecha
	const fechaObj = new Date(anio, mes - 1, dia);

	// Comprobar si el objeto Date es válido y si los componentes coinciden
	return fechaObj.getFullYear() === anio && fechaObj.getMonth() === mes - 1 && fechaObj.getDate() === dia;
}

function desplegarCalendario() {
	const fechaInput = document.getElementById('txtfechanacimiento');
	fechaInput.focus();
	fechaInput.click();
}