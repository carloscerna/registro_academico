//funciones generales
function validarLetras(e)
	    { // 1
			tecla = (document.all) ? e.keyCode : e.which;
			if (tecla == 8)
				return true; // backspace
			if (tecla == 32)
				return true; // espacio
			if (e.ctrlKey && tecla == 86) {
				return true;
			} //Ctrl v
			if (e.ctrlKey && tecla == 67) {
				return true;
			} //Ctrl c
			if (e.ctrlKey && tecla == 88) {
				return true;
			} //Ctrl x
			if (tecla >= 96 && tecla <= 105) {
				return true;
			} //numpad
		
		// patron = /^([a-zA-Z]+\s)*[a-zA-ZñÑ]+$/; //patron NO ACEPTA ACENTOS, ÑÑ
			patron = /^[a-zA-ZáéíóúÁÉÍÓÚÑñ]+$/; // ACEPTE ACENTOS, ÑÑ Y A - Z.
	    
			te = String.fromCharCode(tecla);
				return patron.test(te); // prueba de patron
	    }

function NumCheck(e, field) {
  key = e.keyCode ? e.keyCode : e.which
  // backspace
  if (key == 8) return true
  // 0-9
  if (key > 47 && key < 58) {
    if (field.value == "") return true
    regexp = /.[0-9]{2}$/
    return !(regexp.test(field.value))
  }
  // .
  if (key == 46) {
    if (field.value == "") return false
    regexp = /^[0-9]+$/
    return regexp.test(field.value)
  }
  // other key
  return false
 
}