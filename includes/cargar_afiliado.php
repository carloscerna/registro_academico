<?php
// Set the Content-Type header to application/json
header('Content-Type: application/json');
// Get the root path
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
// Include the database connection and functions file
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
// Prepare the query
$query = "SELECT codigo, descripcion FROM catalogo_afp ORDER BY codigo";
try {
    // Execute the query
    $consulta = $dblink->query($query);
    // Initialize the array
    $datos = array();
    $fila_array = 0;
    // Fetch the data
    while ($listado = $consulta->fetch(PDO::FETCH_ASSOC)) {
        // Trim and sanitize the data
        $codigo = trim($listado['codigo']);
        $descripcion = trim($listado['descripcion']);
        // Add the data to the array
        $datos[$fila_array]["codigo"] = $codigo;
        $datos[$fila_array]["descripcion"] = $descripcion;

        $fila_array++;
    }
    // Encode the array to JSON and print it
    echo json_encode($datos);
} catch (PDOException $e) {
    // Handle any errors that occur during the query execution
    echo json_encode(array("error" => "Database error: " . $e->getMessage()));
}
// Close the database connection
$dblink = null;