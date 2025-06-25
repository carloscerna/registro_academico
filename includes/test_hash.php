<?php
// test_hash.php

// La contraseña en texto plano que estás intentando usar
$password_ingresado = 'sebastian';

// El hash que obtuviste directamente de tu base de datos para "carlos cerna"
$hash_desde_db = '$2y$10$wwGxZGFKG93lCov.wJZMGOmb8tLBIx5yTIe1fHsrTVh.lmHNjpli2';

echo "Contraseña ingresada (longitud " . strlen($password_ingresado) . "): '" . $password_ingresado . "'<br>";
echo "Hash desde DB (longitud " . strlen($hash_desde_db) . "): '" . $hash_desde_db . "'<br>";

// Realizar la verificación
if (password_verify($password_ingresado, $hash_desde_db)) {
    echo "Resultado de password_verify: ¡VERIFICACIÓN EXITOSA!";
} else {
    echo "Resultado de password_verify: ¡VERIFICACIÓN FALLIDA! La contraseña es incorrecta o hay un problema con el hash.";
}
?>