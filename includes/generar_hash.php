<?php
// generar_hash.php
$password_plano = 'sebastian'; // CAMBIA ESTO por una contraseña temporal y segura
$hash_generado = password_hash($password_plano, PASSWORD_DEFAULT);
echo "Hash para '{$password_plano}': " . $hash_generado;
?>