<?php
$directorio = opendir('../files/matricula/');
// Inicializando el array
$datos=array(); $fila_array = 0;
// Iniciar Bucle que recorre el directorio
    while ($elemento = utf8_decode(readdir($directorio)))
    {
        if($elemento !='.' &&  $elemento != '..'){
            if(is_dir('../files/matricula/'.$elemento))
            {
                $datos[$fila_array]["archivo"] = '<tr><td>'.$elemento.'</td></tr>';
            }else{
                $datos[$fila_array]["archivo"] = '<tr><td>'.$elemento.'</td><td><a data-accion=goActualizarOk class="btn btn-sm btn-success" href='."$elemento".'><span class="glyphicon glyphicon-import"></span> Actualizar</a></td>'
                                                    .'</td><td><a data-accion=goEliminarOk class="btn btn-sm btn-warning" href='."$elemento".'><span class="glyphicon glyphicon-trash"></span> Eliminar</a></td></tr>';
                $fila_array++;
            }
        }
    }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>