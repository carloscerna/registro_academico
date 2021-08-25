<?php
header ('Content-type: text/html; charset=utf-8');
// Inicializando el array
$datos=array(); $fila_array = 0;
// variables. del post.
  $ruta = '../files/matricula/' . trim($_REQUEST["nombre_archivo_"]);
// Eliminar hoja de calculo seleccionada.
  unlink($ruta);
$datos[$fila_array]["registro"] = 'Si_registro';
// Enviando la matriz con Json.
echo json_encode($datos);
?>