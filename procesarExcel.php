<?php
include_once(dirname(__FILE__).'/config/config.php');
include_once(LIBRARY_PATH.'PhpSpreadsheet/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function getInputFile()
{
    return $file;
}

function getData($fileName)
{
    $spreadsheet = IOFactory::load($fileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    var_dump($sheetData);
}

function procesarExcel()
{
    global $argc, $argv;

    if (count($argv) <= 1) die("Se requiere un archivo para procesar");
    echo "Se ha detectado el nombre de archivo: ". $argv[1].'...'. PHP_EOL;;
    echo "intentando acceder al archivo ".pathinfo($argv[1], PATHINFO_BASENAME).'...'.PHP_EOL;
    if(!file_exists($argv[1])) die('El archivo '.$argv[1]. ' no se pudo acceder.');

    echo "Comenzando el procesamiento de archivo excel...";
    $data = getData($argv[1]);
    $dataProcesada = procesarData($data);
    $status = escribirArchivoExcelOutput($dataProcesada);
    if($status['exito']) echo "el archivo ".$argv[1]. "se proceso con éxito !... se genero el archivo ". $status['output'];
}


procesarExcel();

