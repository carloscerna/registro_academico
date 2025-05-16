<?php
header('Content-Type: application/json');
//
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ruta de los archivos con su carpeta
$path_root=trim($_SERVER['DOCUMENT_ROOT']);
// Incluimos el archivo de funciones y conexi�n a la base de datos
include($path_root."/registro_academico/includes/mainFunctions_conexion.php");
// Cargar el autoload de Composer para PhpSpreadsheet
require $path_root."/registro_academico/vendor/autoload.php";
//
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\PageMargins;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
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
            case 'procesar_excel':
                    try {
                        if (empty($_FILES['excelFile']['tmp_name'])) {
                            throw new Exception('No se subió ningún archivo Excel.');
                        }
                
                        $archivo = $_FILES['excelFile']['tmp_name'];
                        $ann = trim($_POST['codigo_ann_lectivo']) ?? null;
                        $bach = trim($_POST['codigo_bachillerato']) ?? null;
                        $codigo_grupo = $_POST['codigo_grupo'] ?? null;
                
                        if (!$ann || !$bach || !$codigo_grupo) {
                            throw new Exception('Datos insuficientes para procesar.');
                        }
                
                        [$grado, $seccion, $turno] = explode('|', $codigo_grupo);
                            $grado = trim($grado);
                            $seccion = trim($seccion);
                            $turno = trim($turno);
                        // Cargar el archivo Excel
                        $spreadsheet = IOFactory::load($archivo);
                        $sheet = $spreadsheet->getActiveSheet();
                       // $sheet = $spreadsheet->setActiveSheetIndex(0);
                
                        // Insertar dos columnas al principio (A y B)
                        $sheet->insertNewColumnBefore('A', 1);
                        // Insertar encabezados
                        $sheet->setCellValue('A1', 'Código NIE');
                        $sheet->setCellValue('B1', 'Nombre del Alumno');

                        // Aplicar estilo completo
                        $styleArray = [
                            'font' => [
                                'bold' => true,
                            ],
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => [
                                    'rgb' => 'D9D9D9',
                                ],
                            ],
                        ];
                        $sheet->getStyle('A1:B1')->applyFromArray($styleArray);
                        // Definir estilo de bordes finos
                        $styleArrayBorders = [
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'], // Color negro
                                ],
                            ],
                        ];

                        // Preparar la consulta
                        $codigo_all = $bach . $grado . $seccion . $turno . $ann;
                
                        $stmt = $pdo->prepare("
                            SELECT 
                                a.codigo_nie, 
                                btrim(a.apellido_paterno || ' ' || a.apellido_materno || ', ' || a.nombre_completo) AS nombre_alumno
                            FROM alumno a
                            INNER JOIN alumno_matricula am ON a.id_alumno = am.codigo_alumno 
                            WHERE am.retirado = 'f'
                              AND btrim(am.codigo_bach_o_ciclo || am.codigo_grado || am.codigo_seccion || am.codigo_turno || am.codigo_ann_lectivo) = :codigo_all
                            ORDER BY nombre_alumno
                        ");
                        $stmt->execute([':codigo_all' => $codigo_all]);
                        $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                        if (empty($alumnos)) {
                            throw new Exception('No se encontraron alumnos para este grupo.');
                        }
                
                        // Insertar los datos en la hoja de Excel (desde fila 2)
                        $fila = 2;
                        foreach ($alumnos as $alumno) {
                            $sheet->setCellValue("A{$fila}", $alumno['codigo_nie']);
                            $sheet->setCellValue("B{$fila}", $alumno['nombre_alumno']);
                            $fila++;
                        }
                            // Ajustar automáticamente el ancho de las columnas A y B
                            $sheet->getColumnDimension('A')->setAutoSize(true);
                            $sheet->getColumnDimension('B')->setAutoSize(true);
                            // 4. AHORA sacás la última fila
                            $ultimaFila = $sheet->getHighestRow(); // Esto ya te da el último número de fila usado
                            // Colores de fondo alternos (gris y blanco)
                            $colorFondo1 = 'D9D9D9'; // Gris claro
                            $colorFondo2 = 'FFFFFF'; // Blanco

                            // Recorremos las filas desde la 2 (ya que la 1 es el encabezado)
                            for ($i = 2; $i <= $ultimaFila; $i++) {
                                // Determinar color de fondo: si es impar, gris; si es par, blanco
                                $colorFondo = ($i % 2 == 0) ? $colorFondo2 : $colorFondo1;
                                
                                // Aplicar color de fondo a la fila completa (de A hasta B)
                                $sheet->getStyle("A{$i}:B{$i}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                                $sheet->getStyle("A{$i}:B{$i}")->getFill()->getStartColor()->setRGB($colorFondo);
                            }
                            switch ($grado) {
                                case $grado == '4P' || $grado == '5P' || $grado == '6P' || $grado == '01':
                                    // … despues de cargar $sheet y calcular $ultimaFila …
                                    
                                    // 1. Captura el objeto de validación de C2
                                    /** @var DataValidation $dv */
                                    /** @var DataValidation $dvOriginal */

                                    // 1. Calcula la última fila de datos basándote en la columna B
                                    $ultimaFila = $sheet->getHighestDataRow('B');
                                    
                                    // 2. Calcula la última columna con datos en la hoja
                                    $ultimaColumnaLetra = $sheet->getHighestDataColumn();              // ej. "AR"
                                    $ultimaColumnaIndex = Coordinate::columnIndexFromString($ultimaColumnaLetra); // ej. 44
                                    
                                    // 3. Genera el array de letras desde la C (índice 3) hasta esa última
                                    $columnas = [];
                                    for ($colIndex = 3; $colIndex <= $ultimaColumnaIndex; $colIndex++) {
                                        $columnas[] = Coordinate::stringFromColumnIndex($colIndex);
                                    }
                                    
                                    // 4. Recorre cada columna y clona la validación de la fila 2
                                    foreach ($columnas as $col) {
                                        /** @var DataValidation $dvOriginal */
                                        $dvOriginal = $sheet->getCell("{$col}2")->getDataValidation();
                                        if (!($dvOriginal instanceof DataValidation)) {
                                            continue; // si en esa columna no hay validación en la fila 2, saltamos
                                        }
                                    
                                        // 5. Aplica la validación a cada celda desde la fila 3 hasta $ultimaFila
                                        for ($fila = 3; $fila <= $ultimaFila; $fila++) {
                                            $newDv = clone $dvOriginal;
                                            $newDv->setSqref("{$col}{$fila}");
                                            $sheet->getCell("{$col}{$fila}")->setDataValidation($newDv);
                                        }
                                    }
                                        // RELLENAR CONTENIDO EN LA HOJA 2.
                                        // define aquí los dos colores que quieras alternar
                                        $coloresBloque = ['FFFFFF', 'D9E1F2']; // blanco / celeste claro
                                        // asumimos que $spreadsheet ya está cargado
                                        $sheet1 = $spreadsheet->getSheetByName('Hoja1');
                                        $sheet2 = $spreadsheet->getSheetByName('Hoja2');
                                        
                                        // 1) Calculamos la última columna con datos en Hoja1
                                        $ultimaColLetra = $sheet1->getHighestDataColumn();
                                        $ultimaColIndex = Coordinate::columnIndexFromString($ultimaColLetra);
                                        
                                        // 2) Preparamos la fila inicial en Hoja2 y el tamaño del bloque
                                        $fila2     = 2;    // empezamos en la fila 2
                                        $blockSize = 4;    // cada indicador ocupa 4 filas, pero escribe sólo en la 1ª
                                        $colorIndex = 0; // indice para alternar el color
                                        
                                        // 3) Recorremos todas las columnas desde C hasta la última
                                        for ($col = Coordinate::columnIndexFromString('C'); $col <= $ultimaColIndex; $col++) {
                                            $colLetra  = Coordinate::stringFromColumnIndex($col);
                                            $indicador = $sheet1->getCell($colLetra . '1')->getValue();
                                        
                                            // 3a) escribimos el indicador SOLO en la primera fila del bloque
                                            $sheet2->setCellValue("C{$fila2}", $indicador);
                                        // 3b) Aplicar el color de fondo a TODO el bloque de 4 filas, columnas A–C
                                            $start = $fila2;
                                            $end   = $fila2 + $blockSize - 1;
                                            $color = $coloresBloque[$colorIndex % count($coloresBloque)];
                                            $rango = "C{$start}:C{$end}";
                                            $sheet2->mergeCells($rango);
                                        
                                            // 3) centrar texto en el rango combinado
                                            $sheet2
                                                ->getStyle($rango)
                                                ->getAlignment()
                                                ->setVertical(Alignment::VERTICAL_CENTER)
                                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                                                
                                            $sheet2->getStyle("A{$start}:C{$end}")->applyFromArray([
                                                'fill' => [
                                                    'fillType'   => Fill::FILL_SOLID,
                                                    'startColor' => ['rgb' => $color],
                                                ],
                                            ]);

                                            // 3c) Prepara el índice para el siguiente bloque
                                            $colorIndex++;

                                            // 3d) Avanza el puntero de fila
                                            $fila2 += $blockSize;
                                        }
                                        
                                        // 4) Ajustamos anchos de columna en Hoja2
                                            // Ajustar anchos de columnas
                                            $sheet2->getColumnDimension('A')->setWidth(10);
                                            $sheet2->getColumnDimension('B')->setWidth(70);
                                            $sheet2->getColumnDimension('C')->setWidth(50);

                                            // Ajustar alto de fila por defecto
                                            $sheet2->getDefaultRowDimension()->setRowHeight(35);

                                            // Ajustar texto (wrap) en A, B y C
                                            $sheet2
                                                ->getStyle('A:C')
                                                ->getAlignment()
                                                ->setWrapText(true);
                                        // 1) Configurar orientación vertical (portrait)
                                        $sheet2->getPageSetup()
                                            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                                            ->setPaperSize(PageSetup::PAPERSIZE_LETTER);

                                        // 2) Márgenes estrechos (medidos en pulgadas)
                                        $margins = $sheet2->getPageMargins();
                                        $margins->setTop(0.25)    // margen superior
                                                ->setBottom(0.25) // margen inferior
                                                ->setLeft(0.25)   // margen izquierdo
                                                ->setRight(0.25)  // margen derecho
                                                ->setHeader(0.1)  // margen cabecera
                                                ->setFooter(0.1); // margen pie
                                        // Guardar archivo procesado
                                            // 7) Guardar Excel
                                            $ruta = 'temp_excel_' . '.xlsx';
                                            (new Xlsx($spreadsheet))->save($ruta);
                                            // 8) Leer "nómina" desde Hoja1 columna B (fila2..última)
                                            $ultFilaNom = $sheet1->getHighestDataRow('B');
                                            $dataNom = [];
                                            for ($r = 2; $r <= $ultFilaNom; $r++) {
                                                $nie  = $sheet1->getCell("A{$r}")->getValue();
                                                $name = $sheet1->getCell("B{$r}")->getValue(); // o la columna que sea
                                                $dataNom[] = ['nie'=>$nie,'nombre'=>$name];
                                            }   
                                    break;
                                    default:
                                    $sheet1 = $spreadsheet->getSheetByName('Hoja1'); 
                                     // 8) Leer "nómina" desde Hoja1 columna B (fila2..última)
                                     $ultFilaNom = $sheet1->getHighestDataRow('B');
                                     $dataNom = [];
                                     for ($r = 2; $r <= $ultFilaNom; $r++) {
                                         $nie  = $sheet1->getCell("A{$r}")->getValue();
                                         $name = $sheet1->getCell("B{$r}")->getValue(); // o la columna que sea
                                         $dataNom[] = ['nie'=>$nie,'nombre'=>$name];
                                     }   
                                        // 7) Guardar Excel
                                        $ruta = 'temp_excel_' . '.xlsx';
                                        (new Xlsx($spreadsheet))->save($ruta);
                                        $dataNom[] = ['nie'=>$nie,'nombre'=>$name];
                                        break;
                            }
                            
                         
                        // DESPUES DE GUARDAR EL ARCHIVO.
                        
                            // 1. Iniciar sesión y obtener código de institución
                            if (session_status() !== PHP_SESSION_ACTIVE) {
                                session_start();
                            }
                            $CodigoInstitucion = $_SESSION['codigo_institucion'] ?? 'default';

                            // 2. Carpeta destino absoluta
                            $targetDirectory = "C:/TempSistemaRegistro/Carpetas/{$CodigoInstitucion}";
                            if (!is_dir($targetDirectory)) {
                                mkdir($targetDirectory, 0777, true);
                            }

                            // 3. Obtener grado, sección y turno desde el select
                           /* $grupo = $_POST['codigo_grupo'];           // viene como e.g. "010203"
                            $grado   = substr($grupo, 0, 2);           // "01"
                            $seccion = substr($grupo, 2, 2);           // "02"
                            $turno   = substr($grupo, 4, 2);           // "03"
                            */

                            // 4. Construir nombre de salida
                            $outputFileName = "Nomina_{$grado}_{$seccion}_{$turno}.xlsx";
                            $outputFilePath = "{$targetDirectory}/{$outputFileName}";

                            // 5. Copiar (o renombrar) el archivo
                            if (!copy($ruta, $outputFilePath)) {
                                // Si falla la copia, puedes intentar con rename()
                                 rename($ruta, $outputFilePath);
                                throw new Exception("No se pudo copiar el archivo a la ruta destino.");
                            }
       
                        $response = [
                            'success' => true,
                            'message' => 'Procesado correctamente',
                            'archivo' => $outputFileName,
                            'data'    => $dataNom
                        ];
                    } catch (Exception $e) {
                        echo json_encode([
                            'success' => false,
                            'message' => $e->getMessage(),
                            'consulta' => isset($codigo_all) ? "Código combinado usado: {$codigo_all}" : null
                        ]);
                    }
                    break;                
                
        }
    } else {
        $response['message'] = 'No se recibió acción';
    }
} catch (PDOException $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
