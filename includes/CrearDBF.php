<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// archivos que se incluyen.
    include($path_root."/registro_web/includes/so_version.php");
// Inicializamos variables de mensajes y JSON
$respuestaOK = false;
$mensajeError = "No se puede ejecutar la aplicación";
$contenidoOK = "";
// POST O GET  O REQUEST.

// Extrar la información del sistema operativo.
    //$info["os"];
// Ejecutar. Depende de la condición.
    if($info["os"] === "LINUX"){
        exec("sudo sh CrearDBF.sh");    
    }
    if($info["os"] === "WIN"){
        $hola = array();
        $hola2 = array();
        exec("CrearDBF.bat");    
    }
	// Validamos qe existan las variables post
	if(isset($_POST) && !empty($_POST)){
		if(!empty($_POST['accion'])){
			$_POST['accion'] = $_POST['accion'];
		}
		// Verificamos las variables de acción
		switch ($_POST['accion']) {
			case 'VerificarDBF':
            $nombreDBF = trim($_REQUEST["nombreDBF"]);

            //// COMPROBAR SI EXISTE LA BASE DE DATOS.
                     // variables para la conexion.
                   $host = 'localhost';
                   $port = 5432;
                   $database = $nombreDBF;
                   $username = 'postgres';
                   $password = 'Orellana';
               //Construimos el DSN//
               try{
                   $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                   // Creamos el objeto
                   $dblink = new PDO($dsn, $username, $password);
               
                   // Validar la conexión.
                   if($dblink){
                    // Variable que indica el status de la conexión a la base de datos
                       $respuestaOK = true;
                       $mensajeError = "Si Existe";
                   }
                  }catch(PDOException $e) {
                        //echo  $e->getMessage();
                           $respuestaOK = false;
                           $mensajeError = "No Existe";
                           $contenidoOK= $hola;
                           
                            // variables para la conexion.
                                $host = 'localhost';
                                $port = 5432;
                                $database = 'postgres';
                                $username = 'postgres';
                                $password = 'Orellana';
                            //Construimos el DSN//
                                $dsn = "pgsql:host=$host;port=$port;dbname=$database";
                                // Creamos el objeto
                                $dblink = new PDO($dsn, $username, $password);
                   
                           /*
                            Una vez dentro, hay que desconectar a todos los usuarios conectados a la base de datos. Para eso, ejecutamos el siguiente comando
                            */
                            $query = "SELECT pg_terminate_backend( pid )
                            FROM pg_stat_activity
                            WHERE pid <> pg_backend_pid( )
                                AND datname = 'demo'";
                                $result = $dblink -> query($query);
                            
                            //Luego podremos renombrar la base de datos con el siguiente comando
                            
                            $query = "ALTER DATABASE 'demo' RENAME TO '$nombreDBF'";
                            $consulta = $dblink -> query($query);
                    }
            
			break;

			default:
				$mensajeError = 'Esta acción no se encuentra disponible';
			break;
		}
	}
	else{
		$mensajeError = 'No se puede ejecutar la aplicación';}

// Armamos array para convertir a JSON
$salidaJson = array("respuesta" => $respuestaOK,
		"mensaje" => $mensajeError,
		"contenido" => $contenidoOK);

echo json_encode($salidaJson);
?>

