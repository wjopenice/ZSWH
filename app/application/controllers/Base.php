<?php
use Yaf\Application;
use Yaf\Dispatcher;
use Error\CodeConfigModel;
use Helper\Mem;
use \JPush\PushPayload;
use \JPush\Client;
use \JPush\Exceptions\JPushException;
//注释：继承此类的子类不会出发空操作
//查看隐藏类请使用反射：ReflectionClass::export(类名);
class BaseController extends Yaf\Controller_Abstract {
    const APP_KEY = "07af9a703cb1999c0f4a5404";
    const SECRET = "b62e73366f5034a028143a18";
    public $client;
    //平台
    public $platform = [1=>"all",2=>['android','ios'],3=>'android',4=>'ios'];
    //推送类型(1所有，2别名，3标签)
    public $send_type = [1=>'all',2=>'alias',3=>'tag'];
    public $mem;
    public $db;
    public $error;
    public $request;
    public $response;
    public $cookies;
    public $files;
    public $input;
    public function init(){
        Dispatcher::getInstance()->autoRender(FALSE);
        $this->mem = new Mem();
        $this->db = new dbModel();
        $this->error = (new CodeConfigModel())->getCodeConfig();
        $this->request = $this->getRequest();
        $this->response = $this->getResponse();
        $this->cookies = $this->getRequest()->getCookie();
        $this->files = $this->getRequest()->getFiles();
        $this->input = json_decode(file_get_contents("php://input"),true);
        try{
            $this->client = new Client(self::APP_KEY, self::SECRET);
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
    public function jpapi($type,$uid,$pid,$pnickname,$pavatar,$data,$platform_id = 3,$send_type_id = 2,$tag=""){
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
            case 0:$str = "赞了你的圈子";$url="wanzhuan://gamebox/likes_list";break;
            case 1:$str = "评论了你的圈子";$url="wanzhuan://gamebox/reply_list";break;
            case 2:$str = "回复了你";$url="wanzhuan://gamebox/reply_list";break;
            case 3:$str = "赞了你的评论";$url="wanzhuan://gamebox/likes_list";break;
        }
        if($pnickname == ""){
            $messagestr = $pid.$str;
            $title = $pid;
        }else{
            $messagestr = $pnickname.$str;
            $title = $pnickname;
        }
        if($pavatar == ""){
            $avatar = "http://app.zhishengwh.com/dx.png";
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

        switch ($platform_id){
            case 1:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$datamessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 2:
                //设置安卓推送
                $pusher = $pusher->androidNotification($alert,$datamessage);
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$datamessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
            case 3:
                //设置安卓推送
                $pusher->androidNotification($alert,$datamessage);
                //设置自定义推送
                $pusher->message($alert,$datamessage);
                break;
            case 4:
                //设置IOS推送
                $pusher = $pusher->iosNotification($alert,$datamessage);
                //设置自定义推送
                $pusher = $pusher->message($alert,$datamessage);
                break;
        }
        try {
            $pusher->send();
            $this->ajax_return(0);
        } catch (\JPush\Exceptions\JPushException $e) {
            $this->ajax_return(0);
        }
    }
    public function ajax_return($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]]);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data]);exit;
        }
    }

    public function ajax_return_320($code,$data = null){
        if(is_null($data)){
            echo json_encode(['code'=>$code,'message'=>$this->error[$code]],320);exit;
        }else{
            echo json_encode(['code'=>$code,'message'=>$this->error[$code],"data"=>$data],320);exit;
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

    //301跳转
    public function redirect($url){
        $this->forward($url);
    }

    //游戏赚积分
    public function game_bp($user,$bp,$optime,$bp_type,$jf,$level_type,$status,$game_id){
        $ptime = date("Y-m-d",$optime); //时间戳转为日期
        $user_result =  $this->db->field("*")->table("tab_user")->where(" id = {$user} ")->findobj();
         if(!empty($user_result)){
             if($level_type==4)
             {
                 $result = $this->db->field("*")->table("tab_user_bp")->where(" user = {$user} and share_id = '{$game_id}' and optime='{$ptime}'")->find();
             }
             else{
                 $result = $this->db->field("*")->table("tab_user_bp")->where(" user = {$user} and game_id = '{$game_id}' and type={$level_type}")->find();
             }

            if($status==0)
            //用户积分日志
            {
                $zs_user_bp['user'] = $user;
                $zs_user_bp['bp'] = $bp;
                $zs_user_bp['optime'] = $ptime;
                $zs_user_bp['bp_type'] = $bp_type;
                $zs_user_bp['type'] = $level_type;
                if($level_type==4)
                {
                    $zs_user_bp['share_id'] = $game_id;
                }
                else{
                    $zs_user_bp['game_id'] = $game_id;
                }
                $zs_user_bp['status'] = $status;
                if(empty($result)) {
                    $this->db->action($this->db->insertSql("user_bp", $zs_user_bp));
                    $this->ajax_return(0);
                }
                else{
                    $this->ajax_return(500);
                }
            }
            else{
                //用户积分变动
                $zs_user['points'] = (int)$user_result->points + $jf;
                $this->db->action( $this->db->updateSql("user", $zs_user, "id = {$user}"));
                $zs_user_bp['status'] = $status;
                $this->db->action( $this->db->updateSql("user_bp", $zs_user_bp, "id = {$result['id']}"));
                $this->ajax_return(0);
            }
        }else{
            $this->ajax_return(1010);exit;
        }
    }
    //积分
    public function user_bp($user,$bp,$optime,$bp_type,$jf,$level_type=''){
        $ptime = date("Y-m-d",$optime); //时间戳转为日期
        $user_result =  $this->db->field("*")->table("tab_user")->where(" id = {$user} ")->findobj();
        if(!empty($user_result)){
            if(empty($level_type)) {
                $result =  $this->db->field("*")->table("tab_user_bp")->where(" user = {$user} and optime = '{$ptime}' and type=0")->find();
                if (empty($result)) {
                    //用户积分变动
                    $zs_user['points'] = (int)$user_result->points + $jf;
                    $this->db->action( $this->db->updateSql("user", $zs_user, "id = {$user}"));
                    //用户积分日志
                    $zs_user_bp['user'] = $user;
                    $zs_user_bp['bp'] = $bp;
                    $zs_user_bp['optime'] = $ptime;
                    $zs_user_bp['bp_type'] = $bp_type;
                    $this->db->action( $this->db->insertSql("user_bp", $zs_user_bp));
                }
            }
            else{
                //用户积分变动
                $zs_user['points'] = (int)$user_result->points + $jf;
                $this->db->action( $this->db->updateSql("user", $zs_user, "id = {$user}"));
                //用户积分日志
                $zs_user_bp['user'] = $user;
                $zs_user_bp['bp'] = $bp;
                $zs_user_bp['optime'] = $ptime;
                $zs_user_bp['bp_type'] = $bp_type;
                $zs_user_bp['type']=1;
                $this->db->action( $this->db->insertSql("user_bp", $zs_user_bp));
            }
        }else{
           $this->ajax_return(1010);exit;
        }
    }

    //加密规则
    public function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }

    //post
    public function phpinput(){
        return json_decode(file_get_contents("php://input"),true);
    }

    //换算等级
    public function switch_user_level($level){
        $vip_level = 0;
        switch(true){
            case $level>=30 && $level<90: $vip_level = 1; break;
            case $level>=90 && $level<270: $vip_level = 2; break;
            case $level>=270 && $level<810: $vip_level = 3; break;
            case $level>=810 && $level<2430: $vip_level = 4; break;
            case $level>=2430 && $level<6075: $vip_level = 5; break;
            case $level>=6075 && $level<15187: $vip_level = 6; break;
            case $level>=15187 && $level<37968: $vip_level = 7; break;
            case $level>=37968 : $vip_level = 8; break;
            default:$vip_level = 0; break;
        }
        return $vip_level;
    }

    //
    function get_cover($cover_id, $field = null){
        if(empty($cover_id)){
            return false;
        }
        $picture=$this->db->field("*")->table("sys_picture")->where("id = {$cover_id}")->find();
        if($field == 'path'){
            if(!empty($picture['url'])){
                $picture['path'] = $picture['url'];
            }else{
                $picture['path'] = "http://www.zhishengwh.com".$picture['path'];
            }
        }
        return empty($field) ? $picture : $picture[$field];
    }
    //过滤词语
    function str_rep($str){
        $arrData = $this->db->field("word")->table("tab_filter")->select();
        $arr = [];
        foreach ($arrData as $key=>$value){
            $arr[$key] = $value['word'];
        }
        $re = "***";
        return str_replace($arr,$re,$str);
    }

    //获取CURD请求类型
    public function Get_method(){
        $method = $_SERVER['REQUEST_METHOD'];
        return $method;
    }

    //获取CURD请求数据
    public  function Resp_curl(){
        parse_str(file_get_contents('php://input'), $data);
        $data = array_merge($_GET, $_POST, $data);
        return $data;
    }

    //建立CURD请求模式
    public function Rest_curl($url,$type='GET',$data="",$bool=false,array $headers=["content-type: application/x-www-form-urlencoded;charset=UTF-8"]){
        //post 新增  get查询  put修改  delete删除
        $curl= curl_init();
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_URL,$url);
        if($bool == true){
            curl_setopt($curl, CURLOPT_HEADER, $bool);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        switch ($type){
            case "GET":break;
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:break;
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
        if(curl_exec($curl) === false){
            return "error code:".curl_getinfo($curl, CURLINFO_HTTP_CODE).',error message:'.curl_error($curl);
        }
        $strData = curl_exec($curl);
        curl_close($curl);
        return $strData;
    }

    //获取ip
    public function getIp() {
        $arr_ip_header = array(
            'HTTP_CDN_SRC_IP',
            'HTTP_PROXY_CLIENT_IP',
            'HTTP_WL_PROXY_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        );
        $client_ip = 'unknown';
        foreach ($arr_ip_header as $key)
        {
            if (!empty($_SERVER[$key]) && strtolower($_SERVER[$key]) != 'unknown')
            {
                $client_ip = $_SERVER[$key];
                break;
            }
        }
        return $client_ip;
    }

    /**支付宝
     * 对数组排序
     * @param $para 排序前的数组
     * return 排序后的数组
     */
    protected function argSort($para) {
        ksort($para);
        reset($para);
        return $para;
    }
    /**支付宝
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
    //RSA2签名支付宝
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

    //微信支付随机字符串
    public  function rand_code(){
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';//62个字符
        $str = str_shuffle($str);
        $str = substr($str,0,32);
        return  $str;
    }
    public function getSign($params) {
        ksort($params);        //将参数数组按照参数名ASCII码从小到大排序
        foreach ($params as $key => $item) {
            if (!empty($item)) {         //剔除参数值为空的参数
                $newArr[] = $key.'='.$item;     // 整合新的参数数组
            }
        }
        $stringA = implode("&", $newArr);         //使用 & 符号连接参数
        $stringSignTemp = $stringA."&key="."b2e9b1ada984072c1f0e6cb2709b32a0";        //拼接key
        // key是在商户平台API安全里自己设置的
        $stringSignTemp = MD5($stringSignTemp);       //将字符串进行MD5加密
        $sign = strtoupper($stringSignTemp);      //将所有字符转换为大写
        return $sign;
    }
    public function ToXml($data=array())

    {
        if(!is_array($data) || count($data) <= 0)
        {
            return '数组异常';
        }

        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    public function FromXml($xml)
    {
        if(!$xml){
            echo "xml数据异常！";
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }

    //订单生成
    public function build_order_no(){
        return date('Ymd').date('His').sp_random_string(4);
    }
    //获取用户信息
    function get_userinfo_data($uid){
        $userdata['follow_num'] = count($this->db->field("*")->table("tab_user_follow")->where("user_id = {$uid}")->select());
        $userdata['fans_num'] = count($this->db->field("*")->table("tab_user_fans")->where("user_id = {$uid}")->select());
        return $userdata;
    }
    //alert
    function success($msg,$url){
        echo "<script>alert('".$msg."');window.location.href='".$url."';</script>";
    }
    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }
}
