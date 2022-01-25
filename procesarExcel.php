<?php
include_once(dirname(__FILE__).'/config/config.php');
include_once(LIBRARY_PATH.'PhpSpreadsheet/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

function getInputFile()
{
    return $file;
}

function getInfoRenaper($dataRequest)
{
    $url = 'https://servicios.gcba.gob.ar/renaper/api/personas/'.$dataRequest['A'].'/'.$dataRequest['B'].'/ejemplares/ultimo';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $headers = [
        'client_id: 1b0982a1b48e4b918767d74fc1f10c31',
        'client_secret: 21a93559d985440A859112be44d79fb8'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    echo "ejecutando request: ".$url.PHP_EOL;
    $server_output = curl_exec ($ch);

    curl_close ($ch);

    echo 'Información obtenida: '.PHP_EOL. $server_output .PHP_EOL;

    $renaper = json_decode($server_output, true);

    $result = array(0=>array(), 1=>$url);

    if($renaper['nroError'] == 0) $result[0] = $renaper;

    return $result;
}

function getData($fileName)
{
    $spreadsheet = IOFactory::load($fileName);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
    return $sheetData;
}

function procesarData($data) {
    $result = array();
    foreach ($data as $k=>$fila) {
        echo 'procesando la fila del excel:'.$k.' con la data: col A: '.$fila['A']. ' col B: '.$fila['B'].' col C: '.$fila['C'].PHP_EOL;
        $infoRenaper = getInfoRenaper($fila);
        if(count($infoRenaper[0])==0) {
            $filaResult = array('A'=>$fila['A'], 'B'=>$fila['B'], 'C'=>$fila['C']
                                , 'D'=>'no'
                                , 'E'=>'la persona no se pudo encontrar en el servicio de renaper con esta llamada '. $infoRenaper[1]);
            array_push($result, $filaResult);
            continue;
        }
        else {
            if($fila['C'] == $infoRenaper[0]['fechaNacimiento']) {
                $filaResult = array('A' => $fila['A'], 'B' => $fila['B'], 'C' => $fila['C']
                            , 'D' => 'si'
                            , 'E' => '');
                array_push($result, $filaResult);
            }
            else{
                $filaResult = array('A' => $fila['A'], 'B' => $fila['B'], 'C' => $fila['C']
                                , 'D' => 'no'
                                , 'E' => 'la fecha de nacimiento informada por renaper '.$infoRenaper[0]['fechaNacimiento'].', y la del excel informada es '.$fila['C'].', no coinciden');
                array_push($result, $filaResult);
            }

        }
    }
    return $result;
}

function escribirArchivoExcelOutput($dataProcesada)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    //data
    $columnIndex = 1;
    $rowIndex = 1;

    foreach($dataProcesada as $j=>$dataset)
    {
        foreach ($dataset as $k=>$data)
            if(!is_integer($k))
            {
                @$sheet->setCellValueByColumnAndRow($columnIndex, $rowIndex, $dataset[$k]);
                $columnIndex++;
            }
        $columnIndex = 1;
        $rowIndex++;
    }

    $path = OUTPUT_PATH.'out_'.date('Y-m-d H:i:s').'_'.rand().'.xlsx';
    $writer = new Xlsx($spreadsheet);
    try {
        $result = array('exito'=>true, 'output'=>$path);
        $writer->save($path);
    }
    catch (WriterException $e)
    {
        $result = array('exito'=>false, 'output'=>$path, 'msg'=>$e->getMessage());
    }

    return $result;
}

function procesarExcel()
{
    global $argc, $argv;

    if (count($argv) <= 1) die("Se requiere un archivo para procesar");
    echo "Se ha detectado el nombre de archivo: ". $argv[1].'...'. PHP_EOL;;
    echo "intentando acceder al archivo ".$argv[1].'...'.PHP_EOL;
    if(!file_exists($argv[1])) die('El archivo '.$argv[1]. ' no se pudo acceder.');

    echo "Comenzando el procesamiento de archivo excel...". PHP_EOL;
    $data = getData($argv[1]);
    $dataProcesada = procesarData($data);
    echo "resultados: ".PHP_EOL;
    var_dump($dataProcesada);
    $status = escribirArchivoExcelOutput($dataProcesada);
    if($status['exito']) echo "el archivo ".$argv[1]. " se proceso con éxito !... se genero el archivo ". $status['output'];
    else echo "el archivo ".$argv[1]. " se pudo procesar para no se pudo generar el archivo de salida debido a : ". $status['msg'];
}


procesarExcel();

