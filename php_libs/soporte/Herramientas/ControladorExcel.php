<?php
header('Content-Type: application/json');
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Cargar el autoload de Composer para PhpSpreadsheet
require $path_root."/registro_academico/vendor/autoload.php";
//
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
// conexión.
$pdo = $dblink;

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'ann_lectivo':
                $stmt = $pdo->query("SELECT codigo, descripcion FROM ann_lectivo WHERE codigo_estatus = '01'");
                $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                break;

            case 'bachillerato':
                $codigo = $_POST['codigo_ann_lectivo'];
                $stmt = $pdo->prepare("SELECT o.codigo_bachillerato, b.nombre 
                    FROM organizar_ann_lectivo_ciclos o
                    INNER JOIN bachillerato_ciclo b ON b.codigo = o.codigo_bachillerato
                    WHERE o.codigo_ann_lectivo = :codigo ORDER BY o.ordenar");
                $stmt->execute([':codigo' => $codigo]);
                $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                break;

            case 'grupo':
                $stmt = $pdo->prepare("SELECT orgs.codigo_grado, orgs.codigo_seccion, orgs.codigo_turno,
                        grd.nombre AS grado, sec.nombre AS seccion, tur.nombre AS turno
                        FROM organizacion_grados_secciones orgs
                        INNER JOIN grado_ano grd ON grd.codigo = orgs.codigo_grado
                        INNER JOIN seccion sec ON sec.codigo = orgs.codigo_seccion
                        INNER JOIN turno tur ON tur.codigo = orgs.codigo_turno
                        WHERE orgs.codigo_ann_lectivo = :ann AND orgs.codigo_bachillerato = :bach
                        ORDER BY orgs.codigo_grado, orgs.codigo_seccion");
                $stmt->execute([
                    ':ann' => $_POST['codigo_ann_lectivo'],
                    ':bach' => $_POST['codigo_bachillerato']
                ]);
                $response['data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response['success'] = true;
                break;
        }
    } else {
        $response['message'] = 'No se recibió acción';
    }
} catch (PDOException $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
