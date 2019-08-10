<?php
use Yaf\Application;
use Yaf\Dispatcher;
class ApiController extends Yaf\Controller_Abstract {

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
    //登录SDK接口
    public function loginAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $arr['username'] = $reqdata['username'];
        $arr['ts'] = $reqdata['ts'];
        $resultdb = $this->db->field("*")->table("zs_user")->where("mobile_num = {$arr['username']}")->findobj();
        if(empty($resultdb)){
            echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
        }else if( ($resultdb->is_online == '0') && (($arr['ts'] - $resultdb->last_activity_date) < 604800 ) ){
            //7天之内免登录
            $data['register_ip'] = server("REMOTE_ADDR");
            $data['last_activity_date'] = $arr['ts'];
            $this->db->action($this->db->updateSql("user",$data,"mobile_num = {$arr['username']}"));
            //积分系统
            user_bp($resultdb->id,"+5积分",time(),"7天免登录",5);
            $arrx = get_userinfo_data($resultdb->id);
            $resultdb->follow_num = $arrx['follow_num'];
            $resultdb->collection_num = $arrx['collection_num'];
            $resultdb->commonweal_price = $arrx['commonweal_price'];
            echo json_encode(['code'=>0,'message'=>"success","data"=>$resultdb]);exit;
        }else{
            $arr['password'] = $reqdata['password'];
            $arr['uuid'] = $reqdata['uuid'];
            $sign = $reqdata['sign'];
            $result = $this->issign($arr,$sign);
            if($result == "ok"){
                $res = $this->db->field("*")->table("zs_user")->where(" mobile_num = {$arr['username']} and password = '{$arr['password']}' ")->findobj();
                if($res){
                    $zs_user['is_online'] = '0';
                    $zs_user['register_ip'] = server("REMOTE_ADDR");
                    $zs_user['last_activity_date'] = $arr['ts'];
                    $this->db->action($this->db->updateSql("user",$zs_user," mobile_num = {$arr['username']}"));
                    //积分系统
                    user_bp($res->id,"+5积分",time(),"登录",5);
                    $arrx = get_userinfo_data($res->id);
                    $res->follow_num = $arrx['follow_num'];
                    $res->collection_num = $arrx['collection_num'];
                    $res->commonweal_price = $arrx['commonweal_price'];
                    echo json_encode(['code'=>0,'message'=>"success","data"=>$res]);exit;
                }else{
                    echo json_encode(['code'=>1007,'message'=>"账号密码错误，请稍后再试"]);exit;
                }
            }else{
                echo json_encode(['code'=>100,'message'=>"签名错误"]);exit;
            }
        }
    }
    //注册SDK接口
    public function registerAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $arr['username'] = $reqdata['username'];
        $resultdb = $this->db->field("*")->table("zs_user")->where("mobile_num = {$arr['username']}")->findobj();
        if(!empty($resultdb)){
            echo json_encode(['code'=>1003,'message'=>"手机号已经注册过，请登入"]);exit;
        }else{
            $arr['password'] = $reqdata['password'];
            $arr['uuid']= $reqdata['uuid'];
            $arr['ts'] = $reqdata['ts'];
            $sign = $reqdata['sign'];
            //验证sign
            $result = $this->issign($arr,$sign);
            if($result == "ok"){
                $user['uid'] =  "C".substr(time(),1);
                $user['account'] =  $arr['username'];
                $user['password'] =  $arr['password'];
                $user['mobile_num'] =  $arr['username'];
                $user['sex'] =  "保密";
                $user['nick_name'] =  "";
                $user['location_province'] =  "";
                $user['location_city'] =  "";
                $user['location_area'] =  "";
                $user['real_name'] =  "";
                $user['id_card'] =  "";
                $user['user_level'] = 0;
                $user['user_exp'] =  0;
                $user['lock_status'] =  '0';
                $user['register_time'] =  $arr['ts'];
                $user['register_ip'] =  server("REMOTE_ADDR");
                $user['last_activity_date'] =  $arr['ts'];
                $user['is_online'] =  '1';
                $user['avatar'] = '';
                $outtime = $this->get7day($arr['ts']);
                $rand1 = range("a","z");
                $rand2 = range("0","9");
                $rand3 = range("A","Z");
                $randstr = $rand1[0].$rand2[0].$rand3[0];
                $basestr = $user['uid'].$outtime.$randstr;
                $user['access_token'] = hash_hmac("sha256",$basestr,self::APP_KEY);
                $bool = $this->db->action($this->db->insertSql("user",$user));
                if($bool){
                    $userData = $this->db->field("*")->table("zs_user")->where("uid = '{$user['uid']}'")->find();
                    $user['id'] = $userData['id'];
                    $this->register($arr['username'],$user['uid']); //同步IOS数据
                    echo json_encode(['code'=>0,'message'=>"success","data"=>$user]);exit;
                }else{
                    echo json_encode(['code'=>1006,'message'=>"注册失败，请稍后再试"]);exit;
                }
            }
        }
    }
    //手机验证码登录
    public function phoneloginAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $arr['username'] = $reqdata['username'];
        $resultdb = $this->db->field("*")->table("zs_user")->where("mobile_num = {$arr['username']}")->findobj();
        if(empty($resultdb)){
            echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
        }else{
            $arr['dynamic_code'] = $reqdata['dynamic_code'];
            $arr['uuid'] = $reqdata['uuid'];
            $arr['ts'] = $reqdata['ts'];
            $sign = $reqdata['sign'];
            $result = $this->issign($arr,$sign);
            if($result){
                $locktime = time();
                $telsvcode = $this->db->field("*")->table("zs_short_message")->where(" phone = '{$arr['username']}' and code = '{$arr['dynamic_code']}' ")->findobj();
                $datatime = $telsvcode->send_time;
                if( ($locktime - $datatime) < 600){
                    $datacode = $telsvcode->code;
                    $lockcode = $reqdata['dynamic_code'];
                    if($datacode == $lockcode){
                        $zs_user['is_online'] = '0';
                        $zs_user['register_ip'] = server("REMOTE_ADDR");
                        $zs_user['last_activity_date'] = $arr['ts'];
                        $this->db->action($this->db->updateSql("user",$zs_user," mobile_num = {$arr['username']}"));
                        //积分系统
                        user_bp($resultdb->id,"+5积分",time(),"7天免登录",5);
                        $arrx = get_userinfo_data($resultdb->id);
                        $resultdb->follow_num = $arrx['follow_num'];
                        $resultdb->collection_num = $arrx['collection_num'];
                        $resultdb->commonweal_price = $arrx['commonweal_price'];
                        echo json_encode(['code'=>0,'message'=>"success","data"=>$resultdb]);exit;
                    }else{
                        echo json_encode(["code"=>1002,"message"=>"验证码错误"]);
                    }
                }else{
                    echo json_encode(["code"=>1005,"message"=>"验证码过期"]);
                }
            }else{
                echo json_encode(['code'=>100,'message'=>"签名错误"]);exit;
            }

        }
    }
    //退出SDK接口
    public function logoutAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $arr['username'] = $reqdata['username'];
        $resultdb = $this->db->field("*")->table("zs_user")->where("mobile_num = {$arr['username']}")->find();
        if(empty($resultdb)){
            echo json_encode(['code'=>1000,'message'=>"请输入正确的手机号"]);exit;
        }else{
            $data['is_online'] = '1';
            $this->db->action($this->db->updateSql("user",$data," mobile_num = {$arr['username']}"));
            echo json_encode(['code'=>0,'message'=>"success"]);exit;
        }
    }
    //找回密码SDK接口
    public function forgetpasswordAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $arr['username'] = $reqdata['username'];
        $resultdb = $this->db->field("*")->table("zs_user")->where("mobile_num = {$arr['username']}")->find();
        if(empty($resultdb)){
            echo json_encode(['code'=>1004,'message'=>"手机号不存在，请注册"]);exit;
        }else{
            $arr['dynamic_code'] = $reqdata['dynamic_code'];
            $phonecode = $this->db->field("*")->table("zs_short_message")->where(" phone = '{$arr['username']}' and code = '{$arr['dynamic_code']}' ")->find();
            if(!empty($phonecode)){
                $arr['password'] = $reqdata['password'];
                if(!empty($arr['password'])){
                    $data['password'] = $arr['password'];
                    $bool = $this->db->action($this->db->updateSql("user",$data," mobile_num = {$arr['username']}"));
                    $this->db->action($this->db->updateSql("ios_user",$data," mobile_num = {$arr['username']}")); //同步IOS数据
                    if($bool){
                        echo json_encode(["code"=>0,"message"=>"success"]);
                    }else{
                        echo json_encode(["code"=>500,"message"=>"系统繁忙，请稍候再试"]);exit;
                    }
                }else{
                    echo json_encode(['code'=>1008,'message'=>"	密码为空"]);exit;
                }
            }else{
                echo json_encode(['code'=>1002,'message'=>"验证码错误"]);exit;
            }
        }
    }
    //第三方登录SDK接口
    public function sdkloginAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $sdkid = $reqdata['sdkid'];
        $type = $reqdata['type'];
        $ts = $reqdata['ts'];
        $resultdb = $this->db->field("*")->table("zs_user")->where("{$type} = '{$sdkid}'")->find();
        if(!empty($resultdb)){
            $zs_user['is_online'] = '0';
            $zs_user['register_ip'] = server("REMOTE_ADDR");
            $zs_user['last_activity_date'] = $ts;
            $zs_user[$type] = $sdkid;
            $this->db->action($this->db->updateSql("user",$zs_user,"{$type} = '{$sdkid}'"));
            //积分系统
            user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
            $arrx = get_userinfo_data($resultdb['id']);
            $resultdb['follow_num'] = $arrx['follow_num'];
            $resultdb['collection_num'] = $arrx['collection_num'];
            $resultdb['commonweal_price'] = $arrx['commonweal_price'];
            echo json_encode(['code'=>0,'message'=>"success",'data'=>$resultdb]);
        }else{
            echo json_encode(['code'=>0,'message'=>"success"]);
        }
    }
    //第三方登录手机号验证SDK接口
    public function sdkloginphoneAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $sdkid = $reqdata['sdkid'];
        $type = $reqdata['type'];
        $mobile_num = $reqdata['mobile_num'];
        $ts = $reqdata['ts'];
        $resultdb = $this->db->field("*")->table("zs_user")->where(" mobile_num = {$mobile_num}")->find();
        if(!empty($resultdb)){
            //登录
            $zs_user['is_online'] = '0';
            $zs_user['register_ip'] = server("REMOTE_ADDR");
            $zs_user['last_activity_date'] = $ts;
            $zs_user[$type] = $sdkid;
            $bool = $this->db->action($this->db->updateSql("user",$zs_user," mobile_num = {$mobile_num}"));
            //积分系统
            user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
        }else{
            //注册
            $user['uid'] =  "C".substr(time(),1);
            $user['account'] =  $mobile_num;
            $user['password'] =  $this->hmac256("");;
            $user['mobile_num'] =  $mobile_num;
            $user['sex'] =  "保密";
            $user['nick_name'] =  "";
            $user['location_province'] =  "";
            $user['location_city'] =  "";
            $user['location_area'] =  "";
            $user['real_name'] =  "";
            $user['id_card'] =  "";
            $user['user_level'] = 0;
            $user['user_exp'] =  0;
            $user['lock_status'] =  '0';
            $user['register_time'] =  time();
            $user['register_ip'] =  server("REMOTE_ADDR");
            $user['last_activity_date'] =  time();
            $user['is_online'] =  '1';
            $user['avatar'] = '';
            $outtime = $this->get7day(time());
            $rand1 = range("a","z");
            $rand2 = range("0","9");
            $rand3 = range("A","Z");
            $randstr = $rand1[0].$rand2[0].$rand3[0];
            $basestr = $user['uid'].$outtime.$randstr;
            $user['access_token'] = $this->hmac256($basestr);
            $user['competence'] = '普通用户';
            $user[$type] = $sdkid;
            $bool = $this->db->action($this->db->insertSql("user",$user));
        }
        if($bool){
            $resultdb = $this->db->field("*")->table("zs_user")->where(" mobile_num = {$mobile_num}")->find();
            user_bp($resultdb['id'],"+5积分",time(),"第三方登录",5);
            $arrx = get_userinfo_data($resultdb['id']);
            $resultdb['follow_num'] = $arrx['follow_num'];
            $resultdb['collection_num'] = $arrx['collection_num'];
            $resultdb['commonweal_price'] = $arrx['commonweal_price'];
            $this->register($mobile_num,$user['uid']); //同步IOS数据
            echo json_encode(['code'=>0,'message'=>"success",'data'=>$resultdb]);
        }else{
            echo json_encode(['code'=>500,'message'=>"系统繁忙，请稍候再试"]);
            exit;
        }
    }
    /**
     * 发送验证码判断
     */
    public function sendvcodeAction() {
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $phone = isset($reqdata['phone'])?$reqdata['phone']:"";
        $name = $phone;
        if (empty($phone) || empty($name)) {
            echo json_encode(['code'=>1000,'message'=>"请输入正确的手机号"]);exit;
        }
        $this->telsvcodeAction($phone);
    }
    /**
     * 发送手机安全码
     */
    public function telsvcodeAction($phone=null,$delay=10,$flag=true) {
        Dispatcher::getInstance()->autoRender(false);
        include APP_PATH."/application/core/Xigu.php";
        if (empty($phone) || (strlen($phone) != 11)) {
            echo json_encode(['code'=>1000,'message'=>"请输入正确的手机号"]);exit;
        }
        // 产生手机安全码并发送到手机且存到session
        $rand = rand(100000,999999);
        $smsconfig = ['sms_set' => [
            'smtp' => 'MDAwMDAwMDAwMK62sG1_enZnf7HJmLHc',
            'smtp_account' => 'MDAwMDAwMDAwMLq5qLB_oIJnf4u73bDc',
            'smtp_password' => '273',
            'smtp_port' => '25615'
        ]];
        $xigu = new \app\core\Xigu($smsconfig);
        $param = $rand.",".$delay;
        $result = json_decode($xigu->sendSM($smsconfig['sms_set']['smtp_account'],$phone,$smsconfig['sms_set']['smtp_password'],$param),true);
        // 存储短信发送记录信息
        $result['create_time'] = time();
        $result['pid']=0;

        $zs_short_message['phone'] = $phone;
        $zs_short_message['code'] = $rand;
        $zs_short_message['send_time'] = $result['create_time'];
        $zs_short_message['smsId'] = $result['smsId'];
        $zs_short_message['create_time'] = $result['create_time'];
        $zs_short_message['pid'] = $result['pid'];
        $status = ($result['code'] == 200) ? 1 : 0;
        $zs_short_message['status'] = $status;
        $zs_short_message['ratio'] = 0;

        $this->db->action($this->db->insertSql("short_message",$zs_short_message));

        if ($result['send_status'] != '000000') {
            echo json_encode(['code'=>1001,'message'=>'获取验证码频率过高，请稍后']);exit;
        }
        $telsvcode['code']=$rand;
        $telsvcode['phone']=$phone;
        $telsvcode['time']=$result['create_time'];
        $telsvcode['delay']=$delay;
        //$this->session->set("telsvcode", $telsvcode);
        if ($flag) {
            echo json_encode(['code'=>0,'message'=>"success"]);
        } else{
            echo json_encode(['code'=>0,'message'=>"success"]);
        }

    }
    /**
     * 手机验证码验证
     */
    public function ajaxsmsAction(){
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = json_decode($this->data,true);
        $locktime = time();
        $phone = $reqdata['phone'];
        $lockcode = $reqdata['dynamic_code'];
        $telsvcode = $this->db->field("*")->table("zs_short_message")->where(" phone = '{$phone}' and code = '{$lockcode}' ")->find();
        if($telsvcode){
            $datatime = $telsvcode['send_time'];
            if( ($locktime - $datatime) < 600){
                $datacode = $telsvcode['code'];
                if($datacode == $lockcode){
                    echo json_encode(["code"=>0,"message"=>"success"]);
                }else{
                    echo json_encode(["code"=>1002,"message"=>"验证码错误"]);
                }
            }else{
                echo json_encode(["code"=>1005,"message"=>"验证码过期"]);
            }
        }else{
            echo json_encode(["code"=>1002,"message"=>"验证码错误"]);
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
    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }

    //注册接口
    public function register($mobile_num,$uid){
        $user['uid'] =  $uid;
        $user['account'] =  $mobile_num;
        $user['password'] =  $this->hmac256("123456");
        $user['mobile_num'] =  $mobile_num;
        $user['sex'] =  "保密";
        $user['nick_name'] =  "";
        $user['real_name'] =  "";
        $user['id_card'] =  "";
        $user['user_level'] = 0;
        $user['user_exp'] =  0;
        $user['lock_status'] =  '0';
        $user['register_time'] =  time();
        $user['register_ip'] =  server("REMOTE_ADDR");
        $user['last_activity_date'] =  time();
        $user['is_online'] =  '1';
        $user['avatar'] = '';
        $user['pet_cur'] = 0;
        $user['user_age'] = 0;
        $user['pet_age'] = 0;  
        $outtime = $this->get7day(time());
        $rand1 = range("a","z");
        $rand2 = range("0","9");
        $rand3 = range("A","Z");
        $randstr = $rand1[0].$rand2[0].$rand3[0];
        $basestr = $user['uid'].$outtime.$randstr;
        $user['access_token'] = $this->hmac256($basestr);
        $user['competence'] = '宠爱用户';
        $bool = $this->db->action($this->db->insertSql("ios_user",$user));
        return $bool;
    }


}