<?php
session_name('demoUI');
session_start();

// Directorio Raíz de la app

// Es utilizando en templateEngine.inc.php
            
$root = '';

    include('includes/templateEngine.inc.php');

    $twig->display('layout_login.html');    
?>