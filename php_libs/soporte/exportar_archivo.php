<?php
// ruta de los archivos con su carpeta
    $path_root=trim($_SERVER['DOCUMENT_ROOT']);
// cambiar a utf-8.
    header("Content-Type: text/html; charset=UTF-8");    
// variables y consulta a la tabla.
      //
  // Establecer formato para la fecha.
  // 
   date_default_timezone_set('America/El_Salvador');
   setlocale(LC_TIME, 'spanish');
// buscar la consulta y la ejecuta.
// call the autoload
    require $path_root."/registro_academico/vendor/autoload.php";
// load phpspreadsheet class using namespaces.
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
// call xlsx weriter class to make an xlsx file
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// Creamos un objeto Spreadsheet object
    $objPHPExcel = new Spreadsheet();
// Time zone.
    //echo date('H:i:s') . " Set Time Zone"."<br />";
    date_default_timezone_set('America/El_Salvador');
// set codings.
    $objPHPExcel->_defaultEncoding = 'ISO-8859-1';
// Set default font
    //echo date('H:i:s') . " Set default font"."<br />";
    $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
    $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);
// Creamos un archivo CVS
    //$objWriter = new Xlsx($objPHPExcel);
    //$objWriter->save($path_root."/registro_web/formatos_hoja_de_calculo/05featuredemo.xlsx");
    //$objWriter->save($path_root."/registro_web/formatos_hoja_de_calculo/Formato - Importar Notas SIGES.xlsx");
// Leemos un archivo Excel 2007
    $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
    $origen = $path_root."/registro_academico/formatos_hoja_de_calculo/";
    $objPHPExcel = $objReader->load($origen."Plantilla.xlsx");

// Leemos el archivo CVS
  /* $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
   $objPHPExcel = $objReader->load($path_root."/registro_web/formatos_hoja_de_calculo/05featuredemo.xlsx");*/
// Indicamos que se pare en la hoja uno del libro
    $objPHPExcel->setActiveSheetIndex(0);
    //Grabar el archivo en formato CVS    
    $objWriter = new CSV($objPHPExcel);
    $objWriter->setDelimiter(',');
    $objWriter->setEnclosure('');
    $objWriter->setLineEnding("\r\n");
    $objWriter->setSheetIndex(0);
                    $nombre_archivo = "Plantilla.csv";
    $objWriter->save($nombre_archivo);
?>