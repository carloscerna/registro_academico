<?php
// Rutas y conexión
$path_root = trim($_SERVER['DOCUMENT_ROOT']);
require_once $path_root . "/registro_academico/php_libs/fpdf/fpdf.php";
include($path_root . "/registro_academico/includes/funciones.php");
include($path_root . "/registro_academico/includes/mainFunctions_conexion.php");
header("Content-Type: text/html; charset=UTF-8");

$pdo = $dblink;

// Variables desde el formulario
$modalidad    = $_GET['modalidad'];
$gradoseccion = $_GET['gradoseccion'];
$annlectivo   = $_GET['annlectivo'];
$asignatura   = $_GET['asignatura'] ?? null; // opcional
$periodo      = $_GET['periodo']    ?? null; // opcional

// Obtener cantidad de períodos desde catalogo_periodos
$sqlPeriodo = "SELECT cantidad_periodos FROM catalogo_periodos WHERE codigo_modalidad = ? LIMIT 1";
$cant_periodos = 3;
if ($stmt = $pdo->prepare($sqlPeriodo)) {
    $stmt->execute([$modalidad]);
    $cant_periodos = (int) $stmt->fetchColumn() ?: 3;
}
$calif_minima = 6;



// Clase PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',12);
        // Header del PDF
        $img = $_SERVER['DOCUMENT_ROOT'] . '/registro_academico/img/' . $_SESSION['logo_uno']; //Logo
        $nombre_institucion = convertirtexto($_SESSION['institucion']);
        $this->  Image($img, 10, 10, 20); // Logo
        $this->SetXY(30, 10);
        $this->Cell(0, 6, convertirtexto($nombre_institucion), 0, 1, 'L');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,convertirtexto('Informe Académico - por Estudiante'),0,1,'C');
        $this->Ln(4);
    }
    function Footer() {
        $this->SetY(-25);
        $this->SetFont('Arial','I',9);
        $this->Cell(0,6,convertirtexto("Encargado del Grado ____________________"),0,1,'L');
        $this->Cell(0,6,convertirtexto("Director de la Institución ____________________"),0,1,'L');
        $this->SetY(-10);
        $this->Cell(0,6,convertirtexto('Página ').$this->PageNo(),0,0,'C');
    }
    function addEstudiante($datos) {
        $this->SetFont('Arial','',10);
        foreach ($datos as $k => $v) {
            $this->Cell(35,6,convertirtexto("$k:"),0,0);
            $this->Cell(60,6,convertirtexto($v),0,1);
        }
        $this->Ln(2);
    }
    function addTabla($asignaturas, $cant_periodos, $minima = 6) {
        $this->SetFont('Arial','B',8);
        $col = 10;
        $this->Cell(40,10,'Asignatura',1,0,'C');

        for ($p = 1; $p <= $cant_periodos; $p++) {
            $this->Cell($col*5,5,"PERIODO $p",1,0,'C');
        }
        $this->Cell($col*4,10,'Final',1,0,'C');
        $this->Ln();

        $this->Cell(40,5,'',0,0);
        for ($i = 0; $i < $cant_periodos; $i++) {
            foreach (['A1','A2','A3','R','PP'] as $et) {
                $this->Cell($col,5,$et,1,0,'C');
            }
        }
        foreach (['R1','R2','NF','Res.'] as $et) {
            $this->Cell($col,5,$et,1,0,'C');
        }
        $this->Ln();

        $this->SetFont('Arial','',8);
        foreach ($asignaturas as $nombre => $periodos) {
            $this->Cell(40,6,convertirtexto($nombre),1,0);
            for ($p = 0; $p < $cant_periodos; $p++) {
                $data = $periodos[$p] ?? [];
                foreach (['a1','a2','a3','r','pp'] as $k) {
                    $v = isset($data[$k]) ? number_format($data[$k],1) : '';
                    $this->Cell($col,6,$v,1,0,'C');
                }
            }
            $r1 = $periodos[0]['r1'] ?? 0;
            $r2 = $periodos[0]['r2'] ?? 0;
            $nf = $periodos[0]['nota_final'] ?? 0;
            $res = ($nf >= $minima) ? 'Aprobado' : 'Reprobado';

            foreach ([$r1,$r2,$nf] as $v) {
                $this->Cell($col,6,($v ? number_format($v,1) : ''),1,0,'C');
            }
            $this->Cell($col,6,$res,1,0,'C');
            $this->Ln();
        }
    }
}

// Obtener estudiantes
$sqlEst = "SELECT a.id_alumno, a.codigo_nie,
        TRIM(CONCAT_WS(' ', a.apellido_paterno, a.apellido_materno, ', ', a.nombre_completo)) AS nombre_completo
        FROM alumno a
        INNER JOIN alumno_matricula am ON am.codigo_alumno = a.id_alumno
        WHERE am.codigo_bach_o_ciclo = ?
        AND CONCAT(am.codigo_grado, am.codigo_seccion, am.codigo_turno) = ?
        AND am.codigo_ann_lectivo = ?
        AND am.retirado = false
        ORDER BY nombre_completo";
$st = $pdo->prepare($sqlEst);
$st->execute([$modalidad, $gradoseccion, $annlectivo]);
$estudiantes = $st->fetchAll(PDO::FETCH_ASSOC);

// PDF
$pdf = new PDF("L", "mm", "A4");

foreach ($estudiantes as $est) {
    $pdf->AddPage();
    $pdf->addEstudiante([
        "Nombre" => $est['nombre_completo'],
        "NIE" => $est['codigo_nie'],
        "Grado/Sección" => $gradoseccion,
        "Modalidad" => $modalidad,
        "Año Lectivo" => $annlectivo
    ]);

$sqlNotas = "
    SELECT 
        asig.nombre AS asignatura,
        n.*
    FROM alumno a
    INNER JOIN alumno_matricula am 
        ON a.id_alumno = am.codigo_alumno 
        AND am.retirado = 'f'
        AND am.codigo_ann_lectivo = :annlectivo
    INNER JOIN nota n 
        ON n.codigo_alumno = a.id_alumno 
        AND n.codigo_matricula = am.id_alumno_matricula
    INNER JOIN asignatura asig 
        ON asig.codigo = n.codigo_asignatura
    WHERE a.id_alumno = :id_alumno
";
$rows = $pdo->prepare($sqlNotas);
$rows->execute([
    ':id_alumno' => $est['id_alumno'],
    ':annlectivo' => $annlectivo
]);

    // Preparar estructura por asignatura
    $asignaturas = [];
    foreach ($rows as $r) {
        $asignaturas[$r['asignatura']] = [
            [ 'a1' => $r['nota_a1_1'], 'a2' => $r['nota_a2_1'], 'a3' => $r['nota_a3_1'], 'r' => $r['nota_r_1'], 'pp' => $r['nota_p_p_1'], 'r1' => $r['recuperacion'], 'r2' => $r['nota_recuperacion_2'], 'nota_final' => $r['nota_final'] ],
            [ 'a1' => $r['nota_a1_2'], 'a2' => $r['nota_a2_2'], 'a3' => $r['nota_a3_2'], 'r' => $r['nota_r_2'], 'pp' => $r['nota_p_p_2'] ],
            [ 'a1' => $r['nota_a1_3'], 'a2' => $r['nota_a2_3'], 'a3' => $r['nota_a3_3'], 'r' => $r['nota_r_3'], 'pp' => $r['nota_p_p_3'] ]
        ];
    }

    $pdf->addTabla($asignaturas, $cant_periodos, $calif_minima);
}

$pdf->Output("I", "Informe_Seccion_Horizontal.pdf");
