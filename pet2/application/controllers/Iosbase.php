<?php
use Yaf\Application;
use Yaf\Dispatcher;
use Error\CodeConfigModel;
use \JPush\PushPayload;
use \JPush\Client;
use \JPush\Exceptions\JPushException;
class IosbaseController extends Yaf\Controller_Abstract{
    public $db;
    public $error;
    public $request;
    public $response;
    public $cookies;
    public $files;
    public $input;
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";

    const push_KEY = "9d379faa31de116fe128f1d7";
    const SECRET = "6e597b9d95ce3d6c52c5daca";
    public $client;
    //平台
    public $platform = [1=>"all",2=>['android','ios'],3=>'android',4=>'ios'];
    //推送类型(1所有，2别名，3标签)
    public $send_type = [1=>'all',2=>'alias',3=>'tag'];
    public function init(){
        Dispatcher::getInstance()->autoRender(FALSE);
        $this->db = new dbModel();
        $this->error = (new CodeConfigModel())->getCodeConfig();
        $this->request = $this->getRequest();
        $this->response = $this->getResponse();
        $this->cookies = $this->getRequest()->getCookie();
        $this->files = $this->getRequest()->getFiles();
        $this->input = json_decode(file_get_contents("php://input"),true);
        try{
            $this->client = new Client(self::push_KEY, self::SECRET);
        }catch (\Exception $e){
            echo "The exception code is: " . $e->getCode();
        }
    }

    /**
     * $type int 推送类型 （0点赞 1评论 2回复）
     * $uid string 接收别名（接收用户ID）
     * $pid string 发送别名（发送用户ID）
     * $pnickname string 发送用户昵称
     * $pavatar string 发送用户头像 （http://www.zhishengwh.com/xxx.png）
     * $data array 推送数据
     *
     * $platform_id int 请参考 $platform
     * $send_type_id int 请参考 $send_type
     *
     * return "success"|"error"
     */
    public function jpapi($type,$uid,$pid,$pnickname,$pavatar,$data,$platform_id = 4,$send_type_id = 2,$tag=""){
        $str = "";
        $messagestr = "";
        $title = "";
        $pusher = $this->client->push();
        //设置发送平台
        $pusher = $pusher->setPlatform($this->platform[$platform_id]);
        //选择发送类型
        $send_id = $this->send_type[$send_type_id];
        switch ($send_id){
            case 'all':$pusher = $pusher->addAllAudience();break;
            case 'alias' :$pusher = $pusher->addAlias($uid);break;
            case 'tag' :$pusher = $pusher->addTag($tag);break;
        }
        switch ($type){
            case 0:$str = "赞了你的帖子：";$url=1;break;
            case 1:$str = "评论了你";$url=2;break;
            case 2:$str = "回复了你";$url=2;break;
            case 4:$str = "在您附近发布了";$url=4;break;
        }
        if($pnickname == ""){
            $messagestr = $pid.$str;
            $title = $pid;
        }else{
            $messagestr = $pnickname.$str;
            $title = $pnickname;
        }
        if($pavatar == ""){
            $avatar = "http://www.pettap.cn/dx.png";
        }else{
            $avatar = $pavatar;
        }
        //$alert = $messagestr.json_encode($data);
        $alert=$messagestr.$data;
        $datamessage = [
            "title"=>$title,
            "large_icon"=>$avatar,
            "intent"=>[
                "url"=>$url,
            ],
            "extras"=>[
                "sendtime"=>time(),
                "url"=>$url,
                "large_icon"=>$avatar,
                "msg_content"=>$str,
                "title"=>$title
            ]
        ];

        $iosmessage = [
            "alert"=>$title,
            "sound"=>"default.caf",
            "badge"=>1,
            "extras"=>[
                "title"=>$title,
                "url"=>$url,
                "large_icon"=>$pavatar,
                "msg_content"=>$data
            ]
        ];

        switch ($platform_id){
            case 1:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 2:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 3:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 4:
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$iosmessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
        }
        try {
            $pusher->send();
            echo json_encode(['code'=>0,'message'=>"success"]);exit;
        } catch (\JPush\Exceptions\JPushException $e) {
            echo json_encode(['code'=>0,'message'=>"success"]);exit;
        }
    }

    public function send($arr=array()){
        $this->jpapi($arr['type'],$arr['uid'],$arr['pid'],$arr['pnickname'],$arr['pavatar'],$arr['data']);
    }
    public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]]);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data]);exit;
        }
    }
    //单文件上传
    public function uploadone($file,$path){
        $time = time();
        $dir = APP_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $pathicon = $dir."/".$file['name'];
       move_uploaded_file( $file['tmp_name'],$pathicon);

        $fileArr = "/public/".$path."/".$time."/".$file['name'];
        return $fileArr;
    }
    //多文件上传
    public function uploadss($file,$path){
        $time = time();
        $dir = APP_PATH."/public/".$path."/".$time;
        if(!file_exists($dir)){
            mkdir($dir,0777,true);
        }
        $fileArr = [];
        for($i=0;$i<count($file['name']);$i++){
            $pathicon = $dir."/".$file['name'][$i];
            move_uploaded_file( $file['tmp_name'][$i],$pathicon);
            $fileArr[] = "/public/".$path."/".$time."/".$file['name'][$i];
        }
        $filedata = json_encode($fileArr,320);
        return $filedata;
    }

    public function get7day($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }
    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }
    public function out_trade_no(){
        return "c".rand(100,999).uniqid()."p";
    }
    /**
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    protected function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * @param $para 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    protected function createLinkstring($para) {
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
    //RSA2签名
    public function sign2($data) {
        //读取私钥文件
        $priKey = file_get_contents(APP_PATH."/vendor/pay/rsa_private_key.pem");//私钥文件路径
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($data, $sign, $res ,OPENSSL_ALGO_SHA256);
        //释放资源
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        return $sign;
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
}