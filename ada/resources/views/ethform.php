<?php
date_default_timezone_set('Asia/Shanghai');

function responseJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    die;
}

function saveData($dataArr) {
    $r = null;
    $saveFile = './ethdata.csv';
    $fh = @fopen($saveFile, 'a');
    if($fh) {
        $r = fputcsv($fh, $dataArr);
    }
    return $r;
}

$ethAddress = filter_input(INPUT_POST, 'eth_address');
$appid = filter_input(INPUT_POST, 'app_id');
$appType = filter_input(INPUT_POST, 'app_type');

//输入数据空
if(strlen($ethAddress) === 0 || strlen($ethAddress) === 0 || strlen($appType) === 0) {
    responseJson([
        'status' => 401,
        'message' => 'input data is empty'
    ]);
}

$ethDataArr = [$ethAddress, $appid, $appType, date('Y-m-d H:i:s')];

$saveResult = saveData($ethDataArr);

//保存失败
if(!$saveResult) {
    responseJson([
        'status' => 402,
        'message' => 'input data save error'
    ]);
}

//成功
responseJson([
        'status' => 200,
        'message' => 'success'
    ]);