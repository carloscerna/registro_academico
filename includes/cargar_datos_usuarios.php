<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// variables que traer el ID DEL ALUMNO.

// armando el Query. PARA LA TABLA ALUMNO.
			$query = "SELECT u.id_usuario, u.nombre, u.password, u.codigo_perfil, u.base_de_datos, u.codigo_escuela, u.codigo_personal,
					cat_perfil.codigo as codigo_perfil, cat_perfil.descripcion as descripcion_perfil
					FROM usuarios u
					INNER JOIN catalogo_perfil cat_perfil ON cat_perfil.codigo = u.codigo_perfil
					WHERE u.id_usuario = '$_POST[id_usuario]'";

// Ejecutamos el Query. PARA LA TABLA HISTORIAL ALUMNOS.
   $consulta_usuario = $dblink -> query($query);

// Inicializando el array
$datos=array(); $fila_array = 0;
// Recorriendo la Tabla con PDO::        
         // Rellenando la array. cuarto tabs-4. Padre/Madre/Encargado.
	 // Debera crerase en las tablas correspondientes los campos para poder rellenar dicha informaci�n.
            if($consulta_usuario -> rowCount() != 0){		
                while($listadoUsuarios = $consulta_usuario -> fetch(PDO::FETCH_BOTH))
                  {
                    $codigo_usuario = trim($listadoUsuarios['id_usuario']);
					$nombre = trim($listadoUsuarios['nombre']);
					$password = trim($listadoUsuarios['password']);
					$codigo_perfil = trim($listadoUsuarios['codigo_perfil']);
					$dbname = trim($listadoUsuarios['base_de_datos']);
					$codigo_escuela = trim($listadoUsuarios['codigo_escuela']);
					$codigo_personal = trim($listadoUsuarios['codigo_personal']);
					
                    // pasar a la matriz.
                        $datos[$fila_array]["codigo_usuario"] = $codigo_usuario;
						$datos[$fila_array]["nombre"] = $nombre;
						$datos[$fila_array]["password"] = $password;
						$datos[$fila_array]["codigo_perfil"] = $codigo_perfil;
						$datos[$fila_array]["dbname"] = $dbname;
						$datos[$fila_array]["codigo_escuela"] = $codigo_escuela;
						$datos[$fila_array]["codigo_personal"] = $codigo_personal;

                    // Incrementar el valor del array.
                    $fila_array++;
                  }
            }
// Enviando la matriz con Json.
echo json_encode($datos);	
?>