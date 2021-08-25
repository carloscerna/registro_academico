<?php
// fecha a�o/mes/dia.
// Inciar variable global datos y mensajes de error.
    date_default_timezone_set('America/El_Salvador');
    $day=date("d");
    $month=date("m");
    $year=date("Y");
    $date=$day."/".$month."/".$year;
    $fecha=$year."-".$month."-".$day;

// Inicializando el array
$datos=array();
$datos[$fecha];
      //  }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>