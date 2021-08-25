<?php
// array de salida
// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";

// Verificamos las ariables post y que exista la variables acción.
if(isset($_POST) && !empty($_POST) && isset($_POST['accion']))
{
    // incluimos el archivo de las funciones y conexion a la basse de datos.
    include('mainfunctions.inc.php');
    if($errorDbConexion == false)
    {
        switch ($_POST['accion'])
        {    
            case 'login':
                $respuestaOK = userLogin($_POST,$dblink);
            case 'catalogo-usuario':
                //$appResponse['contenido'] = consultaUsers($dblink);
            break;
        
            default:
                // code
                $mensajeError = "Opción no disponible";
            break;
        }
        
    }else{
        $mensajeError = "Error al conectar con la Base de Datos.";
    }

}else{
    $mensajeError = "Variables no definidas";
}
// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);
		
// Retorno de JSON
//echo json_encode($appResponse);
echo json_encode($salidaJson);
?>