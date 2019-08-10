<?php
use Yaf\Application;
use Yaf\Dispatcher;
class UserController extends Yaf\Controller_Abstract  {
    public $db;
    public $user;
    public function init(){
        $this->db = new dbModel();
        if(!empty($_SESSION['username'])){
            $this->user = $_SESSION['username'];
        }else{
            success("请先登陆!","/login/login");
            exit;
        }
    }

    public function administratorsAction(){
        $arrData = $this->db->field("*")->table("zs_system")->select();
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    public function userlistAction(){
        include APP_PATH."/application/core/Page.php";
        $page2 = new \app\core\Page();
        $len = $this->db->zscount("user");
        $page2->init($len,13);
        $showstr = $page2->show();
        $page = $this->db->action("SELECT * FROM zs_user ORDER BY id DESC {$page2->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function userdelAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $lock_status = get("status");
        $bool = $this->db->action($this->db->deleteSql("user","id= {$id} and lock_status = '{$lock_status}'"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }

    public function usereditAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = get("id");
        $lock_status = get("status");
        $data['lock_status'] = $lock_status;
        $bool = $this->db->action($this->db->updateSql("user",$data,"id = {$id}"));
        if($bool){
            echo json_encode(['msg'=>"ok"]);
        }else{
            echo json_encode(['msg'=>"no"]);
        }
    }

    public function usersearchAction(){
        $search = addslashes(get("search"));
        $result = $this->db->field("*")->table("zs_user")->where("mobile_num = {$search}")->findobj();
        $this->getView()->assign(["page"=>$result]);
    }

    public function userblackAction(){
        $currentPage = empty($_GET["page"])?"1":$_GET["page"];
        $showPage = 10;
        $start =  ($currentPage-1)*$showPage;
        $page = $this->db->field("*")->table("zs_user")->where("lock_status = '1'")->limit($start,$showPage)->selectobj();
        $this->getView()->assign(["page"=>$page]);
    }

    public function userbpAction(){
        include APP_PATH."/application/core/Page.php";
        $page2 = new \app\core\Page();
        $len = $this->db->zscount("user_bp");
        $page2->init($len,13);
        $showstr = $page2->show();
        $page = $this->db->action("SELECT * FROM zs_user_bp ORDER BY id DESC {$page2->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    public function rescueAction(){
        include APP_PATH."/application/core/Page.php";
        $page2 = new \app\core\Page();
        $len = $this->db->zscount("rescue_questionnaire");
        $page2->init($len,13);
        $showstr = $page2->show();
        $page = $this->db->action("SELECT * FROM zs_rescue_questionnaire ORDER BY id DESC {$page2->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }
    public function adoptionAction(){
        include APP_PATH."/application/core/Page.php";
        $page2 = new \app\core\Page();
        $len = $this->db->zscount("rescue_adoption");
        $page2->init($len,13);
        $showstr = $page2->show();
        $page = $this->db->action("SELECT * FROM zs_rescue_adoption ORDER BY id DESC {$page2->limit} ");
        $this->getView()->assign(["page"=>$page,"showstr"=>$showstr]);
    }

    //救助站列表
    public function verifyAction(){
        $arrData = $this->db->field("*")->table("zs_user_merc")->where("status = '0'")->selectobj();
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    //救助站详情
    public function verifydetailAction(){
        $id = get("id");
        $oneData = $this->db->field("id,create_time,commercial,sex,real_name,id_card,mobile_num,address,info")->table("zs_user_merc")->where("id = {$id}")->findobj();
        $twoData = $this->db->field("idcard,reidcard,handidcard,permit,field1,field2,field3,field4")->table("zs_user_merc")->where("id = {$id}")->findobj();
        $mid = $oneData->id;
        $this->getView()->assign(["oneData"=>$oneData,"twoData"=>$twoData,"mid"=>$mid]);
    }

    public function verifydetail2Action(){
        $id = get("id");
        $oneData = $this->db->field("id,create_time,commercial,sex,real_name,id_card,mobile_num,address,info")->table("zs_user_merc")->where("id = {$id}")->findobj();
        $twoData = $this->db->field("idcard,reidcard,handidcard,permit,field1,field2,field3,field4")->table("zs_user_merc")->where("id = {$id}")->findobj();
        $mid = $oneData->id;
        $this->getView()->assign(["oneData"=>$oneData,"twoData"=>$twoData,"mid"=>$mid]);
    }

    public function verifydataAction(){
        Dispatcher::getInstance()->autoRender(false);
        $id = post("id");
        $result = $this->db->field("*")->table("zs_user_merc")->where("id = {$id}")->findobj();
        $data['is_commercial'] = post("is_commercial");//经营性 非经营性
        $data['is_sex'] = post("is_sex");//男 女
        $data['is_real_name'] = post("is_real_name");//姓名
        $data['is_id_card'] = post("is_id_card");//身份证
        $data['is_mobile_num'] = post("is_mobile_num");//手机号
        $data['is_address'] = post("is_address");//地址
        $data['is_info'] = post("is_info");//公司描述
        $data['is_idcard'] = post("is_idcard");//身份证正面照
        $data['is_reidcard'] = post("is_reidcard");//身份证反面照
        $data['is_handidcard'] = post("is_handidcard");//手持身份证照
        $data['is_permit'] = post("is_permit");//营业执照
        $data['is_field1'] = post("is_field1");//场地照片图
        $data['is_field2'] = post("is_field2");//场地照片图
        $data['is_field3'] = post("is_field3");//场地照片图
        $data['is_field4'] = post("is_field4");//场地照片图
        $bool = $this->db->action($this->db->updateSql("user_merc",$data,"id = {$id}"));
        if($bool){
            $result2 = $this->db->field("*")->table("zs_user_merc")->where("id = {$id}")->findobj();
            if( ($result2->is_commercial == 1) && ($result2->is_sex == 1) && ($result2->is_real_name == 1) && ($result2->is_id_card == 1) && ($result2->is_mobile_num == 1) && ($result2->is_address == 1) && ($result2->is_info == 1) && ($result2->is_idcard == 1) && ($result2->is_reidcard == 1) && ($result2->is_handidcard == 1) && ($result2->is_permit == 1) && ($result2->is_field1 == 1) && ($result2->is_field2 == 1) && ($result2->is_field3 == 1) && ($result2->is_field4 == 1) ){
                $data2['status'] = '1';
                $this->db->action($this->db->updateSql("user_merc",$data2,"id = {$id}"));
                $data3['competence'] = ($result->commercial == '经营性')?"官方机构": ($result->commercial == '兽医')?"宠物医师":"救助站";
                $data3['sex'] = $result->sex;
                $data3['real_name'] = $result->real_name;
                $data3['id_card'] = $result->id_card;
                $this->db->action($this->db->updateSql("user",$data3,"mobile_num = {$result->mobile_num}"));
                success("提交成功","/user/verify");
            }else{
                success("提交成功","/user/verify");
            }
        }else{
            error("提交失败，不能重复提交");
        }
    }

    public function verifystatusAction(){
        $arrData = $this->db->field("*")->table("zs_user_merc")->where("status = '1'")->selectobj();
        $this->getView()->assign(["arrData"=>$arrData]);
    }

    public function emptyAction()
    {
        // TODO: Implement __call() method.
    }

    public function addlistuserAction()
    {
        if($this->getRequest()->isPost()){
            $data = $_POST;
            $bool = $this->db->action($this->db->insertSql('ios_user',$data));
            if(!empty($bool)){
                success("提交成功","/user/addlistuser");
            }else{
                error("提交失败");
            }
        }else{
            $this->getView()->assign(["xxxx"=>"yyyy"]);
        }
    }


}
