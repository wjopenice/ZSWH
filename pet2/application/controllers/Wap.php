<?php
use Yaf\Application;
use Yaf\Dispatcher;
class WapController extends Yaf\Controller_Abstract{
    public $db;
    public function init(){
        $this->db = new dbModel();
    }
    public function indexAction(){
        $newData = $this->db->action("SELECT id,title,create_time,content,type,pic FROM zs_news WHERE type=1 ORDER BY id DESC LIMIT 0,5 ");
        foreach ($newData as $key=>$value){
            $newData[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
        }
        $this->getView()->assign(["newData"=>$newData]);
    }
    public function aboutAction(){
        $this->getView()->assign(["xxx"=>"yyy"]);
    }
    public function introduceAction(){
        $this->getView()->assign(["xxx"=>"yyy"]);
    }
    public function newsAction(){
        include APP_PATH."/application/core/Page.php";
        $page2 = new \app\core\Page();
        $len = $this->db->zscount("news","*","total"," type=1 ");
        $showpage = 6;
        $page2->init($len,$showpage);
        $strpage = "SELECT id,title,create_time,content,type,pic FROM zs_news WHERE type=1 ORDER BY id DESC {$page2->limit}";
        $newData = $this->db->action($strpage);
        foreach($newData as $key=>$value){
            $newData[$key]['content'] = mb_substr(strip_tags(htmlspecialchars_decode($value['content'])),0,60,"utf-8");
        }
        $showstr = $page2->wapshow();
        $this->getView()->assign(["newData"=>$newData,"showstr"=>$showstr]);
    }
    //宠爱之家官网文章详情
    public function detailAction(){
        $id = get("id");
        $oneData = $this->db->field("*")->table("zs_news")->where("id = {$id}")->find();
        $this->getView()->assign(["oneData"=>$oneData]);
    }
    public function downloadAction(){
        if(isweixin()){
            $this->getView()->assign(["xxx"=>"yyyy"]);
        }else{
            if(isapple()){
                Dispatcher::getInstance()->autoRender(false);
                echo "<script>alert('IOS工程师疯狂研发中，支持Andriod下载');window.location.href='/wap/index';</script>";
                //alertText("请使用安卓手机下载此APP","/wap/index");
            }else{
                Dispatcher::getInstance()->autoRender(false);
                $arrData = $this->db->field("update_address")->table("zs_setting")->find();
                $filename = $arrData['update_address'];
                apkdownload(APP_PATH.$filename);
            }
        }
    }

    public function buildordernoAction(){
        return "pet".date('YmdHis').substr(implode(array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8)."tap";
    }

    public function hmac256($data){
        return hash_hmac("sha256",$data,"MDc2OWJkYWI0ZGJiMmMxMzBjNzA3MGQ5NTU0MDVkODE=");
    }

    public function get7dayAction($timex){
        return strtotime(date("Y-m-d H:i:s",$timex+60*60*24*7));
    }

    //
    public function ajaxmobilenumAction(){
        Dispatcher::getInstance()->autoRender(false);
        if(!empty($_SESSION['mobile_num'])){
            $mobile_num = $_SESSION['mobile_num'];
            $userData = $this->db->field("*")->table("zs_user")->where("mobile_num = {$mobile_num}")->select();
            if(empty($userData)){
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
            if(!empty($arrData)){
                //跳转审核页面
                header("location:/wap/examine");
            }else{
                $type = isset($_GET['type'])?$_GET['type']:"x";
                if($type == 'wx1'){
                    //跳转提交参数页面
                    header("location:/wap/rescueinfowx?type=$type");
                }else if($type == 'wx2'){
                    //跳转提交参数页面
                    header("location:/wap/rescueinfowx?type=$type");
                }else if($type == 'wx3'){
                    //跳转提交参数页面
                    header("location:/wap/rescueinfowx?type=$type");
                }else{
                    //跳转提交参数页面
                    header("location:/wap/rescueinfo");
                }
            }
        }else{
            //跳转登录页面
            header("location:/login/waplogin");
        }
    }
    //求助站审核页面
    public function examineAction(){
        if(empty($_SESSION['mobile_num'])){
            success("请先登录","/login/waplogin");
            exit;
        }else{
            $merc = $this->db->field("status")->table("zs_user_merc")->where("mobile_num = {$_COOKIE['mobile_num']}")->find();
            $status = $merc['status'];
            $this->getView()->assign(["status"=>$status]);
        }
    }
    //微信用户添加资料
    public function rescueinfowxAction(){
        $type = get("type");
        $this->getView()->assign(["type"=>$type]);
    }
    //添加资料页面
    public function rescueinfoAction(){
        $this->getView()->assign(["xxx"=>"yyyy"]);
    }
    //当前审核进度
    public function rescueinfo2Action(){
        $mobile_num = get("mobile_num");
        $oneData = $this->db->field("*")->table("zs_user_merc")->where("mobile_num = {$mobile_num}")->findobj();
        $this->getView()->assign(["oneData"=>$oneData]);
    }
    //
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
    //
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
    //救助站返回修改信息
    public function rescueeditAction(){
        $mobile_num = get("mobile_num");
        $oneData = $this->db->field("*")->table("zs_user_merc")->where("mobile_num = {$mobile_num}")->findobj();
        $this->getView()->assign(["oneData"=>$oneData]);
    }

    public function rescueAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = $_POST;
        $bool = $this->db->action($this->db->insertSql("rescue_questionnaire",$data));
        if($bool){
            success("信息提交成功，请您保持手机畅通，稍后我们商务会跟您取得联系！","http://www.pettap.cn/giveraise.html");
        }else{
            error("信息提交失败");
        }
    }

    public function adoptionAction(){
        Dispatcher::getInstance()->autoRender(false);
        $data = $_POST;
        $bool = $this->db->action($this->db->insertSql("rescue_adoption",$data));
        if($bool){
            success("信息提交成功，请您保持手机畅通，稍后我们商务会跟您取得联系！","http://www.pettap.cn/adopt.html");
        }else{
            error("信息提交失败");
        }
    }
}
