<?php
use Yaf\Application;
use Yaf\Dispatcher;
class BaseController extends Yaf\Controller_Abstract  {
    public $db;
    public $request; //wap接收数据
    public $data; //sdk接收数据
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    public function init(){
        $this->db = new dbModel();
        $this->data = file_get_contents("php://input");
        if(empty($this->data)){
            echo json_encode(['code'=>104,'message'=>"请求方式错误"]);
        }
    }
    public function jsonmessage($code,$message,$reqdata){
        $resdata = ["code"=>$code,"message"=>$message,"data"=>$reqdata];
        return json_encode($resdata);
    }
    public function issign($lockdata,$sdkdata){
        $newdata = $this->sign($lockdata);
        if($sdkdata != $newdata){
            echo json_encode(['code'=>100,'message'=>"签名错误"]);exit;
        }else{
            return "ok";
        }
    }
    public function sign($data){
        $arrData = $this->argSort($data);
        $signstr = $this->strlink($arrData);
        return hash_hmac("sha256",$signstr,self::APP_KEY);
    }
    public function strlink($para) {
        $arg  = "";
        while (list ($key, $val) = each ($para)) {
            $arg.=$key."=".$val."&";
        }
        //去掉最后一个&字符
        $arg = substr($arg,0,count($arg)-2);
        //如果存在转义字符，那么去掉转义
        if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
        return $arg;
    }
    public function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    public function buildorderno(){
        return "pet".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."tap";
    }
    public function get7day($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }


}