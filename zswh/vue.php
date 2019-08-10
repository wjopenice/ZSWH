<?php
header('Access-Control-Allow-Origin:*');//允许所有来源访问
header('Access-Control-Allow-Headers:content-type');
header('Access-Control-Allow-Method:POST');//允许访问的方式
if(strtoupper($_SERVER['REQUEST_METHOD'])== 'OPTIONS'){
    exit;
}else{
    $data = $_POST['id'];
    $arr = [];
    switch ($data){
        case 1: $arr = ['msg'=>"SELECT","data"=>"1"];break;
        case 2: $arr = ['msg'=>"INSERT","data"=>"2"];break;
        case 3: $arr = ['msg'=>"UPDATE","data"=>"3"];break;
        default : $arr = ['msg'=>"DELETE","data"=>"4"];break;
    }
    echo json_encode($arr);
}
