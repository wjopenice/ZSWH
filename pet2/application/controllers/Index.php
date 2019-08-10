<?php
use Yaf\Application;
use Yaf\Dispatcher;
class IndexController extends Yaf\Controller_Abstract {
    public $db;
    const APP_KEY = "MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=";
    public function init(){
        $this->db = new dbModel();
    }
    //宠爱之家官网首页
    public function indexAction() {//默认Action
        if(isMobile()){
            header("location:/wap/index");
        }else{
            $currentPage = empty($_GET["page"])?"1":$_GET["page"];
            $showPage = 5;
            $start =  ($currentPage-1)*$showPage;
            $newData = $this->db->action("SELECT id,title,create_time,content,type FROM zs_news WHERE type=1 ORDER BY id DESC LIMIT {$start},{$showPage} ");
            foreach ($newData as $key=>$value){
                $newData[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
            }
            $arrData = $this->db->field("update_address")->table("zs_setting")->find();
            $filename = "http://".server("SERVER_NAME").$arrData['update_address'];
            $this->getView()->assign(["newData"=>$newData,"filename"=>$filename]);
        }
       /* $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = 5;
        $start =  ($currentPage-1)*$showPage;
        $newData = $this->db->action("SELECT id,title,create_time,content,type FROM zs_news WHERE type=1 ORDER BY id DESC LIMIT {$start},{$showPage} ");
        foreach ($newData as $key=>$value){
            $newData[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
        }
        $this->getView()->assign(["newData"=>$newData]);*/
    }
    //宠爱之家官网首页
    public function aboutAction(){
        $this->getView()->assign(["xxx"=>"yyy"]);
    }
    //宠爱之家官网新闻
    public function newsAction(){
        include APP_PATH."/application/core/Page.php";
        $pages = new \app\core\Page();
        $len = $this->db->zscount("news","*","total","type=1");
        $pages->init($len,12);
        $showstr = $pages->show();
        $page = $this->db->action("SELECT id,title,create_time,content,type,pic FROM zs_news WHERE type=1 ORDER BY id DESC {$pages->limit} ");
        foreach ($page as $key=>$value){
            $page[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
        }
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }
    //宠爱之家官网文章详情
    public function detailAction(){
        $id = get("id");
        $oneData = $this->db->field("*")->table("zs_news")->where("id = {$id}")->find();
        $this->getView()->assign(["oneData"=>$oneData]);
    }
    //宠爱之家官网救助站
    public function rescueAction(){
        $this->getView()->assign(["xxx"=>"yyy"]);
    }
    //救助站资料
    public function rescueinfoAction(){
        $this->getView()->assign(["xxx"=>"yyy"]);
    }
    //登录
    public function loginAction(){
        $this->getView()->assign(["xxx"=>"yyy"]);
    }
    //退出
    public function logoutAction(){
        Dispatcher::getInstance()->autoRender(false);
        if(!empty($_COOKIE['mobile_num'])){
            setcookie("mobile_num","",time()-1);
            header("location:/index/login");
        }else{
            header("location:/index/login");
            exit;
        }
    }
    //求助站审核页面
    public function examineAction(){
        if(empty($_COOKIE['mobile_num'])){
            success("请先登录","/index/login");
            exit;
        }else{
            $merc = $this->db->field("status")->table("zs_user_merc")->where("mobile_num = {$_COOKIE['mobile_num']}")->find();
            $status = $merc['status'];
            $this->getView()->assign(["status"=>$status]);
        }
    }

    public function rescueeditAction(){
        $mobile_num = get("mobile_num");
        $oneData = $this->db->field("*")->table("zs_user_merc")->where("mobile_num = {$mobile_num}")->findobj();
        $this->getView()->assign(["oneData"=>$oneData]);
    }

    public function rescueinfo2Action(){
        $mobile_num = get("mobile_num");
        $oneData = $this->db->field("*")->table("zs_user_merc")->where("mobile_num = {$mobile_num}")->findobj();
        $this->getView()->assign(["oneData"=>$oneData]);
    }
    //救助站接收资料
    public function getuserdataAction(){
        Dispatcher::getInstance()->autoRender(false);
        $zs_user_merc = $_POST;
        $zs_user_merc['commercial'] = urldecode($zs_user_merc['commercial']);
        $zs_user_merc['sex'] = urldecode($zs_user_merc['sex']);
        $zs_user_merc['real_name'] = urldecode($zs_user_merc['real_name']);
        $zs_user_merc['address'] = urldecode($zs_user_merc['address']);
        $zs_user_merc['info'] = urldecode($zs_user_merc['info']);
        $zs_user_merc['create_time'] = time();
        $success = $this->db->action($this->db->insertSql("user_merc",$zs_user_merc));
        if($success){
            echo json_encode(["msg"=>"ok"]);
        }else{
            echo json_encode(["msg"=>"no"]);
        }
    }
    //救助站修改资料
    public function edituserdataAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = post("id");
        $zs_user_merc = $_POST;
        $success = $this->db->action($this->db->updateSql("user_merc",$zs_user_merc,"id = {$id}"));
        //['commercial','sex','real_name','id_card','mobile_num','location_province','location_city','location_area','address','info','idcard','reidcard','handidcard','permit','field1','field2','field3','field4','status']);
        if($success){
            echo json_encode(["msg"=>"ok"]);
        }else{
            echo json_encode(["msg"=>"no"]);
        }
    }
    //救助站检查用户是否提交审核资料
    public function ajaxmobilenumAction(){
        Dispatcher::getInstance()->autoRender(false);
        if(!empty($_COOKIE['mobile_num'])){
            $mobile_num = $_COOKIE['mobile_num'];
            $userData = $this->db->field("*")->table("zs_user")->where("mobile_num = {$mobile_num}")->select();
            if(empty($userData)){
                $user['uid'] =  "C".substr(time(),1);
                $user['account'] =  $mobile_num;
                $user['password'] =  $this->hmac256("");;
                $user['mobile_num'] =  $mobile_num;
                $user['sex'] =  "保密";
                $user['nick_name'] =  "";
                $user['location_province'] =  0;
                $user['location_city'] =  0;
                $user['location_area'] =  0;
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
                $user['competence'] = '普通用户';
                $user['competence'] = '普通用户';
                $this->db->action($this->db->insertSql("user",$user));
            }
            $arrData = $this->db->field("*")->table("zs_user_merc")->where("mobile_num = {$mobile_num}")->select();
            if(!empty($arrData)){
                //跳转审核页面
                header("location:/index/examine");
            }else{
                //跳转提交参数页面
                header("location:/index/rescueinfo");
            }
        }else{
            //跳转登录页面
            header("location:/index/login");
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
                    setcookie('mobile_num',$phone,time()+60*60);
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
    //文件上传
    public function uploadAction(){
        Dispatcher::getInstance()->autoRender(false);
        if(!empty($_FILES['file'])){
            $time = time();
            $fileicon = files("file");
            $dir = APP_PATH."/public/merc/".$time;
            if(!file_exists($dir)){
                mkdir($dir,0777,true);
            }
            $pathicon = $dir."/".$fileicon['name'];
            $bool = move_uploaded_file( $fileicon['tmp_name'],$pathicon);
            if($bool){
                $fileArr = "/merc/".$time."/".$fileicon['name'];
                echo json_encode(["msg"=>"ok","data"=>$fileArr]);
            }else{
                echo json_encode(["msg"=>"error"]);
            }
        }else{
            echo json_encode(["msg"=>"no"]);
        }
    }
    public function buildordernoAction(){
        return "pet".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."tap";
    }
    public function get7dayAction($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }
    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }
    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }

    public function guideAction(){
        $this->getView()->assign(["xxxx"=>"yyyyy"]);
    }
}
?>
