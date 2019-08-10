<?php
use Yaf\Application;
use Yaf\Dispatcher;
class LoginController extends Yaf\Controller_Abstract  {
    public $db;
    public function init(){
        $this->db = new dbModel();
    }
    public function loginAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $u = addslashes(post("u"));
            $p = $this->hmac256(addslashes(post("p")));
            $result = $this->db->field("*")->table("zs_system")->where("user = '{$u}' and pass = '{$p}'")->find();
            if($u == $result['user'] && $p == $result['pass']){
            	$_SESSION['username'] = $u;
            	$time = date("Y-m-d H:i:s",time());
                $this->db->action($this->db->updateSql("system", ["login_time"=>$time], " user = '{$u}'"));
                echo json_encode(["msg"=>"ok"]);
            }else{
            	echo json_encode(["msg"=>"no"]);
            }
        }else{
        	$this->getView()->assign("content", "xxxxxx");
        }
    }
	public function logoutAction(){
		Dispatcher::getInstance()->autoRender(false);
		unset($_SESSION['username']);
        header("location:/login/login");
    }
    public function codeAction(){
    	Dispatcher::getInstance()->autoRender(false);
        include APP_PATH."/application/core/Image.php";
        header("content-type:image/png");
        \app\core\Image::code(160,56,25,15,35,35,"/public/fonts/MSYHBD.TTC");
        $this->getView()->assign(["xxx"=>"yyy"]);
    }

    public function waploginAction(){
        $type = isset($_GET['type'])?$_GET['type']:"x";
        $this->getView()->assign(["type"=>$type]);
    }

    public function authorAction(){
        if($this->getRequest()->isPost()){
            Dispatcher::getInstance()->autoRender(false);
            $mobile_num = post("mobile_num");
            setcookie("mobile_num",$mobile_num,time()+3600,"/",".pettap.cn");
            $userData = $this->db->field("*")->table("zs_user")->where("mobile_num = {$mobile_num}")->select();
            if(empty($userData)){
                $user['uid'] =  "C".substr(time(),1);
                $user['account'] =  $mobile_num;
                $user['password'] =  $this->hmac256("123456");
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
                $outtime = $this->get7dayAction(time());
                $rand1 = range("a","z");
                $rand2 = range("0","9");
                $rand3 = range("A","Z");
                $randstr = $rand1[0].$rand2[0].$rand3[0];
                $basestr = $user['uid'].$outtime.$randstr;
                $user['access_token'] = $this->hmac256($basestr);
                $user['competence'] = '普通用户';
                $this->db->action($this->db->insertSql("user",$user));
            }
            $arrData = $this->db->field("*")->table("zs_user_merc")->where("mobile_num = {$mobile_num}")->select();
            $this->register($mobile_num,$user['uid']);
            if(!empty($arrData)){
                //跳转审核页面
                header("location:/index/examine");
            }else{
                //跳转提交参数页面
                setcookie("mobile_num",$mobile_num,time()+3600,"/",".pettap.cn");
                header("location:/index/rescueinfo");
            }
        }else{
            $this->getView()->assign(["xxxx"=>"yyyyy"]);
        }
    }

    /**
     * 发送验证码判断
     */
    public function sendvcodeAction() {
        Dispatcher::getInstance()->autoRender(false);
        $reqdata = post("mobile_num");
        $phone = isset($reqdata)?$reqdata:"";
        $name = $phone;
        if (empty($phone) || empty($name) || (strlen($phone) != 11)) {
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
        if (empty($phone)) {
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
        $locktime = time();
        $phone = post('phone');
        $lockcode = post('dynamic_code');
        $telsvcode = $this->db->field("*")->table("zs_short_message")->where("phone = '{$phone}' and code = '{$lockcode}'")->find();
        if($telsvcode){
            $datatime = $telsvcode['send_time'];
            if( ($locktime - $datatime) < 600){
                $datacode = $telsvcode['code'];
                if($datacode == $lockcode){
                    //setcookie('mobile_num',$phone,time()+60*60);
                    setcookie("mobile_num",$phone,time()+3600,"/",".pettap.cn");
                    $_SESSION['mobile_num'] = $phone;
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

    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }

    public function buildordernoAction(){
        return "pet".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."tap";
    }
    public function get7dayAction($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
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
    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }

    public function get7day($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }

}