<?php
session_name('demoUI');
session_start();

// Comprobar si existen las variables de SESSION.
if(empty($_SESSION['userNombre']))
{
    header('Location: /registro_academico');
}else{
// Es utilizando en templateEngine.inc.php
$root = '';
$Id = $_REQUEST['id'];
$accion = $_REQUEST['accion'];
$_SESSION['Id_A'] = $_REQUEST['id'];
    include('includes/templateEngine.inc.php');

    $twig->display('layout-nuevo-editar-estudiante.html',array(
        "userName" => $_SESSION['userNombre'],
        "userID" => $_SESSION['userID'],
        "codigo_perfil" => $_SESSION['codigo_perfil'],
        "codigo_personal" => $_SESSION['codigo_personal'],
        "logo_uno" => $_SESSION['logo_uno'],
        "nombre_institucion" => $_SESSION['institucion'],
        "nombre_personal" => $_SESSION['nombre_personal'],
        "nombre_perfil" => $_SESSION['nombre_perfil'],
        "codigo_institucion" => $_SESSION['codigo_institucion'],
        "id" => $Id,
        "accion" => $accion,
        "valor_c_ann_lectivo" => $_SESSION['s_codigo_ann_lectivo'],
        "valor_c_modalidad" => $_SESSION['s_codigo_modalidad'],
        "valor_c_grado_seccion_turno" => $_SESSION['s_codigo_grado_seccion_turno']
    ));
}
?>